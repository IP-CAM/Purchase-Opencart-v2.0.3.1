<?php
class ModelCheckoutCredit extends Model {
	public function addCredit($order_id, $data,$customer_id) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "credit SET order_id = '" . (int)$order_id . "', code = '" . $this->db->escape($data['code']) . "', description = '" . $this->db->escape($data['description']) . "', amount = '" . (float)$data['amount'] . "'");
                $credit_id = $this->db->getLastId();
		return $credit_id;
	}

	public function disableCredit($order_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "credit SET status = '0' WHERE order_id = '" . (int)$order_id . "'");
	}

        public function deleteCredit($order_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "credit WHERE order_id = '" . (int)$order_id . "'");
	}

        public function deleteTransaction($credit_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE credit_id = '" . (int)$credit_id . "'");
	}

        public function getTotalCreditAmount($order_id){
                $amount = $this->db->query("SELECT SUM(amount) FROM " . DB_PREFIX . "credit WHERE order_id = '" . (int)$order_id . "'");
                return $amount->row['SUM(amount)'];
        }

        public function getOrderCredit($order_id){
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "credit WHERE order_id = '" . (int)$order_id . "'");

            return $query->rows;

        }

        public function addTransaction($customer_id, $description = '', $amount = '', $order_id = 0, $credit_id = 0) {
        	$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', credit_id = '" . (int)$credit_id . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', date_added = NOW()");

	}

	public function getCredit($code) {
		$status = true;

		$credit_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "credit");

		if ($credit_query->num_rows) {
			if ($credit_query->row['order_id']) {
				$implode = array();

				foreach ($this->config->get('config_complete_status') as $order_status_id) {
					$implode[] = "'" . (int)$order_status_id . "'";
				}

				$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$credit_query->row['order_id'] . "' AND order_status_id IN(" . implode(",", $implode) . ")");

				if (!$order_query->num_rows) {
					$status = false;
				}

//				$order_credit_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_credit` WHERE order_id = '" . (int)$credit_query->row['order_id'] . "' AND credit_id = '" . (int)$credit_query->row['credit_id'] . "'");
//
//				if (!$order_credit_query->num_rows) {
//					$status = false;
//				}
			}

			$credit_history_query = $this->db->query("SELECT SUM(amount) AS total FROM `" . DB_PREFIX . "credit` vh WHERE vh.credit_id = '" . (int)$credit_query->row['credit_id'] . "' GROUP BY vh.credit_id");

			if ($credit_history_query->num_rows) {
				$amount = $credit_query->row['amount'] + $credit_history_query->row['total'];
			} else {
				$amount = $credit_query->row['amount'];
			}

			if ($amount <= 0) {
				$status = false;
			}
		} else {
			$status = false;
		}

		if ($status) {
			return array(
				'credit_id'       => $credit_query->row['credit_id'],
				'amount'           => $amount,
				'date_added'       => $credit_query->row['date_added']
			);
		}
	}

	public function confirm($order_id) {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$this->load->model('localisation/language');

			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_directory']);
			$language->load('mail/credit');

			$credit_query = $this->db->query("SELECT *, vtd.name AS theme FROM `" . DB_PREFIX . "credit` v LEFT JOIN " . DB_PREFIX . "credit_theme vt ON (v.credit_theme_id = vt.credit_theme_id) LEFT JOIN " . DB_PREFIX . "credit_theme_description vtd ON (vt.credit_theme_id = vtd.credit_theme_id) AND vtd.language_id = '" . (int)$order_info['language_id'] . "' WHERE v.order_id = '" . (int)$order_id . "'");

			foreach ($credit_query->rows as $credit) {
				// HTML Mail
				$data = array();

				$data['title'] = sprintf($language->get('text_subject'), $credit['from_name']);

				$data['text_greeting'] = sprintf($language->get('text_greeting'), $this->currency->format($credit['amount'], $order_info['currency_code'], $order_info['currency_value']));
				$data['text_from'] = sprintf($language->get('text_from'), $credit['from_name']);
				$data['text_message'] = $language->get('text_message');
				$data['text_redeem'] = sprintf($language->get('text_redeem'), $credit['code']);
				$data['text_footer'] = $language->get('text_footer');

				if (is_file(DIR_IMAGE . $credit['image'])) {
					$data['image'] = $this->config->get('config_url') . 'image/' . $credit['image'];
				} else {
					$data['image'] = '';
				}

				$data['store_name'] = $order_info['store_name'];
				$data['store_url'] = $order_info['store_url'];
				$data['message'] = nl2br($credit['message']);

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/credit.tpl')) {
					$html = $this->load->view($this->config->get('config_template') . '/template/mail/credit.tpl', $data);
				} else {
					$html = $this->load->view('default/template/mail/credit.tpl', $data);
				}

				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($credit['to_email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender(html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $credit['from_name']), ENT_QUOTES, 'UTF-8'));
				$mail->setHtml($html);
				$mail->send();
			}
		}
	}
}

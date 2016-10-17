<?php
class ModelTotalCredit extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		if ($this->config->get('credit_status')) {
			$this->load->language('total/credit');

			$balance = $this->customer->getBalance();
			$total_credits = 0;

      if (!empty($this->session->data['credits'])) {
					foreach ($this->session->data['credits'] as $key => $credit) {
        			$total_credits += $credit['amount'];
					}
			}

			if ((float)$balance) {
          $product_total = $total - $total_credits;
          if ($product_total > 0){
                  if ($balance > $product_total) {
                          $credit = $product_total;
                  } else {
                          $credit = $balance;
                  }
          }else{
                  $credit = 0;
          }

          if ($credit > 0) {
                  $total_data[] = array(
                          'code'       => 'credit',
                          'title'      => $this->language->get('text_credit'),
                          'value'      => -$credit,
                          'sort_order' => $this->config->get('credit_sort_order')
                  );

                  $total -= $credit;
          }
			}
		}
	}

	public function confirm($order_info, $order_total) {
		$this->load->language('total/credit');

		if ($order_info['customer_id']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', amount = '" . (float)$order_total['value'] . "', date_added = NOW()");
		}
	}

	public function unconfirm($order_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");
	}
}

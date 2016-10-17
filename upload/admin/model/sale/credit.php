<?php
class ModelSaleCredit extends Model {
	public function addCredit($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "credit SET order_id = '" . $this->db->escape($data['order_id']) . "', description = '1" . $this->db->escape($data['description']) . "', code = '". $this->db->escape($data['code']) ."', amount = '" . (float)$data['amount'] . "', status = '1'");
                $credit_id = $this->db->getLastId();
                $this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . $this->db->escape($data['customer_id']) . "', credit_id = '". $credit_id ."', order_id = '" . $this->db->escape($data['order_id']) . "', description = '" . $this->db->escape($data['description']) . "', amount = '" . (float)$data['amount'] . "', date_added = NOW()");
	}

	public function editCredit($credit_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "credit SET amount = '" . (float)$data['amount'] . "', code = '" . $this->db->escape($data['code']) . "', status = '" . (int)$data['status'] . "' WHERE credit_id = '" . (int)$credit_id . "'");
	}

	public function deleteCredit($credit_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "credit WHERE credit_id = '" . (int)$credit_id . "'");
	}

	public function getCredit($credit_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "credit WHERE credit_id = '" . (int)$credit_id . "'");

		return $query->row;
	}

	public function getCredits($data = array()) {
		$sql = "SELECT v.credit_id, v.order_id, v.description, v.customer_transaction_id, v.status, v.amount FROM " . DB_PREFIX . "credit v";

		$sort_data = array(
			'v.credit_id',
                        'v.code',
			'v.order_id',
			'v.description',
			'v.customer_transaction_id',
			'v.status',
			'v.amount'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY v.credit_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

        public function getTotalCreditsAmount($order_id){
                $amount = $this->db->query("SELECT SUM(amount) FROM " . DB_PREFIX . "credit WHERE order_id = '" . (int)$order_id . "'");
                return $amount->row['SUM(amount)'];
        }

	public function getTotalCredits() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "credit");

		return $query->row['total'];
	}

	public function getCreditHistories($credit_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT vh.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, vh.amount, vh.date_added FROM " . DB_PREFIX . "customer_transaction vh LEFT JOIN `" . DB_PREFIX . "order` o ON (vh.order_id = o.order_id) WHERE vh.credit_id = '" . (int)$credit_id . "' ORDER BY vh.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalCreditHistories($credit_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_transaction WHERE credit_id = '" . (int)$credit_id . "'");

		return $query->row['total'];
	}
}

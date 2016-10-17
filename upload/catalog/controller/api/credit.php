<?php
class ControllerApiCredit extends Controller {
	public function index() {
		$this->load->language('api/credit');

		// Delete past credit in case there is an error
		unset($this->session->data['credit']);

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$this->load->model('checkout/credit');

			if (isset($this->request->post['credit'])) {
				$credit = $this->request->post['credit'];
			} else {
				$credit = '';
			}

			$credit_info = $this->model_checkout_credit->getcredit($credit);

			if ($credit_info) {
				$this->session->data['credit'] = $this->request->post['credit'];

				$json['success'] = $this->language->get('text_success');
			} else {
				$json['error'] = $this->language->get('error_credit');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function add() {
		$this->load->language('api/credit');

		$json = array();

		if (!isset($this->session->data['api_id'])) {
			$json['error']['warning'] = $this->language->get('error_permission');
		} else {
			// Add keys for missing post vars
			$keys = array(
				'amount'
			);

			foreach ($keys as $key) {
				if (!isset($this->request->post[$key])) {
					$this->request->post[$key] = '';
				}
			}

			if (isset($this->request->post['credit'])) {
				$this->session->data['credits'] = array();

				foreach ($this->request->post['credit'] as $credit) {
					if (isset($credit['amount'])) {
						$this->session->data['credits'][$credit['code']] = array(
							'code'             => $credit['code'],
							'description'      => $credit['description'],
							'amount'           => $this->currency->convert($credit['amount'], $this->currency->getCode(), $this->config->get('config_currency'))
						);
					}
				}
			}

			// Add a new credit if set

			if (($this->request->post['amount'] < $this->config->get('config_credit_min')) || ($this->request->post['amount'] > $this->config->get('config_credit_max'))) {
				$json['error']['amount'] = sprintf($this->language->get('error_amount'), $this->currency->format($this->config->get('config_credit_min')), $this->currency->format($this->config->get('config_credit_max')));
			}

			if (!$json) {
				$code = mt_rand();

				$this->session->data['credits'][$code] = array(
					'code'             => $code,
					'description'      => "Compra de Créditos",
					'amount'           => $this->currency->convert($this->request->post['amount'], $this->currency->getCode(), $this->config->get('config_currency'))
				);

				$json['success'] = $this->language->get('text_cart');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}

<?php
class ControllerCheckoutCredit extends Controller {
	public function index() {
		if ($this->config->get('credit_status')) {
			$this->load->language('checkout/credit');

			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_loading'] = $this->language->get('text_loading');

			$data['entry_credit'] = $this->language->get('entry_credit');

			$data['button_credit'] = $this->language->get('button_credit');

			if (isset($this->session->data['credit'])) {
				$data['credit'] = $this->session->data['credit'];
			} else {
				$data['credit'] = '';
			}

			if (isset($this->request->get['redirect']) && !empty($this->request->get['redirect'])) {
				$data['redirect'] = $this->request->get['redirect'];
			} else {
				$data['redirect'] = $this->url->link('checkout/cart');
			}

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/credit.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/checkout/credit.tpl', $data);
			} else {
				return $this->load->view('default/template/checkout/credit.tpl', $data);
			}
		}
	}

	public function credit() {
		$this->load->language('checkout/credit');

		$json = array();

		$this->load->model('checkout/credit');

		if (isset($this->request->post['credit'])) {
			$credit = $this->request->post['credit'];
		} else {
			$credit = '';
		}

		$credit_info = $this->model_checkout_credit->getcredit($credit);

		if (empty($this->request->post['credit'])) {
			$json['error'] = $this->language->get('error_empty');
		} elseif ($credit_info) {
			$this->session->data['credit'] = $this->request->post['credit'];

			$this->session->data['success'] = $this->language->get('text_success');

			$json['redirect'] = $this->url->link('checkout/cart');
		} else {
			$json['error'] = $this->language->get('error_credit');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}

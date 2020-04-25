<?php
class ControllerExtensionModuleHyperlocal extends Controller {
	private $error = array();


	/**
	 * [install Method is used for creating database table.]
	 * @return [type] [description]
	 */
	public function install() {

		$this->load->model('hyperlocal/hyperlocal');
		$this->model_hyperlocal_hyperlocal->addTable();
	}

	public function uninstall() {

		$this->load->model('hyperlocal/hyperlocal');
		$this->model_hyperlocal_hyperlocal->dropTable();
	}


	/**
	 * [index Method is used for creating form fields.]
	 * @return [type] [description]
	 */
	public function index() {

		$data = array();

		$data = array_merge($data,$this->load->language('extension/module/hyperlocal'));
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_hyperlocal', $this->request->post);
      $this->load->model('hyperlocal/hyperlocal');
			if($this->request->post['module_hyperlocal_status']) {
				$this->model_hyperlocal_hyperlocal->addOptions();
			} else {
				$this->model_hyperlocal_hyperlocal->deleteOptions();
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL'));
		}

		$form_keys = array(
			'module_hyperlocal_status',
			'module_hyperlocal_customer_loc',
			'module_hyperlocal_customer_lat',
			'module_hyperlocal_customer_lng',
			'module_hyperlocal_radius',
			'module_module_hyperlocal_radius_unit',
			'module_hyperlocal_modal',
			'module_hyperlocal_google_api_key',
		);

		foreach ($form_keys as $key => $value) {
			if(isset($this->request->post[$value])) {
				$data[$value] = $this->request->post[$value];
			} else if($this->config->get($value)) {
				$data[$value] = $this->config->get($value);
			} else {
				$data[$value] = '';
			}
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/hyperlocal', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/module/hyperlocal', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/hyperlocal', $data));
	}

	protected function validate() {

		if (!$this->user->hasPermission('modify', 'extension/module/hyperlocal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['module_hyperlocal_status']) && $this->request->post['module_hyperlocal_status']) {
			if (!isset($this->request->post['module_hyperlocal_radius']) || $this->request->post['module_hyperlocal_radius'] <= 0 || $this->request->post['module_hyperlocal_radius'] >= 100000) {
				$this->error['warning'] = $this->language->get('error_radius');
			}

			if (!isset($this->request->post['module_hyperlocal_modal']) || $this->request->post['module_hyperlocal_modal'] =='' || $this->request->post['module_hyperlocal_modal'] == null || ctype_space($this->request->post['module_hyperlocal_modal']) || !preg_match('/^[A-Z ]+$/i',$this->request->post['module_hyperlocal_modal']) || strlen($this->request->post['module_hyperlocal_modal']) >= 255) {
				$this->error['warning'] = $this->language->get('error_modal');
			}

			if (!isset($this->request->post['module_hyperlocal_customer_loc']) || $this->request->post['module_hyperlocal_customer_loc'] =='' || $this->request->post['module_hyperlocal_customer_loc'] == null|| ctype_space($this->request->post['module_hyperlocal_customer_loc'])) {
				$this->error['warning'] = $this->language->get('error_location');
			}

			if (!isset($this->request->post['module_hyperlocal_customer_lat']) || !$this->request->post['module_hyperlocal_customer_lat']) {
				$this->error['warning'] = $this->language->get('error_location');
			}

			if (!isset($this->request->post['module_hyperlocal_customer_lng']) || !$this->request->post['module_hyperlocal_customer_lng']) {
				$this->error['warning'] = $this->language->get('error_location');
			}
		}
		return !$this->error;
	}
}

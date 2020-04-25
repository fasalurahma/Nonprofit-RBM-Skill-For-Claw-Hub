<?php
/**
* @version [Product Version 1.0.0.0.]
* @category Webkul
* @package Opencart MP OrderSplit
* @author [Webkul] <[<http://webkul.com/>:smirk:>;
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/
class ControllerExtensionModuleMpordersplit extends Controller {

	private $error = array();

	public function index() {

		$data = $this->load->language('extension/module/mpordersplit');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model("setting/extension");

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_mpordersplit', $this->request->post);
			$this->request->post['mpordersplit_status'] = $this->request->post['module_mpordersplit_status'];
			$this->model_setting_setting->editSetting('mpordersplit', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL'));

		}		

		/**error variable manipulation starts here */
		$error_arr = array(
		 'warning',
		 'module_mpordersplit_status',
		 'module_mpordersplit_allowed_payment',
		 'module_mpordersplit_allowed_shipping',		 
		);

		foreach ($error_arr as $key => $err) {
			if (isset($this->error[$err])) {
				$data['error_' . $err] = $this->error[$err];
			} else {
				$data['error_' . $err] = '';
			}
		}
		/**error variable manipulation ends here */

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/mpordersplit', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/module/mpordersplit', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL');
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL');		

		/**form data manipulation code starts here */
		$form_arr = array(
		 'module_mpordersplit_status',
		 'module_mpordersplit_allowed_payment',
	  'module_mpordersplit_allowed_shipping',
		);

		foreach ($form_arr as $key => $value) {
			if (isset($this->request->post[$value])) {
				$data[$value] = $this->request->post[$value];
			} else {
				$data[$value] = $this->config->get($value);
			}
		}
		/**form data manipulation code ends here */

		$shipping_methods = $this->model_setting_extension->getInstalled('shipping');
		$data['shipping_methods'] = array();

		foreach ($shipping_methods as $key => $shipping_method) {
			$this->load->language('extension/shipping/' . $shipping_method);

			if($this->config->get('shipping_' . $shipping_method . '_status')) {
				$data['shipping_methods'][] = array(
				 'title' => $this->language->get('heading_title'),
				 'code'  => $shipping_method,
				);
			}			
		}
		$data['payment_methods'] = array();
		$payment_methods = $this->model_setting_extension->getInstalled('payment');

		foreach ($payment_methods as $key => $payment_method) {
			$this->load->language('extension/payment/' . $payment_method);

			if($this->config->get('payment_' . $payment_method . '_status')) {
				$data['payment_methods'][] = array(
				 'title' => $this->language->get('heading_title'),
				 'code'  => $payment_method,
				);
			}			
		}


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/mpordersplit', $data));
	}

	/**
	 * Validate method is used for validate the user.
	 * @return  It will return true or false
	 */
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/mpordersplit')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if(isset($this->request->post['module_mpordersplit_allowed_shipping']) && is_array($this->request->post['module_mpordersplit_allowed_shipping'])) {

			$shipping_methods = $this->model_setting_extension->getInstalled('shipping');

			foreach ($this->request->post['module_mpordersplit_allowed_shipping'] as $key => $value) {
				if(!in_array($value,$shipping_methods)) {

					$this->error['module_mpordersplit_allowed_shipping'] = $this->language->get('error_shipping_method');
					break;
				}
			}
		}

		if(isset($this->request->post['module_mpordersplit_allowed_payment']) && is_array($this->request->post['module_mpordersplit_allowed_payment'])) {

			$payment_methods = $this->model_setting_extension->getInstalled('payment');

			foreach ($this->request->post['module_mpordersplit_allowed_payment'] as $key => $value) {
				if(!in_array($value,$payment_methods)) {

					$this->error['module_mpordersplit_allowed_payment'] = $this->language->get('error_payment_method');
					break;
				}
			}
		}

		$status_check_array = array('module_mpordersplit_status'); 
		
		foreach ($status_check_array as $key => $value) {
            if(!isset($this->request->post[$value]) || !is_numeric($this->request->post[$value])) {
                $this->error[$value] = $this->language->get('error_status_check');
            } elseif(isset($this->request->post[$value]) && !$this->checkStatus($this->request->post[$value])) {
                $this->error[$value] = $this->language->get('error_status_check');
            }
        }

		if(!isset($this->error['warning']) && $this->error) {
			$this->error['warning'] = $this->language->get('error_check');
		}

		return !$this->error;
	}

	/**
     * function to check if status is different from enabled or disabled
     * @param [type] $status [status value]
     * @return boolean
    */
    public function checkStatus($status = 0) {
        
        if($status > 1 || $status < 0) {
            return false;
        } else {
            return true;
        }
    }


	public function install() {
		$this->load->model('mpordersplit/mpordersplit');
		$this->model_mpordersplit_mpordersplit->installModule();
	}

	public function uninstall() {
		$this->load->model('mpordersplit/mpordersplit');
		$this->model_mpordersplit_mpordersplit->uninstallModule();
	}
}

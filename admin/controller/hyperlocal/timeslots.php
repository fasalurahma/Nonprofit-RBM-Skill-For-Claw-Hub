<?php
class ControllerHyperlocalTimeslots extends Controller {
	private $error = array();

	public function index() {
		$data = $this->load->language('hyperlocal/timeslots');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('hyperlocal/hyperlocal');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->validate()) {
			$this->model_hyperlocal_hyperlocal->saveTime($this->request->post);
			if($this->request->post['wk_hyperlocal_addon_status']) {
				$this->model_hyperlocal_hyperlocal->addOptions();
			} else {
				$this->model_hyperlocal_hyperlocal->deleteOptions();
			}
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('hyperlocal/timeslots', '&user_token=' . $this->session->data['user_token'], true));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('hyperlocal/timeslots', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('hyperlocal/timeslots', 'user_token=' . $this->session->data['user_token'], true);

    if(isset($this->session->data['success']) && $this->session->data['success']) {
      $data['success'] = $this->session->data['success'];
      unset($this->session->data['success']);
    } else {
      $data['success'] = '';
    }

		if(isset($this->error) && $this->error) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		$data['user_token'] = $this->session->data['user_token'];

    $data = array_merge($data,$this->model_hyperlocal_hyperlocal->getTime());

    $data['weekArray'] = array(
		   'sunday',
		   'monday',
		   'tuesday',
		   'wednesday',
		   'thursday',
		   'friday',
		   'saturday',
	   );

		$this->load->model('localisation/language');
		$languages = $data['languages'] = $this->model_localisation_language->getLanguages();
		$this->load->model('localisation/currency');
		$currency = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));
		if ($currency['symbol_left']) {
			$data['currency'] = $currency['symbol_left'];
		} else {
			$data['currency'] = $currency['symbol_right'];
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('hyperlocal/timeslots', $data));
	}

	protected function validate() {
		$this->load->language('hyperlocal/timeslots');
		if (!$this->user->hasPermission('modify', 'hyperlocal/timeslots')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		$formdata = $this->request->post;
		$week = array(
			'sunday',
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday',
		);
     if($formdata['wk_hyperlocal_addon_status']) {
			 foreach ($week as $key => $value) {
				 if(isset($formdata['wk_hyperlocal_addon_' . $value . '_stime']) && $formdata['wk_hyperlocal_addon_' . $value . '_stime'] || (ctype_space($formdata['wk_hyperlocal_addon_' . $value . '_stime']) || isset($formdata['wk_hyperlocal_addon_' . $value . '_stime']))) {
					 if(!$formdata['wk_hyperlocal_addon_' . $value . '_ltime']||ctype_space($formdata['wk_hyperlocal_addon_' . $value . '_stime']) || !preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $formdata['wk_hyperlocal_addon_' . $value . '_stime']) || !preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $formdata['wk_hyperlocal_addon_' . $value . '_stime'])) {
						 $this->error['warning'] = $this->language->get('end_time_error');
					 }
				 }
				 if(isset($formdata['wk_hyperlocal_addon_' . $value . '_ltime']) && $formdata['wk_hyperlocal_addon_' . $value . '_ltime'] || ((ctype_space($formdata['wk_hyperlocal_addon_' . $value . '_ltime'])) || isset($formdata['wk_hyperlocal_addon_' . $value . '_ltime']))) {
					 if(!$formdata['wk_hyperlocal_addon_' . $value . '_stime']) {
						$this->error['warning'] = $this->language->get('start_time_error');
					 }
				 }
				 if((strtotime($formdata['wk_hyperlocal_addon_' . $value . '_ltime']) < strtotime($formdata['wk_hyperlocal_addon_' . $value . '_stime']))|| (strtotime($formdata['wk_hyperlocal_addon_' . $value . '_ltime']) == strtotime($formdata['wk_hyperlocal_addon_' . $value . '_stime']))) {
					 $this->error['warning'] = $this->language->get('end_time_less_error');
				 }
			 }
		 }

			return $this->error;
	}
}

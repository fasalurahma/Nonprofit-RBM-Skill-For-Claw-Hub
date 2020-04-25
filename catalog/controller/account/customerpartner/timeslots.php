<?php
class ControllerAccountCustomerpartnerTimeslots extends Controller {

	private $error = array();

	public function index() {

		$data = array();

		$data = array_merge($data,$this->language->load('account/customerpartner/timeslots'));

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/timeslots', '', 'SSL');
			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

        $data['module_hyperlocal_status'] = $this->config->get('module_hyperlocal_status');

        if (!$data['chkIsPartner'] || !$data['module_hyperlocal_status'] || !in_array('location', $this->config->get('marketplace_allowed_account_menu')) || !$data['module_hyperlocal_status']) {
            $this->response->redirect($this->url->link('account/account'));
        }

		$this->load->model('account/timeslots');

		$this->document->setTitle($this->language->get('heading_title_hyperlocal'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->validate($this->request->post)) {

			  $data = $this->request->post;

        $this->model_account_timeslots->addSlots($data);

				if($data['wk_hyperlocal_addon_status']) {
					$this->model_account_timeslots->assignedOptions();
				} else {
					$this->model_account_timeslots->deleteOptions();
				}


        $this->session->data['success'] = $this->language->get('text_success');

				$this->response->redirect($this->url->link('account/customerpartner/timeslots', '', 'SSL'));

		}

    $data = array_merge($data,$this->model_account_timeslots->getslots());

		// $product_total = $this->model_account_shippinglist->viewtotalentry($filter_array);

      	$data['breadcrumbs'] = array();

      	$data['breadcrumbs'][] = array(
			     'text'      => $this->language->get('text_home'),
		      'href'      => $this->url->link('common/home'),
		      'separator' => false,
      	);

      	$data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_account'),
		      'href'      => $this->url->link('account/account'),
        'separator' => $this->language->get('text_separator')
      	);

      	$data['breadcrumbs'][] = array(
        'text'      => $this->language->get('heading_title_hyperlocal'),
			     'href'      => $this->url->link('account/customerpartner/shippinglist', '', 'SSL'),
        'separator' => $this->language->get('text_separator')
      	);

      	if (isset($this->session->data['error_warning'])) {
			$this->error['warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		}

		if (isset($this->session->data['attention'])) {
			$data['attention'] = $this->session->data['attention'];
			unset($this->session->data['attention']);
		} else {
			$data['attention'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

    $data['weekArray'] = array(
     'sunday',
     'monday',
     'tuesday',
     'wednesday',
     'thursday',
     'friday',
     'saturday',
    );

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] = $this->url->link('account/customerpartner/timeslots', '', 'SSL');

		$data['back'] = $this->url->link('account/account', '', 'SSL');


		$data['column_left'] = $this->load->Controller('common/column_left');
		$data['column_right'] = $this->load->Controller('common/column_right');
		$data['content_top'] = $this->load->Controller('common/content_top');
		$data['content_bottom'] = $this->load->Controller('common/content_bottom');
		$data['footer'] = $this->load->Controller('common/footer');
		$data['header'] = $this->load->Controller('common/header');

		$this->response->setOutput($this->load->view('account/customerpartner/timeslot', $data));

	}
	public function validate($formdata) {
		$data = array();
		$this->load->language('account/customerpartner/timeSlots');
	      	$week = array(
					    'sunday',
					    'monday',
					    'tuesday',
					    'wednesday',
					    'thursday',
					    'friday',
					    'saturday',
				    );

				foreach ($week as $key => $value) {
					if(isset($formdata['wk_hyperlocal_addon_' . $value . '_stime']) && $formdata['wk_hyperlocal_addon_' . $value . '_stime'] || (ctype_space($formdata['wk_hyperlocal_addon_' . $value . '_stime']) || isset($formdata['wk_hyperlocal_addon_' . $value . '_stime']))) {
						if(!$formdata['wk_hyperlocal_addon_' . $value . '_ltime']||ctype_space($formdata['wk_hyperlocal_addon_' . $value . '_stime']) || !preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $formdata['wk_hyperlocal_addon_' . $value . '_stime']) || !preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $formdata['wk_hyperlocal_addon_' . $value . '_stime'])) {
							$this->error['warning'] = $this->language->get('end_time_error');
							break;
						}
					}
					if(isset($formdata['wk_hyperlocal_addon_' . $value . '_ltime']) && $formdata['wk_hyperlocal_addon_' . $value . '_ltime'] || ((ctype_space($formdata['wk_hyperlocal_addon_' . $value . '_ltime'])) || isset($formdata['wk_hyperlocal_addon_' . $value . '_ltime']))) {
						if(!$formdata['wk_hyperlocal_addon_' . $value . '_stime']) {
							$this->error['warning'] = $this->language->get('start_time_error');
							break;
						}
					}

						if((strtotime($formdata['wk_hyperlocal_addon_' . $value . '_ltime']) < strtotime($formdata['wk_hyperlocal_addon_' . $value . '_stime']))|| (strtotime($formdata['wk_hyperlocal_addon_' . $value . '_ltime']) == strtotime($formdata['wk_hyperlocal_addon_' . $value . '_stime']))) {
							$this->error['warning'] = $this->language->get('end_time_less_error');
							break;
						}
				}

		     return $this->error;

				}
}
?>

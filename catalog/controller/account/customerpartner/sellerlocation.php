<?php
class ControllerAccountCustomerpartnerSellerlocation extends Controller {
	private $error = array();

	public function index() {
		$data = array();

		$data =array_merge($data,$this->language->load('account/customerpartner/sellerlocation'));

		$this->document->setTitle($this->language->get('heading_title_shipping_location'));

		$this->load->model('account/customerpartner');

        $data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

        $data['module_hyperlocal_status'] = $this->config->get('module_hyperlocal_status');

        if (!$data['chkIsPartner'] || !$data['module_hyperlocal_status'] || !in_array('location', $this->config->get('marketplace_allowed_account_menu'))) {
            $this->response->redirect($this->url->link('account/account'));
        }

		$this->load->model('customerpartner/sellerlocation');


		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['seller'])) {
			$data['error_seller_value'] = $this->error['seller'];
		} else {
			$data['error_seller_value'] = array();
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$filter_array = array(
		 'filter_location',
		 'filter_latitude',
		 'filter_longitude',
		 'page',
		 'sort',
		 'order',
		 'start',
		 'limit',
		);

		$url = '';

		foreach ($filter_array as $unset_key => $key) {

			if (isset($this->request->get[$key])) {
				$filter_array[$key] = $this->request->get[$key];
			} else {
				if ($key=='page')
					$filter_array[$key] = 1;
				elseif($key=='sort')
					$filter_array[$key] = 'cs.id';
				elseif($key=='order')
					$filter_array[$key] = 'ASC';
				elseif($key=='start')
					$filter_array[$key] = ($filter_array['page'] - 1) * 10;
				elseif($key=='limit')
					$filter_array[$key] = 10;
				else
					$filter_array[$key] = null;
			}
			unset($filter_array[$unset_key]);

			if(isset($this->request->get[$key])) {
				$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}

		$data['delete'] = $this->url->link('account/customerpartner/sellerlocation/delete', '', 'SSL');
		$data['action'] = $this->url->link('account/customerpartner/sellerlocation/save', '' , 'SSL');
		$data['cancel'] = $this->url->link('account/account', '', 'SSL');

		$location = array();

		$location = $this->model_customerpartner_sellerlocation->getLocation($filter_array);

		$data['locations'] = $location;

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('account/account', '', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_shipping_location'),
			'href' => $this->url->link('account/customerpartner/sellerlocation', '', 'SSL')
		);

		$url = '';

		foreach ($filter_array as $key => $value) {
			if(isset($this->request->get[$key])) {
				if(!isset($this->request->get['order']) AND isset($this->request->get['sort']))
					$url .= '&order=DESC';
				if ($key=='filter_name')
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
				elseif($key=='order')
					$url .= $value=='ASC' ? '&order=DESC' : '&order=ASC';
				elseif($key!='sort')
					$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}

		$url = '';

		foreach ($filter_array as $key => $value) {
			if(isset($this->request->get[$key])) {
				if(!isset($this->request->get['order']) AND isset($this->request->get['sort']))
					$url .= '&order=DESC';
				if ($key=='filter_name')
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
				elseif($key!='page')
					$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}
		foreach ($filter_array as $key => $value) {
			if($key!='start' AND $key!='end')
				$data[$key] = $value;
		}

		$data['column_left'] = $this->load->Controller('common/column_left');
		$data['column_right'] = $this->load->Controller('common/column_right');
		$data['content_top'] = $this->load->Controller('common/content_top');
		$data['content_bottom'] = $this->load->Controller('common/content_bottom');
		$data['footer'] = $this->load->Controller('common/footer');
		$data['header'] = $this->load->Controller('common/header');

   	$this->response->setOutput($this->load->view('account/customerpartner/sellerlocation', $data));

	}


	/**
	 * [save Method is used for saving seller location.]
	 * @return [type] [description]
	 */
	public function save() {

		$this->language->load('account/customerpartner/sellerlocation');

		if ($this->validateForm()) {
			$this->load->model('customerpartner/sellerlocation');
			$this->model_customerpartner_sellerlocation->saveLocation($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
		}
		$this->response->redirect($this->url->link('account/customerpartner/sellerlocation', '' , 'SSL'));
	}

	public function delete() {

		$this->load->model('customerpartner/sellerlocation');
		$this->load->language('account/customerpartner/sellerlocation');

		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $id) {
				$this->model_customerpartner_sellerlocation->deleteLocation($id);
	  		}
			$this->session->data['success'] = $this->language->get('text_delete_success');
		}

    	$this->response->redirect($this->url->link('account/customerpartner/sellerlocation', '', 'SSL'));
  	}

	private function validateForm() {


    	if(!$this->request->post['location'] || empty($this->request->post['location']) || !$this->request->post['latitude'] || empty($this->request->post['latitude']) || !$this->request->post['longitude'] || empty($this->request->post['longitude'])) {

			$this->error['warning'] = $this->language->get('error_warning');
		}

		if ($this->error && isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
			$this->session->data['error_warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
  	}
}
?>

<?php
class ControllerHyperlocalSellerlocation extends Controller {
	private $error = array();

	public function index() {

		$data  = array();

		$data = array_merge($data,$this->language->load('hyperlocal/sellerlocation'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('hyperlocal/hyperlocal');

		if (isset($this->request->get['filter_seller_name'])) {
			$filter_seller_name = $this->request->get['filter_seller_name'];
		} else {
			$filter_seller_name = null;
		}

		if (isset($this->request->get['filter_location'])) {
			$filter_location = $this->request->get['filter_location'];
		} else {
			$filter_location = null;
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_seller_name'])) {
			$url .= '&filter_seller_name=' . $this->request->get['filter_seller_name'];
		}

		if (isset($this->request->get['filter_location'])) {
			$url .= '&filter_location=' . $this->request->get['filter_location'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$filter_data = array(
			'filter_seller_name' => $filter_seller_name,
			'filter_location'    => $filter_location,
			'order'              => $order,
			'start'              => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'              => $this->config->get('config_limit_admin')
		);

		$data['user_token'] = $this->session->data['user_token'];

		$partners = $this->model_hyperlocal_hyperlocal->getLocation($filter_data);

		$data['partners'] = array();
		foreach ($partners as $key => $value) {
			if (!$value['seller_id']) {
				$data['partners'][] = array(
					'location_id' => $value['id'],
					'seller_id'   => $value['seller_id'],
					'name'        => 'Admin',
					'location'    => $value['location']
				);
			} else {
				$data['partners'][] = array(
					'location_id' => $value['id'],
					'seller_id'   => $value['seller_id'],
					'name'        => $value['seller_name'],
					'location'    => $value['location']
				);
			}
		}

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('hyperlocal/sellerlocation', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['add'] = $this->url->link('hyperlocal/sellerlocation/getform', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['delete'] = $this->url->link('hyperlocal/sellerlocation/delete', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['cancel'] = $this->url->link('hyperlocal/sellerlocation', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['filter_seller_name'] = $filter_seller_name;
		$data['filter_location'] = $filter_location;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('hyperlocal/sellerlocationlist', $data));
	}

	public function getform() {

		$data  = array();

		$data = array_merge($data,$this->language->load('hyperlocal/sellerlocation'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('hyperlocal/hyperlocal');

		$data['partners'] = $this->model_hyperlocal_hyperlocal->getPartners();

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
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

		$url = '';

		$data['action'] = $this->url->link('hyperlocal/sellerlocation/save', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');

		$location = array();
		if (isset($this->request->post['seller']) && $this->request->post['seller']) {
			foreach ($this->request->post['seller'] as $key => $value) {
				$location[] = array(
					'seller_id' => $value['seller_id'],
					'location'  => $value['location'],
					'longitude' => $value['longitude'],
					'latitude'  => $value['latitude']
				);
			}
		}

		$data['module_hyperlocal_google_api_key'] = trim($this->config->get('module_hyperlocal_google_api_key'));

		$data['location'] = $location;

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('hyperlocal/sellerlocation', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['cancel'] = $this->url->link('hyperlocal/sellerlocation', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('hyperlocal/sellerlocation', $data));
	}

	/**
	 * [save Method is used for saving seller location.]
	 * @return [type] [description]
	 */
	public function save() {
		$this->language->load('hyperlocal/sellerlocation');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->post['seller'])) {
				$this->load->model('hyperlocal/hyperlocal');
				$this->model_hyperlocal_hyperlocal->saveLocation($this->request->post);
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('hyperlocal/sellerlocation', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}
		}
		$this->getform();
	}

	public function delete() {
		$this->language->load('hyperlocal/sellerlocation');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('hyperlocal/hyperlocal');

		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $location_id) {
				$this->model_hyperlocal_hyperlocal->deleteLocation($location_id);
			}
			$this->session->data['success'] = $this->language->get('text_delete_success');
		}
		$this->response->redirect($this->url->link('hyperlocal/sellerlocation', 'user_token=' . $this->session->data['user_token'], 'SSL'));
	}

	public function autocomplete() {

		$json = array();

		if (isset($this->request->get['filter_seller_name'])) {

			$this->load->model('hyperlocal/hyperlocal');

			if (isset($this->request->get['filter_seller_name'])) {
				$filter_seller_name = $this->request->get['filter_seller_name'];
			} else {
				$filter_seller_name = '';
			}

			if (isset($this->request->get['filter_view'])) {
				$filter_view = $this->request->get['filter_view'];
			} else {
				$filter_view = 0 ;
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 20;
			}

			$data = array(
				'filter_seller_name' => $filter_seller_name,
				'start'              => 0,
				'limit'              => $limit
			);

			$results = $this->model_hyperlocal_hyperlocal->getPartners($data);

			foreach ($results as $result) {

				$option_data = array();

				$json[] = array(
					'id' 		       => $result['customer_id'],
					'seller_name' => strip_tags(html_entity_decode($result['seller_name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}
		$this->response->setOutput(json_encode($json));
	}

	private function validateForm() {

    	if (!$this->user->hasPermission('modify', 'hyperlocal/sellerlocation')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}
    	if(isset($this->request->post['seller'])) {
			foreach ($this->request->post['seller'] as $key => $value) {
				foreach ($value as $cindex => $index) {
					if(!isset($value[$cindex]) || (empty($value[$cindex]) && $cindex != 'seller_id') ) {
						$this->error['warning'] = $this->language->get('error_warning');
						$this->error['seller'][$key][$cindex] = $this->language->get('error_no_' . $cindex);

					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		return !$this->error;
  	}
}
?>

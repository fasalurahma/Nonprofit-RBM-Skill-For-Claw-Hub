<?php
class ControllerCommonLocationJournal extends Controller {
	private $error = array();

	public function index() {

		$data = array();
		$data = array_merge($data,$this->load->language('common/location'));	
	
		if (isset($this->session->data['loc']) && $this->session->data['loc']) {
			$data['text_location'] = $this->session->data['loc']; 	
		}

		$data['action'] = $this->url->link('common/location_journal/save','', 'SSL');
		$data['home'] = $this->url->link('common/home');
		
		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
		} else {
			$data['error_warning'] = '';
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_location'),
			'href' => $this->url->link('common/location_journal', '', true)
		);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/location_journal', $data));
	}
	public function getCartProducts() {
		$json['cart_products'] = $this->cart->hasProducts();
		$this->response->setOutput(json_encode($json));
	}
	public function save() {
		$this->load->language('common/location');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->registry->set('hyperlocal',new Hyperlocal($this->registry));
			if ($this->cart->hasProducts()) {
				foreach ($this->cart->getProducts() as $cart_product) {
					$seller = $this->hyperlocal->getSellerIdByProduct($cart_product['product_id']);
					$seller_locations = false;
					if ($seller) {
						$seller_locations = $this->hyperlocal->getLocationsBySeller($seller['customer_id']);
					} else {
						$seller_locations = $this->hyperlocal->getLocationsBySeller(0);
					}
					if ($seller_locations) {
						$flag = 1;
						
						if ($this->config->get('module_hyperlocal_radius')) {
							$radius = $this->config->get('module_hyperlocal_radius');	
						} else {
							$radius = 0;
						}

						$unit = $this->config->get('module_module_hyperlocal_radius_unit');

						foreach ($seller_locations as $seller_location) {

							$distance = $this->hyperlocal->getDistance($this->request->post['latitude_head'],$this->request->post['longitude_head'],$seller_location['latitude'],$seller_location['longitude'],$unit);
							
							if ($distance <= $radius) {
								$flag = 0;
								break;
							}
						}
												
						if ($flag) {							
							$this->cart->remove($cart_product['cart_id']);
						}
					}					
				}
			}
			$this->session->data['loc'] = $this->request->post['address'];
			$this->session->data['lat'] = $this->request->post['latitude_head'];
			$this->session->data['lng'] = $this->request->post['longitude_head'];
			$this->response->redirect($this->url->link('common/home', '', 'SSL'));		
		} else {
			$this->session->data['error'] = $this->language->get('text_error_loc');
			$this->response->redirect($this->url->link('common/location_journal', '', 'SSL'));		
		}
	}

	public function autoLocation() {

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			if (isset($this->request->post['latitude_head']) && $this->request->post['latitude_head']) {
				$this->session->data['lat'] = $this->request->post['latitude_head'];
			}

			if (isset($this->request->post['longitude_head']) && $this->request->post['longitude_head']) {
				$this->session->data['lng'] = $this->request->post['longitude_head'];	
			}

			if (isset($this->request->post['address']) && $this->request->post['address']) {
				$this->session->data['loc'] = $this->request->post['address'];	
			}	
		}
		
	}

	private function validate() {
		if(isset($this->request->post['address']) && !empty($this->request->post['address']) && 
		   isset($this->request->post['latitude_head']) && !empty($this->request->post['latitude_head'])  &&
		   isset($this->request->post['longitude_head']) && !empty($this->request->post['longitude_head']) ) {
			return true;
		}
		return false;
	}
}
<?php
class ControllerCommonLocation extends Controller {
	public function index() {

	$data = array();
	$data = array_merge($data,$this->load->language('common/location'));

	$data['module_hyperlocal_google_api_key'] = trim($this->config->get('module_hyperlocal_google_api_key'));

	if (isset($this->session->data['loc']) && $this->session->data['loc']) {
		$data['text_location'] = $this->session->data['loc'];
	}

	if ($this->request->server['HTTPS']) {
		$server = $this->config->get('config_ssl');
	} else {
		$server = $this->config->get('config_url');
	}

	if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
		$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
	} else {
		$data['logo'] = '';
	}

	$data['module_hyperlocal_modal'] = '';
	if($this->config->get('module_hyperlocal_modal')) {
		$data['module_hyperlocal_modal'] = $this->config->get('module_hyperlocal_modal');
	}
	$data['config_maintenance'] = $this->config->get('config_maintenance');
	$data['action'] = $this->url->link('common/location/save','', 'SSL');
	$data['home'] = $this->url->link('common/home');

	//$data['skip'] = $this->url->link('common/location/autoLocation','', 'SSL');

	// $this->document->addScript('catalog/view/javascript/jquery/jquery-2.1.1.min.js');
	// $this->document->addScript('catalog/view/javascript/bootstrap/js/bootstrap.min.js');
	// $this->document->addStyle('catalog/view/theme/default/stylesheet/stylesheet.css');

	return $this->load->view('common/location', $data);

	}
	public function getCartProducts() {
		$json['cart_products'] = $this->cart->hasProducts();
		$this->response->setOutput(json_encode($json));
	}
	public function save() {

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

							if ($distance >= $radius) {
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
		}
		/*$path = explode("route=",$this->request->post['path_head'])[1];

		if (isset($path) && $path) {
			$this->response->redirect($this->url->link($path, '', 'SSL'));
		} else {
			$this->response->redirect($this->url->link('common/home', '', 'SSL'));
		}*/

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

<?php
class ControllerExtensionShippingHyperlocal extends Controller {
	private $error = array();

	public function index() {

		$data = array();

		$data = array_merge($data,$this->load->language('extension/shipping/hyperlocal'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_hyperlocal', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
		}



		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_shipping'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/hyperlocal', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/hyperlocal', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);



		if (isset($this->request->post['shipping_hyperlocal_geo_zone_id'])) {
			$data['shipping_hyperlocal_geo_zone_id'] = $this->request->post['shipping_hyperlocal_geo_zone_id'];
		} else {
			$data['shipping_hyperlocal_geo_zone_id'] = $this->config->get('shipping_hyperlocal_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['shipping_hyperlocal_sort_order'])) {
			$data['shipping_hyperlocal_sort_order'] = $this->request->post['shipping_hyperlocal_sort_order'];
		} else {
			$data['shipping_hyperlocal_sort_order'] = $this->config->get('shipping_hyperlocal_sort_order');
		}

		if (isset($this->request->post['shipping_hyperlocal_title'])) {
			$data['shipping_hyperlocal_title'] = $this->request->post['shipping_hyperlocal_title'];
		} else {
			$data['shipping_hyperlocal_title'] = $this->config->get('shipping_hyperlocal_title');
		}

		if (isset($this->request->post['shipping_hyperlocal_status'])) {
			$data['shipping_hyperlocal_status'] = $this->request->post['shipping_hyperlocal_status'];
		} else {
			$data['shipping_hyperlocal_status'] = $this->config->get('shipping_hyperlocal_status');
		}

		if (isset($this->request->post['shipping_hyperlocal_flatrate'])) {
			$data['shipping_hyperlocal_flatrate'] = $this->request->post['shipping_hyperlocal_flatrate'];
		} else {
			$data['shipping_hyperlocal_flatrate'] = $this->config->get('shipping_hyperlocal_flatrate');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/hyperlocal', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/hyperlocal')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['shipping_hyperlocal_flatrate']) || $this->request->post['shipping_hyperlocal_flatrate']<=0) {
			$this->error['warning'] = $this->language->get('error_flatrate');
		}

		return !$this->error;
	}
}

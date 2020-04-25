<?php
class ModelExtensionShippingHyperlocal extends Model {
	function getQuote($address,$sellerid = array()) {
		$this->registry->set('hyperlocal',new Hyperlocal($this->registry));
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_hyperlocal_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_hyperlocal_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$cost = 0;
			$seller_cost = 0;
			$this->load->model('account/customerpartnerorder');
			$sellers = $this->model_account_customerpartnerorder->sellerAdminData($this->cart->getProducts());

			if ($sellers) {
				foreach ($sellers as $seller) {
					$distance = $this->config->get('module_hyperlocal_radius');
					$latlon = array();
					if ($seller['seller'] != 'Admin') {
						$latlon = $this->db->query("SELECT latitude,longitude FROM " . DB_PREFIX . "seller_location WHERE seller_id = " . $this->db->escape($seller['seller']))->rows;
					} else {
						$latlon = $this->db->query("SELECT latitude,longitude FROM " . DB_PREFIX . "seller_location WHERE seller_id = 0")->rows;
					}

					if ($latlon) {
						foreach ($latlon as $latlonseller) {
							$temp_distance = $this->hyperlocal->getDistance($this->session->data['lat'],$this->session->data['lng'],$latlonseller['latitude'],$latlonseller['longitude']);

							if ($distance > $temp_distance) {
								$distance = $temp_distance;
							}
						}

					}

					if ($seller['seller'] != 'Admin') {
						$query = $this->db->query("SELECT price FROM " . DB_PREFIX . "seller_shipping_cost WHERE weight_from <= " . $seller['weight'] . " AND weight_to >=" . $seller['weight'] . " AND range_from <= " . $distance . " AND range_to >=" . $distance . " AND seller_id =" . $seller['seller'])->row;
					} else {
						$query = $this->db->query("SELECT price FROM " . DB_PREFIX . "seller_shipping_cost WHERE weight_from <= " . $seller['weight'] . " AND weight_to >=" . $seller['weight'] . " AND range_from <= " . $distance . " AND range_to >=" . $distance . " AND seller_id = 0")->row;
					}


					if (isset($query['price'])) {
						$cost += $query['price'];
						$seller_cost = $query['price'];
					} else {
						if ($seller['seller'] != 'Admin') {
							$query = $this->db->query("SELECT amount FROM " . DB_PREFIX . "hyperlocal_flatshipping WHERE seller_id =" . $seller['seller'])->row;
							if (isset($query['amount'])) {
								$cost += $query['amount'];
								$seller_cost = $query['amount'];
							} else {
								$cost += $this->config->get('shipping_hyperlocal_flatrate');
								$seller_cost = $this->config->get('shipping_hyperlocal_flatrate');
							}
						} else {
							$cost += $this->config->get('shipping_hyperlocal_flatrate');
							$seller_cost = $this->config->get('shipping_hyperlocal_flatrate');
						}
					}

					if (isset($sellerid['seller']) && $sellerid['seller'] == $seller['seller']) {
						$quote_data['shipping_hyperlocal'] = array(
							'cost' => $seller_cost
						);
						$method_data = array(
							'quote' => $quote_data
						);
						return 	$method_data;
					}
				}
			}

			$quote_data = array();

			if((float)$cost) {
				$quote_data['hyperlocal'] = array(
					'code'         => 'hyperlocal.hyperlocal',
					'title'        => $this->config->get('shipping_hyperlocal_title'),
					'cost'         => $cost,
					'tax_class_id' => $this->config->get('shipping_hyperlocal_tax_class_id'),
					'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_hyperlocal_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
				);

				$method_data = array(
					'code'       => 'hyperlocal',
					'title'      => $this->config->get('shipping_hyperlocal_title'),
					'quote'      => $quote_data,
					'sort_order' => $this->config->get('shipping_hyperlocal_sort_order'),
					'error'      => false
				);
			}
		}

		return $method_data;
	}
}

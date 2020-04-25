<?php
class Hyperlocal {

	public function __construct($registry) {

		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		$this->load = $registry->get('load');
		$this->language = $registry->get('language');
	}

	/**
	 * [getAllSellersId method is used for getting id of all sellers]
	 * @return [type] [description]
	 */
	public function getAllSellersId() {
		$query = "SELECT DISTINCT seller_id FROM ".DB_PREFIX."seller_location LEFT JOIN ".DB_PREFIX."customerpartner_to_customer ON (seller_id = customer_id AND is_partner = 1 ) WHERE 1";
		$result = $this->db->query($query);
		return $result->rows;
	}

	/**
	 * [getLocationsBySeller method is used for getting all shipping location of seller.]
	 * @param  [type] $seller_id [customer-id of seller]
	 * @return [type]            [description]
	 */
	public function getLocationsBySeller($seller_id) {
		$query = "SELECT sl.*, CONCAT(c.firstname,' ',c.lastname) as name  FROM ".DB_PREFIX."seller_location sl LEFT JOIN ".DB_PREFIX."customer c ON (sl.seller_id = c.customer_id) WHERE sl.seller_id ='".$seller_id."'";
		$result = $this->db->query($query);

		return $result->rows;
	}

	/**
	 * [getTotalLocations function is used for get total no' of locations for seller.]
	 * @param  [int] $seller_id [this is seller_id.]
	 * @return [int]            [it returns total no' of records for a particular seller.]
	 */
	public function getTotalLocations($seller_id) {
		$query = "SELECT count(*) as total FROM ".DB_PREFIX."seller_location WHERE seller_id ='".$seller_id."'";
		$result = $this->db->query($query)->row;
		return $result['total'];
	}

	/**
	 * [userLocation Method is used for getting current location of current user.]
	 * @return [type] [description]
	 */
	public function userLocation() {
		$user_location = array();
		if (isset($this->session->data['loc']) && $this->session->data['loc'] && isset($this->session->data['lat']) && $this->session->data['lat'] && isset($this->session->data['lng']) && $this->session->data['lng']) {
			$user_location = array
			(
			'location' => $this->session->data['loc'],
			'longitude' => $this->session->data['lng'],
			'latitude' => $this->session->data['lat']
			);
		}
		return $user_location;
	}

	/**
	 * [getSellerList Method is used for getting all sellers that lies in the given radius on behalf of current customer's location.]
	 * @return [json] [It contains list of all sellers.]
	 */
	public function getSellerList() {

		$user_location = array();
		$sellerDetail  = array();
		$results       = array();
		$given_radius  = ( $this->config->get('module_hyperlocal_radius') ? $this->config->get('module_hyperlocal_radius') : 10 );

		$user_location = $this->userLocation();

		if (!$user_location) {
			return false;
		}

		$results = $this->getAllSellersId();

		// If location set by customer, then we calculate all distance between customer and seller.

			if($results) {
				foreach ($results as $result => $value) {

					$locations = array();
					$locations = $this->getLocationsBySeller($value['seller_id']);

					if($locations) {
						foreach ($locations as $location => $value) {

							if($given_radius != 0){
								    $distance_result = $this->getDistance($user_location['location'],$value['location'],'K');					    		
							    if($distance_result > $given_radius){
								  continue;
							    }
							}
							$sellerDetail[] = array(
								'seller_id'        => $value['seller_id'],
								'name'        => $value['name'],
								'location'  => $value['location'],
								'latitude'  => $value['latitude'],
								'longitude' => $value['longitude'],
								'distance'  => $distance_result
							);
						}

					}
				}

				if($sellerDetail) {
					return $sellerDetail;
				} else {
					return false;
				}


			}
	}

	/**
	 * [getDistance Method is used for calculate distance between two points in KM.]
	 * @param  [float] $latitude1  [current latitude.]
	 * @param  [float] $longitude1 [current longitude.]
	 * @param  [float] $latitude2  [seller latitude.]
	 * @param  [float] $longitude2 [seller longitude.]
	 * @param  string $unit       [represents KM. ]
	 * @return [float]            [it returns distance.]
	 */
	 public function getDistance($addressFrom, $addressTo, $unit = '') {
 		// Google API key
 		$apiKey = $this->config->get('module_hyperlocal_google_api_key');

 		// Change address format
 		$formattedAddrFrom    = str_replace(' ', '+', $addressFrom);
 		$formattedAddrTo     = str_replace(' ', '+', $addressTo);

 		// Geocoding API request with start address
 		$geocodeFrom = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false&key='.$apiKey);
		 $outputFrom = json_decode($geocodeFrom);

 		if(!empty($outputFrom->error_message)){
 				return $outputFrom->error_message;
 		}

 		// Geocoding API request with end address
		 $geocodeTo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false&key='.$apiKey);
 		$outputTo = json_decode($geocodeTo);
 		if(!empty($outputTo->error_message)){
 				return $outputTo->error_message;
 		}

 		// Get latitude and longitude from the geodata
 		$latitudeFrom    = $outputFrom->results[0]->geometry->location->lat;
 		$longitudeFrom    = $outputFrom->results[0]->geometry->location->lng;
 		$latitudeTo        = $outputTo->results[0]->geometry->location->lat;
 		$longitudeTo    = $outputTo->results[0]->geometry->location->lng;

 		// Calculate distance between latitude and longitude
 		$theta    = $longitudeFrom - $longitudeTo;
 		$dist    = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
 		$dist    = acos($dist);
 		$dist    = rad2deg($dist);
 		$miles    = $dist * 60 * 1.1515;
 		// Convert unit and return distance
 		$unit = strtoupper($unit);
 		if($unit == "K"){
 				return round($miles * 1.609344, 2);
 		}elseif($unit == "M"){
 				return round($miles * 1609.344, 2);
 		}else{
 				return round($miles, 2).' miles';
 		}
 	}

	public function validProducts($seller_id) {

		if ($seller_id) {
			$query = "SELECT product_id FROM ".DB_PREFIX."customerpartner_to_product WHERE customer_id ='".$seller_id."'";
			$result = $this->db->query($query)->rows;
		} else {
			$query = "SELECT product_id FROM ".DB_PREFIX."product WHERE product_id NOT IN(SELECT product_id FROM ".DB_PREFIX."customerpartner_to_product)";
			$result = $this->db->query($query)->rows;
		}

		if($result) {
			$products = array();
			foreach ($result as $key => $value) {
				$products[] = $value['product_id'];
			}
		}
		if (isset($products)) {
			return $products;
		}
		return false;
	}

	public function getAllValidProducts() {
        $product_ids = 0;
        if ($this->config->get('module_marketplace_status') && $this->config->get('module_hyperlocal_status')) {
          $sellers = $this->getSellerList();
          if($sellers){
            foreach ($sellers as $seller) {
	            $products = $this->validProducts($seller['seller_id']);
	            if ($products) {
	            	$product_ids .=',';
	            	$product_ids .=implode(',',$products);
	            }
	        }
          }
        }

        return $product_ids;
	}

	public function getAllValidSellers() {
        $customer_ids = '';
        if ($this->config->get('module_marketplace_status') && $this->config->get('module_hyperlocal_status')) {
          $sellers = $this->getSellerList();
          if($sellers){
            foreach ($sellers as $seller) {
              if ($customer_ids == '') {
                $customer_ids .= $seller['seller_id'];
              } else {
                $customer_ids .= ','.$seller['seller_id'];
              }
            }
          }
        }
        return $customer_ids;
	}

	public function getSellerIdByProduct($product_id) {
		$query = "SELECT DISTINCT customer_id FROM ".DB_PREFIX."customerpartner_to_product  WHERE product_id = ".(int)$product_id;
		$result = $this->db->query($query);
		return $result->row;
	}
}

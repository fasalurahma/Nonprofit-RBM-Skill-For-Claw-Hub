<?php
class ModelHyperlocalHyperlocal extends Model {
	/**
	 * [createTable Method is used for creating tables for database entry.]
	 * @return [type] [description]
	 */
	public function addTable() {

		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "seller_shipping_cost (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `seller_id` int(10) NOT NULL ,
            `range_to` varchar(100) NOT NULL ,
            `range_from` varchar(100) NOT NULL ,
            `price` float NOT NULL ,
            `weight_from` float NOT NULL ,
            `weight_to` float NOT NULL ,
            PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "wk_time_slots` (
															`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,

															`customer_id` varchar(10) NOT NULL ,
						  								`wk_hyperlocal_addon_sunday_stime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_sunday_ltime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_sunday_status` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_sunday_store_pickup` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_monday_stime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_monday_ltime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_monday_status` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_monday_store_pickup` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_tuesday_stime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_tuesday_ltime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_tuesday_status` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_tuesday_store_pickup` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_wednesday_stime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_wednesday_ltime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_wednesday_status` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_wednesday_store_pickup` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_thursday_stime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_thursday_ltime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_thursday_status` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_thursday_store_pickup` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_friday_stime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_friday_ltime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_friday_status` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_friday_store_pickup` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_saturday_stime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_saturday_ltime` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_saturday_status` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_saturday_store_pickup` varchar(50) NOT NULL ,
															`wk_hyperlocal_addon_status` varchar(50) NOT NULL ,
															PRIMARY KEY (`id`) ) DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
				") ;
						$this->load->model('localisation/language');
						$this->load->model('catalog/option');
						$language = $this->model_localisation_language->getLanguages();
						$option1 = array();
						foreach ($language as $key => $value) {
							$option1['option_description'][$value['language_id']]['name'] = 'Start Time';
						}
						$option1['type'] = 'time';
						$option1['sort_order'] = 0;
						$this->model_catalog_option->addOption($option1);
						$option2 = array();
						foreach ($language as $key => $value) {
							$option2['option_description'][$value['language_id']]['name'] = 'End Time';
						}
						$option2['type'] = 'time';
						$option2['sort_order'] = 0;
						$this->model_catalog_option->addOption($option2);
						$option3 = array();
						foreach ($language as $key => $value) {
							$option3['option_description'][$value['language_id']]['name'] = 'Delivery Day';
						}
						$option3['type'] = 'text';
						$option3['sort_order'] = 0;
						$this->model_catalog_option->addOption($option3);

		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "seller_location (id  INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,seller_id INT(11),location VARCHAR(1000),longitude DOUBLE,latitude DOUBLE)");

		$this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "hyperlocal_flatshipping (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`seller_id` int(11) NOT NULL,
			`amount` float,
			PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci") ;

	}
	public function dropTable() {
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "seller_shipping_cost");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "seller_location");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "hyperlocal_flatshipping");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wk_time_slots`");
	}
	public function addOptions() {
		$products = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product` WHERE product_id NOT IN (SELECT product_id FROM `" . DB_PREFIX . "customerpartner_to_product`)")->rows;
		$option1 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Start Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $option2 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='End Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $option3 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Delivery Day' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		foreach ($products as $key => $value) {
			if (isset($option1)) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option1['option_id']) . "'")->num_rows;
				if(!$query) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$this->db->escape($value['product_id']) . "', option_id = '" . (int)$this->db->escape($option1['option_id']) . "', value = '', required = '1'");
				}
			}
			if(isset($option2)) {
				 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option2['option_id']) . "'")->num_rows;
				 if(!$query) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$this->db->escape($value['product_id']) . "', option_id = '" . (int)$this->db->escape($option2['option_id']) . "', value = '', required = '1'");
			 }
			}
			if(isset($option3)) {
				 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option3['option_id']) . "'")->num_rows;
				 if(!$query) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$this->db->escape($value['product_id']) . "', option_id = '" . (int)$this->db->escape($option3['option_id']) . "', value = '', required = '1'");
			 }
			}
		}
	}
	public function deleteOptions() {
		$option1 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Start Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $option2 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='End Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		  $option3 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Delivery Day' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $products = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product`")->rows;
		 foreach ($products as $key => $value) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option1['option_id']) . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option2['option_id']) . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option3['option_id']) . "'");
		 }
		 $sellers = $this->db->query("SELECT * FROM `" . DB_PREFIX . "wk_time_slots`")->rows;
		 foreach ($sellers as $key => $seller) {
			 $this->db->query("UPDATE " . DB_PREFIX . "wk_time_slots SET wk_hyperlocal_addon_status=0 WHERE customer_id='" . $this->db->escape($seller['customer_id']) . "'");
		 }
	}

	public function addShipping($data) {

		$sql = $sqlchk = '';
		foreach ($data as $key => $value) {
			if($key!='price')
				$sqlchk .= $key . " = '" . $this->db->escape($value) . "' AND ";
			$sql .= $key . " = '" . $this->db->escape($value) . "' ,";
		}
		$sqlchk = substr($sqlchk,0,-5);
		$sql = substr($sql,0,-1);
		$getid = $this->db->query("SELECT id FROM " . DB_PREFIX . "seller_shipping_cost WHERE $sqlchk")->row;

		if($getid)
			$this->db->query("UPDATE " . DB_PREFIX . "seller_shipping_cost SET $sql WHERE id = '" . $getid['id'] . "'");
		else
			$this->db->query("INSERT INTO " . DB_PREFIX . "seller_shipping_cost SET $sql ");

		if($getid)
			return true;
		else
			return false;
	}
	public function saveTime($data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "wk_time_slots WHERE customer_id = 0");
	 $query = $this->db->query("INSERT INTO " . DB_PREFIX . "wk_time_slots VALUES('','0','" . $this->db->escape($data['wk_hyperlocal_addon_sunday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_sunday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_sunday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_sunday_store_pickup']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_monday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_monday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_monday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_monday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_tuesday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_tuesday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_tuesday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_tuesday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_wednesday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_wednesday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_wednesday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_wednesday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_thursday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_thursday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_thursday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_thursday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_friday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_friday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_friday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_friday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_saturday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_saturday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_saturday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_saturday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_status']) . "')");
	}
	public function getTime() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "wk_time_slots` WHERE customer_id = '0'");
		return $query->row;
	}

	public function viewtotal($data) {

		$sql ="SELECT CONCAT(c.firstname,' ',c.lastname) as name ,cs.* FROM " . DB_PREFIX . "seller_shipping_cost cs LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id=cs.seller_id) WHERE 1 ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND LCASE(CONCAT(c.firstname,' ',c.lastname)) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if (!empty($data['filter_country'])) {
			$sql .= " AND LCASE(cs.country_code) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_country'])) . "%'";
		}

		if (!empty($data['filter_range_to'])) {
			$sql .= " AND cs.range_to LIKE '" . $this->db->escape($data['filter_range_to']) . "%'";
		}

		if (!empty($data['filter_range_from'])) {
			$sql .= " AND cs.range_from LIKE '" . $this->db->escape($data['filter_range_from']) . "%'";
		}

		if (!empty($data['filter_price'])) {
			$sql .= " AND cs.price = '" . (float)$this->db->escape($data['filter_price']) . "'";
		}

		if (!empty($data['filter_weight_to'])) {
			$sql .= " AND cs.weight_to = '" . (float)$this->db->escape($data['filter_weight_to']) . "'";
		}

		if (!empty($data['filter_weight_from'])) {
			$sql .= " AND cs.weight_from = '" . (float)$this->db->escape($data['filter_weight_from']) . "'";
		}

		$sql .= " GROUP BY cs.id";

		$sort_data = array(
			'name',
			'cs.country_code',
			'cs.price',
			'cs.range_to',
			'cs.range_from',
			'cs.weight_to',
			'cs.weight_from',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY cs.id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$result=$this->db->query($sql);

		return $result->rows;
	}

	public function viewtotalentry($data) {

		$sql ="SELECT CONCAT(c.firstname,' ',c.lastname) as name ,cs.* FROM " . DB_PREFIX . "seller_shipping_cost cs LEFT JOIN " . DB_PREFIX . "customer c ON (c.customer_id=cs.seller_id) WHERE 1 ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND LCASE(CONCAT(c.firstname,' ',c.lastname)) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if (!empty($data['filter_country'])) {
			$sql .= " AND LCASE(cs.country_code) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_country'])) . "%'";
		}

		if (!empty($data['filter_range_to'])) {
			$sql .= " AND cs.range_to LIKE '" . $this->db->escape($data['filter_range_to']) . "%'";
		}

		if (!empty($data['filter_range_from'])) {
			$sql .= " AND cs.range_from LIKE '" . $this->db->escape($data['filter_range_from']) . "%'";
		}

		if (!empty($data['filter_price'])) {
			$sql .= " AND cs.price = '" . (float)$this->db->escape($data['filter_price']) . "'";
		}

		if (!empty($data['filter_weight_to'])) {
			$sql .= " AND cs.weight_to = '" . (float)$this->db->escape($data['filter_weight_to']) . "'";
		}

		if (!empty($data['filter_weight_from'])) {
			$sql .= " AND cs.weight_from = '" . (float)$this->db->escape($data['filter_weight_from']) . "'";
		}

		$result = $this->db->query($sql);

		return count($result->rows);
	}

	public function deleteentry($id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "seller_shipping_cost WHERE id='" . (int)$this->db->escape($id) . "'");
	}

	public function getLocation($data) {

		$sql = "SELECT sl.id,sl.seller_id,sl.location,CONCAT(cu.firstname,' ',cu.lastname) as seller_name FROM " . DB_PREFIX . "seller_location sl LEFT JOIN " . DB_PREFIX . "customerpartner_to_customer cc  ON sl.seller_id = cc.customer_id  LEFT JOIN " . DB_PREFIX . "customer cu ON sl.seller_id = cu.customer_id WHERE 1 ";

		$implode = array();

		if (!empty($data['filter_seller_name'])) {
			$implode[] = " AND CONCAT(cu.firstname, ' ', cu.lastname) LIKE '" . $this->db->escape($data['filter_seller_name']) . "%'";
		}

		if (!empty($data['filter_location'])) {
			$implode[] = " AND location LIKE '%" . $this->db->escape($data['filter_location']) . "%'";
		}

		if ($implode) {
			$sql .= implode($implode);
		}

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sl.id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$result = $this->db->query($sql)->rows;

		return $result;
	}

	public function getPartners() {
		$query = "SELECT cc.customer_id,CONCAT(firstname,' ',lastname) as seller_name FROM " . DB_PREFIX . "customerpartner_to_customer cc LEFT JOIN " . DB_PREFIX . "customer cu ON ( cc.customer_id = cu.customer_id AND is_partner = 1 ) WHERE 1 ";
		$result = $this->db->query($query)->rows;
		return $result;
	}

	/**
	 * [saveLocation Method is used for save location for sellers.]
	 * @param  [array] $data [it contains values for seller such as seller_id,location,longitude and latitude.]
	 * @return [type]       [description]
	 */
	public function saveLocation($data_seller) {

		if(isset($data_seller['seller'])) {

			foreach ($data_seller['seller'] as $key => $data) {

				$count = $this->db->query("SELECT count(*) as count FROM " . DB_PREFIX . "seller_location WHERE seller_id = " . $data['seller_id'] . " AND location = '" . $this->db->escape($data['location']) . "'")->row['count'];
				if ($count) {
					$this->db->query("UPDATE " . DB_PREFIX . "seller_location SET `location` ='" . $this->db->escape($data['location']) . "' , `longitude` =" . (double)$data['longitude'] . " , `latitude` =" . (double)$data['latitude'] . " WHERE seller_id = " . (int)$data['seller_id'] . " AND location = '" . $this->db->escape($data['location']) . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "seller_location SET `seller_id` =" . (int)$data['seller_id'] . " , `location` ='" . $this->db->escape($data['location']) . "' , `longitude` =" . (double)$data['longitude'] . " , `latitude` =" . (double)$data['latitude'] . "");
				}

			}
		}
	}

	public function deleteLocation($location_id) {
		$query = "DELETE FROM " . DB_PREFIX . "seller_location WHERE id='" . (int)$location_id . "'";
		$this->db->query($query);
	}

	public function getLocationValue($location_id) {
		$query = "SELECT sl.id,seller_id,location,longitude,latitude,cu.firstname,cu.lastname FROM " . DB_PREFIX . "seller_location sl LEFT JOIN " . DB_PREFIX . "customerpartner_to_customer cc  ON sl.seller_id = cc.customer_id  LEFT JOIN " . DB_PREFIX . "customer cu ON sl.seller_id = cu.customer_id WHERE sl.id='" . $this->db->escape($location_id) . "'";
		$result = $this->db->query($query)->rows;
		return $result;
	}
}
?>

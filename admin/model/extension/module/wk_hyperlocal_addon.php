<?php
class ModelExtensionModuleWkhyperlocaladdon extends Model {

	public function createTable() {

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
	}
	public function addOptions() {
		$products = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product` WHERE product_id NOT IN (SELECT product_id FROM `" . DB_PREFIX . "customerpartner_to_product`)")->rows;
		$option1 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Start Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $option2 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='End Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		foreach ($products as $key => $value) {
			if (isset($option1)) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option1['option_id']) . "'")->num_rows;
				if(!$query) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$value['product_id'] . "', option_id = '" . (int)$this->db->escape($option1['option_id']) . "', value = '', required = '1'");
				}
			}
			if(isset($option2)) {
				 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option2['option_id']) . "'")->num_rows;
				 if(!$query) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$this->db->escape($value['product_id']) . "', option_id = '" . (int)$this->db->escape($option2['option_id']) . "', value = '', required = '1'");
			 }
			}
		}
	}
	public function deleteOptions() {
		$option1 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Start Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $option2 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='End Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $products = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product`")->rows;
		 foreach ($products as $key => $value) {
		 	$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option1['option_id']) . "'");
			$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_id='" . $this->db->escape($value['product_id']) . "' AND option_id='" . $this->db->escape($option2['option_id']) . "'");
		 }
		 $sellers = $this->db->query("SELECT * FROM `" . DB_PREFIX . "wk_time_slots`")->rows;
		 foreach ($sellers as $key => $seller) {
       $this->db->query("UPDATE " . DB_PREFIX . "wk_time_slots SET wk_hyperlocal_addon_status=0 WHERE customer_id='" . $this->db->escape($seller['customer_id']) . "'");
		 }
	}
  public function dropTable() {
    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "wk_time_slots`");
  }
}

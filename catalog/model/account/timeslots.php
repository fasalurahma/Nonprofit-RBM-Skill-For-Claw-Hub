<?php
class ModelAccountTimeslots extends Model {
	public function addSlots($data = array()) {
    $this->db->query("DELETE FROM " . DB_PREFIX . "wk_time_slots WHERE customer_id = '" . (int)$this->customer->getId() . "'");
   $query = $this->db->query("INSERT INTO " . DB_PREFIX . "wk_time_slots VALUES('','" . (int)$this->customer->getId() . "','" . $this->db->escape($data['wk_hyperlocal_addon_sunday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_sunday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_sunday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_sunday_store_pickup']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_monday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_monday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_monday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_monday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_tuesday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_tuesday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_tuesday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_tuesday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_wednesday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_wednesday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_wednesday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_wednesday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_thursday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_thursday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_thursday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_thursday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_friday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_friday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_friday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_friday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_saturday_stime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_saturday_ltime']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_saturday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_saturday_status']) . "','" . $this->db->escape($data['wk_hyperlocal_addon_status']) . "')");
	}
	public function assignedOptions() {
	   $this->load->model('account/customerpartner');
		 $products = $this->model_account_customerpartner->getallsellerproducts();
		 $option1 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Start Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		  $option2 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='End Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
			$option3 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Delivery Day' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 foreach ($products as $key => $value) {
			 if (isset($option1)) {
				 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id='" . $value['product_id'] . "' AND option_id='" . $option1['option_id'] . "'")->num_rows;
				 if(!$query){
					 $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$value['product_id'] . "', option_id = '" . (int)$option1['option_id'] . "', value = '', required = '1'");
				 }
			 }
			 if(isset($option2)){
				  $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id='" . $value['product_id'] . "' AND option_id='" . $option2['option_id'] . "'")->num_rows;
					if(!$query){
				 $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$value['product_id'] . "', option_id = '" . (int)$option2['option_id'] . "', value = '', required = '1'");
			  }
			 }
			 if(isset($option3)){
 				 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id='" . $value['product_id'] . "' AND option_id='" . $option3['option_id'] . "'")->num_rows;
 				 if(!$query){
 				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$value['product_id'] . "', option_id = '" . (int)$option3['option_id'] . "', value = '', required = '1'");
 			 }
 			}
		 }
	}

	public function deleteOptions(){
		$this->load->model('account/customerpartner');
		$products = $this->model_account_customerpartner->getallsellerproducts();
		$option1 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Start Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $option2 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='End Time' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		 $option3 = $this->db->query("SELECT option_id FROM `" . DB_PREFIX . "option_description` WHERE name='Delivery Day' AND language_id='" . (int)$this->config->get('config_language_id') . "'")->row;
		foreach ($products as $key => $value) {
			if (isset($option1)) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$value['product_id'] . "' AND option_id = '" . (int)$option1['option_id'] . "'");
			}
			if(isset($option2)){
				 $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$value['product_id'] . "' AND option_id = '" . (int)$option2['option_id'] . "'");
			}
			if(isset($option3)){
				  $this->db->query("DELETE FROM `". DB_PREFIX . "product_option` WHERE product_id='" . $value['product_id'] . "' AND option_id='" . $option3['option_id'] . "'");
			}
		}
	}

	public function getslots() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "wk_time_slots` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
		return $query->row;
	}
}

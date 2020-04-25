<?php
class ModelCustomerpartnerSellerlocation extends Model {
	/**
	 * [saveLocation Method is used for save location for sellers.]
	 * @param  [array] $data [it contains values for seller such as seller_id,location,longitude and latitude.]
	 * @return [type]       [description]
	 */
	public function saveLocation($data) {
		
		$count = $this->db->query("SELECT count(*) as count FROM " . DB_PREFIX . "seller_location WHERE seller_id = " . $this->customer->getId() . " AND location ='" . $this->db->escape($data['location']) . "' ")->row['count'];
		if ($count) {
			$this->db->query("UPDATE " . DB_PREFIX . "seller_location SET `location` ='" . $this->db->escape($data['location']) . "' , `longitude` =" . (double)$data['longitude'] . " , `latitude` =" . (double)$data['latitude'] . " WHERE seller_id = " . (int)$this->customer->getId() . " AND location ='" . $this->db->escape($data['location']) . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "seller_location SET `seller_id` =" . (int)$this->customer->getId() . " , `location` ='" . $this->db->escape($data['location']) . "' , `longitude` =" . (double)$data['longitude'] . " , `latitude` =" . (double)$data['latitude'] . "");
		}					
	}

	/**
	 * [getLocation Method is used for getting location of sellers.]
	 * @return [type] [description]
	 */
	public function getLocation($data = array()) {
		
		$sql = "SELECT * FROM " . DB_PREFIX . "seller_location WHERE seller_id = " . $this->customer->getId();
		if (!empty($data['filter_location'])) {
			$sql .= " AND location LIKE '" . $this->db->escape(utf8_strtolower($data['filter_location'])) . "%'";
		}
		
		if (!empty($data['filter_latitude'])) {
			$sql .= " AND latitude LIKE '" . $this->db->escape($data['filter_latitude']) . "%'";
		}

		if (!empty($data['filter_longitude'])) {
			$sql .= " AND longitude LIKE '" . $this->db->escape($data['filter_longitude']) . "%'";
		}
		$result = $this->db->query($sql)->rows; 
		
		return $result;
	}

	public function deleteLocation($location_id) {
		$query = "DELETE FROM " . DB_PREFIX . "seller_location WHERE id='" . (int)$location_id . "'";
		$this->db->query($query);
	}
}
?>
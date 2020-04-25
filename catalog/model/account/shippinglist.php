<?php
class ModelAccountShippinglist extends Model {

	public function addShipping($data) {
		$sql = $sqlchk = '';
		foreach ($data as $key => $value) {
			if($key!='price')
				$sqlchk .= $key . " = '" . $this->db->escape($value) . "' AND ";
			$sql .= $key . " = '" . $this->db->escape($value) . "' ,";
		}
		// $sqlchk = substr($sql,0,-5);
		$sql = substr($sql,0,-1);		
		$get_Id = $this->db->query("SELECT id FROM " . DB_PREFIX . "seller_shipping_cost WHERE $sqlchk seller_id = '" . $this->customer->getId() . "'")->row;

		if($get_Id)
			$this->db->query("UPDATE " . DB_PREFIX . "seller_shipping_cost SET $sql WHERE id = '" . $get_Id['id'] . "'");
		else
			$this->db->query("INSERT INTO " .DB_PREFIX ."seller_shipping_cost SET $sql , seller_id = '" . $this->customer->getId() . "'");

		if($get_Id)
			return true;
		else
			return false;
	}

	public function viewdata($data) {

		$sql ="SELECT cs.* FROM " . DB_PREFIX . "seller_shipping_cost cs WHERE seller_id ='" . $this->customer->getId() . "' ";	
	
		if (!empty($data['filter_country'])) {
			$sql .= " AND LCASE(cs.country_code) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_country'])) . "%'";
		}
		
		if (!empty($data['filter_zip_to'])) {
			$sql .= " AND cs.zip_to LIKE '" . $this->db->escape($data['filter_zip_to']) . "%'";
		}

		if (!empty($data['filter_zip_from'])) {
			$sql .= " AND cs.zip_from LIKE '" . $this->db->escape($data['filter_zip_from']) . "%'";
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
			'cs.country_code',
			'cs.price',
			'cs.zip_to',
			'cs.zip_from',
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

		$sql ="SELECT cs.* FROM " . DB_PREFIX . "seller_shipping_cost cs WHERE seller_id ='" . $this->customer->getId() . "' ";	
	
		if (!empty($data['filter_country'])) {
			$sql .= " AND LCASE(cs.country_code) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_country'])) . "%'";
		}
		
		if (!empty($data['filter_zip_to'])) {
			$sql .= " AND cs.zip_to LIKE '" . $this->db->escape($data['filter_zip_to']) . "%'";
		}

		if (!empty($data['filter_zip_from'])) {
			$sql .= " AND cs.zip_from LIKE '" . $this->db->escape($data['filter_zip_from']) . "%'";
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
		$this->db->query("DELETE FROM " . DB_PREFIX . "seller_shipping_cost WHERE id='" . $this->db->escape($id) . "' AND seller_id ='" . $this->customer->getId() . "'");		
	}

	public function addFlatShipping($id,$amount) {

		$sql = $this->db->query("SELECT id FROM " . DB_PREFIX . "hyperlocal_flatshipping WHERE seller_id = '$id'");
		if(isset($sql->row['id'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "hyperlocal_flatshipping SET amount = '" . (float)$this->db->escape($amount) . "' WHERE id = '" . $sql->row['id'] . "'");
		}else{
			$this->db->query("INSERT INTO " . DB_PREFIX . "hyperlocal_flatshipping SET seller_id  = '$id',amount = '" . (float)$this->db->escape($amount) . "'");
		}
		
	}

	public function getFlatShipping($id) {
		
		$sql = $this->db->query("SELECT amount FROM " . DB_PREFIX . "hyperlocal_flatshipping WHERE seller_id = '$id'");
		
		return $sql->row;
		
	}

}
?>
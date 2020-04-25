<?php
class ModelMpordersplitMpordersplit extends Model {
	/**
	 * [saveCart cart item saved for later restortion]
	 * @return [type] [description]
	 */
	public function saveCart() {

		$this->db->query("DELETE FROM " . DB_PREFIX . "mpordersplit_cartbackup WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 HOUR)");

		$results = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		if ($results->num_rows > 0) {
			$checkarray = array();
			$check = 0;
			foreach ($results->rows as $key => $result) {
				$seller = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id=" . (INT)$result['product_id']);

				if ($seller->num_rows==1) {
					$seller_id = (INT)$seller->row['customer_id'];
					$check++;
				} else {
					$seller_id = "admin";
				}

				if (!in_array($seller_id,$checkarray)) {
					$checkarray[] = $seller_id;
				}

				$this->db->query("DELETE FROM " . DB_PREFIX . "mpordersplit_cartbackup WHERE cart_id = '" . (int)$result['cart_id'] . "'");

				$this->db->query("INSERT INTO " . DB_PREFIX . "mpordersplit_cartbackup SET `seller_id` = '" . (int)$seller_id . "', customer_id = '" . (int)$result['customer_id'] . "',session_id = '" . $this->db->escape($result['session_id']) . "',product_id = '" . (int)$result['product_id'] . "',recurring_id = '" . (int)$result['recurring_id'] . "',`option` = '" . $this->db->escape($result['option']) . "',quantity = '" . (int)$result['quantity'] . "',date_added = '" . $this->db->escape($result['date_added']) . "',cart_id = '" . (int)$result['cart_id'] . "'");

			}
			$checkarray['admincheck'] = $check;
			if ($check==0) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "mpordersplit_cartbackup WHERE `seller_id` = '" . (int)$seller_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
			}
			return $checkarray;
		}

	}

		/**
		 * [restoreCart restoring cart to previous added items]
		 * @return [type] [description]
		 */
		public function restoreCart () {

			$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mpordersplit_cartbackup` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'")->rows;

			foreach ($results as $key => $result) {

				$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$result['cart_id'] . "'");

				$this->db->query("INSERT INTO " . DB_PREFIX . "cart SET customer_id = '" . (int)$result['customer_id'] . "', session_id = '" . $this->db->escape($result['session_id']) . "',	product_id = '" . (int)$result['product_id'] . "', recurring_id = '" . (int)$result['recurring_id'] . "', `option` = '" . $this->db->escape($result['option']) . "', quantity = '" . (int)$result['quantity'] . "', date_added = '" . $this->db->escape($result['date_added']) . "',cart_id = '" . (int)$result['cart_id'] . "'");

			}

			$this->db->query("DELETE FROM `" . DB_PREFIX . "mpordersplit_cartbackup` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");

		}

		/**
		 * [sellercartrestore restoring sub seller cart in cart]
		 * @param  [type] $seller_id [description]
		 * @return [type]            [description]
		 */
		public function sellercartrestore($seller_id) {

			$results = $this->db->query("SELECT * FROM " . DB_PREFIX . "mpordersplit_cartbackup WHERE `seller_id` = '" . (int)$seller_id . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'")->rows;


			foreach ($results as $key => $result) {

				$this->db->query("DELETE FROM " . DB_PREFIX . "cart WHERE cart_id = '" . (int)$result['cart_id'] . "'");

				$this->db->query("INSERT INTO " . DB_PREFIX . "cart SET customer_id = '" . (int)$result['customer_id'] . "', session_id = '" . $this->db->escape($result['session_id']) . "',	product_id = '" . (int)$result['product_id'] . "', recurring_id = '" . (int)$result['recurring_id'] . "', `option` = '" . $this->db->escape($result['option']) . "', quantity = '" . (int)$result['quantity'] . "', date_added = '" . $this->db->escape($result['date_added']) . "',cart_id = '" . (int)$result['cart_id'] . "'");

			}

		}

	/**
	 * [saveSuborder save suborder and their shipping]
	 * @return [type] [description]
	 */
	public function saveSuborder() {

		$subcart = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mpordersplit_suborder` WHERE order_id = '" . (int)$this->session->data['wksplitorderarray']['main_order_id'] . "'");

		if ($subcart) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "mpordersplit_suborder` WHERE order_id = '" . (int)$this->session->data['wksplitorderarray']['main_order_id'] . "'");
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "mpordersplit_suborder` SET order_id = '" . (int)$this->session->data['wksplitorderarray']['main_order_id'] . "', suborder_ids = '" . $this->db->escape(json_encode($this->session->data['wksplitorderarray']['orderidarray'])) . "', suborder_shipping = '" . $this->db->escape(json_encode($this->session->data['wksplitorderarray']['shippingarray'])) . "'");
	}
	/**
	 * [restoreSuborder restore suborder and their shipping]
	 * @param  [type] $order_id [description]
	 * @return [type]           [description]
	 */
	public function restoreSuborder($order_id) {
			$subcart = $this->db->query("SELECT * FROM `" . DB_PREFIX . "mpordersplit_suborder` WHERE order_id = '" . (int)$order_id . "'");
			if ($subcart->num_rows==0) {
				return FALSE;
			}
            $this->session->data['wksplitorderarray']['main_order_id'] = $subcart->row['order_id'];
            $this->session->data['wksplitorderarray']['orderidarray'] = json_decode(utf8_encode($subcart->row['suborder_ids']),TRUE);
            $this->session->data['wksplitorderarray']['shippingarray'] = json_decode(utf8_encode($subcart->row['suborder_shipping']),TRUE);
            $this->db->query("DELETE FROM `" . DB_PREFIX . "mpordersplit_suborder` WHERE `order_id`='" . (int)$order_id . "'");
            return TRUE;
	}

	public function checkSellerProducts() {
		$seller = array();
		$cart_products = $this->cart->getProducts();

		foreach ($cart_products as $key => $product) {
			$results = $this->db->query("SELECT DISTINCT customer_id FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . (int)$product['product_id'] . "'")->row;

			if(empty($results) && !in_array(0,$seller)) {
				$seller[] = 0;
			}

			if (!empty($results) && !in_array($results['customer_id'],$seller)) {
				$seller[] = $results['customer_id'];
			}
		}
		return $seller;
	}

}

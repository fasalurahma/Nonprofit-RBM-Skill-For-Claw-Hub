<?php
/**
* @version [Product Version 1.0.0.0.]
* @category Webkul
* @package Opencart MP OrderSplit
* @author [Webkul] <[<http://webkul.com/>:smirk:>;
* @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/
class ModelMpordersplitMpordersplit extends Model {

	public function uninstallModule() {
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "mpordersplit_cartbackup");
	}

	public function installModule() {
		$sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpordersplit_cartbackup` (
			`id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`cart_id` INT(11) UNSIGNED,
			`seller_id` INT(11),
			`customer_id` INT(11),
			`session_id` VARCHAR(32),
			`product_id` INT(11),
			`recurring_id` INT(11),
			`option` text,
			`quantity` INT(5),
			`date_added` DATETIME
		)";
		$this->db->query($sql);

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mpordersplit_suborder` (
			`id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			`order_id` INT(11),
			`suborder_ids` text,
			`suborder_shipping` text
			)");
	}

}

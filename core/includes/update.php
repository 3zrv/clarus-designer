<?php
/**
*
*	(p) package: lumise
*	(c) author:	King-Theme
*	(i) website: https://www.lumise.com
*
*/

if (!defined('LUMISE')) {
	header('HTTP/1.0 403 Forbidden');
	die();
}

class lumise_update extends lumise_lib {
	
	protected $current;
	protected $api_url;
	
	public function __construct() {
		
		global $lumise;
		
		if (!$lumise->dbready)
			return;
			
		$this->main = $lumise;
		
		$current = $lumise->db->rawQuery("SELECT `value` FROM `{$lumise->db->prefix}settings` WHERE `key`='current_version'");
		
		if (count($current) === 0)
			$current = '1.3';
		else $current = $current[0]['value'];
		
		if ($current == '{{{version}}}') {
			$this->main->set_option('current_version', LUMISE);
			return;
		}
		
		$scheme = (
				(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 
				$_SERVER['SERVER_PORT'] == 443
			) ? 'https' : 'http';
		$this->api_url = $scheme.'://services.lumise.com/';
		
		if ($current != LUMISE) {
			$this->run_updater();
			$this->main->set_option('current_version', LUMISE);
		}

	}
	
	public function check() {
		
		$curDate = date_default_timezone_get();
		date_default_timezone_set("Asia/Bangkok");
		$check = $this->main->lib->remote_connect($this->api_url.'updates/lumise.xml?nonce='.date('dH'));
		date_default_timezone_set($curDate);
		
		$check = @simplexml_load_string($check);
		
		if (!is_object($check) || !isset($check->{$this->main->connector->platform}))
			return null;
			
		$update = $check->{$this->main->connector->platform};
		
		$data = array(
			"time" => time(),
			"version" => (string)$update->version,
			"date" => (string)$update->date,
		);
		
		$this->main->set_option('last_check_update', json_encode($data));
		
		$data['status'] = 1;
		
		return $data;
		
	}
	
	protected function run_updater() {
		/*
		*	Call this when a new version is installed	
		*	$this->main = global $lumise
		*/
		
		/*
		* Version 1.6
		* add `active` to table categories
		*/
		
		if (version_compare(LUMISE, '1.4') >=0 ){
			$sql = "SHOW COLUMNS FROM `{$this->main->db->prefix}categories` LIKE 'active'";
			$columns = $this->main->db->rawQuery($sql);
			if(count($columns) == 0){
				$sql_active = "ALTER TABLE `{$this->main->db->prefix}categories` ADD `active` INT(1) NOT NULL DEFAULT '1' AFTER `order`";
				$this->main->db->rawQuery($sql_active);
			}
		}
		
		if (version_compare(LUMISE, '1.5') >=0 ){
			$sql_active = "ALTER TABLE `{$this->main->db->prefix}products` CHANGE `color` `color` TEXT NOT NULL";
			$this->main->db->rawQuery($sql_active);
			$sql_active = "ALTER TABLE `{$this->main->db->prefix}products` CHANGE `printings` `printings` TEXT NOT NULL";
			$this->main->db->rawQuery($sql_active);
		}
		
		if (version_compare(LUMISE, '1.7') >=0 ){
			
			$sql = "SHOW COLUMNS FROM `{$this->main->db->prefix}fonts` LIKE 'upload_ttf'";
			$columns = $this->main->db->rawQuery($sql);
            
			if(count($columns) == 0){
				$sql_active = "ALTER TABLE `{$this->main->db->prefix}fonts` ADD `upload_ttf` TEXT NOT NULL AFTER `upload`";
				$this->main->db->rawQuery($sql_active);
			}
		}
        
		if (version_compare(LUMISE, '1.7.1') >=0 ){
			
			$this->upgrade_1_7();
			
			$sql = "SHOW COLUMNS FROM `{$this->main->db->prefix}products` LIKE 'variations';";
			$columns = $this->main->db->rawQuery($sql);
			if(count($columns) == 0){
				$sql_active = "ALTER TABLE `{$this->main->db->prefix}products` ADD `variations` TEXT NOT NULL AFTER `stages`";
				$this->main->db->rawQuery($sql_active);
			}
			
			// do the convert old data
			// 1. convert colors to attribute
			// 2. convert all old attribute structure to new structure
			// 3. convert stages
			
			$sql = "SHOW COLUMNS FROM `{$this->main->db->prefix}products` LIKE 'orientation';";
			$columns = $this->main->db->rawQuery($sql);
			if(count($columns) > 0){
				$this->main->db->rawQuery("ALTER TABLE `{$this->main->db->prefix}products` DROP `orientation`;");
				$this->main->db->rawQuery("ALTER TABLE `{$this->main->db->prefix}products` DROP `min_qty`;");
				$this->main->db->rawQuery("ALTER TABLE `{$this->main->db->prefix}products` DROP `max_qty`;");
				$this->main->db->rawQuery("ALTER TABLE `{$this->main->db->prefix}products` DROP `size`;");
				$this->main->db->rawQuery("ALTER TABLE `{$this->main->db->prefix}products` DROP `change_color`;");
				$this->main->db->rawQuery("ALTER TABLE `{$this->main->db->prefix}products` DROP `color`;");
			}
			
		}
        
		if (version_compare(LUMISE, '1.7.3') >=0 ){
			$this->upgrade_1_7_3();
		}
		
		if (version_compare(LUMISE, '1.7.4') >=0 ) {
			
			$tables = $this->main->db->rawQuery("SHOW TABLES LIKE '{$this->main->db->prefix}sessions'");
			
			if (count($tables) === 0) {
			
				$this->main->db->rawQuery(
					"CREATE TABLE IF NOT EXISTS `{$this->main->db->prefix}sessions` (
					  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					  `name` varchar(255) NOT NULL,
					  `value` varchar(255) NOT NULL,
					  `expires` int(11) NOT NULL,
					  `session_id` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB AUTO_INCREMENT=1 CHARSET=utf8mb4"
				);
			}
			
			$sql = "SHOW COLUMNS FROM `{$this->main->db->prefix}order_products` LIKE 'cart_id';";
			$columns = $this->main->db->rawQuery($sql);
			if(count($columns) == 0){
				$sql_active = "ALTER TABLE `{$this->main->db->prefix}order_products` ADD `cart_id` VARCHAR(255) NOT NULL DEFAULT '' AFTER `product_id`";
				$this->main->db->rawQuery($sql_active);
			}
			
		}
		
		if (version_compare(LUMISE, '1.7.5') >=0 ) {
			$this->upgrade_1_7_5();
		}

		if (version_compare(LUMISE, '1.9.2') >=0 ) {
			$sql = "SHOW COLUMNS FROM `{$this->main->db->prefix}fonts` LIKE 'name_desc'";
			$columns = $this->main->db->rawQuery($sql);
			if(count($columns) == 0){
				$sql_active = "ALTER TABLE `{$this->main->db->prefix}fonts` ADD `name_desc` varchar(255) NOT NULL DEFAULT '' AFTER `upload`";
				$this->main->db->rawQuery($sql_active);
			}
		}

		if (version_compare(LUMISE, '1.9.3') >=0 ) {
			$sql = "SHOW COLUMNS FROM `{$this->main->db->prefix}products` LIKE 'active_description'";
			$columns = $this->main->db->rawQuery($sql);
			if(count($columns) == 0){
				$sql_active = "ALTER TABLE `{$this->main->db->prefix}products` ADD `active_description` INT(1) NOT NULL DEFAULT '0' AFTER `description`";
				$this->main->db->rawQuery($sql_active);
			}
		}
		
		
		/*
		*	Create subfolder upload	
		*/
		
		$this->main->check_upload();
		
	}
	
	public function upgrade_1_7() {
		
		$products = $this->main->db->rawQuery("SELECT * FROM `{$this->main->db->prefix}products`");
		
		if (count($products) > 0) {
			
			foreach ($products as $product) {
			
				if (isset($product['color'])) {
					
					$color = explode(':', $product['color']);
					$color = isset($color[1]) ? explode(',', $color[1]) : explode(',', $color[0]);
					
					$attributes = $this->main->lib->dejson($product['attributes']);
					$new_attributes = array();
					$stages = $this->main->lib->dejson($product['stages']);
					
					if (isset($stages->stages))
						$stages = $stages->stages;
					
					if (isset($stages->colors))
						unset($stages->colors);
						
					if (!empty($product['color'])) {
						$id = $this->main->generate_id(4);
						$new_attributes[$id] = array(
							"id" => $id,
							"name" => "Product color",
							"type" => "product_color",
							"use_variation" => false,
							"values" => implode("\n", $color)
						);
					}
					
					foreach ($attributes as $attribute) {
						$id = $this->main->generate_id(4);
						$values = array();
						if (isset($attribute->options)) {
							foreach ($attribute->options as $op) {
								array_push($values, $op->title);
							}
						}
						if (isset($attribute->title) && isset($attribute->type) && count($values) > 0) {
							if ($attribute->type == 'size')
								$attribute->type = 'select';
							$new_attributes[$id] = array(
								"id" => $id,
								"name" => $attribute->title,
								"type" => $attribute->type,
								"use_variation" => false,
								"values" => implode("\n", $values)
							);
						}
					}
					
					$this->main->lib->edit_row( 
						$product['id'], 
						array(
							"attributes" => $this->main->lib->enjson($new_attributes),
							"stages" => $this->main->lib->enjson($stages),
						), 
						'products' 
					);
					
				}
			}
		}
		
	}
	
	public function upgrade_1_7_3() {
		
		$sql = "SHOW COLUMNS FROM `{$this->main->db->prefix}order_products` LIKE 'print_files';";
		$columns = $this->main->db->rawQuery($sql);
		if(count($columns) == 0){
			$sql_active = "ALTER TABLE `{$this->main->db->prefix}order_products` ADD `print_files` TEXT NOT NULL AFTER `screenshots`;";
			$this->main->db->rawQuery($sql_active);
		}
		
		$date = date("Y-m-d").' '.date("H:i:s");
		
		$tbs = array(
			'bugs' => array(
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `content` `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"SET `lumise` = '1' WHERE `lumise` IS NULL",
				"CHANGE `lumise` `lumise` INT(1) NOT NULL DEFAULT 1",
				"CHANGE `status` `status` VARCHAR(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open'",
				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			), 
			'categories' => array(
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `slug` `slug` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"SET `upload` = '' WHERE `upload` IS NULL",
				"CHANGE `upload` `upload` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"SET `thumbnail_url` = '' WHERE `thumbnail_url` IS NULL",
				"CHANGE `thumbnail_url` `thumbnail_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"SET `parent` = 0 WHERE `parent` IS NULL",
				"CHANGE `parent` `parent` INT(11) NOT NULL DEFAULT 0",
				"SET `type` = 'cliparts' WHERE `type` IS NULL",
				"CHANGE `type` `type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'cliparts'",
				"SET `order` = 0 WHERE `order` IS NULL",
				"CHANGE `order` `order` INT(11) NOT NULL DEFAULT 0",
				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			), 
			'categories_reference' => array(
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"SET `category_id` = 0 WHERE `category_id` IS NULL",
				"SET `item_id` = 0 WHERE `item_id` IS NULL",
				"SET `type` = '' WHERE `type` IS NULL",
				"CHANGE `category_id` `category_id` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `item_id` `item_id` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `type` `type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''"
			), 
			'cliparts' => array(
				
				
				"SET `name` = '' WHERE `name` IS NULL",
				"SET `upload` = '' WHERE `upload` IS NULL",
				"SET `thumbnail_url` = '' WHERE `thumbnail_url` IS NULL",
				"SET `price` = 0 WHERE `price` IS NULL",
				"SET `featured` = 0 WHERE `featured` IS NULL",
				"SET `active` = 1 WHERE `active` IS NULL",
				"SET `order` = 0 WHERE `order` IS NULL",
				"SET `use_count` = 0 WHERE `use_count` IS NULL",
				"SET `tags` = '' WHERE `tags` IS NULL",
				
				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `upload` `upload` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `thumbnail_url` `thumbnail_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `price` `price` FLOAT NOT NULL DEFAULT 0",
				"CHANGE `featured` `featured` INT(1) NOT NULL DEFAULT 0",
				"CHANGE `active` `active` INT(1) NOT NULL DEFAULT 1",
				"CHANGE `order` `order` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `use_count` `use_count` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `tags` `tags` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			), 
			'designs' => array(
				
				"SET `name` = '' WHERE `name` IS NULL",
				"SET `uid` = '' WHERE `uid` IS NULL",
				"SET `aid` = '' WHERE `aid` IS NULL",
				"SET `pid` = '' WHERE `pid` IS NULL",
				"SET `screenshots` = '' WHERE `screenshots` IS NULL",
				"SET `categories` = '' WHERE `categories` IS NULL",
				"SET `tags` = '' WHERE `tags` IS NULL",
				"SET `data` = '' WHERE `data` IS NULL",
				"SET `data_sharing` = '' WHERE `data_sharing` IS NULL",
				"SET `share_token` = 0 WHERE `share_token` IS NULL",
				"SET `sharing` = 0 WHERE `sharing` IS NULL",
				"SET `active` = 1 WHERE `active` IS NULL",
				
				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `uid` `uid` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `aid` `pid` VARCHAR(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `pid` `pid` VARCHAR(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `screenshots` `screenshots` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `categories` `categories` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `tags` `tags` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `data` `data` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `data_sharing` `data_sharing` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `share_token` `share_token` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `sharing` `sharing` INT(1) NOT NULL DEFAULT 0",
				"CHANGE `active` `active` INT(1) NOT NULL DEFAULT '1'",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'fonts' => array(
				
				"SET `name` = '' WHERE `name` IS NULL",
				"SET `upload` = '' WHERE `upload` IS NULL",
				"SET `active` = 1 WHERE `active` IS NULL",
				
				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `upload` `upload` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `active` `active` INT(1) NOT NULL DEFAULT '1'",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'guests' => array(
				
				"SET `name` = '' WHERE `name` IS NULL",
				"SET `email` = '' WHERE `email` IS NULL",
				"SET `address` = '' WHERE `address` IS NULL",
				"SET `zipcode` = '' WHERE `zipcode` IS NULL",
				"SET `city` = '' WHERE `city` IS NULL",
				"SET `country` = '' WHERE `country` IS NULL",
				"SET `phone` = '' WHERE `phone` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `address` `address` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `zipcode` `zipcode` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `city` `city` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `country` `country` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `phone` `phone` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'languages' => array(
				
				"SET `text` = '' WHERE `text` IS NULL",
				"SET `original_text` = '' WHERE `original_text` IS NULL",
				"SET `lang` = '' WHERE `lang` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `text` `text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `original_text` `original_text` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `lang` `lang` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'orders' => array(
				
				"SET `total` = 0 WHERE `total` IS NULL",
				"SET `currency` = '' WHERE `currency` IS NULL",
				"SET `payment` = '' WHERE `payment` IS NULL",
				"SET `txn_id` = '' WHERE `txn_id` IS NULL",
				"SET `user_id` = 0 WHERE `user_id` IS NULL",
				"SET `status` = 'pending' WHERE `status` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `total` `total` FLOAT NOT NULL DEFAULT 0",
				"CHANGE `currency` `currency` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `payment` `payment` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `txn_id` `txn_id` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `user_id` `user_id` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `status` `status` VARCHAR(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending'",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'order_products' => array(
				
				
				"SET `order_id` = 0 WHERE `order_id` IS NULL",
				"SET `product_id` = 0 WHERE `product_id` IS NULL",
				"SET `product_base` = 0 WHERE `product_base` IS NULL",
				"SET `product_price` = 0 WHERE `product_price` IS NULL",
				"SET `qty` = 0 WHERE `qty` IS NULL",
				"SET `data` = '' WHERE `data` IS NULL",
				"SET `product_name` = '' WHERE `product_name` IS NULL",
				"SET `design` = '' WHERE `design` IS NULL",
				"SET `currency` = '' WHERE `currency` IS NULL",
				"SET `screenshots` = '' WHERE `screenshots` IS NULL",
				"SET `print_files` = '' WHERE `print_files` IS NULL",
				"SET `custom` = 1 WHERE `custom` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `order_id` `order_id` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0'",
				"CHANGE `product_id` `product_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0",
				"CHANGE `product_base` `product_base` BIGINT(20) NOT NULL DEFAULT 0",
				"CHANGE `product_price` `product_price` FLOAT NOT NULL DEFAULT 0",
				"CHANGE `qty` `qty` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `data` `data` TEXT NOT NULL",
				"CHANGE `design` `design` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `product_name` `product_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `currency` `currency` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `screenshots` `screenshots` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `print_files` `print_files` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `custom` `custom` INT(1) NOT NULL DEFAULT 1",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'printings' => array(
				
				"SET `calculate` = '' WHERE `calculate` IS NULL",
				"SET `thumbnail` = '' WHERE `thumbnail` IS NULL",
				"SET `upload` = '' WHERE `upload` IS NULL",
				"SET `description` = '' WHERE `description` IS NULL",
				"SET `active` = 1 WHERE `active` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `calculate` `calculate` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `thumbnail` `thumbnail` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `upload` `upload` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `description` `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `active` `active` INT(1) NOT NULL DEFAULT 1",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'products' => array(
				
				"SET `name` = '' WHERE `name` IS NULL",
				"SET `price` = 0 WHERE `price` IS NULL",
				"SET `product` = 0 WHERE `product` IS NULL",
				"SET `thumbnail` = '' WHERE `thumbnail` IS NULL",
				"SET `thumbnail_url` = '' WHERE `thumbnail_url` IS NULL",
				"SET `template` = '' WHERE `template` IS NULL",
				"SET `description` = '' WHERE `description` IS NULL",
				"SET `stages` = '' WHERE `stages` IS NULL",
				"SET `variations` = '' WHERE `variations` IS NULL",
				"SET `attributes` = '' WHERE `attributes` IS NULL",
				"SET `printings` = '' WHERE `printings` IS NULL",
				"SET `order` = 0 WHERE `order` IS NULL",
				"SET `active` = 1 WHERE `active` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `price` `price` FLOAT NOT NULL DEFAULT 0",
				"CHANGE `product` `product` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `thumbnail` `thumbnail` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `thumbnail_url` `thumbnail_url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `template` `template` VARCHAR(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `description` `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `stages` `stages` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `variations` `variations` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `attributes` `attributes` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `printings` `printings` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `order` `order` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `active` `active` INT(1) NOT NULL DEFAULT 1",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'settings' => array(
				
				
				"SET `key` = '' WHERE `key` IS NULL",
				"SET `value` = '' WHERE `value` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `key` `key` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `value` `value` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'shapes' => array(
				
				"SET `name` = '' WHERE `name` IS NULL",
				"SET `content` = '' WHERE `content` IS NULL",
				"SET `order` = 0 WHERE `order` IS NULL",
				"SET `active` = 1 WHERE `active` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `content` `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `order` `order` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `active` `active` INT(1) NOT NULL DEFAULT 1",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'shares' => array(
				
				"SET `aid` = '' WHERE `aid` IS NULL",
				"SET `share_id` = '' WHERE `share_id` IS NULL",
				"SET `product` = 0 WHERE `product` IS NULL",
				"SET `product_cms` = 0 WHERE `product_cms` IS NULL",
				"SET `view` = 0 WHERE `view` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `aid` `aid` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `share_id` `share_id` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `product` `product` INT(255) NOT NULL DEFAULT 0",
				"CHANGE `product_cms` `product_cms` INT(255) NOT NULL DEFAULT 0",
				"CHANGE `view` `view` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'tags' => array(
				
				"SET `name` = '' WHERE `name` IS NULL",
				"SET `type` = '' WHERE `type` IS NULL",
				"SET `slug` = '' WHERE `slug` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `type` `type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `slug` `slug` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			),
			'tags_reference' => array(
				
				"SET `tag_id` = 0 WHERE `tag_id` IS NULL",
				"SET `item_id` = 0 WHERE `item_id` IS NULL",
				"SET `type` = '' WHERE `type` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `tag_id` `tag_id` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `item_id` `item_id` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `type` `type` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL"
			),
			'templates' => array(
				
				"SET `name` = '' WHERE `name` IS NULL",
				"SET `price` = 0 WHERE `price` IS NULL",
				"SET `author` = '' WHERE `author` IS NULL",
				"SET `screenshot` = '' WHERE `screenshot` IS NULL",
				"SET `upload` = '' WHERE `upload` IS NULL",
				"SET `tags` = '' WHERE `tags` IS NULL",
				"SET `featured` = 0 WHERE `featured` IS NULL",
				"SET `active` = 1 WHERE `active` IS NULL",
				"SET `order` = 0 WHERE `order` IS NULL",

				"SET `created` = '{$date}' WHERE `created` IS NULL",
				"SET `updated` = '{$date}' WHERE `updated` IS NULL",
				
				"CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",
				"CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `price` `price` FLOAT NOT NULL DEFAULT 0",
				"CHANGE `author` `author` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''",
				"CHANGE `screenshot` `screenshot` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `upload` `upload` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `tags` `tags` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL",
				"CHANGE `featured` `featured` INT(1) NOT NULL DEFAULT 0",
				"CHANGE `active` `active` INT(1) NOT NULL DEFAULT 1",
				"CHANGE `order` `order` INT(11) NOT NULL DEFAULT 0",
				"CHANGE `created` `created` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"CHANGE `updated` `updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'"
			)
		);
		
		foreach ($tbs as $tb => $chs) {
			foreach ($chs as $ch) {
				$this->main->db->rawQuery( (strpos($ch, 'CHANGE') === 0 ? 'ALTER TABLE' : 'UPDATE')." `{$this->main->db->prefix}{$tb}` ".$ch );
			}
		}
	}
	
	public function upgrade_1_7_5() {
		
		$products = $this->main->db->rawQuery("SELECT * FROM `{$this->main->db->prefix}products`");
		
		if ( count($products) > 0 ) {
			
			foreach ( $products as $product ) {
			
				$attributes = $this->main->lib->dejson($product['attributes']);
				$new_attributes = array();
				
				foreach ( $attributes as $i => $attribute ) {
					
					$attribute = (Array)$attribute;
					
					if (
						!empty($attribute['values']) &&
						is_string($attribute['values']) &&
						in_array($attribute['type'], array('select', 'product_color', 'color', 'checkbox', 'radio'))
					) {
					
						$values = explode("\n", $attribute['values']);
						
						for ($j = count($values)-1; $j >=0; $j--) {
							$value = trim($values[$j]);
							if (!empty($value)) {
								$value = explode('|', $value);
								$values[$j] = array(
									'value' => trim($value[0]),
									'title' => isset($value[1]) ? trim($value[1]) : trim($value[0]),
									'price' => '',
									'default' => ''
								);
							} else {
								unset($values[$j]);
							}
						}
						
						$attributes->{$i}->values = array("options" => $values);
						
						if ($attribute['type'] == 'checkbox') {
							$attributes->{$i}->values['multiple'] = true;
							$attribute['type'] = 'options';
						}
						
						if ($attribute['type'] == 'radio')
							$attribute['type'] = 'options';
						
						$attributes->{$i}->values = $attributes->{$i}->values;
						
					}
				
				}
				
				$this->main->lib->edit_row( 
					$product['id'], 
					array("attributes" => $this->main->lib->enjson($attributes)), 
					'products' 
				);
					
			}
			
		}
		
		$list_tables = array(
			'bugs' => 'status',
			'categories' => 'order',
			'categories_reference' => 'item_id',
			'cliparts' => 'use_count',
			'designs' => 'sharing',
			'fonts' => 'active',
			'guests' => 'phone',
			'languages' => 'lang',
			'orders' => 'status',
			'order_products' => 'custom',
			'printings' => 'description',
			'products' => 'active',
			'sessions' => 'expires',
			'settings' => 'value',
			'shapes' => 'order',
			'shares' => 'view',
			'tags' => 'slug',
			'tags_reference' => 'item_id',
			'templates' => 'featured',
		);
		
		foreach ($list_tables as $k => $v) {
			$columns = $this->main->db->rawQuery("SHOW COLUMNS FROM `{$this->main->db->prefix}{$k}` LIKE 'author'");
			if(count($columns) === 0){
				$this->main->db->rawQuery(
					"ALTER TABLE `{$this->main->db->prefix}{$k}` ADD `author` VARCHAR(255) NOT NULL DEFAULT '' AFTER `{$v}`"
				);
			}
		}
	}
	
}

	

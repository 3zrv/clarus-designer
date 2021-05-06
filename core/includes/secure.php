<?php
/**
*	
*	(p) package: Secure
*	(c) author:	Lumise .Inc
*	(i) website: https://www.lumise.com
*
*/

if (!defined('LUMISE')) {
	return header('HTTP/1.0 403 Forbidden');
}

class lumise_secure{
	
	static function create_nonce($name) {
		
		global $lumise;
		
		$session_id	 = lumise_secure::esc($_COOKIE['LUMISESESSID']);
		$name		 = lumise_secure::esc($name);
		$time 		 = time();
		$db 		 = "{$lumise->db->prefix}sessions";
		
		$nonce 		 = $lumise->db->rawQuery(
			"SELECT `id`, `value`, `expires` 
				FROM `$db` 
				WHERE `author`='{$lumise->vendor_id}' AND `session_id`='{$session_id}' AND `name`='{$name}'"
		);
		
		if (count($nonce) > 0) {
			
			$valid = false;
			
			foreach ($nonce as $no) {

				if (
					$no['expires'] > $time &&
					$valid === false
				) {
					
					if ($no['expires'] < $time+1000) {
						$lumise->db->rawQuery(
							"UPDATE `$db` 
							SET `expires`=".($time+(60*60*24))." 
							WHERE `author`='{$lumise->vendor_id}' AND `id`='{$no['id']}'"
						);
					}
					
					$valid = $no;
					
				}
				
			}
			
			if ( $valid !== false ) {
				
				if ( count($nonce) > 1 ) {
					$lumise->db->rawQuery(
						"DELETE FROM `$db` WHERE `author`='{$lumise->vendor_id}' AND `name`='{$name}' AND `session_id`='{$session_id}' AND `id` <> ".$valid['id']
					);
				}
				
				return $valid['value'];
			
			} else {
				$lumise->db->rawQuery(
					"DELETE FROM `$db` WHERE `author`='{$lumise->vendor_id}' AND `name`='{$name}' AND `session_id`='{$session_id}'"
				);
			}
			
		}
		
		$val = strtoupper($lumise->generate_id(8));
		
		$lumise->db->rawQuery(
			"INSERT INTO `$db` 
				(`id`, `name`, `value`, `author`, `expires`, `session_id`) 
				VALUES (NULL, '{$name}', '{$val}', '{$lumise->vendor_id}', ".($time+(60*60*24)).", '{$session_id}')"
		);
		
		return $val;
		
	}
	
	static function check_nonce($name, $value) {
		
		global $lumise;
		
		$db 			= "{$lumise->db->prefix}sessions";
		$query			= "DELETE FROM `$db` WHERE `author`='{$lumise->vendor_id}' AND `expires` < ".time();
		$nonce			= $lumise->db->rawQuery($query);
		$time			= time();
		
		$session_id 	= lumise_secure::esc($_COOKIE['LUMISESESSID']);
		$name 			= lumise_secure::esc($name);
		
		$nonce 			= $lumise->db->rawQuery(
			"SELECT `id`, `value`, `expires` 
				FROM `$db` 
				WHERE `author`='{$lumise->vendor_id}' AND `name`='{$name}' AND `session_id`='{$session_id}'"
		);
		
		$_return = false;
		
		if ( count($nonce) > 0 ) {
			
			foreach ($nonce as $no) {
				if (
					$no["value"] == $value &&
					$no['expires'] > $time &&
					$_return === false
				) {
					if (
						$no['expires'] < $time+1000
					) {
						$lumise->db->rawQuery(
							"UPDATE `$db` 
							SET `expires`=".($time+(60*60*24))." 
							WHERE `author`='{$lumise->vendor_id}' AND `id`='{$no['id']}'"
						);
					}
					
					$_return = $no['id'];
					
				}
				
			}
			
			$_return = $lumise->apply_filters('check-nonce', $_return, $name, $value);
			
			if ( $_return !== false ) {
				
				if ( count($nonce) > 1 ) {
					$lumise->db->rawQuery(
						"DELETE FROM `$db` WHERE `author`='{$lumise->vendor_id}' AND `name`='{$name}' AND `session_id`='{$session_id}' AND `id` <> ".$_return
					);
				}
				
				return true;
			
			} else {
				return false;
			}
			
		} else {
			$_return = $lumise->apply_filters('check-nonce', $_return, $name, $value);
			return $_return;
		}
		
	}
	
	static function nonce_exist($name, $value) {
		
		global $lumise;
		
		$session_id	 = lumise_secure::esc($_COOKIE['LUMISESESSID']);
		$name		 = lumise_secure::esc($name);
		$db 		 = "{$lumise->db->prefix}sessions";
		$nonce 		 = $lumise->db->rawQuery(
			"SELECT `value`, `expires` 
				FROM `$db`
				WHERE `author`='{$lumise->vendor_id}' AND `name`='{$name}' AND `session_id`='{$session_id}'"
		);
		
		if (count($nonce) > 0 && $nonce[0]["value"] == $value) {
			return true;
		}else return false;
		
	}
	
	static function esc($string = '') {
		
	   $string = preg_replace('/[^A-Za-z0-9\-\_]/', '', $string);
	
	   return $string;

	}
	
}

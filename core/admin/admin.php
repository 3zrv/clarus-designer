<?php
/**
*
*	(c) copyright:	lumise
*	(i) website:	lumise
*
*/

if(!defined('LUMISE')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class lumise_admin extends lumise_lib{

	public function __construct() {
		global $lumise;
		$this->main = $lumise;
		$this->process_actions();
	}

	public function get_category_item($item_id, $type){

		global $lumise;
		$db = $lumise->get_db();
		$db->join("categories_reference ca", "cate.id=ca.category_id", "LEFT");
		$db->where("ca.item_id", $item_id);
		$db->where("ca.type", $type);
		$result = $db->get ("categories cate", null, "cate.id, cate.name");

		return $result;

	}

	public function convert_slug_name($slug, $arr, $type) {

		$arr_name = array();
		$slug = explode (',', $slug);

		for ($i = 0; $i < count($slug); $i++) {
			foreach ($arr as $value) {
				if ($value['slug'] == $slug[$i] && $value['type'] == $type) {
					$arr_name[] = $value['name'];
				}
			}
		}
		$arr_name = implode(', ', $arr_name);

		return $arr_name;

	}

	protected function process_save_data($field, $data) {
		
		if (isset($field['type']) && $field['type'] == 'trace')
			return $data;
			
		global $lumise_admin, $lumise;
		
		$pg = $lumise->esc('lumise-page').'-s';
		$pg = str_replace(array('s-s', '-s'), array('s', 's'), $pg);
		
		if ($lumise->esc('lumise-page') == 'category' || $lumise->esc('lumise-page') == 'tag')
			$pg = $_POST['type'];
			
		if (!$lumise->caps('lumise_edit_'.$pg)) {
			$data['errors'] = array($lumise->lang('Sorry, you are not allowed to save data in this section').' '.$pg);
			return $data;
		}
		
		if (isset($field['type']) && $field['type'] != 'categories') {
			$field_name = $this->esc($field['name']);
			if ((isset($field['required']) && $field['required'] === true) && empty($field_name))
				$data['errors'][$field['name']] = $lumise->lang('The required fields can not be empty: ').$field['label'];
			else if ((!isset($field['db']) || $field['db'] !== false) && isset($_POST[$field['name']])) {
				$data[$field['name']] = $_POST[$field['name']];
				if (isset($field['numberic'])){
					switch ($field['numberic']) {
						case 'int':
							$data[$field['name']] = intval($_POST[$field['name']]);
							break;
						
						case 'float':
							$data[$field['name']] = floatval($_POST[$field['name']]);
							break;
						
						default:
							# code...
							break;
					}
				}

				if ($field['type'] == 'tabs')
					$data[$field['name']] = json_encode($data[$field['name']]);
				else if (is_array($data[$field['name']]))
					$data[$field['name']] = implode(',', array_diff($data[$field['name']], array("")));
			}
		}
		
		if (isset($field['type']) && $field['type'] == 'parent') {
			if ($_POST[$field['name']] == 'None')
				$data[$field['name']] = '0';
			else
				$data[$field['name']] = $_POST[$field['name']];
		}	

		if (isset($field['type']) && $field['type'] == 'upload')
			$data = $this->process_upload($field, $data);
			
		if (isset($field['type']) && $field['type'] == 'toggle' && !isset($data[$field['name']]))
			$data[$field['name']] = '0';

		if (isset($field['type']) && $field['type'] == 'tags' && isset($_POST[$field['name']])){
			$data[$field['name']] = $_POST[$field['name']];
		}
		
		$data = $lumise->apply_filters('save_fields', $data, $field);
		
		if (isset($field['db']) && $field['db'] === false) {
			unset($data[$field['name']]);
		}
		
		return $data;

	}

	protected function process_upload($field, $data) {

		global $lumise;
		
		if (!$lumise->caps('lumise_can_upload')) {
			$data['errors'][$field['name']] = $lumise->lang('Sorry, you are not allowed to upload files');
			return $data;
		}
		
		$name = $field['name'];
		$old_upload = isset($_POST['old-'.$name])? $_POST['old-'.$name] : null;
		$old_thumbn = (isset($field['thumbn']) && isset($_POST['old-'.$field['thumbn']])) ? $_POST['old-'.$field['thumbn']] : null;

		if (isset($data[$name]) && $data[$name] == $old_upload)
			return $data;
			
		if (isset($data[$name]) && !empty($data[$name])) {

			if ($data[$name] != $old_upload) {

				$time = time();
				$path = isset($field['path']) ? $field['path'] : '';

				$check = $lumise->check_upload($time);

				if ($check !== 1) {

					$data['errors'][$name] = $check;
					unset($data[$name]);

				}else{

					$process = $this->upload_file($data[$name], $path);
					
					if (isset($process['error'])) {
						$data['errors'][$name] = $process['error'];
					}else{
						$data[$name] = str_replace(DS, '/', $path).$process['name'];
						
						if (
							isset($_POST['old-'.$name]) &&
							$_POST['old-'.$name] != $data[$name] &&
							file_exists($lumise->cfg->upload_path.$_POST['old-'.$name])
						) {
							@unlink($lumise->cfg->upload_path.$_POST['old-'.$name]);
						}

						if (isset($process['thumbn']) && isset($field['thumbn'])) {
							$data[$field['thumbn']] = $lumise->cfg->upload_url.str_replace(DS, '/', $path.$process['thumbn']);
						}

						if (
							isset($field['thumbn']) && isset($_POST['old-'.$field['thumbn']]) && 
							$data[$field['thumbn']] != $_POST['old-'.$field['thumbn']]
						) {
							$old_thumn = str_replace(array($lumise->cfg->upload_url, '/'), array($lumise->cfg->upload_path, DS), $_POST['old-'.$field['thumbn']]);
							@unlink($old_thumn);

						}

					}

				}
			}

		} else {

			if (file_exists($lumise->cfg->upload_path.$old_upload))
				@unlink($lumise->cfg->upload_path.$old_upload);

			if (isset($old_thumbn) && $old_thumbn !== null) {
				$old_thumn = str_replace(array($lumise->cfg->upload_url, '/'), array($lumise->cfg->upload_path, DS), $old_thumbn);
				@unlink($old_thumn);
				$data[$field['thumbn']] = '';
			}

		}
		
		return $data;

	}

	protected function process_save_reference($args, $id) {

		global $lumise_admin, $lumise;

		$cates = array();
		$tags = array();

		if (isset($args['tabs'])) {
			foreach ($args['tabs'] as $key => $tab) {
				foreach($tab as $key2 => $field) {
					if (isset($field['type']) && $field['type'] == 'categories')
						array_push($cates, $field);
					if (isset($field['type']) && $field['type'] == 'tags')
						array_push($tags, $field);
				}
			}
		} else {
			foreach($args as $key => $field) {
				if (isset($field['type']) && $field['type'] == 'categories')
					array_push($cates, $field);
				if (isset($field['type']) && $field['type'] == 'tags')
					array_push($tags, $field);
			}
		}

		if (count($cates) > 0) {
			
			foreach ($cates as $field) {
				
				if (isset($_POST[$field['name']]) && is_array($_POST[$field['name']]))
					$post_cates = array_diff($_POST[$field['name']], array(''));
				else $post_cates = array();
				
				$lumise->db->rawQuery("DELETE FROM `{$lumise->db->prefix}categories_reference` WHERE `item_id`=".$id);

				if (is_array($post_cates) && count($post_cates) > 0) {
					foreach ($post_cates as $cate) {
						$lumise_admin->add_row(array(
							'category_id' => $cate,
							'item_id' => $id,
							'type' => $field['cate_type']
						), 'categories_reference');
					}
				}
			}
		}

		if (count($tags) > 0 && isset($_POST[$field['name']])) {

			foreach ($tags as $field) {

				$post_tags = $_POST[$field['name']];
				$post_tags = preg_replace('/,\s+,|,\s+/', ',', $post_tags);
				$post_tags = explode(',', trim($post_tags, ','));
				$post_tags = array_unique($post_tags);

				$lumise->db->rawQuery("DELETE FROM `{$lumise->db->prefix}tags_reference` WHERE `item_id`=".$id);

				if (is_array($post_tags) && count($post_tags) > 0) {
					foreach ($post_tags as $tag) {

						$tid = $lumise->db->rawQuery("SELECT `id` FROM `{$lumise->db->prefix}tags` WHERE `author`='{$lumise->vendor_id}' AND `slug`='{$this->slugify($tag)}'");

						if (!isset($tid[0])) {
							$tid = $this->add_row( array(
								'name' => $tag,
								'slug' => $this->slugify($tag),
								'author' => $lumise->vendor_id,
								'updated' => date("Y-m-d").' '.date("H:i:s"),
								'created' => date("Y-m-d").' '.date("H:i:s"),
								'type' => $field['tag_type']
							), 'tags' );
						}else $tid = $tid[0]['id'];

						$lumise_admin->add_row(array(
							'tag_id' => $tid,
							'item_id' => $id,
							'author' => $lumise->vendor_id, 
							'type' => $field['tag_type']
						), 'tags_reference');

					}
				}
			}
		}

	}

	protected function process_field($args, $data) {
		
		if (isset($args['name']) && (!isset($args['db']) || $args['db'] !== false)) {
			$args['value'] = isset($data[$args['name']]) ? $data[$args['name']] : '';
			if (
				$args['type'] == 'upload' &&
				isset($args['thumbn']) &&
				isset($data[$args['thumbn']])
			) {
				$args['thumbn_value'] = $data[$args['thumbn']];
			}
		}

		return $args;

	}

	public function process_data($args, $name) {
		
		global $lumise;
		
		$args = $lumise->apply_filters('process-section-'.$name, $args);
		
		$_id = isset($_GET['id'])? $_GET['id'] : 0;
		$_cb = isset($_GET['callback']) ? $_GET['callback'] : '';

		if (isset($_id)) {

			$data = $this->get_row_id($_id, $name);

			if (isset($args['tabs'])) {
				foreach ($args['tabs'] as $key => $tab) {
					foreach($tab as $key2 => $fields) {
						$args['tabs'][$key][$key2] = $this->process_field($args['tabs'][$key][$key2], $data);
					}
				}
			} else {
				foreach($args as $key => $field) {
					$args[$key] = $this->process_field($args[$key], $data);
				}
			}
		}

		if (isset($_POST['lumise-section'])) {
			
			$section = $_POST['lumise-section'];

			$data = array(
				'errors' => array()
			);

			$data_id = $this->esc('id');
			/*
			* Begin checking permision
			*/
			if (!empty($data_id)) {
				
				$db = $lumise->get_db();
				
				$check_per = $db->rawQuery(
					sprintf(
						"SELECT * FROM `%s` WHERE `id`=%d",
						$db->prefix.$name,
						$data_id
					)
				);
				
				if (count($check_per) > 0) {
					
					if (
						isset($check_per[0]['author']) &&
						$check_per[0]['author'] != $lumise->vendor_id
					) {
						
						$lumise_msg = array('status' => 'error', 'errors' => array(
							$this->main->lang('Error, Access denied on changing this section!')
						));
						
						$lumise->connector->set_session('lumise_msg', $lumise_msg);
						
						if (isset($_POST['redirect'])) {
							$lumise->redirect(urldecode($_POST['redirect']).(!empty($data_id) ? '?id='.$data_id : ''));
							exit;
						}
						
						$lumise->redirect(
							$lumise->cfg->admin_url . 
							"lumise-page=$section".
							(isset($data['type']) ? '&type='.$data['type'] : '').
							(isset($_GET['callback']) ? '&callback='.$_GET['callback'] : '')
						);
						
						exit;
						
					}
				} 
			}
			
			/*
			* End checking permision
			*/
			
			if (isset($args['tabs'])) {
				foreach ($args['tabs'] as $key => $tab) {
					foreach($tab as $key2 => $field) {
						$data = $this->process_save_data($field, $data);
					}
				}
			} else {
				foreach($args as $key => $field) {
					$data = $this->process_save_data($field, $data);
				}
			}
			
			if ($section == 'font') {
				
				$fi = 0;
				$fn = $lumise->lib->slugify($data['name']);
				if(isset($data['name_desc']) && $data['name_desc'] != ''){
					$data['name_desc'] = preg_replace("/,/m", "", $data['name_desc']);
				}
				
				do {
					$data['name'] = $fn.($fi > 0 ? '-'.$fi : '');
					$fquery = "SELECT `id` FROM `{$lumise->db->prefix}fonts`";
					$fquery .= " WHERE `author`='{$lumise->vendor_id}' AND `name` = '".$lumise->lib->sql_esc($data['name'])."'";
					if (!empty($data_id))
						$fquery .= " AND `id` <> {$data_id}";
					$check = $lumise->db->rawQuery ($fquery);
					$fi++;
				} while (count($check) > 0);
				
			}
			
			if (isset($data['type'])) {

				$data_slug = array();
				$data['slug'] = $this->slugify($data['name']);

				if ($name == 'tags')
					$val = $this->get_rows_custom(array('slug', 'type'), 'tags');

				if ($name == 'categories')
					$val = $this->get_rows_custom(array('slug', 'type'), 'categories');

				foreach ($val as $value) {
					if ($value['type'] == $data['type']) {
						$data_slug[] = $value['slug'];
					}
				}

				if (in_array($data['slug'], $data_slug))
					$data['slug'] = $this->add_count($data['slug'], $data_slug);
			}

			if (empty($data_id))
				$data['created'] = date("Y-m-d").' '.date("H:i:s");
			
			$data['updated'] = date("Y-m-d").' '.date("H:i:s");
			
			if (count($data['errors']) == 0) {

				unset($data['errors']);
				
				if (!empty($data_id)) {
					$data = $lumise->apply_filters('edit-section', $data, $name);
					$id = $this->edit_row( $data_id, $data, $name );
				} else {
					$data = $lumise->apply_filters('new-section', $data, $name);
					$id = $this->add_row( $data, $name );
				}
				
				$lumise->do_action('process-fields', $section, $id);
				
				$lumise->connector->set_session('lumise_msg', array('status' => 'success'));

			}
			
			if (isset($id) && is_array($id) && isset($id['error'])) {
				if (!isset($data['errors']))
					$data['errors'] = array();
				array_push($data['errors'], $id['error']);
			}
			
			if (!isset($id) || empty($id)) {
				if (!isset($data['errors']))
					$data['errors'] = array();
				array_push($data['errors'], $lumise->db->getLastError());
			}
			
			if (isset($data['errors']) && count($data['errors']) > 0) {

				$lumise_msg = array('status' => 'error', 'errors' => $data['errors']);
				$lumise->connector->set_session('lumise_msg', $lumise_msg);
				
				if (isset($_POST['redirect'])) {
					$lumise->redirect(urldecode($_POST['redirect']).(!empty($data_id) ? '?id='.$data_id : ''));
					exit;
				}
				
				if (!empty($data_id)) {
					$lumise->redirect($lumise->cfg->admin_url . "lumise-page=$section&id=$data_id&".(isset($data['type']) ? '&type='.$data['type'] : '').(isset($_GET['callback']) ? '&callback='.$_GET['callback'] : ''));
				} else {
					$lumise->redirect($lumise->cfg->admin_url . "lumise-page=$section".(isset($data['type']) ? '&type='.$data['type'] : '').(isset($_GET['callback']) ? '&callback='.$_GET['callback'] : ''));
				}
				exit;

			}

			if (isset($id) && !empty($id)) {

				$this->process_save_reference($args, $id);

				if (!empty($_cb) && $this->process_callback($id, $_cb) === false)
					exit;
				
				if (isset($_POST['redirect'])) {
					$lumise->redirect(urldecode($_POST['redirect']).'?id='.$id);
					exit;
				}
				
				$lumise->redirect($lumise->cfg->admin_url . "lumise-page=$section&id=$id".(isset($data['type']) ? '&type='.$data['type'] : '').(!empty($_cb) ? '&callback='.$_cb : ''));

				exit;

			}
			
		}

		return $args;

	}

	public function process_callback($id = 0, $cb = '') {

		global $lumise;

		switch ($cb) {
			case 'edit-cms-product':
				$data = $lumise->db->rawQuery("SELECT `name`,`stages`,`attributes` FROM `{$lumise->db->prefix}products` WHERE `author`='{$lumise->vendor_id}' AND `id`=$id");
	        	if (isset($data[0]) && isset($data[0]['stages'])) {
		        	$color = $lumise->lib->get_color($data[0]['attributes']);
		        	echo "<script>top.lumise_reset_products({
						id: '$id',
						name: '{$data[0]['name']}',
						color: '{$color}',
						stages: ".urldecode(base64_decode($data[0]['stages'])).
					"});</script>";
	        	}
				$lumise->connector->set_session('lumise_msg', array('status' => ''));
				return false;
			break;
			case 'edit-base-product':
				$data = $lumise->db->rawQuery("SELECT `name`,`stages`,`attributes` FROM `{$lumise->db->prefix}products` WHERE `author`='{$lumise->vendor_id}' AND `id`=$id");
	        	if (isset($data[0]) && isset($data[0]['stages'])) {
		        	$color = $lumise->lib->get_color($data[0]['attributes']);
		        	echo "<script>top.lumise_reset_products({
						id: '$id',
						name: '{$data[0]['name']}',
						color: '{$color}',
						stages: ".urldecode(base64_decode($data[0]['stages'])).
					"});</script>";
	        	}
				$lumise->connector->set_session('lumise_msg', array('status' => ''));
				return false;
			break;
		}

	}

	public function process_settings_data($args) {
		
		global $lumise;

		$fields = array();
		$data = array('errors' => array());
		
		if (isset($args['tabs'])) {
			foreach ($args['tabs'] as $tab => $tab_fields) {
				foreach ($tab_fields as $i => $field) {
					if (isset($field['name'])) {
						$args['tabs'][$tab][$i]['value'] = $lumise->get_option($field['name']);
						if (isset($_POST['lumise-section']))
							$data = $this->process_save_data($field, $data);
					}
				}
			}
		} else {
			foreach ($args as $i => $field) {
				if (isset($field['name'])) {
					$args[$i]['value'] = $lumise->get_option($field['name']);
					if (isset($_POST['lumise-section']))
						$data = $this->process_save_data($field, $data);
				}
			}
		}
		
		if (isset($_POST['lumise-section'])) {
			
			if (isset($_POST['admin_email']) && !empty($_POST['admin_email'])) {
				if ($lumise->cfg->settings['admin_email'] != trim($_POST['admin_email'])) {
					if (filter_var(trim($_POST['admin_email']), FILTER_VALIDATE_EMAIL)) {
						$lumise->set_option('admin_email', trim($_POST['admin_email']));
					} else array_push($data['errors'], $lumise->lang('Error: Invalid email format'));
				}
				if (isset($_POST['admin_password']) && !empty($_POST['admin_password'])) {
					if (
						!isset($_POST['re_admin_password']) || 
						empty($_POST['re_admin_password']) ||
						$_POST['admin_password'] != $_POST['re_admin_password'] ||
						strlen($_POST['admin_password']) < 8
					) {
						array_push($data['errors'], $lumise->lang('Error: Admin Passwords do not match or less than 8 characters'));
					}else{
						$lumise->set_option('admin_password', md5(trim($_POST['admin_password'])));
					}
				}
			}
			
			if (
				$_POST['lumise-section'] == 'settings' &&
				count($data['errors']) === 0 && 
				!$lumise->lib->render_css($data)
			) {
				$data['errors'][] = $lumise->lang('Could not save the custom css to file');
				foreach ($data as $key => $val) {
					$lumise->set_option($key, $val);
				}
				$lumise->set_option('last_update', time());
			}
			
			if (count($data['errors']) === 0) {
	
				unset($data['errors']);
				
				if ($_POST['lumise-section'] == 'settings') {
						
					$lumise->lib->render_css($data);
					
					$lumise->set_option('last_update', time());
					
					$lumise->apply_filters('after_save_settings', $data);
					
				}
				
				foreach ($data as $key => $val) {
					$lumise->set_option($key, $val);
				}
				
				$lumise->connector->set_session('lumise_msg', array('status' => 'success'));
	
				if (!isset($_POST['lumise-redirect']))
					$lumise->redirect($lumise->cfg->admin_url . "lumise-page=settings");
				else $lumise->redirect($_POST['lumise-redirect']);
				
				exit;
	
			} else {
				$lumise->connector->set_session('lumise_msg', array('status' => 'error', 'errors' => $data['errors']));
				$lumise->redirect($lumise->cfg->admin_url . "lumise-page=settings");
				exit;
			}
			
		}
		
		return $args;

	}
	
	public function render_custom_css($css) {
		
		global $lumise;
		$path = $lumise->cfg->root_path.'assets'.DS.'css'.DS.'custom.css';

		if (!empty($css)) {
			$content = str_replace(
				array('&gt;', '; ', ' }', '{ ', "\r\n", "\r", "\n", "\t",'  ','    ','    '),
				array('>', ';', '}', '{', '', '', '', '', '', '', ''),
				$css
			);
			@file_put_contents($path, stripslashes($content));
		}
	}
	
	public function process_actions() {
		
		$do_action = $this->main->lib->esc('do_action');
		if (isset($do_action)) {
			switch ($do_action) {
				
				case 'verify-license' : 
				
					$key = $this->esc('key');
					
					if (empty($key) || strlen($key) != 36 || count(explode('-', $key)) != 5) {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('The purchase code is not valid'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						return;
					}
					
					$check = $this->main->lib->remote_connect(
						$this->main->cfg->api_url.'updates/verify/',
						array(), 
						array(
							"Key: ".$key,
							"Referer: ".$_SERVER['HTTP_HOST'],
				        	"Platform: ".$this->main->connector->platform,
				        	"Scheme: ".$this->main->cfg->scheme,
				        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
				        )
					);
					
					$check = @simplexml_load_string($check);
					
					$resp = (string)$check->response[0];
					$lumise_msg = $this->main->connector->get_session('lumise_msg');
					if (!is_array($lumise_msg))
							$lumise_msg = array();
							
					if ($resp == 'anti_spam') {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('It seems you have sent too many requests, please wait for a few minutes and try again later'));
					}else if ($resp == 'register_success') {
						$this->main->set_option('purchase_key', $key);
						$lumise_msg['status'] = 'success';
						$lumise_msg['msg'] =$this->main->lang('Your purchase code has been verified successfully');
					}else{
						$this->main->set_option('purchase_key', '');
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('An error occurred').': '.strtoupper($resp));
					}
					
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				break;
				
				case 'revoke-license' : 
					
					$key = $this->esc('key');
					
					if (empty($key) || strlen($key) != 36 || count(explode('-', $key)) != 5) {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('The purchase code is not valid'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						return;
					}
					
					$check = $this->main->lib->remote_connect(
						$this->main->cfg->api_url.'updates/verify/',
						array(), 
						array(
							"Revoke: yes",
							"Key: ".$key,
							"Referer: ".$_SERVER['HTTP_HOST'],
				        	"Platform: ".$this->main->connector->platform,
				        	"Scheme: ".$this->main->cfg->scheme,
				        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
				        )
					);
					
					$check = @simplexml_load_string($check);
					
					$resp = (string)$check->response[0];
					
					$lumise_msg = $this->main->connector->get_session('lumise_msg');
					if (!is_array($lumise_msg))
							$lumise_msg = array();
					
					if ($resp == 'anti_spam') {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('You sent too much request, please wait for a few minutes and try again'));
					}else if ($resp == 'success') {
						$this->main->set_option('purchase_key', '');
						$lumise_msg['status'] = 'success';
						$lumise_msg['msg'] =$this->main->lang('Your purchase code has been revoked successful');
					}else{
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('An error occurred while processing this request, please try again later.').$resp);
					}
					
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				break;
				
				case 'check-update':
					
					$data = $this->main->update->check();
					
					if ($data === null || !isset($data['version'])) {
						
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('Something went wrong. We could not check the update this time, please check your connection and try again later.'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
					}
					
				break;
				
				case 'do-update':
					
					$key = $this->main->get_option('purchase_key');
					$sys = $this->main->lib->check_sys_update();
					
					if ($key === null || empty($key) || strlen($key) != 36 || count(explode('-', $key)) != 5) {
						$this->main->set_option('purchase_key', '');
						echo '<script type="text/javascript">window.location.href = "'.$this->main->cfg->admin_url.'lumise-page=license";</script></body></html>';
						exit;
					
					} else if ($sys !== true) {
						
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = $sys;
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						
					} else {
						
						$this->main->check_upload();
						$this->main->lib->delete_dir($this->main->cfg->upload_path.'tmpl');
						
						if (
							!is_dir($this->main->cfg->upload_path.'tmpl') && 
							!mkdir($this->main->cfg->upload_path.'tmpl', 0755)
						) {
							
							$lumise_msg['status'] = 'error';
							$lumise_msg['errors'] = array(
								$this->main->lang('Error: Could not create download folder, make sure that the permissions on lumise-data directory is 755')
							);
							$this->main->connector->set_session('lumise_msg', $lumise_msg);
							return;
						
						}
						
						$file = $this->main->cfg->upload_path.'tmpl/lumize.zip';
						
						$fh = $this->main->lib->remote_connect(
							$this->main->cfg->api_url.'updates/verify/',
							array(), 
							array(
								"Download: yes",
								"Key: ".$key,
								"Referer: ".$_SERVER['HTTP_HOST'],
					        	"Platform: ".$this->main->connector->platform,
					        	"Scheme: ".$this->main->cfg->scheme,
					        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
					        )
						);
						
						$data = file_put_contents($file, $fh);
						@fclose($fh);
						
						if ($data === 0) {
							
							$lumise_msg['status'] = 'error';
							$lumise_msg['errors'] = array(
								$this->main->lang('Error: Could not download file, make sure that the fopen() funtion on your server is enabled')
							);
							
							@unlink($file);
							
						} else if ($data < 250) {
							
							$lumise_msg['status'] = 'error';
							$erro = @file_get_contents($file);
							$lumise_msg['errors'] = array($this->main->lang('Error: ').$erro);
							
							@unlink($file);
							
						} else {
							
							$zip = new ZipArchive;
							$res = $zip->open($file);
							$rpath = str_replace(DS.'core'.DS, '', $this->main->cfg->root_path);
							
							if ($res === TRUE) {
								
								$zip->extractTo($this->main->cfg->upload_path.'tmpl');
								$zip->close();
								
								if ($this->main->connector->update()) {
									$lumise_msg['status'] = 'success';
									$lumise_msg['msg'] = $this->main->lang('Congratulations, Lumise has updated successfully, enjoy it!');
									$this->main->connector->set_session('lumise_msg', $lumise_msg);
									echo '<script type="text/javascript">window.location.href = "'.$this->main->cfg->admin_url.'lumise-page=updates";</script></body></html>';
									exit;
								} else {
									$lumise_msg['status'] = 'error';
									$lumise_msg['errors'] = array($this->main->lang('Error: Could not move files'));
								}
								
							} else {
								$lumise_msg['status'] = 'error';
								$lumise_msg['errors'] = array($this->main->lang('Error: Could not open file').$file);
							}
							
						}
						
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						
					}
					
				break;

				// end product function evantor

				case 'verify-license-addon-bundle' : 
				
					$key_addon_bundle = $this->esc('key');
					
					if (empty($key_addon_bundle) || strlen($key_addon_bundle) != 36 || count(explode('-', $key_addon_bundle)) != 5) {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('The purchase code is not valid'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						return;
					}
					
					$check = $this->main->lib->remote_connect(
						$this->main->cfg->api_url.'updates/verify_addon_bundle/',
						array(), 
						array(
							"Key: ".$key_addon_bundle,
							"Referer: ".$_SERVER['HTTP_HOST'],
				        	"Platform: ".$this->main->connector->platform,
				        	"Scheme: ".$this->main->cfg->scheme,
				        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
				        )
					);
					
					
					$check = @simplexml_load_string($check);
					
					$resp = (string)$check->response[0];
					$lumise_msg = $this->main->connector->get_session('lumise_msg');
					if (!is_array($lumise_msg))
							$lumise_msg = array();
							
					if ($resp == 'anti_spam') {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('It seems you have sent too many requests, please wait for a few minutes and try again later'));
					}else if ($resp == 'register_success') {
						$this->main->set_option('purchase_key_addon_bundle', $key_addon_bundle);
						$lumise_msg['status'] = 'success';
						$lumise_msg['msg'] =$this->main->lang('Your purchase code has been verified successfully');
					}else{
						$this->main->set_option('purchase_key_addon_bundle', '');
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('An error occurred').': '.strtoupper($resp));
					}
					
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				break;
				
				case 'revoke-license-addon-bundle' : 
					
					$key_addon_bundle = $this->esc('key');
					
					if (empty($key_addon_bundle) || strlen($key_addon_bundle) != 36 || count(explode('-', $key_addon_bundle)) != 5) {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('The purchase code is not valid'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						return;
					}
					
					$check = $this->main->lib->remote_connect(
						$this->main->cfg->api_url.'updates/verify_addon_bundle/',
						array(), 
						array(
							"Revoke: yes",
							"Key: ".$key_addon_bundle,
							"Referer: ".$_SERVER['HTTP_HOST'],
				        	"Platform: ".$this->main->connector->platform,
				        	"Scheme: ".$this->main->cfg->scheme,
				        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
				        )
					);
					
					$check = @simplexml_load_string($check);
					
					$resp = (string)$check->response[0];
					
					$lumise_msg = $this->main->connector->get_session('lumise_msg');
					if (!is_array($lumise_msg))
							$lumise_msg = array();
					
					if ($resp == 'anti_spam') {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('You sent too much request, please wait for a few minutes and try again'));
					}else if ($resp == 'success') {
						$this->main->set_option('purchase_key_addon_bundle', '');
						$lumise_msg['status'] = 'success';
						$lumise_msg['msg'] =$this->main->lang('Your purchase code has been revoked successful');
					}else{
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('An error occurred while processing this request, please try again later.').$resp);
					}
					
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				break;
				
				case 'check-update-addon-bundle':
					
					$data = $this->main->update->check();
					
					if ($data === null || !isset($data['version'])) {
						
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('Something went wrong. We could not check the update this time, please check your connection and try again later.'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
					}
					
				break;
				
				case 'do-update-addon-bundle':
					
					$key = $this->main->get_option('purchase_key_addon_bundle');
					$sys = $this->main->lib->check_sys_update();
					
					if ($key === null || empty($key) || strlen($key) != 36 || count(explode('-', $key)) != 5) {
						$this->main->set_option('purchase_key_addon_bundle', '');
						echo '<script type="text/javascript">window.location.href = "'.$this->main->cfg->admin_url.'lumise-page=license";</script></body></html>';
						exit;
					
					} else if ($sys !== true) {
						
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = $sys;
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						
					} else {
						
						$this->main->check_upload();
						$this->main->lib->delete_dir($this->main->cfg->upload_path.'tmpl');
						
						if (
							!is_dir($this->main->cfg->upload_path.'tmpl') && 
							!mkdir($this->main->cfg->upload_path.'tmpl', 0755)
						) {
							
							$lumise_msg['status'] = 'error';
							$lumise_msg['errors'] = array(
								$this->main->lang('Error: Could not create download folder, make sure that the permissions on lumise-data directory is 755')
							);
							$this->main->connector->set_session('lumise_msg', $lumise_msg);
							return;
						
						}
						
						$file = $this->main->cfg->upload_path.'tmpl/lumize.zip';
						
						$fh = $this->main->lib->remote_connect(
							$this->main->cfg->api_url.'updates/verify/',
							array(), 
							array(
								"Download: yes",
								"Key: ".$key,
								"Referer: ".$_SERVER['HTTP_HOST'],
					        	"Platform: ".$this->main->connector->platform,
					        	"Scheme: ".$this->main->cfg->scheme,
					        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
					        )
						);
						
						$data = file_put_contents($file, $fh);
						@fclose($fh);
						
						if ($data === 0) {
							
							$lumise_msg['status'] = 'error';
							$lumise_msg['errors'] = array(
								$this->main->lang('Error: Could not download file, make sure that the fopen() funtion on your server is enabled')
							);
							
							@unlink($file);
							
						} else if ($data < 250) {
							
							$lumise_msg['status'] = 'error';
							$erro = @file_get_contents($file);
							$lumise_msg['errors'] = array($this->main->lang('Error: ').$erro);
							
							@unlink($file);
							
						} else {
							
							$zip = new ZipArchive;
							$res = $zip->open($file);
							$rpath = str_replace(DS.'core'.DS, '', $this->main->cfg->root_path);
							
							if ($res === TRUE) {
								
								$zip->extractTo($this->main->cfg->upload_path.'tmpl');
								$zip->close();
								
								if ($this->main->connector->update()) {
									$lumise_msg['status'] = 'success';
									$lumise_msg['msg'] = $this->main->lang('Congratulations, Lumise has updated successfully, enjoy it!');
									$this->main->connector->set_session('lumise_msg', $lumise_msg);
									echo '<script type="text/javascript">window.location.href = "'.$this->main->cfg->admin_url.'lumise-page=updates";</script></body></html>';
									exit;
								} else {
									$lumise_msg['status'] = 'error';
									$lumise_msg['errors'] = array($this->main->lang('Error: Could not move files'));
								}
								
							} else {
								$lumise_msg['status'] = 'error';
								$lumise_msg['errors'] = array($this->main->lang('Error: Could not open file').$file);
							}
							
						}
						
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						
					}
					
				break;

				// end product function evantor

				case 'verify-license-addon-vendor' : 
				
					$key_addon_vendor = $this->esc('key');
					
					if (empty($key_addon_vendor) || strlen($key_addon_vendor) != 36 || count(explode('-', $key_addon_vendor)) != 5) {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('The purchase code is not valid'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						return;
					}
					$check = $this->main->lib->remote_connect(
						$this->main->cfg->api_url.'updates/verify_addon_vendor/',
						array(), 
						array(
							"Key: ".$key_addon_vendor,
							"Referer: ".$_SERVER['HTTP_HOST'],
				        	"Platform: ".$this->main->connector->platform,
				        	"Scheme: ".$this->main->cfg->scheme,
				        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
				        )
					);
					
					
					$check = @simplexml_load_string($check);
					
					$resp = (string)$check->response[0];
					$lumise_msg = $this->main->connector->get_session('lumise_msg');
					if (!is_array($lumise_msg))
							$lumise_msg = array();
							
					if ($resp == 'anti_spam') {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('It seems you have sent too many requests, please wait for a few minutes and try again later'));
					}else if ($resp == 'register_success') {
						$this->main->set_option('purchase_key_addon_vendor', $key_addon_vendor);
						$lumise_msg['status'] = 'success';
						$lumise_msg['msg'] =$this->main->lang('Your purchase code has been verified successfully');
					}else{
						$this->main->set_option('purchase_key_addon_vendor', '');
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('An error occurred').': '.strtoupper($resp));
					}
					
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				break;
				
				case 'revoke-license-addon-vendor' : 
					
					$key_addon_vendor = $this->esc('key');
					
					if (empty($key_addon_vendor) || strlen($key_addon_vendor) != 36 || count(explode('-', $key_addon_vendor)) != 5) {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('The purchase code is not valid'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						return;
					}
					
					$check = $this->main->lib->remote_connect(
						$this->main->cfg->api_url.'updates/verify_addon_vendor/',
						array(), 
						array(
							"Revoke: yes",
							"Key: ".$key_addon_vendor,
							"Referer: ".$_SERVER['HTTP_HOST'],
				        	"Platform: ".$this->main->connector->platform,
				        	"Scheme: ".$this->main->cfg->scheme,
				        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
				        )
					);
					
					$check = @simplexml_load_string($check);
					
					$resp = (string)$check->response[0];
					
					$lumise_msg = $this->main->connector->get_session('lumise_msg');
					if (!is_array($lumise_msg))
							$lumise_msg = array();
					
					if ($resp == 'anti_spam') {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('You sent too much request, please wait for a few minutes and try again'));
					}else if ($resp == 'success') {
						$this->main->set_option('purchase_key_addon_vendor', '');
						$lumise_msg['status'] = 'success';
						$lumise_msg['msg'] =$this->main->lang('Your purchase code has been revoked successful');
					}else{
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('An error occurred while processing this request, please try again later.').$resp);
					}
					
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				break;
				
				case 'check-update-addon-vendor':
					
					$data = $this->main->update->check();
					
					if ($data === null || !isset($data['version'])) {
						
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('Something went wrong. We could not check the update this time, please check your connection and try again later.'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
					}
					
				break;
				
				case 'do-update-addon-vendor':
					
					$key = $this->main->get_option('purchase_key');
					$sys = $this->main->lib->check_sys_update();
					
					if ($key === null || empty($key) || strlen($key) != 36 || count(explode('-', $key)) != 5) {
						$this->main->set_option('purchase_key', '');
						echo '<script type="text/javascript">window.location.href = "'.$this->main->cfg->admin_url.'lumise-page=license";</script></body></html>';
						exit;
					
					} else if ($sys !== true) {
						
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = $sys;
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						
					} else {
						
						$this->main->check_upload();
						$this->main->lib->delete_dir($this->main->cfg->upload_path.'tmpl');
						
						if (
							!is_dir($this->main->cfg->upload_path.'tmpl') && 
							!mkdir($this->main->cfg->upload_path.'tmpl', 0755)
						) {
							
							$lumise_msg['status'] = 'error';
							$lumise_msg['errors'] = array(
								$this->main->lang('Error: Could not create download folder, make sure that the permissions on lumise-data directory is 755')
							);
							$this->main->connector->set_session('lumise_msg', $lumise_msg);
							return;
						
						}
						
						$file = $this->main->cfg->upload_path.'tmpl/lumize.zip';
						
						$fh = $this->main->lib->remote_connect(
							$this->main->cfg->api_url.'updates/verify/',
							array(), 
							array(
								"Download: yes",
								"Key: ".$key,
								"Referer: ".$_SERVER['HTTP_HOST'],
					        	"Platform: ".$this->main->connector->platform,
					        	"Scheme: ".$this->main->cfg->scheme,
					        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
					        )
						);
						
						$data = file_put_contents($file, $fh);
						@fclose($fh);
						
						if ($data === 0) {
							
							$lumise_msg['status'] = 'error';
							$lumise_msg['errors'] = array(
								$this->main->lang('Error: Could not download file, make sure that the fopen() funtion on your server is enabled')
							);
							
							@unlink($file);
							
						} else if ($data < 250) {
							
							$lumise_msg['status'] = 'error';
							$erro = @file_get_contents($file);
							$lumise_msg['errors'] = array($this->main->lang('Error: ').$erro);
							
							@unlink($file);
							
						} else {
							
							$zip = new ZipArchive;
							$res = $zip->open($file);
							$rpath = str_replace(DS.'core'.DS, '', $this->main->cfg->root_path);
							
							if ($res === TRUE) {
								
								$zip->extractTo($this->main->cfg->upload_path.'tmpl');
								$zip->close();
								
								if ($this->main->connector->update()) {
									$lumise_msg['status'] = 'success';
									$lumise_msg['msg'] = $this->main->lang('Congratulations, Lumise has updated successfully, enjoy it!');
									$this->main->connector->set_session('lumise_msg', $lumise_msg);
									echo '<script type="text/javascript">window.location.href = "'.$this->main->cfg->admin_url.'lumise-page=updates";</script></body></html>';
									exit;
								} else {
									$lumise_msg['status'] = 'error';
									$lumise_msg['errors'] = array($this->main->lang('Error: Could not move files'));
								}
								
							} else {
								$lumise_msg['status'] = 'error';
								$lumise_msg['errors'] = array($this->main->lang('Error: Could not open file').$file);
							}
							
						}
						
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						
					}
					
				break;


				// end product function evantor

				case 'verify-license-addon-printful' : 
				
					$key_addon_printful = $this->esc('key');
					
					if (empty($key_addon_printful) || strlen($key_addon_printful) != 36 || count(explode('-', $key_addon_printful)) != 5) {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('The purchase code is not valid'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						return;
					}
					$check = $this->main->lib->remote_connect(
						$this->main->cfg->api_url.'updates/verify_addon_printful/',
						array(), 
						array(
							"Key: ".$key_addon_printful,
							"Referer: ".$_SERVER['HTTP_HOST'],
				        	"Platform: ".$this->main->connector->platform,
				        	"Scheme: ".$this->main->cfg->scheme,
				        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
				        )
					);
					
					
					$check = @simplexml_load_string($check);
					
					$resp = (string)$check->response[0];
					$lumise_msg = $this->main->connector->get_session('lumise_msg');
					if (!is_array($lumise_msg))
							$lumise_msg = array();
							
					if ($resp == 'anti_spam') {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('It seems you have sent too many requests, please wait for a few minutes and try again later'));
					}else if ($resp == 'register_success') {
						$this->main->set_option('purchase_key_addon_printful', $key_addon_printful);
						$lumise_msg['status'] = 'success';
						$lumise_msg['msg'] =$this->main->lang('Your purchase code has been verified successfully');
					}else{
						$this->main->set_option('purchase_key_addon_printful', '');
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('An error occurred').': '.strtoupper($resp));
					}
					
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				break;
				
				case 'revoke-license-addon-printful' : 
					
					$key_addon_printful = $this->esc('key');
					
					if (empty($key_addon_printful) || strlen($key_addon_printful) != 36 || count(explode('-', $key_addon_printful)) != 5) {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('The purchase code is not valid'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						return;
					}
					
					$check = $this->main->lib->remote_connect(
						$this->main->cfg->api_url.'updates/verify_addon_printful/',
						array(), 
						array(
							"Revoke: yes",
							"Key: ".$key_addon_printful,
							"Referer: ".$_SERVER['HTTP_HOST'],
				        	"Platform: ".$this->main->connector->platform,
				        	"Scheme: ".$this->main->cfg->scheme,
				        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
				        )
					);
					
					$check = @simplexml_load_string($check);
					
					$resp = (string)$check->response[0];
					
					$lumise_msg = $this->main->connector->get_session('lumise_msg');
					if (!is_array($lumise_msg))
							$lumise_msg = array();
					
					if ($resp == 'anti_spam') {
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('You sent too much request, please wait for a few minutes and try again'));
					}else if ($resp == 'success') {
						$this->main->set_option('purchase_key_addon_printful', '');
						$lumise_msg['status'] = 'success';
						$lumise_msg['msg'] =$this->main->lang('Your purchase code has been revoked successful');
					}else{
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('An error occurred while processing this request, please try again later.').$resp);
					}
					
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				break;
				
				case 'check-update-addon-printful':
					
					$data = $this->main->update->check();
					
					if ($data === null || !isset($data['version'])) {
						
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = array($this->main->lang('Something went wrong. We could not check the update this time, please check your connection and try again later.'));
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
					}
					
				break;
				
				case 'do-update-addon-printful':
					
					$key = $this->main->get_option('purchase_key');
					$sys = $this->main->lib->check_sys_update();
					
					if ($key === null || empty($key) || strlen($key) != 36 || count(explode('-', $key)) != 5) {
						$this->main->set_option('purchase_key', '');
						echo '<script type="text/javascript">window.location.href = "'.$this->main->cfg->admin_url.'lumise-page=license";</script></body></html>';
						exit;
					
					} else if ($sys !== true) {
						
						$lumise_msg['status'] = 'error';
						$lumise_msg['errors'] = $sys;
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						
					} else {
						
						$this->main->check_upload();
						$this->main->lib->delete_dir($this->main->cfg->upload_path.'tmpl');
						
						if (
							!is_dir($this->main->cfg->upload_path.'tmpl') && 
							!mkdir($this->main->cfg->upload_path.'tmpl', 0755)
						) {
							
							$lumise_msg['status'] = 'error';
							$lumise_msg['errors'] = array(
								$this->main->lang('Error: Could not create download folder, make sure that the permissions on lumise-data directory is 755')
							);
							$this->main->connector->set_session('lumise_msg', $lumise_msg);
							return;
						
						}
						
						$file = $this->main->cfg->upload_path.'tmpl/lumize.zip';
						
						$fh = $this->main->lib->remote_connect(
							$this->main->cfg->api_url.'updates/verify/',
							array(), 
							array(
								"Download: yes",
								"Key: ".$key,
								"Referer: ".$_SERVER['HTTP_HOST'],
					        	"Platform: ".$this->main->connector->platform,
					        	"Scheme: ".$this->main->cfg->scheme,
					        	"Cookie: PHPSESSID=".str_replace('=', '', base64_encode($_SERVER['HTTP_HOST']))
					        )
						);
						
						$data = file_put_contents($file, $fh);
						@fclose($fh);
						
						if ($data === 0) {
							
							$lumise_msg['status'] = 'error';
							$lumise_msg['errors'] = array(
								$this->main->lang('Error: Could not download file, make sure that the fopen() funtion on your server is enabled')
							);
							
							@unlink($file);
							
						} else if ($data < 250) {
							
							$lumise_msg['status'] = 'error';
							$erro = @file_get_contents($file);
							$lumise_msg['errors'] = array($this->main->lang('Error: ').$erro);
							
							@unlink($file);
							
						} else {
							
							$zip = new ZipArchive;
							$res = $zip->open($file);
							$rpath = str_replace(DS.'core'.DS, '', $this->main->cfg->root_path);
							
							if ($res === TRUE) {
								
								$zip->extractTo($this->main->cfg->upload_path.'tmpl');
								$zip->close();
								
								if ($this->main->connector->update()) {
									$lumise_msg['status'] = 'success';
									$lumise_msg['msg'] = $this->main->lang('Congratulations, Lumise has updated successfully, enjoy it!');
									$this->main->connector->set_session('lumise_msg', $lumise_msg);
									echo '<script type="text/javascript">window.location.href = "'.$this->main->cfg->admin_url.'lumise-page=updates";</script></body></html>';
									exit;
								} else {
									$lumise_msg['status'] = 'error';
									$lumise_msg['errors'] = array($this->main->lang('Error: Could not move files'));
								}
								
							} else {
								$lumise_msg['status'] = 'error';
								$lumise_msg['errors'] = array($this->main->lang('Error: Could not open file').$file);
							}
							
						}
						
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
						
					}
					
				break;
				
			}
		}
		
	}
	
	public function check_caps($cap) {
		
		$data_action = isset($_POST['action']) ? $_POST['action'] : '';
		
		if (
			in_array($data_action, array('active', 'deactive', 'delete')) &&
			!$this->main->caps('lumise_edit_'.$cap)
		) {
			$this->main->connector->set_session('lumise_msg', array(
					'status' => 'error', 
					'errors' => array($this->main->lang('Sorry, you are not allowed to do this action'))
				)
			);
			echo '<script type="text/javascript">window.location.reload();</script></body></html>';
			exit;
		}
	}
	
}

class lumise_helper {

	public function breadcrumb($lumise_page, $type = null) {

		global $lumise;
		global $lumise_router;
		return;
		$arr = $lumise->apply_filters('admin_breadcrumb', array(
			'cliparts' => array(
				'title' => $lumise->lang('Cliparts'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=cliparts',
				'child' => array(
					'clipart' => array(
						'type'   => '',
						'title'  => $lumise->lang('Clipart'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=clipart',
					),
					'categories' => array(
						'type'   => 'cliparts',
						'title'  => $lumise->lang('Categories'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=categories&type=cliparts',
					),
					'category' => array(
						'type'   => 'cliparts',
						'title'  => $lumise->lang('Category'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=category&type=cliparts',
					),
					'tags' => array(
						'type'   => 'cliparts',
						'title'  => $lumise->lang('Tags'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=tags&type=cliparts',
					),
					'tag' => array(
						'type'   => 'cliparts',
						'title'  => $lumise->lang('Tag'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=tag&type=cliparts',
					),
				),
			),
			'designs' => array(
				'title' => $lumise->lang('Designs'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=designs',
				'child' => array(
					'design' => array(
						'type'   => '',
						'title'  => $lumise->lang('Design'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=design',
					),
				),
			),
			'templates' => array(
				'title' => $lumise->lang('Templates'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=templates',
				'child' => array(
					'template' => array(
						'type'   => '',
						'title'  => $lumise->lang('Template'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=template',
					),
					'categories' => array(
						'type'   => 'templates',
						'title'  => $lumise->lang('Categories'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=categories&type=templates',
					),
					'category' => array(
						'type'   => 'templates',
						'title'  => $lumise->lang('Category'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=category&type=templates',
					),
					'tags' => array(
						'type'   => 'templates',
						'title'  => $lumise->lang('Tags'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=tags&type=templates',
					),
					'tag' => array(
						'type'   => 'templates',
						'title'  => $lumise->lang('Tag'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=tag&type=templates',
					),
				),
			),
			'products' => array(
				'title' => $lumise->lang('Products'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=products',
				'child' => array(
					'product' => array(
						'type'   => '',
						'title'  => $lumise->lang('Product'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=product',
					),
					'categories' => array(
						'type'   => 'products',
						'title'  => $lumise->lang('Product Categories'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=categories&type=products',
					),
					'category' => array(
						'type'   => 'products',
						'title'  => $lumise->lang('Add New Category'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=category&type=products',
					),
				),
			),
			'shapes' => array(
				'title' => $lumise->lang('Shapes'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=shapes',
				'child' => array(
					'shape' => array(
						'type'   => '',
						'title'  => $lumise->lang('Shape'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=shape',
					),
				),
			),
			'addons' => array(
				'title' => $lumise->lang('Addons'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=addons',
				'child' => array(
					'explore-addons' => array(
						'type'   => '',
						'title'  => $lumise->lang('Explore'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=explore-addons',
					),
					'addons' => array(
						'type'   => '',
						'title'  => $lumise->lang('Installed'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=addons',
					),
				),
			),
			'printings' => array(
				'title' => $lumise->lang('Printing Type'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=printings',
				'child' => array(
					'printing' => array(
						'type'   => '',
						'title'  => $lumise->lang('Printing'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=printing',
					),
				),
			),
			'fonts' => array(
				'title' => $lumise->lang('Fonts'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=fonts',
				'child' => array(
					'font' => array(
						'type'   => '',
						'title'  => $lumise->lang('Edit Font'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=font',
					)
				),
			),
			'languages' => array(
				'title' => $lumise->lang('Languages'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=languages',
				'child' => array(
					'language' => array(
						'type'   => '',
						'title'  => $lumise->lang('Language'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=language',
					),
				),
			),
			'orders' => array(
				'title' => $lumise->lang('Orders'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=orders',
			),
			'settings' => array(
				'title' => $lumise->lang('Settings'),
				'link'   => $lumise->cfg->admin_url.'lumise-page=settings',
			)
		));

		$html = '<ul class="lumise_breadcrumb">';
		
		foreach ($arr as $keys => $values) {


			if ($keys == $lumise_page) {

				$html .= '<li><a href="'.$lumise->cfg->admin_url.'lumise-page=dashboard">'.$lumise->lang('Dashboard').'</a></li><li><span>'.$values['title'].'</span></li>';

			}

			if (isset($values['child'])) {

				if (isset($values['child'][$lumise_page]) && $values['child'][$lumise_page]['type'] == $type) {

					$html .= '<li><a href="'.$lumise->cfg->admin_url.'lumise-page=dashboard">'.$lumise->lang('Dashboard').'</a></li><li><a href="'.$values['link'].'">'.$values['title'].'</a></li>';

				}

				foreach ($values['child'] as $key => $child) {

					if ($key == $lumise_page && $child['type'] == $type) {

						$html .= '<li><span>'.$child['title'].'</span></li>';

					}

				}

			}

		}

		$html .= '</ul>';
		
		ob_start();
			$lumise->views->header_message();
			$content = ob_get_contents();
		ob_end_clean();
		
		if (!empty($content))
			$html .= '<br><br>'.$content;
		
		return $html;

	}

	public function resize_image($file, $w, $h) {

		$image_info = getimagesize($file);
		$type = $image_info['mime'];
		$width = $image_info[0];
		$height = $image_info[1];
		$ratio = $width/$height;
		$img = array();

		switch ($type) {
		    case 'image/jpeg':
		        $image = imagecreatefromjpeg($file);
		        break;
		    case 'image/jpg':
		        $image = imagecreatefromjpeg($file);
		        break;
		    case 'image/gif':
		        $image = imagecreatefromgif($file);
		        break;
		    case 'image/png':
		        $image = imagecreatefrompng($file);
		        break;
		    default:
		        $img['type'] = 'error';
		        break;
		}

	    if ($w == 'auto' && preg_match('/^[0-9]+$/', $h)) {

	    	if ($w/$h < $ratio) {
	    		$newwidth = $h*$ratio;
	    		$newheight = $h;
	    	} else {
	    		$newwidth = $h/$ratio;
	    		$newheight = $h;
	    	}

	    } else if (preg_match('/^[0-9]+$/', $w) && $h == 'auto') {

	    	if ($w/$h > $ratio) {
	    		$newheight = $w*$ratio;
	    		$newwidth = $w;
	    	} else {
	    		$newheight = $w/$ratio;
	    		$newwidth = $w;
	    	}

	    } else if (preg_match('/^[0-9]+$/', $w) && preg_match('/^[0-9]+$/', $h)) {
	    	$newwidth = $w;
	        $newheight = $h;
	    } else {
	    	$img['size'] = 'error';
	    }

		$new_image = imagecreatetruecolor($newwidth, $newheight);
		imagefill($new_image, 0, 0, imagecolorallocate($new_image, 255, 255, 255));
		imagecopyresampled($new_image, $image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

		$before_etx = implode('.', array_pop(explode('.', $file)));
		$file = $before_etx.'-thumbn.jpeg';
		$count = 1;

		while(file_exists($file)) {
			$file = $before_etx.'-thumbn-'.$count.'.jpeg';
			$count++;
		}
		$img['file'] = $file;

		imagejpeg($new_image, $file, 75);
    	imagedestroy($image);

		return $img;

	}

	public function upload_file( $file, $filename, $tar_file, $filetype, $filesize ) {
		
		if (!$this->main->caps('lumise_can_upload')) {
			echo $this->main->lang('Sorry, You do not have permission to upload');
			exit;
		}
		
		$target_file = $tar_file . basename($file[$filename]["name"]);
			
		$path_parts = pathinfo($target_file);
		$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

		$rs = array();
		$rs['file_name'] = basename($file[$filename]["name"]);
		$rs['thumbnail'] = '';

		$count = 1;
		while (file_exists($target_file)) {
			$rs['file_name'] = $path_parts['filename'].'-'.$count.'.'.$path_parts['extension'];
			$target_file = $tar_file.$rs['file_name'];
			$count++;
		}

		if (!in_array($imageFileType, $filetype)) {
			$filetype = implode(', ', $filetype);
			$rs['thumbnail'] = 'Sorry, only '.$filetype.' files are allowed.';
		}

		if ( $file[$filename]['size'] > $filesize ) {
			$filesize = round ($filesize/1048576, 1);
			$rs['thumbnail'] = 'Max size '.$filesize.'MB';
		}
		if (empty($rs['thumbnail'])) {
			$rs['error'] = move_uploaded_file($file[$filename]["tmp_name"], $target_file);

		}

		return $rs;

	}

	public function format_uri( $string, $separator = '-' ){

	    $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
	    $special_cases = array( '&' => 'and', "'" => '');
	    $string = mb_strtolower( trim( $string ), 'UTF-8' );
	    $string = str_replace( array_keys($special_cases), array_values( $special_cases), $string );
	    $string = preg_replace( $accents_regex, '$1', htmlentities( $string, ENT_QUOTES, 'UTF-8' ) );
	    $string = preg_replace("/[^a-z0-9]/u", "$separator", $string);
	    $string = preg_replace("/[$separator]+/u", "$separator", $string);

	    return $string;
	}

	public function import_sample_shapes($shapes) {

		global $lumise, $lumise_router;
		
		for ($i = 0; $i < count($shapes); $i++) {
			$lumise->db->insert('shapes', array(
				"name" => "Shape ".($i+1),
				"content" => $shapes[$i],
				"author" => $lumise->vendor_id,
				"active" => 1,
				"created" => date("Y-m-d").' '.date("h:i:sa"),
				"updated" => date("Y-m-d").' '.date("h:i:sa"),
			));
		}

		$lumise->redirect($lumise->cfg->admin_url.'lumise-page=shapes');

	}

}

global $lumise_admin, $lumise_pagination;
$lumise_admin = new lumise_admin();
$lumise_pagination = new lumise_pagination();
$lumise_helper = new lumise_helper();

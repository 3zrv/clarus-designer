<?php
/**
*
*	(p) package: Lumise main.php
*	(c) author:	King-Theme
*	(i) website: https://www.lumise.com
*
*/

if (!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

define('LUMISE', '1.9.8');

class lumise {

	protected $db;
	protected $views;
	protected $update;
	protected $lib;
	protected $addons;
	protected $cfg;
	protected $logger;
	protected $connector;
	protected $actions = array();
	protected $filters = array();
	protected $app = true;
	protected $router = '';
	protected $vendor_id = '';
	
	protected $dbready = false;
	
	public function __construct() {
		
		define('LUMISE_CORE_PATH', dirname(__FILE__));
		
		require_once( LUMISE_CORE_PATH . DS. 'config.php' );
		require_once( LUMISE_CORE_PATH . DS. 'lib.php' );
		require_once( LUMISE_CORE_PATH . DS. 'views.php' );
		require_once( LUMISE_CORE_PATH . DS. 'interg.php' );
		require_once( LUMISE_CORE_PATH . DS. 'addons.php' );
		require_once( LUMISE_CORE_PATH . DS. 'update.php' );
		
	}

	public function init(){
		
		$this->connector = new lumise_connector();
		$this->cfg = new lumise_cfg($this->connector);

		if (
			property_exists($this->cfg, 'database') &&
			$this->cfg->database !== null &&
			is_array($this->cfg->database)
		) {
			$parse_host = explode(':', $this->cfg->database['host']);
			$this->db = new MysqliDb (
				$parse_host[0],
				$this->cfg->database['user'],
				$this->cfg->database['pass'],
				$this->cfg->database['name'],
				isset($parse_host[1])? $parse_host[1] : '3306'
			);
			
			$this->db->prefix = isset($this->cfg->database['prefix']) ? $this->cfg->database['prefix'] : 'lumise_';
			$this->db->rawQuery("SET SQL_MODE='ALLOW_INVALID_DATES'");
			
		}
		
		$dbready = $this->db->rawQuery("SHOW TABLES LIKE '{$this->db->prefix}settings'");
		
		if (count($dbready) === 0)
			return;
			
		$this->dbready = true;
		
		require_once(LUMISE_CORE_PATH.DS.'actions.php');
		
		$this->views = new lumise_views($this);
		$this->lib = new lumise_lib($this);
		$this->router = $this->esc('lumise-router');
		
		if(!empty($this->router) && $this->router == 'admin') 
			define('LUMISE_ADMIN', true);
		
		if (is_callable(array(&$this->connector, 'capabilities')))
			$this->add_filter('capabilities', array(&$this->connector, 'capabilities'));
		
		$this->update = new lumise_update();
		
		$this->cfg->init();
		
		$this->addons = new lumise_addons($this);
		$this->logger = new lumise_logger($this->cfg->upload_path . 'logs' . DS . 'debug.log');
		
		$this->cfg->apply_filters($this);
		
	}
	
	public function __get( $name ) {
        if ( isset( $this->{$name} ) ) {
            return $this->{$name};
        } else {
            throw new Exception( "Call to nonexistent '$name' property of MyClass class" );
            return false;
        }
    }

    public function __set( $name, $value ) {
        if ( isset( $this->$name ) ) {
            throw new Exception( "Tried to set nonexistent '$name' property of MyClass class" );
            return false;
        } else {
            throw new Exception( "Tried to set read-only '$name' property of MyClass class" );
            return false;
        }
    }

	public function esc($name = '', $default = '') {
		return htmlspecialchars(isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $default));
	}

	static function globe(){
		global $lumise;
		return $lumise;
	}

	public function lang($s) {
		
		if (!isset($this->connector))
			return $s;
				
		if (
			isset($this->connector->platform) && 
			$this->connector->platform == 'php'
		){

			return isset($this->cfg->lang_storage[strtolower($s)]) ?
			   str_replace("'", "&#39;", stripslashes($this->cfg->lang_storage[strtolower($s)])) : $s;
		}

		if (isset($this->connector->platform) &&$this->connector->platform == 'woocommerce'){
			if(is_admin() && isset($this->cfg->lang_storage_backend[strtolower($s)])){
				$s = str_replace("'", "&#39;", stripslashes($this->cfg->lang_storage_backend[strtolower($s)]));
			}

			if(!is_admin() && isset($this->cfg->lang_storage_frontend[strtolower($s)])){
				$s = str_replace("'", "&#39;", stripslashes($this->cfg->lang_storage_frontend[strtolower($s)]));
			}
		}

		if (!isset($this->connector->platform)){
			if(isset($this->cfg->lang_storage[strtolower($s)])){
				return str_replace("'", "&#39;", stripslashes($this->cfg->lang_storage[strtolower($s)]));
			} else {
				return $s;
			}
		}

		return $s;
	}
	
	public function router( $st = '' ) {
		
		$routers = array(
			'cart' => '..'.DS.'cart.php',
			'ajax' => 'ajax.php',
			'admin' => '..'.DS.'admin'.DS.'index.php'
		);
		
		if (!empty($st))
			$this->router = $st;
		
		if (isset($routers[$this->router]) && is_file(dirname(__FILE__).DS.$routers[$this->router])) {
			require_once(dirname(__FILE__).DS.$routers[$this->router]);
			$this->app = false;
		}
		
		if (isset($_GET['pdf_download']) && !empty($_GET['pdf_download'])) {
			$this->lib->pdf_download($_GET['pdf_download']);
		}
		
	}
	
	public function is_app(){
		return $this->app;
	}
	
	public function get_fonts() {

		return $this->db->rawQuery(
			sprintf(
				"SELECT `name`, `name_desc`, `upload` FROM `%s` WHERE `author`='%s' AND `active` = 1",
				$this->db->prefix.'fonts',
				$this->vendor_id
			)
		);

	}

	private function install() {

	}

	public function get_db() {
		return $this->db;
	}

	public function redirect($url, $use_header = false) {

		if (empty($url))
			return;
		if($this->connector->platform == 'php' || $use_header)
			@header("location: " . $url);
		else
			echo '<script type="text/javascript">window.location = "'.htmlspecialchars_decode($url).'";</script>';
		exit();

	}
	
	public function securityFrom() {

		echo '<input type="hidden" value="' . $this->cfg->security_code . '" name="' . $this->cfg->security_name . '"/>';
	}

	public function display($view = '') {

		if (is_callable(array(&$this->views, $view))) {
			call_user_func_array(array(&$this->views, $view), array());
		}

	}

	public function check_upload ($time = '') {

		if (empty($this->cfg->upload_path))
			return $this->lang('The upload folder is not defined, please report to the administrator');
		if (!is_dir($this->cfg->upload_path) && !mkdir($this->cfg->upload_path, 0755))
			return $this->lang('Could not create upload folder, please report to the administrator').': '.$this->cfg->upload_path;
		if (!is_writable($this->cfg->upload_path))
			return $this->lang('The upload folder write permission denied, please report to the administrator').': '.$this->cfg->upload_path;

		$args = $this->apply_filters('upload_folders', array('settings', 'cliparts', 'templates', 'products', 'thumbnails', 'user_uploads', 'fonts', 'orders', 'designs', 'shares', 'printings', 'logs', 'user_data', 'addons'));

		if ($time !== '') {

			$time = (float)$time;
			date_default_timezone_set('UTC');
			
			$y = DS.date('Y', $time);
			$ym = $y.DS.date('m', $time);
			
			$arym = $this->apply_filters('upload_folders', array('user_uploads', 'cliparts', 'templates', 'shares', 'designs', 'user_data', 'orders'));
			
			foreach($arym as $p) {
				array_push($args, $p.$y);
				array_push($args, $p.$ym);
			}

		}

		$index = '<html xmlns="http://www.w3.org/1999/xhtml"><head>\
					<meta http-equiv="refresh" content="0;URL=\'https://lumise.com/?client=folder-protect\'" />\
				  </head><body></body></html>';

		foreach ($args as $arg) {
			if (!is_dir($this->cfg->upload_path.$arg) && !mkdir($this->cfg->upload_path.$arg, 0755)) {
				return $this->lang('Could not create sub folder upload, please report to the administrator').': '.$this->cfg->upload_path.$arg;
			}else if(!file_exists($this->cfg->upload_path.$arg.DS.'index.html'))
				@file_put_contents($this->cfg->upload_path.$arg.DS.'index.html', $index);
		}

		return 1;

	}

	public function generate_id($length = 10) {
		
		return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);

	}

	public function get_langs() {

		$langs = $this->db->rawQuery(
			sprintf(
				"SELECT `lang` as `code` FROM `%s` WHERE `author`='%s'  GROUP BY `lang`",
				$this->db->prefix.'languages',
				$this->vendor_id
			)
		);
		
		$result = array();

		if (count($langs) > 0) {
			foreach ($langs as $lang) {
				array_push($result, $lang['code']);
			}
		}

		return $result;

	}

	public function langs () {

		return $this->cfg->langs;
		
	}

	public function set_lang($code) {

		$langs = $this->langs();
		if(isset($this->connector->platform) && $this->connector->platform == 'php'){
			if (isset($langs[$code])) {
				$this->cfg->active_language = $code;
				$this->connector->set_session('lumise-active-lang', $code);
			}
		}

		if(isset($this->connector->platform) && $this->connector->platform == 'woocommerce'){
			// backend language 
			if (isset($langs[$code]) && is_admin()) {
				$this->cfg->active_language_backend = $code;
				$this->connector->set_session('lumise-active-lang-backend', $code);
			}

			// frontend language 
			if (isset($langs[$code]) && !is_admin()) {
				$this->cfg->active_language_frontend = $code;
				$this->connector->set_session('lumise-active-lang-frontend', $code);
			}
		}

		if (isset($langs[$code]) && !isset($this->connector->platform) ) {
			$this->cfg->active_language = $code;
			$this->connector->set_session('lumise-active-lang', $code);
		}


	}

	public function set_vendor($id = '') {
		$this->vendor_id = $id;
	}

	public function get_option($key = '', $default = '') {
		if ( in_array( $key, [ 'purchase_key', 'purchase_key_addon_bundle', 'purchase_key_addon_vendor', 'purchase_key_addon_printful' ] ) ) return 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

		if (empty($key))
			return $default;
		
		$query = sprintf(
			"SELECT `value` FROM `%s` WHERE `author`='%s' AND `key`='%s'",
			$this->db->prefix.'settings',
			$this->vendor_id,
            $this->lib->sql_esc($key)
        );
			
		$result = $this->db->rawQuery($query);

		if (isset($result[0]))
			return $result[0]['value'];
		else return $default;

	}

	public function set_option($key = '', $val = '') {

		if (empty($key))
			return 0;

		if (is_array($val) || is_object($val))
			$val = json_encode($val);

		$query = sprintf(
			"SELECT `value` FROM `%s` WHERE `author`='%s' AND `key`='%s'",
			$this->db->prefix.'settings',
			$this->vendor_id,
            $this->lib->sql_esc($key)
        );
			
		$result = $this->db->rawQuery($query);
		
		$time = date("Y-m-d").' '.date("H:i:s");

		if (count($result) > 0) {
			$query = sprintf(
				"UPDATE `%s` SET `value`='%s' WHERE `author`='%s' AND `key`='%s'",
				$this->db->prefix.'settings',
	            $this->lib->sql_esc($val),
	            $this->vendor_id,
	            $this->lib->sql_esc($key)
	        );
	       $this->db->rawQuery($query);
		}else{
			$query = sprintf(
				"INSERT INTO `%s` (`id`, `key`, `value`, `author`, `created`, `updated`) VALUES (NULL, '%s', '%s', '%s', '%s', '%s')",
	            $this->db->prefix.'settings',
	            $this->lib->sql_esc($key),
	            $this->lib->sql_esc($val),
	            $this->vendor_id,
	            $time,
	            $time
	        );
			$this->db->rawQuery($query);
		}
		
	}
	
	public function add_action($name = '', $callback = null, $priority = 10) {

		if (empty($name) || !is_callable($callback))
			return;
		
		if (!isset($this->actions[$priority]))
			$this->actions[$priority] = array();
		
		if (!isset($this->actions[$priority][$name]) || !is_array($this->actions[$priority][$name]))
			$this->actions[$priority][$name] = array();

		array_push($this->actions[$priority][$name], $callback);

	}

	public function do_action($name = '', $params = null, $params2 = null) {
		
		if (!empty($name)) {
			
			foreach($this->actions as $actions) {
				if (isset($actions[$name])) {
					foreach ($actions[$name] as $action) {
						if (is_callable($action))
							call_user_func($action, $params, $params2);
					}
				}
			}
			
			if (is_callable(array($this->connector, 'do_action'))) {
				$this->connector->do_action($name, $params, $params2);
			}
			
		}
	}

	public function add_filter($name = '', $callback = null, $priority = 10) {
		
		if (empty($name) || !is_callable($callback))
			return;
		
		if (!isset($this->filters[$priority]))
			$this->filters[$priority] = array();
			
		if (!isset($this->filters[$priority][$name]) || !is_array($this->filters[$priority][$name]))
			$this->filters[$priority][$name] = array();

		array_push($this->filters[$priority][$name], $callback);

	}
	
	public function apply_filter($name = '', $params = null, $params2 = null, $params3 = null) {
		
		return $this->apply_filters($name, $params, $params2, $params3);

	}
	
	public function apply_filters($name = '', $params = '', $params2 = null, $params3 = null) {
		
		if (!empty($name)) {
			
			foreach ($this->filters as $filters) {
				if (isset($filters[$name])) {
					foreach ($filters[$name] as $action) {
						if (is_callable($action)) {
							$params = call_user_func_array($action, array($params, $params2, $params3));
						}
					}
				}
			}
			
			if (is_callable(array($this->connector, 'apply_filters'))) {
				$params = $this->connector->apply_filters($name, $params, $params2, $params3);
			}
		}
		
		return $params;

	}
	
	public function caps($name = '') {
		
		$_ft = $this->apply_filters('capabilities', $name, $name);
		return ((int)$_ft != 0 || $_ft == $name);
		
	}

}

/*==========================================*/
global $lumise;
$lumise = new lumise();
$lumise->init();
/*==========================================*/

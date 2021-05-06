<?php
/**
*
*	(p) package: Lumise
*	(c) author:	King-Theme
*	(i) website: https://lumise.com
*
*/

require_once( LUMISE_CORE_PATH . DS. 'tmpl.php' );

class lumise_cfg extends lumise_tmpl_register{

	public $root_path;
	public $upload_path;
	public $editor_url;
	public $checkout_url;
	public $upload_url;
	public $database;
	public $url;
	public $tool_url;
	public $color;
	public $logo = '4db6ac';
	public $site_uri;
	public $ajax_url;
	public $assets_url;
	public $security_name = 'form_key';
	public $security_code;
	public $admin_url;
	public $admin_assets_url;
	public $admin_ajax_url;
	public $load_jquery;
	public $js_lang;
	public $default_fonts;
	public $base_default;
	public $size_default;
	public $active_language;
	public $active_language_frontend;
	public $active_language_backend;
	public $product;
	public $print_types;
	public $api_url;
	public $scheme;
	public $lang_storage = array();
	public $lang_storage_frontend = array();
	public $lang_storage_backend = array();
    public $settings = array(
	
		'admin_email' => '',
		
		'title' => 'Lumise Design',
		'logo' => '',
		'favicon' => '',
		'logo_link' => 'https://lumise.com',
		'primary_color' => '',
		'conditions' => '',
		
		'enable_colors' => '1',
		'colors' => '#3fc7ba:#546e7a,#757575,#6d4c41,#f4511e,#ffb300,#fdd835,#c0cA33,#a0ce4e,#7cb342,#43a047,#00acc1,#3fc7ba,#039be5,#3949ab,#5e35b1,#d81b60,#eeeeee,#3a3a3a',
		'rtl' => '',
		'user_print' => '',
		'user_download' => '1',
		
		'currency' => '$',
		'currency_code' => 'USD',
		'thousand_separator' => ',',
		'decimal_separator' => '.',
		'number_decimals' => 2,
		'currency_position' => 0,
		'show_only_design' => 0,
		'merchant_id' => '',
		'sanbox_mode' => 0,
		
		'google_fonts' => '{"Roboto":["greek%2Clatin%2Ccyrillic-ext%2Ccyrillic%2Cvietnamese%2Cgreek-ext%2Clatin-ext","100%2C100italic%2C300%2C300italic%2Cregular%2Citalic%2C500%2C500italic%2C700%2C700italic%2C900%2C900italic"],"Poppins":["devanagari%2Clatin%2Clatin-ext","300%2Cregular%2C500%2C600%2C700"],"Oxygen":["latin%2Clatin-ext","300%2Cregular%2C700"],"Anton":["latin%2Clatin-ext%2Cvietnamese","regular"],"Lobster":["latin%2Clatin-ext%2Ccyrillic%2Cvietnamese","regular"],"Abril%20Fatface":["latin%2Clatin-ext","regular"],"Pacifico":["latin%2Clatin-ext%2Cvietnamese","regular"],"Quicksand":["latin%2Clatin-ext%2Cvietnamese","300%2Cregular%2C500%2C700"],"Patua%20One":["latin","regular"],"Great%20Vibes":["latin%2Clatin-ext","regular"],"Monoton":["latin","regular"],"Berkshire%20Swash":["latin%2Clatin-ext","regular"]}',
		
	    'admin_lang' => 'en',
		'editor_lang' => 'en',
		'allow_select_lang' => 1,
		'activate_langs' => array(),


		'help_title' => '',
		'helps' => array(),
		'about' => '',

		'tab' => 'general',
		'share' => 0,
		'report_bugs' => 2,
		'email_design' => 1,
		'components' => array('shop', 'product', 'templates', 'cliparts', 'text', 'uploads', 'shapes', 'drawing', 'layers', 'back'),
		'disable_resources' => '',
		'min_upload' => '',
		'max_upload' => '',
		'dis_qrcode' => '',
		'min_dimensions' => '50x50',
		'max_dimensions' => '1500x1500',
		'min_ppi' => '',
		'max_ppi' => '',
		'ppi_notice' => 'no',
		'required_full_design' => 'no',
		'auto_fit' => '1',
		'calc_formula' => '1',
		'custom_css' => '',
		'custom_js' => '',
		'prefix_file' => 'lumise',
		'text_direction' => '',
		'auto_snap' => 0,
		'template_append' => 0,
		'replace_image' => 0,
		'user_font' => 1,
		'stages' => '4',
		'label_stage_1' => 'Front',
		'label_stage_2' => 'Back',
		'label_stage_3' => 'Left',
		'label_stage_4' => 'Right',
		'last_update' => ''
	);
	
	protected $allows = array(
		'editor_url',
		'checkout_url',
		'upload_path',
		'upload_url',
		'database',
		'logo',
		'url',
		'site_uri',
		'print_types',
		'security_name',
		'security_code',
		'ajax_url',
		'assets_url',
		'load_jquery',
		'root_path',
        'admin_url',
		'admin_assets_url',
		'admin_ajax_url',
		'js_lang',
		'default_fonts',
		'base_default',
		'size_default',
		'settings',
		'product_attributes',
		'api_url',
		'scheme',
		'tool_url',
		'langs'
	);
	
	protected $langs = array(
		"af" => "Afrikaans",
		"sq" => "Albanian",
		"am" => "Amharic",
		"ar" => "Arabic",
		"hy" => "Armenian",
		"az" => "Azerbaijani",
		"eu" => "Basque",
		"be" => "Belarusian",
		"bn" => "Bengali",
		"bs" => "Bosnian",
		"bg" => "Bulgarian",
		"ca" => "Catalan",
		"ceb" => "Cebuano",
		"ny" => "Chichewa",
		"zh-CN" => "Chinese",
		"co" => "Corsican",
		"hr" => "Croatian",
		"cs" => "Czech",
		"da" => "Danish",
		"nl" => "Dutch",
		"en" => "English",
		"eo" => "Esperanto",
		"et" => "Estonian",
		"tl" => "Filipino",
		"fi" => "Finnish",
		"fr" => "French",
		"fy" => "Frisian",
		"gl" => "Galician",
		"ka" => "Georgian",
		"de" => "German",
		"el" => "Greek",
		"gu" => "Gujarati",
		"ht" => "Haitian Creole",
		"ha" => "Hausa",
		"haw" => "Hawaiian",
		"hk"	=> "Hongkong",
		"iw" => "Hebrew",
		"hi" => "Hindi",
		"hmn" => "Hmong",
		"hu" => "Hungarian",
		"is" => "Icelandic",
		"ig" => "Igbo",
		"id" => "Indonesian",
		"ga" => "Irish",
		"it" => "Italian",
		"ja" => "Japanese",
		"jw" => "Javanese",
		"kn" => "Kannada",
		"kk" => "Kazakh",
		"km" => "Khmer",
		"ko" => "Korean",
		"ku" => "Kurdish (Kurmanji)",
		"ky" => "Kyrgyz",
		"lo" => "Lao",
		"la" => "Latin",
		"lv" => "Latvian",
		"lt" => "Lithuanian",
		"lb" => "Luxembourgish",
		"mk" => "Macedonian",
		"mg" => "Malagasy",
		"ms" => "Malay",
		"ml" => "Malayalam",
		"mt" => "Maltese",
		"mi" => "Maori",
		"mr" => "Marathi",
		"mn" => "Mongolian",
		"my" => "Myanmar (Burmese)",
		"ne" => "Nepali",
		"no" => "Norwegian",
		"ps" => "Pashto",
		"fa" => "Persian",
		"pl" => "Polish",
		"pt" => "Portuguese",
		"pa" => "Punjabi",
		"ro" => "Romanian",
		"ru" => "Russian",
		"sm" => "Samoan",
		"gd" => "Scots Gaelic",
		"sr" => "Serbian",
		"st" => "Sesotho",
		"sn" => "Shona",
		"sd" => "Sindhi",
		"si" => "Sinhala",
		"sk" => "Slovak",
		"sl" => "Slovenian",
		"so" => "Somali",
		"es" => "Spanish",
		"su" => "Sundanese",
		"sw" => "Swahili",
		"sv" => "Swedish",
		"tg" => "Tajik",
		"ta" => "Tamil",
		"te" => "Telugu",
		"th" => "Thai",
		"tr" => "Turkish",
		"uk" => "Ukrainian",
		"ur" => "Urdu",
		"uz" => "Uzbek",
		"vi" => "Vietnamese",
		"cy" => "Welsh",
		"xh" => "Xhosa",
		"yi" => "Yiddish",
		"yo" => "Yoruba",
		"zu" => "Zulu"
	);
	
	protected $editor_menus = array();
	protected $product_attributes = array();
	
	protected $access_core = array();

	private $max_stages = 4;
	
	public function __construct($conn) {
		
		global $lumise;
		
		if (
			(function_exists('session_status') && session_status() == PHP_SESSION_NONE) ||
			(function_exists('session_id') && session_id() == '')
		) {
			@session_start();
		}

		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR );
		if(!defined('LUMISE_FILE'))
			define('LUMISE_FILE', __FILE__);
		if(!defined('LUMISE_PATH'))
			define('LUMISE_PATH', str_replace(DS.'includes','',dirname(__FILE__)));
		define('LUMISE_SLUG', basename(dirname(__FILE__)));
		
		
		$this->set($conn->config);
		$this->settings['logo'] = $this->assets_url.'assets/images/logo.png';
		
		require_once(LUMISE_PATH.DS.'includes'.DS.'secure.php');
		require_once(LUMISE_PATH.DS.'includes'.DS.'database.php');

	}
	
	public function set_stages($num = 4) {
		
		global $lumise;
		
		$actives = $lumise->get_option( 'active_addons');
		
		if ($actives !== null && !empty($actives))
			$actives = (Array)@json_decode($actives);
		
		if (!is_array($actives) || !isset($actives['stages']) || $actives['stages'] !== 1)
			return;
		
		$this->max_stages = $num;
		
	}
	
	public function set($args) {

		if (is_array($args)) {
			foreach($args as $name => $val) {
				if (in_array($name, $this->allows)) {
					$this->{$name} = $val;
				}
			}
		}

	}

	public function get($name = '') {

		if (in_array($name, $this->allows)) {
			return $this->{$name};
		}

		return null;

	}

	public function __get( $name ) {
        if ( isset( $this->{$name} ) ) {
            return $this->{$name};
        } else {
            return null;
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

    public function set_lang($lumise) {

    	if($lumise->connector->platform == 'php'){
    		if (
				defined('LUMISE_ADMIN') && 
				LUMISE_ADMIN === true
			) {

				if (
					isset($this->settings['admin_lang']) &&
					!empty($this->settings['admin_lang'])
				)
					$this->active_language = $this->settings['admin_lang'];
				else
					$this->active_language = 'en';

			}else{
				$this->active_language = $lumise->connector->get_session('lumise-active-lang');

				$this->active_language_frontend = $lumise->connector->get_session('lumise-active-lang');
				
				if (
					!isset($this->active_language) ||
					empty($this->active_language) || 
					!$this->settings['allow_select_lang']
				) {

					if (
						isset($this->settings['editor_lang']) &&
						!empty($this->settings['editor_lang'])
					)
						$this->active_language = $this->settings['editor_lang'];
					else
						$this->active_language = 'en';
						
						$lumise->connector->set_session('lumise-active-lang', $this->active_language);

				}
			} 
			
			if (
				isset($this->active_language) &&
				!empty($this->active_language) 
				// && $this->active_language != 'en'
			) {

				$get_query = "SELECT `original_text`, `text` FROM `{$lumise->db->prefix}languages` WHERE `author`='{$lumise->vendor_id}' AND `lang`='".$this->active_language."'";
				$get_langs = $lumise->db->rawQuery($get_query);

				if (count($get_langs) > 0) {
					foreach ($get_langs as $lang) {
						$this->lang_storage[strtolower($lang['original_text'])] = $lang['text'];
					}
				}
				
				$this->lang_storage = $lumise->apply_filters('language', $this->lang_storage);
				
			}
    	}

    	if($lumise->connector->platform == 'woocommerce'){
    		// backend
	    	if(is_admin()){
	    		if ( isset($this->settings['admin_lang']) && !empty($this->settings['admin_lang']) ){
					$this->active_language_backend = $this->settings['admin_lang'];
				} else {
					$this->active_language_backend = 'en';
				}

				if ( isset($this->settings['editor_lang']) && !empty($this->settings['editor_lang']) ){
					$this->active_language_frontend = $this->settings['editor_lang'];
				} else {
					$this->active_language_frontend = 'en';
				}

				if(isset($this->active_language_backend) && !empty($this->active_language_backend) && $this->active_language_backend != 'en'){

					$lumise->connector->set_session('lumise-active-lang-backend', $this->active_language_backend);

					$get_query = "SELECT `original_text`, `text` FROM `{$lumise->db->prefix}languages` WHERE `author`='{$lumise->vendor_id}' AND `lang`='".$this->active_language_backend."'";
					$get_langs = $lumise->db->rawQuery($get_query);

					if (count($get_langs) > 0) {
						foreach ($get_langs as $lang) {
							$this->lang_storage = $this->lang_storage_backend[strtolower($lang['original_text'])] = $lang['text'];
						}
					}
					
					$this->lang_storage_backend = $lumise->apply_filters('language', $this->lang_storage_backend);
				}

				if(isset($this->active_language_frontend) && !empty($this->active_language_frontend) && $this->active_language_frontend != 'en'){
					$lumise->connector->set_session('lumise-active-lang-frontend', $this->active_language_frontend);

					$get_query = "SELECT `original_text`, `text` FROM `{$lumise->db->prefix}languages` WHERE `author`='{$lumise->vendor_id}' AND `lang`='".$this->active_language_frontend."'";
					$get_langs = $lumise->db->rawQuery($get_query);

					if (count($get_langs) > 0) {
						foreach ($get_langs as $lang) {
							$this->lang_storage_frontend[strtolower($lang['original_text'])] = $lang['text'];
						}
					}
					
					$this->lang_storage_frontend = $lumise->apply_filters('language', $this->lang_storage_frontend);
				}
	    	}

	    	// frontend
	    	if(!is_admin()){

	    		// get frontend language session
	    		$this->active_language_frontend = $lumise->connector->get_session('lumise-active-lang-frontend');
				
				// if frontend language session not set or not allow user change, get from setting
				if ( !isset($this->active_language_frontend) || empty($this->active_language_frontend) || !$this->settings['allow_select_lang']) {
					if (!isset($this->settings['editor_lang']) || empty($this->settings['editor_lang'])){
						$this->active_language_frontend = 'en';
						
					}
					if(isset($this->settings['editor_lang']) || !empty($this->settings['editor_lang'])){
						$this->active_language_frontend = $this->settings['editor_lang'];
					}
					$lumise->connector->set_session('lumise-active-lang-frontend', $this->active_language_frontend);
				}

				$get_query = "SELECT `original_text`, `text` FROM `{$lumise->db->prefix}languages` WHERE `author`='{$lumise->vendor_id}' AND `lang`='".$this->active_language_frontend."'";
				$get_langs = $lumise->db->rawQuery($get_query);

				if (count($get_langs) > 0) {
					foreach ($get_langs as $lang) {
						$this->lang_storage_frontend[strtolower($lang['original_text'])] = $lang['text'];
					}
				}
				
				$this->lang_storage_frontend = $lumise->apply_filters('language', $this->lang_storage_frontend);
	    	}
    	}

    }

    public function set_settings($lumise) {
	    
	    global $lumise;
	    
	    $this->settings = $lumise->apply_filters('init_settings', $this->settings);

	    foreach ($this->settings as $key => $val) {
		    $this->settings[$key] = $lumise->get_option($key, $val);
	    }
    }

	public function ex_settings($set = array()) {
		
		global $lumise;
		
		foreach ($set as $key => $val) {
			$this->settings[$key] = $lumise->get_option($key, $val);
		}	
	}

	public function editor_menus($args) {
		
		if (is_array($args)) {
			foreach ($args as $id => $arg) {
				if (!isset($this->editor_menus[$id])) {
					$this->editor_menus[$id] = $arg;
				}
			}
		}
		
	}
	
	public function access_core($name) {
		if (isset($name) && !empty($name) && !in_array($name, $this->access_core))
			array_push($this->access_core, $name);
	}
	
	public function apply_filters($lumise) {
		
		$this->settings = $lumise->apply_filters('settings', $this->settings);
		$this->editor_menus = $lumise->apply_filters('editor_menus', $this->editor_menus);
		$this->product_attributes = $lumise->apply_filters('product_attributes', $this->product_attributes);
		$this->size_default = $lumise->apply_filters('size_default', $this->size_default);
		$this->langs = $lumise->apply_filters('langs_name', $this->langs);
		
	}
	
    public function init() {

	    global $lumise;

		$this->set_settings($lumise);
		$this->set_lang($lumise);
		
		$this->editor_menus = $this->reg_editor_menus();
		
		$this->product_attributes = $this->reg_product_attributes();
		
		$color = explode(':', isset($this->settings['primary_color']) ? $this->settings['primary_color'] : '#4db6ac');
		$this->color = str_replace('#', '', $color[0]);
		
	    if (is_string($lumise->cfg->settings['activate_langs'])) {
		    $lumise->cfg->settings['activate_langs'] = explode(',', $lumise->cfg->settings['activate_langs']);
	    }
		
		if (!empty($lumise->cfg->settings['logo']) && strpos($lumise->cfg->settings['logo'], 'http') === false)
			$lumise->cfg->settings['logo'] = $lumise->cfg->upload_url.$lumise->cfg->settings['logo'];
		
		$lumise->cfg->scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
		$lumise->cfg->api_url = 'https://services.lumise.com/';
		
		$this->base_default = array(
			"bag_back.png", 
			"bag_front.png", 
			"basic_tshirt_back.png", 
			"basic_tshirt_front.png", 
			"basic_women_tshirt_back.png", 
			"basic_women_tshirt_front.png", 
			"cup_back.png", 
			"cup_front.png", 
			"shoe.png", 
			"hat.png",
			"hoodie_back.png", 
			"hoodie_front.png", 
			"hoodies_sweatshirt_back.png", 
			"hoodies_sweatshirt_front.png", 
			"kids_babies_back.png", 
			"kids_babies_front.png", 
			"long_sleeve_back.png",
			"long_sleeve_front.png", 
			"phone_case.png", 
			"premium_back.png", 
			"premium_front.png", 
			"stickers.png", 
			"tank_tops_back.png", 
			"tank_tops_front.png", 
			"v_neck_tshirt_back.png", 
			"v_neck_tshirt_front.png", 
			"women_tank_tops_back.png",
			"women_tank_tops_front.png", 
			"women_tshirt_back.png", 
			"women_tshirt_front.png"
		);
		
		$this->size_default = array(
			'A0' => array(
				'cm' => '84.1 x 118.9',
				'inch' => '33.1 x 46.8',
				'px' => '9933 x 14043'
			),
			'A1' => array(
				'cm' => '59.4 x 84.1',
				'inch' => '23.4 x 33.1',
				'px' => '7016 x 9933'
			),
			'A2' => array(
				'cm' => '42 x 59.4',
				'inch' => '16.5 x 23.4',
				'px' => '4960 x 7016'
			),
			'A3' => array(
				'cm' => '29.7 x 42',
				'inch' => '11.7 x 16.5',
				'px' => '3508 x 4960'
			),
			'A4' => array(
				'cm' => '21 x 29.7',
				'inch' => '8.3 x 11.7',
				'px' => '2480 x 3508'
			),
			'A5' => array(
				'cm' => '14.8 x 21.0',
				'inch' => '5.8 x 8.3',
				'px' => '1748 x 2480'
			),
			'A6' => array(
				'cm' => '10.5 x 14.8',
				'inch' => '4.1 x 5.8',
				'px' => '1240 x 1748'
			),
			'A7' => array(
				'cm' => '7.4 x 10.5',
				'inch' => '2.9 x 4.1',
				'px' => '874 x 1240'
			),
			'A8' => array(
				'cm' => '5.2 x 7.4',
				'inch' => '2 x 2.9',
				'px' => '614 x 874'
			),
			'A9' => array(
				'cm' => '3.7 x 5.2',
				'inch' => '1.5 x 2',
				'px' => '437 x 614'
			),
			'A10' => array(
				'cm' => '2.6 x 3.7',
				'inch' => '1 x 1.5',
				'px' => '307 x 437'
			)
		);
		
	    $this->js_lang = array(
		    'sure'=> $lumise->lang('Are you sure?'),
			'save'=> $lumise->lang('Save'),
			'edit'=> $lumise->lang('Edit'),
			'remove'=> $lumise->lang('Remove'),
			'delete'=> $lumise->lang('Delete'),
			'cancel'=> $lumise->lang('Cancel'),
			'reset'=> $lumise->lang('Reset'),
			'stage'=> $lumise->lang('Stage'),
			'front'=> $lumise->lang('Front'),
			'back'=> $lumise->lang('Back'),
			'left'=> $lumise->lang('Left'),
			'right'=> $lumise->lang('Right'),
			'loading'=> $lumise->lang('Loading'),
			'importing'=> $lumise->lang('Importing'),
			'apply'=> $lumise->lang('Apply Now'),
			'render'=> $lumise->lang('Rendering design'),
			'wait'=> $lumise->lang('Please wait..'),
			'clone'=> $lumise->lang('Clone'),
			'double'=> $lumise->lang('Double'),
			'processing'=> $lumise->lang('Processing..'),
			'error_403'=> $lumise->lang('Your session is expired, Please reload your browser'),
			'01'=> $lumise->lang('Center center'),
			'02'=> $lumise->lang('Horizontal center'),
			'03'=> $lumise->lang('Vertical center'),
			'04'=> $lumise->lang('Square'),
			'05'=> $lumise->lang('Are you sure that you want to make selected objects to one object?'),
			'06'=> $lumise->lang('No layer'),
			'07'=> $lumise->lang('Add new layer to use it as a mask'),
			'08'=> $lumise->lang('Error, the active object should be covered by the mask layer'),
			'09'=> $lumise->lang('Your QRCode text'),
			'10'=> $lumise->lang('Create QR Code'),
			'11'=> $lumise->lang('Enter your text or a link'),
			'12'=> $lumise->lang('Select color for QR Code'),
			'13'=> $lumise->lang('Choose color'),
			'14'=> $lumise->lang('Visibility'),
			'15'=> $lumise->lang('Lock layer'),
			'16'=> $lumise->lang('Delete layer'),
			'17'=> $lumise->lang('Error when select stage, missing configuration'),
			'18'=> $lumise->lang('Invalid type of current active object'),
			'19'=> $lumise->lang('Invalid type of current active object'),
			'20'=> $lumise->lang('Error: missing configuration.'),
			'21'=> $lumise->lang('Your design has been saved successful.'),
			'22'=> $lumise->lang('The design has been removed'),
			'23'=> $lumise->lang('Error : Your session is invalid. Please reload the page to continue.'),
			'24'=> $lumise->lang('We just updated your expired session. Please redo your action'),
			'25'=> $lumise->lang('Data structure error'),
			'26'=> $lumise->lang('The design has been loaded successfully'),
			'27'=> $lumise->lang('You have not created any designs yet. <br>After designing, press Ctrl+S to save your designs in here.'),
			'28'=> $lumise->lang('New design has been created'),
			'29'=> $lumise->lang('The design has been successfully cleaned!'),
			'30'=> $lumise->lang('Enter the new design title'),
			'31'=> $lumise->lang('The export data under JSON has been storaged in your clipboard.'),
			'32'=> $lumise->lang('Only accept the file with type JSON that exported by our system.'),
			'33'=> $lumise->lang('Error loading image '),
			'34'=> $lumise->lang('Double-click on the text to type'),
			'35'=> $lumise->lang('Invalid size, please enter Width x Height'),
			'36'=> $lumise->lang('Error, File too large. Please try to set smaller size'),
			'37'=> $lumise->lang('Your design is not saved. Are you sure you want to leave this page?'),
			'38'=> $lumise->lang('Your design is not saved. Are you sure you want to load new design?'),
			'39'=> $lumise->lang('The link has been copied to your clipboard'),
			'40'=> $lumise->lang('Please save your design first to create link'),
			'41'=> $lumise->lang('You have not granted permission to view or edit this design'),
			'42'=> $lumise->lang('No items found'),
			'43'=> $lumise->lang('Back to categories'),
			'44'=> $lumise->lang('Please wait..'),
			'45'=> $lumise->lang('Prev'),
			'46'=> $lumise->lang('Next'),
			'47'=> $lumise->lang('Delete this image'),
			'48'=> $lumise->lang('Edit this design'),
			'49'=> $lumise->lang('Make a copy'),
			'50'=> $lumise->lang('Download design'),
			'51'=> $lumise->lang('Delete design'),
			'52'=> $lumise->lang('Click to edit design title'),
			'53'=> $lumise->lang('Warning: Images too large may slow down the tool'),
			'54'=> $lumise->lang('Design Title'),
			'55'=> $lumise->lang('Error while loading font'),
			'56'=> $lumise->lang('Categories'),
			'57'=> $lumise->lang('All categories'),
			'58'=> $lumise->lang('Design options'),
			'59'=> $lumise->lang('Keep current design'),
			'60'=> $lumise->lang('Select design from templates'),
			'61'=> $lumise->lang('Design from blank'),
			'62'=> $lumise->lang('Start design now!'),
			'63'=> $lumise->lang('Search product'),
			'64'=> $lumise->lang('Printing'),
			'65'=> $lumise->lang('Side'),
			'66'=> $lumise->lang('Quantity'),
			'67'=> $lumise->lang('Prices table'),
			'68'=> $lumise->lang('Details'),
			'69'=> $lumise->lang('More'),
			'70'=> $lumise->lang('Successfull, view the full cart and checkout in the menu "My Cart"'),
			'71'=> $lumise->lang('Your cart is empty'),
			'72'=> $lumise->lang('Editing'),
			'73'=> $lumise->lang('Your cart details'),
			'74'=> $lumise->lang('Total'),
			'75'=> $lumise->lang('Checkout'),
			'76'=> $lumise->lang('Products'),
			'77'=> $lumise->lang('Options'),
			'78'=> $lumise->lang('Actions'),
			'79'=> $lumise->lang('Updated'),
			'80'=> $lumise->lang('Choose product'),
			'81'=> $lumise->lang('Colors'),
			'82'=> $lumise->lang('Unsave'),
			'83'=> $lumise->lang('File not found'),
			'84'=> $lumise->lang('Your uploaded image'),
			'85'=> $lumise->lang('Active'),
			'86'=> $lumise->lang('Deactive'),
			'87'=> $lumise->lang('Select product'),
			'88'=> $lumise->lang('Loading fonts'),
			'89'=> $lumise->lang('No category'),
			'90'=> $lumise->lang('Categories'),
			'91'=> $lumise->lang('Design templates'),
			'92'=> $lumise->lang('Search design templates'),
			'93'=> $lumise->lang('Select design'),
			'94'=> $lumise->lang('Load more'),
			'95'=> $lumise->lang('Remove template'),
			'96'=> $lumise->lang('Error! Your design is empty, please add the objects'),
			'97'=> $lumise->lang('Can not updated'),
			'98'=> $lumise->lang('Are you sure that you want to remove this stage?'),
			'99'=> $lumise->lang('Please select one of print method.'),
			'100'=> $lumise->lang('Free'),
			'101'=> $lumise->lang('Are you sure that you want to clear design?'),
			'102'=> $lumise->lang('This field is required.'),
			'103'=> $lumise->lang('Enter at least the minimum 1 quantity.'),
			'104'=> $lumise->lang('Price'),
			'105'=> $lumise->lang('Tags:'),
			'106'=> $lumise->lang('Overwrite this design'),
			'107'=> $lumise->lang('New Design'),
			'108'=> $lumise->lang('Printing'),
			'109'=> $lumise->lang('Your design has been saved successfully!'),
			'110'=> $lumise->lang('Draft was saved'),
			'111'=> $lumise->lang('Load draft'),
			'112'=> $lumise->lang('Load the draft designs which was saved before'),
			'113'=> $lumise->lang('Successful! Your next changes will be updated to draft automatically'),
			'114'=> $lumise->lang('Reset to default design'),
			'115'=> $lumise->lang('You are editting an item from your shopping cart'),
			'116'=> $lumise->lang('Your cart item was changed'),
			'117'=> $lumise->lang('Cancel cart editting'),
			'118'=> $lumise->lang('Your cart item has been updated successful'),
			'119'=> $lumise->lang('A cart item is being edited. Do you want to change product for starting new design?'),
			'120'=> $lumise->lang('Error: Cart item not found'),
			'121'=> $lumise->lang('Are you sure to delete this item?'),
			'122'=> $lumise->lang('You are viewing the design of the order'),
			'123'=> $lumise->lang('Error, could not load the design file of this order'),
			'124'=> $lumise->lang('Yes, Start New'),
			'125'=> $lumise->lang('No, Update Current'),
			'126'=> $lumise->lang('Attribute name exists, please enter new name.'),
			'127'=> $lumise->lang('Warning: Please fix issues on attributes marked as red before submit.'),
			'128'=> $lumise->lang('Owhh, Please slow down. You seem to be sharing too much, waiting a few more minutes to continue'),
			'129'=> $lumise->lang('Oops, no item found'),
			'130'=> $lumise->lang('Copy link'),
			'131'=> $lumise->lang('Open link'),
			'132'=> $lumise->lang('Delete link'),
			'133'=> $lumise->lang('Are you sure that you want to delete this link?'),
			'134'=> $lumise->lang('There is no more item'),
			'135'=> $lumise->lang('The link has been copied successfully'),
			'136'=> $lumise->lang('The share link has been loaded successfully'),
			'137'=> $lumise->lang('Less'),
			'138'=> $lumise->lang('Advanced SVG Editor'),
			'139'=> $lumise->lang('Colors Manage'),
			'140'=> $lumise->lang('Add to list'),
			'141'=> $lumise->lang('Select all'),
			'142'=> $lumise->lang('Unselect all'),
			'143'=> $lumise->lang('List of your colors'),
			'144'=> $lumise->lang('No item found, please add new color to your list'),
			'145'=> $lumise->lang('Add new color'),
			'146'=> $lumise->lang('Delete selection'),
			'147'=> $lumise->lang('The size of images you upload is invalid, your upload size is'),
			'148'=> $lumise->lang('Your file upload is not allowed, please upload image files only'),
			'149'=> $lumise->lang('Your total quantity is less than the minimum quantity'),
			'150'=> $lumise->lang('Your total quantity is larger than maximum quantity'),
			'151'=> $lumise->lang('Enter the new label'),
			'152'=> $lumise->lang('Enter number of color'),
			'153'=> $lumise->lang('Add more column'),
			'154'=> $lumise->lang('Reduce  column'),
			'155'=> $lumise->lang('Enter the label'),
			'156'=> $lumise->lang('Average'),
			'157'=> $lumise->lang('item'),
			'158'=> $lumise->lang('Click or drag to add shape'),
			'159'=> $lumise->lang('Upload completed, please wait for a moment'),
			'160'=> $lumise->lang('Failure to add: The minimum dimensions requirement'),
			'161'=> $lumise->lang('Redirecting..'),
			'162'=> $lumise->lang('Error: You can not delete the last item'),
			'163'=> $lumise->lang('Error: Exceeded maximum allowed number of Stages'),
			'164'=> $lumise->lang('Error: You must select a color before upload image'),
			'165'=> $lumise->lang('Could not find your upload image'),
			'166'=> $lumise->lang('Error, could not load the design'),
			'167'=> $lumise->lang('Enter the size of editzone width x height in px'),
			'168'=> $lumise->lang('Constrain aspect ratio'),
			'169'=> $lumise->lang('No items found! Please upload new image or select the samples.'),
			'170'=> $lumise->lang('Delete this image will affect the products that are using it. Are you sure that you want to delete?'),
			'171'=> $lumise->lang('Click to edit image name'),
			'172'=> $lumise->lang('Your cart has been updated'),
			'173'=> $lumise->lang('View cart details'),
			'174'=> $lumise->lang('Start new product'),
			'175'=> $lumise->lang('Checkout Now'),
			'176'=> $lumise->lang('Dismiss and continue design'),
			'177'=> $lumise->lang('I understand and agree with the Terms & Conditions'),
			'178'=> $lumise->lang('Choose an option'),
			'179'=> $lumise->lang('Please select required product options before continue.'),
			'180'=> $lumise->lang('Price calculation formula'),
			'181'=> $lumise->lang('per 1 quantity'),
			'182'=> $lumise->lang('Base price'),
			'183'=> $lumise->lang('External'),
			'184'=> $lumise->lang('You have successfully exited the edit mode!'),
			'185'=> $lumise->lang('Clear designs'),
			'186'=> $lumise->lang('You are editing your custom design'),
			'187'=> $lumise->lang('Cancel editing'),
			'188'=> $lumise->lang('Your design were saved at'),
			'189'=> $lumise->lang('You can save or update your curent design for later use'),
			'190'=> $lumise->lang('Save to MyDesigns'),
			'191'=> $lumise->lang('Number stages'),
			'192'=> $lumise->lang('You are viewing the designs of order'),
			'193'=> $lumise->lang('Price, quantity and printing are affected by the variation'),
			'194'=> $lumise->lang('Failure to add: Your image has low quality, the minimum PPI requirement'),
			'195'=> $lumise->lang('Failure to add: The maximum PPI requirement'),
			'196'=> $lumise->lang('Please render all pages before downloading'),
			'197'=> $lumise->lang('Waring: Your image resolution is too low, it will affect print quality'),
			'198'=> $lumise->lang('Addition price from multi quantity'),
			'199'=> $lumise->lang('Attributes'),
			'200'=> $lumise->lang('Filter by'),
			'201'=> $lumise->lang('Apply for'),
			'202'=> $lumise->lang('New value'),
			'203'=> $lumise->lang('Bulk edit variations'),
			'204'=> $lumise->lang('It has been successfully applied to %s variations'),
			'205'=> $lumise->lang('Min Qty'),
			'206'=> $lumise->lang('Max Qty'),
			'207'=> $lumise->lang('Description'),
			'208'=> $lumise->lang('Error, no configuration for the product you choose'),
			'209'=> $lumise->lang('Error, no configuration for this product'),
			'210'=> $lumise->lang('Error! Please design all stages before adding to cart'),
			'211'=> $lumise->lang('Save your design for later use'),
			'212'=> $lumise->lang('USE THIS'),
			'213'=> $lumise->lang('OVERWRITE THIS'),
	    );

	    $this->default_fonts = $lumise->cfg->settings['google_fonts'];
		
		if ($lumise->cfg->settings['components'] === null) {
			$lumise->cfg->settings['components'] = array('product', 'templates', 'cliparts', 'text', 'uploads', 'layers');
			$lumise->set_option('components', $lumise->cfg->settings['components']);
		}
		
		$lumise->do_action('config_init');
		
    }

}

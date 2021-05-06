<?php
/**
*
*	(p) package: Lumise addons.php
*	(c) author:	King-Theme
*	(i) website: https://www.lumise.com
*
*/
if(!defined('LUMISE')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

/*
*	Extensions class
*/

class lumise_addons {
	
	private $tab = 'store';
	private $page = 1;
	private $path = '';
	private $main;
	private $errors = array();
	protected $actives = array();
	
	function __construct($lumise){
		
		$this->storage = new stdClass();
		
		$this->main = $lumise;
		$this->path = $lumise->cfg->upload_path.'addons'.DS;
		
		$this->load_addons();
		
	}
	
	/*
	*	START ASSIST
	*/
	
	public function get_url($file = '') {
		
		global $lumise;
		
		$key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
		$path = debug_backtrace()[$key]['file'];
		$url = $lumise->cfg->upload_url.'addons/'.basename(dirname($path));
		
		if (!empty($file))
			$url .= '/'.$file;
		
		return $url;
		
	}
	
	public function get_path($file = '') {
		
		$key = array_search(__FUNCTION__, array_column(debug_backtrace(), 'function'));
		$path = dirname(debug_backtrace()[$key]['file']);
		
		if (!empty($file))
			$path .= DS.$file;
			
		return $path;
		
	}
	
	public function add_component($arg) {
		global $lumise;
		$lumise->cfg->editor_menus($arg);
	}
	
	public function render_xitems($arg) {
		
		global $lumise;
		
		$html = '';
		
		$comp = isset($arg['component']) ? $arg['component'] : 'xitems';
		
		if (!isset($arg['search']))
			$arg['search'] = true;
			
		if (!isset($arg['category']))
			$arg['category'] = true;
			
		if (!isset($arg['preview']))
			$arg['preview'] = true;
		
		if (!isset($arg['price']))
			$arg['price'] = true;
		
		if ($arg['search'] === true || $arg['category'] === true) {
			$html .= '<header class="lumise-xitems-header">';
			if ($arg['search'] === true) {
				$html .= '<span class="lumise-xitems-search">'.
							'<input type="search" data-component="'.$comp.'" id="lumise-'.$comp.'-search-inp" '.
								'placeholder="'.$lumise->lang('Search').' '.$comp.'" />'.
							'<i class="lumisex-android-search"></i>'.
						'</span>';
			}
			if ($arg['category'] === true) {	
				$html .= '<div class="lumise-xitem-categories" data-prevent-click="true">'.
							'<button data-func="show-categories" data-type="'.$comp.'">'.
								'<span>'.$lumise->lang('All categories').'</span>'.
								'<i class="lumisex-ios-arrow-forward"></i>'.
							'</button>'.
						'</div>';
			}
			$html .= '</header>';
		}
		
		$html .= '<div id="lumise-'.$comp.'-list" data-component="'.$comp.'" class="smooth lumise-xitems-list'.
			($arg['preview'] !== true ? ' nopreview' : '').
			($arg['price'] !== true ? ' noprice' : '').
			($arg['search'] === false ? ' nosearch' : '').
			($arg['category'] === false ? ' nocategory' : '').
			'">'.(isset($arg['after_header']) ? $arg['after_header'] : '').
					'<ul class="lumise-list-items lumise-list-xitems">'.
						(isset($arg['list']) ? $arg['list'] : '').
						'<i class="lumise-spinner white x3 mt2"></i>'.
					'</ul>'.
				'</div>';
				
		return $html;
		
	}
	
	public function access_corejs($name) {
		global $lumise;
		$lumise->cfg->access_core($name);
	}
	
	public function is_backend() {
		if (defined('LUMISE_ADMIN') && LUMISE_ADMIN === true)
			return true;
		return false;
	}
	
	/*
	*	END ASSIST
	*/
	
	public function extensions_upload ($page = 1) {
		
		$upload = $this->upload_extension;
		$errors = $this->errors;
		include 'extensions/kc.upload.tmpl.php';
		
	}
	
	public function load_installed ($mod = 'all') {
		
		if (!is_dir($this->path) && !mkdir($this->path, 0755)) {
			echo '<center><h2 style="color: #888; margin-top: 50px">'.$this->main->lang('Error, could not create extensions folder '.$this->path).'</h2></center>';
			return;
		}
		
		$this->process_action();
		
		$items = array();
		$files = @scandir($this->path, 0);
		if($files == FALSE){
			$files = array();
		}
		foreach ($files as $file) {
			
			if (is_dir($this->path.$file) && $file != '.' && $file != '..') {
				
				if (file_exists($this->path.$file.DS.'index.php')) {
					
					$data = $this->main->lib->get_file_data($this->path.$file.DS.'index.php', array(
						'Name',
						'Description',
						'Version',
						'Compatible',
						'Platform'
					));
					
					$items[$file] = array(
						'Name' => !empty($data[0]) ? $data[0] : 'Unknow',
						'Description' => !empty($data[1]) ? $data[1] : '',
						'Version' => !empty($data[2]) ? $data[2] : '1.0',
						'Compatible' => !empty($data[3]) ? $data[3] : '1.0',
						'Platform' => isset($data[4]) && !empty($data[4]) ? $data[4] : '',
						'Slug' => $file
					);
				}
			}
		}
		
		return $items;
		
	}

	public function addon_installed_list() {
		
		if (!is_dir($this->path) && !mkdir($this->path, 0755)) {
			return false;
		}
		
		$items = array();
		$files = scandir($this->path, 0);
		
		foreach ($files as $file) {
			
			if (is_dir($this->path.$file) && $file != '.' && $file != '..') {
				
				if (file_exists($this->path.$file.DS.'index.php')) {
					
					$data = $this->main->lib->get_file_data($this->path.$file.DS.'index.php', array(
						'Name',
						'Description',
						'Version',
						'Compatible',
						'Platform'
					));
					
					$items[$file] = array(
						'Name' => !empty($data[0]) ? $data[0] : 'Unknow',
						'Description' => !empty($data[1]) ? $data[1] : '',
						'Version' => !empty($data[2]) ? $data[2] : '1.0',
						'Compatible' => !empty($data[3]) ? $data[3] : '1.0',
						'Platform' => isset($data[4]) && !empty($data[4]) ? $data[4] : '',
						'Slug' => $file
					);
				}
			}
		}
		
		return $items;
		
	}
	
	public function load_addons () {
		
		$actives = $this->main->get_option( 'active_addons');
		$lib = $this->main->lib;
		
		if ($actives !== null && !empty($actives))
			$actives = (Array)@json_decode($actives);
		
		if (!is_array($actives))
			$actives = array();
			
		foreach ($actives as $name => $stt) {
			
			if ($stt == 1) {
				
				$file = $this->path.$name.DS.'index.php';
				
				if (file_exists($file)) {
					
					$data = $lib->get_file_data($file, array('Compatible', 'Platform'));
					
					if (
						isset($data[1]) && 
						!empty($data[1]) && 
						strpos($data[1], $this->main->connector->platform) === false
					) {
						
						array_push(
							$this->errors, 
							'Error: The addon <strong>'.$name.'</strong> does not compatible with your Lumise platform (only support '.$data[1].')'
						);
						
						unset($actives[$name]);
						$this->main->set_option('active_addons', json_encode($actives));
						
					} else if (version_compare(LUMISE, $data[0]) >= 0) {
						
						require_once($this->path.$name.DS.'index.php');
						
						$slug = $this->main->lib->sanitize_title($name);
						$slug = str_replace('-', '_', $slug);
						
						$ex_class = 'lumise_addon_'.$slug;
						
						if (class_exists($ex_class)) {
							$this->storage->{$slug} = new $ex_class();
						} else {
							array_push(
								$this->errors, 
								'Could not find the PHP classname "lumise_addon_'.$slug.'" in the addon file "/'.$name.DS.'index.php"'
							);
							unset($actives[$name]);
							$this->main->set_option('active_addons', json_encode($actives));
						}
						
					}else {
						array_push(
							$this->errors, 
							'Error: The addon <strong>'.$name.'</strong> does not compatible with your Lumise '.LUMISE
						);
						unset($actives[$name]);
						$this->main->set_option('active_addons', json_encode($actives));
					}
					
				} else {
					array_push($this->errors, 'Could not find the extension file /'.$name.DS.'index.php');
					unset($actives[$name]);
					$this->main->set_option('active_addons', json_encode($actives));
				}
			}
		}
		
		$this->actives = $actives;
		
		if (count($this->errors) > 0)
			$this->main->connector->set_session('lumise_msg', array("status" => "error", "errors" => $this->errors));
		
		return $this->errors;
		
	}
	
	public function process_action() {

		if (isset($_POST['action'])) {
			
			if (!$this->main->caps('lumise_can_upload')) {
				echo '<div class="lumise_wrapper" id="lumise-product-page">
							<div class="lumise_content">
								<div class="lumise_message err">
									<em class="lumise_err">
										<i class="fa fa-times"></i>  Sorry, You do not have permission to do action
									</em>	
							</div>
						</div>
					</div>';
				exit;
			}
			
			$actives = $this->main->get_option( 'active_addons');
							
			if ($actives !== null && !empty($actives))
				$actives = (Array)@json_decode($actives);
			
			if (!is_array($actives))
				$actives = array();
			
			$checked = isset($_POST['id_action']) ? explode(',', $_POST['id_action']) : array();
			
			switch ($_POST['action']){
				
				case 'active' :
					
					foreach( $checked as $ext ) {
						$act = $this->active_addon($ext);
						if (!empty($act['error'])) {
							array_push($this->errors, $act['error']);
						}
					}
					
					if (count($this->errors) > 0)
						$this->main->connector->set_session('lumise_msg', array("status" => "error", "errors" => $this->errors));
					
				break;
				
				case 'deactive' :
					
					foreach( $checked as $ext ) {
						$this->deactive_addon($ext);
					}
					
					break;
				
				case 'delete' :
				
					foreach( $checked as $ext ) {
						
						$this->deactive_addon($ext);
						
						if (is_dir($this->path.$ext))
							$this->main->lib->remove_dir($this->path.$ext);
							
					}
					
				break;
				
				case 'upload' : 
					
					$_f = $_FILES["addonzip"];
					
					if (!class_exists('ZipArchive')) {
						array_push($this->errors, 'Server does not support ZipArchive');
					} else if (
						(
							($_f["type"] == "application/zip") || 
							($_f["type"] == "application/x-zip") || 
							($_f["type"] == "application/x-zip-compressed")
						) && 
						($_f["size"] < 20000000)
					) {
						
						$file = $this->path.$_f['name'];
						
						if (move_uploaded_file($_f['tmp_name'], $file) === true) {
							
							$zip = new ZipArchive;
							$res = $zip->open($file);
							
							if ($res === TRUE) {
								
								$ext = $zip->extractTo($this->path);
								
								if (is_dir($this->path.'__MACOSX'))
									$this->main->lib->remove_dir($this->path.'__MACOSX');
									
								if ($ext ===true) {
								
									$ext = trim($zip->getNameIndex(0), DS);
									
									if (!file_exists($this->path.$ext.DS.'index.php'))
										$this->errors[] = $this->main->lang('Upload Error: Missing index.php file of extension');
									
								} else array_push($this->errors, $this->main->lang('Could not extract file'));
								
								$zip->close();
								
							} else array_push($this->errors, $this->main->lang('Could not unzip'));
							
							@unlink($file);
							
						} else {
							array_push(
								$this->errors, 
								$this->main->lang('Error upload file')
							);
						}
					} else {
						array_push(
							$this->errors, 
							$this->main->lang('Invalid file type')
						);
					}
					
					if (count($this->errors) > 0) {
						$msg = array(
							"status" => "error", 
							"errors" => $this->errors
						);
					} else { 
						$msg = array(
							"status" => "success", 
							"msg" => $this->main->lang('Your addon has been uploaded successfull')
						);
					}
					
					$this->main->connector->set_session('lumise_msg', $msg);
					
				break;
				
			}
			
		}
	
	}
	
	public function active_addon($ext) {
		
		$actives = $this->main->get_option('active_addons');
		$msg = ''; $error = '';
						
		if ($actives !== null && !empty($actives))
			$actives = (Array)@json_decode($actives);
		
		if (!is_array($actives))
			$actives = array();
		
		if (file_exists($this->path.$ext.DS.'index.php')) {
			
			$data = $this->main->lib->get_file_data(
				$this->path.$ext.DS.'index.php', 
				array('Compatible', 'Platform')
			);
			
			$platform = isset($data[1]) ? trim($data[1]) : '';
			
			if (
				!empty($platform) && 
				strpos(strtolower($platform), $this->main->connector->platform) === false
			) {
				$error = 'Active Error: The addon <strong>'.$ext.'</strong> does not support your Lumise platform (only support '.$data[1].')';
			} else if (version_compare(LUMISE, $data[0]) >= 0) {
				
				require_once($this->path.$ext.DS.'index.php');
				
				$ex_class = 'lumise_addon_'.str_replace('-', '_', $ext);
				
				if (class_exists($ex_class)) {
					
					$actives[$ext] = 1;
					
					if (method_exists($ex_class, 'active')) {
						ob_start();
						$ex_class::active();
						$msg = ob_get_contents();
						ob_end_clean();
					}
					
				} else $error = 'Active Error: '.$ext.'/index.php is missing the addon class '.$ex_class;
				
			} else $error = 'Active Error: The addon <strong>'.$ext.'</strong> does not compatible with your Lumise '.LUMISE;
			
		} else $error = 'Active Error: The index.php of addon '.$ext.' could not found';
		
		if (isset($actives[$ext]) && $actives[$ext] !== 1)
			unset($actives[$ext]);
		
		$this->main->set_option('active_addons', json_encode($actives));
		
		return array(
			"msg" => $msg,
			"status" => isset($actives[$ext]) ? $actives[$ext] : 0,
			"error" => $error
		);
							
	}
	
	public function deactive_addon($ext) {
		
		$actives = $this->main->get_option('active_addons');
		$msg = ''; $ex_class = 'lumise_addon_'.str_replace('-', '_', $ext);
							
		if ($actives !== null && !empty($actives))
			$actives = (Array)@json_decode($actives);
		
		if (!is_array($actives))
			$actives = array();
		
		if (class_exists($ex_class) && method_exists($ex_class, 'deactive')) {
			ob_start();
			$ex_class::deactive();
			$msg = ob_get_contents();
			ob_end_clean();
		} 
		
		if (isset($actives[$ext]))		
			unset($actives[$ext]);
		
		$this->main->set_option('active_addons', json_encode($actives));
		
		return array(
			"msg" => $msg,
			"status" => 0,
			"error" => ""
		);
							
	}
	
}

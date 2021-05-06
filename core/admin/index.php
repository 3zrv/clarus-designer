<?php
//defines

if (!defined('TS'))
	define('TS', DIRECTORY_SEPARATOR);

if (!defined('LUMISE_ADMIN_PATH'))
    define('LUMISE_ADMIN_PATH', dirname(__FILE__));

if (!defined('LUMISE_ADMIN'))
    define('LUMISE_ADMIN', true);

date_default_timezone_set('UTC');

class lumise_router {
	
	protected $_action;
	protected $_load_assets;
	protected $_asset_uri;
	protected $_allow = false;
	protected $_is_ajax = false;
	protected $_lumise = false;
	protected $_cfg = false;
	protected $_admin_path;
	
	public $menus;
	public $check_update;
	
	public function __construct($lumise_page = '', $load_assets = true) {

        global $lumise;
        $this->_lumise = $lumise;
        $this->_admin_path = $lumise->cfg->root_path.'admin'.DS;
		
		$this->check_update = @json_decode($lumise->get_option('last_check_update'));
		$this->menus =  $lumise->apply_filters('admin_menus', array(
			'dashboard' => array(
				'title' => $lumise->lang('Dashboard'),
				'icon'  => '<i class="fa fa-home"></i>',
				'link'   => $lumise->cfg->admin_url.'lumise-page=dashboard',
				'child'	=> array(
					'dashboard' => array(
						'title' => $lumise->lang('Home'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=dashboard',
					),
					'updates' => array(
						'title' => $lumise->lang('Updates').(!empty($this->check_update) && isset($this->check_update->version) && version_compare(LUMISE, $this->check_update->version, '<') ? ' <span class="update-notice">1</span>' : ''),
						'link'   => $lumise->cfg->admin_url.'lumise-page=updates',
					),
					'license' => array(
						'title' => $lumise->lang('License'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=license',
					),
					'system' => array(
						'title' => $lumise->lang('Status'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=system',
					)
				),
				'capability' => 'lumise_read_dashboard'
			),
			'products' => array(
				'title' => $lumise->lang('Products Base'),
				'icon'  => '<i class="fa fa-cube"></i>',
				'child' => array(
					'products'   => array(
						'type'   => '',
						'title'  => $lumise->lang('All Products Base'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=products',
						'hidden' => false,
					),
					'product' => array(
						'type'   => '',
						'title'  => $lumise->lang('Add New Product Base'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=product',
						'hidden' => false,
					),
					'categories' => array(
						'type'   => 'products',
						'title'  => $lumise->lang('Product Categories'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=categories&type=products',
						'hidden' => false,
					),
					'category' => array(
						'type'   => 'products',
						'title'  => $lumise->lang('Add New Category'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=category&type=products',
						'hidden' => true,
					),
				),
				'capability' => 'lumise_read_products'
			),
			'templates' => array(
				'title' => $lumise->lang('Design Templates'),
				'icon'  => '<i class="fa fa-paper-plane-o"></i>',
				'child' => array(
					'templates'   => array(
						'type'   => '',
						'title'  => $lumise->lang('All Templates'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=templates',
						'hidden' => false,
					),
					'template' => array(
						'type'   => '',
						'title'  => $lumise->lang('Add New Template'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=template',
						'hidden' => false,
					),
					'categories' => array(
						'type'   => 'templates',
						'title'  => $lumise->lang('Categories'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=categories&type=templates',
						'hidden' => false,
					),
					'category' => array(
						'type'   => 'templates',
						'title'  => $lumise->lang('Add New Category'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=category&type=templates',
						'hidden' => true,
					),
					'tags' => array(
						'type'   => 'templates',
						'title'  => $lumise->lang('Tags'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=tags&type=templates',
						'hidden' => false,
					),
					'tag' => array(
						'type'   => 'templates',
						'title'  => $lumise->lang('Add New Tag'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=tag&type=templates',
						'hidden' => true,
					),
				),
				'capability' => 'lumise_read_templates'
			),
			'cliparts' => array(
				'title' => $lumise->lang('Cliparts'),
				'icon'  => '<i class="fa fa-file-image-o"></i>',
				'child' => array(
					'cliparts'   => array(
						'type'   => '',
						'title'  => $lumise->lang('All Cliparts'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=cliparts',
						'hidden' => false,
					),
					'clipart' => array(
						'type'   => '',
						'title'  => $lumise->lang('Add New Clipart'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=clipart',
						'hidden' => false,
					),
					'categories' => array(
						'type'   => 'cliparts',
						'title'  => $lumise->lang('Categories'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=categories&type=cliparts',
						'hidden' => false,
					),
					'category' => array(
						'type'   => 'cliparts',
						'title'  => $lumise->lang('Add New Category'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=category&type=cliparts',
						'hidden' => true,
					),
					'tags' => array(
						'type'   => 'cliparts',
						'title'  => $lumise->lang('Tags'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=tags&type=cliparts',
						'hidden' => false,
					),
					'tag' => array(
						'type'   => 'cliparts',
						'title'  => $lumise->lang('Add New Tag'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=tag&type=cliparts',
						'hidden' => true,
					),
				),
				'capability' => 'lumise_read_cliparts'
			),
			'shapes' => array(
				'title' => $lumise->lang('Shapes'),
				'icon'  => '<i class="fa fa-cube"></i>',
				'child' => array(
					'shapes'   => array(
						'type'   => '',
						'title'  => $lumise->lang('All shapes'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=shapes',
						'hidden' => false,
					),
					'shape' => array(
						'type'   => '',
						'title'  => $lumise->lang('Add New Shape'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=shape',
						'hidden' => false,
					),
				),
				'capability' => 'lumise_read_shapes'
			),
			'printings' => array(
				'title' => $lumise->lang('Printing Type'),
				'icon'  => '<i class="fa fa-print"></i>',
				'child' => array(
					'printings'   => array(
						'type'   => '',
						'title'  => $lumise->lang('All Printing Type'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=printings',
						'hidden' => false,
					),
					'printing' => array(
						'type'   => '',
						'title'  => $lumise->lang('Add New Printing'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=printing',
						'hidden' => false,
					),
				),
				'capability' => 'lumise_read_printings'
			),
			'fonts' => array(
				'title' => $lumise->lang('Fonts'),
				'icon'  => '<i class="fa fa-font"></i>',
				'child' => array(
					'fonts'   => array(
						'type'   => '',
						'title'  => $lumise->lang('All Fonts'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=fonts',
						'hidden' => false,
					),
					'font' => array(
						'type'   => '',
						'title'  => $lumise->lang('Add New Font'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=font',
						'hidden' => false,
					),
				),
				'capability' => 'lumise_read_fonts'
			),
			'languages' => array(
				'title' => $lumise->lang('Languages'),
				'icon'  => '<i class="fa fa-language"></i>',
				'child' => array(
					'languages'   => array(
						'type'   => '',
						'title'  => $lumise->lang('Languages'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=languages',
						'hidden' => false,
					),
					'language' => array(
						'type'   => '',
						'title'  => $lumise->lang('Add Translate Text'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=language',
						'hidden' => false,
					),
				),
				'capability' => 'lumise_read_languages'
			),
			'orders' => array(
				'title' => $lumise->lang('Orders'),
				'icon'  => '<i class="fa fa-shopping-bag"></i>',
				'link'   => $lumise->cfg->admin_url.'lumise-page=orders',
				'child' => array(
					'orders'   => array(
						'type'   => '',
						'title'  => $lumise->lang('All Orders'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=orders',
						'hidden' => false,
					),
					'order'   => array(
						'type'   => '',
						'title'  => $lumise->lang('Order'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=order',
						'hidden' => true,
					)
				),
				'capability' => 'lumise_read_orders'
			),
			'shares' => array(
				'title' => $lumise->lang('Shares'),
				'icon'  => '<i class="fa fa-share-alt"></i>',
				'link'   => $lumise->cfg->admin_url.'lumise-page=shares',
				'capability' => 'lumise_read_shares'
			),
			'bugs' => array(
				'title' => $lumise->lang('Bugs'),
				'icon'  => '<i class="fa fa-bug"></i>',
				'child' => array(
					'bugs'   => array(
						'type'   => '',
						'title'  => $lumise->lang('All Bugs'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=bugs',
						'hidden' => false,
					),
					'bug' => array(
						'type'   => '',
						'title'  => $lumise->lang('Add New Bug'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=bug',
						'hidden' => false,
					),
				),
				'capability' => 'lumise_read_bugs'
			),
			'addons' => array(
				'title' => $lumise->lang('Addons'),
				'icon'  => '<i class="fa fa-plug"></i>',
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
					'addon' => array(
						'type'   => '',
						'title'  => $lumise->lang('Detail'),
						'link'   => $lumise->cfg->admin_url.'lumise-page=addon',
						'hidden' => true,
					),
				),
				'capability' => 'lumise_read_addons',
			),
			'settings' => array(
				'title' => $lumise->lang('Settings'),
				'icon'  => '<i class="fa fa-cog"></i>',
				'link'   => $lumise->cfg->admin_url.'lumise-page=settings',
				'capability' => 'lumise_read_settings'
			),
		));
		
		$this->_load_assets = $load_assets;

		if (empty($lumise_page) && isset($_REQUEST['lumise-page']))
			$this->_lumise_page = $_REQUEST['lumise-page'];
		else
			$this->_lumise_page = 'dashboard';
		
		foreach ($this->menus as $key => $menu) {
			
			if ($key == $this->_lumise_page)
				$this->_allow = true;
			if (isset($menu['child']) && is_array($menu['child'])) {
				foreach ($menu['child'] as $k => $ch) {
					if ($k == $this->_lumise_page)
						$this->_allow = true;
				}
			}
		}
			
        if ((isset($_REQUEST['lumise_ajax']) && $_REQUEST['lumise_ajax'] == 1) || $_SERVER['REQUEST_METHOD'] =='POST')
            $this->_is_ajax = true;
		
		$this->check_caps();
		
	}
	
	public function check_caps () {
		
		global $lumise;
		$page = isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '';
		
		$cap = (
			isset($this->menus[$page]) && 
			isset($this->menus[$page]['capability'])
		) ? $this->menus[$page]['capability'].'-s' : '';
		
		if ($cap == '') {
			foreach ($this->menus as $key => $val) {
				if (
					isset($this->menus[$key]['capability']) &&
					isset($val['child']) && 
					isset($val['child'][$page])
				)$cap = $this->menus[$key]['capability'].'-s';
			}
		}
				
		$cap = str_replace(array('s-s', '-s'), array('', ''), $cap);
		$cap2 = str_replace('_read_', '_edit_', $cap);
		$cap3 = str_replace('_read_', '_edit_', $cap.'s');
		
		if (
			!$lumise->caps($cap) && 
			!$lumise->caps($cap.'s') && 
			!$lumise->caps($cap2) && 
			!$lumise->caps($cap3)
		) $this->_allow = false;
			
	}
	
	public function update_notice() {
		
		global $lumise;
		$lpage = isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '';
		
		if(
			$lpage != 'updates' && 
			$lpage != 'license' && 
			!empty($this->check_update) && 
			isset($this->check_update->version) && 
			version_compare(LUMISE, $this->check_update->version, '<')
		) {
		
		?>	
		<div class="lumise_container">
			<div class="lumise-col lumise-col-12">
				<div class="lumise-update-notice top">
					<a href="https://www.lumise.com/changelogs/<?php echo $lumise->connector->platform; ?>?utm_source=client-site&utm_medium=text&utm_campaign=update-page&utm_term=links&utm_content=<?php echo $lumise->connector->platform; ?>" target=_blank>Lumise <?php echo $this->check_update->version; ?></a> 
					<?php echo $lumise->lang('is available'); ?>! 
					<a href="<?php echo $lumise->cfg->admin_url; ?>lumise-page=updates"><?php echo $lumise->lang('Please update now'); ?></a>.
				</div>
			</div>
		</div>
		<?php 
			
		}
			
	}
	
	public function display() {

		global $lumise_router, $lumise, $lumise_helper, $lumise_admin;
		
		$lumise->do_action('admin-verify');
		
		require_once($this->_admin_path.'admin.php');
		
        if(!isset($_POST['do']) && !isset($_POST['action_submit'])) {
            include($this->_admin_path . 'partials' .DS. 'header.php');
		}
		
		$page = $lumise->apply_filters('admin_page', $this->_admin_path . 'pages' .DS. $this->_lumise_page . '.php', $this->_lumise_page);
		
		if ($this->_allow && is_file($page)) {
			$this->update_notice();
			include($page);
		} else {
			echo '<div class="lumise_container">';
			echo '<div class="lumise-col lumise-col-12">';
			echo '<div class="lumise-update-notice top">';
			if (!$this->_allow && is_file($page))
				echo $lumise->lang('Sorry, you are not allowed to access this page.');
			else echo $lumise->lang('File not found').': <i>'.$page.'</i>';
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}
		
        if(!isset($_POST['do'])) {
             include($this->_admin_path . 'partials' .DS. 'footer.php');
		}
	}

}

/*===================================*/

global $lumise_router;
$lumise_router = new lumise_router();
$lumise_router->display();

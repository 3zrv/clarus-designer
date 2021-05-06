<?php
	
	$langs = $lumise->get_langs();
	$lang_map = $lumise->langs();
	
	$active_langs = array();
	$use_langs = array('en' => 'English');
	
	foreach($langs as $code) {
		if (!empty($code)) {
			$active_langs[$code] = '<img src="'.$lumise->cfg->assets_url.'assets/flags/'.$code.'.png" height="20" /> '.$lang_map[$code];
			if (is_array($lumise->cfg->settings['activate_langs']) && in_array($code, $lumise->cfg->settings['activate_langs']))
				$use_langs[$code] = $lang_map[$code];
		}
	}
	$section = 'settings';
	
	$components = array();
	foreach ($lumise->cfg->editor_menus as $key => $menu) {
		$components[$key] = $menu['label'];
	}
	
	$components = array_merge($components, array(
		'shop' => $lumise->lang('Shopping cart'),
		'back' => $lumise->lang('Back to Shop'),
	));
	
	$arg = array(
		
		'tabs' => array(
			
			'general:' . $lumise->lang('General') => array(
				array(
					'type' => 'upload',
					'name' => 'logo',
					'label' => $lumise->lang('Upload logo'),
					'path' => 'settings'.DS,
					'desc' => $lumise->lang('Upload your own logo to display in the editor (recommented height 80px)')
				),
				array(
					'type' => 'input',
					'name' => 'logo_link',
					'label' => $lumise->lang('Logo url'),
					'desc' => $lumise->lang('The link will be redirect when click on the logo'),
				),
				array(
					'type' => 'input',
					'name' => 'title',
					'label' => $lumise->lang('Site title'),
					'desc' => $lumise->lang('The title of browser'),
				),
				array(
					'type' => 'upload',
					'name' => 'favicon',
					'label' => $lumise->lang('Upload favicon'),
					'path' => 'settings'.DS,
					'desc' => $lumise->lang('Upload your favicon to display in the editor (recommented .PNG and height 50px)')
				),
				array(
					'type' => 'color',
					'name' => 'primary_color',
					'label' => $lumise->lang('Theme color'),
					'default' => '#3fc7ba:#546e7a,#757575,#6d4c41,#f4511e,#fb8c00,#ffb300,#fdd835,#c0cA33,#a0ce4e,#7cb342,#43a047,#00897b,#00acc1,#3fc7ba,#039be5,#3949ab,#5e35b1,#8e24aa,#d81b60,#eeeeee,#3a3a3a'
				),
				array(
					'type' => 'text',
					'name' => 'conditions',
					'label' => $lumise->lang('Terms & conditions'),
					'desc' => $lumise->lang('The terms and conditions show before placing the order (option)'),
				),
			),
			'editor:' . $lumise->lang('Editor') => array(
				array(
					'type' => 'toggle',
					'name' => 'enable_colors',
					'label' => $lumise->lang('Color picker'),
					'desc' => $lumise->lang('Allow users select colors from the color picker and users can add new color to their colors list'),
					'default' => 'yes',
					'value' => null
				),
				array(
					'type' => 'color',
					'name' => 'colors',
					'selection' => false,
					'label' => $lumise->lang('List colors'),
					'default' => '#3fc7ba:#546e7a,#757575,#6d4c41,#f4511e,#fb8c00,#ffb300,#fdd835,#c0cA33,#a0ce4e,#7cb342,#43a047,#00897b,#00acc1,#3fc7ba,#039be5,#3949ab,#5e35b1,#8e24aa,#d81b60,#eeeeee,#3a3a3a',
					'desc' => $lumise->lang('The default colors are used to fill objects'),
				),
				array(
					'type' => 'toggle',
					'name' => 'rtl',
					'label' => $lumise->lang('Right to left (RTL)'),
					'desc' => $lumise->lang('Enable right to left reading mode'),
					'default' => 'no',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'share',
					'label' => $lumise->lang('User can sharing'),
					'desc' => $lumise->lang('Allow non-admin users to share their designs'),
					'default' => 'no',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'user_print',
					'label' => $lumise->lang('User can print'),
					'desc' => $lumise->lang('Allow non-admin users to print their designs'),
					'default' => 'no',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'user_download',
					'label' => $lumise->lang('User can download'),
					'desc' => $lumise->lang('Allow non-admin users to download their designs as a file'),
					'default' => 'yes',
					'value' => null
				),
				array(
					'type' => 'checkboxes',
					'name' => 'components',
					'label' => $lumise->lang('Components'),
					'desc' => $lumise->lang('Show/hide components of editor, you also can arrange them as how you want'),
					'default' => '',
					'value' => null,
					'options' => $components
				),
				array(
					'type' => 'toggle',
					'name' => 'disable_resources',
					'label' => $lumise->lang('Disable resources'),
					'desc' => $lumise->lang('Disable online resources for none-admin users (Facebook, Instagram, Free images..)'),
					'default' => 'no',
					'value' => null
				),
				array(
					'type' => 'input',
					'name' => 'min_upload',
					'label' => $lumise->lang('Min size upload'),
					'desc' => $lumise->lang('The minimum size (kilobyte) that users can upload photos (eg: 100)'),
					'default' => '',
					'placeholder' => $lumise->lang('Enter number in KB'),
					'value' => null
				),
				array(
					'type' => 'input',
					'name' => 'max_upload',
					'label' => $lumise->lang('Max size upload'),
					'desc' => $lumise->lang('The maximum size (kilobyte) that users can upload photos (eg: 5000)'),
					'default' => '',
					'placeholder' => $lumise->lang('Enter number in KB'),
					'value' => null
				),
				array(
					'type' => 'input',
					'name' => 'min_dimensions',
					'label' => $lumise->lang('Min dimensions'),
					'desc' => $lumise->lang('The min width x height in pixel of images can be added'),
					'default' => '',
					'placeholder' => $lumise->lang('Enter dimensions width x height (eg: 100x100)'),
					'value' => null
				),
				array(
					'type' => 'input',
					'name' => 'max_dimensions',
					'label' => $lumise->lang('Max dimensions'),
					'desc' => $lumise->lang('The max width x height in pixel of images can be added, Automatically decreases if bigger'),
					'default' => '',
					'placeholder' => $lumise->lang('Enter dimensions width x height (eg: 500x500)'),
					'value' => null
				),
				array(
					'type' => 'input',
					'name' => 'min_ppi',
					'label' => $lumise->lang('Min PPI'),
					'desc' => $lumise->lang('The min PPI (pixel per inch) of images can be added (It depends on the size you have configured)'),
					'default' => '',
					'placeholder' => $lumise->lang('Recommened minimum 150 PPI'),
					'value' => null
				),
				array(
					'type' => 'input',
					'name' => 'max_ppi',
					'label' => $lumise->lang('Max PPI'),
					'desc' => $lumise->lang('The max PPI of images can be added (It depends on the size you have configured)'),
					'default' => '',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'ppi_notice',
					'label' => $lumise->lang('Low resolution notice'),
					'desc' => $lumise->lang('Allows to add low resolution images with a notice (when its resolution is lower than Min PPI)'),
					'default' => 'no',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'required_full_design',
					'label' => $lumise->lang('Design all stages'),
					'desc' => $lumise->lang('Required design all stages before add to cart'),
					'default' => 'no',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'auto_fit',
					'label' => $lumise->lang('Auto zoom to fit'),
					'desc' => $lumise->lang('Automatically zooms to fit the product with the screen'),
					'default' => 'yes',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'calc_formula',
					'label' => $lumise->lang('Show calculation formula'),
					'default' => 'yes',
					'value' => null
				),
				array(
					'type' => 'radios',
					'name' => 'report_bugs',
					'label' => $lumise->lang('Enable bug reporting'),
					'desc' => $lumise->lang('Allow users report bugs from editor'),
					'default' => '2',
					'value' => null,
					'options' => array(
						'0' => $lumise->lang('Disable'),
						'1' => $lumise->lang('Enable, but do not send to Lumise.com'),
						'2' => $lumise->lang('Enable, and send to Lumise.com'),
					)
				),
				array(
					'type' => 'text',
					'name' => 'custom_css',
					'label' => $lumise->lang('Custom CSS'),
					'desc' => $lumise->lang('Your custom CSS code will run in editor'),
				),
				array(
					'type' => 'text',
					'name' => 'custom_js',
					'label' => $lumise->lang('Custom JS'),
					'desc' => $lumise->lang('Your custom JS code will run in editor'),
				),
				array(
					'type' => 'input',
					'name' => 'prefix_file',
					'label' => $lumise->lang('Prefix name'),
					'desc' => $lumise->lang('The prefix of file name download'),
					'default' => 'Front'
				),
				// array(
				// 	'type' => 'toggle',
				// 	'name' => 'text_direction',
				// 	'label' => $lumise->lang('Text direction'),
				// 	'desc' => $lumise->lang('Fix the text direction when writing, left as default and right for RTL mode'),
				// 	'default' => 'no'
				// ),
				array(
					'type' => 'toggle',
					'name' => 'dis_qrcode',
					'label' => $lumise->lang('Disable QRCode'),
					'desc' => $lumise->lang('Do not show the QRCode generator'),
					'default' => '',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'auto_snap',
					'label' => $lumise->lang('Auto snap'),
					'desc' => $lumise->lang('Automatically align the position of the active object with other objects'),
					'default' => 'no'
				),
				array(
					'type' => 'toggle',
					'name' => 'template_append',
					'label' => $lumise->lang('Template append'),
					'desc' => $lumise->lang('Keep all current objects and append the template into'),
					'default' => '',
					'value' => null
				),
				array(
					'type' => 'toggle',
					'name' => 'replace_image',
					'label' => $lumise->lang('Replace image'),
					'desc' => $lumise->lang('Replace the selected image object instead of creating a new one'),
					'default' => '',
					'value' => null
				),
			),
			'shop:' . $lumise->lang('Shop') => array(
				array(
					'type' => 'input',
					'name' => 'currency',
					'label' => $lumise->lang('Currency symbol')
				),
				array(
					'type' => 'toggle',
					'name' => 'currency_position',
					'label' => $lumise->lang('Currency first?'),
					'desc' => $lumise->lang('Display the currency symbol before or after the price number'),
					'default' => 'yes',
					'value' => null
				),
				array(
					'type' => 'input',
					'name' => 'currency_code',
					'label' => $lumise->lang('Currency code'),
					'desc' => $lumise->lang('The currency code which use for payment'),
				),
				
				array(
					'type' => 'input',
					'name' => 'thousand_separator',
					'label' => $lumise->lang('Thousand separator'),
					'desc' => $lumise->lang('This sets the thousand separator of displayed price'),
				),
				array(
					'type' => 'input',
					'name' => 'decimal_separator',
					'label' => $lumise->lang('Decimal separator'),
					'desc' => $lumise->lang('This sets the decimal separator of displayed price'),
				),
				array(
					'type' => 'input',
					'numberic' => 'int',
					'name' => 'number_decimals',
					'label' => $lumise->lang('Number of decimals'),
					'desc' => $lumise->lang('This sets the number of decimals points show in displayed price'),
				)
			),
			'fonts:' . $lumise->lang('Google Fonts') => array(
				array(
					'type' => 'toggle',
					'name' => 'user_font',
					'label' => $lumise->lang('User can manage fonts'),
					'desc' => $lumise->lang('Allow non-admin users to add new or remove fonts to their browser'),
					'default' => 'yes',
					'value' => null
				),
				array(
					'type' => 'google_fonts',
					'name' => 'google_fonts',
					'label' => $lumise->lang('Default Google fonts'),
					'desc' => $lumise->lang('Users can add new or remove Google fonts in their profile when using the tool'),
				)
			),
			'languages:' . $lumise->lang('Languages') => array(
				array(
					'type' => 'dropbox',
					'name' => 'admin_lang',
					'label' => $lumise->lang('Backend language'),
					'options' => $use_langs
				),
				array(
					'type' => 'dropbox',
					'name' => 'editor_lang',
					'label' => $lumise->lang('Editor language'),
					'options' => $use_langs
				),
				array(
					'type' => 'toggle',
					'name' => 'allow_select_lang',
					'label' => $lumise->lang('Allow users change'),
					'desc' => $lumise->lang('Allow users selecting the language in the tool'),
					'default' => 1
				),
				array(
					'type' => 'checkboxes',
					'name' => 'activate_langs',
					'label' => $lumise->lang('Activate languages'),
					'options' => $active_langs,
					'desc' => '<a href="'.$lumise->cfg->admin_url.'lumise-page=languages"><i class="fa fa-plus"></i> '.$lumise->lang('Add new language ').'</a>'
				),
			),
			'help:' . $lumise->lang('Help contents') => array(
				array(
					'type' => 'input',
					'name' => 'help_title',
					'label' => $lumise->lang('Help title'),
					'desc' => $lumise->lang('This content will be display under menu "Help" on the editor'),
				),
				array(
					'type' => 'tabs',
					'name' => 'helps',
					'label' => $lumise->lang('Help contents'),
					'desc' => $lumise->lang('Add the content as plain text, rick text or HTML code, you can translate the title or the content to another language by creating new language text'),
					'tabs' => 5,
					'default' => '[{"title":"Hot keys","content":"<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">a<\/b>\r\nSelect all objects<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">d<\/b>\r\nDouble the activate object<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">e<\/b>\r\nClear all objects<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">s<\/b>\r\nSave current stage to my design<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">o<\/b>\r\nOpen a file to import design<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">p<\/b>\r\nPrint<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">+<\/b>\r\nZoom out<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">-<\/b>\r\nZoom in<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">0<\/b>\r\nReset zoom<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">z<\/b>\r\nUndo changes<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">shift<\/b>+<b data-view=\"key\">z<\/b>\r\nRedo changes<br>\r\n<b data-view=\"key\">ctrl<\/b>+<b data-view=\"key\">shift<\/b>+<b data-view=\"key\">s<\/b>\r\nDownload current design<br>\r\n<b data-view=\"key\">delete<\/b> Delete the activate object<br>\r\n<b data-view=\"key\">\u2190<\/b> Move the activate object to left<br>\r\n<b data-view=\"key\">\u2191<\/b> Move the activate object to top<br>\r\n<b data-view=\"key\">\u2192<\/b> Move the activate object to right<br>\r\n<b data-view=\"key\">\u2193<\/b> Move the activate object to bottom<br>\r\n<b data-view=\"key\">shift<\/b>+<b data-view=\"key\">\u2190<\/b>\r\nMove the activate object to left 10px<br>\r\n<b data-view=\"key\">shift<\/b>+<b data-view=\"key\">\u2191<\/b>\r\nMove the activate object to top 10px<br>\r\n<b data-view=\"key\">shift<\/b>+<b data-view=\"key\">\u2192<\/b>\r\nMove the activate object to right 10px<br>\r\n<b data-view=\"key\">shift<\/b>+<b data-view=\"key\">\u2193<\/b>\r\nMove the activate object to bottom 10px<br>"},{"title":"Custom","content":"Custom help content"},{"title":"Custom 2","content":"Custom help content 2"},{"title":"Custom 3","content":"Custom help content 3"},{"title":"Custom 4","content":"Custom help content 4"}]'
				),
				array(
					'type' => 'text',
					'name' => 'about',
					'label' => $lumise->lang('About content'),
					'desc' => $lumise->lang('This content will be display on the about tab under your logo, you can use the rick text or HTML format here'),
				)
			)
		)
	);
	
	if ($lumise->connector->platform == 'woocommerce') {
		$arg['tabs']['shop:'.$lumise->lang('Shop')][] =  array(
			'type' => 'toggle',
			'name' => 'show_only_design',
			'label' => $lumise->lang('Show only design'),
			'desc' => $lumise->lang('Show only design in cart page (hide product)'),
		);
	}

	if ($lumise->connector->platform == 'php') {
		$arg['tabs']['admin:'.$lumise->lang('Admin login')] =  array(
			array(
				'type' => 'admin_login'
			)
		);
	}
	
	if ($lumise->connector->platform == 'php') {
		$arg['tabs']['shop:'.$lumise->lang('Shop')][] = array(
			'type' => 'input',
			'name' => 'merchant_id',
			'label' => $lumise->lang('Merchant Paypal Id'),
			'desc' => $lumise->lang('The Paypal username to receive payments'),
		);
		
		$arg['tabs']['shop:'.$lumise->lang('Shop')][] = array(
			'type' => 'toggle',
			'name' => 'sanbox_mode',
			'label' => $lumise->lang('Sanbox Mode'),
			'desc' => $lumise->lang('Enable sanbox paypal mode for testing. If No, it is live production mode.'),
			'default' => 1
		);
	}
	
	$arg = $lumise->apply_filters('settings_fields', $arg);
	
	$fields = $lumise_admin->process_settings_data($arg);
	
?>

<div class="lumise_wrapper" id="lumise-<?php echo $section; ?>-page">
	<div class="lumise_content">
		<form action="<?php echo $lumise->cfg->admin_url; ?>lumise-page=<?php 
			echo $section.(isset($_GET['callback']) ? '&callback='.$_GET['callback'] : '');
		?>" id="lumise-settings-form" method="post" class="lumise_form" enctype="multipart/form-data">
			
			<?php 
				$lumise->views->header_message();
				$lumise->views->tabs_render($fields, 'settings'); 
			?>
			
			<div class="lumise_form_group" style="margin-top: 20px">
				<input type="submit" class="lumise-button lumise-button-primary" value="<?php echo $lumise->lang('Save Settings'); ?>"/>
				<input type="hidden" name="do" value="action" />
				<input type="hidden" name="lumise-section" value="<?php echo $section; ?>">
			</div>
		</form>
	</div>
</div>

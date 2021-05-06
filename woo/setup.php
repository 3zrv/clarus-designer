<?php 
	
	global $lumise_woo, $lumise; 
	$editor_page = get_option('lumise_editor_page', 0);
	$active_plugins = get_option( 'active_plugins', array() );
	$fields = array(
		array(
			'type' => 'upload',
			'name' => 'logo',
			'label' => __('Upload Your Logo', 'lumise'),
			'path' => 'settings'.DS,
			'value' => $lumise->cfg->settings['logo'],
			'desc' => $lumise->lang('Upload your own logo to display in the editor (recommented height 80px)')
		),
		array(
			'type' => 'color',
			'name' => 'primary_color',
			'label' => __('Choose Theme Color for Editor', 'lumise'),
			'value' => $lumise->cfg->settings['primary_color'],
			'default' => '#3fc7ba:#546e7a,#757575,#6d4c41,#f4511e,#ffb300,#fdd835,#c0cA33,#a0ce4e,#7cb342,#43a047,#00acc1,#3fc7ba,#039be5,#3949ab,#5e35b1,#d81b60,#eeeeee,#3a3a3a'
		)
	);
	
?><!DOCTYPE html>
<html lang="en-US">
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php _e('Lumise &rsaquo; Setup Wizard', 'lumise'); ?></title>
	<link rel='stylesheet' href='<?php echo admin_url('load-styles.php?c=1&amp;dir=ltr&amp;load%5B%5D=dashicons,admin-bar,buttons,install'); ?>' type='text/css' media='all' />
	<link rel='stylesheet' id='lumise-setup-css'  href='<?php echo $lumise_woo->assets_url; ?>../woo/assets/css/setup.css?ver=<?php echo LUMISE_WOO; ?>' type='text/css' media='all' />
	<link rel='stylesheet' id='lumise-admin-css'  href='<?php echo $lumise_woo->assets_url; ?>admin/assets/css/admin.css?ver=<?php echo LUMISE_WOO; ?>' type='text/css' media='all' />
	<link rel='stylesheet' id='lumise-admin-css'  href='<?php echo $lumise_woo->assets_url; ?>admin/assets/css/font-awesome.min.css?ver=<?php echo LUMISE_WOO; ?>' type='text/css' media='all' />
</head>
<body class="lumise-setup wp-core-ui">
	
	<h1 id="lumise-logo">
		<a href="https://www.lumise.com/">
			<img src="<?php echo $lumise_woo->assets_url; ?>assets/images/logo.v5.png" alt="Lumise" />
		</a>
	</h1>
	
	<ol class="lumise-setup-steps">
			<li data-step="1" class="active"><?php _e('Editor setup', 'lumise'); ?></li>
			<li data-step="2"><?php _e('Lumise theme', 'lumise'); ?></li>
			<li data-step="3"><?php _e('Sample data', 'lumise'); ?></li>
			<li data-step="4"><?php _e('Ready!', 'lumise'); ?></li>
		</ol>
	
	<div class="lumise-setup-content lumise_wrapper" id="setup-body">		
		<form method="post" class="address-step">
			<div class="store-container lumise_content editor-setup" data-step="1">
				<p class="store-setup" style="display:none">
					<?php _e('The following wizard will help you configure your Lumise and get you started quickly.', 'lumise'); ?>
				</p>
				<?php 
					$lumise->views->tabs_render($fields, 'settings');
				?>
				<div class="two-cols">
					<div>
						<label class="location-prompt"><?php _e('Currency symbol', 'lumise'); ?></label>
						<input type="text" class="location-input" name="currency" value="<?php echo $lumise->cfg->settings['currency']; ?>" />
					</div>
					<div>
						<label class="location-prompt"><?php _e('Assign the Editor to a Page', 'lumise'); ?></label>
						<select id="store_state" name="editor_page" class="location-input lumise-enhanced-select dropdown">
							<?php
								
								$pages = get_pages();
								
								foreach ($pages as $page) {
									echo '<option '.($page->ID == $editor_page ? 'selected' : '').' value="'.$page->ID.'">'.$page->post_title.'</option>';
								}
								
							?>
						</select>
					</div>
				</div>
				<label class="location-prompt"><?php _e('Terms & conditions (option)', 'lumise'); ?></label>
				<textarea class="location-input" name="terms"><?php echo $lumise->cfg->settings['conditions']; ?></textarea>
			</div>
			
			<div class="store-container lumise_content lumise-theme" data-step="2" style="display: none;">
				<h1><?php _e('Lumise theme', 'lumise'); ?></h1>
				<p class="store-setup">
					<?php _e('We offer a Wordpress theme, which we customize and create for Lumise. It looks the same with our', 'lumise'); ?> <a href="https://demo.lumise.com/?select-demo=frontend" target=_blank><?php _e('live demo', 'lumise'); ?> &rarr;</a>
				</p>
				<ul class="lumise-wizard-services in-cart">
					<li class="lumise-wizard-service-item" style="width: 100%;">
						<div class="lumise-wizard-service-name">
							<img src="<?php echo $lumise_woo->assets_url; ?>assets/images/logo.v5.png" alt="Lumise Wordpress Theme">
						</div>
						<div class="lumise-wizard-service-description">
							<p>
								<strong><?php _e('Lumise Wordpress Theme', 'lumise'); ?></strong>
								<br> 
								<?php _e('It brings to you a simple & clean styling for your store and ready to use with Lumise.', 'lumise'); ?>
							</p>
						</div>
						
						<div class="lumise-wizard-service-enable">
							<?php
								$theme = wp_get_theme();
								$theme = strtolower($theme);
								if ($theme != 'lumise') {
							?>
							<span class="lumise-wizard-service-toggle" data-toggle="lumise-theme"></span>
							<?php } else { echo '<i class="fa fa-check"></i>'; } ?>
						</div>
					</li>
					<li class="lumise-wizard-service-item">
						<div class="lumise-wizard-service-name">
							<img src="https://kingcomposer.com/wp-content/themes/king/images/og_image.png" alt="Kingcomposer - Professional pagebuilder for Wordpress">
						</div>
						<div class="lumise-wizard-service-description">
							<p>
								<strong><?php _e('Kingcomposer Pagebuilder', 'lumise'); ?></strong>
								<br> 
								<?php _e('Lumise theme has built on Kingcomposer Pagebuilder, it is another product of Lumise team.', 'lumise'); ?> 
								<a href="https://kingcomposer.com/" target="_blank"><?php _e('Learn more', 'lumise'); ?> &rarr;</a>
							</p>
						</div>
						<div class="lumise-wizard-service-enable">
							<?php if (!in_array('kingcomposer'.DS.'kingcomposer.php', $active_plugins)) { ?>
							<span class="lumise-wizard-service-toggle" data-toggle="kingcomposer"></span>
							<?php } else { echo '<i class="fa fa-check"></i>';	} ?>
						</div>
					</li>
				</ul>
			</div>
			
			<div class="store-container lumise_content sample-data" data-step="3" style="display: none;">
				<h1><?php _e('Sample data', 'lumise'); ?></h1>
				<p class="store-setup">
					<?php _e('We offer the sample data for getting started quickly. You can check our docs for how to import to your site', 'lumise'); ?>
				</p>
				<ul class="lumise-wizard-services in-cart">
					<li class="lumise-wizard-service-item">
						<div class="lumise-wizard-service-name">
							<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="60" height="60" viewBox="0 0 473.8 473.8" xml:space="preserve">
<path d="M454.8,111.7c0-1.8-0.4-3.6-1.2-5.3c-1.6-3.4-4.7-5.7-8.1-6.4L241.8,1.2c-3.3-1.6-7.2-1.6-10.5,0L25.6,100.9   c-4,1.9-6.6,5.9-6.8,10.4v0.1c0,0.1,0,0.2,0,0.4V362c0,4.6,2.6,8.8,6.8,10.8l205.7,99.7c0.1,0,0.1,0,0.2,0.1   c0.3,0.1,0.6,0.2,0.9,0.4c0.1,0,0.2,0.1,0.4,0.1c0.3,0.1,0.6,0.2,0.9,0.3c0.1,0,0.2,0.1,0.3,0.1c0.3,0.1,0.7,0.1,1,0.2   c0.1,0,0.2,0,0.3,0c0.4,0,0.9,0.1,1.3,0.1c0.4,0,0.9,0,1.3-0.1c0.1,0,0.2,0,0.3,0c0.3,0,0.7-0.1,1-0.2c0.1,0,0.2-0.1,0.3-0.1   c0.3-0.1,0.6-0.2,0.9-0.3c0.1,0,0.2-0.1,0.4-0.1c0.3-0.1,0.6-0.2,0.9-0.4c0.1,0,0.1,0,0.2-0.1l206.3-100c4.1-2,6.8-6.2,6.8-10.8   V112C454.8,111.9,454.8,111.8,454.8,111.7z M236.5,25.3l178.4,86.5l-65.7,31.9L170.8,57.2L236.5,25.3z M236.5,198.3L58.1,111.8   l85.2-41.3L321.7,157L236.5,198.3z M42.8,131.1l181.7,88.1v223.3L42.8,354.4V131.1z M248.5,442.5V219.2l85.3-41.4v58.4   c0,6.6,5.4,12,12,12s12-5.4,12-12v-70.1l73-35.4V354L248.5,442.5z" style="fill: rgb(99, 99, 99);"></path></svg>
						</div>
						<div class="lumise-wizard-service-description">
							<p>
								<strong><?php _e('Sample Data Package', 'lumise'); ?></strong>
								<br> 
								<?php _e('This package does not include the copyright of images, it is for demo purpose only. It includes cliparts, templates from our', 'lumise'); ?> <a href="https://demo.lumise.com?select-demo=frontend" target=_blank>demo.lumise.com</a>
							</p>
						</div>
					</li>
				</ul>
				<p class="lumise-setup-actions">
					<a class="button-primary button button-large" href="https://docs.lumise.com/getting-started/installation/sample-data-woocommerce/" target=_blank>
						<?php _e('Download Package', 'lumise'); ?> &nbsp; 
						<i class="fa fa-download"></i>
					</a>
				</p>
			</div>
			
			<div class="store-container lumise_content ready" data-step="4" style="display: none;">
				<h1><?php _e('You are ready to start designing!', 'lumise'); ?></h1>
				<?php if (!in_array('woocommerce'.DS.'woocommerce.php', $active_plugins)) { ?>
				<p class="store-setup error">
					<?php _e('The Woocommerce plugin is not activated', 'lumise'); ?> <a style="float: right;" target=_blank href="<?php echo admin_url('plugins.php'); ?>"><?php _e('Active here', 'lumise'); ?> &rarr;</a>
				</p>
				<?php } ?>
				<ul class="lumise-wizard-services lumise-wizard-next-steps">
					<li class="lumise-wizard-service-item">
						<div class="lumise-wizard-next-step-description">
							<p class="next-step-heading"><?php _e('You can also:', 'lumise'); ?></p>
						</div>
						<div class="lumise-wizard-next-step-action">
							<p class="lumise-setup-actions">
								<a class="button button-large" href="<?php echo $lumise->cfg->admin_url; ?>ref=setup">
									<?php _e('Visit Dashboard', 'lumise'); ?>						
								</a>
								<a class="button button-large" href="<?php echo $lumise->cfg->admin_url; ?>lumise-page=settings">
									<?php _e('Review Settings', 'lumise'); ?>					
								</a>
								<a class="button button-large" href="<?php echo $lumise->cfg->admin_url; ?>lumise-page=product">
									<?php _e('Create Product', 'lumise'); ?>				
								</a>
							</p>
						</div>
					</li>
				</ul>
				<p class="next-steps-help-text">
					
					<?php _e('Watch our', 'lumise'); ?> <a href="https://www.lumise.com/videos" target="_blank"><?php _e('guided tour videos</a> to learn more about Lumise, and visit Lumise.com to learn more about ', 'lumise'); ?><a href="https://docs.lumise.com" target="_blank"><?php _e('getting started', 'lumise'); ?></a>.
				</p>
			</div>
			
			<p class="lumise-setup-actions step">
				<button type="submit" class="button-primary button button-large button-next" data-txt="<?php _e('Continue', 'lumise'); ?>" id="save-step">
					<?php _e('Let\'s go!', 'lumise'); ?>
				</button>
			</p>
		</form>
	</div>
	
	<a class="lumise-setup-footer-links" data-txt="<?php _e('Not right now', 'lumise'); ?>" data-skip="<?php _e('Skip this step', 'lumise'); ?>" href="<?php echo admin_url(); ?>">
		<?php _e('Not right now', 'lumise'); ?>
	</a>
	
	<script>
		var LumiseDesign = {
			url : "<?php echo htmlspecialchars_decode($lumise->cfg->url); ?>",
			admin_url : "<?php echo htmlspecialchars_decode($lumise->cfg->admin_url); ?>",
			ajax : "<?php echo htmlspecialchars_decode($lumise->cfg->admin_ajax_url); ?>",
			assets : "<?php echo $lumise->cfg->assets_url; ?>",
			jquery : "<?php echo $lumise->cfg->load_jquery; ?>",
			nonce : "<?php echo lumise_secure::create_nonce('LUMISE_ADMIN') ?>",
			filter_ajax: function(ops) {
				return ops;
			},
			js_lang : <?php echo json_encode($lumise->cfg->js_lang); ?>,
		};
	</script>
	<script src="<?php echo $lumise_woo->assets_url; ?>admin/assets/js/vendors.js?ver=<?php echo LUMISE_WOO; ?>"></script>
	<script src="<?php echo $lumise_woo->assets_url; ?>admin/assets/js/main.js?ver=<?php echo LUMISE_WOO; ?>"></script>
	<script src="<?php echo $lumise_woo->assets_url; ?>../woo/assets/js/setup.js?ver=<?php echo LUMISE_WOO; ?>"></script>
</body>
</html>
		
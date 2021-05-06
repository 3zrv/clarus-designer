<?php
	
function lumise_cms_product_data_fields($ops, $js_cfg, $id) {
    
    global $lumise;
    
    $product = isset($ops['lumise_product_base']) ? $ops['lumise_product_base'] : '';
    $design = isset($ops['lumise_design_template']) ? $ops['lumise_design_template'] : '';
    $customize = isset($ops['lumise_customize']) ? $ops['lumise_customize'] : '';
    $addcart = isset($ops['lumise_disable_add_cart']) ? $ops['lumise_disable_add_cart'] : '';

    ?>
	<link rel="stylesheet" href="<?php echo $lumise->cfg->assets_url; ?>admin/assets/css/interg.css?version=<?php echo LUMISE; ?>" type="text/css" media="all" />
    <div id="lumise_product_data" class="panel woocommerce_options_panel">
        <p class="form-field lumise_customize_field options_group hidden" id="lumise-enable-customize">
			<label for="lumise_customize">
				<strong><?php echo $lumise->lang('Hide cart button'); ?>:</strong>
			</label>
			<span class="lumise-toggle">
				<input type="checkbox" name="lumise_disable_add_cart"  <?php
				if ($addcart == 'yes')echo 'checked';
			?> id="lumise_customize" value="yes" />
				<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
				<span class="lumise-toggle-handle"></span>
			</span>
			<span style="float: left;margin-left: 10px;">
				<?php echo $lumise->lang('Hide the Add To Cart button in product details page'); ?>
			</span>
		</p>
		<p class="form-field lumise_customize_field options_group hidden" id="lumise-enable-customize">
			<label for="lumise_customize">
				<strong><?php echo $lumise->lang('Allow customize'); ?>:</strong>
			</label>
			<span class="lumise-toggle">
				<input type="checkbox" name="lumise_customize"  <?php
				if ($customize != 'no')echo 'checked';
			?> id="lumise_customize" value="yes" />
				<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
				<span class="lumise-toggle-handle"></span>
			</span>
			<span style="float: left;margin-left: 10px;">
				<?php echo $lumise->lang('Users can change or customize the design before checkout.'); ?>
			</span>
		</p>
        <div id="lumise-product-base" class="options_group"></div>
        <p id="lumise-seclect-base">
	        <a href="#" class="lumise-button lumise-button-primary lumise-button-large" data-func="products">
		        <i class="fa fa-cubes"></i>
		        <?php echo $lumise->lang('Select product base'); ?>
		    </a>
		    &nbsp;
		    <a href="#" title="<?php echo $lumise->lang('Remove product base'); ?>" class="lumise-button lumise-button-link-delete lumise-button-large hidden" data-func="remove-base-product">
		        <i class="fa fa-trash"></i>
		        <?php echo $lumise->lang('Remove product'); ?>
		    </a>
        </p>
        <?php $lumise->do_action( 'product-lumise-option-tab' ); ?>
        <input type="hidden" value="<?php echo $product; ?>" name="lumise_product_base" id="lumise_product_base" />
        <input type="hidden" value="<?php echo $design; ?>" name="lumise_design_template" id="lumise_design_template" />
    </div>
<?php 
	
	$js_cfg = array_merge(array(
		'nonce_backend' => lumise_secure::create_nonce('LUMISE-SECURITY-BACKEND'),
		'ajax_url' => $lumise->cfg->ajax_url,
		'admin_ajax_url' => $lumise->cfg->admin_ajax_url,
		'is_admin' => is_admin(),
		'admin_url' => $lumise->cfg->admin_url,
		'assets_url' => $lumise->cfg->assets_url,
		'upload_url' => $lumise->cfg->upload_url,
		'inline_edit' => (isset($ops['inline_edit']) ? $ops['inline_edit'] : true),
		'current_product' => (isset($id) && !empty($id) ? $id : 0),
		'color' => explode(':', ($lumise->cfg->settings['colors'] ? $lumise->cfg->settings['colors'] : ''))[0],
		'_i42' => $lumise->lang('No items found'),
    	'_i62' => $lumise->lang('Products'),
    	'_i64' => $lumise->lang('Select product'),
    	'_i63' => $lumise->lang('Search product'),
    	'_i56' => $lumise->lang('Categories'),
    	'_i57' => $lumise->lang('All categories'),
    	'_i58' => $lumise->lang('Select template'),
    	'_i59' => $lumise->lang('Create new'),
    	'_i60' => $lumise->lang('Stages'),
    	'_i61' => $lumise->lang('Edit Product Base'),
		'_i65' => $lumise->lang('Start Over'),
    	'_i66' => $lumise->lang('Design templates'),
    	'_i67' => $lumise->lang('Search design templates'),
    	'_i68' => $lumise->lang('Load more'),
    	'_i69' => $lumise->lang('Clear design template'),
    	'_i70' => $lumise->lang('Clear'),
    	'_i71' => $lumise->lang('You need to choose a product base to enable Lumise Editor for this product.'),
    	'_i72' => $lumise->lang('Download'),
    	'_i73' => $lumise->lang('Download design template'),
    	'_front' => $lumise->lang($lumise->cfg->settings['label_stage_1']),
    	'_back' => $lumise->lang($lumise->cfg->settings['label_stage_2']),
    	'_left' => $lumise->lang($lumise->cfg->settings['label_stage_3']),
    	'_right' => $lumise->lang($lumise->cfg->settings['label_stage_4']),
	), $js_cfg);
	
	echo '<script type="text/javascript">';
	echo 'var lumisejs='.json_encode($js_cfg).';';
	echo '</script>';
	echo '<script type="text/javascript" src="'.$lumise->cfg->assets_url.'admin/assets/js/interg.js?version='.LUMISE.'"></script>';

}


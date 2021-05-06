<?php
	
	global $lumise;
	
	$arg = array(

		'tabs' => array(

			'details:' . $lumise->lang('Details') => array(
				array(
					'type' => 'input',
					'name' => 'name',
					'label' => $lumise->lang('Name'),
					'required' => true,
					'default' => 'Untitled'
				),
				(
					$lumise->connector->platform == 'php'? 
					array(
						'type' => 'input',
						'name' => 'price',
						'label' => $lumise->lang('Price'),
						'default' => '0',
						'desc' => $lumise->lang('Enter the base price for this product.')
					) : null
				),
				(
					$lumise->connector->platform == 'php' ?
					array(	
						'type' => 'upload',
						'name' => 'thumbnail',
						'thumbn' => 'thumbnail_url',
						'path' => 'thumbnails'.DS,
						'label' => $lumise->lang('Product thumbnail'),
						'desc' => $lumise->lang('Supported files svg, png, jpg, jpeg. Max size 5MB')
					)
					:
					array(
						'type' => 'input',
						'name' => 'product',
						'label' => $lumise->lang('CMS Product'),
						'default' => '0',
						'desc' => $lumise->lang('This product will not be listed if this field value is zero. It will set automatically when you select this product base for a Woocommerce Product'),
						'readonly' => true
					)
				)
				,
				array(
					'type' => 'text',
					'name' => 'description',
					'label' => $lumise->lang('Description')
				),
				array(
					'type' => 'toggle',
					'name' => 'active_description',
					'label' => $lumise->lang('Active description'),
					'default' => 'no',
					'value' => null,
					'desc' => $lumise->lang('Show description on editor design.')
				),
				array(
					'type' => 'categories',
					'cate_type' => 'products',
					'name' => 'categories',
					'label' => $lumise->lang('Categories'),
					'id' => isset($_GET['id'])? $_GET['id'] : 0
				),
				array(
					'type' => 'printing',
					'name' => 'printings',
					'label' => $lumise->lang('Printing Techniques'),
					'desc' => $lumise->lang('Select Printing Techniques with price calculations for this product').'<br>'.$lumise->lang('Drag to arrange items, the first one will be set as default').'. <br><a href="'.$lumise->cfg->admin_url.'lumise-page=printings" target=_blank>'.$lumise->lang('You can manage all Printings here').'.</a>'
				),
				array(
					'type' => 'toggle',
					'name' => 'active',
					'label' => $lumise->lang('Active'),
					'default' => 'yes',
					'value' => null,
					'desc' => $lumise->lang('Deactivate does not affect the selected products. It will only not show in the switching products.')
				),
				array(
					'type' => 'input',
					'name' => 'order',
					'type_input' => 'number',
					'label' => $lumise->lang('Order'),
					'default' => 0,
					'desc' => $lumise->lang('Ordering of item with other.')
				),
			),

			'design:' . $lumise->lang('Designs') => array(
				array(
					'type' => 'stages',
					'name' => 'stages'
				)
			),
			
			'variations:' . $lumise->lang('Variations') => array(
				array(
					'type' => 'variations',
					'name' => 'variations'
				)
			),

			'attributes:' . $lumise->lang('Attributes') => array(
				array(
					'type' => 'attributes',
					'name' => 'attributes'
				),
			)
		)
	);
	
	$fields = $lumise_admin->process_data($arg, 'products');
	
?>

<div class="lumise_wrapper" id="lumise-product-page">
	<div class="lumise_content">
		<?php

			$lumise->views->detail_header(array(
				'add' => $lumise->lang('Add New Product'),
				'edit' => $fields['tabs']['details:' . $lumise->lang('Details')][0]['value'],
				'page' => 'product'
			));

		?>
		<form action="<?php
			echo $lumise->cfg->admin_url;
		?>lumise-page=product<?php
			if (isset($_GET['callback']))
				echo '&callback='.$_GET['callback'];
		?>" id="lumise-product-form" method="POST" class="lumise_form" enctype="multipart/form-data">

			<?php $lumise->views->tabs_render($fields, 'products'); ?>

			<div class="lumise_form_group" style="margin-top: 20px">
				<input type="submit" class="lumise-button lumise-button-primary" value="<?php echo $lumise->lang('Save Product'); ?>"/>
				<input type="hidden" name="do" value="action" />
				<input type="hidden" name="lumise-section" value="product">
			</div>
		</form>
	</div>
</div>

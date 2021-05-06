<?php
	global $lumise;

	$section = 'clipart';
	$fields = $lumise_admin->process_data(array(
		array(
			'type' => 'input',
			'name' => 'name',
			'label' => $lumise->lang('Name'),
			'required' => true,
			'default' => 'Untitled'
		),
		array(
			'type' => 'categories',
			'cate_type' => 'cliparts',
			'name' => 'categories',
			'label' => $lumise->lang('Categories'),
			'id' => isset($_GET['id'])? $_GET['id'] : 0,
			'db' => false
		),
		array(
			'type' => 'tags',
			'tag_type' => 'cliparts',
			'name' => 'tags',
			'label' => $lumise->lang('Tags'),
			'id' => isset($_GET['id'])? $_GET['id'] : 0,
			'desc' => $lumise->lang('Example: tag1, tag2, tag3 ...'),
		),
		array(
			'type' => 'upload',
			'name' => 'upload',
			'path' => 'cliparts'.DS.date('Y').DS.date('m').DS,
			'thumbn' => 'thumbnail_url',
			'label' => $lumise->lang('Upload design file'),
			'desc' => $lumise->lang('Supported files svg, png, jpg, jpeg. Max size 5MB')
		),
		array(
			'type' => 'input',
			'name' => 'price',
			'label' => $lumise->lang('Price'),
			'default' => 0
		),
		array(
			'type' => 'toggle',
			'name' => 'featured',
			'label' => $lumise->lang('Featured'),
			'default' => 'no',
			'value' => null
		),
		array(
			'type' => 'toggle',
			'name' => 'active',
			'label' => $lumise->lang('Active'),
			'default' => 'yes',
			'value' => null
		),
		array(
			'type' => 'input',
			'name' => 'order',
			'type_input' => 'number',
			'label' => $lumise->lang('Order'),
			'default' => 0,
			'desc' => $lumise->lang('Ordering of item with other.')
		),
	), 'cliparts');

?>

<div class="lumise_wrapper" id="lumise-<?php echo $section; ?>-page">
	<div class="lumise_content">
		<?php
			$lumise->views->detail_header(array(
				'add' => $lumise->lang('Add new clipart'),
				'edit' => $fields[0]['value'],
				'page' => $section
			));
		?>
		<form action="<?php echo $lumise->cfg->admin_url; ?>lumise-page=<?php
			echo $section.(isset($_GET['callback']) ? '&callback='.$_GET['callback'] : '');
		?>" id="lumise-clipart-form" method="post" class="lumise_form" enctype="multipart/form-data">

			<?php $lumise->views->tabs_render($fields); ?>

			<div class="lumise_form_group lumise_form_submit">
				<input type="submit" class="lumise-button lumise-button-primary" value="<?php echo $lumise->lang('Save Clipart'); ?>"/>
				<input type="hidden" name="do" value="action" />
				<a class="lumise_cancel" href="<?php echo $lumise->cfg->admin_url;?>lumise-page=cliparts">
					<?php echo $lumise->lang('Cancel'); ?>
				</a>
				<input type="hidden" name="lumise-section" value="<?php echo $section; ?>">
			</div>
		</form>
	</div>
</div>

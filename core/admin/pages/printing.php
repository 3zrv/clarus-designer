<?php
	global $lumise;

	$section = 'printing';
	$fields = $lumise_admin->process_data(array(
		array(
			'type' => 'input',
			'name' => 'title',
			'label' => $lumise->lang('Printing Title'),
			'required' => true,
			'default' => 'Untitled'
		),
		array(
			'type' => 'upload',
			'name' => 'upload',
			'thumbn' => 'thumbnail',
			'thumbn_width' => 320,
			'path' => 'printings'.DS,
			'label' => $lumise->lang('Printing thumbnail'),
			'desc' => $lumise->lang('Supported files svg, png, jpg, jpeg. Max size 5MB'),
		),
		array(
			'type' => 'text',
			'name' => 'description',
			'label' => $lumise->lang('Description'),
		),
		array(
			'type' => 'print',
			'name' => 'calculate',
			'label' => $lumise->lang('Calculation Price'),
			'prints_type' => $lumise->lib->get_print_types()
		),
		array(
			'type' => 'toggle',
			'name' => 'active',
			'label' => $lumise->lang('Active'),
			'default' => 'yes',
			'value' => null
		),
	), 'printings');

?>

<div class="lumise_wrapper" id="lumise-<?php echo $section; ?>-page">
	<div class="lumise_content">
		<?php
			$lumise->views->detail_header(array(
				'add' => $lumise->lang('Add new printing'),
				'edit' => $fields[0]['value'],
				'page' => $section
			));
		?>
		<form action="<?php echo $lumise->cfg->admin_url; ?>lumise-page=<?php
			echo $section.(isset($_GET['callback']) ? '&callback='.$_GET['callback'] : '');
		?>" id="lumise-<?php echo $section; ?>-form" method="post" class="lumise_form" enctype="multipart/form-data">

			<?php $lumise->views->tabs_render($fields); ?>

			<div class="lumise_form_group lumise_form_submit">
				<input type="submit" class="lumise-button lumise-button-primary" value="<?php echo $lumise->lang('Save Printing'); ?>"/>
				<input type="hidden" name="do" value="action" />
				<a class="lumise_cancel" href="<?php echo $lumise->cfg->admin_url;?>lumise-page=<?php echo $section; ?>s">
					<?php echo $lumise->lang('Cancel'); ?>
				</a>
				<input type="hidden" name="lumise-section" value="<?php echo $section; ?>">
			</div>
		</form>
	</div>
</div>

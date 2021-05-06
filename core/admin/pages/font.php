<?php

	$section = 'font';
	$fields = $lumise_admin->process_data(array(
		array(
			'type' => 'input',
			'name' => 'name',
			'label' => $lumise->lang('Name'),
			'required' => true,
			'desc' => $lumise->lang('The names must be latin letters'),
			'default' => 'Untitled'
		),
		array(
			'type' => 'input',
			'name' => 'name_desc',
			'label' => $lumise->lang('Name Desciption'),
			'required' => true,
			'desc' => $lumise->lang('Show on frontend(design editor), default will is name'),
			'default' => ''
		),
		array(
			'type' => 'upload',
			'name' => 'upload',
			'required' => true,
			'path' => 'fonts'.DS,
			'file' => 'font',
			'file_type' => 'woff2',
			'label' => $lumise->lang('Upload Font'),
			'desc' => $lumise->lang('You can convert your *.ttf to *.woff2 by a converter online tool').
					  ' <a href="https://font-converter.net/en" target="_blank"><strong>Font-Converter.net</strong></a>'
		),
		array(
			'type' => 'upload',
			'name' => 'upload_ttf',
			'path' => 'fonts'.DS,
			'file' => 'font',
			'file_type' => 'ttf',
			'label' => $lumise->lang('PDF render'),
			'desc' => $lumise->lang('Upload *.ttf file to render PDF (in case you want to export design to PDF for printing)')
		),
		array(
			'type' => 'toggle',
			'name' => 'active',
			'label' => $lumise->lang('Active'),
			'default' => 'yes',
			'value' => null
		),
	), 'fonts');

?>

<div class="lumise_wrapper" id="lumise-<?php echo $section; ?>-page">
	<div class="lumise_content">
		<?php
			$lumise->views->detail_header(array(
				'add' => $lumise->lang('Add new font'),
				'edit' => $fields[0]['value'],
				'page' => $section
			));
		?>
		<form action="<?php echo $lumise->cfg->admin_url; ?>lumise-page=<?php
			echo $section.(isset($_GET['callback']) ? '&callback='.$_GET['callback'] : '');
		?>" id="lumise-clipart-form" method="post" class="lumise_form" enctype="multipart/form-data">

			<?php $lumise->views->tabs_render($fields); ?>

			<div class="lumise_form_group lumise_form_submit">
				<input type="submit" class="lumise-button lumise-button-primary" value="<?php echo $lumise->lang('Save Font'); ?>"/>
				<input type="hidden" name="do" value="action" />
				<a class="lumise_cancel" href="<?php echo $lumise->cfg->admin_url;?>lumise-page=<?php echo $section; ?>s">
					<?php echo $lumise->lang('Cancel'); ?>
				</a>
				<input type="hidden" name="lumise-section" value="<?php echo $section; ?>">
			</div>
		</form>
	</div>
</div>

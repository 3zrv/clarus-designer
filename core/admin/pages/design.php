<?php
	$title = "Edit Design";

	$arr = Array ("name");
	$design = $lumise_admin->get_rows_custom($arr,'lumise_designs');

	if (isset($_GET['id'])) {
		$data = $lumise_admin->get_row_id($_GET['id'], 'lumise_designs');
	}

	if (!empty($_POST['save_design'])) {

		$data = array();
		$data_id = isset($_POST['id']) ? trim($_POST['id']) : '';
		$data['name'] = isset($_POST['name']) ? trim($_POST['name']) : '';
		$data['screenshot'] = isset($_POST['screenshot']) ? trim($_POST['screenshot']) : '';
		$data['sharing'] = isset($_POST['sharing']) ? $_POST['sharing'] : '';
		$data['active'] = isset($_POST['active']) ? $_POST['active'] : '';
		$data_name = isset($_POST['name_temp']) ? $_POST['name_temp'] : '';
		$errors = array();

		if (empty($data['name'])) {
			$errors['name'] = $lumise->lang('Please Insert Name.');
		} else {

			$data['name'] = trim($data['name']);
			if (is_array($design) && count($design) >0) {
				foreach ($design as $value) {
					if ($value['name'] == $data['name'] && $data['name'] != $data_name) {
						$errors['name'] = $lumise->lang('The name provided already exists.');
					}
				}
			}
			
		}

		if (!empty($data['sharing'])) {
			$data['sharing'] = 1;
		} else {
			$data['sharing'] = 0;
		}

		if (!empty($data['active'])) {
			$data['active'] = 1;
		} else {
			$data['active'] = 0;
		}

		$data['updated'] = date("Y-m-d").' '.date("H:i:s");
		
		if (count($errors) == 0) {

			if (!empty($data_id)) {
				$id = $lumise_admin->edit_row( $data_id, $data, 'lumise_designs' );
			} else {
				$id =$lumise_admin->add_row( $data, 'lumise_designs' );
			}
			$lumise_msg = array('status' => 'success');
			$lumise->connector->set_session('lumise_msg', $lumise_msg);

		} else {

			$lumise_msg = array('status' => 'error', 'errors' => $errors);
			$lumise->connector->set_session('lumise_msg', $lumise_msg);
			if (!empty($data_id)) {
				$lumise->redirect($lumise->cfg->admin_url . "lumise-page=design&id=".$data_id);
			} else {
				$lumise->redirect($lumise->cfg->admin_url . "lumise-page=design");
			}
			exit;

		}

		if (isset($id) && $id == true ) {
			$lumise->redirect($lumise->cfg->admin_url . "lumise-page=design&id=".$id);
			exit;
		}

	}

?>

<div class="lumise_wrapper">
	<div class="lumise_content">
		<div class="lumise_header">
			<?php

				if (isset($_GET['id'])) {
					echo '<h2>'.$lumise->lang('Edit Design').'</h2><a href="'.$lumise->cfg->admin_url.'lumise-page=design" class="add-new lumise-button">'.$lumise->lang('Add New Design').'</a>';
				} else {
					echo '<h2>'.$lumise->lang('Add Design').'</h2>';
				}
				$lumise_page = isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '';
				echo $lumise_helper->breadcrumb($lumise_page); 

			?>
		</div>
		<?php 

			$lumise_msg = $lumise->connector->get_session('lumise_msg');
			if (isset($lumise_msg['status']) && $lumise_msg['status'] == 'error') { ?>
				
				<div class="lumise_message err">

					<?php foreach ($lumise_msg['errors'] as $val) {
						echo '<em class="lumise_err"><i class="fa fa-times"></i>  ' . $val . '</em>';
						$lumise_msg = array('status' => '');
						$lumise->connector->set_session('lumise_msg', $lumise_msg);
					} ?>

				</div>
				
			<?php }

			if (isset($lumise_msg['status']) && $lumise_msg['status'] == 'success') { ?>
				
				<div class="lumise_message"> 
					<?php
						echo '<em class="lumise_suc"><i class="fa fa-check"></i> '.$lumise->lang('Your data has been successfully saved').'</em>';
						$lumise_msg = array('status' => '');
						$lumise->connector->set_session('lumise_msg', $lumise_msg);
					?>
				</div>

			<?php }

		?>
		<form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=design" method="post" class="lumise_form">
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Name'); ?><em class="required">*</em></span>
				<div class="lumise_form_content">
					<input type="text" name="name" value="<?php echo !empty($data['name']) ? $data['name'] : '' ?>">
					<input type="hidden" name="name_temp" value="<?php echo !empty($data['name']) ? $data['name'] : '' ?>">
				</div>
			</div>
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Screenshot'); ?></span>
				<div class="lumise_form_content">
					<textarea name="screenshot"><?php echo !empty($data['screenshot']) ? $data['screenshot'] : '' ?></textarea>
				</div>
			</div>
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Sharing'); ?></span>
				<div class="lumise_form_content">
					<div class="lumise-toggle">
						<?php 
							$check = '';
							if (isset($data['sharing']) && $data['sharing'] == 1) {
								$check = 'checked';
							}
						?>
						<input type="checkbox" name="sharing" <?php echo $check; ?>>
						<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
						<span class="lumise-toggle-handle"></span>
					</div>
				</div>
			</div>
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Active'); ?></span>
				<div class="lumise_form_content">
					<div class="lumise-toggle">
						<?php 
							$check = '';
							if (isset($data['active']) && $data['active'] == 1) {
								$check = 'checked';
							}
						?>
						<input type="checkbox" name="active" <?php echo $check; ?>>
						<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
						<span class="lumise-toggle-handle"></span>
					</div>
				</div>
			</div>
			<div class="lumise_form_group">
				<input type="hidden" name="id" value="<?php echo !empty($data['id']) ? $data['id'] : '' ?>"/>
				<input type="submit" class="lumise-button lumise-button-primary" value="<?php echo $lumise->lang('Save Design'); ?>"/>
				<input type="hidden" name="save_design" value="true">
			</div>
			<?php $lumise->securityFrom();?>
		</form>
	</div>
</div>

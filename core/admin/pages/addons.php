<?php
$items = $lumise->addons->load_installed();
?>
<div class="lumise_wrapper" id="lumise-addons-page">
	<div class="lumise_content">
		<div class="lumise_header">
			<h2><?php echo $lumise->lang('Addons'); ?></h2>
			<a href="#" class="add-new lumise-button" onclick="document.querySelectorAll('.upload-addon-wrap')[0].style.display = 'inline-block';">
				<i class="fa fa-cloud-upload"></i>
				<?php echo $lumise->lang('Upload a new addon'); ?>
			</a>
			<a href="<?php echo $lumise->cfg->admin_url; ?>lumise-page=explore-addons" class="add_new active">
				<i class="fa fa-th"></i>
				<?php echo $lumise->lang('Explore all available addons'); ?>		
			</a>
		</div>
		<div class="upload-addon-wrap">
			<div class="upload-addon">
				<p class="install-help">
					<?php echo $lumise->lang('If you have an addon in a .zip format, you may install it by uploading it here.'); ?>				
				</p>
				<form method="post" enctype="multipart/form-data" class="addon-upload-form" action="">
					<input type="file" name="addonzip" id="addonzip">
					<input type="hidden" name="action" value="upload">
					<input type="submit" name="install-lumiseaddon-submit" class="lumise_submit" value="<?php echo $lumise->lang('Install Now'); ?>">
				</form>
			</div>
		</div>
		<?php 
			$lumise->views->header_message();
		?>
		<div class="lumise_option">
			<div class="left">
				<form action="<?php echo $lumise->cfg->admin_url; ?>lumise-page=addons" method="post">
					<select name="action" class="art_per_page">
						<option value="none"><?php echo $lumise->lang('Bulk Actions'); ?></option>
						<option value="active"><?php echo $lumise->lang('Active'); ?></option>
						<option value="deactive"><?php echo $lumise->lang('Deactive'); ?></option>
						<option value="delete"><?php echo $lumise->lang('Delete'); ?></option>
					</select>
					<input type="hidden" name="id_action" class="id_action">
					<input type="submit" class="lumise_submit" value="Apply">			
				</form>
			</div>
		</div>
		<form action="" method="post" class="lumise_form" enctype="multipart/form-data">
			<table class="lumise_table lumise_addons">
				<thead>
					<tr>
						<th class="lumise_check">
							<div class="lumise_checkbox">
								<input type="checkbox" id="check_all">
								<label for="check_all"><em class="check"></em></label>
							</div>
						</th>
						<th width="20%"><?php echo $lumise->lang('Name'); ?></th>
						<th><?php echo $lumise->lang('Description'); ?></th>
						<th><?php echo $lumise->lang('Version'); ?></th>
						<th><?php echo $lumise->lang('Compatible'); ?></th>
						<th><?php echo $lumise->lang('Status'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if (count($items) > 0) {
							
							$actives = $lumise->get_option( 'active_addons');
							
							if ($actives !== null && !empty($actives))
								$actives = (Array)@json_decode($actives);
							
							if (!is_array($actives))
								$actives = array();
								
							foreach ($items as $item) {
								echo '<tr>';
								echo '<td class="lumise_check">'.
											'<div class="lumise_checkbox">'.
												'<input type="checkbox" name="checked[]" class="action_check" value="'.$item['Slug'].'" id="lc-'.$item['Slug'].'">'.
												'<label for="lc-'.$item['Slug'].'"><em class="check"></em></label>'.
											'</div>'.
										'</td>';
								echo '<td data-slug="'.$item['Slug'].'" data-name="'.str_replace('"', '\"', $item['Name']).'">';
								
								if (isset($actives[$item['Slug']])) {
									echo '<strong>'.$item['Name'].'</strong><br><a href="'.$lumise->cfg->admin_url.'lumise-page=addon&name='.$item['Slug'].'">Addon settings</a>';
								} else {
									echo $item['Name'];
								}
								echo '</td>';
								echo '<td>'.$item['Description'].'</td>';
								if (version_compare($item['Compatible'], LUMISE, '>')) {
									echo '<td colspan="3">';
									echo '<span class="pub pen"><i class="fa fa-warning"></i> '.$lumise->lang('Required Lumise version').' '.$item['Compatible'].'+</span>';
									echo '</td>';
								} else if (
									isset($item['Platform']) && 
									!empty($item['Platform']) && 
									strpos(strtolower($item['Platform']), $lumise->connector->platform) === false
								) {
									echo '<td colspan="3">';
									echo '<span class="pub pen"><i class="fa fa-warning"></i> '.$lumise->lang('Unsupported your platform').'</span><br><small>Only support '.$item['Platform'].'</small>';
									echo '</td>';
								} else {
									echo '<td>'.$item['Version'].'</td>';
									echo '<td>'.$item['Compatible'].'+</td>';
									echo '<td><a href="#" class="lumise_action" data-type="addons" data-action="switch_active" data-status="'.(isset($actives[$item['Slug']]) ? $actives[$item['Slug']] : 0).'" data-id="'.$item['Slug'].'">';
									echo (
										isset($actives[$item['Slug']]) && $actives[$item['Slug']] == 1 ? 
										'<em class="pub">'.$lumise->lang('Active').'</em>' : 
										'<em class="pub un">'.$lumise->lang('Deactive').'</em>'
									);
									echo '</a></td>';
								}
								echo '</tr>';
							}
						} else {
							echo '<tr><td colspan="6">No items found</td></tr>';
						}
					?>
				</tbody>
			</table>
		</form>
	</div>
</div>

<?php
	global $lumise;
	$checked = @json_decode($lumise->get_option('last_check_update'));
?>

<div class="lumise_wrapper">

	<div id="lumise-updates">
		<h1><?php echo $lumise->lang('Lumise Updates'); ?></h1>
		<?php $lumise->views->header_message(); ?>
		<form action="" method="POST">
			<?php echo $lumise->lang('Last checked on ').' '.(isset($checked->time) ? 
				'<span id="write-time-check"><script>var tm = new Date('.$checked->time.'000), mt = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]; document.getElementById("write-time-check").innerHTML = tm.getHours()+"h:"+(tm.getMinutes()<10 ? "0"+tm.getMinutes() : tm.getMinutes())+" - "+tm.getDate()+" "+mt[tm.getMonth()]+" "+tm.getFullYear();</script></span>' 
				: '<b>Recently</b>'); ?>.
			&nbsp; 
			<button type="submit" class="lumise_btn loaclik" data-func="check"><?php echo $lumise->lang('Check again'); ?></button>
			<input type="hidden" name="do_action" value="check-update" />
		</form>
		<?php
			if (!empty($checked) && isset($checked->version) && version_compare(LUMISE, $checked->version, '<')) {
			?>
			<h3>
				<?php echo $lumise->lang('An updated version of Lumise is available'); ?>. 
				<?php echo $lumise->lang('New version').' '.$checked->version; ?>
			</h3>
			<div class="lumise-update-notice">
				<b><?php echo $lumise->lang('Important'); ?></b>: 
				<?php echo $lumise->lang('before updating, please back up your database and files of Lumise'); ?>
			</div>
			<form action="" method="POST">
				<button class="lumise_btn primary loaclik"><?php echo $lumise->lang('Update Now'); ?></button> 
				&nbsp; 
				<a class="lumise_btn" href="https://www.lumise.com/changelogs/<?php echo $lumise->connector->platform; ?>?utm_source=client-site&utm_medium=text&utm_campaign=update-page&utm_term=links&utm_content=<?php echo $lumise->connector->platform; ?>" target=_blank>
					<?php echo $lumise->lang(' Changelogs'); ?>
				</a>
				<input type="hidden" name="do_action" value="do-update" />
			</form>
			<?php	
			} else {
			?>
			<h2><?php echo $lumise->lang('Great! You have the latest version of Lumise'); ?>.</h2>
			<?php	
			}
		?>
	</div>

</div>

<?php
	global $lumise;
	$check = $lumise->lib->get_system_status();
?>

<div class="lumise_wrapper">
	<div class="lumise_content">
		<div class="lumise_header">
			<h2><?php echo $lumise->lang('System status'); ?></h2>	
		</div>
		<ul class="system-status">
		<?php
			foreach ($check as $key => $val) {
				if ($key == 'memory_limit') {
					echo '<li class="'.($val > 250 ? 'true' : 'false').'">'.$key.' ('.$val.'MB)'.($val > 250 ? '' : ' <small>The value should be greater than 250MB</small>').'</li>';
				} else if ($key == 'post_max_size') {
					echo '<li class="'.($val > 100 ? 'true' : 'false').'">'.$key.' ('.$val.'MB)'.($val > 100 ? '' : ' <small>The value should be greater than 100MB</small>').'</li>';
				} else if ($key == 'upload_max_filesize') {
					echo '<li class="'.($val > 100 ? 'true' : 'false').'">'.$key.' ('.$val.'MB)'.($val > 100 ? '' : ' <small>The value should be greater than 100MB</small>').'</li>';
				} else if ($key == 'max_execution_time') {
					echo '<li class="'.($val > 600 ? 'true' : 'false').'">'.$key.' ('.$val.'Second)'.($val > 600 ? '' : ' <small>The value should be greater than 600</small>').'</li>';
				} else {
					echo '<li class="'.($val == 1 ? 'true' : 'false').'">'.$key.'</li>';
				}
			}
		?>
		</ul>
		<br>
		<p style="font-size: 15px;"><?php echo $lumise->lang('Check our document for more details about the system status'); ?>. <a href="https://docs.lumise.com/system-status/?utm_source=client-site&utm_medium=text&utm_campaign=system-page&utm_term=links&utm_content=<?php echo $lumise->connector->platform; ?>" target=_blank><?php echo $lumise->lang('Click here'); ?> &#10230;</a></p>
	</div>
</div>

<?php
	global $lumise;
?><script>
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
<script src="<?php echo $lumise->cfg->admin_assets_url;?>js/vendors.js?version=<?php echo LUMISE; ?>"></script>
<script src="<?php echo $lumise->cfg->admin_assets_url;?>js/tag-it.min.js?version=<?php echo LUMISE; ?>"></script>
<script src="<?php echo $lumise->cfg->admin_assets_url;?>js/main.js?version=<?php echo LUMISE; ?>"></script>
<?php
	
	$lumise->do_action('editor-footer');
	
	if ($lumise->connector->platform == 'php') {
		echo '</body></html>';
	}
?>

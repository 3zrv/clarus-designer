<?php
	
	global $lumise;
	
	require_once('includes/main.php');
	
	$lumise->router();
	
	if($lumise->is_app()) :
	
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title><?php echo $lumise->lang($lumise->cfg->settings['title']); ?></title>
		<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" name="viewport" />
		<link rel="stylesheet" href="<?php echo $lumise->apply_filters('editor/app.css', $lumise->cfg->assets_url.'assets/css/app.css?version='.LUMISE); ?>">
		<link rel="stylesheet" media="only screen and (max-width: 1170px)" href="<?php echo $lumise->apply_filters('editor/responsive.css', $lumise->cfg->assets_url.'assets/css/responsive.css?version='.LUMISE); ?>">
<?php 
	if (is_file($lumise->cfg->upload_path.'user_data'.DS.'custom.css')) { 
?> <link rel="stylesheet" href="<?php echo $lumise->cfg->upload_url; ?>user_data/custom.css?version=<?php echo $lumise->cfg->settings['last_update']; ?>"><?php 
} 

$lumise->do_action('editor-header'); 

?></head>
<body>
	<div class="wrapper">
		<div id="LumiseDesign" data-site="https://lumise.com" data-processing="true" data-msg="<?php echo $lumise->lang('Initializing'); ?>..">
			<div id="lumise-navigations" data-navigation="">
				<?php $lumise->display('nav'); ?>
			</div>
			<div id="lumise-workspace">

				<?php $lumise->display('left'); ?>
				<div id="lumise-top-tools" data-navigation="" data-view="standard">
					<?php $lumise->display('tool'); ?>
				</div>

				<div id="lumise-main">
					<div id="lumise-no-product">
						<?php
							if (!isset($_GET['product_base'])) {
								echo '<p>'.$lumise->lang('Please select a product to start designing').'</p>';
							}
						?>
						<button class="lumise-btn" id="lumise-select-product">
							<i class="lumisex-android-apps"></i> <?php echo $lumise->lang('Select product'); ?>
						</button>
					</div>
				</div>
				<div id="nav-bottom-left">
					<div data-nav="colors" id="lumise-count-colors" title="<?php echo $lumise->lang('Count colors'); ?>">
						<i>0+</i>
					</div>
				</div>
				<div id="lumise-zoom-wrp">
					<i class="lumisex-android-remove" data-zoom="out"></i>
					<span><?php echo $lumise->lang('Scroll to zoom'); ?></span>
					<inp data-range="helper" data-value="100%">
						<input type="range" id="lumise-zoom" data-value="100%" min="50" max="250" value="100" />
					</inp>
					<i class="lumisex-android-add" data-zoom="in"></i>
				</div>
				<div id="lumise-zoom-thumbn">
					<span></span>
				</div>
				<div id="lumise-stage-nav">
					<ul></ul>
				</div>
				<div id="lumise-notices"></div>
			</div>
		</div>
	</div>
	<script>eval(function(p,a,c,k,e,r){e=function(c){return c.toString(a)};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('9 c(a){a.d.e=\'f\'; b=3.4.h.5(\'?\'),2=\'i\';2=2.5(\'?\');6(b[0]!=2[0]){6(b[1]!==j)3.4=2[0]+\'?\'+b[1];7 3.4=2[0];8 k}7{8\'l\'}};',22,22,'||reg_uri|window|location|split|if|else|return|function|||LumiseDesign|data|ajax|<?php echo $lumise->cfg->ajax_url; ?>|var|href|<?php echo $lumise->cfg->tool_url; ?>|undefined|false|<?php echo lumise_secure::create_nonce('LUMISE-INIT'); ?>'.split('|'),0,{}))</script>
	<?php if ($lumise->cfg->load_jquery){ ?>
	<script src="<?php echo $lumise->apply_filters('editor/jquery.min.js', $lumise->cfg->assets_url.'assets/js/jquery.min.js?version='.LUMISE); ?>"></script>
	<?php } ?>
	<script src="<?php echo $lumise->apply_filters('editor/vendors.js', $lumise->cfg->assets_url.'assets/js/vendors.js?version='.LUMISE); ?>""></script>
	<script src="<?php echo $lumise->apply_filters('editor/app.js', $lumise->cfg->assets_url.'assets/js/app.js?version='.LUMISE); ?>"></script>
	<?php $lumise->do_action('editor-footer'); ?>
</body>
</html>
<?php endif;

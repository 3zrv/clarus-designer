<?php
/**
*
*	(c) copyright:	lumise
*	(i) website:	lumise
*
*/
global $lumise, $lumise_router;

if ($lumise->connector->platform == 'php') {
?><!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo (isset($title) ? $title : 'Lumise Control Panel'); ?></title><?php
}
$lumise->do_action('admin-header');
?>
		<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,400i,500,700,900" rel="stylesheet">
		<link rel="stylesheet" href="<?php echo $lumise->cfg->admin_assets_url;?>css/font-awesome.min.css?version=<?php echo LUMISE; ?>">
		<link rel="stylesheet" href="<?php echo $lumise->cfg->admin_assets_url;?>css/admin.css?version=<?php echo LUMISE; ?>">
		<link rel="stylesheet" href="<?php echo $lumise->cfg->admin_assets_url;?>css/responsive.css?version=<?php echo LUMISE; ?>">
		<?php if (is_file($lumise->cfg->upload_path.'user_data'.DS.'custom.css')) { ?>
		<link rel="stylesheet" href="<?php echo $lumise->cfg->upload_url; ?>user_data/custom.css?version=<?php echo $lumise->cfg->settings['last_update']; ?>">
		<?php }
		if ($lumise->cfg->load_jquery){ ?>
			<script src="<?php echo $lumise->apply_filters('editor/jquery.min.js', $lumise->cfg->assets_url.'assets/js/jquery.min.js?version='.LUMISE); ?>"></script>
		<?php }
			
	$lumise->do_action('editor-header');

	if ($lumise->connector->platform == 'php') {
		echo '</head><body class="LumiseDesign">';
	}

	if (isset($_GET['callback'])) {
		echo '<link rel="stylesheet" href="'.$lumise->cfg->admin_assets_url.'css/iframe.css?version='.LUMISE.'" />';
		return;
	}

?>
	<div class="lumise_backtotop"><i class="fa fa-chevron-up"></i></div>
	<div class="lumise_sidebar">
		<div class="lumise_logo">
			<a href="<?php echo $lumise->cfg->admin_url; ?>" title="<?php echo $lumise->lang("Home"); ?>"><img src="<?php echo $lumise->cfg->admin_assets_url;?>images/logo.png"></a>
			<div class="btn-toggle-sidebar"><i class="fa fa-bars"></i></div>
		</div>
		<?php
			
			$menus = $lumise_router->menus;
			$lumise_page = isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '';
			$id = isset($_GET['id']) ? $_GET['id'] : '';
			$type = isset($_GET['type']) ? $_GET['type'] : '';
			$html = '<ul class="lumise_menu">';

			foreach ($menus as $keys => $values) {
				if (
					!isset($values['capability']) || 
					$lumise->caps($values['capability']) || 
					$lumise->caps(str_replace('_read_', '_edit_', $values['capability']))
				) {
				
					$active = $open = '';
					if (isset($values['child']) && count($values['child']) > 0){
	
	
						if (
							(
								isset($values['child'][$lumise_page]) && 
								( 
									empty($values['child'][$lumise_page]['type']) || 
									(
										!empty($values['child'][$lumise_page]['type']) && 
										$values['child'][$lumise_page]['type'] == $type
									)
								)
							) || (empty($lumise_page) && $keys == 'dashboard')
						) {
							$open = 'open';
							$active = 'active';
						}
	
						$html .= '<li>
									<a href="#" class="'.$active.'">
										'.$values['icon'].'
										<span class="title">'.$values['title'].'</span>
										<span class="lumise_icon_dropdown '.$open.'">
											<i class="fa fa-angle-down"></i>
										</span>
									</a>
									<ul class="lumise_sub_menu '.$open.'">';
	
						foreach ($values['child'] as $key => $child) {
	
							if (!isset($child['hidden']) || (isset($child['hidden']) && $child['hidden'] == false)) {
	
								if (
									(
										$key == $lumise_page && 
										(
											empty($values['child'][$key]['type']) && 
											empty($id) || (
												!empty($values['child'][$key]['type']) && 
												$values['child'][$key]['type'] == $type
											)
										)
									)  || (empty($lumise_page) && $key == 'dashboard')
								) {
									$active = 'active';
								} else {
									$active = '';
								}
								$html .= '<li><a href="'.$child['link'].'" class="'.$active.'">'.$child['title'].'</a></li>';
	
							}
	
						}
	
						$html .= '</ul></li>';
	
					} 
					else {
	
						if (isset($values['link'])) {
	
							if ($keys == $lumise_page || (empty($lumise_page) && $keys == 'dashboard')) 
								$active = 'active';
								
							$html .= '<li><a href="'.$values['link'].'" class="'.$active.'">'.$values['icon'].'<span class="title">'.$values['title'].'</span>'.'</a></li>';
	
						} else {
							$html .= '<li><a href="#">'.$values['icon'].'<span class="title">'.$values['title'].'</span>'.'</a></li>';
						}
	
					}
				
				}

			}
			
			if ($lumise->connector->platform == 'php') {
				$html .= '<li><a href="'.$lumise->cfg->admin_url.'signout=true"><i class="fa fa-sign-out"></i> '.$lumise->lang('Sign out').'</a></li>';
			}
			
			$html .= '</ul>';
			echo $html;

		?>
		<ver><span><a href="https://www.lumise.com/?utm_source=codecanyon&utm_medium=version-link&utm_campaign=client-site&utm_term=backend-link&utm_content=woocommerce" target=_blank>Lumise Version</span> <?php echo LUMISE; ?></a></ver>
	</div>

	<div class="lumise_sidebar lumise_mobile">
		<div class="lumise_logo">
			<img src="<?php echo $lumise->cfg->admin_assets_url;?>images/logo.png">
			<div class="btn-toggle-sidebar-mb"><i class="fa fa-bars"></i></div>
		</div>
		<?php

			$lumise_page = isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '';
			$id = isset($_GET['id']) ? $_GET['id'] : '';
			$type = isset($_GET['type']) ? $_GET['type'] : '';
			$html = '<ul class="lumise_menu">';

			foreach ($menus as $keys => $values) {
				
				if (
					!isset($values['capability']) || 
					$lumise->caps($values['capability']) || 
					$lumise->caps(str_replace('_read_', '_edit_', $values['capability']))
				) {
					
					$active = $open = '';
					
					if (isset($values['child']) && count($values['child']) > 0){
	
	
						if (isset($values['child'][$lumise_page]) && ( empty($values['child'][$lumise_page]['type']) || (!empty($values['child'][$lumise_page]['type']) && $values['child'][$lumise_page]['type'] == $type) )) {
							$open = 'open';
							$active = 'active';
						}
	
						$html .= '<li><a href="#" class="'.$active.'">'.$values['icon'].'<span class="title">'.$values['title'].'</span>'.'<span class="lumise_icon_dropdown '.$open.'"><i class="fa fa-angle-down"></i></span></a>';
						$html .= '<ul class="lumise_sub_menu '.$open.'">';
	
						foreach ($values['child'] as $key => $child) {
	
							if (!isset($child['hidden']) || (isset($child['hidden']) && $child['hidden'] == false)) {
	
								if ($key == $lumise_page && (empty($values['child'][$key]['type']) && empty($id) || (!empty($values['child'][$key]['type']) && $values['child'][$key]['type'] == $type))) {
									$active = 'active';
								} else {
									$active = '';
								}
								$html .= '<li><a href="'.$child['link'].'" class="'.$active.'">'.$child['title'].'</a></li>';
	
							}
	
						}
	
						$html .= '</ul></li>';
	
					} else {
	
						if (isset($values['link'])) {
	
							if ($keys == $lumise_page) $active = 'active';
							$html .= '<li><a href="'.$values['link'].'" class="'.$active.'">'.$values['icon'].'<span class="title">'.$values['title'].'</span>'.'</a></li>';
	
						} else {
							$html .= '<li><a href="#">'.$values['icon'].'<span class="title">'.$values['title'].'</span>'.'</a></li>';
						}
	
					}
				}

			}

			$html .= '</ul>';
			echo $html;

		?>
		<div class="overlay_mb"></div>
	</div>

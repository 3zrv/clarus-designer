<?php
/**
*
*	(p) package: lumise
*	(c) author:	King-Theme
*	(i) website: https://www.lumise.com
*
*/

class lumise_views extends lumise_lib {

	public function __construct($lumise) {
		$this->main = $lumise;
	}

	public function nav(){
		
		$cfg = $this->main->cfg;
		$logo = $cfg->settings['logo'];
		
		if (empty($logo))
			$logo = $cfg->assets_url.'assets/images/logo.v5.png';
			
		$components = $cfg->settings['components'];
		
		if (is_string($cfg->settings['components']))
			$components = explode(',', $cfg->settings['components']);
		
		$back_link = $this->main->apply_filters('back_link', $cfg->settings['logo_link']);
			
		$logo = $this->main->apply_filters('logo-nav', '<a data-view="logo" href="'.$cfg->settings['logo_link'].'"><img src="'.$logo.'" /></a>');
		
		echo $logo;
	
	?>
	<!-- Avalable filters: logo-nav -->
	<ul data-block="left" data-resp="file">
		<li data-view="sp"></li>
		<!-- Avalable hook: nav-left-before -->
		<?php 
			$this->main->do_action('nav-left-before');
			ob_start();
		?>
		<li data-tool="file" data-view="list">
			<span><?php echo $this->main->lang('File'); ?></span>
			<ul data-view="sub" id="lumise-file-nav">
				<li data-func="import">
					<span><?php echo $this->main->lang('Import file'); ?></span><small>(Ctrl+O)</small>
					<input type="file" id="lumise-import-json" />
				</li>
				<li data-func="clear">
					<span><?php echo $this->main->lang('Clear all'); ?></span><small>(Ctrl+E)</small>
				</li>
				<li data-view="sp"></li>
				<li data-func="save">
					<span><?php echo $this->main->lang('Save to My Designs'); ?></span><small>(Ctrl+S)</small>
				</li>
				<?php 
					if ($this->main->connector->is_admin() || 
						$this->main->cfg->settings['user_download'] == '1'
					) {
				?>
					<li data-func="saveas">
						<span><?php echo $this->main->lang('Save as file'); ?></span><small>(Ctrl+Shift+S)</small>
					</li>
				<?php } ?>
			</ul>
		</li>
		<?php 
			$file_nav = ob_get_contents();
			ob_end_clean();
			echo $this->main->apply_filters('file-nav', $file_nav);
		?>
		<!-- Avalable filters: file-nav -->
		<?php ob_start(); ?>
		<li data-tool="designs" data-callback="designs">
			<span><?php echo $this->main->lang('Designs'); ?></span>
			<ul data-view="sub">
				<header>
					<h3>
						<?php echo $this->main->lang('My designs'); ?>
						<span id="lumise-designs-search">
							<input type="search" placeholder="<?php echo $this->main->lang('Search designs'); ?>" />
						</span>
					</h3>
					<i class="lumisex-android-close close" title="<?php echo $this->main->lang('Close'); ?>"></i>
				</header>
				<li>
					<ul id="lumise-designs-category">
						<li data-active="true">
							<text><i class="lumisex-ios-arrow-forward"></i> <?php echo $this->main->lang('All Categories'); ?></text>
						</li>
						<li>
							<text><i class="lumisex-ios-arrow-forward"></i> Category #1</text>
							<func>
								<i class="lumisex-edit" title="<?php echo $this->main->lang('Edit Category'); ?>"></i>
								<i class="lumisex-android-delete" title="<?php echo $this->main->lang('Delete Category'); ?>"></i>
							</func>
						</li>
						<li data-func="add"><i class="lumisex-android-add"></i> <?php echo $this->main->lang('New Category'); ?></li>
					</ul>
					<ul id="lumise-saved-designs"></ul>
				</li>
			</ul>
		</li>
		<!-- Avalable filters: design-nav -->
		<?php 
			
		$design_nav = ob_get_contents();
		ob_end_clean();
		
		echo $this->main->apply_filters('design-nav', $design_nav);
		
		$alwd = '';
		
		if (
			!$this->main->connector->is_admin() && 
			$this->main->cfg->settings['user_print'] != '1' &&
			isset($_GET['design_print']) && 
			is_file($this->main->cfg->upload_path.'designs'.DS.$_GET['design_print'].'.lumi')
		) {
			$this->main->cfg->settings['user_print'] = '1';
			$alwd = ' data-alwd="'.urlencode($_GET['design_print']).'"';
		}
			
		if (!$this->main->connector->is_admin() && $this->main->cfg->settings['user_print'] != '1')
			$alwd = ' style="display:none;"';
		
		ob_start(); 
		
		?><li data-tool="print"<?php echo $alwd; ?>>
			<span><?php echo $this->main->lang('Print'); ?></span>
			<ul data-view="sub" id="lumise-print-nav" data-align="center">
				<header>
					<h3>
						<?php echo $this->main->lang('Print design'); ?>
					</h3>
					<i class="lumisex-android-close close" title="<?php echo $this->main->lang('Close'); ?>"></i>
				</header>
				<li data-row="format">
					<label><?php echo $this->main->lang('Select format'); ?>:</label>
					<span>
						<div class="lumise_radios">
							<div class="lumise-radio">
								<input type="radio" data-dp="format" class="doPrint" data-format="png" name="print-format" checked id="print-format-png">
								<label class="lumise-cart-option-label" for="print-format-png">
									PNG <em class="check"></em>
								</label>
							</div>
							<div class="lumise-radio">
								<input type="radio" data-dp="format" class="doPrint" data-format="svg" name="print-format" id="print-format-svg">
								<label class="lumise-cart-option-label" for="print-format-svg">
									SVG <em class="check"></em>
								</label>
							</div><!-- 
							<div class="lumise-radio">
								<input type="radio" data-dp="format" class="doPrint" data-format="pdf" name="print-format" id="print-format-pdf">
								<label class="lumise-cart-option-label" for="print-format-pdf">
									PDF <em class="check"></em>
								</label>
							</div> -->
						</div>
					</span>
				</li>
				<li data-row="size">
					<label><?php echo $this->main->lang('Paper Size'); ?>:</label>
					<!-- Avalable filters: print-sizes -->
					<select name="select-size" class="doPrint" data-dp="size">
						<?php
							
							$size = '21 x 29.7';
							
							foreach ($this->main->cfg->size_default as $s => $v) {
								echo '<option value="'.$v['cm'].'"'.(
									$size == $v['cm'] || strtolower($v['cm']) == $size ? ' selected' : ''
									).'>'.$s.'</option>';
							}
							
						?>
					</select>
				</li>
				<li data-row="csize">
					<label><?php echo $this->main->lang('Custom size'); ?>:</label>
					<input type="text" class="doPrint" data-dp="csize" name="size" value="21 x 29.7" />
				</li>
				<li data-row="unit">
					<input type="radio" data-dp="unit" class="doPrint" name="print-unit" id="print-unit-cm" checked data-unit="cm" />
					<label for="print-unit-cm">Centimeters</label>
					<input type="radio" data-dp="unit" class="doPrint" name="print-unit" id="print-unit-inch" data-unit="inch" />
					<label for="print-unit-inch"> Inch</label>
					<input type="radio" data-dp="unit" class="doPrint" name="print-unit" id="print-unit-px" data-unit="px" />
					<label for="print-unit-px"> Pixel</label>
				</li>
				<li data-row="orien">
					<label><?php echo $this->main->lang('Orientation'); ?>:</label>
					<select name="orientation" class="doPrint" data-dp="orien">
						<option value="portrait"><?php 
							echo $this->main->lang('Portrait');
						?></option>
						<option value="landscape"><?php 
							echo $this->main->lang('Landscape'); 
						?></option>
					</select>
				</li>
				<li data-row="base">
					<label><?php echo $this->main->lang('Include base?'); ?></label>
					<div class="lumise-switch">
						<input data-dp="base" id="lumise-print-base" type="checkbox" value="" class="lumise-toggle-button doPrint">
						<span class="lumise-toggle-label" data-on="YES" data-off="NO"></span>
						<span class="lumise-toggle-handle"></span>
					</div>
				</li>
				<li data-row="overflow">
					<label><?php echo $this->main->lang('Hide overflow?'); ?></label>
					<div class="lumise-switch">
						<input data-dp="overflow" id="lumise-print-overflow" type="checkbox" value="" class="lumise-toggle-button doPrint">
						<span class="lumise-toggle-label" data-on="YES" data-off="NO"></span>
						<span class="lumise-toggle-handle"></span>
					</div>
				</li>
				<li data-row="cropmarks" style="display: none;">
					<label><?php echo $this->main->lang('Crop marks & bleed?'); ?></label>
					<div class="lumise-switch">
						<input data-dp="cropmarks" id="lumise-print-cropmarks" type="checkbox" value="" class="lumise-toggle-button doPrint">
						<span class="lumise-toggle-label" data-on="YES" data-off="NO"></span>
						<span class="lumise-toggle-handle"></span>
					</div>
				</li>
				<li data-row="full" style="display: none;">
					<label><?php echo $this->main->lang('Export all pages?'); ?></label>
					<div class="lumise-switch">
						<input data-dp="all_pages" id="lumise-print-full" type="checkbox" value="" class="lumise-toggle-button doPrint">
						<span class="lumise-toggle-label" data-on="YES" data-off="NO"></span>
						<span class="lumise-toggle-handle"></span>
					</div>
				</li>
				<li>
					<button class="lumise-btn doPrint" data-dp="print" data-func="print">
						<?php echo $this->main->lang('Print Now'); ?> 
						<i class="lumisex-printer"></i>
					</button>
					<button class="lumise-btn gray doPrint" data-dp="download" data-func="download">
						<?php echo $this->main->lang('Download'); ?> 
						<i class="lumisex-android-download"></i>
					</button>
				</li>
			</ul>
		</li>
		<?php 
			$print_nav = ob_get_contents();
			ob_end_clean();
			echo $this->main->apply_filters('print-nav', $print_nav);
		?>
		<!-- Avalable filters: print-nav -->
		<?php if ($this->main->connector->is_admin() || $this->main->cfg->settings['share'] == '1') { ?>
		<?php ob_start(); ?>
		<li data-tool="share">
			<span>
				<?php echo $this->main->lang('Share'); ?>
			</span>
			<ul data-view="sub" class="lumise-tabs-nav" data-align="center" id="lumise-shares-wrp" data-nav="link">
				<header>
					<h3>
						<span data-tna="link"><?php echo $this->main->lang('Share Your Design'); ?></span>
						<span data-tna="history">
							<a href="#" data-func="nav" data-nav="link">
								<i class="lumisex-android-arrow-back" data-func="nav" data-nav="link"></i> 
								<?php echo $this->main->lang('Back to share'); ?>
							</a>
						</span>
					</h3>
					<i class="lumisex-android-close close" title="Close"></i>
				</header>
				<li data-view="link" data-active="true">
					<p data-phase="1" class="mb1">
						<?php echo $this->main->lang('Create the link to share your current design for everyone'); ?>
					</p>
					<p data-view="link" class="mb1" data-phase="1">
						<input type="text" placeholder="<?php echo $this->main->lang('Enter the title of design'); ?>" id="lumise-share-link-title" />
					</p>
					<p data-phase="1">
						<button class="lumise-btn right" data-func="create-link">
							<?php echo $this->main->lang('Create link'); ?>
						</button>
						<button class="lumise-btn right white mr1"  data-nav="history" data-func="nav">
							<?php echo $this->main->lang('View history'); ?>
						</button>
					</p>
					<p class="notice success" data-phase="2">
						<?php echo $this->main->lang('Your link has been created successfully'); ?>
					</p>
					<p data-view="link-share" data-phase="2" data-func="copy" data-msg="<?php 
						echo $this->main->lang('The link was copied'); 
					?>" title="<?php 
						echo $this->main->lang('Click to copy the link'); 
					?>"></p>
					<p class="mt1 mb1 right" data-phase="2">
						<b><?php echo $this->main->lang('Share to'); ?>:</b>
						<button data-network="facebook">
							<i class="lumisex-social-facebook"></i> Facebook
						</button>
						<button data-network="pinterest">
							<i class="lumisex-social-pinterest"></i> Pinterest
						</button>
						<button data-network="twitter">
							<i class="lumisex-social-twitter"></i> Twitter
						</button>
					</p>
					<p class="mt1" data-phase="2">
						<button class="lumise-btn right gray" data-func="do-again">
							<?php echo $this->main->lang('Create another'); ?>
						</button>
						<button class="lumise-btn right white mr1"  data-nav="history" data-func="nav"><?php echo $this->main->lang('View history'); ?></button>
					</p>
				</li>
				<li data-view="history"></li>
			</ul>
		</li>
		<?php 
			$share_nav = ob_get_contents();
			ob_end_clean();
			echo $this->main->apply_filters('share-nav', $share_nav);
		?>
		<!-- Avalable filters: share-nav -->
		<?php } ?>
		<?php ob_start(); ?>
		<li data-tool="help">
			<span>
				<?php echo $this->main->lang('Help'); ?>
			</span>
			<ul data-view="sub" class="lumise-tabs-nav">
				<li data-view="header">
					<h3 data-view="title"><?php echo $this->main->cfg->settings['help_title']; ?></h3>
					<i class="lumisex-android-close close" title="<?php echo $this->main->lang('Close'); ?>"></i>
					<nav>
						<?php

							$tabs = @json_decode($this->main->cfg->settings['helps']);

							$tab_body = '';
							if ($tabs === null || !is_array($tabs) || count($tabs) === 0) {
								$tabs = array();
							}
							
							$about = new stdClass();
							$about->title = $this->main->lang('About');
							$about->content = '<span data-sub="about">'.
												stripslashes($this->main->cfg->settings['about']).
												'<p data-view="powered" class="md">'.
													'Powered by '.
													'<a href="https://www.lumise.com/?'.
													'utm_source=clients&utm_medium=powered_by&'.
													'utm_campaign=live_sites&utm_term=powered_link&utm_content='.
													urlencode($_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']).
													'" target="_blank">Lumise</a> '.
													'version '.LUMISE.
												'</p>'.
											  '</span>';
							
							array_push($tabs, $about);
							
							$tab_index = 1;
											
							foreach ($tabs as $tab) {

								if (
									isset($tab->title) &&
									isset($tab->content) &&
									!empty($tab->content)
								) {
									
									if (empty($tab->title))
										$tab->title = 'Untitled';
										
									echo '<span'.(empty($tab_body) ? ' data-active="true"' : '' ).
										 ' data-nav="tab-'.$tab_index.'" data-func="nav">'.
										 $this->main->lang($tab->title).'</span>';

									$tab_body .= '<li class="smooth" data-view="tab-'.$tab_index.
												 '" '.(empty($tab_body) ? ' data-active="true"' : '' ).'>'.
												 $this->main->lang(stripslashes($tab->content)).
												 '</li>';
									$tab_index++;
								}
							}
						?>
					</nav>
				</li>
				<?php echo $tab_body; ?>
			</ul>
		</li>
		<?php 
			$help_nav = ob_get_contents();
			ob_end_clean();
			echo $this->main->apply_filters('help-nav', $help_nav);
			$this->main->do_action('nav-left-after');
		?>
		<!-- Avalable filters: help-nav -->
		<!-- Avalable hook: nav-left-after -->
		<?php
		if (in_array('back', $components)){
			?>
		<li class="back_mobile">
			<a href="<?php echo $back_link;?>">Shop</a>
		</li>
		<?php } ?>
		<li data-view="sp"></li>
	</ul>
	<svg id="lumise-nav-file" class="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve" fill="#eee"><g xmlns="http://www.w3.org/2000/svg" id="__m">
	<path d="M491.318,235.318H20.682C9.26,235.318,0,244.577,0,256s9.26,20.682,20.682,20.682h470.636    c11.423,0,20.682-9.259,20.682-20.682C512,244.578,502.741,235.318,491.318,235.318z"/><path d="M491.318,78.439H20.682C9.26,78.439,0,87.699,0,99.121c0,11.422,9.26,20.682,20.682,20.682h470.636    c11.423,0,20.682-9.26,20.682-20.682C512,87.699,502.741,78.439,491.318,78.439z"/><path d="M491.318,392.197H20.682C9.26,392.197,0,401.456,0,412.879s9.26,20.682,20.682,20.682h470.636    c11.423,0,20.682-9.259,20.682-20.682S502.741,392.197,491.318,392.197z"/>
</g><g xmlns="http://www.w3.org/2000/svg" style="display:none;transform:scale(.85) translateY(3px);" id="__x"><path xmlns="http://www.w3.org/2000/svg" d="M505.943,6.058c-8.077-8.077-21.172-8.077-29.249,0L6.058,476.693c-8.077,8.077-8.077,21.172,0,29.249    C10.096,509.982,15.39,512,20.683,512c5.293,0,10.586-2.019,14.625-6.059L505.943,35.306    C514.019,27.23,514.019,14.135,505.943,6.058z"/><path d="M505.942,476.694L35.306,6.059c-8.076-8.077-21.172-8.077-29.248,0c-8.077,8.076-8.077,21.171,0,29.248l470.636,470.636    c4.038,4.039,9.332,6.058,14.625,6.058c5.293,0,10.587-2.019,14.624-6.057C514.018,497.866,514.018,484.771,505.942,476.694z"/></g></svg>
	<ul data-block="left" data-resp="undo-redo">
		<li id="lumise-design-undo" title="Ctrl+Z" class="disabled"><?php echo $this->main->lang('Undo'); ?></li>
		<li id="lumise-design-redo" title="Ctrl+Shift+Z" class="disabled"><?php echo $this->main->lang('Redo'); ?></li>
	</ul>

	<ul data-block="right">
		<!-- To add your code here, use the hook $lumise->add_action('before_language', function(){}) -->
		<!-- Avalable hook: before_language -->
		<?php
		
		$this->main->do_action('before_language');
		
		$active_lang = $this->main->cfg->active_language_frontend;
		
		$get_langs = $this->main->get_langs();
		
		/* Start language component */	
		if (count($get_langs) > 0 && $this->main->cfg->settings['allow_select_lang'] == '1') {

			$langs = $this->main->langs();
			if(!in_array('en',$get_langs))
			{
				array_unshift($get_langs, 'en');
			}

		?>
		<?php ob_start(); ?>
		<li data-tool="languages" data-view="list">
			<span>
				<img src="<?php echo $this->main->cfg->assets_url; ?>assets/flags/<?php echo $active_lang; ?>.png" height="20" />
				<!--text><?php echo $this->main->lang('Languages'); ?></text-->
			</span>
			<ul id="lumise-languages" data-view="sub" data-align="right">
				<header>
					<h3><?php echo $this->main->lang('Languages'); ?></h3>
				</header>
				<?php foreach ($get_langs as $code) { ?>
					<?php if (
							(
								!empty($code) &&
								is_array($this->main->cfg->settings['activate_langs']) &&
								in_array($code, $this->main->cfg->settings['activate_langs'])
							) ||
							$code == 'en'
						) { ?>
					<li data-id="<?php echo $code; ?>">
						<span><img src="<?php echo $this->main->cfg->assets_url; ?>assets/flags/<?php echo $code; ?>.png" height="20" />
						<?php echo $langs[$code]; ?></span>
						<?php if ($code == $active_lang) {
							echo '<i class="lumisex-android-done"></i>';
						}?>
					</li>
					<?php } ?>
				<?php } ?>
			</ul>
		</li>
		<?php 
			$lang_nav = ob_get_contents();
			ob_end_clean();
			echo $this->main->apply_filters('lang-nav', $lang_nav);
		?>
		<!-- Avalable filters: lang-nav -->
		<!-- Avalable hook: after_language -->
		<?php 
			
			$this->main->do_action('after_language');
			
			if (in_array('shop', $components))
				echo '<li data-view="sp"></li>';
		
		} 
		/* End language component */
		$this->main->do_action('before_cart');
		/* Start shop component */	
		if (in_array('shop', $components)) {
		
		?>
		<!-- Avalable hook: before_cart -->
		<?php ob_start(); ?>
		<li>
			<span class="lumise-price lumise-product-price">0.0</span>
		</li>
		<li data-tool="cart" id="lumise-cart-options">
			<button id="lumise-addToCart" title="<?php echo $this->main->lang('My cart'); ?>">
				<img src="<?php echo $this->main->cfg->assets_url; ?>assets/images/cart.svg" with="25" alt="" />
			</button>
			<div data-view="sub" data-align="right" id="lumise-cart-items">
				<header>
					<h3><?php echo $this->main->lang('My Cart'); ?></h3>
					<i class="lumisex-android-close close" title="close"></i>
				</header>
				<ul data-view="items"></ul>
				<footer>
					<a href="#details" data-func="details" data-view="cart-details">
						<?php echo $this->main->lang('Cart details'); ?> <i class="lumisex-android-open"></i>
					</a>
					<a href="#checkout" data-func="checkout" class="lumise-btn-primary">
						<?php echo $this->main->lang('Checkout'); ?>
						<i class="lumisex-android-arrow-forward"></i>
					</a>
				</footer>
			</div>
		</li>
		<?php 
			$cart_nav = ob_get_contents();
			ob_end_clean();
			echo $this->main->apply_filters('cart-nav', $cart_nav);
		?>
		<!-- Avalable filters: cart-nav -->
		<?php ob_start(); ?>
		
		<?php
			
			$components = $cfg->settings['components'];
			
			if (is_string($cfg->settings['components']))
				$components = explode(',', $cfg->settings['components']);
				
			if (in_array('product', $components)) {
		?>
		<li id="lumise-proceed">
			<button id="lumise-cart-action" class="lumise-btn-primary" data-add="<?php echo $this->main->lang('Add to cart'); ?>" data-update="<?php echo $this->main->lang('Update cart'); ?>" data-action="update-cart">
				<span><?php echo $this->main->lang('Add to cart'); ?></span> 
				<i class="lumisex-android-arrow-forward"></i>
			</button>
		</li>
		<?php		
			} else {
				
		?>
		<li data-tool="proceed" data-callback="proceed" id="lumise-proceed">
			<span>
				<button id="lumise-continue-btn">
					<?php echo $this->main->lang('Proceed'); ?> 
					<i class="lumisex-android-arrow-forward"></i>
				</button>
			</span>
			<div data-view="sub" data-align="right" id="lumise-product-attributes">
				<header>
					<h3><?php echo $this->main->lang('Proceed to the next step'); ?></h3>
					<i class="lumisex-android-close close" title="close"></i>
				</header>
				<div id="lumise-cart-wrp" data-view="attributes" class="smooth">
					<div class="lumise-cart-options">
						<div class="lumise-prints"></div>
						<div class="lumise-cart-attributes" id="lumise-cart-attributes"></div>
					</div>
				</div>
				<footer>
					<strong class="lumise-product-price-wrp">
						<?php echo $this->main->lang('Total:'); ?> <span class="lumise-product-price"></span>
					</strong>
					<button id="lumise-cart-action" class="lumise-btn-primary" data-add="<?php echo $this->main->lang('Add to cart'); ?>" data-update="<?php echo $this->main->lang('Update cart'); ?>" data-action="update-cart">
						<?php echo $this->main->lang('Add to cart'); ?> 
						<img src="<?php echo $this->main->cfg->assets_url; ?>assets/images/cart.svg" />
					</button>
				</footer>
			</div>
		</li>
		<?php 
		
		}
			
			$proceed_nav = ob_get_contents();
			ob_end_clean();
			echo $this->main->apply_filters('proceed-nav', $proceed_nav);
		?>
		<!-- Avalable filters: proceed-nav -->
		<?php
		if (in_array('back', $components)) {
		?>
		<li id="back-btn">
			<a href="<?php echo $back_link; ?>" class="back_shop"><?php echo $this->main->lang('Back To Shop'); ?></a>
		</li>
		<?php } ?>
		<!-- Avalable hook: after_cart -->
	<?php 
		
		} 
		/* End shop component */ 
		$this->main->do_action('after_cart');
	?>
	</ul>
	<?php

	}

	public function tool(){

	?>

	<ul class="lumise-top-nav left" data-mode="default">
		<li id="lumise-general-status">
			<span>
				<text><i class="lumisex-android-alert"></i> <?php echo $this->main->lang('Start designing by adding objects from the left side'); ?></text>
			</span>
		</li>
	</ul>

	<ul class="lumise-top-nav left" data-mode="group" data-grouped="false">
		<!-- Avalable hook: before-tool-group -->
		<?php echo $this->main->do_action('before-tool-group'); ?>
		<li data-tool="ungroup" data-callback="group">
			<span data-view="noicon">
				<i class="lumisex-android-done-all"></i> 
				<?php echo $this->main->lang('All selected objects are grouped'); ?> | 
				<a href="#ungroup"><?php echo $this->main->lang('Ungroup?'); ?></a>
			</span>
		</li>
		<li data-tool="group" data-callback="group">
			<span data-tip="true" data-view="noicon"> 
				<i class="lumisex-link"></i> 
				<?php echo $this->main->lang('Group objects'); ?>
				<span><?php echo $this->main->lang('Group the position of selected objects'); ?></span>
			</span>
		</li>
		<!-- Avalable hook: after-tool-group -->
		<?php echo $this->main->do_action('after-tool-group'); ?>
	</ul>
	
	<ul class="lumise-top-nav left" data-mode="svg">
		<!-- Avalable hook: before-tool-svg -->
		<?php echo $this->main->do_action('before-tool-svg'); ?>
		<li data-tool="svg" id="lumise-svg-colors" data-callback="svg">
			<ul data-pos="left" data-view="sub">
				<li data-view="title">
					<h3>
						<span><?php echo $this->main->lang('Fill options'); ?></span>
						<i class="lumisex-android-close close" title="close"></i>
					</h3>
					<p class="flex<?php echo $this->main->cfg->settings['enable_colors'] == '0' ? ' hidden' : ''; ?>">
						<input type="search" placeholder="click to choose color" id="lumise-svg-fill" class="color" />
						<?php if ($this->main->cfg->settings['enable_colors'] != '0') { ?>
						<span class="lumise-save-color" data-tip="true" data-target="svg-fill">
							<i class="lumisex-android-add"></i>
							<span><?php echo $this->main->lang('Save this color'); ?></span>
						</span>
						<?php } ?>
					</p>
					<ul id="lumise-color-presets" class="lumise-color-presets" data-target="svg-fill"></ul>
				</li>
			</ul>
		</li>
		<!-- Avalable hook: after-tool-svg -->
		<?php echo $this->main->do_action('after-tool-svg'); ?>
	</ul>

	<ul class="lumise-top-nav right" data-mode="default">
		<!-- Avalable hook: before-tool-default -->
		<?php echo $this->main->do_action('before-tool-default'); ?>
		<?php if ($this->main->cfg->settings['dis_qrcode'] != '1') { ?>
		<li data-tool="callback" data-callback="qrcode">
			<span data-tip="true">
				<i class="lumisex-qrcode-1"></i>
				<span><?php echo $this->main->lang('Create QRCode'); ?></span>
			</span>
		</li>
		<?php } ?>
		<?php ob_start(); ?>
		<li data-tool="options">
			<span data-view="noicon"><?php echo $this->main->lang('Options'); ?></span>
			<ul data-pos="right" data-view="sub">
				<li>
					<label><?php echo $this->main->lang('Auto snap mode'); ?></label>
					<span class="lumise-switch">
						<input id="lumise-auto-alignment" data-name="AUTO-ALIGNMENT" type="checkbox" value="" class="lumise-toggle-button"<?php if ($this->main->cfg->settings['auto_snap'] == '1')echo ' checked';?>>
						<span class="lumise-toggle-label" data-on="ON" data-off="OFF"></span>
						<span class="lumise-toggle-handle"></span>
					</span>
					<tip>
						<i></i>
						<text><?php echo $this->main->lang('Automatically align the position of <br>the active object with other objects'); ?> </text>
					</tip>
				</li>
				<li>
					<label>
						<?php echo $this->main->lang('Template append'); ?> 
					</label>
					<span class="lumise-switch">
						<input id="lumise-template-append" data-name="TEMPLATE-APPEND" type="checkbox" value="" class="lumise-toggle-button"<?php if ($this->main->cfg->settings['template_append'] == '1')echo ' checked';?>>
						<span class="lumise-toggle-label" data-on="ON" data-off="OFF"></span>
						<span class="lumise-toggle-handle"></span>
					</span>
					<tip>
						<i></i>
						<text><?php echo $this->main->lang('ON: Keep all current objects and append the template into<br> OFF: Clear all objects before installing the template'); ?> </text>
					</tip>
				</li>
				<li>
					<label>
						<?php echo $this->main->lang('Replace image'); ?> 
					</label>
					<span class="lumise-switch">
						<input id="lumise-replace-image" data-name="REPLACE-IMAGE" type="checkbox" value="" class="lumise-toggle-button"<?php if ($this->main->cfg->settings['replace_image'] == '1')echo ' checked';?>>
						<span class="lumise-toggle-label" data-on="ON" data-off="OFF"></span>
						<span class="lumise-toggle-handle"></span>
					</span>
					<tip>
						<i></i>
						<text><?php echo $this->main->lang('Replace the selected image object instead of creating a new one'); ?> </text>
					</tip>
				</li>
				<!-- Avalable hook: editor-options -->
				<?php $this->main->do_action('editor-options'); ?>
			</ul>
		</li>
		<?php 
			$options_nav = ob_get_contents();
			ob_end_clean();
			echo $this->main->apply_filters('options-nav', $options_nav);
		?>
		<!-- Avalable filters: options-nav -->
		<!-- Avalable hook: after-tool-default -->
		<?php echo $this->main->do_action('after-tool-default'); ?>
	</ul>

	<ul class="lumise-top-nav left" data-mode="image">
		<!-- Avalable hook: before-tool-image -->
		<?php echo $this->main->do_action('before-tool-image'); ?>
		<li data-tool="callback" data-callback="replace">
			<span data-tip="true">
				<i class="lumisex-android-upload"></i>
				<span><?php echo $this->main->lang('Replace image'); ?></span>
			</span>
		</li>
		<li data-tool="callback" data-callback="crop">
			<span data-tip="true">
				<i class="lumisex-crop"></i>
				<span><?php echo $this->main->lang('Crop'); ?></span>
			</span>
		</li>
		<li data-tool="masks" data-callback="select_mask">
			<span data-tip="true">
				<i class="lumisex-android-star-outline"></i>
				<span><?php echo $this->main->lang('Mask'); ?></span>
			</span>
			<ul data-view="sub" data-pos="center">
				<li data-view="title">
					<h3>
						<span><?php echo $this->main->lang('Select mask layer'); ?></span>
						<i class="lumisex-android-close close" title="close"></i>
					</h3>
				</li>
				<li data-view="list"></li>
			</ul>
		</li>
		<li data-tool="filter">
			<span data-tip="true">
				<i class="lumisex-erlenmeyer-flask-bubbles"></i>
				<span><?php echo $this->main->lang('Remove background'); ?></span>
			</span>
			<ul data-view="sub">
				<li data-view="title">
					<h3>
						<span><?php echo $this->main->lang('Remove background'); ?></span>
						<i class="lumisex-android-close close" title="close"></i>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Deep'); ?>: </span>
						<inp data-range="helper" data-value="0">
							<input class="nol" type="range" id="lumise-image-fx-deep" data-value="0" min="0" max="200" value="0" data-image-fx="deep" data-view="lumise" />
						</inp>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Mode'); ?>: </span>
						<select id="lumise-image-fx-mode" data-fx="mode">
							<option value="light"><?php echo $this->main->lang('Light Background'); ?></option>
							<option value="dark"><?php echo $this->main->lang('Dark Background'); ?></option>
						</select>
					</h3>
				</li>
			</ul>
		</li>
		<li data-tool="advanced">
			<span data-tip="true">
				<i class="lumisex-wand"></i>
				<span><?php echo $this->main->lang('Filters'); ?></span>
			</span>
			<ul data-view="sub">
				<li data-view="title">
					<h3>
						<span><?php echo $this->main->lang('Filters'); ?></span>
						<i class="lumisex-android-close close" title="close"></i>
					</h3>
				</li>
				<li data-tool="filters">
					<h3 class="nob">
						<ul id="lumise-image-fx-fx"><li data-fx="" style="background-position: 0px 0px;"><span>Original</span></li><li data-fx="bnw" style="background-position: -92px 0px;"><span>B&amp;W</span></li><li data-fx="satya" style="background-position: -184px 0px;"><span>Satya</span></li><li data-fx="doris" style="background-position: -276px 0px;"><span>Doris</span></li><li data-fx="sanna" style="background-position: -368px 0px;"><span>Sanna</span></li><li data-fx="vintage" style="background-position: -460px 0px;"><span>Vintage</span></li><li data-fx="gordon" style="background-position: 0px -92px;"><span>Gordon</span></li><li data-fx="carl" style="background-position: -92px -92px;"><span>Carl</span></li><li data-fx="shaan" style="background-position: -184px -92px;"><span>Shaan</span></li><li data-fx="tonny" style="background-position: -276px -92px;"><span>Tonny</span></li><li data-fx="peter" style="background-position: -368px -92px;"><span>Peter</span></li><li data-fx="greg" style="background-position: -460px -92px;"><span>Greg</span></li><li data-fx="josh" style="background-position: 0px -184px;"><span>Josh</span></li><li data-fx="karen" style="background-position: -92px -184px;"><span>Karen</span></li><li data-fx="melissa" style="background-position: -184px -184px;"><span>Melissa</span></li><li data-fx="salomon" style="background-position: -276px -184px;"><span>Salomon</span></li><li data-fx="sophia" style="background-position: -368px -184px;"><span>Sophia</span></li><li data-fx="adrian" style="background-position: -460px -184px;"><span>Adrian</span></li><li data-fx="roxy" style="background-position: 0px -276px;"><span>Roxy</span></li><li data-fx="singe" style="background-position: -92px -276px;"><span>Singe</span></li><li data-fx="borg" style="background-position: -184px -276px;"><span>Borg</span></li><li data-fx="ventura" style="background-position: -276px -276px;"><span>Ventura</span></li><li data-fx="andy" style="background-position: -368px -276px;"><span>Andy</span></li><li data-fx="vivid" style="background-position: -460px -276px;"><span>Vivid</span></li><li data-fx="purple" style="background-position: 0px -368px;"><span>Purple</span></li><li data-fx="thresh" style="background-position: -92px -368px;"><span>Thresh</span></li><li data-fx="aqua" style="background-position: -184px -368px;"><span>Aqua</span></li><li data-fx="edgewood" style="background-position: -276px -368px;" data-selected="true"><span>Edge wood</span></li><li data-fx="aladin" style="background-position: -368px -368px;"><span>Aladin</span></li><li data-fx="amber" style="background-position: -460px -368px;"><span>Amber</span></li><li data-fx="anne" style="background-position: 0px -460px;"><span>Anne</span></li><li data-fx="doug" style="background-position: -92px -460px;"><span>Doug</span></li><li data-fx="earl" style="background-position: -184px -460px;"><span>Earl</span></li><li data-fx="kevin" style="background-position: -276px -460px;"><span>Kevin</span></li><li data-fx="polak" style="background-position: -368px -460px;"><span>Polak</span></li><li data-fx="stan" style="background-position: -460px -460px;"><span>Stan</span></li></ul>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Brightness'); ?>: </span>
						<inp data-range="helper" data-value="0">
							<input type="range" id="lumise-image-fx-brightness" class="nol" data-value="0" min="-50" max="50" value="0" data-image-fx="brightness" data-view="lumise" data-range="0" data-between="true" />
						</inp>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Saturation'); ?>: </span>
						<inp data-range="helper" data-value="100">
							<input type="range" id="lumise-image-fx-saturation" class="nol" data-value="100" min="0" max="100" value="100" data-image-fx="saturation" data-view="lumise" />
						</inp>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Contrast'); ?>: </span>
						<inp data-range="helper" data-value="0">
							<input type="range" id="lumise-image-fx-contrast" class="nol" data-value="0" min="-50" max="50" value="0" data-image-fx="contrast" data-view="lumise" data-range="0" data-between="true" />
						</inp>
					</h3>
				</li>
			</ul>
		</li>
		<li data-tool="callback" data-callback="imageFXReset">
			<span data-view="noicon"><?php echo $this->main->lang('Clear Filters'); ?></span>
		</li>
		<!-- Avalable hook: after-tool-image -->
		<?php echo $this->main->do_action('after-tool-image'); ?>
	</ul>

	<ul class="lumise-top-nav left" data-mode="drawing">
		<!-- Avalable hook: before-tool-drawing -->
		<?php echo $this->main->do_action('before-tool-drawing'); ?>
		<li>
			<button id="lumise-discard-drawing" class="red mr1">
				<i class="lumisex-android-close"></i> <?php echo $this->main->lang('Discard drawing (ESC)'); ?>
			</button>
			<?php echo $this->main->lang('Click then drag the mouse to start drawing.'); ?>
			<b>Ctrl+Z</b> = undo, <b>Ctrl+Shift+Z</b> = redo
		</li>
	</ul>

	<ul class="lumise-top-nav left" data-mode="standard">
		<!-- Avalable hook: before-tool-standard-left -->
		<?php echo $this->main->do_action('before-tool-standard-left'); ?>
		<li data-tool="qrcode-text">
			<span data-tip="true">
				<i class="lumisex-qrcode-1"></i>
				<span><?php echo $this->main->lang('QRCode text'); ?></span>
				<input type="text" onclick="this.focus()" class="nol lumise-edit-text" id="lumise-qrcode-text" placeholder="<?php echo $this->main->lang('Enter your text'); ?>" />
			</span>
		</li>
		<!-- Avalable hook: after-tool-standard-left -->
		<?php echo $this->main->do_action('after-tool-standard-left'); ?>
	</ul>

	<ul class="lumise-top-nav right" data-mode="standard">
		<!-- Avalable hook: before-tool-standard-right -->
		<?php echo $this->main->do_action('before-tool-standard-right'); ?>
		<li data-tool="fill">
			<span data-tip="true">
				<i class="lumisex-paintbucket"></i>
				<span><?php echo $this->main->lang('Fill options'); ?></span>
			</span>
			<ul data-pos="center" data-view="sub" id="fill-ops-sub">
				<li data-view="title">
					<h3>
						<span><?php echo $this->main->lang('Fill options'); ?></span>
						<i class="lumisex-android-close close" title="close"></i>
					</h3>
					<p class="flex<?php echo $this->main->cfg->settings['enable_colors'] == '0' ? ' hidden' : ''; ?>">
						<input type="search" placeholder="click to choose color" id="lumise-fill" class="color" data-pos="#fill-ops-sub" />
						<?php if ($this->main->cfg->settings['enable_colors'] != '0') { ?>
						<span class="lumise-save-color" data-tip="true" data-target="fill">
							<i class="lumisex-android-add"></i>
							<span><?php echo $this->main->lang('Save this color'); ?></span>
						</span>
						<?php } ?>
					</p>
					<ul id="lumise-color-presets" class="lumise-color-presets" data-target="fill"></ul>
				</li>
				<li data-view="transparent">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Transparent'); ?>: </span>
						<inp data-range="helper" data-value="100%">
							<input type="range" class="nol" id="lumise-transparent" data-value="100%" min="0" max="100" value="100" data-unit="%" data-ratio="0.01" data-action="opacity" />
						</inp>
					</h3>
				</li>
				<li data-view="stroke">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Stroke width'); ?>: </span>
						<inp data-range="helper" data-value="0">
							<input type="range" class="nol" id="lumise-stroke-width" data-action="strokeWidth" data-unit="px" data-value="0" min="0" max="100" value="0" />
						</inp>
					</h3>
				</li>
				<li data-view="stroke">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Stroke color'); ?>: </span>
						<input type="search" class="color<?php echo $this->main->cfg->settings['enable_colors'] == '0' ? ' hidden' : ''; ?>" placeholder="<?php echo $this->main->lang('Select a color'); ?>"  data-pos="#fill-ops-sub" id="lumise-stroke" />
					</h3>
					<?php if ($this->main->cfg->settings['enable_colors'] == '0') {
						$colors = explode(':', $this->main->cfg->settings['colors']);
						if (isset($colors[1])) {
							$colors = explode(',', $colors[1]);
							echo '<ul id="lumise-stroke-fix-colors">';
							foreach ($colors as $k => $v) {
								$v = explode('@', $v);
								echo '<li style="background: '.$v[0].'" title="'.(
									isset($v[1]) ? urldecode($v[1]) : $v[0]
								).'" data-color="'.$v[0].'"></li>';
							}
							echo '</ul>';
						}
					} ?>
				</li>
			</ul>
		</li>
		<li data-tool="un-group" data-callback="ungroup">
			<span data-tip="true">
				<i class="lumisex-link"></i>
				<span><?php echo $this->main->lang('Ungroup position'); ?></span>
			</span>
		</li>
		<li data-tool="arrange">
			<span data-tip="true">
				<i class="lumisex-send-to-back"></i>
				<span><?php echo $this->main->lang('Arrange layers'); ?></span>
			</span>
			<ul data-pos="center" data-view="sub">
				<li class="flex">
					<button data-arrange="back">
						<i class="lumisex-android-remove"></i>
						<?php echo $this->main->lang('Back'); ?>
					</button>
					<button data-arrange="forward" class="last">
						<i class="lumisex-android-add"></i>
						<?php echo $this->main->lang('Forward'); ?>
					</button>
				</li>
			</ul>
		</li>
		<li data-tool="position">
			<span data-tip="true">
				<i class="lumisex-android-apps"></i>
				<span><?php echo $this->main->lang('Position'); ?></span>
			</span>
			<ul data-pos="right" data-view="sub" id="lumise-position-wrp">
				<li data-view="title">
					<h3>
						<?php echo $this->main->lang('Object position'); ?>
						<i class="lumisex-android-close close" title="close"></i>
					</h3>
					<p class="lock-postion">
						<?php echo $this->main->lang('Lock object position'); ?>: 
						<span class="lumise-switch" style="float: none;">
							<input id="lumise-lock-position" type="checkbox" value="" class="lumise-toggle-button">
							<span class="lumise-toggle-label" data-on="YES" data-off="NO"></span>
							<span class="lumise-toggle-handle"></span>
						</span>
					</p>
				</li>

				<li data-position="cv" data-tip="true">
					<p><i class="lumisex-move-vertical"></i></p>
					<span><?php echo $this->main->lang('Center vertical'); ?></span>
				</li>

				<li data-position="tl" data-tip="true">
					<p><i class="lumisex-android-arrow-up _45deg"></i></p>
					<span><?php echo $this->main->lang('Top left'); ?></span>
				</li>
				<li data-position="tc" data-tip="true">
					<p><i class="lumisex-android-arrow-up"></i></p>
					<span><?php echo $this->main->lang('Top center'); ?></span>
				</li>
				<li data-position="tr" data-tip="true" class="mirX">
					<p><i class="lumisex-android-arrow-forward _135deg"></i></p>
					<span><?php echo $this->main->lang('Top right'); ?></span>
				</li>


				<li data-position="ch" data-tip="true" class="rota">
					<p><i class="lumisex-move-horizontal"></i></p>
					<span><?php echo $this->main->lang('Center Horizontal'); ?></span>
				</li>

				<li data-position="ml" data-tip="true">
					<p><i class="lumisex-android-arrow-back"></i></p>
					<span><?php echo $this->main->lang('Middle left'); ?></span>
				</li>
				<li data-position="mc" data-tip="true">
					<p><i class="lumisex-android-radio-button-off"></i></p>
					<span><?php echo $this->main->lang('Middle center'); ?></span>
				</li>
				<li data-position="mr" data-tip="true">
					<p><i class="lumisex-android-arrow-forward"></i></p>
					<span><?php echo $this->main->lang('Middle right'); ?></span>
				</li>

				<li data-position="" data-tip="true">
					<i class="lumise-icon-info"></i>
					<span>
						<?php echo $this->main->lang('Press &leftarrow; &uparrow; &rightarrow; &downarrow; to move 1 px, <br>Hit simultaneously SHIFT key to move 10px'); ?>
					</span>
				</li>
				<li data-position="bl" data-tip="true" class="mirX">
					<p><i class="lumisex-android-arrow-down _45deg"></i></p>
					<span><?php echo $this->main->lang('Bottom left'); ?></span>
				</li>
				<li data-position="bc" data-tip="true">
					<p><i class="lumisex-android-arrow-down"></i></p>
					<span><?php echo $this->main->lang('Bottom center'); ?></span>
				</li>
				<li data-position="br" data-tip="true">
					<p><i class="lumisex-android-arrow-down _45deg"></i></p>
					<span><?php echo $this->main->lang('Bottom right'); ?></span>
				</li>
			</ul>
		</li>
		<li data-tool="transform">
			<span data-tip="true">
				<i class="lumisex-android-options"></i>
				<span><?php echo $this->main->lang('Transforms'); ?></span>
			</span>
			<ul data-pos="right" data-view="sub">
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Rotate'); ?>: </span>
						<inp data-range="helper" data-value="0º">
							<input type="range" id="lumise-rotate" data-value="0º" min="0" max="360" value="0" data-unit="º" data-range="0, 45, 90, 135, 180, 225, 270, 315" data-action="angle" />
						</inp>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Skew X'); ?>: </span>
						<inp data-range="helper" data-value="0">
							<input class="nol" type="range" id="lumise-skew-x" data-value="0" min="-30" max="30" value="0" data-unit="" data-action="skewX" data-range="0" data-between="true" />
						</inp>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Skew Y'); ?>: </span>
						<inp data-range="helper" data-value="0">
							<input class="nol" type="range" id="lumise-skew-y" data-value="0" min="-30" max="30" value="0" data-unit="" data-action="skewY" data-range="0" data-between="true" />
						</inp>
					</h3>
				</li>
				<li class="center">
					<?php echo $this->main->lang('Flip X'); ?>:
					<div class="lumise-switch mr2">
						<input id="lumise-flip-x" type="checkbox" value="" class="lumise-toggle-button">
						<span class="lumise-toggle-label" data-on="ON" data-off="OFF"></span>
						<span class="lumise-toggle-handle"></span>
					</div>

					<?php echo $this->main->lang('Flip Y'); ?>:
					<div class="lumise-switch">
						<input id="lumise-flip-y" type="checkbox" value="" class="lumise-toggle-button">
						<span class="lumise-toggle-label" data-on="ON" data-off="OFF"></span>
						<span class="lumise-toggle-handle"></span>
					</div>
					<p class="blockinl">
						<i class="lumisex-android-bulb"></i>
						<?php echo $this->main->lang('Free transform by press SHIFT+&#10529;'); ?>
						<br>
						<button id="lumise-reset-transform">
							<i class="lumisex-arrows-ccw"></i>
							<?php echo $this->main->lang('Reset all transforms'); ?>
						</button>
					</p>
				</li>
			</ul>
		</li>
		<!-- Avalable hook: before-tool-standard-right -->
		<?php echo $this->main->do_action('before-tool-standard-right'); ?>
	</ul>

	<ul class="lumise-top-nav left" data-mode="text" id="lumise-text-tools">
		<!-- Avalable hook: before-tool-text -->
		<?php echo $this->main->do_action('before-tool-text'); ?>
		<li data-tool="font">
			<span data-tip="true">
				<button class="dropdown">
					<font style="font-family:Arial">Arial</font>
				</button>
				<span><?php echo $this->main->lang('Font family'); ?></span>
			</span>
			<ul data-pos="center" data-func="fonts" data-view="sub">
				<li class="scroll smooth" id="lumise-fonts"></li>
				<?php if ($this->main->connector->is_admin() || $this->main->cfg->settings['user_font'] !== '0') { ?>
				<li class="bttm">
					<button class="lumise-more-fonts">
						<i class="lumisex-android-open"></i> 
						<?php echo $this->main->lang('Get more fonts'); ?>
					</button>
				</li>
				<?php } ?>
			</ul>
		</li>
		<li data-tool="spacing">
			<span data-tip="true">
				<i class="lumisex-text f16"></i>
				<span><?php echo $this->main->lang('Edit text'); ?></span>
			</span>
			<ul data-pos="right" data-view="sub">
				<li data-view="title">
					<h3>
						<?php echo $this->main->lang('Edit text'); ?>
						<i class="lumisex-android-close close" title="Close"></i>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<textarea type="text" class="nol lumise-edit-text" placeholder="Enter your text"></textarea>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span><?php echo $this->main->lang('Font size'); ?>: </span>
						<inp data-range="helper" data-value="16">
							<input type="range" class="nol" id="lumise-font-size" data-action="fontSize" data-unit="px" data-value="16" min="6" max="144" value="16" />
						</inp>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span class="min100"><?php echo $this->main->lang('Letter spacing'); ?> </span>
						<inp data-range="helper" data-value="100%">
							<input type="range" class="nol" id="lumise-letter-spacing" data-value="100%" min="0" max="1000" value="100" data-unit="" data-action="charSpacing" />
						</inp>
					</h3>
				</li>
				<li>
					<h3 class="nob">
						<span class="min100"><?php echo $this->main->lang('Line height'); ?> </span>
						<inp data-range="helper" data-value="10">
							<input type="range" class="nol" id="lumise-line-height" data-value="10" min="1" max="50" value="10"  data-action="lineHeight" data-unit="px" data-ratio="0.1" />
						</inp>
					</h3>
				</li>
				<li><button data-func="update-text-fx"><?php echo $this->main->lang('UPDATE TEXT'); ?></button></li>
			</ul>
		</li>
		<li data-tool="text-effect">
			<span data-tip="true">
				<i class="lumisex-vector"></i>
				<span><?php echo $this->main->lang('Text Effects'); ?></span>
			</span>
			<ul data-pos="right" data-view="sub">
				<li data-view="title">
					<h3>
						<?php echo $this->main->lang('Text effects'); ?>
						<i class="lumisex-android-close close" title="Close"></i>
					</h3>
				</li>
				<li id="lumise-text-effect">
					<h3 class="nob mb1">
						<textarea type="text" class="nol ml0 lumise-edit-text" placeholder="<?php echo $this->main->lang('Enter your text'); ?>"></textarea>
						<button data-func="update-text-fx"><?php echo $this->main->lang('UPDATE TEXT'); ?></button>
					</h3>
					<span data-sef="images">
						<img data-effect="normal" src="<?php echo $this->main->cfg->assets_url; ?>assets/images/text-effect-normal.png" height="80" data-selected="true" />
						<img data-effect="curved" src="<?php echo $this->main->cfg->assets_url; ?>assets/images/text-effect-curved.png" height="80" />
						<img data-effect="bridge" src="<?php echo $this->main->cfg->assets_url; ?>assets/images/text-effect-bridge.png" height="80" />
						<img data-effect="oblique" src="<?php echo $this->main->cfg->assets_url; ?>assets/images/text-effect-oblique.png" height="80" />
					</span>
					<div class="lumise-switch" style="display: none;">
						<input id="lumise-curved" type="checkbox" value="" class="lumise-toggle-button">
						<span class="lumise-toggle-label" data-on="ON" data-off="OFF"></span>
						<span class="lumise-toggle-handle"></span>
					</div>
				</li>
				<li data-func="curved">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Radius'); ?> </span>
						<inp data-range="helper" data-value="80">
							<input type="range" class="nol" id="lumise-curved-radius" data-action="radius" data-value="80" min="-300" max="300" value="80" />
						</inp>
					</h3>
				</li>
				<li data-func="curved">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Spacing'); ?> </span>
						<inp data-range="helper" data-value="0">
							<input type="range" class="nol" id="lumise-curved-spacing" data-action="spacing" data-value="0" min="0" max="100" value="0" />
						</inp>
					</h3>
				</li>
				<li data-func="text-fx">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Curve'); ?> </span>
						<inp data-range="helper" data-value="0">
							<input type="range" class="nol" id="lumise-text-fx-curve" data-callback="textFX" data-fx="curve" data-value="0" min="-100" max="100" data-ratio="0.1" value="0" />
						</inp>
					</h3>
				</li>
				<li data-func="text-fx">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Height'); ?> </span>
						<inp data-range="helper" data-value="100">
							<input type="range" class="nol" id="lumise-text-fx-bottom" data-callback="textFX" data-fx="bottom" data-value="100" min="1" max="150" data-ratio="0.1" value="100" />
						</inp>
					</h3>
				</li>
				<li data-func="text-fx">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Offset'); ?> </span>
						<inp data-range="helper" data-value="50">
							<input type="range" class="nol" id="lumise-text-fx-offsety" data-callback="textFX" data-fx="offsetY" data-value="50" min="1" max="100" data-ratio="0.01" value="50" />
						</inp>
					</h3>
				</li>
				<li data-func="text-fx">
					<h3 class="nob">
						<span><?php echo $this->main->lang('Trident'); ?> </span>
						<div class="lumise-switch">
							<input id="lumise-text-fx-trident" data-fx="trident" type="checkbox" value="" class="lumise-toggle-button">
							<span class="lumise-toggle-label" data-on="ON" data-off="OFF"></span>
							<span class="lumise-toggle-handle"></span>
						</div>
					</h3>
				</li>
			</ul>
		</li>
		<li class="sp"></li>
		<li data-tool="text-align">
			<span data-tip="true">
				<i class="lumisex-align-center" id="lumise-text-align"></i>
				<span><?php echo $this->main->lang('Text align'); ?></span>
			</span>
			<ul data-pos="center" data-view="sub">
				<li>
					<i class="lumisex-align-left text-format" data-align="left" title="<?php echo $this->main->lang('Text align left'); ?>"></i>
					<i class="lumisex-align-center text-format" data-align="center" title="<?php echo $this->main->lang('Text align center'); ?>"></i>
					<i class="lumisex-align-right text-format" data-align="right" title="<?php echo $this->main->lang('Text align right'); ?>"></i>
					<i class="lumisex-align-justify text-format" data-align="justify" title="<?php echo $this->main->lang('Text align justify'); ?>"></i>
				</li>
			</ul>
		</li>
		<li class="text-format" data-format="upper">
			<span data-tip="true">
				<i class="lumisex-letter"></i>
				<span><?php echo $this->main->lang('Uppercase / Lowercase'); ?></span>
			</span>
		</li>
		<li class="text-format" data-format="bold">
			<span data-tip="true">
				<i class="lumisex-bold"></i>
				<span><?php echo $this->main->lang('Font weight bold'); ?></span>
			</span>
		</li>
		<li class="text-format" data-format="italic">
			<span data-tip="true">
				<i class="lumisex-italic"></i>
				<span><?php echo $this->main->lang('Text style italic'); ?></span>
			</span>
		</li>
		<li class="text-format" data-format="underline">
			<span data-tip="true">
				<i class="lumisex-underline"></i>
				<span><?php echo $this->main->lang('Text underline'); ?></span>
			</span>
		</li>
		<!-- Avalable hook: after-tool-text -->
		<?php echo $this->main->do_action('after-tool-text'); ?>
	</ul>

	<?php

	}


	public function left() {
		
		$comps = $this->main->cfg->settings['components'];
		
		if (is_string($this->main->cfg->settings['components']))
			$comps = explode(',', $this->main->cfg->settings['components']);
		
		$menus = $this->main->cfg->editor_menus;
		
	?>
	<div id="lumise-left">
		<div class="lumise-left-nav-wrp">
			<ul class="lumise-left-nav">
				<li data-tab="design">
					<i class="lumisex-android-color-palette"></i>
					<?php echo $this->main->lang('Design'); ?>
				</li>
				<?php 
					
					for ($i = 0; $i < count($comps); $i++) {
						
						if (isset($menus[$comps[$i]])) {
							
							$attrs = array('data-tab="'.$comps[$i].'"');
							
							if (isset($menus[$comps[$i]]['load']) && !empty($menus[$comps[$i]]['load']))
								array_push($attrs, 'data-load="'.$menus[$comps[$i]]['load'].'"');
							
							if (isset($menus[$comps[$i]]['callback']) && !empty($menus[$comps[$i]]['callback']))
								array_push($attrs, 'data-callback="'.$menus[$comps[$i]]['callback'].'"');
								
							echo '<li '.implode(' ', $attrs).'>';
							
							if (
								isset($menus[$comps[$i]]['icon']) && 
								!empty($menus[$comps[$i]]['icon'])
							) echo '<i class="'.$menus[$comps[$i]]['icon'].'"></i>';
							
							echo (isset($menus[$comps[$i]]['label']) ? $menus[$comps[$i]]['label'] : '');
							
							echo '</li>';
							
						}
						
					}
				
				?>
				
				<?php if ($this->main->cfg->settings['report_bugs'] != 0) { ?>
				<li data-tab="bug" title="<?php echo $this->main->lang('Report bugs'); ?>">
					<i class="lumisex-bug"></i>
				</li>
				<?php } ?>
			</ul>
			<i class="lumisex-android-close active" id="lumise-side-close"></i>
		</div>
		
		<?php 
			
			$first = true;
					
			for ($i = 0; $i < count($comps); $i++) {
				
				if (isset($menus[$comps[$i]])) {
					
					$claz = 'lumise-tab-body-wrp';
						
					if (isset($menus[$comps[$i]]['class']) && !empty($menus[$comps[$i]]['class']))
						$claz .= ' '.$menus[$comps[$i]]['class'];
						
					echo '<div id="lumise-'.$comps[$i].'" class="'.$claz.'">';
					if (isset($menus[$comps[$i]]['content']) && !empty($menus[$comps[$i]]['content']))
						echo $menus[$comps[$i]]['content'];
					echo '</div>';
					
				}
				
			}
		
		?>
		
		<?php if ($this->main->cfg->settings['report_bugs'] != 0) { ?>
		<div id="lumise-bug" class="lumise-tab-body-wrp lumise-left-form">
			<bug>
				<h3><?php echo $this->main->lang('Bug Reporting'); ?></h3>
				<p><?php echo $this->main->lang('Please let us know if you find any bugs on this design tool or just your opinion to improve the tool.'); ?></p>
				<textarea placeholder="<?php echo $this->main->lang('Bug description (min 30 - max 1500 characters)'); ?>" maxlength="1500" data-id="report-content"></textarea>
				<button class="lumise-btn submit">
					<?php echo $this->main->lang('Send now'); ?> <i class="lumisex-android-send"></i>
				</button>
				<p data-view="tips">
					<?php echo $this->main->lang('Tips: If you want to send content with screenshots or videos, you can upload them to'); ?> 
					<a href="https://imgur.com" target=_blank>imgur.com</a> 
					<?php echo $this->main->lang('or any drive services and put links here.'); ?>
				</p>
				<center><i class="lumisex-bug"></i></center>
			</bug>
		</div>
		<?php } ?>
		<div id="lumise-x-thumbn-preview">
			<header></header>
			<div></div>
			<footer></footer>
		</div>
	</div>
	<?php
	}

    public function printings()
    {
        ?>
        <div class="lumise-prints lumise-cart-field">
            <div class="lumise-add-cart-heading">
                <?php echo $this->main->lang('Print Technologies'); ?>
            </div>
			<div class="lumise_radios lumise_form_content">
	            <?php
	            $rules = array();
	            $items = $this->get_prints();
	            $default = $i = 0;
	            if(count($items) >0){

	                foreach($items as $print):
	                    if($i == 0)
	                        $default = $print['id'];
	                    $rules[$print['id']] = array(
	                        'calc' => json_decode($print['calculate']),
	                        'type' => $print['type']
	                    );

	                ?>
	            <div class="lumise-radio">
	                <input type="radio" name="printing" value="<?php echo $print['id'];?>" id="lumise-print-<?php echo $print['id'];?>"/>
	                <label for="lumise-print-<?php echo $print['id'];?>">
	                <?php echo $print['title'];?>
	                <div class="check"></div>
	                </label>
	                <em class="lumise-printing-desc">
	                    <?php echo $print['description'];?>
	                </em>
	            </div>

	                <?php
	                $i++;
	                endforeach;

	            }
	            else {
					echo $this->main->lang('This product do not have printing options.');
	            }
	            ?>
			</div>
        </div>
    <?php

    }

	public function detail_header($args = array()) {

		global $lumise, $lumise_router, $lumise_helper;

		echo '<div class="lumise_header">';
		
		if (!isset($args['pages']))
			$args['pages'] = $args['page'].'s';
			
		if (!empty($_GET['id'])) {
			echo '<h2><a href="'.$lumise->cfg->admin_url.'lumise-page='.$args['pages'].(isset($args['type']) ? '&type='.$args['type'] : '').'">'.$args['pages'].'</a> <i class="fa fa-angle-right"></i> '.$args['edit'].'</h2>'.
					'<a href="'.$lumise->cfg->admin_url.'lumise-page='.$args['page'].(isset($args['type']) ? '&type='.$args['type'] : '').(isset($_GET['callback']) ? '&callback=edit-cms-product' : '').'" class="add-new lumise-button"><i class="fa fa-plus"></i> '.
					$args['add'].
					'</a>';
		} else {
			echo '<h2><a href="'.$lumise->cfg->admin_url.'lumise-page='.$args['pages'].(isset($args['type']) ? '&type='.$args['type'] : '').'">'.$args['pages'].'</a> <i class="fa fa-angle-right"></i> '.$args['add'].'</h2>';
		}

		echo $lumise_helper->breadcrumb(isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '');

		echo '</div>';

		$this->header_message();

	}

	public function header_message(){

		$lumise_msg = $this->main->connector->get_session('lumise_msg');

		if (isset($lumise_msg['status']) && $lumise_msg['status'] == 'error' && is_array($lumise_msg['errors'])) { ?>

			<div class="lumise_message err">

				<?php 
					
					foreach ($lumise_msg['errors'] as $val) {
						if (!empty($val)) {
							echo '<em class="lumise_err"><i class="fa fa-times"></i>  ' . $val . '</em>';
						}
					}
					
					$lumise_msg = array('status' => '');
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
					
				?>

			</div>

		<?php }
		
		if (isset($lumise_msg['status']) && $lumise_msg['status'] == 'warn' && is_array($lumise_msg['errors'])) {
			?>
			<div class="lumise_message warn">

				<?php
				echo '<em class="lumise_msg">'.(isset($lumise_msg['msg'])? $lumise_msg['msg'] : $this->main->lang('Your data has been successfully saved')).'</em>';
				
				if( isset($lumise_msg['errors']) ) {
					foreach ($lumise_msg['errors'] as $val) {
						echo '<em class="lumise_err"><i class="fa fa-times"></i>  ' . $val . '</em>';
						$lumise_msg = array('status' => '');
						$this->main->connector->set_session('lumise_msg', $lumise_msg);
					}
				}
				$lumise_msg = array('status' => '');
				?>
			</div>

		<?php }

		if (isset($lumise_msg['status']) && $lumise_msg['status'] == 'success') {

		?>
			<div class="lumise_message">
				<?php
					echo '<em class="lumise_suc"><i class="fa fa-check"></i> '.(isset($lumise_msg['msg'])? $lumise_msg['msg'] : $this->main->lang('Your data has been successfully saved')).'</em>';
					$lumise_msg = array('status' => '');
					$this->main->connector->set_session('lumise_msg', $lumise_msg);
				?>
			</div>
		<?php

		}
	}

    public function tabs_render($args, $tabs_id = '') {
	    
		global $lumise;
	    
	    if (isset($args['tabs'])) {

		    echo '<div class="lumise_tabs_wrapper lumise_form_settings" data-id="'.$tabs_id.'">';
		    echo '<ul class="lumise_tab_nav">';
		    
		    foreach (array_keys($args['tabs']) as $label) {
				$str_att = explode(':', $label);
				$label = (count($str_att) > 1)? $str_att[1]: $label;
				echo '<li>';
				echo '<a href="#lumise-tab-'.((count($str_att) > 1)? $str_att[0]: $this->slugify($label)).'">'.$label.'</a>';
				echo '</li>';
			}
			echo '</ul>';
			echo '<div class="lumise_tabs">';

		    foreach ($args['tabs'] as $label => $fields) {
				$str_att = explode(':', $label);
				$label = (count($str_att) > 1)? $str_att[1]: $label;				
			    echo '<div class="lumise_tab_content" id="lumise-tab-'.((count($str_att) > 1)? $str_att[0]: $this->slugify($label)).'">';

			    $this->fields_render($fields);
			    echo '</div>';
		    }

		    echo '</div>';
		    echo '</div>';

	    }else $this->fields_render($args);

	    if (isset($_GET['id'])) {
	    	echo '<input name="id" value="'.$_GET['id'].'" type="hidden" />';
	    }
		echo '<input type="hidden" name="' . $lumise->cfg->security_name . '" value="'.$lumise->cfg->security_code.'">';
	    echo '<input name="save_data" value="true" type="hidden" />';

    }

    public function fields_render($fields) {
	    if (is_array($fields)) {
		    foreach ($fields as $field) {
		    	if (isset($field['type'])) {
		    		$this->field_render($field);
		    	}
		    }
	    }
    }

    public function field_render ($args = array()) {
	    
	    global $lumise;
	    
	   if ($args['type'] !== 'tabs' && isset($args['value']) && is_string($args['value']))
	    	$args['value'] = htmlentities(stripslashes($args['value']));
	   
	   if (isset($args['type_input']) && $args['type_input'] == 'hidden') {
			if (method_exists($this, 'field_'.$args['type']))
				$this->{'field_'.$args['type']}($args);
		} else {
			
			if (isset($args['label']) && !empty($args['label'])) { ?>
				<div class="lumise_form_group lumise_field_<?php echo $args['type']; ?>">
					<span><?php
						echo (isset($args['label']) ? $args['label']: '');
						echo (isset($args['required']) && $args['required'] === true ? '<em class="required">*</em>' : '');
					?></span>
					<div class="lumise_form_content">
						<?php
	
							$this->field_render_content($args);
	
							if (isset($args['desc']) && !empty($args['desc']))
								echo '<em class="notice">'.$args['desc'].'</em>';
						?>
					</div>
				</div>
			<?php
			}else{ 
			
				$this->field_render_content($args);
				
			}
		} ?>
	<?php
    }

	public function field_render_content ($args) {
		
		global $lumise;
		
		$lumise->do_action('before_field', $args);
		
		if (method_exists($this, 'field_'.$args['type'])) {
			ob_start();
			$this->{'field_'.$args['type']}($args);
			$content = ob_get_contents();
			ob_end_clean();
		} else $content = 'Field not exist: '.$args['type'];
		
		echo $lumise->apply_filters('render_field_'.$args['type'], $content, $args);
		
		$lumise->do_action('after_field', $args);
		
	}

    public function field_input ($args) {
	?><input <?php
		$value = ((isset($args['value']) && !empty($args['value'])) ? $args['value']: (isset($args['default']) ? $args['default']: ''));
		if(
			isset($args['numberic'])
		){
			switch ($args['numberic']) {
				case 'int':
					$value = intval($value);
					break;
				
				case 'float':
					$value = floatval($value);
					break;
			}
		}
		if (isset($args['readonly']) && $args['readonly'] === true)
			echo 'readonly="true"';
	?> type="<?php
				echo (isset($args['type_input']) ? $args['type_input']: 'text');
			?>" name="<?php
				echo (isset($args['name']) ? $args['name']: '');
			?>" placeholder="<?php
				echo (isset($args['placeholder']) ? $args['placeholder']: '');
			?>" value="<?php
				echo $value;
			?>" <?php
				echo (isset($args['type_input']) && $args['type_input'] == 'password' ? 'autocomplete="new-password"' : '');
			?> />
	<?php
    }
    
    public function field_trace ($args) {
		
		if (is_callable($args['content']))
			call_user_func($args['content'], $args);
		else if(is_string($args['content']))
			echo $args['content'];
		
    }
    
    public function field_admin_login ($args) {
	?>
		<div class="lumise_form_group lumise_field_input">
			<span><?php echo $this->main->lang('Admin email'); ?></span>
			<div class="lumise_form_content">
				<input type="text" name="admin_email" value="<?php echo $this->main->cfg->settings['admin_email']; ?>">
				<em class="notice"><?php echo $this->main->lang('Admin email to login and receive important emails'); ?></em>
			</div>
		</div>
		<div class="lumise_form_group lumise_field_input">
			<span><?php echo $this->main->lang('Admin password'); ?></span>
			<div class="lumise_form_content">
				<input type="password" placeholder="<?php echo $this->main->lang('Enter new password'); ?>" name="admin_password" value="" autocomplete="new-password"/>
			</div>
		</div>
		<div class="lumise_form_group lumise_field_input">
			<span> &nbsp; </span>
			<div class="lumise_form_content">
				<input type="password" placeholder="<?php echo $this->main->lang('Re-Enter new password'); ?>" name="re_admin_password" value="" autocomplete="new-password"/>
			</div>
		</div>
	<?php
    }

    public function field_text ($args) {
	?>
		<textarea name="<?php
			echo (isset($args['name']) ? $args['name']: '');
		?>"><?php
			echo (isset($args['value']) ? $args['value']: (isset($args['default']) ? $args['default']: ''));
		?></textarea>
	<?php
    }

    public function field_toggle ($args) {
	?>
		<div class="lumise-toggle">
			<input type="checkbox" name="<?php echo $args['name']; ?>" <?php
				if (
					$args['value'] === 1 || 
					$args['value'] == '1' || 
					(
						!isset($args['value']) || (isset($args['value']) && !is_numeric($args['value'])) && $args['default'] == 'yes')
					)
					echo 'checked="true"';
			?> value="1" />
			<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
			<span class="lumise-toggle-handle"></span>
		</div>
	<?php
    }

    public function field_parent ($args) {

    	global $lumise_admin, $lumise_router;
    	if (isset($args['id'])){
			$data = $lumise_admin->get_row_id($args['id'], 'categories');
    	}
		$cates = $lumise_admin->get_categories($args['cate_type']);

   	?>
    	<select name="parent">
			<option value="0"><?php echo $this->main->lang('None'); ?></option>
			<?php

				if ($args['id']) {
					$arr_temp = array($data['id']);
					foreach ($cates as $value) {

						$select = '';
						if (isset($data) && $value['id'] != $data['id']) {

							if ($value['id'] == $data['parent'])
								$select = 'selected';

							if (in_array($value['parent'], $arr_temp)) {
								$arr_temp[] = $value['id'];
							} else {
								if ($value['id'] != $data['id']) {
									echo '<option value="'.$value['id'].'"'.$select.'>'.str_repeat('&mdash;', $value['lv']).' '.$value['name'].'</option>';
								}
							}

						}

					}

				} else {

					foreach ($cates as $value) {
						$select = '';
						echo '<option value="'.$value['id'].'"'.$select.'>'.str_repeat('-', $value['lv']).' '.$value['name'].'</option>';
					}

				}

			?>
		</select>
	<?php
    }

	public function field_categories ($args) {

		global $lumise, $lumise_admin, $lumise_router;

		$cates = $lumise_admin->get_categories($args['cate_type']);

		if (count($cates) > 0) {
			
			if (!isset($args['id']))
				$args['id'] = 0;
				
			$dt = $lumise_admin->get_category_item($args['id'], $args['cate_type']);
			$dt_id = isset($args['value']) && is_array($args['value']) ? $args['value'] : array();

			foreach ($dt as $value) {
				$dt_id[] = $value['id'];
			}

			echo '<ul class="list-cate">';

			foreach ($cates as $value) {

				$pd = 20*$value['lv'].'px';
				$checked = '';

				if (isset($dt_id)) {
					if (in_array($value['id'], $dt_id)) {
						$checked = 'checked';
					}
				}
			?>
				<li style="padding-left: <?php echo $pd; ?>">
					<div class="lumise_checkbox sty2 <?php echo $checked; ?>">
							<input type="checkbox" name="<?php
								echo isset($args['name']) ? $args['name'].'[]' : '';
							?>" class="action_check" value="<?php
								echo $value['id'];
							?>" class="action" id="lumise-cate-<?php
								echo $value['id'];
							?>" <?php
								echo $checked;
							?> />
							<label for="lumise-cate-<?php echo $value['id']; ?>">
								<?php echo $value['name']; ?>
								<em class="check"></em>
							</label>
					</div>
				</li>
			<?php } ?>
			</ul>
			<input type="checkbox" name="<?php echo $args['name']; ?>[]" checked="true" style="display:none;" value="" />
			<?php if (!isset($args['create_new']) || $args['create_new'] !== false) { ?>
			<a href="<?php echo $lumise->cfg->admin_url; ?>lumise-page=category&type=<?php echo $args['cate_type']; ?>" target=_blank class="lumise_add_cate">
				<?php echo $this->main->lang('Add Category'); ?>
			</a>
			<?php } ?>
		<?php } else  { ?>
			<p class="no-data"><?php echo $this->main->lang('No categories have been created yet'); ?>. </p>
			<?php if (!isset($args['create_new']) || $args['create_new'] !== false) { ?>
			<a href="<?php echo $lumise->cfg->admin_url; ?>lumise-page=category&type=<?php echo $args['cate_type']; ?>" target=_blank  class="add-new">
				<?php echo $this->main->lang('Create new category'); ?>
			</a>
			<?php } ?>
		<?php }
    }

    public function field_tags($args = array()) {

	    global $lumise_admin, $lumise_router;

	?>
		<div class="list-tag">
			<?php

				$dt_name = array();
				if (isset($args['id'])) {

					$dt = $lumise_admin->get_tag_item($args['id'], $args['tag_type']);

					foreach ($dt as $value) {
						$dt_name[] = $value['name'];
					}

				}

			?>
			<input id="tags" type="text" name="<?php
				echo isset($args['name']) ? $args['name'] : '';
			?>" value="<?php echo implode(', ', $dt_name); ?>" />
		</div>
		<script type="text/javascript"><?php

			$tags = $lumise_admin->get_rows_custom(array ("id", "name", "slug", "type"),'tags');

			// Autocomplete Tag
			function js_str($s) {
			    return '"' . addcslashes($s, "\0..\37\"\\") . '"';
			}

			function js_array($array) {
			    $temp = array_map('js_str', $array);
			    return '[' . implode(',', $temp) . ']';
			}

			if (isset($tags) && count($tags) > 0) {
				$values = array();
				foreach ($tags as $value) {

					if ($value['type'] == $args['tag_type'])
						$values[] = $value['name'];

				}
				echo 'var lumise_tag_values = ', js_array($values), ';';
			}
		?></script>
	<?php
    }

    public function field_radios($args = array()) {
	    if ($args['options']) {
		    echo '<div class="lumise_radios">';
		    foreach ($args['options'] as $option => $value) {
				if (empty($args['value']) && empty($option))
					unset($args['default']);
			}
		    foreach ($args['options'] as $option => $value) {
			?>
			<div class="radio">
				<input type="radio" name="<?php
					echo isset($args['name']) ? $args['name'] : ''
				?>" id="lumise-radios-<?php echo (isset($args['name']) ? $args['name'] : '').'-'.$option; ?>" <?php
					if ((empty($args['value']) && isset($args['default']) && $args['default'] == $option) || (isset($args['value']) && $args['value'] == $option))
						echo 'checked="true"';
				?> value="<?php echo $option; ?>">
				<label for="lumise-radios-<?php echo (isset($args['name']) ? $args['name'] : '').'-'.$option; ?>">
					<?php echo $value; ?> <em class="check"></em>
				</label>
			</div>
			<?php
			}
			echo '</div>';
		}else echo 'missing options';
    }


    public function field_checkboxes($args = array()) {

	    if (isset($args['value'])) {
	    	if (is_string($args['value']))
	    		$args['value'] = explode(',', $args['value']);
		}else
			$args['value'] = array();

	    if (isset($args['options'])) {
		    echo '<div class="lumise_checkboxes">';
		    $options = array_replace(array_flip($args['value']), $args['options']);
		    foreach ($options as $option => $value) {
			    if (isset($args['options'][$option])) {
			?>
				<div class="lumise_checkbox sty2 ">
					<input type="checkbox" name="<?php
						echo isset($args['name']) ? $args['name'].'[]' : ''
					?>" class="action_check" value="<?php echo $option; ?>" <?php
						if (in_array($option, $args['value']) || (!isset($args['value']) && $args['default'] == $option))
							echo 'checked="true"';
					?> id="lumise-checkboxes-<?php echo $option; ?>" />
						<label for="lumise-checkboxes-<?php echo $option; ?>">
							<?php echo $value; ?> <em class="check"></em>
						</label>
				</div>
			<?php }} ?>
				<input type="checkbox" name="<?php echo $args['name']; ?>[]" checked="true" style="display:none;" value="" />
			</div>
		<?php }else echo 'missing options';
    }


    public function field_dropbox($args = array()) {
	    if (isset($args['options'])) {
		    echo '<select name="'.(isset($args['name']) ? $args['name'] : '').'" class="'.(isset($args['class']) ? $args['class'] : '').'">';
		    foreach ($args['options'] as $option => $value) {
			    echo '<option'.(((!isset($args['value']) && $args['default'] == $option) || (isset($args['value']) && $args['value'] == $option)) ? ' selected="true"' : '').' value="'.$option.'">'.$value.'</option>';
			}
			echo '</select>';
		}else echo 'missing options';
    }

    public function field_printing($args = array()) {

		$prints = $this->main->views->get_prints();
		
		$inp_val = json_decode(rawurldecode($args['value']), true);
		
		if (count($prints) > 0) {
			
			echo '<div class="lumise_checkboxes">';	
			
			if (isset($inp_val) && !empty($inp_val) && $inp_val !== null) {
				$keys = array_flip(array_keys($inp_val));
				for ($i = count($prints)-1; $i >= 0; $i--) {
					if (isset($keys['_'.$prints[$i]['id']])) {
						array_splice($prints, $keys['_'.$prints[$i]['id']], 0, array_splice($prints, $i, 1));
					}
				}
			}
			foreach ($prints as $print) {
				$calc = $this->main->lib->dejson($print['calculate']);
		?>
			<div class="lumise_checkbox sty2 ui-sortable-handle" data-type="<?php echo $calc->type; ?>">
				<input type="checkbox" name="helper-<?php 
					echo $args['name']; 
				?>[]" class="action_check" value="<?php echo $print['id']; ?>" <?php
					echo (
						is_array($inp_val) && (
							isset($inp_val[$print['id']]) || 
							isset($inp_val['_'.$print['id']]))
						) ? ' checked' : '';
				?> id="lumise-checkboxes-<?php echo $args['name'] . '-'. $print['id']; ?>">
				<label for="lumise-checkboxes-<?php echo $args['name'] . '-'. $print['id']; ?>">
					<?php echo $print['title']; ?> <em class="check"></em>
				</label>
				<?php
					if (is_object($calc) && isset($calc->type) && $calc->type == 'size') {
					
						$first_obj = array_values((Array)$calc->values);
						
						if (count($first_obj) > 0) {
							echo '<div class="lumise_radios field_children display_inline" data-parent="'.$print['id'].'">';
							$sizes = (Array)$first_obj[0];
							$sizes = array_values($sizes);
							$sizes = array_shift($sizes);
							foreach ($sizes as $key => $val) {
								echo '<div class="radio">
										<input type="radio"'.(
												is_array($inp_val) && 
												((
													isset($inp_val[$print['id']]) &&
													$inp_val[$print['id']] == $key
												) ||
												(
													isset($inp_val['_'.$print['id']]) &&
													$inp_val['_'.$print['id']] == $key
												)
												) ? ' checked' : ''
											).' 
											name="print-sizes-'.$args['name'].'-'.$print['id'].'" 
											id="print-size-'.$print['id'].'-'.$args['name'].'-'.$key.'" 
											value="'.$key.'"
										 /> 
										<label for="print-size-'.$print['id'].'-'.$args['name'].'-'.$key.'">'.
										strtoupper(urldecode($key)).'
										<em class="check"></em></label>
									</div>';
							}
							echo '</div>';
						}
					}
				?>
			</div>
			<?php
			}
			
			echo '</div>';
			
		} else {
			echo '<p>'.
				$this->main->lang('You have not created any prints type yet').
			'</p><input type="hidden" name="'.$args['name'].'[]" />';
		}
		
		echo '<input type="hidden" class="field-value" name="'.$args['name'].'" value="'.$args['value'].'" />';

    }

	public function field_color($args) {
	?>
	<div class="lumise-field-color-wrp">
		<ul class="lumise-field-color<?php echo (isset($args['selection']) && $args['selection'] === false) ? ' unselection' : ''; ?>">
		<?php

			if (!isset($args['value']) || empty($args['value'])) {
				if (isset($args['default']))
					$args['value'] = $args['default'];
				else $args['value'] = '#3fc7ba:#546e7a,#757575,#6d4c41,#f4511e,#fb8c00,#ffb300,#fdd835,#c0cA33,#a0ce4e,#7cb342,#43a047,#00897b,#00acc1,#3fc7ba,#039be5,#3949ab,#5e35b1,#8e24aa,#d81b60,#eeeeee,#3a3a3a';
			}

			$colors = explode(':', $args['value']);
			$value = $colors[0];
			$colors = explode(',', isset($colors[1]) ? $colors[1] : '');

			foreach ($colors as $color) {
				
				$color = explode('@', $color);
				$label = isset($color[1]) ? $color[1] : $color[0];
				
				echo '<li data-label="'.(!empty($label) ? $label : $color[0]).'" data-color="'.strtolower($color[0]).
					'" title="'.(!empty($label) ? str_replace('"', '', urldecode($label)) : strtolower($color[0])).
					'" '.($color[0] == $value ? 'class="choosed"' : '').
					' style="background:'.$color[0].'">'.
					'<i class="fa fa-times" data-color="delete"></i>'.
					'</li>';
			}
		?>
		</ul>
		<input type="hidden" data-el="hide" value="<?php echo isset($args['value']) ? $args['value']: $args['default']; ?>" name="<?php
			echo isset($args['name']) ? $args['name'] : '';
		?>" />
		<button data-func="create-color">
			<?php echo $this->main->lang('Add new color'); ?>
		</button>
		<button data-btn data-func="clear-color">
			<?php echo $this->main->lang('Clear all'); ?>
		</button>
	</div>
	<?php
	}

	public function field_upload($attr = array()){

		if (isset($attr['file']) && $attr['file'] == 'font') {
			
		?>
			<h1 id="lumise-<?php echo $attr['name']; ?>-preview" contenteditable="true" style="display: none;"></h1>
			<div class="img-preview">
				<?php if (!empty($attr['value'])) { ?>
					<input type="hidden" name="old-<?php echo $attr['name']; ?>" value="<?php echo $attr['value'] ?>">
				<?php } ?>
				<input type="file" id="lumise-<?php echo $attr['name']; ?>-file-upload" accept=".<?php echo $attr['file_type']; ?>" data-file-select="font" data-file-preview="#lumise-<?php echo $attr['name']; ?>-preview" data-file-input="#lumise-<?php echo $attr['name']; ?>-input" />

				<input type="hidden" name="<?php
					echo $attr['name'];
				?>" id="lumise-<?php
					echo $attr['name'];
				?>-input" value="<?php
					echo !empty($attr['value']) ? $attr['value'] : '';
				?>" class="lumise-upload-helper-inp" data-file="<?php echo $attr['file']; ?>" />

				<label for="lumise-<?php echo $attr['name']; ?>-file-upload">
					<i class="fa fa-cloud-upload"></i> <?php echo $this->main->lang('Choose file'); ?> 
					(*.<?php echo $attr['file_type']; ?>)
				</label>
				<button data-btn="true" data-file-delete="true"  data-file-preview="#lumise-<?php
					echo $attr['name'];
				?>-preview" data-file-input="#lumise-<?php
					echo $attr['name'];
				?>-input" data-file-thumbn="#lumise-<?php
					echo $attr['name'];
				?>-thumbn">
					<i class="fa fa-trash"></i> <?php echo $this->main->lang('Remove file'); ?>
				</button>
			
			</div>
			<script type="text/javascript">

				<?php if (!empty($attr['value']) && !empty($attr['name'])) {
					echo 'jQuery(document).ready(function() {lumise_font_preview("'.$attr['name'].'", "url('.(!empty($attr['value']) ? $this->main->cfg->upload_url.str_replace(TS, '/', $attr['value']) : '').')", "#lumise-'.$attr['name'].'-preview", "'.$attr['file_type'].'");})';
				} ?>

			</script>
		<?php

		return;

		}
		?>
		
		
		<?php
			if (isset($attr['file']) && $attr['file'] == 'design') {
		?>
			<div class="img-preview">
				<?php if (!empty($attr['value'])) { ?>
					<img src="<?php
					echo isset($attr['thumbn_value']) ? $attr['thumbn_value'] : $this->main->cfg->upload_url.'/'.$attr['value'];
				?>" class="img-upload" id="lumise-<?php echo $attr['name']; ?>-preview" style="max-width:350px" />
					<input type="hidden" id="lumise-<?php echo $attr['name']; ?>-input-old" name="old-<?php echo $attr['name']; ?>" value="<?php echo $attr['value'] ?>">
				<?php }else{ ?>
					<img src="<?php echo $this->main->cfg->assets_url; ?>admin/assets/images/img-none.png" class="img-upload" id="lumise-<?php echo $attr['name']; ?>-preview" style="max-width:350px" />
				<?php } ?>
				<input type="file" id="lumise-<?php echo $attr['name']; ?>-file-upload" accept=".json,.lumi,.png,.jpg" data-file-select="design" data-file-preview="#lumise-<?php echo $attr['name']; ?>-preview" data-file-input="#lumise-<?php echo $attr['name']; ?>-input" />

				<input type="hidden" class="lumise-upload-helper-inp" accept=".json,.lumi" name="<?php
					echo $attr['name'];
				?>" id="lumise-<?php
					echo $attr['name'];
				?>-input" data-file-preview="#lumise-<?php
					echo $attr['name'];
				?>-preview" value="<?php
					echo !empty($attr['value']) ? $attr['value'] : '';
				?>" data-path="<?php echo !empty($attr['path']) ? $attr['path'] : ''; ?>" data-file="<?php echo $attr['file']; ?>" />
				
				<?php if (isset($attr['thumbn']) && isset($attr['thumbn_value'])) { ?>

					<input type="hidden" name="old-<?php echo $attr['thumbn']; ?>" value="<?php
						echo isset($attr['thumbn_value']) ? $attr['thumbn_value'] : '';
					?>" />
	
				<?php } ?>
			
				<label for="lumise-<?php echo $attr['name']; ?>-file-upload">
					<i class="fa fa-cloud-upload"></i> <?php echo $this->main->lang('Choose a file'); ?>
				</label>
				<button data-btn="true" data-file-delete="true"  data-file-preview="#lumise-<?php
					echo $attr['name'];
				?>-preview" data-file-input="#lumise-<?php
					echo $attr['name'];
				?>-input" data-file-thumbn="#lumise-<?php
					echo $attr['name'];
				?>-thumbn">
					<i class="fa fa-trash"></i> <?php echo $this->main->lang('Remove file'); ?>
				</button>
			</div>
		<?php

			return;

			}
		?>

		<div class="img-preview">
			<?php if (isset($attr['value']) && !empty($attr['value'])) { ?>

				<img src="<?php
					echo isset($attr['thumbn_value']) ?
						$attr['thumbn_value'] : 
						(
							(strpos($attr['value'], '://') === false) ? 
							$this->main->cfg->upload_url.$attr['value'] : 
							$attr['value']
						);
					
				?>" class="img-upload" id="lumise-<?php echo $attr['name']; ?>-preview" />

				<input type="hidden" id="lumise-<?php
				echo $attr['name'];
			?>-input-old" name="old-<?php echo $attr['name']; ?>" value="<?php
					echo !empty($attr['value']) ? $attr['value'] : '';
				?>" />

			<?php } else { ?>
				<img src="<?php echo $this->main->cfg->assets_url; ?>admin/assets/images/img-none.png" class="img-upload" id="lumise-<?php echo $attr['name']; ?>-preview">
			<?php } ?>

			<input type="file" accept="<?php
				echo isset($attr['accept']) ? $attr['accept'] : 'image/png,image/gif,image/jpeg,image/svg+xml';
			?>" class="lumise-file-upload" id="<?php
				echo $attr['name'];
			?>_file_upload" data-file-select="true" data-file-preview="#lumise-<?php
				echo $attr['name'];
			?>-preview" data-file-input="#lumise-<?php
				echo $attr['name'];
			?>-input" <?php
				if (!isset($attr['thumbn_width']) && !isset($attr['thumbn_height']))
					echo 'data-file-thumbn-width="320"';
				else if (isset($attr['thumbn_width']))
					echo 'data-file-thumbn-width="'.$attr['thumbn_width'].'"';
				else if (isset($attr['thumbn_height']))
					echo 'data-file-thumbn-height="'.$attr['thumbn_height'].'"';
			?> />


			<input type="hidden" name="<?php
				echo $attr['name'];
			?>" id="lumise-<?php
				echo $attr['name'];
			?>-input" value="<?php
				echo !empty($attr['value']) ? $attr['value'] : '';

			?>" class="lumise-upload-helper-inp" data-path="<?php echo !empty($attr['path']) ? $attr['path'] : ''; ?>" data-file="<?php echo isset($attr['file']) ? $attr['file'] : ''; ?>" />

			<?php if (isset($attr['thumbn']) && isset($attr['thumbn_value'])) { ?>

				<input type="hidden" name="old-<?php echo $attr['thumbn']; ?>" value="<?php
					echo isset($attr['thumbn_value']) ? $attr['thumbn_value'] : '';
				?>" />

			<?php } ?>

			<label for="<?php echo $attr['name']; ?>_file_upload">
				<?php echo isset($attr['button_text']) ? $attr['button_text'] : $this->main->lang('Choose a file'); ?>
			</label>
			
			<button data-btn="true" data-file-delete="true"  data-file-preview="#lumise-<?php
				echo $attr['name'];
			?>-preview" data-file-input="#lumise-<?php
				echo $attr['name'];
			?>-input" data-file-thumbn="#lumise-<?php
				echo $attr['name'];
			?>-thumbn">
				<?php echo $this->main->lang('Remove file'); ?>
			</button>
		</div>

	<?php
	}
	
	public function field_stages($args) {
		
		global $lumise;
		
		$data = $this->dejson($args['value']);
		
		if (isset($data->stages))
			$stages = $data->stages;
		else $stages = $this->dejson($args['value']);
		
		unset($stages->{'colors'});
		
	?>
	<div class="lumise_form_group nomargin">
		<h3><?php echo $lumise->lang('Configure designs'); ?></h3>
		<p><?php echo $lumise->lang('Upload your product images, configure stages, edit zones. You can create new stage, change stage\'s name and arrange stages.'); ?></p>
		<div class="lumise_tabs_wrapper lumise-stages-wrp" id="lumise-stages-wrp" data-id="stages">
			<div class="lumise_tab_nav_wrap">
				<i data-move="left" class="fa fa-chevron-left"></i>
				<div class="lumise_tab_nav_inner">
					<ul class="lumise_tab_nav">
						<?php
						if (count(array_keys((Array)$stages)) === 0) {
							$id = $lumise->generate_id();
							$stages = array();
							$stages[$id] = json_decode('{"edit_zone":{"height":270,"width":170,"left":-1,"top":12.5,"radius":"5"},"url":"products\/basic_tshirt_front.png","source":"raws","overlay":true,"product_width":400,"product_height":475,"template":{},"label":"Start stage"}');
						}		
						foreach ($stages as $key => $stage) {
							
							$label = isset($stage->label) ? rawurldecode($stage->label) : 'Untitled';
						?>
						<li>
							<a href="#lumise-stage-<?php echo $key; ?>" data-label="<?php echo rawurlencode($label); ?>">
								<?php
									if (isset($stage->thumbnail) && !empty($stage->thumbnail)) {
										echo '<span>';
										echo '<img src="'.$this->main->cfg->upload_url.$stage->thumbnail.'" data-url="'.$stage->thumbnail.'" data-func="upload-thumbn">';
										echo '<i data-func="delete-thumbn" class="fa fa-times"></i>';
										echo '</span>';
									}
								?>
								<!--i class="fa fa-image" data-edit="thumbnail" title="<?php echo $lumise->lang('Upload thumbnail'); ?>"></i-->
								<text><?php echo str_replace(array('<', '>'), array('&lt;', '&gt;'), $label); ?></text>
								<svg data-func="remove" height="16px" width="16px" viewBox="-75 -75 370 370">
									<path data-func="remove"  d="M131.804,106.491l75.936-75.936c6.99-6.99,6.99-18.323,0-25.312   c-6.99-6.99-18.322-6.99-25.312,0l-75.937,75.937L30.554,5.242c-6.99-6.99-18.322-6.99-25.312,0c-6.989,6.99-6.989,18.323,0,25.312   l75.937,75.936L5.242,182.427c-6.989,6.99-6.989,18.323,0,25.312c6.99,6.99,18.322,6.99,25.312,0l75.937-75.937l75.937,75.937   c6.989,6.99,18.322,6.99,25.312,0c6.99-6.99,6.99-18.322,0-25.312L131.804,106.491z"></path>
								</svg>
							</a>
						</li>
						<?php } ?>
						<li data-add="tab">
							<a href="#add-stage" title="<?php echo $lumise->lang('Add new stage'); ?>">
								<i data-func="add-stage" class="fa fa-plus"></i>
							</a>
						</li>
					</ul>
				</div>
				<i data-move="right" class="fa fa-chevron-right"></i>
			</div>
			<div class="lumise_tabs">
			<?php
				
				$source = '';
				$overlay = '';
				$i = 0;
				
				foreach ($stages as $stage => $sdata) {
	
					if ($stage != 'colors') {
	
						if (isset($sdata->url)) {
							$url = $sdata->url;
							$source = $sdata->source;
						}else if ($i++ == 0){
							$url = 'products/basic_tshirt_front.png';
							$source = 'raws';
						}else $url = '';
						
						$overlay = isset($sdata->overlay) ? $sdata->overlay : true;
						
						if (isset($sdata->edit_zone) && isset($sdata->url)) {
							$limit = ' style="height: '.$sdata->edit_zone->height.'px;';
							$limit .= 'width: '.$sdata->edit_zone->width.'px;';
							$limit .= 'left: '.($sdata->edit_zone->left+($sdata->product_width/2)-($sdata->edit_zone->width/2)).'px;';
							$limit .= 'top: '.($sdata->edit_zone->top+($sdata->product_height/2)-($sdata->edit_zone->height/2)).'px;';
							if(isset($sdata->hide_edz) && $sdata->hide_edz == true){
								$limit .= 'display: none;';
							}
							if (isset($sdata->edit_zone->radius) && !empty($sdata->edit_zone->radius))
								$limit .= 'border-radius: '.$sdata->edit_zone->radius.'%;';
							$limit .= '"';
						}else $limit = '';
						
						if (isset($sdata->template) && isset($sdata->template->id)) {
							
							$design = $lumise->lib->get_template($sdata->template->id);
							if (
								$this->main->connector->platform == 'php' &&
								(!is_array($design) || !isset($design['id']))
							)
								$design = null;
								
						}else $design = null;
					
				?>
					<div class="lumise_tab_content<?php
						if ($i++ === 0)echo " active";
					?>" id="lumise-stage-<?php echo $stage; ?>" data-stage="<?php echo $stage; ?>">
						<div class="lumise-stage-settings lumise-product-design<?php
							echo (!empty($url) ? ' stage-enabled' : ' stage-disabled'); ?>" id="lumise-product-design-<?php echo $stage; ?>">
							<?php
								$is_mask = 'false';
								if ($overlay == '1')
									$is_mask = 'true';
							?>
							<div class="lumise-stage-body" data-is-mask="<?php echo $is_mask; ?>">
								<div class="lumise_form_content" style="<?php if(isset($sdata->hide_mark_layer) && $sdata->hide_mark_layer == true){ echo 'display:none;'; } ?>">
									<div class="lumise-toggle">
										<input type="checkbox" name="is_mask" <?php
											echo ($is_mask == 'true' ? 'checked="true"' : '');
										?> />
										<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
										<span class="lumise-toggle-handle"></span>
									</div>
									<label data-view="mask-true">
										<?php echo $this->main->lang('Use as a mask layer'); ?>.
										<a href="https://docs.lumise.com/product-mask-image/" target=_blank class="tip">
											<?php echo $this->main->lang('What is this'); ?> 
											<i class="fa fa-question-circle"></i>
											<span>
												<?php echo $this->main->lang('Display the product image as a mask layer to be able to change the color, Click for more detail.'); ?>
											</span>
										</a>
									</label>
								</div>
								<?php if (isset($_GET['action']) && $_GET['action'] == 'product_variation') { ?>
								<div class="lumise_form_content fill-base-color">
									<input type="text" value="<?php echo (isset($sdata->color) && $sdata->color !== '' ? $sdata->color : ''); ?>" placeholder="Fill color for product image" /> 
									<input type="color" value="<?php echo (isset($sdata->color) && $sdata->color !== '' ? $sdata->color : ''); ?>" />
								</div>
								<?php } ?>
								<div class="lumise-stage-design-view" <?php
									if (isset($sdata->edit_zone)) {
										echo ' data-info="scale ratio: '.$sdata->edit_zone->width.'x'.$sdata->edit_zone->height.'"';
									}
									if (isset($sdata->color) && $sdata->color !== '') {
										echo ' style="background:'.$sdata->color.';"';
									}
								?>>
									<img src="<?php 
										if (!empty($url)) {
											echo (
												$source == 'raws' ? 
												$this->main->cfg->assets_url.'raws/' : 
												$this->main->cfg->upload_url
											).$url;
										}
									?>" data-url="<?php echo $url; ?>" data-source="<?php echo $source; ?>" class="lumise-stage-image" data-svg="<?php echo (strpos($url, '.svg') !== false); ?>" />
									<div class="lumise-stage-editzone"<?php echo $limit; ?>>
										<?php if ($this->main->connector->platform == 'php') { ?>
											<div class="editzone-funcs">
												<button data-func="select-design" data-label="<?php echo $this->main->lang('Select Design Template'); ?>">
													<i class="fa fa-plus"></i>
												</button>
												<button data-func="clear-design" <?php if (!isset($design) || $design === null)echo 'style="display:none;"'; ?> data-label="<?php echo $this->main->lang('Clear Design Template'); ?>">
													<i class="fa fa-eraser"></i>
												</button>
												<button data-func="move" data-label="<?php echo $this->main->lang('Drag to move the edit zone'); ?>">
													<i class="fa fa-arrows"></i>
												</button>
											</div>
										<?php
											} else {
												echo '<div class="editzone-gui">';
												echo '<strong>';
												echo '<svg width="100%" height="100%" viewBox="0 -120 1000 300" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><text font-size="120" fill="black" x="80">'.$this->main->lang('Drag to move').'</text><text font-size="80" fill="#555" y="120" x="30%">'.$this->main->lang('Design area').'</text></svg>';
												echo '</strong>';
												echo '</div>';
											}
											
										?>
										<i class="fa fa-expand" data-func="resize" title="<?php 
											echo $this->main->lang('Resize the edit zone'); 
										?>"></i>
										<?php
											
											if ($this->main->connector->platform == 'php') {
												if (isset($design) && $design !== null) {
													echo '<div class="design-template-inner" '.(
														(isset($sdata->edit_zone->radius) && !empty($sdata->edit_zone->radius)) ? 
														'style="border-radius: '.$sdata->edit_zone->radius.'px"' : 
														'' 
													).' data-id="'.$design['id'].'">';
													echo '<img style="'.$sdata->template->css.'" src="'.$design['screenshot'].'">';
													echo '</div>';
												}else{
													echo '<button data-func="select-design" class="design-template-btn">';
													echo '<i class="fa fa-paint-brush"></i> ';
													echo $this->main->lang('Design Template');
													echo '</button>';
												}
											}
											
										?>
										
									</div>
									
									<div class="editzone-ranges" style="<?php if(isset($sdata->hide_size) && $sdata->hide_size == true){ echo 'display:none;'; } ?>">
										<?php
											$pos = array(
												"template" => isset($sdata->template->offset) ? $sdata->template->offset : array(),
												"edit_zone" => array(
													"height" => $sdata->edit_zone->height,
													"width" => $sdata->edit_zone->width,
													"left" => $sdata->edit_zone->left,
													"top" => $sdata->edit_zone->top
												),
												"product_width" => $sdata->product_width,
												"product_height" => $sdata->product_height
											);	
										?>
										<input type="hidden" name="pos" value="<?php echo htmlentities(json_encode($pos)); ?>" />
										<?php if ($this->main->connector->platform == 'php') { ?>
										<div class="edr-row design-scale"<?php if(!isset($design) || $design === null)echo ' style="display: none;"';?>>
											<label><?php echo $this->main->lang('Design scale'); ?>:</label>
											<input type="range" min="10" max="200" value="<?php
												if (isset($sdata->template) && isset($sdata->template->scale))
													echo $sdata->template->scale; 
											?>" />
										</div>
										<?php } ?>
										<div class="edr-row editzone-radius">
											<label><?php echo $this->main->lang('Editzone radius'); ?>:</label>
											<input type="range" min="0" max="100" value="<?php
												if (isset($sdata->edit_zone->radius) && !empty($sdata->edit_zone->radius))
													echo $sdata->edit_zone->radius;
												else echo 0; 
											?>" />
										</div>
										<div class="edr-row" data-row="sizes">
											<label><?php echo $this->main->lang('Size for printing'); ?>:</label>
											<select data-name="sizes">
												<option value=""> === <?php echo $this->main->lang('Select Size'); ?> === </option>
												<option<?php 
													if (isset($sdata->size) && is_object($sdata->size)) 
														echo ' selected'; 
												?> value="custom"><?php echo $this->main->lang('Custom'); ?></option>
											<?php
												foreach ($this->main->cfg->size_default as $s => $v) {
													echo '<option value="'.$v['cm'].'"'.(
														isset($sdata->size) && $sdata->size == $v['cm'] ? ' selected' : ''
														).'>'.$s.'</option>';
												}		
											?></select>
											<a href="#tip" class="tip">
												<i class="fa fa-question-circle"></i>
												<span><?php echo $this->main->lang('Select the size of design area for printing, or you can customize it by your size'); ?></span>
											</a>
										</div>
										<div class="edr-row" <?php
											if (!isset($sdata->size) || !is_object($sdata->size))
												echo 'style="display:none"';	
										?> data-row="values">
											<label><?php echo $this->main->lang('Size values'); ?>:</label>
											<span>
												<input value="<?php 
												echo isset($sdata->size) && isset($sdata->size->width) ? $sdata->size->width : ''; 
												?>" type="text" data-name="width" placeholder="Width" />
												<span class="constrain-aspect-ratio<?php
													if (!isset($sdata->size->contrain) || $sdata->size->contrain === true)
														echo ' active';
												?>" title="<?php echo $this->main->lang('Constrain aspect ratio'); ?>">
													<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="30px" height="30px" viewBox="0 0 950 950" xml:space="preserve"><path d="M226.1,702.799H414.2c11.5,0,20.899-9.299,20.899-20.898V564.4H331.3V597.1H226.1c-66.5,0-120.3-53.9-120.3-120.301v-3.5    c0-66.5,53.9-120.3,120.3-120.3h105.2v32.7h103.8V268c0-11.5-9.3-20.9-20.899-20.9H226.1c-60.4,0-117.2,23.5-159.9,66.2    C23.5,356,0,412.799,0,473.2v3.5C0,537.1,23.5,593.9,66.2,636.6C108.9,679.299,165.7,702.799,226.1,702.799z"></path><path d="M723.899,247.2H531.8c-11.5,0-20.9,9.3-20.9,20.9v117.6H618.7V353h105.199c66.5,0,120.301,53.9,120.301,120.3v3.5    c0,66.5-53.9,120.301-120.301,120.301H618.7V564.4H510.899V682c0,11.5,9.301,20.9,20.9,20.9h192.1c60.4,0,117.2-23.5,159.9-66.201    c42.7-42.699,66.2-99.5,66.2-159.9v-3.5c0-60.399-23.5-117.2-66.2-159.9S784.3,247.2,723.899,247.2z"></path><path d="M331.3,425H300c-27.6,0-50,22.4-50,50c0,27.6,22.4,50,50,50h31.3h103.8H511h107.8h31.3c27.601,0,50-22.4,50-50    c0-27.6-22.399-50-50-50h-31.3h-107.9H435H331.3z"></path></svg>
												</span>
												<input value="<?php 
												echo isset($sdata->size) && isset($sdata->size->height) ? $sdata->size->height : ''; 
												?>" type="text" data-name="height" placeholder="Height" />
											</span>
											<a href="#tip" class="tip">
												<i class="fa fa-question-circle"></i>
												<span><?php echo $this->main->lang('The real size width x height, which will be used for export & printing'); ?></span>
											</a>
										</div>
										<div class="edr-row" <?php
											if (!isset($sdata->size) || !is_object($sdata->size))
												echo 'style="display:none"';	
										?> data-row="unit">
											<label><?php echo $this->main->lang('Size unit'); ?>:</label>
											<select data-name="unit">
												<option <?php
													if (
														isset($sdata->size) && 
														isset($sdata->size->unit) && 
														$sdata->size->unit == 'cm'
													) echo 'selected';
												?> value="cm">Centimeters</option>
												<option <?php
													if (
														isset($sdata->size) && 
														isset($sdata->size->unit) && 
														$sdata->size->unit == 'inch'
													) echo 'selected';
												?> value="inch">Inch</option>
												<option <?php
													if (
														isset($sdata->size) && 
														isset($sdata->size->unit) && 
														$sdata->size->unit == 'px'
													) echo 'selected';
												?> value="px">Pixel</option>
											</select>
										</div>
										<div class="edr-row" style="display:none;">
											<label><?php echo $this->main->lang('Print orientation'); ?>:</label>
											<select data-name="orientation">
												<option <?php
													if (
														isset($sdata->orientation) && 
														$sdata->orientation == 'portrait'
													) echo 'selected';
												?> value="portrait">Portrait</option>
												<option <?php
													if (
														isset($sdata->orientation) && 
														$sdata->orientation == 'landscape'
													) echo 'selected';
												?> value="landscape">Landscape</option>
											</select>
										</div>
										<div class="edr-row" data-row="include-base">
											<label><?php echo $this->main->lang('Export include base'); ?>:</label>
											<div class="lumise-toggle">
												<input type="checkbox" <?php
													echo (isset($sdata->include_base) && $sdata->include_base == 'yes' ? 'checked' : '');
												?> data-name="include_base">
												<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
												<span class="lumise-toggle-handle"></span>
											</div>
											<a href="#tip" class="tip">
												<i class="fa fa-question-circle"></i>
												<span><?php echo $this->main->lang('Export for printing include product base image.'); ?></span>
											</a>
										</div>
										<div class="edr-row" data-row="crop-marks">
											<label><?php echo $this->main->lang('Crop marks & bleed'); ?>:</label>
											<div class="lumise-toggle">
												<input type="checkbox" <?php
													echo (isset($sdata->crop_marks_bleed) && $sdata->crop_marks_bleed == 'yes' ? 'checked' : '');
												?> data-name="crop_marks_bleed">
												<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
												<span class="lumise-toggle-handle"></span>
											</div>
											<a href="#tip" class="tip">
												<i class="fa fa-question-circle"></i>
												<span><?php echo $this->main->lang('Show the guide line for crop marks and bleed in Lumise editor.'); ?></span>
											</a>
										</div>
										<!--div class="edr-row" data-row="bleed-range"<?php
											if (!isset($sdata->crop_marks_bleed) || $sdata->crop_marks_bleed != 'yes') {
												echo ' style="display:none;"'; 
											}
											?>>
											<label><?php echo $this->main->lang('Bleed range'); ?>:</label>
											<input style="width: 150px" data-name="bleed_range" type="text" placeholder="<?php echo $this->main->lang('Typically it is 2mm'); ?>" value="<?php
												echo (isset($sdata->bleed_range) ? $sdata->bleed_range : '');
												?>" data-unit="mm" />
												<a href="#tip" class="tip">
													<i class="fa fa-question-circle"></i>
													<span><?php echo $this->main->lang('The bleeed range in milimet, typically it is 2mm'); ?></span>
												</a>
										</div-->
									</div>
										
								</div>
								<div class="lumise-stage-btn" style="<?php if(isset($sdata->hide_size) && $sdata->hide_size == true){ echo 'display:none;'; } ?>"> 
									<button type="button" class="lumise-button lumise-button-large" data-func="select">
										<i class="fa fa-th"></i>
										<?php echo $this->main->lang('Select product image'); ?>
									</button>
									<?php if ($this->main->connector->platform == 'php') { ?>
									<button type="button" class="lumise-button lumise-button-large" data-func="download">
										<i class="fa fa-download"></i>
										<?php echo $this->main->lang('Download mockup'); ?>
									</button>
									<?php } ?>
									<button type="button" class="lumise-button lumise-button-large" data-func="reset">
										<i class="fa fa-refresh"></i>
										<?php echo $this->main->lang('Reset all'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				<?php 
					} 
				}
				?>
			</div>
		</div>
		<textarea style="display: none;" id="lumise-field-stages-inp" class="stages-field" name="<?php echo isset($args['name']) ? $args['name'] : ''; ?>"><?php
			echo isset($args['value']) ? $args['value'] : '';
		?></textarea>
		<div id="lumise-popup" class="lumise_form_group lumise_content">
			<div class="lumise-popup-content">
				<header>
					<h3>
						<span><?php echo $this->main->lang('Select image for product base'); ?></span>
						<button class="lumise-btn" data-act="samples">
							<i class="fa fa-th"></i> 
							<?php echo $this->main->lang('Lumise samples'); ?>
						</button>
						<button class="lumise-btn hidden" data-act="uploaded">
							<i class="fa fa-arrow-left"></i> 
							<?php echo $this->main->lang('My Uploaded'); ?>
						</button>
						<?php
							if (!$lumise->caps('lumise_can_upload')) {
						?>
						<button class="lumise-btn-primary" style="background-color: #bfbfbf !important;cursor: no-drop;" data-act="upload">
							<i class="fa fa-cloud-upload"></i> 
							<?php echo $this->main->lang('Upload new image'); ?>
						</button>
						<small style="color:red"><?php echo $this->main->lang('Sorry, You are not allowed to upload files. Please ask the administrator for permission'); ?></small>
						<?php } else { ?>
						<button class="lumise-btn-primary" data-act="upload">
							<i class="fa fa-cloud-upload"></i> 
							<?php echo $this->main->lang('Upload new image'); ?>
						</button>
						<small><?php echo $this->main->lang('Accept file type: .jpg, .png, .svg (1KB -> 5MB)'); ?></small>
						<input type="file" class="hidden" id="lumise-product-upload-input" />
						<?php } ?>
					</h3>
					<span class="close-pop"><svg enable-background="new 0 0 32 32" height="32px" id="close" version="1.1" viewBox="0 0 32 32" width="32px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M17.459,16.014l8.239-8.194c0.395-0.391,0.395-1.024,0-1.414c-0.394-0.391-1.034-0.391-1.428,0  l-8.232,8.187L7.73,6.284c-0.394-0.395-1.034-0.395-1.428,0c-0.394,0.396-0.394,1.037,0,1.432l8.302,8.303l-8.332,8.286  c-0.394,0.391-0.394,1.024,0,1.414c0.394,0.391,1.034,0.391,1.428,0l8.325-8.279l8.275,8.276c0.394,0.395,1.034,0.395,1.428,0  c0.394-0.396,0.394-1.037,0-1.432L17.459,16.014z" fill="#121313" id="Close"></path></svg></span>
				</header>
				<div id="lumise-base-images">
					<p class="lumise-notice"><?php 
						echo $this->main->lang('Notice: If you want the upload product image have the ability to change color on the editor.'); 
						echo ' <a href="https://docs.lumise.com/product-mask-image/" target="_blank">';
						echo $this->main->lang('Read more Mask Image');
						echo ' <i class="fa fa-arrow-circle-o-right"></i>';
						echo '</a>';
					?></p>
					<ul class="lumise-stagle-list-base" id="lumise-uploaded-bases">
						<li data-act="load-more" data-start="0"><?php echo $this->main->lang('Load more'); ?></li>
					</ul>
					<ul class="lumise-stagle-list-base hidden" id="lumise-sample-bases">
						<?php
							foreach($this->main->cfg->base_default as $item) {
								echo '<li><img data-act="base" data-src="products/'.$item.'" data-source="raws" src="'.$this->main->cfg->assets_url.'raws/products/'.$item.'" />';
								echo '<span>'.str_replace(array('_', '.png'), array(' ', ''), $item).'</span>';
								echo '</li>';
							}
						?>
					</ul>
				</div>
				<div id="lumise-base-upload-progress" class="hidden">
					<div data-view="uploading" class="hidden">
						<span></span>
						<progress value="0" max="100"></progress>
					</div>
					<div data-view="success" class="hidden">
						<img src="" />
						<h5><?php echo $this->main->lang('Upload completed!'); ?></h5>
						<span>
							<button class="lumise-button" data-act="use">
								<i class="fa fa-check" data-act="use"></i> 
								<?php echo $this->main->lang('Use this image'); ?>			
							</button>
							<button class="lumise-button lumise-button-primary" data-act="upload">
								<i class="fa fa-cloud-upload" data-act="upload"></i> 
								<?php echo $this->main->lang('Upload another'); ?>			
							</button>
						</span>
						<svg data-act="dismiss" height="24px" width="24px" version="1.1" viewBox="0 0 32 32"  xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M17.459,16.014l8.239-8.194c0.395-0.391,0.395-1.024,0-1.414c-0.394-0.391-1.034-0.391-1.428,0  l-8.232,8.187L7.73,6.284c-0.394-0.395-1.034-0.395-1.428,0c-0.394,0.396-0.394,1.037,0,1.432l8.302,8.303l-8.332,8.286  c-0.394,0.391-0.394,1.024,0,1.414c0.394,0.391,1.034,0.391,1.428,0l8.325-8.279l8.275,8.276c0.394,0.395,1.034,0.395,1.428,0  c0.394-0.396,0.394-1.037,0-1.432L17.459,16.014z" data-act="dismiss"></path></svg>
					</div>
					<div data-view="fail" class="hidden">
						<svg  height="60px" width="60px" version="1.1" viewBox="0 0 32 32"  xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M17.459,16.014l8.239-8.194c0.395-0.391,0.395-1.024,0-1.414c-0.394-0.391-1.034-0.391-1.428,0  l-8.232,8.187L7.73,6.284c-0.394-0.395-1.034-0.395-1.428,0c-0.394,0.396-0.394,1.037,0,1.432l8.302,8.303l-8.332,8.286  c-0.394,0.391-0.394,1.024,0,1.414c0.394,0.391,1.034,0.391,1.428,0l8.325-8.279l8.275,8.276c0.394,0.395,1.034,0.395,1.428,0  c0.394-0.396,0.394-1.037,0-1.432L17.459,16.014z" fill="red"></path></svg>
						<h5><?php echo $this->main->lang('Upload fail!'); ?></h5>
						<span>
							<button class="lumise-button" data-act="dismiss">
								<i class="fa fa-times" data-act="dismiss"></i> 
								<?php echo $this->main->lang('Dismiss'); ?>			
							</button>
							<button class="lumise-button lumise-button-primary" data-act="upload">
								<i class="fa fa-cloud-upload" data-act="upload"></i> 
								<?php echo $this->main->lang('Try again'); ?>			
							</button>
						</span>
					</div>
				</div>
			</div>
		</div>
		<input type="file" id="lumise-stages-upload-helper" style="display: none;" />
	</div>
	<script type="text/javascript">
		
		var lumise_upload_url = '<?php echo $this->main->cfg->upload_url; ?>',
			lumise_assets_url = '<?php echo $this->main->cfg->assets_url; ?>';
				
		document.lumiseconfig = {
			main: 'product',
			ce: '<?php echo $this->main->lang('The color has exist, please select another'); ?>',
			hs: '<?php echo $this->main->lang('No stages configured, please select image with Edit Area for a minimum of one stage in tab Product Design'); ?>',
			sm: '<?php echo $this->main->lang('The size of image is too small (50KB - 10000KB)'); ?>',
			lg: '<?php echo $this->main->lang('The size of image is too large (50KB - 10000KB)'); ?>',
			tp: '<?php echo $this->main->lang('Only accept image type *.jpg, *.png or *.svg'); ?>',
			ru: '<?php echo $this->main->lang('Your upload is '); ?>',
			bases: <?php echo json_encode($this->main->cfg->base_default); ?>,
			max_stages: <?php echo $this->main->cfg->max_stages; ?>,
			noc : '<?php echo $this->main->lang('Error, you can not create multiple attributes of this type'); ?>'
		};
	</script>
	<?php
	}
	
	
	public function field_variations($args) {
	?>
		<div id="lumise-variations">
			<div class="lumise-att-layout">
				<div class="lumise-att-layout-default hidden">
					<strong>Default Form Values:</strong>
					<div class="att-layout-conditions"></div>
				</div>
				<div class="lumise-att-layout-create">
					<button class="lumise-button" style="display: none;" data-act="add_variation">
						<i class="fa fa-plus" data-act="add_variation"></i> 
						<?php echo $this->main->lang('Add new variation'); ?>
					</button>
					<button class="lumise-button" style="display: none;" data-act="bulk_edit_variation">
						<i class="fa fa-pencil" data-act="bulk_edit_variation"></i> 
						<?php echo $this->main->lang('Bulk edit variations'); ?>
					</button>
					<a href="#close" style="display: none;" data-act="close"><?php echo $this->main->lang('Close'); ?></a>
					<a data-act="sp" style="display: none;">/</a>
					<a href="#expand" style="display: none;" data-act="expand"><?php echo $this->main->lang('Expand'); ?></a>
					<p data-view="notice">
						<?php echo $this->main->lang('Before you can add a variation you need to add some variation attributes on the Attributes tab.'); ?> <a href="https://docs.lumise.com/backend-management/product-base/variables/?utm_source=clients&amp;utm_medium=links&amp;utm_campaign=client-site&amp;utm_term=attributes&amp;utm_content=<?php echo $this->main->connector->platform; ?>" target="_blank"><?php echo $this->main->lang('Learn more'); ?> &rarr;</a>
					</p>
				</div>
				<div id="lumise-field-variations-items" class="lumise-field-layout-items"></div>
				<div class="lumise-att-layout-tmpl hidden">
					<div class="lumise-att-layout-item">
						<div class="att-layout-headitem" data-act="toggle">
							<div class="att-layout-conditions">
								<strong data-act="toggle">#1</strong>
							</div>
							<div class="att-layout-funcs">
								<a title="Arrange variables" href="#arrange" data-act="arrange">
									<i class="fa fa-bars" data-act="arrange"></i>
								</a>
								<a title="Delete variable" href="#delete" data-act="delete">
									<i class="fa fa-trash" data-act="delete"></i>
								</a>
								<a title="Expand/close" href="#toggle" data-act="toggle">
									<i class="fa fa-caret-down" data-act="toggle"></i>
								</a>
							</div>
						</div>
						<div class="att-layout-body">
							<div class="att-layout-body-field third-left">
								<label><?php echo $this->main->lang('Regular price'); ?></label>
								<input type="text" data-name="price" placeholder="Variation price (required)" />
							</div>
							<div class="att-layout-body-field third-midle">
								<label><?php echo $this->main->lang('Min Quantity'); ?></label>
								<input data-name="min-qty" type="text" />
							</div>
							<div class="att-layout-body-field third-right">
								<label><?php echo $this->main->lang('Max Quantity'); ?></label>
								<input data-name="max-qty" type="text" />
							</div>
							<div class="att-layout-body-field full">
								<label><?php echo $this->main->lang('Description'); ?></label>
								<textarea data-name="description"></textarea>
							</div>
							<div class="att-layout-body-field full pdtop">
								<label><?php echo $this->main->lang('Configure printing techniques'); ?>:</label>
								<div class="lumise-toggle">
									<input type="checkbox" data-name="cfgprinting" data-cfgprinting="true" value="1">
									<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
									<span class="lumise-toggle-handle"></span>
								</div>
								<span class="tip">
									<i class="fa fa-question-circle"></i>
									<span><?php echo $this->main->lang('You can configure the printing to change the pricing for this variable'); ?></span>
								</span>
								<div class="att-layout-cfgprinting" style="display: none;"></div>
							</div>
							<div class="att-layout-body-field full pdtop">
								<label><?php echo $this->main->lang('Custom designs configuration'); ?>:</label>
								<div class="lumise-toggle">
									<input type="checkbox" data-name="cfgstages" data-cfgstages="true" value="1">
									<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
									<span class="lumise-toggle-handle"></span>
								</div>
								<span class="tip">
									<i class="fa fa-question-circle"></i>
									<span><?php echo $this->main->lang('You can configure product images, edit zones and stages for this variable'); ?></span>
								</span>
								<div class="att-layout-cfgstages" style="display: none;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<textarea style="display: none;" id="lumise-field-variations-inp" class="stages-field" name="<?php echo isset($args['name']) ? $args['name'] : ''; ?>"><?php
			echo isset($args['value']) ? $args['value'] : '';
		?></textarea>
	<?php
	}
	
	
	public function field_shape($args) {
	?><div id="lumise_shape_preview"></div><br />
		<textarea name="<?php echo isset($args['name']) ? $args['name'] : ''; ?>" id="lumise_shape_content"><?php echo !empty($args['value']) ? $args['value'] : '&lt;svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0,0,100,100"&gt;&lt;polygon points="50 0, 0 100, 100 100"&gt;&lt;/polygon&gt;&lt;/svg&gt;' ?></textarea>
		<script type="text/javascript">
			window.onload = function() {

				jQuery('#lumise_shape_content').on('input', function(e) {
					jQuery('#lumise_shape_preview').html(this.value);
				}).trigger('input');

			};
		</script>
	<?php
	}

	public function field_print($args) {
		
		global $lumise;
		
		$printing_types = $args['prints_type'];
		$prices = isset($args['value'])? $this->dejson($args['value']) : json_decode('{"type":"multi", "multi" : "true"}');

		$print_type = isset($prices->type)? $prices->type : 'multi';

		if (isset($printing_types[$print_type]) && isset($prices->values)) {
        	$printing_types[$print_type]['values'] = $prices->values;
        }
        
		?>
		<div data-view="multi">
			<div class="lumise-toggle">
				<input type="checkbox" data-func="multi" <?php echo ((isset($prices->multi) && $prices->multi) ? 'checked' : ''); ?> value="1">
				<span class="lumise-toggle-label" data-on="Yes" data-off="No"></span>
				<span class="lumise-toggle-handle"></span>
			</div>
			<em class="notice"><?php echo $this->main->lang('Allow setup price for each stage?'); ?></em>
		</div>
		<?php foreach ($printing_types as $type => $calcs) { ?>
		<div class="lumise_radios">
			<div class="radio">
				<input type="radio" data-func="type" name="lumise-printing-<?php echo $args['name']; ?>" id="lumise-radio-<?php echo $type; ?>" value="<?php echo $type; ?>" <?php if($type == $print_type) echo 'checked'; ?>>
				<label for="lumise-radio-<?php echo $type; ?>">
					<?php echo $calcs['label']; ?>
					<div class="check"></div>
				</label>
				<em class="notice">
					<?php echo $calcs['desc']; ?>
				</em>
			</div>
            <div class="lumise_radio_content" data-type="<?php echo $type; ?>"></div>
		</div>
		<?php } ?>
		<input type="hidden" name="<?php echo $args['name']; ?>" data-func="data-saved" value="<?php echo isset($args['value']) ? $args['value'] : ''; ?>" />
		<p data-view="multi"></p>
		<?php echo $this->main->lang('If you need to understand more about the printing cost calculator'); ?>. <a href="https://docs.lumise.com/printing-cost-calculator/?utm_source=clients&utm_medium=links&utm_campaign=client-site&utm_term=attributes&utm_content=<?php echo $this->main->connector->platform; ?>" target=_blank><?php echo $this->main->lang('Click for more details'); ?></a>
		<script>
			document.lumiseconfig = {
				main: 'printing',
				ops: {
					data: <?php echo json_encode( (object) $printing_types ); ?>,
			   		multi: <?php echo (isset($printing_types['multi_sides']) && $printing_types['multi_sides'] == 1)? 'true' : 'false'; ?>,
			   		show_detail: '<?php echo isset($prices->show_detail) ? $prices->show_detail : ''; ?>',
			   		current_type: '<?php echo ($type ? $type : 'multi'); ?>',
			   		langs: {
			    		aqr: '<?php echo $this->main->lang('Add Quantity Range'); ?>',
			    		qr: '<?php echo $this->main->lang('Quantity Range'); ?>',
			    		nd: '<?php echo $this->main->lang('You can not remove all items, must have at least one option for printing method.'); ?>'
					},
					max_stages: <?php echo $this->main->cfg->max_stages; ?>
				}
			}
		</script>
		<?php
	}

	public function field_tabs($args) {

		if (!isset($args['tabs'])) {
			echo 'Missing option tabs';
			return;
		}
		
		if(is_array($args['value']) && !count($args['value']))
			$args['value'] = $args['default'];
			
		if (is_string($args['value']))
			$value = @json_decode($args['value']);
		else $value = $args['value'];

		if ($value === null)
			$value = array();

		$tabs = array();

		for ($i=0; $i<$args['tabs']; $i++) {
			$tabs['Tab '.($i+1)] = array(
				array(
					'type' => 'input',
					'name' => $args['name'].'['.$i.'][title]',
					'label' => $this->main->lang('Title'),
					'value' => isset($value[$i]) ? $value[$i]->title : ''
				),
				array(
					'type' => 'text',
					'name' => $args['name'].'['.$i.'][content]',
					'label' => $this->main->lang('Content'),
					'value' => isset($value[$i]) ? stripslashes($value[$i]->content) : ''
				),
			);
		}

		$this->tabs_render(array(
			'tabs' => $tabs
		));

	}

	public function field_google_fonts($args) {
	?>
	<div class="lumise-field-google_fonts">
		<ul>
			<?php
				
				$fonts = json_decode(htmlspecialchars_decode(trim($args['value'])), true);
				
				if (is_array($fonts) && count($fonts) > 0) {
					foreach ($fonts as $name => $font) {
						
						$txt = str_replace(' ', '+', urldecode($name)).':'.$font[1];
						echo '<li data-n='.$name.' data-f="'.$font[0].'" data-s="'.$font[1].'">';
						echo '<link rel="stylesheet" href="//fonts.googleapis.com/css?family='.$txt.'" />';
						echo '<font style="font-family: '.urldecode($name).';">'.urldecode($name).'</font>';
						echo '<delete data-act="delete">'.$this->main->lang('Delete').'</delete>';
						echo '</li>';
					} 
				} else {
					echo '<p class="lumise-notice">'.$this->main->lang('No items found').'</p>';
				}
			?>
		</ul>
		<p>
			<button data-btn="primary" data-act="add">
				<i class="fa fa-plus"></i> <?php echo $this->main->lang('Add new google font'); ?>
			</button>
		</p>
		<textarea data-func="value" style="display: none;" name="<?php echo isset($args['name']) ? $args['name'] : ''; ?>"><?php 
			echo isset($args['value']) ? $args['value'] : ''; 
		?></textarea>
	</div>
	<?php
	}
	
	public function field_attributes($args) {
	?>
	<div class="lumise_form_content">
		<div class="lumise-att-layout">
			<div class="lumise-att-layout-create">
				<button class="lumise-button" data-act="add_attribute">
					<i class="fa fa-plus" data-act="add_attribute"></i> 
					<?php echo $this->main->lang('Add new attribute'); ?>
				</button>
				<a href="#close" data-act="close"><?php echo $this->main->lang('Close'); ?></a>
				<a>/</a>
				<a href="#expand" data-act="expand"><?php echo $this->main->lang('Expand'); ?></a>
			</div>
			<div id="lumise-field-attributes-items" class="lumise-field-layout-items"><?php

				$attrs = $this->dejson($args['value']);

				
			?></div>
			<div class="lumise-att-layout-tmpl hidden">
				<div class="lumise-att-layout-item">
					<div class="att-layout-headitem" data-act="toggle">
						<strong data-name="Untitled">Untitled</strong> 
						<em data-view="attr-type">text</em>
						<div class="att-layout-funcs">
							<a title="Arrange variables" href="#arrange" data-act="arrange">
								<i class="fa fa-bars" data-act="arrange"></i>
							</a>
							<a title="Delete variable" href="#delete" data-act="delete">
								<i class="fa fa-trash" data-act="delete"></i>
							</a>
							<a title="Expand/close" data-act="toggle" href="#toggle">
								<i class="fa fa-caret-up" data-act="toggle"></i>
							</a>
						</div>
					</div>
					<div class="att-layout-body">
						<div class="att-layout-body-field one-third">
							<label><?php echo $this->main->lang('Name'); ?></label>
							<input type="text" data-name="name" value="Untitled" />
							<label><?php echo $this->main->lang('Attribute type'); ?></label>
							<p data-field="type">
								<select data-name="type">
									<option value=""> === <?php echo $this->main->lang('Select attribute type'); ?> === </option>
									<?php
										$values_render = array();
										foreach ($this->main->cfg->product_attributes as $name => $data) {
											if (!isset($data['hidden']) || $data['hidden'] !== true) {
												echo '<option value="'.$name.'"'.(isset($data['unique']) && $data['unique'] === true ? ' data-unique="true"' : '').(isset($data['use_variation']) && $data['use_variation'] === true ? ' data-use-variation="true"' : '').'>'.$data['title'].'</option>';
											}
											if (isset($data['values'])) {
												$values_render[$name] = trim($data['values']);
											}
										}
									?>
								</select> 
								&nbsp; 
								<span class="tip">
									<i class="fa fa-question-circle"></i>
									<span>
										<?php echo $this->main->lang('Select the attribute type to display this attribute on the editor. You can be personalized it by built the addon'); ?>
									</span>
								</span>
							</p>
							<p data-field="required">
								<input id="" data-name="required" type="checkbox" />
								<label for=""><?php echo $this->main->lang('Field required'); ?></label>
							</p>
							<p data-field="use_variation">
								<input id="" data-name="use_variation" type="checkbox" />
								<label for=""><?php echo $this->main->lang('Used for variations'); ?></label>
							</p>
						</div>
						<div class="att-layout-body-field two-third" data-field="values"></div>
					</div>
				</div>
			</div>
			<textarea data-func="value" id="lumise-field-attributes-inp"  style="display: none;" name="<?php echo isset($args['name']) ? $args['name'] : ''; ?>"><?php echo isset($args['value']) ? $args['value'] : ''; ?></textarea>
			<script type="text/javascript">window.lumise_attribute_values_render = <?php 
				
				$values_render['_values'] = <<<EOF
		
					var el = $('<label>{$this->main->lang('Default value')}</label>'+
					'<textarea data-name="values">'+(values !== undefined ? values : '')+'</textarea>');
					wrp.html('').append(el);
			
EOF;
				$values_render['_values'] = trim($values_render['_values']);
				echo json_encode($values_render); 
				
			?></script>
		</div>
	</div>
	<?php
	}
		
	public function field_template($args) {
		
		if (isset($args['value']) && !empty($args['value'])) {
			
			$db = $this->main->get_db();
			$db->where ('id', $args['value']);
			$template = $db->getOne ('templates');
			
		}
		
	?><div id="lumise_template"><?php
		if (isset($template['screenshot'])) {
			echo '<img src="'.$template['screenshot'].'" style="max-width: 250px;" /><br><a class="button" href="#delete"><i class="fa fa-times"></i></a>';
		}
	?></div>
		<button data-btn="" id="lumise_template_btn" style="margin-left: 0px;">
			<i class="fa fa-th"></i>
			<?php echo $this->main->lang('Select template'); ?>
		</button>
		<br />
		<input type="hidden" name="<?php echo isset($args['name']) ? $args['name'] : ''; ?>" id="lumise_template_inp" value="<?php 
			echo !empty($args['value']) ? $args['value'] : '';
		?>" />
		<?php if (!empty($args['value'])) { ?>
			<input type="hidden" name="old-<?php echo $args['name']; ?>" value="<?php echo $args['value'] ?>">
			<input type="hidden" name="old-<?php echo $attr['thumbn']; ?>" value="<?php
				echo isset($attr['thumbn_value']) ? $attr['thumbn_value'] : '';
			?>" />
		<?php } ?>
	<?php
	}
	
	public function order_statuses($current = '', $submit = false){
		
		global $lumise;
		
		$statuses = $lumise->connector->statuses();
		
		$current
		?>
		<select id="lumise_order_statuses" class="lumise_order_statuses" name="order_status">
	        <?php
	            foreach ($statuses as $key => $value) {
	            ?>
	            <option value="<?php echo $key;?>"<?php echo ($current == $key)? ' selected="selected"' : '';?>><?php echo $value;?></option>
	            <?php
	            }
	        ?>
	    </select>
		<?php
		if ($submit) {
			?> <input class="lumise_submit" type="submit" name="submit" value="<?php echo $this->main->lang('Change'); ?>"><?php
		}
	}
	
public function order_designs($data, $attr = true) {
		
		global $lumise_printings, $lumise;
		
		$scrs = array();
		
		$data['design'] = '';
		
		$prtable = false;
		
		$pdfid = '';
		/*
		*	Customized designs
		*/
		// var_dump($data);die();
		if(empty($data['cart_id'])){
			$session_name = 'product_'.$data['order_id'].'_'.$data['product_cms'].'_'.$data['product_base'].'_length';
			$itemCheckAgain = $this->main->db->rawQuery(
					sprintf(
						"SELECT * FROM `%s` WHERE `order_id`='%s' AND `product_id`='%s' AND `product_base`='%s' ",
						$this->main->db->prefix.'order_products',
						$data['order_id'],
						$data['product_cms'],
						$data['product_base']
					)
	
			);

			if(count($itemCheckAgain) > 0){
				// create new temp
				if( 
					!isset($_SESSION[$session_name]) 
					|| ( isset($_SESSION[$session_name]) && $_SESSION[$session_name]['order_id'] != $data['order_id'] )
				){
					$_SESSION[$session_name] = array(
						'order_id' => $data['order_id'],
						'product_cms' => $data['product_cms'],
						'product_base' => $data['product_base'],
						'index' => 0,
						'maxIndex' => count($itemCheckAgain)
					);
				}

				$item = $this->main->db->rawQuery(

					sprintf(
						"SELECT * FROM `%s` WHERE `order_id`='%s' AND `product_id`='%s' AND `product_base`='%s' ORDER BY id ASC LIMIT ".intval($_SESSION[$session_name]['index']).",1",
						$this->main->db->prefix.'order_products',
						$data['order_id'],
						$data['product_cms'],
						$data['product_base']
					)
	
				);
	
				if (count($item) > 0) {
	
					$_SESSION[$session_name]['index'] = intval($_SESSION[$session_name]['index'])+1;
	
					$sc = @json_decode($item[0]['screenshots']);
	
					$prt = @json_decode($item[0]['print_files'], true);
	
					$prtable = true; 
					$pdfid = $item['cart_id'];
	
					$data['design'] = $item[0]['design'];
	
					
	
					foreach ($sc as $i => $s) {
	
						array_push($scrs, array(
	
							"url" => is_array($prt) && isset($prt[$i]) ? $this->main->cfg->upload_url.'orders/'.$prt[$i] : '#',
	
							"screenshot" => $this->main->cfg->upload_url.'orders/'.$s,
	
							"download" => true
	
						));
	
					}
	
					
	
					$prtable = true; 
					$pdfid = $item[0]['cart_id'];

					if(!empty($data['template'])){
						$data['template'] = '';
					}
	
					
	
					$data_obj = $this->main->lib->dejson($item[0]['data']);
	
							
	
					if ($attr === true && isset($data_obj->attributes)) {
	
						
	
						echo '<br>';
	
						
	
						$attrs = (array) $data_obj->attributes;
	
						
	
						foreach ($attrs as $name => $options) {
	
							
	
							if (is_object($options) && isset($options->name)) {
	
								
	
								if (isset($options->value)) {
	
									echo '<div><strong>'.$options->name.':</strong> ';
	
									if (
	
										$options->type == 'color' || 
	
										$options->type == 'product_color'
	
									) {
	
										$val = trim($options->value);
	
										$lab = $options->value;
	
										if (
	
											isset($options->values) && 
	
											is_object($options->values) && 
	
											is_array($options->values->options)
	
										) {
	
											foreach ($options->values->options as $op) {
	
												if ($op->value == $val)
	
													$lab = $op->title;
	
											}
	
										}
	
										echo '<span title="'.htmlentities($options->value).'" style="background:'.$options->value.';padding: 3px 8px;border-radius: 12px;">'.htmlentities($lab).'</span>';
	
									
	
									} else if($options->type == 'quantity') {
	
										
	
										$val = @json_decode($options->value);
	
										
	
										if (
	
											isset($options->values) &&
	
											is_object($options->values) &&
	
											isset($options->values->type) &&
	
											$options->values->type == 'multiple'
	
										) {
	
											foreach ($options->values->multiple_options as $op) {
	
												if (
	
													is_object($val) &&
	
													isset($val->{$op->value})
	
												) 
	
													echo '<span>'.$op->title.': '.$val->{$op->value}.'</span> ';
	
											}
	
										} else echo '<span>'.$options->value.'</span>';
	
										
	
									} else if (
	
										isset($options->values) && 
	
										is_object($options->values) && 
	
										isset($options->values->options) &&
	
										is_array($options->values->options)
	
									) {
	
										
	
										$val = explode("\n", $options->value);
	
										
	
										foreach ($options->values->options as $op) {
	
											if (in_array($op->value, $val))
	
												echo '<span>'.$op->title.'</span> ';
	
										}
	
										
	
									} else echo '<span>'.$options->value.'</span>';
	
									
	
									echo '</div>';
	
								}
	
								
	
							} else {
	
								echo '<dt class="lumise-variation">'.$name.':</dt>';
	
								foreach ($options as $option) {
	
									echo '<dd class="lumise-variation">'.$option.'</dd>';
	
								}
	
							}
	
						}
	
						
	
					}
	
					
	
					if ($attr === true && isset($data_obj->variation) && !empty($data_obj->variation)) {
	
						
	
						echo "<div>";
	
						echo "<strong>".$this->main->lang('Variation').":</strong> ";
	
						echo "<span>#".$data_obj->variation."</span>";
	
						echo "</div>";
	
						
	
					}
	
					
	
					if ($attr === true && isset($data_obj->printing) && is_array($lumise_printings)) {
	
						
	
						foreach ($lumise_printings as $pmethod) {
	
							if ($pmethod['id'] == $data_obj->printing) {
	
								echo "<div>";
	
								echo "<strong>".$this->main->lang('Printing').":</strong> ";
	
								echo "<span>".$pmethod['title']."</span>";
	
								echo "</div>";
	
							}
	
						}
	
						
	
					}
				}

				if( intval($_SESSION[$session_name]['index']) == intval($_SESSION[$session_name]['maxIndex']) ){
					unset($_SESSION[$session_name]);
				}

			}
		}

		if (!empty($data['cart_id'])) {
			
			$item = $this->main->db->rawQuery(
				sprintf(
					"SELECT * FROM `%s` WHERE `cart_id`='%s'",
					$this->main->db->prefix.'order_products',
					$data['cart_id']
				)
			);
			
			if (count($item) > 0) {
				
				$sc = @json_decode($item[0]['screenshots']);
				$prt = @json_decode($item[0]['print_files'], true);
				
				$data['design'] = $item[0]['design'];
				
				foreach ($sc as $i => $s) {
					array_push($scrs, array(
						"url" => is_array($prt) && isset($prt[$i]) ? $this->main->cfg->upload_url.'orders/'.$prt[$i] : '#',
						"screenshot" => $this->main->cfg->upload_url.'orders/'.$s,
						"download" => true
					));
				}
				
				$prtable = true; 
				$pdfid = $data['cart_id'];
				
				$data_obj = $this->main->lib->dejson($item[0]['data']);
						
				if ($attr === true && isset($data_obj->attributes)) {
					
					echo '<br>';
					
					$attrs = (array) $data_obj->attributes;
					
					foreach ($attrs as $name => $options) {
						
						if (is_object($options) && isset($options->name)) {
							
							if (isset($options->value)) {
								echo '<div><strong>'.$options->name.':</strong> ';
								if (
									$options->type == 'color' || 
									$options->type == 'product_color'
								) {
									$val = trim($options->value);
									$lab = $options->value;
									if (
										isset($options->values) && 
										is_object($options->values) && 
										is_array($options->values->options)
									) {
										foreach ($options->values->options as $op) {
											if ($op->value == $val)
												$lab = $op->title;
										}
									}
									echo '<span title="'.htmlentities($options->value).'" style="background:'.$options->value.';padding: 3px 8px;border-radius: 12px;">'.htmlentities($lab).'</span>';
								
								} else if($options->type == 'quantity') {
									
									$val = @json_decode($options->value);
									
									if (
										isset($options->values) &&
										is_object($options->values) &&
										isset($options->values->type) &&
										$options->values->type == 'multiple'
									) {
										foreach ($options->values->multiple_options as $op) {
											if (
												is_object($val) &&
												isset($val->{$op->value})
											) 
												echo '<span>'.$op->title.': '.$val->{$op->value}.'</span> ';
										}
									} else echo '<span>'.$options->value.'</span>';
									
								} else if (
									isset($options->values) && 
									is_object($options->values) && 
									isset($options->values->options) &&
									is_array($options->values->options)
								) {
									
									$val = explode("\n", $options->value);
									
									foreach ($options->values->options as $op) {
										if (in_array($op->value, $val))
											echo '<span>'.$op->title.'</span> ';
									}
									
								} else echo '<span>'.$options->value.'</span>';
								
								echo '</div>';
							}
							
						} else {
							echo '<dt class="lumise-variation">'.$name.':</dt>';
							foreach ($options as $option) {
								echo '<dd class="lumise-variation">'.$option.'</dd>';
							}
						}
					}
					
				}
				
				if ($attr === true && isset($data_obj->variation) && !empty($data_obj->variation)) {
					
					echo "<div>";
					echo "<strong>".$this->main->lang('Variation').":</strong> ";
					echo "<span>#".$data_obj->variation."</span>";
					echo "</div>";
					
				}
				
				if ($attr === true && isset($data_obj->printing) && is_array($lumise_printings)) {
					
					foreach ($lumise_printings as $pmethod) {
						if ($pmethod['id'] == $data_obj->printing) {
							echo "<div>";
							echo "<strong>".$this->main->lang('Printing').":</strong> ";
							echo "<span>".$pmethod['title']."</span>";
							echo "</div>";
						}
					}
					
				}
				
			}
			
		} else 
		/*
		*	Order directly without customization
		*/
		if (!empty($data['template'])) {
			
			$temps = json_decode(urldecode($data['template']));
			if(isset($temps->stages)){
				$tempsData = json_decode(urldecode(base64_decode($temps->stages)));
				$temps = new stdClass();

				foreach ($tempsData as $key => $detail) {
					if(isset($detail->template) && isset($detail->template->id)){
						$detailtemplate = $lumise->lib->get_template($detail->template->id);
						if($detailtemplate != null){
							$tempsData->$key->template->screenshot = $detailtemplate['screenshot'];
						}
						$temps->$key = $detail->template;
					}
				}
			}
			
			foreach ($temps as $n => $d) {
				
				$dsg = $this->main->db->rawQuery(
					sprintf(
						"SELECT * FROM `%s` WHERE `id`=%d",
						$this->main->db->prefix.'templates',
						$d->id
					)
				);
				
				if (count($dsg) > 0 && strpos($dsg[0]['upload'], '.lumi') === false) {
					$pdfid .= $d->id.',';
					array_push($scrs, array(
						"url" => $this->main->cfg->upload_url.$dsg[0]['upload'],
						"screenshot" => $d->screenshot,
						"download" => true
					));
				} else {
					array_push($scrs, array(
						"url" => '',
						"screenshot" => $d->screenshot
					));
				}
				
			}
			
			if (!empty($pdfid)) {
				$pdfid = base64_encode($pdfid);
			}
			
		}
		
		if (count($scrs) > 0) {

			global $lumise;

			$key = $lumise->get_option('purchase_key');
			$key_valid = ($key === null || empty($key) || strlen($key) != 36 || count(explode('-', $key)) != 5) ? false : true;
			
			$is_query = explode('?', $this->main->cfg->tool_url);
			
			$product = wc_get_product($data['product_cms']);
			
			if ($product && $product->get_type() == 'variation') {
				$data['product_base'] = 'variable:'.$data['product_cms'];
				$data['product_cms'] = $product->get_parent_id();
			}
			
			
						
			$url = $this->main->cfg->url.(isset($is_query[1])? '&':'?');
			$url .= 'product_base='.$data['product_base'];
			if (!empty($data['design'])) {
				$url .= '&design_print='.str_replace('.lumi', '', $data['design']);
				$url .= '&order_print='.$data['order_id'];
			}
			$url .= ($this->main->connector->platform != 'php' ? '&product_cms='.$data['product_cms'] : '');
			$url = str_replace('?&', '?', $url);
						
			$html = '<p>';

			if($key_valid){
				foreach ($scrs as $i => $scr) {
					
					$html .= '<a ';
					
					if (isset($scr['download']) && $scr['download'] === true) {
						$html .= 'href="'.$scr['url'].'" download="order_id#'.$data['order_id'].' order_item_id#'.$data['item_id'].' product_base_id#'.$data['product_base'].' (stage '.($i+1).').png"';
						$prtable = true;
					} else {
						$html .= 'href="'.(!empty($scr['url']) ? $scr['url'] : $url).'" target=_blank';
					}
					$html .= '><img width="120" src="'.$scr['screenshot'].'" /></a>';
				}
			}
			
			$html .= '</p>';
			
			if ($prtable === true && $key_valid) {
				$html .= '<p><font color="#E91E63">(*) ';
				$html .= $this->main->lang('Click on each image above to download the printable file <b>(.PNG)</b>').'</font></p>';
			}
			
			$html .= '<p>';

			if(!$key_valid){
				$html .= '<p style="font-size:14px;"><font color="#E91E63">(*) ';
				$html .= $this->main->lang('<span>Please enter your purchase code to display and download file designs</span></br>
<b><a target="_blank" href="'.$this->main->cfg->admin_url.'lumise-page=license"style="font-weight: 700; text-decoration: underline; font-style: italic;">Enter purchase code now</a></b></br>
<span>Notice: Each License can only be used for one domain.</br><a href="https://codecanyon.net/licenses/standard" target="blank" style="font-weight: 700; text-decoration: underline; font-style: italic;">Click to learn more about license term in Envato.</a></span>').'</font></p>';
			}
			
			if (!empty($pdfid)) {

				$link = $this->main->cfg->url;
				if(strpos($link, '?') !== false && substr($link, -1) != '?'){
					$link .= '&pdf_download='.$pdfid;
				} 
				if(strpos($link, '?') !== false && substr($link, -1) == '?') {
					$link .= 'pdf_download='.$pdfid;
				}
				if(strpos($link, '?') === false) {
					$link .= '?pdf_download='.$pdfid;
				}

				if($key_valid) {
					$html .= '<a href="'.$link.'" target=_blank class="button button-primary">'.$this->main->lang('Download designs as PDF').'</a>  &nbsp; <a href="#" data-href="'.$link.'" target=_blank class="button button-primary" onclick="let r = prompt(\'Enter bleed range in mimilet (Typically it is 2mm)\', \'2\');if (r){this.href = this.dataset.href+\'&bleed=\'+r;return true;}else return false;">'.$this->main->lang('PDF cropmarks & bleed').'</a> &nbsp; ';
				}
			}	
			if($key_valid) {
				$html .= '<a href="'.$url.'" target=_blank class="button">'.$this->main->lang('View in Lumise editor').'</a>';
			}
			
			$html .= '</p>';
			
			echo $html;
			
		}

	}

}

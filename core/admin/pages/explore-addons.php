<div class="lumise_wrapper" id="lumise-addons-page">
	<div class="lumise_content">
		<div class="lumise_header">
			<h2><?php echo $lumise->lang('Explore all addons'); ?></h2>
			<a href="<?php echo $lumise->cfg->admin_url; ?>lumise-page=addons"class="add-new lumise-button">
				<i class="fa fa-download"></i> 
				<?php echo $lumise->lang('Your installed addons'); ?>
			</a>
		</div>
		<div class="">
			<?php
				
				$curDate = date_default_timezone_get();
				date_default_timezone_set("Asia/Bangkok");
				$rss = $lumise->lib->remote_connect($lumise->cfg->api_url.'addons/explore.xml?nonce='.date('dH'));
				date_default_timezone_set($curDate);

				$rss = @simplexml_load_string($rss);
				
				if ($rss) {
		
					$count = count($rss->channel->item);
					$installed = $lumise->addons->load_installed(); 
					$html = '';
					for ($i = 0; $i < $count; $i++) {
		
						$item = $rss->channel->item[$i];
						$slug = (string)$item->slug;
						$platforms = explode(',', (string)$item->platforms);
						
						if (
							!isset($installed[$slug]) && 
							in_array($lumise->connector->platform, $platforms)
						) {
							
							$title_link = (
								isset($item->detail) ? 
								$item->detail : 
								(isset($item->link) ? $item->link : 'javascript:avoid(0)')
							);
							
							$html .= '<div class="lumise_wrap lumise_addons"><figure>';
							$html .= '<img src="'.$item->thumb.'">';
							$html .= '<span class="price"><i class="fa fa-dollar" aria-hidden="true"></i>'.$item->price.'</span>';
							$html .= '</figure>';
							$html .= '<div class="lumise_right"><a href="'.$title_link.'" target="_blank">'.$item->title.'</a>';
							$html .= '<div class="lumise_meta">';
							$html .= '<span><i class="fa fa-folder" aria-hidden="true"></i>'.implode(', ', $platforms).'</span>';
							$html .= '</div>';
							$html .= '<p>'.$item->description.'</p>';
							
							if (isset($item->link))
								$html .= '<a href="'.$item->link.'" target=_blank class="buy_now">'.$lumise->lang('Get It Now').' &rarr;</a>';

							$html .= '</div></div>';
						}
		
					}
		
					echo $html;
					
				} else {
					echo '<p>'.$lumise->lang('Could not load data at this time. Please check your internet connection!').'</p>';
				}
			?>
		</div>
	</div>
</div>

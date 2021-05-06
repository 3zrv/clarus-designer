<?php
	$cliparts = $templates = array();
	$data = $lumise->lib->stats();
	$orders = $lumise->connector->orders(array(), 'updated', 'DESC', 5, 0);
	$section = 'Dashbroad';
	$recent_res = $lumise->lib->recent_resources();
	$lumise->lib->display_check_system();
?>
<div class="lumise_wrapper lumise_dashbroad" id="lumise-<?php echo $section; ?>-page">
	<?php $lumise->do_action('dashboard-container'); ?>
	<div class="lumise_container">
		<div class="lumise-col lumise-col-3">
			<div class="lusime_box_stats">
				<i class="fa fa-heart-o" style="background: #1e88e5"></i>
				<div class="box_right">
					<span><?php echo $data['cliparts'];?></span>
					<p><?php echo $lumise->lang('Cliparts'); ?></p>
				</div>
			</div>
		</div>
		<div class="lumise-col lumise-col-3">
			<div class="lusime_box_stats">
				<i class="fa fa-server" style="background: #81C784"></i>
				<div class="box_right">
					<span><?php echo $data['templates'];?></span>
					<p><?php echo $lumise->lang('Templates'); ?></p>
				</div>
			</div>
		</div>
		<div class="lumise-col lumise-col-3">
			<div class="lusime_box_stats">
				<i class="fa fa-shopping-basket" style="background: #7460ee"></i>
				<div class="box_right">
					<span><?php echo $orders['total_count'];?></span>
					<p><?php echo $lumise->lang('Orders'); ?></p>
				</div>
			</div>
		</div>
		<div class="lumise-col lumise-col-3">
			<div class="lusime_box_stats">
				<i class="fa fa-square" style="background: #fc4b6c"></i>
				<div class="box_right">
					<span><?php echo $data['shapes'];?></span>
					<p><?php echo $lumise->lang('Shapes'); ?></p>
				</div>
			</div>
		</div>
	</div>
	<div class="lumise_container">
		<div class="lumise-col lumise-col-6">
			<div class="lusime_box_dashbroad">
				<h3><?php echo $lumise->lang('Recent Orders'); ?></h3>
				<div class="box_content">
					<table class="lumise_table">
						<thead>
							<tr>
								<th><?php echo $lumise->lang('Orders ID'); ?></th>
								<th><?php echo $lumise->lang('Total'); ?></th>
								<th><?php echo $lumise->lang('Status'); ?></th>
								<th><?php echo $lumise->lang('Date'); ?></th>
								<th><?php echo $lumise->lang('View'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if(count($orders['rows']) > 0){
								foreach ($orders['rows'] as $item) {
								?>
								<tr>
									<td><a href="<?php echo $lumise->cfg->admin_url;?>lumise-page=order&order_id=<?php echo $item['id'];?>">#<?php echo $item['id'];?></a></td>
									<td><?php echo $lumise->lib->price($item['total']);?></td>
									<td><em class="pen"><?php echo $lumise->apply_filters('order_status', $item['status']);?></em></td>
									<td><?php echo date("F j, Y", strtotime($item['updated']));?></td>
									<td><a href="<?php echo $lumise->cfg->admin_url;?>lumise-page=order&order_id=<?php echo $item['id'];?>">View</a></td>
								</tr>
								<?php
								}
							}else{
								?>
								<tr>
									<td colspan="5"><?php echo $lumise->lang('Apologies, but no results were found.');?></td>
								</tr>
								<?php
							}
							?>
							
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="lumise-col lumise-col-6">
			<div class="lusime_box_dashbroad lumise_blog">
				<h3><?php echo $lumise->lang('Lumise RSS'); ?></h3>
				<div class="box_content" id="lumise-rss-display">
					<p><i><?php echo $lumise->lang('Loading latest blog'); ?>..</i></p>
				</div>
			</div>
		</div>
	</div>
	<div class="lumise_container">
		<div class="lumise-col lumise-col-6">
			<div class="lusime_box_dashbroad list_thumb">
				<h3><?php echo $lumise->lang('Newest cliparts'); ?></h3>
				<div class="box_content">
					<?php
					
					$count = count($recent_res['cliparts']);
					
					if($count > 0){
						
						echo '<ul>';
						
						for ($i = 0; $i < $count; $i++) {
							$item = $recent_res['cliparts'][$i];
						?>
						<li>
							<div class="thumb_preview">
								<img src="<?php echo $item['thumbnail_url'];?>" alt="<?php echo $item['name'];?>">
								<div class="thumb_overlay">
									<h4><?php echo $item['name'];?></h4>
									<p><?php echo $lumise->lang('Category'); ?>: <?php echo implode(', ', $item['categories']);?></p>
								</div>
							</div>
						</li>
						<?php
						}
						echo '</ul>';
					}else{
						?>
						<p><?php echo $lumise->lang('Apologies, but no results were found.');?></p>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<div class="lumise-col lumise-col-6">
			<div class="lusime_box_dashbroad list_thumb">
				<h3><?php echo $lumise->lang('Newest design templates'); ?></h3>
				<div class="box_content">
					<ul>
					<?php
					$count = count($recent_res['templates']);
					
					if($count > 0){
						
						echo '<ul>';
						
						for ($i = 0; $i < $count; $i++) {
							$item = $recent_res['templates'][$i];
						?>
						<li>
							<div class="thumb_preview">
								<img src="<?php echo $item['thumbnail_url'];?>" alt="<?php echo $item['name'];?>">
								<div class="thumb_overlay">
									<h4><?php echo $item['name'];?></h4>
									<p><?php echo $lumise->lang('Category'); ?>: <?php echo implode(', ', $item['categories']);?></p>
								</div>
							</div>
						</li>
						<?php
						}
						echo '</ul>';
					}else{
						?>
						<p><?php echo $lumise->lang('Apologies, but no results were found.');?></p>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="lumise_support_icon">
		<div class="lumise_support_close">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="17px" height="17px">
			<g><g><path d="M505.943,6.058c-8.077-8.077-21.172-8.077-29.249,0L6.058,476.693c-8.077,8.077-8.077,21.172,0,29.249    C10.096,509.982,15.39,512,20.683,512c5.293,0,10.586-2.019,14.625-6.059L505.943,35.306    C514.019,27.23,514.019,14.135,505.943,6.058z" fill="#FFFFFF"/></g></g><g><g><path d="M505.942,476.694L35.306,6.059c-8.076-8.077-21.172-8.077-29.248,0c-8.077,8.076-8.077,21.171,0,29.248l470.636,470.636    c4.038,4.039,9.332,6.058,14.625,6.058c5.293,0,10.587-2.019,14.624-6.057C514.018,497.866,514.018,484.771,505.942,476.694z" fill="#FFFFFF"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
		</div>
		<div class="lumise_support_open">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="32px" height="32px"><g><g><path d="M346,319c-5.522,0-10,4.477-10,10v69c0,27.57-22.43,50-50,50H178.032c-5.521,0-9.996,4.473-10,9.993l-0.014,19.882    l-23.868-23.867c-1.545-3.547-5.081-6.008-9.171-6.008H70c-27.57,0-50-22.43-50-50V244c0-27.57,22.43-50,50-50h101    c5.522,0,10-4.477,10-10s-4.478-10-10-10H70c-38.598,0-70,31.402-70,70v154c0,38.598,31.402,70,70,70h59.858l41.071,41.071    c1.913,1.913,4.47,2.929,7.073,2.929c1.287,0,2.586-0.249,3.821-0.76c3.737-1.546,6.174-5.19,6.177-9.233L188.024,468H286    c38.598,0,70-31.402,70-70v-69C356,323.477,351.522,319,346,319z" fill="#FFFFFF"/></g></g><g><g><path d="M366.655,0h-25.309C261.202,0,196,65.202,196,145.346s65.202,145.345,145.345,145.345h25.309    c12.509,0,24.89-1.589,36.89-4.729l37.387,37.366c1.913,1.911,4.469,2.927,7.071,2.927c1.289,0,2.589-0.249,3.826-0.762    c3.736-1.548,6.172-5.194,6.172-9.238v-57.856c15.829-12.819,28.978-29.012,38.206-47.102    C506.687,190.751,512,168.562,512,145.346C512,65.202,446.798,0,366.655,0z M441.983,245.535    c-2.507,1.889-3.983,4.847-3.983,7.988v38.6l-24.471-24.458c-1.904-1.902-4.458-2.927-7.07-2.927c-0.98,0-1.97,0.145-2.936,0.442    c-11.903,3.658-24.307,5.512-36.868,5.512h-25.309c-69.117,0-125.346-56.23-125.346-125.346S272.23,20,341.346,20h25.309    C435.771,20,492,76.23,492,145.346C492,185.077,473.77,221.595,441.983,245.535z" fill="#FFFFFF"/>
			</g></g><g><g><path d="M399.033,109.421c-1.443-20.935-18.319-37.811-39.255-39.254c-11.868-0.815-23.194,3.188-31.863,11.281    c-8.55,7.981-13.453,19.263-13.453,30.954c0,5.523,4.478,10,10,10c5.522,0,10-4.477,10-10c0-6.259,2.522-12.06,7.1-16.333    c4.574-4.269,10.552-6.382,16.842-5.948c11.028,0.76,19.917,9.649,20.677,20.676c0.768,11.137-6.539,20.979-17.373,23.403    c-8.778,1.964-14.908,9.592-14.908,18.549v24.025c0,5.523,4.478,10,10,10c5.523,0,10-4.477,9.999-10v-23.226    C386.949,148.68,400.468,130.242,399.033,109.421z" fill="#FFFFFF"/></g></g><g><g><path d="M363.87,209.26c-1.86-1.86-4.44-2.93-7.07-2.93s-5.21,1.07-7.07,2.93c-1.86,1.86-2.93,4.44-2.93,7.07    c0,2.64,1.071,5.22,2.93,7.08c1.86,1.86,4.44,2.92,7.07,2.92s5.21-1.06,7.07-2.92c1.86-1.87,2.93-4.44,2.93-7.08    C366.8,213.7,365.729,211.12,363.87,209.26z" fill="#FFFFFF"/></g></g><g><g><path d="M275,310H64c-5.522,0-10,4.477-10,10s4.478,10,10,10h211c5.523,0,10-4.477,10-10S280.522,310,275,310z" fill="#FFFFFF"/></g></g><g><g><path d="M282.069,368.93C280.21,367.07,277.63,366,275,366s-5.21,1.07-7.07,2.93c-1.861,1.86-2.93,4.44-2.93,7.07    s1.07,5.21,2.93,7.07c1.86,1.86,4.44,2.93,7.07,2.93s5.21-1.07,7.069-2.93c1.861-1.86,2.931-4.43,2.931-7.07    C285,373.37,283.929,370.79,282.069,368.93z" fill="#FFFFFF"/></g></g><g><g><path d="M235.667,366H64c-5.522,0-10,4.477-10,10s4.478,10,10,10h171.667c5.523,0,10-4.477,10-10S241.189,366,235.667,366z" fill="#FFFFFF"/></g></g><g><g><path d="M210,254H64c-5.522,0-10,4.477-10,10s4.478,10,10,10h146c5.523,0,10-4.477,10-10S215.522,254,210,254z" fill="#FFFFFF"/></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
		</div>
	</div>
	<ul class="lumise_list_icon">
		<li><span>Send Message</span><a href="https://www.facebook.com/lumisecom" target="blank" class="fa fa-facebook"></a></li>
		<li><span>Custom Request</span><a href="https://www.lumise.com/services" target="blank" class="fa fa-pencil-square-o"></a></li>
		<li><span>Help Center</span><a href="https://king.ticksy.com/submit" target="blank" class="fa fa-life-ring"></a></li>
	</ul>
</div>

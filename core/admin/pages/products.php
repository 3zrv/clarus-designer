<?php
	$title = "Products";
	$prefix = 'products_';
	$table_name = 'products';

	// Action Form
	if (isset($_POST['action_submit']) && !empty($_POST['action_submit'])) {

		$data_action = isset($_POST['action']) ? $_POST['action'] : '';
		$val = isset($_POST['id_action']) ? $_POST['id_action'] : '';
		$val = explode(',', $val);
		
		$lumise_admin->check_caps('products');
		
		foreach ($val as $value) {

			$dt = $lumise_admin->get_row_id($value, 'products');
			switch ($data_action) {

				case 'active':
					$data = array(
						'active' => 1
					);
					$dt = $lumise_admin->edit_row( $value, $data, 'products' );
					break;
				case 'deactive':
					$data = array(
						'active' => 0
					);
					$dt = $lumise_admin->edit_row( $value, $data, 'products' );
					break;
				case 'delete':
					$tar_file = realpath($lumise->cfg->upload_path).DS;
					if (!empty($dt['thumbnail'])) {
						if (file_exists($tar_file.$dt['thumbnail'])) {
							@unlink($tar_file.$dt['thumbnail']);
							@unlink(str_replace(array($lumise->cfg->upload_url, '/'), array($tar_file, DS), $dt['thumbnail_url']));
						}
					}
					$lumise_admin->delete_row($value, 'products');
					break;
				default:
					break;

			}

		}

	}

	// Search Form
	$data_search = '';
	if (isset($_POST['search_product']) && !empty($_POST['search_product'])) {

		$data_search = isset($_POST['search']) ? trim($_POST['search']) : '';

		if (empty($data_search)) {
			$errors = 'Please Insert Key Word';
			$_SESSION[$prefix.'data_search'] = '';
		} else {
			$_SESSION[$prefix.'data_search'] = $data_search;
		}

	}
	
	if (!empty($_SESSION[$prefix.'data_search'])) {
		$data_search = '%'.$_SESSION[$prefix.'data_search'].'%';
	}
	
	// Pagination
	$per_page = 20;
	if(isset($_SESSION[$prefix.'per_page']))
		$per_page = $_SESSION[$prefix.'per_page'];

	if (isset($_POST['per_page'])) {

		$data = isset($_POST['per_page']) ? $_POST['per_page'] : '';
		
		if ($data != 'none') {
			$_SESSION[$prefix.'per_page'] = $data;
			$per_page = $_SESSION[$prefix.'per_page'];
		} else {
			$_SESSION[$prefix.'per_page'] = 5;
			$per_page = $_SESSION[$prefix.'per_page'];
		}

	}

    // Sort Form
	if (!empty($_POST['sort'])) {

		$dt_sort = isset($_POST['sort']) ? $_POST['sort'] : '';
		$_SESSION[$prefix.'dt_order'] = $dt_sort;

		switch ($dt_sort) {

			case 'order_asc':
				$_SESSION[$prefix.'orderby'] = '`order`';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'order_desc':
				$_SESSION[$prefix.'orderby'] = '`order`';
				$_SESSION[$prefix.'ordering'] = 'desc';
			break;
			case 'id_asc':
				$_SESSION[$prefix.'orderby'] = '`id`';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'id_desc':
				$_SESSION[$prefix.'orderby'] = '`id`';
				$_SESSION[$prefix.'ordering'] = 'desc';
			break;
			case 'name_asc':
				$_SESSION[$prefix.'orderby'] = '`name`';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'name_desc':
				$_SESSION[$prefix.'orderby'] = '`name`';
				$_SESSION[$prefix.'ordering'] = 'desc';
				break;
			default:
			
			break;

		}

	}

	$orderby  = (isset($_SESSION[$prefix.'orderby']) && !empty($_SESSION[$prefix.'orderby'])) ? $_SESSION[$prefix.'orderby'] : 'name';
	$ordering = (isset($_SESSION[$prefix.'ordering']) && !empty($_SESSION[$prefix.'ordering'])) ? $_SESSION[$prefix.'ordering'] : 'asc';
	$dt_order = isset($_SESSION[$prefix.'dt_order']) ? $_SESSION[$prefix.'dt_order'] : 'name_asc';

	// Get row pagination
    $current_page = isset($_GET['tpage']) ? $_GET['tpage'] : 1;
    $search_filter = array(
        'keyword' => $data_search,
        'fields' => 'name'
    );
	
    $start = ( $current_page - 1 ) *  $per_page;
	$products = $lumise_admin->get_rows('products', $search_filter, $orderby, $ordering, $per_page, $start);
	$total_record = $lumise_admin->get_rows_total('products');

    $config = array(
    	'current_page'  => $current_page,
		'total_record'  => $products['total_count'],
		'total_page'    => $products['total_page'],
 	    'limit'         => $per_page,
	    'link_full'     => $lumise->cfg->admin_url.'lumise-page=products&tpage={page}',
	    'link_first'    => $lumise->cfg->admin_url.'lumise-page=products',
	);

	$lumise_pagination->init($config);
	
	if (isset($_GET['fix-attributes'])) {
		$_products = $lumise_admin->get_rows('products', array(), $orderby, $ordering, 10000, 0);

		foreach ($_products['rows'] as $p) {
			
			$attrs = @json_decode(urldecode(base64_decode($p['attributes'])), true);
			$istrik = false; 
			
			foreach ($attrs as $i => $attr) {
				
				if (is_string($attr['values']))
					$v = @json_decode($attr['values'], true);
				else $v = (Array)$attr['values'];
				
				if (
					is_array($v) && 
					isset($v['options']) &&
					is_array($v['options']) &&
					isset($v['options'][0]['title']) &&
					!empty($v['options'][0]['title'])
				) {
					$_v = @json_decode($v['options'][0]['title'], true);
					if (is_array($_v) && isset($_v['options'])) {
					 
						$attrs[$i]['values'] = json_decode($v['options'][0]['title'],true);
						$istrik = true;
					}
				}
			}
			
			if ($istrik === true) {
				$attrs = $lumise->lib->enjson($attrs);
				$lumise_admin->edit_row( $p['id'], array(
					"attributes" => $attrs
				), 'products' );
				echo '<p>Fixed product #'.$p['id'].'</p>';
			}
		}
		echo '<p>All done!</p>';
		exit; 
	}
	
?>

<div class="lumise_wrapper">

	<div class="lumise_content">

		<div class="lumise_header">
			<h2><?php echo $lumise->lang('Products Base'); ?></h2>
			<a href="<?php echo $lumise->cfg->admin_url; ?>lumise-page=product" class="add-new lumise-button">
				<i class="fa fa-plus"></i> 
				<?php echo $lumise->lang('Add New Product Base'); ?>
			</a>
			<?php
				$lumise_page = isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '';
				echo $lumise_helper->breadcrumb($lumise_page);
			?>
		</div>

		

		<div class="lumise_option">
			<div class="left">
				<form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=products" method="post">
					<select name="action" class="art_per_page">
						<option value="none"><?php echo $lumise->lang('Bulk Actions'); ?></option>
						<option value="active"><?php echo $lumise->lang('Active'); ?></option>
						<option value="deactive"><?php echo $lumise->lang('Deactive'); ?></option>
						<option value="delete"><?php echo $lumise->lang('Delete'); ?></option>
					</select>
					<input type="hidden" name="id_action" class="id_action">
					<input  class="lumise_submit" type="submit" name="action_submit" value="<?php echo $lumise->lang('Apply'); ?>">
					<?php $lumise->securityFrom();?>
				</form>
				<form class="less" action="<?php echo $lumise->cfg->admin_url;?>lumise-page=products" method="post">
					<select name="per_page" class="art_per_page" data-action="submit">
						<option value="none">-- <?php echo $lumise->lang('Per page'); ?> --</option>
						<?php
							$per_pages = array('5', '10', '15', '20', '100');

							foreach($per_pages as $val) {

							    if($val == $per_page) {
							        echo '<option selected="selected">'.$val.'</option>';
							    } else {
							        echo '<option>'.$val.'</option>';
							    }

							}
						?>
					</select>
					<?php $lumise->securityFrom();?>
				</form>
				<form class="less" action="<?php echo $lumise->cfg->admin_url;?>lumise-page=products" method="post">
					<select name="sort" class="art_per_page" data-action="submit">
						<option value="">-- <?php echo $lumise->lang('Sort by'); ?> --</option>
						<option value="order_asc" <?php if ($dt_order == 'order_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Order ASC'); ?></option>
						<option value="order_desc" <?php if ($dt_order == 'order_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Order DESC'); ?></option>
						<option value="id_asc" <?php if ($dt_order == 'id_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('ID ASC'); ?></option>
						<option value="id_desc" <?php if ($dt_order == 'id_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('ID DESC'); ?></option>
						<option value="name_asc" <?php if ($dt_order == 'name_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Name'); ?> A-Z</option>
						<option value="name_desc" <?php if ($dt_order == 'name_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Name'); ?> Z-A</option>
					</select>
					<?php $lumise->securityFrom();?>
				</form>
			</div>
			<div class="right">
				<form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=products" method="post">
					<input type="search" name="search" class="search" placeholder="<?php echo $lumise->lang('Search ...'); ?>" value="<?php if(isset($_SESSION[$prefix.'data_search'])) echo $_SESSION[$prefix.'data_search']; ?>">
					<input  class="lumise_submit" type="submit" name="search_product" value="<?php echo $lumise->lang('Search'); ?>">
					<?php $lumise->securityFrom();?>

				</form>
			</div>
		</div>
		<?php if ( isset($products['total_count']) && $products['total_count'] > 0) { ?>
			<div class="lumise_wrap_table">
				<table class="lumise_table lumise_products">
					<thead>
						<tr>
							<th class="lumise_check">
								<div class="lumise_checkbox">
									<input type="checkbox" id="check_all">
									<label for="check_all"><em class="check"></em></label>
								</div>
							</th>
							<th><?php echo $lumise->lang('Name'); ?></th>
							<th><?php echo $lumise->lang('Description'); ?></th>
							<th><?php echo $lumise->lang('Stages'); ?></th>
							<th><?php echo $lumise->lang('Status'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php

							if ( is_array($products['rows']) && count($products['rows']) > 0 ) {

								foreach ($products['rows'] as $value) { ?>

									<tr>
										<td class="lumise_check">
											<div class="lumise_checkbox">
												<input type="checkbox" name="checked[]" class="action_check" value="<?php if(isset($value['id'])) echo $value['id']; ?>" class="action" id="<?php if(isset($value['id'])) echo $value['id']; ?>">
												<label for="<?php if(isset($value['id'])) echo $value['id']; ?>"><em class="check"></em></label>
											</div>
										</td>
										<td>
											<a href="<?php echo $lumise->cfg->admin_url;?>lumise-page=product&id=<?php if(isset($value['id'])) echo $value['id'] ?>" class="name"><?php echo isset($value['name']) ? $value['name'] : ''; ?></a>
											<a href="#" class="lumise_action_duplicate" data-id="<?php if(isset($value['id'])) echo $value['id'] ?>" data-table="<?php echo $table_name; ?>"><?php echo $lumise->lang('Duplicate'); ?></a>
										</td>
										<td><?php echo isset($value['description']) ? substr($value['description'], 0, 50) : ''; ?></td>
										<td><?php
											
											$color = '#f0f0f0';
											
											if (isset($value['attributes']) && !empty($value['attributes'])) {
												$attrs = $lumise_admin->dejson($value['attributes']);
												foreach ($attrs as $k => $attr) {
													if (is_string($attr->values))
														$attr->values = @json_decode($attr->values);
													if (
														$attr->type == 'product_color' && 
														isset($attr->values) && 
														is_object($attr->values) &&
														isset($attr->values->options) &&
														is_array($attr->values->options) &&
														count($attr->values->options) > 0
													) {
														$color = $attr->values->options[0]->value;
														foreach ($attr->values->options as $op) {
															if (isset($op->default) && $op->default === true)
																$color = $op->value;
														}
													}
												}
											}
											
											if (isset($value['stages']) && !empty($value['stages'])) {
												
												$data = $lumise_admin->dejson($value['stages']);
												
												if ($data !== null) {
													
													$stages = isset($data->stages) ? $data->stages : $data;
													
													if (isset($stages->colors)) {
														if (isset($stages->colors->active))
															$color = $stages->colors->active;
														unset($stages->colors);
													} else if (
														isset($data->options) && 
														isset($data->options->color)
													) {
														$color = $data->options->color;
													}
													
													$snames = array_keys((Array)$stages);
													$total = count($snames);
													$snames = array_splice($snames, 0, 4);
													echo '<ul>';
													foreach ($snames as $sname) {
														if (
															isset($stages->{$sname}->url) && 
															!empty($stages->{$sname}->url)
														) {
															echo '<li>';
															echo '<img style="background:'.$color.'" src="'.(
																	(
																		isset($stages->{$sname}->source) && 
																		$stages->{$sname}->source == 'raws'
																	) ? 
																	$lumise->cfg->assets_url.'raws/' : 
																	$lumise->cfg->upload_url
																).$stages->{$sname}->url.'" height="100" /></li>';
														}
													}
													if (count($snames) > $total) {
														echo '<li class="more-stages">';
														echo '<span>+'.($total-count($snames)).'</span>';
														echo '</li>';
													}
													echo '</ul>';
												}
											}
										?></td>
										<td>
											<a href="#" class="lumise_action" data-type="products" data-action="switch_active" data-status="<?php echo (isset($value['active']) ? $value['active'] : '0'); ?>" data-id="<?php if(isset($value['id'])) echo $value['id'] ?>">
												<?php
													if (isset($value['active'])) {
														if ($value['active'] == 1) {
															echo '<em class="pub">'.$lumise->lang('active').'</em>';
														} else {
															echo '<em class="un pub">'.$lumise->lang('deactive').'</em>';
														}
													}
												?>
											</a>
										</td>
									</tr>

								<?php }

							}

						?>
					</tbody>
				</table>
			</div>
			<div class="lumise_pagination"><?php echo $lumise_pagination->pagination_html(); ?></div>

		<?php } else {
					if (isset($total_record) && $total_record > 0) {
						echo '<p class="no-data">'.$lumise->lang('Apologies, but no results were found.').'</p>';
						$_SESSION[$prefix.'data_search'] = '';
						echo '<a href="'.$lumise->cfg->admin_url.'lumise-page=products" class="btn-back"><i class="fa fa-reply" aria-hidden="true"></i>'.$lumise->lang('Back To Lists').'</a>';
					}
					else
						echo '<p class="no-data">'.$lumise->lang('No data. Please add product.').'</p>';
			}?>

	</div>

</div>

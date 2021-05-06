<?php

	$title = "Cliparts list";
	$prefix = 'cliparts_';
	$currency = isset($lumise->cfg->settings['currency']) ? $lumise->cfg->settings['currency'] : '';

	// Action Form
	if (isset($_POST['action_submit']) && !empty($_POST['action_submit'])) {

		$data_action = isset($_POST['action']) ? $_POST['action'] : '';
		$val = isset($_POST['id_action']) ? $_POST['id_action'] : '';
		$val = explode(',', $val);
		
		$lumise_admin->check_caps('cliparts');
		
		foreach ($val as $value) {

			$dt = $lumise_admin->get_row_id($value, 'cliparts');
			switch ($data_action) {

				case 'active':
					$data = array(
						'active' => 1
					);
					$dt = $lumise_admin->edit_row( $value, $data, 'cliparts' );
					break;
				case 'deactive':
					$data = array(
						'active' => 0
					);
					$dt = $lumise_admin->edit_row( $value, $data, 'cliparts' );
					break;
				case 'featured':
					$data = array(
						'featured' => 1
					);
					$dt = $lumise_admin->edit_row( $value, $data, 'cliparts' );
					break;
				case 'unfeatured':
					$data = array(
						'featured' => 0
					);
					$dt = $lumise_admin->edit_row( $value, $data, 'cliparts' );
					break;
				case 'delete':

					$arr = array("id","item_id");
					$cate_reference = $lumise_admin->get_rows_custom($arr, 'categories_reference', $orderby = 'id', $order='asc');

					foreach ($cate_reference as $vals) {
						if ($vals['item_id'] == $value) {
							$lumise_admin->delete_row($vals['id'], 'categories_reference');
						}
					}

					$arr = array("id","item_id");
					$tag_reference = $lumise_admin->get_rows_custom($arr, 'tags_reference', $orderby = 'id', $order='asc');

					foreach ($tag_reference as $vals) {
						if ($vals['item_id'] == $value) {
							$lumise_admin->delete_row($vals['id'], 'tags_reference');
						}
					}

					$tar_file = realpath($lumise->cfg->upload_path).DS;
					if (!empty($dt['upload'])) {
						if (file_exists($tar_file.$dt['upload'])) {
							@unlink($tar_file.$dt['upload']);
							@unlink(str_replace(array($lumise->cfg->upload_url, '/'), array($tar_file, TS), $dt['thumbnail_url']));
						}
					}
					$lumise_admin->delete_row($value, 'cliparts');

					break;
				default:
					break;

			}

		}

	}

	// Search Form
	$data_search = '';
	if (isset($_POST['search_clipart']) && !empty($_POST['search_clipart'])) {

		$data_search = isset($_POST['search']) ? trim($_POST['search']) : '';

		if (empty($data_search)) {
			$errors = 'Please Insert Key Word';
			$_SESSION[$prefix.'data_search'] = '';
		} else {
			$_SESSION[$prefix.'data_search'] = 	$data_search;
		}

	}

	if (!empty($_SESSION[$prefix.'data_search'])) {
		$data_search = '%'.addslashes($_SESSION[$prefix.'data_search']).'%';
	}

	if (isset($_POST['categories'])) {
		$_SESSION[$prefix.'category'] = $_POST['categories'];
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
			$_SESSION[$prefix.'per_page'] = 20;
			$per_page = $_SESSION[$prefix.'per_page'];
		}

	}

    // Sort Form
	if (isset($_POST['sortby']) && !empty($_POST['sortby'])) {

		$dt_sort = isset($_POST['sort']) ? $_POST['sort'] : '';
		$_SESSION[$prefix.'dt_order'] = $dt_sort;

		switch ($dt_sort) {

			case 'name_asc':
				$_SESSION[$prefix.'orderby'] = 'art.name';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'name_desc':
				$_SESSION[$prefix.'orderby'] = 'art.name';
				$_SESSION[$prefix.'ordering'] = 'desc';
				break;
			case 'price_asc':
				$_SESSION[$prefix.'orderby'] = 'art.price';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'price_desc':
				$_SESSION[$prefix.'orderby'] = 'art.price';
				$_SESSION[$prefix.'ordering'] = 'desc';
				break;
			case 'created_asc':
				$_SESSION[$prefix.'orderby'] = 'art.created';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'created_desc':
				$_SESSION[$prefix.'orderby'] = 'art.created';
				$_SESSION[$prefix.'ordering'] = 'desc';
				break;
			case 'featured':
			case 'active':
			case 'deactive':
				$_SESSION[$prefix.'orderby'] = '';
				$_SESSION[$prefix.'ordering'] = '';
				break;
			default:
				break;

		}

	}

	if (isset($_POST['do']) && !empty($_POST['do'])) {
		$lumise->redirect($lumise->cfg->admin_url . "lumise-page=cliparts");
		exit;
	}

	$orderby  = (isset($_SESSION[$prefix.'orderby']) && !empty($_SESSION[$prefix.'orderby'])) ? $_SESSION[$prefix.'orderby'] : 'created';
	$ordering = (isset($_SESSION[$prefix.'ordering']) && !empty($_SESSION[$prefix.'ordering'])) ? $_SESSION[$prefix.'ordering'] : 'desc';
	$dt_order = isset($_SESSION[$prefix.'dt_order']) ? $_SESSION[$prefix.'dt_order'] : 'created_desc';
	$dt_category = isset($_SESSION[$prefix.'category']) ? $_SESSION[$prefix.'category'] : '';
	
	// Get row pagination
    $current_page = isset($_GET['tpage']) ? $_GET['tpage'] : 1;

    $where = array("`art`.`author`='{$lumise->vendor_id}'");

    if (!empty($data_search))
	    array_push($where, "(art.name LIKE '$data_search' OR art.tags LIKE '$data_search')");
    if (!empty($dt_category))
	    array_push($where, "cate.category_id = '$dt_category'");
	if ($dt_order == 'featured')
		array_push($where, "art.featured = '1'");
	else if ($dt_order == 'active')
		array_push($where, "art.active = '1'");
	else if ($dt_order == 'deactive')
		array_push($where, "art.active <> '1'");
	
    $select = "SELECT SQL_CALC_FOUND_ROWS art.* FROM {$lumise->db->prefix}cliparts art ";
	
    $query = array(
		($dt_category !== '') ? "LEFT JOIN {$lumise->db->prefix}categories_reference cate ON cate.item_id = art.id" : '',
		count($where) > 0 ? "WHERE ".implode(' AND ', $where) : "",
		"GROUP BY art.id"
    );

    $start = ( $current_page - 1 ) *  $per_page;
    array_push($query, "ORDER BY ".$orderby." ".$ordering);
	array_push($query, "LIMIT ".$start.",".$per_page);

	$arts = $lumise->db->rawQuery($select.implode(' ', $query));
	$total = $lumise->db->rawQuery("SELECT FOUND_ROWS() AS count");
        
    if (count($total) > 0 && isset($total[0]['count'])) {
		$total = $total[0]['count'];
	} else $total = 0;
	
	$config = array(
    	'current_page'  => $current_page,
		'total_record'  => $total,
		'total_page'    => ceil($total/$per_page),
 	    'limit'         => $per_page,
	    'link_full'     => $lumise->cfg->admin_url.'lumise-page=cliparts&tpage={page}',
	    'link_first'    => $lumise->cfg->admin_url.'lumise-page=cliparts',
	);

	$lumise_pagination->init($config);
	
	$can_upload = $lumise->caps('lumise_can_upload');
	
?>

<div class="lumise_wrapper">

	<div class="lumise_content">

		<div class="lumise_header">
			<h2><?php echo $lumise->lang('Cliparts'); ?></h2>
			<a href="<?php echo $lumise->cfg->admin_url;?>lumise-page=clipart" class="add-new lumise-button">
				<i class="fa fa-plus"></i>
				<?php echo $lumise->lang('Add new clipart'); ?>
			</a>
			<?php if ($can_upload) { ?>
			<a href="<?php echo $lumise->cfg->admin_url;?>lumise-page=clipart" class="add-new lumise-button" id="lumise-add-bundle-cliparts">
				<i class="fa fa-th"></i>
				<?php echo $lumise->lang('Add multiple Cliparts'); ?>
			</a>
			<?php } ?>
			<?php
				$lumise_page = isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '';
				echo $lumise_helper->breadcrumb($lumise_page);
			?>
		</div>

		<div class="lumise_option">
			<div class="left">
				<form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=cliparts" method="post">
					<select name="action" class="art_per_page">
						<option value="none"><?php echo $lumise->lang('Bulk Actions'); ?></option>
						<option value="active"><?php echo $lumise->lang('Active'); ?></option>
						<option value="deactive"><?php echo $lumise->lang('Deactive'); ?></option>
						<option value="featured"><?php echo $lumise->lang('Featured'); ?></option>
						<option value="unfeatured"><?php echo $lumise->lang('Unfeatured'); ?></option>
						<option value="delete"><?php echo $lumise->lang('Delete'); ?></option>
					</select>
					<input type="hidden" name="id_action" class="id_action">
					<input type="hidden" name="do" value="action" />
					<input type="submit" class="lumise_submit" name="action_submit" value="<?php echo $lumise->lang('Apply'); ?>" />
					<?php $lumise->securityFrom();?>
				</form>
				<form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=cliparts" method="post" class="less">
					<select name="per_page" data-action="submit" class="art_per_page">
						<option value="none">-- <?php echo $lumise->lang('Per page'); ?> --</option>
						<?php
							$per_pages = array('20', '50', '129', '200', '300');

							foreach($per_pages as $val) {

							    if($val == $per_page) {
							        echo '<option selected="selected">'.$val.'</option>';
							    } else {
							        echo '<option>'.$val.'</option>';
							    }

							}
						?>
					</select>
					<input type="hidden" name="perpage" value="<?php echo $lumise->lang('Per Page'); ?>" />
					<input type="hidden" name="do" value="limit" />
					<?php $lumise->securityFrom();?>
				</form>
				<form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=cliparts" method="post" class="less">
					<select name="sort" class="art_per_page" data-action="submit">
						<option value="created_desc">-- <?php echo $lumise->lang('Sort by'); ?> --</option>
						<option value="featured" <?php if ($dt_order == 'featured' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Featured only'); ?></option>
						<option value="active" <?php if ($dt_order == 'active' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Active only'); ?></option>
						<option value="deactive" <?php if ($dt_order == 'deactive' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Deactive only'); ?></option>
						<option value="name_asc" <?php if ($dt_order == 'name_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Name'); ?> A->Z</option>
						<option value="name_desc" <?php if ($dt_order == 'name_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Name'); ?> Z->A</option>
						<option value="created_asc" <?php if ($dt_order == 'created_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Created date'); ?> &uarr;</option>
						<option value="created_desc" <?php if ($dt_order == 'created_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Created date'); ?> &darr;</option>
					</select>
					<input type="hidden" name="sortby" value="<?php echo $lumise->lang('Sortby'); ?>">
					<input type="hidden" name="do" value="sort" />
					<?php $lumise->securityFrom();?>
				</form>
				<form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=cliparts" method="post" class="less">
					<select name="categories" class="art_per_page" data-action="submit" style="width:150px">
						<option value="">-- <?php echo $lumise->lang('Categories'); ?> --</option>
						<?php
							$cates = $lumise_admin->get_categories();
							foreach ($cates as $cate) {
								echo '<option '.($dt_category==$cate['id'] ? 'selected' : '').' value="'.$cate['id'].'">'.str_repeat('&mdash;', $cate['lv']).' '.$cate['name'].'</option>';
							}
						?>
					</select>
					<input type="hidden" name="do" value="categroies" />
					<?php $lumise->securityFrom();?>
				</form>
			</div>
			<div class="right">
				<form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=cliparts" method="post" class="less">
					<input type="search" name="search" class="search" placeholder="<?php echo $lumise->lang('Search ...'); ?>" value="<?php if(isset($_SESSION[$prefix.'data_search'])) echo $_SESSION[$prefix.'data_search']; ?>" style="margin:0px">
					<input type="hidden" name="search_clipart" value="<?php echo $lumise->lang('Search'); ?>">
					<?php $lumise->securityFrom();?>
				</form>
			</div>
		</div>

		<?php if (count($arts) > 0) { ?>

		<div class="lumise_wrap_table">
			<table class="lumise_table lumise_cliparts">
				<thead>
					<tr>
						<th class="lumise_check">
							<div class="lumise_checkbox">
								<input type="checkbox" id="check_all">
								<label for="check_all"><em class="check"></em></label>
							</div>
						</th>
						<th width="20%"><?php echo $lumise->lang('Name'); ?></th>
						<th><?php echo $lumise->lang('Price').' ('.$currency.')'; ?></th>
						<th><?php echo $lumise->lang('Categories'); ?></th>
						<th><?php echo $lumise->lang('Tags'); ?></th>
						<th><?php echo $lumise->lang('Thumbnail'); ?></th>
						<th><?php echo $lumise->lang('Featured'); ?></th>
						<th><?php echo $lumise->lang('Status'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php

						foreach ($arts as $art) { ?>

							<tr>
								<td class="lumise_check">
									<div class="lumise_checkbox">
										<input type="checkbox" name="checked[]" class="action_check" value="<?php if(isset($art['id'])) echo $art['id']; ?>" class="action" id="<?php if(isset($art['id'])) echo $art['id']; ?>">
										<label for="<?php if(isset($art['id'])) echo $art['id']; ?>"><em class="check"></em></label>
									</div>
								</td>
								<td class="lumise-resource-title">
									<a href="<?php echo $lumise->cfg->admin_url;?>lumise-page=clipart&id=<?php if(isset($art['id'])) echo $art['id'] ?>" class="name"><?php if(isset($art['name'])) echo $art['name']; ?></a>
									<span> - #<?php if(isset($art['id'])) echo $art['id'] ?></span>
								</td>
								<td style="position:relative;"><input type="number" class="lumise_set_price" data-type="cliparts" data-id="<?php if(isset($art['id'])) echo $art['id']; ?>" value="<?php if(isset($art['price'])) echo $art['price']; ?>"></td>
								<td>
									<?php
										$art['id'] = isset($art['id']) ? $art['id'] : '';
										$dt = $lumise_admin->get_category_item($art['id'], 'cliparts');
										$dt_name = array();

										foreach ($dt as $val) {
											$dt_name[] = $val['name'];
										}
										echo implode(', ', $dt_name);
									?>
								</td>
								<td style="width:20%; position:relative;">
									<?php
										$art['id'] = isset($art['id']) ? $art['id'] : '';
										$dt = $lumise_admin->get_tag_item($art['id'], 'cliparts');
										$dt_name = array();
										foreach ($dt as $val) {
											$dt_name[] = $val['name'];
										}
									?>
									<input name="tags" class="tagsfield" value="<?php echo implode(',', $dt_name); ?>" data-id="<?php echo $art['id']; ?>" data-type="cliparts">
								</td>
								<td>
									<?php
										if (isset($art['thumbnail_url']) && !empty($art['thumbnail_url'])) {
											echo '<img class="lumise-thumbn" src="'.$art['thumbnail_url'].'">';
										}
									?>
								</td>
								<td class="lumise_featured">
									<a href="#" class="lumise_action" data-type="cliparts" data-action="switch_feature" data-status="<?php echo (isset($art['featured']) ? $art['featured'] : '0'); ?>" data-id="<?php if(isset($art['id'])) echo $art['id'] ?>">
										<?php
											if (isset($art['featured']) && $art['featured'] == 1)
												echo '<i class="fa fa-star"></i>';
											else echo '<i class="none fa fa-star-o"></i>';
										?>
									</a>
								</td>
								<td>
									<a href="#" class="lumise_action" data-type="cliparts" data-action="switch_active" data-status="<?php echo (isset($art['active']) ? $art['active'] : '0'); ?>" data-id="<?php if(isset($art['id'])) echo $art['id'] ?>">
										<?php
											if (isset($art['active'])) {
												if ($art['active'] == 1) {
													echo '<em class="pub">'.$lumise->lang('active').'</em>';
												} else {
													echo '<em class="un pub">'.$lumise->lang('deactive').'</em>';
												}
											}
										?>
									</a>
								</td>
							</tr>

						<?php } ?>
				</tbody>
			</table>
		</div>
		<div class="lumise_pagination"><?php echo $lumise_pagination->pagination_html(); ?></div>

		<?php } else {
					if (isset($total_record[0]['total']) && $total_record[0]['total'] > 0) {
						echo '<p class="no-data">'.$lumise->lang('Apologies, but no results were found.').'</p>';
						$_SESSION[$prefix.'data_search'] = '';
						echo '<a href="'.$lumise->cfg->admin_url.'lumise-page=cliparts" class="btn-back"><i class="fa fa-reply" aria-hidden="true"></i>'.$lumise->lang('Back To Lists').'</a>';
					}
					else echo '<p class="no-data">'.$lumise->lang('No data. Please add clipart.').'</p>';
			}?>

	</div>

</div>

<?php if ($can_upload) { ?>
<div id="lumise-popup">
	<div class="lumise-popup-content lumise-multi-cliparts">
		<header>
			<h3><?php echo $lumise->lang('Add bundle multiple Cliparts'); ?></h3>
			<span class="close-pop" data-close><svg enable-background="new 0 0 32 32" height="32px" id="close" version="1.1" viewBox="0 0 32 32" width="32px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M17.459,16.014l8.239-8.194c0.395-0.391,0.395-1.024,0-1.414c-0.394-0.391-1.034-0.391-1.428,0  l-8.232,8.187L7.73,6.284c-0.394-0.395-1.034-0.395-1.428,0c-0.394,0.396-0.394,1.037,0,1.432l8.302,8.303l-8.332,8.286  c-0.394,0.391-0.394,1.024,0,1.414c0.394,0.391,1.034,0.391,1.428,0l8.325-8.279l8.275,8.276c0.394,0.395,1.034,0.395,1.428,0  c0.394-0.396,0.394-1.037,0-1.432L17.459,16.014z" fill="#121313" id="Close"></path><g></g><g></g><g></g><g></g><g></g><g></g></svg></span>
		</header>
		<div class="lumise-langs-wrp lumise_content">
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Set Categories'); ?></span>
				<div class="lumise_form_content">
					<ul class="list-cate" id="lumise-list-categories"></ul>
					<div id="create-category-form" style="display: none;">
						<div class="lumise_form_group">
							<span><?php echo $lumise->lang('Category thumbnail'); ?></span>
							<div class="lumise_form_content img-preview">
								<img src="<?php echo $lumise->cfg->assets_url; ?>assets/images/img-none.png" class="img-upload" id="lumise-category-preview">
								<input type="file" accept="image/png,image/gif,image/jpeg,image/svg+xml" id="file_upload" data-file-select="true" data-file-preview="#lumise-category-preview" data-file-input="#lumise-category-upload" data-file-thumbn-width="320">
								<input type="hidden" name="category[upload]" id="lumise-category-upload" />
								<label for="file_upload"><?php echo $lumise->lang('Choose a file'); ?></label>
								<button data-btn="true" data-file-delete="true"  data-file-preview="#lumise-category-preview" data-file-input="#lumise-category-upload"><?php echo $lumise->lang('Remove file'); ?></button>
							</div>
						</div>
						<div class="lumise_form_group">
							<span><?php echo $lumise->lang('Category name'); ?></span>
							<div class="lumise_form_content">
								<input type="text" name="category[name]" />
							</div>
						</div>
						<div class="lumise_form_group">
							<span><?php echo $lumise->lang('Parent category'); ?></span>
							<div class="lumise_form_content">
								<select name="category[parent]" id="lumise-parent-categories"></select>
							</div>
						</div>
						<footer>
							<button class="lumise-btn-primary"><?php echo $lumise->lang('Create new category'); ?></button>
							<button data-btn data-click="toggle-form"><?php echo $lumise->lang('Cancel'); ?></button>
						</footer>
					</div>
					<a href="<?php echo $lumise->cfg->admin_url;?>lumise-page=categories&type=cliparts" target=_blank class="add_cate" data-click="toggle-form">
						<i class="fa fa-plus"></i>
						<?php echo $lumise->lang('Create new category'); ?>
					</a>
				</div>
			</div>
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Set Tags'); ?></span>
				<div class="lumise_form_content">
					<input type="text" id="lumise-cliparts-tags" name="tags" placeholder="" value="<?php echo !empty($data['tags']) ? $data['tags'] : '' ?>" />
					<em class="notice"><?php echo $lumise->lang('Example: tag1, tag2, tag3 ...'); ?></em>
				</div>
			</div>
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Set Price'); ?></span>
				<div class="lumise_form_content">
					<input type="text" id="lumise-cliparts-price" name="price" value="<?php echo !empty($data['price']) ? $data['price'] : '' ?>" />
				</div>
			</div>
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Featured'); ?></span>
				<div class="lumise_form_content">
					<div class="lumise-toggle">
						<input type="checkbox" name="category[featured]" id="lumise-cliparts-featured">
						<span class="lumise-toggle-label less" data-on="Yes" data-off="No"></span>
						<span class="lumise-toggle-handle less"></span>
					</div>
				</div>
			</div>
			<div class="lumise_form_group">
				<span><?php echo $lumise->lang('Upload Cliparts'); ?></span>
				<div class="lumise_form_group">
					<h3 id="lumise-cliparts-bundle-stt"><?php echo $lumise->lang('Processed '); ?><span>0/0</span></h3>
					<div id="lumise-upload-form">
						<i class="fa fa-cloud-upload"></i>
						<span><?php echo $lumise->lang('Click or drop images here'); ?></span>
						<input type="file" multiple="true" accept="image/png,image/jpeg,image/svg+xml" />
					</div>
					<em class="notice"><?php echo $lumise->lang('Supported files svg, png, jpg, jpeg. Max size 5MB'); ?></em>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>

<script type="text/javascript">
var nonce = "<?php echo lumise_secure::create_nonce('LUMISE_ADMIN_cliparts') ?>",
	reader = [],
	total = 0,
	done = 0;
<?php

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

			if ($value['type'] == 'cliparts')
				$values[] = $value['name'];

		}
		echo 'var lumise_sampleTags = ', js_array($values), ';';
	} else {
		echo 'var lumise_sampleTags = "";';
	}
?>
</script>
<script src="<?php echo $lumise->cfg->admin_assets_url;?>js/cliparts.js"></script>

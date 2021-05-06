<?php
	
    global $lumise;
    
	$prefix = 'orders';
    
    $lumise->do_action('before_orders');

    // Search Form
	$data_search = '';
	if (isset($_POST['search_orders']) && !empty($_POST['search_orders'])) {
		
		$data_search = isset($_POST['search']) ? trim($_POST['search']) : '';

		if (empty($data_search)) {
			$errors = 'Search Order Id';
			$_SESSION[$prefix.'data_search'] = '';
		} else {
			$_SESSION[$prefix.'data_search'] = 	$data_search;
		}

	}

	if (!empty($_SESSION[$prefix.'data_search'])) {
		$data_search = '%'.$_SESSION[$prefix.'data_search'].'%';
	}
    

    // Pagination
	$per_page = 10;
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
	if (isset($_REQUEST['sort'])) {

		$dt_sort = isset($_POST['sort']) ? $_POST['sort'] : '';
		$_SESSION[$prefix.'dt_order'] = $dt_sort;
		
		switch ($dt_sort) {

			case 'order_id_asc':
				$_SESSION[$prefix.'orderby'] = 'order_id';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'order_id_desc':
				$_SESSION[$prefix.'orderby'] = 'order_id';
				$_SESSION[$prefix.'ordering'] = 'desc';
				break;
			case 'total_asc':
				$_SESSION[$prefix.'orderby'] = 'total';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'total_desc':
				$_SESSION[$prefix.'orderby'] = 'total';
				$_SESSION[$prefix.'ordering'] = 'desc';
				break;
            case 'created_asc':
				$_SESSION[$prefix.'orderby'] = 'os.created';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'created_desc':
				$_SESSION[$prefix.'orderby'] = 'os.created';
				$_SESSION[$prefix.'ordering'] = 'desc';
				break;
            case 'updated_asc':
				$_SESSION[$prefix.'orderby'] = 'os.updated';
				$_SESSION[$prefix.'ordering'] = 'asc';
				break;
			case 'updated_desc':
				$_SESSION[$prefix.'orderby'] = 'os.updated';
				$_SESSION[$prefix.'ordering'] = 'desc';
				break;
			default:
				break;

		}
        
	}


	$orderby  = (isset($_SESSION[$prefix.'orderby']) && !empty($_SESSION[$prefix.'orderby'])) ? $_SESSION[$prefix.'orderby'] : 'order_id';
	$ordering = (isset($_SESSION[$prefix.'ordering']) && !empty($_SESSION[$prefix.'ordering'])) ? $_SESSION[$prefix.'ordering'] : 'desc';
	$dt_order = isset($_SESSION[$prefix.'dt_order']) ? $_SESSION[$prefix.'dt_order'] : 'order_id_desc';

    $current_page = isset($_GET['tpage']) ? $_GET['tpage'] : 1;
    $search_filter = array(
        'keyword' => $data_search,
        'fields' => 'order_id,status'
    );
    
    $start = ( $current_page - 1 ) *  $per_page;
    $items = $lumise->connector->orders($search_filter, $orderby, $ordering, $per_page, $start);

    $config = array(
    	'current_page'  => $current_page,
		'total_record'  => $items['total_count'],
		'total_page'    => $items['total_page'],
 	    'limit'         => $per_page,
	    'link_full'     => $lumise->cfg->admin_url.'lumise-page=orders&tpage={page}',
	    'link_first'    => $lumise->cfg->admin_url.'lumise-page=orders',
	);

	$lumise_pagination->init($config);
	
?><div class="lumise_wrapper">
	
	<div class="lumise_content">

		<div class="lumise_header">
			<h2><?php echo $lumise->lang('Orders'); ?></h2>
			<?php
				$lumise_page = isset($_GET['lumise-page']) ? $_GET['lumise-page'] : '';
				echo $lumise_helper->breadcrumb($lumise_page);
                
			?>
		</div>
        <?php $lumise->views->header_message();?>
        <div class="lumise_option">
            <div class="left">
                <form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=orders" method="post">
                    <select name="per_page" class="orders_per_page" data-action="submit">
                    	<option value="none">-- <?php echo $lumise->lang('Per page'); ?> --</option>
                        <?php
                            $per_pages = array('10', '15', '20', '100');

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
                <form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=orders" method="post">
                    <select name="sort" class="orders_per_page" data-action="submit">
                    	<option value="">-- <?php echo $lumise->lang('Sort by'); ?> --</option>
                        <option value="order_id_asc" <?php if ($dt_order == 'order_id_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Order Id'); ?> &uarr;</option>
                        <option value="order_id_desc" <?php if ($dt_order == 'order_id_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Order Id'); ?> &darr;</option>
                        <option value="total_asc" <?php if ($dt_order == 'total_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Price'); ?> &uarr;</option>
                        <option value="total_desc" <?php if ($dt_order == 'total_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Price'); ?> &darr;</option>
                        <option value="created_asc" <?php if ($dt_order == 'created_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Created At'); ?> &uarr;</option>
                        <option value="created_desc" <?php if ($dt_order == 'created_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Created At'); ?> &darr;</option>
                        <option value="updated_asc" <?php if ($dt_order == 'updated_asc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Updated At'); ?> &uarr;</option>
                        <option value="updated_desc" <?php if ($dt_order == 'updated_desc' ) echo 'selected' ; ?> ><?php echo $lumise->lang('Updated At'); ?> &darr;</option>
                    </select>
                    <?php $lumise->securityFrom();?>
                    <input type="hidden" name="do" value="action"/>
                </form>
            </div>
            <div class="right">
                <form action="<?php echo $lumise->cfg->admin_url;?>lumise-page=orders" method="post">
                    <input type="search" name="search" class="search" placeholder="<?php echo $lumise->lang('Search (ID or Status)'); ?>" value="<?php if(isset($_SESSION[$prefix.'data_search'])) echo $_SESSION[$prefix.'data_search']; ?>">
                    <input  class="lumise_submit" type="submit" name="search_orders" value="<?php echo $lumise->lang('Search'); ?>">
                    <?php $lumise->securityFrom();?>

                </form>
            </div>
        </div>
        
        <div class="lumise_wrap_table">
			<table class="lumise_table lumise_orders">
				<thead>
					<tr>
						<th width="20%"><?php echo $lumise->lang('Order Id'); ?></th>
						<th><?php echo $lumise->lang('Products'); ?></th>
						
                        <th width="10%"><?php echo $lumise->lang('Status'); ?></th>
                        <th width="10%"><?php echo $lumise->lang('Updated'); ?></th>
                        <th width="10%"><?php echo $lumise->lang('Total Price'); ?></th>
                        <?php if($lumise->connector->platform == 'php'):?>
						<th width="10%"><?php echo $lumise->lang('Actions'); ?></th>
                        <?php endif;?>
					</tr>
				</thead>
				<tbody>
	                <?php
	                
	                if(count($items['rows']) > 0){
	                    foreach($items['rows'] as $order):
	                ?>
	                <tr>
						<td><a href="<?php echo $lumise->cfg->admin_url;?>lumise-page=order&order_id=<?php echo $order['order_id'];?>"><?php printf($lumise->lang('Order #%s'), $order['order_id']);?></a></td>
						<td><?php 
                        $products = $lumise->lib->get_order_products($order['order_id']);
                        if(count($products)>0){
                            $phtml = array();
                            foreach($products as $product){
                                $phtml[] = $product['product_name'] .' x '.$product['qty'];
                            }
                            echo implode(', ', $phtml);
                        }
                        ?></td>
						<td>
							<?php 
								$class = '';
								if (strtolower($order['status']) == 'pending')
									$class = 'pen';
								if (strtolower($order['status']) == 'cancel')
									$class = 'un';
							?>
							<em class="<?php if(isset($class)) echo $class; ?> pub"><?php echo $lumise->apply_filters('order_status', $order['status']);?></em>
						</td>
                        
						
						<td><?php echo date('Y/m/d', strtotime($order['updated']));?></td>
                        <td><?php echo $lumise->lib->price($order['total']);?></td>
                        <?php if($lumise->connector->platform == 'php'):?>
	                    <td><a href="#" class="lumise-item-action" data-item="<?php echo $order['order_id'];?>" data-func="delete"><?php echo $lumise->lang('Delete'); ?></a></td>
                        <?php endif;?>
					</tr>
	                    <?php
	                    endforeach;
	                }
	                else {
	                ?>
	                <tr>
	                    <td colspan="6">
	                        <p class="no-data"><?php echo $lumise->lang('Apologies, but no results were found'); ?></p>
	                    </td>
	                </tr>
	                    
	                    
	                <?php
	                }
	                ?>
				</tbody>
			</table>
        </div>
		<div class="lumise_pagination"><?php echo $lumise_pagination->pagination_html(); ?></div>
		
	</div>

</div>

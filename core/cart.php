<?php
	
@set_time_limit(0);
@ini_set('memory_limit','5000M');

/**
 *
 */
class lumise_cart extends lumise_lib
{
	
    protected $action 		= '';
    protected $attributes 	= '';
    protected $data 		= array();

    function __construct(){
      	
      	/*
	    *	Process data from uploaded file before
	    */
	    global $lumise;
	    $this->main = $lumise;
	    $this->action 	= $this->main->lib->esc('action');
	   
	    $this->process_checkout();
        
    }
    
    public function process_checkout() {

		global $lumise;
        
        $items_cart 	= array();
        $upload_fields 	= array();
        $price_rule 	= array();
        $resources 		= array();
        
        //if not POST method, just return
		if( $_SERVER['REQUEST_METHOD'] !='POST' ) 
			return;
        
        //check none
		$nonce = isset( $_POST['nonce'] ) ? explode( ":", htmlspecialchars($_POST['nonce'])) : array('', '');

		if ( !lumise_secure::check_nonce($nonce[0], $nonce[1]) ){
			header('HTTP/1.0 403 Forbidden');
			exit;
		}
		
        //loop through all cart items
        foreach ($_FILES as $cart_id => $file) {
        	
        	$item = @file_get_contents($file['tmp_name']);
        	
        	if (empty($item) || $item === null)
        		continue;
        	
        	$item = json_decode($item);
        	
        	if (
        		!is_object($item) || 
        		!isset($item->product_id) || 
        		!isset($item->design) || 
        		!is_object($item->design)
        	) continue; 
        	
			$current_product = $this->get_product($item->product_id);

			// null 
			if ( $current_product === null && strpos($item->product_id, 'variable') === false ){
				continue; 
			}

			// simple product checkout process
			if($current_product !== null && strpos($item->product_id, 'variable') === false){
				$attributes         = $this->dejson(isset($current_product['attributes']) ? $current_product['attributes'] : '{}');
				$variations			= $this->dejson(isset($current_product['variations']) ? $current_product['variations'] : '{}');
				$product_stages		= $this->dejson(isset($current_product['stages']) ? $current_product['stages'] : '{}');
	            $template_price     = 0;
	            $qty                = 1;
	            
	            $attr_price			= 0;
	            $fixed_price_qtys	= 0;
	            
	            //loop through values of attribute
	            foreach ( $item->options as $aid => $val ) {
		            
	                $attributes->{$aid}->value = $val;
	             
	                if (
	                	
	                	/* Calc price for quantity */
	                	isset($attributes->{$aid}) &&
	                	is_object($attributes->{$aid}) &&
	                	$attributes->{$aid}->type == 'quantity'
	                ) {
		                
	                    $tol = 0;
	                    $_val = @json_decode($val);
	                    
	                    if (is_object($_val)) {
		                    foreach ($_val as $k => $v) {
			                    if (is_numeric((Int)$v))
				               		$tol += (Int)$v;
			               	}
	                    } else if (is_numeric((Int)$val))
	                    	$tol = (Int)$val;
		               	
		               	if (is_numeric($tol) && $tol > 0)
	                    	$qty = $tol;
	                    
	                    if (
		                    isset($attributes->{$aid}) &&
		                    is_object($attributes->{$aid}) &&
	                    	isset($attributes->{$aid}->values) &&
	                    	is_object($attributes->{$aid}->values)
	                    ) {
		                	
		                	if (
		                		$attributes->{$aid}->values->type == 'package' &&
		                		is_object($attributes->{$aid}->values->package_options)
		                	) {
			                	foreach ($attributes->{$aid}->values->package_options as $op) {
				                	if (
				                		$op->value == $val && 
				                		!empty($op->price) &&
				                		is_numeric((Float)$op->price)
				                	) {
					                	$attr_price += (Float)$op->price;
				                	}
			                	}
		                	} 
		                	
		                	if (
		                		$attributes->{$aid}->values->type == 'multiple' &&
		                		is_array($attributes->{$aid}->values->multiple_options) &&
		                		is_object($_val)
		                	) {
			                	foreach ($attributes->{$aid}->values->multiple_options as $op) {
				                	if (
				                		isset($_val->{$op->value}) &&
				                		!empty($op->price) &&
				                		is_numeric((Float)$op->price) &&
				                		is_numeric((Int)$_val->{$op->value})
				                	) {
					                	$fixed_price_qtys += ((Int)$_val->{$op->value}) * ((Float)$op->price);
				                	}
			                	}
			                }
		                	
	                    }	
	                    
					} else if ( 
						
						/* Calc price for attributes */
						
						is_object($attributes->{$aid}->values) &&
						isset($attributes->{$aid}->values->options) &&
						is_array($attributes->{$aid}->values->options)
					){
						$_val = explode("\n", $val);
						foreach ($attributes->{$aid}->values->options as $op) {
							if (
								in_array($op->value, $_val) &&
								!empty($op->price) &&
				                is_numeric((Float)$op->price)
							) $attr_price += (Float)$op->price;
						}
					}
					
	            }
	            
	            //get screenshots & resource
	            //loop through each stage to count resource again and getting screenshot for each stage
	            $stages 		= (array) $item->design->stages;
	            $screenshorts 	= array();
	            $resource 		= array();
				
				foreach ( $stages as $s => $stage ) {
	                
	                if (!isset($stage->screenshot) || empty($stage->screenshot)) {
		                $stage->screenshot = 'data:image/'.(
		                	strpos($stage->image, '.png') !== false ? 'png' : 'jpg'
		                ).';base64,'.base64_encode(@file_get_contents($stage->image));
	                }
	                
					$screenshorts[$s] = $stage->screenshot;
	                $sdata            = isset($stage->data) ? $stage->data : new stdClass();
	                $objects          = isset($sdata->objects) ? (array) $sdata->objects : array();
	                
	                foreach ( $objects as $obj ){
	                    if( 
	                        isset($obj->evented ) 
	                        && $obj->evented
	                         && isset( $obj->resource_id )
	                    ){
	                        // if(
		                       //  !isset($obj->template) && !isset($obj->template[0]) 
		                       //  && (
	                        //     	( isset( $obj->type ) && !in_array( $obj->type, ['i-text', 'image'] ) ) || 
	                        //     	( isset( $obj->resource ) && isset( $obj->resource_id ) && $obj->resource == 'cliparts' )
	                        //     )
	                        // ){
		                        
	                        //     $resource[] = array(
	                        //         'type'  => 'clipart',
	                        //         'id'    => (Int)$obj->resource_id
	                        //     );
	                        // }

	                        if(
		                        !isset($obj->template) 
		                        && !isset($obj->template[0]) 
		                        && isset($obj->resource) 
		                        && isset($obj->resource_id) 
	                        ){
	                            $resource[] = array(
	                                'type'  => $obj->resource,
	                                'id'    => (Int)$obj->resource_id
	                            );
	                        }


	                    }
	                }
				}

	            $resources = array_merge( $resources, $resource );
	            
	            //template price
	            if (isset($item->template)) {
	                foreach ( $item->template->stages as $stage => $temp_ids ) {
		                
		                if (!is_array($temp_ids))
		                    $temp_ids = array($temp_ids);
		                    
	                    if(
	                        count($temp_ids) > 0
	                    ){
			                foreach ($temp_ids as $tem_id) {
				                if (!empty($tem_id)) {
		                    		$template = $this->get_template( $tem_id );
									$template_price += ( $template['price'] > 0 ) ? $template['price'] : 0;
									// filter template price
									$template_price = $lumise->apply_filters('cart-process-template-price', $template_price, $item->cms_id);
								}
		                    }
	                    }
	                        
	                }
	            }
	            
	            $base_price = $lumise->apply_filters(
	                'product_base_price',
	                $current_product['price'], 
	                ($lumise->connector->platform == 'php') ? $item->product_id : $item->cms_id
	            );
	            
				if (is_array($base_price))
					$base_price = array_sum($base_price);
				
				// Add price of attributes to base
				
				$base_price += $attr_price;
					
				// Regular price if there is a variation
				
				if (
					isset($variations) &&
					isset($variations->variations)
				) {
					
					$vari_price = null;
					
					foreach ($variations->variations as $vid => $vari) {
						
						if (
							$vari_price === null && 
							is_object($vari->conditions) && 
							!empty($vari->price)
						) {
							
							$valid = true;
							
							foreach ($vari->conditions as $cid => $cond) {
								if (
									!empty($cond) && 
									(
										!is_object($item->options) ||
										!isset($item->options->{$cid}) || 
										$cond != $item->options->{$cid}
									)
								) {
									$valid = false;
								}
							}
							
							if ($valid === true) {
									
								$vari_price = (Float)$vari->price;
								
								$item->variation = $vid;
								
								if (
									isset($vari->minqty) && 
									!empty($vari->minqty) && 
									$qty < (Float)$vari->minqty
								)
									$qty = (Float)$vari->minqty;
									
								if (
									isset($vari->maxqty) && 
									!empty($vari->maxqty) && 
									$qty > (Float)$vari->maxqty
								)
									$qty = (Float)$vari->maxqty;
									
							}
						}
					}
					
					if ($vari_price !== null && is_numeric($vari_price)) {
						$base_price = $vari_price+$attr_price;
					}
				}
				
	            $extra_filters = $lumise->apply_filters(
	                'product_extra_price',
	                array(),
	                $item
	            );
				
				$extra_filter_price = 0;
				
				if ( is_array($extra_filters) && count($extra_filters) > 0 ) {
					$extra_filter_price = array_sum($extra_filters);
				}
				
				//extra resource from addons
				$extra_price_addons = 0;
				$extra = (array) $item->extra;
				$extra = array_values($extra);
				
				foreach( $extra as $ext ) {
					foreach ( $ext as $ep ) {
						//find resource. If exists, add resource price to global price
						$res = $this->find_resource($ep);
						$extra_price_addons += ($res === false)? 0: floatval($res['price']);
					}
				}

				$items_cart[ $cart_id ] = array(
	                'id'            => $item->product_id,
	                'cart_id'       => $cart_id,
	                'data'          => $item,
	                'qty'           => $qty,
	                'product_id'    => $item->product_id,
	                'product_cms'   => ( $lumise->connector->platform == 'php' )? $item->product_id : $item->cms_id,
	                'product_name'  => $item->product_name,
	                'price' => array(
	                    'total'     => 0,
	                    'fixed'     => $fixed_price_qtys,
	                    'resource'  => 0,
	                    'template'  => $template_price,
	                    'base'      => $base_price + $extra_filter_price + $extra_price_addons
	                ),
	                'options'    	=> $item->options,
	                'variation'    	=> $item->variation,
	                'attributes'    => $attributes,
	                'printing'      => $item->printing,
	                'resource'      => $resource,
	                'design'        => $item->design,
	                'template'      => false,
	                'screenshots'   => $screenshorts
	            );

	            $items_cart[ $cart_id ] = $lumise->apply_filters( 'items-cart-temp', $items_cart[ $cart_id ], array('attributes' => $attributes, 'variations' => $variations, 'product_stages' => $product_stages));
	            
	            unset($item);
			}

			// variation product checkout process
			if(isset($item->product_id) && strpos($item->product_id, 'variable') !== false){
				$attributes         = isset($item->attributes) ? $item->attributes : $this->dejson('{}');
				$variations			= isset($item->variations) ? $item->variations : $this->dejson('{}');
				$product_stages		= isset($item->stages) ? $item->stages : $this->dejson('{}');
	            $template_price     = 0;
	            $qty                = 1;
	            
	            $attr_price			= 0;
	            $fixed_price_qtys	= 0;

	            //loop through values of attribute
	            foreach ( $item->options as $aid => $val ) {
	                $attributes->{$aid}->value = $val;
	             
	                if (
	                	
	                	/* Calc price for quantity */
	                	isset($attributes->{$aid}) &&
	                	is_object($attributes->{$aid}) &&
	                	$attributes->{$aid}->type == 'quantity'
	                ) {
		                
	                    $tol = 0;
	                    $_val = @json_decode($val);
	                    
	                    if (is_object($_val)) {
		                    foreach ($_val as $k => $v) {
			                    if (is_numeric((Int)$v))
				               		$tol += (Int)$v;
			               	}
	                    } else if (is_numeric((Int)$val))
	                    	$tol = (Int)$val;
		               	
		               	if (is_numeric($tol) && $tol > 0)
	                    	$qty = $tol;
	                    
	                    if (
		                    isset($attributes->{$aid}) &&
		                    is_object($attributes->{$aid}) &&
	                    	isset($attributes->{$aid}->values) &&
	                    	is_object($attributes->{$aid}->values)
	                    ) {
		                	
		                	if (
		                		$attributes->{$aid}->values->type == 'package' &&
		                		is_object($attributes->{$aid}->values->package_options)
		                	) {
			                	foreach ($attributes->{$aid}->values->package_options as $op) {
				                	if (
				                		$op->value == $val && 
				                		!empty($op->price) &&
				                		is_numeric((Float)$op->price)
				                	) {
					                	$attr_price += (Float)$op->price;
				                	}
			                	}
		                	} 
		                	
		                	if (
		                		$attributes->{$aid}->values->type == 'multiple' &&
		                		is_array($attributes->{$aid}->values->multiple_options) &&
		                		is_object($_val)
		                	) {
			                	foreach ($attributes->{$aid}->values->multiple_options as $op) {
				                	if (
				                		isset($_val->{$op->value}) &&
				                		!empty($op->price) &&
				                		is_numeric((Float)$op->price) &&
				                		is_numeric((Int)$_val->{$op->value})
				                	) {
					                	$fixed_price_qtys += ((Int)$_val->{$op->value}) * ((Float)$op->price);
				                	}
			                	}
			                }
		                	
	                    }	
	                    
					} else if ( 
						
						/* Calc price for attributes */
						
						is_object($attributes->{$aid}->values) &&
						isset($attributes->{$aid}->values->options) &&
						is_array($attributes->{$aid}->values->options)
					){
						$_val = explode("\n", $val);
						foreach ($attributes->{$aid}->values->options as $op) {
							if (
								in_array($op->value, $_val) &&
								!empty($op->price) &&
				                is_numeric((Float)$op->price)
							) $attr_price += (Float)$op->price;
						}
					}
					
	            }
	            
	            //get screenshots & resource
	            //loop through each stage to count resource again and getting screenshot for each stage
	            $stages 		= (array) $item->design->stages;
	            $screenshorts 	= array();
	            $resource 		= array();
				
				foreach ( $stages as $s => $stage ) {
	                
	                if (!isset($stage->screenshot) || empty($stage->screenshot)) {
		                $stage->screenshot = 'data:image/'.(
		                	strpos($stage->image, '.png') !== false ? 'png' : 'jpg'
		                ).';base64,'.base64_encode(@file_get_contents($stage->image));
	                }
	                
					$screenshorts[$s] = $stage->screenshot;
	                $sdata            = isset($stage->data) ? $stage->data : new stdClass();
	                $objects          = isset($sdata->objects) ? (array) $sdata->objects : array();
	                
	                foreach ( $objects as $obj ){
	                    if( 
	                        isset($obj->evented ) 
	                        && $obj->evented
	                         && isset( $obj->resource_id )
	                    ){
	                        // if(
		                       //  !isset($obj->template) && !isset($obj->template[0]) 
		                       //  && (
	                        //     	( isset( $obj->type ) && !in_array( $obj->type, ['i-text', 'image'] ) ) || 
	                        //     	( isset( $obj->resource ) && isset( $obj->resource_id ) && $obj->resource == 'cliparts' )
	                        //     )
	                        // ){
		                        
	                        //     $resource[] = array(
	                        //         'type'  => 'clipart',
	                        //         'id'    => (Int)$obj->resource_id
	                        //     );
	                        // }

	                        if(
		                        !isset($obj->template) 
		                        && !isset($obj->template[0]) 
		                        && isset($obj->resource) 
		                        && isset($obj->resource_id) 
	                        ){
	                            $resource[] = array(
	                                'type'  => $obj->resource,
	                                'id'    => (Int)$obj->resource_id
	                            );
	                        }


	                    }
	                }
				}

	            $resources = array_merge( $resources, $resource );
	            
	            //template price
	            if (isset($item->template)) {
	                foreach ( $item->template->stages as $stage => $temp_ids ) {
		                
		                if (!is_array($temp_ids))
		                    $temp_ids = array($temp_ids);
		                    
	                    if(
	                        count($temp_ids) > 0
	                    ){
			                foreach ($temp_ids as $tem_id) {
				                if (!empty($tem_id)) {
		                    		$template = $this->get_template( $tem_id );
									$template_price += ( $template['price'] > 0 ) ? $template['price'] : 0;

									// filter template price
									$template_price = $lumise->apply_filters('cart-process-template-price', $template_price, $item->cms_id);
								}
		                    }
	                    }
	                        
	                }
	            }
	            
	            $priceTemp = isset($item->price) ? $item->price : 0;
	            $base_price = $lumise->apply_filters(
	                'product_base_price',
	                $priceTemp, 
	                ($lumise->connector->platform == 'php') ? $item->product_id : $item->cms_id
	            );

	            if( $lumise->connector->platform == 'woocommerce'){
            		$product_id = intval(preg_replace('/[^0-9]+/mi', '', $item->product_id));
					$product = wc_get_product( $product_id );
					$base_price = floatval($product->get_price());
	            }
	            
				if (is_array($base_price)){
					$base_price = array_sum($base_price);
				}
				
				// Add price of attributes to base
				
				$base_price += $attr_price;
					
				// Regular price if there is a variation
				
				if (
					isset($variations) &&
					isset($variations->variations)
				) {
					
					$vari_price = null;
					
					foreach ($variations->variations as $vid => $vari) {
						
						if (
							$vari_price === null && 
							is_object($vari->conditions) && 
							!empty($vari->price)
						) {
							
							$valid = true;
							
							foreach ($vari->conditions as $cid => $cond) {
								if (
									!empty($cond) && 
									(
										!is_object($item->options) ||
										!isset($item->options->{$cid}) || 
										$cond != $item->options->{$cid}
									)
								) {
									$valid = false;
								}
							}
							
							if ($valid === true) {
									
								$vari_price = (Float)$vari->price;
								
								$item->variation = $vid;
								
								if (
									isset($vari->minqty) && 
									!empty($vari->minqty) && 
									$qty < (Float)$vari->minqty
								)
									$qty = (Float)$vari->minqty;
									
								if (
									isset($vari->maxqty) && 
									!empty($vari->maxqty) && 
									$qty > (Float)$vari->maxqty
								)
									$qty = (Float)$vari->maxqty;
									
							}
						}
					}
					
					if ($vari_price !== null && is_numeric($vari_price)) {
						$base_price = $vari_price+$attr_price;
					}
				}
				
	            $extra_filters = $lumise->apply_filters(
	                'product_extra_price',
	                array(),
	                $item
	            );
				
				$extra_filter_price = 0;
				
				if ( is_array($extra_filters) && count($extra_filters) > 0 ) {
					$extra_filter_price = array_sum($extra_filters);
				}
				
				//extra resource from addons
				$extra_price_addons = 0;
				$extra = (array) $item->extra;
				$extra = array_values($extra);
				
				foreach( $extra as $ext ) {
					foreach ( $ext as $ep ) {
						//find resource. If exists, add resource price to global price
						$res = $this->find_resource($ep);
						$extra_price_addons += ($res === false)? 0: floatval($res['price']);
					}
				}
					
				$items_cart[ $cart_id ] = array(
	                'id'            => $item->product_id,
	                'cart_id'       => $cart_id,
	                'data'          => $item,
	                'qty'           => $qty,
	                'product_id'    => $item->product_id,
	                'product_cms'   => ( $lumise->connector->platform == 'php' )? $item->product_id : $item->cms_id,
	                'product_name'  => $item->product_name,
	                'price' => array(
	                    'total'     => 0,
	                    'fixed'     => $fixed_price_qtys,
	                    'resource'  => 0,
	                    'template'  => $template_price,
	                    'base'      => $base_price + $extra_filter_price + $extra_price_addons
	                ),
	                'options'    	=> $item->options,
	                'variation'    	=> $item->variation,
	                'attributes'    => $attributes,
	                'printing'      => $item->printing,
	                'resource'      => $resource,
	                'design'        => $item->design,
	                'template'      => false,
	                'screenshots'   => $screenshorts
	            );

				$product_stage_printful		= $this->dejson(isset($current_product['stages']) ? $current_product['stages'] : '{}');
	            $items_cart[ $cart_id ] = $lumise->apply_filters( 'items-cart-temp', $items_cart[ $cart_id ], array('attributes' => $attributes, 'variations' => $variations, 'product_stages' => $product_stage_printful));
	            
	            unset($item);
			}
            
        }
        
        //get price of resource
        $ids = array();
        
        foreach( $resources as $res ) {
            // $ids[] = $res[ 'id' ];
            $type = preg_replace('/[^a-zA-Z0-9]+/i', '', $res['type']);
            $dataResource = $this->resources( $res );

            $resource_ids[$type.'_'.$dataResource['id']] = $dataResource;
        }
        
        // $resources = $this->resources( $ids );

        $cart_total = 0;
        foreach( $items_cart as $key => $item ) {
            foreach ( $item[ 'resource' ] as $res ){
                // $item[ 'price' ][ 'resource' ] += floatval( $resources[ $res['id'] ][ 'price' ] );
                $type = preg_replace('/[^a-zA-Z0-9]+/i', '', $res['type']);
                if(isset($resource_ids[ $type.'_'.$res['id'] ][ 'price' ]) && $type == 'cliparts' && floatval( $resource_ids[ $type.'_'.$res['id'] ][ 'price' ] ) > 0){
                	$resource_ids[ $type.'_'.$res['id'] ][ 'price' ] = $lumise->apply_filters('cart-process-clipart-price', floatval( $resource_ids[ $type.'_'.$res['id'] ][ 'price' ] ), $item['product_cms']);
                }
                $item[ 'price' ][ 'resource' ] += floatval( $resource_ids[ $type.'_'.$res['id'] ][ 'price' ] );
                $items_cart[ $key ][ 'price' ]['resource'] = $item['price']['resource'];
            }
            
            $qty        = $item['qty'];
            $sub_total  = 0;
            $sum        = $item[ 'price' ][ 'resource' ] + $item[ 'price' ][ 'base' ] + $item[ 'price' ][ 'template' ];
			$print_calc = $this->printing_calc( $item['data'], $qty );
			
			
			$item_total =  ( ( $sum + $print_calc ) * $item['qty'] ) + $items_cart[ $key ]['price']['fixed'];
			
			
            $items_cart[ $key ]['price']['total'] = $item_total;
                 
            $cart_total += $item_total;
            
            unset( $items_cart[ $key ]['data'] );
            
            //store items to files
            $lumise->check_upload();
            
            $item_data  =  $items_cart[ $key ];
            $filename   = $this->save_cart_item_file( $item_data );
            
            if( $filename === false ){
                return $lumise->lang( 'Could not write data on the user data folder, please report to the administrator' );
            }else{
                $items_cart[ $key ]['file'] = $filename;
            }
            
            unset($items_cart[ $key ]['screenshots']);
            unset($items_cart[ $key ]['design']);
            unset($items_cart[ $key ]['uploads']);
            
        }
        
        $_POST = array();
        $cart_data = array(
            'items'     => $items_cart,
            'currency'  => $lumise->cfg->settings[ 'currency' ],
            'total'     => 0
        );
        
        $lumise->connector->set_session( 'lumise_cart_removed', array() );
        $lumise->connector->set_session( 'lumise_cart', $cart_data );
        
		if ( method_exists( $lumise->connector, 'add_to_cart' ) )
			echo  $lumise->connector->add_to_cart ($items_cart);
		else echo '0';

    }

    public function printing_calc( $item, $qty ){
    	
    	global $lumise;
    	
    	$print_price = 0;
    	$db = $lumise->get_db();
    	
        if (isset($item->printing) && $item->printing > 0) {
           
           	$query = "SELECT * FROM `{$db->prefix}printings` WHERE `author`='{$lumise->vendor_id}' AND id = {$item->printing}";
			$printing =  $db->rawQuery($query);
			 
			if (count($printing) > 0)
				$printing = $printing[0];
			else $printing = null;
			
        }
        
        if (isset($printing) && $printing !== null) {
	        
	        $calc			= json_decode(urldecode(base64_decode($printing['calculate'])), true);
            $rules 			= $calc['values'];
            $states_data 	= $item->states_data;
			
            if (empty($rules) && !is_array($rules)) 
            	return $print_price;
			
			$keys = array_keys($rules);
			
			$ind_stage = 0;
			
			foreach ($states_data as $s => $options){
                
                $is_multi = $calc['multi'];
				
                if (!$is_multi)
					$ind_stage  = 0;
                if(!isset($keys[$ind_stage])) continue;
				$stage    	  = $keys[$ind_stage];
                $rules_stages = $rules[$stage];
                $qtys		  = array_keys($rules_stages);
				
                sort($qtys, SORT_NATURAL);
                
				$ind_stage++;
				
                if (count($qtys) == 0) 
                	continue;
                
                $index = -1;
                
                for ($i=0; $i < count($qtys); $i++){
                    if(
                        (
                            intval($qtys[$i] ) < $qty &&
                            strpos($qtys[$i], '>') === false
                        ) ||
                        (
                            strpos($qtys[$i], '>') !== false &&
                            (intval(str_replace('>', '', $qtys[$i])) + 1) <= $qty
                        )
                    )
                        $index = $i;
                }

                if (isset($qtys[$index + 1]))
                    $qty_key = $qtys[$index + 1];
                else
                    $qty_key = $qtys[$index];
                    
                $rule = $rules_stages[$qty_key];
                
                
                $total_res = 0;
                
                foreach ($options as $key => $val) {
	                
                    $unit 	= $val;
                    $option = $key;
                    
                    if( 
                        $calc['type'] == 'color' && 
                        $key == 'colors' && 
                        count((array)$val) > 0
                    ){
                        $unit 	= 1;
                        $option = count((array)$val).'-color';
                        $option = (!isset($rule[$option])) ? 'full-color' : $option;
                    }

                    if (isset($rule[$option]))
                        // $print_price += floatval($rule[$option]*$unit);
                    	$print_price += floatval(floatval($rule[$option])*floatval($unit));
                    
                    if (!is_array($val))
                    	$total_res += $unit;
                }
                
                if(
                   $calc['type'] == 'fixed' 
                    && $total_res > 0
                ){
                    $print_price += floatval( $rule['price'] );
                    if( !$is_multi ) return $print_price;
                }
                
                if(
                    $calc['type'] == 'size' &&
                    is_object($item->printings_cfg) &&
                    $total_res > 0
                ){
                    $product_size   = '';
                    
                    foreach ( $item->printings_cfg as $key => $value ) {
                        if( $key == $item->printing || $key == '_'.$item->printing) 
                        	$product_size = $value;
                    } 
                    
                    $print_price += floatval( $rule[ $product_size ] );
                    
                    if ( !$is_multi ) 
                    	return $print_price;
                }
            }
                           
        }
		
        return $print_price;
    }
	
	public function find_resource( $res ) {
		
		global $lumise;
		
		$query = array(
			"SELECT  t.id, t.price",
			"FROM {$lumise->db->prefix}{$res->table} t",
			"WHERE `t`.`author`='{$lumise->vendor_id}' AND `t`.`id` = {$res->id}"
		);
		
		$res_items = $lumise->db->rawQuery( implode( ' ', $query ) );
		
		return isset($res_items[0])? $res_items[0] : false;
	}

	public function redirect( $url ) {
		if ( empty( $url ) )
			return;
		// clean the output buffer
		ob_clean();
        
		header( "location: " . $url );
        
		exit;
	}
    
    public function resources( $res ){
        
        global $lumise;
        
        $resources = array();

        $table = preg_replace('/[^a-zA-Z0-9]+/i', '', $res['type']);

        $query = array(
			"SELECT  c.id, c.name, c.price",
			"FROM {$lumise->db->prefix}".$table." c",
			"WHERE `c`.`author`='{$lumise->vendor_id}' AND `c`.`id` IN (" . intval($res['id']) .")"
		);
        
        $resourceData = $lumise->db->rawQuery( implode( ' ', $query ) );

        if(count($resourceData) > 0){
        	$resources = array(
        		'id' => intval($resourceData[0]['id']),
        		'name' => $resourceData[0]['name'],
        		'price' => floatval($resourceData[0]['price'])
        	);
        }

        return $resources;
    }
    
    public function on_error($msg) {
    ?><!DOCTYPE html>
		<html
			xmlns="http://www.w3.org/1999/xhtml"lang="en-US">
			<head>
				<meta http-equiv="Content-Type"content="text/html; charset=utf-8"/>
				<meta name="viewport"content="width=device-width">
					<meta name='robots'content='noindex,follow'/>
					<title>Checkout Error</title>
					<style type="text/css">html{background:#f1f1f1}body{background:#fff;color:#444;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;margin:2em auto;padding:1em 2em;max-width:700px;-webkit-box-shadow:0 1px 3px rgba(0,0,0,.13);box-shadow:0 1px 3px rgba(0,0,0,.13)}h1{border-bottom:1px solid#dadada;clear:both;color:#666;font-size:24px;margin:30px 0 0;padding:0 0 7px}#error-page{margin-top:50px}#error-page p{font-size:14px;line-height:1.5;margin:25px 0 20px}#error-page code{font-family:Consolas,Monaco,monospace}ul li{margin-bottom:10px;font-size:14px}a{color:#0073aa}a:active,a:hover{color:#00a0d2}a:focus{color:#124964;-webkit-box-shadow:0 0 0 1px#5b9dd9,0 0 2px 1px rgba(30,140,190,.8);box-shadow:0 0 0 1px#5b9dd9,0 0 2px 1px rgba(30,140,190,.8);outline:0}.button{background:#f7f7f7;border:1px solid#ccc;color:#555;display:inline-block;text-decoration:none;font-size:13px;line-height:26px;height:28px;margin:0;padding:0 10px 1px;cursor:pointer;-webkit-border-radius:3px;-webkit-appearance:none;border-radius:3px;white-space:nowrap;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;-webkit-box-shadow:0 1px 0#ccc;box-shadow:0 1px 0#ccc;vertical-align:top}.button.button-large{height:30px;line-height:28px;padding:0 12px 2px}.button:focus,.button:hover{background:#fafafa;border-color:#999;color:#23282d}.button:focus{border-color:#5b9dd9;-webkit-box-shadow:0 0 3px rgba(0,115,170,.8);box-shadow:0 0 3px rgba(0,115,170,.8);outline:0}.button:active{background:#eee;border-color:#999;-webkit-box-shadow:inset 0 2px 5px-3px rgba(0,0,0,.5);box-shadow:inset 0 2px 5px-3px rgba(0,0,0,.5);-webkit-transform:translateY(1px);-ms-transform:translateY(1px);transform:translateY(1px)}</style>
				</head>
				<body id="error-page">
					<p class="msg">Looks like an error has occurred, please notify the administrator. 
						<font color="red">
							<?php echo $msg; ?>
						</font>
					</p>
				</body>
			</html><?php   
	    exit;
    }

}

/*
*	Init Cart
*/

new lumise_cart();



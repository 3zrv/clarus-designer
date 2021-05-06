<?php
/**
*
*	(p) package: lumise
*	(c) author:	King-Theme
*	(i) website: https://www.lumise.com
*
*/

/*ini_set('display_errors', 1);*/
	
class lumise_lib{

	protected $main;
 
	public function __construct($instance) {
		
		$this->main = $instance;
		
	}

	public function slugify($text) {

		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		if(function_exists('iconv')) $text = (@iconv('utf-8', 'us-ascii//TRANSLIT', $text)) ? : $text;
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, '-');
		$text = preg_replace('~-+~', '-', $text);
		$text = strtolower($text);

		if (empty($text)) {
			return '';
		}

		return $text;

	}

	public function sql_esc($str) {
		
		global $lumise;
		
		if (function_exists('mysqli_real_escape_string'))
			return mysqli_real_escape_string($lumise->db->mysqli(), $str);
		else if (function_exists('mysql_real_escape_string'))
			return mysql_real_escape_string($str);
		else return $str;
		
	}

	public function dejson($text = '', $force_array = false) {

		if (!empty($text))
			$data = @json_decode(rawurldecode(base64_decode($text)), $force_array);

		return isset($data) ? $data : array();

	}

	public function enjson($text = '') {

		if (!empty($text))
			$data = base64_encode(rawurlencode(@json_encode($text)));

		return isset($data) ? $data : '';

	}

	public function esc($name = '', $default = '') {
		return isset($_POST[$name]) ? (is_array($_POST[$name])? $_POST[$name] : htmlspecialchars($_POST[$name])) : $default;
	}

	public function check_upload ($time = '') {

		return $this->main->check_upload($time);

	}

	protected function process_upload ($data, $design_id) {

		foreach ($data->stages as $s => $stage) {

			$type = $this->get_type($stage->screenshot);
			if (
				$this->save_image($stage->screenshot, $s.'_'.$design_id) === 1 &&
				$type !== ''
			) {
				$data->stages->{$s}->screenshot = '/thumbnails/'.$s.'_'.$design_id.$type;
			}

			if (isset($data->stages->{$s}->data)) {

				$obj = json_decode($data->stages->{$s}->data);

				if (isset($obj->objects)) {

					for ($i = 0; $i < count($obj->objects); $i++) {

						if (isset($obj->objects[$i]->id) && isset($obj->objects[$i]->src)) {

							$src = $obj->objects[$i]->src;
							$id = $obj->objects[$i]->id;
							$type = $this->get_type($src);

							/*
								Process all objects contain attribute .src
							*/
							if ($type !== '' && $this->save_image($src, $id) === 1) {
								$obj->objects[$i]->src = '/'.$this->get_path($id, '/').$type;
								$this->user_uploads($obj->objects[$i]->src, $design_id);
							}

							/*
								Process mask
							*/

							if (
								isset($obj->objects[$i]->fx) &&
								isset($obj->objects[$i]->fx->mask) &&
								isset($obj->objects[$i]->fx->mask->dataURL)
							) {

								unset($obj->objects[$i]->fx->mask->image);

								$id = explode(':', $id);
								$id = $id[0].':'.$this->create_sid(10);
								$type = $this->get_type($obj->objects[$i]->fx->mask->dataURL);
								$src = $obj->objects[$i]->fx->mask->dataURL;

								if ($type !== '' && $this->save_image($src, $id) === 1)
									$obj->objects[$i]->fx->mask->dataURL = '/'.$this->get_path($id, '/').$type;

							}

						}
					}

				}

				$data->stages->{$s}->data = json_encode($obj);

			}

		}

		return $data;

	}

	protected function save_image ($data, $id) {

		$type = $this->get_type($data);

		if ($type === '')
			return $this->main->lang('Could not save image because invalid type');;

		$path = $this->main->cfg->upload_path.$this->get_path($id, TS).$type;

		$data = explode(';base64,', $data);

		if ($path === -1)
			return -1;
		else if (!file_put_contents($path, base64_decode($data[1])))
			return $this->main->lang('Could not write data on the upload folder, please report to the administrator');
		else return 1;

	}

	protected function get_type($data) {

		if (strpos($data, 'data:image/jpeg;base64,') !== false)
			return '.jpg';
		else if (strpos($data, 'data:image/png;base64,') !== false)
			return '.png';
		else if (strpos($data, 'data:image/gif;base64,') !== false)
			return '.gif';
		else if (strpos($data, 'data:image/svg+xml;base64,') !== false)
			return '.svg';

		return '';

	}

	protected function get_path($id, $sd) {

		if (strpos($id, ':') === false) {
			return 'thumbnails'.$sd.$id;
		}else{
			$id = explode(':', $id);
			$id[0] = $this->main->generate_id();
			date_default_timezone_set('UTC');
			$name = date('Y', $id[0]).DS.date('m', $id[0]).DS.$id[1];
			$check = $this->check_upload($id[0]);

			if ($check === 1)
				return 'user_uploads'.DS.$name;
			else return -1;
		}

	}

	protected function get_my_designs ($index = 0, $limit = 20) {

		$aid = str_replace("'", "", $this->main->connector->cookie('lumise-AID'));
		$db = $this->main->get_db();

		return $db->where("aid = '$aid'")->get('designs', $limit);

	}

	protected function user_uploads($src, $design_id){

	}

	protected function do_save_design ($id = 0, $data = '') {

		$aid = $this->main->connector->cookie('lumise-AID');

		if ($data !== '' && $id !== 0) {

			$db = $this->main->db;
			$data = json_decode(urldecode(base64_decode($data)));
			$stk = $this->esc('private_key');

			$check_upload = $this->check_upload($data->updated);
			if ($check_upload !== 1)
				return array('error' => $check_upload);

			global $lumise;

			$db = $lumise->get_db();

			$db->where ("author = '{$lumise->vendor_id}' AND id='$id' AND (aid='$aid' OR share_token='$stk')");

			$check = $db->getOne ('designs');

			$date = @date ("Y-m-d H:i:s"/*, strtotime($date_old)*/);

			if ($check && ($check['aid'] == $aid || $check['share_permission'] == 2)) {

				$check['data'] = json_decode($check['data']);

				if (is_object($check['data']->stages)) {
					foreach($check['data']->stages as $name => $stage) {
						if (!isset($data->stages->{$name}))
							$data->stages->{$name} = $stage;
					}
				}

				$db->where ('id', $id)->update ('designs', array(
					'data' => '',
					'updated' => $date
				));

			}else{

				$share_token = $this->create_sid();

				$id = $db->insert ('designs', array(
					'data' => '',
					'aid' => $aid,
					'created' => $date,
					'updated' => $date,
					'share_token' => $share_token
				));

			}

			$data = $this->process_upload($data, $id);

			$db->where ('id', $id)->update ('designs', array(
				'data' => json_encode($data)
			));

			$result = array(
				'success' => true,
				'id' => $id
			);

		}else {
			$result = array('error' => $this->main->lang('The data is empty'));
		}

		return $result;

	}

	protected function do_delete_design ($id = 0) {

		if ($this->is_own_design($id) === true) {

			$db->where ("`author`='{$this->main->vendor_id}' AND id='$id' AND aid='{$this->aid}'");
			$db->delete('designs');

			return array(
				'success' => true,
				'id' => $id
			);
		}else{
			return array(
				'error' => $this->main->lang('You do not have permission to delete.')
			);
		}

	}

	protected function is_own_design ($id = 0) {

		$db = $this->main->get_db();
		$db->where ("author='{$lumise->vendor_id}' AND id='$id' AND aid='{$this->aid}'");
		$check = $db->getOne ('designs');

		if ($check && isset($check['id']))
			return true;
		else return false;

	}

	protected function create_sid ($l = 20){
		return substr(str_shuffle(implode(array_merge(range('A','Z'), range('a','z'), range(0,9)))), 0, $l);
	}

	protected function children_categories ($id = 0, $list = array()) {

	    if ($id === 0)
	    	return $list;
	    else array_push($list, (int)$id);
		
		$query = sprintf(
			"SELECT `id` FROM `%s` WHERE `author`='{$this->main->vendor_id}' AND `parent` = '%d'",
            $this->sql_esc($this->main->db->prefix."categories"),
            $id
        );
        
	    $childs = $this->main->db->rawQuery($query);

	    if (count($childs)>0){
			foreach($childs as $child){
				$list = $this->children_categories($child['id'], $list);
			}
			
			return $list;
		}
	    	
	    else return $list;

    }
    
    public function x_items($type = 'cliparts') {

		global $lumise;
		
		$lumise->do_action('x_items', $type);
		
		$category = htmlspecialchars(isset($_POST['category']) ? $_POST['category'] : 0);
		$index = (int)htmlspecialchars(isset($_POST['index']) ? $_POST['index'] : 0);
		$q = htmlspecialchars(isset($_POST['q']) ? $_POST['q'] : '');
		$limit = (int)htmlspecialchars(isset($_POST['limit']) ? $_POST['limit'] : 48);
		$cate_name = '';
		
		$categories = $this->get_categories($type, $category, '`order` ASC, `name` ASC', true);
		$parents = $this->get_category_parents($category);
		
		$query = sprintf(
			"SELECT `parent`, `name` FROM `%s` WHERE `author`='{$this->main->vendor_id}' AND `id`='%d'",
            $this->sql_esc($lumise->db->prefix."categories"),
           $category
        );
		
		$get_cate = $lumise->db->rawQueryOne($query);
		if ($category !== 0 && count($categories) === 0 && isset($get_cate['parent'])) {
			$categories = $this->get_categories($type, $get_cate['parent'],'`order` ASC, `name` ASC', true);
		}
		
		$end = false;
		foreach ($categories as $cta) {
			if ($cta['id'] == $category)
				$end = true;
		}
		
		if ($category == 0 || ($end === true && count($parents) === 1)) {
			/*if ($type == 'cliparts') {*/
			array_unshift($categories,array(
				"id" => "{free}",
				"name" => $lumise->lang('Free items'),
				"thumbnail" => $lumise->cfg->assets_url.'assets/images/free_thumbn.jpg'
			));
			array_unshift($categories, array(
				"id" => "{featured}",
				"name" => "&star; ".$lumise->lang('Featured'),
				"thumbnail" => $lumise->cfg->assets_url.'assets/images/featured_thumbn.jpg'
			));
		}
		
		if ($category == 0) {
			$parents = array(array(
				"id" => "",
				"name" => $lumise->lang('All categories')
			));
		}

		if(!isset($get_cate['name']) || $get_cate['name'] == NULL){
			$get_cate['name'] = '';
		}
		
		$cate_name = $get_cate['name'];
		
		if ($category == '{featured}') {
			$cate_name = "&star; ".$lumise->lang('Featured');
			$parents = array(array(
				"id" => "{featured}",
				"name" => $cate_name
			));
		}else if ($category == '{free}'){
			$cate_name = $lumise->lang('Free items');
			$parents = array(array(
				"id" => "{free}",
				"name" => $cate_name
			));
		}
		
		foreach ($parents as $key => $val) {
			$parents[$key]['name'] = $lumise->lang($val['name']);
		}
		
		foreach ($categories as $key => $val) {
			$categories[$key]['name'] = $lumise->lang($val['name']);
		}
		
		header('Content-Type: application/json');
		
		$xitems = $this->get_xitems($category, $q, $index, $type, $limit);
		
		$items = $xitems[0];
		$total = $xitems[1];
		
		echo json_encode(array(
			"category" => $category,
			"category_name" => $lumise->lang($cate_name),
			"category_parents" => array_reverse($parents),
			"categories" => $categories,
			"categories_full" => (isset($_POST['ajax']) && $_POST['ajax'] == 'backend') ? $this->get_categories($type) : '',
			"items" => $items,
			"q" => $q,
			"total" => $total,
			"index" => $index,
			"page" => 1,
			"limit" => $limit
		));

	}
    
	public function get_xitems($category = '', $q = '', $index = 0, $type = 'cliparts', $limit = 48){

		$select = array("item.*, '$type' as resource ");
		$extra = array();
		
		$q = trim($q);
		
		if ($q !== '') {
			array_push($extra, "(item.name LIKE '%$q%' OR item.tags LIKE '%$q%')");
		}
		
		$q = $this->sql_esc($q);
		$type = $this->sql_esc($type);
		
		//get all categories deactived
		$query = sprintf(
			"SELECT `id` FROM `%s` WHERE `author`='{$this->main->vendor_id}' AND `type`='%s' AND `active`= 0",
			$this->sql_esc($this->main->db->prefix."categories"),
			$type
		);
		
		$cat_deactives = array();
		$cates = $this->main->db->rawQuery($query);
		
		foreach($cates as $cat){
			//find all child to deactive
			$cat_deactives = $this->children_categories($cat['id'], $cat_deactives);
		}
		
		$select = "item.*, '$type' as resource ";
		$group_by = "GROUP BY item.id";
		$order_by = "ORDER BY `item`.`order` DESC, `item`.`created` DESC";
		
		$where = array("item.author='{$this->main->vendor_id}'", "item.active = 1");
		
		if (is_numeric($category) && $category > 0){
			
			$from = array("FROM {$this->main->db->prefix}{$type} item");
			
			array_push($from, "LEFT JOIN `{$this->main->db->prefix}categories_reference` cref ON item.`id` = cref.`item_id`");
			array_push($where, "cref.`category_id` IN (".implode(',', $this->children_categories($category)).")");
			array_push($where, "cref.`type`='{$type}'");
			
			$query = array(
				"SELECT SQL_CALC_FOUND_ROWS ". $select,
				implode(' ', $from),
				"WHERE ".implode(' AND ', $where),
				(count($extra)>0? ' AND '.implode(' AND ', $extra):''),
				$group_by,
				$order_by
			);
			
		} else if (is_array($category)){
			
			$from = array("FROM {$this->main->db->prefix}{$type} item");
			
			array_push($from, "LEFT JOIN `{$this->main->db->prefix}categories_reference` cref ON item.`id` = cref.`item_id`");
			array_push($where, "cref.`category_id` IN (".implode(',', $category).")");
			array_push($where, "cref.`type`='{$type}'");
			
			$query = array(
				"SELECT SQL_CALC_FOUND_ROWS ". $select,
				implode(' ', $from),
				"WHERE ".implode(' AND ', $where),
				(count($extra)>0? ' AND '.implode(' AND ', $extra):''),
				$group_by,
				$order_by
			);
			
		} else if (in_array($category, array('', 0, '{featured}', '{free}'))) {
			
			
			if ($category == '{featured}') {
				array_push($extra, "item.featured = 1");
			}else if ($category == '{free}') {
				array_push($extra, "item.price = 0");
			}
			
			if (count($cat_deactives)>0) {
				
				$query1 = array(
					"SELECT ". $select,
					"FROM {$this->main->db->prefix}{$type} item",
					"LEFT JOIN `{$this->main->db->prefix}categories_reference` cref ON item.`id` = cref.`item_id`",
					"WHERE ".implode(' AND ', $where)." AND cref.`type`='{$type}'",
					(count($extra)>0? ' AND '.implode(' AND ', $extra):''),
					"AND cref.`category_id` NOT IN (".implode(",", $cat_deactives).")",
					$group_by
				);
				
				$query2 = array(
					"SELECT ". $select,
					"FROM {$this->main->db->prefix}{$type} item",
					"WHERE ".implode(' AND ', $where)." AND item.`id` NOT IN 
						(
							SELECT item_id FROM `{$this->main->db->prefix}categories_reference` as cref 
							WHERE cref.`type` = '{$type}'
						)",
					(count($extra)>0? ' AND '.implode(' AND ', $extra):''),
					$group_by
				);
				
				$query = array(
					"SELECT SQL_CALC_FOUND_ROWS item.* FROM (",
					implode(" ", $query1),
					"UNION ALL",
					implode(" ", $query2),
					") as item ",
					$order_by
				);
				
			} else {
				
				$query = array(
					"SELECT SQL_CALC_FOUND_ROWS ". $select,
					"FROM {$this->main->db->prefix}{$type} item",
					"WHERE ".implode(' AND ', $where),
					(count($extra)>0? ' AND '.implode(' AND ', $extra):''),
					$group_by,
					$order_by
				);
			}
 		}
 		
		if (isset($index) && $index."" != 'total')
			array_push($query, "LIMIT $index, $limit");
		else return count($this->main->db->rawQuery(implode(" ", $query)));
		
		$query = implode(" ", $query);
		
        $items = $this->main->db->rawQuery($query);
        $total = $this->main->db->rawQuery("SELECT FOUND_ROWS() AS count");
        
        if (count($total) > 0 && isset($total[0]['count'])) {
			$total = $total[0]['count'];
		} else $total = 0;
		
        foreach ($items as $key => $val) {
	        
	        $items[$key]['name'] = $this->main->lang($val['name']);
			$tags = $this->get_tags_item($val['id'], $type);
			
			if (count($tags)>0) {
				
				$items[$key]['tags']= array();
				
				foreach ($tags as $tkey => $tval) {
					$items[$key]['tags'][] = $tval['name'];
				}
				
				$items[$key]['tags'] = implode(', ', $items[$key]['tags']);
				
			}
	        
        }
        
        return array($items, $total);

    }
	
	protected function get_tags_item($id, $type){
		
		$select = array("tags.name, tags.id");
		$from = array("FROM {$this->main->db->prefix}tags as tags");
		array_push($from, "LEFT JOIN `{$this->main->db->prefix}tags_reference` tagref ON tags.`id` = tagref.`tag_id`");
		$where = array("tags.author='{$this->main->vendor_id}'", "tagref.item_id = $id");
		
		$query = array(
			"SELECT ".implode(',', $select),
			implode(' ', $from),
			(count($where) > 0 ? "WHERE ".implode(' AND ', $where) : ''),
			"GROUP BY tags.id"
		);
		
		return $this->main->db->rawQuery(implode(" ", $query));
		
	}

    protected function get_shapes($index = 0){

		$query = array(
			"SELECT *, 'shape' as `resource`",
			"FROM `{$this->main->db->prefix}shapes`",
			"WHERE  `{$this->main->db->prefix}shapes`.`author`='{$this->main->vendor_id}' AND `{$this->main->db->prefix}shapes`.`active` = 1",
			"ORDER BY `{$this->main->db->prefix}shapes`.`order` ASC"
		);
		if (isset($index) && $index != 'total')
			array_push($query, "LIMIT $index, 50");
		else return count($this->main->db->rawQuery(implode(" ", $query)));

        return $this->main->db->rawQuery(implode(" ", $query));

    }

    protected function get_items($atts){
		
		global $lumise;
		
        extract($atts);

        $db = $lumise->get_db();

        $order_default = isset($atts['order_default']) ? $order_default : 'name';

        $orderby = isset($atts['orderby']) ? $orderby : $order_default;
        $order = isset($atts['order']) ? $order : 'asc';


        $data = array();

        if (isset($where)) {
            foreach ($where as $key => $val) {
                $operator = (isset($val['operator']) && !empty($val['operator'])) ? $val['operator'] : '=';
                $db->where($key, $val['value'], $operator);
            }
        }

        if (isset($filter) && is_array($filter) && isset($filter['keyword']) && !empty($filter['keyword'])) {

            $fields = explode(',', $filter['fields']);

            $condiFields = array();
            $condiKey = array();

            foreach ($fields as $field) {
                $condiFields[] = $fields . ' LIKE ? ';
                $condiKey[] = '%' . $filter['keyword'] . '%';
            }

            $db->where('(' . implode(' OR ', $condiFields) . ')', $condiKey);
        }


       	$db->orderBy($orderby, $order);

        $limit_cfg = null;

        if (isset($limitstart) && isset($limit))
            $limit_cfg = array($limitstart, $limit);

        $data['items'] = $db->withTotalCount()->get($atts['table'], $limit_cfg);
        $data['total_count'] = $db->totalCount;
        $data['total_page'] = isset($limit) ? ceil($db->totalCount / $limit) : 0;

        return $data;

    }

	protected function scan_languages($path = ''){

		if (empty($path)) {
			$path = explode(DS, dirname(__FILE__));
			array_pop($path);
			$path = implode(DS, $path);
		}

		$result = array();
		
		$files = scandir($path);

		foreach ($files as $file) {
			if ($file != '.' && $file != '..' && $file != '.git' && $file != 'connectors') {
				if (is_dir($path.DS.$file)) {
					$result = array_merge($result, $this->scan_languages($path.DS.$file));
				} else if (strpos($file, '.php') == strlen($file)-4) {

					$content = file_get_contents($path.DS.$file);
					preg_match_all('/->lang\(\'+(.*?)\'\)/i', $content, $matches);

					if (isset($matches[1]) && count($matches[1]) > 0) {
						$result = array_merge($result, $matches[1]);
					}
				}
			}
		}

		return array_unique($result, SORT_STRING);

	}

	public function get_categories_parent($arr, $parent = 0, $lv = 0) {

		$result = array();

		foreach ($arr as $value) {

			if ($value['parent'] == $parent && $value['id'] !== 0) {

				$value['lv'] = $lv;
				$result[] = $value;
				$result = array_merge($result, self::get_categories_parent($arr, $value['id'], $lv + 1));

			}

		}

		return $result;

	}
	
	public function get_category_parents($id, $result = array()) {
		
		global $lumise;
		
		$query = sprintf(
			"SELECT `id`, `name`, `parent` FROM `%s` WHERE `author`='{$this->main->vendor_id}' AND `id`='%d' ORDER BY `name`",
            $this->sql_esc($lumise->db->prefix."categories"),
            $id
        );
		$cate = $lumise->db->rawQueryOne($query);
		
		if (isset($cate['id'])) {
			array_push($result, $cate);
			if ($cate['parent'] != 0) {
				$result = $this->get_category_parents($cate['parent'], $result);
			}
		}
		
		return $result;

	}

	public function get_categories($type = 'cliparts', $parent = null, $orderby = '`order` ASC', $active = false) {

		global $lumise;
		
		$query = sprintf(
			"SELECT `id`, `name`, `parent`, `thumbnail_url` as `thumbnail` FROM `%s` WHERE `%s`.`author`='%s' AND `type`='%s' %s ORDER BY {$orderby}",
            $this->sql_esc($lumise->db->prefix."categories"),
            $this->sql_esc($lumise->db->prefix."categories"),
            $this->main->vendor_id,
            $this->sql_esc($type),
			($active? " AND `active` = 1 ": '') . 
            ($parent !== null ? " AND `parent`='".$this->sql_esc($parent)."'" : '')
        );
        
		$cates = $lumise->db->rawQuery($query);
		
		if ($parent === null)
			return $this->get_categories_parent($cates);
		else return $cates;
		
	}

	public function get_tags($type = 'cliparts') {

		global $lumise;
		$query = sprintf(
			"SELECT * FROM `%s` WHERE `author`='{$this->main->vendor_id}' AND `type`='%s' ORDER BY `name`",
            $this->sql_esc($lumise->db->prefix."tags"),
            $this->sql_esc($type)
        );
		$tags = $lumise->db->rawQuery($query);
		return $tags;

	}

	public function upload_file ($data = '', $path = '') {
	
		global $lumise;

		if (empty($data) || empty($path))
			return array("error" =>  $lumise->lang('Invalid input data'));

		$path = $lumise->cfg->upload_path.$path;

		if (is_string($data)){
			
			$tmpl = $this->main->cfg->upload_path.'user_data'.DS.$data;
			
			if (is_file($tmpl)){
				$data = @file_get_contents($tmpl);
				@unlink($tmpl);
			}
			
			$data = @json_decode(urldecode(base64_decode($data)));

			if (!is_object($data))
				return array("error" => $lumise->lang('Could not decode data'));
		}
		
		if (!isset($data->type))
			$data->type = '';
		
		$data->type = trim($data->type);
		$data->type = strtolower($data->type);
		
		if (
			empty($data->type) || 
			!in_array(
				$data->type, 
				array(
					'application/zip', 
					'application/json', 
					'text/plain', 
					'.docx', 
					'.jpg', 
					'.png', 
					'.lumi', 
					'.woff2', 
					'.ttf', 
					"image/jpeg", 
					"image/png", 
					"image/gif", 
					"image/svg+xml", 
					"application/font-woff",
					"application/font-ttf",
					"ttf", 
					"woff", 
					"woff2", 
					"json",
					"lumi"
				)
			)

		) return array("error" => $lumise->lang('Invalid upload file types, only allows .jpg, .png, .gif and .svg'));
		
		if (!isset($data->size) || ($data->size > 52428800 && strpos($data->name, '.lumi') === false))
			return array("error" => $lumise->lang('Max file size upload is 5MB'));
		
		if($data->size > 52428800 && strpos($data->name, '.lumi') !== false)
			return array("error" => $lumise->lang('Max file size upload is 50MB'));
		
		$ext = strrchr($data->name, '.');
		$name = urlencode(substr($data->name, 0, strlen($data->name) - strlen($ext)));
		
		$i = 1;
		while (file_exists($path.$data->name)) {
			$data->name = $name.'-'.($i++).$ext;
		}

		$data->data = explode('base64,', $data->data);
		$data->data = base64_decode($data->data[1]);

		if (!is_dir($path.$data->name))
			$lumise->check_upload(time());

		if (!file_put_contents($path.$data->name, $data->data))
			return array("error" => $lumise->lang('Could not upload file, error on function file_put_contents when trying to push '.$path.$data->name));

		$thumn_name = '';

		if (isset($data->thumbn)) {

			if ($ext == '.json' || $ext == '.lumi' || $ext == '.png')
				$ext = '.png';
			else $ext = '.jpg';
			
			$thumn_name = $lumise->generate_id(12).$ext;
			$i = 1;

			$data->thumbn = explode('base64,', $data->thumbn);
			$data->thumbn = base64_decode($data->thumbn[1]);

			if (!@file_put_contents($path.$thumn_name, $data->thumbn))
				return array("error" => $lumise->lang('Could not upload thumbn file'));

		}

		return array(
			"success" => true,
			"name" => $data->name,
			"thumbn" => $thumn_name
		);

	}

    //get all printing
    // @active all get published printing or not. Default is true
    public function get_prints($active = true){

        $db = $this->main->get_db();

        if ($active){
            $db->where(' active = 1 ');
        }
		
		$db->where("author='{$this->main->vendor_id}'");
		
        return $db->get('printings');

    }

    public function get_products ($args) {
		
		global $lumise;
		
		if (!isset($args['limit']))
			$args['limit'] = 12;
			
		$categories = $this->get_categories('products', null, '`order` ASC', true /*only active*/);
		
		$query = array(
			"SELECT SQL_CALC_FOUND_ROWS p.*",
			"FROM {$this->main->db->prefix}products p",
			(!empty($args['category']) ?
				"LEFT JOIN {$this->main->db->prefix}categories_reference cref ON cref.item_id = p.id ".
				"WHERE `p`.`author` = '{$this->main->vendor_id}' AND p.active = 1 AND cref.category_id = ".$args['category'].
				(!empty($args['s']) ? " AND p.name LIKE '%{$args['s']}%'" : '')
				:
				"WHERE `p`.`author` = '{$this->main->vendor_id}' AND p.active = 1".(!empty($args['s']) ? " AND p.name LIKE '%{$args['s']}%'" : '')
			),
			"ORDER BY `p`.`order`, `p`.`id` ASC",
			"LIMIT {$args['index']}, {$args['limit']}"
		);
		
		if (isset($args['no_cms_filter']) && $args['no_cms_filter'] === true) {
			
			$products = $this->main->db->rawQuery(implode(' ', $query));
			$total = $this->main->db->rawQuery("SELECT FOUND_ROWS() AS count");
			
		} else {

			$query = $lumise->apply_filters('query_products', $query, $args);
			$products = $this->main->db->rawQuery(implode(' ', $query));
			$total = $this->main->db->rawQuery("SELECT FOUND_ROWS() AS count");
		
			foreach($products as $i => $product) {
				$products[$i] = $this->prepare_product($product);
			}
			
			$products = $lumise->apply_filters('products', $products);
			
		}
		
		if (count($total) > 0 && isset($total[0]['count'])) {
			$total = $total[0]['count'];
		} else $total = 0;
		
		return array(
			'categories' => $categories,
			'products' => $products,
			'index' => ((Int)$args['index'])+count($products),
			'limit' => $args['limit'],
			'total' => $total,
			's'		=> $args['s'],
			'category' => $args['category']
		);
    }

	public function get_product($id = null) {
		
		global $lumise;
		
		if (!$id && isset($_GET['product_base']))
			$id = $lumise->esc('product_base');
		
		if (!$id && isset($_POST['product_base']))
			$id = $lumise->esc('product_base');
		
		if ($id === null)
	    	return null;
		
	    $product = $lumise->db->rawQuery(
	    	"SELECT * FROM `{$lumise->db->prefix}products` WHERE `author`='{$this->main->vendor_id}' AND id=".(Int)$id
	    );
		
		$product = $lumise->apply_filters('get_product', $product, $id);
		
	    if (count($product) > 0) {
			return $this->prepare_product($product[0]);
	    } else return null;

	}
	
	public function prepare_product($product = array()) {
		
		global $lumise;
		
		$product['printings_cfg'] = json_decode(str_replace('\\\'', "'", rawurldecode($product['printings'])));
		$product['printings'] = $this->get_printings($product['printings']);
		$product['variations'] = $this->get_variations($product['variations']);
		
		$product['product'] = ($lumise->connector->platform == 'php') ? 0 : $product['product'];
		$product['price'] = floatval($product['price']);
		$product['stages'] = $this->dejson(stripslashes($product['stages']));
		$product['attributes'] = $this->dejson($product['attributes']);
		
		$has_quantity = false;
		
		foreach ($product['attributes'] as $id => $attr) {
			
			if (isset($attr->name))
				$attr->name = $lumise->lang($attr->name);
			else $attr->name = '';
			
			if (isset($attr->values) && !empty($attr->values)) {
				
				if (is_string($attr->values)) {
					$values = @json_decode($attr->values, true);
					if ($values !== null)
						$attr->values = $values;
					else $attr->values = array("default" => $attr->values);
				}
					
				if ( 
					$attr->type == 'product_color' && 
					is_array($attr->values) && 
					isset($attr->values['options']) &&
					is_array($attr->values['options']) && 
					isset($attr->values['options'][0]['value'])
				) {
					$product['color'] = $attr->values['options'][0]['value'];
					
					foreach ($attr->values['options'] as $op) {
						if ($op['default'] === true)
							$product['color'] = $op['value'];	
					}
					
					if (
						isset($product['variations']) && 
						is_object($product['variations'])
					) {
						if (!is_object($product['variations']->default))
							$product['variations']->default = new stdClass();
						if (!isset($product['variations']->default->{$attr->id}))
							$product['variations']->default->{$attr->id} = $product['color'];
					}
				}
			}
			
			if ($attr->type == 'quantity')
				$has_quantity = true;
			
		}
		
		if (!is_object($product['attributes']))
			$product['attributes'] = new stdClass();
		
		if ($has_quantity === false) {
			$product['attributes']->quantity = array(
				"id" => "quantity",
				"name" => $lumise->lang('Quantity'),
				"value" => isset($_POST['quantity']) ? (Int)$_POST['quantity'] : 1,
				"type" => "quantity"
			);	
		}
		
		if (isset($product['stages']->stages) && is_object($product['stages']->stages)) {
			$product['stages'] = $product['stages']->stages;
			if (isset($product['stages']->colors))
				unset($product['stages']->colors);
		}
			
		foreach ($product['stages'] as $key => $val) {
			if (isset($val->label) && !empty($val->label)) {
				$product['stages']->{$key}->label = $lumise->lang(urldecode($val->label));
			}
		}
			
		$product['variations'] = $this->enjson($product['variations']);
		$product['attributes'] = $this->enjson($product['attributes']);
		
	    $return_product = $lumise->apply_filters('product', $product);

	    if(isset($product['description']) && isset($product['active_description']) && $product['active_description'] == 1 && $return_product !== null ){
	    	$return_product['description'] = $product['description'];
	    }
		
		foreach ($product['stages'] as $n => $d) {
			
			if (isset($d->template)) {
				
				$d->template = (Array)$d->template;
				
				if (isset($d->template['id'])) {
					
					$prod = $lumise->db->rawQuery(
						sprintf(
							"SELECT * FROM `%s` WHERE `id`=%d",
							$lumise->db->prefix.'templates',
							(Int)$d->template['id']
						)
					);
					
					if (count($prod) > 0) {
						$product['stages']->{$n}->template['screenshot'] = $prod[0]['screenshot'];
					} else {
						$product['stages']->{$n}->template = array();	
					}
					
				}
			}
		}
		
		if(!empty($return_product)){
			$return_product['stages'] = $this->enjson($return_product['stages']);
		}
		
		return ($return_product !== null && isset($return_product['id']) ? $return_product : $product);
		
	}	
	
	public function get_printings($prt) {
		
		global $lumise;
		
		if (!empty($prt) && $prt != '%7B%7D') {
		    
		   $cfg = !is_array($prt ) ? json_decode(str_replace(array('\\\''), array("'"), rawurldecode($prt)), true) : $prt;
			
		    if (is_array($cfg)) {
			    
			    $prints = array_keys($cfg);
			    
			    foreach ($prints as $i => $k) {
					$prints[$i] = preg_replace("/[^0-9.]/", "", $k); 
				}
				
				$prints = implode(',', $prints);
				$query = "SELECT * FROM `{$lumise->db->prefix}printings` WHERE `author`='{$this->main->vendor_id}' AND `id` IN ($prints) ORDER BY field(id, $prints)";
				
				return $lumise->db->rawQuery($query);
				
			}
			
	    } else return array();
	    
	}	 
	
	public function get_variations($vrt) {
		
		if (!isset($vrt) || empty($vrt))
			return array();
		
		$vrt = $this->dejson($vrt);
		
		if (is_object($vrt) && isset($vrt->variations)) {
			foreach ($vrt->variations as $id => $vari) {
				$vari->printings_cfg = (Array)$vari->printings;
				$vari->printings = $this->get_printings((Array)$vari->printings);
			}
		}
		
		return $vrt;
		
	}
	
	public function get_print_types(){

		global $lumise;

		return array(
			'multi' => array(
				'options' => array(
					'text' => $lumise->lang('Text'),
					'clipart' => $lumise->lang('Clipart'),
					'images' => $lumise->lang('Images'),
					'vector' => $lumise->lang('Vector'),
					'upload' => $lumise->lang('Upload')
				),
				'default' => array(
					5 => array(
						'text' => 1,
						'clipart' => 1,
						'images' => 1,
						'vector' => 1,
						'upload' => 1
					)
				),
				'label' => $lumise->lang('Calculate price with Text, Clipart, Images, Upload'),
				'desc' => $lumise->lang('Set the price based on quantity range for each text, clipart, images, upload, Vector SVG')
			),
			'color' => array(
				'options' => array(
					'full-color' => $lumise->lang('Full Color'),
				),
				'default' => array(
					1 => array(
						'full-color' => 1
					),
				),
				'label' => $lumise->lang('Calculate price with one color'),
				'desc' => $lumise->lang('Allow setup price with one color of area design. Price of printing = Price of one color * colors number.')
			),
			'size' => array(
				'options' => array(
					'a0' => 'A0',
					'a1' => 'A1',
					'a2' => 'A2',
					'a3' => 'A3',
					'a4' => 'A4',
				),
				'default' => array(
					5 => array(
						'a0' => 1,
						'a1' => 1,
						'a2' => 1,
						'a3' => 1,
						'a4' => 1,
					),
				),
				'label' => $lumise->lang('Calculate price with size of area design'),
				'desc' => $lumise->lang('Allow setup price with paper size (A0, A1, A2, A3, A4, A5, A6). This size is size of area design.')
			),
			'fixed' => array(
				'options' => array(
					'price' => $lumise->lang('Price'),
				),
				'default' => array(
					5 => array(
						'price' => 1
					),
				),
				'label' => $lumise->lang('Price Fixed'),
				'desc' => $lumise->lang('Price is fixed on each view (front, back, left, right) of product design.')
			)
		);
	}
	
	public function get_uploaded_bases($uid = '') {
		
		$path = $this->main->cfg->upload_path.'products'.DS.(!empty($uid) ? $uid.DS : '');
		$items = array();
		
		if ($handle = opendir($path)) {
			while (false !== ($sub = readdir($handle))) {
				if ($sub != "." && $sub != "..") {
		            if (
		            	is_file($path.DS.$sub) &&
		            	(strpos($sub, '.png') !== false || strpos($sub, '.jpg') !== false || strpos($sub, '.svg') !== false)
		            ) {
		                $items[-filemtime($path.DS.$sub)] = (!empty($uid) ? $uid.'/' : '').$sub;
		            }/* else if (is_dir($path.DS.$sub) && $_handle = opendir($path.DS.$sub)) {
						while (false !== ($_sub = readdir($_handle))) {
				            if (
				            	is_file($path.DS.$sub.DS.$_sub) && 
				            	(
				            		strpos($_sub, '.png') !== false ||
				            		strpos($_sub, '.jpg') !== false ||
				            		strpos($_sub, '.svg') !== false
				            	) && 
				            	$_sub != "." && 
				            	$_sub != ".."
				            ) {
					            $items[-filemtime($path.DS.$sub.DS.$_sub)] = (!empty($uid) ? $uid.'/' : '').$sub.'/'.$_sub;
				            }
				        }
						closedir($_handle);
			        }*/   
		        }
	        }
			closedir($handle);
	    }
		
		ksort ($items);
		
        return array_values( $items );
        
	}
		
	function cart_item_from_template($data, $template) {
		
		if (isset($data['product_id']) && $data['product_id'] > 0) {
		
			$item = array(
				'id' => $data['product_id'],
				'cart_id' => isset($data['cart_id'])? $data['cart_id'] : time(),
				'qty' => isset($data['qty'])? $data['qty'] : 1,
				'qtys' => array(),
				'product_id' => $data['product_id'],
				'product_cms' => isset($data['product_cms'])? $data['product_cms'] : $data['product_id'],
				'product_name' => isset($data['product_name'])? $data['product_name'] : '',
				'price' => isset($data['price'])? $data['price'] : array(
		            'total' => 0,
		            'attr' => 0,
		            'printing' => 0,
		            'resource' => 0,
		            'base' => 0,
		        ),
				'attributes' => isset($data['attributes'])? $data['attributes'] : array(),
				'printing' => isset($data['printing'])? $data['printing'] : 0,
				'resource' => isset($data['resource'])? $data['resource'] : array(),
				'uploads' => isset($data['uploads'])? $data['uploads'] : array(),
				'design' => isset($data['design'])? $data['design'] : array(),
				'template' => isset($data['template'])? $data['template'] : '',
				'screenshots' => !empty($template)? array('front' => 'data:image/png;base64,'. base64_encode(file_get_contents($template))) : array()
			);
			return $item;
		}
		return false;
	}
	/*
	* Add more item to session cart
	*/
	public function add_item_cart($item){
		$cart_data = $this->main->connector->get_session('lumise_cart');
		if($cart_data == null)
			$cart_data['items'] = array();
			
		$cart_data['items'][$item['cart_id']] = $item;
		$this->main->connector->set_session('lumise_cart', $cart_data);
	}
	
	public function remove_cart_item( $cart_id, $data = null ){
		
		global $lumise, $lumise_cart_adding;
		
		$cart_data = $lumise->connector->get_session('lumise_cart');
		$items = $lumise->connector->get_session('lumise_cart_removed');
		$removed_items = ($items == null)? array() : $items;
		$removed_items[] = $cart_id;
		
		//remove file data
		if( isset( $data[ 'file' ] ) ){
			$path = $lumise->cfg->upload_path . 'user_data'. DS . $data[ 'file' ]. '.tmp';
		}else if( $data == null) {
			$path = $lumise->cfg->upload_path . 'user_data'. DS . $cart_data[ 'items' ][ $cart_id ]['file'] . '.tmp';
		}
		
		@unlink( $path );
		
		if( !isset($lumise_cart_adding) ) {
			
			unset($cart_data['items'][$cart_id]);
			
			$this->main->connector->set_session('lumise_cart', $cart_data);
			$this->main->connector->set_session('lumise_cart_removed', $removed_items);
		}
	}

	public function store_cart($order_id, $cart_data){
		
		global $lumise;
		
		$db = $lumise->get_db();
		$insert_fail = 0;
		
		$time = time();
		$date = @date ("Y-m-d H:i:s");
		$design_path = $lumise->cfg->upload_path.'designs';
		$order_path = $lumise->cfg->upload_path.'orders';
		
		$checkupl = $lumise->check_upload($time);
				
		if ($checkupl !== 1) {
			return array(
				'error' => 1,
				'msg' => $checkupl
			);
		}
		
		$last_checkout = array();

		if (
			!$cart_data || 
			!isset($cart_data['items']) || 
			!is_array($cart_data['items']) || 
			count($cart_data['items']) == 0
		) {
			$lumise->logger->log('Lumise log for order ID#' . $order_id.' '.date ("Y-m-d H:i:s").' - cart_data session is empty or no items');
			return true;
		}

		$stagesArr = array();
		
		foreach ($cart_data['items'] as $key => $item){
			
			$screenshots = array();
			$print_files = array();
			
			/*
			* get data from file
			*/
			
			$extra_data = $this->get_cart_item_file( $item['file'] );
			
			/*
			* save screenshots
			*/
			
			if(
				(
					!isset($item['template']) || 
					false === $item['template']
				) &&
				isset($extra_data['screenshots']) &&
				count($extra_data['screenshots']) > 0
			){
				foreach ($extra_data['screenshots'] as $stage => $screenshot) {
					
					$scr_file_name = date('Y', $time).DS.date('m', $time).DS.$lumise->generate_id().'.png';
					$scr_name = $order_path . DS . $scr_file_name;
					
					/*
					*	Stored screenshot in editor-checkout step
					*/
					if (strpos($screenshot, 'data:image') === false) {
						$screenshot = str_replace('/', DS, $screenshot);
						if (
							is_file($lumise->cfg->upload_path.$screenshot) &&
							rename($lumise->cfg->upload_path.$screenshot, $scr_name)
						) array_push($screenshots, $scr_file_name);
						continue;
					};
					
					if(
						!@file_put_contents(
							$scr_name,
							base64_decode(
								preg_replace('#^data:image/\w+;base64,#i', '', $screenshot)
							)
						)
					){
						return array(
							'error' => 1,
							'msg' => $lumise->lang('Could not save product screenshot').': '.$order_path
						);
					}
					
					array_push($screenshots, $scr_file_name);
					
				}
			}
			
			/*
			* check if is not template => save design to file
			*/
			
			$design_product = $item['template'];
			
			if (false === $item['template']){
				
				$extra_data['design']['options'] = $extra_data['options'];
				$extra_data['design']['printing'] = $extra_data['printing'];
				$extra_data['design']['product'] = $extra_data['product_id'];
				$extra_data['design']['product_cms'] = $extra_data['product_cms'];
				$extra_data['design']['template'] = $extra_data['template'];
				
				if (isset($extra_data['design']['stages'])) {
					
					$isf = 0;
					
					foreach ($extra_data['design']['stages'] as $s => $sdata) {
						
						$isf++;
						
						if (isset($sdata['print_file'])) {
							
							$scr_file_name = date('Y', $time).DS.date('m', $time).DS.$lumise->generate_id().'-stage'.$isf.'.png';

							$scr_file_name = $lumise->apply_filters('scr-file-name-stage', $scr_file_name, array('sdata' => $sdata, 'isf' => $isf));
							$scr_name = $order_path . DS . $scr_file_name;
							
							if (strpos($sdata['print_file'], 'data:image') === false)
								continue;
							
							if (@file_put_contents(
									$scr_name,
									base64_decode(
										preg_replace('#^data:image/\w+;base64,#i', '', $sdata['print_file'])
									)
								)
							){
								array_push($print_files, $scr_file_name);

								$sdata['limuse_print_file'] = $scr_file_name;
								array_push($stagesArr, $sdata);
							}
							
							unset($extra_data['design']['stages'][$s]['print_file']);
								
						}
					}

					$lumise->do_action('store-cart-stage', $order_id, array('stagesArr' => $stagesArr, 'qty' => $item['qty']) );
				}
				
				$design_raw = json_encode($extra_data['design']);
				
				$design_file = date('Y', $time).DS.date('m', $time).DS.$lumise->generate_id().'.lumi';
				
				if (!file_put_contents($design_path.DS.$design_file, $design_raw)){
					return array(
						'error' => 1,
						'msg' => $this->main->lang('Could not save design file')
					);
				}
				
				$design_product = str_replace('.lumi','', $design_file);
			}
			
			$insert_data = array(
				'order_id' => $order_id,
				'product_base' => $item['product_id'],
				'product_id' => $item['product_cms'],
				'cart_id' => $item['cart_id'],
				'data' => $this->enjson(array(
					'attributes'	=> $item['attributes'],
					'printing'		=> $item['printing'],
					'variation'		=> $item['variation']
				)),
				'screenshots' => json_encode($screenshots),
				'print_files' => json_encode($print_files),
				'created' => $date,
				'updated' => $date,
				'product_price' => floatval($item['price']['total']),
				'product_name' => $item['product_name'],
				'currency' => $lumise->cfg->settings['currency'],
				'qty' => $item['qty'],
				'design' => $design_product,
				'custom' => (false === $item['template']) ? 1 : 0,
				'author' => $lumise->vendor_id,
			);

			$id = $db->insert('order_products', $insert_data);
			
			/*
			*	unset some data to use in thankyou page
			*	remove cart extra data from file
			*/
			
			if( isset($item[ 'file' ]) ){
				$path = $this->main->cfg->upload_path . 'user_data'. DS . $item[ 'file' ];
				@unlink( $path );
			}
			
			if (!$id)
				$insert_fail++;
			
			unset($item['attributes']);
			$last_checkout[$item['cart_id']] = $item;
			
		}
		
		unset($cart_data);
			
		$lumise->connector->set_session('lumise_cart', array('items' => array()));
		$lumise->connector->set_session('lumise_last_checkout', array('items' => $last_checkout ) );
		$lumise->connector->set_session('lumise_checkout', true);
		
		if ($insert_fail > 0) {
			return array(
				'error' => 1,
				'msg' => 'Fail to insert item to lumise.order_products ('.$insert_fail.' items failed)'
			);	
		}
		
		/*
		*	After finishing an order
		*/
			
		$lumise->do_action('store-cart', $order_id);
		
		return true;
		
	}
	
	public function price( $price ) {
		
		global $lumise;
		
		$price = number_format(
			floatval($price),
			intval($lumise->cfg->settings['number_decimals']),
			$lumise->cfg->settings['decimal_separator'],
			$lumise->cfg->settings['thousand_separator']
		);
		
		return (
			$lumise->cfg->settings['currency_position'] === '0' ? 
			$price . $lumise->cfg->settings['currency'] : 
			$lumise->cfg->settings['currency'] . $price
		);
		
	}
	
	public function get_xitems_by_category(
		$cate_id, 
		$search_filter = '', 
		$orderby = 'name', 
		$order = 'asc', 
		$limit = 10, 
		$limitstart = 0, 
		$type = 'products'
	) {

		global $lumise;
        $data = array();
       	$db = $lumise->get_db();
		$db->join("categories_reference c", "p.id=c.item_id", "LEFT");
		$db->where("c.category_id", $cate_id);
		$db->where("c.type", $type);

		if($orderby != null &&  $order != null)
       		$db->orderBy($orderby, $order);

		$data['rows'] = $db->withTotalCount()->get("{$type} p", array($limitstart, $limit), "p.*");
		$data['total_count'] = $db->totalCount;
        $data['total_page'] = ($limit != null) ? ceil($db->totalCount / $limit) : 0;

        return $data;

	}
	
	public function get_by_category(
		$cate_id, 
		$orderby = 'name', 
		$order = 'asc', 
		$limit = 10, 
		$limitstart = 0, 
		$type = 'products', 
		$default_filter = null
	) {

		global $lumise;
		
        $data = array();
       	$db = $lumise->get_db();
		$db->join("categories_reference c", "p.id=c.item_id", "LEFT");
		$db->where("c.category_id", $cate_id);
		$db->where("c.type", $type);
		$db->where("c.author", $this->main->vendor_id);
		
		if (is_array($default_filter) && count($default_filter) > 0) {

        	foreach ($default_filter as $key => $value) {
	        	if ($value != null)
        			$db->where($key, $value);
        		else $db->where($key);
        	}

        }

		if($orderby != null &&  $order != null)
       		$db->orderBy($orderby, $order);

		$data['rows'] = $db->withTotalCount()->get("products p", array($limitstart, $limit), "p.id, p.name, p.price, p.thumbnail_url");
		$data['total_count'] = $db->totalCount;
        $data['total_page'] = ($limit != null) ? ceil($db->totalCount / $limit) : 0;

        return $data;

	}

	public function get_rows(
		$tb_name, 
		$filter = array(), 
		$orderby = 'name', 
		$order = 'asc', 
		$limit = 10, 
		$limitstart = 0, 
		$default_filter = null, 
		$type = null
	) {

        global $lumise;
        $db = $lumise->get_db();

        $data = array();
		
		$db->where('author', $this->main->vendor_id);
		
        if (is_array($filter) && isset($filter['keyword']) && !empty($filter['keyword'])) {

            $fields = explode(',', $filter['fields']);
            $arr_keyword = array();

            for ($i = 0; $i < count($fields); $i++) {
				$arr_keyword[] = $filter['keyword'];
            }

            $fields = '('.implode(' LIKE ? or ', $fields).' LIKE ?)';
            $db->where($fields, $arr_keyword);

        }

        if (is_array($default_filter) && count($default_filter) > 0) {

        	foreach ($default_filter as $key => $value) {
	        	if ($value != null)
        			$db->where($key, $value);
        		else $db->where($key);
        	}

        }

        if ($type != null) {
        	$db->where('type', $type);
        }

        if($orderby != null &&  $order != null){
			$db->orderBy($orderby, $order);
		}
       		

        $limit_cfg = array($limitstart, $limit);
        if($limitstart == null &&  $limit == null)
        	$limit_cfg = null;

        $data['rows'] = $db->withTotalCount()->get($tb_name, $limit_cfg);
        $data['total_count'] = $db->totalCount;
        $data['total_page'] = ($limit != null) ? ceil($db->totalCount / $limit) : 0;

        return $data;
    }

	public function get_rows_custom($arr, $tb_name, $orderby = 'name', $order='asc') {

		global $lumise;
		
		$db = $lumise->get_db();
		$db->where('author', $this->main->vendor_id);
		$db->orderBy($orderby, $order);
		$slug = $db->get ($tb_name, null, $arr);

		return $slug;

	}

	public function get_row_id($id, $tb_name) {

		global $lumise;
		$db = $lumise->get_db();
		$db->where ('id', $id);
		//$db->where('author', $this->main->vendor_id);
		
		$item = $db->getOne ($tb_name);
		
		if (isset($item['author']) && $item['author'] != $this->main->vendor_id) {
			$lumise->connector->set_session(
				'lumise_msg', 
				array(
					'status' => 'error', 
					'errors' => array($this->main->lang('Error, Access denied on editing this section!'))
				)
			);
			$item = array();	
		}
		
		return $item;
	}

	public function get_rows_limit($tb_name, $limit ) {

		global $lumise;
		$db = $lumise->get_db();
		$db->where('author', $this->main->vendor_id);

		$arts = $db->get($tb_name, $limit);

		return $arts;

	}

	public function get_rows_total($tb_name, $col = null, $val = null) {

		global $lumise;
		$db = $lumise->get_db();
		
		$db->where('author', $this->main->vendor_id);
		
		if (!empty($col)) {
			$db->where ($val, $col);
		}

		$db->get($tb_name);
		$total = $db->count;

		return $total;

	}

	public function add_row( $data, $tb_name ) {

		global $lumise;
		
		$db = $lumise->get_db();
		
		$data['author'] = $this->main->vendor_id;
		
		$id = $db->insert($tb_name, $data);
		
		$lumise->do_action ('add_row', $id, $data, $tb_name);
		
        return $id;

	}

	public function edit_row( $id, $data, $tb_name ) {
		
		global $lumise;
		$db = $lumise->get_db();
		
		$check_per = $db->rawQuery(
			sprintf(
				"SELECT * FROM `%s` WHERE `id`=%d",
				$db->prefix.$tb_name,
				$id
			)
		);
		
		if (count($check_per) > 0) {
			
			if (
				!isset($check_per[0]['author']) ||
				$check_per[0]['author'] == $this->main->vendor_id
			) {
				
				$lumise->do_action ('edit_row', $id, $data, $tb_name);
				
				$db->where ('id', $id);
				$db->update ($tb_name, $data);
				
				return $id;
				
			} else if (
				isset($check_per[0]['author']) && 
				$check_per[0]['author'] != $this->main->vendor_id
			) {
				if (!isset($data['errors']) || !is_array($data['errors'])) {
					$data['errors'] = array();
				}
				
				return array('error' => $this->main->lang('Error, Access denied on changing this section!'));
				
			}
		} 

	}

	public function delete_row($id, $tb_name) {

		global $lumise;
		
		$lumise->do_action ('delete_row', $id, $tb_name);
		
		$db = $lumise->get_db();
		$db->where('id', $id);
		$db->where('author', $this->main->vendor_id);
		
		$id = $db->delete($tb_name);
		
		return $id;

	}

	public function add_count($val, $arr, $count = 1) {

		$val_temp = $val;
		while (in_array($val_temp, $arr)) {
			$val_temp = $val.'-'.$count;
			$count++;
		}

		return $val_temp;

	}

	public function get_tag_item($item_id, $type){

		global $lumise;
		$db = $lumise->get_db();
		$db->join("tags_reference tf", "t.id=tf.tag_id", "LEFT");
		$db->where("tf.item_id", $item_id);
		$db->where("tf.type", $type);
		$db->where('t.author', $this->main->vendor_id);
		$result = $db->get ("tags t", null, "t.id, t.name");

		return $result;

	}
	
	public function get_template($id = 0){
		
		global $lumise;
		
		$db = $lumise->get_db();
		$db->where ('id', (int)$id);
		
		//$db->where('author', $this->main->vendor_id);
		
		return $db->getOne ('templates');
		
	}
		
	public function get_order($id = 0){
		
		global $lumise;
		
		$db = $lumise->get_db();
		$db->where ('id', (int)$id);
		$db->where('author', $this->main->vendor_id);
		
		return $db->getOne ('orders');
		
	}
	
	public function get_order_products($order_id = 0){
		
		global $lumise;
		
		$db = $lumise->get_db();
		$db->where ('order_id', (int)$order_id);
		$db->where('author', $this->main->vendor_id);
		
		return $db->get('order_products');
		
	}
	
	/** counting all resource **/
	public function stats(){
		
		global $lumise;
		
		$db = $lumise->get_db();
		
		
		$data = array();
		
		foreach(array('cliparts', 'designs', 'fonts', 'tags', 'shapes', 'templates') as $tb_name){
			$db->where('author', $this->main->vendor_id);
			$db->withTotalCount()->get($tb_name);
	        $data[$tb_name] = $db->totalCount;
		}
		
		return $data;
	}
	
	public function recent_resources() {
		
		$resources = array();
		
		foreach(array('cliparts' => 9, 'templates' => 6) as $tb_name => $limit){
			$data = $this->get_rows($tb_name, array(), 'updated', 'desc', $limit);
			$resources[$tb_name] = $data['rows'];
			if(in_array($tb_name, array('cliparts', 'templates') )){
				foreach($resources[$tb_name] as $key => $item){
					
					$resources[$tb_name][$key]['categories'] = array();
					$cats = $this->get_category_item($item['id'], $tb_name);
					
					if(isset($item['screenshot']))
						$resources[$tb_name][$key]['thumbnail_url'] = $item['screenshot'];
					
					foreach ($cats as $cat) {
						array_push($resources[$tb_name][$key]['categories'], $cat['name']);
					}
				}
			}
		}
		
		return $resources;
	}
	
	protected function get_category_item($id, $type){
		
		$query = array(
			"SELECT cat.* ",
			"FROM `{$this->main->db->prefix}categories` cat",
			"INNER JOIN `{$this->main->db->prefix}categories_reference` cref ON cat.`id` = cref.`category_id` AND cref.`type` = '{$type}'",
			"WHERE `cat`.`author`='{$this->main->vendor_id}' AND cref.`item_id` = {$id}",
			"ORDER BY `cat`.`name` ASC"
		);
		
        $items = $this->main->db->rawQuery(implode(" ", $query));
		
		return $items;
	}
		
	public function get_share($id){
		
		$share = $this->main->db->rawQuery("SELECT * FROM `{$this->main->db->prefix}shares` WHERE `author`='{$this->main->vendor_id}' AND `share_id` = '{$id}'");
		
		if ($share && count($share) > 0)
			return $share[0];
		else return null;
	}
	
	protected function getShares($index = 0) {
		
		$stream = $this->main->lib->esc('stream');
		$aid = $this->main->connector->cookie('lumise-AID');
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM `{$this->main->db->prefix}shares` WHERE `author`='{$this->main->vendor_id}' ";
		
		if ($stream != 'all')
			$query .= "AND `aid`='{$aid}' ";
			
		$query .= "ORDER BY `created` DESC LIMIT {$index}, 20";

        $items = $this->main->db->rawQuery($query);
        $total = $this->main->db->rawQuery("SELECT FOUND_ROWS() AS count");
        
        if (count($total) > 0 && isset($total[0]['count'])) {
			$total = $total[0]['count'];
		}else $total = 0;
		
		return array($items, $total);
		
	}
	
	public function product_cfg($cfg = array()) {
		
		global $lumise;
		
		$product = $this->get_product();

		$has_template = 0;
		
		if ($product !== null) {
			
			// if(isset($product['ext_attributes']) && !empty($product['ext_attributes'])){
			// 	foreach ($product['ext_attributes'] as $key => $detailArr) {
			// 		if(strpos($key, ' ') !== false){
			// 			$newKey = preg_replace('/[ ]+/', '-',  $key);
			// 			$product['ext_attributes'][$newKey] = $detailArr;
			// 			unset($product['ext_attributes'][$key]);
			// 		}
			// 	}
			// }

			$product['stages'] = $lumise->lib->dejson($product['stages']);
			$product['attributes'] = $lumise->lib->dejson($product['attributes']);

			if (is_array($product['printings'])) {
				foreach ($product['printings'] as $key => $value) {
					$product['printings'][$key]['calculate'] = $lumise->lib->dejson($value['calculate']);
				}
			}
			
			foreach ($product['stages'] as $name => $data) {
				if (isset($data->template) && isset($data->template->id)) {
					$template = $lumise->lib->get_template($data->template->id);
					if (isset($template['upload'])) {
						$data->template->upload = $template['upload'];
						$data->template->price = isset($template['price']) ? $template['price'] : 0;
					}
					$has_template = 1;
				}
			}
			
			unset($product['templates']);
			$cfg['has_template'] = $has_template;
			
			$cfg['onload'] = $product;
		
		}
		
		$cfg['enable_colors'] = $lumise->cfg->settings['enable_colors'];
		$cfg['colors'] = $lumise->cfg->settings['colors'];
		
		if (isset($_POST['share']) && !empty($_POST['share'])) {
			
			$share_id = $_POST['share'];
			$share = $lumise->lib->get_share($_POST['share']);
			$pdbase = (isset($_POST['product_base']) ? $_POST['product_base'] : '');
			if (
				$share === null || $share['product'] != $pdbase
			) {
				$cfg['share_invalid'] = $lumise->lang('Oops, The link share is invalid or has been removed by admin');
			} else {
				
				$history = $this->main->connector->get_session('lumise_shares_access');
				
				if (!isset($history))
					$history = array();
					
				if (!in_array($share_id, $history)) {
					array_push($history, $share_id);
					$this->main->db->where('id', $share['id']);
					$this->main->db->update('shares', array('view' => $share['view']+1));
					$this->main->connector->set_session('lumise_shares_access', $history);
				}
				
				$cfg['share'] = date('Y', strtotime($share['created'])).'/'.date('m', strtotime($share['created'])).'/'.$share['share_id'];
					
			}
		}
		
		return $cfg;

	}
	
	public function check_sys_update() {
		
		$check = array('<b><font color="#555">Can not update because the system errors below:</font></b><hr>');
		$ml = ini_get('memory_limit');
		
		$ml = (int)str_replace('M', '', $ml);
		
		if (!ini_get('allow_url_fopen') && ini_get('allow_url_fopen') != -1)
			array_push($check, 'The function fopen() has been disabled on your server');
		
		if ($ml < 10)
			array_push($check, 'Your server memory limit must be configured to greater than 20M');
		
		if (!class_exists('ZipArchive'))
			array_push($check, 'The class ZipArchive has been disabled on your server');
		
		if (count($check) === 1) {
			return true;
		} else {
			return $check;
		}
		
	}
	
	public function delete_dir($from) {
		
	    if (!file_exists($from)) {
		    return false;
	    }
	    
	    $dir = @opendir($from);
	    
	    while (false !== ($file = @readdir($dir))) {
	        if ($file != '.' && $file != '..') {
		        if (is_dir($from.DS.$file))
		            $this->delete_dir($from.DS.$file);
		        else if (is_file($from.DS.$file))
		        	@unlink($from.DS.$file);
	        }
	    }
	    
	    @rmdir($from);
	    @closedir($dir);
	    
	    return true;
	    
	}
	
	public function delete_files($target) {
		
	    if(is_dir($target)){
		    
	        $files = glob( $target . '*', GLOB_MARK );
	        
	        foreach( $files as $file )
	        {
	            $this->delete_files( $file );  
	        }
	      
	        @rmdir( $target );
	        
	    } elseif(is_file($target)) {
	        @unlink( $target );  
	    }
	}
	
	public function delete_order_products($order_id) {
		
		if(empty($order_id)) return;
		
		global $lumise;
		
		$products = $this->get_order_products($order_id);
		foreach($products as $product){
			if(isset($product['custom']) && $product['custom']){
				$design_path = realpath($lumise->cfg->upload_path).DS . 'designs'. DS . $product['design'].'.lumi';
				$this->delete_files($design_path);
			}	
		}
		
		$db = $lumise->get_db();
		$db->where('order_id', $order_id);
		$db->where('author', $lumise->vendor_id);
		$db->delete('order_products');
		
		//delete order folder
		$path = realpath($lumise->cfg->upload_path).DS . 'orders'. DS . $order_id;
		$this->delete_files($path);
	}
	
	public function report_bug_lumise($id = 0) {
		
		global $lumise;
		$db = $lumise->get_db();

		$db->where ('id', $id);
		$db->where ('author', $lumise->vendor_id);

		$bug = $db->getOne ('bugs');
		
		if ($bug) {
			$arg = array(
				'reporting-channel=backend',
				'content='.base64_encode(urlencode($bug['content'])),
				'domain='.urlencode($lumise->cfg->url),
				'created='.$bug['created'],
				'updated='.$bug['updated']
			);
			
			$url = (strpos($lumise->cfg->url, 'https') === 0 ? 'https' : 'http').'://bugs.lumise.com';
			$arg = implode('&', $arg);
			
			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_POST, 1);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $arg);
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt( $ch, CURLOPT_HEADER, 0);
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
			
			$response = curl_exec( $ch );
			
			if ($response != 0) {
				$db->where ('id', $id);
				$db->update ('bugs', array(
					'lumise' => 1,
					'status' => 'pending',
					'updated' => date("Y-m-d").' '.date("H:i:s")
				));
				return 1;
			}else return 0;
		}else return 0;
		
	}
	
	public function stripallslashes($string) { 
	    while(strchr($string,'\\')) { 
	        $string = stripslashes($string); 
	    }
		return stripslashes($string);
	}
	
	public function save_cart_item_file( $data ) {
		
		global $lumise;
		
		@ini_set('memory_limit','5000M');
		
		$check = $lumise->check_upload(time());
		
		if ($check !== 1)
			return false;
		
		$datepath = date('Y', time()).DS.date('m', time()).DS;
		$filename = $datepath.$data[ 'cart_id' ] . '_' . $this->gen_str();
		
		$path = $lumise->cfg->upload_path . 'user_data'. DS . $filename . '.tmp';
		
		$screenshots = array();
		
		foreach ($data[ 'screenshots' ] as $scr) {
			$fnam = uniqid().(strpos($scr, "image/png") !== false ? '.png' : '.jpg');
			$scr = explode(',', $scr);
			if(count($scr) < 2) continue;
			$scr = base64_decode($scr[1]);
			file_put_contents( $lumise->cfg->upload_path . 'user_data'. DS.$datepath.$fnam, $scr );
			array_push($screenshots, 'user_data/'.str_Replace(DS, '/', $datepath).$fnam);
		}
		
		$data[ 'screenshots' ] = base64_encode( json_encode( $screenshots ) );
		
		$content = json_encode( $data );
		$res = file_put_contents( $path, $content );
		
		if ( $res === FALSE )
			return false;
		else return $filename;
		
	}
	
	public function get_cart_item_file( $filename ) {
		
		@ini_set('memory_limit','5000M');
		
		$path = $this->main->cfg->upload_path . 'user_data'. DS . $filename . '.tmp';
		
		if (@file_exists($path)) {
			
			$data = json_decode(file_get_contents($path), 1);
			$data['screenshots'] = json_decode(base64_decode($data['screenshots']), 1);
			
			return $data;
			
		}else return null;
		
	}
	
	public function get_cart_data( $lumise_data ) {
		
		global $lumise;
		
		if (!isset($lumise_data[ 'file' ])) 
			return null;
		
		$file = $lumise_data[ 'file' ];
		$file_data = $this->get_cart_item_file( $file );
		
		return ($file_data== null) ? $lumise_data : $file_data;

	}	

	public function cart_meta($cart_data) {
		
		$custom_items = array();
		
		if ( is_array($cart_data ) ){
			
			foreach ( $cart_data['attributes'] as $aid => $attr ) {
				
				if (isset($attr['value']) ) {
					
					$val = $attr['value'];
					
					if (
						($attr['type'] == 'color' || 
						$attr['type'] == 'product_color') &&
						is_array($attr['values']) &&
						is_array($attr['values']['options'])
					) {
						foreach ($attr['values']['options'] as $v) {
							if (trim($val) == trim($v['value'])) {
								$val = '<span style="background-color: '.$v['value'].';padding: 1px 3px;" title="'.$v['value'].'">'.$v['title'].'</span>';
							}
						}
					} else if ($attr['type'] == 'quantity') {
						
						if ( is_array(@json_decode($val, true)) ) {
							$val = @json_decode($val, true);
							foreach ($val as $k => $v) {
								$val[$k] = '<p><strong>'.$k.':</strong>'.$v.'</p>';
							}
							$val = implode('', array_values($val));
						} else $val = $attr['value'];
					} else if(
						is_array($attr['values']) &&
						isset($attr['values']['options']) &&
						is_array($attr['values']['options'])
					){
						foreach ($attr['values']['options'] as $v) {
							if (trim($val) == trim($v['value'])) {
								$val = $v['title'];
							}
						}		
					}
					
					$custom_items[] = array( 
						"name" => $attr['name'], 
						"value" => $val
					);
				}
				
			}
		}
		
		return $custom_items;
		
	}

    protected function gen_str( $max = 10 ) {
        
        $sources = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len_sourcses = strlen( $sources );
        $str = '';
        for ( $i = 0; $i < $max; $i++ ) {
            $str .= $sources[ rand( 0, $len_sourcses - 1 ) ];
        }
        
        return $str;
    }
    
    public function get_file_data( $file, $default_headers, $context = '' ) {

	        $fp = fopen( $file, 'r' );
	        $file_data = fread( $fp, 8192 );
	        fclose( $fp );
	        $file_data = str_replace( "\r", "\n", $file_data );

	        if ( $context && $extra_headers = apply_filters( "extra_{$context}_headers", array() ) ) {
	                $extra_headers = array_combine( $extra_headers, $extra_headers );
	                $all_headers = array_merge( $extra_headers, (array) $default_headers );
	        } else {
	                $all_headers = $default_headers;
	        }
	
	        foreach ( $all_headers as $field => $regex ) {
	                if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
	                        $all_headers[ $field ] = $this->_cleanup_header_comment( $match[1] );
	                else
	                        $all_headers[ $field ] = '';
	        }
	
	        return $all_headers;
	}
	
    public function _cleanup_header_comment( $str ) {
	    return trim(preg_replace("/\s*(?:\*\/|\?>).*/", '', $str));
	}
	
	public function sanitize_title($title = '') {
		return trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($title)), '-');
	}
	
    public function remove_dir ($dirPath = '') {

		if (empty($dirPath))
			return false;
	
		$dirPath = rtrim( $dirPath, '/\\' ).DS;

		if ( !defined('ABSPATH') ){
			define('ABSPATH', dirname(__FILE__) . '/');
		}

		if (! is_dir($dirPath)) {
	        return false;
	    }
	
		if (defined(ABSPATH) && $dirPath == ABSPATH)
			return false;

	
	    $files = scandir($dirPath, 1);
	
	    foreach ($files as $file) {
		    if ($file != '.' && $file != '..') {
		        if (is_dir($dirPath.$file)) {
		        	$this->remove_dir($dirPath.$file);
		        } else {
		            unlink($dirPath.$file);
		        }
	        }
	    }
	
	    if (is_file($dirPath.'.DS_Store'))
	    	unlink($dirPath.'.DS_Store');
	
	    return rmdir($dirPath);
	
	}
    
    public function get_system_status() {
	    
	    $check = array(
		    'ZipArchive()' => true,
		    'allow_url_fopen' => true,
		    'file_put_contents()' => true,
		    'file_get_contents()' => true,
		    'memory_limit' => 0,
		    'post_max_size' => 0,
		    'upload_max_filesize' => 0,
		    'max_execution_time' => 0
	    );
	    
		$ml = ini_get('memory_limit');
		$check['memory_limit'] = (int)str_replace('M', '', $ml);
		
		$pmz = ini_get('post_max_size');
		$check['post_max_size'] = (int)str_replace('M', '', $pmz);

		$umz = ini_get('upload_max_filesize');
		$check['upload_max_filesize'] = (int)str_replace('M', '', $umz);

		$met = ini_get('max_execution_time');
		$check['max_execution_time'] = (int)$met;
		
		if (!ini_get('allow_url_fopen') && ini_get('allow_url_fopen') != -1)
			$check['allow_url_fopen'] = false;
		
		if (!function_exists('file_put_contents'))
			$check['file_put_contents()'] = false;
		
		if (!function_exists('file_get_contents'))
			$check['file_get_contents()'] = false;
			
		if (!class_exists('ZipArchive'))
			$check['ZipArchive'] = false;
		
		return $check;
		
    }
    
    public function display_check_system() {
	    
	    $check = $this->get_system_status();
	    $amount = 0;
	    
	    foreach ($check as $key => $val) {
			if ($key == 'memory_limit') {
				if ($val < 250)
					$amount++;
			}else if ($key == 'post_max_size') {
				if ($val < 100)
					$amount++;
			}else if (!$val)$amount++;
	    }
	    if ($amount > 0) {
	?>
		<div class="lumise-col lumise-col-12">
			<div class="lumise-update-notice top">
				<?php echo $this->main->lang('We found'); ?> <?php echo $amount; ?> <?php echo $this->main->lang('misconfiguration(s) on your server that may cause the system to operate incorrectly'); ?>. 
				&nbsp; 
				<a href="<?php echo $this->main->cfg->admin_url; ?>lumise-page=system">
					<?php echo $this->main->lang('System status'); ?> &#10230;
				</a>
			</div>
		</div>
	<?php 
		}
    }
    
    public function get_product_colors() {
	    
	    $colors = array("active"=> "", "colors" => array());
	    
	    if (!isset($_GET['id']))
	    	return $colors;
	    
	    $product = $this->main->db->rawQuery("SELECT `color` FROM `".$this->main->db->prefix."products` WHERE `author`='{$this->main->vendor_id}' AND `id`=".(int)$_GET['id']);
	   
	    if (isset($product) && isset($product[0]) && isset($product[0]['color'])) {
		    $color = explode(":", $product[0]['color']);
		    $colors["active"] = $color[0];
		    $color[1] = explode(",", $color[1]);
		    foreach ($color[1] as $cl => $ck) {
			    $ck = explode("@", $ck);
			    $colors["colors"][$ck[0]] = array(
				    "label" => isset($ck[1]) ? $ck[1] : '',
				    "price" => "",
				    "images" => ""
			    );
		    }
	    }
	    
	    return (Object)$colors;
	    
    }
    
    public function upload_bimage($data, $path) {
	    
	    $type = $this->get_type($data);

		if ($type === '')
			return $this->main->lang('Could not save image because invalid type');

		$path .= $type;

		$data = explode(';base64,', $data);
		$data = @base64_decode($data[1]);
		
		$resp = array('success' => 0, 'msg' => '', 'type' => $type);
		
		if (empty($data)) {
			$resp['msg'] = $this->main->lang('Could not decode image data');
		} else if (!file_put_contents($path, $data)) {
			$resp['msg'] = $this->main->lang('Could not write data on the upload folder, please report to the administrator');
		} else {
			$resp['success'] = 1;
			$resp['type'] = $type;
		}
		
		return $resp;
		
    }
    
    public function get_color($attrs = '', $ops = array()) {
	    
	    $color = '#f0f0f0';
	   
	    if (isset($attrs) && !empty($attrs)) {
		    
		    if (is_string($attrs))
		  		$attrs = $this->dejson($attrs, true); 
			
		    if (is_array($attrs)) {
			    
			    foreach ($attrs as $id => $attr) {
				    
				    if (isset($attr['values']) && is_string($attr['values'])) {
						$attr['values'] = @json_decode($attr['values'], true);
					}
					
				    if (
				    	$attr['type'] == 'product_color' && 
				    	is_array($attr['values']) &&
				    	isset($attr['values']['options'])
				    ) {
					    $color = $attr['values']['options'][0]['value'];
					    foreach ($attr['values']['options'] as $op) {
						   if ($op['default'] == '1')
						   		$color = $op['value'];
						}
				    }
				    
				    if (
				    	$attr['type'] == 'product_color' &&
				    	isset($ops[$id])
				    ) $color = $ops[$id];
			    }
		    }
	    }
	    
	    return $color;
	    
    }
    
    public function render_css($data) {
		
		global $lumise;
		
		$color = $data['primary_color'];
		$custom_css = $data['custom_css'];
		$content = '';
		
		if (!empty($custom_css)) {
			$custom_css = str_replace(
				array('&gt;', '; ', ' }', '{ ', "\r\n", "\r", "\n", "\t",'  ','    ','    '),
				array('>', ';', '}', '{', '', '', '', '', '', '', ''),
				$custom_css
			);
		}
		
		$primary = $lumise->cfg->root_path.'assets'.DS.'css'.DS.'primary_color.css';
		$path = $lumise->cfg->upload_path.'user_data'.DS.'custom.css';
		
		if (is_file($primary)){
			
			$content = file_get_contents($primary);
			if (!empty($content)) {
				$_color = explode(':', $color);
				$content = str_replace(
					array('&gt;', '; ', ' }', '{ ', "\r\n", "\r", "\n", "\t",'  ','    ','    '),
					array('>', ';', '}', '{', '', '', '', '', '', '', ''),
					preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!','',
						str_replace('%color%', $_color[0], $content)
					)
				);
			}	
		}
		
		$content .= stripslashes($custom_css);
		
		if ($lumise->check_upload(time()))
			return @file_put_contents($path, $content);
		else return 0;
		
	}
    
    public function pdf_download($id) {
		
		$bleed = isset($_GET['bleed']) ? (float)$_GET['bleed'] : 0;
		
		$pdf_target =  $id.($bleed > 0 ? '-'.$bleed : '').'.pdf';
		
		if (is_file($this->main->cfg->upload_path.'user_data'.DS.'pdf'.DS.$pdf_target)) {
			header('location: '.$this->main->cfg->upload_url. 'user_data/pdf/'.$pdf_target);
		}
		
		global $lumise;
		
		$files = array();
		
		$cart_item = $this->main->db->rawQuery(
			sprintf(
				"SELECT * FROM `%s` WHERE `cart_id`='%s'",
				$this->main->db->prefix.'order_products',
				$id
			)
		);
		
		if (count($cart_item) > 0) {
			
			$prt = @json_decode($cart_item[0]['print_files'], true);
			
			foreach ($prt as $i => $s) {
				if (!empty($s) && is_file($this->main->cfg->upload_path.'orders/'.$s)) {
					array_push($files, $this->main->cfg->upload_path.'orders/'.$s);
				}
			}
						
		} else 
		{
			
			$tmps = @base64_decode($id);
			
			if ($tmps && !empty($tmps)) {
				$tmps = explode(',', $tmps);
				foreach ($tmps as $tmp) {
					if (!empty($tmp)) {
						$tem = $this->main->db->rawQuery(
							sprintf(
								"SELECT * FROM `%s` WHERE `id`=%d",
								$this->main->db->prefix.'templates',
								$tmp
							)
						);
						if (
							count($tem) > 0 && 
							strpos($tem[0]['upload'], '.lumi') === false && 
							is_file($this->main->cfg->upload_path.$tem[0]['upload'])
						) {
							array_push($files, $this->main->cfg->upload_path.$tem[0]['upload']);
						}
					}	
				}
			}
		}
		
		require_once('TCPDF'.DS.'tcpdf.php');
		
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(0, 0, 0, true);
		
		$pdf->SetFooterMargin(0);
		$pdf->SetAutoPageBreak(TRUE, 0);
		
		if (count($files) > 0) {
			
			if (!is_dir($this->main->cfg->upload_path. 'user_data'.DS.'pdf')) {
				@mkdir($this->main->cfg->upload_path. 'user_data'.DS.'pdf', 0755);	
			}
			
			foreach ($files as $file) {
				
				$file_info = getimagesize($file);
				
				$width = (21/248)*$file_info[0];
				$height = (21/248)*$file_info[1];
				
				$pdf->AddPage(($height>$width) ? 'P' : 'L', [$width+(2*$bleed), $height+(2*$bleed)]);
				
				if (isset($bleed) && $bleed > 0) {
					// corner crop marks
					$pdf->cropMark((2*$bleed), (2*$bleed), (2*$bleed), (2*$bleed), $type = 'A', $color = array(0, 0, 0));
					$pdf->cropMark($width, (2*$bleed), (2*$bleed), (2*$bleed), $type = 'B', $color = array(0, 0, 0));
					$pdf->cropMark((2*$bleed), $height, (2*$bleed), (2*$bleed), $type = 'C', $color = array(0, 0, 0));
					$pdf->cropMark($width, $height, (2*$bleed), (2*$bleed), $type = 'D', $color = array(0, 0, 0));
				}
				
				$pdf->Image($file, $bleed, $bleed, $width, $height, '', '', '', false, 300, '', false, false, 1, false, false, false);
			}	
			
			// Export file
			//$this->main->cfg->upload_path. 'user_data'.DS.'pdf'.DS.$id.'.pdf'
			//Close and output PDF document
			
			$pdf->Output($this->main->cfg->upload_path.'user_data'.DS.'pdf'.DS.$pdf_target, 'F');
			
			header('location: '.$this->main->cfg->upload_url. 'user_data/pdf/'.$pdf_target);
			
			exit;
			
		} else {
			die ('No print file found');
		}
		
		   
	}
    
    public function remote_connect($url = '', $data = array(), $headers = array(), $post = 0) {
	    
	    if (empty($url))
	    	return null;
	    
	    if (function_exists('curl_version')){
		  	
		    $ch = curl_init();
		    
		    curl_setopt($ch, CURLOPT_URL, $url);
		   
		    if ($post === 1) {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POST, 1);
		    }
		    
		    if (is_array($data) && count($data) > 0) {
		    	$data = http_build_query($data);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		    }
		    
		    if (is_array($headers) && count($headers) > 0)
		    	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		    
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 	
		    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			
		    $res = curl_exec($ch);
		    curl_close($ch);
		    
		    return $res;
		    
	    } else if (ini_get('allow_url_fopen')) {
		    return file_get_contents($url);
	    } else {
			return 'Error: Your server does not allow outbound connections (curl, fopen & file_get_contents)';   
		}
    }
	
}

class lumise_pagination {

	protected $_pagination = array(
		'current_page' => 1,
		'total_record' => 1,
		'total_page'   => 1,
		'limit'        => 10,
		'start'        => 0,
		'link_full'    => '',
		'link_first'   => ''
	);

	public function init( $pagination = array() ) {

		global $lumise;

		foreach ($pagination as $key => $value) {

			if (isset($this->_pagination[$key])) {
				$this->_pagination[$key] = $value;
			}

		}

		if ( $this->_pagination['limit'] < 0 ) {
			$this->_pagination['limit'] = 0;
		}

		if ( $this->_pagination['current_page'] < 1 ) {
			$this->_pagination['current_page'] = 1;
		}

		$this->_pagination['start'] = ( $this->_pagination['current_page'] - 1 ) * $this->_pagination['limit'];

		if ($_SERVER['REQUEST_METHOD'] =='POST' && LUMISE_ADMIN) {
			$admin_url = explode('?', $lumise->cfg->admin_url);
			$this->redirect($admin_url[0].'?'.$_SERVER['QUERY_STRING']);
		}

	}

	public function redirect($url){
		echo '<script type="text/javascript">window.location = "'.$url.'";</script>';
		exit();
	}

	private function link($page) {

		if ($page <= 1){
            return $this->_pagination['link_first'];
        }

        return str_replace('{page}', $page, $this->_pagination['link_full']);

	}

	public function pagination_html() {

		global $lumise;
		$result = '';

		if ( $this->_pagination['total_record'] > $this->_pagination['limit'] ) {

			$result = '<p>'.$lumise->lang('Showing').' '.(($this->_pagination['current_page']-1)*$this->_pagination['limit']).' '.$lumise->lang('to').' '.($this->_pagination['current_page']*$this->_pagination['limit'] < $this->_pagination['total_record'] ? $this->_pagination['current_page']*$this->_pagination['limit'] : $this->_pagination['total_record']).' '.$lumise->lang('of').' '.$this->_pagination['total_record'].' '.$lumise->lang('entries').'</p>';
			$result .= '<ul>';

			if ( $this->_pagination['current_page'] > 1 ) {
				$result .= '<li><a href="' . $this->link('1') . '"><i class="fa fa-angle-double-left"></i></a></li>';
				$result .= '<li><a href="' . $this->link($this->_pagination['current_page'] - 1) . '"><i class="fa fa-angle-left"></i></a></li>';
			} else {
				$result .= '<li><span class="none"><i class="fa fa-angle-double-left"></i></span></li>';
				$result .= '<li><span class="none"><i class="fa fa-angle-left"></i></span></li>';
			}

			$max = 7;
			if($this->_pagination['current_page'] < $max)
				$sp = 1;
			elseif($this->_pagination['current_page'] >= ($this->_pagination['total_page'] - floor($max / 2)))
				$sp = $this->_pagination['total_page'] - $max + 1;
			elseif($this->_pagination['current_page'] >= $max)
				$sp = $this->_pagination['current_page'] - floor($max / 2);

			if ($this->_pagination['current_page'] >= $max)
				$result .= '<li><span class="none">...</span></li>';

			for ($i = $sp; $i <= ($sp + $max - 1); $i++) { 
				
				if($i > $this->_pagination['total_page'])
					break;

				if ($this->_pagination['current_page'] == $i)
					$result .= '<li><span class="current bgcolor">'.$i.'</span></li>';
				else
					$result .= '<li><a href="' . $this->link($i) . '">'.$i.'</a></li>';

			}

			if ($this->_pagination['current_page'] < ($this->_pagination['total_page'] - floor($max / 2)))
				$result .= '<li><span class="none">...</span></li>';

			if ( $this->_pagination['current_page'] < $this->_pagination['total_page'] ) {
				$result .= '<li><a href="' . $this->link($this->_pagination['current_page'] + 1) . '"><i class="fa fa-angle-right"></i></a></li>';
				$result .= '<li><a href="' . $this->link($this->_pagination['total_page']) . '"><i class="fa fa-angle-double-right"></i></a></li>';
			} else {
				$result .= '<li><span class="none"><i class="fa fa-angle-double-right"></i></span></li>';
				$result .= '<li><span class="none"><i class="fa fa-angle-right"></i></span></li>';
			}

		} else {
			$result = '<p>'.$lumise->lang('Showing').' '.$this->_pagination['total_record'].' '.$lumise->lang('entries').'</p>';
		}

		return $result;

	}

}


/**
 * Logger class
 */
class lumise_logger{
	
	private $file_path;
	
	function __construct( $file_path = '' ) {
		if ( !empty($file_path) ) $this->file_path = $file_path;
	}
	
	public function log( $data ) {
		$pre_data = '-------------------' . "\n";
		$pre_data .= 'Date: '. date("F j, Y, g:i a") . "\n";
		file_put_contents( $this->file_path, $pre_data . $data, FILE_APPEND);
	}
}

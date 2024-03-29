<?php
/**
 * Return the error message related to the message id
 * @method nrvbd_error_message
 * @param  string $message_id
 * @return string
 */
function nrvbd_error_message(string $message_id)
{
    $messages = nrvbd_error_messages();

    if(isset($messages[$message_id])){
        return $messages[$message_id];
    }else{
        return '';
    }
}


/**
 * Return the list of messages
 * @method nrvbd_error_messages
 * @return array
 */
function nrvbd_error_messages()
{
    $messages = array();
	$messages['08201'] = array("message" => __("Successfully sent.", "nrvbd"),
							   "type" => "success");
    $messages['10201'] = array("message" => __("Successfully saved.", "nrvbd"),
							   "type" => "success");							   
    $messages['10202'] = array("message" => __("Successfully deleted.", "nrvbd"),
							   "type" => "warning");
    $messages['10400'] = array("message" => __("Malformed request.", "nrvbd"),
							   "type" => "error");
    $messages['10403'] = array("message" => __("Nonce invalid, please refresh the page.", "nrvbd"),
							   "type" => "error");
	$messages['10404'] = array("message" => __("Entity not found.", "nrvbd"),
							   "type" => "error");
	$messages['10500'] = array("message" => __("Internal server error.", "nrvbd"),
							   "type" => "error");
	$messages['11404'] = array("message" => __("Data not found.", "nrvbd"),
								"type" => "error");
    return $messages;
}


/**
 * Undocumented function
 * @method nrvbd_sql_esc
 * @param  string $value
 * @return void
 */
function nrvbd_sql_esc($value)
{
    if($value != ""){
        return "'".esc_sql($value)."'";
    }
    return 'NULL';
}


/**
 * Undocumented function
 * @method nrvbd_sql_esc_backticks
 * @param  string $value
 * @return void
 */
function nrvbd_sql_esc_backticks($value)
{
    return "`".esc_sql($value)."`";
}


function nrvbd_plugin_version()
{
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	$plugin_path = NRVBD_PLUGIN_PATH . 'brunch-delivery.php';
	$plugin_data = get_plugin_data($plugin_path);
	
	if(isset($plugin_data['Version'])){
		return $plugin_data['Version'];
	}
	return '0.8.0';
}

/**
 * Return the list of drivers
 * @method nrvbd_get_drivers
 * @param  array   $args
 * @param  boolean $load
 * @return \nrvbd\entities\driver[]|int[]
 */
function nrvbd_get_drivers(array $args = array(), bool $load = false)
{
    $default = array(
        "per_pages" => -1,
        "page" => 1,
		"deleted" => 0
    );
    $args = \nrvbd\helpers::set_default_values($default, $args, false);
    global $wpdb;
    $sql = "SELECT ID FROM {$wpdb->prefix}nrvbd_driver WHERE 1=1";

	if(isset($args['deleted']) && $args['deleted'] != ''){
		$sql .= " AND deleted = {$args['deleted']}";
	}

    if($args['per_pages'] > 0){
        $offset = $args['per_pages'] * $args['page'] - $args['per_pages'];
        $sql .= " LIMIT {$args['per_pages']} OFFSET {$offset}";
    }
	
    $ids = $wpdb->get_col($sql);
    if($load == false){
        return $ids;
    }

    $collection = array();
    foreach($ids as $id){
        $collection[] = new \nrvbd\entities\driver($id);
    }
    return $collection;
}


/**
 * Return the next sunday
 * @method nrvbd_next_delivery_date
 * @param  string $format
 * @param  string|int|null $timestamp
 * @return string
 */
function nrvbd_next_delivery_date($format = "d/m/Y", $timestamp = null)
{
	$dates = nrvbd_get_brunch_dates();
	if(empty($dates)){
		return '';
	}
	$today = $timestamp ?? time();
	foreach($dates as $date){
		$brunch_date = nrvbd_format_date('timestamp', $date);
		if($brunch_date > $today){
			return nrvbd_format_date($format, $date);
		}
	}
	return nrvbd_format_date($format, $date);
}


/**
 * Convert a date d/m/Y {H:i} to the wanted format
 * @method nrvbd_format_date
 * @param  string   $date
 * @param  string   $format
 * @return string
 */
function nrvbd_format_date(string $format, string $date)
{
	$pattern = '/^(\d{2})\/(\d{2})\/(\d{4})(?: (\d{2}):(\d{2}))?$/';
    if(preg_match($pattern, $date, $matches)){
        $Y = $matches[3];
        $m = $matches[2];
        $d = $matches[1];
        $h = isset($matches[4]) ? $matches[4] : '';
        $i = isset($matches[5]) ? $matches[5] : '';
	}
	$str_date = "{$Y}-{$m}-{$d}";
	if($h != '' && $i != ''){
		$str_date .= " {$h}:{$i}";
	}
	$time = strtotime($str_date);
	if($format == "timestamp"){
		return $time;
	}
	return date($format, $time);
}


/**
 * Return the list of product ids by brunch date
 * @method nrvbd_get_product_ids_by_brunch_date
 * @param  string $date
 * @return array()
 */
function nrvbd_get_product_ids_by_brunch_date(string $date)
{
	global $wpdb;
	$sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_brunch_date' AND meta_value = %s";
	return $wpdb->get_col($wpdb->prepare($sql, $date));
}


/**
 * Return the order ids which contains the product ids
 * @method nrvbd_get_orders_ids_by_product_ids
 * @param  array  $product_ids
 * @param  array  $args
 * @return array
 */
function nrvbd_get_orders_ids_by_product_ids(array $product_ids, array $args = array())
{
	$default = array(
		'order_status' => array('wc-completed', 'wc-processing'),
		'page' => 1,
		'per_pages' => -1
	);
	$args = \nrvbd\helpers::set_default_values($default, $args);
    global $wpdb;
	$sql = "SELECT order_items.order_id
			FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->prefix}wc_orders AS orders ON order_items.order_id = orders.ID
			WHERE orders.status IN ( '" . implode( "','", $args['order_status'] ) . "' )
			AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_item_meta.meta_value IN ('" . implode( "','", $product_ids ) . "')
			GROUP BY order_items.order_id";
	if($args['per_pages'] > 0){
		$offset = $args['per_pages'] * $args['page'] - $args['per_pages'];
		$sql .= " LIMIT {$args['per_pages']} OFFSET {$offset}";
	}
	return $wpdb->get_col($sql);
}


/**
 * Return the list of order for the given date
 * @method nrvbd_get_orders_by_brunch_date
 * @param  string $date
 * @return array
 */
function nrvbd_get_orders_by_brunch_date(string $date, array $args = array())
{
	$product_ids = nrvbd_get_product_ids_by_brunch_date($date);
	if(empty($product_ids)){
		return array();
	}

	$orders = array();
	$orders_ids = nrvbd_get_orders_ids_by_product_ids($product_ids, $args);
	
	foreach($orders_ids as $order_id){
		$order = wc_get_order($order_id);
		if(is_a( $order, 'WC_Order')){     
			$orders[] = $order;
		}
	}
	return $orders;
}


/**
 * Return the info for the nrvbd_get_orders_ids_by_product_ids query
 * @method nrvbd_get_orders_ids_by_product_ids_info
 * @param  array  $product_ids
 * @param  array  $args
 * @return array
 */
function nrvbd_get_orders_ids_by_product_ids_info(array $product_ids, array $args = array())
{
    global $wpdb;
	$default = array(
		'order_status' => array('wc-completed', 'wc-processing'),
		'per_pages' => -1
	);
	$args = \nrvbd\helpers::set_default_values($default, $args);
	$sql = "SELECT count(DISTINCT(order_items.order_id))
			FROM {$wpdb->prefix}woocommerce_order_items as order_items
			LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
			LEFT JOIN {$wpdb->prefix}wc_orders AS orders ON order_items.order_id = orders.ID
			WHERE orders.status IN ( '" . implode( "','", $args['order_status'] ) . "' )
			AND order_items.order_item_type = 'line_item'
			AND order_item_meta.meta_key = '_product_id'
			AND order_item_meta.meta_value IN ('" . implode( "','", $product_ids ) . "')";
	$total = $wpdb->get_var($sql);
	$pages = 1;
	if($args['per_pages'] > 0){
		$pages = ceil($total / $args['per_pages']);
	}
	return array(
		"total" => $total,
		"pages" => $pages
	);
}


/**
 * Return information for the nrvbd_get_orders_by_brunch_date query
 * @method nrvbd_get_orders_by_brunch_date_info
 * @param  string $date
 * @param  array  $args
 * @return array
 */
function nrvbd_get_orders_by_brunch_date_info(string $date, 
											  array $args = array())
{
	$product_ids = nrvbd_get_product_ids_by_brunch_date($date);
	if(empty($product_ids)){
		return array();
	}
	return nrvbd_get_orders_ids_by_product_ids_info($product_ids, $args);
}


/**
 * Return the list of brunch dates
 * @method nrvbd_get_brunch_dates
 * @return array
 */
function nrvbd_get_brunch_dates()
{
	global $wpdb;
	$now = date('Y-m-d', strtotime('-1 days'));
	$sql = "SELECT DISTINCT meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_brunch_date_en' 
				AND meta_value >= '{$now}' 
			ORDER BY meta_value ASC";
	$return = array();
	$results = $wpdb->get_col($sql);
	foreach($results as $result){
		$return[] = date('d/m/Y', strtotime($result));
	}
	return $return;
}


/**
 * Return the shipping data for this date
 * @method nrvbd_get_shipping
 * @param  array  $args
 * @param  boolean $load
 * @return \nrvbd\entities\shipping|int|null
 */
function nrvbd_get_shipping_by_date(string $date, bool $load = false)
{
	global $wpdb;
	$sql = "SELECT ID FROM {$wpdb->prefix}nrvbd_shipping WHERE delivery_date = %s";
	$id = $wpdb->get_var($wpdb->prepare($sql, $date));
	if($load == false){
		return $id;
	}
	return new \nrvbd\entities\shipping($id);
}


/**
 * Return the shipping data by date
 * @method nrvbd_get_shipping_data_by_date
 * @param  string $date
 * @return array
 */
function nrvbd_get_shipping_data_by_date(string $date)
{
	$orders = nrvbd_get_orders_by_brunch_date($date);
	$drivers = nrvbd_get_drivers(array(), true);
	$known_orders = array();
	$collection = array();
	foreach($drivers as $driver)
	{
		if($driver->latitude == "" || $driver->longitude == "" || $driver->email == ""){
			continue;
		}
		$collection[] = array(
			"id" => $driver->ID,
			"type" => "driver",
			"color" => $driver->color,
			"nom" => $driver->firstname . " " . $driver->lastname,
			"adresse" => $driver->address1 . ' ' . $driver->address2,
			"cp" => $driver->zipcode,
			"ville" => $driver->city,
			"lat" => $driver->latitude,
			"lng" => $driver->longitude
		);
	}
	foreach($orders as $order){
		if(in_array($order->ID, $known_orders)){
			continue;
		}
		$nom_commande = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		if ( empty(trim($nom_commande)) ) {
			$nom_commande = 'Commande #'.$order->ID;
		}
		$collection[] = array(
			"id" => $order->ID,
			"type" => "adresse",
			"nom" => $nom_commande,
			"adresse" => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2(),
			"cp" => $order->get_shipping_postcode(),
			"ville" => $order->get_shipping_city(),
			"lat" => $order->get_meta("_shipping_latitude"),
			"lng" => $order->get_meta("_shipping_longitude")
		);
		$known_orders[] = $order->ID;
	}
	return $collection;
}


/**
 * Return the shipping dates for the given order
 * @method nrvbd_get_order_shipping_dates
 * @param  string|int $order_id
 * @return array
 */
function nrvbd_get_order_shipping_dates($order_id)
{
	global $wpdb;
	$product_ids = array();
	$order = wc_get_order($order_id);
	foreach($order->get_items() as $item){
		$product_ids[] = $item->get_product_id();
	}
	$brunch_dates = array();
	$now = date('Y-m-d', strtotime('-1 days'));
	$sql = "SELECT DISTINCT meta_value 
			FROM {$wpdb->postmeta} 
			WHERE meta_key = '_brunch_date_en' 
				AND meta_value >= '{$now}' 
				AND post_id IN (" . implode(',', $product_ids) . ")
			ORDER BY meta_value ASC";
	$results = $wpdb->get_col($sql);
	foreach($results as $result){
		$brunch_dates[] = date('d/m/Y', strtotime($result));
	}
	return $brunch_dates;
}


/**
 * Return the incoming shippings for the user
 * @method nrvbd_get_user_incoming_shippings
 * @param  int $user_id
 * @return array
 */
function nrvbd_get_user_incoming_shippings($user_id)
{
	global $wpdb;
	$now = date('Y-m-d', strtotime('-1 days'));
	$sql = "SELECT DISTINCT oi.order_id
			FROM {$wpdb->prefix}woocommerce_order_items AS oi
			INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim ON oi.order_item_id = oim.order_item_id
			INNER JOIN {$wpdb->prefix}wc_orders AS o ON oi.order_id = o.id
			WHERE oi.order_item_type = 'line_item'
				AND oim.meta_key = '_product_id'
				AND o.customer_id = %d
				AND oim.meta_value IN (SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_brunch_date_en' AND meta_value >= '{$now}')
			ORDER BY oi.order_id ASC";
	return $wpdb->get_col($wpdb->prepare($sql, $user_id));
}


/**
 * Return the api key
 * @method nrvbd_api_key
 * @return string
 */
function nrvbd_api_key()
{
	return get_option('nrvbd_option_API_KEY', NRVBD_DEFAULT_API_KEY);
}


/**
 * Try to get the coordinates of the order address
 * @method nrvbd_new_order_address_coordinates
 * @param  string|int $order_id
 * @return void
 */
function nrvbd_new_order_address_coordinates($order_id)
{
	$order = wc_get_order($order_id);
	$address1 = $order->get_shipping_address_1();
	$address2 = $order->get_shipping_address_2();
	$postcode = $order->get_shipping_postcode();
	$city = $order->get_shipping_city();

	if(empty($address1) || empty($postcode) || empty($city)){
		$billing_address1 = $order->get_billing_address_1();
		$billing_address2 = $order->get_billing_address_2();
		$billing_postcode = $order->get_billing_postcode();
		$billing_city = $order->get_billing_city();

		if(!empty($billing_address1) && !empty($billing_postcode) && !empty($billing_city)){
			$address1 = $billing_address1;
			$address2 = $billing_address2;
			$postcode = $billing_postcode;
			$city = $billing_city;

			// Save the updated shipping address in the order
			$order->set_shipping_address_1($address1);
			$order->set_shipping_address_2($address2);
			$order->set_shipping_postcode($postcode);
			$order->set_shipping_city($city);
			$order->save();
		}
	}

	if($address1 == "" || $postcode == "" || $city == ""){
		return nrvbd_save_coordinates_error($order_id, 'order', 'empty address');
	}
	$address = $address1 . ' ' . $address2 . ' ' . $postcode . ' ' . $city;
	nrvbd_fetch_order_address_coordinates($order, $address);
}
add_action('woocommerce_new_order', 'nrvbd_new_order_address_coordinates', 10, 1);


/**
 * Try to get the coordinates of the order address
 * @method nrvbd_order_address_changed_coordinates
 * @param  string|int $order_id
 * @return void
 */
function nrvbd_order_address_changed_coordinates($order, $data_store) 
{
	if($order->id == 0){
		return;
	}

	if(!isset($_POST['_shipping_address_1'])
		|| !isset($_POST['_shipping_postcode'])
		|| !isset($_POST['_shipping_city'])){
		return;
	}

	$new_address1 = $_POST['_shipping_address_1'];
	$new_address2 = $_POST['_shipping_address_2'];
	$new_postcode = $_POST['_shipping_postcode'];
	$new_city = $_POST['_shipping_city'];

	$db_address1 = $order->get_shipping_address_1();
	$db_address2 = $order->get_shipping_address_2();
	$db_postcode = $order->get_shipping_postcode();
	$db_city = $order->get_shipping_city();
	
	if($new_address1 == "" || $new_postcode == "" || $new_city == ""){
		return nrvbd_save_coordinates_error($order->id, 'order', 'invalid address.');
	}

	if($new_address1 == $db_address1
		&& $new_address2 == $db_address2
		&& $new_postcode == $db_postcode
		&& $new_city == $db_city){
		return;
	}

	$address = $new_address1 . ' ' . $new_address2 . ' ' . $new_postcode . ' ' . $new_city;
	if(!isset($_POST['_shipping_latitude'])
	   || (isset($_POST['_shipping_latitde']) && $_POST['_shipping_latitude'] == '')  
	   || !isset($_POST['_shipping_longitude'])
	   || (isset($_POST['_shipping_longitude']) && $_POST['_shipping_longitude'] == '')){
		nrvbd_fetch_order_address_coordinates($order, $address);
	}
}
add_action('woocommerce_before_order_object_save', 'nrvbd_order_address_changed_coordinates', 10, 2);


/**
 * Fetch the order address coordinates
 * @method nrvbd_fetch_order_address_coordinates
 * @param  WC_Order $order
 * @param  string $address
 * @return void
 */
function nrvbd_fetch_order_address_coordinates($order, string $address)
{
	try{
		$response = nrvbd_get_address_gps($address);
		if(is_wp_error($response)){
			return nrvbd_save_coordinates_error($order->id, 'order', $response);
		}
	
		$data = json_decode(wp_remote_retrieve_body($response), true);
		if($data['status'] == 'OK'){
			$latitude = $data['results'][0]['geometry']['location']['lat'];
			$longitude = $data['results'][0]['geometry']['location']['lng'];
			if(!in_array($longitude, ['', 0]) && !in_array($latitude, ['', 0])){
				$order->update_meta_data('_shipping_latitude', $latitude);
				$order->update_meta_data('_shipping_longitude', $longitude);
				$user = $order->get_user();
				if($user){
					$user->update_meta_data('_shipping_latitude', $latitude);
					$user->update_meta_data('_shipping_longitude', $longitude);
					$user->save_meta_data();
				}
				$order->save_meta_data();
				return;
			}
		}
		return nrvbd_save_coordinates_error($order->id, 'order', $data);
	}catch(Exception $e){
		return nrvbd_save_coordinates_error($order->id, 'order', $e->getMessage());
	}
}


/**
 * Try to get the coordinates of the address
 * @method nrvbd_get_address_gps
 * @param  string $address
 * @return void
 */
function nrvbd_get_address_gps(string $address)
{
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&key=' . nrvbd_api_key();
	$response = wp_remote_get($url);
	return $response;
}


/**
 * Save the coordinates error in database
 * @method nrvbd_save_coordinates_error
 * @param  string|int $id
 * @param  string $type
 * @param  mixed $data
 * @return void
 */
function nrvbd_save_coordinates_error($id, string $type, $data = null)
{
	if($type == "order"){
		$coordinates_error = nrvbd_get_coordinate_error_by('order_id', $id);
		$coordinates_error->order_id = $id;
		$coordinates_error->user_id = null;
		$coordinates_error->driver_id = null;
		$coordinates_error->fixed = 0;
	}elseif($type == "user"){
		$coordinates_error = nrvbd_get_coordinate_error_by('user_id', $id);
		$coordinates_error->order_id = null;
		$coordinates_error->user_id = $id;
		$coordinates_error->driver_id = null;
		$coordinates_error->fixed = 0;
	}elseif($type == "driver"){
		$coordinates_error = nrvbd_get_coordinate_error_by('driver_id', $id);
		$coordinates_error->order_id = null;
		$coordinates_error->user_id = null;
		$coordinates_error->driver_id = $id;
		$coordinates_error->fixed = 0;
	}
	$coordinates_error->data = $data;
	$coordinates_error->save();
}


/**
 * Return the coordinate error matching the given key and value
 * @method nrvbd_get_coordinate_error_by
 * @param  string $key
 * @param  string $value
 * @return \nrvbd\entities\coordinates_errors
 */
function nrvbd_get_coordinate_error_by(string $key, string $value)
{
	global $wpdb;
	$sql = "SELECT ID FROM {$wpdb->prefix}nrvbd_coordinates_errors WHERE {$key} = %s";
	$id = $wpdb->get_var($wpdb->prepare($sql, $value));
	return new \nrvbd\entities\coordinates_errors($id);
}


/**
 * Return the list of coordinate error matching with args
 * @method nrvbd_get_coordinate_errors
 * @param  array ârgs
 * @param  bool $load 
 * @return \nrvbd\entities\coordinates_errors
 */
function nrvbd_get_coordinate_errors(array $args, bool $load = false)
{
	global $wpdb;
    $default = array(
        "per_pages" => -1,
        "page" => 1,
		"order_id" => null,
		"user_id" => null,
		"fixed" => null,
		"viewed" => null
    );
    $args = \nrvbd\helpers::set_default_values($default, $args, false, false);
	$sql = "SELECT ID FROM {$wpdb->prefix}nrvbd_coordinates_errors WHERE 1=1";
	if(isset($args['order_id'])){
		$sql .= " AND order_id = {$args['order_id']}";
	}
	if(isset($args['user_id'])){
		$sql .= " AND user_id = {$args['user_id']}";
	}
	if(isset($args['fixed'])){
		$sql .= " AND fixed = {$args['fixed']}";
	}
	if(isset($args['viewed'])){
		$sql .= " AND viewed = {$args['viewed']}";
	}	
	if($args['per_pages'] > 0){
		$offset = $args['per_pages'] * $args['page'] - $args['per_pages'];
		$sql .= " LIMIT {$args['per_pages']} OFFSET {$offset}";
	}
	$ids = $wpdb->get_col($sql);
	if($load == false){
		return $ids;
	}
	$collection = array();
	foreach($ids as $id){
		$collection[] = new \nrvbd\entities\coordinates_errors($id);
	}
	return $collection;
}


/**
 * Return the coordinate query info
 * @method nrvbd_get_coordinate_errors_info
 * @param  array ârgs
 * @return array
 */
function nrvbd_get_coordinate_errors_info(array $args)
{
	global $wpdb;
    $default = array(
        "per_pages" => -1,
        "page" => 1,
		"order_id" => null,
		"user_id" => null,
		"fixed" => null,
		"viewed" => null
    );
    $args = \nrvbd\helpers::set_default_values($default, $args, false, false);
	$sql = "SELECT count(ID) FROM {$wpdb->prefix}nrvbd_coordinates_errors WHERE 1=1";
	if(isset($args['order_id'])){
		$sql .= " AND order_id = {$args['order_id']}";
	}
	if(isset($args['user_id'])){
		$sql .= " AND user_id = {$args['user_id']}";
	}
	if(isset($args['fixed'])){
		$sql .= " AND fixed = {$args['fixed']}";
	}
	if(isset($args['viewed'])){
		$sql .= " AND viewed = {$args['viewed']}";
	}	

	$count = $wpdb->get_var($sql);
	if($args['per_pages'] <= 0){
		$pages = 1;
	}else{
		$pages = ceil($count / $args['per_pages']);
	}
	return array(
		"total" => $count,
		"pages" => $pages
	);
}


/**
 * Return the list of emails
 * @method nrvbd_get_delivery_mails
 * @param  array  $args
 * @param  bool $load
 * @return array
 */
function nrvbd_get_delivery_mails($args, $load = false)
{
	global $wpdb;
	$default = array(
		"per_pages" => -1,
		"page" => 1,
		"delivery_date" => null,
		"driver_id" => null
	);
	$args = \nrvbd\helpers::set_default_values($default, $args);
	$sql = "SELECT ID FROM {$wpdb->prefix}nrvbd_delivery_emails WHERE 1=1";

	if(isset($args['delivery_date'])){
		$sql .= " AND delivery_date = '{$args['delivery_date']}'";
	}
	if(isset($args['driver_id'])){
		$sql .= " AND driver_id = {$args['driver_id']}";
	}
	$sql .= " ORDER BY ID DESC";
	if($args['per_pages'] > 0){
		$offset = $args['per_pages'] * $args['page'] - $args['per_pages'];
		$sql .= " LIMIT {$args['per_pages']} OFFSET {$offset}";
	}
	$ids = $wpdb->get_col($sql);
	if($load == false){
		return $ids;
	}
	$collection = array();
	foreach($ids as $id){
		$collection[] = new \nrvbd\entities\email($id);
	}
	return $collection;
}


/**
 * Return the delivery mails info
 * @method nrvbd_get_delivery_mails_info
 * @param  array $args
 * @return array
 */
function nrvbd_get_delivery_mails_info($args)
{
	global $wpdb;
	$default = array(
		"per_pages" => -1,
		"page" => 1,
		"delivery_date" => null,
		"driver_id" => null
	);
	$args = \nrvbd\helpers::set_default_values($default, $args);
	$sql = "SELECT count(ID) FROM {$wpdb->prefix}nrvbd_delivery_emails WHERE 1=1";
	if(isset($args['delivery_date'])){
		$sql .= " AND delivery_date = '{$args['delivery_date']}'";
	}
	if(isset($args['driver_id'])){
		$sql .= " AND driver_id = {$args['driver_id']}";
	}

	$count = $wpdb->get_var($sql);
	if($args['per_pages'] <= 0){
		$pages = 1;
	}else{
		$pages = ceil($count / $args['per_pages']);
	}
	return array(
		"total" => $count,
		"pages" => $pages
	);
}


/**
 * Send the email to the driver for the delivery route
 * @method nrvbd_send_driver_delivery_route_mail
 * @param \nrvbd\entities\email $email
 * @return boolean
 */
function nrvbd_send_driver_delivery_route_mail(\nrvbd\entities\email $email)
{
	$addresses = $email->addresses;
	$delivery_date = $email->delivery_date;
	$delivery_pdf = $email->get_delivery_pdf();
	$driver = $email->get_driver();
	$base_url = "https://www.google.com/maps/dir/";
	$delivery_routes = nrvbd_get_delivery_routes($addresses, $driver);
	$delivery_routes_urls = array();
	foreach($delivery_routes as $key => $route){
		$url = $base_url;
		foreach($route as $address){
			$url .= $address['latitude'] . ',' . $address['longitude'] . '/';
		}
		$delivery_routes_urls[$key] = $url;
	}

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . get_bloginfo('name') . ' <ne-pas-repondre@lesbrunchsdysee.fr>'
	);

	$attachments = array();
	try{		
		$pdf = $delivery_pdf->generate_pdf();
		$pdf_name = $delivery_pdf->get_pdf_name();
		if($pdf_name != "" && $pdf != false){
			$attachments = array($pdf_name => $pdf);
		}
	}catch(\Exception $e){
		$email->error = $e->getMessage();
	}
	$subject = sprintf(__("Your delivery route for %s", "nrvbd"), $delivery_date);
	$content = '<p>' . sprintf(__("Hello %s", "nrvbd"), $driver->firstname) . '</p>';
	$content .= '<p>' . sprintf(__("Here is your delivery route for the %s", "nrvbd"), $delivery_date) . '</p>';
	$content .= '<p>' . __("You can find the google map itineraries at these addresses : ", "nrvbd") . '</p>';
	foreach($delivery_routes_urls as $key => $url){
		$content .= '<p>' . sprintf(__('Part %d : ', 'nrvbd'), $key + 1) . '<a href="' . $url . '" target="_blank">' . $url . '</a></p>';
	}
	
	$content .= "<p>-----------------------------------------</p>";
	$content .= '<p>' . __("Details", "nrvbd") . '</p>';
	foreach($delivery_routes as $key => $route){
		$url = $delivery_routes_urls[$key] ?? '';
		$content .= '<p>------</p>';
		$content .= '<p>' . sprintf(__('Part %d : ', 'nrvbd'), $key + 1) . '</p>';
		$content .= '<p>------</p>';
		foreach($route as $address){
			if($address['type'] == "driver"){
				continue;
			}
			$addr_url = $base_url . "?api=1&origin=Ma+Position&destination=" . $address['latitude'] . ',' . $address['longitude'] ;
			$content .= '<p>' . $address['name'] . '</p>';
			$content .= '<p>' . $address['address'] . '</p>';
			$content .= '<p>' . $address['postcode'] . ' ' . $address['city'] . '</p>';
			$content .= '<p> Itinéraire : ' . $addr_url . '</p>';
			$content .= '<p>--</p>';
		}
	}

	$email->content = $content;
	$email->header = $headers;
	$email->subject = $subject;
	try{
		wp_mail($email->driver_email, $subject, $content, $headers, $attachments);
		$email->date_sent = date('Y-m-d H:i:s');
		$sent = true;
	}catch(\Exception $e){
		$email->error = $e->getMessage();
		$sent = false;
	}
	$email->sent = $sent;
	$email->save();
	return $sent;
}


/**
 * Resend an email
 * @method nrvbd_send_driver_delivery_resend_mail
 * @param  \nrvbd\entities\email $email
 * @return void
 */
function nrvbd_send_driver_delivery_resend_mail(\nrvbd\entities\email $email)
{
	$subject = $email->subject;
	$content = $email->content;
	$headers = $email->header;
	
	$delivery_pdf = $email->get_delivery_pdf();
	$pdf_path = $delivery_pdf->get_pdf_path();
	$pdf_name = $delivery_pdf->get_pdf_name();
	$attachments = array();
	if($pdf_name != "" && $pdf_path != ""){
		$attachments = array($pdf_name => $pdf_path);
	}
	try{
		wp_mail($email->driver_email, $subject, $content, $headers, $attachments);
		$email->date_sent = date('Y-m-d H:i:s');
		$sent = true;
	}catch(\Exception $e){
		$email->error = $e->getMessage();
		$sent = false;
	}
	$email->sent = $sent;
	$email->save();
	return $sent;
}


/**
 * Return the delivery routes
 * @method nrvbd_get_delivery_routes
 * @param  array $addresses
 * @param  \nrvbd\entities\driver $driver
 * @return array
 */
function nrvbd_get_delivery_routes(array $addresses, \nrvbd\entities\driver $driver)
{
	$delivery_routes = array();
	$route_key = 0;
	$steps_count = 1;
	$driver_data = array(
		"name" => $driver->firstname . ' ' . $driver->lastname,
		"address" => $driver->address1 . ' ' . $driver->address2,
		"postcode" => $driver->zipcode,
		"city" => $driver->city,
		"latitude" => $driver->latitude,
		"longitude" => $driver->longitude,
		"type" => "driver"
	);
	$delivery_routes[$route_key][] = $driver_data;
	foreach($addresses as $address){
		$order = $address['adresse'];
		$WC_Order = \wc_get_order($order);
		$data = array(
			"order_id" => $order,
			"name" => $WC_Order->get_shipping_first_name() . ' ' . $WC_Order->get_shipping_last_name(),
			"address" => $WC_Order->get_shipping_address_1() . ' ' . $WC_Order->get_shipping_address_2(),
			"postcode" => $WC_Order->get_shipping_postcode(),
			"city" => $WC_Order->get_shipping_city(),
			"latitude" => $WC_Order->get_meta("_shipping_latitude"),
			"longitude" =>$WC_Order->get_meta("_shipping_longitude"),
			"type" => "customer"
		);
		$steps_count ++;
		$delivery_routes[$route_key][] = $data;
		if($steps_count == 10){
			$route_key ++;
			$delivery_routes[$route_key][] = $data;
			$steps_count = 0;
		}
	}
	return $delivery_routes;
}


/**
 * Send the email to the admin for the delivery
 * @method nrvbd_send_admin_delivery_mail
 * @param  \nrvbd\entities\shipping $shipping
 * @return void
 */
function nrvbd_send_admin_delivery_mail(\nrvbd\entities\shipping $shipping)
{
	$subject = __("New delivery", "nrvbd");
	$content = __("A new delivery has been created for the ", "nrvbd") . $shipping->delivery_date;
	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . get_bloginfo('name') . ' <ne-pas-repondre@lesbrunchsdysee.fr>'
	);
	
	$pdf = $shipping->get_delivery_pdf();
	$pdf_path = $pdf->get_pdf_path();
	$pdf_name = $pdf->get_pdf_name();
	$attachments = array();
	if($pdf_name != "" && $pdf_path != ""){
		$attachments = array($pdf_name => $pdf_path);
	}
	$emails = get_option('nrvbd_option_ADMIN_EMAIL', '');
	$emails = explode(',', $emails);
	foreach($emails as $email){
		wp_mail(trim($email), $subject, $content, $headers, $attachments);
	}
}


/**
 * Encode the text to windows-1252
 * @param  string $text
 * @return string
 */
function nrvbd_pdf_text($text)
{
	if($text == null){
		return "";
	}
	$text = strip_tags($text);
	$text = preg_replace("/\n{2,}/", "\n", $text);
	return iconv('UTF-8', 'windows-1252', $text);
}


function nrvbd_yith_get_blocks(array $args)
{
	global $wpdb;
	$table = $wpdb->prefix . 'yith_wapo_blocks';
	$default = array(
		"per_pages" => 100,
		"page" => 1
	);
	$args = \nrvbd\helpers::set_default_values($default, $args);
	$sql = "SELECT * FROM {$table} WHERE 1=1";
	if(isset($args['per_pages']) && $args['per_pages'] > 0){
		$sql .= " LIMIT {$args['per_pages']} OFFSET " . ($args['per_pages'] * $args['page'] - $args['per_pages']);
	}
	return $wpdb->get_results($sql);
}


function nrvbd_yith_blocks_info(array $args = array())
{
	global $wpdb;
	$default = array(
		"per_pages" => 50,
		"page" => 1
	);
	$args = \nrvbd\helpers::set_default_values($default, $args);
	$table = $wpdb->prefix . 'yith_wapo_blocks';
	$sql = "SELECT count(ID) FROM {$table} WHERE 1=1";
	$total = $wpdb->get_var($sql);
	$pages = 1;
	if($args['per_pages'] > 0){
		$pages = ceil($total / $args['per_pages']);
	}
	return array(
		"total" => $total,
		"pages" => $pages
	);
}


function nrvbd_yith_get_block_assocs($block_id)
{
	global $wpdb;
	$table = $wpdb->prefix . 'yith_wapo_blocks_assoc';
	$sql = "SELECT * FROM {$table} WHERE rule_id=%d";
	return $wpdb->get_results($wpdb->prepare($sql, $block_id));
}


function nrvbd_yith_get_block_addons($block_id)
{
	global $wpdb;
	$table = $wpdb->prefix . 'yith_wapo_addons';
	$sql = "SELECT * FROM {$table} WHERE block_id=%d";
	return $wpdb->get_results($wpdb->prepare($sql, $block_id));

}


function nrvbd_yith_get_categories(array $args = array())
{
	global $wpdb;
	$default = array(
		"per_pages" => 50,
		"page" => 1
	);
	$args = \nrvbd\helpers::set_default_values($default, $args);
	$table = $wpdb->prefix . 'yith_wapo_blocks_assoc';
	$sql = "SELECT object, rule_id FROM {$table} WHERE type='category' GROUP BY object";
	$sql .= " ORDER BY rule_id DESC";
	if(isset($args['per_pages']) && $args['per_pages'] > 0){
		$sql .= " LIMIT {$args['per_pages']} OFFSET " . ($args['per_pages'] * $args['page'] - $args['per_pages']);
	}
	return $wpdb->get_col($sql);
}


function nrvbd_yith_get_categories_info(array $args = array())
{
	global $wpdb;
	$default = array(
		"per_pages" => 50
	);
	$args = \nrvbd\helpers::set_default_values($default, $args);
	$table = $wpdb->prefix . 'yith_wapo_blocks_assoc';
	$sql = "SELECT object FROM {$table} WHERE type='category' GROUP BY object";
	$total = count($wpdb->get_col($sql));
	$pages = 1;
	if($args['per_pages'] > 0){
		$pages = ceil($total / $args['per_pages']);
	}
	return array(
		"total" => $total,
		"pages" => $pages
	);
}


function nrvbd_yith_get_block_addon_by_category($category_id)
{
	global $wpdb;
	$table = $wpdb->prefix . 'yith_wapo_blocks';
	$table_assoc = $wpdb->prefix . 'yith_wapo_blocks_assoc';
	$table_addon = $wpdb->prefix . 'yith_wapo_addons';
	$sql = "SELECT rule_id FROM {$table_assoc} WHERE type='category' AND object=%d";
	$block_id = $wpdb->get_var($wpdb->prepare($sql, $category_id));
	if($block_id == null){
		return array();
	}

	$sql = "SELECT * FROM {$table_addon} WHERE block_id=%d";
	return $wpdb->get_results($wpdb->prepare($sql, $block_id));
}


function nrvbd_yith_resort_addons_object($addons, $sort)
{
	$sorted = array();	
	foreach($sort as $id){
		foreach($addons as $key => $addon){
			if($addon->id == $id){
				$sorted[] = $addon;
				unset($addons[$key]);
				break;
			}
		}
	}
	$sorted = array_merge($sorted, $addons);	
	return $sorted;
}


function nrvbd_yith_get_addon_pdf_text($group, $label, array $data)
{
	// var_dump($data[$group]);
	// var_dump($data[$group][$label]);
	if(isset($data[$group])){
		if(isset($data[$group][$label])){
			return $data[$group][$label];
		}
	}
	return base64_decode($label);
}


function nrvbd_resort_pdf_addons($persons_addons, $sort)
{
	$sorted_keys = array_keys($sort);	
	$decoded_keys = array_map('base64_decode', $sorted_keys);
	foreach ($persons_addons as &$person) {
		$existing_keys = array_intersect($decoded_keys, array_keys($person));
		$sorted_person = array_replace(array_flip($existing_keys), $person);
		$person = $sorted_person;
	}
	unset($person);
	return $persons_addons;
}


function nrvbd_temp()
{
	//  error_reporting(E_ALL);
	// ini_set('display_errors', 1);
	// $email = new \nrvbd\entities\email(6);
	$pdf_entity = new \nrvbd\entities\delivery_pdf(60);
	$pdf = new \nrvbd\pdf\driver_deliveries($pdf_entity->delivery_date, array($pdf_entity->data), true);
	// $shipping = new \nrvbd\entities\shipping(3);
	// $pdf = new \nrvbd\pdf\kitchen_notes('14/04/2024', nrvbd_get_shipping_data_by_date('14/04/2024'));
	$pdf->save("toto", "I");
	// die();
	
}
add_action('admin_post_nrvbd-temp', 'nrvbd_temp');
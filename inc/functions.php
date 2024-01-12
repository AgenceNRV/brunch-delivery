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
    $messages['10400'] = array("message" => __("Malformed request.", "nrvbd"),
							   "type" => "error");
    $messages['10403'] = array("message" => __("Nonce invalid, please refresh the page.", "nrvbd"),
							   "type" => "error");
	$messages['10404'] = array("message" => __("Entity not found.", "nrvbd"),
							   "type" => "error");
    $messages['10201'] = array("message" => __("Successfully saved.", "nrvbd"),
							   "type" => "success");							   
    $messages['10202'] = array("message" => __("Successfully deleted.", "nrvbd"),
							   "type" => "warning");
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
        "page" => 1
    );
    $args = \nrvbd\helpers::set_default_values($default, $args);
    global $wpdb;
    $sql = "SELECT ID FROM {$wpdb->prefix}nrvbd_driver WHERE 1=1";

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
 * @param  array  $order_status
 * @return array
 */
function nrvbd_get_orders_ids_by_product_ids(array $product_ids, 
											 array $order_status = array('wc-completed', 'wc-processing'))
{
    global $wpdb;
	$sql = "SELECT order_items.order_id
	FROM {$wpdb->prefix}woocommerce_order_items as order_items
	LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
	LEFT JOIN {$wpdb->prefix}wc_orders AS orders ON order_items.order_id = orders.ID
	WHERE orders.status IN ( '" . implode( "','", $order_status ) . "' )
	AND order_items.order_item_type = 'line_item'
	AND order_item_meta.meta_key = '_product_id'
	AND order_item_meta.meta_value IN ('" . implode( "','", $product_ids ) . "')";
	return $wpdb->get_col($sql);
}

/**
 * Return the list of order for the given date
 * @method nrvbd_get_orders_by_brunch_date
 * @param  string $date
 * @return array
 */
function nrvbd_get_orders_by_brunch_date(string $date)
{
	$product_ids = nrvbd_get_product_ids_by_brunch_date($date);
	if(empty($product_ids)){
		return array();
	}

	$orders = array();
	$orders_ids = nrvbd_get_orders_ids_by_product_ids($product_ids);
	
	foreach($orders_ids as $order_id){
		$order = wc_get_order($order_id);
		if(is_a( $order, 'WC_Order')){     
			$orders[] = $order;
		}
	}
	return $orders;
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
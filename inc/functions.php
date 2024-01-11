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
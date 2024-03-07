<?php

use nrvbd\helpers;

function nrvbd_plugin_admin_ressources(){
    if(is_nrvbd_plugin_page()){
        wp_enqueue_style('nrvbd-admin', 
						 helpers::css_url('admin.css'), 
						 array(),
						 nrvbd_plugin_version());
        wp_enqueue_style('nrvbd-framework', 
						 helpers::css_url('framework.css'),
						 array(), 
						 nrvbd_plugin_version());
		wp_enqueue_script('nrvbd-framework', 
						 helpers::js_url('framework.js'), 
						 array("jquery"), 
						 nrvbd_plugin_version());
		wp_enqueue_script('nrvbd-admin', 
						helpers::js_url('admin.js'), 
						array("jquery","nrvbd-framework"), 
						nrvbd_plugin_version());
		wp_add_inline_script("nrvbd-admin", 'const nrvbd_ajax_url="'.admin_url("admin-ajax.php").'"');
		
		wp_enqueue_script('jquery-ui-draggable');
		wp_enqueue_script('jquery-ui-droppable');
    }
}
add_action('admin_enqueue_scripts', 'nrvbd_plugin_admin_ressources');


/**
 * Is the current admin page, a nrvbd plugin page ?
 * @method is_nrvbd_plugin_page
 * @return boolean
 */
function is_nrvbd_plugin_page()
{
    $nrvbd_plugin_pages = array(
        // page slugs
		"nrvbd-deliveries",
		"nrvbd-drivers",
		"nrvbd-options",
		"nrvbd-coordinates-errors",
		"nrvbd-emails",
		"nrvbd-custom-yith"
    );
    return (isset($_GET['page']) && in_array($_GET['page'], $nrvbd_plugin_pages));
}
<?php
/**
 * Autoloading the interfaces
 */
add_action('init', function(){
    if(is_admin()){
        $dir = NRVBD_PLUGIN_PATH . 'classes/interfaces/admin';
		\nrvbd\loader::classes($dir, true);
        \nrvbd\admin_menu::init();
    }
	$dir = NRVBD_PLUGIN_PATH . 'classes/interfaces/common';
	\nrvbd\loader::classes($dir, true);
});
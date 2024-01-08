<?php
/**
 * This function is executed after the WordPress initialization.
 * It will load the classes when it is need.
 * @method nrvbd_on_init
 * @return void
 */
function nrvbd_autoloader($class){
    $class_parts = explode('\\', $class);
    if(is_array($class_parts)){
        $namespace = $class_parts[0];
        $file_name = end($class_parts);
        $add_to_path = "";
        if($namespace == "nrvbd"){
            $count_parts = count($class_parts);
            if($count_parts > 2){
                for($i = 1; $i < ($count_parts - 1); $i++){
                    $add_to_path .= $class_parts[$i] . "/";
                }
            }
            $file = NRVBD_PLUGIN_PATH . 'classes/' . $add_to_path . strtolower($file_name) . '.php';
            if(file_exists($file)){
                include_once $file;
            }
        }
    }
}
spl_autoload_register('nrvbd_autoloader');
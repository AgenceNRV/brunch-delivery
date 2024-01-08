<?php

namespace nrvbd;

use nrvbd\helpers;

abstract class loader{

	/**
	 * Load the classes (used for interfaces or api routes)
	 * @method classes
	 * @param  string  $dir
	 * @param  boolean $recursive
	 * @param  array   $ignore
	 * @return void
	 */
	public static function classes(string $dir, 
									bool $recursive = false,
									array $ignore = array())
	{
		$items = helpers::list_dir($dir);

		foreach($items as $item){
			$path = helpers::normalize_path($dir . '/' . $item);
			if(in_array($item, $ignore) || in_array($path, $ignore)){
				continue;
			}

			if(is_dir($path) && $recursive == true){
				self::classes($path);
			}elseif(!is_dir($path) && file_exists($path)){
				$class = str_replace('.php', '', $item);
				$base = helpers::normalize_path(NRVBD_PLUGIN_PATH . 'classes');
				$namespace_part = str_replace($item, '', $path);
				$namespace = "\\nrvbd" . str_replace($base, '', $namespace_part);
				$c = helpers::normalize_path($namespace . $class, "\\");  
				if(class_exists($c)){
					new $c;
				}
			}
		}    
	}


}
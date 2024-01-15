<?php
/**
 * helpers
 *
 * @package  nrvbd/classes
 * @version  0.9.0
 * @since    0.9.0
 *
 * 
 *
 * Copyright (c) Domergue Aymerick 2023. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * --------------------------------------------------------------------------
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * --------------------------------------------------------------------------
 */

namespace nrvbd;

use WP_Error;

if(!class_exists('\nrvbd\helpers')){
    abstract class helpers{
                
        /**
         * Generate an unique identifier
         * @method unique_id
         * @return string
         */
        public static function unique_id(){
            return md5(uniqid(microtime(), true));
        }


        /**
         * Return the asset url
         * @method asset_url
         * @param  string $file
         * @return string
         */
        public static function asset_url(string $file = "")
        {
            return NRVBD_PLUGIN_URL . 'assets/' . $file;
        }


        /**
         * Return the asset path
         * @method asset_path
         * @param  string $file
         * @return void
         */
        public static function asset_path(string $file = "")
        {
            return NRVBD_PLUGIN_PATH . 'assets/' . $file;
        }


        /**
         * Return the media url
         * @method media_url
         * @param  string $file
         * @return string
         */
        public static function media_url(string $file)
        {
            return self::asset_url('media/' . $file);
        }        
        

        /**
         * Return the media path
         * @method media_path
         * @param  string $file
         * @return string
         */
        public static function media_path(string $file)
        {
            return self::asset_path('media/' . $file);
        }


        /**
         * Return the js url
         * @method js_url
         * @param  string $file
         * @return string
         */
        public static function js_url(string $file)
        {
            return self::asset_url('js/' . $file);
        }        
        

        /**
         * Return the js path
         * @method js_path
         * @param  string $file
         * @return string
         */
        public static function js_path(string $file)
        {
            return self::asset_path('js/' . $file);
        }

        
        /**
         * Return the css url
         * @method css_url
         * @param  string $file
         * @return string
         */
        public static function css_url(string $file)
        {
            return self::asset_url('css/' . $file);
        }        
        

        /**
         * Return the css path
         * @method css_path
         * @param  string $file
         * @return string
         */
        public static function css_path(string $file)
        {
            return self::asset_path('css/' . $file);
        }


        /**
         * Return the list of elements in a directory
         * @method list_dir
         * @param  string $dir
         * @param  int    $limit
         * @param  int    $offset
         * @param  array  $diff
         * @return array
         */
        public static function list_dir(string $dir,
										int $limit = -1, 
										int $offset = 0, 
										array $diff = array('..', '.'))
        {
            $collection = array();
            $current = 0;
			
			if(!is_dir($dir)){
				return $collection;
			}
            $dir_handle = opendir($dir);
            while(false !== ($file = readdir($dir_handle))){
                
                if($current < $offset){
                    continue;
                }
                $current++;

                if($limit > 0 && $current > $limit){
                    break;
                }

                if(!in_array($file, $diff)){
                    $collection[] = $file;
                }
            }
            return $collection;
        }




        /**
         * Create the missing repositories
         * @method create_path
         * @param  string $path
         * @return void
         */
        public static function create_path(string $path)
        {
            $parts = explode('/', $path);
            $currentPath = '';
        
            foreach($parts as $part){
                $currentPath .= $part . '/';
                if(!is_dir($currentPath)){
                    mkdir($currentPath, 0755, true);
                }
            }
        }


        /**
         * Delete the file
         * @method rm_file
         * @param  string $file
         * @return void
         */
        public static function rm_file(string $file)
        {
            if(file_exists($file)){
                return @unlink($file);
            }
            return false;
        }

        
        /**
         * Create an empty file
         * @method create_file
         * @param  string $file
         * @return void
         */
        public static function create_file(string $file)
        { 
            $directory = pathinfo($file, PATHINFO_DIRNAME);

            if(!is_dir($directory)){
                self::create_path($directory);
            }
        
            if(!file_exists($file)){
                $handle = fopen($file, 'w');
                fclose($handle);
            }
        }

		
        /**
         * Find the class methods starting by the $prefix
         * @method get_methods_by_prefix
         * @param  string $class
         * @param  string $prefix
         * @return array
         */
        public static function get_methods_by_prefix(string $class, string $prefix)
        {
            $reflection = new \ReflectionClass($class);
            $methods = $reflection->getMethods();            
            $found = array();            
            foreach($methods as $method){
                $name = $method->getName();                
                if(strpos($name, $prefix) === 0){
                    $found[] = $name;
                }
            }
            return $found;
        }


        /**
         * Return true if the array is multidimensionnal
         * @method is_dimensionnal_array
         * @param  array $array
         * @return boolean
         */
        public static function is_dimensionnal_array($array) 
        {
            if (!is_array($array)) {
                return false;
            }            
            foreach ($array as $element) {
                if (!is_array($element)) {
                    return false;
                }
            }            
            return true;
        }


        /**
         * Schedule a cron task with custom options.
         * @method schedule_cron
         * @param  string $hook           Cron task identifier.
         * @param  string $schedule       Schedule frequency (hourly, daily, twicedaily, etc.).
         * @param  array  $options        Additional options for scheduling (start time, end time, etc.).
         * @param  bool   $single         Indicates whether the cron task is a one-time event (true) or recurring (false).
         * @return void
         */
        public static function schedule_cron(string $hook, 
                                             string $schedule, 
                                             array $options = array(), 
                                             bool $single = false) 
        {
            $defaults = array(
                'start_time'   => '00:00:00', // Default start time (00:00:00).
                'end_time'     => '23:59:59', // Default end time (23:59:59).
                'interval'     => 1,          // Interval in minutes (1 minute by default).
                'timezone'     => 'UTC',      // Timezone (UTC by default).
                'days'         => array(),    // Specific days of the week (empty array means all days).
            );        
            $options = wp_parse_args($options, $defaults);
        
            $start_time = strtotime($options['start_time']);
            $end_time = strtotime($options['end_time']);        
            $current_time = time();
            $next_execution_time = false;
        
            if($current_time < $start_time){
                $next_execution_time = $start_time;
            }elseif($current_time >= $start_time && $current_time <= $end_time){
                $next_execution_time = $current_time;
            }else{
                $next_execution_time = strtotime('tomorrow ' . date('H:i:s', $start_time));
            }
            
            $planified = false;
            while($next_execution_time <= $end_time){     
                if(empty($options['days']) || in_array(date('N', $next_execution_time), $options['days'])){
                    wp_schedule_event($next_execution_time, $schedule, $hook, $single);
                }
                $next_execution_time += $options['interval'] * 60; 
                $planified = true;
            }            
            do_action('_' . $hook, $planified);
        }


        /**
         * Find and return the url from a string
         * @method extract_url_from_string
         * @param  string $url
         * @return string|false
         */
        public static function extract_url_from_string(string $string)
        {
            preg_match('/\bhttps?:\/\/\S+/', $string, $matches);
            if(isset($matches[0])){
                return $matches[0];
            }
            return false;
        }


        /**
         * Set default array values if the keys doesn't exist
         * @method set_default_values
         * @param  array $defaults
         * @param  array $array
         * @param  bool  $ignore_null
         * @param  bool  $ignore_empty
         * @return array
         */
        public static function set_default_values(array $defaults, 
                                                  array $array, 
                                                  bool $ignore_null = true, 
                                                  bool $ignore_empty = true) 
        {
			$output = $defaults;
            foreach($array as $key => $value){
                if(array_key_exists($key, $output)){
                    if($ignore_empty === true && empty($value)){
                        continue;
                    }
                    if($ignore_null === true && $value === null){
                        continue;
                    }
                    $output[$key] = $value;
                }
            }
            return $output;
        }


        /**
         * Add or edit a log file for nrv
         * The logs are located in wp-content/uploads/nrv-logs
         * @method log
         * @param  string $filename
         * @param  string $log
         * @return void
         */
        public static function log(string $filename, string $log)
        {
            $dir = ABSPATH . 'wp-content/uploads/nrv-logs';
            self::create_path($dir);

            $filepath = $dir . '/' . $filename;
            if(!file_exists($filepath)){
                self::create_file($filepath);
            }

            $fopen = fopen($filepath, 'a');
            fwrite($fopen, $log);
            fclose($fopen);
        }
		

		/**
		 * Return a normalized file path
		 * @method normalize_path
		 * @param  string $path
		 * @param  string $separator
		 * @return string
		 */
		public static function normalize_path(string $path, string $separator = DIRECTORY_SEPARATOR)
		{
			return str_replace(['/', '\\'], $separator, $path);
		}
    }
}
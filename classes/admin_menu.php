<?php
/**
 * @package  nrvbd/classes
 * @version  0.9.0
 * @since    0.9.0
 *
 * description
 *

 *
 */

namespace nrvbd;

use nrvbd\helpers;

if(!class_exists('\nrvbd\admin_menu')){
    class admin_menu{

        /**
         * Store the admin menu slug
         * @var string
         */
        const slug = 'nrvbd-deliveries';

        /**
         * Store the main admin menu callable
         * @var callable
         */
        static $main_menu;


        /**
         * Add a sub menu
         * @method add
         * @param string $page_title
         * @param string $menu_title
         * @param string $capability
         * @param string $menu_slug
         * @param string $callback
         * @param int    $position
         * @param int    $priority
         * @return void
         */
        public static function add(string $page_title, 
                                   string $menu_title, 
                                   string $capability, 
                                   string $menu_slug, 
                                   $callback = '', 
                                   $position = null,
                                   $priority = 10)
        {
            $parent = self::slug;
            // Register the submenu in the admin_menu hook
            add_action('admin_menu', function() use ($parent, $page_title, $menu_title, $capability, $menu_slug, $callback, $position){
                add_submenu_page($parent, $page_title, $menu_title, $capability, $menu_slug, $callback, $position);
            }, $priority);
        }


        /**
         * Initialize the admin menu
         * @method init
         * @return void
         */
        public static function init(){
            $slug = self::slug;
            $main_menu = self::$main_menu;
            add_action('admin_menu', function() use ($slug, $main_menu){
                add_menu_page(__('Box delivery','nrvbd'), 
                              __('Box delivery','nrvbd'),
                              'nrvbd_deliveries', 
                              $slug,
                              $main_menu,
                              'dashicons-location-alt'
                );
            }, 9);
        }


        /**
         * Register the main menu callable
         * @method main
         * @param  callable $callable
         * @return void
         */
        public static function main(callable $callable){
            self::$main_menu = $callable;
        }


		/**
		 * Add a new menu to the configuration menu
		 * @method add_configuration_menu
		 * @param  string       $group
		 * @param  string       $tag
		 * @param  string       $title
		 * @param  callable     $callable
		 * @param  bool         $in_menu
		 * @param  integer|null $position
		 * @return void
		 */
		public static function add_configuration_menu(string $group,
													  string $tag,
													  string $title, 
													  $callable, 
													  bool $in_menu = true,
													  int $position = null)
		{
			global $NRVBD_CONFIGURATION_MENU;
			if(!isset($NRVBD_CONFIGURATION_MENU[$group])){
				$NRVBD_CONFIGURATION_MENU[$group] = array();
			}
			$insert = array("tag" => $tag, 
						    "title" => $title, 
							"function" => $callable,
						    "in_menu" => $in_menu);
			self::increment_menu($NRVBD_CONFIGURATION_MENU[$group], $insert, $position);
		}


		/**
		 * Return the current menu group
		 * @method get_configuration_menu
		 * @param  string $group
		 * @return array
		 */
		public static function get_configuration_menu(string $group)
		{
			global $NRVBD_CONFIGURATION_MENU;
			return $NRVBD_CONFIGURATION_MENU[$group] ?? array();
		}


		/**
		 * Increment a menu array
		 * @method increment_menu
		 * @param  array  $menu
		 * @param  array  $insert
		 * @param  int|string $position
		 * @return void
		 */
		private static function increment_menu(array &$menu, 
											   array $insert, 
											   $position)
		{
			if(isset($position)){
				if(isset($menu[$position])){
					self::increment_menu($menu, $insert, $position);
				}else{
				  	$menu[$position] = $insert;
				}
			}else{
				array_push($menu, $insert);
			}
		}
    }
}
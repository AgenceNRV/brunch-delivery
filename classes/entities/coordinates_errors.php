<?php
namespace nrvbd\entities;

use nrvbd\database;

if(!class_exists('\nrvbd\entities\coordinates_errors')){
	class coordinates_errors extends database{

		/**
		 * @var int
		 */
		public $order_id = null;

		/**
		 * @var int
		 */
		public $user_id = null;

		/**
		 * @var int
		 */
		public $driver_id = null;

		/**
		 * @var string
		 */
		public $data;

		/**
		 * @var int
		 */
		public $viewed = 0;

		/**
		 * @var int
		 */
		public $fixed = 0;

		/**
		 * @var datetime
		 */
		public $created_at;
		
		/**
		 * @var datetime
		 */
		public $updated_at;

		/**
		 * @var array|null
		 */
		private $WC_Order = null;

		/**
		 * @var array|null
		 */
		private $WP_User = null;

		/**
		 * @var array|null
		 */
		private $Driver = null;


		/**
		 * Class constructor
		 * @param string|integer|null|null $ID
		 */
		public function __construct($ID = null)
		{
			parent::__construct("nrvbd_coordinates_errors", $ID);
		}


		/**
		 * Fire before updating data
		 * @method _on_update
		 * @return void
		 */
		public function _on_update()
		{
			$this->updated_at = date('Y-m-d H:i:s');
		}


		/**
		 * Fire before inserting data
		 * @method _on_insert
		 * @return void
		 */
		public function _on_insert()
		{
			$this->created_at = date('Y-m-d H:i:s');
		}


		/**
		 * Fire after save
		 * @method _after_save
		 * @return void
		 */
		public function _after_save()
		{
		}   


		/**
		 * Fire after the init
		 * @method _after_init
		 * @return void
		 */
		public function _after_init()
		{
		}


		/**
		 * Return the order
		 * @method get_order
		 * @param  boolean $reload
		 * @return WC_Order|null
		 */
		public function get_order(bool $reload = false)
		{
			if(($reload || $this->WC_Order == null) && $this->order_id != null){
				$this->WC_Order = new \WC_Order($this->order_id);
			}
			return $this->WC_Order;
		}


		/**
		 * Return the user
		 * @method get_user
		 * @param  boolean $reload
		 * @return WP_User|null
		 */
		public function get_user(bool $reload = false)
		{
			if(($reload || $this->WP_User == null) && $this->user_id != null){
				$this->WP_User = new \WP_User($this->user_id);
			}
			return $this->WP_User;
		}


		/**
		 * Return the driver
		 * @method get_driver
		 * @param  boolean $reload
		 * @return \nrvbd\entities\driver|null
		 */
		public function get_driver(bool $reload = false)
		{
			if(($reload || $this->Driver == null) && $this->driver_id != null){
				$driver = new \nrvbd\entities\driver($this->driver_id);
				if($driver->db_exists()){
					$this->Driver = $driver;
				}
			}
			return $this->Driver;
		}


	}
}

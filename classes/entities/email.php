<?php
/**
 * The email entity
 *
 * @package  nrvbd/classes/entities
 * @version  0.9.0
 * @since    0.9.0
 *
 * description
 */

namespace nrvbd\entities;

use nrvbd\database;

if(!class_exists('\nrvbd\entities\email')){
    class email extends database{

        /**
         * @var integer
         */
        public $driver_id;

        /**
         * @var string
         */
        public $driver_email;

        /**
         * @var string
         */
        public $delivery_date;

        /**
         * @var string
         */
        public $date_sent;

        /**
         * @var array
         */
        public $addresses;

        /**
         * @var string
         */
        public $subject;

        /**
         * @var string
         */
        public $content;

        /**
         * @var array
         */
        public $header;

        /**
         * @var boolean
         */
        public $sent = false;

        /**
         * @var mixed
         */
        public $error;

        /**
         * @var datetime
         */
        public $created_at;
        
        /**
         * @var datetime
         */
        public $updated_at;

		/**
		 * @var \nrvbd\entities\driver|null
		 */
		private $Driver = null;

        /**
         * Class constructor
         * @param string|integer|null|null $ID
         */
        public function __construct($ID = null)
        {
            parent::__construct("nrvbd_delivery_emails", $ID);
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
		 * Return the driver
		 * @param  boolean $reload
		 * @return \nrvbd\entities\driver
		 */
		public function get_driver($reload = false)
		{
			if(($this->Driver === null || $reload) && $this->driver_id !== null){
				$this->Driver = new \nrvbd\entities\driver($this->driver_id);
			}
			return $this->Driver;
		}


		/**
		 * Set the driver
		 * @param  \nrvbd\entities\driver $driver
		 * @return void
		 */
		public function set_driver(\nrvbd\entities\driver $driver)
		{
			if($driver->ID !== null){
				$this->driver_id = $driver->ID;
				$this->Driver = $driver;
			}
			return $this;
		}
    }
}
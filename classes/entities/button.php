<?php
/**
 * The main_window entity
 *
 * @package  nrvbd/classes/entities
 * @version  0.9.0
 * @since    0.9.0
 *
 * description
 */

namespace nrvbd\entities;

use nrvbd\database;

if(!class_exists('\nrvbd\entities\button')){
    class button extends database{

		/**
		 * @var string
		 */
		public $window_id;

		/**
		 * @var string
		 */
		public $title;

		/**
		 * @var string
		 */
		public $description;

		/**
		 * @var string
		 */
		public $image;

		/**
		 * @var int[]
		 */
		public $product_ids = array();

        /**
         * @var datetime
         */
        public $created_at;
        
        /**
         * @var datetime
         */
        public $updated_at;


        /**
         * Class constructor
         * @param string|integer|null|null $ID
         */
        public function __construct($ID = null)
        {
            parent::__construct("nrv_product_builder_buttons", $ID);
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


    }
}


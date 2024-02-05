<?php
namespace nrvbd\entities;

use nrvbd\database;

if(!class_exists('\nrvbd\entities\shipping')){
	class shipping extends database{

		/**
		 * @var array
		 */
		public $data = array();

		/**
		 * @var string
		 */
		public $delivery_date;

		/**
		 * @var int
		 */
		public $validated = false;

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
			parent::__construct("nrvbd_shipping", $ID);
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

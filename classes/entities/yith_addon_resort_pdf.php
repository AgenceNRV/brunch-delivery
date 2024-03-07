<?php
namespace nrvbd\entities;

use nrvbd\database;

if(!class_exists('\nrvbd\entities\yith_addon_resort_pdf')){
	class yith_addon_resort_pdf extends database{

		/**
		 * @var array
		 */
		public $sort = array();

		/**
		 * @var array
		 */
		public $data = array();

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
			parent::__construct("nrvbd_yith_addon_resort_pdf", $ID);
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

	}
}

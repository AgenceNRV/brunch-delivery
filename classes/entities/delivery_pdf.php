<?php
/**
 * The DeliveryPdf entity
 *
 * @package  nrvbd/classes/entities
 * @version  0.9.0
 * @since    0.9.0
 *
 * description
 */

namespace nrvbd\entities;

use nrvbd\database;
use nrvbd\helpers;

if(!class_exists('\nrvbd\entities\delivery_pdf')){
    class delivery_pdf extends database{

        /**
         * @var string
         */
        public $delivery_date;

        /**
         * @var array
         */
        public $data;

        /**
         * @var integer
         */
        public $driver_id;

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
            parent::__construct("nrvbd_delivery_pdf", $ID);
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


		/**
		 * Return the pdf location
		 * @method get_pdf_location
		 * @return string
		 */
		public function get_pdf_location()
		{
			return ABSPATH.'wp-content/uploads/delivery_pdfs/';
		}


		/**
		 * Return the pdf path
		 * @method get_pdf_path
		 * @return string
		 */
		public function get_pdf_path()
		{
			return $this->get_pdf_location().$this->get_pdf_name();
		}


		/**
		 * Return the pdf url
		 * @method get_pdf_url
		 * @return string
		 */
		public function get_pdf_url()
		{
			return '/wp-content/uploads/delivery_pdfs/'.$this->get_pdf_name();
		}


		/**
		 * Return the pdf name
		 * @method get_pdf_name
		 * @return string
		 */
		public function get_pdf_name()
		{
			if($this->driver_id == null){
				return 'complet_livraisons_'.str_replace('/', '-', $this->delivery_date).'.pdf';
			}else{
				$driver_name = $this->get_driver()->firstname.'_'.$this->get_driver()->lastname;
				return 'livraisons_'.str_replace('/', '-', $this->delivery_date).'_'.$driver_name.'.pdf';
			}
		}


		/**
		 * Generate the pdf
		 * @method generate_pdf
		 * @return string|false
		 */
		public function generate_pdf()
		{
			if($this->driver_id == null){
				$pdf = new \nrvbd\pdf\driver_deliveries($this->delivery_date, $this->data, false);
			}else{
				$pdf = new \nrvbd\pdf\driver_deliveries($this->delivery_date, array($this->data), false);
			}		
			helpers::create_path($this->get_pdf_location());
			$pdf->save($this->get_pdf_path(), 'F');
			if(file_exists($this->get_pdf_path())){
				return $this->get_pdf_path();
			}else{
				return false;
			}
		}


		/**
		 * Return the current pdf
		 * @method get_pdf
		 * @param  boolean $regenerate
		 * @return string|false
		 */
		public function get_pdf($regenerate = false)
		{
			if($regenerate){
				return $this->generate_pdf();
			}else if(file_exists($this->get_pdf_path())){
				return $this->get_pdf_path();
			}else{
				return false;
			}
		}
    }
}
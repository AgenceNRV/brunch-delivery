<?php
/**
 * The driver entity
 *
 * @package  nrvbd/classes/entities
 * @version  0.9.0
 * @since    0.9.0
 *
 * description
 */

namespace nrvbd\entities;

use nrvbd\database;

if(!class_exists('\nrvbd\entities\driver')){
    class driver extends database{

		/**
		 * @var string
		 */
		public $firstname;

		/**
		 * @var string
		 */
		public $lastname;

		/**
		 * @var string
		 */
		public $color;

		/**
		 * @var string
		 */
		public $phone;

		/**
		 * @var string
		 */
		public $email;

		/**
		 * @var string
		 */
		public $address1;

		/**
		 * @var string
		 */
		public $address2;

		/**
		 * @var string
		 */
		public $zipcode;

		/**
		 * @var string
		 */
		public $city;

		/**
		 * @var string
		 */
		public $latitude;

		/**
		 * @var string
		 */
		public $longitude;

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
            parent::__construct("nrvbd_driver", $ID);
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
		 * Return the address as html element
		 * @method get_address_html
		 * @return void
		 */
		public function get_address_html()
		{
			ob_start();
			?>
			<address>
				<?= stripslashes($this->address1);?><br>
				<?php
				if(trim($this->address2) != ''){
				 	echo stripslashes($this->address2) . '<br>';
				}
				?>
				<?= stripslashes($this->zipcode . " " . $this->city);?>
			</address>
			<?php
			return ob_get_clean();
		}


		/**
		 * Return the address as raw text
		 * @method get_raw_address
		 * @return void
		 */
		public function get_raw_address()
		{
			if(!$this->address1){
				return null;
			}
			$address = $this->address1;
			if($this->address2){
				$address .= " ".$this->address2;
			}
			$address .= " ".$this->zipcode." ".$this->city;
			return $address;
		}


		/**
		 * Return the latitude and longitude as raw text
		 * @method get_raw_latlong
		 * @return void
		 */
		public function get_raw_latlong()
		{
			if(!$this->latitude || !$this->longitude){
				return null;
			}
			return $this->latitude.",".$this->longitude;
		}


    }
}


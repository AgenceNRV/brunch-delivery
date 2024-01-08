<?php
/**
 * database
 *
 * @package  nrvbd/classes
 * @version  0.9.0
 * @since    0.9.0
 *
 * Class used to save the entities data
 *

 *
 */

namespace nrvbd;

if(!class_exists('\nrvbd\database')){
    class database{

        /**
         * Does the entity exists in database
         * @var boolean
         */
        private $db_exists = false;

        /**
         * Store the entity table name
         * @var string
         */
        private $table_name = '';

        /**
         * The entity id
         * @var int
         */
        public $ID;


        /**
         * Class constructor
         * @method __construct
         * @param string $table
         */
        public function __construct(string $table = "", $ID = null)
        {
            $this->set_table($table);
            $this->ID = $ID;
            $this->init_db();
        }


        /**
         * Class destructor
         * @method __destruct
         */
        public function __destruct(){
            $this->db_exists = false;
        }


        /**
         * Set the table name
         * @method set_table
         * @param  string $table
         * @return self
         */
        public function set_table(string $table)
        {
            $this->table_name = $table;
            return $this;
        }


        /**
         * Get the table name
         * @method get_table
         * @return string
         */
        public function get_table()
        {
            return $this->table_name;
        }


        /**
         * Does the entity exists in the database
         * @method db_exists
         * @return bool
         */
        public function db_exists()
        {
          return $this->db_exists;
        }


        /**
         * Convert the object to array
         * Only public properties are considered
         * @method to_array
         * @param  boolean $ID include or not the ID in the array
         * @param  boolean $serialize serialize for database
         * @return array
         */
        public function to_array(bool $ID = true, bool $serialize = false)
        {
            $array = array();
            $reflection = new \ReflectionClass($this);
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach($properties as $property){
                if($ID == false && $property->getName() === "ID"){
                    continue;
                }
                $value = $property->getValue($this);
                if($serialize == true){
                    $value = maybe_serialize($value);
                }
                $array[$property->getName()] = $value;
            }
            return $array;
        }


        /**
         * Initialize the child from the data in array
         * Only the known public properties will be defined
         * It doesn't consider the ID
         * @method init_from_array
         * @param  array   $data
         * @param  boolean $only_props
         * @param  boolean $unserialize
         * @return void
         */
        public function init_from_array(array $data, bool $only_props = true, bool $unserialize = true)
        {
            $reflection = new \ReflectionClass($this);
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach($data as $prop => $value){
                if($unserialize == true){
                    $value = maybe_unserialize($value);
                }
        
                if($only_props){
                    $propExists = false;
                    foreach($properties as $property){
                        if($property->getName() === $prop){
                            $propExists = true;
                            break;
                        }
                    }
                    
                    if(!$propExists){
                        continue;
                    }
                }                
                $this->$prop = $value;
            }
        }


        /**
         * Save the entity in the database
         * @method save
		 * @param  bool        $save_id
         * @return int|false   number of rows updated if updated, number of rows inserted if insert.
         */
        public function save(bool $save_id = false)
        {
            $this->execute_hook('_on_save');
            if($this->db_exists()){
                $return = $this->update($save_id);
            }else{
                $return = $this->insert($save_id);
            }
            $this->execute_hook('_after_save');
			return $return;
        }


        /**
         * Delete the entity from the database
         * @method delete
         * @return void
         */
        public function delete(){
            global $wpdb;
            if($wpdb->delete($wpdb->prefix.$this->table_name, $this->db_primary_key()) > 0){
                $this->__destruct();
            }
        }


        /** 
         * Insert the entity in the database
         * @method insert
		 * @param  bool       $save_id
         * @return int|false  The number of rows inserted, or false on error.
         */
        public function insert(bool $save_id = false){
            $this->execute_hook('_on_insert');
            global $wpdb;
            $insert = $wpdb->insert($wpdb->prefix.$this->table_name, 
									$this->to_array($save_id, true));
            if($insert){
                $this->ID = $wpdb->insert_id;
                $this->db_exists = true;
            }
            $this->execute_hook('_after_insert');
            return $insert;
        }


        /**
         * Update the entity in the database
         * @method update
		 * @param  bool       $save_id
         * @return int|false  The number of rows updated, or false on error.
         */
        public function update(bool $save_id = false){
            $this->execute_hook('_on_update');
            global $wpdb;
            $update = $wpdb->update($wpdb->prefix.$this->table_name, 
									$this->to_array($save_id, true), 
									$this->db_primary_key());			
            $this->execute_hook('_after_update');
			return $update;
        }


        /**
         * Retrieve the entity data from the database
         * @method init_db
         * @return void
         */
        protected function init_db(){
            global $wpdb;
            $data = array();
            if(isset($this->ID)){
                $query = "SELECT * FROM ".$wpdb->prefix.$this->table_name." WHERE ID = %s";
                $req = $wpdb->prepare($query, $this->ID);
                $data = $wpdb->get_row($req, ARRAY_A);
            }
			if(is_array($data)){
				$this->db_exists = !empty($data);
				$this->init_from_array($data);
			}
			$this->execute_hook('_after_init');
        }


        /**
         * Return the primary key for database update
         * @method db_primary_key
         * @return array
         */
        protected function db_primary_key()
        {
            return array('ID' => $this->ID);
        }


        /**
         * Call the hook function for each entity
         * @method execute_hook
         * @param  string $hook callback name (_on_insert, _on_update, _on_save...)
         * @return void
         */
        public function execute_hook(string $hook)
        {             
            if(method_exists($this, $hook)){
                call_user_func(array($this, $hook));
            }
        }
    }

}

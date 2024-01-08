<?php
class nrvbd_plugin_activation{

    /**
     * Store the wp option name
     * @var string
     */
    const option_name_version = "nrvbd_db_version";
    
    /**
     * Store the current plugin db version
     * @var string
     */
    const db_version = "0.0.0";

    /**
     * Store the current db version 
     * @var string
     */
    protected static $installed_db_version = "";

    /**
     * Store the current database prefix
     * @var string
     */
    private static $prefix = "";

    /**
     * The DB collate
     * @var string
     */
    private static $collate = "";
    
    /**
     * The class constructor
     * @method __construct
     */
    public static function init()
    {
        global $wpdb;
        self::$prefix = $wpdb->prefix;
        self::$collate = $wpdb->collate;
        self::$installed_db_version = get_option(self::option_name_version, '0.0.0');
        
        self::install_capabilities();

        if(self::need_update()){
            self::install();
        }
    }


    /**
     * Install the database 
     * @return void
     */
    public static function install()
    {
        self::updated();
    }



	// public static function table_nrv_product_builder_button_products()
	// {
	// 	$p = self::$prefix;
	// 	$c = self::$collate;
	// 	$sql = "CREATE TABLE {$p}nrv_product_builder_button_products (
	// 		ID bigint(20) unsigned NOT NULL auto_increment,
	// 		button_id bigint(20) unsigned NOT NULL,
	// 		product_id bigint(20) unsigned NOT NULL,
	// 		PRIMARY KEY  (ID)
	// 	) COLLATE {$c}";
	// 	self::delta($sql);		
	// }


    /**
     * Add capabilities
     * @return void
     */
    public static function install_capabilities()
    {
        $role = get_role('administrator');
        $role->add_cap('nrvbd_deliveries', true);
    }


    /**
     * Does the database needs an update ?
     * @method need_update
     * @return void
     */
    private static function need_update()
    {
        return version_compare(self::$installed_db_version, self::db_version, "<");
    }


    /**
     * Change the database version with the new version
     * @method updated
     * @return void
     */
    private static function updated()
    {
        update_option(self::option_name_version, self::db_version);
        self::$installed_db_version = self::db_version;       
        $methods = \nrvbd\helpers::get_methods_by_prefix(self::class, 'table');
        foreach($methods as $method){
            call_user_func(array(self::class, $method));
        }
    }


    /**
     * Execute the db delta
     * @method delta
     * @param  string $sql
     * @return void
     */
    private static function delta(string $sql)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

nrvbd_plugin_activation::init();
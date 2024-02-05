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
    const db_version = "0.2.0";

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



	public static function table_nrvbd_driver()
	{
		$p = self::$prefix;
		$c = self::$collate;
		$sql = "CREATE TABLE {$p}nrvbd_driver (
			ID bigint(20) unsigned NOT NULL auto_increment,
			firstname char(255),
			lastname char(255),
			color char(50),
			phone char(16),
			email char(255),
			address1 char(255),
			address2 char(255),
			zipcode char(10),
			city char(255),
			latitude char(100),
			longitude char(100),
			deleted tinyint(1) DEFAULT 0,
			deleted_at datetime,
			created_at datetime,
			updated_at datetime,
			PRIMARY KEY  (ID)
		) COLLATE {$c}";
		self::delta($sql);		
	}


	public static function table_nrvbd_shipping()
	{
		$p = self::$prefix;
		$c = self::$collate;
		$sql = "CREATE TABLE {$p}nrvbd_shipping (
			ID bigint(20) unsigned NOT NULL auto_increment,
			data longtext,
			delivery_date char(100),
			validated tinyint(1) DEFAULT 0,
			created_at datetime,
			updated_at datetime,
			PRIMARY KEY  (ID)
		) COLLATE {$c}";
		self::delta($sql);		
	}


	public static function table_nrvbd_delivery_pdf()
	{
		$p = self::$prefix;
		$c = self::$collate;
		$sql = "CREATE TABLE {$p}nrvbd_delivery_pdf (
			ID bigint(20) unsigned NOT NULL auto_increment,
			delivery_date char(100),
			data longtext,
			driver_id bigint(20) unsigned,
			created_at datetime,
			updated_at datetime,
			PRIMARY KEY  (ID)
		) COLLATE {$c}";
		self::delta($sql);		
	}


	public static function table_nrvbd_coordinates_errors()
	{
		$p = self::$prefix;
		$c = self::$collate;
		$sql = "CREATE TABLE {$p}nrvbd_coordinates_errors (
			ID bigint(20) unsigned NOT NULL auto_increment,
			order_id bigint(20) unsigned,
			user_id bigint(20) unsigned,
			driver_id bigint(20) unsigned,
			data longtext,
			viewed tinyint(1) DEFAULT 0,
			fixed tinyint(1) DEFAULT 0,
			created_at datetime,
			updated_at datetime,
			PRIMARY KEY  (ID),
			KEY order_id (order_id),
			KEY user_id (user_id),
			KEY driver_id (driver_id)
		) COLLATE {$c}";
		self::delta($sql);		
	}



	public static function table_nrvbd_delivery_emails()
	{
		$p = self::$prefix;
		$c = self::$collate;
		$sql = "CREATE TABLE {$p}nrvbd_delivery_emails (
			ID bigint(20) unsigned NOT NULL auto_increment,
			driver_id bigint(20) unsigned,
			driver_email char(255),
			delivery_date char(100),
			date_sent char(100),
			addresses longtext,
			subject longtext,
			content longtext,
			header longtext,
			sent tinyint(1) DEFAULT 0,
			error longtext,
			created_at datetime,
			updated_at datetime,
			PRIMARY KEY  (ID),
			KEY driver_id (driver_id)
		) COLLATE {$c}";
		self::delta($sql);		
	}


    /**
     * Add capabilities
     * @return void
     */
    public static function install_capabilities()
    {
        $role = get_role('administrator');
        $role->add_cap('nrvbd_deliveries', true);
        $role->add_cap('nrvbd_manage_driver', true);	
        $role->add_cap('nrvbd_manage_options', true);	
        $role->add_cap('nrvbd_fix_coordinates', true);	
        $role->add_cap('nrvbd_resend_email', true);	
		
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
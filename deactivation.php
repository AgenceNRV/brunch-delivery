<?php

class nrvbd_plugin_deactivation{
    
    /**
     * The class constructor
     * @method __construct
     */
    public function __construct()
    {
        
    }

    
    /**
     * Unschedule the plugin's crons
     * @method unregister_crons
     * @return void
     */
    private function unregister_crons()
    {
        wp_clear_scheduled_hook('');
    }
}

// function nrvbd_plugin_deactivation(){
// 	wp_clear_scheduled_hook('admin_post_nrvbd-cron-check');	
// }
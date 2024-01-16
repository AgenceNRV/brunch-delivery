<?php
/**
 * woocommerce_order interface
 *
 * @package  nrvbd/classes/interfaces/admin
 * @version  0.9.0
 * @since    0.9.0
 */

namespace nrvbd\interfaces\admin;

use nrvbd\admin_menu;
use nrvbd\helpers;

if(!class_exists('\nrvbd\interfaces\admin\woocommerce_order')){
    class woocommerce_order{

		const slug = "wc-orders";


        /**
         * base_url
         * @var string
         */
        protected $base_url = "";
        
        /**
         * action_url
         * @var string
         */
        protected $action_url = "";


        /**
         * Class constructor
         * @method __construct
         * @return void
         */
        public function __construct()
        {
            $this->register_actions();
            $this->base_url = admin_url('admin.php') . "?page=" . self::slug;
            $this->action_url = admin_url('admin-post.php');
        }


        /**
         * Generate the main interface
         * @method interface
         * @return html
         */
        public function interface($order)
        {	
			?>
			<div id="nrvbd-admin-latlong-fields" style="display:none; clear:both; padding-top:15px;">
				<div style="display:flex; justify-content:space-between;">
					<h3 style="margin-top:0;"><?= __('GPS Coordinates','nrvbd');?></h3>
					<button type="button" 
							class="button button-secondary" 
							id="nrvbd-order-admin-get-coordinates">
						<?= __('Get the GPS location','nrvbd');?>
					</button>
				</div>
				<div>
					<p class="form-field">
						<label for="_shipping_latitude"><?= __('Latitude', 'nrvbd');?></label>
						<input type="text" 
							class="short" 
							name="_shipping_latitude" 
							id="_shipping_latitude" 
							value="<?= esc_attr($order->get_meta('_shipping_latitude'));?>"/>
					</p>
					<p class="form-field" style="float:right; clear:right;">
						<label for="_shipping_longitude"><?= __('Longitude', 'nrvbd');?></label>
						<input type="text" 
							class="short" 
							name="_shipping_longitude" 
							id="_shipping_longitude" 
							value="<?= esc_attr($order->get_meta('_shipping_longitude'));?>"/>
					</p>
				</div>
			</div>
			<?php
        }


		public function interface_notice_error()
		{
			if(isset($_GET['id'])){
				$error = nrvbd_get_coordinate_error_by('order_id', $_GET['id']);
				if($error->db_exists() && $error->fixed == 0){
					$fix_href = admin_url('admin.php') . "?page=" 
								. \nrvbd\admin_menu::slug
								. "&setting=" . \nrvbd\interfaces\admin\deliveries\coordinates_errors::setting_fix
								. "&id=" . $error->ID
								. "&type=order";
					?>
					<div class="notice notice-error" style="padding: 8px 12px">
						<?= sprintf(__('The GPS coordinates are missing for this order. Please check the "shipping address" area or <a href="%s">click here to fix</a>.','nrvbd'),  $fix_href);?>
					</div>
					<?php
				}
			}
		}

        /**
         * Register the actions in the WP loop
         * @method register_actions
         * @return void
         */
        public function register_actions()
        {    
			add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'interface'));
			if(isset($_GET['page']) && $_GET['page'] == self::slug){
				add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
				add_action('admin_init', array($this, 'interface_notice_error'));
			}
			add_action('woocommerce_process_shop_order_meta', array($this, 'save_coord_gps_admin_order'));
        }


		/**
		 * Enqueue the scripts
		 * @method enqueue_scripts
		 * @return void
		 */
		public function enqueue_scripts()
		{
			wp_enqueue_script('nrvbd-admin-fix-address', 
							  helpers::js_url('admin-fix-address.js'), 
							  array('jquery'), 
							  nrvbd_plugin_version(), 
							  true);
			wp_localize_script('nrvbd-admin-fix-address', 'nrvbd_API_KEY', nrvbd_api_key());	
		}


		/**
		 * Save the coordinates in the order
		 * @method save_coord_gps_admin_order
		 * @param  int $order_id
		 * @return void
		 */
		public function save_coord_gps_admin_order($order_id)
		{
			$error = nrvbd_get_coordinate_error_by('order_id', $order_id);
			$order = \wc_get_order($order_id);
			if($order->id !== 0){
				$order->update_meta_data('_shipping_latitude', $_POST['_shipping_latitude']);
				$order->update_meta_data('_shipping_longitude', $_POST['_shipping_longitude']);
				$order->save();	
			}

			if($error->db_exists() && $error->fixed == 0){
				$error->fixed = 1;
				$error->save();
			}
		}
    }
}
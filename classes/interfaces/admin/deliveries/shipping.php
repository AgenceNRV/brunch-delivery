<?php
/**
 * base interface
 *
 * @package  nrvbd/classes/interfaces/admin/deliveries
 * @version  0.9.0
 * @since    0.9.0
 */

namespace nrvbd\interfaces\admin\deliveries;

use nrvbd\admin_menu;
use nrvbd\helpers;

if(!class_exists('\nrvbd\interfaces\admin\deliveries\shipping')){
    class shipping{

		const slug = "nrvbd";
		const setting = "shipping";

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
            $this->base_url = admin_url('admin.php') . "?page=" . self::slug . "&setting=" . self::setting;
            $this->action_url = admin_url('admin-post.php');

        }


        /**
         * Generate the main interface
         * @method interface
         * @return html
         */
        public function interface()
        {		
			?>
			<div class="nrvpb-d-flex">
				<div class="nrvpb-col-8">
					
				</div>
				<div class="nrvpb-col-4">
					
				</div>
			</div>
			<?php
        }


        /**
         * Register the admin menu
         * @method register_menu
         * @return void
         */
        public function register_menu()
        {	
			admin_menu::add_configuration_menu(self::setting, 
												__('Shipping', 'nrvbd'), 
												array($this, 'interface'));
        }


        /**
         * Register the actions in the WP loop
         * @method register_actions
         * @return void
         */
        public function register_actions()
        {    
			add_action("admin_menu", [$this, "register_menu"], 140);	
			add_action('admin_enqueue_scripts', [$this, 'register_assets'], 12);
        }


		/**
		 * Register the assets in the wordpress loop
		 * @method register_assets
		 * @return void
		 */
		public function register_assets()
		{
			if(is_nrvbd_plugin_page()){
				wp_enqueue_script('nrvbd-admin-shipping', 
								  helpers::js_url('admin-shipping.js'), 
								  array("jquery"), 
								  nrvbd_plugin_version());
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_shipping_data', $this->temp_json_type());
			}
		}


		public function temp_json_type()
		{
			$data = array(
				array(
					"id" => "125",
					"type" => "driver",
					"color" => "#FF00FF",
					"nom" => "jon doe",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "43.6084497",
					"lng" => "1.4422524"
				),
				array(
					"id" => "124",
					"type" => "driver",
					"color" => "#00E0FF",
					"nom" => "river dance",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "33.6084497",
					"lng" => "1.4422524"
				),
				array(
					"id" => "126",
					"type" => "adresse",
					"nom" => "commande 1",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "41.6084497",
					"lng" => "4.4322524"
				),
				array(
					"id" => "127",
					"type" => "adresse",
					"nom" => "commande 2",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "46.6084497",
					"lng" => "1.4122524"
				),
				array(
					"id" => "128",
					"type" => "adresse",
					"nom" => "commande 3",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "49.6084497",
					"lng" => "2.4922524"
				),
				array(
					"id" => "129",
					"type" => "adresse",
					"nom" => "commande 4",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "36.6084497",
					"lng" => "1.4822524"
				),
				array(
					"id" => "130",
					"type" => "adresse",
					"nom" => "commande 5",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "31.6084497",
					"lng" => "13.4822524"
				),
				array(
					"id" => "131",
					"type" => "adresse",
					"nom" => "commande 6",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "1.6084497",
					"lng" => "1.4822524"
				),
				array(
					"id" => "132",
					"type" => "adresse",
					"nom" => "commande 7",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "1.6084497",
					"lng" => "10.4822524"
				),
				array(
					"id" => "133",
					"type" => "adresse",
					"nom" => "commande 8",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "31.6084497",
					"lng" => "11.4822524"
				),
				array(
					"id" => "134",
					"type" => "adresse",
					"nom" => "commande 9",
					"adresse" => "3 rue bayard",
					"cp" => "33150",
					"ville" => "Bordeaux",
					"lat" => "33.6084497",
					"lng" => "3.4822524"
				)
			);
			return json_encode($data);
		}
    }
}
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
            $this->base_url = admin_url('admin.php') . "?page=" .  admin_menu::slug . "&setting=" . self::setting;
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
			<div class="nrvpb-d-flex" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
                <div class="container-map">
                    <div id="googleMap"></div>
                </div>
                <div class="right-container">
                    <div class="label-drivers">
                        <div>Selectionner les chauffeurs puis les destinations</div>
                        <button id="btn-hidelabels">Labels</button>
                        <button id="btn-initBounds">Recentrer</button>
                    </div>
                    <div class="container-drivers" id="container-drivers"></div>
                    <div class="container-submit"><button id="submit-btn" disabled>Envoyer</button></div>
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
			admin_menu::add_configuration_menu("deliveries",
											   self::setting, 
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

                wp_deregister_script('jquery');
                wp_enqueue_script('jquery', helpers::js_url('jquery.min.js'), array(), '3.7.1', true);


                wp_enqueue_style('shippingCss', helpers::css_url('jquery-ui.min.css'));
                wp_enqueue_style('jqueryUiCss', helpers::css_url('admin-shipping.css'));
                wp_enqueue_script('jquery-ui',
                                    helpers::js_url('jquery-ui.min.js'),
                                    array("jquery"),
                                    nrvbd_plugin_version());
                wp_enqueue_script('markerLabel',
                    helpers::js_url('markerLabel.js'),
                    array("jquery"),
                    nrvbd_plugin_version());
                wp_enqueue_script('sortableJs',
                    helpers::js_url('sortable.js'),
                    array("jquery","jquery-ui","markerLabel"),
                    nrvbd_plugin_version());
                wp_enqueue_script('sortableJquery',
                    helpers::js_url('jquery-sortable.js'),
                    array("sortableJs"),
                    nrvbd_plugin_version());
				wp_enqueue_script('nrvbd-admin-shipping',
								  helpers::js_url('admin-shipping.js'), 
								  array("jquery","jquery-ui","markerLabel","sortableJs","sortableJquery"),
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
					"nom" => "Matt Pokora",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.59697",
					"lng" => "1.424225"
				),
				array(
					"id" => "124",
					"type" => "driver",
					"color" => "#00E0FF",
					"nom" => "Keen V",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.611470",
					"lng" => "1.426349"
				),
                array(
                    "id" => "123",
                    "type" => "driver",
                    "color" => "#000000",
                    "nom" => "Julien Clerc",
                    "adresse" => "3 rue bayard",
                    "cp" => "31000",
                    "ville" => "Toulouse",
                    "lat" => "43.601098",
                    "lng" => "1.459183"
                ),
				array(
					"id" => "126",
					"type" => "adresse",
					"nom" => "commande 1",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.586681",
					"lng" => "1.454935"
				),
				array(
					"id" => "127",
					"type" => "adresse",
					"nom" => "commande 2",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.582504",
					"lng" => "1.408081"
				),
				array(
					"id" => "128",
					"type" => "adresse",
					"nom" => "commande 3",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.600615",
					"lng" => "1.419066"
				),
				array(
					"id" => "129",
					"type" => "adresse",
					"nom" => "commande 4",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.607031",
					"lng" => "1.421069"
				),
				array(
					"id" => "130",
					"type" => "adresse",
					"nom" => "commande 5",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.599252",
					"lng" => "1.446620"
				),
				array(
					"id" => "131",
					"type" => "adresse",
					"nom" => "commande 6",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.604438",
					"lng" => "1.441218"
				),
				array(
					"id" => "132",
					"type" => "adresse",
					"nom" => "commande 7",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.599516",
					"lng" => "1.433511"
				),
				array(
					"id" => "133",
					"type" => "adresse",
					"nom" => "commande 8",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.596923",
					"lng" => "1.453357"
				),
				array(
					"id" => "134",
					"type" => "adresse",
					"nom" => "commande 9",
					"adresse" => "3 rue bayard",
					"cp" => "31000",
					"ville" => "Toulouse",
					"lat" => "43.596263",
					"lng" => "1.443100"
				),
                array(
                    "id" => "135",
                    "type" => "adresse",
                    "nom" => "commande 10",
                    "adresse" => "3 rue bayard",
                    "cp" => "31000",
                    "ville" => "Toulouse",
                    "lat" => "43.593890",
                    "lng" => "1.425681"
                )
			);
			return json_encode($data);
		}
    }
}

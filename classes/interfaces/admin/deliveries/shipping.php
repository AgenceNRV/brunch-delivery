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
		 * @var string
		 */
		protected $date = "";

        /**
         * Class constructor
         * @method __construct
         * @return void
         */
        public function __construct()
        {
            $this->register_actions();
            $this->base_url = admin_url('admin.php') . "?page=" . admin_menu::slug . "&setting=" . self::setting;
            $this->action_url = admin_url('admin-post.php');
			$this->date = $_GET['date'] ?? nrvbd_next_delivery_date('d/m/Y');

        }


        /**
         * Generate the main interface
         * @method interface
         * @return html
         */
        public function interface()
        {		
			?>
			<p id="message-area" class="notice notice-nrvbd" style="display:none;"></p>
			<p class="notice notice-nrvbd">
				<?= __('Select drivers, then destinations','nrvbd');?>
			</p>
			<div class="nrvbd-d-flex nrvbd-flex-wrap nrvbd-jc-flex-start" >
                <div class="container-map nrvbd-col-8">
					<?= $this->interface_map_filters(); ?>
                    <div id="googleMap"></div>
                </div>
                <div class="right-container nrvbd-col-4 nrvbd-pl-2 nrvbd-d-flex nrvbd-flex-col nrvbd-jc-space-between">
					<input type="hidden" id="nrvbd-selected-date" value="<?= $this->date;?>">
                    <div class="container-drivers nrvbd-flex-grow-1" id="container-drivers"></div>
                    <div class="container-submit nrvbd-d-flex nrvbd-jc-space-between">
						<button id="save-btn" 
								class="nrvbd-button-primary-outline">
							<?= __('Save the draft', 'nrvbd');?>
						</button>
						<button id="submit-btn" 
								class="nrvbd-button-primary" 
								disabled>
							<?= __('Validate & Send to driver', 'nrvbd');?>
						</button>
					</div>
                </div>
			</div>
			<div class="nrvbd-loader" style="">
				<div class="nrvbd-spinner"></div>
			</div>
			<?php
        }


		/**
		 * Generate the map filters
		 * @method interface_map_filters
		 * @return html
		 */
		public function interface_map_filters()
		{
			$dates = nrvbd_get_brunch_dates();
			ob_start();
			?>
			<div class="nrvbd-d-flex nrvbd-mb-2 nrvbd-jc-space-between">
				<form class="nrvbd-d-flex nrvbd-col-4 nrvbd-jc-space-between"
					  action="<?= admin_url('admin.php');?>"
					  method="GET">
					<input type="hidden" name="page" value="<?= admin_menu::slug;?>">
					<input type="hidden" name="setting" value="<?= self::setting;?>">
					<label for="date" class="nrvbd-fw-4 nrvbd-as-center"><?= __('Delivery date','nrvbd');?></label>
					<select name="date" id="date-selector" class="nrvbd-col-5">
						<?php 
						foreach($dates as $date){
							$selected = "";
							if($date == $this->date){
								$selected = "selected";
							}
							?>
							<option value="<?= $date; ?>" <?= $selected;?>><?= $date; ?></option>
							<?php 
						}
						?>
					</select>
					<button type="submit" class="nrvbd-button-primary">
						<?= __('Go to date', 'nrvbd');?>
					</button>
				</form>
				<div class="label-drivers">
					<button id="btn-hidelabels" 
							title="<?= __('Hide labels','nrvbd');?>"
							class="nrvbd-button-success">
						<span class="material-symbols-outlined off nrvbd-fs-2-i" style="vertical-align: bottom;">
							label_off
						</span>
						<span class="material-symbols-outlined on nrvbd-fs-2-i" style="vertical-align: bottom;">
							label
						</span>
					</button>
					<button id="btn-initBounds"
							title="<?= __('Refocus','nrvbd');?>"
							class="nrvbd-button-primary">
						<span class="material-symbols-outlined nrvbd-fs-2-i" style="vertical-align: bottom;">
						zoom_out_map
						</span>
					</button>
				</div>
			</div>
			<?php
			return ob_get_clean();
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
			add_action('wp_ajax_nrvbd-save-shipping-map', [$this, 'save_shipping_map']);
        }


		/**
		 * Register the assets in the wordpress loop
		 * @method register_assets
		 * @return void
		 */
		public function register_assets()
		{
			if(is_nrvbd_plugin_page() && isset($_GET['setting']) && $_GET['setting'] == self::setting){
				$this->json_shipping_data();
                wp_deregister_script('jquery');
                wp_enqueue_script('jquery', helpers::js_url('jquery.min.js'), array(), '3.7.1', true);

                wp_enqueue_style('admin-shipping-material-icons', 
						         'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0');
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
								  array("jquery","jquery-ui","markerLabel","sortableJs","sortableJquery","nrvbd-framework"),
								  nrvbd_plugin_version(),
								  true);
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_shipping_data', $this->temp_json_type());
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_shipping_ajax', admin_url('admin-ajax.php'));
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_shipping_draft', $this->json_draft_data());
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_API_KEY', nrvbd_api_key());	
			}
		}


		public function json_shipping_data()
		{
			$orders = nrvbd_get_orders_by_brunch_date($this->date);
			$drivers = nrvbd_get_drivers(array(), true);
			$collection = array();
			foreach($drivers as $driver)
			{
				$collection[] = array(
					"id" => $driver->ID,
					"type" => "driver",
					"color" => $driver->color,
					"nom" => $driver->firstname . " " . $driver->lastname,
					"adresse" => $driver->address1 . ' ' . $driver->address2,
					"cp" => $driver->zipcode,
					"ville" => $driver->city,
					"lat" => $driver->latitude,
					"lng" => $driver->longitude
				);
			}
			foreach($orders as $order){
				$collection[] = array(
					"id" => $order->ID,
					"type" => "adresse",
					// "nom" => $order->get_shipping_firstname() . ' ' . $order->get_shipping_lastname(),
					// "adresse" => $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2(),
					// "cp" => $order->get_shipping_postcode(),
					// "ville" => $order->get_shipping_city(),
					// "lat" => '$order->get_customer_latitude()',
					// "lng" => '$order->get_customer_longitude()'
				);
			}
			?>

			<?php
		}


		/**
		 * Return the draft data
		 * @method json_draft_data
		 * @return json
		 */
		public function json_draft_data()
		{
			$shipping = nrvbd_get_shipping_by_date($this->date, true);
			return $shipping->data;
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


		/**
		 * Save the shipping map
		 * @method save_shipping_map
		 * @return void
		 */
		public function save_shipping_map()
		{
			if(!empty($_POST['date'])){
				$date = $_POST['date'];
				$shipping = nrvbd_get_shipping_by_date($date, true);
				$shipping->delivery_date = $date;
				$shipping->data = $_POST['data'];
				$shipping->save();
				wp_send_json_success(nrvbd_error_message('10201'), 201);
			}
			wp_send_json_error(nrvbd_error_message('10400'), 400);
		}
    }
}

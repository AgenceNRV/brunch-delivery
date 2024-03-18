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
			$orders = nrvbd_get_orders_by_brunch_date($this->date);
			$shipping = nrvbd_get_shipping_by_date($this->date, true);
			$missing_coords = array();
			foreach($orders as $order){
				if($order->get_meta("_shipping_latitude") == "" || $order->get_meta("_shipping_longitude") == ""){
					$missing_coords[] = $order->ID;
					$error = nrvbd_get_coordinate_error_by("order_id", $order->ID);	
					if(!$error->db_exists()){
						$error->order_id = $order->ID;
					}
					$error->fixed = false;
					$error->save();			
				}
			}
			if(!empty($missing_coords)){
				?>
				<div class="notice notice-error notice-nrvbd">
					<p>
						<?= __('Some coordinates are missing, please fix them before continuing :', 'nrvbd');?>
					</p>
					<p>
						<?php
						echo __('Orders : ', 'nrvbd');
						foreach($missing_coords as $order_id){
							?>
							<a href="<?= admin_url('admin.php?page=wc-orders&id=' . $order_id . '&action=edit');?>" 
							   target="_blank"
							   class="nrvbd-mr-1">
								#<?= $order_id;?>
							</a>
							<?php
						}
						?>
					</p>
				</div>
				<?php
			}
			?>
			<p id="message-area" class="notice notice-nrvbd" style="display:none;"></p>
			<?php
			if($shipping->validated){
				?>
				<p class="notice notice-success notice-nrvbd">
					<?= __('The delivery has already been validated and sent to the drivers.','nrvbd');?>
				</p>
				<?php
			}
			?>
			<p class="notice notice-nrvbd">
				<?= __('Select drivers, then destinations','nrvbd');?>
			</p>
			<p class="notice notice-warning notice-nrvbd">
				<?= __('The drivers with missing GPS Location or email are not shown.','nrvbd');?>
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
						<?php						
						if(empty($missing_coords)){
						?>
						<button id="submit-btn" 
								class="nrvbd-button-primary" 
								disabled>
							<?= __('Validate & Send to driver', 'nrvbd');?>
						</button>
						<?php
						}
						?>
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
			add_action('wp_ajax_nrvbd-send-shipping', [$this, 'send_shipping']);
        }


		/**
		 * Register the assets in the wordpress loop
		 * @method register_assets
		 * @return void
		 */
		public function register_assets()
		{
			if(is_nrvbd_plugin_page() && isset($_GET['setting']) && $_GET['setting'] == self::setting){
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
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_shipping_data', $this->json_shipping_data());
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_shipping_ajax', admin_url('admin-ajax.php'));
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_shipping_draft', $this->json_draft_data());
				wp_localize_script('nrvbd-admin-shipping', 'nrvbd_API_KEY', nrvbd_api_key());	
			}
		}


		/**
		 * Return the shipping data
		 * @method json_shipping_data
		 * @return json
		 */
		public function json_shipping_data()
		{
			$collection = nrvbd_get_shipping_data_by_date($this->date);
			return json_encode($collection);
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
				$data = stripslashes($_POST['data']);
				if($data != ''){
					$data = json_decode($data, true);
					if(is_array($data) && !empty($data)){
						$shipping->data = $data;
						$shipping->save();
						wp_send_json_success(nrvbd_error_message('10201'), 201);
					}
				}
			}
			wp_send_json_error(nrvbd_error_message('10400'), 400);
		}


		/**
		 * Save the shipping map
		 * @method save_shipping_map
		 * @return void
		 */
		public function send_shipping()
		{
			if(!empty($_POST['date'])){
				$date = $_POST['date'];
				$shipping = nrvbd_get_shipping_by_date($date, true);
				$shipping->delivery_date = $date;
				$shipping->data = json_decode(stripslashes($_POST['data']), true);
				$shipping->save();
				$total_sent = 0;
				$total_failed = 0;
				$driver_sent = array();
				if(is_array($shipping->data) && !empty($shipping->data)){
					foreach($shipping->data as $data){
						$driver_id = $data['driver'] ?? null;
						$driver = new \nrvbd\entities\driver($driver_id);
						$sent = false;
						if(in_array($driver->ID, $driver_sent)){
							continue;
						}
						if($driver->db_exists() && isset($data['adresses']) && !empty($data['adresses'])){
							$delivery_pdf = new \nrvbd\entities\delivery_pdf();
							$delivery_pdf->set_driver($driver);
							$delivery_pdf->delivery_date = $date;
							$delivery_pdf->data = $data;
							$delivery_pdf->save();

							$email = new \nrvbd\entities\email();
							$email->set_driver($driver);
							$email->driver_email = $driver->email;
							$email->addresses = $data['adresses'];
							$email->delivery_date = $date;
							$email->set_delivery_pdf($delivery_pdf);
							$email->save();
							$sent = nrvbd_send_driver_delivery_route_mail($email, $delivery_pdf);
						}
						if($sent === true){
							$driver_sent[] = $driver->ID;
							$total_sent++;
						}else{
							$total_failed++;
						}						
					}
					$resp = array('total_sent' => $total_sent,
								  'total_failed' => $total_failed);
					if($total_sent > 0 && $total_failed == 0){
						$resp['message'] = __('All emails have been sent', 'nrvbd');
						$resp['type'] = "success";
					}else if($total_sent > 0 && $total_failed > 0){
						$resp['message'] = __('Some emails have been sent', 'nrvbd');
						$resp['type'] = "warning";
					}else{
						$resp['message'] = __('No email has been sent', 'nrvbd');
						$resp['type'] = "error";
					}
					$shipping_pdf = new \nrvbd\entities\delivery_pdf($shipping->delivery_pdf_id);
					$shipping_pdf->delivery_date = $shipping->delivery_date;
					$shipping_pdf->data = $shipping->data;
					$shipping_pdf->generate_pdf();
					$shipping_pdf->save();
					$shipping->set_delivery_pdf($shipping_pdf);
					$shipping->validated = true;
					$shipping->save();					
					nrvbd_send_admin_delivery_mail($shipping);
					wp_send_json_success($resp, 201);
				}else{
					wp_send_json_error(nrvbd_error_message('11404'), 404);
				}
			}
			wp_send_json_error(nrvbd_error_message('10400'), 400);
		}
    }
}

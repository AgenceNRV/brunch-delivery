<?php
/**
 * Coordinate errors interface
 *
 * @package  nrvbd/classes/interfaces/admin
 * @version  0.9.0
 * @since    0.9.0
 */

namespace nrvbd\interfaces\admin;

use nrvbd\admin_menu;
use nrvbd\media;
use nrvbd\helpers;

if(!class_exists('\nrvbd\interfaces\admin\coordinates_errors')){
    class coordinates_errors{
		const slug = "nrvbd-coordinates-errors";
		const setting = "coordinates-errors";
		const setting_fix = "coordinates-errors-fix";
	

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
		 * errors_info
		 * @var array
		 */
		protected $errors_info;


        /**
         * Class constructor
         * @method __construct
         * @return void
         */
        public function __construct()
        {
			$args = array('fixed' => '0');
			$this->errors_info = nrvbd_get_coordinate_errors_info($args);
			$this->register_menu();
            $this->register_actions();
            $this->base_url = admin_url('admin.php') . "?page=" . self::slug . "&setting=" ;
            $this->action_url = admin_url('admin-post.php');		
        }



		public function interface()
		{
			?>
			<div class="nrvbd-wrap tbg-white wrap">
				<div class="nrvbd-admin-wrapper nrvbd-mt-3">
					<div class="nrvbd-setting-wrap">
					<?php
					if(isset($_GET['setting']) && $_GET['setting'] == self::setting_fix){
						$this->interface_form_fix_address();
					}else{
						$this->interface_list();
					}
					?>				
					</div>
				</div>
			</div>
			<?php
		}


        /**
         * Generate the main interface
         * @method interface
         * @return html
         */
        public function interface_list()
        {	
			$args = array('fixed' => 0,
						  'page' => $_GET['paged'] ?? 1,
						  'per_pages' => $_GET['per_pages'] ?? 20);
			$page_info = nrvbd_get_coordinate_errors_info($args);
			$page_data = nrvbd_get_coordinate_errors($args, true);
			echo $this->pagination($page_info);
			?>
			<table class="wp-list-table widefat striped">
				<thead>
					<?= $this->interface_table_column_names(); ?>
				</thead>
				<tbody>
					<?php
					if(empty($page_data)){
						?>
						<tr>
							<td colspan="5"><?= __('No errors found.','nrvbd');?></td>
						</tr>
						<?php
					}else{
						foreach($page_data as $error){
							$type = "";
							if($error->order_id !== null){
								$type = "order";
								$WC_Order = $error->get_order();
							}else if($error->user_id !== null){
								$type = "user";
								$WP_User = $error->get_user();
							}else if($error->driver_id !== null){
								$type = "driver";
								$Driver = $error->get_driver();
							}
							?>
							<tr>
								<td><?= $type; ?></td>
								<td>
									<?php
									if($type == "order" && $WC_Order !== null){
										echo $this->interface_table_column_info_order($WC_Order);
									}else if($type == "user" && $WP_User !== null){
										echo $this->interface_table_column_info_user($WP_User);
									}else if($type == "driver" && $Driver !== null){
										echo $this->interface_table_column_info_driver($Driver);
									}
									?>
								</td>
								<td>
									<?php
									if($type == "order"){
										echo $this->interface_table_column_delivery_order($WC_Order->get_id());
									}else if($type == "user"){
										echo $this->interface_table_column_delivery_user($WP_User->ID);
									}else if($type == "driver"){
										echo "-";
									}
									?>
								</td>
								<td><?= $error->created_at; ?></td>
								<td>
									<?php
									$fix_href = $this->base_url . self::setting_fix . "&id=" . $error->ID . "&type=" . $type;
									?>
									<a class="nrvbd-button-primary thickbox"
									   href="<?= $fix_href ?>" >
									    <span class="dashicons dashicons-admin-tools nrvbd-mr-1"></span>
										<?= __('Fix', 'nrvbd'); ?>
									</a>
									<?php
									$del_href = wp_nonce_url( add_query_arg( array( 
										'action' => 'nrvbd-delete-error', 
										'id' => $error->ID
									), 'admin-post.php'), 'nrvbd-delete-error');
									?>
									<a class="nrvbd-must-confirm nrvbd-button-danger nrvbd-ml-1"
										confirm-href="<?= $del_href;?>"
										confirm-message="<?= __("You're about to delete this error. Do you want to continue ?", "nrvbd");?>" 
										style="cursor:pointer">
									    <span class="dashicons dashicons-trash nrvbd-mr-1"></span>
										<?= __('Delete', 'nrvbd'); ?>
									</a>
								</td>
							</tr>
							<?php
						}
					}
					?>
				</tbody>
				<tfoot>
					<?= $this->interface_table_column_names(); ?>
				</tfoot>
			</table>
			<?php
			echo $this->pagination($page_info);
        }


		/**
		 * Generate the info column for an order
		 * @method interface_table_column_info_order
		 * @param  WC_Order $WC_Order
		 * @return string
		 */
		public function interface_table_column_info_order($WC_Order)
		{
			ob_start();
			if($WC_Order){
				?>
				<div>
					<span class="order-name"><?= __('Order #', 'nrvbd') . ' <b>' . $WC_Order->get_id().'</b>';?></span>
					<address class="order-shipping">
						<?= __('Address 1 : ', 'nrvbd') . ' ' . stripslashes($WC_Order->get_shipping_address_1());?><br>
						<?= __('Address 2 : ', 'nrvbd') . ' ' . stripslashes($WC_Order->get_shipping_address_2());?><br>
						<?= __('Zipcode : ', 'nrvbd') . ' ' . stripslashes($WC_Order->get_shipping_postcode());?><br>
						<?= __('City : ', 'nrvbd') . ' ' . stripslashes($WC_Order->get_shipping_city());?>
					</address>
				</div>
				<?php
			}
			return ob_get_clean();
		}


		/**
		 * Print the delivery dates for an order
		 * @method interface_table_column_delivery_order
		 * @param  string|int $order_id
		 * @return string
		 */
		public function interface_table_column_delivery_order($order_id)
		{
			$dates = nrvbd_get_order_shipping_dates($order_id);
			ob_start();
			?>
			<span class="order-delivery-dates"><?= implode(', ', $dates);?></span>
			<?php
			return ob_get_clean();
		}


		/**
		 * Generate the column info for an user
		 * @method interface_table_column_info_user
		 * @param  WP_user $WP_User
		 * @return string
		 */
		public function interface_table_column_info_user($WP_User)
		{
			ob_start();
			if($WP_User){
				?>
				<div>
					<span class="user-name"><?= __('User #', 'nrvbd') . ' <b>' . $WP_User->ID.'</b>';?></span>
					<address class="user-shipping">
						<?= __('Address 1 : ', 'nrvbd') . ' ' . stripslashes($WP_User->get('shipping_address_1'));?><br>
						<?= __('Address 2 : ', 'nrvbd') . ' ' . stripslashes($WP_User->get('shipping_address_2'));?><br>
						<?= __('Zipcode : ', 'nrvbd') . ' ' . stripslashes($WP_User->get('shipping_postcode'));?><br>
						<?= __('City : ', 'nrvbd') . ' ' . stripslashes($WP_User->get('shipping_city'));?>
					</address>
				</div>
				<?php
			}
			return ob_get_clean();
		}


		/**
		 * Print the orders and delivery dates for an user
		 * @method interface_table_column_delivery_user
		 * @param  string|int $user_id
		 * @return void
		 */
		public function interface_table_column_delivery_user($user_id)
		{
			$inc_shippings = nrvbd_get_user_incoming_shippings($user_id);
			ob_start();
			?>
			<div class="order-deliveries">
				<?php
				foreach($inc_shippings as $order_id){
					?>
					<span>
						<?= __('Order(s) ', 'nrvbd') . '<b>#' . $order_id . '</b> : ';?>
						<?= $this->interface_table_column_delivery_order($order_id);?>
					</span><br>
					<?php
				}
				?>
			</div>
			<?php
			return ob_get_clean();
		}


		/**
		 * Generate the column info for a driver
		 * @method interface_table_column_info_driver
		 * @param  \nrvbd\entities\driver $Driver
		 * @return string
		 */
		public function interface_table_column_info_driver($Driver)
		{
			ob_start();
			?>
			<div>
				<span class="user-name"><?= __('Driver #', 'nrvbd') . ' <b>' . $Driver->ID.'</b>';?></span>
				<address class="user-shipping">
					<?= __('Address 1 : ', 'nrvbd') . ' ' . stripslashes($Driver->address1);?><br>
					<?= __('Address 2 : ', 'nrvbd') . ' ' . stripslashes($Driver->address2);?><br>
					<?= __('Zipcode : ', 'nrvbd') . ' ' . stripslashes($Driver->zipcode);?><br>
					<?= __('City : ', 'nrvbd') . ' ' . stripslashes($Driver->city);?>
				</address>
			</div>
			<?php
			return ob_get_clean();
		}



		/**
		 * Generate the column names
		 * @method interface_table_column_names
		 * @return string
		 */
		public function interface_table_column_names()
		{
			ob_start();
			?>
			<tr>
				<th><?= __('Type', 'nrvbd'); ?></th>
				<th><?= __('Info', 'nrvbd'); ?></th>
				<th><?= __('Deliveries', 'nrvbd'); ?></th>
				<th><?= __('Date', 'nrvbd'); ?></th>
				<th><?= __('Options', 'nrvbd'); ?></th>
			</tr>
			<?php
			return ob_get_clean();
		}


		/**
		 * The fix address form
		 * @method interface_form_fix_address
		 * @return html
		 */
		public function interface_form_fix_address()
		{
			$error_id = $_GET['id'];
			$type = $_GET['type'];	
			$error = new \nrvbd\entities\coordinates_errors($error_id);
			$args = array();
			if($error->db_exists() && $type == "order"){
				$WC_Order = $error->get_order();
				$args = array(
					"shipping_address_1" => $WC_Order->get_shipping_address_1(),
					"shipping_address_2" => $WC_Order->get_shipping_address_2(),
					"shipping_postcode" => $WC_Order->get_shipping_postcode(),
					"shipping_city" => $WC_Order->get_shipping_city(),
					"shipping_latitude" => $WC_Order->get_meta("_shipping_latitude"),
					"shipping_longitude" => $WC_Order->get_meta("_shipping_longitude")

				);
			}else if($error->db_exists() && $type == "user"){
				$WP_User = $error->get_user();
				$args = array(
					"shipping_address_1" => $WP_User->get('shipping_address_1'),
					"shipping_address_2" => $WP_User->get('shipping_address_2'),
					"shipping_postcode" => $WP_User->get('shipping_postcode'),
					"shipping_city" => $WP_User->get('shipping_city'),
					"shipping_latitude" => $WP_User->get('_shipping_latitude'),
					"shipping_longitude" => $WP_User->get('_shipping_longitude')
				);
			}else if($error->db_exists() && $type == "driver"){
				$driver = new \nrvbd\entities\driver($error->driver_id);	
				$args = array(
					"shipping_address_1" => $driver->address1,
					"shipping_address_2" => $driver->address2,
					"shipping_postcode" => $driver->zipcode,
					"shipping_city" => $driver->city,
					"shipping_latitude" => $driver->longitude,
					"shipping_longitude" => $driver->latitude
				);
			}
			if(!empty($args)){
				?>
				<h1><?= __('Fix the error','nrvbd');?></h1>
				<?php
				if($type == "order"){
					?>
					<h2><?= __('Order #','nrvbd');?><?= $WC_Order->get_id();?> <?= $WC_Order->get_shipping_last_name();?> <?= $WC_Order->get_shipping_first_name();?></h2>
					<?php
				}elseif($type == "user"){
					?>
					<h2><?= __('User #','nrvbd');?><?= $WP_User->ID;?></h2>
					<?php
				}elseif($type == "driver"){
					?>
					<h2><?= __('Driver #','nrvbd');?><?= $driver->ID;?> <?= $driver->lastname;?> <?= $driver->firstname;?></h2>
					<?php
				}
				?>
				<form id="fix-address-form" 
					  method="post" 
					  action="<?= $this->action_url;?>"
					  class="nrvbd-col-6">
					<?php
					wp_nonce_field('nrvbd-fix-address');
					?>
					<input type="hidden" name="action" value="nrvbd-fix-address" />
					<input type="hidden" name="id" value="<?= $error_id; ?>" />
					<input type="hidden" name="type" value="<?= $type; ?>" />
					<input type="hidden" name="referer" value="<?= $_SERVER['HTTP_REFERER']; ?>" />
					<fieldset>
						<legend><?= __('Address','nrvbd');?></legend>
						<div class="nrvbd-d-flex nrvbd-ai-center nrvbd-mb-1">
							<label for="address_1"
							       class="nrvbd-col-4">
								<?= __('Address 1','nrvbd');?>
							</label>
							<input type="text" 
								   name="address_1" 
								   id="address_1" 
								   class="nrvbd-col-8"
								   value="<?= stripslashes($args['shipping_address_1'] ?? "");?>" />
						</div>
						<div class="nrvbd-d-flex nrvbd-ai-center nrvbd-mb-1">
							<label for="address_2"
							       class="nrvbd-col-4">
								<?= __('Address 2','nrvbd');?>
							</label>
							<input type="text" 
								   name="address_2" 
								   id="address_2" 
								   class="nrvbd-col-8"
								   value="<?= stripslashes($args['shipping_address_2'] ?? "");?>" />	
						</div>
						<div class="nrvbd-d-flex nrvbd-ai-center nrvbd-mb-1">
							<label for="postcode"
							       class="nrvbd-col-4">
								<?= __('Zipcode','nrvbd');?>
							</label>
							<input type="text" 
								   name="postcode" 
								   id="postcode" 
								   class="nrvbd-col-8"
								   value="<?= stripslashes($args['shipping_postcode'] ?? "");?>" />
						</div>
						<div class="nrvbd-d-flex nrvbd-ai-center nrvbd-mb-1">
							<label for="city"
							       class="nrvbd-col-4">
								<?= __('City','nrvbd');?>
							</label>
							<input type="text" 
								   name="city" 
								   id="city" 
								   class="nrvbd-col-8"
								   value="<?= stripslashes($args['shipping_city'] ?? "");?>" />
						</div>
					</fieldset>
					<fieldset class="nrvbd-mt-2">
						<legend>
							<?= __('GPS Location', 'nrvbd');?>
							<button id="nrvbd-get-coordinates"
									class="nrvbd-button-success nrvbd-ml-1">
								<span class="dashicons dashicons-location-alt"></span>
							</button>
						</legend>
						<div class="nrvbd-d-flex nrvbd-ai-center nrvbd-mb-1">
							<label for="latitude"
							       class="nrvbd-col-4">
								<?= __('Latitude','nrvbd');?>
							</label>
							<input type="text" 
								   name="latitude" 
								   id="latitude" 
								   class="nrvbd-col-8"
								   value="<?= $args['shipping_latitude'] ?? "";?>"/>
						</div>
						<div class="nrvbd-d-flex nrvbd-ai-center nrvbd-mb-1">
							<label for="longitude"
							       class="nrvbd-col-4">
								<?= __('Longitude','nrvbd');?>
							</label>
							<input type="text" 
								   name="longitude" 
								   id="longitude" 
								   class="nrvbd-col-8"
								   value="<?= $args['shipping_longitude'] ?? "";?>"/>
						</div>
					</fieldset>
					<button type="submit"
						    class="nrvbd-button-primary nrvbd-mt-2">
						<?= __('Save','nrvbd');?>
					</button>
				</form>
				<?php
			}else{
				?>
				<p class="notice notice-error"><?= __('No date found.','nrvbd');?></p>
				<?php
			}
		}



		public function pagination(array $info = array())
		{
			$per_page_options = array(20, 50, 100, 200);
			$per_pages = $_GET['per_pages'] ?? 20;
			ob_start();
			?>
			<div class="nrvbd-col-12 nrvbd-d-flex nrvbd-jc-space-between nrvbd-my-1">
				<div class="nrvbd-col-4 nrvbd-as-center">
					<span><?= __('Total results :','nrvbd');?></span>
					<span><?= $info['total'] ?? 0;?></span>
				</div>
				<form class="nrvbd-col-4 nrvbd-d-flex nrvbd-jc-flex-end nrvbd-pagination-form"
					method="GET" 
					action="<?= admin_url('admin.php');?>">
					<input type="hidden" name="page" value="<?= $_GET['page'] ?? 1;?>">
					<input type="hidden" name="setting" value="<?= $_GET['setting'] ?? self::setting_fix;?>">
					<div class="tool-row tool-jc-space-between" style="align-items: center">
						<span><?= __('Showing','nrvbd');?></span>
						<select name="per_pages" class="nrvbd-mx-1">
							<?php
							foreach($per_page_options as $option){
								?>
								<option value="<?= $option;?>" <?= $option == $per_pages ? "selected" : "";?>>
									<?= $option;?>
								</option>
								<?php
							}
							?>
						</select>
						<span><?= __('results.','nrvbd');?></span>
					</div>
					<div class="nrvbd-ml-2" style="align-items: center;">
						<span><?= __('Page n°','nrv-tools');?></span>
						<input type="number"
							name="paged"
							min="1" 
							max="<?= $info['pages'] ?? 1;?>"
							style="width: 75px;"
							value="<?= $_GET['paged'] ?? 1;?>"/>
					</div>
				</form>
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
			$title = __('Coordinates Errors', 'nrvbd');
			if($this->errors_info['total'] > 0){				
				$bubble = '<span class="nrvbd-error-counter" style="color: white; padding: 3px 5px;border-radius: 15px;font-size:12px;background: red;text-align: center;">'.$this->errors_info['total'].'</span>';
				 $title. ' '.$bubble;
			}
			admin_menu::add(__('Fix coordinate errors', 'nrvbd'), 
							$title,
							'nrvbd_fix_coordinates',
							self::slug,
							array($this, 'interface'),
							10,
							11);
        }


        /**
         * Register the actions in the WP loop
         * @method register_actions
         * @return void
         */
        public function register_actions()
        {    
			add_action('admin_bar_menu', [$this, 'add_admin_bubble'], 999);
			add_action('admin_post_nrvbd-fix-address', [$this, 'fix_address']);
			add_action('admin_post_nrvbd-delete-error', [$this, 'delete_error']);
			add_action('admin_enqueue_scripts', [$this, 'register_assets']);
        }


		public function register_assets()
		{
			if(isset($_GET['setting']) && $_GET['setting'] == self::setting_fix){
				wp_enqueue_script('nrvbd-admin-fix-address', 
								  helpers::js_url('admin-fix-address.js'), 
								  array('jquery'), 
								  nrvbd_plugin_version(), 
								  true);
				wp_localize_script('nrvbd-admin-fix-address', 'nrvbd_API_KEY', nrvbd_api_key());	
			}
		}


		/**
		 * Add admin bubble
		 * @method add_admin_bubble
		 * @param WP_Admin_Bar $admin_bar
		 * @return void
		 */
		public function add_admin_bubble($admin_bar)
		{
			if($this->errors_info['total'] > 0){
				$bubble = '<span class="nrvbd-error-counter" style="padding: 3px 5px;border-radius: 15px;font-size:12px;			background: red;text-align: center;">'.$this->errors_info['total'].'</span>';
				$admin_bar->add_menu([
					'id'    => 'nrvbd-coordinates-errors',
					'title' => __('Coordinates Errors', 'nrvbd') . ' '.$bubble,
					'href'  => $this->base_url . self::setting,
					'meta'  => [
						'class' => 'nrvbd-coordinates-errors',
					],
				]);
			}
		}


        /**
         * Save
         * @method save
         * @return void
         */
        public function save()
        {   
            if(wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-save')){    
				
                wp_safe_redirect($this->base_url . "&error=10201");
            }else{
                wp_safe_redirect($this->base_url . "&error=10403");
            }
        }


		/**
		 * Fix the address
		 * @method fix_address
		 * @return void
		 */
		public function fix_address()
		{
			if(wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-fix-address')){
				$error = new \nrvbd\entities\coordinates_errors($_REQUEST['id']);
				if($_REQUEST['type'] == "order" && $error->order_id !== null){
					$WC_Order = $error->get_order();
					$WC_Order->set_shipping_address_1(stripslashes($_REQUEST['address_1']));
					$WC_Order->set_shipping_address_2(stripslashes($_REQUEST['address_2']));
					$WC_Order->set_shipping_postcode(stripslashes($_REQUEST['postcode']));
					$WC_Order->set_shipping_city(stripslashes($_REQUEST['city']));
					$WC_Order->update_meta_data("_shipping_latitude", $_REQUEST['latitude']);
					$WC_Order->update_meta_data("_shipping_longitude", $_REQUEST['longitude']);
					$WC_Order->save();
				}else if($_REQUEST['type'] == "user" && $error->user_id !== null){
					update_user_meta($error->user_id, 'shipping_address_1', stripslashes($_REQUEST['address_1']));
					update_user_meta($error->user_id, 'shipping_address_2', stripslashes($_REQUEST['address_2']));
					update_user_meta($error->user_id, 'shipping_postcode', stripslashes($_REQUEST['postcode']));
					update_user_meta($error->user_id, 'shipping_city', stripslashes($_REQUEST['city']));
					update_user_meta($error->user_id, '_shipping_latitude', $_REQUEST['latitude']);
					update_user_meta($error->user_id, '_shipping_longitude', $_REQUEST['longitude']);
				}else if($_REQUEST['type'] == "driver" && $error->driver_id !== null){
					$driver = new \nrvbd\entities\driver($error->driver_id);
					$driver->address1 = $_REQUEST['address_1'];
					$driver->address2 = $_REQUEST['address_2'];
					$driver->zipcode = $_REQUEST['postcode'];
					$driver->city = $_REQUEST['city'];
					$driver->longitude = $_REQUEST['longitude']; 
					$driver->latitude = $_REQUEST['latitude'];
					$driver->save();
				}else{
					wp_safe_redirect($this->base_url . self::setting_fix . "&error=10404");
				}

				if($_REQUEST['type'] == "order" && $error->order_id !== null){
					$WC_Order = $error->get_order(true);
					if($WC_Order->get_meta("_shipping_latitude") != '' && $WC_Order->get_meta("_shipping_longitude")){
						$error->fixed = true;
						$error->save();
					}

				}elseif($_REQUEST['type'] == "user" && $error->user_id !== null){
					$WP_User = $error->get_user(true);
					if($WP_User->get('_shipping_latitude') != '' && $WP_User->get('_shipping_longitude')){
						$error->fixed = true;
						$error->save();
					}
				}elseif($_REQUEST['type'] == "driver" && $error->driver_id !== null){
					$Driver = $error->get_driver(true);
					if($Driver->latitude != '' && $Driver->longitude != ''){
						$error->fixed = true;
						$error->save();
					}
				}
				$parsed = parse_url($_REQUEST['referer'] );
				$query = $parsed['query'];
				parse_str($query, $params);
				$params['error'] = "10201";
				$url = http_build_query($params);
                wp_safe_redirect(admin_url('admin.php') . '?' . $url);
			}else{
				wp_safe_redirect($this->base_url . self::setting_fix . "&error=10403");
			}
		}


		/**
		 * Delete an error
		 * @method delete_error
		 * @return void
		 */
		public function delete_error()
		{
			$parsed = parse_url($_SERVER['HTTP_REFERER'] );
			$query = $parsed['query'];
			parse_str($query, $params);
			$params['error'] = "10403";
			if(isset($_REQUEST['_wpnonce']) 
				&& wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-delete-error')){
				if($_REQUEST['id']){
					$error = new \nrvbd\entities\coordinates_errors($_REQUEST['id']);
					if($error->db_exists()){
						$error->delete();
						$params['error'] = "10201";
					}
				}
			}
			$url = http_build_query($params);
			wp_safe_redirect(admin_url('admin.php') . '?' . $url);
		}
    }
}
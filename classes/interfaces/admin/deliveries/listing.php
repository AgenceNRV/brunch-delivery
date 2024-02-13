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

if(!class_exists('\nrvbd\interfaces\admin\deliveries\listing')){
    class listing{
		const setting = "list";

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
		 * dates
		 * @var array
		 */
		protected $dates = array();


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
			$this->dates = nrvbd_get_brunch_dates();
        }


        /**
         * Generate the main interface
         * @method interface
         * @return html
         */
        public function interface()
        {		
			if(empty($this->dates)){
				$this->interface_no_dates();
				return;
			}
			$args = array('page' => $_GET['paged'] ?? 1,
						  'per_pages' => $_GET['per_pages'] ?? 50);
			$date = $_GET['date'] ?? $this->dates[0];
			$page_info = nrvbd_get_orders_by_brunch_date_info($date, $args);
			$page_data = nrvbd_get_orders_by_brunch_date($date, $args);
			echo $this->interface_filters($date);
			echo $this->pagination($page_info);
			?>
			<div>
				<table class="wp-list-table widefat striped">
					<thead>
						<?= $this->interface_table_column_names(); ?>
					</thead>
					<tbody>
						<?php
						if(empty($page_data)){
							?>
							<tr>
								<td colspan="8"><?= __('No orders found for the given date.','nrvbd');?></td>
							</tr>
							<?php
						}else{
							foreach($page_data as $order){
								$order_url = admin_url('admin.php?page=wc-orders&id='.$order->get_id().'&action=edit');
								?>
								<tr>
									<td><a href="<?= $order_url;?>">#<?= $order->get_id();?></a></td>
									<td>
										<?= empty($order->get_shipping_last_name()) ? $order->get_billing_last_name() : $order->get_shipping_last_name();?>
										<?= empty($order->get_shipping_first_name()) ? $order->get_billing_first_name() : $order->get_shipping_first_name();?>
									</td>
									<td>
                                      <?= empty($order->get_shipping_phone()) ? $order->get_billing_phone() : $order->get_shipping_phone();?>
									</td>
									<td>
										<?= $order->get_shipping_address_1();?>
										<?= ($a2 = $order->get_shipping_address_2()) != '' ? '<br>'.$a2 : '';?>
									</td>
									<td><?= $order->get_shipping_postcode(); ?></td>
									<td><?= $order->get_shipping_city();?></td>
									<td>
										<?php
										$lat = $order->get_meta("_shipping_latitude");
										$long = $order->get_meta("_shipping_longitude");
										$has_gps = false;
										if($lat != '' && $long != ''){
											$has_gps = true;
											?>
											<a href="https://www.google.com/maps/search/?api=1&query=<?= $lat; ?>,<?= $long; ?>" 	
											   target="_blank"
											   title="<?= $lat; ?>,<?= $long; ?>"
											   class="nrvbd-fc-success">
											   <span class="dashicons dashicons-location"></span>
											</a>
											<?php
										}else{
											?>
											<span class="dashicons dashicons-no nrvbd-fc-danger"></span>
											<?php
										}
										?>
									</td>
									<td>
										<?php
										if(!$has_gps){
											$error = nrvbd_get_coordinate_error_by('order_id', $order->get_id());
											$error->fixed = 0;
											$error->order_id = $order->get_id();
											$error->save();
											$url = admin_url('admin.php')
												   . "?page=" . \nrvbd\interfaces\admin\coordinates_errors::slug
												   . "&setting=" . \nrvbd\interfaces\admin\coordinates_errors::setting_fix
												   . "&id=" . $error->ID
												   . "&type=order";
											?>
											<a class="nrvbd-button-primary"
												href="<?= $url;?>">
												<span class="dashicons dashicons-location-alt nrvbd-mr-1"></span>
												<?= __('Fix the GPS Coordinates','nrvbd');?>
											</a>
											<?php
										}else{
											echo __('No action required.','nrvbd');
										}
										?>
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
			</div>
			<?php
			echo $this->pagination($page_info);
        }


		public function interface_table_column_names()
		{
			ob_start();
			?>
			<tr>
				<th><?= __('Order','nrvbd');?></th>
				<th><?= __('Customer name','nrvbd');?></th>
				<th><?= __('Phone','nrvbd');?></th>
				<th><?= __('Delivery Address','nrvbd');?></th>
				<th><?= __('Zipcode','nrvbd');?></th>
				<th><?= __('City','nrvbd');?></th>
				<th><?= __('GPS','nrvbd');?></th>
				<th><?= __('Actions','nrvbd');?></th>
			</tr>
			<?php
			return ob_get_clean();
		}


		public function interface_filters($selected_date = null)
		{
			?>
			<div class="nrvbd-d-flex nrvbd-jc-space-between">
				<form class="nrvbd-d-flex nrvbd-filter-form" action="<?= admin_url('admin.php');?>">
					<input type="hidden" name="page" value="<?= admin_menu::slug;?>">
					<input type="hidden" name="setting" value="<?= self::setting;?>">
					<div class="nrvbd-d-flex nrvbd-flex-col">
						<label for="" class="nrvbd-mb-1"><?= __('Select the date','nrvbd');?></label>
						<select name="date">
							<?php
							foreach($this->dates as $date)
							{
								$selected = "";
								if(isset($selected_date) && $selected_date == $date){
									$selected = "selected";
								}
								?>
								<option value="<?= $date; ?>" <?= $selected;?>><?= $date; ?></option>
								<?php
							}
							?>
						</select>
					</div>
				</form>
				
				<div class="nrvbd-d-flex nrvbd-ai-flex-end">
					<?php
					$shipping = nrvbd_get_shipping_by_date($selected_date, true);
					if($shipping->validated == true){
						$pdf = $shipping->get_delivery_pdf();
						?>							
						<a class="nrvbd-button-warning-outline nrvbd-ml-1"
							href="<?= $pdf->get_pdf_url();?>"
							download="<?= $pdf->get_pdf_name();?>"
							style="cursor:pointer; text-decoration:none;">
							<span class="dashicons dashicons-download nrvbd-mr-1"></span>
							<?= __('Download the pdf', 'nrvbd'); ?>
						</a>
						<?php
					}
					?>
					<?php
					$shipping = nrvbd_get_shipping_by_date($selected_date, true);
					if($shipping->validated == true){
						$href = wp_nonce_url($this->action_url . "?action=nrvbd-download-kitchen-pdf&shipping=".$shipping->ID, 'nrvbd-download-kitchen-pdf')
						?>							
						<a class="nrvbd-button-primary-outline nrvbd-ml-1"
							href="<?= $href;?>"
							download="<?= "Note_cuisine_".str_replace("/", "-", $shipping->delivery_date);?>.pdf"
							style="cursor:pointer; text-decoration:none;">
							<span class="dashicons dashicons-editor-kitchensink nrvbd-mr-1"></span>
							<?= __('Download the kitchen note', 'nrvbd'); ?>
						</a>
						<?php
					}
					?>
				</div>
			</div>
			<?php
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
					<input type="hidden" name="setting" value="<?= $_GET['setting'] ?? self::setting;?>">
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


		public function interface_no_dates()
		{
			?>
			<div class="nrvbd-d-flex nrvbd-flex-col nrvbd-as-center nrvbd-jc-center">
				<p class="notice notice-error nrvbd-fs-2 notice-nrvbd"><?= __('No incoming deliveries.','nrvbd');?></p>
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
											   __('List', 'nrvbd'), 
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
			add_action("admin_post_nrvbd-download-kitchen-pdf", [$this, "generate_kitchen_pdf"]);	
			add_action("admin_post_nopriv_nrvbd-download-kitchen-pdf", [$this, "generate_kitchen_pdf"]);				
        }

		

		public function generate_kitchen_pdf()
		{
            if(wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-download-kitchen-pdf') && isset($_REQUEST['shipping'])){    
				$shipping = new \nrvbd\entities\shipping($_REQUEST['shipping']);
				$pdf = new \nrvbd\pdf\kitchen_notes($shipping->delivery_date, $shipping->data);
				$pdf->save("Note_cuisine_" . str_replace("/", "-", $shipping->delivery_date) . ".pdf", "D");
            }else{
                wp_safe_redirect($this->base_url . "&error=10404");
            }
		}

    }
}

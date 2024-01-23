<?php
/**
 * base interface
 *
 * @package  nrvbd/classes/interfaces/admin/drivers
 * @version  0.9.0
 * @since    0.9.0
 */

namespace nrvbd\interfaces\admin\drivers;

use nrvbd\admin_menu;
use nrvbd\helpers;

if(!class_exists('\nrvbd\interfaces\admin\drivers\manage')){
	class manage{

		const slug = "nrvbd-drivers";
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
		 * Class constructor
		 * @method __construct
		 * @return void
		 */
		public function __construct()
		{
			$this->register_actions();
			$this->base_url = admin_url('admin.php') . "?page=" . self::slug . "&setting=";
			$this->action_url = admin_url('admin-post.php');
		}


		/**
		 * Generate the main interface
		 * @method interface
		 * @return html
		 */
		public function interface()
		{		
			$args = array(
				'per_pages' => $_GET['per_pages'] ?? 20,
				'page' => $_GET['paged'] ?? 1 
			);
			$drivers = nrvbd_get_drivers($args, true);
			?>
			<table class="wp-list-table widefat striped table-view-list">
				<thead>
					<?= $this->interface_col_names();?>
				</thead>
				<tbody>
					<?php
					if(!empty($drivers)){
						foreach($drivers as $driver){
							?>
							<tr>
								<td><?= $driver->ID;?></td>
								<td>
									<div  class="nrvbd-color-ball" 
										  style="background-color: <?= $driver->color ?? "#00C853";?>"
										  title="<?= $driver->color ?? "#00C853";?>"">
									</div>
								</td>
								<td><?= $driver->firstname;?></td>
								<td><?= $driver->lastname;?></td>
								<td><?= $driver->phone;?></td>
								<td><?= $driver->email;?></td>
								<td><?= $driver->get_address_html();?></td>
								<td>
									<?php
									$has_gps = false;
									if($driver->longitude != '' && $driver->latitude != ''){
										$has_gps = true;
										?>
										<a href="https://www.google.com/maps/search/?api=1&query=<?= $driver->get_raw_latlong(); ?>" 	
											target="_blank"
											title="<?= $driver->get_raw_latlong(); ?>"
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
								<td>
									<a href="<?= $this->base_url;?>edit&id=<?= $driver->ID;?>"
									   class="nrvbd-button-warning">
										<span class="dashicons dashicons-edit"></span>
									</a>
									<?php
									if(!$has_gps){
										$error = nrvbd_get_coordinate_error_by('driver_id', $driver->ID);
										$url = admin_url('admin.php')
											   . "?page=" . \nrvbd\interfaces\admin\coordinates_errors::slug
											   . "&setting=" . \nrvbd\interfaces\admin\coordinates_errors::setting_fix
											   . "&id=" . $error->ID
											   . "&type=driver";
										?>
										<a class="nrvbd-button-primary nrvbd-ml-2"
											href="<?= $url;?>">
											<span class="dashicons dashicons-location-alt nrvbd-mr-1"></span><?= __('Fix the GPS Coordinates','nrvbd');?>
										</a>
										<?php
									}
										
									$del_url = wp_nonce_url(add_query_arg( array( 
										'action' => 'nrvbd-delete-driver', 
										'driver' => $driver->ID
									), 'admin-post.php'), 'nrvbd-delete-driver');
									?>
									<a confirm-href="<?= $del_url;?>"
									   confirm-message="<?= __("You're about to delete this driver. Do you want to continue ?", "nrvbd");?>" 
									   style="cursor:pointer"
									   class="nrvbd-must-confirm nrvbd-button-danger nrvbd-ml-2"
									   title="<?= __('Delete the driver', 'nrvbd');?>">
										<span class="dashicons dashicons-trash"></span>
									</a>
								</td>
							</tr>
							<?php
						}
					}else{
						?>
						<tr>
							<td colspan="8"><?= __('No drivers found.', 'nrvbd');?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
				<tfoot>
					<?= $this->interface_col_names();?>
				</tfoot>
			</table>
			<?php
		}

	
		public function pagination()
		{
			?>
			<form class="nrvbd_per_pages_form" method="GET" action="<?= admin_url('admin.php');?>">
				<input type="hidden" name="page" value="<?= $_GET['page'];?>">
				<input type="hidden" name="paged" value="<?= $paged;?>">
				<input type="hidden" name="setting" value="<?= $_GET['setting'];?>">
				<input type="hidden" name="sub-setting" value="<?= $_GET['sub-setting'];?>">
				<select name="per_pages" id="per_page_selection">
					<?php
					foreach($per_pages_options as $option){
						?>
						<option value="<?= $option; ?>" <?= $option == $per_pages ? 'selected' : '';?>>
							<?= $option; ?>
						</option>
						<?php
					}
					?>
				</select>
				<span><?= sprintf(__("results shown out of <b>%s</b>", 'nrv-tools'), $total_results);?></span>
			</form>
			<form class="nrvbd_paged_form"method="GET" action="<?= admin_url('admin.php');?>">
				<input type="hidden" name="page" value="<?= $_GET['page'];?>">
				<input type="hidden" name="per_pages" value="<?= $per_pages;?>">
				<input type="hidden" name="setting" value="<?= $_GET['setting'];?>">
				<input type="hidden" name="sub-setting" value="<?= $_GET['sub-setting'];?>">
				<span><?= __("Page n°", 'nrv-tools');?></span>
				<select name="paged" id="paged_selection">
					<?php
					for($i=1; $i <= $total_pages; $i++){
						?>
						<option value="<?= $i; ?>" <?= $i == $paged ? 'selected' : '';?>>
							<?= $i; ?>
						</option>
						<?php
					}
					?>
				</select>
				<span><?= sprintf(__("out of %s page(s)", 'nrv-tools'), $total_pages);?></span>
			</form>
			<?php
		}


		public function interface_col_names()
		{
			ob_start();
			?>
			<tr>
				<th>ID</th>
				<th><?= __('Color', 'nrvbd');?></th>
				<th><?= __('Firstname', 'nrvbd');?></th>
				<th><?= __('Lastname', 'nrvbd');?></th>
				<th><?= __('Phone', 'nrvbd');?></th>
				<th><?= __('Email', 'nrvbd');?></th>
				<th><?= __('Starting address', 'nrvbd');?></th>
				<th><?= __('GPS', 'nrvbd');?></th>
				<th></th>
			</tr>
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
			admin_menu::add_configuration_menu("drivers",
												self::setting, 
												__('Manage the drivers', 'nrvbd'), 
												array($this, 'interface'));
		}


		/**
		 * Register the actions in the WP loop
		 * @method register_actions
		 * @return void
		 */
		public function register_actions()
		{    
			add_action("admin_menu", [$this, "register_menu"], 141);	
			add_action("admin_post_nrvbd-delete-driver", [$this, "delete_driver"]);		
		}


		/**
		 * Delete a driver
		 * @method delete_driver
		 * @return void
		 */
		public function delete_driver()
		{
            if(wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-delete-driver')){    
				$driver = new \nrvbd\entities\driver($_REQUEST['driver']);
				$driver->deleted = true;
				$driver->save();
				wp_safe_redirect($this->base_url . self::setting . "&error=10202");
			}else{
				wp_safe_redirect($this->base_url . self::setting . "&error=10403");
			}
		}
	}
}
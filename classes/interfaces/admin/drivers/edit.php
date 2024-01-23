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

if(!class_exists('\nrvbd\interfaces\admin\drivers\edit')){
	class edit{

		const slug = "nrvbd-drivers";
		const setting_add = "add";
		const setting_edit = "edit";

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
		 * @method interface_add
		 * @return html
		 */
		public function interface_add()
		{		
			$driver = new \nrvbd\entities\driver();
			?>			
			<h2><?= __('Add a new driver', 'nrvbd');?></h2>
			<?= $this->interface_form($driver);?>
			<?php
		}


		/**
		 * Generate the main interface
		 * @method interface_edit
		 * @return html
		 */
		public function interface_edit()
		{		
			$driver = new \nrvbd\entities\driver($_GET['id'] ?? null);
			if($driver->db_exists()){
				?>			
				<h2><?= __('Edit a driver', 'nrvbd');?></h2>
				<?= $this->interface_form($driver);?>
				<?php
			}else{
                wp_safe_redirect($this->base_url . "list&error=10404");
			}
		}



		/**
		 * The edit/add form
		 * @method interface_form
		 * @return string
		 */
		public function interface_form($driver)
		{
			ob_start();
			?>
			<form class="nrvbd-form nrvbd-col-4" 
				  action="<?= $this->action_url ;?>" 
				  method="POST">
				<?php
				if($driver->db_exists()){
					?>
					<input type="hidden" name="ID" value="<?= $driver->ID;?>">
					<?php
				}
				wp_nonce_field('nrvbd-save-driver');
				?>
				<input type="hidden" name="action" value="nrvbd-save-driver"/>

				<fieldset class="nrvbd-fieldset">
					<legend><?= __('General information', 'nrvbd');?></legend>
					<div class="nrvbd-row nrvbd-d-flex">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="firstname"><?= __('Firstname', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-6">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="firstname"
									   value="<?= $driver->firstname;?>"
									   name="firstname"
									   maxlength="200">
							</div>
						</div>
					</div>
					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="lastname"><?= __('Lastname', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-6">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="lastname"
									   value="<?= $driver->lastname;?>"
									   name="lastname"
									   maxlength="200">
							</div>
						</div>
					</div>
					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="phone"><?= __('Phone', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-6">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="phone"
									   value="<?= $driver->phone;?>"
									   name="phone"
									   maxlength="16">
							</div>
						</div>
					</div>
					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="email"><?= __('Email', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-9">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="email" 
									   id="email"
									   value="<?= $driver->email;?>"
									   name="email"
									   maxlength="200">
							</div>
						</div>
					</div>
				</fieldset>

				<fieldset class="nrvbd-fieldset nrvbd-mt-2">
					<legend><?= __('Address information', 'nrvbd');?></legend>
					<div class="nrvbd-row nrvbd-d-flex">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="address_1"><?= __('Address', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-9">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="address_1"
									   value="<?= stripslashes($driver->address1);?>"
									   name="address1"
									   maxlength="200">
							</div>
						</div>
					</div>
					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="address_2"><?= __('Additional address', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-9">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="address_2"
									   value="<?= stripslashes($driver->address2);?>"
									   name="address2"
									   maxlength="200">
							</div>
						</div>
					</div>
					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="postcode"><?= __('Zipcode', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-3">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="postcode"
									   value="<?= $driver->zipcode;?>"
									   name="zipcode"
									   maxlength="10">
							</div>
						</div>
						<div class="nrvbd-col-2 nrvbd-as-end nrvbd-pl-2">
							<label for="city"><?= __('City', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-4">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="city"
								       value="<?= $driver->city;?>"
									   name="city"
									   maxlength="200">
							</div>
						</div>
					</div>
				</fieldset>

				<fieldset class="nrvbd-fieldset nrvbd-mt-2">
					<legend>
						<?= __('Map information', 'nrvbd');?>
						<button class="nrvbd-button-primary nrvbd-ml-1"  type="button"
								id="nrvbd-get-coordinates" data-show="false">
							<span class="dashicons dashicons-location-alt"></span>
							<span class="nrvbd-fs-3"><?= __("Can't find my GPS", 'nrvbd');?></span>
						</button>
					</legend>


                    <div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
                        <div class="nrvbd-col-6 nrvbd-as-end">
                            <div class="nrvbd-row nrvbd-d-flex">
                                <div class="nrvbd-col-6 nrvbd-as-end">
                                    <label for="latitude"><?= __('Latitude', 'nrvbd');?></label>
                                </div>
                                <div class="nrvbd-col-6 nrvbd-d-flex nrvbd-flex-col">
                                    <input type="text"
                                           id="latitude"
                                           value="<?= $driver->latitude;?>"
                                           name="latitude"
                                           maxlength="90">
                                </div>
                            </div>
                            <div class="nrvbd-row nrvbd-d-flex">
                                <div class="nrvbd-col-6 nrvbd-as-end">
                                    <label for="longitude"><?= __('Longitude', 'nrvbd');?></label>
                                </div>
                                <div class="nrvbd-col-6 nrvbd-d-flex nrvbd-flex-col">
                                    <input type="text"
                                           id="longitude"
                                           value="<?= $driver->longitude;?>"
                                           name="longitude"
                                           maxlength="90">
                                </div>
                            </div>
                            <div class="nrvbd-row nrvbd-d-flex">
                                <div class="nrvbd-col-6 nrvbd-as-end">
                                    <label for="color"><?= __('Color', 'nrvbd');?></label>
                                </div>
                                <div class="nrvbd-col-6 nrvbd-d-flex nrvbd-flex-col">
                                    <input type="color"
                                           id="color"
                                           value="<?= $driver->color ?? "#00C853";?>"
                                           name="color">
                                </div>
                            </div>
                        </div>
                        <div class="nrvbd-col-6 imap-container" style="display: none;">
                            <div class="nrvbd-row">
                                <div class="nrvbd-col">
                                    <div id="imap" class="w-100 h-100" style="min-height: 250px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
				</fieldset>
				<div class="nrvbd-d-flex nrvbd-jc-flex-end nrvbd-mt-2">
					<button class="button button-primary"><?= __('Save', 'nrvbd');?></button>
				</div>					
			</form>
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
											   self::setting_add, 
											   __('Add a new driver', 'nrvbd'), 
											   array($this, 'interface_add'));
			admin_menu::add_configuration_menu("drivers",
											   self::setting_edit, 
												__('Edit a driver', 'nrvbd'), 
												array($this, 'interface_edit'),
											    false);
		}


		/**
		 * Register the actions in the WP loop
		 * @method register_actions
		 * @return void
		 */
		public function register_actions()
		{    
			add_action("admin_menu", [$this, "register_menu"], 142);	
			add_action("admin_post_nrvbd-save-driver", [$this, "save"]);	
			add_action('admin_enqueue_scripts', [$this, 'register_assets'], 12);	
		}


		/**
		 * Register the assets
		 * @method register_assets
		 * @return void
		 */
		public function register_assets()
		{
			if(isset($_GET['setting']) && ($_GET['setting'] == self::setting_add || $_GET['setting'] == self::setting_edit)){
				wp_enqueue_script('nrvbd-admin-fix-address', 
								  helpers::js_url('admin-fix-address.js'), 
								  array('jquery', 'nrvbd-framework'), 
								  nrvbd_plugin_version(), 
								  true);
				wp_localize_script('nrvbd-admin-fix-address', 'nrvbd_API_KEY', nrvbd_api_key());	
			}
		}


        /**
         * Save
         * @method save
         * @return void
         */
        public function save()
        {   
            if(wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-save-driver')){    
				$data = $_POST;
				unset($data['_wpnonce']);
				unset($data['_wp_http_referer']);
				unset($data['action']);
				unset($data['ID']);
				$driver = new \nrvbd\entities\driver($_POST['ID'] ?? null);
				$old_address = trim($driver->get_raw_address() ?? '');
				$driver->init_from_array($data);
				$new_address = trim($driver->get_raw_address() ?? '');
				$driver->save();
				if($new_address == '' && ($data['longitude'] == "" || $data['latitude'])){
					nrvbd_save_coordinates_error($driver->ID, 'driver', 'The address is empty');
				}
				if( $new_address != "" &&
					($old_address != $new_address
					|| ($data['longitude'] == "" || $data['latitude'] == ""))){
					try{
						$req_gps = nrvbd_get_address_gps($new_address);	
						if(is_wp_error($req_gps)){
							nrvbd_save_coordinates_error($driver->ID, 'driver', $req_gps);
						}else{					
							$data = json_decode(wp_remote_retrieve_body($req_gps), true);
							if($data['status'] == 'OK'){
								$latitude = $data['results'][0]['geometry']['location']['lat'];
								$longitude = $data['results'][0]['geometry']['location']['lng'];
								if(!in_array($longitude, ['', 0]) && !in_array($latitude, ['', 0])){
									$driver->latitude = $latitude;
									$driver->longitude = $longitude;
									$driver->save();
								}
							}else{
								nrvbd_save_coordinates_error($driver->ID, 'driver', $data);
							}
						}
					}catch(\Exception $e){
						nrvbd_save_coordinates_error($driver->ID, 'driver', $e->getMessage());
					}
				}
				if($driver->db_exists()){
                	wp_safe_redirect($this->base_url . self::setting_edit . "&id=" . $driver->ID . "&error=10201");
				}else{
                	wp_safe_redirect($this->base_url . self::setting_add . "&error=10201");
				}
            }else{
				if(isset($_POST['ID'])){
					wp_safe_redirect($this->base_url . self::setting_edit . "&id=" . $_POST['ID'] . "&error=10403");
				}else{
					wp_safe_redirect($this->base_url . self::setting_add . "&error=10403");
				}
            }
        }

	}
}

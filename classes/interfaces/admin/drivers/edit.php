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
					<input type="hidden" name="config[ID]" value="<?= $driver->ID;?>">
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
							<label for="address1"><?= __('Address', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-9">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="address1"
									   value="<?= $driver->address1;?>"
									   name="address1"
									   maxlength="200">
							</div>
						</div>
					</div>
					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="address2"><?= __('Additional address', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-9">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="address2"
									   value="<?= $driver->address2;?>"
									   name="address2"
									   maxlength="200">
							</div>
						</div>
					</div>
					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="zipcode"><?= __('Zipcode', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-3">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="zipcode"
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
					<legend><?= __('Map information', 'nrvbd');?></legend>
					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="latitude"><?= __('Latitude', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-3">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="latitude"
									   value="<?= $driver->latitude;?>"
									   name="latitude"
									   maxlength="90">
							</div>
						</div>
					</div>

					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="longitude"><?= __('Longitude', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-3">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="text" 
									   id="longitude"
									   value="<?= $driver->longitude;?>"
									   name="longitude"
									   maxlength="90">
							</div>
						</div>
					</div>

					<div class="nrvbd-row nrvbd-d-flex nrvbd-mt-1">
						<div class="nrvbd-col-3 nrvbd-as-end">
							<label for="color"><?= __('Color', 'nrvbd');?></label>
						</div>
						<div class="nrvbd-col-3">
							<div class="nrvbd-col nrvbd-d-flex nrvbd-flex-col">
								<input type="color" 
									   id="color"
									   value="<?= $driver->color ?? "#00C853";?>"
									   name="color">
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
			add_action("admin_menu", [$this, "register_menu"], 141);	
			add_action("admin_post_nrvbd-save-driver", [$this, "save"]);		
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
				$driver->init_from_array($data);
				$driver->save();
                wp_safe_redirect($this->base_url . self::setting_add . "&error=10201");
            }else{
                wp_safe_redirect($this->base_url . self::setting_add . "&error=10403");
            }
        }

	}
}
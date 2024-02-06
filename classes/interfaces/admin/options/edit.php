<?php
/**
 * base interface
 *
 * @package  nrvbd/classes/interfaces/admin/options
 * @version  0.9.0
 * @since    0.9.0
 */

namespace nrvbd\interfaces\admin\options;

use nrvbd\admin_menu;
use nrvbd\helpers;

if(!class_exists('\nrvbd\interfaces\admin\options\edit')){
	class edit{

		const slug = "nrvbd-options";
		const setting = "edit";

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
		 * options
		 * @var array
		 */
		protected $options = array();

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
			$this->options = array(
				"nrvbd_option_API_KEY" => array(
					'type' => 'text',
					'label' => __('API Key', 'nrvbd'),
					'value' => stripslashes(get_option('nrvbd_option_API_KEY', nrvbd_api_key()))
				),
				
				"nrvbd_option_ADMIN_EMAIL" => array(
					'type' => 'text',
					'label' => __('Admin email', 'nrvbd'),
					'description' => __('The email that will receive the complete list of deliveries, several emails separated by commas, only the first will be sent in cc, the others in bcc.', 'nrvbd'),
					'value' => stripslashes(get_option('nrvbd_option_ADMIN_EMAIL', ''))
				)
			);
		}



		/**
		 * The main interface
		 * @method interface_form
		 * @return string
		 */
		public function interface()
		{
			?>
			<form class="nrvbd-form nrvbd-col-4" 
				  action="<?= $this->action_url ;?>" 
				  method="POST">
				<h1><?= __('Customize the plugin options','nrvbd');?></h1>
				<?php
				wp_nonce_field('nrvbd-save-options');
				?>
				<input type="hidden" name="action" value="nrvbd-save-options"/>
				<?php
				foreach($this->options as $option => $values){
					echo $this->interface_field($values['type'], $option, $values['label'], $values['value'], $values['description'] ?? "");
				}
				?>
				<div class="nrvbd-d-flex nrvbd-jc-flex-end nrvbd-mt-2">
					<button class="button button-primary"><?= __('Save', 'nrvbd');?></button>
				</div>					
			</form>
			<?php
		}


		public function interface_field(string $type,
										string $name, 
										string $label,
										string $value, 
										string $description = "")
		{
			ob_start();
			switch($type){
				case 'text':
					?>
					<div class="nrvbd-d-flex nrvbd-py-1">
						<label for="field_<?= $name;?>" class="nrvbd-col-4 nrvbd-as-start" style="margin-top:5px;"><?= $label;?></label>
						<div class="nrvbd-d-flex nrvbd-flex-col nrvbd-col-8">
							<input type="text" 
								name="<?= $name;?>" 
								id="field_<?= $name;?>" 
								value="<?= $value;?>"
								class=""/>
							<small><?= $description;?></small>
						</div>
					</div>
					<?php
				break;
				case 'checkbox':
					?>
					<div class="nrvbd-d-flex nrvbd-py-1">
						<label for="field_<?= $name;?>"
						       class="nrvbd-col-4 nrvbd-as-center"><?= $label;?></label>
						<div class="nrvbd-col-8 nrvbd-jc-flex-end">
							<input type="hidden" 
								name="<?= $name;?>" 
								value="false"/>
							<input type="checkbox" 
								name="<?= $name;?>" 
								id="field_<?= $name;?>" 
								value="true" 
								<?= $value == 'true' ? 'checked' : '';?>/>
						</div>
					</div>
					<?php
			}
			return ob_get_clean();
		}

		/**
		 * Register the admin menu
		 * @method register_menu
		 * @return void
		 */
		public function register_menu()
		{	
			admin_menu::add_configuration_menu("options",
											   self::setting, 
											   __('Options', 'nrvbd'), 
											   array($this, 'interface'));
		}


		/**
		 * Register the actions in the WP loop
		 * @method register_actions
		 * @return void
		 */
		public function register_actions()
		{    
			add_action("admin_menu", [$this, "register_menu"], 150);	
			add_action("admin_post_nrvbd-save-options", [$this, "save"]);		
		}


        /**
         * Save
         * @method save
         * @return void
         */
        public function save()
        {   
            if(wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-save-options')){    
				$data = $_POST;
				unset($data['_wpnonce']);
				unset($data['_wp_http_referer']);
				unset($data['action']);
				unset($data['ID']);
				foreach($data as $key => $value){
					update_option($key, $value);
				}
                wp_safe_redirect($this->base_url . "&error=10201");
            }else{
                wp_safe_redirect($this->base_url . "&error=10403");
            }
        }

	}
}
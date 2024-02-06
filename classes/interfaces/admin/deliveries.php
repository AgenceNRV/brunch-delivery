<?php
/**
 * Deliveries interface
 *
 * @package  nrvbd/classes/interfaces/admin
 * @version  0.9.0
 * @since    0.9.0
 */

namespace nrvbd\interfaces\admin;

use nrvbd\admin_menu;
use nrvbd\media;
use nrvbd\helpers;

if(!class_exists('\nrvbd\interfaces\admin\deliveries')){
    class deliveries{

		const slug = "nrvbd-deliveries";
		
		/**
		 * @var array
		 */
		private $labels = array();

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
            $this->register_menu();
            $this->register_actions();
            $this->base_url = admin_url('admin.php') . "?page=" . self::slug;
            $this->action_url = admin_url('admin-post.php');
        }


        /**
         * Generate the main interface
         * @method interface
         * @return html
         */
        public function interface()
        {	
			$menu = admin_menu::get_configuration_menu('deliveries');		
            ?>
			<div class="nrvbd-wrap tbg-white wrap">
				<div class="nrvbd-admin-wrapper nrvbd-mt-3">
					<ul class="nrvbd-setting-menu">
						<?php
						$active_callable = "";
						$first = 0;
						foreach($menu as $key => $item){
							if($item['in_menu'] == false){
								if(isset($_GET['setting']) && $item["tag"] == $_GET["setting"]){
									$active_callable = $item["function"];
								}
								continue;
							}
							$first ++;
							$active = "";
							if(isset($_GET["setting"])){
								if($item["tag"] == $_GET["setting"]){
									$active = "active";
								}
							}else if($first == 1){
								$active = "active";
							}

							if($active != ""){
								$active_callable = $item["function"];
							}
							$date = '';
							if ( isset($_GET["date"]) && !empty($_GET["date"]) ) {
							    $date = '&date='.$_GET["date"];
                            }
							?>
							<li class="clickable <?= $active;?>">
								<a href="?page=<?= self::slug;?>&setting=<?= $item["tag"];?><?= $date ?>"><?= $item["title"];?></a>
							</li>
							<?php
						}
						?>
					</ul>
					<div class="nrvbd-setting-wrap">
						<?php
						if(isset($_GET['error'])){
							echo $this->interface_print_error_notice($_GET['error']);
						}
						if($active_callable != ""){
							call_user_func($active_callable);
						}
						?>
					</div>
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
            admin_menu::main(
                [$this, 'interface']
            );   
            admin_menu::add(__('List of deliveries', 'nrvbd'),
                            __('List of deliveries', 'nrvbd'),
                            'nrvbd_deliveries',
                            admin_menu::slug,
                            null,
                            null,
                            7);
        }


        /**
         * Register the actions in the WP loop
         * @method register_actions
         * @return void
         */
        public function register_actions()
        {    
            add_action("admin_post_nrvbd-save", [$this, "save"]);
            // add_action("admin_post_nrvbd-config-delete-button", [$this, "delete_button"]);
			// add_action("wp_ajax_nrvbd-new-config-button", [$this, "ajax_config_button"]);
            // add_action("wp_ajax_nopriv_nrvbd-new-config-button", [$this, "ajax_config_button"]);
			add_action("admin_init", [$this, "hide_notices"]);			
        }


		/**
		 * Show the error message
		 * @method interface_print_error_notice
		 * @param  string $code
		 * @return string
		 */
		public function interface_print_error_notice($code)
		{
			$message = nrvbd_error_message($code);
			ob_start();
			?>
			<p class="notice notice-<?= $message['type'];?> notice-nrvbd">
				<?= $message['message'];?>
			</p>
			<?php
			return ob_get_clean();
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
		 * Hide the notice for the menu
		 * @method hide_notices
		 * @return void
		 */
		public function hide_notices()
		{
			if(isset($_GET['page']) && isset($_GET['page']) == self::slug)
			{
				remove_all_actions('admin_notices');
			}
		}

    }
}

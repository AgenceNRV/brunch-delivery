<?php
/**
 * base interface
 *
 * @package  nrvbd/classes/interfaces/admin/customize_pdf
 * @version  0.9.0
 * @since    0.9.0
 */

namespace nrvbd\interfaces\admin\customize_pdf;

use nrvbd\admin_menu;
use nrvbd\helpers;

if(!class_exists('\nrvbd\interfaces\admin\customize_pdf\delivery_pdf')){
	class delivery_pdf{

		const slug = "nrvbd-custom-yith";
		const setting = "delivery-pdf";

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
			<h1><?= __('Customize Delivery pdf','nrvbd');?></h1>
			<p class="notice notice-nrvbd">
				<?= __('This interface allows you to customize the delivery pdf product informations. Choose the product category, then click on the edit button to customize.','nrvbd');?>
			</p>
			<?php
			if(!isset($_GET['edit'])){
				echo $this->interface_list_block();
			}else{
				echo $this->interface_edit();
			}
		}

		public function interface_edit()
		{
			ob_start();
			$addons = nrvbd_yith_get_block_addon_by_category($_GET['edit'] ?? -1);			
			if(empty($addons)){
				return $this->interface_edit_not_found();
			}
			$resort_pdf = new \nrvbd\entities\yith_addon_resort_pdf($_GET['edit']);
			$addons = nrvbd_yith_resort_addons_object($addons, $resort_pdf->sort);
			?>
			<form class="nrvbd-form nrvbd-col-4" 
				  action="<?= $this->action_url ;?>" 
				  method="POST">
				<?php 
				wp_nonce_field('nrvbd-save-customize-delivery-pdf');
				?>
				<input type="hidden" name="ID" value="<?= $resort_pdf->ID;?>"/>
				<input type="hidden" name="action" value="nrvbd-save-customize-delivery-pdf"/>
				
				<div id="nrvbd-sortable" class="nrvbd-mt-2">
					<?php
					
					foreach($addons as $addon){
						$settings = maybe_unserialize($addon->settings ?? "a:0:{}");
						$options = maybe_unserialize($addon->options ?? "a:0:{}");
						$row_uid = base64_encode($settings['title']);
						?>
						<div class="nrvbd-sortable-item">
							<details>
								<summary class="nrvbd-d-flex nrvbd-jc-space-between">
									<div class="nrvbd-col-12" style="cursor: zoom-in;">
										<span class="notopened dashicons dashicons-arrow-right-alt2"></span>
										<span class="opened dashicons dashicons-arrow-down-alt2"></span>
										<?= $settings['title'];?>
										<small>(<?= __('Click here to unfold');?>)</small>
									</div>
									<span class="dashicons dashicons-menu-alt3"></span>
								</summary>
								<div class="">
									<input type="hidden" name="nrvbd-yith-sort[]" value="<?= $addon->id;?>"/>
									<?php
									if(is_array($options)){
										$labels = $options['label'] ?? array();
										foreach($labels as $label){
											$uid = helpers::unique_id();
											?>
											<div class="nrvbd-d-flex nrvbd-flex-wrap nrvbd-jc-space-between nrvbd-mt-1">
												<label for="<?= $uid;?>" class="nrvbd-col-12 nrvbd-fw-4"><?= $label;?></label>
												<div class="nrvbd-col-12 nrvbd-d-flex nrvbd-ai-center">
													<label for="<?= $uid;?>" class="nrvbd-col-3">
														<?= __('Text in PDF :', 'nrvbd');?>
													</label>
													<input type="text" 
													       id="<?= $uid;?>"
													       class="nrvbd-col-9"
														   name="nrvbd-yith-addon[<?= $row_uid;?>][<?= base64_encode(trim($label));?>]" 
														   value="<?= nrvbd_yith_get_addon_pdf_text($row_uid, base64_encode(trim($label)), $resort_pdf->data);?>"
														   required/>
												</div>
													   
											</div>
											<?php
										}
									}
									?>
								</div>
							</details>
						</div>
						<?php
					}
					?>
				</div>
				<div class="nrvbd-d-flex nrvbd-jc-space-between nrvbd-mt-2">
					<a href="<?= $this->base_url;?>" 
					   class="nrvbd-button nrvbd-button-primary" 
					   style="text-decoration: none;">
						<?= __('Back','nrvbd');?>
					</a>
					<button class="button button-primary"><?= __('Save', 'nrvbd');?></button>
				</div>					
			</form>

			<script>
				jQuery(document).ready(function($) {
					$('#nrvbd-sortable').sortable({
						revert: true
					});
				});
			</script>
			<?php
			return ob_get_clean();
		}

		public function interface_list_block()
		{
			ob_start();
			$args = array(
				'per_page' => $_GET['per_pages'] ?? 20,
				'page' => $_GET['paged'] ?? 1
			);
			$data = nrvbd_yith_get_categories($args);
			$info = nrvbd_yith_get_categories_info($args);
			?>
			<div class="nrvbd-d-flex nrvbd-flex-wrap nrvbd-col-12">
				<?php
				foreach($data as $item){
					$term = get_term($item);
					?>
					<div class="nrvbd-col-4 nrvbd-p-1">
						<div class="nrvbd-d-flex nrvbd-jc-space-between nrvbd-p-1 nrvbd-ai-center nrvbd-bd-solid nrvbd-bd-solid nrvbd-bd-1 nrvbd-bd-light">
							<h3><?= $term->name;?></h3>
							<a class="nrvbd-button nrvbd-button-primary" 
							   style="text-decoration: none;"
							   title="<?= __('Edit','nrvbd');?>"
							   href="<?= $this->base_url;?>&edit=<?= $item;?>">
								<span class="dashicons dashicons-edit"></span>
							</a>
						</div>
					</div>
					<?php
				}
				?>
			</div>
			<?php
			echo $this->pagination($info);
			return ob_get_clean();
		}	

		
		public function pagination(array $info = array())
		{
			$per_page_options = array(50, 100, 200);
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
						<span><?= __('Page n°','nrvbd');?></span>
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


		public function interface_edit_not_found()
		{
			ob_start();
			?>
			<p class="notice notice-warning notice-nrvbd">
				<?= __("The addons for the given category couldn't be found.",'nrvbd');?>
			</p>
			<a href="<?= $this->base_url;?>" 
			   class="nrvbd-button nrvbd-button-primary" 
			   style="text-decoration: none;">
				<?= __('Back','nrvbd');?>
			</a>
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
			admin_menu::add_configuration_menu("customize-pdf",
											   self::setting, 
											   __('Delivery PDF : Sort yith addons', 'nrvbd'), 
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
			add_action("admin_post_nrvbd-save-customize-delivery-pdf", [$this, "save"]);		
		}


        /**
         * Save
         * @method save
         * @return void
         */
        public function save()
        {   
            if(wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-save-customize-delivery-pdf')){ 
				$sort = $_POST['nrvbd-yith-sort'] ?? array();
				$addons = $_POST['nrvbd-yith-addon'] ?? array();
				$resort_pdf = new \nrvbd\entities\yith_addon_resort_pdf($_POST['ID']);
				$resort_pdf->sort = $sort;
				$resort_pdf->data = $addons;
				$resort_pdf->save(true);
                wp_safe_redirect($this->base_url . "&error=10201");
            }else{
                wp_safe_redirect($this->base_url . "&error=10403");
            }
        }

	}
}
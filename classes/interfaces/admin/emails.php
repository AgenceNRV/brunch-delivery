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

if(!class_exists('\nrvbd\interfaces\admin\emails')){
    class emails{
		const slug = "nrvbd-emails";
	

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
            $this->base_url = admin_url('admin.php') . "?page=" . self::slug ;
            $this->action_url = admin_url('admin-post.php');		
        }



		public function interface()
		{
			?>
			<div class="nrvbd-wrap tbg-white wrap">
				<div class="nrvbd-admin-wrapper nrvbd-mt-3">
					<div class="nrvbd-setting-wrap">
						<h1><?= __('Emails management','nrvbd');?></h1>
					<?php
					$this->interface_list();
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
			$args = array('delivery_date' => $_GET['date'] ?? null,
						  'page' => $_GET['paged'] ?? 1,
						  'per_pages' => $_GET['per_pages'] ?? 20);
			$page_info = nrvbd_get_delivery_mails_info($args);
			$page_data = nrvbd_get_delivery_mails($args, true);
			// echo $this->filters($page_info);
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
							<td colspan="6"><?= __('No emails found.','nrvbd');?></td>
						</tr>
						<?php
					}else{
						foreach($page_data as $email){
							$driver = $email->get_driver();
							?>
							<tr>
								<td>#<?= $email->ID; ?></td>
								<td><?= $email->delivery_date; ?></td>
								<td>
									<?php
									if($driver->db_exists()){
										$edit_url = admin_url('admin.php') . "?page=nrvbd-drivers&setting=edit&id=" . $driver->ID;
										echo "<a href='{$edit_url}'>#{$driver->ID} {$driver->firstname} {$driver->lastname}</a>";
									}
									?>
								</td>
								<td>
									<?= $email->driver_email; ?>
								</td>
								<td>
									<?php
									if($email->date_sent != ''){
										echo date('d/m/Y H:i:s', strtotime($email->date_sent));
									}else{
										echo '<span class="dashicons dashicons-warning" style="color:red;"></span>';
									}
									?>
								</td>
								<td>
									<?php
									$resend_href = wp_nonce_url( add_query_arg( array( 
										'action' => 'nrvbd-resend-email', 
										'id' => $email->ID
									), 'admin-post.php'), 'nrvbd-resend-email');
									?>
									<a class="nrvbd-must-confirm nrvbd-button-warning nrvbd-ml-1"
										confirm-href="<?= $resend_href;?>"
										confirm-message="<?= __("You're about to send back this email. Do you want to continue ?", "nrvbd");?>" 
										style="cursor:pointer">
									    <span class="dashicons dashicons-email-alt2 nrvbd-mr-1"></span>
										<?= __('Resend', 'nrvbd'); ?>
									</a>
									<?php
									if($email->get_delivery_pdf() 
										&& $email->get_delivery_pdf()->db_exists() 
										&& $email->get_delivery_pdf()->get_pdf()){
										?>
										<a class="nrvbd-button-warning-outline nrvbd-ml-1"
											href="<?= $email->get_delivery_pdf()->get_pdf_url();?>"
											download="<?= $email->get_delivery_pdf()->get_pdf_name();?>"
											style="cursor:pointer">
											<span class="dashicons dashicons-download nrvbd-mr-1"></span>
											<?= __('Download the pdf', 'nrvbd'); ?>
										</a>
										<?php
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
			<?php
			echo $this->pagination($page_info);
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
				<th><?= __('ID', 'nrvbd'); ?></th>
				<th><?= __('Delivery date', 'nrvbd'); ?></th>
				<th><?= __('Driver', 'nrvbd'); ?></th>
				<th><?= __('Email', 'nrvbd'); ?></th>
				<th><?= __('Date sent', 'nrvbd'); ?></th>
				<th><?= __('Options', 'nrvbd'); ?></th>
			</tr>
			<?php
			return ob_get_clean();
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
					<input type="hidden" name="page" value="<?= self::slug;?>">
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
		

        /**
         * Register the admin menu
         * @method register_menu
         * @return void
         */
        public function register_menu()
        {
			admin_menu::add(__('Manage emails', 'nrvbd'), 
							__('Manage emails', 'nrvbd'),
							'nrvbd_resend_email',
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
			add_action('admin_post_nrvbd-resend-email', [$this, 'resend_email']);
        }



        /**
         * Save
         * @method save
         * @return void
         */
        public function resend_email()
        {   
            if(wp_verify_nonce($_REQUEST['_wpnonce'], 'nrvbd-resend-email')){    
				$email = new \nrvbd\entities\email($_REQUEST['id']);
				nrvbd_send_driver_delivery_resend_mail($email);
                wp_safe_redirect($this->base_url . "&error=08201");
            }else{
                wp_safe_redirect($this->base_url . "&error=10403");
            }
        }

    }
}
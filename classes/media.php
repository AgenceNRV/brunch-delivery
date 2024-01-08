<?php

namespace nrvbd;

if(!class_exists("media")){
	class media
	{

		/**
		 * Is multiple file upload ?
		 * @var boolean
		 */
		protected $multiple = false;

		/**
		 * Files from multiple upload
		 * @var array
		 */
		protected $files = array();

		/**
		 * URL of the media
		 * @var string
		 */
		protected $url;

		/**
		 * ID of the media
		 * @var integer
		 */
		protected $ID;

		
		protected $instance_id;

		/**
		 * Input name
		 * @var string
		 */
		protected $input_name = "nrv-image";

		/**
		 * Store the upload button text
		 * @var string
		 */
		private $upload_button_label;

		/**
		 * Store the listing label
		 * @var string
		 */
		private $listing_label;

		/**
		 * Store the "select this media" label
		 * @var string
		 */
		private $select_media_label;

		/**
		 * Store the "use this" label
		 * @var string
		 */
		private $use_this_media_label;

		/**
		 * Store the "no file" label
		 * @var string
		 */
		private $no_files_label;

		/**
		 * The media class constructor
		 * @method __construct
		 * @param  int|array media
		 * @param  bool $multiple
		 */
		public function __construct($media = null, $multiple = false)
		{
			$this->instance_id = helpers::unique_id();
			if($multiple == true && is_array($media)){
				$this->multiple = $multiple;
				$this->files = $media;
			}else{
				if(is_array($media)){
					$this->ID = $media["ID"] ?? null;
					if (isset($media["url"])) {
						$this->url = $media["url"];
					} else {
						$this->url = wp_get_attachment_image_url($media["ID"], 'thumbnail');
					}
				}elseif(is_int(intval($media))){
					$this->ID = $media;
					$this->url = wp_get_attachment_image_url($media, 'thumbnail');
				}
			}

			$this->setLabels();
		}


		/**
		 * The current media ID
		 * @method ID
		 */
		public function ID()
		{
			return $this->ID;
		}


		/**
		 * Return the media thumbnail url
		 * @method url
		 * @return string
		 */
		public function url()
		{
			return $this->url;
		}


		/**
		 * Edit or Return the input name
		 * @method input_name
		 * @param  string    $value
		 * @return string|instance
		 */
		public function input_name($value = null)
		{
			if ($value != null) {
				$this->input_name = $value;
			}
			return $this->input_name;
		}
	

		/**
		 * Define the labels
		 * @method setLabels
		 * @param array $args accepted :[upload_button_label, listing_label, select_media_label, use_this_media_label,no_files_label]
		 * @return void
		 */
		public function setLabels($args = array())
		{
			$default = array(
				"upload_button_label" => __("Upload a media", 'nrvbd'),
				"listing_label" => __('List of media', 'nrvbd'),
				"select_media_label" => __("Select a media to upload", 'nrvbd'),
				"use_this_media_label" => __('Use this media', 'nrvbd'),
				"no_files_label" => __('No media attached', 'nrvbd')
			);

			$args = \nrvbd\helpers::set_default_values($default, $args);
			foreach($args as $prop => $text){
				$this->$prop = $text;
			}
		}
		

		/**
		 * Return the html input
		 * @method html
		 * @param  array  $args [description]
		 * @return string
		 */
		public function html(array $args = array())
		{
			$unique_id = md5(microtime(true));
			if(!empty($args)){
				if (isset($args["name"])) {
					$this->input_name($args["name"]);
				}
				if(isset($args['id'])){
					$this->instance_id = $args['id'];
				}
				if(isset($args["default_image"]) && $this->url == null){
					$this->url = $args["default_image"];
				}
			}
			?>
			<div>
				<?php
				if(!$this->multiple){
				?>
				<div class='nrv-image-preview-wrapper image-preview-wrapper'
					 id="preview-<?= $unique_id;?>">
					<img class='nrv-image-preview image-preview' 
					     src='<?= esc_html($this->url()); ?>' 
						 width='100' 
						 height='100' 
						 style='max-height: 100px; width: 100px;'>
				</div>
				<?php
				}
				?>
				<input class="nrvbd-upload-image-button button" 
				       id="<?= $this->instance_id;?>"
					   type="button" 
					   value="<?= $this->upload_button_label;?>" 
						<?php
						if($this->multiple){
							?>
							data-multiple="<?= $this->multiple;?>"
							<?php
						}
						if(isset($args['allowed-types'])){
							?>
							data-allowed-types="<?= $args['allowed-types'];?>"
							<?php
						}
						?>
						data-target-id="<?= $unique_id;?>"
					 />
				<?php
				if($this->multiple !== true){
					?>
					<input type='hidden' 
					       name='<?= $this->input_name(); ?>[ID]' 
						   class='nrv-image-attachment-id' 
						   value='<?= $this->ID(); ?>'
						   id="input-id-<?= $unique_id;?>">
					<input type="hidden" 
						   class="nrv-image-attachment-url" 
						   name='<?= $this->input_name(); ?>[url]' 
						   value="<?= esc_html($this->url()); ?>"
						   id="input-url-<?= $unique_id;?>" />
					<?php
				}
				if($this->multiple){
					?>
					<div class='nrv-media multiple preview' 
						id="preview-<?= $unique_id;?>">
						<h3><?= $this->listing_label;?></h3>
						<?php
						if(!empty($this->files)){
							foreach($this->files as $id => $file){
								?>
								<div class='media-list-element tool-py-5 tool-pr-1'>
									<input type="hidden"
										   name="<?= $this->input_name(); ?>[<?= $id;?>][url]"
										   value="<?= $file['url'];?>">									
									<input type="hidden"
										   name="<?= $this->input_name(); ?>[<?= $id;?>][name]"
									       value="<?= $file['name'];?>">
									<a href="<?= $file['url'];?>">
										<?= $file['name'];?>
									</a>
									<button class="delete" style="display:none">X</buttton>
								</div>
								<?php
							}
						}else{
							?>
							<p class='notice notice-nrv-tool no-files-attached'>
								<?= $this->no_files_label;?>
							</p>
							<?php
						}
						?>
					</div>
					<?php
					}
				?>
			</div>
			<?php				
		}


		/**
		 * Adding the script
		 * @method script
		 * @return html
		 */
		public function script()
		{
			?>
			<script type='text/javascript'>
				jQuery(document).ready(function($) {
					var mediaUploader = "";
					$(document).on('click', '.nrvbd-upload-image-button', function(e) {
						event.preventDefault();
						var unique_id = $(this).data('target-id');
						var isMultiple = $(this).data('multiple') == '1';
						var multiple = false;
						if(isMultiple){
							multiple = "add";
						}
						var allowedTypes = $(this).data('allowed-types') || '';

						var wp_media_post_id = wp.media.model.settings.post.id;
						var set_to_post_id = '';
						var container = $(this).parent();

						mediaUploader = wp.media.frames.file_frame = wp.media({
							title: '<?= $this->select_media_label;?>',
							button: {
								text: '<?= $this->use_this_media_label;?>',
							},
							multiple: multiple,
							library: {
								type: allowedTypes
							}
						});									
						
						mediaUploader.on('open', function(){
							if(set_to_post_id){
								mediaUploader.uploader.uploader.param('post_id', parseInt(set_to_post_id));
								var selection = mediaUploader.state().get('selection');
								selection.add(wp.media.attachment(set_to_post_id));
							}
						});	

						mediaUploader.on('select', function(){
							var attachments = mediaUploader.state().get('selection').map( 
								function(attachment){
									attachment.toJSON();
									return attachment;
								}
							);

							
							if(isMultiple){
								var input = $('#input-media-'+unique_id).val(JSON.stringify(attachments));
								if(attachments.length > 0){
									$(container).find('.no-files-attached').hide();
								}
								attachments.forEach(function(a){
									let input_name = "<?= $this->input_name(); ?>[" + a.id +"]";
									let html = "<div class='media-list-element tool-pr-1'>"
													+ "<input type='hidden' name='" +input_name+ "[url]' value='"+ a.changed.url +"'>"
													+ "<input type='hidden' name='" +input_name+ "[name]' value='"+ a.attributes.filename +"'>"
													+ "<a href='"+a.changed.url+"'>"+ a.attributes.filename +"</a>" 
													+ "<button class='delete' style='display:none'>X</buttton>"
											   +"</div>";
									$('#preview-'+unique_id).append(html);
								});
							}else{
								var attachment = attachments[0];
								$(container).find('.nrv-image-preview').attr('src', attachment.changed.url).css('width', 'auto');
								$(container).find('.nrv-image-attachment-id').val(attachment.id);
								$(container).find('.nrv-image-attachment-url').val(attachment.changed.url);
							}
							
							wp.media.model.settings.post.id = wp_media_post_id;
						});

						mediaUploader.open();
					});

					$('a.add_media').on('click', function() {
						wp.media.model.settings.post.id = wp_media_post_id;
					});
				});
			</script>
			<?php
		}
	}
}

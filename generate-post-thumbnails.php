<?php /*
  Plugin Name:  Generate Post Thumbnails
  Plugin URI:   http://wordpress.shaldybina.com/plugins/generate-post-thumbnails/
  Description:  Tool for mass generation of Wordpress posts thumbnails using the post images.
  Version:      0.4.1
  Author:       Maria Shaldybina
  Author URI:   http://shaldybina.com/
*/
/*  Copyright 2010  Maria I Shaldybina

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
*/
class GeneratePostThumbnails {

	function GeneratePostThumbnails() { // initialization
		load_plugin_textdomain( 'generate-post-thumbnails', false, basename( dirname( __FILE__ ) ) . '/locale' );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'wp_ajax_generate_post_thumbnails', array( $this, 'ajax_process_post' ) );
	}

	function add_admin_menu() { // admin menu item
		$page = add_management_page( __( 'Generate Thumbnails', 'generate-post-thumbnails' ), __( 'Generate Thumbnails', 'generate-post-thumbnails' ), 'administrator', 'generate-post-thumbnails', array( $this, 'admin_interface' ) );
		add_action( 'admin_print_scripts-' . $page, array( $this, 'admin_scripts' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'admin_styles' ) );
	}

	function admin_scripts() {
		wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( 'jquery-ui/ui.progressbar.min.js', __FILE__), array('jquery-ui-core'), '1.7.2' );
	}

	function admin_styles() {
		wp_enqueue_style( 'jquery-ui-generatethumbs', plugins_url( 'jquery-ui/smoothness/jquery-ui-1.7.2.custom.css', __FILE__), array(), '1.7.2' );
	}

	function admin_interface() { // admin page
		$success_message = __( 'Thumbnails generation process is finished. Processed posts: %d', 'generate-post-thumbnails' );
?>
<div class="wrap">
<div class="icon32" id="icon-tools"><br/></div>
	<h2><?php _e( 'Thumbnails Generation', 'generate-post-thumbnails' ); ?></h2>
	<div class="metabox-holder">
	<?php if ( !current_theme_supports( 'post-thumbnails' ) ) { /* theme should support post-thumbnails*/ ?>
	<div class="error"><p><strong><?php _e( 'Plugin warning', 'generate-post-thumbnails' ); ?>:</strong> <?php _e( 'Your current theme does not support thumbnails. You need to adjust your theme in order to use this plugin. Please read plugin page for more information. Settings will appear on this page once you enable thumbnails in your theme.', 'generate-post-thumbnails' ); ?></p></div>
	<?php
	} // endif theme supports thumbnails
	else {
		global $wpdb;
		$posts = array();
		$posts = $wpdb->get_results( "select ID from $wpdb->posts where post_type = 'post'" );
		$posts_count = count( $posts );
		$posts_ids = array();
		foreach ( $posts as $post ) {
			$posts_ids[] = $post->ID;
		}
		$message = '';

		if ( isset( $_POST['generate-thumbnails-submit'] ) ) { // if javascript is not supported and form was submitted
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'No access' ) );
			}
			check_admin_referer( 'generate-thumbnails' );
			set_time_limit( 60 );
			$overwrite = ( isset( $_POST['overwrite'] ) ) ? 'overwrite' : 'skip'; // overwrite or skip current images
			foreach ( $posts_ids as $post_id ) {
				$this->process_images( $post_id, $overwrite, $_POST['imagenumber'] );
			}
			$message = sprintf( $success_message, count( $posts ) );
		}
		?>
		<div id="message" class="updated" <?php if ( empty($message) ) : ?>style="display: none;"<?php endif; ?>><?php echo $message; ?></div>
		<form id="generate_thumbs_form" action="?page=generate-post-thumbnails" method="POST">
			<input type="hidden" name="generate-thumbnails-submit" value="1" />
			<?php wp_nonce_field( 'generate-thumbnails' ); ?>
			<div class="postbox">
				<h3><?php _e( 'Thumbnails generation settings', 'generate-post-thumbnails' ); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Overwrite existing thumbnails', 'generate-post-thumbnails' ); ?>:</th>
						<td>
							<input type="checkbox" name="overwrite" id="overwrite" value="1" />
							<label for="rewrite"><?php _e( 'Check this if you want existing post thumbnails to be overwritten with generated thumbnails.', 'generate-post-thumbnails' ); ?></label><br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Image number in the post', 'generate-post-thumbnails' ); ?>:</th>
						<td>
							<input type="text" name="imagenumber" id="imagenumber" value="1" size="2"/>
							<label for="imagenumber"><?php _e( 'Sequence number of the image in the post to be stored as a post thumbnail. Ex. 1 for the first post image, 2 for the second, etc. If there is no image at the given number, existing thumbnail will be removed.', 'generate-post-thumbnails' ); ?></label><br />
						</td>
					</tr>
				</table>
			</div>
			<input id="generate-thumbnail-button" name="Submit" value="<?php _e( 'Generate thumbnails', 'generate-post-thumbnails' ); ?>" type="submit" class="button"/>
			<noscript><p><?php _e( 'Javascript is disabled, you will be redirected. Please do not close your page until the process is finished.', 'generate-post-thumbnails' ); ?></p></noscript>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
						$("#generate_thumbs_form").submit(function(event) {
							event.preventDefault();
							$("#generate-thumbnail-button").attr('disabled', true);
							$("#message").html("<?php _e( 'Please wait until the process is finished. This process may take up to several minutes, depending on the number of posts and server capacity.', 'generate-post-thumbnails' ); ?>");
							$("#message").show();
							$("#gt_progressbar").progressbar({ value: 0 });
							$("#gt_progressbar_percent").html("0%");
							var gt_count       = 1;
							var gt_percent     = 0;
							var gt_posts       = [<?php echo implode(", ", $posts_ids); ?>];
							var gt_total       = gt_posts.length;
							var gt_overwrite   = $("#overwrite").is(':checked') ? 'overwrite' : 'skip';
							var gt_imagenumber = $("#imagenumber").val();

							function GenerateThumbnails(post_id) {
								$.post(ajaxurl, {action: "generate_post_thumbnails", post_id: post_id, overwrite: gt_overwrite, imagenumber: gt_imagenumber}, function(response){
										gt_percent = (gt_total > 0) ? (gt_count / gt_total) * 100 : 0;
										$("#gt_progressbar").progressbar("value", gt_percent);
										$("#gt_progressbar_percent").html(Math.round(gt_percent) + "%");
										gt_count++;
										if (gt_posts.length) {
											GenerateThumbnails(gt_posts.shift());
										} else {
											$("#message").html("<?php echo js_escape(sprintf($success_message, $posts_count)); ?>");
											$("#generate-thumbnail-button").attr('disabled', false);
										}
									});
							}
							GenerateThumbnails(gt_posts.shift());
							});
					});
			</script>
		</form>
		<div id="gt_progressbar" style="position:relative;width:80%;margin-top:20px;">
			<label id="gt_progressbar_percent" style="position:absolute;left:50%;top:5px;margin-left:-20px;"></label>
		</div>
		<?php
			} // endif theme supports thumbnails
		?>
	</div>
</div>
<?php
	} // endfunction admin_interface

	function process_images( $post_id, $overwrite = 'skip', $imagenumber = 1 ) { // generating thumbnail for a single post
		$post = get_post( $post_id );
		if ( !$post ) // if post was not found by id
			return false;
		if ( $overwrite == 'skip' && has_post_thumbnail( $post_id ) ) // check if skip existing thumbnail
			return false;
		$wud			= wp_upload_dir();
		$image			= '';
		$imagenumber	= preg_replace( '/[^\d]/', '', $imagenumber );
		preg_match_all( '|<img.*?src=[\'"](' . $wud['baseurl'] . '.*?)[\'"].*?>|i', $post->post_content, $matches ); // search for uploaded images in the post
		if ( empty( $imagenumber ) || $imagenumber == 0 )
			return false;
		if ( isset( $matches ) && isset( $matches[1][$imagenumber-1] ) && strlen( trim( $matches[1][$imagenumber-1] ) ) > 0 )
			$image = $matches[1][$imagenumber-1];
		else { // if image was not found in post
			delete_post_meta( $post->ID, '_thumbnail_id' );
			return false;
		}
		$parts = pathinfo( $image );
		$attachments = array();
		$attachments = get_posts( 'post_type=attachment&numberposts=-1&post_mime_type=image&post_status=null&post_parent=' . $post->ID );
		$found_attachment = null;
		foreach ( $attachments as $attachment ) {
			$metadata = array();
			$metadata = get_post_meta( $attachment->ID, '_wp_attachment_metadata' );
			foreach ( $metadata as $metaitem ) {
				$original_image = $wud['baseurl'] . '/' . $metaitem['file'];
				if ( $original_image == $image ) { //check if original image was used in post
					$found_attachment = $attachment->ID;
					break 2;
				}
				elseif ( isset($metaitem['sizes']) && count( $metaitem['sizes'] ) ) { //search for used thumbnail size
					foreach( $metaitem['sizes'] as $image_size ) {
						if ( $image_size['file'] == $parts['basename'] ) {
							$found_attachment = $attachment->ID;
							break 3;
						}
					}
				}
			}
		}
		if ( isset( $found_attachment ) && get_post( $found_attachment ) ) {
			$thumbnail_html = wp_get_attachment_image( $found_attachment, 'thumbnail' );
			if ( !empty( $thumbnail_html ) ) {
				update_post_meta( $post->ID, '_thumbnail_id', $found_attachment );
				return true;
			}
		}
		return false;
	} // endfunction process_images

	function ajax_process_post() { // dealing with ajax requests
		if ( !current_user_can( 'manage_options' ) )
			die('0');
		if ( $this->process_images( $_POST['post_id'], $_POST['overwrite'], $_POST['imagenumber'] ) )
			die('1');
		else
			die('-1');
	}
} // endclass

add_action( 'init', 'GeneratePostThumbnails' );

function GeneratePostThumbnails() {
	global $GenearetPostThumbnails;
	$GeneratePostThumbnails = new GeneratePostThumbnails();
}
?>

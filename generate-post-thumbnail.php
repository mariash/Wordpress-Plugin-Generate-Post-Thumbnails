<?php /*
  Plugin Name:  Generate Post Thumbnail
  Plugin URI:   http://wordpress.shaldybina.com/plugins/generate-post-thumbnail/
  Description:  Tool for mass generating wordpress posts thumbnails from the post image.
  Version:      1.0
  Author:       M.Shaldybina
  Author URI:   http://shaldybina.com/
*/
class GeneratePostThumbnail
{
    function GeneratePostThumbnail() // initialization
    {
        load_plugin_textdomain('generate-post-thumbnail', false, '/generate-post-thumbnail/localization');
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_generate_post_thumbnail', array($this, 'ajax_process_post'));
    }

    function add_admin_menu() // admin menu item
    {
        $page = add_management_page('Generate Thumbnails', 'Generate Thumbnails', 'administrator', 'generate-post-thumbnail', array($this, 'admin_interface'));
        add_action('admin_print_scripts-' . $page, array($this, 'admin_scripts'));
        add_action('admin_print_styles-' . $page, array($this, 'admin_styles'));
    }

    function admin_scripts()
    {
        wp_enqueue_script('jquery-ui-progressbar', plugins_url( 'jquery-ui/ui.progressbar.min.js', __FILE__), array('jquery-ui-core'), '1.7.2' );
    }

    function admin_styles()
    {
        wp_enqueue_style('jquery-ui-generatethumbs', plugins_url( 'jquery-ui/smoothness/jquery-ui-1.7.2.custom.css', __FILE__), array(), '1.7.2' );
    }

    function admin_interface() // admin page
    {
        $success_message = __('Thumbnails generation process is finished. Processed posts: %d', 'generate-post-thumbnail');
?>
<div class="wrap">
  <div class="icon32" id="icon-tools"><br/></div>
  <h2><?php _e('Thumbnails Generation'); ?></h2>
  <div class="metabox-holder">
       <?php
       if (!current_theme_supports('post-thumbnails')) { // theme should support post-thumbnails
       ?>
           <div class="error"><p><strong><?php _e('Plugin warning', 'generate-post-thumbnail');?>:</strong> <?php _e('Your current theme does not support thumbnails. You need to adjust your theme in order to use this plugin. Please read plugin page for more information. Settings will appear on this page once you enable thumbnails in your theme.', 'generate-post-thumbnail');?></p></div>
           <?php
       }
       else {
           global $wpdb;
           $posts = $wpdb->get_results("select ID from $wpdb->posts where post_type = 'post'");
           $posts_count = count($posts);
           if ($posts_count) {
               foreach ($posts as $post) {
                   $posts_ids[] = $post->ID;
               }
           }
           $message = "";
           
           if (isset($_POST['generate-thumbnails-submit'])) { // if javascript is not supported and form was submitted
               if (!current_user_can('manage_options')) {
                   wp_die(__('No access'));
               }

               check_admin_referer('generate-thumbnails');
               $overwrite = (isset($_POST['overwrite'])) ? true: false;
               if ($posts_ids) {
                   foreach ($posts_ids as $post_id) {
                       $this->process_images($post_id, $overwrite, $_POST['imagenumber']);
                   }
               }
               $message = sprintf($success_message, count($posts));
           }
       ?>
     <div id="message" class="updated" <?php if (empty($message)) : ?>style="display: none;"<?php endif; ?>><?php echo $message; ?></div>
    <form id="generate_thumbs_form" action="?page=generate-post-thumbnail" method="POST">
      <input type="hidden" name="generate-thumbnails-submit" value="1" />
      <?php wp_nonce_field('generate-thumbnails') ?>
      <div class="postbox">
        <h3><?php _e('Thumbnails generation settings', 'generate-post-thumbnail');?></h3>
        <table class="form-table">
          <tr valign="top">
            <th scope="row"><?php _e('Overwrite existing thumbnails', 'generate-post-thumbnail'); ?>:</th>
            <td>
            <input type="checkbox" name="overwrite" id="overwrite" value="1" <?php if (false) { echo 'checked="checked"'; } ?>/>
           <label for="rewrite"><?php _e('Check this if you want existing post-thumbnails to be overwritten with generated thumbnails.', 'generate-post-thumbnail'); ?></label><br />
            </td>
          </tr>
          <tr valign="top">
            <th scope="row"><?php _e('Image number in the post', 'generate-post-thumbnail');?>:</th>
            <td>
              <input type="text" name="imagenumber" id="imagenumber" value="1" <?php if (false) { echo 'checked="checked"'; } ?> size="2"/>
              <label for="imagenumber"><?php _e('Sequence number of the image in the post to be stored as a post thumbnail. Ex. 1 for the first post image, 2 for the second, etc. If there is no image at the given number, existing thumbnail will be removed.', 'generate-post-thumbnail'); ?></label><br />
            </td>
          </tr>
        </table>
      </div>
      <input id="generate-thumbnail-button" name="Submit" value="<?php _e('Generate thumbnails', 'generate-post-thumbnail'); ?>" type="submit" class="button"/>
      <noscript><p><?php _e('Javascript is disabled, you will be redirected. Please do not close your page untill process is finished.', 'generate-post-thumbnail');?></p></noscript>
      <script type="text/javascript">
        jQuery(document).ready(function($) {
            $("#generate_thumbs_form").submit(function(event){
                event.preventDefault();
                $("#generate-thumbnail-button").attr('disabled', true);
                $("#message").html("<?php _e('Please wait untill process is finished. This process may take up to several minutes, depending on the number of posts and server capacity.', 'generate-post-thumbnail');?>");
                $("#message").show();
                $("#gt_progressbar").progressbar({ value: 0 });
                $("#gt_progressbar_percent").html("0%");
                var gt_count = 1;
                var gt_percent = 0;
                var gt_posts = [<?php echo implode(", ", $posts_ids); ?>];
                var gt_total = gt_posts.length;
                var gt_overwrite = $('#overwrite').is(':checked') ? 1 : 0;
                var gt_imagenumber = $('#imagenumber').val();

                function GenerateThumbnails(post_id) {
                    $.post(ajaxurl, {action: "generate_post_thumbnail", post_id: post_id, overwrite: gt_overwrite, imagenumber: gt_imagenumber}, function(response){
                        gt_percent = (gt_count / gt_total) * 100;
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

    function process_images($post_id, $overwrite = false, $imagenumber = 1) // generating thumbnail for a single post
    {
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        if (!$overwrite && has_post_thumbnail($post_id)) {
            return false;
        }
        set_time_limit(60);
        $wud = wp_upload_dir();
        $image = '';
        $imagenumber = preg_replace('/[^\d]/', '', $imagenumber);
        preg_match_all('|<img.*?src=[\'"](' . $wud['baseurl'] . '.*?)[\'"].*?>|i', $post->post_content, $matches); // search for uploaded images in the post
        if (empty($imagenumber) || $imagenumber == 0) {
            return false;
        }
        if (isset($matches) and isset($matches[1][$imagenumber-1])) {
            $image = $matches[1][$imagenumber-1];
        }
        else  {
            // no image for thumbnail
            delete_post_meta($post->ID, '_thumbnail_id');
        }

        if (strlen(trim($image)) > 0) { // if image was found
            $parts = pathinfo($image);
            $attachments = get_posts('post_type=attachment&numberposts=-1&post_mime_type=image&post_status=null&post_parent=' . $post->ID);
            $found_attachment = null;
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    $metadata = get_post_meta($attachment->ID, '_wp_attachment_metadata');
                    if ($metadata) {
                        foreach ($metadata as $metaitem) {
                            $original_image = $wud['baseurl'] . '/' . $metaitem['file'];
                            if ($original_image == $image ) { //check if original image was used in post
                                $found_attachment = $attachment->ID;
                                break 2;
                            }
                            else { //search for used thumbnail size
                                if (count($metaitem['sizes'])) {
                                    foreach($metaitem['sizes'] as $image_size) {
                                        if ($image_size['file'] == $parts['basename']) {
                                            $found_attachment = $attachment->ID;
                                            break 3;                                        
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (isset($found_attachment) && get_post($found_attachment)) {
                    $thumbnail_html = wp_get_attachment_image($found_attachment, 'thumbnail');
                    if (!empty($thumbnail_html)) {
                        update_post_meta($post->ID, '_thumbnail_id', $found_attachment);
                        return true;
                    }
                }
            } // endif post has attachments
        } //endif post has image
        return false;
    } // endfunction process_images

    function ajax_process_post()
    {
        if (!current_user_can('manage_options'))
            die('-1');
        if ($this->process_images($_POST['post_id'], $_POST['overwrite'], $_POST['imagenumber'])) {
            die('1');
        } else {
            die('-1');
        }
    }
} // endclass


add_action('init', 'GeneratePostThumbnail');

function GeneratePostThumbnail() {
    global $GenearetPostThumbnail;
    $GeneratePostThumbnail = new GeneratePostThumbnail();
}
?>

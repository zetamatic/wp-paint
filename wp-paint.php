<?php
/**
 * Plugin Name:     WP Paint - WordPress Image Editor
 * Plugin URI:      https://zetamatic.com/wp-paint
 * Description:     WP Paint is a browser based HTML5 Image Editor for WordPress media images. It has an intuitive interface resembling most common Desktop based Photo Editors with an extensive array of Image Editing, Photo Manipulation and Photo Editing Features.
 * Author:          ZetaMatic
 * Author URI:      https://zetamatic.com
 * Text Domain:     wp-paint
 * Version:         0.4.6
 *
 * @package         Wp_Paint
 */


/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit();
}


// // Define Path.
define('WPP_PLUGIN_VERSION', '0.4.6');
define('WPP_PLUGIN_VERSION_HASH', '7610ee57c935d32b661f5bf52ece9b564b155559055fd5c7');
define('WPP_PLUGIN_PATH', dirname(__FILE__));
define('WPP_PLUGIN_URL', plugins_url('', __FILE__));

if(!function_exists('wp_paint_activate')) {
  function wp_paint_activate() {
    if(function_exists('wp_paint_pro_edit_image')) {
      require(WPP_PLUGIN_PATH . "/admin/plugin-activation-error.php");
      exit;
    }
    update_option("wp_paint_activated_on", time());
  }
  register_activation_hook( __FILE__, 'wp_paint_activate' );
}

/* Restore image */
if(!function_exists('wp_paint_restore_image')) {
  function wp_paint_restore_image($post_id) {
    $path = get_attached_file( $post_id );

    $basename = pathinfo( $path, PATHINFO_BASENAME );
    $dirname  = pathinfo( $path, PATHINFO_DIRNAME );
    $ext      = pathinfo( $path, PATHINFO_EXTENSION );
    $filename = pathinfo( $path, PATHINFO_FILENAME );

    $filename = preg_replace( '/-(e|wpp)([0-9]+)$/', '', $filename );
    $original_filename = "{$filename}.{$ext}";
    $original_path     = "{$dirname}/$original_filename";
    update_attached_file( $post_id, $original_path );
    $wpp_revisions = get_post_meta( $post_id, '_wp_attachment_wpp_revisions', true );
    $wpp_revisions['full_orig_filename'] = $original_filename;
    update_post_meta( $post_id, '_wp_attachment_wpp_revisions', $wpp_revisions );
    wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $original_path ));
  }
}

/* Save Image */
if(!function_exists('wp_paint_save_image')) {
  function wp_paint_save_image($image_data_uri, $post_id) {
    $image_data = @file_get_contents("data://{$image_data_uri}");
    if(empty($image_data)) {
      wp_die('<center class="wp-core-ui">Sorry, the image data submitted is not valid. Please check your PHP Settings. <br><br><a href="" class="button button-primary">&laquo; Back to WP Paint Editor</a></center>');
      exit;
    }
    if(empty($post_id) || !is_numeric($post_id) || $post_id < 1) {
      wp_die('<center class="wp-core-ui">Sorry, invalid attachment. <br><br><a href="" class="button button-primary">&laquo; Back to WP Paint Editor</a></center>');
      exit;
    }
    $path = get_attached_file( $post_id );

    $basename = pathinfo( $path, PATHINFO_BASENAME );
    $dirname  = pathinfo( $path, PATHINFO_DIRNAME );
    $ext      = pathinfo( $path, PATHINFO_EXTENSION );
    $filename = pathinfo( $path, PATHINFO_FILENAME );
    $suffix   = time() . rand( 100, 999 );
    $wp_upload_dir = wp_upload_dir();
    $wp_upload_basedir = $wp_upload_dir['basedir'];

    $filename     = preg_replace( '/-wpp([0-9]+)$/', '', $filename );
    $original_filename = $filename;
    $filename    .= "-wpp{$suffix}";
    $new_filename = "{$filename}.{$ext}";
    $new_path     = "{$dirname}/$new_filename";

    $wp_upload_basedir_ts = "{$wp_upload_basedir}/";
    $timeformat   = null;
    if (substr($dirname, 0, strlen($wp_upload_basedir_ts)) == $wp_upload_basedir_ts) {
      $timeformat = substr($dirname, strlen($wp_upload_basedir_ts));
    }
    
    if(!empty($image_data)) {
      $wpp_upload_data = wp_upload_bits($new_filename, null, $image_data, $timeformat);
      $saved_path = $wpp_upload_data['file'];
      update_attached_file( $post_id, $saved_path );
      $wpp_revisions = @get_post_meta( $post_id, '_wp_attachment_wpp_revisions', true );
      $wpp_revisions = (empty($wpp_revisions) || !is_array($wpp_revisions)) ? [] : $wpp_revisions;
      if(!isset($wpp_revisions['full_orig_filename']) || empty($wpp_revisions['full_orig_filename'])) {
        $wpp_revisions['full_orig_filename'] = $original_filename;
      }
      $wpp_revisions['full_wpp_'.$suffix] = $new_filename;
      update_post_meta( $post_id, '_wp_attachment_wpp_revisions', $wpp_revisions );
      wp_update_attachment_metadata( $post_id, wp_generate_attachment_metadata( $post_id, $saved_path ));
    }
  }
}


if(!function_exists('wp_paint_review_request_notice')) {
  function wp_paint_review_request_notice() {
    ?>
    <script type="text/javascript">
      jQuery(function () {
        jQuery('body').on('click', '.wpp-review-notice .notice-dismiss', function () {
          jQuery('.wpp-review-notice .wpp-review-later').trigger('click');
        });
        jQuery('body').on('click', '.wpp-review-action', function () {
          var $self = jQuery(this);
          var wpp_action = $self.data('wppAction');
          jQuery('.wpp-review-notice').css('opacity', 0.5);
          jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
              action: wpp_action
            },
            success: function () {
              jQuery('.wpp-review-notice').fadeOut();
            }
          });
        });
      });
    </script>
    <div class="notice notice-success is-dismissible wpp-review-notice">
      <p><?php _e('We are glad that you are finding <strong>"WP Paint - WordPress Image Editor"</strong> useful - that\'s awesome!'); ?> <br> <?php _e('If you have a moment, please help us spread the word by reviewing the plugin on WordPress.'); ?></p>
      <p><em><?php _e('~ Team ZetaMatic'); ?></em></p>
      <p>
        <a href="https://wordpress.org/support/plugin/wp-paint/reviews/#new-post" target="_blank"><?php _e('Sure, I\'ll write a review!'); ?></a><span style="color: #DDD;"> | </span>
        <a href="javascript:void(0);" class="wpp-review-action wpp-review-done" data-wpp-action="wp_paint_review_done"><?php _e('I\'ve already reviewed this plugin!'); ?></a><span style="color: #DDD;"> | </span>
        <a href="javascript:void(0);" class="wpp-review-action wpp-review-later" data-wpp-action="wp_paint_review_later"><?php _e('Maybe later!'); ?></a>
      </p>
    </div>
    <?php
  }
}
if(!function_exists('wp_paint_review_later')) {
  function wp_paint_review_later() {
    $days_to_remind_after = 7;
    update_option("wp_paint_review_later_time", time() + round($days_to_remind_after * 24 * 3600));
  }
  add_action( 'wp_ajax_wp_paint_review_later', 'wp_paint_review_later' );
}
if(!function_exists('wp_paint_review_done')) {
  function wp_paint_review_done() {
    update_option("wp_paint_review_done", 1);
  }
  add_action( 'wp_ajax_wp_paint_review_done', 'wp_paint_review_done' );
}
/* Register script */
if(!function_exists('wp_paint_register_scripts')) {
  function wp_paint_register_scripts() {
    $wp_paint_activated_on = get_option('wp_paint_activated_on');
    if(!$wp_paint_activated_on) {
      update_option("wp_paint_activated_on", time());
    }
    $wp_paint_edits = get_option("wp_paint_edits", 0);
    $wp_paint_review_done = get_option("wp_paint_review_done", false);
    $wp_paint_review_later_time = get_option("wp_paint_review_later_time", 0);
    if(!$wp_paint_review_done && time() > $wp_paint_review_later_time && $wp_paint_edits >= 3) {
      add_action('admin_notices', 'wp_paint_review_request_notice');
    }
    if(!current_user_can('manage_options'))
      return;
    wp_register_script('wpp_jquery_initialize_js', WPP_PLUGIN_URL . "/static/js/jquery.initialize.js", ['jquery'], WPP_PLUGIN_VERSION_HASH);
    wp_register_script('wpp_paint_admin_js', WPP_PLUGIN_URL . "/static/js/wp-paint-admin.min.js", ['media-grid', 'wpp_jquery_initialize_js'], WPP_PLUGIN_VERSION_HASH);
    wp_register_style('wpp_paint_admin_css', WPP_PLUGIN_URL . "/static/css/wp-paint-admin.min.css", [], WPP_PLUGIN_VERSION_HASH);

    wp_register_script('wpp_pace_js', WPP_PLUGIN_URL . "/static/libs/pace/pace.min.js", ['jquery'], WPP_PLUGIN_VERSION_HASH);
    wp_register_script('wpp_jquery_blockui_js', WPP_PLUGIN_URL . "/static/js/jquery.blockUI.js", ['jquery'], WPP_PLUGIN_VERSION_HASH);
    wp_register_script('wpp_mini_paint_js', WPP_PLUGIN_URL . "/static/libs/mini-paint/mini-paint.min.js", ['jquery'], WPP_PLUGIN_VERSION_HASH);
    wp_register_script('wpp_paint_box_js', WPP_PLUGIN_URL . "/static/js/wp-paint-box.min.js", ['wpp_pace_js', 'wpp_jquery_blockui_js', 'wpp_mini_paint_js'], WPP_PLUGIN_VERSION_HASH);
    wp_register_style('wpp_pace_css', WPP_PLUGIN_URL . "/static/libs/pace/pace.css", [], WPP_PLUGIN_VERSION_HASH);
    wp_register_style('wpp_mini_paint_css', WPP_PLUGIN_URL . "/static/libs/mini-paint/mini-paint.min.css", [], WPP_PLUGIN_VERSION_HASH);
    wp_register_style('wpp_paint_box_css', WPP_PLUGIN_URL . "/static/css/wp-paint-box.min.css", ['wpp_pace_css', 'wpp_mini_paint_css'], WPP_PLUGIN_VERSION_HASH);
  }
  add_action('admin_init', 'wp_paint_register_scripts');
}

/* Edit image */
if(!function_exists('wp_paint_edit_image')) {
  function wp_paint_edit_image() {
    if(!(basename($_SERVER['PHP_SELF']) == "admin.php" && $_GET['page'] == "wp-paint-edit-image" && current_user_can('manage_options'))) {
      return;
    }
    $post_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;

    if(empty($post_id)) {
      return;
    }
    
    $wpp_action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : "";
    $form_url = get_admin_url(null, "admin.php?page=wp-paint-edit-image&id={$post_id}&_=".uniqid());

    if($wpp_action == "restore_image") {
      if (!isset($_POST['wpp_restore_image_nonce']) || ! wp_verify_nonce( $_POST['wpp_restore_image_nonce'], 'wpp_restore_image')) {
        wp_die('<center class="wp-core-ui">Sorry, your nonce did not verify. <br><br><a href="'.$form_url.'" class="button button-primary">&laquo; Back to WP Paint Editor</a></center>');
        exit;
      }
      wp_paint_restore_image( $post_id );
    }
    if($wpp_action == "save_image") {
      if (!isset($_POST['wpp_save_image_nonce']) || ! wp_verify_nonce( $_POST['wpp_save_image_nonce'], 'wpp_save_image')) {
        wp_die('<center class="wp-core-ui">Sorry, your nonce did not verify. <br><br><a href="'.$form_url.'" class="button button-primary">&laquo; Back to WP Paint Editor</a></center>');
        exit;
      }
      $image_data_uri = sanitize_text_field($_POST['wpp_image_data']);
      wp_paint_save_image($image_data_uri, $post_id);
      if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['status'=>'success']);
        exit;
      }
    }

    $post = get_post( $post_id );
    $attachment_url = wp_get_attachment_url( $post_id );
    $attachment_basename = pathinfo( $attachment_url, PATHINFO_BASENAME );
    $ext      = pathinfo( $attachment_url, PATHINFO_EXTENSION );
    $filename = pathinfo( $attachment_url, PATHINFO_FILENAME );

    $original_filename = preg_replace( '/-(e|wpp)([0-9]+)$/', '', $filename );
    
    $wpp_revisions = get_post_meta( $post_id, '_wp_attachment_wpp_revisions', true );
    $can_restore  = false;

    if ( ! empty( $wpp_revisions ) && isset( $wpp_revisions['full_orig_filename'] ) ) {
      $can_restore = $filename != "{$original_filename}";
    }
    wp_enqueue_script('wpp_paint_box_js');
    wp_localize_script('wpp_paint_box_js', 'wp_paint_meta', [
      'can_restore' => $can_restore,
      'wp_admin_url' => admin_url(),
      'wpp_version' => WPP_PLUGIN_VERSION,
      'attachment_url' => $attachment_url,
      'wpp_revisions' => $wpp_revisions,
      'max_width' => 1280,
      'max_height' => 720
    ]);
    wp_enqueue_style('wpp_paint_box_css');

    if(empty($wpp_action)) {
      $wp_paint_edits = get_option("wp_paint_edits", 0);
      update_option("wp_paint_edits", $wp_paint_edits+1);
    }

    require_once(WPP_PLUGIN_PATH . "/admin/edit-image.php");
    exit;
  }
  add_action('admin_init', 'wp_paint_edit_image');
}

/**
 * Manage menu items and pages.
 */
if(!function_exists('wp_paint_setup_menu')) {
  function wp_paint_setup_menu() {
    add_submenu_page( null, 'Edit Image', 'Edit Image', 'manage_options', 'wp-paint-edit-image', 'wp_paint_edit_image');
  }
  add_action('admin_menu', 'wp_paint_setup_menu');
}

/* Enqueue script */
if(!function_exists('wp_paint_enqueue_scripts')) {
  function wp_paint_enqueue_scripts($hook) {
    if(!current_user_can('manage_options'))
      return;
    wp_enqueue_script('wpp_paint_admin_js');
    wp_localize_script('wpp_paint_admin_js', 'wp_paint_admin_meta', [
      'wp_admin_url' => admin_url(),
      'wpp_version' => WPP_PLUGIN_VERSION,
    ]);
    wp_enqueue_style('wpp_paint_admin_css');
  }
  add_action('wp_enqueue_media', 'wp_paint_enqueue_scripts');
}

/* Enqueue admin script */
if(!function_exists('wp_paint_enqueue_admin_scripts')) {
  function wp_paint_enqueue_admin_scripts($hook) {
    if(!in_array($hook, ["post.php", "upload.php"])) {
      return;
    }
    wp_enqueue_media();
    wp_paint_enqueue_scripts($hook);
  }
  add_action('admin_enqueue_scripts', 'wp_paint_enqueue_admin_scripts');
}

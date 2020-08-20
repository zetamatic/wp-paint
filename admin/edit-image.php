<?php
/**
 * WP-Paint Edit Image
 *
 * Edit image window for Wp_Paint
 *
 * @package     Wp_Paint
 * @author      ZetaMatic
 * @copyright   Copyright (c) 2019 ZetaMatic
 * @link        https://zetamatic.com/wp-paint/?utm_src=code_comment
 * @since       0.1.0
 */

if(!defined('WPP_PLUGIN_PATH')) {
  exit;
}
if(empty($attachment_url)) {
  wp_die('<center class="wp-core-ui">Sorry, invalid attachment. <br><br><a href="'.$form_url.'" class="button button-primary">&laquo; Back to WP Paint Editor</a></center>');
  exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>WP Paint</title>
  <?php do_action( 'admin_print_scripts' ); ?>
  <?php do_action( 'admin_print_styles' ); ?>
</head>
<body class="theme-light">
  <div id="wpp-loading">
    <center>Loading ...</center>
  </div>
  <div class="wrapper">
    
    <div class="submenu">
      <div class="block wpp-save-image">
        <form action="<?php echo $form_url; ?>" method="post" id="wpp-save-image-form">
          <input type="hidden" name="id" value="<?php echo $post_id; ?>">
          <input type="hidden" name="action" value="save_image">
          <textarea name="wpp_image_data" id="wpp-image-data" style="display: none;" cols="30" rows="10"></textarea>
          <?php wp_nonce_field( 'wpp_save_image', 'wpp_save_image_nonce' ); ?>
          <button type="submit" style="display: none;">Save</button>
        </form>
        <button type="button" class="trn" id="wpp-save-image-button" style="width: 100%;"><?php _e('Save image'); ?></button>
      </div>
      <div class="block attributes" id="action_attributes"></div>
      <div class="block wpp-restore-image">
        <form action="<?php echo $form_url; ?>" method="post" id="wpp-restore-image-form" style="display: <?php if($can_restore): ?>block<?php else: ?>none;<?php endif; ?>">
          <input type="hidden" name="id" value="<?php echo $post_id; ?>">
          <input type="hidden" name="action" value="restore_image">
          <?php wp_nonce_field( 'wpp_restore_image', 'wpp_restore_image_nonce' ); ?>
          <button type="submit" class="trn" style="width: 100%;"><?php _e('Restore image'); ?></button>
        </form>
      </div>
      <div class="clear"></div>
    </div>
    
    <div class="sidebar_left" id="tools_container"></div>
    
    <div class="main_wrapper" id="main_wrapper">
      <div class="canvas_wrapper" id="canvas_wrapper">
        <div id="mouse"></div>
        <div class="transparent-grid" id="canvas_minipaint_background"></div>
        <canvas id="canvas_minipaint">
          <div class="trn error">
            Your browser does not support canvas or JavaScript is not enabled.
          </div>
        </canvas>
      </div>
    </div>

    <div class="sidebar_right">
      <div class="preview block">
        <h2 class="trn toggle" data-target="toggle_preview">Preview</h2>
        <div id="toggle_preview"></div>
      </div>
      
      <div class="colors block">
        <h2 class="trn toggle" data-target="toggle_colors">Colors</h2>
        <input
          title="Click to change color" 
          type="color" 
          class="color_area" 
          id="main_color" 
          value="#888888"  />
        <div class="content" id="toggle_colors"></div>
      </div>
      
      <div class="block" id="info_base">
        <h2 class="trn toggle toggle-full" data-target="toggle_info">Information</h2>
        <div class="content" id="toggle_info"></div>
      </div>
      
      <div class="details block" id="details_base">
        <h2 class="trn toggle toggle-full" data-target="toggle_details">Layer details</h2>
        <div class="content" id="toggle_details"></div>
      </div>
      
      <div class="layers block">
        <h2 class="trn">Layers</h2>
        <div class="content" id="layers_base"></div>
      </div>
    </div>
  </div>
  <div class="mobile_menu">
    <button class="right_mobile_menu" id="mobile_menu_button" type="button"></button>
  </div>
  <div class="ddsmoothmenu" id="main_menu"></div>
  <div class="hidden" id="tmp"></div>
  <div id="popup"></div>
</body>
</html>

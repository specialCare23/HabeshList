<?php
define('MODERN_THEME_VERSION', '3.2.0');

osc_enqueue_script('php-date');

if( !OC_ADMIN ) {
  if( !function_exists('add_close_button_action') ) {
    function add_close_button_action(){
      echo '<script type="text/javascript">';
        echo '$(".flashmessage .ico-close").click(function(){';
          echo '$(this).parent().hide();';
        echo '});';
      echo '</script>';
    }
    osc_add_hook('footer', 'add_close_button_action');
  }
}

function theme_modern_actions_admin() {
  if( Params::getParam('file') == 'oc-content/themes/modern/admin/settings.php' ) {
    if( Params::getParam('donation') == 'successful' ) {
      osc_set_preference('donation', '1', 'modern_theme');
      osc_reset_preferences();
    }
  }

  switch( Params::getParam('action_specific') ) {
    case('settings'):
      $footerLink  = Params::getParam('footer_link');
      $defaultLogo = Params::getParam('default_logo');
      osc_set_preference('keyword_placeholder', Params::getParam('keyword_placeholder'), 'modern_theme');
      osc_set_preference('footer_link', ($footerLink ? '1' : '0'), 'modern_theme');
      osc_set_preference('default_logo', ($defaultLogo ? '1' : '0'), 'modern_theme');

      osc_add_flash_ok_message(__('Theme settings updated correctly', 'modern'), 'admin');
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/modern/admin/settings.php'));
    break;
    case('upload_logo'):
      $package = Params::getFiles('logo');
      if( $package['error'] == UPLOAD_ERR_OK ) {
        if( move_uploaded_file($package['tmp_name'], WebThemes::newInstance()->getCurrentThemePath() . "images/logo.jpg" ) ) {
          osc_add_flash_ok_message(__('The logo image has been uploaded correctly', 'modern'), 'admin');
        } else {
          osc_add_flash_error_message(__("An error has occurred, please try again", 'modern'), 'admin');
        }
      } else {
        osc_add_flash_error_message(__("An error has occurred, please try again", 'modern'), 'admin');
      }
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/modern/admin/header.php'));
    break;
    case('remove'):
      if(file_exists( WebThemes::newInstance()->getCurrentThemePath() . "images/logo.jpg" ) ) {
        @unlink( WebThemes::newInstance()->getCurrentThemePath() . "images/logo.jpg" );
        osc_add_flash_ok_message(__('The logo image has been removed', 'modern'), 'admin');
      } else {
        osc_add_flash_error_message(__("Image not found", 'modern'), 'admin');
      }
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/modern/admin/header.php'));
    break;
  }
}

if(modern_is_fineuploader()) {
  if(!OC_ADMIN) {
    osc_enqueue_style('fine-uploader-css', osc_assets_url('js/fineuploader/fineuploader.css'));
  }
  osc_enqueue_script('jquery-fineuploader');
}

function modern_is_fineuploader() {
  return Scripts::newInstance()->registered['jquery-fineuploader'] && method_exists('ItemForm', 'ajax_photos');
}

osc_add_hook('init_admin', 'theme_modern_actions_admin');
osc_admin_menu_appearance(__('Header logo', 'modern'), osc_admin_render_theme_url('oc-content/themes/modern/admin/header.php'), 'header_modern');
osc_admin_menu_appearance(__('Theme settings', 'modern'), osc_admin_render_theme_url('oc-content/themes/modern/admin/settings.php'), 'settings_modern');

if( !function_exists('logo_header') ) {
  function logo_header() {
    $html = '<a href="' . osc_base_url() . '"><img border="0" alt="' . osc_page_title() . '" src="' . osc_current_web_theme_url('images/logo.jpg') . '" /></a>';
  //  $html = '<img border="0" alt="' . osc_page_title() . '" src="' . osc_current_web_theme_url('images/logo.jpg') . '" />';
    if( file_exists( WebThemes::newInstance()->getCurrentThemePath() . "images/logo.jpg" ) ) {
      return $html;
    } else if( osc_get_preference('default_logo', 'modern_theme') && (file_exists( WebThemes::newInstance()->getCurrentThemePath() . "images/default-logo.jpg")) ) {
      return '<img border="0" alt="' . osc_page_title() . '" src="' . osc_current_web_theme_url('images/default-logo.jpg') . '" />';
    } else {
      return osc_page_title();
    }
  }
}

// install update options
if( !function_exists('modern_theme_install') ) {
  function modern_theme_install() {
    osc_set_preference('keyword_placeholder', __('ie. PHP Programmer', 'modern'), 'modern_theme');
    osc_set_preference('version', MODERN_THEME_VERSION, 'modern_theme');
    osc_set_preference('footer_link', true, 'modern_theme');
    osc_set_preference('donation', '0', 'modern_theme');
    osc_set_preference('default_logo', '1', 'modern_theme');
    osc_reset_preferences();
  }
}

if(!function_exists('check_install_modern_theme')) {
  function check_install_modern_theme() {
    $current_version = osc_get_preference('version', 'modern_theme');
    //check if current version is installed or need an update<
    if( !$current_version ) {
      modern_theme_install();
    }
  }
}
check_install_modern_theme();

?>

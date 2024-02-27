<?php
/*
 * Copyright 2014 Osclass
 * Copyright 2021 Osclass by OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * You may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Do not edit or add to this file if you wish to upgrade Osclass to newer
 * versions in the future. Software is distributed on an "AS IS" basis, without
 * warranties or conditions of any kind, either express or implied. Do not remove
 * this NOTICE section as it contains license information and copyrights.
 */

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo sigma_default_direction()=='0' ? 'ltr': 'rtl'; ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
  <head>
    <?php osc_current_web_theme_path('head.php') ; ?>
  </head>
<body <?php sigma_body_class(); ?>>
<header>
  <div class="wrapper">
    <div class="box">
      <div id="logo">
        <?php echo logo_header(); ?>
      </div>
      
      <div class="menu-icon isTablet isMobile">
        <div>
          <span class="l1"></span>
          <span class="l2"></span>
          <span class="l3"></span>
        </div>
      </div>
      
      <div class="nav">
    
        <a href="<?php echo osc_base_url(); ?>" class="isMobile"><?php _e('Home', 'sigma'); ?></a>
        <a class="isMobile" href="<?php echo osc_item_post_url_in_category() ; ?>"><?php _e("Publish Ad", 'sigma');?></a>
        <a href="<?php echo osc_contact_url(); ?>" class="isMobile"><?php _e('Contact', 'sigma'); ?></a>

        <?php if( osc_users_enabled() ) { ?>
          <?php if( osc_is_web_user_logged_in() ) { ?>
            <a href="<?php echo osc_user_dashboard_url(); ?>"><?php _e('My account', 'sigma'); ?></a>
            <a href="<?php echo osc_user_logout_url(); ?>"><?php _e('Logout', 'sigma'); ?></a>
          <?php } else { ?>
            <a id="login_open" href="<?php echo osc_user_login_url(); ?>"><?php _e('Login', 'sigma') ; ?></a>

            <?php if(osc_user_registration_enabled()) { ?>
              <a href="<?php echo osc_register_account_url() ; ?>"><?php _e('Register', 'sigma'); ?></a>
            <?php } ?>
          <?php } ?>
        <?php } ?>

        <?php if( osc_users_enabled() || ( !osc_users_enabled() && !osc_reg_user_post() )) { ?>
          <a class="publish isTablet isDesktop" href="<?php echo osc_item_post_url_in_category() ; ?>"><?php _e("Publish Ad", 'sigma');?></a>
        <?php } ?>
      </div>
    </div>
  </div>
</header>

<?php if(osc_get_preference('header-728x90', 'sigma') <> '') { ?>
  <section class="header-ad">
    <div class="wrapper">
      <div class="ads_header"><?php echo osc_get_preference('header-728x90', 'sigma'); ?></div>
    </div>
  </section>
<?php } ?>

<?php if( osc_is_home_page() ) { ?>
  <section class="home-search">
    <div class="wrapper">
      <form action="<?php echo osc_base_url(true); ?>" method="get" class="search nocsrf box">
        <input type="hidden" name="page" value="search"/>
        
        <?php if(osc_is_home_page()) { ?>
          <h1><?php _e('What are you looking for today?', 'sigma'); ?></h1>
        <?php } ?>

        <div class="main-search">
          <div class="cell c1">
            <label><?php _e('Keyword', 'sigma'); ?></label>
            <input type="text" name="sPattern" id="query" class="input-text" value="" placeholder="<?php echo osc_esc_html(__(osc_get_preference('keyword_placeholder', 'sigma'), 'sigma')); ?>" />
          </div>

          <div class="cell c2">
            <label><?php _e('Category', 'sigma'); ?></label>
            <?php osc_categories_select('sCategory', null, __('Select a category', 'sigma')) ; ?>
          </div>

          <div class="cell c3">
            <label>&nbsp;</label>
            <button class="btn btn-primary"><i class="fa fa-search"></i> <span><?php _e("Search", 'sigma');?></span></button>
          </div>
        </div>
        <div id="message-seach"></div>
      </form>
    </div>
  </section>
<?php } ?>


<section>
<?php osc_show_widgets('header'); ?>
  <?php $breadcrumb = osc_breadcrumb('>', false, get_breadcrumb_lang()); ?>

  <?php if( $breadcrumb !== '') { ?>
    <div class="wrapper wrapper-flash">
      <div class="breadcrumb">
        <?php echo $breadcrumb; ?>
        <div class="clear"></div>
      </div>
    </div>
  <?php } ?>

  <div class="wrapper wrapper-flash flash2"><?php osc_show_flash_message(); ?></div>

  <?php osc_run_hook('before-content'); ?>

  <div class="wrapper" id="content">
    <?php osc_run_hook('before-main'); ?>
    <div id="main">
      <?php osc_run_hook('inside-main'); ?>

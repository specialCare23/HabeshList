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
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="robots" content="noindex, nofollow, noarchive"/>
    <meta name="googlebot" content="noindex, nofollow, noarchive"/>
    <title><?php echo osc_page_title(); ?> &raquo; <?php _e('Lost your password'); ?></title>
    <script type="text/javascript" src="<?php echo osc_assets_url('js/jquery.min.js'); ?>"></script>
    <link type="text/css" href="style/backoffice_login.css" media="screen" rel="stylesheet"/>
    <?php osc_run_hook('admin_login_header'); ?>
  </head>
  <body class="recover">
    <div id="login">
      <h1>
        <a href="<?php echo View::newInstance()->_get('login_admin_url'); ?>" title="<?php echo View::newInstance()->_get('login_admin_title'); ?>">
          <img src="<?php echo View::newInstance()->_get('login_admin_image'); ?>" border="0" title="<?php echo View::newInstance()->_get('login_admin_title'); ?>" alt="<?php echo View::newInstance()->_get('login_admin_title'); ?>"/>
        </a>
      </h1>
      <?php osc_show_flash_message('admin'); ?>
      <div class="flashmessage">
        <?php _e('Please enter your username or e-mail address'); ?>.<br/>
        <?php _e('You will receive a new password via e-mail'); ?>.
      </div>

      <form id="recoverform" name="recoverform" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="login"/>
        <input type="hidden" name="action" value="recover_post"/>
        <p>
          <label for="user_email"><span><?php _e('E-mail'); ?></span>
          <input type="text" name="email" id="user_email" class="input" value="" size="20" tabindex="10"/></label>
        </p>
        <?php osc_show_recaptcha(); ?>
        <p class="submit"><input type="submit" name="submit" id="submit" value="<?php echo osc_esc_html( __('Get new password')); ?>" tabindex="100"/></p>
      </form>
      <p id="nav">
        <a title="<?php _e('Log in'); ?>" href="<?php echo osc_admin_base_url(); ?>"><?php _e('Log in'); ?></a>
      </p>
    </div>
    <p id="backtoblog"><a href="<?php echo osc_base_url(); ?>" title="<?php printf( __('Back to %s'), osc_page_title() ); ?>">&larr; <?php printf( __('Back to %s'), osc_page_title() ); ?></a></p>
    <script type="text/javascript">
      $(document).ready(function() {
        $('#user_email').focus(function() {
            $(this).prev().hide();
        }).blur(function() {
          if($(this).val() == '') {
            $(this).prev().show();
          }
        }).prev().click(function() {
            $(this).hide();
        });

        $(".ico-close").click(function() {
          $(this).parent().hide();
        });

        $("#user_email").focus();
      });
    </script>
    <?php osc_run_hook('admin_login_footer'); ?>
  </body>
</html>
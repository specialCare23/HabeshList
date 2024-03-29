<?php

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
    <head>
        <?php osc_current_web_theme_path('head.php'); ?>
        <meta name="robots" content="noindex, nofollow" />
        <meta name="googlebot" content="noindex, nofollow" />
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </head>
    <body>
        <?php osc_current_web_theme_path('header.php'); ?>
        <div class="content user_forms">
            <div class="inner">
                <h1><?php _e('Access to your account', 'modern'); ?></h1>
                <form action="<?php echo osc_base_url(true); ?>" method="post" >
                    <input type="hidden" name="page" value="login" />
                    <input type="hidden" name="action" value="login_post" />
                    <fieldset>
                        <label for="email"><?php _e('E-mail', 'modern'); ?></label> <?php UserForm::email_login_text(); ?><br />
                        <label for="password"><?php _e('Password', 'modern'); ?></label> <?php UserForm::password_login_text(); ?><br />
                        <p class="checkbox"><?php UserForm::rememberme_login_checkbox();?> <label for="remember"><?php _e('Remember me', 'modern'); ?></label></p>
                        <?php osc_run_hook("anr_captcha_form_field"); ?>
                        <button type="submit"><?php _e("Log in", 'modern');?></button>
                        <div class="more-login">
                            <a href="<?php echo osc_register_account_url(); ?>"><?php _e("Register for a free account", 'modern'); ?></a> · <a href="<?php echo osc_recover_user_password_url(); ?>"><?php _e("Forgot password?", 'modern'); ?></a>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
        <?php osc_current_web_theme_path('footer.php'); ?>
    </body>
</html>
<?php

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
    <head>
        <?php osc_current_web_theme_path('head.php'); ?>
        <meta name="robots" content="noindex, nofollow" />
        <meta name="googlebot" content="noindex, nofollow" />
    </head>
    <body>
        <?php osc_current_web_theme_path('header.php'); ?>
        <div class="content user_account">
            <h1><strong><?php _e('User account manager', 'modern'); ?></strong></h1>
            <div id="sidebar">
                <?php echo osc_private_user_menu(); ?>
            </div>
            <div id="main">
                <h2><?php _e('Your alerts', 'modern'); ?></h2>
                <?php if(osc_count_alerts() == 0) { ?>
                    <h3><?php _e('You do not have any alerts yet', 'modern'); ?>.</h3>
                <?php } else { ?>
                    <?php while(osc_has_alerts()) { ?>
                        <div class="userItem" >
                            <div><?php _e('Alert', 'modern'); ?> | <a onclick="javascript:return confirm('<?php echo osc_esc_js(__('This action can\'t be undone. Are you sure you want to continue?', 'modern')); ?>');" href="<?php echo osc_user_unsubscribe_alert_url(); ?>"><?php _e('Delete this alert', 'modern'); ?></a></div>
                            <div style="width: 75%; padding-left: 100px;" >
                            <?php while(osc_has_items()) { ?>
                                <div class="userItem" >
                                    <div><a href="<?php echo osc_item_url(); ?>"><?php echo osc_item_title(); ?></a></div>
                                    <div class="userItemData" >
                                    <?php _e('Publication date', 'modern'); ?>: <?php echo osc_format_date(osc_item_pub_date()); ?><br />
                                    <?php if( osc_price_enabled_at_items() ) { _e('Price', 'modern'); ?>: <?php echo osc_format_price(osc_item_price()); } ?>
                                    </div>
                                </div>
                                <br />
                            <?php } ?>
                            <?php if(osc_count_items() == 0) { ?>
                                    <br />
                                    0 <?php _e('Listings', 'modern'); ?>
                            <?php } ?>
                            </div>
                        </div>
                        <br />
                    <?php } ?>
                <?php  } ?>
            </div>
        </div>
        <?php osc_current_web_theme_path('footer.php'); ?>
    </body>
</html>
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

<?php $size = explode('x', osc_thumbnail_dimensions()); ?>
<li class="<?php osc_run_hook("highlight_class"); ?>listing-card <?php echo $class; if(osc_item_is_premium()){ echo ' premium'; } ?>">
  <?php if( osc_images_enabled_at_items() ) { ?>
    <?php if(osc_count_item_resources()) { ?>
      <a class="listing-thumb" href="<?php echo osc_item_url() ; ?>" title="<?php echo osc_esc_html(osc_item_title()) ; ?>"><img src="<?php echo osc_resource_thumbnail_url(); ?>" title="" alt="<?php echo osc_esc_html(osc_item_title()) ; ?>" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>"></a>
    <?php } else { ?>
      <a class="listing-thumb" href="<?php echo osc_item_url() ; ?>" title="<?php echo osc_esc_html(osc_item_title()) ; ?>"><img src="<?php echo osc_current_web_theme_url('images/no_photo.gif'); ?>" title="" alt="<?php echo osc_esc_html(osc_item_title()) ; ?>" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>"></a>
    <?php } ?>
  <?php } ?>
  
  <?php 
    if(function_exists('fi_make_favorite')) { 
      echo fi_make_favorite();
    }
  ?>
  
  <div class="listing-detail">
    <div class="listing-cell">
      <div class="listing-data">
        <div class="listing-basicinfo">
          <a href="<?php echo osc_item_url() ; ?>" class="title" title="<?php echo osc_esc_html(osc_item_title()) ; ?>"><?php echo osc_item_title() ; ?></a>
          <div class="listing-attributes">
            <?php if( osc_price_enabled_at_items() ) { ?><span class="currency-value"><?php echo osc_format_price(osc_item_price()); ?></span><?php } ?>

            <div class="listing-details">
              <span class="category"><?php echo osc_item_category() ; ?></span>
              <span class="location"><?php echo osc_item_city(); ?> <?php if( osc_item_region()!='' ) { ?> (<?php echo osc_item_region(); ?>)<?php } ?></span> 
              <span class="date"><?php echo osc_format_date(osc_item_pub_date()); ?></span>
            </div>
          </div>

          <div class="desc"><?php echo osc_highlight( osc_item_description() ,250) ; ?></div>
        </div>
        
        <?php if(osc_item_user_id() > 0 && osc_item_user_id() == osc_logged_user_id()){ ?>
          <span class="admin-options">
            <a href="<?php echo osc_item_edit_url(); ?>" rel="nofollow"><?php _e('Edit item', 'sigma'); ?></a>
            
            <?php if(osc_item_can_renew()) { ?>
              <a href="<?php echo osc_item_renew_url();?>" ><?php _e('Renew', 'sigma'); ?></a>
            <?php } ?>
            
            <?php if(osc_item_is_inactive()) {?>
              <a href="<?php echo osc_item_activate_url();?>" ><?php _e('Activate', 'sigma'); ?></a>
            <?php } ?>
            
            <?php if(osc_item_is_active() && osc_can_deactivate_items()) {?>
              <a href="<?php echo osc_item_deactivate_url();?>" ><?php _e('Deactivate', 'sigma'); ?></a>
            <?php } ?>
            
            <a class="delete" onclick="javascript:return confirm('<?php echo osc_esc_js(__('This action can not be undone. Are you sure you want to continue?', 'sigma')); ?>')" href="<?php echo osc_item_delete_url();?>" ><?php _e('Delete', 'sigma'); ?></a>
          </span>
        <?php } ?>
      </div>
    </div>
  </div>
</li>
<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

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


/**
 * Class Form
 */
class Form {
  /**
   * @param $name
   * @param $items
   * @param $fld_key
   * @param $fld_name
   * @param $default_item
   * @param $id
   */
  protected static function generic_select( $name , $items , $fld_key , $fld_name , $default_item , $id, $limit = 1000 ) {
    $name = osc_esc_html($name);
    $fld_name_orig = $fld_name;
    echo '<select name="' . $name . '" id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name) . '">';
    if ( isset( $default_item ) ) {
      echo '<option value="">' . $default_item . '</option>';
    }
    
    $counter = 0;
    if(is_array($items) && !empty($items) && count($items) > 0) {
      foreach($items as $i) {
        if ( isset( $fld_key ) && isset( $fld_name ) ) {
          $fld_name = $fld_name_orig;
          if(osc_get_current_user_locations_native() == 1) {
            if(isset($i[ $fld_name . '_native']) && @$i[ $fld_name . '_native'] <> '') {
              $fld_name = $fld_name . '_native';
            }
          }

          if(isset($i[ $fld_key ])) {
            echo '<option value="' . osc_esc_html( $i[ $fld_key ] ) . '"' . ( ( $id == $i[ $fld_key ] ) ? ' selected="selected"' : '' ) . '>' . $i[ $fld_name ] . '</option>';
          }
        }
        
        if($counter > $limit) {
          break;
        }
        $counter++;
      }
    } else {
      echo '<option value="">' . __('No value') . '</option>';
    }
    
    echo '</select>';
  }

  /**
  * @param    $name
  * @param    $value
  * @param null $maxLength
  * @param bool $readOnly
  * @param bool $autocomplete
  */
  protected static function generic_input_text( $name , $value , $maxLength = null , $readOnly = false , $autocomplete = true, $size = -1, $type = 'text' ) {
    $name = osc_esc_html($name);
    $type = osc_esc_html(strtolower(trim($type)));
    $value = ($value === NULL ? '' : $value);
    
    if(!in_array($type, array('button','checkbox','color','date','datetime-local','email','file','hidden','image','month','number','password','radio','range','reset','search','submit','tel','text','time','url','week'))) {
      $type = 'text';
    }
    
    echo '<input id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name) . '" type="' . $type . '" name="' . $name . '" value="' . osc_esc_html(htmlentities($value, ENT_COMPAT, 'UTF-8')) . '"';

    if (isset($maxLength) && $maxLength > 0) {
      echo ' maxlength="' . osc_esc_html( $maxLength ) . '"';
    }
    
    if (!$autocomplete) {
      echo ' autocomplete="off"';
    }
    
    if ($size > 0) {
      echo ' size="' . $size . '"';
    }
    
    if ($readOnly) {
      echo ' disabled="disabled" readonly="readonly"';
    }
    
    echo ' />';
  }

  /**
  * @param    $name
  * @param    $value
  * @param null $maxLength
  * @param bool $readOnly
  */
  protected static function generic_password( $name , $value , $maxLength = null , $readOnly = false ) {
    $name = osc_esc_html($name);
    echo '<input id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name) . '" type="password" name="' . $name . '" value="' . osc_esc_html(htmlentities( $value, ENT_COMPAT, 'UTF-8' )) . '"';

    if (isset($maxLength) && $maxLength > 0) {
      echo ' maxlength="' . osc_esc_html( $maxLength ) . '"';
    }
    
    if ( $readOnly ) {
      echo ' disabled="disabled" readonly="readonly"';
    }
    
    echo ' autocomplete="off" />';
  }

  /**
  * @param $name
  * @param $value
  */
  protected static function generic_input_hidden( $name , $value ) {
    $name = osc_esc_html($name);
    echo '<input id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name) . '" type="hidden" name="' . $name . '" value="' . osc_esc_html(htmlentities( $value, ENT_COMPAT, 'UTF-8' )) . '" />';
  }

  /**
  * @param    $name
  * @param    $value
  * @param bool $checked
  */
  protected static function generic_input_checkbox( $name , $value , $checked = false ) {
    $name = osc_esc_html($name);
    echo '<input id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name) . '" type="checkbox" name="' . $name . '" value="' . osc_esc_html(htmlentities( $value, ENT_COMPAT, 'UTF-8' )) . '"';

    if ( $checked ) {
      echo ' checked="checked"';
    }
    echo ' />';
  }

  /**
  * @param $name
  * @param $value
  */
  protected static function generic_textarea( $name , $value ) {
    $name = osc_esc_html($name);
    echo '<textarea id="' . preg_replace('|([^_a-zA-Z0-9-]+)|', '', $name) . '" name="' . $name . '" rows="10">' . $value . '</textarea>';
  }
}
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


/**
* Helper Pages
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/

/**
 * Gets current page object
 *
 * @return array
 */
function osc_static_page() {
  if (View::newInstance()->_exists('pages')) {  
    $page = View::newInstance()->_current('pages');
  } else if (View::newInstance()->_exists('page')) {  
    $page = View::newInstance()->_get('page');  
  } else {  
    $page = null;  
  }

  if (!View::newInstance()->_exists('page_meta')) {
    View::newInstance()->_exportVariableToView('page_meta', json_decode(isset($page['s_meta']) ? $page['s_meta'] : '', true));
  }

  return $page;
}

/**
 * Gets current page field
 *
 * @param string $field
 * @param string $locale
 * @return string
 */
function osc_static_page_field($field, $locale = '') {
  return osc_field(osc_static_page(), $field, $locale);
}

/**
 * Gets current page title
 *
 * @param string $locale
 * @return string
 */
function osc_static_page_title($locale = '') {
  if ($locale == '') {
    $locale = osc_current_user_locale();
  }
  return osc_static_page_field('s_title' , $locale);
}

/**
 * Gets current page text
 *
 * @param string $locale
 * @return string
 */
function osc_static_page_text($locale = '') {
  if ($locale == '') {
    $locale = osc_current_user_locale();
  }
  return osc_static_page_field('s_text' , $locale);
}

/**
 * Gets current page ID
 *
 * @return string
 */
function osc_static_page_id() {
  return osc_static_page_field('pk_i_id');
}

/**
 * Get page order
 *
 * @return int
 */
function osc_static_page_order() {
  return (int)osc_static_page_field('i_order');
}

/**
 * Get page add to footer
 *
 * @return boolean
 */
function osc_static_page_footer_link() {
  return (boolean)osc_static_page_field('b_link');
}

/**
 * Get page index or no-index
 *
 * @return boolean
 */
function osc_static_page_indexable() {
  return (boolean)osc_static_page_field('b_index');
}

/**
 * Gets current page modification date
 *
 * @return string
 */
function osc_static_page_mod_date() {
  return osc_static_page_field('dt_mod_date');
}

/**
 * Gets current page publish date
 *
 * @return string
 */
function osc_static_page_pub_date() {
  return osc_static_page_field('dt_pub_date');
}

/**
 * Gets current page slug or internal name
 *
 * @return string
 */
function osc_static_page_slug() {
  return osc_static_page_field('s_internal_name');
}

/**
 * Gets static page visibility
 *
 * @return integer
 */
function osc_static_page_visibility() {
  return osc_static_page_field('i_visibility');
}

/**
 * Gets static page visibility name
 *
 * @return string
 */
function osc_static_page_visibility_name($visibility_id) {
  switch($visibility_id) {
    case 0:
      return __('Anyone');
      break;
    case 1:
      return __('Logged-in users');
      break;
    case 2:
      return __('Personal users');
      break;
    case 3:
      return __('Company users');
      break;
    case 4:
      return __('Admins');
      break;
    default:
      return __('Hidden');
      break;
  }
}



/**
 * Gets current page meta information
 *
 * @param null $field
 *
 * @return string
 */
function osc_static_page_meta($field = null) {
  if (!View::newInstance()->_exists('page_meta')) {
    $meta = json_decode(osc_static_page_field('s_meta'), true);
  } else {
    $meta = View::newInstance()->_get('page_meta');
  }
  if ($field == null) {
    $meta = (isset($meta[$field]) && !empty($meta[$field])) ? $meta[$field] : '';
  }
  return $meta;
}


/**
 * Gets current page url
 *
 * @param string $locale
 *
 * @return string
 * @throws \Exception
 */
function osc_static_page_url($locale = '') {
  if (osc_rewrite_enabled()) {
    $sanitized_categories = array();
    $cat = Category::newInstance()->hierarchy(osc_item_category_id());
    for ($i = count($cat); $i > 0; $i--) {
      $sanitized_categories[] = $cat[$i - 1]['s_slug'];
    }
    $url = str_replace(array ('{PAGE_ID}' , '{PAGE_TITLE}') , array (
      osc_static_page_id() ,
      osc_static_page_title()
   ) , str_replace('{PAGE_SLUG}', urlencode(osc_static_page_slug()), osc_get_preference('rewrite_page_url')));
    if($locale!='') {
      $path = osc_base_url().$locale . '/' . $url;
    } else {
      $path = osc_base_url(false, true).$url;
    }
  } else {
    if($locale!='') {
      $path = osc_base_url(true) . '?page=page&id=' . osc_static_page_id() . '&lang=' . $locale;
    } else {
      $path = osc_base_url(true) . '?page=page&id=' . osc_static_page_id();
    }
  }
  return $path;
}


/**
 * Gets the specified static page by internal name.
 *
 * @param string $internal_name
 * @param string $locale
 *
 * @return void
 */
function osc_get_static_page($internal_name, $locale = '') {
  if ($locale == '') {
    $locale = osc_current_user_locale();
  }
  $page = Page::newInstance()->findByInternalName($internal_name, $locale);
  View::newInstance()->_exportVariableToView('page_meta', json_decode(isset($page['s_meta']) ? $page['s_meta'] : '', true));
  return View::newInstance()->_exportVariableToView('page', $page);
}

/**
 * Gets the total of static pages. If static pages are not loaded, this function will load them.
 *
 * @return int
 */
function osc_count_static_pages() {
  if (!View::newInstance()->_exists('pages')) {
    View::newInstance()->_exportVariableToView('pages', Page::newInstance()->listAll(false));
  }
  return View::newInstance()->_count('pages');
}

/**
 * Let you know if there are more static pages in the list. If static pages are not loaded,
 * this function will load them.
 *
 * @return boolean
 */
function osc_has_static_pages() {
  if (!View::newInstance()->_exists('pages')) {
    View::newInstance()->_exportVariableToView('pages', Page::newInstance()->listAll(false, 1));
  }

  $page = View::newInstance()->_next('pages');
  View::newInstance()->_exportVariableToView('page_meta', json_decode(isset($page['s_meta']) ? $page['s_meta'] : '', true));
  return $page;
}


/**
 * Move the iterator to the first position of the pages array
 * It reset the osc_has_page function so you could have several loops
 * on the same page
 *
 * @return void
 */
function osc_reset_static_pages() {
  return View::newInstance()->_erase('pages');
}
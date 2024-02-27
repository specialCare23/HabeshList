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


class CAdminSettingsMain extends AdminSecBaseModel {
  //Business Layer...
  function doModel() {
    switch($this->action) {
      case('check_updates'):
        osc_set_preference('last_version_check', time());
        $data = osc_file_get_contents(osc_osclass_url());
        $data  = json_decode($data, true);

        if(isset($data['version'])) {
          if($data['version'] > osc_version()) {
            osc_set_preference('update_core_json', json_encode($data));
          } else {
            osc_set_preference('update_core_json', '');
          }
        }
        
        osc_admin_toolbar_update_core(true);
        osc_admin_toolbar_update_themes(true);
        osc_admin_toolbar_update_plugins(true);
        osc_admin_toolbar_update_languages(true);

        osc_add_flash_ok_message( _m('Last check') . ':   ' . date("Y-m-d H:i:s") , 'admin');

        $this->redirectTo(osc_admin_base_url(true) . '?page=settings');
        break;
        
      case('validate_api_key'):
        $data = osc_file_get_contents(osc_market_url('validate_api_key'));
        $data  = json_decode($data, true);

        if(isset($data['error']) && $data['error'] <> '') {
          osc_add_flash_error_message(_m(sprintf(_m('API key validation error: %s'), $data['error'])), 'admin');
        } else if(isset($data['success']) && $data['success'] <> '') {
          osc_add_flash_ok_message($data['success'], 'admin');
        } else {
          osc_add_flash_warning_message(sprintf(_m('API key validation failed, response from server is empty. Your API key is: "%s", make sure it is correct.'), osc_update_api_key()), 'admin');
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings');
        break;
        
        
      case('update'):
        // update index view
        osc_csrf_check();
        $iUpdated = 0;
        $sPageTitle = Params::getParam('pageTitle');
        $sPageDesc = Params::getParam('pageDesc');
        $sContactEmail = Params::getParam('contactEmail');
        $sLanguage = Params::getParam('language');
        $bLocaleToBaseUrl = Params::getParam('locale_to_base_url_enabled');
        $sLocaleToBaseUrlType = Params::getParam('locale_to_base_url_type');
        $sLanguage = Params::getParam('language');
        $sDateFormat = Params::getParam('dateFormat');
        $sCurrency = Params::getParam('currency');
        $sWeekStart = Params::getParam('weekStart');
        $sTimeFormat = Params::getParam('timeFormat');
        $sTimezone = Params::getParam('timezone');
        $sNumRssItems = Params::getParam('num_rss_items');
        $maxLatestItems = Params::getParam('max_latest_items_at_home');
        $searchPatternMethod = Params::getParam('search_pattern_method');
        $numItemsSearch = Params::getParam('default_results_per_page');
        $webContactFormDisabled = Params::getParam('web_contact_form_disabled');
        $contactAttachment = Params::getParam('enabled_attachment');
        $selectableParent = Params::getParam('selectable_parent_categories');
        $adminTheme = Params::getParam('admin_theme');
        $adminColorScheme = Params::getParam('admin_color_scheme');
        $jqueryVersion = Params::getParam('jquery_version');
        $bAutoCron = Params::getParam('auto_cron');
        $hideGenerator = Params::getParam('hide_generator');
        $opApiKey = Params::getParam('osclasspoint_api_key');
        $structuredData = Params::getParam('structured_data');
        $updateIncludeOccontent = Params::getParam('update_include_occontent');
        $sAutoUpdate = (is_array(Params::getParam('auto_update')) ? join("|", Params::getParam('auto_update')) : Params::getParam('auto_update'));

        // preparing parameters
        $sPageTitle = trim(strip_tags($sPageTitle));
        $sPageDesc = trim(strip_tags($sPageDesc));
        $sContactEmail = trim(strip_tags($sContactEmail));
        $sLanguage = trim(strip_tags($sLanguage));
        $bLocaleToBaseUrl = ($bLocaleToBaseUrl != '' ? true : false);
        
        if(osc_subdomain_type() == 'language') {
          osc_add_flash_error_message( _m('Option "Add language code into base URL" is not supported when subdomain type "Language" is enabled!'), 'admin');
          $bLocaleToBaseUrl = false;
        }
        
        $sLocaleToBaseUrlType = trim(strip_tags($sLocaleToBaseUrlType));
        $sDateFormat = trim(strip_tags($sDateFormat));
        $sCurrency = trim(strip_tags($sCurrency));
        $sWeekStart = trim(strip_tags($sWeekStart));
        $sTimeFormat = trim(strip_tags($sTimeFormat));
        $opApiKey = trim(strip_tags($opApiKey));
        $sNumRssItems = (int) trim(strip_tags($sNumRssItems));
        $maxLatestItems = (int) trim(strip_tags($maxLatestItems));
        $searchPatternMethod = trim(strip_tags($searchPatternMethod));
        $numItemsSearch = (int) $numItemsSearch;
        $webContactFormDisabled = ($webContactFormDisabled != '' ? true : false);
        $contactAttachment = ($contactAttachment != '' ? true : false);
        $updateIncludeOccontent = ($updateIncludeOccontent != '' ? true : false);
        $structuredData = ($structuredData != '' ? true : false);
        $bAutoCron = ($bAutoCron != '' ? true : false);
        $hideGenerator = ($hideGenerator != '' ? true : false);
        $error = "";

        $msg = '';
        if(!osc_validate_text($sPageTitle)) {
          $msg .= _m("Page title field is required")."<br/>";
        }
        if(!osc_validate_text($sContactEmail)) {
          $msg .= _m("Contact email field is required")."<br/>";
        }
        if(!osc_validate_int($sNumRssItems)) {
          $msg .= _m("Number of listings in the RSS has to be a numeric value")."<br/>";
        }
        if(!osc_validate_int($maxLatestItems)) {
          $msg .= _m("Max latest listings has to be a numeric value")."<br/>";
        }
        if(!osc_validate_int($numItemsSearch)) {
          $msg .= _m("Number of listings on search has to be a numeric value")."<br/>";
        }
        if($msg!='') {
          osc_add_flash_error_message( $msg, 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=settings');
        }

        $iUpdated += osc_set_preference('pageTitle', $sPageTitle);
        $iUpdated += osc_set_preference('pageDesc', $sPageDesc);

        if( !defined('DEMO') ) {
          $iUpdated += osc_set_preference('contactEmail', $sContactEmail);
        }
        $iUpdated += osc_set_preference('language', $sLanguage);
        $iUpdated += osc_set_preference('locale_to_base_url_enabled', $bLocaleToBaseUrl);
        $iUpdated += osc_set_preference('locale_to_base_url_type', $sLocaleToBaseUrlType);
        $iUpdated += osc_set_preference('dateFormat', $sDateFormat);
        $iUpdated += osc_set_preference('currency', $sCurrency);
        $iUpdated += osc_set_preference('weekStart', $sWeekStart);
        $iUpdated += osc_set_preference('timeFormat', $sTimeFormat);
        $iUpdated += osc_set_preference('timezone', $sTimezone);
        $iUpdated += osc_set_preference('auto_update', $sAutoUpdate);
        $iUpdated += osc_set_preference('osclasspoint_api_key', $opApiKey);
        $iUpdated += osc_set_preference('structured_data', $structuredData);
        $iUpdated += osc_set_preference('update_include_occontent', $updateIncludeOccontent);
        
        if(is_int($sNumRssItems)) {
          $iUpdated += osc_set_preference('num_rss_items', $sNumRssItems);
        } else {
          if($error != '') $error .= "</p><p>";
          $error .= _m('Number of listings in the RSS must be an integer');
        }

        if(is_int($maxLatestItems)) {
          $iUpdated += osc_set_preference('maxLatestItems@home', $maxLatestItems);
        } else {
          if($error != '') $error .= "</p><p>";
          $error .= _m('Number of recent listings displayed at home must be an integer');
        }

        $iUpdated += osc_set_preference('defaultResultsPerPage@search', $numItemsSearch);
        $iUpdated += osc_set_preference('web_contact_form_disabled', $webContactFormDisabled);
        $iUpdated += osc_set_preference('contact_attachment', $contactAttachment);
        $iUpdated += osc_set_preference('search_pattern_method', $searchPatternMethod);
        $iUpdated += osc_set_preference('auto_cron', $bAutoCron);
        $iUpdated += osc_set_preference('hide_generator', $hideGenerator);
        $iUpdated += osc_set_preference('selectable_parent_categories', $selectableParent);
        $iUpdated += osc_set_preference('admin_theme', $adminTheme);
        $iUpdated += osc_set_preference('admin_color_scheme', $adminColorScheme);
        $iUpdated += osc_set_preference('jquery_version', $jqueryVersion);


        if( $iUpdated > 0 ) {
          if( $error != '' ) {
          osc_add_flash_error_message( $error . "</p><p>" . _m('General settings have been updated'), 'admin');
          } else {
          osc_add_flash_ok_message( _m('General settings have been updated'), 'admin');
          }
        } else if($error != '') {
          osc_add_flash_error_message( $error , 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=settings');
        break;
        
        
      default:
        // calling the view
        $aLanguages = OSCLocale::newInstance()->listAllEnabled();
        $aCurrencies = Currency::newInstance()->listAll();

        $this->_exportVariableToView('aLanguages', $aLanguages);
        $this->_exportVariableToView('aCurrencies', $aCurrencies);

        $this->doView('settings/index.php');
        break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/main.php
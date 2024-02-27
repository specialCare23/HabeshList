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


class CAdminTranslations extends AdminSecBaseModel {
  //specific for this class
  private $adminManager;

  function __construct() {
    parent::__construct();

    //specific things for this class
    //$this->adminManager = Admin::newInstance();
  }

  //Business Layer...
  function doModel() {
    parent::doModel();

    switch($this->action) {
      case('edit'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
        }

        $lang = Params::getParam('language');
        $lang_object = @OSCLocale::newInstance()->findByCode($lang)[0];
        $type = strtoupper(Params::getParam('type'));
        $section = strtoupper(Params::getParam('section'));
        $theme = Params::getParam('theme');
        $plugin = Params::getParam('plugin');
        
        $path = $this->get_path(Params::getParam('language'), Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));

        $exists = true;
        $translations = array();
        if(!file_exists($path)) {
          $exists = false;
        }
        
        if(file_exists($path)) {
          $loader = new Gettext\Loader\PoLoader();
          $data = $loader->loadFile($path);
          $translations = $data->getTranslations();
        }

        $this->_exportVariableToView('exists', $exists);
        $this->_exportVariableToView('path', $path);
        $this->_exportVariableToView('dir_path', dirname(dirname($path)));
        $this->_exportVariableToView('dir_name', basename(dirname($path)));
        $this->_exportVariableToView('link', str_replace(osc_base_path(), osc_base_url(), $path));
        $this->_exportVariableToView('file', str_replace(osc_base_path(), '', $path));
        $this->_exportVariableToView('file_name', basename(str_replace(osc_base_path(), '', $path)));
        $this->_exportVariableToView('translations', $translations);
        $this->_exportVariableToView('language', $lang);
        $this->_exportVariableToView('language_name', isset($lang_object['s_name']) ? $lang_object['s_name'] : '');
        $this->_exportVariableToView('market_search_url', $this->market_search_url(Params::getParam('language'), Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme')));

        $this->doView('translations/edit.php');
        break;
        
      case('edit_post'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
        }
        
        osc_csrf_check();

        $sources = Params::getParam('source', false, false, false);
        $translations = Params::getParam('translation', false, false, false);
        
        $sources_new = Params::getParam('source_new', false, false, false);
        $translations_new = Params::getParam('translation_new', false, false, false);

        $sources_remove = Params::getParam('source_remove', false, false, false);
        $comments = Params::getParam('comment', false, false, false);

        $path = Params::getParam('path');
        $file = Params::getParam('file');
        $file_name = Params::getParam('file_name');
        $lang = Params::getParam('language');
        $refresh = false;
        
        $loader = new Gettext\Loader\PoLoader();

        if(file_exists($path)) {
          $data = $loader->loadFile($path);
        } else {
          $data = Gettext\Translations::create(); 
        }
        

        // Update existing
        if(is_array($translations) && count($translations) > 0 && count($data) > 0) {
          foreach($translations as $key => $val) {
            $translation = $data->find(null, $sources[$key]);
            
            if($translation) {
              $translation->translate($val);
              $refresh = true;
            }
          }
        }
        

        // Add new lines
        if(is_array($translations_new) && count($translations_new) > 0) {
          foreach($translations_new as $key => $val) {
            if(trim($sources_new[$key]) != '' && $translations_new[$key] != '') {
              $translation = $data->find(null, $sources_new[$key]);
              
              // If translation exists, override it, otherwise create new
              if($translation) {
                $translation->translate($val);
              } else {
                $translation = Gettext\Translation::create(null, $sources_new[$key]);
                $data->add($translation);
                $translation->translate($val);
              }
              
              $refresh = true;
            }
          }
        }
        
        // Remove translations
        if(is_array($sources_remove) && count($sources_remove) > 0) {
          foreach($sources_remove as $key => $val) {
            if($key >= 0 && $val != '') {
              $translation = $data->find(null, $val);
              
              // If translation exists, find it and remove it
              if($translation) {
                $data->remove($translation);
              } else {
                if(isset($sources[$key])) {
                  $translation = $data->find(null, $sources[$key]);

                  if($translation) {
                    $data->remove($translation);
                  }
                }
              }

              $refresh = true;
            }
          }
        }
        
        
        // Add comments
        // Currently not used
        if(1==2 && is_array($comments) && count($comments) > 0) {
          foreach($comments as $key => $val) {
            if($key >= 0 && $val != '') {
              $translation = false;
              
              if(isset($sources[$key])) {
                $translation = $data->find(null, $sources[$key]);
              } else if(isset($sources_new[$key])) {
                $translation = $data->find(null, $sources_new[$key]);
              } 
              
              // If translation exists, add comment to it
              if($translation) {
                $translation->getComments()->add($val);
                $refresh = true;
              }
            }
          }
        }        


        // Refresh PO & MO files when needed
        if($refresh === true) {
          $data = $this->set_headers($data, Params::getParam('language'), Params::getParam('type'), Params::getParam('section'));

          // Generate folders those are missing
          if(!file_exists($path)) {
            $folder_check = $this->generate_folders($path);
            if($folder_check !== true) {
              osc_add_flash_error_message(sprintf(_m('Required and missing folder %s could not be created. Create it manually in your file system.'), $folder_check), 'admin');
              $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
            }
          }
        
          $po_generator = new Gettext\Generator\PoGenerator();
          $po_generator->generateFile($data, $path);

          $path_mo = substr($path, 0, -3) . '.mo';
          $mo_generator = new Gettext\Generator\MoGenerator();
          $mo_generator->generateFile($data, $path_mo);
        }        
        
        osc_add_flash_ok_message(_m('Translations has been updated'), 'admin');

        $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        break;

      case('update_from_source'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
        }
        
        osc_csrf_check();

        $keywords = $this->get_keywords(Params::getParam('type'), Params::getParam('section'));
        //$base_path = $this->get_base_path(Params::getParam('type'), Params::getParam('section'));
        //$include_paths = $this->get_include_paths(Params::getParam('type'), Params::getParam('section'));
        //$exclude_paths = $this->get_exclude_paths(Params::getParam('type'), Params::getParam('section'));
        $domain = $this->get_domain(Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));
        $path = $this->get_path(Params::getParam('language'), Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));
        $file_paths = $this->get_file_paths(Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));
        $excluded_file_paths = $this->get_excluded_file_paths(Params::getParam('type'), Params::getParam('section'));

        $scan_paths = array();
        foreach($file_paths as $p) {
          if($p != '.') {
            $scan_paths[] = osc_base_path() . $p;
          } else {
            $scan_paths[] = osc_base_path();
          }
        }
        
        $scan_paths = array_unique(array_filter($scan_paths));

        $translations = Gettext\Translations::create($domain);
        $translations->setLanguage(Params::getParam('language'));

        $scanner = new Gettext\Scanner\PhpScanner($translations);
        $scanner->setDefaultDomain($domain);
        
        $functions = array();
        foreach($keywords as $k) {
          $functions[$k] = 'gettext';
        }
        
        $scanner->setFunctions($functions);
        $scanner->ignoreInvalidFunctions(true);
        
        // Recursively scan paths those are not excluded
        $this->scan_paths($scanner, $scan_paths, $excluded_file_paths);

        // Generate folders those are missing
        $folder_check = $this->generate_folders($path);
        if($folder_check !== true) {
          osc_add_flash_error_message(sprintf(_m('Required and missing folder %s could not be created. Create it manually in your file system.'), $folder_check), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        }
        
        $data = $scanner->getTranslations();
        $data = $data[$domain];
        
        $data = $this->set_headers($data, Params::getParam('language'), Params::getParam('type'), Params::getParam('section'));
        
        // Check if file exists and copy existing translations
        $is_new = false;
        if(file_exists($path)) {
          $loader = new Gettext\Loader\PoLoader();
          $original_data = $loader->loadFile($path);
          $original_translations = $original_data->getTranslations();

          if(is_array($original_translations) && count($original_translations) > 0) {
            foreach($original_translations as $key => $value) {
              $translation = $data->find(null, $value->getOriginal());
              
              if($translation) {
                if($value->getTranslation() != '') {
                  $translation->translate($value->getTranslation());
                }
              }
            }
          }
        } else {
          $is_new = true; 
        }


        $po_generator = new Gettext\Generator\PoGenerator();
        $po_generator->generateFile($data, $path);

        $path_mo = substr($path, 0, -3) . '.mo';
        $mo_generator = new Gettext\Generator\MoGenerator();
        $mo_generator->generateFile($data, $path_mo);

        if($is_new) {
          osc_add_flash_ok_message(_m('Translations has been successfully created from source code.'), 'admin');
        } else {
          osc_add_flash_ok_message(_m('Translations has been successfully updated from source code.'), 'admin');
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        break;
        
      case('download'):
        osc_csrf_check();
      
        $path = $this->get_path(Params::getParam('language'), Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));
        
        if(!file_exists($path)) {
          osc_add_flash_error_message(_m('Translations does not exists.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        }
        
        $dir_path = dirname($path);
        $dir_name = basename(dirname($path));
        
        $zip_name = $this->generate_zip_name(Params::getParam('language'), Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));
        $zip_path = osc_uploads_path() . 'temp/' . $zip_name;

        if($this->create_zip_archive($zip_path, $dir_path, $dir_name) === false) {
          osc_add_flash_error_message(_m('ZIP archive could not be created.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . basename($zip_path));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zip_path));
        ob_clean();
        flush();
        readfile($zip_path);
        @unlink($zip_path);
        exit;
        break;
        
      case('send'):
        osc_csrf_check();
      
        $path = $this->get_path(Params::getParam('language'), Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));
        
        if(!file_exists($path)) {
          osc_add_flash_error_message(_m('Translations does not exists.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        }
        
        $dir_path = dirname($path);
        $dir_name = basename(dirname($path));
        
        $zip_name = $this->generate_zip_name(Params::getParam('language'), Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));
        $zip_path = osc_uploads_path() . 'temp/' . $zip_name;

        if($this->create_zip_archive($zip_path, $dir_path, $dir_name) === false) {
          osc_add_flash_error_message(_m('ZIP archive could not be created.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        }


        $url = osc_share_translation_url(Params::getParam('language'), Params::getParam('type'), Params::getParam('plugin'), Params::getParam('theme'));
        
        if(testCurl()) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 10000);
          curl_setopt($ch, CURLOPT_USERAGENT, Params::getServerParam('HTTP_USER_AGENT') . ' Osclass (v.' . osc_version() . ')');

          if(!defined('CURLOPT_RETURNTRANSFER')) {
            define('CURLOPT_RETURNTRANSFER', 1);
          }
          
          @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
          curl_setopt($ch, CURLOPT_REFERER, osc_base_url());
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          
          if(stripos($url, 'https') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
          }

          $post = array(
            'domain' => osc_get_parent_domain(),
            'site_email' => osc_contact_email(),
            'admin_email' => osc_logged_admin_email(),
            'file_name' => basename($zip_path),
            'file_size' => filesize($zip_path),
            'file' => curl_file_create($zip_path)
          );
          
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

          $response = curl_exec($ch);
          @unlink($zip_path);
          
          if($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            osc_add_flash_error_message(sprintf(_m('There was problem sending translation (cURL issue): [%s] %s'), $errno, $error_message), 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
          }
          
          curl_close($ch);

          $response = json_decode($response, true);
          
          if(isset($response['error']) && $response['error'] != '') {
            osc_add_flash_error_message(sprintf(_m('Translation was not accepted with following error: %s'), $response['error']), 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
          }
          

        } else {
          osc_add_flash_error_message(_m('Your server does not have cURL extension activated.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        }
        
        osc_add_flash_ok_message(_m('Translation successfully sent to Osclass Team and is pending validation. Thanks for sharing and helping community!'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));

        break;

      case('remove'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
        }
        
        osc_csrf_check();
      
        $path = $this->get_path(Params::getParam('language'), Params::getParam('type'), Params::getParam('section'), Params::getParam('plugin'), Params::getParam('theme'));
        
        if(!file_exists($path)) {
          osc_add_flash_error_message(_m('Translations does not exists.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        }
        
        $dir_path = dirname($path);
        
        osc_deleteDir($dir_path);
        @unlink($dir_path);
        
        osc_add_flash_ok_message(_m('Translation successfully removed'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'));
        break;
        
      case('copy'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
        }
        
        osc_csrf_check();

        $source_path = $this->get_path(Params::getParam('source_language'), Params::getParam('source_type'), Params::getParam('source_section'), Params::getParam('source_plugin'), Params::getParam('source_theme'));
        $target_path = $this->get_path(Params::getParam('target_language'), Params::getParam('target_type'), Params::getParam('target_section'), Params::getParam('target_plugin'), Params::getParam('target_theme'));
        
        $loader = new Gettext\Loader\PoLoader();

        if(1==2 && $source_path == $target_path) {
          osc_add_flash_error_message(_m('Source translations catalog is same as target translations catalog.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
        }
        
        if(file_exists($source_path)) {
          $source_data = $loader->loadFile($source_path);
        } else {
          osc_add_flash_error_message(_m('Source translations catalog does not exists.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
         }
        
        if(file_exists($target_path)) {
          $target_data = $loader->loadFile($target_path);
        } else {
          osc_add_flash_error_message(_m('Target translations catalog does not exists.'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
        }
        
        // $source_translations = $source_data->getTranslations();
        // $target_translations = $target_data->getTranslations();
        
        $merged_data = $target_data->mergeWith($source_data);
        $merged_data = $this->set_headers($merged_data, Params::getParam('target_language'), Params::getParam('target_type'), Params::getParam('target_section'));

        // Generate folders those are missing
        if(!file_exists($target_path)) {
          $folder_check = $this->generate_folders($target_path);
          if($folder_check !== true) {
            osc_add_flash_error_message(sprintf(_m('Required and missing folder %s could not be created. Create it manually in your file system.'), $folder_check), 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=translations');
          }
        }
      
        $po_generator = new Gettext\Generator\PoGenerator();
        $po_generator->generateFile($merged_data, $target_path);

        $path_mo = substr($target_path, 0, -3) . '.mo';
        $mo_generator = new Gettext\Generator\MoGenerator();
        $mo_generator->generateFile($merged_data, $path_mo);
        
        osc_add_flash_ok_message(_m('Target translations catalog has been merged with translations from source catalog'), 'admin');

        $this->redirectTo(osc_admin_base_url(true) . '?page=translations&action=edit&language=' . Params::getParam('target_language') . '&type=' . Params::getParam('target_type') . '&section=' . Params::getParam('target_section') . '&theme=' . Params::getParam('target_theme') . '&plugin=' . Params::getParam('target_plugin'));
        break;
        
      default:
        $this->_exportVariableToView('plugins', Plugins::listAll());
        $this->_exportVariableToView('themes', WebThemes::newInstance()->getListThemes());
        $this->_exportVariableToView('languages', OSCLocale::newInstance()->listAll());
        
        $this->_exportVariableToView('core_translations', $this->get_translations(osc_base_path() . OC_CONTENT_FOLDER . '/languages/'));
        $this->_exportVariableToView('backoffice_translations', $this->get_translations(osc_base_path() . OC_ADMIN_FOLDER . '/themes/' . AdminThemes::newInstance()->getCurrentTheme() . '/languages/'));
        $this->_exportVariableToView('themes_translations', $this->get_translations(osc_base_path() . OC_CONTENT_FOLDER . '/themes/*/languages/'));
        $this->_exportVariableToView('plugins_translations', $this->get_translations(osc_base_path() . OC_CONTENT_FOLDER . '/plugins/*/languages/'));

        $this->doView('translations/index.php');
        break;

    }
  }

  //hopefully generic...
  function doView($file) {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }
  
  // Get keywords
  function get_keywords($type, $section = '') {
    $keywords = array('__','_e','_m','_n','_mn');

    if($type == 'CORE') {
      if($section == 'CORE') {
        $keywords = array('__', '_e');
      } else if($section == 'MESSAGES') {
        $keywords = array('_n', '_m', '_mn');
      } else if($section == 'THEME') {
        $keywords = array('__', '_e');
      }
    }
    
    return $keywords;
  }
  
  // Get base path
  function get_base_path($type, $section = '') {
    $base_path = '../..';

    if($type == 'CORE') {
      if($section == 'CORE') {
        $base_path = '../../..';
      } else if($section == 'MESSAGES') {
        $base_path = '../../..';
      } else if($section == 'THEME') {
        $base_path = '../../..';
      }
    }
    
    return $base_path;
  }
  
  // Get include paths
  function get_include_paths($type, $section = '') {
    $include_paths = array('.');

    if($type == 'CORE') {
      if($section == 'CORE') {
        $include_paths = array(OC_INCLUDES_FOLDER . '/osclass', 'oc-admin');
      } else if($section == 'THEME') {
        $include_paths = array(OC_INCLUDES_FOLDER . '/osclass/gui', OC_CONTENT_FOLDER . '/themes/sigma');
      }
    }
    
    return $include_paths;
  }
  
  // Get exclude paths
  function get_exclude_paths($type, $section = '') {
    $exclude_paths = array();

    if($type == 'CORE') {
      if($section == 'CORE') {
        $exclude_paths = array(OC_INCLUDES_FOLDER . '/osclass/assets', OC_INCLUDES_FOLDER . '/osclass/gui', OC_CONTENT_FOLDER);
      } else if($section == 'MESSAGES') {
        $exclude_paths = array(OC_INCLUDES_FOLDER . '/vendor', OC_INCLUDES_FOLDER . '/images', OC_INCLUDES_FOLDER . '/osclass/assets');
      }
    }
    
    return $exclude_paths;
  }
  
  // Get domain
  function get_domain($type, $section = '', $plugin = '', $theme = '') {
    $domain = '';
    
    if($type == 'CORE') {
      if($section == 'CORE') {
        $domain = 'core';
        
      } else if($section == 'MESSAGES') {
        $domain = 'messages';
        
      } else if($section == 'THEME') {
        $domain = 'sigma';
      }
    } else if($type == 'ADMIN') {
      $domain = AdminThemes::newInstance()->getCurrentTheme();
    } else if($type == 'PLUGIN') {
      $domain = $plugin;
    } else if($type == 'THEME') {
      $domain = $theme;
    }
    
    return $domain;
  }
  
  // Get path
  function get_path($language, $type, $section = '', $plugin = '', $theme = '') {
    if($type == 'CORE') {
      $path = osc_translations_path() . $language . '/';
      
      if($section == 'CORE') {
        $path .= 'core';
      } else if($section == 'MESSAGES') {
        $path .= 'messages';
      } else if($section == 'THEME') {
        $path .= 'theme'; 
      }
    } else if($type == 'ADMIN') {
      $path = osc_admin_base_path() . 'themes/' . AdminThemes::newInstance()->getCurrentTheme() . '/languages/' . $language . '/messages';
    } else if($type == 'PLUGIN') {
      $path = osc_plugins_path() . $plugin . '/languages/' . $language . '/messages';
    } else if($type == 'THEME') {
      $path = osc_themes_path() . $theme . '/languages/' . $language . '/theme';
    }
    
    $path .= '.po';

    return $path;
  }
  
  // Get translation file paths
  function get_file_paths($type, $section = '', $plugin = '', $theme = '') {
    $file_paths = array('.');

    if($type == 'CORE') {
      if($section == 'THEME') {
        $file_paths = array(OC_INCLUDES_FOLDER . '/osclass/gui/', OC_CONTENT_FOLDER . '/themes/sigma/');
      }
    } else if($type == 'ADMIN') {
      $file_paths = array(OC_ADMIN_FOLDER . '/themes/' . AdminThemes::newInstance()->getCurrentTheme() . '/');
      
    } else if($type == 'PLUGIN') {
      $file_paths = array(OC_CONTENT_FOLDER . '/plugins/' . $plugin . '/');
      
    } else if($type == 'THEME') {
      $file_paths = array(OC_CONTENT_FOLDER . '/themes/' . $theme . '/');
      
      $child_check = explode('_', $theme);
      if(isset($child_check[1]) && $child_check[1] == 'child' && $child_check[0] != '') {
        $file_paths[] = OC_CONTENT_FOLDER . '/themes/' . $child_check[0] . '/';
      }
    }
    
    return $file_paths;
  }
  
  // Get translation file paths to exclude
  function get_excluded_file_paths($type, $section = '') {
    $exclude_paths = array();

    if($type == 'CORE') {
      if($section == 'CORE') {
        $exclude_paths = array(OC_INCLUDES_FOLDER . '/osclass/assets/', OC_INCLUDES_FOLDER . '/osclass/gui/', OC_CONTENT_FOLDER . '/');
      } else if ($section == 'MESSAGES') {
        $exclude_paths = array(OC_INCLUDES_FOLDER . '/vendor/', OC_INCLUDES_FOLDER . '/images/', OC_INCLUDES_FOLDER . '/osclass/assets/', OC_CONTENT_FOLDER . '/');
      }
    } 
    
    return $exclude_paths;
  }
  
  // Generate zip name
  function generate_zip_name($language, $type, $section = '', $plugin = '', $theme = '') {
    $name = date('Ymd') . '_lang_';

    if($type == 'CORE') {
      $name .= 'osclass';
    } else if($type == 'ADMIN') {
      $name .= str_replace('_', '-', AdminThemes::newInstance()->getCurrentTheme());
    } else if($type == 'PLUGIN') {
      $name .= str_replace('_', '-', $plugin);
    } else if($type == 'THEME') {
      $name .= str_replace('_', '-', $theme);
    }

    $name .= '_' . $language . '_';

    if($type == 'CORE') {
      $name .= osc_version(true);
    } else if($type == 'ADMIN') {
      $info = AdminThemes::newInstance()->loadThemeInfo(AdminThemes::newInstance()->getCurrentTheme());

      if(isset($info['version']) && $info['version'] != '') {
        $name .= $info['version'];
      } else {
        $name .= '1.0.0';
      }
    } else if($type == 'PLUGIN') {
      $info = osc_plugin_get_info($plugin . '/index.php'); 

      if(isset($info['version']) && $info['version'] != '') {
        $name .= $info['version'];
      } else {
        $name .= '1.0.0';
      }      
    } else if($type == 'THEME') {
      $info = WebThemes::newInstance()->loadThemeInfo($theme);

      if(isset($info['version']) && $info['version'] != '') {
        $name .= $info['version'];
      } else {
        $name .= '1.0.0';
      }
    }
    
    $name .= '.zip';

    return $name;
  }
  
  // Create ZIP archive
  function create_zip_archive($zip_path, $dir_path, $dir_name) {
    $zip = new ZipArchive;
    $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    if ($handle = opendir($dir_path)) {
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
          $zip->addFile($dir_path . '/' . $file, $dir_name . '/' . $file);
        }
      }
      
      closedir($handle);
    }

    return $zip->close();
  }
  
  // scan paths
  function scan_paths($scanner, $paths, $exclude_paths = array()) {
    if(is_array($paths) && count($paths) > 0) {
      foreach($paths as $path) {
        $is_excluded = false;
        if(is_array($exclude_paths) && count($exclude_paths) > 0) {
          foreach($exclude_paths as $excluded) {
            if(strpos($path, $excluded) !== false) {
              $is_excluded = true;
              break;
            }
          }
        }
        
        if(!$is_excluded) {
          $files = glob($path . '*.php');
          
          if(is_array($files) && count($files) > 0) {
            foreach($files as $file) {
              if(is_file($file)) {
                // In order not to have absolute paths as references in PO file, we replace it with relative paths
                $relative_file = '../' . str_replace(osc_base_path(), '', $file);  
                $scanner->scanFile($relative_file);
              }
            }
          }
          
          $folders = glob($path . '*/', GLOB_ONLYDIR);
          $this->scan_paths($scanner, $folders, $exclude_paths);
        }
      }
    }
  }
  
  // Set headers to PO file
  function set_headers($data, $language, $type, $section = '') {
    $data->getHeaders()->set('Language', $language);
    $data->getHeaders()->set('Language-Team', 'Osclass Core');
    $data->getHeaders()->set('Last-Translator', osc_logged_admin_name() . ' <' . osc_logged_admin_email() . '>');
    $data->getHeaders()->set('Content-Type', 'text/plain; charset=UTF-8');
    $data->getHeaders()->set('Content-Transfer-Encoding', '8bit');
    $data->getHeaders()->set('PO-Revision-Date', date('Y-m-d H:i:s'));
    $data->getHeaders()->set('X-Generator', 'Osclass Core v' . OSCLASS_VERSION . ' & PHP Gettext');
    $data->getHeaders()->set('X-Poedit-SourceCharset', 'UTF-8');

    $keywords = $this->get_keywords($type, $section);
    $base_path = $this->get_base_path($type, $section);
    $include_paths = $this->get_include_paths($type, $section);
    $exclude_paths = $this->get_exclude_paths($type, $section);
    
    $data->getHeaders()->set('X-Poedit-KeywordsList', implode(';', $keywords));
    $data->getHeaders()->set('X-Poedit-Basepath', $base_path);

    $i = 0;
    foreach($include_paths as $p) {
      $data->getHeaders()->set('X-Poedit-SearchPath-' . $i, $p);
      $i++;
    }

    $i = 0;
    foreach($exclude_paths as $p) {
      $data->getHeaders()->set('X-Poedit-SearchPathExcluded-' . $i, $p);
      $i++;
    } 
    
    return $data;
  }
  
  // Get existing translations
  function get_translations($path) {
    $output = array();
    $loader = new Gettext\Loader\PoLoader();

    $dirs = glob($path . '*', GLOB_ONLYDIR);
    if(is_array($dirs) && count($dirs) > 0) {
      foreach($dirs as $dir) {
        $files = glob($dir . '/*.po');

        $strings_counter = 0;
        
        if(count($files) > 0) {
          foreach($files as $file) {
            $translations = $loader->loadFile($file);
            $strings_counter += count($translations);
          }
        }
        
        $code = basename($dir);
        $locale = $this->find_locale($code);
        
        $output[] = array(
          'path' => $dir,
          'dir' => $code,
          'files' => $files,
          'count' => count($files),
          'exists' => (count($files) > 0 ? true : false),
          'strings' => $strings_counter,
          'subject' => basename(dirname(dirname($dir))),
          'language' => $code,
          'language_name' => ($locale !== false ? $locale['s_name'] : $code)
        );
      }
    }

    return $output;
  }
  
  // Find locale
  function find_locale($code) {
    $locales = __get('languages');
    
    if(empty($locales) || count($locales) <= 0) {
      $locales = OSCLocale::newInstance()->listAll();
    }

    $key = array_search($code, array_column($locales, 'pk_c_code'));

    if($key !== false) {
      return $locales[$key];
    }
    
    return false;
  }
  
  // Get market search url
  function market_search_url($language, $type, $section = '', $plugin = '', $theme = '') {
    $url = osc_admin_base_url(true) . '?page=market&action=languages';
    
    if($type == 'CORE') {
      $url .= '&pattern=' . $language;
    } else if ($type == 'THEME' || $type == 'ADMIN') {
      $url .= '-themes&pattern=' . $theme;
    } else if ($type == 'PLUGIN') {
      $url .= '-plugins&pattern=' . $plugin; 
    }
    
    return $url;
  }
  
  // Generate folders to path
  function generate_folders($path) {
    $dir = dirname($path);   // from file to it's dir
    
    $dir = str_replace(osc_base_path(), '', $dir);
    $folders = array_values(array_filter(explode('/', $dir)));

    $check_path = osc_base_path();
    foreach($folders as $folder) {
      $check_path .= $folder . '/';
      
      if(!file_exists($check_path)) {
        if(!@mkdir($check_path, 0755, true)) {
          return $check_path;
        }
      }
    }
    
    return true;  
  }
  
}

/* file end: ./oc-admin/translations.php */
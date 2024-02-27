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

if(!defined('OSCLASS_VERSION')){
  define('OSCLASS_VERSION', '8.1.1');
} 

if(!defined('OC_ADMIN')) {
  define('OC_ADMIN', false);
}

if(!defined('OC_ADMIN_FOLDER')) {
  define('OC_ADMIN_FOLDER', 'oc-admin');
}

if(!defined('OC_INCLUDES_FOLDER')) {
  define('OC_INCLUDES_FOLDER', 'oc-includes');
}

if(!defined('OC_CONTENT_FOLDER')) {
  define('OC_CONTENT_FOLDER', 'oc-content');
}

if(!defined('LIB_PATH')) {
  define('LIB_PATH', ABS_PATH . OC_INCLUDES_FOLDER .'/');
}

if(!defined('CONTENT_PATH')) {
  define('CONTENT_PATH', ABS_PATH . OC_CONTENT_FOLDER . '/');
}

if(!defined('WEB_PATH')) {  // this is needed during installation only, as then WEB_PATH is defined in config.php
  $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
  define('WEB_PATH', $protocol . $_SERVER['HTTP_HOST'] . '/');
}

if(!defined('LIB_WEB_PATH')) {
  define('LIB_WEB_PATH', WEB_PATH . OC_INCLUDES_FOLDER . '/');
}

if(!defined('CONTENT_WEB_PATH')) {
  define('CONTENT_WEB_PATH', WEB_PATH . OC_CONTENT_FOLDER . '/');
}

if(!defined('THEMES_PATH')) {
  define('THEMES_PATH', CONTENT_PATH . 'themes/');
}

if(!defined('THEMES_WEB_PATH')) {
  define('THEMES_WEB_PATH', CONTENT_WEB_PATH . 'themes/');
}

if(!defined('PLUGINS_PATH')) {
  define('PLUGINS_PATH', CONTENT_PATH . 'plugins/');
}

if(!defined('PLUGINS_WEB_PATH')) {
  define('PLUGINS_WEB_PATH', CONTENT_WEB_PATH . 'plugins/');
}

if(!defined('TRANSLATIONS_PATH')) {
  define('TRANSLATIONS_PATH', CONTENT_PATH . 'languages/');
}

if(!defined('TRANSLATIONS_WEB_PATH')) {
  define('TRANSLATIONS_WEB_PATH', CONTENT_WEB_PATH . 'languages/');
}

if(!defined('UPLOADS_PATH')) {
  define('UPLOADS_PATH', CONTENT_PATH . 'uploads/');
}

if(!defined('UPLOADS_WEB_PATH')) {
  define('UPLOADS_WEB_PATH', CONTENT_WEB_PATH . 'uploads/');
}

if(!defined('OSC_DEBUG_DB')) {
  define('OSC_DEBUG_DB', false);
}

if(!defined('OSC_DEBUG_DB_LOG')) {
  define('OSC_DEBUG_DB_LOG', false);
}

if(!defined('OSC_DEBUG_DB_EXPLAIN')) {
  define('OSC_DEBUG_DB_EXPLAIN', false);
}

if(!defined('OSC_DEBUG')) {
  define('OSC_DEBUG', false);
}

if(!defined('OSC_DEBUG_LOG')) {
  define('OSC_DEBUG_LOG', false);
}

if(!defined('OSC_MEMORY_LIMIT')) {
  define('OSC_MEMORY_LIMIT', '64M');
}

if(function_exists('memory_get_usage') && ((int)@ini_get('memory_limit') < abs((int)OSC_MEMORY_LIMIT))) {
  @ini_set('memory_limit', OSC_MEMORY_LIMIT);
}

if(!defined('CLI')) {
  define('CLI', false);
}

if(!defined('OSC_CACHE_TTL')) {
  define('OSC_CACHE_TTL', 60);
}

if(!defined('OSCLASS_AUTHOR')){
  define('OSCLASS_AUTHOR', 'OSCLASSPOINT');
} 

if(!defined('UPGRADE_SKIP_DB')){
  define('UPGRADE_SKIP_DB', true);
} 
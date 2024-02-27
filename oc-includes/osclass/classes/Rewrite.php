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
 * Class Rewrite
 */
class Rewrite {
  private static $instance;
  private $rules;
  private $routes;
  private $request_uri;
  private $raw_request_uri;
  private $uri;
  private $location;
  private $section;
  private $title;
  private $http_referer;

  public function __construct() {
    $this->request_uri = '';
    $this->raw_request_uri = '';
    $this->uri = '';
    $this->location = '';
    $this->section = '';
    $this->title = '';
    $this->http_referer = '';
    $this->routes = array();
    $this->rules = $this->getRules();
  }

  /**
   * @return \Rewrite
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function getTableName() {}

  /**
   * @return array
   */
  public function getRules() {
    return osc_apply_filter('rewrite_rules_array_init', osc_unserialize(osc_rewrite_rules()));
  }

  public function setRules() {
    osc_set_preference('rewrite_rules', osc_apply_filter('rewrite_rules_array_save', osc_serialize($this->rules)));
  }

  /**
   * @return array
   */
  public function listRules() {
    return $this->rules;
  }

  /**
   * @param $rules
   */
  public function addRules($rules) {
    if(is_array($rules)) {
      foreach($rules as $rule) {
        if(is_array($rule) && count($rule)>1) {
          $this->addRule($rule[0], $rule[1]);
        }
      }
    }
  }

  /**
   * @param $regexp
   * @param $uri
   */
  public function addRule($regexp, $uri) {
    $regexp = trim($regexp);
    $uri = trim($uri);
    if($regexp != '' && $uri != '' && !in_array($regexp , $this->rules)) {
    $this->rules[ $regexp ] = $uri;
    }
  }

  /**
   * @param    $id
   * @param    $regexp
   * @param    $url
   * @param    $file
   * @param bool   $user_menu
   * @param string $location
   * @param string $section
   * @param string $title
   */
  public function addRoute($id , $regexp , $url , $file , $user_menu = false , $location = 'custom' , $section = 'custom' , $title = 'Custom') {
    $regexp = trim($regexp);
    $file = trim($file);
    
    if($regexp!='' && $file!='') {
      $this->routes[$id] = array('regexp' => $regexp, 'url' => $url, 'file' => $file, 'user_menu' => $user_menu, 'location' => $location, 'section' => $section, 'title' => $title);
    }
  }

  /**
   * @return array
   */
  public function getRoutes() {
    return osc_apply_filter('rewrite_routes_array_get', $this->routes);
  }

  /**
   * Initialize Rewrite rules
   */
  public function init() {
    if(Params::existServerParam('REQUEST_URI')) {
      if(preg_match('|[\?&]{1}http_referer=(.*)$|', urldecode(Params::getServerParam('REQUEST_URI', false, false)), $ref_match)) {
        $this->http_referer = $ref_match[1];
        $_SERVER['REQUEST_URI'] = preg_replace('|[\?&]{1}http_referer=(.*)$|', '' , urldecode(Params::getServerParam('REQUEST_URI', false, false)));
      }
      
      $request_uri = preg_replace('@^' . REL_WEB_URL . '@', '' , Params::getServerParam('REQUEST_URI', false, false));
      //$lang_slug = (osc_locale_to_base_url_enabled() ? osc_base_url_locale_slug() . '/' : '');   // os810
      $lang_regex = '';
      
      if(osc_locale_to_base_url_enabled()) {
        $lang_regex = osc_base_url_locale_regex()['regex'];   //ie: ([a-z]{2})   // os810
        $lang_regex .= ($lang_regex <> '' ? '/' : '');
      }
      
      $this->raw_request_uri = $request_uri;
      $route_used = false;

      foreach($this->routes as $id => $route) {
        // UNCOMMENT TO DEBUG
        // echo 'Request URI: '.$request_uri." # Match : ".$route['regexp']." # URI to go : ".$route['url']." <br />";
        if(preg_match('#^' . $lang_regex . $route['regexp'] . '#', $request_uri, $m) || preg_match('#^' . $route['regexp'] . '#', $request_uri, $m)) { 
          if(!preg_match_all('#\{([^\}]+)\}#', $route['url'], $args)) {
            $args[1] = array();
          }
          
          // Find langauge code in URL
          // If not matching to current user locale, set it!
          preg_match('#^' . $lang_regex . '#', $request_uri, $lreg);
          
          if(count($lreg) > 0) {
            $lang = str_replace('/', '', end($lreg));
            $locale = osc_current_user_locale();

            //if($lang != '' && (preg_match('/.{2}_.{2}/', $lang) && $locale != $lang || preg_match('/.{2}/', $lang) && substr($locale, 0, 2) != $lang)) {
            if($lang != '' && ((preg_match('/[a-z]{2}_[a-zA-Z]{2}/', $lang) || preg_match('/[a-z]{2}-[a-zA-Z]{2}/', $lang)) && $locale != $lang || preg_match('/[a-z]{2}/', $lang) && substr($locale, 0, 2) != $lang)) {
              //if(preg_match('/.{2}_.{2}/', $lang)) {
              if(preg_match('/[a-z]{2}_[a-zA-Z]{2}/', $lang) || preg_match('/[a-z]{2}-[a-zA-Z]{2}/', $lang)) {
                $lang = strtolower(substr($lang, 0, 2)) . '_' . strtoupper(substr($lang, 3, 2));
                Session::newInstance()->_set('userLocale', $lang);
                Translation::init();
              //} else if(preg_match('/.{2}/', $lang)) {
              } else if(preg_match('/[a-z]{2}/', $lang)) {
                $find_lang = OSCLocale::newInstance()->findByShortCode($lang);
                
                if($find_lang !== false && isset($find_lang['pk_c_code']) && $find_lang['pk_c_code'] != '') {
                  Session::newInstance()->_set('userLocale', $find_lang['pk_c_code']);
                  Translation::init();
                }
              }
            }
          }

          
          $l = count($m);
          for($p=1;$p<$l;$p++) {
            if(isset($args[1][$p-1])) {
              Params::setParam($args[1][$p-1], $m[$p]);
            } else {
              Params::setParam('route_param_'.$p, $m[$p]);
            }
          }
          
          // if(osc_locale_to_base_url_enabled()) {
            // Params::setParam('lang', osc_base_url_locale_slug());
          // }

          Params::setParam('page', 'custom');
          Params::setParam('route', $id);
          $route_used = true;
          
          $this->location = $route['location'];
          $this->section = $route['section'];
          $this->title = $route['title'];
          break;
        }
      }
      
     
      if(!$route_used) {
        // if(1==2 && osc_locale_to_base_url_enabled()) {
          // Check if URL points to static content (js, css, jpg, gif, ...), if yes, remove lang param from URL and redirect
          //if(preg_match('#^(.+?)\.js(.*)$#', $request_uri)) {
          // if(preg_match('#^(.+?)\.([0-9A-Za-z]{2,5})(.*)$#', $request_uri)) {
            // if(substr($request_uri, 0, strlen(osc_base_url_locale_slug())) === osc_base_url_locale_slug()) {
              // $request_uri = osc_base_url(false, false) . substr($request_uri, strlen(osc_base_url_locale_slug())+1);
              // header('Location:' . $request_uri);
              // exit;
            // }
          // }
        // }
        
        if(osc_rewrite_enabled()) {
          $tmp_ar = explode('?' , $request_uri);
          $request_uri = $tmp_ar[0];

          // if try to access directly to a php file
          if(preg_match('#^(.+?)\.php(.*)$#', $request_uri)) {
            $file = explode('?' , $request_uri);
            if(!file_exists(ABS_PATH . $file[0])) {
              self::newInstance()->set_location('error');
              header('HTTP/1.1 404 Not Found');
              osc_current_web_theme_path('404.php');
              exit;
            }
          }

          foreach($this->rules as $match => $uri) {
            // UNCOMMENT TO DEBUG
            // echo 'Request URI: '.$request_uri." # Match : ".$match." # URI to go : ".$uri." <br />";

            if(preg_match('#^'.$match.'#', $request_uri, $m)) {
              $request_uri = preg_replace('#'.$match.'#', $uri, $request_uri);

              // UNCOMMENT TO DEBUG
              // echo 'Matched rule: "'.$match.'" ... Request URI: "' . $request_uri . '"<br />';

              //if($match == '^([a-z]{2})/([^.]+\.(jpe?g|gif|bmp|png|tiff|avif|webp|js|css|min\.js|min\.css))$') {
              //if($match == '^([a-z]{2})/([^.]+\.([0-9A-Za-z]{2,5}))$') {
              // if($match == '^(.+?)\.([0-9A-Za-z]{2,5})(.*)$') {
               // osc_redirect_to($request_uri);
               // exit; 
              // }
              
              break;
            }
          }

          $this->extractParams($request_uri);
        }
        
        $this->request_uri = $request_uri;

        if(Params::getParam('page')!='') { $this->location = Params::getParam('page'); }
        if(Params::getParam('action')!='') { $this->section = Params::getParam('action'); }
      }
    }
  }

  /**
   * @param string $uri
   *
   * @return bool|string
   */
  public function extractURL($uri = '') {
    $uri_array = explode('?', str_replace('index.php', '', $uri));
    if($uri_array[ 0 ][ 0 ] === '/') {
      return substr($uri_array[0], 1);
    } else {
      return $uri_array[0];
    }
  }

  /**
   * @param string $uri
   */
  public function extractParams($uri = '') {
    $uri_array = explode('?', $uri);
    $length_i = count($uri_array);
    
    for($var_i = 1;$var_i<$length_i;$var_i++) {
      parse_str($uri_array[$var_i], $parsedVars);
      foreach($parsedVars as $k => $v) {
        Params::setParam($k, urldecode($v));
      }
    }
  }

  /**
   * @param $regexp
   */
  public function removeRule($regexp) {
    unset($this->rules[$regexp]);
  }

  public function clearRules() {
    unset($this->rules);
    $this->rules = array();
  }

  /**
   * @return string
   */
  public function get_request_uri() {
    return $this->request_uri;
  }

  /**
   * @return string
   */
  public function get_raw_request_uri() {
    return $this->raw_request_uri;
  }

  /**
   * @param $location
   */
  public function set_location($location) {
    $this->location = $location;
  }

  /**
   * @return string
   */
  public function get_location() {
    return $this->location;
  }

  /**
   * @return string
   */
  public function get_section() {
    return $this->section;
  }

  /**
   * @return string
   */
  public function get_title() {
    return $this->title;
  }

  /**
   * @return string
   */
  public function get_http_referer() {
    return $this->http_referer;
  }
}
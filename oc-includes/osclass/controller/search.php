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
 * Class CWebSearch
 */
class CWebSearch extends BaseModel {
  public $mSearch;
  public $uri;

  public function __construct() {
    parent::__construct();

    $this->mSearch = Search::newInstance();
    $lang_slug = strtolower(osc_locale_to_base_url_enabled() ? osc_base_url_locale_slug() . '/' : '');   // os810

    $this->uri = preg_replace('|^' . REL_WEB_URL . '|', '', Params::getServerParam('REQUEST_URI', false, false));
    if(preg_match('/^index\.php/', $this->uri)>0) {
      // search url without permalinks params
    } else {
      $this->uri = preg_replace('|/$|', '', $this->uri);    // search or langCode/search

      // redirect if it ends with a slash NOT NEEDED ANYMORE, SINCE WE CHECK WITH osc_search_url
      $search_tag = strtolower($lang_slug . osc_get_preference('rewrite_search_url'));
      if((strtolower($this->uri) != $search_tag && stripos(strtolower($this->uri), $search_tag . '/')===false) && osc_rewrite_enabled() && !Params::existParam('sFeed')) {
        // clean GET html params
        $this->uri = preg_replace('/(\/?)\?.*$/', '', $this->uri);
        $search_uri = preg_replace('|/[0-9]+$|', '', $this->uri);

        $this->_exportVariableToView('search_uri', $search_uri);

        // get page if it's set in the url
        $iPage = preg_replace('|.*/([0-9]+)$|', '$01', $this->uri);
        if(is_numeric($iPage) && $iPage > 0) {
          Params::setParam('iPage', $iPage);
          // redirect without number of pages
          if($iPage == 1) {
            $this->redirectTo(osc_base_url(false, true) . $search_uri);
          }
        }
        
        if(Params::getParam('iPage') > 1) { 
          $this->_exportVariableToView('canonical', osc_base_url(false, true) . $search_uri);
        }

        // get only the last segment
        $search_uri = preg_replace('|.*?/|', '', $search_uri);
        if(preg_match('|-r([0-9]+)$|', $search_uri, $r)) {
          $region = Region::newInstance()->findByPrimaryKey($r[1]);
          if(!$region) {
            $this->do404();
          }
          
          Params::setParam('sRegion', $region['pk_i_id']);
          
          if(osc_subdomain_type() != 'category') {
            Params::unsetParam('sCategory');
          }
          
          if(preg_match('|(.*?)_.*?-r[0-9]+|', $search_uri, $match)) {
            Params::setParam('sCategory', $match[1]);
          }
          
        } else if(preg_match('|-c([0-9]+)$|', $search_uri, $c)) {
          $city = City::newInstance()->findByPrimaryKey($c[1]);
          if(!$city) {
            $this->do404();
          }
          
          Params::setParam('sCity', $city['pk_i_id']);
          
          if(osc_subdomain_type() != 'category') {
            Params::unsetParam('sCategory');
          }
          
          if(preg_match('|(.*?)_.*?-c[0-9]+|', $search_uri, $match)) {
            Params::setParam('sCategory', $match[1]);
          }
          
        } else {
          if(!Params::existParam('sCategory')) {
            try {
              $category = Category::newInstance()->findBySlug($search_uri);
            } catch (Exception $e) {
            }
            
            if(count($category) === 0) {
              $this->do404();
            }
            
            Params::setParam('sCategory', $search_uri);
            
          } else {
            if (strpos(Params::getParam('sCategory') , '/') !== false) {
              $tmp = explode('/' , preg_replace('|/$|', '', Params::getParam('sCategory')));
              try {
                $category = Category::newInstance()->findBySlug($tmp[count($tmp) - 1]);
              } catch (Exception $e) {
              }
              
              Params::setParam('sCategory', $tmp[count($tmp)-1]);
              
            } else {
              try {
                if(Params::getParam('sCategory') > 0) {
                  $category = Category::newInstance()->findByPrimaryKey(Params::getParam('sCategory'));
                } else {
                  $category = Category::newInstance()->findBySlug(Params::getParam('sCategory'));
                }
              } catch (Exception $e) {
              }
              
              Params::setParam('sCategory', Params::getParam('sCategory'));
            }

            if(is_array($category) && count($category) === 0) { 
              $this->do404();
            }
          }
        }
      }
    }
  }

  //Business Layer...
  public function doModel() {
    osc_run_hook('before_search');

    if(osc_rewrite_enabled()) {
      // IF rewrite is not enabled, skip this part, preg_match is always time&resources consuming task
      $p_sParams = '/' . Params::getParam('sParams', false, false);
      
      if(preg_match_all('|\/([^,]+),([^\/]*)|', $p_sParams, $m)) {
        $l = count($m[0]);
        for($k = 0;$k<$l;$k++) {
          switch($m[1][$k]) {
            case osc_get_preference('rewrite_search_country'):
              $m[1][$k] = 'sCountry';
              break;
            case osc_get_preference('rewrite_search_region'):
              $m[1][$k] = 'sRegion';
              break;
            case osc_get_preference('rewrite_search_city'):
              $m[1][$k] = 'sCity';
              break;
            case osc_get_preference('rewrite_search_city_area'):
              $m[1][$k] = 'sCityArea';
              break;
            case osc_get_preference('rewrite_search_category'):
              $m[1][$k] = 'sCategory';
              break;
            case osc_get_preference('rewrite_search_user'):
              $m[1][$k] = 'sUser';
              break;
            case osc_get_preference('rewrite_search_pattern'):
              $m[1][$k] = 'sPattern';
              break;
              
            default :
              // custom fields
              if(preg_match("/meta(\d+)-?(.*)?/", $m[1][$k], $results)) {
                $meta_key   = $m[1][$k];
                $meta_value = $m[2][$k];
                $array_r  = array();
                if(Params::existParam('meta')) {
                  $array_r = Params::getParam('meta');
                }
                if($results[2]=='') {
                  // meta[meta_id] = meta_value
                  $meta_key = $results[1];
                  $array_r[$meta_key] = $meta_value;
                } else {
                  // meta[meta_id][meta_key] = meta_value
                  $meta_key  = $results[1];
                  $meta_key2 = $results[2];
                  $array_r[$meta_key][$meta_key2]  = $meta_value;
                }
                $m[1][$k] = 'meta';
                $m[2][$k] = $array_r;
              }
              break;
          }

          if($m[1][$k] != 'lang') {
            Params::setParam($m[1][$k], $m[2][$k]);
          }
        }
        
        Params::unsetParam('sParams');
      }
    }

    $uriParams = Params::getParamsAsArray();

    try {
      unset($uriParams['lang']);   // os810
      $searchUri = osc_search_url($uriParams);
    } catch (Exception $e) {
    }

    if($this->uri !== 'feed') {
      $_base_url = WEB_PATH;
      
      // update 420, added strtolower
      // if(urlencode(strip_tags(strtolower(str_replace('+', '', str_replace(' ', '',urldecode($searchUri)))))) != urlencode(strip_tags(strtolower(str_replace(' ', '', urldecode($_base_url . $this->uri)))))) {  // update 420, adding strtolower
      if (strtolower(str_replace('%20' , '+', $searchUri)) != strtolower(str_replace('%20' , '+', $_base_url . $this->uri))) {
        $this->redirectTo($searchUri, 301);
      }
    }
    

    ////////////////////////////////
    //GETTING AND FIXING SENT DATA//
    ////////////////////////////////
    $p_sCategory = Params::getParam('sCategory');
    if(!is_array($p_sCategory)) {
      if($p_sCategory == '') {
        $p_sCategory = array();
      } else {
        $p_sCategory = explode(',' , $p_sCategory);
      }
    }

    $p_sCityArea = Params::getParam('sCityArea');
    if(!is_array($p_sCityArea)) {
      if($p_sCityArea == '') {
        $p_sCityArea = array();
      } else {
        $p_sCityArea = explode(',' , $p_sCityArea);
      }
    }

    $p_sCity = Params::getParam('sCity');
    if(!is_array($p_sCity)) {
      if($p_sCity == '') {
        $p_sCity = array();
      } else {
        $p_sCity = explode(',' , $p_sCity);
      }
    }

    $p_sRegion = Params::getParam('sRegion');
    if(!is_array($p_sRegion)) {
      if($p_sRegion == '') {
        $p_sRegion = array();
      } else {
        $p_sRegion = explode(',' , $p_sRegion);
      }
    }

    $p_sCountry = Params::getParam('sCountry');
    if(!is_array($p_sCountry)) {
      if($p_sCountry == '') {
        $p_sCountry = array();
      } else {
        $p_sCountry = explode(',' , $p_sCountry);
      }
    }

    $p_sUser = Params::getParam('sUser');
    if(!is_array($p_sUser)) {
      if($p_sUser == '') {
        $p_sUser = '';
      } else {
        $p_sUser = explode(',' , $p_sUser);
      }
    }

    $p_sLocale = Params::getParam('sLocale');
    if(!is_array($p_sLocale)) {
      if($p_sLocale == '') {
        $p_sLocale = '';
      } else {
        $p_sLocale = explode(',' , $p_sLocale);
      }
    }

    $p_sPattern = osc_apply_filter('search_pattern', trim(strip_tags(Params::getParam('sPattern'))));


    // ADD TO THE LIST OF LAST SEARCHES
    if(osc_save_latest_searches() && (!Params::existParam('iPage') || Params::getParam('iPage')==1)) {
      $savePattern = osc_apply_filter('save_latest_searches_pattern', $p_sPattern);
      
      if($savePattern != '') {
        LatestSearches::newInstance()->insert(array('s_search' => $savePattern, 'd_date' => date('Y-m-d H:i:s')));
      }
    }

    $p_bPic = Params::getParam('bPic');
    $p_bPic = ($p_bPic == 1) ? 1 : 0;

    $p_bPremium = Params::getParam('bPremium');
    $p_bPremium = ($p_bPremium == 1) ? 1 : 0;

    $p_sPriceMin = Params::getParam('sPriceMin');
    $p_sPriceMax = Params::getParam('sPriceMax');

    //WE CAN ONLY USE THE FIELDS RETURNED BY Search::getAllowedColumnsForSorting()
    $p_sOrder = Params::getParam('sOrder');
    if(!in_array($p_sOrder, Search::getAllowedColumnsForSorting())) {
      $p_sOrder = osc_default_order_field_at_search();
    }
    
    $old_order = $p_sOrder;

    //ONLY 0 (=> 'asc'), 1 (=> 'desc') AS ALLOWED VALUES
    $p_iOrderType = Params::getParam('iOrderType');
    $allowedTypesForSorting = Search::getAllowedTypesForSorting();
    $orderType = osc_default_order_type_at_search();
    
    foreach($allowedTypesForSorting as $k => $v) {
      if($p_iOrderType==$v) {
        $orderType = $k;
        break;
      }
    }
    
    $p_iOrderType = $orderType;

    $p_sFeed = Params::getParam('sFeed');
    $p_iPage = 0;
    
    if(is_numeric(Params::getParam('iPage')) && Params::getParam('iPage') > 0) {
      $p_iPage = (int) Params::getParam('iPage') - 1;
    }

    if($p_sFeed != '') {
      $p_sPageSize = 1000;
    }

    $p_sShowAs = Params::getParam('sShowAs');
    $aValidShowAsValues = array('list', 'gallery');
    
    if (!in_array($p_sShowAs, $aValidShowAsValues)) {
      $p_sShowAs = osc_default_show_as_at_search();
    }

    // search results: it's blocked with the maxResultsPerPage@search defined in t_preferences
    $p_iPageSize = (int) Params::getParam('iPagesize');
    if($p_iPageSize > 0) {
      if ($p_iPageSize > osc_max_results_per_page_at_search()) {
        $p_iPageSize = osc_max_results_per_page_at_search();
      }
    } else {
      $p_iPageSize = osc_default_results_per_page_at_search();
    }

    //FILTERING CATEGORY
    $bAllCategoriesChecked = false;
    $successCat = false;
    
    if(count($p_sCategory) > 0) {
      foreach($p_sCategory as $category) {
        try {
          $successCat = ($this->mSearch->addCategory($category) || $successCat);
        } catch (Exception $e) {
        }
      }
    } else {
      $bAllCategoriesChecked = true;
    }

    //FILTERING CITY_AREA
    foreach($p_sCityArea as $city_area) {
      $this->mSearch->addCityArea($city_area);
    }
    
    $p_sCityArea = implode(', ' , $p_sCityArea);

    //FILTERING CITY
    foreach($p_sCity as $city) {
      $this->mSearch->addCity($city);
    }
    
    $p_sCity = implode(', ' , $p_sCity);

    //FILTERING REGION
    foreach($p_sRegion as $region) {
      $this->mSearch->addRegion($region);
    }
    
    $p_sRegion = implode(', ' , $p_sRegion);

    //FILTERING COUNTRY
    foreach($p_sCountry as $country) {
      $this->mSearch->addCountry($country);
    }
    
    $p_sCountry = implode(', ' , $p_sCountry);

    // FILTERING PATTERN
    if($p_sPattern != '') {
      $this->mSearch->addPattern($p_sPattern);
      $osc_request['sPattern'] = $p_sPattern;
    } else {
      // hardcoded - if there isn't a search pattern, order by dt_pub_date desc
      if($p_sOrder == 'relevance') {
        $p_sOrder = 'dt_pub_date';
        foreach($allowedTypesForSorting as $k => $v) {
          if($p_iOrderType=='desc') {
            $orderType = $k;
            break;
          }
        }
        
        $p_iOrderType = $orderType;
      }
    }

    // FILTERING USER
    if($p_sUser != '') {
      $this->mSearch->fromUser($p_sUser);
    }

    // FILTERING LOCALE
    $this->mSearch->addLocale($p_sLocale);

    // FILTERING IF WE ONLY WANT ITEMS WITH PICS
    if($p_bPic) {
      $this->mSearch->withPicture(true);
    }

    // FILTERING IF WE ONLY WANT PREMIUM ITEMS
    if($p_bPremium) {
      $this->mSearch->onlyPremium(true);
    }

    //FILTERING BY RANGE PRICE
    $this->mSearch->priceRange($p_sPriceMin, $p_sPriceMax);

    //ORDERING THE SEARCH RESULTS
    $this->mSearch->order($p_sOrder, $allowedTypesForSorting[$p_iOrderType]);

    //SET PAGE
    if($p_sFeed == 'rss') {
      // If param sFeed=rss, just output last 'osc_num_rss_items()'
      $this->mSearch->page(0, osc_num_rss_items());
    } else {
      $this->mSearch->page($p_iPage, $p_iPageSize);
    }

    // CUSTOM FIELDS
    $custom_fields = Params::getParam('meta');
    try {
      $fields = Field::newInstance()->findIDSearchableByCategories($p_sCategory);
    } catch (Exception $e) {
    }
    
    $table = DB_TABLE_PREFIX.'t_item_meta';
    if(is_array($custom_fields)) {
      foreach($custom_fields as $key => $aux) {
        if(in_array($key, $fields)) {
          $field = Field::newInstance()->findByPrimaryKey($key);
          switch ($field['e_type']) {
            case 'TEXTAREA':
            case 'TEXT':
            case 'URL':
              if($aux!='') {
                $aux = "%$aux%";
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $str_escaped = Search::newInstance()->dao->escape($aux);
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $table . '.s_value LIKE ' . $str_escaped;
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql.')');
              }
              break;
              
            case 'DROPDOWN':
            case 'RADIO':
              if($aux!='') {
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $str_escaped = Search::newInstance()->dao->escape($aux);
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $table . '.s_value = ' . $str_escaped;
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql.')');
              }
              break;
              
            case 'CHECKBOX':
              if($aux!='') {
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $table . '.s_value = 1';
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql.')');
              }
              break;
              
            case 'DATE':
              if($aux!='') {
                $y = (int)date('Y', $aux);
                $m = (int)date('n', $aux);
                $d = (int)date('j', $aux);
                $start = mktime('0', '0', '0', $m, $d, $y);
                $end = mktime('23', '59', '59', $m, $d, $y);
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $table . '.s_value >= ' . $start . ' AND ';
                $sql .= $table . '.s_value <= ' . $end;
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql.')');
              }
              break;
              
            case 'DATEINTERVAL':
              if(is_array($aux) && (!empty($aux['from']) && !empty($aux['to']))) {
                $from = $aux['from'];
                $to = $aux['to'];
                $start = $from;
                $end = $to;
                $sql = "SELECT fk_i_item_id FROM $table WHERE ";
                $sql .= $table.'.fk_i_field_id = '.$key.' AND ';
                $sql .= $start . ' >= ' . $table . ".s_value AND s_multi = 'from'";
                $sql1 = "SELECT fk_i_item_id FROM $table WHERE ";
                $sql1 .= $table . '.fk_i_field_id = ' . $key . ' AND ';
                $sql1 .= $end . ' <= ' . $table . ".s_value AND s_multi = 'to'";
                $sql_interval = 'select a.fk_i_item_id from (' . $sql . ') a where a.fk_i_item_id IN (' . $sql1 . ')';
                $this->mSearch->addConditions(DB_TABLE_PREFIX.'t_item.pk_i_id IN ('.$sql_interval.')');
              }
              break;
              
            default:
              break;
          }

        }
      }
    }

    osc_run_hook('search_conditions', Params::getParamsAsArray());

    // RETRIEVE ITEMS AND TOTAL
    $key = md5(osc_base_url().$this->mSearch->toJson(false, false));
    $found = null;
    try {
      $cache = osc_cache_get($key , $found);
    } catch (Exception $e) {
    }

    $aItems = null;
    $iTotalItems = null;
    
    if($cache) {
      $aItems = $cache['aItems'];
      $iTotalItems = $cache['iTotalItems'];
    } else {
      $aItems = $this->mSearch->doSearch();
      $iTotalItems = $this->mSearch->count();
      $_cache['aItems'] = $aItems;
      $_cache['iTotalItems'] = $iTotalItems;
      try {
        osc_cache_set($key , $_cache , OSC_CACHE_TTL);
      } catch (Exception $e) {
      }
    }
    
    $aItems = osc_apply_filter('pre_show_items', $aItems);

    $iStart = $p_iPage * $p_iPageSize;
    $iEnd = min(($p_iPage+1) * $p_iPageSize, $iTotalItems);
    $iNumPages = ceil($iTotalItems / $p_iPageSize);

    // works with cache enabled ?
    osc_run_hook('search', $this->mSearch);

    //preparing variables...
    $countryName = $p_sCountry;
    if(strlen($p_sCountry)==2) {
      $c = Country::newInstance()->findByCode($p_sCountry);
      if($c) {
        $countryName = $c['s_name'];

        if(osc_get_current_user_locations_native() == 1) {
          if($c['s_name_native'] <> '') {
            $countryName = $c['s_name_native'];
          }
        }
      }
    }
    
    $regionName = $p_sRegion;
    if(is_numeric($p_sRegion)) {
      $r = Region::newInstance()->findByPrimaryKey($p_sRegion);
      if($r) {
        $regionName = $r['s_name'];

        if(osc_get_current_user_locations_native() == 1) {
          if($r['s_name_native'] <> '') {
            $regionName = $r['s_name_native'];
          }
        }
      }
    }
    
    $cityName = $p_sCity;
    if(is_numeric($p_sCity)) {
      $c = City::newInstance()->findByPrimaryKey($p_sCity);
      if($c) {
        $cityName = $c['s_name'];

        if(osc_get_current_user_locations_native() == 1) {
          if($c['s_name_native'] <> '') {
            $cityName = $c['s_name_native'];
          }
        }
      }
    }

    $this->_exportVariableToView('search_start', $iStart);
    $this->_exportVariableToView('search_end', $iEnd);
    $this->_exportVariableToView('search_category', $p_sCategory);
    
    // hardcoded - non pattern and order by relevance
    $p_sOrder = $old_order;
    $this->_exportVariableToView('search_order_type', $p_iOrderType);
    $this->_exportVariableToView('search_order', $p_sOrder);
    $this->_exportVariableToView('search_pattern', $p_sPattern);
    $this->_exportVariableToView('search_from_user', $p_sUser);
    $this->_exportVariableToView('search_total_pages', $iNumPages);
    $this->_exportVariableToView('search_page', $p_iPage);
    $this->_exportVariableToView('search_has_pic', $p_bPic);
    $this->_exportVariableToView('search_only_premium', $p_bPremium);
    $this->_exportVariableToView('search_country', $countryName);
    $this->_exportVariableToView('search_region', $regionName);
    $this->_exportVariableToView('search_city', $cityName);
    $this->_exportVariableToView('search_price_min', $p_sPriceMin);
    $this->_exportVariableToView('search_price_max', $p_sPriceMax);
    $this->_exportVariableToView('search_total_items', $iTotalItems);
    $this->_exportVariableToView('items', $aItems);
    $this->_exportVariableToView('search_show_as', $p_sShowAs);
    $this->_exportVariableToView('search', $this->mSearch);
    //$this->_exportVariableToView('canonical', $searchUri);

    // json
    $json = $this->mSearch->toJson();
    $encoded_alert = base64_encode(osc_encrypt_alert($json));

    // Create the HMAC signature and convert the resulting hex hash into base64
    $stringToSign = osc_get_alert_public_key() . $encoded_alert;
    $signature = hex2b64(hmacsha1(osc_get_alert_private_key(), $stringToSign));
    $server_signature = Session::newInstance()->_set('alert_signature', $signature);

    $this->_exportVariableToView('search_alert', $encoded_alert);
    $alerts_sub = 0;
    if(osc_is_web_user_logged_in()) {
      $alerts = Alerts::newInstance()->findBySearchAndUser($json, osc_logged_user_id());
      if(count($alerts)>0) {
        $alerts_sub = 1;
      }
    }
    
    $this->_exportVariableToView('search_alert_subscribed', $alerts_sub);

    // calling the view...
    if(count($aItems) === 0) {
      header('HTTP/1.1 404 Not Found');
    }

    osc_run_hook('after_search');

    if(!Params::existParam('sFeed')) {
      $this->doView('search.php');
    } else {
      if($p_sFeed == '' || $p_sFeed=='rss') {
        // FEED REQUESTED!
        header('Content-type: text/xml; charset=utf-8');

        $feed = new RSSFeed;
        $feed->setTitle(__('Latest listings added') . ' - ' . osc_page_title());
        $feed->setLink(osc_base_url());
        $feed->setDescription(__('Latest listings added in') . ' ' . osc_page_title());

        if(osc_count_items()>0) {
          while(osc_has_items()) {

            try {
              $itemArray = array (
                'title' => osc_item_title() ,
                'link' => htmlentities(osc_item_url() , ENT_COMPAT , 'UTF-8') ,
                'description' => osc_item_description() ,
                'country' => osc_item_country() ,
                'region' => osc_item_region() ,
                'city' => osc_item_city() ,
                'city_area' => osc_item_city_area() ,
                'category' => osc_item_category() ,
                'dt_pub_date' => osc_item_pub_date()
             );
            } catch (Exception $e) {
            }

            try {
              if (osc_count_item_resources() > 0) {
                try {
                  osc_has_item_resources();
                } catch (Exception $e) {
                }
                try {
                  $itemArray[ 'image' ] = array (
                    'url' => htmlentities(osc_resource_thumbnail_url() , ENT_COMPAT , 'UTF-8') ,
                    'title' => osc_item_title() ,
                    'link' => htmlentities(osc_item_url() , ENT_COMPAT , 'UTF-8')
                 );
                } catch (Exception $e) {
                }
              }
            } catch (Exception $e) {
            }
            
            $feed->addItem($itemArray);
          }
        }

        osc_run_hook('feed', $feed);
        $feed->dumpXML();
      } else {
        osc_run_hook('feed_' . $p_sFeed, $aItems);
      }
    }
  }

  //hopefully generic...

  /**
   * @param $file
   *
   * @return mixed|void
   */
  public function doView($file) {
    osc_run_hook('before_html');
    osc_current_web_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook('after_html');
  }
}

/* file end: ./search.php */
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
* Helper Location
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/

/**
 * Gets current country
 *
 * @return array|string
 */
function osc_country() {
  if (View::newInstance()->_exists('countries')) {
    return View::newInstance()->_current('countries');
  } else {
    return null;
  }
}


/**
 * Gets current region
 *
 * @return array|string
 */
function osc_region() {
  if (View::newInstance()->_exists('regions')) {
    return View::newInstance()->_current('regions');
  } else {
    return null;
  }
}


/**
 * Gets current city
 *
 * @return array|string
 */
function osc_city() {
  if (View::newInstance()->_exists('cities')) {
    return View::newInstance()->_current('cities');
  } else {
    return null;
  }
}


/**
 * Gets current city area
 *
 * @return array|string
 */
function osc_city_area() {
  if (View::newInstance()->_exists('city_areas')) {
    return View::newInstance()->_current('city_areas');
  } else {
    return null;
  }
}


/**
 * Iterator for countries, return null if there's no more countries
 *
 * @return bool
 */
function osc_has_countries() {
  if ( !View::newInstance()->_exists('countries') ) {
    View::newInstance()->_exportVariableToView('countries', Search::newInstance()->listCountries( '>=' , 'country_name ASC' ) );
  }
  return View::newInstance()->_next('countries');
}


/**
 * Iterator for regions, return null if there's no more regions
 *
 * @param string $country
 *
 * @return bool
 * @throws \Exception
 */
function osc_has_regions($country = '%%%%') {
  if ( !View::newInstance()->_exists('regions') ) {
    View::newInstance()->_exportVariableToView('regions', Search::newInstance()->listRegions( $country, '>=' , 'region_name ASC' ) );
  }
  return View::newInstance()->_next('regions');
}


/**
 * Iterator for cities, return null if there's no more cities
 *
 * @param string $region
 *
 * @return bool
 * @throws \Exception
 */
function osc_has_cities($region = '%%%%') {
  if ( !View::newInstance()->_exists('cities') ) {
    View::newInstance()->_exportVariableToView('cities', Search::newInstance()->listCities( $region, '>=' ) );
  }
  $result = View::newInstance()->_next('cities');

  if ( ! $result ) {
    View::newInstance()->_erase( 'cities' );
  }
  return $result;
}


/**
 * Iterator for city areas, return null if there's no more city areas
 *
 * @param string $city
 * @return bool
 */
function osc_has_city_areas($city = '%%%%') {
  if ( !View::newInstance()->_exists('city_areas') ) {
    View::newInstance()->_exportVariableToView('city_areas', Search::newInstance()->listCityAreas( $city, '>=' , 'city_area_name ASC' ) );
  }
  $result = View::newInstance()->_next('city_areas');

  if ( ! $result ) {
    View::newInstance()->_erase( 'city_areas' );
  }
  return $result;
}

/**
 * Gets number of countries
 *
 * @return int
 */
function osc_count_countries() {
  if ( !View::newInstance()->_exists('contries') ) {
    View::newInstance()->_exportVariableToView('countries', Search::newInstance()->listCountries( '>' , 'country_name ASC' ) );   // replacing >= with > to improve performance drastically
  }
  return View::newInstance()->_count('countries');
}


/**
 * Gets number of regions
 *
 * @param string $country
 *
 * @return int
 * @throws \Exception
 */
function osc_count_regions($country = '%%%%') {
  if ( !View::newInstance()->_exists('regions') ) {
    View::newInstance()->_exportVariableToView('regions', Search::newInstance()->listRegions( $country, '>' , 'region_name ASC' ) );   // replacing >= with > to improve performance drastically
  }
  return View::newInstance()->_count('regions');
}


/**
 * Gets number of cities
 *
 * @param string $region
 *
 * @return int
 * @throws \Exception
 */
function osc_count_cities($region = '%%%%') {
  if ( !View::newInstance()->_exists('cities') ) {
    View::newInstance()->_exportVariableToView('cities', Search::newInstance()->listCities( $region, '>' ) );   // replacing >= with > to improve performance drastically
  }
  return View::newInstance()->_count('cities');
}


/**
 * Gets number of city areas
 *
 * @param string $city
 * @return int
 */
function osc_count_city_areas($city = '%%%%') {
  if ( !View::newInstance()->_exists('city_areas') ) {
    View::newInstance()->_exportVariableToView('city_areas', Search::newInstance()->listCityAreas( $city, '>' , 'city_area_name ASC' ) );   // replacing >= with > to improve performance drastically
  }
  return View::newInstance()->_count('city_areas');
}

/**
 * Gets country's name
 *
 * @return string
 */
function osc_country_name() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_field(osc_country(), 'country_name_native', '') <> '' ? osc_field(osc_country(), 'country_name_native', '') : osc_field(osc_country(), 'country_name', ''));
  }
  return osc_field(osc_country(), 'country_name', '');
}

/**
 * Gets country's items
 *
 * @return int
 */
function osc_country_items() {
  return osc_field(osc_country(), 'items', '');
}

/**
 * Gets region's name
 *
 * @return array|string
 */
function osc_region_name() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_field(osc_region(), 'region_name_native', '') <> '' ? osc_field(osc_region(), 'region_name_native', '') : osc_field(osc_region(), 'region_name', ''));
  }
  return osc_field(osc_region(), 'region_name', '');
}

/**
 * Gets region's items
 *
 * @return int
 */
function osc_region_items() {
  return osc_field(osc_region(), 'items', '');
}

/**
 * Gets city's name
 *
 * @return string
 */
function osc_city_name() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_field(osc_city(), 'city_name_native', '') <> '' ? osc_field(osc_city(), 'city_name_native', '') : osc_field(osc_city(), 'city_name', ''));
  }
  return osc_field(osc_city(), 'city_name', '');
}

/**
 * Gets city's items
 *
 * @return int
 */
function osc_city_items() {
  return osc_field(osc_city(), 'items', '');
}

/**
 * Gets city area's name
 *
 * @return string
 */
function osc_city_area_name() {
  return osc_field(osc_city_area(), 'city_area_name', '');
}

/**
 * Gets city area's items
 *
 * @return int
 */
function osc_city_area_items() {
  return osc_field(osc_city_area(), 'items', '');
}


/**
 * Gets country's url
 *
 * @return string
 * @throws \Exception
 */
function osc_country_url() {
  return osc_search_url(array('sCountry' => osc_country_name()));
}


/**
 * Gets region's url
 *
 * @return string
 * @throws \Exception
 */
function osc_region_url() {
  return osc_search_url(array('sRegion' => osc_region_name()));
}


/**
 * Gets city's url
 *
 * @return string
 * @throws \Exception
 */
function osc_city_url() {
  return osc_search_url(array('sCity' => osc_city_name()));
}


/**
 * Gets city area's url
 *
 * @return string
 * @throws \Exception
 */
function osc_city_area_url() {
  return osc_search_url(array('sCityArea' => osc_city_area_name()));
}
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
 * Model database for City table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class City extends DAO
{
  /**
   * It references to self object: City.
   * It is used as a singleton
   *
   * @access private
   * @since unknown
   * @var City
   */
  private static $instance;

  /**
   * It creates a new City object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since unknown
   * @return City
   */
  public static function newInstance()
  {
    if( !self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_city table
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_city');
    $this->setPrimaryKey('pk_i_id');
    $this->setFields( array('pk_i_id', 'fk_i_region_id', 's_name', 's_name_native','fk_c_country_code', 'b_active', 's_slug', 'd_coord_lat', 'd_coord_long') );
  }

  /**
   * Get the cities having part of the city name and region (it can be null)
   *
   * @access public
   * @since unknown
   * @param string $query The beginning of the city name to look for
   * @param int|null $regionId Region id
   * @return array If there's an error or 0 results, it returns an empty array
   */
  public function ajax($query, $regionId = null)
  {
    $this->dao->select('a.pk_i_id as id, a.s_name as label, a.s_name_native as label_native, a.s_name as value, aux.s_name as region, aux.s_name_native as region_native');
    $this->dao->from($this->getTableName().' as a');
    $this->dao->join(Region::newInstance()->getTableName().' as aux', 'aux.pk_i_id = a.fk_i_region_id', 'LEFT');
    $this->dao->like('a.s_name', $query, 'after');
    $this->dao->orLike('a.s_name_native', $query, 'after');
    if( $regionId != null ) {
      if (is_numeric($regionId)) {
        $this->dao->where('a.fk_i_region_id', $regionId);
      } else {
        $this->dao->where('aux.s_name', $regionId);
      }
    }

    $result = $this->dao->get();
    
    if( $result == false ) {
      return array();
    }

    $return = $result->result();
    $output = array();
    if(count($return) > 0 && osc_get_current_user_locations_native() == 1) {
      foreach($return as $r) {
        $row = $r;
        $row['label_original'] = '';

        if(@$row['label_native'] <> '') {
          $row['label_original'] = $row['label'];
          $row['label'] = $row['label_native'];
          $row['value'] = $row['label_native'];
        }

        $row['region_original'] = '';

        if(@$row['region_native'] <> '') {
          $row['region_original'] = $row['region'];
          $row['region'] = $row['region_native'];
        }

        $output[] = $row;
      }
    } else {
      $output = $return;
    }

    return $output;
  }

  /**
   * Get the cities from an specific region id. It's deprecated, use findByRegion
   *
   * @access public
   * @since unknown
   * @deprecated deprecated since 2.3
   * @see City::findByRegion
   * @param int $regionId Region id
   * @return array If there's an error or 0 results, it returns an empty array
   */
  public function getByRegion($regionId)
  {
    return $this->findByRegion($regionId);
  }

  /**
   * Get the cities from an specific region id
   *
   * @access public
   * @since 2.3
   * @param int $regionId Region id
   * @return array If there's an error or 0 results, it returns an empty array
   */
  public function findByRegion($regionId)
  {
    if($regionId <= 0) { 
      return array();
    }
    
    $this->dao->select($this->getFields());
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_region_id', $regionId);
    $this->dao->orderBy('s_name', 'ASC');

    $result = $this->dao->get();

    if( $result == false ) {
      return array();
    }

    return $result->result();
  }

  /**
   * Get the citiy by its name and region
   *
   * @access public
   * @since  unknown
   *
   * @param   $cityName
   * @param int $regionId
   *
   * @return array
   */
  public function findByName($cityName, $regionId = null)
  {
    if(trim($cityName) == '') { 
      return array();
    }
    
    $this->dao->select($this->getFields());
    $this->dao->from($this->getTableName());
    $this->dao->where(sprintf('(s_name="%s" OR s_name_native="%s")', $cityName, $cityName));
    $this->dao->limit(1);
    if( $regionId != null ) {
      $this->dao->where('fk_i_region_id', $regionId);
    }

    $result = $this->dao->get();

    if( $result == false ) {
      return array();
    }

    return $result->row();
  }

  /**
   * Get all the rows from the table t_city
   *
   * @access public
   * @since unknown
   * @return array
   */
  public function listAll()
  {
    $this->dao->select($this->getFields());
    $this->dao->from($this->getTableName());
    $this->dao->orderBy('s_name', 'ASC');
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   *  Delete a city with its city areas
   *
   * @access public
   * @since  3.1
   *
   * @param $pk
   *
   * @return int number of failed deletions or 0 in case of none
   * @throws \Exception
   */
  public function deleteByPrimaryKey($pk) {
    $mCityAreas = CityArea::newInstance();
    $aCityAreas = $mCityAreas->findByCity($pk);
    $result = 0;
    foreach($aCityAreas as $cityarea) {
      $result += $mCityAreas->deleteByPrimaryKey($cityarea['pk_i_id']);
    }
    Item::newInstance()->deleteByCity($pk);
    CityStats::newInstance()->delete(array('fk_i_city_id' => $pk));
    User::newInstance()->update(array('fk_i_city_id' => null, 's_city' => ''), array('fk_i_city_id' => $pk));
    if(!$this->delete(array('pk_i_id' => $pk))) {
      $result++;
    }
    return $result;
  }

  /**
   * Find a location by its slug
   *
   * @access public
   * @since 3.2.1
   * @param $slug
   * @return array
   */
  public function findBySlug($slug)
  {
    if(trim($slug) == '') { 
      return array();
    }
    
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_slug', $slug);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }
    return $result->row();
  }

  /**
   * Find a locations with no slug
   *
   * @access public
   * @since 3.2.1
   * @return array
   */
  public function listByEmptySlug()
  {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_slug', '');
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }
    return $result->result();
  }

}

/* file end: ./oc-includes/osclass/model/City.php */
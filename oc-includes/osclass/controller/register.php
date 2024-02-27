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
 * Class CWebRegister
 */
class CWebRegister extends BaseModel {
  public function __construct() {
    parent::__construct();

    if(!osc_users_enabled()) {
      osc_add_flash_error_message(_m('Users not enabled'));
      $this->redirectTo(osc_base_url());
    }

    if(!osc_user_registration_enabled()) {
      osc_add_flash_error_message(_m('User registration is not enabled'));
      $this->redirectTo(osc_base_url());
    }

    if(osc_is_web_user_logged_in()) {
      $this->redirectTo(osc_base_url());
    }
    
    osc_run_hook('init_register');
  }

  public function doModel() {
    switch($this->action) {
      case('register'):       // register user view
        $this->doView('user-register.php');
        break;
        
      case('register_post'):  // register user action (form submitted)
        osc_csrf_check();
        
        if(!osc_users_enabled()) {
          osc_add_flash_error_message(_m('Users are not enabled'));
          $this->redirectTo(osc_base_url());
        }

        osc_run_hook('before_user_register');

        $banned = osc_is_banned(Params::getParam('s_email'));
        
        if($banned==1) {
          osc_add_flash_error_message(_m('Your current email is not allowed'));
          $this->redirectTo(osc_register_account_url());
        } else if($banned==2) {
          osc_add_flash_error_message(_m('Your current IP is not allowed'));
          $this->redirectTo(osc_register_account_url());
        }

        require_once LIB_PATH . 'osclass/UserActions.php';
        $userActions = new UserActions(false);
        $success = $userActions->add();
        
        if($success == 1) {
          osc_add_flash_ok_message(_m('The user has been created. An activation email has been sent'));
          $this->redirectTo(osc_base_url());
        } else if($success == 2) {
          osc_add_flash_ok_message(_m('Your account has been created successfully'));
          Params::setParam('action', 'login_post');
          Params::setParam('email', Params::getParam('s_email'));
          Params::setParam('password', Params::getParam('s_password', false, false));
          
          require_once osc_lib_path() . 'osclass/controller/login.php';
          $do = new CWebLogin();
          $do->doModel();
        } else {
          osc_add_flash_error_message($success);
          $this->redirectTo(osc_register_account_url());
        }
        
        break;
        
      case('validate'):     // validate account
        $id = (int) Params::getParam('id');
        $code = Params::getParam('code');
        $userManager = new User();
        $user = $userManager->findByIdSecret($id, $code);

        if (!$user) {
          osc_add_flash_error_message(_m('The link is not valid anymore. Sorry for the inconvenience!'));
          $this->redirectTo(osc_base_url());
        }

        if ($user['b_active'] == 1) {
          osc_add_flash_error_message(_m('Your account has already been validated'));
          $this->redirectTo(osc_base_url());
        }

        $userManager = new User();
        $success = $userManager->update(array('b_active' => '1'), array('pk_i_id' => $id, 's_secret' => $code));

        if($success) {
          // Auto-login user
          Session::newInstance()->_set('userId', $user['pk_i_id']);
          Session::newInstance()->_set('userName', $user['s_name']);
          Session::newInstance()->_set('userEmail', $user['s_email']);
          $phone = $user[ 's_phone_mobile' ] ?: $user[ 's_phone_land' ];
          Session::newInstance()->_set('userPhone', $phone);

          osc_run_hook('hook_email_user_registration', $user);
          osc_run_hook('validate_user', $user);

          osc_add_flash_ok_message(_m('Your account has been validated'));
        } else {
          osc_add_flash_ok_message(_m('Account validation failed'));
        }
        
        $this->redirectTo(osc_base_url());
        break;
    }
  }

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

/* file end: ./register.php */
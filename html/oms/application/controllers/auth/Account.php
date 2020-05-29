<?php

/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-03-25
* Time : 오후 3:18
*/

defined('BASEPATH') or exit('No direct script access allowed');

class Account extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function goAccount($account_id)
    {
        if($account_id=="") return false;

        $this->session->set_userdata(array("oms_account_id"=>$account_id));

        redirect(site_url('/dashboard'));


    }



}
<?php

/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2019-03-25
 * Time : 오후 3:18
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Master_id extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function goMasterId($master_id, $uri_string)
    {
        if($master_id=="") return false;

        $this->session->set_userdata(array("oms_master_id"=>$master_id));

        if($this->session->userdata("current_url")=="order/order/compareOrderItemPrice"){
            redirect(site_url('/dashboard'));
        }else{
            //redirect($this->session->userdata("current_url"));
            $uri_string = urldecode($uri_string);
            redirect($uri_string);
        }


    }



}
<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 4:03
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');

        //masterId 체크하고 리스트 불러오기
        $this->load->library('master_id', array('chk_master_id' => true));
        $this->master_id_list = $this->master_id->getMasterId();

    }

    public function index()
    {
        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('oms_master_id')
        );

        $this->load->view('common/header', array('left_menu'=> $this->load->view('common/left_menu', $left_data, true)));
        $this->load->view('common/dashboard');
        $this->load->view('common/footer', array('left_menu_on' => true));

    }

}
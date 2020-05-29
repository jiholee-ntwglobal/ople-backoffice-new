<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-30
 * Time: 오후 1:36
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Vcode extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('item/ople_item_model');
    }

    public function index()
    {


    }
}
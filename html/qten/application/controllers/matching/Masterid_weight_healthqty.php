<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-30
 * Time: 오후 1:36
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Masterid_weight_healthqty extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('item/ople_item_model');
    }

    public function index()
    {
        $filter = array();
        $select  = 'c.upc, a.it_health_cnt, b.weight';
        $ople_item_health_cnt_weight = $this->ople_item_model->get_item_health_cnt_weight($filter ,$select);

        foreach($ople_item_health_cnt_weight->result_array() as $item){
            var_dump($item);
        }

    }
}
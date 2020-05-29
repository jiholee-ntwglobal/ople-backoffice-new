<?php

/**
* Created by PhpStorm.
* User: 강소진
* Date : 2018-07-26
* Time : 오전 10:30
*/

defined('BASEPATH') or exit('No direct script access allowed');

class Mapping_search extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model("item/virtual_item_model");
        $this->load->model("item/master_item_model");
    }

    public function index()
    {
        $virtual_item_id = preg_replace("/[^0-9]*/s", "", ($this->input->get("virtual_item_id")));
        $this->mapping_search($virtual_item_id);
    }

    public function mapping_search($virtual_item_id)
    {
        $data = array();

        $data['virtual_item_id'] = $virtual_item_id;

        $virtual_item_infos = array();
        $product_infos = array();
        $mapping_products = array();

        $virtual_item_result = $this->virtual_item_model->getVirtualItemDetail(array("virtual_item_id" => $virtual_item_id));

        foreach ($virtual_item_result->result_array() as $virtual_item_info){

            $virtual_item_infos[element('master_item_id',$virtual_item_info)] = $virtual_item_info;
        }

        $data['mapping_info'] = $virtual_item_infos;

        if(count($virtual_item_infos)>0){

            $master_item_infos = array_column($virtual_item_infos, 'master_item_id');
            $master_item_infos = array_unique($master_item_infos);


            $product_result = $this->master_item_model->getMasterItems(array("master_item_id_in"=>$master_item_infos));

            foreach ($product_result->result_array() as $product_info){
                $product_infos[element('master_item_id',$product_info)] =  $product_info;
            }
            $data['product_info'] = $product_infos;
        }


       $this->load->view("item/mapping_search", $data);
    }



}
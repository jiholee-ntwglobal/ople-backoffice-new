<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 9:34
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_additional_info_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function replaceItemAdditionalInfo($data)
    {
        $this->oms_db->replace('item_additional_info', $data);
    }

    public function getItemAdditionalInfo($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'master_item_id_in':
                    $this->oms_db->where_in('master_item_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->from('item_additional_info');

        return $this->oms_db->get();

    }



}
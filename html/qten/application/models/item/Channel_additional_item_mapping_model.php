<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오후 4:11
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Channel_additional_item_mapping_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function getMappingInfo($filter, $select='*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);
        $this->oms_db->from('channel_additional_item_mapping');

        $query = $this->oms_db->get();

        return $query->row_array();
    }

}
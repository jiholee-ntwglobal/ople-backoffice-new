<?php

/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-02-28
* Time : 오전 10:08
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_ople_shipping_model extends CI_Model
{
    private $ntics_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics_db = $this->load->database('ntics', true);
    }


    public function addNs02($add_data)
    {
        $this->ntics_db->insert('ntshipping_test.dbo.NS_S02', $add_data);
    }

    public function addNs03($add_data)
    {
        $this->ntics_db->insert('ntshipping_test.dbo.NS_S03', $add_data);
    }

    public function countNs01($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics_db->select('count(*) as cnt');
        $this->ntics_db->from('ntshipping_test.dbo.NS_S01');

        $query = $this->ntics_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);
    }

    public function countNs02($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics_db->select('count(*) as cnt');
        $this->ntics_db->from('ntshipping_test.dbo.NS_S02');

        $query = $this->ntics_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);
    }

}
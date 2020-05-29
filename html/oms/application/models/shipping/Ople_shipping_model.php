<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오후 10:14
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Ople_shipping_model extends CI_Model
{
    private $ntics_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics_db = $this->load->database('ntics', true);
    }

    public function addNs01($add_data)
    {
        foreach ($add_data as $column => $value){
            switch ($column){
                case 'od_name':case 'od_addr1':case 'od_addr2':case 'od_b_name':case 'od_b_addr1':case 'od_b_addr2':case 'od_memo':
                    $this->ntics_db->set($column, 'N\'' .$this->ntics_db->escape_str($value) . '\'', false);
                    break;
                case 'cdate':
                    $this->ntics_db->set($column, $value, false);
                    break;
                default:
                    $this->ntics_db->set($column, $value);
                    break;
            }

        }

        $this->ntics_db->insert('ntshipping.dbo.NS_S01');
    }

    public function addNs02($add_data)
    {
        $this->ntics_db->insert('ntshipping.dbo.NS_S02', $add_data);
    }

    public function addNs03($add_data)
    {
        $this->ntics_db->insert('ntshipping.dbo.NS_S03', $add_data);
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
        $this->ntics_db->from('ntshipping.dbo.NS_S01');

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
        $this->ntics_db->from('ntshipping.dbo.NS_S02');

        $query = $this->ntics_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);
    }

}
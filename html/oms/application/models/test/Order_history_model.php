<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 8:52
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_history_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function addOrderHistory($add_data)
    {
        $this->oms_db->insert('order_history', $add_data);

        return $this->oms_db->insert_id();
    }

    public function getOrderHistorys($filter, $select='*', $order_by=array())
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);

        $this->oms_db->from('order_history');

        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by($orderby, $direct);
        }

        return $this->oms_db->get();
    }

    public function getOrderHistory($filter, $select=null)
    {
        $select = ($select) ? : "*";
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'status_not':
                    $this->oms_db->where("status !=", $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select,false);

        $this->oms_db->from('order_history');

        $result = $this->oms_db->get();

        return $result->row_array();
    }

    public function addOrderInfoHistory($add_data)
    {
        $this->oms_db->insert('order_info_history', $add_data);

        return $this->oms_db->insert_id();
    }
}
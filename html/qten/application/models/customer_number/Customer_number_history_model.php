<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-31
 * Time: 오후 3:05
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_number_history_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function addCustomer_num_history($add_data)
    {
        $this->oms_db->insert('upload_customer_num_history', $add_data);
    }

    public function getCustomer_num_historys($filter, $select="*", $order_by = array())
    {

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){

                case 'limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;

                default:
                    $this->oms_db->where('a.' . $filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);

        $this->oms_db->from('upload_customer_num_history a');

        $this->oms_db->join('channel_info b','a.channel_id = b.channel_id','inner');

        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by($orderby, $direct);
        }

        return $this->oms_db->get();

    }
}
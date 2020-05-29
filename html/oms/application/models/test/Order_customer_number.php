<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-31
 * Time: 오전 11:22
 */
class Order_customer_number extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function updateOrderCustomer_number($update_data,$filter)
    {

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'package_no_in':
                    $this->oms_db->where_in('package_no', $filter_val);
                    break;
                case 'validate_error':
                    $this->oms_db->where('validate_error & '.$filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }


        $this->oms_db->where("status = '1'");
        $this->oms_db->where("validate_error & 1");
        $this->oms_db->update('OMS.order', $update_data);
    }

    public function getOrderCustomer_number_novalue($filter=array() , $select  = '*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'package_no_in':
                    $this->oms_db->where_in('package_no', $filter_val);
                    break;
                case 'validate_error':
                    $this->oms_db->where('validate_error &'.$filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);

        $this->oms_db->from('OMS.order');

        return $this->oms_db->get();
    }

}
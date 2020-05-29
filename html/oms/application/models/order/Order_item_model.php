<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 8:49
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_item_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function addOrderItem($add_data)
    {
        $this->oms_db->insert('order_item', $add_data);

        return $this->oms_db->insert_id();
    }

    public function addOrderItemOption($add_data)
    {
        $this->oms_db->insert('order_item_option', $add_data);
    }

    public function updateOrderItem($update_data, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'order_id_in':
                    $this->oms_db->where_in("order_id", $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->update('order_item', $update_data);
    }

    public function updateOrderOption($update_data, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->update('order_item_option', $update_data);
    }

    public function getOrderItems($filter, $select='i.*')
    {
        $join_tables = array();

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'sales_flag': case 'stock_flag': case 'status':
                    $this->oms_db->where('o.'. $filter_key, $filter_val);
                    if(!in_array('order', $join_tables)) array_push($join_tables, 'order');
                    break;
                case 'join_virtual_item_detail':
                    if(!in_array('virtual_item_detail', $join_tables)) array_push($join_tables, 'virtual_item_detail');
                    break;
                case 'join_order':
                    if(!in_array('order', $join_tables)) array_push($join_tables, 'order');
                    break;
                case 'order_id_in':
                    $this->oms_db->where_in('i.order_id', $filter_val);
                    break;
                case 'no_cancel':
                    $this->oms_db->where('i.qty > 0');
                    break;
                case 'order_dt_bigger':
                    $this->oms_db->where("left(o.order_date,10)>='$filter_val'",null,false);
                    break;
                default:
                    $this->oms_db->where('i.'. $filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);

        $this-> oms_db->from('order_item i');

        foreach ($join_tables as $join_table){
            switch ($join_table){
                case 'order':
                    $this->oms_db->join('order o', 'o.order_id = i.order_id', 'LEFT');
                    break;
                case 'virtual_item_detail':
                    $this->oms_db->join('virtual_item_detail v', 'v.virtual_item_id = i.virtual_item_id', 'LEFT');
                    break;
            }
        }

        return $this->oms_db->get();

    }

    public function getOrderItem($filter, $select=null){

        $select = ($select) ? : 'i.*';

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'order_item_id_in':
                    $this->oms_db->where_in("i.order_item_id", $filter_val);
                    break;
                default:
                    $this->oms_db->where("i.".$filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);
        $this->oms_db->from('order_item i');
        $this->oms_db->join('order o', 'i.order_id=o.order_id', 'LEFT');

        $query = $this->oms_db->get();

        return $query->row_array();

    }

    public function getOrderItemCount($filter){
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'order_id_in':
                    $this->oms_db->where_in('o.order_id', $filter_val);
                    break;
                case 'status':
                    $this->oms_db->where('o.' . $filter_key, $filter_val);
                    break;
                default:
                    $this->oms_db->where('i.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select('count(*) as cnt');
        $this->oms_db->from('order_item i');
        $this->oms_db->join('order o', 'i.order_id=o.order_id', 'LEFT');

        $query = $this->oms_db->get();

        $return = $query->row_array();

        return element('cnt', $return, 0);
    }

    public function getItemAddtionalInfo($virtual_item_id){
	
		$this->oms_db->select("SUM(IFNULL(i.weight,0)) AS weight, SUM(IFNULL(i.health_food_qty,0)) AS health_cnt, d.virtual_item_id");
		$this->oms_db->from('virtual_item_detail d');
		$this->oms_db->join('item_additional_info i','d.master_item_id = i.master_item_id','LEFT');
		$this->oms_db->where('d.virtual_item_id', $virtual_item_id);
		$sql	= $this->oms_db->get();
		return $sql->row_array();
	}

	public function getOpleItemAddtionalInfo($virtual_item_id)
    {
        $this->oms_db->select("SUM(IFNULL(i.weight,0)*d.quantity) AS weight, IFNULL(i.weight_type_id,0) AS weight_type_id, w.weight_limit, SUM(IFNULL(i.health_cnt,0)*d.quantity) AS health_cnt, d.virtual_item_id");
        $this->oms_db->from('virtual_item_detail d');
        $this->oms_db->join('ople_item_additional_info i','d.master_item_id = i.master_item_id','LEFT');
        $this->oms_db->join('yc4_weight_type_info w','i.weight_type_id = w.weight_type_id','LEFT');

        $this->oms_db->where('d.virtual_item_id', $virtual_item_id);
        $sql	= $this->oms_db->get();
        return $sql->row_array();
    }

	
	public function addErrorResultTmp($add_data)
	{
		$this->oms_db->insert('result_error_tmp', $add_data);
	}

    public function addOrderItemHistory($insert_data){
        $this->oms_db->insert("order_item_history",$insert_data);
    }
}
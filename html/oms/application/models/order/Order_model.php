<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 8:12
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model
{
    private $oms_db;
    private $ntshipping;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
        $this->ntshipping = $this->load->database('ntshipping', true);
    }

    public function addOrder($add_data)
    {
        $this->oms_db->insert('order', $add_data);

        return $this->oms_db->insert_id();
    }

    public function addOrderAddress($add_data)
    {
        $this->oms_db->insert('order_address', $add_data);
    }

    public function addOrderAmount($add_data)
    {
        $this->oms_db->insert('order_amount', $add_data);
    }

    public function updateOrder($update_data, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'order_id_in':
                    $this->oms_db->where_in('order_id', $filter_val);
                    break;
                case 'validate_errors':
                    $this->oms_db->where('validate_error & '.$filter_val);
                    break;
                case 'status_not':
                    $this->oms_db->where("status !=",$filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        foreach ($update_data as $update_key => $update_val) {
            switch ($update_key) {
                case 'validate_error':
                    $this->oms_db->set('validate_error', $update_val,false);
                    unset($update_data['validate_error']);
                    break;
            }
        }

        $this->oms_db->update('order', $update_data);

    }

    public function updateOrderAddress($update_data, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->update('order_address', $update_data);
    }

    public function updateOrderAmount($update_data, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->update('order_amount', $update_data);
    }

    public function getNsAddress($orderId) {
        $this->ntshipping->select("od_name, od_tel, od_hp, od_b_name, od_b_tel, od_b_hp, od_b_zip1, od_b_zip2, od_b_addr1, od_b_addr2, od_memo ");
        $this->ntshipping->from('NS_S01 o');
        $this->ntshipping->where('od_id', $orderId);

        $query = $this->ntshipping->get();
        return $query->row_array();

    }

    public function getOrder($filter,
                             $select='*')
    {
        $join_tables = array();

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'join_item':
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'join_channel_info':
                    if(!in_array('channel_info', $join_tables))
                        array_push($join_tables, 'channel_info');
                    break;
                case 'order_item_id':
                    $this->oms_db->where("i.".$filter_key, $filter_val);
                    break;
                case 'amountinfo':
                    $this->oms_db->join('order_amount b', 'b.order_id = o.order_id', 'LEFT');
                    break;
                default:
                    $this->oms_db->where('o.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);

        $this->oms_db->from('order o');
        $this->oms_db->join('order_address a', 'a.order_id = o.order_id', 'LEFT');

        foreach ($join_tables as $join_table){
            switch ($join_table){
                case 'order_item':
                    $this->oms_db->join('order_item i', 'i.order_id=o.order_id', 'LEFT');
                    break;
                case 'channel_info':
                    $this->oms_db->join('channel_info c', 'o.channel_id=c.channel_id', 'LEFT');
                    break;
            }
        }

        $query = $this->oms_db->get();

        return $query->row_array();
    }

    public function getOrders($filter, $select='o.*', $order_by = array(), $group_by = '')
    {

        $order_by = ($order_by) ? : array("o.order_id"=>"DESC");
        $join_tables = array();
        $sorting_arr = array();
        $sorting_fg = 0;

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'order_id_in':
                    $this->oms_db->where_in('o.order_id', $filter_val);
                    break;
                /** shipping code 여러개 검색가능하게 추가 2019.10.28 */
                case 'shipping_code_in':
                    $this->oms_db->where_in('o.shipping_code', $filter_val);
                    if(!array_key_exists("o.shipping_code",$sorting_arr) && is_array($filter_val) !== false)
                        $sorting_arr = array("o.shipping_code"=>$filter_val);
                    break;
                case 'package_no_in':
                    $this->oms_db->where_in('o.package_no', $filter_val);
                        if(!array_key_exists("o.package_no",$sorting_arr) && is_array($filter_val) !== false)
                            $sorting_arr = array("o.package_no"=>$filter_val);
                    break;
                case 'od_date_search':
                    $this->oms_db->where("left(o.order_date,10)='$filter_val'",null,false);
                    break;
                case 'od_date_filter_search':
                    $this->oms_db->where("left(o.order_date,10)>='$filter_val'",null,false);
                    break;
                case 'od_date_filter_between':
                    $this->oms_db->where("left(o.order_date,10)>='".$filter_val[0]."' and left(o.order_date,10)<='".$filter_val[1]."'",null,false);
                    break;
                case 'package_no_not_ins':
                    $this->oms_db->where_not_in('o.package_no', $filter_val);
                    if(!array_key_exists("o.package_no",$sorting_arr) && is_array($filter_val) !== false)
                        $sorting_arr = array("o.package_no"=>$filter_val);
                    break;
				case 'no_validate_error':
					$this->oms_db->where('o.validate_error=0');
					break;
                case 'validate_errors':
                    $this->oms_db->where('o.validate_error & '.$filter_val);
                    break;
                case 'order_id_in':
                    $this->oms_db->where_in('o.order_id', $filter_val);
                        if(!array_key_exists("o.order_id",$sorting_arr) && is_array($filter_val) !== false)
                            $sorting_arr = array("o.order_id"=>$filter_val);
                    break;
                case 'order_id_big':
                    $this->oms_db->where('o.order_id>=', $filter_val);
                    if(!array_key_exists("o.order_id",$sorting_arr) && is_array($filter_val) !== false)
                        $sorting_arr = array("o.order_id"=>$filter_val);
                    break;
                case 'status_in':
                    $this->oms_db->where_in('o.status', $filter_val);
                    if(!array_key_exists("o.status",$sorting_arr) && is_array($filter_val) !== false)
                        $sorting_arr = array("o.status"=>$filter_val);
                    break;
                case 'cancel_mode':
                    $this->oms_db->where("(o.status = 9 or i.cancel_flag = 1)", null, false);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'cancel_flag':
                    $this->oms_db->where('i.cancel_flag', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'order_period':
                    $this->oms_db->where("
                        left(o.order_date,10) 
                            between 
                            '" . $this->oms_db->escape_str($filter_val[0]) . "' and '" . $this->oms_db->escape_str($filter_val[1]) . "'", null, false);
                    break;
                case 'channel_order_no':
                    $this->oms_db->where('i.channel_order_no', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'order_item_id_in':
                    $this->oms_db->where_in('i.order_item_id', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
				case 'channel_order_no_in':
					$this->oms_db->where_in('i.channel_order_no', $filter_val);
					if(!in_array('order_item', $join_tables))
						array_push($join_tables, 'order_item');
                    if(!array_key_exists("i.channel_order_no",$sorting_arr) && is_array($filter_val) !== false)
                        $sorting_arr = array("i.channel_order_no"=>$filter_val);
					break;
                case 'channel_product_no':
                    $this->oms_db->where('i.channel_product_no', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    if(!array_key_exists("i.channel_product_no",$sorting_arr) && is_array($filter_val) !== false)
                        $sorting_arr = array("i.channel_product_no"=>$filter_val);
                    break;
                case 'channel_product_no_in':
                    $this->oms_db->where_in('i.channel_product_no', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    if(!array_key_exists("i.channel_product_no",$sorting_arr) && is_array($filter_val) !== false)
                        $sorting_arr = array("i.channel_product_no"=>$filter_val);
                    break;
                case 'buyer_name':
                    $this->oms_db->like('a.buyer_name', $filter_val);
                    if(!in_array('order_address', $join_tables))
                        array_push($join_tables, 'order_address');

                    break;
                case 'receiver_name':
                    $this->oms_db->like('a.receiver_name', $filter_val);
                    if(!in_array('order_address', $join_tables))
                        array_push($join_tables, 'order_address');
                    break;
                case 'mapping':
                    $this->oms_db->where('i.virtual_item_id', '0');
                    $this->oms_db->where('i.add_virtual_item_id', '0');
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'validate_error':
                    $this->oms_db->where('o.validate_error>0');
                    break;
                case 'shipping_start':
                    $this->oms_db->where('o.shipping_code IS NOT NULL');
                    break;
                case 'limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;
                case 'join_address':
                    if(!in_array('order_address', $join_tables))
                        array_push($join_tables, 'order_address');
                    break;
                case 'join_amount':
                    if(!in_array('order_amount', $join_tables))
                        array_push($join_tables, 'order_amount');
                    break;
                case 'join_item':
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'sorting' :
                    if($filter_val == "Y") $sorting_fg = 1;
                    break;
                case 'memo' :
                    $this->oms_db->like('c.comment', $filter_val);
                    if(!in_array('order_comment',$join_tables))
                        array_push($join_tables, 'order_comment');
                    break;
                default:
                    $this->oms_db->where('o.' . $filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from('order o');


        foreach ($join_tables as $join_table){
            switch ($join_table){
                case 'order_item':
                    $this->oms_db->join('order_item i', 'i.order_id=o.order_id', 'LEFT');
                    break;
                case 'order_address':
                    $this->oms_db->join('order_address a', 'a.order_id=o.order_id', 'LEFT');
                    break;
                case 'order_amount':
                    $this->oms_db->join('order_amount m', 'm.order_id=o.order_id', 'LEFT');
                    break;
                case 'order_comment':
                    $this->oms_db->join('order_comment c', 'c.order_id=o.order_id', 'LEFT');
                    break;
            }
        }

        if($group_by != '')
            $this->oms_db->group_by($group_by);


        if($sorting_fg == 1 && empty($sorting_arr) != 1) {
            foreach ($sorting_arr as $sorting_key => $sorting_val){
                $order_by_case = "(case $sorting_key ";

                $n = 1;
                foreach ($sorting_val as $val) {
                    $order_by_case .= " when " . $this->oms_db->escape($val) . " THEN " . $n++;
                }

                $order_by_case .= " else 0 end )";
            }
            $this->oms_db->order_by($order_by_case,"ASC");
        }else{
            foreach ($order_by as $orderby => $direct) {
                $this->oms_db->order_by($orderby, $direct);
            }
        }
		
       /* if($_SERVER['REMOTE_ADDR']=='211.214.213.101'){
			$return = $this->oms_db->get(); echo $this->oms_db->last_query(); return $return;
		}*/


       return $this->oms_db->get();

    }

    public function getOrderCount($filter=array(), $select=null)
    {
        $filter = ($filter) ? : array();
        $select = ($select) ? : 'count(distinct o.order_id) as cnt';

        $join_tables = array();

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key) {
                /** shipping code 여러개 검색가능하게 추가 2019.10.28 */
                case 'shipping_code_in':
                    $this->oms_db->where_in('o.shipping_code', $filter_val);
                    break;
				case 'package_no_in':
					$this->oms_db->where_in('o.package_no', $filter_val);
					break;
                case 'order_id_in':
                    $this->oms_db->where_in('o.order_id', $filter_val);
                    break;
                case 'status_in':
                    $this->oms_db->where_in('o.status', $filter_val);
                    break;
                case 'status_not':
                    $this->oms_db->where("o.status !=", $filter_val);
                    break;
                case 'order_item_id_in':
                    $this->oms_db->where_in("i.order_item_id",$filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'cancel_mode':
                    $this->oms_db->where("(o.status = 9 or i.cancel_flag = 1)", null, false);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'cancel_flag':
                    $this->oms_db->where('i.cancel_flag', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'order_period':
                    $this->oms_db->where($a="
                        left(o.order_date,10) 
                            between 
                            '" . $this->oms_db->escape_str($filter_val[0]) . "' and '" . $this->oms_db->escape_str($filter_val[1]) . "'", null, false);
                    break;
                case 'channel_order_no':
                    $this->oms_db->where('i.channel_order_no', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'channel_order_no_in':
                    $this->oms_db->where_in('i.channel_order_no', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'channel_product_no':
                    $this->oms_db->where('i.channel_product_no', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'channel_product_no_in':
                    $this->oms_db->where_in('i.channel_product_no', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'order_item_id_in':
                    $this->oms_db->where_in('i.order_item_id', $filter_val);
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'buyer_name':
                    $this->oms_db->like('a.buyer_name', $filter_val);
                    if(!in_array('order_address', $join_tables))
                        array_push($join_tables, 'order_address');
                    break;
                case 'receiver_name':
                    $this->oms_db->like('a.receiver_name', $filter_val);
                    if(!in_array('order_address', $join_tables))
                        array_push($join_tables, 'order_address');
                    break;
                case 'mapping':
                    $this->oms_db->where('i.virtual_item_id', '0');
                    $this->oms_db->where('i.add_virtual_item_id', '0');
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'validate_error':
                    $this->oms_db->where('o.validate_error>0');
                    break;
                case 'validate_error_search':
                    $this->oms_db->where('o.validate_error', $filter_val);
                    break;
                case 'validate_error_search_not':
                    $this->oms_db->where('o.validate_error!=', $filter_val,false);
                    break;
                case 'memo' :
                    $this->oms_db->like('c.comment', $filter_val);
                    if(!in_array('order_comment',$join_tables))
                        array_push($join_tables, 'order_comment');
                    break;
                case 'join_item':
                    if(!in_array('order_item', $join_tables))
                        array_push($join_tables, 'order_item');
                    break;
                case 'join_amount':
                    if(!in_array('order_amount', $join_tables))
                        array_push($join_tables, 'order_amount');
                    break;
                case 'limit':case 'join_item_option':  case 'sorting' :
                    break;
                default:
                    $this->oms_db->where('o.' . $filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from('order o');

            foreach ($join_tables as $join_table){
            switch ($join_table){
                case 'order_item':
                    $this->oms_db->join('order_item i', 'i.order_id=o.order_id', 'LEFT');
                    break;
                case 'order_address':
                    $this->oms_db->join('order_address a', 'a.order_id=o.order_id', 'LEFT');
                    break;
                case 'order_amount':
                    $this->oms_db->join('order_amount m', 'm.order_id=o.order_id', 'LEFT');
                    break;
                case 'order_comment':
                    $this->oms_db->join('order_comment c', 'c.order_id=o.order_id', 'LEFT');
                    break;
            }
        }

        $query = $this->oms_db->get();
        $result = $query->row_array();

        return element('cnt', $result, 0);


    }

    public function getHealthWeightErrorOrder($filter=array(), $select=null)
    {
        $select = ($select) ? : "";

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'order_item_in_not_cancel':
                    $this->oms_db->where("( i.cancel_flag=0 or i.order_item_id in (".$filter_val.") ) ", null, false);
                    break;
                default:
                    $this->oms_db->where("o.".$filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from("order_item i");
        $this->oms_db->join('order o', 'i.order_id=o.order_id', 'LEFT');
        $this->oms_db->join('virtual_item_detail m', 'i.virtual_item_id = m.virtual_item_id', 'LEFT');
        $this->oms_db->join('ople_item_additional_info a', 'a.master_item_id=m.master_item_id', 'LEFT');
        $this->oms_db->join('yc4_weight_type_info w', 'a.weight_type_id=w.weight_type_id', 'LEFT');

        $this->oms_db->group_by("o.order_id");

        $this->oms_db->having("sum(ifnull(a.health_cnt,0) * i.qty * m.quantity) > 6 or 
                            sum(ifnull(a.weight,0) * i.qty * m.quantity)> w.weight_limit",null,false);

        $result = $this->oms_db->get();

        return $result->row_array();
    }

    public function addupdate_tracking_number_error($add_data)
    {
        $this->oms_db->insert('update_tracking_number_error', $add_data);
    }

    // 송장 업데이트 API 오류 메세지에 따라서 백오피스 상태 변경(배송->완료)시 내역 history 테이블 insert 20191210 - KSJ
    public function addupdate_tracking_error_history($add_data)
    {
        $this->oms_db->insert('update_tracking_error_history', $add_data);
    }

    public  function getExchange_rate($filter=array(), $select='*'){

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'nowdays':
                    $this->oms_db->limit(1);
                    $this->oms_db->order_by('uid', 'desc');
                    break;
                case 'deaesearch':

                    $this->oms_db->where("date_format(dt,'%Y%m%d')='".$filter_val."'");
                    break;
                default:
                    $this->oms_db->where("o.".$filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from("exchange_rate_history o");

        return $this->oms_db->get();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-30
 * Time: 오후 3:09
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Ople_item_model extends CI_Model
{
    private $ople;

    function __construct()
    {
        parent::__construct();

        $this->ople = $this->load->database('ople', true);
    }

    public function get_item_health_cnt_weight($filter = array() ,$select= '*')
    {
        if(count($filter) > 0){

        }
        $this->ople->select($select);

        $this->ople->from('yc4_item a');

        $this->ople->join('yc4_item_weight b', 'a.it_id = b.it_id', 'LEFT');

        $this->ople->join('ople_mapping c', "c.it_id = a.it_id AND ifnull(c.ople_type, '') = 'm'", 'LEFT');

        $this->ople->where('(ifnull(a.it_health_cnt, 0) > 0
      OR b.weight IS NOT NULL) AND c.ople_type IS NOT NULL');

        return $this->ople->get();

    }

    //비가공 무게 분류 추가를 위해 새로 만듬 KSJ
    public function get_item_health_weight_info($filter = array() ,$select= '*')
    {
        $this->ople->select($select);
        $this->ople->from('yc4_item a');
        $this->ople->join('yc4_item_weight_info b', 'a.it_id = b.it_id', 'LEFT');
        $this->ople->join('ople_mapping c', "c.it_id = a.it_id AND ifnull(c.ople_type, '') = 'm'", 'LEFT');

        $this->ople->where('(ifnull(a.it_health_cnt, 0) > 0 OR b.weight IS NOT NULL) AND c.ople_type IS NOT NULL');

        $result = $this->ople->get();

        return $result;

    }

    //비가공 무게 분류 테이블
    public function getWeightTypeInfo($select=null, $filter=array()){
        $select = ($select) ? : "*";
        $filter = ($filter) ? : array();

        foreach ($filter as $filter_key=>$filter_val){
            switch ($filter_key){
                default :
                    $this->ople->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->ople->select($select);
        $this->ople->from("yc4_weight_type_info");

        $result = $this->ople->get();

        return $result;
    }

    public function getItems($filter, $select=null){
        $select = ($select) ? : "*";

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'it_id_in':
                    $this->ople->where_in('it_id', $filter_val);
                    break;
                default:
                    $this->ople->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->ople->select($select);
        $this->ople->from("yc4_item");

        return $this->ople->get();
    }

    public function getItemMapping($filter=array(), $select=null){
        $filter = ($filter) ? : array();
        $select = ($select) ? : "m.*";
        $join_tables = array();

        foreach ($filter as $filter_key=>$filter_val){
            switch ($filter_key){
                case 'it_use': case 'it_discontinued' :
                    if(!in_array('yc4_item', $join_tables))
                        array_push($join_tables, 'yc4_item');
                   $this->ople->where('i.'.$filter_key, $filter_val);
                break;
                case 'it_stock_qty_zero_more':
                    if(!in_array('yc4_item', $join_tables))
                        array_push($join_tables, 'yc4_item');
                    $this->ople->where('i.it_stock_qty>0');
                    break;
                case 'upc_in':
                    $this->ople->where_in('m.upc', $filter_val);
                    break;
                default:
                    $this->ople->where($filter_key,$filter_val);
                    break;
            }
        }

        $this->ople->select($select)
            ->from('ople_mapping m');

        foreach ($join_tables as $join_table){
            switch ($join_table){
                case 'yc4_item':
                    $this->ople->join('yc4_item i', 'm.it_id=i.it_id', 'LEFT');
                    break;
            }
        }

        return $this->ople->get();
    }

    public function getPromotionPrice($filter)
    {
        foreach ($filter as $key => $val){
            switch ($key){
                case 'it_ids': $this->ople->where_in('it_id', $val); break;
                default: $this->ople->where($key, $val); break;
            }
        }

        $this->ople->select('it_id, amount_usd');
        $this->ople->from('yc4_promotion_item_dc_cache');
        $this->ople->where("date_format(now(), '%Y-%m-%d') BETWEEN ifnull(st_dt,'1900-01-01') AND ifnull(en_dt,'9999-12-31')", null, false);

        return $this->ople->get();

    }

    public function getHotDealPrice($filter)
    {
        foreach ($filter as $key => $val){
            switch ($key){
                case 'it_ids': $this->ople->where_in('it_id', $val); break;
                default: $this->ople->where($key, $val); break;
            }
        }

        $this->ople->select('it_id, it_event_amount');
        $this->ople->from('yc4_hotdeal_item');
        $this->ople->where('flag', 'Y');
        $this->ople->where('sort>', '0');
        $this->ople->where('sort<', '9');

        return $this->ople->get();

    }

    public function getMembershipPrice($filter)
    {
        foreach ($filter as $key => $val){
            switch ($key){
                case 'it_ids': $this->ople->where_in('it_id', $val); break;
                default: $this->ople->where($key, $val); break;
            }
        }

        $this->ople->select('it_id, member_price');
        $this->ople->from('item_member_price');
        $this->ople->where("date_format(now(), '%Y-%m-%d') BETWEEN ifnull(start_date,'1900-01-01') AND ifnull(end_date,'9999-12-31')", null, false);

        return $this->ople->get();

    }
    public function getRecentRate()
    {
        $this->ople->select('pay');
        $this->ople->from('exchange_rate_history');
        $this->ople->order_by('dt', 'DESC');
        $this->ople->limit(1);

        $query = $this->ople->get();

        $result = $query->row_array();

        return element('pay', $result);

    }

}

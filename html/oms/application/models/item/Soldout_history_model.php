<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-04
 * File: soldout_history_model.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Soldout_history_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }
	
    public function addSoldoutHistory($add_data)
	{
		$this->oms_db->insert('soldout_history', $add_data);
	}
	
	public function getSoldoutHistory($filter,$select,$order_by)
	{

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'distinct' :
                    $this->oms_db->distinct();
                    break;
                case 'yyyymmdd' :
                    $this->oms_db->where($filter_val);
                    break;

                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by($orderby, $direct);
        }

        $this->oms_db->select($select);

        $this->oms_db->from('soldout_history o');

        return $this->oms_db->get();

	}


    public function getSoldoutHistorys($filter = array(),$select = '*',$order_by = array())
    {

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'distinct' :
                    $this->oms_db->distinct();
                    break;
                case 'yyyy-mm-dd' :
                    $this->oms_db->where("date_format(o.create_date, '%Y-%m-%d') ='".$filter_val."'");
                    break;
                case 'channel_id' :
                    $this->oms_db->where('cc.channel_id',$filter_val);
                    break;
                case 'master_item_id_in' :
                    $this->oms_db->where_in('d.master_item_id', $filter_val);
                    break;
                case 'limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;
                case 'channel_item_codes':
                    $this->oms_db->where('c.channel_item_code', $filter_val);
                    break;
                case 'virtual_items':
                    $this->oms_db->where('v.virtual_item_id', $filter_val);
                    break;
                case 'master_id':
                    $this->oms_db->where('cc.master_id',$filter_val);
                    break;
                default:
                    $this->oms_db->where('o.'.$filter_key, $filter_val);
                    break;
            }
        }
        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by('o.'.$orderby, $direct);
        }
        $this->oms_db->select($select);

        $this->oms_db->from('soldout_history o');

        $this->oms_db->join('channel_item_info c','o.item_info_id=c.item_info_id','left');
        $this->oms_db->join('virtual_item v','c.virtual_item_id = v.virtual_item_id','left');
        $this->oms_db->join('virtual_item_detail d','d.virtual_item_id = v.virtual_item_id','left');
        $this->oms_db->join('channel_info cc','c.channel_id = cc.channel_id','left');

        return $this->oms_db->get();

    }

    public function getSoldoutHistorysCount($filter = array(),$select = 'count(*)cnt',$order_by = array())
    {

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'distinct' :
                    $this->oms_db->distinct();
                    break;
                case 'yyyy-mm-dd' :
                    $this->oms_db->where("date_format(o.create_date, '%Y-%m-%d') ='".$filter_val."'");
                    break;
                case 'channel_id' :
                    $this->oms_db->where('cc.channel_id',$filter_val);
                    break;
                case 'master_item_id_in' :
                    $this->oms_db->where_in('d.master_item_id', $filter_val);
                    break;
                case 'channel_item_codes':
                    $this->oms_db->where('c.channel_item_code', $filter_val);
                    break;
                case 'virtual_items':
                    $this->oms_db->where('v.virtual_item_id', $filter_val);
                    break;
                case 'master_id':
                    $this->oms_db->where('cc.master_id',$filter_val);
                    break;
                case 'limit':
                break;
                default:
                    $this->oms_db->where('o.'.$filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);

        $this->oms_db->from('soldout_history o');

        $this->oms_db->join('channel_item_info c','o.item_info_id=c.item_info_id','left');
        $this->oms_db->join('virtual_item v','c.virtual_item_id = v.virtual_item_id','left');
        $this->oms_db->join('virtual_item_detail d','d.virtual_item_id = v.virtual_item_id','left');
        $this->oms_db->join('channel_info cc','c.channel_id = cc.channel_id','left');

        $query = $this->oms_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);

    }

    public function addSoldoutHistoryError($add_data)
    {
        $this->oms_db->insert('soldout_history_error', $add_data);
    }

    public function getSoldoutHistoryError($filter,$select,$order_by=array())
    {

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'distinct' :
                    $this->oms_db->distinct();
                    break;
                case 'yyyymmdd' :
                    $this->oms_db->where($filter_val);
                    break;
                case 'error_message_like' :
                    $this->oms_db->where("error_message like '$filter_val%'");
                    break;
                case 'groupby' :
                    $this->oms_db->group_by("item_info_id",false);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by($orderby, $direct);
        }

        $this->oms_db->select($select);

        $this->oms_db->from('soldout_history_error o');

        return $this->oms_db->get();

    }

    public function getSoldoutHistorysErrorCount($filter = array(),$select = 'count(*)cnt',$order_by = array())
    {

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'distinct' :
                    $this->oms_db->distinct();
                    break;
                case 'yyyy-mm-dd' :
                    $this->oms_db->where("date_format(o.create_date, '%Y-%m-%d') ='".$filter_val."'");
                    break;
                case 'channel_id' :
                    $this->oms_db->where('cc.channel_id',$filter_val);
                    break;
                case 'master_item_id_in' :
                    $this->oms_db->where_in('d.master_item_id', $filter_val);
                    break;
                case 'channel_item_codes':
                    $this->oms_db->where('c.channel_item_code', $filter_val);
                    break;
                case 'virtual_items':
                    $this->oms_db->where('v.virtual_item_id', $filter_val);
                    break;
                case 'master_id':
                    $this->oms_db->where('cc.master_id',$filter_val);
                    break;
                case 'limit':
                    break;
                default:
                    $this->oms_db->where('o.'.$filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);

        $this->oms_db->from('soldout_history_error o');

        $this->oms_db->join('channel_item_info c','o.item_info_id=c.item_info_id','left');
        $this->oms_db->join('virtual_item v','c.virtual_item_id = v.virtual_item_id','left');
        $this->oms_db->join('virtual_item_detail d','d.virtual_item_id = v.virtual_item_id','left');
        $this->oms_db->join('channel_info cc','c.channel_id = cc.channel_id','left');

        $query = $this->oms_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);

    }

    public function getSoldoutHistorysError($filter = array(),$select = '*',$order_by = array())
    {

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'distinct' :
                    $this->oms_db->distinct();
                    break;
                case 'yyyy-mm-dd' :
                    $this->oms_db->where("date_format(o.create_date, '%Y-%m-%d') ='".$filter_val."'");
                    break;
                case 'channel_id' :
                    $this->oms_db->where('cc.channel_id',$filter_val);
                    break;
                case 'master_item_id_in' :
                    $this->oms_db->where_in('d.master_item_id', $filter_val);
                    break;
                case 'channel_item_codes':
                    $this->oms_db->where('c.channel_item_code', $filter_val);
                    break;
                case 'virtual_items':
                    $this->oms_db->where('v.virtual_item_id', $filter_val);
                    break;
                case 'limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;
                case 'master_id':
                    $this->oms_db->where('cc.master_id',$filter_val);
                    break;
                default:
                    $this->oms_db->where('o.'.$filter_key, $filter_val);
                    break;
            }
        }
        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by('o.'.$orderby, $direct);
        }
        $this->oms_db->select($select);

        $this->oms_db->from('soldout_history_error o');

        $this->oms_db->join('channel_item_info c','o.item_info_id=c.item_info_id','left');
        $this->oms_db->join('virtual_item v','c.virtual_item_id = v.virtual_item_id','left');
        $this->oms_db->join('virtual_item_detail d','d.virtual_item_id = v.virtual_item_id','left');
        $this->oms_db->join('channel_info cc','c.channel_id = cc.channel_id','left');

        return $this->oms_db->get();

    }
}
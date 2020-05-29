<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오전 1:35
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Channel_item_info_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function insertItem($insert_data){
        foreach ($insert_data as $column=>$val){
            switch ($column){
                case 'create_date' :
                    $this->oms_db->set($column, $val, false);
                    break;
                default:
                    $this->oms_db->set($column, $val);
                    break;
            }
        }
        $this->oms_db->insert('channel_item_info');

        return $this->oms_db->insert_id();
    }

    public function updateItem($update_data, $filter){

        foreach ($filter as $filter_key=>$filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key,$filter_val);
                    break;
            }
        }

        foreach ($update_data as $column=>$val){
            switch ($column){
                default:
                    $this->oms_db->set($column,$val);
                    break;
            }
        }

        $this->oms_db->update('channel_item_info',$update_data);
    }

    public function getMasterItems($filter, $select='d.*', $group_by='')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'channel_id':
                    $this->oms_db->where("i.channel_id", $filter_val);
                    break;
                default:
                    $this->oms_db->where('d.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);
        $this->oms_db->from('virtual_item_detail d');
        $this->oms_db->join('channel_item_info i', 'd.virtual_item_id=i.virtual_item_id', 'INNER');
        if($group_by != '') $this->oms_db->group_by($group_by);

        return $this->oms_db->get();

    }

    public function updateChannelItemInfo($update_date, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'item_info_id_in':
                    $this->oms_db->where_in('item_info_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->update('channel_item_info', $update_date);
    }

    public function deleteChannelItemInfo($filter){
        foreach ($filter as $filter_key=> $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }

            $this->oms_db->delete("channel_item_info");
        }
    }
	
	public function getChannelItemInfos($filter, $select='i.*',$order_by = array())
	{
		$join_tables	= array();
		
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				case 'upper_uid':
					$this->oms_db->where('i.item_info_id > '. $filter_val);
					break;
				case 'master_info':
					if(!in_array('master_item_stock', $join_tables))
						array_push($join_tables, 'master_item_stock');
					break;
                case 'distincts':
                    $this->oms_db->distinct();
                    break;
                case 'single_item':
                    if(!in_array('single_item', $join_tables))
                        array_push($join_tables, 'single_item');
                    break;
                case 'single_master_item_id_in' :
                    $this->oms_db->where_in('vd.master_item_id', $filter_val);
                    break;
				case 'limit':
					$this->oms_db->limit($filter_val);
					break;
                case 'single_limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;
                case 'yyyy-mm-dd' :
                    $this->oms_db->where("date_format(i.create_date, '%Y-%m-%d') ='".$filter_val."'");
                    break;
                case 'virtual_item_id_in':
                    $this->oms_db->where_in('i.virtual_item_id',$filter_val);
                    break;
                case 'channel_item_code_in':
                    $this->oms_db->where_in("i.channel_item_code",$filter_val);
                    break;
                case 'master_id':
                    if(!in_array('channel_info', $join_tables))
                        array_push($join_tables, 'channel_info');
                    $this->oms_db->where("c.".$filter_key, $filter_val);
                    break;
				default:
					$this->oms_db->where('i.' . $filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select($select, false);
		$this->oms_db->from('channel_item_info i');
		if($this->session->userdata("qten_manager_user_id") != "fhwauin") $this->oms_db->where("channel_item_code!=", '1663903323', false);
		foreach ($join_tables as $join_table){
			switch ($join_table){
                case 'single_item':
                    $this->oms_db->join('channel_info b', 'i.channel_id = b.channel_id', 'left');
                    $this->oms_db->join('virtual_item v', 'i.virtual_item_id = v.virtual_item_id', 'left');
                    $this->oms_db->join('virtual_item_detail vd', 'v.virtual_item_id = vd.virtual_item_id', 'left');
                    $this->oms_db->group_by("i.item_info_id",false);
                    break;
				case 'master_item_stock':
					$this->oms_db->join('virtual_item_detail d', 'd.virtual_item_id = i.virtual_item_id', 'LEFT');
					$this->oms_db->join('master_item_stock m', 'm.master_item_id = d.master_item_id', 'LEFT');
					break;
                case 'channel_info':
                    $this->oms_db->join('channel_info c', 'c.channel_id=i.channel_id', 'LEFT');
                    break;
			}
		}

        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by($orderby, $direct);
        }

        $result = $this->oms_db->get();
		//if($_SERVER['REMOTE_ADDR'] == "211.214.213.101") { echo $this->oms_db->last_query(); }
        return $result;
	}

    public function testgetChannelItemInfos($filter, $select='i.*',$order_by = array())
    {
        $join_tables	= array();

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'upper_uid':
                    $this->oms_db->where('i.item_info_id > '. $filter_val);
                    break;
                case 'master_info':
                    if(!in_array('master_item_stock', $join_tables))
                        array_push($join_tables, 'master_item_stock');
                    break;
                case 'distincts':
                    $this->oms_db->distinct();
                    break;
                case 'single_item':
                    if(!in_array('single_item', $join_tables))
                        array_push($join_tables, 'single_item');
                    break;
                case 'single_master_item_id_in' :
                    $this->oms_db->where_in('vd.master_item_id', $filter_val);
                    break;
                case 'limit':
                    $this->oms_db->limit($filter_val);
                    break;
                case 'single_limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;
                case 'yyyy-mm-dd' :
                    $this->oms_db->where("date_format(i.create_date, '%Y-%m-%d') ='".$filter_val."'");
                    break;
                case 'virtual_item_id_in':
                    $this->oms_db->where_in('i.virtual_item_id',$filter_val);
                    break;
                case 'channel_item_code_in':
                    $this->oms_db->where_in("i.channel_item_code",$filter_val);
                    break;
                case 'master_id':
                    if(!in_array('channel_info', $join_tables))
                        array_push($join_tables, 'channel_info');
                    $this->oms_db->where("c.".$filter_key, $filter_val);
                    break;
                default:
                    $this->oms_db->where('i.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select, false);
        $this->oms_db->from('channel_item_info i');

        foreach ($join_tables as $join_table){
            switch ($join_table){
                case 'single_item':
                    $this->oms_db->join('channel_info b', 'i.channel_id = b.channel_id', 'left');
                    $this->oms_db->join('virtual_item v', 'i.virtual_item_id = v.virtual_item_id', 'left');
                    $this->oms_db->join('virtual_item_detail vd', 'v.virtual_item_id = vd.virtual_item_id', 'left');
                    $this->oms_db->group_by("i.item_info_id",false);
                    break;
                case 'master_item_stock':
                    $this->oms_db->join('virtual_item_detail d', 'd.virtual_item_id = i.virtual_item_id', 'LEFT');
                    $this->oms_db->join('master_item_stock m', 'm.master_item_id = d.master_item_id', 'LEFT');
                    break;
                case 'channel_info':
                    $this->oms_db->join('channel_info c', 'c.channel_id=i.channel_id', 'LEFT');
                    break;
            }
        }

        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by($orderby, $direct);
        }

        $result = $this->oms_db->get();
        //if($_SERVER['REMOTE_ADDR'] == "211.214.213.101") echo $this->oms_db->last_query();
        return $result;
    }

	function getChannelItemInfosCount($filter, $select='count(*) cnt'){

        $join_tables	= array();

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'master_info':
                    if(!in_array('master_item_stock', $join_tables))
                        array_push($join_tables, 'master_item_stock');
                    break;
                case 'single_item':
                    if(!in_array('single_item', $join_tables))
                        array_push($join_tables, 'single_item');
                    break;
                case 'yyyy-mm-dd' :
                    $this->oms_db->where("date_format(i.create_date, '%Y-%m-%d') ='".$filter_val."'");
                    break;
                case 'single_master_item_id_in' :
                    $this->oms_db->where_in('vd.master_item_id', $filter_val);
                    break;
                case 'distincts':
                    $this->oms_db->distinct();
                    break;
                case 'single_limit':
                    break;
                case 'virtual_item_id_in':
                    $this->oms_db->where_in('i.virtual_item_id',$filter_val);
                    break;
                case 'channel_item_code_in':
                    $this->oms_db->where_in("i.channel_item_code",$filter_val);
                    break;
                case 'item_info_id_in':
                    $this->oms_db->where_in("i.item_info_id", $filter_val);
                    break;
                case 'master_id':
                    if(!in_array('channel_info', $join_tables))
                        array_push($join_tables, 'channel_info');
                    $this->oms_db->where("c.".$filter_key, $filter_val);
                    break;
                default:
                    $this->oms_db->where('i.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);
        $this->oms_db->from('channel_item_info i');
        if($this->session->userdata("qten_manager_user_id") != "fhwauin") $this->oms_db->where("channel_item_code!=", '1663903323', false);

        foreach ($join_tables as $join_table){
            switch ($join_table){
                case 'single_item':
                    $this->oms_db->join('channel_info b', 'i.channel_id = b.channel_id', 'left');
                    $this->oms_db->join('virtual_item v', 'i.virtual_item_id = v.virtual_item_id', 'left');
                    $this->oms_db->join('virtual_item_detail vd', 'v.virtual_item_id = vd.virtual_item_id', 'left');
                    break;

                case 'master_item_stock':
                    $this->oms_db->join('virtual_item_detail d', 'd.virtual_item_id = i.virtual_item_id', 'LEFT');
                    $this->oms_db->join('master_item_stock m', 'm.master_item_id = d.master_item_id', 'LEFT');
                    break;
                case 'channel_info':
                    $this->oms_db->join('channel_info c', 'c.channel_id=i.channel_id', 'LEFT');
                    break;
            }
        }

        $query = $this->oms_db->get();
//
//
//        if($_SERVER['REMOTE_ADDR']=='211.214.213.101'){
//            print_r($this->oms_db->last_query());
//        }

        $result = $query->row_array();

        return element('cnt', $result, 0);

    }

    function getItemInfo($filter, $select='*'){

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where('i.' . $filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from('channel_item_info i');

        $query = $this->oms_db->get();

        return $query->row_array();
    }

    function getItemPriceUpdateHistoryInfos($filter,$select='*',$order_by= array()){

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){

                case 'create_date_between':
                    $this->oms_db->where("
                        left(create_date,10) 
                            between 
                            '" . $this->oms_db->escape_str($filter_val[0]) . "' and '" . $this->oms_db->escape_str($filter_val[1]) . "'", null, false);
                    break;

                case 'update_join' :
                    $this->oms_db->join('channel_info b', 'a.channel_code = b.channel_code AND a.account_id = b.master_id', 'INNER');
                    $this->oms_db->join('channel_item_info c', 'a.channel_item_code = c.channel_item_code AND b.channel_id = c.channel_id', 'INNER');
                    break;
                case 'single_limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;
                default:
                    $this->oms_db->where( $filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from('channel_item_update_price_history a');

        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by($orderby, $direct);
        }

        $result = $this->oms_db->get();

        return $result;

    }

    function getItemPriceUpdateHistorycount($filter, $select='count(*) cnt'){

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){

                case 'create_date_between':
                    $this->oms_db->where("
                        left(create_date,10) 
                            between 
                            '" . $this->oms_db->escape_str($filter_val[0]) . "' and '" . $this->oms_db->escape_str($filter_val[1]) . "'", null, false);
                    break;
                default:
                    $this->oms_db->where( $filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from('channel_item_update_price_history');

        $query = $this->oms_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);

    }

    function addItemPriceUpdateHistory($add_data){

        $this->oms_db->insert('channel_item_update_price_history', $add_data);

        return $this->oms_db->insert_id();

    }

    public function updaItetemPriceUpdateHistory($update_date, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'item_info_id_in':
                    $this->oms_db->where_in('item_info_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->update('channel_item_update_price_history', $update_date);
    }

    //가격 조정 히스토리
    public function addChannelItemPriceHistory($add_data)
    {
        $this->oms_db->insert('channel_item_price_history', $add_data);

        return $this->oms_db->insert_id();
    }

    public function getChannelItemPriceHistory($select=null, $filter=array(), $order_by=array())
    {
        $select = ($select) ? : "h.*";
        $filter = ($filter) ? : array();
        $order_by = ($order_by) ? : array('h.create_date'=>"desc", 'h.channel_item_code'=>"desc");
        $add_join_tables = array();

        foreach ($filter as $filter_key=>$filter_val){
            switch ($filter_key){
                case 'channel_item_code_in':
                    $this->oms_db->where_in("h.channel_item_code", $filter_val);
                    break;
                case 'item_info_id_in':
                    $this->oms_db->where_in("h.item_info_id", $filter_val);
                    break;
                case 'list_mode':
                    if (!in_array('channel_item_info', $add_join_tables))
                        array_push($add_join_tables, 'channel_item_info');
                    break;
                case 'start_create_date':
                    $this->oms_db->where("left(h.create_date,10)>=", $filter_val);
                    break;
                case 'end_create_date':
                    $this->oms_db->where("left(h.create_date,10)<=",$filter_val);
                    break;
                case 'channel_id':
                    $this->oms_db->where("i.".$filter_key, $filter_val);
                    break;
                case 'page' :
                    $this->oms_db->limit($filter_val['start'], $filter_val['end']); //$filter_val['start'], $filter_val['end']);
                    break;
                default:
                    $this->oms_db->where("h.".$filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select,false);
        $this->oms_db->from('channel_item_price_history h');

        foreach ($add_join_tables as $join_table){
            switch ($join_table){
                case 'channel_item_info':
                    $this->oms_db->join('channel_item_info i', 'i.item_info_id=h.item_info_id', 'LEFT');
                    break;
                case 'naver_order_product':
                    $this->oms_db->join('naver_order_product p', 'o.naver_order_id=p.naver_order_id', 'LEFT');
                    break;
            }
        }

        foreach ($order_by as $orderby => $direct){
            $this->oms_db->order_by($orderby, $direct);
        }

        $result = $this->oms_db->get();
      //  echo $this->oms_db->last_query();
        return $result;

    }

    public function getChannelItemPriceHistorycount($filter = array(), $select=null){

        $filter = ($filter) ? : array();
        $select = ($select) ? : "count(*) cnt";
        $add_join_tables = array();

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'channel_item_code_in':
                    $this->oms_db->where_in("h.channel_item_code", $filter_val);
                    break;
                case 'list_mode':
                    if (!in_array('channel_item_info', $add_join_tables))
                        array_push($add_join_tables, 'channel_item_info');
                    break;
                case 'start_create_date':
                    $this->oms_db->where("left(h.create_date,10)>=", $filter_val);
                    break;
                case 'end_create_date':
                    $this->oms_db->where("left(h.create_date,10)<=",$filter_val);
                    break;
                case 'channel_id':
                    $this->oms_db->where("i.".$filter_key, $filter_val);
                    break;
                default:
                    $this->oms_db->where("h.".$filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from('channel_item_price_history h');

        foreach ($add_join_tables as $join_table){
            switch ($join_table){
                case 'channel_item_info':
                    $this->oms_db->join('channel_item_info i', 'i.item_info_id=h.item_info_id', 'LEFT');
                    break;
                case 'naver_order_product':
                    $this->oms_db->join('naver_order_product p', 'o.naver_order_id=p.naver_order_id', 'LEFT');
                    break;
            }
        }

        $query = $this->oms_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);
    }

    public function addItemInfoHistory($add_data){
        foreach ($add_data as $column => $value){
            $this->oms_db->set($column, $value);
        }

        $this->oms_db->insert("channel_item_info_history");

        return $this->oms_db->insert_id();
    }

	/* ===================================================================
	* DESC		: 아젠테 브랜드 코드 리스트 가져오기
	* INPUT		: array		$mfgname_arr		브랜드명
	* OUTPUT	: array		$result				아젠테 브랜드 코드
	* --------------------------------------------------------------------
	* COMMENTS
	* --------------------------------------------------------------------
	* 2020-03-04	@q10_brandcode_20200304
	* ================================================================= */
	public function getAgenteBrandcode($mfgname_arr)
	{
		$this->oms_db->select('brandname_en, id');
        $this->oms_db->from('agente_brand_code');
		$this->oms_db->where("view_status='1'");
		//$this->oms_db->where_in("brandname_en", $mfgname_arr);

		$q = $this->oms_db->get();
        $list = $q->result_array();

		$result = array();
		if ( empty($list) === FALSE && count($list) > 0 )
		{
			foreach ( $list AS $row )
			{
				$bn_en = strtolower($row['brandname_en']);
				$bn_en = str_replace("'", '', $bn_en);
				$bn_en = str_replace(" ", '', $bn_en);
				$result[$bn_en] = $row['id'];
			}
		}

		return $result;
	}
}
<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-04
 * File: Channel_option_item_info_model.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Option_tmp_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }
	
	public function getChannelOptionItemInfo($filter, $select='*')
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select($select);
		$this->oms_db->from('option_item_test');
		
		$query = $this->oms_db->get();
		
		return $query->row_array();
	}

	public function updateChannelOptionItemInfo($update_date, $filter)
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				case 'item_info_id_in':
					$this->oms_db->where_in('item_info_id', $filter_val);
					break;
				case 'channel_item_code_in':
					$this->oms_db->where_in('channel_item_code', $filter_val);
					break;
				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->update('option_item_test', $update_date);
		echo $this->oms_db->last_query();
	}
	
	public function getMasterItems($filter, $select='d.*', $group_by='')
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				case 'regist_fg':
					$this->oms_db->where('i.regist_fg', $filter_val);
					break;
				default:
					$this->oms_db->where('d.' . $filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select($select);
		$this->oms_db->from('virtual_item_detail d');
		$this->oms_db->join('option_item_test i', 'd.virtual_item_id=i.virtual_item_id', 'INNER');
		if($group_by != '') $this->oms_db->group_by($group_by);
		
		return $this->oms_db->get();
		
	}
	
	public function getChannelOptionItemInfos($filter, $select='i.*')
	{
		$join_tables	= array();
		$select_escape	= true;
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				case 'channel_item_code_in':
					$this->oms_db->where_in('i.channel_item_code', $filter_val);
					break;
				case 'virtual_item_id':
					$this->oms_db->where_in('i.virtual_item_id', $filter_val);
					break;
				case 'option_name':
					$this->oms_db->like('i.option_name', $filter_val);
					break;
				case 'master_info':
					if(!in_array('master_item_stock', $join_tables))
						array_push($join_tables, 'master_item_stock');
					break;
				case 'select_escape':
					$select_escape	= $filter_val;
					break;
				case 'limit':
					$this->oms_db->limit($filter_val[0], $filter_val[1]);
					break;
				case 'group_by':
					$this->oms_db->group_by($filter_val);
					break;
				default:
					$this->oms_db->where('i.' . $filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select($select, $select_escape);
		$this->oms_db->from('option_item_test i');
		
		foreach ($join_tables as $join_table){
			switch ($join_table){
				case 'master_item_stock':
					$this->oms_db->join('virtual_item_detail d', 'd.virtual_item_id = i.virtual_item_id', 'LEFT');
					$this->oms_db->join('master_item_stock m', 'm.master_item_id = d.master_item_id', 'LEFT');
					break;
			}
		}
		
		return $this->oms_db->get();
	}
	
	public function getOptionItemCount($filter)
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				case 'group_by':
				case 'select_escape':
				case 'limit':
					break;
				case 'channel_item_code_in':
					$this->oms_db->where_in('channel_item_code', $filter_val);
					break;
				case 'virtual_item_id':
					$this->oms_db->where_in('virtual_item_id', $filter_val);
					break;
				case 'option_name':
					$this->oms_db->like('option_name', $filter_val);
					break;
				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select('COUNT(DISTINCT channel_item_code) AS cnt');
		$this->oms_db->from('option_item_test');
		
		$query	= $this->oms_db->get();
		
		$result	= $query->row_array();
		
		return element('cnt', $result, 0);
	}
	
	public function addChannelOptionItemInfoBulk($add_data)
	{
		$this->oms_db->insert_batch('option_item_test',$add_data);
	}
}
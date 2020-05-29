<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-19
 * File: Channel_option_item_history_model.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Channel_option_item_history_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }
	
    public function addOptionItemHistory($addData)
	{
		$this->oms_db->insert('channel_option_item_history',$addData);
		
		return $this->oms_db->insert_id();
	}
	
	public function addOptionItemHistoryDetail($addData)
	{
		$this->oms_db->insert('channel_option_item_history_detail',$addData);
	}
	
	public function addOptionItemHistoryDetailBulk($addData)
	{
		$this->oms_db->insert_batch('channel_option_item_history_detail',$addData);
	}
	
	public function getOptionItemHistory($filter, $select='*')
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select($select);
		$this->oms_db->from('channel_option_item_history');
		
		return $this->oms_db->get();
	}
	
	public function getOptionItemHistoryDetail($filter, $select='*')
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select($select);
		$this->oms_db->from('channel_option_item_history_detail');
		
		return $this->oms_db->get();
	}

	public function updateOptionItemHistory($update_data, $filter)
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;
			}
		}
		
		$this->oms_db->update('channel_option_item_history', $update_data);
	}

	public function deleteOptionItemHistory($addData)
	{
		$this->oms_db->insert('channel_option_item_delete_history',$addData);

		return $this->oms_db->insert_id();
	}

	// 옵션 아이템 히스토리 정보 가져오기 : @option_history_20200108
	public function getHistoryOptionItem($option_history_id)
	{
		$field = "h.*, hd.option_history_detail_id, hd.section, hd.option_name, hd.virtual_item_id, hd.price, hd.stock_qty, hd.additem_fg";
		$this->oms_db->select($field);
		$this->oms_db->from('channel_option_item_history AS h');
		$this->oms_db->join('channel_option_item_history_detail AS hd', 'h.option_history_id=hd.option_history_id', 'LEFT');
		$this->oms_db->where('h.option_history_id', $option_history_id);
		
		return $this->oms_db->get();
	}

	// 옵션 아이템 히스토리 이전 정보 가져오기 : @option_history_20200108
	public function getHistoryOptionItemIdAgo($channel_item_code, $option_history_id, $additem_fg)
	{
		$field = "MAX(h.option_history_id) AS max";
		$this->oms_db->select($field);
		$this->oms_db->from('channel_option_item_history AS h');
		$this->oms_db->join('channel_option_item_history_detail AS hd', 'h.option_history_id=hd.option_history_id', 'LEFT');
		$this->oms_db->where('h.channel_item_code', $channel_item_code);
		$this->oms_db->where('h.option_history_id <', $option_history_id);
		$this->oms_db->where('hd.additem_fg', $additem_fg);
		$result = $this->oms_db->get()->row_array();
		
		return ( $result['max'] > 0 ) ? $result['max'] : NULL;
	}

	// 옵션 아이템 작업자 내역 가져오기 : @option_history_20200108
	public function getHistoryOptionItemWorker($item_code)
	{
		$this->oms_db->select('*');
		$this->oms_db->from('channel_option_item_history');
		$this->oms_db->where('channel_item_code', $item_code);
		$this->oms_db->order_by('history_date', 'DESC');

		return $this->oms_db->get();
	}

	public function testUpdate(){
		
//		$this->oms_db->where('option_history_id', '1');
//		$update_data	= array(
//			'history_date'=>''
//		);
//    	$this->oms_db->update('channel_option_item_history', $update_data);
    	
	}
}
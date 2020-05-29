<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오전 1:44
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_item_stock_model_bk20190313 extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function empty()
    {
        $this->oms_db->empty_table('master_item_stock');
    }

    public function insertBulk($bulk_data)
    {
        $this->oms_db->insert_batch('master_item_stock', $bulk_data);
    }

    public function update($update_data, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'master_item_id_in':
                    $this->oms_db->where_in('master_item_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->update('master_item_stock', $update_data);

    }

    public function getMasterItemStock($filter=array(),$select='*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);
        $this->oms_db->from('master_item_stock');

        return $this->oms_db->get();
    }

    public function getItemStockInfo($filter, $select = 'i.*,d.master_item_id,s.currentqty')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'channel_id_in':
                    $this->oms_db->where_in('i.channel_id', $filter_val);
                    break;
				case 'channel_id':
					$this->oms_db->where('i.channel_id', $filter_val);
					break;
                case 'currentqty':
                    $this->oms_db->where('s.' . $filter_key, $filter_val);
                    break;
                case 'currentqty_upper':
                    $this->oms_db->where('s.currentqty >', $filter_val);
                    break;
                case 'currentqty_lower':
                    $this->oms_db->where('s.currentqty <', $filter_val);
                    break;
                case 'exclude_account_type_bit_lower':
                    $this->oms_db->where('s.exclude_account_type & ' . $filter_val . ' < 1', null, false);
                    break;
                case 'restrict_customer_clearance_in':
                        $this->oms_db->where_in('s.restrict_customer_clearance' , $filter_val);
                    break;
                case 'restrict_customer_clearance':
                    $this->oms_db->where('s.restrict_customer_clearance', $filter_val);
                    break;
                default:
                    $this->oms_db->where('i.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);
        $this->oms_db->from('channel_item_info123 i');
        $this->oms_db->join('virtual_item_detail d', 'd.virtual_item_id=i.virtual_item_id', 'LEFT');
        $this->oms_db->join('master_item_stock s', 's.master_item_id=d.master_item_id', 'LEFT');

        return $this->oms_db->get();
        //$return = $this->oms_db->get(); echo $this->oms_db->last_query(); return $return;
    }
	
	public function getOptionItemStockInfo($filter, $select = 'i.*,d.master_item_id,s.currentqty')
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				case 'channel_id_in':
					$this->oms_db->where_in('i.channel_id', $filter_val);
					break;
				case 'stock_status':
					if($filter_val == 'Y'){
						$this->oms_db->where('i.stock_qty > 0');
					}else{
						$this->oms_db->where('i.stock_qty < 1');
					}
					break;
				case 'currentqty':
					$this->oms_db->where('s.' . $filter_key, $filter_val);
					break;
				case 'currentqty_upper':
					$this->oms_db->where('s.currentqty >', $filter_val);
					break;
				case 'currentqty_lower':
					$this->oms_db->where('s.currentqty <', $filter_val);
					break;
				case 'exclude_account_type_bit_lower':
					$this->oms_db->where('s.exclude_account_type & ' . $filter_val . ' < 1', null, false);
					break;
				case 'restrict_customer_clearance_in':
					$this->oms_db->where_in('s.restrict_customer_clearance' , $filter_val);
					break;
				case 'restrict_customer_clearance':
					$this->oms_db->where('s.restrict_customer_clearance', $filter_val);
					break;
                case 'need_updateE':
                    $this->oms_db->where("i.need_update !='E'");
                    break;
				default:
					$this->oms_db->where('i.' . $filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select($select);
		$this->oms_db->from('channel_option_item_info i');
		$this->oms_db->join('virtual_item_detail d', 'd.virtual_item_id=i.virtual_item_id', 'LEFT');
		$this->oms_db->join('master_item_stock s', 's.master_item_id=d.master_item_id', 'LEFT');
		
		return $this->oms_db->get();
	}
	
}
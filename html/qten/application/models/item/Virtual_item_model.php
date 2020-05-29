<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 9:56
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Virtual_item_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function addVirtualItem($add_data)
    {
        $this->oms_db->insert('virtual_item', $add_data);
    }

    public function addVirtualItemDetail($add_data)
    {
        $this->oms_db->insert('virtual_item_detail', $add_data);
    }

    public function getVirtualItem($filter, $select='*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);

        $this->oms_db->from('virtual_item');

        $query = $this->oms_db->get();

        return $query->row_array();
    }

    public function getVirtualItems($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'virtual_item_id_in':
                    $this->oms_db->where_in('i.virtual_item_id', $filter_val);
                    break;
                case 'master_item_id_in':
                    $this->oms_db->where_in('d.master_item_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where('i.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select('i.*');

        $this->oms_db->from('virtual_item i');

        $this->oms_db->join('virtual_item_detail d', 'd.virtual_item_id=i.virtual_item_id', 'LEFT');

        $this->oms_db->group_by('i.virtual_item_id');

        return $this->oms_db->get();

    }

    public function getVirtualItemDetail($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'virtual_item_id_in':
                    $this->oms_db->where_in('virtual_item_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->from('virtual_item_detail');

        return  $this->oms_db->get();
    }
	
	public function getVirtualItemTotalQty($filter)
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				default:
					$this->oms_db->where($filter_key, $filter_val);
					break;
			}
		}
		$this->oms_db->select("SUM(quantity) as total_qty");
		
		$this->oms_db->from('virtual_item_detail');
		
		$this->oms_db->group_by('virtual_item_id');
		
		$query = $this->oms_db->get();
		
		return $query->row_array();
	}
}
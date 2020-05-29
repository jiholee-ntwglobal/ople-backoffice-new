<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 9:14
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_item_model extends CI_Model
{
    private $ntics_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics_db = $this->load->database('ntics', true);
    }
	
	public function getMasterItem($filter, $select='m.*')
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				case 'mfg_info' :
					$this->ntics_db->join('NTICS.dbo.N_MFG f', 'f.mfgcd = m.MfgCD', 'LEFT');
					break;
				default:
					$this->ntics_db->where('m.'.$filter_key, $filter_val);
					break;
			}
		}
		
		$this->ntics_db->select($select);
		$this->ntics_db->from('NTICS.dbo.N_MASTER_ITEM m');
		
		$query = $this->ntics_db->get();
		
		return $query->row_array();
	}

    public function getMasterItems($filter,
                                   $select = "
                                   m.master_item_id, 
                                   rtrim(m.upc) as upc, 
                                   concat(rtrim(m.ITEM_NAME), ' ',
                                        rtrim(m.[COUNT]), ' ',
                                        rtrim(m.[TYPE]), ' ',
                                        rtrim(m.POTENCY), ' ',
                                        rtrim(m.POTENCY_UNIT)) as item_name,
                                   m.currentqty,
                                   rtrim(m.location) as location,
                                   rtrim(f.mfgname) as mfgname")
    {
        foreach ($filter as $filter_key => $filter_val){
        switch ($filter_key){
            case 'master_item_id_in':
                /**
                 * 2019-10-15
                 * DB_query_builder.php 에서
                 * preg_match(): Compilation failed: regular expression is too large at offset 62578 발생
                 * IN () 에서 데이터가 너무 많아서 발생.....
                 * 우선 수정
                 */
//                $this->ntics_db->where_in('m.master_item_id', $filter_val);
                $this->ntics_db->group_start();
                $sale_ids_chunk = array_chunk($filter_val,3000);
                foreach($sale_ids_chunk as $sale_ids)
                {
                    $this->ntics_db->or_where_in('m.master_item_id', $sale_ids);
                }
                $this->ntics_db->group_end();

                break;
            case 'item_name_like':
                $this->ntics_db->like('m.ITEM_NAME', $filter_val);
                break;
            case 'upc_where_in':
                $this->ntics_db->where_in('m.upc', $filter_val);
                break;
            case 'search_brand':
                $this->ntics_db->like('f.mfgname', $filter_val);
                break;
            default:
                $this->ntics_db->where('m.' . $filter_key, $filter_val);
                break;
        }
    }

        $this->ntics_db->select($select);

        $this->ntics_db->from('NTICS.dbo.N_MASTER_ITEM m');

        $this->ntics_db->join('NTICS.dbo.N_MFG f', 'f.mfgcd = m.MfgCD', 'LEFT');

        return $this->ntics_db->get();

    }

    public function getMasterItems2($filter, $select='*', $group_by='')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'master_item_id_in':
                    $this->ntics_db->where_in('master_item_id', $filter_val);
                    break;
                case 'item_name_like':
                    $this->ntics_db->like('ITEM_NAME', $filter_val);
                    break;
                case 'upc_in':
                    $this->ntics_db->where_in('upc', $filter_val);
                    break;
                default:
                    $this->ntics_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->ntics_db->select($select);

        $this->ntics_db->from('NTICS.dbo.N_MASTER_ITEM');

        if($group_by != '') $this->ntics_db->group_by($group_by);

        return $this->ntics_db->get();
    }

    public function getShippingMapping($filter, $select = 'm.master_item_id, n.*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'master_item_id_in':
                    $this->ntics_db->where_in('m.master_item_id', $filter_val);
                    break;
                default:
                    $this->ntics_db->where('m.'.$filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics_db->select($select);
        $this->ntics_db->from('NTICS.dbo.N_MASTER_ITEM m');
        $this->ntics_db->join('ntshipping.dbo.NS_M01 n', 'm.upc=n.upc COLLATE Korean_Wansung_CI_AS', 'INNER', false);

        return $this->ntics_db->get();

        //$return = $this->ntics_db->get(); echo $this->ntics_db->last_query(); exit; return $return;
    }

    public function updateStockData($stock_qty, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->ntics_db->set('currentqty', 'isnull(currentqty,0)+(' . $stock_qty . ')', false);

        $this->ntics_db->update('NTICS.dbo.N_MASTER_ITEM');

    }

    public function addShippingMapping($addData)
	{
		$this->ntics_db->insert('ntshipping.dbo.NS_M01',$addData);
	}
	
	public function getNoShippinMapping($filter, $select='m.master_item_id')
	{
		foreach ($filter as $filter_key => $filter_val){
			switch ($filter_key){
				case 'master_item_id_in':
					$this->ntics_db->where_in('m.master_item_id', $filter_val);
					break;
				default:
					$this->ntics_db->where('m.'.$filter_key, $filter_val);
					break;
			}
		}
		$this->ntics_db->where('n.ID IS NULL');
		
		$this->ntics_db->select($select);
		$this->ntics_db->from('NTICS.dbo.N_MASTER_ITEM m');
		$this->ntics_db->join('ntshipping.dbo.NS_M01 n', 'm.upc=n.upc COLLATE Korean_Wansung_CI_AS', 'LEFT OUTER', false);
		
		return $this->ntics_db->get();
	}
}
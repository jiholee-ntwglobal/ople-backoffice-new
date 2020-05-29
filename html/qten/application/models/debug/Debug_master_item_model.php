<?php

/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-02-22
* Time : 오전 10:44
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_master_item_model extends CI_Model
{
    private $ntics_debug_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics_debug_db = $this->load->database('ntics_debug', true);
    }

    public function getMasterItem($filter, $select='m.*')
    {
        $join_tables = array();

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'mfg_info' :
                    $this->ntics_debug_db->join('NTICS_TEST.dbo.N_MFG f', 'f.mfgcd = m.MfgCD', 'LEFT');
                    break;
                case 'join_ns_m01':
                    if(!in_array('NS_M01', $join_tables))
                        array_push($join_tables, 'NS_M01');
                    break;
                default:
                    $this->ntics_debug_db->where('m.'.$filter_key, $filter_val);
                    break;
            }
        }

        $this->ntics_debug_db->select($select);
        $this->ntics_debug_db->from('NTICS_TEST.dbo.N_MASTER_ITEM m');

        foreach ($join_tables as $join_table){
            switch ($join_table){
                case 'join_ns_m01':
                    $this->ntics_db->join('ntshipping.dbo.NS_M01 n', 'm.upc=n.upc COLLATE Korean_Wansung_CI_AS', 'INNER', false);
                    break;

            }
        }

        $query = $this->ntics_debug_db->get();

        return $query->row_array();
    }




    public function updateStockData($stock_qty, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics_debug_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->ntics_debug_db->set('currentqty', 'isnull(currentqty,0)+(' . $stock_qty . ')', false);

        $this->ntics_debug_db->update('NTICS_TEST.dbo.N_MASTER_ITEM');

    }

    public function getShippingMapping($filter, $select = 'm.master_item_id, n.*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'master_item_id_in':
                    $this->ntics_debug_db->where_in('m.master_item_id', $filter_val);
                    break;
                default:
                    $this->ntics_debug_db->where('m.'.$filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics_debug_db->select($select);
        $this->ntics_debug_db->from('NTICS_TEST.dbo.N_MASTER_ITEM m');
        $this->ntics_debug_db->join('ntshipping.dbo.NS_M01 n', 'm.upc=n.upc COLLATE Korean_Wansung_CI_AS', 'INNER', false);

        return $this->ntics_debug_db->get();

        //$return = $this->ntics_db->get(); echo $this->ntics_db->last_query(); exit; return $return;
    }

    public function getNoShippinMapping($filter, $select='m.master_item_id')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'master_item_id_in':
                    $this->ntics_debug_db->where_in('m.master_item_id', $filter_val);
                    break;
                default:
                    $this->ntics_debug_db->where('m.'.$filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics_debug_db->where('n.ID IS NULL');

        $this->ntics_debug_db->select($select);
        $this->ntics_debug_db->from('NTICS_TEST.dbo.N_MASTER_ITEM m');
        $this->ntics_debug_db->join('ntshipping.dbo.NS_M01 n', 'm.upc=n.upc COLLATE Korean_Wansung_CI_AS', 'LEFT OUTER', false);

        return $this->ntics_debug_db->get();
    }

}
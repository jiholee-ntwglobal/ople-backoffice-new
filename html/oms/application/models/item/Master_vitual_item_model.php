<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 9:56
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_vitual_item_model extends CI_Model
{
    private $ntics;

    function __construct()
    {
        parent::__construct();

        $this->ntics = $this->load->database('ntics', true);
    }

    public function addVirtualItem($add_data)
    {
        $this->ntics->insert('virtual_item', $add_data);
    }

    public function addVirtualItemDetail($add_data)
    {
        $this->ntics->insert('virtual_item_detail', $add_data);
    }

    public function getVirtualItem($filter, $select='*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics->select($select);

        $this->ntics->from('virtual_item');

        $query = $this->ntics->get();

        return $query->row_array();
    }

    public function getVirtualItems($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'virtual_item_id_in':
                    $this->ntics->where_in('i.virtual_item_id', $filter_val);
                    break;
                case 'master_item_id_in':
                    $this->ntics->where_in('d.master_item_id', $filter_val);
                    break;
                default:
                    $this->ntics->where('i.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics->select('i.*');

        $this->ntics->from('virtual_item i');

        $this->ntics->join('virtual_item_detail d', 'd.virtual_item_id=i.virtual_item_id', 'LEFT');

        $this->ntics->group_by('i.virtual_item_id');

        return $this->ntics->get();

    }

    public function getVirtualItemDetail($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'virtual_item_id_in':
                    $this->ntics->where_in('virtual_item_id', $filter_val);
                    break;
                default:
                    $this->ntics->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics->from('virtual_item_detail');

        return  $this->ntics->get();
    }

    public function getVirtualDetailMasterMapping($filter, $select = null)
    {
        $select = ($select) ? : 'd.virtual_item_id, d.master_item_id, d.quantity, m.upc';

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'virtual_item_id_in':
                    $this->ntics->where_in('d.virtual_item_id', $filter_val);
                    break;
                default:
                    $this->ntics->where('d.'. $filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics->select($select);
        $this->ntics->from('NTICS.dbo.virtual_item_detail d');
        $this->ntics->join('NTICS.dbo.N_MASTER_ITEM m', 'd.master_item_id=m.master_item_id');

        return $this->ntics->get();

        //$return = $this->ntics_db->get(); echo $this->ntics_db->last_query(); exit; return $return;
    }

}
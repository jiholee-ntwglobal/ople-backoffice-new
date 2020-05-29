<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오전 10:12
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Soldout_exclude_item_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function add($add_data)
    {
        foreach ($add_data as $column => $value){
            switch ($column){
                case 'create_date':
                    $this->oms_db->set($column, $value, false);
                    break;
                default:
                    $this->oms_db->set($column, $value);
                    break;
            }
        }
        $this->oms_db->insert('soldout_exclude_item');

        return $this->oms_db->insert_id();

    }

    public function update($update_data , $filter){
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        foreach ($update_data as $column => $value){
            switch ($column){
                default:
                    $this->oms_db->set($column, $value);
                    break;
            }
        }

        $this->oms_db->update('soldout_exclude_item');

    }


    public function delete($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){

                case 'soldout_exclude_item_ids':
                    $this->oms_db->where_in('soldout_exclude_item_id',$filter_val);
                    break;

                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->delete('soldout_exclude_item');
    }

    public function countSoldoutExcludeItems($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){

                case 'limit': break;

                case 'soldout_exclude_item_ids':
                    $this->oms_db->where_in('soldout_exclude_item_id',$filter_val);
                    break;
                case 'order_by_uid':
                    $this->oms_db->order_by('soldout_exclude_item_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select('count(*) as cnt');
        $this->oms_db->from('soldout_exclude_item');

        $query = $this->oms_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);
    }



    public function getSoldoutExcludeItems($filter, $select='*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){

                case 'master_item_id_in':
                    $this->oms_db->where_in('master_item_id', $filter_val);
                    break;
                case 'limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;
                case 'order_by_uid':
                    $this->oms_db->order_by('soldout_exclude_item_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);
        $this->oms_db->from('soldout_exclude_item');

        return $this->oms_db->get();

    }

    public function getSoldoutExcludeItem($filter, $select='*')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){

                case 'master_item_id_in':
                    $this->oms_db->where_in('master_item_id', $filter_val);
                    break;
                case 'limit':
                    $this->oms_db->limit($filter_val[0], $filter_val[1]);
                    break;
                case 'order_by_uid':
                    $this->oms_db->order_by('soldout_exclude_item_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select($select);
        $this->oms_db->from('soldout_exclude_item');

        $result = $this->oms_db->get();
        return $result->row_array();

    }

    public function getSoldoutExcludeItemSums($filter=array())
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->select('master_item_id,sum(account_type) as sum_account_type');
        $this->oms_db->from('soldout_exclude_item');
        $this->oms_db->group_by('master_item_id');

        return $this->oms_db->get();
    }

    public function addBulk($add_data)
    {
        $this->oms_db->insert_batch('soldout_exclude_item',$add_data);
    }

    public function addSoldoutExcludeItemHistory($add_data){
        foreach ($add_data as $column => $value){
            switch ($column){
                case 'create_date':
                    $this->oms_db->set($column, $value, false);
                    break;
                default:
                    $this->oms_db->set($column, $value);
                    break;
            }
        }
        $this->oms_db->insert('soldout_exclude_item_history');
    }


}
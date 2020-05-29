<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-01-22
* Time : 오전 11:59
*/

defined('BASEPATH') or exit('No direct script access allowed');

class Ople_mapping_change_history extends CI_Model
{
    private $ntics_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics_db = $this->load->database('ntics', true);
    }

    public function getOpleMappingChangeHistorys($filter=array(),$select=null, $group_by=null, $order_by= array()){

        $select = ($select) ? : "h.*";
        $filter = ($filter) ? : array();
        $group_by = ($group_by) ? : "";
        $order_by = ($order_by) ? : array("cdate"=>"desc");
        $add_join_tables = array();

        foreach ($filter as $filter_key => $filter_val) {
            switch ($filter_key) {
                case 'fastoms_list_mode':
                    if (!in_array('fastoms_ople_mapping_change_history_action', $add_join_tables))
                        array_push($add_join_tables, 'fastoms_ople_mapping_change_history_action');
                    if (!in_array('ople_mapping', $add_join_tables))
                        array_push($add_join_tables, 'ople_mapping');
                    break;
                case 'action_id_NULL':
                    $this->ntics_db->where('a.ople_mapping_history_action_id IS NULL', "", false);
                    break;
                case 'page':
                    $this->ntics_db->limit(element('end',$filter_val), element('start',$filter_val));
                    break;
                default:
                    $this->ntics_db->where('h.' . $filter_key, $filter_val);
                    break;
            }
        }

        $this->ntics_db->select($select,false);
        $this->ntics_db->from('ople_mapping_change_history h');

        foreach ($add_join_tables as $join_table){
            switch ($join_table){
                case 'fastoms_ople_mapping_change_history_action':
                    $this->ntics_db->join('ople_mapping_change_history_action a', 'a.ople_mapping_history_id = h.id and a.channel_id = 4', 'LEFT', false);
                    break;
                case 'ople_mapping':
                    $this->ntics_db->join('ople_mapping m', 'h.it_id = m.it_id', 'LEFT');
                    break;
            }
        }
        if($group_by != '')
            $this->ntics_db->group_by($group_by);

        foreach ($order_by as $orderby => $direct){
            $this->ntics_db->order_by($orderby, $direct);
        }

        $result = $this->ntics_db->get();
        //   echo $this->ntics_db->last_query();
        return $result;

    }

    public function getOpleMappingChangeHistoryCount($filter,$select=null){
        $select = ($select) ? : "count(distinct h.id) as cnt";
        $add_join_tables = array();

        foreach ($filter as $filter_key => $filter_val) {
            switch ($filter_key) {
                case 'fastoms_list_mode':
                    if (!in_array('fastoms_ople_mapping_change_history_action', $add_join_tables))
                        array_push($add_join_tables, 'fastoms_ople_mapping_change_history_action');
                    if (!in_array('ople_mapping', $add_join_tables))
                        array_push($add_join_tables, 'ople_mapping');
                    break;
                case 'action_id_NULL':
                    $this->ntics_db->where('a.ople_mapping_history_action_id IS NULL', "", false);
                    break;
                default:
                    $this->ntics_db->where('h.' . $filter_key, $filter_val);
                    break;
            }
        }

        $this->ntics_db->select($select,false);
        $this->ntics_db->from('ople_mapping_change_history h');

        foreach ($add_join_tables as $join_table){
            switch ($join_table){
                case 'fastoms_ople_mapping_change_history_action':
                    $this->ntics_db->join('ople_mapping_change_history_action a', 'a.ople_mapping_history_id = h.id and a.channel_id = 4', 'LEFT', false);
                    break;
                case 'ople_mapping':
                    $this->ntics_db->join('ople_mapping m', 'h.it_id = m.it_id', 'LEFT');
                    break;
            }
        }

        $query = $this->ntics_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);
    }

    public function addMappingChangeAction($insert_data){
        foreach ($insert_data as $column => $value){
            switch ($column) {
                default:
                    $this->ntics_db->set($column, $value);
                    break;
            }
        }

        $this->ntics_db->insert('ople_mapping_change_history_action');

        return $this->ntics_db->insert_id();
    }

}
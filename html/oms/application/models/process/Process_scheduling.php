<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 4:44
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Process_scheduling extends CI_Model
{
    private $ntics_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    function addProcessScheduling($add_data){

        $this->oms_db->insert('process_scheduling', $add_data);

        return $this->oms_db->insert_id();

    }

    function getProcessSchedulingCount($filter, $select='count(*) cnt'){

        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'process_code_in' :
                    $this->oms_db->where_in("p.process_code",$filter_val);
                    break;
                case 'full_query' :
                    $this->oms_db->where($filter_val);
                    break;
                default:
                    $this->oms_db->where('p.' . $filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->select($select);
        $this->oms_db->from('process_scheduling p');

        $query = $this->oms_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);

    }

    public function updateProcessScheduling($update_date, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->update('process_scheduling', $update_date);
    }
}
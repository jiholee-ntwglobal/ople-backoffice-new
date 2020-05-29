<?php

/**
* Created by PhpStorm.
* User: ������
* Date : 2019-02-22
* Time : ���� 11:15
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_sales_model extends CI_Model
{
    private $ntics2_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics2_db = $this->load->database('data_col_debug_db', true);
    }

    public function countSalesData($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics2_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->ntics2_db->select('count(*) as cnt');
        $this->ntics2_db->from('NTICS_TEST.dbo.N_SALES_ITEM');
        $query = $this->ntics2_db->get();

        $result = $query->row_array();

        return element('cnt', $result, 0);

    }

    public function addSalesData($sales_data)
    {
        $this->ntics2_db->insert('NTICS_TEST.dbo.N_SALES_ITEM', $sales_data);

    }

    public function updateSalesData($update_data, $filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics2_db->where($filter_key, $filter_val);
                    break;
            }
        }

        foreach ($update_data as $column => $value){
            switch ($column){
                case 'upc_qty':case 'it_id_qty':
                $this->ntics2_db->set($column, $column . '+(' . $value . ')', false);
                break;
                default:
                    $this->ntics2_db->set($column, $value);
                    break;
            }
        }

        $this->ntics2_db->update('NTICS_TEST.dbo.N_SALES_ITEM');

    }

}
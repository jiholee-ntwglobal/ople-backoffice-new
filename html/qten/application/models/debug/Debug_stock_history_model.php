<?php

/**
* Created by PhpStorm.
* User: ������
* Date : 2019-02-22
* Time : ���� 11:12
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_stock_history_model extends CI_Model
{
    private $ntics2_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics2_db = $this->load->database('data_col_debug_db', true);
    }

    public function addStockHistory($add_data)
    {
        $this->ntics2_db->insert('NTICS_TEST.dbo.sales_history_detail2', $add_data);
    }

}
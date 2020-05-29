<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오전 12:20
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock_history_model extends CI_Model
{
    private $ntics2_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics2_db = $this->load->database('ntics2', true);
    }

    public function addStockHistory($add_data)
    {
        $this->ntics2_db->insert('NTICS.dbo.sales_history_detail2', $add_data);
    }

}
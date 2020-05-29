<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-06-04
 * Time: 오전 10:24
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Clearance_ban_item_model extends CI_Model
{
    private $ntics2_db;

    function __construct()
    {
        parent::__construct();

        $this->ntics2_db = $this->load->database('ntics2', true);
    }

    public function getClearanceBanMasterItemId($filter = array(), $select='i.master_item_id,c.fg')
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                default:
                    $this->ntics2_db->where('c.' . $filter_key, $filter_val);
                    break;
            }
        }
        $this->ntics2_db->select($select);
        $this->ntics2_db->from('customs_clearance_ban c');
        $this->ntics2_db->join('N_MASTER_ITEM i', 'c.upc=i.upc');

        return $this->ntics2_db->get();

    }

}
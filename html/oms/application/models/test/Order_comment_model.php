<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 8:59
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Order_comment_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function addOrderComment($add_data)
    {
        $this->oms_db->insert('order_comment', $add_data);

        return $this->oms_db->insert_id();
    }

    public function getOrderComment($filter)
    {
        foreach ($filter as $filter_key => $filter_val ){
            switch ($filter_key) {
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }

        $this->oms_db->from('order_comment');

        $this->oms_db->order_by('order_comment_id', 'desc');

        return $this->oms_db->get();
    }

}
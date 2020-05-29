<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2017-09-06
 * Time: 오후 3:14
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customs_clearance_model extends CI_Model
{

    function __construct()
    {

        parent::__construct();

        $this->data_call_db = $this->load->database('ntics2', TRUE);

    }

    public function getBanProductUPC($filter=array()){

        if(count($filter) > 0 ) $this->data_call_db->where($filter);

        $this->data_call_db->select('upc');
        $this->data_call_db->from('[NTICS].[dbo].customs_clearance_ban');
        $this->data_call_db->group_by('upc');

        return $this->data_call_db->get();

    }
}
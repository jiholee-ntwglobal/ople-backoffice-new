<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ApiItemModel extends CI_Model {

    private $oms_db;

    function __construct() {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function searchProductNo($productNo) {
        $this->oms_db->select( " count(*) as cnt " );
        $this->oms_db->from( " channel_item_info " );
        $this->oms_db->where(" channel_item_code ", $productNo);

        $result = $this->oms_db->get()->row_array();

        return element('cnt', $result, 0);
    }

}
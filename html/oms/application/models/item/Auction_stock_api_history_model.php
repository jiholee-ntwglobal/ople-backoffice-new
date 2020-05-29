<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2020-02-03
 * Time : 오후 5:43
 */

defined('BASEPATH') or exit('No direct script access allowed');


class Auction_stock_api_history_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function AddAuctionStockApiHistory($insert_data)
    {
        foreach ($insert_data as $column=>$val){
            switch ($column){
                case 'create_date' :
                    $this->oms_db->set($column, $val, false);
                    break;
                default:
                    $this->oms_db->set($column, $val);
                    break;
            }
        }
        $this->oms_db->insert('auction_stock_api_history');

        return $this->oms_db->insert_id();

    }
    
}
?>
<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2018-11-19
* Time : 오후 3:58
*/

defined('BASEPATH') or exit('No direct script access allowed');

class Ople_item_additional_info_model extends CI_Model
{
    private $oms_db;

    function __construct()
    {
        parent::__construct();

        $this->oms_db = $this->load->database('default', true);
    }

    public function resetOpleItemAdditionalInfo(){
        $this->oms_db->empty_table('ople_item_additional_info');

    }

    public function insertOpleItemAdditionalInfo($insert_data){
        $this->oms_db->insert('ople_item_additional_info', $insert_data);

    }

    public function insertBulkOpleItemAdditionalInfo($bulk_data){
        $this->oms_db->insert_batch('ople_item_additional_info', $bulk_data);
    }

    public function replaceOpleItemAdditionalInfo($data)
    {
        $this->oms_db->replace('ople_item_additional_info', $data);
    }

    public function getOpleItemAdditionalInfo($filter)
    {
        foreach ($filter as $filter_key => $filter_val){
            switch ($filter_key){
                case 'master_item_id_in':
                    $this->oms_db->where_in('master_item_id', $filter_val);
                    break;
                default:
                    $this->oms_db->where($filter_key, $filter_val);
                    break;
            }
        }
        $this->oms_db->from('ople_item_additional_info');

        return $this->oms_db->get();

    }


    public function resetWeightTypeInfo(){
        $this->oms_db->empty_table('yc4_weight_type_info');

    }

    public function insertBulkWeightTypeInfo($bulk_data){
        $this->oms_db->insert_batch('yc4_weight_type_info', $bulk_data);

    }
}
?>
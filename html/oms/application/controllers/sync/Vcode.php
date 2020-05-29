<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-30
 * Time: 오후 1:36
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Vcode extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->library('manager_auth');
        $this->load->model('item/master_vitual_item_model');
        $this->load->model('item/virtual_item_model');

    }

    public function index()
    {
        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(31);

        $rest = $history_api->sendHistoryID();

        echo "start";

        $filter = array();

        $master_virtul_max_id =$this->master_vitual_item_model->getVirtualItem($filter ,'max(virtual_item_id) virtual_item_id');
        $oms_virtul_max_id =$this->virtual_item_model->getVirtualItem($filter ,'max(virtual_item_id) virtual_item_id');

        if(!$master_virtul_max_id && !$oms_virtul_max_id) {
            echo "error1";
            return;
        }

        if((!element('virtual_item_id',$oms_virtul_max_id,false)  || !element('virtual_item_id',$master_virtul_max_id,false)) || (element('virtual_item_id',$oms_virtul_max_id,false)== element('virtual_item_id',$master_virtul_max_id,false)) ) {
             echo "END1";
            return;
        }

        if( element('virtual_item_id',$oms_virtul_max_id,false) >= element('virtual_item_id',$master_virtul_max_id,false)  ){

            echo 'oms_virtul_max_id is greater than master_virtul_max_id';
            return;

        }

        $master_virtul_max =  (int)element('virtual_item_id',$master_virtul_max_id,false);

        $oms_virtul_max =  (int)element('virtual_item_id',$oms_virtul_max_id,false);

        $where_in = array();

        for($i = $oms_virtul_max+1 ; $i<=$master_virtul_max ; $i=$i+1 ){

            $master_vitual_data =$this->master_vitual_item_model->getVirtualItem(array('virtual_item_id'=>$i));
			if(!$master_vitual_data) continue;
			
            $this->virtual_item_model->addVirtualItem($master_vitual_data);

            array_push($where_in,$i);

        }

        $master_vitual_Detail_result =$this->master_vitual_item_model->getVirtualItemDetail(array('virtual_item_id_in'=>$where_in));

        foreach ($master_vitual_Detail_result->result_array() as $detail_value){

            $this->virtual_item_model->addVirtualItemDetail($detail_value);

        }

        echo 'END';
        return;

    }
}
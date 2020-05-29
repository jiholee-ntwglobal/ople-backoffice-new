<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-30
 * Time: 오후 1:36
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Masterid_weight_healthqty extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('item/ople_item_model');
        $this->load->model('item/master_item_model');
        $this->load->model('item/item_additional_info_model');
    }

    public function index()
    {

        echo 'START'.PHP_EOL;

        $filter = array();
        $select  = 'c.upc, a.it_health_cnt, b.weight';
        $ople_item_health_cnt_weight = $this->ople_item_model->get_item_health_cnt_weight($filter ,$select);

        if(!$ople_item_health_cnt_weight){
            echo 'error1';
            return;
        }

        $data_save = array();
        $upc_where_in =array();

        foreach($ople_item_health_cnt_weight->result_array() as $item){

            if(element('upc',$item,false)){

                array_push($upc_where_in,element('upc',$item,false));

                $data_save[element('upc',$item,false)]['health_food_qty']=element('it_health_cnt',$item,'0')==NULL ?'0': element('it_health_cnt',$item,'0');
                $data_save[element('upc',$item,false)]['weight']= element('weight',$item,'0')==NULL ?'0': element('weight',$item,'0');

            }

        }

        $upc_where_in = array_chunk($upc_where_in,100);

        if(count($upc_where_in)<1){
            echo 'error2';
            return;
        }

        foreach($upc_where_in as $upcs){
            $filter = array('upc_where_in'=>$upcs );
            $ata=$this->master_item_model->getMasterItems($filter,'m.master_item_id,rtrim(m.upc) as upc');

            foreach($ata->result_array() as $items){

                if(isset($data_save[$items['upc']])){

                    $this->item_additional_info_model->replaceItemAdditionalInfo(
                        array(
                            'master_item_id'=>$items['master_item_id'],
                            'weight'=>$data_save[$items['upc']]['weight'],
                            'health_food_qty'=>$data_save[$items['upc']]['health_food_qty']
                        )
                    );

                }

            }
        }

        echo 'END';

        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(30);

        $rest = $history_api->sendHistoryID();
        return;
    }

    public function newWeightData(){

        $this->load->model('item/ople_item_additional_info_model');


        echo 'START'.PHP_EOL;

        //ople_item_additional_info table 동기화
        $filter = array();
        $select  = 'c.upc, a.it_health_cnt, b.weight, IF(b.weight_type_id is null, 0, b.weight_type_id) as weight_type_id';
        $ople_item_health_cnt_weight = $this->ople_item_model->get_item_health_weight_info($filter ,$select);

        if(!$ople_item_health_cnt_weight){
            echo 'error1';
            return;
        }

        $data_save = array();
        $upc_where_in =array();

        foreach($ople_item_health_cnt_weight->result_array() as $item){

            if(element('upc',$item,false)){
                array_push($upc_where_in,element('upc',$item,false));

                $data_save[element('upc',$item,false)]['health_cnt']=element('it_health_cnt',$item,'0')==NULL ?'0': element('it_health_cnt',$item,'0');
                $data_save[element('upc',$item,false)]['weight']= element('weight',$item,'0')==NULL ?'0': element('weight',$item,'0');
                $data_save[element('upc',$item,false)]['weight_type_id']=element('weight_type_id',$item,'0')==NULL ?'0': element('weight_type_id',$item,'0');

            }

        }

        $upc_where_in = array_unique($upc_where_in);

        $upc_where_in = array_chunk($upc_where_in,100);

        if(count($upc_where_in)<1){
            echo 'error2';
            return;
        }

        $bulk_data2 = array();
        foreach($upc_where_in as $upcs){
            $filter = array('upc_where_in'=>$upcs );
            $ata=$this->master_item_model->getMasterItems($filter,'m.master_item_id,rtrim(m.upc) as upc');

            foreach($ata->result_array() as $items){

                if(isset($data_save[$items['upc']])){

                    array_push($bulk_data2, array(
                        'master_item_id'=>$items['master_item_id'],
                        'weight'=>$data_save[$items['upc']]['weight'],
                        'weight_type_id'=>$data_save[$items['upc']]['weight_type_id'],
                        'health_cnt'=>$data_save[$items['upc']]['health_cnt']
                    ));
                }

            }
        }
		
        // 별도 지정상품 건기식 무게 입력
		array_push($bulk_data2, array(
			'master_item_id'=>18184,
			'weight'=>0,
			'weight_type_id'=>0,
			'health_cnt'=>2
		));
	
		if(count($bulk_data2)>0){
            $this->ople_item_additional_info_model->resetOpleItemAdditionalInfo();
            $this->ople_item_additional_info_model->insertBulkOpleItemAdditionalInfo($bulk_data2);
        }

        

        //yc4_weight_type_info 동기화
        $this->ople_item_additional_info_model->resetWeightTypeInfo();

        $ople_weight_type_result = $this->ople_item_model->getWeightTypeInfo("weight_type_id, type_name, weight_limit, warning_msg");


        $bulk_data = array();
        foreach ($ople_weight_type_result->result_array() as $ople_weight_type_info){
            array_push($bulk_data, $ople_weight_type_info);
        }

        if(count($bulk_data)>0) $this->ople_item_additional_info_model->insertBulkWeightTypeInfo($bulk_data);
        echo 'END';

        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(43);

        $rest = $history_api->sendHistoryID();

        return;

    }

}
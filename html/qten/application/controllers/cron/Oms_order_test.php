<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2018-11-21
* Time : 오후 2:43
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Oms_order_test extends CI_Controller
{
    private $sales_channel_config;
    private $api_key;
    private $item_additional_info	= array();
    private $add_item_mapping_info	= array();

    public function __construct()
    {
        parent::__construct();

        $this->load->model('order/order_model');
        $this->load->model('order/order_item_model');
        $this->load->model('channel/channel_info_model');
        $this->load->model('stock/master_item_stock_model');

    }

    public function vcode_test()
    {
        $item_add_info	= $this->getItemAddInfo(17266);
        echo "<pre>";
        var_dump($item_add_info);
        echo "</pre>";

    }
    public function test(){
    //"4055","2342",
        $order_result = $this->order_model->getOrders(array("order_id_in"=>array("650","2342", "7")));
        $order = array();

        foreach ($order_result->result_array() as $order_info){
            $order_item_result = $this->order_item_model->getOrderItems(array("order_id_in"=>element("order_id", $order_info)));
            $order[element('package_no', $order_info)]['item_addition_info']	= array(
                'weight'		=> array(),
                'weight_over_fg'     => false
            );

           foreach ($order_item_result->result_array() as $order_item_info){

               $item_add_info	= $this->getItemAddInfo(element('virtual_item_id',$order_item_info));

                if(element('weight_type_id',$item_add_info)>0) {
                    $order[element('package_no', $order_info)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] = (isset($order[element('package_no', $order_info)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)])) ? $order[element('package_no', $order_info)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] : 0 ;
                    $order[element('package_no', $order_info)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] += element('weight', $item_add_info) * element('qty', $order_item_info);
                    if($order[element('package_no', $order_info)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]>=element('weight_limit', $item_add_info)) $order[element('package_no', $order_info)]['item_addition_info']['weight_over_fg'] = true;
                    echo $order[element('package_no', $order_info)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]."<br>";
                }
           }
        }
        echo "<pre>";
        var_dump($order);
        echo "</pre>";
        foreach($order as $dlvNo => $info){

            if(element('weight_over_fg',element('item_addition_info',$info)) == true){
                echo "무게 초과://" .$dlvNo."<br>";
            }
        }
    }

    private function getItemAddInfo($virtual_item_id)
    {
        if(trim($virtual_item_id) == ''){
            return array('weight'=> 0, 'health_cnt'=> 0, 'virtual_item_id'=>0);
        }
        if(element($virtual_item_id, $this->item_additional_info, false) !== false){
            return element($virtual_item_id, $this->item_additional_info);
        }
        $return_arr	= array(
            'weight'		=> 0
        ,	'health_cnt'	=> 0
        );
        $item_add_info	= $this->order_item_model->getOpleItemAddtionalInfo($virtual_item_id);
        $return_arr['weight']			+= element('weight', $item_add_info, 0);
        $return_arr['weight_type_id']		= element('weight_type_id', $item_add_info, 0);
        $return_arr['weight_limit']		= element('weight_limit', $item_add_info, 0);
        $return_arr['health_cnt']		+= element('health_cnt', $item_add_info, 0);
        $return_arr['virtual_item_id']	= element('virtual_item_id', $item_add_info, 0);


        array_merge($this->item_additional_info,array($virtual_item_id => $return_arr));
        return $return_arr;
    }

    private function getAddItemMappingInfo($channel_product_no, $add_option_name){
        if(element($add_option_name, element($channel_product_no, $this->add_item_mapping_info, array()),false) !== false){
            return element($add_option_name, element($channel_product_no,$this->add_item_mapping_info));
        }
//		$this->load->model('item/channel_additional_item_mapping_model');
//		$addtional_item_info	= $this->channel_additional_item_mapping_model->getMappingInfo(array('channel_product_no'=>$channel_product_no, 'option_name'=>$add_option_name));

        $this->load->model('item/channel_option_item_info_model');
        $addtional_item_info	= $this->channel_option_item_info_model->getChannelOptionItemInfo(array('channel_item_code'=>$channel_product_no, 'option_name'=>$add_option_name));

        if(!$addtional_item_info){
            $v_code	= '';
        }else {
            if (element('virtual_item_id', $addtional_item_info, false) !== false) {
                $v_code	= element('virtual_item_id', $addtional_item_info);
            }else{
                $v_code	= '';
            }
        }

        if (element($channel_product_no, $this->add_item_mapping_info, false) !== false) {
            array_merge($this->add_item_mapping_info[$channel_product_no], array($add_option_name => $v_code));
        } else {
            array_merge($this->add_item_mapping_info, array($channel_product_no => array($add_option_name => $v_code)));
        }
        return $v_code;
    }

    public function test_clean(){
        $clearance_fg = false;
        $vcode = 18555;
        $channel_type = 2;
        //통관불가 상품인지 체크하기
        if($vcode!=0){
            $item_info =  $this->master_item_stock_model->getVirtualMasterItemStock(array("virtual_item_id"=>$vcode));

            echo "<pre>";
            var_dump($item_info);
            echo "</pre>";

            if(!empty($item_info)) {
                if ($channel_type == 1) {
                    if (element('restrict_customer_clearance', $item_info) == "1") {
                        echo "여기";
                        $clearance_fg = true;
                        // $order[element('PackNo', $request_order)]['item_addition_info']['clearance_fg']	= true;
                    }
                } else {
                    if (element('restrict_customer_clearance', $item_info) != "0") {
                        //   $order[element('PackNo', $request_order)]['item_addition_info']['clearance_fg']	= true;
                    }
                }
            }

        }


        echo $clearance_fg;
    }
}
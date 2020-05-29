<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오후 7:15
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Shipping extends CI_Controller
{
	private $vcode_qty_arr	= array();
	
    public function __construct()
    {
        parent::__construct();
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/virtual_item_model');
        $this->load->model('item/master_item_model');
        $this->load->model('order/order_model');
        $this->load->model('order/order_item_model');
        $this->load->model('shipping/ople_shipping_model');
    }

    public function test()
    {
		$order_item_result = $this->order_item_model->getOrderItems(array('order_id' => 54470));
		$this->vcode_qty_arr	= array(18190=>'3');
		
		foreach ($order_item_result->result_array() as $order_item_data){
			if(array_key_exists(element('virtual_item_id', $order_item_data), $this->vcode_qty_arr)) {
				$set_qty	= element(element('virtual_item_id', $order_item_data),$this->vcode_qty_arr);
			}else{
				$set_qty	= element('total_qty',$this->virtual_item_model->getVirtualItemTotalQty(array('virtual_item_id'=>element('virtual_item_id', $order_item_data))));
				$this->vcode_qty_arr	= $this->vcode_qty_arr + array(element('virtual_item_id', $order_item_data)=>$set_qty);
			}
		
			echo $set_qty.PHP_EOL;
			var_dump($order_item_data);

//			$order_master_item_arr[element('order_item_id', $order_item_data)] =
//                array(
//                    'virtual_item_id' => element('virtual_item_id', $order_item_data),
//                    'order_item_id' => element('order_item_id', $order_item_data),
//                    'order_id' => element('order_id', $order_item_data),
//                    'channel_product_no' => element('channel_product_no', $order_item_data),
//                    'qty' => element('qty', $order_item_data));
		
		}

		var_dump($this->vcode_qty_arr);
//        $virtual_item_ids = array_column($order_master_item_arr, 'virtual_item_id');
//
//        $virtual_item_arr = array();
//
//        $master_item_ids = array();
//
//        $virtual_item_detail_result = $this->virtual_item_model->getVirtualItemDetail(array('virtual_item_id_in' => $virtual_item_ids));
//
//        foreach ($virtual_item_detail_result->result_array() as $virtual_item_detail_data){
//            if(!array_key_exists(element('virtual_item_id', $virtual_item_detail_data), $virtual_item_arr))
//                $virtual_item_arr[element('virtual_item_id', $virtual_item_detail_data)] = array();
//            array_push($virtual_item_arr[element('virtual_item_id', $virtual_item_detail_data)], $virtual_item_detail_data);
//            if(!in_array(element('master_item_id', $virtual_item_detail_data), $master_item_ids))
//                array_push($master_item_ids, element('master_item_id', $virtual_item_detail_data));
//        }
//
//        $shipping_mapping_arr = array();
//
//        $shipping_mapping_result = $this->master_item_model->getShippingMapping(array('master_item_id_in' => $master_item_ids));
//
//        foreach ($shipping_mapping_result->result_array() as $shipping_mapping_data){
//            $shipping_mapping_arr[element('master_item_id', $shipping_mapping_data)] = $shipping_mapping_data;
//        }
//
//        foreach ($order_master_item_arr as $order_item_id => $order_master_item) {
//
//            $virtual_items = element(element('virtual_item_id', $order_master_item), $virtual_item_arr);
//
//            foreach ($virtual_items as $virtual_item) {
//
//                $shipping_mapping_data = element(element('master_item_id', $virtual_item), $shipping_mapping_arr);
//
//                $order_qty = element('qty', $order_master_item, 1) * element('quantity', $virtual_item, 1);
//
//                for ($k = 0; $k < $order_qty; $k++) {
//                    $ns_s03_data = array(
//                        'ct_id' => element('order_item_id', $order_master_item),
//                        'on_uid' => element('order_id', $order_master_item),
//                        'it_id' => element('channel_product_no', $order_master_item),
//                        'id' => element('ID', $shipping_mapping_data),
//                        'upc' => trim(element('UPC', $shipping_mapping_data)),
//                        'mfgname' => trim(element('MFGNAME', $shipping_mapping_data)),
//                        'itemdesc' => trim(element('ITEMDESC', $shipping_mapping_data)),
//                        'size' => trim(element('SIZE', $shipping_mapping_data)),
//                        'wp' => trim(element('WP', $shipping_mapping_data)),
//                        'invoiceokname' => trim(element('INVOICEOKNAME', $shipping_mapping_data)),
//                        'invoiceokprice' => trim(element('INVOICEOKPRICE', $shipping_mapping_data)),
//                    );
//
//                    print_r($ns_s03_data);
//                }
//            }
//        }
    }

    public function transfer_shipping_order()
    {
        $channel_info_arr = array();

        $channel_info_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            $channel_info_arr[element('channel_id', $channel_info_data)] = $channel_info_data;
        }

        $order_filter = array(
			'no_validate_error'	=> true
		,	'status'			=> 3
		,	'join_address'		=> true
		,	'join_amount'		=> true
        );

        $order_result = $this->order_model->getOrders(
            $order_filter,
            'o.*, 
            a.buyer_name, a.buyer_tel1, a.buyer_tel2, a.receiver_name, a.receiver_tel1, a.receiver_tel2, a.zipcode, a.addr1, a.addr2, a.comment,
            m.total_amount, m.shipping_amount');

        foreach ($order_result->result_array() as $order_data){

            $channel = element(element('channel_id', $order_data), $channel_info_arr);

            $order_data['shipping_order_code'] = element('shipping_order_code_prefix', $channel). str_pad(element('order_id', $order_data), 9, '0', STR_PAD_LEFT);

            $ns_s01_data = $this->generateNs01Data($order_data,element('shipping_order_code_prefix', $channel));

            if($this->ople_shipping_model->countNs01(array('od_id' => element('od_id', $ns_s01_data))) > 0) continue;

            $this->ople_shipping_model->addNs01($ns_s01_data);

            $order_item_result = $this->order_item_model->getOrderItems(array('order_id' => element('order_id', $order_data), 'no_cancel' => 1, "cancel_flag"=>0));

            $order_master_item_arr = array();

            foreach ($order_item_result->result_array() as $order_item_data){
				if(array_key_exists(element('virtual_item_id', $order_item_data), $this->vcode_qty_arr)) {
					$set_qty	= element(element('virtual_item_id', $order_item_data),$this->vcode_qty_arr);
				}else{
					$set_qty	= element('total_qty',$this->virtual_item_model->getVirtualItemTotalQty(array('virtual_item_id'=>element('virtual_item_id', $order_item_data))));
					$this->vcode_qty_arr	= $this->vcode_qty_arr + array(element('virtual_item_id', $order_item_data)=>$set_qty);
				}
				$set_qty	= $set_qty ? $set_qty : 1;
				$ns_s02_data =
                    array(
                        'ct_id' => element('order_item_id', $order_item_data),
                        'on_uid' => element('shipping_order_code', $order_data),
                        'it_id' => element('channel_product_no', $order_item_data),
                        'ct_qty' => element('qty', $order_item_data),
                        'ct_amount' => round(element('unit_amount', $order_item_data)/$set_qty)
                    );

                $this->ople_shipping_model->addNs02($ns_s02_data);

                $order_master_item_arr[element('order_item_id', $order_item_data)] =
                    array(
                        'virtual_item_id' => element('virtual_item_id', $order_item_data),
                        'order_item_id' => element('order_item_id', $order_item_data),
                        'order_id' => element('shipping_order_code', $order_data),
                        'channel_product_no' => element('channel_product_no', $order_item_data),
                        'qty' => element('qty', $order_item_data));
            }

            $virtual_item_ids = array_column($order_master_item_arr, 'virtual_item_id');

            $virtual_item_arr = array();

            $master_item_ids = array();

            $virtual_item_detail_result = $this->virtual_item_model->getVirtualItemDetail(array('virtual_item_id_in' => $virtual_item_ids));

            foreach ($virtual_item_detail_result->result_array() as $virtual_item_detail_data){
                if(!array_key_exists(element('virtual_item_id', $virtual_item_detail_data), $virtual_item_arr))
                    $virtual_item_arr[element('virtual_item_id', $virtual_item_detail_data)] = array();
                array_push($virtual_item_arr[element('virtual_item_id', $virtual_item_detail_data)], $virtual_item_detail_data);
                if(!in_array(element('master_item_id', $virtual_item_detail_data), $master_item_ids))
                    array_push($master_item_ids, element('master_item_id', $virtual_item_detail_data));
            }

//            $shipping_mapping_arr = array();
//            $shipping_mapping_result = $this->master_item_model->getShippingMapping(array('master_item_id_in' => $master_item_ids));
//            foreach ($shipping_mapping_result->result_array() as $shipping_mapping_data){
//                $shipping_mapping_arr[element('master_item_id', $shipping_mapping_data)] = $shipping_mapping_data;
//            }
			$shipping_mapping_arr	= $this->getShippinMappingData($master_item_ids);
            
            foreach ($order_master_item_arr as $order_item_id => $order_master_item){

                $virtual_items = element(element('virtual_item_id', $order_master_item), $virtual_item_arr);

                echo element('order_id', $order_master_item)."|||||||||||".element('channel_product_no', $order_master_item);

                foreach ($virtual_items as $virtual_item){

                    $shipping_mapping_data = element(element('master_item_id', $virtual_item), $shipping_mapping_arr);

                    $order_qty = element('qty', $order_master_item, 1) * element('quantity', $virtual_item, 1);

                    for($k = 0; $k < $order_qty; $k++){
                        $ns_s03_data = array(
                            'ct_id' => element('order_item_id', $order_master_item),
                            'on_uid' => element('order_id', $order_master_item),
                            'it_id' => element('channel_product_no', $order_master_item),
                            'id' => element('ID', $shipping_mapping_data),
                            'upc' => trim(element('UPC', $shipping_mapping_data)),
                            'mfgname' => trim(element('MFGNAME', $shipping_mapping_data)),
                            'itemdesc' => trim(element('ITEMDESC', $shipping_mapping_data)),
                            'size' => trim(element('SIZE', $shipping_mapping_data)),
                            'wp' => trim(element('WP', $shipping_mapping_data)),
                            'invoiceokname' => trim(element('INVOICEOKNAME', $shipping_mapping_data)),
                            'invoiceokprice' => trim(element('INVOICEOKPRICE', $shipping_mapping_data)),
                        );
                        //print_r($ns_s03_data);

                        $this->ople_shipping_model->addNs03($ns_s03_data);
                    }

                }
            }

            $this->order_model->updateOrder(array('status' => 5), array('order_id' => element('order_id', $order_data)));

        }
        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(38);

        $rest = $history_api->sendHistoryID();
    }

    private function generateNs01Data($order_data,$channel_code)
    {
        $data['site'] = 'OKFLEX';
        $data['type'] = 'New';
        $data['on_uid'] = element('shipping_order_code', $order_data);
        $data['od_id'] = element('shipping_order_code', $order_data);
        if($channel_code == 'A' || $channel_code == 'G'){
            $data['mb_id'] = 'OPEN_MARKET_ORDER';
        }else{
            $data['mb_id'] = '';
        }

        if($channel_code == 'A' || $channel_code == 'B'){
            $data['market_type'] = 'A';
        }elseif($channel_code == 'G' || $channel_code == 'H'){
            $data['market_type'] = 'G';
        }

        $data['od_name'] = ms_escape_string(element('buyer_name', $order_data));
        $data['od_email'] = '';
        $data['od_tel'] = element('buyer_tel2', $order_data);
        $data['od_hp'] = element('buyer_tel1', $order_data);
        $data['od_zip1'] = '';
        $data['od_zip2'] = '';
        $data['od_addr1'] = '';
        $data['od_addr2'] = '';
        $data['od_b_name'] = ms_escape_string(element('receiver_name', $order_data));
        $data['od_b_tel'] = element('receiver_tel2', $order_data);
        $data['od_b_hp'] = element('receiver_tel1', $order_data);
        $data['od_b_zip1'] = substr(element('zipcode', $order_data), 0, 3);
        $data['od_b_zip2'] = substr(element('zipcode', $order_data) , 3, 3);
        $data['od_b_addr1'] = ms_escape_string(element('addr1', $order_data));
        $data['od_b_addr2'] = ms_escape_string(element('addr2', $order_data));
        $data['od_memo'] = element('comment', $order_data);
        $data['od_time'] = element('order_date', $order_data);
        $data['od_ip'] = '66.209.90.21';
        $data['od_jumin'] = element('customer_number', $order_data);
        $data['od_receipt_card'] = element('total_amount', $order_data);
        $data['od_send_cost'] = element('shipping_amount', $order_data);
        $data['od_receipt_bank'] = 0;
        $data['od_receipt_point'] = 0;
        $data['status'] = 1;
        $data['cdate'] = 'CONVERT(CHAR(8), GETDATE(), 112)';
        $data['od_shop_memo'] = '';

        return $data;

    }
	
//    public function test_shipping_data(){
//    	var_dump($this->getShippinMappingData(array('17804', '7457')));
//	}

	private function getShippinMappingData($master_item_ids)
	{
		$no_mapping_ids	= array();
		$no_mapping_result	= $this->master_item_model->getNoShippinMapping(array('master_item_id_in' => $master_item_ids));
		foreach($no_mapping_result->result_array() as $no_mapping_data){
			if(!in_array(element('master_item_id',$no_mapping_data),$no_mapping_ids)) array_push($no_mapping_ids, element('master_item_id',$no_mapping_data));
		}

		if(count($no_mapping_ids) > 0){
			
			foreach($no_mapping_ids as $master_item_id){
				$item_data	= $this->master_item_model->getMasterItem(
					array('master_item_id' =>$master_item_id, 'mfg_info'=>'1')
				,	'm.upc, f.mfgname, m.item_name, m.potency, m.potency_unit, m.count, m.type, m.WHOLESALE_PRICE AS wp');
				$item_name	= trim(element('mfgname',$item_data)).' '.trim(element('item_name',$item_data));
				
				if(trim(element('potency',$item_data)) != '')		 $item_name	.= ' '.trim(element('potency',$item_data));
				if(trim(element('potency_unit',$item_data)) != '')	 $item_name	.= ' '.trim(element('potency_unit',$item_data));
				if(trim(element('count',$item_data)) != '')			 $item_name	.= ' '.trim(element('count',$item_data));
				if(trim(element('type',$item_data)) != '')			 $item_name	.= ' '.trim(element('type',$item_data));
				
				$this->master_item_model->addShippingMapping(array(
					'UPC'			=> trim(element('upc',$item_data))
				,	'MFGNAME'		=> trim(element('mfgname',$item_data))
				,	'ITEMDESC'		=> $item_name
				,	'SIZE'			=> trim(element('count',$item_data)).' '.trim(element('type',$item_data))
				,	'WP'			=> trim(element('wp',$item_data))
				,	'INVOICEOKNAME'	=> $item_name
				));
			}
		}

		$shipping_mapping_result = $this->master_item_model->getShippingMapping(array('master_item_id_in' => $master_item_ids));
		foreach ($shipping_mapping_result->result_array() as $shipping_mapping_data){
			$shipping_mapping_arr[element('master_item_id', $shipping_mapping_data)] = $shipping_mapping_data;
		}

		return $shipping_mapping_arr;
	}
}
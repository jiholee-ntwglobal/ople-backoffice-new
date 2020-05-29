<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-05-30
 * File: Oms_order.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Oms_order20190711 extends CI_Controller
{
	private $sales_channel_config;
	private $api_key;
	private $item_additional_info	= array();
	private $add_item_mapping_info	= array();
	private $temp_vcode_arr		= array(
		5008 =>18566, 7827 =>18129, 4148 =>18245, 3074 =>18125, 4140 =>18271
	,	5084 =>18567, 17386=>18503, 17391=>18516, 18260=>18568, 11753=>18010
	,	7749 =>18775, 18280=>18776, 18308=>18777
	);

    public function __construct()
    {
        parent::__construct();
	
		$this->load->model('order/order_model');
		$this->load->model('order/order_item_model');
		$this->load->model('channel/channel_info_model');

        $this->load->model('item/channel_item_info_model');
		
    }

	
    public function testconfirmorder()
	{
//		$this->output->enable_profiler(TRUE);
		$this->load->config('api_config_gmarket', true);
		$this->sales_channel_config	= $this->config->item('fastople', 'api_config_gmarket');
		$this->api_key	= element('api_key',$this->sales_channel_config);
		$result	= $this->callGetReceiverEntryNo('2887427543');
		if(element('Result',$result,'Fail') !== 'Success') echo 'Fail';
		var_dump($result);
	
		
//		// ERROR order check
//		$error_result	= $this->order_model->getOrders(array('status'=>'0', 'channel_id'=>'1'));
//		foreach($error_result->result_array() as $error_order){
//			$contirm_modify	= true;
//			$error_item_result	= $this->order_item_model->getOrderItems(array('order_id'=>element('order_id',$error_order)));
//			foreach($error_item_result->result_array() as $error_item){
//				$error_confirm_result	= $this->callConfimOrder(element('channel_order_no',$error_item));
//				if(element('Result', $error_confirm_result) !==  'Success'){
//					$contirm_modify	= false;
//					echo "ERROR";
//					$this->order_item_model->addErrorResultTmp(array(
//						'channel_order_no'	=> element('channel_order_no',$error_item)
//					,	'result'			=> element('Result', $error_confirm_result)
//					,	'msg'				=> (string)element('Comment', $error_confirm_result)
//					,	'create_date'		=> date('Y-m-d H:i:s')
//					));
//				}
//			}
//			if($contirm_modify===true){
//				$this->order_model->updateOrder(array('status'=>'1'), array('order_id' => element('order_id',$error_order), 'status'=>'0'));
//				echo "UPDATE";
//			}
//		}
	}
	
	public function getPayedOrder()
	{
		if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;
		
		echo 'Cron Start : '.date('Ymd H:i:s').PHP_EOL;
        $request_order = array();

        $request_order['ItemNo'] = '1163051606';
        $channel_id = '5';

        if($channel_id=='4' || $channel_id=='5' || $channel_id=='6' || $channel_id=='7'){

            if(element('ItemNo', $request_order,'')!=''){

                $result_vcode_cnt = $this->channel_item_info_model->getChannelItemInfosCount(array('channel_item_code'=>element('ItemNo', $request_order,false),'channel_id'=>$channel_id));

                if($result_vcode_cnt>0){

                    $result_vcode_id = $this->channel_item_info_model->getItemInfo(array('channel_item_code'=>element('ItemNo', $request_order,false),'channel_id'=>$channel_id));

                    if(element('virtual_item_id',$result_vcode_id,'')!=''){

                       echo  $request_order['ItemCode']="V".str_pad(element('virtual_item_id',$result_vcode_id,''),"8","0",STR_PAD_LEFT);;

                    }
                }
            }
        }

//		$channel_arr	= $this->channel_info_model->getChannelInfostest(array());
//
//		foreach($channel_arr->result_array() as $channel_info){
//
//		    var_dump($channel_info);
//		    $this->sales_channel_config	= null;
//            $this->api_key = $this->channel_info_model->getApikey(array("account_id" => element('account_id', $channel_info), "channel_code" => element('channel_code', $channel_info)));
//
//            switch(element('channel_code',$channel_info)){
//				case 'G':
//					$this->load->config('api_config_gmarket', true);
//					//$this->sales_channel_config	= $this->config->item(element('account_id',$channel_info), 'api_config_gmarket');
//
//					$this->getGmarketPayedOrder(element('channel_id',$channel_info));
//
//					break;
////				case 'A':
////					$this->load->config('api_config_auction', true);
////					//$this->sales_channel_config	= $this->config->item(element('account_id',$channel_info), 'api_config_auction');
////
////					$this->getAuctionPayedOrder(element('channel_id',$channel_info));
////
////					break;
//				default :
//
//			}
//		}
	}
	
	### Gmarket API Function ####
	
	private function getGmarketPayedOrder($channel_id)
	{
		//$this->api_key	= element('api_key',$this->sales_channel_config);
		
		$data	= $this->callRequestOrder();
		
		$order				= array();
		$order_result_arr	= array();
		$error_order_arr	= array();
		if(element('faultcode',$data,false) !== false) return;

		$dafjkd = 1 ;
		foreach($data as $request_order){

		    if($dafjkd >1){
		        continue;
            }
            var_dump($request_order);
            $dafjkd ++;
//			if($this->order_model->getOrderCount(array('package_no'=>element('PackNo', $request_order))) > 0) continue;
			if($this->order_model->getOrderCount(array('channel_order_no'=>element('ContrNo', $request_order))) > 0) continue;
			
			if(in_array(element('ContrNo', $request_order), array('3011386279','3011386280'))){
				echo 'testcase :: ContrNo : '.element('ContrNo', $request_order).' || GmktItemNo : '.element('GmktItemNo', $request_order).' || OutItemNo : '.element('OutItemNo', $request_order, '').' || InventoryNo : '.element('InventoryNo', $request_order, '').PHP_EOL;
				continue;
			}
			
			if(element(element('PackNo', $request_order),$order_result_arr, false) === false){
				$p_num_result	= $this->callGetReceiverEntryNo(element('ContrNo', $request_order));
				$personal_num	= (element('Result',$p_num_result,'Fail') !== 'Success') ? '' : element('ReceiverEntryNo', $p_num_result);
//				,	'customer_number'	=> $personal_num
				
				// 오더 인서트 // return insert_id
				$add_order_data	= array(
					'channel_id'		=> $channel_id
				,	'package_no'		=> element('PackNo', $request_order)
				,	'order_date'		=> date('Y-m-d H:i:s',strtotime(element('ContrDate', $request_order)))
				,	'pay_no'			=> ''
				,	'status'			=> '0' // 작업 중
				,	'customer_number'	=> $personal_num
				,	'validate_error'	=> '0'
				);
				$order_id	= $this->order_model->addOrder($add_order_data);
//				echo $order_id." :: order insert".PHP_EOL;
				
				// address insert
				$add_order_address_data	= array(
					'order_id'		=> $order_id
				,	'buyer_name'	=> element('BuyerName', $request_order)
				,	'buyer_tel1'	=> element('BuyerPhone1', $request_order)
				,	'buyer_tel2'	=> element('BuyerPhone2', $request_order)
				,	'receiver_name'	=> element('ReceiverName', $request_order)
				,	'receiver_tel1'	=> element('ReceiverPhone1', $request_order)
				,	'receiver_tel2'	=> element('ReceiverPhone2', $request_order)
				,	'zipcode'		=> element('ReceiverZipcode', $request_order)
				,	'addr1'			=> element('ReceiverAddress1', $request_order)
				,	'addr2'			=> element('ReceiverAddress2', $request_order)
				,	'comment'		=> element('BuyerMemo', $request_order)
				);
				$this->order_model->addOrderAddress($add_order_address_data);
//				echo $order_id." :: order address insert".PHP_EOL;
				
				$order[element('PackNo', $request_order)]['amount']	= array(
					'order_id'			=> $order_id
				,	'total_amount'		=> (float)element('PaymentPrice', $request_order)
				,	'shipping_amount'	=> (float)element('ShippingFee', $request_order)
				,	'coupon_amount'		=> (float)element('CouponDiscountsPrice', $request_order)
				,	'discount_amount1'	=> (float)element('AddDiscountsPrice', $request_order)
				,	'discount_amount2'	=> ''
				,	'pay_method'		=> element('Payment', $request_order)
				);
				$order_result_arr[element('PackNo', $request_order)]	= $order_id;
				$order[element('PackNo', $request_order)]['item_addition_info']	= array(
                    'weight'		=> array()
                ,   'weight_over_fg'     => false
				,	'health_cnt'	=> 0
				);
				$order[element('PackNo', $request_order)]['order_no_arr']			= array(element('ContrNo', $request_order));
			}else{
				
				$order_id	= element(element('PackNo', $request_order),$order_result_arr);
				
				$order[element('PackNo', $request_order)]['amount']['total_amount']		+= (float)element('PaymentPrice', $request_order);
				$order[element('PackNo', $request_order)]['amount']['shipping_amount']	+= (float)element('ShippingFee', $request_order);
				$order[element('PackNo', $request_order)]['amount']['coupon_amount']		+= (float)element('CouponDiscountsPrice', $request_order);
				$order[element('PackNo', $request_order)]['amount']['discount_amount1']	+= (float)element('AddDiscountsPrice', $request_order);
				if(!in_array(element('ContrNo', $request_order),$order[element('PackNo', $request_order)]['order_no_arr'])) {
					array_push($order[element('PackNo', $request_order)]['order_no_arr'], element('ContrNo', $request_order));
				}
			}
			
//			if(element('items', element(element('PackNo', $request_order),$order), false) === false){
//				$order[element('PackNo', $request_order)]['items']	= array();
//			}
			
			// 메인상품이 옵션상품인 경우
			if(is_array(element('itemOption', $request_order, ''))) {
				
				// (결제금액 - 추가구성상품가격) / 옵션갯수 / 주문수량
				$unit_amount	= round((element('PaymentPrice', $request_order) - element('ItemOptionAdditionPrice', $request_order)) / count(element('itemOption', $request_order)) / element('Quantity', $request_order));

				foreach(element('itemOption', $request_order) as $item_option){
					$v_item_id	= (int)str_replace('V','',element('ItemOptionCode', $item_option, ''));
					$add_item_data	= array(
						'order_id'			=> $order_id
					,	'channel_order_no'	=> element('ContrNo', $request_order)
					,	'channel_product_no'=> element('GmktItemNo', $request_order)
					,	'product_name'		=> element('ItemName', $request_order)
					,	'option_name'		=> element('ItemOptionValue', $item_option)
					,	'qty'				=> element('ItemOptionOrderCnt', $item_option)
					,	'unit_amount'		=> $unit_amount
					,	'product_type'		=> '2'
					,	'virtual_item_id'	=> $v_item_id
					);
					
					$item_add_info	= $this->getItemAddInfo($v_item_id);

					if(element('weight_type_id',$item_add_info)>0) {
                        $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] = (isset($order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]))? $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] : 0;
                        $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]	+= element('weight',$item_add_info) * element('ItemOptionOrderCnt', $item_option);
                        if($order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]>=element('weight_limit', $item_add_info)) $order[element('PackNo', $request_order)]['item_addition_info']['weight_over_fg'] = true;
                    }

					$order[element('PackNo', $request_order)]['item_addition_info']['health_cnt']+= element('health_cnt',$item_add_info) * element('ItemOptionOrderCnt', $item_option);
					if(element('virtual_item_id',$item_add_info) == 0) $order[element('PackNo', $request_order)]['item_addition_info']['no_v_code']	= true;
					
					$this->order_item_model->addOrderItem($add_item_data);
					if($v_item_id == 0) $order[element('PackNo', $request_order)]['item_addition_info']['no_v_code']	= true;
//					echo $order_id." :: order item insert".PHP_EOL;
				}
				
			// 메인상품이 단품인 경우
			}else{

                if($channel_id=='4' || $channel_id=='5' || $channel_id=='6' || $channel_id=='7'){

                    if(element('GmktItemNo', $request_order,false)){
                        $request_order['OutItemNo']='';
                    }
                }

                $v_item_id	= (int)str_replace('V','',element('OutItemNo', $request_order, ''));
				if(in_array($v_item_id,array(7827, 11753, 4148, 3074, 4140, 17386, 17391, 5008, 5084, 18260, 7749, 18280, 18308))){
				//
					echo 'case1 :: ContrNo : '.element('ContrNo', $request_order).'||GmktItemNo : '.element('GmktItemNo', $request_order).'||OutItemNo : '.element('OutItemNo', $request_order, '').'||InventoryNo : '.element('InventoryNo', $request_order, '').PHP_EOL;
					$v_item_id = element($v_item_id,$this->temp_vcode_arr);
				}elseif(in_array(element('GmktItemNo', $request_order),array('1524972367','1417931315','1524951918','1524951861','1524987437','1524987497','1417863985','1417914555','1571550554','1418158705','1577839921','1578036075'))){
					echo 'case2 :: ContrNo : '.element('ContrNo', $request_order).'||GmktItemNo : '.element('GmktItemNo', $request_order).'||OutItemNo : '.element('OutItemNo', $request_order, '').'||InventoryNo : '.element('InventoryNo', $request_order, '').PHP_EOL;
				}
				
				$add_item_data	= array(
					'order_id'			=> $order_id
				,	'channel_order_no'	=> element('ContrNo', $request_order)
				,	'channel_product_no'=> element('GmktItemNo', $request_order)
				,	'product_name'		=> element('ItemName', $request_order)
				,	'qty'				=> element('Quantity', $request_order)
				,	'unit_amount'		=> (element('PaymentPrice', $request_order) - element('ItemOptionAdditionPrice', $request_order)) / element('Quantity', $request_order)
				,	'product_type'		=> '1'
				,	'virtual_item_id'	=> $v_item_id
				);
				$item_add_info	= $this->getItemAddInfo($v_item_id);

                if(element('weight_type_id',$item_add_info)>0) {
                    $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] = (isset($order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]))? $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] : 0;
                    $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]	+= element('weight',$item_add_info) * element('Quantity', $request_order);
                    if($order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]>=element('weight_limit', $item_add_info)) $order[element('PackNo', $request_order)]['item_addition_info']['weight_over_fg'] = true;
                }
				$order[element('PackNo', $request_order)]['item_addition_info']['health_cnt']+= element('health_cnt',$item_add_info) * element('Quantity', $request_order);
				if(element('virtual_item_id',$item_add_info) == 0) $order[element('PackNo', $request_order)]['item_addition_info']['no_v_code']	= true;
				
				$this->order_item_model->addOrderItem($add_item_data);
				if($v_item_id == 0) $order[element('PackNo', $request_order)]['item_addition_info']['no_v_code']	= true;
//				echo $order_id." :: order item insert".PHP_EOL;
			}
			
			// 추가구성상품이 있는경우
			if(is_array(element('itemOptionAddition', $request_order, ''))) {
				
				// 추가구성상품가격 / 추가구성상품종류
				$add_item_base_amount	= element('ItemOptionAdditionPrice', $request_order) / count(element('itemOptionAddition', $request_order));
				
				foreach(element('itemOptionAddition', $request_order) as $item_option_add){
					$v_item_id	= (int)str_replace('V','',element('ItemOptionCode', $item_option_add, ''));
					$add_item_data	= array(
						'order_id'			=> $order_id
					,	'channel_order_no'	=> element('ContrNo', $request_order)
					,	'channel_product_no'=> element('GmktItemNo', $request_order)
					,	'product_name'		=> element('ItemName', $request_order)
					,	'option_name'		=> element('ItemOptionValue', $item_option_add)
					,	'qty'				=> element('ItemOptionOrderCnt', $item_option_add)
					,	'unit_amount'		=> $add_item_base_amount/element('ItemOptionOrderCnt', $item_option_add)
					,	'product_type'		=> '3'
					,	'virtual_item_id'	=> $v_item_id
					);
					$item_add_info	= $this->getItemAddInfo($v_item_id);
                    if(element('weight_type_id',$item_add_info)>0) {
                        $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] = (isset($order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]))? $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] : 0;
                        $order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]	+= element('weight',$item_add_info) * element('ItemOptionOrderCnt', $item_option_add);
                        if($order[element('PackNo', $request_order)]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]>=element('weight_limit', $item_add_info)) $order[element('PackNo', $request_order)]['item_addition_info']['weight_over_fg'] = true;
                    }
					$order[element('PackNo', $request_order)]['item_addition_info']['health_cnt']+= element('health_cnt',$item_add_info) * element('ItemOptionOrderCnt', $item_option_add);
					if(element('virtual_item_id',$item_add_info) == 0) $order[element('PackNo', $request_order)]['item_addition_info']['no_v_code']	= true;
					
					$this->order_item_model->addOrderItem($add_item_data);
					if($v_item_id == 0) $order[element('PackNo', $request_order)]['item_addition_info']['no_v_code']	= true;
//					echo $order_id." :: order item insert".PHP_EOL;
				}
			}
			
		}

		echo "Gmarket Order".PHP_EOL;

		$contirm_error	= false;
		foreach($order as $dlvNo => $info){
			$validate_error	= 0;
			
            if(element('weight_over_fg',element('item_addition_info',$info)) == true){
				$validate_error	+= 4;
			}
			if(element('health_cnt',element('item_addition_info',$info)) > 6){
				$validate_error	+= 2;
			}
			if(element('no_v_code',element('item_addition_info',$info), false) !== false){
				$validate_error	+= 8;
			}

			foreach(element('order_no_arr',$info) as $order_no){
				
				$confirm_result	= $this->callConfimOrder($order_no);
				if(element('Result', $confirm_result) !==  'Success'){
					$contirm_error	= true;
					$this->order_item_model->addErrorResultTmp(array(
						'channel_order_no'	=> $order_no
					,	'result'			=> element('Result', $confirm_result)
					,	'msg'				=> (string)element('Comment', $confirm_result)
					,	'create_date'		=> date('Y-m-d H:i:s')
					));
				}

			}
			
			$this->order_model->addOrderAmount(element('amount',$info));
//			echo $dlvNo." :: order amount insert".PHP_EOL;
			$status	= $validate_error > 0 ? 1 : 3;
			if($contirm_error === true) $status = 0;
			$this->order_model->updateOrder(array('validate_error'=>$validate_error, 'status'=>$status), array('package_no' => $dlvNo, 'status'=>'0'));
//			echo $dlvNo." :: order update".PHP_EOL;
		}
		
		// ERROR order check
		$error_result	= $this->order_model->getOrders(array('status'=>'0', 'channel_id'=>$channel_id));
		foreach($error_result->result_array() as $error_order){
			$contirm_modify	= true;
			$error_item_result	= $this->order_item_model->getOrderItems(array('order_id'=>element('order_id',$error_order)));
			foreach($error_item_result->result_array() as $error_item){
				$error_confirm_result	= $this->callConfimOrder(element('channel_order_no',$error_item));
				if(!in_array(element('Result', $error_confirm_result), array('Change','Success'))){
					$contirm_modify	= false;
					$this->order_item_model->addErrorResultTmp(array(
						'channel_order_no'	=> element('channel_order_no',$error_item)
					,	'result'			=> element('Result', $error_confirm_result)
					,	'msg'				=> (string)element('Comment', $error_confirm_result)
					,	'create_date'		=> date('Y-m-d H:i:s')
					));
				}
			}

			if($contirm_modify === true) {
//				echo 'UPDATE :: '.element('order_id',$error_order).PHP_EOL;
				$status	= element('validate_error',$error_order) > 0 ? 1 : 3;
				$this->order_model->updateOrder(array('status'=>$status), array('order_id' => element('order_id',$error_order), 'status'=>'0'));
			}
		}
	
	}
	
	private function callRequestOrder(){
		
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
		
		$RequestOrder = new \sdk\controller\RequestOrder();
		$RequestOrder->setTicket($this->api_key);
		
		return $RequestOrder->getResponse();
		
	}
	
	private function callGetReceiverEntryNo($ContrNo){
		
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
		
		$GetReceiverEntryNo = new \sdk\controller\GetReceiverEntryNo();
		$GetReceiverEntryNo->setTicket($this->api_key);
		$GetReceiverEntryNo->setContrNo($ContrNo);
		
		return $GetReceiverEntryNo->getResponse();
	}
	
	private function callConfimOrder($order_no){
		
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
		
		$ConfimOrder = new \sdk\controller\ConfirmOrder();
		$ConfimOrder->setTicket($this->api_key);
		$ConfimOrder->setContrNo($order_no);
		$ConfimOrder->setSendPlanDate(date('Y-m-d', strtotime('+2 days')));
		
		return $ConfimOrder->getResponse();
		
	}
	### Gmarket API Function ####
	
	
	
	
	private function getAuctionPayedOrder($channel_id)
	{
		require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
		include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
		
		//$this->api_key	= element('api_key',$this->sales_channel_config);
		
		$serverUrl			= "https://api.auction.co.kr/APIv1/AuctionService.asmx";
		$order_result_arr	= array();
		$order				= array();
		$error_order		= array();
		
		#### callPaidOrder ####
		$get_paid_order_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/GetPaidOrderList.xml');
		// getPaidOrder
		$requestXmlBody	= str_replace(
			array('__API_ENCRYPT_KEY__','__SEARCH_TYPE__','__START_DATE__','__END_DATE__')
		,	array($this->api_key, 'ReceiptDate', date('Y-m-d', strtotime('-20 days')), date('Y-m-d',strtotime('+1 days')))
		,	$get_paid_order_dummy
		);
		$action			= "http://www.auction.co.kr/APIv1/AuctionService/GetPaidOrderList";
		$paid_orders	= requestAuction($serverUrl, $action, $requestXmlBody);

		#### callPaidOrder ####
		$get_shipping_address_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/GetShippingAddressList.xml');
		$action						= "http://www.auction.co.kr/APIv1/AuctionService/GetShippingAddressList";
		
		foreach ($paid_orders as $dlvNo => $loadAddress) {
			
//			if($this->order_model->getOrderCount(array('package_no'=>$dlvNo)) > 0) continue;
			
			#### callShippingAddress ####
			$requestXmlBody	= str_replace(
				array('__API_ENCRYPT_KEY__','__SEND_TYPE__','__CONFIRM_TYPE__','__GROUP_ORDER_SEQ_NO__')
				,	array($this->api_key, 'NotSending', 'NotReceiving', $dlvNo)
				,	$get_shipping_address_dummy
			);
			$shippingaddresses	= requestAuction($serverUrl, $action, $requestXmlBody);
			#### callShippingAddress ####
			
			foreach ($shippingaddresses as $request_order) {
				if($this->order_model->getOrderCount(array('channel_order_no'=>element('OrderNo', $request_order))) > 0) continue;
				
				if(element(element('GroupOrderSeqno', $request_order),$order_result_arr, false) === false){
					// 오더 인서트 // return insert_id
					$add_order_data	= array(
//					$order[$dlvNo]['order'][]	= array(
						'channel_id'		=> $channel_id
					,	'package_no'		=> element('GroupOrderSeqno', $request_order)
					,	'order_date'		=> date('Y-m-d H:i:s',strtotime(element('ReceiptDate', $request_order)))
					,	'pay_no'			=> element('PayNo', $request_order)
					,	'customer_number'	=> str_replace('통관번호=','',element('Others', $request_order))
					,	'status'			=> '0' // 작업 중
					,	'validate_error'	=> '0'
					);
					$order_id	= $this->order_model->addOrder($add_order_data);
//					echo $order_id." :: order insert".PHP_EOL;
					
					$loadAddress	= $loadAddress == '' ? element('AddressPost',element('order_addr', $request_order)).' '.element('AddressDetail',element('order_addr', $request_order)) : $loadAddress;
					// address insert
					$add_order_address_data	= array(
//					$order[$dlvNo]['addr'][]	= array(
						'order_id'		=> $order_id
					,	'buyer_name'	=> element('BuyerName', $request_order)
					,	'buyer_tel1'	=> element('DistTel', $request_order)
					,	'buyer_tel2'	=> element('DistMobileTel', $request_order)
					,	'receiver_name'	=> element('RecieverName', $request_order)
					,	'receiver_tel1'	=> element('Tel',element('order_addr', $request_order))
					,	'receiver_tel2'	=> element('MobileTel',element('order_addr', $request_order))
					,	'zipcode'		=> element('PostNo',element('order_addr', $request_order))
					,	'addr1'			=> $loadAddress
					,	'addr2'			=> ''
					,	'comment'		=> element('DeliveryRemark', $request_order)
					);
					$this->order_model->addOrderAddress($add_order_address_data);
//					echo $order_id." :: order address insert".PHP_EOL;
					
					$order[$dlvNo]['amount']	= array(
						'order_id'			=> $order_id
					,	'total_amount'		=> (float)element('AwardAmount', $request_order)
					,	'shipping_amount'	=> (float)element('ShippingCost', $request_order)
					,	'coupon_amount'		=> (float)element('SellerCouponFeeAmount', $request_order)
					,	'discount_amount1'	=> (float)element('QuantitySellerDiscountAmount', $request_order)
					,	'discount_amount2'	=> (float)element('NointOfferingFeeAmount', $request_order)
					,	'pay_method'		=> element('PaymentType', $request_order)
					);
					$order_result_arr[$dlvNo]	= $order_id;
					$order[$dlvNo]['item_addition_info']	= array(
                        'weight'		=> array()
                    ,   'weight_over_fg'     => false
					,	'health_cnt'	=> 0
					);
					$order[$dlvNo]['order_no_arr']			= array(element('OrderNo', $request_order));
				}else{
					$order_id	= element($dlvNo,$order_result_arr);
					
					$order[$dlvNo]['amount']['total_amount']		+= (float)element('AwardAmount', $request_order);
					$order[$dlvNo]['amount']['shipping_amount']		+= (float)element('ShippingCost', $request_order);
					$order[$dlvNo]['amount']['coupon_amount']		+= (float)element('SellerCouponFeeAmount', $request_order);
					$order[$dlvNo]['amount']['discount_amount1']	+= (float)element('QuantitySellerDiscountAmount', $request_order);
					$order[$dlvNo]['amount']['discount_amount2']	+= (float)element('NointOfferingFeeAmount', $request_order);
					
					if(!in_array(element('OrderNo', $request_order),$order[$dlvNo]['order_no_arr'])) {
						array_push($order[$dlvNo]['order_no_arr'], element('OrderNo', $request_order));
					}
				}

				$add_item_amount	= 0;
				// 추가구성상품
				if(is_array(element('itemOption',$request_order,''))){
					
					foreach(element('itemOption',$request_order) as $add_option){
						$v_item_id	= $this->getAddItemMappingInfo(element('ItemNo', $request_order), element('OptionItemName', $add_option));
						$add_item_data	= array(
//						$order[$dlvNo]['item'][]	= array(
							'order_id'			=> $order_id
						,	'channel_order_no'	=> element('OrderNo', $request_order)
						,	'channel_product_no'=> element('ItemNo', $request_order)
						,	'product_name'		=> element('ItemName', $request_order)
						,	'option_name'		=> element('OptionItemName', $add_option)
						,	'qty'				=> element('OptionQuantity', $add_option)
						,	'unit_amount'		=> element('OptionItemPrice', $add_option)/element('OptionQuantity', $add_option)
						,	'product_type'		=> '3'
						,	'virtual_item_id'	=> $v_item_id
						);
						
						$add_item_amount	+= element('OptionItemPrice', $add_option);
						
						if($v_item_id == 0) {
							$order[$dlvNo]['item_addition_info']['no_v_code'] = true;
						}else{
							$item_add_info = $this->getItemAddInfo($v_item_id);

                            if(element('weight_type_id',$item_add_info)>0) {
                                $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] = (isset($order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]))? $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] : 0;
                                $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]		+= element('weight', $item_add_info) * element('OptionQuantity', $add_option);
                                if($order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]>=element('weight_limit', $item_add_info)) $order[$dlvNo]['item_addition_info']['weight_over_fg'] = true;
                            }
                            $order[$dlvNo]['item_addition_info']['health_cnt']	+= element('health_cnt', $item_add_info) * element('OptionQuantity', $add_option);
							if(element('virtual_item_id',$item_add_info) == 0) $order[$dlvNo]['item_addition_info']['no_v_code'] = true;
						}
						$this->order_item_model->addOrderItem($add_item_data);
//						echo $order_id." :: order item insert".PHP_EOL;
					}
				}
				// 추가구성상품
				
				// 단품인경우
				if(element('OptionContent',$request_order,'') == '') {
					$v_item_id	= (int)str_replace('V','',element('ItemCode', $request_order, ''));
					$add_item_data = array(
//					$order[$dlvNo]['item'][]	= array(
						'order_id'				=> $order_id
					,	'channel_order_no'		=> element('OrderNo', $request_order)
					,	'channel_product_no'	=> element('ItemNo', $request_order)
					,	'product_name'			=> element('ItemName', $request_order)
					,	'qty'					=> element('AwardQuantity', $request_order)
					,	'unit_amount'			=> (element('AwardAmount', $request_order) - $add_item_amount) / element('AwardQuantity', $request_order)
					,	'product_type'			=> '1'
					,	'virtual_item_id'		=> $v_item_id
					);
					$item_add_info = $this->getItemAddInfo($v_item_id);

                    if(element('weight_type_id',$item_add_info)>0) {
                        $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] = (isset($order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]))? $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] : 0;
                        $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]		+= element('weight', $item_add_info) * element('AwardQuantity', $request_order);
                        if($order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]>=element('weight_limit', $item_add_info)) $order[$dlvNo]['item_addition_info']['weight_over_fg'] = true;
                    }
					$order[$dlvNo]['item_addition_info']['health_cnt']	+= element('health_cnt', $item_add_info) * element('AwardQuantity', $request_order);
					if(element('virtual_item_id',$item_add_info) == 0) $order[$dlvNo]['item_addition_info']['no_v_code'] = true;
					if($v_item_id == 0) $order[$dlvNo]['item_addition_info']['no_v_code']	= true;
					
					$this->order_item_model->addOrderItem($add_item_data);
//					echo $order_id." :: order item insert".PHP_EOL;
				// 옵션상품인 경우
				}else{
					$select_option_arr	= explode('_',element('SellerStockCode', $request_order));
					$unit_amount		= (element('AwardAmount', $request_order) - $add_item_amount) / element('AwardQuantity', $request_order) / count($select_option_arr);
					
					$select_option_name_tmp	 = explode('/',element('OptionContent',$request_order));
					$select_option_name_arr	 = explode('_',trim($select_option_name_tmp[1]));
					
					foreach($select_option_arr as $key => $option_vcode){
						$v_item_id			= (int)str_replace('V','',$option_vcode);
						
						$add_item_data	= array(
//						$order[$dlvNo]['item'][]	= array(
							'order_id'			=> $order_id
						,	'channel_order_no'	=> element('OrderNo', $request_order)
						,	'channel_product_no'=> element('ItemNo', $request_order)
						,	'product_name'		=> element('ItemName', $request_order)
						,	'option_name'		=> $select_option_name_arr[$key]
						,	'qty'				=> element('AwardQuantity', $request_order)
						,	'unit_amount'		=> $unit_amount
						,	'product_type'		=> '2'
						,	'virtual_item_id'	=> $v_item_id
						);
						$item_add_info	= $this->getItemAddInfo($v_item_id);

                        if(element('weight_type_id',$item_add_info)>0) {
                            $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] = (isset($order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]))? $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)] : 0;
                            $order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]		+= element('weight',$item_add_info) * element('AwardQuantity', $request_order);
                            if($order[$dlvNo]['item_addition_info']['weight'][element('weight_type_id', $item_add_info)]>=element('weight_limit', $item_add_info)) $order[$dlvNo]['item_addition_info']['weight_over_fg'] = true;
                        }
						$order[$dlvNo]['item_addition_info']['health_cnt']	+= element('health_cnt',$item_add_info) * element('AwardQuantity', $request_order);
						if(element('virtual_item_id',$item_add_info) == 0) $order[$dlvNo]['item_addition_info']['no_v_code'] = true;
						if($v_item_id == 0) $order[$dlvNo]['item_addition_info']['no_v_code']	= true;
						
						$this->order_item_model->addOrderItem($add_item_data);
//						echo $order_id." :: order item insert".PHP_EOL;
					}
					
				}
				
			}
		}

        echo "Auction Order".PHP_EOL;

		foreach($order as $dlvNo => $info){
			$validate_error	= 0;
			$order_update_data	= array();
            if(element('weight_over_fg',element('item_addition_info',$info)) == true){
                $validate_error	+= 4;
			}
			if(element('health_cnt',element('item_addition_info',$info)) > 6){
				$validate_error	+= 2;
			}
			if(element('no_v_code',element('item_addition_info',$info), false) !== false){
				$validate_error	+= 8;
			}
			$contirm_error	= false;
			foreach(element('order_no_arr',$info) as $order_no){
				$confirm_order_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ConfirmReceivingOrder.xml');
				// getPaidOrder
				$requestXmlBody	= str_replace(
					array('__API_ENCRYPT_KEY__','__CHANNEL_ORDER_NO__')
					,	array($this->api_key, $order_no)
					,	$confirm_order_dummy
				);
				$action			= "http://www.auction.co.kr/APIv1/AuctionService/ConfirmReceivingOrder";
				$confirm_result	= requestAuction($serverUrl, $action, $requestXmlBody);
				if(element('Result', $confirm_result) !==  'Success'){
					$contirm_error	= true;
					$this->order_item_model->addErrorResultTmp(array(
						'channel_order_no'	=> $order_no
					,	'result'			=> element('Result', $confirm_result)
					,	'msg'				=> (string)element('string', $confirm_result)
					,	'create_date'		=> date('Y-m-d H:i:s')
					));
					
				}
			}
			
			$this->order_model->addOrderAmount(element('amount',$info));
//			echo $dlvNo." :: order amount insert".PHP_EOL;
			
			$status	= $validate_error > 1 ? 1 : 3;
			
			if($contirm_error === true) $status = 0;
			
			$this->order_model->updateOrder(array('validate_error'=>$validate_error, 'status'=>$status), array('package_no' => $dlvNo, 'status'=>'0'));
			
//			echo $dlvNo." :: order update".PHP_EOL;
		}
		
		// ERROR order check
		$error_result	= $this->order_model->getOrders(array('status'=>'0', 'channel_id'=>$channel_id));
		foreach($error_result->result_array() as $error_order){
			$contirm_modify		= true;
			$error_item_result	= $this->order_item_model->getOrderItems(array('order_id'=>element('order_id',$error_order)));
			foreach($error_item_result->result_array() as $error_item){
				$confirm_order_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ConfirmReceivingOrder.xml');
				$requestXmlBody	= str_replace(
					array('__API_ENCRYPT_KEY__','__CHANNEL_ORDER_NO__')
					,	array($this->api_key, element('channel_order_no',$error_item))
					,	$confirm_order_dummy
				);
				$action			= "http://www.auction.co.kr/APIv1/AuctionService/ConfirmReceivingOrder";
				$error_confirm_result	= requestAuction($serverUrl, $action, $requestXmlBody);
				
				if(element('Result', $error_confirm_result) !=  'Success' && substr(trim(element('string', $error_confirm_result)), -11) != 'Status=201)') {
					$contirm_modify = false;
					$this->order_item_model->addErrorResultTmp(array(
						'channel_order_no'	=> element('channel_order_no',$error_item)
					,	'result'			=> element('Result', $error_confirm_result)
					,	'msg'				=> (string)element('string', $error_confirm_result)
					,	'create_date'		=> date('Y-m-d H:i:s')
					));
				}
			}
			if($contirm_modify === true) {
//				echo 'UPDATE :: '.element('order_id',$error_order).PHP_EOL;
				$status	= element('validate_error',$error_order) > 0 ? 1 : 3;
				$this->order_model->updateOrder(array('status'=>$status), array('order_id' => element('order_id',$error_order), 'status'=>'0'));
			}
		}
		
		return;
		
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
}
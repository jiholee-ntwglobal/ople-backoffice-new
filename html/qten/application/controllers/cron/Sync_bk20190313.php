<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-05-30
 * File: Oms_order.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync extends CI_Controller
{
	private $sales_channel_config;
	private $api_key;
	private $item_additional_info	= array();
	private $add_item_mapping_info	= array();
	
    public function __construct()
    {
        parent::__construct();
	
		$this->load->model('item/channel_item_info_model');
		$this->load->model('item/soldout_exclude_item_model');
		$this->load->model('item/soldout_history_model');
		$this->load->model('channel/channel_info_model');
		$this->load->model('order/order_model');
		$this->load->model('stock/master_item_stock_model');
        $this->load->model('process/process_scheduling');
    }
	
	private function callAddPrice($item_info)
	{
		$stock	= element('stock_status',$item_info) =='N' ? '0':'9999';
//		if(in_array(element('discount_unit',$item_info, ''),array('Rate','Money'))){
//			$this->sendDiscountPrice($item_info);
//		}
		$price_info = array(
			'GmktItemNo'	=> element('channel_item_code',$item_info)
		,	'DisplayDate'	=> date('Y-m-d', strtotime('+1 year'))
		,	'SellPrice'		=> element('upload_price',$item_info)
		,	'StockQty'		=> $stock
		,	'InventoryNo'	=> "V".str_pad(element('virtual_item_id',$item_info), 8, "0", STR_PAD_LEFT)
		);
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
		
		$AddPrice = new \sdk\controller\AddPrice();
		$AddPrice->setTicket($this->api_key);
		$AddPrice->setProductPriceInfo($price_info);
		
		$result =  $AddPrice->getResponse();
		
		if(element('faultcode', $result, false) !== false){
			return array(
				'result'	=> 'Fail'
			,	'string'	=> element('faultstring',$result)
			);
		}
		if(element('Result', $result) == 'Fail'){
			return array(
				'result'	=> 'Fail'
			,	'string'	=> element('Comment',$result)
			);
		}
		
		return array(
			'result'	=> 'Success'
		,	'rs_msg'	=> element('Comment',$result)
		);
		
	}

    private function callAuctionPriceUpdate($channel_item_code, $price_info)
    {

        require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
        include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
        $this->api_key	= 'd310kxymI5jbPsYSxgyJ4M9BkjJbtr8HCRcsVRFRK34TOnBIhjWapNEP/kfX7fk0oL/mvCc2bBG9VItchZXNuX0nP5xx1c4/PDd+03Dp0b8+uZpHQPr/3hy4kSD3g4D+X4mYkO7BPw2VRvgXd966yJ44honypujpOuokhesVrSPGolEF5HAWQY4Jewkxlub9mdMEKSVqH4MNgvlAH3OXR+s=';

        // 가격변경
        $item_price_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItem.xml');
        $seller_discount	= '';

        if(element('discount_type',$price_info) != 'N'){
            $seller_discount	= 'SellerDiscount="'.element('discount_value',$price_info).'" SellerDiscountFromDate="'.date('Y-m-d').'" SellerDiscountToDate="9999-12-31" ';

        }else{
            $seller_discount	= ' SellerDiscount="0" ';
        }

        $requestXmlBody	= str_replace(
            array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__', '__ITEM_PRICE__', '__SELLER_DISCOUNT_PRICE__')
            ,	array($this->api_key, $channel_item_code, element('basic_price',$price_info), $seller_discount)
            ,	$item_price_dummy
        );
        $serverUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
        $action			= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItem";

//		$sync_result	= requestAuction($serverUrl, $action, $requestXmlBody);
        return requestAuction($serverUrl, $action, $requestXmlBody);

    }

    private function sendDiscountPrice($channel_item_code, $price_info)
    {
        include_once '/ssd/html/api_sdks/gmarket/autoload.php';
        $this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
        $AddPremiumItem		= new \sdk\controller\AddPremiumItem();
        $AddPremiumItem->setTicket($this->api_key);
        $premium_item_info	= array(
            'GmktItemNo'	=> $channel_item_code
        ,	'IsDiscount'	=> true
        ,	'DiscountPrice'	=> element('discount_value',$price_info)
        ,	'DiscountUnit'	=> element('discount_type',$price_info) // Money:or Rate
        ,	'StartDate'		=> date('Y-m-d')
        ,	'EndDate'		=> '9999-12-31' // 일단 3개월
        );
        $AddPremiumItem->setPremiumItemInfo($premium_item_info);
        $response	= $AddPremiumItem->getResponse();

//		if(element('Result',$response) != 'Success'){
//			print_r($premium_item_info);
//			print_r($response);
//			exit;
//		}

        return (element('Result',$response[0]) == 'Success') ? true : false;
    }
	
	private function callReviseItemSelling($item_info)
	{
		require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
		include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
		$stock	= element('stock_status',$item_info) =='N' ? 'Stop':'OnSale';
		$period	= element('stock_status',$item_info) =='N' ? '':'<Period ApplyPeriod="90" />';
		
		$item_selling_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItemSelling.xml');
		$requestXmlBody	= str_replace(
			array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__', '__ITEM_STATUS__', '__ITEM_PERIOD__')
			,	array($this->api_key, element('channel_item_code',$item_info), $stock, $period)
			,	$item_selling_dummy
		);
		
		$serverUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
		$action			= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItemSelling";
		
		return requestAuction($serverUrl, $action, $requestXmlBody);
		
	}
	
	public function stockInItemSync()
	{
        //cron chk
        $cnt = $this->process_scheduling->getProcessSchedulingCount(array('full_query'=>
            "(process_status ='1' and process_code = 'stockInItemSync' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -3 hour),'%Y%m%d%H'))
            or
            (process_status ='1' and process_code = 'updateProductPrice' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -10 hour),'%Y%m%d%H'))"));

        var_dump($cnt);
        if($cnt>0){
            $process_scheduling_add_data =array(
                'process_code' => 'stockInItemSync',
                'process_status' => '3',
                'process_start_date' => date ("Y-m-d H:i:s"),
                'process_end_date' => date ("Y-m-d H:i:s")
            );
            $this->process_scheduling->addProcessScheduling($process_scheduling_add_data);
            return;
        }

        $process_scheduling_add_data =array(
            'process_code' => 'stockInItemSync',
            'process_status' => '1',
            'process_start_date' => date ("Y-m-d H:i:s")
        );
        $process_scheduling_inset_id=$this->process_scheduling->addProcessScheduling($process_scheduling_add_data);

	    if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;
		
        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(33);

        $rest = $history_api->sendHistoryID();
		// 채널정보
		$channel_arr	= $this->channel_info_model->getChannelInfos(array());
		// 채널별 분기
		foreach($channel_arr->result_array() as $channel_info){
			$success_item_ids	= array();
			$this->sales_channel_config	= null;
//			if(element('channel_code',$channel_info) == 'A') continue;
			switch(element('channel_code',$channel_info)){
				case 'G':
					$this->load->config('api_config_gmarket', true);
					$this->sales_channel_config	= $this->config->item(element('account_id',$channel_info), 'api_config_gmarket');
					$stockInItemSync_method	= 'callAddPrice';
					
					break;
				case 'A':
					$this->load->config('api_config_auction', true);
					$this->sales_channel_config	= $this->config->item(element('account_id',$channel_info), 'api_config_auction');
					$stockInItemSync_method	= 'callReviseItemSelling';
					
					break;
				default :
				
			}
			$this->api_key	= element('api_key',$this->sales_channel_config);
			
			// update 대상
			$filter	= array(
				'need_update'	=> 'Y'
			,	'channel_id'	=> element('channel_id',$channel_info)
			,	'master_info'	=> '1'
//			,	'limit'	=> '1' // test
			);
			$select	= 'i.item_info_id, i.channel_id, i.channel_item_code, i.virtual_item_id, i.upload_price, i.stock_status, d.master_item_id, m.currentqty';
			$item_result		= $this->channel_item_info_model->getChannelItemInfos($filter, $select);
			foreach($item_result->result_array() as $item_info){
				
				// 채널에 상품 정보 동기화
				$sync_result	= $this->{$stockInItemSync_method}($item_info);
				
				if(element('result',$sync_result) == 'Fail'){

					echo "UPDATE Error id ::".element('item_info_id',$item_info)."|| itemcode :: ".element('channel_item_code',$item_info)."||".element('string',$sync_result).PHP_EOL;

                    $error_add_data	= array(
                        'item_info_id'			=> element('item_info_id',$item_info)
                    ,	'stock_status'			=> element('stock_status',$item_info)
                    ,	'currentqty'			=> element('currentqty',$item_info)
                    ,	'error_message'			=> element('string',$sync_result)
                    ,	'soldout_process_type'	=> '1'
                    ,	'process_worker_id'		=> '0'
                    ,	'create_date'			=> date('Y-m-d H:i:s')
                    );
                    $this->soldout_history_model->addSoldoutHistoryError($error_add_data);
					continue;
					
				}
				
				array_push($success_item_ids,element('item_info_id',$item_info));
				$add_data	= array(
					'item_info_id'			=> element('item_info_id',$item_info)
				,	'stock_status'			=> element('stock_status',$item_info)
				,	'currentqty'			=> element('currentqty',$item_info)
				,	'soldout_process_type'	=> '1'
				,	'process_worker_id'		=> '0'
				,	'create_date'			=> date('Y-m-d H:i:s')
				);
				$this->soldout_history_model->addSoldoutHistory($add_data);
				
				echo "UPDATE ::".element('item_info_id',$item_info)."|| itemcode :: ".element('channel_item_code',$item_info).PHP_EOL;
			}
			
			if(count($success_item_ids) > 0) {
				$update_data	= array(
					'update_date' => date('Y-m-d H:i:s')
				,	'need_update' => 'N'
				);
				$this->channel_item_info_model->updateChannelItemInfo($update_data, array('item_info_id_in' => $success_item_ids));
			}
		}

        //cron chk
        $process_scheduling_update_data =array(
            'process_status'=>'2',
            'process_end_date'=>date("Y-m-d H:i:s")
        );
        $this->process_scheduling->updateProcessScheduling($process_scheduling_update_data,array('process_scheduling_id'=>$process_scheduling_inset_id));

	}
	
	public function clearanceItemSync()
	{
		if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;
		
        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(32);

        $rest = $history_api->sendHistoryID();
		// 채널정보
		$channel_arr	= $this->channel_info_model->getChannelInfos(array());
		// 채널별 분기
		foreach($channel_arr->result_array() as $channel_info){
			$success_item_ids	= array();
			$this->sales_channel_config	= null;
//			if(element('channel_code',$channel_info) == 'A') continue;
			switch(element('channel_code',$channel_info)){
				case 'G':
					$this->load->config('api_config_gmarket', true);
					$this->sales_channel_config	= $this->config->item(element('account_id',$channel_info), 'api_config_gmarket');
					$stockInItemSync_method	= 'callAddPrice';

					break;
				case 'A':
					$this->load->config('api_config_auction', true);
					$this->sales_channel_config	= $this->config->item(element('account_id',$channel_info), 'api_config_auction');
					$stockInItemSync_method	= 'callReviseItemSelling';

					break;
				default :

			}
			$this->api_key	= element('api_key',$this->sales_channel_config);

			// 클리어런스 상품 정보
			$filter	= array('channel_id'=>element('channel_id',$channel_info), 'stock_status'=>'Y');
			if(element('account_type',$channel_info) == '1'){
				$filter['restrict_customer_clearance']	= '1';
			}else{
				$filter['restrict_customer_clearance_in']	= array('1','2');
			}
			$item_result	= $this->master_item_stock_model->getItemStockInfo($filter);

			foreach($item_result->result_array() as $item_info){
				// 통관이슈는 품절처리만
				$item_info['stock_status']	= 'N';
//				var_dump($item_info);
//				exit;
				// 채널에 상품 정보 동기화
				$sync_result	= $this->{$stockInItemSync_method}($item_info);

				if(element('result',$sync_result) == 'Fail'){

					echo "UPDATE Error id ::".element('item_info_id',$item_info)."|| itemcode :: ".element('channel_item_code',$item_info)."||".element('string',$sync_result).PHP_EOL;
					continue;

				}

				array_push($success_item_ids,element('item_info_id',$item_info));
				$add_data	= array(
					'item_info_id'			=> element('item_info_id',$item_info)
				,	'stock_status'			=> element('stock_status',$item_info)
				,	'currentqty'			=> element('currentqty',$item_info)
				,	'soldout_process_type'	=> '2'
				,	'process_worker_id'		=> '0'
				,	'create_date'			=> date('Y-m-d H:i:s')
				);
				$this->soldout_history_model->addSoldoutHistory($add_data);

				echo "UPDATE ::".element('item_info_id',$item_info)."|| itemcode :: ".element('channel_item_code',$item_info).PHP_EOL;
			}
			
			if(count($success_item_ids) > 0) {
				$update_data	= array(
					'update_date'	=> date('Y-m-d H:i:s')
				,	'stock_status'	=> 'N'
				);
				$this->channel_item_info_model->updateChannelItemInfo($update_data, array('item_info_id_in' => $success_item_ids));
			}
		}
	}
	
	public function update_tracking_number()
	{
		if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;
		
        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(42);

        $rest = $history_api->sendHistoryID();

    	$select	= 'o.order_id, o.package_no, o.channel_id, o.shipping_code, i.channel_order_no';
		$filter	= array(
			'status'		=> '5'
		,	'shipping_start'=> '1'
		,	'join_item'		=> '1'
//		,	'package_no'		=> '4321264981' // test
//		,	'channel_id'		=> 1 // test
        ,   'cancel_flag'   => '0'  //부분취소 플래그 추가 20190220 KSJ
        ,   'package_no_not_ins' => array('841950748')
		);
		$shipping_on_datas	= $this->order_model->getOrders($filter, $select, array(), $select);
		$shipped_datas		= array();
		foreach($shipping_on_datas->result_array() as $shipped_order){
			if(element(element('channel_id',$shipped_order),$shipped_datas, false) === false){
				$shipped_datas[element('channel_id',$shipped_order)]	= array();
			}
			array_push($shipped_datas[element('channel_id',$shipped_order)],$shipped_order);
		}
		
		if(count($shipped_datas) < 1){
			echo "No Data !! Process END".PHP_EOL;
			return;
		}
		
		foreach($shipped_datas as $channel_id => $order_datas){
			$channel_info	= $this->channel_info_model->getChannelInfo(array('channel_id'=>$channel_id));
			
			switch(element('channel_code',$channel_info)){
				case 'G' :
					$update_track_num_method	= 'updateTrackingNoToGmarket';
					break;
				case 'A' :
					$update_track_num_method	= 'updateTrackingNoToAuction';
					break;
				default :
					return;
			}
			
			$success_order_ids	= array();
			$fail_order_ids		= array();
			
			foreach($order_datas as $order_info){
				$sync_result	= $this->{$update_track_num_method}($order_info);
				if(element('result',$sync_result) == 'Fail'){
					echo element('channel_order_no',$order_info)."Shipping Code Upload Error :: ".element('string',$sync_result).PHP_EOL;
					array_push($fail_order_ids,element('order_id',$order_info));
					continue;
				}
				array_push($success_order_ids,element('order_id',$order_info));
				echo element('channel_order_no',$order_info)."Shipping Code Upload END :: ".element('rs_msg',$sync_result).PHP_EOL;
			}
			
			$update_target_order_ids	= array_diff($success_order_ids, $fail_order_ids);
			if(count($update_target_order_ids) > 0) {
				$update_data	= array(
					'status' => '7'
				);
				$update_filter	= array(
					'order_id_in'	=> $update_target_order_ids
				);
				$this->order_model->updateOrder($update_data, $update_filter);
			}
		}
	}

    public function test()
    {
        if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;


        $select	= 'o.order_id, o.package_no, o.channel_id, o.shipping_code, i.channel_order_no';
        $filter	= array(
            'status'		=> '5'
        ,	'shipping_start'=> '1'
        ,	'join_item'		=> '1'
//		,	'package_no'		=> '4321264981' // test
        ,   'cancel_flag'   => '0'  //부분취소 플래그 추가 20190220 KSJ
        ,   'package_no_not_ins' => array('841950748')
        );
        $shipping_on_datas	= $this->order_model->getOrders($filter, $select, array(), $select);
        $shipped_datas		= array();
        foreach($shipping_on_datas->result_array() as $shipped_order){

            if(element(element('channel_id',$shipped_order),$shipped_datas, false) === false){
                $shipped_datas[element('channel_id',$shipped_order)]	= array();
            }
            array_push($shipped_datas[element('channel_id',$shipped_order)],$shipped_order);
        }

        echo "<pre>";
        var_dump($shipped_datas);
        echo "</pre>";

    }
	
	private function updateTrackingNoToAuction($order_info)
	{
		require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
		include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
		$this->api_key	= 'd310kxymI5jbPsYSxgyJ4M9BkjJbtr8HCRcsVRFRK34TOnBIhjWapNEP/kfX7fk0oL/mvCc2bBG9VItchZXNuX0nP5xx1c4/PDd+03Dp0b8+uZpHQPr/3hy4kSD3g4D+X4mYkO7BPw2VRvgXd966yJ44honypujpOuokhesVrSPGolEF5HAWQY4Jewkxlub9mdMEKSVqH4MNgvlAH3OXR+s=';
		
		$confirm_order_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/DoShippingGeneral.xml');
		$requestXmlBody	= str_replace(
			array('__API_ENCRYPT_KEY__','__CHANNEL_ORDER_NO__', '__SEND_DATE__', '__SHIPPING_CODE_NO__', '__DELIVERY_AGENCY__')
			,	array($this->api_key, element('channel_order_no',$order_info), date('Y-m-d'), element('shipping_code',$order_info), 'shiptrack')
			,	$confirm_order_dummy
		);
		
		$serverUrl		= "https://api.auction.co.kr/APIv1/AuctionService.asmx";
		$action			= "http://www.auction.co.kr/APIv1/AuctionService/DoShippingGeneral";
		
		return requestAuction($serverUrl, $action, $requestXmlBody);
		
	}
	
	private function updateTrackingNoToGmarket($order_info)
	{
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
		$this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
		$info	= array(
			'PackNo'		=> element('package_no',$order_info)
		,	'ContrNo'		=> element('channel_order_no',$order_info)
		,	'ExpressName'	=> '쉽트랙'
		,	'InvoiceNo'		=> element('shipping_code',$order_info)
		,	'ShippingDate'	=> date('Y-m-d')

		);
		$AddShipping = new \sdk\controller\AddShipping();
		$AddShipping->setTicket($this->api_key);
		$AddShipping->setShippingInfo($info);

		$result	= $AddShipping->getResponse();

		if(element('faultcode', $result, false) !== false){
			return array(
				'result'	=> 'Fail'
			,	'string'	=> element('faultstring',$result)
			);
		}
		if(element('Result', $result, 'Faile') != 'Success'){
			return array(
				'result'	=> 'Fail'
			,	'string'	=> element('Comment',$result)
			);
		}
		return array(
			'result'	=> 'Success'
		,	'rs_msg'	=> element('Comment',$result)
		);
	}
	
	public function itemPriceUpdate()
	{
		// 채널정보
		$channel_arr	= $this->channel_info_model->getChannelInfos(array());
		// 채널별 분기
		foreach($channel_arr->result_array() as $channel_info){
			$success_item_ids	= array();
			$this->sales_channel_config	= null;
			if(element('channel_code',$channel_info) == 'G') continue;
			switch(element('channel_code',$channel_info)){
				case 'G':
					$this->load->config('api_config_gmarket', true);
					$this->sales_channel_config	= $this->config->item(element('account_id',$channel_info), 'api_config_gmarket');
					$stockInItemSync_method	= 'callAddPrice';
					
					break;
				case 'A':
					$this->load->config('api_config_auction', true);
					$this->sales_channel_config	= $this->config->item(element('account_id',$channel_info), 'api_config_auction');
					$stockInItemSync_method	= 'callReviseItem';
					
					break;
				default :
				
			}
			$this->api_key	= element('api_key',$this->sales_channel_config);
			
			// update 대상
			$filter	= array(
				'channel_id'	=> element('channel_id',$channel_info)
			,	'master_info'	=> '1'
			,	'upper_uid'		=> 2825 // test
			);
			$select	= 'i.item_info_id, i.channel_id, i.channel_item_code, i.virtual_item_id, i.upload_price, i.stock_status, d.master_item_id, m.currentqty';
			$item_result		= $this->channel_item_info_model->getChannelItemInfos($filter, $select);
			
			foreach($item_result->result_array() as $item_info){
				
				// 채널에 상품 정보 동기화
				$sync_result	= $this->{$stockInItemSync_method}($item_info);
				
				if(element('result',$sync_result) == 'Fail'){
					
					echo "UPDATE Error id ::".element('item_info_id',$item_info)."|| itemcode :: ".element('channel_item_code',$item_info)."||".element('string',$sync_result).PHP_EOL;
					continue;
					
				}
				
				echo "UPDATE ::".element('item_info_id',$item_info)."|| itemcode :: ".element('channel_item_code',$item_info).PHP_EOL;
				
			}
			
		}
		
	}
	
	private function callReviseItem($item_info)
	{
		require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
		include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
		
		$item_selling_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItem.xml');
		$requestXmlBody	= str_replace(
			array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__', '__ITEM_PRICE__','__SELLER_DISCOUNT_PRICE__')
			,	array($this->api_key, element('channel_item_code',$item_info), element('upload_price',$item_info), '')
			,	$item_selling_dummy
		);

		$serverUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
		$action			= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItem";
		
		return requestAuction($serverUrl, $action, $requestXmlBody);
		
	}

    function updateProductPrice(){

        //cron chk
        $cnt = $this->process_scheduling->getProcessSchedulingCount(array('full_query'=>"
        (process_status ='1' and process_code = 'stockInItemSync' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -3 hour),'%Y%m%d%H'))
        or 
        (process_status ='1' and process_code = 'updateProductPrice' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -10 hour),'%Y%m%d%H'))
        "));

        if($cnt>0){
            $process_scheduling_add_data =array(
                'process_code' => 'updateProductPrice',
                'process_status' => '3',
                'process_start_date' => date ("Y-m-d H:i:s"),
                'process_end_date' => date ("Y-m-d H:i:s")
            );
            $this->process_scheduling->addProcessScheduling($process_scheduling_add_data);
            return;
        }

        $process_scheduling_add_data =array(
            'process_code' => 'updateProductPrice',
            'process_status' => '1',
            'process_start_date' => date ("Y-m-d H:i:s")
        );
        $process_scheduling_inset_id=$this->process_scheduling->addProcessScheduling($process_scheduling_add_data);

        echo 'start';

        $update_data_result = $this->channel_item_info_model->getItemPriceUpdateHistoryInfos(array('upload_fg' => '1','update_join'=>'Y'),'a.item_history_id, a.worker_id, b.channel_id, a.channel_item_code, a.upload_price, a.discount_price, a.discount_unit, c.item_info_id,stock_status');

        $discount_unit_arr =array('RATE','MONEY');

        foreach ($update_data_result ->result_array() as $date_info){

            $upate_historyfilter = array('item_history_id'=> element('item_history_id',$date_info));

            $channel_item_code = element('channel_item_code',$date_info);

            if(!in_array(strtoupper(element('discount_unit',$date_info)),$discount_unit_arr)){
                $date_info['discount_unit'] = 'N';
            }

            $discount_unit = ucwords(strtolower(element('discount_unit',$date_info)));;

            if(!is_numeric(element('discount_price',$date_info,'')) && element('discount_price',$date_info,'')!=''&& $discount_unit!='N'){
                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'4','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
                continue;
            }

            $discount_value = element('discount_price',$date_info,'');

            if(!is_numeric(element('upload_price',$date_info,'')) || element('upload_price',$date_info,'')=='0'){
                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'4','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
                continue;
            }
            $upload_price = element('upload_price',$date_info);

            $price_info = array(
                'discount_type'=>$discount_unit,
                'discount_value'=>$discount_value,
                'basic_price'=>$upload_price
            );

            $result  = false;
            switch ($date_info['channel_id']){

                case '1' : //fastople gmarket
                    $result = $this->sendPrice($channel_item_code,element('basic_price',$price_info));

                    if($discount_unit != 'N'){

                        $result  = $this->sendDiscountPrice($channel_item_code,$price_info);

                    }else{

                        $price_info = array(
                            'discount_type'=>'Rate',//임의의값
                            'discount_value'=>'0',
                            'basic_price'=>$upload_price
                        );

                        $result  = $this->sendDiscountPrice($channel_item_code,$price_info);

                    }

                    break;

                case '2' : //fastople auction
                    $result = $this->callAuctionPriceUpdate($channel_item_code,$price_info);
                    break;
            }

            if($result===false){
                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'3','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
                continue;
            }


            $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'2','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);

            $update_date = array(
                'upload_price' => $upload_price,
                'discount_price' => $discount_value,
                'discount_unit'=> $discount_unit/*,
                'need_update'=>'Y'*/
            );

            if($date_info['channel_id']=='1' && $date_info['stock_status']=='N'){
                $update_date['need_update'] ='Y';
            }

            $filter= array(
                'item_info_id' => $date_info['item_info_id']
            );

            //channel_item_info
            $this->channel_item_info_model->updateChannelItemInfo($update_date,$filter);

            //가격조정 히스토리 쌓기
            $history_data = array(
                'item_info_id' => $date_info['item_info_id'],
                'channel_item_code' => $date_info['channel_item_code'],
                'upload_price' => $date_info['upload_price'],
                'discount_unit' => $date_info['discount_unit'],
                'discount_price' => $date_info['discount_price'],
                'action_fg' => 2,
                'worker_id' => $date_info['worker_id'],
                'create_date' => date('Y-m-d H:i:s'),
            );

            $this->channel_item_info_model->addChannelItemPriceHistory($history_data);
        }

        echo 'end';

        //cron chk
        $process_scheduling_update_data =array(
            'process_status'=>'2',
            'process_end_date'=>date("Y-m-d H:i:s")
        );
        $this->process_scheduling->updateProcessScheduling($process_scheduling_update_data,array('process_scheduling_id'=>$process_scheduling_inset_id));

        $this->stockInItemSync();

        return ;
    }

    private function sendPrice($channel_item_code, $price)
    {
        include_once '/ssd/html/api_sdks/gmarket/autoload.php';
        $this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
        $AddPrice	= new \sdk\controller\AddPrice();
        $AddPrice->setTicket($this->api_key);
        $price_info	= array(
            'GmktItemNo'	=> $channel_item_code
        ,	'DisplayDate'	=> date('Y-m-d', strtotime('+1 year'))
        ,	'SellPrice'		=> $price
        ,	'StockQty'		=> '99999'
        ,	'InventoryNo'	=> ''
        );
        $AddPrice->setProductPriceInfo($price_info);
        $response	= $AddPrice->getResponse();

        return (element('Result',$response) == 'Success') ? true : false;
    }
}




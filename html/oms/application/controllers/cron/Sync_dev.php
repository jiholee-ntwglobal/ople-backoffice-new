<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-05-30
 * File: Oms_order.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Sync_dev extends CI_Controller
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
        $this->load->model('item/auction_stock_api_history_model');
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

    private function callAuctionPriceUpdate($channel_item_code, $price_info, $api_key)
    {

        require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
        include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
        //$this->api_key	= 'd310kxymI5jbPsYSxgyJ4M9BkjJbtr8HCRcsVRFRK34TOnBIhjWapNEP/kfX7fk0oL/mvCc2bBG9VItchZXNuX0nP5xx1c4/PDd+03Dp0b8+uZpHQPr/3hy4kSD3g4D+X4mYkO7BPw2VRvgXd966yJ44honypujpOuokhesVrSPGolEF5HAWQY4Jewkxlub9mdMEKSVqH4MNgvlAH3OXR+s=';

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
            ,	array($api_key, $channel_item_code, element('basic_price',$price_info), $seller_discount)
            ,	$item_price_dummy
        );
        $serverUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
        $action			= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItem";

//		$sync_result	= requestAuction($serverUrl, $action, $requestXmlBody);
        $sync_result    = requestAuction($serverUrl, $action, $requestXmlBody);

        return (element('result',$sync_result) == 'Success') ? true : false;
    }

    private function sendDiscountPrice($channel_item_code, $price_info, $api_key)
    {
        include_once '/ssd/html/api_sdks/gmarket/autoload.php';
        //$this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
        $AddPremiumItem		= new \sdk\controller\AddPremiumItem();
        $AddPremiumItem->setTicket($api_key);
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
	
	private function callReviseItemSelling($item_info,$item_2038error_fg = '1')
	{
		require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
		include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
		$stock	= element('stock_status',$item_info) =='N' ? 'Stop':'OnSale';
		$period	= element('stock_status',$item_info) =='N' || $item_2038error_fg =='2' ? '':'<Period ApplyPeriod="90" />';
		
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
	    echo 'dd';
        //cron chk
        $cnt = $this->process_scheduling->getProcessSchedulingCount(array('full_query'=>
            "(process_status ='1' and process_code = 'stockInItemSync' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -3 hour),'%Y%m%d%H'))
            or
            (process_status ='1' and process_code = 'updateProductPrice' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -10 hour),'%Y%m%d%H'))"));

        //var_dump($cnt);
//        if($cnt>0){
//            $process_scheduling_add_data =array(
//                'process_code' => 'stockInItemSync',
//                'process_status' => '3',
//                'process_start_date' => date ("Y-m-d H:i:s"),
//                'process_end_date' => date ("Y-m-d H:i:s")
//            );
//            $this->process_scheduling->addProcessScheduling($process_scheduling_add_data);
//            return;
//        }

        $process_scheduling_add_data =array(
            'process_code' => 'stockInItemSync',
            'process_status' => '1',
            'process_start_date' => date ("Y-m-d H:i:s")
        );
        $process_scheduling_inset_id=$this->process_scheduling->addProcessScheduling($process_scheduling_add_data);

	    if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;
        echo 'ss';
		// 채널정보
		//$channel_arr	= $this->channel_info_model->getChannelInfos(array()); use_flag =0 인 채널까지 다 가져와서 품절돌리기 20190510 KSJ
        $channel_arr = $this->channel_info_model->getnewChannelInfos(array());
		// 채널별 분기
		foreach($channel_arr->result_array() as $channel_info){
			$success_item_ids	= array();
            $error_item_ids 	= array();
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
			//$this->api_key	= element('api_key',$this->sales_channel_config);
            $this->api_key = $this->channel_info_model->getApikey(array("account_id"=>element('account_id', $channel_info), "channel_code"=>element('channel_code', $channel_info)));
            echo 'aa';
            // update 대상
			$filter	= array(
				'need_update'	=> 'Y'
			,	'channel_id'	=> element('channel_id',$channel_info)
			,	'master_info'	=> '1'
//			,	'limit'	=> '1' // test
			);

			//2038년 오류 상번
            $item_2038error_arr = array();
            $item_2038error_result = $this->soldout_history_model->getSoldoutHistoryError(array('error_message_like'=>'최대 판매기간 초과입니다','groupby'=>'item_info_id'),'item_info_id');
            foreach($item_2038error_result->result_array() as $item_2038error_value){

                array_push($item_2038error_arr,element('item_info_id',$item_2038error_value));
            }

			$select	= 'i.item_info_id, i.channel_id, i.channel_item_code, i.virtual_item_id, i.upload_price, i.stock_status, d.master_item_id, m.currentqty';
			$item_result		= $this->channel_item_info_model->getChannelItemInfos($filter, $select);
			foreach($item_result->result_array() as $item_info){

			    if(in_array(element('item_info_id',$item_info),$item_2038error_arr)){
                    $sync_result = $this->{$stockInItemSync_method}($item_info,'2');
                }else {
			        // 채널에 상품 정보 동기화
                    $sync_result = $this->{$stockInItemSync_method}($item_info);
                }

                if(!is_array($sync_result)){
			        echo 'errr1';
                    $error_add_data	= array(
                        'item_info_id'			=> element('item_info_id',$item_info)
                    ,	'stock_status'			=> element('stock_status',$item_info)
                    ,	'currentqty'			=> element('currentqty',$item_info)
                    ,	'error_message'			=> 'Network error :: No response!'
                    ,	'soldout_process_type'	=> '1'
                    ,	'process_worker_id'		=> '0'
                    ,	'create_date'			=> date('Y-m-d H:i:s')
                    );
                    $this->soldout_history_model->addSoldoutHistoryError($error_add_data);
                    array_push($error_item_ids,element('item_info_id',$item_info));
                    continue;
                }

				if(element('result',$sync_result) == 'Fail'){
                    echo 'errr2';
				    if(element('string',$sync_result,false)=='Temporarlly Down for Maintenance.'){
				        break;
                    }
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
                    array_push($error_item_ids,element('item_info_id',$item_info));
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
				echo'success';
				echo "UPDATE ::".element('item_info_id',$item_info)."|| itemcode :: ".element('channel_item_code',$item_info).PHP_EOL;
			}

			if(count($success_item_ids) > 0) {
				$update_data	= array(
					'update_date' => date('Y-m-d H:i:s')
				,	'need_update' => 'N'
				);
				$this->channel_item_info_model->updateChannelItemInfo($update_data, array('item_info_id_in' => $success_item_ids));
			}
            if(count($error_item_ids) > 0) {
                $update_data	= array(
                    'update_date' => date('Y-m-d H:i:s')
                ,	'need_update' => 'E'
                );
                $this->channel_item_info_model->updateChannelItemInfo($update_data, array('item_info_id_in' => $error_item_ids));
            }
		}
        echo '55';
        //cron chk
        $process_scheduling_update_data =array(
            'process_status'=>'2',
            'process_end_date'=>date("Y-m-d H:i:s")
        );
        $this->process_scheduling->updateProcessScheduling($process_scheduling_update_data,array('process_scheduling_id'=>$process_scheduling_inset_id));

        //스케쥴러
        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(33);

        $rest = $history_api->sendHistoryID();
	}

    public function stockInItemSync_test()
    {
        echo 'dd';
        //cron chk
        $cnt = $this->process_scheduling->getProcessSchedulingCount(array('full_query'=>
            "(process_status ='1' and process_code = 'stockInItemSync' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -3 hour),'%Y%m%d%H'))
            or
            (process_status ='1' and process_code = 'updateProductPrice' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -10 hour),'%Y%m%d%H'))"));

        //var_dump($cnt);
//        if($cnt>0){
//            $process_scheduling_add_data =array(
//                'process_code' => 'stockInItemSync',
//                'process_status' => '3',
//                'process_start_date' => date ("Y-m-d H:i:s"),
//                'process_end_date' => date ("Y-m-d H:i:s")
//            );
//            $this->process_scheduling->addProcessScheduling($process_scheduling_add_data);
//            return;
//        }

        $process_scheduling_add_data =array(
            'process_code' => 'stockInItemSync',
            'process_status' => '1',
            'process_start_date' => date ("Y-m-d H:i:s")
        );
        $process_scheduling_inset_id=$this->process_scheduling->addProcessScheduling($process_scheduling_add_data);

        if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;
        echo 'ss';
        // 채널정보
        //$channel_arr	= $this->channel_info_model->getChannelInfos(array()); use_flag =0 인 채널까지 다 가져와서 품절돌리기 20190510 KSJ
        $channel_arr = $this->channel_info_model->getnewChannelInfos(array());
        // 채널별 분기
        foreach($channel_arr->result_array() as $channel_info){
            $success_item_ids	= array();
            $error_item_ids 	= array();
            $return_error_item_ids = array();
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
            //$this->api_key	= element('api_key',$this->sales_channel_config);
            $this->api_key = $this->channel_info_model->getApikey(array("account_id"=>element('account_id', $channel_info), "channel_code"=>element('channel_code', $channel_info)));
            echo 'aa';
            // update 대상
            $filter	= array(
                'need_update'	=> 'Y'
            ,	'channel_id'	=> element('channel_id',$channel_info)
            ,	'master_info'	=> '1'
//			,	'limit'	=> '1' // test
            );

            //2038년 오류 상번
            $item_2038error_arr = array();
            $item_2038error_result = $this->soldout_history_model->getSoldoutHistoryError(array('error_message_like'=>'최대 판매기간 초과입니다','groupby'=>'item_info_id'),'item_info_id');
            foreach($item_2038error_result->result_array() as $item_2038error_value){

                array_push($item_2038error_arr,element('item_info_id',$item_2038error_value));
            }

            $select	= 'i.item_info_id, i.channel_id, i.channel_item_code, i.virtual_item_id, i.upload_price, i.stock_status, d.master_item_id, m.currentqty';
            $item_result		= $this->channel_item_info_model->getChannelItemInfos($filter, $select);
            foreach($item_result->result_array() as $item_info){

                if(in_array(element('item_info_id',$item_info),$item_2038error_arr)){
                    $sync_result = $this->{$stockInItemSync_method}($item_info,'2');
                }else {
                    // 채널에 상품 정보 동기화
                    $sync_result = $this->{$stockInItemSync_method}($item_info);
                }

                if(!is_array($sync_result)){
                    echo 'errr1';
                    $error_add_data	= array(
                        'item_info_id'			=> element('item_info_id',$item_info)
                    ,	'stock_status'			=> element('stock_status',$item_info)
                    ,	'currentqty'			=> element('currentqty',$item_info)
                    ,	'error_message'			=> 'Network error :: No response!'
                    ,	'soldout_process_type'	=> '1'
                    ,	'process_worker_id'		=> '0'
                    ,	'create_date'			=> date('Y-m-d H:i:s')
                    );
                    $this->soldout_history_model->addSoldoutHistoryError($error_add_data);
                    array_push($return_error_item_ids,element('item_info_id',$item_info));
                    continue;
                }

                if(element('result',$sync_result) == 'Fail'){
                    echo 'errr2';
                    if(element('string',$sync_result,false)=='Temporarlly Down for Maintenance.'){
                        break;
                    }
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

                    //옥션, 재고가 0인 문제로 에러가 난 경우
                    if(element('channel_code',$channel_info)=="A" && strpos(element('string',$sync_result), "재고정보가 없거나") !== false){

                        // 옵션 스탁넘버 호출 및 처리
                        $result_data = $this->callViewItemStock($this->api_key,element('channel_item_code',$item_info));
                        $stockNoArr =  element("options",$result_data, array());
                        $stockNo =  $stockNoArr[0]['auction_stock_no'];

                        if($stockNo!=""){ // 재고번호 조회 API 성공시

                            // 재고조정
                            $result = $this->callReviseItemStockSingle(element('channel_item_code',$item_info), $this->api_key, $stockNo);

                            if(element('result',$result) != "Success"){ //재고조정 실패시 히스토리 쌓기

                                $add_history_data =  array(
                                    'channel_item_code'			=> element('channel_item_code',$item_info)
                                ,	'api_service'			=> "ReviseItemStock"
                                ,	'msg'			=> element('string',$result)
                                ,	'create_date'			=> date('Y-m-d')

                                );

                                $this->auction_stock_api_history_model->AddAuctionStockApiHistory($add_history_data);

                            }

                        }else{ //재고번호 조회 API 실패시
                            //재고번호 조회 API 실패시 히스토리 쌓기
                             $add_history_data =  array(
                                'channel_item_code'			=> element('channel_item_code',$item_info)
                            ,	'api_service'			=> "ViewItemStock"
                            ,	'msg'			=> element('string',$result_data)
                            ,	'create_date'			=> date('Y-m-d')

                            );

                            $this->auction_stock_api_history_model->AddAuctionStockApiHistory($add_history_data);

                        }
                    }

                    //20200121 KSJ 품절예외처리(다시 한번 더 시도하도록)
                    if (strpos(element('string',$sync_result), "잠시 후") !== false) {
                        array_push($return_error_item_ids,element('item_info_id',$item_info));
                    }else if (strpos(element('string',$sync_result), "최대 판매기간") !== false) {
                        array_push($return_error_item_ids,element('item_info_id',$item_info));
                    }else if (strpos(element('string',$sync_result), "Connection") !== false) {
                        array_push($return_error_item_ids,element('item_info_id',$item_info));
                    }else if (strpos(element('string',$sync_result), "재고정보가 없거나") !== false) {
                        array_push($return_error_item_ids,element('item_info_id',$item_info));
                    }else{
                        array_push($error_item_ids,element('item_info_id',$item_info));
                    }
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
                echo'success';
                echo "UPDATE ::".element('item_info_id',$item_info)."|| itemcode :: ".element('channel_item_code',$item_info).PHP_EOL;
            }

            if(count($success_item_ids) > 0) {
                $update_data	= array(
                    'update_date' => date('Y-m-d H:i:s')
                ,	'need_update' => 'N'
                );
                $this->channel_item_info_model->updateChannelItemInfo($update_data, array('item_info_id_in' => $success_item_ids));
            }
            if(count($error_item_ids) > 0) {
                $update_data	= array(
                    'update_date' => date('Y-m-d H:i:s')
                ,	'need_update' => 'E'
                );
                $this->channel_item_info_model->updateChannelItemInfo($update_data, array('item_info_id_in' => $error_item_ids));
            }
            //20200121 KSJ 품절예외처리(다시 한번 더 시도하도록 Y로 변경)
            if(count($return_error_item_ids)>0){
                $update_data	= array(
                    'update_date' => date('Y-m-d H:i:s')
                ,	'need_update' => 'Y'
                );
                $this->channel_item_info_model->updateChannelItemInfo($update_data, array('item_info_id_in' => $return_error_item_ids));
            }
        }
        echo '55';
        //cron chk
        $process_scheduling_update_data =array(
            'process_status'=>'2',
            'process_end_date'=>date("Y-m-d H:i:s")
        );
        $this->process_scheduling->updateProcessScheduling($process_scheduling_update_data,array('process_scheduling_id'=>$process_scheduling_inset_id));

        //스케쥴러
        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(33);

        $rest = $history_api->sendHistoryID();
    }


    public function clearanceItemSync()
	{
		if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;

		// 채널정보
		$channel_arr	= $this->channel_info_model->getNewChannelInfos(array());
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
			//$this->api_key	= element('api_key',$this->sales_channel_config);
            $this->api_key = $this->channel_info_model->getApikey(array("account_id"=>element('account_id', $channel_info), "channel_code"=>element('channel_code', $channel_info)));

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


        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(32);

        $rest = $history_api->sendHistoryID();
	}

    public function update_tracking_number()
    {
//		if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;

        $select	= 'o.order_id, o.package_no, o.channel_id, o.shipping_code, i.channel_order_no';
        $filter	= array(
            'status'		=> '5'
        ,	'shipping_start'=> '1'
        ,	'join_item'		=> '1'
//		,
// 'package_no'		=> '980903456' // test
//		,	'channel_id'		=> 1 // test
        ,   'cancel_flag'   => '0'  //부분취소 플래그 추가 20190220 KSJ
        ,   'package_no_not_ins' => array('841950748')

        );
        $shipping_on_datas	= $this->order_model->getOrders($filter, $select, array("o.order_id"=>"DESC"), $select);
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

            $this->api_key = element('api_key',$channel_info);

            $success_order_ids	= array();
            $fail_order_ids		= array();

            $error_arr = array(95518, 95517, 95445, 95410, 95408, 95368, 95367, 95366, 95365, 95364, 95311,
                95310, 95309, 95308, 95235, 95234, 95233, 95179, 95140, 95139, 95138, 95058,
                95057, 95055, 94990, 94989, 94988, 94933, 94929, 94830, 94829, 94828, 94827,
                94826, 94788, 94787, 94729, 94728, 94727, 94726, 94725, 94723, 94590, 94589,
                94588, 94587, 94586, 94585, 94584, 94583, 94551, 94550, 94548, 94468, 94467,
                94466, 94412, 94339, 94337, 94324, 94307, 94306, 94305, 94304, 94286, 94252,
                94251, 94250, 94191, 94190, 94189, 94120, 94119, 94118, 94117, 94115, 94057,
                94055, 94053, 93988, 93940, 93906, 93904, 93903, 93837, 93797, 93795, 93744,
                93742, 93695, 93693, 93662, 93660, 93622, 93621, 93592, 93591, 93590, 93551,
                93489, 93460, 93455, 93451, 93384, 93301, 93275, 93274, 93229, 93228, 93206,
                93202, 93132, 93091, 92983, 92982, 92981, 92903, 92861, 92860, 92859, 92817,
                92816, 92753, 92723, 92484, 92483, 92458, 92421, 92348, 92347, 92345, 92264,
                92100, 92085, 92083, 92068, 92051, 92003, 91947, 91912, 91911, 91910, 91909,
                91879, 91877, 91779, 91776, 91747, 91746, 91718, 91700, 91585, 91572, 91556,
                91552, 91534, 91532, 91505, 91503, 91473, 91471, 91448, 91408, 91355, 91329,
                91215, 91194, 91182, 91181, 91180, 91143, 91121, 91119, 91095, 91094, 91080,
                91079, 91077, 90993, 90975, 90974, 90935, 90867, 90835, 90834, 90783, 90780,
                90726, 90725, 90712, 90711, 90710, 90287, 90047, 90046, 90005, 90003, 90000,
                89999, 89922, 89864, 89803, 89802, 89647, 89646, 89610, 89559, 89558, 89557,
                89486, 89391, 89356, 89355, 89354, 89350, 89311, 89309, 89307, 89270, 89268,
                89228, 89226, 89225, 89194, 89192, 89154, 89153, 89124, 89091, 89090, 89054,
                89052, 89013, 88968, 88856, 88855, 88827, 88826, 88825, 88824, 88797, 88796,
                88758, 88541, 88540, 88539, 88538, 88501, 88497, 88496, 88398, 88366, 88247,
                88245, 88244, 88212, 88210, 88180, 88179, 88178, 88118, 88117, 88116, 88076,
                88075, 88023, 88009, 87997, 87966, 87941, 87883, 87882, 87837, 87836, 87835,
                87834, 87790, 87789, 87788, 87787, 87786, 87748, 87746, 87708, 87681, 87680,
                87658, 87581, 87555, 87554, 87501, 87499, 87477, 87476, 87474, 87455, 87413,
                87389, 87385, 87362, 87360, 87323, 87284, 87278, 87248, 87247, 87189, 87174,
                87172, 87171, 87170, 87112, 87075, 87048, 87042, 86964, 86931, 86930, 86928,
                86927, 86925, 86890, 86852, 86850, 86847, 86819, 86818, 86794, 86793, 86768,
                86758, 86757, 86756, 86439, 86386, 86308, 86304, 86272, 86131, 85987, 85657,
                85494, 85425, 85422, 85377, 85376, 85375, 85374, 85372, 85371, 85327, 85325,
                85324, 85323, 85322, 85218, 85187, 85186, 85148, 85146, 85093, 85091, 85088,
                85049, 85004, 85003, 84959, 84912, 84859, 84858, 84855, 84826, 84791, 84790,
                84753, 84740, 84717, 84700, 84644, 84599, 84549, 84503, 84374, 84333, 84329,
                84328, 84327, 84287, 84206, 84109, 84107, 84106, 84103, 84059, 84058, 84017,
                84016, 83970, 83969, 83934, 83933, 83932, 83913, 83684, 83600, 83530, 83431,
                83333, 83242, 83194, 83176, 83150, 83144, 83141, 83084, 83048, 82987, 82986,
                82941, 82940, 82905, 82871, 82753, 82747, 82479, 82456, 82439, 82438, 82412,
                82384, 82360, 82358, 82335, 82305, 82279, 82237, 82235, 82186, 82168, 82155,
                82044, 82043, 81960, 81924, 81895, 81894, 81892, 81860, 81859, 81826, 81824,
                81793, 81757, 81690, 81660, 81644, 81642, 81629, 81627, 81626, 81588, 81587,
                81586, 81563, 81562, 81527, 81525, 81524, 81436, 81435, 81389, 81388, 81387,
                81386, 81385, 81382, 81316, 81314, 81311, 81308, 81307, 81305, 81303, 81263,
                81262, 81260, 81258, 81256, 81241, 81239, 81238, 81237, 81236, 81217, 81216,
                81214, 81198, 81196, 81195, 81194, 81193, 81192, 81191, 81190, 81189, 81166,
                81165, 81164, 81163, 81161, 81160, 81146, 81143, 81142, 81141, 81138, 81118,
                81117, 81116, 81115, 81114, 81113, 81101, 81098, 81079, 81069, 81066, 81064,
                81055, 81053, 81050, 81028, 81025, 81008, 81005, 80983, 80981, 80976, 80954,
                80953, 80952, 80901, 80882, 80880, 80879, 80853, 80823, 80822, 80801, 80798,
                80796, 80792, 80775, 80774, 80772, 80771, 80723, 80719, 80695, 80673, 80671,
                80670, 80581, 80580, 80579, 80578, 80576, 80570, 80529, 80528, 80526, 80524,
                80523, 80486, 80481, 80480, 80478, 80475, 80474, 80473, 80472, 80468, 80428,
                80426, 80415, 80409, 80408, 80369, 80355, 80354, 80352, 80350, 80348, 80317,
                80316, 80313, 80312, 80307, 80275, 80269, 80266, 80265, 80228, 80227, 80226,
                80219, 80185, 80184, 80183, 80178, 80145, 80138, 80134, 80131, 80097, 80095,
                80094, 80093, 80091, 80069, 80068, 80067, 80034, 80033, 80031, 80027, 80026,
                80021, 80019, 80017, 80015, 79984, 79980, 79955, 79921, 79918, 79916, 79896,
                79894, 79882, 79880, 79879, 79865, 79854, 79853, 79841, 79839, 79838, 79837,
                79816, 79803, 79801, 79731, 79730, 79716, 79713, 79712, 79691, 79619, 79616,
                79606, 79603, 79567, 79485, 79439, 79374, 79326, 79310, 79305, 79294, 79278,
                79260, 79204, 79188, 79118, 79083, 78999, 78997, 78956, 78937, 78934, 78896,
                78882, 78868, 78809, 78805, 78803, 78787, 78723, 78667, 78644, 78642, 78619,
                78560, 78557, 78538, 78470, 78466, 78402, 78249, 78118, 78111, 78098, 78097,
                78085, 78081, 78079, 78070, 78063, 78057, 78055, 78040, 78029, 78022, 78018,
                78004, 78002, 77978, 77977, 77976, 77974, 77954, 77953, 77952, 77950, 77933,
                77932, 77912, 77891, 77889, 77888, 77859, 77855, 77823, 77822, 77818, 77814,
                77813, 77798, 77797, 77789, 77780, 77779, 77771, 77762, 77757, 77741, 77722,
                77721, 77704, 77669, 77661, 77659, 77648, 77621, 77620, 77602, 77592, 77591,
                77580, 77571, 77562, 77537, 77526, 77515, 77513, 77496, 77494, 77483, 77474,
                77473, 77466, 77454, 77441, 77440, 77439, 77438, 77431, 77429, 77428, 77427,
                77426, 77406, 77397, 77387, 77369, 77368, 77364, 77363, 77362, 77358, 77355,
                77333, 77329, 77328, 77322, 77307, 77297, 77295, 77282, 77280, 77272, 77271,
                77260, 77259, 77256, 77241, 77240, 77239, 77226, 77209, 77200, 77199, 77190,
                77187, 77171, 77155, 77148, 77146, 77142, 77086, 77052, 77033, 76902, 76851,
                76591, 76525, 76497, 76435, 76433, 76375, 76341, 76306, 76304, 76303, 76289,
                76272, 76271, 76242, 48702, 48700, 48699, 48673, 48644, 48622, 48621, 48620,
                48617, 48597, 48580, 48579, 48577, 48510, 48501, 48500, 48432, 48431, 48397,
                48376, 48375, 48374, 48353, 48343, 48331, 48329, 48328, 48325, 48311, 48303,
                48288, 48255, 48229, 48225, 48224, 48202, 48184, 48176, 48164, 48157, 48151,
                48150, 48141, 48140, 48139, 48131, 48130, 48118, 48117, 48107, 48060, 48057,
                48042, 48035, 48023, 48022, 47974, 47965, 47943, 47928, 47915, 47873, 47841,
                47836, 47829, 47828, 47788, 47772, 47761, 47738, 47720, 47701, 47640, 47618,
                47601, 47539, 47531, 47523, 47500, 47376, 47353, 47231, 47141, 47112, 47110,
                47089, 46861, 46859, 46858, 46847, 46846, 46843, 46832, 46831, 46827, 46824,
                46816, 46814, 46806, 46803, 46798, 46791, 46786, 46785, 46777, 46773, 46752,
                46747, 46744, 46720, 46718, 46697, 46565, 46554, 46447, 46411, 46368, 46343,
                46248, 45731, 43911, 38246, 38087, 38078, 38066, 38038, 37989, 37986, 37984,
                37943, 37914, 37841, 37758, 37714, 37093, 36873, 36703, 36692, 36653, 36649,
                36610, 36603, 36591, 36583, 36561, 36189, 36136, 36128, 36106, 36096, 36072,
                36049, 35982, 35956, 35919, 35913, 35846, 35808, 35789, 35784, 35772, 35725,
                35708, 35649, 35642, 35639, 35626, 35625, 35606, 35605, 35602, 35590, 35586,
                35529, 35525, 35503, 35502, 35475, 35410, 35378, 35372, 35365, 35347, 35143,
                34903, 34900, 34860, 34859, 34834, 34788, 34719, 34715, 34695, 34670, 34646,
                34618, 34478, 34439, 34421, 34420, 34419, 34413, 34394, 34375, 34362, 34356,
                34307, 34295, 34268, 34260, 34258, 34256, 34253, 34252, 34251, 34250, 34249,
                34248, 34243, 34242, 34241, 34240, 34239, 34238, 34237, 34236, 34235, 34080,
                34043, 33904, 33391, 32598, 32586, 32584, 32583, 32575, 32573, 32568, 32562,
                32561, 32550, 32544, 32543, 32535, 32534, 32533, 32532, 32531, 32511, 32471,
                32466, 32465, 32464, 32449, 32444, 32434, 32432, 32429, 32420, 32417, 32403,
                32402, 32397, 32393, 32357, 32351, 32350, 32347, 32343, 32340, 32333, 32308,
                32299, 32296, 32294, 32283, 32281, 32279, 32278, 32277);
            $shipping_api_status = false;
            foreach($order_datas as $order_info){


                if(in_array(element('order_id',$order_info),$error_arr)){
                    echo'dd';
                    continue;
                }
                $sync_result	= $this->{$update_track_num_method}($order_info);
                if(!empty($sync_result)) {

                    if (element('result', $sync_result) == 'Fail') {

                        //송장번호가 이미 완료됐다고 하는 에러 메세지들은 걸러서 주문서 상태 변경하기 -KSJ 20191210
                        //1. 경매상태가 발송가능한 상태가 아닙니다.
                        //2. 기 배송중/배송완료 상태
                        //3.Specified argument was out of the range of valid values.
                        //4. 경매상태가 발송중입니다.

                        if(strpos(element('string', $sync_result), "경매상태가") !== false) $shipping_api_status = true;
                        if(strpos(element('string', $sync_result), "배송중/배송완료") !== false) $shipping_api_status = true;
                        if(strpos(element('string', $sync_result), "Specified argument") !== false) $shipping_api_status = true;

                        if($shipping_api_status) {
                            //에러메세지 따라서 주문서 상태(배송->완료) 하는 주문서들 history 쌓기 -KSJ 20191210
                            $this->order_model->addupdate_tracking_error_history(array(
                                'order_id' => element('order_id', $order_info),
                                'package_no' => element('package_no', $order_info),
                                'channel_id' => element('channel_id', $order_info),
                                'shipping_code' => element('shipping_code', $order_info),
                                'channel_order_no' => element('channel_order_no', $order_info),
                                'error_message' => element('string', $sync_result),
                                'create_date' => date('Y-m-d H:i:s')
                            ));
                            echo element('channel_order_no', $order_info) . "Shipping Code Upload Error MSG :: Completion :: " . element('string', $sync_result) . PHP_EOL;
                        }else{
                            $this->order_model->addupdate_tracking_number_error(array(
                                'order_id' => element('order_id', $order_info),
                                'error_message' => element('string', $sync_result),
                                'create_date' => date('Y-m-d H:i:s')
                            ));
                            echo element('channel_order_no', $order_info) . "Shipping Code Upload Error :: " . element('string', $sync_result) . PHP_EOL;

                            array_push($fail_order_ids, element('order_id', $order_info));
                            continue;
                        }
                    }
                    array_push($success_order_ids, element('order_id', $order_info));
                    if(!$shipping_api_status) echo element('channel_order_no', $order_info) . "Shipping Code Upload END :: " . element('rs_msg', $sync_result) . PHP_EOL;
                }else{
                    $this->order_model->addupdate_tracking_number_error(array(
                        'order_id' => element('order_id', $order_info),
                        'error_message' => "API 통신오류 array null",
                        'create_date' => date('Y-m-d H:i:s')
                    ));
                    echo element('channel_order_no', $order_info) . "Shipping Code Upload Error :: API null" . PHP_EOL;
                    array_push($fail_order_ids, element('order_id', $order_info));
                    continue;
                }
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

        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(42);

        $rest = $history_api->sendHistoryID();
    }

    public function update_tracking_number_2()
    {
        // 11/1~ 11/25 까지 송장 업데이트 1일 1회 오전 7시 50분
//		if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;

        $select	= 'o.order_id, o.package_no, o.channel_id, o.shipping_code, i.channel_order_no';
        $filter	= array(
            'status'		=> '5'
        ,	'shipping_start'=> '1'
        ,	'join_item'		=> '1'
//		,	'package_no'		=> '980903456' // test
//		,	'channel_id'		=> 1 // test
        ,   'cancel_flag'   => '0'  //부분취소 플래그 추가 20190220 KSJ
        ,   'package_no_not_ins' => array('841950748')
        ,   'od_date_filter_between' => array('2019-11-01','2019-11-24')

        );
        $shipping_on_datas	= $this->order_model->getOrders($filter, $select, array("o.order_id"=>"DESC"), $select);
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

            $this->api_key = element('api_key',$channel_info);

            $success_order_ids	= array();
            $fail_order_ids		= array();

            $error_arr = array(95518, 95517, 95445, 95410, 95408, 95368, 95367, 95366, 95365, 95364, 95311,
                95310, 95309, 95308, 95235, 95234, 95233, 95179, 95140, 95139, 95138, 95058,
                95057, 95055, 94990, 94989, 94988, 94933, 94929, 94830, 94829, 94828, 94827,
                94826, 94788, 94787, 94729, 94728, 94727, 94726, 94725, 94723, 94590, 94589,
                94588, 94587, 94586, 94585, 94584, 94583, 94551, 94550, 94548, 94468, 94467,
                94466, 94412, 94339, 94337, 94324, 94307, 94306, 94305, 94304, 94286, 94252,
                94251, 94250, 94191, 94190, 94189, 94120, 94119, 94118, 94117, 94115, 94057,
                94055, 94053, 93988, 93940, 93906, 93904, 93903, 93837, 93797, 93795, 93744,
                93742, 93695, 93693, 93662, 93660, 93622, 93621, 93592, 93591, 93590, 93551,
                93489, 93460, 93455, 93451, 93384, 93301, 93275, 93274, 93229, 93228, 93206,
                93202, 93132, 93091, 92983, 92982, 92981, 92903, 92861, 92860, 92859, 92817,
                92816, 92753, 92723, 92484, 92483, 92458, 92421, 92348, 92347, 92345, 92264,
                92100, 92085, 92083, 92068, 92051, 92003, 91947, 91912, 91911, 91910, 91909,
                91879, 91877, 91779, 91776, 91747, 91746, 91718, 91700, 91585, 91572, 91556,
                91552, 91534, 91532, 91505, 91503, 91473, 91471, 91448, 91408, 91355, 91329,
                91215, 91194, 91182, 91181, 91180, 91143, 91121, 91119, 91095, 91094, 91080,
                91079, 91077, 90993, 90975, 90974, 90935, 90867, 90835, 90834, 90783, 90780,
                90726, 90725, 90712, 90711, 90710, 90287, 90047, 90046, 90005, 90003, 90000,
                89999, 89922, 89864, 89803, 89802, 89647, 89646, 89610, 89559, 89558, 89557,
                89486, 89391, 89356, 89355, 89354, 89350, 89311, 89309, 89307, 89270, 89268,
                89228, 89226, 89225, 89194, 89192, 89154, 89153, 89124, 89091, 89090, 89054,
                89052, 89013, 88968, 88856, 88855, 88827, 88826, 88825, 88824, 88797, 88796,
                88758, 88541, 88540, 88539, 88538, 88501, 88497, 88496, 88398, 88366, 88247,
                88245, 88244, 88212, 88210, 88180, 88179, 88178, 88118, 88117, 88116, 88076,
                88075, 88023, 88009, 87997, 87966, 87941, 87883, 87882, 87837, 87836, 87835,
                87834, 87790, 87789, 87788, 87787, 87786, 87748, 87746, 87708, 87681, 87680,
                87658, 87581, 87555, 87554, 87501, 87499, 87477, 87476, 87474, 87455, 87413,
                87389, 87385, 87362, 87360, 87323, 87284, 87278, 87248, 87247, 87189, 87174,
                87172, 87171, 87170, 87112, 87075, 87048, 87042, 86964, 86931, 86930, 86928,
                86927, 86925, 86890, 86852, 86850, 86847, 86819, 86818, 86794, 86793, 86768,
                86758, 86757, 86756, 86439, 86386, 86308, 86304, 86272, 86131, 85987, 85657,
                85494, 85425, 85422, 85377, 85376, 85375, 85374, 85372, 85371, 85327, 85325,
                85324, 85323, 85322, 85218, 85187, 85186, 85148, 85146, 85093, 85091, 85088,
                85049, 85004, 85003, 84959, 84912, 84859, 84858, 84855, 84826, 84791, 84790,
                84753, 84740, 84717, 84700, 84644, 84599, 84549, 84503, 84374, 84333, 84329,
                84328, 84327, 84287, 84206, 84109, 84107, 84106, 84103, 84059, 84058, 84017,
                84016, 83970, 83969, 83934, 83933, 83932, 83913, 83684, 83600, 83530, 83431,
                83333, 83242, 83194, 83176, 83150, 83144, 83141, 83084, 83048, 82987, 82986,
                82941, 82940, 82905, 82871, 82753, 82747, 82479, 82456, 82439, 82438, 82412,
                82384, 82360, 82358, 82335, 82305, 82279, 82237, 82235, 82186, 82168, 82155,
                82044, 82043, 81960, 81924, 81895, 81894, 81892, 81860, 81859, 81826, 81824,
                81793, 81757, 81690, 81660, 81644, 81642, 81629, 81627, 81626, 81588, 81587,
                81586, 81563, 81562, 81527, 81525, 81524, 81436, 81435, 81389, 81388, 81387,
                81386, 81385, 81382, 81316, 81314, 81311, 81308, 81307, 81305, 81303, 81263,
                81262, 81260, 81258, 81256, 81241, 81239, 81238, 81237, 81236, 81217, 81216,
                81214, 81198, 81196, 81195, 81194, 81193, 81192, 81191, 81190, 81189, 81166,
                81165, 81164, 81163, 81161, 81160, 81146, 81143, 81142, 81141, 81138, 81118,
                81117, 81116, 81115, 81114, 81113, 81101, 81098, 81079, 81069, 81066, 81064,
                81055, 81053, 81050, 81028, 81025, 81008, 81005, 80983, 80981, 80976, 80954,
                80953, 80952, 80901, 80882, 80880, 80879, 80853, 80823, 80822, 80801, 80798,
                80796, 80792, 80775, 80774, 80772, 80771, 80723, 80719, 80695, 80673, 80671,
                80670, 80581, 80580, 80579, 80578, 80576, 80570, 80529, 80528, 80526, 80524,
                80523, 80486, 80481, 80480, 80478, 80475, 80474, 80473, 80472, 80468, 80428,
                80426, 80415, 80409, 80408, 80369, 80355, 80354, 80352, 80350, 80348, 80317,
                80316, 80313, 80312, 80307, 80275, 80269, 80266, 80265, 80228, 80227, 80226,
                80219, 80185, 80184, 80183, 80178, 80145, 80138, 80134, 80131, 80097, 80095,
                80094, 80093, 80091, 80069, 80068, 80067, 80034, 80033, 80031, 80027, 80026,
                80021, 80019, 80017, 80015, 79984, 79980, 79955, 79921, 79918, 79916, 79896,
                79894, 79882, 79880, 79879, 79865, 79854, 79853, 79841, 79839, 79838, 79837,
                79816, 79803, 79801, 79731, 79730, 79716, 79713, 79712, 79691, 79619, 79616,
                79606, 79603, 79567, 79485, 79439, 79374, 79326, 79310, 79305, 79294, 79278,
                79260, 79204, 79188, 79118, 79083, 78999, 78997, 78956, 78937, 78934, 78896,
                78882, 78868, 78809, 78805, 78803, 78787, 78723, 78667, 78644, 78642, 78619,
                78560, 78557, 78538, 78470, 78466, 78402, 78249, 78118, 78111, 78098, 78097,
                78085, 78081, 78079, 78070, 78063, 78057, 78055, 78040, 78029, 78022, 78018,
                78004, 78002, 77978, 77977, 77976, 77974, 77954, 77953, 77952, 77950, 77933,
                77932, 77912, 77891, 77889, 77888, 77859, 77855, 77823, 77822, 77818, 77814,
                77813, 77798, 77797, 77789, 77780, 77779, 77771, 77762, 77757, 77741, 77722,
                77721, 77704, 77669, 77661, 77659, 77648, 77621, 77620, 77602, 77592, 77591,
                77580, 77571, 77562, 77537, 77526, 77515, 77513, 77496, 77494, 77483, 77474,
                77473, 77466, 77454, 77441, 77440, 77439, 77438, 77431, 77429, 77428, 77427,
                77426, 77406, 77397, 77387, 77369, 77368, 77364, 77363, 77362, 77358, 77355,
                77333, 77329, 77328, 77322, 77307, 77297, 77295, 77282, 77280, 77272, 77271,
                77260, 77259, 77256, 77241, 77240, 77239, 77226, 77209, 77200, 77199, 77190,
                77187, 77171, 77155, 77148, 77146, 77142, 77086, 77052, 77033, 76902, 76851,
                76591, 76525, 76497, 76435, 76433, 76375, 76341, 76306, 76304, 76303, 76289,
                76272, 76271, 76242, 48702, 48700, 48699, 48673, 48644, 48622, 48621, 48620,
                48617, 48597, 48580, 48579, 48577, 48510, 48501, 48500, 48432, 48431, 48397,
                48376, 48375, 48374, 48353, 48343, 48331, 48329, 48328, 48325, 48311, 48303,
                48288, 48255, 48229, 48225, 48224, 48202, 48184, 48176, 48164, 48157, 48151,
                48150, 48141, 48140, 48139, 48131, 48130, 48118, 48117, 48107, 48060, 48057,
                48042, 48035, 48023, 48022, 47974, 47965, 47943, 47928, 47915, 47873, 47841,
                47836, 47829, 47828, 47788, 47772, 47761, 47738, 47720, 47701, 47640, 47618,
                47601, 47539, 47531, 47523, 47500, 47376, 47353, 47231, 47141, 47112, 47110,
                47089, 46861, 46859, 46858, 46847, 46846, 46843, 46832, 46831, 46827, 46824,
                46816, 46814, 46806, 46803, 46798, 46791, 46786, 46785, 46777, 46773, 46752,
                46747, 46744, 46720, 46718, 46697, 46565, 46554, 46447, 46411, 46368, 46343,
                46248, 45731, 43911, 38246, 38087, 38078, 38066, 38038, 37989, 37986, 37984,
                37943, 37914, 37841, 37758, 37714, 37093, 36873, 36703, 36692, 36653, 36649,
                36610, 36603, 36591, 36583, 36561, 36189, 36136, 36128, 36106, 36096, 36072,
                36049, 35982, 35956, 35919, 35913, 35846, 35808, 35789, 35784, 35772, 35725,
                35708, 35649, 35642, 35639, 35626, 35625, 35606, 35605, 35602, 35590, 35586,
                35529, 35525, 35503, 35502, 35475, 35410, 35378, 35372, 35365, 35347, 35143,
                34903, 34900, 34860, 34859, 34834, 34788, 34719, 34715, 34695, 34670, 34646,
                34618, 34478, 34439, 34421, 34420, 34419, 34413, 34394, 34375, 34362, 34356,
                34307, 34295, 34268, 34260, 34258, 34256, 34253, 34252, 34251, 34250, 34249,
                34248, 34243, 34242, 34241, 34240, 34239, 34238, 34237, 34236, 34235, 34080,
                34043, 33904, 33391, 32598, 32586, 32584, 32583, 32575, 32573, 32568, 32562,
                32561, 32550, 32544, 32543, 32535, 32534, 32533, 32532, 32531, 32511, 32471,
                32466, 32465, 32464, 32449, 32444, 32434, 32432, 32429, 32420, 32417, 32403,
                32402, 32397, 32393, 32357, 32351, 32350, 32347, 32343, 32340, 32333, 32308,
                32299, 32296, 32294, 32283, 32281, 32279, 32278, 32277);
            foreach($order_datas as $order_info){


                if(in_array(element('order_id',$order_info),$error_arr)){
                    echo'dd';
                    continue;
                }
                $sync_result	= $this->{$update_track_num_method}($order_info);
                if(!empty($sync_result)) {
                    if (element('result', $sync_result) == 'Fail') {

                        $this->order_model->addupdate_tracking_number_error(array(
                            'order_id' => element('order_id', $order_info),
                            'error_message' => element('string', $sync_result),
                            'create_date' => date('Y-m-d H:i:s')
                        ));
                        echo element('channel_order_no', $order_info) . "Shipping Code Upload Error :: " . element('string', $sync_result) . PHP_EOL;
                        array_push($fail_order_ids, element('order_id', $order_info));
                        continue;
                    }
                    array_push($success_order_ids, element('order_id', $order_info));
                    echo element('channel_order_no', $order_info) . "Shipping Code Upload END :: " . element('rs_msg', $sync_result) . PHP_EOL;
                }else{
                    $this->order_model->addupdate_tracking_number_error(array(
                        'order_id' => element('order_id', $order_info),
                        'error_message' => "API 통신오류 array null",
                        'create_date' => date('Y-m-d H:i:s')
                    ));
                    echo element('channel_order_no', $order_info) . "Shipping Code Upload Error :: API null" . PHP_EOL;
                    array_push($fail_order_ids, element('order_id', $order_info));
                    continue;
                }
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

        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(78);

        $rest = $history_api->sendHistoryID();
    }

    public function update_tracking_number_test()
    {
//		if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;

        $select	= 'o.order_id, o.package_no, o.channel_id, o.shipping_code, i.channel_order_no';
        $filter	= array(
            'status'		=> '5'
        ,	'shipping_start'=> '1'
        ,	'join_item'		=> '1'
//		,
// 'package_no'		=> '980903456' // test
//		,	'channel_id'		=> 1 // test
        ,   'cancel_flag'   => '0'  //부분취소 플래그 추가 20190220 KSJ
        ,   'package_no_not_ins' => array('841950748')
        ,   'od_date_filter_between' => array('2019-11-01','2019-11-24')

        );
        $shipping_on_datas	= $this->order_model->getOrders($filter, $select, array("o.order_id"=>"DESC"), $select);
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

            $this->api_key = element('api_key',$channel_info);

            $success_order_ids	= array();
            $fail_order_ids		= array();

            $error_arr = array(95518, 95517, 95445, 95410, 95408, 95368, 95367, 95366, 95365, 95364, 95311,
                95310, 95309, 95308, 95235, 95234, 95233, 95179, 95140, 95139, 95138, 95058,
                95057, 95055, 94990, 94989, 94988, 94933, 94929, 94830, 94829, 94828, 94827,
                94826, 94788, 94787, 94729, 94728, 94727, 94726, 94725, 94723, 94590, 94589,
                94588, 94587, 94586, 94585, 94584, 94583, 94551, 94550, 94548, 94468, 94467,
                94466, 94412, 94339, 94337, 94324, 94307, 94306, 94305, 94304, 94286, 94252,
                94251, 94250, 94191, 94190, 94189, 94120, 94119, 94118, 94117, 94115, 94057,
                94055, 94053, 93988, 93940, 93906, 93904, 93903, 93837, 93797, 93795, 93744,
                93742, 93695, 93693, 93662, 93660, 93622, 93621, 93592, 93591, 93590, 93551,
                93489, 93460, 93455, 93451, 93384, 93301, 93275, 93274, 93229, 93228, 93206,
                93202, 93132, 93091, 92983, 92982, 92981, 92903, 92861, 92860, 92859, 92817,
                92816, 92753, 92723, 92484, 92483, 92458, 92421, 92348, 92347, 92345, 92264,
                92100, 92085, 92083, 92068, 92051, 92003, 91947, 91912, 91911, 91910, 91909,
                91879, 91877, 91779, 91776, 91747, 91746, 91718, 91700, 91585, 91572, 91556,
                91552, 91534, 91532, 91505, 91503, 91473, 91471, 91448, 91408, 91355, 91329,
                91215, 91194, 91182, 91181, 91180, 91143, 91121, 91119, 91095, 91094, 91080,
                91079, 91077, 90993, 90975, 90974, 90935, 90867, 90835, 90834, 90783, 90780,
                90726, 90725, 90712, 90711, 90710, 90287, 90047, 90046, 90005, 90003, 90000,
                89999, 89922, 89864, 89803, 89802, 89647, 89646, 89610, 89559, 89558, 89557,
                89486, 89391, 89356, 89355, 89354, 89350, 89311, 89309, 89307, 89270, 89268,
                89228, 89226, 89225, 89194, 89192, 89154, 89153, 89124, 89091, 89090, 89054,
                89052, 89013, 88968, 88856, 88855, 88827, 88826, 88825, 88824, 88797, 88796,
                88758, 88541, 88540, 88539, 88538, 88501, 88497, 88496, 88398, 88366, 88247,
                88245, 88244, 88212, 88210, 88180, 88179, 88178, 88118, 88117, 88116, 88076,
                88075, 88023, 88009, 87997, 87966, 87941, 87883, 87882, 87837, 87836, 87835,
                87834, 87790, 87789, 87788, 87787, 87786, 87748, 87746, 87708, 87681, 87680,
                87658, 87581, 87555, 87554, 87501, 87499, 87477, 87476, 87474, 87455, 87413,
                87389, 87385, 87362, 87360, 87323, 87284, 87278, 87248, 87247, 87189, 87174,
                87172, 87171, 87170, 87112, 87075, 87048, 87042, 86964, 86931, 86930, 86928,
                86927, 86925, 86890, 86852, 86850, 86847, 86819, 86818, 86794, 86793, 86768,
                86758, 86757, 86756, 86439, 86386, 86308, 86304, 86272, 86131, 85987, 85657,
                85494, 85425, 85422, 85377, 85376, 85375, 85374, 85372, 85371, 85327, 85325,
                85324, 85323, 85322, 85218, 85187, 85186, 85148, 85146, 85093, 85091, 85088,
                85049, 85004, 85003, 84959, 84912, 84859, 84858, 84855, 84826, 84791, 84790,
                84753, 84740, 84717, 84700, 84644, 84599, 84549, 84503, 84374, 84333, 84329,
                84328, 84327, 84287, 84206, 84109, 84107, 84106, 84103, 84059, 84058, 84017,
                84016, 83970, 83969, 83934, 83933, 83932, 83913, 83684, 83600, 83530, 83431,
                83333, 83242, 83194, 83176, 83150, 83144, 83141, 83084, 83048, 82987, 82986,
                82941, 82940, 82905, 82871, 82753, 82747, 82479, 82456, 82439, 82438, 82412,
                82384, 82360, 82358, 82335, 82305, 82279, 82237, 82235, 82186, 82168, 82155,
                82044, 82043, 81960, 81924, 81895, 81894, 81892, 81860, 81859, 81826, 81824,
                81793, 81757, 81690, 81660, 81644, 81642, 81629, 81627, 81626, 81588, 81587,
                81586, 81563, 81562, 81527, 81525, 81524, 81436, 81435, 81389, 81388, 81387,
                81386, 81385, 81382, 81316, 81314, 81311, 81308, 81307, 81305, 81303, 81263,
                81262, 81260, 81258, 81256, 81241, 81239, 81238, 81237, 81236, 81217, 81216,
                81214, 81198, 81196, 81195, 81194, 81193, 81192, 81191, 81190, 81189, 81166,
                81165, 81164, 81163, 81161, 81160, 81146, 81143, 81142, 81141, 81138, 81118,
                81117, 81116, 81115, 81114, 81113, 81101, 81098, 81079, 81069, 81066, 81064,
                81055, 81053, 81050, 81028, 81025, 81008, 81005, 80983, 80981, 80976, 80954,
                80953, 80952, 80901, 80882, 80880, 80879, 80853, 80823, 80822, 80801, 80798,
                80796, 80792, 80775, 80774, 80772, 80771, 80723, 80719, 80695, 80673, 80671,
                80670, 80581, 80580, 80579, 80578, 80576, 80570, 80529, 80528, 80526, 80524,
                80523, 80486, 80481, 80480, 80478, 80475, 80474, 80473, 80472, 80468, 80428,
                80426, 80415, 80409, 80408, 80369, 80355, 80354, 80352, 80350, 80348, 80317,
                80316, 80313, 80312, 80307, 80275, 80269, 80266, 80265, 80228, 80227, 80226,
                80219, 80185, 80184, 80183, 80178, 80145, 80138, 80134, 80131, 80097, 80095,
                80094, 80093, 80091, 80069, 80068, 80067, 80034, 80033, 80031, 80027, 80026,
                80021, 80019, 80017, 80015, 79984, 79980, 79955, 79921, 79918, 79916, 79896,
                79894, 79882, 79880, 79879, 79865, 79854, 79853, 79841, 79839, 79838, 79837,
                79816, 79803, 79801, 79731, 79730, 79716, 79713, 79712, 79691, 79619, 79616,
                79606, 79603, 79567, 79485, 79439, 79374, 79326, 79310, 79305, 79294, 79278,
                79260, 79204, 79188, 79118, 79083, 78999, 78997, 78956, 78937, 78934, 78896,
                78882, 78868, 78809, 78805, 78803, 78787, 78723, 78667, 78644, 78642, 78619,
                78560, 78557, 78538, 78470, 78466, 78402, 78249, 78118, 78111, 78098, 78097,
                78085, 78081, 78079, 78070, 78063, 78057, 78055, 78040, 78029, 78022, 78018,
                78004, 78002, 77978, 77977, 77976, 77974, 77954, 77953, 77952, 77950, 77933,
                77932, 77912, 77891, 77889, 77888, 77859, 77855, 77823, 77822, 77818, 77814,
                77813, 77798, 77797, 77789, 77780, 77779, 77771, 77762, 77757, 77741, 77722,
                77721, 77704, 77669, 77661, 77659, 77648, 77621, 77620, 77602, 77592, 77591,
                77580, 77571, 77562, 77537, 77526, 77515, 77513, 77496, 77494, 77483, 77474,
                77473, 77466, 77454, 77441, 77440, 77439, 77438, 77431, 77429, 77428, 77427,
                77426, 77406, 77397, 77387, 77369, 77368, 77364, 77363, 77362, 77358, 77355,
                77333, 77329, 77328, 77322, 77307, 77297, 77295, 77282, 77280, 77272, 77271,
                77260, 77259, 77256, 77241, 77240, 77239, 77226, 77209, 77200, 77199, 77190,
                77187, 77171, 77155, 77148, 77146, 77142, 77086, 77052, 77033, 76902, 76851,
                76591, 76525, 76497, 76435, 76433, 76375, 76341, 76306, 76304, 76303, 76289,
                76272, 76271, 76242, 48702, 48700, 48699, 48673, 48644, 48622, 48621, 48620,
                48617, 48597, 48580, 48579, 48577, 48510, 48501, 48500, 48432, 48431, 48397,
                48376, 48375, 48374, 48353, 48343, 48331, 48329, 48328, 48325, 48311, 48303,
                48288, 48255, 48229, 48225, 48224, 48202, 48184, 48176, 48164, 48157, 48151,
                48150, 48141, 48140, 48139, 48131, 48130, 48118, 48117, 48107, 48060, 48057,
                48042, 48035, 48023, 48022, 47974, 47965, 47943, 47928, 47915, 47873, 47841,
                47836, 47829, 47828, 47788, 47772, 47761, 47738, 47720, 47701, 47640, 47618,
                47601, 47539, 47531, 47523, 47500, 47376, 47353, 47231, 47141, 47112, 47110,
                47089, 46861, 46859, 46858, 46847, 46846, 46843, 46832, 46831, 46827, 46824,
                46816, 46814, 46806, 46803, 46798, 46791, 46786, 46785, 46777, 46773, 46752,
                46747, 46744, 46720, 46718, 46697, 46565, 46554, 46447, 46411, 46368, 46343,
                46248, 45731, 43911, 38246, 38087, 38078, 38066, 38038, 37989, 37986, 37984,
                37943, 37914, 37841, 37758, 37714, 37093, 36873, 36703, 36692, 36653, 36649,
                36610, 36603, 36591, 36583, 36561, 36189, 36136, 36128, 36106, 36096, 36072,
                36049, 35982, 35956, 35919, 35913, 35846, 35808, 35789, 35784, 35772, 35725,
                35708, 35649, 35642, 35639, 35626, 35625, 35606, 35605, 35602, 35590, 35586,
                35529, 35525, 35503, 35502, 35475, 35410, 35378, 35372, 35365, 35347, 35143,
                34903, 34900, 34860, 34859, 34834, 34788, 34719, 34715, 34695, 34670, 34646,
                34618, 34478, 34439, 34421, 34420, 34419, 34413, 34394, 34375, 34362, 34356,
                34307, 34295, 34268, 34260, 34258, 34256, 34253, 34252, 34251, 34250, 34249,
                34248, 34243, 34242, 34241, 34240, 34239, 34238, 34237, 34236, 34235, 34080,
                34043, 33904, 33391, 32598, 32586, 32584, 32583, 32575, 32573, 32568, 32562,
                32561, 32550, 32544, 32543, 32535, 32534, 32533, 32532, 32531, 32511, 32471,
                32466, 32465, 32464, 32449, 32444, 32434, 32432, 32429, 32420, 32417, 32403,
                32402, 32397, 32393, 32357, 32351, 32350, 32347, 32343, 32340, 32333, 32308,
                32299, 32296, 32294, 32283, 32281, 32279, 32278, 32277);
            $shipping_api_status = false;
            foreach($order_datas as $order_info){


                if(in_array(element('order_id',$order_info),$error_arr)){
                    echo'dd';
                    continue;
                }
                $sync_result	= $this->{$update_track_num_method}($order_info);
                if(!empty($sync_result)) {

                    if (element('result', $sync_result) == 'Fail') {

                        //송장번호가 이미 완료됐다고 하는 에러 메세지들은 걸러서 주문서 상태 변경하기 -KSJ 20191210
                        //1. 경매상태가 발송가능한 상태가 아닙니다.
                        //2. 기 배송중/배송완료 상태
                        //3.Specified argument was out of the range of valid values.
                        //4. 경매상태가 발송중입니다.

                        if(strpos(element('string', $sync_result), "경매상태가") !== false) $shipping_api_status = true;
                        if(strpos(element('string', $sync_result), "배송중/배송완료") !== false) $shipping_api_status = true;
                        if(strpos(element('string', $sync_result), "Specified argument") !== false) $shipping_api_status = true;

                        if($shipping_api_status) {
                            //에러메세지 따라서 주문서 상태(배송->완료) 하는 주문서들 history 쌓기 -KSJ 20191210
                            $this->order_model->addupdate_tracking_error_history(array(
                                'order_id' => element('order_id', $order_info),
                                'package_no' => element('package_no', $order_info),
                                'channel_id' => element('channel_id', $order_info),
                                'shipping_code' => element('shipping_code', $order_info),
                                'channel_order_no' => element('channel_order_no', $order_info),
                                'error_message' => element('string', $sync_result),
                                'create_date' => date('Y-m-d H:i:s')
                            ));
                            echo element('channel_order_no', $order_info) . "Shipping Code Upload Error MSG :: Completion :: " . element('string', $sync_result) . PHP_EOL;
                        }else{
                            $this->order_model->addupdate_tracking_number_error(array(
                                'order_id' => element('order_id', $order_info),
                                'error_message' => element('string', $sync_result),
                                'create_date' => date('Y-m-d H:i:s')
                            ));
                            echo element('channel_order_no', $order_info) . "Shipping Code Upload Error :: " . element('string', $sync_result) . PHP_EOL;

                            array_push($fail_order_ids, element('order_id', $order_info));
                            continue;
                        }
                    }
                    array_push($success_order_ids, element('order_id', $order_info));
                    if(!$shipping_api_status) echo element('channel_order_no', $order_info) . "Shipping Code Upload END :: " . element('rs_msg', $sync_result) . PHP_EOL;
                }else{
                    $this->order_model->addupdate_tracking_number_error(array(
                        'order_id' => element('order_id', $order_info),
                        'error_message' => "API 통신오류 array null",
                        'create_date' => date('Y-m-d H:i:s')
                    ));
                    echo element('channel_order_no', $order_info) . "Shipping Code Upload Error :: API null" . PHP_EOL;
                    array_push($fail_order_ids, element('order_id', $order_info));
                    continue;
                }
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

        /*include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(42);

        $rest = $history_api->sendHistoryID();*/
    }

    public function test()
    {

        $channel_arr	= $this->channel_info_model->getChannelInfos(array());
        // 채널별 분기
        foreach($channel_arr->result_array() as $channel_info) {
            $success_item_ids = array();
            $this->sales_channel_config = null;
//			if(element('channel_code',$channel_info) == 'A') continue;
            switch (element('channel_code', $channel_info)) {
                case 'G':
                    $this->load->config('api_config_gmarket', true);
                    $this->sales_channel_config = $this->config->item(element('account_id', $channel_info), 'api_config_gmarket');
                    $stockInItemSync_method = 'callAddPrice';

                    break;
                case 'A':
                    $this->load->config('api_config_auction', true);
                    $this->sales_channel_config = $this->config->item(element('account_id', $channel_info), 'api_config_auction');
                    $stockInItemSync_method = 'callReviseItemSelling';

                    break;
                default :

            }
            //$this->api_key	= element('api_key',$this->sales_channel_config);
            $this->api_key = $this->channel_info_model->getApikey(array("account_id" => element('account_id', $channel_info), "channel_code" => element('channel_code', $channel_info)));

            // 클리어런스 상품 정보
            $filter = array('channel_id' => element('channel_id', $channel_info), 'stock_status' => 'Y');
            if (element('account_type', $channel_info) == '1') {
                $filter['restrict_customer_clearance'] = '1';
            } else {
                $filter['restrict_customer_clearance_in'] = array('1', '2');
            }
            $item_result = $this->master_item_stock_model->getItemStockInfo($filter);

            foreach ($item_result->result_array() as $item_info) {
                echo element('channel_item_code', $item_info) . "<Br>";
            }
        }

    }
	
	private function updateTrackingNoToAuction($order_info)
	{
		require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
		include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
		//$this->api_key	= 'd310kxymI5jbPsYSxgyJ4M9BkjJbtr8HCRcsVRFRK34TOnBIhjWapNEP/kfX7fk0oL/mvCc2bBG9VItchZXNuX0nP5xx1c4/PDd+03Dp0b8+uZpHQPr/3hy4kSD3g4D+X4mYkO7BPw2VRvgXd966yJ44honypujpOuokhesVrSPGolEF5HAWQY4Jewkxlub9mdMEKSVqH4MNgvlAH3OXR+s=';
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
		//$this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
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


    /**
     * account 별 처리 로직 추가
     */
    function updateProductPrice($account_id){

        //cron chk
//        $cnt = $this->process_scheduling->getProcessSchedulingCount(array('full_query'=>"
//        (process_status ='1' and process_code = 'stockInItemSync' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -3 hour),'%Y%m%d%H'))
//        or
//        (process_status ='1' and process_code = 'updateProductPrice' and date_format(process_start_date,'%Y%m%d%H') > date_format(date_add(now(), interval -10 hour),'%Y%m%d%H'))
//        "));
//
//        if($cnt>0){
//            $process_scheduling_add_data =array(
//                'process_code' => 'updateProductPrice',
//                'process_status' => '3',
//                'process_start_date' => date ("Y-m-d H:i:s"),
//                'process_end_date' => date ("Y-m-d H:i:s")
//            );
//            $this->process_scheduling->addProcessScheduling($process_scheduling_add_data);
//            return;
//        }

        $process_scheduling_add_data =array(
            'process_code' => 'updateProductPrice',
            'process_status' => '1',
            'process_start_date' => date ("Y-m-d H:i:s")
        );
        $process_scheduling_inset_id=$this->process_scheduling->addProcessScheduling($process_scheduling_add_data);

        echo 'start';

        $update_data_result = $this->channel_item_info_model->getItemPriceUpdateHistoryInfos(
            array('upload_fg' => '1','update_join'=>'Y', 'a.account_id'=>$account_id)
            ,'a.item_history_id, a.worker_id, b.channel_id, b.api_key, b.channel_code, a.channel_item_code, a.upload_price, a.discount_price, a.discount_unit, c.item_info_id,stock_status');

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
            switch ($date_info['channel_code']){

                case 'G' : //fastople gmarket
                    $result = $this->sendPrice($channel_item_code,element('basic_price',$price_info), $date_info['api_key']);

                    if($discount_unit != 'N'){

                        $result  = $this->sendDiscountPrice($channel_item_code,$price_info, $date_info['api_key']);

                    }else{

                        $price_info = array(
                            'discount_type'=>'Rate',//임의의값
                            'discount_value'=>'0',
                            'basic_price'=>$upload_price
                        );

                        $result  = $this->sendDiscountPrice($channel_item_code,$price_info, $date_info['api_key']);

                    }

                    break;

                case 'A' : //fastople auction
                    $result = $this->callAuctionPriceUpdate($channel_item_code,$price_info, $date_info['api_key']);
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

            if($date_info['channel_code']=='G' && $date_info['stock_status']=='N'){
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

    private function sendPrice($channel_item_code, $price, $api_key)
    {
        include_once '/ssd/html/api_sdks/gmarket/autoload.php';
//        $this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
        $AddPrice	= new \sdk\controller\AddPrice();
        $AddPrice->setTicket($api_key);
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


    private function callReviseItemStockSingle($channel_item_code,$api_key,$stockNo)
    {
        require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
        include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';


        //단품 재고 조정 (옥션)
        $Quantity = "99999"; //수량
        $resetXml	= str_replace(array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__', '__QTY__', '__STOCK_NO__'), array($api_key, $channel_item_code, $Quantity, $stockNo)
            ,	file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItemStockSingle.xml')
        );
        $resetUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
        $resetaction	= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItemStock";

        return requestAuction($resetUrl, $resetaction, $resetXml);
    }

    //
    private function callViewItemStock($api_key,$channel_item_code)
    {
        require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
        include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';

        $viewXml	= str_replace(array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__'), array($api_key, $channel_item_code)
            ,	file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ViewItemSingleStock.xml')
        );
        $viewUrl	= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
        $viewAction	= "http://www.auction.co.kr/APIv1/ShoppingService/ViewItemStock";

        return requestAuction($viewUrl, $viewAction, $viewXml);
    }

    public function test_ksj(){
        $api_key = "d2TBfJkToBSGU9QEvy15Z5IDwRJHyLfbgZr9ImRrtXomdMSepC4YIi1PdEir7qRXNXg2sFf/sLygQnMmukHPtvbaHwTzvksWrjTAkc0b98FDu6VsPRbJoPI/J4xIE/0YxMlYHc4GYag1wucNrjgcMC4F0soT8VFEsJr1Glbtek+WtVYk6INyEUMsxrmKPCKh7gx3I1f2kWqj8A8Mb1wsa88=";
        $channel_item_code = "B261896348";

        // 옵션 스탁넘버 호출 및 처리
        $result_data = $this->callViewItemStock($api_key,$channel_item_code);
        $stockNoArr =  element("options",$result_data);
        $stockNo =  $stockNoArr[0]['auction_stock_no'];

       // if($stockNo=="") // API 실패시.....

        // 재고조정
        $result = $this->callReviseItemStockSingle($channel_item_code, $api_key, $stockNo);

        echo "<pre>";
        var_dump($result);
        echo "</pre>";
        if($result['result'] == "Success"){ //재고조정 성공시

        }

    }
}




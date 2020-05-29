<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-07
 * File: Oms_order.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Oms_developping extends CI_Controller
{
	private $sales_channel_config;
	private $api_key;
	
    public function __construct()
    {
        parent::__construct();
	
		$this->load->model('channel/channel_info_model');
		$this->load->model('item/channel_option_item_info_model');
		$this->load->model('item/soldout_option_history_model');
		$this->load->model('order/order_model');
		$this->load->model('stock/master_item_stock_model');
	
	}
	
	public function test_stock(){
		
//		echo $this->channel_option_item_info_model->getOldOptionCount(array('channel_item_code'=>'B539769824'));
		
//		$this->load->model('item/channel_option_item_info_model');
//		$addtional_item_info	= $this->channel_option_item_info_model->getChannelOptionItemInfo(array('channel_item_code'=>'B539769824', 'option_name'=>'23.스포츠 보틀 블랙 앤 화이트'));
//
//		var_dump($addtional_item_info);
//
	}
	public function test(){
        // 상태 변경 필요 옵션이 포함된 상번 리스트 로드
        $filter	= array(
            'need_update'	=> 'Y'
        ,	'channel_id'	=> 1
        ,	'regist_fg'		=> '3'
        ,	'select_escape'	=> false
        );
        $select	= 'DISTINCT i.channel_item_code';

        // 품절 품절해제 필요상품 데이터 로드
        $channel_item_result	= $this->channel_option_item_info_model->getChannelOptionItemInfos($filter,$select);
        $result_datas	= array();
        foreach($channel_item_result->result_array() as $channel_item) {
            $result_datas[element('channel_item_code', $channel_item)] = array(
                'item_info_ids' => array()
            , 'history_datas' => array()
            );

            $filter = array(
                'master_info' => '1'
            , 'regist_fg' => '2'
            , 'order_by' => 'item_info_id'
            , 'channel_item_code' => element('channel_item_code', $channel_item)
            );
            $select = 'i.*, d.master_item_id, m.currentqty';
            $option_result = $this->channel_option_item_info_model->getChannelOptionItemInfos($filter, $select);
            $selections = array();
            $additions = array();
            foreach ($option_result->result_array() as $option) {

                $option['virtual_item_id'] = "V" . str_pad($option['virtual_item_id'], 8, "0", STR_PAD_LEFT);
                if (element('additem_fg', $option) == 'Y') {
                    array_push($additions, $option);
                } else {
                    array_push($selections, $option);
                }
                if (element('need_update', $option) == 'Y') {
                    $result_datas[element('channel_item_code', $channel_item)]['item_info_ids'][] = element('item_info_id', $option);
                    $result_datas[element('channel_item_code', $channel_item)]['history_datas'][] = array(
                        'item_info_id' => element('item_info_id', $option)
                    , 'stock_status' => element('stock_qty', $option) > 0 ? 'Y' : 'N'
                    , 'currentqty' => element('currentqty', $option)
                    , 'soldout_process_type' => '1'
                    , 'process_worker_id' => '0'
                    , 'create_date' => date('Y-m-d H:i:s')
                    );
                }
            }
            echo "<pre>";
            var_dump($selections);
            echo "</pre>";
            echo "<pre>";
            var_dump($additions);
            echo "</pre>";
            echo element('channel_item_code', $channel_item) . PHP_EOL;

        }
    }

    public function optionItemStockSync()
	{
		if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;

		// 채널정보
		//$channel_arr	= $this->channel_info_model->getChannelInfos(array()); use_flag =0 인 채널까지 다 가져와서 옵션 품절 체크하기 20190328 KSJ
        $channel_arr	= $this->channel_info_model->getnewChannelInfos(array());
		// 채널별 분기
		foreach($channel_arr->result_array() as $channel_info) {
			$this->sales_channel_config = null;
			$success_channel_item_id	= array();
//			if(element('channel_code',$channel_info) == 'G') continue;
			switch (element('channel_code', $channel_info)) {
				case 'G':
					$this->load->config('api_config_gmarket', true);
					$this->sales_channel_config = $this->config->item(element('account_id', $channel_info), 'api_config_gmarket');

					$option_sync_method	= 'callAddItemOption';
					break;
				case 'A':
					$this->load->config('api_config_auction', true);
					$this->sales_channel_config = $this->config->item(element('account_id', $channel_info), 'api_config_auction');

					$option_sync_method	= 'auctionOptionStockSync';
					break;
				default :
			}
			
			//$this->api_key	= element('api_key',$this->sales_channel_config);
            $this->api_key = $this->channel_info_model->getApikey(array("account_id"=>element('account_id', $channel_info), "channel_code"=>element('channel_code', $channel_info)));

			// 상태 변경 필요 옵션이 포함된 상번 리스트 로드
			$filter	= array(
				'need_update'	=> 'Y'
			,	'channel_id'	=> element('channel_id', $channel_info)
			,	'regist_fg'		=> '2'
			,	'select_escape'	=> false
			);
			$select	= 'DISTINCT i.channel_item_code';
			
			// 품절 품절해제 필요상품 데이터 로드
			$channel_item_result	= $this->channel_option_item_info_model->getChannelOptionItemInfos($filter,$select);
			$result_datas	= array();
			foreach($channel_item_result->result_array() as $channel_item){
				$result_datas[element('channel_item_code', $channel_item)]	= array(
					'item_info_ids'	=> array()
				,	'history_datas'	=> array()
				);
				
				$filter	= array(
					'master_info'		=> '1'
				,	'regist_fg'			=> '2'
				,	'order_by'			=> 'item_info_id'
				,	'channel_item_code'	=> element('channel_item_code', $channel_item)
                    ,'group_by'=>'item_info_id'
				);
				$select	= 'i.*, d.master_item_id, m.currentqty';
				$option_result	= $this->channel_option_item_info_model->getChannelOptionItemInfos($filter,$select);
				$selections	= array();
				$additions	= array();
				foreach($option_result->result_array() as $option){
					$option['virtual_item_id']	= "V".str_pad($option['virtual_item_id'], 8, "0", STR_PAD_LEFT);
					if(element('additem_fg',$option) == 'Y'){
						array_push($additions, $option);
					}else{
						array_push($selections, $option);
					}
					if(element('need_update', $option) == 'Y'){
						$result_datas[element('channel_item_code', $channel_item)]['item_info_ids'][]	= element('item_info_id', $option);
						$result_datas[element('channel_item_code', $channel_item)]['history_datas'][]	= array(
								'item_info_id'			=> element('item_info_id', $option)
							,	'stock_status'			=> element('stock_qty', $option) > 0 ? 'Y' : 'N'
							,	'currentqty'			=> element('currentqty', $option)
							,	'soldout_process_type'	=> '1'
							,	'process_worker_id'		=> '0'
							,	'create_date'			=> date('Y-m-d H:i:s')
							);
					}
				}
				echo element('channel_item_code', $channel_item).PHP_EOL;

				$sync_result	= $this->{$option_sync_method}(element('channel_item_code', $channel_item), $selections, $additions);

				if(element('result', $sync_result) == 'Fail'){

                    if(element('rs_msg',$sync_result,false)=='Temporarlly Down for Maintenance.'){
                        break;
                    }

                    $result_datas_error =array();
                    $result_datas_error_update =array();
                    $int = 1;
                    foreach (element('history_datas', element(element('channel_item_code', $channel_item), $result_datas)) as $value){
                        $result_datas_error[$int]= $value;
                        $result_datas_error_update[$int] = element('item_info_id', $value);
                        $result_datas_error[$int++]['error_message']=element('rs_msg', $sync_result);


                    }

                    $this->channel_option_item_info_model->updateChannelOptionItemInfo(array('need_update'	=> 'E'),array('item_info_id_in'=>$result_datas_error_update));
                    $this->soldout_option_history_model->addSoldoutOptionHistoryBulkError($result_datas_error);
					echo element('rs_msg', $sync_result);
					continue;
				}

                $this->channel_option_item_info_model->updateChannelOptionItemInfo(
                    array(
                        'update_date'	=> date('Y-m-d H:i:s')
                    ,	'need_update'	=> 'N'
                    ),
                    array('channel_item_code' => element('channel_item_code', $channel_item)
                    ,'need_update'=>'E'
                    )
                );

				$this->channel_option_item_info_model->updateChannelOptionItemInfo(
					array(
						'update_date'	=> date('Y-m-d H:i:s')
					,	'need_update'	=> 'N'
					),
					array('item_info_id_in' => element('item_info_ids', element(element('channel_item_code', $channel_item), $result_datas)))
				);
				$this->soldout_option_history_model->addSoldoutOptionHistoryBulk(element('history_datas', element(element('channel_item_code', $channel_item), $result_datas)));
				
			}
			
			// 통관이슈상품 품절처리
			$clearance_items	= array();
			$clearance_filter	= array('channel_id'=>element('channel_id',$channel_info), 'stock_status'=>'Y', 'regist_fg'=>'2');

			if(element('account_type',$channel_info) == '1'){
				$clearance_filter['restrict_customer_clearance']	= '1';
			}else{
				$clearance_filter['restrict_customer_clearance_in']	= array('1','2');
			}

			$clearance_result	= $this->master_item_stock_model->getOptionItemStockInfo($clearance_filter);
			foreach($clearance_result->result_array() as $item){
				$channel_item_code	= element('channel_item_code',$item);
				
				if(element($channel_item_code,$clearance_items,false) === false){
					$clearance_items[$channel_item_code]	= array();
				}
				if(!in_array(element('item_info_id',$item), element($channel_item_code, $clearance_items))){
					$clearance_items[$channel_item_code][]	= element('item_info_id',$item);
				}
			}
			
			foreach($clearance_items as $channel_item_id => $item_info_ids){

				$clearance_result_datas[$channel_item_id]	= array(
					'item_info_ids'	=> array()
				,	'history_datas'	=> array()
				);
				$filter	= array(
					'master_info'		=> '1'
				,	'regist_fg'			=> '2'
				,	'order_by'			=> 'item_info_id'
				,	'channel_item_code'	=> $channel_item_id
				);
				$select	= 'i.*, d.master_item_id, m.currentqty';
				$option_result	= $this->channel_option_item_info_model->getChannelOptionItemInfos($filter,$select);
				$selections	= array();
				$additions	= array();

				foreach($option_result->result_array() as $option){
					$option['virtual_item_id']	= "V".str_pad($option['virtual_item_id'], 8, "0", STR_PAD_LEFT);

					if(in_array(element('item_info_id', $option),$item_info_ids)){
						$clearance_result_datas[$channel_item_id]['item_info_ids'][]	= element('item_info_id', $option);
						$clearance_result_datas[$channel_item_id]['history_datas'][]	= array(
							'item_info_id'			=> element('item_info_id', $option)
						,	'stock_status'			=> 'N'
						,	'currentqty'			=> element('currentqty', $option)
						,	'soldout_process_type'	=> '2'
						,	'process_worker_id'		=> '0'
						,	'create_date'			=> date('Y-m-d H:i:s')
						);

						$option['stock_qty']	= '0';
					}

					if(element('additem_fg',$option) == 'Y'){
						array_push($additions, $option);
					}else{
						array_push($selections, $option);
					}
				}
				echo $channel_item_id.PHP_EOL;

				$sync_result	= $this->{$option_sync_method}($channel_item_id, $selections, $additions);

				if(element('result', $sync_result) == 'Fail'){

                    if(element('rs_msg',$sync_result,false)=='Temporarlly Down for Maintenance.'){
                        break;
                    }

                    $result_datas_error =array();
                    $result_datas_error_update = array();
                    $int = 1;
                    foreach (element('history_datas', element($channel_item_id, $clearance_result_datas)) as $value){
                        $result_datas_error[$int]= $value;
                        $result_datas_error_update[$int] = element('item_info_id', $value);
                        $result_datas_error[$int++]['error_message']=element('rs_msg', $sync_result);

                    }

                    $this->channel_option_item_info_model->updateChannelOptionItemInfo(array('need_update'	=> 'E'),array('item_info_id_in'=>$result_datas_error_update));
                    $this->soldout_option_history_model->addSoldoutOptionHistoryBulkError($result_datas_error);
					echo element('rs_msg', $sync_result);
					continue;
				}

                $this->channel_option_item_info_model->updateChannelOptionItemInfo(
                    array(
                        'update_date'	=> date('Y-m-d H:i:s')
                    ,	'need_update'	=> 'N'
                    ),
                    array('channel_item_code' => element('channel_item_code', $channel_item)
                    ,'need_update'=>'E'
                    )
                );

				$this->channel_option_item_info_model->updateChannelOptionItemInfo(
					array(
						'update_date'	=> date('Y-m-d H:i:s')
					,	'need_update'	=> 'N'
					,	'stock_qty'		=> '0'
					),
					array('item_info_id_in' => element('item_info_ids', element($channel_item_id, $clearance_result_datas)))
				);
				$this->soldout_option_history_model->addSoldoutOptionHistoryBulk(element('history_datas', element($channel_item_id, $clearance_result_datas)));

			}
			echo "Channel End";
		}
		echo "Process End";

        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(34);

        $rest = $history_api->sendHistoryID();

	}
	
	private function callAddItemOption($channel_item_code, $selections, $additions)
	{
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
		$AddItemOption = new \sdk\controller\AddItemOption();
		$AddItemOption->setTicket($this->api_key);
		$AddItemOption->setGmktItemNo($channel_item_code);
		$AddItemOption->setOptionBlock($selections);
		$AddItemOption->setAdditionBlock($additions);
		
//		print_r($result =  $AddItemOption->getRequestBody());
		$result =  $AddItemOption->getResponse();
		
		if(element('Result', $result) !== 'Success'){
			return array(
				'result'	=> 'Fail'
			,	'rs_msg'	=> element('Comment',$result)
			);
		}else{
			return array(
				'result'	=> 'Success'
			,	'rs_msg'	=> element('Comment',$result)
			);
		}
	}
	
	private function auctionOptionStockSync($channel_item_code, $selections, $additions)
	{
		require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
		include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
		
		$objclassnames			= array();
		$objclassname			= '';
		$option_reset_fg		= false;
		$item_selection_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItemStockSelections.xml');
		$selection_block		= '';
		foreach($selections as $selection_item){
			if(!in_array(element('section',$selection_item), $objclassnames)) array_push($objclassnames,element('section',$selection_item));
			$stock	= element('stock_qty',$selection_item) > 0 ? 'false':'true';
			
			if(element('auction_stock_no',$selection_item,null) === null){
				$selection_stock_no	= 'ChangeType="Add"';
				$option_reset_fg	= true;
			}else{
				$selection_stock_no	= 'ItemStockStandAloneNo="'.element('auction_stock_no',$selection_item).'" ChangeType="Update"';
			}
			
			$selection_block	.= str_replace(
				array('__OPTION_STOCK_NO__','__SECTION_NAME__', '__SECTION_VALUE__', '__VIRTUAL_CODE__', '__OPTION_PRICE__', '__OPTION_STOCK_FLAG__')
				,	array($selection_stock_no, element('section',$selection_item), element('option_name',$selection_item), element('virtual_item_id',$selection_item), element('price',$selection_item), $stock)
				,	$item_selection_dummy
			);
		}
		for($n=0; $n < count($objclassnames); $n++){
			$k	= $n+1;
			$objclassname	.= 'ClaseName'.$k.'="'.$objclassnames[$n].'" ';
		}
		$addition_block	= '';
		$addition_type	= 'NotAvailable';
		if(count($additions) > 0){
			$item_addition_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItemStockAdditions.xml');
			$addition_type			= 'AvailableLimitedStock';
			foreach($additions as $addition_item){
				if(element('auction_stock_no',$addition_item,null) === null){
					$addition_stock_no	= 'ChangeType="Add"';
					$option_reset_fg	= true;
				}else{
					$addition_stock_no	= 'StockNo="'.element('auction_stock_no',$addition_item).'" ChangeType="Update"';
				}
				$addition_block	.= str_replace(
					array('__OPTION_STOCK_NO__','__SECTION_NAME__', '__SECTION_VALUE__', '__VIRTUAL_CODE__', '__OPTION_PRICE__', '__OPTION_STOCK_FLAG__')
					,	array($addition_stock_no, element('section',$addition_item), element('option_name',$addition_item), element('virtual_item_id',$addition_item), element('price',$addition_item), element('stock_qty',$addition_item))
					,	$item_addition_dummy
				);
			}
		}

//		if($option_reset_fg	=== true){
//			if($this->channel_option_item_info_model->getOptionItemCount(array('channel_item_code'=>$channel_item_code, 'regist_fg'=>'3')) > 0 ){
//				$reset_result	= $this->callAuctionOptionReset($channel_item_code);
////				if(element('result',$reset_result) == 'Fail') return $reset_result;
//			}
//		}
		
		$additemoption_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItemStock.xml');
		$requestXmlBody	= str_replace(
			array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__', '__ADDITION_OPTION_TYPE__', '__CLASS_NAMES__', '__OPTION_SELECTION_BLOCK__', '__OPTION_ADDITION_BLOCK__')
			,	array($this->api_key, $channel_item_code, $addition_type, $objclassname, $selection_block, $addition_block)
			,	$additemoption_dummy
		);
//		print_r($requestXmlBody).PHP_EOL;
		
		$serverUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
		$action			= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItemStock";

		return requestAuction($serverUrl, $action, $requestXmlBody);
	}
	
	private function callAuctionOptionReset($channel_item_code)
	{
		$resetXml	= str_replace(array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__'), array($this->api_key, $channel_item_code)
			,	file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItemStockReset.xml')
		);
		$resetUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
		$resetaction	= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItemStock";
		
		return requestAuction($resetUrl, $resetaction, $resetXml);
	}
	
//	public function testPrice()
//	{
//		$this->load->model('item/channel_item_info_model');
//
//		$temp_arr	= array(
//			3330	=>	'1300',	3104	=>	'1300',	3329	=>	'1300',	464	=>	'1800',	463	=>	'1800',	3430	=>	'2200',	3429	=>	'2200',	3426	=>	'2200',	3425	=>	'2200',	2714	=>	'2300',	2711	=>	'2300',	831	=>	'2400',	829	=>	'2400',	808	=>	'2400',	806	=>	'2400',	805	=>	'2400',	810	=>	'2400',	839	=>	'2400',	827	=>	'2400',	816	=>	'2400'
//		,	814	=>	'2400',	3428	=>	'2400',	3427	=>	'2400',	844	=>	'2400',	825	=>	'2400',	824	=>	'2400',	822	=>	'2400',	820	=>	'2400',	3328	=>	'2500',	3106	=>	'2500',	2716	=>	'2500',	31		=>	'2500',	29		=>	'2500',	3421	=>	'2500',	322	=>	'2600',	321	=>	'2600',	319	=>	'2600',	318	=>	'2600',	229	=>	'2600',	1		=>	'2800',	2593	=>	'2800',	422	=>	'3000'
//		,	421	=>	'3000',	873	=>	'3000',	2605	=>	'3100',	3319	=>	'3100',	1764	=>	'3100',	327	=>	'3100',	51		=>	'3100',	50		=>	'3100',	46		=>	'3100',	43		=>	'3100',	543	=>	'3100',	547	=>	'3100',	549	=>	'3100',	763	=>	'3100',	2379	=>	'3100',	2741	=>	'3100',	3123	=>	'3100',	1900	=>	'3300',	461	=>	'3300',	2061	=>	'3300',	413	=>	'3300',	851	=>	'3300'
//		,	849	=>	'3300',	1997	=>	'3300',	862	=>	'3300',	907	=>	'3300',	1068	=>	'3300',	1918	=>	'3300',	2416	=>	'3300',	2462	=>	'3300',	4058	=>	'3300',	1929	=>	'3400',	3351	=>	'3400',	1732	=>	'3400',	3364	=>	'3400',	3353	=>	'3400',	3350	=>	'3400',	147	=>	'3400',	996	=>	'3400',	1227	=>	'3400',	1540	=>	'3400',	2371	=>	'3400',	3169	=>	'3400',	3765	=>	'3400'
//		,	3766	=>	'3400',	3767	=>	'3400',	458	=>	'3500',	144	=>	'3500',	854	=>	'3500',	3334	=>	'3500',	2547	=>	'3500',	1069	=>	'3500',	1731	=>	'3500',	3560	=>	'3500',	3864	=>	'3500',	846	=>	'3600',	9		=>	'3600',	847	=>	'3600',	841	=>	'3600',	867	=>	'3600',	513	=>	'3600',	1072	=>	'3600',	1909	=>	'3600',	1914	=>	'3600',	1916	=>	'3600',	3408	=>	'3700'
//		,	1475	=>	'3700',	2616	=>	'3700',	3415	=>	'3700',	3414	=>	'3700',	3412	=>	'3700',	3411	=>	'3700',	3410	=>	'3700',	3409	=>	'3700',	2750	=>	'3700',	3227	=>	'3700',	3228	=>	'3700',	3230	=>	'3700',	3231	=>	'3700',	3234	=>	'3700',	3236	=>	'3700',	4043	=>	'3700',	4298	=>	'3700',	15		=>	'3800',	2959	=>	'3800',	856	=>	'3800',	16		=>	'3800',	14		=>	'3800'
//		,	518	=>	'3800',	1460	=>	'3800',	1920	=>	'3800',	2898	=>	'3800',	4254	=>	'3800',	1130	=>	'3900',	1684	=>	'3900',	3378	=>	'3900',	2505	=>	'3900',	1158	=>	'3900',	995	=>	'3900',	3322	=>	'3900',	1225	=>	'3900',	2685	=>	'3900',	3127	=>	'3900',	3188	=>	'3900',	3613	=>	'3900',	3614	=>	'3900',	4014	=>	'3900',	812	=>	'4000',	328	=>	'4000',	3383	=>	'4000'
//		,	3382	=>	'4000',	3380	=>	'4000',	3379	=>	'4000',	833	=>	'4000',	1885	=>	'4000',	1902	=>	'4000',	3189	=>	'4000',	3191	=>	'4000',	3195	=>	'4000',	3196	=>	'4000',	3863	=>	'4000',	2021	=>	'4100',	1675	=>	'4100',	1106	=>	'4100',	838	=>	'4100',	924	=>	'4100',	835	=>	'4100',	1904	=>	'4100',	1906	=>	'4100',	1950	=>	'4100',	2474	=>	'4100',	3703	=>	'4100'
//		,	4054	=>	'4100',	3388	=>	'4200',	2869	=>	'4200',	1802	=>	'4200',	1798	=>	'4200',	2395	=>	'4200',	2830	=>	'4200',	3204	=>	'4200',	4067	=>	'4200',	3394	=>	'4300',	3400	=>	'4300',	3398	=>	'4300',	3396	=>	'4300',	3395	=>	'4300',	3393	=>	'4300',	790	=>	'4300',	3210	=>	'4300',	3212	=>	'4300',	3213	=>	'4300',	3215	=>	'4300',	3218	=>	'4300',	3221	=>	'4300'
//		,	3814	=>	'4300',	1625	=>	'4400',	1953	=>	'4400',	2701	=>	'4400',	332	=>	'4400',	2941	=>	'4400',	316	=>	'4400',	752	=>	'4400',	2331	=>	'4400',	2756	=>	'4400',	2881	=>	'4400',	3790	=>	'4400',	4068	=>	'4400',	2214	=>	'4500',	2135	=>	'4500',	1862	=>	'4500',	3385	=>	'4500',	3384	=>	'4500',	3381	=>	'4500',	2442	=>	'4500',	306	=>	'4500',	302	=>	'4500'
//		,	741	=>	'4500',	1643	=>	'4500',	2410	=>	'4500',	2526	=>	'4500',	2565	=>	'4500',	3193	=>	'4500',	3198	=>	'4500',	3199	=>	'4500',	4113	=>	'4500',	1614	=>	'4700',	324	=>	'4700',	320	=>	'4700',	758	=>	'4700',	2326	=>	'4700',	4079	=>	'4700',	222	=>	'4800',	228	=>	'4800',	796	=>	'4800',	1151	=>	'4800',	1142	=>	'4800',	226	=>	'4800',	225	=>	'4800'
//		,	219	=>	'4800',	1957	=>	'4800',	1574	=>	'4800',	1578	=>	'4800',	1581	=>	'4800',	2063	=>	'4800',	3594	=>	'4800',	3773	=>	'4800',	3775	=>	'4800',	3813	=>	'4800',	4049	=>	'4800',	2110	=>	'4900',	1631	=>	'4900',	1243	=>	'4900',	1152	=>	'4900',	423	=>	'4900',	3392	=>	'4900',	314	=>	'4900',	747	=>	'4900',	2334	=>	'4900',	2516	=>	'4900',	3571	=>	'4900'
//		,	3573	=>	'4900',	3697	=>	'4900',	3714	=>	'4900',	1982	=>	'5000',	3327	=>	'5000',	1846	=>	'5000',	939	=>	'5000',	91		=>	'5000',	34		=>	'5000',	317	=>	'5000',	3399	=>	'5000',	3397	=>	'5000',	3413	=>	'5000',	17		=>	'5000',	3326	=>	'5000',	3325	=>	'5000',	3324	=>	'5000',	753	=>	'5000',	1461	=>	'5000',	1474	=>	'5000',	1500	=>	'5000',	2451	=>	'5000'
//		,	3130	=>	'5000',	3132	=>	'5000',	3216	=>	'5000',	3219	=>	'5000',	3233	=>	'5000',	3818	=>	'5000',	3819	=>	'5000',	4071	=>	'5000',	4082	=>	'5000',	53		=>	'5100',	300	=>	'5100',	1640	=>	'5100',	3508	=>	'5100',	3359	=>	'5200',	1270	=>	'5200',	2059	=>	'5200',	1955	=>	'5200',	1156	=>	'5200',	3377	=>	'5200',	3354	=>	'5200',	3352	=>	'5200',	3186	=>	'5200'
//		,	3704	=>	'5200',	3712	=>	'5200',	3769	=>	'5200',	3770	=>	'5200',	3771	=>	'5200',	4011	=>	'5200',	4017	=>	'5200',	469	=>	'5300',	2088	=>	'5300',	1507	=>	'5300',	2043	=>	'5300',	1823	=>	'5300',	3367	=>	'5300',	912	=>	'5300',	917	=>	'5300',	1941	=>	'5300',	2402	=>	'5300',	2484	=>	'5300',	3174	=>	'5300',	3991	=>	'5300',	3998	=>	'5300',	224	=>	'5400'
//		,	218	=>	'5400',	216	=>	'5400',	54		=>	'5400',	2403	=>	'5400',	223	=>	'5400',	217	=>	'5400',	1663	=>	'5400',	1110	=>	'5400',	910	=>	'5400',	221	=>	'5400',	220	=>	'5400',	3		=>	'5400',	675	=>	'5400',	677	=>	'5400',	679	=>	'5400',	1480	=>	'5400',	1572	=>	'5400',	1576	=>	'5400',	1939	=>	'5400',	2041	=>	'5400',	2348	=>	'5400',	2642	=>	'5400'
//		,	3774	=>	'5400',	3776	=>	'5400',	4085	=>	'5400',	468	=>	'5500',	59		=>	'5500',	84		=>	'5500',	915	=>	'5500',	2682	=>	'5500',	3755	=>	'5500',	3833	=>	'5500',	1766	=>	'5600',	2215	=>	'5600',	2706	=>	'5600',	2704	=>	'5600',	2161	=>	'5600',	2105	=>	'5600',	2033	=>	'5600',	1881	=>	'5600',	725	=>	'5600',	36		=>	'5600',	921	=>	'5600',	2093	=>	'5600'
//		,	991	=>	'5600',	986	=>	'5600',	3390	=>	'5600',	3323	=>	'5600',	2866	=>	'5600',	2815	=>	'5600',	2746	=>	'5600',	323	=>	'5600',	146	=>	'5600',	99		=>	'5600',	98		=>	'5600',	49		=>	'5600',	44		=>	'5600',	529	=>	'5600',	541	=>	'5600',	545	=>	'5600',	627	=>	'5600',	760	=>	'5600',	1050	=>	'5600',	1222	=>	'5600',	1506	=>	'5600',	1508	=>	'5600'
//		,	1949	=>	'5600',	1994	=>	'5600',	2479	=>	'5600',	2539	=>	'5600',	2566	=>	'5600',	2757	=>	'5600',	2759	=>	'5600',	2783	=>	'5600',	2811	=>	'5600',	2829	=>	'5600',	3207	=>	'5600',	3501	=>	'5600',	3820	=>	'5600',	4021	=>	'5600',	4046	=>	'5600',	4072	=>	'5600',	37		=>	'5700',	65		=>	'5700',	57		=>	'5700',	1214	=>	'5700',	19		=>	'5700',	916	=>	'5700'
//		,	68		=>	'5700',	67		=>	'5700',	66		=>	'5700',	63		=>	'5700',	1609	=>	'5700',	530	=>	'5700',	1462	=>	'5700',	1944	=>	'5700',	2324	=>	'5700',	3612	=>	'5700',	3825	=>	'5700',	3826	=>	'5700',	3827	=>	'5700',	3828	=>	'5700',	3829	=>	'5700',	3832	=>	'5700',	1771	=>	'5800',	466	=>	'5800',	1433	=>	'5800',	1735	=>	'5800',	2257	=>	'5800',	2381	=>	'5800'
//		,	2345	=>	'5900',	1472	=>	'5900',	901	=>	'5900',	899	=>	'5900',	896	=>	'5900',	1928	=>	'5900',	1931	=>	'5900',	1933	=>	'5900',	2622	=>	'5900',	3990	=>	'5900',	2289	=>	'6000',	2022	=>	'6000',	1749	=>	'6000',	3416	=>	'6000',	1713	=>	'6000',	41		=>	'6000',	90		=>	'6000',	92		=>	'6000',	1476	=>	'6000',	1499	=>	'6000',	1502	=>	'6000',	3237	=>	'6000'
//		,	3463	=>	'6000',	3886	=>	'6000',	4050	=>	'6000',	4073	=>	'6000',	774	=>	'6100',	1518	=>	'6100',	2368	=>	'6100',	2011	=>	'6100',	2139	=>	'6100',	3333	=>	'6100',	148	=>	'6100',	629	=>	'6100',	1058	=>	'6100',	2293	=>	'6100',	2630	=>	'6100',	3138	=>	'6100',	4013	=>	'6100',	4064	=>	'6100',	1903	=>	'6200',	58		=>	'6200',	2494	=>	'6200',	987	=>	'6200'
//		,	60		=>	'6200',	2774	=>	'6200',	1515	=>	'6200',	1484	=>	'6200',	2680	=>	'6200',	2794	=>	'6200',	3492	=>	'6200',	3834	=>	'6200',	4019	=>	'6200',	4182	=>	'6200',	157	=>	'6300',	1556	=>	'6300',	2496	=>	'6300',	2921	=>	'6300',	2920	=>	'6300',	2917	=>	'6300',	2308	=>	'6300',	2681	=>	'6300',	2865	=>	'6300',	2867	=>	'6300',	2868	=>	'6300',	3526	=>	'6300'
//		,	2956	=>	'6400',	1303	=>	'6400',	1694	=>	'6400',	2958	=>	'6400',	2954	=>	'6400',	2823	=>	'6400',	2810	=>	'6400',	2808	=>	'6400',	303	=>	'6400',	8		=>	'6400',	2418	=>	'6400',	2922	=>	'6400',	1644	=>	'6400',	2357	=>	'6400',	2870	=>	'6400',	2891	=>	'6400',	2894	=>	'6400',	2897	=>	'6400',	3603	=>	'6400',	4074	=>	'6400',	4084	=>	'6400',	4280	=>	'6400'
//		,	4282	=>	'6400',	4286	=>	'6400',	1577	=>	'6500',	2431	=>	'6500',	2346	=>	'6500',	315	=>	'6500',	2164	=>	'6500',	903	=>	'6500',	898	=>	'6500',	894	=>	'6500',	2916	=>	'6500',	2914	=>	'6500',	662	=>	'6500',	89		=>	'6500',	749	=>	'6500',	1093	=>	'6500',	1497	=>	'6500',	1927	=>	'6500',	1930	=>	'6500',	2652	=>	'6500',	2862	=>	'6500',	2863	=>	'6500'
//		,	3996	=>	'6500',	4012	=>	'6500',	4030	=>	'6500',	4242	=>	'6500',	1450	=>	'6600',	1742	=>	'6600',	1575	=>	'6600',	1117	=>	'6600',	993	=>	'6600',	88		=>	'6600',	149	=>	'6600',	95		=>	'6600',	576	=>	'6600',	1542	=>	'6600',	1998	=>	'6600',	3462	=>	'6600',	3633	=>	'6600',	3753	=>	'6600',	4057	=>	'6600',	4061	=>	'6600',	1245	=>	'6700',	1208	=>	'6700'
//		,	1126	=>	'6700',	936	=>	'6700',	2454	=>	'6700',	1290	=>	'6700',	2123	=>	'6700',	2664	=>	'6700',	3601	=>	'6700',	3629	=>	'6700',	3713	=>	'6700',	4080	=>	'6700',	2055	=>	'6800',	2062	=>	'6800',	1521	=>	'6800',	1240	=>	'6800',	2350	=>	'6800',	2024	=>	'6800',	1284	=>	'6800',	2148	=>	'6800',	2493	=>	'6800',	2495	=>	'6800',	2624	=>	'6800',	3694	=>	'6800'
//		,	4051	=>	'6800',	4060	=>	'6800',	2424	=>	'6900',	926	=>	'6900',	1113	=>	'6900',	3224	=>	'6900',	3422	=>	'6900',	2864	=>	'6900',	2860	=>	'6900',	2853	=>	'6900',	2843	=>	'6900',	2800	=>	'6900',	2766	=>	'6900',	2763	=>	'6900',	2748	=>	'6900',	931	=>	'6900',	928	=>	'6900',	145	=>	'6900',	2501	=>	'6900',	625	=>	'6900',	959	=>	'6900',	1952	=>	'6900'
//		,	2646	=>	'6900',	2683	=>	'6900',	2804	=>	'6900',	3013	=>	'6900',	3687	=>	'6900',	4255	=>	'6900',	4257	=>	'6900',	4270	=>	'6900',	4271	=>	'6900',	4274	=>	'6900',	4278	=>	'6900',	4285	=>	'6900',	4287	=>	'6900',	4289	=>	'6900',	1099	=>	'7000',	591	=>	'7000',	3181	=>	'7000',	1247	=>	'7000',	471	=>	'7000',	470	=>	'7000',	905	=>	'7000',	271	=>	'7000'
//		,	1002	=>	'7000',	1615	=>	'7000',	1737	=>	'7000',	1738	=>	'7000',	1935	=>	'7000',	2125	=>	'7000',	2989	=>	'7000',	659	=>	'7100',	2237	=>	'7100',	1700	=>	'7100',	1067	=>	'7100',	1318	=>	'7100',	1309	=>	'7100',	1140	=>	'7100',	1132	=>	'7100',	1119	=>	'7100',	1104	=>	'7100',	1017	=>	'7100',	1486	=>	'7100',	2242	=>	'7100',	1219	=>	'7100',	1260	=>	'7100'
//		,	1145	=>	'7100',	1822	=>	'7100',	2020	=>	'7100',	2109	=>	'7100',	2360	=>	'7100',	2578	=>	'7100',	2580	=>	'7100',	3592	=>	'7100',	3610	=>	'7100',	3689	=>	'7100',	3692	=>	'7100',	3698	=>	'7100',	3699	=>	'7100',	3708	=>	'7100',	3710	=>	'7100',	3711	=>	'7100',	3869	=>	'7100',	4250	=>	'7100',	2042	=>	'7200',	2095	=>	'7200',	1895	=>	'7200',	1169	=>	'7200'
//		,	2		=>	'7200',	3404	=>	'7200',	3403	=>	'7200',	1456	=>	'7200',	2076	=>	'7200',	2414	=>	'7200',	2508	=>	'7200',	3223	=>	'7200',	3840	=>	'7200',	4028	=>	'7200',	1323	=>	'7300',	347	=>	'7300',	2312	=>	'7300',	3387	=>	'7300',	2543	=>	'7300',	1248	=>	'7300',	786	=>	'7300',	2610	=>	'7300',	2713	=>	'7300',	3202	=>	'7300',	3700	=>	'7300',	1206	=>	'7400'
//		,	438	=>	'7400',	435	=>	'7400',	425	=>	'7400',	87		=>	'7400',	447	=>	'7400',	443	=>	'7400',	440	=>	'7400',	439	=>	'7400',	426	=>	'7400',	424	=>	'7400',	2851	=>	'7400',	2805	=>	'7400',	2797	=>	'7400',	875	=>	'7400',	883	=>	'7400',	889	=>	'7400',	1705	=>	'7400',	1707	=>	'7400',	1717	=>	'7400',	1722	=>	'7400',	1723	=>	'7400',	1725	=>	'7400'
//		,	2101	=>	'7400',	2807	=>	'7400',	2824	=>	'7400',	3754	=>	'7400',	4279	=>	'7400',	1314	=>	'7500',	1915	=>	'7500',	925	=>	'7500',	61		=>	'7500',	56		=>	'7500',	1611	=>	'7500',	64		=>	'7500',	62		=>	'7500',	47		=>	'7500',	589	=>	'7500',	45		=>	'7500',	3176	=>	'7500',	3343	=>	'7500',	211	=>	'7500',	553	=>	'7500',	557	=>	'7500',	1477	=>	'7500'
//		,	1479	=>	'7500',	1569	=>	'7500',	1795	=>	'7500',	2175	=>	'7500',	2325	=>	'7500',	2420	=>	'7500',	2988	=>	'7500',	3146	=>	'7500',	3830	=>	'7500',	3831	=>	'7500',	4256	=>	'7500',	1923	=>	'7600',	1161	=>	'7600',	558	=>	'7600',	2380	=>	'7600',	3375	=>	'7600',	85		=>	'7600',	977	=>	'7600',	1493	=>	'7600',	3185	=>	'7600',	3617	=>	'7600',	4023	=>	'7600'
//		,	4036	=>	'7600',	2216	=>	'7700',	125	=>	'7700',	121	=>	'7700',	120	=>	'7700',	434	=>	'7700',	431	=>	'7700',	2433	=>	'7700',	465	=>	'7700',	126	=>	'7700',	122	=>	'7700',	119	=>	'7700',	118	=>	'7700',	448	=>	'7700',	444	=>	'7700',	442	=>	'7700',	436	=>	'7700',	432	=>	'7700',	430	=>	'7700',	428	=>	'7700',	427	=>	'7700',	293	=>	'7700'
//		,	602	=>	'7700',	604	=>	'7700',	606	=>	'7700',	609	=>	'7700',	613	=>	'7700',	616	=>	'7700',	877	=>	'7700',	879	=>	'7700',	887	=>	'7700',	891	=>	'7700',	897	=>	'7700',	913	=>	'7700',	1633	=>	'7700',	1708	=>	'7700',	1711	=>	'7700',	1712	=>	'7700',	1716	=>	'7700',	1719	=>	'7700',	2654	=>	'7700',	4024	=>	'7700',	4259	=>	'7700',	3294	=>	'7800'
//		,	1512	=>	'7800',	3111	=>	'7800',	2156	=>	'7800',	2478	=>	'7800',	2475	=>	'7800',	2473	=>	'7800',	2470	=>	'7800',	2670	=>	'7800',	2672	=>	'7800',	2673	=>	'7800',	2674	=>	'7800',	2934	=>	'7800',	3093	=>	'7800',	3906	=>	'7800',	3948	=>	'7800',	1954	=>	'7900',	1218	=>	'7900',	2492	=>	'7900',	914	=>	'7900',	1942	=>	'7900',	3709	=>	'7900',	4044	=>	'7900'
//		,	4246	=>	'7900',	2955	=>	'8000',	1207	=>	'8000',	2434	=>	'8000',	13		=>	'8000',	2127	=>	'8000',	989	=>	'8000',	12		=>	'8000',	515	=>	'8000',	1996	=>	'8000',	2892	=>	'8000',	3498	=>	'8000',	3504	=>	'8000',	3578	=>	'8000',	3848	=>	'8000',	453	=>	'8100',	2051	=>	'8100',	3440	=>	'8100',	3438	=>	'8100',	2196	=>	'8100',	2147	=>	'8100',	2078	=>	'8100'
//		,	329	=>	'8100',	660	=>	'8100',	499	=>	'8100',	3420	=>	'8100',	3443	=>	'8100',	3442	=>	'8100',	3441	=>	'8100',	449	=>	'8100',	2926	=>	'8100',	2768	=>	'8100',	480	=>	'8100',	765	=>	'8100',	900	=>	'8100',	1035	=>	'8100',	2533	=>	'8100',	2660	=>	'8100',	2747	=>	'8100',	2873	=>	'8100',	3850	=>	'8100',	3866	=>	'8100',	3887	=>	'8100',	4018	=>	'8100'
//		,	4069	=>	'8100',	4192	=>	'8100',	4223	=>	'8100',	4267	=>	'8100',	4268	=>	'8100',	4269	=>	'8100',	4281	=>	'8100',	2045	=>	'8200',	1114	=>	'8200',	1105	=>	'8200',	1183	=>	'8200',	1481	=>	'8200',	2444	=>	'8200',	843	=>	'8200',	818	=>	'8200',	1889	=>	'8200',	1911	=>	'8200',	2038	=>	'8200',	2487	=>	'8200',	3593	=>	'8200',	3602	=>	'8200',	3859	=>	'8200'
//		,	3921	=>	'8200',	166	=>	'8300',	3287	=>	'8300',	1264	=>	'8300',	630	=>	'8300',	3423	=>	'8300',	2875	=>	'8300',	2872	=>	'8300',	937	=>	'8300',	1021	=>	'8300',	2832	=>	'8300',	2834	=>	'8300',	3085	=>	'8300',	3530	=>	'8300',	3604	=>	'8300',	1745	=>	'8400',	1864	=>	'8400',	2943	=>	'8400',	454	=>	'8400',	902	=>	'8400',	2882	=>	'8400',	3930	=>	'8400'
//		,	4065	=>	'8400',	1291	=>	'8500',	1226	=>	'8500',	1262	=>	'8500',	1313	=>	'8500',	1288	=>	'8500',	1287	=>	'8500',	1231	=>	'8500',	1210	=>	'8500',	1205	=>	'8500',	1160	=>	'8500',	1116	=>	'8500',	1092	=>	'8500',	3376	=>	'8500',	93		=>	'8500',	2552	=>	'8500',	1503	=>	'8500',	2028	=>	'8500',	2100	=>	'8500',	2151	=>	'8500',	2155	=>	'8500',	2715	=>	'8500'
//		,	3579	=>	'8500',	3580	=>	'8500',	3595	=>	'8500',	3596	=>	'8500',	3598	=>	'8500',	3611	=>	'8500',	3728	=>	'8500',	3732	=>	'8500',	3752	=>	'8500',	2329	=>	'8600',	582	=>	'8600',	2150	=>	'8600',	1991	=>	'8600',	1758	=>	'8600',	2352	=>	'8600',	2039	=>	'8600',	524	=>	'8600',	39		=>	'8600',	1202	=>	'8600',	1173	=>	'8600',	1289	=>	'8600',	2220	=>	'8600'
//		,	486	=>	'8600',	124	=>	'8600',	123	=>	'8600',	1866	=>	'8600',	1551	=>	'8600',	3148	=>	'8600',	3139	=>	'8600',	533	=>	'8600',	612	=>	'8600',	927	=>	'8600',	1794	=>	'8600',	2097	=>	'8600',	2153	=>	'8600',	2378	=>	'8600',	2482	=>	'8600',	2570	=>	'8600',	2618	=>	'8600',	2625	=>	'8600',	2969	=>	'8600',	2975	=>	'8600',	3455	=>	'8600',	3458	=>	'8600'
//		,	3946	=>	'8600',	3994	=>	'8600',	4201	=>	'8600',	4258	=>	'8600',	2425	=>	'8700',	2249	=>	'8700',	1266	=>	'8700',	1869	=>	'8700',	1773	=>	'8700',	3360	=>	'8700',	799	=>	'8700',	2925	=>	'8700',	631	=>	'8700',	1811	=>	'8700',	1875	=>	'8700',	2137	=>	'8700',	2383	=>	'8700',	2584	=>	'8700',	2647	=>	'8700',	2871	=>	'8700',	3164	=>	'8700',	4038	=>	'8700'
//		,	1020	=>	'8800',	457	=>	'8800',	622	=>	'8800',	1096	=>	'8800',	645	=>	'8800',	918	=>	'8800',	1730	=>	'8800',	1945	=>	'8800',	2010	=>	'8800',	3616	=>	'8800',	4218	=>	'8800',	4221	=>	'8800',	1254	=>	'8900',	3315	=>	'8900',	142	=>	'8900',	908	=>	'8900',	906	=>	'8900',	1539	=>	'8900',	1936	=>	'8900',	1938	=>	'8900',	3558	=>	'8900',	3632	=>	'8900'
//		,	520	=>	'9000',	187	=>	'9000',	871	=>	'9000',	788	=>	'9000',	726	=>	'9000',	2260	=>	'9000',	7		=>	'9000',	1051	=>	'9000',	1061	=>	'9000',	1459	=>	'9000',	1924	=>	'9000',	3513	=>	'9000',	3900	=>	'9000',	4203	=>	'9000',	1579	=>	'9100',	325	=>	'9100',	1808	=>	'9100',	1310	=>	'9100',	1144	=>	'9100',	2124	=>	'9100',	3417	=>	'9100',	326	=>	'9100'
//		,	2723	=>	'9100',	2721	=>	'9100',	2372	=>	'9100',	2004	=>	'9100',	1756	=>	'9100',	551	=>	'9100',	3312	=>	'9100',	1782	=>	'9100',	2319	=>	'9100',	2376	=>	'9100',	2467	=>	'9100',	2521	=>	'9100',	2632	=>	'9100',	2769	=>	'9100',	3239	=>	'9100',	3555	=>	'9100',	3620	=>	'9100',	3690	=>	'9100',	4009	=>	'9100',	4075	=>	'9100',	4076	=>	'9100',	4354	=>	'9100'
//		,	680	=>	'9200',	1252	=>	'9200',	1504	=>	'9200',	1375	=>	'9200',	1818	=>	'9200',	412	=>	'9200',	1301	=>	'9200',	860	=>	'9200',	3622	=>	'9200',	3624	=>	'9200',	3889	=>	'9200',	4020	=>	'9200',	4138	=>	'9200',	4243	=>	'9200',	1005	=>	'9300',	2251	=>	'9300',	1478	=>	'9300',	2184	=>	'9300',	1003	=>	'9300',	1917	=>	'9300',	1564	=>	'9300',	1447	=>	'9300'
//		,	930	=>	'9300',	52		=>	'9300',	550	=>	'9300',	1101	=>	'9300',	2003	=>	'9300',	2281	=>	'9300',	3472	=>	'9300',	3486	=>	'9300',	3778	=>	'9300',	3881	=>	'9300',	3995	=>	'9300',	4025	=>	'9300',	3203	=>	'9500',	1977	=>	'9500',	1494	=>	'9500',	451	=>	'9500',	2154	=>	'9500',	1527	=>	'9500',	38		=>	'9500',	450	=>	'9500',	531	=>	'9500',	1727	=>	'9500'
//		,	1728	=>	'9500',	2288	=>	'9500',	3000	=>	'9500',	3476	=>	'9500',	4027	=>	'9500',	4048	=>	'9500',	2945	=>	'9600',	3303	=>	'9600',	2567	=>	'9600',	2884	=>	'9600',	3107	=>	'9600',	3548	=>	'9600',	1995	=>	'9700',	568	=>	'9700',	3363	=>	'9700',	86		=>	'9700',	238	=>	'9700',	2846	=>	'9700',	5		=>	'9700',	308	=>	'9700',	1458	=>	'9700',	1495	=>	'9700'
//		,	1649	=>	'9700',	1788	=>	'9700',	2461	=>	'9700',	2822	=>	'9700',	3168	=>	'9700',	4099	=>	'9700',	152	=>	'9800',	456	=>	'9800',	213	=>	'9800',	701	=>	'9800',	1246	=>	'9800',	2377	=>	'9800',	1444	=>	'9800',	1315	=>	'9800',	3344	=>	'9800',	3337	=>	'9800',	3335	=>	'9800',	2144	=>	'9800',	230	=>	'9800',	2048	=>	'9800',	1876	=>	'9800',	3386	=>	'9800'
//		,	3339	=>	'9800',	3338	=>	'9800',	3336	=>	'9800',	2064	=>	'9800',	487	=>	'9800',	3424	=>	'9800',	1316	=>	'9800',	632	=>	'9800',	730	=>	'9800',	904	=>	'9800',	929	=>	'9800',	1046	=>	'9800',	1583	=>	'9800',	2176	=>	'9800',	2268	=>	'9800',	2411	=>	'9800',	2497	=>	'9800',	2532	=>	'9800',	2634	=>	'9800',	3140	=>	'9800',	3144	=>	'9800',	3201	=>	'9800'
//		,	3605	=>	'9800',	3618	=>	'9800',	3759	=>	'9800',	3760	=>	'9800',	3761	=>	'9800',	3762	=>	'9800',	3763	=>	'9800',	3764	=>	'9800',	3810	=>	'9800',	4037	=>	'9800',	289	=>	'9900',	1582	=>	'9900',	1554	=>	'9900',	1934	=>	'9900',	100	=>	'9900',	270	=>	'9900',	1985	=>	'9900',	1786	=>	'9900',	42		=>	'9900',	21		=>	'9900',	865	=>	'9900',	171	=>	'9900'
//		,	10		=>	'9900',	1446	=>	'9900',	521	=>	'9900',	537	=>	'9900',	583	=>	'9900',	704	=>	'9900',	727	=>	'9900',	2437	=>	'9900',	3466	=>	'9900',	3493	=>	'9900',	3527	=>	'9900',	3843	=>	'9900',	3870	=>	'9900',	3876	=>	'9900',	3933	=>	'9900',	3970	=>	'9900',	4251	=>	'9900',	1268	=>	'10000',	1590	=>	'10000',	2029	=>	'10000',	1319	=>	'10000',	1265	=>	'10000'
//		,	1211	=>	'10000',	1194	=>	'10000',	1148	=>	'10000',	1285	=>	'10000',	1025	=>	'10000',	1013	=>	'10000',	96		=>	'10000',	3160	=>	'10000',	1228	=>	'10000',	1128	=>	'10000',	1261	=>	'10000',	578	=>	'10000',	2012	=>	'10000',	2056	=>	'10000',	2113	=>	'10000',	2134	=>	'10000',	2149	=>	'10000',	2178	=>	'10000',	2320	=>	'10000',	2477	=>	'10000',	2980	=>	'10000',	3452	=>	'10000'
//		,	3597	=>	'10000',	3599	=>	'10000',	3696	=>	'10000',	3701	=>	'10000',	3780	=>	'10000',	3340	=>	'10100',	196	=>	'10100',	394	=>	'10100',	1136	=>	'10100',	668	=>	'10100',	3142	=>	'10100',	3721	=>	'10100',	3791	=>	'10100',	1848	=>	'10200',	919	=>	'10200',	571	=>	'10200',	3309	=>	'10200',	985	=>	'10200',	1947	=>	'10200',	2406	=>	'10200',	3117	=>	'10200',	3345	=>	'10300'
//		,	2340	=>	'10300',	2086	=>	'10300',	2081	=>	'10300',	1729	=>	'10300',	2267	=>	'10300',	2006	=>	'10300',	2082	=>	'10300',	1739	=>	'10300',	1595	=>	'10300',	3156	=>	'10300',	72		=>	'10300',	563	=>	'10300',	2369	=>	'10300',	2504	=>	'10300',	2590	=>	'10300',	3149	=>	'10300',	3751	=>	'10300',	3923	=>	'10300',	4035	=>	'10300',	4039	=>	'10300',	4052	=>	'10300',	4056	=>	'10300'
//		,	4066	=>	'10300',	1302	=>	'10400',	3173	=>	'10400',	705	=>	'10400',	351	=>	'10400',	1277	=>	'10400',	3192	=>	'10400',	3133	=>	'10400',	789	=>	'10400',	2949	=>	'10400',	2986	=>	'10400',	2994	=>	'10400',	3691	=>	'10400',	3733	=>	'10400',	4198	=>	'10400',	2199	=>	'10500',	2198	=>	'10500',	3342	=>	'10500',	1791	=>	'10500',	2390	=>	'10500',	2553	=>	'10500',	3768	=>	'10500'
//		,	3898	=>	'10500',	2195	=>	'10600',	2269	=>	'10600',	2112	=>	'10600',	2365	=>	'10600',	35		=>	'10600',	2396	=>	'10600',	1972	=>	'10600',	1724	=>	'10600',	1659	=>	'10600',	1439	=>	'10600',	859	=>	'10600',	2361	=>	'10600',	2119	=>	'10600',	2027	=>	'10600',	1593	=>	'10600',	2273	=>	'10600',	1442	=>	'10600',	2785	=>	'10600',	1426	=>	'10600',	178	=>	'10600',	1831	=>	'10600'
//		,	3116	=>	'10600',	1123	=>	'10600',	2468	=>	'10600',	2466	=>	'10600',	2463	=>	'10600',	2430	=>	'10600',	117	=>	'10600',	528	=>	'10600',	600	=>	'10600',	1552	=>	'10600',	1922	=>	'10600',	2445	=>	'10600',	2476	=>	'10600',	2551	=>	'10600',	2629	=>	'10600',	2639	=>	'10600',	2651	=>	'10600',	2667	=>	'10600',	2668	=>	'10600',	2669	=>	'10600',	2799	=>	'10600',	2937	=>	'10600'
//		,	3457	=>	'10600',	3461	=>	'10600',	3467	=>	'10600',	3473	=>	'10600',	3600	=>	'10600',	3879	=>	'10600',	3892	=>	'10600',	3902	=>	'10600',	3903	=>	'10600',	3958	=>	'10600',	3989	=>	'10600',	4032	=>	'10600',	4055	=>	'10600',	1464	=>	'10700',	1321	=>	'10700',	1089	=>	'10700',	1751	=>	'10700',	2391	=>	'10700',	882	=>	'10700',	881	=>	'10700',	888	=>	'10700',	998	=>	'10700'
//		,	3419	=>	'10700',	2280	=>	'10700',	892	=>	'10700',	886	=>	'10700',	884	=>	'10700',	880	=>	'10700',	878	=>	'10700',	510	=>	'10700',	1078	=>	'10700',	1079	=>	'10700',	1080	=>	'10700',	1081	=>	'10700',	1082	=>	'10700',	1084	=>	'10700',	1086	=>	'10700',	1090	=>	'10700',	1999	=>	'10700',	2026	=>	'10700',	2597	=>	'10700',	2638	=>	'10700',	3865	=>	'10700',	3993	=>	'10700'
//		,	4002	=>	'10700',	4196	=>	'10700',	4277	=>	'10700',	1734	=>	'10800',	522	=>	'10800',	76		=>	'10800',	1118	=>	'10800',	569	=>	'10800',	3499	=>	'10800',	3659	=>	'10800',	4202	=>	'10800',	874	=>	'10900',	2374	=>	'10900',	1223	=>	'10900',	794	=>	'10900',	574	=>	'10900',	876	=>	'10900',	500	=>	'10900',	214	=>	'10900',	3320	=>	'10900',	940	=>	'10900',	990	=>	'10900'
//		,	1063	=>	'10900',	1077	=>	'10900',	1925	=>	'10900',	2633	=>	'10900',	3125	=>	'10900',	3731	=>	'10900',	3811	=>	'10900',	233	=>	'11000',	861	=>	'11000',	1821	=>	'11000',	1322	=>	'11000',	1028	=>	'11000',	82		=>	'11000',	638	=>	'11000',	2014	=>	'11000',	2182	=>	'11000',	3449	=>	'11000',	3460	=>	'11000',	3772	=>	'11000',	4089	=>	'11000',	4096	=>	'11000',	1682	=>	'11100'
//		,	1212	=>	'11100',	666	=>	'11100',	2210	=>	'11100',	2092	=>	'11100',	1015	=>	'11100',	965	=>	'11100',	2136	=>	'11100',	2263	=>	'11100',	3291	=>	'11100',	1147	=>	'11100',	1036	=>	'11100',	1236	=>	'11100',	1967	=>	'11100',	2065	=>	'11100',	2106	=>	'11100',	2353	=>	'11100',	2528	=>	'11100',	2562	=>	'11100',	3088	=>	'11100',	3894	=>	'11100',	4040	=>	'11100',	3357	=>	'11200'
//		,	2957	=>	'11200',	2951	=>	'11200',	3232	=>	'11200',	1428	=>	'11200',	2619	=>	'11200',	1000	=>	'11200',	3365	=>	'11200',	3356	=>	'11200',	1396	=>	'11200',	1342	=>	'11200',	3158	=>	'11200',	3119	=>	'11200',	2886	=>	'11200',	2896	=>	'11200',	2939	=>	'11200',	3018	=>	'11200',	3159	=>	'11200',	3161	=>	'11200',	3171	=>	'11200',	3502	=>	'11200',	3567	=>	'11200',	3746	=>	'11200'
//		,	3779	=>	'11200',	4167	=>	'11200',	4170	=>	'11200',	1919	=>	'11300',	1639	=>	'11300',	1686	=>	'11300',	2291	=>	'11300',	1987	=>	'11300',	1645	=>	'11300',	1490	=>	'11300',	1436	=>	'11300',	682	=>	'11300',	2232	=>	'11300',	2821	=>	'11300',	2394	=>	'11300',	785	=>	'11300',	2382	=>	'11300',	3316	=>	'11300',	1041	=>	'11300',	1872	=>	'11300',	2261	=>	'11300',	2285	=>	'11300'
//		,	2341	=>	'11300',	2575	=>	'11300',	2635	=>	'11300',	2816	=>	'11300',	3121	=>	'11300',	3470	=>	'11300',	3888	=>	'11300',	3904	=>	'11300',	3937	=>	'11300',	3969	=>	'11300',	4010	=>	'11300',	674	=>	'11400',	2096	=>	'11400',	1887	=>	'11400',	729	=>	'11400',	1312	=>	'11400',	1308	=>	'11400',	1215	=>	'11400',	1094	=>	'11400',	1528	=>	'11400',	3226	=>	'11400',	1174	=>	'11400'
//		,	597	=>	'11400',	3120	=>	'11400',	3405	=>	'11400',	2428	=>	'11400',	1172	=>	'11400',	1168	=>	'11400',	1127	=>	'11400',	1053	=>	'11400',	1652	=>	'11400',	2173	=>	'11400',	2295	=>	'11400',	2412	=>	'11400',	2509	=>	'11400',	2649	=>	'11400',	2940	=>	'11400',	3015	=>	'11400',	3225	=>	'11400',	3582	=>	'11400',	3607	=>	'11400',	3627	=>	'11400',	3628	=>	'11400',	3654	=>	'11400'
//		,	3705	=>	'11400',	3706	=>	'11400',	4185	=>	'11400',	4209	=>	'11400',	1710	=>	'11500',	1783	=>	'11500',	3484	=>	'11500',	3916	=>	'11500',	3349	=>	'11600',	3346	=>	'11600',	3355	=>	'11600',	2555	=>	'11600',	920	=>	'11600',	2717	=>	'11600',	3150	=>	'11600',	3155	=>	'11600',	3157	=>	'11600',	3507	=>	'11600',	311	=>	'11700',	2387	=>	'11700',	1661	=>	'11700',	2401	=>	'11700'
//		,	1654	=>	'11700',	1655	=>	'11700',	1657	=>	'11700',	2347	=>	'11700',	2637	=>	'11700',	2641	=>	'11700',	1986	=>	'11800',	4		=>	'11800',	1754	=>	'11800',	1573	=>	'11800',	1213	=>	'11800',	250	=>	'11800',	11		=>	'11800',	2277	=>	'11800',	255	=>	'11800',	305	=>	'11800',	1457	=>	'11800',	2318	=>	'11800',	2453	=>	'11800',	2594	=>	'11800',	3572	=>	'11800',	3693	=>	'11800'
//		,	4016	=>	'11800',	4098	=>	'11800',	4107	=>	'11800',	2187	=>	'11900',	2118	=>	'11900',	1910	=>	'11900',	2122	=>	'11900',	2833	=>	'11900',	2787	=>	'11900',	2752	=>	'11900',	2181	=>	'11900',	2544	=>	'11900',	2546	=>	'11900',	2786	=>	'11900',	2801	=>	'11900',	2819	=>	'11900',	3943	=>	'11900',	4022	=>	'11900',	4031	=>	'11900',	2138	=>	'12000',	2948	=>	'12000',	191	=>	'12000'
//		,	2421	=>	'12000',	494	=>	'12000',	923	=>	'12000',	663	=>	'12000',	933	=>	'12000',	1098	=>	'12000',	2529	=>	'12000',	2645	=>	'12000',	2885	=>	'12000',	3511	=>	'12000',	2305	=>	'12100',	696	=>	'12100',	2015	=>	'12100',	588	=>	'12100',	3371	=>	'12100',	507	=>	'12100',	489	=>	'12100',	1045	=>	'12100',	1772	=>	'12100',	2608	=>	'12100',	3758	=>	'12100',	4063	=>	'12100'
//		,	4191	=>	'12100',	4199	=>	'12100',	2229	=>	'12200',	2282	=>	'12200',	3281	=>	'12200',	3187	=>	'12200',	2019	=>	'12200',	1812	=>	'12200',	611	=>	'12200',	605	=>	'12200',	512	=>	'12200',	3262	=>	'12200',	2503	=>	'12200',	3297	=>	'12200',	950	=>	'12200',	2399	=>	'12200',	2472	=>	'12200',	2574	=>	'12200',	2684	=>	'12200',	2991	=>	'12200',	3049	=>	'12200',	3077	=>	'12200'
//		,	3096	=>	'12200',	4053	=>	'12200',	4215	=>	'12200',	4227	=>	'12200',	2758	=>	'12300',	766	=>	'12300',	554	=>	'12300',	462	=>	'12300',	1372	=>	'12300',	2848	=>	'12300',	2831	=>	'12300',	2828	=>	'12300',	2795	=>	'12300',	2792	=>	'12300',	2782	=>	'12300',	2780	=>	'12300',	909	=>	'12300',	1863	=>	'12300',	2796	=>	'12300',	2798	=>	'12300',	4172	=>	'12300',	4219	=>	'12300'
//		,	4292	=>	'12300',	4293	=>	'12300',	4294	=>	'12300',	4295	=>	'12300',	4296	=>	'12300',	4297	=>	'12300',	1103	=>	'12400',	709	=>	'12400',	177	=>	'12400',	1279	=>	'12400',	1492	=>	'12400',	283	=>	'12400',	573	=>	'12400',	3358	=>	'12400',	1434	=>	'12400',	722	=>	'12400',	988	=>	'12400',	1048	=>	'12400',	2037	=>	'12400',	2259	=>	'12400',	2287	=>	'12400',	2511	=>	'12400'
//		,	2677	=>	'12400',	3162	=>	'12400',	3528	=>	'12400',	3838	=>	'12400',	256	=>	'12600',	1932	=>	'12600',	1976	=>	'12600',	1501	=>	'12600',	2013	=>	'12600',	455	=>	'12600',	24		=>	'12600',	525	=>	'12600',	1602	=>	'12600',	2469	=>	'12600',	3852	=>	'12600',	3907	=>	'12600',	3976	=>	'12600',	4062	=>	'12600',	685	=>	'12700',	1440	=>	'12700',	1271	=>	'12700',	1121	=>	'12700'
//		,	1026	=>	'12700',	1747	=>	'12700',	1149	=>	'12700',	1979	=>	'12700',	153	=>	'12700',	1467	=>	'12700',	1220	=>	'12700',	634	=>	'12700',	2266	=>	'12700',	3500	=>	'12700',	3591	=>	'12700',	3609	=>	'12700',	3684	=>	'12700',	3823	=>	'12700',	3839	=>	'12700',	3927	=>	'12700',	4026	=>	'12700',	4184	=>	'12700',	1517	=>	'12800',	1186	=>	'12800',	1251	=>	'12800',	2338	=>	'12800'
//		,	2116	=>	'12800',	2240	=>	'12800',	1238	=>	'12800',	1065	=>	'12800',	3372	=>	'12800',	2018	=>	'12800',	2087	=>	'12800',	2292	=>	'12800',	2518	=>	'12800',	2621	=>	'12800',	3180	=>	'12800',	3643	=>	'12800',	3645	=>	'12800',	3878	=>	'12800',	762	=>	'13000',	2490	=>	'13000',	3266	=>	'13000',	1951	=>	'13000',	935	=>	'13000',	1861	=>	'13000',	2679	=>	'13000',	3055	=>	'13000'
//		,	3496	=>	'13000',	3672	=>	'13000',	4247	=>	'13000',	3141	=>	'13100',	1018	=>	'13100',	208	=>	'13100',	20		=>	'13100',	3308	=>	'13100',	1463	=>	'13100',	1567	=>	'13100',	2009	=>	'13100',	2971	=>	'13100',	3115	=>	'13100',	1669	=>	'13200',	1443	=>	'13200',	310	=>	'13200',	2058	=>	'13200',	1509	=>	'13200',	1097	=>	'13200',	1091	=>	'13200',	1191	=>	'13200',	2271	=>	'13200'
//		,	2079	=>	'13200',	1983	=>	'13200',	1698	=>	'13200',	1547	=>	'13200',	1076	=>	'13200',	1975	=>	'13200',	355	=>	'13200',	212	=>	'13200',	1263	=>	'13200',	1108	=>	'13200',	1087	=>	'13200',	797	=>	'13200',	1650	=>	'13200',	2032	=>	'13200',	3469	=>	'13200',	3608	=>	'13200',	3650	=>	'13200',	3673	=>	'13200',	3722	=>	'13200',	3723	=>	'13200',	3724	=>	'13200',	3812	=>	'13200'
//		,	3893	=>	'13200',	3899	=>	'13200',	3945	=>	'13200',	3952	=>	'13200',	3971	=>	'13200',	3977	=>	'13200',	3987	=>	'13200',	3997	=>	'13200',	4355	=>	'13200',	1989	=>	'13300',	2349	=>	'13300',	2694	=>	'13300',	27		=>	'13300',	26		=>	'13300',	200	=>	'13300',	3406	=>	'13300',	527	=>	'13300',	1466	=>	'13300',	2623	=>	'13300',	3057	=>	'13300',	3541	=>	'13300',	3568	=>	'13300'
//		,	3841	=>	'13300',	3967	=>	'13300',	1974	=>	'13400',	197	=>	'13400',	3341	=>	'13400',	1884	=>	'13400',	1237	=>	'13400',	1196	=>	'13400',	1452	=>	'13400',	3348	=>	'13400',	3347	=>	'13400',	1234	=>	'13400',	3300	=>	'13400',	669	=>	'13400',	2117	=>	'13400',	3101	=>	'13400',	3145	=>	'13400',	3152	=>	'13400',	3153	=>	'13400',	3639	=>	'13400',	3640	=>	'13400',	3877	=>	'13400'
//		,	3905	=>	'13400',	4034	=>	'13400',	209	=>	'13500',	2049	=>	'13500',	1854	=>	'13500',	1726	=>	'13500',	1680	=>	'13500',	2212	=>	'13500',	1825	=>	'13500',	3124	=>	'13500',	1838	=>	'13500',	1544	=>	'13500',	81		=>	'13500',	1706	=>	'13500',	3252	=>	'13500',	2179	=>	'13500',	1259	=>	'13500',	2718	=>	'13500',	2351	=>	'13500',	2542	=>	'13500',	2563	=>	'13500',	2615	=>	'13500'
//		,	2767	=>	'13500',	2942	=>	'13500',	3037	=>	'13500',	3459	=>	'13500',	3491	=>	'13500',	3514	=>	'13500',	3651	=>	'13500',	3935	=>	'13500',	3974	=>	'13500',	3984	=>	'13500',	4041	=>	'13500',	4059	=>	'13500',	4090	=>	'13500',	396	=>	'13600',	1973	=>	'13600',	2167	=>	'13600',	1568	=>	'13600',	1541	=>	'13600',	745	=>	'13600',	234	=>	'13600',	1691	=>	'13600',	1851	=>	'13600'
//		,	2447	=>	'13600',	3917	=>	'13600',	3947	=>	'13600',	4001	=>	'13600',	4110	=>	'13600',	2159	=>	'13700',	1354	=>	'13700',	1074	=>	'13700',	1257	=>	'13700',	863	=>	'13700',	299	=>	'13700',	1071	=>	'13700',	1638	=>	'13700',	2132	=>	'13700',	2203	=>	'13700',	2538	=>	'13700',	3678	=>	'13700',	2218	=>	'13800',	452	=>	'13800',	1134	=>	'13800',	736	=>	'13800',	1283	=>	'13800'
//		,	1859	=>	'13800',	1431	=>	'13800',	2146	=>	'13800',	2409	=>	'13800',	2568	=>	'13800',	3494	=>	'13800',	3663	=>	'13800',	3851	=>	'13800',	4197	=>	'13800',	1201	=>	'13900',	1276	=>	'13900',	1249	=>	'13900',	232	=>	'13900',	657	=>	'13900',	3151	=>	'13900',	2335	=>	'13900',	2169	=>	'13900',	1317	=>	'13900',	3126	=>	'13900',	1034	=>	'13900',	2620	=>	'13900',	2944	=>	'13900'
//		,	2976	=>	'13900',	3577	=>	'13900',	3649	=>	'13900',	3679	=>	'13900',	3686	=>	'13900',	3702	=>	'13900',	4005	=>	'13900',	4081	=>	'13900',	4108	=>	'13900',	375	=>	'14000',	346	=>	'14000',	3288	=>	'14000',	2564	=>	'14000',	717	=>	'14000',	1421	=>	'14000',	1337	=>	'14000',	3265	=>	'14000',	784	=>	'14000',	821	=>	'14000',	2720	=>	'14000',	3054	=>	'14000',	3086	=>	'14000'
//		,	4118	=>	'14000',	4176	=>	'14000',	4239	=>	'14000',	199	=>	'14100',	188	=>	'14100',	1873	=>	'14100',	689	=>	'14100',	661	=>	'14100',	1044	=>	'14100',	3531	=>	'14100',	4029	=>	'14100',	1269	=>	'14200',	1311	=>	'14200',	1298	=>	'14200',	1282	=>	'14200',	1274	=>	'14200',	1153	=>	'14200',	1122	=>	'14200',	1085	=>	'14200',	1209	=>	'14200',	1188	=>	'14200',	1131	=>	'14200'
//		,	1648	=>	'14200',	1599	=>	'14200',	2384	=>	'14200',	858	=>	'14200',	1070	=>	'14200',	2025	=>	'14200',	2089	=>	'14200',	2103	=>	'14200',	2145	=>	'14200',	2322	=>	'14200',	2343	=>	'14200',	2636	=>	'14200',	3584	=>	'14200',	3641	=>	'14200',	3680	=>	'14200',	3718	=>	'14200',	3720	=>	'14200',	3726	=>	'14200',	3727	=>	'14200',	2363	=>	'14300',	352	=>	'14300',	2427	=>	'14300'
//		,	1597	=>	'14300',	335	=>	'14300',	3311	=>	'14300',	773	=>	'14300',	792	=>	'14300',	2321	=>	'14300',	2628	=>	'14300',	2648	=>	'14300',	3553	=>	'14300',	538	=>	'14400',	1242	=>	'14400',	143	=>	'14400',	566	=>	'14400',	1571	=>	'14400',	1217	=>	'14400',	791	=>	'14400',	623	=>	'14400',	968	=>	'14400',	1062	=>	'14400',	1787	=>	'14400',	2121	=>	'14400',	2317	=>	'14400'
//		,	3634	=>	'14400',	2761	=>	'14500',	1004	=>	'14500',	3366	=>	'14500',	1233	=>	'14500',	2791	=>	'14500',	3172	=>	'14500',	3590	=>	'14500',	1216	=>	'14600',	2294	=>	'14600',	2332	=>	'14600',	2126	=>	'14600',	2115	=>	'14600',	1192	=>	'14600',	1432	=>	'14600',	411	=>	'14600',	2256	=>	'14600',	2522	=>	'14600',	2603	=>	'14600',	3669	=>	'14600',	3729	=>	'14600',	3786	=>	'14600'
//		,	3934	=>	'14600',	3999	=>	'14600',	363	=>	'14700',	359	=>	'14700',	1325	=>	'14700',	807	=>	'14700',	1679	=>	'14700',	2185	=>	'14700',	170	=>	'14800',	1138	=>	'14800',	3295	=>	'14800',	3244	=>	'14800',	1124	=>	'14800',	1120	=>	'14800',	1100	=>	'14800',	1294	=>	'14800',	1095	=>	'14800',	2030	=>	'14800',	2034	=>	'14800',	2052	=>	'14800',	2054	=>	'14800',	2060	=>	'14800'
//		,	3024	=>	'14800',	3095	=>	'14800',	3520	=>	'14800',	3677	=>	'14800',	643	=>	'14900',	1150	=>	'14900',	22		=>	'14900',	1281	=>	'14900',	366	=>	'14900',	628	=>	'14900',	2452	=>	'14900',	890	=>	'14900',	25		=>	'14900',	523	=>	'14900',	526	=>	'14900',	809	=>	'14900',	1019	=>	'14900',	1027	=>	'14900',	1088	=>	'14900',	2066	=>	'14900',	2663	=>	'14900',	3646	=>	'14900'
//		,	532	=>	'15000',	738	=>	'15000',	1382	=>	'15000',	964	=>	'15000',	1845	=>	'15000',	2217	=>	'15000',	3113	=>	'15100',	1200	=>	'15100',	391	=>	'15100',	626	=>	'15100',	570	=>	'15100',	1790	=>	'15100',	2935	=>	'15100',	3581	=>	'15100',	3792	=>	'15100',	4225	=>	'15100',	2389	=>	'15200',	3255	=>	'15200',	476	=>	'15200',	502	=>	'15200',	1853	=>	'15200',	415	=>	'15200'
//		,	419	=>	'15200',	416	=>	'15200',	414	=>	'15200',	864	=>	'15200',	866	=>	'15200',	868	=>	'15200',	922	=>	'15200',	1699	=>	'15200',	2407	=>	'15200',	3748	=>	'15200',	3872	=>	'15200',	4216	=>	'15200',	1603	=>	'15300',	1692	=>	'15300',	2323	=>	'15300',	2355	=>	'15300',	186	=>	'15400',	2158	=>	'15400',	1235	=>	'15400',	257	=>	'15400',	1427	=>	'15400',	658	=>	'15400'
//		,	3453	=>	'15400',	3583	=>	'15400',	4047	=>	'15400',	4100	=>	'15400',	2614	=>	'15500',	751	=>	'15500',	711	=>	'15500',	1112	=>	'15500',	2749	=>	'15500',	3630	=>	'15500',	4187	=>	'15500',	4235	=>	'15500',	1299	=>	'15600',	1199	=>	'15600',	1993	=>	'15600',	2733	=>	'15600',	407	=>	'15600',	2858	=>	'15600',	2753	=>	'15600',	2737	=>	'15600',	2735	=>	'15600',	855	=>	'15600'
//		,	2094	=>	'15600',	2162	=>	'15600',	2200	=>	'15600',	2775	=>	'15600',	2776	=>	'15600',	2778	=>	'15600',	2788	=>	'15600',	2827	=>	'15600',	3478	=>	'15600',	2201	=>	'15700',	2099	=>	'15700',	3261	=>	'15700',	2206	=>	'15700',	3147	=>	'15700',	2456	=>	'15700',	3122	=>	'15700',	2554	=>	'15700',	2557	=>	'15700',	3735	=>	'15700',	3737	=>	'15700',	3749	=>	'15700',	3862	=>	'15700'
//		,	3910	=>	'15700',	3211	=>	'15800',	3200	=>	'15800',	2999	=>	'15800',	3005	=>	'15800',	1688	=>	'15900',	688	=>	'15900',	193	=>	'15900',	203	=>	'15900',	2036	=>	'15900',	1441	=>	'15900',	1842	=>	'15900',	205	=>	'15900',	2017	=>	'15900',	1351	=>	'15900',	1878	=>	'15900',	1830	=>	'15900',	2404	=>	'15900',	2471	=>	'15900',	2481	=>	'15900',	3464	=>	'15900',	3518	=>	'15900'
//		,	3522	=>	'15900',	3524	=>	'15900',	3931	=>	'15900',	3963	=>	'15900',	4181	=>	'15900',	2952	=>	'16000',	2279	=>	'16000',	2435	=>	'16000',	1558	=>	'16000',	3389	=>	'16000',	1185	=>	'16000',	349	=>	'16000',	1187	=>	'16000',	1796	=>	'16000',	1109	=>	'16000',	1676	=>	'16000',	2393	=>	'16000',	2595	=>	'16000',	2655	=>	'16000',	2888	=>	'16000',	3205	=>	'16000',	3503	=>	'16000'
//		,	3588	=>	'16000',	3707	=>	'16000',	3715	=>	'16000',	204	=>	'16100',	284	=>	'16100',	1634	=>	'16100',	2432	=>	'16100',	654	=>	'16100',	1430	=>	'16100',	719	=>	'16100',	3314	=>	'16100',	278	=>	'16100',	714	=>	'16100',	1033	=>	'16100',	1565	=>	'16100',	1623	=>	'16100',	2336	=>	'16100',	2653	=>	'16100',	3485	=>	'16100',	3506	=>	'16100',	4208	=>	'16100',	2744	=>	'16200'
//		,	1198	=>	'16200',	2855	=>	'16200',	2813	=>	'16200',	2790	=>	'16200',	2777	=>	'16200',	2771	=>	'16200',	2755	=>	'16200',	2751	=>	'16200',	493	=>	'16200',	1762	=>	'16200',	2781	=>	'16200',	2784	=>	'16200',	2789	=>	'16200',	2793	=>	'16200',	2802	=>	'16200',	2809	=>	'16200',	2825	=>	'16200',	3730	=>	'16200',	4288	=>	'16200',	1171	=>	'16300',	503	=>	'16300',	373	=>	'16300'
//		,	344	=>	'16300',	1296	=>	'16300',	780	=>	'16300',	943	=>	'16300',	1683	=>	'16300',	2160	=>	'16300',	3587	=>	'16300',	337	=>	'16400',	1157	=>	'16400',	1550	=>	'16400',	1137	=>	'16400',	559	=>	'16400',	155	=>	'16400',	3154	=>	'16400',	3270	=>	'16400',	1304	=>	'16400',	636	=>	'16400',	979	=>	'16400',	2068	=>	'16400',	2165	=>	'16400',	2306	=>	'16400',	2978	=>	'16400'
//		,	3062	=>	'16400',	3676	=>	'16400',	3785	=>	'16400',	71		=>	'16500',	3273	=>	'16500',	3184	=>	'16500',	3229	=>	'16500',	561	=>	'16500',	3016	=>	'16500',	3066	=>	'16500',	3739	=>	'16500',	2194	=>	'16600',	237	=>	'16600',	1871	=>	'16600',	1538	=>	'16600',	2128	=>	'16600',	1587	=>	'16600',	2302	=>	'16600',	3875	=>	'16600',	3936	=>	'16600',	4033	=>	'16600',	189	=>	'16700'
//		,	180	=>	'16700',	190	=>	'16700',	1280	=>	'16700',	517	=>	'16700',	2108	=>	'16700',	735	=>	'16700',	956	=>	'16700',	1055	=>	'16700',	1559	=>	'16700',	2515	=>	'16700',	3535	=>	'16700',	3536	=>	'16700',	3716	=>	'16700',	2208	=>	'16800',	2728	=>	'16800',	1857	=>	'16800',	1329	=>	'16800',	2730	=>	'16800',	1718	=>	'16800',	2367	=>	'16800',	2408	=>	'16800',	2561	=>	'16800'
//		,	2772	=>	'16800',	2773	=>	'16800',	3547	=>	'16800',	1777	=>	'16900',	1165	=>	'16900',	1362	=>	'16900',	3332	=>	'16900',	3331	=>	'16900',	2072	=>	'16900',	2385	=>	'16900',	3844	=>	'16900',	3845	=>	'16900',	4126	=>	'16900',	667	=>	'17000',	624	=>	'17000',	1190	=>	'17000',	40		=>	'17000',	535	=>	'17000',	1037	=>	'17000',	1809	=>	'17000',	2091	=>	'17000',	635	=>	'17100'
//		,	1470	=>	'17100',	3137	=>	'17100',	1813	=>	'17100',	3922	=>	'17100',	664	=>	'17200',	2286	=>	'17200',	2254	=>	'17200',	2152	=>	'17200',	417	=>	'17200',	2174	=>	'17200',	2104	=>	'17200',	1524	=>	'17200',	1255	=>	'17200',	245	=>	'17200',	1244	=>	'17200',	79		=>	'17200',	870	=>	'17200',	2129	=>	'17200',	2535	=>	'17200',	2599	=>	'17200',	3456	=>	'17200',	3675	=>	'17200'
//		,	3880	=>	'17200',	3975	=>	'17200',	3979	=>	'17200',	4111	=>	'17200',	4233	=>	'17200',	4283	=>	'17200',	472	=>	'17300',	572	=>	'17300',	498	=>	'17300',	938	=>	'17300',	3777	=>	'17300',	4190	=>	'17300',	2233	=>	'17400',	1448	=>	'17400',	555	=>	'17400',	181	=>	'17400',	579	=>	'17400',	1984	=>	'17400',	984	=>	'17400',	655	=>	'17400',	962	=>	'17400',	975	=>	'17400'
//		,	994	=>	'17400',	3497	=>	'17400',	4007	=>	'17400',	4008	=>	'17400',	1636	=>	'17500',	653	=>	'17500',	764	=>	'17500',	1032	=>	'17500',	2337	=>	'17500',	4222	=>	'17500',	1135	=>	'17600',	1449	=>	'17600',	673	=>	'17600',	2953	=>	'17600',	1039	=>	'17600',	2272	=>	'17600',	2889	=>	'17600',	3688	=>	'17600',	1805	=>	'17700',	1656	=>	'17700',	83		=>	'17700',	1491	=>	'17700'
//		,	3477	=>	'17700',	3918	=>	'17700',	1416	=>	'17800',	700	=>	'17800',	1835	=>	'17800',	2239	=>	'17800',	4127	=>	'17800',	2316	=>	'17900',	650	=>	'17900',	1425	=>	'17900',	2840	=>	'17900',	2835	=>	'17900',	2742	=>	'17900',	94		=>	'17900',	1031	=>	'17900',	1505	=>	'17900',	2252	=>	'17900',	2498	=>	'17900',	2613	=>	'17900',	4272	=>	'17900',	4273	=>	'17900',	4284	=>	'17900'
//		,	687	=>	'18000',	619	=>	'18000',	168	=>	'18000',	162	=>	'18000',	1937	=>	'18000',	304	=>	'18000',	640	=>	'18000',	646	=>	'18000',	1646	=>	'18000',	1807	=>	'18000',	2439	=>	'18000',	4226	=>	'18000',	3436	=>	'18100',	364	=>	'18100',	418	=>	'18100',	1102	=>	'18100',	1912	=>	'18100',	374	=>	'18100',	575	=>	'18100',	210	=>	'18100',	772	=>	'18100',	819	=>	'18100'
//		,	872	=>	'18100',	1681	=>	'18100',	1865	=>	'18100',	2035	=>	'18100',	3532	=>	'18100',	3992	=>	'18100',	4211	=>	'18100',	4265	=>	'18100',	546	=>	'18200',	3131	=>	'18200',	779	=>	'18200',	141	=>	'18200',	516	=>	'18200',	954	=>	'18200',	2947	=>	'18200',	4230	=>	'18200',	4234	=>	'18200',	4264	=>	'18200',	2192	=>	'18300',	2440	=>	'18300',	1221	=>	'18300',	2549	=>	'18300'
//		,	2658	=>	'18300',	3658	=>	'18300',	2130	=>	'18400',	3245	=>	'18400',	2202	=>	'18400',	2556	=>	'18400',	3026	=>	'18400',	3908	=>	'18400',	2246	=>	'18500',	953	=>	'18500',	1241	=>	'18500',	958	=>	'18500',	949	=>	'18500',	227	=>	'18500',	1664	=>	'18500',	633	=>	'18500',	1083	=>	'18500',	3166	=>	'18500',	1850	=>	'18500',	1580	=>	'18500',	1959	=>	'18500',	1962	=>	'18500'
//		,	1963	=>	'18500',	2023	=>	'18500',	2120	=>	'18500',	2582	=>	'18500',	2983	=>	'18500',	3490	=>	'18500',	3664	=>	'18500',	3665	=>	'18500',	3666	=>	'18500',	3667	=>	'18500',	3668	=>	'18500',	4004	=>	'18500',	4183	=>	'18500',	972	=>	'18600',	287	=>	'18600',	235	=>	'18600',	280	=>	'18600',	151	=>	'18600',	294	=>	'18600',	263	=>	'18600',	262	=>	'18600',	693	=>	'18600'
//		,	732	=>	'18600',	1111	=>	'18600',	1545	=>	'18600',	1610	=>	'18600',	1621	=>	'18600',	1626	=>	'18600',	1778	=>	'18600',	4105	=>	'18600',	1454	=>	'18700',	1415	=>	'18700',	1408	=>	'18700',	1376	=>	'18700',	3305	=>	'18700',	2276	=>	'18700',	3110	=>	'18700',	4128	=>	'18700',	4129	=>	'18700',	4173	=>	'18700',	973	=>	'18900',	251	=>	'18900',	1816	=>	'18900',	1107	=>	'18900'
//		,	1011	=>	'18900',	1736	=>	'18900',	2370	=>	'18900',	1598	=>	'18900',	1970	=>	'18900',	2373	=>	'18900',	2631	=>	'18900',	3615	=>	'18900',	3867	=>	'18900',	3901	=>	'18900',	70		=>	'19000',	556	=>	'19000',	3135	=>	'19000',	1534	=>	'19000',	3260	=>	'19000',	48		=>	'19000',	1487	=>	'19000',	2299	=>	'19000',	2950	=>	'19000',	3047	=>	'19000',	3849	=>	'19000',	4238	=>	'19000'
//		,	3276	=>	'19100',	1139	=>	'19100',	3373	=>	'19100',	369	=>	'19100',	1653	=>	'19100',	32		=>	'19100',	3317	=>	'19100',	815	=>	'19100',	1471	=>	'19100',	3182	=>	'19100',	3483	=>	'19100',	3554	=>	'19100',	3683	=>	'19100',	3741	=>	'19100',	647	=>	'19200',	73		=>	'19200',	698	=>	'19200',	367	=>	'19200',	733	=>	'19200',	811	=>	'19200',	1054	=>	'19200',	1489	=>	'19200'
//		,	1817	=>	'19200',	1834	=>	'19200',	678	=>	'19300',	581	=>	'19300',	2426	=>	'19300',	340	=>	'19300',	2223	=>	'19300',	1792	=>	'19300',	1826	=>	'19300',	2571	=>	'19300',	3465	=>	'19300',	3787	=>	'19300',	621	=>	'19400',	358	=>	'19400',	802	=>	'19400',	1016	=>	'19400',	398	=>	'19500',	2429	=>	'19500',	3313	=>	'19500',	2650	=>	'19500',	3556	=>	'19500',	3797	=>	'19500'
//		,	243	=>	'19600',	2189	=>	'19600',	215	=>	'19600',	1334	=>	'19600',	368	=>	'19600',	341	=>	'19600',	1384	=>	'19600',	1369	=>	'19600',	1367	=>	'19600',	778	=>	'19600',	813	=>	'19600',	2221	=>	'19600',	2548	=>	'19600',	3809	=>	'19600',	4106	=>	'19600',	4168	=>	'19600',	4171	=>	'19600',	4174	=>	'19600',	2527	=>	'19700',	2244	=>	'19700',	2000	=>	'19700',	354	=>	'19700'
//		,	1483	=>	'19700',	55		=>	'19700',	795	=>	'19700',	1482	=>	'19700',	2283	=>	'19700',	2464	=>	'19700',	2700	=>	'19700',	3569	=>	'19700',	4006	=>	'19700',	681	=>	'19800',	2519	=>	'19800',	2514	=>	'19800',	2090	=>	'19800',	2084	=>	'19800',	2044	=>	'19800',	1981	=>	'19800',	1324	=>	'19800',	1437	=>	'19800',	179	=>	'19800',	2512	=>	'19800',	2204	=>	'19800',	2073	=>	'19800'
//		,	1926	=>	'19800',	1779	=>	'19800',	1584	=>	'19800',	2358	=>	'19800',	768	=>	'19800',	3362	=>	'19800',	2524	=>	'19800',	1553	=>	'19800',	1828	=>	'19800',	2262	=>	'19800',	2423	=>	'19800',	2506	=>	'19800',	2695	=>	'19800',	3167	=>	'19800',	3489	=>	'19800',	3546	=>	'19800',	3835	=>	'19800',	3836	=>	'19800',	3837	=>	'19800',	3885	=>	'19800',	3895	=>	'19800',	3909	=>	'19800'
//		,	3957	=>	'19800',	3959	=>	'19800',	3973	=>	'19800',	3980	=>	'19800',	4210	=>	'19800',	239	=>	'19900',	184	=>	'19900',	2307	=>	'19900',	1453	=>	'19900',	2296	=>	'19900',	2697	=>	'19900',	1374	=>	'19900',	1589	=>	'19900',	2604	=>	'19900',	2609	=>	'19900',	3523	=>	'19900',	3816	=>	'19900',	3938	=>	'19900',	4169	=>	'19900',	3282	=>	'20000',	6		=>	'20000',	3269	=>	'20000'
//		,	3256	=>	'20000',	2545	=>	'20000',	3040	=>	'20000',	3060	=>	'20000',	3561	=>	'20000',	3744	=>	'20000',	4086	=>	'20000',	1009	=>	'20100',	165	=>	'20100',	787	=>	'20100',	2480	=>	'20100',	175	=>	'20100',	2930	=>	'20100',	2671	=>	'20100',	642	=>	'20100',	1060	=>	'20100',	2005	=>	'20100',	2675	=>	'20100',	2876	=>	'20100',	3512	=>	'20100',	3565	=>	'20100',	1256	=>	'20200'
//		,	174	=>	'20200',	2458	=>	'20200',	30		=>	'20200',	2460	=>	'20200',	2131	=>	'20200',	2665	=>	'20200',	2666	=>	'20200',	3519	=>	'20200',	3857	=>	'20200',	2186	=>	'20300',	3302	=>	'20300',	3286	=>	'20300',	3083	=>	'20300',	3105	=>	'20300',	4042	=>	'20300',	1232	=>	'20400',	1203	=>	'20400',	1943	=>	'20400',	1387	=>	'20400',	3170	=>	'20400',	592	=>	'20400',	1797	=>	'20400'
//		,	2224	=>	'20400',	2984	=>	'20400',	3623	=>	'20400',	3625	=>	'20400',	3896	=>	'20400',	2309	=>	'20500',	615	=>	'20500',	620	=>	'20500',	377	=>	'20500',	2235	=>	'20500',	334	=>	'20500',	771	=>	'20500',	823	=>	'20500',	1014	=>	'20500',	3882	=>	'20500',	4244	=>	'20500',	4275	=>	'20500',	1413	=>	'20600',	1273	=>	'20600',	1498	=>	'20600',	3271	=>	'20600',	2140	=>	'20600'
//		,	3063	=>	'20600',	3915	=>	'20600',	4117	=>	'20600',	1286	=>	'20700',	348	=>	'20700',	345	=>	'20700',	783	=>	'20700',	1673	=>	'20700',	3681	=>	'20700',	3284	=>	'20800',	3267	=>	'20800',	3241	=>	'20800',	371	=>	'20800',	3431	=>	'20800',	3129	=>	'20800',	2537	=>	'20800',	817	=>	'20800',	837	=>	'20800',	2708	=>	'20800',	2946	=>	'20800',	3059	=>	'20800',	3079	=>	'20800'
//		,	3736	=>	'20800',	750	=>	'20900',	3247	=>	'20900',	1907	=>	'20900',	1278	=>	'20900',	2933	=>	'20900',	2928	=>	'20900',	1056	=>	'20900',	2874	=>	'20900',	2877	=>	'20900',	3029	=>	'20900',	3719	=>	'20900',	4015	=>	'20900',	1992	=>	'21000',	2171	=>	'21000',	385	=>	'21000',	832	=>	'21000',	2459	=>	'21000',	3986	=>	'21000',	2596	=>	'21100',	403	=>	'21100',	198	=>	'21100'
//		,	291	=>	'21100',	514	=>	'21100',	1810	=>	'21100',	603	=>	'21100',	490	=>	'21100',	296	=>	'21100',	267	=>	'21100',	670	=>	'21100',	952	=>	'21100',	1612	=>	'21100',	1630	=>	'21100',	1635	=>	'21100',	1695	=>	'21100',	1759	=>	'21100',	2398	=>	'21100',	2736	=>	'21100',	4193	=>	'21100',	202	=>	'21200',	1946	=>	'21200',	1689	=>	'21200',	1588	=>	'21200',	1022	=>	'21200'
//		,	1563	=>	'21200',	3822	=>	'21200',	3940	=>	'21200',	3981	=>	'21200',	3985	=>	'21200',	651	=>	'21300',	534	=>	'21300',	761	=>	'21300',	1181	=>	'21300',	1230	=>	'21300',	584	=>	'21300',	540	=>	'21300',	997	=>	'21300',	1819	=>	'21300',	1860	=>	'21300',	2114	=>	'21300',	3725	=>	'21300',	4189	=>	'21300',	4205	=>	'21300',	3301	=>	'21400',	1195	=>	'21400',	1960	=>	'21400'
//		,	379	=>	'21400',	826	=>	'21400',	2443	=>	'21400',	3103	=>	'21400',	3589	=>	'21400',	1403	=>	'21500',	1361	=>	'21500',	4164	=>	'21500',	4175	=>	'21500',	176	=>	'21600',	1672	=>	'21600',	652	=>	'21600',	3955	=>	'21600',	3437	=>	'21700',	2530	=>	'21700',	3447	=>	'21700',	1224	=>	'21700',	2740	=>	'21700',	429	=>	'21700',	1709	=>	'21700',	2111	=>	'21700',	2659	=>	'21700'
//		,	2702	=>	'21700',	2779	=>	'21700',	2931	=>	'21700',	3570	=>	'21700',	3279	=>	'21800',	3278	=>	'21800',	1029	=>	'21800',	770	=>	'21800',	3194	=>	'21800',	1057	=>	'21800',	2996	=>	'21800',	3072	=>	'21800',	3073	=>	'21800',	3821	=>	'21800',	2327	=>	'21900',	1275	=>	'21900',	2142	=>	'21900',	2617	=>	'21900',	1179	=>	'22000',	1175	=>	'22000',	1496	=>	'22000',	1338	=>	'22000'
//		,	3655	=>	'22000',	3656	=>	'22000',	3929	=>	'22000',	4119	=>	'22000',	1833	=>	'22100',	3214	=>	'22100',	3007	=>	'22100',	3942	=>	'22100',	247	=>	'22200',	2523	=>	'22200',	159	=>	'22200',	2534	=>	'22200',	2531	=>	'22200',	2520	=>	'22200',	158	=>	'22200',	508	=>	'22200',	1305	=>	'22200',	1774	=>	'22200',	2166	=>	'22200',	2696	=>	'22200',	2698	=>	'22200',	2703	=>	'22200'
//		,	2705	=>	'22200',	3525	=>	'22200',	3529	=>	'22200',	4101	=>	'22200',	757	=>	'22300',	739	=>	'22300',	504	=>	'22300',	1978	=>	'22300',	708	=>	'22300',	740	=>	'22300',	519	=>	'22300',	492	=>	'22300',	776	=>	'22300',	495	=>	'22300',	1760	=>	'22300',	1763	=>	'22300',	1767	=>	'22300',	1776	=>	'22300',	1847	=>	'22300',	1867	=>	'22300',	2449	=>	'22300',	4200	=>	'22300'
//		,	4220	=>	'22300',	4236	=>	'22300',	1971	=>	'22400',	706	=>	'22400',	2960	=>	'22400',	798	=>	'22400',	1047	=>	'22400',	1874	=>	'22400',	2900	=>	'22400',	4000	=>	'22400',	185	=>	'22500',	3290	=>	'22500',	1586	=>	'22500',	1391	=>	'22500',	3537	=>	'22500',	3738	=>	'22500',	3911	=>	'22500',	4162	=>	'22500',	169	=>	'22600',	1988	=>	'22600',	3206	=>	'22600',	3178	=>	'22600'
//		,	637	=>	'22600',	477	=>	'22600',	3197	=>	'22600',	150	=>	'22600',	1024	=>	'22600',	1543	=>	'22600',	1549	=>	'22600',	1744	=>	'22600',	2455	=>	'22600',	2997	=>	'22600',	3002	=>	'22600',	3734	=>	'22600',	3924	=>	'22600',	1250	=>	'22700',	1159	=>	'22700',	163	=>	'22700',	3274	=>	'22700',	2075	=>	'22700',	2502	=>	'22700',	3068	=>	'22700',	3521	=>	'22700',	3635	=>	'22700'
//		,	3657	=>	'22700',	1836	=>	'22800',	69		=>	'22800',	3488	=>	'22800',	3515	=>	'22800',	409	=>	'22900',	1253	=>	'22900',	404	=>	'22900',	1204	=>	'22900',	1326	=>	'22900',	2558	=>	'22900',	857	=>	'22900',	2098	=>	'22900',	2719	=>	'22900',	3543	=>	'22900',	3644	=>	'22900',	3794	=>	'22900',	97		=>	'23000',	1622	=>	'23000',	580	=>	'23000',	2330	=>	'23000',	1272	=>	'23100'
//		,	410	=>	'23100',	388	=>	'23100',	402	=>	'23100',	842	=>	'23100',	852	=>	'23100',	1042	=>	'23100',	3586	=>	'23100',	3806	=>	'23100',	690	=>	'23300',	548	=>	'23300',	1897	=>	'23300',	74		=>	'23300',	565	=>	'23300',	1781	=>	'23300',	2415	=>	'23300',	4237	=>	'23300',	1410	=>	'23400',	1393	=>	'23400',	577	=>	'23400',	372	=>	'23400',	3238	=>	'23400',	1307	=>	'23400'
//		,	1363	=>	'23400',	1359	=>	'23400',	992	=>	'23400',	2170	=>	'23400',	2231	=>	'23400',	3020	=>	'23400',	3795	=>	'23400',	4166	=>	'23400',	4178	=>	'23400',	4179	=>	'23400',	1012	=>	'23500',	1566	=>	'23500',	1154	=>	'23500',	586	=>	'23500',	3401	=>	'23500',	1424	=>	'23500',	999	=>	'23500',	2007	=>	'23500',	2067	=>	'23500',	2314	=>	'23500',	3495	=>	'23500',	3574	=>	'23500'
//		,	376	=>	'23600',	249	=>	'23600',	506	=>	'23600',	3304	=>	'23600',	599	=>	'23600',	723	=>	'23600',	2446	=>	'23600',	3307	=>	'23600',	1049	=>	'23600',	1685	=>	'23600',	1770	=>	'23600',	2661	=>	'23600',	3108	=>	'23600',	3114	=>	'23600',	4103	=>	'23600',	4228	=>	'23600',	692	=>	'23700',	746	=>	'23700',	1380	=>	'23700',	357	=>	'23700',	1451	=>	'23700',	331	=>	'23700'
//		,	769	=>	'23700',	800	=>	'23700',	1832	=>	'23700',	1852	=>	'23700',	2274	=>	'23700',	4163	=>	'23700',	1601	=>	'23800',	3253	=>	'23800',	2172	=>	'23800',	1905	=>	'23800',	2417	=>	'23800',	3743	=>	'23800',	3883	=>	'23800',	4003	=>	'23800',	252	=>	'24000',	1328	=>	'24000',	1600	=>	'24000',	3545	=>	'24000',	1133	=>	'24100',	1306	=>	'24100',	2168	=>	'24100',	3695	=>	'24100'
//		,	2053	=>	'24200',	3293	=>	'24200',	3109	=>	'24200',	1001	=>	'24200',	2001	=>	'24200',	2491	=>	'24200',	2932	=>	'24200',	3091	=>	'24200',	1178	=>	'24300',	2540	=>	'24300',	1418	=>	'24300',	3250	=>	'24300',	1394	=>	'24300',	2247	=>	'24300',	2710	=>	'24300',	3034	=>	'24300',	3652	=>	'24300',	4147	=>	'24300',	194	=>	'24400',	3217	=>	'24400',	1115	=>	'24400',	3008	=>	'24400'
//		,	3538	=>	'24400',	3631	=>	'24400',	3163	=>	'24600',	1300	=>	'24600',	2982	=>	'24600',	3674	=>	'24600',	607	=>	'24700',	608	=>	'24700',	801	=>	'24700',	2803	=>	'24700',	1006	=>	'24700',	1008	=>	'24700',	1064	=>	'24700',	2806	=>	'24700',	3439	=>	'24800',	290	=>	'24800',	266	=>	'24800',	1455	=>	'24800',	967	=>	'24800',	957	=>	'24800',	1814	=>	'24800',	3435	=>	'24800'
//		,	3432	=>	'24800',	1488	=>	'24800',	1164	=>	'24800',	980	=>	'24800',	699	=>	'24800',	1059	=>	'24800',	1629	=>	'24800',	1968	=>	'24800',	2071	=>	'24800',	2278	=>	'24800',	2644	=>	'24800',	2764	=>	'24800',	3454	=>	'24800',	3474	=>	'24800',	3475	=>	'24800',	3855	=>	'24800',	4091	=>	'24800',	397	=>	'24900',	594	=>	'24900',	1023	=>	'24900',	848	=>	'24900',	3824	=>	'24900'
//		,	4195	=>	'24900',	1761	=>	'25100',	2258	=>	'25100',	1197	=>	'25100',	2225	=>	'25100',	1839	=>	'25100',	2397	=>	'25100',	2640	=>	'25100',	3621	=>	'25100',	3884	=>	'25100',	3966	=>	'25100',	3968	=>	'25100',	3982	=>	'25100',	248	=>	'25200',	1327	=>	'25200',	1356	=>	'25200',	1390	=>	'25200',	3248	=>	'25200',	1596	=>	'25200',	2228	=>	'25200',	3031	=>	'25200',	3542	=>	'25200'
//		,	4114	=>	'25200',	3208	=>	'25300',	1422	=>	'25300',	1365	=>	'25300',	3004	=>	'25300',	4141	=>	'25300',	4151	=>	'25300',	446	=>	'25400',	445	=>	'25400',	441	=>	'25400',	437	=>	'25400',	433	=>	'25400',	1330	=>	'25400',	33		=>	'25400',	885	=>	'25400',	893	=>	'25400',	895	=>	'25400',	1473	=>	'25400',	1714	=>	'25400',	1720	=>	'25400',	3544	=>	'25400',	686	=>	'25500'
//		,	1258	=>	'25500',	1368	=>	'25500',	1043	=>	'25500',	2211	=>	'25500',	3647	=>	'25500',	1619	=>	'25600',	1438	=>	'25600',	2264	=>	'25600',	3925	=>	'25600',	1560	=>	'25700',	330	=>	'25700',	127	=>	'25700',	767	=>	'25700',	1520	=>	'25700',	2310	=>	'25700',	242	=>	'25800',	2436	=>	'25800',	748	=>	'25800',	3444	=>	'25800',	1592	=>	'25800',	2656	=>	'25800',	4266	=>	'25800'
//		,	961	=>	'25900',	112	=>	'25900',	2699	=>	'25900',	353	=>	'25900',	793	=>	'25900',	1965	=>	'25900',	2754	=>	'25900',	4263	=>	'25900',	383	=>	'26000',	759	=>	'26000',	467	=>	'26000',	1536	=>	'26000',	542	=>	'26000',	3402	=>	'26000',	128	=>	'26000',	475	=>	'26000',	830	=>	'26000',	970	=>	'26000',	1522	=>	'26000',	1743	=>	'26000',	1858	=>	'26000',	2300	=>	'26000'
//		,	3858	=>	'26000',	4252	=>	'26000',	460	=>	'26100',	362	=>	'26100',	3246	=>	'26100',	3251	=>	'26100',	1733	=>	'26100',	3027	=>	'26100',	3035	=>	'26100',	3789	=>	'26100',	1180	=>	'26200',	2419	=>	'26200',	1167	=>	'26200',	2074	=>	'26200',	3642	=>	'26200',	3941	=>	'26200',	75		=>	'26400',	781	=>	'26400',	1429	=>	'26400',	3299	=>	'26400',	1435	=>	'26400',	567	=>	'26400'
//		,	3099	=>	'26400',	3468	=>	'26400',	3479	=>	'26400',	4188	=>	'26400',	1667	=>	'26500',	1339	=>	'26500',	2356	=>	'26500',	2255	=>	'26500',	1844	=>	'26500',	1829	=>	'26500',	1702	=>	'26500',	1570	=>	'26500',	2143	=>	'26500',	2315	=>	'26500',	2362	=>	'26500',	2405	=>	'26500',	2587	=>	'26500',	2627	=>	'26500',	2643	=>	'26500',	3480	=>	'26500',	3891	=>	'26500',	3954	=>	'26500'
//		,	4120	=>	'26500',	1340	=>	'26600',	1627	=>	'26600',	2333	=>	'26600',	4116	=>	'26600',	755	=>	'26700',	2298	=>	'26700',	3257	=>	'26700',	400	=>	'26700',	1292	=>	'26700',	1125	=>	'26700',	1401	=>	'26700',	395	=>	'26700',	1690	=>	'26700',	1856	=>	'26700',	2157	=>	'26700',	2236	=>	'26700',	2606	=>	'26700',	3042	=>	'26700',	3626	=>	'26700',	3788	=>	'26700',	560	=>	'27000'
//		,	1789	=>	'27000',	3988	=>	'27000',	4186	=>	'27000',	1677	=>	'27100',	1038	=>	'27100',	3928	=>	'27100',	295	=>	'27200',	279	=>	'27200',	1409	=>	'27200',	1347	=>	'27200',	301	=>	'27200',	715	=>	'27200',	734	=>	'27200',	1641	=>	'27200',	4121	=>	'27200',	4132	=>	'27200',	1366	=>	'27300',	552	=>	'27300',	564	=>	'27300',	482	=>	'27300',	474	=>	'27300',	154	=>	'27300'
//		,	485	=>	'27300',	253	=>	'27300',	1331	=>	'27300',	1741	=>	'27300',	1752	=>	'27300',	1757	=>	'27300',	1784	=>	'27300',	2188	=>	'27300',	2209	=>	'27300',	3517	=>	'27300',	4097	=>	'27300',	4204	=>	'27300',	3280	=>	'27600',	536	=>	'27600',	78		=>	'27600',	1364	=>	'27600',	966	=>	'27600',	3075	=>	'27600',	3606	=>	'27600',	4130	=>	'27600',	2342	=>	'27800',	497	=>	'27800'
//		,	3272	=>	'27800',	1389	=>	'27800',	2226	=>	'27800',	3065	=>	'27800',	4045	=>	'27800',	4206	=>	'27800',	2047	=>	'27900',	2725	=>	'27900',	1066	=>	'27900',	2488	=>	'27900',	2770	=>	'27900',	3637	=>	'27900',	3370	=>	'28000',	3369	=>	'28000',	3177	=>	'28000',	3179	=>	'28000',	1360	=>	'28100',	1377	=>	'28100',	1419	=>	'28100',	1407	=>	'28100',	1358	=>	'28100',	2248	=>	'28100'
//		,	4115	=>	'28100',	4124	=>	'28100',	4140	=>	'28100',	4148	=>	'28100',	1921	=>	'28400',	742	=>	'28400',	1803	=>	'28400',	342	=>	'28400',	1670	=>	'28400',	1849	=>	'28400',	2422	=>	'28400',	3932	=>	'28400',	2541	=>	'28500',	2536	=>	'28500',	1007	=>	'28500',	281	=>	'28500',	718	=>	'28500',	2707	=>	'28500',	2712	=>	'28500',	3868	=>	'28500',	2207	=>	'28600',	1176	=>	'28600'
//		,	2559	=>	'28600',	3682	=>	'28600',	3222	=>	'28700',	3268	=>	'28700',	3235	=>	'28700',	3011	=>	'28700',	3745	=>	'28700',	3750	=>	'28700',	80		=>	'29100',	2517	=>	'29100',	2693	=>	'29100',	4088	=>	'29100',	716	=>	'29300',	1348	=>	'29300',	1841	=>	'29300',	4149	=>	'29300',	399	=>	'29600',	491	=>	'29600',	850	=>	'29600',	4229	=>	'29600',	976	=>	'29700',	974	=>	'29700'
//		,	978	=>	'29700',	955	=>	'29700',	392	=>	'29700',	969	=>	'29700',	3549	=>	'29700',	3799	=>	'29700',	3856	=>	'29700',	4092	=>	'29700',	4093	=>	'29700',	4094	=>	'29700',	2031	=>	'29800',	23		=>	'29800',	1336	=>	'29800',	1465	=>	'29800',	3919	=>	'29800',	4133	=>	'29800',	173	=>	'30000',	167	=>	'30000',	2102	=>	'30000',	1395	=>	'30000',	2450	=>	'30000',	1349	=>	'30000'
//		,	644	=>	'30000',	2662	=>	'30000',	3534	=>	'30000',	3871	=>	'30000',	4122	=>	'30000',	4135	=>	'30000',	473	=>	'30200',	1800	=>	'30200',	1740	=>	'30200',	3487	=>	'30200',	389	=>	'30700',	2826	=>	'30700',	2820	=>	'30700',	2818	=>	'30700',	3263	=>	'30700',	2812	=>	'30700',	2814	=>	'30700',	2817	=>	'30700',	3051	=>	'30700',	3793	=>	'30700',	1557	=>	'30800',	1129	=>	'30800'
//		,	3660	=>	'30800',	3944	=>	'30800',	3407	=>	'30900',	544	=>	'30900',	1406	=>	'30900',	3842	=>	'30900',	4137	=>	'30900',	4232	=>	'30900',	2507	=>	'31000',	562	=>	'31000',	307	=>	'31000',	1400	=>	'31000',	982	=>	'31000',	1647	=>	'31000',	2686	=>	'31000',	2688	=>	'31000',	4134	=>	'31000',	1651	=>	'31600',	2290	=>	'31600',	3368	=>	'31600',	2344	=>	'31600',	2601	=>	'31600'
//		,	3175	=>	'31600',	1379	=>	'31800',	1388	=>	'31800',	4152	=>	'31800',	4161	=>	'31800',	2050	=>	'31900',	2510	=>	'31900',	2689	=>	'31900',	3939	=>	'31900',	2070	=>	'32000',	684	=>	'32000',	2500	=>	'32000',	4240	=>	'32000',	1721	=>	'32100',	390	=>	'32100',	728	=>	'32100',	2227	=>	'32100',	754	=>	'32100',	1184	=>	'32100',	1177	=>	'32100',	2838	=>	'32100',	1182	=>	'32100'
//		,	703	=>	'32100',	2438	=>	'32100',	2375	=>	'32100',	1052	=>	'32100',	1837	=>	'32100',	1855	=>	'32100',	2080	=>	'32100',	2083	=>	'32100',	2085	=>	'32100',	2573	=>	'32100',	2657	=>	'32100',	2692	=>	'32100',	3482	=>	'32100',	3782	=>	'32100',	3890	=>	'32100',	276	=>	'32200',	2002	=>	'32200',	479	=>	'32200',	710	=>	'32200',	1746	=>	'32200',	2465	=>	'32200',	386	=>	'32300'
//		,	172	=>	'32300',	649	=>	'32300',	3804	=>	'32300',	333	=>	'32500',	2177	=>	'32500',	1357	=>	'32500',	1665	=>	'32500',	2205	=>	'32500',	3471	=>	'32500',	3275	=>	'32600',	1293	=>	'32600',	3070	=>	'32600',	3661	=>	'32600',	2243	=>	'33100',	2400	=>	'33100',	3956	=>	'33100',	3972	=>	'33100',	1890	=>	'33400',	277	=>	'33400',	292	=>	'33400',	712	=>	'33400',	1632	=>	'33400'
//		,	2413	=>	'33400',	3434	=>	'33500',	3433	=>	'33500',	2183	=>	'33500',	2457	=>	'33500',	387	=>	'33700',	1397	=>	'33700',	1386	=>	'33700',	207	=>	'33700',	1163	=>	'33700',	671	=>	'33700',	840	=>	'33700',	2234	=>	'33700',	4155	=>	'33700',	258	=>	'33900',	350	=>	'33900',	1604	=>	'33900',	3781	=>	'33900',	1189	=>	'34200',	1166	=>	'34200',	488	=>	'34200',	3636	=>	'34200'
//		,	3638	=>	'34200',	4231	=>	'34200',	380	=>	'34600',	1370	=>	'34600',	2213	=>	'34600',	3796	=>	'34600',	1562	=>	'34700',	244	=>	'34700',	505	=>	'34700',	1769	=>	'34700',	2193	=>	'34700',	2311	=>	'34700',	4102	=>	'34700',	501	=>	'34800',	3190	=>	'34800',	2993	=>	'34800',	4217	=>	'34800',	2687	=>	'34900',	3562	=>	'34900',	3564	=>	'34900',	1642	=>	'35300',	241	=>	'35300'
//		,	869	=>	'35300',	1073	=>	'35300',	1591	=>	'35300',	2339	=>	'35300',	370	=>	'35400',	1267	=>	'35400',	3648	=>	'35400',	3802	=>	'35400',	2284	=>	'35700',	2008	=>	'35700',	1355	=>	'35700',	3960	=>	'35700',	3983	=>	'35700',	4144	=>	'35700',	2313	=>	'35900',	639	=>	'35900',	206	=>	'36100',	1143	=>	'36100',	3516	=>	'36100',	3717	=>	'36100',	2163	=>	'37000',	2046	=>	'37000'
//		,	2040	=>	'37000',	3961	=>	'37000',	3964	=>	'37000',	3978	=>	'37000',	1420	=>	'37100',	2607	=>	'37100',	1531	=>	'37100',	1141	=>	'37100',	1392	=>	'37100',	2611	=>	'37100',	2230	=>	'37100',	2250	=>	'37100',	2297	=>	'37100',	2743	=>	'37100',	2745	=>	'37100',	3585	=>	'37100',	1414	=>	'37400',	1404	=>	'37400',	1350	=>	'37400',	2241	=>	'37400',	4125	=>	'37400',	4165	=>	'37400'
//		,	1990	=>	'37900',	617	=>	'37900',	1804	=>	'37900',	3874	=>	'37900',	3240	=>	'39100',	195	=>	'39100',	3259	=>	'39100',	1561	=>	'39100',	3021	=>	'39100',	3046	=>	'39100',	656	=>	'39200',	1697	=>	'39200',	1820	=>	'39200',	2359	=>	'39200',	1412	=>	'39300',	1417	=>	'39300',	4131	=>	'39300',	4177	=>	'39300',	1341	=>	'39600',	3361	=>	'39600',	484	=>	'39600',	2936	=>	'39600'
//		,	1755	=>	'39600',	2879	=>	'39600',	3165	=>	'39600',	4146	=>	'39600',	236	=>	'40600',	4112	=>	'40600',	4213	=>	'40600',	2878	=>	'40900',	483	=>	'40900',	1753	=>	'40900',	3552	=>	'40900',	2265	=>	'41000',	192	=>	'41000',	665	=>	'41000',	3962	=>	'41000',	1617	=>	'41400',	246	=>	'41400',	1594	=>	'41400',	2328	=>	'41400',	115	=>	'41500',	111	=>	'41500',	596	=>	'41500'
//		,	4262	=>	'41500',	406	=>	'42000',	309	=>	'42000',	744	=>	'42000',	3798	=>	'42000',	3306	=>	'43400',	1793	=>	'43400',	2392	=>	'43400',	3112	=>	'43400',	382	=>	'43900',	1371	=>	'43900',	828	=>	'43900',	4160	=>	'43900',	130	=>	'44500',	971	=>	'44500',	963	=>	'44500',	129	=>	'44500',	1523	=>	'44500',	1966	=>	'44500',	1969	=>	'44500',	4248	=>	'44500',	1827	=>	'44600'
//		,	601	=>	'44600',	2448	=>	'44600',	1801	=>	'44600',	3861	=>	'44600',	3897	=>	'44600',	1373	=>	'45200',	3264	=>	'45200',	3052	=>	'45200',	4158	=>	'45200',	160	=>	'45300',	182	=>	'45300',	1555	=>	'45300',	3540	=>	'45300',	408	=>	'45700',	3289	=>	'45700',	803	=>	'45700',	1606	=>	'45700',	1877	=>	'45700',	3481	=>	'45700',	3747	=>	'45700',	1353	=>	'45800',	1146	=>	'45800'
//		,	3619	=>	'45800',	4139	=>	'45800',	2245	=>	'46300',	459	=>	'46300',	3847	=>	'46300',	3949	=>	'46300',	3445	=>	'46500',	3283	=>	'46500',	2919	=>	'46500',	3078	=>	'46500',	721	=>	'47100',	934	=>	'47100',	1843	=>	'47100',	3450	=>	'47100',	343	=>	'48700',	1383	=>	'48700',	1378	=>	'48700',	1346	=>	'48700',	1671	=>	'48700',	2219	=>	'48700',	4143	=>	'48700',	4157	=>	'48700'
//		,	134	=>	'49300',	131	=>	'49300',	135	=>	'49300',	1525	=>	'49300',	1529	=>	'49300',	3575	=>	'49300',	618	=>	'53700',	384	=>	'53700',	1687	=>	'53700',	1806	=>	'53700',	960	=>	'54400',	3292	=>	'54400',	981	=>	'54400',	948	=>	'54400',	1958	=>	'54400',	1964	=>	'54400',	3090	=>	'54400',	4083	=>	'54400',	109	=>	'61000',	105	=>	'61000',	103	=>	'61000',	102	=>	'61000'
//		,	585	=>	'61000',	587	=>	'61000',	590	=>	'61000',	593	=>	'61000',	945	=>	'64000',	941	=>	'64000',	942	=>	'64000',	3451	=>	'64000',	3550	=>	'64000',	3854	=>	'64000',	201	=>	'65100',	116	=>	'65100',	598	=>	'65100',	3510	=>	'65100',	138	=>	'69000',	140	=>	'69000',	133	=>	'69000',	139	=>	'69000',	1533	=>	'69000',	1535	=>	'69000',	1537	=>	'69000',	3576	=>	'69000'
//		,	2489	=>	'69500',	3391	=>	'69500',	106	=>	'107600',	101	=>	'107600',	1510	=>	'107600',	1513	=>	'107600',	932	=>	'1500',	3418	=>	'1700',	2709	=>	'1900',	4070	=>	'3200',	2057	=>	'3200',	3807	=>	'4600',	28		=>	'4600',	4241	=>	'9400',	694	=>	'9400',	1765	=>	'12500',	496	=>	'12500',	3953	=>	'12900',	1485	=>	'12900',	2270	=>	'18800',	1445	=>	'18800'
//		,	4212	=>	'24500',	641	=>	'24500',	4207	=>	'26300',	691	=>	'26300',	3914	=>	'27700',	1940	=>	'27700',	3783	=>	'28800',	381	=>	'28800',	1868	=>	'29900',	777	=>	'29900',	2016	=>	'30100',	1030	=>	'30100',	1666	=>	'30300',	336	=>	'30300',	2069	=>	'30400',	1162	=>	'30400',	1799	=>	'30500',	595	=>	'30500',	2077	=>	'30600',	1170	=>	'30600',	3653	=>	'31200',	1239	=>	'31200'
//		,	3023	=>	'31300',	3243	=>	'31300',	947	=>	'32400',	511	=>	'32400',	2513	=>	'32700',	2107	=>	'32700',	1840	=>	'32900',	713	=>	'32900',	4154	=>	'33000',	1344	=>	'33000',	1678	=>	'33300',	356	=>	'33300',	3183	=>	'33600',	3374	=>	'33600',	4253	=>	'33800',	983	=>	'33800',	775	=>	'34000',	338	=>	'34000',	2304	=>	'34100',	1548	=>	'34100',	4331	=>	'34400',	3801	=>	'34500'
//		,	405	=>	'34500',	1824	=>	'35000',	672	=>	'35000',	1693	=>	'35100',	401	=>	'35100',	4194	=>	'35500',	648	=>	'35500',	4109	=>	'35800',	254	=>	'35800',	2612	=>	'35900',	3920	=>	'36300',	2301	=>	'36300',	3032	=>	'36500',	3249	=>	'36500',	3853	=>	'36600',	2560	=>	'36600',	4245	=>	'37200',	614	=>	'37200',	2676	=>	'37600',	2483	=>	'37600',	2585	=>	'37700',	2253	=>	'37700'
//		,	3010	=>	'38300',	3220	=>	'38300',	3509	=>	'38700',	161	=>	'38700',	4159	=>	'39400',	1335	=>	'39400',	3846	=>	'39500',	77		=>	'39500',	3913	=>	'39700',	1775	=>	'39700',	2626	=>	'39800',	2354	=>	'39800',	2592	=>	'40000',	2275	=>	'40000',	4260	=>	'40100',	113	=>	'40100',	4224	=>	'40300',	731	=>	'40300',	3671	=>	'40500',	1229	=>	'40500',	1585	=>	'40800',	231	=>	'40800'
//		,	4142	=>	'41200',	1381	=>	'41200',	2973	=>	'41700',	3143	=>	'41700',	3044	=>	'41800',	3258	=>	'41800',	4180	=>	'42100',	1405	=>	'42100',	3098	=>	'42700',	3298	=>	'42700',	4150	=>	'43100',	1399	=>	'43100',	1870	=>	'43200',	782	=>	'43200',	3670	=>	'43300',	1193	=>	'43300',	3873	=>	'43600',	1546	=>	'43600',	1040	=>	'43800',	676	=>	'43800',	3539	=>	'44100',	156	=>	'44100'
//		,	4123	=>	'44700',	1345	=>	'44700',	3533	=>	'45000',	183	=>	'45000',	3563	=>	'45100',	2690	=>	'45100',	1010	=>	'45400',	610	=>	'45400',	3912	=>	'46200',	2303	=>	'46200',	2191	=>	'46400',	1333	=>	'46400',	845	=>	'46600',	393	=>	'46600',	2238	=>	'47000',	1402	=>	'47000',	1668	=>	'47800',	339	=>	'47800',	3742	=>	'47900',	3296	=>	'47900',	4214	=>	'48400',	478	=>	'48400'
//		,	4153	=>	'48800',	1398	=>	'48800',	3685	=>	'49100',	1155	=>	'49100',	3566	=>	'49200',	2691	=>	'49200',	3784	=>	'49500',	378	=>	'49500',	2386	=>	'53100',	1780	=>	'53100',	4095	=>	'53400',	2485	=>	'53400',	3038	=>	'55100',	3254	=>	'55100',	1530	=>	'56300',	136	=>	'56300',	4104	=>	'57000',	240	=>	'57000',	2222	=>	'58200',	1385	=>	'58200',	3081	=>	'58500',	3285	=>	'58500'
//		,	2364	=>	'59500',	1704	=>	'59500',	2388	=>	'59700',	1785	=>	'59700',	1961	=>	'60500',	951	=>	'60500',	3965	=>	'60900',	2141	=>	'60900',	1532	=>	'61500',	137	=>	'61500',	1658	=>	'62000',	312	=>	'62000',	3128	=>	'62300',	3446	=>	'62300',	3551	=>	'62700',	946	=>	'62700',	3662	=>	'63200',	1295	=>	'63200',	3800	=>	'63500',	365	=>	'63500',	2190	=>	'63700',	1332	=>	'63700'
//		,	1519	=>	'64300',	114	=>	'64300',	110	=>	'64800',	4261	=>	'65300',	4145	=>	'66400',	1352	=>	'66400',	1526	=>	'67200',	132	=>	'67200',	1516	=>	'67300',	108	=>	'67300',	3805	=>	'67400',	361	=>	'67400',	2197	=>	'68100',	1343	=>	'68100',	4156	=>	'69200',	1423	=>	'69200',	2441	=>	'69300',	1948	=>	'69300',	2678	=>	'69500',	804	=>	'70000',	360	=>	'70000',	1660	=>	'70600'
//		,	313	=>	'70600',	1514	=>	'72300',	107	=>	'72300',	1511	=>	'73200',	104	=>	'73200',	853	=>	'74100',	2525	=>	'74400',	2133	=>	'74400',	4136	=>	'76300',	1411	=>	'76300',	3740	=>	'77500',	3277	=>	'77500',	3926	=>	'83500',	1768	=>	'83500',	1701	=>	'89600',	420	=>	'89600',	3950	=>	'96300',	1892	=>	'96300',	1956	=>	'101700',	944	=>	'101700',	3951	=>	'111000'
//		,	1980	=>	'111000',	2366	=>	'164600',	1715	=>	'164600'
//		);
//
//		$update_arr	= array();
//		foreach($temp_arr as $uid => $pri){
//			if(element($pri,$update_arr,false) === false){
//				$update_arr[$pri]	= array();
//			}
//			$update_arr[$pri][]	= $uid;
//		}
//		$n	= 1;
//		foreach($update_arr as $price => $uids){
//			$this->channel_item_info_model->updateChannelItemInfo(array('upload_price'=>$price), array('item_info_id_in'=>$uids));
//			echo $n.' :: '.$price.PHP_EOL;
////			var_dump($uids);
////			return;
//			$n++;
//		}
//
//	}
}
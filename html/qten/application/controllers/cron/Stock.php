<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오전 12:09
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/channel_item_info_model');
        $this->load->model('item/channel_option_item_info_model');
        $this->load->model('item/clearance_ban_item_model');
        $this->load->model('item/master_item_model');
        $this->load->model('item/soldout_exclude_item_model');
        $this->load->model('item/virtual_item_model');
        $this->load->model('order/order_model');
        $this->load->model('order/order_item_model');
        $this->load->model('stock/master_item_stock_model');
        $this->load->model('stock/stock_history_model');
    }

	public function testStock(){
        echo date('Y-m-d H:i:s');
	}

    public function test_ksj(){
        $channel_info_arr = array();

        $channel_info_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            $channel_info_arr[element('channel_id', $channel_info_data)] = $channel_info_data;
        }

        $order_item_filter = array(
            'status' => 5,
            'stock_flag' => 1,
            'join_virtual_item_detail' => true,
            'cancel_flag' =>0
        );

        $order_id_arr = array();

        $master_item_arr = array();

        $order_item_result = $this->order_item_model->getOrderItems($order_item_filter, 'i.*, o.channel_id, o.order_date, v.master_item_id, v.quantity as vquantity');

        foreach ($order_item_result->result_array() as $order_item) {

            if (element('master_item_id', $order_item, '') == '') continue;

            if (!in_array(element('order_id', $order_item), $order_id_arr)) array_push($order_id_arr, element('order_id', $order_item));

            if (!array_key_exists(element('master_item_id', $order_item), $master_item_arr)) {
                $master_item_info = $this->master_item_model->getMasterItem(array('master_item_id' => element('master_item_id', $order_item)), 'master_item_id, upc');
                $master_item_arr[element('master_item_id', $master_item_info)] = trim(element('upc', $master_item_info));

            }

             $current_channel = element(element('channel_id', $order_item), $channel_info_arr);

            echo strtolower(element('channel_code', $current_channel))."<br>";
        }

    }

    public function apply_stock()
    {

        $channel_info_arr = array();

        $channel_info_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            $channel_info_arr[element('channel_id', $channel_info_data)] = $channel_info_data;
        }

        $order_item_filter = array(
            'status' => 5,
            'stock_flag' => 0,
            'join_virtual_item_detail' => true,
            'cancel_flag' =>0
        );

        $order_id_arr = array();

        $master_item_arr = array();

        $order_item_result = $this->order_item_model->getOrderItems($order_item_filter, 'i.*, o.channel_id, o.order_date, v.master_item_id, v.quantity as vquantity');

        foreach ($order_item_result->result_array() as $order_item){

            if(element('master_item_id', $order_item, '') == '') continue;

            if(!in_array(element('order_id', $order_item), $order_id_arr)) array_push($order_id_arr, element('order_id', $order_item));

            if(!array_key_exists(element('master_item_id', $order_item), $master_item_arr)){
                $master_item_info = $this->master_item_model->getMasterItem(array('master_item_id' => element('master_item_id', $order_item)), 'master_item_id, upc');
                $master_item_arr[element('master_item_id', $master_item_info)]= trim(element('upc', $master_item_info));

            }

            $current_channel = element(element('channel_id', $order_item), $channel_info_arr);

            $upc = element(element('master_item_id', $order_item), $master_item_arr);
            $qty = element('qty', $order_item) * element('vquantity', $order_item);

            $this->master_item_model->updateStockData($qty * -1, array('upc' => $upc));

            $master_item_info = $this->master_item_model->getMasterItem(array('upc' => $upc), 'currentqty');

            $history_data = array(
                'channel' => "qten". strtolower(element('channel_code', $current_channel)),
                'upc' => (string)$upc,
                'sales_qty' => $qty,
                'ntics_qty' => element('currentqty', $master_item_info),
                'dt' =>  date('YmdHis'),
                'ct_id' => element('order_item_id', $order_item),
                'it_id' => element('channel_product_no', $order_item),
                'od_id' => element('order_id', $order_item),
                'od_time' => preg_replace("/[^0-9]*/s", '', element('order_date', $order_item))
            );
            //print_r($history_data);
            $this->stock_history_model->addStockHistory($history_data);

        }

        if(count($order_id_arr) > 0){
            $this->order_model->updateOrder(array('stock_flag' => 1), array('order_id_in' => $order_id_arr));
        }
        echo 'done!' . PHP_EOL;

        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(37);

        $rest = $history_api->sendHistoryID();

    }

    public function test(){

        $international_channel_info_arr = array();
        $domestic_channel_info_arr = array();

        //$channel_info_result = $this->channel_info_model->getChannelInfos(array()); use_flag =0 인 채널까지 다 가져와서 옵션상품도 재고동기화하기 20190328 KSJ
        $channel_info_result = $this->channel_info_model->getnewChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            if(element('account_type', $channel_info_data) == 1)
                array_push($international_channel_info_arr, element('channel_id', $channel_info_data));
            else
                array_push($domestic_channel_info_arr, element('channel_id', $channel_info_data));
        }
        $item_info_id_arr = array();
        $soldout_item_result = $this->master_item_stock_model->getItemStockInfo(
            array(
                'channel_id_in' => $international_channel_info_arr,
                'stock_status' => 'Y',
                /*  'currentqty_lower' => 5,
                  'exclude_account_type_bit_lower' => 1,*/
//                  'exclude_account_type_bit_soldout'=>array('4','1','2'), /*해외품절,해외판매중,재고*/
                'exclude_account_type_bit_soldout'=>array('1','4','2'), /*해외판매,해외품절,재고*/
                'restrict_customer_clearance_in' => array(0,2)));

        $item_info_id_arr = array();

        $instock_item_result = $this->master_item_stock_model->getItemStockInfo(
            array(
                'channel_id_in' => $international_channel_info_arr,
                'stock_status' => 'N',
                /*  'currentqty_upper' => 5,
                  'exclude_account_type_bit_lower' => 1,*/
                'exclude_account_type_bit_insotck'=>array('1','4','2'), /*해외판매,해외품절,재고*/
                'restrict_customer_clearance_in' => array(0,2)));

        $instock_item_info_id_arr = array_chunk($item_info_id_arr, 100);

    }

    public function check_channel_sales_item_stock()
    {
        if(date('YmdH') > '2018102600' && date('YmdH') < '2018102609') return;

        $international_channel_info_arr = array();
        $domestic_channel_info_arr = array();

        //$channel_info_result = $this->channel_info_model->getChannelInfos(array()); use_flag =0 인 채널까지 다 가져와서 옵션상품도 재고동기화하기 20190328 KSJ
        $channel_info_result = $this->channel_info_model->getnewChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            if(element('account_type', $channel_info_data) == 1)
                array_push($international_channel_info_arr, element('channel_id', $channel_info_data));
            else
                array_push($domestic_channel_info_arr, element('channel_id', $channel_info_data));
        }

        $master_item_infos = array();

        $master_item_result = $this->channel_item_info_model->getMasterItems(array(), 'd.master_item_id', 'd.master_item_id');

        foreach ($master_item_result->result_array() as $master_item_data){
            array_push($master_item_infos, element('master_item_id', $master_item_data));
        }

        $option_master_item_result = $this->channel_option_item_info_model->getMasterItems(array('regist_fg'=>'2'), 'd.master_item_id', 'd.master_item_id');
        foreach ($option_master_item_result->result_array() as $master_item_data){
            if(!in_array(element('master_item_id', $master_item_data),$master_item_infos)) array_push($master_item_infos, element('master_item_id', $master_item_data));
        }

        $this->master_item_stock_model->empty();

        $master_item_id_arr = array_chunk($master_item_infos, 100);

        foreach ($master_item_id_arr as $master_item_ids){

            $master_item_stock_data = array();

            $master_item_stock_result = $this->master_item_model->getMasterItems2(array('master_item_id_in' => $master_item_ids), 'master_item_id, currentqty');

            foreach ($master_item_stock_result->result_array() as $master_item_stock){
                array_push($master_item_stock_data, $master_item_stock);
            }

            if(count($master_item_stock_data) > 0)
                $this->master_item_stock_model->insertBulk($master_item_stock_data);

        }

        //해외판매1, 국내판매2 해외품절4, 국내품절8

        $exclude_item_result = $this->soldout_exclude_item_model->getSoldoutExcludeItemSums();

        foreach ($exclude_item_result->result_array() as $exclude_item_data){
            $this->master_item_stock_model->update(
                array(
                    'exclude_account_type' => element('sum_account_type', $exclude_item_data, 0)
                ),
                array(
                    'master_item_id' => element('master_item_id', $exclude_item_data, 0)
                )
            );
        }

        /*
         *
         * 일본 사업자의 경우 웹 NTICS 통관불가 상품은 참조하지 않음 - 20200121
         *
         *
        $clearace_ban_restrict_item_arr = array();
        $clearace_ban_hold_item_arr = array();

        $clearace_item_result = $this->clearance_ban_item_model->getClearanceBanMasterItemId();

        foreach ($clearace_item_result->result_array() as $clearace_item_data){

            if(element('fg', $clearace_item_data,0) > 1)
                array_push($clearace_ban_hold_item_arr, element('master_item_id', $clearace_item_data));
            else
                array_push($clearace_ban_restrict_item_arr, element('master_item_id', $clearace_item_data));

        }

        if(count($clearace_ban_restrict_item_arr) > 0){
            $this->master_item_stock_model->update(
                array(
                    'restrict_customer_clearance' => 1
                ),
                array(
                    'master_item_id_in' => $clearace_ban_restrict_item_arr
                )
            );
        }
        if(count($clearace_ban_hold_item_arr) > 0){
            $this->master_item_stock_model->update(
                array(
                    'restrict_customer_clearance' => 2
                ),
                array(
                    'master_item_id_in' => $clearace_ban_hold_item_arr
                )
            );
        }*/

        if(count($international_channel_info_arr) > 0) {

            ### single product START ###
            $item_info_id_arr = array();

            $soldout_item_result = $this->master_item_stock_model->getItemStockInfo(
                array(
                    'channel_id_in' => $international_channel_info_arr,
                    'stock_status' => 'Y',
                    /*  'currentqty_lower' => 5,
                      'exclude_account_type_bit_lower' => 1,*/
//                  'exclude_account_type_bit_soldout'=>array('4','1','2'), /*해외품절,해외판매중,재고*/
                    'exclude_account_type_bit_soldout'=>array('1','4','2'), /*해외판매,해외품절,재고*/
                    'restrict_customer_clearance_in' => array(0,2)));

            foreach ($soldout_item_result->result_array() as $soldout_item_data) {
                array_push($item_info_id_arr, element('item_info_id', $soldout_item_data));
            }

            $soldout_item_info_id_arr = array_chunk($item_info_id_arr, 100);

            foreach ($soldout_item_info_id_arr as $soldout_item_info_ids) {
                $this->channel_item_info_model->updateChannelItemInfo(
                    array(
                        'update_date' => NULL,
                        'stock_status' => 'N',
                        'need_update' => 'Y'
                    ),
                    array('item_info_id_in' => $soldout_item_info_ids)
                );
            }

            $item_info_id_arr = array();

            $instock_item_result = $this->master_item_stock_model->getItemStockInfo(
                array(
                    'channel_id_in' => $international_channel_info_arr,
                    'stock_status' => 'N',
                    /*  'currentqty_upper' => 5,
                      'exclude_account_type_bit_lower' => 1,*/
                    'exclude_account_type_bit_insotck'=>array('1','4','2'), /*해외판매,해외품절,재고*/
                    'restrict_customer_clearance_in' => array(0,2)));

            foreach ($instock_item_result->result_array() as $instock_item_data) {
                array_push($item_info_id_arr, element('item_info_id', $instock_item_data));
            }

            $instock_item_info_id_arr = array_chunk($item_info_id_arr, 100);

            foreach ($instock_item_info_id_arr as $instock_item_info_ids) {
                $this->channel_item_info_model->updateChannelItemInfo(
                    array(
                        'update_date' => NULL,
                        'stock_status' => 'Y',
                        'need_update' => 'Y'
                    ),
                    array('item_info_id_in' => $instock_item_info_ids)
                );
            }
            ### single product END ###

            ### option,addition product START ###
            $item_option_info_id_arr	= array();

            $soldout_option_item_result	= $this->master_item_stock_model->getOptionItemStockInfo(
                array(
                    'channel_id_in'						=> $international_channel_info_arr
                ,	'regist_fg'							=> '2'
                ,	'stock_status'						=> 'Y'
                    /*,	'currentqty_lower'					=> 2
                    ,	'exclude_account_type_bit_lower'	=> 1*/
                ,   'exclude_account_type_bit_soldout'=>array('1','4','2') /*해외품절,해외판매중,재고*/
                    /*,	'need_updateE'	=> 'E'*/
                ,	'restrict_customer_clearance_in'	=> array(0,2)));

            foreach ($soldout_option_item_result->result_array() as $soldout_option_item_data) {
                array_push($item_option_info_id_arr, element('item_info_id', $soldout_option_item_data));
            }
            $soldout_option_item_info_id_arr = array_chunk($item_option_info_id_arr, 100);

            foreach ($soldout_option_item_info_id_arr as $soldout_option_item_info_ids) {
                $this->channel_option_item_info_model->updateChannelOptionItemInfo(
                    array(
                        'update_date'	=> NULL,
                        'stock_qty'		=> '0',
                        'need_update'	=> 'Y'
                    ),
                    array('item_info_id_in' => $soldout_option_item_info_ids)
                );
            }

            $item_option_info_id_arr	= array();

            $stockin_option_item_result	= $this->master_item_stock_model->getOptionItemStockInfo(
                array(
                    'channel_id_in'						=> $international_channel_info_arr
                ,	'regist_fg'							=> '2'
                ,	'stock_status'						=> 'N'
                    /*	,	'currentqty_upper'					=> 2
                        ,	'exclude_account_type_bit_lower'	=> 1*/
                ,   'exclude_account_type_bit_insotck'=>array('1','4','2') /*해외품절,해외판매중,재고*/
                    /*,	'need_updateE'	=> 'E'*/
                    /*,	'need_updateY'	=> 'E'*/
                ,	'restrict_customer_clearance_in'	=> array(0,2)));

            foreach ($stockin_option_item_result->result_array() as $stockin_option_item_data) {
                array_push($item_option_info_id_arr, element('item_info_id', $stockin_option_item_data));
            }

            $stockin_option_item_info_id_arr = array_chunk($item_option_info_id_arr, 100);

            foreach ($stockin_option_item_info_id_arr as $stockin_option_item_info_ids) {
                $this->channel_option_item_info_model->updateChannelOptionItemInfo(
                    array(
                        'update_date'	=> NULL,
                        'stock_qty'		=> '999',
                        'need_update'	=> 'Y'
                    ),
                    array('item_info_id_in' => $stockin_option_item_info_ids)
                );
            }
            ### option,addition product END ###
        }

        if(count($domestic_channel_info_arr) > 0) {
            $item_info_id_arr = array();

            $soldout_item_result = $this->master_item_stock_model->getItemStockInfo(
                array(
                    'channel_id_in' => $domestic_channel_info_arr,
                    'stock_status' => 'Y',
                    /*    'currentqty_lower' => 5,
                        'exclude_account_type_bit_lower' => 2,*/
//                    'exclude_account_type_bit_soldout'=>array('8','2','0') ,/*국내품절,국내판매중,재고*/
                    'exclude_account_type_bit_soldout'=>array('2','8','0') ,/*국내판매중,국내품절,재고*/
                    'restrict_customer_clearance' => '0'));

            foreach ($soldout_item_result->result_array() as $soldout_item_data) {
                array_push($item_info_id_arr, element('item_info_id', $soldout_item_data));
            }

            $soldout_item_info_id_arr = array_chunk($item_info_id_arr, 100);

            foreach ($soldout_item_info_id_arr as $soldout_item_info_ids) {
                $this->channel_item_info_model->updateChannelItemInfo(
                    array(
                        'update_date' => NULL,
                        'stock_status' => 'N',
                        'need_update' => 'Y'
                    ),
                    array('item_info_id_in' => $soldout_item_info_ids)
                );
            }

            $item_info_id_arr = array();

            $instock_item_result = $this->master_item_stock_model->getItemStockInfo(
                array(
                    'channel_id_in' => $domestic_channel_info_arr,
                    'stock_status' => 'N',
                    /*      'currentqty_upper' => 5,
                          'exclude_account_type_bit_lower' => 2,*/
                    'exclude_account_type_bit_insotck'=>array('2','8','0') ,/*국내판매중,국내품절,재고*/
                    'restrict_customer_clearance' => '0'));

            foreach ($instock_item_result->result_array() as $instock_item_data) {
                array_push($item_info_id_arr, element('item_info_id', $instock_item_data));
            }

            $instock_item_info_id_arr = array_chunk($item_info_id_arr, 100);

            foreach ($instock_item_info_id_arr as $instock_item_info_ids) {
                $this->channel_item_info_model->updateChannelItemInfo(
                    array(
                        'update_date' => NULL,
                        'stock_status' => 'Y',
                        'need_update' => 'Y'
                    ),
                    array('item_info_id_in' => $instock_item_info_ids)
                );
            }

            ### option,addition product START ###
            $item_option_info_id_arr	= array();

            $soldout_option_item_result	= $this->master_item_stock_model->getOptionItemStockInfo(
                array(
                    'channel_id_in'						=> $domestic_channel_info_arr
                ,	'regist_fg'							=> '2'
                ,	'stock_status'						=> 'Y'
                    /*	,	'currentqty_lower'					=> 0
                        ,	'exclude_account_type_bit_lower'	=> 2*/
                ,   'exclude_account_type_bit_soldout'=>array('2','8','0') /*국내품절,국내판매,재고*/
                    /*,	'need_updateE'	=> 'E'*/
                ,	'restrict_customer_clearance'		=> '0'

                ));

            foreach ($soldout_option_item_result->result_array() as $soldout_option_item_data) {
                array_push($item_option_info_id_arr, element('item_info_id', $soldout_option_item_data));
            }
            $soldout_option_item_info_id_arr = array_chunk($item_option_info_id_arr, 100);

            foreach ($soldout_option_item_info_id_arr as $soldout_option_item_info_ids) {
                //var_dump($soldout_option_item_info_ids);
                $this->channel_option_item_info_model->updateChannelOptionItemInfo(
                    array(
                        'update_date'	=> NULL,
                        'stock_qty'		=> '0',
                        'need_update'	=> 'Y'
                    ),
                    array('item_info_id_in' => $soldout_option_item_info_ids)
                );
            }

            $item_option_info_id_arr	= array();

            $stockin_option_item_result	= $this->master_item_stock_model->getOptionItemStockInfo(
                array(
                    'channel_id_in'						=> $domestic_channel_info_arr
                ,	'regist_fg'							=> '2'
                ,	'stock_status'						=> 'N'
                    /*,	'currentqty_upper'					=> 0
                    ,	'exclude_account_type_bit_lower'	=> 2*/
                ,   'exclude_account_type_bit_insotck'=>array('2','8','0') /*국내판매중,국내품절,재고*/
                    /*,	'need_updateE'	=> 'E'*/
                    /*,	'need_updateY'	=> 'E'*/
                ,	'restrict_customer_clearance'		=> '0'

                ));

            foreach ($stockin_option_item_result->result_array() as $stockin_option_item_data) {
                array_push($item_option_info_id_arr, element('item_info_id', $stockin_option_item_data));
            }

            $stockin_option_item_info_id_arr = array_chunk($item_option_info_id_arr, 100);

            foreach ($stockin_option_item_info_id_arr as $stockin_option_item_info_ids) {
                //var_dump($soldout_option_item_info_ids);
                $this->channel_option_item_info_model->updateChannelOptionItemInfo(
                    array(
                        'update_date'	=> NULL,
                        'stock_qty'		=> '999',
                        'need_update'	=> 'Y'
                    ),
                    array('item_info_id_in' => $stockin_option_item_info_ids)
                );
            }
            ### option,addition product END ###
        }
        echo 'done!' . PHP_EOL;

        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(35);

        $rest = $history_api->sendHistoryID();

    }

}
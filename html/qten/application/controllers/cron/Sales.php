<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오후 10:21
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/virtual_item_model');
        $this->load->model('item/master_item_model');
        $this->load->model('order/order_model');
        $this->load->model('order/order_item_model');
        $this->load->model('sales/sales_model');
    }
    
    public function apply_sales_data()
    {
        $sales_static_channel['A'] = 'AUCTION';
        $sales_static_channel['G'] = 'GMARKET';

        $channel_info_arr = array();

        $channel_info_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            $channel_info_arr[element('channel_id', $channel_info_data)] = $channel_info_data;
        }

        $order_item_filter = array(
            'status' => 5,
            'sales_flag' => 0,
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

            $channel = element(element('channel_code', $current_channel), $sales_static_channel);
            $account = element('account_id', $current_channel);
            $dt = substr(element('order_date', $order_item), 0, 10);
            $it_id = element('channel_product_no', $order_item);
            $upc = element(element('master_item_id', $order_item), $master_item_arr);
            $upc_qty = element('qty', $order_item) * element('vquantity', $order_item);
            $it_id_qty = element('qty', $order_item);


            if($this->sales_model->countSalesData(
                array(
                    'channel' => $channel,
                    'account' => $account,
                    'dt' => $dt,
                    'it_id' => $it_id,
                    'upc' => $upc
                )) > 0){

                $this->sales_model->updateSalesData(
                    array(
                        'upc_qty' => $upc_qty,
                        'it_id_qty' => $it_id_qty
                    ),
                    array(
                        'channel' => $channel,
                        'account' => $account,
                        'dt' => $dt,
                        'it_id' => $it_id,
                        'upc' => $upc
                    )
                );

            } else {
                $this->sales_model->addSalesData(
                    array(
                        'channel' => $channel,
                        'account' => $account,
                        'dt' => $dt,
                        'it_id' => $it_id,
                        'upc' => $upc,
                        'upc_qty' => $upc_qty,
                        'it_id_qty' => $it_id_qty
                    )
                );
            }

        }

        if(count($order_id_arr) > 0){
            $this->order_model->updateOrder(array('sales_flag' => 1), array('order_id_in' => $order_id_arr));
        }
        echo 'done!' . PHP_EOL;

        include "/ssd/html/history_api.php";

        $history_api = new scheduler\History_api();
        $history_api->getHistoryID(36);

        $rest = $history_api->sendHistoryID();

    }

}
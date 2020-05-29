<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2018-07-13
* Time : 오후 12:15
*/

defined('BASEPATH') OR exit('No direct script access allowed');

class Order_test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/virtual_item_model');
        $this->load->model('item/master_item_model');
        $this->load->model('order/order_model');
        $this->load->model('order/order_item_model');
        $this->load->model('order/order_history_model');
        $this->load->model('order/order_comment_model');
        $this->load->model('sales/sales_model');
        $this->load->model('stock/stock_history_model');
    }

    public function index()
    {
        $this->list();

    }

    public function test_list(){
        $this->load->model('item/channel_item_info_model');
        $master_item_ids = array();
        $upcs = array();
        $upc_infos = array();
        $channel_item_ids  =array();
        $virtual_item_result = $this->channel_item_info_model->getChannelItemInfos(array("channel_id"=>2, "single_item"=>""),"v.virtual_item_id, v.item_alias, i.channel_item_code");

        echo "<table>";

        foreach ($virtual_item_result->result_array() as $virtual_item_info){
            echo "<tr>";
            echo "<td>".$virtual_item_info['channel_item_code']."</td>";
            echo "<td>".$virtual_item_info['item_alias']."</td>";
            echo "<td>0000".$virtual_item_info['virtual_item_id']."<td>";
            echo "<td>".'V'.str_pad($virtual_item_info['virtual_item_id'], 8, '0', STR_PAD_LEFT)."</td>";
            echo "</tr>";
        }
        echo "</table>";

/*        $upc_result = $this->master_item_model->getMasterItems(array("master_item_id_in"=>$master_item_ids), "m.master_item_id, rtrim(m.upc) as upc");

        foreach ($upc_result->result_array() as $upc_info) {
            array_push($upc_infos, $upc_info);
            $upc_infos[$upc_info['master_item_id']] = $upc_info;
        }
        echo count($channel_item_ids);

        echo "<table>";
        foreach ($channel_item_ids as $channel_item_info) {
                echo "<tr>";
                echo "<td>".$channel_item_info['channel_item_code']."</td>";
                echo "<td>".$upc_infos[$channel_item_info['master_item_id']]['upc']."</td>";
                echo "<td>".'V'.str_pad($channel_item_info['virtual_item_id'], 9, '0', STR_PAD_LEFT)."</td>";
                echo "</tr>";
                }
            echo "</table>";*/

    }

    private function list()
    {
        list($filter, $data) = $this->getListFilterData();

        if($this->input->get('excel_fg') == 'Y' && $this->input->get('status') == 'all' || $this->input->get('excel_fg') == 'Y' && $this->input->get('status') == '7'){
            return alert('전체/완료일 때 다운로드 기능은 불가능합니다.');
        }

        if($this->input->get("excel_fg") != "Y") {

            $filter['limit'] = array($data['page_per_list'], ($data['page'] - 1) * $data['page_per_list']);

            $data['total_count'] = $this->order_model->getOrderCount($filter,"count(*) as cnt");


            $this->load->library('pagination');

            $paging_config['base_url'] = site_url('order/order/index');
            $url = parse_url($_SERVER['REQUEST_URI']);
            parse_str(element('query', $url), $params);
            if (isset($params['page'])) unset($params['page']);
            $paging_config['base_url'] .= '?' . http_build_query($params);

            $paging_config['total_rows'] = $data['total_count'];
            $paging_config['num_links'] = 5;
            $paging_config['per_page'] = $data['page_per_list'];
            $paging_config['use_page_numbers'] = TRUE;
            $paging_config['page_query_string'] = TRUE;
            $paging_config['query_string_segment'] = 'page';

            $this->pagination->initialize($paging_config);

            $data['paging_content'] = $this->pagination->create_links();
        }


        $data['list_data_result'] = $this->order_model->getOrders(
            $filter,
            'o.*, 
            i.order_item_id, i.channel_order_no, i.channel_product_no, i.qty, i.product_name, 
            i.option_name, i.product_type');

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', '', true),
            'add_stylesheet' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.css'),
            'add_script' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.js'),
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        if($this->input->get("excel_fg") == "Y"){
            $field_arr = array("에러"=>"error_code", "채널명"=>"channel_id", "배송주문번호"=>"order_id","채널주문번호"=>"channel_order_no","장바구니번호"=>"package_no","주문일자"=>"order_date","상품번호"=>"channel_product_no",
                "상품명"=>"product_name","옵션명"=>"option_name","수량"=>"qty","개인통관고유번호"=>"customer_number","처리상태"=>"status","송장번호"=>"shipping_code","매핑여부"=>"mapping");
            $this->downloadExcel("오픈마켓_빠른직구_주문서리스트_",$field_arr, $data);
        }else {
            $this->load->view('common/header', $header_data);
            $this->load->view('order/order_test_list', $data);
            $this->load->view('common/footer', $footer_data);
        }

    }

    private function getListFilterData()
    {
        $filter['join_item'] = 'Y';

        $data['channel_id'] = $this->input->get('channel_id');

        if($data['channel_id'] != '') $filter['channel_id'] = $data['channel_id'];

        $status = $this->input->get('status');

        if($status == '') $status = 1;

        $data['status'] = $status;

        if($status != 'all')
            $filter['status'] = $status;

        $data['page_per_list'] = $this->input->get('page_per_list');

        if($data['page_per_list'] == '') $data['page_per_list'] = 100;

        $data['page'] = $this->input->get('page');

        if($data['page'] == '') $data['page'] = 1;

        $data['status'] = $status;

        $start_dt = $this->input->get('start_dt');
        $end_dt = $this->input->get('end_dt');

        if($start_dt == '') $start_dt = date('Y-m-d', strtotime('-3 month'));
        if($end_dt == '') $end_dt = date('Y-m-d');

        $data['start_dt'] = $start_dt;
        $data['end_dt'] = $end_dt;

        $filter['order_period'] = array($start_dt, $end_dt);

        $data['search_type'] = $this->input->get('search_type');
        $data['search_value'] = $this->input->get('search_value');
        $data['sorting'] = $this->input->get('sorting');

        if($data['search_type']!="" && $data['search_value']!=""){
            $data['sorting'] = $this->input->get('sorting');

            if($this->input->get('sorting') == 'Y') $filter['sorting'] = 'Y';

        }

        switch ($data['search_type']){
            case 'channel_order_no':
                $filter_value	= explode("\r\n", $data['search_value']);

                array_walk($filter_value, function (&$item) {
                    if (is_string($item)) {
                        $item = trim($item);
                    }
                });

                $filter['channel_order_no_in'] = $filter_value;
                break;
            case 'channel_product_no':
                $filter['channel_product_no'] = $data['search_value'];
                break;
            case 'buyer_name':
                $filter['buyer_name'] = $data['search_value'];
                break;
            case 'receiver_name':
                $filter['receiver_name'] = $data['search_value'];
                break;
            case 'package_no':
                $filter_value	= explode("\r\n", $data['search_value']);

                array_walk($filter_value, function (&$item) {
                    if (is_string($item)) {
                        $item = trim($item);
                    }
                });

                $filter['package_no_in'] = $filter_value;
                break;
            case 'shipping_code':
                $filter['shipping_code'] = $data['search_value'];
                break;
            case 'shipping_ordercode':
                $search_val	 = str_replace(array('FA', 'FG') ,'' , $data['search_value']);
                $search_val	= explode("\r\n", $search_val);

                array_walk($search_val, function (&$item) {
                    if (is_string($item)) {
                        $item = trim($item);
                    }
                });

                $filter['status_in'] = array('5', '7');
                $filter['order_id_in'] = $search_val;
                break;
            case 'memo':
                $filter['memo'] = $data['search_value'];
                break;
        }

        $data['mapping'] = $this->input->get('mapping');

        if($data['mapping'] != ''){
            $filter['mapping'] = $data['mapping'];
        }

        $data['validate_error'] = $this->input->get('validate_error');

        if($data['validate_error'] != ''){
            $filter['validate_error'] = 'Y';
        }

        $data['order_validate_error'] = $this->config->item('order_validate_error');

        $data['channel_arr'] = array();

        $channel_info_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info){
            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;
        }

        $data['config_order_status'] = $this->config->item('order_status');

        return array($filter, $data);

    }

    public function detail($order_id)
    {
        $data['order_data'] = $this->order_model->getOrder(array('order_id' => $order_id));

        $data['status_txt'] = element(element('status', $data['order_data']), $this->config->item('order_status'));

        $data['error_txt'] = '';

        $order_validate_error = $this->config->item('order_validate_error');

        if(element('validate_error', $data['order_data'], 0) > 0){
            for($k = 0; $k < 5; $k++){
                if(element('validate_error', $data['order_data']) & 2 ** $k) {
                    $data['error_txt'] .= '<span class="badge badge-danger">' . $order_validate_error[2 ** $k] . '</span>&nbsp;';
                }
            }
        }

        $data['channel_info'] = $this->channel_info_model->getChannelInfo(array('channel_id' => element('channel_id', $data['order_data'])));

        $order_item_result = $this->order_item_model->getOrderItems(
            array(
                'order_id' => element('order_id', $data['order_data'])),
            'i.*');

        $data['order_item_data'] = array();

        foreach ($order_item_result->result_array() as $order_item_data){
            array_push($data['order_item_data'], $order_item_data);
        }

        $data['virtual_item_info'] = array();
        $data['master_item_info'] = array();

        if(count($data['order_item_data']) > 0){

            $virtual_item_id_arr = array_column($data['order_item_data'], 'virtual_item_id');
            $virtual_item_id_arr2 = array_column($data['order_item_data'], 'add_virtual_item_id');

            $tmp_virtual_item_id_arr = array_merge($virtual_item_id_arr, $virtual_item_id_arr2);

            $virtual_item_id_arr = array_unique($tmp_virtual_item_id_arr);

            if(count($virtual_item_id_arr) > 0){

                $master_item_ids = array();

                $virtual_item_result = $this->virtual_item_model->getVirtualItemDetail(array('virtual_item_id_in' => $virtual_item_id_arr));

                foreach ($virtual_item_result->result_array() as $virtual_item_data){

                    if(!array_key_exists(element('virtual_item_id', $virtual_item_data), $data['virtual_item_info']))

                        $data['virtual_item_info'][element('virtual_item_id', $virtual_item_data)] = array();

                    array_push($data['virtual_item_info'][element('virtual_item_id', $virtual_item_data)], $virtual_item_data);

                    if(!in_array(element('master_item_id', $virtual_item_data), $master_item_ids))

                        array_push($master_item_ids, element('master_item_id', $virtual_item_data));
                }

                if(count($master_item_ids)){

                    $master_item_result = $this->master_item_model->getMasterItems(array('master_item_id_in' => $master_item_ids));

                    foreach ($master_item_result->result_array() as $master_item_data){
                        $data['master_item_info'][element('master_item_id', $master_item_data)] = $master_item_data;
                    }

                }

            }

        }

        $this->load->view('order/order_detail', $data);


    }

    private function downloadExcel($title,$field, $data){

        ini_set('memory_limit','-1');

        $this->load->library('Excel');

        $objPHPExcel = new PHPExcel();
        $excel_title =  $title. date('Y-m-d');
        $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
            ->setTitle($excel_title)
            ->setSubject($excel_title)
            ->setDescription($excel_title);

        $sheet = $objPHPExcel->getActiveSheet();
        $string = "A";
        foreach ($field as $field_key=>$field_val){
            $sheet->getCell($string++.'1')->setValueExplicit($field_key, PHPExcel_Cell_DataType::TYPE_STRING);
        }

        $list_data_result = element('list_data_result', $data);

        $line_no = 2;

         foreach ($list_data_result->result_array() as $list_data){

            $string = "A";
            foreach ($field as $field_key=>$field_val) {
                switch ($field_val){
                    case "status" :
                        $excel_val = element(element('status', $list_data), $data['config_order_status'], '');
                        break;
                    case "error_code" :
                        if(element('validate_error', $list_data, 0) > 0){
                            for($k = 0; $k < 5; $k++){
                                if(element('validate_error', $list_data) & 2 ** $k) {
                                    $excel_val = $data['order_validate_error'][2 ** $k];
                                }
                            }
                        }else{
                            $excel_val = "";
                        }
                        break;
                    case "channel_id" :
                        $excel_val = element('comment', element(element('channel_id', $list_data),$data['channel_arr'], array()), '');
                        break;
                    case "option_name":
                        $excel_val = (element('product_type', $list_data) == '3' ? '추' : '') . element('option_name', $list_data);
                        break;
                    case "order_id" :
                        $excel_val	= element('shipping_order_code_prefix', element(element('channel_id', $list_data),$data['channel_arr'], array()), '').str_pad(element('order_id', $list_data), 9, "0", STR_PAD_LEFT);
                        break;
                    case "mapping" :
                        $excel_val = "매핑";
                        break;
                    default :
                        $excel_val = element($field_val, $list_data);
                        break;
                }
                $sheet->getCell($string++ . $line_no)->setValueExplicit($excel_val, PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $line_no++;
        }
        foreach(range('A',$string) as $columnID) {
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        $objPHPExcel->getActiveSheet()->setTitle($excel_title);
        $filename = iconv("UTF-8", "EUC-KR", $excel_title);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    public function mapping_detail($order_id){

/*        $this->load->model('openmarket/manage_model');

        $data = array();

        $data['order_id'] = $order_id;

    //    $data['status'] = $status;

        $order_info = $this->order_11st_model->getOrder(array('order_id' => $order_id));

        $data['order_info'] = $order_info;

        $data['mapping_info'] = array();
        $data['mapping_product'] = '';
        $data['mapping_upcs'] = array();

        if(element('mapping_uid', $order_info, 0) > 0) {

            $data['mapping_info'] = $this->manage_model->getProductMapping11st(array('mapping_uid' => element('mapping_uid', $order_info)));
            $data['mapping_product'] = $this->manage_model->getMappingInfo(array('mapping_uid' => element('mapping_uid', $order_info)));

            $mapping_upc_result = $this->manage_model->getMappingUpc11st(array('mapping_uid' => element('mapping_uid', $order_info)));

            foreach ($mapping_upc_result->result_array() as $mapping_upc){
                if(!array_key_exists(element('it_id', $mapping_upc), $data['mapping_upcs']))
                    $data['mapping_upcs'][element('it_id', $mapping_upc)] = array();

                array_push($data['mapping_upcs'][element('it_id', $mapping_upc)], $mapping_upc);
            }

        }*/

        $this->load->view('order/mapping_detail', $data);

    }

    function history($order_id){

        $data['order_historys'] = array();

        $config_order_status = $this->config->item('order_status');

        $order_history_result = $this->order_history_model->getOrderHistorys(array('order_id' => $order_id),'status,process_date');

        foreach ($order_history_result->result_array() as $order_history){

            if(element('status',$order_history,false) && $config_order_status[element('status',$order_history,false)]){

                $order_history['status'] = $config_order_status[element('status',$order_history,false)];
                array_push($data['order_historys'], $order_history);

            }

        }


        $this->load->view('order/order_history', $data);

    }

    function comment(){

        $order_id = $this->input->post('order_id');
        $worker_id = $this->session->userdata('qten_worker_id');
        $comment = $this->input->post('comment');

        if($comment && $order_id && $worker_id){

            $this->order_comment_model->addOrderComment(
                array(
                    'order_id' => $order_id,
                    'comment' => $comment,
                    'worker_id' => $worker_id,
                    'create_date' => date('Y-m-d H:i:s')

                ));
        }

        $comment_result = $this->order_comment_model->getOrderComment(array('order_id' => $order_id));

        $data = array();
        foreach ($comment_result->result_array() as $value){

            array_push($data, $value);

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));

    }

    public function edit_address($order_id)
    {
        $data = array();

        $data['order_id'] = $order_id;

        $data['order_address']= $this->order_model->getOrder(array('order_id' => $order_id));

        $this->load->view('order/order_edit_address', $data);

    }

    public function editAddress(){

        $order_id = $this->input->post('order_id');

        $order_info = $this->order_model->getOrder(array('order_id' => $order_id));

        if(element('status', $order_info) != '1' && element('status', $order_info) != '3'){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '주문상태가 수정이 불가능한 상태입니다.')));
        }

        $update_data = array(
            'receiver_name' => $this->input->post('receiver_name'),
            'receiver_tel1' => $this->input->post('receiver_tel1'),
            'zipcode' => $this->input->post('zipcode'),
            'addr1' => $this->input->post('addr1'),
            'addr2' => $this->input->post('addr2'),
        );

        $this->order_model->updateOrderAddress($update_data, array('order_id' => $this->input->post('order_id1')));

        if(trim(element('customer_number',$_POST,''))!='') {

            if($order_info['validate_error'] & 1){
                $update_data1 = array(
                    'customer_number' => $this->input->post('customer_number'),
                    'validate_error' => 'cast(validate_error as int) - 1'
                );
            }else{
                $update_data1 = array(
                    'customer_number' => $this->input->post('customer_number')
                );
            }

            $this->order_model->updateOrder($update_data1, array('order_id' => $this->input->post('order_id1')));

            $this->order_model->updateOrder(array('status' => '3'), array('order_id' => $this->input->post('order_id1'),'status' => '1', 'validate_error' => '0',));

        }

        $history_data = array(
            'order_id' => $order_id,
            'history_status' => 6,
            'create_id' => $this->session->userdata('qten_worker_id'),
            'create_date' => date('Y-m-d H:i:s')
        );

        $this->order_history_model->addOrderInfoHistory($history_data);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '수정처리가 완료되었습니다.')));

    }

    function updateOrderProductQuantity(){

        $order_item_id = $this->input->post('order_item_id');
        $quantity = $this->input->post('quantity');
        $order_id = $this->input->post('order_id');

        if($order_item_id && $quantity && is_numeric($quantity)) {
            $order_item_info = $this->order_item_model->getOrderItem(array('order_item_id' => $order_item_id));

            if ($quantity == element('qty', $order_item_info)) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('result' => 'error', 'msg' => '변경하시고자 하는 수량과 변경 전 수량이 동일합니다.')));
            }

            $this->order_item_model->updateOrderItem(array('qty'=>$quantity),array('order_item_id'=>$order_item_id));

            $order_data = $this->order_model->getOrder(array('order_id' => $order_id));

            $order_item_result = $this->order_item_model->getOrderItems(array('order_id' => element('order_id', $order_data)), 'i.*');

            $data['order_item_data'] = array();

            foreach ($order_item_result->result_array() as $order_item_data) {
                array_push($data['order_item_data'], $order_item_data);
            }

            $weight = 0;
            $health_cnt = 0;

            foreach (element('order_item_data', $data) as $item_option) {

                if (element('virtual_item_id', $item_option, false)) {

                    $item_additional = $this->order_item_model->getItemAddtionalInfo(element('virtual_item_id', $item_option, false));

                    $weight += element('weight', $item_additional, 0) * $item_option['qty'];
                    $health_cnt += element('health_cnt', $item_additional, 0) * $item_option['qty'];

                }

            }

            //무게
            $update_data1 = array();
            if ($weight <= 5000 && element('validate_error', $order_data, 0) & 4) {
                $update_data1 = array(
                    'validate_error' => 'cast(validate_error as int) - 4'
                );
            } elseif ($weight > 5000 && !(element('validate_error', $order_data, 0) & 4)) {
                $update_data1 = array(
                    'validate_error' => 'cast(validate_error as int) + 4'
                );
            }

            //건기식
            if ($health_cnt <= 6 && element('validate_error', $order_data, 0) & 2) {
                $update_data1 = array(
                    'validate_error' => 'cast(validate_error as int) - 2'
                );
            } elseif ($health_cnt > 6 && !(element('validate_error', $order_data, 0) & 2)) {
                $update_data1 = array(
                    'validate_error' => 'cast(validate_error as int) + 2'
                );
            }

            if (element('validate_error', $update_data1, false)) {
                $this->order_model->updateOrder($update_data1, array('order_id' => element('order_id', $order_data)));
            }

            $this->order_model->updateOrder(array('status' => '3'), array('order_id' => element('order_id', $order_data), 'status' => '1', 'validate_error' => '0',));

            $history_data = array(
                'order_id' => $order_id,
                'history_status' => 7,
                'create_id' => $this->session->userdata('qten_worker_id'),
                'create_date' => date('Y-m-d H:i:s')
            );

            $this->order_history_model->addOrderInfoHistory($history_data);

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'ok', 'msg' => '수량 변경이 완료되었습니다.')));

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'error', 'msg' => '유효한 값을 넣어주세요')));
    }

    public function cancelOrder(){

        $order_ids = $this->input->post('order_ids');
        $status = $this->input->post('status');

        if (!is_array($order_ids) || count($order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        $search_order_count = $this->order_model->getOrderCount(array('order_id_in' => $order_ids,'status'=> $status));

        if ($search_order_count - count(array_unique($order_ids)) <> 0) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 취소처리가 불가능한 주문서가 존재합니다.')));
        }

        $update_data = array(
            'status' => '9'
        );
        $update_filter = array('status' => $status, 'order_id_in' => $order_ids);

        $this->order_model->updateOrder($update_data, $update_filter);

        $order_ids=array_unique($order_ids);
        foreach ($order_ids as $order_id) {

            $history_data = array(
                'order_id' => $order_id,
                'history_status' => 9,
                'create_id' => $this->session->userdata('qten_worker_id'),
                'create_date' => date('Y-m-d H:i:s')
            );

            $this->order_history_model->addOrderInfoHistory($history_data);

            $update_data1 = array(
                'order_id' => $order_id,
                'status' => '9',
                'process_date' => date('Y-m-d H:i:s')
            );

            $this->order_history_model->addOrderHistory($update_data1);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소처리가 완료되었습니다.')));

    }

    public function cancelShippingOrders()
    {

        $order_ids = $this->input->post('order_ids');
        $status = $this->input->post('status');

        if (!is_array($order_ids) || count($order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        $search_order_count = $this->order_model->getOrderCount(array('order_id_in' => $order_ids,'status'=> $status));

        if ($search_order_count - count(array_unique($order_ids)) <> 0) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 취소처리가 불가능한 주문서가 존재합니다.')));
        }

        $order_item_filter = array(
            'status' => $status,
            'join_virtual_item_detail' => true,
            'order_id_in' =>$order_ids
        );

        $channel_info_arr = array();

        $channel_info_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            $channel_info_arr[element('channel_id', $channel_info_data)] = $channel_info_data;
        }

        $order_id_arr = array();
        $master_item_arr = array();
        $sales_static_channel['A'] = 'AUCTION';
        $sales_static_channel['G'] = 'GMARKET';

        $order_item_result = $this->order_item_model->getOrderItems($order_item_filter, 'i.*, o.channel_id, o.order_date, v.master_item_id, v.quantity as vquantity, o.sales_flag, o.stock_flag');

        foreach ($order_item_result->result_array() as $order_item){


            if(element('master_item_id', $order_item, '') == '') continue;

            if(!in_array(element('order_id', $order_item), $order_id_arr)) array_push($order_id_arr, element('order_id', $order_item));

            if(!array_key_exists(element('master_item_id', $order_item), $master_item_arr)){
                $master_item_info = $this->master_item_model->getMasterItem(array('master_item_id' => element('master_item_id', $order_item)), 'master_item_id, upc');
                $master_item_arr[element('master_item_id', $master_item_info)]= trim(element('upc', $master_item_info));

            }


            $current_channel = element(element('channel_id', $order_item), $channel_info_arr);
            $upc = element(element('master_item_id', $order_item), $master_item_arr);
            $upc_qty = element('qty', $order_item) * element('vquantity', $order_item);
            $channel = element(element('channel_code', $current_channel), $sales_static_channel);
            $it_id = element('channel_product_no', $order_item);
            $it_id_qty = element('qty', $order_item);
            $account = element('account_id', $current_channel);
            $dt = substr(element('order_date', $order_item), 0, 10);

            //stock
            $this->master_item_model->updateStockData($upc_qty, array('upc' => $upc));

            $master_item_info = $this->master_item_model->getMasterItem(array('upc' => $upc), 'currentqty');

            $history_data = array(
                'channel' => strtolower(element('channel_code', $current_channel)),
                'upc' => (string)$upc,
                'sales_qty' => $upc_qty*-1,
                'ntics_qty' => element('currentqty', $master_item_info),
                'dt' =>  date('YmdHis'),
                'ct_id' => element('order_item_id', $order_item),
                'it_id' => $it_id,
                'od_id' => element('order_id', $order_item),
                'od_time' => preg_replace("/[^0-9]*/s", '', element('order_date', $order_item))
            );

            $this->stock_history_model->addStockHistory($history_data);

            //sales
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
                        'upc_qty' => $upc_qty*-1,
                        'it_id_qty' => $it_id_qty*-1
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
                        'upc_qty' => $upc_qty*-1,
                        'it_id_qty' => $it_id_qty*-1
                    )
                );
            }

        }

        foreach ($order_id_arr as $order_id) {

            $history_data = array(
                'order_id' => $order_id,
                'history_status' => 8,
                'create_id' => $this->session->userdata('qten_worker_id'),
                'create_date' => date('Y-m-d H:i:s')
            );

            $this->order_history_model->addOrderInfoHistory($history_data);

        }

        $update_data = array(
            'status' => '9'
        );
        $update_filter = array('status' => $status, 'order_id_in' => $order_id_arr);

        $this->order_model->updateOrder($update_data, $update_filter);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소처리가 완료되었습니다.')));

    }
}
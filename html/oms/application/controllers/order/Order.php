<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오전 10:22
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/virtual_item_model');
        $this->load->model('item/master_item_model');
        if($_SERVER['REMOTE_ADDR'] == "211.214.213.101") $this->load->model('item/ople_item_model');
        $this->load->model('order/order_model');
        $this->load->model('order/order_item_model');
        $this->load->model('order/order_history_model');
        $this->load->model('order/order_comment_model');
        $this->load->model('sales/sales_model');
        $this->load->model('stock/stock_history_model');


        // 수동 완료처리 노출 작업자 체크
        $this->worker_ids = $this->config->item('oms_woker_ids');

        //masterId 체크하고 리스트 불러오기
        $this->load->library('master_id', array('chk_master_id' => true));
        $this->master_id_list = $this->master_id->getMasterId();


    }

    public function index()
    {
        $this->list();

    }

    private function list()
    {
        list($filter, $data) = $this->getListFilterData();

        // 검색 종료시간 - 시작 시간이 30일 이내인 것만 다운로드 되게 변경
        $beginSearchDate = $this->input->get('start_dt') ? new DateTime($this->input->get('start_dt')) : date("Y-m-d", strtotime("-30 days"));
        $endSearchDate = $this->input->get('end_dt') ? new DateTime($this->input->get('end_dt')) : date('Y-m-d');

        $diffDate    = @date_diff($beginSearchDate, $endSearchDate);

        if(is_object($diffDate)) {
            $isDownload = $diffDate->days  < 31 ? true : false;
        } else {
            $isDownload = false;
        }

        if(!$isDownload) {
            if($this->input->get('excel_fg') == 'Y' && $this->input->get('status') == 'all' || $this->input->get('excel_fg') == 'Y' && $this->input->get('status') == '7'){
                return alert('전체/완료일 때 다운로드 기능은 불가능합니다.');
            }
        }

        if($this->input->get("excel_fg") != "Y") {

            $filter['limit'] = array($data['page_per_list'], ($data['page'] - 1) * $data['page_per_list']);

            $data['total_count'] = $this->order_model->getOrderCount($filter, "count(*) as cnt");

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

        $data['worker_ids_fg'] = (in_array($this->session->userdata('oms_manager_user_id'), $this->worker_ids)) ? 1: 0;


        $data['list_data_result'] = $this->order_model->getOrders(
            $filter,
            'o.*, 
            i.order_item_id, i.channel_order_no, i.channel_product_no, i.qty, i.product_name, 
            i.option_name, i.product_type, i.cancel_flag,total_amount_usd');

        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('oms_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true),
            'add_stylesheet' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.css'),
            'add_script' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.js'),
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        if($this->input->get("excel_fg") == "Y"){
            $field_arr = array("에러"=>"error_code", "채널명"=>"channel_id", "배송주문번호"=>"order_id","채널주문번호"=>"channel_order_no","장바구니번호"=>"package_no","주문일자"=>"order_date","상품번호"=>"channel_product_no",
                "상품명"=>"product_name","옵션명"=>"option_name","수량"=>"qty","개인통관고유번호"=>"customer_number","처리상태"=>"status","송장번호"=>"shipping_code","매핑여부"=>"mapping");
            $this->downloadExcel("오픈마켓_뉴베이_주문서리스트_",$field_arr, $data);
        }else {
            $this->load->view('common/header', $header_data);
            $this->load->view('order/order_list', $data);
            $this->load->view('common/footer', $footer_data);
        }

    }

    private function getListFilterData()
    {

        //$filter['account_id'] = $this->session->userdata("oms_master_id");

        $filter['join_item'] = 'Y';
        $filter['join_amount'] = 'Y';

        $data['channel_id'] = $this->input->get('channel_id');

        if($data['channel_id'] != '') $filter['channel_id'] = $data['channel_id'];


        $status = $this->input->get('status');

        switch ($status){
            case 'all':
                break;
            case '9':
                $filter['cancel_mode'] = true;
                break;
            default:
                $filter['status'] = $status;
                if ($filter['status'] == '') { $filter['status'] = 1; $status = 1; }
                $filter['cancel_flag'] = 0;
                break;
        }


        $data['status'] = $status;

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
                $filter_value	= explode("\r\n", $data['search_value']);

                array_walk($filter_value, function (&$item) {
                    $item = trim($item);
                });

                $filter['channel_product_no_in'] = $filter_value;

                break;
            case 'buyer_name':
                $filter['buyer_name'] = trim($data['search_value']);
                break;
            case 'receiver_name':
                $filter['receiver_name'] = trim($data['search_value']);
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
//                $filter['shipping_code'] = trim($data['search_value']);
                /** shipping code 여러개 검색가능하게 추가 2019.10.28 */
                $filter_value	= explode("\r\n", trim($data['search_value']));

                array_walk($filter_value, function (&$item) {
                    if (is_string($item)) {
                        $item = trim($item);
                    }
                });
                $filter['shipping_code_in'] = $filter_value;

                break;
            case 'shipping_ordercode':
                $search_val	 = str_replace(array('FA', 'FG','A','G','H','B') ,'' , $data['search_value']);
                $search_val	= explode("\r\n", $search_val);

                array_walk($search_val, function (&$item) {
                    if (is_string($item)) {
                        $item = trim($item);
                    }
                });

               // $filter['status_in'] = array('5', '7', '9');
                $filter['order_id_in'] = $search_val;
                break;
            case 'memo':
                $filter['memo'] = trim($data['search_value']);
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
        $data['order_data'] = $this->order_model->getOrder(array('order_id' => $order_id,'amountinfo'=>'1'),"o.*, 
                             a.buyer_name, a.buyer_tel1, a.buyer_tel2, a.receiver_name, a.receiver_tel1, a.receiver_tel2, a.zipcode, 
                             a.addr1, a.addr2, a.comment");
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

        /**
         * 서버 장애로 인한 데이터 손실로 인해
         * 배송전산 NS_S01 에서 주소 정보를 가져오게 추가 
         */
        if($data['order_data']['buyer_name'] == '' || $data['order_data']['receiver_name'] == '') {
            $nsAddress = $this->getNsAddress($data['channel_info'], $data['order_data']);

            if(is_array($nsAddress)) {
                $data['order_data']['buyer_name'] = $nsAddress['od_name'];
                $data['order_data']['buyer_tel1'] = str_replace("-", "", $nsAddress['od_tel']) != '' ? $nsAddress['od_tel']  : $nsAddress['od_hp'];
                $data['order_data']['receiver_name'] = $nsAddress['od_b_name'];
                $data['order_data']['receiver_tel1'] = str_replace( "-", "", $nsAddress['od_b_tel']) != '' ? $nsAddress['od_b_tel']  : $nsAddress['od_b_hp'];
                $data['order_data']['zipcode'] = $nsAddress['od_b_zip1'] . $nsAddress['od_b_zip2'];
                $data['order_data']['addr1'] = $nsAddress['od_b_addr1'];
                $data['order_data']['addr2'] = $nsAddress['od_b_addr2'];
            }
        }

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

    private function getNsAddress($channel_info, $order_data) {
        $shipping_order_no = element('shipping_order_code_prefix', $channel_info).str_pad(element('order_id', $order_data), 9, "0", STR_PAD_LEFT);

        return $this->order_model->getNsAddress($shipping_order_no);
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
            $excel_val = "";
            foreach ($field as $field_key=>$field_val) {
                switch ($field_val){
                    case "status" :
                        $excel_val = element(element('status', $list_data), $data['config_order_status'], '');
                        break;
                    case "error_code" :
                        if(element('validate_error', $list_data, 0) > 0){
                            for($k = 0; $k < 5; $k++){
                                if(element('validate_error', $list_data) & 2 ** $k) {
                                    $excel_val .= $data['order_validate_error'][2 ** $k]. " ";
                                }
                            }
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

    public function mapping_detail($order_item_id){

        $data = array();

        $data['order_item_id'] = $order_item_id;

        //    $data['status'] = $status;

        $order_item_info = $this->order_item_model->getOrderItem(array('order_item_id' => $order_item_id));

        $order_info = $this->order_model->getOrder(array('order_id' => element('order_id', $order_item_info)));



        $data['order_item_info'] = $order_item_info;
        $data['order_info'] = $order_info;

        $virtual_item_id_arr = array();
        $data['virtual_item_info'] = array();
        $data['master_item_info'] = array();

        array_push($virtual_item_id_arr, element('virtual_item_id', $order_item_info));
        array_push($virtual_item_id_arr, element('add_virtual_item_id', $order_item_info));

        $virtual_item_id_arr = array_unique($virtual_item_id_arr);

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



        $this->load->view('order/mapping_detail',  $data);

    }

    public function mapping_search()
    {
        $virtual_item_id = preg_replace("/[^0-9]*/s", "", ($this->input->get("virtual_item_id")));

        $data = array();

        $data['virtual_item_id'] = $virtual_item_id;

        $virtual_item_infos = array();
        $product_infos = array();
        $mapping_products = array();

        $virtual_item_result = $this->virtual_item_model->getVirtualItemDetail(array("virtual_item_id" => $virtual_item_id));

        foreach ($virtual_item_result->result_array() as $virtual_item_info){

            $virtual_item_infos[element('master_item_id',$virtual_item_info)] = $virtual_item_info;
        }

        $data['mapping_info'] = $virtual_item_infos;

        if(count($virtual_item_infos)>0){

            $master_item_infos = array_column($virtual_item_infos, 'master_item_id');
            $master_item_infos = array_unique($master_item_infos);


            $product_result = $this->master_item_model->getMasterItems(array("master_item_id_in"=>$master_item_infos));

            foreach ($product_result->result_array() as $product_info){
                $product_infos[element('master_item_id',$product_info)] =  $product_info;
            }
            $data['product_info'] = $product_infos;
        }


        $this->load->view("order/mapping_search", $data);
    }

    public function getItemInfo(){

        $return = array();

        $virtual_item_id = preg_replace("/[^0-9]*/s", "", ($this->input->get("virtual_item_id")));

        $virtual_item_infos = array();
        $product_infos = array();
        $mapping_products = array();

        $virtual_item_result = $this->virtual_item_model->getVirtualItemDetail(array("virtual_item_id" => $virtual_item_id));

        foreach ($virtual_item_result->result_array() as $virtual_item_info){

            $virtual_item_infos[element('master_item_id',$virtual_item_info)] = $virtual_item_info;

        }

        if(count($virtual_item_infos)>0){

            $master_item_infos = array_column($virtual_item_infos, 'master_item_id');
            $master_item_infos = array_unique($master_item_infos);


            $product_result = $this->master_item_model->getMasterItems(array("master_item_id_in"=>$master_item_infos));

            $product_name ="";
            $upc_info = "";

            foreach ($product_result->result_array() as $product_info){
                $product_name .= element('mfgname',$product_info) . " " . element('item_name',$product_info) ."<Br><Br>";
                $upc_info .= element('upc',$product_info) . " X " . element('quantity', $virtual_item_infos[element('master_item_id',$product_info)])."<br><br>";
            }
        }

        $return['virtual_item_id'] =  'V'.str_pad($virtual_item_id, 9, '0', STR_PAD_LEFT);
        $return['product_name'] = $product_name;
        $return['upc_info'] = $upc_info;

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($return));
    }

    public function saveMapping(){

        $worker_id = $this->session->userdata('oms_worker_id');

        $virtual_item_id = preg_replace("/[^0-9]*/s", "", ($this->input->post("virtual_item_id")));
        $order_item_id = preg_replace("/[^0-9]*/s", "", ($this->input->post("order_item_id")));

        $order_id = preg_replace("/[^0-9]*/s", "", ($this->input->post("order_id")));

        $channel_product_no =  $this->input->post('product_no');
        $virtual_item_ids = preg_replace("/[^0-9]*/s", "", ($this->input->post("virtual_item_ids")));

        $order_info = $this->order_model->getOrder(array("order_id"=>$order_id));

        if(element('status',$order_info) != 1){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '해당 주문은 매핑 수정이 불가능한 상태입니다.')));
        }

        if($virtual_item_ids != "") {
            //order_item에서 virtual_item_id 바꾸기
            $this->order_item_model->updateOrderItem(array("virtual_item_id" => $virtual_item_ids), array("order_item_id" => $order_item_id));

            //order_item VCODE 변경시 history 남기기

            $order_item_history_data = array(
                'order_item_id' => $order_item_id,
                'old_virtual_item_id' => (int)$virtual_item_id,
                'new_virtual_item_id' => (int)$virtual_item_ids,
                'worker_id' => $worker_id,
                'history_date' => date('YmdHis')
            );

            $this->order_item_model->addOrderItemHistory($order_item_history_data);

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '매핑처리가 완료되었습니다.')));
        }else{
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '매핑된 상품이 없습니다. 다시 시도해주세요.')));
        }

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
        $worker_id = $this->session->userdata('oms_worker_id');
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
        $order_comments_worker_id = array();
        foreach ($comment_result->result_array() as $value){

            array_push($data, $value);
            array_push($order_comments_worker_id, element('worker_id', $value));

        }

        $this->load->model('user/ntics_user_model');

        //작업자 아이디 불러오기
        $worker_id_infos = array();

        if (count($order_comments_worker_id) > 0) {

            $worker_id_reseult = $this->ntics_user_model->getAllUsers(array("worker_id_in" => $order_comments_worker_id), "USER_NAME, worker_id");

            foreach ($worker_id_reseult->result_array() as $worker_id_info) {
                $worker_id_infos[element('worker_id', $worker_id_info)] = trim(element('USER_NAME', $worker_id_info));
            }
        }

        foreach ($data as $key=>$val){
            $data[$key] = array_merge($data[$key], array("worker_name"=>$worker_id_infos[element('worker_id', $val)]));
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
            'create_id' => $this->session->userdata('oms_worker_id'),
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

            $weight = array();
            $health_cnt = 0;
            $weight_over_fg = false;

            foreach (element('order_item_data', $data) as $item_option) {

                if (element('virtual_item_id', $item_option, false)) {

                    $item_additional = $this->order_item_model->getOpleItemAddtionalInfo(element('virtual_item_id', $item_option, false));

                    if(element('weight_type_id', $item_additional)>0) {

                        $weight[element('weight_type_id', $item_additional)] = (isset($weight[element('weight_type_id', $item_additional)])) ? $weight[element('weight_type_id', $item_additional)] : 0;
                        $weight[element('weight_type_id', $item_additional)] += element('weight', $item_additional, 0) * $item_option['qty'];

                        if ($weight[element('weight_type_id', $item_additional)] > element('weight_limit', $item_additional)) $weight_over_fg = true;
                    }
                    $health_cnt += element('health_cnt', $item_additional, 0) * $item_option['qty'];

                }

            }

            //무게
            $update_data1 = array();
            if ($weight_over_fg == false && element('validate_error', $order_data, 0) & 4) {
                $update_data1 = array(
                    'validate_error' => 'cast(validate_error as int) - 4'
                );
            } elseif ($weight_over_fg == true && !(element('validate_error', $order_data, 0) & 4)) {
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
                'create_id' => $this->session->userdata('oms_worker_id'),
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

    function updateTest(){

        $order_item_id = 1102;//$this->input->post('order_item_id');
        $quantity = 11; // $this->input->post('quantity');
        $order_id = 650; // $this->input->post('order_id');

        if($order_item_id && $quantity && is_numeric($quantity)) {
            $order_item_info = $this->order_item_model->getOrderItem(array('order_item_id' => $order_item_id));

            if ($quantity == element('qty', $order_item_info)) {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array('result' => 'error', 'msg' => '변경하시고자 하는 수량과 변경 전 수량이 동일합니다.')));
            }


            $order_data = $this->order_model->getOrder(array('order_id' => $order_id));

            $order_item_result = $this->order_item_model->getOrderItems(array('order_id' => element('order_id', $order_data)), 'i.*');

            $data['order_item_data'] = array();

            foreach ($order_item_result->result_array() as $order_item_data) {
                array_push($data['order_item_data'], $order_item_data);
            }

            $weight = array();
            $health_cnt = 0;
            $weight_over_fg = false;

            foreach (element('order_item_data', $data) as $item_option) {
                echo "<pre>";
                var_dump($item_option);
                echo "</pre>";

                if (element('virtual_item_id', $item_option, false)) {

                    $item_additional = $this->order_item_model->getOpleItemAddtionalInfo(element('virtual_item_id', $item_option, false));


                    if(element('weight_type_id', $item_additional)>0) {

                        $weight[element('weight_type_id', $item_additional)] = (isset($weight[element('weight_type_id', $item_additional)])) ? $weight[element('weight_type_id', $item_additional)] : 0;
                        $weight[element('weight_type_id', $item_additional)] += element('weight', $item_additional, 0) * $item_option['qty'];
                        echo $item_option['order_item_id']. "//". $weight[element('weight_type_id', $item_additional)]."<Br>";

                        if ($weight[element('weight_type_id', $item_additional)] > element('weight_limit', $item_additional)) $weight_over_fg = true;
                    }
                    $health_cnt += element('health_cnt', $item_additional, 0) * $item_option['qty'];

                }

            }

            echo $weight_over_fg."<Br>";
            //무게
            $update_data1 = array();
            if ($weight_over_fg == false) {
                $update_data1 = array(
                    'validate_error' => 'cast(validate_error as int) - 4'
                );
            } elseif ($weight_over_fg == true) {
                $update_data1 = array(
                    'validate_error' => 'cast(validate_error as int) + 4'
                );
            }

        }
    }
    

    //부분 취소(대기)
    public function partcancelOrder(){

        $product_order_ids = $this->input->post('order_ids');
        $status = $this->input->post('status');

        if (!is_array($product_order_ids) || count($product_order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        //주문서 상태 체크하기
        $order_count = $this->order_model->getOrderCount(array("order_item_id_in"=>$product_order_ids, "status_not"=>$status));

        if($order_count>0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 부분취소처리가 불가능한 주문서가 존재합니다.')));
        }

        $order_ids = array();
        $all_cancel_order_ids = array();
        $update_data2 = array();

        foreach ($product_order_ids as $product_order_id)  {

            //주문서의 order_id 가져오기..
            $order_info = $this->order_item_model->getOrderItem(array("order_item_id"=>$product_order_id), "i.order_id, i.channel_product_no, i.channel_order_no, o.validate_error");
            $order_id = element('order_id', $order_info);

            if (!in_array(element('order_id', $order_info), $order_ids)) array_push($order_ids, element('order_id', $order_info));

            //flag값 바꾸기
            $this->order_item_model->updateOrderItem(array("cancel_flag"=>1),array("order_item_id"=>$product_order_id));

            $update_data1 = array();

            $order_product_result = $this->order_model->getOrders(array("order_id"=>$order_id, "cancel_flag"=>0),"i.cancel_flag, i.virtual_item_id, i.qty, i.order_item_id, o.order_id");

            $weight = array();
            $health_cnt = 0;
            $weight_over_fg = false;

            foreach ($order_product_result->result_array() as $order_product_info){

                //건기식 / 무게초과 체크
                //vaildate_error가 있으면

                if(element('validate_error', $order_info, false)) {


                    if (element('virtual_item_id', $order_product_info, false)) {

                        $item_additional = $this->order_item_model->getOpleItemAddtionalInfo(element('virtual_item_id', $order_product_info, false));

                        if (element('weight_type_id', $item_additional) > 0) {

                            $weight[element('weight_type_id', $item_additional)] = (isset($weight[element('weight_type_id', $item_additional)])) ? $weight[element('weight_type_id', $item_additional)] : 0;
                            $weight[element('weight_type_id', $item_additional)] += element('weight_sum', $item_additional, 0) * $order_product_info['qty'];

                            if ($weight[element('weight_type_id', $item_additional)] > element('weight_limit', $item_additional)) $weight_over_fg = true;
                        }
                        $health_cnt += element('health_cnt', $item_additional, 0) * $order_product_info['qty'];

                    }

                }

                //무게
                if ($weight_over_fg == false && element('validate_error', $order_info, 0) & 4) {
                    $update_data1 = array(
                        'validate_error' => 'cast(validate_error as int) - 4'
                    );
                } elseif ($weight_over_fg == true && !(element('validate_error', $order_info, 0) & 4)) {
                    $update_data1 = array(
                        'validate_error' => 'cast(validate_error as int) + 4'
                    );
                }

                //건기식
                if ($health_cnt <= 6 && element('validate_error', $order_info, 0) & 2) {
                    $update_data1 = array(
                        'validate_error' => 'cast(validate_error as int) - 2'
                    );
                } elseif ($health_cnt > 6 && !(element('validate_error', $order_info, 0) & 2)) {
                    $update_data1 = array(
                        'validate_error' => 'cast(validate_error as int) + 2'
                    );
                }
            }


            //주문서 전체취소인지 체크하기
            $order_cancel_count = $this->order_item_model->getOrderItemCount(array("order_id"=>element('order_id', $order_info), "cancel_flag"=>0));

            if($order_cancel_count==0){
                //전체취소 주문서
                if (!in_array(element('order_id', $order_info), $all_cancel_order_ids)) array_push($all_cancel_order_ids, element('order_id', $order_info));
                unset($update_data2[$order_info['order_id']]);
            }else{
                //위에서 체크한 내용 update
                if (element('validate_error', $update_data1, false)) {
                    $update_data2[element('order_id', $order_info)]  = $update_data1;
                }
            }

            //history
            $comment_data = array(
              "order_id" => element('order_id', $order_info)
            , "comment" =>"채널주문번호 : ".element('channel_order_no',$order_info)." 부분취소"
            , "worker_id"=> $this->session->userdata('oms_worker_id')
            , "create_date" => date('Y-m-d H:i:s')
            );


            $this->order_comment_model->addOrderComment($comment_data);
        }

        //전체취소 주문서
        if(count($all_cancel_order_ids)>0)
            $this->order_model->updateOrder(array("status"=>9), array("order_id_in"=>$all_cancel_order_ids, "status_not"=>9));

        //validate_error update
        foreach ($update_data2 as $order_id=>$update_data){
            $this->order_model->updateOrder($update_data, array('order_id' => $order_id));
        }

        //validate_error 없는 대기 주문서라면 준비상태로 업데이트
        $this->order_model->updateOrder(array('status' => '3'), array('order_id_in' => $order_ids, 'status' => '1', 'validate_error' => '0'));


        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소처리가 완료되었습니다.')));

    }

    //부분 취소(배송)
    public function partcancelShippingOrders()
    {

        $this->load->model('item/master_vitual_item_model');

        $product_order_ids = $this->input->post('order_ids');
        $status = $this->input->post('status');

        if (!is_array($product_order_ids) || count($product_order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        //주문서 상태 체크하기
        $order_count = $this->order_model->getOrderCount(array("order_item_id_in"=>$product_order_ids, "status_not"=>$status));

        if($order_count>0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 부분취소처리가 불가능한 주문서가 존재합니다.')));
        }

        //channel 정보
        $channel_info_arr = array();

        $channel_info_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            $channel_info_arr[element('channel_id', $channel_info_data)] = $channel_info_data;
        }

        $sales_static_channel['A'] = 'AUCTION';
        $sales_static_channel['G'] = 'GMARKET';

        foreach ($product_order_ids as $product_order_id) {

            //flag값 바꾸기
            $this->order_item_model->updateOrderItem(array("cancel_flag"=>1),array("order_item_id"=>$product_order_id));

            //상품정보, 주문서 정보 수집
            $order_item_info = $this->order_item_model->getOrderItem(array("order_item_id"=>$product_order_id), 'i.*, o.order_date, o.channel_id, o.status,o.sales_flag, o.stock_flag');

            if(element('sales_flag', $order_item_info)==1){

                if(element('virtual_item_id', $order_item_info, '') == '')  continue;

                if(element('qty', $order_item_info, '') < 1)  continue;

                $mapping_result = $this->master_vitual_item_model->getVirtualDetailMasterMapping(array('virtual_item_id' => element('virtual_item_id', $order_item_info)));

                $current_channel = element(element('channel_id', $order_item_info), $channel_info_arr);

                $channel = element(element('channel_code', $current_channel), $sales_static_channel);
                $it_id = element('channel_product_no', $order_item_info);
                $it_id_qty = element('qty', $order_item_info);
                $account = element('account_id', $current_channel);
                $dt = substr(element('order_date', $order_item_info), 0, 10);

                foreach ($mapping_result->result_array() as $mapping_data){


                    if (element('master_item_id', $mapping_data, '') == '') continue;

                    $upc = trim(element('upc', $mapping_data));
                    $upc_qty = element('qty', $order_item_info) * element('quantity', $mapping_data);

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

            }

            //stock_flag 체크해서 복구 시키기
            if(element('stock_flag', $order_item_info)==1) {

                if(element('virtual_item_id', $order_item_info, '') == '')  continue;

                if(element('qty', $order_item_info, '') < 1)  continue;

                $mapping_result = $this->master_vitual_item_model->getVirtualDetailMasterMapping(array('virtual_item_id' => element('virtual_item_id', $order_item_info)));

                $current_channel = element(element('channel_id', $order_item_info), $channel_info_arr);

                $it_id = element('channel_product_no', $order_item_info);

                foreach ($mapping_result->result_array() as $mapping_data) {

                    if (element('master_item_id', $mapping_data, '') == '') continue;

                    $upc =  trim(element('upc', $mapping_data));
                    $upc_qty = element('qty', $order_item_info) * element('quantity', $mapping_data);


                    //stock
                    $this->master_item_model->updateStockData($upc_qty, array('upc' => $upc));

                    $master_item_info = $this->master_item_model->getMasterItem(array('upc' => $upc), 'currentqty');


                    $history_data = array(
                        'channel' => strtolower(element('channel_code', $current_channel)),
                        'upc' => (string)$upc,
                        'sales_qty' => $upc_qty * -1,
                        'ntics_qty' => element('currentqty', $master_item_info),
                        'dt' => date('YmdHis'),
                        'ct_id' => element('order_item_id', $order_item_info),
                        'it_id' => $it_id,
                        'od_id' => element('order_id', $order_item_info),
                        'od_time' => preg_replace("/[^0-9]*/s", '', element('order_date', $order_item_info))
                    );

                    $this->stock_history_model->addStockHistory($history_data);
                }

            }
            //주문서 전체취소인지 체크하기
            $order_cancel_count = $this->order_item_model->getOrderItemCount(array("order_id"=>element('order_id', $order_item_info), "cancel_flag"=>0));

            if($order_cancel_count==0){
                //전체취소로 주문서 상태 update
                $this->order_model->updateOrder(array("status"=>9), array("order_id"=>element('order_id',$order_item_info), "status_not"=>9));
            }

            //history
            $comment_data = array(
                "order_id" => element('order_id', $order_item_info)
            , "comment" =>"채널주문번호 : ".element('channel_order_no',$order_item_info)." 부분취소"
            , "worker_id"=> $this->session->userdata('oms_worker_id')
            , "create_date" => date('Y-m-d H:i:s')
            );

             $this->order_comment_model->addOrderComment($comment_data);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소처리가 완료되었습니다.')));

    }

    //전체 취소(대기)
    public function cancelOrder(){

        $product_order_ids = $this->input->post('order_ids');
        $status = $this->input->post('status');

        if (!is_array($product_order_ids) || count($product_order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        $order_status_error_cnt = $this->order_model->getOrderCount(array("order_item_id_in"=>$product_order_ids, "status_not"=>$status));

        if($order_status_error_cnt > 0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 취소처리가 불가능한 주문서가 존재합니다.')));
            exit;
        }

        $order_id_arr = array();
        foreach ($product_order_ids as $product_order_id){
            $order_info = $this->order_item_model->getOrderItem(array("order_item_id"=>$product_order_id),"i.order_id");
            if (!in_array(element('order_id', $order_info), $order_id_arr)) array_push($order_id_arr, element('order_id', $order_info));
        }

        foreach ($order_id_arr as $order_id){

            //주문서 상태 변경
            $this->order_model->updateOrder(array("status"=>9),array("order_id"=>$order_id));

            //주문서 상품 cancel_flag 전체 변경
            $this->order_item_model->updateOrderItem(array("cancel_flag"=>1), array("order_id"=>$order_id));

            //history
            $history_data = array(
                'order_id' => $order_id,
                'history_status' => 9,
                'create_id' => $this->session->userdata('oms_worker_id'),
                'create_date' => date('Y-m-d H:i:s')
            );

              $this->order_history_model->addOrderInfoHistory($history_data);

            //memo history
            $comment_data = array(
                "order_id" => $order_id
            , "comment" =>"주문서 전체 취소"
            , "worker_id"=> $this->session->userdata('oms_worker_id')
            , "create_date" => date('Y-m-d H:i:s')
            );

             $this->order_comment_model->addOrderComment($comment_data);
        }


        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소처리가 완료되었습니다.')));
    }

    //전체 취소(배송)
    public function cancelShippingOrders()
    {
        $this->load->model('item/master_vitual_item_model');

        $product_order_ids = $this->input->post('order_ids');
        $status = $this->input->post('status');

        if (!is_array($product_order_ids) || count($product_order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        $order_status_error_cnt = $this->order_model->getOrderCount(array("order_item_id_in"=>$product_order_ids, "status_not"=>$status));

        if($order_status_error_cnt > 0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 취소처리가 불가능한 주문서가 존재합니다.')));
            exit;
        }

        $order_ids = array();
        foreach ($product_order_ids as $product_order_id){
            $order_info = $this->order_item_model->getOrderItem(array("order_item_id"=>$product_order_id),"i.order_id");
            if (!in_array(element('order_id', $order_info), $order_ids)) array_push($order_ids, element('order_id', $order_info));
        }

        $channel_info_arr = array();

        $channel_info_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info_data){
            $channel_info_arr[element('channel_id', $channel_info_data)] = $channel_info_data;
        }

        $sales_static_channel['A'] = 'AUCTION';
        $sales_static_channel['G'] = 'GMARKET';

        $order_item_filter = array(
            'order_id_in' =>$order_ids,
            'join_order' =>true,
            'cancel_flag' =>0
        );

        $order_item_result = $this->order_item_model->getOrderItems($order_item_filter, 'i.*, o.order_date, o.channel_id, o.status,o.sales_flag, o.stock_flag');

        foreach ($order_item_result->result_array() as $order_item){

            //salse flag 체크해서 복구하기
            if(element('sales_flag', $order_item)==1){

                if(element('virtual_item_id', $order_item, '') == '')  continue;

                if(element('qty', $order_item, '') < 1)  continue;

                $mapping_result = $this->master_vitual_item_model->getVirtualDetailMasterMapping(array('virtual_item_id' => element('virtual_item_id', $order_item)));

                $current_channel = element(element('channel_id', $order_item), $channel_info_arr);

                $channel = element(element('channel_code', $current_channel), $sales_static_channel);
                $it_id = element('channel_product_no', $order_item);
                $it_id_qty = element('qty', $order_item);
                $account = element('account_id', $current_channel);
                $dt = substr(element('order_date', $order_item), 0, 10);

                foreach ($mapping_result->result_array() as $mapping_data){

                    if (element('master_item_id', $mapping_data, '') == '') continue;

                    $upc = trim(element('upc', $mapping_data));
                    $upc_qty = element('qty', $order_item) * element('quantity', $mapping_data);


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
            }

            //stock_flag 체크해서 복구 시키기
            if(element('stock_flag', $order_item)==1) {

                if(element('virtual_item_id', $order_item, '') == '')  continue;

                if(element('qty', $order_item, '') < 1)  continue;

                $mapping_result = $this->master_vitual_item_model->getVirtualDetailMasterMapping(array('virtual_item_id' => element('virtual_item_id', $order_item)));

                $current_channel = element(element('channel_id', $order_item), $channel_info_arr);

                $it_id = element('channel_product_no', $order_item);

                foreach ($mapping_result->result_array() as $mapping_data) {

                    if(element('upc', $mapping_data, '') == '')  continue;

                    if (element('master_item_id', $mapping_data, '') == '') continue;

                    $upc =  trim(element('upc', $mapping_data));
                    $upc_qty = element('qty', $order_item) * element('quantity', $mapping_data);

                    //stock
                   $this->master_item_model->updateStockData($upc_qty, array('upc' => $upc));

                    $master_item_info = $this->master_item_model->getMasterItem(array('upc' => $upc), 'currentqty');

                    $history_data = array(
                        'channel' => strtolower(element('channel_code', $current_channel)),
                        'upc' => (string)$upc,
                        'sales_qty' => $upc_qty * -1,
                        'ntics_qty' => element('currentqty', $master_item_info),
                        'dt' => date('YmdHis'),
                        'ct_id' => element('order_item_id', $order_item),
                        'it_id' => $it_id,
                        'od_id' => element('order_id', $order_item),
                        'od_time' => preg_replace("/[^0-9]*/s", '', element('order_date', $order_item))
                    );

                    $this->stock_history_model->addStockHistory($history_data);
                }

            }

        }

        foreach ($order_ids as $order_id){
            //history
            $history_data = array(
                'order_id' => $order_id,
                'history_status' => 8,
                'create_id' => $this->session->userdata('oms_worker_id'),
                'create_date' => date('Y-m-d H:i:s')
            );

            $this->order_history_model->addOrderInfoHistory($history_data);

            //memo history
            $comment_data = array(
              "order_id" => $order_id
            , "comment" =>"주문서 전체 취소"
            , "worker_id"=> $this->session->userdata('oms_worker_id')
            , "create_date" => date('Y-m-d H:i:s')
            );

            $this->order_comment_model->addOrderComment($comment_data);
        }

        $update_filter = array('status' => $status, 'order_id_in' => $order_ids);

        //주문서 상태 변경
        $this->order_model->updateOrder(array('status' => '9'), $update_filter);

        //주문서 상품 cancel_flag 전체 변경
        $this->order_item_model->updateOrderItem(array("cancel_flag"=>1), array("order_id_in"=>$order_ids));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소처리가 완료되었습니다.')));

    }

    //수동 확인처리(배송)
    public function completeShippingOrders()
    {
        $product_order_ids = $this->input->post('order_ids');
        $status = $this->input->post('status');

        if (!is_array($product_order_ids) || count($product_order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        if(!in_array($this->session->userdata('oms_manager_user_id'), $this->worker_ids))
        {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리할 수 있는 권한을 가지고 있지 않습니다. 로그인 정보를 다시 확인해주세요.')));
        }

        $order_status_error_cnt = $this->order_model->getOrderCount(array("order_item_id_in"=>$product_order_ids, "status_not"=>$status));

        if($order_status_error_cnt > 0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 완료처리가 불가능한 주문서가 존재합니다.')));
        }

        $order_item_filter = array(
            'order_item_id_in' =>$product_order_ids,
            'status' =>5
        );

        $order_result = $this->order_model->getOrders($order_item_filter, 'o.order_id', array(), "o.order_id");

        foreach ($order_result->result_array() as $order_data){

            $this->order_model->updateOrder(array('status'=>7), array('status' => 5, 'order_id' => element("order_id", $order_data)));

            $history_data = array(
                'order_id' => element("order_id", $order_data),
                'history_status' => 10,
                'create_id' => $this->session->userdata('oms_worker_id'),
                'create_date' => date('Y-m-d H:i:s')
            );
            $this->order_history_model->addOrderInfoHistory($history_data);

            //memo history
            $comment_data = array(
                "order_id" => element("order_id", $order_data)
            , "comment" =>"주문서 수동확인 완료처리"
            , "worker_id"=> $this->session->userdata('oms_worker_id')
            , "create_date" => date('Y-m-d H:i:s')
            );

             $this->order_comment_model->addOrderComment($comment_data);

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '주문서 완료처리가 정상적으로 완료되었습니다.')));
    }

    //수동 확인처리(대기)(발주확인용)
    public function completeReadyOrders(){

        $product_order_ids = $this->input->post('order_ids');
        $status = $this->input->post('status');

        if (!is_array($product_order_ids) || count($product_order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        if(!in_array($this->session->userdata('oms_manager_user_id'), $this->worker_ids))
        {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리할 수 있는 권한을 가지고 있지 않습니다. 로그인 정보를 다시 확인해주세요.')));
        }

        $order_status_error_cnt = $this->order_model->getOrderCount(array("order_item_id_in"=>$product_order_ids, "status_not"=>$status));

        if($order_status_error_cnt > 0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 준비처리가 불가능한 주문서가 존재합니다.')));
        }

        $order_status_error_cnt2 = $this->order_model->getOrderCount(array("order_item_id_in"=>$product_order_ids, "validate_error_search_not"=>128));

        if($order_status_error_cnt2 > 0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '주문서확인 에러 주문서만 수동준비처리가 가능합니다.')));
        }

        $order_item_filter = array(
            'order_item_id_in' =>$product_order_ids,
            'status' =>1
        );

        $order_result = $this->order_model->getOrders($order_item_filter, 'o.order_id', array(), "o.order_id");

        foreach ($order_result->result_array() as $order_data){

            $this->order_model->updateOrder(array('status'=>3, 'validate_error'=>0), array('status' => 1,  'validate_errors'=>128, 'order_id' => element("order_id", $order_data)));

            $history_data = array(
                'order_id' => element("order_id", $order_data),
                'history_status' => 3,
                'create_id' => $this->session->userdata('oms_worker_id'),
                'create_date' => date('Y-m-d H:i:s')
            );
            $this->order_history_model->addOrderInfoHistory($history_data);

            //memo history
            $comment_data = array(
                "order_id" => element("order_id", $order_data)
            , "comment" =>"주문서 수동 준비처리"
            , "worker_id"=> $this->session->userdata('oms_worker_id')
            , "create_date" => date('Y-m-d H:i:s')
            );

            $this->order_comment_model->addOrderComment($comment_data);

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '주문서 준비처리가 정상적으로 완료되었습니다.')));
    }

    //취소 복구(취소)
    public function rollbackCancelOrder(){

        $product_order_ids = $this->input->post('order_ids');

        if (!is_array($product_order_ids) || count($product_order_ids) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        $order_id_items = array();
        foreach ($product_order_ids as $product_order_id) {
            $order_result = $this->order_item_model->getOrderItem(array("order_item_id"=>$product_order_id));

            if(!array_key_exists(element('order_id', $order_result),$order_id_items)){
                $order_id_items[element('order_id', $order_result)] = array(element('order_item_id',$order_result));
            }else{
                array_push($order_id_items[element('order_id', $order_result)], element('order_item_id', $order_result));
            }

        }

        //건기식, 무게 초과 주문서 미리 체크
        $order_error_arr = array();
        foreach ($order_id_items as $order_id=>$order_item_id) {

            $order_info = $this->order_model->getOrder(array("order_id" => $order_id));

            $select = "i.order_id, o.status, o.validate_error, m.master_item_id, sum(ifnull(a.weight,0) * i.qty * m.quantity) as weight_sum, 
                    sum(ifnull(a.health_cnt,0) * i.qty * m.quantity) as health_food_qty_sum, a.weight_type_id, w.weight_limit";
            $order_validate_info = $this->order_model->getHealthWeightErrorOrder(array("order_id"=>element("order_id",$order_info), "order_item_in_not_cancel"=>implode(",",$order_item_id)),$select);

            if(count($order_validate_info)>0) {
                $order_error_arr[element('order_id', $order_info)] = (isset($order_error_arr[element('order_id', $order_info)])) ? $order_error_arr[element('order_id', $order_info)] : 0;

                if (element('weight_sum', $order_validate_info) >= element('weight_limit', $order_validate_info) && element('weight_limit', $order_validate_info)!=0) //무게초과건
                    $order_error_arr[element('order_id', $order_info)] += 4;
                if (element('health_food_qty_sum', $order_validate_info) > 6) //건기식초과건
                    $order_error_arr[element('order_id', $order_info)] += 2;
            }

        }

        $fail_rollback_order = array();

        $sales_static_channel['A'] = 'AUCTION';
        $sales_static_channel['G'] = 'GMARKET';

        //주문서 상태에 따라 다르게 체크하여 복구
        $order_id_update = array();
        foreach ($product_order_ids as $product_order_id) {

            $order_info = $this->order_model->getOrder(array("order_item_id" => $product_order_id, "join_item"=>true,"join_channel_info"=>true), "o.*, i.*, c.channel_code, c.account_id");

            switch (element('status', $order_info)) {
                case '1': //대기
                case '3': //준비
                    //flag값 update
                    $this->order_item_model->updateOrderItem(array("cancel_flag"=>0),array("order_item_id"=>$product_order_id));
                    $update_data1 = array();

                    if(array_key_exists(element('order_id', $order_info), $order_error_arr)){

                        //주문서 상태=1 validate_error update
                        if(!(element('validate_error',$order_info) & 2) && $order_error_arr[element('order_id',$order_info)] & 2){
                            //validate_error = +2 로 업데이트
                            $update_data1 = array('validate_error' => 'cast(validate_error as int) + 2');
                        }else if(element('validate_error',$order_info) & 2 && !array_key_exists(element('order_id', $order_info), $order_error_arr)){
                            //validate_error = +2 로 업데이트
                            $update_data1 = array('validate_error' => 'cast(validate_error as int) - 2');
                        }

                        if(!(element('validate_error',$order_info) & 4) && $order_error_arr[element('order_id',$order_info)] & 4){
                            //validate_error = +4 로 업데이트
                            $update_data1 = array(
                                'validate_error' => 'cast(validate_error as int) + 4'
                            );
                        }else if(element('validate_error',$order_info) & 4 && !array_key_exists(element('order_id', $order_info), $order_error_arr)){
                            //validate_error = +4 로 업데이트
                            $update_data1 = array(
                                'validate_error' => 'cast(validate_error as int) - 4'
                            );
                        }

                        $update_data1['status'] = 1;
                        $order_id_update[element('order_id', $order_info)] = $update_data1;
                    }else{
                        //주문서 상태=3 update
                        $order_id_update[element('order_id', $order_info)] = array("status"=>3);
                    }

                    //history
                    $comment_data = array(
                        "order_id" => element('order_id', $order_info)
                    , "comment" =>"채널주문번호 : ".element('channel_order_no',$order_info)." 취소 복구"
                    , "worker_id"=> $this->session->userdata('oms_worker_id')
                    , "create_date" => date('Y-m-d H:i:s')
                    );

                    $this->order_comment_model->addOrderComment($comment_data);
                    break;
                case '5': //배송

                    $this->load->model('item/master_vitual_item_model');

                    if(array_key_exists(element('order_id', $order_info), $order_error_arr)) {
                        array_push($fail_rollback_order, element('channel_order_no',$order_info));
                        //업데이트 안된 주문서 번호 array_push
                        continue;
                    }

                    //flag값 update
                     $this->order_item_model->updateOrderItem(array("cancel_flag"=>0),array("order_item_id"=>$product_order_id));

                    //sales_flag 확인해서 다시 수집
                    if(element('sales_flag',$order_info)==1){
                        if(element('virtual_item_id', $order_info, '') == '')  continue;

                        if(element('qty', $order_info, '') < 1)  continue;

                        $mapping_result = $this->master_vitual_item_model->getVirtualDetailMasterMapping(array('virtual_item_id' => element('virtual_item_id', $order_info)));

                        $channel = element(element('channel_code', $order_info), $sales_static_channel);
                        $it_id = element('channel_product_no', $order_info);
                        $it_id_qty = element('qty', $order_info);
                        $account = element('account_id', $order_info);
                        $dt = substr(element('order_date', $order_info), 0, 10);

                        foreach ($mapping_result->result_array() as $mapping_data){


                            if (element('master_item_id', $mapping_data, '') == '') continue;

                            $upc = trim(element('upc', $mapping_data));
                            $upc_qty = element('qty', $order_info) * element('quantity', $mapping_data);

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
                                        'upc_qty' => $upc_qty*-1,
                                        'it_id_qty' => $it_id_qty
                                    )
                                );
                            }

                        }
                    }

                    //stock_flag 확인해서 다시 수집
                    if(element('stock_flag',$order_info)==1){

                        if(element('virtual_item_id', $order_info, '') == '')  continue;

                        if(element('qty', $order_info, '') < 1)  continue;

                        $mapping_result = $this->master_vitual_item_model->getVirtualDetailMasterMapping(array('virtual_item_id' => element('virtual_item_id', $order_info)));

                        $it_id = element('channel_product_no', $order_info);

                        foreach ($mapping_result->result_array() as $mapping_data) {

                            if (element('master_item_id', $mapping_data, '') == '') continue;

                            $upc =  trim(element('upc', $mapping_data));
                            $upc_qty = element('qty', $order_info) * element('quantity', $mapping_data);


                            //stock
                             $this->master_item_model->updateStockData($upc_qty * -1, array('upc' => $upc));

                            $master_item_info = $this->master_item_model->getMasterItem(array('upc' => $upc), 'currentqty');


                            $history_data = array(
                                'channel' => strtolower(element('channel_code', $order_info)),
                                'upc' => (string)$upc,
                                'sales_qty' => $upc_qty,
                                'ntics_qty' => element('currentqty', $master_item_info),
                                'dt' => date('YmdHis'),
                                'ct_id' => element('order_item_id', $order_info),
                                'it_id' => $it_id,
                                'od_id' => element('order_id', $order_info),
                                'od_time' => preg_replace("/[^0-9]*/s", '', element('order_date', $order_info))
                            );


                              $this->stock_history_model->addStockHistory($history_data);
                        }

                    }

                    //history
                    $comment_data = array(
                        "order_id" => element('order_id', $order_info)
                    , "comment" =>"채널주문번호 : ".element('channel_order_no',$order_info)." 취소 복구"
                    , "worker_id"=> $this->session->userdata('oms_worker_id')
                    , "create_date" => date('Y-m-d H:i:s')
                    );

                     $this->order_comment_model->addOrderComment($comment_data);
                    break;
                case '9': //취소
                    $before_status_info = $this->order_history_model->getOrderHistory(array("order_id"=>element("order_id",$order_info), "status_not"=>9), "max(status) as status");

                    $before_status = element('status',$before_status_info);

                    switch ($before_status){
                        case '1': //대기,준비
                        case '3':
                            //flag값 update
                             $this->order_item_model->updateOrderItem(array("cancel_flag"=>0),array("order_item_id"=>$product_order_id));
                            //validate_error set
                            $update_data1 = array();

                            if(!(element('validate_error',$order_info) & 2) && $order_error_arr[element('order_id',$order_info)] & 2){
                                //validate_error = +2 로 업데이트
                                $update_data1 = array('validate_error' => 'cast(validate_error as int) + 2');
                            }else if(element('validate_error',$order_info) & 2 && !array_key_exists(element('order_id', $order_info), $order_error_arr)){
                                //validate_error = +2 로 업데이트
                                $update_data1 = array('validate_error' => 'cast(validate_error as int) - 2');
                            }


                        if(!(element('validate_error',$order_info) & 4) && $order_error_arr[element('order_id',$order_info)] & 4){
                                //validate_error = +4 로 업데이트
                                $update_data1 = array(
                                    'validate_error' => 'cast(validate_error as int) + 4'
                                );
                            }else if(element('validate_error',$order_info) & 4 && !array_key_exists(element('order_id', $order_info), $order_error_arr)){
                                //validate_error = -4 로 업데이트
                                $update_data1 = array(
                                    'validate_error' => 'cast(validate_error as int) - 4'
                                );
                            }

                            //상태값 세팅
                            if(array_key_exists(element('order_id', $order_info), $order_error_arr)) {
                                $update_data1['status'] = 1;
                            }else{
                                $update_data1['status'] = 3;
                                // status = 3
                            }

                            //validate_error + status 를 주문서에 update
                            $order_id_update[element('order_id', $order_info)] = $update_data1;

                            //history
                            $comment_data = array(
                                "order_id" => element('order_id', $order_info)
                            , "comment" =>"채널주문번호 : ".element('channel_order_no',$order_info)." 취소 복구"
                            , "worker_id"=> $this->session->userdata('oms_worker_id')
                            , "create_date" => date('Y-m-d H:i:s')
                            );

                             $this->order_comment_model->addOrderComment($comment_data);

                            break;
                        case '5': //배송

                            if(array_key_exists(element('order_id', $order_info), $order_error_arr)) {
                                array_push($fail_rollback_order, element('channel_order_no',$order_info));
                                //업데이트 안된 주문서 번호 array_push
                                continue;
                            }
                            $this->load->model('item/master_vitual_item_model');

                            //flag값 update
                             $this->order_item_model->updateOrderItem(array("cancel_flag"=>0),array("order_item_id"=>$product_order_id));

                            //sales_flag 확인해서 다시 수집
                            if(element('sales_flag',$order_info)==1){

                                if(element('virtual_item_id', $order_info, '') == '')  continue;

                                if(element('qty', $order_info, '') < 1)  continue;

                                $mapping_result = $this->master_vitual_item_model->getVirtualDetailMasterMapping(array('virtual_item_id' => element('virtual_item_id', $order_info)));

                                $channel = element(element('channel_code', $order_info), $sales_static_channel);
                                $it_id = element('channel_product_no', $order_info);
                                $it_id_qty = element('qty', $order_info);
                                $account = element('account_id', $order_info);
                                $dt = substr(element('order_date', $order_info), 0, 10);

                                foreach ($mapping_result->result_array() as $mapping_data){


                                    if (element('master_item_id', $mapping_data, '') == '') continue;

                                    $upc = trim(element('upc', $mapping_data));
                                    $upc_qty = element('qty', $order_info) * element('quantity', $mapping_data);

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
                            }

                            //stock_flag 확인해서 다시 수집
                            if(element('stock_flag',$order_info)==1){

                                if(element('virtual_item_id', $order_info, '') == '')  continue;

                                if(element('qty', $order_info, '') < 1)  continue;

                                $mapping_result = $this->master_vitual_item_model->getVirtualDetailMasterMapping(array('virtual_item_id' => element('virtual_item_id', $order_info)));

                                $it_id = element('channel_product_no', $order_info);

                                foreach ($mapping_result->result_array() as $mapping_data) {

                                    if (element('master_item_id', $mapping_data, '') == '') continue;

                                    $upc =  trim(element('upc', $mapping_data));
                                    $upc_qty = element('qty', $order_info) * element('quantity', $mapping_data);


                                    //stock
                                    $this->master_item_model->updateStockData($upc_qty * -1, array('upc' => $upc));

                                    $master_item_info = $this->master_item_model->getMasterItem(array('upc' => $upc), 'currentqty');

                                    $history_data = array(
                                        'channel' => strtolower(element('channel_code', $order_info)),
                                        'upc' => (string)$upc,
                                        'sales_qty' => $upc_qty,
                                        'ntics_qty' => element('currentqty', $master_item_info),
                                        'dt' => date('YmdHis'),
                                        'ct_id' => element('order_item_id', $order_info),
                                        'it_id' => $it_id,
                                        'od_id' => element('order_id', $order_info),
                                        'od_time' => preg_replace("/[^0-9]*/s", '', element('order_date', $order_info))
                                    );

                                  $this->stock_history_model->addStockHistory($history_data);
                                }
                            }

                            //주문서 상태 status = 5
                            $order_id_update[element('order_id', $order_info)] = array("status"=>5);

                            //history
                            $comment_data = array(
                              "order_id" => element('order_id', $order_info)
                            , "comment" =>"채널주문번호 : ".element('channel_order_no',$order_info)." 취소 복구"
                            , "worker_id"=> $this->session->userdata('oms_worker_id')
                            , "create_date" => date('Y-m-d H:i:s')
                            );

                             $this->order_comment_model->addOrderComment($comment_data);
                            break;
                        case '7' : //완료
                            array_push($fail_rollback_order, element('channel_order_no',$order_info));
                            break;
                    }

                    break;
                case '7': //완료
                    array_push($fail_rollback_order, element('channel_order_no',$order_info));
                    break;
                default:
                    array_push($fail_rollback_order, element('channel_order_no',$order_info));
                    break;
            }
        }

        foreach ($order_id_update as $order_id=>$update_date){

            $this->order_model->updateOrder($update_date, array("order_id"=>$order_id));
        }
        $fail_rollback_order = array_unique($fail_rollback_order);

        //완료처리된 주문서, 건기식/무게초과건(배송상태)의 경우 복구 불가능
        if(count($fail_rollback_order)>0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 처리가 불가능한 주문서(채널주문번호 : '.implode(",",$fail_rollback_order).')가 존재합니다. 배송상태의 통관불가 주문서 혹은 완료된 주문서가 아닌지 확인해주세요.')));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소복구 처리가 완료되었습니다.')));
    }

//    public function cancelOrder(){
//
//        $order_ids = $this->input->post('order_ids');
//        $status = $this->input->post('status');
//
//        if (!is_array($order_ids) || count($order_ids) < 1) {
//            return $this->output
//                ->set_content_type('application/json')
//                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
//        }
//
//        $search_order_count = $this->order_model->getOrderCount(array('order_id_in' => $order_ids,'status'=> $status));
//
//        if ($search_order_count - count(array_unique($order_ids)) <> 0) {
//            return $this->output
//                ->set_content_type('application/json')
//                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 취소처리가 불가능한 주문서가 존재합니다.')));
//        }
//
//        $update_data = array(
//            'status' => '9'
//        );
//        $update_filter = array('status' => $status, 'order_id_in' => $order_ids);
//
//        $this->order_model->updateOrder($update_data, $update_filter);
//
//        $order_ids=array_unique($order_ids);
//        foreach ($order_ids as $order_id) {
//
//            $history_data = array(
//                'order_id' => $order_id,
//                'history_status' => 9,
//                'create_id' => $this->session->userdata('oms_worker_id'),
//                'create_date' => date('Y-m-d H:i:s')
//            );
//
//            $this->order_history_model->addOrderInfoHistory($history_data);
//
///*            $update_data1 = array(
//                'order_id' => $order_id,
//                'status' => '9',
//                'process_date' => date('Y-m-d H:i:s')
//            );
//
//            $this->order_history_model->addOrderHistory($update_data1);*/
//        }
//
//        return $this->output
//            ->set_content_type('application/json')
//            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소처리가 완료되었습니다.')));
//
//    }
//
//    public function cancelShippingOrders()
//    {
//
//        $order_ids = $this->input->post('order_ids');
//        $status = $this->input->post('status');
//
//        if (!is_array($order_ids) || count($order_ids) < 1) {
//            return $this->output
//                ->set_content_type('application/json')
//                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
//        }
//
//        $search_order_count = $this->order_model->getOrderCount(array('order_id_in' => $order_ids,'status'=> $status));
//
//        if ($search_order_count - count(array_unique($order_ids)) <> 0) {
//            return $this->output
//                ->set_content_type('application/json')
//                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 취소처리가 불가능한 주문서가 존재합니다.')));
//        }
//
//        $order_item_filter = array(
//            'status' => $status,
//            'join_virtual_item_detail' => true,
//            'order_id_in' =>$order_ids
//        );
//
//        $channel_info_arr = array();
//
//        $channel_info_result = $this->channel_info_model->getChannelInfos(array());
//
//        foreach ($channel_info_result->result_array() as $channel_info_data){
//            $channel_info_arr[element('channel_id', $channel_info_data)] = $channel_info_data;
//        }
//
//        $order_id_arr = array();
//        $master_item_arr = array();
//        $sales_static_channel['A'] = 'AUCTION';
//        $sales_static_channel['G'] = 'GMARKET';
//
//        $order_item_result = $this->order_item_model->getOrderItems($order_item_filter, 'i.*, o.channel_id, o.order_date, v.master_item_id, v.quantity as vquantity, o.sales_flag, o.stock_flag');
//
//        foreach ($order_item_result->result_array() as $order_item){
//
//
//            if(element('master_item_id', $order_item, '') == '') continue;
//
//            if(!in_array(element('order_id', $order_item), $order_id_arr)) array_push($order_id_arr, element('order_id', $order_item));
//
//            if(!array_key_exists(element('master_item_id', $order_item), $master_item_arr)){
//                $master_item_info = $this->master_item_model->getMasterItem(array('master_item_id' => element('master_item_id', $order_item)), 'master_item_id, upc');
//                $master_item_arr[element('master_item_id', $master_item_info)]= trim(element('upc', $master_item_info));
//
//            }
//
//
//            $current_channel = element(element('channel_id', $order_item), $channel_info_arr);
//            $upc = element(element('master_item_id', $order_item), $master_item_arr);
//            $upc_qty = element('qty', $order_item) * element('vquantity', $order_item);
//            $channel = element(element('channel_code', $current_channel), $sales_static_channel);
//            $it_id = element('channel_product_no', $order_item);
//            $it_id_qty = element('qty', $order_item);
//            $account = element('account_id', $current_channel);
//            $dt = substr(element('order_date', $order_item), 0, 10);
//
//            //stock
//            $this->master_item_model->updateStockData($upc_qty, array('upc' => $upc));
//
//            $master_item_info = $this->master_item_model->getMasterItem(array('upc' => $upc), 'currentqty');
//
//            $history_data = array(
//                'channel' => strtolower(element('channel_code', $current_channel)),
//                'upc' => (string)$upc,
//                'sales_qty' => $upc_qty*-1,
//                'ntics_qty' => element('currentqty', $master_item_info),
//                'dt' =>  date('YmdHis'),
//                'ct_id' => element('order_item_id', $order_item),
//                'it_id' => $it_id,
//                'od_id' => element('order_id', $order_item),
//                'od_time' => preg_replace("/[^0-9]*/s", '', element('order_date', $order_item))
//            );
//
//            $this->stock_history_model->addStockHistory($history_data);
//
//            //sales
//            if($this->sales_model->countSalesData(
//                    array(
//                        'channel' => $channel,
//                        'account' => $account,
//                        'dt' => $dt,
//                        'it_id' => $it_id,
//                        'upc' => $upc
//                    )) > 0){
//
//                $this->sales_model->updateSalesData(
//                    array(
//                        'upc_qty' => $upc_qty*-1,
//                        'it_id_qty' => $it_id_qty*-1
//                    ),
//                    array(
//                        'channel' => $channel,
//                        'account' => $account,
//                        'dt' => $dt,
//                        'it_id' => $it_id,
//                        'upc' => $upc
//                    )
//                );
//
//            } else {
//                $this->sales_model->addSalesData(
//                    array(
//                        'channel' => $channel,
//                        'account' => $account,
//                        'dt' => $dt,
//                        'it_id' => $it_id,
//                        'upc' => $upc,
//                        'upc_qty' => $upc_qty*-1,
//                        'it_id_qty' => $it_id_qty*-1
//                    )
//                );
//            }
//
//        }
//
//        foreach ($order_id_arr as $order_id) {
//
//            $history_data = array(
//                'order_id' => $order_id,
//                'history_status' => 8,
//                'create_id' => $this->session->userdata('oms_worker_id'),
//                'create_date' => date('Y-m-d H:i:s')
//            );
//
//            $this->order_history_model->addOrderInfoHistory($history_data);
//
//        }
//
//        $update_data = array(
//            'status' => '9'
//        );
//        $update_filter = array('status' => $status, 'order_id_in' => $order_id_arr);
//
//        $this->order_model->updateOrder($update_data, $update_filter);
//
//        return $this->output
//            ->set_content_type('application/json')
//            ->set_output(json_encode(array('result' => 'ok', 'msg' => '취소처리가 완료되었습니다.')));
//
//    }

   /* public function completeShippingOrders()
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

        if(!in_array($this->session->userdata('oms_manager_user_id'), $this->worker_ids))
        {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리할 수 있는 권한을 가지고 있지 않습니다. 로그인 정보를 다시 확인해주세요.')));
        }

        $orders_result = $this->order_model->getOrders(array('order_id_in' => $order_ids));
        foreach ($orders_result->result_array() as $order_data){

            $this->order_model->updateOrder(array('status'=>7), array('status' => 5, 'order_id' => element("order_id", $order_data)));

            $history_data = array(
                'order_id' => element("order_id", $order_data),
                'history_status' => 10,
                'create_id' => $this->session->userdata('oms_worker_id'),
                'create_date' => date('Y-m-d H:i:s')
            );

            $this->order_history_model->addOrderInfoHistory($history_data);

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '주문서 완료처리가 정상적으로 완료되었습니다.')));
    }*/

    public function compareOrderItemPrice(){

        $data = $master_item_ids = $master_item_id = $order_products = $virtual_item_ids  = $upc_ids = array();

        $set_gap = $this->input->get('set_gap');

        $data['set_gap'] = $set_gap == '' ? 3 : $set_gap;

        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('oms_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true),
            'add_stylesheet' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.css'),
            'add_script' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.js'),
        );
        $footer_data = array(
            'left_menu_on' => true
        );
//8162,9493,13396,
        $order_product_result = $this->order_model->getOrders(array("status"=>3, "join_item"=>"Y"), "i.channel_product_no, i.virtual_item_id, i.unit_amount", array(), "i.virtual_item_id");
        foreach ($order_product_result->result_array() as $order_product_info){
            array_push($order_products, $order_product_info);
        }

        $virtual_item_ids = array_column($order_products, 'virtual_item_id');
        
        $data['order_product_prices'] = $data['mapping_info'] = $data['ople_price'] = array();

        $mapping_list = $mapping_info = $ople_price = array();

        if(count($virtual_item_ids)>0){

            $master_item_result = $this->virtual_item_model->getVirtualItemDetail(array("virtual_item_id_in"=>$virtual_item_ids));

            foreach ($master_item_result->result_array() as $master_item_info){
                $master_item_ids[element('virtual_item_id', $master_item_info)][] = $master_item_info;
                array_push($master_item_id, element('master_item_id', $master_item_info));
            }

            $data['master_item_info'] = $master_item_ids;

            $upc_result = $this->master_item_model->getMasterItems(array("master_item_id_in"=>$master_item_id), "m.master_item_id, rtrim(m.upc) as upc");

            foreach ($upc_result->result_array() as $upc_info){
                $upc_ids[element('master_item_id',$upc_info)] = $upc_info;
            }

            $data['upc_info'] = $upc_ids;


            if(count($upc_ids) > 0) {

                $mapping_info_result = $this->ople_item_model->getItemMapping(array('upc_in' => array_column($upc_ids, 'upc'), "ople_type" => "m", "it_use" => 1, "it_discontinued" => 0), "m.upc, m.it_id, i.it_name");

                $it_ids = array();

                foreach ($mapping_info_result->result_array() as $mapping_info_data) {
                    if (!array_key_exists(element('upc', $mapping_info_data), $mapping_info))
                        $mapping_info[element('upc', $mapping_info_data)] = array();
                    array_push($mapping_info[element('upc', $mapping_info_data)], $mapping_info_data);
                    if (!in_array(element('it_id', $mapping_info_data), $it_ids)) array_push($it_ids, element('it_id', $mapping_info_data));
                }

                $data['mapping_info'] = $mapping_info;

                if (count($it_ids) > 0) {
                    $price_info_result = $this->ople_item_model->getItems(array('it_id_in' => $it_ids), 'it_id, it_amount');
                    foreach ($price_info_result->result_array() as $price_info_data) {
                        $ople_price[element('it_id', $price_info_data)] = element('it_amount', $price_info_data);
                    }

                    $usd_rate = $this->ople_item_model->getRecentRate();

                    $promotion_price_result = $this->ople_item_model->getPromotionPrice(array('it_ids' => $it_ids));
                    foreach ($promotion_price_result->result_array() as $promotion_price_data) {
                        $ople_price[element('it_id', $promotion_price_data)] = round(element('amount_usd', $promotion_price_data) * $usd_rate);
                    }

                    $hotdeal_price_result = $this->ople_item_model->getHotDealPrice(array('it_ids' => $it_ids));
                    foreach ($hotdeal_price_result->result_array() as $hotdeal_price_data) {
                        $ople_price[element('it_id', $hotdeal_price_data)] = element('it_event_amount', $hotdeal_price_data);
                    }

                    $membership_price_result = $this->ople_item_model->getMembershipPrice(array('it_ids' => $it_ids));
                    foreach ($membership_price_result->result_array() as $membership_price_data) {
                        $ople_price[element('it_id', $membership_price_data)] = round(element('member_price', $membership_price_data) * $usd_rate);
                    }
                }

                $data['ople_price'] = $ople_price;

                $mapping_price = array();

                foreach ($master_item_ids as $mapping_uids) {

                    $current_total_price = 0;
                    foreach ($mapping_uids as $mapping_uid) {

                        $current_upc = element('upc', $upc_ids[element('master_item_id', $mapping_uid)]);
                        $current_mapping_info = element($current_upc, $mapping_info);

                        $current_price = 0;
                        if (count($current_mapping_info) > 0) {

                            foreach ($current_mapping_info as $current_mapping_data) {

                                $current_price += (element(element('it_id', $current_mapping_data), $ople_price) * element('quantity', $mapping_uid));

                            }
                        }
                        $current_total_price += $current_price;
                        $mapping_price[element('virtual_item_id', $mapping_uid)] = $current_total_price;
                    }
                }

                $order_product_price_result = $this->order_model->getOrders(array("status" => 3, "join_item" => "Y"), "i.*, o.channel_id, round(i.unit_amount) as product_price", array());

                foreach ($order_product_price_result->result_array() as $order_product_price_info) {

                    $order_product_price_info['ople_price'] = element(element('virtual_item_id', $order_product_price_info), $mapping_price);
                    $order_product_price_info['price_gap'] = element('product_price', $order_product_price_info) - element(element('virtual_item_id', $order_product_price_info), $mapping_price);
                    array_push($data['order_product_prices'], $order_product_price_info);
                }
            }

        }

        $this->load->view('common/header', $header_data);
        $this->load->view('order/compare_order_item_price', $data);
        $this->load->view('common/footer', $footer_data);
    }
	
	public function testmodel(){
		$item_data	= $this->master_item_model->getMasterItem(
			array('master_item_id' =>'17814', 'mfg_info'=>'1')
			,	'm.upc, f.mfgname, m.item_name, m.potency, m.potency_unit, m.count, m.type, m.WHOLESALE_PRICE AS wp');
		
		var_dump($item_data);
		
	}
	
}
<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오후 5:43
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Soldout_history extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/master_item_model');
        $this->load->model('item/soldout_history_model');
        $this->load->model('user/ntics_user_model');

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

        $data['history_date_arr'] =$data['list_datas'] = $filter = $ntics_filter = array();

        $data['status'] = $this->input->get('status');

        if($data['status'] != '2'){
            $data['status'] = '1';
        }

        $data['search_date'] = element('date',$_GET,'');

        $data['channel_id'] = element('channel',$_GET,'');

        $data['upc'] = element('upc',$_GET,'');

        $data['channel_item_code'] = element('channel_item_code',$_GET,'');

        $data['virtual_item_id'] = element('virtual_item_id',$_GET,'');

        $data['brand'] = element('brand',$_GET,'');

        $data['soldout_fg'] = element('soldout_fg',$_GET,'');


        $channel_filter['master_id'] = $this->session->userdata("oms_master_id");
        $channel_result = $this->channel_info_model->getNewChannelInfos($channel_filter);

        foreach ($channel_result->result_array() as $channel_info){

            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;

        }

        if($data['upc']!=''){
            $ntics_filter['upc'] = $data['upc'];
        }

        if($data['brand']!=''){
            $ntics_filter['search_brand'] = $data['brand'];
        }

        if($data['status'] != '2'){
            $date_result = $this->soldout_history_model->getSoldoutHistory(array('distinct'=>'on'),"date_format(create_date, '%Y-%m-%d') create_date",array('create_date'=>'desc'));
        }else{
            $date_result = $this->soldout_history_model->getSoldoutHistoryError(array('distinct'=>'on'),"date_format(create_date, '%Y-%m-%d') create_date",array('create_date'=>'desc'));
        }


        foreach ($date_result ->result_array() as $date_info){

             array_push($data['history_date_arr'],$date_info);

        }


        if(count($ntics_filter)>0){
            $filter['master_item_id_in'] = array();
            $master_item_search_result=$this->master_item_model->getMasterItems($ntics_filter);
            foreach ($master_item_search_result->result_array() as $master_item_search_data){

                array_push($filter['master_item_id_in'], element('master_item_id', $master_item_search_data));

            }

            if(empty($filter['master_item_id_in'])) array_push($filter['master_item_id_in'], '');
        }

        $select =' cc.comment, c.channel_item_code, v.virtual_item_id, d.master_item_id, v.item_alias, o.stock_status, o.currentqty, o.soldout_process_type, o.process_worker_id, o.create_date';

        if($data['channel_id'] != '') $filter['channel_id'] = $data['channel_id'];

        if($data['search_date'] != '') $filter['yyyy-mm-dd'] = $data['search_date'];

        if($data['soldout_fg'] != '') $filter['stock_status'] = $data['soldout_fg'];

        if($data['channel_item_code'] != '') $filter['channel_item_codes'] = $data['channel_item_code'];

        if($data['virtual_item_id'] != '') $filter['virtual_items'] = (int)substr($data['virtual_item_id'], 2, strlen($data['virtual_item_id']) - 1);

        $data['page_per_list'] = $this->input->get('page_per_list');

        if($data['page_per_list'] == '') $data['page_per_list'] = 100;

        $data['page'] = $this->input->get('page');

        if($data['page'] == '') $data['page'] = 1;

        $filter['limit'] = array($data['page_per_list'], ($data['page'] - 1) * $data['page_per_list']);

        if($this->input->get('excel') == 'Y') {
            unset($filter['limit']);
        }
        $this->load->library('pagination');


        $paging_config['base_url'] = site_url('item/soldout_history');
        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str(element('query', $url), $params);
        if (isset($params['page'])) unset($params['page']);
        $paging_config['base_url'] .= '?' . http_build_query($params);

        $filter['master_id'] = $this->session->userdata("oms_master_id");


        if($data['status'] != '2') {
            $data['total_count'] = $this->soldout_history_model->getSoldoutHistorysCount($filter);
        }else{
            $data['total_count'] = $this->soldout_history_model->getSoldoutHistorysErrorCount($filter);
        }

        $paging_config['total_rows'] = $data['total_count'];
        $paging_config['num_links'] = 5;
        $paging_config['per_page'] = $data['page_per_list'];
        $paging_config['use_page_numbers'] = TRUE;
        $paging_config['page_query_string'] = TRUE;
        $paging_config['query_string_segment'] = 'page';

        $this->pagination->initialize($paging_config);

        $data['paging_content'] = $this->pagination->create_links();

        if($data['status'] != '2') {
            $date_result = $this->soldout_history_model->getSoldoutHistorys($filter, $select, array('create_date' => 'desc'));
        }else{
            $select.=',error_message';
            $date_result = $this->soldout_history_model->getSoldoutHistorysError($filter, $select, array('create_date' => 'desc'));
        }
        foreach ($date_result ->result_array() as $date_info){

            array_push($data['list_datas'],$date_info);

        }
        $data['master_item_arr'] = array();
        $data['worker_arr'] = array();


        if(count($data['list_datas']) > 0){

            $master_item_ids = array_column($data['list_datas'], 'master_item_id');

            $master_item_result = $this->master_item_model->getMasterItems(array('master_item_id_in' => $master_item_ids));

            foreach ($master_item_result->result_array() as $master_item_data){
                $data['master_item_arr'][element('master_item_id', $master_item_data)] = $master_item_data;
            }

            $worker_ids = array_column($data['list_datas'], 'process_worker_id');

            $worker_result = $this->ntics_user_model->getUsers(array('worker_id_in' => $worker_ids), 'worker_id, rtrim(USER_NAME) as user_name');

            foreach ($worker_result->result_array() as $worker_data){
                $data['worker_arr'][element('worker_id', $worker_data)] = element('user_name', $worker_data);
            }

        }


        $data['soldout_process_type'] = $this->config->item('soldout_process_type');

        if($this->input->get('excel') == 'Y') {

            $this->dowloadExcel($data);
            exit;

        }
        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('oms_master_id')
        );
        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true)
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        $this->load->view('common/header', $header_data);
        $this->load->view('item/soldout_history_list', $data);
        $this->load->view('common/footer', $footer_data);

    }

    private function dowloadExcel($data)
    {
        $this->load->library('Excel');

        $objPHPExcel = new PHPExcel();
        $excel_title = '이베이_품절_품절해제_' . date('Y-m-d');
        $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
            ->setTitle($excel_title)
            ->setSubject($excel_title)
            ->setDescription($excel_title);

        $sheet = $objPHPExcel->getActiveSheet();

        $sheet->getCell('A1')->setValueExplicit('채널', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('B1')->setValueExplicit('상품코드', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('C1')->setValueExplicit('VCODE', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('D1')->setValueExplicit('UPC', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('E1')->setValueExplicit('상품갯수', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('F1')->setValueExplicit('브랜드', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('G1')->setValueExplicit('상품명', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('H1')->setValueExplicit('로케이션', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('I1')->setValueExplicit('품절여부', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('J1')->setValueExplicit('비고', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('K1')->setValueExplicit('작업자', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('L1')->setValueExplicit('작업날짜', PHPExcel_Cell_DataType::TYPE_STRING);
        if(element('status', $data)=='2'){
            $sheet->getCell('M1')->setValueExplicit('리턴메세지', PHPExcel_Cell_DataType::TYPE_STRING);
        }
        $list_datas = element('list_datas', $data);

        $master_item_arr = element('master_item_arr', $data);

        $soldout_process_type = element('soldout_process_type', $data);

        $worker_arr = element('worker_arr', $data);

        $line_no = 2;
        $vcode = '';
        foreach ($list_datas as $value){
            $vcode = 'V'.str_pad(element('virtual_item_id', $value), 8, '0', STR_PAD_LEFT);

            $sheet->getCell('A' . $line_no)->setValueExplicit(element('comment',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('B' . $line_no)->setValueExplicit(element('channel_item_code', $value), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('C' . $line_no)->setValueExplicit($vcode, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('D' . $line_no)->setValueExplicit(element('upc',$master_item_arr[element('master_item_id',$value,'')]), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('E' . $line_no)->setValueExplicit(element('item_alias', $value), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('F' . $line_no)->setValueExplicit(element('mfgname',$master_item_arr[element('master_item_id',$value,'')]) , PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('G' . $line_no)->setValueExplicit(element('item_name',$master_item_arr[element('master_item_id',$value,'')]), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('H' . $line_no)->setValueExplicit(element('location',$master_item_arr[element('master_item_id',$value,'')]), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('I' . $line_no)->setValueExplicit(element('stock_status',$value,'')=='N'? '품절' : '품절해제', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('J' . $line_no)->setValueExplicit(element(element('soldout_process_type',$value,''),$soldout_process_type), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('K' . $line_no)->setValueExplicit(element(element('process_worker_id',$value,''),$worker_arr,'시스템'), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('L' . $line_no)->setValueExplicit(element('create_date',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);
            if(element('status', $data)=='2'){
                $sheet->getCell('M' . $line_no)->setValueExplicit(element('error_message',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);
            }
            $line_no++;

        }

        foreach(range('A','L') as $columnID) {
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
}
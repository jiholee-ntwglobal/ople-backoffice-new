<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-06-13
 * Time: 오전 11:47
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Single_item extends CI_Controller
{
    private $api_key;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/master_item_model');
        $this->load->model('item/soldout_history_model');
        $this->load->model('user/ntics_user_model');
        $this->load->model('item/channel_item_info_model');
        $this->load->model('process/process_scheduling');

        // 매핑 수정/삭제 노출 작업자 체크
        $this->mapping_worker_ids = $this->config->item('qten_mapping_ids');

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
        $data['create_date_arr'] =$data['list_datas'] = $filter = $ntics_filter = array();

        $data['search_date'] = element('date',$_GET,'');

        $data['channel_id'] = element('channel',$_GET,'');

        $titlechannel = '';
        if($data['channel_id']=='4' || $data['channel_id']=='6' || $data['channel_id']=='2'){;
            $titlechannel='A';
        }else{
            $titlechannel='G';
        }
        $data['upc'] = trim(element('upc',$_GET,''));

        $data['brand'] = trim(element('brand', $_GET, ''));

		$data['vcode'] = trim(element('vcode', $_GET, ''));

		$data['channel_item_code'] = trim(element('channel_item_code', $_GET, ''));

        $channel_filter['master_id'] = $this->session->userdata("qten_master_id");
        $channel_result = $this->channel_info_model->getNewChannelInfos($channel_filter);

        foreach ($channel_result->result_array() as $channel_info){

            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;

        }

        $date_result = $this->channel_item_info_model->getChannelItemInfos(array('distincts'=>'on'),"date_format(i.create_date, '%Y-%m-%d') create_date",array('i.create_date'=>'desc'));

        foreach ($date_result ->result_array() as $date_info){

            array_push($data['create_date_arr'],$date_info);

        }

        if($data['upc']!=''){

            $ntics_filter['upc_where_in'] = array();

            $split_search_values = explode(PHP_EOL, $this->input->get('upc'));

            foreach ($split_search_values as $split_search_value){

                $string = preg_replace("/[^0-9]/",'', $split_search_value);

                if($string != '') array_push($ntics_filter['upc_where_in'], $string);
            }

        }

		if($data['channel_item_code']!=''){

			$filter['channel_item_code_in'] = array();

			$split_search_values = explode(PHP_EOL, $this->input->get('channel_item_code'));

			foreach ($split_search_values as $split_search_value){

				$string = preg_replace("/[^B0-9]/",'', $split_search_value);

				if($string != '') array_push($filter['channel_item_code_in'], $string);
			}

        }

		if($data['vcode']!=''){
			$filter['virtual_item_id_in'] = array();

			$split_search_values = explode(PHP_EOL, $this->input->get('vcode'));

			foreach ($split_search_values as $split_search_value){

				$string = str_replace("V","",preg_replace("/\s/",'', $split_search_value));
				if($string != '') {
					array_push($filter['virtual_item_id_in'], (int)$string);
				}
			}

        }


        if($data['brand']!=''){
            $ntics_filter['search_brand'] = $data['brand'];
        }

        if(count($ntics_filter)>0){
            $filter['single_master_item_id_in'] = array();
            $master_item_search_result=$this->master_item_model->getMasterItems($ntics_filter);
            foreach ($master_item_search_result->result_array() as $master_item_search_data){

                array_push($filter['single_master_item_id_in'], element('master_item_id', $master_item_search_data));

            }

            if(empty($filter['single_master_item_id_in'])) array_push($filter['single_master_item_id_in'], '');
        }

        $select = 'i.item_info_id, b.comment, i.channel_item_code, i.discount_price, i.discount_unit, i.channel_id, v.virtual_item_id, item_alias, i.create_date, vd.master_item_id, i.origin_price, i.upload_price, i.worker_id, stock_status, need_update, c.channel_code';

        if($data['channel_id'] != '') $filter['channel_id'] = $data['channel_id'];

        if($data['search_date'] != '') $filter['yyyy-mm-dd'] = $data['search_date'];

        $data['page_per_list'] = $this->input->get('page_per_list');

        if($data['page_per_list'] == '') $data['page_per_list'] = 100;

        $data['page'] = $this->input->get('page');

        if($data['page'] == '') $data['page'] = 1;

        $filter['single_limit'] = array($data['page_per_list'], ($data['page'] - 1) * $data['page_per_list']);

        if($this->input->get('excel') == 'Y') {
            unset($filter['single_limit']);
        }
        $this->load->library('pagination');

        $paging_config['base_url'] = site_url('item/single_item');
        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str(element('query', $url), $params);
        if (isset($params['page'])) unset($params['page']);
        $paging_config['base_url'] .= '?' . http_build_query($params);

        $filter['single_item'] = 'single_item';

       $filter['master_id'] = $this->session->userdata("qten_master_id");


        $data['total_count'] = $this->channel_item_info_model->getChannelItemInfosCount($filter, "count(distinct(i.channel_item_code)) cnt");

        $paging_config['total_rows'] = $data['total_count'];
        $paging_config['num_links'] = 5;
        $paging_config['per_page'] = $data['page_per_list'];
        $paging_config['use_page_numbers'] = TRUE;
        $paging_config['page_query_string'] = TRUE;
        $paging_config['query_string_segment'] = 'page';

        $this->pagination->initialize($paging_config);

        $data['paging_content'] = $this->pagination->create_links();

        $date_result = $this->channel_item_info_model->getChannelItemInfos($filter, $select, array('i.create_date'=>'desc', 'i.item_info_id'=>'desc'));

        foreach ($date_result ->result_array() as $date_info){

            array_push($data['list_datas'],$date_info);
        }

        $data['item_history_arr'] = array();

        if (count($data['list_datas']) > 0) {

            $item_info_ids = array_column($data['list_datas'], 'item_info_id');
            $item_info_ids_arr = array_chunk($item_info_ids,1000);

            foreach ($item_info_ids_arr as $item_info_ids2){
                $item_history_result = $this->channel_item_info_model->getChannelItemPriceHistory("h.*", array('item_info_id_in' => $item_info_ids2), array('h.item_info_id' => "desc", 'h.create_date' => "asc"));

                foreach ($item_history_result->result_array() as $item_history_data) {
                    $data['item_history_arr'][element('item_info_id', $item_history_data)] = element('create_date', $item_history_data);
                }
            }

        }

        $data['master_item_arr'] = array();
		$mfgname_arr = array();// 브랜드명 수집 : @q10_brandcode_20200304

        if(count($data['list_datas']) > 0){

            $master_item_ids = array_column($data['list_datas'], 'master_item_id');
            $master_item_ids_arr = array_chunk($master_item_ids,1000);

            foreach ($master_item_ids_arr as $master_item_ids2){
                $master_item_result = $this->master_item_model->getMasterItems(array('master_item_id_in' => $master_item_ids2));

                foreach ($master_item_result->result_array() as $master_item_data){
                    $data['master_item_arr'][element('master_item_id', $master_item_data)] = $master_item_data;
					$mfgname_arr[] = $master_item_data['mfgname'];// @q10_brandcode_20200304
                }
            }

			// 아젠테 브랜드 코드 리스트 가져오기 : @q10_brandcode_20200304
			if ( count($mfgname_arr) > 0 )
			{
				array_unique($mfgname_arr);
				$data['mfgname_arr'] = $this->channel_item_info_model->getAgenteBrandcode($mfgname_arr);
			}
        }

        if($this->input->get('excel') == 'Y') {

            $field_arr = array(
                "채널"              => "comment"
            ,   "상품코드"          => "channel_item_code"
            ,   "VCODE"             => "virtual_item_id"
            ,   "상품갯수"          => "item_alias"
            ,   "브랜드"            => "mfgname"
			,   "브랜드코드(아젠테)"=> "mfgname_code"	// 아젠테 브랜드 코드 필드추가 : @q10_brandcode_20200304
            ,   "상품명"            => "item_name"
            ,   "가격"              => "upload_price"
            ,   "판매자 할인"       => "discount_price"
            ,   "판매자 할인단위"   => "discount_type"
            ,   "품절여부"          => "stockfg"
            ,   "로케이션"          => "location"
            ,   "등록날짜"          => "create_date"
            ,   "가격조정 수정날짜" => "price_update_date"
            ,   "최초등록자"        => "worker_id"
            );

            $this->download_Excel('큐텐_'.$this->session->userdata("qten_master_id").'('.$titlechannel.')_단품' , $field_arr , $data);
            exit;
        }

        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('qten_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true)
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        //매핑 수정/삭제 노출 작업자
        $data['worker_ids_fg'] = (in_array($this->session->userdata('qten_manager_user_id'), $this->mapping_worker_ids)) ? 1: 0;

        $this->load->view('common/header', $header_data);
        $this->load->view('item/single_item_list', $data);
        $this->load->view('common/footer', $footer_data);

    }

    public function soldoutList($stock_status="N"){

//        if($_SERVER['REMOTE_ADDR']=='211.214.213.101'){
//            $this->output->enable_profiler(TRUE);
//        }

        $data['list_datas'] = $filter = $ntics_filter = array();

        $data['search_date'] = element('date',$_GET,'');

        $data['channel_id'] = element('channel',$_GET,'');

        $titlechannel = '';
        if($data['channel_id']=='4' || $data['channel_id']=='6' || $data['channel_id']=='2'){;
            $titlechannel='A';
        }else{
            $titlechannel='G';
        }
        $data['upc'] = trim(element('upc',$_GET,''));

        $data['brand'] = trim(element('brand', $_GET, ''));

        $data['vcode'] = trim(element('vcode', $_GET, ''));

        $data['channel_item_code'] = trim(element('channel_item_code', $_GET, ''));

        $channel_filter['master_id'] = $this->session->userdata("qten_master_id");
        $channel_result = $this->channel_info_model->getNewChannelInfos($channel_filter);

        foreach ($channel_result->result_array() as $channel_info){

            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;

        }

        /** 검색 조건 **/
        if($data['upc']!=''){

            $ntics_filter['upc_where_in'] = array();

            $split_search_values = explode(PHP_EOL, $this->input->get('upc'));

            foreach ($split_search_values as $split_search_value){

                $string = preg_replace("/[^0-9]/",'', $split_search_value);

                if($string != '') array_push($ntics_filter['upc_where_in'], $string);
            }

        }

        if($data['channel_item_code']!=''){

            $filter['channel_item_code_in'] = array();

            $split_search_values = explode(PHP_EOL, $this->input->get('channel_item_code'));

            foreach ($split_search_values as $split_search_value){

                $string = preg_replace("/[^B0-9]/",'', $split_search_value);

                if($string != '') array_push($filter['channel_item_code_in'], $string);
            }

        }

        if($data['vcode']!=''){
            $filter['virtual_item_id_in'] = array();

            $split_search_values = explode(PHP_EOL, $this->input->get('vcode'));

            foreach ($split_search_values as $split_search_value){

                $string = str_replace("V","",preg_replace("/\s/",'', $split_search_value));
                if($string != '') {
                    array_push($filter['virtual_item_id_in'], (int)$string);
                }
            }

        }

        if($data['brand']!=''){
            $ntics_filter['search_brand'] = $data['brand'];
        }
        /** 검색 조건 **/

        if(count($ntics_filter)>0){
            $filter['single_master_item_id_in'] = array();
            $master_item_search_result=$this->master_item_model->getMasterItems($ntics_filter);
            foreach ($master_item_search_result->result_array() as $master_item_search_data){

                array_push($filter['single_master_item_id_in'], element('master_item_id', $master_item_search_data));

            }

            if(empty($filter['single_master_item_id_in'])) array_push($filter['single_master_item_id_in'], '');
        }

        $select = 'i.item_info_id, b.comment, i.channel_item_code, i.discount_price, i.discount_unit, i.channel_id, v.virtual_item_id, item_alias, i.create_date, vd.master_item_id, i.origin_price, i.upload_price, i.worker_id, stock_status, need_update, c.channel_code';

        if($data['channel_id'] != '') $filter['channel_id'] = $data['channel_id'];

        if($data['search_date'] != '') $filter['yyyy-mm-dd'] = $data['search_date'];

        $data['page_per_list'] = $this->input->get('page_per_list');

        if($data['page_per_list'] == '') $data['page_per_list'] = 100;

        $data['page'] = $this->input->get('page');

        if($data['page'] == '') $data['page'] = 1;

        $filter['single_limit'] = array($data['page_per_list'], ($data['page'] - 1) * $data['page_per_list']);

        if($this->input->get('excel') == 'Y') {
            unset($filter['single_limit']);
        }
        $this->load->library('pagination');

        $paging_config['base_url'] = site_url('item/single_item');
        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str(element('query', $url), $params);
        if (isset($params['page'])) unset($params['page']);
        $paging_config['base_url'] .= '?' . http_build_query($params);



        $filter['single_item'] = 'single_item';

        $filter['master_id'] = $this->session->userdata("qten_master_id");

        $filter['need_update'] = "Y";

        $filter['stock_status'] = $stock_status;

        $data['stock_status'] =$stock_status;

        $data['total_count'] = $this->channel_item_info_model->getChannelItemInfosCount($filter, "count(distinct(i.channel_item_code)) cnt");

        $paging_config['total_rows'] = $data['total_count'];
        $paging_config['num_links'] = 5;
        $paging_config['per_page'] = $data['page_per_list'];
        $paging_config['use_page_numbers'] = TRUE;
        $paging_config['page_query_string'] = TRUE;
        $paging_config['query_string_segment'] = 'page';

        $this->pagination->initialize($paging_config);

        $data['paging_content'] = $this->pagination->create_links();
        $filter['master_info'] = "";
        $select = 'i.item_info_id, b.comment, i.channel_item_code, i.discount_price, i.discount_unit, i.channel_id, v.virtual_item_id, item_alias, i.create_date, vd.master_item_id, i.origin_price, i.upload_price, i.worker_id, stock_status, need_update, c.channel_code, m.*';


        $date_result = $this->channel_item_info_model->getChannelItemInfos($filter, $select, array('i.virtual_item_id'=>'asc','i.create_date'=>'desc', 'i.item_info_id'=>'desc'));

        foreach ($date_result ->result_array() as $date_info){

            array_push($data['list_datas'],$date_info);
        }

        $data['item_history_arr'] = array();

        if (count($data['list_datas']) > 0) {

            $item_info_ids = array_column($data['list_datas'], 'item_info_id');
            $item_info_ids_arr = array_chunk($item_info_ids,1000);

            foreach ($item_info_ids_arr as $item_info_ids2){
                $item_history_result = $this->channel_item_info_model->getChannelItemPriceHistory("h.*", array('item_info_id_in' => $item_info_ids2), array('h.item_info_id' => "desc", 'h.create_date' => "asc"));

                foreach ($item_history_result->result_array() as $item_history_data) {
                    $data['item_history_arr'][element('item_info_id', $item_history_data)] = element('create_date', $item_history_data);
                }
            }

        }

        $data['master_item_arr'] = array();

        if(count($data['list_datas']) > 0){

            $master_item_ids = array_column($data['list_datas'], 'master_item_id');
            $master_item_ids_arr = array_chunk($master_item_ids,1000);

            foreach ($master_item_ids_arr as $master_item_ids2){
                $master_item_result = $this->master_item_model->getMasterItems(array('master_item_id_in' => $master_item_ids2));

                foreach ($master_item_result->result_array() as $master_item_data){
                    $data['master_item_arr'][element('master_item_id', $master_item_data)] = $master_item_data;
                }
            }

        }

        if($this->input->get('excel') == 'Y') {

//            $field_arr = array(
//                "채널"		=> "comment"
//            ,	"상품코드"		=> "channel_item_code"
//            ,	"VCODE"		=> "virtual_item_id"
//            ,	"상품갯수"		=> "item_alias"
//            ,	"가격"		=> "upload_price"
//            ,	"품절여부"		=> "stockfg"
//            );

            $field_arr = array(
                "채널"		=> "comment"
            ,	"상품코드"		=> "channel_item_code"
            ,	"VCODE"		=> "virtual_item_id"
            ,	"상품갯수"		=> "item_alias"
            ,	"가격"		=> "upload_price"
            ,	"품절여부"		=> "stockfg"
            ,	"NTICS QTY"		=> "currentqty"
            );

            $this->download_Excel($this->session->userdata("qten_master_id").'('.$titlechannel.')_'. ($stock_status=="Y")? "판매중지" : "판매개시" , $field_arr , $data);
            exit;
        }

        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('qten_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true)
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        $this->load->view('common/header', $header_data);
        $this->load->view('item/soldout_manage_list', $data);
        $this->load->view('common/footer', $footer_data);
    }

    public function soldoutAction(){

        $item_info_ids = $this->input->post('item_info_ids');
        $action_txt = ($this->input->post("stock_status")=="Y") ? "품절" : "품절해제";
        if($this->input->post("stock_status")=="Y"){  //품절대상


            foreach ($item_info_ids as $item_info_id){
                $item_info = $this->channel_item_info_model->getItemInfo(array("item_info_id"=>$item_info_id));

                //update
                $this->channel_item_info_model->updateChannelItemInfo(array("need_update"=>"N", "stock_status"=>"N"), array("item_info_id"=>$item_info_id, "stock_status"=>"Y"));
                //history add
                $add_data	= array(
                    'item_info_id'			=> element('item_info_id',$item_info)
                ,	'stock_status'			=> "N"
                ,	'currentqty'			=> ""
                ,	'soldout_process_type'	=> '3'
                ,	'process_worker_id'		=> $this->session->userdata('qten_worker_id')
                ,	'create_date'			=> date('Y-m-d H:i:s')
                );
                $this->soldout_history_model->addSoldoutHistory($add_data);
            }


        }else if($this->input->post("stock_status")=="N"){ //판매대상

            foreach ($item_info_ids as $item_info_id){
                $item_info = $this->channel_item_info_model->getItemInfo(array("item_info_id"=>$item_info_id));

                //update
                $this->channel_item_info_model->updateChannelItemInfo(array("need_update"=>"N", "stock_status"=>"Y"), array("item_info_id"=>$item_info_id, "stock_status"=>"N"));
                //history add
                $add_data	= array(
                    'item_info_id'			=> element('item_info_id',$item_info)
                ,	'stock_status'			=> "Y"
                ,	'currentqty'			=> ""
                ,	'soldout_process_type'	=> '3'
                ,	'process_worker_id'		=> $this->session->userdata('qten_worker_id')
                ,	'create_date'			=> date('Y-m-d H:i:s')
                );
                $this->soldout_history_model->addSoldoutHistory($add_data);

            }

        }

        alert($action_txt.'처리가 완료되었습니다',site_url('/item/single_item/soldoutList/'.$this->input->post("stock_status")) );


    }

    private function dowloadExcel($data)
    {
        $this->load->library('Excel');

        $objPHPExcel = new PHPExcel();
        $excel_title = '이베이_단품_' . date('Y-m-d');
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
        $sheet->getCell('H1')->setValueExplicit('가격', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('I1')->setValueExplicit('로케이션', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('J1')->setValueExplicit('NTICS QTY', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('K1')->setValueExplicit('등록날짜', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('L1')->setValueExplicit('가격조정 수정날짜', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('M1')->setValueExplicit('최초등록자', PHPExcel_Cell_DataType::TYPE_STRING);

        $list_datas = element('list_datas', $data);

        $master_item_arr = element('master_item_arr', $data);

        $line_no = 2;

        foreach ($list_datas as $value){


            $sheet->getCell('A' . $line_no)->setValueExplicit(element('comment',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('B' . $line_no)->setValueExplicit(element('channel_item_code', $value), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('C' . $line_no)->setValueExplicit("V".str_pad(element('virtual_item_id', $value),8,0,STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('D' . $line_no)->setValueExplicit(element('upc',$master_item_arr[element('master_item_id',$value,'')]), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('E' . $line_no)->setValueExplicit(element('item_alias', $value), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('F' . $line_no)->setValueExplicit(element('mfgname',$master_item_arr[element('master_item_id',$value,'')]) , PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('G' . $line_no)->setValueExplicit(element('item_name',$master_item_arr[element('master_item_id',$value,'')]), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('H' . $line_no)->setValueExplicit(element('upload_price',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('I' . $line_no)->setValueExplicit(element('location',$master_item_arr[element('master_item_id',$value,'')]), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('J' . $line_no)->setValueExplicit(element('currentqty',$master_item_arr[element('master_item_id',$value,'')]), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('K' . $line_no)->setValueExplicit(element('create_date',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('L' . $line_no)->setValueExplicit(element('price_upload_date',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('M' . $line_no)->setValueExplicit(element('worker_id',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);

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

    private function download_Excel($title,$field, $data){

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

        $list_datas = element('list_datas', $data);
        $channel_arr = element('channel_arr', $data);
        $master_item_arr = element('master_item_arr', $data);
        $item_history_arr = element('item_history_arr' , $data);
		$mfgname_arr = element('mfgname_arr', $data);	// @q10_brandcode_20200304
        $line_no = 2;

        foreach ($list_datas as $list_data){

            $string = "A";

            foreach ($field as $field_key=>$field_val) {
                switch ($field_val){
                    case 'virtual_item_id':
                        $excel_val = "V".str_pad(element('virtual_item_id', $list_data),"8","0",STR_PAD_LEFT);
                        break;
                    case 'action_fg':
                        $excel_val = (element('action_fg', $list_data)==1) ? "수동조정" : "일괄조정";
                        break;
                    case 'discount_type':
                        if(element('discount_unit',$list_data,'')=="N" || element('discount_unit',$list_data,'')=="") $excel_val = "할인 없음" ;
                        if(element('discount_unit',$list_data,'')=="Rate") $excel_val = "비율로 할인" ;
                        if(element('discount_unit',$list_data,'')=="Money") $excel_val = "금액으로 할인" ;
                        break;
                    case 'channel_id':
                        $excel_val = $channel_arr[element('channel_id', $list_data)]['comment'];
                        break;
                    case 'mfgname':
                        $excel_val = (element('master_item_id',$list_data,'')=="" || element('master_item_id',$list_data,'')==null) ? "VCODE 비동기화" : element('mfgname',$master_item_arr[element('master_item_id',$list_data,'')]);
                        break;
					case 'mfgname_code':// 아젠테 브랜드 코드 필드추가 : @q10_brandcode_20200304
                        $mfgname = (element('master_item_id',$list_data,'')=="" || element('master_item_id',$list_data,'')==null) ? "VCODE 비동기화" : element('mfgname',$master_item_arr[element('master_item_id',$list_data,'')]);
						$bn_en = strtolower($mfgname);
						$bn_en = str_replace("'", '', $bn_en);
						$bn_en = str_replace(" ", '', $bn_en);
						$excel_val = isset($mfgname_arr[$bn_en]) === false ? '' : $mfgname_arr[$bn_en];
                        break;
                    case 'item_name':
                        $excel_val = (element('master_item_id',$list_data,'')=="" || element('master_item_id',$list_data,'')==null) ? "VCODE 비동기화" : element('item_name',$master_item_arr[element('master_item_id',$list_data,'')]);
                        break;
                    case 'discount_type':
                        break;
                    case 'stockfg':
                        $excel_val = element('stock_status',$list_data,'') =='N' ?'품절중':'판매중';
                        $excel_val .= element('need_update',$list_data,'') =='E'?'오류':'';
                        break;
                    case 'location':
                        $excel_val = (element('master_item_id',$list_data,'')=="" || element('master_item_id',$list_data,'')==null) ? "VCODE 비동기화" : element('location',$master_item_arr[element('master_item_id',$list_data,'')]);

                        break;
                    case 'price_update_date':
                        $excel_val = element(element('item_info_id', $list_data, ''),$item_history_arr);
                        break;
                    case 'TradeStatus':
                        if(element('TradeStatus',$list_data,'')=="Active") $excel_val = "거래가능";
                        if(element('TradeStatus',$list_data,'')=="Restricted") $excel_val = "제한상품";
                        break;
                    case 'OrderTypeCode':
                        if(element('OrderTypeCode',$list_data,'')=="BuyerDescriptive") $excel_val = "구매자 작성형";
                        if(element('OrderTypeCode',$list_data,'')=="BuyerSelective") $excel_val = "2개 조합형";
                        if(element('OrderTypeCode',$list_data,'')=="Calculation") $excel_val = "계산형";
                        if(element('OrderTypeCode',$list_data,'')=="Ignore") $excel_val = "주문선택사항 수정안함";
                        if(element('OrderTypeCode',$list_data,'')=="Mixed") $excel_val = "혼합형(2개 조합형 + 구매자작성형)";
                        if(element('OrderTypeCode',$list_data,'')=="NotAvailable") $excel_val = "선택 사항 없음";
                        if(element('OrderTypeCode',$list_data,'')=="StandAlone") $excel_val = "일반형";
                        if(element('OrderTypeCode',$list_data,'')=="StandAloneCalculation") $excel_val = "일반형 + 계산형";
                        if(element('OrderTypeCode',$list_data,'')=="StandAloneMixed") $excel_val = "일반형 + 작성형";
                        if(element('OrderTypeCode',$list_data,'')=="ThreeCombination") $excel_val = "	3개 조합형";
                        if(element('OrderTypeCode',$list_data,'')=="ThreeCombination") $excel_val = "	3개 조합형 + 작성형";
                        break;
                    case 'OptionTypeCode':
                        if(element('OptionTypeCode',$list_data,'')=="Availabe") $excel_val = "사용";
                        if(element('OptionTypeCode',$list_data,'')=="Available") $excel_val = "사용 함(재고관리안함)";
                        if(element('OptionTypeCode',$list_data,'')=="AvailableLimitedStock") $excel_val = "사용 함(재고관리함)";
                        if(element('OptionTypeCode',$list_data,'')=="NotAvailable") $excel_val = "사용 안함";
                        break;
                    case 'SellingStatusCode':
                        if(element('SellingStatusCode',$list_data,'')=="All") $excel_val = "ALL";
                        if(element('SellingStatusCode',$list_data,'')=="Block") $excel_val = "	직권 중지";
                        if(element('SellingStatusCode',$list_data,'')=="OnSale") $excel_val = "판매 진행";
                        if(element('SellingStatusCode',$list_data,'')=="Pause") $excel_val = "일시 중지";
                        if(element('SellingStatusCode',$list_data,'')=="Stop") $excel_val = "판매 중지";
                        break;
                    case 'ShippingCostChargeCode':
                        if(element('ShippingCostChargeCode',$list_data,'')=="Free") $excel_val = "무료";
                        if(element('ShippingCostChargeCode',$list_data,'')=="MultiConditional") $excel_val = "금액 기준";
                        if(element('ShippingCostChargeCode',$list_data,'')=="NotAvailable") $excel_val = "사용";
                        if(element('ShippingCostChargeCode',$list_data,'')=="PayOnArrival") $excel_val = "착불";
                        if(element('ShippingCostChargeCode',$list_data,'')=="SellerConditional") $excel_val = "판매자 조건부";
                        if(element('ShippingCostChargeCode',$list_data,'')=="SingleConditional") $excel_val = "단품 조건부";
                        break;
                    case 'ShippingFeeChargeType':
                        if(element('ShippingFeeChargeType',$list_data,'')=="Amount") $excel_val = "금액별차등";
                        if(element('ShippingFeeChargeType',$list_data,'')=="Fix") $excel_val = "정액";
                        if(element('ShippingFeeChargeType',$list_data,'')=="Free") $excel_val = "무료";
                        if(element('ShippingFeeChargeType',$list_data,'')=="NotAvailable") $excel_val = "";
                        if(element('ShippingFeeChargeType',$list_data,'')=="Volume") $excel_val = "수량별차등";

                        break;
                    case 'ShippingFeeType':
                        if(element('ShippingFeeType',$list_data,'')=="Free") $excel_val = "무료";
                        if(element('ShippingFeeType',$list_data,'')=="ItemShipping") $excel_val = "단일상품 배송비";
                        if(element('ShippingFeeType',$list_data,'')=="None") $excel_val = "";
                        if(element('ShippingFeeType',$list_data,'')=="SellerShipping") $excel_val = "판매자 묶음 배송비";
                        break;
                    case 'Premium':
                    case 'PremiumPlus':
                    case 'Recommend':
                        if(element($field_val,$list_data,'')== -1) $excel_val = "";
                    break;
                    case 'LastUpdateDate':
                    case 'ItemRegistDate':
                    case 'ListingBeginDateReservation':
                    case 'ListingEndDate':
                    case 'ListingBeginDate':
                    case 'ListingEndDateReservation':
                        $time = new DateTime(element($field_val, $list_data));

                        $excel_val = $time->format('Y-m-d H:i:s');

                        break;
                    case 'SellPrice':
                        $excel_val = (element($field_val,$list_data,'')<=0 && element($field_val,$list_data,'')==0) ? "판매중지 상태값의 상품번호는 판매가격이 0원으로 노출됩니다." : number_format(element($field_val,$list_data,''));
                        break;
                    case 'SellingPrice':
                        $excel_val = number_format(element($field_val,$list_data,''));
                        break;
                    case 'OutItemNo':
                        $excel_val = (element($field_val,$list_data,'')=="") ? "옵션 또는 단품 중 코드 미입력 건입니다." : element($field_val,$list_data,'');
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
	
	public function priceUpdateSync()
	{
		$this->output->set_content_type('application/json')->set_header('Access-Control-Allow-Origin:*');
		
		//$this->output->set_output(json_encode($return_data));
		
		// formdata check


		if(element('channel_item_code',$_POST,false)===false){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품번호가 없습니다')));
        }

        if(!is_numeric(element('basic_price',$_POST,''))){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품 기본가격 오류')));
        }

        $param = $this->input->post();

        $item_info	= $this->channel_item_info_model->getItemInfo(array('channel_item_code'=>$param['channel_item_code']));
        if(element('channel_item_code',$item_info, false) === false){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품번호가 없습니다')));
        }

        if($item_info['item_info_id'] != $param['item_info_id']){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품번호가 없습니다')));
        }

        $channel_id_info = $this->channel_info_model->getNewChannelInfo(array('channel_id' => $item_info['channel_id']));


        if(element('channel_id',$channel_id_info, false) === false){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품번호가 없습니다')));
        }

        $channel_item_code = $param['channel_item_code'];

        $price_info = array(
            'discount_type'=>$param['discount_type'],
            'discount_value'=>$param['discount_value'],
            'basic_price'=>$param['basic_price']
        );

        $this->api_key = $channel_id_info['api_key'];

        $result  = false;
        switch ($channel_id_info['channel_code']) {

            case 'G' : //fastople gmarket
                $result = $this->sendPrice($channel_item_code, element('basic_price', $price_info));

                if (element('discount_type', $price_info) != 'N') {
                    $result = $this->sendDiscountPrice($channel_item_code, $price_info);
                } else {

                    $price_info = array(
                        'discount_type' => 'Rate',//임의의값
                        'discount_value' => '0',
                        'basic_price' => $param['basic_price']
                    );

                    $result = $this->sendDiscountPrice($channel_item_code, $price_info);
                }
                break;

            case 'A' : //fastople auction
                $result = $this->callAuctionPriceUpdate($channel_item_code, $price_info);
                break;
        }


        if($result===false){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '업데이트 오류')));
        }

        $update_date = array(
            'upload_price' => $param['basic_price'],
            'discount_price' => $param['discount_value'],
            'discount_unit'=> $param['discount_type']
        );

        if (element('channel_code', $channel_id_info) == 'G' && element('stock_status', $item_info) == 'N') {
            //$update_date['need_update'] = 'Y';

            $this->callAddPrice($item_info);
        }

        $filter= array(
            'item_info_id' => $param['item_info_id']
        );

        $this->channel_item_info_model->updateChannelItemInfo($update_date,$filter);


        //가격조정 히스토리 쌓기
        $history_data = array(
            'item_info_id' => $param['item_info_id'],
            'channel_item_code' => $param['channel_item_code'],
            'upload_price' => $param['basic_price'],
            'discount_unit' => $param['discount_type'],
            'discount_price' => $param['discount_value'],
            'action_fg' => 1,
            'worker_id' => $this->session->userdata('qten_worker_id'),
            'create_date' => date('Y-m-d H:i:s'),
        );

        $this->channel_item_info_model->addChannelItemPriceHistory($history_data);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'Success')));
//		$price_info		= array();
//		// A = Update And Sync, U = Update only
//		$action_type	= element('action_type',$param,'');
//
//		$item_info_id	= element('item_info_id',$param,'');
//
//		if(element('basic_price',$param,'') != '') {
//			$price_info = array(
//				'basic_price'	=> element('basic_price', $param, '')
//			,	'discount_type'	=> element('discount_type', $param, 'N')
//			,	'discount_value'=> element('discount_value', $param, '')
//			);
//		}
//
//		if(count($price_info) > 0){
//			// update channel_item_info SET upload_price='', discount_price='', discount_unit='' WHERE item_info_id=
//			// $this->channel_item_info_model->channelItemUpdate(
//			//		array(
//			//		'item_info_id'	=> $item_info_id
//			//		)
//			//	,	array(
//			//		'upload_price'		=> element('basic_price', $price_info)
//			//		'discount_price'	=> element('discount_value', $price_info)
//			//		'discount_unit'		=> element('discount_type', $price_info)
//			//		)
//			//	);
//		}
//
//		// load data
//		// $item_info	= $this->channel_item_info_model->getItemInfo($item_info_id);
//
//		// price sync
//		// $price_result	= $this->sendPrice(element('channel_item_code', $item_info), element('basic_price', $price_info));
//		// $discount_result	= $this->sendDiscountPrice(element('channel_item_code', $item_info), $price_info);
//
//		// return output;
	}

	//api
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    private function callAuctionPriceUpdate($channel_item_code, $price_info)
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
            ,	array($this->api_key, $channel_item_code, element('basic_price',$price_info), $seller_discount)
            ,	$item_price_dummy
        );
        $serverUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
        $action			= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItem";

        $sync_result = requestAuction($serverUrl, $action, $requestXmlBody);
        return (element('result',$sync_result) == 'Success') ? true : false;

    }

	private function sendPrice($channel_item_code, $price)
	{
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
        //$this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
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
	
	private function sendDiscountPrice($channel_item_code, $price_info)
	{
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
		//$this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';

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
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function priceForm($item_info_id ='')
	{

		if($item_info_id == ''){

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품번호가 없습니다')));
		}

		$item_info	= $this->channel_item_info_model->getItemInfo(array('item_info_id'=>$item_info_id));
		if(element('channel_item_code',$item_info, false) === false){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품번호가 없습니다')));
		}

		$data	= array(
			'channel_item_code'	=> $item_info['channel_item_code']
		,	'item_info_id'		=> $item_info_id
		,	'upload_price'		=> $item_info['upload_price']
		,	'discount_price'	=> $item_info['discount_price']
		,	'discount_unit'		=> $item_info['discount_unit']
		);

		//upload_price, discount_price, discount_unit
		
//		$this->load->view('item/item_price_form_modal', $item_info);
		$this->load->view('item/item_price_form_modal', $data);
	}


	//매핑 정보 변경폼
	public function InfoUpdateForm($item_info_id =''){

        if(!in_array($this->session->userdata('qten_manager_user_id'), $this->mapping_worker_ids))
        {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '처리할 수 있는 권한을 가지고 있지 않습니다. 로그인 정보를 다시 확인해주세요.')));
        }

        if($item_info_id == ''){

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품번호가 없습니다')));
        }

        $item_info	= $this->channel_item_info_model->getItemInfo(array('item_info_id'=>$item_info_id));

        if(element('channel_item_code',$item_info, false) === false){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'false', 'msg' => '상품번호가 없습니다')));
        }

        $data	= array(
            'channel_item_code'	=> element('channel_item_code',$item_info)
        ,	'item_info_id'		=> $item_info_id
        ,	'virtual_item_id'		=> element('virtual_item_id',$item_info)
        ,   'channel_id'          => element('channel_id', $item_info)
        );

        $this->load->view('item/item_info_update_form', $data);
    }

    //매핑 정보 변경
    public function infoUpdateSync(){

        if(!in_array($this->session->userdata('qten_manager_user_id'), $this->mapping_worker_ids))
        {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리할 수 있는 권한을 가지고 있지 않습니다. 로그인 정보를 다시 확인해주세요.')));
        }

        $item_info_id = $this->input->post('item_info_id');
        $virtual_item_id = (int) str_replace("v","",str_replace("V","", $this->input->post('virtual_item_id')));


        if($item_info_id =="" || $virtual_item_id == "")
        {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '필요한 정보가 누락되었습니다. 다시 시도해주세요.')));

            exit;
        }

        $item_info	= $this->channel_item_info_model->getItemInfo(array('item_info_id'=>$item_info_id));

        $history_date = array(
            "item_info_id"  => element('item_info_id', $item_info)
        ,   "history_type"  => 1
        ,   "worker_id"    => $this->session->userdata('qten_worker_id')
        ,   "create_date"  => date('Y-m-d H:i:s')
        ,   "extra_value"  => element('virtual_item_id', $item_info)
        );

        //update
        $this->channel_item_info_model->updateItem(array("virtual_item_id"=>$virtual_item_id), array("item_info_id"=>$item_info_id));

        //히스토리
        $this->channel_item_info_model->addItemInfoHistory($history_date);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '매핑변경이 완료되었습니다.')));

    }

    //매핑 삭제
    public function deleteSingleItem(){

        if(!in_array($this->session->userdata('qten_manager_user_id'), $this->mapping_worker_ids))
        {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리할 수 있는 권한을 가지고 있지 않습니다. 로그인 정보를 다시 확인해주세요.')));
        }

        $item_info_ids = $this->input->post('item_info_ids');

        $item_info_count = $this->channel_item_info_model->getChannelItemInfosCount(array("item_info_id_in"=> $item_info_ids));

        if(count($item_info_ids) != $item_info_count){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '매핑 삭제하려는 상품이 존재하지 않습니다. 다시 확인해주세요.')));

            exit;
        }

        foreach ($item_info_ids as $item_info_id){

            $item_info	= $this->channel_item_info_model->getItemInfo(array('item_info_id'=>$item_info_id));

            $history_date = array(
                "item_info_id"  => $item_info_id
            ,   "history_type"  => 2
            ,   "worker_id"    => $this->session->userdata('qten_worker_id')
            ,   "create_date"  => date('Y-m-d H:i:s')
            ,   "extra_value"  => element('channel_item_code', $item_info)
            );

            //delete
            $this->channel_item_info_model->deleteChannelItemInfo(array("item_info_id"=>$item_info_id));
            //history
             $this->channel_item_info_model->addItemInfoHistory($history_date);

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '매핑삭제가 완료되었습니다.')));

    }

    //상품수동등록 상품폼
	public function insertSingleItemForm(){

        $data = array();

        $channel_filter['master_id'] = $this->session->userdata("qten_master_id");
        $channel_result = $this->channel_info_model->getNewChannelInfos($channel_filter);


        foreach ($channel_result->result_array() as $channel_info){

            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;

        }
        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('qten_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true)
        );
        $footer_data = array(
            'left_menu_on' => true
        );
        $this->load->view('common/header', $header_data);
        $this->load->view('item/single_item_insert', $data);
        $this->load->view('common/footer', $footer_data);
    }

    //상품 수동등록
    public function insertSingleItem(){

        $channel_id = $this->input->post('channel_id');
        $channel_item_code = $this->input->post('channel_item_code');
        $channel_item_code = preg_replace("/[^Bb0-9]*/s", "", $channel_item_code);
        $virtual_item_id = (int) str_replace("v","",str_replace("V","", $this->input->post('virtual_item_id')));
        $upload_price = str_replace(",","",$this->input->post('upload_price'));
        $orgin_price = (($upload_price/100) * 85);
        $discount_unit = $this->input->post('discount_unit');
        $discount_price = $this->input->post('discount_price');

        if($channel_id == "" || $channel_item_code == "" || $virtual_item_id == "" || $upload_price == "" || $discount_unit == ""){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '필요한 정보가 누락되었습니다. 다시 시도해주세요.')));

            exit;
        }

        $channel_item_count = $this->channel_item_info_model->getChannelItemInfosCount(array("channel_item_code"=>$channel_item_code));

        if($channel_item_count>0){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '이미 등록된 상품입니다.')));
            exit;
        }


        $channel_item_insert_arr = array(
           'channel_id' => $channel_id,
           'channel_item_code' => $channel_item_code,
           'virtual_item_id' => $virtual_item_id,
           'upload_price' => $upload_price,
           "origin_price" => $orgin_price,
           "discount_unit" =>$discount_unit,
           "discount_price" =>$discount_price,
           'create_date' =>"now()",
           "need_update" => "N",
           "sell_status" => "Y",
           "stock_status" =>"Y",
           "worker_id" => $this->session->userdata('qten_manager_name')
        );

        $insert_key = $this->channel_item_info_model->insertItem($channel_item_insert_arr);

        if($insert_key){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '저장이 완료되었습니다.')));
        }

    }

    //상품 수동등록 엑셀 업로드폼
    public function uploadSingleItemFrom(){

        $data = array();

        $channel_filter['master_id'] = $this->session->userdata("qten_master_id");
        $channel_result = $this->channel_info_model->getNewChannelInfos($channel_filter);
        foreach ($channel_result->result_array() as $channel_info){
            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;
        }



        //엑셀 파일 업로드하는 폼

        $this->load->view('item/single_item_upload_form', $data);
    }

    //상품 수동등록 엑셀 업로드
    public function uploadSingleItem(){

        $channel_id = $this->input->post('channel_id');

        if($channel_id == ""){
            alert('채널 정보가 누락되었습니다. 다시 시도해주세요.');
        }

        //엑셀 파일 업로드하는 action
        if(!$_FILES['excel']['tmp_name'] || !file_exists($_FILES['excel']['tmp_name'])){
            alert('파일 업로드 중 오류가 발생하였습니다. 다시 시도 해 주세요.');
            exit;
        }

        $column_num         = 1;
        $upload_time		= date ("YmdHis");

        $config['upload_path']		= "./file/single_item/";// './file/tmp/'.$upload_time_yyyy."/".$upload_time_mm."/";
        $config['overwrite']		= true;
        $config['encrypt_name']		= false;
        $config['max_filename']		= 0;
        $config['allowed_types']	= 'xls|xlsx';
        $config['file_name']		= 'single_item_'.$upload_time;


        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel')) {
            alert('업로드에 실패하였습니다.\n'.$this->upload->display_errors());
            exit;
        }

        $file_info = $this->upload->data();
        $file_path = $file_info['full_path'];
        if(!file_exists($file_path)){
            alert('파일 업로드 중 오류가 발생하였습니다. 다시 시도 해 주세요.');
            exit;
        }

        // data action
        $this->load->library('Excel');
        // 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
        $objReader = PHPExcel_IOFactory::createReaderForFile($file_path);
        // 읽기전용으로 설정
        $objReader->setReadDataOnly(true);
        //데이터만 읽기(서식을 모두 무시해서 속도 증가 시킴)
        $objReader->setReadDataOnly(true);
        // 엑셀파일을 읽는다
        $objExcel = $objReader->load($file_path);
        // 첫번째 시트를 선택
        $objExcel->setActiveSheetIndex(0);

        $sheetData	= $objExcel->getActiveSheet()->toArray(null,true,true,true);

        unset($objExcel);

        if(count($sheetData) - $column_num <= 1){
            alert('유효 데이터가 없습니다.');
            exit;
        }

        unset($sheetData[1]);  // 1~2행은 부가설명이라 삭제
        unset($sheetData[2]);

        $fail_id_arr = array();
        $success_id_arr = array();

        foreach ($sheetData as $row) {

            $channel_item_code = element('A', $row); // $this->input->post('channel_item_code');
            $virtual_item_id = (int)str_replace("v", "", str_replace("V", "", element('B', $row)));
            $upload_price = str_replace(",", "", element('C', $row));

            $orgin_price = (($upload_price / 100) * 85);
            $discount_unit = element('D', $row);
            $discount_price = element('E', $row);

            //정보누락 확인
            if($channel_id == "" || $channel_item_code == "" || $virtual_item_id == "" || $upload_price == ""){
                $fail_id_arr[$channel_item_code] = "필수 정보가 누락됐습니다.";
            }else {

                //중복확인
                $channel_item_count = $this->channel_item_info_model->getChannelItemInfosCount(array("channel_item_code" => $channel_item_code));

                if ($channel_item_count > 0) {
                    $fail_id_arr[$channel_item_code] = "이미 등록된 상품코드입니다.";
                } else {

                    // insert
                    $channel_item_insert_arr = array(
                        'channel_id' => $channel_id,
                        'channel_item_code' => $channel_item_code,
                        'virtual_item_id' => $virtual_item_id,
                        'upload_price' => $upload_price,
                        "origin_price" => $orgin_price,
                        "discount_unit" =>$discount_unit,
                        "discount_price"=>$discount_price,
                        'create_date' => "now()",
                        "need_update" => "N",
                        "sell_status" => "Y",
                        "stock_status" => "Y",
                        "worker_id" => $this->session->userdata('qten_manager_name')
                    );

                    $insert_key = $this->channel_item_info_model->insertItem($channel_item_insert_arr);

                    if ($insert_key) {
                        array_push($success_id_arr, $channel_item_code);
                    } else {
                        $fail_id_arr[$channel_item_code] = "개발팀에 문의해주세요.";
                    }
                }
            }
        }
        $data['code_cnt'] = count($sheetData) ;
        $data['fail_id_arr'] = $fail_id_arr;
        $data['success_id_arr'] = $success_id_arr;

        $this->checkUploadSingleItem($data);

    }

    //상품 수동등록 엑셀 업로드시 결과 체크
    public function checkUploadSingleItem($data){
        //올라간 엑셀 파일 체크하는 list -> 단품상품 리스트로 리다이렉트
        if(!$data) return '';

        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('qten_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true)
        );
        $footer_data = array(
            'left_menu_on' => true
        );
        $this->load->view('common/header', $header_data);
        $this->load->view('item/single_item_upload_check', $data);
        $this->load->view('common/footer', $footer_data);

    }

//    function updateProductPrice(){
//
//        //cron chk
//        $cnt = $this->process_scheduling->getProcessSchedulingCount(array('process_status'=>'1','process_code_in'=>array('updateProductPrice','stockInItemSync')));
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
//
//        $process_scheduling_add_data =array(
//            'process_code' => 'updateProductPrice',
//            'process_status' => '1',
//            'process_start_date' => date ("Y-m-d H:i:s")
//        );
//        $process_scheduling_inset_id=$this->process_scheduling->addProcessScheduling($process_scheduling_add_data);
//
//        echo 'start';
//
//        $update_data_result = $this->channel_item_info_model->getItemPriceUpdateHistoryInfos(array('upload_fg' => '1','update_join'=>'Y'),'a.item_history_id, b.channel_id, a.channel_item_code, a.upload_price, a.discount_price, a.discount_unit, c.item_info_id,stock_status');
//
//        $discount_unit_arr =array('RATE','MONEY');
//
//        foreach ($update_data_result ->result_array() as $date_info){
//
//            $upate_historyfilter = array('item_history_id'=> element('item_history_id',$date_info));
//
//            $channel_item_code = element('channel_item_code',$date_info);
//
//            if(!in_array(strtoupper(element('discount_unit',$date_info)),$discount_unit_arr)){
//                $date_info['discount_unit'] = 'N';
//            }
//
//            $discount_unit = ucwords(strtolower(element('discount_unit',$date_info)));;
//
//            if(!is_numeric(element('discount_price',$date_info,'')) && element('discount_price',$date_info,'')!=''&& $discount_unit!='N'){
//                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'4','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
//                continue;
//            }
//
//            $discount_value = element('discount_price',$date_info,'');
//
//            if(!is_numeric(element('upload_price',$date_info,'')) || element('upload_price',$date_info,'')=='0'){
//                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'4','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
//                continue;
//            }
//            $upload_price = element('upload_price',$date_info);
//
//            $price_info = array(
//                'discount_type'=>$discount_unit,
//                'discount_value'=>$discount_value,
//                'basic_price'=>$upload_price
//            );
//
//            $result  = false;
//            switch ($date_info['channel_id']){
//
//                case '1' : //fastople gmarket
//                    $result = $this->sendPrice($channel_item_code,element('basic_price',$price_info));
//
//                    if($discount_unit != 'N'){
//
//                        $result  = $this->sendDiscountPrice($channel_item_code,$price_info);
//
//                    }else{
//
//                        $price_info = array(
//                            'discount_type'=>'Rate',//임의의값
//                            'discount_value'=>'0',
//                            'basic_price'=>$upload_price
//                        );
//
//                        $result  = $this->sendDiscountPrice($channel_item_code,$price_info);
//
//                    }
//
//                    break;
//
//                case '2' : //fastople auction
//                    $result = $this->callAuctionPriceUpdate($channel_item_code,$price_info);
//                    break;
//            }
//
//            if($result===false){
//                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'3','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
//                continue;
//            }
//
//
//            $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'2','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
//
//            $update_date = array(
//                'upload_price' => $upload_price,
//                'discount_price' => $discount_value,
//                'discount_unit'=> $discount_unit/*,
//                'need_update'=>'Y'*/
//            );
//
//            if($date_info['channel_id']=='1' && $date_info['stock_status']=='N'){
//                $update_date['need_update'] ='Y';
//            }
//
//            $filter= array(
//                'item_info_id' => $date_info['item_info_id']
//            );
//
//            //channel_item_info
//            $this->channel_item_info_model->updateChannelItemInfo($update_date,$filter);
//
//        }
//
//        echo 'end';
//
//        //cron chk
//        $process_scheduling_update_data =array(
//            'process_status'=>'2',
//            'process_end_date'=>date("Y-m-d H:i:s")
//        );
//        $this->process_scheduling->updateProcessScheduling($process_scheduling_update_data,array('process_scheduling_id'=>$process_scheduling_inset_id));
//        return ;
//    }

    function updateProductPriceList(){

        $channel_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_result->result_array() as $channel_info){

            $data['channel_arr'][element('channel_code', $channel_info)] = $channel_info['channel_id'];

        }

        $start_dt = $this->input->get('start_dt');
        $end_dt = $this->input->get('end_dt');

        if($start_dt == '') $start_dt = date("Y-m-d", strtotime("-1 month", time()));;
        if($end_dt == '') $end_dt = date('Y-m-d');

        $data['start_dt'] = $start_dt;
        $data['end_dt'] = $end_dt;

        $filter['create_date_between'] = array($start_dt, $end_dt);

        $filter['account_id'] = $this->session->userdata("qten_master_id");

        $data['total_count'] = $this->channel_item_info_model->getItemPriceUpdateHistorycount($filter);

        $page_per_list = $this->input->get('page_per_list') ? $this->input->get('page_per_list') : 100;

        $page = (int)$this->input->get('page');
        if($page<1) $page=1;

        if($this->input->get('excel') != 'Y') {
            $filter['single_limit'] = array($page_per_list, ($page - 1) * $page_per_list);
        }
        $data['page_per_list'] = $page_per_list;

        $this->load->library('pagination');

        $paging_config['base_url'] = site_url('/item/single_item/updateProductPriceList');
        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str(element('query',$url), $params);
        if(isset($params['page'])) unset($params['page']);
        $paging_config['base_url'] .= '?' . http_build_query($params);

        $paging_config['total_rows']         = $data['total_count'];
        $paging_config['num_links']          = 5;
        $paging_config['per_page']           = $data['page_per_list'];
        $paging_config['use_page_numbers']   = TRUE;
        $paging_config['page_query_string']  = TRUE;
        $paging_config['query_string_segment'] = 'page';

        $this->pagination->initialize($paging_config);

        $data['paging_content'] = $this->pagination->create_links();

//		$date_result = $this->channel_item_info_model->getItemPriceUpdateHistoryInfos($filter,'',array('upload_fg'=>'desc'));
		$date_result = $this->channel_item_info_model->getItemPriceUpdateHistoryInfos($filter,'',array('item_history_id'=>'desc'));

        $data['create_date_arr'] =array();
        $worker_id = array();

        foreach ($date_result ->result_array() as $date_info){

            array_push($data['create_date_arr'],$date_info);

            if(element('worker_id',$date_info)){
                array_push($worker_id,element('worker_id',$date_info));
            }

        }

        $worker_id = array_unique($worker_id);

		$data['worker'] = array();
        if(count($worker_id)>0){
            $worker_result = $this->ntics_user_model->getUsers(array('worker_id_in'=>$worker_id),'worker_id,USER_NAME');

            foreach ($worker_result->result_array() as $value){
                if(element('worker_id',$value,false))
                    $data['worker'][element('worker_id',$value,false)] = element('USER_NAME',$value,false);
            }
        }

        $data['upload_fg'] = array(
            '1' => '준비'
        ,'2' => '성공'
        ,'3' => '실패');

        if($this->input->get('excel') == 'Y') {

            $this->updateProductPriceListdowloadExcel($data);
            return;

        }
        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('qten_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true),
            'add_stylesheet' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.css'),
            'add_script' => array('http://ntics.ntwsec.com/info/jquery-ui/jquery-ui.min.js'),
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        $this->load->view('common/header', $header_data);
        $this->load->view('item/product_update_history_list', $data);
        $this->load->view('common/footer', $footer_data);
    }

    function saveUpdateProductPriceExcel(){

        $account_id = $this->session->userdata("qten_master_id");

        if ($account_id == "") {
            alert("계정을 확인 후 다시 시도 해주세요.");
            exit;
        }

        if(!$_FILES['excel']['tmp_name'] || !file_exists($_FILES['excel']['tmp_name'])){
            alert('파일 업로드 중 오류가 발생하였습니다. 다시 시도 해 주세요.');
            exit;
        }

        $create_time = date ("Y-m-d H:i:s");
        $create_times = date ("YmdHis",strtotime($create_time));
        $create_time_yyyy = date ("Y",strtotime($create_time));
        $create_time_mm = date ("m",strtotime($create_time));

        $upload_dir = '/ssd/html/qten/file/item_price_update/';

        if( !is_dir($upload_dir."/".$create_time_yyyy)){
            mkdir($upload_dir."/".$create_time_yyyy);
            chmod($upload_dir."/".$create_time_yyyy, 0777);
        }
        if( !is_dir($upload_dir."/".$create_time_yyyy."/".$create_time_mm)){
            mkdir($upload_dir."/".$create_time_yyyy."/".$create_time_mm);
            chmod($upload_dir."/".$create_time_yyyy."/".$create_time_mm, 0777);
        }

        $config['upload_path'] = './file/item_price_update/'.$create_time_yyyy."/".$create_time_mm."/";
        $config['overwrite'] = true;
        $config['encrypt_name'] = false;
        $config['max_filename'] = 0;
        $config['allowed_types'] = 'xls|xlsx';
        $config['file_name'] = 'item_price_'.$create_times;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel')) {
            alert('업로드에 실패하였습니다');
            exit;
        }

        $file_info = $this->upload->data();
        $file_path = $file_info['full_path'];

        if(!file_exists($file_path)){
            alert('파일 업로드 중 오류가 발생하였습니다. 다시 시도 해 주세요.');
            exit;
        }

        $this->load->library('Excel');

        $objPHPExcel = new PHPExcel();

        // 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
        $objReader = PHPExcel_IOFactory::createReaderForFile($file_path);

        // 읽기전용으로 설정
        $objReader->setReadDataOnly(true);

        //데이터만 읽기(서식을 모두 무시해서 속도 증가 시킴)
        $objReader->setReadDataOnly(true);

        // 엑셀파일을 읽는다
        $objExcel = $objReader->load($file_path);

        // 첫번째 시트를 선택
        $objExcel->setActiveSheetIndex(0);

        $sheetData = $objExcel->getActiveSheet()->toArray(null,true,true,true);

        $excel_data_total_cnt = 1;
        $excel_data_result_cnt = 0;

        foreach ($sheetData as $row) {

            $channel_code = trim(element('A', $row, ''));
            $channel_item_code = trim(element('B', $row, ''));
            $channel_item_code = preg_replace("/[^Bb0-9]*/s", "", $channel_item_code);
            $upload_price = trim(element('C', $row, ''));

            if($excel_data_total_cnt==1 && $channel_code != '채널'){
                return;
            }

            if($excel_data_total_cnt==1 && $channel_code == '채널'){
                $excel_data_total_cnt++;
                continue;
            }

            $excel_data_total_cnt++;

            if($channel_code =='' || $account_id == '' || $channel_item_code == '' || $upload_price == '' ){
                continue;;
            }

            $add_data = array(
                'channel_code' => $channel_code,
                'account_id' => $account_id,
                'channel_item_code' => $channel_item_code,
                'upload_price' => $upload_price,
                'discount_price' => trim(element('E', $row, '')),
                'discount_unit' => trim(element('D', $row, '')),
                'upload_fg' => '1',
                'create_date' => $create_time,
                'worker_id' => $this->session->userdata('qten_worker_id'),
            );


            $this->channel_item_info_model->addItemPriceUpdateHistory($add_data);

            $excel_data_result_cnt++;

        }

        alert('총 :'.$excel_data_total_cnt."데이터 중 ".$excel_data_result_cnt." 데이터 업로드 완료 되었습니다",site_url('/item/single_item/updateProductPriceList') );
        exit;

    }

    //가격조정 히스토리 상세정보
    function priceHistoryLayer($item_info_id,$page_view){


        //상품정보
        $channel_item_info= $this->channel_item_info_model->getItemInfo(array("item_info_id"=>$item_info_id));
        $data['channel_item_info'] = $channel_item_info;

        $filter['item_info_id'] = $item_info_id;
        $filter['list_mode'] = true;

        //히스토리 정보읽기
        $data['total_count'] = $this->channel_item_info_model->getChannelItemPriceHistorycount($filter);

        $page_per_list = 100;

        $page = (int)$page_view;
        if ($page < 1) $page = 1;

        $filter['page'] = array("start" => $page_per_list, "end" => ($page - 1) * $page_per_list);

        $data['page_per_list'] = $page_per_list;

        $date_result = $this->channel_item_info_model->getChannelItemPriceHistory("h.upload_price,  h.discount_unit, h.discount_price, h.action_fg, h.worker_id, h.create_date",$filter,array('create_date'=>'desc'));

        $list_datas = array();
        $worker_id = array();

        foreach ($date_result ->result_array() as $date_info){

            array_push($list_datas, $date_info);

            if(element('worker_id',$date_info)){
                array_push($worker_id,element('worker_id',$date_info));
            }

        }

        $data['list_datas'] = $list_datas;

        if(count($worker_id)>0){
            $worker_result = $this->ntics_user_model->getAllUsers(array('worker_id_in'=>$worker_id),'worker_id,USER_NAME');

            foreach ($worker_result->result_array() as $value){
                if(element('worker_id',$value,false))
                    $data['worker'][element('worker_id',$value,false)] = element('USER_NAME',$value,false);
            }
        }

        $paging_config = array(
            'base_url' => "javascript:loadPriceHistory('".$item_info_id."',",
            'page_rows' => $page_per_list,
            'total_rows' => $data['total_count'],
            'page' => $page
        );
        $this->load->library('paging');
        $this->paging->initialize($paging_config);
        $data['paging_content'] = $this->paging->create();


        $this->load->view('item/product_update_price_history_list', $data);

    }

    private function updateProductPriceListdowloadExcel($data)
    {
        ini_set('memory_limit','-1');
        $this->load->library('Excel');

        $objPHPExcel = new PHPExcel();
        $excel_title = '단품 가격 업데이트_' . date('Y-m-d');
        $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
            ->setTitle($excel_title)
            ->setSubject($excel_title)
            ->setDescription($excel_title);

        $sheet = $objPHPExcel->getActiveSheet();

        $sheet->getCell('A1')->setValueExplicit('채널', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('B1')->setValueExplicit('아이디', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('C1')->setValueExplicit('상품코드', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('D1')->setValueExplicit('판매가', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('E1')->setValueExplicit('할인타입', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('F1')->setValueExplicit('할인수치', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('G1')->setValueExplicit('적용여부', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('H1')->setValueExplicit('적용날짜', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('I1')->setValueExplicit('등록날짜', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('J1')->setValueExplicit('작업자', PHPExcel_Cell_DataType::TYPE_STRING);

        $list_datas = element('create_date_arr', $data);

        $channel_arr = element('channel_arr', $data);

        $upload_fg = element('upload_fg', $data);

        $worker = element('worker', $data);

        $line_no = 2;

        foreach ($list_datas as $list_data){

            $channel_id = element( element('channel_code', $list_data) ,$channel_arr);

            $sheet->getCell('A' . $line_no)->setValueExplicit(element('channel_code', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('B' . $line_no)->setValueExplicit(element('account_id', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('C' . $line_no)->setValueExplicit(element('channel_item_code', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('D' . $line_no)->setValueExplicit(element('upload_price', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('E' . $line_no)->setValueExplicit(element('discount_unit', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('F' . $line_no)->setValueExplicit(element('discount_price', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('G' . $line_no)->setValueExplicit(element(element('upload_fg', $list_data),$upload_fg), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('H' . $line_no)->setValueExplicit(element('upload_date', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('I' . $line_no)->setValueExplicit(element('create_date', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('J' . $line_no)->setValueExplicit(element(element('worker_id', $list_data), $worker), PHPExcel_Cell_DataType::TYPE_STRING);
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


    public function apiItemListExcelDownForm(){

/*        alert("현재 사용이 불가능합니다.");
        exit;*/

        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('qten_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true)
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        //매핑 수정/삭제 노출 작업자
        $data['worker_ids_fg'] = (in_array($this->session->userdata('qten_manager_user_id'), $this->mapping_worker_ids)) ? 1: 0;

        $this->load->view('common/header', $header_data);
        $this->load->view('item/item_list_excel_form', $data);
        $this->load->view('common/footer', $footer_data);
    }

    public function gmarketItemListExcel($status){

        $this->api_key = $this->channel_info_model->getApikey(array("master_id"=>$this->session->userdata("qten_master_id"), "channel_code"=>"G"));

        $result1 = $this->callGetItemList($status, 1);

        $item_list = element('item_list', $result1);


        $item_list_arr = element('item_list', $result1);

        $total_page_count = element('TotalPageCount', $result1);

        if($total_page_count>1) {
            for ($i = 2; $i <= $total_page_count; $i++) {
                $result = $this->callGetItemList($status,$i);

                $item_list = element('item_list', $result);

                $item_list_arr = array_merge($item_list_arr, $item_list);

            }
        }


        $data['list_datas'] = $item_list_arr;

        if(count($item_list_arr)>0){
            $field_arr = array(
                "상품코드"		=> "GmktItemNo"
            ,	"판매자관리코드"		=> "OutItemNo"
            ,	"거래상태"		=> "TradeStatus"
            ,	"판매가격"		=> "SellPrice"
            ,	"재고수량"		=> "StockQty"
            ,	"최종 업데이트 날짜"		=> "LastUpdateDate"
            );


            $this->download_Excel('G_Item_'.$status , $field_arr , $data);

        }

    }

    public function autionItemListexcel(){

        $this->api_key = $this->channel_info_model->getApikey(array("master_id"=>$this->session->userdata("qten_master_id"), "channel_code"=>"A"));

        $result1 = $this->callAuctionGetItemList(1);


        $item_list_arr = element('item_list', $result1);

        $total_count = element('total_count', $result1);

        if($total_count>100) {
            $for_total_cnt = ((int)($total_count % 100) > 0) ? (int)($total_count / 100) + 1 : (int)($total_count / 100);
            for ($i = 2; $i <= $for_total_cnt; $i++) {
                $result = $this->callAuctionGetItemList($i);

                $item_list = element('item_list', $result);

                $item_list_arr = array_merge($item_list_arr, $item_list);

                if(count($item_list)>0) {
                    $item_list_arr = array_merge($item_list_arr, $item_list);
                }else{
                    alert("API 통신 오류로 잠시 후 다시 시도해주시기 바랍니다.");
                    exit;
                }

            }
        }

        $data['list_datas'] = $item_list_arr;


        if(count($item_list_arr)>0){
            $field_arr = array(
                "브랜드명"		=> "BrandName"
            ,	"분류코드"		=> "CategoryCode"
            ,	"분류명"		=> "CategoryName"
            ,	"사은품"		=> "FreeGift"
            ,	"배송비 착불여부"		=> "IsArrival"
            ,	"묶음배송여부"		=> "IsBundleShipping"
            ,	"배송비 선결제 여부"		=> "IsShippingPrePayable"
            ,	"상품명"		=> "ItemName"
            ,	"상품번호"		=> "ItemNo"
            ,	"등록일"		=> "ItemRegistDate"
            ,	"판매시작일"		=> "ListingBeginDate"
            ,	"예약판매시작일"		=> "ListingBeginDateReservation"
            ,	"판매종료일"		=> "ListingEndDate"
            ,	"예약판매종료일"		=> "ListingEndDateReservation"
            ,	"판매자관리코드"		=> "ManagementCode"
            ,	"도매최소구매수량"		=> "MinBuyQty"
            ,	"모델명"		=> "ModelName"
            ,	"추가구성상품 구분	"		=> "OptionTypeCode"
            ,	"결제완료수량"		=> "OrderCompleteQty"
            ,	"낙찰수량"		=> "OrderQty"
            ,	"주문옵션사항 구분	"		=> "OrderTypeCode"
            ,	"프리미엄 기한"		=> "Premium"
            ,	"프리미엄플러스 기한"		=> "PremiumPlus"
            ,	"예약배송일"		=> "PresaleShippingDate"
            ,	"추천 기한"		=> "Recommend"
            ,	"판매가"		=> "SellingPrice"
            ,	"판매상태"		=> "SellingStatusCode"
            ,	"배송조건"		=> "ShippingCondition"
            ,	"배송비"		=> "ShippingCost"
            ,	"배송비구분"		=> "ShippingCostChargeCode"
            ,	"배송비 부담 방식"		=> "ShippingFeeChargeType"
            ,	"배송비 방식"		=> "ShippingFeeType"
            ,	"재고수량"		=> "SumStockQty"
            );


            $this->download_Excel('A_Item' , $field_arr , $data);

        }

    }

    private function callAuctionGetItemList($currentPageCnt)
    {

        require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
        include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
        //$this->api_key	= 'd310kxymI5jbPsYSxgyJ4M9BkjJbtr8HCRcsVRFRK34TOnBIhjWapNEP/kfX7fk0oL/mvCc2bBG9VItchZXNuX0nP5xx1c4/PDd+03Dp0b8+uZpHQPr/3hy4kSD3g4D+X4mYkO7BPw2VRvgXd966yJ44honypujpOuokhesVrSPGolEF5HAWQY4Jewkxlub9mdMEKSVqH4MNgvlAH3OXR+s=';

        // 상품조회 리스트 조회
        $get_item_list_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/GetSellingItemList.xml');


        $requestXmlBody	= str_replace(
            array('__API_ENCRYPT_KEY__', '__CURRENT_PAGE__')
            ,	array($this->api_key, $currentPageCnt),
            $get_item_list_dummy
        );
        $serverUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
        $action			= "http://www.auction.co.kr/APIv1/ShoppingService/GetSellingItemList";

        $result = requestAuction($serverUrl, $action, $requestXmlBody);

        return $result;

    }

    private function callGetItemList($TradeStatus, $PageNo){

        include_once '/ssd/html/api_sdks/gmarket/autoload.php';

        $GetItemList = new \sdk\controller\GetItemList();
        $GetItemList->setTicket($this->api_key);
        $GetItemList->setTradeStatus($TradeStatus);
        $GetItemList->setPageNo($PageNo);
        $GetItemList->setMaxCount(10000);

       // $GetItemList->setSearchType("G");

//        $GetItemList->setSearchValue();

        $result =  $GetItemList->getResponse();


        return $result;
    }
}
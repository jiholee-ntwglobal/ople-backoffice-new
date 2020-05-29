<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-18
 * File: Item_option_manage.php
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Item_option_manage extends CI_Controller
{
    private $api_key;
    private $sales_channel_config;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/channel_option_item_info_model');
        $this->load->model('item/option_tmp_model');
        $this->load->model('item/channel_option_item_history_model');
        $this->load->model('item/channel_option_item_description_model');
        $this->load->model('user/ntics_user_model');	// @option_history_20200108


        //masterId 체크하고 리스트 불러오기
        $this->load->library('master_id', array('chk_master_id' => true));
        $this->master_id_list = $this->master_id->getMasterId();

    }

    public function index()
    {
//		$this->load->view('item/option_item_list');
        $this->lists();
    }

    private function lists()
    {
        list($filter, $data) = $this->getListFilterData();
        $filter['master_id'] = $this->session->userdata("qten_master_id");

        $data['total_count'] = $this->channel_option_item_info_model->getOptionItemCount($filter);
//		$data['total_count'] = $this->option_tmp_model->getOptionItemCount($filter);

        if($this->input->get("excel_fg") != "Y") {
            $filter['limit'] = array($data['page_per_list'], ($data['page'] - 1) * $data['page_per_list']);

            $this->load->library('pagination');

            $paging_config['base_url'] = site_url('item/item_option_manage/index');
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
            $paging_config['full_tag_open'] = "<nav><ul class='pagination'>";
            $paging_config['full_tag_close'] = "</ul></nav>";
            $paging_config['num_tag_open'] = "<li>";
            $paging_config['num_tag_close'] = "</li>";
            $paging_config['cur_tag_open'] = "<li class='active'><a>";
            $paging_config['cur_tag_close'] = "</a></li>";
            $paging_config['next_tag_open'] = $paging_config['prev_tag_open'] = $paging_config['last_tag_open'] = $paging_config['first_tag_open'] = "<li>";
            $paging_config['next_tag_close'] = $paging_config['prev_tag_close'] = $paging_config['last_tag_close'] = $paging_config['first_tag_close'] = "</li>";

            $this->pagination->initialize($paging_config);

            $data['paging_content'] = $this->pagination->create_links();

        }

        $select	= "i.channel_id, i.channel_item_code, SUM(IF(i.additem_fg='N',1,0)) AS selection_cnt, SUM(IF(i.additem_fg='Y',1,0)) AS addition_cnt
		, COUNT(DISTINCT IF(i.additem_fg='N', i.section, NULL)) AS selections, d.item_name, MAX(i.item_info_id) as uid, min(i.need_update) as need_update";


        $data['list_data_result']	= $this->channel_option_item_info_model->getChannelOptionItemInfos($filter, $select);
//		$data['list_data_result']	= $this->option_tmp_model->getChannelOptionItemInfos($filter, $select);

        $data['base_url']	= array(
            1	=> 'http://item.gmarket.co.kr/Item?goodscode='
        ,	2	=> 'http://itempage3.auction.co.kr/DetailView.aspx?itemNo='
        );

        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('qten_master_id')
        );


        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu',$left_data, true),
            'add_stylesheet' => array('/qten/js/jquery-ui.min.css'),
            'add_script' => array('/qten/js/jquery-ui.min.js'),
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        if($this->input->get("excel_fg") == "Y"){
            $field_arr = array(
                "채널"		=> "channel_comment"
            ,	"상품코드"		=> "channel_item_code"
            ,	"상품명"		=> "item_name"
            ,	"선택옵션 (선택갯수)"	=> "selection_cnt"
            ,	"추가구성"		=> "addition_cnt"
            ,	"비고"		=> "need_update"
            );

            $this->downloadExcel($this->session->userdata('qten_master_id')."_옵션_리스트",$field_arr, $data);

        }else {
            $this->load->view('common/header', $header_data);
//		$this->load->view('item/option_item_detail');
            $this->load->view('item/option_item_list', $data);
            $this->load->view('common/footer', $footer_data);
        }
    }

    // 작업 히스토리 레이어창 : @option_history_20200108
    public function history_worker($item_code=''){

        $worker_id = array();
        $data['historys'] = array();

        // 작업자 리스트 가져오기
        $lists = $this->channel_option_item_history_model->getHistoryOptionItemWorker($item_code);
        foreach ($lists->result_array() as $row)
        {
            $worker_id[] = $row['update_user_no'];
            array_push($data['historys'], $row);
        }

        // 작업자 이름 가져오기
        $worker_result = $this->ntics_user_model->getAllUsers(array('worker_id_in'=>$worker_id), 'worker_id, USER_NAME');
        $workers = array();
        foreach ($worker_result->result_array() as $k => $v)
        {
            $workers[$v['worker_id']] = $v['USER_NAME'];
        }

        // 작업자 매칭
        foreach ($data['historys'] as $k => $v)
        {
            $data['historys'][$k]['worker_name'] = $workers[$v['update_user_no']];
        }

        $this->load->view('item/option_item_history_worker', $data);
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
        $channel_arr = element('channel_arr', $data);

        $line_no = 2;

        foreach ($list_data_result->result_array() as $list_data){

            $string = "A";
            $excel_val = "";
            foreach ($field as $field_key=>$field_val) {
                switch ($field_val){
                    case "channel_comment" :
                        $excel_val = element('comment',element(element('channel_id', $list_data, ''),$channel_arr));
                        break;
                    case "selection_cnt" :
                        $excel_val =  element('selection_cnt',$list_data,'') ."개 ( ".element('selections',$list_data,''). " ) ";
                        break;
                    case "need_update" :
                        $excel_val =  element('need_update',$list_data,'')=='E'?'자동품절,품절해제오류':'';
                        break;
                    case 'channel_item_code':
                        $excel_val = preg_replace("/[^B0-9]/",'', element('channel_item_code',$list_data,''));

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


    private function getListFilterData()
    {
        $filter['select_escape']	= false;

        // 1: G마켓, 2: 옥션
        $data['channel_id'] = $this->input->get('channel_id');
        if($data['channel_id'] != '') $filter['channel_id'] = $data['channel_id'];

        $data['channel_code'] = $this->input->get('channel_code');
        if($data['channel_code'] != '') $filter['channel_code'] = $data['channel_code'];

        $data['stock_status'] = $this->input->get('stock_status');
        if($data['stock_status'] != '') $filter['stock_status'] = $data['stock_status'];

        $data['additem_fg'] = $this->input->get('additem_fg');
        if($data['additem_fg'] != '') $filter['additem_fg'] = $data['additem_fg'];

        $reg_status = $this->input->get('regist_fg');
        if($reg_status == '') $reg_status = 2;
        $data['regist_fg']	= $filter['regist_fg']	= $reg_status;

        $data['page_per_list'] = $this->input->get('page_per_list');
        if($data['page_per_list'] == '') $data['page_per_list'] = 50;

        $data['page'] = $this->input->get('page');
        if($data['page'] == '') $data['page'] = 1;

        $filter['group_by'] = 'i.channel_id, i.channel_item_code, d.item_name';
        $filter['order_by'] = 'uid DESC';

        $filter['desc_info'] = '1';

        $data['search_type']	= $this->input->get('search_type');
        $data['search_value']	= $this->input->get('search_value');

        switch ($data['search_type']) {
            case 'option_name':
                $filter['option_name'] = $data['search_value'];
                break;

            // 각 채널의 상품코드
            case 'channel_item_code':
                $filter_value = explode(PHP_EOL, $data['search_value']);
                $filter_value = array_filter(array_unique(array_map('trim', $filter_value)));
                if(count($filter_value) > 0)
                {
                    $filter['channel_item_code_in'] = $filter_value;
                }
                break;

            // Vcode
            case 'virtual_item_id':
                $channel_item_codes = array();

                $filter_value	= explode("\r\n", $data['search_value']);
                array_walk($filter_value,function(&$item){
                    $item = (int)str_replace('V', '',$item);
                });

                //virtual_item_id_->channel_item_code
                $channel_item_result = $this->channel_option_item_info_model->getChannelOptionItemInfos(array('virtual_item_id_in' => $filter_value, "regist_fg"=>2), "i.channel_item_code");
                foreach ($channel_item_result->result_array() as $channel_item_info) {
                    $channel_item_codes[] =  element('channel_item_code', $channel_item_info);
                }

                if(count($channel_item_codes)>0) {
                    $channel_item_codes = array_unique($channel_item_codes);
                    $filter['channel_item_code_in'] = $channel_item_codes; //; //$filter_value;
                }else{
                    $filter['channel_item_code_in'] = array(''); //; //$filter_value;
                }
                break;

            // upc
            case 'upc':

                $this->load->model('item/master_item_model');
                $master_item_ids = array();
                $channel_item_codes = array();
                $filter_value = explode("\r\n", $data['search_value']);
                array_walk($filter_value, function (&$item) {
                    $item = trim($item);
                });

                //upc->master_item_id
                $master_item_result = $this->master_item_model->getMasterItems(array("upc_where_in" => $filter_value), "m.master_item_id");
                foreach ($master_item_result->result_array() as $master_item_info) {
                    array_push($master_item_ids, element('master_item_id', $master_item_info));
                }

                $filter_val = (empty($master_item_ids) != 1) ? $master_item_ids : $filter_value;

                //master_item_id->channel_item_code
                $channel_item_result = $this->channel_option_item_info_model->getChannelOptionItemInfos(array('master_item_id_in' => $filter_val, "regist_fg"=>2), "i.channel_item_code");
                foreach ($channel_item_result->result_array() as $channel_item_info) {
                    $channel_item_codes[] =  element('channel_item_code', $channel_item_info);
                }

                if(count($channel_item_codes)>0) {
                    $channel_item_codes = array_unique($channel_item_codes);
                    $filter['channel_item_code_in'] = $channel_item_codes; //; //$filter_value;
                }else{
                    $filter['channel_item_code_in'] = array('');
                }

                break;

        }



        $data['channel_arr'] = array();
        $channel_info_result = $this->channel_info_model->getNewChannelInfos(array());

        foreach ($channel_info_result->result_array() as $channel_info){
            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;
        }

        $data['option_reg_status'] = $this->config->item('option_reg_status');

        return array($filter, $data);

    }


    public function uploadForm($type='', $channel_item_code='', $search_type='', $search_value='')
    {

        $data['search_type'] = $search_type;
        $data['search_value'] = $search_value;

        $data['type']	= $type=='' ? 'N' : $type;
        if($channel_item_code=='newinsert'){

            $data['channel_item_code']	= '';
            $data['channel_arr'] = array();
            $data['item_name'] = "";

            $channel_filter['master_id'] = $this->session->userdata("qten_master_id");
            $channel_info_result = $this->channel_info_model->getNewChannelInfos($channel_filter);

            foreach ($channel_info_result->result_array() as $channel_info){
                $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;
            }

        }else{

            $data['channel_item_code']	= $channel_item_code;
            $data['channel_arr'] = array();

            $item_data = $this->channel_option_item_info_model->getChannelOptionItemInfo(array('channel_item_code' => $channel_item_code, 'regist_fg' => '2'));


//			$item_data			= $this->option_tmp_model->getChannelOptionItemInfo(array('channel_item_code' => $channel_item_code, 'regist_fg' => '2'));
            $data['item_data']	= $item_data;

            $item_description = $this->channel_option_item_description_model->getOptionDescription(array('channel_item_code'=>$channel_item_code), "item_name");
            $data['item_name'] = element('item_name', $item_description);

            $channel_info = $this->channel_info_model->getNewChannelInfo(array('channel_id' => element('channel_id', $item_data)));

            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;

        }
        $this->load->view('item/option_upload_form', $data);
    }

    public function priceForm()
    {

        $this->load->view('item/item_price_form');
    }

    public function itemDetail($channel_item_code)
    {
        if(!$channel_item_code) return '';
        $filter	= array(
            'channel_item_code'	=> $channel_item_code
        ,	'regist_fg'			=> '2'
        );
        $item_data			= $this->channel_option_item_info_model->getChannelOptionItemInfo($filter);
//		$item_data			= $this->option_tmp_model->getChannelOptionItemInfo($filter);
        $data['item_data']	= $item_data;

        $channel_info			= $this->channel_info_model->getNewChannelInfo(array('channel_id'=>element('channel_id',$item_data)));

        $data['channel_info']	= $channel_info;

        $data['list_data_result'] = $this->channel_option_item_info_model->getChannelOptionItemInfos($filter, "i.*", array("additem_fg" => "desc", "section"=>"asc", "item_info_id"=>"asc"));

//		$data['list_data_result']	= $this->option_tmp_model->getChannelOptionItemInfos($filter);

        $this->load->view('item/option_item_detail',$data);
    }

    public function downloadDataExcel($type="selection", $channel_item_code){

        $list_datas = array();

        if($channel_item_code==""){
            alert('잘못된 경로로 접근하셨습니다.',site_url('item/item_option_manage'));
            exit;
        }

        $additem_fg = ($type=="selection") ? "N" : "Y";

        $list_data_result = $this->channel_option_item_info_model->getChannelOptionItemInfos(array("channel_item_code"=>$channel_item_code, "regist_fg"=>2, "additem_fg"=>$additem_fg), "i.*", array("additem_fg" => "desc", "section"=>"asc", "item_info_id"=>"asc"));

        foreach ($list_data_result->result_array() as $list_data){
            array_push($list_datas, $list_data);
        }

        if(count($list_datas)<1){
            alert('옵션이 존재하지 않습니다.',site_url('item/item_option_manage'));
            exit;
        }

        $this->load->library('Excel');

        $objPHPExcel = new PHPExcel();
        $excel_title = $type. '_' .$channel_item_code.'_' . date('Y-m-d');
        $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
            ->setTitle($excel_title)
            ->setSubject($excel_title)
            ->setDescription($excel_title);

        $sheet = $objPHPExcel->getActiveSheet();


        if($additem_fg=="N") {
            $sheet->getCell('A8')->setValueExplicit('선택정보 타입', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('B8')->setValueExplicit('옵션명', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('C8')->setValueExplicit('옵션값', PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->getCell('D8')->setValueExplicit('영문옵션명', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('E8')->setValueExplicit('영문옵션값', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('F8')->setValueExplicit('중문옵션명', PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->getCell('G8')->setValueExplicit('중문옵션값', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('H8')->setValueExplicit('일문옵션명', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('I8')->setValueExplicit('일문옵션값', PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->getCell('J8')->setValueExplicit('추가금액', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('K8')->setValueExplicit('재고수량', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('L8')->setValueExplicit('상태', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('M8')->setValueExplicit('노출여부', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('N8')->setValueExplicit('관리코드', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('O8')->setValueExplicit('추천옵션_항목_코드', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('P8')->setValueExplicit('추천옵션_필수선택코드', PHPExcel_Cell_DataType::TYPE_STRING);

            $line_no = 9;

            foreach ($list_datas as $list_data) {

                $sheet->getCell('B' . $line_no)->setValueExplicit(element('section', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);

                $sheet->getCell('C' . $line_no)->setValueExplicit(element('option_name', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->getCell('J' . $line_no)->setValueExplicit(element('price', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);

                $sheet->getCell('K' . $line_no)->setValueExplicit(element('stock_qty', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->getCell('N' . $line_no)->setValueExplicit("V" . str_pad(element('virtual_item_id', $list_data), 8, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);

                $line_no++;
            }

        }else{

            $sheet->getCell('A7')->setValueExplicit('추가항목명', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('B7')->setValueExplicit('추가구성명', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('C7')->setValueExplicit('가격', PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->getCell('D7')->setValueExplicit('재고수량', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('E7')->setValueExplicit('상태', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('F7')->setValueExplicit('노출여부', PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->getCell('G7')->setValueExplicit('관리코드', PHPExcel_Cell_DataType::TYPE_STRING);

            $line_no = 8;

            foreach ($list_datas as $list_data) {

                $sheet->getCell('A' . $line_no)->setValueExplicit(element('section', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);

                $sheet->getCell('B' . $line_no)->setValueExplicit(element('option_name', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->getCell('C' . $line_no)->setValueExplicit(element('price', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);

                $sheet->getCell('D' . $line_no)->setValueExplicit(element('stock_qty', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
                $sheet->getCell('G' . $line_no)->setValueExplicit("V" . str_pad(element('virtual_item_id', $list_data), 8, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);

                $line_no++;
            }

        }

        foreach(range('A','S') as $columnID) {
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

    public function OptionDescription($channel_item_code){

        if(!$channel_item_code) return '';

        $option_item_descriptions = array();

        $filter	= array(
            'channel_item_code'	=> $channel_item_code
        ,	'regist_fg'			=> '2'
        );

        $item_data	= $this->channel_option_item_info_model->getChannelOptionItemInfo($filter);

        $channel_info = $this->channel_info_model->getNewChannelInfo(array('channel_id'=>element('channel_id',$item_data)));

        $data['channel_info']	= $channel_info;

        $option_item_descriptions = $this->channel_option_item_description_model-> getOptionDescription(array("channel_item_code"=>$channel_item_code),"item_name");

        $data['option_item_description'] = $option_item_descriptions['item_name'];
        $data['channel_item_code'] = $channel_item_code;
        $this->load->view('item/option_description_modify',$data);

    }

    public function updateOptionDescription(){

        $channel_item_code = preg_replace("/[^A-Z0-9]/ ", "", ($this->input->post("channel_item_code")));
        $item_name = $this->input->post("item_name");
        if(!$channel_item_code || !$item_name) return '';

        $option_item_descriptions = $this->channel_option_item_description_model-> getOptionDescription(array("channel_item_code"=>$channel_item_code),"item_name");


        if(isset($option_item_descriptions) !== false){

            $option_item_descriptions_insert_data = array(
                "item_name" =>   $item_name,
                "update_date" => date("Y-m-d H:i:s"),
                "user_no" => $this->session->userdata('qten_worker_id')
            );

            $this->channel_option_item_description_model->update($option_item_descriptions_insert_data, array("channel_item_code"=>$channel_item_code));

        }else{

            $option_item_descriptions_update_data = array(
                "channel_item_code" => $channel_item_code,
                "item_name" =>   $item_name,
                "update_date" => date("Y-m-d H:i:s"),
                "user_no" => $this->session->userdata('qten_worker_id')
            );

            $this->channel_option_item_description_model->add($option_item_descriptions_update_data);

        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('msg' => '수정이 완료되었습니다.')));

    }

    public function checkReadyOptions($option_history_id, $search_type='', $search_value='')
    {
        if(!$option_history_id) return '';
        $data['option_history_id']	= $option_history_id;
        $data['search_type'] = $search_type;
        $data['search_value'] = $search_value;

        // set filter
        $filter	= array(
            'option_history_id'	=> $option_history_id
        ,	'channel_update_fg'	=> 'N'
        );
        // data load
        $item_dat			= $this->channel_option_item_history_model->getOptionItemHistory($filter);
        $item_data			= $item_dat->row_array();
        $data['item_data']	= $item_data;

        $item_description_data  = $this->channel_option_item_description_model-> getOptionDescription(array("channel_item_code"=>element('channel_item_code',$item_data),"item_name"));
        $data['item_description_data'] = $item_description_data;

        $channel_info = $this->channel_info_model->getNewChannelInfo(array('channel_id' => element('channel_id', $item_data)));

        $data['channel_info']	= $channel_info;

        $list_data	= array();
        $type_arr	= array();
        $detail_result		= $this->channel_option_item_history_model->getOptionItemHistoryDetail(array('option_history_id'=>$option_history_id));
        foreach($detail_result->result_array() as $row){
            if(!in_array(element('additem_fg',$row),$type_arr)) array_push($type_arr, element('additem_fg',$row));
            $list_data[]	= $row;
        }
        $data['list_data']	= $list_data;
        $data['type_arr']	= $type_arr;

        $left_data = array(
            'master_id_arr' => $this->master_id_list,
            'current_master_id' => $this->session->userdata('qten_master_id')
        );

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', $left_data, true),
            'add_stylesheet' => array('/qten/js/jquery-ui.min.css'),
            'add_script' => array('/qten/js/jquery-ui.min.js'),
        );
        $footer_data = array(
            'left_menu_on' => true
        );

        $this->load->view('common/header', $header_data);
        $this->load->view('item/option_item_check',$data);
        $this->load->view('common/footer', $footer_data);
    }

    // 옵션상품 히스토리 엑셀 다운로드 : @option_history_20200108
    public function optionItemHistoryExcel($option_history_id){

        $list_datas = array();
        $ago_list_datas = array();

        if ( $option_history_id == "" )
        {
            alert('잘못된 경로로 접근하셨습니다.',site_url('item/item_option_manage_test'));//TODO
            exit;
        }

        // 변경 정보
        $list_data_result = $this->channel_option_item_history_model->getHistoryOptionItem($option_history_id);
        foreach ($list_data_result->result_array() as $list_data){
            $channel_item_code = element('channel_item_code', $list_data);
            $additem_fg = element('additem_fg', $list_data);
            array_push($list_datas, $list_data);
        }

        // 기존 정보
        $channel_item_code_ago = $this->channel_option_item_history_model->getHistoryOptionItemIdAgo($channel_item_code, $option_history_id, $additem_fg);
        $list_data_result_ago = $this->channel_option_item_history_model->getHistoryOptionItem($channel_item_code_ago);
        foreach ($list_data_result_ago->result_array() as $ago_list_data){
            array_push($ago_list_datas, $ago_list_data);
        }

        $this->load->library('Excel');
        $objPHPExcel = new PHPExcel();
        $excel_title = $channel_item_code.'_'.$option_history_id.'_'.date('Ymd');
        $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
            ->setTitle($excel_title)
            ->setSubject($excel_title)
            ->setDescription($excel_title);
        $sheet = $objPHPExcel->getActiveSheet();

        // 기존 정보
        $sheet->getCell('A1')->setValueExplicit('기존 정보 : 재고는 체크하지 않습니다.', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('A2')->setValueExplicit('선택정보 타입', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('B2')->setValueExplicit('옵션명', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('C2')->setValueExplicit('옵션값', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('D2')->setValueExplicit('추가금액', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('E2')->setValueExplicit('재고수량', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('F2')->setValueExplicit('관리코드', PHPExcel_Cell_DataType::TYPE_STRING);

        // 변경 정보
        $sheet->getCell('H1')->setValueExplicit('변경 정보', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('H2')->setValueExplicit('선택정보 타입', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('I2')->setValueExplicit('옵션명', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('J2')->setValueExplicit('옵션값', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('K2')->setValueExplicit('추가금액', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('L2')->setValueExplicit('재고수량', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('M2')->setValueExplicit('관리코드', PHPExcel_Cell_DataType::TYPE_STRING);

        // 변동 내역
        $sheet->getCell('O1')->setValueExplicit('변동 내역', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('O2')->setValueExplicit('옵션명', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('P2')->setValueExplicit('옵션값', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('Q2')->setValueExplicit('추가금액', PHPExcel_Cell_DataType::TYPE_STRING);
        //$sheet->getCell('R2')->setValueExplicit('재고수량', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('R2')->setValueExplicit('관리코드', PHPExcel_Cell_DataType::TYPE_STRING);

        // 기존 정보
        $line_no = 3;
        foreach ($ago_list_datas as $list_data) {

            $sheet->getCell('B' . $line_no)->setValueExplicit(element('section', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('C' . $line_no)->setValueExplicit(element('option_name', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('D' . $line_no)->setValueExplicit(element('price', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('E' . $line_no)->setValueExplicit(element('stock_qty', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('F' . $line_no)->setValueExplicit("V" . str_pad(element('virtual_item_id', $list_data), 8, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);

            $line_no++;
        }

        // 변경 정보
        $line_no = 3;
        foreach ($list_datas as $list_data) {

            $sheet->getCell('I' . $line_no)->setValueExplicit(element('section', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('J' . $line_no)->setValueExplicit(element('option_name', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('K' . $line_no)->setValueExplicit(element('price', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('L' . $line_no)->setValueExplicit(element('stock_qty', $list_data), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('M' . $line_no)->setValueExplicit("V" . str_pad(element('virtual_item_id', $list_data), 8, 0, STR_PAD_LEFT), PHPExcel_Cell_DataType::TYPE_STRING);

            $line_no++;
        }

        // 변동 내역
        $excel_style = array(
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'FF0000')),
            'font'  => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'))
        );
        $k = 0;
        $line_no = 3;
        if ( count($list_datas) > count($ago_list_datas) )
        {
            foreach ($list_datas as $list_data) {
                if ( isset($ago_list_datas[$k]['section']) === FALSE || $ago_list_datas[$k]['section'] != element('section', $list_data) )
                {
                    $sheet->getCell('O' . $line_no)->setValueExplicit('X', PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getStyle('O' . $line_no)->applyFromArray($excel_style);
                }
                if ( isset($ago_list_datas[$k]['option_name']) === FALSE || $ago_list_datas[$k]['option_name'] != element('option_name', $list_data) )
                {
                    $sheet->getCell('P' . $line_no)->setValueExplicit('X', PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getStyle('P' . $line_no)->applyFromArray($excel_style);
                }
                if ( isset($ago_list_datas[$k]['price']) === FALSE || $ago_list_datas[$k]['price'] != element('price', $list_data) )
                {
                    $sheet->getCell('Q' . $line_no)->setValueExplicit('X', PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getStyle('Q' . $line_no)->applyFromArray($excel_style);
                }
                if ( isset($ago_list_datas[$k]['virtual_item_id']) === FALSE || $ago_list_datas[$k]['virtual_item_id'] != element('virtual_item_id', $list_data) )
                {
                    $sheet->getCell('R' . $line_no)->setValueExplicit('X', PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getStyle('R' . $line_no)->applyFromArray($excel_style);
                }

                $k++;
                $line_no++;
            }
        }
        else
        {
            foreach ($ago_list_datas as $list_data) {
                if ( isset($list_datas[$k]['section']) === FALSE || $list_datas[$k]['section'] != element('section', $list_data) )
                {
                    $sheet->getCell('O' . $line_no)->setValueExplicit('X', PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getStyle('O' . $line_no)->applyFromArray($excel_style);
                }
                if ( isset($list_datas[$k]['option_name']) === FALSE || $list_datas[$k]['option_name'] != element('option_name', $list_data) )
                {
                    $sheet->getCell('P' . $line_no)->setValueExplicit('X', PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getStyle('P' . $line_no)->applyFromArray($excel_style);
                }
                if ( isset($list_datas[$k]['price']) === FALSE || $list_datas[$k]['price'] != element('price', $list_data) )
                {
                    $sheet->getCell('Q' . $line_no)->setValueExplicit('X', PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getStyle('Q' . $line_no)->applyFromArray($excel_style);
                }
                if ( isset($list_datas[$k]['virtual_item_id']) === FALSE || $list_datas[$k]['virtual_item_id'] != element('virtual_item_id', $list_data) )
                {
                    $sheet->getCell('R' . $line_no)->setValueExplicit('X', PHPExcel_Cell_DataType::TYPE_STRING);
                    $sheet->getStyle('R' . $line_no)->applyFromArray($excel_style);
                }

                $k++;
                $line_no++;
            }
        }

        foreach(range('A','S') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $objPHPExcel->getActiveSheet()->setTitle($excel_title);
        $filename = iconv("UTF-8", "EUC-KR", $excel_title);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    public function getOptionToExcel($channel_item_code)
    {

    }

    // 엑셀파일 히스토리 데이터 입력 (가등록 상태)
    public function excelDataUpload()
    {
        $channel_id			= $this->input->post('channel_id');
        $search_type			= $this->input->post('search_type');
        $search_value			= $this->input->post('search_value');

        if($search_type!="" &&  $search_value!= "") $url = "/".$search_type."/".$search_value;
        else $url = '';

        if(!$channel_id) {
            alert('채널을 선택해주세요');
            exit;
        }

//		$channel_item_code	= $this->input->post('channel_item_code');
        $channel_item_code = preg_replace("/[^B0-9]/",'', $this->input->post('channel_item_code'));
        if(!$channel_item_code){
            alert('상품코드를 선택해주세요');
            exit;
        }

        $item_name = $this->input->post("item_name");
        if(!$item_name){
            alert("상품명을 입력해주세요.");
            exit;
        }

        $add_item_fg		= $this->input->post('add_item_fg');
        if($add_item_fg == 'Y'){
            $column_num		= 6;
            $column_mapping	= $this->config->item('column_mapping_addition');
            $file_prefix	= 'addition';
        }elseif($add_item_fg == 'N'){
            $column_num		= 7;
            $column_mapping	= $this->config->item('column_mapping_selection');
            $file_prefix	= 'selection';
        }else{
            alert('추가구성 여부를 선택해주세요');
            exit;
        }

        if(!$_FILES['excel']['tmp_name'] || !file_exists($_FILES['excel']['tmp_name'])){
            alert('파일 업로드 중 오류가 발생하였습니다. 다시 시도 해 주세요.');
            exit;
        }
        $upload_time		= date ("YmdHis");
        $upload_time_yyyy	= substr($upload_time,0,4);
        $upload_time_mm		= substr($upload_time,4,2);
        $upload_dir = '/ssd/html/qten/file/option_item/';
        if( !is_dir($upload_dir."/".$upload_time_yyyy)){
            mkdir($upload_dir."/".$upload_time_yyyy);
            chmod($upload_dir."/".$upload_time_yyyy, 0777);
        }
        if( !is_dir($upload_dir."/".$upload_time_yyyy."/".$upload_time_mm)){
            mkdir($upload_dir."/".$upload_time_yyyy."/".$upload_time_mm);
            chmod($upload_dir."/".$upload_time_yyyy."/".$upload_time_mm, 0777);
        }

        $config['upload_path']		= './file/option_item/'.$upload_time_yyyy."/".$upload_time_mm."/";
        $config['overwrite']		= true;
        $config['encrypt_name']		= false;
        $config['max_filename']		= 0;
        $config['allowed_types']	= 'xls|xlsx';
        $config['file_name']		= $file_prefix.'_'.$channel_item_code.'_'.$upload_time;

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
        $history_data			= array(
            'channel_id'		=> $channel_id
        ,	'channel_item_code'	=> $channel_item_code
        ,	'update_user_no'	=> $this->session->userdata('qten_worker_id')
        ,	'channel_update_fg'	=> 'N'
        ,	'history_date'		=> date('Y-m-d H:i:s')
        );
        $option_history_id	= $this->channel_option_item_history_model->addOptionItemHistory($history_data);

        $history_detail_data	= array();
        $row_num				= 0;
        foreach ($sheetData as $row) {
            // 유효 데이터까지 스킵
            if($row_num < $column_num){
                $row_num++;
                continue;
                // 컬럼 정보 추출
            }elseif($row_num == $column_num){
                $column_info	= array_flip($row);

                foreach($column_mapping as $key => $val){
                    $col[element($key,$column_info)]	= $val;
                }
                $row_num++;
                continue;
            }
            // 데이터 임시 배열 저장
            $tmp	= array();
            foreach($col as $alp => $col_name){
                if($col_name =='virtual_item_id'){
                    $tmp[$col_name]	= (int)str_replace('V','',element($alp,$row));
                }else{
                    $tmp[$col_name]	= element($alp,$row);
                }
            }

            if(trim(element('section',$tmp,'')) == ''){
                break;
            }

            $tmp['additem_fg']			= $add_item_fg;
            $tmp['option_history_id']	= $option_history_id;
            array_push($history_detail_data, $tmp);
        }
        if(count($history_detail_data) > 0){
            $this->channel_option_item_history_model->addOptionItemHistoryDetailBulk($history_detail_data);
        }

        $option_item_descriptions = $this->channel_option_item_description_model-> getOptionDescription(array("channel_item_code"=>$channel_item_code),"item_name");

        if(isset($option_item_descriptions) !== false){

            $option_item_descriptions_insert_data = array(
                "item_name" =>   $item_name,
                "update_date" => date("Y-m-d H:i:s"),
                "user_no" => $this->session->userdata('qten_worker_id')
            );

            $this->channel_option_item_description_model->update($option_item_descriptions_insert_data, array("channel_item_code"=>$channel_item_code));

        }else{

            $option_item_descriptions_update_data = array(
                "channel_item_code" => $channel_item_code,
                "item_name" =>   $item_name,
                "update_date" => date("Y-m-d H:i:s"),
                "user_no" => $this->session->userdata('qten_worker_id')
            );

            $this->channel_option_item_description_model->add($option_item_descriptions_update_data);

        }

        // go to option check page
        redirect(site_url('item/item_option_manage/checkReadyOptions/'.$option_history_id.$url));

    }

    public function optionUpdate($chk	= '')
    {
        // ajax 에서 json 이 아닌 html 받아서 해더 추가
        header('Content-Type: application/json');

        // formdata check
        $param			= $this->input->post();

        $history_id		= element('history_id',$param,'');
        $option_load	= element('option_load',$param,'');
        $price_info	= array(
            'basic_price'		=> element('basic_price',$param,'')
        ,	'discount_type'		=> element('discount_type',$param,'N')
        ,	'discount_value'	=> element('discount_value',$param,'')
        );

//		if($chk != ''){
//			$history_id		= 12;
//			$option_load	= 'N';
//			$price_info	= array(
//				'basic_price'		=> '55900'
//			,	'discount_type'		=> 'Money'
//			,	'discount_value'	=> '6000'
//			);
//		}

        $this->output->set_content_type('application/json')->set_header('Access-Control-Allow-Origin:*');
        $return_data	= array();
        if($history_id == ''){
            $return_data['result']	= 'Fail';
            $return_data['msg']		= 'Fail';
            $this->output->set_output(json_encode($return_data));
            return;
        }
        if($option_load == ''){
            $return_data['result']	= 'Fail';
            $return_data['msg']		= 'Fail';
            $this->output->set_output(json_encode($return_data));
            return;
        }
        if(element('discount_type',$price_info) != 'N' && element('discount_value',$price_info) == ''){
            $return_data['result']	= 'Fail';
            $return_data['msg']		= 'Fail';
            $this->output->set_output(json_encode($return_data));
            return;
        }

        // history load
        $item_tmp	= $this->channel_option_item_history_model->getOptionItemHistory(array('option_history_id'=>$history_id));
        $item_data	= $item_tmp->row_array();
        // history_detail load
        $item_options	= $this->channel_option_item_history_model->getOptionItemHistoryDetail(array('option_history_id'=>$history_id));
        $option_arr		= array();
        foreach($item_options->result_array() as $option_data){
            $tmp_arr	= array(
                'channel_id'		=> element('channel_id',$item_data)
            ,	'channel_item_code'	=> element('channel_item_code',$item_data)
            ,	'virtual_item_id'	=> element('virtual_item_id',$option_data)
            ,	'section'			=> element('section',$option_data)
            ,	'option_name'		=> element('option_name',$option_data)
            ,	'price'				=> element('price',$option_data)
            ,	'additem_fg'		=> element('additem_fg',$option_data)
            ,	'stock_qty'			=> element('stock_qty',$option_data)
            ,	'regist_fg'			=> '1'
            );
            array_push($option_arr, $tmp_arr);
        }

        // 변경 이외 옵션 정보 관련 플래그
        // $option_load 'N'=기존옵션 모두 사용안함, 'A'=추가구성 재사용
        if($option_load == 'A'){
            $this->existOptionReset(element('channel_item_code',$item_data), 'A');
        }elseif($option_load == 'O'){
            $this->existOptionReset(element('channel_item_code',$item_data), 'O');
        }

        // 기존 옵션 삭제 처리(사용안함처리)
        // UPDATE regist_fg = '2' to '3'
        $this->oldOptionUpdate(element('channel_item_code',$item_data));

        // channel_option_item_info insert
        $this->channel_option_item_info_model->addChannelOptionItemInfoBulk($option_arr);
//		$this->option_tmp_model->addChannelOptionItemInfoBulk($option_arr);

        $channel_info = $this->channel_info_model->getNewChannelInfo(array('channel_id' => element('channel_id', $item_data)));

        switch (element('channel_code', $channel_info)) {
            case 'G':
                $this->load->config('api_config_gmarket', true);
                $this->sales_channel_config = $this->config->item(element('account_id', $channel_info), 'api_config_gmarket');

                $option_sync_method = 'callAddItemOption';
                break;
            case 'A':
                $this->load->config('api_config_auction', true);
                $this->sales_channel_config = $this->config->item(element('account_id', $channel_info), 'api_config_auction');

                $option_sync_method = 'auctionOptionStockSync';
                break;
            default :
        }
        //$this->api_key	= element('api_key',$this->sales_channel_config);
        $this->api_key = $this->channel_info_model->getApikey(array("account_id"=>element('account_id', $channel_info), "channel_code"=>element('channel_code', $channel_info)));

        $filter	= array(
            'regist_fg'			=> '1'
        ,	'channel_item_code'	=> element('channel_item_code', $item_data)
        ,	'order_by'			=> 'item_info_id'
        );
        $option_result	= $this->channel_option_item_info_model->getChannelOptionItemInfos($filter);
//		$option_result	= $this->option_tmp_model->getChannelOptionItemInfos($filter);

        $selections	= array();
        $additions	= array();

        foreach($option_result->result_array() as $option){
            $option['virtual_item_id']	= "V".str_pad($option['virtual_item_id'], 8, "0", STR_PAD_LEFT);
            if(element('additem_fg',$option) == 'Y'){
                array_push($additions, $option);
            }else{
                array_push($selections, $option);
            }
        }

        if(count($selections) < 1){
            return false;
        }

        // 채널별 매소드 호출
        $sync_result	= $this->{$option_sync_method}(element('channel_item_code', $item_data), $selections, $additions, $price_info);

        if(element('result',$sync_result) == 'Fail'){
            $this->errorDataReset(element('channel_item_code', $item_data));

            $return_data['result']	= 'Fail';
            $return_data['msg']		= element('rs_msg',$sync_result);
            $this->output->set_output(json_encode($return_data));
            return;

        }
        $return_data['result']	= 'Success';
        $return_data['msg']		= 'Success';
        $this->output->set_output(json_encode($return_data));
        return;
    }

    public function test(){
        $this->load->config('api_config_gmarket', true);

        $this->sales_channel_config = $this->config->item("fastople", 'api_config_gmarket');

        echo	$this->api_key	= element('api_key',$this->sales_channel_config);
        echo "<pre>";
        var_dump($this->sales_channel_config);
        echo "</pre>";


        echo $this->channel_info_model->getApikey(array("account_id"=>"fastople", "channel_code"=>"G"));

    }


    private function errorDataReset($channel_item_code)
    {
        $this->channel_option_item_info_model->updateChannelOptionItemInfo(
            array(
                'regist_fg'			=> '3'
            ),
            array(
                'channel_item_code' => $channel_item_code
            ,	'regist_fg'			=> '1'
            )
        );
    }

    private function existOptionReset($channel_item_code, $fg)
    {
        $add_fg	= $fg == 'A' ? 'Y' : 'N';

        $this->channel_option_item_info_model->updateChannelOptionItemInfo(
//		$this->option_tmp_model->updateChannelOptionItemInfo(
            array(
                'regist_fg'			=> '1'
            ,	'auction_stock_no'	=> NULL
            ),
            array(
                'channel_item_code' => $channel_item_code
            ,	'regist_fg'			=> '2'
            ,	'additem_fg'		=> $add_fg
            )
        );
    }

    private function oldOptionUpdate($channel_item_code)
    {
        $this->channel_option_item_info_model->updateChannelOptionItemInfo(
//		$this->option_tmp_model->updateChannelOptionItemInfo(
            array(
                'regist_fg'			=> '3'
            ),
            array(
                'channel_item_code' => $channel_item_code
            ,	'regist_fg'			=> '2'
            )
        );
    }

    // gmarket
    private function callAddItemOption($channel_item_code, $selections, $additions, $price_info)
    {
        include_once '/ssd/html/api_sdks/gmarket/autoload.php';

//		if(element('basic_price',$price_info) > 0){
//			// 가격변경 AddPrice
//			if(!$this->sendPrice($channel_item_code,element('basic_price',$price_info))) return false;
//
//			if(element('discount_type',$price_info) != 'N'){
//				// 판매자 할인 등록
//				if($this->sendDiscountPrice($channel_item_code,$price_info)) return false;
//			}
//		}

        $AddItemOption = new \sdk\controller\AddItemOption();
        $AddItemOption->setTicket($this->api_key);
        $AddItemOption->setGmktItemNo($channel_item_code);
        $AddItemOption->setOptionBlock($selections);
        $AddItemOption->setAdditionBlock($additions);

        $result =  $AddItemOption->getResponse();

        if(element('Result', $result) !== 'Success'){
            return array(
                'result'	=> 'Fail'
            ,	'rs_msg'	=> element('Comment',$result)
            );
        }

        $this->channel_option_item_info_model->updateChannelOptionItemInfo(
//		$this->option_tmp_model->updateChannelOptionItemInfo(
            array('regist_fg'=> '2')
            ,	array(
                'channel_item_code' => $channel_item_code
            ,	'regist_fg'			=> '1'
            )
        );
        return array(
            'result'	=> 'Success'
        ,	'rs_msg'	=> element('Comment',$result)
        );

        // 결과 처리
    }

    private function sendPrice($channel_item_code, $price)
    {
        include_once '/ssd/html/api_sdks/gmarket/autoload.php';

        $AddPrice	= new \sdk\controller\AddPrice();
        $AddPrice->setTicket($this->api_key);
        $price_info	= array(
            'GmktItemNo'	=> $channel_item_code
        ,	'DisplayDate'	=> date('Y-m-d', strtotime('+1 year'))
        ,	'SellPrice'		=> $price
        ,	'StockQty'		=> '9999'
        ,	'InventoryNo'	=> ''
        );
        $AddPrice->setProductPriceInfo($price_info);
        $response	= $AddPrice->getResponse();

        return (element('Result',$response) == 'Success') ? true : false;
    }

    private function sendDiscountPrice($channel_item_code, $price_info)
    {
        include_once '/ssd/html/api_sdks/gmarket/autoload.php';

        $AddPremiumItem		= new \sdk\controller\AddPremiumItem();
        $AddPremiumItem->setTicket($this->api_key);
        $premium_item_info	= array(
            'GmktItemNo'	=> $channel_item_code
        ,	'IsDiscount'	=> true
        ,	'DiscountPrice'	=> element('discount_value',$price_info)
        ,	'DiscountUnit'	=> element('discount_type',$price_info) // Money:or Rate
        ,	'StartDate'		=> date('Y-m-d')
        ,	'EndDate'		=> date('Y-m-d', strtotime('+3 months')) // 일단 3개월
        );
        $AddPremiumItem->setPremiumItemInfo($premium_item_info);


        $response	= $AddPremiumItem->getResponse();
//
////		if($response[0]['Result'] != 'Success'){
////			print_r($premium_item_info);
////			print_r($response);
////			exit;
////		}
//		return $response;

        return (element('Result',$response[0]) == 'Success') ? true : false;
    }

    // auction
    private function auctionOptionStockSync($channel_item_code, $selections, $additions, $price_info)
    {
        require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
        include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';

//		if(element('basic_price',$price_info) > 0){
//			$reset_result	= $this->callAuctionPriceUpdate($channel_item_code, $price_info);
//		}

        // 기존 옵션이 있을 경우 리셋 API 호출 - 무조건 호출로 변경
//		if($this->channel_option_item_info_model->getOptionItemCount(array('channel_item_code'=>$channel_item_code, 'regist_fg'=>'3')) > 0 ){
//		if($this->option_tmp_model->getOptionItemCount(array('channel_item_code'=>$channel_item_code, 'regist_fg'=>'3')) > 0 ){
        $reset_result	= $this->callAuctionOptionReset($channel_item_code);
//		}

        // 옵션등록 동기화
        $objclassnames			= array();
        $objclassname			= '';
        $item_selection_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItemStockSelections.xml');
        $selection_block		= '';
        foreach($selections as $selection_item){
            if(!in_array(element('section',$selection_item), $objclassnames)) array_push($objclassnames,element('section',$selection_item));

            $stock				= element('stock_qty',$selection_item) > 0 ? 'false':'true';
            $selection_stock_no	= 'ChangeType="Add"';

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
                $addition_stock_no	= 'ChangeType="Add"';
                $addition_block		.= str_replace(
                    array('__OPTION_STOCK_NO__','__SECTION_NAME__', '__SECTION_VALUE__', '__VIRTUAL_CODE__', '__OPTION_PRICE__', '__OPTION_STOCK_FLAG__')
                    ,	array($addition_stock_no, element('section',$addition_item), element('option_name',$addition_item), element('virtual_item_id',$addition_item), element('price',$addition_item), element('stock_qty',$addition_item))
                    ,	$item_addition_dummy
                );
            }
        }

        $additemoption_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItemStock.xml');
        $requestXmlBody	= str_replace(
            array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__', '__ADDITION_OPTION_TYPE__', '__CLASS_NAMES__', '__OPTION_SELECTION_BLOCK__', '__OPTION_ADDITION_BLOCK__')
            ,	array($this->api_key, $channel_item_code, $addition_type, $objclassname, $selection_block, $addition_block)
            ,	$additemoption_dummy
        );

        $serverUrl		= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
        $action			= "http://www.auction.co.kr/APIv1/ShoppingService/ReviseItemStock";

        $sync_result	= requestAuction($serverUrl, $action, $requestXmlBody);

        if(!$sync_result || element('result',$sync_result) == 'Fail'){
            return array(
                'result'	=> 'Fail'
            ,	'rs_msg'	=> (!$sync_result) ? '통신실패 금지단어 등을 확인해주세요' : element('rs_msg',$sync_result)
            );
        }

        // 옵션 스탁넘버 호출 및 처리
        $result_data	= $this->callViewItemStock($channel_item_code);

        // 옵션 데이터 변경처리
        foreach(element('options',$result_data) as $row){
            $this->channel_option_item_info_model->updateChannelOptionItemInfo(
//			$this->option_tmp_model->updateChannelOptionItemInfo(
                array(
                    'regist_fg'			=> '2'
                ,	'auction_stock_no'	=> element('auction_stock_no',$row)
                ),
                array(
                    'regist_fg'			=> '1'
                ,	'channel_item_code'	=> $channel_item_code
                ,	'section'			=> element('section',$row)
                ,	'option_name'		=> element('option_name',$row)
                )
            );
        }
        return array(
            'result'	=> 'Success'
        );

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

    private function callViewItemStock($channel_item_code)
    {
        $viewXml	= str_replace(array('__API_ENCRYPT_KEY__','__CHANNEL_ITEM_ID__'), array($this->api_key, $channel_item_code)
            ,	file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ViewItemStock.xml')
        );
        $viewUrl	= "https://api.auction.co.kr/APIv1/ShoppingService.asmx";
        $viewAction	= "http://www.auction.co.kr/APIv1/ShoppingService/ViewItemStock";

        return requestAuction($viewUrl, $viewAction, $viewXml);
    }

    private function callAuctionPriceUpdate($channel_item_code, $price_info)
    {
        // 가격변경
        $item_price_dummy	= file_get_contents('/ssd/html/api_sdks/auction_tmp/xml/ReviseItem.xml');
        $seller_discount	= '';

        if(element('discount_type',$price_info) != 'N'){
            $seller_discount	= 'SellerDiscount="'.element('discount_value',$price_info).'" SellerDiscountFromDate="'.date('Y-m-d').'" SellerDiscountToDate="'.date('Y-m-d', strtotime('+3 months')).'" ';

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


    public function item_delete($channel_item_code=null)
    {
        if ($this->input->server('REQUEST_METHOD') === 'POST')
        {
            $result_data = array(
                'result' => 'not_normal_access'
            );
            if (strtolower($this->input->server('HTTP_X_REQUESTED_WITH')) == "xmlhttprequest")
            {
                // 1. channel_option_item_info.channel_item_code == $this->input->post('channel_item_code') 인 레코드의 regist_fg 값을 3으로 Update
                $update_data = array(
                    'regist_fg' => 3
                );
                $update_where = array(
                    'channel_item_code' => $this->input->post('channel_item_code'),
                    'regist_fg' => 2
                );
                $affect_rows = $this->channel_option_item_info_model->updateChannelOptionItemInfo($update_data, $update_where);

                if($affect_rows > 0)
                {
                    // 2. channel_option_item_delete_history 에 Insert
                    $history_data = array(
                        'channel_id' => $this->input->post('channel_id'),
                        'channel_item_code' => $this->input->post('channel_item_code'),
                        'update_user_no' => $this->session->userdata('qten_worker_id'),
                        'reason_to_delete' => $this->input->post('reason_to_delete'),
                        'history_date' => date('Y-m-d H:i:s')
                    );
                    $option_history_id	= $this->channel_option_item_history_model->deleteOptionItemHistory($history_data);

                    $result_data = array(
                        'result' => true,
                        'message' => '정상적으로 삭제되었습니다.'
                    );
                }
                else
                {
                    $result_data = array(
                        'result' => false,
                        'message' => "삭제된 옵션상품이 없습니다. 이미 삭제되었을 수도 있습니다.\n잠시 후 다시 시도하시기 바랍니다."
                    );
                }
            }

            echo json_encode($result_data);
        }
        else
        {
            if(!$channel_item_code) return '';

            $option_item_descriptions = array();

            $filter	= array(
                'channel_item_code' => $channel_item_code,
                'regist_fg' => '2'
            );

            $item_data	= $this->channel_option_item_info_model->getChannelOptionItemInfo($filter);

            if(count($item_data) > 0)
            {
                $channel_info = $this->channel_info_model->getNewChannelInfo(array('channel_id'=>element('channel_id', $item_data)));

                $data['channel_info']	= $channel_info;

                $option_item_descriptions = $this->channel_option_item_description_model-> getOptionDescription(array("channel_item_code"=>$channel_item_code),"item_name");

                $data['option_item_description'] = $option_item_descriptions['item_name'];
                $data['channel_item_code'] = $channel_item_code;
                $this->load->view('item/item_delete', $data);
            }
            else
            {
                alert("선택한 옵션상품은 이미 삭제되었거나 존재하지 않습니다.\\n잠시 후 다시 시도하시기 바랍니다.", "reload");
            }
        }
    }

//	public function test_price()
//	{
//		$price_info	= array(
//			'basic_price'		=> '53900'
//		,	'discount_type'		=> 'Money'
//		,	'discount_value'	=> '1000'
//		);
//
////		// auction
////		require_once('/ssd/html/api_sdks/auction_tmp/AuctionSession.php');
////		include_once '/ssd/html/api_sdks/auction_tmp/auction_function.php';
////		$this->api_key	= 'd310kxymI5jbPsYSxgyJ4M9BkjJbtr8HCRcsVRFRK34TOnBIhjWapNEP/kfX7fk0oL/mvCc2bBG9VItchZXNuX0nP5xx1c4/PDd+03Dp0b8+uZpHQPr/3hy4kSD3g4D+X4mYkO7BPw2VRvgXd966yJ44honypujpOuokhesVrSPGolEF5HAWQY4Jewkxlub9mdMEKSVqH4MNgvlAH3OXR+s=';
////		var_dump($this->callAuctionPriceUpdate('B539772675', $price_info));
//
//		// gmarket
//		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
//		$this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
////		var_dump($this->sendPrice('1416481053',element('basic_price',$price_info)));
//		if(element('discount_type',$price_info) != 'N'){
//			// 판매자 할인 등록
//			var_dump($this->sendDiscountPrice('1416481053',$price_info));
//		}
//
//	}

}
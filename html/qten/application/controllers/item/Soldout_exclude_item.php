<?php

/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오후 5:43
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Soldout_exclude_item extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('item/master_item_model');
        $this->load->model('item/soldout_exclude_item_model');
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
        list($filter, $data) = $this->getListFilterData();

        $data['total_count'] = $this->soldout_exclude_item_model->countSoldoutExcludeItems($filter);

        $this->load->library('pagination');

        $paging_config['base_url'] = site_url('item/Soldout_exclude_item/index');
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

        $data['list_datas'] = array();

        $list_data_result = $this->soldout_exclude_item_model->getSoldoutExcludeItems($filter);

        foreach ($list_data_result->result_array() as $list_data){
            array_push($data['list_datas'], $list_data);
        }

        $data['master_item_arr'] = array();
        $data['worker_arr'] = array();

        if(count($data['list_datas']) > 0){

            $master_item_ids = array_column($data['list_datas'], 'master_item_id');

            $master_item_result = $this->master_item_model->getMasterItems(array('master_item_id_in' => $master_item_ids));

            foreach ($master_item_result->result_array() as $master_item_data){
                $data['master_item_arr'][element('master_item_id', $master_item_data)] = $master_item_data;
            }

            $worker_ids = array_column($data['list_datas'], 'create_worker_id');

            $worker_result = $this->ntics_user_model->getUsers(array('worker_id_in' => $worker_ids, 'active' => false), 'worker_id, rtrim(USER_NAME) as user_name, active');

            foreach ($worker_result->result_array() as $worker_data)
            {
                $data['worker_arr'][element('worker_id', $worker_data)] = array(
                	'user_name' => element('user_name', $worker_data),
					'active' => element('active', $worker_data)
				);
            }
        }

        if($this->input->get('excel') == 'Y') {

            $this->dowloadExcel($data);
            return;

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
        $this->load->view('item/soldout_exclude_item_list', $data);
        $this->load->view('common/footer', $footer_data);
    }

    private function getListFilterData()
    {
        $filter = array();
        $data['account_type'] = $this->input->get('account_type');

        if($data['account_type'] != '') $filter['account_type'] = $data['account_type'];

        $data['page_per_list'] = 30;

        $data['page'] = $this->input->get('page');

        if($data['page'] == '') $data['page'] = 1;

        $filter['order_by_uid'] = 'desc';
        //$filter['limit'] = array($data['page_per_list'], ($data['page'] - 1) * $data['page_per_list']);

        return array($filter, $data);
    }

    public function save()
    {
        $account_type = $this->input->post('account_type');
        $upc = $this->input->post('upc');

        $soldout_fg = $this->input->post('soldout_fg');
        $memo = $this->input->post("memo");

        if(trim($account_type) == '' || trim($upc) == '' || trim($soldout_fg)=='')
            return alert('필수값이 입력되지 않았습니다.');


        $master_item = $this->master_item_model->getMasterItem(array('upc'=> $upc), 'master_item_id');

        if(!is_array($master_item) || element('master_item_id', $master_item, '') == '')
            return alert('존재하지 않는 UPC 입니다.');

        if($this->soldout_exclude_item_model->countSoldoutExcludeItems(array('account_type' => $account_type, 'master_item_id' => element('master_item_id', $master_item))) > 0)
            return alert('이미 존재하는 데이터입니다.');

        if($account_type == '1'){
            $account_type  = $soldout_fg == '1'?'4':'1';
        }else{
            $account_type  = $soldout_fg == '1'?'8':'2';
        }



        $soldout_exclude_item_id = $this->soldout_exclude_item_model->add(
            array(
                'account_type' => $account_type,
                'master_item_id' => element('master_item_id', $master_item),
                'memo'  =>  $memo,
                'create_worker_id' => $this->session->userdata('qten_manager_id'),
                'create_date' => "date_format(now(), '%Y-%m-%d %H:%i:%s')"
            )
        );

        //history
        $history_data = array(
            "soldout_exclude_item_id"  => $soldout_exclude_item_id
        ,   "soldout_flag"  => $this->input->post("soldout_fg")
        ,   "create_worker_id"  => $this->session->userdata('qten_manager_id')
        ,   "create_date"  => "date_format(now(), '%Y-%m-%d %H:%i:%s')"
        );

        $this->soldout_exclude_item_model->addSoldoutExcludeItemHistory($history_data);

        alert('품절 예외상품 등록이 완료되었습니다.', site_url('item/soldout_exclude_item/index'));


    }

    public function delete()
    {
        $soldout_exclude_item_id = $this->input->post('soldout_exclude_item_id');

        $this->soldout_exclude_item_model->delete(array('soldout_exclude_item_id' => $soldout_exclude_item_id));

        alert('품절 예외상품 삭제가 완료되었습니다.', site_url('item/soldout_exclude_item/index'));

    }

    private function dowloadExcel($data)
    {
        $this->load->library('Excel');

        $objPHPExcel = new PHPExcel();
        $excel_title = '큐텐_품절예외상품_' . date('Y-m-d');
        $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
            ->setTitle($excel_title)
            ->setSubject($excel_title)
            ->setDescription($excel_title);

        $sheet = $objPHPExcel->getActiveSheet();

        $sheet->getCell('A1')->setValueExplicit('계정유형', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('B1')->setValueExplicit('UPC', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('C1')->setValueExplicit('브랜드', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('D1')->setValueExplicit('상품명', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('E1')->setValueExplicit('로케이션', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('F1')->setValueExplicit('현재고', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('G1')->setValueExplicit('등록일자', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('H1')->setValueExplicit('등록자', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('I1')->setValueExplicit('예외유형', PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('J1')->setValueExplicit('비고', PHPExcel_Cell_DataType::TYPE_STRING);

        $list_datas = element('list_datas', $data);

        $worker_arr = element('worker_arr', $data);

        $master_item_arr = element('master_item_arr', $data);

        $line_no = 2;

        foreach ($list_datas as $value){

            $current_master_item = element(element('master_item_id', $value), $master_item_arr, array());

            $sheet->getCell('A' . $line_no)->setValueExplicit(element('account_type', $value) == '1'|| element('account_type', $value) == '4' ? '해외사업자' : '국내사업자', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('B' . $line_no)->setValueExplicit(element('upc', $current_master_item), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('C' . $line_no)->setValueExplicit(element('mfgname', $current_master_item), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('D' . $line_no)->setValueExplicit(element('item_name', $current_master_item), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('E' . $line_no)->setValueExplicit(element('location', $current_master_item), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('F' . $line_no)->setValueExplicit(element('currentqty', $current_master_item), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('G' . $line_no)->setValueExplicit(element('create_date', $value), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('H' . $line_no)->setValueExplicit(element('user_name',$worker_arr[element('create_worker_id',$value,'')]), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('I' . $line_no)->setValueExplicit(element('account_type', $value) == '1'|| element('account_type', $value) == '2' ? '판매중' : '품절', PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->getCell('J' . $line_no)->setValueExplicit(element('memo', $value), PHPExcel_Cell_DataType::TYPE_STRING);

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

    public function updateExcludeItemFrom($soldout_exclude_item_id){
        if(!$soldout_exclude_item_id) return '';

        $item_info = $this->soldout_exclude_item_model->getSoldoutExcludeItem(array("soldout_exclude_item_id"=>$soldout_exclude_item_id));;

        $master_item_result = $this->master_item_model->getMasterItems(array('master_item_id' => element('master_item_id',$item_info)));

        foreach ($master_item_result->result_array() as $master_item_data){
            $data['master_item_arr'][element('master_item_id', $master_item_data)] = $master_item_data;
        }

        $data['soldout_exclude_item_info'] = $item_info;

        $this->load->view('item/update_soldout_exclude_item',$data);
    }

    public function updateExcludeItem(){

        if($this->input->post('soldout_exclude_item_id')=="")
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '잘못된 접근입니다.')));

        if($this->input->post("soldout_fg")==''){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error1', 'msg' => '예외유형을 선택해주세요.')));
        }
        $item_info = $this->soldout_exclude_item_model->getSoldoutExcludeItem(array("soldout_exclude_item_id"=>$this->input->post('soldout_exclude_item_id')));


        if(element('account_type', $item_info) == "1" || element('account_type', $item_info)== "4"){ // 1,4(품절)해외사업자
            $account_type  = $this->input->post("soldout_fg") == '1'?'4':'1';
        }else{ //2, 8(품절) 국내사업자
            $account_type  = $this->input->post("soldout_fg") == '1'?'8':'2';
        }


        if($account_type == element('account_type', $item_info) && element('memo', $item_info) ==$this->input->post("memo")){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error1', 'msg' => '이전과 값이 동일합니다.')));
        }

        $this->soldout_exclude_item_model->update(array("memo"=>$this->input->post("memo"),"account_type"=>$account_type, "create_worker_id"=>$this->session->userdata('qten_manager_id')), array("soldout_exclude_item_id"=>$this->input->post("soldout_exclude_item_id"), "account_type" => element('account_type', $item_info)));

            //history
        $history_data = array(
            "soldout_exclude_item_id"  => element('soldout_exclude_item_id',$item_info)
        ,   "soldout_flag"  => $this->input->post("soldout_fg")
        ,   "memo"=>$this->input->post("memo")
        ,   "create_worker_id"  => $this->session->userdata('qten_manager_id')
        ,   "create_date"  => "date_format(now(), '%Y-%m-%d %H:%i:%s')"
        );

        $this->soldout_exclude_item_model->addSoldoutExcludeItemHistory($history_data);


        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '수정이 완료되었습니다')));

    }

    public function deleteItems(){

        $item_id_chks = $this->input->post('item_id_chk');

        if (!is_array($item_id_chks) || count($item_id_chks) < 1) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '처리 대상이 존재하지 않습니다.')));
        }

        $db_soldoutExcludeItems_count = $this->soldout_exclude_item_model->countSoldoutExcludeItems(array('soldout_exclude_item_ids' => $item_id_chks));

        if ($db_soldoutExcludeItems_count - count(array_unique($item_id_chks)) <> 0) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '요청 대상 중 삭제처리가 불가능한 상품이 존재합니다.')));
        }

        $this->soldout_exclude_item_model->delete(array('soldout_exclude_item_ids' => $item_id_chks));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('result' => 'ok', 'msg' => '삭제처리가 완료되었습니다.')));
    }

    function saveExcelItems(){

        if(!$_FILES['excel']['tmp_name'] || !file_exists($_FILES['excel']['tmp_name'])){
            alert('파일 업로드 중 오류가 발생하였습니다. 다시 시도 해 주세요.');
            exit;
        }

        $create_time = date ("Y-m-d H:i:s");
        $create_times = date ("YmdHis",strtotime($create_time));
        $create_time_yyyy = date ("Y",strtotime($create_time));
        $create_time_mm = date ("m",strtotime($create_time));

        $upload_dir = '/ssd/html/qten/file/soldout_exclude_item/';

        if( !is_dir($upload_dir."/".$create_time_yyyy)){
            mkdir($upload_dir."/".$create_time_yyyy);
            chmod($upload_dir."/".$create_time_yyyy, 0777);
        }
        if( !is_dir($upload_dir."/".$create_time_yyyy."/".$create_time_mm)){
            mkdir($upload_dir."/".$create_time_yyyy."/".$create_time_mm);
            chmod($upload_dir."/".$create_time_yyyy."/".$create_time_mm, 0777);
        }

        $config['upload_path'] = './file/soldout_exclude_item/'.$create_time_yyyy."/".$create_time_mm."/";
        $config['overwrite'] = true;
        $config['encrypt_name'] = false;
        $config['max_filename'] = 0;
        $config['allowed_types'] = 'xls|xlsx';
        $config['file_name'] = 'item_soldexclude_'.$create_times;

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

        $excel_data_total_cnt = 0;
        $excel_data_total_cnts = 0;
        $account_type = array('해외사업자','국내사업자');

        $soldout_fg_arr = array('판매중','품절');

        $upc_array1 = array();
        $upc_array2 = array();
        $upc_array3 = array();
        $cn123t=0;
        foreach ($sheetData as $row) {
            $cn123t++;
            $channel_code = trim(element('A',$row,''));
            $upc = trim(element('B',$row,''));

            $soldout_fg = trim(element('C',$row,''));
            $memo = trim(element('D', $row, ''));

            if($excel_data_total_cnts==0 && ($channel_code != '계정유형' || $upc!='UPC')){
                return alert('A1,B1은 계정유형,UPC로 입력이 되어있어야합니다.');
            }

            if($excel_data_total_cnts==0 && $channel_code == '계정유형' && $upc=='UPC'){
                $excel_data_total_cnts++;
                continue;
            }

            if($channel_code=='' ||  $upc=='' || $soldout_fg==''){
                return alert($upc.$soldout_fg.$channel_code.'입력이 되지 않는 필수값이 존재합니다.'.$cn123t.'행');
            }

            if(!in_array($channel_code,$account_type)){
                return alert($channel_code.'계정유형을 잘못 입력하였습니다.');
            }

            if(!in_array($soldout_fg,$soldout_fg_arr)){
                return alert($soldout_fg.'예외유형을 잘못 입력하였습니다.');
            }

            $excel_data_total_cnt++;

            $channel_code = $channel_code =='해외사업자' ? '1':'2';

            if($channel_code=='1'){
                $channel_codes = $soldout_fg=='판매중' ?'1':'4';
                array_push($upc_array1,$upc);
            }else{
                $channel_codes = $soldout_fg=='판매중' ?'2':'8';
                array_push($upc_array2,$upc);
            }

            array_push($upc_array3,$upc);

            $soldout_exclude_items[] = array(
                'account_type' => $channel_codes,
                'upc' => $upc,
                'memo' => $memo
            );


        }

        $upc_chk_count1 = array_count_values($upc_array1);

        $upc_chk_count2 = array_count_values($upc_array2);

        $upc_array3 = array_chunk(array_unique($upc_array3),100);

        $master_item_arr =array();

        foreach($upc_array3 as $value){
            $master_item = $this->master_item_model->getMasterItems(array('upc_where_in'=> $value), 'master_item_id,upc');


            foreach ($master_item->result_array() as $itemvalue){
                $master_item_arr[element('upc',$itemvalue)] = element('master_item_id',$itemvalue);
            }
        }

        $upc_array = array_chunk($master_item_arr,100);
        $soldout_exclude_chk = array();

        foreach ($upc_array as $itemvalue){

            $getSoldoutExcludeItems = $this->soldout_exclude_item_model->getSoldoutExcludeItems(array('master_item_id_in'=> $itemvalue), 'master_item_id,account_type');

            foreach ($getSoldoutExcludeItems->result_array() as $itemvalues){
                $soldout_exclude_chk[element('master_item_id',$itemvalues)][element('account_type',$itemvalues)] = element('account_type',$itemvalues);
            }

        }

        $add_cnt =0;
        $excel_cnt = 0;
        $soldout_cnt = 0;

        $add_soldout_exclude_items  =array();
        foreach ($soldout_exclude_items as $additem){

            $master_item_id  = '';

            if(!element(element('upc',$additem),$master_item_arr,false)){
                return alert('존재하지 않는 UPC('.element('upc',$additem).')가 있습니다. ');
            }

            $master_item_id = element(element('upc',$additem),$master_item_arr,false);
            $upc =element('upc',$additem);
            $account_type =element('account_type',$additem);
            $memo = element('memo', $additem);

            $account_fg1 = $account_type=='1'|| $account_type =='4'?'a':'b';

            if($account_fg1 =='a'){
                if( element($upc,$upc_chk_count1)>1){
                    $excel_cnt++;
                    continue;
                }
            }else{
                if( element($upc,$upc_chk_count2)>1){
                    $excel_cnt++;
                    continue;
                }
            }

            if(!empty($soldout_exclude_chk)) {
                $cnt_fg =1;

                if(element($master_item_id,$soldout_exclude_chk)) {
                    foreach ($soldout_exclude_chk[$master_item_id] as $account_type_value){
                        $account_fg2 = $account_type_value=='1'|| $account_type_value =='4'?'a':'b';
                        if($account_fg1 == $account_fg2) {
                            $cnt_fg=2;
                    }
                    }
                }
                if($cnt_fg==2){
                    $soldout_cnt++;
                    $cnt_fg =1;
                    continue;
                }
            }

            $add_cnt ++;
            $add_soldout_exclude_items[]= array(
                'account_type' => $account_type,
                'master_item_id' => $master_item_id,
                'memo' => $memo,
                'create_worker_id' => $this->session->userdata('qten_manager_id'),
                'create_date' => date ("Y-m-d H:i:s")

            );
        }

        if(empty($add_soldout_exclude_items)){
            alert('업로드된 데이터가 없습니다.', site_url('item/soldout_exclude_item/index'));
        }

        $this->soldout_exclude_item_model->addBulk($add_soldout_exclude_items);


        //history

        foreach ($add_soldout_exclude_items as $add_soldout_exclude_item) {
            $soldout_exclude_item_id = $this->soldout_exclude_item_model->getSoldoutExcludeItem(array("master_item_id" => element('master_item_id', $add_soldout_exclude_item), "account_type" => element('account_type', $add_soldout_exclude_item)), "soldout_exclude_item_id");

            $soldout_fg = (element('account_type',$add_soldout_exclude_item) == "4" || element('account_type',$add_soldout_exclude_item ) == "8") ? "1" : "2";

            //history
            $history_data = array(
                "soldout_exclude_item_id"  => element('soldout_exclude_item_id',$soldout_exclude_item_id)
            ,   "soldout_flag"  => $soldout_fg
            ,   "create_worker_id"  => $this->session->userdata('qten_manager_id')
            ,   "create_date"  => "date_format(now(), '%Y-%m-%d %H:%i:%s')"
            );

            $this->soldout_exclude_item_model->addSoldoutExcludeItemHistory($history_data);


        }


        alert("총 데이터 : ".$excel_data_total_cnt.'건\\n등록 성공 : '.$add_cnt .'건\\n엑셀파일 중복 상품 : '.$excel_cnt.'건\\n이미 등록된 UPC : '.$soldout_cnt.'건', site_url('item/soldout_exclude_item/index'));

    }

}
<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-30
 * Time: 오후 9:05
 */
class Customer_number extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
        $this->load->model('order/order_model');
        $this->load->model('customer_number/customer_number_history_model');
        $this->load->model('user/ntics_user_model');

        //masterId 체크하고 리스트 불러오기
        $this->load->library('master_id', array('chk_master_id' => true));
        $this->master_id_list = $this->master_id->getMasterId();

    }

    public function index()
    {
        $this->list();

    }
    private function list(){

        $data = array();

        $channel_result = $this->channel_info_model->getChannelInfos(array('channel_code'=>'G'));

        foreach ($channel_result->result_array() as $channel_info){

            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;

        }

        $filter= array('limit'=>array('30','0'));

        $select ='b.comment, upload_file_name, row_count, apply_count, upload_date, worker_id';

        $orderby = array('upload_date'=>'desc');

        $data['list_data_result'] = $this->customer_number_history_model->getCustomer_num_historys($filter,$select,$orderby);

        $worker_id = array();
        foreach ($data['list_data_result']->result_array() as $history_value){
            if(element('worker_id',$history_value)){
                array_push($worker_id,element('worker_id',$history_value));
            }

        }

        if(count($worker_id)>0){
            $worker_result = $this->ntics_user_model->getUsers(array('worker_id_in'=>$worker_id),'worker_id,USER_NAME');

            foreach ($worker_result->result_array() as $value){
                if(element('worker_id',$value,false))
                $data['worker'][element('worker_id',$value,false)] = element('USER_NAME',$value,false);
            }
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

        $this->load->view('common/header',$header_data);
        $this->load->view('order/customer_number_upload',$data);
        $this->load->view('common/footer',$footer_data);
    }

    function save_excel(){

        $channel_id = $this->input->post('channel_id');

        if(!$channel_id  || !is_numeric($channel_id)){
            alert('채널을 선택 해주세요. ');
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

        $upload_dir = '/ssd/html/qten/file/customer_number/';

        if( !is_dir($upload_dir."/".$create_time_yyyy)){
            mkdir($upload_dir."/".$create_time_yyyy);
            chmod($upload_dir."/".$create_time_yyyy, 0777);
        }
        if( !is_dir($upload_dir."/".$create_time_yyyy."/".$create_time_mm)){
            mkdir($upload_dir."/".$create_time_yyyy."/".$create_time_mm);
            chmod($upload_dir."/".$create_time_yyyy."/".$create_time_mm, 0777);
        }

        $config['upload_path'] = './file/customer_number/'.$create_time_yyyy."/".$create_time_mm."/";
        $config['overwrite'] = true;
        $config['encrypt_name'] = false;
        $config['max_filename'] = 0;
        $config['allowed_types'] = 'xls|xlsx';
        $config['file_name'] = 'customer_numbers_'.$create_times;

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
        $excel_data_result_cnt = 0;

        $cart_numbers = array();
        $customer_number =array();

        foreach ($sheetData as $row) {

            if($excel_data_total_cnt==0){
                $excel_data_total_cnt++;
                continue;
            }

            if(element('A',$row,false)){

                array_push($cart_numbers,trim(element('A',$row,false)));
                $customer_number[trim(element('A',$row,false))] = trim(element('B',$row,''));

                $excel_data_result_cnt++;
            }
            $excel_data_total_cnt++;
        }

        $filter = array();
        $filter['package_no_in'] = $cart_numbers;
        $filter['validate_errors'] = '1';
        $filter['status'] = '1';
        $filter['channel_id'] = $channel_id;

        $result=$this->order_model->getOrders($filter,'o.package_no');

        $update_data_cnt =0;

        $select_data_cnt = $result->num_rows();

        if ( $select_data_cnt> 0) {

            foreach ($result->result_array() as $select_cart_id) {

                if (!element('package_no', $select_cart_id, false)) {
                    continue;
                }

                $update_data =
                    array(
                        'customer_number' => element(element('package_no', $select_cart_id, false), $customer_number, ''),
                        'validate_error' => 'cast(validate_error as int) - 1'
                    );

                $where = array(
                    'package_no' => element('package_no', $select_cart_id, false),
                    'status' => '1',
                    'validate_errors' => '1',
                    'channel_id'=>$channel_id
                );

                $this->order_model->updateOrder($update_data,$where);

                $update_data_cnt++;
            }

            $update_data1 =
                array(
                    'status' => '3'
                );

            $where1 = array(
                'status' => '1',
                'validate_error' => '0',
                'channel_id'=>$channel_id
            );

            $this->order_model->updateOrder($update_data1,$where1);
        }

        $insert_data = array(
            'channel_id'=>$channel_id,
            'upload_file_name'=>$_FILES['excel']['name'],
            'row_count'=>$excel_data_result_cnt,
            'apply_count'=>$update_data_cnt,
            'upload_date' =>$create_time,
            'worker_id'=> $this->session->userdata('qten_worker_id')

        );

        $this->customer_number_history_model->addCustomer_num_history($insert_data);

        alert("업데이트 완료 되었습니다",site_url('order/customer_number') );
        exit;
    }
}


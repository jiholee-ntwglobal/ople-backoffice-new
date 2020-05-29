<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-01-22
* Time : 오전 11:57
*/

defined('BASEPATH') or exit('No direct script access allowed');

class Ople_mapping_change extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('manager_auth');
        $this->load->model('user/ntics_user_model');
        $this->load->model('item/ople_mapping_change_history');

        //masterId 체크하고 리스트 불러오기
        $this->load->library('master_id', array('chk_master_id' => true));
        $this->master_id_list = $this->master_id->getMasterId();
    }

    public function index(){
        $this->list();
    }

    public function list(){
        $filter = array();
        $filter['fastoms_list_mode'] = true;
        $filter['action_id_NULL'] = true;

        if($this->input->get('excel_fg') != 'Y') {

            $data['total_count'] = $this->ople_mapping_change_history->getOpleMappingChangeHistoryCount($filter);
            $page_per_list =  100;

            $page = (int)$this->input->get('page');
            if ($page < 1) $page = 1;

            $filter['page'] = array("start"=>($page - 1) * $page_per_list, "end"=> $page_per_list);

            $data['page_per_list'] = $page_per_list;

        }

        if($this->input->get('it_id')!="") $filter['it_id'] = $this->input->get('it_id');
        $data['it_id'] = $this->input->get('it_id');


        $select = "distinct(h.id), h.it_id, h.bf_upcs, h.af_upcs, h.cdate, m.Ople_Type";
        $ople_mapping_change_result = $this->ople_mapping_change_history->getOpleMappingChangeHistorys($filter,$select);

        $ople_mapping_change_infos = array();

        foreach ($ople_mapping_change_result->result_array() as $ople_mapping_change_info){
            array_push($ople_mapping_change_infos, $ople_mapping_change_info);
        }

        $data['list_datas'] = $ople_mapping_change_infos;

        if($this->input->get('excel_fg') == 'Y'){


            $field_arr = array(
                "ITID"		=> "it_id"
            ,	"상품타입"		=> "Ople_Type"
            ,	"변경전 UPC"		=> "bf_upcs"
            ,	"변경후 UPC"	=> "af_upcs"
            ,	"변경 날짜"		=> "cdate"
            );

            $this->download_Excel('오플매핑변경리스트_뉴베이' ,$field_arr,$data);

        } else {

            $this->load->library('pagination');

            $paging_config['base_url'] = site_url('11st/item/opleMappingChangeList');
            $url = parse_url($_SERVER['REQUEST_URI']);
            parse_str(element('query', $url), $params);
            if (isset($params['page'])) unset($params['page']);
            $paging_config['base_url'] .= '?' . http_build_query($params);

            $paging_config['total_rows'] =  $data['total_count'];
            $paging_config['num_links'] = 5;
            $paging_config['per_page'] = $data['page_per_list'];
            $paging_config['use_page_numbers'] = TRUE;
            $paging_config['page_query_string'] = TRUE;
            $paging_config['query_string_segment'] = 'page';

            $this->pagination->initialize($paging_config);

            $data['paging_content'] = $this->pagination->create_links();

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
            $this->load->view('item/ople_mapping_change_list', $data);
            $this->load->view('common/footer', $footer_data);

        }

    }

    public function MappingChangeAction(){
        $mapping_ids = $this->input->post('mapping_ids');

        if(count($mapping_ids) > 0) {

            foreach ($mapping_ids as $mapping_id) {

                $insert_data = array(
                    "ople_mapping_history_id" => $mapping_id
                ,   "channel_id" => 3
                ,   "worker_id" => $this->session->userdata('oms_manager_name')
                ,   "cdate" => date('Y-m-d H:i:s')

                );

                $this->ople_mapping_change_history->addMappingChangeAction($insert_data);
            }

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'ok', 'msg' => '매핑 변경 이력확인 처리가 완료되었습니다.')));
        }else{
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('result' => 'error', 'msg' => '매핑 변경 이력확인 처리할 데이터가 존재하지 않습니다.')));
        }
    }

    /** 엑셀 다운롤드 **/
    private function download_Excel($title,$field, $data){

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
        $order_product_store_type = element('order_product_store_type', $data);
        $line_no = 2;

        foreach ($list_datas as $list_data){
            $string = "A";
            foreach ($field as $field_key=>$field_val) {
                switch ($field_val){
                    case 'Ople_Type':
                        $excel_val = (element('Ople_Type',$list_data)=="m")? "단품" : "세트";
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
}

?>
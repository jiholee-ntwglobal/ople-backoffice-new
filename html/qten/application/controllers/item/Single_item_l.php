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
    public function __construct()
    {
        parent::__construct();
        //$this->load->library('manager_auth');
        $this->load->model('channel/channel_info_model');
        $this->load->model('item/master_item_model');
        $this->load->model('item/soldout_history_model');
        $this->load->model('user/ntics_user_model');
        $this->load->model('item/channel_item_info_model');
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

        $data['upc'] = element('upc',$_GET,'');

        $data['brand'] = element('brand', $_GET, '');

		$data['vcode'] = element('vcode', $_GET, '');

		$data['channel_item_code'] = element('channel_item_code', $_GET, '');


        $channel_result = $this->channel_info_model->getChannelInfos(array());

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

                $string = preg_replace("/\s/",'', $split_search_value);

                if($string != '') array_push($ntics_filter['upc_where_in'], $string);
            }
        }

		if($data['channel_item_code']!=''){

			$filter['channel_item_code_in'] = array();

			$split_search_values = explode(PHP_EOL, $this->input->get('channel_item_code'));

			foreach ($split_search_values as $split_search_value){

				$string = preg_replace("/\s/",'', $split_search_value);

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

        $select ='i.item_info_id, b.comment, i.channel_item_code, i.discount_price, i.discount_unit, i.channel_id, v.virtual_item_id, item_alias, i.create_date, vd.master_item_id, i.origin_price,  i.update_date, i.upload_price';

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

        $data['total_count'] = $this->channel_item_info_model->getChannelItemInfosCount($filter);

        $paging_config['total_rows'] = $data['total_count'];
        $paging_config['num_links'] = 5;
        $paging_config['per_page'] = $data['page_per_list'];
        $paging_config['use_page_numbers'] = TRUE;
        $paging_config['page_query_string'] = TRUE;
        $paging_config['query_string_segment'] = 'page';

        $this->pagination->initialize($paging_config);

        $data['paging_content'] = $this->pagination->create_links();

        $date_result = $this->channel_item_info_model->getChannelItemInfos($filter, $select, array('i.create_date'=>'desc'));

        foreach ($date_result ->result_array() as $date_info){

            array_push($data['list_datas'],$date_info);

        }

        $data['master_item_arr'] = array();

        if(count($data['list_datas']) > 0){

            $master_item_ids = array_column($data['list_datas'], 'master_item_id');

            $master_item_result = $this->master_item_model->getMasterItems(array('master_item_id_in' => $master_item_ids));

            foreach ($master_item_result->result_array() as $master_item_data){
                $data['master_item_arr'][element('master_item_id', $master_item_data)] = $master_item_data;
            }


        }


        if($this->input->get('excel') == 'Y') {

            $this->dowloadExcel($data);
            exit;

        }

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', '', true)
        );
        $footer_data = array(
            'left_menu_on' => true
        );
        $this->load->view('common/header', $header_data);
        $this->load->view('item/single_item_list', $data);
        $this->load->view('common/footer', $footer_data);

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
        $sheet->getCell('L1')->setValueExplicit('수정날짜', PHPExcel_Cell_DataType::TYPE_STRING);

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
            $sheet->getCell('L' . $line_no)->setValueExplicit(element('update_date',$value,''), PHPExcel_Cell_DataType::TYPE_STRING);

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

        $channel_id_info = $this->channel_info_model->getChannelInfo(array('channel_id'=>$item_info['channel_id']));

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

        $result  = false;
        switch ($channel_id_info['channel_id']){

            case '1' : //fastople gmarket
                $result = $this->sendPrice($channel_item_code,element('basic_price',$price_info));

                if(element('discount_type',$price_info) != 'N'){
                    $result  = $this->sendDiscountPrice($channel_item_code,$price_info);
                }else{

                    $price_info = array(
                        'discount_type'=>'Rate',//임의의값
                        'discount_value'=>'0',
                        'basic_price'=>$param['basic_price']
                    );

                    $result  = $this->sendDiscountPrice($channel_item_code,$price_info);
                }
                break;

            case '2' : //fastople auction
                $result = $this->callAuctionPriceUpdate($channel_item_code,$price_info);
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

        $filter= array(
            'item_info_id' => $param['item_info_id']
        );

        $this->channel_item_info_model->updateChannelItemInfo($update_date,$filter);

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
		$this->api_key	= 'd310kxymI5jbPsYSxgyJ4M9BkjJbtr8HCRcsVRFRK34TOnBIhjWapNEP/kfX7fk0oL/mvCc2bBG9VItchZXNuX0nP5xx1c4/PDd+03Dp0b8+uZpHQPr/3hy4kSD3g4D+X4mYkO7BPw2VRvgXd966yJ44honypujpOuokhesVrSPGolEF5HAWQY4Jewkxlub9mdMEKSVqH4MNgvlAH3OXR+s=';

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

//		$sync_result	= requestAuction($serverUrl, $action, $requestXmlBody);
        return requestAuction($serverUrl, $action, $requestXmlBody);

    }

	private function sendPrice($channel_item_code, $price)
	{
		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
        $this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
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
		$this->api_key	= '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
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

	public function insertSingleItemForm(){

        $data = array();

        $channel_result = $this->channel_info_model->getChannelInfos(array());

        foreach ($channel_result->result_array() as $channel_info){

            $data['channel_arr'][element('channel_id', $channel_info)] = $channel_info;

        }

        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', '', true)
        );
        $footer_data = array(
            'left_menu_on' => true
        );
        $this->load->view('common/header', $header_data);
        $this->load->view('item/single_item_insert', $data);
        $this->load->view('common/footer', $footer_data);
    }

    public function insertSingleItem(){

        $channel_id = $this->input->post('channel_id');
        $channel_item_code = $this->input->post('channel_item_code');
        $virtual_item_id = (int) str_replace("v","",str_replace("V","", $this->input->post('virtual_item_id')));
        $upload_price = str_replace(",","",$this->input->post('upload_price'));
        $orgin_price = (($upload_price/100) * 85);

        if($channel_id == "" || $channel_item_code == "" || $virtual_item_id == "" || $upload_price == ""){
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
           'create_date' =>"now()",
           "need_update" => "N",
           "sell_status" => "Y",
           "stock_status" =>"Y"
        );

        $insert_key = $this->channel_item_info_model->insertItem($channel_item_insert_arr);

        if($insert_key){
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('msg' => '저장이 완료되었습니다.')));
        }

    }

    function updateProductPrice(){

        echo 'start';

        $update_data_result = $this->channel_item_info_model->getItemPriceUpdateHistoryInfos(array('upload_fg' => '1','update_join'=>'Y'),'a.item_history_id, b.channel_id, a.channel_item_code, a.upload_price, a.discount_price, a.discount_unit, c.item_info_id');

        $discount_unit_arr =array('RATE','MONEY');

        foreach ($update_data_result ->result_array() as $date_info){


            $upate_historyfilter = array('item_history_id'=> element('item_history_id',$date_info));

            $channel_item_code = element('channel_item_code',$date_info);

            if(!in_array(strtoupper(element('discount_unit',$date_info)),$discount_unit_arr)){
                $date_info['discount_unit'] = 'N';
            }

            $discount_unit = ucwords(strtolower(element('discount_unit',$date_info)));;

            if(!is_numeric(element('discount_price',$date_info,'')) && element('discount_price',$date_info,'')!=''&& $discount_unit!='N'){
                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'4','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
                continue;
            }

            $discount_value = element('discount_price',$date_info,'');

            if(!is_numeric(element('upload_price',$date_info,'')) || element('upload_price',$date_info,'')=='0'){
                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'4','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
                continue;
            }
            $upload_price = element('upload_price',$date_info);

            $price_info = array(
                'discount_type'=>$discount_unit,
                'discount_value'=>$discount_value,
                'basic_price'=>$upload_price
            );

            $result  = false;
            switch ($date_info['channel_id']){

                case '1' : //fastople gmarket
                    $result = $this->sendPrice($channel_item_code,element('basic_price',$price_info));

                    if($discount_unit != 'N'){

                        $result  = $this->sendDiscountPrice($channel_item_code,$price_info);

                    }else{

                        $price_info = array(
                            'discount_type'=>'Rate',//임의의값
                            'discount_value'=>'0',
                            'basic_price'=>$upload_price
                        );

                        $result  = $this->sendDiscountPrice($channel_item_code,$price_info);

                    }

                    break;

                case '2' : //fastople auction
                    $result = $this->callAuctionPriceUpdate($channel_item_code,$price_info);
                    break;
            }

            if($result===false){
                $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'3','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);
                continue;
            }


            $this->channel_item_info_model->updaItetemPriceUpdateHistory(array('upload_fg'=>'2','upload_date'=>date ("Y-m-d H:i:s")),$upate_historyfilter);

            $update_date = array(
                'upload_price' => $upload_price,
                'discount_price' => $discount_value,
                'discount_unit'=> $discount_unit/*,
                'need_update'=>'Y'*/
            );

            if($date_info['channel_id']=='1' && $date_info['stock_status']=='N'){
                $update_date['need_update'] ='Y';
            }

            $filter= array(
                'item_info_id' => $date_info['item_info_id']
            );

            //channel_item_info
            $this->channel_item_info_model->updateChannelItemInfo($update_date,$filter);

        }

        echo 'end';

        return ;
    }

    function updateProductPriceList(){

        $start_dt = $this->input->get('start_dt');
        $end_dt = $this->input->get('end_dt');

        if($start_dt == '') $start_dt = date('Y-m-d');
        if($end_dt == '') $end_dt = date('Y-m-d');

        $data['start_dt'] = $start_dt;
        $data['end_dt'] = $end_dt;

        $filter['create_date_between'] = array($start_dt, $end_dt);

        $data['total_count'] = $this->channel_item_info_model->getItemPriceUpdateHistorycount($filter);

        $page_per_list = $this->input->get('page_per_list') ? $this->input->get('page_per_list') : 100;

        $page = (int)$this->input->get('page');
        if($page<1) $page=1;

        $filter['single_limit'] = array($page_per_list, ($page - 1) * $page_per_list);

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

        $date_result = $this->channel_item_info_model->getItemPriceUpdateHistoryInfos($filter,'',array('upload_fg'=>'asc'));

        $data['create_date_arr'] =array();
        $worker_id = array();

        foreach ($date_result ->result_array() as $date_info){

            array_push($data['create_date_arr'],$date_info);

            if(element('worker_id',$date_info)){
                array_push($worker_id,element('worker_id',$date_info));
            }

        }

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


        $header_data = array(
            'left_menu'=> $this->load->view('common/left_menu', '', true),
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

           $channel_code = trim(element('A',$row,''));
           $account_id = trim(element('B',$row,''));
           $channel_item_code = trim(element('C',$row,''));
           $upload_price = trim(element('D',$row,''));


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
                'channel_code'=>$channel_code,
                'account_id'=>$account_id,
                'channel_item_code'=>$channel_item_code,
                'upload_price'=>$upload_price,
                'discount_price'=>trim(element('F',$row,'')),
                'discount_unit'=>trim(element('E',$row,'')),
                'upload_fg'=>'1',
                'create_date'=>$create_time,
                'worker_id'=>$this->session->userdata('qten_worker_id'),
            );

            $this->channel_item_info_model->addItemPriceUpdateHistory($add_data);

            $excel_data_result_cnt++;

        }

        alert('총 :'.$excel_data_total_cnt."데이터 중 ".$excel_data_result_cnt." 데이터 업로드 완료 되었습니다",site_url('/item/single_item/updateProductPriceList') );
        exit;

    }
}
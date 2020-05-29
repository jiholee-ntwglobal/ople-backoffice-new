<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-11-22
 * File: Oms_product.php
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Oms_product_testg9  extends CI_Controller
{
	private $channel;
	private $account;
	private $channel_id;

	private $channel_config;
	private $ticket;

	private $product_official_info;
	private $product_category_info;
	private $product_brand_info;

	private $item_additional_info	= array();
	private $free_product	= array
	('V00012156','V00012175','V00015692','V00015694','V00012193','V00012192','V00012182','V00012183','V00012194','V00012199'
	,'V00012203','V00017431','V00017430','V00017429','V00012179','V00017529','V00017530','V00017531','V00017532','V00017540'
	,'V00017541','V00017729','V00017730','V00017735','V00014725','V00014724','V00017920','V00017820','V00017819','V00017818'
	,'V00017817','V00018041','V00018042','V00018043','V00018044','V00009453','V00018930','V00018929','V00000185','V00010178'
	,'V00012197','V00012176','V00009452','V00009454','V00009455','V00010175','V00010176','V00010177','V00014708','V00012177'
	,'V00012181','V00014717','V00014718','V00009456','V00010321','V00014715','V00017766','V00017774','V00017776');
	private $price_info	= array
	('V00012156'=>'38900','V00012175'=>'32900','V00012193'=>'68900','V00012192'=>'68900','V00012182'=>'63900','V00012183'=>'63900','V00012194'=>'68900','V00012199'=>'68900','V00012203'=>'68900','V00017431'=>'37900'
	,'V00017430'=>'37900','V00017429'=>'37900','V00012179'=>'66900','V00017529'=>'43900','V00017530'=>'66900','V00017531'=>'43900','V00017532'=>'43900','V00017540'=>'43900','V00017541'=>'43900','V00017729'=>'49900'
	,'V00017730'=>'49900','V00017735'=>'29900','V00014725'=>'55900','V00014724'=>'53900','V00017920'=>'46900','V00017820'=>'51900','V00017819'=>'51900','V00017818'=>'51900','V00017817'=>'51900','V00018041'=>'33900'
	,'V00018042'=>'33900','V00018043'=>'33900','V00018044'=>'33900','V00009453'=>'57900','V00018930'=>'62900','V00018929'=>'60800','V00000185'=>'46900','V00010178'=>'88900','V00012197'=>'50900','V00012176'=>'39900'
	,'V00009452'=>'57900','V00009454'=>'57900','V00009455'=>'57900','V00010175'=>'57900','V00010176'=>'57900','V00010177'=>'57900','V00014708'=>'44900','V00012177'=>'52900','V00012181'=>'63900','V00014717'=>'58900'
	,'V00014718'=>'38900','V00009456'=>'57900','V00010321'=>'29900','V00014715'=>'67500','V00017766'=>'49900','V00017774'=>'54900','V00017776'=>'54900','V00015176'=>'22900','V00015685'=>'17900','V00015172'=>'25900'
	,'V00015174'=>'29900','V00015687'=>'25900','V00015175'=>'29900','V00015691'=>'16900','V00015681'=>'16900','V00017726'=>'21900','V00018017'=>'31900','V00018019'=>'31900','V00018016'=>'31900','V00014734'=>'35500'
	,'V00012184'=>'26900','V00012166'=>'11900','V00008572'=>'23900','V00008573'=>'21900','V00010181'=>'11900','V00010179'=>'30900','V00009492'=>'15900','V00012173'=>'17900','V00014711'=>'22500','V00017762'=>'42900'
	,'V00015171'=>'19900','V00017423'=>'42900','V00017421'=>'42900','V00017424'=>'42900','V00017790'=>'37900','V00017791'=>'37900','V00018013'=>'31900','V00018012'=>'31900','V00018014'=>'31900','V00018015'=>'31900'
	,'V00018018'=>'31900','V00018215'=>'30900','V00018212'=>'30900','V00018213'=>'30900','V00018214'=>'30900','V00017763'=>'42900','V00017422'=>'42900','V00015692'=>'119000','V00015694'=>'119000');

	function __construct()
	{

		parent::__construct();

		$this->load->model('product_temp/openmarket_model');
		$this->load->model('product_temp/customs_clearance_model');
        $this->load->model('item/channel_item_info_model');

//		$this->load->library('supplement_facts');
		$this->load->library('Master_item_tmp');


		$this->product_official_info = array();
		$this->product_category_info = array();

		set_time_limit(0);
	}

	private function setChannel($channel, $account){
		$this->channel	= $channel;
		$this->account	= $account;

		$this->config->load($channel.'_'.$account.'_api_config', true);
		$this->channel_config	= $this->config->item($channel.'_'.$account.'_api_config');

		$this->ticket = element('ticket',$this->channel_config);
	}

	public function uploadItem($channel='', $account='')
	{
		if($channel=='' || $account==''){
			echo 'channel & account required';
			return false;
		}

		$this->setChannel($channel, $account);

		ini_set('max_execution_time',0);
		ini_set('memory_limit',-1);

		$channel_item_arr	= array(
		 'V00000185'=>'1621309606','V00008572'=>'1621337744','V00008573'=>'1621337789','V00009452'=>'1621309862','V00009453'=>'1621309421','V00009454'=>'1621309913','V00009455'=>'1621309964','V00009456'=>'1621310553','V00009492'=>'1621337919','V00010175'=>'1621310027'
		,'V00010176'=>'1621310093','V00010177'=>'1621310165','V00010178'=>'1621309673','V00010179'=>'1621337876','V00010181'=>'1621337833','V00010321'=>'1621310607','V00012156'=>'1621306176','V00012166'=>'1621337699','V00012173'=>'1621337973','V00012175'=>'1621307464'
		,'V00012176'=>'1621309797','V00012177'=>'1621310296','V00012179'=>'1621308156','V00012181'=>'1621310350','V00012182'=>'1621307729','V00012183'=>'1621307787','V00012184'=>'1621337634','V00012192'=>'1621307672','V00012193'=>'1621307624','V00012194'=>'1621307854'
		,'V00012197'=>'1621309736','V00012199'=>'1621307903','V00012203'=>'1621307957','V00014708'=>'1621310223','V00014711'=>'1621338028','V00014715'=>'1621310657','V00014717'=>'1621310423','V00014718'=>'1621310496','V00014724'=>'1621308838','V00014725'=>'1621308770'
		,'V00014734'=>'1621311970','V00015171'=>'1621338126','V00015172'=>'1621311057','V00015174'=>'1621311122','V00015175'=>'1621311271','V00015176'=>'1621310935','V00015681'=>'1621311458','V00015685'=>'1621310996','V00015687'=>'1621311201','V00015691'=>'1621311372'
		,'V00015692'=>'1621307515','V00015694'=>'1621307564','V00017421'=>'1621338220','V00017422'=>'1621339032','V00017423'=>'1621338172','V00017424'=>'1621338268','V00017429'=>'1621308117','V00017430'=>'1621308062','V00017431'=>'1621308006','V00017529'=>'1621308215'
		,'V00017530'=>'1621308272','V00017531'=>'1621308334','V00017532'=>'1621308413','V00017540'=>'1621308461','V00017541'=>'1621308525','V00017726'=>'1621311564','V00017729'=>'1621308578','V00017730'=>'1621308636','V00017735'=>'1621308712','V00017762'=>'1621338084'
		,'V00017763'=>'1621338973','V00017766'=>'1621310734','V00017774'=>'1621310809','V00017776'=>'1621310878','V00017790'=>'1621338318','V00017791'=>'1621338369','V00017817'=>'1621309147','V00017818'=>'1621309081','V00017819'=>'1621309018','V00017820'=>'1621308961'
		,'V00017920'=>'1621308893','V00018012'=>'1621338472','V00018013'=>'1621338409','V00018014'=>'1621338534','V00018015'=>'1621338595','V00018016'=>'1621311869','V00018017'=>'1621311660','V00018018'=>'1621338675','V00018019'=>'1621311772','V00018041'=>'1621309203'
		,'V00018042'=>'1621309258','V00018043'=>'1621309305','V00018044'=>'1621309364','V00018212'=>'1621338798','V00018213'=>'1621338851','V00018214'=>'1621338922','V00018215'=>'1621338730','V00018929'=>'1621309552','V00018930'=>'1621309487'
		);


//		$filter	= array(
//			'channel_id'	=> 1
//		,	'test'			=> 1
//		,	'vcode_in'		=> array('V00015227')
//		);
//		$filter['no_upc_in'] = array();
//		$ban_upc_datas = $this->customs_clearance_model->getBanProductUPC();
//		foreach ($ban_upc_datas->result_array() as $ban_upc){
//			array_push($filter['no_upc_in'], rtrim(element('upc', $ban_upc)));
//		}
//		$filter['no_it_maker_in']	= $this->config->item('except_maker');
//		$filter['it_use']			= '1';
//		$filter['it_discontinued']	= '0';
//		$filter['no_it_id_in']		= $this->config->item('except_item_code');
//		$filter['bigger_it_id']		= $this->config->item('bigger_it_id');

		$this->channel_id	= $this->master_item_tmp->setChannelId($channel, $account, $this->channel_config);

//		$no_reg_items	= $this->master_item_tmp->getNoRegItems($filter);
		$product_cnt	= 0;

		$no_reg_items	= array(
//		 'V00012156'=>'1510528513'
		'V00012175'=>'1511066080','V00015692'=>'1511243140','V00015694'=>'1511243240','V00012193'=>'1511290060','V00012192'=>'1511290160','V00012182'=>'1511329062','V00012183'=>'1511329162','V00012194'=>'1511329362','V00012199'=>'1511329462'
		,'V00012203'=>'1511329562','V00017431'=>'1511382463','V00017430'=>'1511382563','V00017429'=>'1511382663','V00012179'=>'1511391963','V00017529'=>'1511391363','V00017530'=>'1511391463','V00017531'=>'1511391563','V00017532'=>'1511391663','V00017540'=>'1511393263'
		,'V00017541'=>'1511393363','V00017729'=>'1511410663','V00017730'=>'1511410963','V00017735'=>'1511403563','V00014725'=>'1511415363','V00014724'=>'1511415863','V00017920'=>'1511417763','V00017820'=>'1511423063','V00017819'=>'1511423163','V00017818'=>'1511423963'
		,'V00017817'=>'1511424063','V00018041'=>'1511443863','V00018042'=>'1511443963','V00018043'=>'1511444063','V00018044'=>'1511444163','V00009453'=>'1510561216','V00018930'=>'1511527477','V00018929'=>'1511527377','V00000185'=>'1510528313','V00010178'=>'1510574522'
		,'V00012197'=>'1349819720','V00012176'=>'1510528113','V00009452'=>'1510561116','V00009454'=>'1510561316','V00009455'=>'1510561416','V00010175'=>'1510561616','V00010176'=>'1510561716','V00010177'=>'1510561816','V00014708'=>'1510607234','V00012177'=>'1510961915'
		,'V00012181'=>'1510962015','V00014717'=>'1510987015','V00014718'=>'1407151258','V00009456'=>'1510561516','V00010321'=>'1510803583','V00014715'=>'1510599634','V00017766'=>'1511411563','V00017774'=>'1511412163','V00017776'=>'1511412263','V00015176'=>'1511241340'
		,'V00015685'=>'1511242740','V00015172'=>'1511243440','V00015174'=>'1511297561','V00015687'=>'1511297661','V00015175'=>'1511301062','V00015691'=>'1511301162','V00015681'=>'1511301862','V00017726'=>'1511410363','V00018017'=>'1511430563','V00018019'=>'1511430663'
		,'V00018016'=>'1511435563','V00014734'=>'1407142501','V00012184'=>'1505150111','V00012166'=>'1510416815','V00008572'=>'1510961815','V00008573'=>'1511219120','V00010181'=>'1511049020','V00010179'=>'1511127613','V00009492'=>'1510804083','V00012173'=>'1510528013'
		,'V00014711'=>'1407134442','V00017762'=>'1510527713','V00015171'=>'1511289160','V00017423'=>'1511372863','V00017421'=>'1511372663','V00017424'=>'1511372963','V00017790'=>'1511411063','V00017791'=>'1511411163','V00018013'=>'1511429763','V00018012'=>'1511429863'
		,'V00018014'=>'1511429963','V00018015'=>'1511430063','V00018018'=>'1511430463','V00018215'=>'1511436163','V00018212'=>'1511443363','V00018213'=>'1511443463','V00018214'=>'1511454377','V00017763'=>'1510553016','V00017422'=>'1511372763'
		);

		## data load && loop
//		foreach($no_reg_items->result_array() as $itemcode){
		foreach($no_reg_items as $key => $itemcode){
			if($product_cnt>0 && ($product_cnt % 100)==0) sleep(2);
			// data generate
//			$virtual_item_code	= element('virtual_item_code',$itemcode);
			$virtual_item_code	= $key;

			// temp it_id set
			$it_id				= $this->master_item_tmp->getItidByVcode($virtual_item_code);
			if($it_id === false){
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, '', 'validate', 'No it_id');
				echo $virtual_item_code . " :: error0". PHP_EOL;
				continue;
			}
			$it_id	= $itemcode;

			$item_basic_data	= $this->master_item_tmp->setMasterItemInfo($it_id);

			$this->master_item_tmp->clear();

			if($item_basic_data===false){
				continue;
			}

			if(element('cate_info', $item_basic_data) === null){
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, '', 'validate', 'No category');
				echo $virtual_item_code . " :: error1". PHP_EOL;
				continue;
			}

			// Gmarket Product Official Info Load
			$product_official_info	= array();
			if(isset($this->product_official_info[element('group_id', element('cate_info', $item_basic_data))])){
				$product_official_info	= element(element('group_id', element('cate_info', $item_basic_data)), $this->product_official_info);
			} else {
				$ProductOfficialInfo = $this->openmarket_model->getGmarketProductOfficialInfo(array('group_no' => element('group_id', element('cate_info', $item_basic_data))));
				if(count($ProductOfficialInfo) > 0){
					foreach ($ProductOfficialInfo->result_array() as $official_info){
						array_push($product_official_info, $official_info);
					}
					$this->product_official_info[element('group_id', element('cate_info', $item_basic_data))] = $product_official_info;
				}
			}
			// Gmarket Product Official Info Load End

			// Maker, Brand Data Load
			$MakerNo	= 0;
			$BrandNo	= 0;
			$brand_info	= array();
			if(isset($this->product_brand_info[element('item_maker', $item_basic_data, '')])){
				$brand_info	= element(element('item_maker', $item_basic_data, ''), $this->product_brand_info);
			} else {
				$brand_info	= $this->openmarket_model->getBrandInfo(element('item_maker', $item_basic_data, ''));
				$this->product_brand_info[element('item_maker', $item_basic_data, '')]	= $brand_info;
			}
			if(count($brand_info) > 0){
				$MakerNo	= element('MakerNo', $brand_info);
				$BrandNo	= element('BrandNo', $brand_info);
			}
			// Maker, Brand Data Load End

			$item	= array( // Gmarket default value 2018-05-24 DEV_KKI
				'GmktItemNo'		=> element($key,$channel_item_arr),		'AddImage1'			=> ''
			,	'AddImage2'			=> '',		'GdAddHtml'			=> ''
			,	'GdPrmtHtml'		=> '',		'ModelName'			=> ''
			,	'MadeDate'			=> '',		'FreeGift'			=> ''
			,	'ItemWeight'		=> '',		'BuyUnitCount'		=> ''
			,	'MinBuyCount'		=> '',		'AppearedDate'		=> ''
			,	'AttributeCode'		=> '',		'ItemDescription'	=> ''
			,	'OrderLimitCount'	=> '',		'orderlimitperiod'	=> ''
			,	'FreeDelFeeType'	=> '1',		'IsNego'			=> 'true'
			,	'IsJaehuDiscount'	=> 'true',	'IsAdult'			=> 'false'
			,	'IsPack'			=> 'false',	'IsPriceCompare'	=> 'false'
			,	'IsOverseaTransGoods'	=> 'false'
			,	'Tax'				=> 'VAT',	'SetType'			=> 'Use'
			,	'OriginPlace'		=> '미국',	'Address'			=> 'Seller'
			,	'ItemKind'			=> 'Shipping'
			,	'ExpirationDate'	=> date('Y', strtotime('+2 years')) . '-12-31'
			// TODO check
			,	'BundleNo'			=> '0',		'GroupCode'			=> in_array($virtual_item_code,$this->free_product) ? '685850961': '685859440'
			,	'BrandNo'			=> $BrandNo,'RefundAddrNum'		=> '1994139'
			,	'MakerNo'			=> $MakerNo,'TransPolicyNo'		=> '321291'
			,	'Telephone'			=> '070-7093-9516'
			);

			$item['OutItemNo']		= $virtual_item_code;
			$item['CategoryCode']	= element('sales_channel_cate_id', element('cate_info', $item_basic_data));
			$item['ItemName']		= str_replace(element('replace_text',$this->channel_config), element('replace_value',$this->channel_config),  element('item_name', $item_basic_data));
			$item['ItemEngName']	= str_replace(element('replace_text',$this->channel_config), element('replace_value',$this->channel_config),  element('item_name', $item_basic_data));
			$item['GdHtml']			= str_replace('"','',str_replace(PHP_EOL,'',htmlspecialchars(element('desc_html', $item_basic_data))));
			$item['InventoryNo']	= $virtual_item_code;
			$item['DefaultImage']	= element('img_url', $item_basic_data);
			$item['LargeImage']		= element('img_url', $item_basic_data);

			$gmarket_attr_ids = array();
			if(element('gmarket_attr_id', element('cate_info', $item_basic_data),'') != '') array_push($gmarket_attr_ids, element('gmarket_attr_id', element('cate_info', $item_basic_data),''));
			if(element('gmarket_attr_id2', element('cate_info', $item_basic_data),'') != '') array_push($gmarket_attr_ids, element('gmarket_attr_id2', element('cate_info', $item_basic_data),''));

			// 상품등록
			$item_add_response = $this->callAddItem($item, $gmarket_attr_ids);
			if(element('result', $item_add_response) != 'Success') {
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, '', 'callAddItem', element('body', $item_add_response, ''));
				echo $virtual_item_code . " :: error2". PHP_EOL;
				continue;
			}

			// 상품번호 확인
			$item_add_response_body	= element('body', $item_add_response);
			$gmkt_item_no			= element('GmktItemNo', $item_add_response_body);
			echo $virtual_item_code ." :: additem END :: ".$gmkt_item_no. PHP_EOL;

////			$gmkt_item_no	= '1524879378';
//			// 공시정보 확인 및 등록
//			if (count($product_official_info) > 0) {
//				$add_official_info_response = $this->callAddOfficialInfo($gmkt_item_no, element('group_id', element('cate_info', $item_basic_data)), $product_official_info);
//				if(element('result', $add_official_info_response) != 'Success') {
//					$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddOfficialInfo', element('body', $add_official_info_response, ''));
//					echo "error3". PHP_EOL;
//					continue;
//				}
//				echo "callAddOfficialInfo END :: ";
//			}
//
//			// 통합안전인증 정보 등록
//			$add_intergrate_safe_cert_response = $this->callAddIntegrateSafeCert($gmkt_item_no);
//			if(element('result', $add_intergrate_safe_cert_response) != 'Success') {
//				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddIntegrateSafeCert', element('body', $add_intergrate_safe_cert_response, ''));
//				echo "error4". PHP_EOL;
//				continue;
//			}
//			echo "callAddIntegrateSafeCert END :: ";
//
//			// 반품 배송비 정책 등록
//			$add_item_return_fee_response = $this->callAddItemReturnFee($gmkt_item_no);
//			if(element('result', $add_item_return_fee_response) != 'Success') {
//				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddItemReturnFee', element('body', $add_item_return_fee_response, ''));
//				echo "error5". PHP_EOL;
//				continue;
//			}
//			echo "callAddItemReturnFee END :: ";
//
//			// 어린이 안전인증제품 확인
//			if(in_array( element('sales_channel_cate_id', element('cate_info', $item_basic_data)), element('chidren_safe_cert_category',$this->channel_config))){
//				$add_child_product_safe_cert_response = $this->callAddChildProductSafeCert($gmkt_item_no, 'NotCert');
//				if(element('result', $add_child_product_safe_cert_response) != 'Success') {
//					$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddChildProductSafeCert', element('body', $add_child_product_safe_cert_response, ''));
//					echo "error6". PHP_EOL;
//					continue;
//				}
//				echo "callAddChildProductSafeCert END :: ";
//			}
//
//			// 가격정보 등록
//			// TODO 가격책정 룰 확인
////			$price = ceil(element('item_price', $item_basic_data) * element('price_rule',$this->channel_config) / 1000) * 1000;
//			$price = element($virtual_item_code, $this->price_info);
//			$price_info = array(
//				'GmktItemNo'	=> $gmkt_item_no
//			,	'DisplayDate'	=> date('Y-m-d', strtotime('+1 year'))
//			,	'SellPrice'		=> $price
//			,	'StockQty'		=> '9999'
//			,	'InventoryNo'	=> $virtual_item_code
//			);
//			$add_price_respose = $this->callAddPrice($price_info);
//			if(element('Result', $add_price_respose) != 'Success') {
//				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddPrice', element('Comment', $add_price_respose, ''));
//				echo "error7". PHP_EOL;
//				continue;
//			}
//			echo "callAddPrice END :: ";
//
//			$discount_price	= '';
//			$discount_unit	= '';
//
////			if(element('use_price_discount',$this->channel_config)) {
////				$discount_price	= element('price_discount_rule_amount',$this->channel_config);
////				$discount_unit	= element('price_discount_rule_unit',$this->channel_config);
////				$premium_item_info = array(
////					'GmktItemNo'	=> $gmkt_item_no
////				,	'IsDiscount'	=> true
////				,	'DiscountPrice'	=> $discount_price
////				,	'DiscountUnit'	=> $discount_unit
////				,	'StartDate'		=> date('Y-m-d')
////				,	'EndDate'		=> element('price_discount_end_date',$this->channel_config)
////				);
////				$add_premium_item_response = $this->callAddPremiumItem($premium_item_info);
////				if (element('Result', $add_premium_item_response) != 'Success') {
////					$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddPremiumItem', element('Comment', $add_premium_item_response, ''));
////					echo "error8". PHP_EOL;
////					continue;
////				}
////				echo "callAddPremiumItem END :: ";
////			}
//
//			$this->openmarket_model->addOmsUploadProductInfo(array(
//				'channel_id'			=> $this->channel_id
//			,	'channel_item_code'		=> $gmkt_item_no
//			,	'virtual_item_id'		=> (int)str_replace('V','',$virtual_item_code)
//			,	'origin_price'			=> element('item_price', $item_basic_data)
//			,	'upload_price'			=> $price
//			,	'discount_price'		=> $discount_price
//			,	'discount_unit'			=> $discount_unit
//			,	'sell_status'			=> 'Y'
//			,	'stock_status'			=> 'Y'
//			,	'create_date'			=> date('Y-m-d H:i:s')
//			,	'need_update'			=> 'N'
//            ,   'worker_id'           => 'SYSTEM-API'
//			));
			echo "addOmsProductUploadResult END :: ".$gmkt_item_no. PHP_EOL;
			$product_cnt++;
		}

		echo $product_cnt."개 성공".PHP_EOL;
		## loop end
	}

	private function callAddItem($item, $attrbute_codes){

		include_once '/ssd/html/api_sdks/gmarket/autoload.php';

		$addItem = new \sdk\controller\AddItem();
		$addItem->setTicket($this->ticket);
		$addItem->setItemInfo($item);

		if(count($attrbute_codes) > 0) {
			foreach ($attrbute_codes as $attrbute_code) {
				if ($attrbute_code != '') $addItem->addAttribute($attrbute_code);
			}
		}

		return $addItem->getResponse();
	}

	private function saveUploadErrorInfo($channel_id, $virtual_item_id, $channel_product_id, $call_method, $error_message){

		$error_data = array(
			'channel_id'			=> $channel_id
		,	'virtual_item_id'		=> $virtual_item_id
		,	'channel_product_id'	=> $channel_product_id
		,	'call_method'			=> $call_method
		,	'error_message'			=> $error_message
		,	'create_date'			=> date('Y-m-d H:i:s')
		);

		$this->openmarket_model->addOmsProductUploadError($error_data);
	}

	private function callAddOfficialInfo($gmkt_item_no, $group_code, $official_info_arr){

		include_once '/ssd/html/api_sdks/gmarket/autoload.php';

		$AddOfficialInfo = new \sdk\controller\AddOfficialInfo();
		$AddOfficialInfo->setTicket($this->ticket);
		$AddOfficialInfo->setGmktItemNo($gmkt_item_no);
		$AddOfficialInfo->setGroupCode($group_code);

		foreach ($official_info_arr as $official_info) {
			$AddOfficialInfo->addSubInfoList(
				array(
					'Code' => element('noti_item_no', $official_info),
					'AddYn' => 'Y',
					'AddValue' => element('noti_value', $official_info)
				)
			);
		}

		return $AddOfficialInfo->getResponse();
	}

	private function callAddIntegrateSafeCert($gmkt_item_no){

		include_once '/ssd/html/api_sdks/gmarket/autoload.php';

		$AddIntegrateSafeCert = new \sdk\controller\AddIntergrateSafeCert();
		$AddIntegrateSafeCert->setTicket($this->ticket);
		$AddIntegrateSafeCert->setGmktItemNo($gmkt_item_no);

		return $AddIntegrateSafeCert->getResponse();
	}

	private function callAddItemReturnFee($gmkt_item_no){

		include_once '/ssd/html/api_sdks/gmarket/autoload.php';

		$AddItemReturnFee = new \sdk\controller\AddItemReturnFee();
		$AddItemReturnFee->setTicket($this->ticket);
		$AddItemReturnFee->setGmktItemNo($gmkt_item_no);
		$AddItemReturnFee->setReturnFeeType('Item');
		$AddItemReturnFee->setReturnChargeType('ByBuyer');
		$AddItemReturnFee->setReturnShippingFee(element('return_shipping_fee',$this->channel_config));
		$AddItemReturnFee->setExchangeShippingFee(element('return_shipping_fee',$this->channel_config));
		return $AddItemReturnFee->getResponse();
	}

	private function callAddPrice($price_info){

		include_once '/ssd/html/api_sdks/gmarket/autoload.php';

		$AddPrice = new \sdk\controller\AddPrice();
		$AddPrice->setTicket($this->ticket);
		$AddPrice->setProductPriceInfo($price_info);
		$response =  $AddPrice->getResponse();

		return $response;
	}

	private function callAddPremiumItem($premium_item_info){

		include_once '/ssd/html/api_sdks/gmarket/autoload.php';

		$AddPremiumItem = new \sdk\controller\AddPremiumItem();
		$AddPremiumItem->setTicket($this->ticket);
		$AddPremiumItem->setPremiumItemInfo($premium_item_info);
		$response = $AddPremiumItem->getResponse();

		return $response[0];
	}

	private function callAddChildProductSafeCert($gmkt_item_no, $certification_type){

		include_once '/ssd/html/api_sdks/gmarket/autoload.php';

		$AddChildProductSafeCert = new \sdk\controller\AddChildProductSafeCert();
		$AddChildProductSafeCert->setTicket($this->ticket);
		$AddChildProductSafeCert->setGmktItemNo($gmkt_item_no);
		$AddChildProductSafeCert->setCertificationType($certification_type);
		$response = $AddChildProductSafeCert->getResponse();

		return $response;
	}

//	public function itemPriceUpdate(){
//		$this->load->model('openmarket_model');
//
//		$this->output->enable_profiler(TRUE);
//
//		$this->ticket = '40E3F9EDACC974592C2E85924076246FECA1499A458C4B2D59CF318791413DB4AA6CCEE9E5A7E53795EE1A4329109A9DB44CF5DEDC1F1DE5BE0D6682512B44531294AABEB4587A267879E2CC0602431C69FC30E41CBC04A5B36CFB94653FF301';
//
//		$datas	= $this->openmarket_model->getGmarketItem();
//		$n	= 0;
//		foreach($datas->result_array() as $item){
//
//			$price_info = array(
//				'GmktItemNo'	=> $item['channel_item_code']
//			,	'DisplayDate'	=> date('Y-m-d', strtotime('+1 year'))
//			,	'SellPrice'		=> $item['upload_price']
//			,	'StockQty'		=> '9999'
//			,	'InventoryNo'	=> 'V'.str_pad($item['virtual_item_id'], 8, "0", STR_PAD_LEFT)
//			);
//
//			$add_price_respose = $this->callAddPrice($price_info);
//			if(element('Result', $add_price_respose) != 'Success') {
//				echo "error7 :: ".element('GmktItemNo',$price_info). PHP_EOL;
//				continue;
//			}
//			echo $n."  :: callAddPrice END :: ".element('GmktItemNo',$price_info). PHP_EOL;
//			$n++;
//		}
//		echo "END";
//	}

//	public function getTransPolicyList(){
//
//		$this->setChannel('g', 'fastople');
//
//		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
//		$GetTransPolicyList = new \sdk\controller\GetTransPolicyList();
//		$GetTransPolicyList->setTicket($this->ticket);
//		$GetTransPolicyList->setTransPolicyNo('0');
//		$response = $GetTransPolicyList->getResponse();
//		var_dump($response);
//	}
//	public function requestAddressBoook(){
//
//		$this->setChannel('g', 'fastople');
//
//		include_once '/ssd/html/api_sdks/gmarket/autoload.php';
//		$RequestAddressBook = new \sdk\controller\RequestAddressBook();
//		$RequestAddressBook->setTicket($this->ticket);
//		$RequestAddressBook->setBundleNumber('0');
//		$response = $RequestAddressBook->getResponse();
//		var_dump($response);
//	}


function uploadAddG9Item(){

    echo 'start'.date('Y-m-d H:i:s').PHP_EOL;

    //account API 인증키 (현재 네이코 G마켓)
    $this->ticket = '8DEC453C7AD7275CBA3A970B5DF1EBE77FA5BD3B54C5FE1F75006453C5C7134B806D7950D81635B49874CD421CC03633C40143736089439867D86662D835C44E151AF05F30575203ABC72A823472DA5DA7815FF4E18C04C0DE34EE514B0BC9E1A0D9633D0DA4F12612D3A4ABF7AF10F9';

    //복제할 G마켓 상번
    $gmarketItem_arr = array('1671708295'
    );

        $item_info = array();


	    foreach ($gmarketItem_arr  as $item_code){

/*            //복제하려는 상품이 oms DB에 있는지 확인하기
            $channel_item_info = $this->channel_item_info_model->getItemInfo(array("channel_item_code"=>$item_code));


            if(element('channel_item_code',$channel_item_info,false)==""){
                echo $item_code." :: OMS DB에 해당 상번이 존재하지 않습니다.".PHP_EOL;
                continue;
            }*/


            //API 실행
            $item_info = array(
                'GmktItemNo'	=> $item_code
            ,	'SellManageYn'	=> 'Y'
            ,	'CostManageYn'		=> 'Y'
            ,	'ItemManageYn'		=> 'Y'
            );

            $add_price_respose = $this->callAdditemg9($item_info);

            echo PHP_EOL;


            echo $item_code.'||||||||||'.element('GmktItemNo',$add_price_respose,false);
            echo PHP_EOL;


            if(element('GmktItemNo',$add_price_respose,false)!="") {
/*                // API로 복제된 G9상품 OMS DB에 매핑하기
                $channel_item_insert_arr = array(
                    'channel_id' => element('channel_id', $channel_item_info, 0),
                    'channel_item_code' => element('GmktItemNo', $add_price_respose, false),
                    'virtual_item_id' => element('virtual_item_id', $channel_item_info, 0),
                    'upload_price' => element('upload_price', $channel_item_info, 0),
                    "origin_price" => element('origin_price', $channel_item_info, 0),
                    "discount_unit" => element('discount_unit', $channel_item_info, 0),
                    "discount_price" => element('discount_price', $channel_item_info, 0),
                    'create_date' => "now()",
                    "need_update" => "N",
                    "sell_status" => "Y",
                    "stock_status" => "Y",
                    "worker_id" => "SYSTEM-API"
                );


                $insert_key = $this->channel_item_info_model->insertItem($channel_item_insert_arr);

                if (!$insert_key) {
                    echo element('GmktItemNo', $add_price_respose, false) . " :: OMS DB에 제대로 매핑되지 않았습니다." . PHP_EOL;
                }*/
            }else{
                echo $item_code . " :: API 실패" . PHP_EOL;

            }
        }




    echo 'end'.date('Y-m-d H:i:s').PHP_EOL;


}

    private function callAdditemg9($item_info)
    {
        include_once '/ssd/html/api_sdks/gmarket/autoload.php';

        $AddPrice = new \sdk\controller\AddG9Item();
        $AddPrice->setTicket($this->ticket);
        $AddPrice->setCopyProductOption($item_info);

        $response =  $AddPrice->getResponse();

        return $response;
    }
    private function callAddPriceg9($item_info)
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

}
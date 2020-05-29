<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-11-22
 * File: Oms_product.php
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Oms_product  extends CI_Controller
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
	(
		'V00020428', 'V00020433', 'V00020569', 'V00020531', 'V00020611', 'V00020620',
        'V00020563', 'V00020536', 'V00020534', 'V00020610', 'V00020424', 'V00020535',
        'V00020573', 'V00020602', 'V00020653', 'V00020488', 'V00020582', 'V00020566',
        'V00020444', 'V00020442', 'V00020472', 'V00020619', 'V00020464', 'V00020499',
        'V00020606', 'V00020437', 'V00020509', 'V00020439', 'V00020482', 'V00020565',
        'V00020506', 'V00020657', 'V00020470', 'V00020542', 'V00020621', 'V00020604',
        'V00020623', 'V00020544', 'V00020434', 'V00020613', 'V00020589', 'V00020483',
        'V00020489', 'V00020585', 'V00020651', 'V00020612', 'V00020603',
    );

	private $price_info	= array
	(
        'V00020428'=>127600,
        'V00020433'=>71600,
        'V00020569'=>54500,
        'V00020531'=>88900,
        'V00020611'=>86400,
        'V00020620'=>52200,
        'V00020563'=>60800,
        'V00020536'=>182200,
        'V00020534'=>23200,
        'V00020610'=>80700,
        'V00020424'=>117400,
        'V00020535'=>56400,
        'V00020573'=>41000,
        'V00020602'=>72200,
        'V00020653'=>30900,
        'V00020488'=>106400,
        'V00020582'=>106000,
        'V00020566'=>107100,
        'V00020444'=>93100,
        'V00020442'=>79200,
        'V00020472'=>102200,
        'V00020619'=>122900,
        'V00020464'=>162800,
        'V00020499'=>129200,
        'V00020606'=>87200,
        'V00020437'=>129200,
        'V00020509'=>87400,
        'V00020439'=>117200,
        'V00020482'=>40100,
        'V00020565'=>77300,
        'V00020506'=>44200,
        'V00020657'=>12900,
        'V00020470'=>106000,
        'V00020542'=>28600,
        'V00020621'=>73100,
        'V00020604'=>73100,
        'V00020623'=>73100,
        'V00020544'=>87000,
        'V00020434'=>116800,
        'V00020613'=>32200,
        'V00020589'=>91000,
        'V00020483'=>33300,
        'V00020489'=>29500,
        'V00020585'=>46600,
        'V00020651'=>25900,
        'V00020612'=>19400,
        'V00020603'=>19400
    );
	
	function __construct()
	{
		parent::__construct();
		
		$this->load->model('product_temp/openmarket_model');
		$this->load->model('product_temp/customs_clearance_model');
		// $this->load->library('supplement_facts');
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
	    echo 'dd';
		if($channel=='' || $account==''){
			echo 'channel & account required';
			return false;
		}
		
		$this->setChannel($channel, $account);

		ini_set('max_execution_time',0);
		ini_set('memory_limit',-1);

		// 상품수정시 사용 예정?
		$channel_item_arr	= array(
			/*'V00000185'=>'1621309606','V00008572'=>'1621337744','V00008573'=>'1621337789','V00009452'=>'1621309862','V00009453'=>'1621309421','V00009454'=>'1621309913','V00009455'=>'1621309964','V00009456'=>'1621310553','V00009492'=>'1621337919','V00010175'=>'1621310027'
			,'V00010176'=>'1621310093','V00010177'=>'1621310165','V00010178'=>'1621309673','V00010179'=>'1621337876','V00010181'=>'1621337833','V00010321'=>'1621310607','V00012156'=>'1621306176','V00012166'=>'1621337699','V00012173'=>'1621337973','V00012175'=>'1621307464'
			,'V00012176'=>'1621309797','V00012177'=>'1621310296','V00012179'=>'1621308156','V00012181'=>'1621310350','V00012182'=>'1621307729','V00012183'=>'1621307787','V00012184'=>'1621337634','V00012192'=>'1621307672','V00012193'=>'1621307624','V00012194'=>'1621307854'
			,'V00012197'=>'1621309736','V00012199'=>'1621307903','V00012203'=>'1621307957','V00014708'=>'1621310223','V00014711'=>'1621338028','V00014715'=>'1621310657','V00014717'=>'1621310423','V00014718'=>'1621310496','V00014724'=>'1621308838','V00014725'=>'1621308770'
			,'V00014734'=>'1621311970','V00015171'=>'1621338126','V00015172'=>'1621311057','V00015174'=>'1621311122','V00015175'=>'1621311271','V00015176'=>'1621310935','V00015681'=>'1621311458','V00015685'=>'1621310996','V00015687'=>'1621311201','V00015691'=>'1621311372'
			,'V00015692'=>'1621307515','V00015694'=>'1621307564','V00017421'=>'1621338220','V00017422'=>'1621339032','V00017423'=>'1621338172','V00017424'=>'1621338268','V00017429'=>'1621308117','V00017430'=>'1621308062','V00017431'=>'1621308006','V00017529'=>'1621308215'
			,'V00017530'=>'1621308272','V00017531'=>'1621308334','V00017532'=>'1621308413','V00017540'=>'1621308461','V00017541'=>'1621308525','V00017726'=>'1621311564','V00017729'=>'1621308578','V00017730'=>'1621308636','V00017735'=>'1621308712','V00017762'=>'1621338084'
			,'V00017763'=>'1621338973','V00017766'=>'1621310734','V00017774'=>'1621310809','V00017776'=>'1621310878','V00017790'=>'1621338318','V00017791'=>'1621338369','V00017817'=>'1621309147','V00017818'=>'1621309081','V00017819'=>'1621309018','V00017820'=>'1621308961'
			,'V00017920'=>'1621308893','V00018012'=>'1621338472','V00018013'=>'1621338409','V00018014'=>'1621338534','V00018015'=>'1621338595','V00018016'=>'1621311869','V00018017'=>'1621311660','V00018018'=>'1621338675','V00018019'=>'1621311772','V00018041'=>'1621309203'
			,'V00018042'=>'1621309258','V00018043'=>'1621309305','V00018044'=>'1621309364','V00018212'=>'1621338798','V00018213'=>'1621338851','V00018214'=>'1621338922','V00018215'=>'1621338730','V00018929'=>'1621309552','V00018930'=>'1621309487'*/
		);

		/*
		$filter	= array(
			'channel_id'	=> 1
		,	'test'			=> 1
		,	'vcode_in'		=> array('V00015227')
		);
		$filter['no_upc_in'] = array();
		$ban_upc_datas = $this->customs_clearance_model->getBanProductUPC();
		foreach ($ban_upc_datas->result_array() as $ban_upc){
			array_push($filter['no_upc_in'], rtrim(element('upc', $ban_upc)));
		}
		$filter['no_it_maker_in']	= $this->config->item('except_maker');
		$filter['it_use']			= '1';
		$filter['it_discontinued']	= '0';
		$filter['no_it_id_in']		= $this->config->item('except_item_code');
		$filter['bigger_it_id']		= $this->config->item('bigger_it_id');
		*/
		
		$this->channel_id	= $this->master_item_tmp->setChannelId($channel, $account, $this->channel_config);
		
		//$no_reg_items	= $this->master_item_tmp->getNoRegItems($filter);
		$product_cnt	= 0;
		
		// 등록대상 상품 리스트 : @new_item
		$no_reg_items	= array(
//            'V00000612' => '1510542616'
//            'V00002874' => '1511792277', 'V00005346' => '1511217919', 'V00006020' => '1511586677', 'V00006052' => '1511331362', 'V00006056' => '1511313162', 'V00006652' => '1507165557', 'V00006797' => '1505198241', 'V00006849' => '1510575522', 'V00006903' => '1511583777', 'V00007062' => '1511449974', 'V00008432' => '1510683923',
//            'V00009861' => '1511113913', 'V00009863' => '1511114113', 'V00009864' => '1511114213', 'V00009870' => '1511112813', 'V00010098' => '1511142115', 'V00010101' => '1510913415', 'V00010103' => '1510913615', 'V00010110' => '1510914515', 'V00010113' => '1511151815', 'V00010117' => '1511142015', 'V00010118' => '1510914615', 'V00010134' => '1511117313', 'V00010136' => '1511117213', 'V00010967' => '1511230339', 'V00011000' => '1510410915', 'V00011020' => '1511796877', 'V00011939' => '1511796977', 'V00012382' => '1503112951', 'V00013577' => '1511072396', 'V00013958' => '1367306630', 'V00014463' => '1511076112', 'V00014512' => '1511255847', 'V00016173' => '1382558458', 'V00016731' => '1511178916', 'V00017084' => '1511324462', 'V00017135' => '1511334262', 'V00017535' => '1511392763',
            'V00017790' => '1511411063', 'V00018349' => '1511324462', 'V00018350' => '1511324462', 'V00018351' => '1511324462', 'V00018352' => '1511324462', 'V00018353' => '1511324462', 'V00018354' => '1511324462', 'V00018355' => '1511324462', 'V00018356' => '1511324462', 'V00018357' => '1511324462', 'V00018358' => '1511324462', 'V00018359' => '1511324462', 'V00018360' => '1511324462', 'V00018636' => '1511497777', 'V00018756' => '1511518977', 'V00019400' => '1511577577', 'V00019464' => '1511324462', 'V00019465' => '1511324462', 'V00019466' => '1511324462', 'V00019467' => '1511324462', 'V00019468' => '1511324462', 'V00019633' => '1511597477', 'V00019690' => '1511603977', 'V00020813' => '1511680977', 'V00020817' => '1511687677', 'V00020819' => '1511687477', 'V00021173' => '1511724477', 'V00021642' => '1511782377', 'V00021647' => '1511324462', 'V00021648' => '1511324462', 'V00021649' => '1511324462', 'V00021650' => '1511324462', 'V00021651' => '1511324462', 'V00021652' => '1511324462', 'V00021661' => '1511786477', 'V00021662' => '1511786377', 'V00021663' => '1511786277', 'V00021672' => '1511788377', 'V00021675' => '1511783877', 'V00021680' => '1511780977', 'V00021681' => '1511781277', 'V00021682' => '1511780877', 'V00021683' => '1511778477', 'V00021684' => '1511814877', 'V00021685' => '1511794877', 'V00021686' => '1511777277', 'V00021687' => '1511787677', 'V00021700' => '1511782177', 'V00021701' => '1511781877', 'V00021702' => '1511787277', 'V00021703' => '1511787577', 'V00021704' => '1511787377', 'V00021707' => '1511754877', 'V00021709' => '1511765477', 'V00021710' => '1511765677', 'V00021711' => '1511765277', 'V00021712' => '1511765077', 'V00021714' => '1511783477', 'V00021729' => '1511787777', 'V00021730' => '1511786677', 'V00021732' => '1511778577', 'V00021735' => '1511786577', 'V00021738' => '1511788177', 'V00021760' => '1511788777', 'V00021772' => '1511498777', 'V00021786' => '1511788477', 'V00021787' => '1511788677', 'V00021799' => '1511791277', 'V00021801' => '1511791177', 'V00021803' => '1511791077', 'V00021808' => '1511791577', 'V00021862' => '1511806777', 'V00021863' => '1511807477', 'V00021864' => '1511807177', 'V00021865' => '1511807377', 'V00021866' => '1511806977', 'V00021869' => '1511808277', 'V00021905' => '1511815177', 'V00021906' => '1511815277', 'V00021907' => '1511814977', 'V00021908' => '1511815077', 'V00021909' => '1511802977', 'V00021910' => '1511803077', 'V00021911' => '1511803177', 'V00021912' => '1511803277', 'V00021913' => '1511803377', 'V00021914' => '1511803477', 'V00021915' => '1511803577', 'V00021916' => '1511803677', 'V00021917' => '1511803777', 'V00021918' => '1511803877', 'V00021919' => '1511803977', 'V00021920' => '1511804077', 'V00021921' => '1511804177', 'V00021922' => '1511804277', 'V00021923' => '1511804377', 'V00021924' => '1511804477', 'V00021925' => '1511804577', 'V00021926' => '1511804677', 'V00021927' => '1511804877', 'V00021928' => '1511805077', 'V00021929' => '1511805177', 'V00021930' => '1511805277', 'V00021931' => '1511805377', 'V00021932' => '1511805477', 'V00021933' => '1511805577', 'V00021934' => '1511805677', 'V00021935' => '1511805777', 'V00021936' => '1511805877', 'V00021937' => '1511806677', 'V00021938' => '1511806877', 'V00021939' => '1511807077', 'V00021940' => '1511814377', 'V00021941' => '1511814477', 'V00021942' => '1511814577', 'V00021943' => '1511814677', 'V00021947' => '1511801677', 'V00021949' => '1511788877', 'V00021950' => '1511789077', 'V00021951' => '1511789177', 'V00021952' => '1511789277', 'V00021953' => '1511792177', 'V00021954' => '1511792777', 'V00021955' => '1511792877', 'V00021956' => '1511792977', 'V00021957' => '1511793077', 'V00021958' => '1511794977', 'V00021961' => '1511795277', 'V00021962' => '1511795377', 'V00021963' => '1511795677', 'V00021964' => '1511795777', 'V00021965' => '1511797077', 'V00021966' => '1511797177', 'V00021967' => '1511797277', 'V00021968' => '1511797377', 'V00021969' => '1511797477', 'V00021970' => '1511797577', 'V00021971' => '1511797677', 'V00021972' => '1511797777', 'V00021973' => '1511797877', 'V00021974' => '1511797977', 'V00021975' => '1511798077', 'V00021976' => '1511798177', 'V00021977' => '1511798277', 'V00021978' => '1511798377', 'V00021979' => '1511798477', 'V00021980' => '1511798577', 'V00021981' => '1511798677', 'V00021982' => '1511798777', 'V00021983' => '1511798877', 'V00021984' => '1511799277', 'V00021985' => '1511799377', 'V00021986' => '1511799577', 'V00021987' => '1511799877', 'V00021988' => '1511799977', 'V00021989' => '1511800177', 'V00021990' => '1511800477', 'V00021991' => '1511800777', 'V00021992' => '1511800977', 'V00021993' => '1511801077', 'V00021994' => '1511801177', 'V00021995' => '1511801277', 'V00021996' => '1511801377', 'V00021997' => '1511801477', 'V00021998' => '1511801577', 'V00021999' => '1511801777', 'V00022000' => '1511801877', 'V00022001' => '1511801977', 'V00022002' => '1511802077', 'V00022003' => '1511802177', 'V00022004' => '1511802477', 'V00022005' => '1511802577', 'V00022006' => '1511802677', 'V00022007' => '1511802777', 'V00022008' => '1511804777', 'V00022009' => '1511804977', 'V00022010' => '1511807277', 'V00022011' => '1511808377', 'V00022012' => '1511808477', 'V00022013' => '1511808977', 'V00022014' => '1511809577', 'V00022015' => '1511811877', 'V00022016' => '1511812377', 'V00022017' => '1511812577', 'V00022018' => '1511812677', 'V00022019' => '1511812777', 'V00022020' => '1511812877', 'V00022021' => '1511812977', 'V00022022' => '1511813077', 'V00022023' => '1511813177', 'V00022024' => '1511813277', 'V00022025' => '1511813377', 'V00022026' => '1511813877', 'V00022027' => '1511814277', 'V00022028' => '1511815977', 'V00022029' => '1511816177', 'V00022030' => '1511816277', 'V00022031' => '1511816377', 'V00022032' => '1511816477'
        );
		
		## data load && loop
		//foreach($no_reg_items->result_array() as $itemcode){
		foreach($no_reg_items as $key => $itemcode){
			if($product_cnt>0 && ($product_cnt % 100)==0) sleep(2);
			// data generate
			// $virtual_item_code	= element('virtual_item_code',$itemcode);
			$virtual_item_code	= $key;
			
			// temp it_id set
			$it_id = $this->master_item_tmp->getItidByVcode($virtual_item_code);
			if ( $it_id === false ) {
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, '', 'validate', 'No it_id');
				echo $virtual_item_code . " :: error0". PHP_EOL;
				continue;
			}

			$it_id = $itemcode;// 뭐하는 짓인지......
			$item_basic_data = $this->master_item_tmp->setMasterItemInfo($it_id);
			$this->master_item_tmp->clear();
			if ( $item_basic_data===false ) {
				continue;
			}
		
			if(element('cate_info', $item_basic_data) === null){
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, '', 'validate', 'No category');
				echo $virtual_item_code . " :: error1". PHP_EOL;
				continue;
			}

			// Gmarket Product Official Info Load
			$product_official_info	= array();
			if ( isset($this->product_official_info[element('group_id', element('cate_info', $item_basic_data))]) )
			{
				$product_official_info	= element(element('group_id', element('cate_info', $item_basic_data)), $this->product_official_info);
			}
			else
			{
				$ProductOfficialInfo = $this->openmarket_model->getGmarketProductOfficialInfo(array('group_no' => element('group_id', element('cate_info', $item_basic_data))));
				if ( count($ProductOfficialInfo) > 0 )
				{
					foreach ($ProductOfficialInfo->result_array() as $official_info)
					{
						array_push($product_official_info, $official_info);
					}

					$this->product_official_info[element('group_id', element('cate_info', $item_basic_data))] = $product_official_info;
				}
			}
			// Gmarket Product Official Info Load End
            $item_basic_data = str_replace('복용','섭취',$item_basic_data);


			// Maker, Brand Data Load
			$MakerNo	= 0;
			$BrandNo	= 0;
			$brand_info	= array();
			if ( isset($this->product_brand_info[element('item_maker', $item_basic_data, '')]) )
			{
				$brand_info	= element(element('item_maker', $item_basic_data, ''), $this->product_brand_info);
			}
			else
			{
				$brand_info	= $this->openmarket_model->getBrandInfo(element('item_maker', $item_basic_data, ''));
				$this->product_brand_info[element('item_maker', $item_basic_data, '')]	= $brand_info;
			}

			if ( count($brand_info) > 0 )
			{
				$MakerNo	= element('MakerNo', $brand_info);
				$BrandNo	= element('BrandNo', $brand_info);
			}
			// Maker, Brand Data Load End

			$item	= array( // Gmarket default value 2018-05-24 DEV_KKI
				'GmktItemNo'		=> element($key,$channel_item_arr,''),		'AddImage1'			=> ''
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
			,	'MakerNo'			=> $MakerNo,'TransPolicyNo'		=> '429810' //발송정책
			,	'Telephone'			=> '070-7093-9516'
            ,   'ExpirationDate'    => '2078-12-31'								// @new_item
			);
			
			$item['OutItemNo']		= $virtual_item_code;
			$item['CategoryCode']	= element('sales_channel_cate_id', element('cate_info', $item_basic_data));
            $item['ItemName']		= str_replace(element('replace_text',$this->channel_config), element('replace_value',$this->channel_config),  element('item_name', $item_basic_data));
            $item['ItemEngName']	= str_replace(element('replace_text',$this->channel_config), element('replace_value',$this->channel_config),  element('item_name', $item_basic_data));
            $item['GdHtml']			= str_replace('"','',str_replace(PHP_EOL,'',htmlspecialchars(element('desc_html', $item_basic_data))));
            $item['InventoryNo']	= $virtual_item_code;
            $file_headers = @get_headers(element('img_url', $item_basic_data));

            log_message("error", "ksj: " . $file_headers[0]);
            $img_url = ($file_headers[0] == 'HTTP/1.0 404 Not Found') ? 'http://www.ople.com/mall5/shop/img/no_image_400.gif' : element('img_url', $item_basic_data);

			$item['DefaultImage']	= $img_url;
			$item['LargeImage']		= $img_url;

			$gmarket_attr_ids = array();
			if(element('gmarket_attr_id', element('cate_info', $item_basic_data),'') != '') array_push($gmarket_attr_ids, element('gmarket_attr_id', element('cate_info', $item_basic_data),''));
			if(element('gmarket_attr_id2', element('cate_info', $item_basic_data),'') != '') array_push($gmarket_attr_ids, element('gmarket_attr_id2', element('cate_info', $item_basic_data),''));
			
			// 상품등록
			$item_add_response = $this->callAddItem($item, $gmarket_attr_ids);
			if(element('result', $item_add_response) != 'Success') {
			    echo "이믿지::". $img_url.PHP_EOL;
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, '', 'callAddItem', element('body', $item_add_response, ''));
				echo $virtual_item_code . " :: error2". PHP_EOL;
				continue;
			}
			
			// 상품번호 확인
			$item_add_response_body	= element('body', $item_add_response);
			$gmkt_item_no			= element('GmktItemNo', $item_add_response_body);
			echo $virtual_item_code ." :: additem END :: ".$gmkt_item_no. PHP_EOL;
			
			// $gmkt_item_no	= '1524879378';
			// 공시정보 확인 및 등록
			if (count($product_official_info) > 0) {
				$add_official_info_response = $this->callAddOfficialInfo($gmkt_item_no, element('group_id', element('cate_info', $item_basic_data)), $product_official_info);
				if(element('result', $add_official_info_response) != 'Success') {
					$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddOfficialInfo', element('body', $add_official_info_response, ''));
					echo "error3". PHP_EOL;
					continue;
				}
				echo "callAddOfficialInfo END :: ";
			}

			// 통합안전인증 정보 등록
			$add_intergrate_safe_cert_response = $this->callAddIntegrateSafeCert($gmkt_item_no);
			if(element('result', $add_intergrate_safe_cert_response) != 'Success') {
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddIntegrateSafeCert', element('body', $add_intergrate_safe_cert_response, ''));
				echo "error4". PHP_EOL;
				continue;
			}
			echo "callAddIntegrateSafeCert END :: ";

			// 반품 배송비 정책 등록
			$add_item_return_fee_response = $this->callAddItemReturnFee($gmkt_item_no);
			if(element('result', $add_item_return_fee_response) != 'Success') {
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddItemReturnFee', element('body', $add_item_return_fee_response, ''));
				echo "error5". PHP_EOL;
				continue;
			}
			echo "callAddItemReturnFee END :: ";

			// 어린이 안전인증제품 확인
			if(in_array( element('sales_channel_cate_id', element('cate_info', $item_basic_data)), element('chidren_safe_cert_category',$this->channel_config))){
				$add_child_product_safe_cert_response = $this->callAddChildProductSafeCert($gmkt_item_no, 'NotCert');
				if(element('result', $add_child_product_safe_cert_response) != 'Success') {
					$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddChildProductSafeCert', element('body', $add_child_product_safe_cert_response, ''));
					echo "error6". PHP_EOL;
					continue;
				}
				echo "callAddChildProductSafeCert END :: ";
			}

			// 가격정보 등록
			// TODO 가격책정 룰 확인
			// $price = ceil(element('item_price', $item_basic_data) * element('price_rule',$this->channel_config) / 1000) * 1000;
			// $price = element($virtual_item_code, $this->price_info);
            $price = ceil(element('item_price', $item_basic_data) * element('price_rule',$this->channel_config) / 100) * 100;		// @new_item

			$price_info = array(
				'GmktItemNo'	=> $gmkt_item_no
			,	'DisplayDate'	=> date('Y-m-d', strtotime('+1 year'))
			,	'SellPrice'		=> $price
			,	'StockQty'		=> '9999'			// @new_item
			,	'InventoryNo'	=> $virtual_item_code
			);
			$add_price_respose = $this->callAddPrice($price_info);
			if(element('Result', $add_price_respose) != 'Success') {
				$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddPrice', element('Comment', $add_price_respose, ''));
				echo "error7". PHP_EOL;
				continue;
			}
			echo "callAddPrice END :: ";

			$discount_price	= '';
			$discount_unit	= '';

			if(element('use_price_discount',$this->channel_config)) {
				$discount_price	= element('price_discount_rule_amount',$this->channel_config);
				$discount_unit	= element('price_discount_rule_unit',$this->channel_config);
				$premium_item_info = array(
					'GmktItemNo'	=> $gmkt_item_no
				,	'IsDiscount'	=> true
				,	'DiscountPrice'	=> $discount_price
				,	'DiscountUnit'	=> $discount_unit
				,	'StartDate'		=> date('Y-m-d')
				,	'EndDate'		=> element('price_discount_end_date',$this->channel_config)
				);
				$add_premium_item_response = $this->callAddPremiumItem($premium_item_info);
				if (element('Result', $add_premium_item_response) != 'Success') {
					$this->saveUploadErrorInfo($this->channel_id, $virtual_item_code, $gmkt_item_no, 'callAddPremiumItem', element('Comment', $add_premium_item_response, ''));
					echo "error8". PHP_EOL;
					continue;
				}
				echo "callAddPremiumItem END :: ";
			}

			$this->openmarket_model->addOmsUploadProductInfo(array(
				'channel_id'			=> $this->channel_id
			,	'channel_item_code'		=> $gmkt_item_no
			,	'virtual_item_id'		=> (int)str_replace('V','',$virtual_item_code)
			,	'origin_price'			=> element('item_price', $item_basic_data)
			,	'upload_price'			=> $price
			,	'discount_price'		=> $discount_price
			,	'discount_unit'			=> $discount_unit
			,	'sell_status'			=> 'Y'
			,	'stock_status'			=> 'Y'		// @new_item
			,	'create_date'			=> date('Y-m-d H:i:s')
			,	'need_update'			=> 'N'
            ,   'worker_id'				=> 'SYSTEM-API'
			));
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
}
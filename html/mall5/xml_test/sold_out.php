<?php
if(!$_GET['mode']){
	exit;
}

ini_set('display_errors','On');
error_reporting(E_ALL ^ E_NOTICE);
/*
include "../dbconfig.php";


mysql_connect($mysql_host,$mysql_user,$mysql_password);
mysql_select_db($mysql_db);
*/

include_once "_common.php";


# upc 체크
if($_GET['mode'] == 'upc_chk'){
	$item_chk = mysql_fetch_assoc(mysql_query("select it_id,it_stock_qty,it_name from yc4_item where SKU = '".$_GET['upc']."'"));
	if($item_chk['it_name']){
		$item_chk['it_name'] = get_item_name($item_chk['it_name']);
	}


	$xml = new SimpleXMLElement('<xml/>');
	if(!$item_chk['it_id']){	// 해당 upc 검색 실패시
		$err = 1;
	}elseif($item_chk['it_stock_qty']<1){ // 이미 품절인 제품
		$err = 2;
	}else{
		$err = 0;
		$xml->addChild('it_id',$item_chk['it_id']);
		$xml->addChild('it_name',$item_chk['it_name']);

	}
	$xml->addAttribute('err', $err);




	Header('Content-type: text/xml');
	print($xml->asXML());
	exit;

}

# 스캔제품 입고처리
if($_GET['mode'] == 'item_sold_in'){
	$updateQ = "update yc4_item set it_stock_qty = '999999' where it_id = '".$_GET['it_id']."'";


	$hour = (int)(date("H"));

	# SMS 발송카운트 보여주기 #
	$sms_cnt = mysql_num_rows(mysql_query("select ts_id from yc4_add_item_sms where it_id = '".$_GET['it_id']."' and ts_send = 0"));

	if($hour < 9 || $hour > 21){ // 9~21시 사이가 아닐경우 예약발송
		exit;
	}else{
		it_sms_send($_GET['it_id'],'999999'); // sms 발송

	}

	exit;
}


# 입고 후 sms 미발송 리스트 sms 발송 #
if($_GET['mode'] == 'item_sms'){
	include_once $g4['path']."/lib/icode.sms.lib.php";
	$hour = (int)(date("H"));

	if($hour < 9 || $hour > 21){  // 9~21 사이에만 발송
		exit;
	}

	# 과부화 방지를 위해 랜덤 5개 상품에 대해서만 발송
	$sms_send_qry = sql_query("
		select
			a.it_id,a.it_stock_qty
		from
			yc4_item a
			left outer join
			yc4_add_item_sms b on a.it_id = b.it_id
		where
			a.it_stock_qty > 0
			and
			b.ts_send = 0
		group by a.it_id
		order by rand()
		limit 5
	");




	while($sms_send = sql_fetch_array($sms_send_qry)){

		$it_stock_qty = get_it_stock_qty($sms_send['it_id']);

		if($it_stock_qty > 0) {
			it_sms_send($sms_send['it_id'],$sms_send['it_stock_qty']); // sms 발송
		}

	}




}
?>
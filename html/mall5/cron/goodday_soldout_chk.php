<?php
/*
	굿데이 품절 SMS 알림
*/

include "db.config.php";
$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);

$db_selected = mysql_select_db('okflex5');

$sql = mysql_query("
	SELECT 
		a.uid,a.it_id,a.order_cnt,b.it_name
	FROM   
		yc4_oneday_sale_item as a,
		yc4_item as b
	WHERE  
			a.it_id = b.it_id
		AND ".date('Ymd')." BETWEEN a.st_dt AND a.en_dt 
		AND a.real_qty <= a.order_cnt 
		AND a.sms_send is null
");
$sms_contents = array();
while($row = mysql_fetch_assoc($sql)){
	$update_sql = "update yc4_oneday_sale_item set sms_send = 1 where uid = '".$row['uid']."'";
	if(mysql_query($update_sql)){
		$sms_contents[] = "굿데이 품절".PHP_EOL.'상품코드:'.$row['it_id'].PHP_EOL.$row['order_cnt']."개 판매완료";
	}

}

if(count($sms_contents)>0){
	include "./sms.lib.php";
	$sql = mysql_query("select * from yc4_default");
	$default = mysql_fetch_assoc($sql);
	$SMS = new SMS; // SMS 연결
	$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);

	$send_number = preg_replace("/[^0-9]/", "", $default['de_sms_hp']); // 발신자번호

	$hp_arr = array(
		'01076745505',
		'01063018356',
		'01024141615',
		'01028611799'
	);

	foreach($hp_arr as $receive_number){
		foreach($sms_contents as $val){
			//$val = iconv('utf8','euckr',$val);
			$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($val), "");
		}
	}
	
	$SMS->Send();

}




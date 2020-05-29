<?
exit;
include_once("./_common.php");

include_once("$g4[path]/lib/icode.sms.lib.php");


$sms_contents = "<오플닷컴>
이틀간 PG사 스스템 문제로 카드 결제시 일부 오류가 발생되었습니다.";
$sms_contents2 = "현재 국내외 겸용 신용카드 결제가 가능하니 많은 이용 부탁드립니다.
ople.com";



$sql = sql_query("
	select 
		replace(a.od_hp,'-','') as hp1,
		replace(a.od_b_hp,'-','') as hp2,
		if(replace(a.od_hp,'-','') != replace(a.od_b_hp,'-',''),1,0) as def
		
	from 
		yc4_order a
		left join
		yc4_cart b on a.on_uid = b.on_uid
	where 
		od_time >= '2014-07-22' 
		and 
		od_settle_case = '신용카드'
		and
		a.od_receipt_bank + a.od_receipt_card + a.od_receipt_point = 0
	group by a.od_id
");



$SMS = new SMS; // SMS 연결

$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
$send_number = preg_replace("/[^0-9]/", "", $default[de_sms_hp]); // 발신자번호
$i=0;
while($row = sql_fetch_array($sql)){
	$i++;
	$receive_number = preg_replace("/[^0-9]/", "", $row['hp1']); // 수신자번호
	$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
	$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents2), "");
	if($row['def']){
		$i++;
		$receive_number = preg_replace("/[^0-9]/", "", $row['hp2']); // 수신자번호
		$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
		$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents2), "");
	}
}



$SMS->Send();
echo $i.'건 발송';
print_r($SMS);

?>
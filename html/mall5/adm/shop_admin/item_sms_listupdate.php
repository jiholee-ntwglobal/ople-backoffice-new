<?php
$sub_menu = "300940";
include_once("./_common.php");
include_once "{$g4['path']}/lib/icode.sms.lib.php";
auth_check($auth[$sub_menu], "w");


if($w == 'd')
{
	sql_query("delete from {$g4['item_sms_table']} where ts_id='{$_GET['ts_id']}'");
	goto_url("./item_sms_list.php?ts_send=$ts_send&sfl=$sfl&stx=".urlencode($stx)."&page=$page");
}

if($ts_send)
{
	for($i=0, $count=count($_POST['chk']); $i<$count; $i++)
	{
		$k = $_POST['chk'][$i];
		sql_query("update {$g4['item_sms_table']} set ts_send=0 where ts_id='{$_POST['ts_id'][$k]}' ");
	}
}
else
{
	if(trim($default['de_item_sms_msg']) == '')
		alert("확장설정에서 \'상품입고 SMS 통보내용\'에 기본입력내용을 입력후에 이용하십시오.");

	$SMS = new SMS;
	$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
	$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호는 쇼핑몰대표번호(일반전화)
	$count = count($_POST['chk']);
	for($i=0; $i<$count; $i++)
	{
		$k = $_POST['chk'][$i];
		$receive_number = preg_replace("/[^0-9]/", "", $_POST['ts_hp'][$k]); // 수신자번호
		$sms_contents = trim($default['de_item_sms_msg']);
		$sms_contents = preg_replace("/{이름}/", $_POST['ts_name'][$k], $sms_contents);
		$temp_it_name = cut_str(preg_replace("/\"|\'/", "", $_POST['it_name'][$k]), 16, '..');
		$sms_contents = preg_replace("/{상품명}/", $temp_it_name, $sms_contents);
		$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");

		sql_query("update {$g4['item_sms_table']} set ts_send=1, ts_send_time='{$g4['time_ymdhis']}' where ts_id='{$_POST['ts_id'][$k]}' ");
	}
	$SMS->Send();
	$SMS->Init();
}

goto_url("./item_sms_list.php?ts_send=$ts_send&sfl=$sfl&stx=".urlencode($stx)."&page=$page");
?>
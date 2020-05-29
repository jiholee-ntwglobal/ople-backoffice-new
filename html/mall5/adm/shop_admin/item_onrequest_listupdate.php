<?php
$sub_menu = "300777";
include_once("./_common.php");
include_once "{$g4['path']}/lib/icode.sms.lib.php";
auth_check($auth[$sub_menu], "w");


//print_r2($_POST); EXIT;
if($w == 'd')
{
	sql_query("delete from {$g4['yc4_onrequest_table']} where on_pid='{$_GET['on_pid']}'");
	goto_url("./item_onrequest_list.php?on_sms_post=$on_sms_post&sel_field=$sel_field&search=".urlencode($stx)."&page=$page");
}

	$SMS = new SMS;
	$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
	$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호는 쇼핑몰대표번호(일반전화)
	$sms_contents = "{이름}님OPLE.COM입니다. 입고요청 {상품명} 입고되었습니다.";
	$count = count($_POST['chk']);

	for($i=0; $i<$count; $i++)
	{
		$k = $_POST['chk'][$i];
		$receive_number = preg_replace("/[^0-9]/", "", $_POST['on_hp'][$k]); // 수신자번호
		$sms_contents = preg_replace("/{이름}/", $_POST['on_name'][$k], $sms_contents);
		$temp_it_name = cut_str(preg_replace("/\"|\'/", "", $_POST['on_it_name'][$k]), 16, '..');
		$sms_contents = preg_replace("/{상품명}/", $temp_it_name, $sms_contents);
		$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");

		sql_query("update {$g4['yc4_onrequest_table']}
			set on_sms_post=1,
				on_sms_post_datetime='{$g4['time_ymdhis']}',
				it_id=trim('{$_POST['it_id'][$k]}')
			where on_pid='{$_POST['on_pid'][$k]}' ");
	}

	$SMS->Send();
	$SMS->Init();


goto_url("./item_onrequest_list.php?on_sms_post=$on_sms_post&sel_field=$sel_field&stx=".urlencode($stx)."&page=$page");
?>
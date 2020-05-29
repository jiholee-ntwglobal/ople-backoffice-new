<?php
/*
----------------------------------------------------------------------
file name	 : mail_resend.php
comment		 : 주문메일 재발송
date		 : 2015-02-05
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/
exit;
include "_common.php";
include_once($g4['full_path']."/lib/mailer.lib.php");

$od_id = '1502040458';

$od = sql_fetch("
	select
		*
	from
		".$g4['yc4_order_table']."
	where
		od_id = '".$od_id."'
");
$member['mb_id'] = $od['mb_id'];
$tmp_on_uid = $od['on_uid'];
$od_send_cost = $od['od_send_cost'];
$od_receipt_point = $od['od_receipt_point'];
$od_receipt_card = $od['od_receipt_card'];
$od_receipt_bank = $od['od_receipt_bank'];
$od_bank_account = $od['od_bank_account'];
$od_deposit_name = $od['od_deposit_name'];


$od_name = $od['od_name'];
$od_tel = $od['od_tel'];
$od_hp = $od['od_hp'];
$od_zip1 = $od['od_zip1'];
$od_zip2 = $od['od_zip2'];
$od_addr1 = $od['od_addr1'];
$od_addr2 = $od['od_addr2'];
$od_hope_date = $od['od_hope_date'];
$od_b_name = $od['od_b_name'];
$od_b_tel = $od['od_b_tel'];
$od_b_hp = $od['od_b_hp'];
$od_b_zip1 = $od['od_b_zip1'];
$od_b_zip2 = $od['od_b_zip2'];
$od_b_addr1 = $od['od_b_addr1'];
$od_b_addr2 = $od['od_b_addr2'];
$od_memo = $od['od_memo'];







$subject = "{$default[de_admin_company_name]}에서 다음과 같이 주문하셨습니다.";

include $g4['full_shop_path']."/ordermail1.inc.php";


ob_start();
include $g4['full_shop_path']."/mail/orderupdate2.mail.php";
$content = ob_get_contents();
ob_end_clean();

//echo $content;

mailer($default['de_admin_company_name'], $default['de_post_mail_addr'], 'love35540@naver.com', $subject, $content, 1);
?>
<?php
$sub_menu = "400400";
include_once("./_common.php");
include_once("$g4[path]/lib/mailer.lib.php");
include_once("$g4[path]/lib/icode.sms.lib.php");

$opk_chk = sql_fetch("select opk_fg from {$g4['yc4_order_table']} where od_id = '".$od_id."'");

if($opk_chk['opk_fg'] == 'Y'){
    $opk = true;

    include_once $g4['full_path']."/lib/opk_db.php";

    $opk_db = new opk_db;

}else{
    $opk = false;
}



$sql = " update $g4[yc4_order_table]
			set od_shop_memo = '$od_shop_memo',
				od_name = '$od_name',
				od_tel = '$od_tel',
				od_hp = '$od_hp',
				od_zonecode = '".$od_zonecode."',
				od_zip1 = '$od_zip1',
				od_zip2 = '$od_zip2',
				od_addr1 = '$od_addr1',
				od_addr2 = '$od_addr2',
				od_email = '$od_email'";
if ($default[de_hope_date_use])
	$sql .= " , od_hope_date = '$od_hope_date' ";


// 김선용 201211 :
if($_POST['od_ship'] == '0')
{
	$sql .= ", od_b_name = '$od_b_name',
			od_b_tel = '$od_b_tel',
			od_b_hp = '$od_b_hp',
			od_b_zonecode = '".$od_b_zonecode."',
			od_b_zip1 = '$od_b_zip1',
			od_b_zip2 = '$od_b_zip2',
			od_b_addr1 = '$od_b_addr1',
			od_b_addr2 = '$od_b_addr2' ";
	$sql .= " where od_id = '$od_id' ";
	sql_query($sql);
    if($opk){
        $opk_db->query($sql);
    }
}
else if($_POST['od_ship'] == '1')
{
	//print_r2($_POST); EXIT;
	function rep_quot($str){
		return preg_replace('/\"/', "&quot;", stripslashes($str));
	}

	$sql .= " where od_id = '$od_id' ";
	sql_query($sql);

	// 김선용 201211 : 복수배송지처리
	for($a=0; $a<count($_POST['os_pid']); $a++)
	{
		$post_send_mail = $post_send_sms = false;
		sql_query("update {$g4['yc4_os_table']}
			set os_post_name	= '".rep_quot($_POST['os_post_name'][$a])."',
				os_name			= '".rep_quot($_POST['os_name'][$a])."',
				os_tel			= trim('{$os_tel[$a]}'),
				os_hp			= trim('{$os_hp[$a]}'),
				os_zonecode		= trim('{$os_zonecode[$a]}'),
				os_zip1			= trim('{$os_zip1[$a]}'),
				os_zip2			= trim('{$os_zip2[$a]}'),
				os_addr1		= trim('{$os_addr1[$a]}'),
				os_addr2		= trim('{$os_addr2[$a]}'),
				os_invoice		= trim('{$os_invoice[$a]}'),
				os_invoice_time = trim('{$os_invoice_time[$a]}'),
				os_dl_id		= '{$os_dl_id[$a]}'
			where os_pid='{$os_pid[$a]}' ");

		if($_POST['os_send_mail'][$a]){
			// 메일발송
			define("_ORDERMAIL_", true);
			$post_send_mail = true;
			$post_pid = $os_pid[$a];
			$post_dl_id = $os_dl_id[$a];
			$post_invoice = $os_invoice[$a];
			$post_invoice_time = $os_invoice_time[$a];
			include "./ordermail_multi.inc.php";
		}
		if($_POST['os_send_sms'][$a]){
			// SMS 문자전송
			define("_ORDERSMS_", true);
			$post_send_sms = true;
			$post_pid = $os_pid[$a];
			$post_dl_id = $os_dl_id[$a];
			$post_invoice = $os_invoice[$a];
			$post_invoice_time = $os_invoice_time[$a];
			include "./ordersms.inc.php";
		}
	}
}

if($od_bank_time){
    $od = sql_fetch("select od_pay_time from {$g4['yc4_order_table']} where od_id = '{$od_id}'");
    if(!$od['od_pay_time']){
        sql_query("update {$g4['yc4_order_table']} set od_pay_time = '{$od_bank_time}' where od_id = '{$od_id}'");
    }
}


$qstr = "sort1=$sort1&sort2=$sort2&sel_field=$sel_field&search=$search&page=$page";

goto_url("./orderform.php?od_id=$od_id&$qstr");

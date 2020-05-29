<?php
$sub_menu = "400400";
include_once("./_common.php");
include_once("$g4[path]/lib/mailer.lib.php");
include_once("$g4[path]/lib/icode.sms.lib.php");

auth_check($auth[$sub_menu], "w");

$opk_chk = sql_fetch("select opk_fg from {$g4['yc4_order_table']} where on_uid = '".$od_id."'");
if($opk_chk['opk_fg'] == 'Y'){ // 오플 코리아 주문서인지 체크
    $opk = true;
    include_once $g4['full_path']."/lib/opk_db.php";
    $opk_db = new opk_db;
}else{
    $opk = false;
}

if ($od_bank_time)
{
    if (check_datetime($od_bank_time) == false)
        alert("무통장 입금일시 오류입니다.");
}

if ($od_card_time)
{
    if (check_datetime($od_card_time) == false)
        alert("신용카드 입금일시 오류입니다.");
}

$sql = " update $g4[yc4_order_table]
            set od_deposit_name  = '$od_deposit_name',
                od_bank_account  = '$od_bank_account',
                od_bank_time     = '$od_bank_time',
                od_card_time     = '$od_card_time',
                od_receipt_bank  = '$od_receipt_bank',
                od_receipt_card  = '$od_receipt_card',
                od_receipt_point = '$od_receipt_point',
                od_cancel_card   = '$od_cancel_card',
                od_dc_amount     = '$od_dc_amount',
                od_refund_amount = '$od_refund_amount',
                dl_id            = '$dl_id',
                od_invoice       = '$od_invoice',
                od_invoice_time  = '$od_invoice_time',
                od_b_jumin = '{$od_b_jumin}'";
if (isset($od_send_cost))
    $sql .= " , od_send_cost = '$od_send_cost' ";
$sql .= " where od_id = '$od_id' ";
sql_query($sql);
if($opk) {
    $opk_db->query($sql);
}


	// 메일발송
	define("_ORDERMAIL_", true);
	include "./ordermail.inc.php";


	// SMS 문자전송
	define("_ORDERSMS_", true);
	include "./ordersms.inc.php";


$qstr = "sort1=$sort1&sort2=$sort2&sel_field=$sel_field&search=$search&page=$page";

goto_url("./orderform.php?od_id=$od_id&$qstr");

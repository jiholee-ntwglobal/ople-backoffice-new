<?php
include_once("./_common.php");

// 세션에 저장된 토큰과 폼으로 넘어온 토큰을 비교하여 틀리면 에러
if ($token && get_session("ss_token") == $token) {
    // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
    set_session("ss_token", "");
} else {
	if($_POST['mode'] == 'ajax')
		echo '토큰 에러';
	else
	    alert_close("토큰 에러");
}

$od = sql_fetch(" select * from $g4[yc4_order_table] where od_id = '$od_id' and on_uid = '$on_uid' and mb_id = '$member[mb_id]' ");

if (!$od[od_id]) {
	if($_POST['mode'] == 'ajax')
		echo '존재하는 주문이 아닙니다.';
	else
	    alert("존재하는 주문이 아닙니다.");
}

if (($od[od_temp_bank] > 0 && $od[od_receipt_bank] == 0) ||
    ($od[od_temp_card] > 0 && $od[od_receipt_card] == 0)) {
    ;
} else {
	if($_POST['mode'] == 'ajax')
		echo '취소할 수 있는 주문이 아닙니다.';
	else
	    alert("취소할 수 있는 주문이 아닙니다.");
}

// 장바구니 자료 취소
sql_query(" update $g4[yc4_cart_table] set ct_status = '취소',ct_status_update_dt = '".$g4['time_ymdhis']."' where on_uid = '$on_uid'");
sql_query(" update $g4[yc4_order_table] set od_status_update_dt = '".$g4['time_ymdhis']."' where on_uid = '$on_uid' and od_status_update_dt is not null");

// 주문 취소
$cancel_memo = addslashes($cancel_memo);
//sql_query(" update $g4[yc4_order_table] set od_temp_point = '0', od_receipt_point = '0', od_shop_memo = concat(od_shop_memo,\"\\n주문자 본인 직접 취소 - {$g4['time_ymdhis']} (취소이유 : {$cancel_memo})\") where on_uid = '$on_uid' ");
sql_query(" update $g4[yc4_order_table] set od_send_cost = '0', od_temp_point = '0', od_receipt_point = '0', od_shop_memo = concat(od_shop_memo,\"\\n주문자 본인 직접 취소 - {$g4['time_ymdhis']} (취소이유 : {$cancel_memo})\") where on_uid = '$on_uid' ");


# 주문 취소한 장바구니상품을 현재 장바구니로 복사 2014-11-24 홍민기 #
$sql = sql_query("select * from ".$g4['yc4_cart_table']." where on_uid = '".$on_uid."'");
$s_on_uid = get_session('ss_on_uid');
while($row = sql_fetch_array($sql)){
	$update_qry = "
		insert into
			".$g4['yc4_cart_table']."
		set
			on_uid = '".$s_on_uid."',
			it_id = '".$row['it_id']."',
			it_opt1 = '".$row['it_opt1']."',
			it_opt2 = '".$row['it_opt2']."',
			it_opt3 = '".$row['it_opt3']."',
			it_opt4 = '".$row['it_opt4']."',
			it_opt5 = '".$row['it_opt5']."',
			it_opt6 = '".$row['it_opt6']."',
			ct_status = '쇼핑',
			ct_history = '".mysql_real_escape_string($row['ct_history'])."',
			ct_amount = '".$row['ct_amount']."',
			ct_point = '".$row['ct_point']."',
			ct_point_use = '".$row['ct_point_use']."',
			ct_stock_use = '".$row['ct_stock_use']."',
			ct_qty = '".$row['ct_qty']."',
			ct_time = '".date('Y-m-d H:i:s')."',
			ct_ip = '".$_SERVER['REMOTE_ADDR']."',
			ct_send_cost = '".$row['ct_send_cost']."',
			ct_mb_id = '".$row['ct_mb_id']."',
			ct_ship_os_pid = '".$row['ct_ship_os_pid']."',
			ct_ship_ct_qty = '".$row['ct_ship_ct_qty']."',
			ct_ship_stock_use = '".$row['ct_ship_stock_use']."'
	";

	$ct_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_cart_table']." where on_uid = '".$s_on_uid."' and it_id = '".$row['it_id']."'");
	if($ct_chk['cnt']>0){
		mysql_query("
			update ".$g4['yc4_cart_table']." set ct_qty = ct_qty + ".(int)$row['ct_qty']." where on_uid = '".$s_on_uid."' and it_id = '".$row['it_id']."'
		");
	}else{
		mysql_query($update_qry);
	}


}

// 주문취소 회원의 포인트를 되돌려 줌
if ($od[od_receipt_point] > 0) {
    insert_point($member[mb_id], $od[od_receipt_point], "주문번호 $od_id 본인 취소");
}

if($_POST['mode'] != 'ajax')
	goto_url("./orderinquiryview.php?od_id=$od_id&on_uid=$on_uid");
?>
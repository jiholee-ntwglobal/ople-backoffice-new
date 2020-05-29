<?php
/*
----------------------------------------------------------------------
file name	 : orderlist_point_update.php
comment		 : 포인트 주문건 뱅송상태 준비로 업데이트 처리 파일
date		 : 2014-12-03
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400400";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

# 포인트로만 주문한 주문서인지 체크 #
$chk1 = sql_fetch("
	select
		count(*) as cnt
	from
		".$g4['yc4_order_table']."
	where
		on_uid = '".$on_uid."'
		and
		od_temp_card = 0
		and
		od_temp_bank = 0
		and
		od_temp_point > 0
");

if($chk1['cnt']<1){
	alert('전액 포인트결제 주문서가 아닙니다.');
	exit;
}


# 전액 결제완료된 주문건인지 체크 #
$chk2 = sql_fetch("
	select
		count(*) as cnt
	from
		".$g4['yc4_order_table']."
	where
		on_uid = '".$on_uid."'
		and
		od_receipt_point = od_temp_point
");

if($chk2['cnt']<0){
	alert('미수금이 존재하는 주문건 입니다.');
	exit;
}

$update_qry = "update ".$g4['yc4_cart_table']." set ct_status = '준비' where on_uid = '".$on_uid."'";

if(!sql_query($update_qry)){
	alert('처리중 오류 발생! 관리자에게 문의하세요');
	exit;

}

sql_query("
	update ".$g4['yc4_order_table']." set od_status_update_dt = '".$g4['time_ymdhis']."' where on_uid = '".$on_uid."'
");


$pg_param = $_GET;
unset($pg_param['on_uid']);
$pg_param = http_build_query($pg_param);

alert('해당 주문서가 준비로 변경되었습니다.','orderlist_point.php?'.$pg_param);
exit;


?>
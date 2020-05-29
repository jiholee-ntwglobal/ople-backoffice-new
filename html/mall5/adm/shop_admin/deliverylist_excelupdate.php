<?php
$sub_menu = "400510";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");


if(trim($_POST['excel_data']) == '') alert("입력된 자료가 없습니다.");

// 김선용 201208 : php v5.3 이상에서 split 삭제됨
// 김선용 2009 : 내용이 많은 경우 explode 보다 split 가 빠름
$temp = explode("\n", $_POST['excel_data']);
$temp_count = count($temp);
for($i=0; $i<$temp_count; $i++)
{
	//$fld = explode("\t", stripslashes($temp[$i]));
	$temp[$i] = trim($temp[$i]);
	$od_id = preg_replace("/[^0-9]/", "", $temp[$i]);
	$sql = "update $g4[yc4_order_table]
			   set od_invoice_time = '{$g4['time_ymdhis']}',
				   dl_id           = 7, /* 배송회사 코드 */
				   od_invoice      = '{$temp[$i]}', /* 송장번호 */
				   od_shop_memo	   = concat(od_shop_memo, '\n엑셀 배송일괄처리|{$g4['time_ymdhis']}')
			 where od_id           = '$od_id' ";
	sql_query($sql);

	// 장바구니 상태가 '주문', '준비' 일 경우 '배송' 으로 상태를 변경
	$od = sql_fetch("select on_uid from {$g4['yc4_order_table']} where od_id='$od_id' ");
	$sql = " update $g4[yc4_cart_table]
				set ct_status = '배송'
			  where ct_status in ('주문', '준비')
				and on_uid = '{$od['on_uid']}' ";
	sql_query($sql);
}

alert("총 ".number_format($i,0)." 건의 자료가 업데이트 되었습니다.(주문서기준)", "deliverylist_excel.php");
?>
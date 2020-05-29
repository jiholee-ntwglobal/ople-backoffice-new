<?php 
/*
----------------------------------------------------------------------
file name	 : ajax.proc_deposit_order.php
comment		 : 입금확인 처리 ajax
date		 : 2015-04-13
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400992";
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'single'){

	$order_data = sql_fetch("select * from yc4_order where od_id='$_POST[od_id]'");

	$order_update_qry = "update yc4_order set od_deposit_name='$_POST[deposit_nm]', od_receipt_bank='$_POST[deposit_amount]', od_bank_time=NOW(), od_status_update_dt=NOW() where od_id='$_POST[od_id]'";	

	$cart_update_qty = "update yc4_cart set ct_status='준비',ct_status_update_dt=NOW() where on_uid='$order_data[on_uid]'";		

	sql_query($order_update_qry);

	sql_query($cart_update_qty);	

	sql_query("update yc4_deposit_upload_history set match_od_id='$_POST[od_id]' where upload_date='".date('Ymd')."' and seq='$_SESSION[max_seq]' and name='$_POST[deposit_nm]' and price='$deposit_amount'");

	$msg = "입금확인 및 주문상태 변경이 완료되었습니다.";

} elseif($_POST['mode'] == 'bulk'){

	$parce_num = $_POST['parce_num'];

	$proc_num_array = array();
	$proc_cnt = 0;

	if(is_array($parce_num)){
		foreach($parce_num as $num){

			$od_id = $_POST['matching_order_'.$num];

			if($od_id){
				$od_deposit_name = $_POST['deposit_name_'.$num];
				$deposit_amount = $_POST['deposit_amount_'.$num];

				$order_data = sql_fetch("select * from yc4_order where od_id='$od_id'");

				$order_update_qry = "update yc4_order set od_deposit_name='$od_deposit_name', od_receipt_bank='$deposit_amount', od_bank_time=NOW(), od_status_update_dt=NOW() where od_id='$od_id'";				

				$cart_update_qty = "update yc4_cart set ct_status='준비',ct_status_update_dt=NOW() where on_uid='$order_data[on_uid]'";
				

				sql_query($order_update_qry);
				
				sql_query($cart_update_qty);				

				sql_query("update yc4_deposit_upload_history set match_od_id='$od_id' where upload_date='".date('Ymd')."' and seq='$_SESSION[max_seq]' and name='$od_deposit_name' and price='$deposit_amount'");

				$proc_cnt++;

				array_push($proc_num_array, $num);
			}

		}
	} else {
		$od_id = $_POST['matching_order_'.$parce_num];
		$od_deposit_name = $_POST['deposit_name_'.$parce_num];
		$deposit_amount = $_POST['deposit_amount_'.$parce_num];
		

		$order_data = sql_fetch("select * from yc4_order where od_id='$od_id'");

		$order_update_qry = "update yc4_order set od_deposit_name='$od_deposit_name', od_receipt_bank='$deposit_amount', od_bank_time=NOW(), od_status_update_dt=NOW() where od_id='$od_id'";		

		$cart_update_qty = "update yc4_cart set ct_status='준비',ct_status_update_dt=NOW() where on_uid='$order_data[on_uid]'";				

		sql_query($order_update_qry);

		sql_query($cart_update_qty);
		

		sql_query("update yc4_deposit_upload_history set match_od_id='$od_id' where upload_date='".date('Ymd')."' and seq='$_SESSION[max_seq]' and name='$od_deposit_name' and price='$deposit_amount'");

		$proc_cnt++;

		array_push($proc_num_array, $parce_num);		
	}

	$proc_num = implode('|', $proc_num_array);

	$msg = "${proc_cnt}건에 대한 입금확인 및 주문상태 변경이 완료되었습니다.";
}

header("Content-type: text/xml;charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
echo ('<?xml version="1.0" encoding="utf-8"?>');
?>
<result>
	<error>0</error>
	<msg><![CDATA[<?php echo $msg; ?>]]></msg>
	<proc_num><![CDATA[<?php echo $proc_num; ?>]]></proc_num>
</result>
<?php
$sub_menu = "400500";
include_once("./_common.php");
include_once("$g4[path]/lib/mailer.lib.php");
include_once("$g4[path]/lib/icode.sms.lib.php");
auth_check($auth[$sub_menu], "w");



define("_ORDERMAIL_", true);
$admin = get_admin('super');

if ($default[de_sms_use] == "icode")
{
	$SMS = new SMS;
	$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
}


for ($m=0; $m<count($_POST[od_id]); $m++)
{
	// 김선용 201211 : 복수배송처리
	if($_POST['od_ship'][$m] == '1')
	{
		// 김선용 201211 : 서버(엔징스) or php 설정에서 rong array 지시자에서 2차원배열 막힌것으로 판단됨. 따라서 2차원배열은 _REQUEST 수퍼글로벌로 사용
		for($k=0; $k<count($_REQUEST['os_pid'][$m]);$k++)
		{
			if(trim($_REQUEST['os_invoice'][$m][$k]) && $_REQUEST['os_dl_id'][$m][$k]){
				sql_query("update {$g4['yc4_os_table']}
					set os_invoice		= trim('{$_REQUEST['os_invoice'][$m][$k]}'),
						os_invoice_time = trim('{$_REQUEST['os_invoice_time'][$m][$k]}'),
						os_dl_id		= '{$_REQUEST['os_dl_id'][$m][$k]}'
					where os_pid='{$_REQUEST['os_pid'][$m][$k]}' ");
			}
			// 기 입력된 송장번호나 배송회사가 다르면 처리
			if($_REQUEST['os_save_dl_id'][$m][$k] != $_REQUEST['os_dl_id'][$m][$k] || $_REQUEST['os_save_invoice'][$m][$k] != trim($_REQUEST['os_invoice'][$m][$k]))
			{
				$od_id = $_POST[od_id][$m];
				$on_uid = $_POST[on_uid][$m];

				$sql = " update $g4[yc4_cart_table]
							set ct_status = '배송'
						  where ct_status in ('주문', '준비')
							and on_uid = '$on_uid' and ct_ship_os_pid like '%{$_REQUEST['os_pid'][$m][$k]}%' ";
				sql_query($sql);

				if($_POST['od_send_mail']){
					$post_send_mail = true;
					$post_pid = $_REQUEST['os_pid'][$m][$k];
					$post_dl_id = $_REQUEST['os_dl_id'][$m][$k];
					$post_invoice = $_REQUEST['os_invoice'][$m][$k];
					$post_invoice_time = $_REQUEST['os_invoice_time'][$m][$k];
					include "./ordermail_multi.inc.php";
				}

				// 재고 반영
				$sql2 = " select it_id, ct_id, ct_stock_use, ct_qty, ct_ship_ct_qty, ct_ship_os_pid, ct_ship_stock_use from $g4[yc4_cart_table]
						   where on_uid = '$on_uid' and ct_ship_os_pid like '%{$_REQUEST['os_pid'][$m][$k]}%'
							/* and ct_stock_use = '0' */ ";   ### // 김선용 201211 : 상품부분별 재고처리 완료값을 어떻게 할것인가????
				$result2 = sql_query($sql2);
				while($row2=sql_fetch_array($result2))
				{
					$n_qty = 0;
					// 수량처리. os_pid 처리
					if($row2['ct_ship_ct_qty']){
						$qty = explode("|", $row2['ct_ship_ct_qty']);
						$pid = explode("|", $row2['ct_ship_os_pid']);
						$stock = explode("|", $row2['ct_ship_stock_use']);
						$stock_arr = array();
						for($b=0; $b<count($pid); $b++){
							// 해당 상품의 재고처리
							if($stock[$b] == '0' && $pid[$b] == $_REQUEST['os_pid'][$m][$k] && $qty[$b] != ''){
								$n_qty = (int)$qty[$b];
								$stock_arr[] = '1';
							}
							else
								$stock_arr[] = $stock[$b];
						}
					}
//					$sql3 = sql_query(" update $g4[yc4_item_table] set it_stock_qty = it_stock_qty - '$n_qty' where it_id = '$row2[it_id]' ");
					$sql4 = " update $g4[yc4_cart_table]
								set ct_stock_use  = '1',
									ct_ship_stock_use = '".implode("|", $stock_arr)."',
									ct_history    = CONCAT(ct_history,'\n복수배송일괄|$now|$REMOTE_ADDR')
							  where on_uid = '$on_uid' and ct_ship_os_pid like '%{$_REQUEST['os_pid'][$m][$k]}%'
								and ct_id  = '$row2[ct_id]' ";
					sql_query($sql4);
				}

				//-----------------------------------------
				// 일괄배송처리시 SMS 문자 일괄전송
				if ($default[de_sms_use4] && $_POST['send_sms'])
				{
					$sql = " select od_id, od_name, od_invoice, od_hp, dl_id from $g4[yc4_order_table] where od_id = '$od_id' ";
					$od = sql_fetch($sql);

					$sql = " select dl_company from $g4[yc4_delivery_table] where dl_id = '{$_REQUEST['os_dl_id'][$m][$k]}' ";
					$dl = sql_fetch($sql);

					$sms_contents = $default[de_sms_cont4];
					$sms_contents = preg_replace("/{이름}/", $od[od_name], $sms_contents);
					$sms_contents = preg_replace("/{택배회사}/", $dl[dl_company], $sms_contents);
					$sms_contents = preg_replace("/{운송장번호}/", $_REQUEST['os_invoice'][$m][$k], $sms_contents);
					$sms_contents = preg_replace("/{주문번호}/", $od[od_id], $sms_contents);
					$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);

					$receive_number = preg_replace("/[^0-9]/", "", $od[od_hp]);	// 수신자번호 (받는사람 핸드폰번호 ... 여기서는 주문자님의 핸드폰번호임)
					$send_number = preg_replace("/[^0-9]/", "", $default[de_admin_company_tel]); // 발신자번호
					if ($default[de_sms_use] == "icode")
					{
						$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
					}
				}
			}
		}
	}
	else
	{
		// 배송회사와 운송장번호가 있는것만 수정
		if ($_POST[dl_id][$m] && trim($_POST[od_invoice][$m]))
		{
			$sql = "update $g4[yc4_order_table]
					   set od_invoice_time = '{$_POST[od_invoice_time][$m]}',
						   dl_id           = '{$_POST[dl_id][$m]}',
						   od_invoice      = '{$_POST[od_invoice][$m]}'
					 where od_id           = '{$_POST[od_id][$m]}' ";
			sql_query($sql);

			// 이전에 입력한 배송회사, 운송장번호가 틀리다면 메일 발송
			if ($_POST[save_od_invoice][$m] != trim($_POST[od_invoice][$m]) || $_POST[save_dl_id][$m] != $_POST[dl_id][$m])
			{
				$od_id = $_POST[od_id][$m];

				// 장바구니 상태가 '주문', '준비' 일 경우 '배송' 으로 상태를 변경
				$on_uid = $_POST[on_uid][$m];
				$sql = " update $g4[yc4_cart_table]
							set ct_status = '배송'
						  where ct_status in ('주문', '준비')
							and on_uid = '$on_uid' ";
				sql_query($sql);

				include "./ordermail.inc.php";

				// 재고 반영
				$sql2 = " select it_id, ct_id, ct_stock_use, ct_qty from $g4[yc4_cart_table]
						   where on_uid = '$on_uid'
							 and ct_stock_use = '0' ";
				$result2 = sql_query($sql2);
				for ($k=0; $row2=mysql_fetch_array($result2); $k++)
				{
//					$sql3 =" update $g4[yc4_item_table] set it_stock_qty = it_stock_qty - '$row2[ct_qty]' where it_id = '$row2[it_id]' ";
//					sql_query($sql3);

					$sql4 = " update $g4[yc4_cart_table]
								set ct_stock_use  = '1',
									ct_history    = CONCAT(ct_history,'\n배송일괄|$now|$REMOTE_ADDR')
							  where on_uid = '$on_uid'
								and ct_id  = '$row2[ct_id]' ";
					sql_query($sql4);
				}

				//-----------------------------------------
				// 일괄배송처리시 SMS 문자 일괄전송
				if ($default[de_sms_use4] && $_POST['send_sms'])
				{
					$sql = " select od_id, od_name, od_invoice, od_hp, dl_id from $g4[yc4_order_table] where od_id = '$od_id' ";
					$od = sql_fetch($sql);

					$sql = " select dl_company from $g4[yc4_delivery_table] where dl_id = '$od[dl_id]' ";
					$dl = sql_fetch($sql);

					$sms_contents = $default[de_sms_cont4];
					$sms_contents = preg_replace("/{이름}/", $od[od_name], $sms_contents);
					$sms_contents = preg_replace("/{택배회사}/", $dl[dl_company], $sms_contents);
					$sms_contents = preg_replace("/{운송장번호}/", $od[od_invoice], $sms_contents);
					$sms_contents = preg_replace("/{주문번호}/", $od[od_id], $sms_contents);
					$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);

					$receive_number = preg_replace("/[^0-9]/", "", $od[od_hp]);	// 수신자번호 (받는사람 핸드폰번호 ... 여기서는 주문자님의 핸드폰번호임)
					$send_number = preg_replace("/[^0-9]/", "", $default[de_admin_company_tel]); // 발신자번호

					if ($default[de_sms_use] == "xonda")
					{
						$usrdata1 = "배송일괄처리";
						$usrdata2 = $receive_number;
						$usrdata3 = $od[od_name];

						define("_SMS_", TRUE);
						include "$g4[shop_path]/sms.inc.php";
					}
					else if ($default[de_sms_use] == "icode")
					{
						$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
					}
				}
				//---------------------------------------
			}
		}
		else
		{
			$sql = "update $g4[yc4_order_table]
					   set od_invoice_time = '',
						   dl_id           = '',
						   od_invoice      = ''
					 where od_id           = '{$_POST[od_id][$m]}' ";
			sql_query($sql);
		}
	}
}

if ($default[de_sms_use] == "icode")
{
	$SMS->Send();
}

goto_url("./deliverylist.php?sort1=$sort1&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&page=$page");
?>

<?php
$sub_menu = "400510";
include_once("./_common.php");
include_once("$g4[path]/lib/mailer.lib.php");
include_once("$g4[path]/lib/icode.sms.lib.php");

auth_check($auth[$sub_menu], "w");

function openmarket_order_chk($od_id){
	/*
     * 오픈마켓 주문번호인지 판단
     * 주문번호가 A or G 로 시작한다면 오픈마켓 주문건임
     *
     * */
	$od_id = trim($od_id);
	if(is_numeric($od_id)){
		return false;
	}
	if(strpos($od_id,'A') === false && strpos($od_id,'G') === false){
		return false;
	}
	return true;
}


if(trim($_POST['excel_data']) == '') alert("입력된 자료가 없습니다.");

// 김선용 201211 : 복수배송처리

define("_ORDERMAIL_", true);
if ($default[de_sms_use] == "icode")
{
	$SMS = new SMS;
	$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
}

include_once $g4['full_path']."/lib/opk_db.php";
$opk_db = new opk_db;

include_once $g4['full_path']."/lib/open_db.php";
$open_db = new open_db();

// 김선용 201208 : php v5.3 이상에서 split 삭제됨
// 김선용 2009 : 내용이 많은 경우 explode 보다 split 가 빠름
$temp = explode("\n", $_POST['excel_data']);
$temp_count = count($temp);
for($c=0; $c<$temp_count; $c++)
{
	if(trim($temp[$c]) != '')
	{
		if(openmarket_order_chk(trim($temp[$c]))){
			$open_db->delevery($temp[$c]);
		}else {


			$fld = explode("\t", stripslashes(trim($temp[$c])));
			$od_id = preg_replace("/[^0-9]/", "", trim($fld[0]));

			$opk_chk = sql_fetch("select opk_fg from {$g4['yc4_order_table']} where od_id = '" . $od_id . "'");
			if ($opk_chk['opk_fg'] == 'Y') {
				$opk_fg = true;
			} else {
				$opk_fg = false;
			}

			// 단수배송
			if (count($fld) == 1) {
				$sql = "update {$g4['yc4_order_table']}
					   set od_invoice_time = '{$g4['time_ymdhis']}',
						   dl_id           = 7, /* 배송회사 코드 */
						   od_invoice      = '{$fld[0]}', /* 송장번호 */
						   od_shop_memo	   = concat(od_shop_memo, '\\n', '엑셀 배송일괄처리|{$g4['time_ymdhis']}'),
						   od_status_update_dt = NOW()
					 where od_id           = '$od_id' ";
				sql_query($sql);

				if ($opk_fg) {
					$opk_db->query($sql);
				}

				// 장바구니 상태가 '주문', '준비' 일 경우 '배송' 으로 상태를 변경
				$od_row = sql_fetch("select on_uid, od_name, od_hp, mb_id from {$g4['yc4_order_table']} where od_id='$od_id' ");
				$sql = " update $g4[yc4_cart_table]
						set ct_status = '배송',ct_status_update_dt = now()
					  where ct_status in ('주문', '준비')
						and on_uid = '{$od_row['on_uid']}' ";
				sql_query($sql);
				if ($opk_fg) {
					$opk_db->query($sql);
				}


				// 김선용 201309 : 회원 프로모션 처리
				// 프로모션 테이블 조회 후, 피추천인이 정상회원인지 확인
				$chk_mpr = sql_fetch("select mb_id, mb_id2 from {$g4['yc4_member_promor']} where mb_id2='{$od_row['mb_id']}' ");
				$chk_mb = sql_fetch("select mb_id, mb_level from {$g4['member_table']} where mb_id='{$chk_mpr['mb_id']}' and mb_leave_date='' and mb_intercept_date='' ");
				// 프로모션회원의 주문정보가 있는지 확인하고 없으면 등록하고 구매누적 카운팅. 주문서 1개당 1회누적
				$chk_mpo = sql_fetch("select mo_pid from {$g4['yc4_member_promo_order']} where mb_id2='{$od_row['mb_id']}' and od_id='$od_id' ");
				if ($chk_mb['mb_id'] && !$chk_mpo['mo_pid']) {
					sql_query("update {$g4['yc4_member_promo']} set mp_order_count=mp_order_count+1 where mb_id='{$chk_mb['mb_id']}' ");
					sql_query("insert into {$g4['yc4_member_promo_order']} set mb_id2='{$od_row['mb_id']}', od_id='$od_id', mo_datetime='{$g4['time_ymdhis']}' ");

					// 프로모션 피추천회원 설정값 이상이면 승급처리(가입자/구매누적)
					// 위에 업데이트한 정보 반영값으로 다시 쿼리
					if ($chk_mb['mb_level'] < 4) {
						$c1 = sql_fetch("select mp_mb_count,mp_reg_count, mp_order_count from {$g4['yc4_member_promo']} where mb_id='{$chk_mb['mb_id']}' ");
						if ($c1['mp_reg_count'] >= $c1['mp_mb_count'] && $c1['mp_order_count'] >= $c1['mp_mb_count'])
							sql_query("update {$g4['member_table']} set mb_level=4 where mb_id='{$chk_mb['mb_id']}' ");
					}
				}


				// 재고 반영
				$sql2 = " select it_id, ct_id,opk_ct_id ct_stock_use, ct_qty from $g4[yc4_cart_table]
					   where on_uid = '{$od_row['on_uid']}'
						 and ct_stock_use = '0' ";
				$result2 = sql_query($sql2);
				while ($row2 = sql_fetch_array($result2)) {
					//sql_query(" update $g4[yc4_item_table] set it_stock_qty = it_stock_qty - '$row2[ct_qty]' where it_id = '$row2[it_id]' ");
					$sql4 = " update $g4[yc4_cart_table]
							set ct_stock_use  = '1',
								ct_history    = CONCAT(ct_history, '\\n', '배송일괄|$now|$REMOTE_ADDR')
						  where on_uid = '{$od_row['on_uid']}'
							and ct_id  = '$row2[ct_id]' ";
					sql_query($sql4);
					if ($opk_fg) {
						$opk_db->query("
                        update $g4[yc4_cart_table]
							set ct_stock_use  = '1',
								ct_history    = CONCAT(ct_history, '\\n', '배송일괄|$now|$REMOTE_ADDR')
						  where on_uid = '{$od_row['on_uid']}'
							and ct_id  = '$row2[opk_ct_id]'
                    ");
					}
				}

				if ($default['de_sms_use4'] && $default['de_sms_use'] == "icode" && $_POST['send_sms']) {
					$dl = sql_fetch("select dl_company from $g4[yc4_delivery_table] where dl_id = '7' ");
					$sms_contents = $default[de_sms_cont4];
					$sms_contents = preg_replace("/{이름}/", $od_row[od_name], $sms_contents);
					$sms_contents = preg_replace("/{택배회사}/", $dl[dl_company], $sms_contents);
					$sms_contents = preg_replace("/{운송장번호}/", $fld[0], $sms_contents);
					$sms_contents = preg_replace("/{주문번호}/", $od_id, $sms_contents);
					$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);
					$receive_number = preg_replace("/[^0-9]/", "", $od_row[od_hp]);    // 수신자번호 (받는사람 핸드폰번호 ... 여기서는 주문자님의 핸드폰번호임)
					$send_number = preg_replace("/[^0-9]/", "", $default[de_admin_company_tel]); // 발신자번호
					$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
				}
				include "./ordermail.inc.php";
			} else if (count($fld) > 1 && trim($fld[1]) != '') // 복수배송
			{
				$od_row = sql_fetch("select on_uid, od_name, od_hp from {$g4['yc4_order_table']} where od_id='$od_id' ");
				sql_query("update {$g4['yc4_os_table']}
				set os_invoice		= trim('{$fld[0]}'),
					os_invoice_time = '{$g4['time_ymdhis']}',
					os_dl_id		= '7'
				where os_pid='{$fld[1]}' ");

				sql_query("update $g4[yc4_cart_table]
				set ct_status = '배송'
				 where ct_status in ('주문', '준비')
				and on_uid = '{$od_row['on_uid']}' and ct_ship_os_pid like '%{$fld[1]}%' ");

				// 재고 반영
				$sql2 = " select it_id, ct_id, ct_stock_use, ct_qty, ct_ship_ct_qty, ct_ship_os_pid, ct_ship_stock_use from $g4[yc4_cart_table]
					   where on_uid = '{$od_row['on_uid']}' and ct_ship_os_pid like '%{$fld[1]}%' ";
				$result2 = sql_query($sql2);
				while ($row2 = sql_fetch_array($result2)) {
					$n_qty = 0;
					// 수량처리. os_pid 처리
					if ($row2['ct_ship_ct_qty']) {
						$qty = explode("|", $row2['ct_ship_ct_qty']);
						$pid = explode("|", $row2['ct_ship_os_pid']);
						$stock = explode("|", $row2['ct_ship_stock_use']);
						$stock_arr = array();
						for ($b = 0; $b < count($pid); $b++) {
							// 해당 상품의 재고처리
							if ($stock[$b] == '0' && $pid[$b] == $fld[1] && $qty[$b] != '') {
								$n_qty = (int)$qty[$b];
								$stock_arr[] = '1';
							} else
								$stock_arr[] = $stock[$b];
						}
					}
					//$sql3 = sql_query(" update $g4[yc4_item_table] set it_stock_qty = it_stock_qty - '$n_qty' where it_id = '$row2[it_id]' ");
					$sql4 = " update $g4[yc4_cart_table]
							set ct_stock_use  = '1',
								ct_ship_stock_use = '" . implode("|", $stock_arr) . "',
								ct_history    = CONCAT(ct_history,'\n엑셀 복수배송일괄|$now|$REMOTE_ADDR')
						  where on_uid = '{$od_row['on_uid']}' and ct_ship_os_pid like '%{$fld[1]}%'
							and ct_id  = '$row2[ct_id]' ";
					sql_query($sql4);
				}

				if ($default['de_sms_use4'] && $default['de_sms_use'] == "icode" && $_POST['send_sms']) {
					$dl = sql_fetch("select dl_company from $g4[yc4_delivery_table] where dl_id = '7' ");
					$sms_contents = $default[de_sms_cont4];
					$sms_contents = preg_replace("/{이름}/", $od_row[od_name], $sms_contents);
					$sms_contents = preg_replace("/{택배회사}/", $dl[dl_company], $sms_contents);
					$sms_contents = preg_replace("/{운송장번호}/", $fld[0], $sms_contents);
					$sms_contents = preg_replace("/{주문번호}/", $od_id, $sms_contents);
					$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);
					$receive_number = preg_replace("/[^0-9]/", "", $od_row[od_hp]);    // 수신자번호 (받는사람 핸드폰번호 ... 여기서는 주문자님의 핸드폰번호임)
					$send_number = preg_replace("/[^0-9]/", "", $default[de_admin_company_tel]); // 발신자번호
					$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
				}

				if ($_POST['od_send_mail']) {
					$post_send_mail = true;
					$post_pid = $fld[1];
					$post_dl_id = '7';
					$post_invoice = $fld[0];
					$post_invoice_time = $g4['time_ymdhis'];
					include "./ordermail_multi.inc.php";
				}

			}
		}
	}
}
if ($default[de_sms_use] == "icode")
{
	$SMS->Send();
}

alert("총 ".number_format($c,0)." 건의 자료가 업데이트 되었습니다.(주문서기준)", "deliverylist_excel.php");
?>
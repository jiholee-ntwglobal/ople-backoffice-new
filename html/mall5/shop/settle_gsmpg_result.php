<?php
include_once("./_common.php");

// 부모윈도우
function alert_parent($msg, $href)
{
	global $g4;
	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$g4[charset]\">";
	echo"
		<script type='text/javascript'>
		   alert(\"$msg\");
	       parent.location.replace(\"$href\");
		   //opener.location.replace('$href');
		   window.close();
		</script>
		";
	exit;
}

$abank = array();
$abank['01'] = '한국은행';
$abank['02'] = '한국산업은행';
$abank['03'] = '기업은행';
$abank['04'] = '국민은행';
$abank['05'] = '외환은행';
$abank['07'] = '수협중앙회';
$abank['11'] = '농협중앙회';
$abank['12'] = '단위농협';
$abank['16'] = '축협중앙회';
$abank['20'] = '우리은행';
$abank['21'] = '조흥은행';
$abank['22'] = '상업은행';
$abank['23'] = '제일은행';
$abank['24'] = '한일은행';
$abank['25'] = '서울은행';
$abank['26'] = '신한은행';
$abank['27'] = '씨티은행';
$abank['31'] = '대구은행';
$abank['32'] = '부산은행';
$abank['34'] = '광주은행';
$abank['35'] = '제주은행';
$abank['37'] = '전북은행';
$abank['38'] = '강원은행';
$abank['39'] = '경남은행';
$abank['41'] = '비씨카드';
$abank['53'] = '씨티은행';
$abank['54'] = '홍콩상하이은행';
$abank['71'] = '우체국';
$abank['81'] = '하나은행';
$abank['83'] = '평화은행';
$abank['93'] = '새마을금고';

//print_r2($_POST); EXIT;

$od_arr = explode("||", $_POST['rVEtc01']);
if($_POST['rVAStatus'] === 'O') // 영문자
{
	// 가상계좌내역 INSERT
	$sql = "insert $g4[yc4_card_history_table]
			   set od_id = '{$od_arr[0]}',
				   on_uid = '{$_POST['rVEtc02']}',
				   cd_mall_id = '100140',
				   cd_amount = '0',
				   cd_amount_temp = '{$od_arr[2]}', /* 입금예정액 */
				   cd_app_no = '{$_POST['rVATransactionNo']}',
				   cd_app_rt = '{$_POST['rVARespCode']}',
				   cd_trade_ymd = '{$_POST['rVATradeDate']}',
				   cd_trade_hms = '{$_POST['rVATradeTime']}',
				   cd_opt01 = '{$od_arr[1]}',
				   cd_time = '{$g4['time_ymdhis']}',
				   cd_ip = '{$_SERVER['REMOTE_ADDR']}' ";
	sql_query($sql);

	// 주문서 UPDATE
	$bank_name = $abank[$_POST['rVABankCode']];
	$sql = " update $g4[yc4_order_table]
				set od_bank_account = '{$bank_name} {$_POST['rVAVirAcctNo']} 예금주) (주)글로벌쉬핑마스터',
					od_receipt_bank = '0',
					od_bank_time = '',
					od_escrow1 = '{$_POST['rVATransactionNo']}'
			  where od_id = '{$od_arr[0]}'
				and on_uid = '{$_POST['rVEtc02']}' ";
	sql_query($sql);

	// 김선용 201208 : 포인트차감 여기서 처리. 응답지연등으로 settleresult 로 이동하지 않는경우 포인트차감안됨
	// 포인트 결제를 했다면 실제 포인트 결제한 것으로 수정합니다.
	$od = sql_fetch("select mb_id, od_receipt_point, od_temp_point from $g4[yc4_order_table] where od_id='{$od_arr[0]}' and on_uid='{$_POST['rVEtc02']}' ");
	if ($od[od_receipt_point] == '0' && $od[od_temp_point] != '0' && $od['mb_id'])
	{
		sql_query(" update $g4[yc4_order_table] set od_receipt_point = od_temp_point where od_id='{$od_arr[0]}' and on_uid='{$_POST['rVEtc02']}' ");
		insert_point($od[mb_id], (-1) * $od[od_temp_point], "주문번호:{$od_arr[0]} 결제", "@order", $od[mb_id], "{$od_arr[0]}");
	}

	//sql_query("update {$g4['yc4_cart_table']} set ct_status='준비' where on_uid='$on_uid' ");
	alert_parent("가상계좌발급이 정상적으로 승인되었습니다.", "./settleresult.php?on_uid={$_POST['rVEtc02']}");
}
else
	alert_close("가상계좌 발급실패\\n\\n실패메세지를 확인하시고 다시 시도해 주십시오.\\n\\n실패메세지 : ".stripslashes($_POST['rVAMessage1'])."\\n\\n".stripslashes($_POST['rVAMessage2']));


/*
Array
(
    [rVAApprovalType] => 6001 거래종류1 (6001 고정. 가상계좌)
    [rVATransactionNo] => 642102009072 거래번호
    [rVAStatus] => O 거래성공여부
    [rVATradeDate] => 20110709 거래시간
    [rVATradeTime] => 122045 거래시간
    [rVABankCode] => 04 발급은행코드
    [rVAVirAcctNo] => 67729070343558 가상계좌번호
    [rVAName] => 테스트상점10(주) 예금주
    [rVARespCode] => 0000 응답코드
    [rVAMessage1] => 계좌요청완료 메시지1
    [rVAMessage2] => 메시지2
    [rVEtc01] => 1107130032||okflex||10000 예비필드1
    [rVEtc02] => 3153e3f96458c6ac4e945e10ca11ad32 예비필드2
)
Array
(
    [rVAApprovalType] => 6001
    [rVATransactionNo] => 642142001697
    [rVAStatus] => O
    [rVATradeDate] => 20110713
    [rVATradeTime] => 070251
    [rVABankCode] => 04
    [rVAVirAcctNo] => 67729070307554
    [rVAName] => 테스트상점10(주)
    [rVARespCode] => 0000
    [rVAMessage1] => 계좌요청완료
    [rVAMessage2] =>
    [rVATVCode] => 6fcfbc88be09edf1d8a00899b08020c45d1d75de
    [rVEtc01] => 1107130032||okflex 예비필드1
    [rVEtc02] => 3153e3f96458c6ac4e945e10ca11ad32 예비필드2
)

*/
?>
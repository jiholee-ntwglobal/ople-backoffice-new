<?php
include_once("./_common.php");
//include_once "{$g4['path']}/head.sub.php";

//220.117.243.*
// 김선용 201107 : 가상계좌 입금통보 처리
/************
# 입금통보 송신전문
sum_amount	입금+입금취소된 금액	X(9)
transactionNo	거래번호	X(12)
acco_code	은행코드	X(2)
deal_sele	거래구분	X(2)	20:입금, 51:입금취소
in_bank_cd	송금한 은행	X(9)
amount	입금한 금액	X(9)
rece_nm	고객명	X(50)
cms_no	가상계좌번호	X(30)
deal_star_date	입급일자	X(8)	YYYYN\MMDD
deal_star_time	입금시간	X(6)	HHMMSS or HHMM+"00"
StoreId	상점아이디	X(6)
rVATVCode // transactionNo+StoreId sha1 해시값.
OrderId // 주문번호

// 실제 수신값
'sum_amount' => '160000',
'transactionNo' => '642142001697',
'acco_code' => '04',
'deal_sele' => '20',
'in_bank_cd' => '0',
'amount' => '160000',
'cms_no' => '67729070307554',
'deal_star_date' => '20110713',
'deal_star_time' => '3173511',
'StoreId' => '100140',
'rVATVCode' => '6fcfbc88be09edf1d8a00899b08020c45d1d75de',
'OrderId' => '1107130032',
*********/
// utf-8 to euc-kr
/* // 김선용 201206 : 웹사이트 utf-8 로 변경. 불필요
foreach ($_POST as $key => $val){
	$_POST[$key] = iconv("UTF-8", "EUC-KR", $val);
	${$key} = iconv("UTF-8", "EUC-KR", $val);
}
*/
/*
// 넘어오는 값 임시파일로 저장해서 확인
$file = "./cs_receive_temp_".preg_replace("/[^0-9]/", "", microtime()).".php";
$f = @fopen($file, "w");
@fwrite($f, "<?\n");
foreach($_POST as $key => $value) @fwrite($f, "'$key' => '$value', \n");
@fwrite($f, "?>");
@fclose($f);
@chmod($file, 0644);
*/

// 김선용 201208 : 수신전문 응답 euc-kr 처리 : gsm 에서 수신응답은 euc-kr 만 처리가능
$false_post = iconv("UTF-8", "EUC-KR", "errCode=1&StoreId={$_POST['StoreId']}&transactionNo={$_POST['transactionNo']}");
$true_post = iconv("UTF-8", "EUC-KR", "errCode=0&StoreId={$_POST['StoreId']}&transactionNo={$_POST['transactionNo']}");

// sha1 해시값 확인
$hash_key = sha1("{$_POST['transactionNo']}{$_POST['StoreId']}");
if($hash_key != $_POST['rVATVCode']){
	echo $false_post;
	exit;
}
if($_POST['rVATVCode'] == '' || $_POST['OrderId'] == ''){
	echo $false_post;
	exit;
}

$od = sql_fetch("select od_id, on_uid, mb_id, od_name, od_hp, od_temp_bank from {$g4['yc4_order_table']} where od_id='{$_POST['OrderId']}' ");
if(!$od['od_id']){
	echo $false_post;
	exit;
}


// 김선용 201208 : 입금/취소의 경우 기존 내역과 동일하면 통과
$chk_gsm = sql_fetch("select od_id, gs_deal_sele from {$g4['yc4_gsmpg_table']} where od_id='{$_POST['OrderId']}' and gs_transactionNo='{$_POST['transactionNo']}' ");
if($chk_gsm['od_id']){
	if($chk_gsm['gs_deal_sele'] == $_POST['deal_sele']) // 중복수신은 앞전에 수신응답 오류이므로 다시 정상수신 응답처리
	echo $true_post;
	exit;
}


if($od['od_id'])
{
	if($_POST['deal_sele'] == '20') // 입금
	{
		// 주문서
		sql_query("update {$g4['yc4_order_table']}
			set od_receipt_bank		= '{$_POST['amount']}',
				od_bank_time		= '{$g4['time_ymdhis']}'
			where od_id='{$_POST['OrderId']}' ");

		// pg결제내역. 입금자명 변경
		sql_query("update {$g4['yc4_card_history_table']} set cd_amount='{$_POST['amount']}', cd_opt01='{$_POST['rece_nm']}' where od_id='{$_POST['OrderId']}' ");

		// 장바구니
		// 입금액과 주문서결제액 비교 후 맞으면 상품상태 변경
		if($od['od_temp_bank'] == $_POST['amount'])
			sql_query("update {$g4['yc4_cart_table']} set ct_status='준비' where on_uid='{$od['on_uid']}' and ct_status in('주문') ");

		// 송신전문 db입력
		if($chk_gsm['od_id']) // 취소후 재입금이면 업데이트
		{
			sql_query("update {$g4['yc4_gsmpg_table']}
				set	gs_sum_amount		= '{$_POST['sum_amount']}',
					gs_acco_code		= '{$_POST['acco_code']}',
					gs_deal_sele		= '{$_POST['deal_sele']}',
					gs_in_bank_cd		= '{$_POST['in_bank_cd']}',
					gs_amount			= '{$_POST['amount']}',
					gs_rece_nm			= '{$_POST['rece_nm']}',
					gs_cms_no			= '{$_POST['cms_no']}',
					gs_deal_star_date	= '{$_POST['deal_star_date']}',
					gs_deal_star_time	= '{$_POST['deal_star_time']}',
					gs_StoreId			= '{$_POST['StoreId']}',
					gs_rVATVCode		= '{$_POST['rVATVCode']}',
					gs_datetime			= '{$g4['time_ymdhis']}',
					gs_ip				= '{$_SERVER['REMOTE_ADDR']}'
				where od_id='{$_POST['OrderId']}' and gs_transactionNo='{$_POST['transactionNo']}' ");
		}else{
			sql_query("insert {$g4['yc4_gsmpg_table']}
				set	gs_sum_amount		= '{$_POST['sum_amount']}',
					gs_transactionNo	= '{$_POST['transactionNo']}',
					gs_acco_code		= '{$_POST['acco_code']}',
					gs_deal_sele		= '{$_POST['deal_sele']}',
					gs_in_bank_cd		= '{$_POST['in_bank_cd']}',
					gs_amount			= '{$_POST['amount']}',
					gs_rece_nm			= '{$_POST['rece_nm']}',
					gs_cms_no			= '{$_POST['cms_no']}',
					gs_deal_star_date	= '{$_POST['deal_star_date']}',
					gs_deal_star_time	= '{$_POST['deal_star_time']}',
					gs_StoreId			= '{$_POST['StoreId']}',
					gs_rVATVCode		= '{$_POST['rVATVCode']}',
					gs_datetime			= '{$g4['time_ymdhis']}',
					gs_ip				= '{$_SERVER['REMOTE_ADDR']}',
					od_id				= '{$_POST['OrderId']}' ");
		}

		// 김선용 201207 : sms발송
		if ($default['de_sms_use3'])
		{
			$receive_number = preg_replace("/[^0-9]/", "", $od['od_hp']); // 수신자번호
			$send_number = preg_replace("/[^0-9]/", "", $default['de_sms_hp']); // 발신자번호
			//$sms_contents = $default['de_sms_cont3'];
			$sms_contents = "{이름}님 가상계좌 입금확인\n{입금액}원\n주문번호:{주문번호}\n{회사명}";
			$sms_contents = preg_replace("/{이름}/", $od['od_name'], $sms_contents);
			$sms_contents = preg_replace("/{입금액}/", nf($_POST['amount']), $sms_contents);
			$sms_contents = preg_replace("/{주문번호}/", $_POST['OrderId'], $sms_contents);
			$sms_contents = preg_replace("/{회사명}/", $default['de_admin_company_name'], $sms_contents);

			include_once("$g4[path]/lib/icode.sms.lib.php");
			$SMS = new SMS; // SMS 연결
			$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
			$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
			$SMS->Send();
		}
		// pg사 리턴
		echo $true_post;
		exit;
	}
	else if($_POST['deal_sele'] == '51') // 입금취소
	{
		sql_query("update {$g4['yc4_order_table']}
			set od_receipt_bank		= 0,
				od_bank_time		= ''
			where od_id		= '{$_POST['OrderId']}' ");

		// pg결제내역. 입금자명 변경
		sql_query("update {$g4['yc4_card_history_table']} set cd_amount=0, cd_opt01='{$_POST['rece_nm']}' where od_id='{$_POST['OrderId']}' ");

		// 장바구니
		sql_query("update {$g4['yc4_cart_table']} set ct_status='주문' where on_uid='{$od['on_uid']}' and ct_status in('준비') ");

		// 송신전문 db입력
		if($chk_gsm['od_id']) // 입금후 취소면 업데이트
		{
			sql_query("update {$g4['yc4_gsmpg_table']}
				set	gs_sum_amount		= '{$_POST['sum_amount']}',
					gs_acco_code		= '{$_POST['acco_code']}',
					gs_deal_sele		= '{$_POST['deal_sele']}',
					gs_in_bank_cd		= '{$_POST['in_bank_cd']}',
					gs_amount			= '{$_POST['amount']}',
					gs_rece_nm			= '{$_POST['rece_nm']}',
					gs_cms_no			= '{$_POST['cms_no']}',
					gs_deal_star_date	= '{$_POST['deal_star_date']}',
					gs_deal_star_time	= '{$_POST['deal_star_time']}',
					gs_StoreId			= '{$_POST['StoreId']}',
					gs_rVATVCode		= '{$_POST['rVATVCode']}',
					gs_datetime			= '{$g4['time_ymdhis']}',
					gs_ip				= '{$_SERVER['REMOTE_ADDR']}'
				where od_id='{$_POST['OrderId']}' and gs_transactionNo='{$_POST['transactionNo']}' ");
		}else{
			sql_query("insert {$g4['yc4_gsmpg_table']}
				set	gs_sum_amount		= '{$_POST['sum_amount']}',
					gs_transactionNo	= '{$_POST['transactionNo']}',
					gs_acco_code		= '{$_POST['acco_code']}',
					gs_deal_sele		= '{$_POST['deal_sele']}',
					gs_in_bank_cd		= '{$_POST['in_bank_cd']}',
					gs_amount			= '{$_POST['amount']}',
					gs_rece_nm			= '{$_POST['rece_nm']}',
					gs_cms_no			= '{$_POST['cms_no']}',
					gs_deal_star_date	= '{$_POST['deal_star_date']}',
					gs_deal_star_time	= '{$_POST['deal_star_time']}',
					gs_StoreId			= '{$_POST['StoreId']}',
					gs_rVATVCode		= '{$_POST['rVATVCode']}',
					gs_datetime			= '{$g4['time_ymdhis']}',
					gs_ip				= '{$_SERVER['REMOTE_ADDR']}',
					od_id				= '{$_POST['OrderId']}' ");
		}
		// pg사 리턴
		echo $true_post;
		exit;
	}
}

//include_once "{$g4['path']}/tail.sub.php";
// 수신전문 처리완료 포스팅
/*****************
# 입금완료전문수신완료에 대한 포스팅 전문
항목	길이	비고
errCode	오류코드	X(1)	0 : 완료, 1 : 실패
StoreId	상점아이디	X(6)	송신전문참조
transactionNo	거래번호	X(12)	송신전문참조
***************/
/*
//http://pg.gsmnton.com/gsmpg/handler/DealSuc-Return
$data = "errCode=0&StoreId=100140&transactionNo={$_POST['transactionNo']}";
$url = "http://pg.gsmnton.com/gsmpg/handler/DealSuc-Return";
$conn = curl_init();
curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($conn, CURLOPT_URL, $url);
curl_setopt($conn, CURLOPT_POST, 1);
curl_setopt($conn, CURLOPT_RETURNTRANSFER, 0);
curl_setopt($conn, CURLOPT_POSTFIELDS, $data);
$res_str = curl_exec($conn);
curl_close($conn);
*/
?>
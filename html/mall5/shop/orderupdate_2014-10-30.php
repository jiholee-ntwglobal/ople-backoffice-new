<?php
include_once("./_common.php");
//$test = true;
// 김선용 201305 :
if(get_magic_quotes_gpc())
{
    $_GET  = array_map("stripslashes", $_GET);
    $_POST = array_map("stripslashes", $_POST);
}
$_GET  = array_map("mysql_real_escape_string", $_GET);
$_POST = array_map("mysql_real_escape_string", $_POST);

// 장바구니가 비어있는가?
$tmp_on_uid = get_session('ss_on_uid');
if (get_cart_count($tmp_on_uid) == 0)// 장바구니에 담기
    alert("장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.", "./cart.php");


// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
//include_once "sjsjin.shop_guard.php";

$error = "";
// 장바구니 상품 재고 검사
// 1.03.07 : and a.it_id = b.it_id : where 조건문에 이 부분 추가
$sql = " select a.it_id,
                a.ct_qty,
                b.it_name
           from $g4[yc4_cart_table] a,
                $g4[yc4_item_table] b
          where a.on_uid = '$tmp_on_uid'
            and a.it_id = b.it_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	# 오버스탁 이벤트 상품 체크 2014-10-22 홍민기 #
	if(isset($ov_evt)){
		unset($ov_evt);
	}
	$ov_evt = sql_fetch("
		select
			ov_qty,ev_qty
		from
			yc4_over_stock_item
		where
			it_id = '".$row['it_id']."'
			and
			use_yn = 'y'
			and
			ov_qty <= '".(int)$row['ct_qty']."'
	");

	$ev_qty = 0;
	if($ov_evt){
		$ev_qty = floor($row['ct_qty'] / $ov_evt['ov_qty']); // 서비스 수량
		if($ev_qty>0){
			if(isset($ov_evt_chk)){
				unset($ov_evt_chk);
			}
			$ov_evt_chk = sql_fetch("select count(*) as cnt from yc4_over_stock_item_cart where on_uid = '".$tmp_on_uid."' and it_id = '".$row['it_id']."'");
			if($ov_evt_chk['cnt'] < 1){

				# 오버스탁 히스토리 테이블 저장
				$ov_cart_add_sql = "
					insert into
					yc4_over_stock_item_cart
					(
						on_uid, it_id, ct_qty, ev_qty, ev_time, mb_id
					)values(
						'".$tmp_on_uid."', '".$row['it_id']."', '".(int)$row['ct_qty']."', '".(int)$ev_qty."', now(), '".$member['mb_id']."'
					)
				";
				# 이벤트 상품 장바구니에 0원으로 추가 #
				$cart_add_sql = "
					insert into
						".$g4['yc4_cart_table']."
					(
						on_uid,			it_id,				it_opt1,	it_opt2,
						it_opt3,		it_opt4,			it_opt5,	it_opt6,
						ct_status,		ct_history,			ct_amount,	ct_point,
						ct_point_use,	ct_stock_use,		ct_qty,		ct_time,
						ct_ip,			ct_send_cost,		ct_mb_id,	ct_ship_os_pid,
						ct_ship_ct_qty, ct_ship_stock_use
					) VALUES
					(
						'".$tmp_on_uid."',	'".$row['it_id']."',	'',					'',
						'',					'',						'',					'',
						'쇼핑',				'오버스탁이벤트 사은품',		0,					0,
						0,					0,						".(int)$ev_qty.",	now(),
						'".$_SERVER['REMOTE_ADDR']."', '', '".$member['mb_id']."', '',
						'', ''
					)
				";
				# 오류시 입력한 데이터 삭제
				$cart_add_del_qry[] = "delete from ".$g4['yc4_cart_table']." where on_uid = '".$tmp_on_uid."' and it_id = '".$row['it_id']."' and ct_amount = 0";
				$ov_cart_del_sql[] = "delete from yc4_over_stock_item_cart where on_uid = '".$tmp_on_uid."' ";
				if(sql_query($ov_cart_add_sql)){
					sql_query($cart_add_sql);
				}
				unset($ov_cart_add_sql,$cart_add_sql);
			}
		}
		if($test){
			print_r2($ov_evt);

			echo '<br/>';
			echo $tmp_on_uid. " -- on_uid <br/>";
			echo $row['ct_qty']." -- ct_qty <br/>";
			echo $ev_qty." -- ev_qty <br/>";
			exit;
		}

	}

	if($row['it_name']){
		$row['it_name'] = get_item_name($row['it_name']);
	}
	# 원데이 체크 #
	$sql = "
		select
			it_id,
			real_qty,
			multiplication,
			order_cnt
		from
			yc4_oneday_sale_item
		where
			it_id = '".$row['it_id']."'
			and '".date('Ymd')."' between st_dt and en_dt
	";
	$oneday_data = sql_fetch($sql);
	if($oneday_data){ // 원데이는 제품 재고수량을 별도로 체크
		$it_stock_qty = ($oneday_data['real_qty'] * $oneday_data['multiplication']) - ($oneday_data['order_cnt'] * $oneday_data['multiplication']);
	}else{
    // 상품에 대한 현재고수량
		$it_stock_qty = (int)get_it_stock_qty($row[it_id]);
	}
    // 장바구니 수량이 재고수량보다 많다면 오류
    if ($row[ct_qty] > $it_stock_qty){
		// 주문서 팅겨낼시에 사은품 정보 삭제
		if(is_array($cart_add_del_qry)){
			foreach($cart_add_del_qry as $val){
				sql_query($val);
			}
		}
		if(is_array($ov_cart_del_sql)){
			foreach($ov_cart_del_sql as $val){
				sql_query($val);
			}
		}
        $error .= addslashes($row[it_name])." 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
	}
}

if ($error != "")
{
	// 주문서 팅겨낼시에 사은품 정보 삭제
	if(is_array($cart_add_del_qry)){
		foreach($cart_add_del_qry as $val){
			sql_query($val);
		}
	}
	if(is_array($ov_cart_del_sql)){
		foreach($ov_cart_del_sql as $val){
			sql_query($val);
		}
	}
    $error .= "다른 고객님께서 {$od_name}님 보다 먼저 주문하신 경우입니다. 불편을 끼쳐 죄송합니다.";
    alert($error);
}

// , 를 없애고
$od_receipt_bank = (float)str_replace(",", "", $od_receipt_bank);
$od_receipt_card = (float)str_replace(",", "", $od_receipt_card);

if ($od_settle_case == "무통장")
{
    $od_temp_point = (float)str_replace(",", "", $od_temp_point);
    $od_receipt_point = (float)str_replace(",", "", $od_temp_point);
}
else
{
	// 김선용 201107 : PG(가상계좌/카드등)결제시 포인트로 주문금액을 100% 결제한 경우 주문완료처리.
	if($od_settle_case == '신용카드' && $_POST['od_receipt_card'] == 0){
	    $od_temp_point = (float)str_replace(",", "", $od_temp_point);
		$od_receipt_point = $od_temp_point;
	}else if($od_settle_case == '가상계좌' && $_POST['od_receipt_bank'] == 0){
	    $od_temp_point = (float)str_replace(",", "", $od_temp_point);
		$od_receipt_point = $od_temp_point;
	}else if($od_settle_case == '에스크로' && $_POST['od_receipt_bank'] == 0){
	    $od_temp_point = (float)str_replace(",", "", $od_temp_point);
		$od_receipt_point = $od_temp_point;
	}else{
	    $od_temp_point = (float)str_replace(",", "", $od_temp_point);
		$od_receipt_point = 0;
	}
}

if ($od_temp_point)
{
    if ($member[mb_point] < $od_temp_point){
		// 주문서 팅겨낼시에 사은품 정보 삭제
		if(is_array($cart_add_del_qry)){
			foreach($cart_add_del_qry as $val){
				sql_query($val);
			}
		}
		if(is_array($ov_cart_del_sql)){
			foreach($ov_cart_del_sql as $val){
				sql_query($val);
			}
		}
        alert("회원님의 포인트가 부족하여 포인트로 결제 할 수 없습니다.");
	}
}

// 새로운 주문번호를 얻는다.
$od_id = get_new_od_id();

# 개인통관고유부호, 주민등록번호 중 선택한 항목이 있다면 동의 내용을 저장 2014-07-24 홍민기 #
if($_POST['customs_clearance_code']){
	switch($_POST['customs_clearance_code']){
		case 'c_code' :
			$c_code_flag = 'c';
			break;
		case 'jumin' : default : $c_code_flag = 'c';
			break;
	}
	sql_query("delete from yc4_customs_clearance_agreement where od_id = '".$od_id."' ");
	sql_query("
		insert into
			yc4_customs_clearance_agreement
		(
			od_id,od_b_name,flag,code,create_dt,create_id
		)
		values(
			'".$od_id."','".$od_b_name."','".$c_code_flag."','".trim($od_b_jumin)."','".$g4['time_ymdhis']."','".$member['mb_id']."'
		)
	");
}

// 김선용 2014.03 : kcp 가상계좌 추가포인트
if( ($default['de_kcp_escrow_point'] && $od_settle_case == '가상계좌') || ($default['de_kcp_escrow_point'] && $od_settle_case == '에스크로'))
	$kcp_escrow_point = $default['de_kcp_escrow_point'];
else
	$kcp_escrow_point = '';

if(date('Ymd') >= '20140906' && date('Ymd') <= '20140910'){ // 7월 5일부터 7일까지 주문건만
	# 3만원 이상 주문시 관리자 메모 등록
	$od_total_amount = $od_receipt_bank + $od_receipt_card + $od_receipt_point; // 총 주문액
	if($od_total_amount >= 70000){
		$od_shop_memo = "7만원 이상 구매시 YUMMYEARTH YUMMYEARTH FRUIT SNCK 5Ct OR YUMMYEARTH YUMMYEARTH GUMMY BEAR SNKPK 중 랜덤으로 사은품 증정\n7만원 이상 구매시 YUMMYEARTH YUMMYEARTH FRUIT SNCK 5Ct OR YUMMYEARTH YUMMYEARTH GUMMY BEAR SNKPK 중 랜덤으로 사은품 증정\n7만원 이상 구매시 YUMMYEARTH YUMMYEARTH FRUIT SNCK 5Ct OR YUMMYEARTH YUMMYEARTH GUMMY BEAR SNKPK 중 랜덤으로 사은품 증정\n";
	}
}

if(date('Ymd') >= '20140913' && date('Ymd') <= '20140915'){ // 7월 5일부터 7일까지 주문건만
	# 3만원 이상 주문시 관리자 메모 등록
	$od_total_amount = $od_receipt_bank + $od_receipt_card + $od_receipt_point; // 총 주문액
	if($od_total_amount >= 60000){
		$od_shop_memo = "6만원 이상 구매 고객 LILY OF THE DES LILY OF THE DESERT ALOE VERA GELLY 증정\n6만원 이상 구매 고객 LILY OF THE DES LILY OF THE DESERT ALOE VERA GELLY 증정\n6만원 이상 구매 고객 LILY OF THE DES LILY OF THE DESERT ALOE VERA GELLY 증정\n";
	}
}

if(date('Ymd') >= '20140920' && date('Ymd') <= '20140922'){ // 7월 5일부터 7일까지 주문건만
	# 3만원 이상 주문시 관리자 메모 등록
	$od_total_amount = $od_receipt_bank + $od_receipt_card + $od_receipt_point; // 총 주문액
	if($od_total_amount >= 30000){
		$od_shop_memo = "3만원 이상 구매 고객 GINGER PEOPLE GINGER PEOPLE GINGER CHEW BAG 증정\n3만원 이상 구매 고객 GINGER PEOPLE GINGER PEOPLE GINGER CHEW BAG 증정\n3만원 이상 구매 고객 GINGER PEOPLE GINGER PEOPLE GINGER CHEW BAG 증정\n";
	}
}
if(date('Ymd') >= '20140927' && date('Ymd') <= '20140929'){ // 7월 5일부터 7일까지 주문건만
	# 3만원 이상 주문시 관리자 메모 등록
	$od_total_amount = $od_receipt_bank + $od_receipt_card + $od_receipt_point; // 총 주문액
	if($od_total_amount >= 50000){
		$od_shop_memo = "5만원 이상 구매 고객 TEA TREE THERAP TEA TREE THERAPY SOAP BAR VEG BASE 증정\n5만원 이상 구매 고객 TEA TREE THERAP TEA TREE THERAPY SOAP BAR VEG BASE 증정\n5만원 이상 구매 고객 TEA TREE THERAP TEA TREE THERAPY SOAP BAR VEG BASE 증정\n";
	}
}


if(date('Ymd') >= '20141025' && date('Ymd') <= '20141027'){ // 7월 5일부터 7일까지 주문건만
	# 3만원 이상 주문시 관리자 메모 등록
	$od_total_amount = $od_receipt_bank + $od_receipt_card + $od_receipt_point; // 총 주문액
	if($od_total_amount >= 60000){
		$od_shop_memo = "6만원 이상 구매 고객 증정 Alba Botanical, Hawaiian Coconut Cream Lip Balm 0.15oz  upc  724742008376 \n6만원 이상 구매 고객 증정 Alba Botanical, Hawaiian Coconut Cream Lip Balm 0.15oz  upc  724742008376 \n6만원 이상 구매 고객 증정 Alba Botanical, Hawaiian Coconut Cream Lip Balm 0.15oz  upc  724742008376 \n";
	}
}

if(date('Ymd') >= '20141101' && date('Ymd') <= '20141103'){ // 7월 5일부터 7일까지 주문건만
	# 3만원 이상 주문시 관리자 메모 등록
	$od_total_amount = $od_receipt_bank + $od_receipt_card + $od_receipt_point; // 총 주문액
	if($od_total_amount >= 60000){
		$od_shop_memo = "6만원 이상 구매 고객 증정 Queen Helene 100% Cocoa Butter, Pure Natural Moisturizer Stick 1 oz. upc 79896049462\n6만원 이상 구매 고객 증정 Queen Helene 100% Cocoa Butter, Pure Natural Moisturizer Stick 1 oz. upc 79896049462\n6만원 이상 구매 고객 증정 Queen Helene 100% Cocoa Butter, Pure Natural Moisturizer Stick 1 oz. upc 79896049462\n";
	}
}

if($_POST['od_b_code']){
	$od_b_jumin = $_POST['od_b_code'];
}




// 주문서에 입력
$sql = " insert $g4[yc4_order_table]
            set od_id             = '$od_id',
                on_uid            = '$tmp_on_uid',
                mb_id             = '$member[mb_id]',
                od_pwd            = '$od_pwd',
                od_name           = '$od_name',
                od_email          = '$od_email',
                od_tel            = '$od_tel',
                od_hp             = '$od_hp',
                od_zip1           = '$od_zip1',
                od_zip2           = '$od_zip2',
                od_addr1          = '$od_addr1',
                od_addr2          = '$od_addr2',
				od_addr_jibeon	  = '$_POST[od_addr_jibeon]',
                od_b_name         = '$od_b_name',
                od_b_tel          = '$od_b_tel',
                od_b_hp           = '$od_b_hp',
                od_b_zip1         = '$od_b_zip1',
                od_b_zip2         = '$od_b_zip2',
                od_b_addr1        = '$od_b_addr1',
                od_b_addr2        = '$od_b_addr2',
				od_b_addr_jibeon  = '$_POST[od_b_addr_jibeon]',
                od_deposit_name   = '$od_deposit_name',
                od_memo           = '$od_memo',
                od_send_cost      = '$od_send_cost',
                od_temp_bank      = '$od_receipt_bank',
                od_temp_card      = '$od_receipt_card',
                od_temp_point     = '$od_temp_point',
                od_receipt_bank   = '0',
                od_receipt_card   = '0',
                od_receipt_point  = '$od_receipt_point',
                od_bank_account   = '$od_bank_account',
                od_shop_memo      = '$od_shop_memo',
                od_hope_date      = '$od_hope_date',
                od_time           = '$g4[time_ymdhis]',
                od_ip             = '".getenv('REMOTE_ADDR')."',
                od_settle_case    = '$od_settle_case',
				/* // 김선용 200908 : */
				od_b_jumin		= '".trim($od_b_jumin)."',
				od_recommend_off_sale = '{$_POST['od_recommend_off_sale']}',
				/* // 김선용 2014.03 : 카드 복합결제 구분 처리 */
				card_settle_case ='$card_settle_case',
				kcp_escrow_point = '$kcp_escrow_point'
				/* 고정가상계좌 구분. 미사용 kcp_vbank_fix = '$vbank_fix' */
                ";
sql_query($sql);


// 김선용 201210 : 추천인할인내역 리포트 저장
if($_POST['od_recommend_off_sale']){
	// 응답지연등일때 새로고침 방지
	$chk_re = sql_fetch("select rc_pid from {$g4['yc4_rc_table']} where mb_id='{$member['mb_id']}' and od_id='$od_id' and rc_part='order' ");
	if(!$chk_re['rc_pid']){
		sql_query("insert into {$g4['yc4_rc_table']}
			set mb_id			= '{$member['mb_id']}',
				od_id			= '$od_id',
				rc_off_sale		= '{$_POST['od_recommend_off_sale']}',
				rc_part			= 'order',
				rc_datetime		= '{$g4['time_ymdhis']}' ");
	}
}

// 김선용 201207 : $od_gift_id = gift_id로 넘어오고, 여러개인경우 세미콜론구분자로 넘어옴
$od_gift_id = get_gift_check($_POST['od_amount'], $tmp_on_uid); // 배송료제외 상품가 기준
if($od_gift_id != '')
	sql_query("update {$g4['yc4_order_table']} set od_gift_id = '$od_gift_id' where od_id='$od_id' ");


// 장바구니 쇼핑에서 주문으로
// 신용카드로 주문하면서 신용카드 포인트 사용하지 않는다면 포인트 부여하지 않음
$sql_card_point = "";
if ($od_receipt_card > 0 &&  $default[de_card_point] == false) {
    $sql_card_point = " , ct_point = '0' ";
}
$sql = "update $g4[yc4_cart_table]
           set ct_status = '주문'
               $sql_card_point
         where on_uid = '$tmp_on_uid' ";
sql_query($sql);

// 원데이 상품일 경우 원데이 별도 재고 차감 2014-07-01 홍민기
$sql = sql_query("
	select
		a.it_id,a.ct_qty,
		b.st_dt,b.en_dt
	from
		".$g4['yc4_cart_table']." a
		left join
		yc4_oneday_sale_item b on a.it_id = b.it_id
	where
		a.on_uid = '".$tmp_on_uid."'
		and
		b.st_dt <= '".date('Ymd')."'
		and
		b.en_dt >= '".date('Ymd')."'
");


while($oneday_qty = sql_fetch_array($sql)){

	$one_day_order_cnt = sql_fetch("
		select
			sum(a.ct_qty) as cnt
		from
			yc4_cart a
			left join
			yc4_order b on a.on_uid = b.on_uid
		where
		a.it_id = '".$oneday_qty['it_id']."'
		and
		left(b.od_time,10) >= '".substr($oneday_qty['st_dt'],0,4).'-'.substr($oneday_qty['st_dt'],4,2).'-'.substr($oneday_qty['st_dt'],6,2)."'
		and
		left(b.od_time,10) <= '".substr($oneday_qty['en_dt'],0,4).'-'.substr($oneday_qty['en_dt'],4,2).'-'.substr($oneday_qty['en_dt'],6,2)."'
		and
		a.ct_status in ('준비','주문','배송')
	");

	$one_day_order_cnt = $one_day_order_cnt['cnt'];


	/*
	$oneday_updateq_qry = "
		update
			yc4_oneday_sale_item
		set
			order_cnt = order_cnt + ".(int)$oneday_qty['ct_qty']."
		where
			it_id = '".$oneday_qty['it_id']."'
	";
	*/

	$oneday_updateq_qry = "
		update
			yc4_oneday_sale_item
		set
			order_cnt = ".$one_day_order_cnt."
		where
			it_id = '".$oneday_qty['it_id']."'
	";


	sql_query($oneday_updateq_qry);
}

// 회원이면서 포인트를 사용했다면 포인트 테이블에 사용을 추가
if ($member[mb_id] && $od_receipt_point) {
    insert_point($member[mb_id], (-1) * $od_receipt_point, "주문번호 $od_id 결제");
}

$od_memo = nl2br(htmlspecialchars2(stripslashes($od_memo))) . "&nbsp;";


include_once("./ordermail1.inc.php");

//if ($od_settle_case == "무통장")
    include_once("./ordermail2.inc.php");



###### // 김선용 2014.03 : 테스트계정 sms 막음
if(check_test_id() == false || check_test_id() == true) // 둘다 sms 막음
	;
else
{
	// SMS BEGIN --------------------------------------------------------
	$receive_number = preg_replace("/[^0-9]/", "", $od_hp); // 수신자번호
	$send_number = preg_replace("/[^0-9]/", "", $default[de_sms_hp]); // 발신자번호

	$sms_contents = $default[de_sms_cont2];
	$sms_contents = preg_replace("/{이름}/", $od_name, $sms_contents);
	$sms_contents = preg_replace("/{보낸분}/", $od_name, $sms_contents);
	$sms_contents = preg_replace("/{받는분}/", $od_b_name, $sms_contents);
	$sms_contents = preg_replace("/{주문번호}/", $od_id, $sms_contents);
	$sms_contents = preg_replace("/{주문금액}/", number_format($ttotal_amount), $sms_contents);
	$sms_contents = preg_replace("/{회원아이디}/", $member[mb_id], $sms_contents);
	$sms_contents = preg_replace("/{회사명}/", $default[de_admin_company_name], $sms_contents);

	if ($default[de_sms_use2] && $receive_number)
	{
		if ($default[de_sms_use] == "xonda")
		{
			$usrdata1 = "주문서작성";

			define("_SMS_", TRUE);
			include "./sms.inc.php";
		}
		else if ($default[de_sms_use] == "icode")
		{
			include_once("$g4[path]/lib/icode.sms.lib.php");
			$SMS = new SMS; // SMS 연결
			$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
			$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
			$SMS->Send();
		}
	}
}
// SMS END   --------------------------------------------------------

/*
if($od_name != '오플닷컴 (OPLE.COM)' && $od_name != '오플닷컴-OPLE.COM-'){
	sales_report_cart($tmp_on_uid);
}
*/

// order_confirm 에서 사용하기 위해 tmp에 넣고
set_session('ss_temp_on_uid', $tmp_on_uid);

// ss_on_uid 기존자료 세션에서 제거
set_session('ss_on_uid', '');

//// 김선용 2014.03 : kcp 임시 작업중
if(check_test_id())
	goto_url("./orderconfirm-dev.php");
else
	goto_url("./orderconfirm.php");
?>
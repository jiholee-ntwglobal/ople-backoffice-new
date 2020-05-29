<?php
include_once("./_common.php");

// 장바구니가 비어있는가?
$tmp_on_uid = get_session('ss_on_uid');
if (get_cart_count($tmp_on_uid) == 0)// 장바구니에 담기
    alert("장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.", "./cart.php");


// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "sjsjin.shop_guard.php";

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
	# 원데이 체크 #
	$sql = "
		select 
			it_id,
			real_qty,
			multiplication,
			order_cnt			
		from 
			yc4_oneday_sale_item 
		where it_id = '".$row['it_id']."'
	";
	$oneday_data = sql_fetch($sql);
	if($oneday_data){ // 원데이는 제품 재고수량을 별도로 체크 
		$it_stock_qty = ($oneday_data['real_qty'] * $oneday_data['multiplication']) - ($oneday_data['order_cnt'] * $oneday_data['multiplication']);
	}else{
		// 상품에 대한 현재고수량
		$it_stock_qty = (int)get_it_stock_qty($row[it_id]);
	}
    // 장바구니 수량이 재고수량보다 많다면 오류
    if ($row[ct_qty] > $it_stock_qty)
        $error .= "$row[it_name] 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
}

if ($error != "")
{
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
	}else{
	    $od_temp_point = (float)str_replace(",", "", $od_temp_point);
		$od_receipt_point = 0;
	}
}

if ($od_temp_point)
{
    if ($member[mb_point] < $od_temp_point)
        alert("회원님의 포인트가 부족하여 포인트로 결제 할 수 없습니다.");
}

// 새로운 주문번호를 얻는다.
$od_id = get_new_od_id();


if(date('Ymd') >= '20140719' && date('Ymd') <= '20140721'){ // 7월 5일부터 7일까지 주문건만
	# 3만원 이상 주문시 관리자 메모 등록
	$od_total_amount = $od_receipt_bank + $od_receipt_card + $od_receipt_point; // 총 주문액
	if($od_total_amount >= 60000){
		$od_shop_memo = "6만원 이상 고객 에게 PB2 땅콩 버터 증정\n6만원 이상 고객 에게 PB2 땅콩 버터 증정\n6만원 이상 고객 에게 PB2 땅콩 버터 증정\n";
	}
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
                od_b_name         = '$od_b_name',
                od_b_tel          = '$od_b_tel',
                od_b_hp           = '$od_b_hp',
                od_b_zip1         = '$od_b_zip1',
                od_b_zip2         = '$od_b_zip2',
                od_b_addr1        = '$od_b_addr1',
                od_b_addr2        = '$od_b_addr2',
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
                od_shop_memo      = '".$od_shop_memo."',
                od_hope_date      = '$od_hope_date',
                od_time           = '$g4[time_ymdhis]',
                od_ip             = '$REMOTE_ADDR',
                od_settle_case    = '$od_settle_case',
				/* // 김선용 200908 : */
				od_b_jumin		= '$od_b_jumin',
				od_recommend_off_sale = '{$_POST['od_recommend_off_sale']}',
				od_ship = '{$_POST['od_ship']}'
                ";
sql_query($sql);


// 김선용 201211 : 배송지정보 주소록에 저장, 복수배송지자료에 od_id 저장, 상태 완료로 변경.
$os_sql_mb_id = "";
if($member['mb_id']){ // 회원
	if($_POST['od_ship'] == '1'){ // 복수배송
		$os_sql = sql_query("select * from {$g4['yc4_os_table']} where on_uid='$tmp_on_uid' and os_status='쇼핑' and mb_id='{$member['mb_id']}' order by os_pid ");
		while($os_row=sql_fetch_array($os_sql)){
			$chk_ma = sql_fetch("select ma_pid from {$g4['yc4_ma_table']} where mb_id='{$member['mb_id']}' and ma_addr1='{$os_row['os_addr1']}' and ma_addr2='{$os_row['os_addr2']}' ");
			if(!$chk_ma['ma_pid']){
				sql_query("insert into {$g4['yc4_ma_table']}
					set	mb_id		= '{$member['mb_id']}',
						ma_name		= '{$os_row['os_name']}',
						ma_tel		= '{$os_row['os_tel']}',
						ma_hp		= '{$os_row['os_hp']}',
						ma_zip1		= '{$os_row['os_zip1']}',
						ma_zip2		= '{$os_row['os_zip2']}',
						ma_addr1	= '{$os_row['os_addr1']}',
						ma_addr2	= '{$os_row['os_addr2']}',
						ma_datetime	= '{$g4['time_ymdhis']}' ");
			}
		}
	}else if($_POST['od_ship'] == '0'){ // 단수배송
		$chk_ma = sql_fetch("select ma_pid from {$g4['yc4_ma_table']} where mb_id='{$member['mb_id']}' and ma_addr1='$od_b_addr1' and ma_addr2='$od_b_addr2' ");
		if(!$chk_ma['ma_pid']){
			sql_query("insert into {$g4['yc4_ma_table']}
				set	mb_id		= '{$member['mb_id']}',
					ma_name		= '$od_b_name',
					ma_tel		= '$od_b_tel',
					ma_hp		= '$od_b_hp',
					ma_zip1		= '$od_b_zip1',
					ma_zip2		= '$od_b_zip2',
					ma_addr1	= '$od_b_addr1',
					ma_addr2	= '$od_b_addr2',
					ma_datetime	= '{$g4['time_ymdhis']}' ");
		}
	}
	$os_sql_mb_id = " and mb_id='{$member['mb_id']}' ";
}
if($_POST['od_ship'] == '1') // 복수배송만
	sql_query("update {$g4['yc4_os_table']} set od_id='$od_id', os_status='완료' where on_uid='$tmp_on_uid' and os_status='쇼핑' {$os_sql_mb_id} ");



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

// SMS BEGIN --------------------------------------------------------
$receive_number = preg_replace("/[^0-9]/", "", $od_hp); // 수신자번호
$send_number = preg_replace("/[^0-9]/", "", $default[de_sms_hp]); // 발신자번호

$sms_contents = $default[de_sms_cont2];
$sms_contents = preg_replace("/{이름}/", $od_name, $sms_contents);
$sms_contents = preg_replace("/{보낸분}/", $od_name, $sms_contents);
//$sms_contents = preg_replace("/{받는분}/", $od_b_name, $sms_contents); // 김선용 201211 : 복수배송으로 미사용
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
// SMS END   --------------------------------------------------------


// order_confirm 에서 사용하기 위해 tmp에 넣고
set_session('ss_temp_on_uid', $tmp_on_uid);

// ss_on_uid 기존자료 세션에서 제거
set_session('ss_on_uid', '');

goto_url("./orderconfirm.php");
?>

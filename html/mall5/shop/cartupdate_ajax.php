<?php
header("Content-type: text/xml;charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

include_once("./_common.php");

// 브라우저에서 쿠키를 허용하지 않은 경우라고 볼 수 있음.
$tmp_on_uid = get_session('ss_on_uid');
if (!$tmp_on_uid)
{
    $error_msg = "더 이상 작업을 진행할 수 없습니다.\n\n브라우저의 쿠키 허용을 사용하지 않음으로 설정한것 같습니다.\n\n브라우저의 인터넷 옵션에서 쿠키 허용을 사용으로 설정해 주십시오.\n\n그래도 진행이 되지 않는다면 쇼핑몰 운영자에게 문의 바랍니다.";
	$error_flag = true;
}

// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "sjsjin.shop_guard.php";


// 레벨(권한)이 상품구입 권한보다 작다면 상품을 구입할 수 없음.
if ($member[mb_level] < $default[de_level_sell] && !$error_flag)
{
    $error_msg = "상품을 구입할 수 있는 권한이 없습니다.";
	$error_flag = true;
}


$it_id = $_POST['it_id'] = (int)$_POST['it_id'];
$ct_qty = $_POST['ct_qty'] = (int)$_POST['ct_qty'];

if (!$it_id && !$error_flag){
    $error_msg = "장바구니에 담을 상품을 선택하여 주십시오.";
	$error_flag = true;
}

if ((!$ct_qty || $ct_qty < 1) && !$error_flag){
    $error_msg = "수량은 1 이상 입력해 주십시오.";
	$error_flag = true;
}

// 김선용 201208 :
if($_POST['it_order_onetime_limit_cnt'] && $_POST['it_order_onetime_limit_cnt'] < $ct_qty && !$error_flag){
	$error_msg = "이 상품의 1회 최대구매수량은 {$_POST['it_order_onetime_limit_cnt']} 개 입니다.";
	$error_flag = true;
}


// 비회원가격과 회원가격이 다르다면
if (!$is_member && $default[de_different_msg] && !$error_flag)
{
    $sql = " select it_amount, it_amount2 from $g4[yc4_item_table] where it_id = '$_POST[it_id]' ";
    $row = sql_fetch($sql);
    if ($row[it_amount2] && $row[it_amount] != $row[it_amount2] && !$error_flag) {
        //alert("비회원가격과 회원가격이 다른 상품입니다. 로그인 후 구입하여 주십시오.", "$g4[bbs_path]/login.php?url=".urlencode("$g4[shop_path]/item.php?it_id=$_POST[it_id]"));
        $error_msg = "비회원가격과 회원가격이 다릅니다. 로그인 후 구입하여 주십시오.";
		$error_flag = true;
    }
}

//--------------------------------------------------------
//  재고 검사
//--------------------------------------------------------
// 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
$sql = " select SUM(ct_qty) as cnt from $g4[yc4_cart_table]
          where it_id = '$_POST[it_id]'
            and on_uid = '$tmp_on_uid'
            and ihappy_fg is null";
$row = sql_fetch($sql);
$sum_qty = $row[cnt];

# 원데이 체크 #
$sql = "
	select
		it_id,
		real_qty,
		multiplication,
		order_cnt
	from
		yc4_oneday_sale_item
	where it_id = '".$_POST['it_id']."'
	and '".date('Ymd')."' between st_dt and en_dt
";
$oneday_data = sql_fetch($sql);
if($oneday_data){ // 원데이는 제품 재고수량을 별도로 체크
	$it_stock_qty = ($oneday_data['real_qty'] * $oneday_data['multiplication']) - ($oneday_data['order_cnt'] * $oneday_data['multiplication']);
}else{

	// 재고 구함
    $it_stock_qty = get_it_stock_qty($_POST[it_id]);
}
if ($ct_qty + $sum_qty > $it_stock_qty  && !$error_flag)
{
    //alert("$it_name 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty) . " 개");
	$error_msg = "$it_name 의 재고수량이 부족합니다.\n\n";
	$error_flag = true;
}
//--------------------------------------------------------
// 포인트 사용하지 않는다면
if (!$config[cf_use_point]) { $_POST[it_point] = 0; }


// 김선용 201208 : 같은상품이 현재 장바구니에 존재할 경우 갯수만 증가
$chk2_sql = "select ct_id, ct_qty from {$g4['yc4_cart_table']}
	where on_uid='$tmp_on_uid' and it_id='$it_id'
	and it_opt1 = '{$_POST['it_opt1']}'
	and it_opt2 = '{$_POST['it_opt2']}'
	and it_opt3 = '{$_POST['it_opt3']}'
	and it_opt4 = '{$_POST['it_opt4']}'
	and it_opt5 = '{$_POST['it_opt5']}'
	and it_opt6 = '{$_POST['it_opt6']}'
	and ct_status='쇼핑'
	and ihappy_fg is null";
$chk2 = sql_fetch($chk2_sql);

$add_save_column = '';
if($_POST['it_id'] == $_SESSION['naver_ep_it_id']){
	$add_save_column = ", naver_fg='Y'";
}

if(!$error_flag){

	if($chk2['ct_id'])
	{
		// 김선용 201208 : 최대구매수량 한번더 확인
		if($_POST['it_order_onetime_limit_cnt'] && $_POST['it_order_onetime_limit_cnt'] < ($chk2['ct_qty'] + $ct_qty)){
			$error_msg = "이 상품의 1회 최대구매수량은 {$_POST['it_order_onetime_limit_cnt']} 개 입니다.";
			$error_flag = true;
		}

		if(!$error_flag){

			$sql = "update {$g4['yc4_cart_table']}
				set ct_qty = ct_qty+{$_POST['ct_qty']} {$add_save_column}
				where on_uid='$tmp_on_uid' and it_id='{$_POST['it_id']}'
				and it_opt1 = '{$_POST['it_opt1']}'
				and it_opt2 = '{$_POST['it_opt2']}'
				and it_opt3 = '{$_POST['it_opt3']}'
				and it_opt4 = '{$_POST['it_opt4']}'
				and it_opt5 = '{$_POST['it_opt5']}'
				and it_opt6 = '{$_POST['it_opt6']}'
				and ct_status='쇼핑'
				and ihappy_fg is null";
			sql_query($sql);

		}
	}
	else
	{

		if($_HOTDEAL_FG){
			$hotdeal_chk = sql_fetch("
				select
					count(*) as cnt
				from
					yc4_hotdeal_item
				where
					it_id = '".$_POST['it_id']."'
					and
					flag = 'Y'
			");
			if($hotdeal_chk['cnt'] > 0){
				$add_save_column .= ", ct_hotdeal_fg = 'Y'";
			}

		}

		// 장바구니에 Insert
		$sql = " insert $g4[yc4_cart_table]
					set on_uid       = '$tmp_on_uid',
						it_id        = '$_POST[it_id]',
						it_opt1      = '$_POST[it_opt1]',
						it_opt2      = '$_POST[it_opt2]',
						it_opt3      = '$_POST[it_opt3]',
						it_opt4      = '$_POST[it_opt4]',
						it_opt5      = '$_POST[it_opt5]',
						it_opt6      = '$_POST[it_opt6]',
						ct_status    = '쇼핑',
						ct_amount    = '$_POST[it_amount]',
						ct_point     = '$_POST[it_point]',
						ct_point_use = '0',
						ct_stock_use = '0',
						ct_qty       = '$_POST[ct_qty]',
						ct_time      = '{$g4['time_ymdhis']}',
						ct_ip        = '".getenv('REMOTE_ADDR')."',
						ct_mb_id = '{$member['mb_id']}'
						{$add_save_column}";
		sql_query($sql);


	}
}


echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
?><response>
	<result><![CDATA[<?php echo ($error_flag) ? 'failure' : 'success'; ?>]]></result>
	<error_msg><![CDATA[<?php echo $error_msg; ?>]]></error_msg>
</response>
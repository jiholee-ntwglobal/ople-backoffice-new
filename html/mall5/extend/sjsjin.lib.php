<?php

// 김선용 200904 : 상품입고 sms 발송(상품수정/상품리스트 공통)
function it_sms_send($it_id, $it_stock_qty)
{
	global $g4, $default;

	if(!$default){
		$default = sql_fetch(" select * from $g4[yc4_default_table] ");
	}
	
	if($it_id == '' || !$default['de_item_sms_auto_use']) return false; // 설정안됨

	// 09~21시 이외시간은 예약전송으로 처리 => 예약전송 미사용(예약시간 코드만 작성)
	$hour = (int)(date("H"));
	if($hour < 9 || $hour > 21) return false; //"시간해당안됨";

	$hour_arr = array(
		'0' => '9',
		'1' => '8',
		'2' => '7',
		'3' => '6',
		'4' => '5',
		'5' => '4',
		'6' => '3',
		'7' => '2',
		'8' => '1',
		'21' => '13',
		'22' => '12',
		'23' => '11',
	);
	if($hour < 9 || $hour > 21)
		$recive_time = date("YmdHis", strtotime("+{$hour_arr[$hour]} hour"));
	else
		$recive_time = "";


	$SMS = new SMS;
	$SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
	$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호는 쇼핑몰대표번호(일반전화)

	$sql = "select a.*, b.it_name, b.it_stock_qty
		from {$g4['item_sms_table']} a left join {$g4['yc4_item_table']} b on a.it_id=b.it_id
		where a.it_id='$it_id' and a.ts_send=0 order by a.ts_id";

	if(trim($default['de_item_sms_msg']) != '') // 입고 sms통보메세지 존재
	{
		$result = sql_query($sql);
		while($row=sql_fetch_array($result))
		{
			// 재고확인 여부는 수정전 자료를 기준으로 한다.(수정전 자료상품을 기준으로 입고통보 신청을 했으므로)
			// 기존재고는 확인할필요없음. 신청시 품절상태만 신청가능하므로..
			/*
			if($row['it_optcnt_use'] && $row['ts_opt_index'] && $it_opt1 != '') // 옵션재고
			{
				$opt_new_arr = explode("\n", $it_opt1); // 수정 옵션재고
				$new_chk = explode(";", $opt_new_arr[$row['ts_opt_index']]);
				if(count($new_chk) == 3 && $new_chk[2] > 0)
				{
					// 옵션명도 일부포함
					$sms_it_name = cut_str($row['it_name'], 10, '..');
					$sms_it_name .= "(".cut_str($new_chk[0], 10, '..').")";
				}
				else if(count($new_chk) == 2 && $new_chk[1] > 0)
				{
					// 옵션명도 일부포함
					$sms_it_name = cut_str($row['it_name'], 10, '..');
					$sms_it_name .= "(".cut_str($new_chk[0], 10, '..').")";
				}
			}
			*/
			//if(!$row['it_optcnt_use'] && $row['it_stock_qty'] <= 0 && $it_stock_qty > 0)
			# SMS 미발송 건이 있다면 보낸다 2014-06-02 홍민기 #
			$sms_no_send_cnt = 0;
			$sms_no_send_cnt_qry = mysql_fetch_assoc(mysql_query("
				select 
					count(b.ts_send) as cnt
				from 
					yc4_item a
					left outer join 
					yc4_add_item_sms b on a.it_id = b.it_id
				where
					a.it_stock_qty > 0
					and
					b.ts_send = 0
				  and
				  a.it_id = '".$it_id."'
				group by a.it_id
			"));
			$sms_no_send_cnt = $sms_no_send_cnt_qry[cnt];

			if($sms_no_send_cnt>0 && $_GET['mode'] == 'item_sms'){ // 자동 문자 발송 크론에서만 작동
				$sms_it_name = cut_str(preg_replace("/\"|\'/", "", $row['it_name']), 16, '..');
			}elseif($row['it_stock_qty'] <= 0 && $it_stock_qty > 0)
				$sms_it_name = cut_str(preg_replace("/\"|\'/", "", $row['it_name']), 16, '..');
			else
				continue;

			$receive_number = preg_replace("/[^0-9]/", "", $row['ts_hp']); // 수신자번호
			$sms_contents = trim($default['de_item_sms_msg']);
			$sms_contents = preg_replace("/{이름}/", $row['ts_name'], $sms_contents);
			$sms_contents = preg_replace("/{상품명}/", $sms_it_name, $sms_contents);
			//$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), $recive_time);
			$SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
			sql_query("update {$g4['item_sms_table']} set ts_send=1, ts_send_time='{$g4['time_ymdhis']}' where ts_id='{$row['ts_id']}'");
		}
		$SMS->Send();
		$SMS->Init();
	}
	//return true;
}

function get_item_sms_count($mb_id)
{
	global $g4;
	if($mb_id == '') return;

	return sql_fetch("select count(ts_id) as count from {$g4['item_sms_table']} where mb_id='$mb_id' and ts_send=0");
}

// 김선용 201210 : 추천인 검증 (할인가능여부)
function chk_recommend_order($mb_arr, $ca_id='')
{
	global $g4;

	if($mb_arr['mb_id'] == '') return false;

	$ca_sql = "";
	if($ca_id != '') $ca_sql = " and ca_id='$ca_id' ";
	$chk_date = false;
	$chk_order = false;

	// 가입일 : 20120915 부터
	if(strtotime(substr($mb_arr['mb_datetime'],0,10)) > strtotime('2012-09-14'))
		$chk_date = true;

	// 구매검증
	$ct_sql = sql_query("select ct_id from {$g4['yc4_cart_table']} a left join {$g4['yc4_item_table']} b on a.it_id=b.it_id where a.ct_mb_id='{$mb_arr['mb_id']}' and ct_status in ('주문', '준비', '배송', '완료') {$ca_sql} ");
	while($ct=sql_fetch_array($ct_sql)){
		if($ct['ct_id']){
			$chk_order = true;
			break;
		}
	}
	if($chk_date && !$chk_order)
		return true;
	else
		return false;
}

// 김선용 201210 : 복수배송일경우 배송지/상품정보
function get_fui_ship_item($on_uid, $mb_id='', $cost=0, $del_sw=true, $email=false)
{
	global $g4, $default;
	if($on_uid == '') return;

	$mb_id_sql = "";
	if($mb_id != '') $mb_id_sql = " and mb_id='$mb_id' ";

	$return = array();
	// 등록된 배송지/상품 출력
	$return[] = "
	<fieldset style='padding:5px;'>
		<legend>※ 복수배송지 정보"; if($email) $return[] .= " (배송상품정보는 홈페이지의 주문조회에서 확인하십시오.)"; if($del_sw) $return[] .= " (배송지정보나 설정수량이 틀린경우 삭제후에 다시 등록하십시오.)"; $return[] .= "</legend>

		<table border=2 cellspacing=0 cellpadding=2 align='center' bordercolor='#95A3AC'  class='state_table'  id=chk_ship width='100%'>
		<tr align=center>";
		if($del_sw) $return[] .= "<td class=yalign_head width=40></td>";
		$return[] .= "
			<td class=yalign_head width=90>보내는사람</td>
			<td class=yalign_head width=70>이름</td>
			<td class=yalign_head width=90>휴대전화<br/>기타전화</td>
			<td class=yalign_head>주소</td>";
		if(!$email){
			$return[] .= "<td class=yalign_head width=65>배송상품</td>";
		}
		$return[] .= "</tr>";
	$os_sql = sql_query("select * from {$g4['yc4_os_table']} where on_uid='$on_uid' /*and os_status='쇼핑'*/ {$mb_id_sql} order by os_pid ");
	$os_count = 0;
	for($k=0; $row=sql_fetch_array($os_sql); $k++)
	{
		// 주문이메일 보안처리
		if($email){
			$row['os_name'] = substr_replace($row['os_name'], '**', 3);
			$row['os_hp']	= substr_replace($row['os_hp'], '*******', 0, -4);
			$row['os_tel']	= substr_replace($row['os_tel'], '*******', 0, -4);
			$row['os_zip2'] = substr_replace($row['os_zip2'], '***', 0);
			$row['os_addr2'] = substr_replace($row['os_addr2'], '************', 0);
		}
		$return[] = "
			<tr onmouseover=\"this.style.backgroundColor='#dddddd';\" bgcolor='#FFFFFF' onmouseout=\"this.style.backgroundColor='#FFFFFF';\">";
			if($del_sw) $return[] .= "<td class=yalign_list><a href=\"javascript:;\" onclick=\"del_ship('{$row['os_pid']}', '$on_uid', '$mb_id');\" title='삭제하기'>삭제</a></td>";
			$return[] .= "<td class=yalign_list>{$row['os_post_name']}</td>
				<td class=yalign_list>{$row['os_name']}</td>
				<td class=yalign_list>{$row['os_hp']}<br/>{$row['os_tel']}</td>
				<td class=yalign_list>({$row['os_zip1']}-{$row['os_zip2']}) {$row['os_addr1']} {$row['os_addr2']}</td>";
				if(!$email){
					$return[] .= "<td class=yalign_list><a href=\"javascript:;\" onclick=\"view_ship_item('{$row['os_pid']}');\" title='배송상품보기'>상품보기</a></td>";
				}
		$return[] .= "</tr>";
		if($row['os_invoice'] && $row['os_dl_id']){
			$dl = sql_fetch("select * from $g4[yc4_delivery_table] where dl_id = '{$row['os_dl_id']}' ");
			if (strpos($dl['dl_url'], "=")) $invoice = $row['os_invoice'];
			$return[] .= "<tr><td colspan=10  class=yalign_list>└ 배송회사 : {$dl['dl_company']}&nbsp;&nbsp;[<a href='{$dl['dl_url']}{$invoice}' target=_new>배송조회하기</a>],&nbsp;&nbsp;운송장번호 : {$row['os_invoice']},&nbsp;&nbsp;배송일시 : {$row['os_invoice_time']}</td></tr>";
		}
		$os_count++;
	}
	if($os_count > $default['de_order_ship_multi_default'] && $cost) $return[] = "<tr><td height=30 colspan=10><b>※ 추가배송비 : ".nf($cost * ($os_count - $default['de_order_ship_multi_default']))."원</b></td></tr> <input type=hidden id=add_send_cost value='".($cost * ($os_count - $default['de_order_ship_multi_default']))."' />";
	$return[] = "</table></fieldset>";

	if(!$email){
		$return[] = "
		<div id=_dis_view_item_ style='display:none; position:absolute; z-index:999; width:600px; height:500px; background-color:white; border:1px solid black; border-collapse:collapse; padding:10px; overflow:auto;'></div>

			<script type='text/javascript'>
			function view_ship_item(os_pid)
			{
				$.ajax({
					type: 'POST',
					url: g4_path+'/shop/orderform.jquery.php',
					data: { 's_type' : 'view', 'os_pid' : os_pid, 'tmp_on_uid' : '$on_uid' },
					cache: false,
					async: false,
					success: function(result) {
						var id = '#_dis_view_item_';
						$(id).html(result);
						$(id).css({
							// window.width 사용불가. css에서 강제하기 때문에 가로크기가 다르게 잡힘. 콘테이너 개체로 가로크기를 기준함
							//'left': (($(window).width() - $(id).width())/2 + $(window).scrollLeft()) + 'px',
							'left': (($('#container').width() - $(id).width())/2 + $(window).scrollLeft()) + 'px',
							'top': (($(window).height() - $(id).height())/2 + $(window).scrollTop()) + 'px'
						});//.fadeIn();
						$(id).show();
					}
				});
			}
			</script>
		";
	}

	if(!$k) return '#no_ship';
	return implode("", $return);
}

// 김선용 2014.04 : kcp 고정가상계좌 발급 프로세스
function get_kcp_uniqkey()
{
	global $g4;

    sql_query(" LOCK TABLES $g4[member_table] READ, $g4[yc4_order_table] WRITE ", FALSE);

    $date = date("Ymd"); // 20140101 형식
    $sql = " select max(mb_kcp_vcnt_code) as max_code from $g4[member_table] where left(mb_kcp_vcnt_code, 8)='$date' ";
    $row = sql_fetch($sql);
    $kcp_key = $row[max_code];
    if ($kcp_key == 0)
        $kcp_key = 1;
    else
    {
        $kcp_key = (int)substr($kcp_key, -12);
        $kcp_key++;
    }
    $kcp_key = $date . substr("00000000000000" . $kcp_key, -12);

    sql_query(" UNLOCK TABLES ", FALSE);

    return $kcp_key;
}

// 김선용 2014.04 : 작업시 테스트계정 사용여부
function check_test_id()
{
	global $g4, $member;
	$chk = false; // 테스트작업시 true, 아닐시 false

	if($chk){
		if(in_array($member['mb_id'], array('devtest', 'eqbs', 'snotzman', 'eunhye82')))
			return true;
		else
			return false;
	}
	else
		return false;

}
?>
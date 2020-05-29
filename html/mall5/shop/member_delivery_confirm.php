<?php
# 4색 이벤트 종료되면 member_delivery_confirm2.php 파일로 원복할것
include_once("./_common.php");
//alert("프로그램 점검 중입니다. 나중에 다시 이용하십시오.\\n\\n이용에 불편을 드려 죄송합니다.");
if(!$is_member) alert("회원전용 메뉴 입니다.");
if(!trim(get_session('ss_token')) || get_session('ss_token') != $_POST['ss_token']) alert("정상적인 접근이 아닙니다.");
if(!get_member($member['mb_id'], "mb_id")) alert("존재하지 않는 회원입니다.\\n\\n정상적으로 사용하십시오.");
if(trim($_POST['od_id']) == '' || trim($_POST['on_uid']) == '') alert("필수값이 없습니다.");

// 주문상품 지급내역 존재 확인
$order_chk = false;
$kcp_point_chk = false;




# 4색 이벤트 이벤트 코드 배열 #
$color_event = array('1412579392','1412579377','1412579346','1412579326');

foreach($color_event as $val){
	$color_event_in_qry .= ($color_event_in_qry ? ", ":"")."'". $val ."'";
}


// 마스터카드 프로모션 KB VCN 포인트 적립 2014-11-07 홍민기
$master_card_event_point_data = sql_fetch("
	select point,event_code from master_card_event where od_id = '".$_POST['od_id']."' and point_complate_fg = 'n' and point > 0
");


$master_card_point = $master_card_event_point_data['point'];
if($master_card_point>0){
	$master_card_point_fg = true; // true 이면 다른 포인트 중복적립 x
}


// 새로고침 방지, 주문서, 주문상품, 송장번호가 있는 자료, 상품개별 상태가 배송인 자료
$sql = sql_query("select a.*, b.od_recommend_off_sale,b.od_time from {$g4['yc4_cart_table']} a left join {$g4['yc4_order_table']} b on a.on_uid=b.on_uid where a.ct_status='배송' and a.on_uid='{$_POST['on_uid']}' and b.mb_id='{$member['mb_id']}' and b.od_id='{$_POST['od_id']}' and b.od_invoice<>'' order by a.ct_id");

for($k=0; $row=sql_fetch_array($sql); $k++)
{

	// 재고/적립금이 처리되지 않은 경우만 (새로고침등의 오류 방지)
	if(!$row['ct_stock_use'] || !$row['ct_point_use'])
	{
		// 재고처리
		$stock_use = $row['ct_stock_use'];
		if(!$row['ct_stock_use'])
		{
			$stock_use = 1;
			$sql2 ="update $g4[yc4_item_table] set it_stock_qty=it_stock_qty-'{$row['ct_qty']}' where it_id='{$row['it_id']}'";
			sql_query($sql2);
		}

		// 4색 이벤트에 해당되는 상품인지 체크 #
		$od_time = substr($row['od_time'],0,10);
		$od_time = str_replace('-','',$od_time);

		$color_event_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_event_item_table']." where it_id = '".$row['it_id']."' and ev_id in (".$color_event_in_qry.")");
		if($color_event_chk['cnt'] > 0 && $od_time >= '20141007' && $od_time <= '20141031'){ // 4색이벤트에 해당하는 상품이 있다면

			if(!$color_event_point){
				$color_event_point = 0;
			}
			# 구매금액 로드
			//$4c_item_amount = sql_fetch("select (ct_qty * ct_amount * 0.1) as 4c_point from ".$g4['yc4_cart_table']." where on_uid = '".$_POST['on_uid']."'");
			$color_event_item_amount = round($row['ct_qty'] * $row['ct_amount'] / 10);
			$color_event_point += $color_event_item_amount;

		}else{ // 4색 이벤트 제외 상품은 정상처리
			// 포인트 지급
			$point_use = $row['ct_point_use'];
			if($row['ct_point'] && !$row['ct_point_use'] && !$master_card_point_fg)
			{
				$order_chk = true; // 포인트 적립 확인
				$point_use = 1;
				insert_point($member['mb_id'], ($row['ct_point']*$row['ct_qty']), "주문번호 $od_id({$row['ct_id']}) 구매상품 회원직접 수령확인");
			}
		}


		// 김선용 201209 : 추천인 적립률
		// 추천인 적립금 지급/회수 (1단위 올림)
		// 추천인 적립기능 사용시

		if(floatval($default['de_recom_point']) && $member['mb_recommend'] != '' && !$master_card_point_fg)
		{
			$chk_mb = get_member($member['mb_recommend'], "mb_id"); // 실제 회원이 존재하면
			if($chk_mb['mb_id'] && $row['od_recommend_off_sale']) {
				$it = sql_fetch("select ca_id from {$g4['yc4_item_table']} where it_id='{$row['it_id']}' ");
				// 할인분류에 속한경우만
				if($default['de_recom_off_ca_id'] == '' || $default['de_recom_off_ca_id'] == substr($it['ca_id'], 0, strlen($default['de_recom_off_ca_id'])))
				{
					$per_point = ceil(($row['ct_amount'] * $row['ct_qty']) * ($default['de_recom_point'] / 100));
					insert_point($member['mb_recommend'], $per_point, "추천인({$member['mb_id']}) 상품구매 [주문번호:$od_id({$row['ct_id']})] 지급");

					// 김선용 201210 : 피추천인 적립내역 리포트 저장
					// 응답지연등일때 새로고침 방지
					$chk_re = sql_fetch("select rc_pid from {$g4['yc4_rc_table']} where mb_id='{$chk_mb['mb_id']}' and od_id='$od_id' and rc_part='point' ");
					if(!$chk_re['rc_pid']){
						sql_query("insert into {$g4['yc4_rc_table']}
							set mb_id			= '{$chk_mb['mb_id']}',
								od_id			= '$od_id',
								rc_save_point	= '$per_point',
								rc_part			= 'point',
								rc_datetime		= '{$g4['time_ymdhis']}' ");
					}
				}
			}
		}


		// 히스토리에 남김
		// 히스토리에 남길때는 작업|시간|IP|그리고 나머지 자료
		$ct_history="\n{$row['ct_status']}|$now|{$_SERVER['REMOTE_ADDR']}";
		$sql3 = " update $g4[yc4_cart_table]
					set ct_point_use  = '$point_use',
						ct_stock_use  = '$stock_use',
						ct_status     = '완료',
						ct_history    = CONCAT(ct_history,'$ct_history')
				  where on_uid = '$on_uid'
					and ct_id  = '{$row['ct_id']}' ";
		sql_query($sql3);
	}
}

if($color_event_point){
	$order_chk = true; // 포인트 적립 확인
	insert_point($member['mb_id'], $color_event_point, "주문번호 4색이벤트($od_id) 포인트 지급");
}

// 김선용 2014.03 : kcp 가상계좌 추가 포인트 처리
$od_chk = sql_fetch("select mb_id, od_receipt_bank, od_receipt_card, kcp_escrow_point from {$g4['yc4_order_table']} where od_id='$od_id' and kcp_escrow_point_use=0 and od_settle_case='가상계좌' ");
if($od_chk['kcp_escrow_point'] && $od_chk['od_receipt_bank'])
{
//	if($od_chk['od_receipt_card'])
//		$kcp_point = ($od_chk['od_receipt_card'] * $od_chk['kcp_escrow_point']) / 100;
//	else if($od_chk['od_receipt_bank'])
	
	if($od_chk['od_receipt_bank'] && point_pay_chk($on_uid)){
		$kcp_point = ($od_chk['od_receipt_bank'] * $od_chk['kcp_escrow_point']) / 100;
		insert_point($member['mb_id'], $kcp_point, "주문번호 $od_id (KCP 가상계좌결제 추가 포인트 적립 이벤트)");

		sql_query("update {$g4['yc4_order_table']}
			set od_shop_memo=concat(od_shop_memo, '\\n', 'KCP 가상계좌결제 추가 포인트 적립 이벤트 적립'),
				kcp_escrow_point_use=1
			where od_id='$od_id' ");

		$kcp_point_chk = true;
	}
}


if($master_card_point_fg){
	insert_point($member['mb_id'], $master_card_point, "주문번호 ".$od_id." (".$master_card_event_point_data['event_code']." 포인트 추가적립 이벤트)");
	$order_chk = true;
	sql_query("update master_card_event set point_complate_fg = 'y' where od_id = '".$od_id."'");
	sql_query("update {$g4['yc4_order_table']}
			set od_shop_memo=concat(od_shop_memo, '\\n', '마스타 카드 프로모션 ".$master_card_event_point_data['event_code']." 포인트 적립 완료(".$master_card_point."점)')
			where od_id='$od_id' ");
}

$sam_card_chk = sql_fetch("
	select
		return_point
	from
		yc4_card_return_point
	where
			od_id = '".$od_id."'
		and on_uid = '".$on_uid."'
		and cp_dt is null
");

# 삼성카드 지급 포인트가 있다면 적립 #
if($sam_card_chk['return_point'] > 0){

	insert_point($member['mb_id'], $sam_card_chk['return_point'], "주문번호 ".$od_id." (삼성마스타카드 결제 5% 적립 이벤트)");
	sql_query("update {$g4['yc4_order_table']}
			set od_shop_memo=concat(od_shop_memo, '\\n', '삼성마스타카드 결제 % 적립 완료(".$sam_card_chk['return_point']."점)')
			where od_id='$od_id' ");
	sql_query("
		update
			yc4_card_return_point
		set
			cp_fg = 'Y',
			cp_dt = '".$g4['time_ymdhis']."'
		where
				od_id = '".$od_id."'
			and on_uid = '".$on_uid."'
			and cp_dt is null

	");
}

# 신한 아이해피 적립 포인트가 있다면 적립 #
$shinhan_ihappy_chk = sql_fetch("
    select cd_return_point from yc4_card_history_sh_ihappy where od_id = '".$od_id."' and cd_point_complete_yn = 'N' and cd_return_point > 0
");

if($shinhan_ihappy_chk['cd_return_point']>0){
    insert_point($member['mb_id'], $shinhan_ihappy_chk['cd_return_point'], "주문번호 ".$od_id." (신한 아이행복카드 첫 결제 10% 적립)");
    sql_query("update {$g4['yc4_order_table']}
			set od_shop_memo=concat(od_shop_memo, '\\n', '신한 아이행복카드 첫 결제 10% 적립 완료(".$shinhan_ihappy_chk['cd_return_point']."점) ".$g4['time_ymdhis']."')
			where od_id='$od_id' ");
    sql_query("
		update
			yc4_card_history_sh_ihappy
		set
			cd_point_complete_yn = 'Y'
		where
				od_id = '".$od_id."'
	");
}

# 하나카드 첫 결제 적립 #
if(hanacard_order_confirm($od_id)){
    $order_chk = true;
}


if($_POST['refer']){
	$return_url = $_POST['refer'];
}else{
	$return_url = "orderinquiryview.php?od_id=$od_id&on_uid=$on_uid";
}
	if($order_chk)
		alert("포인트 적립이 정상적으로 처리되었습니다.\\n\\n상세내역은 \'포인트내역\'을 확인 하십시오.", $return_url);
	else
		alert("해당 주문서의 구매상품중 포인트 적립과 관련한 내역이 없거나,\\n\\n상품이 배송중이 아니거나, 이미 포인트 적립이 처리된 주문서 입니다..\\n\\n주문내역을 확인해 주십시오.", $return_url);

?>
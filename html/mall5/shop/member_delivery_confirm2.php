<?php
include_once("./_common.php");
if(date('Ymd') > '20141031'){
	include $g4['full_shop_path'].'/member_delivery_confirm2.php';
	exit;
}
//alert("프로그램 점검 중입니다. 나중에 다시 이용하십시오.\\n\\n이용에 불편을 드려 죄송합니다.");
if(!$is_member) alert("회원전용 메뉴 입니다.");
if(!trim(get_session('ss_token')) || get_session('ss_token') != $_POST['ss_token']) alert("정상적인 접근이 아닙니다.");
if(!get_member($member['mb_id'], "mb_id")) alert("존재하지 않는 회원입니다.\\n\\n정상적으로 사용하십시오.");
if(trim($_POST['od_id']) == '' || trim($_POST['on_uid']) == '') alert("필수값이 없습니다.");

// 주문상품 지급내역 존재 확인
$order_chk = false;
$kcp_point_chk = false;

// 새로고침 방지, 주문서, 주문상품, 송장번호가 있는 자료, 상품개별 상태가 배송인 자료
$sql = sql_query("select a.*, b.od_recommend_off_sale from {$g4['yc4_cart_table']} a left join {$g4['yc4_order_table']} b on a.on_uid=b.on_uid where a.ct_status='배송' and a.on_uid='{$_POST['on_uid']}' and b.mb_id='{$member['mb_id']}' and b.od_id='{$_POST['od_id']}' and b.od_invoice<>'' order by a.ct_id");
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

		// 포인트 지급
		$point_use = $row['ct_point_use'];
		if($row['ct_point'] && !$row['ct_point_use'])
		{
			$order_chk = true; // 포인트 적립 확인
			$point_use = 1;
			insert_point($member['mb_id'], ($row['ct_point']*$row['ct_qty']), "주문번호 $od_id({$row['ct_id']}) 구매상품 회원직접 수령확인");
		}


		// 김선용 201209 : 추천인 적립률
		// 추천인 적립금 지급/회수 (1단위 올림)
		// 추천인 적립기능 사용시

		if(floatval($default['de_recom_point']) && $member['mb_recommend'] != '')
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


// 김선용 2014.03 : kcp 가상계좌 추가 포인트 처리
$od_chk = sql_fetch("select mb_id, od_receipt_bank, od_receipt_card, kcp_escrow_point from {$g4['yc4_order_table']} where od_id='$od_id' and kcp_escrow_point_use=0 and od_settle_case='가상계좌' ");
if($od_chk['kcp_escrow_point'] && $od_chk['od_receipt_bank'])
{
//	if($od_chk['od_receipt_card'])
//		$kcp_point = ($od_chk['od_receipt_card'] * $od_chk['kcp_escrow_point']) / 100;
//	else if($od_chk['od_receipt_bank'])
	if($od_chk['od_receipt_bank']){
		$kcp_point = ($od_chk['od_receipt_bank'] * $od_chk['kcp_escrow_point']) / 100;
		insert_point($member['mb_id'], $kcp_point, "주문번호 $od_id (KCP 가상계좌결제 추가 포인트 적립 이벤트)");

		sql_query("update {$g4['yc4_order_table']}
			set od_shop_memo=concat(od_shop_memo, '\\n', 'KCP 가상계좌결제 추가 포인트 적립 이벤트 적립'),
				kcp_escrow_point_use=1
			where od_id='$od_id' ");

		$kcp_point_chk = true;
	}
}


if($order_chk)
	alert("포인트 적립이 정상적으로 처리되었습니다.\\n\\n상세내역은 \'포인트내역\'을 확인 하십시오.", "orderinquiryview.php?od_id=$od_id&on_uid=$on_uid");
else
	alert("해당 주문서의 구매상품중 포인트 적립과 관련한 내역이 없거나,\\n\\n상품이 배송중이 아니거나, 이미 포인트 적립이 처리된 주문서 입니다..\\n\\n주문내역을 확인해 주십시오.", "orderinquiryview.php?od_id=$od_id&on_uid=$on_uid");
?>
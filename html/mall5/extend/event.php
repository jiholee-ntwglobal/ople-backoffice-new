<?php
/**
 * Created by Eclipse
 * User: kyung-in
 * Date: 2016.04.22
 * file: extend/event.php
 */

/**2017 추석 선결제 포인트 주문 취소 처리
 * @param $od_id
 * @param $mb_id
 * @return bool
 */
function prepay_17_chu_ev_cancel($od_id, $mb_id){
	//회원
	if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
		return false;
	}
	//주문서
	if(!$od_id || trim($od_id)==''){
		return false;
	}
	$od_id = sql_safe_query($od_id);
	$ev		= sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='prepay_17_chu' AND ev_data_type='od_id' AND value1='".$od_id."' AND value6 IS NULL");
	if(!$ev['uid']){
		return false;
	}
	// 주문서 메모
	sql_query("UPDATE yc4_event_data SET value6='CANCEL' WHERE uid='".$ev['uid']."'");
	sql_query("UPDATE yc4_order SET od_shop_memo=CONCAT(od_shop_memo,'\\n','추석 선결제상품 자동적립 취소') WHERE od_id='".$od_id."'");

	return true;
}

/**2017-06-21 강경인 작업
 * 하나카드 100불이상 10불 할인 이벤트 결재 실패 취소처리
 * ev_code      : hana_17_100_10_dc
 * ev_data_type : 'od_id'
 * value1       : od_id
 * value2       : mb_id
 * value3       : amount_usd
 * value4       : dc_amount
 * value5       : cancel_fg
 * @param $od_id
 * @return bool
 */
function hana_17_100_10_dc_cancel($od_id){
	$od_id	= sql_safe_query($od_id);
	$ev_chk	= sql_fetch("SELECT uid, value4 AS dc_amount, value3 AS dc_usd FROM yc4_event_data WHERE ev_code='hana_17_100_10_dc' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value5 IS NULL");
	if(!$ev_chk['uid']){
		return false;
	}
	if($ev_chk['dc_amount'] < 1){
		return false;
	}
	// 이벤트 데이터 변경 처리
	sql_query("UPDATE yc4_event_data SET value5='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");
	// 주문서 정보 변경 처리
	sql_query("UPDATE yc4_order SET od_dc_amount=CAST(od_dc_amount AS int) - " . (int)$ev_chk['dc_amount'] . ", od_shop_memo=CONCAT(od_shop_memo, '\\n', '하나카드 100달러 이상 10달러 할인 관리자취소 초기화') WHERE od_id='" . $od_id . "'");

	return true;
}

/**주문취소 재고 복구 처리(order_cancel 하단 + 관리자 취소처리)
 * ev_code		= nordic_1704_bogo
 * ev_data_type	= item_info		// od_id
 * value1		= it_id			// od_id
 * value2		= amount		// mb_id
 * value3		= sales_qty		// it_id
 * value4		= qty			// flag(cancel)
 * @param $od_id
 * @return bool
 */
function nordic_1704_bogo_cancel($od_id){
	$item_chk	= sql_query("SELECT value3 AS it_id, uid FROM yc4_event_data WHERE ev_code='nordic_1704_bogo' AND ev_data_type='od_id' AND value1='" . $od_id . "'");
	if(!$item_chk){
		return false;
	}
	while($row = sql_fetch_array($item_chk)){
		if(!$row['it_id']){
			continue;
		}
		// 이벤트 데이터 변경
//		sql_query("UPDATE yc4_event_data SET valueWHERE uid='" . $row['uid'] . "'");
		sql_query("UPDATE yc4_event_data SET value4='CANCEL' WHERE uid='" . $row['uid'] . "'");

		// 이벤트 상품 정보 변경
		sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - 1 WHERE ev_code='nordic_1704_bogo' AND ev_data_type='item_info' AND value1='" . $row['it_id'] . "'");
	}
}
## 노르딕 201704 1+1 특가이벤트 끝

/**노르딕 빅이벤트 사은품 취소 처리
 * @param $od_id
 * @return bool
 */
function nordic_1704_gift_cancel($od_id){

	if(!$od_id || trim($od_id) == ""){
		return false;
	}
	$od_id	= sql_safe_query($od_id);
	$ev_chk	= sql_fetch("SELECT uid, value3 as it_id1, value4 AS it_id2 FROM yc4_event_data WHERE ev_code='nordic_17_big_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value5 IS NULL");
	if(!$ev_chk['uid']) {
		return false;
	}
	$it_id1	= sql_safe_query($ev_chk['it_id1']);
	$it_id2	= sql_safe_query($ev_chk['it_id2']);
	// 사은품 수량정보 변경
	if($it_id1 != "") {
		sql_query("
			UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - 1 WHERE ev_code='nordic_17_big_gift' AND ev_data_type='gift_info' AND value2='" . $it_id1 . "'
		");
	}
	if($it_id2 != "") {
		sql_query("
			UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - 1 WHERE ev_code='nordic_17_big_gift' AND ev_data_type='gift_info' AND value2='" . $it_id2 . "'
		");
	}
	// 이벤트 데이터 변경
	sql_query("
		UPDATE yc4_event_data SET value5='CANCEL' WHERE uid='".$ev_chk['uid']."'
	");
	// 주문서에 이벤트 메모 추가
	sql_query("
		UPDATE yc4_order SET od_status_update_dt = NOW(), od_shop_memo = concat(od_shop_memo,'\\n','201704 노르딕 사은품 취소') WHERE od_id='".$od_id."'
	");

	return true;
}

/**원큐패스 유입 주문서 포인트 적립 이벤트 취소 처리
 * 카드결제 완료시 적용 이벤트이므로 취소처리는 관리자 페이지에만 연동
 * @param $od_id
 * @return bool
 */
function hana_1qpass_point_17_cancel($od_id){
	$od_id	= sql_safe_query($od_id);
	$ev_chk	= sql_fetch("SELECT uid, value4 AS point FROM yc4_event_data WHERE ev_code='hana_1qpass_point_17' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value7 IS NULL");
	if($ev_chk['point'] < 1){
		return false;
	}
	// 이벤트 정보 저장
	sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");
	// 주문서 메모 수정
	sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo, '\\n', '원큐패스 유입주문 5%(" . $ev_chk['point'] . " 점) 포인트 취소') WHERE od_id='" . $od_id . "'");
}

/**kb아이해피 첫구매 사은품 취소처리
 * @param $od_id
 * @return bool
 */
function kb_happy_17_sel_gift_cancel($od_id){
	$od_id	= sql_safe_query($od_id);
	// 이벤트 적용 내용 확인
	$ev_chk	= sql_fetch("SELECT uid, value4 AS it_id FROM yc4_event_data WHERE ev_code='kb_happy_17_sel_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value5 IS NULL");
	if($ev_chk['uid'] < 1){
		return false;
	}
	// 사은품 수량 정보 변경
	sql_query("UPDATE yc4_event_data SET value2 = CAST(value2 AS int) - 1 WHERE ev_code='kb_happy_17_sel_gift' AND ev_data_type='gift_info' AND value1='".$ev_chk['it_id']."'");
	// 이벤트 정보 변경
	sql_query("UPDATE yc4_event_data SET value5=NOW() WHERE uid='".$ev_chk['uid']."'");
	// 주문서 메모 추가
	sql_query("UPDATE yc4_order SET od_status_update_dt = NOW(), od_shop_memo = concat(od_shop_memo,'\\n','kb아이해피 선택사은품 취소') WHERE od_id='".$od_id."' AND ihappy_fg='k'");
	return true;
}

/**노르딕 신상품 이벤트 사은품 취소처리(결제 실패, 관리자페이지 취소처리)
 * @param $od_id
 * @return bool
 */
function nordic_nnnp_17_gift_cancel($od_id){
	$ev_chk	= sql_fetch("SELECT uid, value5 AS it_id FROM yc4_event_data WHERE ev_code='nordic_nnnp_17_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value6 IS NULL");
	if(!$ev_chk['uid']){
		return false;
	}
	// 이벤트 정보 취소처리
	sql_query("UPDATE yc4_event_data SET value6 = NOW() WHERE uid='".$ev_chk['uid']."'");
	// 사은품 수량 변경
	sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - 1 WHERE ev_code='nordic_nnnp_17_gift' AND ev_data_type='gift_info' AND value2='".$ev_chk['it_id']."'");
	// 주문서에 이벤트 메모 추가
	sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2017노르딕 신상품 이벤트 사은품 취소') WHERE od_id='".$od_id."'");
	return true;
}

/**2016년 출석체크 이벤트 사은품 지급초기화(관리자페이지)
 * @param $od_id
 * @return bool
 */
function attendance_16_gift_cancel($od_id){
	$od	= sql_fetch("SELECT mb_id, on_uid  FROM yc4_order WHERE od_id='" . $od_id . "'");
	// 비회원 제외
	if(!$od['mb_id']){
		return false;
	}
	$ev_chk	= sql_fetch("SELECT uid, value2 AS it_id FROM yc4_event_data WHERE ev_code='attendance_16_gift' AND ev_data_type='gift_info' AND value1='" . $od['mb_id'] . "' AND value3 IS NOT NULL");
	if(!$ev_chk['it_id']){
		return false;
	}
	// 주문서에 정보 수정
	sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2016출석체크 이벤트 사은품 지급 취소') WHERE od_id='".$od_id."'");
	// 이벤트 데이터 수정
	sql_query("UPDATE yc4_event_data SET value3 = NULL WHERE uid='" . $ev_chk['uid'] . "'");
	return true;
}

/**2017 마스터카드 토요일 5%포인트적립이벤트 초기화(관리자페이지) 2017-01-05 강경인
 * @param $od_id
 * @return bool
 */
function master_17_sat_point_cancel($od_id){
	$ev_chk	= sql_fetch("SELECT uid, value4 AS point FROM yc4_event_data WHERE ev_code='master_17_sat_point' AND ev_data_type='od_id' AND value1='".$od_id."' AND value5 IS NULL AND value6 IS NULL");
	if(!$ev_chk['uid']){
		return false;
	}
	// 이벤트 데이터 변경
	sql_query("UPDATE yc4_event_data SET value6='CANCEL' WHERE uid='".$ev_chk['uid']."'");
	// 주문서 데이터 변경
	sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2017 마스터카드 주말포인트(".$ev_chk['point'].") 취소완료') WHERE od_id='".$od_id."'");
	// 결과 리턴
	return true;
}

/**2017 설날 100달러이상 5% 할인 5% 적립 이벤트 초기화(관리자 페이지) 2017-01-04 강경인
 * @param $od_id
 * @return bool
 */
function new_year_dc_2017_cancel($od_id){
	$ev_chk	= sql_fetch("SELECT uid, value5 AS point FROM yc4_event_data WHERE ev_code='new_year_dc_2017' AND ev_data_type='od_id' AND value1='".$od_id."' AND value6 IS NULL AND value7 IS NULL");
	if($ev_chk['point'] < 1){
		return false;
	}
	// 이벤트 데이터 변경
	sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='".$ev_chk['uid']."'");
	// 주문서 데이터 변경
	sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$ev_chk['point'].", od_shop_memo = CONCAT(od_shop_memo,'\\n','2017 설날 (".$ev_chk['point'].") 할인 및 포인트 취소완료') WHERE od_id='".$od_id."'");
	// 결과 리턴
	return true;
}

/**2016 크리스마스 사은품 지급 초기화(관리자페이지) 2016-12-21 강경인
 * @param $od_id
 * @return bool
 */
function x_mars_16_gift_cancel($od_id){
	$ev_data	= sql_fetch("SELECT uid, value2 AS od_id FROM yc4_event_data WHERE ev_code='x_mars_16_gift' AND ev_data_type='od_id' AND value2='".$od_id."' AND value4 IS NULL");
	if(!$ev_data['uid']){
		return false;
	}
	// 이벤트 데이터 업데이트
	sql_query("UPDATE yc4_event_data SET value4='cancel' WHERE uid='" . $ev_data['uid'] . "'");
	// 주문서에 취소정보 추가
	sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2016 크리스마스 사은품 취소') WHERE od_id='".$od_id."'");
	// 사은품 수량 조절
	sql_query("UPDATE yc4_event_data SET value2 = CAST(value2 AS int) - 1 WHERE ev_code='x_mars_16_gift' AND ev_data_type='item_info'");

	return true;
}

/**마스터카드 50/100 결제건 5/10 할인 이벤트 취소 초기화
 * @param $od_id
 * @return bool
 */
// 마스터카드 50/100 결제건 5/10 할인 이벤트 결재실패 초기화
function master_16_50_100_dc_cancel($od_id){
	global $g4;

	$od	= sql_fetch("SELECT on_uid, exchange_rate, mb_id FROM yc4_order WHERE od_id='".$od_id."'");
	if(!$od){
		return false;
	}
	// 비회원 제외처리
	if(!$od['mb_id']){
		return false;
	}
	// 이벤트 데이터 확인
	$ev_chk = sql_fetch("SELECT value4 AS dc_usd, value5 AS dc_amount, uid FROM yc4_event_data WHERE ev_code = 'master_16_50_100_dc' AND ev_data_type = 'od_id' AND value1 = '".$od['mb_id']."' AND value2='$od_id' AND value6 IS NULL");
	if(!$ev_chk['uid']) {
		return false;
	}
	// 이벤트 데이터 수정
	sql_query("UPDATE yc4_event_data SET value6='CANCEL' WHERE uid='". $ev_chk['uid'] ."'");
	// 주문서 데이터 수정
	sql_query("
		UPDATE {$g4['yc4_order_table']}
		SET od_dc_amount = CAST(od_dc_amount AS int) - CAST(".(int)$ev_chk['dc_amount']." AS int), od_shop_memo = concat(od_shop_memo,'\\n','주문취소로 인한 마스터카드 ". $ev_chk['dc_usd'] ."불 즉시할인 혜택 초기화')
		WHERE od_id = '" . $od_id . "'
	");
	return true;
}

/**주문취소 재고 복구 처리(order_cancel 하단)
 * ev_code		= nordic_1p1_16_dc
 * ev_data_type	= item_info		// od_id
 * value1		= it_id			// od_id
 * value2		= amount		// mb_id
 * value3		= sales_qty		// it_id
 * value4		= qty
 * @param $od_id
 * @return bool
 */
function nordic_1p1_16_dc_cancel($od_id){
	$item_chk	= sql_query("SELECT value3 AS it_id, uid FROM yc4_event_data WHERE ev_code='nordic_1p1_16_dc' AND ev_data_type='od_id' AND value1='" . $od_id . "'");
	if(!$item_chk){
		return false;
	}
	while($row = sql_fetch_array($item_chk)){
		if(!$row['it_id']){
			continue;
		}
		// 이벤트 데이터 삭제
		sql_query("DELETE FROM yc4_event_data WHERE uid='" . $row['uid'] . "'");
		// 이벤트 상품 정보 변경 및 품절시 품절해제 처리
//		$ev_qty_chk	= sql_fetch("SELECT value3 AS sales_qty, value4 AS qty, uid FROM yc4_event_data WHERE ev_code='nordic_1p1_16_dc' AND ev_data_type='item_info' AND value1='" . $row['it_id'] . "'");
//		sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - 1 WHERE uid='" . $ev_qty_chk['uid'] . "'");
//		if($ev_qty_chk['sales_qty'] >= $ev_qty_chk['qty']){
//			sql_query("UPDATE yc4_item SET it_stock_qty = 9999 WHERE it_id='" . $row['it_id'] . "'");
//		}
	}
}

/**빼빼로 데이 특별 사은품 취소처리(관리자페이지)
 * @param $od_id
 * @return boolean
 */
function bbeabbearo_16_gift_cancel($od_id){
	$ev_data	= sql_fetch("SELECT uid, value2 AS gift_type, value3 AS it_id FROM yc4_event_data WHERE ev_code='bbeabbearo_16_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value4 IS NULL");
	if(!$ev_data['it_id']){
		return false;
	}
	// 이벤트 데이터 업데이트
	sql_query("UPDATE yc4_event_data SET value4='cancel' WHERE uid='" . $ev_data['uid'] . "'");
	// 주문서에 취소정보 추가
	sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','빼빼로 데이 특별 사은품 취소') WHERE od_id='".$od_id."'");
	// 사은품 수량 조절
	sql_query("UPDATE yc4_event_data SET value4 = CAST(value4 AS int) - 1 WHERE ev_code='bbeabbearo_16_gift' AND ev_data_type='item_info' AND value1='".$ev_data['gift_type']."' AND value2='".$ev_data['it_id']."'");

	return true;
}

// 헬스관 오픈 사은품 지급 초기화(관리자페이지용)
function health_shop_op_gift_cancel($od_id){
	$ev_chk = sql_fetch("SELECT uid, value3 AS it_id FROM yc4_event_data WHERE ev_code='health_shop_op_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value4 IS NULL");
	if(!$ev_chk['uid']){
		return false;
	}
	sql_query("UPDATE yc4_event_data SET value4='cancel' WHERE uid='".$ev_chk['uid']."'");
	sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='health_shop_op_gift' AND ev_data_type='gift_item' AND value2='".$ev_chk['it_id']."'");
	sql_query("UPDATE yc4_order SET od_shop_memo=CONCAT(od_shop_memo,'\\n','헬스관 오픈기념 사은품 지급 취소') WHERE od_id='".$od_id."'");

	return true;
}

// 추석 사은품 추가지급 취소
function harvest_16_etc_gift_cancel($mb_id, $od_id){
	// 비회원 제외
	if(!$mb_id){
		return false;
	}
	if($mb_id == '비회원'){
		return false;
	}
	$chk = sql_fetch("SELECT uid, value3 FROM yc4_event_data WHERE ev_code='harvest_16_etc_gift' AND ev_data_type='gift_item' AND value1='".$mb_id."' AND value3='".$od_id."'");
	if(!$chk || !$chk['uid'] || !$chk['value3']){
		return false;
	}
	// 주문서 및 이벤트 데이터에 사은품 취소 내용 저장
	sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','추석 90달러 사은품 추가지급 취소') WHERE od_id='".$od_id."'");
	sql_query("UPDATE yc4_event_data SET value3=NULL WHERE uid='".$chk['uid']."'");
	return true;
}

// 추석이벤트 포인트 적립 취소
function harvest_day_16_point_cancel($od_id){
	$ev_chk	= sql_fetch("SELECT uid, value3 AS point, value6 AS flag FROM yc4_event_data WHERE ev_code='harvest_day_16_point' AND ev_data_type='od_id' AND value2='".$od_id."' AND value4 IS NULL AND value5 IS NULL");
	if(!$ev_chk['uid']){
		return false;
	}
	// 주문서에 정보입력
	sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','추석 이벤트 포인트 적립 취소') WHERE od_id='".$od_id."'");
	// 이벤트 데이터 완료처리
	sql_query("UPDATE yc4_event_data SET value5='cancel' WHERE uid='".$ev_chk['uid']."'");
	return true;
}

//	결제 실패 및 취소시 이벤트 혜택 초기화 처리
function kb_16_100_5_dc_reset($mb_id, $od_id){
	// 비회원 제외
	if (!$mb_id) {
		return false;
	}
	if ($mb_id == '비회원') {
		return false;
	}
	// 이벤트 적용내역 조회
	$ev_chk = sql_fetch("SELECT uid, value4 AS dc_amount, value3 AS amount_usd FROM yc4_event_data WHERE ev_code='kb_16_100_5_dc' AND ev_data_type='od_id' AND value1='" . $mb_id . "' AND value2='" . $od_id . "' AND value5 IS NULL");
	if (!$ev_chk['amount_usd']) {
		return false;
	}
	$dc_usd = round($ev_chk['amount_usd'] * 0.05, 2);
	// 이벤트 데이터 삭제
	sql_query("UPDATE yc4_event_data SET value5='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");
	// 주문서 정보 업데이트
	sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - " . (int)$ev_chk['dc_amount'] . ", od_shop_memo = CONCAT(od_shop_memo,'\\n','kb " . $dc_usd . "달러 할인 취소') WHERE od_id='" . $od_id . "'");

	return true;
}

// 2016 추석 사은품 지급 초기화(관리자페이지용)
function harvest_day_16_gift_cancel($od_id){
	$ev_chk = sql_fetch("SELECT uid, value3 AS it_id, value4 AS amount FROM yc4_event_data WHERE ev_code='harvest_day_16_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value5 IS NULL");
	if(!$ev_chk['uid']){
		return false;
	}

	sql_query("UPDATE yc4_event_data SET value5='cancel' WHERE uid='".$ev_chk['uid']."'");
	sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='harvest_day_16_gift' AND ev_data_type='gift_item' AND value2='".$ev_chk['it_id']."'");
	sql_query("UPDATE yc4_order SET od_shop_memo=CONCAT(od_shop_memo,'\\n','추석이벤트 ".$ev_chk['amount']."달러 사은품 지급 취소') WHERE od_id='".$od_id."'");

	return true;
}

// 인스타그램 사은품 지급 초기화
function instagram_16_gift_cancel($od_id,$mb_id){
    if($mb_id=='비회원'){
        return false;
    }
    $chk = sql_fetch("SELECT uid, value3 FROM yc4_event_data WHERE ev_code='instagram_16_gift' AND ev_data_type='gift_item' AND value1='".$mb_id."' AND value3='".$od_id."'");
    if(!$chk || !$chk['uid'] || !$chk['value3']){
        return false;
    }
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','인스타그램 사은품 지급 주문 취소 초기화') WHERE od_id='".$od_id."'");
    sql_query("UPDATE yc4_event_data SET value3=NULL WHERE uid='".$chk['uid']."'");

    return true;
}


// 블랙썸머데이 노르딕 쇼퍼백 지급 초기화
function nordic_set_bag_gift_cancel($on_uid, $od_id){
	// 이벤트 상품정보 로드
	$ev_chk	= sql_fetch("SELECT e.value2 AS it_id FROM yc4_event_data e LEFT JOIN yc4_cart c ON c.it_id=e.value2 WHERE e.ev_code='nordic_set_bag_gift' AND e.ev_data_type='gift_item' AND c.on_uid='".$on_uid."'");

	if(!$ev_chk['it_id']){
		return false;
	}
	// 주문서 및 이벤트 데이터에 사은품 취소 내용 저장
	sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='nordic_set_bag_gift' AND ev_data_type='gift_item' AND value2='".$ev_chk['it_id']."'");
	sql_query("UPDATE yc4_order SET od_status_update_dt=now(), od_shop_memo=CONCAT(od_shop_memo,'\\n','블랙썸머데이 노르딕쇼퍼백 지급취소') WHERE on_uid='".$on_uid."'");
	sql_query("UPDATE yc4_event_data SET value4='cancel' WHERE ev_code='nordic_set_bag_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value3='".$ev_chk['it_id']."'");

	return true;
}

// 리우올림픽 사은품 지급 초기화
function olympic_16_gift_cancel($od_id,$mb_id){
	if($mb_id=='비회원'){
		return false;
	}
	$chk = sql_fetch("SELECT uid, value3 FROM yc4_event_data WHERE ev_code='olympic_16_gift' AND ev_data_type='gift_item' AND value1='".$mb_id."' AND value3='".$od_id."'");
	if(!$chk || !$chk['uid'] || !$chk['value3']){
		return false;
	}
	// 주문서 및 이벤트 데이터에 사은품 취소 내용 저장
	sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','리우올림픽 사은품 지급 취소') WHERE od_id='".$od_id."'");
	sql_query("UPDATE yc4_event_data SET value3=NULL WHERE uid='".$chk['uid']."'");
	return true;
}

// 리우올림픽 취소시 적립 초기화
function olympic_16_point_cancel($od_id){

	$chk	= sql_fetch("SELECT COUNT(*) AS cnt FROM yc4_event_data WHERE ev_code = 'olympic_16_point' AND ev_data_type = 'od_id' AND value2 = '".$od_id."' AND value5 IS NULL");

	if($chk['cnt']>0) {
		// 이벤트 데이터 취소처리
		sql_query("UPDATE yc4_event_data SET value5='cancel' WHERE ev_code = 'olympic_16_point' AND ev_data_type = 'od_id' AND value2 = '".$od_id."'");
		// 주문서 정보 수정
		sql_query("UPDATE yc4_order SET od_shop_memo = concat(od_shop_memo,'\\n','리우올림픽 8% 적립 초기화') WHERE od_id = '".$od_id."'");
		return true;
	}
	return false;
}

## 하나 비바카드 주문 취소시 포인트 취소처리
function viva_5_point_cancel($od_id){
	$ev_chk	= sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='viva_point' AND ev_data_type='od_id' AND value2='".$od_id."'");
	if(!$ev_chk['uid']){
		return false;
	}
	sql_query("UPDATE yc4_event_data SET value5='cancel' WHERE uid='".$ev_chk['uid']."'");
	sql_query("UPDATE yc4_order SET od_shop_memo=CONCAT(od_shop_memo,'\\n','하나비바 5%포인트 적립 취소') WHERE od_id='".$od_id."'");

	return true;
}


## 여름 바캉스 주문 취소시 사은품 지급 내역 초기화
function sum_vacation_16_gift_cancel($on_uid, $od_id){
	// 이벤트 상품정보 로드
	$ev_chk	= sql_fetch("SELECT uid, value3 AS it_id FROM yc4_event_data WHERE ev_code='sum_vacation_16_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value4 IS NULL");
	if(!$ev_chk['it_id']){
		return false;
	}

	sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='sum_vacation_16_gift' AND ev_data_type='gift_item' AND value2='".$ev_chk['it_id']."'");
	sql_query("UPDATE yc4_order SET od_status_update_dt=now(), od_shop_memo=CONCAT(od_shop_memo,'\\n','여름 바캉스 사은품 취소') WHERE on_uid='".$on_uid."'");
	sql_query("UPDATE yc4_event_data SET value4='cancel' WHERE uid='".$ev_chk['uid']."'");

	return true;
}


## 노르딕 주문취소시 상품지급내역 초기화
function nordic_20years_gift_cancel($on_uid, $od_id){
	return false;
	// 이벤트 상품정보 로드
	$ev_sql	= sql_query("SELECT e.value1 AS type, e.value2 AS it_id FROM yc4_event_data e LEFT JOIN yc4_cart c ON c.it_id=e.value2 WHERE e.ev_code='nordic_20years_gift' AND e.ev_data_type='gift_item' AND c.on_uid='".$on_uid."'");
	$ev_chk	= array();
	while($row = sql_fetch_array($ev_sql)){
		$ev_chk[]	= $row;
	}
	if(count($ev_chk)<1){
		return false;
	}
	
	foreach($ev_chk as $val){
		sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='nordic_20years_gift' AND ev_data_type='gift_item' AND value2='".$val['it_id']."' AND value1='".$val['type']."'");
		$ev_str		= $val['type']=="set" ? "세트구매" : "90달러이상구매";
		sql_query("UPDATE yc4_order SET od_shop_memo=CONCAT(od_shop_memo,'\\n','노르딕이벤트 사은품(".$ev_str.") 취소') WHERE on_uid='".$on_uid."'");
		sql_query("UPDATE yc4_event_data SET value4='cancel' WHERE ev_code='nordic_20years_gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value3='".$val['it_id']."'");
	}
	return true;
}

## 가정의달 취소시 적립 초기화
function familymonth_16_point_cancel($od_id){
	global $g4;
	
	$chk	= sql_fetch("SELECT COUNT(*) AS cnt FROM yc4_event_data WHERE ev_code = 'familymonth_16_point' AND ev_data_type = 'od_id' AND value2 = '".$od_id."'");

	if($chk['cnt']>0) {
		sql_query("DELETE FROM yc4_event_data WHERE ev_code = 'familymonth_16_point' AND ev_data_type = 'od_id' AND value2 = '".$od_id."'");
		sql_query("UPDATE ".$g4['yc4_order_table']." SET od_shop_memo = concat(od_shop_memo,'\\n','주문 취소로 인한 가정의달 5% 적립 혜택 초기화') WHERE od_id = '".$od_id."'");
		return true;
	}
	return false;
}

##	국민 마스터카드 결제 취소시 이벤트 혜택 초기화 처리
function kb_master_16_dc_10_reset($mb_id, $od_id){
	// 비회원 제외
	if(!$mb_id){
		return false;
	}
	if($mb_id=='비회원'){
		return false;
	}
	// 이벤트 적용내역 조회
	$ev_chk	= sql_fetch("SELECT uid, value4 AS dc_amount, value5 AS card FROM yc4_event_data WHERE ev_code='kb_master_16_dc_10' AND ev_data_type='od_id' AND value1='".$mb_id."' AND value2='".$od_id."'");
	if(!$ev_chk['uid']){
		return false;
	}
	$dc_usd	= "10";
	if($ev_chk['card']=="Gaon"){
		$dc_usd	= "Gaon 20";
	}
	// 이벤트 데이터 삭제
	sql_query("DELETE FROM yc4_event_data WHERE uid='".$ev_chk['uid']."'");
	// 주문서 정보 업데이트
	sql_query("UPDATE yc4_order SET od_dc_amount=od_dc_amount-".(int)$ev_chk['dc_amount'].", od_shop_memo = CONCAT(od_shop_memo,'\\n','kb ".$dc_usd."달러 할인 결재실패 취소') WHERE od_id='".$od_id."'");
	
	return true;
}

##	나우푸드 사은품 지급 취소시 초기화
function nowfood_16_30gift_cancel($on_uid, $od_id){
	// 이벤트 상품정보 로드
	$ev_chk	= sql_fetch("SELECT e.value1 AS type, e.value2 AS it_id FROM yc4_event_data e LEFT JOIN yc4_cart c ON c.it_id=e.value2 WHERE e.ev_code='nowfood_16_30gift' AND e.ev_data_type='gift_item' AND c.on_uid='".$on_uid."'");

	if(!$ev_chk['it_id']){
		return false;
	}
	
	// 사은품 지급갯수 수정 
	sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='nowfood_16_30gift' AND ev_data_type='gift_item' AND value2='".$ev_chk['it_id']."' AND value1='".$ev_chk['type']."'");
	// 주문서 메모 추가
	sql_query("UPDATE yc4_order SET od_shop_memo=CONCAT(od_shop_memo,'\\n','나우푸드 사음품 지급 취소') WHERE on_uid='".$on_uid."'");
	// 이벤트 데이터 취소처리
	sql_query("UPDATE yc4_event_data SET value4='cancel' WHERE ev_code='nowfood_16_30gift' AND ev_data_type='od_id' AND value1='".$od_id."' AND value3='".$ev_chk['it_id']."'");
	
	return true;
}

##	마스터패스 취소시 이벤트 혜택 초기화 처리
function masterpass_16_80dc_cancel($od_id){

	// 이벤트 적용내역 조회
	$ev_chk	= sql_fetch("SELECT uid, value2 AS dc_amount FROM yc4_event_data WHERE ev_code='masterpass_16_80dc' AND value1='".$od_id."'");

	if(!$ev_chk['uid']){
		return false;
	}

	// 이벤트 데이터 삭제
	sql_query("DELETE FROM yc4_event_data WHERE uid='".$ev_chk['uid']."'");
	// 주문서 정보 업데이트
	sql_query("UPDATE yc4_order SET od_dc_amount=od_dc_amount-".(int)$ev_chk['dc_amount'].", od_shop_memo = CONCAT(od_shop_memo,'\\n','마스터패스 5달러 할인 결재실패 취소') WHERE od_id='".$od_id."'");

	return true;
}

// 할로윈 사은품 이벤트 취소 초기화
function event_weekend_free_gift_201610_cancel($od_id){

	$ev_chk	= sql_fetch("SELECT uid, value3 as it_id FROM yc4_event_data WHERE ev_code='free_gift_201610' AND ev_data_type = 'od_id' AND value1='".$od_id."' AND value4 IS NULL");

	if($ev_chk['uid'] == ''){
		return false;
	} else {

		sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2016 할로윈 주말 사은품 증정 취소') WHERE od_id='".$od_id."'");

		sql_query("UPDATE yc4_event_data set value4='cancel' WHERE ev_code='free_gift_201610' AND ev_data_type = 'od_id' AND value1='".$od_id."'");

	}

}

// 마스터카드 첫구매시 40불이상 10% 즉시할인 이벤트 초기화
function mastercard_first_order_2016_cancel($od_id){

	$ev_chk	= sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='mc_fo_2016' AND ev_data_type = 'md_id' AND value2='".$od_id."' AND value6 IS NULL");

	if($ev_chk['uid'] == ''){
		return false;
	} else {

		sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','마스터카드 첫 주문 40달러 이상 10% 할인 취소') WHERE od_id='".$od_id."'");

		sql_query("UPDATE yc4_event_data set value6='cancel' WHERE ev_code='mc_fo_2016' AND ev_data_type = 'md_id' AND value2='".$od_id."'");

	}
	
}

// 삼성 MasterCard 100불이상 결제시 10% 즉시할인 초기화
function samsung_master_card_2016_cancel($od_id){

	$ev_chk	= sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='samsung_card_2016' AND ev_data_type = 'od_id' AND value2='".$od_id."' AND value6 IS NULL");

	if($ev_chk['uid'] == ''){
		return false;
	} else {

		sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','삼성마스터카드 이벤트 100달러 이상 결제시 10달러 할인 취소') WHERE od_id='".$od_id."'");

		sql_query("UPDATE yc4_event_data set value6='cancel' WHERE ev_code='samsung_card_2016' AND ev_data_type = 'od_id' AND value2='".$od_id."'");

	}

}

// 삼성 5V2 card 결제금액 20%포인트 지급 이벤트 초기화
function samsung_5v3_card_2016_cancel($od_id){

	$ev_chk	= sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='samsung5v2_2016' AND ev_data_type = 'od_id' AND value2='".$od_id."' AND value6=0");

	if($ev_chk['uid'] == ''){
		return false;
	} else {

		sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','삼성 5V2 card 결제금액 20%포인트 지급 취소') WHERE od_id='".$od_id."'");

		sql_query("UPDATE yc4_event_data set value6='cancel' WHERE ev_code='samsung5v2_2016' AND ev_data_type = 'od_id' AND value2='".$od_id."'");

	}

}

// 노르딕 구매금액별 사은품 이벤트 초기화
function nordic_freegift_2016_cancel($od_id){

	$ev_chk	= sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='nordic_freegift_2016' AND ev_data_type = 'od_id' AND value2='".$od_id."' AND value5 is null");

	if($ev_chk['uid'] == ''){
		return false;
	} else {

		sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','노르딕 구매금액별 사은품 이벤트 지급 취소') WHERE od_id='".$od_id."'");

		sql_query("UPDATE yc4_event_data set value5='cancel' WHERE ev_code='nordic_freegift_2016' AND ev_data_type = 'od_id' AND value2='".$od_id."'");

	}

}
//인스타 그램 2017-03-25 이후 주문 시 주문건에 해당 사은품 자동지급 취소
function instargram_20170325_gift_cancel($od_id,$mb_id){
	if($mb_id=="비회원"){
		return false;
	}
    $chk = sql_fetch("SELECT uid,value3 AS od_id FROM yc4_event_data WHERE ev_code='instarwbc_17_gift' AND ev_data_type='gift_item' AND value3 = '".$od_id."' AND value1 ='".$mb_id."'");
    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }
    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','인스타그램 사은품 지급 주문 취소 초기화') WHERE od_id='".$od_id."'");
    // 이벤트 데이터 복구
    sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='".$chk['uid']."'");

    return true;
}
//만우절이벤트 사은품 당첨자 2017-04-11이후 주문 시 주문건에 해당 사은품 자동지급 취소
function april_fool_2017_gift_cancle($od_id,$mb_id){
    if($mb_id=='비회원'){
        return false;
    }
    $chk = sql_fetch("SELECT uid,value3 AS od_id FROM yc4_event_data WHERE ev_code='april_fool_17_gift' AND ev_data_type='gift_item' AND value3 = '".$od_id."' AND value1 ='".$mb_id."'");
    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }
    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','만우절 이벤트 당첨자 사은품 지급취소') WHERE od_id='".$od_id."'");
    // 이벤트 데이터 복구
    sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='".$chk['uid']."'");

    return true;
}
function holiday_Special_Week_2017_Point_Cancel($od_id){
    $event_data = sql_fetch("  
                   SELECT uid from yc4_event_data 
                   where ev_code = 'holidaySpWeek201705'
                   and ev_data_type ='od_id' 
                   and value2 ='{$od_id}'
                   and value5 is null
                   and value4 is null
                   ");
    if($event_data['uid']) {
        sql_query("UPDATE yc4_event_data SET value5='cancel' WHERE uid='{$event_data['uid']}'");
        sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','홀리데이 스페셜 위크 5000포인트 적립 초기화') WHERE od_id='{$od_id}'");
        return true;
    }
    return false;
}

##인스타그램 반려동물 사은품 당첨자 2017-05-10~2017-06-30 주문 시주문건에 해당 사은품 자동지급 취소
function instagram_2017_animal_gift_cancle($od_id,$mb_id){
    //아이디 관련
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서 관련
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    //이벤트 확인
    $chk = sql_fetch("
SELECT count(*) cnt 
FROM yc4_event_data
WHERE     ev_code = 'insta_201705_gift'
      AND ev_data_type = 'gift_item'
      AND value1 = '{$mb_id}'
      AND value3 = '{$od_id}'
");
    if($chk['cnt']<=0){
        return false;
    }
    $slq  = sql_query("
SELECT uid, value3 AS od_id
FROM yc4_event_data
WHERE     ev_code = 'insta_201705_gift'
      AND ev_data_type = 'gift_item'
      AND value1 = '{$mb_id}'
      AND value3 = '{$od_id}'

");
    while ($row =sql_fetch_array($slq)){
        if($row['uid']&& $row['od_id']){
            //당첨자 사은품 지급 취소 = 주문서 메모
            sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='{$row['uid']}'");

        }
    }
    //당첨자 사은품 지급 취소 = 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','반려동물 인스타그램 이벤트 당첨자 사은품 지급취소') WHERE od_id='{$od_id}'");

    return true;
}

##마스터카드 경품 당첨자 2017-06-24 ~ 2017-08-31 주문 시 주문건에 해당 사은품 자동지급 취소 곽범석 2017-06-23작성
function master_201706_gift_cancel($od_id,$mb_id){
    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    //이벤트
    $chk = sql_fetch("SELECT uid,value3 AS od_id FROM yc4_event_data WHERE ev_code='master_201706_gift' AND ev_data_type='gift_item' AND value3 = '".$od_id."' AND value1 ='".$mb_id."'");
    //이벤트체크
    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }
    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','마스터카드 경품 당첨자 사은품 지급취소') WHERE od_id='".$od_id."'");
    // 이벤트 데이터 복구
    sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='".$chk['uid']."'");

    return true;
}
//첫 구매 할인 & 포인트 증정 2017-07-06 ~ 2017-08-06 기간내에 첫구매 50$ 이상 구매 5%할인  주문 취소 및 관리자 취소
function event_201707_usd50_dc5_cancel_inisis($od_id,$mb_id){
    /* 첫 구매 할인 & 포인트 증정 2017-07-06 ~ 2017-08-06 기간내에 첫구매 50$ 이상 구매 5%할인 주문 취소 및 관리자 취소
* 테이블			: yc4_event_data
* 기한              : 2017-07-06 ~ 2017-08-06
* ev_code		    : event_usd50_dc5_2017
* ev_data_type	    : mb_id
* value1		    : mb_id 아이디
* value2		    : od_id 주문서
* value3		    : 결제 금액
* value4		    : 할인 금액 U
* value5		    : 할인 금액 K
* value6		    : 포인트 적립 날짜
* value7		    : 취소여부
* 적용 파일         : ordercartupdate.php
*/

    //회원
    if(!$mb_id || $mb_id=='비회원' || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    $od_id = sql_safe_query($od_id);
    $mb_id = sql_safe_query($mb_id);
    //이벤트
    $event_chk = sql_fetch("
            SELECT uid, value5 as dc_amount
            FROM yc4_event_data
            WHERE     ev_code = 'event_usd50_dc5_2017'
                  AND ev_data_type = 'mb_id'
                  AND value1 = '{$mb_id}'
                  AND value2 = '{$od_id}' 
                  AND value7 IS NULL    
    ");
    //이벤트체크
    if(!$event_chk || !$event_chk['uid']){
        return false;
    }
    if($event_chk['dc_amount'] < 0 ){
        return false;
    }
    // 주문서 메모
    sql_query("
            UPDATE yc4_order 
            SET od_dc_amount= od_dc_amount - {$event_chk['dc_amount']}, od_shop_memo=concat(od_shop_memo,'\\n','2017 Welcome to Ople 이벤트 ({$event_chk['dc_amount']}) 할인 & 1000포인트 지급 취소') 
            WHERE od_id='{$od_id}'
            ");
    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data 
            SET value7 = 'CANCEL'
            WHERE uid='{$event_chk['uid']}'
             ");

    return true;
}
//신규가입 추천인 이벤트 2017.07.06~2017.08.06 추천인 적은 신규 고객이 이벤트 기간 내 구매(수령확인) 시, 추천 받은 고객에게 1000점 적립 취소
function recommender_2017_point1000_cancel($od_id){
    /*신규가입 추천인 이벤트 2017-07-06 ~ 2017-08-06 추천인 적은 신규 고객이 이벤트 기간 내 구매(수령확인) 시, 추천 받은 고객에게 1000점 적립 취소
     * 테이블			: yc4_event_data
     * 기한              : 2017-07-06 ~ 2017-08-06
     * ev_code		    : recommend_2017_1000
     * ev_data_type	    : mb_id
     * value1		    : mb_id 구매한 아이디
     * value2		    : od_id 주문서
     * value3		    : 결제 금액
     * value4		    : 추천인
     * value5		    : 적립 날짜
     * value6		    : 취소 여부
     * 적용파일         : 관리자/ordercartupdate.php
*/
    //이벤트 데이터 체크
    $event_data = sql_fetch("  
            SELECT uid,value4
            FROM yc4_event_data
            WHERE     ev_code = 'recommend_2017_1000'
                  AND ev_data_type = 'mb_id'
                  AND value2 = '{$od_id}'
                  AND value5 IS NULL
                  AND value6 IS NULL             
                   ");

    if($event_data['uid']) {
        //이벤트 데이터 취소처리
        sql_query("
            UPDATE yc4_event_data 
            SET value6 = 'CANCEL' 
            WHERE uid='{$event_data['uid']}'
            ");
        // 주문서 메모
        sql_query("
            UPDATE yc4_order 
            SET od_shop_memo=concat(od_shop_memo,'\\n','추천인 이벤트 추천 받은사람 : {$event_data['value4']} 1000포인트 적립 초기화') 
            WHERE od_id='{$od_id}'
            ");
        return true;
    }
    return false;
}

// 1.노르딕제품 2.80불이상 결제금액 80불이상 사은품 지급(1,2조건 모두 충족) 취소
function nordic_2017_80usd_gift_item_cancel($od_id){
    /* 1.노르딕제품 2.80불이상 결제금액 80불이상 사은품 지급(1,2조건 모두 충족)
         * ev_code		: nordic_2017_80usd
         * ev_data_type	: gift	/ od_id
         * value1			: 오플상품코드		    / 주문번호
         * value2			: 준비된 사은품 갯수	/ 아이디
         * value3			: 나간 사은품 갯수 	    / 결제금액
         * value4			:			            / 취소
         * 적용 파일      : 관리자
         */
    //주문서 확인
    if(!$od_id){
        return false;
    }
    $od_id = sql_safe_query($od_id);

    //이벤트 확인
    $chk	= sql_fetch("
                SELECT value1 as od_id,uid
                FROM yc4_event_data
                WHERE     ev_code = 'nordic_2017_80usd'
                      AND ev_data_type = 'od_id'
                      and value1 = '{$od_id}'
                      and value4 is null
                                    ");

    //이벤트 체크
    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','노르딕 $80이상 사은품 지급 취소 초기화')
            WHERE od_id='{$chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_2017_80usd' AND yc4_event_data.ev_data_type = 'gift'
                                    ");
    return true;

}
//마스터 카드 $100 이상 $10 할인 결제 실패 취소처리 관리자
function kb_master_2017_09_100usd_10dc_admin_cancel($od_id){ //곽범석 작업
    /* 적용 파일  : 카드 결제 실패 시 settle_authorize_result,settle_authorize_result_mp
    */

    $od_id	= sql_safe_query($od_id);
    //이벤트 데이터 체크
    $ev_chk	= sql_fetch("SELECT uid, value4 AS dc_amount, value3 AS dc_usd FROM yc4_event_data WHERE ev_code='kb_m_17_100_10_dc' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value5 IS NULL AND value6 IS NULL");
    if(!$ev_chk['uid']){
        return false;
    }

    if($ev_chk['dc_amount'] < 1){
        return false;
    }

    // 이벤트 데이터 변경 처리
    sql_query("UPDATE yc4_event_data SET value6='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    // 주문서 정보 변경 처리
    sql_query("UPDATE yc4_order SET od_dc_amount=CAST(od_dc_amount AS int) - " . (int)$ev_chk['dc_amount'] . ", od_shop_memo=CONCAT(od_shop_memo, '\\n', '국민 마스터카드 100달러 이상 10달러 할인 결제실패') WHERE od_id='" . $od_id . "'");

    return true;
}

##얼리버드 추석 인스타그램 이벤트 사은품 당첨자 자동지급취소 2017-09-16~무기한 곽범석 2017-09-15 작업
function instagram_201709_gift_cancel($od_id,$mb_id){
    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    $od_id = sql_safe_query($od_id);
    //이벤트
    $chk = sql_fetch("SELECT uid,value3 AS od_id FROM yc4_event_data WHERE ev_code='instar_201709_gift' AND ev_data_type='gift_item' AND value3 = '".$od_id."' AND value1 ='".$mb_id."'");
    //이벤트체크
    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }
    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','얼리버드 추석 인스타그램 사은품 지급취소') WHERE od_id='".$od_id."'");
    // 이벤트 데이터 복구
    sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='".$chk['uid']."'");

    return true;
}

//블랙 프라이데이 2017-10-16 ~ 2017-10-29    $100불이상 구매시 10% 즉시할인 취소
function black_friday_2017_usd100_dc10_cancel($od_id,$mb_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);
    //이벤트
    $chk = sql_fetch("SELECT uid,value1 AS od_id FROM yc4_event_data WHERE ev_code='bf_100usd_10_2017' AND ev_data_type='od_id' AND value1 = '".$od_id."' AND value7 IS NULL");
    //이벤트체크
    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    // 이벤트 데이터 복구
    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    return true;
}

##오플 스마트추석 인스타그램 이벤트 당첨자 사은품 자동지급 취소2017-10-18~무기한 곽범석 2017-10-16 작업
function instagram_201710_gift_item_speaker_cancel($od_id,$mb_id){
    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    $od_id = sql_safe_query($od_id);
    //이벤트
    $chk = sql_fetch("SELECT uid,value3 AS od_id FROM yc4_event_data WHERE ev_code='instar_201710_gift' AND ev_data_type='gift_item' AND value3 = '".$od_id."' AND value1 ='".$mb_id."'");
    //이벤트체크
    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }
    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','오플 스마트추석 인스타그램 이벤트 당첨자 사은품 지급 취소') WHERE od_id='".$od_id."'");
    // 이벤트 데이터 복구
    sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='".$chk['uid']."'");

    return true;
}

//노르딕 내추럴스 2+1 구미 증정 이벤트 취소 곽범석
function nordic_item201710_2_1_event_cancel($od_id,$mb_id){

    /*
     * 적용 파일 : orderinquirycancel.php
     *             관리자 ordercartupdate.php
     * */

    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    $od_id = sql_safe_query($od_id);
    //이벤트
    $chk = "SELECT uid,value3 AS it_id FROM yc4_event_data WHERE ev_code='nordic_201710_2_1' AND ev_data_type='od_id' AND value1 = '".$od_id."' AND value2 ='".$mb_id."'";
    //이벤트체크
    $result		= sql_query($chk);

    $set_chk_data		= array();
    while($row = sql_fetch_array($result)){
        $set_chk_data[] = $row;
    }

    if(count($set_chk_data) < 1){
        return false;
    }

    foreach ($set_chk_data as $value){

        // 사은품 지급갯수 수정
        sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='nordic_201710_2_1' AND ev_data_type='gift' AND value1='".$value['it_id']."' ");

        // 이벤트 데이터 복구
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid='".$value['uid']."'");

    }

    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','노르딕내추럴스 2+1 사은품 사은품 지급 취소') WHERE od_id='".$od_id."'");

    return true;
}
//노르딕 내추럴스 이벤트 노르딕 제품 $100 이상 실결제 $100 이상 보틀 지급, $60이상 $66~$99 치코백 지급 취소
function nordic_item201710_60_bag_100_bottle_cancel($od_id, $mb_id){
    /*
 * 적용 파일 : 관리자 ordercartupdate.php
 * */
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    //이벤트
    $chk = sql_fetch("SELECT uid,value3 AS it_id FROM yc4_event_data WHERE ev_code='nordic_201710_60_100' AND ev_data_type='od_id' AND value1 = '".$od_id."' AND value2 ='".$mb_id."'");

    if(!$chk || !$chk['it_id'] || !$chk['uid']){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','노르딕 사은품지급 취소 초기화')
            WHERE od_id='".$od_id."'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value5= 'CANCEL'
            WHERE uid='".$chk['uid']."'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_201710_60_100' AND yc4_event_data.ev_data_type = 'gift' and value1 = '".$chk['it_id']."'
                                    ");
    return true;
}
##하나 마스터체크 카드 이벤트 $100이상 구매 고객 $10 즉시할인 2017-11-13~2017-12-31  취소처리 곽범석 작업 2017-11-07
function hana_17_100_10_dc_2_cancel($od_id){

    /*
     * 적용파일 : settle_authorize_result.php,settle_authorize_result_mp.php,ordercartupdate.php(관리자)
     * */
    $od_id	= sql_safe_query($od_id);

    $ev_chk	= sql_fetch("SELECT uid, value4 AS dc_amount FROM yc4_event_data WHERE ev_code='hana_17_100_10_dc_2' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value5 IS NULL");

    if(!$ev_chk || !$ev_chk['uid']){
        return false;
    }

    if($ev_chk['dc_amount'] < 1){
        return false;
    }

    // 이벤트 데이터 변경 처리
    sql_query("UPDATE yc4_event_data SET value5='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    // 주문서 정보 변경 처리
    sql_query("UPDATE yc4_order SET od_dc_amount=CAST(od_dc_amount AS int) - " . (int)$ev_chk['dc_amount'] . ", od_shop_memo=CONCAT(od_shop_memo, '\\n', '2017년 하나카드 100달러 이상 10달러 할인 결제실패') WHERE od_id='" . $od_id . "'");

    return true;

}
##국민 마스터카드 할인 이벤트  2017-11-17 ~ 2017-12-31 100불이상 구매시 10% 할인 취소 곽범석 작업 2017-11-14
function kb_mastercard_100_15_cancel($od_id){

    /*
     * 적용 파일 : settle_authorize_result.php, settle_authorize_result_mp.php, orderupdate.php(관리자)
     * */

    if( !$od_id || trim($od_id) ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $ev_chk	= sql_fetch("SELECT uid, value4 AS dc_amount FROM yc4_event_data WHERE ev_code='kbmaster_2017_100_15' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value7 IS NULL");

    if(!$ev_chk || !$ev_chk['uid']){
        return false;
    }

    if($ev_chk['dc_amount'] < 1){
        return false;
    }

    // 이벤트 데이터 변경 처리
    sql_query("UPDATE yc4_event_data SET value7 = 'CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    // 주문서 정보 변경 처리
    sql_query("UPDATE yc4_order SET od_dc_amount=CAST(od_dc_amount AS int) - " . (int)$ev_chk['dc_amount'] . ", od_shop_memo=CONCAT(od_shop_memo, '\\n', '2017년 국민 마스터 카드 100달러 이상 " . $ev_chk['dc_amount'] . "원 할인 취소') WHERE od_id='" . $od_id . "'");

    return true;

}

//블랙프라이데이 노르딕 1+1 이벤트 2017-11-24~2017-11-26 주문시 결제 취소
function bf_nordic_1711_1_1_cancel($od_id){
    /* 블랙프라이데이 노르딕 1+1 이벤트 2017-11-24~2017-11-26
* ev_code		: bf_nordic_1p1_1711
* ev_data_type	: item	/ mb_id
* value1			: 오플상품코드		    / 주문번호
* value2			: 준비된 사은품 갯수	/ 아이디
* value3			: 나간 사은품 갯수 	    / 구매상품
* value4			: 가격                  /
* value5			:                       / 취소
* value6			:
* value7			:
* 적용 파일      : // 주문취소시 (고객)         orderinquirycancel.php // 주문취소시(관리자)        ordercartupdate.php
*/

//    if($_SERVER['REMOTE_ADDR'] != '112.218.8.102'){
//        return false;
//    }

//    $st_date = '20171124';
//    $en_date = '20171126';
//
//    if($st_date > date('Ymd') || $en_date < date('Ymd')){
//        return true;
//    }

    $od_id = sql_safe_query($od_id);

    $event_item = sql_query("
            SELECT value3, uid
            FROM yc4_event_data
            WHERE     ev_code = 'bf_nordic_1p1_1711'
                  AND ev_data_type = 'mb_id'
                  AND value1 = '".$od_id."' 
                ");

    if(!$event_item){
        return false;
    }

    while($row = sql_fetch_array($event_item)){

        if(!$row['value3'] || !$row['uid']){
            return false;
        }

        //수량
        sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - 1 WHERE value1 = '".$row['value3']."' and  ev_code = 'bf_nordic_1p1_1711' AND ev_data_type = 'item'");

        //취소
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid = '".$row['uid']."' ");

    }

    return true;

}
// 골라 담기 이벤트 2017-11-30  수령 확인 시 포인트 11% 적립 2017-11-24 작업 곽범석 취소
function choice_item_201711_cancel($od_id){
    /* 골라 담기 이벤트 2017-11-30
         * 실결제금액기준, 결제시점 이벤트 적용, 수령확인 후 포인트 지급
         * 테이블       : yc4_event_data
         * ev_code      : choice_item_201711
         * ev_data_type : od_id
         * value1		: 아이디
         * value2		: 주문번호
         * value3		: 골라담기 상품 금액
         * value4		: 적립포인트
         * value5		: 적립(수령확인)날짜
         * value6		: 취소여부
         * value7       : 환율
         * 적용 파일    :  ordercartupdate.php(관리자)
         */

    //주문 번호 체크
    if(!$od_id || $od_id ==''){
        return false;
    }

    //주문 번호 escape
    $od_id = sql_safe_query(trim($od_id));

    //이벤트  데이터 체크
    $event_data = sql_fetch("select uid,value4  from yc4_event_data where ev_code ='choice_item_201711' and ev_data_type = 'od_id' and value1 = '".$od_id."' and value6 is null ");

    //이벤트  데이터 없을시
    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    //적립할 포인트 없을 시
    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //적립할 포인트 금액
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터 취소 처리
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop_memo 글 남기기
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2017년 11월 30일 골라담기 이벤트(" . $event_data['value4'] . " 점)포인트 적립 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//스키니 앤코 30 불이상 주문, 결제금액 30 불 이상 시 사은품 지급 취소 (2가지 조건 충족시)
function skinny_co_30_gift_item_event_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'skinny_2017_30_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','스키니앤코 $30이상 사은품 지급 취소 초기화')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'skinny_2017_30_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

}

//실적 이벤트  취소 처리
function month_buy_history_cancel($od_id){
    /*  조건       : 오플 회원 결제시
     *  테이블     : amount_receipt_history
     *  컬럼       :    1.uid 고유 식별 번호
     *             :    2.mb_id 아이디
     *             :    3.od_id  주문번호
     *             :    4. amount_krw 결제 금액 원
     *             :    5. amount_usd 결제금액 달러
     *             :    6. point 포인트 금액
     *             :    7. craete_date 만든날짜
     * 적용 파일   : 관리자 취소 처리
     */
    if(!$od_id){

        return false;

    }

    $od_id = sql_safe_query(trim($od_id));

    $chk =sql_fetch("
            SELECT mb_id,
                   od_id,
                   amount_krw,
                   amount_usd,
                   point
            FROM amount_receipt_history
            WHERE od_id = '".$od_id ."'
                ");

    if(!$chk || !$chk['od_id']){

        return false;
    }

    $chk2 =sql_fetch("
            SELECT count(*) cnt
            FROM amount_receipt_history
            WHERE od_id = '".$od_id ."'
                ");

    if(!$chk2 || $chk2['cnt']>1){

        return false;
    }

    $amount_krw = isset($chk['amount_krw']) && $chk['amount_krw']>0 ? '-'.$chk['amount_krw'] : '0';
    $amount_usd = isset($chk['amount_usd']) && $chk['amount_usd']>0 ? '-'.$chk['amount_usd'] : '0';
    $point = isset($chk['point']) && $chk['point']>0 ? '-'.$chk['point'] : '0';


    sql_query("
INSERT INTO amount_receipt_history(mb_id,
                                   od_id,
                                   amount_krw,
                                   amount_usd,
                                   point,
                                   create_date)
VALUES
('".$chk['mb_id']."',
'".$chk['od_id']."',
'".$amount_krw."',
'".$amount_usd."',
'".$point."',
now())
    ");

    return true;

}

function double_amount_event_201801_cancel($od_id){

    /*
     * 적용 파일         : 오플 관리자 ordercartupdate, orderinquirycancel
     */

    if(!$od_id){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $ev_chk = sql_fetch("
                select count(*) cnt,uid ,value5 as dc_amount
                from yc4_event_data 
                where value1 ='".$od_id."' and value7 is null and ev_code = 'double_201801_ev'
                ");
    if(!$ev_chk || $ev_chk['cnt'] < 0 || !$ev_chk['uid']) {
        return false;
    }

    //이벤트 생
    sql_query("UPDATE yc4_event_data SET value7 = now() WHERE uid ='" . $ev_chk['uid'] . "'");
    //주문서
    sql_query("UPDATE yc4_order SET od_dc_amount= od_dc_amount - {$ev_chk['dc_amount']}, od_shop_memo = CONCAT(od_shop_memo,'\\n','더블해택 이벤트 취소 ') WHERE od_id='" . $od_id . "'");
}


//2017 크리스마스 인스타그램 이벤트 당첨자 사은품 자동지급 2017-12-29곽범석 작업
function instagram_2017_christmas_gift_cancle($od_id,$mb_id){

    //아이디 관련
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서 관련
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    //이벤트 확인
    $chk = sql_fetch("
SELECT count(*) cnt
FROM yc4_event_data
WHERE     ev_code = 'insta_201712_gift'
      AND ev_data_type = 'gift_item'
      AND value1 = '{$mb_id}'
      AND value3 = '{$od_id}'
");
    if($chk['cnt']< 1){
        return false;
    }
    $slq  = sql_query("
SELECT uid, value3 AS od_id
FROM yc4_event_data
WHERE     ev_code = 'insta_201712_gift'
      AND ev_data_type = 'gift_item'
      AND value1 = '{$mb_id}'
      AND value3 = '{$od_id}'

");
    while ($row =sql_fetch_array($slq)){
        if($row['uid']&& $row['od_id']){
            //당첨자 사은품 지급 취소 = 주문서 메모
            sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='{$row['uid']}'");

        }
    }
    //당첨자 사은품 지급 취소 = 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','크리스마스 인스타그램 이벤트 당첨자 사은품 지급취소') WHERE od_id='{$od_id}'");

    return true;
}

//다이어트 상품 이벤트 30불이상 주문시 사은품 지급 취소 곽범석 작업 20180104
function diet_event_30_gift_item_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'diet_30_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 다이어트 이벤트 $30이상 사은품 지급 취소 초기화')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'diet_30_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

}

//얼리버드 설날 이벤트 (설날 세트 구매 시, 쇼핑백 무료 증정 한정 수량) 주문서 취소
function early_bird_new_year_2018_gift_item_cancel($od_id,$mb_id){
    /*
     * orderinquirycancel.php,order cartupdate.php
     * */
    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    $od_id = sql_safe_query($od_id);
    //이벤트
    $chk = "SELECT uid,value3 AS it_id FROM yc4_event_data WHERE ev_code='newyear_18_1_dc' AND ev_data_type='od_id_item' AND value1 = '".$od_id."' AND value2 ='".$mb_id."' and value5 is null";
    //이벤트체크
    $result		= sql_query($chk);

    $set_chk_data		= array();
    while($row = sql_fetch_array($result)){
        $set_chk_data[] = $row;
    }

    if(count($set_chk_data) < 1){
        return false;
    }

    foreach ($set_chk_data as $value){

        // 사은품 지급갯수 수정
        sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='newyear_18_1_dc' AND ev_data_type='gift' AND value1='".$value['it_id']."' ");

        // 이벤트 데이터 복구
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid='".$value['uid']."'");

    }

    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2018년얼리버드 설날 이벤트 사은품 지급 취소') WHERE od_id='".$od_id."'");

    return true;
}

//얼리버드 설날 이벤트 ($60,$80 이상 포인트 적립) 수령확인 취소
function early_bird_new_year_2018_point_cancel($od_id){

    //주문 번호 체크
    if(!$od_id || $od_id ==''){
        return false;
    }

    //주문 번호 escape
    $od_id = sql_safe_query(trim($od_id));

    //이벤트  데이터 체크
    $event_data = sql_fetch("select uid,value4  from yc4_event_data where ev_code ='newyear_18_1_dc' and ev_data_type = 'od_id' and value1 = '".$od_id."' and value6 is null ");

    //이벤트  데이터 없을시
    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    //적립할 포인트 없을 시
    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //적립할 포인트 금액
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터 취소 처리
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop_memo 글 남기기
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 얼리버드 설날 이벤트(" . $event_data['value4'] . " 점)포인트 적립 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2018년 발렌타이데이 이벤트 2018-01-25 ~ 2018-02-14 지정된 상품 주문시 사은품 지급 취소
function valentine_day_2018_02_14_event_cancel($od_id,$mb_id){

    /*
     * 적용 파일 : orderinquirycancel.php
     *             관리자 ordercartupdate.php
     * */

    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    $od_id = sql_safe_query($od_id);
    //이벤트
    $chk = "SELECT uid,value3 AS it_id FROM yc4_event_data WHERE ev_code='day_18_02_14' AND value5 is null and ev_data_type='od_id' AND value1 = '".$od_id."' AND value2 ='".$mb_id."'";
    //이벤트체크
    $result		= sql_query($chk);

    $set_chk_data		= array();
    while($row = sql_fetch_array($result)){
        $set_chk_data[] = $row;
    }

    if(count($set_chk_data) < 1){
        return false;
    }

    foreach ($set_chk_data as $value){

        // 사은품 지급갯수 수정
        sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='day_18_02_14' AND ev_data_type='gift_qty' AND value1='".$value['it_id']."' ");

        // 이벤트 데이터 복구
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid='".$value['uid']."'");

    }

    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 발렌타인데이 사은품 지급 취소') WHERE od_id='".$od_id."'");

    return true;
}

//2018년 본 설날 이벤트 사은품 선택 지급 이벤트  70불 이상 지급 취소
function new_year_2018_gift_item_cancel($od_id){

    //주문 번호 체크
    if(!$od_id || $od_id ==''){
        return false;
    }

    //주문 번호 escape
    $od_id = sql_safe_query(trim($od_id));

    //이벤트  데이터 체크
    $event_data = sql_fetch("select uid,value3  from yc4_event_data where ev_code ='newyear_18_2_dc' and ev_data_type = 'od_id' and value1 = '".$od_id."' and value6 is null ");

    //이벤트  데이터 없을시
    if(!$event_data || !$event_data['uid'] || $event_data['value3'] == '') {
        return false;
    }

    //이벤트 데이터 취소 처리
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    // 사은품 지급갯수 수정
    sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='newyear_18_2_dc' AND ev_data_type='gift' AND value1='".$event_data['value3']."' ");


    //yc4_order shop_memo 글 남기기
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 설날 이벤트 사은품 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}
//마스터카드 이벤트(토요일 마스터카드 결제 3% 포인트 적립) 취소 곽범석 관리자 적용
function master_18_sat_point_cancel($od_id){
    $ev_chk	= sql_fetch("SELECT uid, value4 AS point FROM yc4_event_data WHERE ev_code='master_18_sat_point' AND ev_data_type='od_id' AND value1='".$od_id."' AND value5 IS NULL AND value6 IS NULL");
    if(!$ev_chk['uid']){
        return false;
    }
    // 이벤트 데이터 변경
    sql_query("UPDATE yc4_event_data SET value6='CANCEL' WHERE uid='".$ev_chk['uid']."'");
    // 주문서 데이터 변경
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018 마스터카드 주말포인트(".$ev_chk['point'].") 취소완료') WHERE od_id='".$od_id."'");
    // 결과 리턴
    return true;
}

function scivation_xtend_2018_event_cancel($od_id){

    //주문 번호 체크
    if(!$od_id || $od_id ==''){
        return false;
    }

    //주문 번호 escape
    $od_id = sql_safe_query(trim($od_id));

    //이벤트  데이터 체크
    $event_data = sql_fetch("select uid,value5  from yc4_event_data where ev_code ='scivation_201802' and ev_data_type = 'od_id' and value1 = '".$od_id."' and value7 is null ");

    //이벤트  데이터 없을시
    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    //적립할 포인트 없을 시
    if(!$event_data['value5'] || $event_data['value5'] == '') {
        return false;
    }

    //적립할 포인트 금액
    if($event_data['value5'] < 1 ){
        return false;
    }

    //이벤트 데이터 취소 처리
    sql_query("update yc4_event_data set value7 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop_memo 글 남기기
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','싸이베이션 이벤트(" . $event_data['value5'] . " 점)포인트 적립 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

function  snack_event_gift_item_201803_cancel($od_id,$mb_id){

    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid,value3 AS od_id FROM yc4_event_data WHERE ev_code='snack_201803_gift' AND ev_data_type='gift_item' AND value3 = '".$od_id."' AND value1 ='".$mb_id."'");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','오플 스낵 이벤트 당첨자 사은품 지급 취소') WHERE od_id='".$od_id."'");

    sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='".$chk['uid']."'");

    return true;
}

function nordic_20180302_event_90_gift_cancel($od_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id= sql_safe_query($od_id);

    //이벤트 확인
    $chk	= sql_fetch("
                SELECT value1 as od_id,uid
                FROM yc4_event_data
                WHERE     ev_code = 'nordic_2018_90usd'
                      AND ev_data_type = 'od_id'
                      and value1 = '{$od_id}'
                      and value4 is null
                                    ");

    //이벤트 체크
    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','노르딕 $90이상 사은품 지급 취소 초기화')
            WHERE od_id='{$chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_2018_90usd' AND yc4_event_data.ev_data_type = 'gift'
                                    ");
    return true;
}
function white_day_60usd_gift_item_201803_cancel($od_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id= sql_safe_query($od_id);

    $ev_chk = sql_fetch("
            SELECT uid, value4 as it_id
            FROM yc4_event_data
            WHERE     ev_code = 'white2018_60_gift'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."'
                  AND value5 IS NULL
                ");

    if(!$ev_chk || !$ev_chk['uid'] || !$ev_chk['it_id']) {
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 화이트데이 이벤트 사은품 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value5= 'CANCEL'
            WHERE uid='".$ev_chk['uid']."'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'white2018_60_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '".$ev_chk['it_id']."'
                                    ");
    return true;
}

function scivation_two_item_gift_item2018_cancel($od_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id= sql_safe_query($od_id);

    $ev_chk = sql_fetch("
            SELECT uid, value4 as it_id
            FROM yc4_event_data
            WHERE     ev_code = 'scivation_201803'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."'
                  AND value5 IS NULL
                ");

    if(!$ev_chk || !$ev_chk['uid'] || !$ev_chk['it_id']) {
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 싸이베이션 이벤트 사은품 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value5= 'CANCEL'
            WHERE uid='".$ev_chk['uid']."'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'scivation_201803' AND yc4_event_data.ev_data_type = 'gift' and value1 = '".$ev_chk['it_id']."'
                                    ");
    return true;
}
function welcome_201804_first_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $od = sql_fetch("select mb_id
                   from  yc4_order where od_id = '".$od_id."' ");

    if(!$od){
        return false;
    }

    if(!$od['mb_id'] || $od['mb_id'] =='' || $od['mb_id']=='비회원'){
        return false;
    }

    $ev_chk	= sql_fetch("SELECT uid, value4 AS dc_amount FROM yc4_event_data WHERE ev_code='ev_welcome_dc5_2018' AND ev_data_type='mb_id' AND value1='" . $od['mb_id'] . "' AND value7 IS NULL");

    if(!$ev_chk || !$ev_chk['uid']){
        return false;
    }

    if($ev_chk['dc_amount'] < 1){
        return false;
    }

    // 이벤트 데이터 변경 처리
    sql_query("UPDATE yc4_event_data SET value7 = 'CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    // 주문서 정보 변경 처리
    sql_query("UPDATE yc4_order SET od_dc_amount=CAST(od_dc_amount AS int) - " . (int)$ev_chk['dc_amount'] . ", od_shop_memo=CONCAT(od_shop_memo, '\\n', '2018 Welcome to Ople 이벤트 ({$ev_chk['dc_amount']}) 할인 취소') WHERE od_id='" . $od_id . "'");

    return true;

}

function welcome_201804_second_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $od = sql_fetch("select mb_id
                   from  yc4_order where od_id = '".$od_id."' ");

    if(!$od){
        return false;
    }

    if(!$od['mb_id'] || $od['mb_id'] =='' || $od['mb_id']=='비회원'){
        return false;
    }

    $ev_chk	= sql_fetch("SELECT uid, value4 AS dc_amount FROM yc4_event_data WHERE ev_code='e_welcome_po5_2018' AND ev_data_type='mb_id' AND value1='" . $od['mb_id'] . "' AND value7 IS NULL");

    if(!$ev_chk || !$ev_chk['uid']){
        return false;
    }

    if($ev_chk['dc_amount'] < 1){
        return false;
    }

    // 이벤트 데이터 변경 처리
    sql_query("UPDATE yc4_event_data SET value7 = 'CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    // 주문서 정보 변경 처리
    sql_query("UPDATE yc4_order SET od_shop_memo=CONCAT(od_shop_memo, '\\n', '2018 Welcome to Ople 이벤트 ({$ev_chk['dc_amount']}) 적립 취소') WHERE od_id='" . $od_id . "'");

    return true;

}

function diet_30_gift_item_event_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'diet_2018_30_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 다이어트 기획전 $30이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'diet_2018_30_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

function diet_50_gift_item_event_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'diet_2018_50_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 다이어트 기획전 $50이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'diet_2018_50_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

return true;
}
function diet_scivation_gift_item_event_cancel($od_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id= sql_safe_query($od_id);

    $ev_chk = sql_fetch("
            SELECT uid, value4 as it_id
            FROM yc4_event_data
            WHERE     ev_code = 'diet_2018_scivat'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."'
                  AND value5 IS NULL
                ");

    if(!$ev_chk || !$ev_chk['uid'] || !$ev_chk['it_id']) {
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 다이어트 기획전 싸이베이션 이벤트 사은품 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value5= 'CANCEL'
            WHERE uid='".$ev_chk['uid']."'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'diet_2018_scivat' AND yc4_event_data.ev_data_type = 'gift' and value1 = '".$ev_chk['it_id']."'
                                    ");
    return true;
}

//얼리버드 가정의달 이벤트 100불이상 구매시 5천원할인 0416~0430
function early_bird_familymonth_2018_usd100_dc5000_cancel($od_id,$mb_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid,value1 AS od_id FROM yc4_event_data WHERE ev_code='familymonth_18_point' AND ev_data_type='od_id' AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    return true;
}

//2018 04 노르딕 이벤트 전액 포인트 결제 취소
function nordic_201804_event_cancel($od_id,$mb_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid,value1 AS od_id FROM yc4_event_data WHERE ev_code='nordic_201804' AND ev_data_type='od_id' AND value1 = '".$od_id."' AND value5 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value5= 'CANCEL' WHERE uid='".$chk['uid']."'");

    return true;
}

function jarrow_formulas_40_point_event_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'jarrow_18_40_point'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 자로우 기획전 $40이상 포인트 2000점 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    return true;
}

function nordic_90_gift_item_event_201805_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'nordic_1805_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 노르딕 기획전 $90이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_1805_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//마스터 카드 $100 이상 $10 할인 결제 실패 취소처리
function kb_master_2018_05_100usd_5_cancel($od_id){ //곽범석 작업
    /* 적용 파일  : 카드 결제 실패 시 settle_authorize_result,settle_authorize_result_mp
    */

    if( !$od_id || trim($od_id) ==''){
        return false;
    }

    $od_id	= sql_safe_query($od_id);

    //이벤트 데이터 체크
    $ev_chk	= sql_fetch("SELECT uid, value4 AS dc_amount, value3 AS dc_usd FROM yc4_event_data WHERE ev_code='kb_m_1805_100_5' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value7 IS NULL");

    if(!$ev_chk['uid']){
        return false;
    }

    if($ev_chk['dc_amount'] < 1){
        return false;
    }

    // 이벤트 데이터 변경 처리
    sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    // 주문서 정보 변경 처리
    sql_query("UPDATE yc4_order SET od_dc_amount=CAST(od_dc_amount AS int) - " . (int)$ev_chk['dc_amount'] . ", od_shop_memo=CONCAT(od_shop_memo, '\\n', '국민 마스터카드 100달러 이상 5% 이벤트 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

function familymonth_may_be_happy_2018_ev_cancel($od_id){

    //주문 번호 체크
    if(!$od_id || $od_id ==''){
        return false;
    }

    //주문 번호 escape
    $od_id = sql_safe_query(trim($od_id));



    //이벤트  데이터 체크
    $event_data = sql_fetch("select uid ,value3 from yc4_event_data where ev_code ='family_1805_ev' and ev_data_type = 'od_id' and value1 = '".$od_id."' and value6 is null");

    //이벤트  데이터 없을시
    if(!$event_data || !$event_data['uid'] || $event_data['value3'] == '') {
        return false;
    }

    $od = sql_fetch("
    select
           od_receipt_bank+od_receipt_card AS od_amount
    from yc4_order 
    where od_id = '".$od_id."'
    ");
    if((int)$od['od_amount'] >0){
        sql_query("
            UPDATE yc4_event_data
            SET value3 = cast(value3 AS int) - 1
            WHERE value1 = '" . $event_data['value3'] . "'
           AND  ev_code = 'family_1805_ev'
                  AND ev_data_type = 'gift'
                                  ");
    }
    //이벤트 데이터 취소 처리
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop_memo 글 남기기
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 가정의달 이벤트 사은품 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//KEB 하나카드이벤트 이벤트 배너로 유입시 5프로 적립 취소
function hana_keb_point_18_5point_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'hana_keb_18'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value7 is null
    ");

    if(!$event_chk || !$event_chk['uid'] || $event_chk['uid'] ==''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','KEB 하나카드 유입 5% 포인트 적립 취소')
            WHERE od_id='{$od_id}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value7 = now()
            WHERE uid='{$event_chk['uid']}'
            ");

    return true;
}
//KEB 하나카드 이벤트 배너로 유입시 첫구매 5프로 할인 취소
function hana_keb_point_18_5dc_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'hana_keb_18'
    and ev_data_type = 'mb_id'
    and value1 = '".$od_id."'
    and value7 is null
    ");

    if(!$event_chk || !$event_chk['uid'] || $event_chk['uid'] ==''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','KEB 하나카드 유입 5% 할인 취소')
            WHERE od_id='{$od_id}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value7 = now()
            WHERE uid='{$event_chk['uid']}'
            ");

    return true;
}

function icepack_add_2018_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk   = sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='ice_2018_add' AND value1='" . $od_id . "'");

    if(!$event_chk || !$event_chk['uid'] || $event_chk['uid'] ==''){
        return false;
    }

    sql_query("
            UPDATE yc4_event_data
            SET value7 = now()
            WHERE uid='{$event_chk['uid']}'
            ");

    return false;
}

function scivation_item_gift_item201806_cancel($od_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id= sql_safe_query($od_id);

    $result = sql_query("
            SELECT uid, value4 as it_id
            FROM yc4_event_data
            WHERE     ev_code = 'scivation_201806'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."'
                  AND value5 IS NULL
                ");

    $set_chk_data		= array();
    while($row = sql_fetch_array($result)){
        $set_chk_data[] = $row;
    }

    if(count($set_chk_data) < 1){
        return false;
    }

    foreach ($set_chk_data as $value) {
        // 이벤트 데이터 복구
        sql_query("
            UPDATE yc4_event_data
            SET value5= 'CANCEL'
            WHERE uid='" . $value['uid'] . "'
            ");

        //사은품 소모량 -1
        sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'scivation_201806' AND yc4_event_data.ev_data_type = 'gift' and value1 = '" . $value['it_id'] . "'
                                    ");
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 싸이베이션 이벤트 사은품 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

function scivation_60_gift_item_event_201806_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 
    from yc4_event_data
    where ev_code = 'sciva_201806_list'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['value5'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 싸이베이션 경품 이벤트 $60이상 대상자 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    return true;
}

function time_point_20180690_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk   = sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='point_2018time28' AND value1='" . $od_id . "'");

    if(!$event_chk || !$event_chk['uid'] || $event_chk['uid'] ==''){
        return false;
    }

    sql_query("
            UPDATE yc4_event_data
            SET value6 = now()
            WHERE uid='{$event_chk['uid']}'
            ");

// 주문서 메모 추가
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo, '\\n', '2018년 얼리버드 포인트 적립 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


function gold_rush_2018_gift_item_cancle($od_id,$mb_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    $chk = sql_fetch("
            SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'gold_rush_2018'
                  AND ev_data_type = 'gift_item'
                  AND value1 = '{$mb_id}'
                  AND value3 = '{$od_id}'
            ");
    if($chk['cnt']<=0){
        return false;
    }
    $slq  = sql_query("
            SELECT uid, value3 AS od_id
            FROM yc4_event_data
            WHERE     ev_code = 'gold_rush_2018'
                  AND ev_data_type = 'gift_item'
                  AND value1 = '{$mb_id}'
                  AND value3 = '{$od_id}'
            
            ");

    while ($row =sql_fetch_array($slq)){
        if($row['uid']&& $row['od_id']){

            sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='{$row['uid']}'");

        }
    }

    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 싸이베이션 경품 이벤트 당첨자 사은품 지급 취소') WHERE od_id='{$od_id}'");

    return true;
}

function jarrowformulas_201807_giftitme_cancel($od_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id= sql_safe_query($od_id);

    $ev_chk = sql_fetch("
            SELECT uid, value4 as it_id
            FROM yc4_event_data
            WHERE     ev_code = 'jarrow_201807'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."'
                  AND value5 IS NULL
                ");

    if(!$ev_chk || !$ev_chk['uid'] || !$ev_chk['it_id']) {
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 07월 자로우 사은품 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value5= 'CANCEL'
            WHERE uid='".$ev_chk['uid']."'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'jarrow_201807' AND yc4_event_data.ev_data_type = 'gift' and value1 = '".$ev_chk['it_id']."'
                                    ");
    return true;

}

function summer_80usd_3000point_201807_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'summer2018_point'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 여름 이벤트 80$이상 결제시 3000포인트 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    return true;
}

function nordic_free_gift_2018_amount_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'nordic_1807_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 07월 노르딕 기획전 $90이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_1807_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}


//노르딕 이벤트 노르딕 상품 구매시 마이아더백 지급 취소
function nordic_gift_item_bag_201808_cancel($od_id){


    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id= sql_safe_query($od_id);

    $ev_chk = sql_fetch("
            SELECT uid, value4 as it_id
            FROM yc4_event_data
            WHERE     ev_code = 'nordic_201808'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."'
                  AND value5 IS NULL
                ");

    if(!$ev_chk || !$ev_chk['uid'] || !$ev_chk['it_id']) {
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 8월 노르딕 이벤트 마이아더백 사은품 지급')
            WHERE od_id='".$od_id."'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value5= 'CANCEL'
            WHERE uid='".$ev_chk['uid']."'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_201808' AND yc4_event_data.ev_data_type = 'gift' and value1 = '".$ev_chk['it_id']."'
                                    ");
    return true;

}

function jarrow_member_price_item_event_data_cancel($od_id) {

    /**
    원래 코드 : jarrow_mem_dc_1807

     **/

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  count(*) cnt
    from yc4_event_data
    where ev_code = 'jarrow_mem_dc_1907'
    and ev_data_type = 'od_id'
    and value2 = '".$od_id."'
    and value7 is null
    ");

    if(!$event_chk  || $event_chk['cnt'] < 1){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 7월 게릴라이벤트 자로우상품 5%할인 적용 취소')
            WHERE od_id='{$od_id}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value7 = now()
            where ev_code = 'jarrow_mem_dc_1907'
    and ev_data_type = 'od_id'
    and value2 = '".$od_id."'
            ");

    return true;
}


//게릴라 이벤트 8월 5일 50불이상 구매자중 당첨자 사은품 지급취소
function guerrilla_2018_gift_item_cancle($od_id,$mb_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    $chk = sql_fetch("
            SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'guerrilla_2018'
                  AND ev_data_type = 'gift_item'
                  AND value1 = '{$mb_id}'
                  AND value3 = '{$od_id}'
            ");
    if($chk['cnt']<=0){
        return false;
    }
    $slq  = sql_query("
            SELECT uid, value3 AS od_id
            FROM yc4_event_data
            WHERE     ev_code = 'guerrilla_2018'
                  AND ev_data_type = 'gift_item'
                  AND value1 = '{$mb_id}'
                  AND value3 = '{$od_id}'
            
            ");

    while ($row =sql_fetch_array($slq)){
        if($row['uid']&& $row['od_id']){

            sql_query("UPDATE yc4_event_data SET value3=null WHERE uid='{$row['uid']}'");

        }
    }

    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 게릴라 이벤트 경품 이벤트 당첨자 사은품 지급 취소') WHERE od_id='{$od_id}'");

    return true;
}

//2018 얼리버드 추석이벤트 $60불이상, $100불이상 구매시 2000, 4000포인트 적립 취소
function early_chuseok_2018_60100_point_cancel($od_id){

    //주문 번호 체크
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("select uid,value4  from yc4_event_data where ev_code ='chupo_100_5_2018' and ev_data_type = 'od_id' and value1 = '".$od_id."' and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018 얼리버드 추석이벤트(" . $event_data['value4'] . " 점)포인트 적립 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2018 얼리버드 추석이벤트 $100불이상 구매시 5000원 할인 취소
function early_chuseok_2018_100_dc_cancel($od_id,$mb_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid,value1 AS od_id FROM yc4_event_data WHERE ev_code='chudc_100_5_2018' AND ev_data_type='od_id' AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018 얼리버드 추석이벤트 100불이상 구매시 할인 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


//2018 얼리버드 추석이벤트 선결제 포인트권 취소
function early_chuseok_2018_cancel($od_id, $mb_id){

    $event_name = (date('Ymd')>'20190901') ? "" : "얼리버드" ;


    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }
    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }
    $od_id = sql_safe_query($od_id);
    $ev		= sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='prepay_19_echu' AND ev_data_type='od_id' AND value1='".$od_id."' AND value6 IS NULL");
    if(!$ev['uid']){
        return false;
    }
    // 주문서 메모
    sql_query("UPDATE yc4_event_data SET value6='CANCEL' WHERE uid='".$ev['uid']."'");
    sql_query("UPDATE yc4_order SET od_shop_memo=CONCAT(od_shop_memo,'\\n','2019년 ".$event_name." 추석 선결제상품 자동적립 취소') WHERE od_id='".$od_id."'");

    return true;
}


//2018년 08월 노르딕 상품 100불이상 구매, 실결제 금액도 100불 이상시 사은품 지급
function nordic_100_gift_item_event_201808_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'nordic_1808_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 08월 노르딕 기획전 $100이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_1808_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//노르딕 이벤트 2+1취소201808
function nordic_208008_2_1_cancel($od_id){

    $od_id = sql_safe_query($od_id);

    $event_item = sql_query("
            SELECT value3, uid, value7
            FROM yc4_event_data
            WHERE     ev_code = 'nordic_2p1_1809'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."' 
                  and value5 is null
                ");

    if(!$event_item){
        return false;
    }

    while($row = sql_fetch_array($event_item)){

        if(!$row['value3'] || !$row['uid'] || !$row['value7'] ){
            return false;
        }

        //수량
        sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - ".(int)$row['value7']." WHERE value1 = '".$row['value3']."' and  ev_code = 'nordic_2p1_1809' AND ev_data_type = 'item'");

        //취소
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid = '".$row['uid']."' ");

        $ev_qty_chk	= sql_fetch("
              SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'nordic_2p1_1809'
                  AND ev_data_type = 'item'
                  AND CAST(value2 AS int) > CAST(value3 as int) and value1 = '".$row['value3']."'
        ");
        if($ev_qty_chk['cnt'] >0){
            sql_query("UPDATE yc4_item SET it_stock_qty = 9999 WHERE it_id='" . $row['value3'] . "'");
            sql_query("UPDATE yc4_event_data SET value5 =null WHERE value1 = '".$row['value3']."' and  ev_code = 'nordic_2p1_1809' AND ev_data_type = 'item'");
        }
    }

    return true;

}


//2018 추석이벤트 $60불이상, $100불이상 구매시 3000, 5000포인트 적립 취소
function chuseok_2018_60100_point_cancel($od_id){

    //주문 번호 체크
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("select uid,value4  from yc4_event_data where ev_code ='chupo2_100_5_2018' and ev_data_type = 'od_id' and value1 = '".$od_id."' and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018 추석이벤트(" . $event_data['value4'] . " 점)포인트 적립 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2018 09 자로우 이벤트 상품 30불 이상 구매시 사은품 지급 취소
function jarrow_free_gift_201809_amount_cancel($od_id){
    /*
     * 적용 파일 : 관리자
     * */
    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id 
    from yc4_event_data
    where ev_code = 'jarrow_1809_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 09월 자로우 기획전 $30이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'jarrow_1809_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//2018 추석 연휴 이벤트 결제 금액에 5% 포인트 적립 취소처리
function chuseok_2018_event_5point_cancel($od_id){

    /*
     * 적용 파일: 오플관리자 /ordercartupdate.php
     */

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id	= sql_safe_query($od_id);

    $ev_chk	= sql_fetch("SELECT uid, value4 AS point FROM yc4_event_data WHERE ev_code='2018chu_point_09' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value7 IS NULL");

    if($ev_chk['point'] < 1){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo, '\\n', '2018년 추석 연휴 이벤트 5%(" . $ev_chk['point'] . " 점) 포인트 취소') WHERE od_id='" . $od_id . "'");

    return true;

}

//2018 블랙프라이데이 100불 이상 구매 시 10000원 할인 취소
function pre_bf_2018_100_10000dc_cancel($od_id,$mb_id){

    //주문서
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    //회원
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid,value1 AS od_id FROM yc4_event_data WHERE ev_code='prebf_100_10000' AND ev_data_type='od_id' AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018 블랙프라이이벤트 100불이상 구매시 할인 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2018년 08월 노르딕 상품 100불이상 구매, 실결제 금액도 100불 이상시 사은품 지급
function pbf_nordic_100_gift_item_event_201810_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'pbfnor_1810_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 프리블프 노르딕 기획전 $100이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'pbfnor_1810_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//2018년 11월 할로윈이벤트 상품 60불이상 구매시 사은품(사탕) 지급 취소
function hlw_60_gift_item_event_201811_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'hlw_1811_60_gift'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    // 주문서 메모
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2018년 할로윈 이벤트 $60이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    // 이벤트 데이터 복구
    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    //사은품 소모량 -1
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'hlw_1811_60_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//국민카드 100불이상 구매시 5프로 포인트 적립 2018-10-31 취소
function kb_201811_100_5_point_cancel($od_id){

    /*
     * 적용 파일: 오플관리자 /ordercartupdate.php
     */

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id	= sql_safe_query($od_id);

    $ev_chk	= sql_fetch("SELECT uid, value4 AS point FROM yc4_event_data WHERE ev_code='kb_1811_100_5point' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value7 IS NULL");

    if($ev_chk['point'] < 1){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo, '\\n', '2018년 11월 국민카드 이벤트 5%(" . $ev_chk['point'] . " 점) 포인트 취소') WHERE od_id='" . $od_id . "'");

    return true;

}

//2018년 11월 브랜드 메가 세일 이벤트 해당되는 브랜드 상품 60불이상 구매시 3천 포인트 적립 취소
function brand_mega_sale_2018_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='mega_60_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 11월 메가 세일 이벤트 60불이상 결제시(3,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


//블랙프라이데이 노르딕 1+1 이벤트 2018.11.23~2018.11.25 이벤트 상품 취소
function bf_nordic_1811_1_plus1_cancel($od_id){
    /* 블랙프라이데이 노르딕 1+1 이벤트 2017-11-24~2017-11-26
* ev_code		: bf_nordic_1p1_1811
* ev_data_type	: item	/ mb_id
* value1			: 오플상품코드		    / 주문번호
* value2			: 준비된 사은품 갯수	/ 아이디
* value3			: 나간 사은품 갯수 	    / 구매상품
* value4			: 가격                  /
* value5			:                       / 취소
* value6			:
* value7			:
* 적용 파일      : // 주문취소시 (고객)         orderinquirycancel.php // 주문취소시(관리자)        ordercartupdate.php
*/
    $od_id = sql_safe_query($od_id);

    $event_item = sql_query("
            SELECT value3, uid
            FROM yc4_event_data
            WHERE     ev_code = 'bf_nordic_1p1_1811'
                  AND ev_data_type = 'mb_id'
                  AND value1 = '".$od_id."' 
                ");

    if(!$event_item){
        return false;
    }

    while($row = sql_fetch_array($event_item)){

        if(!$row['value3'] || !$row['uid']){
            return false;
        }

        //수량
        sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - 1 WHERE value1 = '".$row['value3']."' and  ev_code = 'bf_nordic_1p1_1811' AND ev_data_type = 'item'");

        //취소
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid = '".$row['uid']."' ");

        $ev_qty_chk	= sql_fetch("
              SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'bf_nordic_1p1_1811'
                  AND ev_data_type = 'item'
                  AND CAST(value2 AS int) > CAST(value3 as int) and value1 = '".$row['value3']."' and value5 = 'SOLDOUT' 
        ");
        if($ev_qty_chk['cnt'] >0){
            sql_query("UPDATE yc4_item SET it_stock_qty = 9999 WHERE it_id='" . $row['value3'] . "'");
            sql_query("UPDATE yc4_event_data SET value5 =null WHERE value1 = '".$row['value3']."' and  ev_code = 'bf_nordic_1p1_1811' AND ev_data_type = 'item'");
        }

    }

    return true;
}

function cyber_monday_80_5000_2018_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='cy_201811_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 11월 사이먼데이 이벤트 80불이상 결제시(5,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


function pick_item_100_point_2018_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='pick_2018_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 11월 30일 골라담기이벤트 100불이상 결제시 11%(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

// 골라담기 이벤트 골라담기 상품 100불이상 구매 시 12% 적립 취소 : @choice_20191202
function pick_item_100_point_2019_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='pick_2019_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 12월 02일 골라담기이벤트 100불이상 결제시 12%(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


function best_awards_80_3000_2018_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='best_201812_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 12월 베스트 어워즈 이벤트 80불이상 결제시(3,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//노르딕 스페셜 이벤트 노르딕 상품 100불이상 구매, 실결제 금액도 100불 이상시 사은품 지급
function nordic_special_100_gift_item_event_201812_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'nordicsp_1812_item'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','연말 노르딕 스페셜 기획전 $100이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordicsp_1812_item' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//메리 자로우 포뮬러스 이벤트 자로우도피러스 120캡슐 포함 된 주문건 2000포인트 증정
function merry_jarrow_formulas_201812_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='Jarrow_201812_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 MERRY JARROW FORMULAS 이벤트 상품 포함 주문서 결제시(2,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2018년 크리스마스 이벤트 100불이상 결제시 10,000 포인트 지급 취소
function christmas_ev_2018_100usd_10000po_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='chrismas_2018' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 크리스마스 이벤트 100불이상 결제시(10,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


function xtend_ev_point_40_100_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='xtend_40_100_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','엑스텐드 포인트 이벤트 ".$event_data['value5']."불이상 결제시(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


//2019 더블해택 이벤트 100불이상 주문시 3% 할인 5%적립 취소
function double_ev_2019_100_3dc_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid,value1 AS od_id,value5 FROM yc4_event_data 
            WHERE ev_code='double_2019_100' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$chk['value5'].",od_shop_memo = CONCAT(od_shop_memo,'\\n','2019 더블해택이벤트 100불이상 구매시 할인,적립 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


//2018년 12월 실적이벤트 300불이상 500불이상 실적 대상자 3%,5% 적립 취소
function month_buy_history_event_201812_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201812' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2018년 12월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소 ') WHERE od_id='" . $od_id . "'");

    return true;
}
//2019년 플로렉스 뉴트리션 이벤트 해당상품 1개라도 구매 시 2000포인트 지급 취소
function florex_201901_1cnt_2000point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='florex_201901_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 플로렉스 뉴트리션 이벤트 상품 포함 주문서 결제시(2,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2019년 얼리버드 설날 이벤트 120불 이상 구매 시 5000포인트 지급 취소
function early_bird_new_year_dc_2019_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='newyear_19_1_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 얼리버드 설날 이벤트 120불이상 결제시(5,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

function new_year_present_80_3000point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='newyear_19_80_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 설날 선물 대잔치이벤트 80불이상 결제시(3,000) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2019년 설 선물 대잔치 이벤트 선물 세트 구매시 개수만큼 쇼핑백 취소
function new_year_present_set_gift_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    //이벤트
    $chk = "SELECT uid,value3 AS it_id FROM yc4_event_data WHERE ev_code='newyear_19_80_gi' AND ev_data_type='od_id' AND value1 = '".$od_id."' and value5 is null";
    //이벤트체크
    $result		= sql_query($chk);

    $set_chk_data		= array();
    while($row = sql_fetch_array($result)){
        $set_chk_data[] = $row;
    }

    if(count($set_chk_data) < 1){
        return false;
    }

    foreach ($set_chk_data as $value){

        // 사은품 지급갯수 수정
        sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='newyear_19_80_gi' AND ev_data_type='gift' AND value1='".$value['it_id']."' ");

        // 이벤트 데이터 복구
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid='".$value['uid']."'");

    }

    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 설날 선물 대잔치이벤트 사은품 지급 취소') WHERE od_id='".$od_id."'");

    return true;

}

//2019년 1월 노르딕 이벤트 60불 이상 치코백 증정 취소 60불이상
function nordic_special_120_gift_item_event_201901_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'nordicsp_1901_item'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 1월 노르딕 기획전 $60이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordicsp_1901_item' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}


//2019년 레전더리밀크 이벤트 해당상품 1개라도 구매 시 2000포인트 지급 취소
function legendairy_milk_2019_1cnt_2000point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='milk_1cnt_2po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 레전더리밀크 이벤트 상품 포함 주문서 결제시(2,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2019년 설날 연휴이벤트 60불이상 3000포인트 지급 취소
function new_year_2019_60usd_3000point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='newyear_19_60_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 설날 연휴이벤트 60불이상 결제시(3,000) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2019-02 센트룸 기획전 -기획전에 해당상품 40불 이상 구매시 2000포인트 지급 취소
function pfizer_ev201902_point_20_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='pfizer_201902_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 02월 센트룸 이벤트 제품 ".$event_data['value5']."불이상 결제시(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//20190223 D구미 상품 60개 한정 판매 취소 처리
function saturday_20190223_Dgumi_cancel($od_id){

    $od_id = sql_safe_query($od_id);

    $event_item = sql_query("
            SELECT value3, uid, value7
            FROM yc4_event_data
            WHERE     ev_code = 'dgumi_20190223'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."' 
                  and value5 is null
                ");

    if(!$event_item){
        return false;
    }

    while($row = sql_fetch_array($event_item)){

        if(!$row['value3'] || !$row['uid'] || !$row['value7'] ){
            return false;
        }

        //수량
        sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - ".(int)$row['value7']." WHERE value1 = '".$row['value3']."' and  ev_code = 'dgumi_20190223' AND ev_data_type = 'item'");

        //취소
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid = '".$row['uid']."' ");

        $ev_qty_chk	= sql_fetch("
              SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'dgumi_20190223'
                  AND ev_data_type = 'item'
                  AND CAST(value2 AS int) > CAST(value3 as int) and value1 = '".$row['value3']."' and value5 = 'SOLDOUT'
        ");
        if($ev_qty_chk['cnt'] >0){
            sql_query("UPDATE yc4_item SET it_stock_qty = 9999 WHERE it_id='" . $row['value3'] . "'");
            sql_query("UPDATE yc4_event_data SET value5 =null WHERE value1 = '".$row['value3']."' and  ev_code = 'dgumi_20190223' AND ev_data_type = 'item'");
        }
    }

    return true;

}


//2019년 매주 금요일 15시~24시 50불이상 주문결제 이벤트 2000포인트 지급 , 헬스관 이벤트상품 포함 시 1000포인트 추가지급
function friday_17h_24h_50usd_2000point_20190301_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='fday_17h_20190301' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 매주 금요일 15시~24시 50불이상 주문결제 이벤트 (".$event_data['value4'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    return true;
}


//20190311 cD구미 상품 400개 한정
function nordic_20190311_gift_item_ev_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'dcgumi_20190311'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 노르딕 DC구미 랜덤증정 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'dcgumi_20190311' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}


//2019년 노르딕 이벤트 100불이상 10000포인트 지급
function nordic_20190311_10000point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='nordic_20190311' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 노르딕 100불이상 결제 이벤트 (".$event_data['value4'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    return true;
}


//하나카드이벤트 포인트 지급 취소
function hana_lms_point_20190326_evinsert_point_insert_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='hana999_20190327' 
            and ev_data_type = 'point' 
            and value1 = '".$od_id."' 
            and value7 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value7 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 3월 O2O하나카드 이벤트 (".$event_data['value4'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

function hana_20190327_item999_soldout_cancel($od_id){

    $od_id = sql_safe_query($od_id);

    $event_item = sql_query("
            SELECT value3, uid, value7
            FROM yc4_event_data
            WHERE     ev_code = 'hana999_20190327'
                  AND ev_data_type = 'mb_id'
                  AND value1 = '".$od_id."' 
                  and value5 is null
                ");

    if(!$event_item){
        return false;
    }

    while($row = sql_fetch_array($event_item)){

        if(!$row['value3'] || !$row['uid'] || !$row['value7'] ){
            return false;
        }

        //수량
        sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - ".(int)$row['value7']." WHERE value1 = '".$row['value3']."' and  ev_code = 'hana999_20190327' AND ev_data_type = 'item'");

        //취소
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid = '".$row['uid']."' ");

        $ev_qty_chk	= sql_fetch("
              SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'hana999_20190327'
                  AND ev_data_type = 'item'
                  AND CAST(value2 AS int) > CAST(value3 as int) and value1 = '".$row['value3']."' and value5 = 'SOLDOUT'
        ");
        if($ev_qty_chk['cnt'] >0){
            sql_query("UPDATE yc4_item SET it_stock_qty = 9999 WHERE it_id='" . $row['value3'] . "'");
            sql_query("UPDATE yc4_event_data SET value5 =null WHERE value1 = '".$row['value3']."' and  ev_code = 'hana999_20190327' AND ev_data_type = 'item'");
        }
    }

    return true;

}


function april_fools_day_20190401_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $od = sql_fetch("
    select mb_id,
           on_uid
    from yc4_order 
    where od_id = '".$od_id."'
    ");

    if(!$od || !$od['mb_id'] || $od['mb_id'] == '' || $od['mb_id'] == '비회원' ){
        return false;
    }

    $event_data = sql_fetch("
            select uid,value3  
            from yc4_event_data 
            where ev_code ='foolsday_190401' 
            and ev_data_type = 'mb_id' 
            and value1 = '".$od['mb_id']."' 
            and value5 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value3'] || $event_data['value3'] == '') {
        return false;
    }

    if($event_data['value3'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value5 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 04월 01일 만우절 결제 이벤트 (".$event_data['value3'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'foolsday_190401' AND yc4_event_data.ev_data_type = 'point' and value1 = '".$event_data['value3']."'
                                    ");

    return true;
}


//나우푸드 이벤트 나우푸드 상품 40불이상 구매 시 2000포인트 적립
function now_foods_201904_40usd_2000point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='nowfoods_201904' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 04월 나우푸드 이벤트 제품 ".$event_data['value5']."불이상 결제시(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


//2019년 03월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
function month_buy_history_event_201904_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201904' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 03월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소 ') WHERE od_id='" . $od_id . "'");

    return true;
}


////201904 하나카드 50불 3프로적립 취소
function hana_every_50usd_3point_201904_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id	= sql_safe_query($od_id);

    $ev_chk	= sql_fetch("SELECT uid, value4 AS point FROM yc4_event_data WHERE ev_code='hana_201904_3po' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value7 IS NULL");

    if($ev_chk['point'] < 1){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo, '\\n', '2019년 05월 하나카드 이벤트 3%(" . $ev_chk['point'] . " 점) 포인트 취소') WHERE od_id='" . $od_id . "'");

    return true;

}


//하나카드 10불 이벤트 판매 취소 (품절취소처리)
function hana_bigdata_2019_soldout_cancel($od_id){

    $od_id = sql_safe_query($od_id);

    $event_item = sql_query("
            SELECT value3, uid, value4
            FROM yc4_event_data
            WHERE     ev_code = 'hana_bigdata_2019'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."' 
                  and value5 is null
                ");

    if(!$event_item){
        return false;
    }

    while($row = sql_fetch_array($event_item)){

        if(!$row['value3'] || !$row['uid'] || !$row['value4'] ){
            return false;
        }

        //동일한 상품코드인 애들 체크해줘야함

        $event_sql = sql_query($a= "select uid from yc4_event_data where ev_code = 'hana_bigdata_2019' 
                            and ev_data_type = 'ten_shop_info' 
                            and date_format(value1, '%y%m%d') <= '".substr($od_id,0,6)."'
                            and date_format(value2, '%y%m%d') >= '".substr($od_id,0,6)."'");

        while($row_item = sql_fetch_array($event_sql)){
            $uids[] = $row_item['uid'];
        }

        $event_uid_item = sql_fetch($a= "
                                select uid from yc4_event_data 
                                where 
                                ev_data_type in (".implode(",",$uids).")
                                and value1 = '".$row['value3']."'
                                ");


        //수량
        sql_query($a = "UPDATE yc4_event_data SET value5 = CAST(value5 AS int) - ".(int)$row['value4']." WHERE uid = '".$event_uid_item['uid']."'");

        //취소
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid = '".$row['uid']."' ");

        $ev_qty_chk	= sql_fetch("
              SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'hana_bigdata_2019'
                  AND CAST(value4 AS int) > CAST(value5 as int) and uid = '".$event_uid_item['uid']."'
        ");

        if($ev_qty_chk['cnt'] >0){
            sql_query("UPDATE yc4_item SET it_stock_qty = 9999 WHERE it_id='" . $row['value3'] . "'");
        }
    }

    return true;

}



//2019년 4월 노르딕 이벤트 60불 이상 치코백 증정 취소 60불이상
function nordic_60usd_giftitem_bag_201904_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'nordicsp_1904_item'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 4월 노르딕 기획전 $60이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordicsp_1904_item' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}


//2019년 얼리버드 가정의달 이벤트 100불이상 5천 포인트 지급 취소
function early_national_family_month_5000point_cancel_201904($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='e_family_1904' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 얼리버드 가정의달 결제 이벤트 (".$event_data['value4'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

//2019년 얼리버드 가정의달 이벤트 특정상품 갯수 만큼 쇼핑백 지급 취소
function early_national_family_month_gift_cancel_201904($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));


    $chk = "SELECT uid,value3 AS it_id FROM yc4_event_data WHERE ev_code='e_family_1904_item' AND ev_data_type='od_id' AND value1 = '".$od_id."' and value5 is null";

    $result		= sql_query($chk);

    $set_chk_data		= array();
    while($row = sql_fetch_array($result)){
        $set_chk_data[] = $row;
    }

    if(count($set_chk_data) < 1){
        return false;
    }

    foreach ($set_chk_data as $value){

        // 사은품 지급갯수 수정
        sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='e_family_1904_item' AND ev_data_type='gift' AND value1='".$value['it_id']."' ");

        // 이벤트 데이터 복구
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid='".$value['uid']."'");

    }

    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 얼리버드 가정의달 사은품 이벤트 사은품 지급 취소') WHERE od_id='".$od_id."'");

    return true;

}

//2019년  가정의달 이벤트 120불이상 5천,70불이상 3천 포인트 지급 취소
function national_family_month_30005000po_201905_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='family_1905' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 가정의달 결제 이벤트 (".$event_data['value4'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    return true;
}


//게릴라 이벤트 80불이상 5%프로 적립 취소
function national_family_month_guerrilla_80_5po_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='family_1905_rilla' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 가정의달 게릴라 이벤트 (".$event_data['value4'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

//싸이베이션 60불이상 2000포인트 증정 취소
function scivation_60usd_2000po_201905_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='scivation_1905' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 05월 싸이베이션 이벤트 (".$event_data['value4'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

//싸이베이션 특정상품 구매시 보틀 증정
function scivation_1bottle_1order_201905_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'siva_1905_item'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 5월 쉐이커 보틀증정 이벤트 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'siva_1905_item' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//2019년 가정의달 이벤트 특정상품 갯수 만큼 쇼핑백 지급 취소
function national_family_month_gift_cancel_201905($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));


    $chk = "SELECT uid,value3 AS it_id FROM yc4_event_data WHERE ev_code='family_1905_item' AND ev_data_type='od_id' AND value1 = '".$od_id."' and value5 is null";

    $result		= sql_query($chk);

    $set_chk_data		= array();
    while($row = sql_fetch_array($result)){
        $set_chk_data[] = $row;
    }

    if(count($set_chk_data) < 1){
        return false;
    }

    foreach ($set_chk_data as $value){

        // 사은품 지급갯수 수정
        sql_query("UPDATE yc4_event_data SET value3=CAST(value3 AS int)-1 WHERE ev_code='family_1905_item' AND ev_data_type='gift' AND value1='".$value['it_id']."' ");

        // 이벤트 데이터 복구
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid='".$value['uid']."'");

    }

    // 주문서 메모
    sql_query("UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 가정의달 사은품 이벤트 사은품 지급 취소') WHERE od_id='".$od_id."'");

    return true;

}

//2019년 04월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
function month_buy_history_event_201905_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201905' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 04월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소 ') WHERE od_id='" . $od_id . "'");

    return true;
}


//가정의달 게릴라 이벤트 랜덤으로 500~100000 포인트 지급 초콜릿상품포함시 추가1000포인트 지급
function family_month_20190515_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $od = sql_fetch("
    select mb_id,
           on_uid
    from yc4_order 
    where od_id = '".$od_id."'
    ");

    if(!$od || !$od['mb_id'] || $od['mb_id'] == '' || $od['mb_id'] == '비회원' ){
        return false;
    }

    $event_item_chk = sql_fetch("
            SELECT sum(a.ct_qty) cnt
            FROM yc4_cart a 
            WHERE a.on_uid = '".$od['on_uid']."'
          and it_id in (
'1510644019', '1510679823', '1510679723', '1510643219', '1510643519', 
'1510643719', '1511506677', '1511506777', '1511506877', '1511506977', 
'1511003815', '1511003915', '1511004015', '1511003715', '1511003615', 
'1511003515', '1511014815', '1511143115', '1511115313', '1511114613', 
'1510778382', '1511115213', '1503190124', '1511186019', '1511185919', 
'1511114713', '1511261047', '1503184739', '1511186119', '1503185822', 
'1511114813', '1511115113', '1511115013', '1503190031', '1511114913', 
'1503185405', '1511271848', '1503190335', '1503190223', '1510644519', 
'1411223956', '1339600034', '1339600742', '1353364618', '1353365509', 
'1353366530', '1510826615', '1510826715', '1510716627', '1510716727', 
'1510716427', '1510826515', '1510933915', '1510933715', '1510759781', 
'1510759881', '1510766181', '1510950715', '1510635749', '1511394063', 
'1510725727', '1510659019', '1510676823', '1510719127', '1398838165', 
'1412094918', '1511427063', '1412113540', '1511433463', '1418277341', 
'1511150215', '1511428063', '1511428663', '1511433563', '1510773182', 
'1503100325', '1370116926', '1511150415', '1511429663', '1511455177', 
'1511107813', '1511150315', '1511428763', '1511150715', '1511429163', 
'1511433863', '1511427963', '1412112315', '1511427563', '1511405363', 
'1511429363', '1511429463', '1511471177', '1510302615', '1511150615', 
'1511433763', '1511428563', '1511427363', '1511426863', '1511405563', 
'1511427863', '1511433663', '1385086007', '1501100351', '1511150915', 
'1511431763', '1511429563', '1511427263', '1503102137', '1511151015', 
'1385086295', '1412102521', '1511476077', '1511426963', '1511427163', 
'1511427463', '1511150515', '1412092540', '1511151115', '1510525913', 
'1510681923', '1511426763', '1511428963', '1511429063', '1511405063', 
'1511454277', '1511427763', '1511428463', '1510720927', '1511429263', 
'1511427663', '1511150815', '1511471377', '1511431663', '1511428863', 
'1398837779', '1510452415', '1412111124', '1511471277', '1412093222', 
'1511110813', '1501131026', '1501132126', '1510721127', '1370117257', 
'1511138715', '1511138915', '1511139215', '1511139315', '1511139415', 
'1511139615', '1510682723', '1511423463', '1511173415', '1511247247', 
'1511247147', '1511174215', '1511246847', '1511301462', '1511173315', 
'1511169815', '1511259747', '1511169615', '1511398663', '1511173115', 
'1511259447', '1511221923', '1511260047', '1511174115', '1511259847', 
'1511173615', '1511259647', '1511222123', '1511174415', '1511174315', 
'1511259947', '1511169715', '1511246947', '1511222023', '1511173015', 
'1511172915', '1511430263', '1511430163', '1511273148', '1511273048', 
'1511396863', '1511039720', '1511439463', '1511169415', '1511169515', 
'1511222323', '1511222223', '1511169315', '1510417515', '1511123313', 
'1511123613', '1511123213', '1511054620', '1511054320', '1511054520', 
'1511054420', '1511048720', '1511048520', '1511048020', '1511048220', 
'1511048620', '1510648619', '1510798782', '1510531113', '1510705523', 
'1510531313', '1510542216', '1511176615', '1510706823', '1511267247', 
'1510705823', '1510533213', '1510552816', '1510543116', '1510533113', 
'1510706023', '1510705923', '1510530913', '1510543216', '1510706123', 
'1510531013', '1511175015', '1510729227', '1510728927', '1510729127', 
'1357253997')
                ");

    $point_amount = 0;
    if($event_item_chk['cnt'] > 0){
        $point_amount =1000;
    }

    $event_data = sql_fetch("
            select uid,value3  
            from yc4_event_data 
            where ev_code ='fmonth_190515' 
            and ev_data_type = 'mb_id' 
            and value1 = '".$od['mb_id']."' 
            and value5 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value3'] || $event_data['value3'] == '') {
        return false;
    }

    if($event_data['value3'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value5 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 05월 15일 가정의달 게릴라 이벤트 (".$event_data['value3'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'fmonth_190515' AND yc4_event_data.ev_data_type = 'point' and value1 = ".$event_data['value3']."-$point_amount
                                    ");

    return true;
}

//노르딕 1+1 작업
function nordic_20190520_1puls1_cancel($od_id){

    $od_id = sql_safe_query($od_id);

    $event_item = sql_query("
            SELECT value3, uid, value7
            FROM yc4_event_data
            WHERE     ev_code = 'nordic_20190520'
                  AND ev_data_type = 'od_id'
                  AND value1 = '".$od_id."' 
                  and value5 is null
                ");

    if(!$event_item){
        return false;
    }

    while($row = sql_fetch_array($event_item)){

        if(!$row['value3'] || !$row['uid'] || !$row['value7'] ){
            return false;
        }

        //수량
        sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - ".(int)$row['value7']." WHERE value1 = '".$row['value3']."' and  ev_code = 'nordic_20190520' AND ev_data_type = 'item'");

        //취소
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid = '".$row['uid']."' ");

        $ev_qty_chk	= sql_fetch("
              SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'nordic_20190520'
                  AND ev_data_type = 'item'
                  AND CAST(value2 AS int) > CAST(value3 as int) and value1 = '".$row['value3']."' and value5 = 'SOLDOUT'
        ");
        if($ev_qty_chk['cnt'] >0){
            sql_query("UPDATE yc4_item SET it_stock_qty = 9999 WHERE it_id='" . $row['value3'] . "'");
            sql_query("UPDATE yc4_event_data SET value5 =null WHERE value1 = '".$row['value3']."' and  ev_code = 'nordic_20190520' AND ev_data_type = 'item'");
        }
    }

    return true;

}


//201906 우리카드 20명 선착순 80불이상 토요일10불,5불 할인 이벤트 취소
function wooribank_201906_80_dc_cancel($od_id){ //곽범석 작업
    /* 적용 파일  : 카드 결제 실패 시 settle_authorize_result,settle_authorize_result_mp
    */

    $od_id	= sql_safe_query($od_id);

    $ev_chk	= sql_fetch("
            SELECT uid, 
            value6 AS dc_amount, 
            value4 AS dc_usd 
            FROM yc4_event_data 
            WHERE ev_code='woori2019_80_10_5_dc' 
            AND ev_data_type='mb_id' 
            AND value1='" . $od_id . "' 
            AND value7 IS NULL");
    if(!$ev_chk['uid']){
        return false;
    }

    if($ev_chk['dc_amount'] < 1){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    sql_query("UPDATE yc4_order SET od_dc_amount=CAST(od_dc_amount AS int) - " . (int)$ev_chk['dc_amount'] . ", od_shop_memo=CONCAT(od_shop_memo, '\\n', '우리카드 선착순20명 80불이상 주문시  ".$ev_chk['dc_usd']."불 할인 취소') WHERE od_id='" . $od_id . "'");

    return true;
}


//2019 05 27 5일간 메모리얼 기념 할인 이벤트 100불이상 5불 할인 취소
function memorial_ev_201905_100_5usddc_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("
            SELECT uid,
            value1 AS od_id,
            value5 
            FROM yc4_event_data 
            WHERE ev_code='memorial_1905' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$chk['value5'].",od_shop_memo = CONCAT(od_shop_memo,'\\n','2019 05월 메모리얼데이 기념 할인 이벤트 100불이상 구매시 5불(".$chk['value5'].") 할인취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//라이프 익스 텐션 30불이상 2000포인트 취소
function life_extension_30usd_2000point_20190601_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='lifeex_201906' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 06월 라이프 익스텐션 이벤트 제품 30불이상 결제시 2000포인트 지급 취소(기한 20190815까지)') WHERE od_id='" . $od_id . "'");

    return true;
}


//20190601 원데이 이벤트 100불이상 5000포인트 지급 취소
function oneday_100usd_5000point_20190601_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='oneday_20190601' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 06월 01일 원데이 이벤트 100불이상 5000포인트 지급(기한 20190710까지)') WHERE od_id='" . $od_id . "'");

    return true;
}

//2019년 05월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
function month_buy_history_event_201906_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201906' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 05월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소 ') WHERE od_id='" . $od_id . "'");

    return true;
}

function icepack_add_2019_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk   = sql_fetch("SELECT uid FROM yc4_event_data WHERE ev_code='ice_2019_add' AND value1='" . $od_id . "'");

    if(!$event_chk || !$event_chk['uid'] || $event_chk['uid'] ==''){
        return false;
    }

    sql_query("
            UPDATE yc4_event_data
            SET value7 = now()
            WHERE uid='{$event_chk['uid']}'
            ");

    return false;
}


function natures_way_40usd_2000point_201906_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='natures_190617' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 06월 네이쳐스웨이 이벤트 제품 40불이상 결제시 2000포인트 지급 예정(기한 20190831)') WHERE od_id='" . $od_id . "'");

    return true;

}

//트리거 포인트 경품 증정 주문 취소
function trigger_point_gift_cancel($od_id,$mb_id){

    /***
    적용 페이지
    /mall5/shop/orderinquirycancel.php + /m/shop/orderinquirycancel.php
     */

    //비회원 체크
    if($mb_id=='비회원' || !$mb_id || trim($mb_id)==''){
        return false;
    }

    //주문서 체크
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    //해당 이벤트 주문서인지 체크
    $chk = sql_fetch($a = "SELECT uid, value3 AS od_id 
                    FROM yc4_event_data 
                    WHERE ev_code='trigger_point_gift'
                    AND ev_data_type='mb_id' 
                    AND value3 = '".$od_id."' 
                    AND value1 ='".$mb_id."'");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    // 주문서 샵메모 추가
    sql_query($a = "UPDATE yc4_order SET od_shop_memo=concat(od_shop_memo,'\\n','트리거 포인트 이벤트 당첨자 경품 지급취소') WHERE od_id='".$od_id."'");


    // 이벤트 데이터 복구
    sql_query($a = "UPDATE yc4_event_data SET value3=null WHERE uid='".$chk['uid']."'");

    return true;
}

// 노르딕 2+1 우산증정 이벤트
function nordic_20190624_gift_cancel($od_id)
{
    if(!$od_id || $od_id ==""){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    //해당 주문서가 노르딕 이벤트 사은품 받았는지 체크하고
    $chk = sql_fetch($a = "select uid, value1 as od_id, value4 
                  from yc4_event_data e
                  where ev_code = 'nordic_20190624_gift'
                  and ev_data_type = 'od_id'
                  and value1 = '$od_id'
                  and value3 is null");


    if(!$chk || $chk['uid']=="" || $chk['od_id']=="" || $chk['value4']==""){
        return false;
    }

    //yc4_order shop_memo 에 남기기
    sql_query($a = "
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','노르딕 2+1 이벤트 DHA상품 구매시 사은품(우산) 지급 취소')
            WHERE od_id='{$chk['od_id']}'
            ");

    //yc4_event_data 에 취소일자 남기기
    sql_query($a = "
            UPDATE yc4_event_data
            SET value3= now()
            WHERE uid='{$chk['uid']}'
            ");

    //수량 복구하기
    sql_query($a = "UPDATE yc4_event_data
            SET value3 = cast(value3 AS int) - 1
            WHERE ev_code = 'nordic_20190624_gift' AND ev_data_type = 'gift_item' and value1 = '{$chk['value4']}'
            ");

    return true;

}


//독립기념일 실결제 100불 이상 5000포인트 지급 취소
function independence_20190701_point_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='independence_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 독립기념일 100불이상 결제 이벤트 (".$event_data['value4'].") 포인트 지급 취소(기한 20190816)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}


//2019년 06월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
function month_buy_history_event_201907_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/

 /*   if($_SERVER['REMOTE_ADDR'] != '211.214.213.101'){
        return false;
    }*/

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201907' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 06월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소 ') WHERE od_id='" . $od_id . "'");

    return true;
}

////201907 하나카드 50불 3프로적립 취소
function hana_every_50usd_3point_201907_cancel($od_id){
    /**
     * 적용 페이지 : ordercartupdate.php
     **/
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id	= sql_safe_query($od_id);

    $ev_chk	= sql_fetch("SELECT uid, value4 AS point FROM yc4_event_data WHERE ev_code='hana_201907_3po' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value7 IS NULL");

    if($ev_chk['point'] < 1){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo, '\\n', '2019년 07~08월 하나카드 이벤트 3%(" . $ev_chk['point'] . " 점) 포인트 취소(기한 20190930)') WHERE od_id='" . $od_id . "'");

    return true;

}


//나우푸드 기획전 상품 40불 주문 + 실결제 40불 이상일 경우 포인트 2000 지급 취소
function nowfoods_201907_point_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='nowfood_point_201907' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 07월 나우푸드 이벤트 제품 40불이상 결제시 2000포인트 지급 취소(기한 20190915)') WHERE od_id='" . $od_id . "'");

    return true;
}


//여름 세일 100불이상 실결제 5000포인트 지급 이벤트 취소
function summer_sale_201907_point_cancel($od_id){
    /**
     * 적용 페이지 : ordercartupdate.php
     **/

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='summer_201907_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 여름세일 100불이상 결제 이벤트 (".$event_data['value4'].") 포인트 지급 취소(기한 20190831)')
            WHERE od_id='".$od_id."'
            ");

    return true;

}


//201908 노르딕 30%할인 주문취소
function nordic_member_price_item_event_data_cancel($od_id) {


    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  count(*) cnt
    from yc4_event_data
    where ev_code = 'nordic_mem_dc_1908'
    and ev_data_type = 'od_id'
    and value2 = '".$od_id."'
    and value7 is null
    ");

    if(!$event_chk  || $event_chk['cnt'] < 1){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','게릴라이벤트 노르딕상품 30%할인 적용 취소')
            WHERE od_id='{$od_id}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value7 = now()
            where ev_code = 'nordic_mem_dc_1908'
    and ev_data_type = 'od_id'
    and value2 = '".$od_id."'
            ");

    return true;
}

//2019년 07월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
function month_buy_history_event_201908_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    //이미 취소하지 않은 대상 od_id인지 체크하기
    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201908' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    //yc4_event_data에 해당 건 취소 update
    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    //yc4_order shop_memo에 포인트 적립 취소 update
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 07월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소 ') WHERE od_id='" . $od_id . "'");

    return true;
}

function performance_auto_event_cancle($od_id){

    /**
     * 적용 페이지 : member_delivery_confirm.php
     **/

    $date_query = date('Ym');
    $date_query2 = explode('-', date('Y-m', strtotime('-1 month')));
    $year_query = $date_query2[0];
    $month_query = $date_query2[1];

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    //이미 취소하지 않은 대상 od_id인지 체크하기
    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_" . $date_query . "' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    //yc4_event_data에 해당 건 취소 update
    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    //yc4_order shop_memo에 포인트 적립 취소 update
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','" . $year_query . "년 " . $month_query . "월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소 ') WHERE od_id='" . $od_id . "'");

    return true;
}

//2019년 8월 10~11일 말복이벤트 포인트 취소 (프로모션 상품 제품 포함된 결제건이 실결제 50불 이상일 경우 2000포인트 적립)
function marbok_201908_point_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='marbok_point_201908' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order 
                SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 08월 말복 이벤트 프로모션 제품 포함 50불이상 결제시 2000포인트 지급 취소(기한 20190908)') 
                WHERE od_id='" . $od_id . "'");

    return true;
}

//2019년 8월 12~31일 써니라이프 균일가 이벤트 포인트 취소 (프로모션 상품 제품 포함된 결제건이 실결제 50불 이상일 경우 2000포인트 적립)
function sunnylife_201908_point_cancel($od_id){
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='sunnylife_point_1908' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order 
                SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 08월 써니라이프 균일가 프로모션 제품 포함 50불이상 결제시 2000포인트 지급 취소(기한 20190930)') 
                WHERE od_id='" . $od_id . "'");

    return true;
}


//노르딕 전상품 주문금액 + 실결제 90불 이상 5000포인트  지급 취소
function nordic_201908_point_cancel($od_id){
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='nordic_point_201908' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 08월 노르딕 전제품 90불이상 결제시 5000포인트 지급 취소(기한 20191020)') WHERE od_id='" . $od_id . "'");

    return true;
}


//201908 노르딕 20%할인(2+1 상품제외) 주문취소
function nordic_member_price_item_event_data_cancel_201908($od_id) {


    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  count(*) cnt
    from yc4_event_data
    where ev_code = 'nordic_mem_dc_1909'
    and ev_data_type = 'od_id'
    and value2 = '".$od_id."'
    and value7 is null
    ");

    if(!$event_chk  || $event_chk['cnt'] < 1){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','노르딕기획전 노르딕상품(2+1상품 제외) 20%할인 적용 취소')
            WHERE od_id='{$od_id}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value7 = now()
            where ev_code = 'nordic_mem_dc_1909'
    and ev_data_type = 'od_id'
    and value2 = '".$od_id."'
            ");

    return true;
}

//얼리버드 추석 실결제 60불 이상:3000, 100불이상:5000포인트 지급 취소
function early_chuseok_201908_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='early_chuseok_201908' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019 얼리버드 추석이벤트 (".$event_data['value4']."점) 포인트 지급 취소(기한 20191031)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}


//추석 실결제 70불 이상 구매시 10%포인트 지급 취소
function chuseok_201909_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='chuseok_201909_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019 추석이벤트 70불이상 결제시 결제금액 10% 포인트 지급 취소(기한 20191031)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

//2019년 08월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
function month_buy_history_event_201909_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    //이미 취소하지 않은 대상 od_id인지 체크하기
    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201909' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    //yc4_event_data에 해당 건 취소 update
    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    //yc4_order shop_memo에 포인트 적립 취소 update
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 08월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소(기간 20191031) ') WHERE od_id='" . $od_id . "'");

    return true;
}

//자로우 기획전 상품 40불 주문 + 실결제 40불 이상일 경우 포인트 2000 지급 취소
function jarrow_201909_point_cancel($od_id){
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='jarrow_point_201909' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 09월 자로우 기획전 제품 40불이상 결제시 2000포인트 지급 취소(기한 20191115)') WHERE od_id='" . $od_id . "'");

    return true;
}

//라이프 익스텐션 전상품 주문금액 + 실결제 40불 이상 3000포인트  지급 취소
function life_extend_201909_point_cancel($od_id){
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='life_point_201909' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 10월 라이프 익스텐션 전제품 40불이상 결제시 3000포인트 지급 취소(기한 20191130)') WHERE od_id='" . $od_id . "'");

    return true;
}


//임신출산 기획전 : it_id(1510623398) 구매 + 해당 기획전 상품 50불 이상 주문 + 실결제 50불 이상 : 2천포인트 지급 취소
function child_birth_201909_point_cancel($od_id){
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='child_201909_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 10월 임신출산 기획전 50불이상 결제 + 1510623398 구매시 2000포인트 지급 취소(기한 20191215)') WHERE od_id='" . $od_id . "'");

    return true;
}


//2019년 09월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
function month_buy_history_event_201910_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    //이미 취소하지 않은 대상 od_id인지 체크하기
    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201910' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    //yc4_event_data에 해당 건 취소 update
    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    //yc4_order shop_memo에 포인트 적립 취소 update
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 09월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소(기간 20191130) ') WHERE od_id='" . $od_id . "'");

    return true;
}



//2019 프리블프 할인 이벤트 100불이상 1만원 할인 취소
function preblack_2019_100usd_dc_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     */
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("
            SELECT uid,
            value1 AS od_id,
            value4
            FROM yc4_event_data 
            WHERE ev_code='preblack_dc_2019' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$chk['value4'].",od_shop_memo = CONCAT(od_shop_memo,'\\n','2019 10월 프리블프 할인 이벤트 100불이상 구매시 10000원 할인취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//201910 노르딕 25%할인(2+1 상품제외) 프리블프 주문취소
function nordic_member_price_item_event_data_cancel_201910($od_id) {


    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  count(*) cnt
    from yc4_event_data
    where ev_code = 'nordic_mem_dc_1910'
    and ev_data_type = 'od_id'
    and value2 = '".$od_id."'
    and value7 is null
    ");

    if(!$event_chk  || $event_chk['cnt'] < 1){
        return false;
    }

	// 노르딕 프리블프 노르딕상품(2+1상품 제외) 25%할인 적용 취소 => 노르딕 프리블프 노르딕상품 15%할인 적용 취소 @nordic_20200413
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','노르딕 프리블프 노르딕상품 20%할인 적용 취소')
            WHERE od_id='{$od_id}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value7 = now()
            where ev_code = 'nordic_mem_dc_1910'
    and ev_data_type = 'od_id'
    and value2 = '".$od_id."'
            ");

    return true;
}

//201910 랜덤(9)숫자로 끝나는 주문서에 5천포인트 즉시지급(20191019일만) 취소
function random_orderno_point_cancel($od_id){
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='random_191019_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 10월 19일 주문건 주문번호 9로 끝나는 주문건 5000포인트 지급 취소(기한 20191110)') WHERE od_id='" . $od_id . "'");

    return true;
}


//2019년 11월 브랜드 메가 세일 이벤트 해당되는 브랜드 상품 60불이상 구매시 3천 포인트 적립 취소
function brand_mega_sale_2019_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='mega_point_2019' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 11월 메가 세일 이벤트 해당 브랜드 상품 60불이상 구매시 3000포인트 지급 취소 (기한 20191231)') WHERE od_id='" . $od_id . "'");

    return true;
}

//2019년 10월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
function month_buy_history_event_201911_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    //이미 취소하지 않은 대상 od_id인지 체크하기
    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201911' 
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    //yc4_event_data에 해당 건 취소 update
    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    //yc4_order shop_memo에 포인트 적립 취소 update
    sql_query($a="UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 10월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소(기간 20191231)') WHERE od_id='" . $od_id . "'");


    return true;
}

//2019년 11월 노르딕 이벤트 60불 이상 무선충전 패드 증정 취소
function nordic_60usd_giftitem_201911_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'nordic_1911_item'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 11월 노르딕 기획전 $60이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_1911_item' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//201911 게릴라 실결제 100불 이상 구매시 11%포인트 지급 취소
function guerrilla_1111_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='guerrilla_1111_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019 게릴라이벤트 100불이상 결제시 결제금액 11% 포인트 지급 취소(기한 20191231)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

////201911 하나카드 50불 3프로적립 취소
function hana_every_50usd_3point_201911_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id	= sql_safe_query($od_id);

    $ev_chk	= sql_fetch("SELECT uid, value4 AS point FROM yc4_event_data WHERE ev_code='hana_201911_3po' AND ev_data_type='od_id' AND value1='" . $od_id . "' AND value7 IS NULL");

    if($ev_chk['point'] < 1){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='" . $ev_chk['uid'] . "'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo, '\\n', '2019년 11~12월 하나카드 이벤트 3%(" . $ev_chk['point'] . " 점) 포인트 취소(기한 20200130)') WHERE od_id='" . $od_id . "'");

    return true;

}


//닥터스호프 상품 1개라도 구매시 루테인 증정 이벤트 취소
function nrshope_19011_gift_item_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
                    select  value1 as od_id,uid ,value5 as it_id
                    from yc4_event_data
                    where ev_code = 'nrshope_1911_gift'
                    and ev_data_type = 'od_id'
                    and value1 = '".$od_id."'
                    and value4 is null
                    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 11월 닥터스호프 기획전 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nrshope_1911_gift' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

//블랙위크세일 실결제 60불 이상:2000, 100불 이상:3000포인트 지급 취소
function blackweek_201911_point_cancel($od_id){

    /**
    적용 페이지 : ordercartupdate.php
     **/
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='black_week_1911' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 블랙위크 이벤트 (".$event_data['value4'].") 포인트 지급 취소(기한 20200115)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}


//블랙프라이데이 노르딕 1+1 이벤트 2019 11월 이벤트 상품 취소
function bf_nordic_1911_1_plus1_cancel($od_id){
    /* 블랙프라이데이 노르딕 1+1 이벤트
* 적용 파일      : // 주문취소시 (고객)         orderinquirycancel.php // 주문취소시(관리자)        ordercartupdate.php
*/
    $od_id = sql_safe_query($od_id);

    $event_item = sql_query("
            SELECT value3, uid
            FROM yc4_event_data
            WHERE     ev_code = 'bf_nordic_1p1_1911'
                  AND ev_data_type = 'mb_id'
                  AND value1 = '".$od_id."' 
                ");

    if(!$event_item){
        return false;
    }

    while($row = sql_fetch_array($event_item)){

        if(!$row['value3'] || !$row['uid']){
            return false;
        }

        //수량
        sql_query("UPDATE yc4_event_data SET value3 = CAST(value3 AS int) - 1 WHERE value1 = '".$row['value3']."' and  ev_code = 'bf_nordic_1p1_1911' AND ev_data_type = 'item'");

        //취소
        sql_query("UPDATE yc4_event_data SET value5 = 'CANCEL' WHERE uid = '".$row['uid']."' ");

        $ev_qty_chk	= sql_fetch("
              SELECT count(*) cnt
            FROM yc4_event_data
            WHERE     ev_code = 'bf_nordic_1p1_1911'
                  AND ev_data_type = 'item'
                  AND CAST(value2 AS int) > CAST(value3 as int) and value1 = '".$row['value3']."' and value5 = 'SOLDOUT' 
        ");
        if($ev_qty_chk['cnt'] >0){
            sql_query("UPDATE yc4_item SET it_stock_qty = 9999 WHERE it_id='" . $row['value3'] . "'");
            sql_query("UPDATE yc4_event_data SET value5 =null WHERE value1 = '".$row['value3']."' and  ev_code = 'bf_nordic_1p1_1911' AND ev_data_type = 'item'");
        }

    }

    //yc4_order shop memo에 해당 이벤트 상품구매 취소 남김
/*    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 블랙프라이데이 노르딕 1+1 이벤트 주문건 취소')
            WHERE od_id='".$od_id."'
            ");*/

    return true;
}

//2019년 11월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소 : @benefits_20191202
function month_buy_history_event_201912_cancel($od_id){

    /**
     * 적용 페이지 : ordercartupdate.php
     **/

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    //이미 취소하지 않은 대상 od_id인지 체크하기
    $chk = sql_fetch("SELECT uid, value1 AS od_id, value4 
            FROM yc4_event_data 
            WHERE ev_code='sp_benefits_201912'
            AND ev_data_type='od_id' 
            AND value1 = '".$od_id."' AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    //yc4_event_data에 해당 건 취소 update
    sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

    //yc4_order shop_memo에 포인트 적립 취소 update
    sql_query($a="UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 11월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소(기간 20200131)') WHERE od_id='" . $od_id . "'");


    return true;
}

// 2019년 12월 베스트 어워즈 이벤트 : @bestawards_20191206
function best_awards_80_3000_2019_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='best_201912_po' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 12월 베스트 어워즈 이벤트 100불이상 결제시(3,000점) 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2019년 12월 노르딕 이벤트 60불 이상 무선충전 패드 증정 취소
function nordic_60usd_giftitem_201912_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = 'nordic_1912_item'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 12월 노르딕 기획전 $60이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'nordic_1912_item' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}

// 2019년 12월 구매누적왕 이벤트 취소 : @purchase_best_20191223
function purchase_best_201912_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='purchase_best_201912' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2019년 12월 구매누적왕 포인트(1,000점) 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//20191225 크리스마스 게릴라 이벤트 랜덤 포인트 지급 취소- KSJ
function x_mars_20191225_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $od = sql_fetch("
    select mb_id,
           on_uid
    from yc4_order 
    where od_id = '".$od_id."'
    ");

    if(!$od || !$od['mb_id'] || $od['mb_id'] == '' || $od['mb_id'] == '비회원' ){
        return false;
    }

    $event_data = sql_fetch("
            select uid,value3  
            from yc4_event_data 
            where ev_code ='x_mars_2019' 
            and ev_data_type = 'mb_id' 
            and value1 = '".$od['mb_id']."' 
            and value5 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value3'] || $event_data['value3'] == '') {
        return false;
    }

    if($event_data['value3'] < 1 ){
        return false;
    }

    //해당 이벤트 데이터 cancel update
    sql_query("update yc4_event_data set value5 = 'CANCEL' where uid = '".$event_data['uid']."'");


    //해당 order shop memo update
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 12월 25일 크리스마스 결제 이벤트 (".$event_data['value3'].") 포인트 지급 취소')
            WHERE od_id='".$od_id."'
            ");

    //취소 주문서이니 해당 포인트 대상자 수 감소시키기
    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = 'x_mars_2019' AND yc4_event_data.ev_data_type = 'point' and value1 = '".$event_data['value3']."'
                                    ");

    return true;
}

// 실적이벤트 자동화 적립 취소 : @benefit_auto
function month_buy_history_event_auto_cancel($od_id){

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("
		SELECT
						uid, ev_code, value1 AS od_id, value4
            FROM
						yc4_event_benefit 
            WHERE
						ev_code LIKE 'sp_benefits_%' 
				AND		ev_data_type='od_id' 
				AND		value1 = '".$od_id."'
				AND		value7 IS NULL
			");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

	$ev_code_arr = explode('_', $chk['ev_code']);
	$year = substr($ev_code_arr[2], 0, 4);	// 연 정보
	$month = substr($ev_code_arr[2], 4, 2);	// 월 정보

	$ago_year = date('Y', strtotime('-1 month', mktime(0, 0, 0, $month, 15, $year)));	// 실제 실적 연
	$ago_month = date('m', strtotime('-1 month', mktime(0, 0, 0, $month, 15, $year)));	// 실제 실적 월
	$end_date = date('Ymt', strtotime('1 month', mktime(0, 0, 0, $month, 15, $year)));	// 수령확인 기간

    sql_query("UPDATE yc4_event_benefit SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");
	
	//yc4_order shop_memo에 포인트 적립 취소 update
    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','".$ago_year."년 ".$ago_month."월 실적 이벤트 (".$chk['value4']."점)포인트 적립 취소(기간 ".$end_date.")') WHERE od_id='".$od_id."' ");

    return true;
}

// 2019년12월 포인트적립 게릴라이벤트 : @guerilla_201912
function guerrilla_201912_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='guerrilla_1912_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2019년 12월 게릴라이벤트 100불이상 결제시 10,000포인트 지급 취소(기한 20200215)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

//설날 이벤트 실결제 60불 이상:2000, 100불 이상:3000포인트 지급 취소 @newyears_20190102
function newyear_2020_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='newyear_2020' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2020년 설날 이벤트 (".$event_data['value4'].") 포인트 지급 취소(기한 20200310)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

// 2020년 1월 더블혜택 이벤트 - $120 이상 구매 고객에게 10% 할인 + 10% 포인트 적립 : 할인 취소 @double_20200107
function double_202001_dc_cancel($od_id){

    /**
    적용 페이지
     * 1. /shop/settle_authorize_result.php
     * 2. /shop/settle_authorize_result_mp.php
     * 3. /shop/orderinquirycancel.php
     */
    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("
		SELECT
				uid,
				value1 AS od_id,
				value4 
		FROM
				yc4_event_data 
		WHERE
				ev_code='double_202001_point' 
			AND ev_data_type='od_id' 
			AND value1 = '".$od_id."'
			AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

	if(!$chk['value4'] || $chk['value4'] == '') {
        return false;
    }

    // 지급 포인트가 없으면 false
    if($chk['value4'] < 1 ){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7 = 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$chk['value4'].",od_shop_memo = CONCAT(od_shop_memo,'\\n','2020년 1월 더블혜택 이벤트 120불이상 결제시 10% 할인 + 10% 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

// 닥터스호프 상품(it_id:1511707777) 구매시 1000포인트 지급 취소 : @docters_hope_20200116
function drshope_2001_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='drshope_2001_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
			SET od_shop_memo=concat(od_shop_memo,'\\n','2020년 01월 닥터스호프 여성 유산균 구매 시, 1000 포인트 지급 취소(기한 20200322)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}


// 애프터설날 실결제 80불 이상 구매시 2000포인트 지급 취소 : @after_newyear_20200122
function after_newyear_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='after_newyear_2001' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
			SET od_shop_memo=concat(od_shop_memo,'\\n','2020년 애프터설날 80불이상 결제 이벤트 (2000) 포인트 지급 완료(기한 20200315)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

//나우푸드 기획전 상품 30불 주문 + 실결제 30불 이상일 경우 포인트 2000 지급 취소 @nowfoods_202002_point
function nowfoods_202002_point_cancel($od_id){
    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='nowfood_point_202002' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    if($event_data['value4'] < 1 ){
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2020년 02월 나우푸드 이벤트 제품 30불이상 결제시 2000포인트 지급 취소(기한 20200412)') WHERE od_id='" . $od_id . "'");

    return true;
}

// 출산육아 기획전 1000포인트 지급 취소 : @baby_20200206
function baby_202002_point_cancel($od_id){

    if(!$od_id || $od_id ==''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4  
            from yc4_event_data 
            where ev_code ='baby_20200206_point' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if(!$event_data || !$event_data['uid'] || $event_data['uid'] == '') {
        return false;
    }

    if(!$event_data['value4'] || $event_data['value4'] == '') {
        return false;
    }

    //지급 포인트가 없으면 false
    if($event_data['value4'] < 1 ){
        return false;
    }

    //이벤트 데이터에 해당 주문건 취소 update
    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    //yc4_order shop memo에 해당 포인트 지급 취소 남김
    sql_query("
            UPDATE yc4_order
			SET od_shop_memo=concat(od_shop_memo,'\\n','2020년 02월 아덴 아나이스 구매 시, 1000 포인트 취소(기한 20200415)')
            WHERE od_id='".$od_id."'
            ");

    return true;
}

//2020년 02월 노르딕 제품 60불 이상 구매 치코백 무료 증정 취소 : @nordic_20200217
function nordic_60usd_giftitem_202002_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));
	$ev_code = 'nordic_2002_item';

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = '".$ev_code."'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2020년 02월 노르딕 기획전 $60이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = '".$ev_code."' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}


//2020년 02월 블루보넷/헬시오리진스 제품 40불 이상 구매 증정품 무료 증정 취소 : @blueheal_gift_202002
function blue_healthy_gift_202002_insert_cancel($od_id){

    if(!$od_id || trim($od_id) == ''){
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));
    $ev_code = 'blue_healty_202002';

    $event_chk = sql_fetch("
    select  value1 as od_id,uid ,value5 as it_id
    from yc4_event_data
    where ev_code = '".$ev_code."'
    and ev_data_type = 'od_id'
    and value1 = '".$od_id."'
    and value4 is null
    ");

    if(!$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == ''){
        return false;
    }

    sql_query("
            UPDATE yc4_order
            SET od_shop_memo=concat(od_shop_memo,'\\n','2020년 02월 블루보넷,헬시오리진스 기획전 $40이상 사은품 지급 취소')
            WHERE od_id='{$event_chk['od_id']}'
            ");

    sql_query("
            UPDATE yc4_event_data
            SET value4= now()
            WHERE uid='{$event_chk['uid']}'
            ");

    sql_query("
                UPDATE yc4_event_data
                SET value3 = cast(value3 AS int) - 1
                WHERE ev_code = '".$ev_code."' AND yc4_event_data.ev_data_type = 'gift' and value1 = '{$event_chk['it_id']}'
                                    ");

    return true;
}


// 2020년 3월 더블혜택 이벤트 - $100 이상 구매 고객에게 5% 할인 + 5% 포인트 적립 : 할인 취소 @kb_double_202003
function kb_double_202001_dc_cancel($od_id){

    $ev_code = "kb_double_202003";

    if(!$od_id || trim($od_id)==''){
        return false;
    }

    $od_id = sql_safe_query($od_id);

    $chk = sql_fetch("
		SELECT
				uid,
				value1 AS od_id,
				value4 
		FROM
				yc4_event_data 
		WHERE
				ev_code='".$ev_code."' 
			AND ev_data_type='od_id' 
			AND value1 = '".$od_id."'
			AND value7 IS NULL");

    if(!$chk || !$chk['od_id'] || !$chk['uid']){
        return false;
    }

    if(!$chk['value4'] || $chk['value4'] == '') {
        return false;
    }

    // 지급 포인트가 없으면 false
    if($chk['value4'] < 1 ){
        return false;
    }

    sql_query("UPDATE yc4_event_data SET value7 = 'CANCEL' WHERE uid='".$chk['uid']."'");

    sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$chk['value4'].",od_shop_memo = CONCAT(od_shop_memo,'\\n','2020년 3월 국민카드 더블혜택 이벤트 100불이상 결제시 5% 할인 + 5% 포인트 지급 취소(기한 20200430)') WHERE od_id='" . $od_id . "'");

    return true;
}
// 수령확인 취소 :: 첫구매 50$ 이상 구매시 추천 받은 고객에게 1000점 적립 취소: @ev_recom_20200306
function recommender_202003_cancel($od_id)
{
	// 관리자/ordercartupdate.php

	//이벤트 데이터 체크
	$event_data = sql_fetch("  
		SELECT
					uid
			FROM
					yc4_event_data
			WHERE
					ev_code = 'ev_recom_20200306'
				AND ev_data_type = 'mb_id'
				AND value2 = '".$od_id."'
				AND value7 IS NULL ");

	if ( $event_data['uid'] )
	{
		// 이벤트 데이터 취소처리
		sql_query("UPDATE yc4_event_data SET value7='CANCEL' WHERE uid='".$event_data['uid']."' ");

		// 주문서 메모
		sql_query("UPDATE yc4_order SET
					od_shop_memo=concat(od_shop_memo,'\\n','2020년 3월 신규가입 이벤트 첫결제 1,000포인트 지급 적립 취소(기한 20200503)') 
					WHERE od_id='".$od_id."' ");
		return true;
	}

	return false;
}
// 싸이베이션 포인트 이벤트 50불이상 3000, 100불 이상 5000 포인트 적립 취소 : @ev_sciva_20200323
function scivation_point_20200323_cancel($od_id)
{
	if ( !$od_id || $od_id =='' )
	{
		return false;
	}

	$od_id = sql_safe_query(trim($od_id));

	$event_data = sql_fetch("
			select uid,value4,value5  
			from yc4_event_data 
			where ev_code ='ev_sciva_20200323' 
			and ev_data_type = 'od_id' 
			and value1 = '".$od_id."' 
			and value6 is null ");

	if ( !$event_data || !$event_data['uid'] || $event_data['uid'] == '' )
	{
		return false;
	}

	if ( !$event_data['value4'] || $event_data['value4'] == '' )
	{
		return false;
	}

	if ( $event_data['value4'] < 1 )
	{
		return false;
	}

	sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

	sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2020년 03월 싸이베이션 포인트 이벤트 ".$event_data['value5']."불이상 결제시(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

	return true;
}
// 2020년 4월 만우절 이벤트 랜덤 포인트 지급 취소 : @ev_manwoo_20200401
function manwoo_20200401_point_point_cancel($od_id)
{
	if ( !$od_id || $od_id =='' ) {
		return false;
	}
	$od_id = sql_safe_query(trim($od_id));

	$od = sql_fetch("
		SELECT
					mb_id,
					on_uid
			FROM
					yc4_order 
			WHERE
					od_id = '".$od_id."'
	");

	if ( !$od || !$od['mb_id'] || $od['mb_id'] == '' || $od['mb_id'] == '비회원' )
	{
		return false;
	}

	$event_data = sql_fetch("
		SELECT
					uid,
					value3  
			FROM
					yc4_event_data 
			WHERE
					ev_code ='ev_manwoo_20200401' 
				AND ev_data_type = 'mb_id' 
				AND value1 = '".$od['mb_id']."' 
				AND value5 is null
	");

	if ( !$event_data || !$event_data['uid'] || $event_data['uid'] == '' )
	{
		return false;
	}

	if ( !$event_data['value3'] || $event_data['value3'] == '')
	{
		return false;
	}

	if ( $event_data['value3'] < 1 )
	{
		return false;
	}

	// 해당 이벤트 데이터 cancel update
	sql_query("update yc4_event_data set value5 = 'CANCEL' WHERE uid = '".$event_data['uid']."'");


	// 해당 order shop memo update
	sql_query("
		UPDATE yc4_order SET
			od_shop_memo=concat(od_shop_memo,'\\n','2020년 04월 01일 만우절 결제 이벤트 (".$event_data['value3'].") 포인트 지급 취소(기간 20200510)')
		WHERE od_id='".$od_id."'
			");

	// 취소 주문서이니 해당 포인트 대상자 수 감소시키기
	sql_query("
		UPDATE yc4_event_data SET
			value3 = cast(value3 AS int) - 1
		WHERE ev_code = 'ev_manwoo_20200401' AND yc4_event_data.ev_data_type = 'point' and value1 = '".$event_data['value3']."'
	");

	return true;
}

// 2020년 4월 더블혜택 이벤트 - $120 이상 구매 고객에게 10% 할인 + 10% 포인트 적립 : 할인 취소 @double_20200406
function double_202004_dc_cancel($od_id)
{
	/* ###################################################################
		적용 페이지
			/shop/settle_authorize_result.php
			/shop/settle_authorize_result_mp.php
			/shop/orderinquirycancel.php
	* ################################################################# */
	if ( !$od_id || trim($od_id) == '' ) {
		return false;
	}
	$od_id = sql_safe_query($od_id);

	$chk = sql_fetch("
		SELECT
				uid,
				value1 AS od_id,
				value4 
		FROM
				yc4_event_data 
		WHERE
				ev_code='double_202004_point' 
			AND ev_data_type='od_id' 
			AND value1 = '".$od_id."'
			AND value7 IS NULL");
	if ( !$chk || !$chk['od_id'] || !$chk['uid'] ) {
		return false;
	}
	if ( !$chk['value4'] || $chk['value4'] == '' ) {
		return false;
	}

	// 지급 포인트가 없으면 false
	if ( $chk['value4'] < 1 ) {
		return false;
	}

	sql_query("UPDATE yc4_event_data SET value7 = 'CANCEL' WHERE uid='".$chk['uid']."'");

	sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$chk['value4'].",od_shop_memo = CONCAT(od_shop_memo,'\\n','2020년 4월 더블혜택 이벤트 120불이상 결제시 10% 할인 + 10% 포인트 지급 취소(기한 20200531)') WHERE od_id='" . $od_id . "'");

	return true;
}
// 2020년 04월 헬시오리진스 제품 35불 이상 구매 증정품 무료 증정 취소 : @blue_healty_202004
function blue_healthy_gift_202004_insert_cancel($od_id)
{
	// 주문번호 체크
	if ( !$od_id || trim($od_id) == '' ) {
		return false;
	}

	$od_id = sql_safe_query(trim($od_id));
	$ev_code = 'blue_healty_202004';

	// 이벤트 정보 가져오기
	$event_chk = sql_fetch("
		SELECT
					value1 AS od_id,
					uid,
					value5 AS it_id
			FROM
					yc4_event_data
			WHERE
					ev_code = '".$ev_code."'
				AND ev_data_type = 'od_id'
				AND value1 = '".$od_id."'
				AND value4 is null ");

	if ( !$event_chk  || $event_chk['od_id'] =='' || !$event_chk['uid'] || $event_chk['uid'] =='' || $event_chk['it_id'] == '' ) {
		return false;
	}

	sql_query("
		UPDATE yc4_order
			SET od_shop_memo=concat(od_shop_memo, '\\n', '2020년 04월 헬시오리진스 기획전 $35이상 사은품 지급 취소')
			WHERE od_id='".$event_chk['od_id']."' ");

	sql_query("
		UPDATE yc4_event_data
			SET value4 = now()
			WHERE uid='".$event_chk['uid']."' ");

	sql_query("
		UPDATE yc4_event_data
			SET value3 = CAST(value3 AS int) - 1
			WHERE ev_code = '".$ev_code."' AND ev_data_type='gift' AND value1='".$event_chk['it_id']."' ");

	return true;
}
// 202004 얼리버드 가정의달 실결제 100불 이상 구매시 5000포인트 지급 취소 : @early_202004_point
function early_202004_point_cancel($od_id)
{
	if ( !$od_id || $od_id =='' ) {
		return false;
	}
	$od_id = sql_safe_query(trim($od_id));

	$event_data = sql_fetch("
		SELECT
					uid,value4  
			FROM
					yc4_event_data 
			WHERE
					ev_code ='early_202004_point' 
				AND ev_data_type = 'od_id' 
				AND value1 = '".$od_id."' 
				AND value6 is null ");
	if ( !$event_data || !$event_data['uid'] || $event_data['uid'] == '' ) {
		return false;
	}

	if ( !$event_data['value4'] || $event_data['value4'] == '' ) {
		return false;
	}

	// 지급 포인트가 없으면 false
	if ( $event_data['value4'] < 1 ) {
		return false;
	}

	// 이벤트 데이터에 해당 주문건 취소 update
	sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

	//yc4_order shop memo에 해당 포인트 지급 취소 남김
	sql_query("
		UPDATE yc4_order
			SET od_shop_memo=concat(od_shop_memo,'\\n','2020년 04월 얼리버드 가정의달 100불이상 결제시 5,000포인트 지급 취소(기한 20200531)')
		WHERE od_id='".$od_id."'
			");

	return true;
}

// 2020년 5월 노르딕 포인트 이벤트 50불이상 5000, 100불 이상 10000 포인트 적립 취소 : @nordic_20200501
function nordic_point_20200501_cancel($od_id)
{
	if ( !$od_id || $od_id =='' )
	{
		return false;
	}

	$od_id = sql_safe_query(trim($od_id));

	$event_data = sql_fetch("
			select uid,value4,value5  
			from yc4_event_data 
			where ev_code ='ev_nordic_202005' 
			and ev_data_type = 'od_id' 
			and value1 = '".$od_id."' 
			and value6 is null ");

	if ( !$event_data || !$event_data['uid'] || $event_data['uid'] == '' )
	{
		return false;
	}

	if ( !$event_data['value4'] || $event_data['value4'] == '' )
	{
		return false;
	}

	if ( $event_data['value4'] < 1 )
	{
		return false;
	}

	sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

	sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2020년 05월 노르딕 포인트 이벤트 ".$event_data['value5']."불이상 결제시(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

	return true;
}

// 2020년 5월 가정의달 포인트 이벤트 50불이상 5%할인 취소 : @home_20200501
function home_point_20200501_dc_cancel($od_id)
{
	if ( !$od_id || $od_id =='' )
	{
		return false;
	}
	$od_id = sql_safe_query($od_id);

	$chk = sql_fetch("
		SELECT
					uid,
					value1 AS od_id,
					value4
			FROM
					yc4_event_data 
			WHERE
					ev_code='ev_home_20200501_dc' 
				AND ev_data_type='od_id' 
				AND value1 = '".$od_id."'
				AND value7 IS NULL");

	if ( !$chk || !$chk['od_id'] || !$chk['uid'] ) {
		return false;
	}

	if ( !$chk['value4'] || $chk['value4'] == '' )
	{
		return false;
	}

	if ( $chk['value4'] < 1 )
	{
		return false;
	}

	sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

	sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$chk['value4'].", od_shop_memo = CONCAT(od_shop_memo,'\\n','2020년 05월 가정의달 이벤트 50불이상 구매시 5% 할인 취소') WHERE od_id='".$od_id."' ");

	return true;
}

// 2020년 5월 가정의달 포인트 이벤트 60불이상 3000, 100불 이상 6000 포인트 적립 취소 : @home_20200501
function home_point_20200501_cancel($od_id)
{
	if ( !$od_id || $od_id =='' )
	{
		return false;
	}

	$od_id = sql_safe_query(trim($od_id));

	$event_data = sql_fetch("
			select uid,value4,value5  
			from yc4_event_data 
			where ev_code ='ev_home_20200501' 
			and ev_data_type = 'od_id' 
			and value1 = '".$od_id."' 
			and value6 is null ");

	if ( !$event_data || !$event_data['uid'] || $event_data['uid'] == '' )
	{
		return false;
	}

	if ( !$event_data['value4'] || $event_data['value4'] == '' )
	{
		return false;
	}

	if ( $event_data['value4'] < 1 )
	{
		return false;
	}

	sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

	sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','2020년 05월 가정의달 포인트 이벤트 ".$event_data['value5']."불이상 결제시(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

	return true;
}

// 자로우 브랜드 제품 $35 이상 구매 시, 3천 포인트 적립 취소 : @jarrow_20200512
function jarrow_point_20200512_cancel($od_id)
{
	if ( !$od_id || $od_id =='' )
    {
        return false;
    }

    $od_id = sql_safe_query(trim($od_id));

    $event_data = sql_fetch("
            select uid,value4,value5  
            from yc4_event_data 
            where ev_code ='ev_jarrow_202005' 
            and ev_data_type = 'od_id' 
            and value1 = '".$od_id."' 
            and value6 is null ");

    if ( !$event_data || !$event_data['uid'] || $event_data['uid'] == '' )
    {
        return false;
    }

    if ( !$event_data['value4'] || $event_data['value4'] == '' )
    {
        return false;
    }

    if ( $event_data['value4'] < 1 )
    {
        return false;
    }

    sql_query("update yc4_event_data set value6 = 'CANCEL' where uid = '".$event_data['uid']."'");

    sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','자로우 브랜드 제품 포인트 이벤트 ".$event_data['value5']."불이상 결제시(".$event_data['value4'].") 포인트 지급 취소') WHERE od_id='" . $od_id . "'");

    return true;
}

//2020 05 25 7일간 메모리얼 기념 할인 이벤트 100불이상 5% 할인 취소
function memorial_ev_202005_100_5usddc_cancel($od_id){
	if(!$od_id || trim($od_id)==''){
		return false;
	}

	$od_id = sql_safe_query($od_id);

	$chk = sql_fetch("
					SELECT uid,
					value1 AS od_id,
					value5 
					FROM yc4_event_data 
					WHERE ev_code='memorial_202005' 
					AND ev_data_type='od_id' 
					AND value1 = '".$od_id."' AND value7 IS NULL");

	if(!$chk || !$chk['od_id'] || !$chk['uid']){
		return false;
	}

	sql_query("UPDATE yc4_event_data SET value7= 'CANCEL' WHERE uid='".$chk['uid']."'");

	sql_query("UPDATE yc4_order SET od_dc_amount = od_dc_amount - ".$chk['value5'].",od_shop_memo = CONCAT(od_shop_memo,'\\n','2020 05월 메모리얼데이 기념 할인 이벤트 100불이상 구매시 5%(".$chk['value5'].") 할인취소') WHERE od_id='" . $od_id . "'");

	return true;
}

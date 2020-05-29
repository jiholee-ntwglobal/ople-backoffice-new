<?
$sub_menu = "400400";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$opk_chk = sql_fetch("select opk_fg from {$g4['yc4_order_table']} where on_uid = '".$on_uid."'");
if($opk_chk['opk_fg'] == 'Y'){ // 오플 코리아 주문서인지 체크
    $opk = true;
    include_once $g4['full_path']."/lib/opk_db.php";
    $opk_db = new opk_db;
}else{
    $opk = false;
}

$cnt = count($_POST[ct_id]);
$ct_status_change_fg = false;
for ($i=0; $i<$cnt; $i++)
{
    if ($_POST[ct_chk][$i])
    {
        $ct_id = $_POST[ct_id][$i];

        $sql = " select * from $g4[yc4_cart_table]
                  where on_uid = '$on_uid'
                    and ct_id  = '$ct_id' ";
        $ct = sql_fetch($sql);

		// 김선용 201209 :
		$it = sql_fetch("select ca_id from {$g4['yc4_item_table']} where it_id='{$ct['it_id']}' ");



        // 재고를 이미 사용했다면 (재고에서 이미 뺐다면)
        $stock_use = $ct[ct_stock_use];
        if ($ct[ct_stock_use])
        {
            /*if ($ct_status == '주문' || $ct_status == '취소' || $ct_status == '반품' || $ct_status == '품절')
            {
                $stock_use = 0;
                // 재고에 다시 더한다.
                $sql =" update $g4[yc4_item_table] set it_stock_qty = it_stock_qty + '$ct[ct_qty]' where it_id = '$ct[it_id]' ";
                sql_query($sql);
            }*/
        }
        else
        {
            // 재고 오류로 인한 수정
            // if ($ct_status == '주문' || $ct_status == '준비' || $ct_status == '배송' || $ct_status == '완료') {
            /*if ($ct_status == '배송' || $ct_status == '완료')
            {
                $stock_use = 1;
                // 재고에서 뺀다.
                $sql =" update $g4[yc4_item_table] set it_stock_qty = it_stock_qty - '$ct[ct_qty]' where it_id = '$ct[it_id]' ";
                sql_query($sql);
            }*/
            /* 주문 수정에서 "품절" 선택시 해당 상품 자동 품절 처리하기
            else if ($ct_status == '품절') {
                $stock_use = 1;
                // 재고에서 뺀다.
                $sql =" update $g4[yc4_item_table] set it_stock_qty = 0 where it_id = '$ct[it_id]' ";
                sql_query($sql);
            } */
        }

		// 김선용 201209 : 추천인 적립률
		// 추천인 적립금 지급/회수 (1단위 올림)
		// 추천인 적립기능 사용시

		if($mb_id != '' && floatval($default['de_recom_point']))
		{
			// 김선용 201210 : 주문당시 할인액이 있다면 추천인 검증이 이미 처리된 경우임
			// 할인분류에 속한경우만
			//if($default['de_recom_off_ca_id'] == '' || $default['de_recom_off_ca_id'] == substr($it['ca_id'], 0, strlen($default['de_recom_off_ca_id'])))
			//{
				// 김선용 201210 : 주문당시 추천인의 추천시스템 할인내역이 있는지도 점검
				$chk_od = sql_fetch("select od_id from {$g4['yc4_order_table']} where od_id='$od_id' and od_recommend_off_sale>0 ");
				$mb = get_member($mb_id, "mb_recommend");
				$chk_mb = get_member($mb['mb_recommend'], "mb_id"); // 회원 존재여부
				if($chk_mb['mb_id'] && $chk_od['od_recommend_off_sale']) {
					if($ct['ct_status'] != '완료' && $ct_status == '완료'){
						$per_point = ceil(($ct['ct_amount'] * $ct['ct_qty']) * ($default['de_recom_point'] / 100));
						insert_point($mb['mb_recommend'], $per_point, "추천인({$mb_id}) 상품구매 [주문번호:$od_id($ct_id)] 지급");

						// 김선용 201210 : 피추천인 적립내역 리포트 저장
						sql_query("insert into {$g4['yc4_rc_table']}
							set mb_id			= '{$chk_mb['mb_id']}',
								od_id			= '$od_id',
								rc_save_point	= '$per_point',
								rc_part			= 'point',
								rc_datetime		= '{$g4['time_ymdhis']}' ");

					}else if($ct['ct_status'] == '완료' && $ct_status != '완료'){
						$per_point = ceil(($ct['ct_amount'] * $ct['ct_qty']) * ($default['de_recom_point'] / 100));
						insert_point($mb['mb_recommend'], $per_point * (-1), "추천인({$mb_id}) 구매취소 [주문번호:$od_id($ct_id)] 회수");

						sql_query("delete from {$g4['yc4_rc_table']} where od_id='$od_id' and mb_id='{$chk_mb['mb_id']}' "); // 적립포인트 내역 삭제
					}
				}
			//}
		}




        $point_use = $ct[ct_point_use];
        // 회원이면서 포인트가 0보다 크면
        // 이미 포인트를 부여했다면 뺀다.
        if ($mb_id && $ct[ct_point] && $ct[ct_point_use])
        {
            $point_use = 0;
            insert_point($mb_id, (-1) * ($ct[ct_point] * $ct[ct_qty]), "주문번호 $od_id ($ct_id) 취소");

		}elseif($mb_id && $ct[ct_point] && !$ct[ct_point_use] && $ct_status == '완료'){ // 포인트를 적립받지 않았고 배송상태가 완료이면 가상계좌 결제 이벤트 포인트 적립
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

					//$kcp_point_chk = true;
					$point_use = 1;
				}
			}
		}

        // 히스토리에 남김
        // 히스토리에 남길때는 작업|시간|IP|그리고 나머지 자료
        $ct_history="\n$ct_status|$now|$REMOTE_ADDR";

        $sql = " update $g4[yc4_cart_table]
                    set ct_point_use  = '$point_use',
                        ct_stock_use  = '$stock_use',
                        ct_status     = '$ct_status',
                        ct_history    = CONCAT(ct_history,'$ct_history')
                  where on_uid = '$on_uid'
                    and ct_id  = '$ct_id' ";
        sql_query($sql);

        if($opk){
            $sql = "
                update $g4[yc4_cart_table]
                    set ct_point_use  = '$point_use',
                        ct_stock_use  = '$stock_use',
                        ct_status     = '$ct_status',
                        ct_history    = CONCAT(ct_history,'$ct_history')
                  where on_uid = '$on_uid'
                    and ct_id  = '".$ct['opk_ct_id']."'
            ";
            $opk_db->query($sql);
        }

		$ct_change_arr[$ct_id] = $_POST['bf_ct_status'][$i];


		# 주문 상태 변경 정보 체크 2015-02-10 홍민기 #
		if(in_array($ct_status,array('쇼핑','주문','취소','반품','품절'))){
			$sales_status = 2;
		}else{
			$sales_status = 1;
		}

		if(in_array($ct['ct_status'],array('쇼핑','주문','취소','반품','품절'))){
			$ct_sales_status = 2;
		}else{
			$ct_sales_status = 1;
		}

		if($sales_status != $ct_sales_status){

			$ct_status_change_fg = true;


            sql_query("
				update ".$g4['yc4_cart_table']." set ct_status_update_dt = '".$g4['time_ymdhis']."' where on_uid = '".$on_uid."' and ct_id = '".$ct_id."'
			");
            if($opk){
                $opk_db->query("
                    update ".$g4['yc4_cart_table']." set ct_status_update_dt = '".$g4['time_ymdhis']."' where on_uid = '".$on_uid."' and ct_id = '".$ct['opk_ct_id']."'
                ");
            }
		}
    }
}

# 주문 상태가 변경되었다면 od_status_update_dt 업데이트 2015-02-10 홍민기 #
//if($ct_status_change_fg){
	sql_query("
		update ".$g4['yc4_order_table']." set od_status_update_dt = '".$g4['time_ymdhis']."' where od_id = '".$od_id."'
	");
    if($opk){
        $opk_db->query("update ".$g4['yc4_order_table']." set od_status_update_dt = '".$g4['time_ymdhis']."' where od_id = '".$od_id."'");
    }
//}

// 김선용 201309 : 회원 프로모션 처리
// 프로모션 테이블 조회 후, 피추천인이 정상회원인지 확인
if($mb_id != '' && ($ct_status == '배송' || $ct_status == '완료')) {
	$chk_mpr = sql_fetch("select mb_id, mb_id2 from {$g4['yc4_member_promor']} where mb_id2='$mb_id' ");
	$chk_mb = sql_fetch("select mb_id, mb_level from {$g4['member_table']} where mb_id='{$chk_mpr['mb_id']}' and mb_leave_date='' and mb_intercept_date='' ");
	// 프로모션회원의 주문정보가 있는지 확인하고 없으면 등록하고 구매누적 카운팅. 주문서 1개당 1회누적
	$chk_mpo = sql_fetch("select mo_pid from {$g4['yc4_member_promo_order']} where mb_id2='$mb_id' and od_id='$od_id' ");
	if($chk_mb['mb_id'] && !$chk_mpo['mo_pid']){
		sql_query("update {$g4['yc4_member_promo']} set mp_order_count=mp_order_count+1 where mb_id='{$chk_mb['mb_id']}' ");
		sql_query("insert into {$g4['yc4_member_promo_order']} set mb_id2='$mb_id', od_id='$od_id', mo_datetime='{$g4['time_ymdhis']}' ");

		// 프로모션 피추천회원 설정값 이상이면 승급처리(가입자/구매누적)
		// 위에 업데이트한 정보 반영값으로 다시 쿼리
		if($chk_mb['mb_level'] < 4){
			$c1 = sql_fetch("select mp_mb_count, mp_reg_count, mp_order_count from {$g4['yc4_member_promo']} where mb_id='{$chk_mb['mb_id']}' ");
			if($c1['mp_reg_count'] >= $c1['mp_mb_count'] && $c1['mp_order_count'] >= $c1['mp_mb_count'])
				sql_query("update {$g4['member_table']} set mb_level=4 where mb_id='{$chk_mb['mb_id']}' ");
		}
	}
}

if(in_array($ct_status,array('취소','반품','품절'))){
	sql_query("update master_card_event set point = 0 where od_id = '".$od_id."'");
}


$qstr = "sort1=$sort1&sort2=$sort2&sel_field=$sel_field&search=$search&page=$page";

$url = "./orderform.php?od_id=$od_id&$qstr";

// 1.06.06
$od = sql_fetch(" select od_receipt_point,mb_id,on_uid from $g4[yc4_order_table] where od_id = '$od_id' ");

if(is_array($ct_change_arr)){
	clearance_sell_qty($on_uid);
	foreach($ct_change_arr as $ct_id => $bf_ct_status){
		sales_report_cart_update_adm($ct_id,$ct_status,$bf_ct_status);
	}
}

if($ct_status == '준비'){
	$pay_time_chk = sql_fetch("select count(*) as cnt from yc4_order where od_id = '{$od_id}' and od_pay_time is null");
	if($pay_time_chk['cnt'] > 0){ // od_pay_time이 null일 경우에만 update 처리
		$od_pay_time = $g4['time_ymdhis'];
		sql_query("update ".$g4['yc4_order_table']." set od_pay_time = '".$od_pay_time."' where od_id = '".$od_id."'");
	}
}

if($ct_status == '취소' && $_POST['point_return']){

    insert_point($od['mb_id'],$od['od_receipt_point'],'주문번호:'.$od_id.'포인트 결제 취소');
    sql_query("update {$g4['yc4_order_table']} set od_receipt_point=0,od_shop_memo = concat(od_shop_memo,'\\n','포인트 자동 환불 완료(".$od['od_receipt_point'].")') where od_id = '".$od_id."'");
}


# 신한 글로벌 카드 혜택 취소 및 복구 #
$sh_glb_chk = sql_fetch("select count(*) as cnt from yc4_event_data where ev_code = 'sh_glb' and ev_data_type = '10per_point_order' and value2 = '".$od_id."'");
if($sh_glb_chk['cnt']>0 && $ct_status_change_fg) {
    if ($ct_status == '취소') {
        sql_query("update yc4_event_data set value5 = now() where ev_code = 'sh_glb' and ev_data_type = '10per_point_order' and value2 = '" . $od_id . "'");
        sql_query("update {$g4['yc4_order_table']} set od_shop_memo = concat(od_shop_memo,'\\n','신한 글로벌카드 10% 적립 혜택 취소') where od_id = '".$od_id."'");
    }elseif($ct_status = '준비'){
        sql_query("update yc4_event_data set value5 = NULL where ev_code = 'sh_glb' and ev_data_type = '10per_point_order' and value2 = '" . $od_id . "'");
        sql_query("update {$g4['yc4_order_table']} set od_shop_memo = concat(od_shop_memo,'\\n','신한 글로벌카드 10% 적립 혜택 복구') where od_id = '".$od_id."'");
    }
}

# Masterpass 10달러 할인 혜택 취소 초기화 #
if ($ct_status == '취소') {
	$master_pass_chk	= sql_fetch("SELECT COUNT(*) AS cnt FROM yc4_event_data WHERE ev_code = 'masterpass' AND value1='".$od_id."' AND value5='".$od['mb_id']."'");
	if($master_pass_chk['cnt']>0) {
		$cancel_chk	= sql_fetch("SELECT COUNT(*) AS a_cnt, SUM(if(ct_status ='취소',1,0)) as c_cnt FROM yc4_cart WHERE on_uid='".$od['on_uid']."'");
		if($cancel_chk['a_cnt']==$cancel_chk['c_cnt']){
			sql_query("DELETE FROM yc4_event_data WHERE ev_code = 'masterpass' AND value1='".$od_id."' AND value5='".$od['mb_id']."'");
			sql_query("UPDATE ".$g4['yc4_order_table']." SET od_shop_memo = concat(od_shop_memo,'\\n','주문 취소로 인한 masterpass 10달러 할인 혜택 초기화') WHERE od_id = '".$od_id."'");

		}
	}
}

# 마스터카드 고디바 초콜렛 증정 초기화
if($ct_status == '취소'){
	$chk = sql_fetch("select uid,value3 from yc4_event_data where ev_code = 'mastercard' and ev_data_type = 'godiva' and value1 = '{$od['mb_id']}' and value3 = '{$od_id}'");
	if($chk && $chk['uid'] && $chk['value3']){
		sql_query("update yc4_order set od_shop_memo = concat(od_shop_memo,'\\n','마스터카드 이벤트 고디바 경품 주문 취소 초기화') where on_uid = '{$on_uid}'");
		sql_query("update yc4_event_data set value3 = '{$od_id}' where uid = '{$chk['uid']}'");
	}
}


// 전체취소인지 확인
$ct_status_etc	= 0;
$ct_sql			= sql_query("SELECT ct_status FROM yc4_cart WHERE on_uid='".$on_uid."'");
while($row = sql_fetch_array($ct_sql)){
	if(!in_array($row['ct_status'], array('취소','반품','품절'))){
		$ct_status_etc++;
	}
}
// 전체취소면 이벤트 초기화 
// Important!! $mb_id를 인자로 받는 경우 비회원은 $mb_id='비회원' 으로 넘어가기 때문에 예외처리 필수(백오피스 한정)
if($ct_status_etc == 0){
	// 바캉스 사은품 지급 초기화
	sum_vacation_16_gift_cancel($on_uid, $od_id);
	// 노르딕 사은품 지급 초기화
	nordic_20years_gift_cancel($on_uid, $od_id);
	// 가정의달 포인트 지급 초기화
	familymonth_16_point_cancel($od_id);
	// 국민마스터카드 할인 초기화
	kb_master_16_dc_10_reset($mb_id, $od_id);
	// 나우푸드 사은품 지급 초기화
	nowfood_16_30gift_cancel($on_uid, $od_id);
	// 마스터패스 할인 초기화
	masterpass_16_80dc_cancel($od_id);
	// 하나비바 포인트 초기화
	viva_5_point_cancel($od_id);
	// 리우올림픽 8% 포인트 초기화
	olympic_16_point_cancel($od_id);
	// 리우올림픽 사은품 지급 초기화
	olympic_16_gift_cancel($od_id,$mb_id);
	// 블랙썸머데이 노르딕쇼퍼백 지급 초기화
	nordic_set_bag_gift_cancel($on_uid, $od_id);
	// 인스타그램 사은품 지급 초기화
	instagram_16_gift_cancel($od_id,$mb_id);
	// 2016추석 사은품 지급 초기화
	harvest_day_16_gift_cancel($od_id);
	// 2016추석 포인트 지급 초기화
	harvest_day_16_point_cancel($od_id);
	// 국민카드 5%할인 초기화
	kb_16_100_5_dc_reset($mb_id, $od_id);
	// 2016추석 추가사은품 지급 초기화
	harvest_16_etc_gift_cancel($mb_id, $od_id);
	// 헬스관 오픈 사은품 지급 초기화
	health_shop_op_gift_cancel($od_id);
	// 할로윈 이벤트 사은품지급 초기화
	event_weekend_free_gift_201610_cancel($od_id);
	// 마스터카드 첫구매시 40불이상 10% 즉시할인 이벤트 초기화
	mastercard_first_order_2016_cancel($od_id);
	// 삼성 MasterCard 100불이상 결제시 10% 즉시할인 초기화
	samsung_master_card_2016_cancel($od_id);
	// 삼성 5V2 card 결제금액 20%포인트 지급 이벤트 초기화
	samsung_5v3_card_2016_cancel($od_id);
	// 노르딕 구매금액별 사은품 이벤트 초기화
	nordic_freegift_2016_cancel($od_id);
	// 빼빼로 데이 특별 사은품 지급 초기화
	bbeabbearo_16_gift_cancel($od_id);
	// 노르딕 1+1 특가 이벤트 초기화
	nordic_1p1_16_dc_cancel($od_id);
	// 마스터카드50/100 결제 5/10 할인 초기화
	master_16_50_100_dc_cancel($od_id);
	// 2016 크리스마스 특별 사은품 지급 초기화
	x_mars_16_gift_cancel($od_id);
	// 2017 설날 할인 및 포인트 지급 초기화
	new_year_dc_2017_cancel($od_id);
	// 2017 마스터카드 토요일 포인트 지급 초기화
	master_17_sat_point_cancel($od_id);
	// 2016 출석체크 이벤트 사은품 지급 지급 초기화
	attendance_16_gift_cancel($od_id);
	// 2017 노르딕 신상품 이벤트 사은품 지급 지급 초기화
	nordic_nnnp_17_gift_cancel($od_id);
	// 2017 kb아이해피 첫구매 사은품 지급 지급 초기화
	kb_happy_17_sel_gift_cancel($od_id);
    //인스타그램 wbc 20150324 사은품 지급 초기화
    instargram_20170325_gift_cancel($od_id,$mb_id);
    // 하나 원큐패스 유입 주문 5%포인트 지급 초기화
    hana_1qpass_point_17_cancel($od_id);
    //만우절이벤트 사은품 당첨자 2017-04-11이후 주문 시 주문건에 해당 사은품 자동지급 취소
    april_fool_2017_gift_cancle($od_id,$mb_id);
    // 노르딕 1704 빅이벤트 취소처리 2017-04-12 강경인
	nordic_1704_gift_cancel($od_id);
	//홀리데이 스페셜위크 취소처리 2017-04-28
    holiday_Special_Week_2017_Point_Cancel($od_id);
    //반려동물 인스타그램 사은품 지급취소처리 2017-04-28
    instagram_2017_animal_gift_cancle($od_id,$mb_id);
    // 노르딕 1+1 이벤트 상품 수량변경 2017-05-08 강경인
	nordic_1704_bogo_cancel($od_id);
    //마스터카드 경품 당첨자 취소처리 2017-06-24 ~ 2017-08-31 곽범석
    master_201706_gift_cancel($od_id,$mb_id);
    // 하나카드 100불 10불할인 취소처리 2017-06-26 강경인
	hana_17_100_10_dc_cancel($od_id);
	// 첫 구매 할인 & 포인트 증정 2017-07-06 ~ 2017-08-06 기간내에 첫구매 50$ 이상 구매 5%할인 주문 취소 및 관리자 취소 곽범석
    event_201707_usd50_dc5_cancel_inisis($od_id,$mb_id);
    //신규가입 추천인 이벤트 2017.07.06~2017.08.06 추천인 적은 신규 고객이 이벤트 기간 내 구매(수령확인) 시, 추천 받은 고객에게 1000점 적립 취소 곽범석
    recommender_2017_point1000_cancel($od_id);
    // 1.노르딕제품 2.80불이상 결제금액 80불이상 사은품 지급(1,2조건 모두 충족) 취소
    nordic_2017_80usd_gift_item_cancel($od_id);
    //마스터 카드 $100 이상 $10 할인 결제 실패 취소처리 관리자 곽범석
    kb_master_2017_09_100usd_10dc_admin_cancel($od_id);
    //얼리버드 추석 인스타그램 이벤트 사은품 당첨자 자동지급취소 2017-09-16~무기한 곽범석 2017-09-15 작업
    instagram_201709_gift_cancel($od_id,$mb_id);
    //블랙 프라이데이 2017-10-16 ~ 2017-10-29    $100불이상 구매시 10% 즉시할인 취소 곽범석 2017-10-13 작업
    black_friday_2017_usd100_dc10_cancel($od_id,$mb_id);
    //오플 스마트추석 인스타그램 이벤트 당첨자 사은품 자동지급 취소2017-10-18~무기한 곽범석 2017-10-16 작업
    instagram_201710_gift_item_speaker_cancel($od_id,$mb_id);
	//2017추석 선결제권 구매 취소처리
	prepay_17_chu_ev_cancel($od_id,$mb_id);
    //노르딕 내추럴스 2+1 구미 증정 이벤트 취소 곽범석
    nordic_item201710_2_1_event_cancel($od_id,$mb_id);
    //노르딕 내추럴스 이벤트 노르딕 제품 $100 이상 실결제 $100 이상 보틀 지급, $60이상 $66~$99 치코백 지급 취소
    nordic_item201710_60_bag_100_bottle_cancel($od_id,$mb_id);
    //하나 마스터체크 카드 이벤트 $100이상 구매 고객 $10 즉시할인 2017-11-13~2017-12-31  취소처리 곽범석 작업 2017-11-07
    hana_17_100_10_dc_2_cancel($od_id);
    //국민 마스터카드 할인 이벤트  2017-11-17 ~ 2017-12-31 100불이상 구매시 10% 할인 취소 곽범석 작업 2017-11-14
    kb_mastercard_100_15_cancel($od_id);
    //블랙프라이데이 노르딕 1+1 이벤트 2017-11-24~2017-11-26 주문시 결제 취소
    bf_nordic_1711_1_1_cancel($od_id);
    // 골라 담기 이벤트 2017-11-30  수령 확인 시 포인트 11% 적립 2017-11-24 작업 곽범석 취소
    choice_item_201711_cancel($od_id);
//스키니 앤코 30 불이상 주문, 결제금액 30 불 이상 시 사은품 지급 취소 (2가지 조건 충족시)
    skinny_co_30_gift_item_event_cancel($od_id);
//실적 이벤트  취소 처리
    month_buy_history_cancel($od_id);
// 더블해택 이벤트 1월 한달간 100불 이상 구매시 5%할인 + 5% 포인트 적립 취소
    double_amount_event_201801_cancel($od_id);

    //2017 크리스마스 인스타그램 이벤트 당첨자 사은품 자동지급 2017-12-29곽범석 작업
     instagram_2017_christmas_gift_cancle($od_id,$mb_id);
//다이어트 상품 이벤트 30불이상 주문시 사은품 지급 취소 곽범석 작업 20180104
    diet_event_30_gift_item_cancel($od_id);
//얼리버드 설날 이벤트 (설날 세트 구매 시, 쇼핑백 무료 증정 한정 수량) 주문서 취소
    early_bird_new_year_2018_gift_item_cancel($od_id,$mb_id);
//얼리버드 설날 이벤트 ($60,$80 이상 포인트 적립) 수령확인 취소
    early_bird_new_year_2018_point_cancel($od_id);
//2018년 발렌타이데이 이벤트 2018-01-25 ~ 2018-02-14 지정된 상품 주문시 사은품 지급 취소
    valentine_day_2018_02_14_event_cancel($od_id,$mb_id);
    //2018년 본 설날 이벤트 사은품 선택 지급 이벤트  70불 이상 지급 취소
    new_year_2018_gift_item_cancel($od_id);
//마스터카드 이벤트(토요일 마스터카드 결제 3% 포인트 적립) 취소 곽범석 관리자 적용
    master_18_sat_point_cancel($od_id);
//이벤트 상품  싸이베이션 1개 구매 4천, 2개 구매1만 포인트 지급
    scivation_xtend_2018_event_cancel($od_id);
//스낵이벤트 취소 처리
    snack_event_gift_item_201803_cancel($od_id,$mb_id);

    //노르딕 이벤트 90불이상 결제시 이어폰 주문
    nordic_20180302_event_90_gift_cancel($od_id);
    //2018년 화이트데이
    white_day_60usd_gift_item_201803_cancel($od_id);
//싸이베이션 2개이상 구매 시 사은품 증정 이벤트
    scivation_two_item_gift_item2018_cancel($od_id);
    //2018 오플 월컴 이벤트 첫 구매시 5프로 할인
    welcome_201804_first_cancel($od_id);
    welcome_201804_second_cancel($od_id);
//2018 다이어트 이벤트  30 불이상 주문, 결제금액 50 불 이상 시 사은품 지급 (2가지 조건 충족시)
    diet_30_gift_item_event_cancel($od_id);
//2018 다이어트 이벤트  50 불이상 주문, 결제금액 50 불 이상 시 사은품 지급 (2가지 조건 충족시)
    diet_50_gift_item_event_cancel($od_id);
//2018 다이어트 이벤트  해당 상품 싸이베이션 구매시 보틀 증정
    diet_scivation_gift_item_event_cancel($od_id);
//얼리버드 가정의달 이벤트 100불이상 구매시 5천원할인 0416~0430
    early_bird_familymonth_2018_usd100_dc5000_cancel($od_id,$mb_id);

    nordic_201804_event_cancel($od_id,$mb_id);

    jarrow_formulas_40_point_event_cancel($od_id);

    nordic_90_gift_item_event_201805_cancel($od_id);

    kb_master_2018_05_100usd_5_cancel($od_id);

    familymonth_may_be_happy_2018_ev_cancel($od_id);

    hana_keb_point_18_5dc_cancel($od_id);

    hana_keb_point_18_5point_cancel($od_id);

    icepack_add_2018_cancel($od_id);

    scivation_item_gift_item201806_cancel($od_id);

    scivation_60_gift_item_event_201806_cancel($od_id);

    time_point_20180690_cancel($od_id);

    gold_rush_2018_gift_item_cancle($od_id,$mb_id);

    jarrowformulas_201807_giftitme_cancel($od_id);

    summer_80usd_3000point_201807_cancel($od_id);

    nordic_free_gift_2018_amount_cancel($od_id);

    nordic_gift_item_bag_201808_cancel($od_id);

    jarrow_member_price_item_event_data_cancel($od_id);

    guerrilla_2018_gift_item_cancle($od_id,$mb_id);

    early_chuseok_2018_60100_point_cancel($od_id);

    early_chuseok_2018_100_dc_cancel($od_id,$mb_id);

    early_chuseok_2018_cancel($od_id,$mb_id);

    nordic_100_gift_item_event_201808_cancel($od_id);

    nordic_208008_2_1_cancel($od_id);

    chuseok_2018_60100_point_cancel($od_id);

    jarrow_free_gift_201809_amount_cancel($od_id);

    chuseok_2018_event_5point_cancel($od_id);

    pre_bf_2018_100_10000dc_cancel($od_id,$mb_id);

    pbf_nordic_100_gift_item_event_201810_cancel($od_id);

    hlw_60_gift_item_event_201811_cancel($od_id);

    kb_201811_100_5_point_cancel($od_id);

    brand_mega_sale_2018_cancel($od_id);

    bf_nordic_1811_1_plus1_cancel($od_id);

    cyber_monday_80_5000_2018_cancel($od_id);

    pick_item_100_point_2018_cancel($od_id);

    best_awards_80_3000_2018_cancel($od_id);

    nordic_special_100_gift_item_event_201812_cancel($od_id);

    merry_jarrow_formulas_201812_cancel($od_id);

    christmas_ev_2018_100usd_10000po_cancel($od_id);

    xtend_ev_point_40_100_cancel($od_id);

    double_ev_2019_100_3dc_cancel($od_id);

    month_buy_history_event_201812_cancel($od_id);

    florex_201901_1cnt_2000point_cancel($od_id);

    early_bird_new_year_dc_2019_cancel($od_id);

    new_year_present_80_3000point_cancel($od_id);

    new_year_present_set_gift_cancel($od_id);

    nordic_special_120_gift_item_event_201901_cancel($od_id);

    legendairy_milk_2019_1cnt_2000point_cancel($od_id);

    new_year_2019_60usd_3000point_cancel($od_id);

    pfizer_ev201902_point_20_cancel($od_id);

    saturday_20190223_Dgumi_cancel($od_id);

    friday_17h_24h_50usd_2000point_20190301_cancel($od_id);

    nordic_20190311_gift_item_ev_cancel($od_id);

    nordic_20190311_10000point_cancel($od_id);

    hana_20190327_item999_soldout_cancel($od_id);

    hana_lms_point_20190326_evinsert_point_insert_cancel($od_id);

    april_fools_day_20190401_point_cancel($od_id);

    now_foods_201904_40usd_2000point_cancel($od_id);

    month_buy_history_event_201904_cancel($od_id);

    //201904 하나카드 50불 3프로적립 취소
    hana_every_50usd_3point_201904_cancel($od_id);

    //201904 하나카드 균일가 10불 이벤트 수량
    hana_bigdata_2019_soldout_cancel($od_id);

    nordic_60usd_giftitem_bag_201904_cancel($od_id);

    //2019년 얼리버드 가정의달 이벤트 특정상품 갯수 만큼 쇼핑백 지급 취소
    early_national_family_month_gift_cancel_201904($od_id);
    //2019년 얼리버드 가정의달 이벤트 100불이상 5천 포인트 지급 취소
    early_national_family_month_5000point_cancel_201904($od_id);

    //2019년  가정의달 이벤트 120불이상 5천,70불이상 3천 포인트 지급 취소
    national_family_month_30005000po_201905_cancel($od_id);

    //게릴라 이벤트 80불이상 5%프로 적립 취소
    national_family_month_guerrilla_80_5po_cancel($od_id);

    //싸이베이션 60불이상 2000포인트 증정 취소
    scivation_60usd_2000po_201905_cancel($od_id);

    //싸이베이션 특정상품 구매시 보틀 증정
    scivation_1bottle_1order_201905_cancel($od_id);

    //2019년 가정의달 이벤트 특정상품 갯수 만큼 쇼핑백 지급 취소
    national_family_month_gift_cancel_201905($od_id);

    //2019년 04월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
    month_buy_history_event_201905_cancel($od_id);

    //가정의달 게릴라 이벤트 랜덤으로 500~100000 포인트 지급 초콜릿상품포함시 추가1000포인트 지급
    family_month_20190515_point_cancel($od_id);

    nordic_20190520_1puls1_cancel($od_id);

    wooribank_201906_80_dc_cancel($od_id);

    //2019 05 27 5일간 메모리얼 기념 할인 이벤트 100불이상 5불 할인 취소
     memorial_ev_201905_100_5usddc_cancel($od_id);

    //라이프 익스 텐션 30불이상 2000포인트 취소
    life_extension_30usd_2000point_20190601_cancel($od_id);

    //20190601 원데이 이벤트 100불이상 5000포인트 지급 취소
    oneday_100usd_5000point_20190601_cancel($od_id);

    //2019년 05월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소
    month_buy_history_event_201906_cancel($od_id);

    //아이스팩
    icepack_add_2019_cancel($od_id);

    //네이쳐스 웨이 이벤트 해당 상품 40불이상 구매시 2000포인트 지급 금요일이벤트 중복적용 불가능
    natures_way_40usd_2000point_201906_cancel($od_id);

    //2019년 08월 노르딕 전상품 주문금액+실결제 90불 이상 5000포인트 지급
    nordic_201908_point_cancel($od_id);


    # 트리거 포인트 이벤트 경품 취소 강소진 #
    trigger_point_gift_cancel($od_id, $mb_id);

    # 노르딕 2+1 이벤트 우산 증정 강소진 #
    nordic_20190624_gift_cancel($od_id);

    # 독립기념일 이벤트 실결제 100불 이상 5000포인트 증정 강소진 #
    independence_20190701_point_cancel($od_id);

    # 2019년 06월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소 - 강소진 #
    month_buy_history_event_201907_cancel($od_id);
    
    # 201907 하나카드 50불 3프로적립 취소 - 강소진 #
    hana_every_50usd_3point_201907_cancel($od_id);

    # 201907 나우푸드 기획전 기획전 상품 40불 이상 주문 + 실결제 40불 2000포인트 적립 취소 - 강소진 #
    nowfoods_201907_point_cancel($od_id);

    # 201907 여름세일 이벤트 실결제 100불이상 5000포인트 증정 강소진 #
    summer_sale_201907_point_cancel($od_id);

    # 201908 노르딕 전상품 30%할인 이벤트 강소진 #
    nordic_member_price_item_event_data_cancel($od_id);

    # 2019년 07월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소 - 강소진 #
    month_buy_history_event_201908_cancel($od_id);

    # 2019년 08년 말복 이벤트 프로모션 제품 포함 실결제 50불 이상 2000포인트 지급 취소 -  강소진 #
    marbok_201908_point_cancel($od_id);

    # 2019년 08년 써니라이프 균일가 이벤트 프로모션 제품 포함 실결제 50불 이상 2000포인트 지급 취소 -  강소진 #
    sunnylife_201908_point_cancel($od_id);

    # 2019년 08월 노르딕 전상품 주문금액 + 실결제 90불 이상 5000포인트 지급 취소 #
    nordic_201908_point_cancel($od_id);

    # 2019년 08월 얼리버드 추석 실결제 60불 이상:3000, 100불이상:5000포인트 지급 취소 #
    early_chuseok_201908_point_cancel($od_id);

    # 2019년 8-9월 노르딕 전상품(2+1상품 제외) 20% 장바구니 할인 취소 #
    nordic_member_price_item_event_data_cancel_201908($od_id);

    # 2019년 9월 추석 실결제 70불 이상 결제금액 10% 포인트 지급 취소 #
    chuseok_201909_point_cancel($od_id);

    # 2019년 08월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소 - 강소진 #
    month_buy_history_event_201909_cancel($od_id);

    # 2019년 9월 자로우 기획전 상품 40불이상 주문 + 실결제 40불이상 2천 포인트 지급 취소 - 강소진 #
    jarrow_201909_point_cancel($od_id);

    # 2019년 10월 라이프 익스텐션 전상품 기준 40불 이상 주문 + 실결제 40불이상 3천 포인트 지급 취소 - 강소진 #
    life_extend_201909_point_cancel($od_id);

    # 임신출산 기획전 : it_id(1510623398) 구매 + 해당 기획전 상품 50불 이상 주문 + 실결제 50불 이상 : 2천포인트 지급 취소#
    child_birth_201909_point_cancel($od_id);

    /*
    //실적 이벤트 자동화 sko 2019.10.29
    if($_SERVER['REMOTE_ADDR'] == '211.214.213.101') {
        performance_auto_event_cancle($od_id);
    }else {
        # 2019년 09월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소 - 강소진 #
        month_buy_history_event_201910_cancel($od_id);
    }*/

    # 2019년 09월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소 - 강소진 #
    month_buy_history_event_201910_cancel($od_id);
    
    //2019 프리블프 할인 이벤트 100불이상 1만원 할인 취소
    preblack_2019_100usd_dc_cancel($od_id);

    # 2019년 10월 노르딕 전상품(2+1상품 제외) 25% 장바구니 할인 취소 # @nordic_20200413
    nordic_member_price_item_event_data_cancel_201910($od_id);

    # 201910 랜덤(9)숫자로 끝나는 주문서에 5천포인트 즉시지급(20191019일만) 취소 #
    random_orderno_point_cancel($od_id);

    # 2019년 11월 메가세일 해당브랜드 60불 이상 주문 + 실결제 60불 이상 3천포인트 지급 취소 #
    brand_mega_sale_2019_cancel($od_id);

    # 2019년 10월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소 - 강소진 #
    month_buy_history_event_201911_cancel($od_id);

    # 노르딕 제품 60불 이상 구매시 무선패드 증정 취소 - 강소진 #
    nordic_60usd_giftitem_201911_cancel($od_id);

    # 게릴라 이벤트 1111 100불이상 구매시 11% 포인트 증정 취소 #
    guerrilla_1111_point_cancel($od_id);

    # 201911 하나카드 50불 3프로적립 취소 - 강소진 #
    hana_every_50usd_3point_201911_cancel($od_id);

    #닥터스호프 상품 1개라도 구매시 루테인 증정 이벤트 취소#
    nrshope_19011_gift_item_cancel($od_id);

    #201911 블랙위크 이벤트 (60불이상:2천,100불이상:3천 포인트 지급) 취소#
    blackweek_201911_point_cancel($od_id);

    #2019 블랙프라이데이 노르딕 1+1 이벤트 주문 취소#
    bf_nordic_1911_1_plus1_cancel($od_id);

	# 2019년 12월 골라담기 이벤트 @choice_20191202
	pick_item_100_point_2019_cancel($od_id);

	# 2019년 11월 실적이벤트 50불이상 고객 횟수 1회0.5, 2회1, 3회2, 4회3 적립 취소 : @benefits_20191202
    month_buy_history_event_201912_cancel($od_id);

	# 2019년 12월 베스트 어워즈 이벤트 취소 : @bestawards_20191206
	best_awards_80_3000_2019_cancel($od_id);

    # 노르딕 제품 60불 이상 구매시 무선패드 증정 취소 201912~202001 - 강소진 #
    nordic_60usd_giftitem_201912_cancel($od_id);

	# 2019년 12월 구매누적왕 이벤트 취소 : @purchase_best_20191223
	purchase_best_201912_cancel($od_id);

	# 2019년 12월 25일 랜덤포인트 지급 취소 - 강소진
    x_mars_20191225_point_cancel($od_id);

	# 실적이벤트 자동화 : @benefit_auto
	month_buy_history_event_auto_cancel($od_id);

	# 2019년12월 포인트적립 게릴라이벤트 : @guerilla_201912
	guerrilla_201912_point_cancel($od_id);

    # 2020년 설날 이벤트 : @newyears_20190102
    newyear_2020_point_cancel($od_id);

	# 2020년 1월 더블혜택 이벤트 취소 @double_20200107
    double_202001_dc_cancel($od_id);

	# 2020년 1월 닥터스호프 상품(it_id:1511707777) 구매시 1000포인트 지급 취소 : @docters_hope_20200116
    drshope_2001_point_cancel($od_id);

    # 애프터설날 실결제 80불 이상 구매시 2000포인트 지급 취소 : @after_newyear_20200122
    after_newyear_point_cancel($od_id);

    //나우푸드 기획전 이벤트 포인트 지급 취소 @nowfoods_202002_point
    nowfoods_202002_point_cancel($od_id);

	// 출산육아 기획전 1000포인트 지급 취소 : @baby_20200206
    baby_202002_point_cancel($od_id);

	//2020년 02월 노르딕 제품 60불 이상 구매 치코백 무료 증정 취소 : @nordic_20200217
    nordic_60usd_giftitem_202002_cancel($od_id);

    //2020년 02월 블루보넷/헬시오리진스 제품 40불 이상 구매 증정품 무료 증정 취소 : @blueheal_gift_202002
    blue_healthy_gift_202002_insert_cancel($od_id);

    // 2020년 3월 더블혜택 이벤트 - $100 이상 구매 고객에게 5% 할인 + 5% 포인트 적립 : 할인 취소 @kb_double_202003
    kb_double_202001_dc_cancel($od_id);

	// 수령확인 취소 :: 첫구매 50$ 이상 구매시 추천 받은 고객에게 1000점 적립 취소: @ev_recom_20200306
	recommender_202003_cancel($od_id);

	// 싸이베이션 포인트 이벤트 50불이상 3000, 100불 이상 5000 포인트 적립 취소 : @ev_sciva_20200323
	scivation_point_20200323_cancel($od_id);

	// 2020년 4월 만우절 이벤트 랜덤 포인트 지급 취소 : @ev_manwoo_20200401
	manwoo_20200401_point_point_cancel($od_id);

	// 2020년 4월 더블혜택 이벤트 - $120 이상 구매 고객에게 10% 할인 + 10% 포인트 적립 : 할인 취소 @double_20200406
    double_202004_dc_cancel($od_id);

	// 2020년 04월 헬시오리진스 제품 35불 이상 구매 증정품 무료 증정 취소 : @blue_healty_202004
	blue_healthy_gift_202004_insert_cancel($od_id);

	// 202004 얼리버드 가정의달 실결제 100불 이상 구매시 5000포인트 지급 취소 : @early_202004_point
	early_202004_point_cancel($od_id);

	// 2020년 5월 노르딕 포인트 이벤트 50불이상 5000, 100불 이상 10000 포인트 적립 취소 : @nordic_20200501
	nordic_point_20200501_cancel($od_id);

	// 2020년 5월 가정의달 포인트 이벤트 50불이상 5%할인 취소 : @home_20200501
	home_point_20200501_dc_cancel($od_id);

	// 2020년 5월 가정의달 포인트 이벤트 60불이상 3000, 100불 이상 6000 포인트 적립 취소 : @home_20200501
	home_point_20200501_cancel($od_id);
    
    // 자로우 브랜드 제품 $35 이상 구매 시, 3천 포인트 적립 취소 : @jarrow_20200512
    // 기간 20200512 ~ 20200607
    jarrow_point_20200512_cancel($od_id);

	//2020 05 25 7일간 메모리얼 기념 할인 이벤트 100불이상 5% 할인 취소
	memorial_ev_202005_100_5usddc_cancel($od_id);
}


//# 2015 크리스마스 50/100달러 기념품 증정 취소 초기화 #
//// 전체취소 플래그는 어디에 있는지?
//if($_SERVER['REMOTE_ADDR']=="112.218.8.99"){
//if($ct_status == '취소'){
//	$xmas15_5010gift_chk	= sql_fetch("SELECT value4 AS fg, value5 AS it_id FROM yc4_event_data WHERE ev_code = 'xmas15_5010gift' AND ev_data_type = 'od_id' AND value1='".$od['mb_id']."' AND value2='".$od_id."'");
//	if($xmas15_5010gift_chk){
//	// 이벤트 데이터(od_id) 삭제
//		sql_query("DELETE from yc4_event_data where ev_code = 'xmas15_5010gift' AND ev_data_type = 'od_id' AND value1='".$od['mb_id']."' AND value2='".$od_id."'");
//	// 주문서 메모 추가
//		sql_query("UPDATE yc4_order SET od_shop_memo = CONCAT(od_shop_memo,'\\n','크리스마스 사은품 증정 취소(".$xmas15_5010gift_chk['fg']."달러 상품)') WHERE od_id = '".$od_id."' ");
//	// 이벤트 아이템 증정수량 업데이트
//		sql_query("UPDATE yc4_event_data SET value4 = CAST(value4 AS int) - 1 WHERE ev_code = 'xmas15_5010gift' AND ev_data_type = 'item_info' AND value1 = '".$xmas15_5010gift_chk['fg']."' AND value2 = '".$xmas15_5010gift_chk['it_id']."'");
//	}
//}
//}

if ($od['od_receipt_point'])
    alert("포인트로 결제한 주문은,\\n\\n주문상태 변경으로 인해 포인트의 가감이 발생하는 경우\\n\\n회원관리 > 포인트관리에서 수작업으로 포인트를 맞추어 주셔야 합니다.\\n\\n만약, 미수금이 발생하는 경우에는 DC에 금액을 음수로 입력하시면 해결됩니다.", $url);
else
    goto_url($url);

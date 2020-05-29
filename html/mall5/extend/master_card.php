<?php
/*
----------------------------------------------------------------------
file name	 : master_card
comment		 : 마스터카드 이벤트 처리 함수모음
date		 : 2014-11-05
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

// 마스터카드 체크
function master_card_chk($num){
	$num_chk = substr($num,0,2);

	if($num_chk >= 51 && $num_chk <=55){
		return true;
	}
	return false;
}

// kb vcn 체크
function kb_vcn_chk($num){
	$num_chk = substr($num,0,6);
	if(date('Ym') >= '201502'){ // 2월부터 종료
		return false;
	}
	// vcn bin 값
	$vcn_arr = array(
		'517633','538798','520982','510194'
		/*테스트*/
		//,'538720','542416'
	);

	if(in_array($num_chk,$vcn_arr)){
		return true;
	}
	return false;
}

// 할인 혜택 체크
function master_card_pro($num,$od_id,$amount=0){
	global $g4,$member;
	if(!master_card_chk($num)){
		return false;
	}

	if(date('Y') != '2014'){
		return false;
	}
	/*
		od_temp_card = 구매금액 + 배송비(od_send_cost)
	*/
	$od = sql_fetch("select od_id,on_uid,od_send_cost,od_temp_card from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");


	if(!$od){
		return false;
	}


	//$no_brand = sql_fetch("select group_concat('\'',it_maker,'\'') as no_maker_in from master_card_no_item where it_maker is not null");
	/*
	$no_it_id = sql_fetch("select group_concat('\'',it_id,'\'') as no_it_id_in from master_card_no_item where it_id is not null");
	$no_brand_sql = sql_query("select it_maker from master_card_no_item where it_maker is not null");
	while($row = sql_fetch_array($no_brand_sql)){
		$no_brand .= ($no_brand ? ",":"")."'".mysql_real_escape_string($row['it_maker'])."'";
	}

	//$no_brand = $no_brand['no_maker_in'];
	$no_it_id = $no_it_id['no_it_id_in'];

	if($no_brand){
		$sql_where .= ( $sql_where ? " or ":"" )."b.it_maker in (".$no_brand.")";
	}
	if($no_it_id){
		$sql_where .= ( $sql_where ? " or ":"" )."a.it_id in (".$no_it_id.")";
	}
	

	if($sql_where){
		$sql_where = " and ( ".$sql_where." )";
	}
	


	if($amount>0){
		$od_amount = $od_amount2 = $amount;
		$ct_amount_qry = "b.it_amount";
	}else{
		$od_amount = $od_amount2 = $od['od_temp_card'];
		$ct_amount_qry = "a.ct_amount";
	}


	# 프로모션에 해당하지 않는 상품들 총 가격 로드 #
	$od_item_sql = sql_fetch("
		select
			sum(".$ct_amount_qry." * a.ct_qty) as tot_amount
		from
			".$g4['yc4_cart_table']." a,
			".$g4['yc4_item_table']." b
		where
			a.it_id = b.it_id
			and
			a.on_uid = '".$od['on_uid']."'
			".$sql_where."
	");
	*/

	# 제외상품을 제외한 카드 결제금액
	$od_amount = (int)$od_amount - (int)$od_item_sql['tot_amount'];




	if($od['od_temp_card']<50000){ // 5만원 이하는 혜택 없음
		return false;
	}

	if($od['od_temp_card']>=50000){ // 5만원 이상 무료배송

		$result_amount = $od_amount2 - $od['od_send_cost'];

		$apply_fg = true;
	}


	if($od_amount >= 100000){ // 10만원 이상 구매금액 10% 할인

		//$result_amount = $result_amount - round( ($od_amount2 - $od['od_send_cost']) * 0.1);

		if( $od_amount < $od['od_send_cost'] ){
			$dc_price = 0;
		}else{
			$dc_price = round( ($od_amount - $od['od_send_cost']) * 0.1);
		}

		$result_amount = $result_amount - $dc_price;

		$apply_fg = true;
	}



	sql_query("
		insert into
			master_card_event
		(
			mb_id,od_id,
			ori_card_amount, card_amount, result_amount,
			point,pay_dt,
			complate_fg,point_complate_fg,event_code
		) values(
			'".$member['mb_id']."','".$od_id."',
			'".$od_amount2."', '". ( (int)$od_amount2 - (int)$od_item_sql['tot_amount'] )."', '".$result_amount."',
			0,now(),'n','n','kb_vcn'
		)
	");

	if(!$apply_fg){
		return false;
	}

	$insert_id = mysql_insert_id();

	return array($result_amount,$insert_id);
	/*
		5만원 이상 배송비 무료
		10만원 이상 배송비 무료 + 결제금액 10% 할인(일부상품 제외 $master_card_no_brand $master_cart_no_it_id)
		필요 데이터 :
			배송비
			일부상품 제외한 카드 결제금액
	*/
}

function master_card_pro_kb_vcn($num,$od_id,$amount = 0,$insert_id = null){
	global $g4,$member;
	if(!kb_vcn_chk($num)){
		return false;
	}

	$chk = sql_fetch("select count(*) as cnt from master_card_event where mb_id = '".$member['mb_id']."' and point > 0");
	if($chk['cnt'] > 0){ // 이미 혜택을 받았다면 Skip
		return false;
	}

	$od = sql_fetch("select on_uid,od_temp_card as amount from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");

	//$no_brand = sql_fetch("select group_concat('\'',it_maker,'\'') as no_maker_in from master_card_no_item where it_maker is not null");
	$no_it_id = sql_fetch("select group_concat('\'',it_id,'\'') as no_it_id_in from master_card_no_item where it_id is not null");

	$no_brand_sql = sql_query("select it_maker from master_card_no_item where it_maker is not null");
	while($row = sql_fetch_array($no_brand_sql)){
		$no_brand .= ($no_brand ? ",":"")."'".mysql_real_escape_string($row['it_maker'])."'";
	}

	/*
	//$no_brand = $no_brand['no_maker_in'];
	$no_it_id = $no_it_id['no_it_id_in'];

	if($no_brand){
		$sql_where .= ( $sql_where ? " or ":"" )."b.it_maker in (".$no_brand.")";
	}
	if($no_it_id){
		$sql_where .= ( $sql_where ? " or ":"" )."a.it_id in (".$no_it_id.")";
	}

	if($sql_where){
		$sql_where = " and ( ".$sql_where." )";
	}

	$od_item_sql = sql_fetch("
		select
			sum(a.ct_amount*a.ct_qty) as tot_amount
		from
			".$g4['yc4_cart_table']." a,
			".$g4['yc4_item_table']." b
		where
			a.it_id = b.it_id
			and
			a.on_uid = '".$od['on_uid']."'
			".$sql_where."
	");
	*/



	if($amount == 0){
		$amount = sql_fetch("select result_amount from master_card_event where uid = '".$insert_id."'");

		if($amount){
			$amount = $amount['result_amount'];
		}else{
			# 주문서 카드결제금액 로드 ( 제외상품 금액 마이너스 ) #
			$amount = $od['amount'] - $od_item_sql['tot_amount'];
		}

	}

	$point = round($amount * 0.05);

	/*
	# 주문서 카드결제금액 로드 #
	$od = sql_query("select od_temp_card from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");
	*/

	if($insert_id){
		sql_query("update master_card_event set point = '".$point."' where uid = '".$insert_id."'");
	}else{
		sql_query("
			insert into
				master_card_event
			(
				mb_id,od_id,
				ori_card_amount, card_amount, result_amount,
				point,pay_dt,
				complate_fg,point_complate_fg
			) values(
				'".$member['mb_id']."','".$od_id."',
				'".$od['amount']."','".$amount."','".$od['amount']."',
				'".$point."',now(),
				'n','n'
			)
		");
		$insert_id = mysql_insert_id();
	}

	return $insert_id;
}

function usd_convert($amount,$exchange_rate = null){ // 달러로 변환
	global $default;
	if(is_null($exchange_rate)) $exchange_rate = $default['de_conv_pay'];
	return round( ($amount / $exchange_rate) ,2);

}


# 삼성카드 bin 체크 #
function samsung_card_chk($num){
	$samsung_bin = array('510003','512365','517662','517709','517827','518319','520025','522855','527257','527419','529899','531070','531072','531080','531082','531085','533827','540447','540537','540538','541145','542184','545089','548869','552014','552412','553147','553176','558749','558953','558978','518831','536142','536148','536181','536648','552194');

	if(in_array(substr($num,0,6),$samsung_bin)){
		return true;
	}else{
		return false;
	}
}


# 신한 글로벌 카드 체크 2015-03-04 홍민기 #
function shinhan_global_chk($cd_no){
	global $shinhan_global_bin;


	$bin = substr($cd_no,0,6);
	if(in_array($bin,$shinhan_global_bin)){
		return true;
	}

	return false;
	
	
}


# 신한 글로벌 카드 혜택 적용 2015-03-04 홍민기 #
function shinhan_global_event($on_uid,$amount=0){
	global $g4;

	
	# 3월 9일부터 4월 30일까지 #
	if(date('Ymd') < '20150316'){
		return false;
	}

	if(date('Ymd') > '20150430'){
		return false;
	}

	# 주문 정보 로드 #
	$od = sql_fetch("
		select 
			od_id,od_send_cost,od_temp_card,mb_id 
		from 
			".$g4['yc4_order_table']." 
		where 
			on_uid = '".$on_uid."'
	");

	$event_data_chk = sql_fetch("select count(*) as cnt from master_card_event where od_id = '".$od['od_id']."' and point > 0 and event_code = 'sh_glb'");

	if($event_data_chk['cnt'] > 0){
		return false;
	}

	# 총 상품금액에서 포인트 결제금액은 제외 #
	if($amount>0){
		$tot_amount = $amount;
	}else{
		$tot_amount = $od['od_temp_card']; // 카드 결제금액 
	}
	

	$result_amount = $tot_amount;
	
	$return_point = round($tot_amount * 0.1); // 적립 포인트


	/*
	$dc_amount = 0;

	# 무료배송이 아닐경우에만 배송비 다시 책정 #
	if($od['od_send_cost'] > 0){
		# 배송비 책정 #
		$send_cost_info = sh_send_cost_chk($on_uid);
		$send_cost = $send_cost_info['send_cost'];

		# 기존배송비보다 새로 책정된 배송비가 낮을 경우에만 결제금액에서 배송비 할인 #
		if($od['od_send_cost'] > $send_cost){
			$dc_amount = $od['od_send_cost'] - $send_cost; // 할인금액(배송비 차액)
			$result_amount = $tot_amount - $dc_amount; // 결제 예상금액
			
		}
	}
	
	$result_amount_usd = usd_convert($result_amount); // 결제 예상금액(달러)

	# 주문서 업데이트 #
	$order_update_sql = "
		update
			".$g4['yc4_order_table']."
		set
			od_dc_amount = '".$dc_amount."',
			od_shop_memo = concat(od_shop_memo,'\\n','신한카드 프로모션')
		where
			on_uid = '".$on_uid."'
	";
	sql_query($order_update_sql);

	*/

	# 주문서 업데이트 #
	$order_update_sql = "
		update
			".$g4['yc4_order_table']."
		set
			od_shop_memo = concat(od_shop_memo,'\\n','신한카드 프로모션')
		where
			on_uid = '".$on_uid."'
	";
	sql_query($order_update_sql);

	# 이벤트 테이블 isnert #
	$event_table_chk = sql_fetch("
		select count(*) as cnt from master_card_event where od_id = '".$od['od_id']."'
	");

	if($event_table_chk['cnt'] > 0){
		$event_table_sql = "
			update
				master_card_event
			set
				point = '".$return_point."',
				event_code = 'sh_glb'
			where
				od_id = '".$od['od_id']."'
		";
		sql_query($event_table_sql);
	}else{

		$event_table_sql = "
			insert into 
				master_card_event
			(
				mb_id, od_id, 
				ori_card_amount, card_amount, result_amount, 
				point, pay_dt, complate_fg, point_complate_fg, event_code
			) 
			VALUES
			(
				'".$od['mb_id']."', '".$od['od_id']."', 
				".(int)$od['od_temp_card'].", ".(int)$od['od_temp_card'].", ".(int)$result_amount.", 
				'".$return_point."', '".$g4['time_ymdhis']."', 'y', 'n', 'sh_glb'
			)
		";
		sql_query($event_table_sql);
	}
	
	

	$result = array(
		'result_amount'	=> $result_amount,
		'result_amount_usd' => $result_amount_usd
	);

	/*
	echo '원래결제금액 - ' .$tot_amount.PHP_EOL;
	echo '최종 결제금액 - '.$result_amount.PHP_EOL;
	echo '적립 포인트 - '.$return_point.PHP_EOL;
	echo '배송비 - ' . $send_cost.PHP_EOL;
	echo '할인금액(배송비차액) - '.$dc_amount.PHP_EOL;
	*/

	return $result;
}

# 신한카드용 배송비 계산 #
function sh_send_cost_chk($on_uid){
	global $g4,$default;

	//$no_send_cost = $default['no_send_cost']; // 무료배송 금액
	$no_send_cost = 30 * $default['de_conv_pay']; // 30달러 이상 무료배송


	$data = sql_fetch("
		select
			sum(ifnull(b.it_health_cnt,0) * a.ct_qty) as tot_health_cnt,
			sum(a.ct_qty * a.ct_amount) as tot_amount
		from
			".$g4['yc4_cart_table']." a,
			".$g4['yc4_item_table']." b
		where
			a.it_id = b.it_id
			and
			a.on_uid = '".$on_uid."'
	");


	

	$send_cost = old_send_cost_chk($data['tot_amount']);
	$send_promo = false;
	$req_send_usd = usd_convert($no_send_cost); // 무료배송 필요 주문금액 달러
	$send_cost_usd = usd_convert($data['tot_amount']); // 주문금액 합계(달러)

	# 건기식이 6병 이하일 경우 7만원 이상은 무조건 무료배송 #
	if($data['tot_health_cnt'] <= $default['no_send_cost_health_cnt']){
		if($data['tot_amount'] >= $no_send_cost || $send_cost_usd >= $req_send_usd){
			$send_cost = 0;
			$send_promo = true;
		}

	}


	return array(
		'send_cost' => $send_cost, // 배송비
		'send_promo_fg' => $send_promo, // 프로모션 적용 여부
		'req_send_usd' => $req_send_usd, // 필요 주문금액(달러)
		'send_cost_usd' => $send_cost_usd, // 상품금액(달러)
		'health_cnt' => $data['tot_health_cnt'],
		'tot_amount' => $data['tot_amount']
	);
}


### 신한 아이해피 프로모션 시작 ###
# 신한 아이해피 카드 체크 #
function shinhan_ihappy_chk($cd_no){

	if (date('Ymd') < '20150407') {
		return false;
	}
	if (date('Ymd') > '20150531') {
		return false;
	}


    $bin = array('510737','538720'); // 신한 아이해피 bin 값

    $chk_bin = substr($cd_no,0,6);

    if(in_array($chk_bin,$bin)){
        return true;
    }
    return false;
}

# 해당 신한 아이해피카드로 결제한 내역이 있는지 체크 #
function shinhan_ihappy_pay_chk($cd_no){
    global $member;
    if(!$member['mb_id']){
        return false;
    }

    if(!shinhan_ihappy_chk($cd_no)){
        return false;
    }

    $chk = sql_fetch("
      select count(*) as cnt
      from yc4_card_history_sh_ihappy
      where mb_id = '".sql_safe_query($member['mb_id'])."'");
    if($chk['cnt']>0){
        return false;
    }
    return true;
}

# 신한 아이해피 카드 결제정보 저장 #
function shinhan_ihappy_pay_save($od_id,$cd_amount,$cd_amount_usd,$cd_no){
    global $member,$g4;
    if(!shinhan_ihappy_pay_chk($cd_no)){
        return false;
    }
    $cd_return_point = $cd_amount * 0.1;
    $sql = "
        insert into yc4_card_history_sh_ihappy
          (od_id, cd_amount, cd_amount_usd, mb_id, cd_card_no, cd_return_point, cd_point_complete_yn)
        VALUES (
          '" . mysql_real_escape_string($od_id) . "', '" . mysql_real_escape_string($cd_amount) . "', '" . mysql_real_escape_string($cd_amount_usd) . "', '" . mysql_real_escape_string($member['mb_id']) . "', '" . mysql_real_escape_string($cd_no) . "', '" . mysql_real_escape_string($cd_return_point) . "','N'
        )
    ";
    if(!sql_query($sql)){
        return false;
    }

    $sql = "
        update ".$g4['yc4_order_table']."
        set
            od_shop_memo = concat(od_shop_memo,'\\n','신한 아이행복카드 10% 적립 혜택 대상입니다.')
        where
            od_id = '".$od_id."'
    ";
    if(!sql_query($sql)){
        return false;
    }

    return true;

}
### 신한 아이해피 프로모션 끝 ###



### 하나카드 프로모션 시작 ###
function hanacard_chk($cd_no){
    /*
     * 하나카드 체크(결제전)
     * */

    $bin_arr = array(
        '511845', '516411', '516450', '516574', '524180', '524242', '528523', '531838', '532092', '532147', '537725', '538825', '541707', '544174', '546252', '552125', '552323', '552407', '636189', '529875', '553173', '558738', '543617', '540497', '518283', '518185', '523830', '524335', '549861', '524154', '540799', '537733', '538833', '552133'
        ,'552123'
    );

    $bin = substr($cd_no,0,6);
    if(in_array($bin,$bin_arr)){
        return true;
    }

    return false;
}

# 하나카드 주문건이 있는지 검사 (결제전) #
function hanacard_order_chk($mb_id){

    if(!$mb_id){
        return false;
    }

    $chk = sql_fetch("select count(*) as cnt from yc4_event_data where ev_code = 'hana' and ev_data_type = 'od_id' and value2 = '".$mb_id."'");
    if($chk['cnt']>0){
        return false;
    }
    return true;
}

# 하나카드 무료배송 처리 (결제 전)#
function hanacard_send_cost_free($od_id,$amount){
    global $g4;

    $od = sql_fetch("select * from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");

    if(usd_convert($amount) < 50){ // 50달러 미만은 해당 없음
        return false;
    }


    if($od['od_send_cost'] == 0){
        return false;
    }

    # 해당 주문서가 이미 할인을 받았는지 체크 #
    $already_chk = sql_fetch("
        select count(*) as cnt from yc4_event_data
        where ev_code = 'hana' and ev_data_type = 'send_cost_free' and value1 = '".$od_id."'
    ");

    if($already_chk['cnt']>0){
        return false;
    }

    # 건기식 병수 체크 (6병 초과시 해당 안됨) #
    $health_chk = sql_fetch("
        select
            sum(ifnull(b.it_health_cnt,0) * a.ct_qty) as tot_health_cnt
        from
            ".$g4['yc4_cart_table']." a,
            ".$g4['yc4_item_table']." b
        where
            a.it_id = b.it_id
            and a.on_uid = '".$od['on_uid']."'
    ");

    if($health_chk['tot_health_cnt'] > 6){
        return false;
    }

    $dc_amount = $od['od_send_cost'];

    $update_sql = "
        update ".$g4['yc4_order_table']."
        set
            od_shop_memo = concat(od_shop_memo,'\\n','하나카드 무료배송 대상자 배송비 할인(".number_format($dc_amount)."원)'),
            od_dc_amount = od_dc_amount + ".(int)$dc_amount."
        where
            od_id = '".$od['od_id']."'
    ";



    sql_query($update_sql);

    $result_arr = array(
        'result_amount' => $amount - $dc_amount,
        'result_amount_usd' => usd_convert($amount - $dc_amount)
    );

    # 히스토리 저장 #
    sql_query("
        insert into yc4_event_data
        (ev_code,ev_data_type,value1,value2,value3)
        VALUES
        ('hana','send_cost_free','".$od_id."','".$od['mb_id']."','".$result_arr['result_amount']."')
    ");

    //echo $update_sql;
    return $result_arr;
}

# 하나카드 첫구매시 10% 적립 (결제 후) #
function hanacard_first_event($od_id){
    global $g4;
    $od = sql_fetch("select * from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");

    if($od['od_receipt_card'] == 0){
        return false;
    }
    if(!hanacard_order_chk($od['mb_id'])){
        return false;
    }


    $event_point = round($od['od_receipt_card'] * 0.1);

    if($event_point == 0){
        return false;
    }

    # 적립 포인트 저장 #
    $sql = "
        insert into yc4_event_data
        (ev_code,ev_data_type,value1,value2,value3)
        VALUES
        ('hana','od_id','".$od['od_id']."','".$od['mb_id']."','".$event_point."')
    ";
    //echo $sql;
    sql_query($sql);

    $update_sql = "
        update ".$g4['yc4_order_table']."
        set
            od_shop_memo = concat(od_shop_memo,'\\n','하나카드 첫 구매 10% 적립 대상')
        where
            od_id = '".$od['od_id']."'
    ";
    sql_query($update_sql);
    //echo $update_sql;

    return true;
}

# 하나카드 1+1 이벤트 결제 후 #
function hanacard_event_item_chk($od_id){
    global $g4;

    $od = sql_fetch("select * from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");

    $chk = sql_fetch("
        select
          count(*) as cnt
        from yc4_event_data
        where
          ev_code = 'hana'
          and ev_data_type = 'event_item_win'
          AND value2 = '".$od['mb_id']."'
    ");

    # 이벤트 상품을 받은적이 있다면 해당 안됨 #
    if($chk['cnt'] > 0){
        return false;
    }

    $sql = sql_query("select distinct it_id from ".$g4['yc4_cart_table']." where on_uid = '".$od['on_uid']."'");
    $it_id_in = '';
    while($row = sql_fetch_array($sql)){
        $it_id_in .= ($it_id_in ? ",":"")."'".$row['it_id']."'";
    }

    if(!$it_id_in){
        return false;
    }

    $sql = sql_query("
        select value3 as it_id, value4-value5 as qty
        from yc4_event_data
        where
            ev_code = 'hana'
            and ev_data_type = 'event_item'
            and ".date('Ymd')." between value1 and value2
            and value3 in (".$it_id_in.")
            and value4 - value5 > 0
    ");


    $result_arr = array();
    $result_cnt = 0;
    while($row = sql_fetch_array($sql)){
        $result_arr[] = $row;
        $result_cnt++;
    }
    if($result_cnt<1){
        return false;
    }

    return $result_arr;
}






function hanacard_event_item_choice($data){
    global $g4;

    # 중복 적용 방지 #
    $already_chk = sql_fetch("
        select
          count(*) as cnt
        from yc4_event_data
        where
          ev_code='hana'
          and ev_data_type='event_item_win'
          and value1 = '".sql_safe_query($data['od_id'])."'
    ");
    if($already_chk['cnt'] > 0){
        return false;
    }

    $insert_sql = "
        insert into yc4_event_data
        (ev_code,ev_data_type,value1,value2,value3,value4)
        VALUES
        ('hana','event_item_win','".sql_safe_query($data['od_id'])."','".sql_safe_query($data['mb_id'])."','".sql_safe_query($data['it_id'])."','".$g4['time_ymdhis']."')
    ";

    sql_query($insert_sql);

    $update_sql = "
        update yc4_event_data
        set value5 = value5 + 1
        WHERE
          ev_code = 'hana'
          and ev_data_type = 'event_item'
          and value3 = '".sql_safe_query($data['it_id'])."'
          and ".date('Ymd')." between value1 and value2
    ";

    sql_query($update_sql);



    sql_query("
				insert into
					".$g4['yc4_cart_table']."
				set
					on_uid = '".$data['on_uid']."',
					it_id = '".$data['it_id']."',
					it_opt1 = '',
					it_opt2 = '',
					it_opt3 = '',
					it_opt4 = '',
					it_opt5 = '',
					it_opt6 = '',
					ct_status = '준비',
					ct_history = '하나카드 이벤트 증정 상품',
					ct_amount = 0,
					ct_point = 0,
					ct_point_use = 0,
					ct_stock_use = 0,
					ct_qty = 1,
					ct_time = '".date('Y-m-d H:i:s')."',
					ct_ip = '".$_SERVER['REMOTE_ADDR']."',
					ct_send_cost = '',
					ct_mb_id = '".$data['mb_id']."',
					ct_ship_os_pid = '',
					ct_ship_ct_qty = '',
					ct_ship_stock_use = ''

			");
    sql_query("
        update ".$g4['yc4_order_table']."
        set
            od_shop_memo = concat(od_shop_memo,'\\n','하나카드 이벤트 사은품 증정(".$data['it_id'].")')
        where od_id = '".$data['od_id']."'
    ");

    return true;
}

# 하나카드 첫결제 상품 수령확인시 포인트 지급 #
function hanacard_order_confirm($od_id){
    global $g4;

    $sql = sql_fetch("
        select
            value3 as point
        from yc4_event_data
        where
            ev_code = 'hana'
            and ev_data_type = 'od_id'
            and value1 = '".$od_id."'
            and value5 is null
    ");


    if($sql['point']){
        $od = sql_fetch("select mb_id from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");
        insert_point($od['mb_id'],$sql['point'],'하나카드 첫결제 10% 적립 포인트 지급');

        sql_query("
            update ".$g4['yc4_order_table']."
            set
                od_shop_memo = concat(od_shop_memo,'\\n','하나카드 첫결제 10% 적립 완료(".$sql['point'].")')
            where
                od_id = '".$od_id."'
        ");
        return true;
    }
    return false;

}

### 하나카드 프로모션 끝 ###
?>
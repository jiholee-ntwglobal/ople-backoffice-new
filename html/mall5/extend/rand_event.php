<?php
/*
----------------------------------------------------------------------
file name	 : rand_event.php
comment		 : 즉석 추첨 이벤트
date		 : 2014-12-11
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

// 이벤트 랜덤 뽑기 키 생성
function rand_key_new($od_id,$ev_code,$ev_cnt){
	/*
		$ev_cnt = 추첨수
	*/
	global $g4,$member;






	if($ev_code == 5){ // 고디바 골드발로틴 19개 사은품 지급 2015-02-02 홍민기
		/*
			화이트데이 이벤트 페이지 상품을 샀으면서 구매금액 5만원(배송비제외) 이상인 경우
		*/

        return false; // 이벤트 종료

		$chk = sql_fetch("
			select
				sum(b.ct_amount*b.ct_qty) as tot_amount,
				sum(if(c.it_id,1,0)) as white_day_item_cnt
			from
				".$g4['yc4_order_table']." a
				left join
				".$g4['yc4_cart_table']." b on a.on_uid = b.on_uid
				left join
				yc4_event_item c on b.it_id = c.it_id and c.ev_id = '1418289584'
			where
				a.od_id = '1501230076'
		");

		if($chk['tot_amount'] < 50000 || $chk['white_day_item_cnt']<1){
			return false;
		}

		# 해당 이벤트가 끝났는지 체크 #
		$ev_cnt_chk = sql_fetch("select count(*) as cnt from yc4_event_item_rand where create_date = '".date('Y-m-d')."' and ev_code = '".$ev_code."'");

		if($ev_cnt_chk['cnt'] >= $ev_cnt){

			return false;
		}

		# 만약 동일한 주문번호가 있다면 제외 #
		$ev_cnt_chk = sql_fetch("select count(*) as cnt from yc4_event_item_rand where od_id = '".$od_id."'");

		if($ev_cnt_chk['cnt'] > 0){
			return false;
		}

		# 동일 이벤트에 한번이라도 당첨되었다면 그사람은 기회없음 #
		$ev_cnt_chk = sql_fetch("select count(*) as cnt from yc4_event_item_rand where mb_id = '".$member['mb_id']."' and ev_code = '".$ev_code."'");

		if($ev_cnt_chk['cnt'] > 0){
			return false;
		}
	}

    if(in_array($ev_code,array(6,7))){ // 커피이벤트 스타벅스 사은품
        if($_SERVER['REMOTE_ADDR'] != '59.17.43.129') {
            if (date('Ymd') < '20150414') {
                return false;
            }

            if (date('Ymd') > '20150430') {
                return false;
            }
        }



        if(!event_coffee_chk($od_id)){
            return false;
        }



        # 해당 이벤트가 끝났는지 체크 #
        $ev_cnt_chk = sql_fetch("select count(*) as cnt from yc4_event_item_rand where create_date = '".date('Y-m-d')."' and ev_code = '".$ev_code."'");

        if($ev_cnt_chk['cnt'] >= $ev_cnt){

            return false;
        }

        # 만약 동일한 주문번호가 있다면 제외 #
        $ev_cnt_chk = sql_fetch("select count(*) as cnt from yc4_event_item_rand where od_id = '".$od_id."'");

        if($ev_cnt_chk['cnt'] > 0){
            return false;
        }

        # 동일 이벤트에 한번이라도 당첨되었다면 그사람은 기회없음 #
        $ev_cnt_chk = sql_fetch("select count(*) as cnt from yc4_event_item_rand where mb_id = '".$member['mb_id']."' and ev_code = '".$ev_code."'");

        if($ev_cnt_chk['cnt'] > 0){
            return false;
        }

    }

	# 22~23시 까지는 당첨 확률 대폭 향상
	if(date('H') >= '22'){
		$rand = rand(1,30);
	}else{
		$rand = rand(1,5);
	}


	return $rand;
}


function rand_event_gogo($od_id){
	/*
		1.고디바
		2.아워홈
		4.발렌타인데이 이벤트
		5.화이트데이 이벤트
	    6.커피이벤트(커피)
	    7.커피이벤트(머그컵)
	*/

	global $g4,$member;


	$ev_key_arr = array(
		1,2
	);
	$ev_ok_cnt = 2; // 이벤트 추첨인수
	$rand = rand_key_new($od_id,6,$ev_ok_cnt);
	$cp_fg = false;


	# 해당 주문서의 on_uid를 로드 #
	$on_uid = sql_fetch("select on_uid from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");
	$on_uid = $on_uid['on_uid'];

	if($rand){
		if(in_array($rand,$ev_key_arr)){
			sql_query("
				insert into
					yc4_event_item_rand
				(
					od_id,rand_key,create_date,create_dt,mb_id,comment,ev_code
				)values(
					'".$od_id."','".$rand."','".date('Y-m-d')."','".date('Y-m-d H:i:s')."','".$member['mb_id']."','커피이벤트 이벤트 당첨',6
				)
			");

			sql_query("
				insert into
					".$g4['yc4_cart_table']."
				set
					on_uid = '".$on_uid."',
					it_id = '1365659028',
					it_opt1 = '',
					it_opt2 = '',
					it_opt3 = '',
					it_opt4 = '',
					it_opt5 = '',
					it_opt6 = '',
					ct_status = '준비',
					ct_history = '즉석 추첨 이벤트 상품(커피이벤트)',
					ct_amount = 0,
					ct_point = 0,
					ct_point_use = 0,
					ct_stock_use = 0,
					ct_qty = 1,
					ct_time = '".date('Y-m-d H:i:s')."',
					ct_ip = '".$_SERVER['REMOTE_ADDR']."',
					ct_send_cost = '',
					ct_mb_id = '".$member['mb_id']."',
					ct_ship_os_pid = '',
					ct_ship_ct_qty = '',
					ct_ship_stock_use = ''

			");

            $cp_fg = true; // 고디바 이벤트 당첨!
		}
	}

    if($cp_fg == false){
        $ev_key_arr = array(
            3
        );
        $ev_ok_cnt = 1; // 이벤트 추첨인수
        $rand = rand_key_new($od_id,7,$ev_ok_cnt);
        $cp_fg = false;


        # 해당 주문서의 on_uid를 로드 #
        $on_uid = sql_fetch("select on_uid from ".$g4['yc4_order_table']." where od_id = '".$od_id."'");
        $on_uid = $on_uid['on_uid'];

        if($rand){
            if(in_array($rand,$ev_key_arr)){
                sql_query("
				insert into
					yc4_event_item_rand
				(
					od_id,rand_key,create_date,create_dt,mb_id,comment,ev_code
				)values(
					'".$od_id."','".$rand."','".date('Y-m-d')."','".date('Y-m-d H:i:s')."','".$member['mb_id']."','커피이벤트',7
				)
			");

                sql_query("
				insert into
					".$g4['yc4_cart_table']."
				set
					on_uid = '".$on_uid."',
					it_id = '1428903985',
					it_opt1 = '',
					it_opt2 = '',
					it_opt3 = '',
					it_opt4 = '',
					it_opt5 = '',
					it_opt6 = '',
					ct_status = '준비',
					ct_history = '즉석 추첨 이벤트 상품(커피이벤트)',
					ct_amount = 0,
					ct_point = 0,
					ct_point_use = 0,
					ct_stock_use = 0,
					ct_qty = 1,
					ct_time = '".date('Y-m-d H:i:s')."',
					ct_ip = '".$_SERVER['REMOTE_ADDR']."',
					ct_send_cost = '',
					ct_mb_id = '".$member['mb_id']."',
					ct_ship_os_pid = '',
					ct_ship_ct_qty = '',
					ct_ship_stock_use = ''

			");

                $cp_fg = true; // 고디바 이벤트 당첨!
            }
        }
    }


	// true가 있다면 당첨
	return array('cp_fg'=>$cp_fg);
}



# 커피 이벤트 대상인지 체크 #
function event_coffee_chk($od_id){
    global $g4;


    $item_arr = array('1504152709', '1504153355', '1504154135', '1504155417', '1504160450', '1504161736', '1504162228', '1504163451', '1504164934', '1504165459', '1411115714', '1411114735', '1394528598', '1394528281', '1370118279', '1370118005', '1370117826', '1365660715', '1365660428', '1365659028', '1351203476', '1365659655');

    $item_in = "";
    foreach ($item_arr as $val) {
        $item_in .= ($item_in ? ",":"")."'".$val."'";
    }

    $od = sql_fetch("
    select od_id,on_uid,od_receipt_bank,od_receipt_card from ".$g4['yc4_order_table']." where od_id = '".$od_id."'
");

    $item_chk = sql_fetch("
        select
          count(*) as cnt
        from ".$g4['yc4_cart_table']."
        where
            on_uid = '".$od['on_uid']."'
            and ct_status = '준비'
            and it_id in (".$item_in.")
    ");




    if($item_chk['cnt'] < 1){
        return false;
    }

    if( ($od['od_receipt_bank'] + $od['od_receipt_card'])  < 60000){
        return false;

    }

    return true;
}


?>
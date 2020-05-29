<?php
// 카테고리 상품 추기시
function sales_report_cart($on_uid,$it_id = null){
	global $connect_db,$g4,$default;


	$sales_db_info['host']	= "209.216.56.102";
	$sales_db_info['user']	= "okflex5";
	$sales_db_info['pass']	= "dhwhtksfh$%-*";
	$sales_db_info['db']	= "okflex5";
	$sales_db = $connect_db;


	$rate = $default['de_conv_pay']; // 환율

	// 주문 시간
	$od_time = sql_fetch("select od_time from okflex5.".$g4['yc4_order_table']." where on_uid = '".$on_uid."'");
	$od_time = $od_time['od_time'];

	$ct_qry = sql_query("
		select
			ct_id,it_id,ct_qty,ct_amount,ct_status
		from
			okflex5.".$g4['yc4_cart_table']."
		where
			on_uid = '".$on_uid."'
			".($it_id ? " and it_id = '".$it_id."'":"")."
	");






	while($ct = sql_fetch_array($ct_qry)){

		// 이미 저장된 데이터가 있다면 delete 후 insert
		mysql_query("delete from sales.op_sales_item where ct_id = '".$ct['ct_id']."'",$sales_db);

		// 주문 상태 코드
		switch($ct['ct_status']){
			case '쇼핑'	: $status = 0; break;
			case '주문'	: $status = 1; break;
			case '준비'	: $status = 2; break;
			case '배송'	: $status = 3; break;
			case '완료'	: $status = 4; break;
			case '취소'	: $status = 5; break;
			default		: $status = 99; break;
		}


		// 주문 수량만큼 insert
		$values = false;
		for($i=1; $i<=$ct['ct_qty']; $i++){
			$values .= ($values ? ",":"")."( '".$ct['ct_id']."','".$ct['it_id']."','".$od_time."','0','".$status."' )";
		}



		if($values){
			$insert_qry = "
				insert into
					sales.op_sales_item
				(
					ct_id,it_id,dt,rate,status
				)
				values ".$values."
			";


			mysql_query($insert_qry,$sales_db);
		}
	}


}


// 배송 상태 변경 -> 회원 전용
function sales_report_cart_update($on_uid,$it_id = null,$rate = 0){
	global $g4,$default,$_HOTDEAL_FG,$member;




	$nonstop_data = sql_fetch("select * from okflex5.yc4_nontop_sale where status=2");

	// 주문 날짜
	$od_info = sql_fetch("select left(od_time,10) as od_time,od_id from okflex5.".$g4['yc4_order_table']." where on_uid = '".$on_uid."'");
	$od_time = $od_info['od_time'];
	$od_id = $od_info['od_id'];

	$ct_qry = sql_query("
		select
			ct_id,it_id,ct_qty,ct_amount,ct_status
		from
			okflex5.".$g4['yc4_cart_table']."
		where
			on_uid = '".$on_uid."'
			".($it_id ? " and it_id = '".$it_id."'":"")."
	");




	while($ct = sql_fetch_array($ct_qry)){
		// 주문 상태 코드
		switch($ct['ct_status']){
			case '쇼핑'	: $status = 0; break;
			case '주문'	: $status = 1; break;
			case '준비'	: $status = 1; break;
			case '배송'	: $status = 1; break;
			case '완료'	: $status = 1; break;
			case '취소'	: case '반품'	: case '품절'	:
				$status = 2; break;
			default		: $status = 99; break;
		}

		if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){

			if($nonstop_data['it_id'] == $ct['it_id'] && $status == 1){

				sql_query("update okflex5.yc4_nontop_sale set sell_qty=sell_qty+$ct[ct_qty] where it_id='$ct[it_id]' and status='2'");

				if($nonstop_data['ev_qty'] - ($nonstop_data['sell_qty'] + $ct['ct_qty']) < 1 ){

					sql_query("update okflex5.yc4_item set status=3 where it_id='$ct[it_id]' and status='2'");

					if($nonstop_data['it_order_onetime_limit_fg'] == 'N'){
						sql_query("update okflex5.yc4_item set it_order_onetime_limit_cnt=0 where it_id='$ct[it_id]'");
					}

					$next_info = sql_fetch("select min(seq) as seq from yc4_nontop_sale where status=1");

					if($next_info['seq']){

						sql_query("update okflex5.yc4_nontop_sale set status='2' where seq='".$next_info['seq']."'");

						$nonstop_data2 = sql_fetch("select * from okflex5.yc4_nontop_sale where seq='".$next_info['seq']."'");

						if($nonstop_data2['it_order_onetime_limit_fg'] == 'N'){
							sql_query("update okflex5.yc4_item set it_order_onetime_limit_cnt=10 where it_id='$ct[it_id]'");
						}

					}

				}

			}

		}


		/*
		$update_qry = "
			update
				sales.op_sales_item
			set
				status = '".$status."',
				rate = '".$rate."'
			where
				ct_id = '".$ct['ct_id']."'
		";
		*/
		$bl_no_sold_out_it = array('1314862063','1320764100','1142299650','1398838165');
		for($i=1; $i<=$ct['ct_qty']; $i++){
			$update_qry = "
				insert into
					sales.op_sales_item
				(
					ct_id,it_id,dt,rate,status
				)values(
					'".$ct['ct_id']."','".$ct['it_id']."','".$od_time."','".$rate."','".$status."'
				)
			";
			if(mysql_query($update_qry)){
				$up_sql_set = false;
				$event_item_stcok = 0;
				switch($status){
					case 1 :
						$up_sql_set = " total_sold = total_sold + 1";
						$event_item_stcok = 1;
						break;
					case 2 :
						$up_sql_set = " total_sold = total_sold - 1";
						$event_item_stcok = -1;
						break;
				}
				mysql_query("
					update okflex5.yc4_event_item_stock set sell_cnt = sell_cnt + (".$event_item_stcok.") where it_id = '".$ct['it_id']."' and isupdate is null
				");

				// 별도재고관리 상품 품절 처리 2014-12-08
				$ev_qty_chk = sql_fetch("select count(*) as cnt from yc4_event_item_stock where it_id = '".$ct['it_id']."' and isupdate is null");


				if($ev_qty_chk['cnt'] > 0 ){
					$ev_qty_chk2 = sql_fetch("select qty-sell_cnt as result_qty,ch_amount from yc4_event_item_stock where it_id = '".$ct['it_id']."' and isupdate is null");

					if($ev_qty_chk2['result_qty'] <= 0){
						sql_query("update yc4_event_item_stock set isupdate = 'y', update_dt = '".date('Y-m-d H:i:s')."' where it_id = '".$ct['it_id']."' and isupdate is null");

						$naver_brief_chk = sql_fetch("select uid from naver_ep_brief where it_id = '".$ct['it_id']."' and generate_time is null");



						# 원래 가격이 있으면 가격 변경, 없으면 품절처리 2014-12-26 홍민기 #
						if($ev_qty_chk2['ch_amount']){
							sql_query("update ".$g4['yc4_item_table']." set it_amount = '".$ev_qty_chk2['ch_amount']."' where it_id = '".$ct['it_id']."'");

							if(!$naver_brief_chk['uid']){
								sql_query("
									insert into
										naver_ep_brief
									(it_id,update_yn,create_date)
									values
									('".$ct['it_id']."','Y','".$g4['time_ymdhis']."')
								");
							}else{
								sql_query("
									update
										naver_ep_brief
									set
										update_yn = 'Y'
									where
										uid = '".$naver_brief_chk['uid']."'
								");
							}
						}else{
//							sql_query("update ".$g4['yc4_item_table']." set it_stock_qty = '0'  where it_id = '".$ct['it_id']."'");

							if(!$naver_brief_chk['uid']){
								sql_query("
									insert into
										naver_ep_brief
									(it_id,pause_yn,resume_yn,create_date)
									values
									('".$ct['it_id']."','Y',null,'".$g4['time_ymdhis']."')
								");
							}else{
								sql_query("
									update
										naver_ep_brief
									set
										pause_yn = 'Y',
										resume_yn = null
									where
										uid = '".$naver_brief_chk['uid']."'
								");
							}
						}



						# 품절 히스토리 남김 #
						/*sql_query("update yc4_soldout_history set current_fg='N' where it_id='".$ct['it_id']."'");

						sql_query("
							insert into
								yc4_soldout_history
							(
								it_id,flag,mb_id,time,ip,current_fg
							)
							values(
								'".$ct['it_id']."','o','auto(".$member['mb_id'].")','".date('Y-m-d H:i:s')."','".$_SERVER['REMOTE_ADDR']."','Y'
							)
						");*/
					}
				}

				/*
				# 블랙프라이데이 판매량 카운트 증가 2014-11-27 홍민기
				mysql_query("update okflex5.yc4_bl_event_item_stock set sell_cnt = sell_cnt + (".$event_item_stcok.") where it_id = '".$ct['it_id']."' and isupdate is null");

				// 수량 이상 판매되면 자동 품절 처리
				if(!in_array($ct['it_id'],$bl_no_sold_out_it)){
					$bl_qty_chk = mysql_fetch_assoc(mysql_query("select (qty - sell_cnt) as cnt from yc4_bl_event_item_stock where it_id = '".$ct['it_id']."' and isupdate is null"));
					if($bl_qty_chk && $bl_qty_chk['cnt']<=0){
						mysql_query("update yc4_item set it_stock_qty = 0 where it_id = '".$ct['it_id']."'");
						mysql_query("update yc4_bl_event_item_stock set isupdate = 'y' where it_id = '".$ct['it_id']."'");
					}
				}
				*/


				/*
				# 블랙프라이데이 이벤벤트 - 품절처리 대상이면서 자동품절인 상품 정보 로드
				$bl_chk = mysql_fetch_assoc(mysql_query("
					selcet
						uid
					from
						okflex5.yc4_bl_event_item_stock
					where
							it_id = '".$ct['it_id']."'
						and isupdate is null
						and auto_sold_out = 'y'
						and qty <= sell_cnt
				"));
				if($bl_chk['uid']){

				}
				*/

				if($up_sql_set){
					$report_update_qry = "
						update
							sales.op_sales_report
						set
							".$up_sql_set."
						where
							it_id = '".$ct['it_id']."'
							and
							dt = '".$od_time."'
					";
					mysql_query($report_update_qry);
				}

				# 핫딜존 처리 시작 2015-01-27 홍민기 #
				if($_HOTDEAL_FG){
					$hotdeal_chk = sql_fetch("
						select
							uid,qty,sell_qty,sort
						from
							okflex5.yc4_hotdeal_item
						where
							it_id = '".$ct['it_id']."'
							and
							flag = 'Y'
							and sort > 0
							and sort < 9
					");
					if($hotdeal_chk){

						# 핫딜존 판매수량 카운트 증가 #
						sql_query("
							update
								okflex5.yc4_hotdeal_item
							set
								sell_qty = sell_qty + 1
							where
								uid = '".$hotdeal_chk['uid']."'
						");
						# 핫딜존 상품 품절시 처리 #
						if($hotdeal_chk['qty'] - ($hotdeal_chk['sell_qty'] + 1) < 1 ){
							// 핫딜존 종료 처리
							sql_query("
								update
									okflex5.yc4_hotdeal_item
								set
									flag = 'E',
									en_dt = '".$g4['time_ymdhis']."'
								where
									uid = '".$hotdeal_chk['uid']."'
							");

							// 종료된 핫딜존 상품 뒤의 상품 순서를 앞으로 한칸씩 이동
							sql_query("
								update
									okflex5.yc4_hotdeal_item
								set
									sort = sort - 1
								where
									flag = 'Y'
									and
									sort > '".$hotdeal_chk['sort']."'
							");

							# 메인 페이지 캐싱파일 재생성
							exec("/usr/bin/php /ssd/html/mall5/cron/main_data_cache.php");

						}
					}
				}
				# 핫딜존 처리 끝 #
			}
		}

		if($status == '2'){
			sql_query("
				insert into
					cancel_order
				(
					od_id,on_uid,work,create_date
				) values(

					'".$od_id."','".$on_uid."','N','".date('Y-m-d H:i:s')."'
				)
			");
		}
	}



}


function sales_report_cart_update_adm($ct_id,$ct_status,$bf_status){

	global $connect_db,$g4,$default,$_HOTDEAL_FG,$member;
	$sales_db = $connect_db;
	if($ct_status == $bf_status){ // 배송 상태 변경이 없다면 끝
		return false;
	}
	$false_arr = array('취소','반품','품절');
	if( in_array($ct_status,$false_arr) && in_array($bf_status,$false_arr) ){ // 둘다 취소반품품절이면 액션없음
		return false;
	}
	unset($false_arr);

	switch($ct_status){
		case '쇼핑'	: $status_fg = 0; break;
		case '주문'	: $status_fg = 1; break;
		case '준비'	: $status_fg = 1; break;
		case '배송'	: $status_fg = 1; break;
		case '완료'	: $status_fg = 1; break;
		case '취소'	: case '반품'	: case '품절'	:
			$status_fg = 2; break;
		default		: $status_fg = 99; break;
	}

	switch($bf_status){
		case '쇼핑'	: $bf_status_fg = 0; break;
		case '주문'	: $bf_status_fg = 1; break;
		case '준비'	: $bf_status_fg = 1; break;
		case '배송'	: $bf_status_fg = 1; break;
		case '완료'	: $bf_status_fg = 1; break;
		case '취소'	: case '반품'	: case '품절'	:
			$bf_status_fg = 2; break;
		default		: $bf_status_fg = 99; break;
	}

	if($status_fg == $bf_status_fg){ // 상태 같으면 return false;
		return false;
	}

	$nonstop_data = sql_fetch("select * from okflex5.yc4_nontop_sale where status=2");



	// 주문 날짜
	$od_time = sql_fetch("
		select
			a.it_id,
			a.ct_qty,
			b.od_id,
			b.on_uid,
			left(od_time,10) as od_time
		from
			okflex5.".$g4['yc4_cart_table']." a,
			okflex5.".$g4['yc4_order_table']." b
		where
			a.ct_id = '".$ct_id."'
			and a.on_uid = b.on_uid
	");
	$it_id = $od_time['it_id'];
	$ct_qty = $od_time['ct_qty'];
	$od_id = $od_time['od_id'];
	$on_uid = $od_time['on_uid'];
	$od_time = $od_time['od_time'];


	// 판매량용 dbconnect(계정이다름)
//	$sales_db = mysql_connect($sales_db_info['host'],$sales_db_info['user'],$sales_db_info['pass']);
//	mysql_select_db($sales_db_info['db']);

	if($status_fg == 2){


		$sales_tb_chk = sql_fetch("select count(*) as cnt from sales.op_sales_item where ct_id = '".$ct_id."' and isupdate is null");
		if($sales_tb_chk['cnt'] > 0){
			$up_sql_set = "total_sold = total_sold - 1";
			$od_time_sql = date('Y-m-d');
		}else{
			for($i=1; $i<=$ct_qty; $i++){
				sql_query("
					insert into
						sales.op_sales_item
					(
						ct_id,it_id,dt,rate,status
					) values(
						'".$ct_id."','".$it_id."','".$od_time."','".$default['de_conv_pay']."','".$status_fg."'
					)
				");

			}
			if($status_fg == 1){

				if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){

					if($nonstop_data['it_id'] == $it_id){

						sql_query("update okflex5.yc4_nontop_sale set sell_qty=sell_qty+$ct_qty where it_id='$it_id' and status='2'");

						if($nonstop_data['ev_qty'] - ($nonstop_data['sell_qty'] + $ct_qty) < 1 ){

							sql_query("update okflex5.yc4_item set status=3 where it_id='$it_id' and status='2'");

							if($nonstop_data['it_order_onetime_limit_fg'] == 'N'){
								sql_query("update okflex5.yc4_item set it_order_onetime_limit_cnt=0 where it_id='$it_id'");
							}

							$next_info = sql_fetch("select min(seq) as seq from yc4_nontop_sale where status=1");

							if($next_info['seq']){

								sql_query("update okflex5.yc4_nontop_sale set status='2' where seq='".$next_info['seq']."'");

								$nonstop_data2 = sql_fetch("select * from okflex5.yc4_nontop_sale where seq='".$next_info['seq']."'");

								if($nonstop_data2['it_order_onetime_limit_fg'] == 'N'){
									sql_query("update okflex5.yc4_item set it_order_onetime_limit_cnt=10 where it_id='$it_id'");
								}

							}

						}

					}

				}

				mysql_query("update okflex5.yc4_event_item_stock set sell_cnt = sell_cnt + (".$ct_qty.") where it_id = '".$it_id."' and isupdate is null");

				// 별도재고관리 상품 품절 처리 2014-12-08
				$ev_qty_chk = sql_fetch("select count(*) as cnt from yc4_event_item_stock where it_id = '".$it_id."' and isupdate is null and use_yn = 'y'");


				if($ev_qty_chk['cnt'] > 0){

					$ev_qty_chk2 = sql_fetch("select qty-sell_cnt as result_qty,ch_amount from yc4_event_item_stock where it_id = '".$it_id."' and isupdate is null and use_yn = 'y'");

					if($ev_qty_chk2['result_qty'] <= 0){

//						sql_query("update yc4_event_item_stock set isupdate = 'y', update_dt = '".date('Y-m-d H:i:s')."' where it_id = '".$it_id."' and isupdate is null and use_yn = 'y'");

						if($ev_qty_chk2['ch_amount']){
							$amount_qry = ", it_amount = '".$ev_qty_chk2['ch_amount']."'";
						}
//						sql_query("update ".$g4['yc4_item_table']." set it_stock_qty = '0' ".$amount_qry." where it_id = '".$it_id."'");

						/*# 품절 히스토리 남김 #
						sql_query("update yc4_soldout_history set current_fg='N' where it_id='".$it_id."'");

						sql_query("
							insert into
								yc4_soldout_history
							(
								it_id,flag,mb_id,time,ip,current_fg
							)
							values(
								'".$it_id."','o','auto(".$member['mb_id'].")','".date('Y-m-d H:i:s')."','".$_SERVER['REMOTE_ADDR']."','Y'
							)
						");*/
					}
				}


				mysql_query("update okflex5.yc4_bl_event_item_stock set sell_cnt = sell_cnt + (".$ct_qty.") where it_id = '".$it_id."' and isupdate is null");

				# 핫딜존 처리 시작 2015-01-27 홍민기 #
				if($_HOTDEAL_FG){
					$hotdeal_chk = sql_fetch("
						select
							uid,qty,sell_qty,sort
						from
							okflex5.yc4_hotdeal_item
						where
							it_id = '".$it_id."'
							and
							flag = 'Y'
							and sort > 0
							and sort < 5
					");
					if($hotdeal_chk){
						# 핫딜존 판매수량 카운트 증가 #
						sql_query("
							update
								okflex5.yc4_hotdeal_item
							set
								sell_qty = sell_qty + '".$ct_qty."'
							where
								uid = '".$hotdeal_chk['uid']."'
						");
						# 핫딜존 상품 품절시 처리 #
						if($hotdeal_chk['qty'] - ($hotdeal_chk['sell_qty'] + $ct_qty) < 1 ){
							// 핫딜존 종료 처리
							sql_query("
								update
									okflex5.yc4_hotdeal_item
								set
									flag = 'E',
									en_dt = '".$g4['time_ymdhis']."'
								where
									uid = '".$hotdeal_chk['uid']."'
							");

							// 종료된 핫딜존 상품 뒤의 상품 순서를 앞으로 한칸씩 이동
							sql_query("
								update
									okflex5.yc4_hotdeal_item
								set
									sort = sort - 1
								where
									flag = 'Y'
									and
									sort > '".$hotdeal_chk['sort']."'
							");

							# 메인 페이지 캐시파일 재생성 #
							exec("/usr/bin/php /ssd/html/mall5/cron/main_data_cache.php");

						}
					}
				}
			}else{

				if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){

					if($nonstop_data['it_id'] == $it_id){

						sql_query("update okflex5.yc4_nontop_sale set sell_qty=sell_qty-$ct_qty where it_id='$it_id' and status='2'");
					}

				}

				mysql_query("update okflex5.yc4_event_item_stock set sell_cnt = sell_cnt - (".$ct_qty.") where it_id = '".$it_id."' and isupdate is null");
				/*
				mysql_query("update okflex5.yc4_bl_event_item_stock set sell_cnt = sell_cnt - (".$ct_qty.") where it_id = '".$it_id."' and isupdate is null");
				*/
				sql_query("
					insert into
						cancel_order
					(
						od_id,on_uid,work,create_date
					) values(

						'".$od_id."','".$on_uid."','N','".date('Y-m-d H:i:s')."'
					)
				");


			}
		}
	}else{
		$up_sql_set = "total_sold = total_sold + 1";
		$od_time_sql = $od_time;
	}
	/*
	if($status_fg == 5){
		$up_sql_set = "total_cancel = total_cancel + 1";
	}elseif($bf_status_fg == 1 && in_array($status_fg, array(2,3,4) ) ) {
		$up_sql_set = "total_sold = total_sold + 1";
	}elseif( in_array( $bf_status_fg,array(2,3,4) ) ) {
		if($status_fg == 1){
			$up_sql_set = "total_sold = total_sold - 1";
		}
	}elseif($bf_status_fg == 5 && $status_fg != 5){
		$up_sql_set = "total_cancel = total_cancel - 1";
	}
	*/

	# sales 테이블 업데이트
	mysql_query("
		update
			sales.op_sales_item
		set
			status = '".$status_fg."'
		where
			ct_id = '".$ct_id."'
	");

	if($up_sql_set){
		mysql_query("
			update
				sales.op_sales_report
			set
				".$up_sql_set."
			where
				it_id = '".$it_id."'
				and
				dt = '".$od_time_sql."'
		");
	}


}
?>
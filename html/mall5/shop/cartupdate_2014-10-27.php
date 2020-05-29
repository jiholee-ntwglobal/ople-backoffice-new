<?php
//$test = true;
include_once("./_common.php");

// 브라우저에서 쿠키를 허용하지 않은 경우라고 볼 수 있음.
$tmp_on_uid = get_session('ss_on_uid');
if (!$tmp_on_uid)
{
    alert("더 이상 작업을 진행할 수 없습니다.\\n\\n브라우저의 쿠키 허용을 사용하지 않음으로 설정한것 같습니다.\\n\\n브라우저의 인터넷 옵션에서 쿠키 허용을 사용으로 설정해 주십시오.\\n\\n그래도 진행이 되지 않는다면 쇼핑몰 운영자에게 문의 바랍니다.");
}

// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "sjsjin.shop_guard.php";


// 레벨(권한)이 상품구입 권한보다 작다면 상품을 구입할 수 없음.
if ($member[mb_level] < $default[de_level_sell])
{
    alert("상품을 구입할 수 있는 권한이 없습니다.");
}


if ($w == "d") // 삭제이면
{
    $sql = " delete from $g4[yc4_cart_table]
              where ct_id = '$ct_id'
                and on_uid = '$tmp_on_uid' ";
    sql_query($sql);

	// 오버스탁 프로모션 장바구니 삭제
	sql_query("delete from yc4_over_stock_item_cart where on_uid = '$tmp_on_uid' and it_id = '$it_id'");
}
else if ($w == "alldelete") // 모두 삭제이면
{
    $sql = " delete from $g4[yc4_cart_table]
              where on_uid = '$tmp_on_uid' ";
    sql_query($sql);

	// 오버스탁 프로모션 장바구니 삭제
	sql_query("delete from yc4_over_stock_item_cart where on_uid = '$tmp_on_uid'");
}
else if ($w == "allupdate") // 수량 변경이면 : 모두 수정이면
{
    $fldcnt = count($_POST[ct_id]);

    // 수량 변경, 재고등을 검사
    $error = "";
	for ($i=0; $i<$fldcnt; $i++)
    {
		if($_POST['it_name'][$i]){
			$_POST['it_name'][$i] = get_item_name($_POST['it_name'][$i]);
		}

		// 오버스탁 이벤트 상품 처리 2014-10-20 홍민기
		$over_stock_chk_qry = sql_fetch($a="
			select
				ov_qty,ev_qty
			from
				yc4_over_stock_item
			where
				use_yn = 'y'
				and it_id = '".$_POST['it_id'][$i]."'
		");



		// 오버스탁 이벤트 상품에 해당되며 주문 수량이 오버스탁 이벤트 해당 요구 주문사항보다 클경우에만 로직을 탐
		if($over_stock_chk_qry){
			$over_stock_cart_chk = sql_fetch("
				select
					ct_qty,ev_qty
				from
					yc4_over_stock_item_cart
				where
					on_uid = '".$tmp_on_uid."'
					and
					it_id = '".$_POST['it_id'][$i]."'
			");

			if($test){
				print_r2($over_stock_chk_qry);
				print_r2($over_stock_cart_chk);

			}

			if($over_stock_cart_chk){ // 이미 오버스탁 이벤트 장바구니 테이블에 존재한다면

				$old_qty = $over_stock_cart_chk['ct_qty'] + $over_stock_cart_chk['ev_qty'];
				$new_qty = $_POST['ct_qty'][$i] - $old_qty; // 변동된 수량

				if($new_qty != 0) { // 수량이 달라졌을 경우만 로직을 탄다
					//$real_qty =  $new_qty + $old_qty; // 실제 주문수량
					//$real_qty =  $_POST['ct_qty'][$i] + ($over_stock_cart_chk['ct_qty'] + $new_qty); // 실제 주문수량

					/*
					if($_POST['ct_qty'][$i] < $over_stock_chk_qry['ov_qty']){
						$real_qty = $_POST['ct_qty'][$i];
					}else{

					}
					*/
					/*
					$real_qty = $_POST['ct_qty'][$i];

					$ev_qty = floor($real_qty / $over_stock_chk_qry['ov_qty']) * $over_stock_chk_qry['ev_qty'];
					*/
					$ev_qty = floor($_POST['ct_qty'][$i]/$over_stock_chk_qry['ov_qty']);
					$real_qty = $_POST['ct_qty'][$i] - $ev_qty;
					if($test){
						echo $_POST['ct_qty'][$i] . " -- ct_qty <br/>";;
						echo $real_qty . " -- real_qty <br/>";
						echo $ev_qty . " -- ev_qty <br/>";
						echo $over_stock_cart_chk['ct_qty'] . " -- over_stock_cart_chk_ct_qty <br/>";
						echo $old_qty . " -- old_qty <br/>";
						echo $new_qty . " -- new_qty <br/>";
						echo "<br/>";
					}

					$ev_qry = "
						update
							yc4_over_stock_item_cart
						set
							ct_qty = ".(int)$real_qty.",
							ev_qty = ".(int)$ev_qty.",
							ev_time = now()
						where
							on_uid = '".$tmp_on_uid."'
							and
							it_id = '".$_POST['it_id'][$i]."'
					";
				}

			}else{ // 신규 수량 변경일 경우
				$real_qty = $_POST['ct_qty'][$i];
				$ev_qty = floor($real_qty / $over_stock_chk_qry['ov_qty']) * $over_stock_chk_qry['ev_qty'];
				$ev_qry = "
					insert into
						yc4_over_stock_item_cart
					(
						on_uid,it_id,ct_qty,ev_qty,ev_time,mb_id
					)values(
						'".$tmp_on_uid."','".$_POST['it_id'][$i]."','".$real_qty."','".$ev_qty."',now(),'".$member['mb_id']."'
					)
				";
			}




			if($ev_qry){

				$_POST['ct_qty'][$i] = $real_qty + $ev_qty; // 구매 수량 = 실구매수량 + 사은품 수량

				if($test){
					echo $ev_qry;
					echo $_POST['ct_qty'][$i];

				}


				// 재고 구함
				$stock_qty = get_it_stock_qty($_POST[it_id][$i]);

				// 변경된 수량이 재고수량보다 크면 오류
				if ($_POST[ct_qty][$i] > $stock_qty){
					//$error .= "{$_POST[it_name][$i]} 의 재고수량이 부족합니다. 현재 재고수량 : $stock_qty 개\\n\\n";
					$error .= "{$_POST[it_name][$i]} 의 재고수량이 부족합니다.\\n\\n";
				}

				// 김선용 201208 :
				if($_POST['it_order_onetime_limit_cnt'][$i] && $_POST['it_order_onetime_limit_cnt'][$i] < $_POST['ct_qty'][$i])
					$error .= addslashes($_POST[it_name][$i])." 의 1회 최대구매수량은 {$_POST['it_order_onetime_limit_cnt'][$i]} 개 입니다.\\n\\n";

				if($test){
					echo $ev_sql . '<br/><br/>';
				}else{
					if(!sql_query($ev_qry)){
						alert('수량 변경중 오류 발생! 관리자에게 문의해 주세요.');
						exit;
					}
				}
			}
		}


        // 재고 구함
        $stock_qty = get_it_stock_qty($_POST[it_id][$i]);

        // 변경된 수량이 재고수량보다 크면 오류
        if ($_POST[ct_qty][$i] > $stock_qty){
            //$error .= "{$_POST[it_name][$i]} 의 재고수량이 부족합니다. 현재 재고수량 : $stock_qty 개\\n\\n";
			$error .= "{$_POST[it_name][$i]} 의 재고수량이 부족합니다.\\n\\n";
		}

		// 김선용 201208 :
		if($_POST['it_order_onetime_limit_cnt'][$i] && $_POST['it_order_onetime_limit_cnt'][$i] < $_POST['ct_qty'][$i])
			$error .= addslashes($_POST[it_name][$i])." 의 1회 최대구매수량은 {$_POST['it_order_onetime_limit_cnt'][$i]} 개 입니다.\\n\\n";
    }

    // 오류가 있다면 오류메세지 출력
    if ($error != "") { alert($error); }

	for ($i=0; $i<$fldcnt; $i++)
    {
        $sql = " update $g4[yc4_cart_table]
                    set ct_qty = '{$_POST[ct_qty][$i]}'
                  where ct_id  = '{$_POST[ct_id][$i]}'
                    and on_uid = '$tmp_on_uid' ";
		if($test){
			echo "<br/><br/> --- ";
			echo $sql;
		}
        sql_query($sql);
    }
	if($test){
		exit;
	}
}
else if ($w == "multi") // 온라인견적(등)에서 여러개의 상품이 한꺼번에 들어옴.
{
    // 보관함에서 금액이 제대로 반영되지 않던 오류를 수정
    $fldcnt = count($_POST[it_name]);

    // 재고등을 검사
    $error = "";
	for ($i=0; $i<$fldcnt; $i++)
    {
        if ($_POST[it_id][$i] == "" || $_POST[ct_qty][$i] <= 0) { continue; }

        // 비회원가격과 회원가격이 다르다면
        if (!$is_member && $default[de_different_msg])
        {
            $sql = " select it_amount, it_amount2 from $g4[yc4_item_table] where it_id = '{$_POST[it_id][$i]}' ";
            $row = sql_fetch($sql);
            if ($row[it_amount2] && $row[it_amount] != $row[it_amount2]) {
                $error .= "\"{$_POST[it_name][$i]}\" 의 비회원가격과 회원가격이 다릅니다. 로그인 후 구입하여 주십시오.\\n\\n";
            }
        }

        // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
        $sql = " select SUM(ct_qty) as cnt from $g4[yc4_cart_table] where it_id = '{$_POST[it_id][$i]}' and on_uid = '$tmp_on_uid' ";
        $row = sql_fetch($sql);
        $sum_qty = $row[cnt];



        if($sum_qty > 0){ // 이미 장바구니에 존재하는 상품이라면 재고만 증가
			// 재고 구함
			$it_stock_qty = get_it_stock_qty($_POST[it_id][$i]);
			if ($_POST[ct_qty][$i] + $sum_qty > $it_stock_qty) {
				//$error .= "{$_POST[it_name][$i]} 의 재고수량이 부족합니다. 현재 재고수량 : $it_stock_qty\\n\\n";
				$error .= "{$_POST[it_name][$i]} 의 재고수량이 부족합니다.\\n\\n";
			}

			// 김선용 201208 :
			if($_POST['it_order_onetime_limit_cnt'][$i] && $_POST['it_order_onetime_limit_cnt'][$i] < $_POST['ct_qty'][$i]){
				$error .= addslashes($_POST[it_name][$i])." 의 1회 최대구매수량은 {$_POST['it_order_onetime_limit_cnt'][$i]} 개 입니다.\\n\\n";
			}
			$sql = "
				update
					$g4[yc4_cart_table]
				set
					ct_qty = ct_qty + ".(int)$sum_qty."
				where
					on_uid = '$tmp_on_uid'
					and
					it_id = '{$_POST[it_id][$i]}'
			";

		}else{
			if ($_POST[it_id][$i] == "" || $_POST[ct_qty][$i] <= 0) continue;

			// 포인트 사용하지 않는다면
			if (!$config[cf_use_point]) $_POST[it_point][$i] = 0;

			// 장바구니에 Insert
			$sql = " insert $g4[yc4_cart_table]
						set on_uid       = '$tmp_on_uid',
							it_id        = '{$_POST[it_id][$i]}',
							ct_status    = '쇼핑',
							ct_amount    = '{$_POST[it_amount][$i]}',
							ct_point     = '{$_POST[it_point][$i]}',
							ct_point_use = '0',
							ct_stock_use = '0',
							ct_qty       = '{$_POST[ct_qty][$i]}',
							ct_time      = '{$g4['time_ymdhis']}',
							ct_ip        = '".getenv('REMOTE_ADDR')."',
							ct_mb_id = '{$member['mb_id']}' ";
		}
		sql_query($sql);
    }

    // 오류가 있다면 오류메세지 출력
    if ($error != "") { alert($error); }

	/*
	for ($i=0; $i<$fldcnt; $i++)
    {
        if ($_POST[it_id][$i] == "" || $_POST[ct_qty][$i] <= 0) continue;

        // 포인트 사용하지 않는다면
        if (!$config[cf_use_point]) $_POST[it_point][$i] = 0;

        // 장바구니에 Insert
        $sql = " insert $g4[yc4_cart_table]
                    set on_uid       = '$tmp_on_uid',
                        it_id        = '{$_POST[it_id][$i]}',
                        ct_status    = '쇼핑',
                        ct_amount    = '{$_POST[it_amount][$i]}',
                        ct_point     = '{$_POST[it_point][$i]}',
                        ct_point_use = '0',
                        ct_stock_use = '0',
                        ct_qty       = '{$_POST[ct_qty][$i]}',
                        ct_time      = '{$g4['time_ymdhis']}',
                        ct_ip        = '".getenv('REMOTE_ADDR')."',
						ct_mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }
	*/
}
else // 장바구니에 담기
{
	$it_id = $_POST['it_id'] = (int)$_POST['it_id'];
	$ct_qty = $_POST['ct_qty'] = (int)$_POST['ct_qty'];

    if (!$it_id)
        alert("장바구니에 담을 상품을 선택하여 주십시오.");

    if (!$ct_qty || $ct_qty < 1)
        alert("수량은 1 이상 입력해 주십시오.");




    // 비회원가격과 회원가격이 다르다면
    if (!$is_member && $default[de_different_msg])
    {
        $sql = " select it_amount, it_amount2 from $g4[yc4_item_table] where it_id = '$_POST[it_id]' ";
        $row = sql_fetch($sql);
        if ($row[it_amount2] && $row[it_amount] != $row[it_amount2]) {
            //alert("비회원가격과 회원가격이 다른 상품입니다. 로그인 후 구입하여 주십시오.", "$g4[bbs_path]/login.php?url=".urlencode("$g4[shop_path]/item.php?it_id=$_POST[it_id]"));
            echo "<script>alert('비회원가격과 회원가격이 다릅니다. 로그인 후 구입하여 주십시오.');</script>";
        }
    }

    //--------------------------------------------------------
    //  재고 검사
    //--------------------------------------------------------
    // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
    $sql = " select SUM(ct_qty) as cnt from $g4[yc4_cart_table]
              where it_id = '$_POST[it_id]'
                and on_uid = '$tmp_on_uid' ";
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


//$test = true;
	# 오버스탁 이벤트 처리 2014-10-21 홍민기 #
	$over_stock_chk_qry = sql_fetch("
		select
			ov_qty,ev_qty
		from
			yc4_over_stock_item
		where
			use_yn = 'y'
			and it_id = '".$_POST['it_id']."'
	");

	// 오버스탁 이벤트 상품에 해당되며 주문 수량이 오버스탁 이벤트 해당 요구 주문사항보다 클경우에만 로직을 탐
	if($over_stock_chk_qry){
		$over_stock_cart_chk = sql_fetch("
			select
				ct_qty,ev_qty
			from
				yc4_over_stock_item_cart
			where
				on_uid = '".$tmp_on_uid."'
				and
				it_id = '".$_POST['it_id']."'
		");
		if($test){
			print_r2($over_stock_cart_chk);
		}
		if($over_stock_cart_chk){ // 이미 오버스탁 이벤트 장바구니 테이블에 존재한다면
			$old_qty = $over_stock_cart_chk['ct_qty'] + $over_stock_cart_chk['ev_qty'];
			$new_qty = $_POST['ct_qty']; // 변동된 수량


			if($new_qty != 0) { // 수량이 달라졌을 경우만 로직을 탄다
				//$real_qty =  $new_qty + $old_qty; // 실제 주문수량
				//$real_qty =  $old_qty - $new_qty ; // 실제 주문수량
				$real_qty =  $new_qty + $over_stock_cart_chk['ct_qty']; // 실제 주문수량
				$ev_qty = floor( ($real_qty ) / $over_stock_chk_qry['ov_qty']) * $over_stock_chk_qry['ev_qty'];
				if($test){
					echo floor( ($real_qty ) / $over_stock_chk_qry['ov_qty']) . "*" . $over_stock_chk_qry['ev_qty'] . "<br/>";
				}

				if($test){
					echo $ct_qty . " -- ct_qty <br/>";;
					echo $real_qty . " -- real_qty <br/>";
					echo $ev_qty . " -- ev_qty <br/>";
					echo $over_stock_cart_chk['ct_qty'] . " -- over_stock_cart_chk_ct_qty <br/>";
					echo $old_qty . " -- old_qty <br/>";
					echo $new_qty . " -- new_qty <br/>";
					echo "<br/>";
				}

				$ev_qry = "
					update
						yc4_over_stock_item_cart
					set
						ct_qty = ".(int)$real_qty.",
						ev_qty = ".(int)$ev_qty.",
						ev_time = now()
					where
						on_uid = '".$tmp_on_uid."'
						and
						it_id = '".$_POST['it_id']."'
				";
			}

		}else{ // 신규 수량 변경일 경우
			$real_qty = $ct_qty;
			$ev_qty = floor($real_qty / $over_stock_chk_qry['ov_qty']) * $over_stock_chk_qry['ev_qty'];
			$ev_qry = "
				insert into
					yc4_over_stock_item_cart
				(
					on_uid,it_id,ct_qty,ev_qty,ev_time,mb_id
				)values(
					'".$tmp_on_uid."','".$_POST['it_id']."','".$real_qty."','".$ev_qty."',now(),'".$member['mb_id']."'
				)
			";
		}

		if($ev_qry){
			$ct_qty = $real_qty + $ev_qty; // 구매 수량 = 실구매수량 + 사은품 수량
		}

		if($test){
			echo $ev_qry .'<br/>';
			echo $ct_qty . ' --- ct_qty<br/>';
			echo $_POST['ct_qty'][$i];

		}
	}

	// 김선용 201208 :
	if($_POST['it_order_onetime_limit_cnt'] && $_POST['it_order_onetime_limit_cnt'] < $ct_qty){
		alert("이 상품의 1회 최대구매수량은 {$_POST['it_order_onetime_limit_cnt']} 개 입니다.");
	}

	if ($ct_qty + $sum_qty > $it_stock_qty){
        //alert("$it_name 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty) . " 개");
		alert("$it_name 의 재고수량이 부족합니다.\\n\\n");
    }
	if($ev_qry && !$test){
		if(!sql_query($ev_qry)){
			alert('처리중 오류 발생! 관리자에게 문의해 주세요.');
			exit;
		}
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
		and ct_status='쇼핑' ";
	$chk2 = sql_fetch($chk2_sql);
	if($chk2['ct_id'])
	{
		// 김선용 201208 : 최대구매수량 한번더 확인
		if($_POST['it_order_onetime_limit_cnt'] && $_POST['it_order_onetime_limit_cnt'] < ($chk2['ct_qty'] + $ct_qty))
			alert("이 상품의 1회 최대구매수량은 {$_POST['it_order_onetime_limit_cnt']} 개 입니다.");

		$sql = "update {$g4['yc4_cart_table']}
			set ct_qty = ".($over_stock_cart_chk ? $ct_qty : 'ct_qty+'.$ct_qty)."
			where on_uid='$tmp_on_uid' and it_id='{$_POST['it_id']}'
			and it_opt1 = '{$_POST['it_opt1']}'
			and it_opt2 = '{$_POST['it_opt2']}'
			and it_opt3 = '{$_POST['it_opt3']}'
			and it_opt4 = '{$_POST['it_opt4']}'
			and it_opt5 = '{$_POST['it_opt5']}'
			and it_opt6 = '{$_POST['it_opt6']}'
			and ct_status='쇼핑' ";
		if($test){
			echo "<br/><br/> --- ";
			echo $sql; exit;
		}
		sql_query($sql);
	}
	else
	{
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
						ct_qty       = '$ct_qty',
						ct_time      = '{$g4['time_ymdhis']}',
						ct_ip        = '".getenv('REMOTE_ADDR')."',
						ct_mb_id = '{$member['mb_id']}' ";
		if($test){
			echo "<br/><br/> --- ";
			echo $sql; exit;
		}
		sql_query($sql);

	}
}

// 바로 구매일 경우
if ($sw_direct)
{
    if ($member[mb_id])
    {
    	goto_url("./orderform.php");
    }
    else
    {
    	goto_url("$g4[bbs_path]/login.php?url=".urlencode("$g4[shop_path]/orderform.php"));
    }
}
else
{
    goto_url("./cart.php");
}
?>

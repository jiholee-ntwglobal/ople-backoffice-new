<?
include_once("./_common.php");



# 사인품을 주문서 장바구니에 0원으로 추가 #
if($_POST['mode'] == 'insert'){


	$on_uid_qry = sql_fetch("
		select on_uid from yc4_order where od_id = '".$_POST['od_id']."'
	");

	$on_uid = $on_uid_qry['on_uid'];

	# 이미 사은품이 지급된 주문인지 체크 #
	$chk_qry = sql_fetch("
		select
			count(*) as cnt
		from
			yc4_free_gift_event_order_history
		where
			od_id = '".$_POST['od_id']."'
	");
	$chk = $chk_qry['cnt'];


	if($chk > 0){
		alert('이미 사은품을 신청하셨습니다.',$g4['shop_path'].'/orderinquiryview.php?od_id='.$_POST['od_id'].'&on_uid='.$on_uid);
		exit;
	}


	# 현재 주문 상태 로드 #
	$cart_status = sql_fetch("
		select
			ct_status,count(*) as cnt
		from
			".$g4['yc4_cart_table']."
		where
			on_uid = '".$on_uid."'
		group by on_uid
		order by cnt desc
	");
	$cart_status = $cart_status['ct_status'];


	# 장바구니에 추가 #
	$ct_history =
		"사은품 지급||".$_SERVER['REMOTE_ADDR']."\n";

	$insert_qry = "
		insert into
			yc4_cart
		set
			on_uid = '".$on_uid."',
			it_id = '###_it_id_###',
			it_opt1 = '',
			it_opt2 = '',
			it_opt3 = '',
			it_opt4 = '',
			it_opt5 = '',
			it_opt6 = '',
			ct_status = '".$cart_status."',
			ct_history = '".$ct_history."',
			ct_amount = 0,
			ct_point = 0,
			ct_point_use = 0,
			ct_stock_use = 0,
			ct_qty = 1,
			ct_time = now(),
			ct_ip = '".$_SERVER['REMOTE_ADDR']."',
			ct_send_cost = '',
			ct_mb_id = '".$member['mb_id']."',
			ct_ship_os_pid = '',
			ct_ship_ct_qty = '',
			ct_ship_stock_use = ''
	";

	$history_insert_qry = "
		insert into
			yc4_free_gift_event_order_history
		set
			od_id = '".$_POST['od_id']."',
			mb_id = '".$member['mb_id']."',
			bid = '###_bid_###',
			it_id = '###_it_id_###',
			create_dt = now()
	";

	if($_POST['brand']) {
		sql_query(str_replace('###_it_id_###',$_POST['brand'],$insert_qry));
		$his_sql = str_replace('###_bid_###',$_POST['brand_bid'],$history_insert_qry);
		$his_sql = str_replace('###_it_id_###',$_POST['brand'],$his_sql);
		sql_query($his_sql);
	}
	if($_POST['category']) {
		sql_query(str_replace('###_it_id_###',$_POST['category'],$insert_qry));
		$his_sql = str_replace('###_bid_###',$_POST['category_bid'],$history_insert_qry);
		$his_sql = str_replace('###_it_id_###',$_POST['category'],$his_sql);
		sql_query($his_sql);
	}
	if($_POST['amount_event']) {
		sql_query(str_replace('###_it_id_###',$_POST['amount_event'],$insert_qry));
		$his_sql = str_replace('###_bid_###',$_POST['total_amount_bid'],$history_insert_qry);
		$his_sql = str_replace('###_it_id_###',$_POST['amount_event'],$his_sql);
		sql_query($his_sql);
	}


	# 히스토리 등록 #



	alert('사은품 신청이 완료되었습니다.',$g4['shop_path'].'/orderinquiryview.php?od_id='.$_POST['od_id'].'&on_uid='.$on_uid);

	exit;


}



$g4[title] = "이벤트 사은품 신청";



# 이벤트 대상 여부 체크 #
$bid_arr = explode('|',$_POST['bid_merge']);

$event_chk_qry = sql_query("
	select
		count(*) as cnt
	from
		yc4_free_gift_event_order_history
	where
		od_id = '".$_POST['od_id']."'

");

$event_chk = $event_chk_qry['cnt'];

if($event_chk > 0){
	alert('이벤트 대상이 아닙니다.');
}


# 구매 상품 정보 로드 #
$cart_qry = sql_query("
	select
		a.it_id,
		a.ct_amount,
		a.ct_qty,
		(a.ct_amount * a.ct_qty) as ct_item_total_amount,
		b.od_id,
		c.it_name,
		c.it_maker,
		c.ca_id,
		c.ca_id2,
		c.ca_id3,
		c.ca_id4,
		c.ca_id5
	from
		yc4_cart a
		left join
		yc4_order b on a.on_uid = b.on_uid
		left join
		yc4_item c on a.it_id = c.it_id
	where
		b.od_id = '".$_POST['od_id']."'
");


while($cart = sql_fetch_array($cart_qry)){
	if($cart['it_name']){
		$cart['it_name'] = get_item_name($cart['it_name']);
	}
	$brand[$cart['it_maker']] += $cart['ct_item_total_amount']; // 브랜드 배열로 저장 it_maker => 구매금액
	if($cart['ca_id']) $category[$cart['ca_id']] += $cart['ct_item_total_amount'];
	if($cart['ca_id2']) $category[$cart['ca_id2']] += $cart['ct_item_total_amount'];
	if($cart['ca_id3']) $category[$cart['ca_id3']] += $cart['ct_item_total_amount'];
	if($cart['ca_id4']) $category[$cart['ca_id4']] += $cart['ct_item_total_amount'];
	if($cart['ca_id5']) $category[$cart['ca_id5']] += $cart['ct_item_total_amount'];

	$total_amount += $cart['ct_item_total_amount']; // 총 구매금액
}

if(is_array($brand)){
	foreach($brand as $key => $amount){

		$key = mysql_real_escape_string($key);
		$brand_event_qry = sql_query($a="
			select
				a.bid,
				a.name,
				a.comment,
				a.it_maker,
				b.od_amount,
				b.show_amount,
				c.it_id,
				c.it_name,
				c.it_cust_amount,
				c.it_amount,
				c.it_stock_qty
			from
				yc4_free_gift_event a
				left join
				yc4_free_gift_event_item b on a.bid = b.bid
				left join
				yc4_item c on b.it_id = c.it_id
			where
				a.st_dt <= '".date('Ymd')."'
				and
				a.en_dt >= '".date('Ymd')."'
				and
				a.it_maker = '".$key."'
				and
				b.od_amount <= '".$amount."'
				and
				b.`use` = 'Y'
			order by a.bid,a.event_type,b.od_amount
		");
		while($row = sql_fetch_array($brand_event_qry)){
			$gift_event[$row['bid']][$row['od_amount']][] = $row;
		}
	}
}


# 카테고리 이벤트 출력 처리 시작 #
if(is_array($category)){
	foreach($category as $key => $amount){
		$category_event_qry = sql_query("
			select
				a.bid,
				a.name,
				a.comment,
				a.ca_id,
				b.od_amount,
				b.show_amount,
				c.it_id,
				c.it_name,
				c.it_cust_amount,
				c.it_amount,
				c.it_stock_qty,
				d.ca_name
			from
				yc4_free_gift_event a
				left join
				yc4_free_gift_event_item b on a.bid = b.bid
				left join
				yc4_item c on b.it_id = c.it_id
				left join
				yc4_category d on a.ca_id = d.ca_id
			where
				a.st_dt <= '".date('Ymd')."'
				and
				a.en_dt >= '".date('Ymd')."'
				and
				a.ca_id = '".$key."'
				and
				b.od_amount <= '".$amount."'
				and
				b.use = 'Y'
			order by a.bid,a.event_type,b.od_amount
		");
		while($row = sql_fetch_array($category_event_qry)){
			if($row['it_name']){
				$row['it_name'] = get_item_name($row['it_name']);
				$gift_event[$row['bid']][$row['od_amount']][] = $row;
			}
		}
	}
}

# 구매금액별 이벤트 출력 처리 시작 #
if($total_amount>0){
	$total_amount_event_qry = sql_query("
		select
			a.bid,
			a.name,
			a.comment,
			a.it_maker,
			b.od_amount,
			b.show_amount,
			c.it_id,
			c.it_name,
			c.it_cust_amount,
			c.it_amount,
			c.it_stock_qty
		from
			yc4_free_gift_event a
			left join
			yc4_free_gift_event_item b on a.bid = b.bid
			left join
			yc4_item c on b.it_id = c.it_id
		where
			a.st_dt <= '".date('Ymd')."'
			and
			a.en_dt >= '".date('Ymd')."'
			and
			b.od_amount <= '".$total_amount."'
			and
			a.event_type = 'A'
		order by a.bid,a.event_type,b.od_amount


	");
	unset($total_amount_event);
	while($row = sql_fetch_array($total_amount_event_qry)){
		if($row['it_name']){
			$row['it_name'] = get_item_name($row['it_name']);
			$gift_event[$row['bid']][$row['od_amount']][] = $row;
		}

	}
}


// $gift_event[bid][금액][num] = data
if(is_array($gift_event)){
	foreach($gift_event as $bid => $val){
		# 이벤트 정보 다시 로드 #
		$ev_info = sql_fetch("select name,event_type,it_maker,ca_id,st_dt,en_dt,comment,priod_view from yc4_free_gift_event where bid = '".$bid."'");
		switch($ev_info['priod_view']){
			case 'Y':
				$priod =
					substr($ev_info['st_dt'],0,4).'-'.substr($ev_info['st_dt'],4,2).'-'.substr($ev_info['st_dt'],6,2)
					.'~'.
					substr($ev_info['en_dt'],0,4).'-'.substr($ev_info['en_dt'],4,2).'-'.substr($ev_info['en_dt'],6,2);
				break;
			case 'N':
				$priod = null;
				break;
			case 'C':
				$priod = '기간별도공지';
				break;
		}
		switch($ev_info['event_type']){
			case 'A' :
				$radio_name = 'amount_event';
				$event_type_input = "<input type='hidden' name='total_amount_bid' value='".$bid."'/>";
				break;
			case 'B' :
				$radio_name = 'brand';
				$event_type_input = "<input type='hidden' name='brand_bid' value='".$bid."'/>";
				break;
			case 'C' :
				$radio_name = 'category';
				$event_type_input = "<input type='hidden' name='category_bid' value='".$bid."'/>";
				break;
		}
		$event_item_data .= "
			<div class='eventContents'>
				".$event_type_input."
				<div class='eventTitleArea'>
					<p class='eventTitle'><strong>".$ev_info['name']."</strong> <span>".$priod."</span><p>
					<p>사은품 수량 소진 시 대체사은품으로 배송됩니다.</p>
					<p>주문 완료 후 사은품선택을 해주셔야 받으실 수 있습니다.</p>
				</div><!-- .eventTitleArea end -->
		";
		if(is_array($val)){
			foreach($val as $od_amount => $val2){
				$event_item_data .= "
				<div class='gift_listArea'>
					<p class='gift_option'>".number_format($od_amount)."원 이상 구매 시 선택 가능 사은품</p>
				";
				if(is_array($val2)){
					$event_item_data .= "
					<ul>
					";
					foreach($val2 as $row){
						$event_item_data .= "
						<li>
							<p class='product_img'>".get_it_image($row['it_id'].'_s',160,160,$row['it_id'])."</p>
							<p class='product_title'>".$row['it_name'].($row['it_stock_qty']<1 ? "<img src='".$g4['shop_path']."/img/icon_pumjul.gif' />":"")."</p>
							<p class='product_select'><input type='radio' name='".$radio_name."' value='".$row['it_id']."' ".($row['it_stock_qty']<1 ? "disabled":"")." /></p>
						</li>
						";
					}
					$event_item_data .= "
					</ul>
					";
				}
				$event_item_data .= "
				</div><!-- .gift_listArea end -->
				";
			}
		}
		$event_item_data .= "
			</div><!-- .eventContents end -->
		";
	}
}
/*
print_r($brand); // 브랜드 배열 [브랜드] => 구매액
print_r($category); // 카테고리 배열 [카테고리] => 구매액
echo $total_amount; // 총 구매금액
*/
/*
# 브랜드 이벤트 출력 처리 시작 #
if(is_array($brand)){
	foreach($brand as $key => $amount){

		$key = mysql_real_escape_string($key);
		$brand_event_qry = sql_query($a="
			select
				a.bid,
				a.name,
				a.comment,
				a.it_maker,
				b.od_amount,
				b.show_amount,
				c.it_id,
				c.it_name,
				c.it_cust_amount,
				c.it_amount,
				c.it_stock_qty
			from
				yc4_free_gift_event a
				left join
				yc4_free_gift_event_item b on a.bid = b.bid
				left join
				yc4_item c on b.it_id = c.it_id
			where
				a.st_dt <= '".date('Ymd')."'
				and
				a.en_dt >= '".date('Ymd')."'
				and
				a.it_maker = '".$key."'
				and
				b.od_amount <= '".$amount."'
				and
				b.`use` = 'Y'
			order by a.bid,a.event_type,b.od_amount
		");
		unset($brand_event);

		while($result = sql_fetch_array($brand_event_qry)){
			if($result['it_name']){
				$result['it_name'] = get_item_name($result['it_name']);
			}
			$brand_event[] = $result;
		}

		for($i=0; $i<count($brand_event); $i++){
			if($brand_event[$i]['bid'] != $brand_event[$i-1]['bid']){ // 이전 이벤트와 이벤트가 다를 경우 이벤트명 표시
				$brand_event_list .= "
					<input type='hidden' name='brand_bid' value='".$brand_event[$i]['bid']."'>
					<div style='overflow: hidden;'>
						<div class='choice_pro_title'><strong>".$brand_event[$i]['name']."</strong></div>
						<div class='gift_event_comment'>".$brand_event[$i]['comment']."</div>
				";
			}

			if($i == 0 || $brand_event[$i]['od_amount'] != $brand_event[$i-1]['od_amount']){ // 이전 상품과 구매금액 조건이 다를 경우 표시
				$brand_event_list .= "
					<div class='choice_middle_title'>*".number_format($brand_event[$i]['od_amount'])."원 이상 구매 시</div>
				";
			}

			$brand_event_list .= "
						<div class='choice_pro'>
							<div class='choice_img'>".get_it_image($brand_event[$i]['it_id'].'_s',160,160,false,false,false,false)."</div>
							<div class='choice_title'>".$brand_event[$i]['it_name'].($brand_event[$i]['it_stock_qty']<1 ? "<img src='".$g4['shop_path']."/img/icon_pumjul.gif'/>":"")."</div>
							".(($brand_event[$i]['show_amount'] == 'y') ? "
								<div class='choice_price'>".display_amount(get_amount($brand_event[$i]))."</div>
							":"")."
							<div class='choice_box'>사은품 선택 <input type='radio' name='brand' style='vertical-align: sub;' value='".$brand_event[$i]['it_id']."' ".($brand_event[$i]['it_stock_qty']<1 ? "disabled":"")."></div>
						</div>";

			if($brand_event[$i]['od_amount'] != $brand_event[$i+1]['od_amount'] && !$same_amount && !$tmp){ // 홀수로 끝날경우 빈공간 채우기

				$brand_event_list .= "<div class='choice_pro'></div>";
				$same_amount = true;

				$tmp = false;

			}else{
				$tmp = true;
			}

			if($same_amount){
				$same_amount = false;
			}

			if($brand_event[$i]['bid'] != $brand_event[$i+1]['bid']){ // 다음 이벤트와 현재 이벤트가 다를경우 태그 닫기
				$brand_event_list .= "</div>";
			}

		}
	}
}
# 브랜드 이벤트 출력 처리 끝 #

# 카테고리 이벤트 출력 처리 시작 #
if(is_array($category)){
	foreach($category as $key => $amount){
		$category_event_qry = sql_query("
			select
				a.bid,
				a.name,
				a.comment,
				a.ca_id,
				b.od_amount,
				b.show_amount,
				c.it_id,
				c.it_name,
				c.it_cust_amount,
				c.it_amount,
				c.it_stock_qty,
				d.ca_name
			from
				yc4_free_gift_event a
				left join
				yc4_free_gift_event_item b on a.bid = b.bid
				left join
				yc4_item c on b.it_id = c.it_id
				left join
				yc4_category d on a.ca_id = d.ca_id
			where
				a.st_dt <= '".date('Ymd')."'
				and
				a.en_dt >= '".date('Ymd')."'
				and
				a.ca_id = '".$key."'
				and
				b.od_amount <= '".$amount."'
				and
				b.use = 'Y'
			order by a.bid,a.event_type,b.od_amount
		");
		unset($category_event);
		while($result = sql_fetch_array($category_event_qry)){
			if($result['it_name']){
				$result['it_name'] = get_item_name($result['it_name']);
			}
			$category_event[] = $result;
		}

		for($i=0; $i<count($category_event); $i++){
			if($category_event[$i]['bid'] != $category_event[$i-1]['bid']){ // 이전 이벤트와 이벤트가 다를 경우 이벤트명 표시
				$category_event_list .= "
					<input type='hidden' name='category_bid' value='".$category_event[$i]['bid']."'>
					<div style='overflow: hidden;'>
						<div class='choice_pro_title'><b>".$category_event[$i]['name']."</b></div>
						<div class='gift_event_comment'>".$category_event[$i]['comment']."</div>
				";
			}

			if($i == 0 || $category_event[$i]['od_amount'] != $category_event[$i-1]['od_amount']){ // 이전 상품과 구매금액 조건이 다를 경우 표시
				$brand_event_list .= "
					<div class='choice_middle_title'>*".number_format($category_event[$i]['od_amount'])."원 이상 구매 시</div>
				";
			}

			$category_event_list .= "
						<div class='choice_pro'>
							<div class='choice_img'>".get_it_image($category_event[$i]['it_id'].'_s',150,150,false,false,false,false)."</div>
							<div class='choice_title'>".$category_event[$i]['it_name'].($category_event[$i]['it_stock_qty']<1 ? "<img src='".$g4['shop_path']."/img/icon_pumjul.gif'/>":"")."</div>
							".(($category_event[$i]['show_amount'] == 'y') ? "
								<div class='choice_price'>".display_amount(get_amount($category_event[$i]))."</div>
							":"")."
							<div class='choice_box'>사은품 선택 <input type='radio' name='category' style='vertical-align: sub;' value='".$category_event[$i]['it_id']."' ".($category_event[$i]['it_stock_qty'] < 1 ? "disabled":"")."></div>
						</div>";

			if($category_event[$i]['od_amount'] != $category_event[$i+1]['od_amount'] && !$same_amount && !$tmp){ // 홀수로 끝날경우 빈공간 채우기

				$category_event_list .= "<div class='choice_pro'></div>";
				$same_amount = true;

				$tmp = false;

			}else{
				$tmp = true;
			}

			if($same_amount){
				$same_amount = false;
			}

			if($category_event[$i]['bid'] != $category_event[$i+1]['bid']){
				$category_event_list .= "</div>";
			}

		}
	}
}
# 카테고리 이벤트 출력 처리 끝 #


# 구매금액별 이벤트 출력 처리 시작 #
if($total_amount>0){
	$total_amount_event_qry = sql_query("
		select
			a.bid,
			a.name,
			a.comment,
			a.it_maker,
			b.od_amount,
			b.show_amount,
			c.it_id,
			c.it_name,
			c.it_cust_amount,
			c.it_amount,
			c.it_stock_qty
		from
			yc4_free_gift_event a
			left join
			yc4_free_gift_event_item b on a.bid = b.bid
			left join
			yc4_item c on b.it_id = c.it_id
		where
			a.st_dt <= '".date('Ymd')."'
			and
			a.en_dt >= '".date('Ymd')."'
			and
			b.od_amount <= '".$total_amount."'
			and
			a.event_type = 'A'
		order by a.bid,a.event_type,b.od_amount


	");
	unset($total_amount_event);
	while($result = sql_fetch_array($total_amount_event_qry)){
		if($result['it_name']){
			$result['it_name'] = get_item_name($result['it_name']);
		}
		$total_amount_event[] = $result;
	}

	for($i=0; $i<count($total_amount_event); $i++){
		if($total_amount_event[$i]['bid'] != $total_amount_event[$i-1]['bid']){ // 이전 이벤트와 이벤트가 다를 경우 이벤트명 표시
			$total_amount_event_list .= "
				<input type='hidden' name='total_amount_bid' value='".$total_amount_event[$i]['bid']."'>
				<div style='overflow: hidden;'>
					<div class='choice_pro_title'><b>".$total_amount_event[$i]['name']."</b></div>
					<div class='gift_event_comment'>".$total_amount_event[$i]['comment']."</div>
			";
		}

		if($i == 0 || $total_amount_event[$i]['od_amount'] != $total_amount_event[$i-1]['od_amount']){ // 이전 상품과 구매금액 조건이 다를 경우 표시
			$total_amount_event_list .= "
				<div class='choice_middle_title'>*".number_format($total_amount_event[$i]['od_amount'])."원 이상 구매 시</div>
			";
		}

		$total_amount_event_list .= "
			<div class='choice_pro'>
				<div class='choice_img'>".get_it_image($total_amount_event[$i]['it_id'].'_s',150,150,false,false,false,false)."</div>
				<div class='choice_title'>".$total_amount_event[$i]['it_name'].($total_amount_event[$i]['it_stock_qty']<1 ? "<img src='".$g4['shop_path']."/img/icon_pumjul.gif'/>":"")."</div>
				".(($total_amount_event[$i]['show_amount'] == 'y') ? "
					<div class='choice_price'>".display_amount(get_amount($total_amount_event[$i]))."</div>
				":"")."

				<div class='choice_box'>사은품 선택 <input type='radio' name='amount_event' style='vertical-align: sub;' value='".$total_amount_event[$i]['it_id']."' ".($total_amount_event[$i]['it_stock_qty']<1 ? "disabled":"")."></div>
			</div>
		";

		if($total_amount_event[$i]['od_amount'] != $total_amount_event[$i+1]['od_amount'] && !$same_amount && !$tmp){ // 홀수로 끝날경우 빈공간 채우기

				$total_amount_event_list .= "<div class='choice_pro'></div>";
				$same_amount = true;

				$tmp = false;

			}else{
				$tmp = true;
			}

			if($same_amount){
				$same_amount = false;
			}

		if($total_amount_event[$i]['bid'] != $total_amount_event[$i+1]['bid']){
			$total_amount_event_list .= "</div>";
		}
	}
}
*/
# 구매금액별 이벤트 출력 처리 끝 #
include_once("./_head.php");
?>

<div class='PageTitle'>
  <img src="<?=$g4['path']?>/shop/img/gift_evet_title.gif" alt="이벤트사은품선택" />
</div>

<form action="<?=$_SERVER['PHP_SELF']?>" method='POST' onsubmit='return frm_chk();'>
	<input type="hidden" name='mode' value='insert'/>
	<input type="hidden" name='od_id' value='<?=$_POST['od_id']?>' />
	<?=$event_item_data ;?>
	<div class="gift_choice_bt"><input type="image" src='<?=$g4['path']?>/shop/img/gift_choice_okbt.gif' value=' 사은품 신청 ' /></div>
</form>


<?/*

<div class="gift_choice_wrap">
	<div class="choice_pro_wrap">

		<div class="choice_pro_title"><b>Nordic Naturals 구매시 혜택</b></div>
		<div class="choice_middle_title">*20,000원 이상구매 시</div>

		<div class="choice_pro">
			<div class="choice_img"></div>
			<div class="choice_title">[Rainbow light] 레인보우 귤모양 거미 비타민C 슬라이스 90 거미 Gummy Vitamin C Slices™ 90 개 ( 귤젤리 , 귤 젤리 )...</div>
			<div class="choice_price">가격</div>
			<div class="choice_box">사은품선택</div>
		</div>

		<div class="choice_pro">
			<div class="choice_img"></div>
			<div class="choice_title">[Rainbow light] 레인보우 귤모양 거미 비타민C 슬라이스 90 거미 Gummy Vitamin C Slices™ 90 개 ( 귤젤리 , 귤 젤리 )...</div>
			<div class="choice_price">가격</div>
			<div class="choice_box">사은품선택</div>
		</div>

	</div>
</div>

<div class="gift_choice_bt"><a href=""><img src="<?=$g4['path']?>/shop/img/gift_choice_okbt.gif"></a></div>
*/
?>



<script type="text/javascript">


function frm_chk(){
	if(typeof($(':radio[name=brand]').val()) != 'undefined'){
		if($(':radio[name=brand]').is(':checked') == false){
			if(!confirm('브랜드 구매 사은품을 선택하지 않으셨습니다. 계속하시겠습니까?')){
				return false;
			}
		}
	}

	if(typeof($(':radio[name=amount_event]').val()) != 'undefined'){
		if($(':radio[name=amount_event]').is(':checked') == false){
			if(!confirm('구매금액별 사은품을 선택하지 않으셨습니다. 계속하시겠습니까?')){
				return false;
			}
		}
	}

	if(typeof($(':radio[name=category]').val()) != 'undefined'){
		if($(':radio[name=category]').is(':checked') == false){
			if(!confirm('카테고리 구매 사은품을 선택하지 않으셨습니다. 계속하시겠습니까?')){
				return false;
			}
		}
	}

	return true;

}
</script>

<?/*
<!---1013_new --->
<div class='gift_eventSet'>
	<!--p class='gift_info'><img data-original='http://115.68.20.84/main/detail_banner_nordic.jpg'/></p-->
	<div class='eventContents'>
		<div class='eventTitleArea'>
			<p class='eventTitle'><strong>노르딕 구매금액별 사은품 증정 이벤트</strong> <span>기간별도공지</span><p>
			<p>사은품 수량 소진 시 대체사은품으로 배송됩니다.</p>
			<p>주문 완료 후 사은품선택을 해주셔야 받으실 수 있습니다.</p>
		</div>

		<div class='gift_listArea'>
			<p class='gift_option'>120,000원 이상 구매 시 선택 가능 사은품</p>
			<ul>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
			</ul>
		</div>

		<div class='gift_listArea'>
			<p class='gift_option'>80,000원 이상 구매 시 선택 가능 사은품</p>
			<ul>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
			</ul>
		</div>

		<div class='gift_listArea'>
			<p class='gift_option'>30,000원 이상 구매 시 선택 가능 사은품</p>
			<ul>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
				<li>
					<p class='product_img'><img src='http://ople.com/mall6/data/item/1408688719_s' width='160' height='160' border="0"></p>
					<p class='product_title'>[Totes]토트 우산 Totes Umbrella</p>
					<p class='product_select'>사은품 선택 <input type="radio" name="brand" style="vertical-align: sub;" value="1408688237"></p>
				</li>
			</ul>
		</div>

	</div>
</div>
<!---- 여기까지 ---->
*/?>
<?
include_once "./_tail.php";
?>
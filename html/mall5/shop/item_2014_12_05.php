<?php
include_once("./_common.php");

if(in_array($it_id,array('1336079729','1304466295','1413898640','1153757716'))){
	switch($it_id){
		case '1336079729' : $return_it_id = '1378260604'; break;
		//case '1304466295' : $return_it_id = '1334852796'; break;
		//case '1413898640' : $return_it_id = '1332425915'; break;
		case '1153757716' : $return_it_id = '1332425915'; break;
	}

	if($return_it_id){
		alert('세관통관 문제로 인하여 현재 품절 입니다.\n젤라틴이 변경되고 성분은 동일한 피쉬젤라틴 상품 페이지로 이동합니다.',$_SERVER['PHP_SELF'].'?it_id='.$return_it_id);
		exit;
	}
}

# 원데이 이벤트 상품 재고량 ajax 2014-06-12 홍민기 #
if($_POST['mode'] == 'qty_chk'){
	$qry = sql_fetch("select real_qty,multiplication,order_cnt from yc4_oneday_sale_item where it_id = '".$_POST['it_id']."'");
	$result = (($qry['real_qty'] * $qry['multiplication']) - ($qry['order_cnt'] * $qry['multiplication']));
	echo number_format($result);
	exit;
}

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

// 김선용 200908 :
$rand = rand(4, 6);
$norobot_key = substr($token, 0, $rand);
set_session('ss_norobot_key', $norobot_key);


// 오늘 본 상품 저장 시작
// tv 는 today view 약자
$saved = false;
$tv_idx = (int)get_session("ss_tv_idx");
if ($tv_idx > 0) {
    for ($i=1; $i<=$tv_idx; $i++) {
        if (get_session("ss_tv[$i]") == $it_id) {
            $saved = true;
            break;
        }
    }
}

if (!$saved) {
    $tv_idx++;
    set_session("ss_tv_idx", $tv_idx);
    set_session("ss_tv[$tv_idx]", $it_id);
}
// 오늘 본 상품 저장 끝

// 조회수 증가
if ($_COOKIE[ck_it_id] != $it_id) {
    sql_query(" update $g4[yc4_item_table] set it_hit = it_hit + 1 where it_id = '$it_id' "); // 1증가
    setcookie("ck_it_id", $it_id, time() + 3600, $config[cf_cookie_dir], $config[cf_cookie_domain]); // 1시간동안 저장
}

// 분류사용, 상품사용하는 상품의 정보를 얻음
/*
$sql = " select a.*,
                b.ca_name,
                b.ca_use
           from $g4[yc4_item_table] a,
                $g4[yc4_category_table] b
          where a.it_id = '$it_id'
            and a.ca_id = b.ca_id ";
*/
$sql = " select
				a.*,
                b.ca_name,
                b.ca_use,
				c.ca_id
           from $g4[yc4_item_table] a
				left join
				yc4_category_item c on a.it_id = c.it_id
				left join
                $g4[yc4_category_table] b on b.ca_id = c.ca_id
			where
				a.it_id = '".$it_id."'
";

$it = sql_fetch($sql);


// 통관시 메세지 출력 2014-11-04 홍민기
$tongawn_msg_sql = sql_query("
	select
		distinct b.msg_uid,c.msg
	from
		yc4_category_item a
		left join
		yc4_item_tongwan b on a.it_id = b.it_id or a.ca_id = b.ca_id,
		yc4_item_tongwan_msg c
	where
		a.it_id = '".$it['it_id']."'
		and
		b.msg_uid is not null
		and
		c.uid = b.msg_uid
");
while($row = sql_fetch_array($tongawn_msg_sql)){
	$tongawn_msg .= ($tongawn_msg ? "<br/>":"") . $row['msg'];
}

if($tongawn_msg){
	$tongawn_msg = "<div class='tongawn_msg'>".$tongawn_msg."</div>";
}

if($it['it_meta_title']){
}
if($it['it_meta_keyword']){
	$meta_tag .= "<META NAME='keywords' CONTENT=\"".addslashes($it['it_meta_keyword'])."\">\n";
}else{
	$meta_tag .= "<META NAME='keywords' CONTENT=\"".addslashes(get_item_name($it['it_name']))."\">\n";
}
if($it['it_meta_h1']){
}
if($it['it_meta_description']){
	$meta_tag .= "<META NAME='description' CONTENT=\"".addslashes($it['it_meta_description'])."\">\n";
}else{
	$meta_tag .= "<META NAME='description' CONTENT=\"".addslashes(get_item_name($it['it_name']))."\">\n";
}

$meta_tag .= "<link rel=\"image_src\" href=\"".$g4['https_url2']."/data/item/".$it['it_id']."_m\" />\n";
$meta_tag .= "<meta property=\"og:image\" content=\"".$g4['https_url2']."/data/item/".$it['it_id']."_m\" />\n";

# 통관불가상품 대체 리스트 2014-08-27 홍민기 #
// 아이템코드 => 링크
$no_tongwan = array(
	'1380842243' => $g4['shop_path'].'/list.php?ca_id=1020',
	'1351292171' => $g4['shop_path'].'/list.php?ca_id=1020',
	'1351289967' => $g4['shop_path'].'/list.php?ca_id=1020',
	'1351289770' => $g4['shop_path'].'/list.php?ca_id=1020',
	'1351284219' => $g4['shop_path'].'/list.php?ca_id=v020',
	'1342649490' => $g4['shop_path'].'/list.php?ca_id=1020',
	'1332430595' => $g4['shop_path'].'/list.php?ca_id=7010',
	'1331226814' => $g4['shop_path'].'/list.php?ca_id=7010',
	'1330650989' => $g4['shop_path'].'/search.php?search_ca_id=&search_str=maca&x=0&y=0',
	'1328223163' => $g4['shop_path'].'/search.php?search_ca_id=&search_str=%ED%8C%90%ED%86%A0%ED%85%90%EC%82%B0&x=0&y=0',
	'1323734695' => $g4['shop_path'].'/search.php?search_ca_id=&search_str=maca&x=0&y=0',
	'1274743678' => $g4['shop_path'].'/list.php?ca_id=5130',
	'1406170907' => $g4['shop_path'].'/list.php?ca_id=v0h0',
	'1367008736' => $g4['shop_path'].'/search.php?search_ca_id=&search_str=Propolis&x=0&y=0',
	'1331131292' => $g4['shop_path'].'/search.php?search_ca_id=&search_str=Propolis&x=0&y=0'
);

# 비가공 곡물 처리 2014-07-17 홍민기 #

$weight_qry = sql_fetch("select weight from yc4_item_weight where it_id = '".$it_id."'");
if($weight_qry){
	$weight = $weight_qry['weight'];
	$weight_kg = ( $weight / 1000 ) . 'KG';
}


# 원데이 체크 # 2014-07-01 홍민기
$sql = "select * from yc4_oneday_sale_item where it_id = '".$it['it_id']."'";
$oneday_data = sql_fetch($sql);
if($oneday_data){

	if(
		$oneday_data['end_flag'] == 'N'
		||
		(
			$oneday_data['end_flag'] == 'Y'
			&&
			$oneday_data['st_dt'] <= date('Ymd')
			&&
			$oneday_data['en_dt'] >= date('Ymd')
		)
	){
		$oneday_chk = true;
		$oneday_year = substr($oneday_data['en_dt'],0,4);
		$oneday_month = substr($oneday_data['en_dt'],4,2);
		$oneday_day = substr($oneday_data['en_dt'],6,2);

		$gap = mktime(23,59,59,$oneday_month,$oneday_day,$oneday_year)-mktime();
		/*
		$sql = " select a.*,
					b.ca_name,
					b.ca_use
			   from $g4[yc4_item_table] a,
					$g4[yc4_category_table] b
			  where a.it_id = '".$oneday_data['l_it_id']."'
				and a.ca_id = b.ca_id ";
		*/
		$sql = " select
				a.*,
                b.ca_name,
                b.ca_use,
				c.ca_id
           from $g4[yc4_item_table] a
				,yc4_category_item c
                ,$g4[yc4_category_table] b
			where
				a.it_id = '".$it_id."'
				and a.it_id = c.it_id
				and b.ca_id = c.ca_id
		";

		$it2 = sql_fetch($sql);

		include_once $g4['full_shop_path']."/item_oneday.php";
		exit;
	}
}


//if($it['ca_id'] == 'u0'){
//if(in_array($it['it_id'],array('1306524520','1222827644','1222682189','1251860612','1210012129','1210591619') ) ) {
if(in_array($it['it_id'],$master_cart_no_it_id)){
	$sale_off = true;
}


if (!$it[it_id])
    alert("자료가 없습니다.");
//if (!($it[ca_use] && $it[it_use])) {
if (!$it[it_use]) {
	// 김선용 2014.04 : 작업용 테스트계정 통과
    //if (!$is_admin)
	if(!$is_admin && !check_test_id())
        alert("판매가능한 상품이 아닙니다.");
}
// 김선용 2014.04 : 작업용 테스트계정 통과
// 김선용 201211 : 단종
//if(!$is_admin && $it['it_discontinued']) alert("단종된 상품입니다.");
if((!$is_admin && !check_test_id()) && $it['it_discontinued']) alert("단종된 상품입니다.");

// 분류 테이블에서 분류 상단, 하단 코드를 얻음
$sql = " select ca_include_head, ca_include_tail
           from $g4[yc4_category_table]
          where ca_id = '$it[ca_id]' ";
$ca = sql_fetch($sql);

$g4[title] = "상품 상세보기 : $it[ca_name] - ".get_item_name($it['it_name'])." ";

// 분류 상단 코드가 있으면 출력하고 없으면 기본 상단 코드 출력
if ($ca[ca_include_head])
    @include_once($ca[ca_include_head]);
else
    include_once("./_head.php");

// 분류 위치
// HOME > 1단계 > 2단계 ... > 6단계 분류
$ca_id = $it[ca_id];
include $g4['full_shop_path']."/navigation1.inc.php";

$himg = $g4['path']."/data/item/".$it_id."_h";
if (file_exists($himg))
    echo "<img data-original='".$himg."' border=0><br>";

// 상단 HTML
if($it['it_head_html']){
	echo stripslashes($it['it_head_html']);
}

if ($is_admin){
    echo "<p align=center><a href='".$g4['shop_admin_path']."/itemform.php?w=u&it_id=".$it_id."'><img src='".$g4['shop_img_path']."/btn_admin_modify.gif' border=0></a></p>";
}

// 이 분류에 속한 하위분류 출력
//include $g4['full_shop_path']."/listcategory.inc.php";

// 이전 상품보기
$sql = " select it_id, it_name from ".$g4['yc4_item_table']."
          where it_id > '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it[ca_id],0,4)."'
            and it_use = '1'
          order by it_id asc
          limit 1 ";
$row = sql_fetch($sql);
if($row['it_name']){
	$row['it_name'] = get_item_name($row['it_name']);
}
if ($row[it_id]) {
    $prev_title = "[이전상품보기] ".$row['it_name'];
    $prev_href = "<a href='./item.php?it_id=".$row['it_id']."'>";
} else {
    $prev_title = "[이전상품없음]";
    $prev_href = "";
}

// 다음 상품보기
$sql = " select it_id, it_name from ".$g4['yc4_item_table']."
          where it_id < '".$it_id."'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id desc
          limit 1 ";
$row = sql_fetch($sql);
if($row['it_name']){
	$row['it_name'] = get_item_name($row['it_name']);
}
if ($row[it_id]) {
    $next_title = "[다음상품보기] ".$row['it_name'];
    $next_href = "<a href='./item.php?it_id=".$row['it_id']."'>";
} else {
    $next_title = "[다음상품없음]";
    $next_href = "";
}

// 관련상품의 갯수를 얻음
$sql = " select count(*) as cnt
           from ".$g4['yc4_item_relation_table']." a
           left join ".$g4['yc4_item_table']." b on (a.it_id2=b.it_id and b.it_use='1')
          where a.it_id = '".$it['it_id']."' ";
$row = sql_fetch($sql);
$item_relation_count = $row['cnt'];



/*
# 원데이 세일 상품 # 2014-06-12 홍민기
$oneday_chk_qry = sql_query("select * from yc4_oneday_sale_item where it_id = '".$it['it_id']."' and st_dt <= '".date('Ymd')."' and en_dt >= '".date('Ymd')."'");
if($oneday_data['st_dt'] <= date('Ymd') && $oneday_data['en_dt'] >= date('Ymd') ){

}
if(mysql_num_rows($oneday_chk_qry) > 0){
	$oneday_chk = true;
	$oneday = sql_fetch_array($oneday_chk_qry);

	// 종료일자 $oneday['en_dt'];
	# 남은시간
	$oneday_year = substr($oneday['en_dt'],0,4);
	$oneday_month = substr($oneday['en_dt'],4,2);
	$oneday_day = substr($oneday['en_dt'],6,2);
	$gap = mktime(23,59,59,$oneday_month,$oneday_day,$oneday_year)-mktime();

}
*/



# 구매금액별 이벤트 { 2014-07-03 홍민기 #


$gift_event = gift_event($it['it_id']);
if($gift_event){
	$gift_event_data .= "<div class='gift_title' style='height:auto;'>
	<p class='gift_info'><img data-original='http://115.68.20.84/main/detail_banner_nordic.jpg'/></p>
	<!-- ※주문서 작성 완료 후 본 이벤트 상품을 선택할 수 있습니다. -->
	</div>";
}

$evt = array();
if(is_array($gift_event)){
	foreach($gift_event as $val){
		$evt[$val['bid']][$val['od_amount']][] = $val;
	}

	if(is_array($evt)){
		foreach($evt as $bid => $val){
			# 이벤트 정보 로드 #
			$gift_event_info = sql_fetch("select * from yc4_free_gift_event where bid = '".$bid."'");

			switch($gift_event_info['event_type']){
				case 'A' : $event_type = '전상품 이벤트 '; break;
				case 'B' : $event_type = $gift_event_info['it_maker'].' 이벤트 '; break;
				case 'C' : $event_type = $gift_event_info['ca_name'].' 이벤트 '; break;
			}

			switch($gift_event_info['priod_view']){
				case 'Y' : $event_priod = "기간 : ".substr($gift_event_info['st_dt'],0,4).'-'.substr($gift_event_info['st_dt'],4,2).'-'.substr($gift_event_info['st_dt'],6,2).' ~ '.substr($gift_event_info['en_dt'],0,4).'-'.substr($gift_event_info['en_dt'],4,2).'-'.substr($gift_event_info['en_dt'],6,2); break;
				case 'N' : $event_priod = ''; break;
				case 'C' : $event_priod = '기간 : 별도공지'; break;
			}

			$gift_event_data .= "
	<div class='gift_pro'>
		<div class='gift_pro_title'>
			<div style='display:inline;'><strong>".$gift_event_info['name']."</strong></div>
			<div class='gift_pro_title_data'>
				".$event_priod."
			</div><!--.gift_pro_title_data end-->
		</div><!--.gift_pro_title end-->

		<div class='gift_event_comment'>".$gift_event_info['comment']."</div><!--.gift_event_comment end-->
			";

			if(is_array($val)){
				foreach($val as $od_amount => $val2){
					$gift_event_data .= "
		<div class='gift_pro_middletitle'>".number_format($od_amount)."원 이상 구매 시 선택 가능 사은품</div><!--.gift_pro_middletitle end-->
		<div class='gift_com_pro_wrap'>
					";
					if(is_array($val2)){
						foreach($val2 as $val3){
							$gift_event_data .= "
			<div class='gift_com_pro' href='".$g4['shop_path']."/item.php?it_id=".$val3['it_id']."'>
				<div class='gift_img'>".get_it_image($val3['it_id'].'_s',72,72,$val3['it_id'],null,null,null)."</div>
				<div class='gfit_title'>".get_item_name($val3['it_name']).($val3['it_stock_qty']<1 ? "<img src='".$g4['shop_path']."/img/icon_pumjul.gif'/>":"")."</div>
				".(($val3['show_amount'] == 'y') ? "
					<div class='gift_price'>".((get_amount($val3) <= 100) ? '':display_amount(get_amount($val3)))."</div>
				":"
				")."
			</div><!--.gift_com_pro end-->";
						}
					}
					$gift_event_data .= "
		</div><!-- .gift_com_pro_wrap end -->
					";
				}
			}
			$gift_event_data .= "
	</div><!-- .gift_pro end -->
			";
		}
	}
}


/*
for($i=0; $i<count($gift_event); $i++){

	if($same_item){
		unset($same_item);
		continue;
	}
	switch($gift_event[$i]['event_type']){
		case 'A' : $event_type = '전상품 이벤트 '; break;
		case 'B' : $event_type = $gift_event[$i]['it_maker'].' 이벤트 '; break;
		case 'C' : $event_type = $gift_event[$i]['ca_name'].' 이벤트 '; break;
	}
	if($gift_event[$i]['bid'] != $gift_event[$i-1]['bid']){
		switch($gift_event[$i]['priod_view']){
			case 'Y' : $event_priod = "기간 : ".substr($gift_event[$i]['st_dt'],0,4).'-'.substr($gift_event[$i]['st_dt'],4,2).'-'.substr($gift_event[$i]['st_dt'],6,2).' ~ '.substr($gift_event[$i]['en_dt'],0,4).'-'.substr($gift_event[$i]['en_dt'],4,2).'-'.substr($gift_event[$i]['en_dt'],6,2); break;
			case 'N' : $event_priod = ''; break;
			case 'C' : $event_priod = '기간 : 별도공지'; break;
		}

		$gift_event_data .= "
			<div class='gift_pro'>
				<div class='gift_pro_title'>
					<div style='display:inline;'><strong>".$gift_event[$i]['name']."</strong></div>
					<div class='gift_pro_title_data'>
						".$event_priod."
					</div><!--.gift_pro_title_data end-->
				</div><!--.gift_pro_title end-->

				<div class='gift_event_comment'>".$gift_event[$i]['comment']."</div><!--.gift_event_comment end-->";
	}

	if($gift_event[$i]['od_amount'] != $gift_event[$i-1]['od_amount']){
		$gift_event_data .= "
				<div class='gift_pro_middletitle'>".number_format($gift_event[$i]['od_amount'])."원 이상 구매 시 선택 가능 사은품</div><!--.gift_pro_middletitle end-->
				<div class='gift_com_pro_wrap'>
		";
	}


	$gift_event_data .= "
		<div class='gift_com_pro' href='".$g4['shop_path']."/item.php?it_id=".$gift_event[$i]['it_id']."'>
			<div class='gift_img'>".get_it_image($gift_event[$i]['it_id'].'_s',72,72,$gift_event[$i]['it_id'],null,null,null)."</div>
			<div class='gfit_title'>".get_item_name($gift_event[$i]['it_name'])."</div>
			".(($gift_event[$i]['show_amount'] == 'y') ? "
				<div class='gift_price'>".((get_amount($gift_event[$i]) <= 100) ? '':display_amount(get_amount($gift_event[$i])))."</div>
			":"

			")."

		</div><!--.gift_com_pro end-->
	";

	if($gift_event[$i]['bid'] == $gift_event[$i+1]['bid'] && $gift_event[$i]['od_amount'] == $gift_event[$i+1]['od_amount'] && !$same_item){
		$gift_event_data .= "
			<div class='gift_com_pro' href='".$g4['shop_path']."/item.php?it_id=".$gift_event[$i+1]['it_id']."'>
				<div class='gift_img'>".get_it_image($gift_event[$i+1]['it_id'].'_s',72,72,$gift_event[$i+1]['it_id'],null,null,null)."</div>
				<div class='gfit_title'>".get_item_name($gift_event[$i+1]['it_name'])."</div>
				".(($gift_event[$i+1]['show_amount'] =='y') ? "
					<div class='gift_price'>".((get_amount($gift_event[$i+1]) <= 100) ? '':display_amount(get_amount($gift_event[$i+1])))."</div>
				":"")."

			</div><!--.gift_com_pro end-->
		";
		$same_item = true;
	}
	if($gift_event[$i]['od_amount'] != $gift_event[$i-1]['od_amount']){
		$gift_event_data .= "2222</div><!--.gift_com_pro_wrap end-->";
	}




	if( $gift_event[$i]['bid'] != $gift_event[$i+1]['bid'] ){
		$gift_event_data .= "1111</div><!--.gift_pro end-->";

	}
}
*/

# } 구매금액별 이벤트 #



# 마스터카드 제외상품인지 체크 #
if($_MASTER_CARD_EVENT){
	$it_id_chk = sql_fetch("select count(*) as cnt from master_card_no_item where it_id = '".$it['it_id']."'");

	$it_maker_chk = sql_fetch("select count(*) as cnt from master_card_no_item where it_maker = '".mysql_real_escape_string($it['it_maker'])."'");

	if($it_id_chk['cnt']>0){
		$master_card_comment = "* 해당 상품은 상품은 마스타 카드 프로모션 제외상품 입니다.";
	}elseif($it_maker_chk['cnt']>0){
		$master_card_comment = "* 해당 브랜드의 상품은 마스타 카드 프로모션 제외상품 입니다.";
	}
}




?>
<!--style type="text/css">
.gift_com_pro_wrap{
	width:100%;
}
.gift_wrap{
	overflow:hidden;
}
.gift_pro_title{
	width:99%;
}
.gift_pro_title_data{
	right:26px;
}
</style-->
<script type="text/JavaScript" src="<?php echo $g4['path'];?>/js/shop.js"></script>
<script type="text/JavaScript" src="<?php echo $g4['path'];?>/js/md5.js"></script>



<!-- 장바구니 팝업 -->

<div class="cart_pop_wrap" style="z-index:5;display:none;">
<a class="cart_pop_close" href="#" onclick="close_cart_layer();return false;"><img src="http://115.68.20.84/mall6/page/sub/cart_pop_close.jpg"></a>
<div class="cart_pop_bt">
<a href="#" onclick="close_cart_layer();return false;"><img src="http://115.68.20.84/mall6/page/sub/cart_pop_bt_01.jpg"></a>
<a href="./cart.php"><img src="http://115.68.20.84/mall6/page/sub/cart_pop_bt_02.jpg"></a>
</div>
</div>

<!-- 장바구니 팝업 끝 -->


<br>
<table width=100% cellpadding=0 cellspacing=0 align=center border=0><tr><td>

<table width=100% cellpadding=0 cellspacing=0 style='border:solid 1px #eee;'>
<form name=fitem method=post action="./cartupdate.php">
<input type=hidden name=it_id value='<?php echo $it['it_id'];?>'>
<input type=hidden name=it_name value='<?php echo get_item_name(addslashes($it['it_name']));?>'>
<input type=hidden name=sw_direct>
<input type=hidden name=url>
<input type=hidden name=it_order_onetime_limit_cnt value="<?php echo $it['it_order_onetime_limit_cnt'];?>">
<tr>

    <!-- 상품중간이미지 -->
    <?php
    $middle_image = $it['it_id']."_m";

	//if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){

		$oneplus_rs = sql_query("select it_id from yc4_event_item where ev_id='1413783986'");
		$oneplus_it_id_arr = array();
		while($oneplus_data = sql_fetch_array($oneplus_rs)){
			array_push($oneplus_it_id_arr,$oneplus_data['it_id']);
		}

		$oneplus_icon = (in_array($it['it_id'],$oneplus_it_id_arr)) ? "<span class=\"iconAdd\" ><img src=\"http://115.68.20.84/mall6/ico_onepluse.png\" alt=\"1+1\" class='list_sale_ico' style='width:46px;'></span>" : '';

	//}

    ?>
    <td width=405 align=center valign=top style='border-right:solid 1px #eee;'>
        <table cellpadding=0 cellspacing=0 width=100%>
            <tr><td align=center style='padding:35px 0 10px 0;'>
				<?php if($oneday_chk){?>
				<table cellpadding=0 cellspacing=0 style="position:relative;"><tr><td><?php echo $oneplus_icon; ?>
				<span style="position:absolute;top:-4px;left:10px;"><img src="img/ico_onedaysale.png" alt="원데이세일"></span><?=get_large_image($it[it_id]."_l1", $it[it_id], false)?><?php echo get_it_image($middle_image, 200, 200);?></a></td></tr></table></td></tr>
				<?php } elseif($oneplus_icon){?>
				<table cellpadding=0 cellspacing=0 style="position:relative;"><tr><td><?php echo $oneplus_icon; ?><?=get_large_image($it[it_id]."_l1", $it[it_id], false)?><?php echo get_it_image($middle_image, 200, 200);?></a></td></tr></table></td></tr>
				<?php }else{?>
                <table cellpadding=0 cellspacing=0><tr><td><?php echo get_large_image($it['it_id']."_l1", $it['it_id'], false)?><?php echo get_it_image($middle_image, 200, 200,null,"onclick=\"return false;\"",false,false);?></a></td></tr></table></td></tr>
				<?php }?>
            <tr>
                <td colspan=3 align=center style='padding:0 0 10px 0;'>
                <?php
                for ($i=1; $i<=5; $i++)
                {
                    if (get_large_image("{$it_id}_l{$i}", $it[it_id], false))
                    {
                        echo get_large_image("{$it_id}_l{$i}", $it[it_id], false);
                        if ($i==1 && file_exists("$g4[path]/data/item/{$it_id}_m"))
                            //echo "<img id='middle{$i}' src='$g4[path]/data/item/{$it_id}_m' border=0 width=40 height=40 style='border:1px solid #E4E4E4;' ";
							echo get_it_image("{$it_id}_m",40,40, 'middle'.$i,"onmouseover=\"document.getElementById('$middle_image').src=document.getElementById('middle{$i}').src;\" onclick=\"return false;\"",false,false);
                        else
							echo get_it_image("{$it_id}_l{$i}",40,40, 'middle'.$i,"onmouseover=\"document.getElementById('$middle_image').src=document.getElementById('middle{$i}').src;\" onclick=\"return false;\"",false,false);
//                            echo "<img id='middle{$i}' src='$g4[path]/data/item/{$it_id}_l{$i}' border=0 width=40 height=40 style='border:1px solid #E4E4E4;' ";
 //                       echo " onmouseover=\"document.getElementById('$middle_image').src=document.getElementById('middle{$i}').src;\">";
//                        echo "</a> &nbsp;";
						  echo "&nbsp;";
                    }
                }
                ?>
                </td>
            </tr>
            <tr><td align=center style='padding:5px 0 10px 0;'>
				<!--<?php echo $prev_href;?><img src='<?//=$g4[shop_img_path]?>/prev.gif' border=0 title='<?//=$prev_title?>'><img src="<?=$g4['path'];?>/images/category/category_buy_box01_pre.gif" width="24" height="24" border="0" alt="이전상품"></a>-->
                <?php echo get_large_image($it['it_id']."_l1", $it['it_id']);?>
                <!--<?php echo $next_href;?><img src='<?//=$g4[shop_img_path]?>/next.gif' border=0 title='<?//=$next_title?>'><img src="<?php echo $g4['path'];?>/images/category/category_buy_box01_next.gif" width="24" height="24" border="0" alt="다음상품"></a>-->
			</td></tr>
        </table>
    </td>
    <!-- 상품중간이미지 END -->

    <td valign=top align=center style='padding:20px 40px;'>
        <table width=100% cellpadding=0 cellspacing=0><tr>
		<td valign=top><h1 class="itemtitle"><?php echo it_name_icon($it, stripslashes($it['it_name']), 0,'detail');?></h1></td>
		</tr></table>

        <table width=100% cellpadding=0 cellspacing=0 class='item_view_info'>
        <colgroup>
			<col width=110 />
			<col />
		</colgroup>


		<?php if( $oneday_chk == true) {?>
		<tr>
			<td colspan='2' style="background: url(img/bg_onedaysale_box.gif) no-repeat 0 0;height:75px;padding:0 10px 0 90px;vertical-align:top;">
				<p style="float:left;height:23px;line-height:23px;@-moz-document url-prefix(padding-top:12px;)"><strong class='it_stock' style="font-size:15px;color:#ff0000;font-family:tahoma;"><?
				$qry = sql_query("
					select SUM(ct_qty) as sum_qty
					   from ".$g4['yc4_cart_table']."
					  where it_id = '$it_id'
						and ct_stock_use = 0
						and ct_status in ('주문', '준비')
				");
				$it_stock_qty_qry = sql_fetch_array($qry);
				$it_stock_qty = $it['it_stock_qty'] - $it_stock_qty_qry['sum_qty'];


				echo number_format($it_stock_qty);
				?></strong> 개 남음 <button style="font-size:11px;letter-spacing:-1px;" onclick='oneday_item_qty_chk();return false;'>수량 확인</button></p>
				<p style="float:right;height:23px;line-height:23px;">남은시간 <span style="display:inline-block;border:solid 1px #5a5a5a;background-color:#626262;color:#fff;padding:0 10px;font-size:15px;font-family:tahoma;"><strong id="span_limit_time"></strong><span></p>
			</td>
		</tr>
		<!--<tr>
            <td height='25' colspan='2' align='center' style='font-weight:bold;'>원데이 세일 상품</td>
		</tr>
		<tr>
            <th>재고수량</th>
            <td><span class='it_stock' style='font-weight:bold; color:#ff0000;'></span>개 남음 </td>
		</tr>
		<tr>
            <th>남은시간</th>
            <td><span  style='font-weight:bold; color	:#ff0000;'></span> 남음</td>
		</tr>-->
		<?php }?>

        <?php if ($score = get_star_image($it['it_id'])) { ?>
        <tr>
            <th>고객선호도</th>
            <td><img src='<?php echo $g4['shop_img_path']."/star".$score.".gif";?>' border=0></td>
		</tr>
        <?php } ?>


        <?php if ($it[it_maker]) { ?>
        <tr>
            <th>제조사</th>
            <td><a href="<?=$g4['shop_path']?>/search.php?it_maker=<?=urlencode(stripslashes($it['it_maker']))?>" title="새창으로 현재 제조사 상품보기" target="_blank"><?=stripslashes($it['it_maker'])?> <span class='txt_link_box'>해당 제조사 상품보기</span></a></td>
		</tr>
        <?php } ?>


        <?php if ($it[it_origin]) { ?>
        <tr>
            <th>원산지</th>
            <td><?=$it[it_origin]?></td>
		</tr>
        <?php } ?>

		<?php if($it['SKU'] && is_admin($member['mb_id'])) {?>
		<tr>
			<td>SKU</td>
			<td><?php echo $it['SKU'];?></td>
		</tr>
		<?php }?>

        <?php
        // 선택옵션 출력

		$hide_option = array('유사어'); // 숨길 옵션
        for ($i=1; $i<=6; $i++)
        {
            /*
			// 옵션에 문자가 존재한다면
            $str = get_item_options(trim($it["it_opt{$i}_subject"]), trim($it["it_opt{$i}"]), $i);
            // 숨길 옵션은 건너뛴다!
			if(in_array($it["it_opt{$i}_subject"],$hide_option)) continue;
			if ($str)
            {
                echo "<tr>";
                echo "<th>".$it["it_opt{$i}_subject"]."</th>";
                // echo "<td align=center>:</td>";
                echo "<td style='word-break:break-all;'>$str</td></tr>\n";
                // echo "<tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>\n";
            }
			*/

			if(in_array($it["it_opt{$i}_subject"],$hide_option)) continue;

			if($it["it_opt{$i}_subject"]){
				echo "
					<tr>
						<th>
							".$it["it_opt{$i}_subject"]."
						</th>
						<td>
							".$it['it_opt'.$i]."
						</td>
					</tr>
				";
			}
        }
        ?>


        <?php if (!$it[it_gallery]) { // 갤러리 형식이라면 가격, 구매하기 출력하지 않음 ?>

            <?php if ($it[it_tel_inq]) { // 전화문의일 경우 ?>

                <tr>
                    <th>판매가격</th>
                    <td><FONT COLOR="#FF5D00">전화문의</FONT></td>
				</tr>

            <?php } else { ?>

                <?php if ($it[it_cust_amount]) { // 1.00.03 ?>
                <tr>
                    <th>시중가격</th>
                    <td><span class='text_won' style='color:#777; text-decoration:line-through;font-size:11px;'>￦</span><input type=text name=disp_cust_amount size=12 style='border:none;width:80px; color:#777777; text-decoration:line-through;' readonly value='<?=number_format($it[it_cust_amount])?>'></td>
                </tr>
                <?php } ?>

				<!-- // 김선용 201208 : -->
				<?php // 할인설정이 돼있는 경우만 출력한다.
				if(in_array($member['mb_level'], array('3', '4')) && !$sale_off)
				{
					$off_arr = explode("|", $default['de_mb_level_off']);
					$off_true = false;
					for($k=3; $k<5; $k++){
						if(array_pop(explode('=>', $off_arr[($k-3)]))){
							$off_true = true;
							break;
						}
					}
					if($off_true){
				?>
                <tr>
                    <th>일반회원 판매가격</th>
                    <td><span><?=nf($it['it_amount'])?></span> 원</td>
                </tr>
				<?php }}?>

                <tr>
                    <th>판매가격</th>
                    <td><strong class='text_won'>￦</strong> <input type=text name=disp_sell_amount size=12 style='display:inline;border:none;' class=amount readonly>
                        <input type=hidden name=it_amount value='0'>
                    </td>
                </tr>
				<?php
				$oneday_qry = sql_query("
					select * from yc4_oneday_sale_item where it_id = '".$it['it_id']."' and st_dt >= '".date('Ymd')."' and en_dt <= '".date('Ymd')."'
				");
				if(mysql_num_rows($oneday_qry) > 0 ) {
				?>
				<?php }?>

                <?php
                /* 재고를 표시하는 경우 주석을 풀어주세요.
                <tr>
                    <td>재고수량</td>
                    <td><?=number_format(get_it_stock_qty($it_id))?> 개</td>
                </tr>
                */
                ?>

                <?php if ($config[cf_use_point]) { // 포인트 사용한다면 ?>
                <tr>
                    <th>포인트</th>
                    <td><input type=text name=disp_point style='border:none;' readonly value='점'>
                        <input type=hidden name=it_point value='0'>
                    </td>
                </tr>
                <?php } ?>

                <tr>
                    <th>수량</th>
                    <td>
                        <input type=text name=ct_qty value='1' size=4 maxlength=4 class=ed autocomplete='off' style='text-align:right;' onkeyup='amount_change()'>
                        <img src='<?php echo $g4['shop_img_path'];?>/qty_control.gif' border=0 align=absmiddle usemap="#qty_control_map"> 개
                        <map name="qty_control_map">
                        <area shape="rect" coords="0, 0, 10, 9" href="javascript:qty_add(+1);">
                        <area shape="rect" coords="0, 10, 10, 19" href="javascript:qty_add(-1);">
                        </map></td>
                </tr>
				<?if($master_card_comment){?>
				<tr>
					<th colspan='2'><div class='master_card_brand_comment'><?php echo $master_card_comment;?></div></th>
				</tr>
				<?}?>
				<?php if(date('Ymd') <= '20141111' && in_array($it_id,array('1370850688','1328910099'))){?>
				<tr>
					<th colspan='2'>* 본 상품은 2014년 11월12일부터 일괄 발송됩니다.</th>
				</tr>
				<?php }?>
				<?php if($it_id == '1413898640'){?>
				<tr>
					<th colspan='2'>
						<p style='color:#ff0000; font-weight:bold;'>제조사의 사정으로 Pro Omega 로 발송됩니다.</p>
						<p>본 제품은 소매 제품인 얼티메이트 오메가와 기본적인 성분은 동일하나 보다 엄격한 기준의 원료를 사용하고, 각종 임상실험에 쓰이는 약국 및 전문병원 전용 제품입니다.</p>
						<p style='font-weight:bold;margin-top:5px;'><a href="http://www.nordicnaturals.com/en/Comparator_Studies/New_Research/1122">노르딕내추럴스 프로오메가 설명보기</a></p>
					</th>
				</tr>
				<?php }?>
				<?php if($tongawn_msg){?>
				<tr>
					<th colspan='2'><?php echo $tongawn_msg;?></th>
				</tr>
				<?php }?>
				<!-- // 김선용 201208 : -->
				<?php
				// 품절 sms통보
				if($it['it_stock_qty'] < 1) {

				/*
				# 제목의 품절과 품절 카운팅의 차이가 있어서 수정 2014-05-07 홍민기
				$it_stock_qty = get_it_stock_qty($it_id);
				if($it_stock_qty < 1) {
				*/
				?>
				<tr>
					<?php/*
					<td height=30 colspan=2><span style="color:#ff0000; font-weight:bold;">※ 품절상품입니다.</span>&nbsp;&nbsp;<a href="javascript:;" onclick="js_item_sms('<?=$it['it_id']?>');"><b>[ 이상품 입고시 SMS통보 신청하기 ]</b></a></td>
					*/?>
					<td height=30 colspan=2><span style="color:#ff0000; font-weight:bold;">※ 품절상품입니다.</span>&nbsp;&nbsp;<a href="#" onclick="js_item_sms('<?php echo $it['it_id'];?>');"><b>[ 이상품 입고시 SMS통보 신청하기 ]</b></a></td>
				</tr>

					<script type="text/javascript">
					// 품절상품입고시 sms신청
					function js_item_sms(it_id)
					{
						if(it_id == '') return;
						var url = g4_path+'/sjsjin/item_sms_write.php?it_id='+it_id;
						popup_window(url, 'item_sms_write', 'left=250,top=200,width=500,height=300,status=0,scrollbars=0');
					}
					</script>
				<?php }?>

				<?php if($it['it_order_onetime_limit_cnt']){?>
				<tr>
					<td colspan=2><strong>※ 이 상품은 1회 최대구매수량이 <?=$it['it_order_onetime_limit_cnt']?> 개 입니다.</strong></td>
				</tr>
				<?php }?>

            <?php } ?>

        <?php } ?>
        </table>
        <br>

        <table cellpadding=0 cellspacing=0 width=100%>
		<?php if($weight){?>
		<tr>
			<td>
				<p style='margin:5px 0px; font-weight:bold; color:#ff0000;'>
					본 제품은 비가공 곡물 제품 입니다.
					<br/><br />
					대한민국 관세법에 의거, 비가공 곡물 제품은 주문건당 5KG 이상일 경우
					<br />
					과세 대상에 포함되오니, 주문에 유의하시기 바랍니다.
					<br />
					<br />
					제품 무게 : <span class='item_weight'><?=$weight_kg?></span>
					<br/>
					<br/>
					본 상품은 검역 대상으로 정상 통관은 가능하나<br/>통관이 다소 지연될 수 있는 상품임을 양지하시기 바랍니다.
					</p>
			</td>
		</tr>
		<?php }
		if(in_array($it['it_id'],$plant_item) && !$weight){ // 식물 검역 대상 처리
		?>
		<tr>
			<td>
				<p style='margin:5px 0px; font-weight:bold; color:#ff0000;'>
					본 상품은 식물 검역 대상이므로 일반 상품보다 통관이 다소 지연될 수 있습니다.
				</p>
			</td>
		</tr>
		<?php }?>
        <tr>
            <td align=center>
			<?php
			if($order_no){
				echo $order_no_msg;

			?>
			<?php }elseif($it['ca_id'] == 'z2'){

			}else{?>
            <?php if (!$it['it_tel_inq'] && !$it['it_gallery']) { ?>
            <a href="javascript:fitemcheck(document.fitem, 'direct_buy');"><!--<img src='<?=$g4[shop_img_path]?>/btn2_now_buy.gif' border=0>--><img src="<?php echo $g4['path'];?>/images/category/category_buy_box01_btn_buy.gif" border="0"></a>
            <a href="javascript:ajax_cart_update();"><!--<img src='<?=$g4[shop_img_path]?>/btn2_cart.gif' border=0>--><img src="<?php echo $g4['path'];?>/images/category/category_buy_box01_btn_cart.gif" border="0"></a>
            <?php } ?>

            <? if (!$it['it_gallery']) { ?>
            <a href="javascript:item_wish(document.fitem, '<?php echo $it['it_id'];?>');"><!--<img src='<?=$g4[shop_img_path]?>/btn2_wish.gif' border=0>--><img src="<?php echo $g4['path'];?>/images/category/category_buy_box01_btn_wish.gif" border="0" style='margin-left:15px;'></a>
            <a href="javascript:popup_item_recommend('<?php echo $it['it_id'];?>');"><!--<img src='<?=$g4[shop_img_path]?>/btn_item_recommend.gif' border=0>--><img src="<?php echo $g4['path'];?>/images/category/category_buy_box01_btn_friend.gif" border="0"</a>
            <? } ?>

            <script type="text/JavaScript">
            // 상품보관
            function item_wish(f, it_id)
            {
                f.url.value = "<?php echo $g4['shop_path'];?>/wishupdate.php?it_id="+it_id;
                f.action = "<?php echo $g4['shop_path'];?>/wishupdate.php";
                f.submit();
            }

            // 추천메일
            function popup_item_recommend(it_id)
            {
                if (!g4_is_member)
                {
                    if (confirm("회원만 추천하실 수 있습니다."))
                        document.location.href = "<?=$g4[bbs_path]?>/login.php?url=<?=urlencode("$g4[shop_path]/item.php?it_id=$it_id")?>";
                }
                else
                {
                    url = "./itemrecommend.php?it_id=" + it_id;
                    opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
                    popup_window(url, "itemrecommend", opt);
                }
            }
            </script>
			<?}?>

            </td></tr>
        </table></td>
    </tr>

	<?if( $no_tongwan[$it['it_id']] ) { // 동관불가 상품에 해당한다면 대체리스트 링크 출력?>
	<tr>
		<td style='background: url("../images/common/txt_alternative_info.gif") no-repeat 0px 0px; height: 58px; text-align: right; padding-top: 5px; padding-right: 50px;' colspan="3"><a href="<?=$no_tongwan[$it['it_id']]?>"><img alt="대체상품보기" data-original="../images/common/btn_alternative-goods.gif"></a></td>
	</tr>
	<tr><td height="20" colspan="3"></td></tr>
	<?}?>
</form>
</table>
<?php if($gift_event_data){  ?>
            <div class="gift_wrap">
               <?=$gift_event_data;?>
              </div>
<?php } ?>
          <?/*
		  <!-- gift 추가 -->

      <div class="gift_wrap">
        <div class="gift_title">※주문서 작성 완료 후 본 이벤트 상품을 선택할 수 있습니다.</div>
        <div class="gift_pro">
          <div class="gift_pro_title">
            <div>
              <b>Nordic Naturals 구매시 혜택</b>
            </div>
            <div class="gift_pro_title_data">
              2014-07-03<br/> ~ 2015-07-04
            </div>
          </div>
          <div class="gift_pro_middletitle">*20,000원 이상구매 시</div>
          <div class="gift_com_pro">
            <div class="gift_img">이미지</div>
            <div class="gfit_title">
              [Rainbow light] 레인보우 귤모양 거미...
            </div>
            <div class="gift_price">가격</div>
          </div>
          <div class="gift_com_pro">
            <div class="gift_img">이미지</div>
            <div class="gfit_title">
              [Rainbow light] 레인보우 귤모양 거미...
            </div>
            <div class="gift_price">가격</div>
          </div>

          <div class="gift_pro_middletitle">*100,000원 이상구매 시</div>
          <div class="gift_com_pro">
            <div class="gift_img">이미지</div>
            <div class="gfit_title">
              [Rainbow light] 레인보우 귤모양 거미...
            </div>
            <div class="gift_price">가격</div>
          </div>
        </div>

        <div class="gift_pro">
          <div class="gift_pro_title">
            <div>
              <b>전상품 이벤트</b>
            </div>
            <div class="gift_pro_title_data">
              2014-07-03<br/> ~ 2015-07-04
            </div>
          </div>
          <div class="gift_pro_middletitle">*20,000원 이상구매 시</div>
          <div class="gift_com_pro">
            <div class="gift_img">이미지</div>
              <div class="gfit_title">
                [Rainbow light] 레인보우 귤모양 거미...</div>
                <div class="gift_price">가격</div>
          </div>
        </div>

      </div>
      </div>

          <!-- gift 추가완료 -->
*/?>

          <!--0721_내용수정-->
<div class='item_view_tab'>

	<ul>
		<?/*
		<li><a href="#item_explan" onclick="//click_item('*',this); return false;" class='item_view_tab_active'>상품정보</a></li>
		<li><a href="#item_use" onclick="//click_item('item_use',this); return false;">상품후기 (<span id='item_use_count'>0</span>)</a></li>
		<li><a href="#item_qa" onclick="//click_item('item_qa',this); return false;">상품문의 (<span id='item_qa_count'>0</span>)</a></li>
		<li><a href="#item_baesong" onclick="//click_item('item_baesong',this); return false;">배송정보</a></li>
		<li><a href="#item_change" onclick="//click_item('item_change',this); return false;">교환/반품</a></li>
		<!--<li><a href="#" onclick="click_item('item_relation',this); return false;">관련상품 (<span id='item_relation_count'>0</span>)</a></li>-->
		*/?>
		<li><a href="#item_explan" class='item_view_tab_active'>상품정보</a></li>
		<li><a href="#item_use">상품후기 (<span id='item_use_count'>0</span>)</a></li>
		<li><a href="#item_qa">상품문의 (<span id='item_qa_count'>0</span>)</a></li>
		<li><a href="#item_baesong">배송정보</a></li>
		<li><a href="#item_change">교환/반품</a></li>
	</ul>
</div>

<script type="text/JavaScript">
/*
function click_item(id, obj)
{
    <?
	if(preg_match("/okflex\.com/", $_SERVER['HTTP_HOST']))
	    echo "var str = 'item_explan,item_use";
	else
	    echo "var str = 'item_explan,item_use,item_qa";
    if ($default[de_baesong_content]) echo ",item_baesong";
    if ($default[de_change_content]) echo ",item_change";
    echo ",item_relation';";
    ?>

    var s = str.split(',');

    for (i=0; i<s.length; i++)
    {
        if (id=='*')
            document.getElementById(s[i]).style.display = 'block';
        else
            document.getElementById(s[i]).style.display = 'none';
    }

    if (id!='*')
        document.getElementById(id).style.display = 'block';

	$('.item_view_tab_active').removeClass();
	$(obj).addClass('item_view_tab_active');
}
*/
</script>



<!-- 상품설명 -->
<div id='item_explan' class="product-info" style='display:block;'>
<h2>상품정보</h2>
	<?php if($it['it_maker'] == "Doctor's Best"){ // 닥터스 베스트는 동영상 보이도록 2014-10-21 홍민기
	?>
	<iframe src="//player.vimeo.com/video/103447820" width="930" height="520" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe><br />
	<?php }?>
	<table><tr><td>
    <? if ($it[it_basic]) { ?>
<?=$it[it_basic]?>
    <? } ?>

    <? if ($it[it_explan]) { ?>
<div id='div_explan' class='item_explanBOX'>
<!--0721내용추가
	<div>
		<p class='box_title'>제품명</p>
		<p class='box_item_title'>[COUNTRY LIFE] 천연 켈프 (요오드) 225mcg 300 타블렛 Country Life Norwegian Kelp 225 mcg, 300-Count </p>
	</div>
	<div>
		<p class='box_title'>제품설명</p>
		<div>
			<p>켈프 천연 요오드 225  mcg 함유</p>
		</div>
	</div>
	<div>
		<p class='box_title'>보관방법</p>
		<div>
			<p>뚜껑이 쉽게 열리지않도록 닫아놓으시고 어린이 손이 닿지않는 서늘하고 건조한곳에 보관하세요</p>
		</div>
	</div>
	<div>
		<p class='box_title'>주의사항</p>
		<div>
			<p>본 제품은 건강보조식품이며 의약품이 아닙니다.</p>
			<p>사이트 모든 제품 배송은 미국 현지에서 직배송 됩니다.</p>
		</div>
	</div>
	 -->
<?

$file = 'http://115.68.20.84/desc/'.$_GET['it_id'].'.jpg';
$file_headers = @get_headers($file);
if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
    echo conv_content($it[it_explan], 1);
}
else {
    echo "<img data-original='".$file."'/>";

}


?>
<div style='display:none;'><?//=conv_content($it[it_explan], 1);?></div>


</div>
</td></tr></table>
    <? } ?>

</div>
<!-- 상품설명 end -->



<?
// 사용후기
$use_page_rows = 30;    // 사용후기 페이지당 목록수
include_once($g4['full_shop_path']."/itemuse.inc.php");


// 김선용 201107 : OKFLEX.COM 일때 미출력
if(!preg_match("/okflex\.com/", $_SERVER['HTTP_HOST'])){
// 상품문의
$qa_page_rows = 30;     // 상품문의 페이지당 목록수
include_once($g4['full_shop_path']."/itemqa.inc.php");
}
?>


<!-- 배송정보 -->
<div id='item_baesong' class="product-info" style='display:block;'>
<h2>배송정보</h2>
<br>
<center><img data-original='<?php echo $g4['shop_img_path'];?>/baesong_v1.gif'></center>
<?php echo conv_content($default[de_baesong_content], 1);?>
</div>
<!-- 배송정보 end -->


<!-- 교환/반품 -->
<div id='item_change' class="product-info" style='display:block;'>
<h2> 교환/반품</h2>

<br>
<center><img data-original='<?php echo $g4['shop_img_path'];?>/exchange.gif'></center>
</div>
<!-- 교환/반품 end -->

<?php
if($item_relation_count>0){
?>
<!-- 관련상품 -->
<div id='item_relation' class="product-info" style='display:block;'>
<h2>관련상품</h2>
        <?php
        $sql = " select b.*
                   from $g4[yc4_item_relation_table] a
                   left join $g4[yc4_item_table] b on (a.it_id2=b.it_id)
                  where a.it_id = '$it[it_id]'
                    and b.it_use='1' ";
        $result = sql_query($sql);
        $num = @mysql_num_rows($result);
        if ($num){
			$list_mod   = $default[de_rel_list_mod];
			$img_width  = $default[de_rel_img_width];
			$img_height = $default[de_rel_img_height];
			$td_width = (int)(100 / $list_mod);

            include "$g4[full_shop_path]/maintype10.inc.php";
		}
        else
            echo "이 상품과 관련된 상품이 없습니다.";
        ?>
</div>
<!-- 관련상품 end -->
<?php
}
?>


</td></tr></table>

<script type="text/JavaScript">
/*
//var basic_amount = parseInt('<?=$it[it_amount]?>');
var basic_amount = parseInt('<?=get_amount($it)?>');
var basic_point  = parseFloat('<?=$it[it_point]?>');
var cust_amount  = parseFloat('<?=$it[it_cust_amount]?>');
*/



function qty_add(num)
{
    var f = document.fitem;
    var qty = parseInt(f.ct_qty.value);
    if (num < 0 && qty <= 1)
    {
        alert("수량은 1 이상만 가능합니다.");
        qty = 1;
    }
    else if (num > 0 && qty >= 9999)
    {
        alert("수량은 9999 이하만 가능합니다.");
        qty = 9999;
    }
    else
    {
        qty = qty + num;
    }

    f.ct_qty.value = qty;

    amount_change();
}

function get_amount(data)
{
    var str = data.split(";");
    var num = parseInt(str[1]);
    if (isNaN(num)) {
        return 0;
    } else {
        return num;
    }
}

function amount_change()
{
    var basic_amount = parseInt('<?=get_amount($it)?>');
    var basic_point  = parseFloat('<?=$it[it_point]?>');
    var cust_amount  = parseFloat('<?=$it[it_cust_amount]?>');

    var f = document.fitem;
    var opt1 = 0;
    var opt2 = 0;
    var opt3 = 0;
    var opt4 = 0;
    var opt5 = 0;
    var opt6 = 0;
    var ct_qty = 0;

    if (typeof(f.ct_qty) != 'undefined')
        ct_qty = parseInt(f.ct_qty.value);

	if(f.ct_qty.value.replace(/[0-9]/g,'').length > 0 || f.ct_qty.value.length < 1){
		f.ct_qty.value = 1;
		ct_qty = 1;
	}

    if (typeof(f.it_opt1) != 'undefined') opt1 = get_amount(f.it_opt1.value);
    if (typeof(f.it_opt2) != 'undefined') opt2 = get_amount(f.it_opt2.value);
    if (typeof(f.it_opt3) != 'undefined') opt3 = get_amount(f.it_opt3.value);
    if (typeof(f.it_opt4) != 'undefined') opt4 = get_amount(f.it_opt4.value);
    if (typeof(f.it_opt5) != 'undefined') opt5 = get_amount(f.it_opt5.value);
    if (typeof(f.it_opt6) != 'undefined') opt6 = get_amount(f.it_opt6.value);

    var amount = basic_amount + opt1 + opt2 + opt3 + opt4 + opt5 + opt6;
    var point  = parseInt(basic_point);

    if (typeof(f.it_amount) != 'undefined')
	<?if($weight){ # 비가공 곡물일 경우 2014-07-17 홍민기 ?>

	var weight = Number('<?=$weight?>') * ct_qty / 1000;
	$('.item_weight').text(weight + 'KG');
//	document.getElementsByClassName('item_weight')[0].innerHTML = weight + 'KG';
	<?}?>

    if (typeof(f.it_amount) != 'undefined'){
        f.it_amount.value = amount;
	}

    if (typeof(f.disp_sell_amount) != 'undefined')
        f.disp_sell_amount.value = number_format(String(amount * ct_qty));

    if (typeof(f.disp_cust_amount) != 'undefined')
        f.disp_cust_amount.value = number_format(String(cust_amount * ct_qty));

    if (typeof(f.it_point) != 'undefined') {
        f.it_point.value = point;
        f.disp_point.value = number_format(String(point * ct_qty));
    }
}

<?php if (!$it[it_gallery]) { echo "amount_change();"; } // 처음시작시 한번 실행 ?>


function chk_buy_validate(f, act){
	 // 판매가격이 0 보다 작다면
    if (f.it_amount.value < 0)
    {
        alert("전화로 문의해 주시면 감사하겠습니다.");
        return false;
    }

	<?if($weight){ # 비가공 곡물일 경우 2014-07-17 홍민기 ?>
	if(act == 'direct_buy'){
		var weight = document.getElementsByClassName('item_weight')[0].innerHTML.replace('KG','');
		if(weight >= 5){
			if(!confirm('대한민국 관세법에 의거,\n비가공 곡물 제품은 주문건당 5KG 이상일 경우\n과세 대상에 포함되오니, 주문에 유의하시기 바랍니다.\n\n주문하시는 비가공 곡물제품이며 5KG을 초과합니다.\n\n주문하시겠습니까?')){
				return false;
			}
		}

	}
	<?}?>

    for (i=1; i<=6; i++)
    {
        if (typeof(f.elements["it_opt"+i]) != 'undefined')
        {
            if (f.elements["it_opt"+i].value == '선택') {
                alert(f.elements["it_opt"+i+"_subject"].value + '을(를) 선택하여 주십시오.');
                f.elements["it_opt"+i].focus();
                return false;
            }
        }
    }

    if (act == "direct_buy") {
        f.sw_direct.value = 1;
    } else {
        f.sw_direct.value = 0;
    }

    if (!f.ct_qty.value) {
        alert("수량을 입력해 주십시오.");
        f.ct_qty.focus();
        return false;
    } else if (isNaN(f.ct_qty.value)) {
        alert("수량을 숫자로 입력해 주십시오.");
        f.ct_qty.select();
        f.ct_qty.focus();
        return false;
    } else if (parseInt(f.ct_qty.value) < 1) {
        alert("수량은 1 이상 입력해 주십시오.");
        f.ct_qty.focus();
        return false;
    }

	// 김선용 201208 :
	if("<?=$it['it_order_onetime_limit_cnt']?>" != '0'){
		if(parseInt(f.ct_qty.value) > parseInt(<?=$it['it_order_onetime_limit_cnt']?>)){
			alert("이 상품은 1회 최대구매수량이 \'<?=$it['it_order_onetime_limit_cnt']?> 개\' 입니다.");
			return false;
		}
	}

	return true;
}

// 바로구매 또는 장바구니 담기
function fitemcheck(f, act)
{
    if(chk_buy_validate(f, act)){
		amount_change();
		f.submit();
	}
}

function close_cart_layer(){
	$('.layer_wrap2,.cart_pop_wrap').hide();
	$('.site_wrap').removeAttr('style');
	$('.Floating_bannerArea').show();

}


function ajax_cart_update()
{
	var f = document.fitem;
	var act = 'cart_update';

	if(chk_buy_validate(f, act)){

		amount_change();

		$.ajax({
			type: 'POST'
			, dataType: "xml"
			, url: "cartupdate_ajax.php"
			, data: $("form[name=fitem]").serialize()
			, success: function(xml) {
				if($(xml).find("result").text() == "success"){

					$(window).scrollTop(0);

					$('.site_wrap').css({
						'overflow' : 'hidden',
						'width' : '100%',
						'height' : $(window).height()+'px'
					});



					$('.Floating_bannerArea').hide();

					$('.layer_wrap2').show();

					$(".cart_pop_wrap").show();

				} else {
					alert($(xml).find("error_msg").text());
					return false;
				}

			}
			, error: function(xhr, status, error) {alert("네트워크 장애가 발생하였습니다. 새로고침 후 다시한번 시도해주세요."); }
		});

	}
}

function addition_write(element_id)
{
    if (element_id.style.display == 'none') { // 안보이면 보이게 하고
        element_id.style.display = 'block';
    } else { // 보이면 안보이게 하고
        element_id.style.display = 'none';
    }
}


var save_use_id = null;
function use_menu(id)
{
    if (save_use_id != null)
        document.getElementById(save_use_id).style.display = "none";
    menu(id);
    save_use_id = id;
}

var save_qa_id = null;
function qa_menu(id)
{
    if (save_qa_id != null)
        document.getElementById(save_qa_id).style.display = "none";
    menu(id);
    save_qa_id = id;
}

if (document.getElementById("item_use_count"))
    document.getElementById("item_use_count").innerHTML = "<?=$use_total_count?>";
<?php if(!preg_match("/okflex\.com/", $_SERVER['HTTP_HOST'])){ // 김선용 201107 : OKFLEX.COM 일때 미출력?>
if (document.getElementById("item_qa_count"))
    document.getElementById("item_qa_count").innerHTML = "<?=$qa_total_count?>";
<?php }?>
if (document.getElementById("item_relation_count"))
    document.getElementById("item_relation_count").innerHTML = "<?=$item_relation_count?>";

// 상품상세설명에 있는 이미지의 사이즈를 줄임
function explan_resize_image()
{
    var image_width = 600;
    var div_explan = document.getElementById('div_explan');
    if (div_explan) {
        var explan_img = div_explan.getElementsByTagName('img');
        for(i=0;i<explan_img.length;i++)
        {
            //document.write(explan_img[i].src+"<br>");
            img = explan_img[i];
            imgx = parseInt(img.style.width);
            imgy = parseInt(img.style.height);
            if (imgx > image_width)
            {
                image_height = parseFloat(imgx / imgy)
                img.style.width = image_width;
                img.style.height = parseInt(image_width / image_height);
            }
        }
    }
}
<?php if ($it['it_explan']) { echo "explan_resize_image();"; } // onLoad 할때 실행 ?>
</script>


<!-- // 김선용 200908 : // 김선용 201206 :-->
<script type="text/javascript" src="<?=$g4[path]?>/js/jquery.kcaptcha.js"></script>
<script type="text/javascript">
$(function() {
    $("#kcaptcha_image_use, #kcaptcha_image_qa").bind("click", function() {
        $.ajax({
            type: 'POST',
            url: g4_path+'/'+g4_bbs+'/kcaptcha_session.php',
            cache: false,
            async: false,
            success: function(text) {
                $("#kcaptcha_image_use, #kcaptcha_image_qa").attr('src', g4_path+'/'+g4_bbs+'/kcaptcha_image.php?t=' + (new Date).getTime());
            }
        });
    })
    .css('cursor', 'pointer')
    .attr('title', '글자가 잘 안보이시는 경우 클릭하시면 새로운 글자가 나옵니다.')
    .attr('width', '120')
    .attr('height', '60')
    .trigger('click');

    explan_resize_image();
});

<?php if( $oneday_chk == true) {?>
function oneday_item_qty_chk (){
	$.ajax({
		url : '<?=$_SERVER['PHP_SELF'];?>',
		type : 'post',
		datatype : 'html',
		data : {
			'mode' : 'qty_chk',
			'it_id' : '<?=$it['it_id'];?>'
		},success : function( result ){
			if( result == 0 ){
				location.reload();
			}
			$('strong.it_stock').text(result);
		}
	});
}

function init(){
	time_check();
}
var utime = <?=$gap;?>; //초시계 초기값
var flag = true;
var today = new Date(1970, 1, 1)

function time_check(){
	if (!flag) {
		return;
	}
	utime--;
	today.setTime(utime * 1000 - (9 * 60 * 60 * 1000));

	var hours   = today.getHours();
	var minutes = today.getMinutes();
	var seconds = today.getSeconds();
	hours = hours < 10 ? "0" + hours:hours;
	minutes = minutes < 10 ? "0" + minutes:minutes;
	seconds = seconds < 10 ? "0" + seconds:seconds;
	document.getElementById("span_limit_time").innerHTML = hours + " : " + minutes + " : " +  seconds;

	if(utime>0)
		setTimeout("time_check()", 1000); //1초
}

init();


<?php }?>

<?php
if($gift_event_data){
?>
/*
$(document).ready(function(){
	$('.gift_pro').width('auto');
	if( $('.gift_pro').length%2 == 1 ){
		$('.gift_pro:last').width('auto');
	}
	for(var i=0; i<$('.gift_com_pro_wrap').length; i++){


		if( $('.gift_com_pro_wrap:eq('+i+') .gift_com_pro').length / 2 >= 1){

			$('.gift_com_pro_wrap:eq('+i+') .gift_com_pro').css( 'width', ($('.gift_com_pro_wrap:eq('+i+')').width()/2)-2 );
//			$('.gift_com_pro_wrap:eq('+i+') .gift_com_pro')
		}else{
			$('.gift_com_pro_wrap:eq('+i+') .gift_com_pro').css('width',$('.gift_com_pro_wrap:eq('+i+')').width());
			$('.gift_com_pro_wrap:eq('+i+') .gfit_title').css('width');
		}
	}

	for(var i=0; i<$('.gift_com_pro').length; i++){

		$('.gift_com_pro:eq('+i+') .gfit_title').width( Number($('.gift_com_pro:eq('+i+')').width()) - Number($('.gift_com_pro:eq('+i+') .gift_img').width()) -15 );
	}
});
*/
<?php }?>
</script>






<?php
// 하단 HTML
echo stripslashes($it['it_tail_html']);

$timg = "$g4[path]/data/item/{$it_id}_t";
if (file_exists($timg))
    echo "<img data-original='$timg' border=0><br>";

if ($ca['ca_include_tail'])
    @include_once($ca['ca_include_tail']);
else
    include_once("./_tail.php");
?>
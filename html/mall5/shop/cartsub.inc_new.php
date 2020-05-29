<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*
    $s_page 는 cart.php 일때 수량의 수정, 물품의 삭제를 위한 변수이다.
               orderinquiryview.php 일때 배송상태등을 나타내는 변수이다.

    $s_on_uid 는 유일한 키인데 orderupdate.php 에서 ck_on_uid 를 죽이면서
    ck_tmp_on_uid 에 복사본을 넣어준다. ck_tmp_on_uid 는 orderconfirm.php 에서만 사용한다.
*/

$colspan = 9;

?>

<form name=frmcartlist method=post style="padding:0px;">



<table width="100%" cellpadding="0" cellspacing="0" class="list_styleB" style="margin-top:-10px;" >

  <colgroup>
    <col width="60"/>
	<col width="120"/>
    <col />
    <col width='120'/>
    <col width='100'/>
	<col width='100'/>
	<col width='100'/>
	<col width='120'/>
	<col width='60'/>
  </colgroup>
  <thead>
<tr>
	<th><input type="checkbox" name="all_check" checked /></th>
    <th colspan=2>상품명</th>
    <th>수량</th>
    <th>판매가</th>
    <th>소계</th>
    <th>포인트</th>
	<th>비고</th>
	<th>상태</th>
</tr>
</thead>

<?
$tot_point = 0;
$tot_sell_amount = 0;
$tot_cancel_amount = 0;

// 김선용 201209 : 추천인 할인처리
$recommend_off_sale = false;
$order_save_amount = 0;
$it_bottle_sum = 0; // 김선용 201210 : 병수량 카운트

$goods = $goods_it_id = "";
$goods_count = -1;

// $s_on_uid 로 현재 장바구니 자료 쿼리
$sql = " select a.ct_id,
                a.it_opt1,
                a.it_opt2,
                a.it_opt3,
                a.it_opt4,
                a.it_opt5,
                a.it_opt6,
                a.ct_amount,
                a.ct_point,
                a.ct_qty,
                a.ct_status,
                b.it_id,
                b.it_name,
				b.it_maker, /* 홍민기 2014-07-03 구매금액별 이벤트 때문에 추가*/
                b.ca_id,
				b.ca_id2,
				b.ca_id3,
				b.ca_id4,
				b.ca_id5,
				b.it_order_onetime_limit_cnt,
				b.it_bottle_count /* // 김선용 201210 : 병수량 */
           from $g4[yc4_cart_table] a,
                $g4[yc4_item_table] b
          where a.on_uid = '$s_on_uid'
            and a.it_id  = b.it_id
			and a.ihappy_fg is null
          order by a.ct_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	if($row['it_name']){
		$row['it_name'] = get_item_name($row['it_name']);
	}
	# 원데이 상품 체크 #
	$sql = "
		select it_id,l_it_id from yc4_oneday_sale_item where it_id = '".$row['it_id']."'
	";
	$oneday_data = sql_fetch($sql);
	if($oneday_data){
		//$oneday_time_chk = sql_fetch($a="select it_id,price,end_flag from yc4_oneday_sale_item where it_id = '".$row['it_id']."' and ( st_dt < '".date('Ymd')."' or en_dt > '".date('Ymd')."' ) and real_qty>order_cnt");

		$oneday_time_chk = sql_fetch("
			select
				it_id,price,end_flag
			from
				yc4_oneday_sale_item
			where
					it_id = '".$row['it_id']."'
				and not'".date('Ymd')."' between st_dt and en_dt
				and real_qty > order_cnt
		");

		$it_amount = sql_fetch("select it_id,it_amount,it_tel_inq,it_amount3,it_amount2 from ".$g4['yc4_item_table']." where it_id = '".$row['it_id']."'");
		$it_amount = get_amount($it_amount);

		if($oneday_time_chk && $row['ct_status'] == '쇼핑'){ // 기간이 지났는데 결제가 안된 장바구니에 있는 원데이 상품은 삭제한다.
			if(
				$oneday_time_chk['end_flag'] == 'N'  // 종료 후 종료로 남은 상품은 무조건 삭제
				||
				(
					# 상품 원래 가격보다 싸다면 삭제
					$it_amount > $row['ct_amount']
				)
			){
				$oneday_delete = true;
				sql_query("delete from $g4[yc4_cart_table] where on_uid = '".$s_on_uid."' and it_id = '".$row['it_id']."'");
			}
		}
		$it_id = $row['it_id'];
		$it_id2 = $oneday_data['l_it_id'];

		$point_disabled = true;

	}else{
		$it_id = $it_id2 = $row['it_id'];
	}

	# 비가공 곡물 제품 처리 2014-07-17 홍민기 #

	$weight_qry = sql_fetch("select weight,type from yc4_item_weight where it_id = '".$row['it_id']."'");
	if($weight_qry){
		$weight[$weight_qry['type']] += $weight_qry['weight'] * $row['ct_qty'];
		$weight_kg[$weight_qry['type']] = ($weight[$weight_qry['type']] / 1000) . 'KG';
	}

	if(is_array($weight)){
		foreach($weight as $key => $val){
			if($val>=5000){
				$weight_over[$key] = true; // 품목별 무게 초과 여부
				$weight_over_result = true; // 무게를 초과하는 품목이 하나라도 있다면 true
			}
			$weight_kg[$key] = ($val/1000).'KG';
		}
	}



    if (!$goods)
    {
        //$goods = addslashes($row[it_name]);
        //$goods = get_text($row[it_name]);
		// 김선용 201306 : 우리 에스크로관련 상품명 넘길때 잘라서 넘겨준다.(상품명이 너무 길다)
        $goods = preg_replace("/\'|\"|\||\,|\&|\;/", "", $row[it_name]);
		$goods_cut = cut_str($goods, 20, '...');
        $goods_it_id = $row[it_id];
    }
    $goods_count++;

    if ($i==0) { // 계속쇼핑
		$continue_ca_id = sql_fetch("select ca_id from yc4_category_item where it_id = '".$row['it_id']."'");
		$continue_ca_id = $continue_ca_id['ca_id'];
//        $continue_ca_id = $row[ca_id];
    }


    $a1 = "<a href='./item.php?it_id=$row[it_id]'>";
    $a2 = "</a>";
    $image       = get_it_image($it_id2."_s", 50, 50, $row[it_id]);


    $it_name = $a1 . stripslashes($row[it_name]) . $a2 . "<br>";
    $it_name .= print_item_options($row[it_id], $row[it_opt1], $row[it_opt2], $row[it_opt3], $row[it_opt4], $row[it_opt5], $row[it_opt6]);

 	$point       = $row[ct_point] * $row[ct_qty];
	$sell_amount = $row[ct_amount] * $row[ct_qty];

	// 김선용 201209 : 추천인 할인처리. 할인금액존재, 회원, 피추천인존재
	if($default['de_recom_off_amount'] && $member['mb_id'] && $member['mb_recommend'] != '') {
		$chk_mb = get_member($member['mb_recommend'], "mb_id"); // 실제 회원이 존재하면
		$chk_order = chk_recommend_order($member, $default['de_recom_off_ca_id']); // 할인적용분류 구매내역 확인
		if($chk_mb['mb_id'] && $chk_order){ // 피추천회원이 있고, 구매내역이 없으면(주문/준비/배송/완료)
			if($default['de_recom_off_ca_id'] == '' || $default['de_recom_off_ca_id'] == substr($row['ca_id'], 0, strlen($default['de_recom_off_ca_id'])))
				$recommend_off_sale = true;
		}
	}

	// 김선용 201210 : 병수량 카운트
	$it_bottle_sum += ((int)$row['it_bottle_count'] * $row['ct_qty']);

	// 브랜드별 집계 2014-07-03 홍민기
	$gift_event_brand[$row['it_maker']] += $row['ct_amount'] * $row['ct_qty'];

	// 카테고리별 집계 2014-07-03 홍민기
	if($row['ca_id']) $gift_event_cate[$row['ca_id']] += $row['ct_amount'] * $row['ct_qty'];
	if($row['ca_id2']) $gift_event_cate[$row['ca_id2']] += $row['ct_amount'] * $row['ct_qty'];
	if($row['ca_id3']) $gift_event_cate[$row['ca_id3']] += $row['ct_amount'] * $row['ct_qty'];
	if($row['ca_id4']) $gift_event_cate[$row['ca_id4']] += $row['ct_amount'] * $row['ct_qty'];
	if($row['ca_id5']) $gift_event_cate[$row['ca_id5']] += $row['ct_amount'] * $row['ct_qty'];


    if ($i > 0)
        // echo "<tr><td colspan='$colspan' height=1 bgcolor=#E7E9E9></td></tr>";

	echo "<tr>";
	echo "<td><input type='checkbox' name='ct_checkbox_num[]' value='$i' checked onclick='check_order_price()'/>";
	echo "<input type=hidden name='ct_amount_sum_{$i}'    value='$sell_amount'>";
	echo "<input type=hidden name='ct_point_sum_{$i}'    value='$point'>";
	echo "</td>";
    echo "<td>$image</td><td style='text-align:left;'>";
    echo "<input type=hidden name='ct_id[$i]'    value='$row[ct_id]'>";
    echo "<input type=hidden name='it_id[$i]'    value='$row[it_id]'>";
    echo "<input type=hidden name='ap_id[$i]'    value='$row[ap_id]'>";
    echo "<input type=hidden name='bi_id[$i]'    value='$row[bi_id]'>";
    echo "<input type=hidden name='it_name[$i]'  value='".get_text(str_replace('||',' ',$row['it_name']))."'>";
	// 김선용 201208 :
    echo "<input type=hidden name='it_order_onetime_limit_cnt[$i]' value='$row[it_order_onetime_limit_cnt]'>";

    echo get_item_name($it_name,1,0);
    echo "</td>";

    // 수량, 입력(수량)
    echo "<td><input type=text id='ct_qty_{$i}' name='ct_qty[{$i}]' value='$row[ct_qty]' size=4 maxlength=6 class=ed style='text-align:right;padding-right:3px;' autocomplete='off'></td>";

    echo "<td><span class='amount'>" . number_format($row[ct_amount]) . " <span>원</span></span></td>";
    echo "<td><span class='amount'>" . number_format($sell_amount) . " <span>원</span></span></td>";
    echo "<td><span class='item_point'>" . number_format($point) . "</td>";

	# 주문날짜를 구한다
	$od_time = sql_fetch("
		select left(od_time,10) as od_time from ".$g4['yc4_order_table']." where on_uid = '".$s_on_uid."'
	");
	$od_time = str_replace('-','',$od_time['od_time']);

	if($od_time >= '20141023'){
		$ov_evt = sql_fetch("
			select ov_qty,ev_qty from yc4_over_stock_item where it_id = '".$row['it_id']."' and use_yn = 'y'
		");
	}else{
		$ov_evt = false;
	}
	if($ov_evt){
		$ov_evt_flag = true;
		if($row['ct_amount'] == '0'){ // 서비스상품
			$comment = $ov_evt['ov_qty'].'+'.$ov_evt['ev_qty'].' 이벤트 사은품';
		}else{ // 주문상품
			$comment = $ov_evt['ov_qty'].'+'.$ov_evt['ev_qty'].' 이벤트 상품';
		}
	}else{
		$comment = '';
	}

	if($_MASTER_CARD_EVENT){
		$it_id_chk = sql_fetch("select count(*) as cnt from master_card_no_item where it_id = '".$row['it_id']."'");

		$it_maker_chk = sql_fetch("select count(*) as cnt from master_card_no_item where it_maker = '".mysql_real_escape_string($row['it_maker'])."'");

		if($it_id_chk['cnt']>0 || $it_maker_chk['cnt']>0){
			$master_card_comment = ($comment ? "<br/>":"")."마스타 카드 프로모션 제외상품";
		}else{
			$master_card_comment = '';
		}
	}

	echo "<td>".$comment.$master_card_comment."</td>";




    echo "<td align=center><a href='./cartupdate.php?w=d&ct_id=$row[ct_id]'><img src='$g4[shop_img_path]/btn_del.gif' border='0' align=absmiddle alt='삭제'></a></td>";

    echo "</tr>";
    // echo "<tr><td colspan='$colspan' class=dotline></td></tr>";
	// echo "<tr><td colspan='$colspan' height=1 bgcolor=#c7c7c7></td></tr>";

	$tot_point       += $point;
	$tot_sell_amount += $sell_amount;

    if ($row[ct_status] == '취소' || $row[ct_status] == '반품' || $row[ct_status] == '품절') {
        $tot_cancel_amount += $sell_amount;
    }

	# 선결제 포인트가 장바구니에 있을경우 포인트 결제 비활성화 처리 2014-05-15 홍민기#
	if(!$no_point && $row['ca_id'] == 'u0'){
		$no_point = true;
	}

	# 마스터 카드 프로모션 #
	if(!in_array($row['it_maker'],$master_card_no_brand) && !in_array($row['it_id'],$master_cart_no_it_id)){
		$master_cart_price += $sell_amount;
	}
}
if($oneday_delete){
	echo "<script>
		location.reload();
	</script>";
}

// 김선용 201306 : 우리 에스크로관련 상품명 넘길때 잘라서 넘겨준다.(상품명이 너무 길다)
if ($goods_count){
    $goods .= " 외 {$goods_count}건";
	$goods_cut .= " 외 {$goods_count}건";
}

if ($i == 0) {
    echo "<tr>";
    echo "<td colspan='$colspan' align=center height=100><span class=textpoint>장바구니가 비어 있습니다.</span></td>";
    echo "</tr>";
} else {
    // 배송비가 넘어왔다면
    if ($_POST[od_send_cost]) {
        $send_cost = (int)$_POST[od_send_cost];
    } else {
        // 배송비 계산
        if ($default[de_send_cost_case] == "없음")
            $send_cost = 0;
        else {
            // 배송비 상한 : 여러단계의 배송비 적용 가능
            $send_cost_limit = explode(";", $default[de_send_cost_limit]);
            $send_cost_list  = explode(";", $default[de_send_cost_list]);
            $send_cost = 0;
            for ($k=0; $k<count($send_cost_limit); $k++) {
                // 총판매금액이 배송비 상한가 보다 작다면
                if ($tot_sell_amount < $send_cost_limit[$k]) {
                    $send_cost = $send_cost_list[$k];
                    break;
                }
            }
        }

        // 이미 주문된 내역을 보여주는것이므로 배송비를 주문서에서 얻는다.
        $sql = "select od_send_cost from $g4[yc4_order_table] where od_id = '$od_id' ";
        $row = sql_fetch($sql);
        if ($row[od_send_cost] > 0)
            $send_cost = $row[od_send_cost];
    }

	// 김선용 201208 : lv 3,4 무료배송 설정시 무료배송 처리
	if($s_page != 'orderinquiryview.php'){
		if(in_array($member['mb_level'], array('3', '4'))){
			$post_arr = explode("|", $default['de_mb_level_free_post']);
			for($k=3; $k<5; $k++){
				if(array_shift(explode('=>', $post_arr[($k-3)])) == $member['mb_level']){
					if(array_pop(explode('=>', $post_arr[($k-3)]))){
						$send_cost = 0;
						break;
					}
				}
			}
		}
	}

    // 배송비가 0 보다 크다면 (있다면)
    if ($send_cost > 0)
    {
		$beasong_flag = true;
        //echo "<tr><td colspan='$colspan' height=1 bgcolor=#E7E9E9></td></tr>";
        echo "<tr class='tatal_Area'>";
		echo "<td colspan='5' rowspan='3' style='width:735px;'>".($ov_evt_flag  ? "※ +1 이벤트 사은품은 주문서 작성 완료 후 주문내역에 추가됩니다.":"")."</td>";
        echo "<td style='text-align:right;'>배송비</td>";
        echo "<td colspan=3 style='text-align:right;padding-right:10px;'>" . number_format($send_cost) . "</td>";
        // echo "<td>&nbsp;</td>";

        echo "  </tr>   ";
    }

	if($beasong_flag){
		$colspan2 = 1;
	}else{
		$colspan2 = $colspan - 3;
	}


    // 총계 = 주문상품금액합계 + 배송비
    $tot_amount = $tot_sell_amount + $send_cost;

    // echo "<tr><td colspan='$colspan' height=1 bgcolor=#c7c7c7></td></tr>";
    echo "<tr class='tatal_Area'>";
    echo "<td colspan='".$colspan2."' style='text-align:right;'><strong>총계</strong></td>";
    echo "<td colspan=3 style='text-align:right;padding-right:10px;'><strong>" . number_format($tot_amount) . "</strong></td>";
    // echo "<td align=right class=font11_orange>" . number_format($tot_point) . "&nbsp;</td>";

    echo "</tr>";
	echo "<!--<tr><td colspan='".$colspan."' height=2 bgcolor=#94D7E7></td></tr>-->
		<!-- 적립포인트 추가 -->
		<tr class='tatal_Area'>
			<td colspan='".$colspan2."'style='text-align:right;'>적립포인트</td>
			<td colspan=3 style='text-align:right;padding-right:10px;'>".number_format($tot_point)."</td>
		</tr>";


    echo "<input type=hidden name=w value=''>";
    echo "<input type=hidden name=records value='$i'>";
}
?>
<!--<tr><td height="2" colspan=10 bgcolor="#fd7c00"></td></tr>-->
<?
if($weight){
	echo "
	<tr>
		<td colspan='".$colspan."' style='padding:10px 0px;'>
			<strong>
			대한민국 관세법에 의거, 비가공 곡물 제품은 5KG 이상 주문시 과세 대상에 포함되오니, 주문에 유의하시기 바랍니다.
			<br/>
			<br/>
	";

	if(is_array($weight)){
		foreach($weight as $key => $val){
			switch($key){
				case 'q' : $type_name = '퀴노아'; break;
				case 'e' : $type_name = '그외'; break;
			}
			echo "<br/>";
			echo $type_name . " : " . $weight_kg[$key];
			if($weight_over[$key]) echo " -> <b style='color:#ff0000'>5KG을 초과합니다.</b>";


		}
		echo "<br/>";

	}


	if($weight_over_result){
		echo "
			<span style='color:#ff0000;'>현재 주문하시려는 비가공 곡물 제품중 5KG을 초과하는 품목이 있습니다.</span>
		";
	}
	echo "
		</strong>
		</td>
	</tr>

	";
}
?>

</table>

<div class='button_place'>
    <?
    if ($i == 0) {
        echo "<a href='$g4[path]'><img src='{$g4['path']}/images/common/common_btn_shopping.gif' border='0'></a>";
    } else {
        echo "
		<input type=hidden name=url value='./orderform.php'>
        <span class='place_left'>
		<a href=\"javascript:form_check('allupdate')\"><img src='{$g4['path']}/images/common/common_btn_cart_quan.gif' hspace='5' border='0'></a>
        <a href=\"javascript:form_check('alldelete');\"><img src='{$g4['path']}/images/common/common_btn_cart_out.gif'  hspace='5' border='0'></a>
		</span>
		<span class='place_right'>
		<a href=\"javascript:form_check('buy')\"><img src='{$g4['path']}/images/common/common_btn_buy.gif' hspace=5 border=0></a>
        <a href='./list.php?ca_id=$continue_ca_id'><img src='{$g4['path']}/images/common/common_btn_shopping.gif' hspace='5' border='0'></a>
		</span>";
    }
    ?>
</div>

	<?	/* 구매금액별 이벤트 표시 2014-07-03 홍민기 */
			/*
			print_r($gift_event_brand); // 브랜드별 총 상품 가격
			print_r($gift_event_cate); // 카테고리별 총 상품 가격
			echo $tot_amount; // 총 주문 가격
			*/

			# 브랜드 이벤트 처리 시작 #

			if(is_array($gift_event_brand)){
				$qry_where_template = "
					a.it_maker = '###_brand_###'
					and
					st_dt <= '".date('Ymd')."'
					and
					en_dt >= '".date('Ymd')."'
					and
					b.use = 'Y'
				";
				$qry_from = "
					yc4_free_gift_event a
					left join
					yc4_free_gift_event_item b on a.bid = b.bid
					left join
					yc4_item c on b.it_id = c.it_id
				";
				foreach($gift_event_brand as $brand => $amt){
					# 현재 진행중인 이벤트에 해당하는지 체크 #
					$brand = mysql_escape_string($brand);
					$where_qry = str_replace('###_brand_###',$brand,$qry_where_template);
					$brand_chk_qry = sql_fetch("
						select
							count(*) as cnt
						from
							".$qry_from."
						where
							".$where_qry."
					");

					if($brand_chk_qry['cnt'] == 0 ) continue; // 이벤트가 진행중이지 않은 브랜드는 skip

					$brand_event_qry = sql_query("
						select
							a.bid,a.name,a.comment,a.priod_view,a.st_dt,a.en_dt,
							b.it_id,od_amount,b.show_amount,
							c.it_name,c.it_cust_amount,c.it_amount,c.it_stock_qty
						from
							".$qry_from."
						where
							".$where_qry."
						order by a.bid,a.event_type,b.od_amount
					");

					$event_name = $event_comment = $priod_view = $event_st_dt = $event_en_dt = '';
					while($result = sql_fetch_array($brand_event_qry)){
						if($result['it_name']){
							$result['it_name'] = get_item_name($result['it_name']);
						}

						$bid[$result['bid']] = $result['bid'];
						$brand_event_gift[] = $result;
						if($event_name == '') $event_name = $result['name'];
						if($event_comment == '') $event_comment = $result['comment'];
						if($priod_view == '') $priod_view = $result['priod_view'];
						if($event_st_dt == '') $event_st_dt = $result['st_dt'];
						if($event_en_dt == '') $event_en_dt = $result['en_dt'];

					}
					switch($priod_view){
						case 'Y' : $event_priod = substr($event_st_dt,0,4).'-'.substr($event_st_dt,4,2).'-'.substr($event_st_dt,6,2).' ~ '.substr($event_en_dt,0,4).'-'.substr($event_en_dt,4,2).'-'.substr($event_en_dt,6,2); break;
						case 'N' : $event_priod = ''; break;
						case 'C' : $event_priod = '별도공지'; break;
					}



					if($brand_event_gift){
						$gift_event_item_view .= "
							<div class='event_A'>
							<div class='gift_pro'>
							<div class='gift_pro_title'>
								<div style='display:inline'><strong>".$event_name."</strong></div>
								<div class='gift_pro_title_data'>".$event_priod."</div>
							</div>

							<div class='gift_event_comment'>
									".$event_comment."
									<strong>현재 ".$brand." 제품 금액 : ".number_format($amt)."원</strong>
							</div>

						";


						for($ii=0; $ii<count($brand_event_gift); $ii++){
							if($brand_event_gift[$ii]['od_amount'] != $brand_event_gift[$ii-1]['od_amount']){

								$gift_event_item_view .= "
									<div class='gift_pro_middletitle'>".number_format($brand_event_gift[$ii]['od_amount'])."원 이상 구매시 선택가능 사은품</div>
										<div class='gift_com_pro_wrap'>
								";

							}
							$gift_event_item_view .= "
								<div class='gift_com_pro'>
									<div class='gift_img'>".get_it_image($brand_event_gift[$ii]['it_id'].'_s',74,74,null,null,null,null)."</div>
									<div class='gfit_title'>".$brand_event_gift[$ii]['it_name'].($brand_event_gift[$ii]['it_stock_qty']<1 ? "<img src='".$g4['shop_path']."/img/icon_pumjul.gif'/>":"")."</div>
									".(($brand_event_gift[$ii]['show_amount'] == 'y') ? "
										<div class='gift_price'>".display_amount(get_amount($brand_event_gift[$ii]))."</div>
									":"")."

								</div>
							";
							if($brand_event_gift[$ii]['od_amount'] != $brand_event_gift[$ii+1]['od_amount']){
								$gift_event_item_view .= "</div>";
							}
							if( $brand_event_gift[$ii]['bid'] != $brand_event_gift[$ii+1]['bid'] ){
								$gift_event_item_view .= "</div>";
							}
						}

						$gift_event_item_view .= "</div><!-- gift_event_line End -->";
					}

				}
			}
			# 브랜드 이벤트 처리 끝 #

			# 카테고리 이벤트 시작 #
			if(is_array($gift_event_cate)){
				$qry_where_template = "
					a.ca_id = '###_cate_###'
					and
					st_dt <= '".date('Ymd')."'
					and
					en_dt >= '".date('Ymd')."'
					and
					b.use = 'Y'
				";
				$qry_from = "
					yc4_free_gift_event a
					left join
					yc4_free_gift_event_item b on a.bid = b.bid
					left join
					yc4_item c on b.it_id = c.it_id
					left join
					yc4_category d on a.ca_id = d.ca_id
				";
				foreach($gift_event_cate as $cate => $amt){
					$where_qry = str_replace('###_cate_###',$cate,$qry_where_template);

					$cate_chk_qry = sql_fetch("
						select
							count(*) as cnt
						from
							".$qry_from."
						where
							".$where_qry."
					");

					if($cate_chk_qry['cnt'] == 0 ) continue; // 이벤트가 진행중이지 않은 카테고리는 skip
					$cate_event_qry = sql_query("
						select
							a.bid,a.name,a.comment,a.priod_view,a.st_dt,a.en_dt,
							b.it_id,od_amount,b.show_amount,
							c.it_name,c.it_cust_amount,c.it_amount,c.it_stock_qty,
							d.ca_name
						from
							".$qry_from."
						where
							".$where_qry."
						order by a.bid,a.event_type,b.od_amount
					");
					$event_name = $event_comment = $priod_view = $event_st_dt = $event_en_dt = '';
					while($result = sql_fetch_array($cate_event_qry)){
						if($result['it_name']){
							$result['it_name'] = get_item_name($result['it_name']);
						}
						$bid[$result['bid']] = $result['bid'];
						$cate_event_gift[] = $result;
						if($event_name == '') $event_name = $result['name'];
						if($event_comment == '') $event_comment = $result['comment'];
						if($event_ca_name == '') $event_ca_name = $result['ca_name'];
						if($priod_view == '') $priod_view = $result['priod_view'];
						if($event_st_dt == '') $event_st_dt = $result['st_dt'];
						if($event_en_dt == '') $event_en_dt = $result['en_dt'];
					}
					switch($priod_view){
						case 'Y' : $event_priod = substr($event_st_dt,0,4).'-'.substr($event_st_dt,4,2).'-'.substr($event_st_dt,6,2).'~'.substr($event_en_dt,0,4).'-'.substr($event_en_dt,4,2).'-'.substr($event_en_dt,6,2); break;
						case 'N' : $event_priod = ''; break;
						case 'C' : $event_priod = '별도공지'; break;
					}
					if($cate_event_gift){
						$gift_event_item_view .= "
							<div class='gift_pro'>
							<div class='gift_pro_title'>
								<div style='display:inline;'><strong>".$event_name."</strong></div>
								<div class='gift_pro_title_data'>".$event_priod."</div>
							</div>

							<div class='gift_event_comment'>
									".$event_comment."
									<strong>현재 ".$event_ca_name." 제품 금액 : ".number_format($amt)."원</strong>
								</div>

						";

						for($ii=0; $ii<count($cate_event_gift); $ii++){
							if($cate_event_gift[$ii]['od_amount'] != $cate_event_gift[$ii-1]['od_amount']){

								$gift_event_item_view .= "
									<div class='gift_pro_middletitle'>".number_format($cate_event_gift[$ii]['od_amount'])."원 이상 구매시 선택가능 사은품</div>
										<div class='gift_com_pro_wrap'>
								";

							}
							$gift_event_item_view .= "
								<div class='gift_com_pro'>
									<div class='gift_img'>".get_it_image($cate_event_gift[$ii]['it_id'].'_s',74,74,null,null,null,null)."</div>
									<div class='gfit_title'>".$cate_event_gift[$ii]['it_name'].($cate_event_gift[$ii]['it_stock_qty']<1 ? "<img src='".$g4['shop_path']."/img/icon_pumjul.gif'/>":"")."</div>
									".(($cate_event_gift[$ii]['show_amount'] == 'y') ? "
										<div class='gift_price'>".display_amount(get_amount($cate_event_gift[$ii]))."</div>
									":"")."

								</div>
							";
							if($cate_event_gift[$ii]['od_amount'] != $cate_event_gift[$ii+1]['od_amount']){
								$gift_event_item_view .= "</div>";
							}
							if( $cate_event_gift[$ii]['bid'] != $cate_event_gift[$ii+1]['bid'] ){
								$gift_event_item_view .= "</div>";
							}
						}
						$gift_event_item_view .= "</div><!-- gift_event_line End -->";
					}
				}
			}
			# 카테고리 이벤트 끝 #



			# 구매금액별 이벤트 시작 (전상품) #
			$gift_item_event_qry = sql_query("
				select
					a.bid,a.name,a.comment,a.priod_view,a.st_dt,a.en_dt,
					b.it_id,od_amount,b.show_amount,
					c.it_name,c.it_cust_amount,c.it_amount,c.it_stock_qty
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
					a.event_type = 'A'
					and
					b.it_id is not null
					and
					b.use = 'Y'
				order by a.bid,a.event_type,b.od_amount
			");

			$event_name = $event_comment = $priod_view = $event_st_dt = $event_en_dt = '';
			while($result = sql_fetch_array($gift_item_event_qry)){
				if($result['it_name']){
					$result['it_name'] = get_item_name($result['it_name']);
				}
				$bid[$result['bid']] = $result['bid'];
				$gift_item_event_gift[] = $result;
				if($event_name == '') $event_name = $result['name'];
				if($event_comment == '') $event_comment = $result['comment'];
				if($priod_view == '') $priod_view = $result['priod_view'];
				if($event_st_dt == '') $event_st_dt = $result['st_dt'];
				if($event_en_dt == '') $event_en_dt = $result['en_dt'];

			}

			if($gift_item_event_gift){

				switch($priod_view){
					case 'Y' : $event_priod = substr($event_st_dt,0,4).'-'.substr($event_st_dt,4,2).'-'.substr($event_st_dt,6,2).' ~ '.substr($event_en_dt,0,4).'-'.substr($event_en_dt,4,2).'-'.substr($event_en_dt,6,2); break;
					case 'N' : $event_priod = ''; break;
					case 'C' : $event_priod = '별도공지'; break;
				}


				$gift_event_item_view .= "
					<div class='gift_pro'>
					<div class='gift_pro_title'>
						<div style='display:inline;'><strong>".$event_name."</strong></div>
						<div class='gift_pro_title_data'>".$event_priod."</div>
					</div>

					<div class='gift_event_comment'>
							".$event_comment."
							<strong>현재 총 상품 구매 금액 : ".number_format($tot_amount)."원</strong>
						</div>

				";

				for($ii=0; $ii<count($gift_item_event_gift); $ii++){
					if($gift_item_event_gift[$ii]['od_amount'] != $gift_item_event_gift[$ii-1]['od_amount']){

						$gift_event_item_view .= "
							<div class='gift_pro_middletitle'>".number_format($gift_item_event_gift[$ii]['od_amount'])."원 이상 구매시 선택가능 사은품</div>
								<div class='gift_com_pro_wrap'>
						";

					}
					$gift_event_item_view .= "
						<div class='gift_com_pro'>
							<div class='gift_img'>".get_it_image($gift_item_event_gift[$ii]['it_id'].'_s',74,74,null,null,null,null)."</div>
							<div class='gfit_title'>".$gift_item_event_gift[$ii]['it_name'].($gift_item_event_gift[$ii]['it_stock_qty']<1 ? "<img src='".$g4['shop_path']."/img/icon_pumjul.gif'/>":"")."</div>
							".(($gift_item_event_gift[$ii]['show_amount'] == 'y') ? "
								<div class='gift_price'>".((get_amount($gift_item_event_gift[$ii])<=100)? '': display_amount(get_amount($gift_item_event_gift[$ii])))."</div>
							":"")."

						</div>
					";
				}
				if($gift_item_event_gift[$ii]['od_amount'] != $gift_item_event_gift[$ii+1]['od_amount']){
					$gift_event_item_view .= "</div>";
				}
				if( $gift_item_event_gift[$ii]['bid'] != $gift_item_event_gift[$ii+1]['bid'] ){
					$gift_event_item_view .= "</div>";
				}

				$gift_event_item_view .= "</div><!-- .gift_pro_middletitle End -->";
			}

			# 구매금액별 이벤트 끝 (전상품) #


				if($gift_event_item_view){
					?>

					<script type="text/javascript">
					/*
					$(document).ready(function(){
							$('.gift_pro').width('50%');
							if( $('.gift_pro').length%2 == 1 ){
								$('.gift_pro:last').width('100%');
							}
							for(var i=0; i<$('.gift_com_pro_wrap').length; i++){


								if( $('.gift_com_pro_wrap:eq('+i+') .gift_com_pro').length / 2 >= 1){

									$('.gift_com_pro_wrap:eq('+i+') .gift_com_pro').css( 'width', ($('.gift_com_pro_wrap:eq('+i+')').width()/2)-2 );
						//			$('.gift_com_pro_wrap:eq('+i+') .gift_com_pro')
								}else{
									$('.gift_com_pro_wrap:eq('+i+') .gift_com_pro').css('width',$('.gift_com_pro_wrap:eq('+i+')').width());
									$('.gift_com_pro_wrap:eq('+i+') .gfit_title').css('width',266);
								}
							}

							for(var i=0; i<$('.gift_com_pro').length; i++){

								$('.gift_com_pro:eq('+i+') .gfit_title').width( Number($('.gift_com_pro:eq('+i+')').width()) - Number($('.gift_com_pro:eq('+i+') .gift_img').width()) -15 );
							}
						});
						*/
					</script>
					<?
					echo "
						<div class='gift_wrap'>
							<div class='gift_title' style='height:auto;'>
							<p class='gift_info'><img src='http://115.68.20.84/main/detail_banner_nordic.jpg'/></p>
							<!--※주문서 작성 완료 후 본 이벤트 상품을 선택할 수 있습니다.-->
							</div>
							".$gift_event_item_view."
						</div>
					";
				}



		?>







    <script type='text/javascript'>
    <? if ($i != 0) { ?>
	function check_order_price(){
	}

        function form_check(act) {
            var f = document.frmcartlist;
            var cnt = f.records.value;

            if (act == "buy")
            {
				<?if($weight_over_result){ # 비가공 곡물 처리 2014-07-18 홍민기?>

				if(!confirm('대한민국 관세법에 의거,\n비가공 곡물 제품은 주문건당 5KG 이상일 경우\n과세 대상에 포함되오니, 주문에 유의하시기 바랍니다.\n\n주문하시는 비가공 곡물중 5KG을 초과하는 품목이 존재합니다.\n\n주문하시겠습니까?')){
					return false;
				}

				<?}?>
            	f.w.value = act;

                <?
                if (get_session('ss_mb_id')) // 회원
                {
					// 김선용 2014.03 : kcp 작업중
					if(check_test_id())
	            	    echo "f.action = './orderform-dev.php';";
					else
	            	    echo "f.action = './orderform.php';";

                    echo "f.submit();";
                }
                else
                    echo "document.location.href = '$g4[bbs_path]/login.php?url=".urlencode("$g4[shop_path]/orderform.php")."';";
                ?>
            }
            else if (act == "alldelete")
            {
            	f.w.value = act;
            	f.action = "<?="./cartupdate.php"?>";
            	f.submit();
            }
            else if (act == "allupdate")
            {
                for (i=0; i<cnt; i++)
                {
                    //if (f.elements("ct_qty[" + i + "]").value == "")
                    if (document.getElementById('ct_qty_'+i).value == '')
                    {
                        alert("수량을 입력해 주십시오.");
                        //f.elements("ct_qty[" + i + "]").focus();
                        document.getElementById('ct_qty_'+i).focus();
                        return;
                    }
                    //else if (isNaN(f.elements("ct_qty[" + i + "]").value))
                    else if (isNaN(document.getElementById('ct_qty_'+i).value))
                    {
                        alert("수량을 숫자로 입력해 주십시오.");
                        //f.elements("ct_qty[" + i + "]").focus();
                        document.getElementById('ct_qty_'+i).focus();
                        return;
                    }
                    //else if (f.elements("ct_qty[" + i + "]").value < 1)
                    else if (document.getElementById('ct_qty_'+i).value < 1)
                    {
                        alert("수량은 1 이상 입력해 주십시오.");
                        //f.elements("ct_qty[" + i + "]").focus();
                        document.getElementById('ct_qty_'+i).focus();
                        return;
                    }
                }
            	f.w.value = act;
            	f.action = "./cartupdate.php";
            	f.submit();
            }

            return true;
        }
    <? } ?>
    </script>




<div class='alert_notice'>
	<p><strong>상품 주문하기</strong> 주문서를 작성하시려면 '주문하기' 버튼을 누르세요.</p>
	<p><strong>상품 수량변경</strong> 주문수량을 변경하시려면 원하시는 수량을 입력하신 후 '수량변경' 버튼을 누르세요.</p>
	<p><strong>상품 삭제하기</strong> 모든 주문내용을 삭제하시려면 '삭제하기' 버튼을 누르세요.</p>
	<p><strong>쇼핑 계속하기</strong> 쇼핑하시던 페이지로 돌아가시려면 '쇼핑 계속하기' 버튼을 누르세요.</p>
</div>


<?php if($_MASTER_CARD_EVENT && !in_array($s_page,array('orderform.php','orderinquiryview.php') ) ) {?>
<div><img src="http://115.68.20.84/event/master_card/master-card_event_cart.jpg"/></div>
<?php }?>

</form>
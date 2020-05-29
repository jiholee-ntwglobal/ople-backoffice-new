<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/*
    $s_page 는 cart.php 일때 수량의 수정, 물품의 삭제를 위한 변수이다.
               orderinquiryview.php 일때 배송상태등을 나타내는 변수이다.

    $s_on_uid 는 유일한 키인데 orderupdate.php 에서 ck_on_uid 를 죽이면서
    ck_tmp_on_uid 에 복사본을 넣어준다. ck_tmp_on_uid 는 orderconfirm.php 에서만 사용한다.
*/

if ($s_page == 'cart.php' || $s_page == 'orderinquiryview.php')
    $colspan = 7;
else
    $colspan = 6;
?>

<form name=frmcartlist method=post style="padding:0px;">

<?if($s_page == 'cart.php'){?>
<table width=98% cellpadding=0 cellspacing=0 align=center>
<tr><td><img src="<?=$g4['path']?>/images/menu/menu01_list_title.gif" width="747" height="24"></td></tr>
</table>
<?}else if($s_page != 'orderinquiryview.php'){?>
<table width=98% cellpadding=0 cellspacing=0 align=center>
<tr><td><img src="<?=$g4['path']?>/images/category/category_list_title.gif" width="747" height="24"></td></tr>
</table>
<?}?>

<table width=95% cellpadding=0 cellspacing=0 align=center border=0>
<?if($s_page == 'orderinquiryview.php'){?>
<colgroup width=60>
<colgroup width=''>
<colgroup width=80>
<colgroup width=80>
<colgroup width=80>
<colgroup width=80>
<colgroup width=60>
<tr><td colspan='<?=$colspan?>' height=2 class=c1></td></tr>
<tr align=center height=28 class=c2>
    <td colspan=2>상품명</td>
    <td>수량</td>
    <td>판매가</td>
    <td>소계</td>
    <td>포인트</td>
	<td>상태</td>
</tr>

<?}else if($s_page == 'cart.php'){?>
<colgroup width=65>
<colgroup width=''>
<colgroup width=65>
<colgroup width=85>
<colgroup width=80>
<colgroup width=60>
<colgroup width=50>

<?}else{?>
<colgroup width=60>
<colgroup width=''>
<colgroup width=50>
<colgroup width=75>
<colgroup width=80>
<colgroup width=70>
<?}?>

<?
/*
if ($s_page == 'cart.php')
    echo '<td>삭제</td>';
else if ($s_page == 'orderinquiryview.php')
    echo '<td>상태</td>';
*/
?>
<!--
<tr><td colspan='<?=$colspan?>' height=1 class=c1></td></tr>
-->
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
                b.ca_id,
				b.it_order_onetime_limit_cnt,
				b.it_bottle_count /* // 김선용 201210 : 병수량 */
           from $g4[yc4_cart_table] a,
                $g4[yc4_item_table] b
          where a.on_uid = '$s_on_uid'
            and a.it_id  = b.it_id
          order by a.ct_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	# 원데이 상품 체크 #
	$sql = "
		select it_id,l_it_id from yc4_oneday_sale_item where it_id = '".$row['it_id']."'
	";
	$oneday_data = sql_fetch($sql);
	if($oneday_data){
		//$oneday_time_chk = sql_fetch($a="select it_id,price,end_flag from yc4_oneday_sale_item where it_id = '".$row['it_id']."' and ( st_dt < '".date('Ymd')."' or en_dt > '".date('Ymd')."' ) and real_qty>order_cnt");

		$oneday_time_chk = sql_fetch($a="select it_id,price,end_flag from yc4_oneday_sale_item where it_id = '".$row['it_id']."' and 
			not'".date('Ymd')."' between st_dt and en_dt  and real_qty>order_cnt
			
		");

		$it_amount = sql_fetch("select it_id,it_amount,it_tel_inq,it_amount3,it_amount2 from ".$g4['yc4_item_table']." where it_id = '".$row['it_id']."'");
		$it_amount = get_amount($it_amount);

		if($oneday_time_chk && $row['ct_status'] == '쇼핑'){ // 기간이 지났는데 결제가 안된 장바구니에 있는 원데이 상품은 삭제한다.
			if(
				$oneday_time_chk['end_flag'] == 'N'  // 종료 후 종료로 남은 상품은 무조건 삭제
				||
				(
					# 이벤트 가격 입력된 상품 중 장바구니 금액과 이벤트 금액이 같을경우 삭제
					$oneday_time_chk['price'] > 0
					&&
					$oneday_time_chk['price'] == $row['ct_amount']
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
        $continue_ca_id = $row[ca_id];
    }

    if ($s_page == "cart.php" || $s_page == "orderinquiryview.php") { // 링크를 붙이고
        $a1 = "<a href='./item.php?it_id=$row[it_id]'>";
        $a2 = "</a>";
        $image       = get_it_image($it_id2."_s", 50, 50, $row[it_id]);
    } else { // 붙이지 않고
        $a1 = "";
        $a2 = "";
        $image = get_it_image($it_id2."_s", 50, 50,null,null,null,null);
    }

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


    if ($i > 0)
        //echo "<tr><td colspan='$colspan' height=1 bgcolor=#E7E9E9></td></tr>";

	echo "<tr>";
    echo "<td align=left style='padding:5px;'>$image</td><td>";
    echo "<input type=hidden name='ct_id[$i]'    value='$row[ct_id]'>";
    echo "<input type=hidden name='it_id[$i]'    value='$row[it_id]'>";
    echo "<input type=hidden name='ap_id[$i]'    value='$row[ap_id]'>";
    echo "<input type=hidden name='bi_id[$i]'    value='$row[bi_id]'>";
    echo "<input type=hidden name='it_name[$i]'  value='".get_text($row[it_name])."'>";
	// 김선용 201208 :
    echo "<input type=hidden name='it_order_onetime_limit_cnt[$i]' value='$row[it_order_onetime_limit_cnt]'>";

    echo $it_name;
    echo "</td>";

    // 수량, 입력(수량)
    if ($s_page == "cart.php")
        echo "<td align=center><input type=text id='ct_qty_{$i}' name='ct_qty[{$i}]' value='$row[ct_qty]' size=4 maxlength=6 class=ed style='text-align:right;' autocomplete='off'></td>";
	else
	    echo "<td align=center>$row[ct_qty]</td>";

    echo "<td align=right>" . number_format($row[ct_amount]) . "</td>";
    echo "<td align=right>" . number_format($sell_amount) . "</td>";
    echo "<td align=right>" . number_format($point) . "&nbsp;</td>";

    if ($s_page == "cart.php")
        echo "<td align=center><a href='./cartupdate.php?w=d&ct_id=$row[ct_id]'><img src='$g4[shop_img_path]/btn_del.gif' border='0' align=absmiddle alt='삭제'></a></td>";
  	else if ($s_page == "orderinquiryview.php")
    {
        switch($row[ct_status])
        {
            case '주문' : $icon = "<img src='$g4[shop_img_path]/status01.gif'>"; break;
            case '준비' : $icon = "<img src='$g4[shop_img_path]/status02.gif'>"; break;
            case '배송' : $icon = "<img src='$g4[shop_img_path]/status03.gif'>"; break;
            case '완료' : $icon = "<img src='$g4[shop_img_path]/status04.gif'>"; break;
            default     : $icon = $row[ct_status]; break;
        }
  	    echo "<td align=center>$icon</td>";
    }

    echo "</tr>";
    //echo "<tr><td colspan='$colspan' class=dotline></td></tr>";
	echo "<tr><td colspan='$colspan' height=1 bgcolor=#c7c7c7></td></tr>";

	$tot_point       += $point;
	$tot_sell_amount += $sell_amount;

    if ($row[ct_status] == '취소' || $row[ct_status] == '반품' || $row[ct_status] == '품절') {
        $tot_cancel_amount += $sell_amount;
    }

	# 선결제 포인트가 장바구니에 있을경우 포인트 결제 비활성화 처리 2014-05-15 홍민기#
	if(!$no_point && $row['ca_id'] == 'u0'){
		$no_point = true;
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
        echo "<tr><td colspan='$colspan' height=1 bgcolor=#E7E9E9></td></tr>";
        echo "<tr>";
        echo "<td height=28 colspan=4 align=right>배송비 : </td>";
        echo "<td align=right>" . number_format($send_cost) . "</td>";
        echo "<td>&nbsp;</td>";
        if ($s_page == "cart.php" || $s_page == "orderinquiryview.php")
           echo "<td>&nbsp;</td>";
        echo "  </tr>   ";
    }

	// 김선용 201209 : 추천인 할인표기
	if($s_page == 'orderinquiryview.php' && $od['od_recommend_off_sale']) // 주문조회시
	{
		echo "<tr><td colspan='$colspan' height=1 bgcolor=#E7E9E9></td></tr>";
		echo "<tr>";
		echo "<td height=28 colspan=4 align=right><span style='color:blue;'>※ 추천인등록 시스템에 의한 할인적용</span> : </td>";
		echo "<td align=right>-".nf($od['od_recommend_off_sale'])."원</td>";
		echo "<td>&nbsp;</td>";
		echo "<td>&nbsp;</td>";
		echo "</tr>";
	}
	else if($recommend_off_sale && $s_page != 'orderinquiryview.php') // 주문시
	{
		$order_save_amount = $default['de_recom_off_amount']; // 주문서 저장용
		$tot_sell_amount -= $default['de_recom_off_amount'];
		echo "<tr><td colspan='$colspan' height=1 bgcolor=#E7E9E9></td></tr>";
		echo "<tr>";
		echo "<td height=28 colspan=4 align=right><span style='color:blue;'>※ 추천인등록 시스템에 의한 할인적용</span> : </td>";
		echo "<td align=right>-".nf($default['de_recom_off_amount'])."원</td>";
		echo "<td>&nbsp;</td>";
		if ($s_page == "cart.php")
		   echo "<td>&nbsp;</td>";
		echo "  </tr>   ";
	}

    // 총계 = 주문상품금액합계 + 배송비
    $tot_amount = $tot_sell_amount + $send_cost;

    echo "<tr><td colspan='$colspan' height=1 bgcolor=#c7c7c7></td></tr>";
    echo "<tr align=center height=28>";
    echo "<td colspan=4 align=right><b>총계 : </b></td>";
    echo "<td align=right><span class=font11_orange><b>" . number_format($tot_amount) . "</b></span></td>";
    echo "<td align=right class=font11_orange>" . number_format($tot_point) . "&nbsp;</td>";
    if ($s_page == "cart.php" || $s_page == "orderinquiryview.php")
        echo "<td> &nbsp;</td>";
    echo "</tr>";

    echo "<input type=hidden name=w value=''>";
    echo "<input type=hidden name=records value='$i'>";
}
?>
<tr><td height="2" colspan=10 bgcolor="#fd7c00"></td></tr>
<?
if($weight){
	echo "
	<tr>
		<td colspan='".$colspan."' style='padding:10px 0px;'>
			<b>
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
		</b></td>
	</tr>

	";
}
?>
<!--<tr><td colspan='<?=$colspan?>' height=2 bgcolor=#94D7E7></td></tr>-->
<tr>
    <td colspan='<?=$colspan?>' align=center>
    <?
    if ($s_page == "cart.php") {
        if ($i == 0) {
            echo "<br><a href='$g4[path]'><img src='$g4[shop_img_path]/btn_shopping.gif' border='0'></a>";
        } else {
            echo "
			<br><input type=hidden name=url value='./orderform.php'>
            <a href='#' onclick=\"form_check('buy'); return false;\"><img src='{$g4['path']}/images/common/common_btn_buy.gif' hspace=5 border=0></a>
            <a href=\"javascript:form_check('allupdate')\"><img src='{$g4['path']}/images//common/common_btn_cart_quan.gif' hspace='5' border='0'></a>
            <a href=\"javascript:form_check('alldelete');\"><img src='{$g4['path']}/images//common/common_btn_cart_out.gif'  hspace='5' border='0'></a>
            <a href='./list.php?ca_id=$continue_ca_id'><img src='{$g4['path']}/images//common/common_btn_shopping.gif' hspace='5' border='0'></a>";
        }
    }
    ?>
    </td>
</tr>
</form>
</table>



<? if ($s_page == "cart.php") { ?>
    <script type='text/javascript'>
    <? if ($i != 0) { ?>
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
<? } ?>

<? if ($s_page == "cart.php") { ?>
<br><br>
<table width=672 align=center cellpadding=0 cellspacing=0>
    <tr><td> </td></tr>
    <tr><td width=672 style='line-height:180%; padding-left:80px'>
        · <FONT COLOR="#FF8200">상품 주문하기</FONT> : 주문서를 작성하시려면 '주문하기' 버튼을 누르세요.<BR>
        · <FONT COLOR="#FF8200">상품 수량변경</FONT> : 주문수량을 변경하시려면 원하시는 수량을 입력하신 후 '수량변경' 버튼을 누르세요.<BR>
        · <FONT COLOR="#FF8200">상품 삭제하기</FONT> : 모든 주문내용을 삭제하시려면 '삭제하기' 버튼을 누르세요.<BR>
        · <FONT COLOR="#FF8200">쇼핑 계속하기</FONT> : 쇼핑하시던 페이지로 돌아가시려면 '쇼핑 계속하기' 버튼을 누르세요.
        </td></tr>
    <tr><td> </td></tr>
</table><br><br>
<? } ?>

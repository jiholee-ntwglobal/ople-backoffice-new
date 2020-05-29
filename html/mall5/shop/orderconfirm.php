<?php
include_once("./_common.php");
if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){
//	$_DEBUG = true;
}
// 장바구니가 비어있는가?
$tmp_on_uid = get_session('ss_temp_on_uid');
if (get_cart_count($tmp_on_uid) == 0)// 장바구니에 담기
    alert("장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.", "./cart.php");

$sql = " select * from $g4[yc4_order_table] where on_uid = '$tmp_on_uid' ";
$od = sql_fetch($sql);

if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){
//	print_r2($od);
}



$g4[title] = "주문 및 결제완료";

include_once("./_head.php");

// 상품명만들기
$sql = " select a.it_id, b.it_name
           from $g4[yc4_cart_table] a, $g4[yc4_item_table] b
          where a.it_id = b.it_id
            and a.on_uid = '$tmp_on_uid'
          order by ct_id
          limit 1 ";
$row = sql_fetch($sql);

if($row['it_name']){
	$row['it_name'] = get_item_name($row['it_name']);
}


// 김선용 2014.03 : kcp 복합 처리
if($od['card_settle_case'] == 'kcp' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '에스크로')
	$default['de_card_pg'] = 'kcp';
else if($od['card_settle_case'] == 'authorize')
	$default['de_card_pg'] = 'authorize';

?>
<!--<img src="<?=$g4[shop_img_path]?>/top_orderconfirm.gif" border=0><p>-->
<div class='PageTitle'>
<img src="<?=$g4['path']?>/images/category/category_title01_c.gif" alt="주문완료" />
</div>

<?php
$s_page = '';
$s_on_uid = $tmp_on_uid;
$od_id = $od[od_id];
include_once("./cartsub.inc.php");
?>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
    <td style="padding:20px;text-align:center;border:solid 1px #fa7c00;"><span class="font11_orange">주문번호 : <strong><?=$od[od_id]?></strong></span></td>
</tr>
</table>

<!-- 주문하시는 분 -->
<table width=100% cellpadding=0 cellspacing=0 border=0 style='margin-top:30px;'>
<tr>
    <th class="table_title">주문하시는 분</th>
</tr>
    <td>
        <table cellpadding=0 cellspacing=0 width=100% class='list_order'>
        <colgroup>
			<col width='140'>
			<col />
		</colgroup>
		<tbody>
        <tr>
            <th>이름</th>
            <td><? echo $od[od_name] ?></td>
        </tr>
        <tr>
            <th>전화번호</th>
            <td><? echo $od[od_tel] ?></td>
        </tr>
        <tr>
            <th>핸드폰</th>
            <td><? echo $od[od_hp] ?></td>
        </tr>
        <tr>
            <th>주소</th>
            <td><? echo sprintf("(%s-%s) %s %s", $od[od_zip1], $od[od_zip2], $od[od_addr1], $od[od_addr2]); ?></td>
        </tr>
        <tr>
            <th>E-mail</th>
            <td><? echo $od[od_email] ?></td>
        </tr>

        <? if ($default[de_hope_date_use]) { // 희망배송일 사용한다면 ?>
        <tr>
            <th>희망배송일</th>
            <td><?=$od[od_hope_date]?> (<?=get_yoil($od[od_hope_date])?>)</td>
        </tr>
        <? } ?>
		</tbody>
        </table>
    </td>
</tr>
</table>


<!-- 받으시는 분 -->
<table width=100% cellpadding=0 cellspacing=0 border=0 style='margin-top:30px;'>
<tr>
    <th class="table_title">받으시는 분</th>
</tr>
<tr>
    <td>

	<?if($od['od_ship']){ // 김선용 201211 : 복수배송?>
		<? echo get_fui_ship_item($tmp_on_uid, $member['mb_id'], 0, false);?>
	<?}else{ // 단수배송?>
        <table cellpadding=0 cellspacing=0 width=100% class='list_order'>
        <colgroup>
			<col width='140'>
			<col />
		</colgroup>
		<tbody>
        <tr>
            <th>이름</th>
            <td><? echo $od[od_b_name]; ?></td>
        </tr>
        <tr>
            <th>전화번호</th>
            <td><? echo $od[od_b_tel] ?></td>
        </tr>
        <tr>
            <th>핸드폰</th>
            <td><? echo $od[od_b_hp] ?>&nbsp;</td>
        </tr>
        <tr>
            <th>주소</th>
            <td><? echo sprintf("(%s-%s) %s %s", $od[od_b_zip1], $od[od_b_zip2], $od[od_b_addr1], $od[od_b_addr2]); ?></td>
        </tr>
        <tr>
            <th>배송시 요청사항<br/>(ex.부재시 경비실에 맡겨주세요.)</th>
            <td><? echo nl2br(htmlspecialchars2($od[od_memo])); ?>&nbsp;</td>
        </tr>
		</tbody>
        </table>
	<?}?>
    </td>
</tr>
</table>

<!-- 결제 정보 -->
<table width=100% cellpadding=0 cellspacing=0 border=0 style='margin-top:30px;'>
<tr>
     <th class="table_title">결제 정보</th>
</tr>
<tr>
        <td>
        <table cellpadding=0 cellspacing=0 width=100% class='list_order'>
        <colgroup>
			<col width='140'>
			<col />
		</colgroup>
		<tbody>

        <? if ($od[od_receipt_point] > 0) { ?>
        <tr>
            <th>포인트결제</th>
            <td><span class='item_point'><? echo display_point($od[od_receipt_point]) ?></span></td>
        </tr>
        <? } ?>

        <? if ($od['od_temp_bank'] > 0) { ?>
            <tr>
                <th><?=$od[od_settle_case]?></th>
                <td><span class='amount'><? echo display_amount($od[od_temp_bank] - $od['od_dc_amount']) ?> <em>원</em></span>(결제하실 금액)</td>
            </tr>
            <? if ($od[od_settle_case] == '무통장') { ?>
                <tr>
                    <th>계좌번호</th>
                    <td><? echo $od[od_bank_account]; ?></td>
                </tr>
            <? } ?>
        <tr>
            <th>입금자 이름</th>
            <td><? echo $od[od_deposit_name]; ?></td>
        </tr>
        <? } ?>

        <? if ($od[od_temp_card] > 0) { ?>
		<tr>
            <th>신용카드</th>
            <td><span class='amount'>
				<? echo display_amount($od[od_temp_card] - $od['od_dc_amount']) ?>
				<?
				// 김선용 200801 : PG 사가 authorize 인 경우 USD 금액 표기, 소수점 3자리에서 무조건 올림
				if($default['de_card_pg'] == 'authorize'){
					$temp_pay = (($od['od_temp_card'] - $od['od_dc_amount']) / $default['de_conv_pay']);
					$x_amount = ceil($temp_pay * 100)/100;
					echo "<span style='font-size:9pt; font-family:tahoma; color:#6666FF;'>(\$".number_format($x_amount,2).")</span>";
				}
				?>
				<?if($x_amount){?>
				<em>원</em></span>(결제하실 금액)
				<?}?>
			</td>
        </tr>
		<?}?>
		</tbody>
        </table>
    </td>
</tr>
</table>

<?php
/*
$bid = array_keys($bid);

for($b=0; $b<count($bid); $b++){
	$bid_in .= (($b == 0) ? '' : ',')."'".$bid[$b]."'";
	$bid_sum .= (($b == 0) ? '' : '|').$bid[$b];
}
$bid_in = (($bid_in) ? "bid in (".$bid_in.")" : '');

$where .= (($where) ? ' and':'') . $bid_in;


$where .= (($where) ? ' and':'') ." od_id = '".$od['od_id']."'";

$where = (($where) ? ' where ' : '' ).$where;

$gift_event_history_chk_qry = sql_fetch($a="
	select
		count(*) as cnt
	from
		yc4_free_gift_event_order_history
	".$where."
");
//echo $a; exit;

$gift_event_history_chk = $gift_event_history_chk_qry['cnt'];
if($gift_event_history_chk == 0 && $bid){
	if(is_array($bid)){
		foreach($bid as $val){
			# 이벤트 정보 로드 #
			$ev_info = sql_fetch("select bid,event_type,it_maker,ca_id from yc4_free_gift_event where bid = '".$val."'");

			# 이벤트 최소금액 로드 #
			$ev_item_amount_info = sql_fetch("select od_amount from yc4_free_gift_event_item where bid = '".$val."' order by od_amount asc");

			switch($ev_info['event_type']){
				case 'A' :
					if($tot_sell_amount >= $ev_item_amount_info['od_amount']){
						$gift_event_flag = true;
					}
					break;
				case 'B' :
					if($gift_event_brand[$ev_info['it_maker']] >= $ev_item_amount_info['od_amount']){
						$gift_event_flag = true;
					}
					break;
				case 'C' :
					if($gift_event_cate[$ev_info['ca_id']] >= $ev_item_amount_info['od_amount']){
						$gift_event_flag = true;
					}
					break;
			}

		}
	}
	if($gift_event_flag){
		$event_item_choice_btn = "
			<form action='".$g4['shop_path']."/gift_event_item_choice.php' method='post' name='gift_item_frm'>
				<input type='hidden' name='od_id' value='".$od['od_id']."'/>
				<input type='hidden' name='bid_merge' value='".$bid_sum."'>
				<p align='center'>
				<input type='image' src='http://115.68.20.84/main/btn_order_gift.gif' value=' 이벤트 사은품 신청 '></input>
				</p>
			</form>
			<script>
				$(document).ready(function(){
					alert('노르딕 이벤트에 해당되어 사은품 선택 페이지로 이동합니다.');
					gift_item_frm.submit();
				});
			</script>
		";
	}
}
*/

// 파일이 존재한다면 ...
if (file_exists("./settle_{$default[de_card_pg]}.inc.php"))
{

	$settle_case = $od['od_settle_case'];
	if ($settle_case == '')
	{
		echo "*** 결제방법 없음 오류 ***";
	}
	else if ($settle_case == '무통장')
	{
		// 김선용 201207 : 사은품
		if($od['od_gift_id'] != ''){
			$gi_str = array();
			$gi_id = explode(";", $od['od_gift_id']);
			for($k=0; $k<count($gi_id); $k++){
				$gi = sql_fetch("select gift_title from {$g4['yc4_gift_table']} where gift_id='{$gi_id[$k]}' ");
				$gi_str[] = stripslashes($gi['gift_title']);
			}
			echo "<p style='margin:4px; padding:4px; border:1px solid #ff0000;'><b>※ 사은품 이벤트에 포함되셨습니다. 상품 배송시 같이 배송됩니다.</b><br/>".implode("<br/>", $gi_str)."</p>";
		}

		if($event_item_choice_btn){
			echo $event_item_choice_btn;
		}else{
			echo "<p align=center><a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><img src='{$g4[shop_img_path]}/btn_order_end.gif' border=0></a>";
		}
	}
	// 김선용 201107 :
	else if($settle_case == '가상계좌' && $od['od_temp_bank'] == 0){
		if($event_item_choice_btn)
			echo $event_item_choice_btn;
		else
			echo "<p align=center><a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><img src='{$g4[shop_img_path]}/btn_order_end.gif' border=0></a>";
	}
	else if($settle_case == '신용카드' && $od['od_temp_card'] == 0){
		if($event_item_choice_btn)
			echo $event_item_choice_btn;
		else
			echo "<p align=center><a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><img src='{$g4[shop_img_path]}/btn_order_end.gif' border=0></a>";
	}else
	{
		if ($settle_case == '신용카드'){
			$settle_amount = $od['od_temp_card'] - $od['od_dc_amount'];
		}
		else{
			$settle_amount = $od['od_temp_bank'] - $od['od_dc_amount'];
		}

		// 김선용 2014.04 : 가상계좌 분리.
		if($settle_case == '가상계좌'){

			//if($od['od_kcp_vbank_fix'])
				include "./settle_{$default[de_card_pg]}.vcnt.inc.php"; // 고정
			//else
			//	include "./settle_{$default[de_card_pg]}.inc.php";
		}
		else{
			/*
			if($_DEBUG){
				include "./settle_{$default[de_card_pg]}.inc_test.php"; // kcp 신용카드결제도 지원해야 한다.
			}else{
				include "./settle_{$default[de_card_pg]}.inc.php"; // kcp 신용카드결제도 지원해야 한다.
			}
			*/
			include "./settle_{$default[de_card_pg]}.inc.php"; // kcp 신용카드결제도 지원해야 한다.
		}

		// 김선용 200801
		//if($default['de_card_pg'] != 'authorize')
		//	echo "<p style='padding:20px 0 30px 0;text-align:center;'><input type='image' src='$g4[shop_img_path]/btn_settle.gif' border=0 onclick='OpenWindow();'>";

		if($settle_case != '가상계좌') echo "<p align=left>&nbsp; &middot; 결제가 제대로 되지 않은 경우 [<a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><u>주문상세조회 페이지</u></a>] 에서 다시 결제하실 수 있습니다.</p>";
	}
}
else
{
	if ($od[od_temp_card]) {
		include "./ordercard{$default[de_card_pg]}.inc.php";
		echo "<p align=center><input type='image' src='$g4[shop_img_path]/btn_card.gif' border=0 onclick='OpenWindow();'></p>";
		echo "<p align=left>&nbsp; &middot; 결제가 제대로 되지 않은 경우 <a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><u>주문상세조회 페이지</u></a>에서 다시 결제하실 수 있습니다.</p>";
	} else if ($od[od_temp_bank] && $od[od_bank_account] == "계좌이체")  {
		include "./orderiche{$default[de_card_pg]}.inc.php";
		echo "<p align=center><input type='image' src='$g4[shop_img_path]/btn_iche.gif' border=0 onclick='OpenWindow();'></p>";
		echo "<p align=left>&nbsp; &middot; 결제가 제대로 되지 않은 경우 [<a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><u>주문상세조회 페이지</u></a>] 에서 다시 결제하실 수 있습니다.</p>";
	} else {
		if($event_item_choice_btn)
			echo $event_item_choice_btn;
		else
			echo "<p align=center><a href='{$g4[path]}'><img src='{$g4[shop_img_path]}/btn_order_end.gif' border=0></a>";

	}
}



?>
<br><br>

<?php
include_once("./_tail.php");
?>
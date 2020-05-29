<?
include_once("./_common.php");

// 장바구니가 비어있는가?
$tmp_on_uid = get_session('ss_temp_on_uid');
if (get_cart_count($tmp_on_uid) == 0)// 장바구니에 담기
    alert("장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.", "./cart.php");

$sql = " select * from $g4[yc4_order_table] where on_uid = '$tmp_on_uid' ";
$od = sql_fetch($sql);

//print_r2($od);

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


// 김선용 2014.03 : kcp 복합 처리
if($od['card_settle_case'] == 'kcp' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '에스크로')
	$default['de_card_pg'] = 'kcp';
else if($od['card_settle_case'] == 'authorize')
	$default['de_card_pg'] = 'authorize';

?>
<!--<img src="<?=$g4[shop_img_path]?>/top_orderconfirm.gif" border=0><p>-->
<div style="padding-top:20px;"></div>
<table width="755" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="319"><img src="<?=$g4['path']?>/images/category/category_title01_c.gif"></td>
	<td width="353" align="right" class="font11">HOME &gt; <span class="font11_orange">주문확인 및 결제</span></td>
</tr>
<tr><td height="2" colspan="2" bgcolor="#fa5a00"></td></tr>
</table><p>

<?
$s_page = '';
$s_on_uid = $tmp_on_uid;
$od_id = $od[od_id];
include_once("./cartsub.inc.php");
?>


<br>
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<tr>
    <td><span class="font11_orange">주문번호 : <b><?=$od[od_id]?></B></span></td>
</tr>
</table>

<!-- 주문하시는 분 -->
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td class="c3" align=center>주문하시는 분</td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=120>
        <colgroup width=''>
        <tr height=25>
            <td>이름</td>
            <td><? echo $od[od_name] ?></td>
        </tr>
        <tr height=25>
            <td>전화번호</td>
            <td><? echo $od[od_tel] ?></td>
        </tr>
        <tr height=25>
            <td>핸드폰</td>
            <td><? echo $od[od_hp] ?></td>
        </tr>
        <tr height=25>
            <td>주소</td>
            <td><? echo sprintf("(%s-%s) %s %s", $od[od_zip1], $od[od_zip2], $od[od_addr1], $od[od_addr2]); ?></td>
        </tr>
        <tr height=25>
            <td>E-mail</td>
            <td><? echo $od[od_email] ?></td>
        </tr>

        <? if ($default[de_hope_date_use]) { // 희망배송일 사용한다면 ?>
        <tr height=25>
            <td>희망배송일</td>
            <td><?=$od[od_hope_date]?> (<?=get_yoil($od[od_hope_date])?>)</td>
        </tr>
        <? } ?>
        </table>
    </td>
</tr>
</table>


<!-- 받으시는 분 -->
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td class="c3" align=center>받으시는 분</td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>

	<?if($od['od_ship']){ // 김선용 201211 : 복수배송?>
		<? echo get_fui_ship_item($tmp_on_uid, $member['mb_id'], 0, false);?>
	<?}else{ // 단수배송?>
        <table cellpadding=3>
        <colgroup width=120>
        <colgroup width=''>
        <tr height=25>
            <td>이름</td>
            <td><? echo $od[od_b_name]; ?></td>
        </tr>
        <tr height=25>
            <td>전화번호</td>
            <td><? echo $od[od_b_tel] ?></td>
        </tr>
        <tr height=25>
            <td>핸드폰</td>
            <td><? echo $od[od_b_hp] ?>&nbsp;</td>
        </tr>
        <tr height=25>
            <td>주소</td>
            <td><? echo sprintf("(%s-%s) %s %s", $od[od_b_zip1], $od[od_b_zip2], $od[od_b_addr1], $od[od_b_addr2]); ?></td>
        </tr>
        <tr height=25>
            <td>전하실말씀</td>
            <td><? echo nl2br(htmlspecialchars2($od[od_memo])); ?>&nbsp;</td>
        </tr>
        </table>
	<?}?>
    </td>
</tr>
</table>

<!-- 결제 정보 -->
<table width=97% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
     <td class="c3" align=center>결제 정보</td>
         <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3 width=100% cellspacing=0 border=0>
        <colgroup width=120>
        <colgroup width=''>

        <? if ($od[od_receipt_point] > 0) { ?>
        <tr height=25>
            <td>포인트결제</td>
            <td><? echo display_point($od[od_receipt_point]) ?></td>
        </tr>
        <? } ?>

        <? if ($od['od_temp_bank'] > 0) { ?>
            <tr height=25>
                <td><?=$od[od_settle_case]?></td>
                <td><? echo display_amount($od[od_temp_bank]) ?>  (결제하실 금액)</td>
            </tr>
            <? if ($od[od_settle_case] == '무통장') { ?>
                <tr height=25>
                    <td>계좌번호</td>
                    <td><? echo $od[od_bank_account]; ?></td>
                </tr>
            <? } ?>
        <tr height=25>
            <td>입금자 이름</td>
            <td><? echo $od[od_deposit_name]; ?></td>
        </tr>
        <? } ?>

        <? if ($od[od_temp_card] > 0) { ?>
		<tr height=25>
            <td>신용카드</td>
            <td>
				<? echo display_amount($od[od_temp_card]) ?>
				<?
				// 김선용 200801 : PG 사가 authorize 인 경우 USD 금액 표기, 소수점 3자리에서 무조건 올림
				if($default['de_card_pg'] == 'authorize'){
					$temp_pay = ($od['od_temp_card'] / $default['de_conv_pay']);
					$x_amount = ceil($temp_pay * 100)/100;
					echo "<span style='font-size:9pt; font-family:tahoma; color:#6666FF;'>(\$".number_format($x_amount,2).")</span>";
				}
				?>
				(결제하실 금액)
			</td>
        </tr>
		<?}?>
        </table>
    </td>
</tr>
</table>

<?

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

		echo "<p align=center><a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><img src='{$g4[shop_img_path]}/btn_order_end.gif' border=0></a>";
	}
	// 김선용 201107 :
	else if($settle_case == '가상계좌' && $od['od_temp_bank'] == 0)
		echo "<p align=center><a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><img src='{$g4[shop_img_path]}/btn_order_end.gif' border=0></a>";
	else if($settle_case == '신용카드' && $od['od_temp_card'] == 0)
		echo "<p align=center><a href='./orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]'><img src='{$g4[shop_img_path]}/btn_order_end.gif' border=0></a>";
	else
	{
		if ($settle_case == '신용카드')
			$settle_amount = $od['od_temp_card'];
		else
			$settle_amount = $od['od_temp_bank'];

		// 김선용 2014.04 : 가상계좌 분리.
		if($settle_case == '가상계좌'){
			//if($od['od_kcp_vbank_fix'])
				include "./settle_{$default[de_card_pg]}.vcnt.inc.php"; // 고정
			//else
			//	include "./settle_{$default[de_card_pg]}.inc.php";
		}
		else
			include "./settle_{$default[de_card_pg]}.inc.php"; // kcp 신용카드결제도 지원해야 한다.

		// 김선용 200801
		//if($default['de_card_pg'] != 'authorize')
		//	echo "<p align=center><input type='image' src='$g4[shop_img_path]/btn_settle.gif' border=0 onclick='OpenWindow();'>";

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
		echo "<p align=center><a href='{$g4[path]}'><img src='{$g4[shop_img_path]}/btn_order_end.gif' border=0></a>";
	}
}
?>
<br><br>

<?
include_once("./_tail.php");
?>
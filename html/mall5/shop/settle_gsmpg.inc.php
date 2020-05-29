<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 상품명 갖고오기
$item_name = get_goods($_SESSION['ss_temp_on_uid']);
?>
<iframe width="0" height="0" name="_gsmpg_"></iframe>
<form accept-charset="euc-kr" name="KSpayAuth" method="post" onsubmit="return gsmpg_post(this);" autocomplete="off" style="margin:0;">
<!--<form name="KSpayAuth" method="post" onsubmit="return gsmpg_post(this);" autocomplete="off" style="margin:0;">-->
<input type="hidden" name="authty" value="6000"><!-- 가상계좌발급 -->
<input type="hidden" name="returnurl" value="<?=$g4['url']?>/shop/settle_gsmpg_result.php"><!-- 결과값을 리턴받을 URL -->
<input type="hidden" name="shopid" value="100162"> <!-- 상점 id :  -->
<input type="hidden" name="orderamount" value="<?=$od['od_temp_bank']?>"><!-- 주문금액  -->
<input type="hidden" name="orderno" value="<?=$od['od_id']?>"><!-- 주문번호(on_uid 로 대체) -->
<input type="hidden" name="itemname" value="<?=cut_str($item_name['full_name'], 10, '..')?>"><!-- 상품명 -->
<input type="hidden" name="email" value="<?=substr($od['od_email'],0,25)?>"><!-- 주문자 e-mail -->
<input type="hidden" name="mobile" value="<?=preg_replace("/[^0-9]/", "", $od['od_hp'])?>"><!-- 주문자 휴대전화 -->
<input type="hidden" name="escrow" value="0"><!-- 에스크로 미사용 처리 -->
<input type="hidden" name="etc01" value="<?=$od['od_id']?>||<?=$od['od_name']?>||<?=$od['od_temp_bank']?>"><!-- 그대로 리턴받을값(예비1) -->
<input type="hidden" name="etc02" value="<?=$_SESSION['ss_temp_on_uid']?>"><!-- 그대로 리턴받을값(예비2) -->

<table cellpadding=4 cellspacing=2 align="center" width="95%" summary="" border=0 style="border-collapse:collapse; border:4px solid darkblue;">
<col width=150></col>
<col width=""></col>
<tr>
    <td>입금자명</td>
    <td><input type="text" name="ordername" size=10 class="ed" value="<?=$od['od_name']?>"></td>
</tr>
<tr><td height=1 bgcolor=#eaeaea colspan=2></td></tr>
<tr><td height=1 bgcolor=#fafafa colspan=2></td></tr>
<tr>
    <td>결제금액</td>
    <td><?=number_format($od['od_temp_bank'],0)?></td>
</tr>
<tr><td height=1 bgcolor=#eaeaea colspan=2></td></tr>
<tr><td height=1 bgcolor=#fafafa colspan=2></td></tr>
<tr>
    <td>주문자E-mail<br>(승인메일발송용)</td>
    <td><?=$od['od_email']?></td>
</tr>
<tr><td height=1 bgcolor=#eaeaea colspan=2></td></tr>
<tr><td height=1 bgcolor=#fafafa colspan=2></td></tr>
<tr>
    <td>주문자휴대전화<br>(SMS발송용)</td>
    <td><?=$od['od_hp']?></td>
</tr>
<tr><td height=1 bgcolor=#eaeaea colspan=2></td></tr>
<tr><td height=1 bgcolor=#fafafa colspan=2></td></tr>
<tr>
    <td>입금은행</td>
    <td>
        <select name="bankcode">
            <option value="27">시티은행</option>
			<option value="04">국민은행</option>
            <option value="11">농협</option>
            <option value="26">신한은행</option>
            <option value="20">우리은행</option>
            <option value="23">제일은행</option>
            <option value="71">우체국</option>
            <option value="81">하나은행</option>
            <option value="03">기업은행</option>
        </select>
    </td>
</tr>
<tr><td height=1 bgcolor=#eaeaea colspan=2></td></tr>
<tr><td height=1 bgcolor=#fafafa colspan=2></td></tr>
<!--
<tr>
    <td>에스크로구분</td>
    <td>
        <input type="radio" name="escrow" value="0" checked>미사용
        <input type="radio" name="escrow" value="1" >사용 (<strong>10만원이상</strong>만 해당됩니다.)
    </td>
</tr>
-->
<tr>
    <td colspan=2 align=center>
        <HR>
    </td>
</tr>
<tr>
<td colspan=2 align=center>
    <input type="submit" value="가상계좌발급">
</td>
</tr>
</table>
</form>

<script type="Text/JavaScript">
<!--
// 카드결제
function gsmpg_post(f)
{
	// 실제 사용시
	var url  = "http://pg.gsmnton.com/gsmpg/handler/Demand-Start";
	f.action = "http://pg.gsmnton.com/gsmpg/handler/Demand-Start";
	//var url  = "http://testpg.gsmnton.com/gsmpg/handler/Demand-Start";
	//f.action = "http://testpg.gsmnton.com/gsmpg/handler/Demand-Start";

	if(g4_is_ie) document.charset = 'euc-kr';
	window.open(url, "_gsmpg_", "");
	f.target = "_gsmpg_";
	//window.open(url, "temp", "width=600,height=500,scrollbars=1,status=1,top=100,left=100");
	//f.target = "temp";
	f.submit();
	return false;
}
//-->
</script>

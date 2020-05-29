<?php
include_once("./_common.php");
include $g4['full_path']."/lib/encrypt.lib.php";
// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

$sql = "select * from $g4[yc4_order_table] where od_id = '$od_id' and on_uid = '$on_uid' ";
$od = sql_fetch($sql);
if (!$od[od_id])
    alert("조회하실 주문서가 없습니다.", $g4[path]);


$od_mb_point = sql_fetch("select mb_point from ".$g4['member_table']." where mb_id = '".$od['mb_id']."' ");
$od_mb_point = $od_mb_point['mb_point'];


// 결제방법
$settle_case = $od[od_settle_case];

set_session('ss_temp_on_uid', $on_uid);

$g4[title] = "주문상세내역 : 주문번호 - $od_id";
include_once("./_head.php");
?>
<div class='PageTitle'>
<img src="<?=$g4['path']?>/images/menu/menu_title02_b.gif" alt="주문상세조회" />
</div>

<?php
$s_on_uid = $od[on_uid];
$s_page = "orderinquiryview.php";
include "./cartsub.inc.php";

$delivery_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_cart_table']." where on_uid = '".$s_on_uid."' and ct_status in ('배송')");

?>

<div style='padding:0 0 15px 0;text-align:right;'><img src='<?=$g4[shop_img_path]?>/status01.gif' align=absmiddle> : 주문대기, <img src='<?=$g4[shop_img_path]?>/status02.gif' align=absmiddle> : 상품준비중, <img src='<?=$g4[shop_img_path]?>/status03.gif' align=absmiddle> : 배송중, <img src='<?=$g4[shop_img_path]?>/status04.gif' align=absmiddle> : 배송완료</div>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
	<td style='border:solid 5px #d9dfe8;padding:20px;line-height:200%;	'>
	<p class="font11_orange" style='font-size:17px;width:300px;float:left;'>주문번호 | <strong><?=$od[od_id]?></strong></p>
<!-- 김선용 200805 : 상품수령 처리 -->
    <?php if($delivery_chk['cnt'] > 0){ // 배송 상태의 상품이 있을 경우에만 수령확이 버튼 출력 ?>
	<div style="text-align:right;">
	※ 상품을 받으신 경우 <strong>'상품수령확인'</strong>을 하시면 포인트 적립이 바로 처리됩니다.
	<input type="button" value=" 상품수령확인 " onclick="delivery_confirm();" title="상품수령확인하기" style='height:20px;line-height:18px;padding:0 5px;font-size:11px;'>
	</div>
    <?php }?>
	<form name="fdelivery" target="_self" method="post" action="member_delivery_confirm.php">
	<input type="hidden" name="ss_token" value="<?=$token?>">
	<input type="hidden" name="od_id" value="<?=$od_id?>">
	<input type="hidden" name="on_uid" value="<?=$on_uid?>">
	</form>
	<script type="Text/JavaScript">
	function delivery_confirm()
	{
		if(confirm('상품수령확인을 하시겠습니까?'))
			document.fdelivery.submit();

		return;
	}
	</script>
	</td>
</tr>

<!-- // 김선용 201207 : -->
<tr>
	<td colspan=10>
	<?php
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
	?>
	</td>
</tr>

<tr><th class="table_title" style="padding-top:30px;">주문하시는 분</th></tr>
<tr>
    <td>
        <table cellpadding=0 cellspacing=0 width=100% class='list_order'>
        <colgroup>
			<col width='140'>
			<col />
		</colgroup>
		<tbody>
        <tr><th>주문일시</th><td><strong><? echo $od[od_time] ?></strong></td></tr>
        <tr><th>이 름</th><td><? echo $od[od_name] ?></td></tr>
        <tr><th>전화번호</th><td><? echo $od[od_tel] ?></td></tr>
        <tr><th>핸드폰</th><td><? echo $od[od_hp] ?></td></tr>
        <tr>
			<th>주 소</th>
			<td><p><?=sprintf("(%s-%s)&nbsp;%s %s", $od[od_zip1], $od[od_zip2], $od[od_addr1], $od[od_addr2])?></p>
				<?if($od['od_addr_jibeon']){?>
				<p>지번주소 <?=$od['od_addr_jibeon'];?></p>
				<?}?>
			</td>
		</tr>
        <tr><th>이메일</th><td><? echo $od[od_email] ?></td></tr>
    </tbody>
	</table></td>
</tr>

<tr><th class="table_title" style="padding-top:30px;">받으시는 분</th></tr>
<tr>
    <td>

	<?if($od['od_ship']){ // 김선용 201211 : 복수배송?>
		<? echo get_fui_ship_item($on_uid, $member['mb_id'], 0, false);?>
	<?}else{ // 단수배송?>

        <table cellpadding=0 cellspacing=0 width=100% class='list_order'>
        <colgroup>
			<col width='140'>
			<col />
		</colgroup>
		<tbody>
        <tr><th>이 름</th><td><? echo $od[od_b_name] ?></td></tr>
        <tr><th>전화번호</th><td><? echo $od[od_b_tel] ?></td></tr>
        <tr><th>핸드폰</th><td><? echo $od[od_b_hp] ?></td></tr>
        <tr>
			<th>주 소</th>
			<td><p><?=sprintf("(%s-%s)&nbsp;%s %s", $od[od_b_zip1], $od[od_b_zip2], $od[od_b_addr1], $od[od_b_addr2])?></p>
				<?if($od['od_b_addr_jibeon']){?>
				<p>지번주소 <?=$od['od_b_addr_jibeon'];?></p>
				<?}?>
			</td>
		</tr>
        <?
        // 희망배송일을 사용한다면
        if ($default[de_hope_date_use])
        {
            echo "<tr>";
            echo "<th>희망배송일</th>";
            echo "<td>".substr($od[od_hope_date],0,10)." (".get_yoil($od[od_hope_date]).")</td>";
            echo "</tr>";
        }

        if ($od[od_memo]) {
            echo "<tr>";
            echo "<th>전하실 말씀</th>";
            echo "<td>".conv_content($od[od_memo], 0)."</td>";
            echo "</tr>";
        }
        ?>
		</tbody>
        </table>
	<?}?>
	</td></tr>


<?
// 배송회사 정보
$dl = sql_fetch(" select * from $g4[yc4_delivery_table] where dl_id = '$od[dl_id]' ");

if ($od[od_invoice] || !$od[misu])
{
    echo "<tr><th class='table_title' style='padding-top:30px;'>배송정보</th></tr>";
    echo "<tr><td>";
    if (is_array($dl))
    {
        // get 으로 날리는 경우 운송장번호를 넘김
        if (strpos($dl[dl_url], "=")) $invoice = $od[od_invoice];


		echo "<form method='post' name='invoice_frm' target='itracking' action='http://216.74.54.38/ShippingTracking.php'><input type='hidden' name='ots' value='".encrypt($invoice,$encrypt_key)."'/></form>";

        echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
        echo "<colgroup><col width='140'><col /></colgroup><tbody>";
        echo "<tr><th>배송회사</th><td> $dl[dl_company] &nbsp;&nbsp;[<a href='#' onclick='invoice_frm.submit();return false;'>배송조회하기</a>]</td></tr>";
        echo "<tr><th>운송장번호</th><td> $od[od_invoice]</td></tr>";
        echo "<tr><th>배송일시</th><td> $od[od_invoice_time]</td></tr>";
/*         echo "<tr><th>고객센터 전화</th><td> $dl[dl_tel]</td></tr>"; */
        echo "</tbody></table><iframe name='itracking' style='width:100%;height:130px;font-size:10px'></iframe><div style='text-align:left;color:red;padding:5px 0;'>국내등기/택배조회는 통관이 완료되고 택배사 전산에 업데이트 된 이후부터 조회가 가능합니다.<br/>
항공배송 및 통관과정 중에는 국내 택배 정보는 나오지 않습니다.</div>
";
    }
    else
    {
        echo "<span class=leading>아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</span>";
    }
    // echo "<tr><td colspan=2 height=1 bgcolor='#cccccc'></td></tr>";
}
?><p>

<?
$receipt_amount = $od[od_receipt_bank]
                + $od[od_receipt_card]
                + $od[od_receipt_point]
                - $od[od_cancel_card]
                - $od[od_refund_amount];

$misu = true;

if($_MASTER_CARD_EVENT){
	$tot_amount_result  = $od[od_temp_bank]
						+ $od[od_temp_card]
						+ $od[od_temp_point];

	if ($tot_amount_result - $tot_cancel_amount == $receipt_amount) {
		$wanbul = " (완불)";
		$misu = false; // 미수금 없음
	}
}else{
	if ($tot_amount - $tot_cancel_amount == $receipt_amount) {
		$wanbul = " (완불)";
		$misu = false; // 미수금 없음
	}
}

$misu_amount = $tot_amount - $tot_cancel_amount - $receipt_amount;

echo "<tr>";
echo "<tr><th class='table_title' style='padding-top:30px;'>결제정보</th></tr>";
echo "<tr><td>";


if ($od[od_settle_case] == '신용카드')
{
    if ($od[od_receipt_card] > 0)
    {
        $sql = " select * from $g4[yc4_card_history_table] where od_id = '$od[od_id]' order by cd_id desc ";
        $result = sql_query($sql);
        $cd = mysql_fetch_array($result);
    }

	// 김선용 2014.03 : kcp/authorize 구분
	//$card_str = ($od['card_settle_case'] == 'kcp' ? ' [ 국내카드결제(KCP) ]' : '[ 해외카드결제(authorize.net) ]');

	switch($od['card_settle_case']){
		case 'kcp': $card_str = ' [ 국내카드결제(KCP) ]'; break;
		case 'masterpass': $card_str = ' [ MasterPass ]'; break; // 이성용 2015-07-21 MasterPass 조건 추가
		default: $card_str = '[ 해외카드결제(authorize.net) ]'; break;
	}

    echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
    echo "<colgroup><col width='140'><col /></colgroup><tobdy>";
    echo "<tr><td>· 결제방식</td><td>: 신용카드 결제 <b>{$card_str}</b></td></tr>";

    if ($od[od_receipt_card])
    {
		$usd_str = "";
		if(in_array($od['card_settle_case'],array('authorize','masterpass'))){
            $usd_str = " (\$".number_format($cd['cd_amount_usd'],2).")";
        }

        echo "<tr><th>결제금액</th><td class=amount> " . display_amount($cd[cd_amount]) . "{$usd_str}</td></tr>";
        echo "<tr><th>승인일시</th><td> $cd[cd_trade_ymd] $cd[cd_trade_hms]</td>";
        echo "<tr><th>승인번호</th><td> $cd[cd_app_no]</td></tr>";
    }
    else if ($default[de_card_use] && $tot_cancel_amount == 0)
    {
        $settle_amount = $od['od_temp_card'] - $od['od_dc_amount'];
        echo "<tr><th>결제정보</th><td> 아직 승인되지 않았거나 승인을 확인하지 못하였습니다.</td></tr>";

		// 김선용 200801 : PG 사가 authorize 인 경우 USD 금액 표기, 소수점 3자리에서 무조건 올림
		echo "<tr><th>결제금액</th><td> ".display_amount($od[od_temp_card])."";
		if($od['card_settle_case'] == 'authorize'){
            $temp_pay = (($od['od_temp_card'] - $od['od_dc_amount']) / $default['de_conv_pay']);
			$x_amount = ceil($temp_pay * 100)/100;
			echo "(\$".number_format($x_amount,2).")&nbsp;";
		}
        if(!$wanbul){
            echo "(결제하실 금액)";
        }
        if(!$wanbul) {
            echo "</td></tr>";

            echo "<tr><td colspan=2>";
            if ((int)$member[mb_point] >= $od[od_temp_point]) {
                // 김선용 2014.03 : kcp/authorize
                if ($od['card_settle_case'] == 'kcp')
                    $default['de_card_pg'] = 'kcp';
                else if ($od['card_settle_case'] == 'authorize')
                    $default['de_card_pg'] = 'authorize';

                include "./settle_{$default[de_card_pg]}.inc.php";

                // 김선용 200801
                //if($default['de_card_pg'] != 'authorize')
                //	echo "<input type='image' src='$g4[shop_img_path]/btn_settle.gif' border=0 onclick='OpenWindow();'>";
            } else {
                echo "<font color=red>· 보유포인트가 모자라서 결제할 수 없습니다. 주문후 다시 결제하시기 바랍니다.</font>";
            }
            echo "</td></tr>";
        }
    }
    echo "</tbody></table>";
}
else
{
    echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
    echo "<colgroup><col width='140'><col /></colgroup><tbody>";
    echo "<tr><th>결제방식</th><td>{$od['od_settle_case']}</td></tr>";

    if ($od[od_receipt_bank])
    {
        echo "<tr><th>입금액</th><td>" . display_amount($od[od_receipt_bank]) . "</td></tr>";
        echo "<tr><th>입금확인일시</th><td>$od[od_bank_time]</td></tr>";
    }
    else
    {
        echo "<tr><th>입금액</th><td>아직 입금되지 않았거나 입금정보를 입력하지 못하였습니다.</td></tr>";
    }

    if ($od[od_settle_case] != '계좌이체')
        echo "<tr><th>계좌번호</th><td>$od[od_bank_account]</td></tr>";

    echo "<tr><th>입금자명</th><td>$od[od_deposit_name]</td></tr>";




    if ($default[de_iche_use] && $od[od_receipt_bank] == 0)
    {
        if ($od['od_settle_case'] == '계좌이체')
        {
            $settle_amount = $od['od_temp_bank'] - $od['od_dc_amount'];
            echo "<tr><td colspan=2>";
            if ($member[mb_point] >= $od[od_temp_point]) {
                include "./settle_{$default[de_card_pg]}.inc.php";
                echo "<input type='image' src='$g4[shop_img_path]/btn_settle.gif' border=0 onclick='OpenWindow();'>";
            } else {
                echo "<font color=red>· 보유포인트가 모자라서 결제할 수 없습니다. 주문후 다시 결제하시기 바랍니다.</font>";
            }
            echo "</td></tr>";
        }
	}

	// 김선용 2014.04 :
	if ($od['od_settle_case'] == '가상계좌' && $od['od_bank_account'] == '가상계좌' && $default['de_kcp_escrow_use'])
	{
		$settle_amount = $od['od_temp_bank'] - $od['od_dc_amount'];
		echo "<tr><td colspan=2>";
		if ($od_mb_point >= $od[od_temp_point]) {
			include "./settle_kcp.vcnt.inc.php";
			//echo "<input type='image' src='$g4[shop_img_path]/btn_settle.gif' border=0 onclick='OpenWindow();'>";
		} else {
		    echo "<font color=red>· 보유포인트가 모자라서 결제할 수 없습니다. 주문후 다시 결제하시기 바랍니다.</font>";
		}
		echo "</td></tr>";
	}

    echo "</tbody></table>";
}

if ($od[od_receipt_point] > 0)
{
    echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
    echo "<colgroup><col width='140'><col /></colgroup><tbody>";
    echo "<tr><th>포인트결제</th><td><span class='item_point'>" . display_point($od[od_receipt_point]) . "</span></td></tr>";
    echo "</table>";
//} else if ($od[od_temp_point] > 0) {
} else if ($od[od_temp_point] > 0 && $member[mb_point] >= $od[od_temp_point]) {
    echo "<table cellpadding=4 cellspacing=0 width=100%>";
    echo "<colgroup width=120><colgroup width=''>";
    echo "<tr><th>포인트결제</th><td><span class='item_point'>" . display_point($od[od_temp_point]) . "</span></td></tr>";
    echo "</tbody></table>";
}

if ($od[od_cancel_card] > 0)
{
    echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
    echo "<colgroup><col width='140'><col /></colgroup><tbody>";
    echo "<tr><th>승인취소 금액</th><td>" . display_amount($od[od_cancel_card]) . "</td></tr>";
    echo "</tbody></table>";
}

if ($od[od_refund_amount] > 0)
{
    echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
    echo "<colgroup><col width='140'><col /></colgroup><tbody>";
    echo "<tr><th>환불 금액</th><td>" . display_amount($od[od_refund_amount]) . "</td></tr>";
    echo "</tbody></table>";
}

// 취소한 내역이 없다면
if ($tot_cancel_amount == 0) {
    if (($od[od_temp_bank] > 0 && $od[od_receipt_bank] == 0) ||
        ($od[od_temp_card] > 0 && $od[od_receipt_card] == 0)) {
        echo "<form method='post' action='./orderinquirycancel.php' style='margin:0;'>";
        echo "<input type=hidden name=od_id  value='$od[od_id]'>";
        echo "<input type=hidden name=on_uid value='$od[on_uid]'>";
        echo "<input type=hidden name=token  value='$token'>";
        echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
        echo "<colgroup><col width='140'><col /></colgroup><tbody>";
        echo "<tr><th>주문취소</th><td><a href='javascript:;' onclick=\"document.getElementById('_ordercancel').style.display='';\"><img src='$g4[shop_img_path]/ordercancel.gif'></a></td></tr>";
        echo "<tr id='_ordercancel' style='display:none;'><th>취소사유</th><td>: <input type=text name='cancel_memo' size=40 maxlength=100 required itemname='취소사유'></textarea> <input type=submit value='확인'></td></tr>";
        echo "</tbody></table></form>";
    } else if ($od[od_invoice] == "") {
        echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
        echo "<colgroup><col width='140'><col /></colgroup><tbody>";
        echo "<tr><td style='color:blue;'>주문취소를 원하실 경우에는 <strong>한국시각 기준 주문당일 밤 12시이전까지 전화(고객상담시간내) 또는 1:1 문의로 요청</strong> 해주시기바랍니다.<br/>
밤 12시 이후에는 배송작업을 완료하여 주문취소가 안되니 이용에 참고 부탁드립니다.<br/>
또한 시간과 관계없이 물품의 발송작업이 완료 될 경우 제품수량변경, 취소가 안되니 참고해주세요.<br/>
<strong>※ 주말에는 취소처리가 실시간으로 어려운 점 양해 부탁드립니다. </strong><br/>
<strong>※ 주문 전 신중한 선택을 부탁드립니다. </strong></td></tr>";
        echo "</tbody></table>";
    }
} else {
    $misu_amount = $misu_amount - $send_cost;

    echo "<table cellpadding=0 cellspacing=0 width=100% class='list_order'>";
    echo "<colgroup><col width='140'><col /></colgroup><tbody>";
    echo "<tr><td style='color:red;'>주문 취소, 반품, 품절된 내역이 있습니다.</td></tr>";
    echo "</tbody></table>";
}
?>

<tr>
    <td>
	<table cellpadding=0 cellspacing=0 width=100% class='list_order'>
	<colgroup><col width='140'><col /></colgroup>
	<tbody>
	<tr>
	<th><strong>결제합계</strong><? echo $wanbul ?></th>
	<td>
        <p><span class='amount'><strong><? echo display_amount($receipt_amount) ?></strong><em>원</em></span></p>
        <?
        if ($od[od_dc_amount] > 0) {
            echo "<p>DC <span class='amount'> ". display_amount($od[od_dc_amount]) . "<em>원</em></span></p>";
        }

        if ($misu_amount > 0 && $misu) {

            echo "<p><font color=crimson>아직 결제하지 않으신 금액 <strong><span class='amount'> ".display_amount($misu_amount)." <em>원</em></strong></font></p>";
        }
        ?></td></tr>
	</tbody>
	</table>
	</td>
</tr>
<tr><td colspan=2 style='padding:30px 0;text-align:center;'><input type="image" src="<?=$g4['path']?>/images/common/common_btn_list.gif" value=" 목록으로 " onclick="self.location.href='orderinquiry.php';"></td></tr>
</table>

<?php
include_once("./_tail.php");

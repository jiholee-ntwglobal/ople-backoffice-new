<?
include_once("./_common.php");

$html_title = "결제 결과";
include_once("./_head.php");

if (get_session('ss_temp_on_uid') != $on_uid)
    alert("정상적인 방법으로 확인하실 수 있습니다.", $g4[path]);


$sql = " select * from $g4[yc4_card_history_table] where on_uid = '$on_uid' ";
$cd = sql_fetch($sql);
if ($cd[cd_id] == "")
    alert("값이 제대로 전달되지 않았습니다.");

$sql = " select * from $g4[yc4_order_table] where on_uid = '$on_uid' ";
$od = sql_fetch($sql);

// 김선용 201208 : 카드결제 완료부분에서 차감처리
/*
// 포인트 결제를 했다면 실제 포인트 결제한 것으로 수정합니다.
if ($od[od_receipt_point] == '0' && $od[od_temp_point] != '0')
{
    sql_query(" update $g4[yc4_order_table] set od_receipt_point = od_temp_point where on_uid = '$on_uid' ");
    insert_point($member[mb_id], (-1) * $od[od_temp_point], "주문번호:$od[od_id] 결제", "@order", $member[mb_id], "$od[od_id]");
}
*/

// 이곳에서 정상 결제되었다는 메일도 같이 발송합니다.
@extract($od);
$tmp_on_uid = $on_uid;

if ($od[od_settle_case] == '가상계좌')
    $od_receipt_bank = $od['od_temp_bank'];

// 김선용 201207 : 메일발송. extract 가 안먹네..설정문제인가..
$od_email = $od['od_email'];
$od_name = $od['od_name'];
$od_id = $od['od_id'];
$od_send_cost = $od['od_send_cost'];
$od_receipt_point = $od['od_receipt_point'];
$od_receipt_card = $od['od_receipt_card'];
$od_receipt_bank = $od['od_receipt_bank'];
$od_bank_account = $od['od_bank_account'];
$od_deposit_name = $od['od_deposit_name'];
$od_tel = $od['od_tel'];
$od_hp = $od['od_hp'];
$od_zip1 = $od['od_zip1'];
$od_zip2 = $od['od_zip2'];
$od_addr1 = $od['od_addr1'];
$od_addr2 = $od['od_addr2'];
$od_b_tel = $od['od_b_tel'];
$od_b_hp = $od['od_b_hp'];
$od_b_zip1 = $od['od_b_zip1'];
$od_b_zip2 = $od['od_b_zip2'];
$od_b_addr1 = $od['od_b_addr1'];
$od_b_addr2 = $od['od_b_addr2'];
$od_hope_date = $od['od_hope_date'];
$od_memo = nl2br(htmlspecialchars2(stripslashes($od['od_memo']))) . "&nbsp;";

include_once("ordermail1.inc.php");
include_once("ordermail2.inc.php");

if ($od[od_settle_case] == '가상계좌' || $od[od_settle_case] == '에스크로')
{
    $msg_settle_amount = '결제하실 금액';
    $settle_amount = $od[od_temp_bank];
    $msg_trade_time = '처리일시';
}
else
{
    $msg_settle_amount = '결제금액';
    $settle_amount = $cd[cd_amount];
    $msg_trade_time = '결제일시';
}
?>

<img src="<?=$g4[shop_img_path]?>/top_orderconfirm.gif" border="0"><p>

<?
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
<table width=500 align=center cellpadding=0 cellspacing=0>
<tr><td align=center height=50><!-- 결제를 정상적으로 처리하였습니다. --></td></tr>
<tr><td height=2 bgcolor=#94a9e7></td></tr>
<tr><td bgcolor=#e7ebf7 height=28 align=center><?=$od['od_settle_case']?> 결제 내역</td></tr>
<tr>
    <td style='padding-left:10px'>
        <table cellpadding=5>
        <tr><td> · 주문번호</td><td>: <?=$cd[od_id]?></td></tr>
        <? if ($od[od_settle_case] == '신용카드') { ?><tr><td width=100> · 승인번호</td><td><font color="#7f3ca2">: <b><?=$cd[cd_app_no]?></b></font></td></tr><? } ?>
        <tr><td> · <?=$msg_settle_amount?></td><td>: <span class=amount><?=display_amount($settle_amount)?></span></td></tr>
        <? 
		if ($od[od_settle_case] == '가상계좌' || $od[od_settle_case] == '에스크로') { 
		?><tr><td width=100> · 계좌번호</td><td><font color="#7f3ca2">: <b><?=$od[od_bank_account]?></b></font></td></tr>
		<? 
		}
		?>
        <tr><td> · <?=$msg_trade_time?></td><td>: <?=$cd[cd_trade_ymd]?> <?=$cd[cd_trade_hms]?></td></tr>
        </table>
    </td></tr>
<tr><td height=2 bgcolor=#94a9e7></td></tr>
</table><br><br>

<p align=center>
    <a href='<?="$g4[shop_path]/orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]";?>'><img src='<?=$g4[shop_img_path]?>/btn_confirm.gif' border=0></a>
<!-- <a href="javascript:;" onclick="window.open('http://admin.kcp.co.kr/Modules/Sale/Card/ADSA_CARD_BILL_Receipt.jsp?c_trade_no=<?=$_POST[trace_no]?>', 'winreceipt', 'width=620,height=670')">영수증 출력</a> -->


<?
include_once("./_tail.php");
?>
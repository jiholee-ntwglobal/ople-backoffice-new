<?

include_once("./_common.php");

$html_title = "결제 결과";
include_once("./_head.php");

//if (get_session('ss_temp_on_uid') != $on_uid)
//    alert("정상적인 방법으로 확인하실 수 있습니다.", $g4[path]);

$on_uid = '453b2d2e8c14616a0c355c58c9e6d490';
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
<div class="PageTitle">
  <img src="<?=$g4[shop_img_path]?>/top_orderconfirm.gif" alt="주문확인 및 결제완료">
</div>

<!--<img src="<?=$g4[shop_img_path]?>/top_orderconfirm.gif" border="0">-->

<?
$s_page = '';
$s_on_uid = $tmp_on_uid;
$od_id = $od[od_id];
include_once("./cartsub.inc.php");

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
?>
<div style='margin:30px auto;width:600px;border:solid 2px #aaa;'>
<table width=100% cellpadding=0 cellspacing=0>
<tbody>
<tr><td bgcolor=#f9f9f9 style='font-weight:bold;text-align:center;padding:8px 0;border-bottom:solid 1px #aaa;font-size:1.2em;'><?=$od['od_settle_case']?> 결제 내역</td></tr>
<tr>
    <td style='padding-left:10px'>
        <table width=100% cellpadding=0 cellspacing=0 class='sell_comfirm'>
        <tr><th>주문번호</th><td><?=$cd[od_id]?></td></tr>
        <? if ($od[od_settle_case] == '신용카드') { ?><tr><th width=100>승인번호</th><td><font color="#7f3ca2"><b><?=$cd[cd_app_no]?></b></font></td></tr><? } ?>
        <tr><th><?=$msg_settle_amount?></th><td><span class=amount><?=display_amount($settle_amount)?></span>
		<?
		if($_MASTER_CARD_EVENT){
			$mc_info = sql_fetch("select ori_card_amount - result_amount as dc_amount from master_card_event where od_id = '".$cd['od_id']."'");
			if($mc_info['dc_amount']) echo "(".number_format($mc_info['dc_amount'])." 할인)";
		}
		?>
		</td></tr>
        <?
		if ($od[od_settle_case] == '가상계좌' || $od[od_settle_case] == '에스크로') {
		?><tr><th width=100>계좌번호</th><td><font color="#7f3ca2"><b><?=$od[od_bank_account]?></b></font></td></tr>
		<?
		}
		?>
        <tr><th><?=$msg_trade_time?></th><td><?=$cd[cd_trade_ymd]?> <?=$cd[cd_trade_hms]?></td></tr>
        </table>
    </td></tr>
</table>
</div>

<p align=center>
	<?if($event_item_choice_btn){
		echo $event_item_choice_btn;
	}else{?>
    <a href='<?="$g4[shop_path]/orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]";?>'><img src='<?=$g4[shop_img_path]?>/btn_confirm.gif' border=0></a>
	<?}?>
</p>


<?php
# 즉석 이벤트 당첨 여부 확인 #
$rand_ev_chk = sql_fetch("select ev_code from yc4_event_item_rand where od_id = '".$od_id."'");

switch($rand_ev_chk['ev_code']){
	case '1' : $ev_comment = "고디바 이벤트"; break;
	case '2' : $ev_comment = "마스타카드 아워홈 상품권 이벤트"; break;
}


if($ev_comment){
?>
<div class='event_layer_contents'>
	<div style='background-image:url(<?php echo $g4['path'];?>/images/event/win_banner.gif); width:406px; height:206px; position:relative;'>
		<div style='height:40px; position:relative;'>
			<div style='position:absolute; right:0px; top:0px; height:40px; width:40px; cursor:pointer;' onclick="layer2_close();"></div>
		</div>
		<div style='position:absolute;top:136px; right:0px; left:0px; /*width:75px;*/ height:20px; text-align:center; font-weight:bold;'><span style='background-color:#f7f7f7; padding:0 20px;'>[<?=$ev_comment;?>]에 당첨되셨습니다.</span></div>
	</div>
</div>

<style type="text/css">
.event_layer_contents{
	display:none;
}
.layer_contents_wrap2{
	position: absolute;
	left:50%;
	top:50%;
	width: 406px;
	height: 206px;
	margin-left: -203px;
	margin-top: -103px;

}
</style>
<script type="text/javascript">
$(function(){
	$('.site_wrap').css({
		'height' : $(window).height()+'px',
		'overflow' : 'hidden'
	});
	$('.Floating_bannerArea').hide();
	$('.layer_wrap2').append("<div class='layer_contents_wrap2'></div>").show();
	$('.layer_wrap2 .layer_contents_wrap2').html($('.event_layer_contents').html());
});
function layer2_close(){
	$('.site_wrap').removeAttr('style');
	$('.Floating_bannerArea').show();
	$('.layer_contents_wrap2').remove();
	$('.layer_wrap2').hide();
}
$(document).click(function(a){
	if($(a.originalEvent.target).attr('class') == 'layer_mask2'){
		layer2_close();
	}
});
</script>
<?php }?>
<?php
include_once("./_tail.php");
?>
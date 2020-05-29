<?php
// 김선용 201205 : 인코딩 해제됨.(sir 영카트4 자체 인코딩 해제)
$sub_menu = "400400";
include_once("./_common.php");

include $g4['full_path'].'/lib/db_bs.php';

$db = new db();

//$sql = " ALTER TABLE `$g4[yc4_order_table]` ADD `od_cash` TINYINT NOT NULL ";
//sql_query($sql, false);

// 메세지
$html_title = "주문 내역 수정";
$alt_msg1   = "주문번호 오류입니다.";
$mb_guest   = "비회원";
// 김선용 201206 :
// 720 시간 = 30일
$hours = 720; // 설정 시간이 지난 주문서 없는 장바구니 자료 삭제

$cart_title1 = "쇼핑";
$cart_title2 = "완료";
$cart_title3 = "주문번호";
$cart_title4 = "배송완료";

auth_check($auth[$sub_menu], "w");

$g4['title'] = $html_title;
include_once $g4['admin_path']."/admin.head.php";

//------------------------------------------------------------------------------
// 설정 시간이 지난 주문서 없는 장바구니 자료 삭제
//------------------------------------------------------------------------------
/*if (!isset($cart_not_delete)) {
    if (!$hours) $hours = 6;
    $beforehours = date("Y-m-d H:i:s", ( $g4['server_time'] - (60 * 60 * $hours) ) );
    $sql = " delete from $g4[yc4_cart_table] where ct_status = '$cart_title1' and ct_time <= '$beforehours' ";
    sql_query($sql);
}*/
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// 주문완료 포인트
//      설정일이 지난 포인트 부여되지 않은 배송완료된 장바구니 자료에 포인트 부여
//      설정일이 0 이면 주문서 완료 설정 시점에서 포인트를 바로 부여합니다.
//------------------------------------------------------------------------------
/*if (!isset($order_not_point)) {
    $beforedays = date("Y-m-d H:i:s", ( time() - (60 * 60 * 24 * (int)$default[de_point_days]) ) );
    $sql = " select * from $g4[yc4_cart_table]
               where ct_status = '$cart_title2'
                 and ct_point_use = '0'
                 and ct_time <= '$beforedays' ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 회원 ID 를 얻는다.
        $tmp_row = sql_fetch("select od_id, mb_id from $g4[yc4_order_table] where on_uid = '$row[on_uid]' ");

        // 회원이면서 포인트가 0보다 크다면
        if ($tmp_row[mb_id] && $row[ct_point] > 0)
        {
            $po_point = $row[ct_point] * $row[ct_qty];
            $po_content = "$cart_title3 $tmp_row[od_id] ($row[ct_id]) $cart_title4";
            insert_point($tmp_row[mb_id], $po_point, $po_content, "@delivery", $tmp_row[mb_id], "$tmp_row[od_id],$row[on_uid],$row[ct_id]");
        }

        sql_query("update $g4[yc4_cart_table] set ct_point_use = '1' where ct_id = '$row[ct_id]' ");
    }
}*/
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = $db->ople_backup->query(" select * from $g4[yc4_order_table] where od_id = '$od_id' ");

$od = $sql->fetch_array(1);

if (!$od['od_id']) {
    alert($alt_msg1);
}


if ($od['mb_id'] == "") {
    $od['mb_id'] = $mb_guest;
}
$cd_history= $db->ople_db->query("select * from ".$g4['yc4_card_history_table']." where od_id = '".$od_id."'");
$cd_history = $cd_history->fetch_array();

//------------------------------------------------------------------------------
$qstr = "sort1=$sort1&sort2=$sort2&sel_field=$sel_field&search=$search&page=$page";


// 김선용 201208 : 주민번호 저장안함
// 김선용 200811 : 주민번호 별도저장
//sql_query("alter table {$g4['yc4_order_table']} add od_jumin varchar(13) not null", false);



// PG사를 KCP 사용하면서 테스트 상점아이디라면
if ($default['de_card_pg'] == 'kcp' && $default['de_kcp_mid'] == 'T0007')
    $g4['yc4_cardpg']['kcp'] = "http://admin.dev.kcp.co.kr"; // 로그인 아이디/비번 : escrow/escrow
else if ($default['de_card_pg'] == 'dacom' && $default['de_dacom_mid'] == 'tlinkret')
    $g4['yc4_cardpg']['dacom'] = "http://pgweb.dacom.net:7085/index.jsp"; // 로그인 아이디/비번 : tlinkret/tlinkret

$it_id_arr= $db->ople_backup->query("
        select
            a.ct_id,
            a.it_id,
            a.ct_qty,
            a.ct_amount,
            a.ct_point,
            a.ct_status,
            a.ct_time,
            a.ct_point_use,
            a.ct_stock_use,
            a.it_opt1,
            a.it_opt2,
            a.it_opt3,
            a.it_opt4,
            a.it_opt5,
            a.it_opt6,
            a.ct_amount_usd
        from
            yc4_cart a
        where
            a.on_uid = '$od[on_uid]'
");
$yc4_cart_arr = array();
while($it_id = $it_id_arr->fetch_array()){
    if($it_id['it_id']) {
        $it_id_in .= ($it_id_in ? "," : "") . "'" . $it_id['it_id'] . "'";
        $yc4_cart_arr[] = $it_id;
    }
}
$item_upc= $db->ople_db->query("
 SELECT DISTINCT b.upc
 from ople_mapping b
 where b.it_id in ({$it_id_in})
");

$upc_in = '';
while($row = $item_upc->fetch_array()){
	$upc_in .= ($upc_in ? "||":"") . $row['upc'];
}
# NTICS 재고량 출력 2015-02-12 홍민기 #
if($upc_in && $NTICS_DATA_ON){
	$post_data = array(
		"upc" => $upc_in
	);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $NTICS_DATA_URL);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, $_CURL_TIMEOUT_ );
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$json_data = curl_exec($ch);
	curl_close($ch);

	$json_data = json_decode($json_data);

	foreach($json_data as $upc => $val){
		$item_info[$upc]['location'] = $val->location;
		$item_info[$upc]['ITEM_NAME'] = $val->ITEM_NAME;
		$item_info[$upc]['ITEM_NAME_EXTRA'] = $val->ITEM_NAME_EXTRA;
		$item_info[$upc]['TYPE'] = $val->TYPE;
		$item_info[$upc]['COUNT'] = $val->COUNT;
		$item_info[$upc]['currentqty'] = $val->currentqty;
	}
}
$yc4_item= $db->ople_db->query("
 select
       b.it_id,
       b.it_name,
       b.it_health_cnt
from
       yc4_item b
where
       b.it_id in  ({$it_id_in})
");
$yc4_item_arr =array();
while($row = $yc4_item->fetch_array()){
    $yc4_item_arr[trim($row['it_id'])]  = $row;
}



$order_msg = '';
if($od['ihappy_fg']){
    $order_msg = "아이해피몰 주문건 입니다.";
}elseif($od['opk_fg'] == 'Y'){
    $order_msg = "오플코리아 주문서 입니다.";
}elseif($od['open_market_fg']){
    switch($od['open_market_fg']){
        case 'A' :
            $order_msg = "오픈마켓(옥션) 주문서 입니다.";
            break;
        case 'G' :
            $order_msg = "오픈마켓(G마켓) 주문서 입니다.";
            break;
    }
}

if($od['mobile_fg'] == 'Y'){
    $order_msg .= ($order_msg ? "<br/>":"")."모바일 주문 입니다.";
}

# 배송 DB에 넘어 갔는지 체크 #
$shipping_chk = $db->ntics_db->query("select count(*) as cnt from ntshipping.dbo.ns_s01 where on_uid = '{$od['on_uid']}'")->fetchObject()->cnt;

$shipping_update_chk = true;

if($shipping_chk){
    # invoice 테이블에 insert 되었는지 체크 #
    $invoice_chk = $db->ntics_db->query("select count(*) as cnt from ntshipping.dbo.ns_invoice where ordercode = 'k{$od_id}'")->fetchObject()->cnt;
    if($invoice_chk > 0){
        $shipping_update_chk = false;
    }
}

?>
<style type="text/css">
.item_name_etc_deatil{
	font-size: 13px;
	font-weight:normal;
}
</style>
<p><b style='color:#ff0000;'><?php echo $order_msg;?></b></p>
<p>
<table width=100% cellpadding=0 cellspacing=0>
	<tr>
        <td><?php echo subtitle("주문상품")?></td>
        <td align=right>
        <? if ($default['de_hope_date_use']) { ?>
            희망배송일은
            <b><?php echo $od['od_hope_date']?> (<?php echo get_yoil($od['od_hope_date'])?>)</b> 입니다.
        <? } ?>
        </td>
    </tr>
</table>


<form name=frmorderform method=post action='' style="margin:0px;">
<input type=hidden name=ct_status value=''>
<input type=hidden name=on_uid    value='<?php echo $od['on_uid'] ?>'>
<input type=hidden name=od_id     value='<?php echo $od_id ?>'>
<input type=hidden name=mb_id     value='<?php echo $od['mb_id'] ?>'>
<input type=hidden name=od_email  value='<?php echo $od['od_email'] ?>'>
<input type=hidden name=sort1 value="<?php echo $sort1 ?>">
<input type=hidden name=sort2 value="<?php echo $sort2 ?>">
<input type=hidden name=sel_field  value="<?php echo $sel_field ?>">
<input type=hidden name=search     value="<?php echo $search ?>">
<input type=hidden name=page       value="<?php echo $page ?>">

<table width=100% cellpadding=0 cellspacing=0 class='list_styleAD'>
<thead>
	<tr>
		<td width=40>전체 <span style='vertical-align:middle;'><!--<input type=checkbox onclick='select_all();'>--></span></td>
		<td>상품명</td>
		<td width=60>상태</td>
		<td width=35>수량</td>
		<td width=75>판매가</td>
		<td width=75>소계</td>
		<td width=75>포인트</td>
		<td width=60>포인트반영</td>
		<td width=60>건기식<br/>주문수량<br/>단위수량</td>
	</tr>
</thead>
<tbody>
<?
$image_rate = 2.5;
$tot_health_cnt = 0;
/*for ($i=0; $row=sql_fetch_array($result); $i++)
{*/
foreach($yc4_cart_arr as $i => $row){
    if(isset($yc4_item_arr[trim($row['it_id'])])){
        $row['it_name'] = $yc4_item_arr[trim($row['it_id'])]['it_name'];
        $row['it_health_cnt'] = $yc4_item_arr[trim($row['it_id'])]['it_health_cnt'];
    }else{
        continue;
    }
//    $ct_amount_usd = $row['ct_amount_usd'] ? $row['ct_amount_usd'] : usd_convert($row['ct_amount'],$od['exchange_rate']);
    $ct_amount_usd = $row['ct_amount_usd'] ? $row['ct_amount_usd'] : usd_convert($row['ct_amount']);

	# 매핑된 상품인지 체크 #
	$ntics_info = '';
    $mapping_chk = $db->ople_db->query("select upc,qty from ople_mapping where it_id = '".$row['it_id']."'");
	while($row2 = $mapping_chk->fetch_array()){
		$ntics_info = "
			<p>UPC : ".$row2['upc']." x ".$row2['qty']."ea / NTICS QTY : ".number_format($item_info[$row2['upc']]['currentqty'])."</p>
		";
	}
	if($ntics_info){
		$ntics_info = "
			<tr>
				<td></td>
				<td colspan='8' style='text-align:left;'>".$ntics_info."</td>
			</tr>
		";
	}

    $it_name = "<a href='./itemform.php?w=u&it_id=$row[it_id]'>".stripslashes(get_item_name($row[it_name],'detail'))."</a><br>";
    $it_name .= print_item_options($row['it_id'], $row['it_opt1'], $row['it_opt2'], $row['it_opt3'], $row['it_opt4'], $row['it_opt5'], $row['it_opt6']);

    $ct_amount['소계'] = $row['ct_amount'] * $row['ct_qty'];
    $ct_amount['소계_usd'] = $ct_amount_usd * $row['ct_qty'];
    $ct_point['소계'] = $row['ct_point'] * $row['ct_qty'];
    if ($row['ct_status']=='주문' || $row['ct_status']=='준비' || $row['ct_status']=='배송' || $row['ct_status']=='완료')
        $t_ct_amount['정상'] += $row['ct_amount'] * $row['ct_qty'];
    else if ($row['ct_status']=='취소' || $row['ct_status']=='반품' || $row['ct_status']=='품절')
        $t_ct_amount['취소'] += $row['ct_amount'] * $row['ct_qty'];

    $image = get_it_image("$row[it_id]_s", (int)($default['de_simg_width'] / $image_rate), (int)($default['de_simg_height'] / $image_rate), $row['it_id']);

    $list = $i%2;
//<input type=checkbox id='ct_chk_{$i}' name='ct_chk[{$i}]' value='1'> 제거
    echo "
    <tr>
        <td title='$row[ct_id]'><input type='hidden' name='bf_ct_status[$i]' value='".$row['ct_status']."'/><input type=hidden name=ct_id[$i] value='$row[ct_id]'></td>
        <td class='ADlist_itemBox' style='padding:0'>
			<table width='100%'>
				<tr>
					<td width='80' style='padding:0'>$image</td>
					<td style='padding:0; text-align:left;'>$it_name</td>
				</tr>
			</table>
		</td>
        <td>$row[ct_status]</td>
        <td>$row[ct_qty]</td>
        <td>
			￦ ".number_format($row['ct_amount'])." <br />
			($ ".number_format($ct_amount_usd,2).")
		</td>
        <td>
			￦ ".number_format($ct_amount['소계'])." <br />
			($ ".number_format($ct_amount['소계_usd'],2).")
		</td>
        <td>".number_format($ct_point['소계'])."</td>
        <td>".get_yn($row['ct_point_use'])."</td>
        <td>
            ".($row['it_health_cnt'] * $row['ct_qty'])."<br/>".$row['it_health_cnt']."<br/>
        </td>";
    echo "</tr>"; // <a href='$g4[shop_admin_path]/item_health_cnt.php?it_id=".$row['it_id']."' target='_blank'>수정</a> 버튼 제거
	echo $ntics_info;
    $tot_health_cnt += $row['it_health_cnt'] * $row['ct_qty'];

    $t_ct_amount['합계'] += $ct_amount['소계'];
    $t_ct_point['합계'] += $ct_point['소계'];
}
?>
<tr class='ADlist_resultBox'>
    <td colspan=3 style='text-align:left;padding-left:20px;'>
        <!--<a href="javascript:form_submit('주문')">주문</a> |
        <?php /*if($_SESSION['ss_mb_id']=='okyo' || $_SESSION['ss_mb_id']=='dev'){*/?>
        <a href="javascript:form_submit('준비')">상품준비중</a> |
        <?php /*} */?>
        <a href="javascript:form_submit('배송')">배송중</a> |
        <a href="javascript:form_submit('완료')">완료</a> |
        <a href="javascript:form_submit('취소')">취소</a> |
        <a href="javascript:form_submit('반품')">반품</a> |
        <a href="javascript:form_submit('품절')">품절</a>
        --><?php /*echo help("한 주문에 여러가지의 상품주문이 있을 수 있습니다.\n\n상품을 체크하여 해당되는 상태로 설정할 수 있습니다.");*/?>
    </td>
    <td colspan=3>주문일시 : <?php echo substr($od[od_time],0,16)?> (<?php echo get_yoil($od[od_time]);?>)</td>
    <td colspan=3>
        <input type=hidden name="chk_cnt" value="<? echo $i ?>">

		<?php
		// 2011.07.01 이후 주문서는 배송비 출력. 1107130207
		// 김선용 201107 : root 계정일때 배송비제로. 상품합계에 합산
		$edit_ct_amount = ($member['mb_id'] == 'root' && substr($od_id,0,6) < 110701 ? $od['od_send_cost'] : 0);
		?>
		<b>주문합계 : <? echo number_format($t_ct_amount['합계']+$edit_ct_amount); ?>원</B>

    </td>


	    <? //echo number_format($t_ct_point[합계]); ?>
</tr>
</tbody>
</table>
    <p><b>건기식 병수 합계 : <?php echo number_format($tot_health_cnt)?></b></p>
<?php if($od['od_receipt_point']){?>
    <p><input type="checkbox" name="point_return" value="1"/> 취소시 포인트 환불</p>
<?php }?>
</form>
    <p><b style='color:#ff0000;'><?php echo $order_msg;?></b></p>
<?php
// 김선용 201207 : 사은품
if($od['od_gift_id'] != ''){
	$gi_str = array();
	$gi_id = explode(";", $od['od_gift_id']);
	for($k=0; $k<count($gi_id); $k++){
		$gi = sql_fetch("select gift_title from {$g4['yc4_gift_table']} where gift_id='{$gi_id[$k]}' ");
		$gi_str[] = stripslashes($gi['gift_title']);
	}
	echo "<p style='margin:4px; padding:4px; border:1px solid #ff0000;'><b>※ 사은품 이벤트 주문서 입니다.</b><br/>".implode("<br/>", $gi_str)."</p>";
}
?>
<?php if($shipping_chk){?>
    <h3><strong>배송 전산에 입력 된 주문서 입니다.</strong></h3>
    <?php if($invoice_chk){?>
        <h3><strong style="color:#ff0000;">송장이 출력된 주문서 입니다. 배송정보 수정 불가</strong></h3>
    <?php }?>
<?php }?>
<br>
<br>

<?php echo subtitle("주문결제")?>


<?php


// 주문금액 = 상품구입금액 + 배송비
$amount['정상'] = $t_ct_amount['정상'] + $od[od_send_cost];

// 김선용 201209 : 추천인할인
// 입금액 = 무통장 + 신용카드 + 포인트
$amount['입금'] = $od[od_receipt_bank] + $od[od_receipt_card] + $od[od_receipt_point] + $od['od_recommend_off_sale'];

// 미수금 = (주문금액 - DC + 환불액) - (입금액 - 신용카드승인취소)
$amount['미수'] = ($amount['정상'] - $od[od_dc_amount] + $od[od_refund_amount]) - ($amount['입금'] - $od[od_cancel_card]);



// 결제방법
$s_receipt_way = $od[od_settle_case];

if ($od[od_receipt_point] > 0)
    $s_receipt_way .= "+포인트";
?>


<table width=100% cellpadding=0 cellspacing=0 class='list_styleAD'>
<!-- on_uid : <?php echo $od[on_uid] ?> -->
<thead>
<tr>
	<th>주문번호</th>
	<th>결제방법</th>
	<th>주문총액</th>
	<th>포인트결제액</th>
	<th>추천인할인</th>
	<th>결제액(포인트포함)</th>
	<th>DC</th>
	<th>환불액</th>
	<th>주문취소</th>
</tr>
</thead>
<tbody>
<tr>
    <td><?php echo $od[od_id] ?></td>
	<td><?php echo $s_receipt_way ?></td>
	<td><?php echo display_amount($amount['정상']) ?></td>
	<td><?php echo display_point($od[od_receipt_point]); ?></td>
	<td><?php echo nf($od['od_recommend_off_sale'])?></td>
	<td><?php echo number_format($amount['입금']); ?>원</td>
    <td><?php echo display_amount($od[od_dc_amount]); ?></td>
    <td><?php echo display_amount($od[od_refund_amount]); ?></td>
	<td><?php echo number_format($t_ct_amount['취소']) ?>원</td>
</tr>
<tr><td colspan=10 style='color:#FF6600;text-align:right;border-top:solid 1px #666;'><strong>미수금 : <?php echo display_amount($amount['미수']) ?></strong></td></tr>
</tbody>
</table>
<?php
echo "<b>";
echo "주문자 ID : ";
echo $od['mb_id'] ? $od['mb_id'] : '비회원';
echo "</b>";
?>

<p>
<form name=frmorderreceiptform method=post action="./orderreceiptupdate.php" autocomplete=off style="margin:0px;">
<input type=hidden name=od_id     value="<?php echo $od_id?>">
<input type=hidden name=sort1     value="<?php echo $sort1?>">
<input type=hidden name=sort2     value="<?php echo $sort2?>">
<input type=hidden name=sel_field value="<?php echo $sel_field?>">
<input type=hidden name=search    value="<?php echo $search?>">
<input type=hidden name=page      value="<?php echo $page?>">
<input type=hidden name=od_name   value="<?php echo $od[od_name]?>">
<input type=hidden name=od_hp     value="<?php echo $od[od_hp]?>">
<input type="hidden" name="od_ship" value="<?php echo $od['od_ship']?>" /> <!-- // 김선용 201211 : -->
<br/>
<br/>
<table border=0 cellpadding=0 cellspacing=0 width=100%>
<tr>
    <td width=49% valign=top>

        <?php echo subtitle("결제상세정보")?>
        <table width=100% cellpadding=0 cellspacing=0 class='list_styleAD2'>

        <?php if ($od[od_settle_case] != '신용카드') { ?>
            <?
            if ($od[od_settle_case] == '무통장' || $od[od_settle_case] == '가상계좌')
            {
                echo "<tr>";
                echo "<th>계좌번호</th>";
                echo "<td>".$od[od_bank_account]."</td>";
                echo "</tr>";
            }
            ?>
            <tr>
                <th><?php echo $od[od_settle_case]?> 입금액</th>
                <td>
                <?php
                    if ($od[od_receipt_bank] > 0) {
                        echo "" . display_amount($od[od_receipt_bank]);
                    }elseif($od[od_receipt_card] > 0){
                        echo "" . display_amount($od[od_receipt_card]);
                    } else {
                        echo "0원";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>입금자</th>
                <td><? echo $od[od_deposit_name] ?></td>
            </tr>
            <tr>
                <th>입금확인일시</th>
                <td>
                <?php
                    if ($od[od_bank_time] == "0000-00-00 00:00:00") {
                        echo "입금 확인일시를 체크해 주세요.";
                    } else {
                        echo " " . substr($od[od_bank_time], 0, 16);
                    }
                  ?>
                </td>
            </tr>
        <?php } ?>


        <? if ($od[od_settle_case] == '신용카드') { ?>
        <tr>
            <th width='150px'>신용카드 입금액</th>
            <td>
            <?
                if ($od[od_card_time] == "0000-00-00 00:00:00")
                    echo "0원";
                else
                    echo display_amount($od[od_receipt_card]).' / $ '.$cd_history['cd_amount_usd'];
            ?>
            </td>
        </tr>
		<tr>
			<th>카드 승인일시</th>
			<td>
            <?
                if ($od[od_card_time] == "0000-00-00 00:00:00")
                    echo "신용카드 결제 일시 정보가 없습니다.";
                else
                {
                    echo "" . substr($od[od_card_time], 0, 20);
                }
            ?>
			</td>
		</tr>
        <tr>
            <th>카드 종류</th>
            <td><?php echo $cd_history['cd_card_name']?></td>
        </tr>
            <tr>
                <th>카드번호 앞 6자리</th>
                <td><?php echo $cd_history['cd_card_bin']?></td>
            </tr>
        <tr>
            <th>카드 승인취소</th>
            <td><? echo display_amount($od[od_cancel_card]); ?></td>
        </tr>
		<?php
			if( $cd_history['cd_conv_pay'] > 0 ){
		?>
		<tr>
			<th>결제시 환율</th>
			<td><? echo display_amount($cd_history['cd_conv_pay']);?></td>
		</tr>
		<?php
			}
		?>
        <? } ?>

        <tr class='listAD_line'>
            <th>포인트</th>
            <td><? echo display_point($od[od_receipt_point]); ?></td>
        </tr>
        <tr>
            <th>DC</th>
            <td><? echo display_amount($od[od_dc_amount]); ?></td>
        </tr>
        <tr>
            <th>환불액</th>
            <td><? echo display_amount($od[od_refund_amount]); ?></td>
        </tr>
        <?
        $sql =$db->ople_db->query(" select dl_company, dl_url, dl_tel from $g4[yc4_delivery_table] where dl_id = '$od[dl_id]' ");
        $dl =$sql->fetch_array();
        ?>
        <tr class='listAD_line'>
            <th>배송회사</th>
			<td>
	        <?
            if ($od[dl_id] > 0) {
                // get 으로 날리는 경우 운송장번호를 넘김
                if (strpos($dl[dl_url], "=")) $invoice = $od[od_invoice];
                echo "<a href='$dl[dl_url]{$invoice}' target=_new>$dl[dl_company]</a> &nbsp;&nbsp;(고객센터 : $dl[dl_tel]) ";
            } else
                echo "배송회사를 선택해 주세요.";
			?>
			</td>
        </tr>
        <tr >
            <th>운송장번호</th>
            <td><? echo $od[od_invoice] ?>&nbsp;</td>
        </tr>
        <tr>
            <th>배송일시</th>
            <td><? echo $od[od_invoice_time] ?>&nbsp;</td>
        </tr>
        <tr>
            <th>주문자 배송비</th>
            <!-- <td><? echo number_format($od[od_send_cost]) ?>원</td> -->
			<?// 김선용 201107 : root 계정일때 배송비제로. 상품합계에 합산
			$edit_send_cost = ($member['mb_id'] == 'root' && substr($od_id,0,6) < 110701 ? 0 : $od['od_send_cost']);
			?>
            <td><input type=text name='od_send_cost' value='<?php echo $edit_send_cost?>' class=ed size=10 style='text-align:right;'>원
                <?php echo help("주문취소시 배송비는 취소되지 않으므로 이 배송비를 0으로 설정하여 미수금을 맞추십시오.");?></td>
        </tr>
        </table>
    </td>
    <td width=1%> </td>
    <td width=50% valign=top align=center>

        <?php echo subtitle("결제상세정보 수정")?>
        <table width=100% cellpadding=0 cellspacing=0 class='list_styleAD2'>
        <? if ($od[od_settle_case] != '신용카드') { ?>
            <?
            // 주문서
            $sql = $db->ople_backup->query(" select * from $g4[yc4_order_table] where od_id = '$od_id' ");

            $od = $sql->fetch_array();

            if ($od['od_settle_case'] == '무통장')
            {
                // 은행계좌를 배열로 만든후
                $str = explode("\n", $default[de_bank_account]);
                $bank_account = "\n<select name=od_bank_account>\n";
                $bank_account .= "<option value=''>------------ 선택하십시오 ------------\n";
                for ($i=0; $i<count($str); $i++) {
                    $str[$i] = str_replace("\r", "", $str[$i]);
                    $bank_account .= "<option value='$str[$i]'>$str[$i] \n";
                }
                $bank_account .= "</select> ";
            }
            else if ($od['od_settle_case'] == '가상계좌')
                $bank_account = $od[od_bank_account];
            else if ($od['od_settle_case'] == '계좌이체')
                $bank_account = $od['od_settle_case'];
            ?>

            <?
            if ($od[od_settle_case] == '무통장' || $od[od_settle_case] == '가상계좌')
            {
                echo "<tr align='left'>";
                echo "<th>계좌번호</th>";
                echo "<td>$bank_account</td>";
                echo "</tr>";
            }

            if ($od[od_settle_case] == '무통장')
                echo "<script> document.frmorderreceiptform.od_bank_account.value = '".str_replace("\r", "", $od[od_bank_account])."'; </script>";
            ?>
            <tr align='left'>
                <th width=150><?php echo $od[od_settle_case]?> 입금액</th>
                <td>
                    <input type=text class=ed name=od_receipt_bank size=10
                        value='<? echo $od[od_receipt_bank] ?>'>원
                    <?
                    if ($od['od_settle_case'] == '계좌이체' || $od['od_settle_case'] == '가상계좌')
                    {
                        $pg_url = $g4['yc4_cardpg'][$default['de_card_pg']];
                        echo "&nbsp;<a href='$pg_url' target=_new>결제대행사</a>";
                    }
                    ?>
                </td>
            </tr>
            <tr align='left'>
                <th>입금자명</th>
                <td>
                    <input type=text class=ed name=od_deposit_name
                        value='<? echo $od[od_deposit_name] ?>'>
                    <? if ($default[de_sms_use3]) { ?>
                        <input type=checkbox name=od_sms_ipgum_check> SMS 문자전송
                    <? } ?>
                </td>
            </tr>
            <tr align='left'>
                <th>입금 확인일시</th>
                <td>
                    <input type=text class=ed name=od_bank_time maxlength=19 value='<? echo is_null_time($od[od_bank_time]) ? "" : $od[od_bank_time]; ?>'>
                    <input type=checkbox name=od_bank_chk
                        value="<? echo date("Y-m-d H:i:s", $g4['server_time']); ?>"
                        onclick="if (this.checked == true) this.form.od_bank_time.value=this.form.od_bank_chk.value; else this.form.od_bank_time.value = this.form.od_bank_time.defaultValue;">현재 시간
                </td>
            </tr>
        <? } ?>

        <? if ($od[od_settle_case] == '신용카드') { ?>
        <tr align='left'>
            <th width='150px'>신용카드 결제액</th>
            <td>
                <input type=text class=ed name=od_receipt_card size=10
                    value='<? echo $od[od_receipt_card] ?>'>원
                &nbsp;
                <?
                $card_url = $g4[yc4_cardpg][$default[de_card_pg]];
                ?>
                <a href='<? echo $card_url ?>' target=_new>결제대행사</a>
            </td>
        </tr>
        <tr align='left'>
            <th>카드 승인일시</th>
            <td>
                <input type=text class=ed name=od_card_time size=19 maxlength=19 value='<? echo is_null_time($od[od_card_time]) ? "" : $od[od_card_time]; ?>'>
                <input type=checkbox name=od_card_chk
                    value="<? echo date("Y-m-d H:i:s", $g4['server_time']); ?>"
                    onclick="if (this.checked == true) this.form.od_card_time.value=this.form.od_card_chk.value; else this.form.od_card_time.value = this.form.od_card_time.defaultValue;">현재 시간
            </td>
        </tr>
        <tr align='left'>
            <th>카드 승인취소</th>
            <td>
                <input type=text class=ed name=od_cancel_card size=10 value='<? echo $od[od_cancel_card] ?>'>원
            </td>
        </tr>
        <? } ?>

        <tr class='listAD_line' align='left'>
            <th>포인트 결제액</th>
            <td>
                <input type=text class=ed name=od_receipt_point size=10 value='<? echo $od[od_receipt_point] ?>'>점
            </td>
        </tr>
        <tr align='left'>
            <th>DC</th>
            <td>
                <input type=text class=ed name=od_dc_amount size=10 value='<? echo $od[od_dc_amount] ?>'>원
            </td>
        </tr>
        <tr align='left'>
            <th>환불액</th>
            <td>
                <input type=text class=ed name=od_refund_amount size=10 value='<? echo $od[od_refund_amount] ?>'>원
                <?php echo help("카드승인취소를 입력한 경우에는 중복하여 입력하면 미수금이 틀려집니다.", 0, -100);?>
            </td>
        </tr>

        <tr class='listAD_line' align='left'>
            <th>배송회사</th>
            <td>
                <select name=dl_id>
                    <option value=''>배송시 선택하세요.
                <?
                $result = $db->ople_db->query("select * from $g4[yc4_delivery_table] order by dl_order desc, dl_id desc ");
                for ($i=0; $row=$result->fetch_array(); $i++)
                    echo "<option value='$row[dl_id]'>$row[dl_company]\n";
                mysql_free_result($result);
                ?>
                </select>
        </tr>
        <tr align='left'>
            <th>운송장번호</th>
            <td><input type=text class=ed name=od_invoice
                value='<? echo $od[od_invoice] ?>'>
                <? if ($default[de_sms_use4]) { ?>
                    <input type=checkbox name=od_sms_baesong_check> SMS 문자전송
                <? } ?>
            </td>
        </tr>
        <tr align='left'>
            <th>배송일시</th>
            <td>
                <input type=text class=ed name=od_invoice_time maxlength=19 value='<? echo is_null_time($od[od_invoice_time]) ? "" : $od[od_invoice_time]; ?>'>
                <input type=checkbox name=od_invoice_chk
                    value="<? echo date("Y-m-d H:i:s", $g4['server_time']); ?>"
                    onclick="if (this.checked == true) this.form.od_invoice_time.value=this.form.od_invoice_chk.value; else this.form.od_invoice_time.value = this.form.od_invoice_time.defaultValue;">현재 시간
            </td>
        </tr>
        <tr align='left'>
            <th>메일발송</th>
            <td>
                <input type=checkbox name=od_send_mail value='1'>예
                <?php echo help("주문자님께 입금, 배송내역을 메일로 발송합니다.\n\n메일발송후 상점메모에 메일발송 시간을 남겨 놓습니다.");?>
            </td>
        </tr>
        <tr align='left'>
            <th>통관고유부호</th>
            <td>
                <input type='text' name='od_b_jumin' value='<?php echo $od['od_b_jumin']?>'>
            </td>
        </tr>
        </table>

        <?
        if ($od[dl_id] > 0)
            echo "<script language='javascript'> document.frmorderreceiptform.dl_id.value = '$od[dl_id]' </script>";
        ?>
		</td>
		</tr>
		<tr>
			<td colspan=3 style='text-align:center;padding:20px 0;'>
				<!--<input type=submit class=btn1 value='결제/배송내역 수정'>&nbsp;-->
				<input type=button class=btn1 value='  목  록  ' onclick="document.location.href='./orderlist2yearago.php?<?php echo $qstr?>';">
			</td>
		</tr>
</table>
</form>

<?php echo subtitle("상점메모")?>
<form name='frmorderform2' method=post action="./orderformupdate.php" style="margin:0px;">
<input type=hidden name=od_id     value="<?php echo $od_id?>">
<input type=hidden name=sort1     value="<?php echo $sort1?>">
<input type=hidden name=sort2     value="<?php echo $sort2?>">
<input type=hidden name=sel_field value="<?php echo $sel_field?>">
<input type=hidden name=search    value="<?php echo $search?>">
<input type=hidden name=page      value="<?php echo $page?>">
<input type="hidden" name="od_ship" value="<?php echo $od['od_ship']?>" /> <!-- // 김선용 201211 : -->


<table width=100% cellpadding=0 cellspacing=0 style='margin-top:20px;'>
<tr>
	<td><textarea name="od_shop_memo" rows=8 style='width:99%;' class=ed><? echo stripslashes($od[od_shop_memo]) ?></textarea></td>
</tr>
<tr>
	<td style='padding:15px 0;text-align:center;'>
        <!--<input type=submit class=btn1 value='메모 수정'>--> <?php echo help("이 주문에 대해 일어난 내용을 메모하는곳입니다.\n\n위에서 메일발송한 내역도 이곳에 저장합니다.", -150);?>
    </td>
</tr>
</table>

<p><?php echo subtitle("주소정보")?>
<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
    <td width=49%>
        <table width=100% cellpadding=0 cellspacing=0 class='list_styleAD2'>
        <tr>
            <td colspan=2 style='border-bottom:solid 1px #ccc;'><strong>주문하신 분</strong></td>
        </tr>
        <tr>
            <th width='150px'>이름</th>
            <td><input type=text class=ed name=od_name value='<?php echo $od[od_name]?>' required itemname='주문하신 분 이름'></td>
		</tr>

		<!--
		<?
		// 김선용 201209 : 주민번호 저장안함
		// 김선용 200811 : 주민번호 처리
		/*
		if($od['mb_id']){
			$temp_mb = get_member($od['mb_id'], "mb_jumin2");
			$jumin = $temp_mb['mb_jumin2'];
		}
		else
			$jumin = $od['od_jumin'];
		*/
		?>
		<tr>
			<th>주민번호</th>
			<td><?php echo substr($jumin,0,6)?>-<?php echo substr($jumin,6)?></td>
		</tr>
		-->

        <tr>
            <th>전화번호</th>
            <td><input type=text class=ed name=od_tel value='<?php echo $od[od_tel]?>' required itemname='주문하신 분 전화번호'></td>
		</tr>
		<tr>
            <th>핸드폰</th>
            <td><input type=text class=ed name=od_hp value='<?php echo $od[od_hp]?>'></td>
        </tr>
        <tr>
            <th>주소</th>
            <td>
                <p><input type=text class=ed name=od_zip1 size=4 readonly  itemname='우편번호 앞자리' value='<?php echo $od[od_zip1]?>'> -
                <input type=text class=ed name=od_zip2 size=4 readonly  itemname='우편번호 뒷자리' value='<?php echo $od[od_zip2]?>'>
                &nbsp;<a href="javascript:;" onclick="win_zip('frmorderform2', 'od_zip1', 'od_zip2', 'od_addr1', 'od_addr2', 'od_addr_jibeon', 'od_zonecode');"><img src="<?php echo $g4[shop_admin_path]?>/img/btn_zip_find.gif" border=0 align=absmiddle></a></p>
                <p><input type=text class=ed name=od_addr1 size=50 readonly  itemname='주소' value='<?php echo $od[od_addr1]?>'></p>
                <p><input type=text class=ed name=od_addr2 size=50  itemname='상세주소' value='<?php echo $od[od_addr2]?>'></p>
				<p><input type='hidden' name='od_addr_jibeon' value='<?php echo $od['od_addr_jibeon']?>'/></p>
				<p><span id='od_addr_jibeon'><?if($od['od_addr_jibeon']){?>지번주소 : <?php echo $od['od_addr_jibeon'];?><?}?></span></p>
				<input type="hidden" name='od_zonecode' value="<?php echo $od['od_zonecode'];?>" />

			</td>
        </tr>
		<tr>
            <th>E-mail</th>
            <td><input type=text class=ed name=od_email size=30 email  itemname='주문하신 분 E-mail' value='<?php echo $od[od_email]?>'></td>
        </tr>
		<tr>
            <th>IP Address</th>
            <td><?php echo $od[od_ip]?></td>
        </tr>
        </table>
    </td>
    <td width=1%></td>
    <td width=50% valign=top align=center>
		<table width=100% cellpadding=0 cellspacing=0 class='list_styleAD2'>
        <tr align='left'>
            <td colspan=2 style='border-bottom:solid 1px #ccc;'><strong>받으시는 분</strong></td>
        </tr>

	<!-- // 김선용 201211 : 복수배송처리 -->
	<?if($od['od_ship'] == '0'){?>
        <tr align='left'>
            <th>이름</th>
            <td><input type=text class=ed name=od_b_name value='<?php echo $od[od_b_name]?>' required itemname='받으시는 분 이름'></td>
        </tr>
        <tr align='left'>
            <th>전화번호</th>
            <td><input type=text class=ed name=od_b_tel value='<?php echo $od[od_b_tel]?>' required itemname='받으시는 분 전화번호'></td>
		</tr>
		<tr align='left'>
            <th>핸드폰</th>
            <td><input type=text class=ed name=od_b_hp value='<?php echo $od[od_b_hp]?>'></td>
        </tr>
        <tr align='left'>
            <th>주소</th>
            <td>
                <p><input type=text class=ed name=od_b_zip1 size=4 readonly required itemname='우편번호 앞자리' value='<?php echo $od[od_b_zip1]?>'> -
                <input type=text class=ed name=od_b_zip2 size=4 readonly required itemname='우편번호 뒷자리' value='<?php echo $od[od_b_zip2]?>'>
                &nbsp;<a href="javascript:;" onclick="win_zip('frmorderform2', 'od_b_zip1', 'od_b_zip2', 'od_b_addr1', 'od_b_addr2', 'od_b_addr_jibeon', 'od_b_zonecode');"><img src="<?php echo $g4[shop_admin_path]?>/img/btn_zip_find.gif" border=0 align=absmiddle></a></p>
                <p><input type=text class=ed name=od_b_addr1 size=50 readonly required itemname='주소' value='<?php echo $od[od_b_addr1]?>'></p>
                <p><input type=text class=ed name=od_b_addr2 size=50  itemname='상세주소' value='<?php echo $od[od_b_addr2]?>'></p>
				<p><input type='hidden' name='od_b_addr_jibeon' value='<?php echo $od['od_b_addr_jibeon']?>'/></p>
				<p><span id='od_b_addr_jibeon'><?if($od['od_b_addr_jibeon']){?>지번주소 : <?php echo $od['od_b_addr_jibeon'];?><?}?></span></p>
				<input type="hidden" name='od_b_zonecode' value="<?php echo $od['od_b_zonecode'];?>" />
			</td>

        </tr>
        <? if ($default[de_hope_date_use]) { ?>
        <tr align='left'>
            <th>희망배송일</th>
            <td>
                <input type=text class=ed name=od_hope_date value='<?php echo $od[od_hope_date]?>' maxlength=10 minlength=10 required itemname='희망배송일'>
                (<?php echo get_yoil($od[od_hope_date])?>)</td>
		</tr>
        <? } ?>

        <tr align='left'>
            <th>전하는 말씀</td>
            <td><?php echo nl2br($od[od_memo])?></td>
        </tr>
	<?}else if($od['od_ship'] == '1'){?>
		<?
		$os_sql = $db->ople_db->query("select * from {$g4['yc4_os_table']} where on_uid='{$od['on_uid']}' and od_id='$od_id' order by os_pid ");

		for($k=0; $row=$os_sql->fetch_array(); $k++)
		{
		?>
			<input type="hidden" name="os_pid[]" value="<?php echo $row['os_pid']?>" />
		   <tr align='left'>
				<th>보내는사람</th>
				<td><input type=text class=ed name='os_post_name[]' value='<?php echo $row['os_post_name']?>' required itemname='보내는사람'>&nbsp;&nbsp;<a href="javascript:;" onclick="view_ship_item('<?php echo $row['os_pid']?>');" title='배송상품보기'>배송상품보기</a></td>
			</tr>
		   <tr align='left'>
				<th>이름</th>
				<td><input type=text class=ed name='os_name[]' value='<?php echo $row['os_name']?>' required itemname='받으시는 분 이름'></td>
			</tr>
			<tr align='left'>
				<th>전화번호</th>
				<td><input type=text class=ed name='os_tel[]' value='<?php echo $row['os_tel']?>' required itemname='받으시는 분 전화번호'></td>
			</tr>
			<tr align='left'>
				<th>핸드폰</th>
				<td><input type=text class=ed name='os_hp[]' value='<?php echo $row['os_hp']?>'></td>
			</tr>
			<tr align='left'>
				<th>주소</th>
				<td>
					<p><input type=text class=ed name='os_zip1[]' size=4 readonly required itemname='우편번호 앞자리' value='<?php echo $row['os_zip1']?>'> -
					<input type=text class=ed name='os_zip2[]' size=4 readonly required itemname='우편번호 뒷자리' value='<?php echo $row['os_zip2']?>'>
					&nbsp;<a href="javascript:;" onclick="win_zip('frmorderform2', 'os_zip1[]', 'os_zip2[]', 'os_addr1[]', 'os_addr2[]', '<?php echo $k?>');"><img src="<?php echo $g4[shop_admin_path]?>/img/btn_zip_find.gif" border=0 align=absmiddle></a></p>
					<p><input type=text class=ed name='os_addr1[]' size=50 readonly required itemname='주소' value='<?php echo $row['os_addr1']?>'></p>
					<p><input type=text class=ed name='os_addr2[]' size=50 required itemname='상세주소' value='<?php echo $row['os_addr2']?>'></p></td>
			</tr>
			<? if ($default[de_hope_date_use]) { ?>
			<tr align='left'>
				<th>희망배송일</th>
				<td>
					<input type=text class=ed name=od_hope_date value='<?php echo $od[od_hope_date]?>' maxlength=10 minlength=10 required itemname='희망배송일'>
					(<?php echo get_yoil($od[od_hope_date])?>)</td>
			</tr>
			<? } ?>
			<tr align='left'>
				<th>전하는 말씀</th>
				<td><?php echo nl2br($row['os_memo'])?></td>
			</tr>
			<tr align='left'>
				<th>배송회사</th>
				<td>
					<select name='os_dl_id[]'>
						<option value=''>배송시 선택하세요.</option>
						<?
                        $result2 = $db->ople_db->query("select * from $g4[yc4_delivery_table] order by dl_order desc, dl_id desc ");

						for ($i=0; $row2=$result2->fetch_array(); $i++)
							echo "<option value='$row2[dl_id]' ".($row2['dl_id'] == $row['os_dl_id'] ? 'selected' : '').">$row2[dl_company]</option>";
						?>
					</select>
			</tr>
			<tr align='left'>
				<th>운송장번호</th>
				<td><input type=text class=ed name='os_invoice[]' value='<? echo $row[os_invoice] ?>'>
					<? if ($default[de_sms_use4]) { ?>
						<input type=checkbox name='os_send_sms[]' value=1 /> SMS 문자전송
					<? } ?>
				</td>
			</tr>
			<tr align='left'>
				<th>배송일시</th>
				<td>
					<input type=text class=ed name='os_invoice_time[]' id='os_invoice_time[<?php echo $k?>]' maxlength=19 value='<? echo is_null_time($row[os_invoice_time]) ? "" : $row[os_invoice_time]; ?>'>
					<label><input type=checkbox value="<? echo $g4['time_ymdhis']; ?>"
						onclick="if(this.checked) get_id('os_invoice_time[<?php echo $k?>]').value=this.value; else get_id('os_invoice_time[<?php echo $k?>]').value = get_id('os_invoice_time[<?php echo $k?>]').defaultValue;">현재 시간</label>
				</td>
			</tr>
			<tr align='left'>
				<th>메일발송</th>
				<td>
					<label><input type=checkbox name='os_send_mail[]' value='1' />예</label>
					<?php echo help("주문자님께 입금, 배송내역을 메일로 발송합니다.\n\n메일발송후 상점메모에 메일발송 시간을 남겨 놓습니다.");?>
				</td>
			</tr>
		<?}?>
	<?}?>

		</table>
    </td>
</tr>
</table>


<!-- // 김선용 201211 : 복수배송상품정보-->
<div id=_dis_view_item_ style='display:none; position:absolute; z-index:999; width:600px; height:500px; background-color:white; border:1px solid black; border-collapse:collapse; padding:10px; overflow:auto;'></div>
<script type='text/javascript'>
function view_ship_item(os_pid)
{
	$.ajax({
		type: 'POST',
		url: g4_path+'/shop/orderform.jquery.php',
		data: { 's_type' : 'view', 'os_pid' : os_pid, 'tmp_on_uid' : '<?php echo $od['on_uid']?>' },
		cache: false,
		async: false,
		success: function(result) {
			var id = '#_dis_view_item_';
			$(id).html(result);
			$(id).css({
				'left': (($(window).width() - $(id).width())/2 + $(window).scrollLeft()) - 50 + 'px',
				'top': (($(window).height() - $(id).height())/2 + $(window).scrollTop()) + 'px'
			});//.fadeIn();
			$(id).show();
		}
	});
}
</script>
<!-- // 김선용 201211 : 복수배송상품정보-->


<p style='padding:20px 0;text-align:center;'>
    <!--<input type=submit class=btn1 value='주소정보 수정'>&nbsp;-->
    <input type=button class=btn1 value='  목  록  ' accesskey='l' onclick="document.location.href='./orderlist2yearago.php?<?php echo $qstr?>';">&nbsp;
    <!--<input type=button class=btn1 value='주문서 삭제' onclick="del('<?php /*echo "./orderdelete.php?od_id=$od[od_id]&on_uid=$od[on_uid]&mb_id=$od[mb_id]&$qstr"*/?>');">-->
</p>
</form>

<script language='javascript'>
var select_all_sw = false;
var visible_sw = false;

// 전체선택, 전체해제
/*function select_all()
{
    var f = document.frmorderform;

    for (i=0; i<f.chk_cnt.value; i++)
    {
        if (select_all_sw == false)
            document.getElementById('ct_chk_'+i).checked = true;
        else
            document.getElementById('ct_chk_'+i).checked = false;
    }

    if (select_all_sw == false)
        select_all_sw = true;
    else
        select_all_sw = false;
}*/

/*function form_submit(status)
{
    var f = document.frmorderform;
    var check = false;

    for (i=0; i<f.chk_cnt.value; i++) {
        if (document.getElementById('ct_chk_'+i).checked == true) check = true;
    }

    if (check == false) {
        alert("처리할 자료를 하나 이상 선택해 주십시오.");
        return;
    }

    if (confirm("\'" + status + "\'을(를) 선택하셨습니다.\n\n이대로 처리 하시겠습니까?") == true) {
        f.ct_status.value = status;
        f.action = "./ordercartupdate.php";
        f.submit();
    }

    return;
}*/
</script>

<?php
include_once("$g4[admin_path]/admin.tail.php");

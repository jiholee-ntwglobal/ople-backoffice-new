<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
$adm_chk = sql_fetch("select count(*) as cnt from ".$g4['auth_table']." where mb_id = '".$member['mb_id']."'");

if($adm_chk['cnt'] > 0){
	$is_admin = 'super';
}
?>

<!-- 게시글 보기 시작 -->
<table width="100%" align="center" cellpadding="0" cellspacing="0" style='margin-top:20px;'><tr><td>

<!-- 링크 버튼  -->
<? // 검색목록 문의 개편 으로 수정
ob_start();
?>
<table width='100%' cellpadding=0 cellspacing=0>
<tr height=35>
    <td width=75%>
        <?/*곽범석수정*/ if ($search_href) { echo "<a href=\"$search_href&question=$question&re=$re&ymd=$ymd\"><img src='$board_skin_path/img/btn_search_list.gif' border='0' align='absmiddle'></a> "; } ?>
        <? echo "<a href=\"$list_href&question=$question&re=$re&ymd=$ymd\"><img src='$board_skin_path/img/btn_list.gif' border='0' align='absmiddle'></a> "; ?>

        <? if ($write_href) { echo "<a href=\"$write_href\"><img src='$board_skin_path/img/btn_write.gif' border='0' align='absmiddle'></a> "; } ?>
        <? if ($reply_href && $is_admin) { echo "<a href=\"$reply_href&question=$question&re=$re&ymd=$ymd\"><img src='$board_skin_path/img/btn_reply.gif' border='0' align='absmiddle'></a> "; } ?>

        <? if ($update_href) { echo "<a href=\"$update_href&question=$question&re=$re&ymd=$ymd\"><img src='$board_skin_path/img/btn_modify.gif' border='0' align='absmiddle'></a> "; } ?>
        <? if ($delete_href) { echo "<a href=\"$delete_href\"><img src='$board_skin_path/img/btn_del.gif' border='0' align='absmiddle'></a> "; } ?>

        <? if ($good_href && $is_admin) { echo "<a href=\"$good_href\" target='hiddenframe'><img src='$board_skin_path/img/btn_good.gif' border='0' align='absmiddle'></a> "; } ?>
        <? if ($nogood_href && $is_admin) { echo "<a href=\"$nogood_href\" target='hiddenframe'><img src='$board_skin_path/img/btn_nogood.gif' border='0' align='absmiddle'></a> "; } ?>

        <? if ($scrap_href && $is_admin) { echo "<a href=\"javascript:;\" onclick=\"win_scrap('$scrap_href');\"><img src='$board_skin_path/img/btn_scrap.gif' border='0' align='absmiddle'></a> "; } ?>

        <? if ($copy_href) { echo "<a href=\"$copy_href\"><img src='$board_skin_path/img/btn_copy.gif' border='0' align='absmiddle'></a> "; } ?>
        <? if ($move_href) { echo "<a href=\"$move_href\"><img src='$board_skin_path/img/btn_move.gif' border='0' align='absmiddle'></a> "; } ?>
    </td>
    <td width=25% align=right>
        <? if ($prev_href) { echo "<a href=\"$prev_href\" title=\"$prev_wr_subject\"><img src='$board_skin_path/img/btn_prev.gif' border='0' align='absmiddle'></a>&nbsp;"; } ?>
        <? if ($next_href) { echo "<a href=\"$next_href\" title=\"$next_wr_subject\"><img src='$board_skin_path/img/btn_next.gif' border='0' align='absmiddle'></a>&nbsp;"; } ?>
    </td>
</tr>
</table>
<?
$link_buttons = ob_get_contents();
ob_end_flush();
?>

<!-- 제목, 글쓴이, 날짜, 조회, 추천, 비추천 -->
<table width="100%" cellspacing="0" cellpadding="0">
<?


# 관리자 로그인시 주문 건수 및 총 주문 금액 출력 2014-04-22 홍민기 #
if($is_admin){

$order_infoQ = mysql_query("
	select
		count(*) as cnt,
		sum(b.ct_amount) as total_receipt,
		od_receipt_card,
		od_receipt_bank,
		od_receipt_point
	from
		yc4_order a
		left outer join
		yc4_cart b on a.on_uid = b.on_uid
	where
		b.ct_mb_id = '".$view['mb_id']."'
		and
		b.ct_status not in('쇼핑','취소','반품','품절')
	group by od_id
");
//$order_info = mysql_fetch_array(mysql_query($order_infoQ));
$total_cnt = 0;
while($order_info = mysql_fetch_array($order_infoQ)){
	$total_cnt2 += $order_info['cnt'];
	$total_cnt++;
	$total_card += $order_info['od_receipt_card']; # 총 카드 사용 누적 금액
	$total_cash += $order_info['od_receipt_bank']; # 총 무통장,가상계좌 사용 누적 금액
	$total_point += $order_info['od_receipt_point']; # 총 포인트 사용 누적 금액
#	$total_receipt += $order_info['total_receipt']; # 총 누적 결제 금액 (카드+무통장+포인트) -> 포인트 구매 회원 때문에 사용하지 않는다.
}
?>

<tr class='buy_result_tr'>
	<td height='30'>
		&nbsp;&nbsp;총 주문 건수 : <?=number_format($total_cnt);?>건(<?=$total_cnt2?>개 제품)
		&nbsp;&nbsp;총 카드결제 금액 : <?=number_format($total_card);?>원
		&nbsp;&nbsp;총 입금 금액 : <?=number_format($total_cash);?>원
		&nbsp;&nbsp;총 포인트 결제 : <?=number_format($total_point);?>

	</td>
</tr>
<?
}
if($customer_question) {
    if (trim($view['wr_1'])) { //곽범석
        $sql = "select it_name from " . $g4['yc4_item_table'] . " WHERE it_id = '" . $view['wr_1'] . "'";
        $it_id_name = sql_fetch($sql);
        $middle_image = $view['wr_1'] . "_m";
    }

}

?>
<tr><td height=1 bgcolor="#000"></td></tr>
<tr><td height=30 style="padding:5px 0 5px 0;">
    <table width=100% cellpadding=0 cellspacing=0>
    <tr>
    	<td style='word-break:break-all; height:28px;'>&nbsp;&nbsp;<strong><span id="writeSubject"><? if ($is_category) { echo ($category_name ? "[$view[ca_name]] " : ""); } ?><?=cut_hangul_last(get_text($view[wr_subject]))?></span></strong></td>
    	<td width=70><a href="javascript:scaleFont(+1);"><img src='<?=$board_skin_path?>/img/icon_zoomin.gif' border=0 title='글자 확대'></a>
            <a href="javascript:scaleFont(-1);"><img src='<?=$board_skin_path?>/img/icon_zoomout.gif' border=0 title='글자 축소'></a></td>
    </tr>
	<tr><td colspan="2" height=3 style="background:url(<?=$board_skin_path?>/img/title_bg.gif) repeat-x;"></td></tr>
        <? if($customer_question) { ?>
        <? if(trim($view['wr_1'])){ //곽범석 ?>
            <table width=100% cellpadding=0 cellspacing=0 >
                <tr>
                    <td width="40px">
                        <a target="_blank" href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $wr_1;?>">
                            <?php echo get_image($middle_image, 100, 100);?>
                        </a>
                    </td>
                    <td>
                        <a target="_blank" href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $wr_1;?>">
                            <? echo get_item_name($it_id_name['it_name'],"detail"); ?>
                    </td>
                    </a>
                </tr>
                <tr><td colspan="4" height=3 style="background:url(<?=$board_skin_path?>/img/title_bg.gif) repeat-x;"></td></tr>
            </table>
        <? } ?>
        <? } ?>
    </table></td></tr>

	<?
	// 김선용 200806 : 사이드뷰 미사용시 관리자인경우 적용
    if(!$board['bo_use_sideview'] && $is_admin)
        $view['name'] = get_sideview($view['mb_id'], get_text($view['wr_name']), $view['wr_email'], $view['wr_homepage']);
	?>

<tr><td height=30>&nbsp;&nbsp;<font style="font:normal 11px 돋움; color:#BABABA;">글쓴이 :</font> <?=$view[name]?><? if ($is_ip_view) { echo "&nbsp;($ip)"; } ?>&nbsp;&nbsp;&nbsp;&nbsp;
    <font style="font:normal 11px 돋움; color:#BABABA;">날짜 :</font><font style="font:normal 11px tahoma; color:#BABABA;"> <?=substr($view[wr_datetime],2,14)?>&nbsp;&nbsp;&nbsp;&nbsp;</font>
    <font style="font:normal 11px 돋움; color:#BABABA;">조회 :</font><font style="font:normal 11px tahoma; color:#BABABA;"> <?=$view[wr_hit]?>&nbsp;&nbsp;&nbsp;&nbsp;</font>
    <? if ($is_good) { ?><font style="font:normal 11px 돋움; color:#BABABA;">추천</font> :<font style="font:normal 11px tahoma; color:#BABABA;"> <?=$view[wr_good]?>&nbsp;&nbsp;&nbsp;&nbsp;<?}?></font>
    <? if ($is_nogood) { ?><font style="font:normal 11px 돋움; color:#BABABA;">비추천</font> :<font style="font:normal 11px tahoma; color:#BABABA;"> <?=$view[wr_nogood]?>&nbsp;&nbsp;&nbsp;&nbsp;<?}?></font>
    <? if ($trackback_url) { ?><a href="javascript:trackback_send_server('<?=$trackback_url?>');" style="letter-spacing:0;" title='주소 복사'><img src="<?=$board_skin_path?>/img/icon_trackback.gif" alt="" align="absmiddle"></a><?}?>
    </td>
</tr>
<tr><td height=1 bgcolor=#E7E7E7></td></tr>

<?
// 가변 파일
$cnt = 0;
for ($i=0; $i<count($view[file]); $i++) {
    if ($view[file][$i][source] && !$view[file][$i][view]) {
        $cnt++;
        //echo "<tr><td height=22>&nbsp;&nbsp;<img src='{$board_skin_path}/img/icon_file.gif' align=absmiddle> <a href='{$view[file][$i][href]}' title='{$view[file][$i][content]}'><strong>{$view[file][$i][source]}</strong> ({$view[file][$i][size]}), Down : {$view[file][$i][download]}, {$view[file][$i][datetime]}</a></td></tr>";
        echo "<tr><td height=30>&nbsp;&nbsp;<img src='{$board_skin_path}/img/icon_file.gif' align=absmiddle> <a href=\"javascript:file_download('{$view[file][$i][href]}', '{$view[file][$i][source]}');\" title='{$view[file][$i][content]}'><font style='normal 11px 돋움;'>{$view[file][$i][source]} ({$view[file][$i][size]}), Down : {$view[file][$i][download]}, {$view[file][$i][datetime]}</font></a></td></tr><tr><td height='1'  bgcolor='#E7E7E7'></td></tr>";
    }
}

// 링크
$cnt = 0;
for ($i=1; $i<=$g4[link_count]; $i++) {
    if ($view[link][$i]) {
        $cnt++;
        $link = cut_str($view[link][$i], 70);
        echo "<tr><td height=30>&nbsp;&nbsp;<img src='{$board_skin_path}/img/icon_link.gif' align=absmiddle> <a href='{$view[link_href][$i]}' target=_blank><font  style='normal 11px 돋움;'>{$link} ({$view[link_hit][$i]})</font></a></td></tr><tr><td height='1' bgcolor='#E7E7E7'></td></tr>";
    }
}
?>

<!-- <tr><td height=1 bgcolor=#"E7E7E7"></td></tr> //-->
<tr>
    <td height="150" style='word-break:break-all;padding:10px;'>
        <?
        // 파일 출력
        for ($i=0; $i<=count($view[file]); $i++) {
            if ($view[file][$i][view])
                echo $view[file][$i][view] . "<p>";
        }
        ?>

        <!-- 내용 출력 -->
        <span id="writeContents"><?=$view[content];?></span>

        <?//echo $view[rich_content]; // {이미지:0} 과 같은 코드를 사용할 경우?>
        <!-- 테러 태그 방지용 --></xml></xmp><a href=""></a><a href=''></a>

</td>
</tr>
<tr><td height="1" bgcolor="#E7E7E7"></td></tr>
        <? if ($is_signature) { echo "<tr><td align='center' style='border-bottom:1px solid #E7E7E7; padding:5px 0;'>$signature</td></tr>"; } // 서명 출력 ?>

</table><br>

<?
// 원글의 답변글
include_once "{$board_skin_path}/write_relation.php";
?>
<br>

<?
// 코멘트 입출력
include_once("./view_comment.php");
?>

<?=$link_buttons?>

</td></tr></table><br>

<script language="JavaScript">
function file_download(link, file) {
    <? if ($board[bo_download_point] < 0) { ?>if (confirm("'"+file+"' 파일을 다운로드 하시면 포인트가 차감(<?=number_format($board[bo_download_point])?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?"))<?}?>
    document.location.href=link;
}
</script>

<script language="JavaScript" src="<?="$g4[path]/js/board.js"?>"></script>
<script language="JavaScript">
window.onload=function() {
    resizeBoardImage(<?=(int)$board[bo_image_width]?>);
    drawFont();
}
</script>
<!-- 게시글 보기 끝 -->

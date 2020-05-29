<link rel="stylesheet" type="text/css" href="<?=$board_skin_path?>/css/style.css" charset="UTF-8" media="all" />
<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<!-- 게시글 보기 시작 -->
<div class="bbsview_top">
    <div id="left">
    <? if ($is_category) { echo ($category_name ? "[$view[ca_name]] " : ""); } ?>
    <? if ($is_good) { ?>
	<strong>추천 : </strong>
	<span><?=number_format($view[wr_good])?></span>
	<? } ?>
	<? if ($is_nogood) { ?>
	<strong>비추천 : </strong>
	<span><?=number_format($view[wr_nogood])?></span>
	<? } ?>
    </div>
	<div id="right">
    <? if ($scrap_href) { echo "<a class='btn' href=\"javascript:;\" onclick=\"win_scrap('$scrap_href');\">스크랩</a> "; } ?>
    <? if ($trackback_url) { ?><a class="btn"  href="javascript:trackback_send_server('<?=$trackback_url?>');" title='주소 복사'>트랙백</a><?}?>
    <? if ($admin_href) { ?><a href="<?=$admin_href?>"><img src="<?=$board_skin_path?>/img/btn_admin.gif" title="관리자"></a><?}?>
    </div>
</div>
<div class="bbsviewtable">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<colgroup>
<col style="width:10%"  />
<col style="width:60%"  />
<col style="width:10%"  />
<col style="width:20%"  />
</colgroup>
  <tr>
    <th>제목</th>
    <td colspan="3"><?=cut_hangul_last(get_text($view[wr_subject]))?></td>
  </tr>
  <tr>
    <th>작성자</th>
    <td colspan="3"><?=$view[name]?><? if ($is_ip_view) { echo "&nbsp;($ip)"; } ?></td>
  </tr>
  <tr>
    <th>작성일</th>
    <td><?=date("y-m-d H:i", strtotime($view[wr_datetime]))?></td>
    <th>조회수</th>
    <td><?=number_format($view[wr_hit])?></td>
  </tr>
</table>
</div>
<?
// 가변 파일
$cnt = 0;
for ($i=0; $i<count($view[file]); $i++) {
    if ($view[file][$i][source] && !$view[file][$i][view]) {
        $cnt++;
        echo "<tr><td height=30 background=\"$board_skin_path/img/view_dot.gif\">";
        echo "&nbsp;&nbsp;<img src='{$board_skin_path}/img/icon_file.gif' align=absmiddle border='0'>";
        echo "<a href=\"javascript:file_download('{$view[file][$i][href]}', '".urlencode($view[file][$i][source])."');\" title='{$view[file][$i][content]}'>";
        echo "&nbsp;<span style=\"color:#888;\">{$view[file][$i][source]} ({$view[file][$i][size]})</span>";
        echo "&nbsp;<span style=\"color:#ff6600; font-size:11px;\">[{$view[file][$i][download]}]</span>";
        echo "&nbsp;<span style=\"color:#d3d3d3; font-size:11px;\">DATE : {$view[file][$i][datetime]}</span>";
        echo "</a></td></tr>";
    }
}

// 링크
$cnt = 0;
for ($i=1; $i<=$g4[link_count]; $i++) {
    if ($view[link][$i]) {
        $cnt++;
        $link = cut_str($view[link][$i], 70);
        echo "<tr><td height=30 background=\"$board_skin_path/img/view_dot.gif\">";
        echo "&nbsp;&nbsp;<img src='{$board_skin_path}/img/icon_link.gif' align=absmiddle border='0'>";
        echo "<a href='{$view[link_href][$i]}' target=_blank>";
        echo "&nbsp;<span style=\"color:#888;\">{$link}</span>";
        echo "&nbsp;<span style=\"color:#ff6600; font-size:11px;\">[{$view[link_hit][$i]}]</span>";
        echo "</a></td></tr>";
    }
}
?>
<div class="content">
    <? 
	// 파일 출력
	for ($i=0; $i<=count($view[file]); $i++) {
		if ($view[file][$i][view]) 
			echo $view[file][$i][view] . "<p />";
	}
	?>

	<!-- 내용 출력 -->
	<span><?=$view[content];?></span>
</div>
<div class="bbs_bt bbs_btn">
	<div class="bbs_right_btn">
    <? if ($scrap_href) {?>
	<a class='other' style="height:22px;" href="javascript:;" onclick="win_scrap('<?=$scrap_href?>');" target="hiddenframe">스크랩</a>
	<? } ?>

	<? if ($nogood_href) {?>
	<a style="height:22px;" href="<?=$nogood_href?>" target="hiddenframe">비추천</a>
	<? } ?>

	<? if ($good_href) {?>
	<a style="height:22px;" href="<?=$good_href?>" target="hiddenframe">추천</a>
	<? } ?>
    </div>
</div>
<?
// 코멘트 입출력
include_once("./view_comment.php");
?>
<div class="bbs_bt bbs_btn">
	<div class="bbs_left_btn">
    <? if ($prev_href) { echo "<a class='other' style='height:22px;' href=\"$prev_href\" title=\"$prev_wr_subject\">이전글</a>"; } ?>
	<? if ($next_href) { echo "<a class='ohter' style='height:22px;' href=\"$next_href\" title=\"$next_wr_subject\">다음글</a>"; } ?>
    </div>
    <div class="bbs_right_btn">
    <? if ($copy_href) { echo "<a class='admin' style='height:22px;' href=\"$copy_href\">복사</a>"; } ?>
	<? if ($move_href) { echo "<a class='admin' style='height:22px;' href=\"$move_href\">이동</a>"; } ?>
	<? if ($search_href) { echo "<a class='other' style='height:22px;' href=\"$search_href\">검색목록</a>"; } ?>
	<? echo "<a class='other' style='height:22px;' href=\"$list_href\">목록</a>"; ?>
	<? if ($update_href) { echo "<a class='other' style='height:22px;' href=\"$update_href\">수정</a>"; } ?>
	<? if ($delete_href) { echo "<a class='other' style='height:22px;' href=\"$delete_href\">삭제</a>"; } ?>
	<? if ($reply_href) { echo "<a class='other' style='height:22px;' href=\"$reply_href\">답변</a>"; } ?>
	<? if ($write_href) { echo "<a style='height:22px;' href=\"$write_href\">글쓰기</a> "; } ?>
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
function file_download(link, file) {
    <? if ($board[bo_download_point] < 0) { ?>if (confirm("'"+decodeURIComponent(file)+"' 파일을 다운로드 하시면 포인트가 차감(<?=number_format($board[bo_download_point])?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?"))<?}?>
    document.location.href=link;
}
</script>

<script type="text/javascript" src="<?="$g4[path]/js/board.js"?>"></script>
<script type="text/javascript">
window.onload=function() {
    resizeBoardImage(<?=(int)$board[bo_image_width]?>);
    drawFont();
}
</script>
<!-- 게시글 보기 끝 -->

<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
 
$tagsArray = explode(",",$view[wr_10]);
$tags = '';
for($ti=0; $ti<count($tagsArray); $ti++) {
 $tagTemp = trim($tagsArray[$ti]);
 $tagText = urlencode($tagTemp);
 if($tagKey == $tagTemp) {
  $tags .= "<a href='$g4[bbs_path]/search.php?sfl=wr_subject||wr_content||wr_10&sop=and&stx=$tagText'><span style='background-color:yellow'>$tagTemp</span></a> ";      
 } else {
  $tags .= "<a href='$g4[bbs_path]/search.php?sfl=wr_subject||wr_content||wr_10&sop=and&stx=$tagText' target='_blank'>$tagTemp</a> "; 
 }
}
?>
<style type="text/css">
<!--
@import url("../../../webbus01.css");
-->
</style>
<!--프린트공간 시작 소스코드 시작-->
<link href="css/webbus01.css" rel="stylesheet" type="text/css" />
<div id='print_table'>
<!--프린트공간 시작 소스코드 끝-->

<!-- 게시글 보기 시작 -->
<table width="100%" align="center" cellpadding="0" cellspacing="0"><tr><td>
<div style="border-top:1px solid #ddd; border-bottom:1px solid #eee; clear:both; height:34px; background-color:FCFCFC; repeat-x;">
    <table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td style="padding:8px 0 0 6px;">
            <div style="color:#333333; font-size:13px; font-family:돋움; font-weight:bold; word-break:break-all;">
            <?=cut_hangul_last(get_text($view[wr_subject]))?>
            </div>
        </td>
        <td align="right" style="padding:6px 6px 0 0;" width=250>
	<? 
    ob_start(); 
    ?>
    <? if ($update_href) { echo "<a href=\"$update_href\"><img src='$board_skin_path/img/btn_modify.gif' border='0' align='absmiddle'></a> "; } ?>
    <? if ($delete_href) { echo "<a href=\"$delete_href\"><img src='$board_skin_path/img/btn_delete.gif' border='0' align='absmiddle'></a> "; } ?>
    <? if ($reply_href) { echo "<a href=\"$reply_href\"><img src='$board_skin_path/img/btn_reply.gif' border='0' align='absmiddle'></a> "; } ?>
    <?
    $link_buttons = ob_get_contents();
    ob_end_flush();
    ?>
	</td>
    </tr>
    </table>
</div>
<table border=0 cellpadding=0 cellspacing=0 width=100%>
<tr>
    <td height=40>
        <div style="float:left;"><span class="gray8s">작성자:</span> <span class="gray6s"><strong><?=$view[wr_name]?></strong></span>&nbsp;<!--IP주소 부분숨기기소스-->
		<span class="gray6s">
		<? if ($is_ip_view) { 
            $ip0 = explode(".",$ip); 
            $ip_guest = $ip0[0] .".♡.".$ip0[2].".".$ip0[3]; 
            $ip_admin = $ip0[0] .".".$ip0[1].".".$ip0[2].".".$ip0[3]; 
            if($member[mb_level] >= 10) { echo $ip_admin; } else { echo $ip_guest; } 
          } 
          ?>
		  </span>
		  <!--IP주소 부분숨기기소스 끝-->
		</div>
        <div class="gray6s" style="float:right;"> 조회 : <span class="gray8s"><strong><?=number_format($view[wr_hit])?></strong></span>
        <? if ($is_good) { ?>&nbsp;<img src="<?=$board_skin_path?>/img/icon_good.gif" align=absmiddle><span class="oranges"><strong><?=number_format($view[wr_good])?></strong></span><? } ?>
        <? if ($is_nogood) { ?>&nbsp;<img src="<?=$board_skin_path?>/img/icon_nogood.gif" align=absmiddle><span class="gray8s"><?=number_format($view[wr_nogood])?></span><? } ?>
&nbsp;</div>
    </td>
</tr>
<tr>
<td style="height:1px; bgcolor:#eeeeee;"></td>
</tr>
<tr>
<td style="height:3px; background:url(<?=$board_skin_path?>/img/title_shadow.gif) repeat-x; line-height:1px; font-size:1px;"></td>
</tr>
<?
// 가변 파일
$cnt = 0;
for ($i=0; $i<count($view[file]); $i++) {
    if ($view[file][$i][source] && !$view[file][$i][view]) {
        $cnt++;
        echo "<tr><td height=30 background=\"$board_skin_path/img/view_dot.gif\">";
        echo "&nbsp;&nbsp;<img src='{$board_skin_path}/img/icon_file.gif' align=absmiddle>";
        echo "<a href=\"javascript:file_download('{$view[file][$i][href]}', '{$view[file][$i][source]}');\" title='{$view[file][$i][content]}'>";
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
        echo "&nbsp;&nbsp;<img src='{$board_skin_path}/img/icon_link.gif' align=absmiddle>";
        echo "<a href='{$view[link_href][$i]}' target=_blank>";
        echo "&nbsp;<span style=\"color:#888;\">{$link}</span>";
        echo "&nbsp;<span style=\"color:#ff6600; font-size:11px;\">[{$view[link_hit][$i]}]</span>";
        echo "</a></td></tr>";
    }
}
?>
<tr> 
    <td height="150" style="word-break:break-all; padding:10px;">
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

        <? if ($nogood_href) {?>
        <div style="width:72px; height:55px; background:url(<?=$board_skin_path?>/img/good_bg.gif) no-repeat; text-align:center; float:right;">
        <div style="color:#888; margin:7px 0 5px 0;">비추천 : <?=number_format($view[wr_nogood])?></div>
        <div><a href="<?=$nogood_href?>" target="hiddenframe"><img src="<?=$board_skin_path?>/img/icon_nogood.gif" align="absmiddle" border="0"></a></div>
        </div>
        <? } ?>

        <? if ($good_href) {?>
        <div style="width:72px; height:55px; background:url(<?=$board_skin_path?>/img/good_bg.gif) no-repeat; text-align:center; float:right;">
        <div style="color:#888; margin:7px 0 5px 0;"><span style='color:crimson;'>추천 : <?=number_format($view[wr_good])?></span></div>
        <div><a href="<?=$good_href?>" target="hiddenframe"><img src="<?=$board_skin_path?>/img/icon_good.gif" align="absmiddle" border="0"></a></div>
        </div>
        <? } ?>

</td>
</tr>
<? if ($view[wr_10]) { echo "<tr><td align='left' style='border-bottom:0px solid #E7E7E7; padding:5px 13;'><img src='{$board_skin_path}/img/icon_tag.gif'>&nbsp;$tags</td></tr>"; } // 태그 출력 ?>
<? if ($is_signature) { echo "<tr><td align='left' style='border-bottom:1px solid #E7E7E7; padding:5px 0;'>서명:$signature</td></tr>"; } // 서명 출력 ?>
</table>
<!--########### 이전글, 다음글 시작 : yesmoa 님의 스킨 소스에서 발췌 ###############-->  
<? if (!$board[bo_use_list_view]) { ?>
<table width=100% align=center border=0 cellpadding=0 cellspacing=0>  
  <tr><td height=1 bgcolor=#ececec></td></tr>
  <? if ($prev_href) { echo "<tr><td height=25> <img src='$board_skin_path/img/001.gif' align='absmiddle'> <a class='b1' href=\"$prev_href\"> $prev_wr_subject</a></td></tr>"; } ?>  
 
  <? if ($next_href) { echo "<tr><td height=25> <img src='$board_skin_path/img/003.gif' align='absmiddle'> <a class='b1' href=\"$next_href\"> $next_wr_subject</a></td></tr>"; } ?>  
  <tr><td height=1 bgcolor=#ececec></td></tr>  
</table>  
<? } ?>  
<!--########### 이전글, 다음글 끝 ###############--> 

<!--관련글출력-->
<!--관련글출력 끝-->
<br>

<!-- 소셜 미디어로 글보내기_TERRORBOY -->
<tr><td align='right'>
<?php
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////// 2010년 8월 14일 제작 : 테러보이 /////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
// 공통사용
/////////////////////////////////////////////////////////////////////////////////////////////
// EUC --> UTF 설정후 전송
$subject_con = iconv('euc-kr', 'utf-8',$view[wr_subject]);
/////////////////////////////////////////////////////////////////////////////////////////////
// 현제 페이지 주소 추출
$board_url = $trackback_url;
// 2010 10 19 수정
// 다수 파라미터 지원 불가로 게시판 주소에서 트래백으로 변경 "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] -> $trackback_url
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// 트위터
/////////////////////////////////////////////////////////////////////////////////////////////
// URL붙이기 // 일부 시스템에서만 사용
$url= $subject_con."   ".$board_url;
//URL암호화
$url = urlencode($url);
/////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// 페이스북
/////////////////////////////////////////////////////////////////////////////////////////////
$face_url= $board_url;
$face_url = urlencode($face_url);
$face_subject = urlencode($subject_con);
/////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// 미투데이
/////////////////////////////////////////////////////////////////////////////////////////////
$me2_url= $board_url;
$me2_url = urlencode($me2_url);
$me2_subject = urlencode($subject_con);
$me2_url_text = $config[cf_title]; // 홈페이지 제목으로 출력
$me2_url_text = str_replace("\"","˝","$me2_url_text"); // 사이트 명에 따온표 들어 가면 출력 안되던것 수정 // 2010 10 19 수정
$me2_url_text = iconv('euc-kr', 'utf-8',$me2_url_text); //UTF변환
$me2_url_text = urlencode($me2_url_text); // 인코딩
$me2_teg = $g4['title']; // 테그 부분에 현제글 위치 표기
$me2_teg = iconv('euc-kr', 'utf-8',$me2_teg); //UTF변환
$me2_teg = urlencode($me2_teg); // 인코딩
/////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////
// 요즘
/////////////////////////////////////////////////////////////////////////////////////////////
$yozm_url= $board_url;
$yozm_url = urlencode($yozm_url);
$yozm_subject = urlencode($subject_con);
/////////////////////////////////////////////////////////////////////////////////////////////

?>
<a href="http://twitter.com/home/?status=<?=$url?>" target="_blank"><img src="<?=$board_skin_path?>/img/icon/twitter.png" width="32" height="32" border="0" alt="게시글을 twitter로 보내기"></a>
<a href="http://www.facebook.com/sharer.php?u=<?=$face_url?>&t=<?=$face_subject?>" target="_blank"><img src="<?=$board_skin_path?>/img/icon/facebook.png" width="32" height="32" border="0" alt="게시글을 facebook으로 보내기"></a>
<a href='http://me2day.net/posts/new?new_post[body]=<?=$me2_subject?>+++++++["<?=$me2_url_text?>":<?=$me2_url?>+]&new_post[tags]=<?=$me2_teg?>'  target="_blank"><img src="<?=$board_skin_path?>/img/icon/Me2Day.png" width="32" height="32" border="0" alt="게시글을 Me2Day로 보내기"></a>
<a href="http://yozm.daum.net/api/popup/prePost?sourceid=41&link=<?=$yozm_url?>&prefix=<?=$yozm_subject?>" target="_blank"><img src="<?=$board_skin_path?>/img/icon/yozm.png" alt="게시글을 요즘으로 보내기" border="0"></a>
</td></tr>
<!-- 소셜 미디어로 글보내기 -->


</table>
<br>

<?
// 코멘트 입출력
include_once("./view_comment.php");
?>

<div style="height:1px; line-height:1px; font-size:1px; background-color:#ddd; clear:both;">&nbsp;</div>

<div style="clear:both; height:43px;">
    <div style="float:left; margin-top:10px;">
    <? if ($prev_href) { 
		echo "<a href=\"$prev_href\" title=\"$prev_wr_subject\" id=\"btn_list\"><img src='{$board_skin_path}/img/btn_prev1.gif' align=absmiddle border='0' title='이전글보기'></a>"; } else { echo "<img src='{$board_skin_path}/img/btn_prev1no.gif' align=absmiddle border='0' title='글이 없습니다'>"; } ?><? echo "<a href=\"$list_href\" id=\"btn_list\"><img src='{$board_skin_path}/img/btn_list1.gif' align=absmiddle border='0'  title='목록보기'></a>"; ?><? if ($next_href) { echo "<a href=\"$next_href\" title=\"$next_wr_subject\" id=\"btn_list\"><img src='{$board_skin_path}/img/btn_next1.gif' align=absmiddle border='0'  title='다음글보기'></a>"; } else { echo "<img src='{$board_skin_path}/img/btn_next1no.gif' align=absmiddle border='0' title='글이 없습니다'>"; } ?>
    </div>

    <!-- 링크 버튼 -->
    <div style="float:right; margin-top:10px;">
    <? 
    ob_start(); 
    ?>
    <? if ($copy_href) { echo "<a href=\"$copy_href\"><img src='$board_skin_path/img/btn_copy.gif' border='0' align='absmiddle'></a> "; } ?>
    <? if ($move_href) { echo "<a href=\"$move_href\"><img src='$board_skin_path/img/btn_move.gif' border='0' align='absmiddle'></a> "; } ?>

    <? if ($search_href) { echo "<a href=\"$search_href\"><img src='$board_skin_path/img/btn_list_search.gif' border='0' align='absmiddle'></a> "; } ?>
    <? echo "<a href=\"$list_href\"><img src='$board_skin_path/img/btn_list.gif' border='0' align='absmiddle'></a> "; ?>
    <? if ($update_href) { echo "<a href=\"$update_href\"><img src='$board_skin_path/img/btn_modify.gif' border='0' align='absmiddle'></a> "; } ?>
    <? if ($delete_href) { echo "<a href=\"$delete_href\"><img src='$board_skin_path/img/btn_delete.gif' border='0' align='absmiddle'></a> "; } ?>
    <? if ($reply_href) { echo "<a href=\"$reply_href\"><img src='$board_skin_path/img/btn_reply.gif' border='0' align='absmiddle'></a> "; } ?>
    <? if ($write_href) { echo "<a href=\"$write_href\"><img src='$board_skin_path/img/btn_write.gif' border='0' align='absmiddle'></a> "; } ?>
    <?
    $link_buttons = ob_get_contents();
    ob_end_flush();
    ?>
    </div>
</div>

<!--<div style="height:2px; line-height:1px; font-size:1px; background-color:#dedede; clear:both;">&nbsp;</div>-->

</td></tr></table><br>
<!--프린트공간 끝 소스코드 시작-->
</div>
<!--프린트공간 끝 소스코드 끝-->

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

<?
if (!defined("_GNUBOARD_")) exit; // ���� ������ ���� �Ұ�
 
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
<!--����Ʈ���� ���� �ҽ��ڵ� ����-->
<link href="css/webbus01.css" rel="stylesheet" type="text/css" />
<div id='print_table'>
<!--����Ʈ���� ���� �ҽ��ڵ� ��-->

<!-- �Խñ� ���� ���� -->
<table width="100%" align="center" cellpadding="0" cellspacing="0"><tr><td>
<div style="border-top:1px solid #ddd; border-bottom:1px solid #eee; clear:both; height:34px; background-color:FCFCFC; repeat-x;">
    <table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td style="padding:8px 0 0 6px;">
            <div style="color:#333333; font-size:13px; font-family:����; font-weight:bold; word-break:break-all;">
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
        <div style="float:left;"><span class="gray8s">�ۼ���:</span> <span class="gray6s"><strong><?=$view[wr_name]?></strong></span>&nbsp;<!--IP�ּ� �κм����ҽ�-->
		<span class="gray6s">
		<? if ($is_ip_view) { 
            $ip0 = explode(".",$ip); 
            $ip_guest = $ip0[0] .".��.".$ip0[2].".".$ip0[3]; 
            $ip_admin = $ip0[0] .".".$ip0[1].".".$ip0[2].".".$ip0[3]; 
            if($member[mb_level] >= 10) { echo $ip_admin; } else { echo $ip_guest; } 
          } 
          ?>
		  </span>
		  <!--IP�ּ� �κм����ҽ� ��-->
		</div>
        <div class="gray6s" style="float:right;"> ��ȸ : <span class="gray8s"><strong><?=number_format($view[wr_hit])?></strong></span>
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
// ���� ����
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

// ��ũ
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
        // ���� ���
        for ($i=0; $i<=count($view[file]); $i++) {
            if ($view[file][$i][view]) 
                echo $view[file][$i][view] . "<p>";
        }
        ?>

        <!-- ���� ��� -->
        <span id="writeContents"><?=$view[content];?></span>
        
        <?//echo $view[rich_content]; // {�̹���:0} �� ���� �ڵ带 ����� ���?>
        <!-- �׷� �±� ������ --></xml></xmp><a href=""></a><a href=''></a>

        <? if ($nogood_href) {?>
        <div style="width:72px; height:55px; background:url(<?=$board_skin_path?>/img/good_bg.gif) no-repeat; text-align:center; float:right;">
        <div style="color:#888; margin:7px 0 5px 0;">����õ : <?=number_format($view[wr_nogood])?></div>
        <div><a href="<?=$nogood_href?>" target="hiddenframe"><img src="<?=$board_skin_path?>/img/icon_nogood.gif" align="absmiddle" border="0"></a></div>
        </div>
        <? } ?>

        <? if ($good_href) {?>
        <div style="width:72px; height:55px; background:url(<?=$board_skin_path?>/img/good_bg.gif) no-repeat; text-align:center; float:right;">
        <div style="color:#888; margin:7px 0 5px 0;"><span style='color:crimson;'>��õ : <?=number_format($view[wr_good])?></span></div>
        <div><a href="<?=$good_href?>" target="hiddenframe"><img src="<?=$board_skin_path?>/img/icon_good.gif" align="absmiddle" border="0"></a></div>
        </div>
        <? } ?>

</td>
</tr>
<? if ($view[wr_10]) { echo "<tr><td align='left' style='border-bottom:0px solid #E7E7E7; padding:5px 13;'><img src='{$board_skin_path}/img/icon_tag.gif'>&nbsp;$tags</td></tr>"; } // �±� ��� ?>
<? if ($is_signature) { echo "<tr><td align='left' style='border-bottom:1px solid #E7E7E7; padding:5px 0;'>����:$signature</td></tr>"; } // ���� ��� ?>
</table>
<!--########### ������, ������ ���� : yesmoa ���� ��Ų �ҽ����� ���� ###############-->  
<? if (!$board[bo_use_list_view]) { ?>
<table width=100% align=center border=0 cellpadding=0 cellspacing=0>  
  <tr><td height=1 bgcolor=#ececec></td></tr>
  <? if ($prev_href) { echo "<tr><td height=25> <img src='$board_skin_path/img/001.gif' align='absmiddle'> <a class='b1' href=\"$prev_href\"> $prev_wr_subject</a></td></tr>"; } ?>  
 
  <? if ($next_href) { echo "<tr><td height=25> <img src='$board_skin_path/img/003.gif' align='absmiddle'> <a class='b1' href=\"$next_href\"> $next_wr_subject</a></td></tr>"; } ?>  
  <tr><td height=1 bgcolor=#ececec></td></tr>  
</table>  
<? } ?>  
<!--########### ������, ������ �� ###############--> 

<!--���ñ����-->
<!--���ñ���� ��-->
<br>

<!-- �Ҽ� �̵��� �ۺ�����_TERRORBOY -->
<tr><td align='right'>
<?php
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////// 2010�� 8�� 14�� ���� : �׷����� /////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////
// ������
/////////////////////////////////////////////////////////////////////////////////////////////
// EUC --> UTF ������ ����
$subject_con = iconv('euc-kr', 'utf-8',$view[wr_subject]);
/////////////////////////////////////////////////////////////////////////////////////////////
// ���� ������ �ּ� ����
$board_url = $trackback_url;
// 2010 10 19 ����
// �ټ� �Ķ���� ���� �Ұ��� �Խ��� �ּҿ��� Ʈ�������� ���� "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] -> $trackback_url
/////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// Ʈ����
/////////////////////////////////////////////////////////////////////////////////////////////
// URL���̱� // �Ϻ� �ý��ۿ����� ���
$url= $subject_con."   ".$board_url;
//URL��ȣȭ
$url = urlencode($url);
/////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// ���̽���
/////////////////////////////////////////////////////////////////////////////////////////////
$face_url= $board_url;
$face_url = urlencode($face_url);
$face_subject = urlencode($subject_con);
/////////////////////////////////////////////////////////////////////////////////////////////


/////////////////////////////////////////////////////////////////////////////////////////////
// ��������
/////////////////////////////////////////////////////////////////////////////////////////////
$me2_url= $board_url;
$me2_url = urlencode($me2_url);
$me2_subject = urlencode($subject_con);
$me2_url_text = $config[cf_title]; // Ȩ������ �������� ���
$me2_url_text = str_replace("\"","��","$me2_url_text"); // ����Ʈ �� ����ǥ ��� ���� ��� �ȵǴ��� ���� // 2010 10 19 ����
$me2_url_text = iconv('euc-kr', 'utf-8',$me2_url_text); //UTF��ȯ
$me2_url_text = urlencode($me2_url_text); // ���ڵ�
$me2_teg = $g4['title']; // �ױ� �κп� ������ ��ġ ǥ��
$me2_teg = iconv('euc-kr', 'utf-8',$me2_teg); //UTF��ȯ
$me2_teg = urlencode($me2_teg); // ���ڵ�
/////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////
// ����
/////////////////////////////////////////////////////////////////////////////////////////////
$yozm_url= $board_url;
$yozm_url = urlencode($yozm_url);
$yozm_subject = urlencode($subject_con);
/////////////////////////////////////////////////////////////////////////////////////////////

?>
<a href="http://twitter.com/home/?status=<?=$url?>" target="_blank"><img src="<?=$board_skin_path?>/img/icon/twitter.png" width="32" height="32" border="0" alt="�Խñ��� twitter�� ������"></a>
<a href="http://www.facebook.com/sharer.php?u=<?=$face_url?>&t=<?=$face_subject?>" target="_blank"><img src="<?=$board_skin_path?>/img/icon/facebook.png" width="32" height="32" border="0" alt="�Խñ��� facebook���� ������"></a>
<a href='http://me2day.net/posts/new?new_post[body]=<?=$me2_subject?>+++++++["<?=$me2_url_text?>":<?=$me2_url?>+]&new_post[tags]=<?=$me2_teg?>'  target="_blank"><img src="<?=$board_skin_path?>/img/icon/Me2Day.png" width="32" height="32" border="0" alt="�Խñ��� Me2Day�� ������"></a>
<a href="http://yozm.daum.net/api/popup/prePost?sourceid=41&link=<?=$yozm_url?>&prefix=<?=$yozm_subject?>" target="_blank"><img src="<?=$board_skin_path?>/img/icon/yozm.png" alt="�Խñ��� �������� ������" border="0"></a>
</td></tr>
<!-- �Ҽ� �̵��� �ۺ����� -->


</table>
<br>

<?
// �ڸ�Ʈ �����
include_once("./view_comment.php");
?>

<div style="height:1px; line-height:1px; font-size:1px; background-color:#ddd; clear:both;">&nbsp;</div>

<div style="clear:both; height:43px;">
    <div style="float:left; margin-top:10px;">
    <? if ($prev_href) { 
		echo "<a href=\"$prev_href\" title=\"$prev_wr_subject\" id=\"btn_list\"><img src='{$board_skin_path}/img/btn_prev1.gif' align=absmiddle border='0' title='�����ۺ���'></a>"; } else { echo "<img src='{$board_skin_path}/img/btn_prev1no.gif' align=absmiddle border='0' title='���� �����ϴ�'>"; } ?><? echo "<a href=\"$list_href\" id=\"btn_list\"><img src='{$board_skin_path}/img/btn_list1.gif' align=absmiddle border='0'  title='��Ϻ���'></a>"; ?><? if ($next_href) { echo "<a href=\"$next_href\" title=\"$next_wr_subject\" id=\"btn_list\"><img src='{$board_skin_path}/img/btn_next1.gif' align=absmiddle border='0'  title='�����ۺ���'></a>"; } else { echo "<img src='{$board_skin_path}/img/btn_next1no.gif' align=absmiddle border='0' title='���� �����ϴ�'>"; } ?>
    </div>

    <!-- ��ũ ��ư -->
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
<!--����Ʈ���� �� �ҽ��ڵ� ����-->
</div>
<!--����Ʈ���� �� �ҽ��ڵ� ��-->

<script language="JavaScript">
function file_download(link, file) {
    <? if ($board[bo_download_point] < 0) { ?>if (confirm("'"+file+"' ������ �ٿ�ε� �Ͻø� ����Ʈ�� ����(<?=number_format($board[bo_download_point])?>��)�˴ϴ�.\n\n����Ʈ�� �Խù��� �ѹ��� �����Ǹ� ������ �ٽ� �ٿ�ε� �ϼŵ� �ߺ��Ͽ� �������� �ʽ��ϴ�.\n\n�׷��� �ٿ�ε� �Ͻðڽ��ϱ�?"))<?}?>
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
<!-- �Խñ� ���� �� -->

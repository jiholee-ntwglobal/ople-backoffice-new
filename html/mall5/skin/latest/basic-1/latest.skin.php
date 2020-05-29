<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<!-- <?=$board[bo_subject]?> (<?=$board[bo_table]?>) 최신글 시작 -->
<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td width="29" height="38"><img src="<?=$latest_skin_path?>/img/board_title_left.gif" width="29" height="38"></td>
    <td background="<?=$latest_skin_path?>/img/board_title_bg.gif"><a href='<?=$g4[bbs_path]?>/board.php?bo_table=<?=$bo_table?>'><font style='font-family:돋움; font-size:9pt; color:#696969;'><strong><?=$board[bo_subject]?></strong></font></a>&nbsp;</td>
    <td width="60" align="right" background="<?=$latest_skin_path?>/img/board_title_bg.gif"><a href='<?=$g4[bbs_path]?>/board.php?bo_table=<?=$bo_table?>'><img src="<?=$latest_skin_path?>/img/board_more.gif" width="45" height="18" border="0"></a></td>
    <td width="19"><img src="<?=$latest_skin_path?>/img/board_title_right.gif" width="19" height="38"></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? for ($i=0; $i<count($list); $i++) { ?>
<tr> 
    <td width="40" height="35" align="center" valign="middle" background="<?=$latest_skin_path?>/img/board_bg_line.gif"><img src="<?=$latest_skin_path?>/img/board_icon.gif" width="9" height="13"></td>
    <td background="<?=$latest_skin_path?>/img/board_bg_line.gif" style='word-break:break-all;'>
        <?
        echo $list[$i][icon_reply] . " ";
        echo "<a href='{$list[$i][href]}'>";
        if ($list[$i][is_notice])
            echo "<font style='font-family:돋움; font-size:9pt; color:#2C88B9;'><strong>{$list[$i][subject]}</strong></font>";
        else
            echo "<font style='font-family:돋움; font-size:9pt; color:#6A6A6A;'>{$list[$i][subject]}</font>";
        echo "</a>";

        if ($list[$i][comment_cnt]) 
            echo " <a href=\"{$list[$i][comment_href]}\"><span style='font-family:돋움; font-size:8pt; color:#9A9A9A;'>{$list[$i][comment_cnt]}</span></a>";

        // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
        // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }

        echo " " . $list[$i][icon_new];
        echo " " . $list[$i][icon_file];
        echo " " . $list[$i][icon_link];
        echo " " . $list[$i][icon_hot];
        echo " " . $list[$i][icon_secret];
        ?>
    </td>
</tr>
<? } ?>

<? if (count($list) == 0) { ?>
<tr><td colspan=2 align=center height=30 background="<?=$latest_skin_path?>/img/board_bg_line.gif">게시물이 없습니다.</td></tr>
<? } ?>
</table>

</td></tr></table>
<!-- <?=$board[bo_subject]?> (<?=$board[bo_table]?>) 최신글 끝 -->

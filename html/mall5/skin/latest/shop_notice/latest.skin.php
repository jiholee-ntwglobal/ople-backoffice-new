<?php
if(!defined("_GNUBOARD_")) exit;
?>
<table width="205" border="0" cellspacing="0" cellpadding="0">
<?for($i=0; $i<count($list); $i++){?>
<tr>
	<nobr style='display:block; overflow:hidden; width:200px;'>
	<td height="20" class="font11"><img src="<?=$g4['path']?>/images/main/main_notice_dot.gif" width="2" height="2" hspace="5" align="absmiddle"><a href='<?=$list[$i][href]?>' title="<?=get_text(stripslashes($list[$i]['wr_subject']))?>"><?=$list[$i][subject]?></a>
	<span style='font-family:돋움; font-size:8pt; color:#9A9A9A;'><?=$list[$i][comment_cnt]?></span></td>
	</nobr>
</tr>
<?}?>
<?if(!$i) echo "<tr><td align=center height=30>자료가 없습니다.</td></tr>"; ?>
</table>
<?
if(!defined("_GNUBOARD_")) exit;

// 답변글이 있는지를 먼저 확인하고 있는 경우 처리
$sql2 = "from $write_table where wr_is_comment=0 and wr_reply<>'' and wr_num='{$view['wr_num']}' ";
$chk = sql_fetch("select count(wr_id) as count $sql2");
if($chk['count'])
{
?>
<table width=100% border=0 cellspacing=0 cellpadding=2 align=center style='border:1px solid #efefef; padding:2px'>
<tr><td height=26 bgcolor=#666699><span style="color:white; font-weight:bold; padding-left:2px;">☞ 이 글과 관련된 글입니다.</span></td></tr>
<tr>
	<td valign=top> 
	<table width=100% border=0 cellspacing=0 cellpadding=0> 
	<?
	// 원글
	$wr_write = sql_fetch("select wr_id, wr_subject from $write_table where wr_is_comment=0 and wr_num='{$view['wr_num']}' and wr_reply='' ");
	?>
	<tr>
		<td style="padding:4px 0 2px 5px;"><a href="<?="{$g4['bbs_path']}/board.php?bo_table=$bo_table&wr_id={$wr_write['wr_id']}{$qstr}";?>" title="<?=get_text(stripslashes($wr_write['wr_subject']))?>"><?=get_text(stripslashes($wr_write['wr_subject']))?></a></td>
	</tr>
	<tr>
		<td valign=top>
		<table width=100% border=0 cellspacing=6 cellpadding=0>
		<?
		// 답변글
		$result2 = sql_query("select wr_id, wr_subject,wr_datetime $sql2 order by wr_reply desc");
		for($k=0; $row2=sql_fetch_array($result2); $k++)
		{
			$subject2 = get_text(stripslashes($row2['wr_subject']));
			$whref2 = "{$g4['bbs_path']}/board.php?bo_table=$bo_table&wr_id={$row2['wr_id']}{$qstr}";
		?>
		<tr>
			<td>
			<?if($wr_id == $row2['wr_id']){?>
				<img src='<?=$board_skin_path?>/img/icon_reply.gif' align='absmiddle' alt='답변글'>&nbsp;<a href="<?=$whref2?>" title="<?=stripslashes($row2['wr_subject'])?>"><span style="font-weight:bold; color:black;"><?=$subject2?></span></a>
			<?}else{?>
				<img src='<?=$board_skin_path?>/img/icon_reply.gif' align='absmiddle' alt='답변글'>&nbsp;<a href="<?=$whref2?>" title="<?=$subject2?>"><?=$subject2?></a>
			<?}?>
			</td>
			<td width=120 align=right><?=$row2['wr_datetime']?></td>
		</tr>
		<tr><td height=1 colspan=2 bgcolor=#e7e7e7></td></tr>
		<?}?>
		</table> 
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>
<?}?>
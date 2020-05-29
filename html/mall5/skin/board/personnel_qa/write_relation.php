<?
if(!defined("_GNUBOARD_")) exit;

// 답변글이 있는지를 먼저 확인하고 있는 경우 처리

$chk = sql_fetch("select count(uid) as count from yc4_personal_qa where depth='1' and mb_id='$member[mb_id]' and parent_uid='$view[uid]'");
if($chk['count'])
{
?>
<table width=100% border=0 cellspacing=0 cellpadding=2 align=center style='border:1px solid #efefef; padding:2px'>
<tr><td height=26 bgcolor=#666699><span style="color:white; font-weight:bold; padding-left:2px;">☞ 이 글에 대한 답변 글입니다.</span></td></tr>
<tr>
	<td valign=top> 
	<table width=100% border=0 cellspacing=0 cellpadding=0> 
	<?
	// 원글
	$wr_write = sql_fetch("select uid, subject from yc4_personal_qa where depth=0  and mb_id='$member[mb_id]'");
	?>
	<!-- <tr>
		<td style="padding:4px 0 2px 5px;"><?=get_text(stripslashes($view['subject']))?></td>
	</tr> -->
	<tr>
		<td valign=top>
		<table width=100% border=0 cellspacing=6 cellpadding=0>
		<?
		// 답변글
		$result2 = sql_query("select uid, subject,contents,create_dt from yc4_personal_qa where depth='1' and mb_id='$member[mb_id]' and parent_uid='$view[uid]' order by create_dt desc");
		for($k=0; $row2=sql_fetch_array($result2); $k++)
		{
			$subject2 = get_text(stripslashes($row2['subject']));
			$whref2 = "{$g4['bbs_path']}/board.php?bo_table=$bo_table&wr_id={$row2['wr_id']}{$qstr}";
		?>
		<tr>
			<td>
			<img src='<?=$board_skin_path?>/img/icon_reply.gif' align='absmiddle' alt='답변글'>&nbsp;<span style="font-weight:bold; color:black;"><?=$subject2?></span><br/>
			</td>
			<td width=120 align=right><?=$row2['create_dt']?></td>
		</tr>
		<tr><td colspan="2" style="padding:4px 0 2px 5px;"><?=get_text(stripslashes($row2['contents']))?></td></tr>
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
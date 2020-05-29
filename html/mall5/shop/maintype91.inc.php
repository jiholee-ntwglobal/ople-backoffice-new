<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<table width="672" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td><img src="<?=$g4['path']?>/images/main/main_product_title02.gif" width="625" height="45"><a href="<?=$g4[shop_path]?>/listtype.php?type=<?=$type?>"><img src="<?=$g4['path']?>/images/main/main_product_more.gif" width="47" height="45" border="0"></a></td>
</tr>
<tr>
	<td background="<?=$g4['path']?>/images/main/main_product_bg.gif">
	<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
	<tr>
	<?
	for($i=0; $row=sql_fetch_array($result); $i++)
	{
		if($i && ($i%$list_mod)==0)
			echo "</tr><tr>";

		$href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' title='".get_text(stripslashes(get_item_name($row['it_name'])))."'>";
	?>
		<td height="<?=$td_width?>%" bgcolor="#FFFFFF" valign=top style="padding:2px 0 2px 0;">
		<table width="94%" border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#dadada">
		<tr>
			<td bgcolor="#FFFFFF" valign=top>
			<table width="94%" border="0" align="center" cellpadding="0" cellspacing="0">
			<tr>
				<td align="center" style="padding:3px 0 3px 0;"><?=$href?><?=get_it_image($row[it_id]."_s", $img_width, $img_height,$row['it_id'],null,false,true,true)?></a></td>
			</tr>
			<tr>
				<td><?=$href?><?=cut_str(stripslashes(it_name_icon($row,$row['it_name'],1,'list')),46,'..')?></a></td>
			</tr>
			<tr>
				<td class="pd25" align=center><?=display_amount(get_amount($row), $row[it_tel_inq])?></td>
			</tr>
			<tr>
				<td class="pd25" align=center><?=display_point($row['it_point'])?></td>
			</tr>
			</table>
			</td>
		</tr>
		</table>
		</td>
	<?
	}
	// 나머지 td 를 채운다.
	if (($cnt = $i%$list_mod) != 0)
	    for ($k=$cnt; $k<$list_mod; $k++)
		    echo "<td>&nbsp;</td>\n";
	?>
	</tr>
	</table>
	</td>
</tr>
<tr>
	<td><img src="<?=$g4['path']?>/images/main/main_product_bottom.gif" width="672" height="4"></td>
</tr>
</table>
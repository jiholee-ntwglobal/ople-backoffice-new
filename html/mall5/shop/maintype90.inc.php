<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<table width="755" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td><img src="<?=$g4['path']?>/images/main/main_product_title01.gif" width="555" height="30"><a href="<?=$g4[shop_path]?>/listtype.php?type=<?=$type?>"><img src="<?=$g4['path']?>/images/main/main_product_more.gif" width="200" height="30" border="0"></a></td>
</tr>
<tr>
<td background="<?=$g4['path']?>/images/main/main_product_bg.gif">
	<table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
	<tr>
	<?
	for($i=0; $row=sql_fetch_array($result); $i++)
	{
		if($i && ($i%$list_mod)==0)
			echo "</tr><td colspan='$list_mod' background='$g4[shop_img_path]/line_h.gif' height=1></td><tr>";


		$href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' title='".get_text(stripslashes(get_item_name($row['it_name'])))."'>";
	?>
		<td height="<?=$td_width?>%" bgcolor="#FFFFFF" valign=top style="padding:2px 0 2px 0;">
		<table width="94%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#dadada">
		<tr>
			<td bgcolor="#FFFFFF" valign=top>
			<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
			<tr>
				<td align="center" style="padding:30px 0 3px 0;"><?=$href?><?=get_it_image($row[it_id]."_s", $img_width, $img_height,$row['it_id'],null,false,true,true)?></a></td>
			</tr>
			<tr>
				<td align=center ><?=$href?><?=stripslashes(it_name_icon($row,$row['it_name'],1,'list'))?></a></td>
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
	<br>
	</td>
</tr>
<tr>
	<td><img src="<?=$g4['path']?>/images/main/main_product_bottom.gif" width="755" height="4"></td>
</tr>
</table>
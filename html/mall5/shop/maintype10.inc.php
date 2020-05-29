<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>


<table width=100% cellpadding=0 cellspacing=0 style='background: url(http://115.68.20.84/mall6/bg_list_line.gif) repeat-y 0 0;margin:10px 0;'>
<tr>
<?php
for ($i=0; $row=sql_fetch_array($result); $i++) {
    if ($i > 0 && $i % $list_mod == 0) {
        echo "</tr>\n\n<tr>\n";
    }

    $href = "<a href='".$g4['shop_path']."/item.php?it_id=".$row['it_id']."' class=item>";
?>
    <td align=center valign=top class='item_box'>
        <table width='205px' cellpadding=0 cellspacing=0 border=0>
        <tr><td align=center class='item_image'><?php echo $href;?><?php echo get_it_image($row[it_id]."_s", $img_width."", $img_height."",$row['it_id'],null,false,true,true)?></a></td></tr>
        <tr><td class='item_title'><?=$href?><?=it_name_icon($row,$row['it_name'],1,'list')?></a></td></tr>
        <!--시중가격<tr><td><strike><?=display_amount($row[it_cust_amount])?></strike></td></tr>-->
		<?if($new_year_item_set[$row['it_id']]){
			/*
			echo
				"
				<tr>
					<td>
						<span class='item_point' style='text-decoration:line-through;'>
							<em>정상가</em> ￦ ".number_format($new_year_item_set[$row['it_id'])."
						</span>
						<span style='text-decoration:none; display:inline; color:#ff0000;'>
							".get_dc_percent(get_amount($row),$new_year_item_set[$row['it_id'])."% OFF
						</span>
					</td>
				</tr>
				";
			*/
			echo "
				<tr>
					<td>
						<span class='item_point' style='text-decoration:line-through;'>
							￦ ".number_format($new_year_item_set[$row['it_id']])."&nbsp;&nbsp;($ ".display_amount_usd($new_year_item_set[$row['it_id']]).")
						</span>
						<span style='text-decoration:none; display:inline; color:#ff0000;'>
							".get_dc_percent(get_amount($row),$new_year_item_set[$row['it_id']])."% OFF
						</span>
					</td>
				</tr>
			";


		$fffff = true;
		}?>
        <tr><td><span class='amount'><em>￦</em> <?=display_amount(get_amount($row), $row[it_tel_inq])?>&nbsp;&nbsp;(<em>$</em> <?=display_amount_usd(get_amount($row));?>)</span></td></tr>
		<tr><td><span class="item_point"><em>포인트</em><?=display_point($row['it_point'])?></td></tr>

        </table></td>
<?php
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "<td>&nbsp;</td>\n";
?>
</tr>
</table>
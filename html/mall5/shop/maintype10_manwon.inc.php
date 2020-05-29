<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<style type='text/css'>
	.item_box_171 {
	width: 121px;
	padding: 0 25px 20px 25px;
	text-align: center;
	border-right: solid 1px #f0f0f0;
	}
	.item_box_171 .item_title {padding-bottom:0;color:#777;line-height:14px;}
	.item_box_171 .item_title img {display:none;}
	.item_box_171 .amount {text-align:center;margin-top:-3px; }
</style>


<div style='position: absolute;margin-top:10px;z-index:1'><img src='http://115.68.20.84/mall6/page/manwonhappy/tag_best.gif' alt='BEST_icon'/></div>

<table width='1030px' cellpadding='0' cellspacing='0' style='margin:10px 0;border-left: solid 1px #f0f0f0;border-bottom: solid 1px #f0f0f0;'>
<tr>
<?php
for ($i=0; $row=sql_fetch_array($result); $i++) {
    if ($i > 0 && $i % $list_mod == 0) {
        echo "</tr>\n\n<tr>\n";
    }

    $href = "<a href='".$g4['shop_path']."/item.php?it_id=".$row['it_id']."' class=item>";
?>
    <td align=center valign=top class='item_box_171'>
        <table width='100%' cellpadding=0 cellspacing=0 border=0>
        <tr><td align=center class='item_image'><?php echo $href;?><?php echo get_it_image($row[it_id]."_s", $img_width."", $img_height."",$row['it_id'],null,false,true,true)?></a></td></tr>
        <tr><td class='item_title'><?=$href?><?=it_name_icon($row,$row['it_name'],1,'list',68)?></a></td></tr>
        <!--시중가격<tr><td><strike><?=display_amount($row[it_cust_amount])?></strike></td></tr>-->
        <tr><td>

		<?php
			echo "<span class=amount><em>￦</em> ".display_amount(get_amount($row), $row[it_tel_inq])."&nbsp;&nbsp;(<em>$</em> ".display_amount_usd(get_amount($row)).")</span>";
		?>

		</td></tr>
		<!--tr><td><span class="item_point"><em>포인트</em><?=display_point($row['it_point'])?></td></tr-->

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

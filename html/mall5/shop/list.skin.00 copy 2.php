<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<table width=755 cellpadding=2 cellspacing=0>
<tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ( ($i>0) && (($i%$list_mod)==0) )
    {
        echo "</tr>\n\n";
        echo "<tr><td colspan='$list_mod' height=5></td></tr>\n\n";
        echo "<tr>\n";
    }

    echo "
    <td width='{$td_width}%' align=center valign=top>
        <br>
        <table width=98% cellpadding=2 cellspacing=0>
        <tr><td align=center>".get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id])."</td></tr>
        <tr><td align=center>".it_name_icon($row,$row['it_name'],1,'list')."</td></tr>";

    /*
	if ($row[it_cust_amount] && !$row[it_gallery])
        echo "<tr><td align=center><strike>".display_amount($row[it_cust_amount])."</strike></td></tr>";
	*/

    echo "<tr><td align=center>";

    if (!$row[it_gallery]){
        echo "<span class=amount style='font-size:18px'>".display_amount(get_amount($row), $row[it_tel_inq])."</span>";
		echo "<br><span style='margin:0px; color:#fa5a00;'>".display_point($row[it_point])."</span>";
	}
	// 김선용 201207 : 사용후기 갯수
	$ps_chk = sql_fetch("select count(is_id) as count from {$g4['yc4_item_ps_table']} where it_id='{$row['it_id']}' ");
	if($ps_chk['count'])
		echo "<br/><span style='margin:0px; color: #d76a1f;  font-size:12px'>사용후기(".nf($ps_chk['count']).")</span>";

    echo "</td></tr></table></td>";
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "    <td></td>\n";
?>
</tr>
</table>

<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<table width=100% cellpadding=0 cellspacing=0 style='background: url(http://115.68.20.84/mall6/bg_list_line.gif) repeat-y 0 0;margin:10px 0;'>
<tr>
<?

//if($_GET['ev_id'] == '1416298712' || $_GET['ev_id'] == '1416298694' || $_GET['ev_id'] == '1416298679'){

	$oneplus_rs = sql_query("select it_id from yc4_event_item where ev_id='1413783986'");
	$oneplus_it_id_arr = array();
	while($oneplus_data = sql_fetch_array($oneplus_rs)){
		array_push($oneplus_it_id_arr,$oneplus_data['it_id']);
	}
//}

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ( ($i>0) && (($i%$list_mod)==0) )
    {
        echo "</tr>\n\n";
        // echo "<tr><td colspan='$list_mod' background='$g4[shop_img_path]/line_h.gif' height=1></td></tr>\n\n";
        echo "<tr>\n";
    }

	//if($_GET['ev_id'] != '1413783986'){
		$oneplus_icon = (in_array($row['it_id'],$oneplus_it_id_arr)) ? "<span class=\"iconAdd\"><img src=\"http://115.68.20.84/mall6/ico_onepluse.png\" alt=\"1+1\"></span>" : '';
	//}

    echo "
    <td width='{$td_width}%' align=center valign=top class='item_box'>
        <br>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td align=center class='item_image'>".$oneplus_icon.get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id],false,false,true,true,true)."</td></tr>
        <tr><td class='item_title'>".it_name_icon($row,$row['it_name'],1,'list')."</td></tr>";

    /*
	시중가 표시 안함 2014-10-17 홍민기
	if ($row[it_cust_amount] && !$row[it_gallery])
        echo "<tr><td class='item_price'><strong>".display_amount($row[it_cust_amount])."</strong></td></tr>";
	*/

    echo "<tr><td>";

    if (!$row[it_gallery]){
        echo "<span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."<em>원</em></span>";

		/*
		할인율 표시 안함 2014-10-17 홍민기
		if ($row[it_cust_amount]) {
			$halin = 100 - ($row[it_amount] / $row[it_cust_amount] * 100);
			$halin = round($halin, 0);
			if ($halin) echo "<span class=discount>{$halin}%↓</span>";
		}
		*/


		echo "<span class=item_point><em>포인트</em>".display_point($row[it_point])."</span>";
	}
	// 김선용 201207 : 사용후기 갯수
	$ps_chk = sql_fetch("select count(is_id) as count from {$g4['yc4_item_ps_table']} where it_id='{$row['it_id']}' ");
	if($ps_chk['count'])
		echo "<span class='item_review'><em>사용후기</em>(".nf($ps_chk['count']).")</span>";

    echo "</td></tr></table></td>";
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "    <td></td>\n";
?>
</tr>
</table>

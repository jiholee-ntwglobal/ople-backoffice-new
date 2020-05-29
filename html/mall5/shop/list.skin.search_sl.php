<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

	$oneplus_rs = sql_query("select it_id from yc4_event_item where ev_id='1413783986'");
	$oneplus_it_id_arr = array();
	while($oneplus_data = sql_fetch_array($oneplus_rs)){
		array_push($oneplus_it_id_arr,$oneplus_data['it_id']);
	}

	if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){
		$nonstop_rs = sql_query("select it_id,ev_amount from yc4_nontop_sale where status=2");
		while($nonstop_data = sql_fetch_array($nonstop_rs)){
			$nonstop_event_item[$nonstop_data['it_id']] = $nonstop_data['ev_amount'];
		}
	}

?>
<div class='paging'>
  <?php echo get_paging($config['cf_write_pages'], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr1&page=");?>
</div>
<table width="100%" cellpadding="0" cellspacing="0" style="background: url(http://115.68.20.84/mall6/bg_list_line.gif) repeat-y 0 0;margin:10px 0;">
<tr>
<?
$cnt = count($it_list);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ( ($i>0) && (($i%$list_mod)==0) )
    {
        echo "</tr>\n\n";
        echo "<tr><td colspan='$list_mod' height=1></td></tr>\n\n";
        echo "<tr>\n";
    }
	if(trim($_GET['search_str']) != ''){
		$it_name = search_font(stripslashes($_GET['search_str']), stripslashes($row['it_name']));
	}else if(trim($_GET['it_maker']) != ''){
		$it_name = search_font(stripslashes(trim($_GET['it_maker'])), stripslashes($row['it_name']));
	}

	//if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){
		$oneplus_icon = (in_array($row['it_id'],$oneplus_it_id_arr)) ? "<span class=\"iconAdd\" ><img src=\"http://115.68.20.84/mall6/ico_onepluse.png\" alt=\"1+1\" class='list_sale_ico' style='width:46px;'></span>" : '';
	//}

	if($nonstop_event_item[$row['it_id']] > 0){
			$row['it_amount'] = $nonstop_event_item[$row['it_id']];
		}




    echo "
    <td width='{$td_width}%' align=center valign=top class='item_box'>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td align=center class='item_image'>".$oneplus_icon.get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id],false,false,true,true)."</td></tr>
        <tr><td class='item_title'>".it_name_icon($row,$it_name,1,'list')."</td></tr>";

    /*
	if ($row[it_cust_amount] && !$row[it_gallery])
        echo "<tr><td class='item_price'><strike>".display_amount($row[it_cust_amount])."</strike></td></tr>";
	*/

    echo "<tr width='{$td_width}%'><td>";

    if (!$row[it_gallery]){
        echo "<span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."<em>원</em></span>";
		echo "<span class=item_point><em>포인트</em>".display_point($row[it_point])."</span>";
	}
	// 김선용 201207 : 사용후기 갯수
	$ps_chk = sql_fetch("select count(is_id) as count from {$g4['yc4_item_ps_table']} where it_id='{$row['it_id']}' ");
	if($ps_chk['count'])
		echo "<span class='item_review'><em>사용후기</em>(".nf($ps_chk['count']).")</span>";

    echo "</td></tr></table></td>";
	$ca_arr[$row['ca_id']]++;
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "    <td width='{$td_width}%'></td>\n";
?>
</tr>
</table>


<script type="text/javascript">
$(document).ready(function(){
	$('.item_image img').removeAttr('width').removeAttr('height');
	var cnt = $('.item_image img').length;
	for(var i=0; i<cnt; i++){
		var img = $('.item_image > a > img:eq('+i+')');
		var height = img.height();
		var width = img.width();

		if(height > width){
			img.height(200);
		}else{
			img.width(200);
		}
	}
});
</script>
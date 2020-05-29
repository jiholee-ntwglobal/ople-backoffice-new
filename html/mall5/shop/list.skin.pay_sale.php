<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

?>

<table width=100% cellpadding=0 cellspacing=0 style='background: url(http://115.68.20.84/mall6/bg_list_line_3.gif) repeat-y 0 0;margin:10px 0;border-top: solid 1px #eee;'>
<tr>
<?

//if($_GET['ev_id'] == '1416298712' || $_GET['ev_id'] == '1416298694' || $_GET['ev_id'] == '1416298679'){

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


//}

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ( ($i>0) && (($i%$list_mod)==0) )
    {
        echo "</tr>\n\n";
        // echo "<tr><td colspan='$list_mod' background='$g4[shop_img_path]/line_h.gif' height=1></td></tr>\n\n";
        echo "<tr>\n";
    }

	if($nonstop_event_item[$row['it_id']] > 0){
		$row['it_amount'] = $nonstop_event_item[$row['it_id']];
		$no_discount_price_flag = true;
	}


	//if($_GET['ev_id'] != '1413783986'){
		$oneplus_icon = (in_array($row['it_id'],$oneplus_it_id_arr)) ? "<span class=\"iconAdd\"><img src=\"http://115.68.20.84/mall6/ico_onepluse.png\" alt=\"1+1\"></span>" : '';
	//}

    echo "
    <td width='{$td_width}%' align=center valign=top class='item_box_3'>
        <br>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td align=center class='item_image' style='position:relative;'><span class='ico_off_arrow'>50%</span>".$oneplus_icon.get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id],false,false,true,true,true)."</td></tr>
        <tr><td class='item_title'>".it_name_icon($row,$row['it_name'],1,'list')."</td></tr>";

    /*
	시중가 표시 안함 2014-10-17 홍민기
	if ($row[it_cust_amount] && !$row[it_gallery])
        echo "<tr><td class='item_price'><strong>".display_amount($row[it_cust_amount])."</strong></td></tr>";
	*/

    echo "<tr><td>";

    if (!$row[it_gallery]){

		$etc_amount_chk = sql_fetch("
			select
				a.amount,
				a.money_type,
				b.code_name as channel,
				c.code_name as money_name
			from
				yc4_item_etc_amount a,
				yc4_code b,
				yc4_code c
			where
					a.pay_code = b.code_value
				and	b.code_type = 'pay'
				and a.money_type = c.code_value
				and c.code_type = 'money'
				and a.it_id = '".$row['it_id']."'
		");


		if($etc_amount_chk){
			if($etc_amount_chk['money_type'] == 'usd'){
				echo "<span class='item_point' style='text-decoration:line-through;'><em>".$etc_amount_chk['channel']."</em> ￦ ".number_format($etc_amount_chk['amount'] * $default['de_conv_pay'])." &nbsp;&nbsp;($ ".number_format($etc_amount_chk['amount'],2).")</span>
				<span style='text-decoration:none; display:inline; color:#ff0000;'>".get_dc_percent(get_amount($row),$etc_amount_chk['amount'] * $default['de_conv_pay'])."% OFF</span>
			";
			}else{
				echo "<span class='item_point' style='text-decoration:line-through;'><em>".$etc_amount_chk['channel']."</em> ￦ ".number_format($etc_amount_chk['amount'])." &nbsp;&nbsp;($ ".number_format(usd_convert($etc_amount_chk['amount']),2).")</span>
				<span style='text-decoration:none; display:inline; color:#ff0000;'>".get_dc_percent(get_amount($row),$etc_amount_chk['amount'])."% OFF</span>
				";
			}
		}


        echo "
		<span class='price_Msrp'><em>MSRP : ￦8,741($ 7.99)</em> <em>아이허브가 : ￦8,741($ 7.99)</em></span>
		<span class=amount><em>￦</em> ".display_amount(get_amount($row), $row[it_tel_inq])."&nbsp;&nbsp;(<em>$</em> ".display_amount_usd(get_amount($row)).")</span>";

		


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

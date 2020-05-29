<?
$sub_menu = "400210";
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");

$g4['title'] = "상품분류 변환2(구->신)";
include_once ($g4['admin_path']."/admin.head.php");


$cnt_rs = sql_query($a="select count(i.it_id) as cnt from yc4_item i left outer join yc4_category_item o ON o.it_id = i.it_id where isnull(o.it_id)");
$cnt_info = sql_fetch_array($cnt_rs);

$it_qry = sql_query($a="
	SELECT c.ca_id, c.ca_name,sum(if(isnull(i.ca_id),0,1)) as cnt
	FROM yc4_category_new c
       LEFT OUTER JOIN yc4_category_item i on i.ca_id = c.ca_id
       LEFT OUTER JOIN yc4_item o ON o.it_id = i.it_id
	group by c.ca_id
");

while($data = sql_fetch_array($it_qry)){

	unset($obj);
	if(!$cate_cnt){
		$cate_cnt = array();
	}

	switch(strlen($data['ca_id'])){
		case 2:
			$cate_cnt[$data['ca_id']] += $data['cnt'];
			$depth = 1;

			
			break;
		case 4:
			$cate_cnt[substr($data['ca_id'],0,2)] += $data['cnt'];
			$cate_cnt[$data['ca_id']] += $data['cnt'];
			$depth = 2;			
			break;
		case 6:
			$cate_cnt[substr($data['ca_id'],0,2)] += $data['cnt'];
			$cate_cnt[substr($data['ca_id'],0,4)] += $data['cnt'];
			$cate_cnt[$data['ca_id']] += $data['cnt'];
			$depth = 3;
			break;
		case 8:
			$cate_cnt[substr($data['ca_id'],0,2)] += $data['cnt'];
			$cate_cnt[substr($data['ca_id'],0,4)] += $data['cnt'];
			$cate_cnt[substr($data['ca_id'],0,6)] += $data['cnt'];
			$cate_cnt[$data['ca_id']] += $data['cnt'];
			$depth = 4;
			break;
		case 10:
			$cate_cnt[substr($data['ca_id'],0,2)] += $data['cnt'];
			$cate_cnt[substr($data['ca_id'],0,4)] += $data['cnt'];
			$cate_cnt[substr($data['ca_id'],0,6)] += $data['cnt'];
			$cate_cnt[substr($data['ca_id'],0,8)] += $data['cnt'];
			$cate_cnt[$data['ca_id']] += $data['cnt'];
			$depth = 5;
			break;
	}
	
	
	$cate[$data['ca_id']]['depth'] = $depth;
	$cate[$data['ca_id']]['ca_id'] = $data['ca_id'];
	$cate[$data['ca_id']]['name'] = $data['ca_name'];
	$cate[$data['ca_id']]['cnt'] = $data['cnt'];
}


foreach($cate as $catecode => $obj){

	switch($obj['depth']){

		case '1': case '2':
			$font_style = ($obj['depth'] == '1') ? "color:white;background-color:red;'" : '';
			$list .= "
			<tr><td colspan='5' height='10'></td></tr>
			<tr>
				<td colspan='5'><a href='category_item_list2.php?catecode=$catecode' style='font-weight:bold;$font_style'>".$obj['name']."(".$cate_cnt[$catecode].")</a></td>
			</tr>";
			break;
		case '3':

			if($current_code != substr($catecode,0,4)){

				${'colnum_'.$current_code}--;

				while(${'colnum_'.$current_code} % 5 != 0){
					$list .= "<td></td>";
					${'colnum_'.$current_code}++;
				}

				$list .= "</tr>";

			}


			if(!isset(${'colnum_'.substr($catecode,0,4)})) {
				$current_code = substr($catecode,0,4);
				${'colnum_'.$current_code} = 1;
			}

			if(${'colnum_'.$current_code} % 5 == 1) $list .= "<tr>";

			$list .= "<td valign='top'><a href='category_item_list2.php?catecode=$catecode'>".$obj['name']."(".$cate_cnt[$catecode].")</a><div id='detail_$catecode'></div></td>";


			if(${'colnum_'.$current_code} % 5 == 0) $list .= "</tr>";

			${'colnum_'.$current_code}++;

			break;

		case '4':

			$sub_data[substr($catecode,0,6)] .= "&nbsp;&nbsp;<a href='category_item_list2.php?catecode=$catecode'>- ".$obj['name']."(".$cate_cnt[$catecode].")</a><div id='detail_$catecode'></div>";
			
			break;
		case '5':

			$sub_data2[substr($catecode,0,8)] .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='category_item_list2.php?catecode=$catecode'>-> ".$obj['name']."(".$cate_cnt[$catecode].")</a><br>";
			
			break;
	}
	

}


foreach($sub_data as $code => $txt){
	$script_txt .= "$('#detail_$code').html(\"$txt\");";
}

foreach($sub_data2 as $code => $txt){
	$script_txt2 .= "$('#detail_$code').html(\"$txt\");";
}

?>
<table width='100%'>
	<tbody>
		<tr>
			<td colspan="5"><a href="category_item_no.php">미적용 상품(<?php echo $cnt_info['cnt']; ?>)</a></td>
		</tr>
		<?=$list;?>
	</tbody>
</table>


<script>
<?php echo $script_txt.$script_txt2; ?>
</script>


<?

include_once ($g4['admin_path']."/admin.tail.php");
?>
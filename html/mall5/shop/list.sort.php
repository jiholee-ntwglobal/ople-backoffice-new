<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<table width=98% cellpadding=0 cellspacing=0 align=center>
<tr>
	<td>총 <span class=point><strong><?php echo number_format($total_count) ?></strong></span>개의 상품이 있습니다.</td>
    <td align=right style='padding-top:3px; padding-bottom:3px;'>
        <select id=it_maker name=it_maker onchange="document.location = '<?php echo $_SERVER['PHP_SELF']."?ca_id=".$ca_id."&ev_id=".$ev_id."&sort=".$sort."&items=".$items."&it_maker="?>'+this.value;">
            <option value="">- 제조사별 보기 -</option>
			<?php
			// 김선용 200804 : 제조사
			// 200805 : 현재 분류의 하위분류
			//$msql = sql_query("select distinct it_maker from {$g4['yc4_item_table']} where ca_id like '$ca_id%' and it_use=1 and it_maker<>'' ".$hide_makerQ." order by it_maker");
			$msql = sql_query("
				select
					a.it_maker,count(*) as cnt
				from
					".$g4['yc4_item_table']." a
					left join
					yc4_category_item b on a.it_id = b.it_id
				where
					a.it_use=1
					and a.it_discontinued = 0
					and	b.ca_id like '$ca_id%'
					and a.it_maker<>''
					".$hide_makerQ."
				group by a.it_maker
				order by a.it_maker
			");
			for($k=0; $row=sql_fetch_array($msql); $k++)
				echo "<option value='".urlencode($row['it_maker'])."' ".($row['it_maker'] == stripslashes($it_maker) ? 'selected' : '').">".$row['it_maker']."(".number_format($row['cnt']).")</option>\n";
			?>
        </select>

        <select id=it_sort name=sort onchange="document.location = '<?php echo "$_SERVER[PHP_SELF]?ca_id=$ca_id&ev_id=$ev_id&it_maker=$it_maker&items=$items&sort="?>'+this.value;">
            <option value=''>- 출력 순서 - </option>
            <option value='it_amount asc'>낮은가격순</option>
            <option value='it_amount desc'>높은가격순</option>
            <option value='it_name asc'>상품명순</option>
            <option value='it_type1 desc'>히트상품</option>
            <option value='it_type2 desc'>추천상품</option>
            <option value='it_type3 desc'>최신상품</option>
            <option value='it_type4 desc'>인기상품</option>
            <option value='it_type5 desc'>할인상품</option>
        </select>

		<select id=items name=items onchange="if (this.value=='') return; document.location = '<?php  echo "$_SERVER[PHP_SELF]?ca_id=$ca_id&ev_id=$ev_id&sort=$sort&it_maker=$it_maker&items=" ?>'+this.value;">
            <option value="">- 목록수 -</option>
			<?php for($k=1; $k<21; $k++){?>
			<option value="<?php echo ($k*5);?>"><?php echo ($k*5)?> 개</option>
			<?php }?>
        </select>
    </td>
</tr>
</table>

<script type='text/JavaScript'>
document.getElementById('it_sort').value="<?=$sort?>";
//document.getElementById('it_maker').value="<?=$it_maker?>";
document.getElementById('items').value="<?=$items?>";
</script>

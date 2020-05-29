<?
$sub_menu = "400900";

include_once("./_common.php");





if($_GET['s_id']){
	$where .= (($where) ? ' and ' : ' where ') . "a.s_id = '".$_GET['s_id']."'";

}

# 카테고리 리스트 로드 #
$sql = sql_query("
	select 
		a.*,
		b.ca_name
	from 
		shop_category a
		left join
		".$g4['yc4_category_table']." b on a.ca_id = b.ca_id
	".$where."
");
while($cate = sql_fetch_array($sql)){
	# 하위 카테고리 갯수 #
	$child_cate_cntQ = sql_fetch("
		select 
			count(*) as cnt
		from
			".$g4['yc4_category_table']."
		where
			ca_id like '".$cate['ca_id']."%'
			and
			ca_id != '".$cate['ca_id']."'
	");
	$child_cate_cnt = $child_cate_cntQ['cnt'];

	# 상품 갯수 (하위 카테고리 포함) #
	$item_cnt = sql_fetch("
		select
			count(*) as cnt
		from
			yc4_category_item
		where
			ca_id like '".$cate['ca_id']."%'
	");
	$item_cnt = $item_cnt['cnt'];

	$list_tr .= "
		<tr>
			<td align='center'>".$cate['ca_id']."</td>
			<td>".$cate['ca_name']."</td>
			<td align='right'>".number_format($child_cate_cnt)."개</td>
			<td align='right'>".number_format($item_cnt)."개</td>
			<td align='center'>".$cate['sort']."</td>
			<td align='center'>".icon('수정',$g4['shop_admin_path'].'/station_cate_write.php?s_id='.$_GET['s_id'].'&uid='.$cate['uid'])."</td>
		</tr>
	";
}

auth_check($auth[$sub_menu], "r");
$g4['title'] = '제품관 관리';
include_once ("$g4[admin_path]/admin.head.php");
?>

<table width='100%'>
	<tr class='ht' align='center'>
		<td>카테고리코드</td>
		<td>카테고리명</td>
		<td>하위카테고리 갯수</td>
		<td>상품 갯수</td>
		<td >순서</td>
		<td><?=icon('입력',$g4['shop_admin_path'].'/station_cate_write.php?s_id='.$_GET['s_id']);?></td>
	</tr>
	<?=$list_tr;?>
</table>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
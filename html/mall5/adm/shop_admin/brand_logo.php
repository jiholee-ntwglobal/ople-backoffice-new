<?php
/*
----------------------------------------------------------------------
file name	 : brand_logo.php
comment		 : 브랜드 로고 관리
date		 : 2015-01-27
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "600100";
include "_common.php";
auth_check($auth[$sub_menu], "r");

# 브랜드 리스트 로드 #
$sql = sql_query("
	select
		trim(a.it_maker) as it_maker,
		a.it_maker_kor,
		upper(left(trim(a.it_maker),1)) as it_maker_sort,
		b.logo_img,
		count(*) as cnt
	from
		".$g4['yc4_item_table']." a
		left join
		yc4_it_maker b on trim(a.it_maker) = b.it_maker
	where
		a.it_use = 1
		and
		a.it_maker not in ( '078347300756' )
		and
		trim(a.it_maker) != ''
	group by it_maker
	order by it_maker_sort asc, cnt desc
");
$list_tr = '';
while($row = sql_fetch_array($sql)){
	if($row['it_maker_sort'] != $bf_it_maker_sort){
		$list_tr .= "
			<tr>
				<td colspan='5'><b>".$row['it_maker_sort']."</b></td>
			</tr>
		";
		$bf_it_maker_sort = $row['it_maker_sort'];
	}
	if($row['logo_img']){
		$logo_img = "<img src='".$row['logo_img']."'/>";
	}else{
		$logo_img = '';
	}
	$list_tr .= "
		<tr>
			<td>".$logo_img."</td>
			<td>".$row['it_maker']."</td>
			<td>".$row['it_maker_kor']."</td>
			<td>".number_format($row['cnt'])."</td>
			<td>".icon('입력',$g4['shop_admin_path'].'/brand_logo_edit.php?it_maker_eng='.urlencode($row['it_maker']))."</td>
		</tr>
	";
}

include_once $g4['admin_path']."/admin.head.php";
?>

<table width='100%' style='border-collapse: collapse;' border='1'>
	<tr>
		<td>로고 이미지</td>
		<td>브랜드영문명</td>
		<td>브랜드 한글명</td>
		<td>브랜드 판매중 상품 갯수</td>
		<td></td>
	</tr>
	<?php echo $list_tr;?>
</table>


<?php
include_once $g4['admin_path']."/admin.tail.php";
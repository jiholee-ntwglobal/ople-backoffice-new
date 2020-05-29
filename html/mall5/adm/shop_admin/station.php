<?php
$sub_menu = "100710";

include_once("./_common.php");



auth_check($auth[$sub_menu], "r");
$g4['title'] = '제품관 관리';


if($_POST['mode'] == 'cate_list_ajax'){
	$sql = sql_query($a="
		select 
			a.ca_id, a.sort,
			b.ca_name 
		from 
			shop_category a 
			left join 
			yc4_category b on a.ca_id = b.ca_id 
		where 
			a.s_id = '".$_POST['s_id']."' 
		order by a.sort 
	");
//	echo $a; exit;


	while( $category = sql_fetch_array($sql) ){
		$list_tr .= "
			<tr>
				<td align='center'>".$category['ca_id']."</td>
				<td>".$category['ca_name']."</td>
				<td align='center'>".$category['sort']."</td>
			</tr>
		";

	}

	if($list_tr) $list_tr = "
		<table width='80%' align='center'>
			<tr align='center'>
				<td>카테고리 코드</td>
				<td>카테고리명</td>
				<td>출력순서</td>
			</tr>
			".$list_tr."
		</table>
	";
	echo $list_tr;
	exit;
}

# 제품관 리스트 로드 #
$sql = sql_query("
	select 
		a.s_id,a.name,a.view,a.sort,a.create_dt,
		count(b.s_id) as cnt
	from 
		yc4_station a
		left join
		shop_category b on a.s_id = b.s_id 
	group by a.s_id
	order by a.sort

");

while($station = sql_fetch_array($sql)){
	switch($station['view']){
		case 'Y' : $view = 'O'; break;
		default : $view = 'X'; break;
	}
	# 상품 수량 
	$item_cntQ = sql_fetch("
		select
			count(*) as cnt
		from
			yc4_station a
			left join
			shop_category b on a.s_id = b.s_id
			left join
			yc4_category_item c on c.ca_id like concat(b.ca_id,'%')
		where
			a.s_id = '".$station['s_id']."'

	");
	$item_cnt = $item_cntQ['cnt'];
	$list_tr .= "
		<tr>
			<td align='center' class='s_id'>".$station['s_id']."</td>
			<td>".$station['name']."</td>
			<td align='right'><span class='category_view_btn' onclick=\"category_view(this)\">".number_format($station['cnt'])."개</span></td>
			<td align='right'>".number_format($item_cnt)."개</td>
			<td align='center'>".$view."</td>
			<td align='center'>".$station['sort']."</td>
			<td align='center'>".$station['create_dt']."</td>
			<td align='center'>".icon('수정',$g4['shop_admin_path'].'/station_write.php?s_id='.$station['s_id']).icon('삭제').icon('보기',$g4['shop_admin_path'].'/station_cate.php?s_id='.$station['s_id'])."</td>
		</tr>
		<tr>
			<td colspan='7'>
				<div class='category_wrap'>asdf</div>
			</td>
		</tr>

	";

}

include_once ("$g4[admin_path]/admin.head.php");
?>

<style type="text/css">
.category_wrap{
	display:none;
}
.category_view_btn{
	cursor:pointer;
	font-weight:bold;
}
</style>
<table width='100%'>
	<tr class='ht' align='center'>
		<td>코드</td>
		<td>이름</td>
		<td>카테고리 갯수</td>
		<td>상품 갯수</td>
		<td>사용여부</td>
		<td>순서</td>
		<td>생성일</td>
		<td><?=icon('입력',$g4['shop_admin_path'].'/station_write.php')?></td>
	</tr>
	<?=$list_tr;?>
</table>


<script type="text/javascript">
function category_view(obj){
	var cate_view_wrap = $(obj).parent().parent().next().find('div.category_wrap');
	var s_id = $(obj).parent().parent().find('.s_id').text();

	if(cate_view_wrap.css('display') == 'none'){
		$.ajax({
			url : '<?=$_SERVER['PHP_SELF']?>',
			type : 'post',
			data : {
				'mode' : 'cate_list_ajax',
				's_id' : s_id
			},
			success : function (result){
				if(result == ''){
					return false;
				}

				cate_view_wrap.show();
				cate_view_wrap.html(result);
			}
		});
	}else{
		cate_view_wrap.hide();
	}
	

}
</script>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
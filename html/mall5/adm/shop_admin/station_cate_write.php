<?
$sub_menu = "400900";

include_once("./_common.php");


if($_POST['mode'] == 'insert'){
	$sql = "
		insert into
			shop_category
		(
			s_id,ca_id,sort
		)values(
			'".$_POST['s_id']."','".$_POST['ca_id']."','".$_POST['sort']."'
		)
	";
	if(!sql_query($sql)){
		alert('등록중 오류 발생! 다시 시도해 주세요');
	}
	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/station_cate.php?s_id='.$_POST['s_id']);
	exit;
}


if($_POST['mode'] == 'update'){
	$sql = "
		update
			shop_category
		set
			s_id = '".$_POST['s_id']."',
			ca_id = '".$_POST['ca_id']."',
			sort = '".$_POST['sort']."' 
		where
			uid = '".$_POST['uid']."'
	";

	if(!sql_query($sql)){
		alert('등록중 오류 발생! 다시 시도해 주세요');
	}

	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/station_cate.php?s_id='.$_POST['s_id']);
	exit;
}


if($_POST['mode'] == 'delete'){
	$sql = "
		delete from shop_category where uid = '".$_POST['uid']."'
	";

	if(!sql_query($sql)){
		alert('처리중 오류 발생! 다시 시도해 주세요');
	}

	alert('삭제가 완료되었습니다.',$g4['shop_admin_path'].'/station_cate.php?s_id='.$_POST['s_id']);
	exit;
}

# 제품관 카테고리 데이터 로드 #
$station_cate = sql_fetch("
	select * from shop_category where uid = '".$_GET['uid']."' and s_id = '".$_GET['s_id']."' order by sort
");



# 카테고리 로드 (1단) #
$sql = sql_query("
	select 
		a.ca_id,a.ca_name
	from 
		".$g4['yc4_category_table']." a
	where 
		length(a.ca_id) = 2
		and
		a.ca_id not in (select ca_id from shop_category where s_id = '".$_GET['s_id']."')
		".(($station_cate['ca_id']) ? "or a.ca_id = '".$station_cate['ca_id']."'":'')."
");


while($cate_option = sql_fetch_array($sql)){
	$cate_opt .= "
		<option value='".$cate_option['ca_id']."' ".(($station_cate['ca_id'] == $cate_option['ca_id'])?'selected':'').">".$cate_option['ca_name']."</option>
	";
}

# 제품관 리스트 로드 #
$sql = sql_query("
	select s_id,name from yc4_station
");

while($st = sql_fetch_array($sql)){
	$st_option .= "
		<option value='".$st['s_id']."' ".(($_GET['s_id'] == $st['s_id']) ? 'selected':'').">".$st['name']."</option>
	";
}



if($station_cate['sort']){
	$sort = $station_cate['sort'];
}else{
	$sort_qry = sql_fetch("select max(sort)+1 as sort from shop_category where s_id = '".$_GET['s_id']."'");
	$sort = $sort_qry['sort'];
}


auth_check($auth[$sub_menu], "r");
$g4['title'] = '제품관 관리';
include_once ("$g4[admin_path]/admin.head.php");
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method='post'>
	<input type="hidden" name='mode' value='<?=($station_cate) ? 'update':'insert'?>'/>
	<?if($station_cate){?>
	<input type="hidden" name='uid' value='<?=$station_cate['uid']?>'/>
	<?}?>
	<table width='100%'>
		<tr class='ht'>
			<td>제품관</td>
			<td>
				<select name="s_id">
					<?=$st_option?>
				</select>
			</td>
		</tr>
		<tr class='ht'>
			<td>카테고리</td>
			<td>
				<select name="ca_id" id="">
					<?=$cate_opt;?>
				</select>
			</td>
		</tr>
		<tr class='ht'>
			<td>순서</td>
			<td><input type="text" name='sort' value='<?=$sort?>'/></td>
		</tr>
	</table>
	<p align='center'>
		<input type="submit" value=' 저 장 ' />
		<input type="button" value=' 목 록 ' onclick="location.href='<?=$g4['shop_admin_path']?>/station_cate.php?s_id=<?=$_GET['s_id']?>'"/>
		<input type="button" value=' 삭 제 ' onclick="this.form.mode.value='delete'; this.form.submit();"/>
	</p>

</form>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
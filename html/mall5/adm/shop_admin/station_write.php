<?php
$sub_menu = "100710";

include_once("./_common.php");
$station = null;
if($_POST['mode'] == 'insert'){
	$qry = "
		insert into
			yc4_station
		(
			name,head_file,index_file,view,sort,create_dt,create_id
		)values(
			'".$_POST['name']."','".$_POST['head_file']."','".$_POST['index_file']."','".$_POST['view']."','".$_POST['sort']."',now(),'".$member['mb_id']."'
		)

	";

	if(!sql_query($qry)){
		alert('저장중 오류 발생! 다시 시도해 주세요');
	}

	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/station.php');
}


if($_POST['mode'] == 'update'){
	$qry = "
		update
			yc4_station
		set
			name = '".$_POST['name']."',
			head_file = '".$_POST['head_file']."',
			index_file = '".$_POST['index_file']."',
			view = '".$_POST['view']."',
			sort = '".$_POST['sort']."',
			update_dt = now(),
			update_id = '".$member['mb_id']."'
		where
			s_id = '".$_POST['s_id']."'
	";

	if(!sql_query($qry)){
		alert('저장중 오류 발생! 다시 시도해 주세요');
	}
	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/station.php');
}



if($_GET['s_id']){
	$station = sql_fetch("
		select * from yc4_station where s_id = '".$_GET['s_id']."'
	");
}


auth_check($auth[$sub_menu], "r");
$g4['title'] = '제품관 관리';
include_once ("$g4[admin_path]/admin.head.php");
?>

<form action="<?=$_SERVER['PHP_SELF']?>" method='POST'>
	<input type="hidden" name='mode' value='<?=($station) ? 'update':'insert'?>'/>
	<?if($station){?>
	<input type="hidden" name='s_id' value='<?=$station['s_id']?>' />
	<?}?>
	<table width='100%'>
		<tr>
			<td>제품관명</td>
			<td><input type="text" name='name' value='<?=$station['name']?>'/></td>
			<td>사용여부</td>
			<td><input type="checkbox" name='view' value='Y' <?=($station['view'] == 'Y') ? 'checked':''?>/></td>
		</tr>
		<tr>
			<td>상단파일경로</td>
			<td><input type="text" name='head_file' value='<?=$station['head_file']?>'/></td>
			<td>Index 파일명</td>
			<td><input type="text" name='index_file' value='<?=$station['index_file']?>'/></td>
		</tr>
		<tr>
			<td>순서</td>
			<td colspan="3"><input type="text" name='sort' value='<?=$station['sort']?>'/></td>
		</tr>
	</table>
	<p align='center'>
		<input type="submit" value='저장'/>
		<input type="button" value='목록' onclick="location.href='<?=$g4['shop_admin_path']?>/station.php'"/>
	</p>
</form>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
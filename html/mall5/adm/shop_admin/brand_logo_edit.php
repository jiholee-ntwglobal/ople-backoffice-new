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
auth_check($auth[$sub_menu], "w");

if($_POST['mode'] == 'update'){
	$_POST['it_maker_eng'] = stripslashes($_POST['it_maker_eng']);
	$chk = sql_fetch("select count(*) as cnt from yc4_it_maker where it_maker = '".mysql_real_escape_string($_POST['it_maker_eng'])."'");
	if($chk['cnt'] > 0){
		$sql = "
			update
				yc4_it_maker
			set
				logo_img = '".$_POST['logo_img']."'
			where
				it_maker = '".mysql_real_escape_string($_POST['it_maker_eng'])."'
		";
	}else{
		$sql = "
			insert into
				yc4_it_maker
			(it_maker,logo_img)
			values(
				'".mysql_real_escape_string($_POST['it_maker_eng'])."','".$_POST['logo_img']."'
			)
		";
	}

	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요');
		exit;
	}
	alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/brand_logo.php');
	exit;
}


$it_maker = stripslashes($_GET['it_maker_eng']);


$data = sql_fetch("select * from yc4_it_maker where it_maker = '".mysql_real_escape_string($it_maker)."'");
include_once $g4['admin_path']."/admin.head.php";
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='post'>
	<input type="hidden" name='mode' value='update' />
	<input type="hidden" name='it_maker_eng' value="<?php echo $it_maker;?>" />
	<table>
		<tr>
			<td>브랜드명</td>
			<td><?php echo $it_maker;?></td>
		</tr>
		<tr>
			<td>브랜드 로고 이미지 링크</td>
			<td><input type="text" name='logo_img' value='<?php echo $data['logo_img']?>' /></td>
		</tr>
	</table>
	<p align='center'><input type="submit" value='저장' /></p>
</form>
<?php
include_once $g4['admin_path']."/admin.tail.php";
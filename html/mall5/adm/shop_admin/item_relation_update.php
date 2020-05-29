<?php

$sub_menu = "300377";
include_once("./_common.php");
include $g4['full_path'].'/lib/db.php';

$pdo		= new db();
$ople		= $pdo->ople_db_pdo;

$main_id	= isset($_POST['main_id']) ? $_POST['main_id'] : "";
$rel_id		= isset($_POST['chk_rel']) ? $_POST['chk_rel'] : "";
$values		= "";

// 메인상품코드 유효성 확인
if($main_id=="" || preg_match("/([^0-9\.])/",$main_id)){
	echo "
		<script type=text/JavaScript>
			alert('메인 상품코드가 올바르지 않습니다.');
			history.back();
		</script>
	";
	exit;
}

if($rel_id!=""){
	foreach($rel_id as $val){
		if(preg_match("/([^0-9\.])/",$val)){
			echo "
				<script type=text/JavaScript>
					alert('관련상품의 상품코드가 올바르지않습니다.');
					history.back();
				</script>
			";
			exit;
		}else{
			$values	.= "('".$main_id."','".$val."'), ";
		}
	}
	$values		= substr($values, 0 ,-2);
	
	// 기존 관련상품 데이터 삭제
	$del_sql	= "DELETE FROM yc4_item_relation WHERE it_id='".$main_id."'";
	$del_res	= $ople->query($del_sql);
	
	// 신규 관련상품 데이터 추가
	$ins_sql	="INSERT INTO yc4_item_relation (it_id,it_id2) VALUES ".$values;
	$ins_res	= $ople->query($ins_sql);
	
}else{
	// 기존 관련상품 데이터 삭제
	$del_sql	= "DELETE FROM yc4_item_relation WHERE it_id='".$main_id."'";
	$del_res	= $ople->query($del_sql);
}

echo "
	<script type=text/JavaScript>
		alert('처리되었습니다.');
		location.href='item_relation_write.php?main_id=".$main_id."';
	</script>
";
exit;

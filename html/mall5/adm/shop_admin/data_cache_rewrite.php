<?php
/*
----------------------------------------------------------------------
file name	 : main_hotdeal_item.php
comment		 : 메인 핫딜존 관리
date		 : 2015-01-22
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "600600";
include "_common.php";
auth_check($auth[$sub_menu], "r");

if($_GET['mode'] == 'update'){
	# 메인 페이지 캐싱파일 재생성
	file_get_contents("http://www.ople.com/mall5/cron/main_data_cache.php");


}

include_once $g4['admin_path']."/admin.head.php";



if(!$_GET['mode']){
echo "
	<script>
		if(confirm('메인 데이터 캐싱을 재생성 하시겠습니까?')){
			location.href='".$_SERVER['PHP_SELF']."?mode=update';
		}
	</script>
";
}elseif($_GET['mode'] == 'update'){
	echo "<p>메인 데이터 캐시 재생성이 완료되었습니다.</p>";
}


include_once $g4['admin_path']."/admin.tail.php";
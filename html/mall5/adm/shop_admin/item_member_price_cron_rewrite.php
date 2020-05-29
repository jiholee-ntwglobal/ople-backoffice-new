<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-26
 * Time: 오후 3:25
 */
$sub_menu = "600600";
include "_common.php";
auth_check($auth[$sub_menu], "w");

if($_GET['mode'] == 'update'){
    # 메인 페이지 캐싱파일 재생성
    file_get_contents("http://www.ople.com/mall5/cron/member_price_cron.php");

    alert('재생성 되었습니다 ','./item_member_price_list.php');
}

include_once $g4['admin_path']."/admin.head.php";



if(!$_GET['mode']){
    echo "
	<script>
		if(confirm('멤버프라이스 상품 가격을 재생성 하시겠습니까?')){
			location.href='".$_SERVER['PHP_SELF']."?mode=update';
		}
	</script>
";
}elseif($_GET['mode'] == 'update'){
    echo "<p>메인 데이터 캐시 재생성이 완료되었습니다.</p>";
}


include_once $g4['admin_path']."/admin.tail.php";
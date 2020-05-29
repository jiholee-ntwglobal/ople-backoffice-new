<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-12
 * Time: 오후 5:49
 */
$sub_menu = "500540";
include_once("./_common.php");
auth_check($auth[$sub_menu], "d");

//삭제
if($_POST['mode'] == 'delete'){

    //uid chk
    if((int)$_POST['uid']<1 ){
        alert('개발팀의 문의 해주세요.');
    }

    //data chk
    $sql = "
SELECT count(*) cnt
FROM coupon_history_new 
WHERE coupon_uid = '".sql_safe_query($_POST['uid'])."'
    ";

    $coupon_result = sql_fetch($sql);

    if($coupon_result['cnt'] <1){
        sql_query("
        DELETE FROM coupon_new
        WHERE coupon_uid = '".sql_safe_query($_POST['uid'])."'
        ");
        alert('삭제되었습니다','./coupon_list.php');
    }

    alert('쿠폰을 등록한 고객이 있어 삭제할수 없습니다');

}
alert('잘못된 접근 방식입니다.');
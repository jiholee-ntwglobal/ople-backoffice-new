<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-12
 * Time: 오후 5:49
 */
$sub_menu = "500540";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

//post data chk
if(!isset($_POST['coupon_name']) || trim($_POST['coupon_name']) == '' ){
    alert('쿠폰이름을 입력해주세요.');
}
if(!isset($_POST['coupon_code']) || trim($_POST['coupon_code']) == ''){
    alert('쿠폰번호를 입력해주세요.');
}
if(!isset($_POST['coupon_value']) || trim($_POST['coupon_value']) == '' || !is_numeric($_POST['coupon_value'])  ){
    alert('포인트금액을 입력해주세요.');
}
if(!isset($_POST['start_date']) || trim($_POST['start_date']) == '' ){
    alert('시작날짜를 선택해주세요.');
}
if(!isset($_POST['end_date']) || trim($_POST['end_date']) == '' ){
    $_POST['end_date'] = 'NULL';
}else{
    $_POST['end_date'] = "'".sql_safe_query(trim($_POST['end_date']))."'";
}
if(!isset($_POST['coupon_type']) || (trim($_POST['coupon_type']) != '1' && trim($_POST['coupon_type']) != '2'  && trim($_POST['coupon_type']) != '3') ){
    alert('쿠폰 타입을 선택해주세요');
}
if(!isset($_POST['use_flag']) || (trim($_POST['use_flag']) != 'yes' && trim($_POST['use_flag']) != 'no') ) {
    alert('사용가능 여부를 산택해주세요');
}
$_POST['use_flag'] = trim($_POST['use_flag']) == 'yes' ? '1' : '0';

if(!isset($_POST['du_publish']) || (trim($_POST['du_publish']) != 'yes' && trim($_POST['du_publish']) != 'no') ){
    alert('중복가능 여부를 선택해주세요');
}
$_POST['du_publish'] = trim($_POST['du_publish']) == 'yes' ? '1' : '0';

//insert
if($_POST['mode'] == 'insert'){

    // coupon code data chk
    $sql ="
    SELECT count(*) cnt 
    FROM coupon_new
    where coupon_code = '".sql_safe_query(trim($_POST['coupon_code']))."'
    ";
    $coupon_chk = sql_fetch($sql);

    if($coupon_chk['cnt'] > 0){

        alert('중복되는 쿠폰 번호 입니다');

    }

    //inset query
    sql_query("
            INSERT INTO coupon_new(coupon_code,
                                   coupon_name,
                                   coupon_type,
                                   start_dt,
                                   end_dt,
                                   coupon_value,
                                   use_flag,
                                   du_publish,
                                   create_dt)
            VALUES ('".sql_safe_query(trim($_POST['coupon_code']))."',
                    '".sql_safe_query(trim($_POST['coupon_name']))."',
                    '".sql_safe_query(trim($_POST['coupon_type']))."',
                    '".sql_safe_query(trim($_POST['start_date']))."',
                     ".$_POST['end_date'].",
                    '".sql_safe_query(trim($_POST['coupon_value']))."',
                    '".sql_safe_query(trim($_POST['use_flag']))."',
                    '".sql_safe_query(trim($_POST['du_publish']))."',
                    now())
    ");

    alert('쿠폰이 생성 되었습니다','./coupon_list.php');

    //update
}else if($_POST['mode'] == 'update'){

    //uid chk
    if((int)$_POST['uid']<1 ){
        alert('개발팀의 문의 해주세요.');
    }

    //coupon chk
    $sql = "
    SELECT count(*) cnt , coupon_code
    FROM coupon_new
    where coupon_uid = '".$uid."'
    ";
    $coupon_result  = sql_fetch($sql);

    if(!$coupon_result || $coupon_result['cnt']<1  || trim($coupon_result['coupon_code'])==''){

        alert('개발팀의 문의해주시기 바랍니다');

    }

    //update
    if($coupon_code == $coupon_result['coupon_code']){

        sql_query("
        update coupon_new
        set start_dt = '".sql_safe_query($_POST['start_date'])."'
        ,   end_dt = ".$_POST['end_date']."
        ,   use_flag = '".sql_safe_query($_POST['use_flag'])."'
        ,   du_publish = '".sql_safe_query($_POST['du_publish'])."'
        where coupon_uid = '".$uid."'
        ");

    }

    alert('업데이트 되었습니다 ','./coupon_list.php');

}


?>




<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-19
 * Time: 오후 5:51
 */
$sub_menu = "500550";
include_once("./_common.php");
auth_check($auth[$sub_menu], "d");

//update & insert
if($_POST['mode']=='delete'){

    if((int)$_POST['delete_uid']<1 ){
        alert('개발팀의 문의 해주세요.');
    }

    sql_query("
    delete from item_member_price where uid  ='".sql_safe_query(trim($_POST['delete_uid']))."'
    ");

    alert('삭제가 완료 되었습니다','./item_member_price_list.php');
}

alert('잘못된 접근 방식입니다');

<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-06-22
 * Time: 오후 4:55
 */
$sub_menu = "500530";
include_once("./_common.php");


if($_GET['del']=='del'){
    auth_check($auth[$sub_menu], "d");
    $_GET['uid'] = trim($_GET['uid']) ? sql_safe_query(trim($_GET['uid'])) : '';
    $fg=sql_query("
    DELETE FROM opler  WHERE uid = '{$_GET['uid']}'
    ");
}else {
    auth_check($auth[$sub_menu], "w");
    $_POST['mb_id'] = trim($_POST['mb_id']) ? sql_safe_query(trim($_POST['mb_id'])) : '';

    $_POST['start'] = trim($_POST['start']) ? trim($_POST['start']) : '';
    $_POST['end'] = trim($_POST['end']) ? trim($_POST['end']) : '';
    $_POST['start'] = preg_replace("/[^0-9]*/s", "", $_POST['start']);
    $_POST['end'] = preg_replace("/[^0-9]*/s", "", $_POST['end']);

    if (strlen($_POST['start']) != 8 || strlen($_POST['end']) != 8) {
        alert('모두 입력해주시기 바랍니다');
    }
    if (!$_POST['mb_id'] || !$_POST['start'] || !$_POST['end']) {
        alert('모두 입력해주시기 바랍니다');
    }

    $mb_id = sql_fetch("select count(*) cnt from g4_member where mb_id ='{$_POST['mb_id']}'");
    if ($mb_id['cnt'] <= 0) {
        alert('존재하지않는 회원입니다');
    }
    if ($_POST['mode'] == 'insert') {
        $fg = sql_query("
    INSERT INTO opler
    (mb_id, start_dt, end_dt) VALUES 
    ('{$_POST['mb_id']}','{$_POST['start']}','{$_POST['end']}');
    ");
    } elseif ($_POST['mode'] == 'update') {
        $_POST['uid'] = trim($_POST['uid']) ? sql_safe_query(trim($_POST['uid'])) : '';
        $fg = sql_query("
    UPDATE opler SET start_dt = '{$_POST['start']}', end_dt = '{$_POST['end']}' WHERE uid = '{$_POST['uid']}'
    ");
    }
}
if($fg==false){
    alert('개발팀의 문의 주시기바랍니다');
}
alert('완료 되었습니다','./opler_list.php');
/*if ($_GET['mode'] == 'idsearch') {
    $_GET['mb_id'] = trim($_GET['mb_id']) ? sql_safe_query(trim($_GET['mb_id'])) : '';
    if ($_GET['mb_id']) {
        $mb_id = sql_fetch("select mb_name from g4_member where mb_id ='{$_GET['mb_id']}'");
        echo $mb_id['mb_name'];
        exit;
    } else {
        echo false;
        exit;
    }
}*/
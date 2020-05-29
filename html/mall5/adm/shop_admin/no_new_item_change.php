<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-13
 * Time: 오후 5:09
 */
$sub_menu = "300666";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");
$fail = 0;
$success = 0;
$message='';
if(!isset($_POST['it_id'])|| empty($_POST['it_id'])){
    $message = '상품 코드가 잘못 되었습니다';
    alert($message,'');
    return;
}
if(count($_POST['it_id'])>0) {
    $it_id_in = '';//it_create_time
    if ($_POST['fg'] == 'Y') {
        foreach ($_POST['it_id'] as $value){
            $it_id = trim($value);
            if(!is_numeric($value)){
                $fail++;
                continue;
            }
            $row_cnts = sql_fetch("select count(*) cnt  from yc4_item where it_id ='{$it_id}'");
            if ($row_cnts['cnt'] <= 0) {
                $fail++;
                continue;
            }
            $row_cnt = sql_fetch("select count(*) cnt from yc4_no_new_item where it_id ='{$it_id}'");
            if ($row_cnt['cnt'] > 0) {
                $fail++;
                continue;
            }
            $success++;
            $it_id_in .= ($it_id_in ? "," : "") . "('" . $it_id . "')";

        }

// insert

        sql_query("insert into yc4_no_new_item (it_id) values $it_id_in ");
        $total =$success+$fail;
        $message = "총{$total} 건 성공 {$success}건 실패 {$fail}건";
        alert($message,'./no_new_item.php');
    } elseif ($_POST['fg'] == 'N') {
        foreach ($_POST['it_id'] as $value){
            $it_id = trim($value);
            if(!is_numeric($value)){
                $fail++;
                continue;
            }
            $row_cnt = sql_fetch("select count(*) cnt from yc4_no_new_item where it_id ='{$it_id}'");
            if ($row_cnt['cnt'] <= 0) {
                $fail++;
                continue;
            }
            $success++;
            $it_id_in .= ($it_id_in ? "," : "") . "'" . $it_id . "'";
        }
// delete
        sql_query("delete from yc4_no_new_item where it_id in ($it_id_in)");
        $total =$success+$fail;
        $message = "총{$total} 건 성공 {$success}건 실패 {$fail}건";
        alert($message,'./no_new_item.php');
    }
    $message = '올바른 접근이 아닙니다';
    alert($message,'');
}
$message = '올바른 접근이 아닙니다';
alert($message,'');


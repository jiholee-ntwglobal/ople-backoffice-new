<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-07-12
 * Time: 오전 9:31
 */
include_once("./_common.php");
$sub_menu = "600970";
auth_check($auth[$sub_menu], "w");

//ti_id 확인
if ($_POST['mode'] == 'it_id_chk') {
    $where = '';
    //배열로 왔는지 체크
    if (is_array($_POST['item'])) {

        $where = "'" . implode("','", $_POST['item']) . "'";
        $result = sql_query(" SELECT it_id,it_name 
                               FROM yc4_item 
                               where it_id IN ({$where})");
        $arr_itname = array();

        while ($row = sql_fetch_array($result)) {
            $arr_itname[$row['it_id']] = $row['it_name'];
            $key = array_search($row['it_id'], $_POST['item']);
            if ($key !== false) {
                array_splice($_POST['item'], $key, 1);
            }

        }
        if (empty($_POST['item'])) {
            $arr_itname['fg'] = true;
            echo json_encode($arr_itname);
        } else {
            $msg = "'" . implode("','", $_POST['item']) . "'";
            $arr_itname['fg'] = false;
            $arr_itname['msg'] = $msg;
            echo json_encode($arr_itname);
        }
    } else {
        $result = sql_fetch(" SELECT it_id,it_name FROM yc4_item 
                               where it_id = '" . sql_safe_query(trim($_POST['item'])) . "'");
        if ($result['it_id']) {
            $arr_itname['fg'] = true;
            $arr_itname[$result['it_id']] = $result['it_name'];
            echo json_encode($arr_itname);
        } else {
            $msg = '';
            $msg .= $_POST['item'];
            $arr_itname['fg'] = false;
            $arr_itname['msg'] = $msg;
            echo json_encode($arr_itname);
        }

    }
    exit;
}

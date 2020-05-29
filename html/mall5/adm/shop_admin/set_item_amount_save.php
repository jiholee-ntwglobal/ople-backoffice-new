<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-10-10
 * Time: 오후 4:16
 */
$sub_menu = "300124";
include_once "./_common.php";
include_once $g4['full_path'] . '/lib/db.php';
auth_check($auth[$sub_menu], "w");
if(!$_POST['upc'] || trim($_POST['upc'])==''){

    alert('잘못된 접근 방식입니다');
}

if(!is_numeric($_POST['single_amount'])){

    alert('단품가의 유효하지않는 값이 입력되었습니다');
}

$set_upc_chk = sql_fetch("
            SELECT a.it_id
            FROM ople_mapping    a
                 INNER JOIN ople_mapping b
                    ON     a.it_id = b.it_id
                       AND a.upc = '".sql_safe_query(trim($_POST['upc']))."'
                       AND a.ople_type = 's'
                       AND b.ople_type = 's'
            GROUP BY a.it_id
            HAVING count(a.it_id) > 1
            LIMIT 1
            ");
if( !$set_upc_chk['it_id'] || trim($set_upc_chk['it_id']) == ''){

    alert('다중 상품  UPC 가 아닙니다');

}

if(isset($_POST['uid'])){

    $db = new db();

    $stmt = $db->ntics_db->prepare("
            SELECT count( *) cnt
            FROM ople_set_amount_info a
            where upc =? and uid = ?
            ");
    $stmt->bindValue(1,$_POST['upc']);
    $stmt->bindValue(2,$_POST['uid']);
    $stmt->execute();
    $save_chk = $stmt->fetch(PDO::FETCH_ASSOC);

    if( $save_chk['cnt'] < 1 ) {

        alert('개발팀의 문의해주시기 바랍니다1');
    }

    $stmt = $db->ntics_db->prepare("
            update ople_set_amount_info
            set single_amount = ?
            where uid = ?
            ");
    $stmt->bindValue(1,$_POST['single_amount']);
    $stmt->bindValue(2,$_POST['uid']);

    if(!$stmt->execute()){
        alert('업데이트를 실패하였습니다');
    }



}else{

    $db = new db();

    $stmt = $db->ntics_db->prepare("
            SELECT count( *) cnt
            FROM ople_set_amount_info a
            where upc =? 
            ");
    $stmt->bindValue(1,$_POST['upc']);
    $stmt->execute();
    $save_chk = $stmt->fetch(PDO::FETCH_ASSOC);

    if( $save_chk['cnt'] > 0 ) {

        alert('개발팀의 문의해주시기 바랍니다2');
    }

    $stmt = $db->ntics_db->prepare("
INSERT INTO ople_set_amount_info (upc, single_amount, cdate)
VALUES (?, ?, ?)
            ");
    $stmt->bindValue(1,$_POST['upc']);
    $stmt->bindValue(2,$_POST['single_amount']);
    $stmt->bindValue(3,date('Y-m-d H:i:s'));
    if(!$stmt->execute()){
        alert('단품가 등록을 실패하였습니다');
    }

}
alert('적용 되었습니다','./set_item_amount_list.php');

$g4[title] = "다중세트상품 단품가";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
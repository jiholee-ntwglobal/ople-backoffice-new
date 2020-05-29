<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-04
 * Time: 오후 2:26
 */

$sub_menu = "300567";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

if(!$_POST['it_id'] || trim($_POST['it_id'])==''){
    alert('개발팀의 문의해주세요');
}

if( !isset($_POST['data_item']) || empty($_POST['data_item']) || count($_POST['data_item'])<1){
    alert('상품코드를 입력해주세요');
}

if( !isset($_POST['child_it_id_qty']) || empty($_POST['child_it_id_qty']) || count($_POST['child_it_id_qty'])<1){
    alert('수량을 입력해주세요');
}

if((count(array_unique($_POST['data_item'])) != count($_POST['data_item']))){
    alert('중복된 값이 존재합니다.');
}

$select_query  ='';

foreach ($_POST['data_item'] as  $item){
    $select_query .= ",'".sql_safe_query($item)."'";
}
$select_query = substr($select_query, 1);

//it_id가 존재하지않는 상품이 있을 시
$it_id_chk = sql_fetch("
            SELECT count(*) cnt
            FROM yc4_item
            WHERE it_id IN (".$select_query.")
            ");

if($it_id_chk['cnt'] != count($_POST['data_item']) ){

    alert('존재하지않는 상품이있습니다 ');

}

if ($_POST['mode'] == 'insert') {

    $set_chk = sql_fetch("
            SELECT count(*) cnt 
            FROM yc4_item_set
            WHERE it_id = '".sql_safe_query(trim($_POST['it_id']))."'
            ");

    if($set_chk['cnt'] > 0 ){
        alert('세트상품이 존재합니다');
    }

    $insert = '';
    foreach ($_POST['data_item'] as $sort => $it_id){


        if(!isset($_POST['child_it_id_qty'][$sort])){
            alert('수량이 존재 하지않는 상품이있습니다');
        }
        $insert.= ",('".sql_safe_query(trim($_POST['it_id']))."','".sql_safe_query(trim($it_id))."','".sql_safe_query(trim($_POST['child_it_id_qty'][$sort]))."')";
    }
    $insert = substr($insert, 1);

    $result = sql_query("
          INSERT INTO yc4_item_set(it_id,
                                         child_it_id,
                                         child_qty
                                         )
            VALUES ".$insert."
    ");

    if(!$result ){
        alert('세트상품 상세페이지 생성에 실패하였습니다.');
    }

    alert('생성되었습니다.','set_item_info_list.php');
    //실행 끝


} elseif ($_POST['mode'] == 'update') {

    $set_it_id = sql_query("
            SELECT uid,
                   it_id,
                   child_it_id,
                   child_qty
            FROM yc4_item_set
            WHERE it_id = '".sql_safe_query(trim($_POST['it_id']))."'
            ");

   while ($row = sql_fetch_array($set_it_id)){

       if(in_array($row['child_it_id'],$_POST['data_item'])){

           if(  ( trim($_POST['child_it_id_qty'][array_keys($_POST['data_item'],$row['child_it_id'])[0]]) != $row['child_qty'])  ){

               sql_query("
               UPDATE yc4_item_set
               SET child_qty = '".sql_safe_query(trim($_POST['child_it_id_qty'][array_keys($_POST['data_item'],$row['child_it_id'])[0]]))."'
               WHERE uid = '".trim($row['uid'])."'
               ");

           }

           unset($_POST['data_item'][array_keys($_POST['data_item'],$row['child_it_id'])[0]]);
       }else{

           //delete
           sql_query("
                    DELETE FROM yc4_item_set
                    WHERE it_id = '".trim($row['it_id'])."' and child_it_id = '".trim($row['child_it_id'])."'
                ");
       }

   }//while

    $insert = '';
    if(!empty($_POST['data_item']) && count($_POST['data_item']) > 0) {

        foreach ($_POST['data_item'] as $sort => $it_id) {

            if(!isset($_POST['child_it_id_qty'][$sort])){
                alert('수량이 존재 하지않는 상품이있습니다');
            }

            //insert 문
            $insert.= ",('".sql_safe_query(trim($_POST['it_id']))."','".sql_safe_query(trim($it_id))."','".sql_safe_query(trim($_POST['child_it_id_qty'][$sort]))."')";
        }
        $insert = substr($insert, 1);

        //insert  실행
        sql_query("
            INSERT INTO yc4_item_set(it_id,
                                         child_it_id,
                                         child_qty
                                         )
            VALUES ".$insert."
                ");
    }
    alert('업데이트 되었습니다.','set_item_info_list.php');

}
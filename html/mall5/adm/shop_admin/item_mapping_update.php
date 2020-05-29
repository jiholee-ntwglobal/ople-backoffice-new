<?php
/**
 * Created by PhpStorm.
 * File name : item_mapping_update.php.
 * Comment :
 * Date: 2015-12-18
 * User: Minki Hong
 */
$sub_menu = "300110";
include_once "_common.php";
include_once $g4['full_path'] . '/lib/ople_mapping.php';

$ople_mapping = new ople_mapping();

if($_POST['mode'] == 'upc_chk'){

//    if($ople_mapping->upc_chk(trim($_POST['upc']))){
    if($ople_mapping->upc_chk($_POST['upc'])){
        echo "ok";
    }
    exit;
}

auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'ople_mapping'){
    $upc_arr = array();
    if(is_array($upc)){
        foreach ($upc as $key => $row_upc) {
            $upc_arr[$key]['upc'] = $row_upc;
        }
    }
    if(is_array($qty)){
        foreach ($qty as $key => $row_qty) {
            if(!$row_qty){
                $row_qty = 1;
            }
            $upc_arr[$key]['qty'] = $row_qty;
        }
    }

    $result = $ople_mapping->ople_mapping($it_id,$upc_arr);
    if($result === true){
        echo "
            <script>
                var url = '".$g4['shop_admin_path']."/item_mapping.php';
                if(confirm('매핑이 완료되었습니다. 확인하시겠습니까?')){
                    url = '".$g4['shop_admin_path']."/item_mapping_edit.php?it_id=".$it_id."';
                }
                location.href=url;
            </script>
        ";
    }else{
        alert('처리중 오류 발생! 관리자에게 문의해 주세요.');
    }
    exit;
}

exit;
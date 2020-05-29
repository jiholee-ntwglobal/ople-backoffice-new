<?php
include_once("./_common.php");
$it_id =isset($_GET['it_id'])?trim($_GET['it_id']):'';
$arr_yc4_item =array();
$escape=array("'",'"','\\');
if($it_id) {
    $it_id=str_replace($escape,'',($it_id));
    $sql = "select it_name,it_id from yc4_item where it_id  ='{$it_id}' ";
    $result = sql_fetch($sql);
    if ($result['it_id']) {
        echo get_image( $result['it_id'] . "_m",180,180).$arr_yc4_item['it_name']= get_item_name($result['it_name'],'list');
    } else {
        echo false;
    }
}else{
    echo false;
}

?>


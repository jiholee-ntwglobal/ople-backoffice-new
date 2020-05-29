<?php
$sub_menu = "300127";
include_once("./_common.php");
include_once $g4['full_path']."/lib/icode.sms.lib.php";
include_once $g4['full_path']."/lib/opk_db.php";

auth_check($auth[$sub_menu], "w");


if(is_numeric($_POST['amount'])!=1 || is_numeric($_POST['amount'])!=1){
    echo json_encode(array('result' => 'error', 'msg' =>"요청 데이터 오류입니다."));
    exit;
}
if($_POST['it_id']=="" || $_POST['amount']==""){
    echo json_encode(array('result' => 'error', 'msg' =>"요청 데이터 오류입니다."));
    exit;
}

$msrp_row = sql_fetch_array(sql_query("SELECT * FROM yc4_item_etc_amount WHERE it_id = '".$_POST['it_id']."' AND pay_code = '3'"));

if($msrp_row['uid']){
    sql_query("UPDATE yc4_item_etc_amount SET amount = '".$_POST['amount']."' WHERE uid = '".$msrp_row['uid']."'");
}else{
    sql_query("INSERT yc4_item_etc_amount SET 
            it_id = '".floatval($_POST['it_id'])."',
            pay_code = '3',
            amount = '".floatval($_POST['amount'])."',
            money_type = 'usd'");
}
echo json_encode(array('result' => 'ok', 'msg' => '수정이 완료되었습니다.'));

?>
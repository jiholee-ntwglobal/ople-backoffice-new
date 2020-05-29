<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2018-09-28
 * Time : 오후 4:59
 */

$sub_menu = "300667";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

switch ($_POST['mode']) {

    //등록
    case 'insert' :

        //중복검사
        $cnt = sql_fetch("SELECT count(*) cnt FROM yc4_weight_type_info WHERE type_name = '".$_POST['type_name']."'");

        if($cnt['cnt'] > 0) {
            alert("이미 등록된 분류명입니다.");
            exit;
        }else{
            $insert_fg = sql_query("INSERT yc4_weight_type_info SET 
                        type_name = '".$_POST['type_name']."',
                        weight_limit = '".$_POST['weight_limit']."'
                        ");

            if($insert_fg == false) {
                alert("개발팀에 문의주시기 바랍니다.");
            }else{
                alert("정상적으로 분류명이 등록되었습니다.","./item_weight_type_list.php");
            }
        }
        break;
    case 'modify':

        if(!$_POST['weight_type_id']){
            alert("개발팀에 문의해주시기 바랍니다.");
            exit;
        }

        $cnt = sql_fetch("SELECT count(*) cnt FROM yc4_weight_type_info WHERE weight_type_id = '".$_POST['weight_type_id']."'");

        if($cnt['cnt'] > 0) {
            $modify_fg = sql_query(
                                "UPDATE yc4_weight_type_info 
                                SET type_name = '".$_POST['type_name']."', weight_limit = '".$_POST['weight_limit']."' 
                                WHERE weight_type_id = '".$_POST['weight_type_id']."'"
                            );
            if($modify_fg == false){
                alert("개발팀에 문의주시기 바랍니다.");

            }else{
                alert("정상적으로 수정되었습니다.","./item_weight_type_list.php");

            }
        }else{
            alert("오류가 발생하였습니다. 개발팀에 문의해주시기 바랍니다.");
            exit;

        }
        break;
    case 'del':

        if(!$_POST['weight_type_id']){
            alert("개발팀에 문의해주시기 바랍니다.");
            exit;
        }
        $cnt = sql_fetch("SELECT count(*) cnt FROM yc4_item_weight_info WHERE weight_type_id = '".$_POST['weight_type_id']."'");

        if($cnt['cnt'] > 0){
            alert("이미 사용되고 있는 종류명입니다. 삭제가 불가능합니다. 삭제를 원하시면 개발팀에 문의주시기 바랍니다.");
            exit;
        }

        $fg = sql_query("DELETE FROM yc4_weight_type_info WHERE weight_type_id = '".$_POST['weight_type_id']."'");

        if($fg==false){
            alert("개발팀에 문의주시기 바랍니다.");
        }else{
            alert("삭제되었습니다.","./item_weight_type_list.php");
        }
        break;
    default:
        alert('유효하지않은 접근 방식입니다 .404');
        break;
}
alert('유효하지않은 접근 방식입니다 .404');
?>
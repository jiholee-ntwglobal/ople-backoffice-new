<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-08-07
* Time : 오후 4:58
*/

$sub_menu = "300160";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$date = date('Y-m-d');
if($_POST['mode']=="write"){ //등록 수정

    if($single_uid!=""){ //개별저장

        if($it_id_mode[$single_uid]=="insert"){
            sql_query("
            insert comment_insert
            set 
            st_dt = '".$st_dt[$single_uid]."',
            en_dt = '".$en_dt[$single_uid]."',
            comment = '".$comment[$single_uid]."',
            it_id = '".$it_id[$single_uid]."',
            create_dt = '$date'
            ");
        }else if($it_id_mode[$single_uid]=="update"){

            sql_query("
                        update comment_insert
                        set 
                        st_dt = '".$st_dt[$single_uid]."',
                        en_dt = '".$en_dt[$single_uid]."',
                        comment = '".$comment[$single_uid]."',
                        create_dt = '$date'            
                        where it_id = '".$it_id[$single_uid]."'
                        ");

        }
    }else { //일괄저장
        foreach ($_POST['chk_id'] as $chk_id) {

            if ($it_id_mode[$chk_id] == "insert") {
                sql_query($a="insert comment_insert
                        set 
                        st_dt = '" . $st_dt[$chk_id] . "',
                        en_dt = '" . $en_dt[$chk_id] . "',
                        comment = '" . $comment[$chk_id]."',
                        it_id = '" . $it_id[$chk_id] . "',
                        create_dt = '$date'            
                        ");
            } else if ($it_id_mode[$chk_id] == "update") {
                sql_query($a ="
                        update comment_insert
                        set 
                        st_dt = '" . $st_dt[$chk_id] . "',
                        en_dt = '" . $en_dt[$chk_id] . "',
                        comment = '" . $comment[$chk_id] . "',
                        create_dt = '$date'
                        where it_id = '" . $it_id[$chk_id] . "'
                        ");
            }
        }
    }

    alert("저장이 완료되었습니다.","./commnet_insert.php");


}else if($_POST['mode']=="delete"){ //삭제

    if($_POST['single_uid']!=""){ //개별삭제
        sql_query("delete  from comment_insert where it_id = '".$it_id[$single_uid]."'");
    }else { //일괄삭제
        foreach ($_POST['chk_id'] as $chk_id){
            sql_query("delete  from comment_insert where it_id = '".$it_id[$chk_id]."'");
        }
    }

    alert("삭제가 완료되었습니다.","./commnet_insert.php");

}


?>
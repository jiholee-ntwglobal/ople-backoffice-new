<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-06-24
* Time : 오후 5:46
*/


$sub_menu = "500600";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$it_id_arr = explode("<br>",str_replace("\r\n","<br>",$_POST['it_item_new']));
$it_id_arr = array_filter($it_id_arr);
$it_id_arr = array_unique($it_id_arr);


if($_REQUEST['mode']=="insert"){

    //중복 검사
    $chk = sql_fetch($a = "SELECT count(uid) cnt FROM yc4_keyword WHERE category = '".sql_safe_query($_POST['category'])."' AND keyword_name = '".trim($_POST['keyword_name'])."'");


    if($chk['cnt']>0){
        alert('이미 등록된 키워드입니다.');
    }

    $keyword_description = htmlspecialchars( str_replace("\r\n","<br>",$_POST['keyword_description']));

    // keyword insert
    $fg = sql_query($a = "INSERT INTO yc4_keyword(category, keyword_name, keyword_description, pc_image_url, pc_image_url_over, mobile_image_url, mobile_image_url_over, mobile_banner_url, sort, use_yn, create_dt, mb_id)
                                 VALUES ('".sql_safe_query($_POST['category'])."','".sql_safe_query($_POST['keyword_name'])."','".sql_safe_query($keyword_description)."',
                                 '".sql_safe_query($_POST['pc_image_url'])."','".sql_safe_query($_POST['pc_image_url_over'])."','".sql_safe_query($_POST['mobile_image_url'])."',
                                 '".sql_safe_query($_POST['mobile_image_url_over'])."','".sql_safe_query($_POST['mobile_banner_url'])."' ,'".sql_safe_query($_POST['keyword_sort'])."',
                                 '".sql_safe_query($_POST['use_yn'])."',now(),'{$member['mb_id']}')
                                ");

    $keyword_uid = mysql_insert_id();

    //keyword item insert
    $fg2 = true;

    if(count($it_id_arr)>0){
        foreach ($it_id_arr as $it_id){
            $fg2 = sql_query("INSERT INTO yc4_keyword_item(keyword_uid, it_id) VALUES ('".sql_safe_query($keyword_uid)."', '".sql_safe_query($it_id)."')");
        }
    }

    //모바일 메인에 노출될 키워드 랜덤추출
    keyword_rand_insert();

    if($fg==false || $fg2 == false){
        alert('개발팀에 문의 주시기바랍니다.');
    }

    alert('키워드 등록이 완료되었습니다.','./keyword_list.php');

}else  if($_REQUEST['mode'] =="update"){

    $chk_info = sql_fetch("SELECT keyword_name FROM yc4_keyword WHERE uid = '".$_POST['keyword_uid']."'");

    if($chk_info['keyword_name']!=trim($_POST['keyword_name'])) {

        //중복 검사
        $chk = sql_fetch("SELECT count(uid) cnt FROM yc4_keyword WHERE category = '" . sql_safe_query($_POST['category']) . "' AND keyword_name = '" . trim($_POST['keyword_name']) . "'");

        if ($chk['cnt'] > 0) {
            alert('이미 등록된 키워드입니다.');
            exit;
        }
    }

   $keyword_description = htmlspecialchars( str_replace("\r\n","<br>",$_POST['keyword_description']));
    // keyword update
    $fg = sql_query($a = "UPDATE yc4_keyword SET 
                           keyword_name = '".sql_safe_query(trim($_POST['keyword_name']))."',
                           keyword_description = '".sql_safe_query($keyword_description)."',
                           pc_image_url = '".sql_safe_query($_POST['pc_image_url'])."',
                           pc_image_url_over = '".sql_safe_query($_POST['pc_image_url_over'])."',
                           mobile_image_url = '".sql_safe_query($_POST['mobile_image_url'])."',
                           mobile_image_url_over = '".sql_safe_query($_POST['mobile_image_url_over'])."',
                           mobile_banner_url = '".sql_safe_query($_POST['mobile_banner_url'])."',
                           sort = '".sql_safe_query($_POST['keyword_sort'])."',
                           use_yn = '".sql_safe_query($_POST['use_yn'])."',                           
                           update_dt = now()
                          WHERE uid = '".$_POST['keyword_uid']."'");


    //keyword item delete
    $fg3 = sql_query($a = "DELETE FROM yc4_keyword_item WHERE keyword_uid = '".$_POST['keyword_uid']."'");

    //keyword item insert
    $fg2 = true;
    if(count($it_id_arr)>0){
        foreach ($it_id_arr as $it_id){
            $fg2 = sql_query("INSERT INTO yc4_keyword_item(keyword_uid, it_id) VALUES ('".sql_safe_query($keyword_uid)."', '".sql_safe_query($it_id)."')");
        }
    }

    //모바일 메인에 노출될 키워드 랜덤추출
    keyword_rand_insert();

    if($fg==false || $fg2 == false ||$fg3 == false){
        alert('개발팀에 문의 주시기바랍니다.');
    }

    alert('키워드 수정이 완료되었습니다.','./keyword_list.php');

}else if($_REQUEST['mode']=="delete"){



    $fg = sql_query("DELETE FROM yc4_keyword WHERE uid = '".sql_safe_query($_REQUEST['keyword_uid'])."'");

    $fg2 = sql_query("DELETE FROM yc4_keyword_item WHERE keyword_uid = '".sql_safe_query($_REQUEST['keyword_uid'])."'");

    //모바일 메인에 노출될 키워드 랜덤추출
    keyword_rand_insert();


    if($fg==false || $fg2 == false){
        alert('개발팀에 문의 주시기바랍니다.');
    }

    alert('키워드 삭제가 완료되었습니다.','./keyword_list.php');
}

function keyword_rand_insert(){
    $insert_value = '';

    $que = sql_query("select uid from yc4_keyword where use_yn = 'y' order by rand() limit 10");
    $i = 1;
    while ($row = sql_fetch_array($que)) {
        $insert_value .= "('" . $row['uid'] . "','" . $i . "',now()),";
        $i++;
    }

    //and mobile_banner_url is not null
    $insert_sql = "insert into yc4_keyword_rand(keyword_uid, sort, create_dt) values" . substr($insert_value, 0, -1);
    sql_query("delete from yc4_keyword_rand");
    sql_query($insert_sql);

    //메인 데이터 캐싱 파일 재생성
    file_get_contents("http://www.ople.com/mall5/cron/main_data_cache.php");
}


?>



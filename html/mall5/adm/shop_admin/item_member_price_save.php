<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-19
 * Time: 오후 5:51
 */
$sub_menu = "500550";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

// 유효성 검사
if(!isset($_POST['start_date']) || trim($_POST['start_date']) == '' ){
    alert('시작날짜를 선택해주세요.');
}
if($_POST['start_date']<date('Y-m-d') && $_POST['mode'] == 'insert'){
    alert('시작 날짜가 현재날짜보다 이전입니다 ');
}
if(!isset($_POST['end_date']) || trim($_POST['end_date']) == '' ){
    $_POST['end_date'] = 'NULL';
}else{
    if($_POST['end_date']<date('Y-m-d')){
         alert('종료 날짜가 현재날짜보다 이전입니다 ');
    }
    $_POST['end_date'] = "'".sql_safe_query(trim($_POST['end_date']))."'";
}
if(!isset($_POST['member_price']) || trim($_POST['member_price']) == '' ){
    alert('회원가를 입력해주세요');
}
if(!isset($_POST['it_id']) || trim($_POST['it_id']) == '' ){
    alert('회원가를 입력해주세요');
}

//업데이트 일 경우 where
$where = '';
if($_POST['mode'] == 'update'){

    if((int)$_POST['uid']<1 ){
        alert('개발팀의 문의 해주세요.');
    }

    $where = " and uid != '".sql_safe_query(trim($_POST['uid']))."'";
}

//날짜가 겹치는 지 확인
$date_cnt = sql_fetch("
            SELECT COUNT(*) cnt
            FROM item_member_price
            WHERE     it_id = '".sql_safe_query(trim($_POST['it_id']))."'
                  AND (   '".sql_safe_query(trim($_POST['start_date']))."' BETWEEN start_date
                                 AND ifnull(end_date, '2077-12-31')
                       OR ".($_POST['end_date']=='NULL' ? "'2077-12-31'" : $_POST['end_date'])." BETWEEN start_date
                                 AND ifnull(end_date, '2077-12-31'))
                                 $where
            ");

if($date_cnt['cnt'] >0){
    alert('기존에 등록된 멤버프라이스 상품이랑 날짜가 겹칩니다');
}

//update & insert
if($_POST['mode']){

    if( $_POST['mode'] == 'update'){

        //업데이트 it_id 확인
        $update_chk = sql_fetch("
            SELECT COUNT(*) cnt,it_id
            FROM item_member_price
            WHERE uid = '".sql_safe_query(trim($_POST['uid']))."' 
            ");

        if( $update_chk['cnt'] <1 || !trim($update_chk['it_id']) || $update_chk['it_id'] != $_POST['it_id']){

            alert('잘못된 접근입니다');

        }

        sql_query("
          update item_member_price
          set member_price = '".sql_safe_query(trim($_POST['member_price']))."',
               end_date = ".$_POST['end_date']."           
           WHERE uid = '".sql_safe_query(trim($_POST['uid']))."'
            ");

        alert('업데이트 되었습니다','./item_member_price_form.php?mode=update&uid='.$_POST['uid']);

    }elseif ($_POST['mode'] == 'insert'){

        sql_query("
            INSERT INTO item_member_price(it_id,
                                          member_price,
                                          start_date,
                                          end_date,
                                          create_date,
                                          create_id)
            VALUES ('".sql_safe_query(trim($_POST['it_id']))."',
                    '".sql_safe_query(trim($_POST['member_price']))."',
                    '".sql_safe_query(trim($_POST['start_date']))." ',
                    ".$_POST['end_date'].",
                    now(),
                    '".sql_safe_query($member['mb_id'])."')
                    ");

        alert('생성 되었습니다','./item_member_price_form.php?mode=insert&it_id='.$_POST['it_id']);
    }

}

alert('잘못된 접근 방식입니다');

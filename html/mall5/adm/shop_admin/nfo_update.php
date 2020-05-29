<?php
/**
 * Created by PhpStorm.
 * File name : nfo_update.php.
 * Comment :
 * Date: 2016-01-11
 * User: Minki Hong
 */

//print_r(PDO::getAvailableDrivers()); exit;
//phpinfo(); exit;
$sub_menu = "300120";
include_once "./_common.php";
include_once $g4['full_path'] . '/lib/nfo.php';

$nfo = new nfo();

//print_r($_POST);

$data = array();
if($_POST['uid']){
    $data['uid'] = stripcslashes($_POST['uid']);
}
$data['upc'] = stripcslashes($_POST['upc']);
$data['it_id'] = stripcslashes($_POST['it_id']);
$data['it_maker_eng'] = stripcslashes($_POST['brand_eng']);
$data['it_maker_kor'] = stripcslashes($_POST['brand_kor']);
$data['it_name_kor'] = stripcslashes($_POST['name_kor']);
$data['it_name_eng'] = stripcslashes($_POST['name_eng']);
$data['it_name_comment'] = stripcslashes($_POST['name_comment']);
$data['it_amount_usd'] = stripcslashes($_POST['it_amount_usd']);
$data['it_cust_amount_usd'] = stripcslashes($_POST['it_cust_amount_usd']);
if($_POST['ca_id']){

    $data['ca_id_arr'] = json_encode($_POST['ca_id']);

}else{
    $data['ca_id_arr'] = null;
}
$data['img_url'] = stripcslashes($_POST['image_url']);
$data['it_explan'] = stripcslashes($_POST['it_explan']);
$data['desc_kor'] = trim(stripcslashes($_POST['desc_kor']));
$data['desc_direction'] = stripcslashes($_POST['desc_direction']);
$data['desc_warning'] = stripcslashes($_POST['desc_warning']);
$data['desc_eng'] = stripcslashes($_POST['desc_eng']);
$data['desc_supp'] = stripcslashes($_POST['desc_supp']);
$data['it_health_cnt'] = stripcslashes($_POST['it_health_cnt']);
$data['it_origin'] = stripcslashes($_POST['it_origin']);
$data['it_order_onetime_limit_cnt'] = stripcslashes($_POST['it_order_onetime_limit_cnt']);
$data['list_clearance'] = stripcslashes($_POST['list_clearance']);
$data['it_use'] = stripcslashes($_POST['it_use']);
$data['soldout_fg'] = stripcslashes($_POST['soldout_fg']);
$data['direct_fg'] = stripcslashes($_POST['direct_fg']);
$data['ople_option'] = stripcslashes($_POST['ople_option']);

//print_r($data);
//exit;
//var_dump($nfo->tmp_item_insert($data));
$result = $nfo->temp_item_insert($data);

$cps_ca_name =  $_POST['cps_ca_name'];
$cps_ca_name2 =  $_POST['cps_ca_name2'];
$cps_ca_name3 =  $_POST['cps_ca_name3'];
$cps_ca_name4 =  $_POST['cps_ca_name4'];

$cps_count = sql_fetch("select count(*) cnt from yc4_cps_item where it_id = '".$it_id."'");

if($cps_count['cnt']>0){
    if($_POST['cps_use_yn']=="y"){

        //update
        sql_query($a="update yc4_cps_item set 
                        cps_ca_name = '$cps_ca_name',
                        cps_ca_name2 = '$cps_ca_name2',
                        cps_ca_name3 = '$cps_ca_name3',
                        cps_ca_name4 = '$cps_ca_name4',
                        update_date = now(),
                        use_yn ='y'
                        where it_id = '$it_id'");
    }else{
        //update
        sql_query($a="update yc4_cps_item set 
                        cps_ca_name = '$cps_ca_name',
                        cps_ca_name2 = '$cps_ca_name2',
                        cps_ca_name3 = '$cps_ca_name3',
                        cps_ca_name4 = '$cps_ca_name4',
                        update_date = now(),
                        use_yn = 'n'
                        where it_id = '$it_id'");

    }

}else{
    if($_POST['cps_use_yn']=="y") {

        //insert
        sql_query($a = "insert into yc4_cps_item(it_id, cps_ca_name, cps_ca_name2, cps_ca_name3, cps_ca_name4, create_date, use_yn)
          values ('$it_id','$cps_ca_name','$cps_ca_name2','$cps_ca_name3','$cps_ca_name4',now(), 'y')");
    }

}



if($result['it_id']){
    alert('상품 등록이 완료되었습니다.','./nfo_detail.php?upc='.$data['upc'].'&it_id='.$result['it_id'].'&'.$_POST['list_page_qstr']);
}elseif($result['uid']){
    alert('상품 임시 등록이 완료되었습니다.','./nfo_detail.php?upc='.$data['upc'].'&uid='.$result['uid'].'&'.$_POST['list_page_qstr']);
}else{
	var_dump($result);
	exit;
    alert('처리중 오류 발생 관리자에게 문의하세요!','./nfo.php');
}
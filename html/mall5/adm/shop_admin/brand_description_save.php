<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-06-04
 * Time: 오후 6:15
 */
$sub_menu = "600971";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

function unescape($text)
{
    return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', create_function('$word','return iconv("UTF-16LE", "UHC", chr(hexdec(substr($word[1], 2, 2))).chr(hexdec(substr($word[1], 0, 2))));'), $text));
}

$_POST['brand'] = $_POST['brand'] ? sql_safe_query(unescape($_POST['brand'])) : "";
$_POST['st_dt'] = preg_replace("/[^0-9]*/s", "", $_POST['st_dt']);
$_POST['en_dt'] = preg_replace("/[^0-9]*/s", "", $_POST['en_dt']);
$_POST['use_flag_pc'] = isset($_POST['use_flag_pc'])? '1': '0';
$_POST['it_maker_description_pc'] =  sql_safe_query(stripslashes($_POST['it_maker_description_pc']));
$_POST['use_flag_mo'] = isset($_POST['use_flag_mo'])? '1': '0';
$_POST['it_maker_description_mo'] =  sql_safe_query(stripslashes($_POST['it_maker_description_mo']));

switch ($_POST['mode']){
    case 'insert':

        if($_POST['brand']==''){
            alert('브랜드가 없습니다');
        }

        $banner_data = sql_fetch("
	select
		count(*) cnt 
	from
		brand_description
	where
		uid = '" . sql_safe_query($_POST['uid']) . "' and it_maker ='".$_POST['brand']."'
");

        if($banner_data['cnt']>0){
            alert('오류');
        }

        sql_query("
insert into brand_description
(it_maker, it_maker_description_pc, use_flag_pc, it_maker_description_mo, use_flag_mo, start_date, end_date, create_date, create_id) VALUES
(
          '".$_POST['brand']."'
        , '".$_POST['it_maker_description_pc']."'
        , '".$_POST['use_flag_pc']."'
        , '".$_POST['it_maker_description_mo']."'
        , '".$_POST['use_flag_mo']."'
        , '".$_POST['st_dt']."'
        , '".$_POST['en_dt']."'
        , now()
        , '".sql_safe_query($member['mb_id'])."'
)
        ");
        alert('생성 되었습니다','./brand_description_list.php');
        break;

    case 'update':

        if($_POST['brand']==''){
            alert('브랜드가 없습니다');
        }

        if(!is_numeric($_POST['uid'])){
            alert('업데이트 할수 없습니다.');
        }

        $banner_data = sql_fetch("
	select
		count(*) cnt 
	from
		brand_description
	where
		uid = '" . sql_safe_query($_POST['uid']) . "' and it_maker ='".$_POST['brand']."'
");

        if($banner_data['cnt']<1){
            alert('업데이트 할수 없습니다');
        }

        sql_query("
        update brand_description
        set it_maker ='".$_POST['brand']."'
        , use_flag_pc = '".$_POST['use_flag_pc']."'
        , start_date = '".$_POST['st_dt']."'
        , end_date = '".$_POST['en_dt']."'
        , use_flag_mo = '".$_POST['use_flag_mo']."'
        , it_maker_description_pc = '".$_POST['it_maker_description_pc']."'
        , it_maker_description_mo = '".$_POST['it_maker_description_mo']."'
        where uid = '" . sql_safe_query($_POST['uid']) . "' and it_maker ='".$_POST['brand']."'
        ");
        alert('업데이트 되었습니다','./brand_description_list.php');
        break;

    case 'delete':

        if(!is_numeric($_POST['uid'])){
            alert('삭제 할수 없습니다.');
        }

        sql_query("delete from brand_description  where uid = '" . sql_safe_query($_POST['uid']) . "'");

        alert('삭제 되었습니다','./brand_description_list.php');

        break;
    default :
        alert('오류');
        break;
}

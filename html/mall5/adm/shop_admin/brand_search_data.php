<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-06-04
 * Time: 오후 4:35
 */
include_once("./_common.php");
function unescape($text)
{
    return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', create_function('$word','return iconv("UTF-16LE", "UHC", chr(hexdec(substr($word[1], 2, 2))).chr(hexdec(substr($word[1], 0, 2))));'), $text));
}

switch ($_POST['mode']){

    case 'brand_search' :

        if(!$_POST['brand']){
            alert('개발팀에게 문의해주시기 바랍니다.');
        }

        $sql = "
            select distinct a.it_maker from yc4_item a
            where it_maker like '%".sql_safe_query(unescape(trim($_POST['brand'])))."%'
                ";

        $result = sql_query($sql);

        $data = array();

        while ($row = sql_fetch_array($result)){
            array_push($data,$row);
        }

        echo json_encode($data);

        break;

    case 'banner_data' :

        if(!$_POST['brand']){
            alert('개발팀에게 문의해주시기 바랍니다.');
        }
        $sql = "
SELECT DISTINCT a.it_maker,
                b.uid,
                b.it_maker_description_pc,
                b.use_flag_pc,
                b.it_maker_description_mo,
                b.use_flag_mo,
                b.start_date,
                b.end_date
FROM yc4_item a LEFT JOIN brand_description b ON b.it_maker = a.it_maker
where a.it_maker like '%".sql_safe_query(unescape(trim($_POST['brand'])))."%'
                ";
        $data = sql_fetch($sql);

        echo json_encode($data);
        break;
}
<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-07-19
 * Time: 오후 2:03
 */
$sub_menu = "300123";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

switch ($_POST['mode']){

    //삭제
    case 'del' :
        //삭제
        $fg=sql_query("
         DELETE FROM yc4_item_weight
         WHERE it_id = '".sql_safe_query($_POST['it_id'])."'
            ");

        $fg1 =sql_query("
         DELETE FROM yc4_item_weight_info
         WHERE it_id = '".sql_safe_query($_POST['it_id'])."'
            ");
        if($fg==false || $fg1 == false){
            alert('개발팀의 문의 주시기바랍니다 .123');
        }
        alert('삭제되었습니다','./item_weight_list.php');
        break;

    //아이디 체크
    case 'it_id_chk' :
        //유효성 검사
        if(!$_POST['it_id']){
            alert('개발팀에게 문의해주시기 바랍니다.');
        }
        $sql = "
            SELECT count(*) cnt
            FROM yc4_item a LEFT OUTER JOIN yc4_item_weight b ON a.it_id = b.it_id
            WHERE b.it_id IS NULL AND a.it_id = '".sql_safe_query($_POST['it_id'])."'
                ";
        $cnt = sql_fetch($sql);

        $sql_info = "
                     SELECT count(*) cnt
                    FROM yc4_item a LEFT OUTER JOIN yc4_item_weight_info b ON a.it_id = b.it_id
                    WHERE b.it_id IS NULL AND a.it_id = '".sql_safe_query($_POST['it_id'])."'
                        ";
        $cnt_info = sql_fetch($sql_info);


        $data = array();

        //data 추출하여 보내기
        if($cnt['cnt'] > 0 || $cnt_info['cnt']>0){
            $it_id = sql_fetch("
        SELECT it_id, it_name FROM yc4_item where it_id ='".sql_safe_query($_POST['it_id'])."' 
        ");
            $data['img'] = get_it_image($it_id['it_id'] . '_s', 200, 200, null, null, false, false, false);
            $data['name'] = get_item_name($it_id['it_name'], 'list');
            $data['fg'] =true;
        }else{
            $data['fg'] = false;
        }
        echo json_encode($data) ;
        exit;
        break;

    //생성
    case 'insert' :
        //유효성 검사
        if(!$_POST['type']){
            alert('개발팀의 문의 주시기바랍니다 .123');
        }
        $unit = array('oz'=>'28.349523','lb'=>'453.59237','g'=>'1');
        if(!is_numeric($_POST['weight'])){
            alert('무게는 숫자만 가능합니다');
        }
        if(isset($unit[$_POST['weight_unit']])){
            $weight = round($_POST['weight']*$unit[$_POST['weight_unit']]);
        }


        $sql = "
            SELECT count(*) cnt
            FROM yc4_item a LEFT OUTER JOIN yc4_item_weight b ON a.it_id = b.it_id
            WHERE b.it_id IS NULL AND a.it_id = '".sql_safe_query($_POST['it_id'])."'
                ";
        $cnt = sql_fetch($sql);

        $sql_info = "
            SELECT count(*) cnt
            FROM yc4_item a LEFT OUTER JOIN yc4_item_weight_info b ON a.it_id = b.it_id
            WHERE b.it_id IS NULL AND a.it_id = '".sql_safe_query($_POST['it_id'])."'
                ";
        $cnt_info = sql_fetch($sql_info);

        $data = array();

        // 등록되지 않았다면 insert
        if($cnt['cnt'] > 0 || $cnt_info['cnt']>0){

            $fix_type_arr = array(1,2,4);
            if(in_array($_POST['type'], $fix_type_arr)){

                if($_POST['type']==1) $type = "q";
                if($_POST['type']==2) $type = "h";
                if($_POST['type']==4) $type = "m";

                $fg=sql_query("INSERT INTO yc4_item_weight(it_id, weight, type) 
                                 VALUES ('".sql_safe_query($_POST['it_id'])."', ".sql_safe_query($weight).", '".sql_safe_query($type)."')
                                ");

            }else{
                $type = "e";
                $fg=sql_query("INSERT INTO yc4_item_weight(it_id, weight, type)
                                 VALUES ('".sql_safe_query($_POST['it_id'])."', ".sql_safe_query($weight).", '".sql_safe_query($type)."')
                                ");
            }

            $fg1 =sql_query("INSERT INTO yc4_item_weight_info(it_id, weight, weight_type_id)
                             VALUES ('".sql_safe_query($_POST['it_id'])."', ".sql_safe_query($weight).", '".sql_safe_query($_POST['type'])."')
                            ");


            if($fg==false || $fg1 == false){
                alert('개발팀의 문의 주시기바랍니다 .123');
            }
            alert('생성되었습니다','./item_weight_list.php');
        }else{
            alert('없는 상품이거나 등록된 상품입니다');
        }
        break;

    case 'modify':

        //유효성 검사
        if(!$_POST['type']){
            alert('개발팀의 문의 주시기바랍니다 .123');
        }
        $unit = array('oz'=>'28.349523','lb'=>'453.59237','g'=>'1');
        if(!is_numeric($_POST['weight'])){
            alert('무게는 숫자만 가능합니다');
        }
        if(isset($unit[$_POST['weight_unit']])){
            $weight = round($_POST['weight']*$unit[$_POST['weight_unit']]);
        }


        $sql = "
            SELECT count(*) cnt
            FROM yc4_item a LEFT OUTER JOIN yc4_item_weight b ON a.it_id = b.it_id
            WHERE b.it_id IS NOT NULL AND a.it_id = '".sql_safe_query($_POST['it_id'])."'
                ";
        $cnt = sql_fetch($sql);

        $sql_info = "
            SELECT count(*) cnt
            FROM yc4_item a LEFT OUTER JOIN yc4_item_weight_info b ON a.it_id = b.it_id
            WHERE b.it_id IS NOT NULL AND a.it_id = '".sql_safe_query($_POST['it_id'])."'
                ";
        $cnt_info = sql_fetch($sql_info);

        $data = array();

        // 등록되었다면 update
        if($cnt['cnt'] > 0 || $cnt_info['cnt']>0){

            $fix_type_arr = array(1,2,4);
            if(in_array($_POST['type'], $fix_type_arr)){

                if($_POST['type']==1) $type = "q";
                if($_POST['type']==2) $type = "h";
                if($_POST['type']==4) $type = "m";

                $fg=sql_query("UPDATE yc4_item_weight SET
                                weight = '".sql_safe_query($weight)."',
                                type = '".sql_safe_query($type)."'
                                WHERE it_id = '".sql_safe_query($_POST['it_id'])."'
                                ");

            }else{
                $type = "e";
                $fg=sql_query("UPDATE yc4_item_weight SET
                                weight = '".sql_safe_query($weight)."',
                                type = '".sql_safe_query($type)."'
                                WHERE it_id = '".sql_safe_query($_POST['it_id'])."'
                                ");
            }

            $fg1 =sql_query("UPDATE yc4_item_weight_info SET
                                weight = '".sql_safe_query($weight)."',
                                weight_type_id = '".sql_safe_query($_POST['type'])."'
                                WHERE it_id = '".sql_safe_query($_POST['it_id'])."'
                            ");


            if($fg==false || $fg1 == false){
                alert('개발팀의 문의 주시기바랍니다 .123');
            }
            alert('수정되었습니다','./item_weight_list.php');
        }else{
            alert('없는 상품이거나 등록된 상품입니다');
        }

        break;
    default :
        alert('유효하지않은 접근 방식입니다 .404');
        break;
}
alert('유효하지않은 접근 방식입니다 .404');
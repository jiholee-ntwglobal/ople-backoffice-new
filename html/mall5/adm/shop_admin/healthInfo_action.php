<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-06-20
 * Time: 오후 6:00
 */
include_once("./_common.php");
$sub_menu = "600970";
auth_check($auth[$sub_menu], "w");


//insert & update data
if(!isset($_POST['list_title']) || trim($_POST['list_title']) == '' ){
    alert('리스트 제목을 입력해주세요');
}
if(!isset($_POST['list_subtitle']) || trim($_POST['list_subtitle']) == '' ){
    alert('리스트 부제목을 입력해주세요');
}
if(!isset($_POST['list_imamge']) || trim($_POST['list_imamge']) == '' ){
    alert('리스트 이미지 URL을 입력해주세요');
}
if(!isset($_POST['content_title1']) || trim($_POST['content_title1']) == '' ){
    alert('본문 부제목1을 입력해주세요');
}
if(!isset($_POST['content_title2']) || trim($_POST['content_title2']) == '' ){
    alert('본문 부제목2을 입력해주세요');
}
if(!isset($_POST['content_image']) || trim($_POST['content_image']) == '' ){
    alert('본문 이미지 URL을 입력해주세요');
}
if(!isset($_POST['content']) || trim($_POST['content']) == '' ){
    alert('본문 내용을 입력해주세요');
}
if(!isset($_POST['category_code']) || !is_numeric($_POST['category_code']) ){
    alert('카테고리를 선택해주세요');
}

//검색 관련 데이터 content는 다포함하여 따로
$health_names_search = 'list_title, list_subtitle, content_title1, content_title2, keyword, contents_title, contents_content, contents_product_desct_id';
$search_data = '';

//escape,trim,검색 관련 데이터,post data
foreach ($_POST as  $health_name => $value){
    if(is_array($value)){
        $health_names_search_key=$health_name;
        $health_name =array();
        foreach ($value as $key => $value_item){
            if(strpos($health_names_search,$health_names_search_key) !== false){
                $search_data .= trim($value_item);
            }
            $health_name[$key] = sql_safe_query(trim($value_item));
        }
    }else{
        if(strpos($health_names_search, $health_name) !== false || $health_name =='content'){
            $search_data .= trim($value);
        }
        $health_name = sql_safe_query(trim($value));
    }
}
//insert
if ($_POST['mode'] == 'insert'){
    //health_info  본문
    $insert_fg = sql_query("
        INSERT INTO health_info
        ( list_title, list_subtitle, list_image, content_title1, content_title2,
          content, keyword, product_title, status, create_date, 
          create_mb_id,content_image,category_code,search_data) VALUES 
          ('{$list_title}','{$list_subtitle}','{$list_imamge}','{$content_title1}','{$content_title2}'
          ,'{$content}','{$keyword}','{$product_title}','{$status}',now()
          ,'{$member['mb_id']}','{$content_image}','{$category_code}','{$search_data}')
    ");
    if($insert_fg == false){
        alert('개발팀의 문의해주시기 바랍니다');
    }
    $create_uid = mysql_insert_id();
    //health_info_product 추천상품
    if(count($product_item)>0){
        $health_product_insert_query = '';
        foreach($product_item as $sort=>$it_id){
            $health_product_insert_query .= ($health_product_insert_query ==''?'':',')."('{$create_uid}','{$it_id}','{$sort}')";
        }
        $health_info_product_fg = sql_query("
              INSERT INTO health_info_product
              (health_info_uid, it_id, sort) VALUES 
              {$health_product_insert_query}
        ");
        if($insert_fg == false){
            alert('개발팀의 문의해주시기 바랍니다');
        }
    }
    //health_info_contents 서브 컨텐츠
    if(count($contents_title)>0){
        foreach($contents_title as $sub_it_id_key=> $sub){
            $health_info_contents_fg = sql_query("
              INSERT INTO health_info_contents
              (health_info_uid, contents_title, contents_content, contents_image) VALUES 
              ('{$create_uid}','{$sub}','{$contents_content[$sub_it_id_key]}','{$contents_image[$sub_it_id_key]}')
              ");
            if($health_info_contents_fg == false){
                alert('개발팀의 문의해주시기 바랍니다');
            }
            $sub_create_uid = mysql_insert_id();

            //health_info_contents_product 서브 컨텐츠 상품
            if(isset($_POST["subcontents_item".$sub_it_id_key])){
                $health_info_contents_product_query = '';
                foreach ($_POST["subcontents_item".$sub_it_id_key] as $key => $value){
                    $health_info_contents_product_query .= ($health_info_contents_product_query ==''?'':',')."('{$sub_create_uid}','{$value}','{$_POST["contents_product_desct_id".$sub_it_id_key][$key]}','{$key}')";
                }
                $health_info_contents_product_fg = sql_query("
              INSERT INTO health_info_contents_product
              (health_info_contents_uid, it_id, product_desc, sort) VALUES 
              $health_info_contents_product_query
              ");
                if($health_info_contents_product_fg == false){
                    alert('개발팀의 문의해주시기 바랍니다');
                }
            }

        }
    }
    alert('생성되었습니다','./healthInfo_list.php');
//update
}elseif ($_POST['mode'] == 'update'){
    if($uid==''){
        alert('개발팀의 문의해주시기 바랍니다1');
    }
    //health_info 본문 업데이트
    $update_fg = sql_query("
        UPDATE health_info
        SET list_title = '{$list_title}',
            list_subtitle = '{$list_subtitle}',
            list_image = '{$list_imamge}',
            content_title1 = '{$content_title1}',
            content_title2 = '{$content_title2}',
            content_image = '{$content_image}',
            content = '{$content}',
            keyword = '{$keyword}',
            product_title = '{$product_title}',
            status = '{$status}',
            update_date = now(),
            update_mb_id = '{$member['mb_id']}',
            category_code = '{$category_code}',
            search_data = '{$search_data}'
        WHERE health_info_uid = '{$uid}'
    ");
    if($update_fg == false){
        alert('개발팀의 문의해주시기 바랍니다2');
    }
    //health_info_product 추천상품 업데이트
    $health_info_product_list_query = sql_query("
            SELECT health_info_product_uid,it_id,sort
            FROM health_info_product
            WHERE health_info_uid = '{$uid}'
            order by sort asc
            ");
    $health_info_product_list = array();
    while ($row= sql_fetch_array($health_info_product_list_query)){
        if(isset($product_item[$row['sort']])){
            if($product_item[$row['sort']] !=  $row['it_id']){
                sql_query("
                  UPDATE health_info_product
                  SET it_id = '{$product_item[$row['sort']]}', sort = '{$row['sort']}'
                  WHERE health_info_product_uid = '{$row['health_info_product_uid']}'
                  ");
            }
            unset($product_item[$row['sort']]);
        }else{
            sql_query("
              DELETE FROM health_info_product
              WHERE health_info_product_uid = '{$row['health_info_product_uid']}'
             ");
        }
    }
    if(count($product_item)>0) {
        foreach ($product_item as $sort => $it_id) {
            $health_product_insert_query .= ($health_product_insert_query == '' ? '' : ',') . "('{$uid}','{$it_id}','{$sort}')";
        }
        $health_info_product_fg = sql_query("
            INSERT INTO health_info_product
            (health_info_uid, it_id, sort) VALUES 
            {$health_product_insert_query}
            ");
    }
    //health_info_contents 서브컨텐츠 업데이트
    $health_info_contents_uid_result = sql_query("
            SELECT health_info_contents_uid, health_info_uid
            FROM health_info_contents
            WHERE health_info_uid = '{$uid}'
            ");
    $health_info_contents_uid = array();
    while ($row= sql_fetch_array($health_info_contents_uid_result)){
        $health_info_contents_uid[] = $row['health_info_contents_uid'];
    }
    //health_info_contents update delete
    if(count($contents_title)>0){
        //health_info_contents 삭제된거 제거
        foreach($contents_title as $sub_it_id_key=> $sub){
            $contents_title_diff[] =$sub_it_id_key;
        }
        $array_diffs=array_diff($health_info_contents_uid,$contents_title_diff);
        sql_query("
                DELETE FROM health_info_contents
                WHERE health_info_contents_uid in ("."'".implode("','",$array_diffs)."'".")
                ");
        sql_query("
                DELETE FROM health_info_contents_product
                WHERE health_info_contents_uid in ("."'".implode("','",$array_diffs)."'".")
                ");
        foreach($contents_title as $sub_it_id_key=> $sub){
            $sub_create_uid= array();
            if(in_array($sub_it_id_key,$health_info_contents_uid)){
                $health_info_contents_fg = sql_query("
                UPDATE health_info_contents
                SET contents_title = '{$sub}', contents_content = '{$contents_content[$sub_it_id_key]}', contents_image = '{$contents_image[$sub_it_id_key]}'
                WHERE health_info_contents_uid = '{$sub_it_id_key}' AND health_info_uid = '{$uid}'
                ");
                if($health_info_contents_fg == false){
                    alert('개발팀의 문의해주시기 바랍니다4');
                }
                $sub_create_uid['uid'] = $sub_it_id_key;
             /*   sql_query("
                DELETE FROM health_info_contents_product
                WHERE health_info_contents_uid ='{$sub_it_id_key}'
                ");*/
            }else{
                $health_info_contents_fg = sql_query("
              INSERT INTO health_info_contents
              (health_info_uid, contents_title, contents_content, contents_image) VALUES 
              ('{$uid}','{$sub}','{$contents_content[$sub_it_id_key]}','{$contents_image[$sub_it_id_key]}')
              ");
                if($health_info_contents_fg == false){
                    alert('개발팀의 문의해주시기 바랍니다5');
                }
                $sub_create_uid['uid'] = mysql_insert_id();

            }



//서브 컨텐츠 아이템
            $health_info_contents_product_list_query = sql_query("
                SELECT *
                FROM health_info_contents_product
                WHERE health_info_contents_uid = '{$sub_it_id_key}'
                ORDER BY sort ASC
                 ");
            while ($row= sql_fetch_array($health_info_contents_product_list_query)){
                if(isset($_POST["subcontents_item".$sub_it_id_key][$row['sort']])){
                    if(($_POST["subcontents_item".$sub_it_id_key][$row['sort']] !=  $row['it_id']) || ($_POST["contents_product_desct_id".$sub_it_id_key][$row['sort']] !=  $row['product_desc'])){
                        sql_query("
                          UPDATE health_info_contents_product
                          SET health_info_contents_uid = '{$sub_it_id_key}',
                                it_id = '{$_POST["subcontents_item".$sub_it_id_key][$row['sort']]}',
                                product_desc = '{$_POST["contents_product_desct_id".$sub_it_id_key][$row['sort']]}',
                                sort = '{$row['sort']}'
                          WHERE health_info_contents_product_uid = '{$row['health_info_contents_product_uid']}'
                        ");
                    }
                    unset($_POST["subcontents_item".$sub_it_id_key][$row['sort']]);
                    unset($_POST["contents_product_desct_id".$sub_it_id_key][$row['sort']]);
                }else{
                    sql_query("
              DELETE FROM health_info_contents_product
              WHERE health_info_contents_product_uid = '{$row['health_info_contents_product_uid']}'
             ");
                }
            }

            if(count($_POST["subcontents_item".$sub_it_id_key])>0) {
                foreach ($_POST["subcontents_item".$sub_it_id_key] as $sort => $it_id) {
                    $health_info_contents_product_query .= ($health_info_contents_product_query == '' ? '' : ',') . "('$sub_it_id_key','{$it_id}','{$_POST["contents_product_desct_id".$sub_it_id_key][$sort]}','{$sort}')";
                }

                $health_info_product_fg = sql_query("
            INSERT INTO health_info_contents_product
            (health_info_contents_uid, it_id, product_desc, sort) VALUES 
            {$health_info_contents_product_query}
            ");
                if($health_info_product_fg == false){
                    alert('개발팀의 문의해주시기 바랍니다6');
                }
            }

        }

    }else{
        sql_query("
                DELETE FROM health_info_contents
                WHERE health_info_uid ='{$uid}'
                ");
    }
    alert('수정되었습니다','./healthInfo_list.php');
}


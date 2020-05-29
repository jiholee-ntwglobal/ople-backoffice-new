<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-05-11
 * Time: 오후 1:25
 */
$sub_menu = "400123";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");
if($_POST['mode'] == 'del'){
    $_POST['uid']=sql_safe_query($_POST['uid']);
    sql_query("
delete from gangja_mapping where uid ='{$_POST['uid']}'
    ");
    alert('삭제되었습니다',"./gangja_mapping_list.php");
}
if($_POST['mode'] == 'insert'){
    $_POST['gangja_it_id'] = sql_safe_query($_POST['gangja_it_id']);
    $gangja_mapping=sql_fetch("select count(*) cnt from gangja_mapping where gangja_it_id = '{$_POST['gangja_it_id']}'");
    if($gangja_mapping['cnt'] >0){
        alert('등록된 강자닷컴 코드입니다','./gangja_mapping_list.php');
    }
    $_POST['it_id'] = sql_safe_query($_POST['it_id']);
    $yc4_item=sql_fetch("select count(*) cnt from yc4_item where it_id = '{$_POST['it_id']}'");
    if($yc4_item['cnt'] <=0){
        alert('오플에 없는 상품입니다.','./gangja_mapping_list.php');
    }
    sql_query("
INSERT INTO gangja_mapping(gangja_it_id,
                           it_id,
                           create_dt,
                           create_id)
VALUES ('{$_POST['gangja_it_id']}',
        '{$_POST['it_id']}',
         now(),
        '{$member['mb_id']}')
    ");
    alert('생성되었습니다',"./gangja_mapping_list.php");
}

$g4[title] = "강자닷컴 상품 생성";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form method="post" onsubmit="return submit_chk()">
    <input type="hidden" name="mode" value="insert" >
<table class="table table-hover table-striped table-bordered">
    <thead>
    <tr>
        <th colspan="2" class="text-center"><strong><h4>강자닷컴 상품코드 = 오플 상품코드 매핑 생성</h4></strong></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th class="text-center">강자닷컴 코드</th>
        <td><input type="text" name="gangja_it_id" class="form-control"></td>
    </tr>
    <tr>
        <th class="text-center">오플상품코드</th>
        <th><input type="text" name="it_id" class="form-control"></th>
    </tr>
    <tr>
        <td class="text-right" colspan="2"><button  type="submit" class="btn btn-success">생성</button>&nbsp;<button class="btn btn-warning" type="button" onclick="location.href='./gangja_mapping_list.php'">목록</button></td>
    </tr>
    </tbody>
</table>
</form>
<script>
    function submit_chk() {
        var gangja_it_id= $('input[name=gangja_it_id]').val().trim();
        var it_id= $('input[name=it_id]').val().trim();
        if(gangja_it_id =='' || it_id==''){
            gangja_it_id =='' ? $('input[name=gangja_it_id]').focus(): $('input[name=it_id]').focus() ;
            alert('강자닷컴 코드,오플상품코드입력해주세요');
            return false;
        }

    }
</script>
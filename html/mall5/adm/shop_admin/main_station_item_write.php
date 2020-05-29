<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-03-30
 * Time: 오후 2:29
 */

$sub_menu = "600400";
include "_common.php";
auth_check($auth[$sub_menu], "w");

if($_POST['mode'] == 'ajax'){
    $it = sql_fetch("select it_name,it_use from ".$g4['yc4_item_table']." where it_id = '".sql_safe_query($_POST['it_id'])."'");
    if($it){
        $it['it_name'] = get_item_name($it['it_name']);
        echo json_encode($it);
    }
    exit;
}

if($_POST['mode'] == 'insert'){
    $it_id_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_id = '".mysql_real_escape_string($_POST['it_id'])."'");
    if($it_id_chk['cnt'] < 1){
        alert('존재하지 않는 상품코드 입니다.');
        exit;
    }
    if($_POST['useyn'] != 'Y'){
        $_POST['useyn'] = 'N';
    }
    $sql = "
        insert into yc4_station_main_item
        (s_id, it_id, it_name, msrp, sort,useyn, create_dt, create_id)
        VALUES (
        '".$_POST['s_id']."',
        '".sql_safe_query($_POST['it_id'])."',
        '".sql_safe_query($_POST['it_name'])."',
        '".sql_safe_query($_POST['msrp'])."',
        '".sql_safe_query($_POST['sort'])."',
        '".sql_safe_query($_POST['useyn'])."',
        '".$g4['time_ymdhis']."',
        '".$member['mb_id']."'
        )
    ";
    if(sql_query($sql)){
        alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/main_station_item.php?s_id='.$_POST['s_id']);
    }else{
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }
    exit;
}

if($_POST['mode'] == 'update'){
    $it_id_chk = sql_fetch("select count(*) as cnt from ".$g4['yc4_item_table']." where it_id = '".mysql_real_escape_string($_POST['it_id'])."'");
    if($it_id_chk['cnt'] < 1){
        alert('존재하지 않는 상품코드 입니다.');
        exit;
    }
    if($_POST['useyn'] != 'Y'){
        $_POST['useyn'] = 'N';
    }
    $sql = "
        update yc4_station_main_item
        set
            s_id = '".sql_safe_query($_POST['s_id'])."',
            it_name = '".sql_safe_query($_POST['it_name'])."',
            msrp = '".sql_safe_query($_POST['msrp'])."',
            sort = '".sql_safe_query($_POST['sort'])."',
            useyn = '".sql_safe_query($_POST['useyn'])."',
            update_dt = '".$g4['time_ymdhis']."',
            update_id = '".$member['mb_id']."'
        where uid = '".sql_safe_query($_POST['uid'])."'

    ";

    if(sql_query($sql)){
        alert('저장이 완료되었습니다.',$g4['shop_admin_path'].'/main_station_item.php?s_id='.$_POST['s_id']);
    }else{
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }
    exit;
}

if($_POST['mode'] == 'delete'){
    $sql = "delete from yc4_station_main_item where uid = '".mysql_real_escape_string($_GET['uid'])."'";
    if(sql_query($sql)){
        alert('삭제가 완료되었습니다.',$g4['shop_admin_path'].'/main_station_item.php');
    }else{
        alert('처리중 오류 발생! 관리자에게 문의하세요');
    }
    exit;
}


$data = sql_fetch("
  select
    a.*,b.it_name as ori_it_name
  from
    yc4_station_main_item a
    left JOIN
    ".$g4['yc4_item_table']." b on a.it_id = b.it_id
  where
    uid = '".mysql_real_escape_string($_GET['uid'])."'
");

# 제품관리스트 로드 #
$s_sql = sql_query("
    select s_id,name from yc4_station where s_id <> 6 order by sort asc
");
$s_option = '';
while($row = sql_fetch_array($s_sql)){
    $selected = false;
    if($data['s_id'] == $row['s_id']){
        $selected = true;
    }
    $s_option .= "<option value='".$row['s_id']."' ".($selected ? "selected":"").">".$row['name']."</option>";
}


if($data){
    $data['ori_it_name'] = get_item_name($data['ori_it_name']);
    $input_hidden = "
        <input type='hidden' name='uid' value='".$data['uid']."'/>
        <input type='hidden' name='mode' value='update'/>
    ";

}else{
    $input_hidden = "
        <input type='hidden' name='mode' value='insert'/>
    ";
}

include_once $g4['admin_path']."/admin.head.php";
?>
    <style>
        .frm input[type=text]{
            width:500px;
        }
    </style>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" class="frm">
    <?php echo $input_hidden;?>
    <table width="100%">
        <col width="150"/>
        <col/>
        <tr>
            <td>제품관</td>
            <td>
                <select name="s_id" required>
                    <?php echo $s_option;?>
                </select>
            </td>
        </tr>
        <tr>
            <td>상품코드</td>
            <td><input type="text" name="it_id" value="<?php echo $data['it_id']?>" required  /></td>
        </tr>
        <tr>
            <td>상품명</td>
            <td class="it_name"><?php echo $data['ori_it_name']?></td>
        </tr>
        <tr>
            <td>출력 상품명</td>
            <td><input type="text" name="it_name" value="<?php echo htmlspecialchars($data['it_name']);?>" required /></td>
        </tr>
        <tr>
            <td>MSRP</td>
            <td><input type="text" name="msrp" value="<?php echo $data['msrp'];?>" required /></td>
        </tr>
        <tr>
            <td>순서</td>
            <td><input type="text" name="sort" value="<?php echo $data['sort'];?>" required /></td>
        </tr>
        <tr>
            <td>진열 여부</td>
            <td><input type="checkbox" name="useyn" value="Y" <?php echo $data['useyn'] == 'Y' ? "checked":"";?>/></td>
        </tr>
    </table>
    <p>
        <input type="submit" value="저장"/>
        <input type="button" value="목록" onclick="location.href='<?php echo $g4['shop_admin_path'];?>/main_station_item.php'"/>
        <input type="button" value="삭제" onclick="location.href='<?php echo $_SERVER['PHP_SELF'];?>?mode=delete&uid=<?php echo $data['uid'];?>'"/>
    </p>
</form>

<script>
$('input[name=it_id]').change(function(){
    $.ajax({
        url : '<?php echo $_SERVER['PHP_SELF'];?>',
        type : 'post',
        data_type : 'json',
        data : {
            'mode' : 'ajax',
            'it_id' : $(this).val()
        },success : function ( result ){
                if(result != ''){
                    var json = $.parseJSON(result);

                    if(json.it_use == 0){
                        alert('판매중인 상품이 아닙니다.');
                        return false;
                    }
                    $('.it_name').text(json.it_name);

                }else{
                    alert('존재하지 않는 상품코드 입니다.');
                    return false;
                }

            }
    });
});
</script>

<?php
include_once $g4['admin_path']."/admin.tail.php";
?>
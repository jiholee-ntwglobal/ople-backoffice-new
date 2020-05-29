<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-04-01
 * Time: 오후 5:46
 */


$sub_menu = "600700";
include "_common.php";
auth_check($auth[$sub_menu], "w");

if($_POST['mode'] == 'ajax'){
    $it = sql_fetch("select it_name from ".$g4['yc4_item_table']." where it_id ='".sql_safe_query($_POST['it_id'])."'");
    if($it) {
        echo json_encode($it);
    }

    exit;
}


if($_GET['uid']){
    $data = sql_fetch("
        select
            a.*,b.it_name as ori_it_name
        from
            yc4_station_main_best_item a,
            ".$g4['yc4_item_table']." b
        where
            a.it_id = b.it_id
            and a.uid = '".sql_safe_query($_GET['uid'])."'
    ");
}


if($data){
    $input_hidden = "
        <input type='hidden' name='mode' value='update'/>
        <input type='hidden' name='uid' value='".$data['uid']."'/>
    ";
}else{
    $input_hidden = "<input type='hidden' name='mode' value='insert'/>";
}

# 제품관 로드 #
$st_sql = sql_query("select s_id,name from yc4_station where s_id<>6 order by sort");
$st_option = '';
while($row = sql_fetch_array($st_sql)){
    $st_option .= "
        <option value='".$row['s_id']."' ".($row['s_id'] == $_GET['s_id'] ? "selected":"").">".$row['name']."</option>
    ";
}


include_once $g4['admin_path']."/admin.head.php";
?>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
        <?php echo $input_hidden;?>
        <table width="100%">
            <tr>
                <td>제품관</td>
                <td>
                    <select name="s_id">
                        <?php echo $st_option;?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>상품코드</td>
                <td><input type="text" name="it_id" value="<?php echo $data['it_id']?>"/></td>
            </tr>
            <tr>
                <td>상품명</td>
                <td class="it_name"><?php echo $data['ori_it_id']?></td>
            </tr>
            <tr>
                <td>출력 상품명</td>
                <td><input type="text" name="it_name" value="<?php echo $data['it_name']?>"/></td>
            </tr>
            <tr>
                <td>사용여부</td>
                <td></td>
            </tr>
        </table>
        <p align="center">
            <input type="submit" value="저장"/>
            <input type="button" value="목록" onclick="location.href='<?php echo $g4['shop_admin_path'];?>/main_station_best_item.php'"/>
            <?php if($data){?>
            <input type="button" value="삭제" onclick="location.href='<?php echo $_SERVER['PHP_SELF'];?>?mode=delete&uid=<?php echo $data['uid'];?>'"/>
            <?php }?>
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
                    if(result == ''){
                        alert('존재하지 않는 상품코드 입니다.');
                        return false;
                    }
                    var json = $.parseJSON(result);
                    $('.it_name').text(json.it_name);
                }
            });
        });
    </script>


<?php
include_once $g4['admin_path']."/admin.tail.php";
?>
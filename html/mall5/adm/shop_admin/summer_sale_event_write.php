<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-06-15
 * Time: 오후 6:38
 */

$sub_menu = "500702";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$st_arr = array(
    '3' => array('name' =>'건강식품관', 'ev_id'=> '1434340585'),
    '4' => array('name' =>'생활용품관', 'ev_id'=> '1434340608'),
    '5' => array('name' =>'출산/육아관', 'ev_id'=> '1434340632'),
    '1' => array('name' =>'미용용품관', 'ev_id'=> '1434340652'),
    '2' => array('name' =>'식품관', 'ev_id'=> '1434340672'),
);

if($_GET['mode'] == 'delete'){
    $data = sql_fetch("
        select
            value1 as it_id,
            value2 as s_id
        from
            yc4_event_data
        where
            uid = '" . mysql_real_escape_string($_GET['uid']) . "'
            AND ev_code = 'summer_big'
            AND ev_data_type = 'item_list'
    ");
    if($data['s_id']){
        $ev_id = $st_arr[$data['s_id']]['ev_id'];
        sql_query("delete from yc4_event_item where ev_id = '".$ev_id."' and it_id = '".$data['it_id']."'");
        sql_query("delete from yc4_event_data where uid = '".$_GET['uid']."'");
        alert('삭제가 완료되었습니다.',$g4['shop_admin_path'].'/summer_sale_event.php');
        exit;
    }else{
        alert('잘못된 접근입니다.');
        exit;
    }
}


if($_POST['mode'] == 'ajax'){
    $_POST['it_id'] = mysql_real_escape_string($_POST['it_id']);

    $mapping = sql_fetch("select upc from ople_mapping where it_id = '".$_POST['it_id']."'");

    $ntics_info = file_get_contents('http://ntics.ntwsec.com/etc/item_info.php?upc='.$mapping['upc']);
    $ntics_info = json_decode($ntics_info,true);

    $result_arr = array();
    $result_arr['upc'] = $mapping['upc'];
    $result_arr['currentqty'] = number_format($ntics_info[$mapping['upc']]['currentqty']);

    $it = sql_fetch("select it_amount from {$g4['yc4_item_table']} where it_id = '".$_POST['it_id']."'");

    $result_arr['it_amount'] = number_format($it['it_amount']);

    echo json_encode($result_arr);

    exit;
}

if($_POST['mode'] == 'insert'){

    $msrp = $_POST['msrp'] ? "'".$_POST['msrp']."'" : 'null';

    $insert_sql = "
        insert into yc4_event_data (ev_code, ev_data_type, value1, value2, value3, value4, value5)
        VALUES ('summer_big','item_list','".$_POST['it_id']."','".$_POST['s_id']."','".(int)$_POST['qty']."','".$_POST['amount']."',".$msrp.")
    ";
    sql_query($insert_sql);

    $insert_sql2 = "
        insert into yc4_event_item (ev_id, it_id)
        VALUES ('".$st_arr[$_POST['s_id']]['ev_id']."','".$_POST['it_id']."')
    ";

    if($_POST['msrp']){
        $msrp_chk = sql_fetch("select count(*) as cnt from yc4_item_etc_amount where it_id = '".$_POST['it_id']."' and pay_code = 3");
        if($msrp_chk['cnt']>0){
            sql_query("update yc4_item_etc_amount set amount = '".$_POST['msrp']."' where it_id = '".$_POST['it_id']."' and pay_code = 3");
        }else{
            sql_query("
                insert into yc4_item_etc_amount (it_id, pay_code, amount, money_type)
                VALUES ('".$_POST['it_id']."',3,'".$_POST['msrp']."','usd')
            ");
        }
    }
    sql_query($insert_sql2);
    alert('저장이 완료되었습니다.',$_SERVER['PHP_SELF']);
    exit;
}

if($_POST['mode'] == 'update'){
    $data = sql_fetch("select * from yc4_event_data  where uid = '" . $_POST['uid']."' AND ev_code = 'summer_big' AND ev_data_type = 'item_list' AND value1 = '".$_POST['it_id']."'");
    $update_sql = "
        update
          yc4_event_data
        set
          value2 = '".$_POST['s_id']."',
          value3 = '".$_POST['qty']."',
          value4 = '".$_POST['amount']."',
          value5 = '".$_POST['msrp']."'
        where
            uid = '".$_POST['uid']."'
            AND ev_code = 'summer_big'
            AND ev_data_type = 'item_list'
            AND value1 = '".$_POST['it_id']."'
    ";
    sql_query($update_sql);

    if($data['s_id'] != $_POST['s_id']){ // 제품관 변경시
        sql_query("
            update yc4_event_item
            set ev_id = '".$st_arr[$_POST['s_id']]['ev_id']."'
            where ev_id = '".$st_arr[$data['s_id']]['ev_id']."' and it_id = '".$_POST['it_id']."'
        ");
    }

    if($_POST['msrp']){
        $msrp_chk = sql_fetch("select count(*) as cnt from yc4_item_etc_amount where it_id = '".$_POST['it_id']."' and pay_code = 3");
        if($msrp_chk['cnt']>0){
            sql_query("update yc4_item_etc_amount set amount = '".$_POST['msrp']."' where it_id = '".$_POST['it_id']."' and pay_code = 3");
        }else{
            sql_query("
                insert into yc4_item_etc_amount (it_id, pay_code, amount, money_type)
                VALUES ('".$_POST['it_id']."',3,'".$_POST['msrp']."','usd')
            ");
        }
    }
    alert('수정이 완료되었습니다.',$_SERVER['PHP_SELF'].'?uid='.$_POST['uid']);
    exit;
}

if($_GET['uid']){
    $data = sql_fetch("
        select
            uid,
            value1 as it_id,
            value2 as s_id,
            value3 as qty,
            value4 as amount,
            value5 as msrp,
            value6 as en_dt,
            value7 as order_qty
        from
            yc4_event_data
        where
            uid = '" . mysql_real_escape_string($_GET['uid']) . "'
            AND ev_code = 'summer_big'
            AND ev_data_type = 'item_list'
    ");

}

if($data['uid']){
    $input_hidden = "
        <input type='hidden' name='uid' value='".$data['uid']."'/>
        <input type='hidden' name='it_id' value='".$data['it_id']."'/>
        <input type='hidden' name='mode' value='update'/>
    ";
}else{
    $input_hidden = "
        <input type='hidden' name='mode' value='insert'/>
    ";
}


if($data['it_id']){
    $it_id_input = $data['it_id'];
}else{
    $it_id_input = "<input type='text' name='it_id'/>";
}

$st_option = "";
foreach ($st_arr as $s_id => $val) {
    $st_option .= "<option value='".$s_id."' ".($data['s_id'] == $s_id ? "selected":"").">".$val['name']."</option>".PHP_EOL;
}


$g4['title'] = "썸머 빅 세일 이벤트";
include_once $g4['admin_path']."/admin.head.php";
?>
<form action="<?php echo $_SERVER['PHP_SELF']?>" onchange="frm_change(this);" method="post" name="frm" onsubmit="return frm_chk(this)">
    <?php echo $input_hidden;?>
    <table width="100%">
        <tr>
            <td>제품관</td>
            <td><select name="s_id"><?php echo $st_option;?></select></td>
        </tr>
        <tr>
            <td>오플 상품코드</td>
            <td><?php echo $it_id_input;?></td>
        </tr>
        <tr>
            <td>상품정보</td>
            <td>
                NTICS_QTY : <span class="ntics_qty">0</span> <br/>
                정상판매가 : <span class="it_amount">0</span>
            </td>
        </tr>
        <tr>
            <td>이벤트 수량</td>
            <td><input type="text" name="qty" value="<?php echo $data['qty']?>"/></td>
        </tr>
        <tr>
            <td>이벤트 가격(￦)</td>
            <td><input type="text" name="amount" value="<?php echo $data['amount']?>"/></td>
        </tr>
        <tr>
            <td>MSRP($)</td>
            <td><input type="text" name="msrp" value="<?php echo $data['msrp']?>"/></td>
        </tr>
        <?php if($data['en_dt']){?>
        <tr>
            <td>종료일</td>
            <td><?php echo $data['en_dt'];?></td>
        </tr>
        <?php }?>
        <?php if($data['order_qty']){?>
            <tr>
                <td>판매수량</td>
                <td><?php echo number_format($data['order_qty']);?></td>
            </tr>
        <?php }?>
    </table>
    <p align="center">
        <input type="submit" value="저장"/>
        <input type="button" value="목록" onclick="location.href='<?php echo $g4['shop_admin_path'];?>/summer_sale_event.php'"/>
    </p>
</form>

<script>
    $(function(){
        frm_change(frm);
    });
    function frm_change(f){
        if(f.it_id.value != ''){
            $.ajax({
                url : '<?php echo $_SERVER['PHP_SELF']?>',
                type : 'post',
                datatype : 'json',
                data : {
                    'it_id' : f.it_id.value,
                    'mode' : 'ajax'
                },success : function(result){
                    var json = $.parseJSON(result);
                    $('.ntics_qty').text(json.currentqty);
                    $('.it_amount').text(json.it_amount);
                }

            });
        }
    }
    function frm_chk(f){
        if(f.it_id.value == ''){
            alert('상품 코드를 입력해 주세요');
            return false;
        }

        if(f.qty.value == ''){
            alert('이벤트 수량을 입력해 주세요');
            return false;
        }

        if(f.amount.value == ''){
            alert('이벤트 가격을 입력해 주세요.');
            return false;
        }
        return true;
    }
</script>

<?php
include_once $g4['admin_path']."/admin.tail.php";

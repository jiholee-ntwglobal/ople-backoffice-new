<?php
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2016-12-01
 * Time: 오후 5:01
 */
$sub_menu = "300170";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");
$success = 0;
$total = 0;
$fail_od_id = '';
$message='';
$escape=array("'",'"','\\');
if (count($_POST['od'])>0) {
    $it_id = isset($_POST['payment_it_id_hidden'])? str_replace($escape,'',trim($_POST['payment_it_id_hidden'])):'';
    $memo = isset($_POST['payment_meno'])? str_replace($escape,'',trim($_POST['payment_meno'])):'';
    $qty = isset($_POST['payment_qty'])?str_replace($escape,'',trim($_POST['payment_qty'])):'';

    if (!$it_id) {
        $message = '사은품 코드가 잘못 되었습니다';
        alert($message,'');
    }
    if (!$memo) {
        $message = '메모가 잘못 되었습니다';
        alert($message,'');
    }
    if (!$qty||!is_numeric($qty)||$qty<=0) {
        $message = '수량이 잘못 되었습니다';
        alert($message,'');
    }

    $arr_od_id = $_POST['od'];
    foreach ($arr_od_id as $key => $value) {

        $od_id = str_replace($escape,'',trim($value));
        $total++;

        if (!$od_id) {
            $fail_od_id .=$value." ";
            continue;
        }
        $yc4_order_data = sql_fetch("select on_uid,mb_id from yc4_order where od_id = '{$value}'");
        $on_uid = trim($yc4_order_data['on_uid']);
        $mb_id = trim($yc4_order_data['mb_id']);
        if (!$on_uid) {
            $fail_od_id .=$value." ";
            continue;
        }
        $yc4_item_data = sql_fetch("select count(*) cnt from yc4_item where it_id = '{$it_id}'");
        if ($yc4_item_data['cnt'] < 1) {
            $fail_od_id .=$value." ";
            continue;
        }
        /*echo "
               INSERT INTO yc4_cart(on_uid,
                     it_id,
                     ct_status,
                     ct_mb_id,
                     ct_time,
                     ct_ip,
                     ct_history,
                     ct_qty,
                     ct_amount,
                     ct_point,
                     ct_point_use,
                     ct_stock_use,
                     it_opt1,
                     it_opt2,
                     it_opt3,
                     it_opt4,
                     it_opt5,
                     it_opt6,
                     ct_send_cost,
                     ct_ship_os_pid,
                     ct_ship_ct_qty,
                     ct_ship_stock_use,
                     ct_amount_usd)
            VALUES ('" . $on_uid . "',
                     '" . $it_id . "',
                     '준비',
                      '".$mb_id."',
                     now(),
                     '" . $_SERVER['REMOTE_ADDR'] . "',
                     '" . $memo . "',
                     " . $qty . ",
                     0,
                     0,
                     0,
                     0,
                     '',
                     '',
                     '',
                     '',
                     '',
                     '',
                     '',
                     '',
                     '',
                     '',
                     0)
               ";*/
        sql_query("
               INSERT INTO
                    yc4_cart
                    (
                            on_uid, it_id, ct_status, ct_mb_id, ct_time,
                            ct_ip, ct_history, ct_qty, ct_amount, ct_point,
                            ct_point_use, ct_stock_use, it_opt1, it_opt2, it_opt3,
                            it_opt4, it_opt5, it_opt6, ct_send_cost, ct_ship_os_pid,
                            ct_ship_ct_qty, ct_ship_stock_use, ct_amount_usd
                    )
                VALUES
                    (
                            '" . $on_uid . "', '" . $it_id . "', '준비', '".$mb_id."', now(),
                            '" . $_SERVER['REMOTE_ADDR'] . "', '" . $memo . "', " . $qty . ", 0, 0,
                            0, 0, '', '', '',
                            '', '', '', '', '',
                            '', '', 0
                    )
               ");

        sql_query("
        update
            yc4_order
        set
            od_shop_memo = concat(od_shop_memo,'\\n','" . $memo . "') , od_status_update_dt = now()
        where
            on_uid = '" . $on_uid . "'
    ");
       /* echo "
        update yc4_order set
        od_shop_memo = concat(od_shop_memo,'\\n','" . $memo . "') , od_status_update_dt = now()
        where on_uid = '" . $on_uid . "'
    ";*/
        $success++;
    }
    $fail = $total-$success;
    $message = "총 {$total}건 성공 {$success}건 실패 {$fail}건 $fail_od_id ";
    alert($message,'./payment_insert.php');
}else{
    $message = '주문번호가 올바르지 않습니다. ';
    alert($message,'');
}


?>

<?php
/*$tr ='';
foreach ($arr_od_id as $key => $value) {
    $od_id = trim($value);
    $results = sql_fetch("select on_uid,od_name,od_status_update_dt from yc4_order where od_id = '{$value}'");
    if($results['on_uid']){
        $it_id=trim($_POST['it_id'][$key]);
        $memo=trim($_POST['memo'][$key]);
        $it_name=trim($_POST['it_name'][$key]);
        $result = sql_fetch("select ct_history,ct_status,ct_qty from yc4_cart where it_id = '{$it_id}' and ct_history = {$memo}");
        $result['it_id']=$result['ct_qty'] >0 && $result['ct_history'] ?  $it_id : '';
        $result['it_name']=$result['ct_qty'] >0 && $result['ct_history'] ?  $it_name : '';
        $tr .= "
            <tr>
                <td>{$value}</td>
                <td>{$results['od_name']}</td>
                <td>
                    <table>
                        <thead>
                        <tr>
                            <th>상품오플코드</th>
                            <th>상품명</th>
                            <th>수량</th>
                            <th>메모</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{$result['it_id']}</td>
                            <td>{$result['it_name']}</td>
                            <td>{$result['ct_qty']}</td>
                            <td>{$result['ct_history']}</td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td>{$results['od_status_update_dt']}</td>
            </tr>
        ";
    }
}*/

//ar_dump($yc4_order_arr);
/*define('bootstrap', true);
$g4[title] = "주문서관리";
include_once("$g4[admin_path]/admin.head.php");*/
?>

<!--<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
<!--<table class="table">
    <thead>
    <tr>
        <th>주문번호</th>
        <th>이름</th>
        <th>상품정보</th>
        <th>업데이트날짜</th>
    </tr>
    </thead>
    <tbody>
        <?php /*echo $tr;*/?>
    </tbody>
</table>-->

<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-11-04
 * Time: 오후 4:26
 */

define('bootstrap', true);
$sub_menu = "500703";
include "_common.php";
auth_check($auth[$sub_menu], "r");


$sql = sql_query("SELECT
b.od_id,b.od_b_name,b.od_b_hp,b.od_card_time,b.od_receipt_card_usd,b.mb_id
FROM
yc4_event_data a
left join
yc4_order b on a.value2 = b.od_id
WHERE
a.ev_code='master_10'
AND a.ev_data_type='od_id'
order by b.od_card_time desc
");

$list_tr = '';
while($row = sql_fetch_array($sql)){
    $list_tr .= "
        <tr>
            <td><a href='orderform.php?od_id=".$row['od_id']."' target='_blank'>".$row['od_id']."</a></td>
            <td><a href='orderlist.php?sel_field=mb_id&search=".$row['mb_id']."' target='_blank'>".$row['mb_id']."</a></td>
            <td>".$row['od_b_name']."</td>
            <td>".$row['od_b_hp']."</td>
            <td>".$row['od_card_time']."</td>
            <td>$ ".$row['od_receipt_card_usd']."</td>

        </tr>
    ";
}

$g4['title'] = '마스터카드 블랙프라이데이 이벤트 대상자';
include_once $g4['admin_path']."/admin.head.php";
?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<table class="table">
    <thead>
    <tr>
        <th>주문번호</th>
        <th>아이디</th>
        <th>이름</th>
        <th>휴대폰번호</th>
        <th>주문시간</th>
        <th>주문금액</th>
    </tr>
    </thead>
    <tbody>
    <?php echo $list_tr;?>
    </tbody>


</table>

<?php
include_once $g4['admin_path']."/admin.tail.php";

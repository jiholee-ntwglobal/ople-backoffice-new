<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-06-01
 * Time: 오후 3:42
 */
$sub_menu = "500701";
include "_common.php";
auth_check($auth[$sub_menu], "r");


$sql = sql_query("
    select
        a.it_id,
        b.it_name,
        a.qty,
        a.sell_qty,
        a.qty - a.sell_qty as result_qty,
        c.upc
    from
        yc4_item_sample a
    left join
        {$g4['yc4_item_table']} b on a.it_id = b.it_id
    left JOIN  ople_mapping c on a.it_id = c.it_id

");
$list_tr = '';
$it_id_in = '';
$data = array();
while ($row = sql_fetch_array($sql)) {
    $it_id_in .= ($it_id_in ? ",":"")."'".$row['it_id']."'";
    $data[] = $row;
}
$sql = sql_query("select distinct upc from ople_mapping where it_id in (".$it_id_in.")");
$upc_in = '';
while($row = sql_fetch_array($sql)){
    $upc_in .= ($upc_in ? "||":"").$row['upc'];
}
$NTICS_INFO = json_decode(file_get_contents('http://ntics.ntwsec.com/etc/item_info.php?upc='.$upc_in),true);



foreach($data as $row) {
    $list_tr .= "
        <tr>
            <td>" . $row['it_id'] . "</td>
            <td>" . get_it_image($row['it_id'] . '_s', 80, 80) . "</td>
            <td>" . $row['it_name'] . "</td>
            <td align='right' style='padding-right:5px;'>" . number_format($row['qty']) . "</td>
            <td align='right' style='padding-right:5px;'>" . number_format($row['sell_qty']) . "</td>
            <td align='right' style='padding-right:5px;'>" . number_format($row['result_qty']) . "</td>
            <td align='right' style='padding-right:5px;'>". number_format($NTICS_INFO[$row['upc']]['currentqty']) ."</td>
        </tr>
    ";
}

include $g4['admin_path'] . '/admin.head.php';

?>

    <table width="100%" style="border-collapse: collapse" border="1">
        <col width="70"/>
        <col width="85"/>
        <col/>
        <col width="65"/>
        <col width="50"/>
        <col width="50"/>
        <tr align="center">
            <td>상품코드</td>
            <td></td>
            <td>상품명</td>
            <td>이벤트수량</td>
            <td>판매수량</td>
            <td>잔여수량</td>
            <td>NTICS 재고량</td>
        </tr>
        <?php echo $list_tr; ?>
    </table>

<?php
include $g4['admin_path'] . '/admin.tail.php';
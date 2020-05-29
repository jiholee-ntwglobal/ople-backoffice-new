<?php
/**
 * Created by PhpStorm.
 * File name : discontinued_sales_item.php.
 * Comment :
 * Date: 2016-01-29
 * User: Minki Hong
 */
$sub_menu = "300140";
include_once "./_common.php";
include_once $g4['full_path'] . '/lib/db.php';
$db = new db;

$ntics_data = $db->ntics_db->query("
    select
        rtrim(a.upc) as upc,
        b.mfgname,
        concat(
          rtrim(a.item_name),
          case when rtrim(isnull(a.potency,'')) != '' then concat(' ',rtrim(a.potency)) end,
          case when rtrim(isnull(a.potency_unit,'')) != '' then concat(' ',rtrim(a.potency_unit)) end,
          case when rtrim(isnull(a.count,'')) != '' then concat(' ',rtrim(a.count)) end,
          case when rtrim(isnull(a.type,'')) != '' then concat(' ',rtrim(a.type)) end
        ) as item_name,
        a.location,
        a.currentqty
        from
        N_MASTER_ITEM a
        left join
        N_MFG b on a.MfgCD = b.mfgcd
        where
        a.location like '%dis%'
        and a.currentqty > 0
")->fetchAll(PDO::FETCH_ASSOC);

$upc_arr = array();
$ntics_arr = array();
$ntics_qty_sql = '';
foreach ($ntics_data as $row) {
    if(!in_array($row['upc'],$upc_arr)){
        $upc_arr[] = $row['upc'];
        $ntics_arr[$row['upc']] = $row;
        $ntics_qty_sql .= "when a.upc = '{$row['upc']}' then '{$row['currentqty']}' ";
    }
}
$order_by = '';
if($ntics_qty_sql){
    $ntics_qty_sql = ", case {$ntics_qty_sql} end as ntics_qty";
    $order_by = ' order by cast(ntics_qty as int) desc';
}
$item_data = array();
if(count($upc_arr) > 0 ){
    $ople_data_stmt = $db->ople_db_pdo->prepare("
        select
        a.upc,a.qty,b.it_id,b.it_maker,b.it_name,b.it_stock_qty
        {$ntics_qty_sql}
        from
        ople_mapping a
        left join
        yc4_item b on a.it_id = b.it_id
        where
        a.upc in (".implode(',',array_fill(0,count($upc_arr),'?')).")
        and b.it_use = 1 and b.it_discontinued = 0
        and b.it_id is not null
        and b.it_stock_qty > 0
        {$order_by}
    ");
    /*echo '<pre>';
    echo $ople_data_stmt->queryString;
    echo '</pre>';*/
    $ople_data_stmt->execute($upc_arr);

    $ople_data = $ople_data_stmt->fetchAll(PDO::FETCH_ASSOC);

    $item_data = array();
    foreach ($ople_data as $row) {
        if(!isset($item_data[$row['upc']])){
            $item_data[$row['upc']] = array();
        }
        $item_data[$row['upc']][] = $row;
    }
}
$g4['title'] = '단종 판매중 상품관리';
define('bootstrap', true);
include_once $g4['full_path'].'/adm/admin.head.php';
?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<table class="table">
    <thead>
    <tr>
        <th>UPC</th>
        <th>Mfg name</th>
        <th>Item name</th>
        <th>Location</th>
        <th>NTICS QTY</th>
        <th>오플 상품코드</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($item_data as $upc => $row) { ?>
        <tr>
            <td><?php echo $upc;?></td>
            <td><?php echo $ntics_arr[$upc]['mfgname'];?></td>
            <td><?php echo $ntics_arr[$upc]['item_name'];?></td>
            <td><?php echo $ntics_arr[$upc]['location'];?></td>
            <td><?php echo number_format($ntics_arr[$upc]['currentqty']);?></td>
            <td>
                <?php foreach ($row as $ople_data) { ?>
                    <a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $ople_data['it_id'];?>" target="_blank"><?php echo $ople_data['it_id']; ?></a>
                <? }?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<?php
include_once $g4['full_path'].'/adm/admin.tail.php';
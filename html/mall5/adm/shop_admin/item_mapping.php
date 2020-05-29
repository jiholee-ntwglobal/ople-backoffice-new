<?php
/**
 * Created by PhpStorm.
 * File name : item_mapping.php.
 * Comment :
 * Date: 2015-12-18
 * User: Minki Hong
 */
//phpinfo(); exit;
$sub_menu = "300110";
include_once "_common.php";
include_once $g4['full_path'] . '/lib/ople_mapping.php';
auth_check($auth[$sub_menu], "r");

$ople_mapping = new ople_mapping();


$where = $order_by = '';

if ($_GET['it_id_str']) {
    $it_id_str = explode(PHP_EOL, $_GET['it_id_str']);
    foreach ($it_id_str as $k => $v) {
        $it_id_str[$k] = trim($v);
    }

    $where .= " and a.it_id in ('" . implode("','", $it_id_str) . "')";


}


if ($_GET['fg'] == 'NFO') {
    $where .= " and ifnull(b.nfo,'N') = 'N'";
    $order_by .= ($order_by ? ',' : '') . "case when ifnull(b.nfo,'N') = 'N' then 0 else 1 end asc";
} elseif ($_GET['fg'] == 'SHIPPING') {
    $where .= " and ifnull(b.shipping,'N') = 'N'";
    $order_by .= ($order_by ? ',' : '') . "case when ifnull(b.shipping,'N') = 'N' then 0 else 1 end asc";
}
$order_by .= ($order_by ? ',' : '') . "case when b.nfo='Y' then 1 else 0 end + case when b.shipping = 'Y' then 1 else 0 end asc";


/*$cnt = sql_fetch("
select
        count( DISTINCT a.it_id ) as cnt
    from
        yc4_item a
        left join
        ople_mapping b on a.it_id = b.it_id
        left join
        ople_mapping_shipping c on a.it_id = c.it_id
    where
        a.it_use = '1'
        and a.it_discontinued = '0'
        {$where}
    group by a.it_id

");*/
$cnt = sql_fetch($a="
    select
        count( DISTINCT a.it_id ) as cnt
    from
        yc4_item a
        left join
        ople_mapping b on a.it_id = b.it_id
    where
        a.it_use = '1'
        and a.it_discontinued = '0'
        {$where}

");
$cnt = $cnt['cnt'];
/*echo '<pre>';
echo $a;
echo '</pre>';*/
$rows = 20;

if ((int)$_GET['rows']) {
    $rows = (int)$_GET['rows'];
}
$page = 1;
if ((int)$_GET['page']) {
    $page = (int)$_GET['page'];
}
$from = ($page - 1) * $rows;


$total_page = ceil($cnt / $rows);
$url = $_SERVER['PHP_SELF'];
$page_qstr = $_GET;
unset($page_qstr['page']);
$page_qstr = http_build_query($page_qstr);
$url .= '?' . $page_qstr . '&page=';
$page_btn = get_paging($rows, $page, $total_page, $url);

# 상품 리스트 로드 #
/*$sql = sql_query("
    select
        a.it_id,a.it_maker,a.it_name,a.it_amount_usd,a.it_stock_qty
    from
        yc4_item a
        left join
        ople_mapping b on a.it_id = b.it_id
        left join
        ople_mapping_shipping c on a.it_id = c.it_id
    where
        a.it_use = '1'
        and a.it_discontinued = '0'
        {$where}
    group by a.it_id
    order by
        {$order_by} ,a.it_id desc
    limit {$from}, {$rows}
");*/

$sql = sql_query("
    select
        a.it_id,a.it_maker,a.it_name,a.it_amount_usd,a.it_stock_qty
    from
        yc4_item a
        left join
        ople_mapping b on a.it_id = b.it_id
    where
        a.it_use = '1'
        {$where}
    group by a.it_id,a.it_maker,a.it_name,a.it_amount_usd,a.it_stock_qty
    order by
        {$order_by} , a.it_id desc
    limit {$from}, {$rows}
");

$it_arr = array();
while ($row = sql_fetch_array($sql)) {
    $it_arr[] = $row;
}

unset($row);

foreach ($it_arr as $key => $row) {
    $row['nfo_mapping_fg'] = $it_arr[$key]['shipping_mapping_fg'] = false;
    if ($ople_mapping->nfo_mapping_chk($row['it_id'])) {
        $it_arr[$key]['nfo_mapping_fg'] = true;
        $it_arr[$key]['nfo_mapping'] = $ople_mapping->get_ople_mapping_data($row['it_id']);
    }
    if ($ople_mapping->shipping_mapping_chk($row['it_id'])) {
        $it_arr[$key]['shipping_mapping_fg'] = true;
        $it_arr[$key]['shipping_mapping'] = $ople_mapping->get_shipping_mapping_data2($row['it_id']);
        $shipping_mapping_arr = array();

    }
}


$fg_qstr = $_GET;
unset($fg_qstr['page'], $fg_qstr['fg']);
$fg_qstr = http_build_query($fg_qstr);


$g4['title'] = '상품 매핑';
define('bootstrap', true);
include_once $g4['full_path'] . '/adm/admin.head.php';

?>
    <style>
        a.btn {
            color: #ffffff;
        }
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <div class="panel panel-default">
        <div class="panel-heading">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
                <label>
                    오플 상품코드
                    <textarea name="it_id_str" class="form-control" rows="10"><?php echo $_GET['it_id_str']; ?></textarea>
                </label>
                <button class="btn btn-primary" type="submit">검색</button>
            </form>
            <ul class="nav nav-pills">
                <li role="presentation" <?php echo !$_GET['fg'] ? 'class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $fg_qstr; ?>">전체</a></li>
                <li role="presentation" <?php echo $_GET['fg'] == 'NFO' ? 'class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $fg_qstr; ?>&fg=NFO">NFO 매핑 안된 상품</a></li>
                <li role="presentation" <?php echo $_GET['fg'] == 'SHIPPING' ? 'class="active"' : ''; ?>><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $fg_qstr; ?>&fg=SHIPPING">배송 매핑 안된 상품</a></li>
            </ul>
        </div>

        <div class="panel-body">
            <table class="table">
                <col width="100">
                <col width="83">
                <col width="280">
                <col width="160">
                <col width="160">
                <col>

                <thead>
                <tr>
                    <th></th>
                    <th>IT_ID</th>
                    <th>상품명</th>
                    <th>NFO매핑</th>
                    <th>배송매핑</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($it_arr as $row) : ?>
                    <tr>
                        <td><?php echo get_it_image($row['it_id'] . '_s', 80, 80, null, null, false, false) ?></td>
                        <td><a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id'] ?>" target="_blank"><?php echo $row['it_id']; ?></a></td>
                        <td><?php
                            echo get_item_name($row['it_name'], 'list');
                            if ($row['it_stock_qty'] < 1) { ?>
                                <img src="<?php echo $g4['shop_path']; ?>/img/icon_pumjul.gif" alt="">
                            <?php }
                            ?></td>
                        <td>
                            <?php if ($row['nfo_mapping_fg']) : ?>
                                <ul class="list-group">
                                    <?php foreach ($row['nfo_mapping'] as $nfo_row) : ?>
                                        <li class="list-group-item">
                                            <span class="badge"><?php echo $nfo_row['qty']; ?></span>
                                            <?php echo trim($nfo_row['upc']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['shipping_mapping_fg']) : ?>
                                <ul class="list-group">
                                    <?php foreach ($row['shipping_mapping'] as $shipping_row) : ?>
                                        <li class="list-group-item">
                                            <span class="badge"><?php echo $shipping_row['qty']; ?></span>
                                            <?php echo trim($shipping_row['upc']); ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                        <td><a href="<?php echo $g4['shop_admin_path']; ?>/item_mapping_edit.php?it_id=<?php echo $row['it_id']; ?>" class="btn btn-primary">수정</a></td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
        <div class="panel-footer text-center">
            <?php echo $page_btn; ?>
        </div>
    </div>
<?php
include_once $g4['full_path'] . '/adm/admin.tail.php';
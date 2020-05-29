<?php
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2016-12-13
 * Time: 오전 10:38
 */
$sub_menu = "300999";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");
$escape=array("'",'"','\\');

$where = '';
if (trim($sh_it_id)) {
    $it_id_str = explode(PHP_EOL, $_GET['sh_it_id']);
    foreach ($it_id_str as $k => $v) {
        $it_id_str[$k] = str_replace($escape,'',trim($v));
    }

    $where .=  ($where ? ' and ' :' where ')."  a.it_id in ('" . implode("','", $it_id_str) . "')";


}
if (trim($sh_upc)) {
    $upc_str = explode(PHP_EOL, $sh_upc);
    foreach ($upc_str as $k => $v) {
        $upc_str[$k] = str_replace($escape,'',trim($v));
    }

    $where .=  ($where ? ' and ' :' where ')."  c.upc in ('" . implode("','", $upc_str) . "')";


}
if(trim($sh_item_name)){
    $name =str_replace($escape,'',trim($sh_item_name));

    $where .=  ($where ? ' and ' :' where ')."  b.it_name like '%{$name}%'";
}

//페이징
$cnt = sql_fetch($a="
    select
      count( distinct a.it_id) as cnt
    from
        set_item a
        inner join yc4_item b
            on a.it_id = b.it_id
        left join ople_mapping c
            on b.it_id = c.it_id
    {$where}
");
$cnt = $cnt['cnt'];
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


//Select
$sql = sql_query("
    select
    a.it_id , b.it_name , b.it_amount, b.it_amount_usd, b.it_health_cnt, b.it_stock_qty, b.list_clearance, b.it_origin , b.it_order_onetime_limit_cnt
    from set_item a
          inner join yc4_item b
                on a.it_id = b.it_id
          left join ople_mapping c
                on b.it_id = c.it_id
    {$where}
    group by a.it_id , b.it_name , b.it_amount, b.it_amount_usd, b.it_health_cnt, b.it_stock_qty, b.list_clearance, b.it_origin , b.it_order_onetime_limit_cnt
    limit {$from}, {$rows}
");

$it_arr = array();
while ($row = sql_fetch_array($sql)) {
    $it_arr[] = $row;
}
unset($row);

foreach ($it_arr as $key => $row) {
    $row['upc_mapping_fg'] = $it_arr[$key]['upc_mapping_fg'] = false;

    if (upc_mapping_chk($row['it_id'])) {
        $it_arr[$key]['upc_mapping_fg'] = true;
        $it_arr[$key]['upc_mapping'] = get_ople_mapping_data($row['it_id']);
    }

}

function get_ople_mapping_data($it_id){
    $stmt = sql_query("select upc,qty from ople_mapping where it_id = '{$it_id}'");
    while ($row = sql_fetch_array($stmt)) {
        $it_arr[] = $row;
    }
    return $it_arr;

}
function upc_mapping_chk($it_id){
    $chk = sql_fetch("select count(*) as cnt from ople_mapping where it_id = '{$it_id}' ");

    $res = $chk['cnt'];
    if($res < 1){
        return false;
    }
    return true;
}



$fg_qstr = $_GET;
unset($fg_qstr['page'], $fg_qstr['fg']);
$fg_qstr = http_build_query($fg_qstr);

$g4[title] = "세트상품 생성";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form class="panel-heading">
    <div class="row col-lg-12">
        <div class="col-lg-4">
            <label>IT_ID(엔터로 구분)</label>
            <textarea name="sh_it_id" cols="30" rows="5" class="form-control"><?php echo trim($sh_it_id); ?></textarea>
        </div>
        <div class="col-lg-4">
            <label>UPC(엔터로 구분)</label>
            <textarea name="sh_upc" cols="30" rows="5" class="form-control"><?php echo trim($sh_upc); ?></textarea>
        </div>
        <div class="col-lg-4">
            <div>
                <label>ITEM NAME</label>
                <input type="text" class="form-control" name="sh_item_name" value="<?php echo trim($sh_item_name); ?>">
            </div>
            <br>
            <br>
            <button type="submit" class="btn btn-primary btn-block">Search</button>
            <div class="clearfix"></div>
        </div>
    </div>
</form>
<table class="table table-hover table-condensed row col-lg-12">
    <thead>
    <tr>
        <th colspan="2">상품코드</th>
        <th class="text-center">상품명</th>
        <th>판매가</th>
        <th>기타</th>

        <th><button class="btn btn-success" type="button" onclick="location.href='./set_item_insert.php'">추가</button></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($it_arr as $row){ ?>
    <tr>
        <td><?php echo get_it_image($row['it_id'] . '_s', 80, 80, null, null, false, false) ?></td>
        <td>
            <a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id'] ?>" target="_blank"><?php echo $row['it_id']; ?></a>
            <?php
            if ($row['list_clearance']) { ?>
            <img src="jieun.jpg" alt="">
            <?php }
            ?>
        </td>
        <td><?php
            echo get_item_name($row['it_name'], 'list');
            if ($row['it_stock_qty'] < 1) { ?>
                <img src="<?php echo $g4['shop_path']; ?>/img/icon_pumjul.gif" alt="">
            <?php }?>
            <br>
            <?php if($row['upc_mapping_fg']){?>
                <?php foreach ($row['upc_mapping'] as $upc_row){ ?>
                   <span style="color:  green">
                    <strong>
                    <?php echo "UPC: ".$upc_row['upc']." 수량 : " ;?>
                    <?php echo $upc_row['qty'] ;?>
                    </strong>
                </span>
                    <?php } ?>

            <?php } ?>
        </td>


        <td width="80" class="text-right">$ <?php echo $row['it_amount_usd']; ?><br>(\ <?php echo $row['it_amount']; ?>)</td>
        <td>
            건기식 <?php echo $row['it_health_cnt'];?> 병<br>

            <?php echo $row['it_origin']?'원산지 '.$row['it_origin']:''?>
        </td>
        <td ><button onclick="location.href='<?php echo $g4['shop_admin_path']; ?>/itemform.php?w=u&it_id=<?php echo $row['it_id']; ?>'" class="btn btn-primary">수정</button>
            <br>
            <button type="button" onclick="location.href='<?php echo $g4['shop_admin_path']; ?>/item_mapping_edit.php?it_id=<?php echo $row['it_id']; ?>'"  class="btn btn-danger type">맵핑</button>
        </td>
    </tr>
    <tr class="<?php echo $row['it_id'];?>" style="display: none">
        <td colspan="6" class="s<?php echo $row['it_id'];?>"></td>
    </tr>
    </tbody>
    <? } ?>
    <tfoot>
    <tr>
        <td colspan="8" class="text-center"><?php echo $page_btn; ?></td>
    </tr>
    </tfoot>
</table>
<script>
    /*function ajax_data(id){
        var it_id =id;
        if ($('.' + id + ':visible').length > 0) {
            $('.' + id).hide();
        } else {
            $.ajax({
                type: 'get'
                , url: 'set_item_ajax.php'
                , data: 'it_id=' + it_id
                , dataType : 'html'
                , success: function (data) {
                    if (data != false) {
                        $('.' + id).show();
                        $('.s' + id).html(data);

                    } else {
                        $('.' + id).hide();
                    }
                }
            });
        }
    }*/
</script>
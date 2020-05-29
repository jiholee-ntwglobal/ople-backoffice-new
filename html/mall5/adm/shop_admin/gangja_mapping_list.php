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
//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";
$where = '';
$gangja_it_id = trim($_GET['gangja_it_id'])? sql_safe_query($_GET['gangja_it_id']) : '' ;
$it_id = trim($_GET['it_id'])? sql_safe_query($_GET['it_id']) : '' ;
if ($gangja_it_id) {

    $where .= ($where == '' ? ' where ' : ' and ') . " a.gangja_it_id = '{$gangja_it_id}' ";
}
if ($it_id) {

    $where .= ($where == '' ? ' where ' : ' and ') . " a.it_id = '{$it_id}' ";
}

//페이징
function paging($total_list, $now_page, $list_page, $num_page, $searchdata)
{
    $total_page = ceil($total_list / $list_page); // 전체 페이지
    $total_block = ceil($total_page / $num_page); // 전체 블록
    $now_block = ceil($now_page / $num_page); //현재 블록
    $start_page = (((int)(($now_page - 1) / $num_page)) * $num_page) + 1; //시작페이지
    $end_page = $start_page + $num_page - 1; //끝페이지
    $next_page = (((int)(($now_page - 1 + $num_page) / $num_page)) * $num_page) + 1; //다음페이지
    $prev_page = (((int)(($now_page - 1 - $num_page) / $num_page)) * $num_page) + $num_page; //이전페이지
    $server = $_SERVER['PHP_SELF'];
    $pages = "";
    unset($searchdata['page']);
    $search = "";
    $search = http_build_query($searchdata);
    if ($total_page == 0 || $now_page > $total_page) {
        $pages .= "검색한 결과는 없습니다.";
    } else {
        if ($now_page > 1) {
            $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=1" . "&" . $search . "> 처음 </a>";
        }
        if ($now_block > 1) {
            $pages .= "<a class=\"btn btn-primary\" z href=" . $server . "?page=" . $prev_page . "&" . $search . "> 이전 </a>";
        }
        if ($end_page >= $total_page) {
            $end_page = $total_page;
        }
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $now_page) {
                $pages .= "<b class=\"btn btn-info\" disabled=\"disabled\"> " . $i . " </b>";
            } else {
                $pages .= "<a class=\"btn btn-info\" href=" . $server . "?page=" . $i . "&" . $search . "> " . $i . " </a>";
            }
        }
        if ($now_page >= 1) {
            if ($total_block != $now_block) {
                $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=" . $next_page . "&" . $search . "> 다음 </a>";
            }
            if ($now_page != $total_page) {
                $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=" . $total_page . "&" . $search . "> 마지막 </a>";
            }
        }
    }
    return $pages;
}

$row_cnt = sql_fetch("
SELECT COUNT(*) cnt FROM gangja_mapping a
$where
");
$total_count = $row_cnt['cnt'];
$total_list = $total_count;//페이징=전체컬럼수
$list_page = 30;//보여줄 컬럼
$num_page = 10;//표시할 페이지


$pr_item_sql = sql_query("
    SELECT a.uid,a.gangja_it_id, a.it_id, b.it_name
FROM gangja_mapping a INNER JOIN yc4_item b ON a.it_id = b.it_id
$where
order by create_dt desc
        limit " . ($now_page - 1) * $list_page . "," . $list_page
);
$pr_item_list = array();
while ($row = sql_fetch_array($pr_item_sql)) {
    $pr_item_list[] = $row;
}

$g4[title] = "강자닷컴 상품 매핑";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<h4>강자 닷컴 매핑</h4>
<br>
<form class="form-inline">
<div class="form-group">
    <input class="form-control" type="text" placeholder="강자닷컴 상품코드" name="gangja_it_id" size="40" value="<?php echo $gangja_it_id;?>">
    <input class="form-control" type="text" placeholder="오플 상품코드" name="it_id" size="40" value="<?php echo $it_id;?>">
    <button type="submit" class="btn btn-primary">검색</button><a href="gangja_order_list.php">강자닷컴 주문서 리스트</a>
</div>
</form>
<table class="table table-hover table-striped">
    <thead>
    <tr>
        <th>이미지</th>
        <th>강자닷컴상품코드</th>
        <th>오플상품코드</th>
        <th>상품명</th>
        <th><button class="btn btn-success" onclick="location.href='./gangja_mapping_insert.php'">추가</button></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($pr_item_list as $row) { ?>
        <tr>
            <td><?php echo get_it_image($row['it_id'] . '_s', 70, 70, null, null, false, false, false); ?></td>
            <td><a target="_blank;" href="http://gangja.com/<?php echo $row['gangja_it_id']; ?>?cuid=&sub_cuid="><?php echo $row['gangja_it_id']; ?></a></td>
            <td><a target="_blank;" href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id']; ?>"><?php echo $row['it_id']; ?></a></td>
            <td><?php echo get_item_name($row['it_name'], 'list'); ?></td>
            <td><button onclick="delete_submit('<?php echo $row['uid']; ?>')" class="btn btn-primary">삭제</button></td>
        </tr>
    <?}?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5" class="text-center">
            <?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); //페이징 다시 작업?>
        </td>
    </tr>
    </tfoot>
</table>
<form action="gangja_mapping_insert.php" method="post" id="f">
    <input type="hidden" name="mode" value="del">
    <input type="hidden" name="uid" value="">
</form>
<script>
    function delete_submit(id) {
        if(confirm('삭제 하시겠습니까?')){
            $('input[name=uid]').val(id);
            $('#f').submit();
        }
    }
</script>
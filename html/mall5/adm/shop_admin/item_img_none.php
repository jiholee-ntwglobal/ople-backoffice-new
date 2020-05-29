<?php
$sub_menu = "600900";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

//검색
$it_id = isset($_GET['it_id']) ? trim($_GET['it_id']) : '';
$it_name = isset($_GET['it_name']) ? trim($_GET['it_name']) : '';
$it_maker = isset($_GET['it_maker']) ? trim($_GET['it_maker']) : '';
$upc = isset($_GET['upc']) ? trim($_GET['upc']) : '';

//조건문
$where = '';
if ($it_id != '') {
    $where .= " and a.it_id = '" . trim($it_id) . "' ";
}
if ($it_name != '') {
    $where .= " and a.it_name like '%" . trim($it_name) . "%' ";
}
if ($it_maker != '') {
    $where .= " and a.it_maker like '%" . trim($it_maker) . "%' ";
}
if ($upc != '') {
    $where .= " and d.upc = '" . trim($upc) . "' ";
}


//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";
//페이징
$pagequery = "  select  count(distinct a.it_name) cnt
      from yc4_item a
      left join yc4_item_img_detail b
      on a.it_id = b.it_id
      left join test_yc4_item_img_none c
      on a.it_id = c.it_id
      left join ople_mapping d
      on a.it_id = d.it_id
      inner join yc4_category_item e
      on e.it_id = a.it_id
      where  it_use = 1
      and it_discontinued = 0
      and b.it_id is null
      $where";
$pageresult = sql_query( $pagequery);
$page_rows = sql_fetch_array($pageresult);
$row = $page_rows['cnt'];
$total_list = $row;//페이징=전체컬럼수
$list_page = 15;//보여줄 컬럼
$num_page = 10;//표시할 페이지
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


// 출력 쿼리
$sql = "select  distinct a.it_name,a.it_id,c.comment,a.it_maker,a.it_amount,a.it_amount_usd
      from yc4_item a
      left join yc4_item_img_detail b
      on a.it_id = b.it_id
      left join test_yc4_item_img_none c
      on a.it_id = c.it_id
      left join ople_mapping d
      on a.it_id = d.it_id
      inner join yc4_category_item e
      on e.it_id = a.it_id
      where  it_use = 1
      and it_discontinued = 0
      and b.it_id is null
      $where
      order by if(c.comment='' || c.comment is null ,1,0) asc,if(a.it_stock_qty<1,1,0) asc,a.ps_cnt desc,it_order desc
      limit " . ($now_page - 1) * $list_page . "," . $list_page;
$result = sql_query( $sql);
$g4['title'] = "상품이미지 없는 리스트";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<!--검색-->
<form class="form-inline" style="text-align: right;" onsubmit="return searchdata()">
    <input type="text" name="it_id" value="<?php echo $it_id; ?>" placeholder="상품번호">
    <input type="text" name="it_name" value="<?php echo $it_name; ?>" placeholder="상품명">
    <input type="text" name="it_maker" value="<?php echo $it_maker; ?>" placeholder="브랜드">
    <input type="text" name="upc" value="<?php echo $upc; ?>" placeholder="upc">
    <button class="btn btn-default" type="submit">검색</button>
    <?php if ($it_id || $it_name || $it_maker || $upc) {
        echo "<input class=\"btn btn-warning\" type=\"button\" value=\"처음으로\" onclick=\"location.href='http://209.216.56.107/mall5/adm/shop_admin/item_img_none.php?'\" >";
    } ?>
</form>
<!--뷰단-->
<table class="table">
    <thead>
    <tr>
        <th>이미지</th>
        <th>브랜드</th>
        <th>상품번호</th>
        <th>상품명</th>
        <th>UPC</th>
        <th>가격</th>
        <th colspan="2">메모</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = sql_fetch_array($result)){

    //가격 원 달러
    $it_amount_usd = '';
    $it_amount = '';
    if ($row['it_amount_usd']) {
        $it_amount_usd = $row['it_amount_usd'] . " $<br/>";
    }
    if ($row['it_amount']) {
        $it_amount = number_format($row['it_amount']) . " ￦";
    }
    $amount = $it_amount_usd . $it_amount;


    //upc 쿼리
    $sql_set = "select upc from ople_mapping  where it_id='" . $row['it_id'] . "'";
    $result_set = sql_query( $sql_set);
    $upc_sets = '';
    //upc 출력문
    while ($result_set_row = sql_fetch_array($result_set)) {
        $upc_sets .= $result_set_row['upc'] . "<br/>";
    }
    ?>
    <tr>
        <td><img src="http://115.68.20.84/item/<?php echo $row['it_id']; ?>_l1" width="100" height="100"></td>
        <td><?php echo $row['it_maker']; ?></td>
        <td>
            <a target="_blank"
               href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id']; ?>"><span><?php echo $row['it_id']; ?></span></a>
        </td>
        <td style="width: 850px;"><?php echo get_item_name($row['it_name'],"detail"); ?></td>
        <td><?php echo $upc_sets; ?></td>
        <td style="text-align: right;"><?php echo $amount; ?></td>
        <td><textarea rows="5" style="width: 200px;"><?php echo $row['comment']; ?></textarea></td>
        <td>
            <button class="btn btn-success" type="button" onclick="modify_item_img(this)">저장</button>
        </td>
    </tr>
    </tbody>
    <?php } ?>
    <tfoot>
    <tr>
        <td colspan="8"
            style="text-align: center;"><?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); //페이징 다시 작업?></td>
    </tr>
    </tfoot>
</table>
<?php //수정 및 insert ?>
<form action="./item_img_none_insert.php" method="post" id="frm_insert">
    <input type="hidden" name="it_ids">
    <textarea name="comments" style="display: none;"></textarea>
    <input type="hidden" name="urls" value="<?php echo http_build_query($_GET); ?>">
    <input name="id" type="hidden" value="<?=$member['mb_id']?>">

</form>
<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
<script>
    //수정및 insert
    function modify_item_img(btn) {

        var comment = $(btn).parent().parent().find("textarea").val();
        var it_id = $(btn).parent().parent().find("span").text();
        $("textarea[name=comments]").val(comment);
        $("input[name=it_ids]").val(it_id);

        $('#frm_insert').submit();
    }

    //검색
    function searchdata() {
        var it_id = $.trim($('input[name=it_id]').val());
        var it_name = $.trim($('input[name=it_name]').val());
        var it_maker = $.trim($('input[name=it_maker]').val());
        var upc = $.trim($('input[name=upc]').val());
        if (it_id == '' && it_name == '' && it_maker == '' && upc == '') {
            alert('검색어를 입력해주세요');
            return false;
        }
        $('input[name=it_id]').val(it_id);
        $('input[name=it_name]').val(it_name);
        $('input[name=it_maker]').val(it_maker);
        $('input[name=upc]').val(upc);
        return true;
    }
</script>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-08-31
 * Time: 오전 10:19
 */
$sub_menu = "300567";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

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

//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";
$list_page = 20;//보여줄 컬럼
$num_page = 5;//표시할 페이지

$where = '';

if(isset($_GET['it_id_search']) && trim($_GET['it_id_search'])!=''){

    $it_id_search = sql_safe_query(trim($_GET['it_id_search']));
    $where .= ($where == ''?' where ' : ' and ') ." a.it_id ='".$it_id_search."'";

}

if(isset($_GET['it_id_search_1']) && trim($_GET['it_id_search_1'])!=''){

    $it_id_search = sql_safe_query(trim($_GET['it_id_search_1']));
    $where .=($where == ''?' where ' : ' and ') ." child_it_id ='".$it_id_search."'";

}

//리스트
$yc4_item_set_it_id_result = sql_query("
            SELECT a.it_id,b.it_name
            FROM yc4_item_set a
            inner join yc4_item b on a.it_id = b.it_id 
            $where
            GROUP BY a.it_id
            ORDER BY a.uid DESC
            limit  ".($now_page - 1) * $list_page." ,{$list_page}
");
$in = '';
$yc4_item_set_it_name = array();

while ($yc4_item_set_row_it_id = sql_fetch_array($yc4_item_set_it_id_result) ){

    $in .= ",'".$yc4_item_set_row_it_id['it_id']."'";
    $yc4_item_set_it_name[$yc4_item_set_row_it_id['it_id']]=$yc4_item_set_row_it_id['it_name'];

}


if($in!='') {

    $in = substr($in, 1);

    $yc4_item_set_result = sql_query("
        SELECT a.it_id,
              
                child_it_id,
                c.it_name as child_it_name,
                b.upc,
                child_qty
        FROM yc4_item_set a
        inner join ople_mapping b on  a.it_id = b.it_id 
        left outer join yc4_item c on  a.child_it_id = c.it_id
        where a.it_id in (" . $in . ")
        ORDER BY a.uid DESC
        ");

    $yc4_item_set_array = array();

    while ($yc4_item_set_row = sql_fetch_array($yc4_item_set_result) ){
        $yc4_item_set_array[$yc4_item_set_row['it_id']][$yc4_item_set_row['child_it_id']]['upc'] = $yc4_item_set_row['upc'];
        $yc4_item_set_array[$yc4_item_set_row['it_id']][$yc4_item_set_row['child_it_id']]['child_qty'] = $yc4_item_set_row['child_qty'];
        $yc4_item_set_array[$yc4_item_set_row['it_id']][$yc4_item_set_row['child_it_id']]['it_name'] = $yc4_item_set_row['child_it_name'];

    }
}

//개수
$cnt_sql  = "
        SELECT count(distinct it_id) cnt
        FROM yc4_item_set a
        $where
";
$total_cnt = sql_fetch($cnt_sql);
$total_list = $total_cnt['cnt'];

$g4[title] = "건강정보 관리자";

define('bootstrap', true);

include_once("$g4[admin_path]/admin.head.php");
?>

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div>
    <h4>오플 세트상품 상세설명 등록된 리스트</h4>
</div>
<form class="form-inline text-right" method="get">
    <div class="form-group">
        <label>오플 세트상품 코드</label>
        <input type="text" class="form-control" name="it_id_search" value="<?php echo htmlspecialchars(trim($_GET['it_id_search'])); ?>">
    </div>
    <div class="form-group">
        <label>오플 단품상품 코드</label>
        <input type="text" class="form-control" name="it_id_search_1" value="<?php echo htmlspecialchars(trim($_GET['it_id_search_1'])); ?>">
    </div>
    <button type="submit" class="btn btn-default">검색</button>
</form>
<table class="table table-hover">
    <thead>
    <tr>
        <th>
            세트상품 IT_ID
        </th>
        <th>
            등록된 IT_ID
        </th>
        <th><button class="btn btn-success" onclick="location.href='./set_item_info_form.php?mode=insert'" type="button">추가</button></th>
    </tr>
    </thead>
    <tbody>
    <?php if(is_array($yc4_item_set_array) && !empty($yc4_item_set_array)){
        foreach($yc4_item_set_array as $key => $child_it_ids){ ?>
            <tr>
                <td style="vertical-align: middle;"><label>상품코드</label><a href="http://ople.com/mall5/shop/item.php?it_id=?<?php echo $key;?>" target="_blank"><strong style="color: red;"><?php echo $key ;?></strong></a>
                    <?php echo get_item_name($yc4_item_set_it_name[$key],'list');?>
                </td>
                <td>
                    <?php if(is_array($child_it_ids) && !empty($child_it_ids)){?>
                        <table class="table">
                            <thead>
                            <tr>
                                <td>IT_ID<BR><span style="color: green">UPC</span></td>
                                <td>제품명</td>
                                <td>수량</td>
                            </tr>
                            </thead>
                            <tbody>
                        <?php foreach ($child_it_ids as $child_it_id => $child_it_id_data){ ?>
                            <tr>
                                <td><a href="http://ople.com/mall5/shop/item.php?it_id=?<?php echo $child_it_id;?>" target="_blank"><?php echo  $child_it_id; ?></a><br>
                                    <span style="color: green;"><?php echo  $child_it_id_data['upc']; ?></span></td>
                                <td><?php echo get_item_name($child_it_id_data['it_name'],'list');?></td>
                                <td><?php echo  $child_it_id_data['child_qty']; ?></td>
                            </tr>
                        <?php } ?>
                            </tbody>
                        </table>
                    <?php }?>
                </td>
                <td><button class="btn-danger btn" type="button" onclick="location.href='./set_item_info_form.php?mode=update&it_id=<?php echo $key;?>'">수정</button></td>
            </tr>
        <?php }
    }?>

    </tbody>
    <tfoot>
    <tr>
        <td class="text-center" colspan="3"><?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?></td>
    </tr>
    </tfoot>
</table>

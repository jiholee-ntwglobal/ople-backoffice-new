<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-10-10
 * Time: 오후 4:16
 */
$sub_menu = "300124";
include_once "./_common.php";
include_once $g4['full_path'] . '/lib/db.php';
auth_check($auth[$sub_menu], "r");
$searchs =array();
$where = '';
$where_in = '';
$_GET['name'] = trim($_GET['name']) ? trim($_GET['name']) : '';
$_GET['tab'] = trim($_GET['tab'])=='' ? 'all' : $_GET['tab'];

if($_GET['name'] !=''){
    $where .=  " and  b.it_name like '%".sql_safe_query(trim($_GET['name']))."%'";
}

if(trim($_GET['upc']) != '' ){

    $result = sql_query("
                SELECT a.it_id
            FROM ople_mapping a 
            WHERE a.ople_type = 's'
            AND a.upc = '".sql_safe_query(trim($_GET['upc']))."'
    ");

    while ($search_upc = sql_fetch_array($result)){

        $where_in .= ",'".$search_upc['it_id']."'";

    }
}

if (trim($_GET['it_id']) != '') {
    $searchs = explode(PHP_EOL, trim($_GET['it_id']));
    array_walk($searchs, function (&$item) {
        if (is_string($item)) {
            $item = sql_safe_query(trim($item));
        }
    });
}

if(count($searchs)>0){
    foreach ($searchs as $search_it_id){
        $where_in .= ",'".$search_it_id."'";
    }

}

if(trim($where_in)!=''){
    $where_in = substr($where_in, 1);
    $where = ' and  a.it_id in ('.$where_in.')';
}

//paging
function bootstrap_paging($total_list, $now_page, $list_page, $num_page, $searchdata)
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

$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";
$list_page = 10;//보여줄 컬럼
$num_page = 5;//표시할 페이지
if($_GET['tab'] =='all') {
    $result = sql_query("
            SELECT a.it_id, b.it_maker, b.it_name,b.it_amount,b.it_amount_usd
            FROM ople_mapping a INNER JOIN yc4_item b ON a.it_id = b.it_id
            WHERE a.ople_type = 's'
            {$where}
            group by a.it_id
            having count(a.it_id) >1
            ORDER BY b.it_create_time DESC
            limit  " . ($now_page - 1) * $list_page . " ,{$list_page}
            ");

    $set_item = array();
    $in = '';
    while ($row = sql_fetch_array($result)) {

        array_push($set_item, $row);

        $in .= ",'" . $row['it_id'] . "'";
    }
    $in = substr($in, 1);
    $upc_amount = array();

    if (trim($in) != '') {

        $db = new db();

        $stmt = $db->ntics_db->prepare("
            SELECT a.it_id,
                   a.upc,
                   a.qty,
                  /* concat (
                      rtrim (b.item_name),
                      CASE
                         WHEN rtrim (isnull (b.POTENCY, '')) != ''
                         THEN
                            concat (' ', rtrim (b.POTENCY))
                      END,
                      CASE
                         WHEN rtrim (isnull (b.POTENCY_UNIT, '')) != ''
                         THEN
                            concat (' ', rtrim (b.POTENCY_UNIT))
                      END,
                      CASE
                         WHEN rtrim (isnull (b.count, '')) != ''
                         THEN
                            concat (' ', rtrim (b.count))
                      END,
                      CASE
                         WHEN rtrim (isnull (b.type, '')) != ''
                         THEN
                            concat (' ', rtrim (b.type))
                      END)
                      item_name,*/
                   c.single_amount
              FROM ople_mapping a
                   /*INNER JOIN N_MASTER_ITEM b ON a.upc = b.upc*/
                   LEFT OUTER JOIN ople_set_amount_info c ON a.upc = c.upc
             WHERE a.Ople_Type = 's' AND a.it_id IN
            ({$in})
            ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $value) {

            $upc_amount[$value['it_id']][] = $value;

        }

    }

    $sql = "
            SELECT count(*) cnt
            FROM (SELECT COUNT(a.it_id) cnt
                  FROM ople_mapping a INNER JOIN yc4_item b ON a.it_id = b.it_id
                  WHERE a.ople_type = 's'
                  {$where}
                  GROUP BY a.it_id
                  HAVING count(a.it_id) > 1) b
            ";
    $coupon_cnt = sql_fetch($sql);
    $total_list = $coupon_cnt['cnt'];

}else{

    $db = new db();

    $stmt = $db->ntics_db->prepare("
            SELECT DISTINCT a.it_id
              FROM ople_mapping a
                   INNER JOIN ople_mapping b ON a.it_id = b.it_id AND a.upc != b.upc
                   LEFT OUTER JOIN ople_set_amount_info c ON c.upc = a.upc
             WHERE b.id IS NOT NULL AND c.upc IS NULL
            ORDER BY a.it_id DESC
            OFFSET " . ($now_page - 1) * $list_page . " ROWS
            FETCH NEXT ".$list_page." ROWS ONLY
            ");

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $where = '';
    foreach ($result as $row) {

        $where .= ",'" . $row['it_id'] . "'";
    }

    $where = substr($where, 1);


    $result = sql_query("
            SELECT a.it_id, b.it_maker, b.it_name,b.it_amount,b.it_amount_usd
            FROM ople_mapping a INNER JOIN yc4_item b ON a.it_id = b.it_id
            WHERE a.ople_type = 's'
            and a.it_id in ({$where})
            group by a.it_id
            having count(a.it_id) >1
            ORDER BY b.it_create_time DESC
            ");

    $set_item = array();
    $in = '';
    while ($row = sql_fetch_array($result)) {

        array_push($set_item, $row);

        $in .= ",'" . $row['it_id'] . "'";
    }
    $in = substr($in, 1);
    $upc_amount = array();

    if (trim($in) != '') {



        $stmt = $db->ntics_db->prepare("
            SELECT a.it_id,
                   a.upc,
                   a.qty,
                  /* concat (
                      rtrim (b.item_name),
                      CASE
                         WHEN rtrim (isnull (b.POTENCY, '')) != ''
                         THEN
                            concat (' ', rtrim (b.POTENCY))
                      END,
                      CASE
                         WHEN rtrim (isnull (b.POTENCY_UNIT, '')) != ''
                         THEN
                            concat (' ', rtrim (b.POTENCY_UNIT))
                      END,
                      CASE
                         WHEN rtrim (isnull (b.count, '')) != ''
                         THEN
                            concat (' ', rtrim (b.count))
                      END,
                      CASE
                         WHEN rtrim (isnull (b.type, '')) != ''
                         THEN
                            concat (' ', rtrim (b.type))
                      END)
                      item_name,*/
                   c.single_amount
              FROM ople_mapping a
                   /*INNER JOIN N_MASTER_ITEM b ON a.upc = b.upc*/
                   LEFT OUTER JOIN ople_set_amount_info c ON a.upc = c.upc
             WHERE a.Ople_Type = 's' AND a.it_id IN
            ({$in})
            ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $value) {

            $upc_amount[$value['it_id']][] = $value;

        }

    }



    $stmt = $db->ntics_db->prepare("
            SELECT count (DISTINCT a.it_id) cnt
              FROM ople_mapping a
                   INNER JOIN ople_mapping b ON a.it_id = b.it_id AND a.upc != b.upc
                   LEFT OUTER JOIN ople_set_amount_info c ON c.upc = a.upc
             WHERE b.id IS NOT NULL AND c.upc IS NULL
            ");
    $stmt->execute();
    $total_list = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_list = $total_list['cnt'];

}
$g4[title] = "다중세트상품 단품가";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form class="form-inline text-right">
    <div class="form-group">
        <label>UPC</label>
        <input type="text" class="form-control" name="upc" value="<?php echo htmlspecialchars($_GET['upc']); ?>">
    </div>
    <div class="form-group">
        <label>세트상품명</label>
        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($_GET['name']); ?>">
    </div>

    <div class="form-group">
        <label>세트상품코드</label>
        <textarea name="it_id" rows="4" class="form-control"><?php echo htmlspecialchars($_GET['it_id']);?></textarea>
    </div>
    <button class="btn btn-primary" type="submit">검색</button>
</form>
<div class='row'>
    <div class="col-lg-12 text-right">
        <ul class="nav nav-tabs">
            <li role="presentation" id="tab_upc" class='<?php echo $_GET['tab'] == '' || $_GET['tab'] == 'all' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?">전체</a>
            </li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'no' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?tab=no">단품가 미등록 세트상품</a>
            </li>
        </ul>
    </div>
</div>
<table class="table">
    <thead>
    <tr>
        <th colspan="2">세트상품코드</th>
        <th>UPC</th>
        <th>세트가격</th>
        <th>단품정보</th>
    </tr>
    </thead>
    <?php if (!empty($set_item)) { ?>
    <tbody>
    <?php foreach ($set_item as $set_row){ ?>
    <tr>
        <td><?php echo get_it_image($set_row['it_id'] . '_s', 80, 80, null, null, false, false) ?></td>
        <td><a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $set_row['it_id'] ?>" target="_blank"><?php echo $set_row['it_id']; ?></a></td>
        <td width="35%"><?php echo get_item_name($set_row['it_name'], 'list');?></td>
        <td>$ <?php echo $set_row['it_amount_usd']; ?><br>(\ <?php echo $set_row['it_amount']; ?>)</td>
        <td>
            <?php if(count($upc_amount[$set_row['it_id']])>0){ ?>
                <table class="table table-hover">
                    <tr>
                        <td><strong>UPC</strong></td>
                        <td><strong>SET QTY</strong></td>
                        <!--<td>제품명</td>-->
                        <td><strong>단품가</strong></td>
                        <td></td>
                    </tr>
                    <?php foreach($upc_amount[$set_row['it_id']] as $upc_data){ ?>
                        <tr>
                            <td><?php echo $upc_data['upc'];?></td>
                            <td><?php echo $upc_data['qty'];?></td>
                            <!--<td><?php /*echo $upc_data['item_name'];*/?></td>-->
                            <td><?php echo !$upc_data['single_amount'] || trim($upc_data['single_amount'])== ''? '미등록' : '$ '.$upc_data['single_amount'];?></td>
                            <td>
                                <?php if(!$upc_data['single_amount'] || trim($upc_data['single_amount'])== ''){ ?>
                                    <button class="btn btn-success btn-xs" type="button" onclick="location.href='./set_item_amount_form.php?upc=<?php echo $upc_data['upc'];?>'">등록</button>
                                <?php }else { ?>
                                    <button class="btn btn-info btn-xs" type="button" onclick="location.href='./set_item_amount_form.php?upc=<?php echo $upc_data['upc'];?>'">수정</button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php }?>
        </td>
    </tr>
    <?php } ?>
    </tbody>
        <tfoot>
        <tr>
            <td class="text-center" colspan="9"><?php echo bootstrap_paging($total_list, $now_page, $list_page, $num_page, $_GET); ?></td>
        </tr>
        </tfoot>
    <? } ?>
</table>
<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-13
 * Time: 오후 2:45
 *       AND b.it_id NOT IN ('1506165138',
'1510294015',
'1510800182',
'1510800282',
'1510800582',
'1510801783',
'1510801883',
'1510801983',
'1510802083',
'1510815383',
'1510815683',
'1510840915',
'1510841015',
'1510845115',
'1510848615',
'1510848715',
'1510848815',
'1510848915',
'1510849015',
'1510852215',
'1510852315',
'1510866515',
'1510866915',
'1510873415')
 */
$sub_menu = "300666";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");
$_GET['tab'] = trim($_GET['tab']) != '' ? $_GET['tab'] : 'Y';
// 클리어런스 상품 제외
$except_smt   = sql_query("
                      SELECT a.it_id FROM yc4_item a
                      LEFT JOIN yc4_event_item b ON a.it_id=b.it_id
                      WHERE b.ev_id = '1424920190'
                      AND a.it_use = '1'
                      ");
$except_it_id   = array();
while($row = sql_fetch_array($except_smt)){
    $except_it_id[] = $row['it_id'];
}
$except_where   = "";
if(count($except_it_id) > 0){
    $except_where   = " AND b.it_id NOT IN ('".implode("', '", $except_it_id)."')";
}
$where = '';
if ($_GET['it_id']) {
    if(trim($_GET['it_id'])) {
        $arritid['it_id']=$_GET['it_id'];
        $it_id_arr = explode(PHP_EOL, trim($arritid['it_id']));
        $it_id_cnt = count($it_id_arr);
        if ($it_id_cnt > 0) {
            $it_id_in = '';
            foreach ($it_id_arr as $value) {
                $po_it_id = trim($value);
                if (!$po_it_id) {
                    continue;
                }
                $it_id_in .= ($it_id_in ? "," : "") . "'" . $po_it_id . "'";
            }
            $where .= "and b.it_id in (" . $it_id_in . ") ";
        }
    }
}
$pr_item_list = array();
//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";
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

if ($_GET['tab'] == 'Y') {

    $row_cnt = sql_fetch("
    SELECT COUNT(DISTINCT b.it_id) AS cnt
FROM yc4_category_new    a
     LEFT JOIN yc4_category_item c ON a.ca_id = c.ca_id
     LEFT JOIN yc4_item b ON c.it_id = b.it_id
     left join yc4_no_new_item n on b.it_id=n.it_id
WHERE     b.it_id IS NOT NULL
      AND date_format(b.it_create_time, '%Y%m%d') >=
             date_format(date_add(now(), INTERVAL -3 MONTH), '%Y%m%d')
             $except_where
      AND b.it_use = '1'                                       /* 판매 가능 상풍만 */
$where
      AND b.it_discontinued = 0
");
    $total_count = $row_cnt['cnt'];
    $total_list = $total_count;//페이징=전체컬럼수
    $list_page = 30;//보여줄 컬럼
    $num_page = 5;//표시할 페이지
    $pr_item_sql = sql_query("
SELECT DISTINCT b.it_id,
                b.it_name,
              date_format(b.it_create_time, '%Y-%m-%d') ymd
FROM yc4_category_new    a
     LEFT JOIN yc4_category_item c ON a.ca_id = c.ca_id
     LEFT JOIN yc4_item b ON c.it_id = b.it_id
     left join yc4_no_new_item n on b.it_id=n.it_id
WHERE     b.it_id IS NOT NULL
and n.it_id is null
      AND date_format(b.it_create_time, '%Y%m%d') >=
             date_format(date_add(now(), INTERVAL -3 MONTH), '%Y%m%d')
      AND b.it_use = '1'                                       /* 판매 가능 상풍만 */
      $except_where
      AND b.it_discontinued = 0
      $where
ORDER BY b.it_create_time DESC, b.it_id DESC
  limit " . ($now_page - 1) * $list_page . "," . $list_page);

    while ($row = sql_fetch_array($pr_item_sql)) {
        $pr_item_list[] = $row;
    }

} elseif ($_GET['tab'] == 'N') {
    $row_cnt = sql_fetch("
    SELECT COUNT(DISTINCT a.it_id) AS cnt
FROM yc4_no_new_item a
INNER JOIN yc4_item b on a.it_id=b.it_id 
where date_format(b.it_create_time, '%Y%m%d') >=

             date_format(date_add(now(), INTERVAL -3 MONTH), '%Y%m%d')
             $where
");
    $total_count = $row_cnt['cnt'];
    $total_list = $total_count;//페이징=전체컬럼수
    $list_page = 30;//보여줄 컬럼
    $num_page = 5;//표시할 페이지
    $pr_item_sql = sql_query("
SELECT DISTINCT b.it_id,
                b.it_name,
                date_format(b.it_create_time, '%Y-%m-%d') ymd
FROM yc4_no_new_item a
INNER JOIN yc4_item b on a.it_id=b.it_id 
where date_format(b.it_create_time, '%Y%m%d') >=
             date_format(date_add(now(), INTERVAL -3 MONTH), '%Y%m%d')
             $where
ORDER BY b.it_create_time DESC
  limit " . ($now_page - 1) * $list_page . "," . $list_page);

    while ($row = sql_fetch_array($pr_item_sql)) {
        $pr_item_list[] = $row;
    }
}
$tab_url  = $_GET;
unset($tab_url['page']);
unset($tab_url['tab']);
$tab_url = http_build_query($tab_url);
$g4[title] = "신상품 카테고리 관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <div class="row">
        <div class="col-lg-12">
            <h4>신상품 카테고리 노출/비노출 관리<a href="http://ople.com/mall5/shop/new_item_list.php" target="_blank"><<바로보기>></a></h4>
            <span style="color:  red;">※ 신상품 노출 = 오플상품코드가 생성된날짜(3달전~현재날짜) 노출 <br></span>
            <span style="color:  red;">※ 이벤트 관리 -> 이벤트 관리 -> 클리어런스_할인에 등록된 상품은 자동제외 </span>
        </div>
    </div>
    <div class="panel">
        <form>
            <div class="row">
                <div class="col-lg-7">

                </div>
                <div class="col-lg-3">
                    <label>오플 상품코드</label><br>
                    <textarea name="it_id" style="width: 100%" rows="3"><?php echo $_GET['it_id'] ;?></textarea>
                    <input type="hidden" name="tab" value="<?php echo $_GET['tab'];?>">
                </div>

                <div class="col-lg-2">
                    <BR>
                    <button class="btn btn-primary btn-block" type="submit">검색</button>
                </div>
            </div>
            <div class='row'>
                <div class="col-lg-12 text-right">
                    <ul class="nav nav-tabs">
                        <li role="presentation" class='<?php echo $_GET['tab'] == 'Y' ? 'active' : ''; ?>'><a
                                    href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $tab_url;?>&tab=Y">노출되는 신상품</a></li>
                        <li role="presentation" class='<?php echo $_GET['tab'] == 'N' ? 'active' : ''; ?>'><a
                                    href="<?php echo $_SERVER['PHP_SELF']; ?>?&<?php echo $tab_url;?>&tab=N">노출안되는 신상품</a></li>
                    </ul>
                </div>
            </div>
        </form>
        <form method="post" action="no_new_item_change.php"  onsubmit="return chk_send();" >
            <input type="hidden" name="fg" value="<?php echo $_GET['tab'];?>">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th> <input type="checkbox" name="chk_all" onclick="chk_all_item()"></th>
                    <th>상품코드</th>
                    <th>이미지</th>
                    <th>상품명</th>
                    <th>생성된날짜</th>
                    <th><button class="btn btn-info">일괄처리</button></th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($pr_item_list)) { ?>
                    <? foreach ($pr_item_list as $row) { ?>
                        <tr>
                            <td> <input type="checkbox" name="it_id[]" value="<?php echo $row['it_id']; ?>"></td>
                            <td><?php echo $row['it_id']; ?></td>
                            <td><?php echo get_it_image($row['it_id'] . '_s', 70, 70, null, null, false, false, false); ?></td>
                            <td><?php echo get_item_name($row['it_name'], 'list'); ?></td>
                            <td><?php echo $row['ymd']; ?></td>
                            <td>
                                <button class="btn btn-info" type="button"
                                        onclick="set_new_item('<?php echo $row['it_id']; ?>','<?php echo $_GET['tab']; ?>');"><?php echo $_GET['tab'] == 'Y' ? "제외" : "노출"; ?></button>
                            </td>
                        </tr>
                    <?php }
                } ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="11" class="text-center">
                        <?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
    </div>
    <form method="post" action="./no_new_item_change.php" id="f">
        <input type="hidden" name="it_id[]">
        <input type="hidden" name="fg">
    </form>
    <script>
        function set_new_item(id, fg) {
            if (confirm(id + "해당 상품을 변경하겠습니까?")) {
                if (id && (fg == 'Y' || fg == 'N')) {
                    $('input[name*=it_id]').val(id);
                    $('input[name=fg]').val(fg);
                    $('#f').submit();
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        function chk_all_item(){
            var chk = $('input[name=chk_all]').prop('checked');
            $('input[name*=it_id]').prop('checked',chk);
        }
        function chk_send(){
            var item_sel = $('input[name*=it_id]:checked').length;
            if(item_sel < 1){
                alert('선택된 상품이 없습니다.');
                return false;
            }
            if(!confirm(item_sel+'개 상품을 처리 하시겠습니까?')){
                return false;
            }

            return true;
        }
    </script>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
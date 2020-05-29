<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-06-22
 * Time: 오후 2:58
 */
$sub_menu = "500530";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");
//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";
$_GET['tab'] = trim($_GET['tab']) ? trim($_GET['tab']) : 'all';
$_GET['mb_id'] = trim($_GET['mb_id']) ? trim($_GET['mb_id']) : '';
$_GET['mb_name'] = trim($_GET['mb_name']) ? trim($_GET['mb_name']) : '';
$_GET['tab'] = trim($_GET['tab']) ? trim($_GET['tab']) : 'all';
$where = '';

if($_GET['mb_id']){
    $mb_id = sql_safe_query($_GET['mb_id']);
    $where .= ($where==''?' where ' : ' and ') . "a.mb_id = '{$mb_id}'";
}
if($_GET['mb_name']){
    $mb_name= sql_safe_query($_GET['mb_name']);
    $where .= ($where==''?' where ' : ' and ') . "b.mb_name = '{$mb_name}'";
}
if ($_GET['tab'] == 'ing'){
    $where .= ($where==''?' where ' : ' and ') . "date_format(now(),'%Y%m%d')  BETWEEN  a.start_dt and a.end_dt";
}elseif ($_GET['tab'] == 'end'){
    $where .= ($where==''?' where ' : ' and ') . "a.end_dt < date_format(now(),'%Y%m%d')";

}elseif ($_GET['tab'] == 'wait'){
    $where .= ($where==''?' where ' : ' and ') . "a.start_dt > date_format(now(),'%Y%m%d')";
}
$list_page = 20;//보여줄 컬럼
$num_page = 5;//표시할 페이지
$sql =
    "
SELECT
       a.uid,
       a.mb_id,
       b.mb_name,
       date_format(a.start_dt,'%Y-%m-%d') start,
       date_format(a.end_dt,'%Y-%m-%d') end,
       sum(if(date_format(c.wr_datetime,'%Y%m%d')  BETWEEN  a.start_dt and a.end_dt and c.mb_id is not null ,1,0)) 'now_list'
FROM opler a INNER JOIN g4_member b 
        ON a.mb_id = b.mb_id
LEFT OUTER JOIN g4_write_experience c
        ON a.mb_id = c.mb_id AND c.wr_is_comment = 0 AND c.wr_id > 0
{$where}
group by 
        a.uid,
        a.mb_id,
        b.mb_name,
        date_format(a.start_dt, '%Y-%m-%d'),
        date_format(a.end_dt, '%Y-%m-%d')
order by a.uid desc
limit  ".($now_page - 1) * $list_page." ,{$list_page}
   ";
$opler_result = sql_query($sql);
$opler_list = array();
while ($row = sql_fetch_array($opler_result)){
    array_push($opler_list ,$row);
}
$sql =
    "
SELECT count(*) cnt
FROM opler a INNER JOIN g4_member b ON a.mb_id = b.mb_id
{$where}
order by a.uid desc
   ";
$opler_cnt = sql_fetch($sql);
$total_list = $opler_cnt['cnt'];

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
$get_data = $_GET;
unset($get_data['tab']);
unset($get_data['page']);
$get_data=http_build_query($get_data);
$g4[title] = "오플러 관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>오플러 관리</h4>
    </div>
</div>
<form class="form-inline text-right">
    <input type="hidden"value="<?php echo $_GET['tab'];?>" name="tab">
    <div class="form-group">
        <label>이름</label>
        <input type="text" class="form-control" name="mb_name" value="<?php echo $_GET['mb_name'];?>">
    </div>
    <div class="form-group">
        <label>아이디</label>
        <input type="text" class="form-control" name="mb_id" value="<?php echo $_GET['mb_id'];?>">
    </div>
    <button class="btn btn-primary" type="submit">검색</button>
</form>
<br>
<div class='row'>
    <div class="col-lg-12 text-right">
        <ul class="nav nav-tabs">
            <li role="presentation" id="tab_upc" class='<?php echo $_GET['tab'] == 'all' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $get_data;?>&tab=all">전체</a></li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'ing' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $get_data;?>&tab=ing">진행중인오플러</a></li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'wait' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $get_data;?>&tab=wait">시작예정오플러</a></li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'end' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $get_data;?>&tab=end">종료된오플러</a></li>
        </ul>
    </div>
</div>
<?php if(!empty($opler_list)){ ?>
    <table class="table table-hover">
        <thead>
        <tr>
            <th>아이디</th>
            <th>이름</th>
            <th>시작날짜</th>
            <th>종료날짜</th>
            <th>게시글 등록 여부</th>
            <th><button type="button" class="btn btn-success" onclick="location.href='ople_write.php'">추가</button></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($opler_list as $opler_data){ ?>
        <tr>
            <td><?php echo $opler_data['mb_id']; ?></td>
            <td><?php echo $opler_data['mb_name']; ?></td>
            <td><?php echo $opler_data['start']; ?></td>
            <td><?php echo $opler_data['end']; ?></td>
            <td class="text-center"><?php echo $opler_data['now_list']==='0'?'<label class="label label-danger">미등록</label>':'<label class="label label-success">등록</label>'; ?></td>
            <th><button type="button" class="btn btn-info" onclick="location.href='ople_write.php?uid=<?php echo $opler_data['uid'];?>'">수정</button> <button type="button" class="btn btn-danger" onclick="opler_del('<?php echo $opler_data['uid'];?>')">삭제</button></th>
        </tr>
        </tbody>
        <?php } ?>
        <tfoot>
        <tr>
            <td class="text-center" colspan="5"><?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?></td>
        </tr>
        </tfoot>
    </table>

<?php }?>
<script>
    function opler_del(id) {
        if(confirm('삭제하시겠습니까?')){
            location.href="./opler_write_upin.php?del=del&uid="+id;
        }
        return false;
    }
</script>

<?php
include_once("$g4[admin_path]/admin.tail.php");
?>


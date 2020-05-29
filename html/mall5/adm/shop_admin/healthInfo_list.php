<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-06-20
 * Time: 오후 4:57
 */
include_once("./_common.php");
$sub_menu = "600970";
auth_check($auth[$sub_menu], "r");

//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";

//검색
$_GET['tab'] =  $_GET['tab'] ? $_GET['tab'] : 'all';
$_GET['list_title'] =  $_GET['list_title'] ? trim($_GET['list_title']) : '';
$where = '';

if($_GET['list_title']){
    $list_title= sql_safe_query($_GET['list_title']);
    $where .= ($where==''?' where ' : ' and ') . "list_title like '%{$list_title}%'";
}
if ($_GET['tab'] == 'ing'){
    $where .= ($where == ''?' where ' : ' and ') . "status = '1'";
}elseif ($_GET['tab'] == 'end'){
    $where .= ($where == ''?' where ' : ' and ') . "status = '0'";

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

//리스트
$list_page = 20;//보여줄 컬럼
$num_page = 5;//표시할 페이지
$sql = "
 SELECT health_info_uid AS uid,
       list_title AS title,
       list_subtitle AS titles,
       status,
       create_date
  FROM health_info
  $where
ORDER BY health_info_uid DESC
limit  ".($now_page - 1) * $list_page." ,{$list_page}
 ";
$result = sql_query($sql);
$health_info_list = array();
while ($row =sql_fetch_array($result)){
    $health_info_list[] = $row;
}

//개수
$cnt_sql  = "
SELECT count(*)cnt
  FROM health_info
  $where
ORDER BY health_info_uid DESC
";
$total_cnt = sql_fetch($cnt_sql);
$total_list = $total_cnt['cnt'];

$get_data = $_GET;
unset($get_data['tab']);
unset($get_data['page']);
$get_data=http_build_query($get_data);

$g4[title] = "건강정보 관리자";
define('bootstrap', true);
include_once ("$g4[admin_path]/admin.head.php");
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>건강정보 리스트</h4>
    </div>
</div>
<form class="form-inline text-right">
    <input type="hidden"value="<?php echo $_GET['tab'];?>" name="tab">
    <div class="form-group">
        <label>제목</label>
        <input class="form-control" type="text" name="list_title" value="<?php echo $_GET['list_title'];?>">
    </div>
    <button class="btn btn-primary">검색</button>
</form>
<div class='row'>
    <div class="col-lg-12 text-right">
        <ul class="nav nav-tabs">
            <li role="presentation" id="tab_upc" class='<?php echo $_GET['tab'] == 'all' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $get_data;?>&tab=all">전체</a></li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'ing' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $get_data;?>&tab=ing">노출</a></li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'end' ?'active': '';?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $get_data;?>&tab=end">비노출</a></li>
        </ul>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <table class="table">
            <thead>
            <tr class="">
                <th >번호</th>
                <th>제목</th>
                <th>부제목</th>
                <th>상태</th>
                <th>등록일자</th>
                <th class="text-center"><button class="btn btn-success" type="button" onclick="location.href='healthInfo_form.php'">건강정보 추가</button></th>
            </tr>
            </thead>
            <?php if(!empty($health_info_list)){?>
                <tbody>
                <?php foreach ($health_info_list as $row){ ?>
                    <tr>
                        <td><?php echo $row['uid'];?></td>
                        <td><?php echo $row['title'];?></td>
                        <td><?php echo $row['titles'];?></td>
                        <td><?php echo $row['status'] == '1' ?  '노출' : '비노출';?></td>
                        <td><?php echo $row['create_date'];?></td>
                        <td class="text-center"><button class="btn btn-success" type="button" onclick="location.href='./healthInfo_form.php?uid=<?php echo $row['uid'];?>'">수정</button>&nbsp;<button class="btn btn-info" type="button" onclick="location.href='http://ople.com/mall5/shop/healthInfo_conpage.php?uid=<?php echo $row['uid'];?>&fg=y'">미리보기</button>&nbsp;<button class="btn btn-primary" type="button" onclick="location.href='http://ople.com/mall5/shop/healthInfo_conpage.php?uid=<?php echo $row['uid'];?>'">바로가기</button></td>
                    </tr>
                <? }?>
                </tbody>
                <tfoot>
                <tr>
                    <td class="text-center" colspan="6"><?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?></td>
                </tr>
                </tfoot>
            <? }?>
        </table>
    </div>
</div>

<?
include_once("$g4[admin_path]/admin.tail.php");
?>
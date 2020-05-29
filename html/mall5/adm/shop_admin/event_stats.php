<?php
$sub_menu = "500512";
include "_common.php";
auth_check($auth[$sub_menu], "r");

//검색
$tabs = ($_GET['tab'] == 'm' || $_GET['tab'] == 's') ? $_GET['tab'] : 'total';
$where = '';
$event=trim($_GET['event'])?trim($_GET['event']):'';
$week_array = array("일", "월", "화", "수", "목", "금", "토");

//조건문
if ($tabs == 'm') {
    $where = ($where==''?'where':' and ').' date_format(now(),\'%Y-%m%-%d\')>= st_dt
       and date_format(now(),\'%Y-%m%-%d\') <= en_dt or en_dt is null';
} elseif ($tabs == 's') {
    $where = ($where==''?'where':' and ').' date_format(now(),\'%Y-%m%-%d\') > en_dt';
}
if($event!=''){
    $where .= ($where==''?'where':' and ')." a.pr_name like '%". $event."%' ";
}

//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";
//페이징
$pagequery = "  SELECT
       count(distinct a.pr_name) cnt
  FROM yc4_promotion a
       LEFT JOIN yc4_promotion_visit_log b ON a.pr_id = b.pr_id
       left JOIN g4_member c ON b.mb_id = c.mb_id
       $where
";
$pageresult = sql_query( $pagequery);
$page_rows = sql_fetch_array($pageresult);
$row = $page_rows['cnt'];
$total_list = $row;//페이징=전체컬럼수
$list_page = 20;//보여줄 컬럼
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
        $pages.="<ul class=\"pagination\">";
        if ($now_page > 1) {
            $pages .= "<li><a  href=" . $server . "?page=1" . "&" . $search . "> 처음 </a></li>";
        }
        if ($now_block > 1) {
            $pages .= "<li><a   href=" . $server . "?page=" . $prev_page . "&" . $search . "> 이전 </a></li>";
        }
        if ($end_page >= $total_page) {
            $end_page = $total_page;
        }
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $now_page) {
                $pages .= "<li class='active'><a>" . $i . "</a></li>";
            } else {
                $pages .= "<li><a  href=" . $server . "?page=" . $i . "&" . $search . "> " . $i . " </a></li>";
            }
        }
        if ($now_page >= 1) {
            if ($total_block != $now_block) {
                $pages .= "<li><a  href=" . $server . "?page=" . $next_page . "&" . $search . "> 다음 </a></li>";
            }
            if ($now_page != $total_page) {
                $pages .= "<li><a  href=" . $server . "?page=" . $total_page . "&" . $search . "> 마지막 </a></li>";
            }
        }
        $pages.="</ui>";
    }
    return $pages;
}

//출력 쿼리
$sql = "SELECT
       a.pr_id,
       a.pr_name,
       a.st_dt,
       a.en_dt,
       sum(if(b.fg = 'P', 1, 0)) pc,
       sum(if(b.fg = 'M', 1, 0)) mo,
       sum(if(c.mb_sex = 'M', 1, 0)) f,
       sum(if(c.mb_sex = 'F', 1, 0)) m,
       sum(if(c.mb_sex = '' or c.mb_sex is null, 1, 0)) rain,
       if(date_format(now(),'%Y-%m%-%d')>= st_dt
       and date_format(now(),'%Y-%m%-%d') <= en_dt
       or en_dt is null,'진행중','종료') ing
  FROM yc4_promotion a
       LEFT JOIN yc4_promotion_visit_log b ON a.pr_id = b.pr_id
       left JOIN g4_member c ON b.mb_id = c.mb_id
       $where
GROUP BY  a.pr_name
order by en_dt
limit " . ($now_page - 1) * $list_page . "," . $list_page
;
$result = sql_query($sql);

$g4[title] = "프로모션 통계";
define('bootstrap', true);
include $g4['full_path'] . "/adm/admin.head.php";
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form class="form-inline" style="text-align: right;">
<div class="form-group">
    <label for="exampleInputEmail2">이벤트 명</label>
    <input type="hidden" name="tab" value="<?php echo $tabs; ?>">
    <input type="text" class="form-control" id="exampleInputEmail2" name="event"
           value="<?php echo $event;?>">
</div>
<button type="submit" class="btn btn-default">검색</button>

</form>
<ul class="nav nav-tabs" role="tablist" id="myTab">
    <li role="presentation" class="<?= $tabs == 'total' ? 'active' : ''; ?>" id="total" onclick="a('total')"><a
            href="<?php $_SERVER['PHP_SELF']; ?>?tab=total" aria-controls="home" role="tab" data-toggle="tab"><span
                style="color: black;">전체</span></a></li>
    <li role="presentation" class="<?= $tabs == 'm' ? 'active' : ''; ?>" onclick="a('m')" id="me"><a
            href="<?php $_SERVER['PHP_SELF']; ?>?tab=m" aria-controls="profile" role="tab" data-toggle="tab"><span
                style="color: black;">진행</span></a></li>
    <li role="presentation" class="<?= $tabs == 's' ? 'active' : ''; ?>" onclick="a('s')" id="se"><a
            href="<?php $_SERVER['PHP_SELF']; ?>?tab=s" aria-controls="messages" role="tab" data-toggle="tab"><span
                style="color: black;">종료</span></a></li>
    <div style="text-align: right; margin-top: 12px; "><span style="color: darkgreen;">■진행중</span><span style="color: #BABABA;">■종료</span></div>
</ul>

<table class="table table-bordered table-hover">
    <thead>
    <tr>
        <th style="text-align: center; font-size: 12px;">코드</th>
        <th style="text-align: center; font-size: 12px;">이벤트명</th>
        <th style="text-align: center; font-size: 12px;">시작일자</th>
        <th style="text-align: center; font-size: 12px;">종료일자</th>
        <th style="text-align: center; font-size: 12px;">PC</th>
        <th style="text-align: center; font-size: 12px;">MOBILE</th>
        <th style="text-align: center; font-size: 12px;">남자</th>
        <th style="text-align: center; font-size: 12px;">여자</th>
        <th style="text-align: center; font-size: 12px;">비회원</th>
        <th style="text-align: center; font-size: 12px;">합계</th>
    </tr>
    </thead>
    <tbody>
    <?php
    while ($row = sql_fetch_array($result)) {
        $row['pc']=$row['pc']?$row['pc']:'0';
        $row['mo']=$row['mo']?$row['mo']:'0';
       $pcmo= $row['mo']+$row['pc'];
        ?>
        <tr>

            <td style="text-align: center;"><?php echo $row['pr_id']; ?></td>
            <td><button class="btn btn-<?php echo $row['ing']=='진행중'?'success':'default';?> btn-xs" onclick="location='http://209.216.56.107/mall5/adm/shop_admin/event_stats_deteil.php?pr_id=<?php echo $row['pr_id'];?>&tab=<?php echo $tabs;?>&event=<?php echo $event;?>&page=<?php echo $now_page;?>'"><?php echo $row['pr_name']; ?></button></td>
            <td style="text-align: center; font-size: 10px;"><?php echo $row['st_dt']."&nbsp;&nbsp;".$week_array[date('w', strtotime($row['st_dt']))]; ?></td>
            <td style="text-align: center; font-size: 10px;"><?php echo $row['en_dt']==null || $row['en_dt']==''?"기한없음":$row['en_dt']."&nbsp;&nbsp;".$week_array[date('w', strtotime($row['en_dt']))]; ?></td>
            <td style="text-align: right; font-size: 10px;"><?php echo number_format($row['pc']); ?>&nbsp;&nbsp;(<?php echo $pcmo!='0'?round($row['pc']/($pcmo)*100,0):'0'; ?>%)</td>
            <td style="text-align: right; font-size: 10px;"><?php echo number_format($row['mo']); ?>&nbsp;&nbsp;(<?php echo $pcmo!='0'?round($row['mo']/($pcmo)*100,0):0; ?>%)</td>
            <td style="text-align: right; font-size: 10px;"><?php echo number_format($row['f']); ?>&nbsp;&nbsp;(<?php echo round($row['f']/($row['f'] + $row['m'] + $row['rain'])*100,0); ?>%)</td>
            <td style="text-align: right; font-size: 10px;"><?php echo number_format($row['m']); ?>&nbsp;&nbsp;(<?php echo round($row['m']/($row['f'] + $row['m'] + $row['rain'])*100,0); ?>%)</td>
            <td style="text-align: right; font-size: 10px;"><?php echo number_format($row['rain']); ?>&nbsp;&nbsp;(<?php echo round($row['rain']/($row['f'] + $row['m'] + $row['rain'])*100,0); ?>%)</td>
            <td style="text-align: right; font-size: 10px;"><?php echo number_format($row['f'] + $row['m'] + $row['rain']); ?></td>
        </tr>
    <?php } ?>

    </tbody>
    <tfoot>
    <tr>
        <td colspan="11"
            style="text-align: center;">
            <?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); //페이징 다시 작업?>
        </td>
    </tr>
    </tfoot>
</table>


<script>
    function a(aa) {
        if (aa == 'm') {

            location.href = "<?php $_SERVER['PHP_SELF'];?>?tab=m&event=<?php echo $event;?>";
        } else if (aa == 's') {

            location.href = "<?php $_SERVER['PHP_SELF'];?>?tab=s&event=<?php echo $event;?>";
        } else {

            location.href = "<?php $_SERVER['PHP_SELF'];?>?tab=total&event=<?php echo $event;?>";
        }
    }

</script>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>

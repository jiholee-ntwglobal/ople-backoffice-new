<?php
$sub_menu = "400950";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";

$where = '';
if ($_GET['year']) {
    if ($_GET['year'] >= '2016' && $_GET['year'] <= date('Y')) {
        $where .= " and date_format(create_date,'%Y') = '" . trim($_GET['year']) . "' ";
    } else {
        $where .= '';
        $_GET['year'] = '';
    }

}
if ($_GET['month']) {
    if ($_GET['month'] >= '1' && $_GET['month'] <= '12') {
        $where .= " and date_format(create_date,'%c') = '" . trim($_GET['month']) . "' ";
    } else {
        $where .= '';
        $_GET['month'] = '';
    }
}

//페이징
$pagequery = "  select
                 count( distinct date_format(create_date,'%Y-%m-%d')) cnt
                 from payment_request_order
                 where flag ='Y' " . $where . "
                 order by date_format(create_date,'%Y-%m-%d') desc";
$pageresult = sql_query($a = $pagequery);
/*$pageresult = mysqli_query($conn, $pagequery) or die("실패하였습니다." . mysqli_error($conn));*/
$page_rows = sql_fetch_array($pageresult);
$row = $page_rows['cnt'];
$total_list = $row;//페이징=전체컬럼수
$list_page = 31;//보여줄 컬럼
$num_page = 10;//표시할 페이지
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
            $pages .= "<a href=" . $server . "?page=1" . "&" . $search . "> 처음 </a>";
        }
        if ($now_block > 1) {
            $pages .= "<a href=" . $server . "?page=" . $prev_page . "&" . $search . "> 이전 </a>";
        }
        if ($end_page >= $total_page) {
            $end_page = $total_page;
        }
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $now_page) {
                $pages .= "<b> " . $i . " </b>";
            } else {
                $pages .= "<a href=" . $server . "?page=" . $i . "&" . $search . "> " . $i . " </a>";
            }
        }
        if ($now_page >= 1) {
            if ($total_block != $now_block) {
                $pages .= "<a  href=" . $server . "?page=" . $next_page . "&" . $search . "> 다음 </a>";
            }
            if ($now_page != $total_page) {
                $pages .= "<a href=" . $server . "?page=" . $total_page . "&" . $search . "> 마지막 </a>";
            }
        }
    }
    return $pages;
}


$sql_payment_request = "select
                    date_format(create_date,'%Y-%m-%d') days,
                    sum(case when pay_method='kcp-vcnt' then 1 else 0 end) as pay_method_kcp_vcnt,
                    sum(case when pay_method='Authorize' then 1 else 0 end) as pay_method_Authorize_vcnt
                 from payment_request_order
                 where flag ='Y' " . $where . "
                 group by date_format(create_date,'%Y-%m-%d')
                 order by date_format(create_date,'%Y-%m-%d') desc
                 limit " . ($now_page - 1) * $list_page . "," . $list_page;


$sql_query_payment = sql_query($sql_payment_request);

$payment_tr = '';
/*
while($payment_row=sql_fetch_array($sql_query_payment)){
    $payment_tr .="<tr>";
    $payment_tr .= "  <td>" . $payment_row['days'] . "</td>
                      <td>" . $payment_row['pay_method_kcp_vcnt'] . "</td>
                      <td>" . $payment_row['pay_method_Authorize_vcnt'] . "</td>";
    $payment_tr .="</tr>";
    $result=sql_fetch($sql = "select
            order_id,
            rtrim(date_format(create_date,'%H:%i:%s'))times,
            pay_method
            from
            payment_request_order
            where
            date_format(create_date,'%Y-%m-%d') = '".$payment_row['days']."'
            and flag ='Y'");
    $payment_tr .="<tr>";
    $payment_tr .="<td colspan='3' style='text-align: center;'>";
    $pay_methods= '';
    foreach($result as $value){

        $payment_tr .= $value['order_id'].$value['times'].$pay_methods;
    }
    $payment_tr .="</td>";
    $payment_tr .="</tr>";
}*/

$g4[title] = "이상 결제 내역";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
    <body>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <h1 style="text-align: center;">오플 이상 결제시도 리스트 </h1>
    <div align="right">
        <form class="form-inline" onsubmit="return sharch()">
            <div class="form-group">
                <label>년/월 검색</label>
                <select class="form-control" name="year">
                    <option>전체</option>
                    <?php for ($y = 2016; $y <= date('Y'); $y = $y + 1) {
                        if ($y == $_GET['year']) { ?>
                            <option value="<?php echo $y; ?>" selected><?php echo $y; ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php }
                    } ?>
                </select>
                <select class="form-control" name="month">
                    <option>전체</option>
                    <?php for ($m = 1; $m <= 12; $m++) {
                        if ($m == $_GET['month']) { ?>
                            <option value="<?php echo $m; ?>" selected><?php echo $m; ?></option>
                        <?php } else {
                            ?>
                            <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                        <?php }
                    } ?>
                </select>
                <button type="submit" class="btn btn-default">Search</button>
            </div>
        </form>
    </div>

    <div class="panel-body">
        <table class="table table-bordered table-hover table-condensed">
            <thead>
            <tr>
                <th rowspan="2" style="text-align: center;">날짜</th>
                <th colspan="3" style="text-align: center;">이상 주문건 건수</th>
            </tr>
            <tr>
                <th style="text-align: center;">가상계좌</th>
                <th style="text-align: center;">신용카드</th>
                <th style="text-align: center;">합계</th>
            </tr>
            </thead>
            <tbody>
            <?php $i = 1;
            while ($payment_row = sql_fetch_array($sql_query_payment)) { ?>
                <tr onclick="content_view('<?php echo (String)$payment_row['days']; ?>')" style="cursor: pointer;">
                    <td style="text-align: center;"><span
                            style="font-weight: bold; font-size: 14px;"><?php echo $payment_row['days']; ?></span></td>
                    <td style="text-align: center;"><span
                            style="font-weight: bold; font-size: 14px;"><?php echo $payment_row['pay_method_kcp_vcnt']; ?></span>
                    </td>
                    <td style="text-align: center;"><span
                            style="font-weight: bold; font-size: 14px;"><?php echo $payment_row['pay_method_Authorize_vcnt']; ?></span>
                    </td>
                    <td style="text-align: center;"><span
                            style="font-weight: bold; font-size: 14px;"><?php echo (int)$payment_row['pay_method_Authorize_vcnt'] + (int)$payment_row['pay_method_kcp_vcnt']; ?></span>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="<? echo $payment_row['days']; ?>" style="display:none;"></td>
                </tr>
            <? } ?>

            </tbody>
            <tfoot>
            <tr>
                <td colspan="4"
                    style="text-align: center;"><?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); //페이징 다시 작업?></td>
            </tr>
            </tfoot>
        </table>
    </div>

    <script>
        function content_view(v_id) {
            $.ajax({
                type: 'post'
                , url: 'payment_request_cnt_ajax.php'
                , data: 'days=' + v_id
                , success: function (data) {
                    $('.' + v_id).html(data);

                }
            });
            if ($('.' + v_id + ':visible').length > 0) {
                $('.' + v_id).hide();
            } else {
                $('.' + v_id).show();
            }
        }
    </script>
    </body>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

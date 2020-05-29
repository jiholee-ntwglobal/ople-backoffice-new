<?php
$sub_menu = "800500";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");
$_GET['year'] = $_GET['year'] == '' ? '' : $_GET['year'];
$_GET['month'] = $_GET['month'] == '' ? '' : $_GET['month'];
$where = '';

if ($_GET['month'] != '') {
    if ($_GET['month'] >= '1' && $_GET['month'] <= '12') {
        $where .= " and date_format(a.wr_datetime,'%c') = '" . trim($_GET['month']) . "' ";

    }
}
if ($_GET['year'] != '') {
    if ($_GET['year'] >= '2005' && $_GET['year'] <= date('Y')) {
        $where .= " and date_format(a.wr_datetime,'%Y') = '" . trim($_GET['year']) . "' ";

    }
}
$groupby = ($_GET['year'] != '' && $_GET['month'] != '') ? " date_format(wr_datetime, '%Y-%m-%d') " : " date_format(wr_datetime, '%Y-%m') ";
/*if($_GET['year']==''&&$_GET['month']==''){
    $groupby="date_format(wr_datetime, '%Y')";
    $where='';
}*/
if ($where) {
    $sql = "SELECT $groupby a,sum(
          if(
                 wr_num NOT IN
                    (SELECT wr_num
                       FROM g4_write_qa b
                      WHERE     a.wr_num = b.wr_num
                            AND b.wr_is_comment = 0
                            AND b.wr_reply != a.wr_reply)
             AND a.wr_1 != '',
             1,
             0))
          ngoodscnt,
       sum(
          if(
                 wr_num NOT IN
                    (SELECT wr_num
                       FROM g4_write_qa b
                      WHERE     a.wr_num = b.wr_num
                            AND b.wr_is_comment = 0
                            AND b.wr_reply != a.wr_reply)
             AND a.wr_1 = '',
             1,
             0))
          nnocnt,
          sum(
          if(
                 wr_num  IN
                    (SELECT wr_num
                       FROM g4_write_qa b
                      WHERE     a.wr_num = b.wr_num
                            AND b.wr_is_comment = 0
                            AND b.wr_reply != a.wr_reply)
             AND a.wr_1 != '',
             1,
             0))
          ygoodscnt,
       sum(
          if(
                 wr_num  IN
                    (SELECT wr_num
                       FROM g4_write_qa b
                      WHERE     a.wr_num = b.wr_num
                            AND b.wr_is_comment = 0
                            AND b.wr_reply != a.wr_reply)
             AND a.wr_1 = '',
             1,
             0))
          ynocnt

  FROM g4_write_qa a
 where wr_reply = ''
 AND wr_is_comment = 0
 $where
 group by $groupby

ORDER BY a.wr_datetime DESC";
    $resultl = sql_query($sql);

}
$g4[title] = "고객센터 통계";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");

?>
<body>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
</body>
<h1 style="text-align: center;">고객센터 통계 </h1>
<div align="right">
    <form class="form-inline" onsubmit="return sharch()">
        <div class="form-group">
            <label>년/월 검색</label>
            <select class="form-control" name="year">
                <option value="">전체</option>
                <?php for ($y = 2005; $y <= date('Y'); $y = $y + 1) {
                    if ($y == $_GET['year']) { ?>
                        <option value="<?php echo $y; ?>" selected><?php echo $y; ?></option>
                    <?php } else { ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                    <?php }
                } ?>
            </select>
            <select class="form-control" name="month">
                <option value="">전체</option>
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
            <th colspan="3" style="text-align: center;">미답변</th>
            <th colspan="3" style="text-align: center;">답변</th>
            <th colspan="3" style="text-align: center;">총건수</th>
        </tr>
        <tr>
            <th style="text-align: center;">상품</th>
            <th style="text-align: center;">일반</th>
            <th style="text-align: center;">합계</th>
            <th style="text-align: center;">상품</th>
            <th style="text-align: center;">일반</th>
            <th style="text-align: center;">합계</th>
            <th style="text-align: center;">상품문의 합계</th>
            <th style="text-align: center;">일반문의 합계</th>
            <th style="text-align: center;">합계</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (!$resultl) {
            echo "<td colspan='10' style='text-align: center;'>년 월 을 검색해주세요 </td>";
        } else {
            $row_ngoodscnt = '';
            $row_nnocnt = '';
            $row_ygoodscnt = '';
            $row_ynocnt = '';
            while ($row = sql_fetch_array($resultl)) {
                $row_ngoodscnt += $row['ngoodscnt'];
                $row_nnocnt += $row['nnocnt'];
                $row_ygoodscnt += $row['ygoodscnt'];
                $row_ynocnt += $row['ynocnt'];
                ?>
                <tr>
                    <td style="text-align: center;"><?php echo $row['a']; ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['ngoodscnt']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['nnocnt']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['nnocnt'] + $row['ngoodscnt']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['ygoodscnt']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['ynocnt']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['ynocnt'] + $row['ygoodscnt']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['ygoodscnt'] + $row['ngoodscnt']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['ynocnt'] + $row['nnocnt']); ?></td>
                    <td style="text-align: right;"><?php echo number_format($row['ynocnt'] + $row['ygoodscnt'] + $row['nnocnt'] + $row['ngoodscnt']); ?></td>

                </tr>

            <? }
            if ($row_ngoodscnt || $row_nnocnt || $row_ygoodscnt || $row_ynocnt) {
                ?>
                <td style="text-align: center; font-weight: bold;">총 합계</td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_ngoodscnt); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_nnocnt); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_nnocnt + $row_ngoodscnt); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_ygoodscnt); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_ynocnt); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_ygoodscnt + $row_ynocnt); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_ygoodscnt + $row_ngoodscnt); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_ynocnt + $row_nnocnt); ?></td>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($row_nnocnt + $row_ngoodscnt + $row_ygoodscnt + $row_ynocnt); ?></td>
            <? }
        } ?>
        </tbody>
        <tfoot>

        </tfoot>
    </table>
</div>


<?
include_once("$g4[admin_path]/admin.tail.php");
?>

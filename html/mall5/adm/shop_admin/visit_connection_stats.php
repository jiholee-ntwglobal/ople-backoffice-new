<?php
$sub_menu = "800600";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");


//쿼리
$y = trim($_GET['y']) ? trim($_GET['y']) : '';
$m = (int)trim($_GET['m']) ? (int)trim($_GET['m']) : '';
$ms = $m;
$gogo_week= 0;
if ($y >= 2008 && $y <= date('Y') || $y == '') {
    if ($y != '') {
        $sql = " SELECT date_format(vs_date, '%Y') y,
       date_format(vs_date, '%c') m,
       sum(vs_count) cnt
  FROM g4_visit_sum
 WHERE date_format(vs_date, '%Y') = '$y'
GROUP BY date_format(vs_date, '%Y%m') ";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $arr[] = trim($row['cnt']);
            $arrs[] = trim($row['m']);
        }

    }

    if (($y && $m >= 1 && $m <= 12) || ($m == '' && $y == '') || ($m == '' && $y)) {
        if ($y != '' && $m != '') {
            $sqls = "SELECT date_format(vs_date, '%Y') y,date_format(vs_date, '%c') m,
       date_format(vs_date, '%e') d,
       sum(vs_count) cnt
  FROM g4_visit_sum
 WHERE date_format(vs_date, '%Y%c') = '$y$m'
GROUP BY date_format(vs_date, '%Y%m%d')";
            $week_sql = "SELECT DAYOFWEEK(vs_date) AS week_n,
       sum(vs_count) cnt
  FROM g4_visit_sum
 WHERE date_format(vs_date, '%Y%c') = '$y$m'
GROUP BY DAYOFWEEK(vs_date)";
            $week_result = sql_query($week_sql);
            $week_total = 0;
            while ($week_row = sql_fetch_array($week_result)) {
                if ($week_row['cnt']) {
                    $week_tr[$week_row['week_n']] = $week_row['cnt'];
                    $week_total += $week_row['cnt'];
                }
            }
            $results = sql_query($sqls);
            while ($rows = sql_fetch_array($results)) {
                $day[$rows['d']] = $rows['d'];
                $day_cnt[$rows['d']] = $rows['cnt'];
            }
            $m = Str_pad($m, 2, 0, STR_PAD_LEFT);
            (int)$todays = date("Ym"); //지금 년월저장
            (int)$yearr = $y . $m;
            $c = 1;// 테이블에서 일수 찍어주기
            $mktime = mktime(0, 0, 0, $m, 1, $y);// 입력한 년월 진짜 년월로 바꿔주기
            $days = date("t", $mktime); //입력한 년도 입력한 달에  일수
            $start = date("w", $mktime);//입력한 년도 입력한 달에 몇일부터인지
            $dom = array("<span style='color: red;'>일</span>", "월", "화", "수", "목", "금", "<span style='color: blue;'>토</span>",);// 요일을 배열에 담아서 뿌린다.
            $coudom = count($dom);
            $daystart = $days + $start;// for문 돌리기위한
            if ($daystart > 28 && $daystart <= 35) {
                $td = 35;
            } elseif ($daystart > 35) {
                $td = 42;
            } elseif ($daystart == 28) {
                $td = 28;
            }
        }
        if ($y == '' && $m == '') {
            $sql = "select date_format(vs_date, '%Y%m') ym,date_format(vs_date, '%Y') y, date_format(vs_date, '%c') m,sum(vs_count)cnt from g4_visit_sum
GROUP BY date_format(vs_date, '%Y%m')
order by y desc,m asc";
            $result = sql_query($sql);
            while ($ytotal = sql_fetch_array($result)) {
                $td_y[$ytotal['y']][$ytotal['m']] = $ytotal['cnt'];
            }

        }

    } else {
        ?>
        <script>
            history.back();
        </script>

        <?php
    }
    $total_cnt = 0;
    $weeks = 1;
} else {
    ?>
    <script>
        history.back();
    </script>
    <?
}
$g4[title] = "접속 년/월 통계";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");

?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<!--검색-->
<form name="f" class="form-inline text-right" onsubmit="return upcsearch()">
    <div class="form-group">
        <label>날짜</label>
        <select name="y" class="form-control input-sm">
            <option value="">전체</option>
            <?php for ($i = date('Y'); $i >= 2008; $i--) {
                if ($y == $i) { ?>
                    <option selected value="<?php echo $i ?>"><?php echo $i; ?></option>
                <?php } else {
                    ?>
                    <option value="<?php echo $i ?>"><?php echo $i; ?></option>
                <?php }
            } ?>
        </select>
    </div>
    <button type="submit" class="btn btn-default">Search</button>
</form>
<?php if ($y == '' && $m == '') { ?>
    <br><br>
    <h3 style="text-align: center;">2008년 ~ <?php echo date('Y') ?>년</h3>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th></th>
            <th>1월</th>
            <th>2월</th>
            <th>3월</th>
            <th>4월</th>
            <th>5월</th>
            <th>6월</th>
            <th>7월</th>
            <th>8월</th>
            <th>9월</th>
            <th>10월</th>
            <th>11월</th>
            <th>12월</th>
            <th>합계</th>


        </tr>
        </thead>
        <tbody>
        <?php for ($ym = date('Y'); $ym >= 2008; $ym--) {
            $y_to = 0; ?>
            <tr>
                <th style="white-space: nowrap;"><?php echo $ym; ?>년</th>
                <? for ($md = 1; $md <= 12; $md++) {
                    if ($td_y[$ym][$md]) {
                        $y_to += $td_y[$ym][$md] ?>
                        <td style="text-align: right;"><?php echo number_format($td_y[$ym][$md]); ?></td>
                    <?php } else { ?>
                        <td style="text-align: right;">0</td>
                        <?php
                    }
                } ?>
                <td style="text-align: right; font-weight: bold;"><?php echo number_format($y_to); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
<?php } ?>
<?php if ($y != '') { ?>
    <h3 style="text-align: center;"><?php echo $y; ?>년</h3>
    <table class="table table-bordered">
        <tbody>
        <tr>
            <td style="text-align: center; font-weight: bold;">월</td>
            <?php for ($i = 0; $i < count($arrs); $i++) { ?>
                <td style="text-align: center; font-weight: bold;">
                    <button class="btn btn-default btn-sm"
                            onclick="location='http://209.216.56.107/mall5/adm/shop_admin/visit_connection_stats.php?y=<?php echo $y; ?>&m=<?php echo $arrs[$i]; ?>'"><?php echo $arrs[$i]; ?></button>
                </td>
            <?php } ?>
            <td style="text-align: center; font-weight: bold;">합계</td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">접속자수</td>
            <?php
            $sum = 0;
            for ($i = 0; $i < count($arr); $i++) {
                ?>
                <td style="text-align: right;"><?php echo number_format($arr[$i]); ?></td>
                <?php
                $sum += $arr[$i];
            } ?>
            <td style="text-align: right; font-weight: bold;"><?php echo number_format($sum); ?></td>
        </tr>
        </tbody>
    </table>
<?php } ?>
<?php if ($m != '' && $y != '') { ?>
    <h3 style="text-align: center;"><?php echo $ms; ?>월</h3>
    <table class="table table-bordered">
        <tr>
            <td></td>
            <?php for ($p = 0; $p < $coudom; $p++) { ?>
                <td style="text-align: center;"><?php echo $dom[$p]; ?></td>
            <?php } ?>
            <td style="text-align: center;"><span style="color: green; font-weight: bold;">소계</span></td>
        </tr>
        <tr>
            <?php for ($i = 1;$i <= $td;$i++){
            if ($i == 1 || $i == 8 || $i == 15 || $i == 22 || $i == 29 || $i == 36) {
                echo '<td style="text-align: center; font-weight: bold;">'.++$gogo_week.'주차</td>';
            }
            ?>
            <?php if ($i >= $start + 1) { ?>
            <?php if ((int)$y . $m == (int)$todays && (int)$c == (int)date("d")) { ?>
                <td style=" text-align: right;">
                <span style="color:darkorange;">
                    <?php $total_cnt += $day_cnt[$c];
                    echo '<span style="font-weight: bold;">' . $c . '</span><br/>' . number_format($day_cnt[$c++]); ?>
                </span>
                </td>
            <?php }else{ ?>
            <td style="text-align: right;">
                <?php
                if ($daystart >= $i) {
                    if ($c == $day[$c]) {
                        if ($i == 1 || $i == 8 || $i == 15 || $i == 22 || $i == 29 || $i == 36) {
                            $total_cnt += $day_cnt[$c];
                            echo '<span style="color: red;"><span style="font-weight: bold;">' . $c . '</span><br/>' . number_format($day_cnt[$c++]) . '</span>';
                        } elseif ($i == 7 || $i == 14 || $i == 21 || $i == 28 || $i == 35) {
                            $total_cnt += $day_cnt[$c];
                            echo '<span style="color: blue;"><span style="font-weight: bold;">' . $c . '</span><br/>' . number_format($day_cnt[$c++]) . '</span>';
                        } else {
                            $total_cnt += $day_cnt[$c];
                            echo '<span style="font-weight: bold;">' . $c . '</span><br/>' . number_format($day_cnt[$c++]);
                        }
                    } else {
                        if ($i == 1 || $i == 8 || $i == 15 || $i == 22 || $i == 29) {
                            echo '<span style="color: red; font-weight: bold;">' . $c++ . '</span><br/>';
                        } elseif ($i == 7 || $i == 14 || $i == 21 || $i == 28) {
                            echo '<span style="color: blue; font-weight: bold;">' . $c++ . '</span><br/>';
                        } else {
                            echo '<span style="font-weight: bold;">' . $c++ . '</span><br/>';
                        }
                    }
                } ?>

                <?php } ?>
            </td>
            <?php if ($i % 7 == 0){ ?>
            <td style="text-align: right;"><span
                    style="color: green;font-weight: bold;"><?php echo '<span style="">' . $weeks++ . '</span><br/>' . number_format($total_cnt); ?></span>
            </td>
        </tr>
        <tr>
            <?php $total_cnt = 0;
            }
            } else { ?>
                <td></td>
            <?php } ?>
            <?php } ?>
        </tr>
        <tr>
            <?php for ($weeks_tr = 1; $weeks_tr <= 7; $weeks_tr++) {
                if ($weeks_tr == 1) { ?>
                    <td style="font-weight: bold; text-align: center;">합계</td>
                    <td style="text-align: right;color: red; font-weight: bold;"><?php echo $week_tr[$weeks_tr] ? number_format($week_tr[$weeks_tr]) : '0'; ?></td>
                <?php } elseif ($weeks_tr == 7) {
                    ?>
                    <td style="text-align: right; color: blue; font-weight: bold;"><?php echo $week_tr[$weeks_tr] ? number_format($week_tr[$weeks_tr]) : '0'; ?></td>
                <?php } else {
                    ?>
                    <td style="text-align: right; font-weight: bold;"><?php echo $week_tr[$weeks_tr] ? number_format($week_tr[$weeks_tr]) : '0'; ?></td>
                <?php }
            } ?>
            <td style="text-align: right;font-weight: bold; color: green;"><?php echo number_format($week_total); ?></td>
        </tr>
    </table>
<?php } ?>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>

<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-05-18
 * Time: 오후 4:21
 */
$sub_menu = "300100";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

// 상품코드
$_GET['it_id'] = trim($_GET['it_id']) ? sql_safe_query(trim($_GET['it_id'])) : '';

//카테고리2
$sql = "
                      SELECT ca_id
                      FROM yc4_category_item
                      WHERE it_id = '{$_GET['it_id']}'       
				";
$ca_sql = sql_query($sql);
$ca_id_arr_in = '';
while ($row = sql_fetch_array($ca_sql)) {
    if (strlen($row['ca_id']) == 6) {
        $ca_id_arr_in .= ($ca_id_arr_in == '' ? '' : ',') . "'" . substr($row['ca_id'], 0, 2) . "'";
        $ca_id_arr_in .= ($ca_id_arr_in == '' ? '' : ',') . "'" . substr($row['ca_id'], 0, 4) . "'";
        $ca_id_arr_in .= ($ca_id_arr_in == '' ? '' : ',') . "'" . substr($row['ca_id'], 0, 6) . "'";
    } elseif (strlen($row['ca_id']) == 4) {
        $ca_id_arr_in .= ($ca_id_arr_in == '' ? '' : ',') . "'" . substr($row['ca_id'], 0, 2) . "'";
        $ca_id_arr_in .= ($ca_id_arr_in == '' ? '' : ',') . "'" . substr($row['ca_id'], 0, 4) . "'";
    } elseif (strlen($row['ca_id']) == 2) {
        $ca_id_arr_in .= ($ca_id_arr_in == '' ? '' : ',') . "'" . substr($row['ca_id'], 0, 2) . "'";
    }
}
if ($ca_id_arr_in) {
    $sql = "
                      SELECT c.ca_id,
                           c.ca_name,
                           d.name,
                           d.s_id
                      FROM yc4_category_new c
                           LEFT JOIN shop_category s ON s.ca_id = substr(c.ca_id, 1, 2)
                           LEFT JOIN yc4_station d ON s.s_id = d.s_id
                     WHERE c.ca_id IN ( {$ca_id_arr_in} )
                     ORDER BY rpad(c.ca_id, 6, '0') ASC
				";
    $ca_sql = sql_query($sql);
    $it_id_ca_name = array();
    $it_id_sa_name = array();
    $it_id_ca_id = array();
    $ca_arr3 = array();
    while ($row = sql_fetch_array($ca_sql)) {
        $it_id_ca_name[$row['ca_id']] = $row['ca_name'];
        if (strlen($row['ca_id']) == 2) {
            $it_id_ca_id[$row['ca_id']] = array();
            $it_id_sa_name[$row['ca_id']]['s_id'] = $row['s_id'];
            $it_id_sa_name[$row['ca_id']]['name'] = $row['name'];
        }
        if (strlen($row['ca_id']) == 4) {
            $it_id_ca_id[substr($row['ca_id'], 0, 2)][$row['ca_id']] = array();
        }
        if (strlen($row['ca_id']) == 6) {
            $it_id_ca_id[substr($row['ca_id'], 0, 2)][substr($row['ca_id'], 0, 4)][$row['ca_id']] = '';
        }
    }
}
$i = 0;
foreach ($it_id_ca_id as $ca1 => $ca_1) {
    if (count($ca_1) > 0) {
        foreach ($ca_1 as $ca2 => $ca_2) {
            if (count($ca_2) > 0) {
                foreach ($ca_2 as $ca3 => $ca_3) {
                    $str .= "<div class='list_navigation" . ($i > 0 ? " list_navigation_hide" : "") . "'><a target=\"_blank;\" id='global-nav' href='http://ople.com/mall5/?s_id=" . $it_id_sa_name[$ca1]['s_id'] . "' style='" . ($i > 0 ? "background:none;" : "") . "'>" . strtoupper($it_id_sa_name[$ca1]['name']) . "</a>";
                    $str .= "<a target=\"_blank;\" href='http://ople.com/mall5/shop/list.php?ca_id=$ca1'>{$it_id_ca_name[$ca1]}</a>" . "<a target=\"_blank;\" href='http://ople.com/mall5/shop/list.php?ca_id=$ca2'>{$it_id_ca_name[$ca2]}</a>" . "<a target=\"_blank;\" href='http://ople.com/mall5/shop/list.php?ca_id=$ca3'>{$it_id_ca_name[$ca3]}</a>";
                    $str .= "</div>";
                    $i++;
                }
            } else {
                $str .= "<div class='list_navigation" . ($i > 0 ? " list_navigation_hide" : "") . "'><a target=\"_blank;\" id='global-nav' href='http://ople.com/mall5/?s_id=" . $it_id_sa_name[$ca1]['s_id'] . "' style='" . ($i > 0 ? "background:none;" : "") . "'>" . strtoupper($it_id_sa_name[$ca1]['name']) . "</a>";
                $str .= "<a target=\"_blank;\" href='http://ople.com/mall5/shop/list.php?ca_id=$ca1'>{$it_id_ca_name[$ca1]}</a>" . "<a target=\"_blank;\" href='http://ople.com/mall5/shop/list.php?ca_id=$ca2'>{$it_id_ca_name[$ca2]}</a>";
                $str .= "</div>";
                $i++;
            }
        }
    } else {
        $str .= "<div class='list_navigation" . ($i > 0 ? " list_navigation_hide" : "") . "'><a target=\"_blank;\" id='global-nav' href='http://ople.com/mall5/?s_id=" . $it_id_sa_name[$ca1]['s_id'] . "' style='" . ($i > 0 ? "background:none;" : "") . "'>" . strtoupper($it_id_sa_name[$ca1]['name']) . "</a>";
        $str .= "<a target=\"_blank;\" href='http://ople.com/mall5/shop/list.php?ca_id=$ca1'>{$it_id_ca_name[$ca1]}</a>";
        $str .= "</div>";
        $i++;
    }
}


//프로모션
$sql = "
                    SELECT DISTINCT
                           ci.uid,
                           ci.pr_id,
                           ci.pr_ca_id,
                           ci.it_id,
                           ci.icon,
                           ci.sort,
                           ci.create_dt,
                           ci.ip,
                           ci.mb_id,
                           c.pr_ca_name,
                           p.pr_name,
                           i.it_name,
                           i.it_amount,
                           i.it_amount_usd,
                           if(cidc.st_dt IS NULL, p.st_dt, cidc.st_dt) pr_dc_st_dt,
                           if(cidc.en_dt IS NULL,
                              if(p.en_dt IS NULL, p.st_dt, p.en_dt),
                              cidc.en_dt)
                              pr_dc_en_dt,
                           cidc.amount_usd                             AS pr_dc_amount_usd,
                           idc.st_dt                                   AS it_dc_st_dt,
                           idc.en_dt                                   AS it_dc_en_dt,
                           idc.amount_usd                              AS dc_amount_usd
                    FROM yc4_promotion_item    ci
                         LEFT JOIN yc4_item i ON ci.it_id = i.it_id
                         LEFT JOIN yc4_promotion p ON ci.pr_id = p.pr_id
                         LEFT JOIN yc4_promotion_category c
                            ON ci.pr_id = c.pr_id AND ci.pr_ca_id = c.pr_ca_id
                         LEFT JOIN yc4_promotion_item_dc cidc
                            ON ci.it_id = cidc.it_id AND ci.pr_id = cidc.pr_id
                         LEFT JOIN yc4_promotion_item_dc idc
                            ON ci.it_id = idc.it_id AND idc.pr_id IS NULL
                    WHERE     ci.it_id = '{$_GET['it_id']}'
                          and '" . date('Y-m-d') . "'
                              between date_format(ifnull(p.st_dt, '1970-01-01') ,'%Y-%m-%d')
                              and date_format(ifnull(p.en_dt, '9999-12-31'),'%Y-%m-%d')
                    ORDER BY ci.pr_id,
                             ifnull(ci.sort, 999) ASC,
                             ifnull(c.sort, 999) ASC,
                             ci.pr_ca_id
           ";
$pr_item_sql = sql_query($sql);
$pr_item_list = array();
while ($row = sql_fetch_array($pr_item_sql)) {
    $pr_item_list[] = $row;
}

define('bootstrap', true);
$g4['title'] = "상품코드 트래킹";
include_once("$g4[admin_path]/admin.head.php");
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <div class="row">
        <div class="col-lg-12">
            <h4>오플상품코드 트래킹</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 text-right">
            <form class="form-inline">
                <input class="form-control" type="text" placeholder="오플 상품코드" name="it_id"
                       value="<?php echo $_GET['it_id']; ?>">
                <button class="btn btn-primary" type="submit">검색</button>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <br>
        </div>
    </div>
<?php if ($_GET['it_id']) { ?>
    <div class="well well-lg well-white">
        <div class="row">
            <div class="col-lg-12">
                <h5>등록된 카테고리</h5>
            </div>
            <div class="col-lg-12">
                <div class='list_title_wrap' style='margin-top:20px;'>
                    <div class='list_title'><img src="<?= $g4['path'] ?>/images/category/category_title01.gif" width="320" height="40"></div>
                    <?
                    echo $str;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="well well-lg well-white">
        <div class="row">
            <div class="col-lg-12">
                <h5>진행중인 프로모션</h5>
            </div>
            <div class="col-lg-12">
                <table class="table table-hover table-bordered table-condensed table-striped">
                    <thead>
                    <tr>
                        <td class="text-center" rowspan="3">프로모션<br/>(코드)</td>
                        <td class="text-center" rowspan="3">카테고리<br/>(코드)</td>
                        <td class="text-center" rowspan="3">오플상품번호</td>

                        <td class="text-center" rowspan="3" colspan="2">상품명</td>
                        <td class="text-center" rowspan="3">상품가격</td>
                        <td class="text-center" colspan="4">할인정보</td>
                        <td class="text-center" rowspan="3">아이콘</td>
                        <!--                        <td class="text-center" rowspan="3">등록일</td>-->
                    </tr>
                    <tr>
                        <td class="text-center" colspan="2">프로모션</td>
                        <td class="text-center" colspan="2">일반할인</td>
                    </tr>
                    <tr>
                        <td class="text-center">할인금액</td>
                        <td class="text-center">기간</td>
                        <td class="text-center">할인금액</td>
                        <td class="text-center">기간</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pr_item_list as $row) { ?>
                        <tr>
                            <td><a target="_blank;"
                                   href="http://ople.com/mall5/shop/promotion.php?pr_id=<?php echo $row['pr_id'] ?>&preview=1"><?php echo $row['pr_name']; ?>
                                    <br/>코드:<?php echo $row['pr_id'] ?></a></td>
                            <td><?php echo $row['pr_ca_id'] ? $row['pr_ca_name'] . '<br/>코드:' . $row['pr_ca_id'] : ''; ?></td>
                            <td>
                                <a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id']; ?>"><?php echo $row['it_id']; ?></a>
                            </td>
                            <td><?php echo get_it_image($row['it_id'] . '_s', 70, 70, null, null, false, false, false); ?></td>
                            <td><?php echo get_item_name($row['it_name'], 'list'); ?></td>
                            <td>$ <?php echo usd_convert($row['it_amount']); ?><br/>
                                (￦ <?php echo number_format($row['it_amount']); ?>)
                            </td>
                            <td>
                                <?php if ($row['pr_dc_amount_usd']) { ?>
                                    $ <?php echo $row['pr_dc_amount_usd']; ?>
                                    <br/>(￦ <?php echo number_format(round($row['pr_dc_amount_usd'] * $default['de_conv_pay'])); ?>)
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row['pr_dc_st_dt'] || $row['pr_dc_en_dt']) { ?>
                                    <?php echo $row['pr_dc_st_dt']; ?> ~ <?php echo $row['pr_dc_en_dt']; ?>
                                <?php } else { ?>
                                    프로모션 기간 내
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row['it_dc_amount_usd']) { ?>
                                    $ <?php echo $row['it_dc_amount_usd']; ?>
                                    <br/>(￦ <?php echo number_format(round($row['it_dc_amount_usd'] * $default['de_conv_pay'])); ?>)
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row['it_dc_st_dt'] || $row['it_dc_en_dt']) { ?>
                                    <?php echo $row['it_dc_st_dt']; ?> ~ <?php echo $row['it_dc_en_dt']; ?>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row['icon']) { ?>
                                    아이콘 <?php echo $row['icon']; ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
<?php } ?>
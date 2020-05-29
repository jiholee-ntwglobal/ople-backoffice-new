<?php
$sub_menu = "400400";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "주문서관리";
include_once("$g4[admin_path]/admin.head.php");
include $g4['full_path'].'/lib/db_bs.php';

$db = new db();

$where = " where ";
$sql_search = "";

$sql_search  =$where." left(od_id, 6) < 150101  ";
$where  = ' and ';
///$sql_search .= " $where a.opk_fg is null ";
//$where = " and ";


// 김선용 201107 : root 계정일때 카드/포인트 결제건만 출력
if ($member['mb_id'] == 'root') {
    $sql_search .= " $where od_settle_case not in('무통장') ";
    $where = " and ";
}

if ($_GET['od_fg'] == 'opk') {
    $sql_search .= $where . " a.opk_fg = 'Y'";
    $where = "and";
} elseif ($_GET['od_fg'] == 'mobile') {
    $sql_search .= $where . " a.mobile_fg = 'Y'";
} elseif ($_GET['od_fg'] == 'open_market') {
    if ($_GET['open_market'] == 'A') {
        $sql_search .= $where . " a.open_market_fg = 'A'";
    } elseif ($_GET['open_market'] == 'G') {
        $sql_search .= $where . " a.open_market_fg = 'G'";
    } else {
        $sql_search .= $where . " a.open_market_fg is not null";
    }

    $where = "and";
} elseif ($_GET['od_fg'] == 'ople') {
    $sql_search .= $where . " a.opk_fg is null";
    $where = "and";
}

if ($search != "") {
    if ($sel_field != "") {
        // 김선용 200806 : 한글검색시 정확한 매치지원
        //$sql_search .= " $where $sel_field like '%$search%' ";
        // 김선용 200911 :
        if ($sel_field == 'receiptamount1') { // 이하
            $sql_search .= " $where (od_receipt_bank + od_receipt_card + od_receipt_point) > 0 and (od_receipt_bank + od_receipt_card + od_receipt_point) <= $search ";
        } elseif ($sel_field == 'receiptamount2') { // 이상
            $sql_search .= " $where (od_receipt_bank + od_receipt_card + od_receipt_point) > 0 and (od_receipt_bank + od_receipt_card + od_receipt_point) >= $search ";
        } elseif ($sel_field == 'misu') { // 미수금 : 매치
            $sql_search .= " $where ((od_temp_bank+od_temp_card+od_temp_point) - (od_receipt_bank+od_receipt_card+od_receipt_point)) = $search ";
        } elseif ($sel_field == 'mb_id') {
            // 김선용 2014.04 : full-text 컬럼 리-인덱싱 처리
            // od_name, mb_id 컬럼만 해당
            $sql_search .= " $where a.mb_id = '$search' ";
        } elseif ($sel_field == 'od_name') {
            //	$sql_search .= " $where match($sel_field) against('$search') "; // full-text 는 검색어=(앞/뒤 매치) 이므로 불편함을 줄이고자 아래 b-tree 로 대체
            $sql_search .= " $where $sel_field like '$search%' ";
        } else {
            $sql_search .= " $where $sel_field like '%$search%' ";
        }

        $where = " and ";
    }

    if ($save_search != $search) {
        $page = 1;
    }
}

if ($sel_field == "") $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from $g4[yc4_order_table] a
                left join $g4[yc4_cart_table] b on (a.on_uid=b.on_uid)
                $sql_search ";
//left join $g4[member_table] c on a.mb_id = c.mb_id 제거
// 김선용 200805 : 조인 사용으로 전체카운트가 일정레코드 이상일 때 지연시간 문제가 심각하므로 변경
/*
$result = sql_query(" select DISTINCT od_id ".$sql_common);
$total_count = mysql_num_rows($result);
*/
/*
$sql = sql_query("select od_id from {$g4['yc4_order_table']} a $sql_search");
$total_count = mysql_num_rows($sql);
*/

$sql =$db->ople_backup->query("select count(*) as cnt from {$g4['yc4_order_table']} a $sql_search")->fetch_array();

$total_count = $sql['cnt'];

//echo "select od_id from {$g4['yc4_order_table']} $sql_search<br/>";

$rows = $config[cf_page_rows];
$total_page = ceil($total_count / $rows);  // 전체 페이지 계산

if ($page == "") {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
//,c.mb_memo 제거

$result = $db->ople_backup->query("
        select a.*, " . _MISU_QUERY_ . "
           $sql_common
           group by a.od_id
		   /*$having_search*/
           order by $sort1 $sort2
           limit $from_record, $rows ");

//echo "<pre>";
//echo $sql;
//echo "</pre>";
//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
// 김선용 200805 : sel_ca_id - 쓰레기 코드
//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&save_search=$search";
$qstr1 = "sel_field=$sel_field&search=$search&save_search=$search";

$qstr = $qstr2 = $qstr3 = $_GET;
unset($qstr['page'], $qstr['save_search'], $qstr['x'], $qstr['y'], $qstr2['od_fg'], $qstr2['open_market']);
$qstr['save_search'] = $search;
$qstr = http_build_query($qstr);
$qstr2 = http_build_query($qstr2);
$qstr3 = http_build_query($qstr3);


//$qstr = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";

# 오늘 날짜 주문서 갯수 #
?>
    <style>
        .list_tab {
            list-style-type: none;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .list_tab > li {
            float: left;
            border: 1px solid #DDDDDD;
            padding: 5px;
        }

        .list_tab > li.active {
            font-weight: bold;
        }

        .mobile {
            background-color: #fff000;
        }
        .opk {
            background-color: lightblue;
        }
    </style>
<?php if ($member['mb_id'] != 'csteam') { ?>
    현재 환율  <?php echo $default['de_conv_pay'] ?>
<?php } ?>
    <fieldset style="margin:0; padding:5px 5px 5px 5px; width:100%; border:4px solid #5A6973;">
        <legend style="color:blue;">검색안내</legend>
        <b>※ 회원ID, 주문자명으로 검색시에는 빠른처리를 위해서 앞에서부터 일치하는 순으로 검색됩니다. (중간포함 검색어 지원안함)</B>
    </fieldset>

    <table width=100% cellpadding=4 cellspacing=0>
        <form name=frmorderlist>
            <input type=hidden name=sort1 value="<? echo $sort1 ?>">
            <input type=hidden name=sort2 value="<? echo $sort2 ?>">
            <input type=hidden name=page value="<? echo $page ?>">
            <tr>
                <td width=20%><a href='<?= $_SERVER[PHP_SELF] ?>'>처음</a></td>
                <td width=60% align=center>
                    <select name=sel_field>
                        <option value='od_id'>주문번호</option>
                        <option value='mb_id'>회원 ID</option>
                        <option value='od_name'>주문자</option>
                        <option value='od_tel'>주문자전화</option>
                        <option value='od_hp'>주문자핸드폰</option>
                        <option value='od_b_name'>받는분</option>
                        <option value='od_b_tel'>받는분전화</option>
                        <option value='od_b_hp'>받는분핸드폰</option>
                        <option value='od_deposit_name'>입금자</option>
                        <option value='od_invoice'>운송장번호</option>
                        <option value="misu">미수금</option>
                        <option value="receiptamount1">입금액↓</option>
                        <option value="receiptamount2">입금액↑</option>
                    </select>
                    <input type=hidden name=save_search value='<?= $search ?>'>
                    <input type=hidden name=od_fg value='<?= $od_fg ?>'>
                    <input type=text name=search value='<? echo $search ?>' autocomplete="off">
                    <input type=image src='<?= $g4[admin_path] ?>/img/btn_search.gif' align=absmiddle>
                </td>
                <td width=20% align=right>건수 : <? echo number_format($total_count) ?>&nbsp;</td>
            </tr>
    </table>

    <ul class="list_tab">
        <li class="<?php echo !$_GET['od_fg'] ? "active" : "" ?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $qstr2; ?>">전체</a></li>
        <li class="<?php echo $_GET['od_fg'] == 'ople' ? "active" : "" ?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?od_fg=ople&<?php echo $qstr2; ?>">오플주문서</a></li>
        <li class="<?php echo $_GET['od_fg'] == 'mobile' ? "active" : "" ?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?od_fg=mobile&<?php echo $qstr2; ?>">모바일 주문서</a></li>
        <li class="<?php echo $_GET['od_fg'] == 'opk' ? "active" : "" ?>"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?od_fg=opk&<?php echo $qstr2; ?>">오플코리아 주문서</a></li>
        <!--<li class="<?php /*echo $_GET['od_fg'] == 'open_market' && !$_GET['open_market'] ? "active":""*/ ?>"><a href="<?php /*echo $_SERVER['PHP_SELF'];*/ ?>?od_fg=open_market&<?php /*echo $qstr2;*/ ?>">오픈마켓 주문서</a></li>
    <li class="<?php /*echo $_GET['od_fg'] == 'open_market' && $_GET['open_market'] == 'A' ? "active":""*/ ?>"><a href="<?php /*echo $_SERVER['PHP_SELF'];*/ ?>?od_fg=open_market&open_market=A&<?php /*echo $qstr2;*/ ?>">옥션(오픈마켓)</a></li>
    <li class="<?php /*echo $_GET['od_fg'] == 'open_market' && $_GET['open_market'] == 'G' ? "active":""*/ ?>"><a href="<?php /*echo $_SERVER['PHP_SELF'];*/ ?>?od_fg=open_market&open_market=G&<?php /*echo $qstr2;*/ ?>">G마켓(오픈마켓)</a></li>-->
    </ul>
    <table width=100% cellpadding=0 cellspacing=0>
        <col width=60>
        <col width=''>
        <col width=70>
        <col width=70>
        <col width=70>
        <col width=60>
        <col width=60>
        <col width=70>
        <col width=60>
        <col width=70>
        <col width=60>
        <col width=55>
        <tr>
            <td colspan=12 height=2 bgcolor=#0E87F9></td>
        </tr>
        <tr align=center class=ht>
            <td><a href='<?= title_sort("od_id", 1) . "&$qstr1"; ?>'>주문번호</a></td>
            <td><a href='<?= title_sort("od_name") . "&$qstr1"; ?>'>주문자</a></td>
            <td><a href='<? echo title_sort("mb_id") . "&$qstr1"; ?>'>회원ID</a></td>
            <td><a href='<?= title_sort("itemcount", 1) . "&$qstr1"; ?>'>건수</a> <span title='회원별 누적 건수'>(누적)</span></td>
            <td><a href='<?= title_sort("orderamount", 1) . "&$qstr1"; ?>'><FONT COLOR="1275D3">주문합계</a></FONT></td>
            <td><a href='<?= title_sort("ordercancel", 1) . "&$qstr1"; ?>'>주문취소</a></td>
            <td><a href='<?= title_sort("od_dc_amount", 1) . "&$qstr1"; ?>'>DC</a></td>
            <td><a href='<?= title_sort("receiptamount") . "&$qstr1"; ?>'><FONT COLOR="1275D3">입금합계</font></a></td>
            <td><a href='<?= title_sort("receiptcancel", 1) . "&$qstr1"; ?>'>입금취소</a></td>
            <td><a href='<?= title_sort("misu", 1) . "&$qstr1"; ?>'><font color='#FF6600'>미수금</font></a></td>
            <? if ($member['mb_id'] != 'root') { // 김선용 201107 : root 계정일때 카드/포인트 결제건만 출력 ?>
                <td>결제수단</td>
            <? } ?>
            <td>수정</td>
        </tr>
        <tr>
            <td colspan=12 height=1 bgcolor=#CCCCCC></td>
        </tr>

        <?
        $tot_itemcnt = 0;
        $tot_orderamount = 0;
        $tot_ordercancel = 0;
        $tot_dc_amount = 0;
        $tot_receiptamount = 0;
        $tot_receiptcancel = 0;
        $tot_misu = 0;
        for ($i = 0; $row = $result->fetch_array(); $i++) {
            // 결제 수단
            $s_receipt_way = $s_br = "";
            if ($row[od_settle_case]) {
                $s_receipt_way = $row[od_settle_case];
                $s_br = '<br/>';
            } else {
                if ($row[od_temp_bank] > 0 || $row[od_receipt_bank] > 0) {
                    //$s_receipt_way = "무통장입금";
                    $s_receipt_way = cut_str($row[od_bank_account], 8, "");
                    $s_br = "<br>";
                }

                if ($row[od_temp_card] > 0 || $row[od_receipt_card] > 0) {
                    // 미수금이 없고 카드결제를 하지 않았다면 카드결제를 선택후 무통장 입금한 경우임
                    if ($row[misu] <= 0 && $row[od_receipt_card] == 0)
                        ; // 화면 출력하지 않음
                    else {
                        $s_receipt_way .= $s_br . "카드";
                        if ($row[od_receipt_card] == 0)
                            $s_receipt_way .= "<span class=small><span class=point style='font-size:8pt;'>(미승인)</span></span>";
                        $s_br = "<br>";
                    }
                }
            }

            if ($row[od_receipt_point] > 0)
                $s_receipt_way .= $s_br . "포인트";

            // 김선용 201207 : 사은품
            if ($row['od_gift_id'] != '')
                $s_receipt_way .= "<br/><span style='color:blue;'>사은품</span>";

            $s_mod = icon("수정", "./orderform2yearago.php?od_id=$row[od_id]&$qstr3");


            $mb_nick = get_sideview($row[mb_id], $row[od_name], $row[od_email], '');

            $tot_cnt = "";
            if ($row[mb_id]) {
                $sql2 = $db->ople_backup->query(" select count(*) as cnt from $g4[yc4_order_table] where mb_id = '$row[mb_id]' ");
                $row2 = $sql2->fetch_array();
                $tot_cnt = "($row2[cnt])";
            }

            // 김선용 201107 : root 계정일때 카드/포인트 결제건만 출력
            if ($member['mb_id'] == 'root') {
                $od_id_ip = substr(preg_replace('/[^0-9]/', '', $row['od_ip']), 0, 5);
            } else {
                $od_id_ip = "";
            }
            if ($row['ihappy_fg']) {
                $ihappy_msg = "<b style='color:#ff0000;'>IH</b>";
            } else {
                $ihappy_msg = '';
            }
            if ($row['open_market_fg']) {
                $ihappy_msg = "<b style='color:#ff0000;'>" . $row['open_market_fg'] . "</b>";
            }

            $mobile_class = '';
            if ($row['mobile_fg'] == 'Y') {
                $mobile_class = ' mobile';
            } elseif ($row['opk_fg']) {
                $mobile_class = 'opk';
            }

            $list = $i % 2;
            echo "
    <tr class='list$list $mobile_class ht'>
        <td align=center title='주문일시 : $row[od_time]'>" . $ihappy_msg . "<a href='http://209.216.56.107/mall5/shop/orderinquiryview2yearago.php?od_id=$row[od_id]&on_uid=$row[on_uid]' target='_blank'>$row[od_id]</a>{$od_id_ip}</td>
        <!-- <td align=center><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=od_name&search=$row[od_name]'><span title='$od_deposit_name'>" . cut_str($row[od_name], 8, "") . "</span></a></td> -->
        <td align=center>$mb_nick</td>
        <td align=center><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=mb_id&search=$row[mb_id]' title=\"" . htmlspecialchars($row['mb_memo']) . "\">$row[mb_id]</a></td>
        <td align=center>{$row[itemcount]}건 $tot_cnt</td>
        <td align=right><FONT COLOR='#1275D3'>" . number_format($row[orderamount]) . "</font></td>
        <td align=right>" . number_format($row[ordercancel]) . "</td>
        <td align=right>" . number_format($row[od_dc_amount]) . "</td>
        <td align=right><FONT COLOR='#1275D3'>" . number_format($row[receiptamount]) . "</font></td>
        <td align=right>" . number_format($row[receiptcancel]) . "</td>
        <td align=right><FONT COLOR='#FF6600'>" . number_format($row[misu]) . "</FONT></td>";
            // 김선용 201107 : root 계정일때 카드/포인트 결제건만 출력
            if ($member['mb_id'] != 'root') {
                echo "<td align=center>$s_receipt_way</td>";
            }
            echo "<td align=center>$s_mod </a></td>
    </tr>";

            $tot_itemcount += $row[itemcount];
            $tot_orderamount += $row[orderamount];
            $tot_ordercancel += $row[ordercancel];
            $tot_dc_amount += $row[od_dc_amount];
            $tot_receiptamount += $row[receiptamount];
            $tot_receiptcancel += $row[receiptcancel];
            $tot_misu += $row[misu];
        }
        mysql_free_result($result);
        if ($i == 0)
            echo "<tr><td colspan=12 align=center height=100 bgcolor='#FFFFFF'><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
        ?>
        </form>
        <tr>
            <td colspan=12 bgcolor='#CCCCCC'></td>
        </tr>
        <tr class=ht>
            <td colspan=3 align=center>합 계</td>
            <td align=center><?= (int)$tot_itemcount ?>건</td>
            <td align=right><FONT COLOR='#1275D3'><?= number_format($tot_orderamount) ?></FONT></td>
            <td align=right><?= number_format($tot_ordercancel) ?></td>
            <td align=right><?= number_format($tot_dc_amount) ?></td>
            <td align=right><FONT COLOR='#1275D3'><?= number_format($tot_receiptamount) ?></FONT></td>
            <td align=right><?= number_format($tot_receiptcancel) ?></td>
            <td align=right><FONT COLOR='#FF6600'><?= number_format($tot_misu) ?></FONT></td>
            <td colspan=2></td>
        </tr>
        <tr>
            <td colspan=12 bgcolor='#CCCCCC'></td>
        </tr>
    </table>

    <table width=100%>
        <tr>
            <td width=50%>&nbsp;</td>
            <td width=50% align=right><?= get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page="); ?></td>
        </tr>
    </table>

    <font color=crimson>주의)</font> 주문번호를 클릭하여 나오는 주문상세내역의 주소를 외부에서 조회가 가능한곳에 올리지 마십시오.

    <script language="JavaScript">
        var f = document.frmorderlist;
        f.sel_field.value = '<? echo $sel_field ?>';
        $(document).ready(function () {
            $("input[name=search]").focus();
        });
    </script>

<?php

include_once("$g4[admin_path]/admin.tail.php");

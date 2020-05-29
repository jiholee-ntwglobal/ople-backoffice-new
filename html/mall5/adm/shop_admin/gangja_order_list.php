<?php
$sub_menu = "400124";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "강자닷컴 주문서관리";
include_once("$g4[admin_path]/admin.head.php");

$where = " where ";
$sql_search = "";

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
            $sql_search .= " $where a.$sel_field like '$search%' ";
        } else {
            $sql_search .= " $where $sel_field like '$search' ";
        }

        $where = " and ";
    }

    if ($save_search != $search) {
        $page = 1;
    }
}

if ($sel_field == "") $sel_field = "a.od_id";
if ($sort1 == "") $sort1 = "a.od_id";
if ($sort2 == "") $sort2 = "desc";

$sql_common = " from $g4[yc4_order_table] a
                left join $g4[yc4_cart_table] b on (a.on_uid=b.on_uid)
                inner join gangja_uid e on a.od_id = e.od_id
				left join $g4[member_table] c on a.mb_id = c.mb_id
                $sql_search ";


$sql = sql_fetch("select count(distinct a.od_id) as cnt from {$g4['yc4_order_table']} a inner join gangja_uid e on a.od_id = e.od_id $sql_search ");
$total_count = $sql['cnt'];



$rows = $config[cf_page_rows];
$total_page = ceil($total_count / $rows);  // 전체 페이지 계산

if ($page == "") {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select a.*,c.mb_memo, " . _MISU_QUERY_ . "
           $sql_common
           group by a.od_id
		   /*$having_search*/
           order by $sort1 $sort2
           limit $from_record, $rows ";


$result = sql_query($sql);
//echo $sql;


$qstr1 = "sel_field=$sel_field&search=$search&save_search=$search";

$qstr = $qstr2 = $qstr3 = $_GET;
unset($qstr['page'], $qstr['save_search'], $qstr['x'], $qstr['y'], $qstr2['od_fg'], $qstr2['open_market']);
$qstr['save_search'] = $search;
$qstr = http_build_query($qstr);
$qstr2 = http_build_query($qstr2);
$qstr3 = http_build_query($qstr3);


//$qstr = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";

define('bootstrap', true);
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <h4>강자 닷컴 주문서</h4>
    <br>
    <table width=100% cellpadding=4 cellspacing=0>
        <form name=frmorderlist>
            <input type=hidden name=sort1 value="<? echo $sort1 ?>">
            <input type=hidden name=sort2 value="<? echo $sort2 ?>">
            <input type=hidden name=page value="<? echo $page ?>">
            <tr>
                <td width=20%><a href='./gangja_mapping_list.php'>강자닷컴 매핑 리스트</a></td>
                <td width=60% align=center>
                    <select name=sel_field>
                        <option value='a.od_id'>주문번호</option>
                        <option value='mb_id'>회원 ID</option>
                        <option value='od_name'>주문자</option>
                        <option value='od_tel'>주문자전화</option>
                        <option value='od_hp'>주문자핸드폰</option>
                        <option value='od_b_name'>받는분</option>
                        <option value='od_b_tel'>받는분전화</option>
                        <option value='od_b_hp'>받는분핸드폰</option>
                        <option value='od_deposit_name'>입금자</option>
                        <option value='od_invoice'>운송장번호</option>
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
    <table width=100% cellpadding=0 cellspacing=0>
        <col width=60>
        <col width=70>
        <col width=50>
        <col width=70>
        <col width=70>
        <col width=60>
        <col width=60>
        <col width=70>
        <col width=60>
        <col width=70>
        <col width=60>


        <tr>
            <td colspan=12 height=2 bgcolor=#0E87F9></td>
        </tr>
        <tr align=center class=ht>
            <td><a href='<?= title_sort("od_id", 1) . "&$qstr1"; ?>'>주문번호</a></td>
            <td><a href='<?= title_sort("od_name") . "&$qstr1"; ?>'>주문자</a></td>
            <td><a href='<?= title_sort("od_b_name") . "&$qstr1"; ?>'>받는분</a></td>
            <td><a href='<? echo title_sort("mb_id") . "&$qstr1"; ?>'>회원ID</a></td>

            <td><a href='<?= title_sort("od_temp_bank", 1) . "&$qstr1"; ?>'><FONT COLOR="1275D3">주문합계</a></FONT></td>
            <td><a href='<?= title_sort("ordercancel", 1) . "&$qstr1"; ?>'>주문취소</a></td>
            <td><a href='<?= title_sort("od_dc_amount", 1) . "&$qstr1"; ?>'>DC</a></td>
            <td><a href='<?= title_sort("receiptamount") . "&$qstr1"; ?>'><FONT COLOR="1275D3">입금합계</font></a></td>
            <td><a href='<?= title_sort("receiptcancel", 1) . "&$qstr1"; ?>'>입금취소</a></td>
            <? if ($member['mb_id'] != 'root') { // 김선용 201107 : root 계정일때 카드/포인트 결제건만 출력 ?>
                <td>결제수단</td>
            <? } ?>
            <td><a href="gangja_order_create.php" style="color: red">주문서 생성</a></td>
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
        for ($i = 0; $row = mysql_fetch_array($result); $i++) {
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

            $s_mod = icon("수정", "./orderform.php?od_id=$row[od_id]&$qstr3");
           // $s_del = icon("삭제", "javascript:del('./orderdelete.php?od_id=$row[od_id]&on_uid=$row[on_uid]&mb_id=$row[mb_id]&$qstr3');");

            $mb_nick = get_sideview($row[mb_id], $row[od_name], $row[od_email], '');

            $tot_cnt = "";
            if ($row[mb_id]) {
                $sql2 = " select count(*) as cnt from $g4[yc4_order_table] where mb_id = '$row[mb_id]' ";
                $row2 = sql_fetch($sql2);
                $tot_cnt = "($row2[cnt])";
            }

            // 김선용 201107 : root 계정일때 카드/포인트 결제건만 출력
            if ($member['mb_id'] == 'root') {
                $od_id_ip = substr(preg_replace('/[^0-9]/', '', $row['od_ip']), 0, 5);
            } else {
                $od_id_ip = "";
            }
            if ($row['ihappy_fg']=='Y') {
                $ihappy_msg = "<b style='color:#ff0000;'>IH</b>";
            } elseif($row['ihappy_fg']=='k') {
                $ihappy_msg = "<b style='color:#ff0000;'>KB_IH</b>";
            }else{
                $ihappy_msg ='';
            }
            if ($row['open_market_fg']) {
                $ihappy_msg = "<b style='color:#ff0000;'>" . $row['open_market_fg'] . "</b>";
            }

            $mobile_class = '';

            $list = $i % 2;
            echo "
    <tr class='list$list $mobile_class ht'>
        <td align=center title='주문일시 : $row[od_time]'>" . $ihappy_msg . "$row[od_id]{$od_id_ip}</td>
        <!-- <td align=center><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=od_name&search=$row[od_name]'><span title='$od_deposit_name'>" . cut_str($row[od_name], 8, "") . "</span></a></td> -->
        <td align=center>$mb_nick</td>
        <td align=center>$row[od_b_name]</td>
        <td align=center><a href='$_SERVER[PHP_SELF]?sort1=$sort1&sort2=$sort2&sel_field=mb_id&search=$row[mb_id]' title=\"" . htmlspecialchars($row['mb_memo']) . "\">$row[mb_id]</a></td>
       
        <td align=right><FONT COLOR='#1275D3'>" . number_format($row[od_temp_bank]) . "</font></td>
        <td align=right>" . number_format($row[ordercancel]) . "</td>
        <td align=right>" . number_format($row[od_dc_amount]) . "</td>
        <td align=right><FONT COLOR='#1275D3'>" . number_format($row[receiptamount]) . "</font></td>
        <td align=right>" . number_format($row[receiptcancel]) . "</td>
        ";
            // 김선용 201107 : root 계정일때 카드/포인트 결제건만 출력
            if ($member['mb_id'] != 'root') {
                echo "<td align=center>$s_receipt_way</td>";
            }
            echo "<td align=center>$s_mod </a></td>
    </tr>";

            $tot_orderamount += $row[od_temp_bank];
            $tot_ordercancel += $row[ordercancel];
            $tot_dc_amount += $row[od_dc_amount];
            $tot_receiptamount += $row[receiptamount];
            $tot_receiptcancel += $row[receiptcancel];

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
            <td colspan=4 align=center>합 계</td>

            <td align=right><FONT COLOR='#1275D3'><?= number_format($tot_orderamount) ?></FONT></td>
            <td align=right><?= number_format($tot_ordercancel) ?></td>
            <td align=right><?= number_format($tot_dc_amount) ?></td>
            <td align=right><FONT COLOR='#1275D3'><?= number_format($tot_receiptamount) ?></FONT></td>
            <td align=right><?= number_format($tot_receiptcancel) ?></td>

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

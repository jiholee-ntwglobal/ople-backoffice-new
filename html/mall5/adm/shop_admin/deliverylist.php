<?
$sub_menu = "400500";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "배송일괄처리";
include_once ("$g4[admin_path]/admin.head.php");

//sql_query(" update $g4[yc4_cart_table] set ct_status = '완료' where ct_status = '배송' ");

// 배송회사리스트 ---------------------------------------------
$delivery_options = "";
$sql = " select * from $g4[yc4_delivery_table] order by dl_order ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    $delivery_options .= "<option value='$row[dl_id]'>$row[dl_company]</option>";
}
// 배송회사리스트 end ---------------------------------------------

$where = " where ";
$sql_search = "";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
    }
}
/* 김선용 200805 : 불필요한 코드
if ($sel_ca_id != "") {
    $sql_search .= " $where ca_id like '$sel_ca_id%' ";
}
*/

if ($sel_field == "")  $sel_field = "od_id";

$sql_common = " from $g4[yc4_order_table] a
                left outer join $g4[yc4_cart_table] b on (a.on_uid=b.on_uid)
                $sql_search ";

// 테이블의 전체 레코드수만 얻음
// 김선용 200805 : 조인 사용으로 전체카운트가 일정레코드 이상일 때 지연시간 문제가 심각하므로 변경
/*
$sql = " select a.od_id " . $sql_common . " group by a.od_id ";
$result = sql_query($sql);
$total_count = @mysql_num_rows($result);
*/
$sql = sql_query("select od_id from {$g4['yc4_order_table']} $sql_search");
$total_count = mysql_num_rows($sql);


$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sort1) {
    $sort1 = "a.od_id";
}

if (!$sort2) {
    $sort2 = "desc";
}

if ($sort2 == "desc") {
    $unsort2 == "asc";
} else {
    $unsort2 == "desc";
}
// 김선용 200805 : sel_ca_id - 쓰레기 코드
//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
$qstr1 = "sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>
<form name=flist autocomplete='off' style="margin:0px;">
<input type=hidden name=page value="<?=$page?>">
<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=20%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>
        <select name=sel_field>
            <option value='od_id'>주문번호
			<option value='mb_id'>회원ID
            <option value='od_name'>주문자
            <option value='od_invoice'>운송장번호
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo $search ?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>


<form name=fdeliverylistupate method=post action="./deliverylistupdate.php" autocomplete='off' style="margin:0px;">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search     value="<? echo $search ?>">
<input type=hidden name=page       value="<? echo $page ?>">
<input type=hidden name=sort1      value="<? echo $sort1 ?>">
<input type=hidden name=sort2      value="<? echo $sort2 ?>">
<table cellpadding=0 cellspacing=0 width=100% border=0>
<colgroup width=70>
<colgroup width=100>
<colgroup width=80>
<colgroup width=80>
<colgroup width=80>
<colgroup width=100>
<colgroup width=120>
<colgroup width=''>
<colgroup width=100>
<tr><td colspan=9 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td><a href='<? echo title_sort("od_id",1) . "&$qstr1"; ?>'>주문번호</a></td>
    <td><a href='<? echo title_sort("od_name") . "&$qstr1"; ?>'>주문자</a></td>
    <td><a href='<? echo title_sort("orderamount",1) . "&$qstr1"; ?>'>주문액</a></td>
    <td><a href='<? echo title_sort("receiptamount",1) . "&$qstr1"; ?>'>입금액</a></td>
    <td><a href='<? echo title_sort("misu",1) . "&$qstr1"; ?>'>미수금</a></td>
    <td><a href='<? echo title_sort("od_hope_date",1) . "&$qstr1"; ?>'>희망배송일</a></td>
    <td><a href='<? echo title_sort("od_invoice_time") . "&$qstr1"; ?>'>배송일시</a></td>
    <td>배송회사</td>
    <td><a href='<? echo title_sort("od_invoice", 1) . "&$qstr1"; ?>'>운송장번호</a></td>
</tr>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
<?
$sql  = " select a.*, "._MISU_QUERY_."
          $sql_common
          group by a.od_id
          order by $sort1 $sort2/* 김선용 심각한 트래픽으로 미사용, a.od_invoice asc*/
          limit $from_record, $config[cf_page_rows] ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $invoice_time = $g4[time_ymdhis];
    if (!is_null_time($row[od_invoice_time]))
        $invoice_time = $row[od_invoice_time];

	// 김선용 200805 : 불필요한 쿼리
    //$sql1 = " select * from $g4[member_table] where mb_id = '$row[mb_id]' ";
    //$row1 = sql_fetch($sql1);
    $name = get_sideview($row[mb_id], $row[od_name], $row[od_email]);//, $row[mb_homepage]);

    if ($default[de_hope_date_use])
        $hope_date = substr($row[od_hope_date],2,8)." (".get_yoil($row[od_hope_date]).")";
    else
        $hope_date = "<span title='사용안함'>-</span>";

	$disabled = ($row['od_ship'] == '1' ? 'disabled' : '');

    $list = $i%2;
    echo "
    <input type='hidden' name='od_id[$i]' value='$row[od_id]'>
    <input type='hidden' name='on_uid[$i]' value='$row[on_uid]'>
    <input type='hidden' name='od_ship[$i]' value='$row[od_ship]' /> <!-- // 김선용 201211 : -->
    <tr class='list$list center ht'>
        <td><a href='./orderform.php?od_id=$row[od_id]'>$row[od_id]</a></td>
		<td>$name</td>
        <td align=right>".display_amount($row[orderamount])."&nbsp;</td>
        <td align=right>".display_amount($row[receiptamount])."&nbsp;</td>
        <td align=right>".display_amount($row[misu])."&nbsp;</td>
        <td>$hope_date</td>
        <td><input type='text' name='od_invoice_time[$i]' class=ed size=18 maxlength=19 value='$invoice_time' {$disabled}></td>
        <td>
            <select name=dl_id[$i] {$disabled}>
            <option value=''>--------
            $delivery_options
            </select>
        </td>
        <!-- 값이 바뀌었는지 비교하기 위하여 저장 -->
        <input type='hidden' name='save_dl_id[$i]' value='$row[dl_id]'>
        <input type='hidden' name='save_od_invoice[$i]' value='$row[od_invoice]'>
        <td><input type='text' name='od_invoice[$i]' class=ed size=10 value='$row[od_invoice]' {$disabled}></td>
	</tr>";


	// 김선용 201211 :
	if($row['od_ship'] == '1'){
		$os_sql = sql_query("select * from {$g4['yc4_os_table']} where on_uid='{$row['on_uid']}' and od_id='{$row['od_id']}' order by os_pid ");
		for($k=0; $os=sql_fetch_array($os_sql); $k++)
		{
		    if (!is_null_time($os['os_invoice_time'])) $invoice_time = $os['os_invoice_time'];
			echo "<input type=hidden name='os_pid[$i][$k]' value='{$os['os_pid']}' />";
			echo "<input type='hidden' name='os_save_dl_id[$i][$k]' value='{$os['os_dl_id']}' />";
			echo "<input type='hidden' name='os_save_invoice[$i][$k]' value='{$os['os_invoice']}' />";
			echo "<tr><td colspan=10>";
			echo "
				└ 받는사람 : {$os['os_name']}&nbsp;&nbsp;
				<input type='text' name='os_invoice_time[$i][$k]' class=ed size=18 maxlength=19 value='$invoice_time'>
				<select name='os_dl_id[$i][$k]'>
					<option value=''>--------</option>
					$delivery_options
				</select>
				<input type='text' name='os_invoice[$i][$k]' class=ed size=15 value='$os[os_invoice]'>
			";
			echo "</td></tr>";
			if($os['os_dl_id']){
				echo "<script type='text/javascript'> document.fdeliverylistupate.elements['os_dl_id[$i][$k]'].value = '$os[os_dl_id]'; </script>";
			}
		}
	}

    if ($row[dl_id]) {
        echo "<script> document.fdeliverylistupate.elements['dl_id[$i]'].value = '$row[dl_id]'; </script>";
    }
}
if ($i == 0)
    echo "<tr><td colspan=20 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 한건도 없습니다.</span></td></tr>";
?>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr bgcolor=#ffffff>
    <td width=50%>
        <table>
        <tr>
            <td><input type=checkbox name='od_send_mail' value='1' checked> 메일발송&nbsp;</td>
            <td><input type=checkbox name='send_sms' value='1' checked> SMS&nbsp;</td>
            <td><input type=submit class=btn1 accesskey='s' value='일괄수정'></td>
        </tr>
        </table>
    </td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>
</form>

<br>
* 주문액은 취소, 반품, 품절, DC가 포함된 금액이 아닙니다.<br>
* 입금액은 환불, 승인취소가 포함된 금액이 아닙니다.<br>
* 배송일시, 배송회사는 입력의 편의성을 위하여 기본값으로 설정되어 있습니다. 운송장번호만 없는것이 미배송 주문자료입니다.

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

<?
$sub_menu = "400800";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "전자결제내역";
include_once ("$g4[admin_path]/admin.head.php");


$where = " where ";
$sql_search = "";
if ($search != "")
{
	if ($sel_field != "")
    {
    	$sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
		$count_search = preg_replace("/a\./", "", $sql_search); // 김선용 200805 : 전체카운트용 쿼리
    }
}

if ($sel_field == "")  $sel_field = "od_id";
if ($sort1 == "") $sort1 = "od_id";
if ($sort2 == "") $sort2 = "desc";

// 김선용 201009 :
/*
$sql_common = " from $g4[yc4_card_history_table] a
                left outer join $g4[yc4_order_table] b on (a.od_id = b.od_id)
                $sql_search ";
*/
$sql_common = " from $g4[yc4_card_history_table] {$sql_search} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *
                 /*concat(a.cd_trade_ymd, ' ', a.cd_trade_hms) as cd_app_time*/
           $sql_common
           order by $sort1 $sort2
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>

<form name=flist style="margin:0px;">
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=page  value="<? echo $page ?>">
<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>
        <select name=sel_field>
            <option value='od_id'>주문번호
            <option value='cd_app_no'>승인번호
            <option value='cd_opt01'>결제자
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo $search ?>' autocomplete="off">
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=30% align=right>건수 : <? echo number_format($total_count) ?>&nbsp;</td>
</tr>
</table>


<table width=100% cellpadding=0 cellspacing=0 border=0>
<colgroup width=110>
<colgroup width=110>
<colgroup width=''>
<colgroup width=90>
<colgroup width=60>
<colgroup width=140>
<colgroup width=90>
<tr><td colspan=10 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
	<td>PG ID</td>
    <td><a href="<? echo title_sort("od_id") . "&$qstr1"; ?>">주문번호</a></td>
    <td><a href="<? echo title_sort("cd_amount") . "&$qstr1"; ?>">승인금액($)</a></td>
    <td><a href="<? echo title_sort("cd_app_no") . "&$qstr1"; ?>">승인번호</a></td>
    <td><a href="<? echo title_sort("cd_app_rt") . "&$qstr1"; ?>">승인결과</a></td>
    <td><a href="<? echo title_sort("cd_time") . "&$qstr1"; ?>">승인일시</a></td>
    <td><a href="<? echo title_sort("cd_opt01") . "&$qstr1"; ?>">결제(입금)자</a></td>
</tr>
<tr><td colspan=10 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
		// 김선용 201107 : root 계정일때 카드/포인트 결제건만 출력
	if($member['mb_id'] == 'root')
		$od_id_ip = substr(preg_replace('/[^0-9]/', '', $row['cd_ip']),0,5);
	else
		$od_id_ip = "";

    $list = $i%2;
    echo "
    <tr class='list$list center ht'>
		<td>{$row['cd_mall_id']}</td>
        <td><a href='./orderform.php?od_id=$row[od_id]'><U>$row[od_id]</U></a>{$od_id_ip}</td>";

		// 김선용 2014.03 : kcp 가상계좌/신용카드
		// 김선용 201107 : 가상계좌주문시 입금예정액과 입금액 추가. 분리
		if($row['cd_mall_id'] == '100140') // gsm
	        echo "<td align=right style='padding-right:15px;'>입:".display_amount($row[cd_amount])." (주:".number_format($row[cd_amount_temp],0).")</td>";
		else if($row['cd_mall_id'] == 'E8397') // kcp
		{
			if($row['cd_method_type'] == '가상계좌')
				echo "<td align=right style='padding-right:15px;'>입:".display_amount($row[cd_amount])." (주:".number_format($row[cd_amount_temp],0).")<br/>[ 가상계좌 ]</td>";
			else if($row['cd_method_type'] == '신용카드')
				echo "<td align=right style='padding-right:15px;'>".display_amount($row[cd_amount])."<br/>[ 신용카드 ]</td>";
			else
				echo "<td align=right style='padding-right:15px;'></td>";
		}
		else // authorize.net
	        echo "<td align=right style='padding-right:15px;'>".display_amount($row[cd_amount])." (\$".number_format($row[cd_amount_usd],2).")</td>";

		echo "
        <td>$row[cd_app_no]</td>
        <td>$row[cd_app_rt]</td>
        <td>$row[cd_time]</td>
        <td>$row[cd_opt01]</td>
    </tr><tr><td colspan=10 height=1 bgcolor=F5F5F5></td></tr>";
}

if ($i == 0)
    echo "<tr><td colspan=10 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
<tr><td colspan=10 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <td width=50%></td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>
</form>

* 신용카드, 실시간 계좌이체로 승인한 내역이며, 주문번호를 클릭하시면 주문상세 페이지로 이동합니다.


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

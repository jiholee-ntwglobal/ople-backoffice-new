<?php
$sub_menu = "400900";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$abank = array();
$abank['01'] = '한국은행';
$abank['02'] = '한국산업은행';
$abank['03'] = '기업은행';
$abank['04'] = '국민은행';
$abank['05'] = '외환은행';
$abank['07'] = '수협중앙회';
$abank['11'] = '농협중앙회';
$abank['12'] = '단위농협';
$abank['16'] = '축협중앙회';
$abank['20'] = '우리은행';
$abank['21'] = '조흥은행';
$abank['22'] = '상업은행';
$abank['23'] = '제일은행';
$abank['24'] = '한일은행';
$abank['25'] = '서울은행';
$abank['26'] = '신한은행';
$abank['27'] = '씨티은행';
$abank['31'] = '대구은행';
$abank['32'] = '부산은행';
$abank['34'] = '광주은행';
$abank['35'] = '제주은행';
$abank['37'] = '전북은행';
$abank['38'] = '강원은행';
$abank['39'] = '경남은행';
$abank['41'] = '비씨카드';
$abank['53'] = '씨티은행';
$abank['54'] = '홍콩상하이은행';
$abank['71'] = '우체국';
$abank['81'] = '하나은행';
$abank['83'] = '평화은행';
$abank['93'] = '새마을금고';

// 김선용 201107 : 가상계좌 처리테이블
sql_query("create table {$g4['yc4_gsmpg_table']} (
	gs_pid int not null auto_increment,
	gs_sum_amount int not null,
	gs_transactionNo varchar(15) not null,
	gs_acco_code char(2) not null,
	gs_deal_sele char(2) not null,
	gs_in_bank_cd varchar(15) not null,
	gs_amount int not null,
	gs_rece_nm varchar(20) not null,
	gs_cms_no varchar(30) not null,
	gs_deal_star_date varchar(8) not null,
	gs_deal_star_time varchar(8) not null,
	gs_StoreId varchar(10) not null,
	gs_rVATVCode varchar(40) not null,
	gs_datetime datetime not null,
	gs_ip varchar(15) not null,
	primary key(gs_pid),
	unique tno(gs_transactionNo)
)", false);
sql_query("alter table {$g4['yc4_gsmpg_table']} add od_id varchar(20) not null", false);


$g4[title] = "가상계좌입금통보";
include_once ("$g4[admin_path]/admin.head.php");


$where = " where ";
$sql_search = "";
if ($search != "")
{
	if ($sel_field != "")
    {
    	$sql_search .= " $where $sel_field like '%$search%' ";
        $where = " and ";
    }
}
if ($sel_field == "")  $sel_field = "od_id";

$sql = " from {$g4['yc4_gsmpg_table']} $sql_search ";
$total_count = mysql_num_rows(sql_query("select gs_pid $sql"));
$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
//$gs = sql_fetch("select * $sql order by gs_datetime desc limit 1"); print_r2($gs); exit;
$result = sql_query("select * $sql order by gs_datetime desc limit $from_record, $rows ");

$qstr1 = "sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&page=$page";
?>
<?=subtitle($g4[title])?>

<form name=flist style="margin:0px;">
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=page  value="<? echo $page ?>">
<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>
        <select name=sel_field>
            <option value='od_id'>주문번호
            <option value='gs_transactionNo'>거래번호
            <option value='gs_rece_nm'>입금자
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo $search ?>' autocomplete="off">
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=30% align=right>전체 : <? echo number_format($total_count) ?>&nbsp;</td>
</tr>
</table>
</form>

<table cellpadding=0 cellspacing=0 width=100%>
<tr><td colspan=9 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
	<td>주문번호</td>
	<td>거래번호</td>
	<td>구분</td>
	<td>가상계좌</td>
	<td>입금은행</td>
	<td>입금액</td>
	<td>입금자</td>
	<td>일시</td>
</tr>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
<?
for($k=0; $row=sql_fetch_array($result); $k++)
{
    $list = $k%2;
?>
<tr class='list<?=$list?> ht'>
	<td><a href='./orderform.php?od_id=<?=$row[od_id]?>'><u><?=$row['od_id']?></u></a></td>
	<td align=center><?=$row['gs_transactionNo']?></td>
	<?if($row['gs_deal_sele'] == '20'){?>
		<td align=center>입금</td>
	<?}else if($row['gs_deal_sele'] == '51'){?>
		<td align=center>입금취소</td>
	<?}?>
	<td align=center><?=$row['gs_cms_no']?></td>
	<td align=center><?=$abank[$row['gs_acco_code']]?></td>
	<td align=right><?=nf($row['gs_amount'])?>&nbsp;</td>
	<td align=center><?=$row['gs_rece_nm']?></td>
	<td align=center><?=$row['gs_datetime']?></td>
</tr>
<?}?>

<?if(!$k) echo "<tr><td colspan=15 align=center height=100 bgcolor=#ffffff>자료가 없습니다.</td></tr>"; ?>
</tr><tr><td colspan=10 height=1 bgcolor=F5F5F5></td></tr>
</tr>
</table>
<br/>
<table width=100%>
<tr>
    <td align=center height=30><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr1&page=");?></td>
</tr>
</table>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
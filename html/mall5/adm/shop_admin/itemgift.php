<?php
$sub_menu = "400512";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");


$g4[title] = "사은품관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from $g4[yc4_gift_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by gift_id desc limit $from_record, $rows ";
$result = sql_query($sql);
?>

<table width=100%>
<tr>
    <td width=50%><a href="<?=$_SERVER['PHP_SELF']?>">처음으로</A></td>
    <td width=50% align=right>전체 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100% border=0>
<tr><td colspan=10 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>이벤트코드</td>
    <td>제목</td>
	<td>분류</td>
    <td>금액</td>
	<td>총수량</td>
	<td>주문수량</td>
    <td>시작일</td>
	<td>종료일</td>
    <td><a href='./itemgiftform.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0 title='등록'></a></td>
</tr>
<tr><td colspan=10 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $s_mod = icon("수정", "./itemgiftform.php?w=u&gift_id=$row[gift_id]");
    $s_del = icon("삭제", "javascript:del('./itemgiftformupdate.php?w=d&gift_id=$row[gift_id]');");

	$row2 = ($row['gift_category'] ? sql_fetch("select ca_name from  $g4[yc4_category_table] where ca_id = '$row[gift_category]' ") : '');

	if($row['gift_ed_time'] < $g4['time_ymdhis'])
		$gift_title = "<span style='color:#ff0000;'>".stripslashes($row['gift_title'])."</span>";
	else if($row['gift_qty_now'] >= $row['gift_qty_all'])
		$gift_title = "<span style='color:#ff0000;'>".stripslashes($row['gift_title'])."</span>";
	else
		$gift_title = stripslashes($row['gift_title']);

	$list = $i%2;
    echo "
    <tr class='list$list center ht'>
        <td><a href='./orderlist.php?sel_field=od_gift_id&search=$row[gift_id]' target='_blank'>$row[gift_id]</a></td>
        <td align=center>{$gift_title}</td>
		<td>".stripslashes($row2[ca_name])."<br/>($row[gift_category])</td>
		<td>".nf($row[gift_amount])." ~ <br/> ".nf($row[gift_amount2])."</td>
		<td>$row[gift_qty_all]</td>
		<td>$row[gift_qty_now]</td>
		<td>".str_replace(" ", "<br/>", $row[gift_st_time])."</td>
		<td>".str_replace(" ", "<br/>", $row[gift_ed_time])."</td>
        <td>$s_mod $s_del</td>
    </tr><tr><td colspan=9 height=1 bgcolor=F5F5F5></td></tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=10 align=center height=100 bgcolor=#ffffff><span class=point>자료가 없습니다.</span></td></tr>\n";
}
?>

<tr><td colspan=10 height=1 bgcolor=CCCCCC></td></tr>
</table>

<?=$pagelist = get_paging($config[cf_write_pages], $page, $total_page, "?$qstr&page=");?>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

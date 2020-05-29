<?php
$sub_menu = "400501";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "요오드 예약관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from {$g4['yc4_rs_table']} ";
if(trim($search) != '') $sql_common .= " where $sel_field like '%$search%' ";

$count = sql_fetch("select count(rs_pid) as count {$sql_common}");
$total_count = $count['count'];
$rows = 1000; //$config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$result = sql_query("select * {$sql_common} order by rs_pid desc limit $from_record, $rows");
$qstr = "sel_field=$sel_field&search=$search";
?>
<form name=frmorderlist method=get style="margin:0;">
<input type=hidden name=page  value="<? echo $page ?>" />
<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=20%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>
        <select name=sel_field>
            <option value='rs_name'>성명</option>
            <option value='rs_hp'>휴대전화</option>
        </select>
        <input type=text name=search value='<? echo $search ?>' />
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle />
    </td>
    <td width=20% align=right>전체 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>

<table cellpadding=2 cellspacing=2 align="center" width="100%" summary="" border=0 style="border:4px solid #eee;">
<tr>
	<td height=28>성명</td>
	<td>휴대전화</td>
	<td>이메일</td>
	<td>작업</td>
</tr>
<tr><td height=2 colspan=10 bgcolor=#f7f7f7></td></tr>
<?
for($k=0; $row=sql_fetch_array($result); $k++)
{
	$s_del = icon("삭제", "javascript:del('rs_iodine_listdelete.php?rs_pid=$row[rs_pid]&$qstr&page=$page');");
?>
<tr>
	<td><?=$row['rs_name']?></td>
	<td><?=$row['rs_hp']?></td>
	<td><?=$row['rs_email']?></td>
	<td><?=$s_del?></td>
</tr>
<tr><td height=1 bgcolor=#f7f7f7 colspan=10></td></tr>
<?}?>
<?if(!$k) echo "<tr><td colspan=10 height=100 align=center>자료가 없습니다.</td></tr>"; ?>
</table>

<table width=100%>
<tr>
    <td width=50% align=CENTER><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

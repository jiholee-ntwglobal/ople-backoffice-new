<?php
$sub_menu = "100100";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

// 김선용 201107 :
sql_query("create table {$g4['yc4_bui_ip_table']} (
	bi_pid int not null auto_increment,
	bi_access_ip varchar(15) not null,
	bi_datetime datetime not null,
	bi_ip varchar(15) not null,
	primary key(bi_pid)
)", false);
sql_query("alter table {$g4['config_table']} add cf_bui_ip_access_sw tinyint(4) not null", false);

$g4[title] = "BUI접근IP설정";
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
if ($sel_field == "")  $sel_field = "bi_access_ip";

$sql = " from {$g4['yc4_bui_ip_table']} $sql_search ";
$total_count = mysql_num_rows(sql_query("select bi_pid $sql"));
$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$result = sql_query("select * $sql order by bi_pid desc limit $from_record, $rows ");

$qstr1 = "sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&page=$page";
?>
<?=subtitle($g4[title])?>

<div style="padding:4px; border:2px solid #0E87F9; text-align:center; width:49%; float:left;">
<form name="fselect" target="_self" method="post" action="bui_ip_access_update.php" style="margin:0;">
<input type="hidden" name="w" value="cf_sw">
<input type="checkbox" name="cf_bui_ip_access_sw" id="cf_bui_ip_access_sw" value=1 <?if($config['cf_bui_ip_access_sw']) echo "checked";?>><label for="cf_bui_ip_access_sw">IP접근제한 사용</label>&nbsp;&nbsp;<input type=submit class=btn1 value='  확  인  '>
</form>
</div>

<div style="padding:4px; border:2px solid #0E87F9; text-align:center; width:49%; float:right;">
<form name="fselect2" target="_self" method="post" action="bui_ip_access_update.php" style="margin:0;">
<input type="hidden" name="w" value="w">
<textarea name="bi_access_ip_arr" id="bi_access_ip_arr" rows=3 style="width:60%;" class="ed"></textarea>&nbsp;&nbsp;<input type=submit class=btn1 value='  확  인  '><br/>※ 등록된 IP만 BUI MODE 접근가능.(개별IP 풀매치)
</form>
</div>
<br/>

<form name=flist style="margin:0;">
<input type=hidden name=page  value="<?=$page?>">
<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>
        <select name=sel_field>
            <option value='bi_access_ip'>IP</option>
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
	<td>IP</td>
	<td>등록일</td>
	<td>작업</td>
</tr>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
<?
for($k=0; $row=sql_fetch_array($result); $k++)
{
    $list = $k%2;
?>
<tr class='list<?=$list?> ht'>
	<td align=center><?=$row['bi_access_ip']?></td>
	<td align=center><?=$row['bi_datetime']?></td>
	<td align=center><a href="bui_ip_access_update.php?w=d&bi_pid=<?=$row['bi_pid']?>&<?=$qstr?>">삭제</a></td>
	<td></td>
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
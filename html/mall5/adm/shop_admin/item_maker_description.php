<?php
$sub_menu = "300500";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "제조사 설명관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_search = "";
if ($search != "") {
	if ($sel_field != "") $sql_search .= " and {$sel_field} like '".mysql_real_escape_string($_GET['search'])."%' ";
}
$sql_common = " from $g4[yc4_item_table] where it_maker<>'' "; // where it_use=1
$sql_common .= "{$sql_search} group by it_maker ";

// 테이블의 전체 레코드수만 얻음
$sql = " select it_maker as cnt {$sql_common} ";
$row = mysql_num_rows(sql_query($sql));
$total_count = $row;

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select it_maker, it_maker_description
          $sql_common
          order by it_maker
          limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&page=$page";
?>
<?=subtitle($g4[title])?>

<div style="padding:4px; border:2px solid #0E87F9; text-align:center; width:100%;">
<form name=flist method=get style="margin:0px;">
	<select name=sel_field>
		<option value='it_maker'>제조사명
	</select>
	<? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>
	<input type=text name=search value='<? echo get_text(stripslashes($_GET['search'])) ?>' size=20 title="좌측부터 일치순으로 검색">
	<input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
</form>
</div><br/>

<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=50%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=50% align=right>전체 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<form name=fitem method=post action="item_maker_description_update.php" onsubmit="return fcheck(this)" style="margin:0px;">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search     value="<? echo $search ?>">
<input type=hidden name=page       value="<? echo $page ?>">

<table cellpadding=0 cellspacing=0 width=100% border=0>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td width=200><a href='<? echo title_sort("it_maker") . "&$qstr1&ev_id=$ev_id"; ?>'>제조사</a><br/>(상품수)</td>
    <td>간략설명(HTML로 입력(줄바꿈등 모든태그))</td>
	<td width=50><input type="checkbox" id="chkall" name="chkall" onclick="check_all(document.fitem);"></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	$it_count = sql_fetch("select count(it_id) as count from {$g4['yc4_item_table']} where it_maker='".mysql_real_escape_string($row['it_maker'])."' ");

    $list = $i%2;

	echo "
	<input type='hidden' name='it_maker[$i]' value='".urlencode($row['it_maker'])."'>
    <tr class='list$list center'>
        <td>".stripslashes($row['it_maker'])."</a><br/>(".nf($it_count['count']).")</td>
        <td align=left><textarea name='it_maker_description[$i]' id='it_maker_description[$i]' rows=6 cols=80 class=ed>".get_text($row['it_maker_description'])."</textarea></td>
        <td><input type=checkbox name='chk[]' value='$i'></td>
    </tr>";
}

if ($i == 0)
    echo "<tr><td colspan=4 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 없습니다.</span></td></tr>";
?>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <td width=50%><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
    <td width=50% align=right><input type=submit class=btn1 value='일괄수정' accesskey='s'></td>
</tr>
</table>
</form><br/>

<script type="text/JavaScript">
function fcheck(f)
{
	var chk = false;
	var a = document.getElementsByName('chk[]');
	for(k=0; k<a.length; k++){
		if(a[k].checked){
			chk = true;
		}
	}
	if(!chk){
		alert("처리할 자료를 하나이상 선택하십시오.");
		return false;
	}
    return true;
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
<?php
$sub_menu = "300960";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "상품코드관리";
include_once ("$g4[admin_path]/admin.head.php");


$sql_search = "";
$where = " where ";
if ($search != "") {
	if ($sel_field != "") $sql_search .= " $where $sel_field like '%$search%' ";
	$where = " and ";
}
if ($sel_ca_id != "") $sql_search .= " $where ca_id like '$sel_ca_id%' ";
if ($sel_field == "") $sel_field = "it_name";

$sql_common = " from $g4[yc4_item_table] "; // where it_use=1
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *
          $sql_common
          order by it_id desc
          limit $from_record, $rows ";
echo $sql;
$result = sql_query($sql);

$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
$qstr  = "$qstr1&page=$page";
$qstr1 = urlencode($qstr1);
?>
<?=subtitle($g4[title])?>

<div style="padding:4px; border:2px solid #0E87F9; text-align:center; width:100%;">
<form name=flist style="margin:0px;">
<input type=hidden name=page value="<? echo $page ?>">
	<select name="sel_ca_id">
	<option value=''>전체분류
	<?
	$sql1 = " select ca_id, ca_name from $g4[yc4_category_table] order by ca_id ";
	$result1 = sql_query($sql1);
	for ($i=0; $row1=sql_fetch_array($result1); $i++)
	{
		$len = strlen($row1[ca_id]) / 2 - 1;
		$nbsp = "";
		for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
		echo "<option value='$row1[ca_id]'>$nbsp$row1[ca_name]\n";
	}
	?>
	</select>
	<script> document.flist.sel_ca_id.value = '<?=$sel_ca_id?>';</script>

	<select name=sel_field>
	<option value='it_name'>상품명
	<option value='it_id'>상품코드
	</select>
	<? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>
	<input type=text name=search value="<? echo htmlspecialchars(stripslashes($search)); ?>" size=10>
	<input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
</form>
</div><br/>

<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=50%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=50% align=right>전체 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<form name=fitem method=post action="./itemcode_manager_update.php" onsubmit="return fcheck(this)" style="margin:0px;">
<input type=hidden name=sel_ca_id  value="<? echo $sel_ca_id ?>">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search     value="<? echo stripslashes($search) ?>">
<input type=hidden name=page       value="<? echo $page ?>">

<table cellpadding=0 cellspacing=0 width=100% border=0>
	<colgroup width=100>
	<colgroup width=60>
	<colgroup width=>
	<colgroup width=50>
	<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
	<tr align=center class=ht>
		<td>
			<a href="<? echo title_sort("a.it_id") . "&$qstr1&ev_id=$ev_id"; ?>">상품코드</a>
		</td>

		<td width='' colspan=2><a href="<? echo title_sort("it_name") . "&$qstr1&ev_id=$ev_id"; ?>">상품명</a></td>

		<td><input type="checkbox" id="chkall" name="chkall" onclick="check_all(document.fitem);"></td>
        <td></td>
	</tr>
	<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
	<?
	for ($i=0; $row=sql_fetch_array($result); $i++)
	{
		$href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";
		$admin_href = "{$g4[shop_admin_path]}/itemform.php?w=u&it_id=$row[it_id]";


		$list = $i%2;
		echo "

		<tr class='list$list center'>
			<td><a href='$href'>$row[it_id]</a></td>
			<td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50)."</a></td>
			<td align=left><a href='$admin_href'>".stripslashes($row[it_name])."</a></td>
			<td><input type='hidden' name='it_id[$i]' value='$row[it_id]'><input type=checkbox name='chk[]' value='$i'></td>
			<td><a href='".$_SERVER['PHP_SELF']."?sel_field=it_name&search=".urlencode($row['it_name'])."'>상품명검색</a></td>
		</tr>";
	}

	if ($i == 0)
		echo "<tr><td colspan=4 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 없습니다.</span></td></tr>";
	?>
	<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>
<?=$hidden_input;?>
<table width=100%>
<tr>
    <td colspan=50%><input type=submit class=btn1 value='일괄수정' accesskey='s'></td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>
</form><br/>

<script language="JavaScript">
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
		alert('상품코드를 변경할 자료를 하나이상 선택하십시오.');
		return false;
	}
    return true;
}
</script>

<?php
include_once ("$g4[admin_path]/admin.tail.php");

<?php
$sub_menu = "300777";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "상품입고요청관리 : 상품찾기";
include_once ("$g4[path]/head.sub.php");

$sql_search = "";
if($stx != ""){
    if ($sfl != "")
        $sql_search .= " and $sfl like '%$stx%' ";
}
if($sel_ca_id != "") $sql_search .= " and ca_id like '$sel_ca_id%' ";
$sql_common = " from $g4[yc4_item_table] where it_use=1 and it_discontinued=0  {$sql_search} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

if(!$items) $items = $config['cf_page_rows'];
$rows = $items;
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql  = " select it_id, it_name, it_stock_qty, it_amount, it_point
           $sql_common
           order by it_id desc
           limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = "class_index=$class_index&items=$items&sel_ca_id=$sel_ca_id&sfl=$sfl&search=".urlencode($search);
$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
?>
<div style="padding:4px; border:4px #eaeaea solid; text-align:center; margin-top:5px;">
※ 단종상품은 제외됩니다.<br/>
<form name=fhsearch METHOD=GET style="margin:0;">
<input type="hidden" name="class_index" id="class_index" value="<?=$class_index?>">

<select name="items" id="items">
	<option value="">목록수</option>
	<?for($k=2; $k<21; $k++){?>
	<option value="<?=($k*5)?>" <?if($items==($k*5)) echo 'selected';?>><?=($k*5)?></option>
	<?}?>
</select>
<select name=sfl>
	<option value='it_name'>상품명</option>
	<option value='it_id'>상품코드</option>
</select>
<? if ($sfl) echo "<script> document.fhsearch.sfl.value = '$sfl';</script>"; ?>
<input type=text size=30 name=stx value='<?=stripslashes(get_text($_GET['stx']));?>'>
<input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle><br/>

<select name="sel_ca_id">
	<option value=''>전체분류
	<?
	$sql1 = " select ca_id, ca_name from $g4[yc4_category_table] order by ca_id ";
	$result1 = sql_query($sql1);
	for ($i=0; $row1=sql_fetch_array($result1); $i++) {
		$len = strlen($row1[ca_id]) / 2 - 1;
		$nbsp = "";
		for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
		echo "<option value='$row1[ca_id]'>$nbsp$row1[ca_name]\n";
	}
	?>
</select>
<script> document.fhsearch.sel_ca_id.value = '<?=$sel_ca_id?>';</script>
</form>
</div>

<table width=100% cellpadding=4 cellspacing=0 border=0>
<tr>
    <td><a href='<?=$_SERVER[PHP_SELF]?>?class_index=<?=$class_index?>'><b>처음으로</b></a></td>
    <td align=right>전체 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>

<table border=2 cellspacing=0 cellpadding=2 align="center" bordercolor='#95A3AC'  class="state_table">
<colgroup>
	<col width=65></col>
	<col width=50></col>
	<col width=''></col>
	<col width=60></col>
	<col width=60></col>
	<col width=60></col>
	<col width=30></col>
</colgroup>
<tr align=center>
	<td class=yalign_head>상품코드</TD>
	<td colspan=2 class=yalign_head><a href="<?=title_sort("it_name")."&$qstr1";?>"><span style="color:white; text-decoration:underline;">상품명</span></a></td>
    <td class=yalign_head><a href="<?=title_sort("it_amount")."&$qstr1";?>"><span style="color:white; text-decoration:underline;">가격</span></a></td>
    <td class=yalign_head><a href="<?=title_sort("it_point")."&$qstr1";?>"><span style="color:white; text-decoration:underline;">포인트</span></a></td>
    <td class=yalign_head>재고</td>
	<td class=yalign_head>선택</td>
</tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	$u_href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";
    $a_href = "./itemform.php?w=u&it_id={$row['it_id']}";

	echo "<input type=hidden name='it_id[]' value='{$row['it_id']}' />";
?>
	<tr bgcolor="#FFFFFF" onmouseover="this.style.backgroundColor='#c9c9c9';" onmouseout="this.style.backgroundColor='#FFFFFF';" align=center>
		<TD><?=$row['it_id']?></td>
		<td style='padding:5px 5px 5px 5px'><a href='<?=$u_href?>' target='_blank' title='새창으로 상품보기'><?=get_it_image("{$row[it_id]}_s", 60, 50)?></a></td>
        <td style='padding-left:5px;'><a href='<?=$a_href?>' target='_blank' title='새창으로 상품수정하기'><?=stripslashes($row['it_name'])?></td>
		<td><?=number_format($row['it_amount'])?></td>
		<td><?=number_format($row['it_point'])?></td>
		<td><?=$row['it_stock_qty']?></td>
		<td><input type="button" value="선택" class="it_sel" /></td>
    </tr>
<?}?>
<?if ($i == 0)
    echo "<tr><td colspan=20 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 없습니다.</span></td></tr>";
?>
</table>
<br/>
<p style="margin-left:20px; float:left;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?items=$items&$qstr&page=");?></p>
<p style="margin:0; float:right;"><input type="button" value=" 닫/기 " onclick="window.close();" title="닫기"></p>


<script type="text/javascript">

$('.it_sel').click(function()
{
	var i = $('.it_sel').index(this);
	var it_id = $("input[name='it_id[]']").eq(i).val();
	$("input[name='it_id[]']:eq("+<?=$class_index?>+")", opener.document).val(it_id);
	window.close();
});
</script>

<? include_once ("$g4[path]/tail.sub.php"); ?>
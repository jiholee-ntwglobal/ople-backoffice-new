<?php
include_once("./_common.php");

$g4[title] = "구매후기";
include_once("./_head.php");

if(trim($search) != '') $search = mysql_real_escape_string($_GET['search']);
$sql_search = " where is_confirm=1 ";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}
if ($sel_ca_id != "") {
    $sql_search .= " and ca_id like '$sel_ca_id%' ";
}
if (!$sort1) $sort1 = "is_best, is_id";
if (!$sort2) $sort2 = "desc";
$sql_common = "  from $g4[yc4_item_ps_table] a
                 left join $g4[yc4_item_table] b on (a.it_id = b.it_id) ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(a.is_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = 30; //$config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$sql  = " select a.*, b.it_name $sql_common order by $sort1 $sort2 limit $from_record, $rows ";
$result = sql_query($sql);

$qstr = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search);
?>
<fieldset style="margin:5px 10px 5px 0;">
	<legend>사용후기 안내</legend>
	<div class=fieldset_div>
		<span class=span_left>사용후기 모음페이지입니다. 사용후기 작성은 해당상품의 상세보기 페이지에서 작성하실 수 있습니다.<br/>상품평은 개인의 주관적인 생각이므로 다양한 의견이 있을 수 있으니, 참고사항으로 사용하시기 바랍니다.
		<?if($is_admin === 'super') echo "<br/><span style='color:blue;'>※ 관리자안내 : 베스트후기 선정은 해당 후기를 선택하고 베스트후기선정 버튼을 클릭</span>"; ?>
		</span>
	</div>
</fieldset>
<br/>

<form name=flist style="margin:0px;" method="get">
<input type=hidden name=page  value="<? echo $page ?>">

<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=70% align=center>
        <select name="sel_ca_id">
            <option value=''>-전체분류-</option>
            <?
            $sql1 = " select ca_id, ca_name from $g4[yc4_category_table] order by ca_id ";
            $result1 = sql_query($sql1);
            for ($i=0; $row1=sql_fetch_array($result1); $i++) {
                $len = strlen($row1[ca_id]) / 2 - 1;
                $nbsp = "";
                for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
                echo "<option value='$row1[ca_id]'>$nbsp$row1[ca_name]</option>";
            }
            ?>
        </select>
        <script type="text/javascript">
        if("<?=$sel_ca_id?>" != '')
	        document.flist.sel_ca_id.value = '<?=$sel_ca_id?>';
		</script>

        <select name=sel_field>
            <option value='b.it_name'>상품명</option>
            <option value='b.it_id'>상품코드</option>
        </select>
        <? if ($sel_field != '') echo "<script type='text/javascript'> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo get_text(stripslashes($search))?>' title="좌측부터 일치하는 방식으로 검색됩니다." />
        <input type=submit value=" 검색 " />
    </td>
    <td width=20% align=right>전체 : <? echo number_format($total_count,0)?></td>
</tr>
</table>
</form>

<?if($is_admin === 'super'){?>
<form name=flist style="margin:0;" method="post" action="hoogi_listupdate.php" onsubmit="return js_best_update(this);">
<input type=hidden name=page  value="<? echo $page ?>">
<input type=hidden name=sel_ca_id  value="<? echo $sel_ca_id ?>">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search  value="<? echo urlencode($search) ?>">
<?}?>

<table cellpadding=3 cellspacing=0 width=100% border=0>
<colgroup width=80>
<colgroup width=''>
<colgroup width=65>
<colgroup width=200>
<colgroup width=55>
<?if($is_admin === 'super'){?>
<colgroup width=30>
<?}?>
<tr><td colspan=10 height=1 bgcolor=#EBE4DB></td></tr>
<tr><td colspan=10 height=1 bgcolor=#F1ECE6></td></tr>
<tr align=center height=30 bgcolor=#FAF7F3>
    <td></td>
    <td>상품명</td>
    <td>작성자</td>
    <td>제목</td>
    <td>점수</td>
	<?if($is_admin === 'super'){?>
	<td>선택</td>
	<?}?>
</tr>
<tr><td colspan=10 height=1 bgcolor=#EBE4DB></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	if($row['it_name']){
		$row['it_name'] = get_item_name($row['it_name']);
	}
    $row[is_subject] = get_text(stripslashes($row[is_subject]));
	$is_content = conv_content($row[is_content], 0);
    $href = "$g4[shop_path]/item.php?it_id=$row[it_id]";
	$star = get_star($row[is_score]);
	$is_name = stripslashes($row['is_name']);

	// 회원처리
	if($row['mb_id']) $is_name = "<b>{$is_name}</b>";

	$img_str = "";
	for($a=0; $a<5; $a++){
		if($row["is_image{$a}"] != '' && file_exists("{$g4['path']}/data/ituse/".$row["is_image{$a}"]))
			$img_str .= "<img src='{$g4['path']}/data/ituse/".$row["is_image{$a}"]."' border=0 /><br/><br/>";
	}

    echo "
    <tr>
        <td style='padding-top:5px; padding-bottom:5px;' align=center>
		<table border=0 cellspacing=0 cellpadding=0><tr><td style='cursor:pointer; border:1px solid #FFFFFF; padding:0px' onMouseOver=\"this.style.border='1px solid #FF6600';\" onMouseOut=\"this.style.border='1px solid #FFFFFF';\"><a href='$href' target=_blank title='새창으로 [".stripslashes($row['it_name'])."] 상품보기'>".get_it_image("{$row[it_id]}_s", 70, 60)."</a></td></tr></table></td>
        <td align=left><a href='$href' target=_blank title='새창으로 [".stripslashes($row['it_name'])."] 상품보기'>".stripslashes($row[it_name])."</a></td>
        <td align=center>$is_name</td>
        <td style='padding : 10 0 10 0;'>
			<div><b>$row[is_subject]</b><br><span style='font-size:8pt;'>[{$row['is_time']}]</span></div>
		</td>
        <td align=center><img src=\"{$g4[shop_img_path]}/star{$star}.gif\"></td>";
		if($is_admin === 'super'){
			echo "
			<td align=center>
				<input type=checkbox name='chk[]' value='$i' />
				<input type=hidden name='is_id[]' value='{$row['is_id']}' />
				<input type=hidden name='mb_id[]' value='{$row['mb_id']}' />
			</td>";
		}
	echo "
    </tr>
	<tr><td colspan=10><div>$is_content<br/>{$img_str}</div></td></tr>
	<tr><td colspan=10 align=center height=2 bgcolor='#F1EFEC'></td></tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=10 align=center height=100 bgcolor=#ffffff><span class=point>자료가 없습니다.</span></td></tr>";
}
?>

<tr><td colspan=5 height=1 bgcolor=F1EFEC></td></tr>
</table>

<?if($is_admin === 'super'){?>
<p align=right style="margin:10px 0;"><input type="submit" value="베스트후기선정" /></p>
</form>
<?}?>

<table width=100%>
<tr>
    <td align=center><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>


<?if($is_admin === 'super'){?>
<script type="text/javascript">
function js_best_update(f)
{
	var a = document.getElementsByName('chk[]');
	var c = false;
	for(k=0; k<a.length; k++){
		if(a[k].checked){
			c = true;
			break;
		}
	}
	if(c == false){
		alert("베스트후기로 선정할 자료를 1개이상 선택해 주십시오.");
		return false;
	}
	return true;
}
</script>
<?}?>


<? include_once("./_tail.php"); ?>

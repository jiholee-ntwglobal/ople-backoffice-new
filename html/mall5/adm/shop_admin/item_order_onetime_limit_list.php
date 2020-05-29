<?php
$sub_menu = "300300";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");


$g4[title] = "1회구매수량관리";
include_once ("$g4[admin_path]/admin.head.php");


if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}

$tmp_ca_id = $_GET['sel_ca_id'];
$result_child_ca_id = array_pop($tmp_ca_id);

if ($result_child_ca_id != "") $sql_search .= " and b.ca_id like '$result_child_ca_id%' ";
if ($sel_field == "")  $sel_field = "a.it_name";
if ($sort1 == "") $sort1 = "a.it_id";
if ($sort2 == "") $sort2 = "desc";
if($_GET['sel_s_id']) $sql_search .= " and c.s_id = '".$_GET['sel_s_id']."'";

$sql_common = "
	from
		{$g4['yc4_item_table']} a
		left join
		yc4_category_item b on a.it_id = b.it_id
		left join
		shop_category c on b.ca_id like concat(c.ca_id,'%')
	where 1=1
";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;

$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select a.it_id,
                 a.it_name,
                 a.it_use,
				 a.it_order_onetime_limit_cnt
           $sql_common
          order by $sort1 $sort2
          limit $from_record, $rows ";

$result = sql_query($sql);

$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search);
$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";

# 제품관 리스트 #
$station_op_qry = sql_query("select s_id,name from yc4_station where view='Y' order by sort asc");
while($row = sql_fetch_array($station_op_qry)){
	$st_op .= "<option value='".$row['s_id']."' ".($_GET['sel_s_id'] == $row['s_id'] ? "selected":"").">".$row['name']."</option>";
}
$st_param = $ca_param = $_GET;
unset($st_param['sel_s_id']);
unset($st_param['page'],$ca_param['page']);
unset($st_param['sel_ca_id'],$ca_param['sel_ca_id']);
$st_param = http_build_query($st_param);
$ca_param = http_build_query($ca_param);

if(is_array($_GET['sel_ca_id'])){
	foreach($_GET['sel_ca_id'] as $key => $val){
		if($key == 0) continue;
	}
	$ca_param_arr[$key] = $_GET;
	unset($ca_param_arr[$key]['page']);
	unset($ca_param_arr[$key]['sel_ca_id']);
	$ca_param_arr[$key] = http_build_query($ca_param_arr[$key]);
}

?>
<?=subtitle($g4[title])?>

<form name=flist method=get style="margin:0px;">
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=sort2 value="<? echo $sort2 ?>">

<table width=100% cellpadding=4 cellspacing=0>
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=80% align=center>
		<select name="sel_s_id" onchange="location.href='<?=$_SERVER['PHP_SELF']."?".$st_param;?>&sel_s_id='+this.value">
			<option value="">제품관</option>
			<?=$st_op;?>
		</select>
        <select name="sel_ca_id[0]" onchange="location.href='<?=$_SERVER['PHP_SELF']."?".$ca_param;?>&sel_ca_id[0]='+this.value">
            <option value=''>전체분류
            <?
            $sql1 = "
				select
					b.ca_id, b.ca_name
				from
					shop_category a
					left join
					$g4[yc4_category_table] b on a.ca_id = b.ca_id
				where
					b.ca_id is not null
					and
					a.s_id = '".$_GET['sel_s_id']."'
				order by a.sort
			";
            $result1 = sql_query($sql1);
            for ($i=0; $row1=sql_fetch_array($result1); $i++) {
                echo "<option value='$row1[ca_id]' ".($_GET['sel_ca_id'][0] == $row1['ca_id'] ? "selected":"").">$row1[ca_name]</option>";
            }
            ?>
        </select>
		<?
			if($_GET['sel_ca_id'][0]){
				//foreach($_GET['sel_ca_id'] as $key => $val){
				$sel_ca_id_cnt = count($_GET['sel_ca_id']);

				for($i=0; $i<=$sel_ca_id_cnt; $i++){
					$sel_ca_id_val .= "&sel_ca_id[".$i."]=".$_GET['sel_ca_id'][$i];
					if($i<1){
						continue;
					}

					$ca_child_qry = sql_query($a="
						select
							ca_id,ca_name
						from
							$g4[yc4_category_table]
						where
							ca_id like '".$_GET['sel_ca_id'][$i-1]."%'
							and
							length(ca_id) = '". (strlen($_GET['sel_ca_id'][$i-1]) + 2) ."'
					");
					$ca_child_cnt = mysql_num_rows($ca_child_qry);



					if($ca_child_cnt > 0){
						echo "<select name='sel_ca_id[". ($i) ."]'  onchange=\"location.href='".$_SERVER['PHP_SELF']."?".$ca_param.$sel_ca_id_val."&sel_ca_id[".$i."]='+this.value\">";
						echo "
								<option value=''>전체분류</option>
						";
						while($rows = sql_fetch_array($ca_child_qry)){
							echo "
								<option value='".$rows['ca_id']."' ".($rows['ca_id'] == $_GET['sel_ca_id'][$i] ? "selected":"").">".$rows['ca_name']."</option>
							";
						}
						echo "</select>";
					}
				}
			}
		?>
        <script> document.flist.sel_ca_id.value = '<?=$sel_ca_id?>';</script>

        <select name=sel_field>
            <option value='a.it_name'>상품명
            <option value='a.it_id'>상품코드
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo get_text(stripslashes($search)) ?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=10% align=right>전체 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>

<div style="width:100%; margin:5px 0 0 0; text-align:center;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></div>

<form name=fitemcardonly method=post action="./item_order_onetime_limit_listupdate.php" style="margin:0;">
<input type=hidden name=sort1      value="<? echo $sort1 ?>">
<input type=hidden name=sort2      value="<? echo $sort2 ?>">
<input type=hidden name=sel_ca_id  value="<? echo $sel_ca_id ?>">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search     value="<? echo $search ?>">
<input type=hidden name=page       value="<? echo $page ?>">

<? ob_start(); ?>
<p align=right style="margin:3px 0 3px 0;"><input type=submit class=btn1 value='일괄수정'></p>
<?
$all_update_button = ob_get_contents();
ob_end_flush();
?>

<table border=2 cellspacing=0 cellpadding=4 align="center" bordercolor='#95A3AC'  class="state_table">
<colgroup>
	<col width=70></col>
	<col width=60></col>
	<col></col>
	<col width=70></col>
	<col width=70></col>
	<col width=70></col>
</colgroup>
<tr>
	<td class=yalign_head><a href='<? echo title_sort("it_id") . "&$qstr1"; ?>'><span style="color:#FFFFCC;">상품코드</span></a></td>
	<td class=yalign_head colspan=2><a href='<? echo title_sort("it_name") . "&$qstr1"; ?>'><span style="color:FFFFCC;">상품명</span></a></td>
	<td class=yalign_head><a href='<? echo title_sort("it_order_onetime_limit_cnt") . "&$qstr1"; ?>'><span style="color:FFFFCC;">구매수량</span></a></td>
	<td class=yalign_head><a href='<? echo title_sort("it_use") . "&$qstr1"; ?>'><span style="color:FFFFCC;">판매</span></a></td>
	<td class=yalign_head>작업</td>
</tr>
<?
for($i=0; $row=sql_fetch_array($result); $i++)
{
    $href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";
	$s_mod = icon("수정", "./itemform.php?w=u&it_id=$row[it_id]&return_url=item_order_onetime_limit_list&$qstr");
	$s_del = icon("삭제", "javascript:del('./itemformupdate.php?w=d&return_url=item_order_onetime_limit_list&it_id={$row['it_id']}&$qstr');");

	echo "<input type='hidden' name='it_id[$i]' value='$row[it_id]'>\n";
?>
<tr bgcolor="#FFFFFF" onmouseover="this.style.backgroundColor='#c9c9c9';" onmouseout="this.style.backgroundColor='#FFFFFF';" align=center>
	<td><?=$row['it_id']?></td>
	<td style='padding-top:5px; padding-bottom:5px;'><a href='<?=$href?>'><?=get_it_image("{$row[it_id]}_s", 60, 60)?></a></td>
	<td align=left><a href='<?=$href?>'><?=stripslashes($row['it_name'])?></a></td>
	<td><input type=text size=4 name='it_order_onetime_limit_cnt[<?=$i?>]' value='<?=$row['it_order_onetime_limit_cnt']?>'></td>
	<td><input type=checkbox style='background-color:#DBEEFB' name='it_use[<?=$i?>]' value='1' <?=($row[it_use] ? "checked" : "");?>></td>
	<td><?=$s_mod?>&nbsp;<?=$s_del?></td>
</tr>
<?}?>
<?if(!$i) echo "<tr><td align=center height=100 colspan=10>자료가 없습니다.</td></tr>"; ?>
</table>
<?=$all_update_button?>
</form>
<br/>

<div style="width:100%; margin:0 0 5px 0; text-align:center;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></div>

<? include_once ("$g4[admin_path]/admin.tail.php"); ?>
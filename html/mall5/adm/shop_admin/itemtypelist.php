<?
$sub_menu = "300600";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "상품유형관리";
include_once ("$g4[admin_path]/admin.head.php");

/*
$sql_search = " where 1 ";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}

if ($sel_ca_id != "") {
    $sql_search .= " and (ca_id like '$sel_ca_id%' or ca_id2 like '$sel_ca_id%' or ca_id3 like '$sel_ca_id%') ";
}

if ($sel_field == "")  $sel_field = "it_name";
*/

$where = " where ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where a.$sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

$tmp_ca_id = $_GET['sel_ca_id'];
$result_child_ca_id = array_pop($tmp_ca_id);

if ($result_child_ca_id != "") {
	$sql_search .= $where. " b.ca_id like '$result_child_ca_id%' ";
	$where = ' and ';
}


if ($sfl == "")  $sfl = "a.it_name";

if (!$sst)  {
    $sst  = "a.it_id";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";

$sql_common = " 
	from 
		{$g4['yc4_item_table']} a
		left join
		yc4_category_item b on a.it_id = b.it_id
		left join
		shop_category c on b.ca_id like concat(c.ca_id,'%')
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
                 a.it_type1,
                 a.it_type2,
                 a.it_type3,
                 a.it_type4,
                 a.it_type5
          $sql_common
          $sql_order
          limit $from_record, $rows ";
$result = sql_query($sql);


//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
//$qstr  = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
$qstr  = "$qstr&sca=$sca&page=$page&save_stx=$stx";

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

<form name=flist style="margin:0px;">
<table width=100% cellpadding=4 cellspacing=0>
<input type=hidden name=doc   value="<? echo $doc ?>">
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=sort2 value="<? echo $sort2 ?>">
<input type=hidden name=page  value="<? echo $page ?>">
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
        <script> document.flist.sca.value = '<?=$sca?>';</script>

        <select name=sfl>
            <option value='it_name'>상품명
            <option value='it_id'>상품코드
        </select>
        <? if ($slf) echo "<script> document.flist.slf.value = '$sfl';</script>"; ?>

        <input type=text name=stx value='<? echo $stx ?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=10% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>


<form name=fitemtypelist method=post action="./itemtypelistupdate.php" style="margin:0px;">
<input type=hidden name=sca  value="<?=$sca?>">
<input type=hidden name=sst  value="<?=$sst?>">
<input type=hidden name=sod  value="<?=$sod?>">
<input type=hidden name=sfl  value="<?=$sfl?>">
<input type=hidden name=stx  value="<?=$stx?>">
<input type=hidden name=page value="<?=$page?>">
<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=80>
<colgroup width=80>
<colgroup width=''>
<colgroup width=80>
<colgroup width=80>
<colgroup width=80>
<colgroup width=80>
<colgroup width=80>
<colgroup width=40>
<tr><td colspan=9 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td><?=subject_sort_link("it_id", $qstr, 1)?>상품코드</a></td>
    <td colspan=2><?=subject_sort_link("it_name")?>상품명</a></td>
    <td><?=subject_sort_link("it_type1", $qstr, 1)?>히트상품</a></td>
    <td><?=subject_sort_link("it_type2", $qstr, 1)?>추천상품</a></td>
    <td><?=subject_sort_link("it_type3", $qstr, 1)?>신규상품</a></td>
    <td><?=subject_sort_link("it_type4", $qstr, 1)?>인기상품</a></td>
    <td><?=subject_sort_link("it_type5", $qstr, 1)?>할인상품</a></td>
    <td>수정</td>
</tr>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";

    $s_mod = icon("수정", "./itemform.php?w=u&it_id=$row[it_id]&ca_id=$row[ca_id]&$qstr");

    $list = $i%2;
    echo "
    <input type='hidden' name='it_id[$i]' value='$row[it_id]'>
    <tr class='list$list center'>
        <td>$row[it_id]</td>
        <td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50)."</a></td>
        <td align=left><a href='$href'>".cut_str(stripslashes($row[it_name]), 60, "&#133")."</a></td>
        <td><input type=checkbox name='it_type1[$i]' value='1' ".($row[it_type1] ? 'checked' : '')."></td>
        <td><input type=checkbox name='it_type2[$i]' value='1' ".($row[it_type2] ? 'checked' : '')."></td>
        <td><input type=checkbox name='it_type3[$i]' value='1' ".($row[it_type3] ? 'checked' : '')."></td>
        <td><input type=checkbox name='it_type4[$i]' value='1' ".($row[it_type4] ? 'checked' : '')."></td>
        <td><input type=checkbox name='it_type5[$i]' value='1' ".($row[it_type5] ? 'checked' : '')."></td>
        <td>$s_mod</td>
    </tr>";
}

if (!$i)
    echo "<tr><td colspan=9 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>";
?>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <td colspan=50%><input type=submit class=btn1 value='일괄수정' accesskey='s'></td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</form>
</table><br>

* 상품의 유형을 일괄 처리합니다.

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

<?
$sub_menu = "500300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "이벤트일괄처리";
include_once ("$g4[admin_path]/admin.head.php");

$where = " where ";
$sql_search = "";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " $where a.$sel_field like '%$search%' ";
        $where = " and ";
    }
}

$tmp_ca_id = $_GET['sel_ca_id'];
$result_child_ca_id = array_pop($tmp_ca_id);

if ($result_child_ca_id != "") {
	$sql_search .= $where. " b.ca_id like '$result_child_ca_id%' ";
	$where = ' and ';
}

if ($sel_field == "")  {
    $sel_field = "a.it_name";
}



$sql_common = "
	from
		{$g4['yc4_item_table']} a
		left join
		yc4_category_item b on a.it_id = b.it_id
		left join
		shop_category c on b.ca_id like concat(c.ca_id,'%')
		left join
		$g4[yc4_event_item_table] d on (a.it_id=d.it_id and d.ev_id='$ev_id')
";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(distinct a.it_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sort1) {
    $sort1 = "d.ev_id";
}

if (!$sort2) {
    $sort2 = "desc";
}

$sql  = " select distinct a.it_id,a.*, d.ev_id
          $sql_common
          order by $sort1 $sort2
          limit $from_record, $rows ";

$result = sql_query($sql);

//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
$qstr1 = "ev_id=$ev_id&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
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

<form name=flist autocomplete='off' style="margin:0px;">
<table width=100% cellpadding=4 cellspacing=0>
<input type=hidden name=page value="<? echo $page ?>">
<tr>
    <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=20% align=center>
	    <?
        // 이벤트 옵션처리
        $event_option = "<option value=''>이벤트를 선택하세요";
        $sql1 = " select ev_id, ev_subject from $g4[yc4_event_table] order by ev_id desc ";
        $result1 = sql_query($sql1);
        while ($row1=mysql_fetch_array($result1))
            $event_option .= "<option value='$row1[ev_id]'>".conv_subject($row1[ev_subject], 20,"…");

        echo "<select name='ev_id' onchange='this.form.submit();'>$event_option</select>";
        if ($ev_id)
            echo "<script> document.flist.ev_id.value = '$ev_id'; </script>";
        ?>
	</td>
    <td width=60% align=center>
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
        <option value='it_name'>상품명
        <option value='it_id'>상품코드
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo $search ?>' size=10>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=10% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>
</form>


<form name=fitemeventlistupdate method=post action="./itemeventlistupdate.php" onsubmit="return fitemeventlistupdatecheck(this)" style="margin:0px;">
<input type=hidden name=ev_id      value="<? echo $ev_id ?>">
<input type=hidden name=sel_ca_id  value="<? echo $sel_ca_id ?>">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search     value="<? echo $search ?>">
<input type=hidden name=page       value="<? echo $page ?>">
<input type=hidden name=sort1      value="<? echo $sort1 ?>">
<input type=hidden name=sort2      value="<? echo $sort2 ?>">
<table cellpadding=0 cellspacing=0 width=100% border=0>
<colgroup width=100>
<colgroup width=100>
<colgroup width=80>
<colgroup width=''>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>이벤트사용</td>
    <td><a href='<? echo title_sort("a.it_id") . "&$qstr1&ev_id=$ev_id"; ?>'>상품코드</a></td>
    <td width='' colspan=2><a href='<? echo title_sort("it_name") . "&$qstr1&ev_id=$ev_id"; ?>'>상품명</a></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";

    $sql = " select ev_id from $g4[yc4_event_item_table]
              where it_id = '$row[it_id]'
                and ev_id = '$ev_id' ";
    $ev = sql_fetch($sql);

    $list = $i%2;
    echo "
    <input type='hidden' name='it_id[$i]' value='$row[it_id]'>
    <tr class='list$list center'>
        <td><input type=checkbox name='ev_chk[$i]' ".($row[ev_id] ? "checked" : "")." value='1'></td>
        <td><a href='$href'>$row[it_id]</a></td>
        <td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50)."</a></td>
        <td align=left><a href='$href'>".cut_str(stripslashes($row[it_name]), 60, "&#133")."</a></td>
    </tr>";
}

if ($i == 0)
    echo "<tr><td colspan=4 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 한건도 없습니다.</span></td></tr>";
?>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <td colspan=50%><input type=submit class=btn1 value='일괄수정' accesskey='s'></td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</form>
</table><br>

* 상품을 이벤트별로 일괄 처리합니다.

<script language="JavaScript">
function fitemeventlistupdatecheck(f)
{
    if (!f.ev_id.value)
    {
        alert('이벤트를 선택하세요');
        document.flist.ev_id.focus();
        return false;
    }

    return true;
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

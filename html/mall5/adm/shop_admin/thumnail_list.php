<?
$sub_menu = "400300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "썸네일 리스트";
include_once ("$g4[admin_path]/admin.head.php");


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
	$sql_search .= " $where b.ca_id like '$result_child_ca_id%' ";
	$where = " and ";
}


if($_GET['sel_s_id']){
	$sql_search .= " $where c.s_id = '".$_GET['sel_s_id']."' ";
	$where = " and ";
}


if ($sfl == "")  $sfl = "a.it_name";

$sql_common = " from $g4[yc4_item_table] a ,
                     $g4[yc4_category_table] b
               where (a.ca_id = b.ca_id";
$sql_common = "
	from
		{$g4['yc4_item_table']} a
		left join
		yc4_category_item b on a.it_id = b.it_id
		left join
		shop_category c on b.ca_id like concat(c.ca_id,'%')
";
if ($is_admin != 'super')
//    $sql_common .= " and b.ca_mb_id = '$member[mb_id]'";
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;

$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "a.it_id";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";


$sql  = " select a.*
           $sql_common
           $sql_order
           limit $from_record, $rows ";

$result = sql_query($sql);

//$qstr  = "$qstr&sca=$sca&page=$page";
$qstr  = "$qstr&sca=$sca&page=$page&save_stx=$stx&sel_s_id=".$_GET['sel_s_id'];

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

<table width=100% cellpadding=4 cellspacing=0>
<form name=flist>
<input type=hidden name=page value="<?=$page?>">
<tr>
    <td width=20%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
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

					$ca_child_qry = sql_query("
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
            <option value='it_name' <?=$_GET['sfl'] == 'it_name' ? "selected":""?>>상품명</option>
            <option value='it_id' <?=$_GET['sfl'] == 'it_id' ? "selected":""?>>상품코드</option>
			<option value='SKU' <?php echo $sfl == 'SKU' ? "selected":"";?>>SKU</option>
            <option value='it_maker' <?=$_GET['sfl'] == 'it_maker' ? "selected":""?>>제조사</option>
            <option value='it_origin' <?=$_GET['sfl'] == 'it_origin' ? "selected":""?>>원산지</option>
            <option value='it_sell_email' <?=$_GET['sfl'] == 'it_sell_email' ? "selected":""?>>판매자 e-mail</option>
        </select>

        <input type=hidden name=save_stx value='<?=$stx?>'>
        <input type=text name=stx value='<?=$stx?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100% border=0>
<tr><td colspan=13 height=2 bgcolor=0E87F9></td></tr>
<tr align=center class=ht>
    <td width=70>상품코드</td>
    <td width='210'>상품이미지</td>
	<td width='100'>이미지명</td>
	<td width=''>상품명</td>
    
</tr>
<tr><td colspan=13 height=1 bgcolor=#CCCCCC></td></tr>
</form>



<?
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";

    

    $list = $i%2;
    echo "
    <tr class='list$list'>
        <td>$row[it_id]</td>
        <td style='padding-top:5px; padding-bottom:5px;'>".get_it_image("{$row[it_id]}_s", 200, 200,$row['it_id'],null,false,true,true)."</td>
		<td align=left>{$row[it_id]}_s</td>
        <td align=left>".htmlspecialchars2(cut_str($row[it_name],250, ""))."</td>
        
    </tr>";
}
if ($i == 0)
    echo "<tr><td colspan=4 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 한건도 없습니다.</span></td></tr>";
?>
<tr><td colspan=13 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <td width=50%></td>
    <td width=50% align=right><?=get_paging(10, $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

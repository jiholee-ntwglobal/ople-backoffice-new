<?
$sub_menu = "700400";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "상품문의";
include_once ("$g4[admin_path]/admin.head.php");

$sql_search = " where 1 ";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}

$tmp_ca_id = $_GET['sel_ca_id'];
$result_child_ca_id = array_pop($tmp_ca_id);

if ($result_child_ca_id != "") {
	$sql_search .= " and b.ca_id like '$result_child_ca_id%' ";
}


if ($sel_field == "")  $sel_field = "it_name";
if (!$sort1) $sort1 = "iq_id";
if (!$sort2) $sort2 = "desc";

$sql_common = "  from 
				 $g4[yc4_item_qa_table] a
                 left join $g4[yc4_item_table] b on (a.it_id = b.it_id)
                 left join $g4[member_table] c on (a.mb_id = c.mb_id) ";

$sql_common = " 
	from 
		$g4[yc4_item_qa_table] d
		left join
		{$g4['yc4_item_table']} a on a.it_id = d.it_id
		left join 
		$g4[member_table] e on (d.mb_id = e.mb_id)
		left join
		yc4_category_item b on a.it_id = b.it_id
		left join
		shop_category c on b.ca_id like concat(c.ca_id,'%')
";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " 
	select sum(cnt) as cnt from (
		select count(*) as cnt " . $sql_common ."group by iq_id
	) t
	";

$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " 
	select 
		a.it_id,a.it_name,
		d.iq_id,d.mb_id,d.iq_name,d.iq_subject,d.iq_question,d.iq_answer
	$sql_common
	group by iq_id
	order by $sort1 $sort2, iq_id desc
	limit $from_record, $rows 
";

$result = sql_query($sql);

$qstr = "page=$page&sort1=$sort1&sort2=$sort2";

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
        <script> document.flist.sel_ca_id.value = '<?=$sel_ca_id?>';</script>

        <select name=sel_field>
            <option value='it_name'>상품명
            <option value='a.it_id'>상품코드
        </select>
        <? if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo $search ?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=10% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>

<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=80>
<colgroup width=''>
<colgroup width=100>
<colgroup width=250>
<colgroup width=50>
<colgroup width=80>
<tr><td colspan=6 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td></td>
    <td><a href='<? echo title_sort("it_name"); ?>'>상품명</a></td>
    <td><a href='<? echo title_sort("mb_name"); ?>'>이름</a></td>
    <td><a href='<? echo title_sort("iq_subject"); ?>'>질문</a></td>
    <td><a href='<? echo title_sort("iq_answer"); ?>'>답변</a></td>
    <td>수정 삭제</td>
</tr>
<tr><td colspan=6 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $row[iq_subject] = cut_str($row[iq_subject], 30, "...");

    $href = "$g4[shop_path]/item.php?it_id=$row[it_id]";

    $name = get_sideview($row[mb_id], $row[iq_name], $row[mb_email], $row[mb_homepage]);

    $s_mod = icon("수정", "./itemqaform.php?w=u&iq_id=$row[iq_id]&$qstr");
    $s_del = icon("삭제", "javascript:del('./itemqaformupdate.php?w=d&iq_id=$row[iq_id]&$qstr');");

    $answer = $row[iq_answer] ? "Y" : "&nbsp;";

    $list = $i%2;
    echo "
    <tr class='list$list'>
        <td align=center style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50)."</a></td>
        <td><a href='$href'>".cut_str($row[it_name],30)."</a></td>
        <td align=center>$name</td>
        <td>$row[iq_subject]</td>
        <td align=center>$answer</td>
        <td align=center>$s_mod $s_del</td>
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=6 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan=6 height=1 bgcolor=#CCCCCC></td></tr>
</table>


<table width=100%>
<tr>
    <td width=50%>&nbsp;</td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

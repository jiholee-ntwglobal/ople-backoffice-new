<?
$sub_menu = "300100";
include_once("./_common.php");
//error_reporting(E_ALL);
auth_check($auth[$sub_menu], "r");

$g4[title] = "상품관리";

/*
// 분류
$ca_list  = "";
$sql = " select * from $g4[yc4_category_table] ";
if ($is_admin != 'super')
    $sql .= " where ca_mb_id = '$member[mb_id]' ";
$sql .= " order by ca_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row[ca_id]) / 2 - 1;
    $nbsp = "";
    for ($i=0; $i<$len; $i++) {
        $nbsp .= "&nbsp;&nbsp;&nbsp;";
    }
    $ca_list .= "<option value='$row[ca_id]'>$nbsp$row[ca_name]";
}
$ca_list .= "</select>";
*/


$where = " where ";
$sql_search = "";
$sql_search .= $where."
	a.it_id = b.it_id
	and
	b.ca_id like concat(c.ca_id,'%')
";
$where = "and";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where a.$sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}
//엑셀 다운로드 곽범석 2016-10-26 버튼생성
$excel_button='';
if($_GET['sel_s_id']){

    $excel_button = "<button class='btn btn-danger' onclick=\"location.href=".$_SERVER['PHP']."'itemlist.php?".http_build_query($_GET)."&mode=excel_download'\" type='button'>상품리스트 다운로드</button>";
}


if($_GET['sel_ca_id']){
    $tmp_ca_id = $_GET['sel_ca_id'];
    $result_child_ca_id = array_pop($tmp_ca_id);

    if ($result_child_ca_id != "") {
        $sql_search .= " $where b.ca_id like '$result_child_ca_id%' ";
        $where = " and ";
    }
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
		,
		yc4_category_item b
		,
		shop_category c
";

//if ($is_admin != 'super')
//    $sql_common .= " and b.ca_mb_id = '$member[mb_id]'";
//$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
//$sql = " select count(*) as cnt from (select 1 as cnt " . $sql_common ." group by a.it_id ) t";
$sql = " select count(distinct a.it_id) as cnt " . $sql_common ."";
//엑셀 다운로드 곽범석 2016-10-26 버튼생성
if($_GET['mode']=='excel_download'){

    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = 'category_item_list'.date('Y-m-d');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('it_id', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('UPC', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('item_name', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('판매가(USD)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('판매가(KRW)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('판매상태', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('G1')->setValueExplicit('품절여부', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('H1')->setValueExplicit('단종여부', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('I1')->setValueExplicit('통관구분', PHPExcel_Cell_DataType::TYPE_STRING);

    $excel_sql ='
SELECT DISTINCT d.upc,
                a.it_id,
                a.it_amount,
                a.it_amount_usd,
                a.it_use,
                a.it_name,
                a.it_stock_qty,
                a.it_discontinued,
                a.list_clearance
FROM yc4_item             a,
     yc4_category_item    b,
     shop_category        c,
     ople_mapping         d
  '.$sql_search.' 
  and d.it_id = b.it_id
  ORDER BY b.ca_id,a.it_id DESC
  ';
    $excel_result = sql_query($excel_sql);
    $return_arr = array();
    while($row = sql_fetch_array($excel_result)){
        $return_arr[] = $row;
    }
    $line = 2;
    foreach($return_arr as $value){

        $objPHPExcel->getActiveSheet()->getCell('A'.$line)->setValueExplicit($value['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B'.$line)->setValueExplicit($value['upc'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C'.$line)->setValueExplicit($value['it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D'.$line)->setValueExplicit($value['it_amount_usd'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E'.$line)->setValueExplicit($value['it_amount'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('F'.$line)->setValueExplicit($value['it_use']=='1'?'판매':"판매정지", PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('G'.$line)->setValueExplicit($value['it_stock_qty']>0?'재고있음':"품절", PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('H'.$line)->setValueExplicit($value['it_discontinued']=='1'?'단종':"단종아님", PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('I'.$line)->setValueExplicit($value['list_clearance']=='Y'?'목록':"일반", PHPExcel_Cell_DataType::TYPE_STRING);
        $line++;
    }
    $objPHPExcel->getActiveSheet()->setTitle($excel_title);
    $filename = iconv("UTF-8", "EUC-KR", $excel_title);

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}


$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
$page =is_numeric($_GET['page'])? $_GET['page'] : '';
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "a.it_id";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";


$sql  = "
	select a.*
           $sql_common
		   group by a.it_id
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = "$qstr&sca=$sca&page=$page";
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
include_once ("$g4[admin_path]/admin.head.php");
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
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;&nbsp;&nbsp;<?php echo $excel_button;?></td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100% border=0>
<tr><td colspan=13 height=2 bgcolor=0E87F9></td></tr>
<tr align=center class=ht>
    <td width=70><?=subject_sort_link("it_id", "sca=$sca")?>상품코드</a></td>
    <td width='' colspan=2><?=subject_sort_link("it_name", "sca=$sca")?>상품명</a></td>
    <td width=70><?=subject_sort_link("it_amount_usd", "sca=$sca")?>판매가(USD)</a></td>
    <td width=70><?=subject_sort_link("it_amount", "sca=$sca")?>판매가(KRW)</a></td>
    <td width=70><?=subject_sort_link("it_point", "sca=$sca")?>포인트</a></td>
    <td width=30><?=subject_sort_link("it_stock_qty", "sca=$sca")?>재고</a></td>
    <td width=30><?=subject_sort_link("it_use", "sca=$sca", 1)?>판매</a></td>
    <td width=80><a href='./itemform.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0 title='상품등록'></a></td>
</tr>
<tr><td colspan=13 height=1 bgcolor=#CCCCCC></td></tr>
</form>


<?
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";

    $s_mod = icon("수정", "./itemform.php?w=u&it_id=$row[it_id]&ca_id=$row[ca_id]&$qstr");
//    $s_del = icon("삭제", "javascript:del('./itemformupdate.php?w=d&it_id=$row[it_id]&ca_id=$row[ca_id]&$qstr');");
    $s_del = ""; // 상품삭제버튼 미노출처리 2017-03-08 강경인
    $s_vie = icon("보기", $href);

    $gallery = $row[it_gallery] ? "Y" : "";

    //$tmp_ca_list  = "<select id='ca_id_$i' name='ca_id[$i]'>" . $ca_list;
    $tmp_ca_list .= "<script language='javascript'>document.getElementById('ca_id_$i').value='$row[ca_id]';</script>";

    $list = $i%2;
    echo "

    <tr class='list$list'>
        <td>$row[it_id]</td>
        <td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50,$row['it_id'],null,false,true,true)."</a></td>
        <td align=left>".get_item_name($row['it_name'],'list')."</td>
        <td>$ ".number_format($row['it_amount_usd'],2)."</td>
        <td>￦ ".number_format($row['it_amount'])."</td>
        <td>".number_format($row['it_point'])."</td>
        <td>".(get_it_stock_qty($row['it_id']) ? number_format(get_it_stock_qty($row['it_id'])) : '품절')."</td>
        <td>".($row[it_use] ? "판매" : "판매정지")."</td>
        <td>$s_mod $s_del $s_vie</td>
    </tr>";
}
if ($i == 0)
    echo "<tr><td colspan=20 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 한건도 없습니다.</span></td></tr>";
?>
<tr><td colspan=13 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <?php unset($_GET['page']);
        $qstr= http_build_query($_GET);
    ?>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>


<?
include_once ("$g4[admin_path]/admin.tail.php");

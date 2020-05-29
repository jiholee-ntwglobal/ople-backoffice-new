<?php
/*
----------------------------------------------------------------------
file name	 : clearance_list.php
comment		 : 클리어런스 상품 리스트
date		 : 2015-02-26
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/
$sub_menu = "300880";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if($_GET['excel']=="download"){
    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = 'cleance_item_' . date('Ymd');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('상품코드', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('제품명', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('가격', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('MSRP', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('할인율', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('입력재고', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('G1')->setValueExplicit('판매량', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('H1')->setValueExplicit('잔여재고', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('I1')->setValueExplicit('품절시간', PHPExcel_Cell_DataType::TYPE_STRING);


    $sql = "select c.*,if(a.it_stock_qty <=0,0,1) as cnt , a.it_name, a.it_amount
            from yc4_item a 
            left join yc4_event_item b on (a.it_id=b.it_id) 
            left join yc4_clearance_item c on c.it_id = a.it_id
            where b.ev_id = '1424920190' 
            and a.it_use = '1' 
            order by cnt desc, b.sort asc, a.it_order, a.it_id desc";
    $result = sql_query($sql);
    $excel_item_data = array();
    $line = 2;
    while ($excel_item_data_row = sql_fetch_array($result)) {
        $it_amount = "￦ ".number_format($excel_item_data_row['it_amount'])." ($ ".usd_convert($excel_item_data_row['it_amount']).")";
        $it_msrp = "￦ ".number_format($excel_item_data_row['msrp']*$default['de_conv_pay'],-2)." ($ ".number_format($excel_item_data_row['msrp'],2).")";
        $it_sale = get_dc_percent(usd_convert($excel_item_data_row['it_amount']),$excel_item_data_row['msrp'])."%";

        $objPHPExcel->getActiveSheet()->getCell('A' . $line)->setValueExplicit($excel_item_data_row['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B' . $line)->setValueExplicit($excel_item_data_row['it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C' . $line)->setValueExplicit($it_amount, PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D' . $line)->setValueExplicit($it_msrp, PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E' . $line)->setValueExplicit($it_sale, PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('F' . $line)->setValueExplicit($excel_item_data_row['qty'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('G' . $line)->setValueExplicit($excel_item_data_row['sell_qty'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('H' . $line)->setValueExplicit(($excel_item_data_row['qty']-$excel_item_data_row['sell_qty']), PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('I' . $line++)->setValueExplicit($excel_item_data_row['soldout_dt'], PHPExcel_Cell_DataType::TYPE_STRING);

    }

    $filename = iconv("UTF-8", "EUC-KR", $excel_title);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

$g4[title] = "클리어런스 상품 리스트";
include_once ("$g4[admin_path]/admin.head.php");

$sql_search = '';

switch($_GET['fg']){
	case 'A' :
		break;
	case 'N' :
		$sql_search .= " and a.qty-a.sell_qty <= 0";
		break;
	default :
		$sql_search .= " and a.qty-a.sell_qty > 0";
		break;
}

$cnt_sql = sql_fetch("
	select
		count(*) as cnt
	from
		yc4_clearance_item a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		".$sql_search."
");
$total_count = $cnt_sql['cnt'];


$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = sql_query($a="
	select
		a.* ,
		b.it_name,
		b.it_amount
	from
		yc4_clearance_item a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		".$sql_search."
	limit ".$from_record.", ".$rows."
");

$list_tr = '';
while($row = sql_fetch_array($sql)){
	$list_tr .= "
		<tr>
			<td>".$row['it_id']."</td>
			<td>".get_it_image($row['it_id'].'_s',80,80,$row['it_id'])."</td>
			<td>".$row['it_name']."</td>
			<td>
			    ￦ ".number_format($row['it_amount'])." ($ ".usd_convert($row['it_amount']).")<br/>
			    ￦ ".number_format($row['msrp']*$default['de_conv_pay'],-2)." ($ ".number_format($row['msrp'],2).")<br/>
			    ".get_dc_percent(usd_convert($row['it_amount']),$row['msrp'])."%
            </td>
			<td>".$row['qty']."<br/>".$row['sell_qty']."<br/>".($row['qty']-$row['sell_qty'])."</td>
			<td>".$row['soldout_dt']."</td>
			<td>".icon('수정',$g4['shop_admin_path'].'/clearance_write.php?it_id='.$row['it_id'])." ".icon('삭제','#','',"onclick=\"item_del('".$row['it_id']."'); return false;\"")."</td>
		</tr>
	";
}

$qstr = $_GET;
unset($qstr['page']);
$qstr = http_build_query($qstr);

?>

<style type="text/css">
.list_tab{
	list-style:none;
	margin:0px;
	overflow:hidden;
}
.list_tab > li{
	float:left;
	padding:5px;
	border:1px solid #dddddd;
}
.list_tab > li.active{
	font-weight:bold;
}
</style>
    <div>
        <button class="btn btn-success" type="button" onclick="location.href='./clearance_list.php?excel=download'">엑셀다운로드</button>
    </div>
<ul class='list_tab'>
	<li class='<?=!$_GET['fg'] ?"active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>">진행</a></li>
	<li class='<?=$_GET['fg'] == 'N' ?"active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?fg=N">종료</a></li>
	<li class='<?=$_GET['fg'] == 'A' ?"active":"";?>'><a href="<?php echo $_SERVER['PHP_SELF'];?>?fg=A">전체</a></li>
</ul>
<table style='border-collapse: collapse;' border='1'>
	<col width='70'/>
	<col width='85'/>
	<col width=''/>
	<col width='120'/>
	<col width='80'/>
	<col width='100'/>
	<col width='50'/>
	<tr align="center">
		<td>상품코드</td>
		<td>이미지</td>
		<td>제품명</td>
		<td>가격<br/>MSRP<br/>할인율</td>
		<td>입력재고<br/>판매량<br/>잔여재고</td>
		<td>품절시간</td>
		<td><?php echo icon('입력',$g4['shop_admin_path'].'/clearance_write.php')?></td>
	</tr>
	<?php echo $list_tr;?>
</table>
<?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?>

<script type="text/javascript">
	function item_del(it_id){
		if(!confirm('클리어런스 상품을 삭제하시겠습니까?')){
			return false;
		}
	}
</script>

<?php
include_once ("$g4[admin_path]/admin.tail.php");
?>
<?php
/**
 * Created by Eclipse
 * User: kyung-in
 * Date: 2015.10.27
 * file: it_id_change_his.php
 */

$mode	= isset($_POST['mode']) ?  $_POST['mode'] : "none";


// 검색화면
if($mode == "dt_chek"){
//	//	날짜로

$sql	= "
		SELELCT
			it_id
		,	bf_it_id
		,	dt
//		,	처리날짜
		FROM
			yc4_it_id_change_history
		ORDER BY
			dt DESC, bf_it_id DESC
";

$res	= mysql_query($sql, $conn);

while($row=mysql_fetch_assoc($res)){
	$in_it_id	.= ", '".trim($row['it_id'])."'";
	$arr_it[trim($row['bf_it_id'])]	= trim($row['it_id']);
}
$in_it_id	= substr($in_it_id, 2);

//ms
	$sql_ms	= "
			SELECT	c.it_id
				,	a.upc
				,	b.mfgname
				,	a.ITEM_NAME
				,	a.type
				,	a.count
			FROM	ople_mapping	c
				,	N_MASTER_ITEM	a
				,	N_MFG			b
			WHERE	a.mfgcd = b.mfgcd
			AND		a.upc = c.upc
			AND		c.it_id in (".$in_it_id.")
			ORDER BY	c.it_id ASC
		";

	$res_ms	= mssql_query($sql_ms,$ms_db_conn);





}elseif($mode == "id_chek"){
//	//	it_id

	$a_it_id	= explode("\n",$_POST['it_id']);
	$in_it_id	= "";
	var_dump($a_it_id);
	foreach($a_it_id as $val){
		$in_it_id	.= ", '".trim($val)."'";
	}
	$in_it_id	= substr($in_it_id, 2);

	echo $in_it_id;

	$sql	= "
			SELECT	ifnull(it_id,'처리대기') as it_id
				,	bf_it_id
			FROM	yc4_it_id_change_history
			WHERE	bf_it_id in (".$in_it_id.")
				AND	date_format(dt,'%Y-%m-%d')='2015-10-16'
			ORDER BY	bf_it_id	ASC
		";

	echo $sql;
	$res	= mysql_query($sql,$db_conn_test);
	while($row	= mysql_fetch_assoc($res)){
		$arr_it[trim($row['bf_it_id'])]	= trim($row['it_id']);
	}
	
	 var_dump($arr_it);

	$sql_ms	= "
			SELECT	c.it_id
				,	a.upc
				,	b.mfgname
				,	a.ITEM_NAME
				,	a.type
				,	a.count
			FROM	ople_mapping	c
				,	N_MASTER_ITEM	a
				,	N_MFG			b
			WHERE	a.mfgcd = b.mfgcd
			AND		a.upc = c.upc
			AND		c.it_id in (".$in_it_id.")
			ORDER BY	c.it_id ASC
		";

	$res_ms	= mssql_query($sql_ms,$ms_db_conn);

	echo $sql_ms;




}elseif($mode == "down"){
// 엑셀출력기능


		
	
	require_once("C:\inetpub\wwwroot\phpexcel/PHPExcel.php");
	$objPHPExcel = new PHPExcel();
	
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue("A1", "이전 상품코드")
				->setCellValue("B1", "새 상품코드")
				->setCellValue("C1", "upc")
				->setCellValue("D1", "브랜드")
				->setCellValue("E1", "아이템이름")
				->setCellValue("F1", "타입")
				->setCellValue("G1", "count");
	$n	= '1';
	foreach($list_data as $row){
		$n++;
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueExplicit("A".$n, trim($['']), PHPExcel_Cell_DataType::TYPE_STRING)
					->setCellValueExplicit("B".$n, trim($['']), PHPExcel_Cell_DataType::TYPE_STRING)
					->setCellValueExplicit("C".$n, trim($['']), PHPExcel_Cell_DataType::TYPE_STRING)
					->setCellValue("D".$n, trim($['']))
					->setCellValue("E".$n, trim($['']))
					->setCellValue("F".$n, trim($['']))
					->setCellValue("G".$n, trim($['']));
			$n++;
	}
	$objPHPExcel->getActiveSheet()->setTitle("mapping_request");
//	$objPHPExcel->setActiveSheetIndex(0);
//	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(11);
//	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(13);
//	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
//	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
//	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(22);
//	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(85);
//	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
//	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
		
	$file_name = "매핑요청_".date("Ymd_His").".xlsx";
	$file_name = iconv("UTF-8", "EUC-KR", $file_name);

	header('Content-type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename="'.$file_name.'"');
	header('Cache-Control: max-age=0');
	 
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;	




}elseif($mode == "none"){
// 기본화면(리스트)
$sql	= "
		SELELCT
			it_id
		,	bf_it_id
		,	dt
		FROM
			yc4_it_id_change_history
		ORDER BY
			dt DESC
";
$res	= mysql_query($sql);

while($row=mysql_fetch_assoc($res)){
	$in_it_id	.= ", '".trim($row['it_id'])."'";
	$arr_it[trim($row['bf_it_id'])]	= trim($row['it_id']);
}

$sql_ms	= "
			SELECT	c.it_id
				,	a.upc
				,	b.mfgname
				,	a.ITEM_NAME
				,	a.type
				,	a.count
			FROM	ople_mapping	c
				,	N_MASTER_ITEM	a
				,	N_MFG			b
			WHERE	a.mfgcd = b.mfgcd
			AND		a.upc = c.upc
			AND		c.it_id in (".$in_it_id.")
			ORDER BY	c.it_id ASC
		";

	$res_ms	= mssql_query($sql_ms,$ms_db_conn);
?>

<table>
	<tr>
		<td>이전 it_id</td>
		<td>새 it_id</td>
		<td>upc</td>
		<td>브랜드</td>
		<td>상품명</td>
		<td>타입</td>
		<td>count</td>
	</tr>

<?php	while($row_ms = mssql_fetch_assoc($res_ms)){
?>
	<tr>
		<td><?php echo $row_ms['it_id']; ?></td>
		<td><?php echo isset($arr_it[$row_ms['it_id']]) ? $arr_it[$row_ms['it_id']] : "처리대기"; ?></td>
		<td><?php echo $row_ms['upc']; ?></td>
		<td><?php echo $row_ms['mfgname']; ?></td>
		<td><?php echo $row_ms['ITEM_NAME']; ?></td>
		<td><?php echo $row_ms['type']; ?></td>
		<td><?php echo $row_ms['count']; ?></td>
	</tr>
		
<?php	}?>
</table>


<?php
}else{
	echo "history.back();";
}
<?php
/**
 * Created by Eclipse
 * User: kyung-in
 * Date: 2015.09.09
 * file: test_2/ev_result.php
 */
$sub_menu = "500800";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "이벤트통계관리";




// 테스트 디비에서 값받아오기
$seqno	= $_GET['seqno'];
$sql	= "SELECT
			*
		FROM
			event_research
		WHERE
			seqno=".$seqno."
";
$res	= mysql_query($sql);
$row	= mysql_fetch_assoc($res);
$name	= $row['ev_name'];
$query	= $row['ev_query'];
$vmode	= $row['stat'];
($vmode == "Y") ? $vmode = "N" : $vmode = "Y";
$list_path	= "./eventresearch_list.php?vmode=".$vmode;

// 메인디비에서 출력값 받기
$res2	= mysql_query($query);
if(!$res2){
	echo "
		<script type=text/JavaScript>
			alert('등록된 쿼리가 올바르지 않습니다.')
			location.replace('".$list_path."')
		</script>
	";
}
// 컬럼의 갯수
$field_no	= mysql_num_fields($res2);
// 데이터 row 갯수
$rows		= mysql_num_rows($res2);
// 데이터 배열에 담
while($row	= mysql_fetch_array($res2)){
	if($row['it_id']){
		$link[$row['it_id']]	= "<a href='http://ople.com/mall5/shop/item.php?it_id=".$row['it_id']."' target='_blank'>".$row['it_id']."</a>";
	}
	$data[]=$row;

}

if(isset($_GET['mode']) && $_GET['mode'] == "down"){
	require_once("../../classes/PHPExcel.php");

	$objPHPExcel = new PHPExcel();
	
	// 컬럼 명 표시
	for($i=0,$k="A"; $i<$field_no; $i++,$k++){
		$field_name[]	= mysql_field_name($res2, $i);
		$n = $k."1";
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($n, $field_name[$i]);
	}
	// 데이터 출력
	for( $i=0; $i<$rows; $i++){
		$r = $i+2;
		for($j=0,$k="A"; $j<$field_no; $j++,$k++){
			$n = $k.$r;
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue($n, is_float($data[$i][$j]) ? floor(trim($data[$i][$j])*100)/100:$data[$i][$j]);
		}
	}

	$file_name = $name.date("Ymd_His").".xls";
	$file_name = iconv("UTF-8", "EUC-KR", $file_name);

	header('Content-type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename="'.$file_name.'"');
	header('Cache-Control: max-age=0');
	 
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
}

include_once ("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<!-- 결과 데이터 테이블 시작 -->
<table class="table table-striped" align="center">
	<tr>
		<th colspan="<?php echo $field_no;?>"><?php echo $name;?></th>
	</tr>
	<tr>
<?php
//	( $i=0,$k='A'; $i<; $i++,$k++)

// 컬럼 명 표시
	for( $i=0; $i<$field_no; $i++){
		$field_name[]	= mysql_field_name($res2, $i);
?>
		<td><?php echo $field_name[$i];?></td>
	<?php }?>
	</tr>
<?php
// 데이터 출력
	for( $i=0; $i<$rows; $i++){
?>
	<tr>
<?php
		for( $j=0; $j<$field_no; $j++){
            $data[$i][$j] = is_numeric(trim($data[$i][$j])) ? floor(trim($data[$i][$j])*100)/100 :  $data[$i][$j];
?>
			<td>
				<?php echo $link[trim($data[$i][$j])] ? $link[trim($data[$i][$j])] : trim($data[$i][$j]);?>
			</td>
		<?php }?>
	</tr>
	<?php }?>
	<tr>
		<td colspan="<?php echo $field_no;?>" align=center>
			<a href="<?php echo $list_path;?>"><input type=button value="리스트"></a>
			<a href="<?php echo $_SERVER['PHP_SELF'];?>?seqno=<?php echo $seqno;?>&mode=down"><input type=button value="엑셀로 다운"></a>
		</td>
	</tr>
</table>
<?php
//var_dump($data);

include_once ("$g4[admin_path]/admin.tail.php");
?>
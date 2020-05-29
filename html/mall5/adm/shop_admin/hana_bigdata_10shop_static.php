<?php /* vim: set ts=4 sw=4 syntax=php fdm=marker: */

/*=====================================================================
* Page		: /ssd/html/mall5/adm/shop_admin/hana_bigdata_10shop_static.php
* Desc		: 하나카드 일별 통계
* Url		: http://66.209.90.21/mall5/adm/shop_admin/hana_bigdata_10shop_static.php?uid=870646
* Code		: @hana_20191206
-----------------------------------------------------------------------
* Comments
-----------------------------------------------------------------------
Date            Auth		Desc
2019-12-09		__KDM__		일별 통계 페이지 추가
=====================================================================*/

/*=====================================================================
* Basic Setting
=====================================================================*/
	$sub_menu = "500560";
	include_once("./_common.php");

	// 관리자 체크
	auth_check($auth[$sub_menu], "r");

	$g4['title'] = "하나빅데이터 일별 통계";
	$ev_uid	= trim($_GET['uid']);
	define('bootstrap', true);

/*=====================================================================
* Main
=====================================================================*/
	// 상품코드 가져오기
	$query = "SELECT
							e.value1, i.it_name
				FROM
							`yc4_event_data` AS e
				LEFT JOIN
							yc4_item AS i
					ON		e.value1 = i.it_id
				WHERE
							`ev_code`='hana_bigdata_2019'
					AND		`ev_data_type`='".$ev_uid."'
				ORDER BY
							CAST(e.value7 as int) ASC
				";
	$res = sql_query($query);
	//var_dump($query);

	$it_id_arr = array();
	while ( $row = mysql_fetch_assoc($res) )
	{
		$it_id_arr[$row['value1']]['it_id']		= $row['value1'];
		$it_id_arr[$row['value1']]['it_name']	= $row['it_name'];
		$it_id_arr[$row['value1']]['sum']		= 0;
		$it_id_arr[$row['value1']]['pc']		= 0;
		$it_id_arr[$row['value1']]['mobile']	= 0;
	}

	if ( count($it_id_arr) <= 0 )
	{
		$error_msg = "상품 리스트가 존재하지 않습니다. TS팀에 문의 주세요.";
	}
	else
	{
		/*=====================================================================
		* 이벤트 기간 날짜들 배열로 생성하기
		=====================================================================*/
		$query = "SELECT * FROM `yc4_event_data` WHERE `ev_code`='hana_bigdata_2019' AND `uid`='".$ev_uid."'";
		$res = sql_query($query);
		$ev_row = mysql_fetch_assoc($res);
		$sdate = new DateTime($ev_row['value1']);
		$edate = new DateTime($ev_row['value2']);
		$edate = $edate->modify('+1 day');
		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($sdate, $interval, $edate);

		/*=====================================================================
		* 날짜 설정
		=====================================================================*/
		if ( empty($_GET['ev_date']) === true )
		{
			if ( strtotime(date('Y-m-d')) > strtotime($ev_row['value2']) )
			{
				$sel_date = date('Y-m-d', strtotime($ev_row['value2']));
			}
			else
			{
				$sel_date = date('Y-m-d');
			}
		}
		else
		{
			$sel_date = trim($_GET['ev_date']);
		}

		/*=====================================================================
		* Validation
		=====================================================================*/
		if ( strtotime(date('Y-m-d')) < strtotime($ev_row['value1']) )
		{
			//$error_msg = "이벤트 진행 전입니다.";
		}
		
		/*=====================================================================
		* 통계 가져오기
		=====================================================================*/
		$total_query = "SELECT
										e.value3, sum(e.value4) as sum
							FROM
										yc4_event_data AS e 
							LEFT JOIN
										yc4_order AS o
								ON
										e.value1 = o.od_id
							WHERE
										e.ev_code = 'hana_bigdata_2019'
								AND		e.ev_data_type = 'od_id' 
								AND		LEFT(e.value1,6) = '".date('ymd', strtotime($sel_date))."' 
								AND		e.value5 is null 
							GROUP BY
										value3";
		$res = sql_query($total_query);
		//var_dump($total_query);
		$total = array();
		while ( $row = mysql_fetch_assoc($res) )
		{
			if ( isset($it_id_arr[$row['value3']]) === true )
			{
				$it_id_arr[$row['value3']]['sum']	= $row['sum'];
				$it_id_arr[$row['value3']]['pc']	= $row['sum'];
			}
		}

		$mobile_query = "SELECT
										e.value3, sum(e.value4) as sum
							FROM
										yc4_event_data e
							LEFT JOIN
										yc4_order o
								ON		e.value1 = o.od_id
							WHERE
										e.ev_code ='hana_bigdata_2019'
								AND		e.ev_data_type = 'od_id'
								AND		LEFT(e.value1,6) = '".date('ymd', strtotime($sel_date))."' 
								AND		e.value5 is null
								AND		o.od_mobile_fg = 'Y'
								AND		o.mobile_fg ='Y'
							GROUP BY
										value3";
		$res = sql_query($mobile_query);
		//var_dump($mobile_query);
		$mobile = array();
		while ( $row = mysql_fetch_assoc($res) )
		{
			if ( isset($it_id_arr[$row['value3']]) === true )
			{
				$it_id_arr[$row['value3']]['pc'] = $it_id_arr[$row['value3']]['pc'] - $row['sum'];
				$it_id_arr[$row['value3']]['mobile'] = $row['sum'];
			}
		}

		// 엑셀 다운로드
		if ( $_GET['excel'] == "download" )
		{
			include_once $g4['full_path'].'/classes/PHPExcel.php';
			$objPHPExcel = new PHPExcel();
			$excel_title = 'hana_bigdata_static_'.date('Ymd');

			$objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
				->setTitle($excel_title)
				->setSubject($excel_title)
				->setDescription($excel_title);
			$objPHPExcelWrite = $objPHPExcel->getActiveSheet();

			$objPHPExcelWrite->setCellValue('A1','No.')
				->setCellValue('B1','날짜')
				->setCellValue('C1','상품코드')
				->setCellValue('D1','상품명')
				->setCellValue('E1','전체 판매수')
				->setCellValue('F1','모바일 판매수')
				->setCellValue('G1','PC 판매수');

			$line = 2;
			foreach ($it_id_arr as $data) {
				$objPHPExcelWrite->getCell('A'.$line)->setValueExplicit($line-1, PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcelWrite->getCell('B'.$line)->setValueExplicit($sel_date, PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcelWrite->getCell('C'.$line)->setValueExplicit($data['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcelWrite->getCell('D'.$line)->setValueExplicit($data['it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcelWrite->getCell('E'.$line)->setValueExplicit($data['sum'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcelWrite->getCell('F'.$line)->setValueExplicit($data['mobile'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcelWrite->getCell('G'.$line)->setValueExplicit($data['pc'], PHPExcel_Cell_DataType::TYPE_NUMERIC);

				$line++;
			}

			$objPHPExcelWrite->setTitle($excel_title);
			unset($objPHPExcelWrite);
			
			$filename = iconv("UTF-8", "EUC-KR", $excel_title);

			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5')
				->setPreCalculateFormulas(false)
				->save('php://output');
			exit;
		}
	}

include '../admin.head.php';
/*=====================================================================
* HTML
=====================================================================*/
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<style>
.pull-right {
float: right !important;
}
</style>
<div class="row" style="margin-bottom: 5px;">
<div class="col-md-12">
	<form name="frm" method="get" style="margin:0px;">
		<input type="hidden" name="uid" value="<?=$ev_uid;?>" />
		<select name="ev_date" onchange="this.form.submit();">
			<?php
			foreach ( $period AS $v )
			{
				$selected = '';
				if ( $sel_date == $v->format("Y-m-d") )
				{
					$selected = 'selected';
				}
			?>
			<option value="<?=$v->format("Y-m-d");?>" <?=$selected;?>><?=$v->format("Y-m-d");?></option>
			<?php
			}
			?>
		</select>
		<!-- 엑셀 다운로드 -->
		<div class="pull-right">
			<button class="btn btn-sm btn-success" type="button" onclick="location.href='./hana_bigdata_10shop_static.php?uid=<?=$ev_uid;?>&ev_date=<?=$sel_date;?>&excel=download'">엑셀다운로드</button>
		</div>
	</form>
</div>
</div>
<!-- 통계 -->
<table class="table table-hover table-bordered table-condensed table-striped">
	<thead>
		<tr>
			<th colspan="12" style="text-align: center;">하나 빅데이터 일별 통계</th>
		</tr>
		<tr>
			<td align="center">No.</td>
			<td align="center">날짜</td>
			<td align="center">상품코드</td>
			<td align="center">상품명</td>
			<td align="center">전체판매수</td>
			<td align="center">모바일 판매수</td>
			<td align="center">PC 판매수</td>
		</tr>
	</thead>
	<tbody>
	<?php
	if ( empty($error_msg) === FALSE )
	{
	?>
		<tr>
			<td colspan="12" style="text-align: center;"><?=$error_msg;?></td>
		</tr>
	<?php
	}
	else
	{
		$i = 1;
		foreach ( $it_id_arr AS $data )
		{
			?>
		<tr>
			<td align="center"><?=$i;?></td>
			<td><?=$sel_date;?></td>
			<td><?=$data['it_id'];?></td>
			<td><?=$data['it_name'];?></td>
			<td><?=number_format($data['sum']);?></td>
			<td><?=number_format($data['mobile']);?></td>
			<td><?=number_format($data['pc']);?></td>
		</tr>
		<?php
			$i++;
		}
	}
		?>
	</tbody>
</table>

<?php
/*=====================================================================
* Include Tail
=====================================================================*/
include_once ("$g4[admin_path]/admin.tail.php");
?>
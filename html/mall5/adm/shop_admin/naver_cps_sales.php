<?
$sub_menu = "800770";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$current_y = date('Y');
$year = $now_date = date("Y",strtotime("-1 year"));
$search_y = ($_GET['year']=="") ? $current_y : $_GET['year'];

$current_m = (int) date('m');
$current_m = ($search_y==$current_y) ? $current_m : 12;

$month = (int) $now_date = date("m",strtotime("-1 year"));
$month = ($search_y==$current_y) ? 1 : $month;
$search_m = ($_GET['month']=="") ? $current_m : $_GET['month'];


$search_month = ($search_m<10) ? "0".$search_m : $search_m;// "201902";
$search_date = $search_y. $search_month;
$search_date = ($search_date=="") ? date("Ym") : $search_date;

// 엑셀 다운로드 추가 : @navercps_20191203
$dstr = "&year=".$search_y."&month=".$search_m;

//통계 쿼리
$res = sql_query($a="
                    select date_format(d.create_date, '%Y-%m-%d') as create_date, count(distinct d.od_id) as od_cnt, sum(amount) - sum(cancel_amount) as order_amount, 
                    sum(tot_amount) - sum(cancel_amount) as tot_amount, sum(cancel_cnt) as cancel_cnt from 
                        (select 
                        n.settlement_date as create_date, n.od_id,
                        TRUNCATE(if
                          (c.ct_status='준비'||c.ct_status='배송'||c.ct_status='완료',
                          (o.od_receipt_bank + o.od_receipt_card + o.od_receipt_point)/o.exchange_rate,0),2) amount, 
                        TRUNCATE(if
                          (c.ct_status='준비'||c.ct_status='배송'||c.ct_status='완료',
                          ((o.od_receipt_bank + o.od_receipt_card - o.od_send_cost)/o.exchange_rate),0),2) tot_amount, 
                        if(c.ct_status='취소',1,0) cancel_cnt ,
                          TRUNCATE(sum(if(c.ct_status='품절' || c.ct_status = '반품', (c.ct_amount*c.ct_qty/o.exchange_rate), 0)),2) cancel_amount 
                        from naver_cps_order n 
                        left join yc4_order o on n.od_id = o.od_id 
                        left join yc4_cart c on o.on_uid = c.on_uid 
                        where c.it_id is not null 
                        and date_format(n.settlement_date, '%Y%m') ='$search_date' 
                        group by n.od_id) d 
                    group by date_format(d.create_date, '%Y%m%d')");
$list_data = array();
while($row=sql_fetch_array($res)){
    $list_data[] = $row;
}

// 엑셀 다운로드 부분 : @navercps_20191203 
if ( $_GET['excel'] == "download" )
{
    include_once $g4['full_path'].'/classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $excel_title = 'naver_cps_sales_'.date('Ymd');

    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);
    $objPHPExcelWrite = $objPHPExcel->getActiveSheet();

    $objPHPExcelWrite->setCellValue('A1','수취일자')
        ->setCellValue('B1','주문건수')
        ->setCellValue('C1','주문총액(USD)')
        ->setCellValue('D1','결제총액(USD)')
        ->setCellValue('E1','4%(USD)')
        ->setCellValue('F1','취소건수');

	$line = 2;
    foreach ($list_data as $val) {
		$amount_4 = round($val['tot_amount'] * 0.04, 2);

		//$objPHPExcelWrite->getCell('A'.$line)->setValueExplicit($val['create_date'], PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcelWrite->setCellValue('A'.$line, $val['create_date'])
			->setCellValue('B'.$line, $val['od_cnt'])
			->setCellValue('C'.$line, $val['order_amount'])
			->setCellValue('D'.$line, $val['tot_amount'])
			->setCellValue('E'.$line, $amount_4)
			->setCellValue('F'.$line, $val['cancel_cnt']);

		$line++;
	}

    $objPHPExcelWrite->setTitle($excel_title);

    unset($objPHPExcelWrite);

    $filename = iconv("UTF-8", "EUC-KR", $excel_title);

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5')
        ->setPreCalculateFormulas(false)
        ->save('php://output');

    exit;
}

// 엑셀 다운로드를 위해 위치 변경 : @navercps_20191203 
$g4[title] = "네이버CPS 통계";
include_once ("$g4[admin_path]/admin.head.php");
?>

<head>
    <!-- 합쳐지고 최소화된 최신 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <!-- 부가적인 테마 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
    <!-- 합쳐지고 최소화된 최신 자바스크립트 -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</head>

<?php echo  subtitle(date('m')."월 네이버 CPS 통계")?>
<br/>

* 최근 1년치만 검색이 가능합니다.
<br/>
* 모든 통계는 고객이 수취 확인한 일자 기준으로 검색가능합니다.
<br/>
* 고객이 미확인시 송장이 나온 일자 기준으로 21일 후 자동 수취확인됩니다.
<br/>
<br/>

<form name="frm" method="get" action="<?php $_SERVER['PHP_SELF']?>">
	<div class="row">
		<div class="col-lg-10">
			기간 :
			<select name="year" onchange="frm.submit()">
				<?php for($i=$year; $i<=$current_y; $i++){?>
					<option  value="<?php echo $i?>" <?php if($i==$search_y) echo "selected"; ?>><?php echo $i?>년</option>
				<?php } ?>
			</select>
			년
			<select name="month" onchange="frm.submit()">
			<?php for($i=$month; $i<=$current_m; $i++){?>
			<option  value="<?php echo $i?>" <?php if($i==$search_m) echo "selected"; ?>><?php echo $i?>월</option>
			<?php } ?>
			</select>
		 </div>

		<!-- 엑셀 다운로드 추가 : @navercps_20191203 -->
		<div class="col-lg-2">
			<?php if (count($list_data)>0) { ?>
				<button class="btn btn-success" type="button" onclick="location.href='./naver_cps_sales.php?excel=download<?php echo $dstr?>'">엑셀다운로드</button>
			<?php } ?>
		</div>
	</div>
</form>
<table width=100% cellpadding=0 cellspacing=1 border=0>
        <tr><td colspan='6' class='line1'></td></tr>
        <tr class='bgcol1 bold col1 ht center'>
            <td>수취일자</td>
            <td>주문건수</td>
            <td>주문총액(USD)</td>
            <td>결제총액(USD)</td>
            <td>4%(USD)</td>
            <td>취소건수</td>
        </tr>
    <?php
    $i=0;
    $list = ($i%2);
    if(count($list_data)>0){
        foreach ($list_data as $val) {
            $amount_4 = round($val['tot_amount'] * 0.04,2);
        ?>
        <tr class='list<?php echo $list; ?> col1 ht center'>
            <td><?php echo $val['create_date']?></td>
            <td><?php echo $val['od_cnt']?></td>
            <td><?php echo $val['order_amount']?></td>
            <td><?php echo $val['tot_amount']?></td>
            <td><?php echo $amount_4?></td>
            <td><?php echo $val['cancel_cnt']?></td>
        </tr>
    <?php
        }
    } else { ?>
        <tr class='list<?php echo $list;?> col1 ht center'>
            <td colspan="6">자료가 존재하지 않습니다.</td>
        </tr>
    <?php } ?>
</table>


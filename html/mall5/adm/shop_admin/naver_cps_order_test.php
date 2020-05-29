<?
$sub_menu = "800760";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");


$where = ($_GET['start_date'] !="" && $_GET['end_date']!="") ? " AND (left(o.od_pay_time, 10)  >= '".$_GET['start_date']."') AND (left(o.od_pay_time, 10) <= '".$_GET['end_date']."')" : "";
$where .= ($_GET['search_value']=="") ? "" : " AND o.od_id = '".$_GET['search_value']."'";

//전체리스트
$count_row = sql_fetch("select count(distinct o.od_id) as cnt
                            from naver_cps_order n 
                            left join yc4_order o on n.od_id = o.od_id
                            left join yc4_cart c on o.on_uid = c.on_uid
                            left join yc4_item i on c.it_id = i.it_id
                            where n.it_id is not null
                            and c.ct_status in ('배송', '준비', '완료','반품','품절')
                            and (o.od_pay_time != '' and o.od_pay_time is not null)
                            $where
                                LIMIT 1");
$total_count = $count_row['cnt'];

//금일 리스트
$today_list =array();
$today_count_row = sql_query($a="select distinct (o.od_id)
                            from naver_cps_order n 
                            left join yc4_order o on n.od_id = o.od_id
                            left join yc4_cart c on o.on_uid = c.on_uid
                            left join yc4_item i on c.it_id = i.it_id
                            where n.it_id is not null
                            and c.ct_status in ('배송', '준비', '완료','반품','품절')
                            and date_format(o.od_pay_time, '%Y%m%d') ='".date('Ymd')."'
                            $where
                               ");
while($today_row = sql_fetch_array($today_count_row)){
    $today_list[] = $today_row['od_id'];
}
$today_count = ($_GET['page']!="1" && $_GET['page']!="") ?  count($today_list) - (($_GET['page']-1)*30) : count($today_list);

//페이징
$rows = 30;// $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

//$qstr  = "page=$page";

$qstr = "&search_value=$search_value&start_date=$start_date&end_date=$end_date";
$dstr = "&search_value=$search_value&start_date=$start_date&end_date=$end_date";

$que = sql_query($a=" select n.od_id, n.settlement_date, o.od_time, i.it_name, o.on_uid, TRUNCATE(o.od_send_cost/o.exchange_rate,2) as od_send_cost, 
                  TRUNCATE(o.od_receipt_point/o.exchange_rate,2) as od_receipt_point, 
                    TRUNCATE(o.od_receipt_point/o.exchange_rate,2), TRUNCATE((o.od_receipt_bank + o.od_receipt_card + o.od_receipt_point)/o.exchange_rate,2) as order_amount,
                    TRUNCATE((o.od_receipt_bank + o.od_receipt_card - o.od_send_cost)/o.exchange_rate,2) as total_amount,
                     date_format(ifnull(o.od_pay_time,ifnull(o.od_time,ifnull(o.od_bank_time, o.od_card_time))),'%Y-%m-%d %H:%i:%s') as pay_date,
                    if(c.ct_status='취소' || c.ct_status='반품' || c.ct_status='품절',c.ct_status_update_dt, '') cancel_date,c.ct_status, c.it_id, 
                    round(c.ct_amount * c.ct_qty/o.exchange_rate,2) as item_amount, c.ct_qty
                    from naver_cps_order n 
                    left join yc4_order o on n.od_id = o.od_id
                    left join yc4_cart c on o.on_uid = c.on_uid
                    left join yc4_item i on c.it_id = i.it_id
                    where n.it_id is not null
                    and c.ct_status in ('배송', '준비', '완료','반품','품절')
                    and (o.od_pay_time != '' and o.od_pay_time is not null)
                    $where
                    group by o.od_id
                    ORDER BY o.od_pay_time desc, n.od_id desc, n.create_date desc, FIELD(c.ct_status, '품절', '반품', '배송','준비','완료')
                    LIMIT $from_record, $rows
                    ");
//echo $a;
$list_value = array();

while($row = sql_fetch_array($que)){
    $list_value[] = $row;
}


//품절, 반품된 상품 금액 찾기
$page_od_id = array_column($list_value,"od_id");


if(count($page_od_id)>0) {
    $cancel_amount = array();
    $que = sql_query("select o.od_id, round(c.ct_amount * c.ct_qty/o.exchange_rate,2) as cancel_item_amount from yc4_order o
                left join yc4_cart c on o.on_uid = c.on_uid
                where o.od_id in (".implode(",",$page_od_id).")
                and c.ct_status in ('반품', '품절')");
    while($row = sql_fetch_array($que)){
        $cancel_amount[$row['od_id']] = $row['cancel_item_amount'];
    }

}
if($_GET['excel']=="download"){
    if($_GET['start_date']=="" || $_GET['end_date']==""){
        alert("결제일자를 검색해주세요.");
    }
    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();

    $excel_title = 'naver_cps_order'.date('Ymd');

    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);

    $objPHPExcelWrite = $objPHPExcel->getActiveSheet();

    $objPHPExcelWrite->setCellValue('A1','주문번호')
        ->setCellValue('B1','상품명')
        ->setCellValue('C1','수량')
        ->setCellValue('D1','상품코드')
        ->setCellValue('E1','제품수량')
        ->setCellValue('F1','주문상태')
        ->setCellValue('G1','주문일시')
        ->setCellValue('H1','결제일시')
        ->setCellValue('I1','취소일시')
        ->setCellValue('J1','수취일시')
        ->setCellValue('K1','상품금액')
        ->setCellValue('L1','주문금액')
        ->setCellValue('M1','할인금액(포인트)')
        ->setCellValue('N1','배송료')
        ->setCellValue('O1','과세')
        ->setCellValue('P1','최종결제금액');

    $que = sql_query($a=" select n.od_id, n.settlement_date, o.od_time, i.it_name, o.on_uid, TRUNCATE(o.od_send_cost/o.exchange_rate,2) as od_send_cost, 
                  TRUNCATE(o.od_receipt_point/o.exchange_rate,2) as od_receipt_point, 
                    TRUNCATE(o.od_receipt_point/o.exchange_rate,2), TRUNCATE((o.od_receipt_bank + o.od_receipt_card + o.od_receipt_point)/o.exchange_rate,2) as order_amount,
                    TRUNCATE((o.od_receipt_bank + o.od_receipt_card - o.od_send_cost)/o.exchange_rate,2) as total_amount,
                     date_format(ifnull(o.od_pay_time,ifnull(o.od_time,ifnull(o.od_bank_time, o.od_card_time))),'%Y-%m-%d %H:%i:%s') as pay_date,
                    if(c.ct_status='취소' || c.ct_status='반품' || c.ct_status='품절',c.ct_status_update_dt, '') cancel_date,c.ct_status, 
                    if(c.ct_status='반품' || c.ct_status='품절',TRUNCATE(c.ct_amount * c.ct_qty/o.exchange_rate,2), 0) cancel_amount,
                    c.it_id, TRUNCATE(c.ct_amount * c.ct_qty/o.exchange_rate,2) as item_amount, c.ct_qty
                    from naver_cps_order n 
                    left join yc4_order o on n.od_id = o.od_id
                    left join yc4_cart c on o.on_uid = c.on_uid
                    left join yc4_item i on c.it_id = i.it_id
                    where n.it_id is not null
                    and c.ct_status in ('배송', '준비', '완료','반품','품절')
                    and (o.od_pay_time != '' and o.od_pay_time is not null)
                    $where
                    group by o.od_id
                    ORDER BY n.od_id, n.create_date desc, FIELD(c.ct_status, '품절', '반품', '배송','준비','완료')
                    ");

    $line = 2;
    $cancel_amount = array();
    while($value= sql_fetch_array($que)){

        $cancel_amount[$value['od_id']] += $value['cancel_amount'];

        $order_amount = round((($value['order_amount']  * 100)- ($cancel_amount[$value['od_id']]*100))/100,2);
        $total_amount = round((($value['total_amount']  * 100)- ($cancel_amount[$value['od_id']]*100))/100,2);


        $item_name = get_item_name($value['it_name'],'excel_mode');

        $objPHPExcelWrite->getCell('A'.$line)->setValueExplicit($value['od_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->getCell('B'.$line)->setValueExplicit($item_name, PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->getCell('C'.$line)->setValueExplicit($value['ct_qty'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->getCell('D'.$line)->setValueExplicit($value['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->setCellValue('E'.$line,$value['ct_qty'])
            ->setCellValue('F'.$line,$value['ct_status'])
            ->setCellValue('G'.$line,$value['od_time'])
            ->setCellValue('H'.$line,$value['pay_date'])
            ->setCellValue('I'.$line,$value['cancel_date'])
            ->setCellValue('J'.$line,$value['settlement_date'])
            ->setCellValue('K'.$line,$value['item_amount'])
            ->setCellValue('L'.$line,$order_amount)
            ->setCellValue('M'.$line,$value['od_receipt_point'])
            ->setCellValue('N'.$line,$value['od_send_cost'])
            ->setCellValue('O'.$line,0)
            ->setCellValue('P'.$line,$total_amount);

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
$commnet = ($_GET['search_value']=="") ? "주문이 존재하지 않습니다." : "결과가 존재하지 않습니다.";
$g4[title] = "네이버CPS 주문건정보 검색";
include_once ("$g4[admin_path]/admin.head.php");



?>
<!DOCTYPE html><!--뷰단-->
<html>
<head>
    <!-- 합쳐지고 최소화된 최신 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <!-- 부가적인 테마 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
    <!-- 합쳐지고 최소화된 최신 자바스크립트 -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</head>

<body>
<div class="row">
    <div class="col-lg-12">
        <br>
    </div>
</div>
<form>
    <div class="row">
        <div class="col-lg-2">
            <label>결제일1</label>
            <input type="text" id="datepicker-from" class="form-control" name="start_date" value="<?php echo $_GET['start_date']; ?>"/>
        </div>
        <div class="col-lg-2">
            <label>결제일2</label>
            <input type="text" id="datepicker-to" class="form-control" name="end_date" value="<?php echo $_GET['end_date']; ?>"/>
        </div>
        <div class="col-lg-2">
            <label>주문번호</label>
            <input type="text" name="search_value" class="form-control" value="<?php echo $_GET['search_value']?>" />
        </div>
        <div class="col-lg-2">
            <button class="btn btn-primary btn-block">검색</button>
        </div>
        <div class="col-lg-2">
        </div>
        <div class="col-lg-2">
            <?php if (count($list_value)>0) { ?>
                <button class="btn btn-success" type="button" onclick="location.href='./naver_cps_order_test.php?excel=download<?php echo $dstr?>'">엑셀다운로드</button>
            <?php } ?>
        </div>
    </div>
</form>
<div class="row">
    <div class="col-md-12">
        <br>
    </div>
</div>
<div class="row">

    <div class="col-lg-12">
        <b>* 주문상태 <준비,배송,완료>의 경우만이 결제가 진행된 주문건입니다.</b>
        <br/>
        <b>* 모든 금액은 USD입니다.</b>
        <br/>
        <?php
            $articltNum = $total_count - ($rows * ($page -1));
            echo subtitle("주문내역");
        ?>
        <table width=100% cellpadding=0 cellspacing=0 class='list_styleAD'>
            <thead>
            <tr>
                <th>No.</th>
                <th>주문번호</th>
                <th>주문일시</th>
                <th>결제일시</th>
                <th>취소일시</th>
                <th>수취일시</th>
                <th>주문금액</th>
                <th>할인금액(포인트)</th>
                <th>배송료</th>
                <th>과세</th>
                <th>최종결제금액</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($list_value)>0) {
                foreach ($list_value as $value) {
                    $order_amount = round((($value['order_amount']  * 100)- ($cancel_amount[$value['od_id']]*100))/100,2);
                    $total_amount = round((($value['total_amount']  * 100)- ($cancel_amount[$value['od_id']]*100))/100,2);
                    ?>
                    <tr>
                        <td><? if(in_array($value['od_id'],$today_list)) { echo $today_count; }else{ echo "-"; }  ?></td>
                        <td><a href="./naver_cps_order_detail.php?od_id=<?php echo $value['od_id'];?>"><?php echo $value['od_id'] ?></a></td>
                        <td><?php echo $value['od_time']; ?></td>
                        <td><?php echo $value['pay_date']; ?><br></td>
                        <td><?php echo $value['cancel_date']; ?></td>
                        <td><?php echo $value['settlement_date']?></td>
                        <td><?php echo ($order_amount); ?></td>
                        <td><?php echo $value['od_receipt_point']; ?></td>
                        <td><?php echo $value['od_send_cost']; ?></td>
                        <td>0</td>
                        <td><?php echo $total_amount; ?></td>

                    </tr>
                <?php

                    $today_count--;
                }
            } else { ?>
                <tr>
                    <td colspan="13" class="text-center"><strong style="color: red;"><?php echo $commnet; ?></strong></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <div align="center">
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <?php echo get_paging_boot($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?>
                </ul>
            </nav>
        </div>
    </div>
</div>
</body>
<link rel="stylesheet" href="//code.jquery.com/ui/1.8.18/themes/base/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.8.18/jquery-ui.min.js"></script>
</html>
<script>
    $( function() {
        var dateFormat = "yy-mm-dd",
            from = $( "#datepicker-from" )
                .datepicker({
                    defaultDate: "+1w",
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    numberOfMonths: 1
                })
                .on( "change", function() {
                    to.datepicker( "option", "minDate", getDate( this ) );
                }),
            to = $( "#datepicker-to" ).datepicker({
                defaultDate: "+1w",
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                numberOfMonths: 1
            })
                .on( "change", function() {
                    from.datepicker( "option", "maxDate", getDate( this ) );
                });

        function getDate( element ) {
            var date;
            try {
                date = $.datepicker.parseDate( dateFormat, element.value );
            } catch( error ) {
                date = null;
            }

            return date;
        }
    } );

    $("#datepicker-from").datepicker({
        dateFormat: 'yy-mm-dd',
        onSelect : function(selectDate){
            setEdate = selectDate;
            console.log(setEdate)
        }
    });

</script>

<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-08-11
 * Time: 오후 3:02
 */

$sub_menu = "400920";
include "_common.php";
auth_check($auth[$sub_menu], "w");

if($_GET['mode'] == 'auction_update'){
    file_get_contents('http://59.17.43.129/auction/update_shiping_info.php');
    alert('옥션 송장번호 업데이트가 완료되었습니다.',$_SERVER['PHP_SELF']);
    exit;
}

if($_POST['mode'] == 'complete'){
    $op_od_id_arr = explode("\n",$_POST['op_od_id']);
    if(count($op_od_id_arr)>0){
        $op_od_id_in = '';
        foreach ($op_od_id_arr as $op_od_id) {
            $op_od_id = trim($op_od_id);
            if(!$op_od_id){
                continue;
            }
            $op_od_id_in .= ($op_od_id_in ? ",":"")."'".$op_od_id."'";
        }
        if($op_od_id_in){
            $sql = sql_query("select op_cart_id from open_market_order_item where op_od_id in (".$op_od_id_in.")");
            while($row = sql_fetch_array($sql)){
                sql_query("update open_market_order set status = 'C' where op_cart_id = '".$row['op_cart_id']."'");
            }
            alert('완료 처리되었습니다.');
            exit;
        }
        alert('처리할 주문이 없습니다.');
        exit;

    }
    alert('처리할 주문이 없습니다.');
    exit;
}

$ntics_db = mssql_connect('ntics', 'sa', 'Tlstkddnr80');
mssql_select_db('ntshipping');


if ($_GET['mode'] == 'excel') {
    include $g4['full_path'] . '/classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $excel_title = '오플 오픈마켓 주문처리'.date('Y-m-d');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);

    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('계정', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('주문번호', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('택배사', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('운송장/등기번호', PHPExcel_Cell_DataType::TYPE_STRING);
}


# 미처리 주문건 로드 #
$list_tr = '';
/*$sql = sql_query("
    select
        a.op_cart_id,a.od_id,a.od_b_name,a.channel,b.op_od_id
    from
        open_market_order a
    left join
        open_market_order_item b on a.channel = b.channel and a.op_cart_id = b.op_cart_id
    where
        a.od_id is not null
        and a.status is null
    order by a.op_cart_id
");*/
$sql = sql_query("
    select
        a.op_cart_id,a.od_id,a.od_b_name,a.channel,b.op_od_id
    from
        open_market_order a
    left join
        open_market_order_item b on a.channel = b.channel and a.op_cart_id = b.op_cart_id,
        yc4_order c
    where
        a.od_id = c.od_id
        and ifnull(c.od_invoice,'') !=''
        and a.od_id is not null
        and a.status is null
    order by a.op_cart_id
");
$no=1;

while ($row = sql_fetch_array($sql)) {

    $invoice_sql = mssql_query("select CJNUM from NS_INVOICE where ORDERCODE ='k" . $row['od_id'] . "'");
    $invoice_no = '';
    if ($invoice_sql) {
        $invoice_data = mssql_fetch_assoc($invoice_sql);
        $invoice_no = trim($invoice_data['CJNUM']);
    }
    if (!$invoice_no) {
        continue;
    }
    $no++;

    if ($_GET['mode'] == 'excel') {
        switch($row['channel']){
            case 'A' :
                $objPHPExcel->getActiveSheet()->getCell('A'.$no)->setValueExplicit('옥션(neiko0413)', PHPExcel_Cell_DataType::TYPE_STRING);
                break;
            case 'G' :
                $objPHPExcel->getActiveSheet()->getCell('A'.$no)->setValueExplicit('지마켓(neiko13)', PHPExcel_Cell_DataType::TYPE_STRING);
                break;
        }
        $objPHPExcel->getActiveSheet()->getCell('B'.$no)->setValueExplicit($row['op_od_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C'.$no)->setValueExplicit('우체국택배', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D'.$no)->setValueExplicit($invoice_no, PHPExcel_Cell_DataType::TYPE_STRING);

    } else {
        $list_tr .= "
            <tr>
                <td>" . $row['channel'] . "</td>
                <td>" . $row['op_cart_id'] . "</td>
                <td>" . $row['op_od_id'] . "</td>
                <td>" . $invoice_no . "</td>
            </tr>
        ";
    }
}

if ($_GET['mode'] == 'excel') {
    $objPHPExcel->getActiveSheet()->setTitle($excel_title);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
//$objPHPExcel->setActiveSheetIndex(0);

// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
    $filename = iconv("UTF-8", "EUC-KR", $excel_title);

// Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

define('bootstrap', true);
$g4[title] = "오픈마켓 주문서 배송처리";
include $g4['full_path'] . "/adm/admin.head.php";
?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post" name="frm">
        <input type="hidden" name="mode" value="complete">
        <div class="input-group">
            <span class="input-group-addon">오픈마켓 주문번호</span>
            <textarea class="form-control custom-control" name="op_od_id" rows="3" style="resize:none"></textarea>
            <span class="input-group-addon btn btn-primary" onclick="document.frm.submit();">Send</span>
        </div>
    </form>
    <div class="panel">
        <div class="panel panel-heading">
            <a href="<?php echo $_SERVER['PHP_SELF'] ?>?mode=excel" class="btn btn-primary">엑셀 다운로드</a>
            <a href="<?php echo $_SERVER['PHP_SELF'] ?>?mode=auction_update" class="btn btn-info">옥션 자동처리</a>
        </div>
        <table class="table table-condensed">
            <thead>
            <tr>
                <td>채널</td>
                <td>결제번호(장바구니번호)</td>
                <td>주문번호</td>
                <td>송장번호</td>
            </tr>
            </thead>
            <tbody>
            <?php echo $list_tr ?>
            </tbody>
        </table>
    </div>
<?php
include $g4['full_path'] . "/adm/admin.tail.php";


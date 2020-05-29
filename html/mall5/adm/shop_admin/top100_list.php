<?php
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2017-01-10
 * Time: 오전 10:31
 */
$sub_menu = "300888";
include_once("./_common.php");
include $g4['full_path'] . '/lib/db_bs.php';
$db = new db();
auth_check($auth[$sub_menu], "r");

if (!$st || $st > 7 || $st < 1) {
    $st = '3';
}
if (!is_numeric($st)) {
    $st = '3';
}


# 제품관 리스트 #
$station_op_qry = sql_query("select s_id,name from yc4_station where view='Y' and s_id !='6' order by sort asc");
while($row = sql_fetch_array($station_op_qry)){
    $st_op .= "<option value='top100_list.php?st=".$row['s_id']."' ".($st == $row['s_id'] ? "selected":"").">".$row['name']."</option>";
}
$sql = "
SELECT
       d.name, a.sort, a.it_id, c.upc, b.it_name,
       b.it_amount, b.it_amount_usd, b.it_stock_qty,
       b.it_use, b.it_discontinued, b.it_health_cnt, b.list_clearance
FROM   yc4_best_item a
INNER JOIN yc4_item b ON a.it_id = b.it_id AND useyn = 'y'
INNER JOIN ople_mapping c ON a.it_id = c.it_id
inner join yc4_station d on a.s_id = d.s_id
where a.s_id ='$st'
ORDER BY a.s_id, a.sort
";
$result = sql_query($sql);
$top100_arr = array();
$upc_in = '';
while ($row = sql_fetch_array($result)) {

    $top100_arr[] = $row;
    if($row['upc']) {
        $upc_in .= ($upc_in ? "," : "") . "'" . trim($row['upc']) . "'";
    }
}
if($upc_in) {
    $sql = "
select
     upc, currentqty, location
from
     n_master_item
where
     upc in ($upc_in)
";
    $result = $db->ntics_db->query($sql);
    $upc_arr = array();
    foreach ($row2 = $result->fetchAll() as $row) {

        $upc_arr[trim($row['upc'])]['currentqty'] = $row['currentqty'];
        $upc_arr[trim($row['upc'])]['location'] = $row['location'];

    }
}
if($_GET['mode']=='excel_download'){
    $station_op_qry = sql_fetch("select left(name,2) name  from yc4_station where s_id = '$st'");

    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = $station_op_qry['name'].'관_top100'.date('Ymd');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('순위', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('오플상품코드', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('UPC', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('수량', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('상품명', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('상태', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('G1')->setValueExplicit('가격(원)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('H1')->setValueExplicit('가격(달러)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('I1')->setValueExplicit('통관', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('J1')->setValueExplicit('건기식병수', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('K1')->setValueExplicit('LOCATION', PHPExcel_Cell_DataType::TYPE_STRING);
    $it_id = '';
    $line= 2;
    foreach ($top100_arr as $key => $row) {

/*        if (isset($upc_arr[trim($row['upc'])])) {
            $top100_arr[$key]['upc'] = $top100_arr[$key]['upc'] . " 수량: " . $upc_arr[trim($row['upc'])]['currentqty'];
        }

        if (!$it_id) {
            $it_id = $row['it_id'];
        }
        if (isset($top100_arr[$key + 1]['it_id'])) {
            if ($it_id == $top100_arr[$key + 1]['it_id']) {
                $top100_arr[$key + 1]['upc'] = $top100_arr[$key]['upc'] . "<br>" . $top100_arr[$key + 1]['upc'];
                continue;
            } else if ($it_id != $top100_arr[$key + 1]['it_id']) {
                $it_id = $top100_arr[$key + 1]['it_id'];
            }
        }*/
        $ople_status = '';
        if ($row['it_stock_qty'] < 1) {
            $ople_status .='품절';
        }
        if ($row['it_discontinued']) {
            $ople_status .= '단종';
        }
        if (!$row['it_use']) {
            $ople_status .=  '판매중단';
        }
        if ($ople_status == '') {
            $ople_status = '판매중';
        }
       /* $list_clearance = '일반통관' . "<br>";
        if ($row['list_clearance']) {
            $list_clearance = '목록통관' . "<br>";
        }
        $row['it_health_cnt'] = $row['it_health_cnt'] ? $row['it_health_cnt'] : '0';
        $list_clearance .= '건기식 ' . $row['it_health_cnt'] . "<br>";
        if (isset($upc_arr[trim($row['upc'])])) {
            $list_clearance .= $upc_arr[trim($row['upc'])]['location'];
        }*/


        $objPHPExcel->getActiveSheet()->getCell('A'.$line)->setValueExplicit($row['sort'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B'.$line)->setValueExplicit($row['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C'.$line)->setValueExplicit($row['upc'], PHPExcel_Cell_DataType::TYPE_STRING);

        $objPHPExcel->getActiveSheet()->getCell('D'.$line)->setValueExplicit(isset( $upc_arr[trim($row['upc'])]['currentqty'])? $upc_arr[trim($row['upc'])]['currentqty']:'0', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E'.$line)->setValueExplicit($row['it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('F'.$line)->setValueExplicit($ople_status, PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('G'.$line)->setValueExplicit($row['it_amount']?$row['it_amount']:"0", PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('H'.$line)->setValueExplicit($row['it_amount_usd']?$row['it_amount_usd']:"0", PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('I'.$line)->setValueExplicit($row['list_clearance']?'목록통관':"일반통관", PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('J'.$line)->setValueExplicit($row['it_health_cnt']?$row['it_health_cnt']:"0", PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('K'.$line)->setValueExplicit(isset( $upc_arr[trim($row['upc'])]['location'])? $upc_arr[trim($row['upc'])]['location']:'0', PHPExcel_Cell_DataType::TYPE_STRING);
        $line++;

    }
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->setTitle($excel_title);
    $filename = iconv("UTF-8", "EUC-KR", $excel_title);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;

}


$g4[title] = "관별 TOP100 리스트";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <div class="row">
        <div class="col-lg-12">
            <select class="form-control" onchange="location.href=this.value">
                <?php echo $st_op;?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 text-right">
            <button onclick="location.href='./top100_list.php?mode=excel_download&st=<?php echo $st;?>'" class="btn-success btn">엑셀다운로드</button>
        </div>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th class="text-center" width="50px;">순위</th>
            <th class="text-center">이미지</th>
            <th class="text-center">오플상품코드</th>
            <th class="text-center"  width="210px;">UPC</th>
            <th class="text-center">상품명</th>
            <th class="text-center" width="80px;">가격</th>
            <th class="text-center" width="100px;">비고</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $it_id = '';
        $location = '';
        foreach ($top100_arr as $key => $row){

        if (isset($upc_arr[trim($row['upc'])])) {

            $top100_arr[$key]['upc'] = $top100_arr[$key]['upc'] . "<br> 수량: " . $upc_arr[trim($row['upc'])]['currentqty']."  ".$upc_arr[trim($row['upc'])]['location'];

        }
        if (!$it_id) {
            $it_id = $row['it_id'];
        }
        if (isset($top100_arr[$key + 1]['it_id'])) {

            if ($it_id == $top100_arr[$key + 1]['it_id']) {
                $top100_arr[$key + 1]['upc'] = $top100_arr[$key]['upc'] . "<br>" . $top100_arr[$key + 1]['upc'];

                continue;
            } else if ($it_id != $top100_arr[$key + 1]['it_id']) {
                $it_id = $top100_arr[$key + 1]['it_id'];

            }
        }
        $ople_status = '';
        if ($row['it_stock_qty'] < 1) {
            $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>품절</strong>';
        }
        if ($row['it_discontinued']) {
            $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>단종</strong>';
        }
        if (!$row['it_use']) {
            $ople_status .= ($ople_status ? '<br/>' : '') . '<strong>판매중단</strong>';
        }
        if ($ople_status == '') {
            $ople_status = '<br/> 판매중';
        }
        $list_clearance = '일반통관' . "<br>";
        if ($row['list_clearance']) {
            $list_clearance = '목록통관' . "<br>";
        }
        $row['it_health_cnt'] = $row['it_health_cnt'] ? $row['it_health_cnt'] : '0';
        $list_clearance .= '건기식 ' . $row['it_health_cnt'] . "<br>";
       /* if (isset($upc_arr[trim($row['upc'])])) {
            $list_clearance .= $upc_arr[trim($row['upc'])]['location'];
        }*/

        ?>
        <tr>
            <td><?php echo $row['sort']; ?></td>
            <td><?php echo get_it_image($row['it_id'] . '_s', 80, 80, null, null, false, false) ?></td>
            <td>
                <a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id']; ?>"><?php echo $row['it_id']; ?></a>
            </td>
            <td><?php echo $top100_arr[$key]['upc']; ?></td>

            <td><?php echo get_item_name($row['it_name'], 'list'); ?><span
                    style="color: red"><?php echo $ople_status; ?></span></td>
            <td class="text-right">\ <?php echo $row['it_amount'] ? number_format($row['it_amount']) : '0'; ?><br>$ <?php echo $row['it_amount_usd']; ?></td>
            <td><?php echo $list_clearance; ?></td>

        </tr>
        </tbody>
        <?php } ?>
    </table>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
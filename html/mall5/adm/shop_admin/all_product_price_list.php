<?
$sub_menu = "300126";
include_once("./_common.php");

ini_set('memory_limit','512M');
set_time_limit(-1);

auth_check($auth[$sub_menu], "r");

$g4[title] = "전체 상품 가격 리스트";


$de_conv_pay = $default['de_conv_pay'];

$item_list_arr = array();
$regdate = date('Y-m-d');


$item_total_row = sql_fetch_array(sql_query("SELECT count(it_id) as cnt FROM yc4_item WHERE it_use =1 AND it_discontinued = 0 LIMIT 0,1"));
$total_count = $item_total_row['cnt'];
/*$rows = 5000;
$total_list_count = ($total_count%$rows>0) ? intval($total_count/$rows) +1 : intval($total_count/$rows);*/

if($_REQUEST['mode']=="excel"){

    $limit_cnt = ($_GET['limit_cnt']) ? : 1;
    $start = ($_GET['limit_cnt']-1) * $rows;

    $item_que = sql_query("
                SELECT 
                  i.it_id, i.it_name, i.it_maker,i.it_stock_qty,i.it_amount,i.it_amount_usd,
                  m.upc, m.ople_type,
                  e.amount as msrp_price
                FROM yc4_item i
                LEFT OUTER JOIN  ople_mapping m ON i.it_id = m.it_id
                LEFT OUTER JOIN yc4_item_etc_amount e ON (i.it_id = e.it_id and e.pay_code = '3' and e.money_type = 'usd')
                WHERE i.it_use = 1 AND i.it_discontinued = 0                
                GROUP BY i.it_id
                "
    );

    while ($row = sql_fetch_array($item_que)) {
        $item_list_arr[$row['it_id']] = $row;
    }
    unset($row);

    $member_price_que = sql_query("
         SELECT 
          member_price, it_id
         FROM item_member_price
         WHERE  start_date <= '".$regdate."'
         AND    ( end_date >= '".$regdate."' or end_date is null )
         ");

    while ($row = sql_fetch_array($member_price_que)) {
        $item_price_arr[$row['it_id']] = $row;
        if(array_key_exists($row['it_id'], $item_list_arr))
            $item_list_arr[$row['it_id']] =  array_merge($item_list_arr[$row['it_id']], array("member_price"=>$row['member_price']));
    }
    unset($row);

    $promotion_price_que = sql_query("
         SELECT 
          it_id, amount_usd as promotion_price
         FROM yc4_promotion_item_dc_cache
         WHERE ( st_dt <= '".$regdate."' OR st_dt is null )
         AND   ( en_dt >= '".$regdate."' OR en_dt is null )
    ");

    while ($row = sql_fetch_array($promotion_price_que)){
        if(array_key_exists($row['it_id'], $item_list_arr))
            $item_list_arr[$row['it_id']] =  array_merge($item_list_arr[$row['it_id']], array("promotion_price"=>$row['promotion_price']));
    }
    unset($row);

    $hot_price_que = sql_query("
         SELECT 
        it_id , it_event_amount_usd as hot_price 
        FROM yc4_hotdeal_item
        WHERE flag = 'Y' AND sort > 0 AND sort < 9
    ");

    while($row = sql_fetch_array($hot_price_que)){
        if(array_key_exists($row['it_id'], $item_list_arr))
            $item_list_arr[$row['it_id']] =  array_merge($item_list_arr[$row['it_id']], array("hot_price"=>$row['hot_price']));
    }
    unset($row);

    if(count($item_list_arr)>0){
        include $g4['full_path'] . '/classes/PHPExcel.php';


        $objPHPExcel = new PHPExcel();

        $excel_title = 'all_product_price_'.date('Ymd');

        $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
            ->setTitle($excel_title)
            ->setSubject($excel_title)
            ->setDescription($excel_title);

        $objPHPExcelWrite = $objPHPExcel->getActiveSheet();

        $objPHPExcelWrite->setCellValue('A1','현재환율')
            ->setCellValue('B1',number_format($de_conv_pay))
            ->setCellValue('A2','상품코드')
            ->setCellValue('B2','UPC')
            ->setCellValue('C2','상품명')
            ->setCellValue('D2','제조사')
            ->setCellValue('E2','상품타입')
            ->setCellValue('F2','판매상태')
            ->setCellValue('G2','오플가(USD)')
            ->setCellValue('H2','오플가(원화)')
            ->setCellValue('I2','회원가(USD)')
            ->setCellValue('J2','회원가(원화)')
            ->setCellValue('K2','프로모션가(USD)')
            ->setCellValue('L2','프로모션가(원화)')
            ->setCellValue('M2','핫딜가(USD)')
            ->setCellValue('N2','핫딜가(원화)')
            ->setCellValue('O2','권장소비자가(USD)')
            ->setCellValue('P2','권장소비자가(원화)');


        $line = 3;
        foreach($item_list_arr as $value){

            $product_type = ($value['ople_type']=="s") ? "세트" : "단품";
            $upc = ($product_type == "세트") ? " 외" : "";

            $objPHPExcelWrite->getCell('A'.$line)->setValueExplicit($value['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcelWrite->getCell('B'.$line)->setValueExplicit($value['upc'].$upc, PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcelWrite->setCellValue('C'.$line,$value['it_name'])
                ->setCellValue('D'.$line,$value['it_maker'])
                ->setCellValue('E'.$line,$product_type)
                ->setCellValue('F'.$line,$value['it_stock_qty']>0 ? "품절아님" : "품절")
                ->setCellValue('G'.$line,$value['it_amount_usd'])
                ->setCellValue('H'.$line,$value['it_amount'])
                ->setCellValue('I'.$line,$value['member_price'])
                ->setCellValue('J'.$line,round($value['member_price']*$de_conv_pay))
                ->setCellValue('K'.$line,$value['promotion_price'])
                ->setCellValue('L'.$line,round($value['promotion_price']*$de_conv_pay))
                ->setCellValue('M'.$line,$value['hot_price'])
                ->setCellValue('N'.$line,round($value['hot_price']*$de_conv_pay))
                ->setCellValue('O'.$line,$value['msrp_price'])
                ->setCellValue('P'.$line++,round($value['msrp_price']*$de_conv_pay));
        }
        unset($item_list_arr);
        unset($line);

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
}
include_once ("$g4[admin_path]/admin.head.php");

?>
<form action="<?php echo $_SERVER['PHP_SELF']?>" id="excel-form" method="get">
    <input type="hidden" name="mode" value="excel">
    <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td colspan=12 height=2>
                <h2>전체 상품 가격 리스트&nbsp;</h2>
                <br>
            </td>
        </tr>
        <tr>
            <td colspan="12" align="right">
<!--                <select name="limit_cnt">
                    <?php /*for($i=1; $i<=$total_list_count; $i++){ */?>
                        <option value="<?php /*echo $i*/?>" <?php /*if($_GET['limit_cnt'] == $i) echo "selected";*/?>><?php /*echo $i*/?></option>
                    <?php /*} */?>
                </select>-->
                <button type='submit'>상품 가격 엑셀 다운로드</button>
            </td>
        </tr>
        <tr>
            <td colspan=12 height=5></td>
        </tr>
        <tr>
            <td colspan=12 height=1 bgcolor=#CCCCCC></td>
        </tr>
    </table>
</form>
<form name=flist>
    <table width=100% cellpadding=4 cellspacing=0>
        <input type=hidden name=page value="<?=$page?>">
        <tr>
            <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <tr>
            <td colspan=12 height=1 bgcolor=#CCCCCC></td>
        </tr>
    </table>
</form>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

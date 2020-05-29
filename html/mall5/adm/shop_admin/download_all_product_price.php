<?
//$sub_menu = "300126";
//include_once("./_common.php");

error_reporting(E_ALL);
ini_set("display_errors", 1);


ini_set('memory_limit','512M');
set_time_limit(-1);

//auth_check($auth[$sub_menu], "r");

//$g4[title] = "전체 상품 가격 리스트";


$g4_path = "../..";
include_once  $g4_path."/common.php";

$de_conv_pay = $default['de_conv_pay'];

$item_list_arr = array();
$regdate = date('Y-m-d');

$_REQUEST['mode'] = 'excel';

if($_REQUEST['mode']=="excel"){

    $limit_cnt = ($_GET['limit_cnt']) ? : 1;
    $start = ($_GET['limit_cnt']-1) * $rows;

    $item_que = sql_query("
                SELECT 
                  i.it_id, i.it_name, i.it_maker,i.it_stock_qty,i.it_amount,i.it_amount_usd, i.it_use, i.it_discontinued,
                  m.upc, m.ople_type, m.qty,
                  e.amount as msrp_price
                FROM yc4_item i
                LEFT OUTER JOIN  ople_mapping m ON i.it_id = m.it_id
                LEFT OUTER JOIN yc4_item_etc_amount e ON (i.it_id = e.it_id and e.pay_code = '3' and e.money_type = 'usd')                        
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
            ->setCellValue('C2','매핑수량')
            ->setCellValue('D2','상품명')
            ->setCellValue('E2','제조사')
            ->setCellValue('F2','상품타입')
            ->setCellValue('G2','판매상태')
            ->setCellValue('H2','오플가(USD)')
            ->setCellValue('I2','오플가(원화)')
            ->setCellValue('J2','회원가(USD)')
            ->setCellValue('K2','회원가(원화)')
            ->setCellValue('L2','프로모션가(USD)')
            ->setCellValue('M2','프로모션가(원화)')
            ->setCellValue('N2','핫딜가(USD)')
            ->setCellValue('O2','핫딜가(원화)')
            ->setCellValue('P2','권장소비자가(USD)')
            ->setCellValue('Q2','권장소비자가(원화)')
            ->setCellValue('R2','단종여부')
            ->setCellValue('S2','노출여부');


        $line = 3;
        foreach($item_list_arr as $value){

            $product_type = ($value['ople_type']=="s") ? "세트" : "단품";
            //$upc = ($product_type == "세트") ? " 외" : "";

            $objPHPExcelWrite->getCell('A'.$line)->setValueExplicit($value['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcelWrite->getCell('B'.$line)->setValueExplicit($value['upc'], PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcelWrite->setCellValue('C'.$line,$value['qty'])
                ->setCellValue('D'.$line,$value['it_name'])
                ->setCellValue('E'.$line,$value['it_maker'])
                ->setCellValue('F'.$line,$product_type)
                ->setCellValue('G'.$line,$value['it_stock_qty']>0 ? "품절아님" : "품절")
                ->setCellValue('H'.$line,$value['it_amount_usd'])
                ->setCellValue('I'.$line,$value['it_amount'])
                ->setCellValue('J'.$line,$value['member_price'])
                ->setCellValue('K'.$line,round($value['member_price']*$de_conv_pay))
                ->setCellValue('L'.$line,$value['promotion_price'])
                ->setCellValue('M'.$line,round($value['promotion_price']*$de_conv_pay))
                ->setCellValue('N'.$line,$value['hot_price'])
                ->setCellValue('O'.$line,round($value['hot_price']*$de_conv_pay))
                ->setCellValue('P'.$line,$value['msrp_price'])
                ->setCellValue('Q'.$line,round($value['msrp_price']*$de_conv_pay))
                ->setCellValue('R'.$line,$value['it_discontinued']==1 ? "단종" : "")
                ->setCellValue('S'.$line++,$value['it_use']==1 ? "노출" : "미노출");

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
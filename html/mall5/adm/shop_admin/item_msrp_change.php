<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-13
 * Time: 오전 10:27
 */
$sub_menu = "300888";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");
if ($_POST['mode'] == 'msrp_excel_change') {
    include $g4['full_path'] . '/classes/PHPExcel.php';


    $UpFile = $_FILES["excel_file"];
    $UpFileName = $UpFile["name"];

    $UpFilePathInfo = pathinfo($UpFileName);
    $UpFileExt = strtolower($UpFilePathInfo["extension"]);


    if ($UpFileExt != "xls" && $UpFileExt != "xlsx") {
        echo "엑셀파일만 업로드 가능합니다. (xls, xlsx 확장자의 파일포멧)";
        exit;
    }

//업로드된 엑셀파일을 서버의 지정된 곳에 옮기기 위해 경로 적절히 설정
    $upload_path = $g4["full_path"] . "/adm/shop_admin/msrp_upload";
    $upfile_path = $upload_path . "/" . date("Ymd_His") . "_" . $UpFileName;

    if (is_uploaded_file($UpFile["tmp_name"])) {

        if (!move_uploaded_file($UpFile["tmp_name"], $upfile_path)) {
            echo "업로드된 파일을 옮기는 중 에러가 발생했습니다.";
            exit;
        }

        //파일 타입 설정 (확자자에 따른 구분)
        $inputFileType = 'Excel2007';
        if ($UpFileExt == "xls") {
            $inputFileType = 'Excel5';
        }

        //엑셀리더 초기화
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);

        //데이터만 읽기(서식을 모두 무시해서 속도 증가 시킴)
        $objReader->setReadDataOnly(true);


        //업로드된 엑셀 파일 읽기
        $objPHPExcel = $objReader->load($upfile_path);

        //첫번째 시트로 고정
        $objPHPExcel->setActiveSheetIndex(0);

        //고정된 시트 로드
        $objWorksheet = $objPHPExcel->getActiveSheet();

        //시트의 지정된 범위 데이터를 모두 읽어 배열로 저장
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        $total_rows = count($sheetData);

        $i = 0;
        $pr_it_arr = array();
        $filter_sheetData = array();
        foreach ($sheetData as $rows_key => $rows) {
            if ($rows_key <= 1) {
                continue;
            }
            $it_id = $rows['A'];
            $msrp_krw = $rows['B'];
            $msrp_usd = $rows['C'];

            if (!is_numeric($it_id) || !is_numeric($msrp_krw) || !is_numeric($msrp_usd)) {
                continue;
            }
            echo $msrp_krw . "\n";
            //yc4_item_etc_amount
            $yc4_item_etc_amount_it_id_chk = sql_fetch("select count(*) cnt  from yc4_item_etc_amount where it_id ='{$it_id}'");
            if ($yc4_item_etc_amount_it_id_chk['cnt'] < 1) {

            } else {
                //업데이트
            }


            //yc4_item
            $yc4_item_it_id_chk = sql_fetch("select count(*) as cnt from yc4_item where it_id = '{$it_id}'");
            if ($yc4_item_it_id_chk['cnt'] < 1) {

            } else {
                //업데이트
            }

            //hot_deal
            $hot_deal_it_id_chk = sql_fetch("select count(*) cnt  from yc4_hotdeal_item where it_id ='{$it_id}' and flag in ('Y','W')");
            if ($hot_deal_it_id_chk['cnt'] < 1) {

            } else {
                //업데이트
            }

        }
    }
}elseif ($_POST['mode'] == 'msrp_change'){
    if($_POST['chk'] !='on'){
        return;
    }
    $it_id=$_POST['it_id'];
    $msrp_krw = $_POST['msrp_u'];
    $msrp_usd = $_POST['msrp_k'];
    if (!is_numeric($it_id) || !is_numeric($msrp_krw) || !is_numeric($msrp_usd)) {
        return;
    }
    echo $msrp_krw . "\n";
    //yc4_item_etc_amount
    $yc4_item_etc_amount_it_id_chk = sql_fetch("select count(*) cnt  from yc4_item_etc_amount where it_id ='{$it_id}'");
    if ($yc4_item_etc_amount_it_id_chk['cnt'] < 1) {

    } else {
        //업데이트
    }


    //yc4_item
    $yc4_item_it_id_chk = sql_fetch("select count(*) as cnt from yc4_item where it_id = '{$it_id}'");
    if ($yc4_item_it_id_chk['cnt'] < 1) {

    } else {
        //업데이트
    }

    //hot_deal
    $hot_deal_it_id_chk = sql_fetch("select count(*) cnt  from yc4_hotdeal_item where it_id ='{$it_id}' and flag in ('Y','W')");
    if ($hot_deal_it_id_chk['cnt'] < 1) {

    } else {
        //업데이트
    }
}
$g4[title] = "관별 TOP100 리스트";
define('bootstrap', true);
?>


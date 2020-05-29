<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-09-09
* Time : 오후 3:18
*/

//$sub_menu = "500600";
include_once("./_common.php");
//auth_check($auth[$sub_menu], "w");

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
$upload_path = $g4["full_path"] . "/adm/shop_admin/admin_upload";
$upfile_path = $upload_path . "/naver_cps_" . date("Ymd_His") . "_" . $UpFileName;

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
    /*
     *
        [A] => ITID(필수)
        [B] => 카테고리1(필수)
        [C] => 카테고리2
        [D] => 카테고리3
        [E] => 카테고리4
        [F] => 사용여부
     * */
    $pr_it_arr = array();
    $filter_sheetData = array();
    foreach ($sheetData as $rows_key => $rows) {
        if($rows_key <= 1){
            continue;
        }
        $it_id = $rows['A'];
        $cps_ca_name = $rows['B'];
        $cps_ca_name2 = $rows['C'];
        $cps_ca_name3 = $rows['D'];
        $cps_ca_name4 = $rows['E'];
        if($rows['F'] == "사용"){
            $use_yn = 'y';
        }else if($rows['F'] == "미사용"){
            $use_yn = 'n';
        }


        if($it_id=="" || $cps_ca_name==""){
            continue;
        }


        $cps_count = sql_fetch("select count(*) cnt from yc4_cps_item where it_id = '".$it_id."'");

        if($cps_count['cnt']>0){
            //update
            sql_query($a="update yc4_cps_item set 
                            cps_ca_name = '$cps_ca_name',
                            cps_ca_name2 = '$cps_ca_name2',
                            cps_ca_name3 = '$cps_ca_name3',
                            cps_ca_name4 = '$cps_ca_name4',
                            use_yn = '$use_yn',
                            update_date = now()
                            where it_id = '$it_id'");
            echo $a."<br>";

        }else{
            //insert
            sql_query($a="insert into yc4_cps_item(it_id, cps_ca_name, cps_ca_name2, cps_ca_name3, cps_ca_name4, create_date, use_yn)
                              values ('$it_id','$cps_ca_name','$cps_ca_name2','$cps_ca_name3','$cps_ca_name4',now(), '$use_yn')");
            echo $a."<br>";

        }


    }

    $return_url = "naver_cps_item.php";
    alert('네이버 CPS 상품 등록이 완료되었습니다.',$return_url);
}


?>
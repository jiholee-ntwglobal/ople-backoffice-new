<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2019-07-19
 * Time : 오후 2:41
 */

$sub_menu = "500700";
include './_common.php';
auth_check($auth[$sub_menu], "w");


// 엑셀 파일 업로드 시작
if($_REQUEST['mode'] == 'excel_upload'){
    include $g4['full_path'] . '/classes/PHPExcel.php';


    $UpFile = $_FILES["excel_file"];
    $UpFileName = $UpFile["name"];

    $UpFilePathInfo = pathinfo($UpFileName);
    $UpFileExt = strtolower($UpFilePathInfo["extension"]);


    if ($UpFileExt != "xls" && $UpFileExt != "xlsx") {
        alert('엑셀파일만 업로드 가능합니다. (xls, xlsx 확장자의 파일포멧)',$return_url);
        exit;
    }

    //업로드된 엑셀파일을 서버의 지정된 곳에 옮기기 위해 경로 적절히 설정
    $upload_path = $g4["full_path"] . "/adm/shop_admin/admin_upload";
    $upfile_path = $upload_path . "/gift_hoogi_list_" . date("Ymd_His") . "_" . $UpFileName;

    if (is_uploaded_file($UpFile["tmp_name"])) {

        if (!move_uploaded_file($UpFile["tmp_name"], $upfile_path)) {
            echo "업로드된 파일을 옮기는 중 에러가 발생했습니다.";
            alert('업로드된 파일을 옮기는 중 에러가 발생했습니다.',$return_url);

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
         *   [A] => card_event_id
         *   [B] => 회원아이디
         *   [C] => 경품명
         *
         * */
        $pr_it_arr = array();
        $filter_sheetData = array();
        foreach ($sheetData as $rows_key => $rows) {
            if($rows_key <= 2){
                continue;
            }
            array_walk($rows, function (&$item) {
                if(is_string($item)){
                    $item = trim($item);
                }
            });

            if(!$rows['A'] || !$rows['B'] || !$rows['C']){ //필수값
                continue;
            }

            // 중복확인
            $it_id_chk = sql_fetch($a = "select count(*) as cnt from yc4_gift_hoogi where card_event_id = '{$rows['A']}' and mb_id = '".$rows['B']."'");
            if($it_id_chk['cnt'] >0){
                continue;
            }

            $sql = "insert into yc4_gift_hoogi(card_event_id, mb_id, it_name, create_dt, write_id) values 
                  ('".$rows['A']."', '".$rows['B']."', '".$rows['C']."', now(),  '".$member['mb_id']."');";

             sql_query($sql);

        }
        unset($sheetData);
        $return_url = "gift_hoogi_list.php";
        alert('경품후기 대상자 등록이 완료되었습니다.',$return_url);
    }




}else if($_REQUEST['mode']=="delete"){

    $fg = sql_query("DELETE FROM yc4_gift_hoogi WHERE uid = '".sql_safe_query($_REQUEST['uid'])."'");

    if($fg==false){
        alert('개발팀에 문의 주시기바랍니다.');
    }

    alert('삭제가 완료되었습니다.','./gift_hoogi_list.php');
}
?>
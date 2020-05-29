<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2019-07-19
 * Time : 오후 2:41
 */

$sub_menu = "300999";
include './_common.php';
include_once $g4['full_path'] . '/lib/ople_mapping.php';

$ople_mapping = new ople_mapping();
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
    $upfile_path = $upload_path . "/item_insert_excel_" . date("Ymd_His") . "_" . $UpFileName;

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

            if(!$rows['A'] || !$rows['C']){ //필수값
                continue;
            }

            $escape=array("'",'"','\\');

            $it_name = trim($rows['A']) != '' ?str_replace($escape,'',trim($rows['A'])) : ''; // 상품명
            $qty_u = trim($rows['C']) !=''? str_replace($escape,'',trim($rows['C'])) : '';//가격$
            $qty_k = trim($rows['B'])!='' ? str_replace($escape,'',trim($rows['B'])) : $default['de_conv_pay'] * $qty_u; //가격\
            $health = $rows['D'] !='' ? str_replace($escape,'',trim($rows['D'])) : '0'; //건기식
            $stock_qty = $rows['H']; //품절 여부
            $clearance = $rows['G']; //목록통관
            $it_origin = trim($rows['F']) !='' ? str_replace($escape,'',trim($rows['F'])) : ''; //원산지
            $onetime_limit_cnt = is_numeric(trim($rows['E']))  ? str_replace($escape,'',trim($rows['E'])) : 0; //1회구매수량
            $upc[0] = $rows['I'];
            $qty[0] = $rows['J'];

            if(!$it_name){
//튕기기  상품명
                alert('상품명을 입력해주세요','');
                exit;
            }
            if(!$qty_k || !$qty_u){
//튕기기 가격 원
                alert('가격을 입력해주세요','');
                exit;
            }

            if( !is_numeric($qty_k) || !is_numeric($qty_u)){
                alert('가격을 입력해주시기 바랍니다');
                exit;
            }
            if($clearance =='Y' && $health >0 ){
                alert('목록통관 체크및 건기식 병수가 수량이 입력 되었습니다 확인 부탁드립니다.');;
                exit;
            }
            if( !is_numeric($health)){
                $health = '0';
            }
            $stock_qty=$stock_qty != '9999' ?'0':$stock_qty;
            $max_itid = sql_fetch("select max(it_id ) m from yc4_item limit 1");
            if(!isset($max_itid['m'])){
                //튕기기 맥스값이 없다면
                alert('오플상품코드를 불러오지 못했습니다','');
            }
            $new_it_id = trim($max_itid['m']) +100;
// 새로운 it_id 있는지 없는지 검사
            $new_cnt = sql_fetch("select count(*) cnt from yc4_item where it_id ='{$new_it_id}'");
            if($new_cnt['cnt']>0){
                //튕기거나 다시 새로운 it_id 생성
                alert('새로운 상품코드가 존재합니다 다시해주세요','');
            }else{
                //insert
                sql_query("INSERT INTO yc4_item
                (
                
                it_id, it_name, it_amount, it_use, it_time,
                it_ip, it_discontinued, it_health_cnt, it_amount_usd, it_stock_qty,
                it_gallery , it_type1 ,	it_type2 ,	it_type3 ,	it_type4 ,
                it_type5 ,	it_explan ,	it_explan_html ,	it_cust_amount ,	it_amount2 ,
                it_amount3 ,	it_point ,	it_hit , it_order ,	it_tel_inq ,
                SKU ,	it_order_onetime_limit_cnt ,	it_bottle_count ,	it_cust_amount_usd ,	ps_cnt
                ,list_clearance, it_origin,it_create_time
                )
                VALUES
                (
                '{$new_it_id}', '{$it_name}', '{$qty_k}', 1, now(),
                '{$_SERVER['REMOTE_ADDR']}', 0, '{$health}', '{$qty_u}', {$stock_qty},
                0,  0,	0,	0,	0,
                0, '',	0,	0,	0,
                0,	0,	0,  0,	0,
                '',	{$onetime_limit_cnt},	0,	0,	0,
                '{$clearance}', '{$it_origin}',now()
                )");
                //insert 한 it_id 확인 및 데이터 가지고 와서
                $insert_it_id_cnt = sql_fetch("select count(*) cnt from yc4_item where it_id ='{$new_it_id}'");
                if($insert_it_id_cnt<=0){
                    // it_id 가 만들어지지 않았으니 튕기기
                    alert('오플상품코드가 만들어지지 않았습니다. 다시해주세요','');
                }
                $it_id=$new_it_id;

                sql_query("

                INSERT INTO set_item
                (
                it_id,mb_id
                )
                VALUES
                (
                '{$it_id}', '{$_SESSION['ss_mb_id']}'
                )");
                $insert_it_id_cnt = sql_fetch("select count(*) cnt from set_item where it_id ='{$it_id}'");
                if($insert_it_id_cnt<=0){
                    // it_id 가 만들어지지 않았으니 튕기기
                    alert('오플상품코드가 만들어지지 않았습니다. 다시해주세요.','');
                }
                // ople _mapping
                $upc_arr = array();
                if(is_array($upc)){
                    foreach ($upc as $key => $row_upc) {
                        $upc_arr[$key]['upc'] = trim($row_upc);
                    }
                }
                if(is_array($qty)){
                    foreach ($qty as $key => $row_qty) {
                        if(!$row_qty){
                            $row_qty = 1;
                        }
                        $upc_arr[$key]['qty'] = trim($row_qty);
                    }
                }
                // 생성한 it_id
                $result = $ople_mapping->ople_mapping($it_id,$upc_arr);
                if($result === true){
                    continue;
                }else{
                    alert('처리중 오류 발생! 관리자에게 문의해 주세요.');
                }
            }
        }
        unset($sheetData);
        $return_url = "set_item_view.php";
        alert('세트 상품 등록이 완료되었습니다.',$return_url);
    }
}
?>
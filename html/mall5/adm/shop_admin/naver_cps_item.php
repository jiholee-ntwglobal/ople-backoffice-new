<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-09-09
* Time : 오전 11:36
*/

$sub_menu = "300770";
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");

$g4[title] = "네이버CPS 상품관리";


$where = ($_GET['search_value']=="") ? "" : " where it_id = '".$_GET['search_value']."'";

//전체리스트
$count_row = sql_fetch("SELECT count(it_id) as cnt 
                              from yc4_cps_item 
                                $where
                                LIMIT 1");
$total_count = $count_row['cnt'];

//페이징
$rows = 20;// $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$qstr  = "page=$page";
$dstr = "&search_value=$search_value";

$que = sql_query("SELECT it_id, cps_ca_name,cps_ca_name2,cps_ca_name3,cps_ca_name4,use_yn 
                        from yc4_cps_item 
                        $where
                        order by create_date
                        LIMIT $from_record, $rows
                        ");
$list = array();
while($row = sql_fetch_array($que)){
    $list[] = $row;
}
if($_GET['mode']=="excel_download"){
    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();

    $excel_title = 'naver_cps_item'.date('Ymd');

    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);

    $objPHPExcelWrite = $objPHPExcel->getActiveSheet();

    $objPHPExcelWrite->setCellValue('A1','ITID')
        ->setCellValue('B1','카테고리1')
        ->setCellValue('C1','카테고리2')
        ->setCellValue('D1','카테고리3')
        ->setCellValue('E1','카테고리4')
        ->setCellValue('F1','사용여부');

    $line = 2;
    $cancel_amount = array();

    $que = sql_query("SELECT it_id, cps_ca_name,cps_ca_name2,cps_ca_name3,cps_ca_name4,use_yn 
                        from yc4_cps_item 
                        $where
                        order by create_date
                        ");

    while($value = sql_fetch_array($que)){

        $objPHPExcelWrite->getCell('A'.$line)->setValueExplicit($value['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->getCell('B'.$line)->setValueExplicit($value['cps_ca_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->getCell('C'.$line)->setValueExplicit($value['cps_ca_name2'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->getCell('D'.$line)->setValueExplicit($value['cps_ca_name3'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->getCell('E'.$line)->setValueExplicit($value['cps_ca_name4'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcelWrite->getCell('F'.$line)->setValueExplicit(($value['use_yn']=="y") ? "사용" : "미사용" , PHPExcel_Cell_DataType::TYPE_STRING);


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
define('bootstrap', true);

include_once ("$g4[admin_path]/admin.head.php");



?>

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <form>
        <div class="row">
            <div class="col-lg-8"></div>
            <div class="col-lg-2">
                <input type="text" name="search_value" class="form-control" value="<?php echo $_GET['search_value']?>" placeholder="ITID검색" />
            </div>
            <div class="col-lg-2">
                <button class="btn btn-primary btn-block">검색</button>
            </div>
        </div>
    </form>
<br>
<div class="row">
    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-hover table-bordered table-condensed table-striped">
                <thead>
                <tr>
                    <td class="text-center">ITID</td>
                    <td class="text-center">카테고리1</td>
                    <td class="text-center">카테고리2</td>
                    <td class="text-center">카테고리3</td>
                    <td class="text-center">카테고리4</td>
                    <td class="text-center">사용여부</td>
                </tr>
                </thead>
                <tbody>
                <?php
                if(count($list)>0) {
                    foreach ($list as $val) { ?>
                        <tr>
                            <td><?php echo $val['it_id']?></td>
                            <td><?php echo $val['cps_ca_name']?></td>
                            <td><?php echo $val['cps_ca_name2']?></td>
                            <td><?php echo $val['cps_ca_name3']?></td>
                            <td><?php echo $val['cps_ca_name4']?></td>
                            <td><?php echo ($val['use_yn']=="y") ? "사용" : "미사용"?></td>
                        </tr>
                    <?php }
                }else{
                ?>
                    <tr>
                        <td colspan="6">등록된 CPS 상품정보가 없습니다.</td>
                    </tr>
                <?php }?>
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
        <div class="panel-footer text-center">
            <a href="<?php echo $_SERVER['PHP_SELF']?>?mode=excel_download<?php echo $dstr;?>" class="btn btn-default">엑셀 다운로드</a>
            <a href="./naver_cps_item_sample.xlsx" class="btn btn-default">샘플 엑셀파일 다운로드</a>

            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#excel_modal">엑셀 업로드</button>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="excel_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" enctype="multipart/form-data" action="./naver_cps_item_action.php">
            <input type="hidden" name="mode" value="excel_upload">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">엑셀 업로드</h4>
            </div>
            <div class="modal-body form-horizontal">
                <div class="row">
                    <label class="control-label col-lg-4">엑셀 파일 업로드</label>
                    <div class="col-lg-8"><input type="file" name="excel_file" class="form-control" required/></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">저장</button>
                <a href="./naver_cps_item_sample.xlsx" class="btn btn-info">샘플 엑셀파일 다운로드</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
            </div>
        </form>
    </div>
</div>


<?php
include '../admin.tail.php';



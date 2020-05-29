<?php
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2016-12-01
 * Time: 오후 5:01
 */
$sub_menu = "300170";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");


$it_ids = explode("\r\n", $_POST['it_id']);
$it_ids = array_filter($it_ids);


$it_ids_info = array();
if(count($it_ids)>0) {
    $sql = sql_query("select it_id, it_maker, it_name, it_create_time from yc4_item where it_id in (" . implode(",", $it_ids) . ")");

    while ($row = sql_fetch_array($sql)){
        $it_ids_info[$row['it_id']] = $row;
    }

}

if($_POST['mode']=="excel"){

    if(count($it_ids)>0){
        include $g4['full_path'] . '/classes/PHPExcel.php';


        $objPHPExcel = new PHPExcel();

        $excel_title = 'itid_check_date_'.date('Ymd');

        $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
            ->setTitle($excel_title)
            ->setSubject($excel_title)
            ->setDescription($excel_title);

        $objPHPExcelWrite = $objPHPExcel->getActiveSheet();

        $objPHPExcelWrite->setCellValue('A1','ITID')
            ->setCellValue('B1',"상품명")
            ->setCellValue('C1','제조사')
            ->setCellValue('D1','생성일자');


        $line = 2;
        foreach($it_ids as $it_id){

            $objPHPExcelWrite->getCell('A'.$line)->setValueExplicit($it_id, PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcelWrite->setCellValue('B'.$line,get_item_name($it_ids_info[$it_id]['it_name']))
                ->setCellValue('C'.$line,$it_ids_info[$it_id]['it_maker'])
                ->setCellValue('D'.$line,$it_ids_info[$it_id]['it_create_time']);
            $line++;
        }
        unset($it_ids);
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
define('bootstrap', true);
$g4[title] = "ITID 생성일자 조회";
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form name ="frm" class="form" action="it_id_check_date.php" method="post">
    <div class="row">
        <div class="col-lg-1">

        </div>
        <div class="col-lg-5">
            <textarea placeholder="IT_ID 엔터로 구분됩니다." name="it_id" class="form-control" rows="5"><?php echo $_POST['it_id']; ?></textarea>
        </div>
        <div class="col-lg-4">
            <br>
            <br>
            <br>
            <input type="button" value="검색" class="btn btn-primary btn-block" onclick="frm.mode.value=''; frm.submit();">
        </div>
        <div class="col-lg-2">
        </div>
    </div>
<div class="row">
    <div class="col-lg-12">
        <br>
    </div>
</div>
<?php if(count($it_ids)>0) { ?>
<input type="hidden" name="mode" value="">
<div>
    <div class="col-lg-10"></div>
    <div class="col-lg-2">

    <input type="button" onclick="frm.mode.value='excel'; frm.submit();" value="엑셀다운" class="btn btn-success btn-block">
    </div>
</div>
<div class="col-lg-12">
    <table class="table">
        <thead>
        <tr>
            <th>IT_ID</th>
            <th>상품명</th>
            <th>제조사</th>
            <th>생성일자</th>
        </tr>
        </thead>
        <tbody>
        <?php  foreach ($it_ids as $it_id) { ?>
            <tr class="odvalue">
                <td><?php echo $it_id?></td>
                <td><?php echo get_item_name($it_ids_info[$it_id]['it_name'], 'list')?></td>
                <td><?php echo $it_ids_info[$it_id]['it_maker']?></td>
                <td><?php echo $it_ids_info[$it_id]['it_create_time']?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</form>
<?php } ?>
<script>
    function ajax_row() {
        var it_id = $.trim($('#payment_it_id').val());

        if (!it_id) {
            alert('사은품코드를 입력해주세요');
            $('#payment_it_id_hidden').val('');
            $('#payment_it_id').val('');
            $('#payment_it_name').val('');
            $('#payment_it_id').focus();
            $('.gifts_search').css('display', 'none');
            return;
        }
        $.ajax({
            type: 'get'
            , url: 'payment_insert_ajax.php'
            , data: 'it_id=' + it_id
            , datatype: 'html'
            , success: function (data) {
                if (data != false) {
                    $('input[name =payment_it_id_hidden]').val(it_id);
                    $('#payment_it_name').html(data);
                    $('.gifts_search').css('display', '');

                } else {
                    alert('존재하지 않는 상품입니다.');
                    $('#payment_it_name').val('');
                    $('#payment_it_id').val('');
                    $('input[name =payment_it_id_hidden]').val('');
                    $('#payment_it_id').focus();
                    $('.gifts_search').css('display', 'none');
                }
            }
        });
    }
    function frm_submit() {

        return true;
    }
</script>



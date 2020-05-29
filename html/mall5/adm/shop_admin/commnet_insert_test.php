<?php
/*include "../ople/html/mall5/common.php";*/
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2016-11-24
 * Time: 오후 5:42
 */
$sub_menu = "300160";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$comment_value = array();
$it_id_arr = array();
$it_id_arr = explode("\n",$_GET['search_value']);
$it_id_arr = array_filter($it_id_arr);

array_walk($it_id_arr, function(&$item){
    $item = "'".trim($item)."'";
});

$excel_list =array();
$where = ($_GET['search_value'] != "") ? "where i.it_id in (" . implode(",", $it_id_arr) . ") or m.upc in (".implode(",", $it_id_arr).")" : "";

if(count($it_id_arr)>0) {
    $list_res = sql_query($a="select i.it_id, i.it_name, c.it_id as commnet_uid, c.comment, c.st_dt, c.en_dt, c.create_dt , m.upc
                            from comment_insert c 
                            right join yc4_item i on c.it_id = i.it_id 
                            right join ople_mapping m on m.it_id = i.it_id
                           {$where}
                            group by i.it_id
                            order by c.create_dt desc");

}else{
    $list_res = sql_query($a = "select i.it_id, i.it_name, c.it_id as commnet_uid, c.comment, c.st_dt, c.en_dt, c.create_dt
                           from comment_insert c 
                            inner join yc4_item i on c.it_id = i.it_id
                            order by c.create_dt desc
                            ");

}
echo $a;
$where_it_id = array();
while($row = sql_fetch_array($list_res)){
    $list_value[$row['it_id']] = $row;

    $where_it_id[] = $row['it_id'];

    if($row['commnet_uid']!="")
        $excel_list[$row['it_id']] = $row;
}

$where_it_id = array_filter($where_it_id);

$where_itid = (count($where_it_id)>0) ? " where a.it_id in (".implode(",",$where_it_id).")" : "";

$sql = "select a.it_id,i.upc,i.qty from comment_insert a
            inner join ople_mapping i on a.it_id = i.it_id 
            {$where_itid}
";
$result = sql_query($sql);
$it_id_upc = array();
while ($it_id_upc_row = sql_fetch_array($result)) {
    $it_id_upc[$it_id_upc_row['it_id']][] = $it_id_upc_row;

    $excel_upc[$it_id_upc_row['it_id']][] = $it_id_upc_row['upc'];
    $excel_qty[$it_id_upc_row['it_id']][] = $it_id_upc_row['qty'];
}

if($_GET['excel']=="download"){
    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = 'ople_item_comment' . date('Ymd');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('오플코드', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('UPC', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('UPC_QTY', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('상품명', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('문구', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('시작날짜', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('G1')->setValueExplicit('종료날짜', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('H1')->setValueExplicit('만든날짜', PHPExcel_Cell_DataType::TYPE_STRING);


    $sql = "
            select i.it_id,b.it_name,i.comment,i.st_dt,i.en_dt,i.create_dt,c.upc,c.qty 
            from comment_insert i
            inner join yc4_item b on i.it_id = b.it_id
            inner join ople_mapping c on i.it_id = c.it_id 
            {$where}
            order by i.create_dt  desc
";
    $result = sql_query($sql);
    $excel_item_data = array();
    $line = 2;
    while ($excel_item_data_row = sql_fetch_array($result)) {
        $objPHPExcel->getActiveSheet()->getCell('A' . $line)->setValueExplicit($excel_item_data_row['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B' . $line)->setValueExplicit($excel_item_data_row['upc'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C' . $line)->setValueExplicit($excel_item_data_row['qty'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D' . $line)->setValueExplicit($excel_item_data_row['it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E' . $line)->setValueExplicit($excel_item_data_row['comment'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('F' . $line)->setValueExplicit($excel_item_data_row['st_dt'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('G' . $line)->setValueExplicit($excel_item_data_row['en_dt'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('H' . $line++)->setValueExplicit($excel_item_data_row['create_dt'], PHPExcel_Cell_DataType::TYPE_STRING);
    }

    $filename = iconv("UTF-8", "EUC-KR", $excel_title);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

$g4[title] = "상품문구삽입";
include_once ("$g4[admin_path]/admin.head.php");
?>
<!DOCTYPE html><!--뷰단-->
<html>
<head>
    <!-- 합쳐지고 최소화된 최신 CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <!-- 부가적인 테마 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
    <!-- 합쳐지고 최소화된 최신 자바스크립트 -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</head>

<body>
<div class="row">
    <div class="col-lg-12">
        <br>
    </div>
</div>
<form>
    <div class="row">
        <div class="col-lg-2">

        </div>
        <div class="col-lg-4">
            <textarea name="search_value" class="form-control" rows="4"><?php echo $_GET['search_value'];?></textarea>
        </div>
        <div class="col-lg-4">
            <button class="btn btn-primary btn-block">검색 및 추가</button>
        </div>
        <div class="col-lg-2">
            <?php if (count($excel_list)>0) { ?>
                <button class="btn btn-success" type="button" onclick="location.href='./commnet_insert.php?excel=download'">엑셀다운로드</button>
            <?php } ?>
        </div>
    </div>
</form>
<div class="row">
    <div class="col-md-12">
        <br>
    </div>
</div>
<div class="row">

    <div class="col-lg-12">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th colspan="6" class="text-center">문구가 등록된 상품</th>
            </tr>
            <tr>
                <th>오플코드</th>
                <th>이미지</th>
                <th>상품명</th>
                <th>문구</th>
                <th>시작종료</th>
                <th>만든날짜</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($list_value)>0) {
                foreach ($list_value as $value) {
                    if($value['commnet_uid']=="") continue;
                    ?>
                    <tr>
                        <td>
                            <a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $value['it_id']; ?>"><?php echo $value['it_id']; ?>
                            </a>
                            <br>
                            <?if(!empty($it_id_upc[$value['it_id']])){
                                foreach ($it_id_upc[$value['it_id']] as $upc_datas){ ?>
                                    <span style="color: green;"><?php echo $upc_datas['upc']."*".$upc_datas['qty']."ea"; ?></span>
                                <?php }
                            }?>
                        </td>
                        <td><?php echo get_image( $value['it_id'] . "_m",100,100); ?></td>
                        <td><?php echo get_item_name($value['it_name'],'list');?></td>
                        <td><?php echo $value['comment']; ?></td>
                        <td><?php echo $value['st_dt'] . "<br>~<br>" . $value['en_dt']; ?></td>
                        <td><?php echo $value['create_dt']; ?><br>
                            <?php if (!$_GET['search_value']) { ?>
                                <button id="modifyI_id" class="btn btn-info btn-small" onclick="location.href='<?php echo $_SERVER['PHP_SELF'];?>?search_value=<?php echo $value['it_id']; ?>'">수정</button>
                            <?php } ?>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="6" class="text-center"><strong style="color: red;">결과가 존재하지 않습니다.</strong></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if($_GET['search_value']!="" && count($list_value)>0){ ?>
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-8"></div>
                <div class="col-lg-4" align="right">
                    <button class="btn btn-primary" type="button" onclick="location.href='<?php echo $_SERVER['PHP_SELF'];?>'">목록</button>
                    <button class="btn btn-success" type="button" onclick="frm_submit('write')">선택저장</button>
                    <button class="btn btn-danger" type="button" onclick="frm_submit('delete')">선택삭제</button>
                </div>
            </div>
            <br/>
            <form method="post" id="frm" action="./commnet_insert_action.php">
                <input type="hidden" name="mode" id="mode" value="">
                <input type="hidden" name="single_uid" id="single_uid" value="">
                <table class="table table-bordered">
                    <tr>
                        <td width="5%"><input type="checkbox" id="all-checkbox"/></td>
                        <td width="90%" colspan="2">내용</td>
                    </tr>
                    <?php
                    $i=0;
                    foreach ($list_value as $val){ ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="list-checkbox" name="chk_id[]" value="<?php echo $i?>">
                            </td>
                            <td align="">
                                <?php echo get_image( $val['it_id']."_m",200,200); ?>
                                <br/>
                                <label>
                                    <?php echo get_item_name($val['it_name'],'list');?>
                                </label>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-lg-9">
                                        <label><?php echo $val['it_id']; ?></label>
                                        <input type="hidden" value="<?php echo $val['it_id']; ?>" name="it_id[<?php echo $i;?>]">
                                        <input type="hidden" value="<?php echo ($val['commnet_uid']=="")? "insert" : "update";?>" name="it_id_mode[<?php echo $i;?>]">
                                    </div>
                                </div>
                                <br/>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <input class="form-control" type="text" value="<?php echo isset($val['st_dt']) ? $val['st_dt'] : ''; ?>" placeholder="시작날짜" id="from<?php echo $i;?>" name="st_dt[<?php echo $i;?>]"></div>
                                    <div class="col-lg-6">
                                        <input class="form-control" type="text" value="<?php echo isset($val['en_dt']) ? $val['en_dt'] : ''; ?>" placeholder="종료날짜" id="to<?php echo $i;?>" name="en_dt[<?php echo $i;?>]">
                                    </div>
                                </div>
                                <br/>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <textarea class="form-control" rows="4" placeholder="문구" id="comment1" name="comment[<?php echo $i;?>]"><?php echo isset($val['comment']) ? $val['comment'] : ''; ?></textarea>
                                    </div>
                                </div>
                                <br/>
                                <div class="row">
                                    <div class="col-lg-6"></div>
                                    <div class="col-lg-3">
                                        <button class="btn btn-success btn-block" type="button" onclick="single_frm_submit('write',<?php echo $i?>)">저장</button>
                                    </div>
                                    <div class="col-lg-3 text-right">
                                        <?php if($val['commnet_uid']!=''){?>
                                            <button class="btn btn-danger btn-block" type="button" onclick="single_frm_submit('delete',<?php echo $i?>)">삭제</button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php
                        $i++;
                    } ?>
                </table>
            </form>
        </div>
    <?php } ?>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.8.18/themes/base/jquery-ui.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script src="//code.jquery.com/ui/1.8.18/jquery-ui.min.js"></script>
    <script>
        <?php
        $i=0;
        foreach ($list_value as $val){ ?>
        $(function() {
            var dates = $( "#from<?php echo $i?>, #to<?php echo $i?> " ).datepicker({
                prevText: '이전 달',
                nextText: '다음 달',
                monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
                monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
                dayNames: ['일','월','화','수','목','금','토'],
                dayNamesShort: ['일','월','화','수','목','금','토'],
                dayNamesMin: ['일','월','화','수','목','금','토'],
                dateFormat: 'yy-mm-dd',
                showMonthAfterYear: true,
                yearSuffix: '년',
                maxDate:'+30d',
                onSelect: function( selectedDate ) {
                    var option = this.id == "from<?php echo $i?>" ? "minDate" : "maxDate",
                        instance = $( this ).data( "datepicker" ),
                        date = $.datepicker.parseDate(
                            instance.settings.dateFormat ||
                            $.datepicker._defaults.dateFormat,
                            selectedDate, instance.settings );
                    dates.not( this ).datepicker( "option", option, date );
                }
            });
        });
        <?php $i++; } ?>
        function single_frm_submit(mode,i) {

            $("#single_uid").val(i);
            $("#mode").val(mode);

            if(mode=="") {
                alert("버튼을 눌러주세요.");
                return false;
            }

            $('#frm').submit();
        }
        function frm_submit(mode){

            if($(".list-checkbox:checked").length < 1){
                alert("처리할 상품을 선택하세요.");
                return false;
            }

            $("#mode").val(mode);
            if(mode=="") {
                alert("버튼을 눌러주세요.");
                return false;
            }

            $('#frm').submit();

        }
        $(document).ready(function () {

            $("#all-checkbox").click(function () {
                $(".list-checkbox").prop("checked", ($(this).is(":checked") ? "checked" : false));
                $(".list-checkbox").trigger("change");
            });


            $(".list-checkbox").on("change", function () {
                if ($(this).is(":checked")) {
                    $(this).parent().parent().css({"font-weight": "bold", "font-size": "11px"});
                } else {
                    $(this).parent().parent().css({"font-weight": "normal", "font-size": "10px"});
                }

            });
        });
    </script>
</body>
</html>


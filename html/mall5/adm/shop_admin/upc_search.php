<?php
/**
 * Created by PhpStorm.
 * File name : upc_search.php.
 * Comment :
 * Date: 2016-05-11
 * User: Minki Hong
 */

$sub_menu = "300150";
include_once("./_common.php");
if(!$_POST['auto_fg']) {
    auth_check($auth[$sub_menu], "r");
}

if($_POST['upc']){
    $upc_arr = explode(PHP_EOL,$_POST['upc']);

    $upc_arr = array_filter($upc_arr,function($item){
        if(trim($item)){
            return true;
        }
        return false;
    });
    array_walk($upc_arr,function(&$item){
        if(is_string($item)){
            $item = trim($item);
        }
    });

    $result_arr = array('data'=>null,'error'=>null);
    if(count($upc_arr) > 0){
        include_once $g4['full_path'] . '/lib/db.php';
        $db = new db();

        $upc_where = "a.upc in (".implode(',',array_fill(0,count($upc_arr),'?')).")";
        $upc_param = $upc_arr;

        $ntics_stmt = $db->ntics_db->prepare("
            select 
                a.upc,a.it_id,a.qty,
                case when a.ople_type = 's' then 'Y' else 'N' end as ople_type,
                rtrim(c.mfgname) as mfgname,
                b.currentqty,
                concat(
                    rtrim(b.item_name),
                    case when rtrim(isnull(b.potency,'')) != '' then concat(' ',rtrim(b.potency)) end,
                    case when rtrim(isnull(b.potency_unit,'')) != '' then concat(' ',rtrim(b.potency_unit)) end,
                    case when rtrim(isnull(b.count,'')) != '' then concat(' ',rtrim(b.count)) end,
                    case when rtrim(isnull(b.type,'')) != '' then concat(' ',rtrim(b.type)) end
                ) as item_name
            from 
            ntics.dbo.ople_mapping a
            left join
            ntics.dbo.n_master_item b on a.upc = b.upc
            left join
            ntics.dbo.n_mfg c on b.mfgcd = c.mfgcd
            where {$upc_where} and b.upc is not null 
            order by a.upc asc
        ");
        if($ntics_stmt->execute($upc_param) === false){
            if(!is_array($result_arr['error'])){
                $result_arr['error'] = array();
            }
            $result_arr['error'][] = array(
                'LINE : '.__LINE__,
                $ntics_stmt->errorInfo(),
                $ntics_stmt->queryString,
                $upc_param
            );
        }else{
            $ntics_data = $ntics_stmt->fetchAll(PDO::FETCH_ASSOC);

            $it_id_arr = array();
            foreach ($ntics_data as $row) {
                $row['it_id'] = trim($row['it_id']);
                if(!in_array($row['it_id'],$it_id_arr)){
                    $it_id_arr[] = $row['it_id'];
                }
            }
            if(count($it_id_arr) > 0){
                $ople_stmt = $db->ople_db_pdo->prepare("
                    select 
                    it_id,
                    case when it_use = 1 then 'Y' else 'N' end  as it_use,
                    case when it_discontinued = 1 then 'Y' else 'N' end as it_discontinued,
                    case when it_stock_qty < 1 then 'Y' else 'N' end as it_stock_qty,
                    it_name,
                    it_maker 
                    from 
                    okflex5.yc4_item where it_id in (".implode(',',array_fill(0,count($it_id_arr),'?')).")
                ");
                if($ople_stmt->execute($it_id_arr) === false){
                    if(!is_array($result_arr['error'])){
                        $result_arr['error'] = array();
                    }
                    $result_arr['error'][] = array(
                        'LINE : '.__LINE__,
                        $ntics_stmt->errorInfo(),
                        $ntics_stmt->queryString,
                        $upc_param
                    );
                }

                $ople_stmt_data = $ople_stmt->fetchAll(PDO::FETCH_ASSOC);
                $ople_data = array();
                foreach ($ople_stmt_data as $row) {
                    $ople_data[$row['it_id']] = $row;
                }

                $result_data = array();
                foreach ($ntics_data as $row) {
                    array_walk($row,function(&$item){
                        if(is_string($item)){
                            $item = trim($item);
                        }
                    });
                    if(isset($ople_data[$row['it_id']])){
                        $result_data[] = $row + $ople_data[$row['it_id']];
                    }
                }
                $result_arr['data'] = $result_data;
                if($_POST['mode'] == 'excel'){
                    include $g4['full_path'] . '/classes/PHPExcel.php';
                    $objPHPExcel = new PHPExcel();
                    $excel_title = 'UPC Search_'.date('Ymd_His');
                    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
                        ->setTitle($excel_title)
                        ->setSubject($excel_title)
                        ->setDescription($excel_title);
                    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('UPC', PHPExcel_Cell_DataType::TYPE_STRING);
                    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('it_id', PHPExcel_Cell_DataType::TYPE_STRING);
                    $line = 2;
                    foreach ($result_arr['data'] as $row) {
                        $objPHPExcel->getActiveSheet()->getCell('A'.$line)->setValueExplicit($row['upc'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet()->getCell('B'.$line)->setValueExplicit($row['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $line++;
                    }
                    $objPHPExcel->getActiveSheet()->setTitle($excel_title);
                    $filename = iconv("UTF-8", "EUC-KR", $excel_title);

// Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
                    header('Cache-Control: max-age=0');
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    ob_start();
                    $objWriter->save('php://output');
                    $xlsData = ob_get_contents();
                    ob_end_clean();
                    $response =  array(
                        'op' => 'ok',
                        'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
                    );

                    die(json_encode($response));

                }
            }
        }
    }
        exit(json_encode($result_arr));
}






define('bootstrap', true);
$g4['title'] = "UPC 상품 검색";
//include_once $g4['full_path'] . '/head.sub.php';
include_once $g4['full_path'].'/adm/admin.head.php';
?>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $g4['path'];?>/js/handsontable/handsontable.full.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="<?php echo $g4['path'];?>/js/handsontable/handsontable.full.min.js"></script>

<div class="panel panel-default">
    <div class="panel-heading">
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return get_ajax_data(this.upc.value.trim());">
            <textarea name="upc" class="form-control"><?php echo $_POST['upc'];?></textarea>
            <button type="submit" class="btn btn-primary">검색</button>
            <button type="button" class="btn btn-info" onclick="get_ajax_data(this.form.upc.value,true);">엑셀(CSV)파일 다운로드</button>
        </form>
    </div>
	<div class="panel-body">
        <div id="data_content"></div>
	</div>
</div>


    <div id="data_content"></div>


    <script>
        function get_ajax_data(upc,excel_fg){
            if(excel_fg == true){
                $.ajax({
                    type:'POST',
                    url:"<?php echo $_SERVER['PHP_SELF']?>",
                    data: {
                        'upc' : upc,
                        'mode' : 'excel'
                    },
                    dataType:'json'
                }).done(function(data){
                    if(data.op != 'ok'){
                        alert('처리중 문제가 발생하였습니다 관리자에게 문의하여 주세요.');
                        return false;
                    }
                    var $a = $("<a>");
                    $a.attr("href",data.file);
                    $("body").append($a);
                    $a.attr("download","upc_search.xls");
                    $a[0].click();
                    $a.remove();
                });

            }else{
                $.ajax({
                    url:'<?php echo $_SERVER['PHP_SELF']?>',
                    type:'post',
                    data : {
                        'upc' : upc,
                    },success : function (result) {
//                    console.log(result);
                        var result_json = $.parseJSON(result);
                        if(result_json.error){
                            alert('처리 처리중 오류 발생!\n\n'+result_json.error);
                            return false;
                        }

                        var data = result_json.data;
                        get_handsontable(data,excel_fg);

                    }
                });
            }


            return false;
        }




        function get_handsontable(data,excel_fg){
            var container = document.getElementById('data_content');
            $(container).empty();
            var hot = new Handsontable(container, {
                data: data,
                colHeaders : [
                    'UPC',
                    'it_id',
                    /*'Mapping QTY',
                    'NTICS QTY',
                    '오플 판매 여부',
                    '오플 단종 여부',
                    '오플 품절 여부',
                    '세트 상품 여부',
                    'NTICS 브랜드명',
                    'NTICS 상품명',
                    '오플 브랜드명',
                    '오플 상품명'*/
                ],
                columns : [
                    {
                        data : 'upc',
                        type : 'text'
                    },
                    {
                        data : 'it_id',
                        type : 'text'
                    },
                    /*{
                        data : 'qty',
                        type : 'numeric',
                        width:110
                    },
                    {
                        data : 'currentqty',
                        type : 'numeric'
                    },
                    {
                        data : 'it_use',
                        type : 'text'
                    },
                    {
                        data : 'it_discontinued',
                        type : 'text'
                    },
                    {
                        data : 'it_stock_qty',
                        type : 'text'
                    },
                    {
                        data : 'ople_type',
                        type : 'text'
                    },
                    {
                        data : 'mfgname',
                        type : 'text'
                    },
                    {
                        data : 'item_name',
                        type : 'text'
                    },
                    {
                        data : 'it_maker',
                        type : 'text'
                    },
                    {
                        data : 'it_name',
                        type : 'text'
                    }*/

                ],

                rowHeaders: true,
//        dropdownMenu: true,
//        filters: true,
                readOnly : true,
                autoWrapRow : true
//                ,maxRows:data.length


            });




        }
    </script>

<?php include_once $g4['full_path'] . '/tail.sub.php';

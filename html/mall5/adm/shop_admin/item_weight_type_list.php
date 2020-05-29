<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2018-09-28
 * Time : 오후 3:47
 */
$sub_menu = "300667";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");


//페이징
function paging($total_list, $now_page, $list_page, $num_page, $searchdata)
{
    $total_page = ceil($total_list / $list_page); // 전체 페이지
    $total_block = ceil($total_page / $num_page); // 전체 블록
    $now_block = ceil($now_page / $num_page); //현재 블록
    $start_page = (((int)(($now_page - 1) / $num_page)) * $num_page) + 1; //시작페이지
    $end_page = $start_page + $num_page - 1; //끝페이지
    $next_page = (((int)(($now_page - 1 + $num_page) / $num_page)) * $num_page) + 1; //다음페이지
    $prev_page = (((int)(($now_page - 1 - $num_page) / $num_page)) * $num_page) + $num_page; //이전페이지
    $server = $_SERVER['PHP_SELF'];
    $pages = "";
    unset($searchdata['page']);
    $search = "";
    $search = http_build_query($searchdata);
    if ($total_page == 0 || $now_page > $total_page) {
        $pages .= "검색한 결과는 없습니다.";
    } else {
        if ($now_page > 1) {
            $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=1" . "&" . $search . "> 처음 </a>";
        }
        if ($now_block > 1) {
            $pages .= "<a class=\"btn btn-primary\" z href=" . $server . "?page=" . $prev_page . "&" . $search . "> 이전 </a>";
        }
        if ($end_page >= $total_page) {
            $end_page = $total_page;
        }
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $now_page) {
                $pages .= "<b class=\"btn btn-info\" disabled=\"disabled\"> " . $i . " </b>";
            } else {
                $pages .= "<a class=\"btn btn-info\" href=" . $server . "?page=" . $i . "&" . $search . "> " . $i . " </a>";
            }
        }
        if ($now_page >= 1) {
            if ($total_block != $now_block) {
                $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=" . $next_page . "&" . $search . "> 다음 </a>";
            }
            if ($now_page != $total_page) {
                $pages .= "<a class=\"btn btn-primary\" href=" . $server . "?page=" . $total_page . "&" . $search . "> 마지막 </a>";
            }
        }
    }
    return $pages;
}

//검색
$where = ($_REQUEST['type_name']) ? " AND type_name = '".$_REQUEST['type_name']."'" : '';

//엑셀다운로드
if($_GET['mode']=='excel'){
    //라이브러리
    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = '비가공식품리스트'.date('Ymd');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);
    $sheet = $objPHPExcel->getActiveSheet();
    //제목
    $sheet->getCell('A1')->setValueExplicit('분류명', PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->getCell('B1')->setValueExplicit('무게제한', PHPExcel_Cell_DataType::TYPE_STRING);
    $it_id = '';
    $line= 2;

    //db data
    $sql = "
            SELECT *
            FROM yc4_weight_type_info
            WHERE 1=1
            $where
            ORDER BY weight_type_id DESC
             ";
    $result = sql_query($sql);
    $weight_item_list_excel = array();
    while ($row = sql_fetch_array($result)){
        array_push($weight_item_list_excel,$row );
    }

    foreach ($weight_item_list_excel as $row) {
        //데이터
        $sheet->getCell('A'.$line)->setValueExplicit($row['type_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('B'.$line)->setValueExplicit($row['weight_limit'], PHPExcel_Cell_DataType::TYPE_STRING);
        $line++;

    }

    //컬럼 사이즈
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);

    //파일 생성
    $excel_title ='Unprocessed_food'.date('Ymd');
    $sheet->setTitle($excel_title);
    $filename = iconv("UTF-8", "EUC-KR", $excel_title);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;

}


$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = max($now_page,1);
$now_page = is_numeric($now_page) ? $now_page : "1";

//리스트
$list_page = 20;//보여줄 컬럼
$num_page = 5;//표시할 페이지
$sql = "
            SELECT *
            FROM yc4_weight_type_info
            WHERE 1=1
            $where             
            ORDER BY weight_type_id DESC
            limit  ".($now_page - 1) * $list_page." ,{$list_page}
             ";
$result = sql_query($sql);
$weight_item_list = array();
while ($row = sql_fetch_array($result)){
    array_push($weight_item_list,$row );
}

//개수
$cnt_sql  = "
            SELECT count(*) cnt
            FROM yc4_weight_type_info
            WHERE 1=1
            $where
            ";
$total_cnt = sql_fetch($cnt_sql);
$total_list = $total_cnt['cnt'];

//엑셀 다운로드
$get_data = $_GET;
unset($get_data['tab']);
unset($get_data['page']);
unset($get_data['mode']);
$get_data = http_build_query($get_data);

$g4[title] = "비가공 식품관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>비가공 식품 종류 관리</h4>
    </div>
</div>
<form class="form-inline text-right">
    <input type="hidden" value="<?php echo $_GET['tab']; ?>" name="tab">
    <div class="form-group">
        <label>분류명</label>
        <input type="text" class="form-control" name="type_name" value="<?php echo $_GET['type_name']; ?>">
    </div>
    <button class="btn btn-primary" type="submit">검색</button>
</form>
<br>
<table class="table table-hover">
    <thead>
    <tr>
        <th>분류명</th>
        <th>무게제한(g)</th>
        <th class="text-center">
            <button class="btn btn-success" type="button" onclick="location.href='./item_weight_type_form.php'" >등록</button>
        </th>
    </tr>
    <?php if(!empty($weight_item_list)){?>
    <tbody>
    <?php foreach ($weight_item_list as $row){ ?>
        <tr>
            <td><?php echo $row['type_name']?></td>
            <td><?php echo $row['weight_limit']?></td>
            <td>
                <button class="btn btn-info" type="button" onclick="location.href='./item_weight_type_modify.php?weight_type_id=<?php echo $row['weight_type_id'];?>'" >수정</button>
                <button class="btn btn-danger" type="button" onclick="del_type_weight('<?php echo $row['weight_type_id'];?>');" >삭제</button>
            </td>
        </tr>
    <? }?>
    </tbody>
    <tfoot>
    <tr>
        <td class="text-center" colspan="6"><?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?></td>
    </tr>
    </tfoot>
    <? }?>
    </thead>
</table>
<form id="f" action="./item_weight_type_action.php" method="post">
    <input type="hidden" name="mode" value="del">
    <input type="hidden" name="weight_type_id" value="">
</form>

<script>
    function del_type_weight(weight_type_id) {
        if(confirm('삭제하시겠습니까?') !== false) {
            $("input[name=weight_type_id]").val(weight_type_id);
            $("#f").submit();
        }else{
            return false;
        }
    }
</script>
<? include_once("$g4[admin_path]/admin.tail.php");  ?>



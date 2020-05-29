<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-07-11
 * Time: 오전 10:41
 */
$sub_menu = "300123";
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
$where = '';
$_GET['name'] = trim($_GET['name']) ? trim($_GET['name']) : '';
$_GET['id'] = trim($_GET['id']) ? trim($_GET['id']) : '';
$_GET['type'] = trim($_GET['type']) ? trim($_GET['type']) : '';
if($_GET['name'] != ''){
    $where  .= ($where =='' ? ' where ' : ' and '). " b.it_name like'%".sql_safe_query($_GET['name'])."%'";
}
if($_GET['id'] != ''){
    $where  .= ($where =='' ? ' where ' : ' and '). " a.it_id ='".sql_safe_query($_GET['id'])."'";
}
if($_GET['type'] != ''){
    $where  .= ($where =='' ? ' where ' : ' and '). " t.type_name ='".sql_safe_query($_GET['type'])."'";
}

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
    $sheet->getCell('A1')->setValueExplicit('상품코드', PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->getCell('B1')->setValueExplicit('브랜드', PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->getCell('C1')->setValueExplicit('제품명', PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->getCell('D1')->setValueExplicit('종류', PHPExcel_Cell_DataType::TYPE_STRING);
    $sheet->getCell('E1')->setValueExplicit('무게', PHPExcel_Cell_DataType::TYPE_STRING);
    $it_id = '';
    $line= 2;

    //db data
    $sql = "
            SELECT a.it_id,
                   b.it_maker,
                   b.it_name,
                   a.weight,
                   t.type_name as type_name
            FROM yc4_item_weight_info a 
            LEFT OUTER JOIN yc4_item b ON b.it_id = a.it_id
            LEFT OUTER JOIN yc4_weight_type_info t ON a.weight_type_id = t.weight_type_id
            $where
            ORDER BY it_create_time DESC
             ";
    $result = sql_query($sql);
    $weight_item_list_excel = array();
    while ($row = sql_fetch_array($result)){
        array_push($weight_item_list_excel,$row );
    }

    foreach ($weight_item_list_excel as $row) {
        //데이터
        $sheet->getCell('A'.$line)->setValueExplicit($row['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('B'.$line)->setValueExplicit($row['it_maker'], PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('C'.$line)->setValueExplicit($row['it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('D'.$line)->setValueExplicit($row['type_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $sheet->getCell('E'.$line)->setValueExplicit($row['weight']);
        $line++;

    }

    //컬럼 사이즈
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->getColumnDimension('C')->setAutoSize(true);
    $sheet->getColumnDimension('D')->setAutoSize(true);
    $sheet->getColumnDimension('E')->setAutoSize(true);

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
            SELECT a.it_id,
                   b.it_name,
                   a.weight,
                   t.type_name as type_name
            FROM yc4_item_weight_info a 
            LEFT OUTER JOIN yc4_item b ON b.it_id = a.it_id
            LEFT OUTER JOIN yc4_weight_type_info t ON a.weight_type_id = t.weight_type_id
            $where
            ORDER BY it_create_time DESC
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
            FROM yc4_item_weight_info a 
            LEFT OUTER JOIN yc4_item b ON b.it_id = a.it_id
            LEFT OUTER JOIN yc4_weight_type_info t ON a.weight_type_id = t.weight_type_id
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
            <h4>비가공 식품관리</h4>
        </div>
    </div>
    <form class="form-inline text-right">
        <input type="hidden" value="<?php echo $_GET['tab']; ?>" name="tab">
        <div class="form-group">
            <label>상품코드</label>
            <input type="text" class="form-control" name="id" value="<?php echo $_GET['id']; ?>">
        </div>
        <div class="form-group">
            <label>제품명</label>
            <input type="text" class="form-control" name="name" value="<?php echo $_GET['name']; ?>">
        </div>
        <div class="form-group">
            <label>종류</label>
            <select name="type" class="form-control">
                <option value=""  <?php echo $_GET['type'] == '' ? 'selected' : ''; ?>>전체</option>

                <?php
                $sql = "SELECT *  FROM yc4_weight_type_info ORDER BY type_name ASC";
                $result = sql_query($sql);
                $weight_item_list_excel = array();
                while ($row = sql_fetch_array($result)){
                    ?>
                    <option value="<?php echo $row['type_name'] ?>" <?php echo $_GET['type'] == $row['type_name'] ? 'selected' : ''; ?>><?php echo $row['type_name']?></option>
                <?php } ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">검색</button>
    </form>
    <br>
    <div class="text-right">
        <button class="btn btn-success" type="button" onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>&mode=excel'">엑셀 다운로드(총<?php echo $total_list;?>개)</button>
    </div>
    <table class="table table-hover">
        <thead>
        <tr>
            <th>상품코드</th>
            <th>이미지</th>
            <th>제품명</th>
            <th>종류</th>
            <th>무게</th>
            <th class="text-center"><button class="btn btn-success" type="button" onclick="location.href='./item_weight_form.php'" >등록</button></th>
        </tr>
        <?php if(!empty($weight_item_list)){?>
        <tbody>
        <?php foreach ($weight_item_list as $row){ ?>
            <tr>
                <td><a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id'];?>" target="_blank"><?php echo $row['it_id'];?></a></td>
                <td><?php echo get_it_image($row['it_id'] . '_s', 70, 70, null, null, false, false, false); ?></td>
                <td><?php echo get_item_name($row['it_name'], 'list'); ?></td>
                <td><?php echo $row['type_name'];?></td>
                <td class="text-right"><?php echo number_format($row['weight']);?>g</td>
                <td class="text-center">
                    <button class="btn btn-info" type="button" onclick="location.href='./item_weight_modify.php?it_id=<?php echo $row['it_id'];?>'" >수정</button>
                    <button class="btn btn-danger" type="button" onclick="del_weight('<?php echo $row['it_id'];?>');" >삭제</button>
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
<form id="f" action="./item_weight_action.php" method="post">
    <input type="hidden" name="mode" value="del">
    <input type="hidden" name="it_id" value="">
</form>

<script>
    function del_weight(id) {
        if(confirm('삭제하시겠습니까?')){
            $('input[name =it_id]').val(id);
            $('#f').submit();
        }else{
            $('input[name =it_id]').val('');
            return false;
        }
    }
</script>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
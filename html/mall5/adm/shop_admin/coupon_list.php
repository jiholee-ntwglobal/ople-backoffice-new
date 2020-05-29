<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-07-11
 * Time: 오전 10:41
 */
$sub_menu = "500540";
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

$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";

//검색
$_GET['name'] = trim($_GET['name']) ? trim($_GET['name']) : '';
$_GET['id'] = trim($_GET['id']) ? trim($_GET['id']) : '';
$_GET['du_publish'] = trim($_GET['du_publish']) ? trim($_GET['du_publish']) : '';
$_GET['tab'] = trim($_GET['tab']) ? trim($_GET['tab']) : 'all';
$_GET['mode'] = trim($_GET['mode']) ? trim($_GET['mode']) : '';

$where = '';

if ($_GET['name'] != '') {
    $where .= ($where == '' ? " where ":' and ')." a.coupon_name like '%".sql_safe_query($_GET['name'] )."%'";
}
if($_GET['id'] !=''){
    $where .= ($where == '' ? " where ":' and ')." a.coupon_code = '".sql_safe_query($_GET['id'] )."'";
}
if($_GET['du_publish'] =='yes'){
    $where .= ($where == '' ? " where ":' and ')." a.du_publish = '1'";
}elseif ($_GET['du_publish'] =='no'){
    $where .= ($where == '' ? " where ":' and ')." a.du_publish = '0'";
}
if($_GET['tab']== 'ing'){
    $where .= ($where == '' ? " where ":' and ')." date_format(now(),'%Y-%m-%d') between date_format(a.start_dt,'%Y-%m-%d') and ifnull(date_format(a.end_dt,'%Y-%m-%d'),date_format(now(),'%Y-%m-%d')) and a.use_flag = '1'";
}elseif ($_GET['tab']== 'wait'){
    $where .= ($where == '' ? " where ":' and ')." (date_format(now(),'%Y%m%d') < date_format(a.start_dt,'%Y%m%d') or date_format(now(),'%Y%m%d') > date_format(a.end_dt,'%Y%m%d')  or a.use_flag != '1') ";
}

//excel
if($_GET['mode']=='excel'){

    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = 'coupon_list'.date('Ymd');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);

    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('쿠폰이름', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('쿠폰번호', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('타입', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('포인트금액', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('시작날짜', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('종료날짜', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('G1')->setValueExplicit('사용가능여부', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('H1')->setValueExplicit('중복가능여부', PHPExcel_Cell_DataType::TYPE_STRING);

    $line= 2;

    $sql ="
    SELECT coupon_uid, 
       coupon_name,
       coupon_code,
       CASE coupon_type
          WHEN '1' THEN 'POINT'
          WHEN '2' THEN 'DISCOUNT RATE'
          ELSE 'DISCOUNT AMOUNT'
       END
        coupon_type,
       coupon_value,
       start_dt,
       end_dt,
       if (use_flag = '1','사용가능','사용불가능') use_flag ,
       if (du_publish = '1','중복가능','중복불가능') du_publish 
    FROM coupon_new
    ".$where."
    ORDER BY coupon_uid DESC
    ";

    $coupon_excel_result = sql_query($sql);

    while ($excel_row = sql_fetch_array($coupon_excel_result)){

        $objPHPExcel->getActiveSheet()->getCell('A'.$line)->setValueExplicit($excel_row['coupon_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B'.$line)->setValueExplicit($excel_row['coupon_code'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C'.$line)->setValueExplicit($excel_row['coupon_type'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D'.$line)->setValueExplicit($excel_row['coupon_value'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E'.$line)->setValueExplicit($excel_row['start_dt'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('F'.$line)->setValueExplicit($excel_row['end_dt'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('G'.$line)->setValueExplicit($excel_row['use_flag'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('H'.$line)->setValueExplicit($excel_row['du_publish'], PHPExcel_Cell_DataType::TYPE_STRING);
        $line++;

    }

    $filename = iconv("UTF-8", "EUC-KR", $excel_title);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');

    exit;
}

//paging
$list_page = 20;//보여줄 컬럼
$num_page = 5;//표시할 페이지

//리스트
$sql =
    "
SELECT a.coupon_uid,
       a.coupon_name,
       a.coupon_code,
       CASE a.coupon_type
          WHEN '1' THEN 'POINT'
          WHEN '2' THEN 'DISCOUNT RATE'
          ELSE 'DISCOUNT AMOUNT'
       END
          coupon_type,
       a.coupon_value,
       date_format(a.start_dt,'%Y-%m-%d') start_dt,
       date_format(a.end_dt,'%Y-%m-%d') end_dt,
       if(a.use_flag = '1', '사용가능', '사용불가능')   use_flag,
       if(a.du_publish = '1', '중복가능', '중복불가능') du_publish,
       count(b.coupon_uid)                                       cnt
FROM coupon_new    a
     LEFT OUTER JOIN coupon_history_new b ON a.coupon_uid = b.coupon_uid
     ".$where."
GROUP BY a.coupon_uid,
         a.coupon_name,
         a.coupon_code,
         a.coupon_type,
         a.coupon_value,
         a.start_dt,
         a.end_dt,
         a.use_flag,
         a.du_publish
ORDER BY a.coupon_uid DESC
limit  ".($now_page - 1) * $list_page." ,{$list_page}
   ";

$coupon_result = sql_query($sql);
$coupon_list = array();

while ($row = sql_fetch_array($coupon_result)){

    array_push($coupon_list ,$row);

}

//갯수
$sql =
    "
SELECT count(*) cnt
FROM coupon_new a
".$where."
ORDER BY coupon_uid DESC
   ";
$coupon_cnt = sql_fetch($sql);
$total_list = $coupon_cnt['cnt'];

//url
$get_data = $_GET;
unset($get_data['mode']);
$excel_url =http_build_query($get_data);
unset($get_data['tab']);
unset($get_data['page']);
$get_data = http_build_query($get_data);

$g4[title] = "쿠폰관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <div class="row">
        <div class="col-lg-12">
            <h4>쿠폰관리</h4>
            <p>중복가능 : 아이디당 1회사용가능</p>
            <p>중복불가능 : 1개 아이디 1회만 사용가능</p>
        </div>
    </div>
    <form class="form-inline text-right">
        <input type="hidden" value="<?php echo $_GET['tab']; ?>" name="tab">
        <div class="form-group">
            <label>쿠폰명</label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($_GET['name']); ?>">
        </div>
        <div class="form-group">
            <label>쿠폰번호</label>
            <input type="text" class="form-control" name="id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
        </div>
        <div class="form-group">
            <select class="form-control" name="du_publish">
                <option value="" <?php echo $_GET['du_publish'] == '' ? 'selected' : ''; ?>>전체</option>
                <option value="yes" <?php echo $_GET['du_publish'] == 'yes' ? 'selected' : ''; ?>>중복가능</option>
                <option value="no" <?php echo $_GET['du_publish'] == 'no' ? 'selected' : ''; ?>>중복불가능</option>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">검색</button>
    </form>
    <br>
    <div class='row'>
        <div class="col-lg-8 text-right">
            <ul class="nav nav-tabs">
                <li role="presentation" id="tab_upc" class='<?php echo $_GET['tab'] == '' || $_GET['tab'] == 'all' ? 'active' : ''; ?>'>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>">전체</a>
                </li>
                <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'ing' ? 'active' : ''; ?>'>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>&tab=ing">사용가능 쿠폰</a>
                </li>
                <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'wait' ? 'active' : ''; ?>'>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>&tab=wait">사용불가능 쿠폰</a>
                </li>
            </ul>
        </div>
        <div class="col-lg-4 text-right">
            <button class="btn btn-success" type="button" onclick="location.href='./coupon_form.php'" >쿠폰 생성</button>
            <button class="btn btn-primary" type="button" onclick="location.href='./coupon_list.php?<?php echo $excel_url;?>&mode=excel'" >엑셀다운로드</button>
        </div>
    </div>
    <table class="table table-hover">
        <thead>
        <tr>
            <td><strong>쿠폰이름</strong></td>
            <td><strong>쿠폰번호</strong></td>
            <td><strong>타입</strong></td>
            <td><strong>포인트금액</strong></td>
            <td><strong>시작날짜</strong></td>
            <td><strong>종료날짜</strong></td>
            <td><strong>사용가능여부</strong></td>
            <td><strong>중복가능여부</strong></td>
            <td></td>
        </tr>
        </thead>
        <?php if (!empty($coupon_list)) { ?>
            <tbody>
            <?php foreach ($coupon_list as $value){ ?>
                <tr>
                    <td><a href="./coupon_detail.php?uid=<?php echo $value['coupon_uid']; ?>"><?php echo $value['coupon_name']; ?>(<span style="color: blue;"><?php echo $value['cnt']; ?></span>)</a></td>
                    <td><?php echo $value['coupon_code']; ?></td>
                    <td><?php echo $value['coupon_type']; ?></td>
                    <td class="text-right"><?php echo number_format($value['coupon_value']); ?></td>
                    <td><?php echo $value['start_dt']; ?></td>
                    <td><?php echo $value['end_dt'] ? $value['end_dt'] :'무기한'; ?></td>
                    <td><?php echo $value['use_flag']; ?></td>
                    <td><?php echo $value['du_publish']; ?></td>
                    <td>
                        <button type="button" onclick="location.href='./coupon_form.php?uid=<?php echo $value['coupon_uid']; ?>'" class="btn btn-info btn-sm">수정</button>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <td class="text-center" colspan="9"><?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?></td>
            </tr>
            </tfoot>
        <? } ?>
    </table>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
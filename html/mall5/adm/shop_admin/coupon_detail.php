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
$where = '';
$_GET['uid'] = trim($_GET['uid']) ? trim($_GET['uid']) : '';
$_GET['mb_id'] = trim($_GET['mb_id']) ? trim($_GET['mb_id']) : '';
$_GET['mb_name'] = trim($_GET['mb_name']) ? trim($_GET['mb_name']) : '';

if ($_GET['uid'] != '') {
    $where .= " where a.coupon_uid ='" . sql_safe_query($_GET['uid']) . "' ";
}else{
    alert('잘못된 접근방식입니다','./coupon_list.php');
}

if( $_GET['mb_id'] != ''){
    $where .= " and c.mb_id = '".sql_safe_query($_GET['mb_id'])."'";
}

if( $_GET['mb_name'] != ''){
    $where .= " and c.mb_name = '".sql_safe_query($_GET['mb_name'])."'";
}
$list_page = 20;//보여줄 컬럼
$num_page = 5;//표시할 페이지

//excel
if($_GET['mode']=='excel'){

    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = 'coupon_userlist'.date('Ymd');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);

    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('쿠폰이름', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('쿠폰번호', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('타입', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('포인트금액', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('아이디', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('이름', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('G1')->setValueExplicit('사용날짜', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('H1')->setValueExplicit('등록날짜', PHPExcel_Cell_DataType::TYPE_STRING);

    $line= 2;

    $sql ="
SELECT b.coupon_history_uid, 
        c.mb_id,
        c.mb_name, 
        b.use_date, 
        b.create_date,
        
        a.coupon_name,
        a.coupon_code,
        a.coupon_value,
        a.start_dt,
        a.end_dt,
        if (a.use_flag = '1','사용가능','사용불가능') use_flag ,
        if (a.du_publish = '1','중복가능','중복불가능') du_publish ,
       CASE a.coupon_type
          WHEN '1' THEN 'POINT'
          WHEN '2' THEN 'DISCOUNT RATE'
          ELSE 'DISCOUNT AMOUNT'
       END
          coupon_type
FROM coupon_history_new b
     INNER JOIN coupon_new    a 
        ON a.coupon_uid = b.coupon_uid 
     LEFT JOIN g4_member c ON c.mb_no = b.member_no
$where 
ORDER BY b.coupon_history_uid desc
    ";

    $coupon_excel_result = sql_query($sql);

    while ($excel_row = sql_fetch_array($coupon_excel_result)){

        $objPHPExcel->getActiveSheet()->getCell('A'.$line)->setValueExplicit($excel_row['coupon_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B'.$line)->setValueExplicit($excel_row['coupon_code'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C'.$line)->setValueExplicit($excel_row['coupon_type'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D'.$line)->setValueExplicit($excel_row['coupon_value'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E'.$line)->setValueExplicit($excel_row['mb_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('F'.$line)->setValueExplicit($excel_row['mb_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('G'.$line)->setValueExplicit($excel_row['use_date'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('H'.$line)->setValueExplicit($excel_row['create_date'], PHPExcel_Cell_DataType::TYPE_STRING);
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


//리스트
$sql =
    "
SELECT b.coupon_history_uid, 
        c.mb_id,
        c.mb_name, 
        b.use_date, 
        b.create_date,
        
        a.coupon_name,
        a.coupon_code,
        a.coupon_value,
        a.start_dt,
        a.end_dt,
        if (a.use_flag = '1','사용가능','사용불가능') use_flag ,
        if (a.du_publish = '1','중복가능','중복불가능') du_publish ,
       CASE coupon_type
          WHEN '1' THEN 'POINT'
          WHEN '2' THEN 'DISCOUNT RATE'
          ELSE 'DISCOUNT AMOUNT'
       END
          coupon_type
FROM coupon_history_new b 
     INNER JOIN coupon_new    a
        ON a.coupon_uid = b.coupon_uid 
     LEFT JOIN g4_member c ON c.mb_no = b.member_no
$where 
ORDER BY b.coupon_history_uid desc
limit  " . ($now_page - 1) * $list_page . " ,{$list_page}
   ";

$coupon_result = sql_query($sql);

$coupon_list = array();
$coupon_data = array();
$int = 1;

while ($row = sql_fetch_array($coupon_result)) {
    if ( $int  == 1 ){
        $int ++;
        $coupon_data = $row;
    }
    array_push($coupon_list, $row);
}

if(count($coupon_data) <1){
    alert('데이터가없습니다');
}
//갯수
$sql =
    "
SELECT count(*) cnt
FROM coupon_history_new b 
     INNER JOIN  coupon_new    a
        ON a.coupon_uid = b.coupon_uid 
     LEFT JOIN g4_member c ON c.mb_no = b.member_no
$where 

   ";
$coupon_cnt = sql_fetch($sql);
$total_list = $coupon_cnt['cnt'];

$get_data = $_GET;
unset($get_data['page']);
$get_data = http_build_query($get_data);

$g4[title] = "쿠폰관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<div class="panel panel-default">
    <div class="row">
        <div class="col-lg-9">
            <div class="panel-heading">
                <h4 style="display: inline-block">쿠폰정보</h4> <a href="./coupon_list.php">목록으로가기</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12">
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
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo $coupon_data['coupon_name'];?></td>
                        <td><?php echo $coupon_data['coupon_code'];?></td>
                        <td><?php echo $coupon_data['coupon_type'];?></td>
                        <td><?php echo $coupon_data['coupon_value'];?></td>
                        <td><?php echo $coupon_data['start_dt'];?></td>
                        <td><?php echo $coupon_data['end_dt'] ? $coupon_data['end_dt'] :"무기한";?></td>
                        <td><?php echo $coupon_data['use_flag'];?></td>
                        <td><?php echo $coupon_data['du_publish'];?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-default">
    <div class="row">
        <div class="col-lg-9">
            <div class="panel-heading">
                <h4 style="display: inline-block">쿠폰등록한 고객 리스트</h4> 총 <?php echo $total_list;?> 건
                <button class="btn btn-primary" type="button" onclick="location.href='./coupon_detail.php?<?php echo $get_data;?>&mode=excel'" >엑셀다운로드</button>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12 text-right">
                <form class="form-inline">
                    <div class="form-group input-group-sm">
                        <label>아이디</label>
                        <input type="hidden" value="<?php echo $_GET['uid'];?>" name="uid">
                        <input class="form-control" name="mb_id" value="<?php echo htmlspecialchars($_GET['mb_id']);?>">
                        <label>이름</label>
                        <input class="form-control" name="mb_name" value="<?php echo htmlspecialchars($_GET['mb_name']);?>">
                    </div>
                    <button type="submit" class="btn btn-info btn-group-xs">검색</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>아이디</th>
                        <th>이름</th>
                        <th>사용날짜</th>
                        <th>등록날짜</th>
                    </tr>
                    </thead>
                    <?php if (!empty($coupon_list)) { ?>
                        <tbody>
                        <?php foreach ($coupon_list as $value){ ?>
                        <tr>
                            <td><?php echo $value['mb_id']; ?></td>
                            <td><?php echo $value['mb_name']; ?></td>
                            <td><?php echo $value['use_date']; ?></td>
                            <td><?php echo $value['create_date']; ?></td>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="text-center" colspan="6">
                                <?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); ?>
                            </td>
                        </tr>
                        </tfoot>
                    <? } ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
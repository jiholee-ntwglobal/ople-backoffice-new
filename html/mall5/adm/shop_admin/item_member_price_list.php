<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-18
 * Time: 오후 2:30
 */
$sub_menu = "500550";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

//페이징
function bootstrap_paging($total_list, $now_page, $list_page, $num_page, $searchdata)
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

$_GET['it_id'] = trim($_GET['it_id']) ? trim($_GET['it_id']) : '';
$_GET['it_maker'] = trim($_GET['it_maker']) ? trim($_GET['it_maker']) : '';
$_GET['it_name'] = trim($_GET['it_name']) ? trim($_GET['it_name']) : '';
$_GET['tab'] = trim($_GET['tab']) ? trim($_GET['tab']) : 'all';

$where = '';

if ($_GET['it_id'] != '') {
    $where .= ($where == '' ? " where ":' and ')." a.it_id = '".sql_safe_query($_GET['it_id'] )."'";
}

if($_GET['it_maker'] !=''){
    $where .= ($where == '' ? " where ":' and ')." b.it_maker like '%".sql_safe_query($_GET['it_maker'] )."%'";
}

if($_GET['it_name'] !=''){
    $where .= ($where == '' ? " where ":' and ')." b.it_name like '%".sql_safe_query($_GET['it_name'] )."%'";
}

if($_GET['tab']== 'ing'){
    $where .= ($where == '' ? " where ":' and ')." date_format(now(),'%Y-%m-%d') between date_format(a.start_date,'%Y-%m-%d') and ifnull(date_format(a.end_date,'%Y-%m-%d'),date_format(now(),'%Y-%m-%d')) ";
}elseif ($_GET['tab']== 'fu'){
    $where .= ($where == '' ? " where ":' and ')." date_format(now(),'%Y-%m-%d') < date_format(a.start_date,'%Y-%m-%d')  ";
}elseif ($_GET['tab']== 'wait'){
    $where .= ($where == '' ? " where ":' and ')." ifnull(date_format(a.end_date,'%Y-%m-%d'),'2077-12-31') <  date_format(now(),'%Y-%m-%d')  ";
}

//excel
if($_GET['mode']=='excel'){

    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = 'member_price'.date('Ymd');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);

    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('상품코드', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('브랜드', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('상품명', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('판매가', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('회원 할인가', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('시작날짜', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('G1')->setValueExplicit('종료날짜', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('H1')->setValueExplicit('등록날짜', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('I1')->setValueExplicit('등록한ID', PHPExcel_Cell_DataType::TYPE_STRING);

//리스트
    $sql = "
            SELECT a.uid, 
                   a.it_id,
                   b.it_maker,
                   b.it_name,
                   b.it_amount_usd,
                   a.member_price,
                   a.start_date,
                   a.end_date,
                   a.create_date,
                   a.create_id
            FROM item_member_price a INNER JOIN yc4_item b ON a.it_id = b.it_id
            $where
            order by a.uid desc
        ";

    $coupon_excel_result = sql_query($sql);

    $line= 2;

    while ($excel_row = sql_fetch_array($coupon_excel_result)){

        $objPHPExcel->getActiveSheet()->getCell('A'.$line)->setValueExplicit($excel_row['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B'.$line)->setValueExplicit($excel_row['it_maker'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C'.$line)->setValueExplicit($excel_row['it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D'.$line)->setValueExplicit($excel_row['it_amount_usd'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E'.$line)->setValueExplicit($excel_row['member_price'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('F'.$line)->setValueExplicit($excel_row['start_date'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('G'.$line)->setValueExplicit($excel_row['end_date']?$excel_row['end_date']:'무기한', PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('H'.$line)->setValueExplicit($excel_row['create_date'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('I'.$line)->setValueExplicit($excel_row['create_id'], PHPExcel_Cell_DataType::TYPE_STRING);
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
$sql = "
            SELECT a.uid, 
                   a.it_id,
                   b.it_maker,
                   b.it_name,
                   b.it_amount_usd,
                   a.member_price,
                   a.start_date,
                   a.end_date,
                   a.create_date,
                   a.create_id
            FROM item_member_price a INNER JOIN yc4_item b ON a.it_id = b.it_id
            $where
            order by a.uid desc
            limit  ".($now_page - 1) * $list_page." ,{$list_page}
        ";
$item_member_price_result = sql_query($sql);
$item_member_price_list = array();

while ($row = sql_fetch_array($item_member_price_result)){

    array_push($item_member_price_list ,$row);

}

$sql =
    "
            SELECT count(*) cnt
            FROM item_member_price a INNER JOIN yc4_item b ON a.it_id = b.it_id
            $where
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

$g4[title] = "회원할인가 관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>상품 회원할인가 관리</h4>
    </div>
</div>
    <div class="row">
        <div class="col-lg-12">
            <p>※ 매일 00시에 회원 할인가가 업데이트 됩니다 바로 적용을 원하시면 적용버튼을 클릭하시기 바랍니다</p>
            <p>※마감된 상품은 수정이 불가능합니다.</p>
            <p>※등록,수정 시 날짜가 기존에 등록되어 있는 상품과 겹치면 등록,수정이 불가능합니다 </p>
        </div>
    </div>
<form class="form-inline text-right">
    <input type="hidden" name="tab" value="<?php echo $_GET['tab'];?>">
    <div class="form-group">
        <label>상품코드</label>
        <input type="text" class="form-control" name="it_id" value="<?php echo htmlspecialchars($_GET['it_id']);?>">
    </div>

    <div class="form-group ">
        <label>브랜드명</label>
        <input type="text" class="form-control" name="it_maker" value="<?php echo htmlspecialchars($_GET['it_maker']);?>">
    </div>

    <div class="form-group">
        <label>상품명</label>
        <input type="text" class="form-control" name="it_name" value="<?php echo htmlspecialchars($_GET['it_name']);?>">
    </div>
    <button class="btn btn-primary" type="submit">검색</button>
    <button class="btn btn-warning" type="button" onclick="location.href='./item_member_price_cron_rewrite.php'">적용</button>
</form>
<br>
<div class='row'>
    <div class="col-lg-8 text-right">
        <ul class="nav nav-tabs">
            <li role="presentation" id="tab_upc" class='<?php echo $_GET['tab'] == '' || $_GET['tab'] == 'all' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>">전체</a>
            </li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'fu' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>&tab=fu">진행예정인 상품</a>
            </li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'ing' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>&tab=ing">진행중인 상품</a>
            </li>
            <li role="presentation" id="tab_it_id" class='<?php echo $_GET['tab'] == 'wait' ? 'active' : ''; ?>'>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $get_data; ?>&tab=wait">마감된 상품</a>
            </li>
        </ul>
    </div>
    <div class="col-lg-4 text-right">
        <button class="btn btn-success" type="button" onclick="location.href='./item_member_price_form.php?mode=insert'" >회원 할인가 등록</button>
        <button class="btn btn-primary" type="button" onclick="location.href='./item_member_price_list.php?<?php echo $excel_url;?>&mode=excel'" >엑셀다운로드</button>
    </div>
</div>
<table class="table table-hover">
    <thead>
    <tr>
        <td><strong>상품코드</strong></td>
        <td><strong>이미지</strong></td>
        <td><strong>상품명</strong></td>
        <td><strong>판매가</strong></td>
        <td><strong>회원가</strong></td>
        <td width="90px;"><strong>기간</strong></td>
        <td><strong>등록날짜</strong></td>
        <td><strong>등록ID</strong></td>
        <?php if($_GET['tab'] != 'wait'){?>
            <td></td>
        <?php }?>
    </tr>
    </thead>
    <?php if (!empty($item_member_price_list)) { ?>
        <tbody>
        <?php foreach ($item_member_price_list as $value){ ?>
            <tr>
                <td><a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $value['it_id'];?>"><?php echo $value['it_id'];?></a></td>
                <td><img src="http://115.68.20.84/item/<?php echo $value['it_id'];?>_l1" width="80" height="80"></td>
                <td><?php echo get_item_name($value['it_name'],'list');?></td>
                <td class="text-right"><?php echo $value['it_amount_usd'];?></td>
                <td class="text-right"><?php echo $value['member_price'];?></td>
                <td><?php echo $value['start_date'].'<br>~<br>';?><?php echo (trim($value['end_date']) ? $value['end_date'] :"무기한") ;?></td>
                <td><?php echo $value['create_date'];?></td>
                <td><?php echo $value['create_id'];?></td>
                <?php if($_GET['tab'] != 'wait'){?>
                    <td><button class="btn btn-info" type="button" onclick="location.href='./item_member_price_form.php?mode=update&uid=<?php echo $value['uid'];?>'">수정</button></td>
                <?php } ?>

            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <td class="text-center" colspan="9"><?php echo bootstrap_paging($total_list, $now_page, $list_page, $num_page, $_GET); ?></td>
        </tr>
        </tfoot>
    <? } ?>
</table>
<?
include_once("$g4[admin_path]/admin.tail.php");
?>
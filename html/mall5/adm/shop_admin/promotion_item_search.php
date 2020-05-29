<?php
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2016-10-31
 * Time: 오후 4:15
 */
$sub_menu = "500520";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");
//페이징
$now_page = isset($_GET['page']) ? $_GET['page'] : "1";
$now_page = $now_page == 0 ? 1 : $now_page;
$now_page = is_numeric($now_page) ? $now_page : "1";


$where = '';
if ($_GET['it_id']) {
    if(trim($_GET['it_id'])) {
        $arritid['it_id']=$_GET['it_id'];
        $it_id_arr = explode(PHP_EOL, trim($arritid['it_id']));
        $it_id_cnt = count($it_id_arr);
        if ($it_id_cnt > 0) {
            $it_id_in = '';
            foreach ($it_id_arr as $value) {
                $po_it_id = trim($value);
                if (!$po_it_id) {
                    continue;
                }
                $it_id_in .= ($it_id_in ? "," : "") . "'" . $po_it_id . "'";
            }
            $where .= ($where ? ' and ' : ' where ') . "ci.it_id in (" . $it_id_in . ") ";
        }
    }
}
if ($pr_name) {
    $pr_name=trim($pr_name);
    $where .= ($where == '' ? ' where ' : ' and ') . " p.pr_name like '%{$pr_name}%' ";
}
if ($pr_ca_name) {
    $pr_ca_name=trim($pr_ca_name);
    $where .= ($where == '' ? ' where ' : ' and ') . " c.pr_ca_name like '%{$pr_ca_name}%' ";
}
if ($it_name) {
    $it_name=trim($it_name);
    $where .= ($where == '' ? ' where ' : ' and ') . " i.it_name like '%{$it_name}%' ";
}
if ($pr_dc_st_dt || $pr_dc_en_dt) {
    $pr_dc_st_dt=trim($pr_dc_st_dt);
    $pr_dc_en_dt=trim($pr_dc_en_dt);
    $where .= ($where == '' ? ' where ' : ' and ') . "
            date_format(if(cidc.st_dt IS NULL, p.st_dt, cidc.st_dt),
                                '%Y-%m-%d') <= '{$pr_dc_en_dt}'
                AND date_format(
                       if(cidc.en_dt IS NULL,
                          if(p.en_dt IS NULL, date_format(now(),'%Y-%m-%d'), p.en_dt),
                          cidc.en_dt),
                       '%Y-%m-%d') >= '{$pr_dc_st_dt}' ";
}
if($mode=='downloads'){
    include $g4['full_path'] . '/classes/PHPExcel.php';

    $objPHPExcel = new PHPExcel();
    $excel_title = 'promotionitemlist'.date('Y-m-d');
    $objPHPExcel->getProperties()->setCreator("NTWGLOBAL")
        ->setTitle($excel_title)
        ->setSubject($excel_title)
        ->setDescription($excel_title);
    $objPHPExcel->getActiveSheet()->getCell('A1')->setValueExplicit('프로모션', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('B1')->setValueExplicit('카테고리', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('C1')->setValueExplicit('오플상품번호', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('D1')->setValueExplicit('상품명', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('E1')->setValueExplicit('상품가격(USD)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('F1')->setValueExplicit('상품가격(KRW)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('G1')->setValueExplicit('할인금액(USD)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('H1')->setValueExplicit('할인금액(KRW)', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('I1')->setValueExplicit('시작기간', PHPExcel_Cell_DataType::TYPE_STRING);
    $objPHPExcel->getActiveSheet()->getCell('J1')->setValueExplicit('종료기간', PHPExcel_Cell_DataType::TYPE_STRING);

    $sql=sql_query("select
        distinct
        ci.uid,
        ci.pr_id,
        ci.pr_ca_id,
        ci.it_id,
        ci.icon,
        ci.sort,
        ci.create_dt,
        ci.ip,
        ci.mb_id,
        c.pr_ca_name,
        p.pr_name,
        i.it_name,
        i.it_amount,
        i.it_amount_usd,
        if(cidc.st_dt IS NULL, p.st_dt, cidc.st_dt) pr_dc_st_dt,
        if(cidc.en_dt IS NULL,
          if(p.en_dt IS NULL,date_format(now(),'%Y-%m-%d'), p.en_dt),
          cidc.en_dt)
          pr_dc_en_dt,
        cidc.amount_usd as pr_dc_amount_usd,
        idc.st_dt as it_dc_st_dt,
        idc.en_dt as it_dc_en_dt,
        idc.amount_usd as dc_amount_usd
    from
        yc4_promotion_item ci
        left join
        yc4_item i on ci.it_id = i.it_id
        left join
        yc4_promotion p on ci.pr_id = p.pr_id
        left join
        yc4_promotion_category c on ci.pr_id = c.pr_id and ci.pr_ca_id = c.pr_ca_id
        left join
        yc4_promotion_item_dc cidc on ci.it_id = cidc.it_id and ci.pr_id = cidc.pr_id
        left join
        yc4_promotion_item_dc idc on ci.it_id = idc.it_id and idc.pr_id is null
    {$where}
    order by
        ci.pr_id,
        ifnull(ci.sort,999) asc,
        ifnull(c.sort,999) asc,
        ci.pr_ca_id");
    $pr_item_list = array();
    while ($row = sql_fetch_array($sql)) {
        $pr_item_list[] = $row;
    }
    $line = 2;
    foreach($pr_item_list as $value){
        $objPHPExcel->getActiveSheet()->getCell('A'.$line)->setValueExplicit($value['pr_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('B'.$line)->setValueExplicit($value['pr_ca_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('C'.$line)->setValueExplicit($value['it_id'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('D'.$line)->setValueExplicit($value['it_name'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('E'.$line)->setValueExplicit(usd_convert($value['it_amount']), PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $objPHPExcel->getActiveSheet()->getCell('F'.$line)->setValueExplicit(number_format($value['it_amount']), PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('G'.$line)->setValueExplicit($value['pr_dc_amount_usd']?$value['pr_dc_amount_usd']:'0', PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $objPHPExcel->getActiveSheet()->getCell('H'.$line)->setValueExplicit(number_format(round($value['pr_dc_amount_usd'] * $default['de_conv_pay'])), PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('I'.$line)->setValueExplicit($value['pr_dc_st_dt'], PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->getCell('J'.$line)->setValueExplicit($value['pr_dc_en_dt'], PHPExcel_Cell_DataType::TYPE_STRING);
        $line++;
    }
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth("15");
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth("15");
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth("12");
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("100");
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth("12");
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth("12");
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth("11");
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth("11");
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth("10");
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth("10");

    $objPHPExcel->getActiveSheet()->setTitle($excel_title);
    $filename = iconv("UTF-8", "EUC-KR", $excel_title);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;

}
$row_cnt = sql_fetch("
    select
        count(*) cnt
    from
        yc4_promotion_item ci
        left join
        yc4_item i on ci.it_id = i.it_id
        left join
        yc4_promotion p on ci.pr_id = p.pr_id
        left join
        yc4_promotion_category c on ci.pr_id = c.pr_id and ci.pr_ca_id = c.pr_ca_id
        left join
        yc4_promotion_item_dc cidc on ci.it_id = cidc.it_id and ci.pr_id = cidc.pr_id
        left join
        yc4_promotion_item_dc idc on ci.it_id = idc.it_id and idc.pr_id is null
    {$where}
    order by
        ci.pr_id,
        ifnull(ci.sort,999) asc,
        ifnull(c.sort,999) asc,
        ci.pr_ca_id
");
$total_count = $row_cnt['cnt'];
$total_list = $total_count;//페이징=전체컬럼수
$list_page = 30;//보여줄 컬럼
$num_page = 10;//표시할 페이지

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


$pr_item_sql = sql_query("
    select
        distinct
        ci.uid,
        ci.pr_id,
        ci.pr_ca_id,
        ci.it_id,
        ci.icon,
        ci.sort,
        ci.create_dt,
        ci.ip,
        ci.mb_id,
        c.pr_ca_name,
        p.pr_name,
        i.it_name,
        i.it_amount,
        i.it_amount_usd,
        if(cidc.st_dt IS NULL, p.st_dt, cidc.st_dt) pr_dc_st_dt,
        if(cidc.en_dt IS NULL,
          if(p.en_dt IS NULL, p.st_dt, p.en_dt),
          cidc.en_dt)
          pr_dc_en_dt,
        cidc.amount_usd as pr_dc_amount_usd,
        idc.st_dt as it_dc_st_dt,
        idc.en_dt as it_dc_en_dt,
        idc.amount_usd as dc_amount_usd
    from
        yc4_promotion_item ci
        left join
        yc4_item i on ci.it_id = i.it_id
        left join
        yc4_promotion p on ci.pr_id = p.pr_id
        left join
        yc4_promotion_category c on ci.pr_id = c.pr_id and ci.pr_ca_id = c.pr_ca_id
        left join
        yc4_promotion_item_dc cidc on ci.it_id = cidc.it_id and ci.pr_id = cidc.pr_id
        left join
        yc4_promotion_item_dc idc on ci.it_id = idc.it_id and idc.pr_id is null
    {$where}
    order by
        ci.pr_id,
        ifnull(ci.sort,999) asc,
        ifnull(c.sort,999) asc,
        ci.pr_ca_id
        limit " . ($now_page - 1) * $list_page . "," . $list_page
);
$pr_item_list = array();
while ($row = sql_fetch_array($pr_item_sql)) {
    $pr_item_list[] = $row;
}


define('bootstrap', true);
$g4['title'] = "프로모션 상품 검색";
include '../admin.head.php';
?>
    <style>
        .a {
            display: inline;
        }
    </style>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <form method="get" onsubmit="return resubmit()">
        <div class="col-lg-4">
            <div class="row">
                <div class="col-md-12">
                    <input type="text" class="form-control" value="<?php echo $pr_name; ?>" placeholder="프로모션이름"
                       name="pr_name">
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <input type="text" class="form-control" value="<?php echo $it_name; ?>" placeholder="상품명"
                       name="it_name">
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="row">
                <div class="col-md-12">
                    <input type="text" class="form-control" value="<?php echo $pr_ca_name; ?>" placeholder="카테고리"
                       name="pr_ca_name">
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-6" style="background-color: lightgreen">
                    <input type="text" class="a form-control" id="from" value="<?php echo $pr_dc_st_dt; ?>"
                       placeholder="기간조회시작" name="pr_dc_st_dt" >
                </div>
                <div class="col-md-6" style="background-color: lightgreen">
                    <input type="text" class="form-control" id="to" value="<?php echo $pr_dc_en_dt; ?>" placeholder="기간조회종료"
                           name="pr_dc_en_dt">
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="row">
                <div class="col-md-12">
                    <textarea name="it_id" class="form-control" rows="4" placeholder="오플상품번호 엔터로 구분" style="width: 293px;"><?php echo $_GET['it_id'];?></textarea>
                </div>
            </div>
            <br>
        </div>
                <!--<input type="text" class="form-control" value="<?php /*echo $it_id; */?>" placeholder="오플상품번호" name="it_id">-->
        <br>
       <div class="row text-right" >
           <div class="col-lg-8"></div>
           <div class="col-lg-2">
               <button type="button" class="btn btn-success btn-md btn-block" onclick="location.href='<?php echo $_SERVER['PHP_SELF'];?>?mode=downloads&<?php echo http_build_query($_GET);?>'">엑셀다운로드</button>
           </div>
           <div class="col-lg-2" style="padding: 0">
               <button type="submit" class="btn btn-primary btn-md btn-block"><strong>Search</strong></button>
           </div>
       </div>
    </form>
<br>
    <div class="row text-right">
        <strong><?php echo $total_count; ?>건</strong>
    </div>
    <div class="row">
        <table class="table table-hover table-bordered table-condensed table-striped">
            <thead>
            <tr>
                <td class="text-center" rowspan="3">프로모션<br/>(코드)</td>
                <td class="text-center" rowspan="3">카테고리<br/>(코드)</td>
                <td class="text-center" rowspan="3">오플상품번호</td>

                <td class="text-center" rowspan="3" colspan="2">상품명</td>
                <td class="text-center" rowspan="3">상품가격</td>
                <td class="text-center" colspan="4">할인정보</td>
                <td class="text-center" rowspan="3">아이콘</td>
                <!--                        <td class="text-center" rowspan="3">등록일</td>-->
            </tr>
            <tr>
                <td class="text-center" colspan="2">프로모션</td>
                <td class="text-center" colspan="2">일반할인</td>
            </tr>
            <tr>
                <td class="text-center">할인금액</td>
                <td class="text-center">기간</td>
                <td class="text-center">할인금액</td>
                <td class="text-center">기간</td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pr_item_list as $row) { ?>
                <tr>
                    <td><a href="http://ople.com/mall5/shop/promotion.php?pr_id=<?php echo $row['pr_id'] ?>&preview=1"><?php echo $row['pr_name']; ?> <br/>코드:<?php echo $row['pr_id'] ?></a></td>
                    <td><?php echo $row['pr_ca_id'] ? $row['pr_ca_name'] . '<br/>코드:' . $row['pr_ca_id'] : ''; ?></td>
                    <td><a href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $row['it_id']; ?>"><?php echo $row['it_id']; ?></a></td>
                    <td><?php echo get_it_image($row['it_id'] . '_s', 70, 70, null, null, false, false, false); ?></td>
                    <td><?php echo get_item_name($row['it_name'], 'list'); ?></td>
                    <td>$ <?php echo usd_convert($row['it_amount']); ?><br/>
                        (￦ <?php echo number_format($row['it_amount']); ?>)
                    </td>
                    <td>
                        <?php if ($row['pr_dc_amount_usd']) { ?>
                            $ <?php echo $row['pr_dc_amount_usd']; ?>
                            <br/>(￦ <?php echo number_format(round($row['pr_dc_amount_usd'] * $default['de_conv_pay'])); ?>)
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['pr_dc_st_dt'] || $row['pr_dc_en_dt']) { ?>
                            <?php echo $row['pr_dc_st_dt']; ?> ~ <?php echo $row['pr_dc_en_dt']; ?>
                        <?php } else { ?>
                            프로모션 기간 내
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['it_dc_amount_usd']) { ?>
                            $ <?php echo $row['it_dc_amount_usd']; ?>
                            <br/>(￦ <?php echo number_format(round($row['it_dc_amount_usd'] * $default['de_conv_pay'])); ?>)
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['it_dc_st_dt'] || $row['it_dc_en_dt']) { ?>
                            <?php echo $row['it_dc_st_dt']; ?> ~ <?php echo $row['it_dc_en_dt']; ?>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['icon']) { ?>
                            아이콘 <?php echo $row['icon']; ?>
                        <?php } ?>
                    </td>
                    <!--                            <td>--><?php //echo $row['create_dt'];?><!--</td>-->
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="11" class="text-center">
                    <?php echo paging($total_list, $now_page, $list_page, $num_page, $_GET); //페이징 다시 작업?>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>

    <link rel="stylesheet" href="http://code.jquery.com/ui/1.8.18/themes/base/jquery-ui.css" type="text/css"
          media="all">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <script src="http://code.jquery.com/ui/1.8.18/jquery-ui.min.js" type="text/javascript"></script>
    <script>
        $(function () {
            var dates = $("#from, #to ").datepicker({
                prevText: '이전 달',
                nextText: '다음 달',
                monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
                monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
                dayNames: ['일', '월', '화', '수', '목', '금', '토'],
                dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
                dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
                dateFormat: 'yy-mm-dd',
                showMonthAfterYear: true,
                yearSuffix: '년',
                onSelect: function (selectedDate) {
                    var option = this.id == "from" ? "minDate" : "maxDate",
                        instance = $(this).data("datepicker"),
                        date = $.datepicker.parseDate(
                            instance.settings.dateFormat ||
                            $.datepicker._defaults.dateFormat,
                            selectedDate, instance.settings);
                    dates.not(this).datepicker("option", option, date);
                }
            });
        });
        function resubmit(){
            var st= $.trim($('#from').val());
            var en=$.trim($('#to').val());
            if((st!='' && en=='')|| (st=='' && en!='')){
                alert('시작일과 종료일을 바르게 입력해주세요');
                return false;
            }
            return true;

        }
    </script>
<?php
include '../admin.tail.php';


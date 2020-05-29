<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-08-10
 * Time: 오후 12:02
 */

$sub_menu = "400910";
include "_common.php";
auth_check($auth[$sub_menu], "w");

if($_GET['mode'] == 'auction_order_collect'){
    $result = file_get_contents('http://59.17.43.129/auction/get_auction_order2.php');
    alert("주문서 수집(".preg_replace('/[^0-9]/','',$result)."건)이 완료되었습니다.",$_SERVER['PHP_SELF']);
    exit;

}

if($_POST['mode'] == 'order_insert'){

    $op_cart_id_in = '';
    if(is_array($_POST['op_cart_id'])){
        foreach ($_POST['op_cart_id'] as $op_cart_id) {
            $op_cart_id_in .= ($op_cart_id_in ? ", ":" and ")."'".$op_cart_id."'";
        }
        if($op_cart_id_in){

        }
    }
    $sql = sql_query($a="
        select
            *
        from
        open_market_order a
        where a.od_id is null
        ".$op_cart_id_in."
        and op_cart_id not in (select op_cart_id from open_market_order_item where it_id is null or it_id = '')
    ");

    while($row = sql_fetch_array($sql)){


        list($od_zip1, $od_zip2) = explode('-', $row['od_zip']);
        $od_id = get_new_od_id();
        $on_uid = get_unique_id();
        $sql1 = "
            insert $g4[yc4_order_table]
            set od_id             = '$od_id',
                on_uid            = '$on_uid',
                mb_id             = 'OPEN_MARKET_ORDER',
                od_pwd            = password('OPEN_MARKET_ORDER'),
                od_name           = '" . sql_safe_query($row['od_b_name']) . "',
                od_email          = '',
                od_tel            = '" . sql_safe_query($row['od_phone']) . "',
                od_hp             = '" . sql_safe_query($row['od_hp']) . "',
                od_zonecode		  = '',
                od_zip1           = '$od_zip1',
                od_zip2           = '$od_zip2',
                od_addr1          = '" . sql_safe_query($row['od_addr']) . "',
                od_addr2          = '',
                od_addr_jibeon	  = '',
                od_b_name         = '" . sql_safe_query($row['od_b_name']) . "',
                od_b_tel          = '" . sql_safe_query($row['od_phone']) . "',
                od_b_hp           = '" . sql_safe_query($row['od_hp']) . "',
                od_b_zonecode	  = '',
                od_b_zip1         = '$od_zip1',
                od_b_zip2         = '$od_zip2',
                od_b_addr1        = '" . sql_safe_query($row['od_addr']) . "',
                od_b_addr2        = '',
                od_b_addr_jibeon  = '',
                od_deposit_name   = '',
                od_memo           = '" . sql_safe_query($row['od_memo']) . "',
                od_send_cost      = '',
                od_temp_bank      = '',
                od_temp_card      = '".$row['od_receipt_amount']."',
                od_temp_point     = '',
                od_receipt_bank   = '0',
                od_receipt_card   = '".$row['od_receipt_amount']."',
                od_receipt_point  = '',
                od_bank_account   = '',
                od_shop_memo      = '오픈마켓 주문건 입니다.',
                od_hope_date      = '',
                od_time           = '$g4[time_ymdhis]',
                od_ip             = '" . getenv('REMOTE_ADDR') . "',
                od_settle_case    = '오픈마켓',
                od_b_jumin		= '" . trim($row['od_b_jumin']) . "',
                od_recommend_off_sale = '',
                card_settle_case ='',
                kcp_escrow_point = '',
                open_market_fg = '" . $row['channel'] . "',
                od_pay_time = now(),
                od_status_update_dt = now(),
                od_auto_pay_fg = 'Y'
        ";
        sql_query($sql1);


        # 주문 상품 로드 #
        $order_item_info_sql = sql_query("select * from open_market_order_item where channel = '".$row['channel']."' and op_cart_id = '".$row['op_cart_id']."'");
        while($item_row = sql_fetch_array($order_item_info_sql)){
            $sql2 = "
                INSERT INTO
                    {$g4['yc4_cart_table']}
                SET
                    on_uid = '" . $on_uid . "',
                    it_id = '" . $item_row['it_id'] . "',
                    it_opt1 = '',
                    it_opt2 = '',
                    it_opt3 = '',
                    it_opt4 = '',
                    it_opt5 = '',
                    it_opt6 = '',
                    ct_status = '준비',
                    ct_history = '오픈마켓 주문건(" . $row['channel'] . ")',
                    ct_amount = '" . $item_row['ct_amount'] . "',
                    ct_point = 0,
                    ct_point_use = 0,
                    ct_stock_use = 0,
                    ct_qty = '" . $item_row['op_qty'] . "',
                    ct_time = '" . date('Y-m-d H:i:s') . "',
                    ct_ip = '" . $_SERVER['REMOTE_ADDR'] . "',
                    ct_send_cost = '',
                    ct_mb_id = '',
                    ct_ship_os_pid = '',
                    ct_ship_ct_qty = '',
                    ct_ship_stock_use = '',
                    open_market_fg = '".$row['channel']."',
                    ct_status_update_dt = now()

            ";
            sql_query($sql2);
        }

        sql_query("update open_market_order set od_id = '".$od_id."' where op_cart_id = '".$row['op_cart_id']."'");


    }


    alert('주문서 작성이 완료되었습니다',$_SERVER['PHP_SELF']);
    exit;

}

if ($_POST['mode'] == 'excel_upload') {
    include $g4['full_path'] . '/classes/PHPExcel.php';


    $UpFile = $_FILES["excel_file"];
    $UpFileName = $UpFile["name"];

    $UpFilePathInfo = pathinfo($UpFileName);
    $UpFileExt = strtolower($UpFilePathInfo["extension"]);


    if ($UpFileExt != "xls" && $UpFileExt != "xlsx") {
        echo "엑셀파일만 업로드 가능합니다. (xls, xlsx 확장자의 파일포멧)";
        exit;
    }

//-- 읽을 범위 필터 설정 (아래는 A열만 읽어오도록 설정함  => 속도를 중가시키기 위해)
    class MyReadFilter implements PHPExcel_Reader_IReadFilter
    {
        public function readCell($column, $row, $worksheetName = '')
        {
            // Read rows 1 to 7 and columns A to E only
            if (in_array($column, range('A', 'AL'))) {
                return true;
            }
            return false;
        }
    }

    $filterSubset = new MyReadFilter();

//업로드된 엑셀파일을 서버의 지정된 곳에 옮기기 위해 경로 적절히 설정
    $upload_path = $g4["full_path"] . "/data/tmp";
    $upfile_path = $upload_path . "/" . date("Ymd_His") . "_" . $UpFileName;
    if (is_uploaded_file($UpFile["tmp_name"])) {

        if (!move_uploaded_file($UpFile["tmp_name"], $upfile_path)) {
            echo "업로드된 파일을 옮기는 중 에러가 발생했습니다.";
            exit;
        }

        //파일 타입 설정 (확자자에 따른 구분)
        $inputFileType = 'Excel2007';
        if ($UpFileExt == "xls") {
            $inputFileType = 'Excel5';
        }

        //엑셀리더 초기화
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);

        //데이터만 읽기(서식을 모두 무시해서 속도 증가 시킴)
        $objReader->setReadDataOnly(true);

        //범위 지정(위에 작성한 범위필터 적용)
//        $objReader->setReadFilter($filterSubset);

        //업로드된 엑셀 파일 읽기
        $objPHPExcel = $objReader->load($upfile_path);

        //첫번째 시트로 고정
        $objPHPExcel->setActiveSheetIndex(0);

        //고정된 시트 로드
        $objWorksheet = $objPHPExcel->getActiveSheet();

        //시트의 지정된 범위 데이터를 모두 읽어 배열로 저장
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
        $total_rows = count($sheetData);

        $i = 0;
        /*
         *
    [A] => 아이디
    [B] => 상품번호
    [C] => 주문번호
    [D] => 주문일자(결제확인전)
    [E] => 판매금액
    [F] => 판매단가
    [G] => 구매자명
    [H] => 구매자ID
    [I] => 상품명
    [J] => 수량
    [K] => 주문옵션
    [L] => 추가구성
    [M] => 사은품
    [N] => 수령인명
    [O] => 수령인 휴대폰
    [P] => 수령인 전화번호
    [Q] => 우편번호
    [R] => 주소
    [S] => 배송시 요구사항
    [T] => 배송번호
    [U] => 배송비
    [V] => 배송비 금액
    [W] => 택배사명(발송방법)
    [X] => 송장번호
    [Y] => 장바구니번호(결제번호)
    [Z] => 구매자 휴대폰
    [AA] => 구매자 전화번호
    [AB] => 발송예정일
    [AC] => 배송지연사유
    [AD] => 판매자 관리코드
    [AE] => 판매자 상세관리코드
    [AF] => 주문확인일자
    [AG] => 서비스이용료
    [AH] => 정산예정금액
    [AI] => 판매자쿠폰할인
    [AJ] => 판매자포인트적립
    [AK] => 일시불할인
    [AL] => (옥션)복수구매할인
    [AM] => (옥션)우수회원할인
    [AN] => 판매방식
    [AO] => 결제완료일
    [AP] => 구매쿠폰적용금액
    [AQ] => 주문종류
    [AR] => SKU번호 및 수량
    [AS] => 수취인 통관번호
    [AT] => 수취인 주민번호
         * */

        foreach ($sheetData as $rows) {
            if ($i > 0) {
                if (strpos($rows['A'], '옥션') !== false) {
                    $channel = 'A';
                } elseif (strpos($rows['A'], '지마켓') !== false) {
                    $channel = 'G';
                }

                if (!$channel) {
                    continue;
                }

                $op_cart_id = sql_safe_query($rows['Y']);
                $order_table_chk = sql_fetch($a="select count(*) as cnt from open_market_order where channel = '" . $channel . "' and op_cart_id = '" . $op_cart_id . "'");

                if ($channel == 'A') {
                    continue;
                }

                if ($order_table_chk['cnt'] < 1) {


                    $od_b_jumin = '';
                    if ($rows['AS']) {
                        $od_b_jumin = $rows['AS'];
                    }
                    if ($rows['AT']) {
                        $od_b_jumin = $rows['AT'];
                    }


                    sql_query("insert into open_market_order
                    (
                      channel,op_cart_id,op_mb_id,op_time,op_pay_time,
                      od_b_name,od_hp,od_phone,od_zip,od_addr,od_memo,
                      od_b_jumin,od_receipt_amount,create_dt

                    ) VALUE
                    (
                      '" . $channel . "','" . sql_safe_query($rows['Y']) . "','" . sql_safe_query($rows['H']) . "','" . sql_safe_query($rows['D']) . "','" . sql_safe_query($row['AO']) . "',
                      '" . sql_safe_query($rows['N']) . "','" . sql_safe_query($rows['O']) . "','" . sql_safe_query($rows['P']) . "','" . sql_safe_query($rows['Q']) . "','" . sql_safe_query($rows['R']) . "','" . sql_safe_query($rows['S']) . "',
                      '" . $od_b_jumin . "','" . sql_safe_query(preg_replace('/[^0-9]/','',$rows['E'])) . "',now()
                    )");



                }

                $cart_table_chk = sql_fetch("select count(*) as cnt from open_market_order_item where channel = '" . $channel . "' and op_cart_id = '" . $op_cart_id . "' and op_od_id = '" . $rows['C'] . "'");
                if ($cart_table_chk['cnt'] < 1) {
                    $option_name = '';
                    if ($channel == 'A') {
                        $it_id = sql_fetch("select it_id from auction_mapping where auction_itemid = '" . sql_safe_query($rows['B']) . "'");
                        $it_id = $it_id['it_id'];
                    } elseif ($channel == 'G') {
                        if ($rows['K']) {
                            $option_name = preg_replace('/옵션명:선택(\d)+.\s/', '', $rows['K']);
                            $option_name = preg_replace('/\/\d개/', '', $option_name);
                            $it_id = sql_fetch("select it_id from gmarket_mapping where gmarket_itemid = '" . sql_safe_query($rows['B']) . "' and option_name = '" . sql_safe_query($option_name) . "'");
                        } else {
                            $it_id = sql_fetch("select it_id from gmarket_mapping where gmarket_itemid = '" . sql_safe_query($rows['B']) . "'");
                        }

                        $it_id = $it_id['it_id'];

                    }

                    sql_query("
                      insert into open_market_order_item
                      (channel,op_cart_id,op_od_id,it_id,op_item_name,op_item_option_name,op_qty,ct_amount) VALUES
                      ('" . $channel . "','" . sql_safe_query($rows['Y']) . "','" . sql_safe_query($rows['C']) . "','" . sql_safe_query($it_id) . "','".sql_safe_query($rows['I'])."','" . sql_safe_query($option_name) . "','" . sql_safe_query($rows['J']) . "','".preg_replace('/[^0-9]/','',$rows['F'])."')
                    ");
                }





            }
            $i++;
        }


    }

    alert('주문서 업로드가 완료되었습니다.',$_SERVER['PHP_SELF']);

    exit;
}

$sql = sql_query("
    select
        channel,
        op_cart_id,
        od_b_name,
        op_time
    from
    open_market_order a
    where a.od_id is null
");
$list_tr = '';
$mapping_false = false;
while ($row = sql_fetch_array($sql)) {

    $od_item_sql = sql_query("select * from open_market_order_item where channel = '" . $row['channel'] . "' and op_cart_id = '" . $row['op_cart_id'] . "'");
    $order_item_list = '';
    $item_cnt = 0;
    while ($item_row = sql_fetch_array($od_item_sql)) {
        $item_cnt += $item_row['op_qty'];
        $item_name = $item_row['op_item_name'];
        if($row['op_item_option_name']){
            $item_name = $item_row['op_item_option_name'];
        }
        if(!$item_row['it_id']){
            $row['mapping_false'] = true;
            $mapping_false = true;
        }else{
            $row['mapping_false'] = false;
        }
        $order_item_list .= "<tr".($row['mapping_false'] ? " class='danger'":"").">
            <td>".$item_row['op_od_id']."</td>
            <td>".$item_name."</td>
            <td>".$item_row['op_qty']."</td>
            <td>".$item_row['it_id']."</td>

        </tr>";
    }
    if($order_item_list){

        $order_item_list = "
            <table class='table table-condensed'>
                <thead>
                   <tr>
                        <td>주문번호</td>
                        <td>상품명</td>
                        <td>수량</td>
                        <td>오플상품코드</td>
                    </tr>
                </thead>
                <tbody>".$order_item_list."</tbody>
            </table>
        ";
    }


    $list_tr .= "
        <tr>
            <td>" . $row['channel'] . "</td>
            <td>" . $row['op_cart_id'] . "</td>
            <td>" . $row['od_b_name'] . "</td>
            <td>" . $row['op_time'] . "</td>
            <td>" . $item_cnt . "</td>
            <td>".$order_item_list."</td>
        </tr>

    ";
}

define('bootstrap', true);
$g4[title] = "오픈마켓 주문서 업로드";
include $g4['full_path'] . "/adm/admin.head.php";


?>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="excel_upload">

        <div class="input-group">
            <span class="input-group-addon">신규 주문 엑셀파일 업로드</span>
            <input type="file" name="excel_file" class="form-control"/>
            <span class="input-group-btn">
                <button class="btn btn-primary" type="submit">등록</button>
            </span>
        </div>
    </form>

    <?php if($mapping_false){?>
    <div class="alert alert-danger">
        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
        매핑 실패 상품이 존재합니다
    </div>
    <?php }?>

    <div class="panel panel-default">
        <form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
            <input type="hidden" name="mode" value="order_insert" />

            <div class="panel-heading">
                <button class="btn btn-primary" type="submit">오플 주문서 입력</button>
                <a href="<?php echo $_SERVER['PHP_SELF'];?>?mode=auction_order_collect" class="btn btn-info">옥션 주문서 자동수집</a>
            </div>

            <table class="table table-condensed">
                <thead>
                <tr>
                    <td><strong>채널</strong></td>
                    <td><strong>결제번호 (장바구니번호)</strong></td>
                    <td><strong>이름</strong></td>
                    <td><strong>주문일자</strong></td>
                    <td><strong>총 상품 수량</strong></td>
                    <td><strong>주문상품정보</strong></td>
                </tr>

                </thead>
                <tbody>
                <?php echo $list_tr; ?>
                </tbody>
            </table>
            <div class="panel-footer">
                <button class="btn btn-primary" type="submit">오플 주문서 입력</button>
            </div>
        </form>
    </div>

<?php
include $g4['full_path'] . "/adm/admin.tail.php";

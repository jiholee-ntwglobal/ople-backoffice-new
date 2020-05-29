<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-05-12
 * Time: 오후 2:28
 */
$sub_menu = "500520";
include_once("./_common.php");
include $g4['full_path'] . '/lib/db_bs.php';
$db = new db();
$db->ntics_db->query("USE ntshipping");
auth_check($auth[$sub_menu], "r");
$_GET['ymd'] = $_GET['ymd'] ? trim($_GET['ymd']) : date("Y-m-d", strtotime("-1 day"));
//tab
if (!$_GET['tab'] || $_GET['tab'] == '' || $_GET['tab'] != 'N') {
    $_GET['tab'] = 'O';
}

//where
$where = '';
//data
$cancel_data = array();
// O or N
$time_data = array();
if ($_GET['tab'] == 'O') {
    $ymd = preg_replace("/[^0-9]*/s", "", $_GET['ymd']);
    $sql = sql_query("
                SELECT DISTINCT b.ct_id , date_format(a.od_pay_time, '%Y-%m-%d') kortime
                FROM yc4_order    a
                     INNER JOIN yc4_cart b
                        ON     a.on_uid = b.on_uid
                           AND b.ct_status = '취소'
                           AND date_format(a.od_pay_time, '%Y%m%d') = '" . $ymd . "'
                ");
//echo "  SELECT DISTINCT b.ct_id  , date_format(a.od_pay_time, '%Y%m%d') time
//                FROM yc4_order    a
//                     INNER JOIN yc4_cart b
//                        ON     a.on_uid = b.on_uid
//                           AND b.ct_status = '취소'
//                           AND date_format(a.od_pay_time, '%Y%m%d') = '" . $ymd . "'";
    while ($row = sql_fetch_array($sql)) {
        //where
        $where .= ($where == '' ? ' ' : ' , ') . "'" . $row['ct_id'] . "'";
        //time_data
        $time_data[$row['ct_id']] =  $row;
    }
    $sql = "
                SELECT a.od_id, c.it_id, c.status as ct_status,c.ct_id,c.cdate usatime
                FROM ntshipping.dbo.NS_S01    a
                     INNER JOIN ntshipping.dbo.NS_S03 c
                        on     c.on_uid = a.on_uid
                           AND c.status != '취소(CANCEL)'
                             AND c.ct_id in (" . $where . ")
              ";
    $result = $db->ntics_db->prepare($sql);
    $result->execute();
    // $cancel_data = $result->fetchAll(PDO::FETCH_ASSOC);
    $int = 1;
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

        if(isset($time_data[$row['ct_id']])){
            $cancel_data[$int] = $row;
            $cancel_data[$int++]['kortime'] = $time_data[$row['ct_id']]['kortime'];
        }
    }

} elseif ($_GET['tab'] == 'N') {
    $sql = "
                SELECT ct_id, cdate usatime
                FROM ntshipping.dbo.ns_s03
                WHERE cdate = '" . $_GET['ymd'] . "' 
                AND status = '취소(CANCEL)'
              ";

    $result = $db->ntics_db->prepare($sql);
    $result->execute();
    $row2 = $result->fetchAll();
    foreach ($row2 as $row) {
        $where .= ($where == '' ? ' ' : ' , ') . "'" . $row['ct_id'] . "'";
        $time_data[$row['ct_id']] = $row;
    }
    if ($where) {
        $sql = "
                  SELECT DISTINCT od_id, it_id, ct_status ,b.ct_id , date_format(a.od_pay_time, '%Y-%m-%d') kortime
                  FROM yc4_order a INNER JOIN yc4_cart b ON a.on_uid = b.on_uid
                  WHERE ct_status NOT IN ('취소', '품절') 
                  AND ct_id in (" . $where . ")
                ";
        $result = sql_query($sql);
        $int = 1;
        while ($row = sql_fetch_array($result)) {
            //where
            if(isset($time_data[$row['ct_id']])){
                $cancel_data[$int] = $row;
                $cancel_data[$int++]['usatime'] = $time_data[$row['ct_id']]['usatime'];
            }

        }
    }
}
$tab_url = $_GET;
unset($tab_url['tab']);
$tab_url = http_build_query($tab_url);
define('bootstrap', true);
$g4['title'] = "취소 주문서 체크";
include '../admin.head.php';
?>

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>취소 주문서 체크</h4>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <span style="color: red;">※ OPLE -> NTICS (오플 결제일자 기준) 오플서버에서는 취소가 되어있지만 배송에서는 취소가 안되있는 주문서</span>
        <br>
        <span style="color: red;">※ NTICS -> OPLE (배송데이터 만들어진날짜 기준) 배송서버에는 취소가 되어있지만 오플서버에서는 취소가 안되어있는 주문서</span>
        <br>
        <span style="color: red;">※ 미국->16 시간->한국 (시차)</span>
    </div>
</div>
<form>
    <div class="row">
        <div class="col-lg-7">
        </div>
        <div class="col-lg-5 form-inline text-right">
            <?php if ($_GET['tab'] == 'N')
            {
                echo"미국";
            }else {
                echo"한국";
            } ?>날짜 :
            <input type="text" class="form-control " id="datepicker1" name="ymd" value="<?php echo $_GET['ymd']; ?>"
                   readonly>
            <input type="hidden" class="form-control " id="datepicker1" name="tab" value="<?php echo $_GET['tab']; ?>"
                   readonly>
            <button class="btn">검색</button>
        </div>
    </div>
</form>
<div class='row'>
    <div class="col-lg-12 text-right">
        <ul class="nav nav-tabs">
            <li role="presentation" class='<?php echo $_GET['tab'] == 'O' ? 'active' : ''; ?>'><a
                        href="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $tab_url; ?>&tab=O">OPLE->NTICS</a></li>
            <li role="presentation" class='<?php echo $_GET['tab'] == 'N' ? 'active' : ''; ?>'><a
                        href="<?php echo $_SERVER['PHP_SELF']; ?>?&<?php echo $tab_url; ?>&tab=N">NTICS->OPLE</a></li>
        </ul>
    </div>
</div>
<div>
    <?php if ($_GET['tab'] == 'N') { ?>
        <span style="color: red;">※ NTICS -> OPLE  주문서 클릭시 해당 주문서 수정 페이지로 넘어감니다 </span>
    <?php } else { ?>
        <span style="color: red;">※ OPLE -> NTICS  NFS프로그램에서 수정  </span>
    <?php } ?>
</div>
<table class="table">
    <thead>
    <tr>
        <th>주문서</th>
        <th>상품</th>
        <th>상태</th>
        <th>한국시간</th>
        <th>미국시간</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($cancel_data)) { ?>
        <tr>
            <td colspan="2" class="text-center"><?php echo "자료가 한건도 없습니다."; ?></td>
        </tr>

    <?php } else { ?>
        <?php foreach ($cancel_data as $row) { ?>
            <tr>
                <td><?php echo $_GET['tab'] == 'N' ? "<a href=\"./orderform.php?od_id=" . $row['od_id'] . "\" target=\"_blank;\">" . $row['od_id'] . "</a>" : $row['od_id']; ?></td>
                <td><?php echo $row['it_id']; ?></td>
                <td><?php echo $row['ct_status']; ?></td>
                <td><?php echo $row['kortime']; ?></td>
                <td><?php echo $row['usatime']; ?></td>
            </tr>
        <?php }
    } ?>
    </tbody>
</table>

<link rel="stylesheet" href="//code.jquery.com/ui/1.8.18/themes/base/jquery-ui.css"/>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.8.18/jquery-ui.min.js"></script>
<script>
    $.datepicker.setDefaults({
        dateFormat: 'yy-mm-dd',
        prevText: '이전 달',
        nextText: '다음 달',
        monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        dayNames: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        showMonthAfterYear: true,
        yearSuffix: '년'
    });

    $(function () {
        $("#datepicker1").datepicker();
    });

</script>


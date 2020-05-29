<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-09-08
 * Time: 오전 9:21
 */

$sub_menu = "400940";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

# 포인트 지급 대상 리스트
$point_mb = $point_mb2 = array();

// 페이지 사용 일시 중단 2017-01-02 강경인
alert("현재 잠시 사용이 중단된 메뉴입니다.");

# 1회 구매 시작 #

# 3000 포인트 지급자 리스트
$point_sql = sql_query("select distinct value1 as mb_id from yc4_event_data where ev_code = 'attendance' and ev_data_type = 'point_insert' and value3 = '3000'");
$no_mb_id = '';
while($row = sql_fetch_array($point_sql)){
    $no_mb_id .= ($no_mb_id ? ",":"") . "'".$row['mb_id']."'";
}

$where = '';
if($no_mb_id){
    $where .= ($where ? " and ":" where ")."mb_id not in (".$no_mb_id.")";
}

# 20회 이상 출석 체크 회원 로드 #
$sql = sql_query("select mb_id,count(*) as cnt from attendance {$where} group by mb_id having cnt >= 20");
//$sql = sql_query("select mb_id,count(*) as cnt from attendance where mb_id='aridda35' group by mb_id having cnt >= 20");
while($row = sql_fetch_array($sql)){

    $buy_chk = sql_fetch("
            select
                o.od_id
            from
                yc4_order o
                left outer join
                yc4_cart c on c.on_uid = o.on_uid
                left outer join
                yc4_event_data e on e.ev_code = 'attendance' and e.ev_data_type = 'point_insert' and o.od_id = e.value2
            where c.ct_status = '완료'
                and o.od_receipt_bank + o.od_receipt_card >= 50000
                and date_format(o.od_time,'%Y%m%d') between '20150803' and '20150828'
                and mb_id='$row[mb_id]'
                and e.uid is null
            group by o.od_id");

    if($buy_chk['od_id'] && !isset($point_mb[$row['mb_id']])){
        $point_mb[$row['mb_id']] = $buy_chk['od_id'];
    }
}


$insert_sql = "insert into yc4_event_data (ev_code,ev_data_type,value1,value2,value3,value4) values ".PHP_EOL;
$insert_val = '';
foreach ($point_mb as $mb_id => $od_id) {
    $insert_val .= ($insert_val ? ",":"")."('attendance','point_insert','".$mb_id."','".$od_id."','3000',now())".PHP_EOL;
}
if($insert_val){
    sql_query($insert_sql.$insert_val);
}


# 1회 구매 끝 #




# 2회 구매 시작 #

$where = '';
# 3000 포인트 지급자 리스트
$point_sql = sql_query("select distinct value1 as mb_id from yc4_event_data where ev_code = 'attendance' and ev_data_type = 'point_insert' and value3 = '3000'");
$mb_id_in = '';
while($row = sql_fetch_array($point_sql)){
    $mb_id_in .= ($mb_id_in ? ",":"") . "'".$row['mb_id']."'";
}
if($mb_id_in){
    $where .= ($where ? " and ":" where ")."mb_id in (".$mb_id_in.")";
}

# 4000 포인트 지급자 리스트
$point_sql = sql_query("select distinct value1 as mb_id from yc4_event_data where ev_code = 'attendance' and ev_data_type = 'point_insert' and value3 = '4000'");
$no_mb_id = '';
while($row = sql_fetch_array($point_sql)){
    $no_mb_id .= ($no_mb_id ? ",":"") . "'".$row['mb_id']."'";
}
if($no_mb_id){
    $where .= ($where ? " and ":" where ")."mb_id not in (".$no_mb_id.")";
}

# 20회 이상 출석 체크 회원 로드 #
$sql = sql_query("select mb_id,count(*) as cnt from attendance {$where} group by mb_id having cnt >= 20");

//$sql = sql_query("select mb_id,count(*) as cnt from attendance where mb_id='aridda35' group by mb_id having cnt >= 20");
while($row = sql_fetch_array($sql)){

    $buy_chk = sql_fetch("
            select
                o.od_id
            from
                yc4_order o
                left outer join
                yc4_cart c on c.on_uid = o.on_uid
                left outer join
                yc4_event_data e on e.ev_code = 'attendance' and e.ev_data_type = 'point_insert' and o.od_id = e.value2
            where c.ct_status = '완료'
                and o.od_receipt_bank + o.od_receipt_card >= 50000
                and date_format(o.od_time,'%Y%m%d') between '20150803' and '20150828'
                and mb_id='$row[mb_id]'
                and e.uid is null
            group by o.od_id");

    if($buy_chk['od_id'] && !isset($point_mb2[$row['mb_id']])){
        $point_mb2[$row['mb_id']] = $buy_chk['od_id'];
    }
}

$insert_sql = "insert into yc4_event_data (ev_code,ev_data_type,value1,value2,value3,value4) values ".PHP_EOL;
$insert_val = '';
foreach ($point_mb2 as $mb_id => $od_id) {
    $insert_val .= ($insert_val ? ",":"")."('attendance','point_insert','".$mb_id."','".$od_id."','4000',now())".PHP_EOL;
}
if($insert_val){
    sql_query($insert_sql.$insert_val);
}

# 2회 구매 끝 #


# 포인트 지급 처리 #
$sql = sql_query("select uid,value1,value2,value3 from yc4_event_data where ev_code = 'attendance' and ev_data_type='point_insert' and value5 is null");
while($row = sql_fetch_array($sql)) {
    insert_point($row['value1'],$row['value3'],'출석체크 기간 중 5만원 이상 구매 '.number_format($row['value3']).'포인트 지급');
    sql_query("update yc4_event_data set value5 = now() where uid = '".$row['uid']."'"); // 포인트 지급 시간 기록
}

# 지급 데이터 로드 #
$sql = sql_query("select value1,value2,value3,value4 from yc4_event_data where ev_code = 'attendance' and ev_data_type='point_insert' order by value4 desc, value1, value3 asc");
$list_tr = '';
while($row = sql_fetch_array($sql)){
    $list_tr .= "
        <tr>
            <td>{$row['value1']}</td>
            <td>{$row['value2']}</td>
            <td>".number_format($row['value3'])."</td>
            <td>{$row['value4']}</td>
        </tr>
    ";
}


$g4['title'] = "출석체크 이벤트 포인트 지급";
include_once $g4['admin_path']."/admin.head.php";
?>

<table>
    <thead>
    <tr>
        <th>아이디</th>
        <th>주문번호</th>
        <th>지급포인트</th>
        <th>시간</th>
    </tr>
    </thead>
    <tbody>
        <?php echo $list_tr;?>
    </tbody>
</table>


<?php
include_once $g4['admin_path']."/admin.tail.php";
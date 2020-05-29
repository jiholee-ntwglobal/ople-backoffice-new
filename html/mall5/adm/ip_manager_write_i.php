<?php
$sub_menu = "100999";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");
$g4['title'] = "ip수정및등록";
include_once ("./admin.head.php");
//받은값
$user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : "";
$user_explanation = isset($_GET['user_explanation']) ? trim($_GET['user_explanation']) : "";
$start_ip = isset($_GET['start_ip']) ? trim($_GET['start_ip']) : "";
$end_ip = ($_GET['end_ip'] != "") ? trim($_GET['end_ip']) : trim($_GET['start_ip']);
$uid = isset($_GET['uid']) ? $_GET['uid'] : "";
$dates = date("Y-m-d H:i:s", time());

//중복확인
$lap01 = ($uid != "") ? "select count(uid)cnt from ip_manager where (INET_ATON('" . $start_ip . "') between start_ip_num and end_ip_num) and user_explanation='" . $user_explanation . "'"
    : "select count(uid)cnt from ip_manager where INET_ATON('" . $start_ip . "') between start_ip_num and end_ip_num";
$resultlap01 = sql_query($lap01) or die("중복확인 실패" . mysqli_error());
$resultlap01_row = sql_fetch_array($resultlap01);
$resultlap01_row_cnt = $resultlap01_row['cnt'];
$lap02 = ($uid != "") ? "select count(uid)cnt from ip_manager where (INET_ATON('" . $end_ip . "') between start_ip_num and end_ip_num) and user_explanation='" . $user_explanation . "'"
    : "select count(uid)cnt from ip_manager where INET_ATON('" . $end_ip . "') between start_ip_num and end_ip_num";
$resultlap02 = sql_query($lap02) or die("중복확인 실패" . mysqli_error());
$resultlap02_row = sql_fetch_array($resultlap02);
$resultlap02_row_cnt = $resultlap02_row['cnt'];
// 반대로 입력
if (ip2long($start_ip) > ip2long($end_ip)) {
    $msg = 'ip 대역 범위를 잘못 지정했습니다.';
    ?>
    <script>
        alert("<?php echo $msg ?>");
        history.back();
    </script>
    <?php
} else {
//update
    if ($uid != "") {
        if ($resultlap01_row_cnt != "1" || $resultlap02_row_cnt != "1") {
            $sql = "update ip_manager set start_ip ='" . $start_ip . "' ,end_ip ='" . $end_ip . "' ,start_ip_num = INET_ATON('" . $start_ip . "') ,
                                    end_ip_num = INET_ATON('" . $end_ip . "'),user_explanation='" . $user_explanation . "',user_id='" . $user_id . "',create_dt='" . $dates . "'
                                     where  uid ='" . $uid . "'";
            $result = sql_query($sql);
        }
        if (isset($result)) { //update 했을경 우
            $msg = '수정되었습니다.';
            $reurl = './ip_manager.php'; ?>
            <script>
                alert("<?php echo $msg ?>");
                location.replace("<?php echo $reurl ?>");
            </script>
        <?php } else { // update못했을 경 우
            $msg = '수정을 못했습니다.'
            ?>
            <script>
                alert("<?php echo $msg ?>");
                history.back();
            </script>
        <?php }
    } else {

//insert
        if ($resultlap01_row_cnt != "1" || $resultlap02_row_cnt != "1") {
            $sql = "insert into ip_manager( start_ip,end_ip,start_ip_num,end_ip_num,user_explanation,user_id,create_dt)
        values ('" . $start_ip . "','" . $end_ip . "', INET_ATON('" . $start_ip . "'), INET_ATON('" . $end_ip . "'),'" . $user_explanation . "','" . $user_id . "','" . $dates . "')";
            $result = sql_query($sql);
        }
        if (isset($result)) { //insert 했을경우
            $msg = '등록되었습니다.';
            $reurl = './ip_manager.php'; ?>
            <script>
                alert("<?php echo $msg ?>");
                location.replace("<?php echo $reurl ?>");
            </script>
        <?php } else { //insert 못했을경우
            $msg = '아이피가 중복되거나 잘못 입력하여 등록을 못했습니다.';
            ?>
            <script>
                alert("<?php echo $msg ?>");
                history.back();
            </script>
        <?php }
     }
} ?>
<?
include_once("./admin.tail.php");
?>
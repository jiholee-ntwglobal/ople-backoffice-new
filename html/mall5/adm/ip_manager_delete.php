<?php
$sub_menu = "100999";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");

//삭제되는 uid
$uid = trim($_GET['uid']);

//delete 쿼리
$query = "delete from ip_manager where uid = '" . $uid . "'";
$result = sql_query($query);
$g4['title'] = "기본환경설정";
include_once ("./admin.head.php");
// 쿼리실행 후
if ($result) {
    $msg = '삭제되었습니다.';
    $reurl = './ip_manager.php';
} else {
    $msg = '삭제하지못했습니다.';
    ?>
    <script>
        alert("<?php echo $msg ?>");
        history.back();
    </script>
<?php } ?>
<script>
    alert("<?php echo $msg ?>");
    location.replace("<?php echo $reurl ?>");
</script>
<?
include_once("./admin.tail.php");
?>
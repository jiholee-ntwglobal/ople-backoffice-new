<?php
include_once("./_common.php");


$comment= trim($_POST['comments']);
$it_id =trim($_POST['it_ids']);
$id = trim($_POST['id']);


// it_id 가 테이블에 존재하면 update 존재 하지 않으면 insert
//comment가 원래 값이 없는데  값 없이 저장을 누르면 다시 백
$sql = "select count(it_id) cnt,comment
      from test_yc4_item_img_none
      where it_id='" . $it_id . "'";
$result = sql_query($conn, $sql);
$row = sql_fetch_array($result);
if ($row['cnt'] == '1') {
    $str = strcmp(trim($row['comment']), $comment);
    if (!$str) {
        $msg = "내용이 같습니다.";
        ?>
        <script>
            alert("<?php echo $msg ?>");
            history.back();
        </script>
    <?php } else {
        $sql="update test_yc4_item_img_none
            set comment ='".$comment."',id = '".$id. "',create_dt= now() where it_id ='".$it_id."'";
        $result = sql_query($sql);
    }
}else{
    $sql="insert into test_yc4_item_img_none(it_id,id,comment,create_dt)
         VALUES ('".$it_id."','".$id."','".$comment."',now())";
    $result = sql_query( $sql);
}

?>
<script>
    location.replace("http://209.216.56.107/mall5/adm/shop_admin/item_img_none.php?<?php echo $_POST['urls'];?>");
</script>
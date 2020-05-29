<?php
/**
 * Created by PhpStorm.
 * File name : ople_item_add_lms_contents.php.
 * Comment :
 * Date: 2016-04-15
 * User: Minki Hong
 */

$sub_menu = "500900";
include './_common.php';

auth_check($auth[$sub_menu], "w");
include '../admin.head.php';

$sql = sql_query("
    select 
    a.*,b.mb_name
    from 
    yc4_add_item_lms_contents a
    left JOIN 
    g4_member b on a.mb_id = b.mb_id
");
$data = array();
while ($row = sql_fetch_array($sql)){
    $data[] = $row;
}
?>

<table width="100%" border="1" style="border-collapse: collapse;">
    <thead>
    <tr>
        <td>제목</td>
        <td>기간</td>
        <td><?php echo icon('입력',$g4['shop_admin_path'].'/ople_item_add_lms_contents_write.php')?></td>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $row) :?>
        <tr>
            <td><?php echo $row['title']?></td>
            <td><?php echo $row['st_dt']?>~<?php echo $row['en_dt']?></td>
            <td><?php echo icon('수정','ople_item_add_lms_contents_write.php?uid='.$row['uid'])?></td>
        </tr>
    <?php endforeach;?>
    </tbody>

</table>


<?php
include '../admin.tail.php';

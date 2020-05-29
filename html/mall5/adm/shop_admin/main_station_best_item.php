<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-04-01
 * Time: 오후 5:23
 */

$sub_menu = "600700";
include "_common.php";
auth_check($auth[$sub_menu], "r");

if(!isset($_GET['s_id'])){
    $_GET['s_id'] = 3;
}

$qstr = $qstr2 = $_GET;
unset($qstr['s_id'],$qstr2['fg']);
$qstr = http_build_query($qstr);
$qstr2 = http_build_query($qstr2);


# 관 로드 #
$st_arr = array();
$st = sql_query("select s_id,name from yc4_station where s_id<>6 order by sort");
$st_li = "<li><a href='".$_SERVER['PHP_SELF']."?".$qstr."'>전체</a></li>";
while($row = sql_fetch_array($st)){
    $st_li .= "<li ".($_GET['s_id'] == $row['s_id'] ? "class='active'":"")."><a href='".$_SERVER['PHP_SELF']."?".$qstr."&s_id=".$row['s_id']."'>".$row['name']."</a></li>";
    $st_arr[$row['s_id']] = $row['name'];
}


$sql_search = '';
switch($_GET['fg']){
    case 'Y' : $sql_search .= ($sql_search ? " and ":" where ")."useyn = 'Y'"; break;
    case 'N' : $sql_search .= ($sql_search ? " and ":" where ")."useyn = 'N'"; break;
}

$sql_search .= ($sql_search ? " and ":" where ")."s_id = '".sql_safe_query($_GET['s_id'])."'";


# 데이터 로드 #
$sql = sql_query("
    select
        *
    from
        yc4_station_main_best_item
     ".$sql_search."
");

$list_tr = '';
while($row = sql_fetch_array($sql)){
    $useyn = '';
    switch($row['useyn']){
        case 'Y' : $useyn = "O"; break;
        case 'N' : $useyn = "X"; break;
    }
    $list_tr .= "
        <tr>
            <td>".$st_arr[$row['s_id']]."</td>
            <td>".$row['it_id']."</td>
            <td>".get_it_image($row['it_id'].'_s',80,80,null,null,null,null,false)."</td>
            <td>".$row['it_name']."</td>
            <td>".$useyn."</td>
            <td>".date('Y.m.d',strtotime($row['create_dt']))."</td>
            <td>".icon('수정',$g4['shop_admin_path'].'/main_station_best_item_write.php?uid='.$row['uid'])."</td>
        </tr>
    ";
}




include_once $g4['admin_path']."/admin.head.php";
?>

    <style>
        .tab_wrap{
            overflow: hidden;
        }
        .tab{
            overflow: hidden;
            list-style: none;
            float: left;
        }
        .tab>li{
            float:left;
            padding:5px;
            border:1px solid #dddddd;
        }
        .tab>li.active{
            font-weight: bold;
        }
    </style>

<div class="tab_wrap">
    <ul class="tab">
        <li <?php echo $_GET['fg'] == 'Y' ? "class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=Y">진행</a></li>
        <li <?php echo $_GET['fg'] == 'N' ? "class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=N">종료</a></li>
    </ul>
    <ul class="tab" style="float: right;">
        <?php echo $st_li;?>
    </ul>
</div>
<table width="100%">
    <thead>
        <tr>
            <td>제품관</td>
            <td>상품코드</td>
            <td></td>
            <td>상품명</td>
            <td>사용여부</td>
            <td>등록일</td>
            <td><?php echo icon('입력',$g4['shop_admin_path'].'/main_station_best_item_write.php?');?></td>
        </tr>
    </thead>
</table>

<?php
include_once $g4['admin_path']."/admin.tail.php";
?>
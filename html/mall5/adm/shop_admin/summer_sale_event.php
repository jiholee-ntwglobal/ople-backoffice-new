<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-06-15
 * Time: 오후 6:21
 */


$sub_menu = "500702";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4['title'] = "썸머 빅 세일 이벤트";
include_once $g4['admin_path']."/admin.head.php";

$st_sql = sql_query("select s_id,name from yc4_station where s_id<6 order by sort");
$st_arr = array();
$st_tab = "<li ".(!$_GET['s_id']? "class='active'":"")."><a href='".$_SERVER['PHP_SELF']."'>전체</a></li>".PHP_EOL;
while($row = sql_fetch_array($st_sql)){
    $st_arr[$row['s_id']] = $row['name'];
    $st_tab .= "<li ".($_GET['s_id'] == $row['s_id'] ? "class='active'":"")."><a href='".$_SERVER['PHP_SELF']."?s_id=".$row['s_id']."'>".$row['name']."</a></li>".PHP_EOL;
}


$sql_search = "";

if($_GET['s_id']){
    $sql_search .= " and a.value2 = '".$_GET['s_id']."'";
}

$sql = sql_query("
    select
     b.it_maker,b.it_name,b.it_amount,
     a.uid,
     a.value1 as it_id,
     a.value2 as s_id,
     a.value3 as qty,
     a.value4 as amount,
     a.value5 as msrp,
     a.value6 as en_dt,
     a.value7 as order_qty
    from
      yc4_event_data a
      left JOIN
      yc4_item b on b.it_id = a.value1
    WHERE
      a.ev_code = 'summer_big'
      AND
      a.ev_data_type = 'item_list'
      ".$sql_search."
");
$list_tr = "";
while($row = sql_fetch_array($sql)){
    $list_tr .= "
        <tr>
            <td>".$st_arr[$row['s_id']]."</td>
            <td>".$row['it_id']."</td>
            <td>".$row['it_name']."</td>
            <td>".$row['qty']."</td>
            <td>".number_format($row['amount'])." / ".number_format($row['it_amount'])." / ".number_format($row['msrp'],2)."</td>
            <td>".number_format($row['order_qty'])."</td>
            <td>".$row['en_dt']."</td>
            <td>".icon('수정',$g4['shop_admin_path'].'/summer_sale_event_write.php?uid='.$row['uid'])."&nbsp;".icon('삭제',$g4['shop_admin_path'].'/summer_sale_event_write.php?mode=delete&uid='.$row['uid'])."</td>
        </tr>
    ";

}

?>
<style>
    ul.list_tab{
        list-style: none;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }
    ul.list_tab > li {
        float: left;
        padding: 5px;
        border: 1px solid #DDDDDD;
    }
    ul.list_tab > li.active{
        font-weight: bold;
    }
</style>
<ul class="list_tab">
    <?php echo $st_tab;?>
</ul>

<table width='100%' border="1" style="border-collapse: collapse;">
    <col width="85"/>
    <col width="80"/>
    <col width=""/>
    <col width="50"/>
    <col width="150"/>
    <col width="50"/>
    <col width="100"/>
    <col width="50"/>
    <tr>
        <td>관</td>
        <td>상품코드</td>
        <td>제품명</td>
        <td>수량</td>
        <td>가격<br/>(이벤트가 / 원래오플가 / MSRP)</td>
        <td>판매수량</td>
        <td>종료시간</td>
        <td><?php echo icon('입력',$g4['shop_admin_path'].'/summer_sale_event_write.php');?></td>
    </tr>
    <?php echo $list_tr;?>
</table>

<?php
include_once $g4['admin_path']."/admin.tail.php";
?>

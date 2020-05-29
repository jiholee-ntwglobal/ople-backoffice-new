<?php
/**
 * Created by PhpStorm.
 * User: Min
 * Date: 2015-05-18
 * Time: 오후 4:39
 */


$sub_menu = "400431";
include "_common.php";
auth_check($auth[$sub_menu], "w");
$sql_search = '';
if($_GET['search']){
    $sql_search .= " and ".sql_safe_query($_GET['sel_field'])." = '".sql_safe_query($_GET['search'])."'";

}

$order_by = "order by od_id desc";
if($_GET['sort1']){
    $order_by .= ', '.$_GET['sort1']." ".$_GET['sort2'];
}



$sql = sql_fetch(" SELECT count(*) as cnt FROM {$g4['yc4_order_table']} a WHERE od_auto_pay_fg = 'Y' and od_settle_case != '신용카드' and od_temp_bank = 0 {$sql_search} ");
$total_count = $sql['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함



$sql = sql_query("
    SELECT
        a.od_id,a.on_uid,a.od_name,a.mb_id,a.od_dc_amount,
        a.od_time,a.od_pay_time,
      "._MISU_QUERY_."
    FROM
        {$g4['yc4_order_table']} a
        LEFT JOIN
        {$g4['yc4_cart_table']} b ON a.on_uid = b.on_uid
    WHERE
        a.od_auto_pay_fg = 'Y'
        and a.od_settle_case != '신용카드'
        and a.od_temp_bank = 0
        {$sql_search}
    GROUP BY a.od_id
    {$order_by}
    limit {$from_record}, {$rows}
");

$data =  array();

while($row = sql_fetch_array($sql)){
    $it_sql = sql_query("
        SELECT
         a.ct_id,a.ct_amount,a.ct_qty,a.ct_status,a.it_id,a.ct_history,
         b.it_name
        FROM
            {$g4['yc4_cart_table']} a
            left join
            {$g4['yc4_item_table']} b ON a.it_id = b.it_id
        WHERE a.on_uid = '".$row['on_uid']."'
    ");
    while($it_row = sql_fetch_array($it_sql)){
        $data[$row['od_id']]['item'][] = $it_row;
    }

    $data[$row['od_id']] = array_merge($data[$row['od_id']],$row);
}


$qstr = $_GET;
unset($qstr['page']);
$qstr = http_build_query($qstr);

include_once $g4['admin_path']."/admin.head.php";
?>

    <form name=frmorderlist>
        <table width=100% cellpadding=4 cellspacing=0>
            <input type=hidden name=sort1 value="<? echo $sort1 ?>">
            <input type=hidden name=sort2 value="<? echo $sort2 ?>">
            <input type=hidden name=page  value="<? echo $page ?>">
            <tr>
                <td width=20%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
                <td width=60% align=center>
                    <select name=sel_field>
                        <option value='od_id'>주문번호</option>
                        <option value='mb_id'>회원 ID</option>
                        <option value='od_name'>주문자</option>
                        <option value='od_tel'>주문자전화</option>
                        <option value='od_hp'>주문자핸드폰</option>
                        <option value='od_b_name'>받는분</option>
                        <option value='od_b_tel'>받는분전화</option>
                        <option value='od_b_hp'>받는분핸드폰</option>
                    </select>
                    <input type=hidden name=save_search value='<?=$search?>'>
                    <input type=text name=search value='<? echo $search ?>' autocomplete="off">
                    <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
                </td>
                <td width=20% align=right>건수 : <? echo number_format($total_count) ?>&nbsp;</td>
            </tr>
        </table>
    </form>

    <table width=100% cellpadding=4 cellspacing=0>
        <thead>
            <tr align=center class=ht>
                <td><a href='<?=title_sort("od_id", 1)."&$qstr1";?>'>주문번호</a></td>
                <td><a href='<?=title_sort("od_name")."&$qstr1";?>'>주문자</a></td>
                <td><a href='<? echo title_sort("mb_id")."&$qstr1"; ?>'>회원ID</a></td>
                <td><a href='<?=title_sort("itemcount", 1)."&$qstr1";?>'>건수</a></td>
                <td><a href='<?=title_sort("orderamount", 1)."&$qstr1";?>'><FONT COLOR="1275D3">주문합계</a></FONT></td>
                <td><a href='<?=title_sort("ordercancel", 1)."&$qstr1";?>'>주문취소</a></td>
                <td><a href='<?=title_sort("od_dc_amount", 1)."&$qstr1";?>'>DC</a></td>
                <td><a href='<?=title_sort("receiptamount")."&$qstr1";?>'><FONT COLOR="1275D3">입금합계</font></a></td>
                <td><a href='<?=title_sort("receiptcancel", 1)."&$qstr1";?>'>입금취소</a></td>
                <td><a href='<?=title_sort("misu", 1)."&$qstr1";?>'><font color='#FF6600'>미수금</font></a></td>
                <td>주문날짜/처리날짜</td>
                <td>수정</td>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($data as $val) {
            echo "
            <tr class='list0 ht'>
                <td align='center'>".$val['od_id']."</td>
                <td align='center'>".$val['od_name']."</td>
                <td align='center'>".$val['mb_id']."</td>
                <td align='right'>".number_format($val['itemcount'])."</td>
                <td align='right'>".number_format($val['orderamount'])."</td>
                <td align='right'>".number_format($val['ordercancel'])."</td>
                <td align='right'>".number_format($val['od_dc_amount'])."</td>
                <td align='right'>".number_format($val['receiptamount'])."</td>
                <td align='right'>".number_format($val['receiptcancel'])."</td>
                <td align='right'>".number_format($val['misu'])."</td>
                <td align='center'>".$val['od_time']."<br/>".$val['od_pay_time']."</td>
                <td>
                    ".icon('수정',$g4['shop_admin_path'].'/orderform.php?od_id='.$val['od_id'],'_blank')."
                    <br/>
                    <a href='".$g4['admin_path']."/point_list.php?sfl=mb_id&stx=".$val['mb_id']."' target='_blank'>포인트 내역</a>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan='10'>
                    <table width='100%' cellpadding='5'>
                        <col width='57'/>
                        <col width=''/>
                        <col width='50'/>
            ";
            if(is_array($val['item'])){
                foreach ($val['item'] as $item) {

                    echo "
                        <tr>
                            <td>".get_it_image($item['it_id'].'_s',50,50)."</td>
                            <td>".get_item_name($item['it_name'],'list')."</td>
                            <td><b>".$item['ct_status']."</b></td>
                            <td>
                                단가 : ".number_format($item['ct_amount'])."<br/>
                                수량 : ".number_format($item['ct_qty'])."<br/>
                                소계 : ".number_format($item['ct_amount'] * $item['ct_qty'])."
                            </td>
                            <td>".nl2br($item['ct_history'])."</td>
                        </tr>

            ";
                }
            }
            echo "
                    </table>
                </td>
            </tr>
            <tr><td colspan=12 height=1 bgcolor=#CCCCCC></td></tr>
            ";
        }
        ?>
        </tbody>

        <tr><td colspan=12 height=1 bgcolor=#CCCCCC></td></tr>
    </table>

    <table width=100%>
        <tr>
            <td width=50%>&nbsp;</td>
            <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
        </tr>
    </table>

<?php include_once $g4['admin_path']."/admin.tail.php"; ?>
<?
$sub_menu = "300127";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "상품 MSRP 입력/수정";

include_once ("$g4[admin_path]/admin.head.php");

$item_arr = array();
$msrp_arr = array();
$msrp_ihub_arr = array();

$sst  = ($_REQUEST['sst']) ? : "i.it_id";
$sod = ($_REQUEST['sod']) ? : "desc";

$where = ($stx !="" && $sfl != "") ? " AND i.$sfl like '%$stx%'" : "";
$page = ($save_stx != $stx && $_GET['page']=="") ? 1 : $_GET['page'];

/**페이징**/
$item_total_cnt = sql_fetch_array(sql_query("
                                            SELECT count(i.it_id) as cnt 
                                            FROM yc4_item i 
                                            LEFT OUTER JOIN yc4_item_etc_amount e ON (i.it_id = e.it_id AND e.pay_code = '3')  
                                            WHERE i.it_use = 1 AND i.it_discontinued = 0 $where"));

$total_count = $item_total_cnt['cnt'];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$qstr  = "$qstr&sca=$sca&page=$page&save_stx=$stx";

/**페이징 끝**/

//상품 리스트
$item_que = sql_query("
                SELECT 
                  i.it_id, i.it_name, i.it_maker,i.it_use,i.it_stock_qty,i.it_amount,i.it_amount_usd, i.it_cust_amount, i.it_cust_amount_usd,
                  e.amount
                FROM yc4_item i
                LEFT OUTER JOIN yc4_item_etc_amount e ON (i.it_id = e.it_id AND e.pay_code = '3') 
                WHERE i.it_use = 1 AND i.it_discontinued = 0
                $where
                ORDER BY $sst $sod
                LIMIT $from_record, $rows        
                "
);

while ($row = sql_fetch_array($item_que)) {
    $item_arr[] = $row;
    $it_id[] = $row['it_id'];
}
if(count($it_id)>0) {
    $search_itid = implode(",", $it_id);

    //아이허브 MSRP
    $msrp_ihub_que = sql_query("SELECT it_id, iherb_msrp FROM ople_mapping 
                        WHERE it_id in ($search_itid)");

    while ($orw = sql_fetch_array($msrp_ihub_que)) {
        $msrp_ihub_arr[$row['it_id']] = $row;
    }

}

?>
<table width=100% cellpadding=0 cellspacing=0>
    <tr>
        <td colspan=12 height=2>
            <h2>상품 MSRP 입력/수정&nbsp;</h2>
            <br>
        </td>
    </tr>
    <tr>
        <td colspan=12 height=5></td>
    </tr>
    <tr>
        <td colspan=12 height=1 bgcolor=#CCCCCC></td>
    </tr>
</table>
<form name=flist>
    <table width=100% cellpadding=4 cellspacing=0>
        <input type=hidden name=page value="<?php echo $page?>">
        <tr>
            <td width=20%><a href='<?php echo $_SERVER[PHP_SELF]?>'>처음</a></td>
            <td width=60% align=center>
                <select name=sfl>
                    <option value='it_name' <?php echo $_GET['sfl'] == 'it_name' ? "selected":""?>>상품명</option>
                    <option value='it_id' <?php echo $_GET['sfl'] == 'it_id' ? "selected":""?>>상품코드</option>
                    <option value='SKU' <?php echo $sfl == 'SKU' ? "selected":"";?>>SKU</option>
                    <option value='it_maker' <?php echo $_GET['sfl'] == 'it_maker' ? "selected":""?>>제조사</option>
                </select>
                <input type=hidden name=save_stx value='<?php echo $stx?>'>
                <input type=text name=stx value='<?php echo $stx?>'>
                <input type=image src='<?php echo $g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
            </td>
            <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;&nbsp;&nbsp;<?php echo $excel_button;?></td>
        </tr>
        <tr>
            <td colspan=12 height=1 bgcolor=#CCCCCC></td>
        </tr>
    </table>
    <table cellpadding=0 cellspacing=0 width=100% border=0>
        <tr><td colspan=13 height=2 bgcolor=0E87F9></td></tr>
        <tr align=center class=ht>
            <td width=80><?php echo subject_sort_link("it_id", "sca=$sca")?>상품코드</a></td>
            <td width=''><?php echo subject_sort_link("it_name", "sca=$sca")?>상품명</a></td>
            <td width="120">MSRP(달러가)</td>
            <td width="80">아이허브 MSRP(달러가)</td>
            <td width=30><?php echo subject_sort_link("it_stock_qty", "sca=$sca")?>재고</a></td>
            <td width=30><?php echo subject_sort_link("it_use", "sca=$sca", 1)?>판매</a></td>
        </tr>
        <tr><td colspan=13 height=1 bgcolor=#CCCCCC></td></tr>
</form>
<?php
    $i=1;
    foreach ($item_arr as $item){
    $msrp_amount = '';
    $msrp_amount = $item['amount'];

    $msrp_ihub_amount = '';
    $msrp_ihub_amount = ($msrp_ihub_arr[$item['it_id']]['iherb_msrp']) ? "$". $msrp_ihub_arr[$item['it_id']]['iherb_msrp'] : "";
    ?>
    <tr class="list<?php echo $i%2?>" height='60'>
        <td><?php echo $item['it_id']?></td>
        <td><?php echo get_item_name($item['it_name'],"list")?></td>
        <td width="120">
            $<input type="text" name="msrp" id="msrp_<?php echo $item['it_id']?>" value="<?php echo $msrp_amount; ?>" style="width:60px">
            <input type="button" onclick="msrpUpdate('<?php echo $item["it_id"]?>','<?php echo $msrp_amount?>')" value="수정">
        </td>
        <td width="70"><?php echo $msrp_ihub_amount?></td>
        <td align="center"><?php echo $item['it_stock_qty']?></td>
        <td align="center"><?php echo ($item['it_use']==1) ? "판매" : "판매중지"; ?></td>
    </tr>
<?php
    $i++;
    } ?>
<?php if($total_count==0){ ?>
    <tr height="60">
        <td colspan="6" align="center">존재하는 상품이 없습니다.</td>
    </tr>
<?php } ?>
</table>
<table width=100%>
    <tr>
        <?php
        unset($_GET['page']);
        $qstr= http_build_query($_GET);
        ?>
        <td width=50% align=right><?php echo get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
    </tr>
</table>

<?  include_once ("$g4[admin_path]/admin.tail.php"); ?>

<script>
    function msrpUpdate(it_id, amount) {
        if(confirm("해당 상품의 MSRP를 변경하시겠습니까?")){
            var msrp_before = amount;
            var msrp_after = $("#msrp_"+it_id).val();
            if(msrp_after == msrp_before){
                alert("가격이 변경전과 같습니다.")
                return false;
            }
            $.ajax({
                type : "POST",
                url : "<?php echo $g4[admin_path]?>/shop_admin/product_msrp_update.php",
                dataType : 'json',
                data : "it_id="+it_id+"&amount="+msrp_after,
                success : function (val) {
                    alert(val.msg);
                    location.reload();
                },
                error : function (xhr, status, error){
                    alert("잠시 통신이 원활하지 않습니다. 잠시후 다시 시도해주세요.")
                }
            })

        }
    }
</script>
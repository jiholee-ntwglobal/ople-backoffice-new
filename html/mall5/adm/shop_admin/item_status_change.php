<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-10-10
 * Time: 오후 4:16
 */
$sub_menu = "300125";
include_once "./_common.php";
include_once $g4['full_path'] . '/lib/db.php';
auth_check($auth[$sub_menu], "r");



$searchs =array();
$where_in = '';
if (trim($_GET['it_id']) != '') {
    $searchs = explode(PHP_EOL, trim($_GET['it_id']));
    array_walk($searchs, function (&$item) {
        if (is_string($item)) {
            $item = sql_safe_query(trim($item));
        }
    });
}
if(count($searchs) > 200){
    alert('최대 200개 까지 조회가 가능합니다');
}

if(count($searchs) > 0 && count($searchs) < 201){
    $where_in = implode("','",$searchs);
}

if(trim($where_in)!='') {

    $result = sql_query("
    select it_id, 
           it_stock_qty, 
           it_use, 
           it_maker, 
           it_name, 
           it_discontinued  
    from yc4_item 
    where it_id in ('".$where_in."')
            ");

    $it_id_data = array();
    while ($row = sql_fetch_array($result)) {

        array_push($it_id_data, $row);

    }
}

$g4[title] = "다중세트상품 단품가";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>상품 일괄 상태 변경</h4>
    </div>
</div>
<div class="row">
    <form class="text-left" >
        <div class="col-lg-4">
            <label>IT_ID</label>
            <div class="form-group">
                <textarea name="it_id" rows="5" class="form-control" placeholder="최대 100개 (엔터로 구분)"><?php echo htmlspecialchars($_GET['it_id']);?></textarea>
            </div>
        </div>
        <div class="col-lg-3">
            <br>
            <button class="btn btn-primary btn-block" type="submit">조회</button><br>
            <button class="btn btn-primary btn-block" onclick="location.href='<?php echo $_SERVER['PHP_SELF'];?>'" type="button">검색조건 초기화</button>
        </div>
    </form>
    <div class="col-lg-5">
    </div>
</div>

<?php if (!empty($it_id_data)) { ?>
    <form method="post" onsubmit="return data_chks()" action="./item_status_change_save.php">
<div class="row">
    <div class="col-lg-7">
    </div>
    <div class="col-lg-3">
        <select class="form-control" name="status_update">
            <option value="discontinued">일괄 단종처리</option>
            <option value="received">일괄 단종해제 처리</option>
            <option value="salestop">일괄 판매중지 처리</option>
            <option value="sale">일괄 판매중 처리</option>
            <option value="soldout">일괄 품절처리</option>
            <option value="instock">일괄 품절해제처리</option>
        </select>
    </div>
    <div class="col-lg-2">
        <button class="btn btn-primary btn-block" type="submit">일괄처리</button>
    </div>
</div>

<table class="table">

    <thead>
    <tr STYLE="font-size: 10px;">
        <th><input type="checkbox" id="chk_all" onclick="chk_all_item()"></th>
        <th>IT_ID</th>
        <th>브랜드명</th>
        <th>상품명</th>
        <th>노출유무</th>
        <th>단종여부</th>
        <th>품절여부</th>
    </tr>
    </thead>



    <tbody>
    <?php foreach ($it_id_data as $it_id_row){ ?>
        <tr>
            <td><input class="it_id_checkbox" type="checkbox" value="<?php echo $it_id_row['it_id'];?>" name="it_ids[]"></td>
            <td><?php echo $it_id_row['it_id'];?></td>
            <td width="17%"><?php echo $it_id_row['it_maker'];?></td>
            <td width="40%"><?php echo $it_id_row['it_name'];?></td>
            <td><?php echo $it_id_row['it_use']=='1'?"노출":'비노출';?></td>
            <td><?php echo $it_id_row['it_discontinued']=='1'?'단종':'단종아님';?></td>
            <td><?php echo $it_id_row['it_stock_qty']>0?'재고있음':'품절';?></td>
        </tr>
    <?php } ?>
    </tbody>

</table>
        <input type="hidden" name="get_url" value="<?php echo http_build_query($_GET);?>">
        <input type="hidden" name="mode" value="update">
</form>
<? } ?>

<script>

    function chk_all_item() {
        var chk = $('#chk_all').prop('checked');
        $('input:checkbox[name*=it_ids]').prop("checked",chk);
    }
    function data_chks() {
        var cnt = $('input:checkbox[name*=it_ids]:checked').length;
        if(cnt <1){
            alert('1개이상 체크해주세요');
            return false;
        }

        if(confirm(cnt+"개 상품을 일괄 처리 하시겠습니까?")){
            return true;
        }else{
            return false;
        }
    }
</script>

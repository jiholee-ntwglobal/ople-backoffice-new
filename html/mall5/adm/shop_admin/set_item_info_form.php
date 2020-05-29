<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-01
 * Time: 오후 4:51
 */
$sub_menu = "300567";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$list_set = array();
$int = 1;
$set_data = array();

if($_GET['mode'] == 'insert') {

    $_GET['it_id_search'] = isset($_GET['it_id_search']) && trim($_GET['it_id_search']) != '' ? trim($_GET['it_id_search']) : '';

    if ($_GET['it_id_search'] != '') {

        $set_result = sql_query("
            SELECT a.it_id,
                   it_name,
                   b.upc,
                   b.qty
            FROM yc4_item    a
                 INNER JOIN ople_mapping b ON a.it_id = b.it_id AND ople_type = 's'
                 left outer join yc4_item_set c on a.it_id =c.it_id
            WHERE a.it_id = '" . sql_safe_query(trim($_GET['it_id_search'])) . "'
            and c.it_id is null
                ");

        while ($row_set = sql_fetch_array($set_result)) {

            if ($int == 1) {

                $set_data['it_name'] = $row_set['it_name'];
                $set_data['it_id'] = $row_set['it_id'];

            }

            $child_it_id_data = sql_fetch("
                    SELECT a.it_id AS child_it_id
                    FROM ople_mapping    a
                         INNER JOIN yc4_item b ON a.it_id = b.it_id AND ople_type = 'm'
                    WHERE upc = '" . trim($row_set['upc']) . "'
                    ORDER BY it_use DESC
                    LIMIT 1
                        ");

            $list_set[$int]['qty']= $row_set['qty'];

            $list_set[$int]['child_it_id']=$child_it_id_data['child_it_id'];

            $int++;

        }

        if (empty($list_set)) {

            alert('세트상품이 아니거나 등록된 세트상품입니다');
        }

    }
}elseif ($_GET['mode'] == 'update'){

    if(!$_GET['it_id'] || trim($_GET['it_id'])==''){

        alert('잘못된 접근입니다.','./set_item_info_list.php');

    }

    $set_result = sql_query("
            SELECT a.it_id,
                   b.it_name,
                   a.child_it_id,
                   a.child_qty AS qty
            FROM yc4_item_set a INNER JOIN yc4_item b ON b.it_id = a.it_id
            WHERE a.it_id = '" . sql_safe_query(trim($_GET['it_id'])) . "'
                ");



    while ($row_set = sql_fetch_array($set_result)) {

        if ($int == 1) {

            $set_data['it_name'] = $row_set['it_name'];
            $set_data['it_id'] = $row_set['it_id'];

        }
        $list_set[$int]['qty']= $row_set['qty'];

        $list_set[$int]['child_it_id']=$row_set['child_it_id'];

        $int++;

    }

}else{
    alert('잘못된 접근입니다.','./set_item_info_list.php');
}
$g4[title] = "건강정보 관리자";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div>
    <h4><a href="./set_item_info_list.php">오플 세트상품 리스트</a> -> 상세설명 <?php echo $_GET['mode'] == 'insert'?'생성' : '수정';?> </h4>
</div>
<?php if($_GET['mode'] == 'insert') {?>
<form class="form-inline text-cente form " method="get">
    <div class="text-right">

        <span style="color: red">※세트상품코드를 검색해주세요</span>
        <div class="form-group">
            <label>오플 세트상품 코드</label>
            <input type="text" class="form-control" name="it_id_search" value="<?php echo htmlspecialchars(trim($_GET['it_id_search'])); ?>">
            <input type="hidden" name="mode" value="insert">
        </div>
        <button type="submit" class="btn btn-default">검색</button>
    </div>
</form>
<?php }?>
<div class="row">
    <div class="col-lg-12">
        <br>
    </div>
</div>
<?php if (!empty($set_data)) { ?>
    <form method="post" action="set_item_info_action.php" onsubmit="return data_chk()">
        <input type="hidden" name="mode" value="<?php echo $_GET['mode'];?>">
        <div class="panel panel-default col-lg-12">
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-2">
                    </div>
                    <div class="col-lg-2">
                        <?php echo get_image($set_data['it_id'] . '_s', '100', '100'); ?>
                    </div>
                    <div class="col-lg-6">
                        <label><?php echo $set_data['it_id']; ?></label>
                        <input type="hidden" value="<?php echo $set_data['it_id']; ?>" name="it_id">
                        <p>
                            <?php echo get_item_name($set_data['it_name'], 'list'); ?>
                        </p>
                    </div>

                </div>
                <div class="row text-center">
                    <strong style="color: red;">※ UPC, 수량 확인후 저장해주시기 바랍니다</strong>
                </div>
                <div class="child_data">
                    <?php
                    $int = 1;
                    foreach ($list_set as $value) {?>
                        <div class="form-group row child_data_div<?php echo $int; ?>">
                            <div class="col-lg-5">
                                <div class="input-group">
                                    <span class="input-group-addon">IT_ID</span>
                                    <input type="text" class="form-control" name="data_item[<?php echo $int; ?>]" value="<?php echo $value['child_it_id']; ?>">
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="input-group">
                                    <span class="input-group-addon">수량</span>
                                    <input type="number" min="1" class="col-lg-5 form-control" name="child_it_id_qty[<?php echo $int; ?>]" value="<?php echo $value['qty']; ?>">
                                </div>
                            </div>

                            <button class="btn btn-danger col-lg-2" type="button" onclick="remove_child_it_id('<?php echo $int++; ?>');">제거
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="from-group col-lg-12 text-center">
                        <button class="btn btn-info" type="button" onclick="add_child_it_id();">상품추가</button>
                        <button class="btn btn-primary" type="submit">저장</button>
                        <button class="btn btn-default" type="button" onclick="location.href='./set_item_info_list.php'">목록</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php } ?>

<script>
    child_cnt = <?php echo $int ? $int : 1;?>;
    function remove_child_it_id(id) {
        $('.child_data_div' + id).remove();
    }
    function add_child_it_id() {
        $('.child_data').append(''+
            "<div class=\"form-group row child_data_div"+child_cnt+"\">"+
                "<div class=\"col-lg-5\">"+
                    "<div class=\"input-group\">"+
                        "<span class=\"input-group-addon\">IT_ID</span>"+
                        "<input type=\"text\" class=\"form-control\" name=\"data_item["+child_cnt+"]\" value=\"\">"+
                    "</div>"+
                "</div>"+
                "<div class=\"col-lg-5\">"+
                    "<div class=\"input-group\">"+
                        "<span class=\"input-group-addon\">수량</span>"+
                        "<input type=\"number\" min=\"1\" class=\"col-lg-5 form-control\" name=\"child_it_id_qty["+child_cnt+"]\" value=\"\">"+
                    "</div>"+
                "</div>"+
                "<button class=\"btn btn-danger col-lg-2\" type=\"button\" onclick=\"remove_child_it_id('"+child_cnt++ +"');\">제거</button>"+
            "</div>"+
        '');
    }
    function data_chk() {
        fg = true;
        $('input[name^=child_it_id_qty]').each(function (index) {
            var it_id = $('input[name^=data_item]')[index].value;
            var sort = $('input[name^=child_it_id_qty]')[index].value;

            if(it_id  == '' || sort == '' ){
                fg = false;
                alert('모두 입력해주시기 바랍니다');
                it_id == '' ? $('input[name^=data_item]')[index].focus() : $('input[name^=child_it_id_qty]')[index].focus();
                return false;
            }
            if(!$.isNumeric(sort) || sort < 1){
                alert('수량은 숫자만 입력 가능합니다 ');
                $('input[name^=child_it_id_qty]')[index].focus();
                fg = false;
                return false;
            }
        });
        if(fg===false){
            return false;
        }
        if($('input[name=mode]').val().trim() == 'update'){
            if(confirm('수정하시겠습니까')){
                return true;
            }
            return false;
        }

        return true;

    }
</script>

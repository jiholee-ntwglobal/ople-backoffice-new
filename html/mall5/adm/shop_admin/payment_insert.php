<?php
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2016-12-01
 * Time: 오후 5:01
 */
$sub_menu = "300170";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");
$escape=array("'",'"','\\');
$od_id_arr = array();
$od_id = isset($_POST['od_id']) ? trim($_POST['od_id']) : "";
if ($od_id) {
    $od_id_arr = explode(PHP_EOL, trim($od_id));
    array_walk($od_id_arr, function (&$item) {
        if (is_string($item)) {
            $item = trim($item);
        }
    });
}

if ($od_id_arr) {
    $od_id_in = '';
    foreach ($od_id_arr as $value_od_id) {
        if (!trim($value_od_id)) {
            continue;
        }
        $value_od_id = str_replace($escape,'',$value_od_id);
        $od_id_in .= ($od_id_in ? "," : "") . "'" . trim($value_od_id) . "'";


    }
    $yc4_order_arr = array();
    $sql = " select
                        mb_id,
                        od_id,
                        od_status_update_dt,
                        od_name
                from yc4_order
                where od_id in ({$od_id_in}) ";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $yc4_order_arr[] = $row;
    }

}
//ar_dump($yc4_order_arr);
define('bootstrap', true);
$g4[title] = "사은품 추가";
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form class="form" action="payment_insert.php" method="post">
    <div class="row">
        <div class="col-lg-2">

        </div>
        <div class="col-lg-4">
            <textarea placeholder="오플주문번호 엔터로구분" name="od_id" class="form-control"
                      rows="4"><?php echo $od_id; ?></textarea>
        </div>
        <div class="col-lg-4">
            <span><strong style="color: red;">※ 배송으로 넘어간 주문서는 사은품이 추가는되나 배송이 되지 않습니다.</strong></span>
            <br>
            <br>
            <br>
            <button class="btn btn-primary btn-block" type="submit">검색 및 추가</button>
        </div>
        <div class="col-lg-2">
        </div>
    </div>
</form>
<div class="row">
    <div class="col-lg-12">
        <br>
    </div>
</div>
<?php if (!empty($yc4_order_arr)) { ?>
    <form class="" action="./payment_insert_insert.php" onsubmit="return frm_submit();" method="post" id="frm">
        <div class="col-lg-7">
            <table class="table">
                <thead>
                <tr>
                    <th class="text-center" colspan="5">주문서</th>
                </tr>
                <tr>
                    <th>주문번호</th>
                    <th>이름</th>
                    <th>아이디</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($yc4_order_arr as $value) {
                    ?>
                    <tr class="odvalue">
                        <td>
                            <span><strong><?php echo trim($value['od_id']); ?></strong></span>
                            <input type="hidden" class="form-control mapping_input upc"
                                   name="od[<?php echo trim($value['od_id']); ?>]" readonly
                                   value="<?php echo trim($value['od_id']); ?>">
                        </td>
                        <td>
                            <span><strong><?php echo trim($value['od_name']); ?></strong></span>
                            <input type="hidden" class="form-control mapping_input qty"
                                   name="names[<?php echo trim($value['od_id']); ?>]" readonly
                                   value="<?php echo trim($value['od_name']); ?>">
                        </td>
                        <td>
                            <span><strong><?php echo trim($value['mb_id']) ? trim($value['mb_id']) : '비회원'; ?></strong></span>
                            <input type="hidden" class="form-control mapping_input qty"
                                   name="id[<?php echo trim($value['od_id']); ?>]" readonly
                                   value="<?php echo trim($value['mb_id']) ? trim($value['mb_id']) : '비회원'; ?>">
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="col-lg-5">
            <table class="table">
                <thead>
                <tr>
                    <th class="text-center" colspan="3">사은품검색</th>
                </tr>
                </thead>
            </table>
            <div class="row">
                <div class="col-lg-10">
                    <div class="input-group">
                        <span class="input-group-addon">사은품코드</span>
                        <input type="number" class="form-control mapping_input qty"
                               id="payment_it_id"
                               value="">
                        <input type="hidden" class="form-control mapping_input qty"
                               name="payment_it_id_hidden"
                               value="">
                    </div>
                    <!-- <button type="button" class="btn btn-danger btn-block" onclick="del_row(this);">-</button>-->
                </div>
                <div class="col-lg-2">
                    <div class="input-group">
                        <button type="button" class="btn btn-danger btn-block"
                                onclick="ajax_row();">검색
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <br>
                </div>
            </div>
            <div class="gifts_search" style="display: none;">
                <div class="row">
                    <div class="col-lg-12">
                        <!--<input type="text" class="form-control"
                               id="payment_it_name" readonly value="">-->
                        <div id="payment_it_name"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <label class="">수량</label>
                        <input type="number" class="form-control"
                               name="payment_qty" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <label class="">메모</label>
                        <input type="text" class="form-control"
                               name="payment_meno" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <br>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <button class="btn btn-success btn-block" type="submit">주문서 사은품 추가</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <br>
                    <span><strong style="color: red;">※ 사은품은 건기식 병수를 체크하지 않습니다. 건기식인 사은품을 추가해서 6병이 초과되면 문제가 발생할수 있습니다. 이
                            점 유의하시기 바랍니다.</strong></span>
                </div>
            </div>
        </div>
    </form>
<? } ?>

<script>
    function ajax_row() {
        var it_id = $.trim($('#payment_it_id').val());

        if (!it_id) {
            alert('사은품코드를 입력해주세요');
            $('#payment_it_id_hidden').val('');
            $('#payment_it_id').val('');
            $('#payment_it_name').val('');
            $('#payment_it_id').focus();
            $('.gifts_search').css('display', 'none');
            return;
        }
        $.ajax({
            type: 'get'
            , url: 'payment_insert_ajax.php'
            , data: 'it_id=' + it_id
            , datatype: 'html'
            , success: function (data) {
                if (data != false) {
                    $('input[name =payment_it_id_hidden]').val(it_id);
                    $('#payment_it_name').html(data);
                    $('.gifts_search').css('display', '');

                } else {
                    alert('존재하지 않는 상품입니다.');
                    $('#payment_it_name').val('');
                    $('#payment_it_id').val('');
                    $('input[name =payment_it_id_hidden]').val('');
                    $('#payment_it_id').focus();
                    $('.gifts_search').css('display', 'none');
                }
            }
        });
    }
    function frm_submit() {
        var payment_qty = $.trim($('input[name=payment_qty]').val());
        var payment_meno = $.trim($('input[name=payment_meno]').val());
        var payment_it_id = $.trim($('#payment_it_id').val());
        var payment_it_id_hidden = $.trim($('input[name =payment_it_id_hidden]').val());
        if (!payment_it_id) {
            alert('사은품 코드를 검색해주세요');
            return false;
        }
        if (!payment_qty) {
            alert('사은품 수량을 입력 해주세요 ');
            return false;
        }
        if (!payment_meno) {
            alert('메모를 입력 해주세요 ');
            return false;
        }
        if (payment_qty <= 0) {
            alert('1개 이상으로 수정 해주세요;');
            return false;
        }
        var trcnt = $('.odvalue').length;
        if (confirm('주문서 총 ' + trcnt + ' 건\n' + '사은품 코드 ' + payment_it_id_hidden + '\n수량 ' + payment_qty + '\n메모 ' + payment_meno + '\n사은품을 추가를 하시겠습니까?')) {
            return true;
        }
        return false;
    }
</script>



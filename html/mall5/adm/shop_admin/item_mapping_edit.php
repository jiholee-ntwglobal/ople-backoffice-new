<?php
/**
 * Created by PhpStorm.
 * File name : item_mapping_edit.php.
 * Comment :
 * Date: 2015-12-18
 * User: Minki Hong
 */

$sub_menu = "300110";
include_once "_common.php";
include_once $g4['full_path'] . '/lib/ople_mapping.php';
auth_check($auth[$sub_menu], "r");
$ople_mapping = new ople_mapping();

if (!$_GET['it_id']) {
    alert('잘못된 방법으로 접근하였습니다.');
}

$nfo_mapping_fg = $ople_mapping->nfo_mapping_chk($it_id);

$shipping_mapping_fg = $ople_mapping->shipping_mapping_chk($it_id);

$it = sql_fetch("select it_id,it_maker,it_name,sku,it_stock_qty from yc4_item where it_id = '{$it_id}'");

$g4['title'] = '상품 매핑 수정';
define('bootstrap', true);
include_once $g4['full_path'] . '/adm/admin.head.php';
?>
    <style>
        a.btn {
            color: inherit;
        }
        .noti_wrap{
            display: none;
        }
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<?php if (!$nfo_mapping_fg && !$shipping_mapping_fg) { ?>
    <div class="alert alert-danger" role="alert"><strong>매핑 데이터가 존재하지 않습니다.</strong></div>
<?php } elseif (!$nfo_mapping_fg) { ?>
    <div class="alert alert-danger" role="alert"><strong>NFO 매핑</strong> 데이터가 존재하지 않습니다.</div>
<?php } elseif (!$shipping_mapping_fg) { ?>
    <div class="alert alert-danger" role="alert"><strong>배송 매핑</strong> 데이터가 존재하지 않습니다.</div>
<?php } ?>
    <div class="noti_wrap">
        <div class="alert alert-danger noti_contents" role="alert"></div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-4">
            <a class="thumbnail" href="http://ople.com/mall5/shop/item.php?it_id=<?php echo $it['it_id'];?>" target="_blank">
                <img src="http://ople.com/mall5/data/item/<?php echo $it['it_id'];?>_l1" width="400" alt="...">
            </a>
        </div>
        <div class="col-sm-6 col-md-8">
            <?php
            echo get_item_name($it['it_name'],'detail');
            if($it['it_stock_qty'] < 1){ ?>
                <img src="<?php echo $g4['shop_path'];?>/img/icon_pumjul.gif" alt="">
            <?php }?>

        </div>
    </div>
    <div class="row">
        <?php if ($nfo_mapping_fg || $shipping_mapping_fg) { ?>
            <div class="col-lg-6">
                <div class="panel panel-info">
                    <div class="panel-heading">NFO 매핑정보</div>
                    <div class="panel-body">
                        <?php if ($nfo_mapping_fg) { ?>
                            <ul class="list-group">
                                <?php
                                $nfo_mapping = $ople_mapping->get_ople_mapping_data($it_id);
                                foreach ($nfo_mapping as $nfo_row) { ?>
                                    <li class="list-group-item nfo_mapping">
                                        <span class="badge qty"><?php echo $nfo_row['qty']; ?></span>
                                        <span class="upc"><?php echo trim($nfo_row['upc']); ?></span>
                                    </li>
                                <? } ?>
                            </ul>
                        <?php } else { ?>
                            매핑 데이터가 존재하지 않습니다.
                        <?php } ?>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-warning" type="button" onclick="mapping_copy('nfo');">매핑 정보 복사</button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel panel-info">
                    <div class="panel-heading">배송 매핑정보</div>
                    <div class="panel-body">
                        <?php if ($shipping_mapping_fg) { ?>
                            <ul class="list-group">
                                <?php
                                $shipping_mapping = $ople_mapping->get_shipping_mapping_data2($it_id);
                                foreach ($shipping_mapping as $shipping_row) { ?>
                                    <li class="list-group-item shipped_mapping">
                                        <span class="badge qty"><?php echo $shipping_row['qty']; ?></span>
                                        <span class="upc"><?php echo trim($shipping_row['upc']); ?></span>
                                    </li>
                                <? } ?>
                            </ul>
                        <?php } else { ?>
                            매핑 데이터가 존재하지 않습니다.
                        <?php } ?>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-warning" type="button" onclick="mapping_copy('shipping');">매핑 정보 복사</button>
                    </div>
                </div>
            </div>
        <?php } ?>



        <form class="col-lg-12" action="<?php echo $g4['shop_admin_path']; ?>/item_mapping_update.php" onsubmit="frm_submit(this); return false;" method="post">
            <input type="hidden" name="mode" value="ople_mapping">
            <input type="hidden" name="it_id" value="<?php echo $it_id;?>">
            <div class="panel">
                <ul class="list-group mapping_ul">
                    <?php
                    $mapping_arr = array();
                    if($nfo_mapping_fg || $shipping_mapping_fg){
                        if($nfo_mapping && $shipping_mapping){
                            if(count($nfo_mapping) > count($shipping_mapping)){
                                $mapping_arr = $nfo_mapping;
                            }else{
                                $mapping_arr = $shipping_mapping;
                            }
                        }elseif($nfo_mapping){
                            $mapping_arr = $nfo_mapping;
                        }else{
                            $mapping_arr = $shipping_mapping;
                        }
                        foreach ($mapping_arr as $key => $row) { ?>
                            <li class="list-group-item row" seq="0">
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">UPC</span>
                                        <input type="text" class="form-control mapping_input upc" name="upc[<?php echo $key;?>]"
                                               value="<?php echo trim($row['upc']); ?>">
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div class="input-group">
                                        <span class="input-group-addon">qty</span>
                                        <input type="text" class="form-control mapping_input qty" name="qty[<?php echo $key;?>]" value="<?php echo $row['qty']?>">
                                    </div>
                                </div>
                                <div class="col-lg-1">
                                    <button type="button" class="btn btn-danger btn-block" onclick="del_row(this);">-</button>
                                </div>
                            </li>
                        <?php } ?>

                    <?php }else {?>
                    <li class="list-group-item row" seq="0">
                        <div class="col-lg-6">
                            <div class="input-group">
                                <span class="input-group-addon">UPC</span>
                                <input type="text" class="form-control mapping_input upc" name="upc[0]"
                                       value="<?php echo trim($it['sku']); ?>">
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <span class="input-group-addon">qty</span>
                                <input type="text" class="form-control mapping_input qty" name="qty[0]" value="1">
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <button type="button" class="btn btn-danger btn-block" onclick="del_row(this);">-</button>
                        </div>
                    </li>
                    <?php }?>
                </ul>
                <div class="panel-footer text-center">
                    <button class="btn btn-info" type="button" onclick="add_row()">상품 추가</button>
                    <button class="btn btn-primary" type="submit">저장</button>
                    <a class="btn btn-default" href="item_mapping.php" >목록</a>
                </div>
            </div>

        </form>

    </div>
    <script>

        $(function(){
            mapping_mompare_view();
        });

        function mapping_compare(){
            var nfo_mapping = [];
            var shipped_mapping = [];
            $('.nfo_mapping').each(function(){
                var row = {
                    upc : $(this).find('.upc').text().trim(),
                    qty : $(this).find('.qty').text().trim()
                };
                nfo_mapping.push(row);
            });
            $('.shipped_mapping').each(function(){
                var row = {
                    upc : $(this).find('.upc').text().trim(),
                    qty : $(this).find('.qty').text().trim()
                };
                shipped_mapping.push(row);
            });
            if(nfo_mapping.length != shipped_mapping.length){
                return false;
            }

            var shipping_matching_cnt = 0;
            shipped_mapping.forEach(function(shipping_row){
                nfo_mapping.forEach(function(nfo_row){
                    if(shipping_row.upc == nfo_row.upc && shipping_row.qty == nfo_row.qty ){
                        shipping_matching_cnt++;
                    }
                });
            });

            if(shipping_matching_cnt != nfo_mapping.length || shipping_matching_cnt != shipped_mapping.length){
                return false;
            }
            return true;


        }

        function mapping_mompare_view(){
            if(mapping_compare() === false){
                $('.noti_contents').html('매핑 정보가 일치하지 않습니다.').parent().show();
                return false;
            }else{
                $('.noti_contents').empty().parent().hide();
                return true;
            }
        }

        function frm_submit(f) {

            var mode = f.mode.value;

            if (mode == 'ople_mapping') {


                var data_validate = true;

                if ($('.ople_mapping').length < 0) {
                    alert('매핑할 상품이 존재하지 않습니다.');
                    return false;
                }

                if(mapping_mompare_view() === false){
                    if(!confirm('현재 NFO와 배송 매핑이 일치하지 않습니다.\n입력하신 정보로 수정하시겠습니까?')){
                        return false;
                    }
                }
                $.each($('.mapping_input'),function(){
                    $(this).val($(this).val().trim());
                    if(data_validate === false){
                        return false;
                    }

                    if($(this).hasClass('qty') === true){
                        $(this).val($(this).val().replace(/[^0-9]/g,''));
                        if($(this).val() == ''){
                            data_validate = false;
                            alert('QTY는 숫자만 입력 가능합니다.');
                            $(this).focus();
                            return false;
                        }
                    }
                    if($(this).hasClass('upc') === true){

                        $.ajax({
                            url : '<?php echo $g4['shop_admin_path'];?>/item_mapping_update.php',
                            type : 'post',
                            data : {
                                mode : 'upc_chk',
                                upc : $(this).val()
                            },
                            async : false,
                            success : function(res){
                                if(res != 'ok'){
                                    data_validate = false;
                                    alert('NTICS에 등록된 UPC가 아닙니다.');
                                    $(this).focus();
                                    return false;
                                }
                                return true;
                            }
                        });
                    }

                    if($(this).val().trim() == ''){
                        data_validate = false;
                        alert('데이터를 입력 해 주세요');
                        $(this).focus();
                        return false;
                    }
                });
                if(data_validate === false){
                    return false;
                }
            }
            f.submit();

            return false;

        }
        function add_row() {
            var seq = get_new_seq();
            var html =
                "<li class=\"list-group-item row\" seq=\"" + seq + "\">" +
                "<div class=\"col-lg-6\">" +
                "<div class=\"input-group\">" +
                "<span class=\"input-group-addon\">UPC</span>" +
                "<input type=\"text\" class=\"form-control mapping_input upc\" name=\"upc[" + seq + "]\" value=\"\">" +
                "</div>" +
                "</div>" +
                "<div class=\"col-lg-5\">" +
                "<div class=\"input-group\">" +
                "<span class=\"input-group-addon\">qty</span>" +
                "<input type=\"text\" class=\"form-control mapping_input qty\" name=\"qty[" + seq + "]\" value=\"1\">" +
                "</div>" +
                "</div>" +
                "<div class=\"col-lg-1\">" +
                "<button type=\"button\" class=\"btn btn-danger btn-block\" onclick=\"del_row(this);\">-</button>" +
                "</div>" +
                "</li>";
            $('.mapping_ul').append(html);
            seq_reload();
            return;
        }
        function del_row(obj) {
            $(obj).parent().parent().remove();
            seq_reload();
            return;
        }
        function seq_reload() {
            $('.mapping_ul > li').each(function (seq, li) {
                $(li).attr('seq', seq);
                $(li).find('.mapping_input').each(function (no, input) {
                    var input_nm = $(input).attr('name').replace(/\[.*.\]/g, '[' + seq + ']');
                    $(input).attr('name', input_nm);
                });
            });
            return;
        }
        function get_new_seq() {
            return Number($('.mapping_ul > li').length);
        }

            var mapping = [];
        function mapping_copy(fg){
            var selector = '';
            if(fg == 'nfo'){
                selector = 'nfo_mapping';
            }else{
                selector = 'shipped_mapping';
            }

            var seq = 0;
            var html = '';
            $('.'+selector).each(function(){
                var row = {
                    upc : $(this).find('.upc').text().trim(),
                    qty : $(this).find('.qty').text().trim()
                };
                html += '<li class="list-group-item row" seq="'+seq+'">'+
                    '<div class="col-lg-6">'+
                    '<div class="input-group">'+
                    '<span class="input-group-addon">UPC</span>'+
                    '<input type="text" class="form-control mapping_input upc" name="upc['+seq+']" value="'+$(this).find('.upc').text().trim()+'">'+
                    '</div>'+
                    '</div>'+
                    '<div class="col-lg-5">'+
                    '<div class="input-group">'+
                    '<span class="input-group-addon">qty</span>'+
                    '<input type="text" class="form-control mapping_input qty" name="qty['+seq+']" value="'+$(this).find('.qty').text().trim()+'">'+
                    '</div>'+
                    '</div>'+
                    '<div class="col-lg-1">'+
                    '<button type="button" class="btn btn-danger btn-block" onclick="del_row(this);">-</button>'+
                '</div>'+
                '</li>';
                seq++;
            });
            $('.mapping_ul').html(html);
        }
    </script>

<?php include_once $g4['full_path'] . '/adm/admin.tail.php';

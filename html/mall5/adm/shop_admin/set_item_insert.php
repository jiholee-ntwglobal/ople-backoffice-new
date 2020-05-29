<?php
/**
 * Created by PhpStorm.
 * User: DEV_4
 * Date: 2016-12-01
 * Time: 오후 3:06
 */
$sub_menu = "300999";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "세트상품 생성";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<div class="col-lg-12">
    <div class="panel panel-info">
        <div class="panel-heading"> 세트상품 등록</div>
    </div>
</div>

<div class="col-lg-12">
    <div class="right">
        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#excel_modal">엑셀 업로드</button>
    </div>
    <br/>
    <div class="modal fade" id="excel_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="post" enctype="multipart/form-data" action="./set_item_insert_excel_action.php">
                <input type="hidden" name="mode" value="excel_upload">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">엑셀 업로드</h4>
                </div>
                <div class="modal-body form-horizontal">
                    <div class="row">
                        <label class="control-label col-lg-4">엑셀 파일 업로드</label>
                        <div class="col-lg-8"><input type="file" name="excel_file" class="form-control" required/></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <a href="set_item_excel_insert_sample.xlsx" class="btn btn-info">샘플 엑셀파일 다운로드</a>
                    <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="col-lg-12">
    <form action="./set_item_insert_1.php" onsubmit="frm_submit(this); return false;" method="post">
        <input type="hidden" value="<?php echo $default['de_conv_pay']; ?>" name="to_exchange_rate">
        <input type="hidden" value="ople_mapping" name="mode">
        <div lass="list-group">
            <div class="list-group-item">
                <h5 class="list-group-heading">제품명</h5>
                <div class="list-group-item-text">
                    <div class="form-group">
                        <label for="it_name">상품명</label>
                        <input type="text" class="form-control" name="it_name" value="">
                    </div>
                </div>
            </div>
        </div>
        <div class="list-group">
            <div class="list-group-item">
                <h5 class="list-group-heading">가격정보</h5>
                <div class="list-group-item-text form-inline">
                    <div class="form-group">
                        <label for="qty_k">가격</label>
                        <div class="input-group">
                            <div class="input-group-addon">\</div>
                            <input type="text" class="form-control" name="qty_k" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="qty_u">가격</label>
                        <div class="input-group">
                            <div class="input-group-addon">$</div>
                            <input type="text" class="form-control" name="qty_u" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="list-group">
            <div class="list-group-item">
                <h5 class="list-group-heading">기타</h5>
                <div class="list-group-item-text form-inline">
                    <div class="form-group">
                        <label for="health">건기식병수</label>
                        <input type="text" class="form-control" name="health" value="">
                    </div>
                    <div class="form-group">
                        <label for="onetime_limit_cnt">1회구매 제한수량</label>
                        <input type="text" class="form-control" name="onetime_limit_cnt" value="">
                    </div>
                    <div class="form-group">
                        <label for="it_origin">원산지</label>
                        <input type="text" class="form-control" name="it_origin" value="">
                    </div>
                </div>
                <div class="list-group-item-text form-inline">
                    <div class="form-group">
                        <input type="checkbox" name="clearance">
                        <label for="clearance">목록통관여부</label>

                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="stock_qty" >

                        <label for="stock_qty">품절</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="list-group">
            <div class="list-group-item">
                <h5 class="list-group-heading">UPC 매핑</h5>
                <div class="list-group-item-test">
                    <div class="mapping_ul">
                        <div class="row li">
                            <div class="col-lg-5">
                                <div class="input-group">
                                    <span class="input-group-addon">UPC</span>
                                    <input type="text" class="form-control mapping_input upc" name="upc[0]" value="">
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="input-group">
                                    <span class="input-group-addon">qty</span>
                                    <input type="text" class="form-control mapping_input qty" name="qty[0]" value="">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <!--<button type="button" class="btn btn-danger btn-block" onclick="del_row(this);">-</button>-->
                                <button class="btn btn-info btn-block" type="button" onclick="add_row()">상품 추가</button>
                            </div>

                            <div class="col-lg-12">
                                <br>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="list-group">
            <div class="list-group-item">
                <h5 class="list-group-item-heading text-center">
                    <button class="btn btn-primary" type="submit">저장</button>
                    <a class="btn btn-default" href="./set_item_view.php">목록</a>
                </h5>
            </div>
        </div>
    </form>
</div>
<script>
    $('input[name=qty_u]').change(function(){
        var	ex_rate     = $('input[name=to_exchange_rate]').val();
        var	amount_usd	= $(this).val();
        var	amount_krw	= Math.round(amount_usd*ex_rate);

        $('input[name=qty_k]').val(amount_krw);
        
    });
    function add_row() {
        var seq = get_new_seq();
        var html =
            "<div class=\"row li\" seq=\"" + seq + "\">" +
            "<div class=\"col-lg-5\">" +
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
            "<div class=\"col-lg-2\">" +
            "<button type=\"button\" class=\"btn btn-danger\" onclick=\"del_row(this);\">-</button>" +
            "</div>" +
            "<div class=\"col-lg-12\">" +
            "<br>" +
            "</div>" +
            "</div>";
        $('.mapping_ul').append(html);
        return;
    }
    function get_new_seq() {
        return Number($('.mapping_ul > .li').length);
    }
    function del_row(obj) {
        $(obj).parent().parent().remove();
        return;
    }
    function frm_submit(f) {
        var mode = f.mode.value;
        if (mode == 'ople_mapping') {
            var it_name = $('input[name= it_name]').val().trim();
            var qty_k = $('input[name= qty_k]').val().trim();
            var qty_u = $('input[name= qty_u]').val().trim();
            var health = $('input[name= health]').val().trim();
            var onetime_limit_cnt = $('input[name= onetime_limit_cnt]').val().trim();
            var it_origin = $('input[name= it_origin]').val().trim();

            if(!it_name){
                alert('상품명을 입력해주세요');
                $('input[name= it_name]').focus();
                return false;
            }
            if(!qty_k){
                alert('가격을 입력해주세요');
                $('input[name= qty_k]').focus();
                return false;
            }
            if(!qty_u){
                alert('가격을 입력해주세요');
                $('input[name= qty_u]').focus();
                return false;
            }

            if(!health){
                alert('건기식 병수를 입력해주세요');
                $('input[name= health]').focus();
                return false;
            }
            if ($('.ople_mapping').length < 0) {
                alert('매핑할 상품이 존재하지 않습니다.');
                return false;
            }
            var data_validate = true;
            $.each($('.mapping_input'),function(){

                $(this).val($(this).val().trim());

                if(data_validate === false){
                    return false;
                }

                if($(this).val().trim() == ''){
                    data_validate = false;
                    alert('UPC매핑란을 입력 해 주세요');
                    $(this).focus();
                    return false;
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
                            console.log(res);
                            if(res != 'ok'){
                                data_validate = false;
                                alert('NTICS에 등록된 UPC가 아닙니다.');
                                $(this).focus();
                                return false;
                            }

                        }
                    });
                }


                if($(this).hasClass('qty') === true) {
                    $(this).val($(this).val().replace(/[^0-9]/g, ''));
                    if ($(this).val() == '') {
                        data_validate = false;
                        alert('QTY는 숫자만 입력 가능합니다.');
                        $(this).focus();
                        return false;
                    }
                }
            });
            if(data_validate === false){
                return false;
            }
        }
        f.submit();
       return false;
    }
</script>
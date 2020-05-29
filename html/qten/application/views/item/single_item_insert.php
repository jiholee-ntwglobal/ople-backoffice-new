<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2018-09-18
* Time : 오후 2:23
*/
$option_tpl = '<option value="%s" %s>%s</option>';
?>
<div class="row">
    <div class="col-md-12">
        <h4>수동 등록 상품 등록</h4>
    </div>
</div>

<div class="row">
    <form role="form" id="connect-item-form">
        <div class="panel panel-default">
            <div class="panel-heading">
                상품 등록
            </div>
                <div class="panel-body">
                <div >
                    <div class="form-group">
                        <label for="upc">채널</label>
                        <select class="form-control" name="channel_id">
                            <?php
                            foreach ($channel_arr as $current_channel_id => $channel){
                                echo sprintf($option_tpl, $current_channel_id, null, element('comment', $channel));
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 form-group">
                        <label for="upc">채널상품코드</label>
                        <input type="text" name="channel_item_code" class="form-control">
                    </div>
                </div>
                <div >
                    <div class="form-group">
                        <label for="">VCODE</label>
                        <input type="text" class="form-control" name="virtual_item_id" placeholder="V00017432와 같은 형식으로 입력해주세요."/>
                    </div>
                </div>
                <div >
                    <div class="form-group">
                        <label for="">상품 가격</label>
                        <input type="text" class="form-control" name="upload_price" value=""/>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label for="">판매자 할인</label>
                        <select name="discount_unit" class="form-control" >
                            <option value="N">할인 없음</option>
                            <option value="Rate">비율로 할인</option>
                            <option value="Money">금액으로 할인</option>
                        </select>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label for="">판매자 할인단위</label>
                        <input type="text" class="form-control" name="discount_price" value=""/>
                    </div>
                </div>
            </div>
        </div>
        <div class="row text-right">
            <div class="col-lg-12">
                <div class="col-lg-8" style="text-align:left">
                    <label><font style="color: red">※ 본 기능은 가격조정이 되지 않고, 저희 서버에서만 매핑데이터를 만드는 기능입니다. 가격 조정을 하시려면 단품 목록에서 반드시 조정이 필요합니다.</font></label>
                </div>
                <div class="col-lg-4">
                    <button type="button" onclick="return saveItem(this.form)" class="btn btn-primary">등록</button>
                    <button type="button" onclick="location.href='<?php echo site_url("/item/single_item")?>'" class="btn btn-defalt">단품 상품 목록</button>
                    <button type="button" onclick="return uploadForm()" class="btn btn-primary">수동등록 상품 엑셀 업로드</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="file-form-modal" tabindex="-1" role="dialog" aria-labelledby="file-form-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="file-form-content">
        </div>
    </div>
</div>
<script>
    function uploadForm() {
        var loadUrl	= "<?php echo site_url('/item/single_item/uploadSingleItemFrom'); ?>";
        $("#file-form-content").empty().load(loadUrl);
        $("#file-form-modal").modal("show");
    }
    function saveItem(form) {
        var item_name = $("#item_info").val();


        if(form.channel_item_code.value==""){
            alert("채널상품코드를 입력해주세요.");
            return false;
        }

        if(form.virtual_item_id.value==""){
            alert("VCODE를 입력해주세요.");
            return false;
        }

        if(form.upload_price.value==""){
            alert("상품가격을 입력해주세요.");
            return false;
        }

        if(confirm("상품을 등록하시겠습니까?")!==false){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url('/item/single_item/insertSingleItem')?>",
                data: $("#connect-item-form").serialize(),
                success: function (json) {
                    alert(json.msg);
                    location.reload();
                },
                error: function (xhr, status, error) {
                    alert("잠시 통신이 원활하지 않습니다. 잠시후 다시 시도해주세요.");
                }
            });

        }
    }

</script>

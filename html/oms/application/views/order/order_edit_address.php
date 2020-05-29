<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2017-10-12
 * Time: 오후 6:52
 *
 */
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">주문정보 변경</h4>
</div>
<div class="modal-body">
    <form id="address-edit-form">
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>"/>
        <input type="hidden" name="order_id1" value="<?php echo element('order_id', $order_address); ?>"/>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>수령인명</label>
                <input type="text" name="receiver_name" class="form-control" value="<?php echo element('receiver_name',$order_address); ?>"/>
            </div>
            <div class="col-md-6 form-group">
                <label>핸드폰번호</label>
                <input type="text" name="receiver_tel1" class="form-control" value="<?php echo element('receiver_tel1',$order_address); ?>"/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>우편번호</label>
                <input type="text" name="zipcode" class="form-control" value="<?php echo element('zipcode',$order_address); ?>"/>
            </div>
            <div class="col-md-6 form-group">
                <label>개인통관고유부호</label>
                <input type="text" name="customer_number" class="form-control" value="<?php echo element('customer_number',$order_address); ?>"/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label>기본주소</label>
                <input type="text" name="addr1" class="form-control" value="<?php echo element('addr1',$order_address); ?>"/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label>상세주소</label>
                <input type="text" name="addr2" class="form-control" value="<?php echo element('addr2',$order_address); ?>"/>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    <button type="button" class="btn btn-primary" onclick="saveAddress()">수정</button>
</div>
<script type="text/javascript">
    function saveAddress() {
        if(confirm("입력하신 정보로 수정하시겠습니까?")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url("order/order/editAddress"); ?>",
                data: $("#address-edit-form").serialize(),
                success: function (json) {
                    if (json.result == "ok") {
                        alert(json.msg);
                        $('#edit-modal').modal("hide");
                        location.reload();
                    } else {
                        alert(json.msg);
                    }
                },
                error: function (xhr, status, error) {
                    alert("잠시 통신이 원활하지 않습니다. 잠시후 다시 시도해주세요.");
                }
            });

        }
    }
</script>
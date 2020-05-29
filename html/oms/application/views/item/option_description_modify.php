<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2018-07-27
* Time : 오후 12:11
*/
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <form role="form" id="connect-option-form" onsubmit="return false;">
            <input type="hidden" name="channel_item_code" id="channel_item_code" value="<?php echo $channel_item_code; ?>">
            <!-- Default panel contents -->
            <div class="panel-heading">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>상품명 수정</h4>
            </div>
            <div class="panel-body">
                <div class="btn-group btn-group-justified">
                </div>
                <table class="table">
                    <tr class="info">
                        <th>채널</th>
                        <td><?php echo element('comment',$channel_info,''); ?></td>
                    </tr>
                    <tr class="info">
                        <th>상품코드</th>
                        <td><?php echo $channel_item_code; ?></td>
                    </tr>
                    <tr class="info">
                        <th>상품명</th>
                        <td><input type="text" name="item_name" id="item_name" class="form-control" value="<?php echo $option_item_description?>"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="saveOption()">수정</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    function saveOption() {

        var item_name = $("#item_name").val();

        if(item_name == ""){
            alert("상품명을 입력해주세요.");
            $('#item_name').focus();
            return false;
        }

        if(confirm("입력하신 상품명으로 수정하시겠습니까?")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "item_option_manage/updateOptionDescription",
                data: $("#connect-option-form").serialize(),
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
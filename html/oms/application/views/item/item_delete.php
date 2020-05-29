<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2018-07-27
 * Time : 오후 12:11
 */
?>
<style>
	.table>tbody>tr.info>td, .table>tbody>tr.info>th {
		/*background-color: #fbefef;*/
		background-color: #fff;
	}
</style>
<div class="">
	<div class="tab-content">
        <form role="form" id="connect-option-form" onsubmit="return false;">
            <input type="hidden" name="channel_item_code" value="<?php echo $channel_item_code; ?>">
            <input type="hidden" name="channel_id" value="<?php echo element('channel_id', $channel_info,'');?>">

            <table class="table">
                <tr class="info">
                    <th>채널</th>
                    <td><?php echo element('comment', $channel_info,''); ?></td>
                </tr>
                <tr class="info">
                    <th>상품코드</th>
                    <td><?php echo $channel_item_code; ?></td>
                </tr>
                <tr class="info">
                    <th>상품명</th>
                    <td><?php echo $option_item_description; ?></td>
                </tr>
                <tr class="info">
                    <th>삭제 이유</th>
                    <td>
                        <textarea class="form-control" name="reason_to_delete" style="height:100px;"></textarea>
                    </td>
                </tr>
                </tr>
            </table>

            <div class="text-center">
                <button type="button" class="btn btn-danger btn-item-delete">삭제</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
	</div>
</div>
<script type="text/javascript">
    $(function() {
        $('button.btn-item-delete').on('click', function() {
            if(!$.trim($('textarea[name=reason_to_delete]').val()))
            {
                alert("삭제 이유를 입력하세요.");
                $('textarea[name=reason_to_delete]').focus();
                return false;
            }

			if(confirm("삭제한 상품은 복구가 불가능합니다.\n해당 상품을 정말 삭제하시겠습니까?"))
            {
                var post_data = {
                	channel_item_code: $('form#connect-option-form input[name=channel_item_code]').val(),
                    channel_id: $('form#connect-option-form input[name=channel_id]').val(),
                    reason_to_delete: $('form#connect-option-form textarea[name=reason_to_delete]').val()
            	};

                $.ajax({
                    type: "POST",
                    url: "<?php echo site_url('item/item_option_manage/item_delete')?>",
                    data: post_data,
                    dataType: "json",
                    // processData: false,
                    // contentType: false,
                    success: function (response) {
                        switch (response.result) {
                            case true :
                                alert(response.message);
                                window.location.reload();
                                break;

                            case false :
                                alert(response.message);
                                window.location.reload();
                                break;
                        }
                    },
                    error: function (request, status, error) {
                        if (request && error) {
                            alert(request.status + " " + error + "\n잠시 후 다시 시도하세요.");
                        }
                        else {
                            alert('잠시 후 다시 시도하세요.');
                        }
                    }
                });
            }
        });

        setTimeout(function(){
        	$('textarea[name=reason_to_delete]').focus();
		}, 300);
    });
</script>
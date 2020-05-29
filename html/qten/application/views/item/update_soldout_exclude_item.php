<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-04-10
* Time : 오후 5:39
*/

$current_master_item = element(element('master_item_id', $soldout_exclude_item_info), $master_item_arr, array());
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <form role="form" id="connect-option-form">
                <input type="hidden" name="soldout_exclude_item_id" id="soldout_exclude_item_id" value="<?php echo element('soldout_exclude_item_id', $soldout_exclude_item_info); ?>">
                <!-- Default panel contents -->
                <div class="panel-heading">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4>품절예외상품 수정</h4>
                </div>
                <div class="panel-body">
                    <div class="btn-group btn-group-justified">
                    </div>
                    <table class="table">
                        <tr class="info">
                            <th>계정유형</th>
                            <td><?php echo element('account_type', $soldout_exclude_item_info) == '1' || element('account_type', $soldout_exclude_item_info) == '4'? '해외사업자' : '국내사업자'; ?></td>
                        </tr>
                        <tr class="info">
                            <th>UPC</th>
                            <td><?php echo element('upc', $current_master_item); ?></td>
                        </tr>
                        <tr class="info">
                            <th>예외유형</th>
                            <td>
                                <select name="soldout_fg">
                                    <option value="">예외유형 선택</option>
                                    <option value="1" <?php if(element('account_type', $soldout_exclude_item_info) != '1' && element('account_type', $soldout_exclude_item_info) != '2') echo "selected"; ?>>품절</option>
                                    <option value="2" <?php if(element('account_type', $soldout_exclude_item_info) == '1' ||element('account_type', $soldout_exclude_item_info) == '2') echo "selected"; ?>>판매중</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="info">
                            <th>비고</th>
                            <td><input type="text" name="memo" id="memo" class="form-control" value="<?php echo element('memo', $soldout_exclude_item_info)?>"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="button" onclick="return saveOption()">수정</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">

    function saveOption() {

        if(confirm("수정하시겠습니까?\n정확하게 다시 한번 확인해주세요.")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url("/item/soldout_exclude_item/updateExcludeItem"); ?>",
                data: $("#connect-option-form").serialize(),
                success: function (json) {
                    alert(json.msg);
                    if(json.result!='error1') {
                        location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    alert("잠시 통신이 원활하지 않습니다. 잠시후 다시 시도해주세요.");
                }
            });

        }
    }
</script>

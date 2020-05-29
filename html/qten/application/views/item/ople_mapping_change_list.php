<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-01-22
* Time : 오후 1:50
*/
?>


    <form method="get" action="<?php echo site_url('item/ople_mapping_change');?>">

        <div class="row">
            <h4>오플매핑변경 관리</h4>

            <div class="col-md-2">
                <button type="button" onclick="mappingChangeAction()" class="btn btn-primary">매핑 변경 이력확인</button>
            </div>

            <div class="col-md-6">
            </div>

            <div class="col-md-2 form-group">
                <label>ITID</label>
                <input type="text" name="it_id" class="form-control" value="<?php echo $it_id?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">검색</button>
                <button type="button" onclick="downloadExcel()" class="btn btn-danger">엑셀다운</button>
            </div>

        </div>
    </form>

    <div class="row">
        <form id="list-form">
            <div class="table-responsive">
                <table class="table" style="font-size:10px;">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="all-checkbox"></th>
                        <th>ITID</th>
                        <th>상품타입</th>
                        <th>변경전 UPC</th>
                        <th>변경후 UPC</th>
                        <th>변경 날짜</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list_datas as $list_data){ ?>
                        <tr>
                            <td><input type="checkbox" class="list-checkbox" name="mapping_ids[]" value="<?php echo element('id', $list_data); ?>"></td>
                            <td><?php echo element('it_id', $list_data); ?></td>
                            <td><?php echo (element('Ople_Type',$list_data)=="m")? "단품" : "세트"; ?></td>
                            <td><?php echo element('bf_upcs', $list_data); ?></td>
                            <td><?php echo element('af_upcs', $list_data); ?></td>
                            <td><?php echo element('cdate', $list_data); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    <div class="row"><?php echo $paging_content; ?></div>

<div style="display:none">
    <form id="excel-hidden-form" method="GET">
        <input type="hidden" name="excel_fg" value="Y"/>
        <input type="hidden" name="it_id" value="<?php echo $it_id?>">
    </form>
</div>
<script type="text/javascript">

    $("#all-checkbox").click(function () {
        $(".list-checkbox").prop("checked", ($(this).is(":checked") ? "checked" : false));$(".list-checkbox").trigger("change");
    });

    function downloadExcel() {
        $("#excel-hidden-form").submit();
    }

    function mappingChangeAction() {
        if($(".list-checkbox:checked").length < 1){
            alert("처리대상을 선택하세요.");
            return false;
        } else {

            if(confirm("선택하신 " + $(".list-checkbox:checked").length + " 개의 상품에 대한 매핑 변경 이력확인 처리를 진행하시겠습니까?")){
                $.ajax({
                    type: "POST",
                    async: false,
                    dataType: "json",
                    url: "<?php echo site_url('/item/ople_mapping_change/MappingChangeAction'); ?>",
                    data: $("#list-form").serialize(),
                    success: function (json) {
                        if (json.result == "ok") {
                            alert(json.msg);
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
    }
</script>

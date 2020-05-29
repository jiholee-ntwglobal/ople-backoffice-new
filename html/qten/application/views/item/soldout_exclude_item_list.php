<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-31
 * Time: 오후 6:00
 */?>
<h4>품절 예외상품 리스트</h4>
<div class="row">
    <div class="col-md-4">
        <ul class="nav nav-pills">
            <li <?php if($account_type == '') echo 'class="active"'; ?>><a href="#" onclick="location.href='<?php echo site_url('item/soldout_exclude_item'); ?>'" data-toggle="tab">전체</a></li>
            <li <?php if($account_type == '1') echo 'class="active"'; ?>><a href="#" onclick="location.href='<?php echo site_url('item/soldout_exclude_item'); ?>?account_type=1'" data-toggle="tab">해외사업자</a></li>
            <li <?php if($account_type == '2') echo 'class="active"'; ?>><a href="#" onclick="location.href='<?php echo site_url('item/soldout_exclude_item'); ?>?account_type=2'" data-toggle="tab">국내사업자</a></li>
        </ul>
    </div>

    <div class="col-md-4">

        <button type="button" onclick="cancelOrder()" class="btn btn-danger">선택삭제처리</button>
    </div>

    <div class="col-md-4">
        <button type="button" data-toggle="modal" data-target="#upload-modal" class="btn btn-primary">엑셀업로드</button>
        <button type="button" data-toggle="modal" data-target="#reg-modal" class="btn btn-primary">예외 상품 등록</button>
        <button class="btn btn-success" type="button" onclick="downloadOrderExcel()">엑셀다운로드</button>
    </div>
</div>
<div class="row">
    <form id="list-data-form">
    <div class="table-responsive">
        <table class="table" style="font-size:10px;">
            <thead>
            <tr>
                <th><input type="checkbox" id="all-checkbox"></th>
                <th>계정유형</th>
                <th>UPC</th>
                <th>브랜드</th>
                <th>상품명</th>
                <th>로케이션</th>
                <th>현재고</th>
                <th>판매유형</th>
                <th>비고</th>
                <th>등록일자</th>
                <th>등록자</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($list_datas as $list_data){
                $current_master_item = element(element('master_item_id', $list_data), $master_item_arr, array());
                ?>
                <tr>
                    <td><input type="checkbox" class="list-checkbox" name="item_id_chk[]" value="<?php echo element('soldout_exclude_item_id', $list_data); ?>"></td>
                    <td><?php echo element('account_type', $list_data) == '1' || element('account_type', $list_data) == '4'? '해외사업자' : '국내사업자'; ?></td>
                    <td><?php echo element('upc', $current_master_item); ?></td>
                    <td><?php echo element('mfgname', $current_master_item); ?></td>
                    <td><?php echo element('item_name', $current_master_item); ?></td>
                    <td><?php echo element('location', $current_master_item); ?></td>
                    <td><?php echo element('currentqty', $current_master_item); ?>
                    <TD><?php echo element('account_type', $list_data) == '1' ||element('account_type', $list_data) == '2' ? '판매중' : '품절'; ?></TD>
                    <td><?php echo element('memo', $list_data)?></td>
                    <td><?php echo element('create_date', $list_data); ?></td>
                    <td <?php echo $worker_arr[$list_data['create_worker_id']]['active'] == "N" ? "style='text-decoration:line-through';" : ""?>><?php echo $worker_arr[$list_data['create_worker_id']]['user_name']; ?></td>
                    <td>
                        <button type="button" onclick="updateExcludeItem('<?php echo element('soldout_exclude_item_id', $list_data); ?>')" class="btn btn-success">수정</button>
                        <button type="button" onclick="deleteExcludeItem('<?php echo element('soldout_exclude_item_id', $list_data); ?>')" class="btn btn-danger">삭제</button>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php //echo $paging_content; ?>
    </div>
    </form>
</div>
<div class="modal fade" id="reg-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo site_url('item/soldout_exclude_item/save');?>" onsubmit="return chkUplodForm()">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">품절 예외 상품 등록</h4>
                </div>
                <div class="modal-body">

                    <select name="account_type">
                        <option value="">계정유형 선택</option>
                        <option value="1">해외사업자</option>
                        <option value="2">국내사업자</option>
                    </select>
                    <select name="soldout_fg">
                        <option value="">예외유형 선택</option>
                        <option value="1">품절</option>
                        <option value="2">판매중</option>
                    </select><br>
                    <input type="text" name="upc" placeholder="UPC">
                    <br/>
                    <input type="text" name="memo" placeholder="비고">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">등록</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<form id="delete-exclude-item-form" method="post" action="<?php echo site_url('item/soldout_exclude_item/delete'); ?>">
    <input type="hidden" name="soldout_exclude_item_id" />
</form>

<form id="excel-hidden-form" method="GET" action="<?php echo site_url('/item/soldout_exclude_item'); ?>">
    <input type="hidden" name="account_type" value="<?php echo $account_type ;?>"/>
    <input type="hidden" name="excel" value="Y"/>
</form>

<div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" >
        <div class="modal-content">
            <form method="POST" action="<?php echo site_url('item/soldout_exclude_item/saveExcelItems'); ?>" enctype="multipart/form-data" onsubmit="return chkUplodForm()">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">품절예외 업데이트 엑셀 등록</h4>
                </div>
                <div class="modal-body">
                    <input type="file" name="excel"/>

                    <h6>샘플 파일은 개발팀에 문의하시기 바랍니다.</h6>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-info" type="button" onclick="location.href='http://oms.ntwsec.com/qten/file/sample_soldout_exclude_item.xlsx'">샘플 파일 다운</button>
                    <button class="btn btn-primary">업로드</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="update-item-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" style="width:1500px;">
        <div class="modal-content" id="update-item-layer-content"></div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<script type="text/javascript">
    function chkUplodForm() {
        if($("select[name=account_type]").val() == ''){
            alert("계정 유형 선택하세요.");
            $("select[name=account_type]").focus();
            return false;
        }
        if($("input[name=upc]").val().trim() == ''){
            alert("UPC를 입력하세요.");
            $("input[name=upc]").focus();
            return false;
        }
    }

    function deleteExcludeItem(soldout_exclude_item_id) {
        if(confirm("해당 품절 예외상품을 삭제 처리하시겠습니까?")){
            $(":hidden[name=soldout_exclude_item_id]").val(soldout_exclude_item_id);
            $("#delete-exclude-item-form").submit();
        }

    }

   function  updateExcludeItem(soldout_exclude_item_id){
        $("#update-item-layer-content").empty();
        $("#update-item-layer-content").load("<?php echo site_url('item/soldout_exclude_item/updateExcludeItemFrom'); ?>/" + soldout_exclude_item_id);

        $('#update-item-modal').modal('toggle');

        return false;
    }

    function downloadOrderExcel() {
        $("#excel-hidden-form").submit();
    }

    $("#all-checkbox").click(function () {
        $(".list-checkbox").prop("checked", ($(this).is(":checked") ? "checked" : false));$(".list-checkbox").trigger("change");
    });


    function cancelOrder() {

        if($(".list-checkbox:checked").length < 1){
            alert("삭제처리할 상품을 선택하세요.");
            return false;
        }

        if(confirm("선택하신 " + $(".list-checkbox:checked").length + "개의 상품을 삭제처리 하시겠습니까?")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url("item/soldout_exclude_item/deleteItems"); ?>",
                data: $("#list-data-form").serialize(),
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

    function chkUplodForm() {
        if($("input[name=upload_file]").val() == ""){
            alert("업로드하실 파일을 선택하세요.");
            return false;
        }
        return true;
    }
</script>
<?php

    $current_virtual_item_info = array();

    $current_virtual_id = element('virtual_item_id', $order_item_info) > 0 ? element('virtual_item_id', $order_item_info) : element('add_virtual_item_id', $order_item_info);


    if(element('virtual_item_id', $order_item_info) > 0){
        $current_virtual_item_info = element(element('virtual_item_id', $order_item_info), $virtual_item_info, array());
    }


?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">상품 매핑정보</h4>
</div>

<div class="modal-body">
    <div class="alert alert-warning">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" style="font-size:12px;">
                <thead>
                <tr>
                    <th>채널 상품번호</th>
                    <th>상품명</th>
                    <th>옵션명</th>
                    <th>비고</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo $order_item_info['channel_product_no']?></td>
                    <td>
                        <a href="<?php echo site_url('item/channel_item/openChannelUrl/' . element('channel_id', $order_info). '/' . element('channel_product_no', $order_item_info)); ?>" target="_blank">
                            <b><?php echo $order_item_info['product_name']?></b></a>
                    </td>
                    <td>
                        <?php if(element('option_name',$order_item_info) != ""){
                            echo element('option_name',$order_item_info);
                         } ?>
                    </td>
                    <td><?php
                        if(element('product_type', $order_item_info) == '3')
                            echo '<span class="badge badge-danger">추가구성</span>';
                        else if(element('product_type', $order_item_info, '') == '2')
                            echo '<span class="badge badge-success">옵션</span>'; ?></td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            매핑 상품 <?php if(element('status',$order_info) == 1) echo "등록 수정"; ?></div>
        <div class="panel-body">

            <div class="row">
                <?php if(element('status',$order_info) == 1) { ?>
                <div class="col-md-6">

                    <form id="search-product-form" onsubmit="return searchMappingItem()">
                        <div class="row justify-content-md-center">
                            <div class="col-md-6 form-group">
                                <input type="text" class="form-control" name="virtual_item_id" placeholder="VCODE"/>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-success">검색</button>
                            </div>
                        </div>
                    </form>

                    <div id="search-result-div"></div>
                <?php } ?>
                </div>
                <div class="col-md-<?php if(element('status',$order_info) == 1) echo "6"; else echo "12";?>">
                    <h5>매핑 연결 상품</h5>
                    <form id="connect-mapping-form">
                        <input type="hidden" name="virtual_item_id" value="<?php echo element('virtual_item_id', $order_item_info)?>"/>
                        <input type="hidden" name="order_id" value="<?php echo element('order_id', $order_info)?>"/>
                        <input type="hidden" name="order_item_id" value="<?php echo element('order_item_id', $order_item_info)?>"/>
                        <input type="hidden" name="product_no" value="<?php echo element('channel_product_no', $order_item_info)?>"/>
                        <input type="hidden" name="product_name" value="<?php echo element('product_name', $order_item_info)?>"/>
                        <div class="table-responsive">
                            <table class="table table-hover" id="mapping-product-table" style="font-size:10px;">
                                <thead>
                                <tr>
                                    <th>VCODE</th>
                                    <th>상품정보</th>
                                    <th>매핑UPC</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr id="mapping-product-row-<?php echo element('virtual_item_id',$order_item_info) ?>">
                                    <td><?php if($current_virtual_id > 0) echo 'V'.str_pad($current_virtual_id, 9, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php
                                        if(count($current_virtual_item_info) > 0){

                                            for($k = 0; $k < count($current_virtual_item_info); $k++){

                                                $now_current_virtual_item_info = $current_virtual_item_info[$k];

                                                $current_master_item_info = element(element('master_item_id', $now_current_virtual_item_info), $master_item_info, array());

                                                if($k > 0) echo '<br/><br/>';

                                                echo element('mfgname', $current_master_item_info, '') . ' ' . element('item_name', $current_master_item_info, '');
                                            }
                                        } ?></td>
                                    <td><?php
                                        if(count($current_virtual_item_info) > 0){

                                            for($k = 0; $k < count($current_virtual_item_info); $k++){

                                                $now_current_virtual_item_info = $current_virtual_item_info[$k];

                                                $current_master_item_info = element(element('master_item_id', $now_current_virtual_item_info), $master_item_info, array());

                                                if($k > 0) echo '<br/><br/>';

                                                echo element('upc', $current_master_item_info, '') . ' X ' . element('quantity', $now_current_virtual_item_info) . '<br/>';
                                            }
                                        } ?></td>
                                        <td>
                                            <?php if(element('status',$order_info) == 1) { ?>
                                            <button type="button"
                                                    onclick="deleteMappingProduct('<?php echo element('virtual_item_id',$order_item_info) ?>')"
                                                    class="btn btn-primary">삭제
                                            </button>
                                            <?php } ?>
                                        </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>
<div class="modal-footer">
    <?php if(element('status',$order_info) == 1){ ?>
    <button type="button" onclick="saveMapping()" class="btn btn-primary">매핑수정</button>
    <?php } ?>
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>

<script type="text/javascript">

    function saveMapping() {

        var mapping_cnt = $(".mapping-product-vc-id").length;

        if(mapping_cnt < 1){
            alert("매핑상품을 추가해주세요.");
            return false;
        }

        if(confirm("입력하신 상품정보로 매핑처리 하시겠습니까?")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "http://oms.ntwsec.com/qten/order/order/saveMapping",
                data: $("#connect-mapping-form").serialize(),
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

    function addMappingProduct(virtual_item_id) {

        var error = false;

        $(".mapping-product-vc-id").each(function () {
            if($(this).val() != "") error = true;
        });

        if(error){
            alert("이미 상품을 추가하셨습니다.");
            return false;
        }

        var append_html = "";

        $.ajax({
            type: "GET",
            async: false,
            dataType: "json",
            url: "http://oms.ntwsec.com/qten/order/order/getItemInfo",
            data: "virtual_item_id=" + virtual_item_id,
            success: function (json) {

                append_html += "<tr id=\"mapping-product-row-" + json.virtual_item_id + "\">";
                append_html += "<td>" + json.virtual_item_id + "<input type=\"hidden\" name=\"virtual_item_ids\" class=\"mapping-product-vc-id\" value=\"" + json.virtual_item_id + "\">" + "</td>" ;
                append_html += "<td>" + json.product_name + "</td>";
                append_html += "<td>" + json.upc_info + "</td>";
                append_html += "<td><button type=\"button\" onclick=\"deleteMappingProduct('" + json.virtual_item_id + "')\" class=\"btn btn-primary\">삭제</button></td>";

                $("#mapping-product-table > tbody").append(append_html);
            },
            error: function (xhr, status, error) {
                alert("잠시 통신이 원활하지 않습니다. 잠시후 다시 시도해주세요.");
            }
        });

    }

    function deleteMappingProduct(it_id) {
        $("#mapping-product-row-" + it_id).remove();
    }

    function searchMappingItem() {

        $.ajax({
            type: "GET",
            async: false,
            dataType: "html",
            url: "http://oms.ntwsec.com/qten/order/order/mapping_search",
            data: $("#search-product-form").serialize(),
            success: function (html) {
                $("#search-result-div").empty().html(html);
            },
            error: function (xhr, status, error) {
                alert("잠시 통신이 원활하지 않습니다. 잠시후 다시 시도해주세요.");
            }
        });

        return false;
    }
</script>
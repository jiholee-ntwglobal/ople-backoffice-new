<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오전 11:19
 */
$option_tpl = '<option value="%s" %s>%s</option>';
?>
<div class="row">
    <ul class="nav nav-pills">
        <li class="<?php if($status == '1') echo 'active'; ?>"><a href="#" onclick="location.href='<?php echo site_url('order/order/index?status=1'); ?>'" data-toggle="tab">대기</a></li>
        <li class="<?php if($status == '3') echo 'active'; ?>"><a href="#" onclick="location.href='<?php echo site_url('order/order/index?status=3'); ?>'" data-toggle="tab">준비</a></li>
        <li class="<?php if($status == '9') echo 'active'; ?>"><a href="#" onclick="location.href='<?php echo site_url('order/order/index?status=9'); ?>'" data-toggle="tab">취소</a></li>
        <li class="<?php if($status == '5') echo 'active'; ?>"><a href="#" onclick="location.href='<?php echo site_url('order/order/index?status=5'); ?>'" data-toggle="tab">배송</a></li>
        <li class="<?php if($status == '7') echo 'active'; ?>"><a href="#" onclick="location.href='<?php echo site_url('order/order/index?status=7'); ?>'" data-toggle="tab">완료</a></li>
        <li class="<?php if($status == 'all') echo 'active'; ?>"><a href="#" onclick="location.href='<?php echo site_url('order/order/index?status=all'); ?>'" data-toggle="tab">전체</a></li>
    </ul>

    <form id="list-search-form" method="get">

        <input type="hidden" name="status" value="<?php echo $status; ?>">
        <input type="hidden" name="page_per_list" value="<?php echo $page_per_list; ?>">
        <input type="hidden" name="excel_fg" value="N">

        <div class="row">

            <div class="col-md-2"></div>

            <div class="col-md-1 form-group">
                <label>채널정보</label>
                <select class="form-control" name="channel_id">
                    <option value="">채널 선택</option>
                    <?php
                    foreach ($channel_arr as $current_channel_id => $channel){
                        $select = $current_channel_id == $channel_id ? 'selected' : '';
                        echo sprintf($option_tpl, $current_channel_id, $select, element('comment', $channel));
                    }
                    ?>

                </select>
            </div>

            <div class="col-md-1 form-group">
                <label>결제기간조회1</label>
                <input type="text" id="datepicker-from" class="form-control" name="start_dt" value="<?php echo $start_dt; ?>">
            </div>

            <div class="col-md-1 form-group">
                <label>결제기간조회2</label>
                <input type="text" id="datepicker-to" class="form-control" name="end_dt" value="<?php echo $end_dt; ?>">
            </div>

            <div class="col-md-2 form-group">
                <label>조회조건</label>
                <select class="form-control" name="search_type">
                    <option value="">조회조건 선택</option>
                    <option <?php if($search_type == 'channel_order_no') echo 'selected'; ?> value="channel_order_no">채널주문번호</option>
                    <option <?php if($search_type == 'channel_product_no') echo 'selected'; ?> value="channel_product_no">채널상품번호</option>
                    <option <?php if($search_type == 'buyer_name') echo 'selected'; ?> value="buyer_name">구매자명</option>
                    <option <?php if($search_type == 'receiver_name') echo 'selected'; ?> value="receiver_name">수령자명</option>
                    <option <?php if($search_type == 'package_no') echo 'selected'; ?> value="package_no">장바구니번호</option>
                    <option <?php if($search_type == 'shipping_code') echo 'selected'; ?> value="shipping_code">송장번호</option>
                    <option <?php if($search_type == 'shipping_ordercode') echo 'selected'; ?> value="shipping_ordercode">배송주문번호</option>
                    <option <?php if($search_type == 'memo') echo 'selected'; ?> value="memo">관리메모</option>
                </select>
            </div>

            <div class="col-md-2 form-group">
                <label>조회값(엔터구분) <input type="checkbox" name="sorting" value="Y" <?php if($sorting=='Y') echo 'checked'; ?>> 정렬적용</label>
                <textarea name="search_value" class="form-control"><?php echo $search_value; ?></textarea>
                <!--<input type="text" class="form-control" name="search_value" value=""/>-->
            </div>

            <div class="col-md-1 form-group">
                <label>매핑여부</label>
                <select class="form-control" name="mapping">
                    <option value="">전체</option>
                    <option <?php if($mapping == 'Y') echo 'selected'; ?> value="N">미매핑</option>
                    <option <?php if($mapping == 'N') echo 'selected'; ?> value="Y">매핑</option>
                </select>
            </div>

            <div class="col-md-1 form-group">
                <label>에러여부</label>
                <select class="form-control" name="validate_error">
                    <option value="">전체</option>
                    <option value="Y" <?php if($validate_error != '') echo 'selected'; ?>>에러만</option>
                </select>
            </div>


            <div class="col-md-1"><button class="btn btn-success"> 검색</button></div>

        </div>

    </form>
</div>
<div class="row">
    <div class="col-md-2 form-group">
        <select class="form-control" id="page-per-list">
            <option value="100" <?php if($page_per_list == '100') echo 'selected'; ?>>100개씩 보기</option>
            <option value="200" <?php if($page_per_list == '200') echo 'selected'; ?>>200개씩 보기</option>
            <option value="500" <?php if($page_per_list == '500') echo 'selected'; ?>>500개씩 보기</option>
        </select>
    </div>
    <div class="col-md-4"></div>
        <div class="col-md-6">
            <button type="button" onclick="downloadOrderExcel()" class="btn btn-success">엑셀 다운로드</button>
            <?php if($status == '1' || $status == '3'){ ?><button type="button" onclick="cancelOrder()" class="btn btn-danger">선택 취소처리</button><?php } ?>
            <?php if(($status == '1')) { ?><button type="button" onclick="partcancelOrder()" class="btn btn-warning">선택 부분취소처리</button><?php } ?>
            <?php if($status == '5'){ ?><button type="button" onclick="cancelShippingOrder()" class="btn btn-danger">선택 취소처리(배송)</button><?php }?>
            <?php if($status == '5') { ?><button type="button" onclick="partcancelShippingOrder()" class="btn btn-warning">선택 부분취소처리(배송)</button><?php } ?>
            <?php if($status == '9') { ?><button type="button" onclick="rollbakcCancelOrder()" class="btn btn-danger">선택취소복구처리</button><?php } ?>
            <?php if($status == '5' && $worker_ids_fg == 1){ ?><button type="button" onclick="completeShippingOrder()" class="btn btn-primary">선택 완료처리(배송)</button><?php } ?>
            <?php if($status == '1' && $worker_ids_fg == 1){ ?><button type="button" onclick="completeReadyOrder()" class="btn btn-primary">선택 확인처리</button><?php } ?>
        </div>
</div>

<div class="row">
    <form id="list-data-form">
        <input type="hidden" name="status" value="<?php echo $status;?>">
        <div class="table-responsive">
            <table class="table" style="font-size:10px;">
                <thead>
                <tr>
                    <th><input type="checkbox" id="all-checkbox"></th>
                    <th>에러</th>
                    <th>채널명</th>
                    <th>배송주문번호</th>
                    <th>채널주문번호</th>
                    <th>장바구니번호</th>
                    <th>주문일자</th>
                    <th>상품번호</th>
                    <th>상품명</th>
                    <th>옵션명</th>
                    <th>수량</th>
                    <th>인보이스금액</th>
                    <th>개인통관고유번호</th>
                    <th>처리상태</th>
                    <th>송장번호</th>
                    <th>매핑여부</th>
                    <th>수정</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($list_data_result->result_array() as $list_data){

                    $badge_html = '';

                    if(element('validate_error', $list_data, 0) > 0){
                        for($k = 0; $k < 6; $k++){
                            $error_style = ($k==0) ? "" : "style=\"background-color:red;color:white\"";
                            if(element('validate_error', $list_data) & 2 ** $k) {
                                $badge_html .= '<span class="badge badge-danger" '.$error_style.'>' . $order_validate_error[2 ** $k] . '</span><br>';
                            }
                        }
                    }

                    if(element('validate_error', $list_data) & 64){
                        $error_style = ($k==0) ? "" : "style=\"background-color:green;color:white\"";
                        $badge_html .= '<span class="badge badge-danger" '.$error_style.'>' . $order_validate_error[64] . '</span><br>';
                    }

                    if(element('validate_error', $list_data) & 128){
                        $error_style = ($k==0) ? "" : "style=\"background-color:blue;color:white\"";
                        $badge_html .= '<span class="badge badge-danger" '.$error_style.'>' . $order_validate_error[128] . '</span><br>';
                    }

                    $channel_name = element('comment', element(element('channel_id', $list_data),$channel_arr, array()), '');

                    $shipping_order_no	= element('shipping_order_code_prefix', element(element('channel_id', $list_data),$channel_arr, array()), ''). date("ymd",strtotime(element('order_date',$list_data))) . str_pad(dechex(element('order_id', $list_data)), 6, "0", STR_PAD_LEFT);
					
                    $status_txt = element(element('status', $list_data), $config_order_status, '');

                    $status_txt = (element('cancel_flag',$list_data)==1) ? "취소" : $status_txt ;


                    $shipping_code_link_s = (element('shipping_code', $list_data, '')) ? '<a href="https://track.shiptrack.co.kr/epost/'.element('shipping_code', $list_data, '').'" target="_blank">' : "";
                    $shipping_code_link_e = (element('shipping_code', $list_data, '')) ? '</a>' : "";

                ?>
                    <tr class="<?php if(element('validate_error', $list_data, 0) > 0) echo 'bg-danger'; ?>">
                        <td><input type="checkbox" class="list-checkbox error-order-checkbox" name="order_ids[]" status="<?php echo element('status', $list_data); ?>" value="<?php echo element('order_item_id', $list_data); ?>">
                        </td>
                        <td><?php echo $badge_html; ?></td>
                        <td><?php echo $channel_name; ?></td>
                        <td><?php echo $shipping_order_no; ?></td>
                        <td><a href="javascript:loadOrderDetail('<?php echo element('order_id', $list_data); ?>')"><?php echo element('channel_order_no', $list_data); ?></a>
                            <a href="#" onclick="loadOrderHistory('<?php echo element('order_id', $list_data); ?>')">
                                <p class="fa fa-search-plus"></p>
                            </a>
                        </td>
                        <td><?php echo element('package_no', $list_data); ?></td>
                        <td><?php echo element('order_date', $list_data); ?></td>
                        <td><a href="<?php echo site_url('item/channel_item/openChannelUrl/' . element('channel_id', $list_data). '/' . element('channel_product_no', $list_data)); ?>" target="_blank"><?php echo element('channel_product_no', $list_data); ?></a></td>
                        <td><?php echo element('product_name', $list_data); ?></td>
                        <td><?php echo (element('product_type', $list_data) == '3' ? '<span class="badge badge-danger">추</span>' : '') . element('option_name', $list_data); ?></td>
                        <td>
                            <?php if( ($status == '1' || $status == '3') && $_SERVER['REMOTE_ADDR']=='211.214.213.101' ){ ?>
                                <input type="text" style="width:30px;" id="ordQty_<?php echo element('order_item_id', $list_data); ?>" value="<?php echo element('qty', $list_data); ?>"/>
                                <button type="button" onclick="changeQuantity('<?php echo element('order_item_id', $list_data); ?>','<?php echo element('order_id', $list_data); ?>')">수량 업데이트</button>
                            <?php } else {
                                echo number_format(element('qty', $list_data));
                            }?>
                        </td>
                        <td><?php echo element('total_amount_usd', $list_data, ''); ?></td>
                        <td><?php echo element('customer_number', $list_data, ''); ?></td>
                        <td><?php echo $status_txt; ?></td>
                        <td>
                            <?php echo $shipping_code_link_s; ?><?php echo element('shipping_code', $list_data, ''); ?><?php echo $shipping_code_link_e; ?>
                        </td>
                        <td><a href="javascript:loadMappingLayer('<?php echo element('order_item_id', $list_data); ?>')">매핑</a></td>
                        <td>
                            <?php
                            if($status == '3' || $status == '1'){ ?>
                            <button type="button" onclick="loadEditLayer('<?php echo element('order_id', $list_data); ?>')" class="btn btn-primary btn-sm">수정</button>
                            <?php }?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="row"><?php echo $paging_content; ?></div>
    </form>
</div>

<div class="modal fade" id="order-detail-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" style="width:1500px;">
        <div class="modal-content" id="order-detail-layer-content"></div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="mapping-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" style="width:1200px;">
        <div class="modal-content" id="mapping-layer-content"></div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="edit-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" style="width:1200px;">
        <div class="modal-content" id="edit-layer-content"></div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="order-history-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content" id="order-history-layer-content"></div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div style="display:none">
    <form id="excel-hidden-form" method="GET">
        <input type="hidden" name="status" value="<?php echo $status; ?>"/>
        <input type="hidden" name="page_per_list" value="<?php echo $page_per_list; ?>"/>
        <input type="hidden" name="excel_fg" value="Y"/>
        <input type="hidden" name="error_order" value="<?php if(isset($error_order) && $error_order == 'Y') echo 'Y'; ?>"/>
        <input type="hidden" name="start_dt" value="<?php echo $start_dt; ?>"/>
        <input type="hidden" name="end_dt" value="<?php echo $end_dt; ?>"/>
        <input type="hidden" name="search_type" value="<?php echo $search_type; ?>"/>
        <textarea name="search_value"><?php echo $search_value; ?></textarea>
        <input type="hidden" name="mapping_fg" value="<?php echo $mapping; ?>"/>
    </form>
</div>
<script type="text/javascript">

    function changeQuantity(order_item_id,order_id){
        if($("#ordQty_" + order_item_id).val() == "" || $("#ordQty_" + order_item_id).val() < 1){
            alert("변경수량은 0 이상 입력하세요.");
            return false;
        }
        if(confirm($("#ordQty_" + order_item_id).val() + "로 주문수량을 변경하시겠습니까?")){
            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url("order/order/updateOrderProductQuantity"); ?>",
                data: "order_id="+order_id+"&order_item_id=" + order_item_id + "&quantity=" + $("#ordQty_" + order_item_id).val(),
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

    $(document).ready(function () {

        $("#page-per-list").change(function () {
            $(":hidden[name=page_per_list]").val($(this).val());
            $("#list-search-form").submit();
        });

        $("#all-checkbox").click(function () {
            $(".list-checkbox").prop("checked", ($(this).is(":checked") ? "checked" : false));$(".list-checkbox").trigger("change");
        });

        $( function() {
            var dateFormat = "yy-mm-dd",
                from = $( "#datepicker-from" )
                    .datepicker({
                        defaultDate: "+1w",
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        numberOfMonths: 1
                    })
                    .on( "change", function() {
                        to.datepicker( "option", "minDate", getDate( this ) );
                    }),
                to = $( "#datepicker-to" ).datepicker({
                    defaultDate: "+1w",
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    numberOfMonths: 1
                })
                    .on( "change", function() {
                        from.datepicker( "option", "maxDate", getDate( this ) );
                    });

            function getDate( element ) {
                var date;
                try {
                    date = $.datepicker.parseDate( dateFormat, element.value );
                } catch( error ) {
                    date = null;
                }

                return date;
            }
        } );

        $("#datepicker-from").datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect : function(selectDate){
                setEdate = selectDate;
                console.log(setEdate)
            }
        });

        $(".list-checkbox").on("change", function () {
            if($(this).is(":checked")){
                $(this).parent().parent().css({"font-weight":"bold", "font-size":"11px"});
            } else{
                $(this).parent().parent().css({"font-weight":"normal", "font-size":"10px"});
            }

        });


    });

    function partcancelOrder() {

        if($(".list-checkbox:checked").length < 1){
            alert("부분 취소처리할 주문서를 선택하세요.");
            return false;
        }

        var error_count = 0;

        $(".list-checkbox:checked").each(function () {
            if($(this).attr("status") == '7' || $(this).attr("status") == '9') error_count++;
        });

        if(error_count > 0){
            alert("이미 부분취소처리가 되었거나 완료처리가 된 주문서는 부분취소처리가 불가능합니다.");
            return false;
        }

        if(confirm("선택하신 " + $(".list-checkbox:checked").length + "개의 주문서를 부분취소처리 하시겠습니까?")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url("order/order/partcancelOrder"); ?>",
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

    function partcancelShippingOrder() {

        if($(".list-checkbox:checked").length < 1){
            alert("부분취소처리(배송)할 주문서를 선택하세요.");
            return false;
        }

        var error_count = 0;

        $(".list-checkbox:checked").each(function () {
            if($(this).attr("status") != '5') error_count++;
        });

        if(error_count > 0){
            alert("배송상태의 주문서만 처리할 수 있습니다.");
        }

        if(confirm("배송 부분취소 처리를 하시겠습니까?\n현재는 재고복구 및 판매량 원복 기능만 지원합니다.\n반드시 배송전산 프로그램에서 선택 주문서에 대한 부분취소처리를 하셔야 합니다.")){
            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url("order/order/partcancelShippingOrders"); ?>",
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
    
    function cancelOrder() {

        if($(".list-checkbox:checked").length < 1){
            alert("취소처리할 주문서를 선택하세요.");
            return false;
        }

        var error_count = 0;

        $(".list-checkbox:checked").each(function () {
            if($(this).attr("status") == '7' || $(this).attr("status") == '9') error_count++;
        });

        if(error_count > 0){
            alert("이미 취소처리가 되었거나 완료처리가 된 주문서는 취소처리가 불가능합니다.");
            return false;
        }

        if(confirm("선택하신 " + $(".list-checkbox:checked").length + "개의 주문서를 취소처리 하시겠습니까?")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url:
                    "<?php echo site_url("order/order/cancelOrder"); ?>",
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

    function cancelShippingOrder() {

        if($(".list-checkbox:checked").length < 1){
            alert("취소처리(배송)할 주문서를 선택하세요.");
            return false;
        }

        var error_count = 0;

        $(".list-checkbox:checked").each(function () {
            if($(this).attr("status") != '5') error_count++;
        });

        if(error_count > 0){
            alert("배송상태의 주문서만 처리할 수 있습니다.");
        }

        if(confirm("배송 취소 처리를 하시겠습니까?\n현재는 재고복구 및 판매량 원복 기능만 지원합니다.\n반드시 배송전산 프로그램에서 선택 주문서에 대한 취소처리를 하셔야 합니다.")){
            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php  echo site_url("order/order/cancelShippingOrders"); ?>",
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
    function completeReadyOrder() {
        if($(".list-checkbox:checked").length < 1){
            alert("준비처리할 주문서를 선택하세요.");
            return false;
        }

        var error_count = 0;

        $(".list-checkbox:checked").each(function () {
            if($(this).attr("status") != '1') error_count++;
        });

        if(error_count > 0){
            alert("대기상태의 주문서만 처리할 수 있습니다.");
            return false;
        }

        if(confirm("준비 처리를 하시겠습니까?\n반드시 주문서확인 주문서와 ESM에서 발주확인이 된 주문서만 처리해주시기 바랍니다.\n")){
            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url("order/order/completeReadyOrders"); ?>",
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

    function completeShippingOrder() {
        if($(".list-checkbox:checked").length < 1){
            alert("완료처리(배송)할 주문서를 선택하세요.");
            return false;
        }

        var error_count = 0;

        $(".list-checkbox:checked").each(function () {
            if($(this).attr("status") != '5') error_count++;
        });

        if(error_count > 0){
            alert("배송상태의 주문서만 처리할 수 있습니다.");
            return false;
        }

        if(confirm("배송 완료 처리를 하시겠습니까?\n반드시 ESM에서 이미 수동으로 완료처리한 주문서만 처리해주시기 바랍니다.\n")){
            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url("order/order/completeShippingOrders"); ?>",
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

    function rollbakcCancelOrder() {

        if($(".list-checkbox:checked").length < 1){
            alert("취소 복구처리할 주문서를 선택하세요.");
            return false;
        }

        var error_count = 0;

      /*  $(".list-checkbox:checked").each(function () {
            if($(this).attr("status") != '9') error_count++;
        });



        if(error_count > 0){
            alert("취소처리가 된 주문서는 취소 복구처리가 불가능합니다.");
            return false;
        }*/

        if(confirm("선택하신 " + $(".list-checkbox:checked").length + "개의 주문서를 취소 복구처리 하시겠습니까?")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url:  "<?php echo site_url('order/order/rollbackCancelOrder'); ?>",
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


    function loadOrderDetail(order_id) {

        $("#order-detail-layer-content").empty();
        $("#order-detail-layer-content").load("<?php echo site_url('order/order/detail'); ?>/" + order_id);

        $('#order-detail-modal').modal('toggle');

        return false;

    }

    function loadOrderHistory(order_id) {

        $("#order-history-layer-content").empty();
        $("#order-history-layer-content").load("<?php echo site_url('order/order/history'); ?>/" + order_id);

        $('#order-history-modal').modal('toggle');

        return false;

    }

    function loadEditLayer(order_id) {

        $("#edit-layer-content").empty();
        $("#edit-layer-content").load("<?php echo site_url('order/order/edit_address'); ?>/" + order_id);

        $('#edit-modal').modal('toggle');

        return false;

    }

    function loadMappingLayer (order_id) {

        $("#mapping-layer-content").empty();
        $("#mapping-layer-content").load("<?php echo site_url('order/order/mapping_detail'); ?>/" + order_id);

        $('#mapping-modal').modal('toggle');

        return false;
    }
    function downloadOrderExcel(){
        $("#excel-hidden-form").submit();
    }

</script>

<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오후 4:39
 */
@$shipping_code_link_s = (element('shipping_code', $order_data, '')) ? '<a href="https://track.shiptrack.co.kr/epost/'.element('shipping_code', $order_data, '').'" target="_blank">' : "";
@$shipping_code_link_e = (element('shipping_code', $order_data, '')) ? '</a>' : "";
$shipping_order_no = element('shipping_order_code_prefix', $channel_info).str_pad(element('order_id', $order_data), 9, "0", STR_PAD_LEFT);
$shipping_order_no	= element('shipping_order_code_prefix', $channel_info). date("ymd",strtotime(element('order_date',$order_data))) . str_pad(dechex(element('order_id', $order_data)), 6, "0", STR_PAD_LEFT);

?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">주문서 상세정보</h4>
</div>
<div class="modal-body">

    <div class="row">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" style="font-size:12px;">
                                <thead>
                                <tr>
                                    <th>채널명</th>
                                    <th>장바구니번호</th>
                                    <th>결제번호</th>
                                    <th>배송주문번호</th>
                                    <th>처리상태</th>
                                    <th>통관고유부호</th>
                                    <th>송장번호</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo element('comment', $channel_info); ?></td>
                                    <td><?php echo element('package_no', $order_data); ?></td>
                                    <td><?php echo element('pay_no', $order_data); ?></td>
                                    <td><?php echo $shipping_order_no; ?></td>
                                    <td><?php echo $status_txt; ?></td>
                                    <td><?php echo element('customer_number', $order_data); ?></td>
                                    <td>
                                            <?php echo $shipping_code_link_s; ?><?php echo element('shipping_code', $order_data, ''); ?><?php echo $shipping_code_link_e; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7">
                                        <?php echo "주문일시 : " . element('order_date', $order_data); ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <?php echo $error_txt; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" style="font-size:12px;">
                                <thead>
                                <tr>
                                    <th>구매자명</th>
                                    <th>구매자 연락처</th>
                                    <th>수령인명</th>
                                    <th>수령인 연락처</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo element('buyer_name', $order_data, ''); ?></td>
                                        <td><?php echo element('buyer_tel1', $order_data, ''); ?></td>
                                        <td><?php echo element('receiver_name', $order_data, ''); ?></td>
                                        <td><?php echo element('receiver_tel1', $order_data, ''); ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <?php echo element('zipcode', $order_data) . ' ' . element('addr1', $order_data) . ' ' . element('addr2', $order_data); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                구매 상품 정보
            </div>
            <div class="panel-body" style="overflow-y: scroll; height:250px;">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" style="font-size:10px;">
                        <thead>
                        <tr>
                            <th>채널주문번호</th>
                            <th>상품번호</th>
                            <th>상품명</th>
                            <th>옵션명</th>
                            <th>추가구성 옵션명</th>
                            <th>구매수량</th>
                            <th colspan="2">매핑정보</th>
                            <th>상품재고</th>
                            <th>주문상태</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($order_item_data as $order_item){

                            $current_virtual_item_info = array();

                            $current_virtual_id = element('virtual_item_id', $order_item) > 0 ? element('virtual_item_id', $order_item) : element('add_virtual_item_id', $order_item);


                            if(element('virtual_item_id', $order_item) > 0){
                                $current_virtual_item_info = element(element('virtual_item_id', $order_item), $virtual_item_info, array());
                            }
                        ?>
                        <tr>
                            <td><?php echo element('channel_order_no', $order_item); ?></td>
                            <td><?php echo element('channel_product_no', $order_item); ?></td>
                            <td><?php echo element('product_name', $order_item); ?></td>
                            <td><?php echo element('option_name', $order_item); ?></td>
                            <td><?php echo element('add_option_name', $order_item); ?></td>
                            <td><?php echo element('qty', $order_item); ?></td>
                            <td><?php if($current_virtual_id > 0) echo 'V'.str_pad($current_virtual_id, 9, '0', STR_PAD_LEFT); ?></td>
                            <td><?php
                                if(count($current_virtual_item_info) > 0){

                                    for($k = 0; $k < count($current_virtual_item_info); $k++){

                                        $now_current_virtual_item_info = $current_virtual_item_info[$k];

                                        $current_master_item_info = element(element('master_item_id', $now_current_virtual_item_info), $master_item_info, array());

                                        if($k > 0) echo '<br/><br/>';

                                        echo element('upc', $current_master_item_info, '') . ' X ' . element('quantity', $now_current_virtual_item_info) . '<br/>';
                                        echo element('mfgname', $current_master_item_info, '') . ' ' . element('item_name', $current_master_item_info, '');

                                    }
                                } ?></td>
                            <td><?php
                                if(count($current_virtual_item_info) > 0){

                                    for($k = 0; $k < count($current_virtual_item_info); $k++){

                                        $now_current_virtual_item_info = $current_virtual_item_info[$k];

                                        $current_master_item_info = element(element('master_item_id', $now_current_virtual_item_info), $master_item_info, array());

                                        if($k > 0) echo '<br/><br/>';

                                        echo  element('currentqty', $current_master_item_info, '').'<br/>';

                                    }
                                } ?></td>
                                <td><?php echo element('cancel_flag', $order_item)=="1" ? "취소" : $status_txt;?></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                관리자 메모
            </div>
            <div class="panel-body"  style="overflow-y: scroll; height:160px;">
                <div class="table-responsive col-md-6">
                    <table class="table table-striped table-bordered table-hover" style="font-size:10px;">
                        <thead>
                        <tr>
                            <th>메모</th>
                            <th style="width: 20%;">등록날짜</th>
                            <th>등록자</th>
                        </tr>
                        </thead>
                        <tbody id="comment_data">

                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <form class="form-inline" id="manager-memo-form">
                        <input type="hidden" name="order_id" value="<?php echo element('order_id', $order_data); ?>"/>
                    <textarea class="form-control" minlength="1" maxlength="200" rows="3" style="margin: 0px; width: 593px; height: 124px;" name="comment" required></textarea>
                        <button type="button" onclick="saveManagerMemo()" class="btn btn-primary">메모 저장</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>


<style>
    #loding_temple {
        z-index: 30;
        position: fixed;
        top: 50%;
        left: 50%;
        margin-left: -21px;
        margin-top: -21px;
    }

    .wrap-loading { /*화면 전체를 어둡게 합니다.*/
        z-index: 29;
        position: fixed;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.2); /*not in ie */
        filter: progid:DXImageTransform.Microsoft.Gradient(startColorstr='#20000000', endColorstr='#20000000'); /* ie */

    }

    .display-none { /*감추기*/
        display: none;
    }

</style>
<div class="row">
    <div class="lodinggood display-none">
        <div class="col-sm-12">
            <div class="pace-demo" id="loding_temple">
                <div class="theme_tail">
                    <div class="pace_progress"></div>
                    <div class="pace_activity"></div>
                </div>
            </div>
            <div class="wrap-loading">
            </div>
        </div>
    </div>
</div>

<script>
    saveManagerMemo();
    function saveManagerMemo() {
        $.ajax({
            type: "POST",
            async: false,
            dataType: "json",
            url: "<?php echo site_url("order/order/comment"); ?>",
            data: $("#manager-memo-form").serialize(),
            success: function (json) {
                if(json.length>0){
                    var tr = '';
                    $.each(json, function (i, item) {
                        tr += '' +
                            '<tr>' +
                            '<td>' + item.comment + '</td>' +
                            '<td>' + item.create_date + '</td>' +
                            '<td>' + item.worker_name + '</td>' +
                            '</tr>';
                    });
                    $('#comment_data').html(tr);

                }
            },
            error: function (xhr, status, error) {
                alert("잠시 통신이 원활하지 않습니다. 잠시후 다시 시도해주세요.");
            },
            beforeSend: function () {
                console.log('DONE');
                $('.lodinggood').removeClass('display-none');
            },
            complete: function () {
                console.log('DONE1');
                $('.lodinggood').addClass('display-none');

            }
        });
    }
</script>
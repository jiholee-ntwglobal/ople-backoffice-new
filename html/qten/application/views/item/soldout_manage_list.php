<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-10-17
* Time : 오후 4:14
*/
$option_tpl = '<option value="%s" %s>%s</option>';
$stockfg_txt = ($stock_status=="Y") ?"판매중지" : "판매개시";

?>
<div class="row">
    <div class="col-md-12">
        <h4>수동 품절관리 리스트(단품)</h4>
    </div>
</div>
<div class="alert alert-danger">
    ※ 엔틱스 재고, 통관 상품, 품절 예외를 기준으로 한 품절관리 리스트입니다.
    <br>
    ※ 해당 페이지에서 실행되는 품절해제/품절은 백오피스에서의 상태만 변경됩니다. <b>ESM에서 따로 상품상태를 반드시 변경해주세요. </b>
    <br>
    ※ 해당 페이지에 표기되는 NTICS QTY는 실시간이 아닙니다. 1시간에 1번씩 동기화된 데이터입니다.
    <br>
</div>

<div class="row">
<form action="<?php echo site_url('/item/single_item'); ?>" id="frm" class="form-inline" method="get">


    <div class="col-md-4">
        <ul class="nav nav-pills">
            <li class="<?php if($stock_status=="N") echo "active";?>"><a href="#" onclick="location.href='<?php echo site_url('/item/single_item/soldoutList/N');?>'" data-toggle="tab" >판매개시 대상</a></li>
            <li class="<?php if($stock_status=="Y") echo "active";?>"><a href="#" onclick="location.href='<?php echo site_url('/item/single_item/soldoutList/Y');?>'" data-toggle="tab">판매중지 대상</a></li>
        </ul>
    </div>

<!--        <input type="hidden" name="sold_out_flag" value="Y">
-->        <div class="col-md-2 form-group">
<!--            <select class="form-control" name="search_type">
                <option value="">조회조건 선택</option>
                <option value="product_no">11번가 상품코드</option>
                <option value="it_id">오플 상품코드</option>
            </select>-->
        </div>

        <div class="col-md-2 form-group">
<!--            <input type="text" class="form-control" placeholder="조회값" name="search_value" value="">
-->        </div>

        <div class="col-md-2 form-group">
<!--            <button type="submit" class="btn btn-primary">검색</button>
-->        </div>


    <div class="col-md-2">
        <button type="button" onclick="stockAction()" class="btn btn-danger"><?php echo $stockfg_txt?></button>
        <button type="button" onclick="downloadExcel()" class="btn btn-danger">엑셀 다운로드</button>
    </div>
</div>
</form>

    <!-- <div class="col-md-4">
        <button type="button" class="btn btn-danger" onclick="deleteItem()">
            품절
        </button>
    </div>
    <div class="col-md-8 text-right">
        <div class="form-group">
            <select class="form-control" name="channel" onchange="search_data();">
                <option value="">전채채널</option>
                <?php
/*                foreach ($channel_arr as $current_channel_id => $channel){
                    $select = $current_channel_id == $channel_id ? 'selected' : '';
                    echo sprintf($option_tpl, $current_channel_id, $select, element('comment', $channel));
                }
                */?>
            </select>
        </div>
       <div class="form-group">
            <textarea name="channel_item_code" placeholder="상품코드" class="form-control"><?php /*echo $channel_item_code;*/?></textarea>
        </div>

        <div class="form-group">
            <textarea name="vcode" placeholder="VCODE" class="form-control"><?php /*echo $vcode;*/?></textarea>
        </div>

        <div class="form-group">
            <input name="brand"  type="text" placeholder="브랜드" class="form-control" value="<?php /*echo $brand;*/?>">
        </div>

        <div class="form-group">
            <textarea name="upc" placeholder="UPC" class="form-control"><?php /*echo $upc;*/?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">검색</button>
        <button class="btn btn-success" type="button" onclick="downloadOrderExcel()">엑셀다운로드</button>
    </div>-->
<br/>
<div class="row" style="font-size:10px;">
    <?php echo $total_count."건";?>
</div>
<div class="row">
    <div class="table-responsive">
        <form id="list-form" method="post" action="<?php echo site_url('item/single_item/soldoutAction'); ?>">
            <input type="hidden" name="stock_status" value="<?php echo $stock_status?>">
            <table class="table" style="font-size:10px;">
                <thead>
                <tr>
                    <th><input type="checkbox" id="all-checkbox"/></th>
                    <th>채널</th>
                    <th>상품코드</th>
                    <th>VCODE</th>
                    <th>상품갯수</th>
                    <th>가격</th>
                    <th>품절여부</th>
                    <th>NTICS QTY</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($list_datas as $value){

                    $bg_color = "";
                    $bg_color = (element('channel_code', $value, '') == "G") ? "warning" : "success";

                    $stockfg =element('stock_status',$value,'') =='N' ?'품절중':'판매중';
                    $stockfg .= element('need_update',$value,'') =='E'?'오류':'';


                    ?>
                    <tr class = "<?php echo $bg_color;?>">
                        <td><input type="checkbox" class="list-checkbox" name="item_info_ids[]" value="<?php echo element('item_info_id', $value); ?>" /></td>
                        <td><?php echo element('comment',$value,'');?></td>
                        <td>
                            <a href="<?php echo site_url('item/channel_item/openChannelUrl/' . element('channel_id', $value). '/' . element('channel_item_code', $value)); ?>" target="_blank">
                                <?php echo element('channel_item_code',$value,'');?>
                            </a>
                            <?php if(element(element('item_info_id', $value, ''),$item_history_arr)!="") { ?>
                                <a href="#" onclick="loadPriceHistory('<?php echo element('item_info_id', $value); ?>',1)">
                                    <p class="fa fa-search-plus"></p>
                                </a>
                            <?php } ?>
                        </td>
                        <td><?php echo element('virtual_item_id',$value,'')==''?'':"V".str_pad(element('virtual_item_id', $value),"8","0",STR_PAD_LEFT);?></td>
                        <td><?php echo element('item_alias',$value,'');?></td>
                        <td><?php echo element('upload_price',$value,'');?></td>
                        <td><?php echo $stockfg;?></td>
                        <td><?php echo element('currentqty', $value,'')?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
<div class="row"><?php echo $paging_content; ?></div>
<form id="excel-hidden-form" method="GET" action="<?php echo site_url('/item/single_item/soldoutList/'.$stock_status); ?>">
    <input type="hidden" name="excel" value="Y"/>
</form>

<!-- Modal Start -->
<div class="modal fade" id="price-form-modal" tabindex="-1" role="dialog" aria-labelledby="price-form-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="price-form-content">
        </div>
    </div>
</div>
<div class="modal fade" id="info-form-modal" tabindex="-1" role="dialog" aria-labelledby="info-form-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" id="info-form-content">
        </div>
    </div>
</div>
<!-- Modal End -->
<div class="modal fade" id="price-history-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" style="width:1500px;">
        <div class="modal-content" id="price-history-layer-content"></div>
    </div>
</div>
<script>
    function search_data() {
        $('#frm').submit();
    }

    function downloadExcel() {
        $("#excel-hidden-form").submit();
    }
    $("#all-checkbox").click(function () {
        $(".list-checkbox").prop("checked", ($(this).is(":checked") ? "checked" : false));$(".list-checkbox").trigger("change");
    });


    function loadPriceForm(infomationID) {
        var loadUrl	= "<?php echo site_url('item/single_item/priceForm'); ?>/" + infomationID;

        $("#price-form-content").empty().append('<div id="priceForm"></div>');
        $('#priceForm').load(loadUrl);
        $("#price-form-modal").modal("show");
    }

    function loadUpdateInfoForm(infomationID) {
        var loadUrl	= "<?php echo site_url('item/single_item/InfoUpdateForm'); ?>/" + infomationID;

        $("#info-form-content").empty().append('<div id="infoForm"></div>');
        $('#infoForm').load(loadUrl);
        $("#info-form-modal").modal("show");
    }

    function loadPriceHistory(item_info_id,page) {

        $("#price-history-layer-content").empty();
        $("#price-history-layer-content").load("<?php echo site_url('item/single_item/priceHistoryLayer'); ?>/" + item_info_id + "/" + page);

        $('#price-history-modal').modal('show');

        return false;
    }




    function stockAction() {

        if($(".list-checkbox:checked").length < 1){
            alert("처리대상을 선택하세요.");
            return false;
        } else {

            if(confirm("선택하신 " + $(".list-checkbox:checked").length + " 개의 상품에 대한 <?php echo $stockfg_txt; ?> 처리를 진행하시겠습니까?")){
                $("#list-form").submit();
            }

        }

    }

</script>
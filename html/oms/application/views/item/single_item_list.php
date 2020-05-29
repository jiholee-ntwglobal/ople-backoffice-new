<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-31
 * Time: 오후 8:55
 */
$option_tpl = '<option value="%s" %s>%s</option>';
?>
<div class="row">
    <div class="col-md-12">
        <h4>단품 상품 리스트</h4>
    </div>
</div>
<div class="alert alert-danger">
    ※ 검색하실 경우 엑셀 다운로드 버튼이 노출됩니다.
    <br>
    ※ VCODE 비동기화 상품의 경우 매시각 정시(ex. 03:00, 04:00) 이후에 UPC와 브랜드 검색이 가능합니다.
    <br>
    ※ 전채채널 선택란에서 채널을 변경을 할 경우  변경된 채널로 상품 다운로드가 가능합니다
</div>
    <form action="<?php echo site_url('/item/single_item'); ?>" id="frm" class="form-inline" method="get">
        <div class="col-md-4">
            <?php if($worker_ids_fg==1) { ?>
            <button type="button" class="btn btn-danger" onclick="deleteItem()">
                매핑선택삭제
            </button>
            <?php } ?>
        </div>
        <div class="col-md-8 text-right">
            <?php if($error_total_count>0) { ?>
            <div class="form-group">

                <button type="button" class="btn btn-danger" onclick="errorItemDownload()">
                    에러상품 다운로드
                </button>
            </div>
            <?php } ?>
            <div class="form-group">
                <select class="form-control" name="channel" onchange="search_data();">
                    <option value="">전채채널</option>
                    <?php
                    foreach ($channel_arr as $current_channel_id => $channel){
                        $select = $current_channel_id == $channel_id ? 'selected' : '';
                        echo sprintf($option_tpl, $current_channel_id, $select, element('comment', $channel));
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <textarea name="channel_item_code" placeholder="상품코드" class="form-control"><?php echo $channel_item_code;?></textarea>
            </div>

            <div class="form-group">
                <textarea name="vcode" placeholder="VCODE" class="form-control"><?php echo $vcode;?></textarea>
            </div>

            <div class="form-group">
                <input name="brand"  type="text" placeholder="브랜드" class="form-control" value="<?php echo $brand;?>">
            </div>

            <div class="form-group">
                <textarea name="upc" placeholder="UPC" class="form-control"><?php echo $upc;?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">검색</button>
            <?php if($channel_item_code || $vcode || $brand || $upc || $channel_id){?>
                <button class="btn btn-success" type="button" onclick="downloadOrderExcel()">엑셀다운로드</button>
            <?php }?>
        </div>
    </form>
<div class="row" style="font-size:10px;">
    <?php echo $total_count."건";?>
</div>
<div class="row">
    <div class="table-responsive">
        <form id="list-data-form">
        <table class="table" style="font-size:10px;">
            <thead>
            <tr>
                <?php if($worker_ids_fg==1) { ?>
                    <th><input type="checkbox" id="all-checkbox"/></th>
                <?php } ?>
                <th>채널</th>
                <th>상품코드</th>
                <th>VCODE</th>
                <th>상품갯수</th>
                <th>브랜드</th>
                <th>상품명</th>
                <th>가격</th>
                <th>판매자 할인</th>
                <th>판매자 할인단위</th>
                <th>품절여부</th>
                <th>로케이션</th>
                <th>등록날짜</th>
                <th>가격조정 수정날짜</th>
                <th>최초등록자</th>
				<th>btn</th>
                <?php if($worker_ids_fg==1) { ?>
                    <th>매핑관리</th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list_datas as $value){

                $bg_color = "";
                $bg_color = (element('channel_code', $value, '') == "G") ? "warning" : "success";

                if(element('discount_unit',$value,'')=="N" || element('discount_unit',$value,'')=="") $discount_type = "할인 없음" ;
                if(element('discount_unit',$value,'')=="Rate") $discount_type = "비율로 할인" ;
                if(element('discount_unit',$value,'')=="Money") $discount_type = "금액으로 할인" ;

                $mfgname = (element('master_item_id',$value,'')=="" || element('master_item_id',$value,'')==null) ? "VCODE 비동기화" : element('mfgname',$master_item_arr[element('master_item_id',$value,'')]);
                $item_name = (element('master_item_id',$value,'')=="" || element('master_item_id',$value,'')==null) ? "VCODE 비동기화" : element('item_name',$master_item_arr[element('master_item_id',$value,'')]);
                $location = (element('master_item_id',$value,'')=="" || element('master_item_id',$value,'')==null) ? "VCODE 비동기화" : element('location',$master_item_arr[element('master_item_id',$value,'')]);
//                $mfgname	= '임시중단';
//                $item_name	= '임시중단';
//                $location	= '임시중단';
                $stockfg =element('stock_status',$value,'') =='N' ?'품절중':'판매중';
                $stockfg .= element('need_update',$value,'') =='E'?'오류':'';


                ?>
                <tr class = "<?php echo $bg_color;?>">
                    <?php if($worker_ids_fg==1) { ?>
                    <td><input type="checkbox" class="list-checkbox" name="item_info_ids[]" value="<?php echo element('item_info_id', $value); ?>" /></td>
                    <?php } ?>
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
                    <td><?php echo $mfgname ;?></td>
                    <td><?php echo $item_name?></td>
                    <td><?php echo element('upload_price',$value,'');?></td>
					<td><?php echo element('discount_price',$value,'');?></td>
					<td><?php echo $discount_type;?></td>
                    <td><?php echo $stockfg;?></td>
                    <td><?php echo $location; ?></td>
                    <td><?php echo element('create_date',$value,'');?></td>
                    <td>
                        <?php echo element(element('item_info_id', $value, ''),$item_history_arr); ?>
                    </td>
                    <td><?php echo element('worker_id', $value,'');?></td>
					<td>
						<button type="button" class="btn btn-info" onclick="return loadPriceForm(<?php echo element('item_info_id',$value,'');?>);">
							가격조정
						</button>
					</td>
                    <td>
                        <?php if($worker_ids_fg==1) {?>
                            <button type="button" class="btn btn-success" onclick="return loadUpdateInfoForm(<?php echo element('item_info_id',$value,'');?>);">
                                매핑수정
                            </button>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        </form>
    </div>
</div>
<div class="row"><?php echo $paging_content; ?></div>
<form id="excel-hidden-form" method="GET" action="<?php echo site_url('/item/single_item'); ?>">
    <input type="hidden" name="channel" value="<?php echo $channel_id;?>">
    <input type="hidden" class="form-control" name="upc" value="<?php echo $upc;?>" >
	<input type="hidden" class="form-control" name="channel_item_code" value="<?php echo $channel_item_code;?>" >
	<input type="hidden" class="form-control" name="vcode" value="<?php echo $vcode;?>" >
    <input type="hidden" class="form-control" name="brand" value="<?php echo $brand;?>">
    <input type="hidden" name="excel" value="Y"/>
</form>
<form id="error-excel-hidden-form" method="GET" action="<?php echo site_url('/item/single_item'); ?>">
    <input type="hidden" name="excel" value="Y"/>
    <input type="hidden" name="need_update" value="E"/>
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
    
    function downloadOrderExcel() {
        $("#excel-hidden-form").submit();
    }
    function errorItemDownload() {
        $("#error-excel-hidden-form").submit();
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

    function infoSync() {

        // 매핑 정보가 있는 경우
        if($('#infoForm').length > 0){
            var vcode	= $('#virtual_item_id').val();

            console.log(vcode);

            if(vcode==""){
                alert("VCODE를 입력해주셔야 합니다.");
                return false;
            }


            var formData	= $('form').serializeArray();
            $.ajax({
                'url': '<?php echo site_url("item/single_item/infoUpdateSync"); ?>',
                'type': 'post',
                'data': formData,
                'dataType': 'json',
                'success': function (json) {
                    if(json.result == 'ok'){
                        alert("ok\n매핑변경이 완료되었습니다.");
                        location.reload();
                    }else{
                        alert(json.result + "\n" + json.msg);
                    }

                },
                error:function(request,status,error){
                    alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
                    console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
                }
            });

        }
    }

    function priceSync() {
	
		// 가격 정보가 있는 경우
		if($('#priceForm').length > 0){
			var basePrice		= parseInt($('#basic_price').val(), 10);
			var discountType	= $('#discount_type').val();
			var discountPrice	= parseInt($('#discount_value').val(), 10);
			
			if(basePrice < 1000) {
				alert('기본가격 입력은 필수입니다.');
				return false;
			}
			if(discountType == 'Rate') {
				if(discountPrice < 1) {
					alert('할인방식을 설정하면 내역을 정해주십시오.');
					return false;
				}
				if(discountPrice > 50) {
					alert('비율 할인은 50%를 넘을 수 없습니다.');
					return false;
				}
			}
			if(discountType == 'Money') {
				if(discountPrice < 100) {
					alert('정가 할인은 100원 이상부터 설정 가능합니다.');
					return false;
				}
			}
		}

		var formData	= $('form').serializeArray();
		$.ajax({
			'url': '<?php echo site_url("item/single_item/priceUpdateSync"); ?>',
			'type': 'post',
			'data': formData,
			'dataType': 'json',
			'success': function (json) {
				console.log(json);
				if(json.result == 'Success'){
					alert("처리가 완료되었습니다.");
                    location.reload();
				}else{
					alert(json.result + "\n" + json.msg);
				}

			},
			error:function(request,status,error){
				alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
				console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
			}
		});
	}

    function deleteItem() {
        if($(".list-checkbox:checked").length < 1){
            alert("매핑삭제할 상품을 선택하세요.");
            return false;
        }

        if(confirm("선택하신 " + $(".list-checkbox:checked").length + "개의 상품을 매핑삭제 하시겠습니까?")){

            $.ajax({
                type: "POST",
                async: false,
                dataType: "json",
                url: "<?php echo site_url('item/single_item/deleteSingleItem'); ?>",
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
	
</script>
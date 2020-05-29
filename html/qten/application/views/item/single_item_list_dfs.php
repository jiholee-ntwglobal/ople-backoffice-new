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
<div class="alert alert-danger">※ 검색하실 경우 엑셀 다운로드 버튼이 노출됩니다.</div>
<form action="<?php echo site_url('/item/single_item'); ?>" id="frm" class="form-inline text-right" method="get">
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
	
<?php /* } else { ?>
    <div class="form-group">
        <label>채널</label>
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
        <label>등록날짜</label>
        <select class="form-control" name="date" onchange="search_data();">
            <option value="">전체</option>
            <?php
            foreach ($create_date_arr as $history_date){
                $select = element('create_date',$history_date) == $search_date ? 'selected' : '';
                echo sprintf($option_tpl, element('create_date',$history_date), $select, element('create_date',$history_date));
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label>브랜드</label>
        <input type="text" class="form-control" name="brand" value="<?php echo $brand;?>">
    </div>
    <div class="form-group">
        <label>UPC</label>
        <textarea name="upc" class="form-control"><?php echo $upc;?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">검색</button>
    <?php if($search_date){?>
        <button class="btn btn-success" type="button" onclick="downloadOrderExcel()">엑셀다운로드</button>
    <?php }?>
<?php } */ ?>

</form>
<div class="row" style="font-size:10px;">
    <?php echo $total_count."건";?>
</div>
<div class="row">
    <div class="table-responsive">
        <table class="table" style="font-size:10px;">
            <thead>
            <tr>
                <th>채널</th>
                <th>상품코드</th>
                <th>VCODE</th>
                <th>상품갯수</th>
                <th>브랜드</th>
                <th>상품명</th>
                <th>가격</th>
                <th>판매자 할인</th>
                <th>판매자 할인단위</th>
                <th>로케이션</th>
                <th>등록날짜</th>
				<th>btn</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list_datas as $value){
                $bg_color = "";
                $bg_color = (element('channel_id',$value,'')=="1")  ? "warning" :"success";
                if(element('discount_unit',$value,'')=="N" || element('discount_unit',$value,'')=="") $discount_type = "할인 없음" ;
                if(element('discount_unit',$value,'')=="Rate") $discount_type = "비율로 할인" ;
                if(element('discount_unit',$value,'')=="Money") $discount_type = "금액으로 할인" ;
                ?>
                <tr class = "<?php echo $bg_color;?>">
                    <td><?php echo element('comment',$value,'');?></td>
                    <td>
						<a href="<?php echo site_url('item/channel_item/openChannelUrl/' . element('channel_id', $value). '/' . element('channel_item_code', $value)); ?>" target="_blank">
							<?php echo element('channel_item_code',$value,'');?>
						</a>
					</td>
                    <td><?php echo element('virtual_item_id',$value,'')==''?'':"V".str_pad(element('virtual_item_id', $value),"8","0",STR_PAD_LEFT);?></td>
                    <td><?php echo element('item_alias',$value,'');?></td>
                    <td><?php echo element('mfgname',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('item_name',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('upload_price',$value,'');?></td>
					<td><?php echo element('discount_price',$value,'');?></td>
					<td><?php echo $discount_type;?></td>
                    <td><?php echo element('location',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('create_date',$value,'');?></td>
					<td>
						<button type="button" class="btn btn-info" onclick="return loadPriceForm(<?php echo element('item_info_id',$value,'');?>);">
							가격조정
						</button>
					</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
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

<!-- Modal Start -->
<div class="modal fade" id="price-form-modal" tabindex="-1" role="dialog" aria-labelledby="price-form-label">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content" id="price-form-content">
		</div>
	</div>
</div>
<!-- Modal End -->

<script>
    function search_data() {
        $('#frm').submit();
    }
    
    function downloadOrderExcel() {
        $("#excel-hidden-form").submit();
    }
    
	function loadPriceForm(infomationID) {
		var loadUrl	= "<?php echo site_url('item/single_item/priceForm'); ?>/" + infomationID;
		
		$("#price-form-content").empty().append('<div id="priceForm"></div>');
		$('#priceForm').load(loadUrl);
		$("#price-form-modal").modal("show");
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
	
</script>
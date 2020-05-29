<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-25
 * File: option_upload_form.php
 */
//site_url('item/item_option_manage/excelDataUpload')
?>
<!-- 가격 변경 폼 -->
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4>가격 변경 폼</h4>
			</div>
			<div class="panel-body">
				<form role="form" method="post" action="" id="priceUploadForm">
					<input type="hidden" name="item_info_id" value="<?php echo $item_info_id; ?>"/>
					<input type="hidden" name="" value="" />
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label for="channel_item_code">오픈마켓 상품코드</label>
								<input type="text" class="form-control" name="channel_item_code" id="channel_item_code" value="<?php echo $channel_item_code; ?>" readonly/>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="">상품 기본가격</label>
								<input type="text" class="form-control" name="basic_price" id="basic_price" value="<?php echo $upload_price; ?>"/>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="">판매자 할인단위</label>
								<select class="form-control" name="discount_type" id="discount_type">
									<option value="N" <?php echo $discount_unit=='N' ? 'selected':''; ?>>할인 없음</option>
									<option value="Rate" <?php echo $discount_unit=='Rate' ? 'selected':''; ?>>비율로 할인</option>
									<option value="Money" <?php echo $discount_unit=='Money' ? 'selected':''; ?>>금액으로 할인</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="">판매자 할인</label>
								<input type="text" class="form-control" name="discount_value" id=="discount_value" value="<?php echo $discount_price; ?>"/>
							</div>
						</div>
					</div>
					<div class="text-center">
						<button class="btn btn-success btn-block" type="button" onclick="return priceSync();"> 업로드</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- 가격 변경 폼 -->
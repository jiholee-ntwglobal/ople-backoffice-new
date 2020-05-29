<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-25
 * File: option_upload_form.php
 */

?>

<div class="row">
	<div class="col-md-4">
		<div class="form-group">
			<label for="">상품 기본가격</label>
			<input type="text" class="form-control" name="basic_price" value=""/>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label for="">판매자 할인단위</label>
			<select class="form-control" name="discount_type">
				<option value="N">할인 없음</option>
				<option value="Rate">비율로 할인</option>
				<option value="Money">금액으로 할인</option>
			</select>
		</div>
	</div>
	<div class="col-md-4">
		<div class="form-group">
			<label for="">판매자 할인</label>
			<input type="text" class="form-control" name="discount_value" value=""/>
		</div>
	</div>
</div>
<!-- 엑셀 등록 폼 -->
<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-25
 * File: option_upload_form.php
 */
//$('#btn-test').parents('.form-group:first').append('<div>test</div>')
$option_tpl = '<option value="%s">%s</option>';
if($type=='N'){
	$option_txt		= '선택옵션';
	$sample_file	= 'option_selection_sample.xlsx';
	$btn = "<button class='btn btn-default btn-lg' type='button' onclick=\"location.href='".site_url('item/item_option_manage/downloadDataExcel/selection/').$channel_item_code."'\">엑셀다운</button>";
}else{
	$option_txt		= '추가구성';
	$sample_file	= 'option_addition_sample.xlsx';
    $btn = "<button class='btn btn-default btn-lg' type='button' onclick=\"location.href='".site_url('item/item_option_manage/downloadDataExcel/addition/').$channel_item_code."'\">엑셀다운</button>";
}

?>
<!-- 엑셀 등록 폼 -->
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4><?php echo $option_txt; ?> 파일 등록</h4>
			</div>
			<div class="panel-body">
				<form role="form" method="post" action="<?php echo site_url('item/item_option_manage/excelDataUpload') ?>" enctype="multipart/form-data">
					<input type="hidden" name="add_item_fg" value="<?php echo $type;?>"/>
                    <input type="hidden" name="search_type" value="<?php echo $search_type?>"/>
                    <input type="hidden" name="search_value" value="<?php echo $search_value?>"/>
					<div class="form-group">
						<label for="channel_id">채널 선택</label>
						<select class="form-control" name="channel_id" id="channel_id">
							<?php
							foreach ($channel_arr as $current_channel_id => $channel){
								echo sprintf($option_tpl, $current_channel_id, element('comment', $channel));
							}
							?>
						</select>
					</div>
                    <div class="form-group">
                        <label for="channel_id">상품명</label>
                        <input type="text" name="item_name" id="item_name" value="<?php echo $item_name; ?>" class="form-control">
                    </div>
					<div class="form-group">
						<label for="channel_item_code">오픈마켓 상품코드</label>
						<div class="input-group">
							<input type="text" class="form-control" name="channel_item_code" id="channel_item_code" value="<?php echo $channel_item_code;?>"/>
							<div class="input-group-btn">
								<button class="btn btn-primary" type="button" onclick="#">옵션 내용 체크</button>
							</div>
						</div>
					</div>
					
<!--					<div class="form-group">-->
<!--						<label for="channel_item_code">오픈마켓 상품코드</label>-->
<!--						<div class="input-group">-->
<!--							<input type="hidden" class="form-control" name="use_price" value="N"/>-->
<!--							<button class="btn btn-primary" type="button" onclick="return addPriceForm(this);" id="btn-test">메인 가격 조정</button>-->
<!--						</div>-->
<!--					</div>-->
					
					<!--					<div class="form-group">-->
<!--						<label for="add_item_fg">옵션분류(일반/추가구성)</label>-->
<!--						<select class="form-control" name="add_item_fg" id="add_item_fg">-->
<!--							<option value="N">일반옵션</option>-->
<!--							<option value="Y">추가구성</option>-->
<!--						</select>-->
<!--					</div>-->
<!--					<div class="form-group">-->
<!--						<div class="input-group">-->
<!--							<label class="input-group-addon">파일</label>-->
<!--							<input class="form-control" type="file" name="excel">-->
<!--							<div class="input-group-btn">-->
<!--								<button class="btn btn-success " type="submit"> 업로드</button>-->
<!--							</div>-->
<!--						</div>-->
<!--					</div>-->
					<div class="form-group">
						<label for="excel">
							옵션 설정 파일
						</label>
						<input type="file" class="form-control-file" name="excel" id="excel" />
						<p class="help-block">
							ESM에서 다운받은 경우 새로운 문서로 생성해주세요.
						</p>
					</div>
					<div class="text-center">
						<button class="btn btn-success btn-lg" type="submit"> 업로드</button>
                        <?php if($channel_item_code != ""){ echo $btn; }?>
						<button class="btn btn-info btn-lg" type="button" onclick="location.href='<?php echo site_url('file/option_item/'.$sample_file) ?>';">샘플 파일 다운</button>
						
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- 엑셀 등록 폼 -->
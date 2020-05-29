<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-25
 * File: option_item_detail.php
 */
$option_tpl = '<option value="%s">%s</option>';

$search_url = ($search_type!="" && $search_value !="") ? "?search_type=".$search_type."&search_value=".$search_value : "";
?>
<!-- 사용중 옵션 확인(디테일) -->
<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading">
				<div class="btn-group pull-right">
					<a href="#" class="btn btn-primary" onclick="return syncProccess();">등록하기</a>
				</div>
				<h4>등록내용 상세보기</h4>
			</div>
			<div class="panel-body">
				<form role="form" method="post" action="">
					<input type="hidden" name="history_id" value="<?php echo $option_history_id; ?>" />
					<div class="form-group">
						<label for="option_load">기존 옵션 선택사항</label>
						<select class="form-control" name="option_load" id="option_load">
							<?php
							// $option_load 'N'=기존옵션 모두 사용안함, 'A'=추가구성 재사용, 'S'=선택옵션 재사용option_load
							foreach ($type_arr as $type){
								if($type=='N'){
									echo sprintf($option_tpl, '', '추가구성 처리내용은 선택하여 주세요');
									echo sprintf($option_tpl, 'A', '추가구성 유지');
									echo sprintf($option_tpl, 'N', '추가구성 삭제');
								}else{
									echo sprintf($option_tpl, 'O', '추가구성 등록의 경우 선택사항이 없습니다.');
								}
							}
							?>
						</select>
					</div>
				</form>
			</div>
			<div class="row">
				<div class="col-md-10">
					<table class="table">
						<thead>
						<tr class="info">
							<th>채널</th>
							<th>account</th>
							<th>상품번호</th>
                            <th>상품명</th>
						</tr>
						</thead>
						<tbody>
							<tr class="info">
								<td><?php echo element('comment',$channel_info,''); ?></td>
								<td><?php echo element('account_id',$channel_info,''); ?></td>
								<td><?php echo element('channel_item_code',$item_data,''); ?></td>
                                <td><?php echo element('item_name', $item_description_data,'')?></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-md-2" id="btnArea">
					<!--<button class="btn btn-lg btn-block" type="button" onclick="loadPriceForm()">가격정보입력</button>-->

					<!-- @option_history_20200108 -->
					<div class="col-md-12">
						<button class="btn btn-success btn-block" type="button" onclick="location.href='<?=site_url('/item/item_option_manage/optionItemHistoryExcel/'.$option_history_id);?>';">
							기존 옵션값 비교<br>엑셀 다운로드
						</button>
					</div>
				</div>
			</div>
			
			<!-- Table -->
			<table class="table">
				<thead>
					<tr>
						<th>옵션타입</th>
						<th>분류명</th>
						<th>옵션명</th>
						<th>가격</th>
						<th>재고</th>
						<th>Vcode</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($list_data as $option){
					if(element('additem_fg',$option,'')=='Y'){
						$line_class		= 'class="warning"';
						$option_type	= '추가구성';
					}else{
						$line_class		= 'class="success"';
						$option_type	= '선택옵션';
					}
					?>
					<tr <?php echo $line_class; ?>>
						<td><?php echo $option_type; ?></td>
						<td><?php echo element('section',$option,''); ?></td>
						<td><?php echo element('option_name',$option,''); ?></td>
						<td><?php echo element('price',$option,''); ?></td>
						<td><?php echo element('stock_qty',$option,''); ?></td>
						<td><?php echo element('virtual_item_id',$option,'')==''?'':"V".str_pad(element('virtual_item_id', $option),"8","0",STR_PAD_LEFT); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="modal fade" id="file-form-modal" tabindex="-1" role="dialog" aria-labelledby="file-form-label">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content" id="file-form-content">
		</div>
	</div>
</div>

<script>
	function syncProccess() {
		/*optionUpdate*/
		if($('#option_load').val() == ''){
			alert('기존옵션 관련 설정을 선택해주세요');
			return false;
		}
		// 가격 정보가 있는 경우
		if($('#priceForm').length > 0){
			if($('input[name=basic_price]').val() == '' || $('#option_load').val() == '0') {
				alert('기본가격 입력은 필수입니다.');
				return false;
			}
			if($('select[name=discount_type]').val() != 'N' && $('input[name=discount_value]').val() == '') {
				alert('할인방식을 설정하면 내역을 정해주십시오.');
				return false;
			}
		}
		
		// var myjson = {};
		// $.each($('form').serializeArray(), function() {
		// 	if (myjson[this.name]) {
		// 		if (!myjson[this.name].push) {
		// 			myjson[this.name] = [myjson[this.name]];
		// 		}
		// 		myjson[this.name].push(this.value || '');
		// 	} else {
		// 		myjson[this.name] = this.value || '';
		// 	}
		// });
		// console.log(myjson);
		// console.log(JSON.stringify(myjson));
		//
		var formData	= $('form').serializeArray();
		$.ajax({
			'url': '<?php echo site_url("item/item_option_manage/optionupdate"); ?>',
			'type': 'post',
			'data': formData,
			'dataType': 'json',
			'success': function (json) {
				console.log(json);
				if(json.result == 'Success'){
					alert("처리가 완료되었습니다.");
					location.href="<?php echo site_url('item/item_option_manage'.$search_url); ?>";
				}else{
					alert(json.result + "\n" + json.msg);
				}
				
			},
			error:function(request,status,error){
                console.log("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
				alert("code:"+request.status+"\n"+"message:"+request.responseText+"\n"+"error:"+error);
			}
		});
	}

	function loadPriceForm() {
		var loadUrl	= "<?php echo site_url('item/item_option_manage/priceForm'); ?>";
		var newBtn	= '<button id="delFormBtn" class="btn btn-lg btn-block" type="button" onclick="delPriceForm()">가격 지우기</button>';
		
		$('form').append('<div id="priceForm"></div>');
		$('#priceForm').load(loadUrl);
		$('#btnArea').empty().append(newBtn);
		
		// $('form').append(loadUrl);
		// $("#file-form-content").empty().load(loadUrl);
		// $("#file-form-modal").modal("show");
	}
	
	function delPriceForm() {
		var newBtn	= '<button id="loadFormBtn" class="btn btn-lg btn-block" type="button" onclick="loadPriceForm()">가격정보입력</button>';
		$('#priceForm').remove();
		$('#btnArea').empty().append(newBtn);
	}

</script>
<!-- 사용중 옵션 확인(디테일) -->
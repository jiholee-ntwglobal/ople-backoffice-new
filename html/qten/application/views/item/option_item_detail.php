<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-25
 * File: option_item_detail.php
 */
?>
<!-- 사용중 옵션 확인(디테일) -->
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<!-- Default panel contents -->
			<div class="panel-heading">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4>옵션 상세보기</h4>
			</div>
			<div class="panel-body">
				<div class="btn-group btn-group-justified">
					<a href="#" class="btn btn-info" onclick="return loadForm('N','<?php echo element('channel_item_code',$item_data,''); ?>');"><i class="fa fa-sign-in fa-fw"></i>선택옵션</a>
					<a href="#" class="btn btn-danger" onclick="return loadForm('Y','<?php echo element('channel_item_code',$item_data,''); ?>');"><i class="fa fa-sign-in fa-fw"></i>추가구성</a>
				</div>
			<table class="table">
				<thead>
				<tr class="info">
					<th>채널</th>
					<th>account</th>
					<th>상품번호</th>
				</tr>
				</thead>
				<tbody>
					<tr class="info">
						<td><?php echo element('comment',$channel_info,''); ?></td>
						<td><?php echo element('account_id',$channel_info,''); ?></td>
						<td><?php echo element('channel_item_code',$item_data,''); ?></td>
					</tr>
				</tbody>
			</table>
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
                        <th>비고</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($list_data_result->result_array() as $option){
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
                        <td><?php
                            if(element('need_update',$option,'')=='E' && element('stock_qty',$option,'')>0) {
                                echo '자동품절해제오류';
                            }else if(element('need_update',$option,'')=='E' && element('stock_qty',$option,'')<1) {
                                echo '자동품절오류';
                            }
                            ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
		</div>
	</div>
</div>
<!-- 사용중 옵션 확인(디테일) -->
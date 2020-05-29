<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-01-14
* Time : 오후 2:40
*/
$option_tpl = '<option value="%s" %s>%s</option>';
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">단품 가격조정 히스토리</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                상품정보
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="font-size:12px;">
                    <thead>
                    <tr>
                        <th>상품코드</th>
                        <td><?php echo $channel_item_info['channel_item_code'];?></td>
                    </tr>
                    <tr>
                        <th>VCODE</th>
                        <td><?php echo "V".str_pad(element('virtual_item_id', $channel_item_info),"8","0",STR_PAD_LEFT);?></td>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                가격조정 히스토리
            </div>
        </div>
        <div class="panel-body" style="overflow-y: scroll;height:300px;">
        <?php echo '총 : '.$total_count.'건';?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" style="font-size:12px;">
                    <thead>
                    <tr>
                        <th>조정가격</th>
                        <th>할인타입</th>
                        <th>할인수치</th>
                        <th>수동/일괄</th>
                        <th>작업자</th>
                        <th>등록날짜</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <?php foreach ($list_datas as $list_data){
                        $action_fg = (element('action_fg', $list_data)==1) ? "수동조정" : "일괄조정";
                        if(element('discount_unit',$list_data,'')=="N" || element('discount_unit',$list_data,'')=="") $discount_type = "할인 없음" ;
                        if(element('discount_unit',$list_data,'')=="Rate") $discount_type = "비율로 할인" ;
                        if(element('discount_unit',$list_data,'')=="Money") $discount_type = "금액으로 할인" ;
                        ?>
                    <tr>
                        <td><?php echo element('upload_price', $list_data); ?></td>
                        <td><?php echo $discount_type; ?></td>
                        <td><?php echo element('discount_price', $list_data); ?></td>
                        <td><?php echo $action_fg ?></td>
                        <td><?php echo element(element('worker_id', $list_data), $worker); ?></td>
                        <td><?php echo element('create_date', $list_data); ?></td>
                    </tr>
                    <?php }  ?>
                    </tr>
                    </tbody>
                </table>
            </div>
        <div class="row"><?php echo $paging_content;  ?></div>
    </div>
    </div>
</div>
    <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
<div style="display:none">
    <form id="excel-hidden-form" method="GET">
        <input type="hidden" name="excel_fg" value="Y"/>
    </form>
</div>
<script type="text/javascript">
    function ExcelDownload() {
        $("#excel-hidden-form").submit();
    }
</script>


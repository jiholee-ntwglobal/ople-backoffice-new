<?php
/**
 * Created by PhpStorm.
 * User: beomsuk
 * Date: 2018-10
 * Time: 오전 11:19
 */
?>
<h4>단품 가격 업데이트 히스토리</h4>

<strong style="color: red;">※ 매시 30분에 가격 업데이트 진행(ex 05:30, 06:30) </strong><br>
<strong style="color: red;">※ 저희 관리 시스템에 등록된 단품 상품만 일괄 가격 업로드가 가능합니다</strong><br>
<strong style="color: red;">※ 품절,품절해제 처리는 가격조정 후 진행됩니다 </strong><br>

<form method="GET">

    <div class="row">

        <div class="col-md-2">
            <button type="button" data-toggle="modal" data-target="#upload-modal" class="btn btn-primary">엑셀업로드</button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-info" type="button" onclick="location.href='http://oms.ntwsec.com/qten/file/qten_item_update_price_sample.xlsx'">샘플 파일 다운</button>
        </div>
        <div class="col-md-2 form-group">
            <label>기간조회1</label>
            <input type="text" id="datepicker-from" class="form-control" name="start_dt" value="<?php echo $start_dt; ?>">
        </div>
        <div class="col-md-2 form-group">
            <label>기간조회2</label>
            <input type="text" id="datepicker-to" class="form-control" name="end_dt" value="<?php echo $end_dt; ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-success"> 검색</button>
            <button class="btn btn-success" type="button" onclick="downloadOrderExcel()">엑셀다운로드</button>
        </div>

    </div>

</form>

<?php echo '총 : '.$total_count.'건';?>
<div class="row">
            <div class="table-responsive">
                <table class="table" style="font-size:10px;">
                    <thead>
                    <tr>
                        <th>채널</th>
                        <th>아이디</th>
                        <th>상품코드</th>
                        <th>판매가</th>
                        <th>할인타입</th>
                        <th>할인수치</th>
                        <th>적용여부</th>
                        <th>적용날짜</th>
                        <th>등록날짜</th>
                        <th>작업자</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($create_date_arr as $list_data){
                        $channel_id = element( element('channel_code', $list_data) ,$channel_arr);
                        $line_class	= '';
						if(element('upload_fg', $list_data) == 3) $line_class = ' class="danger"';
                        ?>
                        <tr<?php echo $line_class; ?>>
                            <td><?php echo element('channel_code', $list_data); ?></td>
                            <td><?php echo element('account_id', $list_data); ?></td>
                            <td>
                                <a href="<?php echo site_url('item/channel_item/openChannelUrl/' . $channel_id . '/' . element('channel_item_code', $list_data)); ?>" target="_blank">
                                <?php echo element('channel_item_code', $list_data); ?>
                                </a></td>
                            <td><?php echo element('upload_price', $list_data); ?></td>
                            <td><?php echo element('discount_unit', $list_data); ?></td>
                            <td><?php echo element('discount_price', $list_data); ?></td>
                            <td><?php echo element(element('upload_fg', $list_data),$upload_fg); ?></td>
                            <td><?php echo element('upload_date', $list_data); ?></td>
                            <td><?php echo element('create_date', $list_data); ?></td>
                            <td><?php echo (element(element('worker_id', $list_data), $worker)) == '' ? '-' : element(element('worker_id', $list_data), $worker); ?></td>
                        </tr>
                    <?php }  ?>
                    </tbody>
                </table>
            </div>
        </div>
<div class="row"><?php echo $paging_content;  ?></div>

<div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog" >
        <div class="modal-content">
            <form method="POST" action="<?php echo site_url('/item/single_item/saveUpdateProductPriceExcel'); ?>" enctype="multipart/form-data" onsubmit="return chkUplodForm()">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title" id="myModalLabel">상품가 업데이트 엑셀 등록<font color="red">현재 계정 ( <?php echo $current_master_id; ?> ) </font>
                        </h4>
                </div>
                <div class="modal-body">
                    <input type="file" name="excel"/>

                    <h6>샘플 파일은 개발팀에 문의하시기 바랍니다.</h6>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">업로드</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<form id="excel-hidden-form" method="GET" >
    <input type="hidden" id="datepicker-from" class="form-control" name="start_dt" value="<?php echo $start_dt; ?>">

    <input type="hidden" id="datepicker-to" class="form-control" name="end_dt" value="<?php echo $end_dt; ?>">

    <input type="hidden" name="excel" value="Y"/>
</form>

<script type="text/javascript">

    $( function() {
        var dateFormat = "yy-mm-dd",
            from = $( "#datepicker-from" )
                .datepicker({
                    defaultDate: "+1w",
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    numberOfMonths: 1
                })
                .on( "change", function() {
                    to.datepicker( "option", "minDate", getDate( this ) );
                }),
            to = $( "#datepicker-to" ).datepicker({
                defaultDate: "+1w",
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                numberOfMonths: 1
            })
                .on( "change", function() {
                    from.datepicker( "option", "maxDate", getDate( this ) );
                });

        function getDate( element ) {
            var date;
            try {
                date = $.datepicker.parseDate( dateFormat, element.value );
            } catch( error ) {
                date = null;
            }

            return date;
        }
    } );

    $("#datepicker-from").datepicker({
        dateFormat: 'yy-mm-dd',
        onSelect : function(selectDate){
            setEdate = selectDate;
            console.log(setEdate)
        }
    });

    function chkUplodForm() {
        if($("input[name=upload_file]").val() == ""){
            alert("업로드하실 파일을 선택하세요.");
            return false;
        }
        return true;
    }

    function downloadOrderExcel() {
        $("#excel-hidden-form").submit();
    }
</script>

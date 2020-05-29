<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2019-03-08
 * Time : 오전 11:11
 */
$option_tpl = '<option value="%s" %s>%s</option>';

?>
<form role="form" method="post" action="<?php echo site_url('item/single_item/uploadSingleItem') ?>" enctype="multipart/form-data">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title" id="myModalLabel">수동 등록 상품 엑셀 업로드</h4>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="channel">채널</label>
                <select id="upload-form-channel" name="channel_id" class="form-control">
                    <?php
                    foreach ($channel_arr as $current_channel_id => $channel){
                        echo sprintf($option_tpl, $current_channel_id, null, element('comment', $channel));
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="excel_file">엑셀 파일 등록</label>
                <input id="upload-form-file" type="file" name="excel" class="form-control"/>
                <p class="help-block">
                    반드시 샘플 파일 양식에 맞춰 업로드해주세요.
                </p>
            </div>
        </div>
        <?php if($_SERVER['REMOTE_ADDR'] == "211.214.213.101")  {?>
            <div class="alert-danger">※ 본 기능은 가격조정이 되지 않고, 저희 서버에서만 매핑데이터를 만드는 기능입니다. 가격 조정을 하시려면 단품 목록에서 반드시 조정이 필요합니다.</div>
        <?php }?>
    </div>

    <div class="modal-footer">
        <button class="btn btn-success btn-lg" type="submit"> 업로드</button>
        <button class="btn btn-info btn-lg" type="button" onclick="location.href='http://oms.ntwsec.com/qten/file/single_item/newbay_single_item_sample.xlsx';">샘플 파일 다운</button>
    </div>
</form>
<script type="text/javascript">
    function chkExcelorderUploadForm() {
        if($("#upload-form-channel").val() == ""){
            alert("채널을 선택해주세요.");
            $("#upload-form-channel").focus();
            return false;
        }
        if($("#upload-form-file").val() == ""){
            alert("엑셀 파일을 선택해주세요.");
            return false;
        }
        return true;
    }
</script>

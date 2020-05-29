<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-03-11
* Time : 오전 10:53
*/
?>
<!-- 매핑 정보 변경 폼 -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <!-- Default panel contents -->
            <div class="panel-heading">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>매핑 변경 폼</h4>
            </div>
            <div class="panel-body">
                <form role="form" method="post" action="" id="infoUploadForm">
                    <input type="hidden" name="item_info_id" value="<?php echo $item_info_id; ?>"/>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="channel_item_code">오픈마켓 상품코드</label>
                                <input type="text" class="form-control" name="channel_item_code" id="channel_item_code" value="<?php echo $channel_item_code; ?>" readonly/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">VCODE</label>
                                <input type="text" class="form-control" name="virtual_item_id" id="virtual_item_id" placeholder="V00017432와 같은 형식으로 입력해주세요." value="<?php echo  "V".str_pad($virtual_item_id, 8 , 0 , STR_PAD_LEFT); ?>"/>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-success btn-block" type="button" onclick="return infoSync();"> 매핑 변경</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- 매핑 정보 변경 폼  -->

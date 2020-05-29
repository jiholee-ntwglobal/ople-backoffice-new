<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2019-03-08
* Time : 오후 2:30
*/
?>
<br/>
<div class="row">
    <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading">
            <div class="btn-group pull-right">
                <a href="<?php echo site_url('/item/single_item/'); ?>" class="btn btn-primary" onclick="">목록보기</a>
            </div>
            <h4>상품 엑셀 등록 내용 확인</h4>
        </div>
        <br/>

        <div class="table-responsive">

            <div class="col-md-10">
                <table class="table table-hover">
                    <tr class="info">
                        <td>총 등록 개수</td>
                        <td>등록 성공 개수</td>
                        <td>등록 실패 개수</td>
                    </tr>
                    </thead>
                    <tr class="info">
                        <td><?php echo $code_cnt?></td>
                        <td><?php echo count($success_id_arr); ?></td>
                        <td><?php echo count($fail_id_arr); ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="panel-body">
            <label>등록 성공 채널상품코드</label>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="font-weight:bold">
                <tr>
                    <td>채널상품코드</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($success_id_arr as $channel_product_code){ ?>
                    <tr class="success">
                        <td><?php echo $channel_product_code; ?></td>
                    </tr>
                <?php } ?>
                <?php if(count($success_id_arr) == 0) { ?>
                    <tr class="success">
                        <td colspan="2">등록된 상품코드가 없습니다.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="panel-body">
            <label>등록 실패 상품코드</label>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="font-weight:bold">
                <tr>
                    <td>채널상품코드</td>
                    <td>실패내용</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($fail_id_arr as $channel_product_code=>$msg){ ?>
                    <tr class="warning">
                        <td><?php echo $channel_product_code; ?></td>
                        <td><?php echo $msg; ?></td>
                    </tr>
                <?php } ?>
                <?php if(count($fail_id_arr) == 0) { ?>
                    <tr class="success">
                        <td rowspan="2">등록 실패된 상품코드가 없습니다.</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

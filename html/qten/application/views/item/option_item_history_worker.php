<?php
/**
 * Created by PhpStorm.
 * User: 강소진
 * Date : 2020-02-18
 * Time : 오후 2:02
 */

?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">옵션상품 수정내역</h4>
</div>

<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover" style="font-size:12px;">
            <thead>
            <tr>
                <th>처리자</th>
                <th>처리일자</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($historys as $history){ ?>
                <tr>
                    <td><?php echo element('worker_name', $history); ?></td>
                    <td><?php echo element('history_date', $history); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>

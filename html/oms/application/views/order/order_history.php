<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오후 4:39
 */?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">주문 히스토리</h4>
</div>

<div class="modal-body">
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover" style="font-size:12px;">
            <thead>
            <tr>
                <th>처리일자</th>
                <th>처리상태</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($order_historys as $order_history){ ?>
                <tr>
                    <td><?php echo element('process_date', $order_history); ?></td>
                    <td><?php echo element('status', $order_history); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
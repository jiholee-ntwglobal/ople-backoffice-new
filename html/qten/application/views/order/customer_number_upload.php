<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-30
 * Time: 오후 9:05
 */
$option_tpl = '<option value="%s" >%s</option>';
?>
<div class="row">
    <div class="col-md-12">
        <h4>통관고유부호</h4>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <h6>최근 History</h6>
        <div class="table-responsive">
            <table class="table" style="font-size:15px;">
                <thead>
                <tr>
                    <th>채널</th>
                    <th>파일명</th>
                    <th>데이터 수</th>
                    <th>업데이트 수</th>
                    <th>작업자</th>
                    <th>업로드 날짜</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($list_data_result->result_array() as $list_data) {
                    ?>
                    <tr>
                        <td><?php echo element('comment', $list_data); ?></td>
                        <td><?php echo element('upload_file_name', $list_data); ?></td>
                        <td><?php echo element('row_count', $list_data); ?></td>
                        <td><?php echo element('apply_count', $list_data); ?></td>
                        <td><?php echo element(element('worker_id', $list_data), $worker); ?></td>
                        <td><?php echo element('upload_date', $list_data); ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-4">
        <br><br><br>
        <form class="form-inline" method="post" action="<?php echo site_url('order/customer_number/save_excel') ?>"
              enctype="multipart/form-data">
            <div class="form-group">
                <select class="form-control" name="channel_id">
                    <option value="">채널 선택</option>
                    <?php
                    foreach ($channel_arr as $current_channel_id => $channel) {
                        echo sprintf($option_tpl, $current_channel_id, element('comment', $channel));
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <div class="input-group">
                    <label class="input-group-addon">파일</label>
                    <input class="form-control" type="file" name="excel">
                    <div class="input-group-btn">
                        <button class="btn btn-success " type="submit"> 업로드</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

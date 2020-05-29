<?php
/**
* Created by PhpStorm.
* User: 강소진
* Date : 2018-07-11
* Time : 오후 5:54
*/
echo $header;
$option_tpl = '<option value="%s" %s>%s</option>';
?>
<h4>옵션 품절, 품절해제 히스토리</h4>

<ul class="nav nav-pills">
    <li class="<?php if($status == '1') echo 'active'; ?>"><a href="#" onclick="location.href='<?php echo site_url('item/soldout_option_history?status=1'); ?>'" data-toggle="tab">성공</a></li>
    <li class="<?php if($status == '2') echo 'active'; ?>"><a href="#" onclick="location.href='<?php echo site_url('item/soldout_option_history?status=2'); ?>'" data-toggle="tab">실패</a></li>
</ul>

<form action="<?php echo site_url('/item/soldout_option_history'); ?>" id="frm" class="form-inline text-right" method="get">
    <input type="hidden" name="status" value="<?php echo $status; ?>">
    <div class="form-group">
        <label>채널</label>
        <select class="form-control" name="channel" onchange="search_data();">
            <option value="">전채채널</option>
            <?php
            foreach ($channel_arr as $current_channel_id => $channel){
                $select = $current_channel_id == $channel_id ? 'selected' : '';
                echo sprintf($option_tpl, $current_channel_id, $select, element('comment', $channel));
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>날짜</label>
        <select class="form-control" name="date" onchange="search_data();">
            <option value="">전체</option>
            <?php
            foreach ($history_date_arr as $history_date){
                $select = element('create_date',$history_date) == $search_date ? 'selected' : '';
                echo sprintf($option_tpl, element('create_date',$history_date), $select, element('create_date',$history_date));
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label>품절여부</label>
        <select class="form-control" name="soldout_fg" onchange="search_data();">
            <option value="">전체</option>
            <option value="N" <?php echo $soldout_fg =='N' ? 'selected' :''; ?>>품절</option>
            <option value="Y" <?php echo $soldout_fg =='Y' ? 'selected' :''; ?>>품절해제</option>
        </select>
    </div>
    <div class="form-group">
        <label>UPC</label>
        <input type="text" class="form-control" name="upc" value="<?php echo $upc;?>" >
    </div>
    <div class="form-group">
        <label>VCODE</label>
        <input type="text" class="form-control" name="virtual_item_id" value="<?php echo $virtual_item_id;?>" >
    </div>
    <div class="form-group">
        <label>상품코드</label>
        <input type="text" class="form-control" name="channel_item_code" value="<?php echo $channel_item_code;?>" >
    </div>
    <div class="form-group">
        <label>브랜드</label>
        <input type="text" class="form-control" name="brand" value="<?php echo $brand;?>">
    </div>
    <button type="submit" class="btn btn-primary">검색</button>
    <button class="btn btn-success" type="button" onclick="downloadOrderExcel()">엑셀다운로드</button>

</form>


<div class="row" style="font-size:10px;">
    <?php echo $total_count."건";?>
</div>
<div class="row">
    <div class="table-responsive">
        <table class="table" style="font-size:10px;">
            <thead>
            <tr>
                <th>채널</th>
                <th>SECTION</th>
                <th>옵션명</th>
                <th>상품코드</th>
                <th>VCODE</th>
                <th>상품갯수</th>
                <th>브랜드</th>
                <th>상품명</th>
                <th>로케이션</th>
                <th>품절여부</th>
                <!--<th>사용여부</th>-->
                <th>비고</th>
                <th>작업자</th>
                <th>작업날짜</th>
                <?php if($status == '2'){?>
                    <th>리턴메세지</th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list_datas as $value){ ?>
                <tr>
                    <td><?php echo element('comment',$value,'');?></td>
                    <td><?php echo element('section',$value,'');?></td>
                    <td><?php echo element('option_name',$value,'');?></td>
                    <td><?php echo element('channel_item_code',$value,'');?></td>
                    <td><?php echo element('virtual_item_id',$value,'')==''?'':"V".str_pad(element('virtual_item_id', $value),"8","0",STR_PAD_LEFT);?></td>
                    <td><?php echo element('item_alias',$value,'');?></td>
                    <td><?php echo element('mfgname',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('item_name',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('location',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('stock_status',$value,'')=='N'? '품절' : '품절해제';?></td>
                    <!--<td><?php /*echo element('regist_fg',$value,'')=="2"? "사용" : "사용중지"; */?></td>-->
                    <td><?php echo element(element('soldout_process_type',$value,''),$soldout_process_type);?></td>
                    <td><?php echo element(element('process_worker_id',$value,''),$worker_arr,'시스템');?></td>
                    <td><?php echo element('create_date',$value,'');?></td>
                    <?php if($status == '2'){?>
                        <td><?php echo element('error_message',$value,'');?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="row"><?php echo $paging_content; ?></div>

<form id="excel-hidden-form" method="GET" action="<?php echo site_url('/item/soldout_option_history'); ?>">
    <input type="hidden" name="status" value="<?php echo $status; ?>">
    <input type="hidden" name="channel" value="<?php echo $channel_id;?>">
    <input type="hidden" name="date" value="<?php echo $search_date;?>">
    <input type="hidden" name="soldout_fg" value="<?php echo $soldout_fg;?>">
    <input type="hidden" class="form-control" name="upc" value="<?php echo $upc;?>" >
    <input type="hidden" class="form-control" name="brand" value="<?php echo $brand;?>">
    <input type="hidden" name="excel" value="Y"/>
</form>
<script>
    function search_data() {
        $('#frm').submit();
    }
    function downloadOrderExcel() {

        $("#excel-hidden-form").submit();
    }
</script>
<?php echo $footer; ?>

<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-06-25
 * File: option_item_list.php
 */

?>
    <!-- 옵션상품 리스트 페이지 -->
    <div class="row">
        <div class="col-md-12" style="margin:20px 0;">
            <form action="<?php echo site_url('/item/item_option_manage'); ?>" id="frm" class="form-inline" method="get" style="display:inline-flex;">
                <input type="hidden" name="channel_id" value="">
                <input type="hidden" name="channel_code" value="">
                <div class="form-group" style="padding-right:5px;">
                    <select class="form-control" name="search_type">
                        <option value="channel_item_code" <?php if($search_type == "channel_item_code") echo "selected"; ?>>상품코드</option>
                        <option value="upc" <?php if($search_type == 'upc') echo 'selected'; ?>>UPC</option>
                        <option value="virtual_item_id" <?php if($search_type == 'virtual_item_id') echo 'selected'; ?>>VCODE</option>
                    </select>
                </div>
                <div class="form-group" style="padding-right:5px;">
                    <textarea name="search_value" class="form-control" placeholder="구성상품 조회값"><?php echo $search_value; ?></textarea>
                </div>
                <div class="form-group" style="padding-right:5px;">
                    <button type="submit" class="btn btn-primary">검색</button>
                </div>
                <div class="form-group" style="padding-right:5px;">
                    <button type="button" class="btn btn-info" type="button" onclick="loadForm('N','newinsert');" title="선택옵션 파일 등록">
                        <i class="fa fa-sign-in fa-fw"></i> 선택옵션 파일 등록
                    </button>

                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-success" type="button" onclick="downloadOptionListExcel();">엑셀 다운로드</button>
                </div>
            </form>
        </div>

        <div class="col-md-12">
            <ul class="nav nav-tabs">
                <li>
                    <a href="#"><?php echo number_format($total_count)."건";?></a>
                </li>
                <li class="nav-item <?php if($channel_code=="") echo "active"; ?>">
                    <a href="#" data-toggle="tab" onclick="loadTap();">전체</a>
                </li>
                <li class="nav-item <?php if($channel_code=="G") echo "active"; ?>">
                    <a href="#" data-toggle="tab" onclick="loadTap('G');">G마켓</a>
                </li>
                <li class="nav-item <?php if($channel_code=="A") echo "active"; ?>">
                    <a href="#" data-toggle="tab" onclick="loadTap('A');">옥션</a>
                </li>
            </ul>
            <div class="table-responsive">
                <table class="table table-striped">
                    <colgroup>
                        <col width="140">
                        <col width="160">
                        <col>
                        <col width="200">
                        <col width="200">
                        <col width="400">
                    </colgroup>
                    <thead>
                    <tr>
                        <th>채널</th>
                        <th>상품코드</th>
                        <th>상품명</th>
                        <th>선택옵션 (선택갯수)</th>
                        <th>추가구성</th>
                        <th width="10%">비고</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list_data_result->result_array() as $value){ /*if($_SERVER['REMOTE_ADDR']=='211.214.213.101'){echo "<pre>"; var_dump($value); echo "</pre>";}*/ ?>
                        <tr>
                            <td><?php echo element('comment',element(element('channel_id', $value, ''),$channel_arr));?></td>
                            <td>
                                <a href="<?php echo site_url('item/channel_item/openChannelUrl/' . element('channel_id', $value). '/' . rtrim(element('channel_item_code', $value))); ?>" target="_blank">
                                    <?php echo element('channel_item_code',$value,'');?>
                                </a>
                            </td>
                            <td><?php echo element('item_name',$value,'');?></td>
                            <td><?php echo element('selection_cnt',$value,''); ?> 개 ( <?php echo element('selections',$value,''); ?> )</td>
                            <td><?php echo element('addition_cnt',$value,'');?> 개</td>
                            <td><?php echo element('need_update',$value,'')=='E'?'자동품절,품절해제오류':'';?></td>
                            <td>
                                <button class="btn btn-info btn-xs" type="button" onclick="loadForm('N','<?php echo element('channel_item_code',$value,'');?>');"><i class="fa fa-sign-in fa-fw"></i>선택옵션</button>
                                <button class="btn btn-warning btn-xs" type="button" onclick="loadForm('Y','<?php echo element('channel_item_code',$value,'');?>');"><i class="fa fa-sign-in fa-fw"></i>추가구성</button>
                                <button class="btn btn-info btn-xs" type="button" onclick="loadDetail('<?php echo element('channel_item_code',$value,'');?>');" title="옵션 상세보기" alt="옵션 상세보기"><i class="fa fa-eye fa-fw"></i></button>
                                <button class="btn btn-info btn-xs" type="button" onclick="OptionDescription('<?php echo element('channel_item_code',$value,'');?>');" title="상품명 수정" alt="상품명 수정"><i class="fa fa-edit fa-fw"></i></button>
                                <button class="btn btn-danger btn-xs btn-item-delete-request" type="button" data-code="<?php echo element('channel_item_code',$value,'');?>"  title="상품 삭제" alt="상품 삭제"><i class="fa fa-trash fa-fw"</button>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="paging text-center">
        <?php echo $paging_content; ?>
    </div>
    <!-- 옵션상품 리스트 페이지 -->

    <!-- 대기 옵션 확인(디테일2) -->
    <!-- 일단은 위와 동일 -->
    <div class="row">
        <div class="col-md-12">

        </div>
    </div>
    <!-- 대기 옵션 확인(디테일2) -->

    <!-- 변경 히스토리 -->
    <div class="row">
        <div class="col-md-12">

        </div>
    </div>
    <!-- 변경 히스토리 -->

    <!-- Modal -->
    <div class="modal fade" id="file-form-modal" tabindex="-1" role="dialog" aria-labelledby="file-form-label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" id="file-form-content">
            </div>
        </div>
    </div>
    <form id="excel-hidden-form" method="GET" action="<?php echo site_url('/item/item_option_manage'); ?>">
        <input type="hidden" name="channel_code" value="<?php echo $channel_code;?>">
        <input type="hidden" name="search_type" value="<?php echo $search_type;?>" >
        <input type="hidden" name="search_value" value="<?php echo $search_value;?>" >
        <input type="hidden" name="excel_fg" value="Y"/>
    </form>
    <style>
        ul.nav-tabs > li:first-child > a:hover {
            background-color: #fff;
            border-top: 1px solid #fff;
            border-right: 1px solid #fff;
            border-left: 1px solid #fff;
            cursor: default;
        }
        table.table>tbody>tr>td, table.table>tbody>tr>th, table.table>tbody>tr>td {
            vertical-align: middle;
            border-bottom: 1px solid #ddd;
            border-top: 0;
        }
        .table-striped>tbody>tr:nth-of-type(odd):hover {
            background-color: #d0e9c6;
        }
        .table-striped>tbody>tr:nth-of-type(even):hover {
            background-color: #efefef;
        }
    </style>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.4/css/bootstrap-dialog.min.css" rel="stylesheet" />
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.4/js/bootstrap-dialog.min.js"></script>
    <script>
        $(function() {
            $('ul.nav-tabs > li:first-child > a').on('click', function(e) {
                e.preventDefault();
            });

            $('button.btn-item-delete-request').on('click', function() {
                // var item_code = $.trim($(this).parent().parent().children('td').eq(1).text());
                var channel_item_code = $(this).data('code');
                var page = channel_item_code;
                var title = '옵션상품 삭1제';
                modal_layer_open(page, title, 'normal');
            });
        });

        function loadTap(val){
            $("#frm input[name=channel_code]").val(val);
            $("#frm").submit();
        }
        function loadForm(fg, itemcode) {
            var loadUrl	= "<?php echo site_url('item/item_option_manage/uploadForm'); ?>/" + fg + "/" +itemcode + "/<?php echo trim($search_type); ?>/<?php echo urlencode(trim($search_value)); ?>";
            $("#file-form-content").empty().load(loadUrl);
            $("#file-form-modal").modal("show");
        }

        function loadDetail(itemcode) {
            var loadUrl	= "<?php echo site_url('item/item_option_manage/itemDetail'); ?>/" + itemcode;
            $("#file-form-content").empty().load(loadUrl);
            $("#file-form-modal").modal("show");
        }
        function OptionDescription(itemcode){
            var loadUrl = "<?php echo site_url('item/item_option_manage/OptionDescription'); ?>/" + itemcode;
            $("#file-form-content").empty().load(loadUrl);
            $("#file-form-modal").modal("show");
        }

        function modal_layer_open(page, title, size)
        {
            size = (!size || size == 'undefined') ? 'wide' : '';
//		var btns = new Array;
//		var btn3 = {
//			label:"취소",
//			action:function(dialogItself){
//				dialogItself.close();
//			}
//		};
//		var btn2 = {
//			label:"저장",
//			cssClass: 'btn-primary',
//			action:function(){
//				alert("준비중입니다.");
//			}
//		};
//		btns[btns.length] = btn2;
//		btns[btns.length] = btn3;

            var page_url = "<?php echo site_url('item/item_option_manage/item_delete/')?>" + page;
            var dialog = BootstrapDialog.show({
                type: BootstrapDialog.TYPE_DANGER,
                size: ((size == 'wide') ? BootstrapDialog.SIZE_WIDE : BootstrapDialog.SIZE_NORMAL),
                closable: true,
                closeByBackdrop: false,
                closeByKeyboard: false,
                title: title,
//			buttons: btns,
                message: function(dialog) {
                    var $message = $('<div>Data Loading...</div>');
                    var pageToLoad = dialog.getData('pageToLoad');

                    console.log(pageToLoad);
                    $message.load(pageToLoad);
                    return $message;
                },
                data: {
                    'pageToLoad': page_url
                }
            });

//		dialog.getModalHeader().hide();
            dialog.getModalFooter().hide();
        }

        function downloadOptionListExcel() {
            $("#excel-hidden-form").submit();
        }
    </script>

<?php
/*

<div class="row">
    <div class="col-md-12">
        <h4>옵션 상품 리스트</h4>
    </div>
</div>
<form action="<?php echo site_url('/item/single_item'); ?>" id="frm" class="form-inline text-right" method="get">
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
        <label>등록날짜</label>
        <select class="form-control" name="date" onchange="search_data();">
            <option value="">전체</option>
            <?php
            foreach ($create_date_arr as $history_date){
                $select = element('create_date',$history_date) == $search_date ? 'selected' : '';
                echo sprintf($option_tpl, element('create_date',$history_date), $select, element('create_date',$history_date));
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label>브랜드</label>
        <input type="text" class="form-control" name="brand" value="<?php echo $brand;?>">
    </div>
    <div class="form-group">
        <label>UPC</label>
        <textarea name="upc" class="form-control"><?php echo $upc;?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">검색</button>
    <?php if($search_date){?>
    <button class="btn btn-success" type="button" onclick="downloadOrderExcel()">엑셀다운로드</button>
    <?php }?>

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
                <th>상품코드</th>
                <th>VCODE</th>
                <th>상품갯수</th>
                <th>브랜드</th>
                <th>상품명</th>
                <th>등록전 가격</th>
                <th>등록후 가격</th>
                <th>로케이션</th>
                <th>등록날짜</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list_datas as $value){?>
                <tr>
                    <td><?php echo element('comment',$value,'');?></td>
                    <td><?php echo element('channel_item_code',$value,'');?></td>
                    <td><?php echo element('virtual_item_id',$value,'')==''?'':"V".str_pad(element('virtual_item_id', $value),"8","0",STR_PAD_LEFT);?></td>
                    <td><?php echo element('item_alias',$value,'');?></td>
                    <td><?php echo element('mfgname',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('item_name',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('origin_price',$value,'');?></td>
                    <td><?php echo element('upload_price',$value,'');?></td>
                    <td><?php echo element('location',$master_item_arr[element('master_item_id',$value,'')]) ;?></td>
                    <td><?php echo element('create_date',$value,'');?></td>

                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<div class="row"><?php echo $paging_content; ?></div>
<form id="excel-hidden-form" method="GET" action="<?php echo site_url('/item/single_item'); ?>">
    <input type="hidden" name="channel" value="<?php echo $channel_id;?>">
    <input type="hidden" name="date" value="<?php echo $search_date;?>">
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
*/
?>
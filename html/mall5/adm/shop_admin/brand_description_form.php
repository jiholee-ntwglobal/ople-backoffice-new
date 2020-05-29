<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-06-14
 * Time: 오후 6:58
 */
$sub_menu = "600971";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");


$_GET['uid'] = trim($_GET['uid']) ? trim($_GET['uid']) : '';
$banner_data = array();
if ($_GET['uid']) {
    $banner_data = sql_fetch("
	select
		*
	from
		brand_description
	where
		uid = '" . sql_safe_query($_GET['uid']) . "'
");
# 시작일 #
    $st_date = $banner_data['start_date'];
# 종료일 #
    $en_date = $banner_data['end_date'];
}
$g4[title] = "브랜드별 배너 관리";
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="panel panel-default">
    <div class="row">
        <div class="col-md-12">
            <h4 class="panel-heading">브랜드 배너 생성 및 수정</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-inline text-right panel-heading">
                <div class="form-group">
                    <label>브랜드명</label>
                    <input type="text" class="form-control" name="name"
                           value="<?php echo htmlspecialchars($banner_data['it_maker']); ?>">
                </div>
                <button class="btn btn-primary" type="button" onclick="brand_search_datas();">검색</button>
            </div>
        </div>
    </div>
    <div class="panel-body">
    <table class="table table-hover table-bordered brand_da" style="display: none;"  >
        <thead>
        <tr>
            <th>브랜드</th>
        </tr>
        </thead>
        <tbody class="search_data text-center" style="overflow-y: scroll; height:400px;" >
        </tbody>
    </table>
    </div>
    <form method="post" action="./brand_description_save.php" onsubmit=" return datacheck()">
        <input type="hidden" name='uid' value='<?= isset($banner_data['uid']) ? $banner_data['uid']:''; ?>'/>
        <input type="hidden" name='mode' value='<?= isset($banner_data['uid']) ? 'update' : 'insert'; ?>'/>
        <input type="hidden" name='brand' value="<?= isset($banner_data['it_maker']) ? $banner_data['it_maker'] : ''; ?>"/>
        <div class="row">
            <div class="col-md-12">
                <div class="panel-body">
                    <table class="table table-hover table-bordered">
                        <tr>
                            <th class="text-center">브랜드</th>
                            <td class="text-center" id="brand_name"><strong><?php echo $banner_data['it_maker']; ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-center">시작일과 종료일</th>
                            <td>
                                <div class="form-group form-inline text-center">
                                    <input type="text" class="form-control" name="st_dt" id="from"
                                           value="<?php echo $st_date; ?>" readonly>~
                                    <input type="text" class="form-control" name="en_dt" id="to"
                                           value="<?php echo $en_date; ?>" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr class="info">
                            <th class="text-center" rowspan="2">PC</th>
                            <th class="text-center">배너
                                <div class="text-right"><input type="checkbox" id="fp" name="use_flag_pc" <?= isset($banner_data['use_flag_pc']) && $banner_data['use_flag_pc']=='1' ? 'checked':''; ?>>적용</div>
                            </th>
                        </tr>
                        <tr class="info">
                            <td class="text-center"><textarea class="form-control" name="it_maker_description_pc"
                                                              rows="5"><?php echo $banner_data['it_maker_description_pc']; ?></textarea>
                            </td>
                        </tr>
                        <tr class="success">
                            <th class="text-center" rowspan="2">모바일</th>
                            <th class="text-center">배너
                                <div class="text-right"><input type="checkbox" id="fm" name="use_flag_mo" <?= isset($banner_data['use_flag_mo']) && $banner_data['use_flag_mo']=='1' ? 'checked':''; ?>>적용</div>
                            </th>
                        </tr>
                        <tr class="success">
                            <td class="text-center"><textarea class="form-control" name="it_maker_description_mo"
                                                              rows="5"><?php echo $banner_data['it_maker_description_mo']; ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-right">
                                <button class="btn btn-success" type="submit" id="sumit_button"><?= isset($banner_data['uid']) ? '수정':'생성'; ?></button>
                                &nbsp;&nbsp;
                                <button class="btn btn-danger" type="button" onclick="history.back();">목록</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function datacheck() {
        var subject = $('input[name=subject]').val().trim();
        var st_dt = $('input[name=st_dt]').val().trim();
        if (subject == '') {
            alert('제목을 입력해주세요.');
            return false;
        }
        if (st_dt == '') {
            alert('시작날짜를 입력해주세요.');
            return false;
        }
        return true;
    }

    function brand_search_datas() {
        var brand = $('input[name=name]').val().trim();
        if(brand != ''){
            $.ajax({
                type: 'post'
                , url: './brand_search_data.php'
                , data: {
                    mode : 'brand_search',
                    brand : brand
                }
                , datatype: 'json'
                , success: function (data) {
                    $('.brand_da').css('display','none');
                    var data =  JSON.parse(data);
                    if(!data || data=='') {
                        alert('검색되는 브랜드가 없습니다');
                    }else {

                        var tr = '';
                        $.each(data, function (i, item) {
                            tr += "<tr>" +
                                "<td>" +
                                    "<a href='#' onclick=\"banner_data_ajax('"+escape(item.it_maker)+"')\">"+
                                item.it_maker +
                                    "</a>"+
                                "</td>" +
                                "</tr>";
                        });
                        $('.search_data').html(tr);
                        $('.brand_da').css('display', '');
                    }
                }
            });
        }else {
            $('.brand_da').css('display','none');
            alert('브랜드를 입력해주세요 ');
            $('input[name=name]').focus();
        }
    }

    function banner_data_ajax(brand) {
        var brand = brand;
        $('#sumit_button').html('생성');
        $('input[name=uid]').val('');
        $('input[name=mode]').val('');
        $('input[name=st_dt]').val('');
        $('input[name=en_dt]').val('');
        $('textarea[name=it_maker_description_pc]').val('');
        $('textarea[name=it_maker_description_mo]').val('');
        $('input:checkbox[name=use_flag_pc]').prop('checked',false);
        $('input:checkbox[name=use_flag_mo]').prop('checked',false);

        if(brand != ''){
            $.ajax({
                type: 'post'
                , url: './brand_search_data.php'
                , data: {
                    mode : 'banner_data',
                    brand : brand
                }
                , datatype: 'json'
                , success: function (data) {
                    var data =  JSON.parse(data);

                    if(data.it_maker ==undefined){
                        alert('없는 브랜드입니다');
                    }else{
                        $('.brand_da').css('display','none');
                        $('#brand_name').html(data.it_maker);
                        $('input[name=mode]').val('insert');
                        $('input[name=brand]').val(escape(data.it_maker));

                        if(data.uid !=null){

                              var mo = data.use_flag_mo ;


                              var pc = data.use_flag_pc ;
                              if(mo == '1'){
                                  $('input:checkbox[name=use_flag_mo]').prop('checked',true);
                              }

                              if(pc == '1'){
                                  $('input:checkbox[name=use_flag_pc]').prop('checked',true);
                              }

                            $('#sumit_button').html('수정');
                            $('input[name=uid]').val(data.uid);
                            $('input[name=mode]').val('update');

                            $('input[name=st_dt]').val(data.start_date);
                            $('input[name=en_dt]').val(data.end_date);
                            $('textarea[name=it_maker_description_pc]').val(data.it_maker_description_pc);
                            $('textarea[name=it_maker_description_mo]').val(data.it_maker_description_mo);

                        }

                    }

                }
            });
        }
    }
</script>



<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>

<script>
    $(function () {
        var dates = $("#from, #to ").datepicker({
            prevText: '이전 달',
            nextText: '다음 달',
            monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
            dayNames: ['일', '월', '화', '수', '목', '금', '토'],
            dayNamesShort: ['일', '월', '화', '수', '목', '금', '토'],
            dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
            dateFormat: 'yy-mm-dd',
            showMonthAfterYear: true,
            yearSuffix: '년',
            onSelect: function (selectedDate) {
                var option = this.id == "from" ? "minDate" : "maxDate",
                    instance = $(this).data("datepicker"),
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings);
                dates.not(this).datepicker("option", option, date);
            }
        });
    });
</script>
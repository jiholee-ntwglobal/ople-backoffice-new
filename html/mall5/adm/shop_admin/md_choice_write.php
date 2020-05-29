<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-17
 * Time: 오후 6:58
 */
$sub_menu = "600700";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");


$banner_data = sql_fetch("
	select
		uid, title, mobile_img_url, mobile_link_url, pc_img_url, pc_link_url, st_dt, en_dt, sort
	from
		md_choice_data
	where
		uid = '".$_GET['uid']."'
");
# 시작일 #
$st_date = $banner_data['st_dt'];
# 종료일 #
$en_date = $banner_data['en_dt'];

$g4[title] = "롤링배너 관리";
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<form method="post" action="./md_choice_porc.php" onsubmit=" return datacheck()">
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="panel-heading">MD-CHOICE 생성 및 수정</h4>
                </div>
            </div>
            <?if($_GET['uid']){?>
                <input type="hidden" name='uid' value='<?=$_GET['uid'];?>'/>
            <?}?>
                <input type="hidden" name='mode' value='<?=($_GET['uid']) ? 'update':'insert';?>'/>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel-body">
                        <input type="hidden" value="1" name="stockinputsort">
                        <table class="table table-hover table-bordered">
                            <tr >
                                <th class="text-center">제목</th>
                                <td class="text-center"><input type="text"class="form-control" name="subject" value="<?php echo $banner_data['title'];?>"></td>
                            </tr>
                            <tr>
                                <th class="text-center">시작일과 종료일</th>
                                <td>
                                    <div class="form-group form-inline">
                                    <input type="text"class="form-control" name="st_dt" id="from" value="<?php echo $st_date;?>" readonly>~
                                    <input type="text"class="form-control" name="en_dt" id="to" value="<?php echo $en_date;?>" readonly>
                                    </div>
                                </td>
                            </tr>
                            <tr >
                                <th class="text-center">정렬</th>
                                <td class="text-center"><input type="text"class="form-control" name="sort" value="<?php echo $banner_data['sort'];?>"></td>
                            </tr>
                            <tr  class="success" >
                                <th class="text-center" rowspan="4">모바일</th>
                                <th class="text-center" >이미지 URL</th>

                            </tr>
                            <tr  class="success">
                                <td class="text-center"><textarea class="form-control" name="mobile_img_url"><?php echo $banner_data['mobile_img_url'];?></textarea></td>
                            </tr>
                            <tr  class="success">
                                <th class="text-center" >링크 URL</th>
                            </tr>
                            <tr  class="success">
                                <td class="text-center"><textarea class="form-control" name="mobile_link_url"><?php echo $banner_data['mobile_link_url'];?></textarea></td>
                            </tr>
                            <tr class="info" >
                                <th class="text-center" rowspan="4">PC</th>
                                <th class="text-center" >이미지 URL</th>

                            </tr>
                            <tr class="info">
                                <td class="text-center"><textarea class="form-control" name="pc_img_url"><?php echo $banner_data['pc_img_url'];?></textarea></td>
                            </tr>
                            <tr class="info">
                                <th class="text-center" >링크 URL</th>
                            </tr>
                            <tr class="info">
                                <td class="text-center"><textarea class="form-control" name="pc_link_url"><?php echo $banner_data['pc_link_url'];?></textarea></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-right"><button class="btn btn-success" type="submit">생성</button>&nbsp;&nbsp;<button class="btn btn-danger" type="button" onclick="history.back();">목록</button></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
<script>
    function datacheck() {
        var subject = $('input[name=subject]').val().trim();
        var st_dt = $('input[name=st_dt]').val().trim();
        if(subject==''){
            alert('제목을 입력해주세요.');
            return false ;
        }
        if(st_dt==''){
            alert('시작날짜를 입력해주세요.');
            return false ;
        }
        return true;
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
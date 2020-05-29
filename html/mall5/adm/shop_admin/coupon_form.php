<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-07-11
 * Time: 오후 1:50
 */
$sub_menu = "500540";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");

$_GET['uid'] = trim($_GET['uid']) ? trim($_GET['uid']) : '';
$coupon_result =  array();
if($_GET['uid']){
    $sql =
        "
SELECT coupon_uid, 
       coupon_name,
       coupon_code,
       coupon_type,
       coupon_value,
       date_format(start_dt,'%Y-%m-%d') start_dt,
       date_format(end_dt,'%Y-%m-%d') end_dt,
       if (use_flag = '1','yes','no') use_flag ,
       if (du_publish = '1','yes','no') du_publish ,
       CASE coupon_type
          WHEN '1' THEN 'POINT'
          WHEN '2' THEN 'DISCOUNT RATE'
          ELSE 'DISCOUNT AMOUNT'
       END
          coupon_types
FROM coupon_new
where coupon_uid = '{$_GET['uid']}'
   ";

    $coupon_result = sql_fetch($sql);
}

$g4[title] = "쿠폰관리";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-sm-12">
        <h4>쿠폰 등록 수정</h4>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <p>중복가능 : 아이디당 1회사용가능</p>
        <p>중복불가능 : 1개 아이디 1회만 사용가능</p>
        <p>쿠폰 타입 : POINT (포인트적립)만 가능 </p>
        <p>종료날짜 기입을 안할시 시작날짜~무기한  </p>
    </div>
</div>

<div class="alert alert-success">

    <form class="form-horizontal" method="post" action="coupon_save.php" onsubmit="return uploadchk();" id="coupon_form">
        <input type="hidden" value="<?php echo !empty($coupon_result) ? 'update' : 'insert';?>" name="mode">
        <?php if(!empty($coupon_result)){?>
            <input type="hidden" value="<?php echo $coupon_result['coupon_uid'];?>" name="uid">
        <?php } ?>
        <div class="form-group">
            <label class="col-sm-2 control-label">쿠폰 이름</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" value="<?php echo !empty($coupon_result) ? $coupon_result['coupon_name'] : '';?>" name="coupon_name" <?php echo !empty($coupon_result) ? 'readonly' : '';?> required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">쿠폰 번호</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" maxlength="14" minlength="8" size="15" value="<?php echo !empty($coupon_result) ? $coupon_result['coupon_code'] : '';?>" name="coupon_code" <?php echo !empty($coupon_result) ? 'readonly' : '';?> required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">쿠폰 타입</label>
            <div class="col-sm-10">
                <select class="form-control" name="coupon_type" <?php echo !empty($coupon_result) ? 'readonly' : '';?>>
                    <?php if(!empty($coupon_result)){ ?>
                        <option  value="<?php echo $coupon_result['coupon_type'];?>"><?php echo $coupon_result['coupon_types'];?></option>
                    <?php }else{?>
                        <option value="1">POINT</option>
                        <option value="2">DISCOUNT RATE(미구현)</option>
                        <option value="3">DISCOUNT AMOUNT(미구현)</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">포인트 금액</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" value="<?php echo !empty($coupon_result) ? $coupon_result['coupon_value'] : '';?>" name="coupon_value" <?php echo !empty($coupon_result) ? 'readonly' : '';?> required>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">날짜</label>
            <div class="col-sm-5">
                <input type="text" class="form-control" name="start_date" id="from"  value="<?php echo !empty($coupon_result) ? $coupon_result['start_dt'] : '';?>">
            </div>
            <div class="col-sm-5">
                <input type="text" class="form-control" name="end_date" id="to"  value="<?php echo !empty($coupon_result) ? $coupon_result['end_dt'] : '';?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">사용가능 여부</label>
            <div class="col-sm-10">
                <select class="form-control" name="use_flag">
                    <option value="yes" <?php echo !empty($coupon_result) && $coupon_result['use_flag'] == 'yes'?'selected' : '';?>>사용가능</option>
                    <option value="no" <?php echo !empty($coupon_result) && $coupon_result['use_flag'] == 'no'?'selected' : '';?>>사용불가능</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">중복가능 여부</label>
            <div class="col-sm-10">
                <select class="form-control" name="du_publish">
                    <option value="yes" <?php echo !empty($coupon_result) && $coupon_result['du_publish'] == 'yes'?'selected' : '';?>>중복가능</option>
                    <option value="no" <?php echo !empty($coupon_result) && $coupon_result['du_publish'] == 'no'?'selected' : '';?>>중복불가능</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10 text-right">
                <?php if(!empty($coupon_result)){?>
                    <button type="button" onclick="del_data();" class="btn btn-danger">삭제</button>
                <?php }?>
                <button type="submit" class="btn btn-default">적용</button>
                <button type="button" class="btn btn-primary" onclick="location.href='./coupon_list.php'">목록</button>
            </div>
        </div>
    </form>
</div>

<form action="coupon_delete.php" id="coupon_delete_form" method="post">
    <input type="hidden" name="mode" value="delete">
    <?php if(!empty($coupon_result)){?>
        <input type="hidden" value="<?php echo $coupon_result['coupon_uid'];?>" name="uid">
    <?php } ?>
</form>

<script>
    function uploadchk() {
        var coupon_name = $('input[name=coupon_name]').val().trim();
        var coupon_code = $('input[name=coupon_code]').val().trim();
        var coupon_value = $('input[name=coupon_value]').val().trim();
        var start_date = $('input[name=start_date]').val().trim();
        var end_date = $('input[name=end_date]').val().trim();
        var yyyymmdd = /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/;

        if(coupon_name == ''){
            alert('쿠폰이름을 입력해주세요');
            $('input[name=coupon_name]').focus();
            return false;
        }
        if(coupon_code == ''){
            alert('쿠폰번호를 입력해주세요');
            $('input[name=coupon_code]').focus();
            return false;
        }
        if(coupon_value == ''){
            alert('포인트 금액을 입력해주세요');
            $('input[name=coupon_value]').focus();
            return false;
        }
        if(start_date ==''){
            alert('시작날짜를 선택해주세요');
            $('input[name=start_date]').focus();
            return false;
        }
        if(end_date != '' && !yyyymmdd.test(start_date) ){
            alert('YYYY-MM-DD 형식으로만 가능합니다');
            $('input[name=end_date]').focus();
            return true;
        }

        if(!yyyymmdd.test(start_date)){
            alert('YYYY-MM-DD 형식으로만 가능합니다');
            $('input[name=start_date]').focus();
            return false;
        }

        return true;

    }
    function del_data() {
        if(confirm('쿠폰을 삭제하시겠습니까?\n(쿠폰을 등록한 고객이 있다면 삭제가 불가능합니다)')){
            $('#coupon_delete_form').submit();
        }
        return false;
    }
</script>


<link rel="stylesheet" href="http://code.jquery.com/ui/1.8.18/themes/base/jquery-ui.css" type="text/css" media="all">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="http://code.jquery.com/ui/1.8.18/jquery-ui.min.js" type="text/javascript"></script>
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
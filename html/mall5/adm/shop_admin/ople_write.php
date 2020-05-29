<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-06-22
 * Time: 오후 4:35
 */
$sub_menu = "500530";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");
$_GET['uid'] = trim($_GET['uid']) ? trim($_GET['uid']) : '';
$mb_id =array();
if($_GET['uid']){
    $uid = sql_safe_query($_GET['uid']);
    $mb_id = sql_fetch(" SELECT mb_id,start_dt,end_dt from opler where uid ='{$uid}'");
    $mb_id['start_dt']=date('Y-m-d',strtotime($mb_id['start_dt']));
    $mb_id['end_dt']=date('Y-m-d',strtotime($mb_id['end_dt']));

}
$g4[title] = "오플러 생성 및 수정";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<div class="row">
    <div class="col-lg-12">
        <h4>오플러<?php echo $_GET['uid']?' 수정' : ' 생성';?></h4>
    </div>
</div>
<form class="form" action="opler_write_upin.php" method="post">
    <input type="hidden" name="mode" value="<?php echo $_GET['uid']?'update' : 'insert';?>">
    <input type="hidden" name="uid" value="<?php echo $_GET['uid'];?>">
    <table class="table">
        <tr>
            <th>아이디</th>
            <td><input class="form-control" type="text" name="mb_id" value="<?php echo isset($mb_id['mb_id'])? $mb_id['mb_id']:'';?>" <?php echo isset($mb_id['mb_id'])?'readonly':''; ?> ></td>
        </tr>
        <tr>
            <th>날짜</th>
            <td class="form-inline"><input class="form-control" type="text" name="start" id="from" value="<?php echo isset($mb_id['start_dt'])? $mb_id['start_dt']:'';?>" readonly>
                ~
                <input class="form-control" type="text" name="end" id="to" value="<?php echo isset($mb_id['end_dt'])? $mb_id['end_dt']:'';?>" readonly></td>
        </tr>
        <tr>
            <td colspan="2" class="text-right"><button class="btn btn-success" type="submit"><?php echo $_GET['uid']?' 수정' : ' 생성';?></button><button type="button" onclick="history.back()" class="btn btn-danger">목록</button></td>
        </tr>
    </table>

</form>
<script>

</script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.8.18/themes/base/jquery-ui.css" type="text/css" media="all" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="http://code.jquery.com/ui/1.8.18/jquery-ui.min.js" type="text/javascript"></script>
<script>
    $(function() {
        var dates = $( "#from, #to " ).datepicker({
            prevText: '이전 달',
            nextText: '다음 달',
            monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
            monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
            dayNames: ['일','월','화','수','목','금','토'],
            dayNamesShort: ['일','월','화','수','목','금','토'],
            dayNamesMin: ['일','월','화','수','목','금','토'],
            dateFormat: 'yy-mm-dd',
            showMonthAfterYear: true,
            yearSuffix: '년',
            onSelect: function( selectedDate ) {
                var option = this.id == "from" ? "minDate" : "maxDate",
                    instance = $( this ).data( "datepicker" ),
                    date = $.datepicker.parseDate(
                        instance.settings.dateFormat ||
                        $.datepicker._defaults.dateFormat,
                        selectedDate, instance.settings );
                dates.not( this ).datepicker( "option", option, date );
            }
        });
    });
</script>
<!-- <button type="button" onclick="get_name_jax()" >검색</button>
  이름
  <input class="form-control" type="text" id="name" readonly>-->
<!--function get_name_jax() {
var mb_id = $('input[name =mb_id]').val();
$.ajax({
type: 'get' ,
url: 'opler_write_upin.php' ,
data : "mb_id="+ mb_id+"&mode=idsearch",
dataType : 'html'
, success: function(data) {
$("#name").val(data);
}
});

}-->
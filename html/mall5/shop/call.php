<?php
include_once("./_common.php");

if (!$member['mb_id'])
    alert("로그인 한 회원만 접근하실 수 있습니다.");



if($_POST['mode'] == 'insert'){

	if( in_array(date('w',strtotime($_POST['dt'])),array(0,6)) ) {
		alert('죄송합니다. 해당시간 전화접수가 마감되었습니다.');
		exit;
	}

	// 현재보다 과거는 신청할 수 없습니다
	if(date('Ymd') > $_POST['dt']){
		alert('죄송합니다. 해당시간 전화접수가 마감되었습니다.');
		exit;
	}

	if(date('Ymd') == $_POST['dt'] && date('Hi') >= $_POST['time']){
		alert('죄송합니다. 해당시간 전화접수가 마감되었습니다.');
		exit;
	}

	// 동시간대 동일 회원이 연속으로 신청 못하도록
	$chk = sql_query("select count(*) as cnt from yc4_outbound_call where mb_id = '".$member['mb_id']."' and dt = '".$_POST['dt']."' and time = '".$_POST['time']."'");
	if( $chk['cnt'] > 0 ){
		alert('죄송합니다. 해당시간 전화접수가 마감되었습니다.');
		exit;
	}


	// 점심시간은 무조건 마감인걸로.... 밥은먹고살아야지
	$no_time = array(1300,1330);
	$no_day = array(
		20150201,
		20150214,
		20150215,
		20150218,
		20150219,
		20150220,
		20150221,
		20150222,
		20150228,
		20150301,
		20150307,
		20150308,
		20150314,
		20150315,
		20150321,
		20150322,
		20150328,
		20150329,
		20150404,
		20150405,
		20150411,
		20150412,
		20150418,
		20150419,
		20150425,
		20150426,
		20150502,
		20150503,
		20150505,
		20150509,
		20150510,
		20150516,
		20150517,
		20150523,
		20150524,
		20150525,
		20150530,
		20150531,
		20150606,
		20150607,
		20150613,
		20150614,
		20150620,
		20150621,
		20150627,
		20150628,
		20150704,
		20150705,
		20150711,
		20150712,
		20150718,
		20150719,
		20150725,
		20150726,
		20150801,
		20150802
	);

	# 동일 시간 내에 중복된 요청 수를 구한다
	$call_chk = sql_fetch("select count(*) as cnt from yc4_outbound_call where dt = '".$_POST['dt']."' and time = '".$_POST['time']."'");


	if(
		$call_chk['cnt'] >= 6 ||
		in_array($_POST['time'],$no_time) ||
		in_array($_POST['dt'],$no_day)
	) {
		alert('죄송합니다. 해당시간 전화접수가 마감되었습니다.');
		exit;
	}



	$hp_no = $_POST['hp_no1'].'-'.$_POST['hp_no2'].'-'.$_POST['hp_no3'];
	$comment = mysql_real_escape_string($_POST['comment']);
	$insertQ = "
		insert into
			yc4_outbound_call
		(
			mb_id,hp_no,dt,time,comment,create_dt
		)values(
			'".$member['mb_id']."','".$hp_no."','".$_POST['dt']."','".$_POST['time']."','".$comment."','".date('Y-m-d H:i:s')."'
		)
	";
	if(!sql_query($insertQ)){
		alert('저장중 오류발생! 다시 시도해 주세요');
		exit;
	}

	alert('요청이 완료되었습니다.',$_SERVER['PHP_SELF']);
	exit;


}

$g4['title'] = '전화요청';
include_once $g4['full_path']."/_head.php";





if(date('H')>=18){
	$today = date('Y년 m월 d일',mktime(0,0,0,date('m'),date('d')+1,date('Y')));

}else{
	$today = date('Y년 m월 d일');
}


# 전화요청 내역이 있는지 확인 #
$call_chk = sql_query("
	select * from yc4_outbound_call where mb_id = '".$member['mb_id']."' order by create_dt desc
");
$call_history_cnt = mysql_num_rows($call_chk);

$no = $call_history_cnt;

while($call = sql_fetch_array($call_chk)){
	$history_tr .= "
		<tr align='center'>
			<td>".$no."</td>
			<td>".substr($call['dt'],0,4).'년 '.substr($call['dt'],4,2).'월 '.substr($call['dt'],6,2)."일</td>
			<td>".$call['hp_no']."</td>
			<td align='left'>".nl2br($call['comment'])."</td>
			<td>".$call['create_dt']."</td>
		</tr>
	";
	$no--;
}


# 요청 시간 처리 시작 #
$time = 1000;
$time2 = 1100;


for($i=0; $i<6; $i++){

	$time = str_pad($time,4,0,STR_PAD_LEFT);
	$time2 = str_pad($time2,4,0,STR_PAD_LEFT);

	$time_option .= "<option value='".$time."'>".substr($time,0,2).':'.substr($time,2,2).'~'.substr($time2,0,2).':'.substr($time2,2,2)."</option>";

	$time_h = substr($time,0,2);
	$time_m = substr($time,2,2);
	$time_m = $time_m + 60;

	if($time_m == 60){
		$time_m = '00';
		$time_h = $time_h + 1;
	}
	$time = $time_h.''.$time_m;

	$time_h2 = substr($time2,0,2);
	$time_m2 = substr($time2,2,2);
	$time_m2 = $time_m2 + 60;

	if($time_m2 == 60){
		$time_m2 = '00';
		$time_h2 = $time_h2 + 1;
	}
	$time2 = $time_h2.''.$time_m2;
}
# 요청 시간 처리 끝 #
?>

<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/smoothness/jquery-ui.css" />
<style type="text/css">
	.box {border:solid 1px #bdbdbd;background-color:#f7f7f7;margin:5px;}
	.box table {width:100%;}
	.box th {padding:10px 0 11px 12px;border-bottom:solid 1px #e5e5e5;color:#919191;font-size:12px;text-align:left;font-weight:normal;}
	.box td {padding:10px 0 11px 12px;border-bottom:solid 1px #e5e5e5;}

	.list_style {}
	.list_style table {width:100%;}
	.list_style th {border-top:2px solid #fd7c00;border-bottom:1px solid #fd7c00;font-size:12px;font-weight:normal;color:#fd4700;padding:11px 0 10px 0;}
	.list_style td {border-bottom:1px solid #dddddd;padding:11px 0 10px 0;}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js"></script>
<div class='PageTitle'>
  <img src="<?=$g4['path']?>/images/menu/menu_title_call.gif" alt="전화요청" />
</div>

<form action="<?=$_SERVER['PHP_SELF']?>" method='post' onsubmit="return call_fnc();">
	<input type="hidden" name='mode' value='insert'/>
	<div class="box">
	<table border="0" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<th>날짜</th>
			<td><input type="text" name='dt' id='call_dt' value='<?=$today;?>' readonly onchange="date_disabled();"/> <input type="button" value='오늘' onclick='today();'/></td>
		</tr>
		<tr>
			<th>요청 시간</th>
			<td>
				<select name="time">
					<?=$time_option;?>
				</select>
				<?/*
				<span style="padding-left:15px;color:#fd4700;">※시간별 최대 6건까지만 신청 가능합니다. 가능한 시간대를 선택해주세요.</span>
				*/?>
			</td>
		</tr>
		<tr>
			<th>전화번호</th>
			<td><input type="text" name='hp_no1' class='numeric' maxlength='3'/> - <input type="text" name='hp_no2' class='numeric' maxlength='4'/> - <input type="text" name='hp_no3' class='numeric' maxlength='4'/></td>
		</tr>
		<tr>
			<th>내용</th>
			<td><textarea name="comment" id="" cols="120" rows="10"></textarea></td>
		</tr>
	</tbody>
	</table>
	</div>
	<p style="text-align:right;padding:10px 25px 10px 0;"><input type="submit" value='요청'/></p>
</form>

<div>
<p style="font-size:13px;margin-bottom:5px;text-align:right;padding-right:10px;"><strong><?=$call_history_cnt;?></strong>건</p>
<table border="0" cellspacing="0" cellpadding="0" class="list_styleA" style="border-top:solid 2px #000;width:100%;">
	<colgroup>
        <col width="60" />
        <col />
		<col width="100" />
		<col />
		<col width="80" />
    </colgroup>
	<thead>
		<tr>
			<th>번호</th>
			<th>요청시간</th>
			<th>전화번호</th>
			<th>내용</th>
			<th>등록일</th>
		</tr>
	</thead>
	<tbody>
		<?=$history_tr;?>
	</tbody>
</table>
</div>


<script type="text/javascript">
$(document).ready(function(){
	$('#call_dt').datepicker({
		dateFormat : "yy년 mm월 dd일",
		firstDay : 0, // 일요일 부터 시작
		maxDate : '+10',
		minDate : '-0',
		changeMoth : true,
		changeYear : true,
		dayNamesMin : ['일','월','화','수','목','금','토'],
		beforeShowDay : $.datepicker.noWeekends // 주말 선택 안되도록

	});

    $(".numeric").css("ime-mode", "disabled");  //요렇게 하면 한글도 잡아준다

	date_disabled();

});
function today(){
	$('#call_dt').val('<?=date('Y년 m월 d일')?>');
	date_disabled();
}

function call_fnc(){

	if($('#call_dt').val() == ''){
		alert('날짜를 입력해 주세요.');
		return false;
	}

	if($('select[name=time]').val() == ''){
		alert('시간을 선택해 주세요');
		return false;
	}

	if($('input[name=hp_no1]').val() == ''){
		alert('전화번호를 입력해 주세요.');
		$('input[name=hp_no1]').focus();
		return false;
	}

	if($('input[name=hp_no2]').val() == ''){
		alert('전화번호를 입력해 주세요.');
		$('input[name=hp_no2]').focus();
		return false;
	}

	if($('input[name=hp_no3]').val() == ''){
		alert('전화번호를 입력해 주세요.');
		$('input[name=hp_no3]').focus();
		return false;
	}

	$('#call_dt').val($('#call_dt').val().replace(/[^0-9]/g,''));
	return true;
}


function date_disabled(){ // 과거시간 비활성화

	var st = srvTime();
	var date = new Date(st);
	var now_year = date.getFullYear();
	var now_month = date.getMonth();
	now_month = (String(now_month).length <2 ) ? '0' + Number(now_month+1) : Number(now_month+1);
	var now_day = date.getDate();
	now_day = (String(now_day).length <2 ) ? '0' + now_day : now_day;
	var now_hour = date.getHours();
	now_hour = (String(now_hour).length <2 ) ? '0' + now_hour : now_hour;
	var now_min = date.getMinutes();
	now_min = (String(now_min).length <2 ) ? '0' + now_min : now_min;
	var now_time = String(now_hour) + String(now_min);


	// 날짜 처리
	var dt_arr = $('input[name=dt]').val().replace(/[ㄱ-힣]/g,'').split(' ');

	if(dt_arr[0] == now_year && dt_arr[1] == now_month && dt_arr[2] == now_day){ // 서버 시간과 선택한 날짜가 같을 경우 과거 시간은 선택할 수 없다

		var time_option = $('select[name=time] option');

		var time_result = '';
		for( var i=0; i<time_option.length; i++){
			if( $('select[name=time] option:eq('+i+')').val() < now_time ){
				$('select[name=time] option:eq('+i+')').attr('disabled',true);
			}else if( time_result == '' ){
				time_result = $('select[name=time] option:eq('+i+')').val();
				$('select[name=time]').val(time_result);
			}
		}
	}else{
		$('select[name=time] option').removeAttr('disabled'); // 미래의 날짜에는 모든 시간 선택 가능
	}
}

function srvTime(){ //  서버시간 가져오기
    if (window.XMLHttpRequest) {//분기하지 않으면 IE에서만 작동된다.
        xmlHttp = new XMLHttpRequest(); // IE 7.0 이상, 크롬, 파이어폭스 등
        xmlHttp.open('HEAD',window.location.href.toString(),false);
        xmlHttp.setRequestHeader("Content-Type", "text/html");
        xmlHttp.send('');
        return xmlHttp.getResponseHeader("Date");
    }else if (window.ActiveXObject) {
        xmlHttp = new ActiveXObject('Msxml2.XMLHTTP');
        xmlHttp.open('HEAD',window.location.href.toString(),false);
        xmlHttp.setRequestHeader("Content-Type", "text/html");
        xmlHttp.send('');
        return xmlHttp.getResponseHeader("Date");
    }
}


$('input.numeric').keydown(function(key){ // 전화번호는 숫자만 입력받도록
	var keycode = key.keyCode;
	var no_number = [8,9,35,36,37,38,49,40,46,47,48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105];

	// 8, 9, 48~57, 35~36, 37~40, 46, 96~105
	if(no_number.indexOf(keycode) < 0 ){
		return false;
	}
});

$('input.numeric').blur(function(){ // 포커스 이동시 숫자 이외의 숫자는 다날려버림
	$(this).val($(this).val().replace(/[^0-9]/g,''));
});



</script>

<?
include_once $g4['full_path']."/_tail.php";
?>
<?php
/**
 * Created by Eclipse
 * User: kyung-in
 * Date: 2015.09.09
 * file: test_2/ev_list_update.php
 */
$sub_menu = "500800";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "이벤트통계관리";
include_once ("$g4[admin_path]/admin.head.php");

$name	= "";
$query	= "";
$seqno	= "";
$mode	= "";
$st_dt	= "";
$ed_dt	= "";
$btname	= "등록";
$list_path	= "./eventresearch_list.php";
$act_chk= "checked";

// 글 수정일때 디비에서 기존의 글정보를 불러옴
if(isset($_GET['mode']) && $_GET['mode'] == "rewrite"){
	$seqno	= trim($_GET['seqno']);
//	$seqno	= mysql_real_escape_string($seqno);
	$mode	= $_GET['mode'];
	$sql	= "SELECT
				*
			FROM
				event_research
			WHERE
				seqno=".$seqno."
	";
	$res	= mysql_query($sql);
	$row	= mysql_fetch_assoc($res);
	$name	= $row['ev_name'];
	$query	= $row['ev_query'];
	$st_dt	= $row['st_dt'];
	$ed_dt	= $row['ed_dt'];
	$stat	= $row['stat'];
	$btname	= "수정";
	($stat == "Y") ? $stat = "N" : $stat = "Y";
	$list_path	= $list_path."?vmode=".$stat;
}
?>
</head>
<body>
<form name="form_write" method="post" action="eventresearch_action.php" onsubmit="return chk_form(this);">
<table width="850" border="1" cellpadding="0" cellspacing="0" align="center">
<input type="hidden" name="seqno" value="<?php echo $seqno;?>">
<input type="hidden" name="mode" value="<?php echo $mode;?>">
	<tr>
		<td colspan="4" align="center">이벤트 통계 쿼리 <?php echo $btname;?></td>
	</tr>
	<tr>
		<td>
			이벤트 이름
			<input type="text" name="ev_name" width="600" value="<?php echo $name;?>" maxlength="20">
		</td>
		<td>
			이벤트기간: <input type="text" id="from" name="st_dt" value="<?php echo $st_dt;?>" readonly /> ~ <input type="text" id="to" name="ed_dt" value="<?php echo $ed_dt;?>" readonly /></p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea name="ev_query" rows="10" cols="90"><?php echo $query;?></textarea>
		</td>
		<td>
			<input type="submit" value="<?php echo $btname;?>" />
		</td>
	</tr>
	<tr>
		<td colspan="4" align="center"><input type="button" value="리스트" onclick="location.href='<?php echo $list_path;?>'" /></td>
	</tr>
</table>
</form>
</body>

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
<script type="text/javascript">
function chk_form(a){
	if(a.ev_name.value == ""){
		alert("이벤트 이름");
		a.ev_name.focus();
		return false;
	}
	if (a.ev_name.value.length >= a.ev_name.maxlength){
		alert("이벤트 이름은 20자 이하로 제한됩니다.");
		a.ev_name.value = a.ev_name.value.slice(0, a.ev_name.maxlength);
		a.ev_name.focus();
		return false;
	}    
	if(a.st_dt.value == ""){
			alert("이벤트 시작날짜를 정해주세요");
			a.st_dt.focus();
			return false;
	}
	if(a.ed_dt.value == ""){
			alert("이벤트 종료날짜를 정해주세요");
			a.ed_dt.focus();
			return false;
	}
	if(a.ev_query.value ==""){
			alert("사용할 명령어 내용을 입력해주세요");
			a.ev_query.focus();
			return false;
	}
}
</script>
<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
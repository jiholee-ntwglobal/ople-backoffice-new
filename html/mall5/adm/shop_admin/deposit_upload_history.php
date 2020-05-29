<?php 
/*
----------------------------------------------------------------------
file name	 : deposit_upload_history.php
comment		 : 무통장입금자 일괄 입금확인처리 히스토리
date		 : 2015-04-15
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400992";
include_once("./_common.php");

//auth_check($auth[$sub_menu], "r");

$g4['title'] = "무통장입금자 일괄 입금확인처리 내역";
include_once ($g4['admin_path']."/admin.head.php");

$yyyy = $_GET['yyyy'] ? $_GET['yyyy'] : date('Y');
$mm = $_GET['mm'] ? str_pad($_GET['mm'],2,'0',STR_PAD_LEFT) : date('m');
$dd = $_GET['dd'] ? str_pad($_GET['dd'],2,'0',STR_PAD_LEFT) : date('d');

$set_date = $yyyy . $mm . $dd;

switch($_GET['type']){
	case 'o': $add_where = " AND NOT ISNULL(match_od_id)"; break;
	case 'x': $add_where = " AND ISNULL(match_od_id)"; break;
	default: $add_where = ''; break;
}

$max_data = sql_fetch("select max(seq) as max_seq from yc4_deposit_upload_history where upload_date='$set_date' $add_where");
$max_seq = $max_data['max_seq'];

$choose_seq = ($_GET['seq']) ? $_GET['seq'] : $max_seq;

$match_od_id_arr = array();


if($choose_seq > 0){

	$rs = sql_query("select * from yc4_deposit_upload_history where upload_date='$set_date' and seq='$choose_seq' $add_where order by uid asc");	

	while($data = sql_fetch_array($rs)){

		$duplicate_class = (in_array($data['match_od_id'],$match_od_id_arr) && $data['match_od_id']!='') ? 'duplicate_row' : '';

		if(!in_array($data['match_od_id'],$match_od_id_arr) && $data['match_od_id']!='') array_push($match_od_id_arr,$data['match_od_id']);

		$tbody .= "
			<tr class='$duplicate_class'>
				<td style='padding-left:25px'>$data[match_od_id]</td>
				<td style='padding-left:25px'>$data[name]</td>
				<td style='padding-left:25px'>$data[price]</td>
			</tr>";
	}


} else {
	$tbody = "
	<tr>
		<td colspan='3' align='center'>업로드 자료가 존재하지 않습니다.</td>		
	</tr>";
}

?>
<style>
.match_tr {background-color:yellow;}
.Pstyle {
	opacity: 0;
	display: none;
	position: relative;
	width: auto;
	border: 5px solid #fff;
	padding: 20px;
	background-color: #fff;
}

.b-close {
	position: absolute;
	right: 5px;
	top: 5px;
	padding: 5px;
	display: inline-block;
	cursor: pointer;
}
.hover {
	background-color:#FFA7A7;
}
.hover2 {
	background-color:#B2CCFF
}
.duplicate_row {
	background-color:#FFA7A7;
}
</style>
<h2>무통장입금자 일괄 입금확인처리 내역&nbsp;<input type="button" value="입금 일괄처리페이지로 이동" onclick="location.href='./deposit_manager.php'"/></h2>
<br>
<div style="border:1px solid black;padding:10px 20px;">
<form method="GET">
<table >
	<tr>
		<td>
		<select name="yyyy">
		<?php
		for($k=2015;$k<=date('Y');$k++){
			$selected = $k==$yyyy ? 'selected' : '';
			echo "<option value='$k' $selected>${k}년</option>";
		}
		?>
		</select>
		</td>
		<td>
		<select name="mm">
		<?php
		for($k=1;$k<=12;$k++){
			$selected = $k==$mm ? 'selected' : '';
			echo "<option value='$k' $selected>${k}월</option>";
		}
		?>
		</select>
		</td>
		<td>
		<select name="dd">
		<?php
		for($k=1;$k<=date('t', mktime(0,0,0,$mm,1,$yyyy));$k++){
			$selected = $k==$dd ? 'selected' : '';
			echo "<option value='$k' $selected>${k}일</option>";
		}
		?>
		</select>
		</td>
		<td>
		<?php
		if($max_seq > 0){ ?>
		<select name="seq">
		<?php
		for($k=1;$k<=$max_seq;$k++){
			$selected = $k==$choose_seq ? 'selected' : '';
			echo "<option value='$k' $selected>${k}회차</option>";
		}
		?>
		</select>
		<?php } ?>
		</td>
		<td>
		<select name="type">
		<option value="">전체</option>
		<option value="o" <?php if($_GET['type'] == 'o') echo 'selected'; ?>>입금</option>
		<option value="x" <?php if($_GET['type'] == 'x') echo 'selected'; ?>>미입금</option>
		</select>
		</td>
		<td>
		<input type="submit" value="검색"/>
		</td>
	</tr>
</table>
</form>
</div>
<br/>
<table width="300"style="border:1px solid black;">
	<thead style="font-weight:bold;background-color:#BDBDBD;" align="center">
		<tr>
			<td height="30">처리 주문ID</td>
			<td>입금자명</td>
			<td>입금액</td>
		</tr>
	</thead>
	<tbody >
	<?php
	echo $tbody;
	?>
	</tbody>
</table>
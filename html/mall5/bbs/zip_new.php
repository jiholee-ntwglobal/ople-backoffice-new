<?
# 도로명 주소 적용 2014-04-17 홍민기 #
include "_common.php";

# 주소 검색 로드 #
if($_POST['mode'] == 'addr_search'){
	/*
	$addr_listQ = mysql_query("
		select 
			* 
		from 
			zipcode_street 
		where 
			SIDO = '".$_POST['addr_1']."' 
			and
			SIGUNGU = '".$_POST['addr_2']."'
			and
			(
				RI like '%".$_POST['addr_3']."%'
				or
				DONG like '%".$_POST['addr_3']."%'
				or
				STREET like '%".$_POST['addr_3']."%'
				or
				BUILDING like '%".$_POST['addr_3']."%'
			)
	");
	*/
	$key_arr = explode(' ',$_POST['addr_3']);
	if(is_array($key_arr)){
		foreach($key_arr as $val){
			$result .= " + *".$val."* ";
		}
	}

	$result = ($result) ? $result : $_POST['addr_3'];

	$qry = "
		select 
			match(`STREET`,`DONG`,`RI`,`BUILDING`,`ZIPCODE`,`BUILDINGNUM1`,`BUILDINGNUM2`) against('".$result."' IN BOOLEAN MODE) as score  ,
			SIDO, SIGUNGU, EUP, DONG, RI, ISMOUNTAIN, JIBUN1, JIBUN2, STREET, ISUNDER, BUILDINGNUM1, BUILDINGNUM2, BUILDING, ZIPCODE
		from 
			zipcode_street 
		where 
			SIDO = '".$_POST['addr_1']."' 
			and
			SIGUNGU = '".$_POST['addr_2']."'
			and
			match(`STREET`,`DONG`,`RI`,`BUILDING`,`ZIPCODE`,`BUILDINGNUM1`,`BUILDINGNUM2`) against('".$result."' IN BOOLEAN MODE)
		order by score desc

	";


	$limit = (!$_POST['limit']) ? " limit 0,30" : "limit ".$_POST['limit'].",30";

	$addr_listQ = mysql_query($qry.$limit);
	$addr_list_cnt = mysql_num_rows(mysql_query($qry));

	while($addr_list = mysql_fetch_array($addr_listQ)){
		$addr = $addr_list['SIDO']." ".$addr_list['SIGUNGU']." ".$addr_list['STREET']." " . $addr_list['BUILDINGNUM1'] . (($addr_list['BUILDINGNUM2'])? '-'.$addr_list['BUILDINGNUM2']:'') . '('.$addr_list['DONG'].$addr_list['EUP'].(($addr_list['BUILDING']) ? ', '.$addr_list['BUILDING']:'').')';

		$list_data .= "
			<tr>
				<td align='center'>".substr($addr_list['ZIPCODE'],0,3)."-".substr($addr_list['ZIPCODE'],3,3)."</td>
				<td>
					<input type='hidden' name='post1' value='".substr($addr_list['ZIPCODE'],0,3)."'>
					<input type='hidden' name='post2' value='".substr($addr_list['ZIPCODE'],3,3)."'>
					<input type='hidden' name='addr1' value='".$addr."'>

					<div>".$addr."</div>
					<div>".$addr_list['SIDO']." ".$addr_list['SIGUNGU']." ".$addr_list['DONG']." ".$addr_list['RI']." ".$addr_list['JIBUN1']."-".$addr_list['JIBUN2']."</div>
				</td>
				<td align='center'><button class='addr_choice_btn'>선택</button></td>
			</tr>
		";
	}
	echo $addr_list_cnt;
	echo "###_SPLIT_###";
	echo $list_data;
	exit;
}


# 시/군/구 로드 #
if($_POST['mode'] == 'addr_2'){
	$addr_2Q = mysql_query("select SIGUNGU from zipcode_street where SIDO = '".$_POST['addr_1']."' group by SIGUNGU");
	echo "<option value=''>선택하세요</option>";
	while($addr_2 = mysql_fetch_array($addr_2Q)){
		echo "<option value='".$addr_2['SIGUNGU']."'>".$addr_2['SIGUNGU']."</option>";
	}
	exit;
}

# 시도 리스트 로드 #
$addr_1Q = mysql_query("select SIDO from zipcode_street group by SIDO");

$addr_1_option = "<option value=''>선택하세요</option>";
while($addr_1 = mysql_fetch_array($addr_1Q)){
	$addr_1_option .= "<option value='".$addr_1['SIDO']."'>".$addr_1['SIDO']."</option>";
}
?>
<!doctype html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<title>주소찾기</title>
	<style type="text/css">
	.result_cnt{
		text-align:right;
	}
	.addr_list_wrap{
		display:none;
		height:400px;
		overflow-y:auto;
	}

	.loding_box{
		display:none;
		background-color:antiquewhite;
		position: absolute;
		right: 6px;
		top: 20px;
		border: 1px solid #dddddd;
		padding: 5px 60px;
	}
	.addr_result_wrap{
		display:none;
	}
	.result_cnt{
		padding:5px;
	}
	</style>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

</head>
<body>
도로명주소
<br />
시도
<select name="addr_1" id="addr_1">
	<?=$addr_1_option;?>
</select>

시군구
<select name="addr_2" id="addr_2" disabled>
	<option value="">선택하세요</option>
</select>

주소명
<form action="<?=$_SERVER['PHP_SELF']?>" onsubmit='return addr_search()'>
<input type="text" name='addr_3' id='addr_3' disabled/><button>검색</button>
</form>

<div class='loding_box'>처리중.. 잠시만 기다려 주세요.</div>
<div class='result_cnt'></div>
<div class='addr_list_wrap' onscroll="scrolling()">
	
	<table width='100%' border='1' style='border-collapse:collapse;' cellpadding='5'>
		<thead>
			<tr>
				<th>우편번호</th>
				<th>주소</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		
		</tbody>
	</table>
</div>


<div class='addr_result_wrap'>
	<h4>상세 주소를 입력해 주세요.</h4>
	우편번호 : <input type="text" name='post1' readonly/> - <input type="text" name='post2' readonly/> <br />
	주소 : <input type="text" name='addr1' readonly/> <br />
	상세주소 : <input type="text" name='addr2'/>

	<button class='addr_submit_btn' onclick='addr_submit();'>완료</button>
</div>

<script type="text/javascript">
addr_cnt = 0;
var tmp_addr1,tmp_addr2,tmp_addr3;

function addr_cnt_proc(){
	addr_cnt = $('.addr_list_wrap table tbody tr').length;
}

function scrolling(){
	if($('.addr_list_wrap').scrollTop() == $('.addr_list_wrap table').height()-398){
		lodding('Y');

		$.ajax({      
			type: 'POST',  
			dataType: 'HTML',
			url: '<?=$_SEREVER['PHP_SELF']?>',  
			data: {
				'mode' : 'addr_search',
				'limit' : addr_cnt,
				'addr_1' : tmp_addr1,
				'addr_2' : tmp_addr2,
				'addr_3' : tmp_addr3
			},  
			success: function( result ){

				var result_arr = result.split('###_SPLIT_###');

				var cnt = result_arr[0];
				var data = result_arr[1];

				lodding('N');
				$('.addr_list_wrap table tbody').append(data);
				addr_cnt_proc();


			}  
			
		});
	}else{
		return false;
	}
}


// 시군구 로드
$('#addr_1').change(function(){
	
	if($('#addr_1').val() == ''){
		$('#addr_2').html("<option value=''>선택하세요</option>");
		$('#addr_2').attr('disabled',true);
		$('#addr_3').attr('disabled',true);
		$('#addr_3').val('');
		return false;
	}

	lodding('Y');
	
	$.ajax({      
		type: 'POST',  
		dataType: 'HTML',
		url: '<?=$_SEREVER['PHP_SELF']?>',  
		data: {
			'mode' : 'addr_2',
			'addr_1' : $('#addr_1').val()
		},  
		success: function( result ){
			lodding('N');
			$('#addr_2').html(result);
			$('#addr_2').removeAttr('disabled');
			tmp_addr1 = $('#addr_1').val();
			
		}  
		
	});
	
});

$('#addr_2').change(function(){
	tmp_addr2 = $('#addr_2').val();

	if($('#addr_2').val() == ''){
		$('#addr_3').val('');
		$('#addr_3').attr('disabled',true);
		return false;
	}

	$('#addr_3').removeAttr('disabled');
});

function addr_search(){
	if($('#addr_1').val() == ''){
		alert('시/도를 선택해 주세요.');
		$('#addr_1').focus();
		return false;
	}

	if($('#addr_2').val() == ''){
		alert('시/군/구를 선택해 주세요.');
		$('#addr_2').focus();
		return false;
	}

	if($('#addr_3').val().length<2){
		alert('두글자 이상 입력해 주세요.');
		return false;
	}

	lodding('Y');

	$.ajax({      
		type: 'POST',  
		dataType: 'HTML',
		url: '<?=$_SEREVER['PHP_SELF']?>',  
		data: {
			'mode' : 'addr_search',
			'addr_1' : $('#addr_1').val(),
			'addr_2' : $('#addr_2').val(),
			'addr_3' : $('#addr_3').val()
		},  
		success: function( result ){

			var result_arr = result.split('###_SPLIT_###');

			var cnt = result_arr[0];
			var data = result_arr[1];

			lodding('N');
			$('.addr_list_wrap table tbody').html(data);
			$('.result_cnt').html('검색결과 총 <b>'+cnt+'</b>건');
			$('.addr_list_wrap').show();
			addr_cnt_proc();
			tmp_addr1 = $('#addr_1').val();
			tmp_addr2 = $('#addr_2').val();
			tmp_addr3 = $('#addr_3').val();
			$('.addr_list_wrap').scrollTop(0);
		}  
		
	});

	return false;
}

// 주소 선택
$('.addr_list_wrap').delegate('.addr_choice_btn','click',function(){
	var post1 = $(this).parent().parent().find('input[name=post1]').val();
	var post2 = $(this).parent().parent().find('input[name=post2]').val();
	var addr1 = $(this).parent().parent().find('input[name=addr1]').val();
	var addr2 = $(this).parent().parent().find('input[name=addr2]').val();
	$('.addr_list_wrap').hide();
	$('.result_cnt').empty();
	addr_input_display();

	$('.addr_result_wrap input[name=post1]').val(post1);
	$('.addr_result_wrap input[name=post2]').val(post2);
	$('.addr_result_wrap input[name=addr1]').val(addr1);
});

function addr_input_display(){
	if($('.addr_result_wrap:hidden').length>0){
		$('.addr_result_wrap').show();
	}
}


function lodding( mode ){
	switch( mode ){
		case 'Y' : $('.loding_box').show(); break;
		case 'N' : $('.loding_box').fadeOut(); break;
	}

	return false;
}


function addr_submit(){
	if(
		$('.addr_result_wrap input[name=post1]').val() == '' ||
		$('.addr_result_wrap input[name=post2]').val() == '' ||
		$('.addr_result_wrap input[name=addr1]').val() == ''
	){
		alert('주소를 선택해 주세요.');
		return false;
	}

	if($('.addr_result_wrap input[name=addr2]').val() == ''){
		alert('상세주소를 입력해 주세요.');
		$('.addr_result_wrap input[name=addr2]').val().focus();
		return false;
	}


	$("form[name=<?=$_GET['frm_name'];?>] input[name=<?=$_GET['frm_zip1'];?>]", opener.document).val($('.addr_result_wrap input[name=post1]').val());
	$("form[name=<?=$_GET['frm_name'];?>] input[name=<?=$_GET['frm_zip2'];?>]", opener.document).val($('.addr_result_wrap input[name=post2]').val());
	$("form[name=<?=$_GET['frm_name'];?>] input[name=<?=$_GET['frm_addr1'];?>]", opener.document).val($('.addr_result_wrap input[name=addr1]').val());
	$("form[name=<?=$_GET['frm_name'];?>] input[name=<?=$_GET['frm_addr2'];?>]", opener.document).val($('.addr_result_wrap input[name=addr2]').val());

	self.close();
}
</script>
</body>
</html>
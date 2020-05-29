<?
## 베너 등록 & 수정 페이지 2014-04-15 홍민기 ##
include_once("./_common.php");

# 베너관리 접근 가능 계정 #
$permit_arr = array(
	'ghdalsrldi','sun1002a','admin','beeby'
);

if(!in_array($_SESSION['ss_mb_id'],$permit_arr)){
	exit;
}


# 저장 처리 #
if($_POST['mode']){

	$_POST['contents'] = addslashes($_POST['contents']);
	$st_dt = $_POST['st_year'].'-'.$_POST['st_month'].'-'.$_POST['st_day'].' '.$_POST['st_h'].':'.$_POST['st_m'].':00';
	$en_dt = $_POST['en_year'].'-'.$_POST['en_month'].'-'.$_POST['en_day'].' '.$_POST['en_h'].':'.$_POST['en_m'].':00';

	if($_POST['mode'] == 'insert'){

		

		$insertQ = "
			insert into 
				banner_data
			(
				contents, st_dt, en_dt, create_dt,sort
			)values(
				'".$_POST['contents']."','".$st_dt."','".$en_dt."',now(),'".$_POST['sort']."'
			)
		";
		if(mysql_query($insertQ)){
			echo "
				<script>
					alert('저장이 완료되었습니다.');
					location.href='banner_config.php';
				</script>
			";
			exit;
		}else{
			echo "
				<script>
					alert('저장중 오류가 발생했습니다. 다시 시도해 주세요');
					history.back();
				</script>
			";
			exit;
		}
	}


	if($_POST['mode'] == 'update'){


		$updateQ = "
			update
				banner_data
			set
				st_dt = '".$st_dt."',
				en_dt = '".$en_dt."',
				contents = '".$_POST['contents']."',
				sort = '".$_POST['sort']."',
				update_dt = now()
			where
				uid = '".$_POST['uid']."'
		";


		if(mysql_query($updateQ)){
			echo "
				<script>
					alert('저장이 완료되었습니다.');
					location.href='banner_config.php';
				</script>
			";
			exit;
		}else{
			echo "
				<script>
					alert('저장중 오류가 발생했습니다. 다시 시도해 주세요');
					history.back();
				</script>
			";
			exit;
		}
	}

}

if($_GET['uid']){
	$banner_data = mysql_fetch_array(mysql_query("select * from banner_data where uid ='".$_GET['uid']."'"));
	$banner_data['contents'] = Stripslashes($banner_data['contents']);

	# 시작일 #
	$st_date = $banner_data['st_dt'];
	$st_arr = explode(' ',$st_date);
	$st_dt_arr = explode('-',$st_arr[0]);
	$st_time_arr = explode(':',$st_arr[1]);
	$st_y = $st_dt_arr[0];
	$st_m = $st_dt_arr[1];
	$st_d = $st_dt_arr[2];
	$st_h = $st_time_arr[0];
	$st_i = $st_time_arr[1];




	# 종료일 #
	$en_date = $banner_data['en_dt'];
	$en_arr = explode(' ',$en_date);
	$en_dt_arr = explode('-',$en_arr[0]);
	$en_time_arr = explode(':',$en_arr[1]);
	$en_y = $en_dt_arr[0];
	$en_m = $en_dt_arr[1];
	$en_d = $en_dt_arr[2];
	$en_h = $en_time_arr[0];
	$en_i = $en_time_arr[1];
	
	
}








?>
<form action="<?=$_SERVER['PHP_SELF']?>" method='POST' id='banner_frm' onsubmit='return banner_submit(this);'>
	<input type="hidden" name='mode' value='<?=($_GET['uid']) ? 'update':'insert';?>'/>
	<?if($_GET['uid']){?>
	<input type="hidden" name='uid' value='<?=$_GET['uid'];?>'/>
	<?}?>
	<table width='100%' border='1' style='border-collapse: collapse;'>
		<thead>
			<tr>
				<th colspan='2'>베너등록</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan='2' align='center'>
					<input type="submit" value='저장'/>
					<input type="reset"  value='초기화'/>
					<?if($_GET['uid']){?>
					<a href='#' onclick="banner_del('<?=$banner_data['uid']?>; return false;')">삭제</a>
					<?}?>
					<a href='#' onclick="location.href='banner_config.php'; return false;">목록</a>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<th>시작시간</th>
				<td>
					<input type="text" name='st_year' value='<?=$st_y;?>'/>년
					<input type="text" name='st_month' value='<?=$st_m;?>'/>월
					<input type="text" name='st_day' value='<?=$st_d;?>'/>일
					<select name="st_h">
						<?for($h=0; $h<=23; $h++){
							$h = str_pad($h,2,0,STR_PAD_LEFT);
							echo "<option value='".$h."' ".(($h == $st_h)?'selected':'').">".$h."</option>";
						}?>
					</select>시
					<select name="st_m">
						<option value="00" <?=($st_i == '00') ? 'selected':'';?>>00</option>
						<option value="30" <?=($st_i == '30') ? 'selected':'';?>>30</option>
					</select>분
				</td>
			</tr>
			<tr>
				<th>종료시간</th>
				<td>
					<input type="text" name='en_year' value='<?=$en_y;?>'/>년
					<input type="text" name='en_month' value='<?=$en_m;?>'/>월
					<input type="text" name='en_day' value='<?=$en_d;?>'/>일
					<select name="en_h">
						<?for($h=0; $h<=23; $h++){
							$h = str_pad($h,2,0,STR_PAD_LEFT);
							echo "<option value='".$h."' ".(($h == $en_h)?'selected':'').">".$h."</option>";
						}?>
					</select>시
					<select name="en_m">
						<option value="00" <?=($en_i == '00') ? 'selected':'';?>>00</option>
						<option value="30" <?=($en_i == '30') ? 'selected':'';?>>30</option>
					</select>분
				</td>
			</tr>
			<tr>
				<th>베너순서</th>
				<td><input type="text" name='sort' value='<?=$banner_data['sort'];?>'/>(숫자입력->낮을수록 앞에 노출)</td>
			</tr>
			<tr>
				<th>내용</th>
				<td><textarea name="contents" rows="10" style='width:100%;'><?=stripslashes($banner_data['contents'])?></textarea></td>
			</tr>
		</tbody>
	</table>
</form>


<script type="text/javascript">
function banner_submit(f){
	if(f.st_year.value == ''){
		alert('시작시간(년도)를 입력해 주세요.');
		f.st_year.focus();
		return false;
	}
	if(f.st_month.value == ''){
		alert('시작시간(월)를 입력해 주세요.');
		f.st_month.focus();
		return false;
	}
	if(f.st_day.value == ''){
		alert('시작시간(일)를 입력해 주세요.');
		f.st_day.focus();
		return false;
	}
	if(f.en_year.value == ''){
		alert('종료시간(년도)를 입력해 주세요.');
		f.en_year.focus();
		return false;
	}
	if(f.en_month.value == ''){
		alert('종료시간(월)를 입력해 주세요.');
		f.en_month.focus();
		return false;
	}
	if(f.en_day.value == ''){
		alert('종료시간(일)를 입력해 주세요.');
		f.en_day.focus();
		return false;
	}

	if(f.contents.value == ''){
		alert('내용을 입력해 주세요.');
		f.contents.focus();
		return false;
	}

	return true;
}

function banner_del( uid ){
	if(!confirm('베너를 삭제하시겠습니까?')){
		return false;
	}

	location.href='banner_config.php?mode=del&uid='+uid;
}
</script>
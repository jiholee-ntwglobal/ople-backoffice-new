<?
## 베너 등록 & 수정 페이지 2014-04-15 홍민기 ##
include_once("./_common.php");

# 베너관리 접근 가능 계정 #
$permit_arr = array('ghdalsrldi','sun1002a','admin','beeby','design','dev');

if(!in_array($_SESSION['ss_mb_id'],$permit_arr)){
	exit;
}

if($_POST['mode']){

	$_POST['contents'] = mysql_real_escape_string(stripslashes($_POST['contents']));
	$_POST['title'] = mysql_real_escape_string(stripslashes($_POST['title']));
	$_POST['title_link'] = mysql_real_escape_string(stripslashes($_POST['title_link']));
    $_POST['mobile_title_link'] = mysql_real_escape_string(stripslashes($_POST['mobile_title_link']));


	$st_dt = $_POST['st_year'].'-'.str_pad($_POST['st_month'],2,0,STR_PAD_LEFT).'-'.str_pad($_POST['st_day'],2,0,STR_PAD_LEFT).' '.$_POST['st_h'].':'.$_POST['st_m'].':00';
	$en_dt = $_POST['en_year'].'-'.str_pad($_POST['en_month'],2,0,STR_PAD_LEFT).'-'.str_pad($_POST['en_day'],2,0,STR_PAD_LEFT).' '.$_POST['en_h'].':'.$_POST['en_m'].':00';

	if($_POST['sort']){
		$update_qry = "
			update
				banner_data_new
			set sort = sort +1
			where
				s_id = '".$_POST['s_id']."'
				and sort >= '".$_POST['sort']."'
				".($_POST['uid'] ? " and uid != '".$_POST['uid']."'":"")."
		";

		sql_query($update_qry);
	}else{
		$sort_chk = sql_fetch("select max(sort) as sort from banner_data_new where s_id = '".$_POST['s_id']."'");
		$_POST['sort'] = $sort_chk['sort'] + 1;
	}

	if($_POST['mode'] == 'update'){
		$qry = "
			update
				banner_data_new
			set
				st_dt = '".$st_dt."',
				en_dt = '".$en_dt."',
				contents = '".$_POST['contents']."',
				title = '".$_POST['title']."',
				s_id = '".$_POST['s_id']."',
				title_link = '".$_POST['title_link']."',
				mobile_title_link = '".$_POST['mobile_title_link']."',
				sort = '".$_POST['sort']."',
				mobile_img = '".$_POST['mobile_img']."',
				update_dt = now()
			where
				uid = '".$_POST['uid']."'
		";
		$msg = '수정이 완료되었습니다.';
	}elseif($_POST['mode'] == 'insert'){
		$qry = "
			insert into
				banner_data_new
			(
				s_id, title, title_link, mobile_title_link, contents, st_dt, en_dt, sort, create_dt,mobile_img
			) VALUES(
				'".$_POST['s_id']."','".$_POST['title']."', '".$_POST['title_link']."', '".$_POST['mobile_title_link']."' , '".$_POST['contents']."','".$st_dt."','".$en_dt."','".$_POST['sort']."',now(),'".$_POST['mobile_img']."'
			)
		";
		$msg = '베너 등록이 완료되었습니다.';
	}
	if(!sql_query($qry)){
		alert('처리중 오류 발생! 다시 시도해 주세요.');exit;
	}

	alert($msg,$g4['path'].'/banner_config_new.php');
	exit;


}

$banner_data = sql_fetch("
	select
		*
	from
		banner_data_new
	where
		uid = '".$_GET['uid']."'
");

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

# 제품관 리스트 로드 #
$st_qry = sql_query("
	select
		s_id,name
	from
		yc4_station
	order by sort asc
");
while($row = sql_fetch_array($st_qry)){
	$st_option .= "<option value='".$row['s_id']."' ".($row['s_id'] == $banner_data['s_id'] ? "selected":"").">".$row['name']."</option>";
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
				<a href='#' onclick="location.href='banner_config_new.php'; return false;">목록</a>
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
			<th>제품관</th>
			<td>
				<select name="s_id">
					<option value="">제품관 선택</option>
					<?=$st_option;?>
					<option value="10">아이행복</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>베너순서</th>
			<td><input type="text" name='sort' value='<?=$banner_data['sort'];?>'/>(숫자입력->낮을수록 앞에 노출)</td>
		</tr>
		<tr>
			<th>제목</th>
			<td><input type="text" name='title' value='<?=$banner_data['title']?>' /></td>
		</tr>
		<tr>
			<th>제목 링크</th>
			<td><input type="text" name='title_link' style='width:100%;' value="<?=htmlspecialchars($banner_data['title_link']);?>" /></td>
		</tr>
		<tr>
			<th>모바일이미지링크</th>
			<td><input type="text" name="mobile_img" style="width: 100%" value="<?php echo $banner_data['mobile_img']?>"></td>
		</tr>
        <tr>
            <th>모바일 링크</th>
            <td><input type="text" name='mobile_title_link' style='width:100%;' value="<?=htmlspecialchars($banner_data['mobile_title_link']);?>" /></td>
        </tr>
		<tr>
			<th>내용</th>
			<td><textarea name="contents" rows="10" style='width:100%;'><?=$banner_data['contents']?></textarea></td>
		</tr>
		</tbody>
	</table>
</form>

<script type="text/javascript">

	function banner_submit(f){
		if(f.title.value == ''){
			alert('제목을 입력해 주세요.');
			f.title.focus();
			return false;
		}
		if(f.s_id.value == ''){
			alert('제품관을 선택해 주세요.');
			f.s_id.focus();
			return false;
		}

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
	}

</script>
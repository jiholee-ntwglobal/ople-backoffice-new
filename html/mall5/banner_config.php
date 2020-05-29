<?
## 베너 관리 페이지 2014-04-15 홍민기 ##
include_once("./_common.php");

# 베너관리 접근 가능 계정 #
$permit_arr = array(
	'ghdalsrldi','sun1002a','admin','beeby'
);

if(!$_GET['cron']){
	if(!in_array($_SESSION['ss_mb_id'],$permit_arr)){
		exit;
	}
}


# 사용중인 베너 최신화 #
if($_GET['mode'] == 'banner_update'){
	# 사용 기간 안에 드는 베너 로드  #
	
	# 베너 모두 삭제 #
	mysql_query("delete from banner_table");
	
	$banner_listQ = mysql_query("select * from banner_data where st_dt <= now() and en_dt >= now()");
	while($banner_list = mysql_fetch_array($banner_listQ)){
		mysql_query("
			insert into 
				banner_table 
			(
				b_uid,contents,sort
			)values(
				'".$banner_list['uid']."','".$banner_list['contents']."','".$banner_list['sort']."'
			)
		");
	}

	if($_GET['cron']){
		$xml = new SimpleXMLElement('<xml/>');
		$xml->addChild('row');
		$xml->addChild('row');
		$xml->addChild('row');
		$xml->addChild('row');
		$xml->addChild('row');
		Header('Content-type: text/xml');
		print($xml->asXML());
		exit;
	}
	echo "
		<script>
			alert('적용이 완료되었습니다.');
			location.href='".$_SERVER['PHP_SELF']."';
		</script>
	";
	exit;
}


# 삭제 처리 #
if($_GET['mode'] == 'del'){

	# 베너 데이터 삭제
	$delQ = "
		delete from banner_data where uid = '".$_GET['uid']."'
	";

	# 삭제한 베너가 돌아가고 있다면 삭제 #
	$delQ2 = "
		delete from banner_table where b_uid = '".$_GET['uid']."'
	";

	if(mysql_query($delQ) && mysql_query($delQ2)){
	
		echo "
			<script>
				alert('삭제가 완료되었습니다.');
				location.href='".$_SERVER['PHP_SELF']."';
			</script>
		";
		exit;
	}else{
		echo "
			<script>
				alert('삭제중 오류가 발생했습니다. 다시 시도해 주세요.');
				history.back();
			</script>
		";
		exit;
	}
}


$_GET['view'] = (!$_GET['view']) ? 'Y':$_GET['view'];


switch($_GET['view']){
	case 'Y' : $subQ = "uid in (select b_uid from banner_table)";
		break;
	case 'N' : $subQ = "uid not in (select b_uid from banner_table)";
		break;
}


$subQ = ($subQ) ? ' where '.$subQ : '';

$banner_cntQ = "select count(*) from banner_data";

$banner_cnt_use =  mysql_fetch_array(mysql_query($banner_cntQ." where uid in (select b_uid from banner_table)"));
$banner_cnt_use = $banner_cnt_use[0];

$banner_cnt_end =  mysql_fetch_array(mysql_query($banner_cntQ." where uid not in (select b_uid from banner_table)"));
$banner_cnt_end = $banner_cnt_end[0];

$banner_cnt_all =  mysql_fetch_array(mysql_query($banner_cntQ));
$banner_cnt_all = $banner_cnt_all[0];


$banner_dataQ = mysql_query("select * from banner_data $subQ order by sort asc");


while($banner_data = mysql_fetch_array($banner_dataQ)){
	$banner_data['contents'] = Stripslashes($banner_data['contents']);
	$list_tr .= "
		<tr align='center'>
			<td>".$banner_data['sort']."</td>
			<td>".$banner_data['uid']."</td>
			<td>".$banner_data['contents']."</td>
			<td>".$banner_data['st_dt']."</td>
			<td>".$banner_data['en_dt']."</td>
			<td>".$banner_data['create_dt']."</td>
			<td>
				<a href='banner_write.php?uid=".$banner_data['uid']."'>수정</a>
				<a href='#' onclick=\"banner_del('".$banner_data['uid']."')\">삭제</a>
			</td>
		</tr>
	";
}

?>
<style type="text/css">
.banner_c_tap{
	list-style:none;
	margin:0px;
	padding:0px;
}
.banner_c_tap li{
	float:left;
	padding:5px 10px;
	border:1px solid #dddddd;
	cursor:pointer;
}
.banner_c_tap li.active{
	font-weight:bold;
}
</style>

<ul class='banner_c_tap'>
	<li onclick="location.href='<?=$_SERVER['PHP_SELF'];?>?view=Y'" class='<?=($_GET['view'] == 'Y')?'active':'';?>'>사용중(<?=$banner_cnt_use;?>)</li>
	<li onclick="location.href='<?=$_SERVER['PHP_SELF'];?>?view=N'" class='<?=($_GET['view'] == 'N')?'active':'';?>'>미사용(<?=$banner_cnt_end;?>)</li>
	<li onclick="location.href='<?=$_SERVER['PHP_SELF'];?>?view=ALL'" class='<?=($_GET['view'] == 'ALL')?'active':'';?>'>전체(<?=$banner_cnt_all;?>)</li>
</ul>
<table width='100%;' border='1' style='border-collapse: collapse;'>
	<thead>
		<th>베너노출번호</th>
		<th>베너코드</th>
		<th>내용</th>
		<th>시작시간</th>
		<th>종료시간</th>
		<th>생성일</th>
		<th></th>
	</thead>
	<tbody>
		<?=$list_tr;?>
	</tbody>
</table>
<button onclick='banner_update();'>베너 적용</button>
<button onclick="location.href='banner_write.php'">생성</button>

<script type="text/javascript">
function banner_del( uid ){
	if(!confirm('배너를 삭제하시겠습니까?')){
		return false;
	}

	location.href='<?=$_SERVER['PHP_SELF'];?>?mode=del&uid='+uid;
}

function banner_update(){
	if(!confirm('베너를 적용하시겠습니까')){
		return false;
	}

	location.href='<?=$_SERVER['PHP_SELF'];?>?mode=banner_update';
}
</script>
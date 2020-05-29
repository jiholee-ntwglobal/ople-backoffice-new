<?php
## 베너 관리 페이지 2014-04-15 홍민기 ##
include_once("./_common.php");
include $g4['full_path'].'/head.sub.php';

# 베너관리 접근 가능 계정 #
$permit_arr = array('minheee1202','naya2410','admin','beeby','design','dev','greg_kim');

if(!$_GET['cron']){
	if(!in_array($_SESSION['ss_mb_id'],$permit_arr)){
		exit;
	}
}

if($_GET['mode'] == 'del'){
	$qry = "
		delete from banner_data_new where uid = '".$_GET['uid']."'
	";
	if(!sql_query($qry)){
		alert('처리중 오류 발생! 다시 시도해 주세요.');
		exit;
	}

	alert('삭제가 완료되었습니다.',$_SERVER['PHP_SELF']);
	exit;
}
if($_GET['mode'] == 'banner_update'){
	$replace_search = array('[shop_path]','[path]','[bbs_path]');
	$replace_val = array('[m_shop_path]','[m_path]','[m_bbs_path]');
	$replace_search2 = array('<?=$g4[shop_path]?>','<?=$g4[path]?>','<?=$g4[m_bbs_path]?>','.php');
	$replace_val2 = array('shop','','bbs','.html');
	$sql = sql_query("
		select
			title,title_link,contents,s_id,mobile_img,mobile_title_link
		from banner_data_new
		where '".date('Y-m-d H:i:s')."' between st_dt and en_dt
		order by s_id,sort asc
	");
	while($row = sql_fetch_array($sql)){
		$arr[$row['s_id']][] = $row;
	}
    if ($_SERVER['REMOTE_ADDR'] == "211.214.213.101"){
        echo "<pre>";
        var_dump($arr);
        echo "</pre>";
    }
	if(is_array($arr)){
		foreach($arr as $s_id => $val){
			$contents = $contents2 = '';
			$m_contents = $m_contents2 = $app_contents = $app_contents2 = '';
			if(is_array($val)){
				$i=1;
				foreach($val as $val2){
					# 메인은 HTML 형태가 다름 2015-01-26 홍민기 #
					if($s_id == 6){
						$contents .= "
							<div class='planning_banner_contents'>
								<a href='".$val2['title_link']."'>
									".$val2['contents']."
								</a>
							</div>
						";
						$contents2 .= "
							<li".($i==1 ? " class='Select'":"")." banner_id='".$i."' onclick=\"planning_banner_change(this.getAttribute('banner_id'));\"><a>".$i."</a></li>
						";
						$i++;
					}else{
						if($val2['title_link']){
							$title = "<a href='".$val2['title_link']."'>".$val2['title']."</a>";
						}else{
							$title = $val2['title'];
						}
						$contents .= "<div><span class='MainSpotImgTitle'>".$title."</span>".$val2['contents']."</div>\n";
					}
					if($val2['mobile_img']){
					    $mobile_title_link = ($val2['mobile_title_link']=="") ? $val2['title_link'] : $val2['mobile_title_link'];
						$m_contents .= "<a class=\"swiper-slide\" href=\"".str_replace($replace_search,$replace_val,$mobile_title_link)."\" title=\"".$val2['title']."\"><img src=\"".$val2['mobile_img']."\" /></a>".PHP_EOL;

						if(preg_match_all("/(shop_path|path|bbs_path)/", $mobile_title_link)) {
							$app_contents .= "<a class=\"swiper-slide\" href=\"" . str_replace($replace_search2, $replace_val2, $mobile_title_link) . "\" title=\"" . $val2['title'] . "\"><img src=\"" . $val2['mobile_img'] . "\" /></a>" . PHP_EOL;
						}
					}

				}
			}
			if($contents){
				# 메인은 HTML 형태가 다름 2015-01-26 홍민기 #
				if($s_id == 6){
					$contents = "<div class='planning_banner_mask'>".$contents."</div>
					<ul class='listing_button'>".$contents2."</ul>
					";
				}
				$file = fopen($g4['path'].'/cache/rolling_banner_'.$s_id.'.htm','w+');


				fwrite($file,$contents);
				fclose($file);


				$file = fopen($g4['path'].'/cache/rolling_banner_'.$s_id.'.htm','r');

				if($_SERVER['REMOTE_ADDR']=='211.214.213.101') {
//				    print_r($g4['front_ftp_server']);
//				    print_r($g4['front_ftp_user_name']);
//				    print_r($g4['front_ftp_user_pass']);
//                    $conn_id = ftp_connect($g4['front_ftp_server']);
//                    $login_result = ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);
//                    ftp_pasv($conn_id, true);
//                    ftp_fput($conn_id, '/ssd/html/mall5/cache/rolling_banner_' . $s_id . '.htm', $file, FTP_BINARY);
//
//                    ftp_close($conn_id);
//                    exit;
				}

				$conn_id = @ftp_connect($g4['front_ftp_server']);
				$login_result = @ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);
				ftp_pasv($conn_id, true);
				@ftp_fput($conn_id, '/ssd/html/mall5/cache/rolling_banner_' . $s_id . '.htm', $file, FTP_BINARY);

				@ftp_close($conn_id);

				fclose($file);


			}
			if($m_contents){



				$m_contents2 = "
					<section class=\"MainBannerSpot\">
						<div class=\"main_banner_swiper swiper-container\">
							<div class=\"swiper-wrapper\">
								".$m_contents."
							</div>
							<div class=\"main_banner_pagination swiper-pagination\"></div>
						</div>
					</section>
				";
				$file = fopen($g4['path'].'/cache/m_rolling_banner_'.$s_id.'.htm','w+');
				fwrite($file,$m_contents2);
				fclose($file);

				$file = fopen($g4['path'].'/cache/m_rolling_banner_'.$s_id.'.htm','r');

				$conn_id = @ftp_connect($g4['front_ftp_server']);
				$login_result = @ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);
				ftp_pasv($conn_id, true);
				@ftp_fput($conn_id, '/ssd/html/mall5/cache/m_rolling_banner_'.$s_id.'.htm', $file, FTP_BINARY);

				@ftp_close($conn_id);

				fclose($file);
			}

			if($app_contents){



				$app_contents2 = "
					<section class=\"MainBannerSpot\">
						<div class=\"main_banner_swiper swiper-container\">
							<div class=\"swiper-wrapper\">
								".$app_contents."
							</div>
							<div class=\"main_banner_pagination swiper-pagination\"></div>
						</div>
					</section>
				";
				$file = fopen($g4['path'].'/cache/app_rolling_banner_'.$s_id.'.htm','w+');
				fwrite($file,$app_contents2);
				fclose($file);

				$file = fopen($g4['path'].'/cache/app_rolling_banner_'.$s_id.'.htm','r');

				$conn_id = @ftp_connect($g4['front_ftp_server']);
				$login_result = @ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);
				ftp_pasv($conn_id, true);
				@ftp_fput($conn_id, '/ssd/html/mall5/cache/app_rolling_banner_'.$s_id.'.htm', $file, FTP_BINARY);

				@ftp_close($conn_id);

				fclose($file);
			}

		}
	}
	alert('처리가 완료되었습니다.', $_SERVER['PHP_SELF']);
	exit;
}

# 제품관 리스트 로드 #
$st_qry = sql_query("
	select
		s_id,name
	from
		yc4_station
	order by sort
");
while($row = sql_fetch_array($st_qry)){
	$st_li .= "<li class='".($row['s_id'] == $_GET['s_id'] ? "active":"")."' onclick=\"location.href='".$_SERVER['PHP_SELF']."?view=".$_GET['view']."&s_id=".$row['s_id']."'\">".$row['name']."</li>";
}

$st_li .= "<li class='".($_GET['s_id'] == 10 ? "active":"")."' onclick=\"location.href='".$_SERVER['PHP_SELF']."?view=".$_GET['view']."&s_id=10'\">아이행복</li>";



# 베너 리스트 로드 #
switch($_GET['view']){
	case 'Y' :
		$where .= ($where ? " and ":" where "). "'".date('Y-m-d H:i:s')."' between a.st_dt and a.en_dt";
		break;
	case 'N' :
		$where .= ($where ? " and ":" where "). "not '".date('Y-m-d H:i:s')."' between a.st_dt and a.en_dt";
		break;
	case 'ALL' :
		break;
}

if($_GET['s_id']){
	$where .= ($where ? " and ":" where ")."a.s_id = '".$_GET['s_id']."'";
}

$sql = sql_query($a="
	select
		a.*,
		b.name
	from
		banner_data_new a
		left join
		yc4_station b on a.s_id = b.s_id
	".$where."
	order by a.sort
");

while($row = sql_fetch_array($sql)){
	$list_tr .= "
		<tr>
			<td>".$row['sort']."</td>
			<td>".$row['uid']."</td>
			<td>".$row['contents']."</td>
			<td rowspan='2'>".$row['st_dt']."</td>
			<td rowspan='2'>".$row['en_dt']."</td>
			<td rowspan='2'>".$row['create_dt']."</td>
			<td rowspan='2'>
				<a href='banner_write_new.php?uid=".$row['uid']."'>수정</a>
				<a href='#' onclick=\"banner_del('".$row['uid']."')\">삭제</a>
			</td>
		</tr>
		<tr>
			<td colspan='2'>".$row['name']."</td>
			<td>".$row['title']."</td>
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
	<li onclick="location.href='<?=$_SERVER['PHP_SELF'];?>?view=Y&s_id=<?=$_GET['s_id']?>'" class='<?=($_GET['view'] == 'Y')?'active':'';?>'>사용중</li>
	<li onclick="location.href='<?=$_SERVER['PHP_SELF'];?>?view=N&s_id=<?=$_GET['s_id']?>'" class='<?=($_GET['view'] == 'N')?'active':'';?>'>미사용</li>
	<li onclick="location.href='<?=$_SERVER['PHP_SELF'];?>?view=ALL&s_id=<?=$_GET['s_id']?>'" class='<?=($_GET['view'] == 'ALL')?'active':'';?>'>전체</li>
</ul>
<div style='clear:both;'></div>
<ul class='banner_c_tap'>
	<?=$st_li?>
</ul>
<button onclick='banner_update();'>베너 적용</button>
<button onclick="location.href='banner_write_new.php'">생성</button>

<table width='100%;' border='1' style='border-collapse: collapse;'>
	<thead>
	<tr>
		<th>베너노출번호</th>
		<th>베너코드</th>
		<th>내용</th>
		<th>시작시간</th>
		<th>종료시간</th>
		<th>생성일</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
	<?=$list_tr;?>
	</tbody>
</table>

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
<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2019-04-05
 * File: hana_bigdata_10shop.php
 */

$sub_menu = "500560";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "하나빅데이터 서프라이즈샵";

function get_link($str){
	$get_str = $_GET;
	unset($get_str[$str]);
	return $get_str = http_build_query($get_str);
}

function showpage($npage, $totalrec, $nblocksize, $npagesize, $surl, $sget = "") {
	$str = "";
	$nblock				= ceil($npage/$nblocksize);						//현재블록
	$prev_start_page	= (((int)(($npage-1-$nblocksize)/$nblocksize))*$nblocksize)+1;	//이전블록 시작페이지
	$next_start_page	= (((int)(($npage-1+$nblocksize)/$nblocksize))*$nblocksize)+1;	//다음블록 시작페이지
	$startpage			= (((int)(($npage-1)/$nblocksize))*$nblocksize)+1;				//링크시작페이지
	$endpage			= $startpage+$nblocksize-1;										//링크 끝페이지
	$totalpage			= ceil($totalrec/$npagesize);									//전체페이지
	$totalblock			= ceil($totalpage/$nblocksize);									//전체 블록
	
	// 처음으로
	if ($npage == 1) {
		$str = "";
	}
	else {
		$str = "<a href='".$surl;
		$str.= "?page=1&".$sget."'>처음</a>";
	}

	// 이전
	if ($nblock == 1) {
		$str.= "";
	}
	else {
		$str.= "<a href=".$surl;
		$str.= "?page=".$prev_start_page."&".$sget;
		$str.= ">이전</a>&nbsp;";
	}

	// 페이지표시
	for ($i = $startpage; $i <= $endpage && $i <= $totalpage; $i++) {
		if ($i == $npage) {
			$str.= "<b>".$i."</b>&nbsp;";
		}
		else {
			$str.= "<a href=".$surl;
			$str.= "?page=".$i."&".$sget;
			$str.= ">[".$i."]</a>&nbsp;";
		}
	}

	// 다음
	if ($nblock == $totalblock) {
		$str.= "";
	}
	else {
		$str.= "<a href=".$surl;
		$str.= "?page=".$next_start_page."&".$sget;
		$str.= ">다음</a>&nbsp;";
	}

	// 끝으로
	if ($npage == $totalpage) {
		$str.= "";
	}
	else {
		$str.= "<a href=".$surl;
		$str.= "?page=".$totalpage."&".$sget;
		$str.= ">끝</a>&nbsp;";
	}
	if($totalrec == "0"){
		$str	= "표시할 페이지가 없습니다.";
	}
	return $str;
}

define('bootstrap', true);

include '../admin.head.php';



// TODO 페이징작업
$page		= ( isset($_GET['page']) ) ? $_GET['page'] : "1";
$cnt_sql	= sql_query("SELECT count(*) FROM yc4_event_data WHERE ev_code='hana_bigdata_2019' AND ev_data_type='ten_shop_info'");
$cnt		= mysql_result($cnt_sql,0,0);
if($cnt == "0"){
	echo "
		<script type=text/JavaScript>
			alert('결과가 존재하지 않습니다.')
		</script>
	";
}

//현재페이지시작게시물	=	((현재페이지-1)*게시물수)+1
if(($page > ceil($cnt/30)) && ($page != "1")){
$page = ceil($cnt/20);
}
$start_list	= (($page-1)*30);

// 활성화/비활성화 보기


$sql	= "SELECT
			uid, value3 AS 'month', value1 AS 'st_dt', value2 AS 'en_dt'
		FROM
			yc4_event_data
		WHERE
			ev_code='hana_bigdata_2019' AND ev_data_type='ten_shop_info'
		LIMIT
			".$start_list.", 30
";
//	TODO 정렬/날짜별/날짜입력필요(테이블에 날짜컬럼 추가함)
//		ORDER BY
//			dt DESC

$res	= sql_query($sql);
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<script type="text/javascript">
function button_event(a,b,c){
	if (confirm("정말 " + b + "하시겠습니까??") == true){
		location.replace("./eventresearch_action.php?seqno=" + a + "&mode=" + c);
	}else{
		return;
	}
}
</script>

<table class="table table-hover table-bordered table-condensed table-striped">
	<thead>
		<tr>
			<th colspan="5" style="text-align: center;">하나 빅데이터 10달러샵 관리</th>
<!--			<th colspan="2" style="text-align: center;">-->
<!--				<button class="btn btn-primary" type="button" onclick="">추가</button>-->
<!--			</th>-->
<!--			<th style="text-align: center;">-->
<!--				<button class="btn btn-info" type="button" onclick="" />보기</button>-->
<!--			</th>-->
		</tr>
		<tr>
			<td style="width:20%" align="center">진행월</td>
			<td style="width:20%" align="center">시작날짜</td>
			<td style="width:20%" align="center">종료날짜</td>
			<td style="width:20%" align="center">상세보기</td>
			<td style="width:20%" align="center">판매통계</td>
<!--			<td align="center">수정</td>-->
		</tr>
	</thead>
	<tbody>
	<?php
	while($row=mysql_fetch_assoc($res)){
	?>
		<tr>
			<td align="center"><?php echo date('Y-m', strtotime($row['st_dt']));?></td>
			<td align="center"><?php echo date('Y-m-d', strtotime($row['st_dt']));?></td>
			<td align="center"><?php echo date('Y-m-d', strtotime($row['en_dt']));?></td>
			<td align="center"><a href="./hana_bigdata_10shop_detail.php?uid=<?php echo $row['uid'];?>" target="blank">상세보기</a></td>
			<td align="center"><a href="./hana_bigdata_10shop_static.php?uid=<?php echo $row['uid'];?>" target="blank">통계보기</a></td>
<!--			<td align="center"><a href="./eventresearch_write.php?mode=rewrite&seqno=--><?php //echo $row['uid'];?><!--">수정</a></td>-->
		</tr>
	<?php } ?>
<!--		<tr>-->
<!--			<td colspan="6"align="center">-->
<!--			--><?php //echo showpage($page, $cnt, 10, 30, "./eventresearch_list.php", get_link("page"))?>
<!--			</td>-->
<!--		</tr>-->
	</tbody>
</table>
<?php 
include_once ("$g4[admin_path]/admin.tail.php");
?>
<?php
/**select * from samsung_5v2_report
 * Created by Eclipse
 * User: kyung-in
 * Date: 2015.09.09
 * file: test_2/ev_list.php
 */
$sub_menu = "500800";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "이벤트통계관리";
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

$vmode	= "Y";
$vstr	= "비활성";
$vstr2	= "활성";
if(isset($_GET['vmode']) && $_GET['vmode'] == "Y"){
	$vmode	= "N";	
	$vstr	= "활성";
	$vstr2	= "비활성";
}

// TODO 페이징작업
$page		= ( isset($_GET['page']) ) ? $_GET['page'] : "1";
$cnt_sql= sql_query("select count(*) as cnt from event_research where stat='".$vmode."'");
$cnt	= mysql_result($cnt_sql,0,0); 
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
			*
		FROM
			event_research
		WHERE
			stat='".$vmode."'
		limit
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
			<th colspan="3" style="text-align: center;">이벤트 통계 관리(<?php echo $vstr2;?>)</th>
			<th colspan="2" style="text-align: center;">
				<button class="btn btn-primary" type="button" onclick="location.href='./eventresearch_write.php'">추가</button>
			</th>
			<th style="text-align: center;">
				<button class="btn btn-info" type="button" onclick="location.href='./eventresearch_list.php?vmode=<?php echo $vmode;?>'" /><?php echo $vstr;?>보기</button>
			</th>
		</tr>
		<tr>
			<td align="center">이벤트이름</td>
			<td align="center">시작날짜</td>
			<td align="center">종료날짜</td>
			<td align="center">링크</td>
			<td align="center">수정</td>
			<td align="center">활성/비활성</td>
		</tr>
	</thead>
	<tbody>
	<?php
	while($row=mysql_fetch_assoc($res)){
	?>
		<tr>
			<td><?php echo $row['ev_name'];?></td>
			<td align="center"><?php echo $row['st_dt'];?></td>
			<td align="center"><?php echo $row['ed_dt'];?></td>
			<td align="center"><a href="./eventresearch_view.php?seqno=<?php echo $row['seqno'];?>" target="blank">통계보기</a></td>
			<td align="center"><a href="./eventresearch_write.php?mode=rewrite&seqno=<?php echo $row['seqno'];?>">수정</a></td>
			<td align="center"><a href="#" onclick="button_event('<?php echo $row['seqno'];?>', '<?php echo $vstr;?>', '<?php echo $vmode;?>')"><?php echo $vstr;?>하기</a></td>
		</tr>
	<?php } ?>
		<tr>
			<td colspan="6"align="center">
			<?php echo showpage($page, $cnt, 10, 30, "./eventresearch_list.php", get_link("page"))?>
			</td>
		</tr>
	</tbody>
</table>
<?php 
include_once ("$g4[admin_path]/admin.tail.php");
?>
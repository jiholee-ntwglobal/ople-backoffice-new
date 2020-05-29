<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가



$sop = strtolower($sop);
if ($sop != "and" && $sop != "or")
    $sop = "and";

// 김선용 201108 : 관리자가 아닌경우 mb_id, wr_name 컬럼만 검색허용
if(!$is_admin && !preg_match('/(mb_id|wr_name)/', $sfl)) {
	$sfl = "";
	$stx = "";
}

// 분류 선택 또는 검색어가 있다면
$stx = trim($stx);
if ($sca || $stx)
{
    $sql_search = get_sql_search($sca, $sfl, $stx, $sop);

    // 가장 작은 번호를 얻어서 변수에 저장 (하단의 페이징에서 사용)
    $sql = " select MIN(uid) as min_wr_num from yc4_personal_qa ";
    $row = sql_fetch($sql);
    $min_spt = $row[min_wr_num];

    if (!$spt) $spt = $min_spt;

    $sql_search .= " and (uid between '".$spt."' and '".($spt + $config[cf_search_part])."') ";

    // 원글만 얻는다. (코멘트의 내용도 검색하기 위함)
    //$sql = " select distinct wr_parent from $write_table where $sql_search ";
	$sql = " select count(distinct uid) as cnt from yc4_personal_qa where  depth=0 and mb_id='{$member[mb_id]}' $sql_search "; 
    /*
	$result = sql_query($sql);
    $total_count = mysql_num_rows($result);
	*/
	$total_count = sql_fetch($sql);
	$total_count = $total_count['cnt'];
}
else
{
    $sql_search = "";

    $sql = " select count(distinct uid) as cnt from yc4_personal_qa where  depth=0 and mb_id='{$member[mb_id]}' $sql_search "; 
    /*
	$result = sql_query($sql);
    $total_count = mysql_num_rows($result);
	*/
	$total_count = sql_fetch($sql);
	$total_count = $total_count['cnt'];
}


$total_page  = ceil($total_count / 10);  // 전체 페이지 계산
if (!$page) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) *20; // 시작 열을 구함


// 정렬에 사용하는 QUERY_STRING
$qstr2 = "bo_table=$bo_table&sop=$sop";



    
	$sql = " 
			select
				*,date_format(create_dt,'%Y.%m.%d') as create_date
			from 
				yc4_personal_qa 
			where 
				 depth=0 and mb_id='$member[mb_id]'
				$sql_order 
			order by create_dt desc
			limit $from_record, 20 
			
		";

$result = sql_query($sql);

// 년도 2자리
$today2 = $g4[time_ymd];

$list = array();
$i = 0;
/*
if (!$sca && !$stx)
{
    $arr_notice = explode("\n", trim($board[bo_notice]));
    for ($k=0; $k<count($arr_notice); $k++)
    {
        if (trim($arr_notice[$k])=='') continue;

        $row = sql_fetch(" select * from $write_table where wr_id = '$arr_notice[$k]' ");

        if (!$row[wr_id]) continue;

        $list[$i] = get_list($row, $board, $board_skin_path, $board[bo_subject_len]);
        $list[$i][is_notice] = true;

        $i++;
    }
}
*/

$k = 0;

while ($row = sql_fetch_array($result))
{
    
    $list[$i] = $row;
    if (strstr($sfl, "subject"))
        $list[$i][subject] = search_font($stx, $list[$i][subject]);
   
    $list[$i][num] = $total_count - ($page - 1) * $board[bo_page_rows] - $k;

    $i++;
    $k++;
}

$write_pages = get_paging($config[cf_write_pages], $page, $total_page, "./board_personnel_qa.php?".$qstr."&page=");

$list_href = '';
$prev_part_href = '';
$next_part_href = '';
if ($sca || $stx)
{
    $list_href = "./board_personnel_qa.php?";

    //if ($prev_spt >= $min_spt)
    $prev_spt = $spt - $config[cf_search_part];
    if (isset($min_spt) && $prev_spt >= $min_spt)
        $prev_part_href = "./board_personnel_qa.php?".$qstr."&spt=$prev_spt&page=1";

    $next_spt = $spt + $config[cf_search_part];
    if ($next_spt < 0)
        $next_part_href = "./board_personnel_qa.php?".$qstr."&spt=$next_spt&page=1";
}

 $write_href = "./board_personnel_qa.php?mode=write";

$nobr_begin = $nobr_end = "";
if (preg_match("/gecko|firefox/i", $_SERVER['HTTP_USER_AGENT'])) {
    $nobr_begin = "<nobr style='display:block; overflow:hidden;'>";
    $nobr_end   = "</nobr>";
}

$board_skin_path = "../skin/board/personnel_qa";

$width = '100%';

$stx = get_text(stripslashes($stx));
include_once("../skin/board/personnel_qa/list.skin.php");

// 김선용 201204 : 작업중
//if($member['mb_id'] === 'coolina'){
//	$sec = get_microtime() - $begin_time;
//	echo '처리시간 : '.$sec;
//}
?>

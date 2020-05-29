<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($_GET['bo_table'] == 'qa'){ // 고객센터 게시판 select 필드 분기 2014-03-27 홍민기
		$sql_column = "wr_id,wr_subject,wr_name,mb_id,wr_datetime,wr_hit,wr_reply,wr_option,wr_comment";

}else{
	$sql_column = "*";
}

// 분류 사용 여부
$is_category = false;
if ($board[bo_use_category])
{
    $is_category = true;
    $category_location = "./board.php?bo_table=$bo_table&sca=";
    $category_option = get_category_option($bo_table); // SELECT OPTION 태그로 넘겨받음
}

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
    $sql = " select MIN(wr_num) as min_wr_num from $write_table ";
    $row = sql_fetch($sql);
    $min_spt = $row[min_wr_num];

    if (!$spt) $spt = $min_spt;

    $sql_search .= " and (wr_num between '".$spt."' and '".($spt + $config[cf_search_part])."') ";

    // 원글만 얻는다. (코멘트의 내용도 검색하기 위함)
    //$sql = " select distinct wr_parent from $write_table where $sql_search ";
	$sql = " select count(distinct wr_parent) as cnt from $write_table where $sql_search ";
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

    $total_count = $board[bo_count_write];
}


$total_page  = ceil($total_count / $board[bo_page_rows]);  // 전체 페이지 계산
if (!$page) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $board[bo_page_rows]; // 시작 열을 구함

// 관리자라면 CheckBox 보임
$is_checkbox = false;
if ($member[mb_id] && ($is_admin == "super" || $group[gr_admin] == $member[mb_id] || $board[bo_admin] == $member[mb_id]))
    $is_checkbox = true;

// 정렬에 사용하는 QUERY_STRING
$qstr2 = "bo_table=$bo_table&sop=$sop";

if ($board[bo_gallery_cols])
    $td_width = (int)(100 / $board[bo_gallery_cols]);

// 정렬
// 인덱스 필드가 아니면 정렬에 사용하지 않음
//if (!$sst || ($sst && !(strstr($sst, 'wr_id') || strstr($sst, "wr_datetime")))) {
if (!$sst)
{
    if ($board[bo_sort_field])
        $sst = $board[bo_sort_field];
    else
        $sst  = "wr_num, wr_reply";
    $sod = "";
}else {
    $sst = preg_match("/^(wr_datetime|wr_hit|wr_good|wr_nogood)$/i", $sst) ? $sst : "";
}
if ($sst) $sql_order = " order by $sst $sod ";

if ($sca || $stx)
{
    $sql = " select distinct wr_parent from $write_table where $sql_search $sql_order limit $from_record, $board[bo_page_rows] ";
}
else
{

	$sql = "
			select
				".$sql_column."
			from
				$write_table
			where
				wr_is_comment = 0
				and wr_id > 0
				$sql_order
			limit $from_record, $board[bo_page_rows]
		";
}
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

if (!$sca && !$stx)
{
    $arr_notice = explode("\n", trim($board[bo_notice]));
    $arr_notice_count = count($arr_notice);

    if ($arr_notice_count > 0) { // 공지사항이 있는 경우

        $sql_case = " ";
        $j = 0;
        for ($k=0; $k<$arr_notice_count; $k++)
        {
            if (trim($arr_notice[$k]) == '')
              continue;

            $sql_case .= " when " . $arr_notice[$k] . " then " . $k ;
            if ($j == 0)
              $sql_where = " wr_id = " . $arr_notice[$k] . " ";
            else
              $sql_where .= " or wr_id = " . $arr_notice[$k] . " ";
            $j++;
        } // end of for

        if ($j > 0) {
            $sql = " select $sql_column, case wr_id $sql_case else 10000 end as fsort from $write_table where $sql_where order by fsort,wr_num, wr_reply ";
            $result_notice = sql_query($sql);

            while ($row_notice = sql_fetch_array($result_notice))
            {
                if (!$row_notice['wr_id']) continue;

                $list[$i] = get_list($row_notice, $board, $board_skin_path, $board[bo_subject_len]);
                $list[$i][is_notice] = true;

                $i++;
            } // end of while
        } // end of if $j > 0

    } // end of if $arr_notice_count > 0
}

$k = 0;

while ($row = sql_fetch_array($result))
{
    // 검색일 경우 wr_id만 얻었으므로 다시 한행을 얻는다
    if ($sca || $stx)
        $row = sql_fetch(" select $sql_column from $write_table where wr_id = '$row[wr_parent]' ");

    $list[$i] = get_list($row, $board, $board_skin_path, $board[bo_subject_len]);
    if (strstr($sfl, "subject"))
        $list[$i][subject] = search_font($stx, $list[$i][subject]);
    $list[$i][is_notice] = false;
    //$list[$i][num] = number_format($total_count - ($page - 1) * $board[bo_page_rows] - $k);
    $list[$i][num] = $total_count - ($page - 1) * $board[bo_page_rows] - $k;

    $i++;
    $k++;
}

$write_pages = get_paging($config[cf_write_pages], $page, $total_page, "./board.php?bo_table=$bo_table".$qstr."&page=");

$list_href = '';
$prev_part_href = '';
$next_part_href = '';
if ($sca || $stx)
{
    $list_href = "./board.php?bo_table=$bo_table";

    //if ($prev_spt >= $min_spt)
    $prev_spt = $spt - $config[cf_search_part];
    if (isset($min_spt) && $prev_spt >= $min_spt)
        $prev_part_href = "./board.php?bo_table=$bo_table".$qstr."&spt=$prev_spt&page=1";

    $next_spt = $spt + $config[cf_search_part];
    if ($next_spt < 0)
        $next_part_href = "./board.php?bo_table=$bo_table".$qstr."&spt=$next_spt&page=1";
}

$write_href = "";
if ($member[mb_level] >= $board[bo_write_level])
    $write_href = "./write.php?bo_table=$bo_table";

$nobr_begin = $nobr_end = "";
if (preg_match("/gecko|firefox/i", $_SERVER['HTTP_USER_AGENT'])) {
    $nobr_begin = "<nobr style='display:block; overflow:hidden;'>";
    $nobr_end   = "</nobr>";
}

// RSS 보기 사용에 체크가 되어 있어야 RSS 보기 가능 061106
$rss_href = "";
if ($board[bo_use_rss_view])
    $rss_href = "./rss.php?bo_table=$bo_table";

$stx = get_text(stripslashes($stx));
include_once("$board_skin_path/list.skin.php");

// 김선용 201204 : 작업중
//if($member['mb_id'] === 'coolina'){
//	$sec = get_microtime() - $begin_time;
//	echo '처리시간 : '.$sec;
//}
?>

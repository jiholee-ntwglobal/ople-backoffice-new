<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if (defined('IS_MOBILE')) {
    $board_skin_path = '../../m/skin/board/' . $_GET['bo_table'];

}
//echo $write_table;
if (trim($write_table)) { //곽범석 추가
    $write_tabless= $write_table;
    $write_table .= " a";
}
$ymds = '';
if ($ymd) { //곽범석 추가2
    $ymds = " and date_format(a.wr_datetime,'%Y-%m-%d') ='$ymd'";
}
if($question){
    if($question=='goods'){
        $questions=" and ca_name ='상품문의' ";
    }elseif($question=='normal'){
        $questions=" and wr_1='' ";
    }
}
if ($_GET['bo_table'] == 'qa') { // 고객센터 게시판 select 필드 분기 2014-03-27 홍민기  //곽범석 2개컬럼 추가
    if ($customer_question) {
        $sql_column = "wr_id,wr_subject,wr_name,mb_id,wr_datetime,wr_hit,wr_reply,wr_option,wr_comment, wr_content,ca_name,wr_1";
    } else {
        $sql_column = "wr_id,wr_subject,wr_name,mb_id,wr_datetime,wr_hit,wr_reply,wr_option,wr_comment, wr_content";
    }


} else {
    $sql_column = "*";
}


# 관리자 체크 #
$admin_chk = sql_fetch("select count(*) as cnt from " . $g4['auth_table'] . " where mb_id = '" . $member['mb_id'] . "'");

if ($admin_chk['cnt'] > 0) {
    $is_admin = 'super';
}

// 분류 사용 여부
$is_category = false;
if ($board[bo_use_category]) {
    $is_category = true;
    $category_location = "./board2.php?bo_table=$bo_table&sca=";
    $category_option = get_category_option($bo_table); // SELECT OPTION 태그로 넘겨받음
}

$sop = strtolower($sop);
if ($sop != "and" && $sop != "or")
    $sop = "and";

// 김선용 201108 : 관리자가 아닌경우 mb_id, wr_name 컬럼만 검색허용
if (!$is_admin && !preg_match('/(mb_id|wr_name)/', $sfl)) {
    $sfl = "";
    $stx = "";
}

// 분류 선택 또는 검색어가 있다면.

$stx = trim($stx);
if ($sca || $stx) {

    $sql_search = get_sql_search($sca, $sfl, $stx, $sop);
    $sql_search = str_replace("wr_is_comment", "a.wr_is_comment", $sql_search);


    // 가장 작은 번호를 얻어서 변수에 저장 (하단의 페이징에서 사용)
    $sql = " select MIN(wr_num) as min_wr_num from $write_table ";
    $row = sql_fetch($sql);
    $min_spt = $row[min_wr_num];

    if (!$spt) $spt = $min_spt;

    // 이게 왜 필요한거지? - 2015-02-03 이성용
    //$sql_search .= " and (wr_num between '".$spt."' and '".($spt + $config[cf_search_part])." ";

    // 원글만 얻는다. (코멘트의 내용도 검색하기 위함)
    //$sql = " select distinct wr_parent from $write_table where $sql_search ";
    /*if ($re == 'Y') { // 답변 검색 카운트 곽범석
        $sql ="select count(*) cnt
                from g4_write_qa a
                where
                $sql_search
                and wr_is_comment = 0
                and a.wr_num in (select wr_num from g4_write_qa b where a.wr_num = b.wr_num and b.wr_is_comment = 0 and length(b.wr_reply) = 1)
                $ymds
                ";

    } else*/if ($re == 'N') { //미답변 검색 카운트
        $sql = "select count(*) cnt
                from $write_table
                where
                $sql_search
                and wr_is_comment = 0
                and a.wr_num not in (select wr_num from $write_tabless b where a.wr_num = b.wr_num and b.wr_is_comment = 0 and length(b.wr_reply) = 1)
                $ymds
                $questions
                ";
    } else {//전체 검색 카운트
        $sql = "select count(*) cnt
                from $write_table
                where
                $sql_search
                and wr_is_comment = 0
                $ymds
                $questions
                ";
    }
    /*
	$result = sql_query($sql);
    $total_count = mysql_num_rows($result);
	*/
    $total_count = sql_fetch($sql);
    $total_count = $total_count['cnt'];
} else {

    if ($is_admin) {
        $sql_search = "";
        /*if ($re == 'Y') { //전체 답변 카운트
            $sql="    select
                      count(*) cnt
                      from
                      g4_write_qa a
                      where
                      wr_is_comment = 0
                      and a.wr_num in (select wr_num from g4_write_qa b where a.wr_num = b.wr_num and b.wr_is_comment = 0 and length(b.wr_reply) = 1)
                      $ymds";
            $sql_cnt = sql_fetch($sql);
            $total_count = $sql_cnt['cnt'];
        } else*/if ($re == 'N') { //전체 미답변 카운트
            $sql = "select
                      count(*) cnt
                      from
                      $write_table
                      where
                      wr_is_comment = 0
                      and a.wr_num not in (select wr_num from $write_tabless b where a.wr_num = b.wr_num and b.wr_is_comment = 0 and length(b.wr_reply) = 1)
                      $ymds
                      $questions
                      ";
            $sql_cnt = sql_fetch($sql);
            $total_count = $sql_cnt['cnt'];
        } else {
            if ($questions) { //전체 카운트 곽범석 추가
                $sql = "select
                      count(*) cnt
                      from
                      $write_table
                      where
                      wr_is_comment = 0
                      $questions
                      ";
                $sql_cnt = sql_fetch($sql);
                $total_count = $sql_cnt['cnt'];

            } else {
                $sql="select count(*) cnt from $write_table where wr_is_comment=0";
                $sql_cnt = sql_fetch($sql);
                $total_count = $sql_cnt['cnt'];
               // $total_count = $board[bo_count_write];
            }
        }
    } else {
        if ($member['mb_id']) {
            $sql = "
				select count(*) as cnt from $write_table  where mb_id = '" . $member['mb_id'] . "'
			";
        } else {
            $sql = "
				select count(*) as cnt from $write_table  where false
			";
            if ($_SESSION['login_email']) {
                $sql = "
					select count(*) as cnt from $write_table  where wr_email = '" . $_SESSION['login_email'] . "' and wr_password = '" . sql_password($_SESSION['wr_password']) . "'
				";
            }
        }
        $sql_cnt = sql_fetch($sql);
        $total_count = $sql_cnt['cnt'];
    }
}


$total_page = ceil($total_count / $board[bo_page_rows]);  // 전체 페이지 계산
if (!$page) {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
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
if (!$sst) {
    if ($board[bo_sort_field])
        $sst = $board[bo_sort_field];
    else
        $sst = "wr_num, wr_reply"; //곽범석 추가
    $sod = "";
} else {
    $sst = preg_match("/^(wr_datetime|wr_hit|wr_good|wr_nogood)$/i", $sst) ? $sst : "";
}
if ($sst) $sql_order = " order by $sst $sod ";

if ($sca || $stx) {
    if ($re == 'N') { //미답변 검색 row
        $sql = "select
                wr_parent
                from
                $write_table
                where
                $sql_search
                and wr_is_comment = 0
                and a.wr_num not in (select wr_num from $write_tabless b where a.wr_num = b.wr_num and b.wr_is_comment = 0 and length(b.wr_reply) = 1)
                $ymds
                $questions
                order by a.wr_num, a.wr_reply
                limit $from_record, $board[bo_page_rows] ";

    }/* elseif ($re == 'Y') { // 답변 검색 row 곽범석

    $sql ="select
                wr_parent
                from
                g4_write_qa a
                where
                $sql_search
                and wr_is_comment = 0
                and a.wr_num in (select wr_num from g4_write_qa b where a.wr_num = b.wr_num and b.wr_is_comment = 0 and length(b.wr_reply) = 1)
                $ymds
                order by a.wr_num, a.wr_reply
                limit $from_record, $board[bo_page_rows]  ";

    } */else { //모든글 전체 검색 row

            $sql = " select  DISTINCT wr_parent from $write_table where $sql_search and wr_is_comment = 0  $ymds  $questions order by a.wr_num, a.wr_reply  limit $from_record, $board[bo_page_rows]";

    }
//    if($_SERVER['REMOTE_ADDR']=="112.218.8.102"){
//        echo $sql;
//    }
} else {

    if ($is_admin) {

        if ($re == 'N') { //모든글 미답변 row
            $sql = "select a.wr_id,
                            a.wr_subject,
                            a.wr_name,
                            a.mb_id,
                            a.wr_datetime,
                            a.wr_hit,
                            a.wr_reply,
                            a.wr_option,
                            a.wr_comment,
                            a.wr_content,
                            a.ca_name,
                            a.wr_1
                    FROM $write_table
                    where
                    wr_is_comment = 0
                    and a.wr_num not in (select wr_num from $write_tabless b where a.wr_num = b.wr_num and b.wr_is_comment = 0 and length(b.wr_reply) = 1)
                    $ymds
                    $questions
                    order by a.wr_num, a.wr_reply
                    limit $from_record, $board[bo_page_rows]
                    ";

        }/* elseif ($re == 'Y') { //전체 답변 곽범석

                $sql ="select a.wr_id,
                            a.wr_subject,
                            a.wr_name,
                            a.mb_id,
                            a.wr_datetime,
                            a.wr_hit,
                            a.wr_reply,
                            a.wr_option,
                            a.wr_comment,
                            a.wr_content,
                            a.ca_name,
                            a.wr_1
                    FROM g4_write_qa a
                    where
                    wr_is_comment = 0
                    and a.wr_num and a.wr_num in (select wr_num from g4_write_qa b where a.wr_num = b.wr_num and b.wr_is_comment = 0 and length(b.wr_reply) = 1)
                    $ymds
                    order by a.wr_num, a.wr_reply
                    limit $from_record, $board[bo_page_rows]
                    ";


        } */else { //전체

            $sql = "
			select
				" . $sql_column . "
			from
				$write_table
			where
				wr_is_comment = 0
				$ymds
				$questions
				$sql_order
			limit $from_record, $board[bo_page_rows]";



        }

    } else {
        if ($member['mb_id']) {
            $sql = sql_query("
					select
						wr_num
					from
						$write_table
					where
						wr_is_comment = 0
						and wr_id > 0
						and mb_id = '" . $member['mb_id'] . "'
						$sql_order
					limit $from_record, $board[bo_page_rows]
				");

        } else {
            $sql = sql_query("
					select
						wr_num
					from
						$write_table
					where
						false
					limit 0
				");

            if ($_SESSION['login_email']) {
                $sql = sql_query($a = "
					select
						wr_num
					from
						$write_table
					where
						wr_is_comment = 0
						and wr_id > 0
						and mb_id = ''
						and wr_email = '" . $_SESSION['login_email'] . "'
						and wr_password = '" . sql_password($_SESSION['wr_password']) . "'
						$sql_order
					limit $from_record, $board[bo_page_rows]
				");
            }
        }
        $wr_num_in = '';
        while ($row = sql_fetch_array($sql)) {
            $wr_num_in .= ($wr_num_in ? "," : "") . "'" . $row['wr_num'] . "'";
        }


        if ($wr_num_in) {
            $sql = "
				select
					" . $sql_column . "
				from
					$write_table
				where
					wr_num in ($wr_num_in)
					$sql_order

			"; //곽범석 수정
        } else {
            $sql = "
				select
					" . $sql_column . "
				from
					$write_table
				where
					false
					$sql_order
				limit $from_record, $board[bo_page_rows]
			";

        }
    }

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

if (!$sca && !$stx) {
    $arr_notice = explode("\n", trim($board[bo_notice]));
    $arr_notice_count = count($arr_notice);

    if ($arr_notice_count > 0) { // 공지사항이 있는 경우

        $sql_case = " ";
        $j = 0;
        for ($k = 0; $k < $arr_notice_count; $k++) {
            if (trim($arr_notice[$k]) == '')
                continue;

            $sql_case .= " when " . $arr_notice[$k] . " then " . $k;
            if ($j == 0)
                $sql_where = " wr_id = " . $arr_notice[$k] . " ";
            else
                $sql_where .= " or wr_id = " . $arr_notice[$k] . " ";
            $j++;
        } // end of for

        if ($j > 0) {
            $sql = " select $sql_column, case wr_id $sql_case else 10000 end as fsort from $write_table  where $sql_where order by fsort,wr_num, wr_reply ";
            $result_notice = sql_query($sql);
            while ($row_notice = sql_fetch_array($result_notice)) {
                if (!$row_notice['wr_id']) continue;

                $list[$i] = get_list2($row_notice, $board, $board_skin_path, $board[bo_subject_len]);
                $list[$i][is_notice] = true;

                $i++;
            } // end of while
        } // end of if $j > 0

    } // end of if $arr_notice_count > 0
}

$k = 0;

while ($row = sql_fetch_array($result)) {
    // 검색일 경우 wr_id만 얻었으므로 다시 한행을 얻는다
    if ($sca || $stx)
        $row = sql_fetch(" select $sql_column from $write_table  where wr_id = '$row[wr_parent]' ");

    $list[$i] = get_list2($row, $board, $board_skin_path, $board[bo_subject_len]);
    if (strstr($sfl, "subject"))
        $list[$i][subject] = search_font($stx, $list[$i][subject]);
    $list[$i][is_notice] = false;
    //$list[$i][num] = number_format($total_count - ($page - 1) * $board[bo_page_rows] - $k);
    $list[$i][num] = $total_count - ($page - 1) * $board[bo_page_rows] - $k;

    $i++;
    $k++;
}
//곽범석 추가

$write_pages = get_paging($config[cf_write_pages], $page, $total_page, "./board2.php?question=$question&ymd=$ymd&re=$re&bo_table=$bo_table" . $qstr . "&page=");

$list_href = '';
$prev_part_href = '';
$next_part_href = '';
if ($sca || $stx || $ymd) { //곽범석
    $list_href = "./board2.php?bo_table=$bo_table";

    //if ($prev_spt >= $min_spt)
   /* $prev_spt = $spt - $config[cf_search_part];
    if (isset($min_spt) && $prev_spt >= $min_spt)//곽범석추가
        $prev_part_href = "./board2.php?bo_table=$bo_table" . $qstr . "&spt=$prev_spt&page=1&re=$re";

    $next_spt = $spt + $config[cf_search_part];
    if ($next_spt < 0)//곽범석 주석 처리
        $next_part_href = "./board2.php?bo_table=$bo_table" . $qstr . "&spt=$next_spt&page=1&re=$re";*/
}

$write_href = "";
if ($member[mb_level] >= $board[bo_write_level] || $_SESSION['login_email'])
    $write_href = "./write.php?bo_table=$bo_table";


$nobr_begin = $nobr_end = "";
if (preg_match("/gecko|firefox/i", $_SERVER['HTTP_USER_AGENT'])) {
    $nobr_begin = "<nobr style='display:block; overflow:hidden;'>";
    $nobr_end = "</nobr>";
}

// RSS 보기 사용에 체크가 되어 있어야 RSS 보기 가능 061106
$rss_href = "";
if ($board[bo_use_rss_view])
    $rss_href = "./rss.php?bo_table=$bo_table";

$stx = get_text(stripslashes($stx));

function channelunset($channelunset) //곽범석추가
{

    unset($channelunset['page']);
    unset($channelunset['wr_id']);
    unset($channelunset['ymd']);
    unset($channelunset['re']);
    unset($channelunset['question']);
    $data = "";
    return $data = http_build_query($channelunset);
}
function searchs($searchs) //곽범석추가
{

    unset($searchs['page']);
    unset($searchs['wr_id']);
    unset($searchs['question']);

    $data = "";
    return $data = http_build_query($searchs);
}
//echo $board_skin_path."/list.skin.php";exit;
include_once($board_skin_path . "/list.skin.php");

// 김선용 201204 : 작업중
//if($member['mb_id'] === 'coolina'){
//	$sec = get_microtime() - $begin_time;
//	echo '처리시간 : '.$sec;
//}
?>

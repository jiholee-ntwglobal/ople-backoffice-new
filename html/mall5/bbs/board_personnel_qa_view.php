<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$width = '100%';

$board_skin_path = "../skin/board/personnel_qa";

@include_once("$board_skin_path/view.head.skin.php");

$sql_search = "";
// 검색이면
if ($sca || $stx) {
    // where 문을 얻음
    $sql_search = get_sql_search($sca, $sfl, $stx, $sop);
    $search_href = "./board_personnel_qa.php?page=$page" . $qstr;
    $list_href = "./board_personnel_qa.php";
} else {
    $search_href = "";
    $list_href = "./board_personnel_qa.php?page=$page";
}

if ($sql_search)
    $sql_search = " and " . $sql_search;

$view = sql_fetch("select *,date_format(create_dt,'%Y%m%d%H%i%s') as create_date from yc4_personal_qa where depth=0 and mb_id = '$member[mb_id]' and uid='$_GET[uid]'");


// 윗글을 얻음
$sql = " select uid, subject from yc4_personal_qa where depth = 0 and mb_id = '$member[mb_id]' and date_format(create_dt,'%Y%m%d%H%i%s') > '$view[create_date]' $sql_search order by create_dt asc limit 1 ";
$prev = sql_fetch($sql);


// 아래글을 얻음
$sql = " select uid, subject from yc4_personal_qa where depth = 0 and mb_id = '$member[mb_id]' and date_format(create_dt,'%Y%m%d%H%i%s') < '$view[create_date]' $sql_search order by create_dt desc limit 1 ";
$next = sql_fetch($sql);

// 이전글 링크
$prev_href = "";
if ($prev[uid]) {
    $prev_subject = get_text(cut_str($prev[subject], 255));
    $prev_href = "./board_personnel_qa.php?mode=view&uid=$prev[uid]&page=$page" . $qstr;
}

// 다음글 링크
$next_href = "";
if ($next[uid]) {
    $next_subject = get_text(cut_str($next[subject], 255));
    $next_href = "./board_personnel_qa.php?mode=view&uid=$next[uid]&page=$page" . $qstr;
}



// 수정, 삭제 링크
$update_href = $delete_href = "";
// 로그인중이고 자신의 글이라면 또는 관리자라면 패스워드를 묻지 않고 바로 수정, 삭제 가능
if (($member[mb_id] && ($member[mb_id] == $write[mb_id])) || $is_admin) {
    $update_href = "./board_personnel_qa.php?mode=write&uid=$view[uid]&page=$page" . $qstr;
    $delete_href = "javascript:del('./board_personnel_qa.php?mode=delete&uid=$view[uid]&page=$page".$qstr."');";
}


if (strstr($sfl, "subject"))
    $view[subject] = search_font($stx, $view[subject]);

$html = 0;

# 답글이 있는지 확인 #
$reply = sql_fetch("select * from yc4_personal_qa where parent_uid = '".$view['uid']."'");

if($reply){
	$view['contents'] = $view['contents']."\n\n\n\n------------답변------------

	제목 : ".$reply['subject']."

	날짜 : ".substr($reply['create_dt'],2,14)."

	내용 :\n
	".$reply['contents']."
	";
}

$view[content] = conv_content($view[contents], $html);
if (strstr($sfl, "content"))
    $view[contents] = search_font($stx, $view[contents]);
$view[contents] = preg_replace("/(\<img )([^\>]*)(\>)/i", "\\1 name='target_resize_image[]' onclick='image_window(this)' style='cursor:pointer;' \\2 \\3", $view[content]);


include_once("$board_skin_path/view.skin.php");

@include_once("$board_skin_path/view.tail.skin.php");
?>

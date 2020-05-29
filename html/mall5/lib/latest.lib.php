<?
if (!defined('_GNUBOARD_')) exit;
header("Content-Type: text/html; charset=$g4[charset]");

// 최신글 추출
function latest($skin_dir="", $bo_table, $rows=10, $subject_len=40, $options="")
{
    global $g4;

    if ($skin_dir)
        $latest_skin_path = "$g4[path]/skin/latest/$skin_dir";
    else
        $latest_skin_path = "$g4[path]/skin/latest/basic";

    $list = array();

    $sql = " select * from $g4[board_table] where bo_table = '$bo_table'";
    $board = sql_fetch($sql);

    $tmp_write_table = $g4['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
    //$sql = " select * from $tmp_write_table where wr_is_comment = 0 order by wr_id desc limit 0, $rows ";
    // 위의 코드 보다 속도가 빠름
	// 김선용 200804
	//$sql = " select * from $tmp_write_table where wr_is_comment = 0 order by wr_num limit 0, $rows ";
	$sql = "select * from $tmp_write_table where wr_is_comment=0 order by wr_num limit 0, $rows ";
    //explain($sql);
    $result = sql_query($sql);
    for ($i=0; $row = sql_fetch_array($result); $i++)
        $list[$i] = get_list($row, $board, $latest_skin_path, $subject_len);

    ob_start();
    include "$latest_skin_path/latest.skin.php";
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

// 김선용 200804 : 마이페이지 본인 질문/답변 처리에 따른 확장 ($search, $orderby)
function latest_mb_write($skin_dir="", $bo_table, $rows=10, $subject_len=40, $mb_id="", $search="")
{
    global $g4;

    if ($skin_dir)
        $latest_skin_path = "$g4[path]/skin/latest/$skin_dir";
    else
        $latest_skin_path = "$g4[path]/skin/latest/basic";

    $board = sql_fetch("select bo_table, bo_subject, bo_notice, bo_subject_len, bo_new, bo_hot from $g4[board_table] where bo_table='$bo_table' ");
	//$board = sql_fetch("select * from $g4[board_table] where bo_table='$bo_table' ");

	$list = array();
    $tmp_write_table = $g4['write_prefix'].$bo_table;
	$sql = "select * from $tmp_write_table where wr_is_comment=0 and mb_id='{$mb_id}' {$search} order by wr_num, wr_reply limit 0, $rows ";
    //explain($sql);
    $result = sql_query($sql);
    for ($i=0; $row = sql_fetch_array($result); $i++)
        $list[$i] = get_list($row, $board, $latest_skin_path, $subject_len);

    ob_start();
    include "$latest_skin_path/latest.skin.php";
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}


function latest_personel_write($skin_dir="", $bo_table, $rows=10, $subject_len=40, $mb_id="", $search="")
{
    global $g4;

    if ($skin_dir)
        $latest_skin_path = "$g4[path]/skin/latest/$skin_dir";
    else
        $latest_skin_path = "$g4[path]/skin/latest/basic";

    $board = sql_fetch("select bo_table, bo_subject, bo_notice, bo_subject_len, bo_new, bo_hot from $g4[board_table] where bo_table='$bo_table' ");
	//$board = sql_fetch("select * from $g4[board_table] where bo_table='$bo_table' ");

	$list = array();
    $tmp_write_table = $g4['write_prefix'].$bo_table;
	$sql = "select * from yc4_personal_qa where depth=0 and mb_id='{$mb_id}' {$search} order by create_dt desc limit 0, $rows ";
    //explain($sql);
    $result = sql_query($sql);
    for ($i=0; $row = sql_fetch_array($result); $i++){
        //$list[$i] = get_list($row, $board, $latest_skin_path, $subject_len);
		$list[$i] = $row;
	
		$reply = sql_fetch("select count(*) as cnt from yc4_personal_qa where depth=1 and parent_uid='$row[uid]'");

		if($reply['cnt'] > 0) $list[$i]['reply'] = ' &nbsp;&nbsp; ';

		
		if ($list[$i]['reply'])
			$list[$i]['icon_reply'] = "<img src='$latest_skin_path/img/icon_reply.gif' align='absmiddle'>";

	}

    ob_start();
    include "$latest_skin_path/latest.skin.php";
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
?>
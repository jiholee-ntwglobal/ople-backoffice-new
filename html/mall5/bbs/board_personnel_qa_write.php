<?
include_once("./_common.php");

if($_GET['uid']){
	$uid = mysql_escape_string($_GET['uid']);
	$write = sql_fetch("select * from yc4_personal_qa where uid = '".$uid."'");
	if($write){
		$w = 'u';
		$uid = $write['uid'];
		$content = get_text($write['contents'], 0);
	}
}

$title_msg = '1:1문의 작성';

$board_skin_path = $g4['path']."/skin/board/personnel_qa";
$width = '100%';
include_once($board_skin_path."/write.skin.php");
?>

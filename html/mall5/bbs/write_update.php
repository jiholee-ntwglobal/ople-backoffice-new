<?
$g4[title] = $wr_subject . "글입력";
include_once("./_common.php");

// 090710
if (substr_count($wr_content, "&#") > 50) {
    alert("내용에 올바르지 않은 코드가 다수 포함되어 있습니다.");
    exit;
}

@include_once("$board_skin_path/write_update.head.skin.php");

include_once("$g4[path]/lib/trackback.lib.php");



$upload_max_filesize = ini_get('upload_max_filesize');

if (empty($_POST))
    alert("파일 또는 글내용의 크기가 서버에서 설정한 값을 넘어 오류가 발생하였습니다.\\n\\npost_max_size=".ini_get('post_max_size')." , upload_max_filesize=$upload_max_filesize\\n\\n게시판관리자 또는 서버관리자에게 문의 바랍니다.");

// 리퍼러 체크
//referer_check();

$w = $_POST["w"];

$notice_array = explode("\n", trim($board[bo_notice]));

if ($w == "u" || $w == "r") {
    $wr = get_write($write_table, $wr_id);
    if (!$wr[wr_id])
        alert("글이 존재하지 않습니다.\\n\\n글이 삭제되었거나 이동하였을 수 있습니다.");
}

// 외부에서 글을 등록할 수 있는 버그가 존재하므로 비밀글은 사용일 경우에만 가능해야 함
if (!$is_admin && !$board[bo_use_secret] && $secret)
	alert("비밀글 미사용 게시판 이므로 비밀글로 등록할 수 없습니다.");

if ($w == "" || $w == "u") {
    // 김선용 1.00 : 글쓰기 권한과 수정은 별도로 처리되어야 함
    if($w =="u" && $member['mb_id'] && $wr['mb_id'] == $member['mb_id'])
        ;
    else if ($member[mb_level] < $board[bo_write_level])
        alert("글을 쓸 권한이 없습니다.");

	// 외부에서 글을 등록할 수 있는 버그가 존재하므로 공지는 관리자만 등록이 가능해야 함
	if (!$is_admin && $notice)
		alert("관리자만 공지할 수 있습니다.");
}
else if ($w == "r")
{
    if (in_array((int)$wr_id, $notice_array))
        alert("공지에는 답변 할 수 없습니다.");

    if ($member[mb_level] < $board[bo_reply_level])
        alert("글을 답변할 권한이 없습니다.");

    // 게시글 배열 참조
    $reply_array = &$wr;

    // 최대 답변은 테이블에 잡아놓은 wr_reply 사이즈만큼만 가능합니다.
    if (strlen($reply_array[wr_reply]) == 10)
        alert("더 이상 답변하실 수 없습니다.\\n\\n답변은 10단계 까지만 가능합니다.");

    $reply_len = strlen($reply_array[wr_reply]) + 1;
    if ($board[bo_reply_order]) {
        $begin_reply_char = "A";
        $end_reply_char = "Z";
        $reply_number = +1;
        $sql = " select MAX(SUBSTRING(wr_reply, $reply_len, 1)) as reply from $write_table where wr_num = '$reply_array[wr_num]' and SUBSTRING(wr_reply, $reply_len, 1) <> '' ";
    } else {
        $begin_reply_char = "Z";
        $end_reply_char = "A";
        $reply_number = -1;
        $sql = " select MIN(SUBSTRING(wr_reply, $reply_len, 1)) as reply from $write_table where wr_num = '$reply_array[wr_num]' and SUBSTRING(wr_reply, $reply_len, 1) <> '' ";
    }
    if ($reply_array[wr_reply]) $sql .= " and wr_reply like '$reply_array[wr_reply]%' ";
    $row = sql_fetch($sql);

    if (!$row[reply])
        $reply_char = $begin_reply_char;
    else if ($row[reply] == $end_reply_char) // A~Z은 26 입니다.
        alert("더 이상 답변하실 수 없습니다.\\n\\n답변은 26개 까지만 가능합니다.");
    else
        $reply_char = chr(ord($row[reply]) + $reply_number);

    $reply = $reply_array[wr_reply] . $reply_char;
} else
    alert("w 값이 제대로 넘어오지 않았습니다.");


if ($w == "" || $w == "r")
{
    if(!$is_admin){
		if(get_session('ss_datetime') >= ($g4[server_time] - $config[cf_delay_sec]))
	        alert("너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.");
	}
    set_session('ss_datetime', $g4[server_time]);

    // 동일내용 연속 등록 불가
    $row = sql_fetch(" select MD5(CONCAT(wr_ip, wr_subject, wr_content)) as prev_md5 from $write_table order by wr_id desc limit 1 ");
    $curr_md5 = md5($_SERVER[REMOTE_ADDR].$wr_subject.$wr_content);
    if ($row[prev_md5] == $curr_md5 && !$is_admin)
        alert("동일한 내용을 연속해서 등록할 수 없습니다.");
}

// 김선용 201005 :
// 자동등록방지 검사
//include_once ("./norobot_check.inc.php");
if (!$is_member) {
    if ($w=='' || $w=='r') {
        $key = get_session("captcha_keystring");
        if (!($key && $key == $_POST[wr_key])) {
            session_unregister("captcha_keystring");
            alert("정상적인 접근이 아닌것 같습니다.");
        }
    }
}

if (!isset($_POST[wr_subject]) || !trim($_POST[wr_subject]))
    alert("제목을 입력하여 주십시오.");

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir("$g4[path]/data/file/$bo_table", 0707);
@chmod("$g4[path]/data/file/$bo_table", 0707);

// "인터넷옵션 > 보안 > 사용자정의수준 > 스크립팅 > Action 스크립팅 > 사용 안 함" 일 경우의 오류 처리
// 이 옵션을 사용 안 함으로 설정할 경우 어떤 스크립트도 실행 되지 않습니다.
//if (!$_POST[wr_content]) die ("내용을 입력하여 주십시오.");
$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

// 가변 파일 업로드
$file_upload_msg = "";
$upload = array();
for ($i=0; $i<count($_FILES[bf_file][name]); $i++)
{
    // 삭제에 체크가 되어있다면 파일을 삭제합니다.
    if ($_POST[bf_file_del][$i])
    {
        $upload[$i][del_check] = true;

        $row = sql_fetch(" select bf_file from $g4[board_file_table] where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$i' ");
        @unlink("$g4[path]/data/file/$bo_table/$row[bf_file]");
    }
    else
        $upload[$i][del_check] = false;

    $tmp_file  = $_FILES[bf_file][tmp_name][$i];
    $filename  = $_FILES[bf_file][name][$i];
    $filesize  = $_FILES[bf_file][size][$i];

    // 서버에 설정된 값보다 큰파일을 업로드 한다면
    if ($filename)
    {
        if ($_FILES[bf_file][error][$i] == 1)
        {
            $file_upload_msg .= "\'{$filename}\' 파일의 용량이 서버에 설정($upload_max_filesize)된 값보다 크므로 업로드 할 수 없습니다.\\n";
            continue;
        }
        else if ($_FILES[bf_file][error][$i] != 0)
        {
            $file_upload_msg .= "\'{$filename}\' 파일이 정상적으로 업로드 되지 않았습니다.\\n";
            continue;
        }
    }

    if (is_uploaded_file($tmp_file))
    {
        // 관리자가 아니면서 설정한 업로드 사이즈보다 크다면 건너뜀
        if (!$is_admin && $filesize > $board[bo_upload_size])
        {
            $file_upload_msg .= "\'{$filename}\' 파일의 용량(".number_format($filesize)." 바이트)이 게시판에 설정(".number_format($board[bo_upload_size])." 바이트)된 값보다 크므로 업로드 하지 않습니다.\\n";
            continue;
        }


        //=================================================================\
        // 090714
        // 이미지나 플래시 파일에 악성코드를 심어 업로드 하는 경우를 방지
        // 에러메세지는 출력하지 않는다.
        //-----------------------------------------------------------------
        $timg = @getimagesize($tmp_file);
        // image type
        if ( preg_match("/\.($config[cf_image_extension])$/i", $filename) ||
             preg_match("/\.($config[cf_flash_extension])$/i", $filename) )
        {
            if ($timg[2] < 1 || $timg[2] > 16)
            {
                //$file_upload_msg .= "\'{$filename}\' 파일이 이미지나 플래시 파일이 아닙니다.\\n";
                continue;
            }
        }
        //=================================================================

        // 4.00.11 - 글답변에서 파일 업로드시 원글의 파일이 삭제되는 오류를 수정
        if ($w == 'u')
        {
            // 존재하는 파일이 있다면 삭제합니다.
            $row = sql_fetch(" select bf_file from $g4[board_file_table] where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$i' ");
            @unlink("$g4[path]/data/file/$bo_table/$row[bf_file]");
        }

        // 프로그램 원래 파일명
        $upload[$i][source] = $filename;
        $upload[$i][filesize] = $filesize;

        // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
        $filename = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);

        // 접미사를 붙인 파일명
        //$upload[$i][file] = abs(ip2long($_SERVER[REMOTE_ADDR])).'_'.substr(md5(uniqid($g4[server_time])),0,8).'_'.urlencode($filename);
        // 달빛온도님 수정 : 한글파일은 urlencode($filename) 처리를 할경우 '%'를 붙여주게 되는데 '%'표시는 미디어플레이어가 인식을 못하기 때문에 재생이 안됩니다. 그래서 변경한 파일명에서 '%'부분을 빼주면 해결됩니다.
        shuffle($chars_array);
        $shuffle = implode("", $chars_array);
        $upload[$i][file] = abs(ip2long($_SERVER[REMOTE_ADDR])).'_'.substr($shuffle,0,8).'_'.str_replace('%', '', urlencode($filename));

        $dest_file = "$g4[path]/data/file/$bo_table/" . $upload[$i][file];

        // 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
        $error_code = move_uploaded_file($tmp_file, $dest_file) or die($_FILES[bf_file][error][$i]);

        // 올라간 파일의 퍼미션을 변경합니다.
        chmod($dest_file, 0606);

        $upload[$i][image] = @getimagesize($dest_file);

    }
}

if ($w == "" || $w == "r")
{
    if($bo_table == 'lowset_price'){
        $point_mb_id = $mb_id;
        $wr_4 = $po_point;
        $wr_5 = $po_content;
    }
    if ($member[mb_id])
    {
        $mb_id = $member[mb_id];
        $wr_name = $board[bo_use_name] ? $member[mb_name] : $member[mb_nick];
        $wr_password = $member[mb_password];
        $wr_email = $member[mb_email];
        $wr_homepage = $member[mb_homepage];
    }
    else
    {
        $mb_id = "";
        // 비회원의 경우 이름이 누락되는 경우가 있음
		$wr_name = strip_tags(mysql_escape_string($_POST['wr_name']));
		if (!trim($wr_name))
            alert("이름은 필히 입력하셔야 합니다.");

		// 김선용 201108 :
        $wr_password = sql_password(sql_safe_query($wr_password));

		# 비회원 이메일 인증을 경우 이메일과 패스워드를 세션값으로 넣는다 2015-02-06 홍민기 #
		if($_SESSION['login_email']){
			$wr_email = $_SESSION['login_email'];
			$wr_password = sql_password($_SESSION['wr_password']);
		}
    }

    if ($w == "r")
    {
        // 답변의 원글이 비밀글이라면 패스워드는 원글과 동일하게 넣는다.
        if ($secret)
            $wr_password = $wr[wr_password];

        $wr_id = $wr_id . $reply;
        $wr_num = $write[wr_num];
        $wr_reply = $reply;
    }
    else
    {
        $wr_num = get_next_num($write_table);
        $wr_reply = "";
    }

    $sql = " insert into $write_table
                set wr_num = '$wr_num',
                    wr_reply = '$wr_reply',
                    wr_comment = 0,
                    ca_name = '$ca_name',
                    wr_option = '$html,$secret,$mail',
                    wr_subject = '".sql_safe_query($wr_subject)."',
                    wr_content = '$wr_content',
                    wr_link1 = '".sql_safe_query($wr_link1)."',
                    wr_link2 = '".sql_safe_query($wr_link2)."',
                    wr_link1_hit = 0,
                    wr_link2_hit = 0,
                    wr_trackback = '$wr_trackback',
                    wr_hit = 0,
                    wr_good = 0,
                    wr_nogood = 0,
                    mb_id = '$member[mb_id]',
                    wr_password = '$wr_password',
                    wr_name = '".sql_safe_query($wr_name)."',
                    wr_email = '".sql_safe_query($wr_email)."',
                    wr_homepage = '".sql_safe_query($wr_homepage)."',
                    wr_datetime = '$g4[time_ymdhis]',
                    wr_last = '$g4[time_ymdhis]',
                    wr_ip = '$_SERVER[REMOTE_ADDR]',
                    wr_1 = '$wr_1',
                    wr_2 = '$wr_2',
                    wr_3 = '$wr_3',
                    wr_4 = '$wr_4',
                    wr_5 = '$wr_5',
                    wr_6 = '$wr_6',
                    wr_7 = '$wr_7',
                    wr_8 = '$wr_8',
                    wr_9 = '$wr_9',
                    wr_10 = '$wr_10' ";
    sql_query($sql);

    $wr_id = mysql_insert_id();

    // 부모 아이디에 UPDATE
    sql_query(" update $write_table set wr_parent = '$wr_id' where wr_id = '$wr_id' ");

    // 새글 INSERT
    //sql_query(" insert into $g4[board_new_table] ( bo_table, wr_id, wr_parent, bn_datetime ) values ( '$bo_table', '$wr_id', '$wr_id', '$g4[time_ymdhis]' ) ");
    sql_query(" insert into $g4[board_new_table] ( bo_table, wr_id, wr_parent, bn_datetime, mb_id ) values ( '$bo_table', '$wr_id', '$wr_id', '$g4[time_ymdhis]', '$member[mb_id]' ) ");

    // 게시글 1 증가
    sql_query("update $g4[board_table] set bo_count_write = bo_count_write + 1 where bo_table = '$bo_table'");

    // 쓰기 포인트 부여
    if ($w == '')
    {
        if ($notice)
        {
            $bo_notice = $wr_id . "\n" . $board[bo_notice];
            sql_query(" update $g4[board_table] set bo_notice = '$bo_notice' where bo_table = '$bo_table' ");
        }

        insert_point($member[mb_id], $board[bo_write_point], "$board[bo_subject] $wr_id 글쓰기", $bo_table, $wr_id, '쓰기');
    }
    else
    {
        if($bo_table == 'lowset_price'){
            insert_point($point_mb_id, $po_point, $po_content, $bo_table, $wr_id, '최저가 보상');
        }else {
            // 답변은 코멘트 포인트를 부여함
            // 답변 포인트가 많은 경우 코멘트 대신 답변을 하는 경우가 많음
            insert_point($member[mb_id], $board[bo_comment_point], "$board[bo_subject] $wr_id 글답변", $bo_table, $wr_id, '쓰기');
        }
    }
}
else if ($w == "u")
{
	/*
	// 김선용 201305 : 보류. 스킨과 모듈이 이전거라서 호환안됨
    if (get_session('ss_bo_table') != $_POST['bo_table'] || get_session('ss_wr_id') != $_POST['wr_id']) {
        alert('올바른 방법으로 수정하여 주십시오.');
    }
    if ($is_admin == "super") // 최고관리자 통과
        ;
    else if ($is_admin == "group") { // 그룹관리자
        $mb = get_member($write[mb_id]);
        if ($member[mb_id] != $group[gr_admin]) // 자신이 관리하는 그룹인가?
            alert("자신이 관리하는 그룹의 게시판이 아니므로 수정할 수 없습니다.");
        else if ($member[mb_level] < $mb[mb_level]) // 자신의 레벨이 크거나 같다면 통과
            alert("자신의 권한보다 높은 권한의 회원이 작성한 글은 수정할 수 없습니다.");
    } else if ($is_admin == "board") { // 게시판관리자이면
        $mb = get_member($write[mb_id]);
        if ($member[mb_id] != $board[bo_admin]) // 자신이 관리하는 게시판인가?
            alert("자신이 관리하는 게시판이 아니므로 수정할 수 없습니다.");
        else if ($member[mb_level] < $mb[mb_level]) // 자신의 레벨이 크거나 같다면 통과
            alert("자신의 권한보다 높은 권한의 회원이 작성한 글은 수정할 수 없습니다.");
    } else if ($member[mb_id]) {
        if ($member[mb_id] != $write[mb_id])
            alert("자신의 글이 아니므로 수정할 수 없습니다.");
    } else {
        if ($write[mb_id]) {
            alert("로그인 후 수정하세요.", "./login.php?url=".urlencode("./board.php?bo_table=$bo_table&wr_id=$wr_id"));
        }
    }
	*/
    if ($member[mb_id])
    {
        // 자신의 글이라면
        if ($member[mb_id] == $wr[mb_id])
        {
            $mb_id = $member[mb_id];
            $wr_name = $board[bo_use_name] ? $member[mb_name] : $member[mb_nick];
            $wr_email = $member[mb_email];
            $wr_homepage = $member[mb_homepage];
        }
        else
        {
            $mb_id = $wr[mb_id];
            $wr_name = $wr[wr_name];
            $wr_email = $wr[wr_email];
            $wr_homepage = $wr[wr_homepage];
        }
    }
    else
    {
        $mb_id = "";
        // 비회원의 경우 이름이 누락되는 경우가 있음
        //if (!trim($wr_name)) alert("이름은 필히 입력하셔야 합니다.");
    }

    $sql_password = $wr_password ? " , wr_password = '".sql_password(sql_safe_query($wr_password))."' " : "";

    $sql_ip = "";
    if (!$is_admin)
        $sql_ip = " , wr_ip = '$_SERVER[REMOTE_ADDR]' ";

    $sql = " update $write_table
                set ca_name = '$ca_name',
                    wr_option = '$html,$secret,$mail',
                    wr_subject = '$wr_subject',
                    wr_content = '$wr_content',
                    wr_link1 = '".sql_safe_query($wr_link1)."',
                    wr_link2 = '".sql_safe_query($wr_link2)."',
                    mb_id = '$mb_id',
                    wr_name = '".sql_safe_query($wr_name)."',
                    wr_email = '".sql_safe_query($wr_email)."',
                    wr_homepage = '".sql_safe_query($wr_homepage)."',
                    wr_1 = '$wr_1',
                    wr_2 = '$wr_2',
                    wr_3 = '$wr_3',
                    wr_4 = '$wr_4',
                    wr_5 = '$wr_5',
                    wr_6 = '$wr_6',
                    wr_7 = '$wr_7',
                    wr_8 = '$wr_8',
                    wr_9 = '$wr_9',
                    wr_10= '$wr_10'
                    $sql_ip
                    $sql_password
              where wr_id = '$wr[wr_id]' ";
    sql_query($sql);

    // 분류가 수정되는 경우 해당되는 코멘트의 분류명도 모두 수정함
    // 코멘트의 분류를 수정하지 않으면 검색이 제대로 되지 않음
    $sql = " update $write_table set ca_name = '$ca_name' where wr_parent = '$wr[wr_id]' ";
    sql_query($sql);

    if ($notice)
    {
        //if (!preg_match("/[^0-9]{0,1}{$wr_id}[\r]{0,1}/",$board[bo_notice]))
        if (!in_array((int)$wr_id, $notice_array))
        {
            $bo_notice = $wr_id . '\n' . $board[bo_notice];
            sql_query(" update $g4[board_table] set bo_notice = '$bo_notice' where bo_table = '$bo_table' ");
        }
    }
    else
    {
        $bo_notice = '';
        for ($i=0; $i<count($notice_array); $i++)
            if ((int)$wr_id != (int)$notice_array[$i])
                $bo_notice .= $notice_array[$i] . '\n';
        $bo_notice = trim($bo_notice);
        //$bo_notice = preg_replace("/^".$wr_id."[\n]?$/m", "", $board[bo_notice]);
        sql_query(" update $g4[board_table] set bo_notice = '$bo_notice' where bo_table = '$bo_table' ");
    }
}


//------------------------------------------------------------------------------
// 가변 파일 업로드
// 나중에 테이블에 저장하는 이유는 $wr_id 값을 저장해야 하기 때문입니다.
for ($i=0; $i<count($upload); $i++)
{
    $row = sql_fetch(" select count(*) as cnt from $g4[board_file_table] where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$i' ");
    if ($row[cnt])
    {
        // 삭제에 체크가 있거나 파일이 있다면 업데이트를 합니다.
        // 그렇지 않다면 내용만 업데이트 합니다.
        if ($upload[$i][del_check] || $upload[$i][file])
        {
            $sql = " update $g4[board_file_table]
                        set bf_source = '{$upload[$i][source]}',
                            bf_file = '{$upload[$i][file]}',
                            bf_content = '{$bf_content[$i]}',
                            bf_filesize = '{$upload[$i][filesize]}',
                            bf_width = '{$upload[$i][image][0]}',
                            bf_height = '{$upload[$i][image][1]}',
                            bf_type = '{$upload[$i][image][2]}',
                            bf_datetime = '$g4[time_ymdhis]'
                      where bo_table = '$bo_table'
                        and wr_id = '$wr_id'
                        and bf_no = '$i' ";
            sql_query($sql);
        }
        else
        {
            $sql = " update $g4[board_file_table]
                        set bf_content = '{$bf_content[$i]}'
                      where bo_table = '$bo_table'
                        and wr_id = '$wr_id'
                        and bf_no = '$i' ";
            sql_query($sql);
        }
    }
    else
    {
        $sql = " insert into $g4[board_file_table]
                    set bo_table = '$bo_table',
                        wr_id = '$wr_id',
                        bf_no = '$i',
                        bf_source = '{$upload[$i][source]}',
                        bf_file = '{$upload[$i][file]}',
                        bf_content = '{$bf_content[$i]}',
                        bf_download = 0,
                        bf_filesize = '{$upload[$i][filesize]}',
                        bf_width = '{$upload[$i][image][0]}',
                        bf_height = '{$upload[$i][image][1]}',
                        bf_type = '{$upload[$i][image][2]}',
                        bf_datetime = '$g4[time_ymdhis]' ";
        sql_query($sql);
    }
}

// 업로드된 파일 내용에서 가장 큰 번호를 얻어 거꾸로 확인해 가면서
// 파일 정보가 없다면 테이블의 내용을 삭제합니다.
$row = sql_fetch(" select max(bf_no) as max_bf_no from $g4[board_file_table] where bo_table = '$bo_table' and wr_id = '$wr_id' ");
for ($i=(int)$row[max_bf_no]; $i>=0; $i--)
{
    $row2 = sql_fetch(" select bf_file from $g4[board_file_table] where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$i' ");

    // 정보가 있다면 빠집니다.
    if ($row2[bf_file]) break;

    // 그렇지 않다면 정보를 삭제합니다.
    sql_query(" delete from $g4[board_file_table] where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$i' ");
}
//------------------------------------------------------------------------------

// 비밀글이라면 세션에 비밀글의 아이디를 저장한다. 자신의 글은 다시 패스워드를 묻지 않기 위함
if ($secret)
    set_session("ss_secret_{$bo_table}_{$wr_num}", TRUE);

// 메일발송 사용 (수정글은 발송하지 않음)
if (!($w == "u" || $w == "cu") && $config[cf_email_use] && $board[bo_use_email] && $email_send_fg)
{
    // 관리자의 정보를 얻고
    $super_admin = get_admin("super");
    $group_admin = get_admin("group");
    $board_admin = get_admin("board");

    $wr_subject = get_text(stripslashes($wr_subject));

    $tmp_html = 0;
    if (strstr($html, "html1"))
        $tmp_html = 1;
    else if (strstr($html, "html2"))
        $tmp_html = 2;

    $wr_content = conv_content(stripslashes($wr_content), $tmp_html);

    $warr = array( ""=>"입력", "u"=>"수정", "r"=>"답변", "c"=>"코멘트", "cu"=>"코멘트 수정" );
    $str = $warr[$w];

    $subject = "'{$board[bo_subject]}' 게시판에 {$str}글이 올라왔습니다.";
    $link_url = "$g4[url]/$g4[bbs]/board.php?bo_table=$bo_table&wr_id=$wr_id&$qstr&question=$question&re=$re&ymd=$ymd  "; //곽범석 수정

    include_once("$g4[path]/lib/mailer.lib.php");

    ob_start();
    include_once ("./write_update_mail.php");
    $content = ob_get_contents();
    ob_end_clean();

    
	// 게시판관리자에게 보내는 메일
    if ($config[cf_email_wr_board_admin])
        //mailer($wr_name, $wr_email, $board_admin[mb_email], $subject, $content, 1);
		mailer($wr_name, $wr_email, $default['de_receive_mail_addr'], $subject, $content, 1);

    // 그룹관리자에게 보내는 메일
    if ($group_admin[mb_email] != $board_admin[mb_email])
    {
        if ($config[cf_email_wr_group_admin])
            //mailer($wr_name, $wr_email, $group_admin[mb_email], $subject, $content, 1);
			mailer($wr_name, $wr_email, $default['de_receive_mail_addr'], $subject, $content, 1);
    }

    // 최고관리자에게 보내는 메일
    if ($super_admin[mb_email] != $board_admin[mb_email])
    {
        if ($config[cf_email_wr_super_admin])
            //mailer($wr_name, $wr_email, $super_admin[mb_email], $subject, $content, 1);
			mailer($wr_name, $wr_email, $default['de_receive_mail_addr'], $subject, $content, 1);
    }

    // 답변 메일받기 (원게시자에게 보내는 메일)
    if (strstr($wr[wr_option], "mail") && $wr[wr_email] && $wr[wr_email] != $admin[mb_email])
    {
        if ($config[cf_email_wr_write])
            //mailer($wr_name, $wr_email, $wr[wr_email], $subject, $content, 1);
			mailer($wr_name, $wr_email, $wr[wr_email], $subject, $content, 1);

    }
	
}

// 사용자 코드 실행
@include_once ("$board_skin_path/write_update.skin.php");

// 트랙백 주소가 있다면
if (($w != "u" && $wr_trackback) || ($w=="u" && $wr_trackback && $re_trackback))
{
    $trackback_url = "$g4[url]/$g4[bbs]/tb.php/$bo_table/$wr_id";
    $msg = "";
    $msg = send_trackback($wr_trackback, $trackback_url, $wr_subject, $board[bo_subject], $_POST[wr_content]);
    if ($msg)
        echo "<script type='text/JavaScript'>alert(\"$msg $wr_trackback\");</script>";
}

@include_once("$board_skin_path/write_update.tail.skin.php");

if ($g4[https_url])
    $https_url = "$g4[url]/$g4[bbs]&question=$question&re=$re&ymd=$ymd";
else
    $https_url = ".";

if ($file_upload_msg)
    alert($file_upload_msg, "{$https_url}/board.php?bo_table=$bo_table&wr_id=$wr_id&page=$page&question=$question&re=$re&ymd=$ymd" . $qstr); //곽범석 수장
else
    goto_url("{$https_url}/board.php?bo_table=$bo_table&wr_id=$wr_id&page=$page&question=$question&re=$re&ymd=$ymd" . $qstr);
?>

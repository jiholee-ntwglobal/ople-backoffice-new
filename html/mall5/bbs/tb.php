<?
//
// 트랙백 핑 받는 페이지
//
define("_GNUBOARD_", TRUE);

include_once("./_common.php");

// 오류는 write_log() 로 잡는다.
include_once("$g4[path]/lib/etc.lib.php");
//write_log("$g4[path]/lib/log/aaa", 1);

//phpinfo(); exit;

//preg_match("/tb\.php\/([^\/]+)\/([^\/]+)$/", $_SERVER[PHP_SELF], $matches);
//$bo_table = $matches[1];
//$wr_id = $matches[2];

$arr = explode("/", $_SERVER[PATH_INFO]);
$bo_table = $arr[1];
$wr_id = $arr[2];
$to_token = $arr[3];

$write_table   = $g4[write_prefix] . $bo_table; // 게시판 테이블 전체이름

$sql = " select wr_id,
                ca_name, 
                wr_email
           from $write_table where wr_id = '$wr_id' ";
$wr = sql_fetch($sql, FALSE);

// wr_id가 없거나 트랙백으로 넘어온게 아니라면
if (!$wr[wr_id] || !($_POST[title] && $_POST[excerpt] && $_POST[url] && $_POST[blog_name])) {
    $tmp_dir = str_replace("/tb.php", "", $_SERVER[SCRIPT_NAME]);
    header("location:$tmp_dir/board.php?bo_table=$bo_table&wr_id=$wr_id");
    exit;
}

$title   = $_POST[title];
$excerpt = $_POST[excerpt];

if (strlen($title) > 255)   $title   = cut_str($title, 255);
if (strlen($excerpt) > 255) $excerpt = cut_str($excerpt, 255);

$msg = "";
// 두번씩 INSERT 되는것을 막기 위해
if ($_POST[title]) {
    $board = sql_fetch(" select bo_subject, bo_use_trackback from $g4[board_table] where bo_table = '$bo_table' ");
    if (!$board[bo_use_trackback]) 
        $msg = "트랙백 사용이 금지된 게시판입니다.";

    // 토큰검사
    if (isset($g4['token_time']) == false)
        $g4['token_time'] = 3; 

    $sql = " delete from $g4[token_table] 
              where to_datetime < '".date("Y-m-d", $g4[server_time] - 86400 * $g4['token_time'])."' ";
    sql_query($sql);

    $sql = " select to_token from $g4[token_table]
              where to_token = '$to_token' ";
    $row = sql_fetch($sql);
    if ($row[to_token] && $to_token) {
        // 두번 이상 트랙백을 보내지 못하도록 하기 위하여 토큰을 삭제한다
        sql_query(" delete from $g4[token_table] where to_token = '$to_token' ");
    } else {
        $msg = "트랙백 주소가 올바르지 않습니다. (토큰 유효시간 경과 등)";
    }

    if (!$msg) {
        $next_num = get_next_num($write_table);

        $sql = " select max(wr_comment) as max_comment from $write_table 
                  where wr_parent = '$wr_id' and wr_is_comment = 1 ";
        $row = sql_fetch($sql);
        $row[max_comment] += 1;

        $sql = " insert into $g4[write_prefix]$bo_table
                    set wr_num = '$next_num',
                        wr_parent = '$wr_id',
                        wr_is_comment = '1',
                        wr_comment = '$row[max_comment]',
                        wr_content = '$title\n$excerpt',
                        wr_trackback = '$_POST[url]',
                        wr_name = '$_POST[blog_name]',
                        wr_password = '".sql_password($g4[server_time])."',
                        wr_datetime = '$g4[time_ymdhis]', 
                        wr_ip = '$_SERVER[REMOTE_ADDR]' ";
        $result = sql_query($sql, FALSE);
        if ($result) {
            $comment_id = mysql_insert_id();
            sql_query(" update $write_table set wr_comment = wr_comment + 1 where wr_id = '$wr_id' ", FALSE);
            sql_query(" insert into $g4[board_new_table] ( bo_table, wr_id, wr_parent, bn_datetime ) values ( '$bo_table', '$comment_id', '$wr_id', '$g4[time_ymdhis]' ) ");
            sql_query(" update $g4[board_table] set bo_count_comment = bo_count_comment + 1 where bo_table = '$bo_table' ", FALSE);
        } else 
            $msg = "$write_table TABLE INSERT 오류";
    }

    //write_log("$g4[path]/data/log/aaa", $msg);

    if ($msg) // 비정상(오류)
    { 
        echo "<?xml version=\"1.0\" encoding=\"$g4[charset]\"?>\n";
        echo "<response>\n";
        echo "<error>1</error>\n";
        echo "<message>$msg</message>\n";
        echo "</response>\n";
        exit;
    } 
    else // 정상
    { 
        // 메일발송 사용
        if ($config[cf_email_use] && $board[bo_use_email])
        {
            include_once("$g4[path]/lib/mailer.lib.php");

            // 관리자의 정보를 얻고
            $super_admin = get_admin("super");
            $group_admin = get_admin("group");
            $board_admin = get_admin("board");

            $wr_name    = $blog_name = get_text(stripslashes($_POST[blog_name]));
            $wr_subject = $title = get_text(stripslashes($title));
            $wr_content = $excerpt = nl2br(get_text(stripslashes($excerpt)));

            $link_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            $subject = "'{$board[bo_subject]}' 게시판에 트랙백에 의한 글이 올라왔습니다.";

            define("_GNUBOARD_", TRUE);
            ob_start();
            include_once ("./write_update_mail.php");
            $content = ob_get_contents();
            ob_end_clean();

            // 게시판 관리자에게 보내는 메일
            if ($config[cf_email_wr_board_admin])
                mailer($blog_name, "", $board_admin[mb_email], $subject, $content, 1);

            // 그룹 관리자에게 보내는 메일
            if ($group_admin[mb_email] != $board_admin[mb_email])
            {
                if ($config[cf_email_wr_group_admin])
                    mailer($blog_name, "", $group_admin[mb_email], $subject, $content, 1);
            }
            
            // 최고관리자에게 보내는 메일
            if ($super_admin[mb_email] != $board_admin[mb_email])
            {
                if ($config[cf_email_wr_super_admin])
                    mailer($blog_name, "", $super_admin[mb_email], $subject, $content, 1);
            }

            // 답변 메일받기 (원게시자에게 보내는 메일)
            //if ($wr[wr_recv_email] && $wr[wr_email] && $wr[wr_email] != $admin[mb_email]) 
            if (strstr($wr[wr_option], 'mail') && $wr[wr_email] && $wr[wr_email] != $admin[mb_email]) 
            {
                if ($config[cf_email_wr_write])
                    mailer($blog_name, "", $wr[wr_email], $subject, $content, 1);

                // 코멘트 쓴 모든이에게 메일 발송
                if ($config[cf_email_wr_comment_all])
                {
                    $sql = " select wr_email from $write_table
                              where wr_email not in ( '$admin[mb_email]' , '$wr[wr_email]', '' )
                                and wr_parent = '$wr_id'
                              group by wr_email ";
                    $result = sql_query($sql);
                    while ($row=sql_fetch_array($result)) 
                        mailer($blog_name, "", $row[wr_email], $subject, $content, 1);
                }
            }
        }
    }
}
echo "<?xml version=\"1.0\" encoding=\"$g4[charset]\"?>\n";
echo "<response>\n";
echo "<error>0</error>\n";
echo "</response>\n";
?>

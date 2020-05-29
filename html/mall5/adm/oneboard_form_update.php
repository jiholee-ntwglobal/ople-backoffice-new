<?
$sub_menu = "300300";
include_once("./_common.php");

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], "w");

if (!$ob_table) { alert("게시판 TABLE명은 반드시 입력하세요."); }
if (!ereg("^([A-Za-z0-9_]{1,20})$", $ob_table)) { alert("게시판 TABLE명은 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (20자 이내)"); }
if (!$_POST[ob_subject]) { alert("게시판 제목을 입력하세요."); }

@mkdir("$g4[path]/data/one", 0707);
@chmod("$g4[path]/data/one", 0707);

$board_path = "$g4[path]/data/one/$ob_table";

// 게시판 디렉토리 생성
@mkdir($board_path, 0707);
@chmod($board_path, 0707);

// 디렉토리에 있는 파일의 목록을 보이지 않게 한다.
$file = $board_path . "/index.php";
$f = @fopen($file, "w");
@fwrite($f, "");
@fclose($f);
@chmod($file, 0606);

$sql_common = " ob_subject          = '$ob_subject',
                ob_admin            = '$ob_admin',
                ob_write_level      = '$ob_write_level',
                ob_upload_level     = '$ob_upload_level',
                ob_use_dhtml_editor = '$ob_use_dhtml_editor',
                ob_use_email        = '$ob_use_email',
                ob_table_width      = '$ob_table_width',
                ob_subject_len      = '$ob_subject_len',
                ob_page_rows        = '$ob_page_rows',
                ob_image_width      = '$ob_image_width',
                ob_skin             = '$ob_skin',
                ob_include_head     = '$ob_include_head',
                ob_include_tail     = '$ob_include_tail',
                ob_content_head     = '$ob_content_head',
                ob_content_tail     = '$ob_content_tail',
                ob_insert_content   = '$ob_insert_content',
                ob_1_subj           = '$ob_1_subj',
                ob_2_subj           = '$ob_2_subj',
                ob_3_subj           = '$ob_3_subj',
                ob_4_subj           = '$ob_4_subj',
                ob_5_subj           = '$ob_5_subj',
                ob_6_subj           = '$ob_6_subj',
                ob_7_subj           = '$ob_7_subj',
                ob_8_subj           = '$ob_8_subj',
                ob_9_subj           = '$ob_9_subj',
                ob_10_subj          = '$ob_10_subj',
                ob_1                = '$ob_1',
                ob_2                = '$ob_2',
                ob_3                = '$ob_3',
                ob_4                = '$ob_4',
                ob_5                = '$ob_5',
                ob_6                = '$ob_6',
                ob_7                = '$ob_7',
                ob_8                = '$ob_8',
                ob_9                = '$ob_9',
                ob_10               = '$ob_10' ";

if ($ob_image_head_del) {
    @unlink("$board_path/$ob_image_head_del");
    $sql_common .= " , ob_image_head = '' ";
}

if ($ob_image_tail_del) {
    @unlink("$board_path/$ob_image_tail_del");
    $sql_common .= " , ob_image_tail = '' ";
}

if ($_FILES[ob_image_head][name]) {
    $ob_image_head_urlencode = urlencode($_FILES[ob_image_head][name]);
    $sql_common .= " , ob_image_head = '$ob_image_head_urlencode' ";
}

if ($_FILES[ob_image_tail][name]) {
    $ob_image_tail_urlencode = urlencode($_FILES[ob_image_tail][name]);
    $sql_common .= " , ob_image_tail = '$ob_image_tail_urlencode' ";
}

if ($w == "") {
    $row = sql_fetch(" select count(*) as cnt from $g4[oneboard_table] where ob_table = '$ob_table' ");
    if ($row[cnt])
        alert("{$ob_table} 은(는) 이미 존재하는 TABLE 입니다.");

    $sql = " insert into $g4[oneboard_table]
                set ob_table = '$ob_table',
                    $sql_common ";
    sql_query($sql);

    // 게시판 테이블 생성
    $file = file("./sql_one.sql");
    $sql = implode($file, "\n");

    $create_table = $g4[one_prefix] . $ob_table;

    // sql_board.sql 파일의 테이블명을 변환
    $source = array("/__TABLE_NAME__/", "/;/");
    $target = array($create_table, "");
    $sql = preg_replace($source, $target, $sql);
    sql_query($sql, TRUE);
} else if ($w == "u") {
    $sql = " update $g4[oneboard_table]
                set $sql_common
              where ob_table = '$ob_table' ";
    sql_query($sql);
}

// 전체 1:1 게시판 동일 옵션 적용
$s = "";
if ($chk_admin) $s .= " , ob_admin = '$ob_admin' ";
if ($chk_write_level) $s .= " , ob_write_level = '$ob_write_level' ";
if ($chk_upload_level) $s .= " , ob_upload_level = '$ob_upload_level' ";
if ($chk_use_sideview) $s .= " , ob_use_sideview = '$ob_use_sideview' ";
if ($chk_use_dhtml_editor) $s .= " , ob_use_dhtml_editor = '$ob_use_dhtml_editor' ";
if ($chk_use_email) $s .= " , ob_use_email = '$ob_use_email' ";
if ($chk_skin) $s .= " , ob_skin = '$ob_skin' ";
if ($chk_table_width) $s .= " , ob_table_width = '$ob_table_width' ";
if ($chk_page_rows) $s .= " , ob_page_rows = '$ob_page_rows' ";
if ($chk_subject_len) $s .= " , ob_subject_len = '$ob_subject_len' ";
if ($chk_image_width) $s .= " , ob_image_width = '$ob_image_width' ";
if ($chk_include_head) $s .= " , ob_include_head = '$ob_include_head' ";
if ($chk_include_tail) $s .= " , ob_include_tail = '$ob_include_tail' ";
if ($chk_content_head) $s .= " , ob_content_head = '$ob_content_head' ";
if ($chk_content_tail) $s .= " , ob_content_tail = '$ob_content_tail' ";
if ($chk_insert_content) $s .= " , ob_insert_content = '$ob_insert_content' ";
for ($i=1; $i<=10; $i++) {
    if ($_POST["chk_{$i}"]) {
        $s .= " , ob_{$i}_subj = '".$_POST["ob_{$i}_subj"]."' ";
        $s .= " , ob_{$i} = '".$_POST["ob_{$i}"]."' ";
    }
}

if ($s) {
        $sql = " update $g4[oneboard_table]
                    set ob_table = ob_table
                        {$s} ";
        sql_query($sql);
}

if ($_FILES[ob_image_head][name]) { 
    $ob_image_head_path = "$board_path/$ob_image_head_urlencode";
    move_uploaded_file($_FILES[ob_image_head][tmp_name], $ob_image_head_path);
    chmod($ob_image_head_path, 0606);
}

if ($_FILES[ob_image_tail][name]) { 
    $ob_image_tail_path = "$board_path/$ob_image_tail_urlencode";
    move_uploaded_file($_FILES[ob_image_tail][tmp_name], $ob_image_tail_path);
    chmod($ob_image_tail_path, 0606);
}

goto_url("oneboard_form.php?w=u&ob_table=$ob_table&$qstr");
?>

<?php
// ---------------------------------------------------------------------------
//                              CHXImage
//
// 이 코드는 데모를 위해서 제공됩니다.
// 환경에 맞게 수정 또는 참고하여 사용해 주십시오.
//
// ---------------------------------------------------------------------------

require_once("_config.php");

//----------------------------------------------------------------------------
//
//
$tempfile = $_FILES['file']['tmp_name'];
$filename = md5($_SERVER['REMOTE_ADDR']).'_'.$_FILES['file']['name'];

// 저장 파일 이름
// md5(IP)_년월일시분초_랜덤문자4자.확장자
// 1234567890abcdef1234567890abcdef_20140327125959_abcd.jpg
$savefile = SAVE_DIR . '/' . $filename;

// 사용자PC의 파일 이름: $_POST["origName"]
// 사용자PC의 파일 경로: $_POST["filePath"]
// 사용자PC의 파일 크기: $_POST["filesize"]

// 파일의 확장자가 이미지가 아니라면 삭제
if (!preg_match("/.(gif|jpe?g|png)$/i", $savefile))
    die("-ERR: File Format!");

move_uploaded_file($tempfile, $savefile);
$imgsize = getimagesize($savefile);
$filesize = filesize($savefile);

if (!$imgsize) {
	$filesize = 0;
	$random_name = '-ERR';
	unlink($savefile);
};

switch ($imgsize[2]) {
    case IMAGETYPE_GIF :    // 1
    case IMAGETYPE_JPEG :   // 2
    case IMAGETYPE_PNG :    // 3
        break;
    default :
        $filesize = 0;
        $random_name = '-ERR';
        unlink($savefile);
}

$rdata = sprintf('{"fileUrl": "%s/%s", "filePath": "%s", "fileName": "%s", "fileSize": "%d" }',
	SAVE_URL,
	$filename,
	$savefile,
	$filename,
	$filesize );

echo $rdata;
?>

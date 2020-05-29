<?php
require_once("_config.php");
// ---------------------------------------------------------------------------

$filepath = $_POST["filepath"];
$r = false;

# md5(ip)_날짜시분초_파일명.확장자
preg_match('#\/([0-9a-f]+)_([0-9]+)_([a-z]+)\.(gif|png|jpe?g)$#i', $filepath, $m);
$md5ip = $m[1];
// 자신이 업로드 한 파일만 삭제가 가능하게 함
if ($md5ip == md5($_SERVER['REMOTE_ADDR'])) {
    if (file_exists($filepath)) {
        $r = unlink($filepath);
        if ($r) {
            $thumbPath = dirname($filepath) . DIRECTORY_SEPARATOR . "thumb_" . basename($filepath);
            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }
        }
    }
}

echo $r ? true : false;
?>
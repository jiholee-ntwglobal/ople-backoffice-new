<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2018-05-09
 * Time: 오후 4:42
 */
include_once "./_common.php";
$upload_file_arr[] = array(
    'remote_path' => '/ssd/ople_data/data/item_tmp/1511309062_l1',
    'local_path' =>'/ssd/ople_data/data/item_tmp/8098_l1'
);

file_upload($upload_file_arr);
function file_upload($arr = array()){
    $g4['front_ftp_server']	 = "66.209.90.19";
    $g4['front_ftp_user_name'] = "ntwglobal";
    $g4['front_ftp_user_pass'] = "qwe123qwe!@#";
    $conn_id = @ftp_connect($g4['front_ftp_server']);

    //var_dump($conn_id);
    $login_result = @ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);
    //var_dump($login_result);
    foreach ($arr as $row) {

        $local_file = fopen($row['local_path'],'r');

        if(!file_exists($row['local_path'])){
            echo '파일이 존재하지 않습니다. '.$row['local_path'].PHP_EOL;
        }
        ftp_pasv($conn_id, true);
        $upload = ftp_fput($conn_id, $row['remote_path'], $local_file, FTP_BINARY);
        if($upload === false){
            echo '1';
            return false;
        }
        $chmod = ftp_chmod($conn_id, 0777, $row['remote_path']);
    }

    echo '2';
    return true;
}
exit;
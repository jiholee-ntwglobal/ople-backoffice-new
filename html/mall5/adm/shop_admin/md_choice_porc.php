<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-18
 * Time: 오후 2:54
 */
$sub_menu = "600950";
include_once("./_common.php");
auth_check($auth[$sub_menu], "w");
if($_GET['real']=='Y'){
    $md_item_sql= sql_query($sql="
      select title, mobile_img_url, mobile_link_url, pc_img_url, pc_link_url 
      from md_choice_data 
      where date_format(st_dt,'%Y%m%d') <= date_format(now(),'%Y%m%d')
       and if(en_dt is null or en_dt ='' ,date_format(now(),'%Y%m%d') ,en_dt) >= date_format(now(),'%Y%m%d') 
      ORDER  by sort");

    //file
    /*$html = '';*/
    //xml
    /*$te = <<<XML
XML;*/
    $md_choice_list =array();
    while ($row = sql_fetch_array($md_item_sql)) {
        $md_choice_list[]=$row;
        //file
        /*$html .="<li class=\"mdChoice_list_item\"><a href=\"{$row['pc_link_url']}\"><img src=\"{$row['pc_img_url']}\" alt=\"{$row['title']}\" /></a></li>".PHP_EOL;*/
        //xml
       /* $row['mobile_link_url']= htmlspecialchars($row['mobile_link_url']);
        $row['mobile_img_url']= htmlspecialchars($row['mobile_img_url']);
        $row['pc_img_url']= htmlspecialchars($row['pc_img_url']);
        $row['pc_link_url']= htmlspecialchars($row['pc_link_url']);
        $te .= <<<XML
<mdlist> 
<subject>{$row['title']}</subject>
<mobileimg>{$row['mobile_img_url']}</mobileimg>
<mobilelink>{$row['mobile_link_url']}</mobilelink>
<pcimg>{$row['pc_img_url']}</pcimg>
<polink>{$row['pc_link_url']}</polink>
</mdlist>
XML;*/



    }

    //json
    $requestXmlBody=    json_encode($md_choice_list);
    //xml
 /*   $requestXmlBody = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<root>{$te}</root>
XML;*/
    // fsock으로 POST 전송
    $host = '66.209.90.19';
    $path = '/mall5/cron/md_choice_cache.php';
    $xmlData = $requestXmlBody;
    $errno = null;
    $errstr = null;
    // 헤더를 설정해서 POST로 전송
    $fp = fsockopen($host, '80', $errno, $errstr, 30);
    if($fp)
    {

        $header  = "POST ".$path." HTTP/1.1\r\n";
        $header .= "Host: ".$host."\r\n";
        $header .= "User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)\r\n";
        $header .= "Content-type: text/html\r\n";
        $header .= "Content-length: ".strlen($xmlData)."\r\n\r\n";
        $header .= $xmlData."\r\n";

        fputs($fp, $header.$xmlData."\r\n\r\n");

        while(!feof($fp))
        {
            $result .= fgets($fp, 1024);
        }

        fclose($fp);
        alert('적용 되었습니다.','./md_choice_new.php');
    }




     //file
   /* $file = fopen($g4['path'].'/cache/mdchoice_pc.htm','w+');
    fwrite($file,$html);
    fclose($file);
    $file = fopen($g4['path'].'/cache/mdchoice_pc.htm','r');
    $conn_id = @ftp_connect($g4['front_ftp_server']);
    $login_result = @ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);
    @ftp_fput($conn_id, '/ssd/html/mall5/cache/mdchoice_pc.htm', $file, FTP_BINARY);
    @ftp_close($conn_id);
    fclose($file);*/

}
if($_POST['mode']){
    if($_POST['mode'] == 'delete'){
        $delete_uid= sql_fetch("select uid,sort from md_choice_data where uid ={$_POST['uid']}");

        $sql_query ="
        select uid
        from md_choice_data
        where sort >= {$delete_uid['sort']}
        and uid !='{$delete_uid['uid']}'
        order by sort asc";
        $sql = sql_query($sql_query);
        while($row = sql_fetch_array($sql)){
            sql_query("
        update md_choice_data
        set sort = ".$delete_uid['sort']++."
       where uid = '{$row['uid']}'
        ");

        }
        $qry= " delete from md_choice_data where uid= '{$_POST['uid']}' ";
        if(!sql_query($qry)){
            $msg= '삭제를 실패하였습니다';
            alert($msg,'./md_choice_new.php');
            exit;
        }else{
            $msg= '삭제되었습니다';
            alert($msg,'./md_choice_new.php');
            exit;
        }
    }
    $_POST['subject']  = $_POST['subject'] ? mysql_real_escape_string(stripslashes($_POST['subject'])) : "";

    if($_POST['subject']==''){
        alert("제목을 입력하셔야 합니다","");
    }
    $st_dt =preg_replace("/[^0-9]*/s", "", $_POST['st_dt']);
    if(!$st_dt){
        alert("시작날짜를 입력하셔야합니다".'');
    }
    $en_dt =preg_replace("/[^0-9]*/s", "", $_POST['en_dt']);
    $_POST['sort'] = mysql_real_escape_string(stripslashes($_POST['sort']));
    $_POST['mobile_img_url'] = mysql_real_escape_string(stripslashes($_POST['mobile_img_url']));
    $_POST['mobile_link_url'] = mysql_real_escape_string(stripslashes($_POST['mobile_link_url']));
    $_POST['pc_img_url'] = mysql_real_escape_string(stripslashes($_POST['pc_img_url']));
    $_POST['pc_link_url'] = mysql_real_escape_string(stripslashes($_POST['pc_link_url']));

    if($_POST['sort']){
        if(!is_numeric($_POST['sort'])){
            alert("정렬은 숫자만 입력가능합니다.",'');
            exit;
        }
        $sort =$_POST['sort'];

        $sql_query ="
        select uid
        from md_choice_data
        where sort >= {$_POST['sort']}
        and uid !='{$_POST['uid']}'
        order by sort asc";
        $sql = sql_query($sql_query);
        while($row = sql_fetch_array($sql)){
            sql_query("
        update md_choice_data
        set sort = ".++$sort."
       where uid = '{$row['uid']}'
        ");

        }

    }else{
        $sort_chk = sql_fetch("select max(sort) as sort from md_choice_data");
        $_POST['sort'] = $sort_chk['sort'] + 1;
    }

    if($_POST['mode'] == 'update'){
        $qry = "
			update
				md_choice_data
			set
				title ='{$_POST['subject']}',
				mobile_img_url  ='{$_POST['mobile_img_url']}',
				mobile_link_url  ='{$_POST['mobile_link_url']}',
				pc_img_url  ='{$_POST['pc_img_url']}',
				pc_link_url  ='{$_POST['pc_link_url']}',
				st_dt  ='{$st_dt}',
				en_dt  ='{$en_dt}',
				sort = '".$_POST['sort']."',
				
				update_dt = now()
			where
				uid = '".$_POST['uid']."'
		";
        $msg = '수정이 완료되었습니다.';
    }elseif($_POST['mode'] == 'insert'){
        $qry = "
			insert into
				md_choice_data
			(
				title,mobile_img_url,mobile_link_url,pc_img_url,pc_link_url,
				st_dt,en_dt,sort,
				create_dt,ip,mb_id
			) VALUES(
				'{$_POST['subject']}','{$_POST['mobile_img_url']}','{$_POST['mobile_link_url']}','{$_POST['pc_img_url']}','{$_POST['pc_link_url']}',
				'{$st_dt}','{$en_dt}','{$_POST['sort']}',
				now(),'{$_SERVER['REMOTE_ADDR']}','{$_SESSION['ss_mb_id']}'
			)
		";
        $msg = '엠디초이스 등록이 완료되었습니다.';
    }
    if(!sql_query($qry)){
        alert('처리중 오류 발생! 다시 시도해 주세요.');exit;
    }

    alert($msg,'./md_choice_new.php');
    exit;

}
<?php
error_reporting(E_ALL);

ini_set("display_errors", 1);



include_once("./_common.php");

function sql_query($sql, $error=TRUE)
{
    global $g4;
    global $connect_db;
    if ($error){
//        $result = @mysql_query($sql) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : $_SERVER[PHP_SELF]");
        $result = @mysqli_query($connect_db,$sql) ;
        $sql_error = mysqli_error($connect_db);
        if($sql_error){ // SQL 에러 발생시 별도 테이블에 저장 2015-12-15 홍민기
            $e = new Exception;
            $trace = $e->getTrace();
            $trace = $trace[0];
            sql_query(
                "
                    insert into sql_error.sql_error (`file`,`error`,`sql`,line,dt,ip,url) VALUES
                    ('".sql_safe_query($trace['file'])."','".sql_safe_query($sql_error)."','".sql_safe_query($sql)."','".sql_safe_query($trace['line'])."',now(),'{$_SERVER['REMOTE_ADDR']}','".sql_safe_query($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])."')
                "
                ,false
            );
            if(in_array($_SERVER['REMOTE_ADDR'], array('47.176.39.130'))){
                echo "<p>$sql<p>" . mysqli_errno($connect_db) . " : " .  mysqli_error($connect_db) . "<p>error file : $_SERVER[PHP_SELF]";
            }else{
                alert('처리중 오류가 발생하였습니다. 다시 시도해 주세요.',$g4['path']);
            }
            exit;
        }
    }else {
        $result = @mysqli_query($connect_db,$sql);
    }
    return $result;
}


$sql = sql_query("select * from g4_member");

while($row = sql_fetch_array($sql)){
    echo "<pre>";
    var_dump($row);
    echo "</pre>";
}

?>
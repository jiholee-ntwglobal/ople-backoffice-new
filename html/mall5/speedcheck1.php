<?
// 서버 평균 부하량 측정
function sys_getloadavg_hack()
{
    $str = substr(strrchr(shell_exec("uptime"),":"),1);
    $avs = array_map("trim",explode(",",$str));

    return $avs;
}

//print_r(sys_getloadavg_hack());
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    $load = $load[0] * 100;   
} else {
    $load = sys_getloadavg_hack();
    $load = $load[0] * 100;
}
?>
        <p style="margin:20px 0;">현재 서버활동량: <?=$load;?> <br>
( 0~180 => 양호 , 180~250 => 가끔렉걸림, 250이상 렉쩔어, 400이상 서
버다운직전)</p>


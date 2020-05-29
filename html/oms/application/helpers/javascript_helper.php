<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 4:16
 */
function alert($msg, $url=null)
{
    echo '<script type="text/javascript">';
    echo 'alert("' . $msg . '");';
    if($url == "reload")
	{
		echo "window.location.reload()";
	}
	else
	{
    	echo $url == null ? 'history.back();' : 'location.href="' . $url . '";';
	}
    echo '</script>';
}
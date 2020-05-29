<?php
function guard_script1($str)
{
	return preg_replace("/\<([\/]?)(script|iframe|link|style)([^\>]*)\>/i", "", $str);
}
function guard_script2($str)
{
	return preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|<img[^>]*>|style="[^"]*")|<script[^>]*>.*?<\/script>|<style[^>]*>.*?<\/style>|<!--.*?-->/i', '', $str);
	//$str = preg_replace("/\"|<|>|\'/", "", $str);
	//return $str;
}

while(list($k, $v) = each($_POST))
{
	if(is_array($_POST[$k]))
	{
		while(list($k2, $v2) = each($_POST[$k]))
			$_POST[$k][$k2] = guard_script1($v2);

		@reset($_POST[$k]);
	}
	else
		$_POST[$k] = guard_script1($v);
}
@reset($_POST);
?>
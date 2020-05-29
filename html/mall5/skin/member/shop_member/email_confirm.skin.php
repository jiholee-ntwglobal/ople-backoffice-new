<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 김선용 201005 :
if ($g4['https_url2'])
{
	if(preg_match("/^okflex\.com/", strtolower($_SERVER['HTTP_HOST'])))
	{
		if (isset($url)) {
			if (preg_match("/^\.\.\//", $url))
				$url = urlencode($g4['url_login']."/".preg_replace("/^\.\.\//", "", $url));
			else
				$url = $g4[url_login].$urlencode;
		} else {
			$url = $g4[url_login];
		}
		$faction = $_SERVER['PHP_SELF'];
	}
	else if($config['cf_ssl_use'])
	{
		if (isset($url)) {
			if (preg_match("/^\.\.\//", $url))
				$url = urlencode($g4['url_login']."/".preg_replace("/^\.\.\//", "", $url));
			else
				$url = $g4[url_login].$urlencode;
		} else {
			$url = $g4[url_login];
		}
		$faction = "{$g4['https_url2']}/bbs/login_check.php";
	}
	else
	{
	    $url = $urlencode;
		$faction = "{$g4['bbs_path']}/login_check.php";
	}
}
else {
    $url = $urlencode;
	$faction = "{$g4['bbs_path']}/login_check.php";
}
$faction = $_SERVER['PHP_SELF'];
?>
<br>
<!--table width="755" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="319"><img src="<?=$g4['path']?>/images/member/member_title01.gif" width="319" height="26"></td>
	<td width="353" align="right" class="font11">HOME &gt; <span class="font11_orange">로그인</span></td>
</tr>
<tr><td height="1" colspan="2" bgcolor="#fa5a00"></td></tr>
</table><p-->

<form name="flogin" method="post" onsubmit="return flogin_submit(this);" action="<?=$faction?>" autocomplete="off" style="padding:0px;">
<input type="hidden" name="url" value='<?=$url?>'>
<!--0721_수정_로그인영역-->
<div class='orange_box'>
<p class='box_title'><img src="<?=$g4['path']?>/images/member/member_01_email.gif" alt='오플비회원E-mail인증'></p>
<fieldset>
	<div class='box_login_Area'>
		<p class='box_input'>
			<span><input class=ed size=15 id='login_email' name='login_email' itemname="E-Mail" required minlength="2" value="<?=$tmp_mb_id?>" placeholder="E-Mail"></span>
			<span><input type="password" class=ed maxlength=20 size=15 name="wr_password" id="mb_password" required itemname="패스워드" value="<?=$tmp_mb_password?>" placeholder="비밀번호"></span>
		</p>
		<p class='button'><input type=image src="<?=$g4['path']?>/images/member/member_01_confirmbtn.gif" border="0"></p>
	</div>
	<p class='box_button'>
		<a href="./login.php"><img src='<?=$member_skin_path?>/img/btn_member_login.gif' border=0></a>
		<a href="./register.php"><img src='<?=$member_skin_path?>/img/btn_member_join.gif' border=0></a>
        <a href="javascript:;" onclick="win_password_forget('./password_forget.php');"><img src='<?=$member_skin_path?>/img/btn_passfind.gif' border=0></a>
	</p>
</fieldset>

</div>
<!--
<table cellpadding=2 bgcolor=#F6F6F6 align=center>
<tr><td>
    <table width=480 bgcolor=#FFFFFF cellpadding=0 border=0>
    <tr><td align=center height=60><img src='<?=$member_skin_path?>/img/title_login.gif'></td></tr>
    <tr>
        <td>
            <table>
            <tr>
                <td>
                    <table>
                    <tr>
                        <td width=120 align=right>아이디</td>
                        <td>&nbsp;&nbsp;<input class=ed maxlength=20 size=15 id='login_mb_id' name=mb_id itemname="아이디" required minlength="2" value="<?=$tmp_mb_id?>"></td>
                    </tr>
                    <tr>
                        <td width=120 align=right>패스워드</td>
                        <td>&nbsp;&nbsp;<input type=password class=ed maxlength=20 size=15 name=mb_password itemname="패스워드" required value="<?=$tmp_mb_password?>"></td>
                    </tr>
                    </table>
                </td>
                <td><input type=image src='<?=$member_skin_path?>/img/btn_confirm.gif' border=0 align=absmiddle></td>
            </tr>
            </table>
        </td>
    </tr>
    <tr><td height=30 align=center><a href="./register.php"><img src='<?=$member_skin_path?>/img/btn_member_join.gif' border=0></a>
            <a href="javascript:;" onclick="win_password_forget('./password_forget.php');"><img src='<?=$member_skin_path?>/img/btn_passfind.gif' border=0></a></td></tr>
    <tr><td background='<?=$member_skin_path?>/img/dot_line.gif'></td></tr>
    <tr><td height=60 style='padding-left:70px; line-height:150%'>
        · 회원이 아니실 경우에는 '무료 회원가입'을 하십시오.<br>
        · 패스워드를 잊으셨다면 '아이디/패스워드 찾기'로 찾으시면 됩니다.</td></tr>
    </table></td></tr>
</table>
-->
</form>

<script language='Javascript'>
document.getElementById('login_mb_id').focus();

function flogin_submit(f)
{
    return true;
}
</script>





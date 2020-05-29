<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

$url = '';
if ($g4['https_url']) {
    if (preg_match("/^\./", $urlencode))
        $url = $g4[url];
    else
        $url = $g4[url].$urlencode;
} else {
    $url = $urlencode;
}
?>
<script language="javascript">
function bookmark(){
window.external.AddFavorite('http://xxxxxx.com/', '사이트명을 입력하세요')
}
</script>
<script type="text/javascript" src="<?=$g4[path]?>/js/capslock.js"></script>
<script language=javascript src="<?=$outlogin_skin_path?>/init.js"></script>

<table width="176" height="40" border="0" cellpadding="0" cellspacing="0" background="<?=$outlogin_skin_path?>/img/btn_bg.png">
	<tr>
		<td align=center valign="bottom"><a href="javascript:view_cover('LayLoginForm','','');"><img src="<?=$outlogin_skin_path?>/img/btn_login.gif" border=0></a><a href="<?=$g4[bbs_path]?>/register.php"><img src="<?=$outlogin_skin_path?>/img/btn_join.gif" border=0></a><a href="#" onclick="bookmark();"><img src="<?=$outlogin_skin_path?>/img/btn_fav.gif" border=0></a></td>
</tr>
</table>

<div id=LayLoginForm style="display: none; z-index: 2; left: 0px; width: 536px; position: absolute; top: 0px; height:226px">

<table style="border: #389af1 3px solid;" cellspacing=0 cellpadding=2 width=528 align=center border=0 background="<?=$outlogin_skin_path?>/img/login_bg.gif">
<form method=post name=lay_login_form onsubmit="return flogin_submit(this);" autocomplete="off" onKeypress="flogin_keypress(this);">
<input type="hidden" name="url" value='<?=$url?>'>
	<tr>
	<td>
		<table cellspacing=0 cellpadding=0 width="100%" border=0>
		<tr><td colspan=3 height=4></td></tr>
		<tr>
			<td colspan='2'></td>
			<td align='right' style="padding-top:5px;" valign=top><a onclick="cover_off('LayLoginForm')" href="javascript:;"><img src="<?=$outlogin_skin_path?>/img/close.gif" width="42" height="11" border=0></a></td>
		</tr>
		<tr>
			<td colspan='3'>

        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:19px;">
        <tr>
            <td width="100%" height="185" align="center"><iframe src="about:blank" mce_src="about:blank" scrolling="no" frameborder="0" style="position:absolute;width:528px;height:170px;top:3px;left:3px;z-index:-1;border:none;display:block"></iframe>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr><td width="300"></td>
                    <td width="140">
                        <table width="120" border="0" cellpadding="0" cellspacing="0" height=48>
                        <tr>
                            <td height=24 style=padding-left:8><INPUT class="ed" maxLength=20 size=20 name=mb_id itemname="아이디" required minlength="2" style="width:120px;height:22px; background-color:#FFFFFF;border: 1px solid #CCCCCC";></td></tr>
                        <tr>                            
                            <td height=24 style=padding-left:8><INPUT type=password class="ed" maxLength=20 size=20 name=mb_password itemname="패스워드" required style="width:120px;height:22px; background-color:#FFFFFF;border: 1px solid #CCCCCC;"></td>
                        </tr>
                        </table>
                    </td>
                    <td width="88" valign="top"><INPUT type="image" value="로그인" src="<?=$outlogin_skin_path?>/img/login_button.gif"></td>
                </tr>
                <tr>
                    <td></td><td height="30" colspan=2><div id="ld_right"><input type="checkbox" name="auto_login" value="1" onclick="if (this.checked) { if (confirm('자동로그인을 사용하시면 다음 접속시 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n자동로그인을 사용하시겠습니까?')) { this.checked = true; } else { this.checked = false; } }"></div></td>
                </tr>
				<tr><td colspan=3 height=13></td></tr>
                <tr>
                    <td></td><td height="26" colspan='2' align=center style=padding-left:93><a href="<?=$g4[bbs_path]?>/register.php"><img src="<?=$outlogin_skin_path?>/img/login_join_button.gif" border=0 align="absmiddle"></a></td>
                </tr>
                <tr>
                    <td></td><td height="26" colspan='2' align=center style=padding-left:93><a href="javascript:win_password_lost();"><img src="<?=$outlogin_skin_path?>/img/login_pw_find_button.gif" width="108" height="20" border=0 align="absmiddle"></td>
                </tr>
                </table></td>
        </tr>
        </table>
	</td>
	</tr>
</form>
</table>


<script language='Javascript'>
function flogin_submit(f)
{
    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/login_check.php';";
    else
        echo "f.action = '$g4[bbs_path]/login_check.php';";
    ?>

    return true;
}
</script>

<script language='Javascript'> 
function flogin_keypress(f) { 
	if (event.keyCode==13 && document.getElementById("LayLoginForm").style.display != "none") { 
		if (flogin_submit(f)) f.submit(); 
	} 
}
</script> 
</div>

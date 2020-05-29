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
<script type="text/javascript" language=JavaScript>
// 엠파스 로긴 참고
var bReset = true;
function chkReset(f) 
{
    if (bReset) { if ( f.mb_id.value == '아이디' ) f.mb_id.value = ''; bReset = false; }
    document.getElementById("pw1").style.display = "none";
    document.getElementById("pw2").style.display = "";
}
</script>

<!-- 로그인 전 외부로그인 시작 -->
<table width="220" border="0" cellpadding="0" cellspacing="0">
<form name="fhead" method="post" action="javascript:fhead_submit(document.fhead);" autocomplete="off">
<input type="hidden" name="url" value="<?=$url?>">
<tr> 
    <td width="220" height="42" colspan="6" valign="top"><img src="<?=$outlogin_skin_path?>/img/login_top.gif" width="220" height="42"></td>
</tr>
<tr> 
    <td width="5" height="114" rowspan="5" background="<?=$outlogin_skin_path?>/img/login_left_bg.gif"></td>
    <td width="210" height="9" colspan="4"></td>
    <td width="5" height="114" rowspan="5" background="<?=$outlogin_skin_path?>/img/login_right_bg.gif"></td>
</tr>
<tr> 
    <td width="210" colspan="4">
        <table width="210" border="0" cellpadding="0" cellspacing="0">
        <tr> 
            <td width="141">
                <table width="141" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="35" height="23"><img src="<?=$outlogin_skin_path?>/img/login_id.gif" width="35" height="23"></td>
                    <td width="106" height="23" colspan="2" align="center"><input name="mb_id" type="text" class=ed size="12" maxlength="20" required itemname="아이디" value='아이디' onMouseOver='chkReset(this.form);' onFocus='chkReset(this.form);'></td>
                </tr>
                <tr> 
                    <td width="35" height="23"><img src="<?=$outlogin_skin_path?>/img/login_pw.gif" width="35" height="23"></td>
                    <td id=pw1 width="106" height="23" colspan="2" align="center"><input type="text" class=ed size="12" maxlength="20" required itemname="패스워드" value='패스워드' onMouseOver='chkReset(this.form);' onfocus='chkReset(this.form);'></td>
                    <td id=pw2 style='display:none;' width="106" height="23" colspan="2" align="center"><input name="mb_password" type="password" class=ed size="12" maxlength="20" itemname="패스워드" onMouseOver='chkReset(this.form);' onfocus='chkReset(this.form);'></td>
                </tr>
                </table>
            </td>
            <td width="69" height="46" rowspan="2" align="center"><input type="image" src="<?=$outlogin_skin_path?>/img/login_button.gif" width="52" height="46"></td>
        </tr>
        </table></td>
</tr>
<tr> 
    <td width="35" height="28"></td>
    <td width="20" height="28" valign="top"><input type="checkbox" name="auto_login" value="1" onclick="if (this.checked) { if (confirm('자동로그인을 사용하시면 다음부터 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\n\공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?')) { this.checked = true; } else { this.checked = false; } }"></td>
    <td width="86"><img src="<?=$outlogin_skin_path?>/img/login_auto.gif" width="46" height="28"></td>
    <td width="69" height="28"></td>
</tr>
<tr> 
    <td height="20"></td>
    <td height="20" colspan="3">
        <a href="javascript:win_password_forget();"><img src="<?=$outlogin_skin_path?>/img/login_pw_find_button.gif" width="90" height="20" border="0"></a>
        <a href="<?=$g4[bbs_path]?>/register.php"><img src="<?=$outlogin_skin_path?>/img/login_join_button.gif" width="69" height="20" border="0"></a></td>
</tr>
<tr> 
    <td width="210" colspan="4"></td>
</tr>
<tr> 
    <td width="220" height="14" colspan="6"><img src="<?=$outlogin_skin_path?>/img/login_down.gif" width="220" height="14"></td>
</tr>
</form>
</table>

<script language="JavaScript">
function fhead_submit(f)
{
    if (!f.mb_id.value)
    {
        alert("회원아이디를 입력하십시오.");
        f.mb_id.focus();
        return;
    }

    if (document.getElementById('pw2').style.display!='none' && !f.mb_password.value)
    {
        alert("패스워드를 입력하십시오.");
        f.mb_password.focus();
        return;
    }

    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/login_check.php';";
    else
        echo "f.action = '$g4[bbs_path]/login_check.php';";
    ?>
    f.submit();
}
</script>
<!-- 로그인 전 외부로그인 끝 -->

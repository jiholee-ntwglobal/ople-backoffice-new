<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<form name="fmemberconfirm" method="post" onsubmit="return fmemberconfirm_submit(this);">
<input type=hidden name=mb_id value='<?=$member[mb_id]?>'>
<input type=hidden name=w     value='u'>

<!--0721_수정_로그인영역-->
<div class='orange_box'>
<p class='box_title'><img src="<?=$member_skin_path?>/img/modify_img.gif" alt='오플정보수정'></p>
<fieldset>
	<div class='box_login_Area'>
		<p class='box_input'>
			<span><strong><?=$member[mb_id]?></strong></span>
			<span><INPUT type=password maxLength=20 size=15 name=mb_password itemname="패스워드" placeholder="비밀번호" required></span>
		</p>
		<p class='button'><input type="image" name="btn_submit" id="btn_submit" src="<?=$member_skin_path?>/img/ok_button.gif" border=0></p>
	</div>
</fieldset>
<div class='box_info'>
	<p>외부로부터 회원님의 정보를 안전하게 보호하기 위해<br>패스워드를 확인하셔야 합니다.</p>
</div>
</div>

<!--
<table width="668" border="0" cellspacing="0" cellpadding="0" align=center>
<tr>
    <td width="20" height="26"></td>
    <td width="628"></td>
    <td width="20"></td>
</tr>
<tr>
    <td width="20" height="2"></td>
    <td width="628" bgcolor="#8F8F8F"></td>
    <td width="20"></td>
</tr>
<tr>
    <td width="20" height="48"></td>
    <td width="628" align="right" background="<?=$member_skin_path?>/img/modify_table_bg_top.gif"><img src="<?=$member_skin_path?>/img/modify_img.gif" width="344" height="48"></td>
    <td width="20"></td>
</tr>
<tr>
    <td width="20" height="223"></td>
    <td width="628" align="center" background="<?=$member_skin_path?>/img/modify_table_bg.gif">
        <table width="460" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td width="460" height="223" align="center" bgcolor="#FFFFFF">
                <table width="350" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="250">
                        <table width="250" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="10"><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"></td>
                            <td width="90" height="26"><b>회원아이디</b></td>
                            <td width="150"><b><?=$member[mb_id]?></b></td>
                        </tr>
                        <tr>
                            <td><img src="<?=$member_skin_path?>/img/icon.gif" width="3" height="3"></td>
                            <td height="26"><b>패스워드</b></td>
                            <td><INPUT type=password maxLength=20 size=15 name=mb_password itemname="패스워드" required></td>
                        </tr>
                        </table>
                    </td>
                    <td width="100" valign="top">
					<input type="image" name="btn_submit" id="btn_submit" src="<?=$member_skin_path?>/img/ok_button.gif" width="65" height="52" border=0>
					</td>
                </tr>
                <tr>
                    <td height="20" colspan="2"></td>
                </tr>
                <tr>
                    <td height="1" background="<?=$member_skin_path?>/img/dot_line.gif" colspan="2"></td>
                </tr>

                <table>
                <tr align="center">
                    <td height="80" colspan="2">외부로부터 회원님의 정보를 안전하게 보호하기 위해<br>패스워드를 확인하셔야 합니다.</td>
                </tr>
                </table></td>
        </tr>
        </table></td>
    <td width="20"></td>
</tr>
<tr>
    <td width="20" height="1"></td>
    <td width="628" bgcolor="#F0F0F0"></td>
    <td width="20"></td>
</tr>
<tr>
    <td height="20" colspan="3"></td>
</tr>
</table>
-->
</form>


<script type="text/Javascript">
document.onload = document.fmemberconfirm.mb_password.focus();
function fmemberconfirm_submit(f)
{
	document.getElementById("btn_submit").disabled = true;
    f.action = "<?=$url?>";
	return true;
}
</script>

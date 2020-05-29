<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<table width=600 cellspacing=0 cellspacing=0 align=center><tr><td>

<table width="100%" cellspacing="0" cellpadding="0">
<form name="fregister" method="POST" onsubmit="javascript:fregister_submit(document.fregister);" autocomplete="off">
<tr>
    <td align=center><img src="<?=$member_skin_path?>/img/join_title.gif" width="624" height="72"></td>
</tr>
</table>

<? if ($config[cf_use_jumin]) { // 주민등록번호를 사용한다면 ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td height=25></td>
</tr>
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100% border=0>
        <TR bgcolor="#FFFFFF">
            <TD width="140" height=30>&nbsp;&nbsp;&nbsp;<b>이름</b></TD>
            <TD width="">&nbsp;&nbsp;&nbsp;<INPUT name=mb_name itemname="이름" required minlength="2" nospace hangul></TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD height=30>&nbsp;&nbsp;&nbsp;<b>주민등록번호</b></TD>
            <TD>&nbsp;&nbsp;&nbsp;<INPUT name=mb_jumin itemname="주민등록번호" required jumin minlength="13" maxLength=13><font style="font-family:돋움; font-size:9pt; color:#66A2C8">&nbsp;&nbsp;※ 숫자 13자리 중간에 - 없이 입력하세요.</font></TD>
        </TR>
        </TABLE></td>
</tr>
</table>
<? } ?>

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td height="20"></td>
</tr>
<tr>
    <td height="48" background="<?=$member_skin_path?>/img/login_table_bg_top.gif">&nbsp; <b>회원가입약관</b></td>
</tr>
<tr>
    <td height="223" align="center" valign="top" background="<?=$member_skin_path?>/img/login_table_bg.gif"><TEXTAREA style="WIDTH: 100%" rows=15 readOnly><?=get_text($config[cf_stipulation])?></TEXTAREA></td>
</tr>
<tr>
    <td><INPUT type=checkbox value=1 id=agree name=agree>&nbsp;<label for='agree'>회원가입약관을 읽었으며 내용에 동의합니다.</label></td>
</tr>
<tr>
    <td height="35" background="<?=$member_skin_path?>/img/line.gif"></td>
</tr>

<tr>
    <td height="20"></td>
</tr>
<tr>
    <td height="48" background="<?=$member_skin_path?>/img/login_table_bg_top.gif">&nbsp; <b>개인정보보호정책</b></td>
</tr>
<tr>
    <td height="223" align="center" valign="top" background="<?=$member_skin_path?>/img/login_table_bg.gif"><TEXTAREA style="WIDTH: 100%" rows=15 readOnly><?=get_text($config[cf_privacy])?></TEXTAREA></td>
</tr>
<tr>
    <td><INPUT type=checkbox value=1 id=agree2 name=agree2>&nbsp;<label for='agree2'>개인정보보호정책을 읽었으며 내용에 동의합니다.</label></td>
</tr>
<tr>
    <td height="35" background="<?=$member_skin_path?>/img/line.gif"></td>
</tr>

<tr>
    <td align="center"><INPUT type=image src="<?=$member_skin_path?>/img/join_ok_btn.gif" border=0></td>
</tr>
</form>
</table>

<script type="text/javascript">
function fregister_submit(f)
{
    if (!f.agree.checked) {
        alert("회원가입약관의 내용에 동의해야 회원가입 하실 수 있습니다.");
        f.agree.focus();
        return;
    }

    if (!f.agree2.checked) {
        alert("개인정보보호정책의 내용에 동의해야 회원가입 하실 수 있습니다.");
        f.agree2.focus();
        return;
    }

    f.action = "./register_form.php";
    f.submit();
}

if (typeof(document.fregister.mb_name) != "undefined")
    document.fregister.mb_name.focus();
</script>

</td></tr></table>
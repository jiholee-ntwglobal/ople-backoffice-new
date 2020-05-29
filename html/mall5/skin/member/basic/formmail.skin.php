<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width="600" height="50" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" valign="middle" bgcolor="#EBEBEB"><table width="590" height="40" border="0" cellspacing="0" cellpadding="0">
                <tr> 
                    <td width="25" align="center" bgcolor="#FFFFFF" ><img src="<?=$member_skin_path?>/img/icon_01.gif" width="5" height="5"></td>
                    <td width="75" align="left" bgcolor="#FFFFFF" ><font color="#666666"><b><?=$g4[title]?></b></font></td>
                    <td width="490" bgcolor="#FFFFFF" ></td>
                </tr>
            </table></td>
    </tr>
</table>

<table width="600" border="0" cellspacing="0" cellpadding="0">
    <tr> 
        <td width="600" height="20" colspan="4"></td>
    </tr>
    <tr> 
        <td width="30" height="24"></td>
        <td width="20" align="center" valign="middle" bgcolor="#EFEFEF"><img src="<?=$member_skin_path?>/img/arrow_01.gif" width="7" height="5"></td>
        <td width="520" align="left" valign="middle" bgcolor="#EFEFEF"><b><?=$name?></b>님께 메일보내기</td>
        <td width="30" height="24"></td>
    </tr>
</table>

<table width="600" border="0" cellspacing="0" cellpadding="0">
<form name="fformmail" method="post" action="javascript:fformmail_submit(document.fformmail);" enctype="multipart/form-data">
<input type="hidden" name="to"     value="<?=$email?>">
<input type="hidden" name="attach" value="2">
<tr> 
    <td height="330" align="center" valign="top"><table width="540" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td height="20"></td>
        </tr>
        <tr> 
            <td height="2" bgcolor="#808080"></td>
        </tr>
        <tr> 
            <td width="540" height="2" align="center" valign="top" bgcolor="#FFFFFF">
                <table width="540" border="0" cellspacing="0" cellpadding="0">
                
                <? if ($is_member) { // 회원이면 ?>
                <input type='hidden' name='fnick'  value='<?=$member[mb_nick]?>'>
                <input type='hidden' name='fmail'  value='<?=$member[mb_email]?>'>
                <? } else { ?>
                <tr> 
                    <td width="80" height="27" align="center"><b>이름</b></td>
                    <td width="10" valign="bottom"><img src="<?=$member_skin_path?>/img/l.gif" width="1" height="8"></td>
                    <td width="450"><input type=text style='width:90%;' name='fnick' required minlength=2 itemname='이름'></td>
                </tr>
                <tr> 
                    <td width="80" height="27" align="center"><b>E-mail</b></td>
                    <td width="10" valign="bottom"><img src="<?=$member_skin_path?>/img/l.gif" width="1" height="8"></td>
                    <td width="450"><input type=text style='width:90%;' name='fmail' required email itemname='E-mail'></td>
                </tr>
                <? } ?>

                <tr> 
                    <td width="80" height="27" align="center"><b>제목</b></td>
                    <td width="10" valign="bottom"><img src="<?=$member_skin_path?>/img/l.gif" width="1" height="8"></td>
                    <td width="450"><input type=text style='width:90%;' name='subject' required itemname='제목'></td>
                </tr>
                <tr> 
                    <td height="1" colspan="3" bgcolor="#E9E9E9"></td>
                </tr>
                <tr> 
                    <td width="80" height="28" align="center"><b>선택</b></td>
                    <td width="10" valign="bottom"><img src="<?=$member_skin_path?>/img/l.gif" width="1" height="8"></td>
                    <td width="450"><input type='radio' name='type' value='0' checked> TEXT <input type='radio' name='type' value='1' > HTML <input type='radio' name='type' value='2' > TEXT+HTML</td>
                </tr>
                <tr> 
                    <td height="1" colspan="3" bgcolor="#E9E9E9"></td>
                </tr>
                <tr> 
                    <td width="80" height="150" align="center"><b>내용</b></td>
                    <td width="10" valign="bottom"><img src="<?=$member_skin_path?>/img/l.gif" width="1" height="8"></td>
                    <td width="450"><textarea name="content" style='width:90%;' rows='9' required itemname='내용'></textarea></td>
                </tr>
                <tr> 
                    <td height="1" colspan="3" bgcolor="#E9E9E9"></td>
                </tr>
                <tr> 
                    <td width="80" height="27" align="center">첨부파일 #1</td>
                    <td width="10" valign="bottom"><img src="<?=$member_skin_path?>/img/l.gif" width="1" height="8"></td>
                    <td width="450"><input type=file style='width:90%;' name='file1'></td>
                </tr>
                <tr> 
                    <td height="1" colspan="3" bgcolor="#E9E9E9"></td>
                </tr>
                <tr> 
                    <td width="80" height="27" align="center">첨부파일 #2</td>
                    <td width="10" valign="bottom"><img src="<?=$member_skin_path?>/img/l.gif" width="1" height="8"></td>
                    <td width="450"><input type=file style='width:90%;' name='file2'></td>
                </tr>
                <tr> 
                    <td height="1" colspan="3" bgcolor="#E9E9E9"></td>
                </tr>
                </table></td>
        </tr>
        </table></td>
</tr>
<tr> 
    <td height="2" align="center" valign="top" bgcolor="#D5D5D5"></td>
</tr>
<tr>
    <td height="2" align="center" valign="top" bgcolor="#E6E6E6"></td>
</tr>
<tr>
    <td height="40" align="center" valign="bottom"><input id=btn_submit type=image src="<?=$member_skin_path?>/img/btn_mail_send.gif" border=0>&nbsp;&nbsp;<a href="javascript:window.close();"><img src="<?=$member_skin_path?>/img/btn_close.gif" width="48" height="20" border="0"></a></td>
</tr>
</form>
</table>

<script language="JavaScript">
with (document.fformmail) {
    if (typeof fname != "undefined")
        fname.focus();
    else if (typeof subject != "undefined")
        subject.focus();
}

function fformmail_submit(f)
{
    if (f.file1.value || f.file2.value) {
        // 4.00.11
        if (!confirm("첨부파일의 용량이 큰경우 전송시간이 오래 걸립니다.\n\n메일보내기가 완료되기 전에 창을 닫거나 새로고침 하지 마십시오."))
            return;
    }

    document.getElementById('btn_submit').disabled = true;

    f.action = "./formmail_send.php";
    f.submit();
}
</script>

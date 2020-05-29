<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<div class='pop_title'>
	<p><?=$g4[title]?></p>
</div>

<div class="pop_style">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form name=fpasswordforget method=post action="javascript:fpasswordforget_submit(document.fpasswordforget);" autocomplete=off>
    <tr>
        <td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:solid 5px #f0f1f3;padding:30px 40px;">
            <tr>
                <td colspan="2" style="padding:5px;font-size:13px;font-weight:bold;color:#000;">회원아이디를 입력해주세요.</td>
             </tr>
             <tr>
                <td width="140px" style="padding:5px 0;">아이디</td>
                <td style="padding:5px 0;"><input type=text name='pass_mb_id' size=18 maxlength=20 itemname='회원아이디'></td>
             </tr>
		 </table>
         </td>
    </tr>
    <tr>
        <td style="padding-top:20px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:solid 5px #f0f1f3;padding:30px 40px;">
           <tr>
               <td colspan="2" style="padding:5px;font-size:13px;font-weight:bold;color:#000;">회원아이디를 잊으셨나요?</td>
            </tr>
            <tr>
               <td width="140px">이름</td>
               <td style="padding:5px 0;"><INPUT name=mb_name itemname="이름" size=18></td>
            </tr>

                    <? if ($config[cf_use_jumin]) { // 주민등록번호를 사용한다면(입력 받았다면) ?>
                    <tr>
                        <td width="140px"  style="padding:5px 0;">주민등록번호</td>
                        <td style="padding:5px 0;"><INPUT name=mb_jumin itemname="주민등록번호" jumin size=18 maxlength=13> - 없이 입력</td>
                    </tr>
                    <? } else { ?>
                    <tr>
                        <td width="140px">E-mail</td>
                        <td style="padding:5px 0;"><INPUT name=mb_email itemname="E-mail" email size=30></td>
                    </tr>
                    <? } ?>
                    </table></td>
            </tr>
    </tr>
    <tr>
        <td style="padding:20px 0;text-align:center;"><input type="image" src="<?=$member_skin_path?>/img/btn_next_01.gif" alt="다음" style="padding:0;"> <a href="javascript:window.close();"><img src="<?=$member_skin_path?>/img/btn_close.gif" border="0" alt="창닫기"></a></td>
    </tr>
</table>
</div>

<script TYPE="text/JavaScript">
function fpasswordforget_submit(f)
{
    if (f.pass_mb_id.value == "") {
        if (typeof f.mb_jumin != "undefined") {
            if (f.mb_name.value == "" || f.mb_jumin.value == "") {
                alert("회원아이디를\n\n아실 경우에는 회원아이디를\n\n모르실 경우에는 이름과 주민등록번호를\n\n입력하여 주십시오.");
                return;
            }
        } else if (typeof f.mb_email != "undefined") {
            if (f.mb_name.value == "" || f.mb_email.value == "") {
                alert("회원아이디를\n\n아실 경우에는 회원아이디를\n\n모르실 경우에는 이름과 E-mail 을\n\n입력하여 주십시오.");
                return;
            }
        }
    }

    f.action = "./password_forget2.php";
    f.submit();
}

document.fpasswordforget.pass_mb_id.focus();
</script>

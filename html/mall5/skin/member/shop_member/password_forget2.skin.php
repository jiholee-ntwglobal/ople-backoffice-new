<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<div class='pop_title'>
	<p>회원아이디 / 비밀번호찾기</p>
</div>

<div class="pop_style">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<form name=fpasswordforget2 method=post action="javascript:fpasswordforget2_submit(document.fpasswordforget2);" autocomplete=off>
<input type=hidden name=bo_table   value='<?=$bo_table?>'>
<input type=hidden name=pass_mb_id value='<?=$mb[mb_id]?>'>
    <tr> 
        <td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:solid 5px #f0f1f3;padding:30px 40px;">
                <tr> 
                    <td width="140px" style="padding:5px 0;">회원아이디</td>
                    <td><strong><?=$mb[mb_id]?></strong></td>
                </tr>
                <tr> 
                    <td style="padding:5px 0;">패스워드 분실시 질문</td>
					<td><strong><?=$mb[mb_password_q]?></td>
                </tr>
                <tr> 
                    <td colspan="2" style="padding:5px 0;">패스워드 분실시 답변</td>
                </tr>
                <tr> 
                    <td colspan="2" style="padding:5px 0;"><input type=text name='mb_password_a' size=55 required itemname='패스워드 분실시 답변' value=''></td>
                </tr>
            </table></td>
    </tr>
    <tr>
        <td style="padding:20px 0;text-align:center;"><input type="image" src="<?=$member_skin_path?>/img/btn_next_01.gif" style="padding:0;"> <a href="javascript:window.close();"><img src="<?=$member_skin_path?>/img/btn_close.gif" border="0"></a></td>
    </tr>
</form>
</table>
</div>

<script language='JavaScript'>
function fpasswordforget2_submit(f)
{
    f.action = "./password_forget3.php";
    f.submit();
}

document.fpasswordforget2.mb_password_a.focus();
</script>

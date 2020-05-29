<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<div class='pop_title'>
	<p>회원아이디 / 비밀번호찾기</p>
</div>

<div class="pop_style">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr> 
        <td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:solid 5px #f0f1f3;padding:30px 40px;">
                <tr> 
                    <td width="140px" style="padding:5px 0;">회원아이디</td>
                    <td><strong><?=$mb[mb_id]?></strong></td>
                </tr>
                <tr> 
                    <td style="padding:5px 0;">부여된 패스워드</td>
                    <td><strong><?=$change_password?></strong></td>
                </tr>
                <tr> 
                    <td colspan="2" style="padding:5px 0;">새로 부여된 패스워드는 로그인 후 변경해 주십시오.</td>
                </tr>
            </table></td>
    </tr>
    <tr>
        <td style="padding:20px 0;text-align:center;"><a href="javascript:window.close();"><img src="<?=$member_skin_path?>/img/btn_close.gif"></a></td>
    </tr>
</table>
</div>

<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<!--0721_수정_로그인영역-->
<div class='orange_box'>
<form name="fboardpassword" method=post action="javascript:fboardpassword_submit(document.fboardpassword);">
<input type=hidden name=w           value="<?=$w?>">
<input type=hidden name=bo_table    value="<?=$bo_table?>">
<input type=hidden name=wr_id       value="<?=$wr_id?>">
<input type=hidden name=comment_id  value="<?=$comment_id?>">
<input type=hidden name=sfl         value="<?=$sfl?>">
<input type=hidden name=stx         value="<?=$stx?>">
<input type=hidden name=page        value="<?=$page?>">

<p class='box_title'><img src="<?=$member_skin_path?>/img/secrecy_img.gif" alt='비밀번호확인'></p>
<fieldset>
	<div class='box_login_Area'>
		<p class='box_input'>
			<span><input type=password maxLength=20 size=15 name="wr_password" itemname="패스워드" placeholder="비밀번호" required></span>
		</p>
		<p class='button'><input name="image" type=image src="<?=$member_skin_path?>/img/btn_confirm.gif"></p>
	</div>
</fieldset>
<div class='box_info'>
	<p>이 게시물의 패스워드를 입력하십시오.</p>
</div>
</div>

</form>
<script language='JavaScript'>
document.fboardpassword.wr_password.focus();

function fboardpassword_submit(f)
{
    f.action = "<?=$action?>";
    f.submit();
}
</script>

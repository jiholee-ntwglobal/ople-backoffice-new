<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>
<style>
.sml8s {font-size:8pt; font-family:맑은 고딕,돋움; color:#FFFFFF;letter-spacing:-1;}
.sml8 {font-size:8pt; font-family:맑은 고딕,돋움; color:#6bd7fb;letter-spacing:-1;font-weight:bold;}

A:link    {color:FFFFFF;text-decoration:none;}
A:visited {color:FFFFFF;text-decoration:none;}
A:active  {color:FFFFFF;text-decoration:none;}
A:hover   {color:fff100;text-decoration:none;}
</style>
<table width="176" height="40" border="0" cellpadding="0" cellspacing="0" background="<?=$outlogin_skin_path?>/img/btn_bg.png">
<tr><td height=20 align=center class=sml8s valign=bottom><? if ($is_admin == "super" || $is_auth) { ?><a href="<?=$g4[admin_path]?>/"><font class=sml8><?=$nick?></a><?} else if ($member[mb_level] == '5' && $member[mb_3] == '2') { ?> <a href="../blog/"><font class=sml8><?=$nick?></a><? } else{ ?><span class='member'><strong><font class=sml8><?=$nick?></strong></span><? } ?><font class=sml8s>님 (<a href="javascript:win_memo();">쪽지 : <?=$memo_not_read?></a> / <a href="javascript:win_point();"><B>포인트 : <?=$point?></a>)</td></tr>
	<tr>
		<td align=center valign="bottom" height=20><a href="javascript:win_scrap();"><img src="<?=$outlogin_skin_path?>/img/btn_scr.gif" border=0></a><a href="<?=$g4[bbs_path]?>/member_confirm.php?url=register_form.php"><img src="<?=$outlogin_skin_path?>/img/btn_info.gif" border=0></a><a href="<?=$g4[bbs_path]?>/logout.php"><img src="<?=$outlogin_skin_path?>/img/btn_logout.gif" border=0></a></td>
</tr>
</table>

<script language="JavaScript">
// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
function member_leave() 
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?")) 
            location.href = "<?=$g4[bbs_path]?>/member_confirm.php?url=member_leave.php";
}
</script>


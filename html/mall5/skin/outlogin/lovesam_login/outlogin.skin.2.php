<?
if (!defined("_GNUBOARD_")) exit; // ���� ������ ���� �Ұ� 
?>
<style>
.sml8s {font-size:8pt; font-family:���� ���,����; color:#FFFFFF;letter-spacing:-1;}
.sml8 {font-size:8pt; font-family:���� ���,����; color:#6bd7fb;letter-spacing:-1;font-weight:bold;}

A:link    {color:FFFFFF;text-decoration:none;}
A:visited {color:FFFFFF;text-decoration:none;}
A:active  {color:FFFFFF;text-decoration:none;}
A:hover   {color:fff100;text-decoration:none;}
</style>
<table width="176" height="40" border="0" cellpadding="0" cellspacing="0" background="<?=$outlogin_skin_path?>/img/btn_bg.png">
<tr><td height=20 align=center class=sml8s valign=bottom><? if ($is_admin == "super" || $is_auth) { ?><a href="<?=$g4[admin_path]?>/"><font class=sml8><?=$nick?></a><?} else if ($member[mb_level] == '5' && $member[mb_3] == '2') { ?> <a href="../blog/"><font class=sml8><?=$nick?></a><? } else{ ?><span class='member'><strong><font class=sml8><?=$nick?></strong></span><? } ?><font class=sml8s>�� (<a href="javascript:win_memo();">���� : <?=$memo_not_read?></a> / <a href="javascript:win_point();"><B>����Ʈ : <?=$point?></a>)</td></tr>
	<tr>
		<td align=center valign="bottom" height=20><a href="javascript:win_scrap();"><img src="<?=$outlogin_skin_path?>/img/btn_scr.gif" border=0></a><a href="<?=$g4[bbs_path]?>/member_confirm.php?url=register_form.php"><img src="<?=$outlogin_skin_path?>/img/btn_info.gif" border=0></a><a href="<?=$g4[bbs_path]?>/logout.php"><img src="<?=$outlogin_skin_path?>/img/btn_logout.gif" border=0></a></td>
</tr>
</table>

<script language="JavaScript">
// Ż���� ��� �Ʒ� �ڵ带 �����Ͻø� �˴ϴ�.
function member_leave() 
{
    if (confirm("���� ȸ������ Ż�� �Ͻðڽ��ϱ�?")) 
            location.href = "<?=$g4[bbs_path]?>/member_confirm.php?url=member_leave.php";
}
</script>


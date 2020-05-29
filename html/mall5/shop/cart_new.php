<?
include_once("./_common.php");

$g4[title] = "장바구니";
include_once("./_head.php");
?>
<div style="padding-top:20px;"></div>
<table width="755" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="319"><img src="<?=$g4['path']?>/images/menu/menu_title01.gif" width="319" height="26"></td>
	<td width="353" align="right" class="font11">HOME &gt; <span class="font11_orange">장바구니</span></td>
</tr>
<tr><td height="1" colspan="2" bgcolor="#fa5a00"></td></tr>
</table><p>
<!--<img src="<?=$g4[shop_img_path]?>/top_cart.gif" border="0"><p>-->

<?
$s_page = 'cart.php';
$s_on_uid = get_session('ss_on_uid');
include "$g4[shop_path]/cartsub.inc_new.php";
?>
<br><br>

<?
include_once("./_tail.php");
?>
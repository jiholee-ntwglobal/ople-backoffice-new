<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
include("$g4[path]/config.php");
?>

<!-- 로그인 후 외부로그인 시작 -->

 <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="669" height="21">
                      </td>
                      <td width="8" height="21"></td>
                      <td width="1" height="21"><a href="<?=$g4['bbs_path']?>/logout.php?opidx=<?=$opidx?>&skin_idx=<?=$skin_idx?>" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image41','','<?=$g4['opskin_path']?>/img/bt_logout_on.gif',0)"><img src="<?=$g4['opskin_path']?>/img/bt_logout_off.gif" name="Image41" width="51" height="21" border="0" id="Image41" /></a></td>
                      <td width="1" height="21"></td>
                      <td width="74" height="21"><a href="<?=$subpage_url?>&subcon=Mypage1" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image51','','<?=$g4['opskin_path']?>/img/bt_member_on.gif',1)"><img src="<?=$g4['opskin_path']?>/img/bt_member_off.gif" name="Image51" width="74" height="21" border="0" id="Image51" /></a></td>
                      <td width="1" height="21"></td>
                      <td width="62" height="21"><a href="<?=$sub_bbs_url?>&subcon=Mypage2&bo_table=2Team_QA" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage('Image61','','<?=$g4['opskin_path']?>/img/bt_my_on.gif',1)"><img src="<?=$g4['opskin_path']?>/img/bt_my_off.gif" name="Image61" width="62" height="21" border="0" id="Image61" /></a></td>
                    </tr>
                  </table>
<script type="text/javascript">
// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
function member_leave() 
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?")) 
            location.href = "<?=$g4['bbs_path']?>/member_confirm.php?url=member_leave.php";
}
</script>
<!-- 로그인 후 외부로그인 끝 -->

<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

$admin = get_admin("super");

// 사용자 화면 우측과 하단을 담당하는 페이지입니다.
// 우측, 하단 화면을 꾸미려면 이 파일을 수정합니다.
?>

</td></tr></table>
<!-- 중간끝 -->



<!--
<table align=center width='<?=$table_width?>' cellpadding=0 cellspacing=0>
<tr>
    <td width=180 bgcolor=#EBEBEB><a href='<?=$g4[path]?>/'><img src='<?=$g4[path]?>/data/common/logo_img' border=0 style="filter:gray();"></a></td>
    <td><img src='<?=$g4[shop_img_path]?>/tail_img01.gif'></td>
    <td width=10></td>
    <td><img src='<?=$g4[shop_img_path]?>/tail_img02.gif'></td>
    <td width=770 bgcolor=#EBEBEB style='padding-left:10px;'>
        <table width=98% cellpadding=0 cellspacing=0 border=0>
        <tr><td height=30>
            <a href="<?=$g4[shop_path]?>/content.php?co_id=company">회사소개</a> | 
            <a href="<?=$g4[shop_path]?>/content.php?co_id=provision">서비스이용약관</a> | 
            <a href="<?=$g4[shop_path]?>/content.php?co_id=privacy">개인정보 보호정책</a>
            </td></tr>
        <tr><td height=1 bgcolor=#CBCBCB></td></tr>
        <tr><td height=60 style='line-height:150%'>
            <FONT COLOR="#46808F">
                <?=$default[de_admin_company_addr]?> / 
                전화 : <?=$default[de_admin_company_tel]?> / 
                팩스 : <?=$default[de_admin_company_fax]?> / 
                운영자 : <?=$admin[mb_name]?> <BR>
                사업자 등록번호 : <?=$default[de_admin_company_saupja_no]?> / 
                대표 : <?=$default[de_admin_company_owner]?> / 
                개인정보관리책임자 : <?=$default[de_admin_info_name]?> <br>
                통신판매업신고번호 : <?=$default[de_admin_tongsin_no]?>
                <? if ($default[de_admin_buga_no]) echo " / 부가통신사업신고번호 : $default[de_admin_buga_no]"; ?>
               <br>Copyright &copy; 2001-2011 <?=$default[de_admin_company_name]?>. All Rights Reserved. </FONT></td></tr></table>
    </td>
</tr>
</table>
<!-- 하단끝 -->


<?
$sec = get_microtime() - $begin_time;
$file = $_SERVER[PHP_SELF];
?>

</td>
<!--<td valign=top><?//include("$g4[shop_path]/boxtodayview.inc.php");?></td>-->
<td valign=top>
<table width="110" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><img src="<?=$g4['path']?>/images/main/main_quick_01.gif" width="110" height="247"></td>
  </tr>
  <tr>
    <td><a href="<?=$g4['shop_path']?>/mypage.php"><img src="<?=$g4['path']?>/images/main/main_quick_02.gif" width="110" height="41" border="0"></a></td>
  </tr>
  <tr>
    <td><a href="<?=$g4['bbs_path']?>/board.php?bo_table=qa"><img src="<?=$g4['path']?>/images/main/main_quick_03.gif" width="110" height="41" border="0"></a></td>
  </tr>
  <tr>
    <td><a href="javascript:addfavorites()"><img src="<?=$g4['path']?>/images/main/main_quick_04.gif" width="110" height="46" border="0"></a></td>
  </tr>
  <tr>
    <td style="padding-top:5px"><a href="/mall4/shop/item.php?it_id=1174531678"><img src="<?=$g4['path']?>/images/main/main_quick_05.gif" width="110" height="156" border="0"></a></td>
  </tr>
  <tr>
    <td style="padding-top:5px"><a href="/mall4/shop/item.php?it_id=1185983467"><img src="<?=$g4['path']?>/images/main/main_quick_06.gif" width="110" height="126" border="0"></a></td>
  </tr>
  <tr>
    <td style="padding-top:5px"><a href="/mall4/shop/item.php?it_id=1169234028"><img src="<?=$g4['path']?>/images/main/main_quick_07.gif" width="110" height="126" border="0"></a></td>
  </tr>
  <tr>
    <td style="padding-top:5px"><a href="/mall4/shop/item.php?it_id=1310335231"><img src="<?=$g4['path']?>/images/main/main_quick_08.gif" width="110" height="126" border="0"></a></td>
  </tr>
  <tr>
    <td style="padding-top:5px"><a href="/mall4/shop/item.php?it_id=1142549523"><img src="<?=$g4['path']?>/images/main/main_quick_09.gif" width="110" height="126" border="0"></a></td>
  </tr>
</table>
</td>

</tr>
</table>
<!-- 전체끝 -->

<!-- 하단 -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="15" colspan="3"></td>
  </tr>
  <tr>
    <td colspan="3" background="<?=$g4['path']?>/images/main/main_bottom_topbg.gif" style="padding-left:885px"><div class="btnTop"><a href="javascript:go_scroll_top();"><img src="<?=$g4['path']?>/images/main/main_bottom_top.gif" alt="TOP" border="0"></a></div></td>
  </tr>
  <tr>
    <td height="35" colspan="3" style="padding-left:198px"><table width="527" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><a href="<?=$g4['shop_path']?>/content.php?co_id=company"><img src="<?=$g4['path']?>/images/main/main_bottom_menu01.gif" width="111" height="22" border="0"></a></td>
        <td><a href="<?=$g4['bbs_path']?>/board.php?bo_table=faq"><img src="<?=$g4['path']?>/images/main/main_bottom_menu02.gif" width="82" height="22" border="0"></a></td>
        <td><a href="<?=$g4['shop_path']?>/content.php?co_id=provision"><img src="<?=$g4['path']?>/images/main/main_bottom_menu03.gif" width="112" height="22" border="0"></a></td>
        <td><a href="<?=$g4['shop_path']?>/content.php?co_id=privacy"><img src="<?=$g4['path']?>/images/main/main_bottom_menu04.gif" width="121" height="22" border="0"></a></td>
        <td><a href="<?=$g4['bbs_path']?>/board.php?bo_table=qa"><img src="<?=$g4['path']?>/images/main/main_bottom_menu05.gif" width="101" height="22" border="0"></a></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td width="20%" height="87" align="center"><img src="<?=$g4['path']?>/images/main/main_top_ci.gif" width="137" height="50"></td>
    <td width="48%"><span style="font-size:8pt; FONT-FAMILY: 돋움,verdana,arial;">고객상담 전화번호 : 070-7676-1633  오케이플렉스 <br>
      Copyright(C) 2003~2011 <font color="#ff5b00"><b>OPLE.COM & STOKOREA</b></font>. All rights Reserved.</span></td>
    <td width="32%"><img src="<?=$g4['path']?>/images/main/main_bottom_banner.gif" width="173" height="46"></td>
  </tr>
</table>
<!-- 하단 -->

<?
include_once("$g4[path]/tail.sub.php");
?>

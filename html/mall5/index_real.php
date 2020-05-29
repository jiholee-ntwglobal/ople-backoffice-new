<?
include_once("./_common.php");
include_once("$g4[path]/lib/latest.lib.php");

define("_INDEX_", TRUE);

$g4[title] = "";
include_once("$g4[path]/head.php");
?>
<script language="JavaScript" src="<?=$g4[path]?>/js/shop.js"></script>
<style type="text/css">
<!--
#apDiv1 {
	position:absolute;
	width:205px;
	height:80px;
	z-index:1;
}
/*
#apDiv2 {
	position:absolute;
	width:205px;
	height:80px;
	z-index:1;
	visibility: hidden;
}
*/
-->
</style>


   <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="459">

			<form name='frmsearch1' style='margin:0px;' action="<?=$g4['shop_path']?>/search.php">
			<input type='hidden' name='sfl' value='wr_subject||wr_content'>
			<input type='hidden' name='sop' value='and'>
			<input type='hidden' name='stx' value=''>

			<table width="449" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="3" background="<?=$g4['path']?>/images/main/main_search_bg.gif"><img src="<?=$g4['path']?>/images/main/main_search_left.gif" width="3" height="32"></td>
                <td width="428" background="<?=$g4['path']?>/images/main/main_search_bg.gif"><img src="<?=$g4['path']?>/images/main/main_search_title.gif" width="47" height="14" hspace="8">
				<select name='search_ca_id' style="width:140px; vertical-align:middle;">
					<option value="">- 전체상품 -</option>
					<?
					$hsql = " select ca_id, ca_name from $g4[yc4_category_table]
							  where length(ca_id) = '2'
								and ca_use = '1' ";
					if(!$is_admin)
						$hsql .= " and ca_id RegExp ('^ev')=0 ";
					$hsql .= " order by ca_order_print, ca_id ";
					$hresult = sql_query($hsql);
					for($k=0; $hrow=sql_fetch_array($hresult); $k++)
						echo "<option value='{$hrow['ca_id']}'>{$hrow['ca_name']}</option>";
					?>				
				</select>
                  <input type=text name=search_str class='ed' required itemname="검색어" value='<?=stripslashes(get_text($search_str))?>' size=25 style="vertical-align:middle;">
                  <input type=image src="<?=$g4['path']?>/images/main/main_search_button.gif" width="58" height="20" border="0" style="vertical-align:middle;"></a></td>
                <td width="3" align="right" background="<?=$g4['path']?>/images/main/main_search_bg.gif"><img src="<?=$g4['path']?>/images/main/main_search_right.gif" width="3" height="32"></td>
              </tr>
            </table>
			</form>

			</td>
            <td rowspan="2" valign="top"><table width="213" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td height="119" valign="top"><table width="213" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td>&nbsp;</td>
                    <td align=left>
					<!--
						<img src="<?=$g4['path']?>/images/main/main_notice_titler.gif" width="83" height="24" id="Image2" style="cursor:pointer" onMouseOver="MM_swapImage('Image2','','<?=$g4['path']?>/images/main/main_notice_titler.gif','Image1','','<?=$g4['path']?>/images/main/main_event_title.gif',0);MM_showHideLayers('apDiv1','','show','apDiv2','','hide')">
						<img src="<?=$g4['path']?>/images/main/main_event_title.gif" width="130" height="24" id="Image1" style="cursor:pointer" onMouseOver="MM_swapImage('Image2','','<?=$g4['path']?>/images/main/main_notice_title.gif','Image1','','<?=$g4['path']?>/images/main/main_event_titler.gif',0);MM_showHideLayers('apDiv1','','hide','apDiv2','','show')">
					-->
						<a href="<?=$g4['bbs_path']?>/board.php?bo_table=notice" title="공지사항게시판"><img src="<?=$g4['path']?>/images/main/main_notice_titler.gif" width="83" height="24" border=0></a>
					</td>
                  </tr>
                  <tr>
                    <td height="95" colspan="2" valign="top" style="padding-top:7px">
					
					<div id="apDiv1">
					<?=latest('shop_notice', 'notice', 4, 25);?>
					</div>
				
					</td>
                    </tr>
                </table></td>
              </tr>
              <tr>
                <td><img src="<?=$g4['path']?>/images/main/main_center_banner01.gif" width="213" height="92"></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td height="169" style="padding-top:10px"><a href="<?=$g4['path']?>/shop/list.php?ca_id=t0"><img src="<?=$g4['path']?>/images/main/main_visual.gif" border=0></a>
			<!--
			<script type="text/javascript">
		       flashWrite('<?//=$g4['path']?>/flash/visual.swf', 449, 169, '', '','transparent');
			</script>
			-->
            </td>
            </tr>
        </table></td>
      </tr>
      <tr>
        <td style="padding-top:10px"><table width="672" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><a href="<?=$g4['path']?>/shop/list.php?ca_id=1010"><img src="<?=$g4['path']?>/images/main/main_center_banner02.gif" width="222" height="129" border="0"></a></td>
            <td><a href="<?=$g4['path']?>/shop/list.php?ca_id=l0"><img src="<?=$g4['path']?>/images/main/main_center_banner03.gif" width="219" height="129" border="0"></a></td>
            <td><a href="<?=$g4['path']?>/shop/list.php?ca_id=b0"><img src="<?=$g4['path']?>/images/main/main_center_banner04.gif" width="231" height="129" border="0"></a></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td style="padding-top:10px"><table width="672" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td><a href="<?=$g4['path']?>/shop/list.php?ca_id=o0"><img src="<?=$g4['path']?>/images/main/main_center_banner05.gif" width="331" height="121" border="0"></a></td>
            <td align="right"><a href="<?=$g4['path']?>/shop/list.php?ca_id=p0"><img src="<?=$g4['path']?>/images/main/main_center_banner06.gif" width="331" height="121" border="0"></a></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td style="padding-top:10px"><img src="<?=$g4['path']?>/images/main/main_center_banner07.gif" width="672" height="109"></td>
      </tr>
      <tr>
        <td style="padding-top:10px">
		<?
        // 최신상품
        $type = 3;
        if ($default["de_type{$type}_list_use"]) 
        {
            display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
        }
        ?>
		</td>
      </tr>
      <tr>
        <td style="padding-top:10px">
		<?
		// 히트(인기)상품
		$type = 1;
		if ($default["de_type{$type}_list_use"]) 
		{
			display_type($type, $default["de_type{$type}_list_skin"], $default["de_type{$type}_list_mod"], $default["de_type{$type}_list_row"], $default["de_type{$type}_img_width"], $default["de_type{$type}_img_height"]);
		}
		?>
		</td>
      </tr>
	</table>

<?
include "$g4[shop_path]/newwin.inc.php"; // 새창띄우기

include_once("$g4[path]/tail.php");
?>

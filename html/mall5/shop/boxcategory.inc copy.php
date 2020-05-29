<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

//
// 2단계 분류 레이어 표시
//
$menu = ""; // 메뉴 레이어 임시저장 변수 (처음엔 아무값도 없어야 합니다.)
/* $sub_menu_left = 0; // 2단계 메뉴 왼쪽 좌표 (1단계 좌표에서 부터) */
?>
<div id="shopping-category">
<table width="188" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td valign=top>
	<table width="188" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td><img src="<?=$g4['path']?>/images/main/main_category_top.gif" width="188" height="5"></td>
    </tr>
    <tr>
        <td height="35" align="center" background="<?=$g4['path']?>/images/main/main_category_bg.gif"><img src="<?=$g4['path']?>/images/main/main_category_title.gif" width="174" height="21"></td>
    </tr>
    <tr>
        <td background="<?=$g4['path']?>/images/main/main_category_bg.gif" valign=top>
		<table width="170" border="0" align="center" cellpadding="0" cellspacing="0">

<?
// 1단계 분류 판매가능한것만
$hsql = " select ca_id, ca_name from $g4[yc4_category_table]
          where length(ca_id) = '2'
            and ca_use = '1' ";

if(!$is_admin)
	$hsql .= " and ca_id RegExp ('^ev')=0 ";

// 김선용 200805 : 분류 출력순서 임의 지정
//$hsql .= " order by ca_id ";

$hsql .= " order by ca_order_print, ca_id ";
$hresult = sql_query($hsql);
$hnum = @mysql_num_rows($hresult);
for ($i=0; $row=sql_fetch_array($hresult); $i++)
{
    // 2단계 분류
    $menubody = "";
    $onmouseover = "";
    $onmouseout  = "";
    $sql2 = " select ca_id, ca_name from $g4[yc4_category_table]
               where LENGTH(ca_id) = '4'
                 and SUBSTRING(ca_id,1,2) = '$row[ca_id]'
                 and ca_use = '1'
               order by ca_id ";
    $result2 = sql_query($sql2);
    $hnum2 = @mysql_num_rows($hresult);
    for ($j=0; $row2=sql_fetch_array($result2); $j++) 
    {
        $menubody .= "<tr height='25'><td>&nbsp;&nbsp;· <a href='$g4[shop_path]/list.php?ca_id=$row2[ca_id]'>$row2[ca_name]</a></td></tr>";
        // 맨밑줄은 출력하지 않음
        if ($j < $hnum2)
            $menubody .= "<tr><td align=center>
            </td></tr>";
    }

    if ($menubody) 
    {
        $onmouseover = " layer_view('lmenu{$i}', 'lmenu_layer{$i}', 'view', 0 , 0); ";
        $onmouseout  = " layer_view('lmenu{$i}', 'lmenu_layer{$i}', 'hide'); ";
    }

    $category_link = "<a href='$g4[shop_path]/list.php?ca_id=$row[ca_id]'>";
	echo "<tr id='lmenu{$i}' onmouseover=\"$onmouseover\" onmouseout=\"$onmouseout\">";
	echo "<td height=22><img src='{$g4['path']}/images/main/main_category_icon.gif' width=3 height=7 hspace=4>{$category_link}{$row['ca_name']}</a>\n";

	//echo "<tr id='lmenu{$i}' onmouseover=\"$onmouseover\" onmouseout=\"$onmouseout\">";
    //echo "<td height='22'>&nbsp;&nbsp;· $category_link$row[ca_name]</a>\n";

    if ($menubody) 
    {
        //echo "<div id='lmenu_layer{$i}' style='width:180px; display:none; position:absolute; FILTER: alpha(opacity=95); z-index:999;'>";
        echo "<div id='lmenu_layer{$i}' style='width:188px; display:none; position:absolute; z-index:9999;'>";
        echo "<table cellpadding=1 cellspacing=0 bgcolor=#fa5500 width=100%><tr><td>";
        echo "<table border=0 width=100% bgcolor=#FFFFFF cellpadding=0 cellspacing=0>$menubody</table>";
        echo "</td></tr></table>";
        echo "</div>";
    }

    echo "</td></tr>\n";

    if ($i<$hnum-1) // 맨밑줄은 출력하지 않음
        //echo "<tr><td align=center><img src='$g4[shop_img_path]/dot_line.gif'></td></tr>\n";
		echo "<tr><td height=1 bgcolor='#eaeaea'></td></tr>\n";
}

if ($i==0)
    echo "<tr><td height=50 align=center>등록된 자료가 없습니다.</td></tr>\n";
?>
</table>
</td>
      </tr>
      <tr>
        <td><img src="<?=$g4['path']?>/images/main/main_category_bottom.gif" width="188" height="16"></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td style="padding-top:10px"><table width="188" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td align="right"><a href="<?=$g4['bbs_path']?>/board.php?bo_table=qa"><img src="<?=$g4['path']?>/images/main/main_customer_01.gif" width="181" height="81"></td>
      </tr>
      <tr>
        <td align="right"><a href="<?=$g4['bbs_path']?>/board.php?bo_table=faq"><img src="<?=$g4['path']?>/images/main/main_customer_02.gif" width="181" height="36" border="0"></a></td>
      </tr>
      <tr>
        <td align="right"><img src="<?=$g4['path']?>/images/main/main_customer_03.gif" width="181" height="227"></td>
      </tr>
    </table></td>
  </tr>
  <!--
  <tr>
    <td><table width="181" border="0" align="right" cellpadding="0" cellspacing="0">
      <tr>
        <td><img src="<?=$g4['path']?>/images/main/main_special_top.gif" width="181" height="26"></td>
      </tr>
      <tr>
        <td align="center" background="<?=$g4['path']?>/images/main/main_special_bg.gif"><a href="#"><img src="<?=$g4['path']?>/images/main/main_special_img01.gif" width="155" height="139" border="0"></a></td>
      </tr>
      <tr>
        <td height="20" background="<?=$g4['path']?>/images/main/main_special_bg.gif"><img src="<?=$g4['path']?>/images/main/main_special_line.gif" width="181" height="1"></td>
      </tr>
      <tr>
        <td align="center" background="<?=$g4['path']?>/images/main/main_special_bg.gif"><a href="#"><img src="<?=$g4['path']?>/images/main/main_special_img02.gif" width="155" height="141" border="0"></a></td>
      </tr>
      <tr>
        <td height="20" background="<?=$g4['path']?>/images/main/main_special_bg.gif"><img src="<?=$g4['path']?>/images/main/main_special_line.gif" width="181" height="1"></td>
      </tr>
      <tr>
        <td align="center" background="<?=$g4['path']?>/images/main/main_special_bg.gif"><a href="#"><img src="<?=$g4['path']?>/images/main/main_special_img03.gif" width="155" height="141" border="0"></a></td>
      </tr>
      <tr>
        <td><img src="<?=$g4['path']?>/images/main/main_special_bottom.gif" width="181" height="30"></td>
      </tr>
    </table></td>
  </tr>
  -->
</table>
</div>

<?=$menu?>

<script language="JavaScript">
var save_layer = null;

function layer_view(link_id, menu_id, opt, x, y)
{
    var link = document.getElementById(link_id);
    var menu = document.getElementById(menu_id);

    //for (i in link) { document.write(i + '<br/>'); } return;

    if (save_layer != null)
        save_layer.style.display = "none";

    if (opt == 'hide')
    {
        menu.style.display = 'none';
    }
    else
    {
    
   var windowWidth;
   if (typeof window.innerWidth != 'undefined') {
            windowWidth = window.innerWidth;
   } else if (typeof document.documentElement != 'undefined'
            && typeof document.documentElement.clientWidth !=
            'undefined' && document.documentElement.clientWidth != 0) {
            windowWidth = document.documentElement.clientWidth;
   } else {
            windowWidth = document.getElementsByTagName('body')[0].clientWidth;
   }

 

/* alert(document.body.clientWidth); */
        x = parseInt(x);
        y = parseInt(y);
        /* menu.style.left = get_left_pos(link) + x; */
        menu.style.left = (138);
        
/* 		menu.style.left = (document.body.clientWidth-menu.style.width)/2; */
/* 		menu.style.left = (windowWidth - menu.style.width)/2 + 188; */
        menu.style.top  = get_top_pos(link) + link.offsetHeight + y - 22 ;
        menu.style.display = 'block';
/*
alert(menu.style.left);
alert(windowWidth);
*/

    }

    save_layer = menu;
}
</script>


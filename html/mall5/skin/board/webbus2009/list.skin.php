<?
if (!defined("_GNUBOARD_")) exit; // ���� ������ ���� �Ұ� 

// ���ÿɼ����� ���� ����ġ�Ⱑ ���������� ����
$colspan = 5;

//if ($is_category) $colspan++;
if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

// ������ ���ٷ� ǥ�õǴ� ��� �� �ڵ带 ����� ������.
// <nobr style='display:block; overflow:hidden; width:000px;'>����</nobr>
?>
<script language="JavaScript">
// �˻�â��ġ��
function togglelist(){
  if(document.getElementById('hidden1').style.display==""){
    document.getElementById('hidden1').style.display="none";
  }else{
    document.getElementById('hidden1').style.display="";  
  }
}
</script>

<!-- �Խ��� ��� ���� -->
<link href="css/webbus01.css" rel="stylesheet" type="text/css" />
<!-- ���� -->
<form name="fboardlist" method="post" style="margin:0;">
<input type='hidden' name='bo_table' value='<?=$bo_table?>'>
<input type='hidden' name='sfl'  value='<?=$sfl?>'>
<input type='hidden' name='stx'  value='<?=$stx?>'>
<input type='hidden' name='spt'  value='<?=$spt?>'>
<input type='hidden' name='page' value='<?=$page?>'>
<input type='hidden' name='sw'   value=''>

<div style="border-top:1px solid #ddd; border-bottom:1px solid #ddd; border-left:1px solid #ddd;border-right:1px solid #ddd;height:30px; background:url(<?=$board_skin_path?>/img/title_bg.gif) repeat-x;">
<table width=100% border=0 cellpadding=0 cellspacing=0 style="font-weight:bold; color:#505050;">
<tr height=28 align=center>
    <td width=50 height="28" class="gray8s"><img src="<?=$board_skin_path?>/img/w_1.gif" align="absmiddle" border="0"></td>
    <? if ($is_checkbox) { ?><td width=30><input onclick="if (this.checked) all_checked(true); else all_checked(false);" type=checkbox></td>
    <?}?>
    <td><img src="<?=$board_skin_path?>/img/w_2.gif" align="absmiddle" border="0"></td>
    <td width=90 class="gray8s"><img src="<?=$board_skin_path?>/img/w_3.gif" align="absmiddle" border="0"></td>
    <?/**/?><td width=65><?=subject_sort_link('wr_datetime', $qstr2, 1)?>
      <img src="<?=$board_skin_path?>/img/w_4.gif" align="absmiddle" border="0"></a></td>
    <td width=60><?=subject_sort_link('wr_hit', $qstr2, 1)?>
      <img src="<?=$board_skin_path?>/img/w_5.gif" align="absmiddle" border="0"></td>
    <?/**/?>
    <!--<td width=40>��¥</td>
    <td width=50>��ȸ</td>-->
    <?/*?><td width=40 title='������ �ڸ�Ʈ �� �ð�'><?=subject_sort_link('wr_last', $qstr2, 1)?><font color="5d5e5b" class="smailfont1">�ֱ�</font></a></td><?*/?>
    <? if ($is_good) { ?><td width=35><?=subject_sort_link('wr_good', $qstr2, 1)?>
      <img src="<?=$board_skin_path?>/img/w_6.gif" align="absmiddle" border="0"></td>
    <?}?>
	<!--����õ �����
    <? if ($is_nogood) { ?><td width=40><?=subject_sort_link('wr_nogood', $qstr2, 1)?>
      <span class="gray8s">����õ</span></a></td>
    <?}?>
	-->
</tr>
</table>
</div>
<div style="height:3px; background:url(<?=$board_skin_path?>/img/title_shadow.gif) repeat-x; line-height:1px; font-size:1px;"></div>

<table width=100% border=0 cellpadding=0 cellspacing=0>
<!-- ��� -->
<? for ($i=0; $i<count($list); $i++) { ?> 

<tr height=29 align=center> 
    <tr height=28 align=center <?  if ($list[$i][is_notice])  { echo " bgcolor=fafafa "; } ?>> 
<td width="30">
        <? 
        if ($list[$i][is_notice]) // �������� 
            echo "<img src=\"$board_skin_path/img/icon_notice.gif\">";
        else if ($wr_id == $list[$i][wr_id]) // ������ġ
            echo "<span style='font:bold 9px tahoma; color:#E15916;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$list[$i][num]}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
        else
            echo "<span style='font:normal 9px tahoma; color:#B3B3B3;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$list[$i][num]}&nbsp;&nbsp;&nbsp;&nbsp;</span>";
        ?></td>
    <? if ($is_checkbox) { ?><td width=30><input type=checkbox name=chk_wr_id[] value="<?=$list[$i][wr_id]?>"></td><? } ?>
    <td height="23" align=left style='word-break:break-all;'>
        <? 
        echo $nobr_begin;
        echo $list[$i][reply];
        echo $list[$i][icon_reply];
     //   if ($is_category && $list[$i][ca_name]) { 
       //     echo "<a href='{$list[$i][ca_name_href]}'><span class='gal2s'>[{$list[$i][ca_name]}]</span></a> ";
       // }
        $style = "";
        if ($list[$i][is_notice]) $style = " style='font-weight: bold;'";

        echo "<font class='12font'><a href='{$list[$i][href]}'>";
        echo $list[$i][subject];
        echo "</a></font>";

        if ($list[$i][comment_cnt]) 
            echo " <a href=\"{$list[$i][comment_href]}\"><span style='font-family:Tahoma;font-size:10px;color:#EE5A00;'><b>{$list[$i][comment_cnt]}</b></span></a>";

        // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
        // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }
		//�ڸ�Ʈ24�ð��̳� ��Ͻ�new������ǥ��
        if($list[$i][icon_new]) 
          echo " " . $list[$i][icon_new]; 
        else { 
        $temp = sql_fetch("select wr_datetime from `$write_table` where wr_num='{$list[$i][wr_num]}' and wr_is_comment='1' ORDER BY wr_id DESC " ); 
        if($temp[wr_datetime]){ 
           $co_time = strtotime($temp[wr_datetime]); 
        if(time() - $co_time < 24*60*60) //24�ð� �̳��� �� 
           echo " <img src='$board_skin_path/img/ico_n.gif' align=absmiddle title='���ο� �ڸ�Ʈ���'>"; 
           } 
         }
		 //�ڸ�Ʈ24�ð��̳� ��Ͻ�new������ǥ�� ��


        //echo " " . $list[$i][icon_new];
        echo " " . $list[$i][icon_file];
        echo " " . $list[$i][icon_link];
        echo " " . $list[$i][icon_hot];
        echo " " . $list[$i][icon_secret];
        echo $nobr_end;
        ?>    </td>
    <td align=center width=90><nobr style='display:block; overflow:hidden; width:90px;'><font color="5d5e5b" class="smailfont1"><?=$list[$i][name]?></span></nobr></td>
    <td width=65 class="gray8s"><span style='font:normal 9px tahoma; color:#888888;'><?=$list[$i][datetime]?></span></td>
    <td width=60 class="gray8s"><span style='font:normal 9px tahoma; color:#666666;'><strong><?=$list[$i][wr_hit]?></strong></span></td>
    <?/*?><td width=40><span style='font:normal 11px tahoma; color:#BABABA;'><?=$list[$i][last2]?></span></td><?*/?>
    <? if ($is_good) { ?><td width=35 align="center" class="gray8s"><span class='oranges'><strong><?=$list[$i][wr_good]?></strong></span></td>
    <? } ?>
	<!--����õ �����
    <? if ($is_nogood) { ?><td width=40 align="center" class="gray8s"><span style='font:normal 11px tahoma; color:#BABABA;'><?=$list[$i][wr_nogood]?></span></td>
    <? } ?>
	-->
</tr>
<tr><td colspan=<?=$colspan?> height=1 bgcolor=#eeeeee></td></tr>
<?}?>
<? if (count($list) == 0) { echo "<tr><td colspan='$colspan' height=100 align=center>�Խù��� �����ϴ�.</td></tr>"; } ?>
</table>
</form>


<div style="clear:both; margin-top:7px; height:31px;">
    <div style="float:left;">
    <? if ($list_href) { ?>
    <a href="<?=$list_href?>"><img src="<?=$board_skin_path?>/img/btn_list.gif" align="absmiddle" border="0"></a>
    <? } ?>
    <? if ($is_checkbox) { ?>
    <a href="javascript:select_delete();"><img src="<?=$board_skin_path?>/img/btn_select_delete.gif" align="absmiddle" border="0"></a>
    <a href="javascript:select_copy('copy');"><img src="<?=$board_skin_path?>/img/btn_select_copy.gif" align="absmiddle" border="0"></a>
    <a href="javascript:select_copy('move');"><img src="<?=$board_skin_path?>/img/btn_select_move.gif" align="absmiddle" border="0"></a>
    <? } ?>
    </div>

    <div style="float:right;">
    <? if ($write_href) { ?><a href="<?=$write_href?>"><img src="<?=$board_skin_path?>/img/btn_write.gif" border="0"></a><? } ?>
    </div>
</div>

<!-- �Ʒ����� �����
<div style="height:1px; line-height:1px; font-size:1px; background-color:#eee; clear:both;">&nbsp;</div>
<div style="height:1px; line-height:1px; font-size:1px; background-color:#ddd; clear:both;">&nbsp;</div>
-->

<!-- ������ -->
<div style="text-align:center; line-height:30px; clear:both; margin:5px 0 5px 0; padding:5px 0; font-family:gulim;">
    <? if ($prev_part_href) { echo "<a href='$prev_part_href'><img src='$board_skin_path/img/page_search_prev.gif' border=0 align=absmiddle title='�����˻�'></a>"; } ?>
    <?
    // �⺻���� �Ѿ���� �������� �Ʒ��� ���� ��ȯ�Ͽ� �̹����ε� ����� �� �ֽ��ϴ�.
    //echo $write_pages;
    $write_pages = str_replace("ó��", "<img src='$board_skin_path/img/page_begin.gif' border='0' align='absmiddle' title='ó��'>", $write_pages);
    $write_pages = str_replace("����", "<img src='$board_skin_path/img/page_prev.gif' border='0' align='absmiddle' title='����'>", $write_pages);
    $write_pages = str_replace("����", "<img src='$board_skin_path/img/page_next.gif' border='0' align='absmiddle' title='����'>", $write_pages);
    $write_pages = str_replace("�ǳ�", "<img src='$board_skin_path/img/page_end.gif' border='0' align='absmiddle' title='�ǳ�'>", $write_pages);
    $write_pages = preg_replace("/<span>([0-9]*)<\/span>/", "<b><span style=\"color:#B3B3B3; font-size:12px;\">$1</span></b>", $write_pages);
    $write_pages = preg_replace("/<b>([0-9]*)<\/b>/", "<b><span style=\"color:#4D6185; font-size:12px; text-decoration:underline;\">$1</span></b>", $write_pages);
    ?>
    <?=$write_pages?>
    <? if ($next_part_href) { echo "<a href='$next_part_href'><img src='$board_skin_path/img/page_search_next.gif' border=0 align=absmiddle title='�����˻�'></a>"; } ?>

</div>
<!-- ��ũ ��ư, �˻� -->
<div style="text-align:center;">
<form name=fsearch method=get style="margin:0px;">
          <table width="100%" border="0" cellpadding="5" cellspacing="1">
            <tr>
              
          <td align="center" bgcolor="fafafa"> 
            <input type=hidden name=bo_table value="<?=$bo_table?>">
<input type=hidden name=sca      value="<?=$sca?>">
<select name=sfl style="background-color:#ffffff; border:1px solid #D9D9D9; height:18px;">
    <option value='wr_subject'>����</option>
    <option value='wr_content'>����</option>
    <option value='wr_subject||wr_content'>����+����</option>
    <option value='mb_id,1'>ȸ�����̵�</option>
    <option value='mb_id,0'>ȸ�����̵�(��)</option>
    <option value='wr_name,1'>�۾���</option>
    <option value='wr_name,0'>�۾���(��)</option>
</select>
<input name=stx maxlength=15 itemname="�˻���" required value='<?=$stx?>' style="width:150px; background-color:#ffffff; border:1px solid #D9D9D9; height:20px;">
<input type=image src="<?=$board_skin_path?>/img/btn_search.gif" border=0 align=absmiddle>
      </form></td>
            </tr>
          </table>
         
</div>

<? if ($is_checkbox) { ?>
<script language="JavaScript">
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function check_confirm(str) {
    var f = document.fboardlist;
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(str + "�� �Խù��� �ϳ� �̻� �����ϼ���.");
        return false;
    }
    return true;
}

// ������ �Խù� ����
function select_delete() {
    var f = document.fboardlist;

    str = "����";
    if (!check_confirm(str))
        return;

    if (!confirm("������ �Խù��� ���� "+str+" �Ͻðڽ��ϱ�?\n\n�ѹ� "+str+"�� �ڷ�� ������ �� �����ϴ�"))
        return;

    f.action = "./delete_all.php";
    f.submit();
}

// ������ �Խù� ���� �� �̵�
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == "copy")
        str = "����";
    else
        str = "�̵�";
                       
    if (!check_confirm(str))
        return;

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}
</script>
<? } ?>
<!-- �Խ��� ��� �� -->
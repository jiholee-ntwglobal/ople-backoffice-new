<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

//if ($is_category) $colspan++;
if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

// 제목이 두줄로 표시되는 경우 이 코드를 사용해 보세요.
// <nobr style='display:block; overflow:hidden; width:000px;'>제목</nobr>
?>
<script language="JavaScript">
// 검색창펼치기
function togglelist(){
  if(document.getElementById('hidden1').style.display==""){
    document.getElementById('hidden1').style.display="none";
  }else{
    document.getElementById('hidden1').style.display="";  
  }
}
</script>

<!-- 게시판 목록 시작 -->
<link href="css/webbus01.css" rel="stylesheet" type="text/css" />
<!-- 제목 -->
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
    <!--<td width=40>날짜</td>
    <td width=50>조회</td>-->
    <?/*?><td width=40 title='마지막 코멘트 쓴 시간'><?=subject_sort_link('wr_last', $qstr2, 1)?><font color="5d5e5b" class="smailfont1">최근</font></a></td><?*/?>
    <? if ($is_good) { ?><td width=35><?=subject_sort_link('wr_good', $qstr2, 1)?>
      <img src="<?=$board_skin_path?>/img/w_6.gif" align="absmiddle" border="0"></td>
    <?}?>
	<!--비추천 숨기기
    <? if ($is_nogood) { ?><td width=40><?=subject_sort_link('wr_nogood', $qstr2, 1)?>
      <span class="gray8s">비추천</span></a></td>
    <?}?>
	-->
</tr>
</table>
</div>
<div style="height:3px; background:url(<?=$board_skin_path?>/img/title_shadow.gif) repeat-x; line-height:1px; font-size:1px;"></div>

<table width=100% border=0 cellpadding=0 cellspacing=0>
<!-- 목록 -->
<? for ($i=0; $i<count($list); $i++) { ?> 

<tr height=29 align=center> 
    <tr height=28 align=center <?  if ($list[$i][is_notice])  { echo " bgcolor=fafafa "; } ?>> 
<td width="30">
        <? 
        if ($list[$i][is_notice]) // 공지사항 
            echo "<img src=\"$board_skin_path/img/icon_notice.gif\">";
        else if ($wr_id == $list[$i][wr_id]) // 현재위치
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
		//코멘트24시간이내 등록시new아이콘표시
        if($list[$i][icon_new]) 
          echo " " . $list[$i][icon_new]; 
        else { 
        $temp = sql_fetch("select wr_datetime from `$write_table` where wr_num='{$list[$i][wr_num]}' and wr_is_comment='1' ORDER BY wr_id DESC " ); 
        if($temp[wr_datetime]){ 
           $co_time = strtotime($temp[wr_datetime]); 
        if(time() - $co_time < 24*60*60) //24시간 이내일 때 
           echo " <img src='$board_skin_path/img/ico_n.gif' align=absmiddle title='새로운 코멘트등록'>"; 
           } 
         }
		 //코멘트24시간이내 등록시new아이콘표시 끝


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
	<!--비추천 숨기기
    <? if ($is_nogood) { ?><td width=40 align="center" class="gray8s"><span style='font:normal 11px tahoma; color:#BABABA;'><?=$list[$i][wr_nogood]?></span></td>
    <? } ?>
	-->
</tr>
<tr><td colspan=<?=$colspan?> height=1 bgcolor=#eeeeee></td></tr>
<?}?>
<? if (count($list) == 0) { echo "<tr><td colspan='$colspan' height=100 align=center>게시물이 없습니다.</td></tr>"; } ?>
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

<!-- 아래라인 숨기기
<div style="height:1px; line-height:1px; font-size:1px; background-color:#eee; clear:both;">&nbsp;</div>
<div style="height:1px; line-height:1px; font-size:1px; background-color:#ddd; clear:both;">&nbsp;</div>
-->

<!-- 페이지 -->
<div style="text-align:center; line-height:30px; clear:both; margin:5px 0 5px 0; padding:5px 0; font-family:gulim;">
    <? if ($prev_part_href) { echo "<a href='$prev_part_href'><img src='$board_skin_path/img/page_search_prev.gif' border=0 align=absmiddle title='이전검색'></a>"; } ?>
    <?
    // 기본으로 넘어오는 페이지를 아래와 같이 변환하여 이미지로도 출력할 수 있습니다.
    //echo $write_pages;
    $write_pages = str_replace("처음", "<img src='$board_skin_path/img/page_begin.gif' border='0' align='absmiddle' title='처음'>", $write_pages);
    $write_pages = str_replace("이전", "<img src='$board_skin_path/img/page_prev.gif' border='0' align='absmiddle' title='이전'>", $write_pages);
    $write_pages = str_replace("다음", "<img src='$board_skin_path/img/page_next.gif' border='0' align='absmiddle' title='다음'>", $write_pages);
    $write_pages = str_replace("맨끝", "<img src='$board_skin_path/img/page_end.gif' border='0' align='absmiddle' title='맨끝'>", $write_pages);
    $write_pages = preg_replace("/<span>([0-9]*)<\/span>/", "<b><span style=\"color:#B3B3B3; font-size:12px;\">$1</span></b>", $write_pages);
    $write_pages = preg_replace("/<b>([0-9]*)<\/b>/", "<b><span style=\"color:#4D6185; font-size:12px; text-decoration:underline;\">$1</span></b>", $write_pages);
    ?>
    <?=$write_pages?>
    <? if ($next_part_href) { echo "<a href='$next_part_href'><img src='$board_skin_path/img/page_search_next.gif' border=0 align=absmiddle title='다음검색'></a>"; } ?>

</div>
<!-- 링크 버튼, 검색 -->
<div style="text-align:center;">
<form name=fsearch method=get style="margin:0px;">
          <table width="100%" border="0" cellpadding="5" cellspacing="1">
            <tr>
              
          <td align="center" bgcolor="fafafa"> 
            <input type=hidden name=bo_table value="<?=$bo_table?>">
<input type=hidden name=sca      value="<?=$sca?>">
<select name=sfl style="background-color:#ffffff; border:1px solid #D9D9D9; height:18px;">
    <option value='wr_subject'>제목</option>
    <option value='wr_content'>내용</option>
    <option value='wr_subject||wr_content'>제목+내용</option>
    <option value='mb_id,1'>회원아이디</option>
    <option value='mb_id,0'>회원아이디(코)</option>
    <option value='wr_name,1'>글쓴이</option>
    <option value='wr_name,0'>글쓴이(코)</option>
</select>
<input name=stx maxlength=15 itemname="검색어" required value='<?=$stx?>' style="width:150px; background-color:#ffffff; border:1px solid #D9D9D9; height:20px;">
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
        alert(str + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }
    return true;
}

// 선택한 게시물 삭제
function select_delete() {
    var f = document.fboardlist;

    str = "삭제";
    if (!check_confirm(str))
        return;

    if (!confirm("선택한 게시물을 정말 "+str+" 하시겠습니까?\n\n한번 "+str+"한 자료는 복구할 수 없습니다"))
        return;

    f.action = "./delete_all.php";
    f.submit();
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == "copy")
        str = "복사";
    else
        str = "이동";
                       
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
<!-- 게시판 목록 끝 -->
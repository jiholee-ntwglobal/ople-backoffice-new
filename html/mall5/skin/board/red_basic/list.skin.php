<link rel="stylesheet" type="text/css" href="<?=$board_skin_path?>/css/style.css" charset="UTF-8" media="all" />

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

<!-- 게시판 목록 시작 -->
<div>
    <!-- 분류 셀렉트 박스, 게시물 몇건, 관리자화면 링크 -->
	<div class="bbslist_top">
    	<div id="category">
        <form name="fcategory" method="get" style="margin:0px;">
        <? if ($is_category) { ?>
        <select name=sca onchange="location='<?=$category_location?>'+<?=strtolower($g4[charset])=='utf-8' ? "encodeURIComponent(this.value)" : "this.value"?>;">
        <option value=''>전체</option>
        <?=$category_option?>
        </select>
        <? } ?>
        </form>
        </div>
        <div id="admin">
        <img src="<?=$board_skin_path?>/img/icon_total.gif">
        <span class="B">총게시글 <?=number_format($total_count)?></span> &nbsp;&nbsp;&nbsp;
        <? if ($rss_href) { ?><a href='<?=$rss_href?>'><img src="<?=$board_skin_path?>/img/btn_rss.gif"></a><?}?>
        <? if ($admin_href) { ?><a href="<?=$admin_href?>"><img src="<?=$board_skin_path?>/img/btn_admin.gif" title="관리자"></a><?}?>
        </div>
    </div>
    <div class="bbslisttable">
    <!-- 제목 -->
    <form name="fboardlist" method="post">
    <input type='hidden' name='bo_table' value='<?=$bo_table?>'>
    <input type='hidden' name='sfl'  value='<?=$sfl?>'>
    <input type='hidden' name='stx'  value='<?=$stx?>'>
    <input type='hidden' name='spt'  value='<?=$spt?>'>
    <input type='hidden' name='page' value='<?=$page?>'>
    <input type='hidden' name='sw'   value=''>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<colgroup>
    <col width="50" />
    <? if ($is_checkbox) { ?><col width="40" /><? } ?>
    <col />
    <col width="110" />
    <col width="90" />
    <col width="50" />
    <? if ($is_good) { ?><col width="40" /><? } ?>
    <? if ($is_nogood) { ?><col width="60" /><? } ?>
</colgroup>
  <thead>
  	<tr>
    	<th>번호</th>
    	<? if ($is_checkbox) { ?><th><input onclick="if (this.checked) all_checked(true); else all_checked(false);" type="checkbox"></th><?}?>
    	<th>제&nbsp;&nbsp;&nbsp;목</th>
    	<th>글쓴이</th>
    	<th><?=subject_sort_link('wr_datetime', $qstr2, 1)?>날짜</a></th>
    	<th><?=subject_sort_link('wr_hit', $qstr2, 1)?>조회</a></th>
    	<? if ($is_good) { ?><th><?=subject_sort_link('wr_good', $qstr2, 1)?>추천</a></th><?}?>
    	<? if ($is_nogood) { ?><th><?=subject_sort_link('wr_nogood', $qstr2, 1)?>비추천</a></th><?}?>
  	</tr>
  </thead>
   <? 
    for ($i=0; $i<count($list); $i++) { 
        $bg = $i%2 ? 0 : 1;
    ?>
  <tbody>
    <tr> 
        <td class="num">
            <? 
            if ($list[$i][is_notice]) // 공지사항 
                echo "<b>공지</b>";
            else if ($wr_id == $list[$i][wr_id]) // 현재위치
                echo "<span class='current'>{$list[$i][num]}</span>";
            else
                echo $list[$i][num];
            ?>
        </td>
        <? if ($is_checkbox) { ?><td class="checkbox"><input type=checkbox name=chk_wr_id[] value="<?=$list[$i][wr_id]?>"></td><? } ?>
        <td class="subject">
            <? 
            echo $nobr_begin;
            echo $list[$i][reply];
            echo $list[$i][icon_reply];
            if ($is_category && $list[$i][ca_name]) { 
                echo "<span class=small><font color=gray>[<a href='{$list[$i][ca_name_href]}'>{$list[$i][ca_name]}</a>]</font></span> ";
            }

            if ($list[$i][is_notice])
                echo "<a href='{$list[$i][href]}'><span class='notice'>{$list[$i][subject]}</span></a>";
            else
                echo "<a href='{$list[$i][href]}'>{$list[$i][subject]}</a>";

            if ($list[$i][comment_cnt]) 
                echo " <a href=\"{$list[$i][comment_href]}\"><span class='comment'>{$list[$i][comment_cnt]}</span></a>";

            // if ($list[$i]['link']['count']) { echo "[{$list[$i]['link']['count']}]"; }
            // if ($list[$i]['file']['count']) { echo "<{$list[$i]['file']['count']}>"; }

            echo " " . $list[$i][icon_new];
            echo " " . $list[$i][icon_file];
            echo " " . $list[$i][icon_link];
            echo " " . $list[$i][icon_hot];
            echo " " . $list[$i][icon_secret];
            echo $nobr_end;
            ?>
         </td>
         <td class="name"><?=$list[$i][name]?></td>
         <td class="datetime"><?=$list[$i][datetime2]?></td>
         <td class="hit"><?=$list[$i][wr_hit]?></td>
        <? if ($is_good) { ?><td class="good"><?=$list[$i][wr_good]?></td><? } ?>
        <? if ($is_nogood) { ?><td class="nogood"><?=$list[$i][wr_nogood]?></td><? } ?>
  	</tr>
    <? } // end for ?>
    <? if (count($list) == 0) { echo "<tr><td colspan='$colspan' height=100 align=center>게시물이 없습니다.</td></tr>"; } ?>
  </tbody>
</table>
</form>
          </div>
          <div class="bbs_bt bbs_btn">
          	<div class="bbs_left_btn">
            <? if ($list_href) { ?>
        	<a class="other" style="height:22px;" href="<?=$list_href?>">목록보기</a>
        	<? } ?>
        	<? if ($is_checkbox) { ?>
        	<a class="admin" style="height:22px;" href="javascript:select_delete();">선택삭제</a>
        	<a class="admin" style="height:22px;" href="javascript:select_copy('copy');">선택복사</a>
        	<a class="admin" style="height:22px;" href="javascript:select_copy('move');">선택이동</a>
        	<? } ?>
            </div>
          	<div class="bbs_right_btn">
            <? if ($write_href) { ?><a style="height:22px;" href="<?=$write_href?>">글쓰기</a><? } ?>
            </div>
          </div>
          <!-- 페이지 -->
          <div class="bbs_bt"><? if ($prev_part_href) { echo "<a class='other' href='$prev_part_href'>이전검색</a>"; } ?>
          <?
          // 기본으로 넘어오는 페이지를 아래와 같이 변환하여 이미지로도 출력할 수 있습니다.
          //echo $write_pages;
          $write_pages = str_replace("처음", "<img src='$board_skin_path/img/btn_pre_end.gif' border='0' title='처음'>", $write_pages);
          $write_pages = str_replace("이전", "<img src='$board_skin_path/img/btn_pre.gif' border='0' title='이전'>", $write_pages);
          $write_pages = str_replace("다음", "<img src='$board_skin_path/img/btn_next.gif' border='0' title='다음'>", $write_pages);
          $write_pages = str_replace("맨끝", "<img src='$board_skin_path/img/btn_next_end.gif' border='0'  title='맨끝'>", $write_pages);
          //$write_pages = preg_replace("/<span>([0-9]*)<\/span>/", "$1", $write_pages);
          $write_pages = preg_replace("/<b>([0-9]*)<\/b>/", "<b><span style=\"color:#4D6185; font-size:12px; text-decoration:underline;\">$1</span></b>", $write_pages);
          ?>
          <?=$write_pages?>
          <? if ($next_part_href) { echo "<a class='other' href='$next_part_href'>다음검색</a>"; } ?>
        </div>
        <!-- 검색 -->
        <div id="search">
            <form name="fsearch" method="get">
				<input type="hidden" name="bo_table" value="<?=$bo_table?>" />
				<input type="hidden" name="sca"	  value="<?=$sca?>" />
				<select class="sfl textarea_mem" name="sfl">
					<option value="wr_subject">제목</option>
					<option value="wr_content">내용</option>
					<option value="wr_subject||wr_content">제목+내용</option>
					<option value="mb_id,1">회원아이디</option>
					<option value="mb_id,0">회원아이디(코)</option>
					<option value="wr_name,1">글쓴이</option>
					<option value="wr_name,0">글쓴이(코)</option>
				</select>
				<select class="sop textarea_mem" name="sop">
					<option value="and">and</option>
					<option value="or">or</option>
				</select>
				<input class="stx textarea_mem" name="stx" itemname="검색어" required value="<?=stripslashes($stx)?>" />
				<input type="image" src="<?=$board_skin_path?>/img/btn_search.gif" />
			</form>
      </div>
     <!-- 검색 끝 -->            
</div>
<script type="text/javascript">
//if ('<?=$sca?>') document.fcategory.sca.value = '<?=$sca?>';
if ('<?=$stx?>') {
	document.fsearch.sfl.value = '<?=$sfl?>';

	if ('<?=$sop?>' == 'and') 
		document.fsearch.sop[0].selected = true;

	if ('<?=$sop?>' == 'or')
		document.fsearch.sop[1].selected = true;
} else {
	document.fsearch.sop[0].selected = true;
}
</script>

<? if ($is_checkbox) { ?>
<script type="text/javascript">
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
<!-- 게시판 끝 -->
<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

?>
<div class='sub_title'>
	<span><strong>1:1문의</strong></span>
	<a href="<?=$g4['bbs_path']?>/board_personnel_qa.php" title="1:1문의"><img src='<?=$latest_skin_path?>/img/icon_more.gif' align=right></a>
</div>

<table width=100% cellpadding=0 cellspacing=0 style="border-top:solid 1px #e4e4e4;">
<? for ($i=0; $i<count($list); $i++) {?>
<tr onmouseover="this.style.backgroundColor='#f7f7f7'" onmouseout="this.style.backgroundColor=''">
	<td style="padding:5px 0 8px 0;"><img src='<?=$latest_skin_path?>/img/latest_icon.gif' align=absmiddle>&nbsp;&nbsp; 
	<?
	echo $list[$i]['icon_reply'] . " ";
	//echo "<a href='{$list[$i]['href']}' title='".stripslashes(get_text($list[$i]['wr_subject']))."'>";
	echo "<span onclick=\"personnel_content_view('personnel_content_{$i}');\" style='cursor:pointer;' title='".stripslashes(get_text($list[$i]['subject']))."' style='font-weight:bold;'>{$list[$i]['subject']}</span>";
	//echo "</a>";
	/*
	if ($list[$i]['comment_cnt']) 
		echo " <a href=\"{$list[$i]['comment_href']}\"><span style='font-family:돋움; font-size:8pt; color:#9A9A9A;'>{$list[$i]['comment_cnt']}</span></a>";
	*/
	echo " " . $list[$i]['icon_new'];
	echo " " . $list[$i]['icon_file'];
	echo " " . $list[$i]['icon_link'];
	echo " " . $list[$i]['icon_hot'];
	//echo " " . $list[$i]['icon_secret'];

	$html = 0;
	if (strstr($list[$i]['wr_option'], "html1"))
		$html = 1;
	else if (strstr($list[$i]['wr_option'], "html2"))
		$html = 2;
	$content = conv_content($list[$i]['contents'], $html);
	?>
	<div id="personnel_content_<?=$i?>" style="display:none; line-height:150%; padding:10px 10px 10px 30px;">
		<?
		# 답변글 리스트 로드 2014-09-15 홍민기 #
		$reply_qry = sql_query("select * from yc4_personal_qa where parent_uid = '".$list[$i]['uid']."' and depth = '1'");
		$reply_cnt = mysql_num_rows($reply_qry);
		if($reply_cnt > 0){
			echo "<div class='latest_reply'>";
			while($reply = sql_fetch_array($reply_qry)){
				echo "<div style='font-weight:bold;'>".$reply['subject']." </div>"; // 답변글 타이틀
				echo "<div style='padding-left:10px;'>".conv_content($reply['contents'],$html)."</div>"; // 답변글 내용
			}
			echo "</div><br/><br/><br/><br/>";
		}
		echo "<div class='reply_content'>".($reply_cnt > 0 ? "============원글============<br/><br/>":"")."".$content."</div>"; // 원글 내용
	?>
	</div>
	</td>
	<td width=130 align=right style="padding:5px 10px 8px 0;vertical-align:top;"><?=$list[$i]['create_dt']?></td>
</tr>
<tr><td height=1 colspan=2 style="border-top:1px solid #e8e8e8 "></td></tr>
<? } ?>
<?if(count($list) == 0){?><tr><td colspan=4 align=center height=50><font color=#6A6A6A>1:1문의가 없습니다.</a></td></tr><? } ?>
<tr><td height=1 colspan=2></td></tr>
</table>

<script language="JavaScript">
<!--
var personnel_saveView = null;
var personnel_saveWrite = null;
var personnel_v_count = <?=count($list)?>;

function personnel_content_view(v_id){

	if(v_id == personnel_saveView)
	{
		document.getElementById(v_id).style.display = "none";
		personnel_saveView = null;
	}
	else
	{
		for(var i=0; i<personnel_v_count; i++)
		{
			var oEle_v = document.getElementById('personnel_content_'+i);
			if(document.getElementById(v_id) == oEle_v){
				oEle_v.style.display = "block";
				personnel_saveWrite = v_id;
				personnel_saveView = 'personnel_content_'+i;
			}
			else {
				oEle_v.style.display = "none";
			}
				
		}
	}
}
//-->
</script>
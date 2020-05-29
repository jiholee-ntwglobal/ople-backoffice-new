<link rel="stylesheet" type="text/css" href="<?=$board_skin_path?>/css/style.css" charset="UTF-8" media="all" />
<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($is_dhtml_editor) {
	include_once("$g4[path]/lib/cheditor4.lib.php");
	echo "<script src='$g4[cheditor4_path]/cheditor.js'></script>";
	echo cheditor1('wr_content', '100%', '300px');
}
?>

<script type="text/javascript">
// 글자수 제한
var char_min = parseInt(<?=$write_min?>); // 최소
var char_max = parseInt(<?=$write_max?>); // 최대
</script>

<div class="bbswritetable">
	<form name="fwrite" method="post" onsubmit="return fwrite_submit(this);" enctype="multipart/form-data">
		<input type="hidden" name="null"> 
		<input type="hidden" name="w"	  value="<?=$w?>">
		<input type="hidden" name="bo_table" value="<?=$bo_table?>">
		<input type="hidden" name="wr_id" value="<?=$wr_id?>">
		<input type="hidden" name="sca"	  value="<?=$sca?>">
		<input type="hidden" name="sfl"	  value="<?=$sfl?>">
		<input type="hidden" name="stx"	  value="<?=$stx?>">
		<input type="hidden" name="spt"	  value="<?=$spt?>">
		<input type="hidden" name="sst"	  value="<?=$sst?>">
		<input type="hidden" name="sod"	  value="<?=$sod?>">
		<input type="hidden" name="page"  value="<?=$page?>">
		<table width="100%">
<colgroup>
<col style="width:15%"  />
<col style="width:85%"  />
</colgroup>
			<tbody>
			<? if ($is_name) { ?>
				<tr>
					<th>이름</th>
					<td><input name="wr_name" type="text" required class="textarea_mem" value="<?=$name?>" maxlength="20" itemname="이름" /></td>
				</tr>
			<? } ?>

			<? if ($is_password) { ?>
				<tr>
					<th>패스워드</th>
					<td><input class="textarea_mem" type="password" maxlength="20" name="wr_password" itemname="패스워드" <?=$password_required?> /></td>
				</tr>
			<? } ?>

			<? if ($is_email) { ?>
				<tr>
					<th>이메일</th>
					<td><input class="textarea_mem" type="text" maxlength="100" name="wr_email" email itemname="이메일" value="<?=$email?>" /></td>
				</tr>
			<? } ?>

			<? if ($is_homepage) { ?>
				<tr>
					<th>홈페이지</th>
					<td><input class="textarea_mem" type="text" name="wr_homepage" itemname="홈페이지" value="<?=$homepage?>" /></td>
				</tr>
			<? } ?>

			<? 
				$option = "";
				$option_hidden = "";
				if ($is_notice || $is_html || $is_secret || $is_mail) { 
					$option = "";
					if ($is_notice) { 
						$option .= "<input type='checkbox' id='notice' name='notice' value='1' $notice_checked /><label for='notice'>공지</label>";
					}

					if ($is_html) {
						if ($is_dhtml_editor) {
							$option_hidden .= "<input type='hidden' value='html1' name='html' />";
						} else {
							$option .= "<input onclick='html_auto_br(this);' type='checkbox' value='$html_value' id='html' name='html' $html_checked /><label for='html'>HTML</label>";
						}
					}

					if ($is_secret) {
						if ($is_admin || $is_secret==1) {
							$option .= "<input type='checkbox' value='secret' id='secret' name='secret' $secret_checked /><label for='secret'>비밀글</label>";
						} else {
							$option_hidden .= "<input type='hidden' value='secret' name='secret' />";
						}
					}
					
					if ($is_mail) {
						$option .= "<input type='checkbox' value='mail' id='mail' name='mail' $recv_email_checked /><label for='mail'>답변메일받기</label>";
					}
				}
				echo $option_hidden;
			?>

			<? if ($option) { ?>
				<tr>
					<th>옵션</th>
					<td><?=$option?></td>
				</tr>
			<? } ?>

			<? if ($is_category) { ?>
				<tr>
					<th>분류</th>
					<td><select name="ca_name" required itemname="분류"><option value="">선택하세요<?=$category_option?></select></td>
				</tr>
			<? } ?>

				<tr>
					<th>제목</th>
					<td><input name="wr_subject" type="text" required class="textarea_mem" id="wr_subject" value="<?=$subject?>" size="95%" itemname="제목" /></td>
				<tr>

				<tr>
					<td colspan="2" class="textarea">
					<? if ($is_dhtml_editor) { ?>
						<?=cheditor2('wr_content', $content);?>
					<? } else { ?>
						<div>
							<textarea id="wr_content" class="textarea" name="wr_content" rows="15" itemname="내용" required 
							<? if ($write_min || $write_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?}?>><?=$content?></textarea>
						</div>
						<div class="resize">
							<? if ($write_min || $write_max) { ?><span id="char_count"></span>글자&nbsp;<?}?>
							<span style="cursor: pointer;" onclick="textarea_decrease('wr_content', 10);"><img src="<?=$board_skin_path?>/img/up.gif" /></span>
							<span style="cursor: pointer;" onclick="textarea_original('wr_content', 10);"><img src="<?=$board_skin_path?>/img/start.gif" /></span>
							<span style="cursor: pointer;" onclick="textarea_increase('wr_content', 10);"><img src="<?=$board_skin_path?>/img/down.gif" /></span>
						</div>
						<? if ($write_min || $write_max) { ?><script type="text/javascript"> check_byte('wr_content', 'char_count'); </script><?}?>
					<? } ?>
					</td>
				</tr>

			<? if ($is_link) { ?>
				<? for ($i=1; $i<=$g4[link_count]; $i++) { ?>
				<tr>
					<th>링크 #<?=$i?></th>
					<td><input name="wr_link<?=$i?>" type="text" class="textarea_mem" value="<?=$write["wr_link{$i}"]?>" size="95%" itemname="링크 #<?=$i?>" /></td>
				</tr>
				<? } ?>
			<? } ?>

			<? if ($is_file) { ?>
				<tr>
					<th>
						파일첨부
						<span onclick="add_file();" style="cursor:pointer;"><img src="<?=$board_skin_path?>/img/btn_file_add.gif" /></span> 
						<span onclick="del_file();" style="cursor:pointer;"><img src="<?=$board_skin_path?>/img/btn_file_minus.gif" /></span>
					</th>
					<td>
						<table id="variableFiles" class="file"></table>
					</td>
				</tr>
			<? } ?>
            <? if ($is_trackback) { ?>
				<tr>
    				<td class=write_head>트랙백주소</td>
    				<td><input class='ed' size=50 name=wr_trackback itemname="트랙백" value="<?=$trackback?>">
        			<? if ($w=="u") { ?><input type=checkbox name="re_trackback" value="1">핑 보냄<? } ?></td>
				</tr>
				<tr>
                   <td colspan=2 height=1 bgcolor=#e7e7e7></td>
                 </tr>
			<? } ?>

			<? if ($is_guest) { ?>
				<tr>
					<th><img id="kcaptcha_image" /></th>
					<td><input class="textarea_mem" type="input" name="wr_key" itemname="자동등록방지" required />&nbsp;&nbsp;왼쪽의 글자를 입력하세요.</td>
				</tr>
			<? } ?>
			</tbody>
		</table>

		<div class="bbs_bt bbs_btn">
			<div class="bbs_left_btn">
				<a class="other" href="./board.php?bo_table=<?=$bo_table?>">목록</a>
			</div>
			<div class="bbs_right_btn">
				<input class="btn" type="submit" id="btn_submit" value="글쓰기" accesskey='s' />
			</div>
            <div class="clear".</div>
		</div>
	</form>
</div>
<script type="text/javascript" src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script type="text/javascript">
<?
// 관리자라면 분류 선택에 '공지' 옵션을 추가함
if ($is_admin) 
{
	echo "
	if (typeof(document.fwrite.ca_name) != 'undefined')
	{
		document.fwrite.ca_name.options.length += 1;
		document.fwrite.ca_name.options[document.fwrite.ca_name.options.length-1].value = '공지';
		document.fwrite.ca_name.options[document.fwrite.ca_name.options.length-1].text = '공지';
	}";
} 
?>

with (document.fwrite) 
{
	if (typeof(wr_name) != "undefined")
		wr_name.focus();
	else if (typeof(wr_subject) != "undefined")
		wr_subject.focus();
	else if (typeof(wr_content) != "undefined")
		wr_content.focus();

	if (typeof(ca_name) != "undefined")
		if (w.value == "u")
			ca_name.value = "<?=$write[ca_name]?>";
}

function html_auto_br(obj) 
{
	if (obj.checked) {
		result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
		if (result)
			obj.value = "html2";
		else
			obj.value = "html1";
	}
	else
		obj.value = "";
}

function fwrite_submit(f) 
{
	/*
	var s = "";
	if (s = word_filter_check(f.wr_subject.value)) {
		alert("제목에 금지단어('"+s+"')가 포함되어있습니다");
		return false;
	}

	if (s = word_filter_check(f.wr_content.value)) {
		alert("내용에 금지단어('"+s+"')가 포함되어있습니다");
		return false;
	}
	*/

	if (document.getElementById('char_count')) {
		if (char_min > 0 || char_max > 0) {
			var cnt = parseInt(document.getElementById('char_count').innerHTML);
			if (char_min > 0 && char_min > cnt) {
				alert("내용은 "+char_min+"글자 이상 쓰셔야 합니다.");
				return false;
			} 
			else if (char_max > 0 && char_max < cnt) {
				alert("내용은 "+char_max+"글자 이하로 쓰셔야 합니다.");
				return false;
			}
		}
	}

	if (document.getElementById('tx_wr_content')) {
		if (!ed_wr_content.outputBodyText()) { 
			alert('내용을 입력하십시오.'); 
			ed_wr_content.returnFalse();
			return false;
		}
	}

	<?
	if ($is_dhtml_editor) echo cheditor3('wr_content');
	?>

	var subject = "";
	var content = "";
	$.ajax({
		url: "<?=$board_skin_path?>/ajax.filter.php",
		type: "POST",
		data: {
			"subject": f.wr_subject.value,
			"content": f.wr_content.value
		},
		dataType: "json",
		async: false,
		cache: false,
		success: function(data, textStatus) {
			subject = data.subject;
			content = data.content;
		}
	});

	if (subject) {
		alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
		f.wr_subject.focus();
		return false;
	}

	if (content) {
		alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
		if (typeof(ed_wr_content) != "undefined") 
			ed_wr_content.returnFalse();
		else 
			f.wr_content.focus();
		return false;
	}

	if (!check_kcaptcha(f.wr_key)) {
		return false;
	}

	document.getElementById('btn_submit').disabled = true;
//	document.getElementById('btn_list').disabled = true;

	<?
	if ($g4[https_url])
		echo "f.action = '$g4[https_url]/$g4[bbs]/write_update.php';";
	else
		echo "f.action = './write_update.php';";
	?>
	
	return true;
}
</script>

<script type="text/javascript">
	var flen = 0;
	function add_file(delete_code)
	{
		var upload_count = <?=(int)$board[bo_upload_count]?>;
		if (upload_count && flen >= upload_count)
		{
			alert("이 게시판은 "+upload_count+"개 까지만 파일 업로드가 가능합니다.");
			return;
		}

		var objTbl;
		var objRow;
		var objCell;
		if (document.getElementById)
			objTbl = document.getElementById("variableFiles");
		else
			objTbl = document.all["variableFiles"];

		objRow = objTbl.insertRow(objTbl.rows.length);
		objCell = objRow.insertCell(0);

		objCell.innerHTML = "<input class='input-file' type='file' name='bf_file[]' title='파일 용량 <?=$upload_max_filesize?> 이하만 업로드 가능'>";
		if (delete_code)
			objCell.innerHTML += delete_code;
		else
		{
			<? if ($is_file_content) { ?>
			objCell.innerHTML += "<br><input class='textarea_mem' type='text' name='bf_content[]' title='업로드 이미지 파일에 해당 되는 내용을 입력하세요.'>";
			<? } ?>
			;
		}

		flen++;
	}

	<?=$file_script; //수정시에 필요한 스크립트?>

	function del_file()
	{
		// file_length 이하로는 필드가 삭제되지 않아야 합니다.
		var file_length = <?=(int)$file_length?>;
		var objTbl = document.getElementById("variableFiles");
		if (objTbl.rows.length - 1 > file_length)
		{
			objTbl.deleteRow(objTbl.rows.length - 1);
			flen--;
		}
	}
</script>

<script type="text/javascript" src="<?="$g4[path]/js/board.js"?>"></script>
<script type="text/javascript"> window.onload=function() { drawFont(); } </script>

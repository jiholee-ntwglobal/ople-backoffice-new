<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($is_dhtml_editor) {
    include_once("$g4[path]/lib/cheditor.lib.php");
    echo "<script src='$g4[editor_path]/cheditor.js'></script>";
    echo cheditor1('wr_content', $content);
}
?>

<script language="javascript">
// 글자수 제한
var char_min = parseInt(<?=$write_min?>); // 최소
var char_max = parseInt(<?=$write_max?>); // 최대
</script>

<form name="fwrite" method="post" onsubmit="return fwrite_submit(this);" enctype="multipart/form-data" style="margin:0px;">
<input type=hidden name=w        value="<?=$w?>">
<input type="hidden" name='uid' value='<?=$uid?>' />
<input type=hidden name=page     value="<?=$page?>">

<div class='PageTitle'>
  <img src="../images/menu/menu_title08.gif" alt="일대일문의" />
</div>

<tabletable width="100%" cellspacing="0" cellpadding="0" style="margin-bottom:8px;"><tr><td>

<table width="100%" cellpadding="0" cellspacing="0" class="list_styleC">
<colgroup>
    <col width="100"/>
    <col />
</colgroup>
<tbody>
<? 
/*
if ($is_name) { ?>
<tr>
    <th style='padding-left:20px; height:30px;'>이름</th>
    <td><input class='field_pub_01' maxlength=20 size=15 name=wr_name itemname="이름" required value="<?=$name?>"></td></tr>
<tr><td colspan=2 height=1 bgcolor=#e7e7e7></td></tr>
<? } ?>

<? if ($is_password) { ?>
<tr>
    <th>패스워드</th>
    <td><input class='field_pub_01' type=password maxlength=20 size=15 name=wr_password itemname="패스워드" <?=$password_required?>></td></tr>
<? } ?>

<? if ($is_email) { ?>
<tr>
    <th>이메일</th>
    <td><input class='field_pub_01' maxlength=100 size=50 name=wr_email email itemname="이메일" value="<?=$email?>"></td></tr>
<? } ?>

<? if ($is_homepage) { ?>
<tr>
    <th>홈페이지</td>
    <td><input class='field_pub_01' size=50 name=wr_homepage itemname="홈페이지" value="<?=$homepage?>"></td></tr>
<? } ?>

<? if ($is_notice || $is_html || $is_secret || $is_mail) { ?>
<tr>
    <th>옵션</td>
    <td>
        <? if ($is_html) { ?>
            <? if ($is_dhtml_editor) { ?>
            <input type=hidden value="html1" name="html">
            <? } else { ?>
            <input onclick="html_auto_br(this);" type=checkbox value="<?=$html_value?>" name="html" <?=$html_checked?>><span class=w_title>html</span>&nbsp;
            <? } ?>
        <? } ?>
        <? if ($is_mail) { ?><input type=checkbox value="mail" name="mail" <?=$recv_email_checked?>>답변메일받기&nbsp;<? } ?></td></tr>
<? } ?>

<?

if ($is_category) { ?>
<tr>
    <th>분류</th>
    <td><select name=ca_name required itemname="분류"><option value="">선택하세요<?=$category_option?></select></td></tr>
<? } 
*/
?>

<tr>
    <th>제목</th>
    <td><input class="field_pub_01" style="width:100%;" name=wr_subject id="wr_subject" itemname="제목" required value="<?=$write['subject']?>"></td></tr>
<tr>
    <th>내용</th>
    <td>
        <? if ($is_dhtml_editor) { ?>
            <?=cheditor2('fwrite', 'wr_content', '100%', '350');?>
        <? } else { ?>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
            <td>
                <span style="cursor: pointer;" onclick="textarea_decrease('wr_content', 10);"><img src="<?=$board_skin_path?>/img/up.gif"></span>
                <span style="cursor: pointer;" onclick="textarea_original('wr_content', 10);"><img src="<?=$board_skin_path?>/img/start.gif"></span>
                <span style="cursor: pointer;" onclick="textarea_increase('wr_content', 10);"><img src="<?=$board_skin_path?>/img/down.gif"></span>
			</td>
            <td><? if ($write_min || $write_max) { ?><span id=char_count></span>글자<?}?></td>
        </tr>
        </table>
        <textarea id="wr_content" name="wr_content" class=tx style='width:100%; word-break:break-all;' rows=10 itemname="내용" required
        <? if ($write_min || $write_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?}?>><?=$content?></textarea>
        <? if ($write_min || $write_max) { ?><script language="javascript"> check_byte('wr_content', 'char_count'); </script><?}?>
        <? } ?>
        </td>
</tr>

<? 
/*
if ($is_link) { ?>
<? for ($i=1; $i<=$g4[link_count]; $i++) { ?>
<tr>
    <th>링크 #<?=$i?></th>
    <td><input type='text' class='field_pub_01' size=50 name='wr_link<?=$i?>' itemname='링크 #<?=$i?>' value='<?=$write["wr_link{$i}"]?>'></td>
</tr>
<? } ?>
<? } */?>

<? if ($is_file) { ?>
<tr>
    <td>
	<table cellpadding=0 cellspacing=0>
	<tr><td style=" padding-top: 10px;">파일 <span onclick="add_file();" style='cursor:pointer; font-family:tahoma; font-size:12pt;'>+</span> <span onclick="del_file();" style='cursor:pointer; font-family:tahoma; font-size:12pt;'>-</span></td></tr></table></td>
    <td><table id="variableFiles" cellpadding=0 cellspacing=0></table><?// print_r2($file); ?>
        <script language="JavaScript">
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

            objCell.innerHTML = "<input type='file' class='field_pub_01' name='bf_file[]' title='파일 용량 <?=$upload_max_filesize?> 이하만 업로드 가능'>";
            if (delete_code)
                objCell.innerHTML += delete_code;
            else
            {
                <? if ($is_file_content) { ?>
                objCell.innerHTML += "<br><input type='text' class='field_pub_01' size=50 name='bf_content[]' title='업로드 이미지 파일에 해당 되는 내용을 입력하세요.'>";
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
        </script></td>
</tr>
<? } ?>



<? if ($is_norobot) { ?>
<tr>
    <th><img id='kcaptcha_image' /></th>
    <td><input class='field_pub_01' type=input size=10 name=wr_key itemname="자동등록방지" required>&nbsp;&nbsp;* 왼쪽의 글자중 <font color="red">빨간글자만</font> 순서대로 입력하세요.</td>
</tr>
<? } ?>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%" align="center" valign="top" style="padding-top:30px;">
        <input type=image id="btn_submit" src="<?=$board_skin_path?>/img/btn_write.gif" border=0 accesskey='s'>&nbsp;
        <a href="./board.php?bo_table=<?=$bo_table?>"><img id="btn_list" src="<?=$board_skin_path?>/img/btn_list.gif" border=0></a></td>
</tr>
</table>

</td></tr></table>
</form>
<script type="text/javascript" src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>

<script language="javascript">
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

with (document.fwrite) {
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

function html_auto_br(obj) {
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

function fwrite_submit(f) {

    if (document.getElementById('char_count') != null) {
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

    <?
    if ($is_dhtml_editor) {
        echo cheditor3('wr_content');
    }
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
    document.getElementById('btn_list').disabled = true;

    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/board_personnel_qa_update.php';";
    else
        echo "f.action = './board_personnel_qa_update.php';";
    ?>
    return true;
}
</script>

<script type="text/JavaScript" src="<?="$g4[path]/js/board.js"?>"></script>
<script type="text/JavaScript">
window.onload=function() {
    drawFont();
}
</script>
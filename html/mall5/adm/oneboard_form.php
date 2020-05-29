<?
$sub_menu = "300300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

function b_draw($pos, $color='red') {
    return "border-{$pos}-width:1px; border-{$pos}-color:{$color}; border-{$pos}-style:solid; ";
}

$html_title = "1:1 게시판";
if ($w == "") {
    $html_title .= " 생성";

    $ob_table_attr = "required alphanumericunderline";

    $oneboard[ob_count_delete] = '1';
    $oneboard[ob_count_modify] = '1';
    $oneboard[ob_read_point] = $config[cf_read_point];
    $oneboard[ob_write_point] = $config[cf_write_point];
    $oneboard[ob_comment_point] = $config[cf_comment_point];
    $oneboard[ob_download_point] = $config[cf_download_point];

    $oneboard[ob_gallery_cols] = '4';
    $oneboard[ob_table_width] = '97';
    $oneboard[ob_page_rows] = $config[cf_page_rows];
    $oneboard[ob_subject_len] = '60';
    $oneboard[ob_new] = '24';
    $oneboard[ob_hot] = '100';
    $oneboard[ob_image_width] = '600';
    $oneboard[ob_reply_order] = '1';
    $oneboard[ob_use_search] = '1';
    $oneboard[ob_skin] = 'one';
    $oneboard[bo_image_width] = '600';
} else if ($w == "u") {
    $html_title .= " 수정";

    $oneboard = sql_fetch(" select * from $g4[oneboard_table] where ob_table = '$ob_table' ");

    if (!$oneboard[ob_table])
        alert("존재하지 않은 1:1 게시판 입니다.");

    $ob_table_attr = "readonly style='background-color:#dddddd'";
}

$g4[title] = $html_title;
include_once ("./admin.head.php");
?>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<form name=foneboardform method=post action="javascript:foneboardform_submit(document.foneboardform)" enctype="multipart/form-data">
<input type=hidden name="w"    value="<?=$w?>">
<input type=hidden name="sfl"  value="<?=$sfl?>">
<input type=hidden name="stx"  value="<?=$stx?>">
<input type=hidden name="sst"  value="<?=$sst?>">
<input type=hidden name="sod"  value="<?=$sod?>">
<input type=hidden name="page" value="<?=$page?>">
<colgroup width=5% class='left'>
<colgroup width=20% class='col1 pad1 bold right'>
<colgroup width=75% class='col2 pad2'>
<tr>
    <td colspan=3 class=title align=left><img src='<?=$g4[admin_path]?>/img/icon_title.gif'> <?=$html_title?></td>
</tr>
<tr><td colspan=3 class='line1'></td></tr>
<tr class='ht'>
    <td></td>
    <td>TABLE</td>
    <td><input type=text class=ed name=ob_table size=30 maxlength=20 <?=$ob_table_attr?> itemname='TABLE' value='<?=$oneboard[ob_table] ?>'>
        <? 
        if ($w == "") 
            echo "영문자, 숫자, _ 만 가능 (공백없이 20자 이내)";
        else 
            echo "<a href='$g4[bbs_path]/one.php?ob_table=$oneboard[ob_table]'><img src='$g4[admin_path]/img/icon_view.gif' border=0 align=absmiddle></a>";
        ?>
    </td>
</tr>
<tr class='ht'>
    <td></td>
    <td>1:1 게시판 제목</td>
    <td>
        <input type=text class=ed name=ob_subject size=60 maxlength=120 required itemname='게시판 제목' value='<?=get_text($oneboard[ob_subject])?>'>
    </td>
</tr>
<tr class='ht'>
    <td></td>
    <td>상단 이미지</td>
    <td>
        <input type=file name=ob_image_head class=ed size=60>
        <?
        if ($oneboard[ob_image_head])
            echo "<br><a href='$g4[path]/data/one/$ob_table/$oneboard[ob_image_head]' target='_blank'>$oneboard[ob_image_head]</a> <input type=checkbox name='ob_image_head_del' value='$oneboard[ob_image_head]'> 삭제";
        ?>
    </td>
</tr>
<tr class='ht'>
    <td></td>
    <td>하단 이미지</td>
    <td>
        <input type=file name=ob_image_tail class=ed size=60>
        <? 
        if ($oneboard[ob_image_tail]) 
            echo "<br><a href='$g4[path]/data/one/$ob_table/$oneboard[ob_image_tail]' target='_blank'>$oneboard[ob_image_tail]</a> <input type=checkbox name='ob_image_tail_del' value='$oneboard[ob_image_tail]'> 삭제";
        ?>
    </td>
</tr>

<tr><td colspan=3 class='line2'></td></tr>
<tr class='ht'>
    <td>
        <input type=checkbox name=chk_admin value=1>
        <?=help("모든 1:1 게시판의 설정을 동일하게 변경할 경우에 체크합니다.");?>
    </td>
    <td>게시판 관리자</td>
    <td><input type=text class=ed name=ob_admin maxlength=20 value='<?=$oneboard[ob_admin]?>'></td>
</tr>

<tr><td colspan=3 class='line2'></td></tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_write_level value=1></td>
    <td>글쓰기 권한</td>
    <td><?=get_member_level_select('ob_write_level', 2, 10, $oneboard[ob_write_level]) ?></td>
</tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_upload_level value=1></td>
    <td>업로드 권한</td>
    <td><?=get_member_level_select('ob_upload_level', 2, 10, $oneboard[ob_upload_level]) ?></td>
</tr>

<tr><td colspan=3 class='line2'></td></tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_use_dhtml_editor value=1></td>
    <td>DHTML 에디터 사용</td>
    <td>
        <input type=checkbox name=ob_use_dhtml_editor value='1' <?=$oneboard[ob_use_dhtml_editor]?'checked':'';?>>사용
        &nbsp;<?=help("글작성시 내용을 DHTML 에디터 기능으로 사용할 것인지 설정합니다.\n\n스킨에 따라 적용되지 않을 수 있습니다.")?>
    </td>
</tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_use_email value=1></td>
    <td>메일발송 사용</td>
    <td><input type=checkbox name=ob_use_email value='1' <?=$oneboard[ob_use_email]?'checked':'';?>>사용</td>
</tr>

<tr><td colspan=3 class='line2'></td></tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_skin value=1></td>
    <td>스킨 디렉토리</td>
    <td><select name=ob_skin required itemname="스킨 디렉토리">
        <?
        $arr = get_skin_dir("oneboard");
        for ($i=0; $i<count($arr); $i++) {
            echo "<option value='$arr[$i]'>$arr[$i]</option>\n";
        }
        ?></select>
        <script language="JavaScript">document.foneboardform.ob_skin.value="<?=$oneboard[ob_skin]?>";</script>
    </td>
</tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_table_width value=1></td>
    <td>게시판 테이블 폭</td>
    <td><input type=text class=ed name=ob_table_width size=10 required itemname='게시판 테이블 폭' value='<?=$oneboard[ob_table_width]?>'> 100 이하는 %</td>
</tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_page_rows value=1></td>
    <td>페이지당 목록 수</td>
    <td><input type=text class=ed name=ob_page_rows size=10 required itemname='페이지당 목록 수' value='<?=$oneboard[ob_page_rows]?>'></td>
</tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_subject_len value=1></td>
    <td>제목 길이</td>
    <td><input type=text class=ed name=ob_subject_len size=10 required itemname='제목 길이' value='<?=$oneboard[ob_subject_len]?>'> 목록에서의 제목 글자수. 잘리는 글은 … 로 표시</td>
</tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_image_width value=1></td>
    <td>이미지 폭 크기</td>
    <td><input type=text class=ed name=ob_image_width size=10 required itemname='이미지 폭 크기' value='<?=$oneboard[ob_image_width]?>'> 픽셀 (게시판에서 출력되는 이미지의 폭 크기)</td>
</tr>

<tr><td colspan=3 class='line2'></td></tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_include_head value=1></td>
    <td>상단 파일 경로</td>
    <td><input type=text class=ed name=ob_include_head style='width:80%;' value='<?=$oneboard[ob_include_head]?>'></td>
</tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_include_tail value=1></td>
    <td>하단 파일 경로</td>
    <td><input type=text class=ed name=ob_include_tail style='width:80%;' value='<?=$oneboard[ob_include_tail]?>'></td>
</tr>

<tr><td colspan=3 class='line2'></td></tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_content_head value=1></td>
    <td>상단 내용</td>
    <td><textarea class=ed name=ob_content_head rows=5 style='width:80%;'><?=$oneboard[ob_content_head] ?></textarea></td>
</tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_content_tail value=1></td>
    <td>하단 내용</td>
    <td><textarea class=ed name=ob_content_tail rows=5 style='width:80%;'><?=$oneboard[ob_content_tail] ?></textarea></td></tr>
</tr>

<tr><td colspan=3 class='line2'></td></tr>
<tr class='ht'>
    <td><input type=checkbox name=chk_insert_content value=1></td>
    <td>글쓰기 기본 내용</td>
    <td><textarea class=ed name=ob_insert_content rows=5 style='width:80%;'><?=$oneboard[ob_insert_content] ?></textarea></td>
</tr>

<? for ($i=1; $i<=10; $i++) { ?>
<tr class='ht'>
    <td><input type=checkbox name=chk_<?=$i?> value=1></td>
    <td><input type=text class=ed name='ob_<?=$i?>_subj' value='<?=get_text($oneboard["ob_{$i}_subj"])?>' title='여분필드 <?=$i?> 제목' style='text-align:right;font-weight:bold;'></td>
    <td><input type=text class=ed style='width:80%;' name='ob_<?=$i?>' value='<?=get_text($oneboard["ob_$i"])?>' title='여분필드 <?=$i?> 설정값'></td>
</tr>
<? } ?>

<tr><td colspan=3 class='line1'></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 value='  목  록  ' onclick="document.location.href='oneboard_list.php?<?=$qstr?>';">&nbsp;
</form>

<script language="JavaScript">
function foneboardform_submit(f) {
    var tmp_title;
    var tmp_image;

    tmp_title = "상단";
    tmp_image = f.ob_image_head;
    if (tmp_image.value) {
        if (!tmp_image.value.toLowerCase().match(/.(gif|jpg|png)$/i)) {
            alert(tmp_title + "이미지가 gif, jpg, png 파일이 아닙니다.");
            return;
        }
    }

    tmp_title = "하단";
    tmp_image = f.ob_image_tail;
    if (tmp_image.value) {
        if (!tmp_image.value.toLowerCase().match(/.(gif|jpg|png)$/i)) {
            alert(tmp_title + "이미지가 gif, jpg, png 파일이 아닙니다.");
            return;
        }
    }

    f.action = "oneboard_form_update.php";
    f.submit();
}
</script>

<?
include_once ("./admin.tail.php");
?>

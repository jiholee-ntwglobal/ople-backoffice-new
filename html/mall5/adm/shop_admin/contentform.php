<?
$sub_menu = "600800";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor4.lib.php");

auth_check($auth[$sub_menu], "w");

$html_title = "내용";

if ($w == "u") 
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from $g4[yc4_content_table] where co_id = '$co_id' ";
    $co = sql_fetch($sql);
    if (!$co[co_id])  
        alert("등록된 자료가 없습니다.");
} 
else 
{
    $html_title .= " 입력";
    $co[co_html] = 2;
}

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title)?><p>

<script src="<?=$g4[path]?>/cheditor5/cheditor.js"></script>
<?=cheditor1('co_content', '100%','200');?>
<?=cheditor1('co_content_m', '100%','200');?>

<table cellpadding=0 cellspacing=0 width=100%>
<form name=frmcontentform method=post action="./contentformupdate.php" enctype="MULTIPART/FORM-DATA" onsubmit="return frmcontentform_check(this);">
<input type=hidden name=w value='<? echo $w?>'>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#ffffff></colgroup>
<tr><td colspan=2 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
    <td>ID</td>
    <td>
        <input type=text class=ed name=co_id size=20 max=20 value='<? echo $co[co_id] ?>' <? echo $readonly ?> required itemname='ID'>
        <? if ($w == 'u') { echo icon("보기", "$g4[shop_path]/content.php?co_id=$co_id"); } ?>
        (영문자, 숫자, _ 만 가능; 20자 이내; 공란 불가)
    </td>
</tr>
<tr class=ht>
    <td>제목</td>
    <td><input type=text class=ed name=co_subject style='width:99%;' value='<?=htmlspecialchars2($co[co_subject])?>' required itemname='제목'></td>
</tr>

<input type=hidden name=co_html value=1>
<tr>
    <td>내용</td>
    <td style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('frmcontentform', 'co_content', $co[co_content]);?></td>
</tr>
    <tr>
    <td>내용(모바일)</td>
    <td style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('frmcontentform', 'co_content_m', $co[co_content_m]);?></td>
</tr>
<tr class=ht>
    <td>상단이미지</td>
    <td>
        <input type=file class=ed name=co_himg size=40>
        <?
        $himg = "$g4[path]/data/content/{$co[co_id]}_h";
        if (file_exists($himg)) {
            echo "<input type=checkbox name=co_himg_del value='1'>삭제";
            $himg_str = "<img src='$himg' border=0>";
        }
        ?>
    </td>
</tr>
<? if ($himg_str) { echo "<tr><td colspan=2>$himg_str</td></tr>"; } ?>

<tr class=ht>
    <td>하단이미지</td>
    <td>
        <input type=file class=ed name=co_timg size=40>
        <?
        $timg = "$g4[path]/data/content/{$co[co_id]}_t";
        if (file_exists($timg)) {
            echo "<input type=checkbox name=co_timg_del value='1'>삭제";
            $timg_str = "<img src='$timg' border=0>";
        }
        ?>
    </td>
</tr>
<? if ($timg_str) { echo "<tr><td colspan=2>$timg_str</td></tr>"; } ?>

<tr><td colspan=2 height=1 bgcolor=#CCCCCC></td></tr>
</table>


<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./contentlist.php';">
</p>
</form>


<script language="javascript">
function frmcontentform_check(f) 
{
    errmsg = "";
    errfld = "";
    <?=cheditor3('co_content');?>
    <?=cheditor3('co_content_m');?>

    check_field(f.co_id, "ID를 입력하세요.");
    check_field(f.co_subject, "제목을 입력하세요.");
    check_field(f.co_content, "내용을 입력하세요.");

    if (errmsg != "") {
        alert(errmsg);
        errfld.focus();
        return false;
    }
    return true;
}

<? if ($w == "u") { ?>
    document.frmcontentform.co_subject.focus();
<? } else { ?>
    document.frmcontentform.co_id.focus();
<? } ?>
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

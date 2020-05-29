<?
$sub_menu = "400600";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor.lib.php");

auth_check($auth[$sub_menu], "w");

$html_title = "온라인견적 ";
if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from $g4[yc4_onlinecalc_table] where oc_id = '$oc_id' ";
    $oc = sql_fetch($sql);
    if (!$oc[oc_id])
        alert("등록된 자료가 없습니다.");
}
else
{
    $html_title .= " 입력";
}

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title);?><p>

<script src="<?=$g4[path]?>/cheditor/cheditor.js"></script>
<?=cheditor1('oc_head_html', stripslashes($oc[oc_head_html]));?>
<?=cheditor1('oc_tail_html', stripslashes($oc[oc_tail_html]));?>

<table cellpadding=0 cellspacing=0 width=100%>
<form name=fonlinecalcform method=post action="./onlinecalcformupdate.php" enctype="MULTIPART/FORM-DATA" onsubmit="return fonlinecalcform_check(this);">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=oc_id value='<? echo $oc_id ?>'>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
    <td>제 목</td>
    <td colspan=3>
        <input type=text class=ed name=oc_subject size=80 value='<?=$oc[oc_subject]?>' required itemname='제목'>
        <? if ($w == 'u') { echo icon("보기", "$g4[shop_path]/onlinecalc.php?oc_id=$oc_id"); } ?>
    </td>
</tr>
<tr>
    <td>
        선택된 분류 <?=help("오른쪽 등록된 목록의 분류목록에서 더블클릭하면 선택된 분류에 추가됩니다\n반드시 아래의 확인버튼을 클릭하셔야 정상 등록되므로 이점 유의하여 주십시오");?>
    </td>
    <td style='padding-top:5px; padding-bottom:5px;'>
        분류 선택후 <FONT COLOR="#FF6600">더블클릭하면 삭제됨</FONT><br>
        <select name=categoryselect size=8 style='width:250px;' ondblclick="category_del(this);">
        <?
        if (trim($oc[oc_category]))
        {
            $str = "";
            $comma = "";
            $tmp = explode("\n", trim($oc[oc_category]));
            for ($i=0; $i<count($tmp); $i++) {
                $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$tmp[$i]' ";
                $row = sql_fetch($sql);
                echo "<option value='$tmp[$i]'>".cut_str($row[ca_name],30);
                $str .= $comma . $tmp[$i];
                $comma = ",";
            }
        }
        ?>
        </select>
        <input type='hidden' name='ca_list' value='<?=$str?>'>
    </td>
    <td>◀ 분류목록</td>
    <td>
        분류 선택후 <FONT COLOR="#0E87F9">더블클릭하면 왼쪽에 추가됨</FONT><br>
        <select size=8 style='width:250px; background-color:#F6F6F6;' ondblclick="category_add(this);">
        <?
        $sql = " select ca_id, ca_name from $g4[yc4_category_table] order by ca_id ";
        $result = sql_query($sql);
        for ($i=0; $row=mysql_fetch_array($result); $i++) {
            echo "<option value='$row[ca_id]'>".cut_str($row[ca_name],30);
        }
        ?>
        </select>
        <SCRIPT LANGUAGE="JavaScript">
            function category_add(fld)
            {
                var f = document.fonlinecalcform;
                var len = f.categoryselect.length;
                var find = false;

                for (i=0; i<len; i++) {
                    if (fld.options[fld.selectedIndex].value == f.categoryselect.options[i].value) {
                        find = true;
                        break;
                    }
                }

                // 같은 분류를 찾지못하였다면 입력
                if (!find) {
                    f.categoryselect.length += 1;
                    f.categoryselect.options[len].value = fld.options[fld.selectedIndex].value;
                    f.categoryselect.options[len].text  = fld.options[fld.selectedIndex].text;
                }

                category_hidden();
            }

            function category_del(fld)
            {
                if (fld.length == 0) {
                    return;
                }

                if (fld.selectedIndex < 0)
                    return;

                for (i=0; i<fld.length; i++) {
                    // 선택된것과 값이 같다면 1을 더한값을 현재것에 복사
                    if (fld.options[i].value == fld.options[fld.selectedIndex].value) {
                        for (k=i; k<fld.length-1; k++) {
                            fld.options[k].value = fld.options[k+1].value;
                            fld.options[k].text  = fld.options[k+1].text;
                        }
                        break;
                    }
                }
                fld.length -= 1;

                category_hidden();
            }

            // hidden 값을 변경
            function category_hidden()
            {
                var f = fonlinecalcform;

                var str = '';
                var comma = '';
                for (i=0; i<f.categoryselect.length; i++) {
                    str += comma + f.categoryselect.options[i].value;
                    comma = ',';
                }
                f.ca_list.value = str;
            }
        </SCRIPT>
    </td>
</tr>
<tr class=ht>
    <td>상단이미지</td>
    <td colspan=3>
        <input type=file class=ed name=oc_himg size=40>
        <?
        $himg_str = "";
        $himg = "$g4[path]/data/onlinecalc/{$oc[oc_id]}_h";
        if (file_exists($himg)) {
            echo "<input type=checkbox name=oc_himg_del value='1'>삭제";
            $himg_str = "<img src='$himg' border=0>";
            //$size = getimagesize($himg);
            //echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('himg', $size[0], $size[1]);\"><input type=checkbox name=oc_himg_del value='1'>삭제";
            //echo "<div id='himg' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$himg' border=1></div>";
        }
        ?> &nbsp;<?=help("온라인견적 페이지 상단에 출력하는 이미지입니다.");?>
    </td>
</tr>
<? if ($himg_str) { echo "<tr><td colspan=4>$himg_str</td></tr>"; } ?>

<tr class=ht>
    <td>하단이미지</td>
    <td colspan=3>
        <input type=file class=ed name=oc_timg size=40>
        <?
        $timg_str = "";
        $timg = "$g4[path]/data/onlinecalc/{$oc[oc_id]}_t";
        if (file_exists($timg)) {
            echo "<input type=checkbox name=oc_timg_del value='1'>삭제";
            $timg_str = "<img src='$timg' border=0>";
            //$size = getimagesize($timg);
            //echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('timg', $size[0], $size[1]);\"><input type=checkbox name=oc_timg_del value='1'>삭제";
            //echo "<div id='timg' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$timg' border=1></div>";
        }
        ?> &nbsp;<?=help("온라인견적 페이지 하단에 하단에 출력하는 이미지입니다.");?>
    </td>
</tr>
<? if ($timg_str) { echo "<tr><td colspan=4>$timg_str</td></tr>"; } ?>

<tr>
    <td>상단 내용</td>
    <td colspan=3 align=right style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('fonlinecalcform', 'oc_head_html', '100%', '200');?></td>
</tr>
<tr>
    <td>하단 내용</td>
    <td colspan=3 align=right style='padding-top:5px; padding-bottom:5px;'><?=cheditor2('fonlinecalcform', 'oc_tail_html', '100%', '200');?></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./onlinecalclist.php';"></p>
</form>

<br><font color=crimson>※ 선택옵션이 있는 상품은 온라인견적에 포함되지 않습니다.</font>

<script language="javascript">
function fonlinecalcform_check(f)
{
    errmsg = "";
    errfld = "";

    check_field(f.oc_subject, "제목을 입력하세요.");

    if (errmsg != "") {
        alert(errmsg);
        errfld.focus();
        return false;
    }

    <?=cheditor3('oc_head_html');?>
    <?=cheditor3('oc_tail_html');?>

    return true;
}

document.fonlinecalcform.oc_subject.focus();
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

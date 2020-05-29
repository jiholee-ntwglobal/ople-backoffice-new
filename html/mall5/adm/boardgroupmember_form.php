<?
$sub_menu = "100900";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$mb = get_member($mb_id);
if (!$mb[mb_id])
    alert("존재하지 않는 회원입니다."); 

$g4[title] = "접근가능그룹선택";
include_once("./admin.head.php");

$colspan = 4;
?>

<table width=100% cellpadding=3 cellspacing=1>
<tr>
    <td>* <? echo "<a href='./member_form.php?w=u&mb_id=$mb[mb_id]'><b>$mb[mb_id]</b> ($mb[mb_name] / $mb[mb_nick])</a> 님이 접근가능한 그룹 목록"; ?></td>
</tr>
</table>
    
<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=120>
<colgroup width=''>
<colgroup width=200>
<colgroup width=100>
<tr><td colspan='<?=$colspan?>' class='line1'></td></tr>
<tr class='bgcol1 bold col1 ht center'>
    <td>그룹아이디</td>
    <td>그룹</td>
    <td>처리일시</td>
    <td>삭제</td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>
<?
$sql = " select * 
           from $g4[group_member_table] a, 
                $g4[group_table] b
          where a.mb_id = '$mb[mb_id]' 
            and a.gr_id = b.gr_id ";
if ($is_admin != 'super') 
    $sql .= " and b.gr_admin = '$member[mb_id]' ";
$sql .= " order by a.gr_id desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $s_del = "<a href=\"javascript:del('./boardgroupmember_update.php?w=d&gm_id=$row[gm_id]')\"><img src='img/icon_delete.gif' border=0></a>";

    $list = $i%2;
    echo "
    <tr class='list$list col1 ht center'>
        <td><a href='$g4[bbs_path]/group.php?gr_id=$row[gr_id]'><b>$row[gr_id]</b></a></td>
        <td><b>$row[gr_subject]</b></td>
        <td>$row[gm_datetime]</td>
        <td>$s_del</td>
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan='$colspan' align=center height=100>접근가능한 그룹이 없습니다.</td></tr>";
}
?>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>
</table>

<p>
<table width=100% align=center cellpadding=3 cellspacing=1 class=tablebg>
<form name=fboardgroupmember_form method=post action='./boardgroupmember_update.php' onsubmit="return boardgroupmember_form_check(this)">
<input type=hidden name=mb_id value='<?=$mb[mb_id]?>'>
<colgroup width=20% class='col1 pad1 bold right'>
<colgroup width=80% class='col2 pad2'>
<tr>
    <td>그룹</td>
    <td>
        <select name=gr_id>
        <option value=''>접근가능 그룹을 선택하세요.
        <option value=''>--------------------------
        <?
        $sql = " select * 
                   from $g4[group_table]
                  where gr_use_access = 1 ";
        //if ($is_admin == 'group') {
        if ($is_admin != 'super') 
            $sql .= " and gr_admin = '$member[mb_id]' ";
        $sql .= " order by gr_id ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            echo "<option value='$row[gr_id]'>$row[gr_subject]";            
        }
        ?>
        </select>
        &nbsp;
        <input type=submit class=btn1 value='  확  인  ' accesskey='s'>
    </td>
</tr>
</table>
</form>

<script language="JavaScript">
function boardgroupmember_form_check(f) 
{
    if (f.gr_id.value == '') {
        alert('접근가능 그룹을 선택하세요.');
        return false;
    }

    return true;
}
</script>

<?
include_once("./admin.tail.php");
?>

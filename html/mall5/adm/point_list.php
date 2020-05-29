<?php
$sub_menu = "200200";
include_once "./_common.php";

auth_check($auth[$sub_menu], "r");

/* // 김선용 201208 : 포인트 베스트선정 지급오류 처리. (db삭제후 다시 합산처리해야 정상 포인트로 다시 환원됨)
$tm = array('mimiclover', 'mushu80', 'cbeta', 'knix35', 'hijinone', 'psm0203', 'ms6555', 'hemihouse', 'yhee917', 'boygeorge', 'whdtm');
for($k=0; $k<count($tm); $k++){

    $sql = " select sum(po_point) as sum_po_point from $g4[point_table] where mb_id = '$tm[$k]' ";
    $row = sql_fetch($sql);
    $sum_point = $row[sum_po_point];
    $sql= " update $g4[member_table] set mb_point = '$sum_point' where mb_id = '$tm[$k]' ";
    sql_query($sql);
}
exit;
*/

$sql_common = " from $g4[point_table] ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "mb_id" :
            $sql_search .= " ($sfl = '$stx') ";
            break;
        default :
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "po_datetime";
    $sod = "desc";
}
$sql_order = " order by $sst $sod ";
/* 김선용 200804 : 포인트 테이블 레코드 카운트로 인한 부하 처리(레코드 10만건 이상 부하심함)*/
if($_GET['stx']) {
    $sql = " select count(*) as cnt
         $sql_common
         $sql_search
         ";
    $row = sql_fetch($sql);
    $total_count = $row[cnt];
}else {
    $total_count = $config['cf_point_sum'];
}

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
          $sql_common
          $sql_search
          $sql_order
          limit $from_record, $rows ";
$result = sql_query($sql);
//echo $sql;
$listall = "<a href='$_SERVER[PHP_SELF]'>처음</a>";

if ($sfl == "mb_id" && $stx)
    $mb = get_member($stx);

$g4[title] = "포인트관리";
include_once ("./admin.head.php");

$colspan = 8;
?>

<script language="javascript" src="<?=$g4[path]?>/js/sideview.js"></script>
<script language="JavaScript">
var list_update_php = "";
var list_delete_php = "point_list_delete.php";
</script>

<script language="JavaScript">
function point_clear()
{
    if (confirm("포인트 정리를 하시면 최근 50건 이전의 포인트 부여 내역을 삭제하므로\n\n포인트 부여 내역을 필요로 할때 찾지 못할 수도 있습니다.\n\n\n그래도 진행하시겠습니까?"))
    {
        document.location.href = "./point_clear.php?ok=1";
    }
}
</script>

<table width=100%>
<form name=fsearch method=get>
<tr>
    <td width=50% align=left>
        <?=$listall?> (건수 : <?=number_format($total_count)?>)
        <?
        if ($mb[mb_id])
            echo "&nbsp;(" . $mb[mb_id] ." 님 포인트 합계 : " . number_format($mb[mb_point]) . "점)";
        else {
            $row2 = sql_fetch(" select sum(po_point) as sum_point from $g4[point_table] ");
            echo "&nbsp;(전체 포인트 합계 : " . number_format($row2[sum_point]) . "점)";
        }
        ?>
        <? if ($is_admin == "super") { ?><!-- <a href="javascript:point_clear();">포인트정리</a> --><? } ?>
    </td>
    <td width=50% align=right>
        <select name=sfl class=cssfl>
            <option value='mb_id'>회원아이디</option>
            <option value='po_content'>내용</option>
        </select>
        <input type=text name=stx required itemname='검색어' value='<?=$stx?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle></td>
</tr>
</form>
</table>

<table width=100% cellpadding=0 cellspacing=1>
<form name=fpointlist method=post>
<input type=hidden name=sst  value='<?=$sst?>'>
<input type=hidden name=sod  value='<?=$sod?>'>
<input type=hidden name=sfl  value='<?=$sfl?>'>
<input type=hidden name=stx  value='<?=$stx?>'>
<input type=hidden name=page value='<?=$page?>'>
<colgroup width=30>
<colgroup width=100>
<colgroup width=80>
<colgroup width=80>
<colgroup width=140>
<colgroup width=''>
<colgroup width=50>
<colgroup width=80>
<tr><td colspan='<?=$colspan?>' class='line1'></td></tr>
<tr class='bgcol1 bold col1 ht center'>
    <td><input type=checkbox name=chkall value='1' onclick='check_all(this.form)'></td>
    <td><?=subject_sort_link('mb_id')?>회원아이디</a></td>
    <td>이름</td>
    <td>별명</td>
    <td><?=subject_sort_link('po_datetime')?>일시</a></td>
    <td><?=subject_sort_link('po_content')?>포인트 내용</a></td>
    <td><?=subject_sort_link('po_point')?>포인트</a></td>
    <td>포인트누계</td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>
<?
$point_ctotal = 0;
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    //곽범석 포인트 누계 추가
    if($i==0){
        $sql3 = " select sum(ifnull(po_point,0)) sums
                      $sql_common
                      $sql_search
                      and date_format(po_datetime,'%Y%m%d%H%i%s') <= date_format('{$row['po_datetime']}','%Y%m%d%H%i%s')
                      $sql_order
                ";
        $row3 = sql_fetch($sql3);

    }
    if ($row2[mb_id] != $row[mb_id])
    {
        $sql2 = " select mb_id, mb_name, mb_nick, mb_email, mb_homepage, mb_point from $g4[member_table] where mb_id = '$row[mb_id]' ";
        $row2 = sql_fetch($sql2);
    }

    $mb_nick = get_sideview($row[mb_id], $row2[mb_nick], $row2[mb_email], $row2[mb_homepage]);

    $link1 = $link2 = "";
    if (!preg_match("/^\@/", $row[po_rel_table]) && $row[po_rel_table])
    {
        $link1 = "<a href='$g4[bbs_path]/board.php?bo_table={$row[po_rel_table]}&wr_id={$row[po_rel_id]}' target=_blank>";
        $link2 = "</a>";
    }

    $list = $i%2;
    echo "
    <input type=hidden name=po_id[$i] value='$row[po_id]'>
    <input type=hidden name=mb_id[$i] value='$row[mb_id]'>
    <tr class='list$list col1 ht center'>
        <td><input type=checkbox name=chk[] value='$i'></td>
        <td><a href='?sfl=mb_id&stx=$row[mb_id]'>$row[mb_id]</a></td>
        <td>$row2[mb_name]</td>
        <td>$mb_nick</td>
        <td>$row[po_datetime]</td>
        <td align=left>&nbsp;{$link1}$row[po_content]{$link2}</td>
        <td align=right>".number_format($row[po_point])."&nbsp;</td>
        <td align=right>".number_format($row3['sums']-$point_ctotal)."&nbsp;</td>
    </tr> ";

    $point_ctotal += $row[po_point];
}

if ($i == 0)
    echo "<tr><td colspan='$colspan' align=center height=100 bgcolor=#ffffff>자료가 없습니다.</td></tr>";

echo "<tr><td colspan='$colspan' class='line2'></td></tr>";
echo "</table>";

$pagelist = get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");
echo "<table width=100% cellpadding=3 cellspacing=1>";
echo "<tr><td width=50%>";
echo "<input type=button class='btn1' value='선택삭제' onclick=\"btn_check(this.form, 'delete')\">";
echo "</td>";
echo "<td width=50% align=right>$pagelist</td></tr></table>\n";

if ($stx)
    echo "<script language='javascript'>document.fsearch.sfl.value = '$sfl';</script>\n";

if (strstr($sfl, "mb_id"))
    $mb_id = $stx;
else
    $mb_id = "";
?>
</form>

<script language='javascript'> document.fsearch.stx.focus(); </script>

<?$colspan=4?>
<p>
<form name=fpointlist2 method=post action="./point_update.php" autocomplete="off" onsubmit='return fpointlist2_submit(document.fpointlist2);'>
<table width=100% cellpadding=0 cellspacing=1 class=tablebg>

<input type=hidden name=sfl  value='<?=$sfl?>'>
<input type=hidden name=stx  value='<?=$stx?>'>
<input type=hidden name=sst  value='<?=$sst?>'>
<input type=hidden name=sod  value='<?=$sod?>'>
<input type=hidden name=page value='<?=$page?>'>
<colgroup width=150>
<colgroup width=''>
<colgroup width=100>
<colgroup width=100>
<tr><td colspan='<?=$colspan?>' class='line1'></td></tr>
<tr class='bgcol1 bold col1 ht center'>
    <td>회원아이디</td>
    <td>포인트 내용</td>
    <td>포인트</td>
    <td>입력</td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>
<tr class='ht center'>
    <td><input type=text class=ed name=mb_id required itemname='회원아이디' value='<?=$mb_id?>'></td>
    <td><input type=text class=ed name=po_content required itemname='내용' style='width:99%;'></td>
    <td><input type=text class=ed name=po_point required itemname='포인트' size=10></td>
    <td><input type=submit class=btn1 value='  확  인  '></td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>

</table>
</form>
<script language="JavaScript">
function fpointlist2_submit(f)
{
    f.action = "./point_update.php";
	f.onsubmit = "return false;";
	$(f).find('input[type=submit]').attr('disabled',true);
    //f.submit();
	return true;
}
</script>

<?
include_once ("./admin.tail.php");
?>

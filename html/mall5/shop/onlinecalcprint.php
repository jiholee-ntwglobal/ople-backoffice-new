<?
include_once("./_common.php");
?>
<html>
<head>
<title>온라인 견적서</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=$g4['charset']?>">
<style>
    body, td { font-size:9pt; font-family:돋움; }
</style>
</head>
<body>
<table width=640>
<tr align=center><td colspan=8 height=70><h3>온라인 견적서</h3></td></tr>
<tr><td colspan=8>견적일시 : <? echo date("Y년 m월 d일 H시 i분") ?></tr>
<tr><td colspan=8 height=2><hr size=0></td></tr>
<tr align=center height=22>
    <td width=100><b>구 분</b></td>
    <td width=340><b>품 명</b></td>
    <td width=40 align=right><b>수 량</b></td>
    <td width=80 align=right><b>단 가</b>&nbsp;</td>
    <td width=80 align=right><b>합 계</b>&nbsp;</td>
</tr>
<tr><td colspan=8 height=2><hr size=0></td></tr>
<?
$k = 0;
$total_amount = 0;
for ($i=0; $i<count($_POST[it_id]); $i++)
{
    $it_id = $_POST[it_id][$i];
    if (!$it_id)
        continue;

    $sql = " select b.ca_name 
               from $g4[yc4_item_table] a, $g4[yc4_category_table] b 
              where a.ca_id = b.ca_id
                and a.it_id= '$it_id' ";
    $ca = sql_fetch($sql);

    $qty = $_POST[ct_qty][$i];

    $amount = $_POST[it_amount][$i];
    $subtotal_amount = $amount * $qty;
    $total_amount += $subtotal_amount;

    if ($k > 0)
        echo "<tr><td colspan=8 height=1><hr size=0></td></tr>";
    $k++;

    echo "<tr height=20>";
    echo "<td align=center>$ca[ca_name]</td>";
    echo "<td>".($_POST[it_name][$i])."</td>";
    echo "<td align=center>$qty</td>";
    echo "<td align=right>".display_amount($amount)."</td>";
    echo "<td align=right>".display_amount($subtotal_amount)."</td>";
    echo "</tr>";
}
?>
<tr><td colspan=8 height=2><hr size=0></td></tr>
<tr><td colspan=8 align=right height=22><b>견적 합계 : &nbsp;&nbsp;&nbsp;<font color=crimson><? echo display_amount($total_amount) ?></font></b></td></tr>
<tr><td colspan=8 height=2><hr size=0></td></tr>
<tr><td colspan=8 height=3></td></tr>
<tr><td colspan=8>상 호 : <?=$default[de_admin_company_name]?></td></tr>
<tr><td colspan=8>전 화 : <?=$default[de_admin_company_tel]?></td></tr>
<tr><td colspan=8>주 소 : (<?=$default[de_admin_company_zip]?>) <?=$default[de_admin_company_addr]?></td></tr>
<tr><td colspan=8>홈페이지 : <?=$g4[url]?></td></tr>
</table>
</body>
</html>

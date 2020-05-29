<?
$str = "";
$exists = false;

$depth2_ca_id = substr($ca_id, 0, 2);

$sql = " select ca_id, ca_name from $g4[yc4_category_table]
          where ca_id like '${depth2_ca_id}%'
            and length(ca_id) = 4
            and ca_use = '1'
          order by ca_id ";
$result = sql_query($sql);

// 김선용 200804 : 대분류 정보
$mca = sql_fetch("select ca_name from {$g4['yc4_category_table']} where ca_id='{$depth2_ca_id}' and ca_use=1");

$str .= "<tr><td width=11 background='$g4[shop_img_path]/ca_bg02.gif'></td>";
$str .= "<td><table width=740 border=0><tr><td><a href='./list.php?ca_id={$depth2_ca_id}'><span style='color: #fa5a00; font-size:18px; font-family:Dotum; font-weight:bold; line-height:200%;' >{$mca['ca_name']}&nbsp;&nbsp;>&nbsp;&nbsp;</span></a><br>";
while ($row=sql_fetch_array($result)) {
    if (preg_match("/^$row[ca_id]/", $ca_id))
        $span = "<span style=' font-size:14px; font-family:Dotum, Arial, Helvetica, sans-serif, NanumGothic, 돋움, Apple SD Gothic Neo; line-height:250%; font-weight:bold; white-space: nowrap;
        background: #0096ff;padding: 5px;color: #fff'>";
    else
        $span = "<span style='font-size:14px; font-family:Dotum, Arial, Helvetica, sans-serif, NanumGothic, 돋움, Apple SD Gothic Neo; line-height:250%; white-space: nowrap; '>";
    $str .= "<a href='./list.php?ca_id=$row[ca_id]'>{$span}$row[ca_name]</a></span>&nbsp&nbsp|&nbsp&nbsp; ";
    $exists = true;
}
$str .= "</td></tr></table></td><td width=11 background='$g4[shop_img_path]/ca_bg03.gif'></td>";

if ($exists) {
    echo "
    <br>
    <table width=755 cellpadding=0 cellspacing=0 align=center border=0>
    <colgroup width=11>
    <colgroup width=''>
    <colgroup width=11>
    <tr>
        <td width=11><img src='$g4[shop_img_path]/ca_box01.gif'></td>
        <td background='$g4[shop_img_path]/ca_bg01.gif'></td>
        <td width=11><img src='$g4[shop_img_path]/ca_box02.gif'></td>
    </tr>
    $str
    <tr>
        <td width=11><img src='$g4[shop_img_path]/ca_box03.gif'></td>
        <td background='$g4[shop_img_path]/ca_bg04.gif'></td>
        <td width=11><img src='$g4[shop_img_path]/ca_box04.gif'></td>
    </tr>
    </table><br>";
}
?>
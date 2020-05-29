<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

if (!defined("_ORDERINQUIRY_")) exit; // 개별 페이지 접근 불가 
?>

<table width=100% cellpadding=0 cellspacing=0 class='list_styleA'>
<colgroup>
	<col width='180'/>
	<col />
	<col width='90'/>
	<col width='150'/>
	<col width='150'/>
	<col width='100'/>
</colgroup>
<thead>
<? if (!$limit) { echo "<tr><td colspan=6 align=right>총 {$cnt} 건</td></tr>"; } ?>
<tr>
    <th>주문서번호</th>
    <th>주문일시</th>
    <th>상품수</th>
    <th>주문금액</th>
    <th>입금액</th>
    <th>미입금액</th>
</tr>
</thead>
<tbody>
<?
$sql = " select a.od_id, 
                a.*, "._MISU_QUERY_."
           from $g4[yc4_order_table] a
           left join $g4[yc4_cart_table] b on (b.on_uid=a.on_uid)
          where mb_id = '$member[mb_id]' and isnull(a.ihappy_fg)
          group by a.od_id 
          order by a.od_id desc 
          $limit ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ($i > 0)
        //echo "<tr><td colspan=6 height=1 background='$g4[shop_img_path]/dot_line.gif'></td></tr>\n";

    echo "<tr>\n";
    echo "<td>";
    echo "<input type=hidden name='ct_id[$i]' value='$row[ct_id]'>\n";
    echo "<a href='./orderinquiryview.php?od_id=$row[od_id]&on_uid=$row[on_uid]'><U>$row[od_id]</U></a></td>\n";
    echo "<td>".substr($row[od_time],0,16)." (".get_yoil($row[od_time]).")</td>\n";
    echo "<td>$row[itemcount]</td>\n";
    echo "<td><span class='amount'><span class='text_won'>\</span> ".display_amount($row[orderamount])."</span></td>\n";
    echo "<td><span class='amount'><span class='text_won'>\</span> ".display_amount($row[receiptamount])."</span></td>\n";
    echo "<td><span class='item_point'>".display_amount($row[misu])."</span></td>\n";
    echo "</tr>\n";
}

if ($i == 0)
    echo "<tr><td colspan=6 height=100 align=center><span class=point>주문 내역이 없습니다.</span></td></tr>";
?>
</tbody>
</table>

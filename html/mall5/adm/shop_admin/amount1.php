<?php
include_once "./_common.php";
if($is_admin != "super") exit;
include_once "$g4[admin_path]/admin.head.php";


// 구매액 기준
$sql = "select sum(a.ct_amount*a.ct_qty) as sum_amount, c.mb_id, c.mb_name, c.mb_email, c.mb_hp from {$g4['yc4_cart_table']} a left join {$g4['yc4_order_table']} b on a.on_uid=b.on_uid left join {$g4['member_table']} c on b.mb_id=c.mb_id where a.ct_status RegExp('배송|완료') and b.mb_id<>'' group by b.mb_id having sum_amount >= 2000000 order by sum_amount desc";
$result = sql_query($sql);
?>
<table cellpadding=0 cellspacing=4 align="center" width="100%" summary="">
<tr><td height=2 colspan=3 bgcolor=#c7c7c7></td></tr>
<tr align=center>
	<td height=26>아이디</td>
	<td>이름</td>
	<td>이메일</td>
	<td>구매액</td>
        <td>전화번호</td>
</tr>
<tr><td height=2 colspan=3 bgcolor=#c7c7c7></td></tr>
<?for($k=0; $row=sql_fetch_array($result); $k++){?>
<tr>
	<td height=24><?=$row['mb_id']?></td>
	<td><?=$row['mb_name']?></td>
	<td><?=$row['mb_email']?></td>
        <td><?=number_format($row['sum_amount']);?></td>
        <td><?=$row['mb_hp']?></td>

</tr>
<?}?>
</table>

<? include_once "$g4[admin_path]/admin.tail.php"; ?>

<?php
include_once "./_common.php";
if($is_admin != "super") exit;
include_once "$g4[admin_path]/admin.head.php";

$sql = "select a.*, b.ct_status from {$g4['yc4_order_table']} a
	left outer join {$g4['yc4_cart_table']} b on a.on_uid=b.on_uid
	where b.ct_status RegExp('배송|완료')
	group by a.od_id order by a.od_id";

$result = sql_query($sql);
?>
<table cellpadding=0 cellspacing=4 align="center" width="100%" summary="">
<tr><td height=2 colspan=3 bgcolor=#c7c7c7></td></tr>
<tr align=center>
	<td height=26>이름</td>
	<td>이메일</td>
	<td>배송상태</td>
</tr>
<tr><td height=2 colspan=3 bgcolor=#c7c7c7></td></tr>
<?for($k=0; $row=sql_fetch_array($result); $k++){?>
<tr>
	<td height=24><?=$row['od_name']?></td>
	<td><?=$row['od_email']?></td>
	<td><?=$row['ct_status']?></td>
</tr>
<?}?>
</table>

<? include_once "$g4[admin_path]/admin.tail.php"; ?>

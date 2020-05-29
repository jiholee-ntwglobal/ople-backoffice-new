<?php
include_once "./_common.php";
if($is_admin != "super") exit;
include_once "$g4[admin_path]/admin.head.php";


// 구매액 기준
$sql = "select mb_name, mb_email, mb_hp from g4_member where mb_leave_date = ''";
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
	<td><?=$row['mb_name']?></td>
	<td><?=$row['mb_email']?></td>
        <td><?=$row['mb_hp']?></td>

</tr>
<?}?>
</table>

<? include_once "$g4[admin_path]/admin.tail.php"; ?>

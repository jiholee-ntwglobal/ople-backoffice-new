<?php
$sub_menu = "400920";
include "_common.php";
auth_check($auth[$sub_menu], "w");


$g4[title] = "오플코리아 미등록상품관리";
include $g4['full_path']."/adm/admin.head.php";



$fg = $_GET['fg'] == 'it_id' ? "it_id":"it_maker";
switch($fg){
	case 'it_id' :
		$th = "
			<td>상품코드</td>
			<td>브랜드</td>
			<td>상품명</td>
			<td>".icon('입력')."</td>
		";
		$sql = sql_query("select a.it_id,b.it_maker,b.it_name from yc4_opk_no_item a,".$g4['yc4_item_table']." b where a.it_id = b.it_id");
		while($row = sql_fetch_array($sql)){
			$list_tr .= "
				<tr>
					<td>".$row['it_id']."</td>
					<td>".$row['it_maker']."</td>
					<td>".$row['it_name']."</td>
					<td>".icon('수정','')."</td>
				</tr>
			";
		}
		break;
	case 'it_maker' :
		$th = "
			<td>브랜드</td>
			<td>상품수</td>
			<td>".icon('입력','opk_no_item_form.php?fg='.$fg)."</td>
		";
		$sql = sql_query("select a.it_maker,count(*) as cnt from yc4_opk_no_item_maker a left join ".$g4['yc4_item_table']." b on a.it_maker = b.it_maker group by a.it_maker order by cnt desc");
		while($row = sql_fetch_array($sql)){
			$list_tr .= "
				<tr>
					<td>".$row['it_maker']."</td>
					<td>".number_format($row['cnt'])."</td>
					<td>".icon('수정','')."</td>
				</tr>
			";
		}
		break;
}
?>

<ul style='list-style:none;'>
	<li style='float:left; border:1px solid #dddddd; padding:5px; <?=$fg == 'it_maker' ? "font-weight:bold;":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>?fg=it_maker">브랜드</a></li>
	<li style='float:left; border:1px solid #dddddd; padding:5px; <?=$fg == 'it_id' ? "font-weight:bold;":""?>'><a href="<?=$_SERVER['PHP_SELF'];?>?fg=it_id">상품코드</a></li>
</ul>
<table width='100%'>
	<tr>
		<?=$th;?>
	</tr>
	<?=$list_tr;?>
</table>

<?php
include $g4['full_path']."/adm/admin.tail.php";
?>
<?
include "_common.php";

if($_GET['mode'] == 'ca_cache_update'){
	$file = fopen($g4['path'].'/cache/ca_navi_'.$_GET['ca_id'].'.htm','w');
	$contents = 'test';
	fwrite($file,$content); 
	fclose($file); 

}

include $g4['admin_path']."/admin.head.php";

# 제품관 리스트 로드 #
$st_sql = sql_query("select s_id,name from yc4_station");
while($row = sql_fetch_array($st_sql)){
	$st_tr .= "
		<tr class='".($_GET['s_id'] == $row['s_id'] ? "active":"")."' onclick=\"location.href='".$_SERVER['PHP_SELF']."?s_id=".$row['s_id']."'\">
			<td>".$row['s_id']."</td>
			<td>".$row['name']."</td>
		</tr>
	";
}


if($_GET['s_id']){
	$ca_sql = sql_query("
		select 
			b.*
		from
			shop_category a
			left join
			".$g4['yc4_category_table']." b on a.ca_id = b.ca_id
		where
			a.s_id = '".$_GET['s_id']."'
	");
	while($row = sql_fetch_array($ca_sql)){
		if(file_exists($g4['path'].'/cache/ca_navi_'.$row['ca_id'].'.htm')){
			$file_chk = "파일있음";
		}else{
			$file_chk = '-';
		}
		$ca_tr .= "
			<tr>
				<td>".$row['ca_id']."</td>
				<td>".$row['ca_name']."</td>
				<td>".$file_chk."</td>
			</tr>
		";
	}
}
?>

<style type="text/css">
.station_wrap,.cate_wrap{
float:left;
width:50%;
}
.active{
	font-weight:bold;
}
</style>

<div class='station_wrap'>
	<table>
		<tr>
			<td>제품관코드</td>
			<td>제품관</td>
		</tr>
		<?=$st_tr?>
	</table>
</div>
<div class="cate_wrap">
	<table>
		<tr>
			<td>카테고리코드</td>
			<td>카테고리명</td>
		</tr>
		<?=$ca_tr;?>
	</table>
</div>

<?
include $g4['admin_path']."/admin.tail.php";
?>
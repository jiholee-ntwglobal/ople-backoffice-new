<?
$sub_menu = "300400";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");


if(!$_GET['dt']){
	# 날짜별 히스토리 로드 #
	if($_GET['it_id']){
		$search_qry .= (($search_qry) ? " and ":" where ")."a.it_id = '".$_GET['it_id']."'";
		$view_page_param = "&it_id=".$_GET['it_id'];
	}

	if($_GET['it_name']){
		$search_qry .= (($search_qry) ? " and ":" where ")."b.it_name like '%".$_GET['it_id']."%'";
		$view_page_param = "&it_name=".$_GET['it_name'];
	}
	
	$qry = sql_query("
		select
			count(*) as cnt,
			sum( if( a.flag = 'o' , 1,0) ) as sold_out,
			sum( if( a.flag = 'i' , 1,0) ) as sold_in,
			left(a.time,10) as dt
		from
			yc4_soldout_history a
			left join
			".$g4['yc4_item_table']." b on a.it_id = b.it_id
		".$search_qry."
		group by left(a.time,10) desc
	");

	
	while($row = sql_fetch_array($qry)){
		$list_tr .= "
			<tr>
				<td>".$row['dt']."</td>
				<td>".$row['cnt']."</td>
				<td align='right'>".number_format($row['sold_out'])."</td>
				<td align='right'>".number_format($row['sold_in'])."</td>
				<td align='center'>".icon('보기',$_SERVER['PHP_SELF'].'?dt='.$row['dt'].$view_page_param)."</td>
			</tr>
		";
	}
	$colspan = 5;

	
	$list_header = "
		<tr align='center'>
			<td>날짜</td>
			<td>총 처리갯수</td>
			<td>품절</td>
			<td>품절해제</td>
			<td></td>
		</tr>
	";

	$search_form = "
		<input type='text' name='it_id' value='".$_GET['it_id']."' placeholder='상품코드'/>
		<input type='text' name='it_name' value='".$_GET['it_name']."' placeholder='상품명'/>
	";
	
}else{
	# 선택한 날짜의 히스토리 로드 #
	if($_GET['it_id']){
		$search_qry .= " and a.it_id = '".$_GET['it_id']."'";
	}
	if($_GET['it_name']){
		$search_qry .= " and b.it_name like '%".$_GET['it_name']."%'";
	}

	$qry = sql_query("
		select
			a.it_id,a.flag,a.time,a.ip,a.mb_id,
			b.it_name,b.SKU,
			c.mb_name
		from
			yc4_soldout_history a
			left join
			".$g4['yc4_item_table']." b on a.it_id = b.it_id
			left join
			".$g4['member_table']." c on a.mb_id = c.mb_id
		where
			left(a.time,10) = '".$_GET['dt']."'
			".$search_qry."
		order by a.time desc
	");
	while($row = sql_fetch_array($qry)){
		switch($row['flag']){
			case 'i' : $flag = '품절해제'; break;
			case 'o' : $flag = '품절'; break;
		}
		if($row['mb_id'] == 'AUTO') $row['mb_name'] = 'SYSTEM AUTO';
		$list_tr .= "
			<tr>
				<td>".$row['it_id']."</td>
				<td>".$row['SKU']."</td>
				<td>".$row['it_name']."</td>
				<td>".$flag."</td>
				<td>".$row['time']."</td>
				<td>".($row['mb_name'] ? $row['mb_name'] : $row['mb_id'])."</td>
				<td>".$row['ip']."</td>
			</tr>
		";
	}
	$colspan = '5';
	$cols = "
		<col width='80'/>
		<col width='90'/>
		<col width=''/>
		<col width='60'/>
		<col width='80'/>
		<col width='80'/>
		<col width='90'/>
	";

	$list_header = "
		<tr>
			<td>상품코드</td>
			<td>UPC</td>
			<td>상품명</td>
			<td>처리</td>
			<td>처리시간</td>
			<td>처리자</td>
			<td>IP주소</td>
		</tr>
	";

	$search_form = "
		<input type='text' name='it_id' value='".$_GET['it_id']."' placeholder='상품코드'/>
		<input type='text' name='it_name' value='".$_GET['it_name']."' placeholder='상품명'/>
		<input type='checkbox' name='dt' value='".$_GET['dt']."'/>현재 날짜 내 검색
	";

	$list_btn .= "
		<input type='button' value='목록' onclick=\"location.href='".$_SERVER['PHP_SELF']."?it_id=".$_GET['it_id']."&it_name=".$_GET['it_name']."'\"/>
	";
}


if(!$list_tr){
	$list_tr = "
		<tr><td colspan='".$colspan."' align='center'>데이터가 존재하지 않습니다.</td></tr>
	";
}



$g4[title] = "상품품절 히스토리";
include_once ("$g4[admin_path]/admin.head.php");
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method='get'>
	<?=$search_form?>
	<input type="submit" value='검색'/>
	<?if($_GET['it_id'] || $_GET['it_name']){?>
	<input type="button" value='검색초기화' onclick="location.href='<?=$_SERVER['PHP_SELF'].(($_GET['dt']) ? "?dt=".$_GET['dt']:"")?>'" />
	<?}?>
</form>
<table width='100%' border='1' style='border-collapse: collapse;' cellpadding='5'>
	<?=$cols;?>
	<thead>
		<?=$list_header;?>
	</thead>
	<tbody>
		<?=$list_tr;?>
	</tbody>
</table>
<?=$list_btn;?>



<? include_once ("$g4[admin_path]/admin.tail.php"); ?>
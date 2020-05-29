<?php
/*
	list_clearance_item.php
	목록통관 상품 관리
	2015-03-10
	ghdalslrdi@ntwglobal.com
*/




$sub_menu = "300200";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");


if($_POST['mode']){
	
	if($_POST['mode'] == 'update'){
		$sql = "update yc4_item set list_clearance = 'Y' where it_id = '".$_POST['it_id']."'";
		$msg = "목록 통관 상품 지정이 완료되었습니다.";
	}else{
		$sql = "update yc4_item set list_clearance = null where it_id = '".$_POST['it_id']."'";
		$msg = "목록 통관 상품 해제가 완료되었습니다.";
	}
	if(sql_query($sql)){
		alert($msg,$_SERVER['PHP_SELF']."?".$_POST['qstr']);
	}else{
		alert('처리중 오류 발생 ! 관리자에게 문의하세요.');
	}
	exit;
}

$sql_search = '';

switch($_GET['fg']){
	case 'ALL' : break;
	case 'N' : $sql_search .= ($sql_search ? " and ":" where "). "list_clearance is null"; break;
	case 'Y' : default : 
		$sql_search .= ($sql_search ? " and ":" where "). "list_clearance = 'Y'"; 
		$_GET['fg'] = 'Y';
		break;
}

switch($_GET['it_use']){
	case 'ALL' : break;
	case 'N' : $sql_search .= ($sql_search ? " and ":" where "). "it_use = 0"; break;
	case 'Y' : default : 
		$sql_search .= ($sql_search ? " and ":" where "). "it_use = 1"; 
		$_GET['it_use'] = 'Y';
		break;
}

if($_GET['it_id']){
	$sql_search .= ($sql_search ? " and ":" where "). "it_id = '" .mysql_real_escape_string($_GET['it_id']). "'";
}

if($_GET['it_maker']){
	$sql_search .= ($sql_search ? " and ":" where "). "it_maker = '".mysql_real_escape_string($_GET['it_maker'])."'";
}

if($_GET['it_name']){
	$sql_search .= ($sql_search ? " and ":" where "). "it_name like '%".mysql_real_escape_string($_GET['it_name'])."%'";
}

$station_info_sql = sql_query("
	select
		s_id,name
	from
		yc4_station
	where s_id between 1 and 5
	order by sort
");
$st_option = "<option value=''>전체</option>";
while($row = sql_fetch_array($station_info_sql)){
	$st_option .= "<option value='".$row['s_id']."' ".($_GET['search_s_id'] == $row['s_id'] ? "selected":"").">".$row['name']."</option>";
}

if($_GET['search_s_id']){
	$ca_sql = "
		select 
			distinct it_id
		from 
			shop_category a
			left join
			yc4_category_item b on a.ca_id = left(b.ca_id,2)
		where
			s_id = '".$_GET['search_s_id']."'
	";
	if($_GET['ca_id']){
		$ca_len = strlen($_GET['ca_id']);
		$ca_sql .= " and left(b.ca_id,".(int)$ca_len.") = '".$_GET['ca_id']."'";

		$ca_depth = $ca_len/2;


		# 카테고리 트리 로드 #
		$category_select = '';
		for($i=1; $i<=$ca_depth; $i++){

			$l_ca_id = substr($_GET['ca_id'],0,($i*2));
			
			
			$tmp_sql = sql_query($a="
				select 
					distinct a.ca_id,a.ca_name,length(a.ca_id) as len
				from 
					".$g4['yc4_category_table']." a, 
					shop_category b 
				where 
					left(a.ca_id,2) = b.ca_id
					and
					b.s_id = '".$_GET['search_s_id']."'
					and length(a.ca_id) <= ".(int)(($i*2))."
				order by a.ca_id
				");
			$ca_arr = array();
			while($row = sql_fetch_array($tmp_sql)){
				$ca_arr[$row['len']][] = array('ca_id'=>$row['ca_id'],'ca_name'=>$row['ca_name']);
			}

			
			
			
			
		}

		foreach($ca_arr as $len => $ca_data){
			$category_select .= "<select name='ca_id' depth='".(int)($len)."'>";
			$category_select .= "<option value=''>전체</option>";
			foreach($ca_data as $key => $val){
				$category_select .= "<option value='".$val['ca_id']."' ".(substr($_GET['ca_id'],0,strlen($val['ca_id'])) == $val['ca_id'] ? "selected":"").">".$val['ca_name']."</option>";
			}
			$category_select .= "</select>";
		}
		# 하위 카테고리 로드 #
		$tmp_sql = sql_query($a="
			select distinct ca_id,ca_name
			from ".$g4['yc4_category_table']."
			where 
				ca_id like '".$_GET['ca_id']."%'
				and length(ca_id) = ".(int)(($i*2))."
			order by ca_id
		");

		if(mysql_num_rows($tmp_sql)>0){
			$category_select .= "<select name='ca_id' depth='".(int)(($i*2))."'>";
			$category_select .= "<option value=''>전체</option>";
		}
		while($row = sql_fetch_array($tmp_sql)){
			

			$category_select .= "<option value='".$row['ca_id']."' ".(substr($_GET['ca_id'],0,strlen($row['ca_id'])) == $row['ca_id'] ? "selected":"").">".$row['ca_name']."</option>";

			
		}
		if(mysql_num_rows($tmp_sql)>0){
			$category_select .= "</select>";
		}

	}else{
		$tmp_sql = sql_query("
			select 
				distinct a.ca_id,a.ca_name 
			from 
				".$g4['yc4_category_table']." a, 
				shop_category b 
			where 
				a.ca_id = b.ca_id
				and
				b.s_id = '".$_GET['search_s_id']."' and length(a.ca_id) = 2 
			order by a.ca_id
		");
		$category_select .= "<select name='ca_id'>";
		$category_select .= "<option value=''>전체</option>";
		while($row = sql_fetch_array($tmp_sql)){
			$category_select .= "<option value='".$row['ca_id']."'>".$row['ca_name']."</option>";
		}
		$category_select .= "</select>";
	}

	$ca_sql = sql_query($ca_sql);
		

	$it_id_in = '';
	while($row = sql_fetch_array($ca_sql)){
		$it_id_in .= ($it_id_in ? ",":"")."'".$row['it_id']."'";
	}
	if($it_id_in){
		$sql_search .= ($sql_search ? " and ":" where "). "it_id in (".$it_id_in.")";
	}
}


// 테이블의 전체 레코드수만 얻음
$sql = "select count(*) as cnt  from ".$g4['yc4_item_table']. $sql_search;

$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$sql = sql_query("
	select
		it_id,it_name,it_amount,it_use,it_tel_inq,list_clearance
	from
		".$g4['yc4_item_table']."
	".$sql_search."
	order by it_id
	limit $from_record, $rows
");
$list_tr = '';
while($row = sql_fetch_array($sql)){


	if($row['list_clearance'] == 'Y'){
		$list_clearance_btn = "<input type='button' value='->일반통관' onclick=\"frm_submit('".$row['it_id']."','delete')\" />";

	}else{
		$list_clearance_btn = "<input type='button' value='->목록통관' onclick=\"frm_submit('".$row['it_id']."','update')\" />";
	}

	
	$list_tr .= "
		<tr>
			<td>".$row['it_id']."</td>
			<td>".get_item_name($row['it_name'])."</td>
			<td>".number_format(get_amount($row))."</td>
			<td>
				".$list_clearance_btn."
			</td>
		</tr>
	";
}


$qstr = $_GET;
unset($qstr['page']);
$qstr = http_build_query($qstr);

$qstr2 = $_GET;
unset($qstr2['fg']);
$qstr2 = http_build_query($qstr2);

$qstr3 = $_GET;
unset($qstr3['fg']);
$qstr3 = http_build_query($qstr3);

$qstr_all = $_GET;
$qstr_all = http_build_query($qstr_all);

$g4[title] = "목록통관 상품 관리";
include_once ("$g4[admin_path]/admin.head.php");


?>
<style type="text/css">
.tab_wrap{
	overflow:hidden;
}
.tab_wrap > ul{
	overflow:hidden;
	list-style:none;
}
.tab_wrap > ul > li{
	float:left;
	border:1px solid #dddddd;
	padding:5px;
}
.tab_wrap > ul > li.active{
	font-weight:bold;
}

.list_table{
	border-collapse: collapse;
	width : 100%;
}
.list_table td {
	padding : 5px;
}
</style>


<form action="<?php echo $_SERVER['PHP_SELF']?>" method='get'>
	상품코드
	<input type="text" name='it_id' value="<?php echo $_GET['it_id'];?>"/>
	브랜드명
	<input type="text" name='it_maker' value="<?php echo $_GET['it_maker'];?>"/>
	상품명
	<input type="text" name='it_name' value="<?php echo $_GET['it_name'];?>"/>	
	<br />
	<select name="search_s_id">
		<?php echo $st_option;?>
	</select>

	<?php echo $category_select;?>
	<input type="submit" value='검색' />

</form>

<div class='tab_wrap'>
	<ul style='float:left;'>
		<li <?=$_GET['fg'] == 'Y' ? "class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=Y">목록통관</a></li>
		<li <?=$_GET['fg'] == 'N' ? "class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=N">일반통관</a></li>
		<li <?=$_GET['fg'] == 'ALL' ? "class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&fg=ALL">전체</a></li>
	</ul>
	<ul style='float:right;'>
		<li <?=$_GET['it_use'] == 'Y' ? "class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&it_use=Y">판매</a></li>
		<li <?=$_GET['it_use'] == 'N' ? "class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&it_use=N">미판매</a></li>
		<li <?=$_GET['it_use'] == 'ALL' ? "class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo $qstr2;?>&it_use=ALL">전체</a></li>
	</ul>
</div>
<table border='1' class='list_table'>
	<thead>
		<tr>
			<td>상품코드</td>
			<td>상품명</td>
			<td>판매가</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		<?php echo $list_tr;?>
	</tbody>
</table>

<p align='center'>
	<?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?>
</p>

<form action="<?php echo $_SERVER['PHP_SELF']?>" method='post' name='submit_frm'>
	<input type="hidden" name='qstr' value="<?php echo $qstr_all?>" />
	<input type="hidden" name='it_id' />
	<input type="hidden" name='mode' />
</form>

<script type="text/javascript">
$('select[name=ca_id][depth]').change(function(){
	var depth = Number($(this).attr('depth'));
	var cnt = $('select[name=ca_id][depth]').length;

	for(var i=0; i<cnt; i++){

		if(Number($('select[name=ca_id][depth]:eq('+i+')').attr('depth')) > depth ){

			$('select[name=ca_id][depth]:eq('+i+')').addClass('remove');
		}
	}
	$('.remove').remove();
});

function frm_submit(it_id,mode){
	var msg;
	switch(mode){
		case 'update' : 
			msg = "해당 상품을 목록통관 상품으로 등록하시겠습니까?";
			break;
		case 'delte' : 
			msg = "해당 상품을 목록통관 상품에서 해제하시겠습니까?";
			break;
	}

	if(!confirm(msg)){
		return false;
	}
	submit_frm.it_id.value = it_id;
	submit_frm.mode.value = mode;

	submit_frm.submit();

}

</script>

<?php
include_once ("$g4[admin_path]/admin.tail.php");
?>

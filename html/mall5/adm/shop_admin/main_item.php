<?php
/*
----------------------------------------------------------------------
file name	 : main_item.php
comment		 : 메인 상품 진열 관리
date		 : 2015-01-16
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "600200";
include "_common.php";
auth_check($auth[$sub_menu], "r");


# 메인 진열상품 삭제처리 #
if($_POST['mode'] == 'delete'){
	$data = sql_fetch("select en_dt,m_type from yc4_main_item where uid = '".$_POST['uid']."'");

		$sql = "
			update
				yc4_main_item
			set
				en_dt = '".second_minus($data['en_dt'])."'
			where
				uid != '".$_POST['uid']."'
				and
				m_type = '".$data['m_type']."'
				and
				en_dt <= '".$data['en_dt']."'
			order by en_dt desc
			limit 1
		";

		sql_query($sql);

	$sql = "delete from yc4_main_item where uid = '".$_POST['uid']."'";

	if(!sql_query($sql)){
		alert('처리중 오류 발생! 관리자에게 문의하세요.');
		exit;
	}

	alert('삭제가 완료되었습니다.',$_SERVER['PHP_SELF'].'?'.$_POST['qstr']);
	exit;
}

array_walk($_GET,'trim');

$search_sql = '';
if($_GET['m_type']){
	$search_sql .= " and a.m_type = '".mysql_real_escape_string($_GET['m_type'])."'";
}
if($_GET['it_id']){
	$search_sql .= " and b.it_id = '".mysql_real_escape_string($_GET['it_id'])."'";
}
if($_GET['it_name']){
	$search_sql .= " and b.it_name like '%".mysql_real_escape_string($_GET['it_name'])."%'";
}
if($_GET['it_maker']){
	$search_sql .= " and b.it_maker like '%".mysql_real_escape_string($_GET['it_maker'])."%'";
}

switch($_GET['fg']){
	case 'Y' :
		$search_sql .= " and '".$g4['time_ymdhis']."' between st_dt and en_dt";
		break;
	case 'N' :
		$search_sql .= " and '".$g4['time_ymdhis']."' not between st_dt and en_dt";
		break;
}




$sql = sql_query("
	select
		a.*,
		if(a.st_dt <='".$g4['time_ymdhis']."' ,'y',null) as del_fg,
		b.it_name,
		b.it_amount
	from
		yc4_main_item a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		".$search_sql."
	order by st_dt desc
");
$list_tr = '';

$m_type_arr = array(
	'H' => 'HOT',
	'N' => 'NEW',
	'B' => 'BEST',
	'M' => '만원의행복'
);



while($row = sql_fetch_array($sql)){
	$img_link = '';
	if($row['img_link']){
		$img_link = "<img src='".$row['img_link']."'/><br/>";
	}
	if(!$row['del_fg']){
		$del_icon = icon('삭제','#','',"onclick=\"main_item_del('".$row['uid']."'); return false;\"");
	}else{
		$del_icon = '';
	}
	$list_tr .= "
		<tr>
			<td>".$m_type_arr[$row['m_type']]."</td>
			<td>".$row['st_dt']." ~ ".$row['en_dt']."</td>
			<td>".$row['it_id']."</td>
			<td>".get_item_name($row['it_name'],'korshort')."</td>
			<td>".$img_link.$row['img_link']."</td>
			<td>".icon('수정',$g4['shop_admin_path'].'/main_item_write.php?uid='.$row['uid'])." ".$del_icon."</td>
		</tr>
	";
}
if(!$list_tr){
	$list_tr = "
		<tr align='center'>
			<td colspan='5'>데이터가 존재하지 않습니다.</td>
		</tr>
	";
}

$qstr = $qstr2 = $qstr3 = $_GET;
unset($qstr['page'],$qstr['m_type']);
unset($qstr2['page'],$qstr2['fg']);
$qstr = http_build_query($qstr);
$qstr2 = http_build_query($qstr2);
$qstr3 = http_build_query($qstr3);


include_once $g4['admin_path']."/admin.head.php";





?>
<style type="text/css">
.admin_table_tab > ul{
	list-style:none;
}
.admin_table_tab > ul > li {
	float:left;
	padding:5px;
	border:1px solid #dddddd;
}
.admin_table_tab > ul > li.active{
	font-weight:bold;
}
</style>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='get'>
	제품코드 <input type="text" name='it_id' value="<?php echo $_GET['it_id']?>"/>
	제품명 <input type="text" name='it_name' value="<?php echo $_GET['it_name']?>"/>
	브랜드명 <input type="text" name='it_maker' value="<?php echo $_GET['it_maker']?>"/>
	<input type="submit" value='검색' />
</form>

<div class='admin_table_tab' style='overflow:hidden;'>
	<ul style='float:left;'>
		<li<?php echo !$_GET['m_type'] ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr;?>">전체</a></li>
		<li<?php echo $_GET['m_type'] == 'H' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr."&m_type=H";?>">HOT</a></li>
		<li<?php echo $_GET['m_type'] == 'N' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr."&m_type=N";?>">NEW</a></li>
		<li<?php echo $_GET['m_type'] == 'B' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr."&m_type=B";?>">BEST</a></li>
		<li<?php echo $_GET['m_type'] == 'M' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr."&m_type=M";?>">만원의행복</a></li>
	</ul>
	<ul style='float:right;'>
		<li<?php echo !$_GET['fg'] ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr2;?>">전체</a></li>
		<li<?php echo $_GET['fg'] == 'Y' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr2."&fg=Y";?>">진행</a></li>
		<li<?php echo $_GET['fg'] == 'N' ? " class='active'":"";?>><a href="<?php echo $_SERVER['PHP_SELF']."?".$qstr2."&fg=N";?>">종료</a></li>
	</ul>
</div>
<table width='100%'>
	<col width='40'/>
	<col width='250'/>
	<col width='75'/>
	<col width=''/>
	<col width='150'/>
	<col width=''/>
	<tr>
		<td>구분</td>
		<td>기간</td>
		<td>상품코드</td>
		<td>상품명</td>
		<td>작업이미지</td>
		<td><?php echo icon('입력',$g4['shop_admin_path'].'/main_item_write.php');?></td>
	</tr>
	<?php echo $list_tr;?>
</table>


<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='post' name='del_frm'>
	<input type="hidden" name='mode' value='delete' />
	<input type="hidden" name='uid' value='' />
	<input type="hidden" name='qstr' value='<?php echo $qstr3;?>' />
</form>

<script type="text/javascript">
function main_item_del(uid){
	if(!confirm('해당 상품을 메인 진열에서 해제하시겠습니까?')){
		return false;
	}

	del_frm.uid.value = uid;
	del_frm.submit();
}
</script>

<?php
include_once $g4['admin_path']."/admin.tail.php";
<?php
$sub_menu = "300160";
include_once("./_common.php");
include $g4['full_path'].'/lib/db.php';

$g4[title] = "관련상품";

include_once ("$g4[admin_path]/admin.head.php");

$pdo		= new db();
$ople		= $pdo->ople_db_pdo;
$ntics		= $pdo->ntics_db;
// 데이터 로드

$src_name	= isset($_GET['src_name']) ? $_GET['src_name'] : "";
$src_val	= isset($_GET['src_val']) ? mysql_real_escape_string($_GET['src_val']) : "";
$list_tab	= isset($_GET['list_tab']) ? $_GET['list_tab'] : "all";
$where		= "";
$qstr		= "src_name=".$src_name."&src_val=".$src_val;
if($src_name!="" && $src_val!=""){
	if($src_name=="it_id"){
		if(preg_match("/([^0-9\.])/",$src_val)){
			echo "
				<script type=text/JavaScript>
					alert('상품코드검색은 숫자만 가능합니다.');
					history.back();
				</script>
			";
			exit;
		}else{
			$where	= "WHERE a.it_id='".$src_val."'";
		}
	}elseif($src_name=="upc"){
		$where		= "WHERE m.upc='".$src_val."'";
	}elseif($src_name=="it_name"){
		$where		= "WHERE b.it_name like '%".$src_val."%'";
	}elseif($src_name=="it_maker"){
		$where		= "WHERE b.it_maker like '%".$src_val."%'";
	}else{
		echo "
			<script type=text/JavaScript>
				alert('검색영역이 올바르지 않습니다.');
				history.back();
			</script>
		";
		exit;
	}
}

$page		= isset($_GET['page']) ? $_GET['page'] : "1";
$num_row	= 10;
$st_rec		= ($page-1)*$num_row;

$cnt_sql	= $ople->query("SELECT COUNT(DISTINCT it_id) AS cnt FROM yc4_item_relation");
$total_cnt	= $cnt_sql->fetchColumn();
$total_page	= ceil($total_cnt/$num_row);
$sql	= $ople->prepare("SELECT a.it_id, b.it_name, b.it_use, b.it_amount, b.it_amount_usd, m.upc, SUM(1) AS cnt FROM yc4_item_relation a LEFT JOIN yc4_item b ON a.it_id=b.it_id LEFT JOIN ople_mapping m ON m.it_id=a.it_id ".$where." GROUP BY it_id  limit ".$st_rec." , ".$num_row);
$sql->execute();
$list	= $sql->fetchAll(PDO::FETCH_ASSOC);
$upc_in	= "";
foreach($list as $val){
	if($val['upc']!=''){
		$upc_in	.= "'".$val['upc']."', ";
	}
}
$upc_in	= substr($upc_in,0,-2); 
$ms_sql	= $ntics->query("SELECT upc,wholesale_price,exept_explain FROM N_MASTER_ITEM_WHOLESALE_PRICE WHERE isnow='Y' AND upc in (".$upc_in.")");
$wholesale	= array();
while($row = $ms_sql->fetchALL()){
	$whole_list	= $row;
}
foreach($whole_list as $val){
	if($val['wholesale_price']==''){
		$wholesale[$val['upc']]	= $val['exept_explain'];
	}else{
		$wholesale[$val['upc']]	= $val['wholesale_price'];
	}
}
$item_list	= "";
if(count($list)>0){
	foreach($list as $val){
		$ws_amount	= '';
		$op_amount	= $val['it_amount_usd']=='' ? number_format($val['it_amount']/$default['de_conv_pay'],2) : $val['it_amount_usd'];
		$item_list	.= "
		<tr>
			<td><img src='http://115.68.20.84/item/".$val['it_id']."_s' width='50px' hight='50px' /></td>
			<td>
				".$val['it_id']."<br /><br />
				".$val['upc']."
			</td>
			<td><a href='http://ople.com/mall5/shop/item.php?it_id=".$val['it_id']."' target='blank'>".get_item_name($val['it_name'],'list')."</a></td>
			<td>".$wholesale[$val['upc']]."<br /><br />(".$op_amount.")</td>
			<td>".$val['cnt']."</td>
			<td><button class='btn btn-default' onclick='location.href=\"./item_relation_write.php?main_id=".$val['it_id']."\"'>수정</button></td>
		</tr>
		";
	}
}else{
	$item_list	= "<tr><td colspan='5'>상품정보가 없습니다.</td></tr>";
}

?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<form name='src_form' method='GET' action='item_relation_list.php'>
	<div class='form-group'>
		<div class='col-lg-2'>
			<select class='form-control' name='src_name'>
				<option value='' disabled selected>검색영역</option>
				<option value='it_id'>IT_ID</option>
				<option value='upc'>UPC</option>
				<option value='it_name'>상품명</option>
				<option value='it_maker'>제조사</option>
			</select>
		</div>
		<div class='col-lg-7'>
			<input class='form-control' type='text' name='src_val' value='' placeholder='search for...' />
		</div>
		<div class='col-lg-3'>
			<button class='btn btn-primary' type='submit'>검색</button>
			<button class='btn btn-primary' type='button' onclick='location.href="item_relation_list.php"'>초기화</button>
		</div>
	</div>
</form>
<div class="container-fluid">
<!--	<div class="row">
		<div class="col-lg-12 text-right">
			<ul class="nav nav-tabs">
			  <li role="presentation" id="tab_all" class='active'><a href="#" onclick="">전체상품</a></li>
			  <li role="presentation" id="tab_related" class=''><a href="#" onclick="">관련상품 등록된상품<span class="badge">4</span></a></li>
			  <li role="presentation" id="tab_related_no" class=''><a href="#" onclick="">관련상품 미등록된 상품<span class="badge">4</span></a></li>
			</ul>
		</div>
	</div> -->
	<div class='row'>
		<div class='col-lg-12'>
			<div class='row'>
				<table class='table'>
					<tr>
	    				<td align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
					</tr>
				</table>
			</div>
			<div class='row'>
				<table class='table table-striped table-hover table-bordered' >
					<tr>
						<th class='text-center'>이미지</th>
						<th class='text-center'>상품코드<br />UPC</th>
						<th class='text-center'>상품명</th>
						<th class='text-center'>입고가<br />(가격)</th>
						<th class='text-center'>관련상품수</th>
						<th class='text-center'><button class='btn btn-success' onclick='location.href="./item_relation_write.php"'>추가</button></th>
					</tr>
					<?php echo  $item_list; ?>
				</table>
			</div>
			<div class='row'>
				<table class='table'>
					<tr>
					    <td width=100% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$('li[id]').each(function() {
		$('li[id^=tab]').attr('class','');
		$('li[id$=<?php echo $list_tab; ?>]').attr('class','active');
	});
	
	$('form[name=src_form]').submit(function(){
		if($('form[name=src_form] option:selected').val()==''){
			alert('검색영역을 선택하여 주세요');
			return false;
		}
		if($('input[name=src_val]').val().trim()==''){
			alert('검색어를 입력하여 주세요');
			return false;
		}
		
		return true;
	})
</script>
<?php

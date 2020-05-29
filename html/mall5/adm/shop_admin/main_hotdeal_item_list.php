<?php
/*
----------------------------------------------------------------------
file name	 : main_hotdeal_item_list.php
comment		 : 메인 핫딜존 관리
date		 : 2015-01-23
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "600300";
include "_common.php";
auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'update'){
	array_walk($_POST,'trim');
	array_walk($_POST,'mysql_real_escape_string');

	if(is_array($_POST['uid'])){

		foreach($_POST['uid'] as $sort => $uid){
			$sql = "
				update
					yc4_hotdeal_item
				set
					sort = '".$sort."'
				where
					uid = '".$uid."'
			";
			sql_query($sql);
		}
	}

	if(is_array($_POST['no_uid'])){

		foreach($_POST['no_uid'] as $uid){
			$sql = "
				update
					yc4_hotdeal_item
				set
					sort = '0'
				where
					uid = '".$uid."'
			";
			sql_query($sql);
		}

	}

	alert("저장이 완료되었습니다.",$_SERVER['PHP_SELF']);
	exit;
}

$list_tr = $list_tr2 = '';

# 현재 진행중인 상품리스트 로드 #
$sql = sql_query("
	select
		a.*,
		b.it_name
	from
		yc4_hotdeal_item a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		and a.flag = 'Y'
		and a.sort > 0
	order by a.sort asc
");

//if($member['mb_id']=='dev' || $member['mb_id']=='ople_mrs'){

while($row = sql_fetch_array($sql)){
	$msrp_krw = $row['it_amount_msrp'] * $default['de_conv_pay'];
	$list_tr .= "
		<li>
			<input type='hidden' class='uid' name='uid[".$row['sort']."]' value='".$row['uid']."'/>
			<table width='100%'>
				<tr>
					<td width='160'><img src='".$row['img_link']."' width='160'/></td>
					<td>
						<p>상품코드 : ".$row['it_id']."</p>
						<br />
						<p>".get_item_name($row['it_name'],'list')."</p>
						<br />
						<p><b>이벤트가 : ￦".number_format($row['it_event_amount'])." ($".$row['it_event_amount_usd'].")</b></p>
						<p>MSRP : ￦".number_format($msrp_krw)." ($".$row['it_amount_msrp'].")</p>
						<p>할인율 : ".(get_dc_percent($row['it_event_amount'],$msrp_krw))."%</p>
						<p>판매수량 / 이벤트수량 : ".$row['sell_qty']." / ".$row['qty']."</p>
					</td>
				</tr>
			</table>

		</li>
	";
}

# 진행중 인데 순서가 정해지지 않은 상품리스트 로드 #
$sql = sql_query("
	select
		a.*,
		b.it_name
	from
		yc4_hotdeal_item a,
		".$g4['yc4_item_table']." b
	where
		a.it_id = b.it_id
		and a.flag = 'Y'
		and a.sort = 0

");

$i = 1;
while($row = sql_fetch_array($sql)){
	$msrp_krw = $row['it_amount_msrp'] * $default['de_conv_pay'];
	$list_tr2 .= "
		<li>
			<input type='hidden' class='uid' name='no_uid[".$i."]' value='".$row['uid']."'/>
			<table width='100%'>
				<tr>
					<td width='160'><img src='".$row['img_link']."' width='160'/></td>
					<td>
						<p>상품코드 : ".$row['it_id']."</p>
						<br />
						<p>".get_item_name($row['it_name'],'list')."</p>
						<br />
						<p><b>이벤트가 : ￦".number_format($row['it_event_amount'])." ($".$row['it_event_amount_usd'].")</b></p>
						<p>MSRP : ￦".number_format($msrp_krw)." ($".$row['it_amount_msrp'].")</p>
						<p>할인율 : ".(get_dc_percent($row['it_event_amount'],$msrp_krw))."%</p>
						<p>판매수량 / 이벤트수량 : ".$row['sell_qty']." / ".$row['qty']."</p>
					</td>
				</tr>
			</table>
		</li>
	";
	$i++;
}

//}else{
//
//while($row = sql_fetch_array($sql)){
//	$msrp_krw = $row['it_amount_msrp'] * $default['de_conv_pay'];
//	$list_tr .= "
//		<li>
//			<input type='hidden' class='uid' name='uid[".$row['sort']."]' value='".$row['uid']."'/>
//			<table width='100%'>
//				<tr>
//					<td width='160'><img src='".$row['img_link']."' width='160'/></td>
//					<td>
//						<p>상품코드 : ".$row['it_id']."</p>
//						<br />
//						<p>".get_item_name($row['it_name'],'list')."</p>
//						<br />
//						<p><b>이벤트가 : ￦".number_format($row['it_event_amount'])." ($".usd_convert($row['it_event_amount']).")</b></p>
//						<p>MSRP : ￦".number_format($msrp_krw)." ($".$row['it_amount_msrp'].")</p>
//						<p>할인율 : ".(get_dc_percent($row['it_event_amount'],$msrp_krw))."%</p>
//						<p>판매수량 / 이벤트수량 : ".$row['sell_qty']." / ".$row['qty']."</p>
//					</td>
//				</tr>
//			</table>
//		</li>
//	";
//}
//
//# 진행중 인데 순서가 정해지지 않은 상품리스트 로드 #
//$sql = sql_query("
//	select
//		a.*,
//		b.it_name
//	from
//		yc4_hotdeal_item a,
//		".$g4['yc4_item_table']." b
//	where
//		a.it_id = b.it_id
//		and a.flag = 'Y'
//		and a.sort = 0
//
//");
//
//$i = 1;
//while($row = sql_fetch_array($sql)){
//	$msrp_krw = $row['it_amount_msrp'] * $default['de_conv_pay'];
//	$list_tr2 .= "
//		<li>
//			<input type='hidden' class='uid' name='no_uid[".$i."]' value='".$row['uid']."'/>
//			<table width='100%'>
//				<tr>
//					<td width='160'><img src='".$row['img_link']."' width='160'/></td>
//					<td>
//						<p>상품코드 : ".$row['it_id']."</p>
//						<br />
//						<p>".get_item_name($row['it_name'],'list')."</p>
//						<br />
//						<p><b>이벤트가 : ￦".number_format($row['it_event_amount'])." ($".usd_convert($row['it_event_amount']).")</b></p>
//						<p>MSRP : ￦".number_format($msrp_krw)." ($".$row['it_amount_msrp'].")</p>
//						<p>할인율 : ".(get_dc_percent($row['it_event_amount'],$msrp_krw))."%</p>
//						<p>판매수량 / 이벤트수량 : ".$row['sell_qty']." / ".$row['qty']."</p>
//					</td>
//				</tr>
//			</table>
//		</li>
//	";
//	$i++;
//}
//
//}


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

.droptrue,.dropfalse{
	float:left;
	width:45%;
	min-height:400px;
}

.droptrue > li,.dropfalse > li {
	border: 1px solid #dddddd;
}

</style>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>

<div class='admin_table_tab' style='overflow:hidden;'>
	<ul style='float:right;'>
		<li><a href="<?php echo $g4['shop_admin_path'];?>/main_hotdeal_item.php">상품관리</a></li>
		<li class='active'><a href="#">진행 상품관리</a></li>
	</ul>
</div>

<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='post'>
	<input type="hidden" name='mode' value='update'/>
	<div style='float:left; width:45%;'>미진열상품</div>
	<div style='float:left; width:10%;'>&nbsp;</div>
	<div style='float:left; width:45%;'>진열상품</div>
	<ul id='sortable1' class='droptrue'>
		<?php echo $list_tr2;?>
	</ul>
	<div style='float:left; width:10%; height:400px;'></div>
	<ul id='sortable2' class='dropfalse'>
		<?php echo $list_tr;?>
	</ul>
	<p style='clear:both;' align='center'><input type="submit" value='저장' /></p>
</form>


<script type="text/javascript">
$(function() {
	$( "ul.droptrue" ).sortable({
		connectWith: "ul",
		update : function (event,ui) {
			sorting_fnc();
		}
	});

	$( "ul.dropfalse" ).sortable({
		connectWith: "ul",

		//dropOnEmpty: false, // 여길로 못들어오게
		update : function (event,ui) {
			sorting_fnc();
		}

	});

	$( "#sortable1, #sortable2" ).disableSelection();
});

function sorting_fnc(){
	for(var i=0; i<$('#sortable1 > li').length; i++){
		var sort = i+1;

		$('#sortable1 > li:eq('+i+') > .uid').attr('name','no_uid['+sort+']');
	}

	for(var i=0; i<$('#sortable2 > li').length; i++){
		var sort = i+1;

		$('#sortable2 > li:eq('+i+') > .uid').attr('name','uid['+sort+']');
	}
}
</script>
<?php
include_once $g4['admin_path']."/admin.tail.php";
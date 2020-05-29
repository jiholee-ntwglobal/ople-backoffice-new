<?
$sub_menu = "500400";

include_once("./_common.php");

# insert 처리 시작 #
if($_POST['mode'] == 'insert'){
	
	/*
	# 상품 총 판매 수량 계산 #
	$qry = sql_query("
		select 
			sum(ct_qty) as ct_qty
		from
			yc4_cart
		where
			it_id = '".$_POST['it_id']."'
			and
			ct_status in ('주문','배송','완료')
	");
	$result = sql_fetch_array($qry);
	$order_cnt = $result['ct_qty'];
	*/
	
	$_POST['memo'] = mysql_escape_string($_POST['memo']);
	if($_POST['end_flag']){
		$end_flag = 'Y';
	}else{
		$end_flag = 'N';
	}

	$qry = "
		insert into 
			yc4_oneday_sale_item
		(
			it_id, order_cnt, st_dt, en_dt, 
			memo, create_id, create_dt,
			real_qty,l_it_id,multiplication,
			price,end_flag,goodday_fg
		)values(
			'".$_POST['it_id']."','0','".$_POST['st_dt']."','".$_POST['en_dt']."',
			'".$_POST['memo']."','".$member['mb_id']."',now(),
			'".$_POST['real_qty']."','".$_POST['l_it_id']."','".$_POST['multiplication']."',
			'".$_POST['price']."','".$end_flag."','".$_POST['goodday_fg']."'
		)
	";


	if(sql_query($qry)){
		$uid = mysql_insert_id();
		$msg = '저장이 완료되었습니다.';
		$url = $_SERVER['PHP_SELF'].'?uid='.$uid;
	}else{
		$msg = '저장중 오류가 발생했습니다. 다시 시도해 주세요.';
	}


	alert($msg,$url);
	exit;
}
# insert 처리 끝 #

# update 처리 시작 #
if($_POST['mode'] == 'update'){
	/*
	# 상품 총 판매 수량 계산 #
	$qry = sql_query("
		select 
			sum(ct_qty) as ct_qty
		from
			yc4_cart
		where
			it_id = '".$_POST['it_id']."'
			and
			ct_status in ('주문','배송','완료')
	");
	$result = sql_fetch_array($qry);
	$order_cnt = $result['ct_qty'];
	*/

	if($_POST['end_flag']){
		$end_flag = 'Y';
	}else{
		$end_flag = 'N';
	}
	
	$_POST['memo'] = mysql_escape_string($_POST['memo']);
	$qry = "
		update
			yc4_oneday_sale_item
		set
			st_dt = '".$_POST['st_dt']."', 
			en_dt = '".$_POST['en_dt']."', 
			memo = '".$_POST['memo']."', 
			update_id = '".$member['mb_id']."',
			update_dt  = now(),
			real_qty = '".$_POST['real_qty']."',
			l_it_id = '".$_POST['l_it_id']."',
			multiplication = '".$_POST['multiplication']."',
			end_flag = '".$end_flag."',
			price = '".$_POST['price']."',
			goodday_fg = '".$_POST['goodday_fg']."'
		where
			uid = '".$_POST['uid']."'
			
	";
	if(sql_query($qry)){
		$msg = '수정이 완료되었습니다.';
		$url = $_SERVER['PHP_SELF'].'?uid='.$_POST['uid'];
	}else{
		$msg = '저장중 오류가 발생했습니다. 다시 시도해 주세요.';
	}


	alert($msg,$url);


	exit;
}
# update 처리 끝 #

# 상품검색 ajax xml 출력 시작 #
if($_POST['mode'] == 'item_search'){
	if($_POST['it_id']){
		$search .= (($search) ? " and ":" where ")."a.it_id = '".$_POST['it_id']."'";
	}
	if($_POST['it_name']){
		$search .= (($search) ? " and ":" where ")."a.it_name like '%".$_POST['it_name']."%'";
	}


	$qry = sql_query("
		select 
			a.it_id, a.it_name , 
			sum(b.ct_qty) as ct_qty
		from
			".$g4['yc4_item_table']." a
			left join
			".$g4['yc4_cart_table']." b on a.it_id = b.it_id and ( b.ct_status = '주문' or b.ct_status = '배송' or b.ct_status = '완료' )
		".$search."
	");

	

	while($data = sql_fetch_array($qry)){
		if($data['it_id']){
			$result .= "
				<tr>
					<td class='it_id'>".$data['it_id']."</td>
					<td class='it_name'>".$data['it_name']."</td>
					<td class='it_qty'>".number_format($data['ct_qty'])."</td>
					<td align='center'><button onclick=\"oneday_item_select(this);\">선택</button></td>
				</tr>
			";
		}
	}

	if(!$result){
		$result = "
			<tr align='center'>
				<td colspan='4'>데이터가 존재하지 않습니다.</td>
			</tr>
		";
	}

	echo $result;
	exit;

}
# 상품검색 ajax xml 출력 끝 #


# 원데이 이벤트 정보 로드 #
if($_GET['uid']){
	$oneday_data = sql_fetch("
		select 
			a.*,b.it_name,
			c.mb_name as create_name,
			d.mb_name as update_name
		from 
			yc4_oneday_sale_item a
			left join
			yc4_item b on a.it_id = b.it_id
			left join
			g4_member c on c.mb_id = a.create_id
			left join
			g4_member d on d.mb_id = a.update_id
		where 
			a.uid = '".$_GET['uid']."'
	");
//	print_r($oneday_data);


}

auth_check($auth[$sub_menu], "r");
$html_title = "원데이 이벤트";

if($oneday_data)
	$html_title .= " 수정";
else
	$html_title .= " 입력";

$g4['title'] = $html_title;


include_once ("$g4[admin_path]/admin.head.php");
?>
<?=subtitle($html_title);?>

<form name='onedaysale_frm' method='post' action='<?=$_SERVER['PHP_SELF'];?>'>
	<input type="hidden" name='it_id' value='<?=$oneday_data['it_id']?>'/>
	<input type="hidden" name='mode' value='<?=($oneday_data)?'update':'insert'?>'/>
	<?if($oneday_data){?>
	<input type="hidden" name='uid' value='<?=$oneday_data['uid']?>'/>
	<?}?>
	<table cellpadding='0' cellspacing='0' width='100%'>
		<col width='15%' />
		<col />
		<col width='15%' />
		<col />
		<tr><td colspan='4' height='2' bgcolor='#0E87F9'></td></tr>
		<tr class='ht'>
			<td>상품코드</td>
			<td class='it_id'><?=$oneday_data['it_id'];?><button onclick="oneday_layer_open(); return false;">상품선택</button></td>
			<td>실제상품코드</td>
			<td class='l_it_id'><input type="text" name='l_it_id' value='<?=$oneday_data['l_it_id'];?>'/></td>
		</tr>
		<tr class='ht'>
			<td>상품명</td>
			<td class='it_name' colspan='3'><?=$oneday_data['it_name']?></td>
		</tr>
		<tr class='ht'>
			<td>실재고량</td>			
			<td><input type="text" name='real_qty' value='<?=$oneday_data['real_qty']?>'/></td>
			<td>곱할수량(출력용재고)</td>
			<td><input type="text" name='multiplication' value='<?=$oneday_data['multiplication']?>'/></td>
		</tr>
		<tr class='ht'>
			<td>판매수량</td>
			<td colspan='3'><?=$oneday_data['order_cnt'];?></td>
		</tr>
		<tr class='ht'>
			<td>출력재고</td>
			<td colspan='3'><?=$oneday_data['real_qty'] * $oneday_data['multiplication']?></td>
		</tr>
		<tr class='ht'>
			<td>시작일</td>
			<td><input type="text" name='st_dt' value='<?=$oneday_data['st_dt']?>'/></td>
			<td>종료일</td>
			<td><input type="text" name='en_dt' value='<?=$oneday_data['en_dt']?>'/></td>
		</tr>
		<tr class='ht'>
			<td colspan='4'>
				<font color="#66A2C8">※ 시작일,종료일은 8자리 숫자만 입력하세요.(20140611)</font>
			</td>
		</tr>
		<tr class='ht'>
			<td>판매가격</td>
			<td><input type="text" name='price' value='<?=$oneday_data['price']?>' /></td>
			<td>종료 후 일반상품으로 전환</td>
			<td><input type="checkbox" name='end_flag' value='Y' <?=($oneday_data['end_flag'] == 'Y') ? 'checked':''?> /></td>
		</tr>
		<tr class='ht'>
			<td>원데이 세일 이미지 표시</td>
			<td><input type="checkbox" name='goodday_fg' value='1' <?=($oneday_data['goodday_fg'] == '1') ? "checked":""?> />(체크시 상품 상세페이지에 굿데이 세일 이미지가 노출)</td>
		</tr>
		<tr class='ht'>
			<td>메모</td>
			<td colspan='3'>
				<textarea name="memo" rows="10" style='width:100%;'><?=$oneday_data['memo']?></textarea>
			</td>
		</tr>
		<tr class='ht'>
			<td>등록일</td>
			<td><?=$oneday_data['create_dt']?></td>
			<td>등록자</td>
			<td><?=$oneday_data['create_name']?></td>
		</tr>
		<?if($oneday_data['update_dt']){?>
		<tr class='ht'>
			<td>수정일</td>
			<td><?=$oneday_data['update_dt']?></td>
			<td>수정자</td>
			<td><?=$oneday_data['update_name']?></td>
		</tr>
		<?}?>
	</table>
	<p align=center>
		<input type='submit' class='btn1' accesskey='s' value='  확  인  '>&nbsp;
		<input type='button' class='btn1' accesskey='l' value='  목  록  ' onclick="document.location.href='./oneday.php';">
	</p>
</form>


<style type="text/css">
.item_search_layer{
	width:100%;
}
.item_search_layer_title{
	text-align:center;
	padding:5px;
	position:relative;
	color:#ffffff;
	font-weight:bold;
}
.layer_btn_close{
	position:absolute;
	right : 2px;
	top : 0px;
	color:#000000;
}
.item_search_list .it_qty{
	text-align:right;
}

.item_search_layer{
	display:none;
}
.item_search_layer_title{
	background-color:#0E87F9;
}
</style>
<div class='item_search_layer'>
	<div class='item_search_layer_title'>
		상품검색
		<button class='layer_btn_close' onclick="oneday_layer_close();">X</button>
	</div>
	상품코드 : <input type="text" name='sc_it_id'/>
	<br />
	상품명 : <input type="text" name='sc_it_name'/>
	<br />
	<button onclick="item_search();">검색</button>

	<table width='100%' class='item_search_list'>
		<thead>
		<tr>
			<th>상품코드</th>
			<th>상품명</th>
			<th>판매수량</th>
			<th></th>
		</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>

<script type="text/javascript">

function item_search(){
	var it_id	= $('input[name=sc_it_id]').val();
	var it_name = $('input[name=sc_it_name]').val();

	if(it_id == '' && it_name == ''){
		alert('상품코드나 상품명을 입력해 주세요');
		return false;
	}

	$.ajax({
		url : '<?=$_SERVER['PHP_SELF'];?>',
		type : 'post',
		datatype : 'html',
		data : {
			'mode' : 'item_search',
			'it_id' : it_id,
			'it_name' : it_name
		},
		success : function ( result ) {
			$('.item_search_list tbody').html( result );
		}
	});
}

function oneday_item_select(obj){
	var it_id = $(obj).parent().parent().find('.it_id').text();
	var it_name = $(obj).parent().parent().find('.it_name').text();
	var it_qty = $(obj).parent().parent().find('.it_qty').text();

	$('form[name=onedaysale_frm] input[name=it_id]').val(it_id);
	$('form[name=onedaysale_frm] .it_id').text(it_id);
	$('form[name=onedaysale_frm] .l_it_id').text(it_id);
	$('form[name=onedaysale_frm] .it_name').text(it_name);
	$('form[name=onedaysale_frm] .it_qty').text(it_qty);

}

function oneday_layer_open(){
	if($('.item_search_layer').css('display') == 'none'){
		$('.item_search_layer').slideDown();
	}
		return false;
	
}

function oneday_layer_close(){
	if($('.item_search_layer').css('display') == 'none'){
		return false;
	}else{
		$('.item_search_layer').slideUp();
		$('.item_search_list tbody').empty();
		$('input[name=sc_it_id]').val('');
		$('input[name=sc_it_name]').val('');
	}
}

</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

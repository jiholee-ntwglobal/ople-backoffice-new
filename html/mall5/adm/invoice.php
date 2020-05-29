<?
include_once "_common.php";

/*
# 환율 정보 로드 #
$money = simplexml_load_file("http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency=KRW&ToCurrency=USD");

$money = floatVal($money);
*/





if($member['mb_level'] != 10){
	alert('접근 권한이 없습니다.');
	exit;
}


# 주문서 정보 로드 #
$orderQry = sql_query("
	select 
		on_uid,
		od_b_name,
		od_zip1,
		od_zip2,
		od_addr1,
		od_addr2,
		od_time,
		od_id,
		od_settle_case,
		od_receipt_card_usd
	from 
		yc4_order 
	where 
		od_id = '".$_GET['od_id']."'
");

$order_info = sql_fetch_array($orderQry);

$address = $order_info['od_zip1']."-".$order_info['od_zip2']."\n".$order_info['od_addr1']."\n".$order_info['od_addr2'];
/*
echo $address;
echo "<pre>";
print_r($order_info);
echo "</pre>";
*/

# 주문서 상품 리스트 로드 #
$itemQry = sql_query("
	select 
		a.ct_qty,
		a.ct_qty,
		b.it_name,
		b.it_amount,
		a.it_id
	from 
		yc4_cart a
		left join
		yc4_item b on a.it_id = b.it_id
	where 
		a.on_uid = '".$order_info['on_uid']."'
");
//print_r($order_info);
$order_total_price = 0;
while($item_list = sql_fetch_array($itemQry)){

	$total_item_price = $item_list['it_amount'] * $item_list['ct_qty'];
	$order_total_price += $total_item_price;
	$item_tr.= "
		<tr>
			<td align='center'>
				<input type='checkbox' class='item_list''/>

				<input type='hidden' name='it_list[]' value='".$item_list['it_id']."'/>
				<input type='hidden' name='it_name_".$item_list['it_id']."' value='".urlencode($item_list['it_name'])."'/>
				<input type='hidden' name='it_qty_".$item_list['it_id']."' value='".$item_list['ct_qty']."'/>
				<input type='hidden' name='it_price_".$item_list['it_id']."' value='".number_format($item_list['it_amount'])."'/>
				<input type='hidden' name='it_total_price_".$item_list['it_id']."' value='".number_format($total_item_price)."'/>
			</td>
			<td>".$item_list['it_name']."</td>
			<td align='right'>
				<input type='text' class='item_qty'  name='item_qty_".$item_list['it_id']."' value='".$item_list['ct_qty']."' onchange='price_sum();'/>
			</td>
			<td align='right'>
				<input type='text' class='item_price' name='item_price_".$item_list['it_id']."' value='".number_format($item_list['it_amount'])."' onchange='price_sum();'/>
			</td>
			<td align='right' class='item_price_result'>".number_format($total_item_price)."</td>
		</tr>
		
	";
}

# 배송비 처리 #


if($order_total_price >= 200000){
	$shipping = 0;
}elseif($order_total_price >= 100000){
	$shipping = 2000;
}elseif($order_total_price >= 50000){
	$shipping = 4000;
}else{
	$shipping = 5000;
}


$total_amount = $order_total_price + $shipping;

# 카드 결제시에만 총 금액 달러로 표시(당시 결제된 금액) #
if($order_info['od_receipt_card_usd']>0){
	$total_amount = 'USD '.$order_info['od_receipt_card_usd'];
}

$tfoot.= "
	<tr>
		<td colspan='3'></td>
		<td align='right'>SHIPPING</td>
		<td align='right' class='shipping'><input type='hidden' name='shipping' value='".number_format($shipping)."'/>".number_format($shipping)."</td>
	</tr>
	<tr>
		<td colspan='3'></td>
		<td align='right'>Paid</td>
		<td align='right' class='paid'><input type='hidden' name='order_total_price' value='".number_format($order_total_price)."'/>".number_format($order_total_price)."</td>
	</tr>
	<tr>
		<td colspan='3'></td>
		<td align='right'>Total Amount</td>
		<td align='right' class='total_amount'><input type='hidden' name='total_amount' value='".$total_amount."'/>".$total_amount."</td>
	</tr>
";


# 신용카드로 결제시 신용카드 번호 출력(뒷자리만 저장되어있음)
if(in_array($order_info['od_settle_case'],array('신용카드','신2'))){
	$comment = "
		<div class='card_info'>
			<input type='hidden' name='od_case' value='card'/>
			Card No :
			<input type='text' name='card_no1' maxlength='4' size='4'>
			-
			<input type='password' maxlength='4' size='4'>
			-
			<input type='password' maxlength='4' size='4'>
			-
			<input type='text' name='card_no2' maxlength='4' size='4'>
		</div>
	";
}else{
	$comment = "<div>
		<input type='hidden' name='od_case' value='bank'/>
		Direct deposit
	</div>";
}

# 날짜 형식 변경 월-일-년 #
$order_info['od_time'] = substr($order_info['od_time'],0,10);
$order_time_arr = explode('-',$order_info['od_time']);
$order_time_y = $order_time_arr[0];
$order_time_m = $order_time_arr[1];
$order_time_d = $order_time_arr[2];
$order_time = $order_time_m.'-'.$order_time_d.'-'.$order_time_y;
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<style>
.od_date{
		text-align : right;
}

#layer{
		display:none;
		position:absolute;
		left:0px; right:0px;
		top:0px; bottom:0px;
}
.layer_mask{
	background-color:#000000;
	position:absolute;
	left:0px; right:0px;
	top:0px; bottom:0px;
	opacity : 0.3;
}

.layer_contents_wrap{
	z-index:1;
	position:absolute;
	background-color:#ffffff;
	border:1px solid #dddddd;
	width : 80%;
	
	
}
.layer_contents{
	z-index:1;
	
	padding:15px;
	
}

.layer_header{
	margin-bottom:10px;
	overflow:hidden;
	background-color:blue;
	color:#ffffff;
	font-weight:bold;
}
.layer_title{
		float:left;
}
.layer_header button{
	float:right;
}

.item_row{
	cursor:pointer;
}
.item_row:hover{
	background-color:#dddddd;
	
}


.order_item_list input{
	text-align:right;
}

.card_info{
	text-align:right;
}

</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<h1>INVOICE</h1>
<form action="invoice_export.php" method='POST'>
	<h4 class='od_date'><input type="text" name='od_time' value='<?=$order_time;?>' style='text-align:right;'/></h4>
	<h3>Invoice Number : <input type='text' name='od_id' value='<?=$_GET['od_id']?>' onblur="this.value=this.value.replace(/[^0-9]/g,'');location.href='<?=$_SERVER['PHP_SELF'];?>?od_id='+this.value;"/></h3>
	<h5>OPLE.COM</h5>
	<h5>
		GARDEN GROVE,CA 92841<br/>
		USA<br/>
		Email : qa@ople.com
	</h5>
	<h5 style='text-align:right;'>
	TO:<br/>
	<input type="text" name='name' class='od_name' value='<?=$order_info['od_b_name'];?>' style='text-align:right;'/>
	<textarea name='address' style='width:100%; height:100px;text-align:right;'><?=$address;?></textarea>
	</h5>
	<div style='text-align:right;'>
		<button onclick="item_add('상품검색');">+</button>
		<button onclick='item_del();'>-</button>
	</div>
	<table width='100%' style='border-collapse: collapse;' border='1' class='order_item_list'>
		<thead>
			<tr>
				<th><input type='checkbox' class='chk_all' onclick="chk_all_fnc();"/></th>
				<th>Product/Service</th>
				<th>Quantity</th>
				<th>Rate/Unit</th>
				<th>Total (South Korean Won)</th>
			</tr>
		</thead>
		<tfoot>
			<?=$tfoot;?>
		</tfoot>
		<tbody><?=$item_tr;?></tbody>
	</table>
	<?=$comment;?>
	<p>
		<input type='text' class='od_name' value='<?=$order_info['od_b_name'];?>'/></br/>
		THANK YOU FOR YOUR BUSINESS!
	</p>

	<div id='layer'>
		<div class='layer_mask'></div>
		<div class='layer_contents_wrap'>
			<div class='layer_header'>
				<span class='layer_title'></span>
				<button onclick="layer_close();">X</button>
			</div>
			<div class='layer_contents'></div>
		</div>
	</div>
	<div>
		<input type="submit" value='PDF 파일 다운'/>
	</div>
</form>
<script>

// 체크박스 처리
function chk_all_fnc(){
	if($('.chk_all').prop('checked') == true){
			$('.item_list').prop('checked',true);
	}else{
			$('.item_list').prop('checked',false);
	}
}

// 선택 제품 삭제
function item_del(){
	if(!confirm('선택된 상품을 주문서에서 삭제하시겠습니까?')){
			return false;
	}

	$('.item_list').parent().parent().remove();
	price_sum();
}

// 제품 추가
function item_add(title){
	$('.layer_contents').load('item_search_layer.php',function(){
		$('.layer_title').text(title);
		price_sum();
		$('#layer').fadeIn();
	});
	
	
}
// 레이어 닫기
function layer_close(){
	$('#layer').fadeOut(function(){
		$('.layer_contents,.layer_title').empty();
	});
}



// 주문서 이름 변경 처리
function name_change(name){
	$('.od_name').val(name);
}

$('.od_name').blur(function(){
	name_change($(this).val());
});


function price_sum(){
	var total_paid = 0;
	for(var i=0; i<$('.order_item_list tbody tr').length; i++){
		var qty = $('.order_item_list tbody tr:eq('+i+')').find('.item_qty').val().replace(/[^0-9]/g,'');
		var item_price = $('.order_item_list tbody tr:eq('+i+')').find('.item_price').val().replace(/[^0-9]/g,'');
		var result = item_price*qty;
		$('.order_item_list tbody tr:eq('+i+')').find('.item_price_result').text(numberCommas(result));
		total_paid += result;

		// 콤마 찍기
		$('.order_item_list tbody tr:eq('+i+')').find('.item_qty').val(numberCommas(qty));
		$('.order_item_list tbody tr:eq('+i+')').find('.item_price').val(numberCommas(item_price));
	}
	

	// 배송비 처리
	if(total_paid >= 200000){
		var shipping = 0;
	}else if(total_paid >= 100000){
		var shipping = 2000;
	}else if(total_paid >= 50000){
		var shipping = 4000;
	}else if($('.order_item_list tbody tr').length == 0){
		var shipping = 0;
	}else{
		var shipping = 5000;
	}


	$('.paid').text(numberCommas(total_paid));
	$('.shipping').text(numberCommas(shipping));
	$('.total_amount').text('USD '+numberCommas( total_paid+shipping ));
}

// 천단위 컴마찍기
function numberCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


</script>

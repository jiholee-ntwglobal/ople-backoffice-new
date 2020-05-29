<?
include_once "_common.php";


if(!$_GET['key_value']){
	$data_tr = "
		<tr>
			<td colspan='4' align='center'>제품을 검색해 주세요</td>
		</tr>
	";
}else{

	$item_Q = sql_query("
		select * from yc4_item where ".$_GET['key']." like '%".$_GET['key_value']."%'
	");
	
	while($item = sql_fetch_array($item_Q)){
		$data_tr .= "
			<tr class='item_row' onclick=\"item_click($(this));\">
				<td class='it_id'>".$item['it_id']."</td>
				<td class='it_name'>".$item['it_name']."</td>
				<td>".$item['it_maker']."</td>
				<td class='it_amount'>".$item['it_amount']."</td>
			</tr>
		";
	}

	if(!$data_tr){
		$data_tr = "
			<tr>
				<td colspan='4' align='center'>데이터가 존재하지 않습니다.</td>
			</tr>
		";
	}
}


if($_GET['mode'] == 'search'){
	echo $data_tr;
	exit;
}
?>

<div id='item_search_layer'>
	<select name='key'>
		<option value='it_name'>제품명</option>
		<option value='it_maker'>제조사</option>
		<option value='id_id'>제품코드</option>
	</select>
	<input type='text' name='key_value'/>
	<button onclick="item_search_submit();">검색</button>
	<table width='100%' style='border-collapse: collapse;' border='1'>
		<thead>
			<tr>
				<th>제품코드</th>
				<th>제품명</th>
				<th>제조사</th>
				<th>가격</th>
			</tr>
		</thead>
		<tbody class='item_list_contents'>
			<?=$data_tr;?>
		</tbody>
	</table>
</div>


<script type="text/javascript">

// 제품 검색 처리
function item_search_submit(){
	var key = $('select[name=key]').val();
	var key_value = $('input[name=key_value]').val();

	$('.item_list_contents').empty();
	$('.item_list_contents').html("<tr><td align='center' colspan='4'>데이터 로딩중...</td></tr>");
	$('.item_list_contents').load('<?=$_SERVER['PHP_SELF'];?>?mode=search&key='+key+'&key_value='+key_value);
}

function item_click(obj){
	var it_id = obj.find('.it_id').text();
	var it_name = obj.find('.it_name').text();
	var it_amount = obj.find('.it_amount').text();

	var result = 
		"<tr>"+
		"	<td align='center'>"+
		"		<input type='checkbox' class='item_list''/>"+
		"		<input type='hidden' name='it_list[]' value='"+it_id+"'/>"+
		"	</td>"+
		"	<td>"+it_name+"</td>"+
		"	<td align='right'>"+
		"		<input type='text' class='item_qty'  name='item_qty_"+it_id+"' value='1' onchange='price_sum();'/>"+
		"	</td>"+
		"	<td align='right'>"+
		"		<input type='text' class='item_price' name='item_price_"+it_id+"' value='"+numberCommas(it_amount)+"' onchange='price_sum();'/>"+
		"	</td>"+
		"	<td align='right' class='item_price_result'>"+numberCommas(it_amount)+"</td>"+
		"</tr>";
	$('.order_item_list tbody').append(result);

	layer_close();
	return true;
}
</script>
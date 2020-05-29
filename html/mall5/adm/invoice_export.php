<?php
#############################################################
#															#
#															#
#	File Name	: invoice_export.php						#
#	Comment		: INVOICE PDF 파일 출력 처리 파일			#
#	Date		: 2014-05-07 홍민기							#
#															#
#															#
#############################################################


include_once "../../dompdf-master/dompdf_config.inc.php";





# 스타일시트 #
$style = "
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
";



# 상품 합계금액 처리 #
$tfoot = "
	<tr>
		<td colspan='2'></td>
		<td align='right'>SHIPPING</td>
		<td align='right' class='shipping'>".$_POST['shipping']."</td>
	</tr>
	<tr>
		<td colspan='2'></td>
		<td align='right'>Paid</td>
		<td align='right' class='paid'>".$_POST['order_total_price']."</td>
	</tr>
	<tr>
		<td colspan='2'></td>
		<td align='right'>Total Amount</td>
		<td align='right' class='total_amount'>".$_POST['total_amount']."</td>
	</tr>
";


# 상품 리스트 처리 #
$item_tr = "";
if(is_array($_POST['it_list'])){
	foreach($_POST['it_list'] as $val){
		$item_tr .= "
			<tr>
				<td>".urldecode($_POST['it_name_'.$val])."</td>
				<td align='right'>".$_POST['it_qty_'.$val]."</td>
				<td align='right'>".$_POST['it_price_'.$val]."</td>
				<td align='right' class='item_price_result'>".$_POST['it_total_price_'.$val]."</td>
			</tr>
		";
	}
}


switch($_POST['od_case']){
	case 'card' : 
		$comment = "
			<div class='card_info'>
				Card No : <span>".$_POST['card_no1']."</span><span>-****</span><span>-****-</span><span>".$_POST['card_no2']."</span>
			</div>
		";
		break;
	case 'bank' : 
		$comment = "
			<div>Direct deposit</div>
		";
		break;
}


# 컨텐츠 영역 #
$body ="
	<h1>INVOICE</h1>
	<h4 class='od_date'>".$_POST['od_time']."</h4>
	<h3>Invoice Number : ".$_POST['od_id']."</h3>
	<h5>OPLE.COM</h5>
	<h5>
		GARDEN GROVE,CA 92841<br/>
		USA<br/>
		Email : qa@ople.com
	</h5>
	<h5 style='text-align:right;'>
	TO:<br/>
	".$_POST['name']."<br/>
	".nl2br($_POST['address'])."
	</h5>
	<table width='100%' style='border-collapse: collapse;' border='1' class='order_item_list' cellpadding='5'>
		<thead>
			<tr>
				<th>Product/Service</th>
				<th>Quantity</th>
				<th>Rate/Unit</th>
				<th>Total (South Korean Won)</th>
			</tr>
		</thead>
		
		<tbody>".$item_tr."</tbody>
		<tfoot>
			".$tfoot."
		</tfoot>
	</table>
	".$comment."
	<p>
		".$_POST['name']."<br/>
		THANK YOU FOR YOUR BUSINESS!
	</p>
";

# 실제 출력 부분 #
$html = "
	<!doctype html>
	<html lang='en'>
	<head>
	<meta charset='UTF-8'>
	<title>Invoice</title>
	
	</head>
	<body>
		".$style."
		".$body."
	</body>
	</html>

";



# pdf 파일 출력 #
$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->render();

# PDF 파일명 처리 ople_order_주문번호.pdf #
$dompdf->stream("ople_order_".$_POST['od_id'].".pdf");



?>
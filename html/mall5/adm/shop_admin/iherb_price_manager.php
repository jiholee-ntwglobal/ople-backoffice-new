<?php 
/*
----------------------------------------------------------------------
file name	 : iherb_price_manager.php
comment		 : 아이허브 가격수집 상품 관리
date		 : 2015-03-24
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

$real = false;

if($real){
	$amount_column = 'it_amount';
}else{
	$amount_column = 'it_amount_test';
}




function utf8_length($str) {
  $len = strlen($str);
  for ($i = $length = 0; $i < $len; $length++) {
   $high = ord($str{$i});
   if ($high < 0x80)//0<= code <128 범위의 문자(ASCII 문자)는 인덱스 1칸이동
    $i += 1;
   else if ($high < 0xE0)//128 <= code < 224 범위의 문자(확장 ASCII 문자)는 인덱스 2칸이동
    $i += 2;
   else if ($high < 0xF0)//224 <= code < 240 범위의 문자(유니코드 확장문자)는 인덱스 3칸이동 
    $i += 3;
   else//그외 4칸이동 (미래에 나올문자)
    $i += 4;
  }
  return $length;
}

function utf8_strcut($str, $chars, $tail = '...') {  
  if (utf8_length($str) <= $chars)//전체 길이를 불러올 수 있으면 tail을 제거한다.
   $tail = '';
  else
   $chars -= utf8_length($tail);//글자가 잘리게 생겼다면 tail 문자열의 길이만큼 본문을 빼준다.
  $len = strlen($str);
  for ($i = $adapted = 0; $i < $len; $adapted = $i) {
   $high = ord($str{$i});
   if ($high < 0x80)
    $i += 1;
   else if ($high < 0xE0)
    $i += 2;
   else if ($high < 0xF0)
    $i += 3;
   else
    $i += 4;
   if (--$chars < 0)
    break;
  }
  return trim(substr($str, 0, $adapted)) . $tail;
}

$sub_menu = "300920";
include_once("./_common.php");

$ratio = $default['de_iherb_amount_ratio'] / 100;

if($_POST['mode']){

	switch($_POST['mode']){

		case 'manual_save':
			$rs = sql_query("select count(*) as cnt from yc4_item_amount_manual where it_id='$_POST[it_id]'");
			$data = sql_fetch_array($rs);

			if($data['cnt'] > 0){
				$qry = "update yc4_item_amount_manual set amount_usd='$_POST[manual_price]',update_dt=NOW() where it_id='$_POST[it_id]'";
			} else {
				$qry = "insert into yc4_item_amount_manual (it_id,amount_usd, create_dt) values ('$_POST[it_id]','$_POST[manual_price]',NOW())";
			}
			
			sql_query(($_POST['manual_price'] != '') ? $qry : "delete from yc4_item_amount_manual where it_id='$_POST[it_id]'");

			$amount_usd = ($_POST['manual_price'] != '') ? $_POST['manual_price'] : 0;

			sql_query("insert into yc4_item_amount_usd_history (it_id, dt, amount_usd, amount_type) values ('$_POST[it_id]',NOW(),'$amount_usd','4')");


			alert('저장되었습니다.','iherb_price_manager.php?menu_code='.$_POST['menu_code'].'&chz_iherb='.$_POST['chz_iherb'].'&search_where='.$_POST['search_where'].'&search_value='.$_POST['search_value'].'&page='.$_POST['page']);			

		
		break;

		case 'item_price_update':

			$rs = sql_query("select amount_usd from yc4_item_amount_manual where it_id='$_POST[it_id]'");
			$m_data = sql_fetch_array($rs);

			$rs = sql_query("select sum(cast(iherb_amount * qty AS decimal(5, 2))) AS iherb_amount from ople_mapping where it_id='$_POST[it_id]'");
			$ihub_data = sql_fetch_array($rs);

			$it_amount_usd = ($m_data['amount_usd'] > 0) ? $m_data['amount_usd'] : $ihub_data['iherb_amount'] * $ratio;

			sql_query("update yc4_item set it_amount_usd='$it_amount_usd',it_amount_iherb='$ihub_data[iherb_amount]' where it_id='$_POST[it_id]'");


			alert('저장되었습니다.','iherb_price_manager.php?menu_code='.$_POST['menu_code'].'&chz_iherb='.$_POST['chz_iherb'].'&search_where='.$_POST['search_where'].'&search_value='.$_POST['search_value'].'&page='.$_POST['page']);			

			break;
		
		case 'item_price_submit' : 
			
			$data = sql_fetch("select ".$amount_column.",it_amount_usd from ".$g4['yc4_item_table']." where it_id = '".$_POST['it_id']."'");

			$it_amount = round($data['it_amount_usd'] * $default['de_conv_pay'],-2);

			if(!$it_amount){
				alert('달러가격 업데이트를 실행해 주세요.');
				exit;
			}

			if($it_amount == $data[$amount_column]){
				alert("가격 변동이 없습니다.");
				exit;
			}

			$update_sql = "
				update ".$g4['yc4_item_table']." set ".$amount_column." = ".(int)$it_amount." where it_id = '".$_POST['it_id']."'
			";
			$result = sql_query($update_sql);
			if(!$result){
				alert('처리중 오류 발생 다시 시도해 주세요.');
				exit;
			}

			$history_insert_sql = "
				insert into yc4_item_amount_history (it_id,amount,update_id,update_dt,fg)
				values('".$_POST['it_id']."',".(int)$it_amount.",'".$member['mb_id']."','".$g4['time_ymdhis']."','A')
			";
			sql_query($history_insert_sql);

			alert('상품가격이 업데이트 되었습니다.','iherb_price_manager.php?menu_code='.$_POST['menu_code'].'&chz_iherb='.$_POST['chz_iherb'].'&search_where='.$_POST['search_where'].'&search_value='.$_POST['search_value'].'&page='.$_POST['page']);
			exit;
			break;
		case 'item_price_submit_all' : 
			
			$it_id = stripslashes($_POST['it_id']);
			$json = json_decode($it_id);
			if(count($json) < 1){
				alert('가격을 업데이트할 상품이 존재하지 않습니다.');exit;
			}

			$cnt = 0;

			foreach($json as $val){
				$data = sql_fetch("select it_id,".$amount_column.",it_amount_usd from ".$g4['yc4_item_table']." where it_id = '".$val."'");

				$it_amount = round($data['it_amount_usd'] * $default['de_conv_pay'],-2);
				if(!$it_amount){
					continue;
				}

				if($it_amount == $data[$amount_column]){
					continue;
				}

				$update_sql = "
					update ".$g4['yc4_item_table']." set ".$amount_column." = ".(int)$it_amount." where it_id = '".$val."'
				";
				$result = sql_query($update_sql);

				$history_insert_sql = "
					insert into yc4_item_amount_history (it_id,amount,update_id,update_dt,fg)
					values('".$val."',".(int)$it_amount.",'".$member['mb_id']."','".$g4['time_ymdhis']."','A')
				";
				sql_query($history_insert_sql);
				$cnt++;

				
				


				

				

			}

			alert($cnt.'개의 상품가격이 업데이트 되었습니다.','iherb_price_manager.php?menu_code='.$_POST['menu_code'].'&chz_iherb='.$_POST['chz_iherb'].'&search_where='.$_POST['search_where'].'&search_value='.$_POST['search_value'].'&page='.$_POST['page']);
			exit;

			//print_r2($data);
		
			exit;
			break;
	}
}


auth_check($auth[$sub_menu], "r");

$g4['title'] = "아이허브 가격 수집상품 관리";
include_once ($g4['admin_path']."/admin.head.php");


if($_GET['page'] == '') $_GET['page'] = 1;
if($_GET['menu_code'] == '') $_GET['menu_code'] = 'A';

switch($_GET['menu_code']){
	case 'A': $add_where = "AND m.it_id IS NULL AND op.iherb_update_dt IS NOT NULL AND !isnull(op.uid)"; break;
	case 'M': $add_where = "AND m.it_id IS NOT NULL AND !isnull(op.uid)"; break;
	case 'J': $add_where = "AND op.iherb_update_dt IS NULL AND !isnull(op.uid)"; break;
	case 'W': $add_where = ""; break;
}

if($_GET['search_where'] && $_GET['search_value']){
	switch($_GET['search_where']){
		case 'it_id': $add_where .= " AND i.it_id='$_GET[search_value]'"; break;
		case 'it_name': $add_where .= " AND i.it_name like '%$_GET[search_value]%'"; break;
		case 'upc': $add_where .= " AND op.upc = '$_GET[search_value]'"; break;
	}
}

if($_GET['chz_iherb'] == 'Y'){	
	$order_by = "order by if(i.it_amount_iherb>0 && iherb_amount>0 && i.it_amount_iherb!=iherb_amount, 1, 0) desc";
}

$sql = "SELECT count(DISTINCT i.it_id) AS cnt
		  FROM yc4_item i
			   LEFT OUTER JOIN yc4_item_amount_manual m ON i.it_id = m.it_id
			   LEFT OUTER JOIN ople_mapping op ON i.it_id = op.it_id
		 WHERE     i.it_use = 1
			   $add_where";



$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함





$item_rs = sql_query($a="
	SELECT i.it_id,
       i.it_name,
	   i.it_amount_usd,
	   i.it_amount_iherb,
	   i.it_amount,
	   i.".$amount_column.",
       if(isnull(m.it_id), '', m.amount_usd) AS manual_price,
       sum(cast(op.wholesale_price * op.qty * ".$ratio." AS decimal(5, 2)))
          AS wholesale_price,
       sum(cast(op.iherb_amount * op.qty * ".$ratio." AS decimal(5, 2)))
          AS iherb_amount,
       sum(cast(op.iherb_amount * op.qty AS decimal(5, 2)))
          AS iherb_amount_ori
  FROM yc4_item i
       LEFT OUTER JOIN yc4_item_amount_manual m ON i.it_id = m.it_id
       LEFT OUTER JOIN ople_mapping op ON i.it_id = op.it_id
 WHERE i.it_use = 1 $add_where
GROUP BY i.it_id
$order_by
 LIMIT $from_record, $rows
");
//if($_SERVER['REMOTE_ADDR'] == '59.17.43.129') echo $a;
$qstr = "menu_code=$_GET[menu_code]&search_where=$_GET[search_where]&search_value=$_GET[search_value]&chz_iherb=$_GET[chz_iherb]";

$no = 0;
while($item_data = sql_fetch_array($item_rs)){
	$show_no = $total_count - ($config[cf_page_rows] * ($_GET['page']-1)) - $no;

	$tr_class = ($item_data['it_amount_iherb'] && $item_data['iherb_amount'] && $item_data['it_amount_iherb'] != $item_data['iherb_amount_ori']) ? 'chz_iherb' : '';
	
	$list_tr .= "
		<tr class='aa $tr_class'>
			<td class='ca_id' align='center'>$show_no</td>
			<td class='ca_id it_id' align='center'>$item_data[it_id]</td>
			<td align='left' style='padding-left:10px;'>"./*utf8_strcut(get_item_name($item_data['it_name']),60)*/mb_substr(get_item_name($item_data['it_name']),0,50,'utf8')."</td>
			<td class='ca_id' align='right'>￦ ".number_format($item_data['it_amount'])."</td>
			<td class='ca_id' align='right'>$ ".usd_convert($item_data['it_amount'])."</td>
			<td class='ca_id' align='right'>￦ ".number_format($item_data[$amount_column])."</td>
			<td class='ca_id' align='right'>$ ".number_format($item_data['it_amount_usd'],2)."</td>
			<!-- <td class='ca_id' align='right'>￦ ".number_format(round( ($item_data['it_amount_usd']*$default['de_conv_pay']),-2 ))."</td> -->
			<td class='ca_id' align='right'>$ ".number_format($item_data['it_amount_iherb'],2)."</td>
			<td class='ca_id' align='right'>$ ".number_format($item_data['wholesale_price'],2)."</td>
			<!-- <td class='ca_id' align='right'>$ ".number_format($item_data['iherb_amount'],2)."</td> -->
			<td class='ca_id' align='right'>$ ".number_format($item_data['iherb_amount_ori'],2)."</td>
			<td align='center'><input type='text' name='it_id_$item_data[it_id]' value='$item_data[manual_price]' style='width:50px;'/>&nbsp;<input type='button' value='변경' onclick=\"chz_manual_price('$item_data[it_id]')\"/></td>			
			<td align='center'><input type='button' value='달러가격 업데이트' onclick=\"update_price('$item_data[it_id]')\"/>&nbsp;<input type='button' value='상품가격(원) 업데이트' onclick=\"submit_price('".$item_data['it_id']."');\"></td>
		</tr>

	";
	$no++;
}
?>
<style type="text/css">
a.ca_tab {border:1px solid black; padding:5px 30px;color:black;}
a.sel_ca_id {font-weight:bold;color:red}
.chz_iherb {background-color:yellow;}
.yellowgreen {background-color:yellowgreen;}
.yellowgreen td {color:white;}
</style>
<h1>현재환율 $1 = ￦<?php echo number_format($default['de_conv_pay']);?> &nbsp;&nbsp;<a href="<?php echo $g4['shop_admin_path']?>/configform.php">환율 변경</a></h1>
<h2>아이허브 대비 설정가격 : 아이허브 가격 <?php echo $default['de_iherb_amount_ratio'];?>%(수동가격설정 상품 제외) &nbsp;&nbsp;<a href="<?php echo $g4['shop_admin_path'];?>/configform_extension.php">설정변경</a></h2>
<br/>
<hr/>
<br/>
<a href="./iherb_price_manager.php?menu_code=A" class="ca_tab <?php if($_GET['menu_code'] == 'A') echo 'sel_ca_id'; ?>">아이허브가 자동수집 상품</a>
<a href="./iherb_price_manager.php?menu_code=M" class="ca_tab <?php if($_GET['menu_code'] == 'M') echo 'sel_ca_id'; ?>">수동관리 상품</a>
<a href="./iherb_price_manager.php?menu_code=J" class="ca_tab <?php if($_GET['menu_code'] == 'J') echo 'sel_ca_id'; ?>">아이허브 미수집 상품</a>
<a href="./iherb_price_manager.php?menu_code=W" class="ca_tab <?php if($_GET['menu_code'] == 'W') echo 'sel_ca_id'; ?>">전체</a>

<?php if($_GET['menu_code'] == 'A'){?>
<input type="checkbox" id="chz_iherb" value='Y' <?php if($_GET['chz_iherb'] == 'Y') echo 'checked'; ?> onclick="go_chz_iherb()"/>&nbsp;가격 변경된 아이허브 상품만 보기
<?php } ?>
<?php if($_GET['menu_code'] == 'J'){?>
&nbsp;&nbsp;<input type="button" value="아이허브 미수집 상품 가격(달러) 일괄 등록" onclick="alert('준비중입니다.')">
<?php } ?>
<form name="search_frm" method="GET" >
	<input type="hidden" name="menu_code" value="<?php echo $_GET['menu_code']; ?>"/>	
	<input type="hidden" name="chz_iherb" value="<?php if($_GET['menu_code'] == 'A') echo $_GET['chz_iherb']; ?>"/>	
	<table width="100%" style="padding-top:30px;">		
		<tr>
			<td><select name='search_where'>
			<option value='it_name' <?php if($_GET['search_where'] == 'it_name') echo 'selected'; ?>>아이템명</option>
			<option value='it_id' <?php if($_GET['search_where'] == 'it_id') echo 'selected'; ?>>it_id</option>
			<option value='upc' <?php if($_GET['search_where'] == 'upc') echo 'selected'; ?>>UPC</option>
			</select>&nbsp;
			<input type="text" name="search_value" value="<?php echo $_GET['search_value']; ?>"/>
			<input type="submit" value="검색" /></td>			
			<td align="right" width="50%"><input type="button" value="전체 상품가격(원) 업데이트" onclick="submit_price_all();"></td>
		</tr>
	</table>
</form>

<form name="manual_save_frm" method="post">
	<input type="hidden" name="mode" value=""/>
	<input type="hidden" name="menu_code" value="<?php echo $_GET['menu_code']; ?>"/>
	<input type="hidden" name="chz_iherb" value="<?php echo $_GET['chz_iherb']; ?>"/>
	<input type="hidden" name="search_where" value="<?php echo $_GET['search_where']; ?>" />
	<input type="hidden" name="search_value" value="<?php echo $_GET['search_value']; ?>" />
	<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
	<input type="hidden" name="it_id" value="" />
	<input type="hidden" name="manual_price" value="" />
</form>

<table width='1600'>
	<tr class='ht' align='center'>
		<td><b>NO</b></td>
		<td><b>IT_ID</b></td>
		<td><b>아이템명</b></td>
        <td><b>원래 상품 가격</b></td>
        <td><b>원래 달러 가격</b></td>
		<td><b>현재 상품 가격</b></td>
		<td><b>저장 달러 가격</b></td>
        <!-- <td><b>예상 가격(한화)</b></td> -->
		<td><b>저장 아이허브가</b></td>
		<td><b>WHOLESALE PRICE</b></td>
		<!-- <td><b>아이허브수집가<br>할인율적용</b></td> -->
		<td><b>아이허브수집가</b></td>
		<td><b>수동달러가격</b></td>
		<td><b>업데이트</b></td>
	</tr>
	<?=$list_tr;?>
</table>
<table width=100%>
<tr>    
    <td width=50% align=left><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
	<td width=50%>&nbsp;</td>
</tr>
</table>
<script>
$(document).ready(function(){
	$(".aa").hover(function(){ 
		$(this).addClass("yellowgreen");
	},function(){
		$(this).removeClass("yellowgreen");
	});
});
function go_chz_iherb(){
	var val = $("#chz_iherb").is(":checked") ? 'Y' : '';
	location.href='iherb_price_manager.php?menu_code=A&chz_iherb='+val;
}

function chz_manual_price(it_id){

	var frm = document.manual_save_frm;

	if(confirm("입력 상품의 수동가격을 변경하시겠습니까?")){
		frm.mode.value = 'manual_save';
		frm.it_id.value = it_id;
		frm.manual_price.value = $("input[name=it_id_" + it_id + "]").val();
		frm.submit();
	}
	return false;

}

function update_price(it_id){

	var frm = document.manual_save_frm;

	if(confirm("달러가격을 업데이트하시겠습니까?")){
		frm.mode.value = 'item_price_update';
		frm.it_id.value = it_id;
		frm.submit();
	}
	return false;
}


function submit_price(it_id){

	var frm = document.manual_save_frm;

	if(confirm('실제 상품가격을 업데이트 하시겠습니까?')){
		frm.mode.value = 'item_price_submit';
		frm.it_id.value = it_id;
		frm.submit();
	
	}
	return false;
}


function submit_price_all(){

	if(!confirm('전체 상품가격을 업데이트 하시겠습니까?')){
		return false;
	}

	var frm = document.manual_save_frm;
	
	var arr = new Array();
	var data = new Object();

	var cnt = $('.it_id').length;

	for(var i=0; i<cnt; i++){
		data.it_id = trim($('.it_id:eq('+i+')').text());
		arr.push(data.it_id);
	}

	var result = JSON.stringify(arr);
	

	frm.mode.value = 'item_price_submit_all';
	frm.it_id.value = result;
	frm.submit();

	
}
</script>
<?
$sub_menu = "500423";

include_once("./_common.php");



# insert 처리 시작 #
if($_POST['mode'] == 'insert'){
	
	$_POST['memo']			= mysql_real_escape_string($_POST['memo']);
	$_POST['description']	= mysql_real_escape_string($_POST['description']);
	$end_flag = 'Y';
	
	$sale_limit	= isset($_POST['sale_limit']) ? $_POST['sale_limit'] : "2";
	
	$krw	= round($_POST['price_usd']*$default['de_conv_pay']);

	$qry = "
		insert into 
			yc4_oneday_sale_item
		(	it_id
		,	l_it_id			
		,	price
		,	price_usd
		,	real_qty
		,	multiplication
		,	st_dt
		,	en_dt
		,	create_id
		,	create_dt
		,	end_flag
		,	goodday_fg
		,	order_cnt
		,	memo
		,	description
		,	sale_limit
		,   sort
		)values(
			'".$_POST['it_id']."'
		,	'".$_POST['it_id']."'
		,	'".$krw."'
		,	'".$_POST['price_usd']."'
		,	'".$_POST['real_qty']."'
		,	'".$_POST['multiplication']."'
		,	'".$_POST['st_dt']."'
		,	'".$_POST['en_dt']."'
		,	'".$member['mb_id']."'
		,	now()
		,	'".$end_flag."'
		,	'".$_POST['goodday_fg']."'
		,	'0'
		,	'".$_POST['memo']."'
		,	'".$_POST['description']."'
		,	'".$sale_limit."'
		,	'".$_POST['sort']."'
	)";

	if(sql_query($qry)){
		$uid	= mysql_insert_id();
		$sql	= sql_query("DELETE FROM yc4_oneday_sale_item where it_id='".$_POST['it_id']."' AND uid!='".$uid."'");
		$msg	= '저장이 완료되었습니다.';
		$url	= $_SERVER['PHP_SELF'].'?uid='.$uid;
		if($_POST['msrp_check']=="T"){
			$etc_del	= sql_query("DELETE FROM yc4_item_etc_amount WHERE it_id='".$_POST['it_id']."'");
			$etc_sql	= sql_query("INSERT INTO yc4_item_etc_amount (it_id, pay_code, amount, money_type) VALUES ('".$_POST['it_id']."', '3', '".$_POST['msrp']."', 'usd')");
			$msg		.= '\\n(msrp 등록완료)';
		}
	}else{
		$msg	= '저장중 오류가 발생했습니다. 다시 시도해 주세요.';
	}

	alert($msg,$url);
	exit;
}
# insert 처리 끝 #

# update 처리 시작 #
if($_POST['mode'] == 'update'){

	$end_flag = 'Y';

	$krw					= round($_POST['price_usd']*$default['de_conv_pay']);	
	$_POST['memo']			= mysql_real_escape_string($_POST['memo']);
	$_POST['description']	= mysql_real_escape_string($_POST['description']);
	$qry = "
		update
			yc4_oneday_sale_item
		set
			st_dt			= '".$_POST['st_dt']."'
		,	en_dt			= '".$_POST['en_dt']."'
		,	memo			= '".$_POST['memo']."'
		,	update_id		= '".$member['mb_id']."'
		,	update_dt		= now()
		,	iherb_amount	= '".$_POST['iherb_amount']."'
		,	real_qty		= '".$_POST['real_qty']."'
		,	multiplication	= '".$_POST['multiplication']."'
		,	end_flag		= '".$end_flag."'
		,	price			= '".$krw."'
		,	price_usd		= '".$_POST['price_usd']."'
		,	goodday_fg		= '".$_POST['goodday_fg']."'
		,	description		= '".$_POST['description']."'
		,	sale_limit		= '".$_POST['sale_limit']."'
		where
			uid				= '".$_POST['uid']."'
	";
	if(sql_query($qry)){
		$msg = '수정이 완료되었습니다.';
		$url = $_SERVER['PHP_SELF'].'?uid='.$_POST['uid'];
		if($_POST['msrp_check']=="T"){
			$etc_del	= sql_query("DELETE FROM yc4_item_etc_amount WHERE it_id='".$_POST['it_id']."'");
			$etc_sql	= sql_query("INSERT INTO yc4_item_etc_amount (it_id, pay_code, amount, money_type) VALUES ('".$_POST['it_id']."', '3', '".$_POST['msrp']."', 'usd')");
			$msg		.= '\\n(msrp 등록완료)';
		}
	}else{
		$msg = '저장중 오류가 발생했습니다. 다시 시도해 주세요.';
	}

	alert($msg,$url);
	exit;
}
# update 처리 끝 #

# 상품검색 ajax xml 출력 시작 #
if($_POST['mode'] == 'item_search'){
	$qry = sql_query("
		select 
			a.it_id, a.it_name, a.it_amount, a.it_amount_usd,
			e.amount as msrp, m.iherb_amount
		from
			".$g4['yc4_item_table']." a
		left join
			ople_mapping m on m.it_id=a.it_id
		left join
			yc4_item_etc_amount e ON e.it_id=a.it_id and pay_code = '3'
		where a.it_id = '".$_POST['it_id']."'
	");

	while($data = sql_fetch_array($qry)){
		if($data['it_id']){
			if($data['it_amount_usd']){
				$amount	= "＄ ".$data['it_amount_usd']." ( ￦ ".number_format($data['it_amount_usd']*$default['de_conv_pay'],0)." )";
			}else{
				$amount	= "＄ ".number_format($data['it_amount']/$default['de_conv_pay'],2)." ( ￦ ".$data['it_amount']." )";
			}
			
			$result = array('it_name'=>$data['it_name'],'it_amount'=>$amount,'msrp'=>$data['msrp'],'iherb_amount'=>$data['iherb_amount']);
		}
	}
	if(!$result){
		$result = "데이터가 존재하지 않습니다.";
	}
	echo json_encode($result);
	exit;
}
# 상품검색 ajax xml 출력 끝 #

# 원데이 이벤트 정보 로드 #
if($_GET['uid']){
	$oneday_data = sql_fetch("
		select 
			a.*,b.it_name
		,	c.mb_name as create_name
		,	d.mb_name as update_name
		,	e.amount as msrp
		,	m.iherb_amount as m_iherb_amount
		,	b.it_amount
		,	b.it_amount_usd
		,	a.sort
		from 
			yc4_oneday_sale_item a
		left join
			ople_mapping m on m.it_id = a.it_id
		left join
			yc4_item_etc_amount e ON e.it_id = a.it_id
		left join
			yc4_item b on a.it_id = b.it_id
		left join
			g4_member c on c.mb_id = a.create_id
		left join
			g4_member d on d.mb_id = a.update_id
		where 
			a.uid = '".$_GET['uid']."'
	");
}

auth_check($auth[$sub_menu], "r");
$html_title = "원데이 이벤트";

if($oneday_data){
	$html_title .= " 수정";
	if($oneday_data['it_amount_usd']){
		$ople_price	= "＄ ".$oneday_data['it_amount_usd']." ( ￦ ".number_format($oneday_data['it_amount_usd']*$default['de_conv_pay'],0)." )";
	}else{
		$ople_price	= "＄ ".number_format($oneday_data['it_amount']/$default['de_conv_pay'],2)." ( ￦ ".$oneday_data['it_amount']." )";
	}
}else{
	$html_title .= " 입력";
	$ople_price	= "";
}
$g4['title'] = $html_title;

$iherb_amount = ($oneday_data['iherb_amount'] != 0) ?  $oneday_data['iherb_amount'] : $oneday_data['m_iherb_amount'];

include_once ("$g4[admin_path]/admin.head.php");
?>
<?=subtitle($html_title);?>

<form name='onedaysale_frm' method='post' action='<?=$_SERVER['PHP_SELF'];?>' onsubmit="return submit_chk();">
	<input type="hidden" name='mode' value='<?=($oneday_data)?'update':'insert'?>'/>
<?php if($oneday_data){?>
	<input type="hidden" name='uid' value='<?=$oneday_data['uid']?>'/>
<?php }?>
	<table cellpadding='0' cellspacing='0' width='100%'>
		<col width='17%' />
		<col width='40%' />
		<col width='15%' />
		<col width='28%' />
		<tr><td colspan='4' height='2' bgcolor='#0E87F9'></td></tr>
		<tr class='ht'>
			<td>상품코드</td>
			<td class='it_id'><input type="text" name='it_id' value='<?=$oneday_data['it_id'];?>'/><?=$oneday_data['it_id'];?><button onclick="item_search(); return false;">상품선택</button></td>
			<td>실제상품코드</td>
			<td class='l_it_id'><?=$oneday_data['l_it_id'];?></td>
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
			<td>출력재고</td>
			<td><?=$oneday_data['real_qty'] * $oneday_data['multiplication']?></td>
			<td>현재 판매수량</td>
			<td><?=$oneday_data['order_cnt'];?></td>
		</tr>
		<tr class='ht'>
			<td>1일 구매제한 수량</td>
			<td><input type="text" name='sale_limit' value='<?=$oneday_data['sale_limit']?>' placeholder='기본 2개로 설정됩니다.'></td>
            <td>정렬순서</td>
            <td><input type="text" name="sort" value="<?php echo $oneday_data['sort'] ? $oneday_data['sort'] : 1?>" <?php if($_GET['uid']) { echo "disabled"; }?> style="width:50px;"></td>
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
			<td>오플판매가격</td>
			<td class='ople_amount'><?=$ople_price?></td>
			<td>iherb판매가격</td>
			<td class='iherb_amount'>
                <input type="text" name="iherb_amount" value="<?=$iherb_amount?>">
            </td>
		</tr>
		<tr class='ht'>
			<td>판매가격(USD)</td>
			<td><input type="text" name='price_usd' value='<?=$oneday_data['price_usd']?>' /></td>
			<td>msrp</td>
			<td>
				<input type='text' name='msrp' value='<?=$oneday_data['msrp']?>' disabled />
				<input type='checkbox' name='msrp_check' value='T' />입력
			</td>
		</tr>
		<tr class='ht'>
			<td>원데이 세일 이미지 표시</td>
			<td><input type="checkbox" name='goodday_fg' value='1' <?=($oneday_data['goodday_fg'] == '1') ? "checked":""?> />(체크시 상품 상세페이지에 굿데이 세일 이미지가 노출)</td>
			<td colspan="2"><strong style="color:red;">모든 굿데이 상품은 종료 후 일반상품으로 전환됩니다.</strong></td>
		</tr>
		<tr class='ht'>
			<td>굿데이 설명</td>
			<td colspan='3'>
				<textarea name="description" rows="2" style='width:100%;'><?=$oneday_data['description']?></textarea>
			</td>
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
		<input type='button' class='btn1' accesskey='l' value='  목  록  ' onclick="document.location.href='./oneday_new.php';">
	</p>
</form>
<script type="text/javascript">
	
	$('input[name=msrp_check]').click(function(){
		var	chk	= $(this).prop('checked');
		if(chk){
			$('input[name=msrp]').attr('disabled',false);
		}else{
			$('input[name=msrp]').attr('disabled',true);
		}
	});
	
	function item_search(){
		var it_id	= $('input[name=it_id]').val();
	
		if(it_id == ''){
			alert('상품코드를 입력해 주세요');
			return false;
		}
	
		$.ajax({
			url			: '<?=$_SERVER['PHP_SELF'];?>',
			type		: 'post',
			datatype	: 'json',
			data		: {
					'mode'	: 'item_search',
					'it_id'	: it_id,
			},
			success		: function ( result ) {
					var rtn_json = $.parseJSON(result)
					$('.it_name').text(rtn_json['it_name']);
					$('.ople_amount').text(rtn_json['it_amount']);
					if(rtn_json['iherb_amount']){
						$('.iherb_amount').text(rtn_json['iherb_amount']);
					}else{
						$('.iherb_amount').text('정보가없습니다.');
					}
					if(rtn_json['msrp']){
						$('input[name=msrp]').val(rtn_json['msrp']);
						
					}
			}
		});
		$('.l_it_id').text(it_id);
	}

	function submit_chk(){
		var st_dt = Number($('input[name=st_dt]').val());
		var en_dt = Number($('input[name=en_dt]').val());

		var date = new Date();
		var y = date.getFullYear();
		var m = date.getMonth() + 1;
		var d = date.getDate();

		if(String(m).length < 2){
			m = '0' + String(m);
		}
		if(String(d).length < 2){
			d = '0' + String(d);
		}

		var ymd = Number(String(y) + String(m) + String(d));

		<?php if(!$oneday_data['uid']){ ?>
		if(st_dt < ymd){
			alert('시작일은 현재시간보다 과거로 설정할 수 없습니다.');
			$('input[name=st_dt]').focus();
			return false;
		}
		if(en_dt < ymd){
			alert('종료일은 현재시간보다 과거로 설정할 수 없습니다.');
			$('input[name=en_dt]').focus();
			return false;
		}
		<?php }?>
		if(st_dt>en_dt){
			alert('시작일은 종료일보다 과거로 설정 해 주세요.');
			$('input[name=st_dt]').focus();
			return false;
		}
		if($('input[name=sale_limit]').val().trim()==''){
			$('input[name=sale_limit]').val('2');
		}
		return true;

	}



</script>

<?php
include_once ("$g4[admin_path]/admin.tail.php");


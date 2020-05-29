<?php
include_once("./_common.php");


if($_POST['s_type'] == ''){
	echo "#error";
	exit;
}


$s_type = $_POST['s_type'];
$return_arr = array();

if($s_type == '0') // 기본 1곳 입력폼
{
	$bottle_str = "";
	if($_POST['it_bottle_sum'] > 6) // 병수량 6병초과 안내문
		$bottle_str = "<tr><td height=30 colspan=2><span style='color:blue;'><b>※ 안내)</b> 주문상품의 병수량이 기준수량(6병)을 초과했기 때문에 6병씩 나누어서 배송됩니다.<br/>(현재 병수량 : {$_POST['it_bottle_sum']} 병)</span></td></tr>";

	$return_arr[] = "
	<table width='97%' align=center cellpadding=0 cellspacing=10 border=0>
	<colgroup width=140>
	<colgroup width=''>
	<tr>
		<td class=c3 align=center>받으시는 분</td>
		<td bgcolor=#FAFAFA style='padding-left:10px'>
			<table cellpadding=3>
			<colgroup width=100>
			<colgroup width=''>
			{$bottle_str}
			<tr height=30>
				<td colspan=2>
					<input type=checkbox id=same name=same onclick=\"javascript:gumae2baesong(document.forderform);\">
					<label for='same'><b>주문하시는 분과 받으시는 분의 정보가 동일한 경우 체크</b></label>&nbsp;&nbsp;<input type=button value='기존배송지찾기' title='기존배송지찾기' style='height:25px;' onclick=\"get_ship_addr();\" />
				</td>
			</tr>
			<tr>
				<td>이름</td>
				<td><input type=text name=od_b_name id=od_b_name class=ed maxlength=20></td>
			</tr>
			<tr>
				<td><u>주민등록번호</u></td>
				<td><input type=password name=od_b_jumin id=od_b_jumin size=18 maxlength=13 class=ed required itemname='받는사람 주민등록번호'>(\"-\"없이 숫자만 입력하세요) <br/><span class=warning>※주의</span> 받으시는 분 주민등록 번호를 정확히 입력해주세요. 한국관세법에 의거하여 물품 통관시 관세청 권한으로 주민등록번호를 확인합니다. 받으시는 분의 주민등록번호 오류시 통관이 지연되며, 저희 사이트는 이러한 이유에 대한 배송지연은 책임을 지지 않습니다.<br/><span style='color:blue;'>※ 주민등록번호는 배송이 완료되면 파기합니다.</span></td>
			</tr>
			<tr>
				<td>전화번호</td>
				<td><input type=text name=od_b_tel id=od_b_tel class=ed maxlength=20></td>
			</tr>
			<tr>
				<td>핸드폰</td>
				<td><input type=text name=od_b_hp id=od_b_hp class=ed maxlength=20></td>
			</tr>
			<tr>
				<td rowspan=2>주 소</td>
				<td>
					<input type=text name=od_b_zip1 id=od_b_zip1 size=3 maxlength=3 class=ed readonly>
					-
					<input type=text name=od_b_zip2 id=od_b_zip2 size=3 maxlength=3 class=ed readonly>
					<a href=\"javascript:;\" onclick=\"win_zip('forderform', 'od_b_zip1', 'od_b_zip2', 'od_b_addr1', 'od_b_addr2');\"><img
						src='{$g4[shop_img_path]}/btn_zip_find.gif' border=0 align=absmiddle></a>
					</a>
				</td>
			</tr>
			<tr>
				<td>
					<input type=text name=od_b_addr1 id=od_b_addr1 size=35 maxlength=50 class=ed readonly>
					<input type=text name=od_b_addr2 id=od_b_addr2 size=15 maxlength=50 class=ed> (상세주소)
				</td>
			</tr>
			<tr>
				<td>전하실말씀</td>
				<td><textarea name=od_memo rows=4 cols=60 class=ed></textarea></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>";

	echo implode("", $return_arr);
	exit;
}
else if($s_type == '1') // 복수배송지 입력폼. 지정할상품
{
	$sql = " select a.ct_id, a.ct_qty, a.ct_ship_os_pid, a.ct_ship_ct_qty, a.it_opt1, a.it_opt2, a.it_opt3, a.it_opt4, a.it_opt5, a.it_opt6,
		b.it_id, b.it_name, b.it_bottle_count
		from $g4[yc4_cart_table] a left join $g4[yc4_item_table] b on a.it_id  = b.it_id
		where a.on_uid = '{$_POST['tmp_on_uid']}'
		order by a.ct_id ";
	$result = sql_query($sql);
	$ct_arr = array("<table width=99% align=center cellpadding=0 cellspacing=0 border=0>
		<col width=30></col>
		<col width=50></col>
		<col></col>
		<col width=50></col>
		<col width=60></col>
		<col width=60></col>
		<tr align=center>
			<th><input type=checkbox name=chkall value=1 onclick=\"check_all(document.forderform); print_bottle_sum();\" /></th>
			<th colspan=2 style='font-size:9pt;'>상품명</th>
			<th style='font-size:9pt;'>총수량</th>
			<th style='font-size:9pt;'>남은수량</th>
			<th style='font-size:9pt;'>설정수량</th>
		</tr>
		<tr><td height=1 bgcolor=#003366 colspan=6></td></tr>");
	$chk_ship_complete = true; // 전상품 수량설정이 끝났는가?
	for($k=0; $row=sql_fetch_array($result); $k++)
	{
		// 수량처리. os_pid 처리
		$n_qty = 0;
		if($row['ct_ship_ct_qty']){
			$qty = explode("|", $row['ct_ship_ct_qty']);
			for($a=0; $a<count($qty); $a++){
				if($qty[$a] != '')
					$n_qty += (int)$qty[$a];
			}
			$n_qty = $row['ct_qty'] - $n_qty;
		}
		else
			$n_qty = $row['ct_qty'];

		if($n_qty < 1){
			$disabled = " disabled ";
			$bgcolor = " bgcolor='gray'  ";
		}else{
			$disabled = "";
			$bgcolor = "";
			$chk_ship_complete = false; // 하나라도 남았다면
		}
		$it_name = stripslashes($row['it_name']).'<br/>';
		$it_name .= print_item_options($row['it_id'], $row['it_opt1'], $row['it_opt2'], $row['it_opt3'], $row['it_opt4'], $row['it_opt5'], $row['it_opt6']);
		$ct_arr[] = "<input type=hidden name='ct_id[]' id='ct_id[$k]' value='{$row['ct_id']}' />";
		$ct_arr[] .= "<input type=hidden name='it_id[]' id='it_id[$k]' value='{$row['it_id']}' />";
		$ct_arr[] .= "<input type=hidden name='n_qty[]' id='n_qty[$k]' value='{$n_qty}' />";
		$ct_arr[] .= "<input type=hidden name='it_bottle_count[]' id='it_bottle_count[$k]' value='{$row['it_bottle_count']}' />";
		$ct_arr[] .= "
		<tr align=center onmouseover=\"this.style.backgroundColor='#dddddd';\" {$bgcolor} onmouseout=\"this.style.backgroundColor='';\">
			<td><input type=checkbox name='chk[]' value='$k' onclick=\"print_bottle_sum();\" {$disabled} /></td>
			<td>".get_it_image("{$row['it_id']}_s", 50, 50)."</td>
			<td align=left style='padding-left:3px;'>{$it_name}</td>
			<td>{$row['ct_qty']}</td>
			<td>{$n_qty}</td>
			<td><input type=text name='sel_qty[]' id='sel_qty[$k]' size=2 value='{$n_qty}' onkeyup=\"if(parseInt(this.value) > parseInt(get_id('n_qty[$k]').value)){ alert('설정한 수량이 설정가능한 남은수량보다 큽니다.\\n\\n설정수량은 남은수량을 넘지않아야 합니다.'); this.value=get_id('n_qty[$k]').value; }else if(parseInt(this.value) < 1){ alert('수량은 1이상 설정해야 합니다.'); this.value=get_id('n_qty[$k]').value; } if(this.value != '' && !/(^[0-9]+$)/.test(this.value)){ alert('숫자만 입력하십시오.'); this.value=get_id('n_qty[$k]').value; } print_bottle_sum(); \" /></td>
		</tr>
		<tr><td height=1 bgcolor=#003366 colspan=6></td></tr>";
	}
	$ct_arr[] = "</table>";

	$return_arr[] = "
	<table width='97%' align=center cellpadding=0 cellspacing=10 border=0>
	<colgroup width=140>
	<colgroup width=''>
	<tr>
		<td class=c3 align=center>받으시는 분</td>
		<td bgcolor=#FAFAFA style='padding-left:10px'>

			<fieldset style='padding:2px; width:100%;' align=center>
				<legend>배송상품지정 : [ <a href=\"javascript:;\" onclick=\"init_set();\" title='설정한 복수배송정보와 설정수량을 모두 삭제하고 처음상태로 돌립니다.'><u>초기화(복수배송정보와 설정수량을 모두 삭제하려면 여기를 클릭.)</u></a> ]</legend>
				".implode("", $ct_arr)."
				<div style='margin:8px 0; width:100%;'>※ 현재 병수량 : <span id='_dis_bottle_str_' style='font-weight:bold;'></span>&nbsp;병</div>
			</fieldset>";

			// 전상품 설정완료가 아니면
			if(!$chk_ship_complete)
			{
				$return_arr[] .= "
			<table cellpadding=3>
			<colgroup width=100>
			<colgroup width=''>
			<tr>
				<td colspan=2>
					<input type=checkbox id=same name=same onclick=\"javascript:gumae2baesong(document.forderform);\">
					<label for='same'><b>주문하시는 분과 받으시는 분의 정보가 동일한 경우 체크</b></label>&nbsp;&nbsp;<input type=button value='기존배송지찾기' title='기존배송지찾기' onclick=\"get_ship_addr();\" style='height:25px;' />
				</td>
			</tr>
			<tr>
				<td>보내는사람(업체명등)</td>
				<td><input type=text name=od_post_name id=od_post_name class=ed maxlength=20> ※ 최대 20자</td>
			</tr>
			<tr>
				<td>이름</td>
				<td><input type=text name=od_b_name id=od_b_name class=ed maxlength=20></td>
			</tr>
			<tr>
				<td><u>주민등록번호</u></td>
				<td><input type=password name=od_b_jumin id=od_b_jumin size=18 maxlength=13 class=ed>&nbsp;&nbsp;(\"-\"없이 숫자만 입력하세요) <br/><span class=warning>※주의</span> 받으시는 분 주민등록 번호를 정확히 입력해주세요. 한국관세법에 의거하여 물품 통관시 관세청 권한으로 주민등록번호를 확인합니다. 받으시는 분의 주민등록번호 오류시 통관이 지연되며, 저희 사이트는 이러한 이유에 대한 배송지연은 책임을 지지 않습니다.<br/><span style='color:blue;'>※ 주민등록번호는 배송이 완료되면 파기합니다.</span></td>
			</tr>
			<tr>
				<td>휴대전화</td>
				<td><input type=text name=od_b_hp id=od_b_hp class=ed maxlength=20></td>
			</tr>
			<tr>
				<td>기타전화</td>
				<td><input type=text name=od_b_tel id=od_b_tel class=ed maxlength=20></td>
			</tr>
			<tr>
				<td rowspan=2>주 소</td>
				<td>
					<input type=text name=od_b_zip1 id=od_b_zip1 size=3 maxlength=3 class=ed readonly>
					-
					<input type=text name=od_b_zip2 id=od_b_zip2 size=3 maxlength=3 class=ed readonly>
					<a href=\"javascript:;\" onclick=\"win_zip('forderform', 'od_b_zip1', 'od_b_zip2', 'od_b_addr1', 'od_b_addr2');\"><img
						src='{$g4[shop_img_path]}/btn_zip_find.gif' border=0 align=absmiddle></a>
					</a>
				</td>
			</tr>
			<tr>
				<td>
					<input type=text name=od_b_addr1 id=od_b_addr1 size=35 maxlength=50 class=ed readonly>
					<input type=text name=od_b_addr2 id=od_b_addr2 size=15 maxlength=50 class=ed> (상세주소)
				</td>
			</tr>
			<tr>
				<td>전하실말씀</td>
				<td><textarea name=od_memo rows=4 cols=60 class=ed></textarea></td>
			</tr>
			<tr>
				<td colspan=2 align=center><input type=button value='저장하기' title='저장하기' onclick=\"get_ship_save();\" style='height:30px;' /></td>
			</tr>
			<tr><td colspan=2><span style='color:blue;'>※ 배송지를 추가할경우, 입력하고 저장하기를 반복하면 계속 추가 입력이 가능합니다.</span></td></tr>
			</table>";

			}

		$return_arr[] .= "
		</td>
	</tr>
	</table>";

	echo implode("", $return_arr);
	exit;
}
else if($s_type == 'addr') // 배송지 주소록
{
	$sql = " from {$g4['yc4_ma_table']} where mb_id='{$member['mb_id']}' ";
	$count = sql_fetch("select count(ma_pid) as count {$sql} ");
	$return_arr[] = "
	<p style='margin:2px 0 2px 0;'>전체 : ".nf($count['count'])." &nbsp;<b>|</b>&nbsp; <a href=\"javascript:;\" onclick=\"get_id('_dis_addr_').style.display = 'none';\" title='닫기'>닫기</a>&nbsp;&nbsp;(이름을 클릭하면 적용됩니다.)</p>
	<table border=2 cellspacing=2 cellpadding=2 align='center' bordercolor='#95A3AC'  class='state_table'>
	<tr align=center>
		<td class=yalign_head width=85>이름</td>
		<td class=yalign_head width=100>휴대전화</td>
		<td class=yalign_head>주소</td>
	</tr>";
	$result = sql_query("select * {$sql} order by ma_pid desc ");
	for($k=0; $row=sql_fetch_array($result); $k++){
		$return_arr[] = "
			<tr onmouseover=\"this.style.backgroundColor='#dddddd';\" bgcolor='#FFFFFF' onmouseout=\"this.style.backgroundColor='#FFFFFF';\">
				<td class=yalign_list><a href=\"javascript:;\" title=\"적용하기\" style=\"cursor:pointer;\" onclick=\"insert_ship_addr('{$row['ma_name']}', '{$row['ma_hp']}', '{$row['ma_tel']}', '{$row['ma_zip1']}', '{$row['ma_zip2']}', '{$row['ma_addr1']}', '{$row['ma_addr2']}'); \">{$row['ma_name']}</a></td>
				<td class=yalign_list>{$row['ma_hp']}</td>
				<td> ".sprintf("(%s-%s) %s %s", $row['ma_zip1'], $row['ma_zip2'], $row['ma_addr1'], $row['ma_addr2'])."</td>
			</tr>
		";
	}
	if(!$k) $return_arr[] = "<tr class=ht><td height=100 colspan=10 align=center>자료가 없습니다.</td></tr>";
	$return_arr[] = "</table>";
	$return_arr[] = "<p style='margin:5px 0; text-align:right;'><a href=\"javascript:;\" onclick=\"get_id('_dis_addr_').style.display = 'none';\" title='닫기'>닫기</a></p>";
	echo implode("", $return_arr);
	exit;
}
else if($s_type == 'save')
{
	function rep_quot($str){
		return preg_replace('/\"/', "&quot;", stripslashes($str));
	}

	// 포스트값 위/변조 확인
	$chk = sql_fetch("select ct_id from {$g4['yc4_cart_table']} where on_uid='{$_POST['tmp_on_uid']}' ");
	if(!$chk['ct_id']){
		echo "#save_error";
		exit;
	}

	sql_query("insert into {$g4['yc4_os_table']}
		set on_uid			= '{$_POST['tmp_on_uid']}',
			mb_id			= '{$member['mb_id']}',
			od_id			= '',
			os_post_name	= '".rep_quot($_POST['od_post_name'])."',
			os_name			= '".rep_quot($_POST['od_b_name'])."',
			os_jumin		= trim('{$_POST['od_b_jumin']}'),
			os_tel			= trim('$od_b_tel'),
			os_hp			= trim('$od_b_hp'),
			os_zip1			= trim('$od_b_zip1'),
			os_zip2			= trim('$od_b_zip2'),
			os_addr1		= trim('$od_b_addr1'),
			os_addr2		= trim('$od_b_addr2'),
			os_memo			= trim('$od_memo'),
			os_datetime		= '{$g4['time_ymdhis']}',
			os_status		= '쇼핑' ");
	$os_pid = mysql_insert_id();

	for($a=0; $a<count($_POST['chk']); $a++)
	{
		$b = $_POST['chk'][$a];
		$ct = sql_fetch("select ct_id, ct_ship_os_pid, ct_ship_ct_qty, ct_ship_stock_use from {$g4['yc4_cart_table']} where on_uid='{$_POST['tmp_on_uid']}' and ct_id='{$_POST['ct_id'][$b]}' ");
		$ct_pid = explode("|", $ct['ct_ship_os_pid']);
		$ct_qty = explode("|", $ct['ct_ship_ct_qty']);
		$ct_stock = explode("|", $ct['ct_ship_stock_use']);
		$pid_arr = $qty_arr = $stock_arr = array();
		for($k=0; $k<count($ct_pid); $k++){
			if($ct_pid[$k] != ''){
				$pid_arr[] = $ct_pid[$k];
				$qty_arr[] = $ct_qty[$k];
				$stock_arr[] = $ct_stock[$k];
			}
		}
		$pid_arr[] = $os_pid;
		$qty_arr[] = $_POST['sel_qty'][$b];
		$stock_arr[] = '0'; // 재고처리를 구분하기 위해 사용됨(BUI - 배송처리시)
		sql_query("update {$g4['yc4_cart_table']}
			set ct_ship_os_pid='".implode("|", $pid_arr)."',
				ct_ship_ct_qty='".implode("|", $qty_arr)."',
				ct_ship_stock_use='".implode("|", $stock_arr)."'
			where on_uid='{$_POST['tmp_on_uid']}' and ct_id='{$_POST['ct_id'][$b]}' "); // os_pid, 설정수량 저장, 구분자(|)로 들어감
		unset($pid_arr);
		unset($qty_arr);
		unset($stock_arr);
	}

	// 복수배송지, 상품정보
	$return_arr[] = get_fui_ship_item($_POST['tmp_on_uid'], $member['mb_id'], $_POST['send_cost']);
	echo implode("", $return_arr);
	exit;
}
else if($s_type == 'del_os')
{
	sql_query("delete from {$g4['yc4_os_table']} where os_pid='{$_POST['os_pid']}' and on_uid='{$_POST['tmp_on_uid']}' and os_status='쇼핑' ");

	$ct_sql = sql_query("select ct_id, ct_ship_os_pid, ct_ship_ct_qty from {$g4['yc4_cart_table']} where on_uid='{$_POST['tmp_on_uid']}' and ct_ship_os_pid like '%{$_POST['os_pid']}%' and ct_status='쇼핑' order by ct_id ");
	for($k=0; $row=sql_fetch_array($ct_sql); $k++)
	{
		$ct_pid = explode("|", $row['ct_ship_os_pid']);
		$ct_qty = explode("|", $row['ct_ship_ct_qty']);
		$ct_stock = explode("|", $ct['ct_ship_stock_use']);
		$pid_arr = $qty_arr = $stock_arr = array();
		for($a=0; $a<count($ct_pid); $a++){
			if($ct_pid[$a] != '' && $ct_pid[$a] != $_POST['os_pid']){
				$pid_arr[] = $ct_pid[$a];
				$qty_arr[] = $ct_qty[$a];
				$stock_arr[] = $ct_stock[$k];
			}
		}
		sql_query("update {$g4['yc4_cart_table']}
			set ct_ship_os_pid='".implode("|", $pid_arr)."',
				ct_ship_ct_qty='".implode("|", $qty_arr)."',
				ct_ship_stock_use='".implode("|", $stock_arr)."'
			where ct_id='{$row['ct_id']}' "); // os_pid, 설정수량 저장, 구분자(|)로 들어감
		unset($pid_arr);
		unset($qty_arr);
		unset($stock_arr);
	}
	$return = get_fui_ship_item($_POST['tmp_on_uid'], $member['mb_id'], $_POST['send_cost']);
	echo $return;
	exit;
}
else if($s_type == 'init')
{
	sql_query("delete from {$g4['yc4_os_table']} where on_uid='{$_POST['tmp_on_uid']}' and os_status='쇼핑' ");
	sql_query("update {$g4['yc4_cart_table']} set ct_ship_os_pid='', ct_ship_ct_qty='', ct_ship_stock_use='' where on_uid='{$_POST['tmp_on_uid']}' and ct_status='쇼핑' ");
	$return = get_fui_ship_item($_POST['tmp_on_uid'], $member['mb_id']);
	echo $return;
	exit;
}
else if($s_type == 'view')
{
	$sql = " select a.ct_id, a.ct_qty, a.ct_ship_os_pid, a.ct_ship_ct_qty, a.it_opt1, a.it_opt2, a.it_opt3, a.it_opt4, a.it_opt5, a.it_opt6,
		b.it_id, b.it_name, b.it_bottle_count
		from $g4[yc4_cart_table] a left join $g4[yc4_item_table] b on a.it_id  = b.it_id
		where a.on_uid = '{$_POST['tmp_on_uid']}' and ct_ship_os_pid like '%{$_POST['os_pid']}%'
		order by a.ct_id ";
	$result = sql_query($sql);
	$return_arr[] = "<p style='margin:2px 0 2px 0;'><a href=\"javascript:;\" onclick=\"$('#_dis_view_item_').hide(); $('#_dis_view_item_').empty('');\" title='닫기'>닫기</a></p>";
	$return_arr[] = "<table border=2 cellspacing=0 cellpadding=2 align='center' bordercolor='#95A3AC'  class='state_table'>
		<col width=50></col>
		<col></col>
		<col width=70></col>
		<tr align=center>
			<th class=yalign_head colspan=2 style='font-size:9pt;'>상품명</th>
			<th class=yalign_head style='font-size:9pt;'>설정수량</th>
		</tr>";
	$bottle_sum = 0;
	for($k=0; $row=sql_fetch_array($result); $k++)
	{
		$n_qty = 0;
		// 수량처리. os_pid 처리
		if($row['ct_ship_ct_qty']){
			$qty = explode("|", $row['ct_ship_ct_qty']);
			$pid = explode("|", $row['ct_ship_os_pid']);
			for($a=0; $a<count($pid); $a++){
				if($pid[$a] == $_POST['os_pid'] && $qty[$a] != '')
					$n_qty = (int)$qty[$a];
			}
		}
		$it_name = stripslashes($row['it_name']).'<br/>';
		$it_name .= print_item_options($row['it_id'], $row['it_opt1'], $row['it_opt2'], $row['it_opt3'], $row['it_opt4'], $row['it_opt5'], $row['it_opt6']);
		$bottle_sum += $row['it_bottle_count'] * $n_qty;
		$return_arr[] = "
		<tr align=center onmouseover=\"this.style.backgroundColor='#dddddd';\" {$bgcolor} onmouseout=\"this.style.backgroundColor='';\">
			<td>".get_it_image("{$row['it_id']}_s", 50, 50)."</td>
			<td align=left style='padding-left:3px;'>{$it_name}</td>
			<td>{$n_qty}</td>
		</tr>";
	}
	$return_arr[] = "<tr><td height=30 colspan=10>※ 병수량합계 : <b>{$bottle_sum} 병</b></td></tr>";
	if(!$k) $return_arr[] = "<tr><td height=100 colspan=10 align=center>자료가 없습니다.</td></tr>";
	$return_arr[] = "</table>";
	$return_arr[] = "<p style='margin:5px 0; text-align:right;'><a href=\"javascript:;\" onclick=\"$('#_dis_view_item_').hide(); $('#_dis_view_item_').empty('');\" title='닫기'>닫기</a></p>";
	echo implode("", $return_arr);
	exit;
}
?>
<?php 
/*
----------------------------------------------------------------------
file name	 : ihappy_item_manual_reg.php
comment		 : 아이해피 상품 수동등록
date		 : 2015-03-06
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/
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

$sub_menu = "300900";
include_once("./_common.php");

if($_POST['mode']){

	switch($_POST['mode']){

		case 'insert':
			$rs = sql_query("select count(*) as cnt from yc4_best_item_manual_ihappy where ca_id='$_POST[ca_id]' and it_id='$_POST[it_id]'");
			$data = sql_fetch_array($rs);

			if($data['cnt'] > 0){
				alert('이미 등록된 상품입니다.');
				exit;
			}

			sql_query("insert into yc4_best_item_manual_ihappy (it_id,ca_id,create_dt,sort) values ('$_POST[it_id]','$_POST[ca_id]',NOW(),'$_POST[sort]')");


			alert('저장되었습니다.','ihappy_item_manual_reg.php?ca_id='.$_POST['ca_id']);
			break;

		case 'sort_edit':

			sql_query("update yc4_best_item_manual_ihappy set sort='$_POST[sort]' where ca_id='$_POST[ca_id]' and it_id='$_POST[it_id]'");


			alert('수정되었습니다.','ihappy_item_manual_reg.php?ca_id='.$_POST['ca_id']);

			break;

		case 'delete':

			sql_query("delete from yc4_best_item_manual_ihappy where ca_id='$_POST[ca_id]' and it_id='$_POST[it_id]'");


			alert('삭제되었습니다.','ihappy_item_manual_reg.php?ca_id='.$_POST['ca_id']);
		break;
	}
}


auth_check($auth[$sub_menu], "r");

$g4['title'] = "아이해피 상품 수동등록";
include_once ($g4['admin_path']."/admin.head.php");


if($_GET['ca_id'] == '') $_GET['ca_id'] = 10;


$best_item_rs = sql_query("
	select b.it_id,b.sort,it_name from yc4_best_item_manual_ihappy b, yc4_item i where i.it_id=b.it_id and b.ca_id='$_GET[ca_id]' order by b.sort asc
");

# 구 분류 로드 끝 #

$no = 1;
while($item_data = sql_fetch_array($best_item_rs)){
	$list_tr .= "
		<tr >
			<td class='ca_id' align='center'>".$no."</td>
			<td align='left' style='padding-left:10px;'>".utf8_strcut($item_data['it_name'],80)."</td>
			<td><input type='hidden' name='it_id_$no' value='$item_data[it_id]'/></td>
			<td>
			<input type='text' name='sort_$no' value='$item_data[sort]'/>
			<input type='button' value='순서변경' onclick='change_sort($no)'/>
			<input type='button' value='삭제' onclick='del_mitem($no)'/>
			</td>
		</tr>

	";
	$no++;
}
?>
<style type="text/css">
a.ca_tab {border:1px solid black; padding:5px 30px;color:black;}
a.sel_ca_id {font-weight:bold;color:red}
</style>
<a href="./ihappy_item_manual_reg.php?ca_id=10" class="ca_tab <?php if($_GET['ca_id'] == '10') echo 'sel_ca_id'; ?>">건강</a>
<a href="./ihappy_item_manual_reg.php?ca_id=20" class="ca_tab <?php if($_GET['ca_id'] == '20') echo 'sel_ca_id'; ?>">뷰티</a>
<a href="./ihappy_item_manual_reg.php?ca_id=30" class="ca_tab <?php if($_GET['ca_id'] == '30') echo 'sel_ca_id'; ?>">생활</a>
<a href="./ihappy_item_manual_reg.php?ca_id=40" class="ca_tab <?php if($_GET['ca_id'] == '40') echo 'sel_ca_id'; ?>">식품</a>

<form name="manual_frm" method="POST" onsubmit="return chkForm(this)">
	<input type="hidden" name="ca_id" value="<?php echo $_GET['ca_id']; ?>"/>
	<input type="hidden" name="mode" value="insert"/>
	<table width="40%">
		<tr>
			<td>it_id</td>
			<td>sort</td>
			<td></td>
		</tr>
		<tr>
			<td><input type="text" name="it_id" /></td>
			<td><input type="text" name="sort" /></td>
			<td><input type="submit" value="저장" /></td>
		</tr>
	</table>
</form>

<form name="edit_frm" method="post">
	<input type="hidden" name="ca_id" value="<?php echo $_GET['ca_id']; ?>"/>
	<input type="hidden" name="mode" value=""/>
	<input type="hidden" name="it_id" value="" />
	<input type="hidden" name="sort" value="" />
</form>

<table width='100%'>
	<tr class='ht' align='center'>
		<td>No</td>
		<td>아이템명</td>
		<td>sort</td>
		<td>관리</td>
	</tr>
	<?=$list_tr;?>
</table>
<script>
function change_sort(no){
	var frm = document.edit_frm;
	frm.mode.value = 'sort_edit';
	frm.it_id.value = $("input[name=it_id_" + no + "]").val();
	frm.sort.value = $("input[name=sort_" + no + "]").val();
	frm.submit();
}

function del_mitem(no){
	if(confirm("정말로 삭제하시겠습니까?")){
		var frm = document.edit_frm;
		frm.mode.value = 'delete';
		frm.it_id.value = $("input[name=it_id_" + no + "]").val();
		frm.submit();
	} return false;
}

function chkForm(frm){
	if(frm.it_id.value==""){
		alert("it_id를 입력하세요.");
		frm.it_id.focus();
		return false;
	}
	if(frm.sort.value==""){
		alert("sort를 입력하세요.");
		frm.sort.focus();
		return false;
	}

	return true;

}
</script>
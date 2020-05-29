<?
$sub_menu = "400210";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4['title'] = "상품분류 변환(미적용 상품)";
//include_once ($g4['admin_path']."/admin.head.php");
include_once $g4['full_path'].'/head.sub.php';

if($_POST['mode']){

	include 'category_item_lib.php';

	$it_id_arr = array();

	if(is_array($_POST['it_id'])){
		$it_id_arr = $_POST['it_id'];
	} else {
		array_push($it_id_arr,$_POST['it_id']);
	}

	$insert_cnt = category_item_insert3($_POST['new_cate'] ,$it_id_arr);

	alert("카테고리 상품 등록이 완료되었습니다.(".$insert_cnt."건)","./category_item_no2.php?old_cate=$_POST[old_cate]");
}

## 기존 카테고리 selectbox 처리 시작 ##

$old_cate_rs = sql_query("select ca_id,ca_name from yc4_category order by ca_id asc");

while($data = sql_fetch_array($old_cate_rs)){

	$blank = strlen($data['ca_id']) > 2 ? '&nbsp;&nbsp;' : '';

	$selected = $data['ca_id'] === $_GET['old_cate'] ? 'selected' : '';

	$old_cate_options .= "<option value='$data[ca_id]' $selected>$blank $data[ca_name]</option>";
}

## 기존 카테고리 selectbox 처리 끝 ##


## 신규 카테고리 selectbox 처리 시작 ##

$old_cate_rs = sql_query("select ca_id,ca_name from yc4_category_new order by ca_id asc");

while($data = sql_fetch_array($old_cate_rs)){

	$blank = str_repeat('&nbsp;&nbsp;', (strlen($data['ca_id'])/2)-1);

	switch(strlen($data['ca_id'])){
		case '2': $option_style = "style='color:blue;font-weight:bold;'"; break;
		case '4': $option_style = "style='font-weight:bold;'"; break;
		default : $option_style = ''; break;
	}

	$new_cate_options .= "<option value='$data[ca_id]' $selected $option_style>$blank $data[ca_name]</option>";
}

## 신규 카테고리 selectbox 처리 끝 ##


$total_rs = sql_query("select count(i.it_id) as cnt from yc4_item i left outer join yc4_category_item o ON o.it_id = i.it_id where  isnull(o.it_id) and i.it_use = 1 and i.ca_id not in( 'h0' ,'u0')");
$total_info = sql_fetch_array($total_rs);

if($_GET['old_cate']) $cate_where = " AND (left(i.ca_id,".strlen($_GET['old_cate']).")='$_GET[old_cate]' or left(i.ca_id2,".strlen($_GET['old_cate']).")='$_GET[old_cate]' or left(i.ca_id3,".strlen($_GET['old_cate']).")='$_GET[old_cate]' or left(i.ca_id4,".strlen($_GET['old_cate']).")='$_GET[old_cate]' or left(i.ca_id5,".strlen($_GET['old_cate']).")='$_GET[old_cate]' ) ";

if($_GET['it_name']) $cate_where .= " AND i.it_name like '%$_GET[it_name]%' ";
if($_GET['it_maker']) $cate_where .= " AND i.it_maker like '%$_GET[it_maker]%' ";

$cnt_rs = sql_query("select count(i.it_id) as cnt from yc4_item i left outer join yc4_category_item o ON o.it_id = i.it_id where  isnull(o.it_id)  and i.it_use = 1 and i.ca_id not in( 'h0' ,'u0') $cate_where");
$cnt_info = sql_fetch_array($cnt_rs);

$page = $_GET['page'] ? $_GET['page'] : 1;

$it_cnt = $cnt_info['cnt'];
$rows = $config['cf_page_rows'];
$total_page  = ceil($it_cnt / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$it_qry = sql_query($a="
	SELECT i.it_id, i.it_name, i.it_maker, i.it_amount, i.it_discontinued
	FROM yc4_item i left outer join yc4_category_item o ON o.it_id = i.it_id $cate_where
	where  isnull(o.it_id)  and i.it_use = 1 and i.ca_id not in( 'h0' ,'u0') $cate_where limit $from_record,$rows
");

while($row = sql_fetch_array($it_qry)){

	$list .= "<tr>
			<td><input type='checkbox' name='it_id[]' value='".$row['it_id']."'/></td>
			<td>".$row['it_id']."</td>
			<td>".$row['it_maker']."</td>
			<td>".$row['it_name']."</td>
			<td align='right'>".number_format($row['it_amount'])."</td>
			<td align='center'>".($row['it_discontinued'] == 1 ? "o":"x" )."</td>
		</tr>";

}


?>

<a href="category_item_no2.php">미적용 상품</a>(<?php echo $total_info['cnt']; ?>)<br/><br/>
<form name="frm">
<input type="hidden" name="mode" value="convert"/>
<table width='100%'>
	<tr>
		<td width="55%" align="left">
		<select name="old_cate" onchange="location.href='category_item_no2.php?old_cate='+this.value">
			<option value="">기존 카테고리 선택</option>
			<?php echo $old_cate_options; ?>
		</select>&nbsp;<input type="text" name="it_name" value="<?php echo stripcslashes($_GET['it_name']);?>" placeholder="상품명 입력" style="width:90px;">&nbsp;<input type="text" name="it_maker" value="<?php echo stripcslashes($_GET['it_maker']);?>" placeholder="제조사 입력" style="width:90px;">&nbsp;<input type="button" value="검색" onclick="seach_it_name()"/>
		</td>
		<td width="45%" align="right">
		<select name="new_cate">
			<option value="">적용할 NEW 카테고리 선택</option>
			<?php echo $new_cate_options; ?>
		<select>&nbsp;
		<input type="button" value="적용" onclick="save_item_category()"/>
		</td>
	</tr>
</table>
<table width='100%'>
	<thead>
		<th>
			<input type="checkbox" class='chk_all'/>
		</th>
		<th>상품코드</th>
		<th>제조사</th>
		<th>상품명</th>
		<th>가격</th>
		<th>단종여부</th>
	</thead>
	<tbody>

		<?=$list;?>
	</tbody>
</table>
<?
$gparam = $_GET;
unset($gparam['page']);

echo get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?".http_build_query($gparam)."&page=");
?>
</form>

<script>
$(document).ready(function(){
	$(".chk_all").click(function(){
		if($(this).is(":checked")){
			$(":checkbox[name^=it_id]").attr("checked","checked");
		} else {
			$(":checkbox[name^=it_id]").attr("checked",false);
		}
	});
});

function seach_it_name(){

	location.href = "category_item_no2.php?" + $("form[name=frm]").serialize();
}

function save_item_category(){

	var chk_num = 0;

	$(":checkbox[name^=it_id]").each(function(){
		if($(this).is(":checked")) chk_num++;
	});
	if(chk_num < 1){
		alert("적용할 상품을 선택해주세요.");
		return false;
	}

	if($("select[name=new_cate]").val() == ""){
		alert("적용할 신규 카테고리를 선택해주세요.");
		return false;
	}

	$("form[name=frm]").attr("method","POST");

	$("form[name=frm]").submit();
}
</script>

<?
//include_once ($g4['admin_path']."/admin.tail.php");
include_once $g4['full_path'].'/tail.sub.php';
?>
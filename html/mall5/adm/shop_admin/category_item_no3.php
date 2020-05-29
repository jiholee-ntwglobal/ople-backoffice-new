<?
$sub_menu = "400210";
include_once("./_common.php");

if($member['mb_id'] != 'ople_md' && $member['mb_id'] != 'ople_manager'){
    auth_check($auth[$sub_menu], "r");
}



function category_item_insert2($ca_arr,$it_id_arr){

	if(is_array($ca_arr) && is_array($it_id_arr)){
		$n=0;
		foreach($it_id_arr as $it_id){
			foreach($ca_arr as $cate){
				//$qry .= (($qry) ? ", ":" values ")."('".$cate."', '".$it_id."') ";
				$duplicate_info = sql_fetch("select count(*) as cnt from yc4_category_item where ca_id='$cate' and it_id='$it_id'");

				if($duplicate_info['cnt'] < 1){
					//echo "insert into yc4_category_item (ca_id,it_id) values ('".$cate."', '".$it_id."')<br/>".PHP_EOL;
					sql_query("insert into yc4_category_item (ca_id,it_id) values ('".$cate."', '".$it_id."')");
					$n++;
				}
			}
		}

		return $n;
	}

}

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

	$new_cate_arr = array();

	if(is_array($_POST['new_cate'])){
		$new_cate_arr = $_POST['new_cate'];
	} else {
		array_push($new_cate_arr,$_POST['new_cate']);
	}




	$insert_cnt = category_item_insert2($new_cate_arr ,$it_id_arr);

	alert("카테고리 상품 등록이 완료되었습니다.(".$insert_cnt."건)","./category_item_no3.php?old_cate=$_POST[old_cate]&page=$_POST[page]");
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
$third_cate = array('10','11','13','19','20','21','22','23','24','25','26','30','31','32','33','40','41','42','51','52');
$n=0;
while($data = sql_fetch_array($old_cate_rs)){

	$blank = str_repeat('&nbsp;&nbsp;', (strlen($data['ca_id'])/2)-1);

	switch(strlen($data['ca_id'])){
		case '2': $style = "style='background-color:red;color:white;font-weight:bold;'"; $blank = '&nbsp;&nbsp;'; $title_flag = true; break;
		case '4': $style = "style='color:blue;font-weight:bold;'";  $blank = '&nbsp;&nbsp;&nbsp;&nbsp;'; $title_flag = (in_array(substr($data['ca_id'],0,2),$third_cate))? true : false; break;
		default : $style = ''; $blank = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; $title_flag = false; break;
	}

	if($title_flag){
		for($j=0;$j<$n%3;$j++){
			$new_cate_contents .= "<td></td>";
		}
		if($n%3!=2)	$new_cate_contents .= "</tr>";
		$new_cate_contents .= "<tr>";

	} else {
		if($n%3==0)	$new_cate_contents .= "<tr>";
	}

	$new_cate_contents .= "<td>$blank<input type='checkbox' name='new_cate[]' value='$data[ca_id]'><span $style>$blank $data[ca_name]</span>";

	if($title_flag){
		$new_cate_contents .= "<td></td><td></td></tr>";
	} else {
		if($n%3==2)	$new_cate_contents .= "</tr>";
	}


	$n++;
	if($title_flag){
		$n=0;
	}
}

## 신규 카테고리 selectbox 처리 끝 ##


$total_rs = sql_query("select count(i.it_id) as cnt from yc4_item i left outer join yc4_category_item o ON o.it_id = i.it_id where  isnull(o.it_id) and i.it_use = 1 and i.ca_id not in( 'h0' ,'u0')");
$total_info = sql_fetch_array($total_rs);

if($_GET['old_cate']) $cate_where = " AND (left(i.ca_id,".strlen($_GET['old_cate']).")='$_GET[old_cate]' or left(i.ca_id2,".strlen($_GET['old_cate']).")='$_GET[old_cate]' or left(i.ca_id3,".strlen($_GET['old_cate']).")='$_GET[old_cate]' or left(i.ca_id4,".strlen($_GET['old_cate']).")='$_GET[old_cate]' or left(i.ca_id5,".strlen($_GET['old_cate']).")='$_GET[old_cate]' ) ";

if($_GET['str_it_id']) $cate_where .= " AND i.it_id = '$_GET[str_it_id]' ";
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
echo $a;

while($row = sql_fetch_array($it_qry)){

	$list .= "<tr>
			<td><input type='checkbox' name='it_id[]' value='".$row['it_id']."'/></td>
			<td>".$row['it_id']."</td>
			<td>".$row['it_maker']."</td>
			<td>".$row['it_name']."</td>
		</tr>";

}


?>

<a href="category_item_no3.php">미적용 상품</a>(<?php echo $total_info['cnt']; ?>)<br/><br/>
<form name="frm">
<input type="hidden" name="mode" value="convert"/>
<input type="hidden" name="page" value="<?php echo $page; ?>"/>
<div>
<select name="old_cate" onchange="location.href='category_item_no3.php?old_cate='+this.value">
	<option value="">기존 카테고리 선택</option>
	<?php echo $old_cate_options; ?>
</select>
<input type="text" name="str_it_id" value="<?php echo stripcslashes($_GET['str_it_id']);?>" placeholder="상품코드 입력" style="width:90px;">
<input type="text" name="it_name" value="<?php echo stripcslashes($_GET['it_name']);?>" placeholder="상품명 입력" style="width:90px;">
<input type="text" name="it_maker" value="<?php echo stripcslashes($_GET['it_maker']);?>" placeholder="제조사 입력" style="width:90px;">
<input type="button" value="검색" onclick="seach_it_name()"/>
</div>

<table width='100%'>
	<tr>
		<td width='50%' valign='top'>
			<table width='100%'>
				<thead>
					<th>
						<input type="checkbox" class='chk_all'/>
					</th>
					<th>상품코드</th>
					<th>제조사</th>
					<th>상품명</th>

				</thead>
				<tbody>

					<?=$list;?>
				</tbody>
			</table>
		</td>
		<td width='50%' valign='top'>
			<div style="overflow-y:scroll;height:700px;">
				<table width='100%' border='1'>
				<?php echo $new_cate_contents; ?>
				</table>
				</div>
				<table width='100%'>
					<tr>
						<td align="center"><input type="button" style="width:200px;height:60px;" value="적용" onclick="save_item_category()"/></td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
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
	frm.page.value = 1;
	location.href = "category_item_no3.php?" + $("form[name=frm]").serialize();
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

	var chk_flag = false;

	$("input[name^=new_cate]").each(function(){
		if($(this).is(":checked")){
			chk_flag = true;
			return;
		}

	});

	if(!chk_flag){
		alert("적용할 신규 카테고리를 체크해주세요.");
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
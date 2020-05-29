<?
$sub_menu = "400210";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

function category_item_insert2($ca_arr,$it_id_arr){

	if(is_array($ca_arr) && is_array($it_id_arr)){
		$n=0;
		foreach($it_id_arr as $it_id){
			foreach($ca_arr as $cate){
				//$qry .= (($qry) ? ", ":" values ")."('".$cate."', '".$it_id."') ";
				$duplicate_info = sql_fetch("select count(*) as cnt from yc4_category_item_tmp where ca_id='$cate' and it_id='$it_id'");

				if($duplicate_info['cnt'] < 1){
					//echo "insert into yc4_category_item (ca_id,it_id) values ('".$cate."', '".$it_id."')".PHP_EOL;
					sql_query("insert into yc4_category_item_tmp (ca_id,it_id) values ('".$cate."', '".$it_id."')");
					$n++;
				}
			}
		}
		
		return $n;
	}
	
}


$g4['title'] = "다중 상품분류 변환";

include_once ($g4['path']."/head.sub.php");

//include_once ($g4['admin_path']."/admin.head.php");

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

	alert("카테고리 상품 등록이 완료되었습니다.(".$insert_cnt."건)","./category_item_new.php?old_cate=$_POST[old_cate]");
}

## 기존 카테고리 selectbox 처리 시작 ##

$old_cate1_rs = sql_query("select * from yc4_station order by sort asc limit 0,10");
while($data = sql_fetch_array($old_cate1_rs)){
	$cate1_info[$data['name']] = $data['s_id'];
}

$old_cate2_rs = sql_query("
					select sc.s_id, cn.ca_id, cn.ca_name from shop_category sc
					left outer join yc4_category_new cn on sc.ca_id = cn.ca_id
					order by sc.s_id,sc.sort asc");

while($data = sql_fetch_array($old_cate2_rs)){
	if(!is_array($cate2_info['_'.$data['s_id']])) $cate2_info['_'.$data['s_id']] = array();

	array_push($cate2_info['_'.$data['s_id']],array('ca_id'=>$data['ca_id'], 'ca_name'=>$data['ca_name']));

}

$old_cate3_rs = sql_query("select ca_id,ca_name from yc4_category_new c where length(ca_id) >2 order by ca_id asc");
while($data = sql_fetch_array($old_cate3_rs)){

	if(!is_array($cate3_info['_'.substr($data['ca_id'],0,2)])) $cate3_info['_'.substr($data['ca_id'],0,2)] = array();

	array_push($cate3_info['_'.substr($data['ca_id'],0,2)],array('ca_id'=>$data['ca_id'], 'ca_name'=>$data['ca_name']));

}

foreach($cate1_info as $cate1_name => $cate1_id){

	$selected = ($cate1_id == $_GET['old_cate']) ? 'selected' : '';

	if($cate1_id == $_GET['old_cate']){
		$category_where = " and i.it_id in (select ci.it_id from yc4_category_item ci where substring(ci.ca_id,1,2) in (select isc.ca_id from shop_category isc  where isc.s_id='$cate1_id')) ";
	}

	$old_cate_options .= "<option value='$cate1_id' style='font-weight:bold;color:red;' $selected>$cate1_name</option>".PHP_EOL;

	if(is_array($cate2_info['_'.$cate1_id])){

		foreach($cate2_info['_'.$cate1_id] as $info_arr){

			$selected = ($info_arr['ca_id'] == $_GET['old_cate']) ? 'selected' : '';

			if($info_arr['ca_id'] == $_GET['old_cate']){
				$category_where = " and i.it_id in (select ci.it_id from yc4_category_item ci where substring(ci.ca_id,1,2) ='$info_arr[ca_id]') ";
			}

			$old_cate_options .= "<option value='$info_arr[ca_id]' style='color:blue;' $selected>&nbsp;&nbsp;$info_arr[ca_name]</option>".PHP_EOL;

			if(is_array($cate3_info['_'.$info_arr['ca_id']])){
				foreach($cate3_info['_'.$info_arr['ca_id']] as $info_arr2){
					$selected = ($info_arr2['ca_id'] == $_GET['old_cate']) ? 'selected' : '';

					if($info_arr2['ca_id'] == $_GET['old_cate']){
						$category_where = " and i.it_id in (select ci.it_id from yc4_category_item ci where ci.ca_id ='$info_arr2[ca_id]') ";
					}

					$blank = str_repeat('&nbsp;&nbsp;', ((strlen($info_arr2['ca_id'])-2)/2));

					$old_cate_options .= "<option value='$info_arr2[ca_id]' $selected>&nbsp;&nbsp;$blank$info_arr2[ca_name]</option>".PHP_EOL;
				}
			}

		}

	}
}

## 기존 카테고리 selectbox 처리 끝 ##


## 신규 카테고리 selectbox 처리 시작 ##

$old_cate_rs = sql_query("select ca_id,ca_name from yc4_category_new_tmp order by ca_id asc");
$third_cate = array('12','14','17');
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



$total_rs = sql_query("select count(i.it_id) as cnt from yc4_item i where   i.it_use = 1 and i.ca_id not in( 'h0' ,'u0')");
$total_info = sql_fetch_array($total_rs);


if($category_where) $cate_where = $category_where;
else {
	if($_GET['old_cate']) $cate_where = " AND left(i.ca_id,".strlen($_GET['old_cate']).")='$_GET[old_cate]' ";
}

if($_GET['it_name']) $cate_where .= " AND i.it_name like '%$_GET[it_name]%' ";
if($_GET['it_maker']) $cate_where .= " AND i.it_maker like '%$_GET[it_maker]%' ";

$cnt_rs = sql_query("select count(i.it_id) as cnt from yc4_item i  where  i.it_use = 1 and i.ca_id not in( 'h0' ,'u0') $cate_where");
$cnt_info = sql_fetch_array($cnt_rs);

$page = $_GET['page'] ? $_GET['page'] : 1;

$it_cnt = $cnt_info['cnt'];
$rows = $config['cf_page_rows'];
$total_page  = ceil($it_cnt / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


$it_qry = sql_query($a="
	SELECT i.it_id, i.it_name, i.it_maker, i.it_amount, i.it_discontinued
	FROM yc4_item i 
	where  i.it_use = 1 and i.ca_id not in( 'h0' ,'u0') $cate_where limit $from_record,$rows
");

while($row = sql_fetch_array($it_qry)){

	$list .= "<tr>
			<td><input type='checkbox' name='it_id[]' value='".$row['it_id']."'/></td>
			<td>".$row['it_maker']."</td>
			<td>".$row['it_name']."</td>
			<td align='right'>".number_format($row['it_amount'])."</td>
			<td align='center'>".($row['it_discontinued'] == 1 ? "o":"x" )."</td>
		</tr>";

}


?>

<a href="category_item_new.php">전체 상품</a>(<?php echo $total_info['cnt']; ?>)<br/><br/>
<form name="frm">
<input type="hidden" name="mode" value="convert"/>
<table width='100%'>
	<tr>
		<td width="55%" align="left">
		<select name="old_cate" onchange="location.href='category_item_new.php?old_cate='+this.value">
			<option value="">기존 카테고리 선택</option>
			<?php echo $old_cate_options; ?>
		</select>&nbsp;<input type="text" name="it_name" value="<?php echo stripcslashes($_GET['it_name']);?>" placeholder="상품명 입력" style="width:90px;">&nbsp;<input type="text" name="it_maker" value="<?php echo stripcslashes($_GET['it_maker']);?>" placeholder="제조사 입력" style="width:90px;">&nbsp;<input type="button" value="검색" onclick="seach_it_name()"/>
		</td>
		<td width="45%" align="right">
		</td>
	</tr>
</table>
<table width="100%">
	<tr>
		<td width="60%" valign="top">
		
		<table width='100%'>
			<thead>
				<th>
					<input type="checkbox" class='chk_all'/>
				</th>
				<th>제조사</th>
				<th>상품명</th>
				<th>가격</th>
				<th>단종여부</th>
			</thead>
			<tbody>

				<?=$list;?>
			</tbody>
		</table>
		</td>
		<td width="40%" valign="top">
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

	location.href = "category_item_no.php?" + $("form[name=frm]").serialize();
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
include_once ($g4['admin_path']."/admin.tail.php");
?>
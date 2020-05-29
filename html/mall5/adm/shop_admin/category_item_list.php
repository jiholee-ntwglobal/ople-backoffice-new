<?
$sub_menu = "400210";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");


if($_POST['mode'] == 'child_cate_open'){
	$sql = sql_query($a="
		select 
			a.ca_id,a.ca_name,
			(
				select 
					count(*) 
				from 
					".$g4['yc4_item_table']." b
				where 
					b.ca_id like concat(a.ca_id,'%')
					or
					b.ca_id2 like concat(a.ca_id,'%')
					or
					b.ca_id3 like concat(a.ca_id,'%')
					or
					b.ca_id4 like concat(a.ca_id,'%')
					or
					b.ca_id5 like concat(a.ca_id,'%')
			) as cnt /* 해당 카테고리 상품 갯수 */,
			round(length(a.ca_id)/2) as depth /* 해당 카테고리 depth */,
			(
				select
					count(*)
				from
					yc4_category c
				where
					c.ca_id like concat(a.ca_id,'%')
					and
					c.ca_id != a.ca_id
			) as child_cate_cnt /* 자식 카테고리 갯수 */
		from 
			yc4_category a
		where
			length(a.ca_id) = ".(int)(strlen($_POST['ca_id'])+2)."
			and
			a.ca_id like '".$_POST['ca_id']."%'
		order by
			a.ca_id asc
	");

	while($row = sql_fetch_array($sql)){
		echo "
		<tr class='ht sub_cate' ca_id='".$_POST['ca_id']."'>
			<td class='ca_id' align='center'>".$row['ca_id']."</td>
			<td align='center'>".(($row['child_cate_cnt']>0) ? "<input type='button' value=' + ' onclick=\"cate_open(this);\">":"")."</td>
			<td class='ca_name'>".$row['ca_name']."</td>
			<td align='center'>".$row['depth']."</td>
			<td align='right'>".$row['cnt']."</td>
			<td align='right'>".$row['child_cate_cnt']."</td>
			<td align='center'><input type='button' value='복사' onclick='new_cate_open(this);'/></td>
		</tr>
	";
	}
	exit;
}

if($_POST['mode'] == 'new_child_cate'){
	$sql = sql_query("
		select
			ca_id,ca_name
		from
			".$g4['yc4_category_table']."
		where
			ca_id like '".$_POST['ca_id']."%'
			and
			ca_id != '".$_POST['ca_id']."'
			and
			length(ca_id) = ".(int)(strlen($_POST['ca_id'])+2)." 
	");
	while($row = sql_fetch_array($sql)){
		$result .= "
			<option value='".$row['ca_id']."'>".$row['ca_name']."</option>
		";
	}

	if($result){
		echo "<select class='new_cate' depth='". (int)((strlen($_POST['ca_id'])/2)+1) ."' onchange=\"new_child_cate(this);\"><option value=''>선택</option>".$result."</select>";
	}
	
	exit;
}


if($_POST['mode'] == 'category_copy'){

	
	$sql = sql_query("
		select 
			it_id
		from
			".$g4['yc4_item_table']."
		where
			ca_id = '".$_POST['ca_id']."'
			or
			ca_id2 = '".$_POST['ca_id']."'
			or
			ca_id3 = '".$_POST['ca_id']."'
			or
			ca_id4 = '".$_POST['ca_id']."'
			or
			ca_id5 = '".$_POST['ca_id']."'
	");

	while($row = sql_fetch_array($sql)){
		$chk = sql_fetch("
			select count(*) as cnt from yc4_category_item where it_id = '".$row['it_id']."' and ca_id = '".$_POST['new_ca_id']."'
		");
		if($chk['cnt'] > 0 ){
			continue;
		}

		$insertQ = "
			insert into yc4_category_item (it_id,ca_id) values('".$row['it_id']."','".$_POST['new_ca_id']."')
		";
			
		if(!sql_query($insertQ)){
			alert('처리중 요류 발생!');
		}

		
	}
	alert('신규 카테고리로 복사가 완료되었습니다.',$_SERVER['PHP_SELF']);
	exit;
}

$g4['title'] = "상품분류 변환(구->신)";
include_once ($g4['admin_path']."/admin.head.php");

# 구 분류 로드 시작 #

$old_cate_cnt = sql_fetch("
	select 
		count(*) as cnt
	from 
		yc4_category a
	where
		length(ca_id) = 2

");

// 테이블의 전체 레코드수만 얻음
$old_cate_cnt = $old_cate_cnt['cnt'];


$rows = $config[cf_page_rows];
$total_page  = ceil($old_cate_cnt / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함



$old_cateQ = sql_query("
	select 
		a.ca_id,a.ca_name,
		(
			select 
				count(*) 
			from 
				".$g4['yc4_item_table']." b
			where 
				b.ca_id like concat(a.ca_id,'%')
				or
				b.ca_id2 like concat(a.ca_id,'%')
				or
				b.ca_id3 like concat(a.ca_id,'%')
				or
				b.ca_id4 like concat(a.ca_id,'%')
				or
				b.ca_id5 like concat(a.ca_id,'%')
		) as cnt /* 해당 카테고리 상품 갯수 */,
		round(length(a.ca_id)/2) as depth /* 해당 카테고리 depth */,
		(
			select
				count(*)
			from
				yc4_category c
			where
				c.ca_id like concat(a.ca_id,'%')
				and
				c.ca_id != a.ca_id
		) as child_cate_cnt /* 자식 카테고리 갯수 */
	from 
		yc4_category a
	where
		length(a.ca_id) = 2
	order by
		a.ca_id asc
	limit $from_record, $rows
");

# 구 분류 로드 끝 #

while($old_cate = sql_fetch_array($old_cateQ)){
	$list_tr .= "
		<tr class='ht'>
			<td class='ca_id' align='center'>".$old_cate['ca_id']."</td>
			<td align='center'>".(($old_cate['child_cate_cnt']>0) ? "<input type='button' value=' + ' onclick=\"cate_open(this);\">":"")."</td>
			<td class='ca_name'>".$old_cate['ca_name']."</td>
			<td align='center'>".$old_cate['depth']."</td>
			<td align='right'>".$old_cate['cnt']."</td>
			<td align='right'>".$old_cate['child_cate_cnt']."</td>
			<td align='center'><input type='button' value='복사' onclick='new_cate_open(this);'/></td>
		</tr>

	";
}


# 신 분류 로드 시작 #
$new_cateQ = sql_query("
	select
		ca_id,ca_name
	from
		".$g4['yc4_category_table']."
	where
		length(ca_id) = 2
	order by
		ca_id asc
");
while($new_cate = sql_fetch_array($new_cateQ)){

	$new_cate_option .= "
		<option value='".$new_cate['ca_id']."'>".$new_cate['ca_name']."</option>
	";
}
?>
<style type="text/css">
.ht.active{
	background-color:#00ccff;
	color:#ffffff;
	font-weight:bold;
}

.new_category_layer{
	position:fixed;
	top: 150px;
	right:0px;
	background-color:#ffffff;
	padding:15px;

	border:1px solid #dddddd;
	width:500px;
}
.new_cate_layer_title{
	background-color:#0000cc;
	color:#ffffff;

	font-weight:bold;
	margin-top:-15px;
	margin-left:-15px;
	margin-right:-15px;
	margin-bottom:15px;
	padding:10px;
}

.new_category_layer p{
	margin-top:15px;
}

.new_cate_list{
	margin-top:15px;
}
</style>

<table width='100%'>
	<tr class='ht' align='center'>
		<td>코드</td>
		<td></td>
		<td>카테고리명</td>
		<td>Depth</td>
		<td>상품갯수</td>
		<td>하위카테고리</td>
		<td>신규 카테고리</td>
	</tr>
	<?=$list_tr;?>
</table>

<div class='new_category_layer'>
	<form action="<?=$_SERVER['PHP_SELF']?>" method='post' onsubmit='return cate_copy_submit();'>
		<div class='new_cate_layer_title'>신 카테고리 리스트</div>
		<div>
			복사할 카테고리 : <span class='ca_name'></span>
			&nbsp;&nbsp;&nbsp;&nbsp;
			(<input type="text" name='ca_id' readonly size='10' style='text-align:center;'/>)
			<input type="hidden" name='mode' value='category_copy' />
			<input type="hidden" name='new_ca_id' />
		</div>
		<br />
		<hr />
		<div class='new_cate_list'>
			<select class='new_cate' depth='1' onchange="new_child_cate(this);">
				<option value="">선택</option>
				<?=$new_cate_option?>
			</select>
		</div>
		<p align='center'><input type="submit" value=' 저 장 ' /></p>
	</form>
</div>


<script type="text/javascript">
function cate_open(obj){
	var ca_id = $(obj).parent().parent().find('.ca_id').text();

	var mode = $(obj).val().replace(/[\s]/g,'');

	if(mode == '+'){
	
		$.ajax({
			url : '<?=$_SERVER['PHP_SELF']?>',
			type : 'post',
			data : {
				'mode' : 'child_cate_open',
				'ca_id' : ca_id
			},success : function ( result ) {
				if(result == ''){
					return false;
				}
				$(obj).parent().parent().after(result);
				$(obj).val(' - ');
				$(obj).parent().parent().addClass('active');
			}
		});
	}else{
		$(obj).val(' + ');
		$('.sub_cate[ca_id='+ca_id+']').remove();
		$(obj).parent().parent().removeClass('active');
	}
}

function new_child_cate(obj){
	var ca_id = $(obj).val();
	var depth = $(obj).attr('depth');
	

	for(var i=0; i<$('.new_cate').length; i++){
		if( $('.new_cate:eq('+i+')').attr('depth') > depth ){
			$('.new_cate:eq('+i+')').addClass('remove_cate');
		}
	}
	$('.remove_cate').remove();


	if(ca_id == ''){
		return false;
	}
	

	$.ajax({
		url : '<?=$_SERVER['PHP_SELF']?>',
		type : 'post',
		data : {
			'mode' : 'new_child_cate',
			'ca_id' : ca_id
		},success : function ( result ) {
			if(result == ''){
				return false;
			}
			$(obj).after(result);
		}
	});
}

function new_cate_open( obj ){
	var ca_id = $(obj).parent().parent().find('.ca_id').text();
	var ca_name = $(obj).parent().parent().find('.ca_name').text();

	$('.new_category_layer .ca_name').text(ca_name);
	$('.new_category_layer input[name=ca_id]').val(ca_id);
}

function cate_copy_submit(){
	var old_ca_id = $('.new_category_layer input[name=ca_id]').val();
	var new_ca_id = $('.new_category_layer .new_cate:last').val();

	if(old_ca_id == ''){
		return false;
	}

	if(new_ca_id == ''){
		return false;
	}

	$('.new_category_layer input[name=new_ca_id]').val(new_ca_id);

	return true;


}

</script>
<?
include_once ($g4['admin_path']."/admin.tail.php");
?>
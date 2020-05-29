<?
$sub_menu = "500100";
include_once("./_common.php");


if($_POST['mode'] == 'update'){
	if(!$_POST['ev_id']){
		alert('잘못된 경로로 접근하였습니다.');
		exit;
	}

	sql_query("delete from ".$g4['yc4_event_item_table']." where ev_id = '".$_POST['ev_id']."'");

	if(is_array($_POST['it_id'])){
		foreach($_POST['it_id'] as $key => $val){
			sql_query("insert into ".$g4['yc4_event_item_table']." (ev_id,it_id,sort) values('".$_POST['ev_id']."','".$val."','".$_POST['sort'][$key]."')");
		}
	}

	alert('이벤트 저장이 완료되었습니다.',$_SERVER['PHP_SELF'].'?ev_id='.$_POST['ev_id']);



	exit;
}

auth_check($auth[$sub_menu], "r");

if($_POST['mode'] == 'item_search'){

	if($_POST['it_id']){
		$search_qry .= (($search_qry) ? " and ":' where ')."it_id like '%".$_POST['it_id']."%'";
	}

	if($_POST['SKU']){
		$search_qry .= (($search_qry) ? " and ":' where ')."SKU like '%".$_POST['SKU']."%'";
	}

	if($_POST['it_name']){
		$search_qry .= (($search_qry) ? " and ":' where ')."it_name like '%".$_POST['it_name']."%'";
	}

	if($_POST['it_maker']){
		$search_qry .= (($search_qry) ? " and ":' where ')."it_maker like '%".$_POST['it_maker']."%'";
	}

	$sql = sql_query("select it_id,it_name,it_maker,SKU,it_amount from ".$g4['yc4_item_table']." ".$search_qry." order by it_time desc");


	while($row = sql_fetch_array($sql)){
		echo "
			<li class='ui-state-default'>
				<input type='hidden' name='it_id[".$i."]' value='".$row['it_id']."'/>
				<input type='hidden' name='sort[".$i."]' value='".$i."'>
				<p>".$row['it_maker']."</p>
				상품코드 : ".$row['it_id']." | SKU : ".$row['SKU']." | 가격 : ".number_format($row['it_amount'])."원<br/>
				
				".$row['it_name']."
			</li>
		";
	}
	exit;
}


if($_GET['ev_id']){
	# 이벤트 상품 로드 #
	$ev_item_sql = sql_query("
		select 
			a.*,
			b.it_name,b.it_amount,b.it_maker
		from 
			".$g4['yc4_event_item_table']." a
			left join
			".$g4['yc4_item_table']." b on a.it_id = b.it_id
		where 
			ev_id = '".$_GET['ev_id']."'
		order by sort asc
	");
	$ev_item_cnt = mysql_num_rows($ev_item_sql);
	$i = 0;
	while($ev_row= sql_fetch_array($ev_item_sql)){
		$ev_item .= "
			<li class='ui-state-highlight'>
				<input type='hidden' name='it_id[".$i."]' value='".$ev_row['it_id']."'/>
				<input type='hidden' name='sort[".$i."]' value='".$i."'>
				<p>".$ev_row['it_maker']."</p>
				".$ev_row['it_name']."
			</li>
		";
		$i++;
	}


	# 오플 상품 로드 #
	$it_sql = sql_query("select it_id,it_name,it_maker from ".$g4['yc4_item_table']." order by it_time desc limit 10");
	while($it = sql_fetch_array($it_sql)){
		$it_item .= "
			<li class='ui-state-default'>
				<input type='hidden' name='it_id[".$i."]' value='".$it['it_id']."'/>
				<input type='hidden' name='sort[".$i."]' value='".$i."'>
				<p>".$it['it_maker']."</p>
				".$it['it_name']."
			</li>
		";
	}

}else{

	# 이벤트 리스트 로드 #
	$ev_sql = sql_query("
		select 
			ev_id,ev_subject,ev_use , (select count(*) from ".$g4['yc4_event_item_table']." where ev_id = ".$g4['yc4_event_table'].".ev_id) as cnt
		from 
			".$g4['yc4_event_table']."
	");

	while($ev_row = sql_fetch_array($ev_sql)){
		$contents .= "
			<tr>
				<td>".$ev_row['ev_id']."</td>
				<td>".$ev_row['ev_subject']."</td>
				<td align='center'>".$ev_row['cnt']."</td>
				<td align='center'>".icon('수정',$_SERVER['PHP_SELF'].'?ev_id='.$ev_row['ev_id'])."</td>
			</tr>
		";
	}

	if(!$contents){
		$contents = "
			<tr>
				<td>데이터가 존재하지 않습니다.</td>
			</tr>
		";
	}


	$contents = "
		<table>
			<tr>
				<td>이벤트코드</td>
				<td>이벤트명</td>
				<td>상품갯수</td>
			</tr>
			".$contents."
		</table>
	";
	}
$g4[title] = "이벤트상품등록(신)";
include_once ("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
<script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>

<script>
  
  </script>

<style type="text/css">
.droptrue {

}
.dropfalse{

}
.inline-form1{
	float:left;
	width:45%;
}
.inline-form2{
	float:right;
	width:45%;
}
#sortable1, #sortable2{
	min-height: 400px;
}
#sortable2{
	background-color:#ccffff;
}
</style>

<?if($_GET['ev_id']){?>
<div class='inline-form1'>
	<input type="text" name='it_id' placeholder='상품코드' value='<?=$_GET['it_id']?>'/>
	<input type="text" name='SKU' placeholder='SKU' value='<?=$_GET['SKU']?>'/>
	<input type="text" name='it_name' placeholder='상품명' value='<?=$_GET['it_name']?>'/>
	<input type="text" name='it_maker' placeholder='브랜드명' value='<?=$_GET['it_maker']?>'/>
	<input type="button" value='검색' onclick='item_search();' />
	<p>오플상품 <span class='item_cnt'></span></p>
	<ul id="sortable1" class="droptrue">
		<?=$it_item;?>
	</ul>
</div>
<form action="<?=$_SERVER['PHP_SELF']?>" method='post' class='inline-form2'>
	<input type="hidden" name='mode' value='update'/>
	<input type="hidden" name='ev_id' value='<?=$_GET['ev_id']?>' />
	<p>이벤트상품 <span class='event_item_cnt'><?=number_format($ev_item_cnt);?>개</span></p>
	<input type="submit" value='저장'/>
	<ul id="sortable2" class="dropfalse">
		<?=$ev_item;?>
	</ul>
</form>
<ul id="sortable3" class="droptrue">
</ul>
 
<br style="clear:both">
<?}else{?>
<?=$contents;?>
<?}?>


<script type="text/javascript">
$(function() {
	$( "ul.droptrue" ).sortable({
		connectWith: "ul"
	});

	$( "ul.dropfalse" ).sortable({
		connectWith: "ul",
		dropOnEmpty: false,
		update : function (event,ui) {
			var tmp_arr = Array();
			var it_id = $(this).find('input[name^=it_id]').val();
			for(var i=0; i<$('.dropfalse li').length; i++){
				var it_id = $('.dropfalse li:eq('+i+')').find('input[name^=it_id]').val();

				tmp_arr[it_id] = true;
				$('.dropfalse li:eq('+i+')').find('input[name^=it_id]').attr('name','it_id\['+i+'\]');
				$('.dropfalse li:eq('+i+')').find('input[name^=sort]').attr('name','sort\['+i+'\]');
				$('.dropfalse li:eq('+i+')').find('input[name^=sort]').val(i);
			}
		}
	});

	$( "#sortable1, #sortable2, #sortable3" ).disableSelection();
});
function item_search(){
	$.ajax({
		url : '<?=$_SERVER['PHP_SELF']?>',
		type : 'post',
		data : {
			'mode' : 'item_search',
			'it_id' : $('input[name=it_id]').val(),
			'SKU' : $('input[name=SKU]').val(),
			'it_name' : $('input[name=it_name]').val(),
			'it_maker' : $('input[name=it_maker]').val()
		},success : function ( result ) {
			$('#sortable1').html(result);
			var item_cnt = $('#sortable1 li').length;
			item_cnt = number_format(String(item_cnt));
			$('.item_cnt').text(item_cnt+'개');

		}
		
	});
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

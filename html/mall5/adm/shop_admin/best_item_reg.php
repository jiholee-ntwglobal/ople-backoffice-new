<?
$sub_menu = "300840";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");


if($_POST['it_id']){
	$tmp = explode("\n",$_POST['it_id']);

	if(count($tmp) > 0){
		sql_query("delete from yc4_best_item_manual");
		$num=1;
        $tmp = array_unique($tmp);
		foreach($tmp as $it_id){
			$it_id = preg_replace("/[^0-9]*/s", "", $it_id);
			if($it_id!="") {
                sql_query($a = "insert into yc4_best_item_manual (it_id, create_dt, sort) values ('$it_id', NOW(), '$num')");
                $num++;
            }
		}

        //메인 데이터 캐싱 파일 재생성
        file_get_contents("http://www.ople.com/mall5/cron/main_data_cache.php");

        alert('베스트 상품 등록이 완료되었습니다.',$_SERVER['PHP_SELF']);
		exit;
	}


}


$g4['title'] = "베스트 상품 수동 등록";
include_once ($g4['admin_path']."/admin.head.php");

# 구 분류 로드 시작 #


$best_item_rs = sql_query("
	select b.it_id,it_name from yc4_best_item_manual b, yc4_item i where i.it_id=b.it_id order by b.sort asc
");

# 구 분류 로드 끝 #

$no = 1;
while($item_data = sql_fetch_array($best_item_rs)){
	$list_tr .= "
		<tr class='ht'>
			<td class='ca_id' align='center'>".$no."</td>
			<td align='left' style='padding-left:10px;'>".$item_data['it_name']."</td>
		</tr>

	";
	$no++;
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
		<td>순번</td>
		<td>아이템명</td>
	</tr>
	<?=$list_tr;?>
</table>

<div class='new_category_layer'>
	<form method='post' onsubmit='return cate_copy_submit();'>
		<div class='new_cate_layer_title'>베스트 상품 등록</div>
		<br />
		<hr />
		<textarea name="it_id"  rows="20" cols="50"></textarea>
		<p align='center'><input type="submit" value=' 저 장 ' /></p>
	</form>
</div>



<?
include_once ($g4['admin_path']."/admin.tail.php");
?>
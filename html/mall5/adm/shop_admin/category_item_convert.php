<?
$sub_menu = "400210";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4['title'] = "상품분류 변환2(구->신)";
include_once ($g4['admin_path']."/admin.head.php");

$it_qry = sql_query("
	SELECT c.ca_id, c.ca_name,sum(if(isnull(i.ca_id),0,1)) as cnt
	FROM yc4_category_new c
       LEFT OUTER JOIN yc4_category_item i on i.ca_id = c.ca_id
       LEFT OUTER JOIN yc4_item o ON o.it_id = i.it_id
	group by c.ca_id
");

while($data = sql_fetch_array($it_qry)){

	unset($obj);

	switch(strlen($data['ca_id'])){
		case 1:
			$cate_cnt[$data['ca_id']] += $data['cnt'];
			$cate[$data['ca_id']]['depth'] = 1;
			
			break;
		case 2:
			$cate_cnt[substr($data['ca_id'],0,2)] += $data['cnt'];
			$cate_cnt[substr($data['ca_id'],0,2)][substr($data['ca_id'],2,2)] += $data['cnt'];
			$cate[$data['ca_id']]['depth'] = 2;			
			break;
		case 3:
			$cate_cnt[substr($data['ca_id'],0,2)] += $data['cnt'];
			$cate_cnt[substr($data['ca_id'],0,2)][substr($data['ca_id'],2,2)] += $data['cnt'];
			$cate[$data['ca_id']]['depth'] = 3;
			break;
	}

	$obj = &$cate[$data['ca_id']]
	$obj['name'] = $data['ca_name'];
	$obj['cnt'] = $data['cnt'];
}

print_r($cate);

exit;
?>

<table width='100%'>
	<thead>
		<th>
			<input type="checkbox" class='chk_all'/>
		</th>
		<th>제조사</th>
		<th>상품명</th>
		<th>가격</th>
	</thead>
	<tbody>
		<?=$list_tr;?>
	</tbody>
</table>





<?
include_once ($g4['admin_path']."/admin.tail.php");
?>
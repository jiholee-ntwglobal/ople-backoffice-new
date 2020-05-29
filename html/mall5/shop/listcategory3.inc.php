<?
$str = "";
$exists = false;

//$depth2_ca_id = substr($ca_id, 0, 2);
$depth2_ca_id = $ca_id;
/*
$sql = " select ca_id, ca_name from $g4[yc4_category_table]
          where ca_id like '${depth2_ca_id}%'
            and length(ca_id) = 4
            and ca_use = '1'
          order by ca_id ";
*/
# co.kr로 접속시 지정된 카테고리는 노출하지 않는다 2014-04-17 홍민기 #
/*
$sql = " select ca_id, ca_name from $g4[yc4_category_table]
          where ca_id like '${depth2_ca_id}%'
            and length(ca_id) = 4
            and ca_use = '1'
			".$hide_caQ."
          order by ca_id ";
*/
# 선택한 카테고리의 하위 가테고리만 출력
/*
$sql = "
	select
		a.ca_id, a.ca_name ,length(a.ca_id),a.ca_view,
		(select count(*) from yc4_category_item where ca_id like concat(a.ca_id,'%') ) as item_count
	from $g4[yc4_category_table] a
	where
		a.ca_id like '${depth2_ca_id}%'
		and length(a.ca_id) = ". (int)(strlen($ca_id)+2) ."
		and a.ca_use = '1'
	order by a.ca_id
";
*/
$name_sort_arr = array(
	'10','11','13'
);
if(in_array($ca_id,$name_sort_arr)){
	$listcate_orderby = "a.ca_view desc,a.ca_name";
}else{
	$listcate_orderby = "a.ca_view desc,a.ca_id";
}
$sql = "
	select
		a.ca_id, a.ca_name ,length(a.ca_id),a.ca_view,
		count(distinct b.it_id) as item_count
	from
		$g4[yc4_category_table] a
		left join
		yc4_category_item b on b.ca_id like concat(a.ca_id,'%')
		left join
		".$g4['yc4_item_table']." c on c.it_id = b.it_id
	where
		a.ca_id like '${depth2_ca_id}%'
		and a.ca_use = '1'
		and length(a.ca_id) = ". (int)(strlen($ca_id)+2) ."
		and c.it_id is not null
		and c.it_discontinued = 0
		and c.it_use = 1
	group by a.ca_id
	order by ".$listcate_orderby."
";


$result = sql_query($sql);

# 하위 분류가 존재하지 않는다면 같은 depth의 분류를 보여준다
if(mysql_num_rows($result) < 1){
	/*
	$sql = "
		select
			a.ca_id, a.ca_name ,length(a.ca_id),a.ca_view,
			(
				select
					count(*)
				from
					yc4_category_item c
					left join
					".$g4['yc4_item_table']." d on c.it_id=d.it_id
				where
					d.it_id is not null
					and d.it_discontinued = 0
					and d.it_use = 1
					and	c.ca_id like concat(a.ca_id,'%')

			) as item_count
		from
			$g4[yc4_category_table] a
			left join
			shop_category b on left(a.ca_id,2) = b.ca_id
		where
			a.ca_id like '".substr($depth2_ca_id,0,(strlen($ca_id))-2)."%'
			and length(a.ca_id) = ". (int)(strlen($ca_id)) ."
			and a.ca_use = '1'
			and b.s_id = '".$_SESSION['s_id']."'
		order by a.ca_id
	";
	*/
	if(in_array($depth2_ca_id,$name_sort_arr)){
		$listcate_orderby = "a.ca_view desc,a.ca_name";
	}else{
		$listcate_orderby = "a.ca_view desc,a.ca_id";
	}
	$sql = "
		select
			a.ca_id, a.ca_name ,
			length(a.ca_id),a.ca_view,
			count(distinct c.it_id) as item_count
		from
			yc4_category_new a
			left join
			shop_category b on left(a.ca_id,2) = b.ca_id
			left join
			yc4_category_item c on c.ca_id like concat(a.ca_id,'%')
			left join
			yc4_item d on d.it_id = c.it_id
		where
			a.ca_id like '".substr($depth2_ca_id,0,(strlen($ca_id))-2)."%'
			and length(a.ca_id) = ". (int)(strlen($ca_id)) ."
			and a.ca_use = '1'
			and b.s_id = '".$_SESSION['s_id']."'
			and d.it_id is not null
			and d.it_discontinued = 0
			and d.it_use = 1
		group by a.ca_id
		order by ".$listcate_orderby."
	";


	$result = sql_query($sql);
}


/*
// 김선용 200804 : 대분류 정보
$mca = sql_fetch("select ca_name from {$g4['yc4_category_table']} where ca_id='{$depth2_ca_id}' and ca_use=1");
*/

# co.kr로 접속시 지정된 카테고리는 노출하지 않는다 2014-04-17 홍민기 #
$mca = sql_fetch("select ca_name from {$g4['yc4_category_table']} where ca_id='{$depth2_ca_id}' and ca_use=1".$hide_caQ);

$str .= "<div class='listcategory'><div class='listcateogry-title'><a href='./list.php?ca_id={$depth2_ca_id}'>{$mca['ca_name']}</a></div>";
while ($row=sql_fetch_array($result)) {



    if (preg_match("/^$row[ca_id]/", $ca_id))
        $span = "<span class='listcategory1'>";
    else
        $span = "<span class='listcategory2'>";
	if($row['ca_view'] == 1){
		$class = '';
	}else{
		$class ='hide_cate';
		$hide_cate = true;
	}
    $str .= "<a href='./list.php?ca_id=$row[ca_id]' class='".($row['ca_view'] != '1' ? "hide_cate":"")."'>{$span}$row[ca_name] <strong>".number_format($row['item_count'])."</strong></span></a> ";
    $exists = true;

}
$str .= "";
if($hide_cate){
	$str .= "<a href='#' class='cate_view_vtn' onclick='cate_view_toggle();' style='font-weight: bold;'><span class='listcategory2'>자세히 +</span></a>";
}

if ($exists) {
    echo "
    <div id='listcategory-wrap'>
    $str
    </div>
    </div>
    <br>";
}
?>
<script type="text/javascript">
function cate_view_toggle(){
	if($('.cate_view_vtn span').text() == '자세히 +'){
		$('.cate_view_vtn span').text('간단히 -');
		$('.hide_cate').show();
	}else{
		$('.cate_view_vtn span').text('자세히 +');
		$('.hide_cate').hide();
	}
}
</script>
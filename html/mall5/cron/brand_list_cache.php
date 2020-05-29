<?php
/*
----------------------------------------------------------------------
file name	 : brand_list_cache.php
comment		 : 브랜드 리스트 캐시 생성
date		 : 2015-01-28
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/
include "db.config.php";
$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);

$db_selected1 = mysql_select_db('okflex5');


$sql = mysql_query("
	select
		trim(a.it_maker) as it_maker,
		a.it_maker_kor,
		upper(left(trim(a.it_maker),1)) as it_maker_sort,
		b.logo_img,
		count(*) as cnt
	from
		yc4_item a
		left join
		yc4_it_maker b on trim(a.it_maker) = b.it_maker
	where
		a.it_use = 1
		and
		a.it_maker not in ( '078347300756' )
		and
		trim(a.it_maker) != ''
	group by it_maker
	order by it_maker_sort asc, cnt desc
");


$result = '';
$bf_it_maker_sort = '';
while($row = mysql_fetch_assoc($sql)){
	if($row['it_maker_sort'] != $bf_it_maker_sort){

		$bf_it_maker_sort = $row['it_maker_sort'];
	}
	$it_maker = addslashes($row['it_maker']);

	$result .= <<<EOL
\$cahce_brand['{$bf_it_maker_sort}']['{$it_maker}'] = array('it_maker_kor'=>'{$row['it_maker_kor']}','item_cnt'=>'{$row['cnt']}','logo_img'=>'{$row['logo_img']}');

EOL;
}

if($result){
	$date = date('Y-m-d H:i:s');
	//$result = '<?php '.PHP_EOL.$result;
	$result = <<<EOL
<?php
# {$date} 생성 #

{$result}
EOL;
	$file = fopen('/ssd/html/mall5/cache/brand_cache.php','w');
	fwrite($file,$result);
	fclose($file);
}
echo $result;

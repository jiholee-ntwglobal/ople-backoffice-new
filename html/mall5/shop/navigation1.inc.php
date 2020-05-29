<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$ca_sql = sql_query("
	select
		a.ca_id,a.it_id,
		b.ca_name,d.name,d.s_id,
		a.ca_id like '".$_GET['ca_id']."%' as sort
	from
		yc4_category_item a
		left join
		".$g4['yc4_category_table']." b on a.ca_id = b.ca_id
		left join
		shop_category c on c.ca_id = substr(b.ca_id,1,2)
		left join
		yc4_station d on c.s_id = d.s_id
	where
		a.it_id = '".$it['it_id']."'
	order by sort desc
");

$ca_cnt = mysql_num_rows($ca_sql);

$i = 0;
while($ca_row = sql_fetch_array($ca_sql)){
	$bar = "";
	$len = strlen($ca_row['ca_id']) / 2;
	$str .= "<div class='list_navigation".($i>0 ? " list_navigation_hide":"")."'><a id='global-nav' href='".$g4['path']."?s_id=".$ca_row['s_id']."' style='".($i > 0 ? "background:none;":"")."'>".strtoupper($ca_row['name'])."</a>";
	for ($ii=1; $ii<=$len; $ii++){
		$code = substr($ca_row['ca_id'],0,$ii*2);

        $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$code' ";
        $row = sql_fetch($sql);

        $style = "";
        if ($ca_row['ca_id'] == $_GET['ca_id'] && $ii == $len){
            //$style = "style='font-weight:bold;'";
			$styles = "<span class=navi_end>";
			$stylee = "</span>";
		}else{
			$stylee = $styles = '';
		}
		
        $str .= $bar . "<a href='./list.php?ca_id=$code'>{$styles}$row[ca_name]{$stylee}</a>";
		if($i==0 && $ii == $len && $ca_cnt > 1){
			$str .= "<img src='http://115.68.20.84/mall6/ico_down_point.png' style='vertical-align: middle; cursor:pointer;' onclick=\"$('.list_navigation_hide').toggle();\"/>";
		}
        $bar = "  ";
	}
	$str .= "</div>";
	$i++;
}

/*
if ($ca_id)
{    
    $str = $bar = "";
    $len = strlen($ca_id) / 2;
    for ($i=1; $i<=$len; $i++) 
    {
        $code = substr($ca_id,0,$i*2);

        $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$code' ";
        $row = sql_fetch($sql);

        $style = "";
        if ($ca_id == $code){
            //$style = "style='font-weight:bold;'";
			$styles = "<span class=navi_end>";
			$stylee = "</span>";
		}

        $str .= $bar . "<a href='./list.php?ca_id=$code'>{$styles}$row[ca_name]{$stylee}</a>";
        $bar = "  ";
    }
}
else
    $str = $g4[title];
*/
//if ($it_id) $str .= " > $it[it_name]";

include("./navigation2.inc.php");
?>
<style type="text/css">
.list_navigation_hide{
	display:none;
}
</style>

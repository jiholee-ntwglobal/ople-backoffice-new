<?
include_once("./_common.php");

// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "sjsjin.shop_guard.php";
//guard_script1($search_str);

// 상품이미지 사이즈(폭, 높이)를 몇배 축소 할것인지를 설정
// 0 으로 설정하면 오류남 : 기본 2
$image_rate = 2;

$g4[title] = "상품 검색";
include_once("./_head.php");

if($_GET['search_str_all']){
	$_GET['search_str'] = $_GET['search_str_all'];
	$search_name = '전체검색';
}elseif($_GET['search_str']){
	$search_name = strtoupper($station['name']).'관 검색';
}

$_GET['search_str'] = $_GET['search_str'] ? stripslashes($_GET['search_str']) : null;
$_GET['it_maker'] = $_GET['it_maker'] ? stripslashes($_GET['it_maker']) : null;

// 김선용 201206 : 서제스트 쿼리에서 이스케이프 처리돼서 넘어옴
$search_str = (trim($_GET['search_str']) != '' ? mysql_real_escape_string($_GET['search_str']) : '');
$it_maker = (trim($_GET['it_maker']) != '' ? mysql_real_escape_string(stripslashes($_GET['it_maker'])) : '');

if($search_str){
	$keyword = $search_str;
}
elseif($it_maker){
	$keyword = $it_maker;
}else{
	alert('잘못된 접근입니다.'); exit;
}


if($search_str && strlen($search_str)<2){
	alert('검색어는 최소 2글자 이상 입력해 주세요');
	exit;
}

// 김선용 201207 : 검색전용 테이블. 모든 검색어 저장
// 검색어, 회원id, 일시, ip
sql_query("insert into yc4_search_key
	set sk_key = '{$keyword}',
		mb_id = '{$member['mb_id']}',
		sk_datetime = '{$g4['time_ymdhis']}',
		sk_ip = '".getenv('REMOTE_ADDR')."' ");

$sql_where .= ($sql_where ? " and ":"")."a.it_use = '1' \n";
$sql_where .= ($sql_where ? " and ":"")."a.it_discontinued = 0 \n";

# 카테고리 검색 처리 #
if(is_array($_GET['ca_id'])){
	foreach($_GET['ca_id'] as $val){
		$sql_ca_like .= ($sql_ca_like ? " or ":"")." b.ca_id like '".$val."%'";
	}
	if($sql_ca_like){
		$sql_ca_like = "(".$sql_ca_like.")";
		$sql_where_search .= ($sql_where ? " and ":"").$sql_ca_like."\n";
	}
}

# 제품관 검색 처리 #
/*
if(is_array($_GET['sh_s_id'])){
	foreach($_GET['sh_s_id'] as $val){
		$sql_s_like .= ($sql_s_like ? ", ":"")."'".$val."'";
	}
	if($sql_s_like){
		$sql_where_search .= ($sql_where ? "and ":"")."d.s_id in(".$sql_s_like.")";
	}
}
*/

if($it_maker){
	$sql_where2 = $sql_where;

	$sql_select = "
		a.it_id,a.it_name,a.it_amount,a.it_point,a.it_cust_amount,a.it_gallery,a.it_tel_inq,
		if(a.it_stock_qty>0,0,1) as sold_out,
		b.ca_id
	";
	$sql_from = "
		".$g4['yc4_item_table']." a
		left join
		yc4_category_item b on a.it_id = b.it_id


	";

	$sql_where .= ($sql_where ? " and ":"")."a.it_maker = '".$it_maker."' \n";
	//$sql_where .= ($sql_where ? " and ":"")."d.ca_id is not null \n";
	$sql_order_by .= ($sql_order_by ? ", ":""). "a.it_discontinued asc,	sold_out asc \n";
	# 카테고리 리스트 로드
	$sql = "
		select
			".$sql_select."
		from
			".$sql_from."
		where
			".$sql_where.$sql_where_search."
		group by a.it_id
		order by a.it_id desc

	";

	$sql_select2 = "
		a.it_id,a.it_name,a.it_amount,a.it_point,a.it_cust_amount,a.it_gallery,a.it_tel_inq,
		if(a.it_stock_qty>0,0,1) as sold_out,
		c.ca_id,c.ca_name,e.s_id,e.name
	";
	$sql_from2 = "
		".$g4['yc4_item_table']." a
		left join
		yc4_category_item b on a.it_id = b.it_id
		left join
		".$g4['yc4_category_table']." c on c.ca_id = b.ca_id
		left join
		shop_category d on d.ca_id = left(c.ca_id,2)
		left join
		yc4_station e on e.s_id = d.s_id

	";



	$sql_where2 .= ($sql_where2 ? " and ":"")."a.it_maker = '".$it_maker."' \n";
	$sql_where2 .= ($sql_where2 ? " and ":"")."d.ca_id is not null \n";
	$sql_order_by2 .= ($sql_order_by2 ? ", ":""). "a.it_discontinued asc,	sold_out asc \n";

	# 카테고리 리스트 로드
	$sql2 = "
		select
			".$sql_select."
		from
			".$sql_from."
		where
			".$sql_where.$sql_where_search."
		group by d.s_id,c.ca_id,a.it_id
		order by e.sort asc

	";

	$it_row = sql_fetch("select it_maker_description from ".$g4['yc4_item_table']." where it_maker='".$it_maker."' ".$hide_caQ.$hide_makerQ." and it_maker_description != '' limit 1");
	if($it_row['it_maker_description'] != ''){

		if( trim(strtolower($_GET['it_maker'])) == trim(strtolower("doctor's best")) ){
			echo $it_row['it_maker_description'];
		}else{
			echo "<div style='text-align:center;'>".conv_content($it_row['it_maker_description'],1)."</div>";
		}
	}else{
		echo "
			<div class='brandtitle'><b>".stripslashes($it_maker)."</b> products</div>
		";
	}

	if($_MASTER_CARD_EVENT){
		$master_card_brand_chk = sql_fetch("select count(*) as cnt from master_card_no_item where it_maker = '".$it_maker."'");

		if($master_card_brand_chk['cnt'] > 0){
			echo "<div class='master_card_brand_comment'>* 해당 브랜드의 상품은 마스타 카드 프로모션 제외상품 입니다.</div>";
		}
	}


}else{
	if($_GET['station_search']){ // 관내 검색 처리
		$sql_where .= ($sql_where ? " and ":"") . "d.s_id = '".$_SESSION['s_id']."' \n";
	}

	if(is_numeric ($search_str) ){ // 검색어가 숫자만 있다면 UPC or it_id로 검색 2014-10-17 홍민기
		$sql_where .= ($sql_where ? " and ":"") . " (a.SKU = '".$search_str."' or a.it_id = '".$search_str."')";
		$upc_search = true;
	}else{

		// 검색어 공백구분으로 분리
		$search_arr = explode(" ", $search_str);
		if(count($search_arr) == 1){
			$search_str2 = "+{$search_str}*";
			$search_str3 = "+{$search_str}*";
		}else
		{
			$search_str2 = "*{$search_arr[0]}*";
			$search_str3 = "+{$search_arr[0]}";
			for($k=1; $k<count($search_arr); $k++){
				$search_str2 .= "*{$search_arr[$k]}*";
				$search_str3 .= "*{$search_arr[$k]}*";
			}
		}
//		$search_str2 = "+{$search_str}*";
		//$search_str2 .= "*";
		/*$sql_common .= " and ( a.it_id like '$search_str%' or
							   a.it_name like   '%$search_str%' or
							   a.it_basic like  '%$search_str%' or
							   a.it_explan like '%$search_str%' ) ";  , a.it_basic, a.it_explan*/
		$search_where = " match(a.it_name) against('{$search_str2}' in boolean mode)";
		$sql_where .= ($sql_where ? " and ":"")."(".$search_where." or match(a.it_opt4) against('{$search_str3}' in boolean mode)) \n";
	}

	$sql_from = "
		".$g4['yc4_item_table']." a
		left join
		yc4_category_item b on a.it_id = b.it_id
		left join
		shop_category c on c.ca_id = left(b.ca_id,2)
		left join
		yc4_station d on d.s_id = c.s_id
	";
	if($search_where){
		$score2 = true;
	}


	$sql = "
		select
			a.it_id,a.it_name,a.it_amount,a.it_point,a.it_cust_amount,a.it_gallery,a.it_tel_inq,
			if(a.it_stock_qty>0,0,1) as sold_out,
			d.s_id,d.name
			".($search_where ? ",a.it_name = '".$search_str."' as score2":"")."
			".($search_where ? ",".$search_where." + match(a.it_opt4) against('{$search_str3}' in boolean mode) as score":"")."
		from
			".$sql_from."
		where
			".$sql_where.$sql_where_search."
		group by a.it_id
		order by
			".($score2 ? "score2 desc,":"")."
			sold_out asc

			".($search_where ? ",score desc":"")."
			,it_id desc

	";


}


# 카테고리 영역 출력 #
if($it_maker){
	$ca_sql = sql_query("
		select
			d.s_id,left(c.ca_id,2) as ca_id,1 as cnt
		from
			".$sql_from2."
		where
			".$sql_where2."
		group by d.s_id,ca_id,a.it_id
		order by d.sort asc,c.ca_id asc
	");

} else {
	$ca_sql = sql_query("
		select
			d.s_id,left(c.ca_id,2) as ca_id,1 as cnt
		from
			".$sql_from."
		where
			".$sql_where."
		group by d.s_id,ca_id,a.it_id
		order by d.sort asc,c.ca_id asc
	");
}




$ca_arr = array();
while($row = sql_fetch_array($ca_sql)){
	if(!$ca_arr[$row['s_id']][$row['ca_id']]){
		$ca_arr[$row['s_id']][$row['ca_id']] = 0;
	}
	$ca_arr[$row['s_id']][$row['ca_id']] += $row['cnt'];
}


if(is_array($ca_arr)){
	foreach($ca_arr as $s_id => $val){
		# 제품관 카운트
		foreach($val as $row_ca_id => $val2){
			$depth = strlen($row_ca_id) / 2;
			if($depth){
				/*
				if($depth == 1){
					$ca_navi_data[$s_id][$row_ca_id] += $val2;
				}elseif($depth == 2){
					$ca_navi_data[$s_id][substr($row_ca_id,0,2)][$row_ca_id] += $val2;
				}elseif($depth == 3){
					$ca_navi_data[$s_id][substr($row_ca_id,0,2)][substr($row_ca_id,0,4)][$row_ca_id] += $val2;
				}elseif($depth == 4){
					$ca_navi_data[$s_id][substr($row_ca_id,0,2)][substr($row_ca_id,0,4)][substr($row_ca_id,0,6)][$row_ca_id] += $val2;
				}elseif($depth == 5){
					$ca_navi_data[$s_id][substr($row_ca_id,0,2)][substr($row_ca_id,0,4)][substr($row_ca_id,0,6)][substr($row_ca_id,0,8)][$row_ca_id] += $val2;
				}
				*/
				$ca_navi_data[$s_id][substr($row_ca_id,0,2)] += $val2;
			}
			$s_cnt[$s_id]+=$val2;
		}

		//$ca_navi_data[$s_id]

	}
}


$get_param_st_arr = $_GET;
unset($get_param_st_arr['sh_s_id'],$get_param_st_arr['ca_id']);
$get_param_st = http_build_query($get_param_st_arr);

if(is_array($ca_navi_data)){
	foreach($ca_navi_data as $s_id => $val){
		if(!$_GET['ca_id']){
			$s_id_empty = true;
		}
		if($s_id_empty){
			$_GET['sh_s_id'][] = $s_id;
		}

		# 제품관 이름 로드 #
		$s_name = sql_fetch("select name from yc4_station where s_id = '".$s_id."'");
		$s_name = $s_name['name'];

		$ca_navi .= "
			<tr>
		";
		$ca_navi .= "
				<th><input type='checkbox' name='sh_s_id[]' value='".$s_id."' ".(array_search($s_id,$_GET['sh_s_id']) !== false && $_GET['sh_s_id'] ? "checked":"")."/>".$s_name."</th>
		";
		$ca_navi .= "
				<td s_id='".$s_id."'>";

		if(is_array($val)){
			foreach($val as $ca_id1 => $val1){
				if(!$_GET['ca_id']){
					$ca_id_empty = true;
				}
				if($ca_id_empty){
					$_GET['ca_id'][] = $ca_id1;
				}
				$ca_name1 = sql_fetch("select ca_name from ".$g4['yc4_category_table']." where ca_id = '".$ca_id1."'");
				$ca_name1 = "<span class='search_cate search_cate_1depth' ca_id='".$ca_id1."'><input type='checkbox' name='ca_id[]' value='".$ca_id1."' s_id='".$s_id."' ".(array_search($ca_id1,$_GET['ca_id']) !== false && $_GET['ca_id'] ? "checked":"")."/>".$ca_name1['ca_name']."<em>###_ca_cnt=".$ca_id1."_###</em></span>";
				$ca_navi .= $ca_name1;
				if(is_array($val1)){
					foreach($val1 as $ca_id2 => $val2){
						$ca_name2 = sql_fetch("select ca_name from ".$g4['yc4_category_table']." where ca_id = '".$ca_id2."'");
						$ca_name2 = "<span class='search_cate search_cate_2depth' ca_id='".$ca_id2."'><input type='checkbox' name='ca_id[]' s_id='".$s_id."'  ".(array_search($ca_id2,$_GET['ca_id']) !== false && $_GET['ca_id'] ? "checked":"")."value='".$ca_id2."'/>".$ca_name2['ca_name']."<em>###_ca_cnt=".$ca_id2."_###</em></span>";
						$ca_navi .= $ca_name2;
						if(is_array($val2)){
							foreach($val2 as $ca_id3 => $val3){
								$ca_name3 = sql_fetch("select ca_name from ".$g4['yc4_category_table']." where ca_id = '".$ca_id3."'");
								$ca_name3 = "<span class='search_cate search_cate_3depth' ca_id='".$ca_id3."'><input type='checkbox' name='ca_id[]' s_id='".$s_id."' ".(array_search($ca_id3,$_GET['ca_id']) !== false && $_GET['ca_id'] ? "checked":"")." value='".$ca_id3."'/>".$ca_name3['ca_name']."<em>###_ca_cnt=".$ca_id3."_###</em></span>";
								$ca_navi .= $ca_name3;
								if(is_array($val3)){
									foreach($val3 as $ca_id4 => $val4){
										$ca_name4 = sql_fetch("select ca_name from ".$g4['yc4_category_table']." where ca_id = '".$ca_id4."'");
										$ca_name4 = "<span class='search_cate search_cate_4depth' ca_id='".$ca_id4."'><input type='checkbox' name='ca_id[]' s_id='".$s_id."' ".(array_search($ca_id4,$_GET['ca_id']) !== false && $_GET['ca_id'] ? "checked":"")." value='".$ca_id4."'/>".$ca_name4['ca_name']."<em>###_ca_cnt=".$ca_id4."_###</em></span>";
										$ca_navi .= $ca_name4;
										if(is_array($val4)){
											foreach($val4 as $ca_id5 => $val5){
												$ca_name5 = sql_fetch("select ca_name from ".$g4['yc4_category_table']." where ca_id = '".$ca_id5."'");
												$ca_name5 = "<span class='search_cate search_cate_5depth' ca_id='".$ca_id5."'><input type='checkbox' name='ca_id[]' s_id='".$s_id."' ".(array_search($ca_id5,$_GET['ca_id']) !== false && $_GET['ca_id'] ? "checked":"")." value='".$ca_id5."'/>".$ca_name5['ca_name']."<em>###_ca_cnt=".$ca_id5."_###</em></span>";
												$ca_navi .= $ca_name5;
												if(is_array($val5)){
												# 현재 4depth 까지 있음
												}else{
													$ca_navi = str_replace('###_ca_cnt='.$ca_id5.'_###',$val5,$ca_navi);
													$ca_sum[$ca_id1]++;
													$ca_sum[$ca_id2]++;
													$ca_sum[$ca_id3]++;
													$ca_sum[$ca_id4]++;
												}
											}
											if($ca_sum[$ca_id4]) $ca_navi = str_replace('###_ca_cnt='.$ca_id4.'_###',$ca_sum[$ca_id4],$ca_navi);
										}else{
											$ca_navi = str_replace('###_ca_cnt='.$ca_id4.'_###',$val4,$ca_navi);
											$ca_sum[$ca_id1]++;
											$ca_sum[$ca_id2]++;
											$ca_sum[$ca_id3]++;
										}
									}
									if($ca_sum[$ca_id3]) $ca_navi = str_replace('###_ca_cnt='.$ca_id3.'_###',$ca_sum[$ca_id3],$ca_navi);
								}else{
									$ca_navi = str_replace('###_ca_cnt='.$ca_id3.'_###',$val3,$ca_navi);
									$ca_sum[$ca_id1]++;
									$ca_sum[$ca_id2]++;
								}
							}
							if($ca_sum[$ca_id2]) $ca_navi = str_replace('###_ca_cnt='.$ca_id2.'_###',$ca_sum[$ca_id2],$ca_navi);
						}else{
							$ca_navi = str_replace('###_ca_cnt='.$ca_id2.'_###',$val2,$ca_navi);
							$ca_sum[$ca_id1]++;
						}
					}
					if($ca_sum[$ca_id1]) $ca_navi = str_replace('###_ca_cnt='.$ca_id1.'_###',$ca_sum[$ca_id1],$ca_navi);
				}else{
					$ca_navi = str_replace('###_ca_cnt='.$ca_id1.'_###',$val1,$ca_navi);
				}
			}
		}

		$ca_navi .= "
				</td>
		";
		$ca_navi .= "
			</tr>
		";
	}
}
?>
<style type="text/css">
.search_cate{
	display:none;
	margin : 5px;
}
.search_cate_1depth{
	display:inline-block;
}
</style>
<?php
if($ca_navi){
?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method='get' name='item_search_frm' onsubmit="return search_before(this)">
	<?if(is_array($get_param_st_arr)){
		foreach($get_param_st_arr as $key => $val){
			if(!$val){
				continue;
			}
			$val = stripslashes($val);
			echo "<input type='hidden' name='".$key."' value=\"".$val."\"/>";
		}
	}?>
	<div class='Search_category_wrap'>
	<table cellpadding=0 cellspacing=0>
		<col width='100'/>
		<col />
		<?php echo $ca_navi ;?>
	</table>
	<p class='detailSearch_button'><input type="image" src='<?=$g4['path'];?>/images/common/common_btn_detailSearch.gif' value='상세검색' /></p>
	</div>
</form>

<script type="text/javascript">
function search_before(frm){
	frm.page.value='';
	return true;
}
$('input[name=sh_s_id\\[\\]]').click(function(){
	var s_id = $(this).val();
	if($(this).is(':checked') == true){
		$('.Search_category_wrap td[s_id='+s_id+'] input:checkbox').prop('checked',true);
	}else{
		$('.Search_category_wrap td[s_id='+s_id+'] input:checkbox').prop('checked',false);
	}
});
$('input[name=ca_id\\[\\]]').click(function(){
	var s_id = $(this).attr('s_id');
	var ca_id = $(this).val();
	if($(this).is(':checked') == true){
		$('input[name=ca_id\\[\\]][value^='+ca_id+']').prop('checked',true);
	}else{
		$('input[name=ca_id\\[\\]][value^='+ca_id+']').prop('checked',false);
	}


	if($(this).is(':checked') == true && $('input[name=ca_id\\[\\]][s_id='+s_id+']').length == $('input[name=ca_id\\[\\]][s_id='+s_id+']:checked').length){

		$('input[name=sh_s_id\\[\\]][value='+s_id+']').prop('checked',true);
	}else{
		$('input[name=sh_s_id\\[\\]][value='+s_id+']').prop('checked',false);
	}
});
</script>
<?php
}

if($s_id_empty || $ca_id_empty){

	echo "
		<script>
			document.item_search_frm.submit();
		</script>
	";
	exit;
}

$list_mod = 4;
$td_width = (int)(100 / $list_mod);
$list_row = 5;
if(!$items){
	$items = $list_mod * $list_row;
}

$cnt_sql = "
	select count(*) as cnt from (
	select 1 from $sql_from where $sql_where $sql_where_search group by a.it_id
	) t
";

$row = sql_fetch($cnt_sql);
$total_count = $row[cnt];

if(count($ca_arr) < 1){ // 검색결과가 존재하지 않는다면
	if($it_maker){}else{
		echo "검색 결과가 존재하지 않습니다.";
	}
}


// 전체 페이지 계산
$total_page  = ceil($total_count / $items);
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page == "") $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $items;

$result = sql_query($sql. " limit $from_record, $items ");

if($search_str){
	echo "검색어 <strong>\"".stripslashes($search_str)."\"</strong>에 대한 검색 결과 ";
}
echo "총 <span class='point'><strong>".number_format($total_count)."</strong></span>개의 상품이 검색되었습니다.";

$qstr1 = $_GET;
unset($qstr1['page']);
$qstr1 = http_build_query($qstr1);


include $g4['full_shop_path'].'/list.skin.search.php';




//$qstr1 .= "items=$items&ca_id=$ca_id&ev_id=$ev_id&sort=$sort";
echo "<div class='paging'>";
echo get_paging($config['cf_write_pages'], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr1&page=");
echo "</div>";

include $g4['full_path'].'/_tail.php';
exit;
/*
while($row = sql_fetch_array($sql)){
	$ca_depth = strlen($row['ca_id']) / 2;

	for($i=1; $i<=$ca_depth; $i++){
		$dp = $i*2;
		$crop_ca_id = substr($row['ca_id'],0,$dp);
		$row['ca_id'] = $crop_ca_id;
		$cnt[$crop_ca_id] = $cnt[$crop_ca_id] ? $cnt[$crop_ca_id] : 0;
		$ca_navi[$row['s_id']][$crop_ca_id][$cnt[$crop_ca_id]] = $row;

		$ca_nm_qry = sql_fetch("select ca_name from ".$g4['yc4_category_table']." where ca_id = '".$crop_ca_id."'");
		$ca_navi[$row['s_id']][$crop_ca_id][$cnt[$crop_ca_id]]['ca_name'] = $ca_nm_qry['ca_name'];

		$cnt[$crop_ca_id]++;
	}
	$ca_navi_cnt[$row['s_id']] += (int)$row['cnt'];
	$ca_navi_st_name[$row['s_id']] = $row['name'];
}
*/
/*
	$ca_navi[제품관코드][카테고리코드][순서][필드명];
*/
//print_r2($ca_navi);
/*
if(is_array($ca_navi)){

	foreach($ca_navi as $s_id => $ca_info){
		$ca_navi_view .= "
			<tr>
				<td><input type='checkbox' name='s_id[]' value='".$s_id."'/>".$ca_navi_st_name[$s_id]."(".number_format($ca_navi_cnt[$s_id]).")</td>
				<td>
		";
		if(is_array($ca_info)){

			foreach($ca_info as $ca => $val){
				if(is_array($val)){
					foreach($val as $val2){
						if($tmp_arr[$val2['ca_id']]){
							continue;
						}
						$tmp_arr[$val2['ca_id']] = true;
						$ca_depth = strlen($val2['ca_id']) / 2;
						$ca_navi_view .=  "
					<input type='checkbox' name='ca_id[".$s_id."][". $ca_depth ."][]' value='".$val2['ca_id']."'/>".$val2['ca_name']."(".number_format($val2['cnt']).")
						";

					}
				}

			}
		}
		$ca_navi_view .=  "
				</td>
			</tr>
		";
	}
}
*/

if($_GET['station_search']){ # 관내 검색일 경우

	include $g4['full_shop_path']."/search_station.php";


	exit;
}
?>
<script type="text/javascript">
if(document.getElementById('search-input'))
	document.getElementById('search-input').value = "<?=stripslashes($_GET['search_str'])?>";
</script>
<!-- 검색결과에 대한 제품관 및 카테고리 상세검색 체크박스 -->
<table>
	<?php echo $ca_navi_view;?>
</table>
<table width=100% cellpadding=0 cellspacing=0 align=center border=0>

    <td valign=top>
    <?if($it_maker){?>

		<?}else if($search_str){?>
		<fieldset style="margin:0 5px;padding:10px;">
			<legend>검색어 입력&검색방법 안내</legend>
			※ 검색어는 최소 <u>2글자 이상</u> 입력하고, 여러문구를 입력시에는 띄워서 입력하십시오. 띄워서 입력하면 해당 문구들이 포함된 상품들이 검색됩니다.
		</fieldset>
		<br/>
<?}?>

		<?if($it_maker){
			$search_chk = true;
			?>

			<div class="brandtitle"><strong><?=stripslashes($_GET['it_maker'])?></strong></div>
			<?
			$it_row = sql_fetch("select it_maker_description from {$g4['yc4_item_table']} where it_maker='$it_maker' ".$hide_caQ.$hide_makerQ." limit 1");
			if($it_row['it_maker_description'] != '')
				echo "<div style='text-align:center;'>".conv_content($it_row['it_maker_description'],1)."</div>";
			?>
		<?}else if($search_str){
			$search_chk = true;
		?>
			&nbsp;&nbsp; <?=$search_name;?> <br />
	        &nbsp;&nbsp; 찾으시는 검색어는 &quot; <strong><?=stripslashes(get_text($_GET['search_str']))?></strong> &quot; 입니다.
		<?}else{
			$search_chk = false;
		?>
			&nbsp;&nbsp; 검색어가 없습니다. 검색어를 입력해 주십시오.
		<?}?>
        <br><br>

		<?

		if(!$search_chk){
			# 검색어가 없다면 검색을 하지 않는다 2014-09-10 홍민기 #
			goto no_str;

		}
		// 김선용 201211 : 단종상품 미출력
        // QUERY 문에 공통적으로 들어가는 내용
        // 상품명에 검색어가 포한된것과 상품판매가능인것만
        $sql_common = "
			from
				$g4[yc4_item_table] a
				left join
				yc4_category_item c on a.it_id = c.it_id
				left join
				$g4[yc4_category_table] b on c.ca_id=b.ca_id
			where
				a.it_use = 1
				and
				a.it_discontinued=0
				and
				b.ca_use = 1 ".$hide_caQ5.$hide_maker3.$hide_itemQ2;

		// 김선용 200804 : ev 로 시작하는 분류는 숨김
		if(!$is_admin)
			$sql_common .= " and b.ca_id not like ('ev%') ";

		// 김선용 201206 : 제조사, 풀텍스트
		if($it_maker)
			$sql_common .= " and it_maker='$it_maker' ";
		else if($search_str)
		{
			// 검색어 공백구분으로 분리
			$search_arr = explode(" ", $search_str);
			if(count($search_arr) == 1)
				$search_str2 = "+{$search_str}*";
			else
			{
				$search_str2 = "+{$search_arr[0]}*";
				for($k=1; $k<count($search_arr); $k++){
					$search_str2 .= " +{$search_arr[$k]}*";
				}
			}
			$search_str2 = "+{$search_str}*";
			//$search_str2 .= "*";
            /*$sql_common .= " and ( a.it_id like '$search_str%' or
                                   a.it_name like   '%$search_str%' or
                                   a.it_basic like  '%$search_str%' or
                                   a.it_explan like '%$search_str%' ) ";  , a.it_basic, a.it_explan*/
            $sql_common .= " and  match(a.it_name) against('{$search_str2}' in boolean mode) ";
        }

        // 분류선택이 있다면 특정 분류만
        if ($search_ca_id != "")
            $sql_common .= " and a.ca_id like '$search_ca_id%' ";

		//echo "select a.ca_id,    a.it_id    $sql_common  order by a.ca_id, a.it_id desc ";
        // 검색된 내용이 몇행인지를 얻는다
        $sql = " select COUNT(*) as cnt $sql_common ";

        $row = sql_fetch($sql);
        $total_count = $row[cnt];
        if($it_maker)
        echo "&nbsp;&nbsp; 총 상품수 <b>{$total_count}개</b><br>";
        else if($search_str)
        echo "&nbsp;&nbsp; 입력하신 검색어로 총 <b>{$total_count}건</b>의 상품이 검색 되었습니다.<br>";

        // 임시배열에 저장해 놓고 분류별로 출력한다.
        // write_serarch_save() 함수가 임시배열에 있는 내용을 출력함
        if ($total_count > 0) {

			// 김선용 200908 : 미사용
            // 인기검색어
            $sql = " insert into $g4[popular_table]
                        set pp_word = '$search_str',
                            pp_date = '$g4[time_ymd]',
                            pp_ip = '$_SERVER[REMOTE_ADDR]' ";
            sql_query($sql, FALSE);


            unset($save); // 임시 저장 배열
            $sql = " select c.ca_id,
                            a.it_id,if(it_stock_qty <=0,0,1) as cnt,
							match(a.it_name) against('{$search_str2}' in boolean mode) as score
                     $sql_common
                     order by cnt desc,score desc, a.ca_id, a.it_id desc ";

            $result = sql_query($sql);
            for ($i=0; $row=mysql_fetch_array($result); $i++) {
                if ($save[ca_id] != $row[ca_id]) {
                    if ($save[ca_id]) {
                        write_search_save($save);
                        unset($save);
                    }
                    $save[ca_id] = $row[ca_id];
                    $save[cnt] = 0;
                }
                $save[it_id][$save[cnt]] = $row[it_id];
                $save[cnt]++;
            }
            mysql_free_result($result);
            write_search_save($save);
        }
        ?>
    </td>
</tr>
</table>

<?
function write_search_save($save)
{
	global $g4, $search_str , $default , $image_rate , $cart_dir;

    $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$save[ca_id]' ";
    $row = sql_fetch($sql);

    /*
    echo "
    <table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
    <colgroup width=80>
    <colgroup width=>
    <colgroup width=150>
    <colgroup width=100>
    <tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
    <tr>
        <td colspan=2 height='28'>&nbsp;<b><a href='./list.php?ca_id={$save[ca_id]}'>$row[ca_name]</a></b> ($save[cnt])</td>
        <td align=center>판매가격</td>
        <td align=center>포인트</td>
    </tr>
    <tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
    ";
    */

    // 김선용 2006.12 : 중복 하위분류명이 많으므로 대분류 포함하여 출력
     $ca_temp = "";
     if(strlen($save['ca_id']) > 2) // 중분류 이하일 경우
     {
         $sql2 = " select ca_name from $g4[yc4_category_table] where ca_id='".substr($save[ca_id],0,2)."' ";
        $row2 = sql_fetch($sql2);
        $ca_temp = "<b><a href='./list.php?ca_id=".substr($save[ca_id],0,2)."'>$row2[ca_name]</a></b> &gt; ";
     }
    echo "
    <table width=100% cellpadding=0 cellspacing=0 border=0 class='list_styleA' style='margin:10px 0;'>
	<colgroup>
		<col width='100px'/>
		<col />
		<col width='80px'/>
		<col width='80px'/>
	</colgroup>
	<thead>
    <!--<tr><td colspan=4 height=2 bgcolor=#fa5a00></td></tr>-->
    <tr>
        <th colspan=2 style='text-align:left;padding-left:10px;'>{$ca_temp}<strong><a href='./list.php?ca_id={$save[ca_id]}'>$row[ca_name]</a></strong> ($save[cnt])</td>
        <th align=center>판매가격</td>
        <th align=center>포인트</td>
    </tr>
	</thead>
	<tbody>
    <!--<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>-->";

    for ($i=0; $i<$save[cnt]; $i++) {
        $sql = " select it_id,
                        it_name,
                        it_amount,
                        it_amount2,
                        it_amount3,
                        it_tel_inq,
                        it_point,
                        it_type1,
                        it_type2,
                        it_type3,
                        it_type4,
                        it_type5
                   from $g4[yc4_item_table] where it_id = '{$save[it_id][$i]}' ";
        $row = sql_fetch($sql);

        $image = get_it_image("$row[it_id]_s", (int)($default[de_simg_width] / $image_rate), (int)($default[de_simg_height] / $image_rate), $row[it_id],null,null,true,true);

//        if ($i > 0)
            // echo "<tr><td height=1></td><td bgcolor=#CCCCCC colspan=3></td></tr>";
		$row['it_name'] = get_item_name($row['it_name'],'list');

		if(trim($_GET['search_str']) != ''){
			$it_name = search_font(stripslashes($_GET['search_str']), stripslashes($row['it_name']));
		}else if(trim($_GET['it_maker']) != ''){
			$it_name = search_font(stripslashes(trim($_GET['it_maker'])), stripslashes($row['it_name']));
		}



        echo "
            <tr>
                <td style='padding:7px;text-align:left;'>$image</td>
                <!--<td>".it_name_icon($row)."</td>-->
				<td style='text-align:left;'><a href='{$g4['shop_path']}/item.php?it_id={$row['it_id']}'>".$it_name."</a></td>
                <!-- <td><span class=amount>".display_amount($row[it_amount])."<em>원</em></span></td> -->
                <td><span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."<em>원</em></span></td>
                <td><span class=item_point>".display_point($row[it_point])."</span></td>
            </tr>";
    }
    // echo "<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>";
    echo "</tbody></table>\n";
}
# 검색어가 없다면 여기로 이동 2014-09-10 홍민기 #
no_str :
include_once("./_tail.php");
?>
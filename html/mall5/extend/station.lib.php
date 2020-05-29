<?
# 2014-07-08 현재 재품관 정보 로드 #
$_SESSION['s_id'] = 6;
$station = sql_fetch("select s_id,name,head_file,index_file,view from yc4_station where s_id = '".$_SESSION['s_id']."' and view = 'Y'");
if(!$station['s_id']){
	$_SESSION['s_id'] = 6;
	$station = sql_fetch("select s_id,name,head_file,index_file,view from yc4_station where s_id = '".$_SESSION['s_id']."' and view = 'Y'");
}




$wrap_class = $station['index_file'];


# 장바구니 수량 로드 #

//$cate_cnt = sql_fetch("select count(*) as cnt from ".$g4['yc4_cart_table']." where on_uid = '".$_SESSION['ss_on_uid']."' and ihappy_fg is null");
//$cate_cnt = $cate_cnt['cnt'];
$cate_cnt = 0;


# 찜목록 수량 로드 #
//$wish_cnt = sql_fetch("select count(*) as cnt from ".$g4['yc4_wish_table']." where mb_id = '".$member['mb_id']."' and ihappy_fg is null");
//$wish_cnt = $wish_cnt['cnt'];

$wish_cnt = 0;


# 제품관 카테고리 리스트 로드 해당 카테고리의 서브 카테고리 로드 #
function station_category_list($ca_id){
	$name_sort_arr = array(
		'10','11','13'
	);
	if(in_array($ca_id,$name_sort_arr)){
		$order_by = "c.ca_view desc,c.ca_name,b.sort,depth asc";
	}else{
		$order_by = "c.ca_view desc,c.ca_id,b.sort,depth asc";
	}
	$sql = sql_query("
		select
			c.ca_id,
			c.ca_name,
			c.ca_view,
			round(length(c.ca_id) / 2) as depth,
			left( c.ca_id,2 ) as top_ca_id,
			left( c.ca_id,(length(c.ca_id)-2) ) as parent_ca_id
		from
			yc4_station a
			left join
			shop_category b on a.s_id = b.s_id
			left join
			yc4_category_new c on left(c.ca_id,2) = left(b.ca_id,2)
		where
			a.s_id = '".$_SESSION['s_id']."'
			and
			c.ca_use = 1
			and
			c.ca_id like '".$ca_id."%'
			and
			length(c.ca_id) = '".(strlen($ca_id)+2)."'

		order by ".$order_by."
	");

	while($row = sql_fetch_array($sql)){
		$result[] = $row;
	}

	return $result;
}

# 상단 네비게이션에 서브카테고리 출력
function sub_cate_view($ca_id,$row_cnt,$sub_view = false){
	global $g4;
	$cate_1 = station_category_list($ca_id);

	$cnt = 0;
	$row_cnt = $row_cnt;
	for($i=0; $i<count($cate_1); $i++){
		if(!$div_open){
			$div_open = true;
			$cate_depth_1 .= "<div>";
			if($sub_view){
				$cate_depth_1 .= "<dl>";
			}
		}
		$cnt++;
		$cnt++;
		if($sub_view){
			$cate_tag = "dd";
		}else{
			$cate_tag = "dt";
		}
		if(!$sub_view){
			$cate_depth_1 .= "<dl>";
		}
		# 1depth 카테고리 #
		$cate_depth_1 .= "<".$cate_tag."><a href='".$g4['shop_path']."/list.php?ca_id=".$cate_1[$i]['ca_id']."'>".$cate_1[$i]['ca_name']."</a></".$cate_tag.">";
		# 2depth 카테고리 #

		if(!$sub_view){
			if($cate_sub = station_category_list($cate_1[$i]['ca_id'])){
				for($ii=0; $ii<count($cate_sub); $ii++){
					$cate_depth_1 .= "<dd><a href='".$g4['shop_path']."/list.php?ca_id=".$cate_sub[$ii]['ca_id']."'>".$cate_sub[$ii]['ca_name']."</a></dd>";

					$cnt++;
				}
			}
		}
		if(!$sub_view){
			$cate_depth_1 .= "</dl>";
		}

		if($div_open && $cnt >= $row_cnt || !$cate_1[$i+1]){
			if($sub_view){
				$cate_depth_1 .= "<dl>";
			}
			$cate_depth_1 .= "</div>";
			$div_open = false;
			$cnt = 0;

		}

	}

	return $cate_depth_1;
}

function sub_cate_cache_write($ca_id,$row_cnt,$sub_view = false){
	global $g4;
	$cate_1 = station_category_list($ca_id);

	$cnt = 0;
	$row_cnt = $row_cnt;
	for($i=0; $i<count($cate_1); $i++){

		if($cate_1[$i]['ca_view'] == 1){
			$hide_cate = true;
			$ca_view = ' ca_view';
		}else{
			$ca_view = '';
		}


		if(!$div_open){
			$div_open = true;
			$cate_depth_1 .= "<div>\n";
			if($sub_view){
				$cate_depth_1 .= "\t<dl>\n";
			}
		}
		$cnt++;
		$cnt++;

		if($sub_view){
			$cate_tag = "dd";
		}else{
			$cate_tag = "dt";
		}
		if(!$sub_view){
			$cate_depth_1 .= "\t<dl>";
		}
		# 1depth 카테고리 #
		$cate_depth_1 .= "<".$cate_tag.$ca_view."><a href='";
		$cate_depth_1 .= '<?=$g4[shop_path];?>/list.php?';
		$cate_depth_1 .= "ca_id=".$cate_1[$i]['ca_id']."'>".$cate_1[$i]['ca_name']."</a></".$cate_tag.">";
		# 2depth 카테고리 #

		if(!$sub_view){
			if($cate_sub = station_category_list($cate_1[$i]['ca_id'])){
				for($ii=0; $ii<count($cate_sub); $ii++){

					if($cate_sub[$ii]['ca_view'] == 1){
						$hide_cate = true;
						$ca_view = ' ca_view';
					}else{
						$ca_view = '';
					}

					$cate_depth_1 .= "<dd".$ca_view."><a href='";
					$cate_depth_1 .= '<?=$g4[shop_path]?>/list.php?';
					$cate_depth_1 .= "ca_id=".$cate_sub[$ii]['ca_id']."'>".$cate_sub[$ii]['ca_name']."</a></dd>";


					$cnt++;

				}
			}
		}
		if(!$sub_view){
			$cate_depth_1 .= "</dl>\n";
		}

		if($div_open && $cnt >= $row_cnt || !$cate_1[$i+1]){
			if($sub_view){
				$cate_depth_1 .= "\t<dl>\n";
			}
			$cate_depth_1 .= "</div>\n";
			$div_open = false;
			$cnt = 0;

		}

	}

	if($hide_cate){
		$cate_depth_1 .= "<div class='hide_cate_flag' ca_id='".$ca_id."'></div>";
	}

	$file = fopen($g4['full_path'].'/cache/ca_navi_'.$ca_id.'.htm','w');
	fwrite($file,$cate_depth_1);
	fclose($file);

	return true;

}

function sub_cate_cache_load($ca_id,$row_cnt=10,$sub_view = false){
	global $g4;
	if(!file_exists($g4['full_path'].'/cache/ca_navi_'.$ca_id.'.htm')){
		cate_sub_navi_view($ca_id,$row_cnt,$sub_view);
//		return false;
	}
	ob_start();
	include $g4['full_path'].'/cache/ca_navi_'.$ca_id.'.htm';
	$result = ob_get_contents();
	ob_end_clean();

//	$result = file_get_contents($g4['path'].'/cache/ca_navi_'.$ca_id.'.htm');
	return $result;

}

function sub_cate_cache_load_new($ca_id,$more =false){
	global $g4;
	if(!file_exists($g4['full_path'].'/cache/ca_navi_'.$ca_id.'.htm')){
		cate_sub_navi_view($ca_id,$more);
//		return false;
	}
	ob_start();
	@include $g4['full_path'].'/cache/ca_navi_'.$ca_id.'.htm';
	$result = ob_get_contents();
	ob_end_clean();

//	$result = file_get_contents($g4['path'].'/cache/ca_navi_'.$ca_id.'.htm');
	return $result;

}

function sub_cate_more_cache_load_new($ca_id){
	global $g4;
	if(!file_exists($g4['full_path'].'/cache/ca_navi_more_'.$ca_id.'.htm')){
		cate_sub_navi_view_more($ca_id);
//		return false;
	}
	ob_start();
	include $g4['full_path'].'/cache/ca_navi_more_'.$ca_id.'.htm';
	$result = ob_get_contents();
	ob_end_clean();

//	$result = file_get_contents($g4['path'].'/cache/ca_navi_'.$ca_id.'.htm');
	return $result;

}


function cate_sub_navi_view($ca_id,$more=false){
	global $g4;
	$sql = sql_query("
		select
			b.ca_id,b.ca_name,b.ca_view
		from
			shop_category a
			left join
			".$g4['yc4_category_table']." b on b.ca_id like concat(a.ca_id,'%')
		where
			a.s_id = '".$_SESSION['s_id']."'
			and
			b.ca_id like '".$ca_id."%'
			and
			b.ca_use = 1
			and
			b.ca_view = 1
			and
			length(b.ca_id) > ".strlen($ca_id)."
		order by
			b.ca_id
	");
	$cate_cnt = mysql_num_rows($sql);
	$cnt = 0;
	while($row=sql_fetch_array($sql)){
		$cnt++;
		$depth = strlen($row['ca_id'])/2 -1;


		switch($depth){
			case '1' :
				if($st[$depth]) {
					if($st[2]){
						$cate_view .=  " </a></em>\n";
						if($bf_depth && $bf_depth != $depth){
							$cate_view .=  "\t\t</span><!--inBox_depth end-->\n";
							$cate_view .=  "\t</span><!--inBox_depth_warp end-->\n";
						}
					}
					$cate_view .=  "</dd> \n";

					$st[2] = false;
					$st[3] = false;
					$st[4] = false;
					$st[5] = false;
				}
				$cate_view .=  "<dd> \n";
				$cate_view .=  "\t<strong><a href='shop_path/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."</a></strong>\n";
				/*
				if($bf_depth != $depth){

					$cate_view .= "\t<span class='inBox_depth_warp' style='display:none;'>\n";
					$cate_view .= "\t\t<span class='pointer'>포인트</span>\n";
					$cate_view .= "\t\t<span class='inBox_depth'>\n";
				}
				*/
				break;
			case '2' :

				if($st[1] && !$st[2]){
					$cate_view .= "\t<span class='inBox_depth_warp' style='display:none;'>\n";
					$cate_view .= "\t\t<span class='pointer'>포인트</span>\n";
					$cate_view .= "\t\t<span class='inBox_depth'>\n";
				}

				if($st[$depth]){
					$cate_view .=  " </a></em>\n";
					$st[3] = false;
					$st[4] = false;
					$st[5] = false;
				}
				$cate_view .=  "\t\t\t<em><a href='shop_path/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."";
				if($cate_cnt == $cnt){
					$cate_view .= "</a></em>\n";
					$cate_view .=  "\t\t</span><!--inBox_depth end-->\n";
					$cate_view .=  "\t</span><!--inBox_depth_warp end-->\n";
					$cate_view .= "</dd>";
				}
				break;
		}
		$st[$depth] = true;
		$bf_depth = $depth;

	}
	if($cate_view){
		/*
		$cate_view = "
	<p class='pointer'>포인터(열기)</p>
	<div>
		<dl>
	".$cate_view."
	".($more ? "<a hre='#' class='more' onclick=\"showDetailCategory('".$ca_id."');\">더보기</a>":"")."
		</dl>
	</div>
		";
		*/
		$cate_view = "
	<p class='pointer'>포인터(열기)</p>
	<div>
		<dl>
	".$cate_view."
		</dl>
	</div>
		";
		$file = fopen($g4['full_path'].'/cache/ca_navi_'.$ca_id.'.htm','w');
		fwrite($file,str_replace('shop_path','<?php echo $g4[shop_path];?>',$cate_view));
		fclose($file);
	}
	return $cate_view;
}


function cate_sub_navi_view_more($ca_id){
	global $g4;
	$sql = sql_query("
		select
			b.ca_id,b.ca_name,b.ca_view
		from
			shop_category a
			left join
			".$g4['yc4_category_table']." b on b.ca_id like concat(a.ca_id,'%')
		where
			a.s_id = '".$_SESSION['s_id']."'
			and
			b.ca_id like '".$ca_id."%'
			and
			b.ca_use = 1
			and
			length(b.ca_id) = ". (strlen($ca_id) + 2) ."
		order by
			b.ca_id
	");
	$cate_cnt = mysql_num_rows($sql);

	$cnt = 0;
	while($row=sql_fetch_array($sql)){
		$cnt++;
		$depth = strlen($row['ca_id'])/2 -1;

//		switch($depth){
//			case 1 :
//
//				if(!$bf_depth){
//					$ca_view .= "<p class='pointer'>포인터(열기)</p>\n";
//
//					$ca_view .= "\t<dl>\n";
//				}else{
//					$ca_view .= "\t</dl>\n";
//
//
//					$ca_view .= "\t<dl>\n";
//				}
//				*/
//				$ca_view .= "\t\t<dt ca_view=''><a href='".$g4['shop_path']."/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."</a></dt>\n";
//
//				break;
//			case 2 :
//				$ca_view .= "\t\t<dd ca_view=''><a href='".$g4['shop_path']."/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."</a></dd>\n";
//				/*
//				if($bf_depth > $depth){
//					$ca_view .= "\t</dl>\n";
//
//				}
//				*/
//				break;
//		}
		$ca_view .= "\t\t<dd ca_view=''><a href='".$g4['shop_path']."/list.php?ca_id=".$row['ca_id']."'>".$row['ca_name']."</a></dd>\n";

		$bf_depth = $depth;
	}
	if($ca_view){
		$ca_view = "
			<dl>
				".$ca_view."
				<dd><a href='#' class='more_close' onclick=\"$('.DepthCategory[ca_id=".$ca_id."]').hide().empty(); return false;\">접기</a></dd>
			</dl>";
		$file = fopen($g4['full_path'].'/cache/ca_navi_more_'.$ca_id.'.htm','w');
		fwrite($file,str_replace($g4['path'],'<?php echo $g4[path];?>',$ca_view));
		fclose($file);

	}
	return $ca_view;
}
?>
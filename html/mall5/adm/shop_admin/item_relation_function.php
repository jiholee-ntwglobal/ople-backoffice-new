<?php
function showpage($npage, $totalrec, $nblocksize, $npagesize, $mode='ca', $li_tab='t_all') {
	$str = "";
	$nblock				= ceil($npage/$nblocksize);						//현재블록
	$prev_start_page	= (((int)(($npage-1-$nblocksize)/$nblocksize))*$nblocksize)+1;	//이전블록 시작페이지
	$next_start_page	= (((int)(($npage-1+$nblocksize)/$nblocksize))*$nblocksize)+1;	//다음블록 시작페이지
	$startpage			= (((int)(($npage-1)/$nblocksize))*$nblocksize)+1;				//링크시작페이지
	$endpage			= $startpage+$nblocksize-1;										//링크 끝페이지
	$totalpage			= ceil($totalrec/$npagesize);									//전체페이지
	$totalblock			= ceil($totalpage/$nblocksize);									//전체 블록
	
	// 처음으로
	if ($npage == 1) {
		$str = "";
	}else {
		$str = "<a href='#' onclick='cate_src(\"".$mode."\",\"1\",\"".$li_tab."\")'>처음</a>";
	}

	// 이전
	if ($nblock == 1) {
		$str.= "";
	}else {
		$str.= "<a href='#' onclick='cate_src(\"".$mode."\",\"".$prev_start_page."\",\"".$li_tab."\")'>이전</a>&nbsp;";
	}

	// 페이지표시
	for ($i = $startpage; $i <= $endpage && $i <= $totalpage; $i++) {
		if ($i == $npage) {
			$str.= "<b>".$i."</b>&nbsp;";
		}else {
			$str.= "<a href='#' onclick='cate_src(\"".$mode."\",\"".$i."\",\"".$li_tab."\")'>[".$i."]</a>&nbsp;";
		}
	}

	// 다음
	if ($nblock == $totalblock) {
		$str.= "";
	}else {
		$str.= "<a href='#' onclick='cate_src(\"".$mode."\",\"".$next_start_page."\",\"".$li_tab."\")'>다음</a>&nbsp;";
	}

	// 끝으로
	if ($npage == $totalpage) {
		$str.= "";
	}else {
		$str.= "<a href='#' onclick='cate_src(\"".$mode."\",\"".$totalpage."\",\"".$li_tab."\")'>끝</a>&nbsp;";
	}
	if($totalrec == "0"){
		$str	= "표시할 페이지가 없습니다.";
	}
	return $str;
}

function ntics_data($upc_arr, $pdo_db){
	$return	= array();
	if(count($upc_arr)>0){
		$sql	= $pdo_db->prepare("
					SELECT
						i.upc, i.currentqty, w.wholesale_price, w.exept_explain
					FROM
						N_MASTER_ITEM i
					LEFT JOIN
						N_MASTER_ITEM_WHOLESALE_PRICE w ON w.upc=i.upc AND w.isnow='Y'
					WHERE
						i.upc IN (".implode(',',array_fill(0,count($upc_arr),'?')).")");
		$sql	->execute($upc_arr);
		$res	= $sql->fetchAll(PDO::FETCH_ASSOC);
		foreach($res as $val){
			$return[$val['upc']]['currentqty']		= $val['currentqty'];
			$return[$val['upc']]['wholesale_price']	= $val['wholesale_price'];
			$return[$val['upc']]['exept_explain']	= $val['exept_explain'];
		}
	}
	return $return;
}

//	yc4_category_item a
//		left join
//		".$g4['yc4_category_table']." b on a.ca_id = b.ca_id
//		left join
//		shop_category c on c.ca_id = substr(b.ca_id,1,2)
//		left join
//		yc4_station d on c.s_id = d.s_id
	
//	$where .= " and a.it_id = '{$it['it_id']}'";
//	$select = 'a.ca_id,a.it_id, b.ca_name,d.name,d.s_id';
//	$order_by = "order by a.ca_id like '".$_GET['ca_id']."%' desc";

function cate_name($it_id,$ca_id="",$ople){
	$cate_list	= "";
	if($ca_id!=""){
		$len = strlen($ca_id) / 2;
		for ($ii=1; $ii<=$len; $ii++){
			$code = substr($ca_id,0,$ii*2);
	        $sql	= "select ca_name from yc4_category_new where ca_id = '$code' ";
	        $row	= $ople->query($sql)->fetch(PDO::FETCH_COLUMN);
	        $cate_list	.= $row."->";
		}
		$cate_list	= substr($cate_list,0,-2);
	}else{
		$cate_sql	= $ople->prepare('SELECT c.ca_id FROM yc4_category_new c LEFT JOIN yc4_category_item b on b.ca_id=c.ca_id LEFT JOIN yc4_item i ON i.it_id=b.it_id WHERE i.it_id = :it_id');
		$cate_sql	->bindParam(':it_id',$it_id);
		$cate_sql	->execute();
		$cate_res	= $cate_sql->fetchAll(PDO::FETCH_ASSOC);
		$ca_cnt		= count($cate_res);
		foreach($cate_res as $val){
			
			$len = strlen($val['ca_id']) / 2;
			for ($ii=1; $ii<=$len; $ii++){
				$code = substr($val['ca_id'],0,$ii*2);
		        $sql	= "select ca_name from yc4_category_new where ca_id = '$code' ";
		        $row	= $ople->query($sql)->fetch(PDO::FETCH_COLUMN);
		        $cate_list	.= $row."->";
			}
			$cate_list	= substr($cate_list,0,-2);
			$cate_list	.= "<br />";
		}
	}
	return $cate_list;
}

function cate_id($it_id,$ople){
	$cate_sql	= $ople->prepare('SELECT c.ca_id FROM yc4_category_new c LEFT JOIN yc4_category_item b on b.ca_id=c.ca_id LEFT JOIN yc4_item i ON i.it_id=b.it_id WHERE i.it_id = :it_id');
	$cate_sql->bindParam(':it_id',$it_id);
	$cate_sql->execute();
	$cate_res	= $cate_sql->fetchAll(PDO::FETCH_ASSOC);
	return $cate_res; 
}

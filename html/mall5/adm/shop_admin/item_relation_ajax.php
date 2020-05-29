<?php
include_once("./_common.php");
include $g4['full_path'].'/lib/db.php';
include "./item_relation_function.php";


$rel_data	= array();
$main_info	= array();
$rel_list	= "";

$pdo		= new db();
$ople		= $pdo->ople_db_pdo;
$ntics		= $pdo->ntics_db;

if(isset($_POST['mode'])){
	if($_POST['mode']=="ajax"){
		$main_id= $_POST['main_id'];
		$page	= isset($_POST['page']) ?$_POST['page'] : "1";
		$sort	= isset($_POST['sort']) ?$_POST['sort'] : "";
		$pg_row	= 10;
		$st_rec	= ($page-1)*$pg_row; 
		$where	= "WHERE i.it_use=1 AND i.it_discontinued=0 AND i.it_id!='".$main_id."'";
		$li_tab	= $_POST['li_tab'];
		if($li_tab=='t_rel'){
			$where	.= " AND r.it_id IS NOT NULL ";
		}elseif($li_tab=='t_rel_no'){
			$where	.= " AND r.it_id IS NULL ";
		}else{
		
		}
		
		if($sort==''){
			$orderby	= " ORDER BY i.ps_cnt DESC ";
		}elseif($sort=='qty'){
		
		}
		
		
		if($_POST['func']=="ca"){
			$ca_arr	= explode(',',$_POST['ca_id']);
			$values	= array_map(array($ople,'quote'),$ca_arr);
			$where	.= " AND c.ca_id in (".join(',',$values).") ";
		
		}elseif($_POST['func']=="src"){
			$src_name	= trim($_POST['src_name']);
			$src_val	= trim($_POST['src_value']);
			
			if($src_name=="it_id"){
				$where	.= " AND i.".$src_name."='".$src_val."' ";
			}elseif($src_name=="upc"){
				$where	.= " AND m.".$src_name."='".$src_val."' ";
			}elseif($src_name=="it_name" || $src_name=="it_maker"){
				$where	.= " AND i.".$src_name." like '%".$src_val."%' ";
			}else{
				echo "검색영역이 올바르지 않습니다.";
				exit;
			}
		}
//		echo "SELECT count(distinct i.it_id) as cnt FROM yc4_item i LEFT JOIN yc4_category_item c ON i.it_id=c.it_id LEFT JOIN ople_mapping m ON m.it_id=i.it_id LEFT JOIN yc4_item_relation r ON r.it_id=i.it_id ".$where;
//		exit;
		$r_cnt	= $ople->query("SELECT count(distinct i.it_id) as cnt FROM yc4_item i LEFT JOIN yc4_category_item c ON i.it_id=c.it_id LEFT JOIN ople_mapping m ON m.it_id=i.it_id LEFT JOIN yc4_item_relation r ON r.it_id=i.it_id ".$where);
		$rows	= $r_cnt->fetchColumn();
		
		$ajax_sql	= "
					SELECT
						i.it_id, i.it_name, i.it_maker, i.it_amount_usd, i.ps_cnt, m.upc
					,	(CASE WHEN m.ople_type = 's' THEN 'set' ELSE '' END) AS 'set_fg'
					,	(CASE WHEN c.it_id IS NULL AND i.it_amount = '99999' THEN '가등록' ELSE '' END ) AS 'regi_fg'
					FROM
						yc4_item i
					LEFT JOIN
						yc4_category_item c ON i.it_id=c.it_id
					LEFT JOIN
						ople_mapping m ON i.it_id=m.it_id
					LEFT JOIN
						yc4_item_relation r ON r.it_id=i.it_id
					".$where."
					GROUP BY
						i.it_id
					".$orderby."
					LIMIT
						".$st_rec.", ".$pg_row;
		
		$ajax_res	= $ople->query($ajax_sql);
		$src_val	= "<strong>전체 (".$rows.")개 상품</strong>";

//	echo $ajax_sql;
//	exit;

		$src_list	= "
<div class='container-fluid'>
    <div class='alert alert-info'>
		".$src_val."
    </div>
</div>
<div class='container-fluid'>
	<div class='row'>
		<button class='btn btn-success pull-right' onclick='edit_row_chk(\"add\")'>선택상품 추가</button>
		<div class='text-center'>".showpage($page, $rows, '10', '10', $_POST['func'], $li_tab)."</div>
	</div>
	<div class='row'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered table-hover' id='src_res'>
				<thead>
					<tr>
						<td width='10px'><input type='checkbox' name='all_chk_rel' value='".$val['it_id']."' /></td>
						<td width='50px'>이미지</td>
						<td width='90px'>it_id<br />upc</td>
						<td width='100px'>브랜드</td>
						<td width='100px'>상품명</td>
						<td width='80px'>
							<a href='#' onclick='cate_src(\"".$_POST['func']."\",\"1\",\"".$li_tab."\")'>입고가</a><br />
							<a href='#' onclick='cate_src(\"".$_POST['func']."\",\"1\",\"".$li_tab."\")'>(판매가)</a>
						</td>
						<td width='80px'><a href='#' onclick='cate_src(\"".$_POST['func']."\",\"1\",\"".$li_tab."\")'>재고</a></td>
						<td width='80px'><a href='#' onclick='cate_src(\"".$_POST['func']."\",\"1\",\"".$li_tab."\")'>후기수</a></td>
						<td width='80px'>세트</td>
						<td width='80px'>가등록</td>
						<td width='50px'></td>
					</tr>
				</thead>
				<tbody>
		";
		$upc_in	= array();
		while($row = $ajax_res->fetchALL()){
			$src_info	= $row;
		}
		if(count($src_info)>0){
		foreach($src_info as $val){
			array_push($upc_in,$val['upc']);
		}
		$src_ntics	= ntics_data($upc_in,$ntics);
		for($i=0; $i<count($src_info); $i++){
			$src_info[$i]['wholesale_price']	= $src_ntics[$src_info[$i]['upc']]['wholesale_price'];
			$src_info[$i]['exept_explain']		= $src_ntics[$src_info[$i]['upc']]['exept_explain'];
			$src_info[$i]['currentqty']			= $src_ntics[$src_info[$i]['upc']]['currentqty'];
		}
//asort($src_info);


		foreach($src_info as $val){
			$op_amount	= $val['it_amount_usd']=='' ? number_format($val['it_amount']/$default['de_conv_pay'],2) : $val['it_amount_usd'];
			$ws_amount	= $val['wholesale_price'] ? $val['wholesale_price'] : $val['exept_explain'];
			$src_list	.= "
	                <tr>
						<td>
							<input type='checkbox' name='chk_rel[]' value='".$val['it_id']."' />
						</td>
						<td>
							<img src='http://115.68.20.84/item/".$val['it_id']."_s' height='50' width='50'/>
						</td>
						<td>
							".$val['it_id']."<br /><br />
							".$val['upc']."
						</td>
						<td>
							".$val['it_maker']."
						</td>
						<td><a href='http://ople.com/mall5/shop/item.php?it_id=".$val['it_id']."' target='blank'>
							".get_item_name($val['it_name'],'list')."
						</a></td>
						<td>
							".$ws_amount."<br /><br />
							(".$op_amount.")
						</td>
						<td>
							".$src_ntics[$val['upc']]['currentqty']."
						</td>
						<td>
							".$val['ps_cnt']."
						</td>
						<td>
							".$val['set_fg']."
						</td>
						<td>
							".$val['regi_fg']."
						</td>
						<td>
							<button class='btn' onclick=\"edit_row('add',this)\">추가</button>
						</td>
					</tr>
					";
		}
        $src_list	.= "
				</tbody>
			</table>
		</div>
    </div>
    <div class='row'>
		<button class='btn btn-success pull-right' onclick='edit_row_chk(\"add\")'>선택상품 추가</button>
    	<div class='text-center'>".showpage($page, $rows, '10', '10', $_POST['func'], $li_tab)."</div>
    </div>
</div>
		";
		}else{
		$src_list	= "검색결과가 없습니다.";
		}
	}else{
		$src_list	= "검색결과가 없습니다.";
	}
	echo $src_list;
	exit;
}
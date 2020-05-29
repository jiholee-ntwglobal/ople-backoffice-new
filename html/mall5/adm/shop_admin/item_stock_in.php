<?php
$sub_menu = "300360";
include_once "./_common.php";
auth_check($auth[$sub_menu], "r");
include_once $g4['full_path']."/lib/opk_db.php";
$opk_db = new opk_db;


if($_POST['mode']){
	if($_POST['mode'] == 'one_item'){
		# 해당 제품의 수량 및 단종처리 해제 #
		$update_qry = "
			update
				".$g4['yc4_item_table']."
			set
				it_discontinued = 0,
				it_stock_qty = 99999
			where
				it_id = '".$_POST['it_id']."'
		";

		sql_query("update yc4_soldout_history set current_fg='N' where it_id='".$_POST['it_id']."'");

		$insert_qry = "
			insert into
				yc4_soldout_history
			(
				it_id,flag,mb_id,time,ip,current_fg
			)values(
				'".$_POST['it_id']."','i','".$member['mb_id']."','".$g4['time_ymdhis']."','".$_SERVER['REMOTE_ADDR']."','Y'
			)
		";

		$soldout_history_fnc = function($it_id,$fg,$mb_id){
			global $g4;
			if(!in_array($fg,array('Y','N'))){
				return false;
			}

			if(!$mb_id){
				$mb_id = $_SESSION['ss_mb_id'];
			}
			if(!$mb_id){
				return false;
			}

			include_once $g4['full_path'].'/lib/db.php';
			$db = new db();
			$ntics_stmt =  $db->ntics_db->prepare("select a.upc,b.currentqty from ople_mapping a left join N_MASTER_ITEM b on a.upc = b.upc where a.it_id = ? and b.upc is not null");
			$ntics_stmt->execute(array($it_id));
			if($ntics_stmt === false){
				return false;
			}
			$ntics_data = $ntics_stmt->fetch(PDO::FETCH_ASSOC);
			if(!trim($ntics_data['upc'])){
				return false;
			}
			$params = array('OPLE',$ntics_data['upc'],$it_id,$fg,'OPLE-'.$mb_id,$ntics_data['currentqty']);
//		$db->ntics_db->beginTransaction();
			$insert_stmt = $db->ntics_db->prepare("insert into soldout_proc_history (channel,upc,it_id,flag,create_dt,create_id,ntics_qty) VALUES (?,?,?,?,FORMAT ( GETDATE(), 'yyyyMMddHHmmss'),?,?)");
			if($insert_stmt->execute($params) === false){
				return false;
			}
			$uid = $db->ntics_db->lastInsertId();
			if(!$uid){
				return false;
			}

//		$db->ntics_db->rollBack();
			return true;
		};
		$soldout_history_fnc($_POST['it_id'],'N',$member['mb_id']);


		if(sql_query($update_qry)){
            $opk_db->query($update_qry);
			$msg = "해당 상품을 품절 해제 처리하였습니다.";
		}
	}

	if($_POST['mode'] == 'many_item'){
		if(is_array($_POST['it_id_arr'])){
			$i = 0;
			foreach($_POST['it_id_arr'] as $val){
				$i++;
				$it_id_in .= ( ($it_id_in) ? ", ":"" )."'".$val."'";
				$insert_qry_value .= (($insert_qry_value) ? ", ":"")."('".$val."','i','".$member['mb_id']."','".$g4['time_ymdhis']."','Y')";				
			}

			if($it_id_in) {
				$it_id_in = '('.$it_id_in.')';
			}
		}

		$update_qry = "
			update
				".$g4['yc4_item_table']."
			set
				it_discontinued = 0,
				it_stock_qty = 99999
			where
				it_id in ".$it_id_in."
		";

		sql_query("update yc4_soldout_history set current_fg='N' where it_id in ".$it_id_in);
		

		$insert_qry = "
			insert into
				yc4_soldout_history
			(
				it_id,flag,mb_id,time,current_fg
			)
			values ".$insert_qry_value."
		";


		$soldout_history_fnc = function($it_id_arr = array(),$fg,$mb_id){
			global $g4;
			if(!in_array($fg,array('Y','N'))){
				return false;
			}
			if(count($it_id_arr) < 1){
				return false;
			}

			if(!$mb_id){
				$mb_id = $_SESSION['ss_mb_id'];
			}
			if(!$mb_id){
				return false;
			}

			include_once $g4['full_path'].'/lib/db.php';
			$db = new db();
			$ntics_stmt =  $db->ntics_db->prepare("
				select a.it_id,a.upc,b.currentqty 
				from 
				ople_mapping a 
				left join 
				N_MASTER_ITEM b on a.upc = b.upc 
				where a.it_id in (".explode(',',array_fill(0,count($it_id_arr),'?')).") and b.upc is not null
			");
			$ntics_stmt->execute($it_id_arr);
			if($ntics_stmt === false){
				return false;
			}
			$ntics_data = $ntics_stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($ntics_data as $row) {
				if(!trim($row['upc'])){
					continue;
				}
				$params = array('OPLE',$row	['upc'],$row['it_id'],$fg,'OPLE-'.$mb_id,$row['currentqty']);
				$insert_stmt = $db->ntics_db->prepare("insert into soldout_proc_history (channel,upc,it_id,flag,create_dt,create_id,ntics_qty) VALUES (?,?,?,?,FORMAT ( GETDATE(), 'yyyyMMddHHmmss'),?,?)");
				if($insert_stmt->execute($params) === false){
					continue;
				}
			}

			return true;
		};
		$soldout_history_fnc($_POST['it_id_arr'],'N',$member['mb_id']);


		# 입고처리만 해 놓으면 sms는 크론에서 처리한다 #
		if(sql_query($update_qry)){
			sql_query($insert_qry);
            $opk_db->query($update_qry);
			$msg = $i."개의 상품을 품절 해제 처리하였습니다.";
		}

	}

	if($msg){
		alert($msg,$_SERVER['PHP_SELF']);
	}else{
		echo "
			<script>
				alert('처리중 오류 발생! 다시 시도해 주세요');
				history.back();
			</script>
		";
	}

	# 입고처리만 해 놓으면 sms는 인트라넷 크론에서 처리한다 #

	exit;
}

if($_GET['st_dt'] && $_GET['en_dt']){
	$sales_where .= " and ct_time between '".$_GET['st_dt']." 00:00:00' and '".$_GET['en_dt']." 23:59:59'";
}

$column = "
	it_id,
	it_name,
	it_stock_qty,
	it_discontinued,
	SKU,
	(select count(*) from yc4_add_item_sms where it_id = ".$g4['yc4_item_table']." .it_id) as sms_cnt,
	(select ifnull(sum(ct_qty),0) from yc4_cart where it_id = yc4_item.it_id ".$sales_where.") as sell,
	it_stock_qty as qty
";
$from = $g4['yc4_item_table'];
//$where .= (($where) ? " and ":"")."it_stock_qty <= 0";
$where .= (($where) ? " and ":"")."
	it_stock_qty  <= 0
";
if($_GET['sort'] == 'sale'){
	$order_by = "order by sell desc";
}else{
	$order_by = "order by sms_cnt desc";
}


# 검색처리 #
if($_GET['it_id']){
	$where .= (($where) ? " and ":"") . "it_id = '".$_GET['it_id']."'";
}
if($_GET['SKU']){
	$where .= (($where) ? " and ":"") . "SKU = '".$_GET['SKU']."'";
}
if($_GET['it_name']){
	$where .= (($where) ? " and ":"") . "it_name like '%".$_GET['it_name']."%'";
}




// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt from " . $from . " where " .$where;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


# 픔절상품 리스트 쿼리 #
sql_set_charset('utf8');
$sold_out_list_qry = sql_query($a="
	select
		".$column."
	from
		".$from."
	where
		".$where."
	".$order_by."
	".((!$_GET['excel']) ? "limit ".$from_record.", ".$rows:"")."
");

if($_GET['excel']){
	ini_set('memory_limit',-1);
	include $g4['path'].'/classes/PHPExcel.php';

	$excel_title = '오플 품절상품 리스트-'.date('Y-m-d');


	$objPHPExcel = new PHPExcel();

	$objPHPExcel->getProperties()->setCreator("오플닷컴")
								 ->setTitle($excel_title)
								 ->setSubject($excel_title)
								 ->setDescription($excel_title);

	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue("A1", $excel_title)
				->setCellValue("A2", "UPC")
				->setCellValue("B2", "오플상품코드")
				->setCellValue("C2", "상품명")
				->setCellValue("D2", "입력재고 / 계산재고")
				->setCellValue("E2", "단종여부")
				->setCellValue("F2", "SMS신청")
				->setCellValue("G2", "판매량");


	$line = 3;

	while($row = sql_fetch_array($sold_out_list_qry)){


		$it_name = preg_replace("/[^ㄱ-힣 a-zA-Z0-9\-*\[\]]/u", "", $row['it_name']);

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue("A".$line, $row['SKU'])
					->setCellValue("B".$line, $row['it_id'])
					->setCellValue("C".$line, $it_name)
					->setCellValue("D".$line, $row['it_stock_qty'] . ' / ' . $row['qty'])
					->setCellValue("E".$line, $row['it_discontinued'])
					->setCellValue("F".$line, $row['sms_cnt'])
					->setCellValue("G".$line, $row['sell']);
		$objPHPExcel->getActiveSheet(0)->getCell("A".$line)->setValueExplicit($row['SKU'], PHPExcel_Cell_DataType::TYPE_STRING );
		$objPHPExcel->getActiveSheet(0)->getCell("B".$line)->setValueExplicit($row['it_id'], PHPExcel_Cell_DataType::TYPE_STRING );
		$line++;

	}




	// Rename sheet
	$objPHPExcel->getActiveSheet()->setTitle($excel_title);
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
	// 파일의 저장형식이 utf-8일 경우 한글파일 이름은 깨지므로 euc-kr로 변환해준다.
	$filename = iconv("UTF-8", "EUC-KR", $excel_title);
	// Redirect output to a client’s web browser (Excel5)
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
}


while($row = sql_fetch_array($sold_out_list_qry)){
	$list_tr .= "
		<tr>
			<td rowspan='2' align='center'><input type='checkbox' name='it_id_arr[]' value='".$row['it_id']."'/></td>
			<td rowspan='2'>".get_it_image($row['it_id'].'_s',30,30)."</td>
			<td>".$row['it_id']."</td>
			<td rowspan='2'>".$row['it_name']."</td>
			<td align='center'>".$row['it_stock_qty']." / ".$row['qty']."</td>

			<td rowspan='2' align='center'>".$row['sms_cnt']."</td>
			<td rowspan='2' align='right'>".number_format($row['sell'])."</td>
			<td rowspan='2' align='center'><a href='#' onclick=\"one_item_fnc('".$row['it_id']."'); return false;\">품절해제</a></td>

		</tr>
		<tr>
			<td>".$row['SKU']."</td>
			<td align='center'>".(($row['it_discontinued'] == 1) ? '<b>단종</b>' : '&nbsp;')."</td>
		</tr>
	";
}



if(is_array($_GET)){
	foreach($_GET as $key => $val){
		if($key != 'sort'){
			$param .= "&".$key."=".$val;
		}
	}
}

$g4['title'] = "품절상품관리-2014";
include_once $g4['admin_path'].'/admin.head.php';
?>

<form action="<?=$_SERVER['PHP_SELF']?>" method='get'>

	<p>상품검색</p>
	<input type="text" name='it_id' value='<?=$_GET['it_id']?>' placeholder='상품코드'/>
	<input type="text" name='it_name' value='<?=$_GET['it_name']?>' placeholder='상품명'/>
	<input type="text" name='SKU' value='<?=$_GET['SKU']?>' placeholder='SKU'/>
	<hr />

	<p>판매량 기간설정</p>
	<input type="text" name='st_dt' value='<?=$_GET['st_dt']?>' placeholder='조회 시작일 (YYYY-MM-DD)'/>
	<input type="text" name='en_dt' value='<?=$_GET['en_dt']?>' placeholder='조회 종료일 (YYYY-MM-DD)'/>

	엑셀 다운 : <input type="checkbox" name='excel' value='Y' />

	<input type="submit" value='검색' />
	<?if( $_GET['it_id'] || $_GET['it_name'] || $_GET['SKU'] ){?>
	<input type="button" value='검색 초기화' onclick="location.href='<?=$_SERVER['PHP_SELF']?>'" />
	<?}?>
</form>
<p align='right'>총 <?=number_format($total_count);?>개 상품</p>

<form action="<?=$_SERVER['PHP_SELF']?>" method='post' name='list_fnc' onsubmit="return frm_chk_fnc();">
	<input type="hidden" name='mode' value='many_item' />
	<input type="submit" value='선택 상품 품절 해제' />
	<table width='100%' border='1' style='border-collapse: collapse;'>
		<col width='30'/>
		<col width='30'/>
		<col width='90'/>
		<col width=''/>
		<col width='120'/>
		<col width='60'/>
		<col width='60'/>
		<col width='60'/>

		<tr align='center'>
			<td rowspan='2'><input type="checkbox" class='chk_all' /></td>
			<td rowspan='2'></td>
			<td>상품코드</td>
			<td rowspan='2'>상품명</td>
			<td>입력재고 / 계산재고</td>
			<td rowspan='2'><a href="<?=$_SERVER['PHP_SELF']?>?sort=sms<?=$param;?>" style="<?=(!$_GET['sort'] || $_GET['sort'] == 'sms') ? "font-weight:bold;":""?>">SMS 신청</a></td>
			<td rowspan='2'><a href="<?=$_SERVER['PHP_SELF']?>?sort=sale<?=$param;?>" style="<?=($_GET['sort'] == 'sale') ? "font-weight:bold;":""?>">판매량</a></td>
			<td rowspan='2'></td>
		</tr>
		<tr align='center'>
			<td>UPC</td>
			<td>단종여부</td>
		</tr>
		<?=$list_tr;?>
	</table>
	<input type="submit" value='선택 상품 품절 해제' />
	<div style="width:100%; margin:0 0 5px 0; text-align:center;"><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></div>
</form>

<form action="<?=$_SERVER['PHP_SELF']?>" method='post' name='one_item_frm'>
	<input type="hidden" name='mode' value='one_item'/>
	<input type="hidden" name='it_id'/>
</form>

<script type="text/javascript">
function one_item_fnc( it_id ){ // 단일 상품 품절 해제
	if(!confirm('해당 상품의 품절을 해제하시겠습니까?')){
		return false;
	}
	one_item_frm.it_id.value = it_id;

	one_item_frm.submit();
}


function frm_chk_fnc(){ // 복수건 처리시 유효성검사 체크
	if($('input[name=it_id_arr\\[\\]]:checked').length < 1){
		alert('품절 해제할 상품을 체크해 주세요');
		return false;
	}

	return true;
}

$('.chk_all').click(function(){
	if($(this).is(':checked') == true){
		$('input[name=it_id_arr\\[\\]]').prop('checked',true);
	}else{
		$('input[name=it_id_arr\\[\\]]').prop('checked',false);
	}
});

</script>

<?php
include_once $g4['admin_path']."/admin.tail.php";
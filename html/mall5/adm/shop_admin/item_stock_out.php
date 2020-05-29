<?php
$sub_menu = "300380";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");
include_once $g4['full_path']."/lib/opk_db.php";

$opk_db = new opk_db;


# 품절처리, 품절해제 처리
if($_POST['mode'] == 'pum'){

	/*
		$_POST['pum'] == 1 ? 품절해제 : 품절처리
	*/
	# 네이버에 상품 수정 테이블에 해당상품이 처리되지 않은 데이터가 있다면 update else insert
	$naver_brief_chk = sql_fetch("select uid from naver_ep_brief where it_id = '".$_POST['it_id']."' and generate_time is null");

	if(!$naver_brief_chk['uid']){
		sql_query("
			insert into
				naver_ep_brief
			(it_id,create_date)
			values
			('".$it_id."','".$g4['time_ymdhis']."')
		");
		$naver_brief_chk['uid'] = mysql_insert_id();
	}


	# 품절 해제 #
	if($_POST['pum'] == 1){
		sql_query("update ".$g4['yc4_item_table']." set it_stock_qty = '99999' where it_id = '".trim($_POST['it_id'])."'");
        $opk_db->query("update ".$g4['yc4_item_table']." set it_stock_qty = '99999' where it_id = '".trim($_POST['it_id'])."'");
		$flag = 'i';
		$fg = 'N';
		$mode_name = '품절해제';

		# 네이버 brief 테이블 업데이트 2015-02-05 홍민기 #
		sql_query("
			update
				naver_ep_brief
			set
				resume_yn = 'Y',
				pause_yn = null
			where
				uid = '".$naver_brief_chk['uid']."'
		");

	}else{
		sql_query("update ".$g4['yc4_item_table']." set it_stock_qty = '0' where it_id = '".trim($_POST['it_id'])."'");
        $opk_db->query("update ".$g4['yc4_item_table']." set it_stock_qty = '0' where it_id = '".trim($_POST['it_id'])."'");
		$flag = 'o';
		$fg = 'Y';
		$mode_name = '품절처리';

		# 네이버 brief 테이블 업데이트 2015-02-05 홍민기 #
		sql_query("
			update
				naver_ep_brief
			set
				pause_yn = 'Y',
				resume_yn = null
			where
				uid = '".$naver_brief_chk['uid']."'
		");
	}

	sql_query("update yc4_soldout_history set current_fg='N' where it_id='".trim($_POST['it_id'])."'");

	sql_query("
		insert into
			yc4_soldout_history
		(
			it_id,flag,mb_id,time,ip,current_fg
		)values(
			'".trim($_POST['it_id'])."','".$flag."',
			'".$member['mb_id']."','".$g4['time_ymdhis']."','".$_SERVER['REMOTE_ADDR']."','Y'
		)
	");


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

	$soldout_history_fnc($_POST['it_id'],$fg,$member['mb_id']);
	alert($mode_name.'가 완료되었습니다.',$_SERVER['PHP_SELF']."?it_id=".trim($_POST['it_id']));
	exit;
}

$g4[title] = "상품품절 처리";
include_once ("$g4[admin_path]/admin.head.php");




if($_GET['it_id']){
	$_GET['it_id'] = trim($_GET['it_id']);
	$it = sql_fetch("
		select
			it_id,it_maker,it_name,it_stock_qty
		from
			".$g4['yc4_item_table']."
		where
			it_id = '".$_GET['it_id']."'
	");

	$upc_sql = sql_query("select * from ople_mapping where it_id = '".$_GET['it_id']."'");


	$upc = '';
	while($row = sql_fetch_array($upc_sql)){
		$upc .= ($upc ? "<br/>":"") . $row['upc'] . " * " . $row['qty'] ."ea";
	}


}

?>

<form action="<?=$_SERVER['PHP_SELF']?>" method='get'>
	it_id
	<input type="text" name='it_id' value='<?=$it['it_id']?>' />
	<input type="submit" value='검색' />
</form>

<?if($it){?>
<table width='100%'>
<tr>
	<td><?=get_it_image($it['it_id'].'_m', 100, 100, $it['it_id']);?></td>
	<td>
		<table>
			<tr>
				<td align='right'>상품코드</td>
				<td><?=$it['it_id']?></td>
			</tr>
			<tr>
				<td align='right'>UPC</td>
				<td><?=$upc?></td>
			</tr>
			<tr>
				<td align='right'>브랜드명</td>
				<td><?=$it['it_maker']?></td>
			</tr>
			<tr>
				<td align='right'>제품명</td>
				<td><?=get_item_name($it['it_name'])?></td>
			</tr>

		</table>
	</td>
	<td>품절여부</td>
	<td>
	<?php
	echo $it['it_id'];

		if($it['it_stock_qty'] > 0){
			$pum = false;
			echo '판매중';
		}else{
			$pum = true;
			echo '품절';
		}
	?>
	</td>
</tr>
<tr>
	<td colspan='4' align='center'>
	<button class='pum_btn'>
	<?
		if($pum){
			echo "품절해제";
		}else{
			echo "품절처리";
		}
	?>
	</button>
	</td>
</tr>
</table>
<form action="<?=$_SERVER['PHP_SELF']?>" method='post' name='pum_frm'>
	<input type="hidden" name='mode' value='pum'  />
	<input type="hidden" name='it_id' value='<?=$it['it_id']?>'  />
	<input type="hidden" name='pum' value=''  />
</form>
<script type="text/javascript">
$('.pum_btn').click(function(){
	var mode = $(this).text().trim();
	if(!confirm('해당 상품을 '+mode+' 하시겠습니까?')){
		return false;
	}
	if(mode == '품절해제'){
		pum_frm.pum.value=1;
	}else{
		pum_frm.pum.value=2;
	}
	pum_frm.submit();
});
</script>
<?}?>
<? include_once ("$g4[admin_path]/admin.tail.php"); ?>
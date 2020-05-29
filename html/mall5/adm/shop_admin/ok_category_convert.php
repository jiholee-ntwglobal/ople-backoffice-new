<?php 
/*
----------------------------------------------------------------------
file name	 : ok_category_convert.php
comment		 : 오플코리아 카테고리 연동
date		 : 2014-09-11
author		 : rsmaker@ntwglobal.com

----------------------------------------------------------------------
*/

$sub_menu = "400311";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");


$g4[title] = "오플코리아 카테고리 연동";
include_once ("$g4[admin_path]/admin.head.php");

//$_GET['menu_1depth'] = $_GET['menu_1depth'] ? $_GET['menu_1depth'] : '01';


### 오플리뉴얼 1depth 메뉴(관) 시작 로드 ###

$rs = sql_query("select s_id,name from yc4_station order by s_id asc");
while($data = sql_fetch_array($rs)){
	$add_style = $_GET['menu_1depth'] == $data['s_id'] ? 'background-color:#95A3AC;color:white;font-weight:bold' : '';
	$menu_1dpeth_contents .= "<td align='center' style='border:1px solid #95A3AC;height:25px;width:120px;cursor:pointer;$add_style' onclick=\"document.category_frm.menu_1depth.value='$data[s_id]';form_action(1)\">$data[name]</td>";
}

### 오플리뉴얼 1depth 메뉴(관) 시작 끝 ###


if($_GET['menu_1depth']){

	### 오플리뉴얼 2depth 메뉴 로드 시작 ###

	$rs = sql_query("
					select 
						s.ca_id,c.ca_name
					from 
						shop_category s left outer join yc4_category_new c on s.ca_id=c.ca_id and length(c.ca_id)=2 
					where 
						s.s_id='$_GET[menu_1depth]' and not isnull(c.ca_id) order by s.sort asc");
	while($data = sql_fetch_array($rs)){
		$selected = $_GET['menu_2depth'] == $data['ca_id'] ? 'selected' : '';
		$menu_2dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
	}

	if($menu_2dpeth_contents){

		$menu_2dpeth_contents = "
								<select name='menu_2depth' onchange=\"form_action(2)\">
								<option value=''>선택하세요.</option>
								$menu_2dpeth_contents
								</selcted>";
	}

	### 오플리뉴얼 2depth 메뉴(관) 로드 끝 ###



	### 오플리뉴얼 3depth 메뉴(관) 로드 시작 ###

	if($_GET['menu_1depth'] && $_GET['menu_2depth']){

		$cnt_info = sql_fetch_array(sql_query("select count(*) as cnt from yc4_category_new where length(ca_id)=4 and left(ca_id,2)='$_GET[menu_2depth]'"));

		if($cnt_info['cnt'] > 0){

			$rs = sql_query("select ca_id,ca_name from yc4_category_new where length(ca_id)=4 and left(ca_id,2)='$_GET[menu_2depth]' order by ca_name asc");

			while($data = sql_fetch_array($rs)){
				$selected = $_GET['menu_3depth'] == $data['ca_id'] ? 'selected' : '';
				$menu_3dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
			}

			if($menu_3dpeth_contents){

				$menu_3dpeth_contents = "
										<select name='menu_3depth' onchange=\"form_action(3)\">
										<option value=''>선택하세요.</option>
										$menu_3dpeth_contents
										</selcted>";
			}

			if($_GET['menu_1depth'] && $_GET['menu_2depth'] && $_GET['menu_3depth']){

				$cnt_info = sql_fetch_array(sql_query("select count(*) as cnt from yc4_category_new where length(ca_id)=6 and left(ca_id,4)='$_GET[menu_3depth]'"));

				if($cnt_info['cnt'] > 0){

					$rs = sql_query("select ca_id,ca_name from yc4_category_new where length(ca_id)=6 and left(ca_id,4)='$_GET[menu_3depth]' order by ca_name asc");

					while($data = sql_fetch_array($rs)){
						$selected = $_GET['menu_4depth'] == $data['ca_id'] ? 'selected' : '';
						$menu_4dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
					}

					if($menu_4dpeth_contents){

						$menu_4dpeth_contents = "
												<select name='menu_4depth' onchange=\"form_action(4)\">
												<option value=''>선택하세요.</option>
												$menu_4dpeth_contents
												</selcted>";
					}

					if($_GET['menu_4depth']){

						$info_5depth = sql_fetch_array(sql_query("select count(*) as cnt from yc4_category_new where length(ca_id)=8 and left(ca_id,6)='$_GET[menu_4depth]'"));

						if($info_5depth['cnt'] > 0){

							$rs = sql_query("select ca_id,ca_name from yc4_category_new where length(ca_id)=8 and left(ca_id,6)='$_GET[menu_4depth]'  order by ca_name asc");

							while($data = sql_fetch_array($rs)){
								$selected = $_GET['menu_5depth'] == $data['ca_id'] ? 'selected' : '';
								$menu_5dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
							}

							if($menu_5dpeth_contents){

								$menu_5dpeth_contents = "
														<select name='menu_5depth' onchange=\"form_action(5)\">
														<option value=''>선택하세요.</option>
														$menu_5dpeth_contents
														</selcted>";
							}

							if($_GET['menu_5depth']){
								$oplekorea_flag = true;
								$ople_last_code = $_GET['menu_5depth'];
							}
						} else {
							$oplekorea_flag = true;
							$ople_last_code = $_GET['menu_4depth'];
						}
					}

				} else {
					$oplekorea_flag = true;
					$ople_last_code = $_GET['menu_3depth'];
				}
			}
		} else {
			$oplekorea_flag = true;
			$ople_last_code = $_GET['menu_2depth'];
		}

	}


	### 오플리뉴얼 3depth 메뉴(관) 로드 끝 ###


} else {
	$menu_2dpeth_contents = '관을 선택하세요.';
}




### 오플코리아 메뉴 선택 영역 처리 시작 ###

if($oplekorea_flag){

	$rs = sql_query("select ca_id,ca_name from oplekorea.yc4_category_new where length(ca_id)=2 order by ca_name asc ");

	while($data = sql_fetch_array($rs)){
		$selected = $_GET['ok_menu_1depth'] == $data['ca_id'] ? 'selected' : '';
		$ok_menu_1dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
	}

	if($_GET['ok_menu_1depth']){

		$rs = sql_query("select ca_id,ca_name from oplekorea.yc4_category_new where length(ca_id)=4 and left(ca_id,2)='$_GET[ok_menu_1depth]' order by ca_name asc ");

		while($data = sql_fetch_array($rs)){
			$selected = $_GET['ok_menu_2depth'] == $data['ca_id'] ? 'selected' : '';
			$ok_menu_2dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
		}

		if($ok_menu_2dpeth_contents){

			$ok_menu_2dpeth_contents = "
									<select name='ok_menu_2depth' onchange=\"form_action(7)\">
									<option value=''>선택하세요.</option>
									$ok_menu_2dpeth_contents
									</selcted>";

		}

		if($_GET['ok_menu_2depth']){

			$cnt_info = sql_fetch_array(sql_query("select count(*) as cnt from oplekorea.yc4_category_new where length(ca_id)=6 and left(ca_id,4)='$_GET[ok_menu_2depth]'"));

			if($cnt_info['cnt'] > 0){

				$rs = sql_query("select ca_id,ca_name from oplekorea.yc4_category_new where length(ca_id)=6 and left(ca_id,4)='$_GET[ok_menu_2depth]' order by ca_name asc ");

				while($data = sql_fetch_array($rs)){
					$selected = $_GET['ok_menu_3depth'] == $data['ca_id'] ? 'selected' : '';
					$ok_menu_3dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
				}

				if($ok_menu_3dpeth_contents){

					$ok_menu_3dpeth_contents = "
											<select name='ok_menu_3depth' onchange=\"form_action(8)\">
											<option value=''>선택하세요.</option>
											$ok_menu_3dpeth_contents
											</selcted>";

				}

				if($_GET['ok_menu_3depth']){

					$cnt_info = sql_fetch_array(sql_query("select count(*) as cnt from oplekorea.yc4_category_new where length(ca_id)=8 and left(ca_id,6)='$_GET[ok_menu_3depth]'"));

					if($cnt_info['cnt'] > 0){

						$rs = sql_query("select ca_id,ca_name from oplekorea.yc4_category_new where length(ca_id)=8 and left(ca_id,6)='$_GET[ok_menu_3depth]' order by ca_name asc ");

						while($data = sql_fetch_array($rs)){
							$selected = $_GET['ok_menu_4depth'] == $data['ca_id'] ? 'selected' : '';
							$ok_menu_4dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
						}

						if($ok_menu_4dpeth_contents){

							$ok_menu_4dpeth_contents = "
													<select name='ok_menu_4depth' onchange=\"form_action(9)\">
													<option value=''>선택하세요.</option>
													$ok_menu_4dpeth_contents
													</selcted>";

						}

						if($_GET['ok_menu_4depth']){

							$cnt_info = sql_fetch_array(sql_query("select count(*) as cnt from oplekorea.yc4_category_new where length(ca_id)=10 and left(ca_id,8)='$_GET[ok_menu_4depth]'"));

							if($cnt_info['cnt'] > 0){

								$rs = sql_query("select ca_id,ca_name from oplekorea.yc4_category_new where length(ca_id)=10 and left(ca_id,8)='$_GET[ok_menu_4depth]' order by ca_name asc ");

								while($data = sql_fetch_array($rs)){
									$selected = $_GET['ok_menu_5depth'] == $data['ca_id'] ? 'selected' : '';
									$ok_menu_5dpeth_contents .= "<option value='$data[ca_id]' $selected>$data[ca_name]</option>";
								}

								if($ok_menu_5dpeth_contents){

									$ok_menu_5dpeth_contents = "
															<select name='ok_menu_5depth' onchange=\"form_action(10)\">
															<option value=''>선택하세요.</option>
															$ok_menu_5dpeth_contents
															</selcted>";

								}

								if($_GET['ok_menu_5depth']){
									$save_button_flag = true;
									$oplekorea_last_code = $_GET['ok_menu_5depth'];
								}
							} else {
								$save_button_flag = true;
								$oplekorea_last_code = $_GET['ok_menu_4depth'];
							}

						}
					} else {
						$save_button_flag = true;
						$oplekorea_last_code = $_GET['ok_menu_3depth'];
					}

				}
			} else {
				$save_button_flag = true;
				$oplekorea_last_code = $_GET['ok_menu_2depth'];
			}
		}
	}
	
}

### 오플코리아 메뉴 선택 영역 처리 끝 ###



?>
<?=subtitle($g4[title])?>
<form name='category_frm' method="POST" action="ok_category_covert_save.php">
<div style="background-color:#FAECC5">
<span style="font-weight:bold">오플 리뉴얼 카테고리 선택</span>
<input type="hidden" name="menu_1depth" value="<?php echo $_GET['menu_1depth']; ?>"/>
<input type="hidden" name="ople_last_code" value="<?php echo $ople_last_code; ?>"/>
<table width='100%'>
	<tr>
		<td align='left' colspan='5'>관 선택</td>
	</tr>
	<tr>
		<?php echo $menu_1dpeth_contents; ?>
	</tr>
</table>
<table width='100%'>
	<tr>
		<td align="center" height="30" width='20%'><b>1depth Menu</b></td>
		<td align="center" width='30%'><?php echo $menu_2dpeth_contents; ?></td>	
		<td align="center" width='20%'><b>2depth Menu</b></td>
		<td align="center" width='30%'><?php echo $menu_3dpeth_contents; ?></td>		
	</tr>
	<tr>
		<td align="center"><b>3depth Menu</b></td>
		<td align="center"><?php echo $menu_4dpeth_contents; ?></td>
		<td align="center"><b>4depth Menu</b></td>
		<td align="center"><?php echo $menu_5dpeth_contents; ?></td>
	</tr>
</table>
</div>
<?php if($oplekorea_flag){ ?>
<br><br>
<input type="hidden" name="oplekorea_last_code" value="<?php echo $oplekorea_last_code; ?>"/>
<span style="font-weight:bold">오플코리아 카테고리 선택</span>
<table width='100%'>
	<tr>
		<td>1depth</td>
		<td>2depth</td>
		<td>3depth</td>
		<td>4depth</td>
		<td>5depth</td>
	</tr>
	<tr>
		<td>
		<select name='ok_menu_1depth' onchange="form_action(6)">
		<option value=''>선택하세요.</option>
		<?php echo $ok_menu_1dpeth_contents; ?>
		</select></td>
		<td><?php echo $ok_menu_2dpeth_contents; ?></td>
		<td><?php echo $ok_menu_3dpeth_contents; ?></td>
		<td><?php echo $ok_menu_4dpeth_contents; ?></td>
		<td><?php echo $ok_menu_5dpeth_contents; ?></td>
	</tr>
</table>
<?php } ?>
<?php if($save_button_flag){ 

	$item_info = sql_fetch_array(sql_query("select count(*) as cnt from oplekorea.yc4_category_item where ca_id='$oplekorea_last_code'"));


?>

<p align="center"><br><input type="button" value="<?php echo $item_info['cnt']; ?>개 아이템 오플 리뉴얼 카테고리로 복사" onclick="goSave()"></p>
<?php } ?>
</form>
<script>
function form_action(num){
	var url = "ok_category_convert.php?";
	for(var k=1;k<=num;k++){
		if(k<6){
			if(typeof(eval("document.category_frm.menu_" + k + "depth")) != "undefined"){
				url += "menu_" + k + "depth=" + eval("document.category_frm.menu_" + k + "depth.value") + "&";
			}
		} else {
			var el_num = parseInt(k,10)-parseInt(5,10);
			if(typeof(eval("document.category_frm.ok_menu_" + el_num + "depth")) != "undefined"){
				url += "ok_menu_" + el_num + "depth=" + eval("document.category_frm.ok_menu_" + el_num + "depth.value") + "&";
				
			}
		}
		
	}
	
	location.href = url;
}

function goSave(){
	if(confirm("오플 리뉴얼 카테고리로 복사하시겠습니끼??")){
		document.category_frm.submit();
	}
}
</script>
<? include_once ("$g4[admin_path]/admin.tail.php"); ?>
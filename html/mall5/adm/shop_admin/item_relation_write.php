<?php
$sub_menu = "300377";
include_once("./_common.php");
include $g4['full_path'].'/lib/db.php';
$g4[title] = "관련상품";
include_once ("$g4[admin_path]/admin.head.php");
include "./item_relation_function.php";

$pdo		= new db();
$ople		= $pdo->ople_db_pdo;
$ntics		= $pdo->ntics_db;

$main_id	= isset($_GET['main_id']) ? trim($_GET['main_id']) : "";
$ajax_data	= "mode=ajax";
$rel_data	= array();
$main_info	= array();
$rel_list	= "";

if($main_id != ""){
	if(preg_match("/([^0-9\.])/",$main_id)){
		echo "
			<script type=text/JavaScript>
				alert('유효하지않은 상품코드입니다.')
				history.back()
			</script>
		";
		exit;
	}

// 메인상품 정보
	$sql_main	= $ople->prepare('SELECT i.it_id, i.it_name, i.it_maker, i.it_amount_usd, i.it_amount, m.upc FROM yc4_item i LEFT JOIN ople_mapping m ON m.it_id=i.it_id WHERE i.it_id= :it_id ');
	$sql_main	->bindParam(':it_id',$main_id);
	$sql_main	->execute();
	
	$main_info	= $sql_main->fetch();
	if(!$main_info['it_id']){
		echo "
			<script type=text/JavaScript>
				alert('올바르지 않은 상품코드입니다.')
				history.back()
			</script>
		";
		exit;
	}
	$main_upc	= array($main_info['upc']);
	$main_ntics	= ntics_data($main_upc,$ntics);
	$main_info['wholesale_price']	= $main_ntics[$main_info['upc']]['wholesale_price'] ? $main_ntics[$main_info['upc']]['wholesale_price'] : $main_ntics[$main_info['upc']]['exept_explain'];
	$main_info['currentqty']		= $main_ntics[$main_info['upc']]['currentqty'];
	$ajax_data	.= "&main_id=".$main_id;
// 관련상품 목록
	$sql_rel	= $ople->prepare("
					SELECT
						i.it_id, i.it_name, i.it_maker, i.it_amount_usd, i.it_amount, i.ps_cnt, m.upc
					,	(CASE WHEN m.ople_type = 's' THEN 'set' ELSE '' END) AS 'set_fg'
					,	(CASE WHEN c.it_id IS NULL AND i.it_amount = '99999' THEN '가등록' ELSE '' END ) AS 'regi_fg'
					FROM
						yc4_item i
					LEFT JOIN
						yc4_category_item c ON i.it_id=c.it_id
					LEFT JOIN
						ople_mapping m ON i.it_id=m.it_id
					LEFT JOIN
						yc4_item_relation r ON i.it_id=r.it_id2
					WHERE
						r.it_id= :it_id
					GROUP BY i.it_id");
//i.it_use=1 AND i.it_discontinued=0 AND 
	$sql_rel	->bindParam(':it_id',$main_id);
	$sql_rel	->execute();
	
	$rel_info	= $sql_rel->fetchAll(PDO::FETCH_ASSOC);
	if($rel_info){
		$upc_in		= array();
		foreach($rel_info as $val){
			array_push($upc_in,$val['upc']);
		}
		$rel_ntics	= ntics_data($upc_in,$ntics);
		foreach($rel_info as $val){
			$op_amount	= $val['it_amount_usd']=='' ? number_format($val['it_amount']/$default['de_conv_pay'],2) : $val['it_amount_usd'];
			$ws_amount	= $rel_ntics[$val['upc']]['wholesale_price'] ? $rel_ntics[$val['upc']]['wholesale_price'] : $rel_ntics[$val['upc']]['exept_explain'];
			$rel_list	.= "
                <tr>
					<input type='hidden' name='it_id' value='".$val['it_id']."' />
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
						".$rel_ntics[$val['upc']]['currentqty']."
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
	}
}

?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<style>
    .modal-lg {
    width: 1500px;
  	}
</style>
<div class='row'>
	<div class='col-lg-12'>
		<div class='row text-center'>
			<div class='col-lg-8 col-lg-offset-2'>
				<form class='form-inline' name='main_load' method='GET' action='item_relation_write.php'>
					<input class='form-control' type='text' name='main_id' value='<?php echo $main_id; ?>' placeholder="상품코드" />
					<button class='btn btn-success' type='submit'>메인상품 불러오기</button>
					<button class='btn btn-success' type='button' onclick='location.href="./item_relation_list.php"'>목록</button>
				</form>
			</div>
		</div>


		<div class='row'>
			<div class='col-lg-6'>
				<table class='table'>
					<tr>
						<td rowspan="4">
							<?php echo isset($main_info['it_id']) ? "<img id='help_btn' src='http://115.68.20.84/item/".$main_info['it_id']."_s' width='120px' hight='120px' />" : "이미지"; ?>
						</td>
						<td>
							<?php echo isset($main_info['it_name']) ? get_item_name($main_info['it_name'],'detail') : "상품명"; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo isset($main_info['it_maker']) ? $main_info['it_maker'] : "브랜드"; ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo isset($main_info['it_amount_usd']) ? $main_info['it_amount_usd'] : number_format($val['it_amount']/$default['de_conv_pay'],2); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo isset($main_info['wholesale_price']) ? $main_info['wholesale_price'] : "입고가"; ?>
						</td>
					</tr>
				</table>
			</div>

			<div class='col-lg-6'>
				<?php foreach(cate_id($main_id,$ople) as $val){ ?>
					<input type='checkbox' name='chk_cate1[]' value='<?php echo $val['ca_id']; ?>' /><?php echo cate_name("",$val['ca_id'],$ople); ?> <br />
				<?php } ?>
				<button class='btn btn-info' onclick='cate_src("ca","","t_all")'>선택한카테고리상품보기</button>
			</div>
		</div>
	</div>
</div>

<div class='row'>
	<div class='col-lg-12'>
<!--
<div class="btn-group">
  <button class="btn btn-primary">Checked option</button>
  <button data-toggle="dropdown" class="btn btn-primary dropdown-toggle" data-placeholder="false"><span class="caret"></span></button>
    <ul class="dropdown-menu">
      <li><input type="checkbox" id="ID" name="NAME" value="VALUE"><label for="ID">OPTIN1</label></li>
      <li><input type="checkbox" id="ID" name="NAME" value="VALUE"><label for="ID">OPTIN2</label></li>
      <li><input type="checkbox" id="ID" name="NAME" value="VALUE"><label for="ID">OPTIN3</label></li>
      <li><input type="checkbox" id="ID" name="NAME" value="VALUE"><label for="ID">OPTIN4</label></li>
    </ul>
</div>
-->

<ul class="btn-group cate-map">
	<li class="btn dropdown"> <a href="#" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
		<ul class="dropdown-menu">
			<li class="dropdown-submenu"><a href="#" data-toggle="dropdown">1</a>
				<ul class="dropdown-menu">
					<li><a href="#">1-1</a></li>
					<li class="dropdown-submenu"><a href="#" data-toggle="dropdown">1-2</a>
						<ul class="dropdown-menu">
							<li>1-2-1</li>
							<li>1-2-2</li>
						</ul>
					</li>
					<li>1-3</li>
				</ul>
			</li>
			<li class="dropdown-submenu"><a href="#" data-toggle="dropdown">2</a>
				<ul class="dropdown-menu">
					<li>2-1</li>
					<li>2-2</li>
					<li>2-3</li>
				</ul>
			</li>
			<li>3</li>
			<li class="divider"></li>
			<li>4</li>
		</ul>
	</li>
</ul>
<script type="text/javascript">
$('.cate-map > li').mouseover(function(){
	$('.dropdown-submenu > ul').hide();
	$(this).find('ul').show();
//	$(this).perent
//	$('.all_category_wrap > li').width(370);
//	$(this).find(' > a').addClass('active');
});
</script>
    	
		<form name='rel_update' method='POST' action='item_relation_update.php'>
		<input type='hidden' name='main_id' value='<?php echo $main_id; ?>' />
		<button class='btn btn-primary' onclick='edit_row_chk("remove")'>선택상품 지우기</button>
		<button type='submit' class='btn btn-primary pull-right'>적용</button>
		<table class='table table-striped' style='font-size:90%;' name='tbl_rel'>
			<tr>
				<td width='10px'></td>
				<td width='50px'>이미지</td>
				<td width='90px'>it_id<br />upc</td>
				<td width='100px'>브랜드</td>
				<td width='100px'>상품명</td>
				<td width='80px'>입고가<br />(판매가)</td>
				<td width='80px'>재고</td>
				<td width='80px'>후기수</td>
				<td width='80px'>세트</td>
				<td width='80px'>가등록</td>
				<td width='50px'></td>
			</tr>
	<?php if($rel_list!=""){
		echo $rel_list;
	}else{ ?>
			<tr>
				<td colspan='8' align='center'>
					등록된 관련상품이 없습니다.
				</td>
			</tr>
	<?php }?>
		</table>
	
		<button class='btn btn-primary' onclick='edit_row_chk("remove")'>선택상품 지우기</button>
		<button type='submit' class='btn btn-primary pull-right'>적용</button>
		</form>
	</div>
</div>


<button id="btn_layer" data-toggle="modal" data-target="#myModal" style="display:none;"></button>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">상품검색</h4>
            </div>
            <div class="modal-body">
				<div class='row'>
					<div class='col-lg-4'>
						<form name='src_form' method='GET' action=''>
							<input type='hidden' name='li_tab' value='' />
							<div class='form-group'>
								<div class='row'>
									<div class='col-lg-5'>
										<select class='form-control' name='src_name'>
											<option disabled selected>검색영역</option>
											<option value='it_id'>IT_ID</option>
											<option value='upc'>UPC</option>
											<option value='it_name'>상품명</option>
											<option value='it_maker'>브랜드</option>
										</select>
									</div>
									<div class='col-lg-7'>
										<button class='btn btn-primary' type='button' onclick='cate_src("src","1","t_all")'>검색</button>
										<button class='btn btn-primary' type='button' onclick='reset_src()'>초기화</button>
										<button class='btn btn-primary' type='button' onclick='bran_src()'>같은브랜드보기</button>
									</div>
								</div>	
								<div class='row'>
									<div class='col-lg-12'>
										<input class='form-control' type='text' name='src_val' value='' placeholder='search for...' />
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class='col-lg-5 col-lg-offset-1'>
						<button class='btn btn-primary' type='button' onclick='cate_src("ca","1","t_all")'>선택한카테고리상품보기</button><br />
						<?php foreach(cate_id($main_id,$ople) as $val){ ?>
						<input type='checkbox' name='chk_cate2[]' value='<?php echo $val['ca_id']; ?>' /><?php echo cate_name("",$val['ca_id'],$ople); ?> <br />
						<?php } ?>
					</div>
					
				</div>
				<div class='row'>
					<div class="col-lg-12 text-right">
						<ul class="nav nav-tabs">
						  <li role="presentation" id="t_all" class='active'><a href="#" onclick='cate_src("","1","t_all")'>전체상품</a></li>
						  <li role="presentation" id="t_rel" class=''><a href="#" onclick='cate_src("","1","t_rel")'>관련상품 등록된상품</a></li>
						  <li role="presentation" id="t_rel_no" class=''><a href="#" onclick='cate_src("","1","t_rel_no")'>관련상품 미등록된 상품</a></li>
						</ul>
					</div>
				</div>
            </div>
            <div id="modal_contents" class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<div style="position:absolute; z-index:1; left:0; top:0;">
<div id="test" style="display:none; width:500px; height:900px; position:relative; z-index:1; left:300; top:300;">
<img src='http://115.68.20.84/item/1351295919_m' width='500px' hight='900px' />
</div>
</div>

<script type="text/javascript">
	var	it_id_list	= [];

$("#help_btn").hover(
  function () {
			if($("#test").css('display')=='none'){
			$("#test").css("display", "block");
			}
			$(this).mousemove(function(e){
				$('#test').css('left',e.pageX+'px');
				$('#test').css('top',e.pageY+'px');
			});
		},
  function () {
			$("#test").css("display", "none");
  });


	$(document).ready(function(){
		$('table[name=tbl_rel] input[type=checkbox]').each(function(){
			it_id_list.push($(this).val());
		});
		
		$('input[name^=chk_cate]').change(function() {
			var chk = $(this).is(":checked");
			var val	= $(this).val();
			if(chk)	$('input[value='+val+']').attr('checked', true);
			else	$('input[value='+val+']').attr('checked', false);		
		});
	});

	$('form[name=main_load]').submit(function(){
		var it_id = $('input[name=main_id]').val();
		if(it_id.length<8 || it_id.length>12){
			alert('상품코드 길이가 유효하지 않습니다.');
			return false;
		}
		if(!$.isNumeric(it_id)){
			alert('상품코드는 숫자만 사용할수 있습니다.');
			return false;
		}
		return true;
	});		
	
	function reset_src(){
		$('form[name=src_form] option:first').attr('selected',true);
		$('form[name=src_form] input[name=src_val]').val('');
		$('input[type=checkbox]:checked').each(function(){
			$(this).removeAttr('checked');
		});
	}
	
	function edit_row_chk(act){
		if(act=='remove'){
			if($('table[name=tbl_rel] input[type=checkbox]:checked').length<1){
				alert('하나이상의 상품을 선택하여 주세요');
				return true;
			}else{
				$('table[name=tbl_rel] input[type=checkbox]:checked').each(function(){
					it_id_list.splice($.inArray($(this).val(),it_id_list),1);
					$(this).parents('tr:first').remove();
				});
			}
		}else if(act=='add'){
			if($('#modal_contents input[type=checkbox]:checked').length<1){
				alert('하나이상의 상품을 선택하여 주세요');
				return false;
			}else{
				var	except	= 0;
				var	affect	= 0;
				$('#modal_contents input[type=checkbox]:checked').each(function(){
					if($.inArray($(this).val(),it_id_list)>=0){
						except++;
						return true;
					}else{
						affect++;
						$('table[name=tbl_rel]').append($(this).parents('tr:first').clone(true));
						it_id_list.push($(this).val());
					}
				});
				if(affect>0){
					// 버튼이름변경, 체크박스 해제
					$('table[name=tbl_rel] input[type=checkbox]').each(function(){
						$(this).removeAttr('checked');
					});
					$('table[name=tbl_rel] button').each(function(){
						$(this).attr("onclick","edit_row('remove',this)");
						$(this).text('삭제')
					});
					if(except>0){
						alert('이미 리스트에있는 '+except+'개 상품을 제외하고 추가하였습니다.');
					}else{
						alert('리스트에 상품을 추가하였습니다.');
					}
				}else{
					alert('이미 리스트에 있는 상품들입니다.');
				}
			}
		}
		$('#src_res>tbody>tr').each(function(){
			if($.inArray($(this).contents('td:first').contents('input[type=checkbox]').val(),it_id_list)>=0){
				$(this).attr('class','info');
			}
		
		});
		return false;
	}

	function edit_row(act, btn){
		var	trow	= $(btn).parents('tr:first');
		var	it_id	= $(btn).parent().parent().contents('td:first').contents('input').val()
		if(act=='remove'){
			it_id_list.splice($.inArray(it_id,it_id_list),1);
			trow.remove();
		}
		if(act=='add'){
			var	chk	= 0;
			$('table[name=tbl_rel] input[type=checkbox]').each(function(){
				if($(this).val()==it_id){
					alert('이미 리스트에 있는 상품입니다.');
					chk++;
				}
			})
			if(chk==0){
				$('table[name=tbl_rel]').append(trow.clone(true));
				it_id_list.push(it_id);
			}
			$('table[name=tbl_rel] button').each(function(){
				$(this).attr("onclick","edit_row('remove',this)");
				$(this).text('삭제')
			});
		}
		$('#src_res>tbody>tr').each(function(){
			if($.inArray($(this).contents('td:first').contents('input[type=checkbox]').val(),it_id_list)>=0){
				$(this).attr('class','info');
			}
		
		});
		
	}

	$('form[name=rel_update]').submit(function(){
		var	main_id	= '<?php echo $main_id; ?>';
		if(it_id_list.length<1){
			if(confirm('관련상품이 없습니다. 이대로 진행하시겠습니까?')){
				return true;
			}else{
				return false;
			}
		}else{
			if(confirm('리스트에 있는 관련상품을 적용하시겠습니까?')){
				$('table[name=tbl_rel] input[type=checkbox]').each(function(){
					$(this).attr('checked',true);
				});
				return true;
			}else{415521
				return false;
			}
		}
	});
	
	function bran_src(){
		$('form[name=src_form] option[value=it_maker]').attr('selected',true);
		$('form[name=src_form] input[name=src_val]').val('<?php echo $main_info['it_maker']; ?>');
		cate_src('src','1','t_all');
	
	}
	
	
	function cate_src(func,page,li_tab){
		if(!page){
			var data	= "<?php echo $ajax_data; ?>";
		}else{
			var data	= "<?php echo $ajax_data; ?>&page="+page;
		}
		
		if(func == "ca"){
			var ca_id	= [];
			$('input[name^=chk_cate]:checked').each(function(){
				ca_id.push($(this).val());
			});
			if(ca_id.length < 1){
				alert('하나이상의 카테고리를 선택해주세요');
				return false;
			}
			data	= data + "&ca_id=" + ca_id + "&func=ca";
			$('form[name=src_form] option:first').attr('selected',true);
			$('form[name=src_form] input[name=src_val]').val('');
		}
		if(func == "src"){
			var src_name	= $('form[name=src_form] option:selected').val();
			var src_value	= $('form[name=src_form] input[name=src_val]').val();
			if(src_value.length<1){
				alert('검색어를 입력해 주세요');
				return false;
			}
			if(src_name=="it_id"){
				if(!$.isNumeric(src_value)){
					alert('상품코드는 숫자만 사용할수 있습니다.');
					return false;
				}
			}
			if(src_name=="upc"){
				
			}
			if(src_name=="it_name"){
				
			}
			if(src_name=="it_maker"){
				
			}
			data	= data + "&src_name=" + src_name + "&src_value=" + src_value + "&func=src";
			
			$('input[name^=chk_cate]:checked').each(function(){
				$(this).attr('checked', false);
			});
		}
		data	= data + "&li_tab=" + li_tab;
		$.ajax({
			type		: "POST"
		,	url			: "./item_relation_ajax.php"
		,	dataType	: "text"
		,	data		: data
		,	error		: function(rtn) {
								alert("정보전송에 실패하였습니다.");
						}
		,	success		: function(rtn) {
					        $("#modal_contents").empty();
					        $("#modal_contents").parent().find('h4.modal-title').text('검색결과');
					        $("#modal_contents").html(rtn);
				        	if(!page){
				        		$("#btn_layer").click();
				        	}
							$('#src_res>tbody>tr').each(function(){
								if($.inArray($(this).contents('td:first').contents('input[type=checkbox]').val(),it_id_list)>=0){
									$(this).attr('class','info');
								}
							});
						}
		});
		$('li[id]').each(function() {
			$(this).attr('class','');
			$(this).contents('a:first').attr('onclick','cate_src("'+func+'","1","'+this.id+'")');
		});
		$('li[id$='+li_tab+']').attr('class','active');
		
	}
</script>

<?php
//	$('#tble > tbody:last').append($('#tble tr:first').clone(true));
//	$('form[name=src_form]').submit(function(){
//		var src_name	= $('form[name=src_form] option:selected').val();
//		var src_value	= $('form[name=src_form] input[name=src_val]').val();
//		
//
//	});

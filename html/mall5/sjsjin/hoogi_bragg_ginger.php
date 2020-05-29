<?php
include "_common.php";


# 관리자 체크 #
$adm_chk_sql = sql_query("select count(*) as cnt from ".$g4['auth_table']." where mb_id = '".$member['mb_id']."'");
$adm_chk = 0;
$adm_chk = (int)$adm_chk_sql['cnt'];
if(is_admin($member['mb_id'])){
	$adm_chk ++;
}


// 테이블의 전체 레코드수만 얻음
$sql = "
	select 
	count(*) as cnt
	from 
	yc4_item_ps a,
	yc4_item b
	where a.it_id = b.it_id
	and b.it_maker in ('Bragg','Ginger People')
	and date_format(a.is_time,'%Y%m%d') >= '20150407'
";
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = 10; //$config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함



# 후기 리스트 로드 #
$sql = sql_query("
	select 
	a.* 
	from 
	yc4_item_ps a,
	yc4_item b
	where a.it_id = b.it_id
	and b.it_maker in ('Bragg','Ginger People')
	and date_format(a.is_time,'%Y%m%d') >= '20150407'
	order by is_best desc,is_time desc
	limit $from_record, $rows 
");
$list_tr = "";
while($row = sql_fetch_array($sql)){
	$star = get_star($row[is_score]);

	$is_name = stripslashes($row['is_name']);
	$is_content = conv_content($row[is_content], 0);
	$row['is_subject'] = stripslashes($row['is_subject']);

	$img_str = "";
	for($a=0; $a<5; $a++){
		if($row["is_image{$a}"] != '' && file_exists("{$g4['path']}/data/ituse/".$row["is_image{$a}"]))
			$img_str .= "<img src='{$g4['path']}/data/ituse/".$row["is_image{$a}"]."' border=0  style='max-width:900px;'/><br/><br/>";
	}

	// 회원처리
	if($is_auth || $adm_chk) {
		$is_name .= "({$row['mb_id']})";
	}elseif($row['mb_id']) {
		if($row['mb_id'] == $member['mb_id']){
			$is_name = "<strong>".$row['mb_id']."</strong>";
		}else{
			$is_name = substr($row['mb_id'],0,-3)."***";
		}
	}


	$list_tr .= "
		<tr onclick=\"hoogi_contents_view('".$row['is_id']."');\">
			<td><img src=\"{$g4[shop_img_path]}/star{$star}.gif\"></td>
			<td class='hoogi_list_subject'><a href='#' onclick='return false;'>".$row['is_subject']."</a></td>
			<td>".$is_name."</td>
			<td>".date('y-m-d H:i',strtotime($row['is_time']))."</td>
		</tr>
		<tr class='hoogi_list_more' is_id='".$row['is_id']."'>
			<td colspan='4' style='padding-left:20px;'>".$is_content."<br/>".$img_str."</td>
		</tr>
	";
}

if(!$list_tr){
	$list_tr = "
		<tr>
			<td colspan='4'>후기가 존재하지 않습니다. 첫 후기의 주인공이 되어 보세요</td>
		</tr>
	";
}


$qstr = $_GET;
unset($qstr['page']);
$qstr = http_build_query($qstr);
$page_btn = "<div class='paging'>".get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=")."</div>";



# 작성 가능한 상품 리스트 로드 #
$sql = sql_query("
	select 
		distinct a.it_id,b.it_maker,b.it_name
	from 
		".$g4['yc4_cart_table']." a,
		".$g4['yc4_item_table']." b,
		".$g4['yc4_order_table']." c
	where 
		a.it_id = b.it_id
		and
		a.on_uid = c.on_uid
		and
		a.ct_mb_id = '".$member['mb_id']."'
		and
		a.ct_status = '완료'
		and b.it_maker in ('Bragg','Ginger People')
		and date_format(c.od_time,'%Y%m%d') >= '20150407'
");

if(in_array($member['mb_id'],array('beeby','ghdalsrldi'))){
    $sql = sql_query("
        select it_id,it_maker,it_name from yc4_item where it_use = 1 and it_maker in ('Bragg','Ginger People')
    ");
}


$it_option = "";
while($row = sql_fetch_array($sql)){
	$it_option .= "<option value='".$row['it_id']."'>[".$row['it_maker']."]".get_item_name($row['it_name'])."</option>";
}


include_once "_head.php";
?>

<style type="text/css">
.hoogi_list_more{
	display:none;
}
.event_hoogi_write{
	display:none;
}
</style>

					<div style="width:1030px;">
                        <img src="http://115.68.20.84/event/bragg&ginger_hoogi_top.jpg">
                    </div>
                    <div class="event_hoogi">
                        <table class="hoogi_list" cellpadding="0" cellspacing="0">
                            <colgroup>
                                <col width="105" />
                                <col width="750" />
                                <col width="72" />
                                <col width="105" />
                            </colgroup>
							<?php echo $list_tr;?>
                            <?/*
							<tr>
                                <td><img src="http://ople.com/mall5/shop/img/star5.gif" /></td>
                                <td class="hoogi_list_subject"><a href="">어린이집 선생님 선물용</a></td>
                                <td>글쓴이</td>
                                <td>15-04-06 10:00</td>
                            </tr>
                            <tr class="hoogi_list_more">
                                <td colspan="4" style="padding-left:20px;">
                                    <span>요거 참 맛난데요. 가격은 좀 사악해요ㅋ 후기임 후기후기<br />요거 참 맛난데요. 가격은 좀 사악해요ㅋ 후기임 후기후기</span>
                                    <img src="http://ople.com/mall5/data/ituse/1391702938_0_43d30d32c746f0e526ab36770e1c7ae0.JPG">
                                </td>
                            </tr>
                            <tr>
                                <td><img src="http://ople.com/mall5/shop/img/star5.gif" /></td>
                                <td class="hoogi_list_subject"><a href="">어린이집 선생님 선물용</a></td>
                                <td>글쓴이</td>
                                <td>15-04-06 10:00</td>
                            </tr>
                            <tr style="display:none;" class="hoogi_list_more">
                                <td colspan="4" style="padding-left:20px;">
                                    <span>요거 참 맛난데요. 가격은 좀 사악해요ㅋ 후기임 후기후기<br />요거 참 맛난데요. 가격은 좀 사악해요ㅋ 후기임 후기후기</span>
                                    <img src="http://ople.com/mall5/data/ituse/1391702938_0_43d30d32c746f0e526ab36770e1c7ae0.JPG">
                                </td>
                            </tr>
							*/?>
                        </table>
                        
						<div class="hoogi_bt"><a href="#" onclick="hoogi_write(); return false;"><img src="<?php echo $g4['shop_path'];?>/img/btn_story.gif"></a></div>
						<?php if($it_option){?>
                        <div class="event_hoogi_write">
							<form action="<?php echo $g4['shop_path'];?>/itemuseupdate.php" method='post'>
								<table cellpadding="0" cellspacing="0">
									<colgroup>
										<col width="137" />
										<col width="893" />
									</colgroup>
									<tr>
										<td>구매상품 정보</td>
										<td>
											<select name="it_id" id="event_hoogi_product" style="width:97%;"><?php echo $it_option;?></select>
										</td>
									</tr>
									<tr>
										<td>제목</td>
										<td><input type="text" name="is_subject" style="width: 97%; background-image: url(<?php echo $g4['path'];?>/js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;" class="ed" required="" itemname="제목"></td>
									</tr>
									<tr>
										<td>내용</td>
										<td><textarea name="is_content" rows="7" style="width: 97%; background-image: url(<?php echo $g4['path'];?>/js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;" class="ed" required="" itemname="내용"></textarea></td>
									</tr>
									<tr>
										<td>평가</td>
										<td>
											<input type="radio" name="is_score" value="10" checked=""><img src="<?php echo $g4['shop_path'];?>/img/star5.gif" align="absmiddle">
											<input type="radio" name="is_score" value="8"><img src="<?php echo $g4['shop_path'];?>/img/star4.gif" align="absmiddle">
											<input type="radio" name="is_score" value="6"><img src="<?php echo $g4['shop_path'];?>/img/star3.gif" align="absmiddle">
											<input type="radio" name="is_score" value="4"><img src="<?php echo $g4['shop_path'];?>/img/star2.gif" align="absmiddle">
											<input type="radio" name="is_score" value="2"><img src="<?php echo $g4['shop_path'];?>/img/star1.gif" align="absmiddle">
										</td>
									</tr>
									<tr>
										<td>SNS URL</td>
										<td><input type="text" name='is_sns_url' class='ed' style='width: 97%; background-image: url(<?php echo $g4['path'];?>/js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;' /></td>
									</tr>
									<tr>
										<td>첨부이미지</td>
										<td><input type="file" name="is_image[0]" size="60"><input type="file" name="is_image[0]" size="60"><input type="file" name="is_image[0]" size="60"><input type="file" name="is_image[0]" size="60"><input type="file" name="is_image[0]" size="60"></td>
									</tr>
									<tr>
										<td><img id="kcaptcha_image_use" title="글자가 잘 안보이시는 경우 클릭하시면 새로운 글자가 나옵니다." width="137" height="60" style="cursor: pointer;"></td>
										<td style="background-color:#f7f7f7;"><input type="text" name="is_key" class="ed" required="" itemname="자동등록방지용 코드" style="background-image: url(<?php echo $g4['path'];?>/js/wrest.gif); background-position: 100% 0%; background-repeat: no-repeat;">                                         &nbsp;* 왼쪽의 자동등록방지 코드를 입력하세요.</td>
									</tr>
								</table>
								<div class="hoogi_bt"><a href=""><img src="<?php echo $g4['shop_path'];?>/img/btn_confirm.gif"></a></div>
							</div>
						</form>
                        

                    </div>
					<?php }?>
					<?php echo $page_btn;?>

<script type="text/javascript">
function hoogi_write(){
	<?php if($it_option){?>
	
	var wrap = $('.event_hoogi_write');
	if(wrap.is(':visible') == true){
		wrap.hide();
	}else{
		wrap.show();
	}
	<?php }else{?>
	alert('구매 내역중 이벤트 상품이 존재하지 않습니다.');
	return false;
	<?php }?>
}

function hoogi_contents_view(is_id){
	var content_tr = $('.hoogi_list_more[is_id='+is_id+']');
	var hide_fg = true;
	if(content_tr.is(':visible') == false){
		hide_fg = false;
	}
	$('.hoogi_list_more').hide();
	if(hide_fg == false){
		content_tr.show();
	}
}

$(function() {
    $("#kcaptcha_image_use").bind("click", function() {
        $.ajax({
            type: 'POST',
            url: g4_path+'/'+g4_bbs+'/kcaptcha_session.php',
            cache: false,
            async: false,
            success: function(text) {
                $("#kcaptcha_image_use").attr('src', g4_path+'/'+g4_bbs+'/kcaptcha_image.php?t=' + (new Date).getTime());
            }
        });
    })
    .css('cursor', 'pointer')
    .attr('title', '글자가 잘 안보이시는 경우 클릭하시면 새로운 글자가 나옵니다.')
    .attr('width', '120')
    .attr('height', '60')
    .trigger('click');

    explan_resize_image();
});

</script>


<?php
include_once "_tail.php";
?>
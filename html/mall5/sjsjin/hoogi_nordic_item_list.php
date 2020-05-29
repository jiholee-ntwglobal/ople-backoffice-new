<?php
include_once("./_common.php");

$g4[title] = "구매후기";
include_once("./_head.php");
# 내가 구매한 노르딕 제품 #
if($member['mb_id']){
	$sql_search_in_qry = sql_query("select DISTINCT it_id from yc4_event_item where ev_id in('1403859680','1403859988','1403860193','1403861457','1403863302','1403863916')");
	while($row = sql_fetch_array($sql_search_in_qry)){
		$sql_search_in .= ($sql_search_in ? ", ":"") . "'".$row['it_id']."'";
	}
	$my_od_qry = "
		select 
			a.it_id,
			b.it_name,
			count(*) as cnt
		from
			".$g4['yc4_order_table']." c
			left join
			".$g4['yc4_cart_table']." a on a.on_uid = c.on_uid
			left join
			".$g4['yc4_item_table']." b on a.it_id = b.it_id
		where
			a.ct_status = '완료'
			and
			a.it_id in (".$sql_search_in.")
			and
			c.mb_id = '".$member['mb_id']."'
		group by a.it_id
	";

	$my_od_sql = sql_query($my_od_qry);
	$my_od_sql_cnt = sql_fetch("select count(*) as cnt from (".$my_od_qry.") as tb");
	$my_od_sql_cnt = $my_od_sql_cnt['cnt'];
	
	while($row = sql_fetch_array($my_od_sql)){
		if($row['it_name']){
			$row['it_name'] = get_item_name($row['it_name']);
		}
		$review_chk = sql_fetch("select count(*) from ".$g4['yc4_item_ps_table']." where mb_id = '".$member['mb_id']."' and it_id = '".$row['it_id']."'");
		if($review_chk['cnt'] >= $row['cnt']){
			$my_od_sql_cnt--;
			continue;
		}
		$my_od_row .= "
			<tr>
				<td style='border-bottom: solid 1px #ececec; padding:10px 0;'>".get_it_image($row['it_id'].'_s',100,100,$row['it_id'])."</td>
				<td style='padding-left:5px; border-bottom: solid 1px #ececec;'><a href='".$g4['shop_path']."/item.php?it_id=".$row['it_id']."'>".$row['it_name']."</a></td>
				<td style='text-align:center; border-bottom: solid 1px #ececec;'><a href='#' onclick='review_view(this);'><img src='$g4[shop_path]/img/btn_story.gif'></a></td>
			</tr>
			<tr class='review_form_tr' style='border-top:none; display:none;'>
				<td colspan='3'>
					<form method='post' enctype='multipart/form-data' onsubmit='return fitemuse_submit(this);'>
						<input type='hidden' name='refer' value='".$g4['path']."/sjsjin/hoogi_nordic.php'/>
						<input type='hidden' name='it_id' value='".$row['it_id']."'/>
						<table width='100%'>
							<col width='70'/>
							<col width=''/>
							<tr bgcolor='#fafafa'>
								<td align='right'>제목</td>
								<td><input name='is_subject' class='ed' style=\"background-position: right top; width: 90%; background-image: url('".$g4['path']."/js/wrest.gif'); background-repeat: no-repeat;\" required type='text' itemname='제목' /></td>
							</tr>
							<tr bgcolor='#fafafa'>
								<td align='right'>내용</td>
								<td><textarea name='is_content' class='ed' style=\"background-position: right top; width: 90%; background-image: url('".$g4['path']."/js/wrest.gif'); background-repeat: no-repeat;\" required rows='7' itemname='내용'></textarea></td>
							</tr>
							<tr bgcolor='#fafafa'>
								<td align='right'>평가</td>
								<td>
									<input name='is_score' type='radio' checked value='10' checked />
									<img align='absmiddle' src='".$g4['shop_path']."/img/star5.gif' />
									<input name='is_score' type='radio' value='8' />
									<img align='absmiddle' src='".$g4['shop_path']."/img/star4.gif' />
									<input name='is_score' type='radio' value='6' />
									<img align='absmiddle' src='".$g4['shop_path']."/img/star3.gif' />
									<input name='is_score' type='radio' value='4' />
									<img align='absmiddle' src='".$g4['shop_path']."/img/star2.gif' />
									<input name='is_score' type='radio' value='2' />
									<img align='absmiddle' src='".$g4['shop_path']."/img/star1.gif' />
								</td>
							</tr>
							<tr bgcolor='#fafafa'>
								<td align='right'>이미지</td>
								<td><input type='file' name='is_image[0]' size='60'/></td>
							</tr>
							<tr bgcolor='#fafafa'>
								<td align='right'>이미지</td>
								<td><input type='file' name='is_image[1]' size='60'/></td>
							</tr>
							<tr bgcolor='#fafafa'>
								<td align='right'>이미지</td>
								<td><input type='file' name='is_image[2]' size='60'/></td>
							</tr>
							<tr bgcolor='#fafafa'>
								<td align='right'>이미지</td>
								<td><input type='file' name='is_image[3]' size='60'/></td>
							</tr>
							<tr bgcolor='#fafafa'>
								<td align='right'>이미지</td>
								<td><input type='file' name='is_image[4]' size='60'/></td>
							</tr>
							<tr bgcolor='#fafafa'>
								<td><img class='kcaptcha_image_use' /></td>
								<td><input type='text' name='is_key' class='ed' required itemname='자동등록방지용 코드'>&nbsp;* 왼쪽의 자동등록방지 코드를 입력하세요.</td>
							</tr>
							<tr>
								<td colspan='2' align='right' style='padding-top:10px;'>
									<input type='image' src='".$g4['shop_path']."/img/btn_confirm.gif' border='0'>
									<input type='image' src='".$g4['shop_path']."/img/btn_cancel.gif' border='0' onclick=\"$(this).parent().parent().parent().parent().parent().parent().hide(); return false;\">
								</td>
							</tr>
						</table>
					</form>
				</td>
			</tr>
		";
	}
	
}
?>
<table border='0' cellspacing="0" cellpadding="0" style="width: 755px;">
  <col width='100'/>
	<col width=''/>
	<col width='50'/>
  <tr>
    <td colspan='3' style='padding: 5px 0;'>
      <img src='http://115.68.20.84/main/hoogi.gif' alt=''>
    </td>
  </tr>
	<tr>
		<td colspan='2' style='text-align: center; font-size: 12px;border-bottom: 1px solid #666;background-color: #f4f4f4;padding: 10px 0;color: #000;border-top: 3px solid #000;'>상품명</td>
		<td style='text-align:center; width: 90px; font-size: 12px;border-bottom: 1px solid #666;background-color: #f4f4f4;padding: 10px 0;color: #000;border-top: 3px solid #000;' >후기작성</td>
	</tr>
	<?=$my_od_row;?>
</table>
<p align='center'><a href="<?=$g4['path']?>/sjsjin/hoogi_nordic_new.php">목록</a></p>

<script type="text/javascript" src="<?=$g4[path]?>/js/jquery.kcaptcha.js"></script>
<script type="text/javascript">
	function review_view( obj ){
		var review_form_tr = $(obj).parent().parent().next();
		review_form_tr.find(".kcaptcha_image_use").bind("click", function() {
			$.ajax({
				type: 'POST',
				url: g4_path+'/'+g4_bbs+'/kcaptcha_session.php',
				cache: false,
				async: false,
				success: function(text) {
					review_form_tr.find(".kcaptcha_image_use").attr('src', g4_path+'/'+g4_bbs+'/kcaptcha_image.php?t=' + (new Date).getTime());
				}
			});
		})
		.css('cursor', 'pointer')
		.attr('title', '글자가 잘 안보이시는 경우 클릭하시면 새로운 글자가 나옵니다.')
		.attr('width', '120')
		.attr('height', '60')
		.trigger('click');

		$('.review_form_tr').hide();
		review_form_tr.show();

	}

	function fitemuse_submit(f){
		if(f.is_subject.value == ''){
			alert('제목을 입력해 주세요');
			f.is_subject.focus();
			return false;
		}
		
		if(f.is_content.value == ''){
			alert('내용을 입력해 주세요');
			f.is_content.focus();
			return false;
		}

		if (!check_kcaptcha(f.is_key)) {
			return false;
		}
	

		f.action = "<?=$g4['shop_path'];?>/itemuseupdate.php"
		return true;
	}
</script>
<? include_once("./_tail.php"); ?>
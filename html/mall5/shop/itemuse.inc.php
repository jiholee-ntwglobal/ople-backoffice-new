<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 사용후기 -->
<a name="use"></a>
<div id='item_use' class="product-info" style='display:block;'>
<h2>사용후기</h2>
<table width=100% cellpadding=0 cellspacing=0>
<tr><td style='padding:15px;'>
        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr>
            <td width=11><img src='<?=$g4[shop_img_path]?>/corner01.gif'></td>
            <td valign=top>
                <table width=100% height=31 cellpadding=0 cellspacing=0 border=0>
                <tr align=center>
                    <td width=40 background='<?=$g4[shop_img_path]?>/box_bg01.gif'>번호</td>
                    <td background='<?=$g4[shop_img_path]?>/box_bg01.gif'>제목</td>
                    <td width=80 background='<?=$g4[shop_img_path]?>/box_bg01.gif'>작성자</td>
                    <td width=100 background='<?=$g4[shop_img_path]?>/box_bg01.gif'>작성일</td>
                    <td width=80 background='<?=$g4[shop_img_path]?>/box_bg01.gif'>평가점수</td>
                </tr>
                </table></td>
            <td width=11><img src='<?=$g4[shop_img_path]?>/corner02.gif'></td>
        </tr>
        <?
		# 노르딕 얼티메이트 오메가 후기 합치기 2014-12-26 홍민기 #
		if(in_array($it['it_id'],array('1413898640','1332425915'))){
			$item_use_it_id = "it_id in ('1413898640','1332425915')";
		}else{
			$item_use_it_id = "it_id = '".$it['it_id']."'";
		}

		# 셋트상품일 경우 해당되는 제품의 후기들로 로드 2015-02-05 홍민기 #
		if($child_it_id_in){
			$item_use_it_id = "it_id in (".$child_it_id_in.")";
		}

		# 클리어런스 상품 원래 상품후기와 함께 출력 2015-02-26 홍민기 #
		if($clearance_chk['ori_it_id']){
			$item_use_it_id = "it_id in ('".$it['it_id']."','".$clearance_chk['ori_it_id']."')";
		}

        $sql_common = " from ".$g4['yc4_item_ps_table']." where ".$item_use_it_id." and is_confirm = '1' ";

        // 테이블의 전체 레코드수만 얻음
        $sql = " select COUNT(is_id) as cnt " . $sql_common;
        $row = sql_fetch($sql);
        $use_total_count = $row[cnt];

        $use_total_page  = ceil($use_total_count / $use_page_rows); // 전체 페이지 계산
        if ($use_page == "") $use_page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
        $use_from_record = ($use_page - 1) * $use_page_rows; // 시작 레코드 구함

		// 김선용 201208 : 베스트 우선
        $sql = "select * $sql_common order by is_best desc, is_id desc limit $use_from_record, $use_page_rows ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++)
        {
            if ($i > 0)
                //echo "<tr><td colspan=3 background='$g4[shop_img_path]/dot_line.gif'></td></tr>";
				echo "<tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>";

            $num = $use_total_count - ($use_page - 1) * $use_page_rows - $i;

            $star = get_star($row[is_score]);

            $is_name = get_text($row[is_name]);
            $is_subject = conv_subject($row[is_subject],50,"…");
			//$is_subject = cut_str(stripslashes($row[is_subject]), 50, '..');
            $is_content = conv_content($row[is_content],0);
            $is_time = substr($row[is_time], 2, 14);

			// 김선용 201208 : 첨부이미지
			$img_str = "";
			for($a=0; $a<5; $a++){
				if($row["is_image{$a}"] != '' && file_exists("{$g4['path']}/data/ituse/".$row["is_image{$a}"]))
					$img_str .= "<img src='{$g4['path']}/data/ituse/".$row["is_image{$a}"]."' border=0 style='max-width:900px;'/><br/><br/>";
			}
			$best_str = ($row['is_best'] ? "<span style='color:blue; font-weight:bold;'>[베스트]</span> " : "");
            echo "
            <tr>
                <td width=11 background='$g4[shop_img_path]/box_bg02.gif'></td>
                <td valign=top>
                    <table width=100% cellpadding=0 cellspacing=0 border=0>
                    <tr align=center>
                        <td width=40 height=25>$num</td>
                        <td align=left>
							{$best_str}<b><a href='javascript:;' onclick=\"use_menu('is$i')\"><b>$is_subject</b></a></b>
                        <td width=80>$is_name</td>
                        <td width=100>$is_time</td>
                        <td width=80><img src='$g4[shop_img_path]/star{$star}.gif' border=0></td>
                    </tr>
                    </table>

                    <div id='is$i' style='display:none;'>
                    <table width=100% cellpadding=0 cellspacing=0 border=0>
                    <tr>
                        <td style='padding:10px;' class=lh>{$is_content}<br/>{$img_str}</td>
                    </tr>
                    <tr>
                        <td align=right height=30>
                            <textarea id='tmp_is_id{$i}' style='display:none;'>{$row[is_id]}</textarea>
                            <textarea id='tmp_is_name{$i}' style='display:none;'>{$row[is_name]}</textarea>
                            <textarea id='tmp_is_subject{$i}' style='display:none;'>{$row[is_subject]}</textarea>
                            <textarea id='tmp_is_content{$i}' style='display:none;'>{$row[is_content]}</textarea>";

            if ($row[mb_id] == $member[mb_id] || $is_admin === 'super')
            {
                echo "<a href='javascript:itemuse_update({$i});'><span class=small><b>수정</b></span></a>&nbsp;";
                echo "<a href='javascript:itemuse_delete(fitemuse_password{$i}, {$i});'><span class=small><b>삭제</b></span></a>&nbsp;";
            }

            echo "
                        </td>
                    </tr>
                    <!-- 사용후기 삭제 패스워드 입력 폼 -->
                    <tr id='itemuse_password{$i}' style='display:none;'>
                        <td align=right height=30>
                            <form name='fitemuse_password{$i}' method='post' action='./itemuseupdate.php' autocomplete=off style='padding:0px;'>
								<input type='hidden' name='w' value=''>
								<input type='hidden' name='is_id' value=''>
								<input type='hidden' name='it_id' value='{$it[it_id]}'>
								패스워드 : <input type=password class=ed name=is_password required itemname='패스워드'>
								<input type=image src='{$g4[shop_img_path]}/btn_confirm.gif' border=0 align=absmiddle></a>
                            </form>
                        </td>
                    </tr>
                    </table>
                    </div></td>
                <td width=11 background='$g4[shop_img_path]/box_bg03.gif'>&nbsp;</td></tr>
            </tr>
            ";
        }

        if (!$i)
        {
            echo "
            <tr>
                <td width=11 background='$g4[shop_img_path]/box_bg02.gif'></td>
                <td height=100 align=center class=lh>";

					// 김선용 201208 :
					if($default['de_it_use_first_postpoint']){ echo "<br><img src='$g4[shop_img_path]/point.gif' border=0><br><br>"; }

					echo "이 상품에 대한 사용후기가 아직 없습니다. 사용후기를 작성해 주시면 다른 분들께 많은 도움이 됩니다.</td>
                <td width=11 background='$g4[shop_img_path]/box_bg03.gif'>&nbsp;</td></tr>
            </tr>";
        }

        $use_pages = get_paging($use_page_rows, $use_page, $use_total_page, "./item.php?it_id=$it_id&$qstr&use_page=", "#use");
        if ($use_pages)
        {
            echo "<tr><td colspan=3 background='$g4[shop_img_path]/dot_line.gif'></td></tr>";
            echo "<tr>";
            echo "<td width=11 background='$g4[shop_img_path]/box_bg02.gif'></td>";
            echo "<td height=22 align=center>$use_pages</td>";
            echo "<td width=11 background='$g4[shop_img_path]/box_bg03.gif'>&nbsp;</td></tr>";
            echo "</tr>";
        }
        ?>
        <tr>
            <td width=11><img src='<?=$g4[shop_img_path]?>/corner03.gif'></td>
            <td width=100% background='<?=$g4[shop_img_path]?>/box_bg04.gif'></td>
            <td width=11><img src='<?=$g4[shop_img_path]?>/corner04.gif'></td>
        </tr>
        </table>



        <table width=100% cellpadding=0 cellspacing=0>
        <tr><td colspan=2 height=35>* 이 상품을 사용해 보셨다면 사용후기를 써 주십시오. 베스트후기에 선정되시면 <span class="font11_orange">포인트 2000점</span>을 드립니다!
            <input type=image src='<?="$g4[shop_img_path]/btn_story.gif"?>' onclick="itemuse_insert();" align=absmiddle></td></tr>
        </table>



        <!-- 사용후기 폼 -->
        <div id='itemuse' style='display:none;'>
		<?
		# 해당 상품의 본인이 작성한 후기 갯수 #
		$review_chk = sql_fetch("
			select count(*) as cnt from ".$g4['yc4_item_ps_table']." where it_id = '".$it_id."' and mb_id = '".$member['mb_id']."' and mb_id != ''
		");

		# 주문내역의 해당 상품 주문 수 #
		$order_item_cnt = sql_fetch("
			select
			count(*) as cnt
			from
				".$g4['yc4_order_table']." a
				left join
				".$g4['yc4_cart_table']." b on a.on_uid = b.on_uid
			where
				b.it_id = '".$it_id."'
				and
				b.ct_status = '완료'
				and
				a.mb_id = '".$member['mb_id']."'
				and
				a.mb_id != ''
		");



		# 주문내역이 없거나 주문내역보다 후기가 많거나 같을경우에는 후기 못씀
		if($order_item_cnt['cnt'] == 0 || $review_chk['cnt'] >= $order_item_cnt['cnt']){
			# 주문내역의 해당 상품 주문 수 #
			$order_item_cnt = sql_fetch("
				select
				count(*) as cnt
				from
					".$g4['yc4_order_table']." a
					left join
					".$g4['yc4_cart_table']." b on a.on_uid = b.on_uid
				where
					b.it_id = '".$it_id."'
					and
					b.ct_status in ('배송','준비','주문')
					and
					a.mb_id = '".$member['mb_id']."'
					and
					a.mb_id != ''
			");



			# 주문내역은 존재하지만 아직 배송중인 주문건일 경우 수령확인 누르라는 메세지 #
			if($order_item_cnt['cnt'] > 0 && $review_chk['cnt'] <= $order_item_cnt['cnt']){
				$review_err = 1;
				echo "해당상품이 배송 완료된 주문내역이 존재하지 않습니다. 상품 수령확인 후 후기를 작성해 주세요.";
			}else{
				$review_err = 2;
				echo "주문 내역이 존재하지 않습니다.";
			}
		}?>
			<form name="fitemuse" method="post" onsubmit="return fitemuse_submit(this);" autocomplete=off style="margin:0;" enctype="multipart/form-data">
			<input type=hidden name=w value=''>
			<input type=hidden name=token value='<?=$token?>'>
			<input type=hidden name=is_id value=''>
			<input type=hidden name=it_id value='<?=$it[it_id]?>'>
			<table width=100% cellpadding=0 cellspacing=0 border=0>
			<tr><td height=2 bgcolor=#6EA7D3 colspan=2></td></tr>

			<? if (!$is_member) { ?>
			<tr bgcolor=#fafafa>
				<td width=100 height=30 align=right>이름&nbsp;</td>
				<td>&nbsp;<input type="text" name="is_name" class=ed maxlength=20 minlength=2 required itemname="이름"></td></tr>
			<tr bgcolor=#fafafa>
				<td height=30 align=right>패스워드&nbsp;</td>
				<td>&nbsp;<input type="password" name="is_password" class=ed maxlength=20 minlength=3 required itemname="패스워드">
					<span class=small>패스워드는 최소 3글자 이상 입력하십시오.</span></td></tr>
			<? } ?>

			<tr bgcolor=#fafafa>
				<td width=100 height=30 align=right>제목&nbsp;</td>
				<td>&nbsp;<input type="text" name="is_subject" style="width:90%;" class=ed required itemname="제목"></td></tr>
			<tr bgcolor=#fafafa>
				<td align=right>내용&nbsp;</td>
				<td>&nbsp;<textarea name="is_content" rows="7" style="width:90%;" class=ed required itemname="내용"></textarea></td></tr>
			<tr bgcolor=#fafafa>
				<td height=30 align=right>평가&nbsp;</td>
				<td>
					<input type=radio name=is_score value='10' checked><img src='<?=$g4[shop_img_path]?>/star5.gif' align=absmiddle>
					<input type=radio name=is_score value='8'><img src='<?=$g4[shop_img_path]?>/star4.gif' align=absmiddle>
					<input type=radio name=is_score value='6'><img src='<?=$g4[shop_img_path]?>/star3.gif' align=absmiddle>
					<input type=radio name=is_score value='4'><img src='<?=$g4[shop_img_path]?>/star2.gif' align=absmiddle>
					<input type=radio name=is_score value='2'><img src='<?=$g4[shop_img_path]?>/star1.gif' align=absmiddle>
				</td>
			</tr>

			<!-- // 김선용 201208 : -->
			<?for($k=0; $k<5; $k++){ ?>
			<tr bgcolor=#fafafa>
				<td align=right>이미지&nbsp;</td>
				<td><input type="file" name="is_image[<?=$k?>]" size=60 /></td>
			</tr>
			<?}?>
			<tr bgcolor=#fafafa><td height=30 colspan=2><span style="margin-left:100px;">
			<?/*
			※ 등록후 이미지수정은 해당 이미지입력란에 새로운 이미지를 선택해서 등록 하십시오.
			*/?>
			※ 직접 촬영한 사진이 아닐경우 일반 후기 포인트로 조정될 수 있습니다.
			</span></td></tr>

		   <tr bgcolor=#fafafa>
				<td align=right><img id='kcaptcha_image_use' /></td>
				<td>
					&nbsp;<input type='text' name='is_key' class='ed' required itemname='자동등록방지용 코드'>
					&nbsp;* 왼쪽의 자동등록방지 코드를 입력하세요.</td></tr>

			<tr><td height=2 bgcolor=#6ea7d3 colspan=2></td></tr>
			<tr><td colspan=2 align=right height=30><input type=image src='<?=$g4[shop_img_path]?>/btn_confirm.gif' border=0></a></td></tr>
			</table>
			</form>


        <br><br>
        </div>
    </td>
</tr>
<tr><td colspan=2 height=1></td></tr>
</table>
</div>

<script type="text/javascript">
function fitemuse_submit(f)
{
    if (!check_kcaptcha(f.is_key)) {
        return false;
    }

	<?/* 후기 20자 이상 입력 2015-02-24 홍민기 */?>
	if(f.is_content.value.length<20){
		alert('내용은 20자 이상 입력해야 합니다.');
		return false;
	}

    f.action = "itemuseupdate.php"
	return true;
}

function itemuse_insert()
{
    if (!g4_is_member) {
        alert("로그인 하시기 바랍니다.");
        return;
    }

	var review_err = '<?=$review_err?>';

	switch(review_err){
		case '1' : alert('해당상품이 배송 완료된 주문내역이 존재하지 않습니다. 상품 수령확인 후 후기를 작성해 주세요.'); return false; break;
		case '2' : alert('주문 내역이 존재하지 않습니다.'); return false; break;
	}

    var f = document.fitemuse;
    var id = document.getElementById('itemuse');

    id.style.display = 'block';

    f.w.value = '';
    f.is_id.value = '';
    if (!g4_is_member)
    {
        f.is_name.value = '';
        f.is_name.readOnly = false;
        f.is_password.value = '';
    }
    f.is_subject.value = '';
    f.is_content.value = '';
}

function itemuse_update(idx)
{
    var f = document.fitemuse;
    var id = document.getElementById('itemuse');

    id.style.display = 'block';

    f.w.value = 'u';
    f.is_id.value = document.getElementById('tmp_is_id'+idx).value;
    if (!g4_is_member)
    {
        f.is_name.value = document.getElementById('tmp_is_name'+idx).value;
        f.is_name.readOnly = true;
    }
    f.is_subject.value = document.getElementById('tmp_is_subject'+idx).value;
    f.is_content.value = document.getElementById('tmp_is_content'+idx).value;
}

function itemuse_delete(f, idx)
{
    var id = document.getElementById('itemuse');

    f.w.value = 'd';
    f.is_id.value = document.getElementById('tmp_is_id'+idx).value;

    if (g4_is_member)
    {
        if (confirm("삭제하시겠습니까?"))
            f.submit();
    }
    else
    {
        id.style.display = 'none';
        document.getElementById('itemuse_password'+idx).style.display = 'block';
    }
}
</script>
<!-- 사용후기 end -->

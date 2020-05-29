<?php
include_once("./_common.php");

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

// 김선용 200908 :
$rand = rand(4, 6);
$norobot_key = substr($token, 0, $rand);
set_session('ss_norobot_key', $norobot_key);


// 오늘 본 상품 저장 시작
// tv 는 today view 약자
$saved = false;
$tv_idx = (int)get_session("ss_tv_idx");
if ($tv_idx > 0) {
    for ($i=1; $i<=$tv_idx; $i++) {
        if (get_session("ss_tv[$i]") == $it_id) {
            $saved = true;
            break;
        }
    }
}

if (!$saved) {
    $tv_idx++;
    set_session("ss_tv_idx", $tv_idx);
    set_session("ss_tv[$tv_idx]", $it_id);
}
// 오늘 본 상품 저장 끝

// 조회수 증가
if ($_COOKIE[ck_it_id] != $it_id) {
    sql_query(" update $g4[yc4_item_table] set it_hit = it_hit + 1 where it_id = '$it_id' "); // 1증가
    setcookie("ck_it_id", $it_id, time() + 3600, $config[cf_cookie_dir], $config[cf_cookie_domain]); // 1시간동안 저장
}

// 분류사용, 상품사용하는 상품의 정보를 얻음
$sql = " select a.*,
                b.ca_name,
                b.ca_use
           from $g4[yc4_item_table] a,
                $g4[yc4_category_table] b
          where a.it_id = '$it_id'
            and a.ca_id = b.ca_id ";
$it = sql_fetch($sql);
if (!$it[it_id])
    alert("자료가 없습니다.");
if (!($it[ca_use] && $it[it_use])) {
	// 김선용 2014.04 : 작업용 테스트계정 통과
    //if (!$is_admin)
	if(!$is_admin && !check_test_id())
        alert("판매가능한 상품이 아닙니다.");
}
// 김선용 2014.04 : 작업용 테스트계정 통과
// 김선용 201211 : 단종
//if(!$is_admin && $it['it_discontinued']) alert("단종된 상품입니다.");
if((!$is_admin && !check_test_id()) && $it['it_discontinued']) alert("단종된 상품입니다.");

// 분류 테이블에서 분류 상단, 하단 코드를 얻음
$sql = " select ca_include_head, ca_include_tail
           from $g4[yc4_category_table]
          where ca_id = '$it[ca_id]' ";
$ca = sql_fetch($sql);

$g4[title] = "상품 상세보기 : $it[ca_name] - $it[it_name] ";

// 분류 상단 코드가 있으면 출력하고 없으면 기본 상단 코드 출력
if ($ca[ca_include_head])
    @include_once($ca[ca_include_head]);
else
    include_once("./_head.php");

// 분류 위치
// HOME > 1단계 > 2단계 ... > 6단계 분류
$ca_id = $it[ca_id];
include "$g4[shop_path]/navigation1.inc.php";

$himg = "$g4[path]/data/item/{$it_id}_h";
if (file_exists($himg))
    echo "<img src='$himg' border=0><br>";

// 상단 HTML
echo stripslashes($it[it_head_html]);

if ($is_admin)
    echo "<p align=center><a href='$g4[shop_admin_path]/itemform.php?w=u&it_id=$it_id'><img src='$g4[shop_img_path]/btn_admin_modify.gif' border=0></a></p>";

// 이 분류에 속한 하위분류 출력
include "$g4[shop_path]/listcategory.inc.php";

// 이전 상품보기
$sql = " select it_id, it_name from $g4[yc4_item_table]
          where it_id > '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it[ca_id],0,4)."'
            and it_use = '1'
          order by it_id asc
          limit 1 ";
$row = sql_fetch($sql);
if ($row[it_id]) {
    $prev_title = "[이전상품보기] $row[it_name]";
    $prev_href = "<a href='./item.php?it_id=$row[it_id]'>";
} else {
    $prev_title = "[이전상품없음]";
    $prev_href = "";
}

// 다음 상품보기
$sql = " select it_id, it_name from $g4[yc4_item_table]
          where it_id < '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it[ca_id],0,4)."'
            and it_use = '1'
          order by it_id desc
          limit 1 ";
$row = sql_fetch($sql);
if ($row[it_id]) {
    $next_title = "[다음상품보기] $row[it_name]";
    $next_href = "<a href='./item.php?it_id=$row[it_id]'>";
} else {
    $next_title = "[다음상품없음]";
    $next_href = "";
}

// 관련상품의 갯수를 얻음
$sql = " select count(*) as cnt
           from $g4[yc4_item_relation_table] a
           left join $g4[yc4_item_table] b on (a.it_id2=b.it_id and b.it_use='1')
          where a.it_id = '$it[it_id]' ";
$row = sql_fetch($sql);
$item_relation_count = $row[cnt];

?>
<script type="text/JavaScript" src="<?=$g4[path]?>/js/shop.js"></script>
<script type="text/JavaScript" src="<?=$g4[path]?>/js/md5.js"></script>

<br>
<table width=99% cellpadding=0 cellspacing=0 align=center border=0><tr><td>

<table width=755 cellpadding=0 cellspacing=0>
<form name=fitem method=post action="./cartupdate.php">
<input type=hidden name=it_id value='<?=$it[it_id]?>'>
<input type=hidden name=it_name value='<?=$it[it_name]?>'>
<input type=hidden name=sw_direct>
<input type=hidden name=url>
<input type=hidden name=it_order_onetime_limit_cnt value="<?=$it['it_order_onetime_limit_cnt']?>">
<tr>

    <!-- 상품중간이미지 -->
    <?
    $middle_image = $it[it_id]."_m";
    ?>
    <td align=center valign=top>
        <table cellpadding=0 cellspacing=0>
            <tr><td height=22></td></tr>
            <tr><td colspan=3 align=center>
                <table cellpadding=1 cellspacing=0 bgcolor=#E4E4E4><tr><td><?=get_large_image($it[it_id]."_l1", $it[it_id], false)?><?=get_it_image($middle_image, 200, 200);?></a></td></tr></table></td></tr>
            <tr><td colspan=3 height=10></td></tr>
            <tr>
                <td colspan=3 align=center>
                <?
                for ($i=1; $i<=5; $i++)
                {
                    if (file_exists("$g4[path]/data/item/{$it_id}_l{$i}"))
                    {
                        echo get_large_image("{$it_id}_l{$i}", $it[it_id], false);
                        if ($i==1 && file_exists("$g4[path]/data/item/{$it_id}_m"))
                            echo "<img id='middle{$i}' src='$g4[path]/data/item/{$it_id}_m' border=0 width=40 height=40 style='border:1px solid #E4E4E4;' ";
                        else
                            echo "<img id='middle{$i}' src='$g4[path]/data/item/{$it_id}_l{$i}' border=0 width=40 height=40 style='border:1px solid #E4E4E4;' ";
                        echo " onmouseover=\"document.getElementById('$middle_image').src=document.getElementById('middle{$i}').src;\">";
                        echo "</a> &nbsp;";
                    }
                }
                ?>
                </td>
            </tr>
            <tr><td colspan=3 height=7></td></tr>
            <tr><td align=center>
				<?=$prev_href?><!--<img src='<?//=$g4[shop_img_path]?>/prev.gif' border=0 title='<?//=$prev_title?>'>--><img src="<?=$g4['path']?>/images/category/category_buy_box01_pre.gif" width="24" height="24" border="0" alt="이전상품"></a>
                <?=get_large_image($it[it_id]."_l1", $it[it_id])?>
                <?=$next_href?><!--<img src='<?//=$g4[shop_img_path]?>/next.gif' border=0 title='<?//=$next_title?>'>--><img src="<?=$g4['path']?>/images/category/category_buy_box01_next.gif" width="24" height="24" border="0" alt="다음상품"></a>
			</td></tr>
        </table>
    </td>
    <!-- 상품중간이미지 END -->

    <td width=480 valign=top align=center>
        <table width=460 cellpadding=0 cellspacing=0 bgcolor=#fa5a00><tr>
		<td bgcolor=white style="padding:10px;" colspan=2 valign=top><span class="itemtitle"><?=it_name_icon($it, stripslashes($it[it_name]), 0)?></span></td>
		</tr></table>
		<div style="padding-top:5px;"></div>

        <table width=460 cellpadding=0 cellspacing=0>
        <colgroup width=110></colgroup>
        <colgroup width=20></colgroup>
        <colgroup width=300></colgroup>
        <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>

        <? if ($score = get_star_image($it[it_id])) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 고객선호도</td>
            <td align=center>:</td>
            <td><img src='<?="$g4[shop_img_path]/star{$score}.gif"?>' border=0></td></tr>
        <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
        <? } ?>


        <? if ($it[it_maker]) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 제조사</td>
            <td align=center>:</td>
            <td><a href="<?=$g4['shop_path']?>/search.php?it_maker=<?=urlencode(stripslashes($it['it_maker']))?>" title="새창으로 현재 제조사 상품보기" target="_blank"><?=stripslashes($it['it_maker'])?>&nbsp;&nbsp;[ 해당 제조사 상품보기 ]</a></td></tr>
        <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
        <? } ?>


        <? if ($it[it_origin]) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 원산지</td>
            <td align=center>:</td>
            <td><?=$it[it_origin]?></td></tr>
        <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
        <? } ?>


        <?
        // 선택옵션 출력
        for ($i=1; $i<=6; $i++)
        {
            // 옵션에 문자가 존재한다면
            $str = get_item_options(trim($it["it_opt{$i}_subject"]), trim($it["it_opt{$i}"]), $i);
            if ($str)
            {
                echo "<tr height=25>";
                echo "<td>&nbsp;&nbsp;&nbsp; · ".$it["it_opt{$i}_subject"]."</td>";
                echo "<td align=center>:</td>";
                echo "<td style='word-break:break-all;'>$str</td></tr>\n";
                echo "<tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>\n";
            }
        }
        ?>


        <? if (!$it[it_gallery]) { // 갤러리 형식이라면 가격, 구매하기 출력하지 않음 ?>

            <? if ($it[it_tel_inq]) { // 전화문의일 경우 ?>

                <tr>
                    <td height=25>&nbsp;&nbsp;&nbsp; · 판매가격</td>
                    <td align=center>:</td>
                    <td><FONT COLOR="#FF5D00">전화문의</FONT></td></tr>
		        <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>

            <? } else { ?>

                <? if ($it[it_cust_amount]) { // 1.00.03 ?>
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 시중가격</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_cust_amount size=12 style='text-align:right; border:none; border-width:0px; font-weight:bold; width:80px; color:#777777; text-decoration:line-through;' readonly value='<?=number_format($it[it_cust_amount])?>'> 원</td>
                </tr>
                <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
                <? } ?>

				<!-- // 김선용 201208 : -->
				<? // 할인설정이 돼있는 경우만 출력한다.
				if(in_array($member['mb_level'], array('3', '4')))
				{
					$off_arr = explode("|", $default['de_mb_level_off']);
					$off_true = false;
					for($k=3; $k<5; $k++){
						if(array_pop(explode('=>', $off_arr[($k-3)]))){
							$off_true = true;
							break;
						}
					}
					if($off_true){
				?>
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 일반회원 판매가격</td>
                    <td align=center>:</td>
                    <td><span style="color:black; font-weight:bold;"><?=nf($it['it_amount'])?></span> 원</td>
                </tr>
                <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
				<?}}?>

                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 판매가격</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_sell_amount size=12 style='text-align:right; border:none; border-width:0px; font-weight:bold; width:80px; font-family:Tahoma;' class=amount readonly> 원
                        <input type=hidden name=it_amount value='0'>
                    </td>
                </tr>
                <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>

                <?
                /* 재고를 표시하는 경우 주석을 풀어주세요.
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 재고수량</td>
                    <td align=center>:</td>
                    <td><?=number_format(get_it_stock_qty($it_id))?> 개</td>
                </tr>
                <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
                */
                ?>

                <? if ($config[cf_use_point]) { // 포인트 사용한다면 ?>
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 포 인 트</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_point size=12 style='text-align:right; border:none; border-width:0px; width:80px;' readonly> 점
                        <input type=hidden name=it_point value='0'>
                    </td>
                </tr>
                <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
                <? } ?>

                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 수 량</td>
                    <td align=center>:</td>
                    <td>
                        <input type=text name=ct_qty value='1' size=4 maxlength=4 class=ed autocomplete='off' style='text-align:right;' onkeyup='amount_change()'>
                        <img src='<?=$g4[shop_img_path]?>/qty_control.gif' border=0 align=absmiddle usemap="#qty_control_map"> 개
                        <map name="qty_control_map">
                        <area shape="rect" coords="0, 0, 10, 9" href="javascript:qty_add(+1);">
                        <area shape="rect" coords="0, 10, 10, 19" href="javascript:qty_add(-1);">
                        </map></td>
                </tr>
				<!-- // 김선용 201208 : -->
				<?
				// 품절 sms통보
//				if($it['it_stock_qty'] < 1) {
				
				# 제목의 품절과 품절 카운팅의 차이가 있어서 수정 2014-05-07 홍민기
				$it_stock_qty = get_it_stock_qty($it_id);
				if($it_stock_qty < 1) {
				?>
				<tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
				<tr>
					<?/*
					<td height=30 colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#ff0000; font-weight:bold;">※ 품절상품입니다.</span>&nbsp;&nbsp;<a href="javascript:;" onclick="js_item_sms('<?=$it['it_id']?>');"><b>[ 이상품 입고시 SMS통보 신청하기 ]</b></a></td>
					*/?>
					<td height=30 colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#ff0000; font-weight:bold;">※ 품절상품입니다.</span>&nbsp;&nbsp;<a href="#" onclick="js_item_sms('<?=$it['it_id']?>');"><b>[ 이상품 입고시 SMS통보 신청하기 ]</b></a></td>
				</tr>

					<script type="text/javascript">
					// 품절상품입고시 sms신청
					function js_item_sms(it_id)
					{
						if(it_id == '') return;
						var url = g4_path+'/sjsjin/item_sms_write.php?it_id='+it_id;
						popup_window(url, 'item_sms_write', 'left=250,top=200,width=500,height=300,status=0,scrollbars=0');
					}
					</script>
				<?}?>

				<?if($it['it_order_onetime_limit_cnt']){?>
				<tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
				<tr>
					<td colspan=3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>※ 이 상품은 1회 최대구매수량이 <?=$it['it_order_onetime_limit_cnt']?> 개 입니다.</b></td>
				</tr>
				<?}?>
            <? } ?>

        <? } ?>
        <tr><td colspan=3 height=1 bgcolor=#eaeaea></td></tr>
        </table>
        <br>

        <table cellpadding=0 cellspacing=0 width=100%>
        <tr>
            <td align=center>
            <? if (!$it[it_tel_inq] && !$it[it_gallery]) { ?>
            <a href="javascript:fitemcheck(document.fitem, 'direct_buy');"><!--<img src='<?=$g4[shop_img_path]?>/btn2_now_buy.gif' border=0>--><img src="<?=$g4['path']?>/images/category/category_buy_box01_btn_buy.gif" width="95" height="40" hspace="1" border="0"></a>
            <a href="javascript:fitemcheck(document.fitem, 'cart_update');"><!--<img src='<?=$g4[shop_img_path]?>/btn2_cart.gif' border=0>--><img src="<?=$g4['path']?>/images/category/category_buy_box01_btn_cart.gif" width="95" height="40" hspace="1" border="0"></a>
            <? } ?>

            <? if (!$it[it_gallery]) { ?>
            <a href="javascript:item_wish(document.fitem, '<?=$it[it_id]?>');"><!--<img src='<?=$g4[shop_img_path]?>/btn2_wish.gif' border=0>--><img src="<?=$g4['path']?>/images/category/category_buy_box01_btn_wish.gif" width="95" height="40" hspace="1" border="0"></a>
            <a href="javascript:popup_item_recommend('<?=$it[it_id]?>');"><!--<img src='<?=$g4[shop_img_path]?>/btn_item_recommend.gif' border=0>--><img src="<?=$g4['path']?>/images/category/category_buy_box01_btn_friend.gif" width="95" height="40" hspace="1" border="0"</a>
            <? } ?>

            <script type="text/JavaScript">
            // 상품보관
            function item_wish(f, it_id)
            {
                f.url.value = "<?=$g4[shop_path]?>/wishupdate.php?it_id="+it_id;
                f.action = "<?=$g4[shop_path]?>/wishupdate.php";
                f.submit();
            }

            // 추천메일
            function popup_item_recommend(it_id)
            {
                if (!g4_is_member)
                {
                    if (confirm("회원만 추천하실 수 있습니다."))
                        document.location.href = "<?=$g4[bbs_path]?>/login.php?url=<?=urlencode("$g4[shop_path]/item.php?it_id=$it_id")?>";
                }
                else
                {
                    url = "./itemrecommend.php?it_id=" + it_id;
                    opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
                    popup_window(url, "itemrecommend", opt);
                }
            }
            </script>

            </td></tr>
        </table></td>
    </tr>
	<tr><td colspan=3 height=20></td></tr>


    <tr ><td colspan=3>
    <br><br>
        <table  cellpadding=0 cellspacing=0>
        <tr class="product-tab">
            <!-- 상품정보 --><td><a href="javascript:click_item('*');"><img src='<?=$g4[shop_img_path]?>/tab01.gif' border=0></a></td>
            <!-- 사용후기 --><td background='<?=$g4[shop_img_path]?>/tab02.gif' border=0 style='padding-top:2px; width:125px'><a href="javascript:click_item('item_use');" style="cursor:pointer;"><span class="small" style='color:#fff;text-decoration:none'>(<span id=item_use_count>0</span>)</span></a></td>

			<?if(!preg_match("/okflex\.com/", $_SERVER['HTTP_HOST'])){ // 김선용 201107 : okflex.com 일때 미출력 ?>
			<!-- 상품문의 --><td background='<?=$g4[shop_img_path]?>/tab03.gif' border=0 style='padding-top:2px; width:125px'><a href="javascript:click_item('item_qa');" style="cursor:pointer;"><span class="small" style='color:#fff;text-decoration:none'>(<span id=item_qa_count>0</span>)</span></a></td>
			<?}?>

<td><a href="javascript:click_item('item_baesong');"><img src='<?=$g4[shop_img_path]?>/tab04.gif' border=0></a></td>
<td><a href="javascript:click_item('item_change');"><img src='<?=$g4[shop_img_path]?>/tab05.gif' border=0></a></td>
            <!-- 관련상품 --><td  background='<?=$g4[shop_img_path]?>/tab06.gif' border=0 style='padding-top:2px; width:125px'><a href="javascript:click_item('item_relation');" style="cursor:pointer;"><span class="small" style='color:#fff;text-decoration:none'>(<span id=item_relation_count>0</span>)</span></a></td>
        </tr>
        </table>
</td></tr>
</form>
</table>

<script type="text/JavaScript">
function click_item(id)
{
    <?
	if(preg_match("/okflex\.com/", $_SERVER['HTTP_HOST']))
	    echo "var str = 'item_explan,item_use";
	else
	    echo "var str = 'item_explan,item_use,item_qa";
    if ($default[de_baesong_content]) echo ",item_baesong";
    if ($default[de_change_content]) echo ",item_change";
    echo ",item_relation';";
    ?>

    var s = str.split(',');

    for (i=0; i<s.length; i++)
    {
        if (id=='*')
            document.getElementById(s[i]).style.display = 'block';
        else
            document.getElementById(s[i]).style.display = 'none';
    }

    if (id!='*')
        document.getElementById(id).style.display = 'block';
}
</script>



<!-- 상품설명 -->
<div id='item_explan' class="product-info" style='display:block;'>
<h2>상품정보</h2>
    <? if ($it[it_basic]) { ?>
<?=$it[it_basic]?>
    <? } ?>

    <? if ($it[it_explan]) { ?>
<div id='div_explan'>
<?

$file = 'http://115.68.20.84/desc/'.$_GET['it_id'].'.jpg';
$file_headers = @get_headers($file);
if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
    echo conv_content($it[it_explan], 1);
}
else {
    echo "<img src='".$file."'/>";

}


?>
<?//=conv_content($it[it_explan], 1);?>
</div>
    <? } ?>

</div>
<!-- 상품설명 end -->



<?
// 사용후기
$use_page_rows = 30;    // 사용후기 페이지당 목록수
include_once("./itemuse.inc.php");


// 김선용 201107 : OKFLEX.COM 일때 미출력
if(!preg_match("/okflex\.com/", $_SERVER['HTTP_HOST'])){
// 상품문의
$qa_page_rows = 30;     // 상품문의 페이지당 목록수
include_once("./itemqa.inc.php");
}
?>


<!-- 배송정보 -->
<div id='item_baesong' class="product-info" style='display:block;'>
<h2>배송정보</h2>
<br>
<center><img src='<?=$g4[shop_img_path]?>/baesong.gif'></center>
<?=conv_content($default[de_baesong_content], 1);?>
</div>
<!-- 배송정보 end -->


<!-- 교환/반품 -->
<div id='item_change' class="product-info" style='display:block;'>
<h2> 교환/반품</h2>

<br>
<center><img src='<?=$g4[shop_img_path]?>/exchange.gif'></center>
</div>
<!-- 교환/반품 end -->


<!-- 관련상품 -->
<div id='item_relation' class="product-info" style='display:block;'>
<h2>관련상품</h2>
        <?
        $sql = " select b.*
                   from $g4[yc4_item_relation_table] a
                   left join $g4[yc4_item_table] b on (a.it_id2=b.it_id)
                  where a.it_id = '$it[it_id]'
                    and b.it_use='1' ";
        $result = sql_query($sql);
        $num = @mysql_num_rows($result);
        if ($num){
			$list_mod   = $default[de_rel_list_mod];
			$img_width  = $default[de_rel_img_width];
			$img_height = $default[de_rel_img_height];
			$td_width = (int)(100 / $list_mod);

            include "$g4[shop_path]/maintype10.inc.php";
		}
        else
            echo "이 상품과 관련된 상품이 없습니다.";
        ?>
</div>
<!-- 관련상품 end -->



</td></tr></table>


<script type="text/JavaScript">
/*
//var basic_amount = parseInt('<?=$it[it_amount]?>');
var basic_amount = parseInt('<?=get_amount($it)?>');
var basic_point  = parseFloat('<?=$it[it_point]?>');
var cust_amount  = parseFloat('<?=$it[it_cust_amount]?>');
*/

function qty_add(num)
{
    var f = document.fitem;
    var qty = parseInt(f.ct_qty.value);
    if (num < 0 && qty <= 1)
    {
        alert("수량은 1 이상만 가능합니다.");
        qty = 1;
    }
    else if (num > 0 && qty >= 9999)
    {
        alert("수량은 9999 이하만 가능합니다.");
        qty = 9999;
    }
    else
    {
        qty = qty + num;
    }

    f.ct_qty.value = qty;

    amount_change();
}

function get_amount(data)
{
    var str = data.split(";");
    var num = parseInt(str[1]);
    if (isNaN(num)) {
        return 0;
    } else {
        return num;
    }
}

function amount_change()
{
    var basic_amount = parseInt('<?=get_amount($it)?>');
    var basic_point  = parseFloat('<?=$it[it_point]?>');
    var cust_amount  = parseFloat('<?=$it[it_cust_amount]?>');

    var f = document.fitem;
    var opt1 = 0;
    var opt2 = 0;
    var opt3 = 0;
    var opt4 = 0;
    var opt5 = 0;
    var opt6 = 0;
    var ct_qty = 0;

    if (typeof(f.ct_qty) != 'undefined')
        ct_qty = parseInt(f.ct_qty.value);

    if (typeof(f.it_opt1) != 'undefined') opt1 = get_amount(f.it_opt1.value);
    if (typeof(f.it_opt2) != 'undefined') opt2 = get_amount(f.it_opt2.value);
    if (typeof(f.it_opt3) != 'undefined') opt3 = get_amount(f.it_opt3.value);
    if (typeof(f.it_opt4) != 'undefined') opt4 = get_amount(f.it_opt4.value);
    if (typeof(f.it_opt5) != 'undefined') opt5 = get_amount(f.it_opt5.value);
    if (typeof(f.it_opt6) != 'undefined') opt6 = get_amount(f.it_opt6.value);

    var amount = basic_amount + opt1 + opt2 + opt3 + opt4 + opt5 + opt6;
    var point  = parseInt(basic_point);

    if (typeof(f.it_amount) != 'undefined')
        f.it_amount.value = amount;

    if (typeof(f.disp_sell_amount) != 'undefined')
        f.disp_sell_amount.value = number_format(String(amount * ct_qty));

    if (typeof(f.disp_cust_amount) != 'undefined')
        f.disp_cust_amount.value = number_format(String(cust_amount * ct_qty));

    if (typeof(f.it_point) != 'undefined') {
        f.it_point.value = point;
        f.disp_point.value = number_format(String(point * ct_qty));
    }
}

<? if (!$it[it_gallery]) { echo "amount_change();"; } // 처음시작시 한번 실행 ?>

// 바로구매 또는 장바구니 담기
function fitemcheck(f, act)
{
    // 판매가격이 0 보다 작다면
    if (f.it_amount.value < 0)
    {
        alert("전화로 문의해 주시면 감사하겠습니다.");
        return;
    }

    for (i=1; i<=6; i++)
    {
        if (typeof(f.elements["it_opt"+i]) != 'undefined')
        {
            if (f.elements["it_opt"+i].value == '선택') {
                alert(f.elements["it_opt"+i+"_subject"].value + '을(를) 선택하여 주십시오.');
                f.elements["it_opt"+i].focus();
                return;
            }
        }
    }

    if (act == "direct_buy") {
        f.sw_direct.value = 1;
    } else {
        f.sw_direct.value = 0;
    }

    if (!f.ct_qty.value) {
        alert("수량을 입력해 주십시오.");
        f.ct_qty.focus();
        return;
    } else if (isNaN(f.ct_qty.value)) {
        alert("수량을 숫자로 입력해 주십시오.");
        f.ct_qty.select();
        f.ct_qty.focus();
        return;
    } else if (parseInt(f.ct_qty.value) < 1) {
        alert("수량은 1 이상 입력해 주십시오.");
        f.ct_qty.focus();
        return;
    }

	// 김선용 201208 :
	if("<?=$it['it_order_onetime_limit_cnt']?>" != '0'){
		if(parseInt(f.ct_qty.value) > parseInt(<?=$it['it_order_onetime_limit_cnt']?>)){
			alert("이 상품은 1회 최대구매수량이 \'<?=$it['it_order_onetime_limit_cnt']?> 개\' 입니다.");
			return;
		}
	}

    amount_change();
    f.submit();
}

function addition_write(element_id)
{
    if (element_id.style.display == 'none') { // 안보이면 보이게 하고
        element_id.style.display = 'block';
    } else { // 보이면 안보이게 하고
        element_id.style.display = 'none';
    }
}


var save_use_id = null;
function use_menu(id)
{
    if (save_use_id != null)
        document.getElementById(save_use_id).style.display = "none";
    menu(id);
    save_use_id = id;
}

var save_qa_id = null;
function qa_menu(id)
{
    if (save_qa_id != null)
        document.getElementById(save_qa_id).style.display = "none";
    menu(id);
    save_qa_id = id;
}

if (document.getElementById("item_use_count"))
    document.getElementById("item_use_count").innerHTML = "<?=$use_total_count?>";
<?if(!preg_match("/okflex\.com/", $_SERVER['HTTP_HOST'])){ // 김선용 201107 : OKFLEX.COM 일때 미출력?>
if (document.getElementById("item_qa_count"))
    document.getElementById("item_qa_count").innerHTML = "<?=$qa_total_count?>";
<?}?>
if (document.getElementById("item_relation_count"))
    document.getElementById("item_relation_count").innerHTML = "<?=$item_relation_count?>";

// 상품상세설명에 있는 이미지의 사이즈를 줄임
function explan_resize_image()
{
    var image_width = 600;
    var div_explan = document.getElementById('div_explan');
    if (div_explan) {
        var explan_img = div_explan.getElementsByTagName('img');
        for(i=0;i<explan_img.length;i++)
        {
            //document.write(explan_img[i].src+"<br>");
            img = explan_img[i];
            imgx = parseInt(img.style.width);
            imgy = parseInt(img.style.height);
            if (imgx > image_width)
            {
                image_height = parseFloat(imgx / imgy)
                img.style.width = image_width;
                img.style.height = parseInt(image_width / image_height);
            }
        }
    }
}
<? if ($it[it_explan]) { echo "explan_resize_image();"; } // onLoad 할때 실행 ?>
</script>


<!-- // 김선용 200908 : // 김선용 201206 :-->
<script type="text/javascript" src="<?=$g4[path]?>/js/jquery.kcaptcha.js"></script>
<script type="text/javascript">
$(function() {
    $("#kcaptcha_image_use, #kcaptcha_image_qa").bind("click", function() {
        $.ajax({
            type: 'POST',
            url: g4_path+'/'+g4_bbs+'/kcaptcha_session.php',
            cache: false,
            async: false,
            success: function(text) {
                $("#kcaptcha_image_use, #kcaptcha_image_qa").attr('src', g4_path+'/'+g4_bbs+'/kcaptcha_image.php?t=' + (new Date).getTime());
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


<?
// 하단 HTML
echo stripslashes($it[it_tail_html]);

$timg = "$g4[path]/data/item/{$it_id}_t";
if (file_exists($timg))
    echo "<img src='$timg' border=0><br>";

if ($ca[ca_include_tail])
    @include_once($ca[ca_include_tail]);
else
    include_once("./_tail.php");
?>

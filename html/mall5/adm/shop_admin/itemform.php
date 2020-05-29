<?
$sub_menu = "300100";
include_once("./_common.php");

# 신분류 selectbox로 카테고리 선택시 자식 카테고리 출력  2014-07-10 홍민기 #
if($_POST['mode'] == 'child_cate_search_ajax'){

	if($_POST['value'] == ''){
		exit;
	}
	$result	= '';
	
	$sql = sql_query("
		select ca_id,ca_name from ".$g4['yc4_category_table']." where ca_id like '".$_POST['value']."%' and length(ca_id) > '".strlen($_POST['value'])."'
	");
	
	while($row = sql_fetch_array($sql)){
		$result .= "
			<option value='".$row['ca_id']."'>".$row['ca_name']."</option>
		";
	}
	
	if($result){
		$result = "
			<select  class='new_cate' depth='".((int)$_POST['depth']+1)."' onchange='category_change_new(this);'><option value=''>선택</option>".$result."</select>
		";
	}
	echo $result;

	exit;
}


# 신분류 카테고리 검색 처리 2014-07-10 홍민기 #
if($_POST['mode'] == 'cate_search'){
	if(!$_POST['key']){
		exit;
	}

	$sql = sql_query("
		select ca_id,ca_name from ".$g4['yc4_category_table']." where ".$_POST['key']." like '".trim($_POST['value'])."%' order by ca_id asc
	");


	while($row = sql_fetch_array($sql)){
		# 상위 카테고리명 로드 #
		$depth = strlen($row['ca_name'])/2;
		$paernt_cate = '';

		for($i=1; $i<$depth; $i++){
			$tmp = $i*2;
			$paernt_cateQ = sql_fetch("
				select
					ca_id,ca_name
				from
					".$g4['yc4_category_table']."
				where
					ca_id like '".substr($row['ca_id'],0,$tmp)."%'
					and
					length(ca_id) < '".strlen($row['ca_id'])."'
					".(($ca_id_result) ? "and ca_id not in (".$ca_id_result.")":"")."
			");
			if($paernt_cateQ){
				$ca_id_result .= (($ca_id_result) ? ',':'')."'".$paernt_cateQ['ca_id']."'";

				$paernt_cate .= (($paernt_cate) ? ' > ':'').$paernt_cateQ['ca_name'];

			}

			unset($paernt_cateQ);
		}
		if($paernt_cate) $paernt_cate .= ' > ' .$row['ca_name'];
		echo "<li>".$paernt_cate." <input type='hidden' class='search_ca' value='".$row['ca_id']."'/><input type='button' value='추가' onclick=\"add_category2(this); return false;\"/></li>\n";

	}

	exit;
}

include_once ("$g4[path]/lib/cheditor4.lib.php");
auth_check($auth[$sub_menu], "w");




$html_title = "상품 ";

if ($w == "")
{
    $html_title .= "입력";

    // 옵션은 쿠키에 저장된 값을 보여줌. 다음 입력을 위한것임
    //$it[ca_id] = _COOKIE[ck_ca_id];
    $it[ca_id] = get_cookie("ck_ca_id");
    $it[ca_id2] = get_cookie("ck_ca_id2");
    $it[ca_id3] = get_cookie("ck_ca_id3");
    if (!$it[ca_id])
    {
        $sql = " select ca_id from $g4[yc4_category_table] order by ca_id limit 1 ";
        $row = sql_fetch($sql);
        if (!$row[ca_id])
            alert("등록된 분류가 없습니다. 우선 분류를 등록하여 주십시오.");
        $it[ca_id] = $row[ca_id];
    }
    //$it[it_maker]  = stripslashes($_COOKIE[ck_maker]);
    //$it[it_origin] = stripslashes($_COOKIE[ck_origin]);
    $it[it_maker]  = stripslashes(get_cookie("ck_maker"));
    $it[it_origin] = stripslashes(get_cookie("ck_origin"));
}
else if ($w == "u")
{
    $html_title .= "수정";

    /*
	if ($is_admin != 'super')
    {
        $sql = " select it_id from $g4[yc4_item_table] a, $g4[yc4_category_table] b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '$member[mb_id]' ";
        $row = sql_fetch($sql);
        if (!$row[it_id])
            alert("\'{$member[mb_id]}\' 님께서 수정 할 권한이 없는 상품입니다.");
    }
	*/

    $sql = " select * from $g4[yc4_item_table] where it_id = '$it_id' ";
    $it = sql_fetch($sql);


    $cps_it = sql_fetch("select use_yn, cps_ca_name,cps_ca_name2,cps_ca_name3,cps_ca_name4 from yc4_cps_item where it_id = '$it_id'");



	if (!$ca_id){
		$ca_id = $it[ca_id];
	}


    $sql = " select * from $g4[yc4_category_table] where ca_id = '$ca_id' ";
    $ca = sql_fetch($sql);
}
else
{
	alert();
}

if (!$it[it_explan_html])
{
    $it[it_explan] = get_text($it[it_explan], 1);
}

//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
//$qstr = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
$qstr  = "$qstr&sca=$sca&page=$page";

// 김선용 201208 :
if($return_url != ''){
	$qstr = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search);
	$qstr .= "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
	$return_url = "$return_url.php";
}
else
	$return_url = "itemlist.php";

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<script src="<?=$g4[path]?>/cheditor5/cheditor.js"></script>
<?=cheditor1('it_explan', '100%', '200');?>
<?=cheditor1('it_head_html', '100%', '200');?>
<?=cheditor1('it_tail_html', '100%', '200');?>

<form name=fitemform method=post action="./itemformupdate.php" onsubmit="return fitemformcheck(this)" enctype="MULTIPART/FORM-DATA" autocomplete="off" style="margin:0px;">
<?=subtitle("기본정보")?>
<table width=100% cellpadding=0 cellspacing=0 border=0>
<input type=hidden name=codedup     value="<?=$default[de_code_dup_use]?>">
<input type=hidden name=w           value="<?=$w?>">
<!-- <input type=hidden name=sel_ca_id   value="<?=$sel_ca_id?>">
<input type=hidden name=sel_field   value="<?=$sel_field?>">
<input type=hidden name=search      value="<?=$search?>">
<input type=hidden name=sort1       value="<?=$sort1?>">
<input type=hidden name=sort2       value="<?=$sort2?>"> -->
<input type=hidden name=sca  value="<?=$sca?>">
<input type=hidden name=sst  value="<?=$sst?>">
<input type=hidden name=sod  value="<?=$sod?>">
<input type=hidden name=sfl  value="<?=$sfl?>">
<input type=hidden name=stx  value="<?=$stx?>">
<input type=hidden name=page value="<?=$page?>">
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=0E87F9></td></tr>
<tr class=ht>
	<td>상품코드</td>
	<td colspan=3>

	<? if ($w == "") { // 추가 ?>
		<!-- 최근에 입력한 코드(자동 생성시)가 목록의 상단에 출력되게 하려면 아래의 코드로 대체하십시오. -->
		<!-- <input type=text class=ed name=it_id value="<?=10000000000-time()?>" size=12 maxlength=10 required nospace alphanumeric itemname="상품코드"> <a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a> -->
		<input type=text class=ed name=it_id value="<?=time()?>" size=12 maxlength=10 required nospace alphanumeric itemname="상품코드">
        <? if ($default[de_code_dup_use]) { ?><a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a><? } ?>
        <?=help("상품의 코드는 10자리 숫자로 자동생성합니다.\n운영자 임의로 상품코드를 입력하실 수 있습니다.\n상품코드는 영문자와 숫자만 입력 가능합니다.");?>
	<? } else { ?>
		<input type=hidden name=it_id value="<?=$it[it_id]?>">
		<?=$it[it_id]?>
		<?=icon("보기", "{$g4[shop_path]}/item.php?it_id=$it_id");?>
        <a href='./itempslist.php?sel_field=a.it_id&search=<?=$it_id?>'>사용후기</a>
        <a href='./itemqalist.php?sel_field=a.it_id&search=<?=$it_id?>'>상품문의</a>
	<? } ?>

	</td>
</tr>
<tr class='ht'>
	<td>신분류</td>
	<td colspan='3'>
		<div><b>※신분류 설정은 검색과 선택 모두 가능합니다.</b></div>
		<br />
		<div>카테고리 검색</div>
		<div>
			<select class="cate_key">
				<option value="ca_name">카테고리명</option>
				<option value="ca_id">카테고리코드</option>
			</select>
			<input type="text" class='cate_value'/><input type="button" value=' 검 색 ' onclick="cate_search();return false;"/>
		</div>
		<div class='cate_search_wrap'></div>

		<br /><br />
		<hr />
		<br /><br />

		<div>카테고리 선택</div>
		<div style='display:inline-block;'>
			<select class="new_cate" depth='1' onchange="category_change_new(this);">
				<option value="">선택</option>
				<?
					$ca_sql = sql_query("select ca_id,ca_name from ".$g4['yc4_category_table']." where length(ca_id) = 2 order by ca_id asc");
					while($ca_option = sql_fetch_array($ca_sql)){
						echo "<option value='".$ca_option['ca_id']."'>".$ca_option['ca_name']."</option>";
					}
				?>
			</select>
		</div>
		<div style='display:inline-block;'><input type='button' value='추가' onclick="add_category();return false;";></a></div>

		<br /><br />
		<hr />
		<br /><br />

		<div><b>현재 설정된 카테고리</b></div>
		<ul class='selected_cate'>
			<?
				$ca_sql = sql_query("
					select
						c.ca_id, c.ca_name
					from
						".$g4['yc4_item_table']." a
						left join
						yc4_category_item b on a.it_id = b.it_id
						left join
						".$g4['yc4_category_table']." c on b.ca_id = c.ca_id
					where
						a.it_id = '".$it['it_id']."'
					order by c.ca_id asc
				");

				while($ca_list = sql_fetch_array($ca_sql)){
					$depth = strlen($ca_list['ca_id'])/2;
					echo "<li>";

					# 상위 뎁스의 카테고리들을 찾는다.
					for($i=1; $i<$depth; $i++){
						$substr = $i*2;
						$top_ca_id = substr($ca_list['ca_id'],0,$substr);
						$top_ca_name = sql_fetch("select ca_name from ".$g4['yc4_category_table']." where ca_id = '".$top_ca_id."'");
						$top_ca_name = $top_ca_name['ca_name'];
						echo $top_ca_name.' > ';

					}
					echo $ca_list['ca_name'];
					echo "<input type='hidden' name='new_cate[]' value='".$ca_list['ca_id']."'>";
					echo "<input type='button' value=' X ' onclick=\"category_del(this);\"></li>";
				}
			?>
		</ul>
		<br /><br />
	</td>
</tr>
<tr class=ht style="display:none;">
    <td>분류명(구분류)</td>
    <td colspan=3 style="padding:3px 0 3px 0;">
        <select name="ca_id" onchange="categorychange(this.form)">
            <option value="">= 기본분류 =
            <?
            $script = "";
            $sql = " select * from yc4_category ";
            if ($is_admin != 'super')
                $sql .= " where ca_mb_id = '$member[mb_id]' ";
            $sql .= " order by ca_id ";
            $result = sql_query($sql);
            for ($i=0; $row=sql_fetch_array($result); $i++)
            {
                $len = strlen($row[ca_id]) / 2 - 1;

                $nbsp = "";
                for ($i=0; $i<$len; $i++)
                    $nbsp .= "&nbsp;&nbsp;&nbsp;";

                $str = "<option value='$row[ca_id]'>$nbsp$row[ca_name]\n";
                $category_select .= $str;
                echo $str;

                $script .= "ca_use['$row[ca_id]'] = $row[ca_use];\n";
                $script .= "ca_stock_qty['$row[ca_id]'] = $row[ca_stock_qty];\n";
                //$script .= "ca_explan_html['$row[ca_id]'] = $row[ca_explan_html];\n";
                $script .= "ca_sell_email['$row[ca_id]'] = '$row[ca_sell_email]';\n";
                $script .= "ca_opt1_subject['$row[ca_id]'] = '$row[ca_opt1_subject]';\n";
                $script .= "ca_opt2_subject['$row[ca_id]'] = '$row[ca_opt2_subject]';\n";
                $script .= "ca_opt3_subject['$row[ca_id]'] = '$row[ca_opt3_subject]';\n";
                $script .= "ca_opt4_subject['$row[ca_id]'] = '$row[ca_opt4_subject]';\n";
                $script .= "ca_opt5_subject['$row[ca_id]'] = '$row[ca_opt5_subject]';\n";
                $script .= "ca_opt6_subject['$row[ca_id]'] = '$row[ca_opt6_subject]';\n";
            }
            ?>
        </select>
        <script> document.fitemform.ca_id.value = '<?=$it[ca_id]?>'; </script>
        <script>
            var ca_use = new Array();
            var ca_stock_qty = new Array();
            //var ca_explan_html = new Array();
            var ca_sell_email = new Array();
            var ca_opt1_subject = new Array();
            var ca_opt2_subject = new Array();
            var ca_opt3_subject = new Array();
            var ca_opt4_subject = new Array();
            var ca_opt5_subject = new Array();
            var ca_opt6_subject = new Array();
            <?="\n$script"?>
        </script>

        <? if ($w == "") { ?>
            <?=help("기본분류를 선택하면 선택한 분류의 기본값인 판매, 재고, HTML사용, 판매자 E-mail 을 기본값으로 설정합니다.");?>
        <? } ?>

        <?
        for ($i=2; $i<4; $i++)
        {
            echo "&nbsp; <select name='ca_id{$i}'><option value=''>= {$i}차 분류 ={$category_select}</select>\n";
            echo "<script> document.fitemform.ca_id{$i}.value = '".$it["ca_id{$i}"]."'; </script>\n";
        }
		echo "<p style='margin:2px 0;'></p>";
        for ($i=4; $i<6; $i++)
        {
            echo "<select name='ca_id{$i}'><option value=''>= {$i}차 분류 ={$category_select}</select>\n";
            echo "<script> document.fitemform.ca_id{$i}.value = '".$it["ca_id{$i}"]."'; </script>\n&nbsp;&nbsp;";
        }
        ?>
    </td>
</tr>


<tr class=ht>
    <td>분류명(구분류)</td>
    <td colspan=3 style="padding:3px 0 3px 0;">
<?php
	if ($is_admin != 'super') $old_cate_where .= " and ca_mb_id = '$member[mb_id]' ";

	for($k=0;$k<5;$k++){

		$target_ca_id = ($k<1) ? $it['ca_id'] : $it['ca_id'.($k+1)];
		$rs = sql_query("select * from yc4_category where ca_id='$target_ca_id' $old_cate_where");
		if($rs){
			$old_cate_info = sql_fetch_array($rs);
			if($old_cate_info['ca_name']) {
				$ca_id_length_work = 2;
				$ca_id_length = strlen($old_cate_info['ca_id']);
				while($ca_id_length - $ca_id_length_work > 0){

					$up_ca_info = sql_fetch_array(sql_query("select ca_id,ca_name from yc4_category where ca_id='".substr($old_cate_info['ca_id'],0,$ca_id_length_work)."' $old_cate_where"));

					$up_ca_info_txt .= "$up_ca_info[ca_name] > ";

					$ca_id_length_work += 2;
				}

				if($k > 0) echo '<br/>';

				echo ($k+1)."차 분류 : $up_ca_info_txt $old_cate_info[ca_name]";

				unset($up_ca_info_txt);
			}
		}
	}
?>
    </td>
</tr>

<tr class=ht>
    <td>상품명</td>
    <td colspan=3>
        <input type=text name=it_name value='<?=get_text(stripslashes($it[it_name]))?>' style='width:97%;' required itemname='상품명' class=ed <?php echo !is_admin($member['mb_id'] ? "readonly":"")?>>
    </td>
</tr>


<!-- // 김선용 201306 : seo -->
<tr class=ht>
    <td>META 타이틀</td>
    <td colspan=3>
		<textarea name=it_meta_title style='width:95%;' itemname='타이틀KR' class=ed><?=$it['it_meta_title']?></textarea>
    </td>
</tr>
<tr class=ht>
	<td>META 키워드</td>
	<td colspan=3>
		<textarea name=it_meta_keyword style='width:95%;' itemname='메타-키워드KR' class=ed><?=$it['it_meta_keyword']?></textarea>
	</td>
</tr>
<tr class=ht>
	<td>META H1</td>
	<td colspan=3>
		<textarea name=it_meta_h1 style='width:95%;' itemname='아이템-H1EN' class=ed><?=$it['it_meta_h1']?></textarea>
	</td>
</tr>
<tr class=ht>
    <td>META 설명</td>
    <td colspan=3>
		<textarea name=it_meta_description style='width:95%;' itemname='메타-설명KR' class=ed><?=$it['it_meta_description']?></textarea>
    </td>
</tr>



<!-- 김선용 200812 -->
<tr class=ht>
    <td>바코드입력(SKU)</td>
    <td>
        <input type=text name=SKU value='<?=$it['SKU']?>' size=20 class=ed>
    </td>
    <td>병수량</td>
    <td>
        <input type=text name="it_health_cnt" value='<?=$it['it_health_cnt']?>' size=4 class=ed> 병
    </td>
</tr>

<tr class=ht>
    <td>출력유형</td>
    <td>
        <input type=checkbox name=it_gallery value='1' <?=($it[it_gallery] ? "checked" : "")?>> 갤러리로 사용
        <?=help("금액표시는 하지 않고 상품을 구매할 수 없으며 상품설명만 나타낼때 사용합니다.");?>
    </td>
    <td>출력순서</td>
    <td>
        <input type=text class=ed name=it_order size=10 value='<? echo $it[it_order] ?>'>
        <?=help("상품의 출력순서를 인위적으로 변경할때 사용합니다.\n숫자를 입력하며 기본은 0 입니다.\n숫자가 작을 수록 상위에 출력됩니다.\n음수 입력도 가능합니다.\n구간 :  -2147483648 ~ 2147483647");?>
    </td>
</tr>
<tr class=ht>
    <td>상품유형</td>
    <td colspan=3>
        <input type=checkbox name=it_type1 value='1' <?=($it[it_type1] ? "checked" : "");?>><img src='<?="$g4[url]/$g4[shop]/img/icon_type1.gif"?>' align=absmiddle>
        <input type=checkbox name=it_type2 value='1' <?=($it[it_type2] ? "checked" : "");?>><img src='<?="$g4[url]/$g4[shop]/img/icon_type2.gif"?>' align=absmiddle>
        <input type=checkbox name=it_type3 value='1' <?=($it[it_type3] ? "checked" : "");?>><img src='<?="$g4[url]/$g4[shop]/img/icon_type3.gif"?>' align=absmiddle>
        <input type=checkbox name=it_type4 value='1' <?=($it[it_type4] ? "checked" : "");?>><img src='<?="$g4[url]/$g4[shop]/img/icon_type4.gif"?>' align=absmiddle>
        <input type=checkbox name=it_type5 value='1' <?=($it[it_type5] ? "checked" : "");?>><img src='<?="$g4[url]/$g4[shop]/img/icon_type5.gif"?>' align=absmiddle>
        <?=help("메인화면에 유형별로 출력할때 사용합니다.\n\n이곳에 체크하게되면 상품리스트에서 유형별로 정렬할때 체크된 상품이 가장 먼저 출력됩니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>제조사</td>
    <td>
        <input type=text class=ed name=it_maker value='<?=get_text(stripslashes($it[it_maker]))?>' size=41>
        <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
    </td>
    <td>원산지</td>
    <td>
        <input type=text class=ed name=it_origin value='<?=get_text(stripslashes($it[it_origin]))?>' size=41>
        <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
    </td>
</tr>
<?
for ($i=1; $i<=3; $i++) {
    $k1=$i*2-1;
    $k2=$i*2;
    $val11 = stripslashes($it["it_opt".$k1."_subject"]);
    $val12 = stripslashes($it["it_opt".$k1]);
    $val21 = stripslashes($it["it_opt".$k2."_subject"]);
    $val22 = stripslashes($it["it_opt".$k2]);

    echo "
    <tr class=ht>
        <td><input type=text name='it_opt{$k1}_subject' size=15 class=ed value='".get_text($val11)."'></td>
        <td><textarea name='it_opt{$k1}' rows='3' cols=40 class=ed>$val12</textarea></td>
        <td><input type=text name='it_opt{$k2}_subject' size=15 class=ed value='".get_text($val21)."'></td>
        <td><textarea name='it_opt{$k2}' rows='3' cols=40 class=ed>$val22</textarea></td>
    </tr>\n";
}
?>
<tr>
    <td height=80>가격/포인트/재고</td>
    <td colspan=3>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
        	<td width=16%>비회원가격 <?=help("상품의 기본판매가격(로그인 이전 가격)이며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.", 50);?></td>
        	<td width=16%>회원가격 <?=help("상품의 로그인 이후 가격(회원 권한 2 에만 적용)이며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.\n\n입력이 없다면 비회원가격으로 대신합니다.", 50);?></td>
        	<td width=16%>특별회원가격 <?=help("상품의 로그인 이후 가격(회원 권한 3 이상에 적용)이며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.\n\n입력이 없다면 회원가격으로 대신합니다.\n회원가격도 없다면 비회원가격으로 대신합니다.", 50);?></td>
        	<td width=16%>시중가격 <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.", 50);?></td>
        	<td width=16%>포인트 <?=help("주문완료후 환경설정에서 설정한 주문완료 설정일 후 회원에게 부여하는 포인트입니다.\n포인트를 사용하지 않는다면 의미가 없습니다.\n또, 포인트부여를 '아니오'로 설정한 경우 신용카드, 계좌이체로 주문하는 회원께는 부여하지 않습니다.", -150);?></td>
        	<td width=16%>재고수량 <?=help("<span style='width:500px'>재고는 규격, 색상별로 관리되지는 않으며 상품별로 관리됩니다.\n이곳에 100개를 설정하고 상품 10개가 주문,준비,배송,완료 상태에 있다면 현재고는 90개로 나타내어집니다.\n주문관리에서 상품별로 상태가 변경될때 재고를 가감하게 됩니다.</span>", -450, -120);?></td>
        </tr>
        <tr>
            <!-- 비회원가 대비 회원가격은 90%, 특별회원가격은 75%로 자동 설정할 경우의 코드
            <td><input type=text class=ed name=it_amount size=8 value='<?=$it[it_amount]?>' style='text-align:right; background-color:#DDE6FE;' onblur="document.fitemform.it_amount2.value=document.fitemform.it_amount.value*.9;document.fitemform.it_amount3.value=document.fitemform.it_amount.value*.75;"></td>
            -->
            <td><input type=text class=ed name=it_amount size=8 value='<?=$it[it_amount]?>' style='text-align:right; background-color:#DDE6FE;' readonly></td>
            <td><input type=text class=ed name=it_amount2 size=8 value='<?=$it[it_amount2]?>' style='text-align:right; background-color:#DDFEDE;'></td>
            <td><input type=text class=ed name=it_amount3 size=8 value='<?=$it[it_amount3]?>' style='text-align:right; background-color:#FEDDDD;'></td>
            <td><input type=text class=ed name=it_cust_amount size=8 value='<?=$it[it_cust_amount]?>' style='text-align:right;' readonly></td>
            <td><input type=text class=ed name=it_point size=8 value='<? echo $it[it_point] ?>' style='text-align:right;'> 점</td>
            <td><input type=text class=ed name=it_stock_qty size=8 value='<? echo $it[it_stock_qty] ?>' style='text-align:right;'> 개
			<!-- // 김선용 201211 : -->
			<input type="hidden" name="it_stock_qty_old" value="<?=$it['it_stock_qty']?>" />
			</td>
		</tr>
		<tr>
        	<td width=16%>아이해피가격 <?=help("아이해피 상품 리슽, 상세페이지에 출력합니다.", 50);?></td>
			<td width=16%>제품 달러가격 <?=help("아이해피 상품 리슽, 상세페이지에 출력합니다.", 50);?></td>
			<td width=16%>시중가격(달러) <?=help("아이해피 상품 리슽, 상세페이지에 출력합니다.", 50);?></td>
			<td colspan="3"></td>
		</tr>
		<tr>
			<td><input type=text class=ed name=ihappy_amount size=8 value='<?=$it[ihappy_amount]?>' style='text-align:right; background-color:#DDE6FE;'></td>
			<td><input type=text class=ed name=it_amount_usd size=8 value='<?=$it[it_amount_usd]?>' style='text-align:right; background-color:#DDE6FE;'></td>
			<td><input type=text class=ed name=it_cust_amount_usd size=8 value='<?=$it[it_cust_amount_usd]?>' style='text-align:right; background-color:#DDE6FE;'></td>

			<td colspan="4"></td>
		</tr>
        </table>
    </td>
</tr>
<!-- // 김선용 201208 : -->
<tr class=ht>
    <td>1회구매수량</td>
    <td>
        <input type=text class=ed name=it_order_onetime_limit_cnt size=4 value='<?=$it['it_order_onetime_limit_cnt']?>'>
        <?=help("1회구매가능한 최대수량을 설정합니다.", 50);?>
    </td>
	<!-- // 김선용 201211 : -->
	<td>단종</td>
	<td>
        <label><input type=checkbox name=it_discontinued value='1' <?if($it['it_discontinued']) echo 'checked';?> />단종처리</label> ※ 단종처리되면 재고가 변경돼도 sms통보를 하지 않습니다.
	</td>
</tr>
<tr class=ht>
    <td>기본설명</td>
    <td colspan=3>
        <input type=text class=ed name=it_basic style='width:97%;' value='<?=get_text($it[it_basic])?>'>
        <?=help("상품상세페이지의 상품설명 상단에 표시되는 설명입니다.\nHTML 입력도 가능합니다.", -150, -100);?>
    </td>
</tr>
<input type=hidden name=it_explan_html value=1>
<tr>
    <td>상품설명</td>
    <td colspan=3 style='padding-top:7px; padding-bottom:7px;'><?=cheditor2('fitemform', 'it_explan', $it['it_explan']);?></td>
</tr>
<tr class=ht>
    <td>판매자 e-mail</td>
    <td colspan=3>
        <input type=text class=ed name=it_sell_email size=40 value='<? echo $it[it_sell_email] ?>'>
        <?=help("운영자와 판매자가 다른 경우 이곳에 판매자의 e-mail을 입력해 놓으면 이 상품이 주문되는 시점에서 판매자에게 별도의 주문서 메일을 발송합니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>전화문의</td>
    <td>
        <input type=checkbox name='it_tel_inq' <? echo ($it[it_tel_inq]) ? "checked" : ""; ?> value='1'> 예
        <?=help("상품 금액 대신 전화문의로 표시됩니다.");?>
    </td>
    <td>판매가능</td>
    <td>
        <input type=checkbox name='it_use' <? echo ($it[it_use]) ? "checked" : ""; ?> value='1'> 예
        <?=help("잠시 판매를 중단하거나 재고가 없을 경우에 체크하면 이 상품은 출력하지 않으며 주문도 할 수 없습니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>CPS 사용여부</td>
    <td>
        <input type=checkbox name='cps_use_yn' <? echo ($cps_it[use_yn]=="y") ? "checked" : ""; ?> value='y'> 예
        <?=help("네이버 CPS 사용여부입니다.");?>
    </td>
    <td colspan=3>
        <table width=100% cellpadding=0 cellspacing=0>
            <tr>
                <td width="16%">CPS용 카테고리1</td>
                <td width="16%">CPS용 카테고리2</td>
                <td width="16%">CPS용 카테고리3</td>
                <td width="16%">CPS용 카테고리4</td>
            </tr>
            <tr>
                <td><input type=text class=ed name="cps_ca_name" value='<?=$cps_it[cps_ca_name]?>'></td>
                <td><input type=text class=ed name="cps_ca_name2" value='<?=$cps_it[cps_ca_name2]?>'></td>
                <td><input type=text class=ed name="cps_ca_name3" value='<?=$cps_it[cps_ca_name3]?>'></td>
                <td><input type=text class=ed name="cps_ca_name4" value='<?=$cps_it[cps_ca_name4]?>'></td>
            </tr>
        </table>
    </td>
</tr>

<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p>
</p>
<?php
/* // 이미지 관련 액션 삭제 2017-03-08 강경인
echo subtitle("이미지"); ?>
<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=0E87F9></td></tr>
<tr>
    <td>이미지(대)</td>
    <td colspan=3>
        <input type=file class=ed name=it_limg1 size=40>
        <?
        $limg1 = "$g4[path]/data/item/{$it[it_id]}_l1";
        if (file_exists($limg1)) {
            $size = getimagesize($limg1);
            echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('limg1', $size[0], $size[1]);\"><input type=checkbox name=it_limg1_del value='1'>삭제";
            echo "<div id='limg1' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$limg1' border=1></div>";
        }
        ?>

        <?
        if (function_exists("imagecreatefromjpeg")) {
            //echo "<input type=checkbox name=createimage value='1'> <FONT COLOR=FF6600>이미지(중), 이미지(소)를 자동생성 하시려면 체크하세요. JPG 파일만 가능합니다.</FONT> ";
            echo "<br><input type=checkbox name=createimage value='1'> 중, 소 이미지를 자동으로 생성하시는 경우에 체크하세요. (JPG 파일만 가능)";
            echo help("이미지(중) 이미지(소) 의 사이즈를 환경설정에서 정한 폭과 높이로 자동생성합니다.\n\nJPG 파일만 가능합니다.");
        }
        ?>
    </td>
</tr>
<tr class=ht>
    <td>이미지(중)</td>
    <td colspan=3>
        <input type=file class=ed name=it_mimg size=40>
        <?
        $mimg = "$g4[path]/data/item/{$it[it_id]}_m";
        if (file_exists($mimg)) {
            $size = getimagesize($mimg);
            echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('mimg', $size[0], $size[1]);\"><input type=checkbox name=it_mimg_del value='1'>삭제";
            echo "<div id='mimg' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$mimg' border=1></div>";
        }
        ?>
        &nbsp;<?=help("업로드 하지 않으면 기본 noimage 로 출력합니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>이미지(소)</td>
    <td colspan=3>
        <input type=file class=ed name=it_simg size=40>
        <?
        $simg = "$g4[path]/data/item/{$it[it_id]}_s";
        if (file_exists($simg)) {
            $size = getimagesize($simg);
            echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('simg', $size[0], $size[1]);\"><input type=checkbox name=it_simg_del value='1'>삭제";
            echo "<div id='simg' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$simg' border=1></div>";
        }
        ?>
        &nbsp;<?=help("업로드 하지 않으면 기본 noimage 로 출력합니다.");?>
    </td>
</tr>

<? for ($i=2; $i<=5; $i++) { // 이미지(대)는 5개 ?>
<tr class=ht>
    <td>이미지(대) <?=$i?></td>
    <td colspan=3>
        <input type=file class=ed name=it_limg<?=$i?> size=40>
        <?
        $limg = "$g4[path]/data/item/{$it[it_id]}_l{$i}";
        if (file_exists($limg)) {
            $size = getimagesize($limg);
            echo "<img src='$g4[admin_path]/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('limg$i', $size[0], $size[1]);\"><input type=checkbox name=it_limg{$i}_del value='1'>삭제";
            echo "<span id=limg{$i} style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='$limg' border=1></div>";
        }
        ?>
    </td>
</tr>
<? } ?>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>
*/ ?>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='<?=$return_url?>?<?=$qstr?>';">
<p>

<?=subtitle("선택정보")?>
<table width=100% cellpadding=0 cellspacing=0 border=0>
<colgroup width=14%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=3 bgcolor=#FFFFFF></colgroup>
<colgroup width=13%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=5 height=2 bgcolor=0E87F9></td></tr>
<tr>
	<td colspan=5>
		<table width=100% cellpadding=0 cellspacing=0>
			<tr class=ht align=center>
				<td width=50%><b>선택된 목록</b></td>
                <td width=50%><b>등록된 목록</b></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
    <td align="center">
        선택된 관련상품
        <?=help("오른쪽 등록된 목록의 상품목록에서 더블클릭하면 선택된 관련상품에 추가됩니다.\n만약, 이 상품이 a 이고 b 라는 상품을 관련상품으로 등록하면 b 라는 상품에도 a 라는 상품을 관련상품으로 자동 등록합니다.\n반드시 아래의 확인버튼을 클릭하셔야 정상 등록되므로 이점 유의하여 주십시오", -100);?><br><span id="sel_span" style="line-height:200%"></span>
    </td>
    <td>
        ※ 상품 선택후 <FONT COLOR="#FF6600">더블클릭하면 삭제됨</FONT><br>※ 한 번 클릭시 상품이미지/상품금액 출력<br>
        <br>
        <select name='relationselect' size=8 style='width:250px;' onclick="relation_img(this.value, 'sel_span')" ondblclick="relation_del(this);">
        <?
        $str = array();
        $sql = " select b.ca_id, b.it_id, b.it_name, b.it_amount
                   from $g4[yc4_item_relation_table] a
                   left join $g4[yc4_item_table] b on (a.it_id2=b.it_id)
                  where a.it_id = '$it_id'
                  order by b.ca_id, b.it_name ";
        $result = sql_query($sql);
        while($row=sql_fetch_array($result))
        {
            $sql2 = " select ca_name from $g4[yc4_category_table] where ca_id = '$row[ca_id]' ";
            $row2 = sql_fetch($sql2);

			// 김선용 2006.10
			if(file_exists("{$g4['path']}/data/item/{$row['it_id']}_s"))
				$it_image = "{$row['it_id']}_s";
			else
				$it_image = "";

            echo "<option value='$row[it_id]/$it_image/{$row['it_amount']}'>$row2[ca_name] : ".cut_str(get_text(strip_tags($row[it_name])),30);
            $str[] = $row[it_id];
        }
		$str = implode(",", $str);
        ?>
        </select>
        <input type='hidden' name='it_list' value='<?=$str?>'>
    </td>
	<td rowspan=2 width=20 bgcolor=#FFFFFF>◀</td>
    <td align="center">상품목록<br><span id="add_span" style="line-height:200%"></span></td>
    <td>
        <script>
        function search_relation(fld) {
            if (fld.value) {
                window.open('itemformrelation.php?it_id=<?=$it_id?>&ca_id='+fld.value, 'hiddenframe', '');
            }
        }
        </script>
        ※ 상품 선택후 <FONT COLOR="#0E87F9">더블클릭하면 왼쪽에 추가됨</FONT><br>※ 한 번 클릭시 상품이미지/상품금액 출력<br>
        <select onchange="search_relation(this)">
        <option value=''>분류별 관련상품
        <option value=''>----------------------
        <?
            $sql = " select ca_id, ca_name from $g4[yc4_category_table] where length(ca_id) = 2 order by ca_id ";
            $result = sql_query($sql);
            for ($i=0; $row=sql_fetch_array($result); $i++)  {
                echo "<option value='$row[ca_id]'>$row[ca_name]\n";
            }
        ?>
        </select><br>
        <select  id='relation' size=8 style='width:250px; background-color:#F6F6F6;' onclick="relation_img(this.value, 'add_span')" ondblclick="relation_add(this);">
        <?
        /*
        $sql = " select ca_id, it_id, it_name, it_amount
                   from $g4[yc4_item_table]
                  where it_id <> '$it_id'
                  order by ca_id, it_name ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++)
        {
            $sql2 = " select ca_name from $g4[yc4_category_table] where ca_id = '$row[ca_id]' ";
            $row2 = sql_fetch($sql2);

			// 김선용 2006.10
			if(file_exists("{$g4['path']}/data/item/{$row['it_id']}_s"))
				$it_image = "{$row['it_id']}_s";
			else
				$it_image = "";

            echo "<option value='$row[it_id]/$it_image/{$row['it_amount']}'>$row2[ca_name] : ".cut_str(get_text(strip_tags($row[it_name])),30);
        }
        */
        ?>
        </select>
        <SCRIPT LANGUAGE="JavaScript">

			// 김선용 2006.10
			function relation_img(name, id)
			{
				item_image_dir = "<?=$g4['path']?>/data/item";
				if(!name) return;
				temp = name.split("/");
				if(temp[1] == ''){
					temp[1] = "no_image.gif";
					var item_image_dir = "<?=$g4['shop_img_url']?>";
				}
				view_span = document.getElementById(id);
				item_price = number_format(String(temp[2]));
				view_span.innerHTML = "<img src='"+item_image_dir+"/"+temp[1]+"' width=100 height=80 border=1 style='border-color:#333333; cursor:pointer' onclick=\"popup_window('"+g4_path+"/shop/item.php?it_id="+temp[0]+"', '', '')\" title='새창으로 상품보기' alt='새창으로 상품보기'><br>"+item_price+" 원";
			}

			function relation_add(fld)
            {
                var f = document.fitemform;
                var len = f.relationselect.length;
                var find = false;

                for (i=0; i<len; i++) {
                    if (fld.options[fld.selectedIndex].value == f.relationselect.options[i].value) {
                        find = true;
                        break;
                    }
                }

                // 같은 이벤트를 찾지못하였다면 입력
                if (!find) {
                    f.relationselect.length += 1;
                    f.relationselect.options[len].value = fld.options[fld.selectedIndex].value;
                    f.relationselect.options[len].text  = fld.options[fld.selectedIndex].text;
                }

                relation_hidden();
            }

            function relation_del(fld)
            {
                if (fld.length == 0) {
                    return;
                }

                if (fld.selectedIndex < 0)
                    return;

                for (i=0; i<fld.length; i++) {
                    // 선택된것과 값이 같다면 1을 더한값을 현재것에 복사
                    if (fld.options[i].value == fld.options[fld.selectedIndex].value) {
                        for (k=i; k<fld.length-1; k++) {
                            fld.options[k].value = fld.options[k+1].value;
                            fld.options[k].text  = fld.options[k+1].text;
                        }
                        break;
                    }
                }
                fld.length -= 1;

                relation_hidden();
            }

            // hidden 값을 변경 : 김선용 2006.10 일부수정
            function relation_hidden()
            {
                var f = fitemform;
                //var str = '';
                //var comma = '';
				var str = new Array();
                for (i=0; i<f.relationselect.length; i++) {
                    //str += comma + f.relationselect.options[i].value;
                    //comma = ',';
					temp = f.relationselect.options[i].value.split("/");
					str[i] = temp[0]; // 상품ID 만 저장
                }
                //f.it_list.value = str;
				f.it_list.value = str.join(",");
            }
        </SCRIPT>
    </td>
</tr>

<script> var eventselect = new Array(); </script>
<tr>
    <td>
        선택된 이벤트<br>
        <?=help("오른쪽 등록된 목록의 이벤트목록에서 더블클릭하면 선택된 이벤트에 추가됩니다.\n이벤트는 분류가 다른 상품들을 묶을 수 있는 또다른 방법입니다.\n이벤트목록은 이벤트관리에서 등록한 내용이 나타납니다.\n반드시 아래의 확인버튼을 클릭하셔야 정상 등록되므로 이점 유의하여 주십시오", -100);?>
    </td>
    <td>
        이벤트 선택후 <FONT COLOR="#FF6600">더블클릭하면 삭제됨</FONT><br>
        <select name=eventselect size=6 style='width:250px;' ondblclick="event_del(this);">
        <?
        $str = "";
        $comma = "";
        $sql = " select b.ev_id, b.ev_subject
                   from $g4[yc4_event_item_table] a
                   left join $g4[yc4_event_table] b on (a.ev_id=b.ev_id)
                  where a.it_id = '$it_id'
                  order by b.ev_id desc ";
        $result = sql_query($sql);
        while ($row=sql_fetch_array($result)) {
            echo "<option value='$row[ev_id]'>".get_text($row[ev_subject]);
            $str .= $comma . $row[ev_id];
            $comma = ",";
        }
        ?>
        </select>
        <input type='hidden' name='ev_list' value='<?=$str?>'>
    </td>
    <td>이벤트목록</td>
    <td>
        이벤트 선택후 <FONT COLOR="#0E87F9">더블클릭하면 왼쪽에 추가됨</FONT><br>
        <select size=6 style='width:250px; background-color:#F6F6F6;' ondblclick="event_add(this);">
        <?
        $sql = " select ev_id, ev_subject from $g4[yc4_event_table] order by ev_id desc ";
        $result = sql_query($sql);
        while ($row=sql_fetch_array($result)) {
            echo "<option value='$row[ev_id]'>".get_text($row[ev_subject]);
        }
        ?>
        </select>
        <script>
            function event_add(fld)
            {
                var f = document.fitemform;
                var len = f.eventselect.length;
                var find = false;

                for (i=0; i<len; i++) {
                    if (fld.options[fld.selectedIndex].value == f.eventselect.options[i].value) {
                        find = true;
                        break;
                    }
                }

                // 같은 이벤트를 찾지못하였다면 입력
                if (!find) {
                    f.eventselect.length += 1;
                    f.eventselect.options[len].value = fld.options[fld.selectedIndex].value;
                    f.eventselect.options[len].text  = fld.options[fld.selectedIndex].text;
                }

                event_hidden();
            }

            function event_del(fld)
            {
                if (fld.length == 0) {
                    return;
                }

                if (fld.selectedIndex < 0)
                    return;

                for (i=0; i<fld.length; i++) {
                    // 선택된것과 값이 같다면 1을 더한값을 현재것에 복사
                    if (fld.options[i].value == fld.options[fld.selectedIndex].value) {
                        for (k=i; k<fld.length-1; k++) {
                            fld.options[k].value = fld.options[k+1].value;
                            fld.options[k].text  = fld.options[k+1].text;
                        }
                        break;
                    }
                }
                fld.length -= 1;

                event_hidden();
            }

            // hidden 값을 변경
            function event_hidden()
            {
                var f = fitemform;

                var str = '';
                var comma = '';
                for (i=0; i<f.eventselect.length; i++) {
                    str += comma + f.eventselect.options[i].value;
                    comma = ',';
                }
                f.ev_list.value = str;
            }
        </script>
    </td>
</tr>
</table>

<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#FFFFFF></colgroup>
<tr class=ht>
    <td>상단이미지</td>
    <td colspan=3>
        <input type=file class=ed name=it_himg size=40>
        <?
        $himg_str = "";
        $himg = "$g4[path]/data/item/{$it[it_id]}_h";
        if (file_exists($himg)) {
            echo "<input type=checkbox name=it_himg_del value='1'>삭제";
            $himg_str = "<img src='$himg' border=0>";
        }
        ?>
        <?=help("상품상세설명 페이지 상단에 출력하는 이미지입니다.");?>
    </td>
</tr>
<? if ($himg_str) { echo "<tr><td colspan=4>$himg_str</td></tr>"; } ?>

<tr class=ht>
    <td>하단이미지</td>
    <td colspan=3>
        <input type=file class=ed name=it_timg size=40>
        <?
        $timg_str = "";
        $timg = "$g4[path]/data/item/{$it[it_id]}_t";
        if (file_exists($timg)) {
            echo "<input type=checkbox name=it_timg_del value='1'>삭제";
            $timg_str = "<img src='$timg' border=0>";
        }
        ?>
        <?=help("상품상세설명 페이지 하단에 출력하는 이미지입니다.");?>
    </td>
</tr>
<? if ($timg_str) { echo "<tr><td colspan=4>$timg_str</td></tr>"; } ?>

<tr>
    <td>상품상단내용 <?=help("상품상세설명 페이지 상단에 출력하는 HTML 내용입니다.", -150);?></td>
    <td colspan=3 align=right style='padding-top:7px; padding-bottom:7px;'><?=cheditor2('fitemform', 'it_head_html', $it['it_head_html']);?></td>
</tr>
<tr>
    <td>상품하단내용 <?=help("상품상세설명 페이지 상단에 출력하는 HTML 내용입니다.", -150);?></td>
    <td colspan=3 align=right style='padding-top:7px; padding-bottom:7px;'><?=cheditor2('fitemform', 'it_tail_html', $it['it_tail_html']);?></td>
</tr>

<? if ($w == "u") { ?>
<tr class=ht>
    <td>입력일시</td>
    <td colspan=3>
        <?=$it[it_time]?>
        <?=help("상품을 처음 입력(등록)한 시간입니다.");?>
    </td>
</tr>
<? } ?>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table><br>


<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='<?=$return_url?>?<?=$qstr?>';">
</form>

<script language='javascript'>
var f = document.fitemform;

function codedupcheck(id)
{
    if (!id) {
        alert('상품코드를 입력하십시오.');
        f.it_id.focus();
        return;
    }
    window.open("./codedupcheck.php?it_id="+id+"&frmname=fitemform", "hiddenframe");
}

function fitemformcheck(f)
{
	/*
	// Edit By rsmaker@ntwglobal.com
    if (!f.ca_id.value) {
        alert("기본분류를 선택하십시오.");
        f.ca_id.focus();
        return false;
    }*/

    if (f.w.value == "") {
        if (f.codedup.value == '1') {
            alert("코드 중복검사를 하셔야 합니다.");
            return false;
        }
    }
    <?=cheditor3('it_explan')."\n";?>
    <?=cheditor3('it_head_html')."\n";?>
    <?=cheditor3('it_tail_html')."\n";?>
    return true;
}

function categorychange(f)
{
    var idx = f.ca_id.value;


    if (f.w.value == "" && idx)
    {
        f.it_use.checked = ca_use[idx] ? true : false;
        //f.it_explan_html[ca_explan_html[idx]].checked = true;
        f.it_stock_qty.value = ca_stock_qty[idx];
        f.it_sell_email.value = ca_sell_email[idx];
        f.it_opt1_subject.value = ca_opt1_subject[idx];
        f.it_opt2_subject.value = ca_opt2_subject[idx];
        f.it_opt3_subject.value = ca_opt3_subject[idx];
        f.it_opt4_subject.value = ca_opt4_subject[idx];
        f.it_opt5_subject.value = ca_opt5_subject[idx];
        f.it_opt6_subject.value = ca_opt6_subject[idx];
    }
}

//categorychange(document.fitemform);

document.fitemform.it_name.focus();

function category_change_new( obj ){
	var ca_id = obj.value;
	var depth = $(obj).attr('depth');

	// 선택한 다음 depth의 카테고리 selectbox는 삭제한다
	for(var i=0; i<$('.new_cate').length; i++){
		if($('.new_cate:eq('+i+')').attr('depth') > depth){
			$('.new_cate:eq('+i+')').remove();
		}
	}

	if(ca_id == ''){
		return false;
	}
	$.ajax({
		url : '<?=$_SERVER['PHP_SELF']?>',
		type : 'post',
		data : {
			'mode' : 'child_cate_search_ajax',
			'value' : ca_id,
			'depth' : $(obj).attr('depth')
		},success : function (result){
			if(result == ''){
				return false;
			}

			$(obj).parent().append(result);
		}
	});
}

function add_category(){
	if($('.new_cate:eq(0)').val() == ''){
		alert('카테고리를 선택해 주세요.');
		return false;
	}

	if(!confirm('선택하신 카테고리를 추가하시겠습니까?\n상품 저장시에 적용됩니다.')){
		return false;
	}

	var ca_id = '';
	var ca_name = '';
	for(var i=0; i<$('.new_cate').length; i++){
		if($('.new_cate:eq('+i+')').val() != ''){
			ca_id = $('.new_cate:eq('+i+')').val();

			ca_name += ((i>0) ? ' > ':'')+$('.new_cate:eq('+i+') option[value='+ca_id+']').text();

		}
	}

	if(ca_name == ''){
		return false;
	}

	if($('input[name=new_cate\\[\\]][value='+ca_id+']').length>0){
		alert('이미 선택된 분류입니다.');
		return false;
	}

	$('.selected_cate').append("<li>"+ca_name+"<input type='hidden' name='new_cate[]' value='"+ca_id+"'/><input type='button' value=' X ' onclick=\"category_del(this);\"></li>");

}

function category_del(obj){
	if(!confirm('카테고리 설정을 해제하시겠습니까?\n상품 저장시 반영됩니다.')){
		return false;
	}

	$(obj).parent().remove();
}

function cate_search(){
	if($('.cate_value').val() == ''){
		alert('검색할 카테고리명을 입력해 주세요.');
		$('.cate_value').focus();
		return false;
	}

	$.ajax({
		url : '<?=$_SERVER['PHP_SELF']?>',
		type : 'post',
		data : {
			'mode' : 'cate_search',
			'key' : $('.cate_key').val(),
			'value' : $('.cate_value').val()
		},success : function( result ){
			if(result == ''){
				$('.cate_search_wrap').text('데이터가 존재하지 않습니다.');
				return false;
			}

			$('.cate_search_wrap').html("<ul>"+result+"</ul>");

		}
	});
}

function add_category2( obj ){
	var ca_id = $(obj).parent().find('.search_ca').val();
	var ca_name = $(obj).parent().text();

	if($('input[name=new_cate\\[\\]][value='+ca_id+']').length>0){
		alert('이미 선택된 분류입니다.');
		return false;
	}

	$('.selected_cate').append("<li>"+ca_name+"<input type='hidden' name='new_cate[]' value='"+ca_id+"'/><input type='button' value=' X ' onclick=\"category_del(this);\"></li>");
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

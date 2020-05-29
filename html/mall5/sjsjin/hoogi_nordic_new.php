<?php
include_once("./_common.php");

$g4[title] = "구매후기";
include_once("./_head.php");

// 김선용 201208 : 권한처리 : [400650] 사용후기
// [au_menu] => 400650
// [au_auth] => r,w
$is_auth = false;
$au_sql = sql_query("select au_menu, au_auth from $g4[auth_table] where mb_id='{$member['mb_id']}' ");
while($au=sql_fetch_array($au_sql)){
	if($au['au_menu'] == '400650') {
		$is_auth = true;
		break;
	}
}
if($is_admin === 'super') $is_auth = true;

if(trim($search) != '') $search = mysql_real_escape_string($_GET['search']);
$sql_search_in_qry = sql_query("select DISTINCT it_id from yc4_event_item where ev_id in('1403859680','1403859988','1403860193','1403861457','1403863302','1403863916')");
while($row = sql_fetch_array($sql_search_in_qry)){
	$sql_search_in .= ($sql_search_in ? ", ":"") . "'".$row['it_id']."'";
}


$sql_search = " where is_confirm=1 and a.it_id in (".$sql_search_in.") and a.is_time >= '2014-09-10' ";

if($_GET['st_dt']){
	$sql_search .= " and a.is_time >= '".$_GET['st_dt']." 00:00:00'";
}

if($_GET['en_dt']){
	$sql_search .= " and a.is_time <= '".$_GET['en_dt']." 23:59:59'";
}

if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}
if ($sel_ca_id != "") {
    $sql_search .= " and ca_id like '$sel_ca_id%' ";
}
# 관리자는 베스트후기 상관없이 업로드 순서대로 보인다
if (!$sort1){ 
	if($is_auth){
		$sort1 = " is_id";
	}else{
		$sort1 = "is_best desc, is_id";
	}
}
if (!$sort2) $sort2 = "desc";
$sql_common = "  from $g4[yc4_item_ps_table] a
                 left join $g4[yc4_item_table] b on (a.it_id = b.it_id) ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(a.is_id) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = 30; //$config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
$sql  = " select a.*, b.it_name $sql_common order by $sort1 $sort2 limit $from_record, $rows ";
$result = sql_query($sql);

# 내가 구매한 노르딕 제품 #
if($member['mb_id']){
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
	}
	
}


$qstr = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search);
?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js"></script>
<SCRIPT LANGUAGE="JAVASCRIPT" SRC="<?=$g4['path']?>/js/protect.js"></SCRIPT>
<script language="javascript">

</script>
<script language="javascript">

</script>

<div style="margin:0; positionrelative;">
  <img src="http://115.68.20.84/event/freegift_nordic/Nordic_hogi.jpg" style="margin-bottom:15px;"/>
    <?if($my_od_sql_cnt>0){?>
    <a href="<?=$g4['path']?>/sjsjin/hoogi_nordic_item_list.php" style="position:absolute; right: 50px; top:530px; display:inline-block;"><img src="http://115.68.20.84/event/freegift_nordic/go_hoogi.png"></a>
    <?}else{?>
    <?}?>
</div>

<div style="margin:0;">	
	<img src="http://115.68.20.84/main/hoogi.gif"alt="" />
</div>
<br/>

<form name=flist style="margin:0px;" method="get">
<input type=hidden name=page  value="<? echo $page ?>">

<table width=755px cellpadding=0 cellspacing=0>
<tr>
<!--     <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>첫 페이지로</a></td> -->
    <td width=70% align=left>
       <?/*
		<select name="sel_ca_id">
            <option value=''>-전체분류-</option>
            <?
            $sql1 = " select ca_id, ca_name from $g4[yc4_category_table] order by ca_id ";
            $result1 = sql_query($sql1);
            for ($i=0; $row1=sql_fetch_array($result1); $i++) {
                $len = strlen($row1[ca_id]) / 2 - 1;
                $nbsp = "";
                for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
                echo "<option value='$row1[ca_id]'>$nbsp$row1[ca_name]</option>";
            }
            ?>
        </select>
        <script type="text/javascript">
        if("<?=$sel_ca_id?>" != '')
	        document.flist.sel_ca_id.value = '<?=$sel_ca_id?>';
		</script>
		*/?>
		<?
		if($is_auth){
			echo "
				<input type='text' name='st_dt' id='st_dt' value='".$_GET['st_dt']."' readonly/>
				~
				<input type='text' name='en_dt' id='en_dt' value='".$_GET['en_dt']."' readonly/>
			";
		}
		?>

        <select name=sel_field>
            <option value='b.it_name'>상품명</option>
            <option value='b.it_id'>상품코드</option>
        </select>
        <? if ($sel_field != '') echo "<script type='text/javascript'> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo get_text(stripslashes($search))?>' title="좌측부터 일치하는 방식으로 검색됩니다." />
        <input type=submit value=" 검색 " />
    </td>
    <td width=20% align=right>전체 : <? echo number_format($total_count,0)?></td>
</tr>
</table>
</form>

<?if($is_auth){?>
<form name=flist style="margin:0;" method="post" action="hoogi_listupdate.php" onsubmit="return js_best_update(this);">
<input type=hidden name=page  value="<? echo $page ?>">
<input type=hidden name=sel_ca_id  value="<? echo $sel_ca_id ?>">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search  value="<? echo urlencode($search) ?>">
<?}?>

<table cellpadding=0 cellspacing=0 width=755px border=0>
<colgroup width=80px>
<colgroup width=340px>
<colgroup width=200px>
<colgroup width=55px>
<colgroup width=80px>
<?if($is_auth){?>
<colgroup width=30>
<?}?>
<tr><td colspan=10 height=1 bgcolor=#EBE4DB></td></tr>
<tr><td colspan=10 height=1 bgcolor=#F1ECE6></td></tr>
<tr align=center height=30 bgcolor=#FAF7F3>
    <td></td>
    <td>상품명</td>
    <td>제목</td>
    <td>작성자</td>
    <td>점수</td>
	<?if($is_auth){?>
	<td>선택</td>
	<?}?>
</tr>
<tr><td colspan=10 height=1 bgcolor=#EBE4DB></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
	if($row['it_name']){
		$row['it_name'] = get_item_name($row['it_name']);
	}
    $row[is_subject] = stripslashes($row[is_subject]);
	$is_content = conv_content($row[is_content], 0);
    $href = "$g4[shop_path]/item.php?it_id=$row[it_id]";
	$star = get_star($row[is_score]);
	$is_name = stripslashes($row['is_name']);

	// 회원처리
	if($row['mb_id']) $is_name = "<b>{$is_name}</b>";
	if($is_auth) $is_name .= "<br/>({$row['mb_id']})";

	$img_str = "";
	for($a=0; $a<5; $a++){
		if($row["is_image{$a}"] != '' && file_exists("{$g4['path']}/data/ituse/".$row["is_image{$a}"]))
			$img_str .= "<img src='{$g4['path']}/data/ituse/".$row["is_image{$a}"]."' border=0 /><br/><br/>";
	}

	$best_str = ($row['is_best'] ? "<span style='color:#fd5900; font-weight:bold;'>[베스트]<br/></span> " : "");

    echo "
    <tr width=755px>
        <td style='padding-top:5px; padding-bottom:5px;' align=center>
		<table border=0 cellspacing=0 cellpadding=0><tr><td style='cursor:pointer; border:1px solid #FFFFFF; padding:0px' onMouseOver=\"this.style.border='1px solid #FF6600';\" onMouseOut=\"this.style.border='1px solid #FFFFFF';\"><a href='$href' target=_blank title='새창으로 [".stripslashes($row['it_name'])."] 상품보기'>".get_it_image("{$row[it_id]}_s", 70, 60,false,false,false,false,true)."</a></td></tr></table></td>
        <td align=left><a href='$href' target=_blank title='새창으로 [".stripslashes($row['it_name'])."] 상품보기'>".stripslashes($row[it_name])."</a></td>
                <td style='padding : 10px;'>
			<div>{$best_str}<b>$row[is_subject]</b><br><span style='font-size:8pt;'>[{$row['is_time']}]</span></div>
		</td>
		<td align=center>$is_name</td>
        <td align=center><img src=\"{$g4[shop_img_path]}/star{$star}.gif\"></td>";
		if($is_auth){
			echo "
			<td align=center>
				<input type=checkbox name='chk[]' value='$i' />
				<input type=hidden name='is_id[]' value='{$row['is_id']}' />
				<input type=hidden name='mb_id[]' value='{$row['mb_id']}' />
			</td>";
		}
	echo "
    </tr>
	<tr><td colspan=10><div style='width:755px;margin:20px 0  40px 0;overflow:hidden' class='hoogi_cont'>$is_content<br/>{$img_str}</div></td></tr>
	<tr><td colspan=10 align=center height=2 bgcolor='#cfcfcf'></td></tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=10 align=center height=100 bgcolor=#ffffff><span class=point>자료가 없습니다.</span></td></tr>";
}
?>

<tr><td colspan=5 height=1 bgcolor=F1EFEC></td></tr>
</table>

<?if($is_auth){?>
<p align=right style="margin:10px 0;"><input type="submit" value="베스트후기선정" /></p>
</form>
<?}?>

<table width=100%>
<tr>
    <td align=center><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>


<?if($is_auth){?>
<script type="text/javascript">
function js_best_update(f)
{
	var a = document.getElementsByName('chk[]');
	var c = false;
	for(k=0; k<a.length; k++){
		if(a[k].checked){
			c = true;
			break;
		}
	}
	if(c == false){
		alert("베스트후기로 선정할 자료를 1개이상 선택해 주십시오.");
		return false;
	}
	return true;
}

$(document).ready(function(){
	$('#st_dt,#en_dt').datepicker({
		dateFormat : "yy-mm-dd",
		firstDay : 0, // 일요일 부터 시작
		maxDate : '0',
//		minDate : '-0',
		minDate: new Date(2014, 9 - 1, 11),
		changeMoth : true,
		changeYear : true,
		dayNamesMin : ['일','월','화','수','목','금','토']
		,beforeShowDay : 
			$.datepicker.noWeekends // 주말 선택 안되도록
//			$.datepicker.iso8601Week( new Date( 2007, 1 - 1, 26 ) )
		
	});

//    $(".numeric").css("ime-mode", "disabled");  //요렇게 하면 한글도 잡아준다

//	date_disabled();
	
});
</script>
<?}?>


<? include_once("./_tail.php"); ?>

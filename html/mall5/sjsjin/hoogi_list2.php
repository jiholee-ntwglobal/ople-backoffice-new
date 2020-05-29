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
$sql_search = " where is_confirm=1 ";
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



$qstr = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search);
?>
<SCRIPT LANGUAGE="JAVASCRIPT" SRC="<?=$g4['path']?>/js/protect.js"></SCRIPT>
<script language="javascript">
<!--
ObjectWrite();
-->
</script>
<script language="javascript">
<!--
StartGuard(2);
-->
</script>
<div class='PageTitle'>
<img src="http://115.68.20.84/main/hoogi.gif" alt="사용후기모음" />
</div>
<div style="line-height:18px;padding-bottom:15px;">
<p style="color:#959595;">사용후기 작성은 해당상품의 상세보기 페이지에서 작성하실 수 있습니다.</p>
<p style="color:#959595;">상품평은 개인의 주관적인 생각이므로 다양한 의견이 있을 수 있으니, 참고사항으로 사용하시기 바랍니다.</p>
</div>
<div>
<img src="http://115.68.20.84/mall6/page/review/review_top.jpg" alt="사용후기 포인트적립">
</div>

<form name=flist style="margin:0px;" method="get">
<input type=hidden name=page  value="<? echo $page ?>">

<table width=100% cellpadding=0 cellspacing=0 style="padding:15px 0;">
<tr>
<!--     <td width=10%><a href='<?=$_SERVER[PHP_SELF]?>'>첫 페이지로</a></td> -->
    <td width=20%>전체 : <? echo number_format($total_count,0)?></td>
	<td width=70% align=right>
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

        <select name=sel_field>
            <option value='b.it_name'>상품명</option>
            <option value='b.it_id'>상품코드</option>
        </select>
        <? if ($sel_field != '') echo "<script type='text/javascript'> document.flist.sel_field.value = '$sel_field';</script>"; ?>

        <input type=text name=search value='<? echo get_text(stripslashes($search))?>' title="좌측부터 일치하는 방식으로 검색됩니다." style='padding:1px 0;'/>
        <input type=submit value=" 검색 " style="padding:1px 0;"/>
    </td>
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

<table cellpadding=0 cellspacing=0 width=100% border=0 class='list_box'>
<colgroup>
	<col width='175px'/>
	<col />
	<?if($is_auth){?>
	<col width='30px'/>
	<?}?>
</colgroup>
<thead>
<tr>
    <th>상품명</th>
    <th>상품후기</th>
	<?if($is_auth){?>
	<th>선택</th>
	<?}?>
</tr>
</thead>
<tbody>
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
	if($row['mb_id']) $is_name = "<strong>{$is_name}</strong>";
	if($is_auth) $is_name .= "({$row['mb_id']})";

	$img_str = "";
	for($a=0; $a<5; $a++){
		if($row["is_image{$a}"] != '' && file_exists("{$g4['path']}/data/ituse/".$row["is_image{$a}"]))
			$img_str .= "<img src='{$g4['path']}/data/ituse/".$row["is_image{$a}"]."' border=0 /><br/><br/>";
	}

	$best_str = ($row['is_best'] ? "<span style='padding-right:5px;color:#fd5900; font-weight:bold;'>[베스트]</span> " : "");

    echo "
    <tr>
        <td style='padding:10px;vertical-align:top;border-right:solid 1px #ececec;'>
		<table width=100% border=0 cellspacing=0 cellpadding=0>
			<tr>
				<td style='text-align:center;cursor:pointer; border:1px solid #FFFFFF; padding:15px 0px' onMouseOver=\"this.style.border='1px solid #FF6600';\" onMouseOut=\"this.style.border='1px solid #FFFFFF';\"><a href='$href' target=_blank title='새창으로 [".stripslashes($row['it_name'])."] 상품보기'>".get_it_image("{$row[it_id]}_s", 90, 90,false,false,false,false,true)."</a></td>
			</tr>
			<tr>
				<td style='line-height:16px;letter-spacing:-0.05em;'><a href='$href' target=_blank title='새창으로 [".stripslashes($row['it_name'])."] 상품보기'>".stripslashes($row[it_name])."</a></td>
			</tr>
		</table>
		</td>
        <td style='padding:10px;background-color:#f9f9f9;'>
			<table width=100% border=0 cellspacing=0 cellpadding=0>
				<colgroup>
					<col />
					<col width='120px'/>
					<col width='150px'/>
					<col width='30px'/>
				</colgroup>
				<tr>
					<td>{$best_str}<strong>$row[is_subject]</strong></td>
					<td style='font-size:11px;color:#b7b7b7;text-align:center;'>{$row['is_time']}</td>
					<td style='text-align:center;'>$is_name</td>
					<td style='text-align:center;'><img src=\"{$g4[shop_img_path]}/star{$star}.gif\"></td>
				</tr>
				<tr>
					<td colspan=4>
						<div style='width:100%;margin:20px 0;overflow:hidden' class='hoogi_content'>$is_content<br/>{$img_str}</div>
					</td>
				</tr>
			</table>
		</td>";
		if($is_auth){
			echo "
			<td align=center  style='background-color:#f9f9f9;'>
				<input type=checkbox name='chk[]' value='$i' />
				<input type=hidden name='is_id[]' value='{$row['is_id']}' />
				<input type=hidden name='mb_id[]' value='{$row['mb_id']}' />
			</td>";
		}
	echo "
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=10 align=center height=100 bgcolor=#ffffff><span class=point>자료가 없습니다.</span></td></tr>";
}
?>
</tbody>
</table>

<?if($is_auth){?>
<p align=right style="margin:10px 0;"><input type="submit" value="베스트후기선정" /></p>
</form>
<?}?>

<div class='paging'>
<?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</div>


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
</script>
<?}?>


<? include_once("./_tail.php"); ?>
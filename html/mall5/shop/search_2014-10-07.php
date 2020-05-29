<?
include_once("./_common.php");

// 김선용 201006 : 전역변수 XSS/인젝션 보안강화 및 방어
include_once "sjsjin.shop_guard.php";
//guard_script1($search_str);

// 상품이미지 사이즈(폭, 높이)를 몇배 축소 할것인지를 설정
// 0 으로 설정하면 오류남 : 기본 2
$image_rate = 2;

$g4[title] = "상품 검색";
include_once("./_head.php");


if($_GET['search_str_all']){
	$_GET['search_str'] = $_GET['search_str_all'];
	$search_name = '전체검색';
}elseif($_GET['search_str']){
	$search_name = strtoupper($station['name']).'관 검색';
}


// 김선용 201206 : 서제스트 쿼리에서 이스케이프 처리돼서 넘어옴
//$search_str = (trim($_GET['search_str']) != '' ? mysql_real_escape_string($_GET['search_str']) : '');
$search_str = (trim($_GET['search_str']) != '' ? $_GET['search_str'] : '');
$it_maker = (trim($_GET['it_maker']) != '' ? mysql_real_escape_string(stripslashes($_GET['it_maker'])) : '');

if($search_str && strlen($search_str)<2){
	alert('검색어는 최소 2글자 이상 입력해 주세요');
	exit;
}

// 김선용 201207 : 검색전용 테이블. 모든 검색어 저장
// 검색어, 회원id, 일시, ip
sql_query("insert into yc4_search_key
	set sk_key = '{$_GET['search_str']}',
		mb_id = '{$member['mb_id']}',
		sk_datetime = '{$g4['time_ymdhis']}',
		sk_ip = '".getenv('REMOTE_ADDR')."' ");

if($_GET['station_search']){ # 관내 검색일 경우

	include $g4['full_shop_path']."/search_station.php";


	exit;
}
?>
<script type="text/javascript">
if(document.getElementById('search-input'))
	document.getElementById('search-input').value = "<?=stripslashes($_GET['search_str'])?>";
</script>
<table width=100% cellpadding=0 cellspacing=0 align=center border=0>

    <td valign=top>
    <?if($it_maker){?>

		<?}else if($search_str){?>
		<fieldset style="margin:0 5px;padding:10px;">
			<legend>검색어 입력&검색방법 안내</legend>
			※ 검색어는 최소 <u>2글자 이상</u> 입력하고, 여러문구를 입력시에는 띄워서 입력하십시오. 띄워서 입력하면 해당 문구들이 포함된 상품들이 검색됩니다.
		</fieldset>
		<br/>
<?}?>

		<?if($it_maker){
			$search_chk = true;
			?>
			
			<div class="brandtitle"><strong><?=stripslashes($_GET['it_maker'])?></strong></div>
			<?
			$it_row = sql_fetch("select it_maker_description from {$g4['yc4_item_table']} where it_maker='$it_maker' ".$hide_caQ.$hide_makerQ." limit 1");
			if($it_row['it_maker_description'] != '')
				echo "<div style='text-align:center;'>".conv_content($it_row['it_maker_description'],1)."</div>";
			?>
		<?}else if($search_str){
			$search_chk = true;
		?>
			&nbsp;&nbsp; <?=$search_name;?> <br />
	        &nbsp;&nbsp; 찾으시는 검색어는 &quot; <strong><?=stripslashes(get_text($_GET['search_str']))?></strong> &quot; 입니다.
		<?}else{
			$search_chk = false;
		?>
			&nbsp;&nbsp; 검색어가 없습니다. 검색어를 입력해 주십시오.
		<?}?>
        <br><br>

		<?

		if(!$search_chk){
			# 검색어가 없다면 검색을 하지 않는다 2014-09-10 홍민기 #
			goto no_str;

		}
		// 김선용 201211 : 단종상품 미출력
        // QUERY 문에 공통적으로 들어가는 내용
        // 상품명에 검색어가 포한된것과 상품판매가능인것만
        $sql_common = " 
			from 
				$g4[yc4_item_table] a 
				left join 
				yc4_category_item c on a.it_id = c.it_id
				left join
				$g4[yc4_category_table] b on c.ca_id=b.ca_id
			where 
				a.it_use = 1 
				and 
				a.it_discontinued=0 
				and 
				b.ca_use = 1 ".$hide_caQ5.$hide_maker3.$hide_itemQ2;

		// 김선용 200804 : ev 로 시작하는 분류는 숨김
		if(!$is_admin)
			$sql_common .= " and b.ca_id not like ('ev%') ";

		// 김선용 201206 : 제조사, 풀텍스트
		if($it_maker)
			$sql_common .= " and it_maker='$it_maker' ";
		else if($search_str)
		{
			// 검색어 공백구분으로 분리
			$search_arr = explode(" ", $search_str);
			if(count($search_arr) == 1)
				$search_str2 = "+{$search_str}*";
			else
			{
				$search_str2 = "+{$search_arr[0]}*";
				for($k=1; $k<count($search_arr); $k++){
					$search_str2 .= " +{$search_arr[$k]}*";
				}
			}
			$search_str2 = "+{$search_str}*";
			//$search_str2 .= "*";
            /*$sql_common .= " and ( a.it_id like '$search_str%' or
                                   a.it_name like   '%$search_str%' or
                                   a.it_basic like  '%$search_str%' or
                                   a.it_explan like '%$search_str%' ) ";  , a.it_basic, a.it_explan*/
            $sql_common .= " and  match(a.it_name) against('{$search_str2}' in boolean mode) ";
        }

        // 분류선택이 있다면 특정 분류만
        if ($search_ca_id != "")
            $sql_common .= " and a.ca_id like '$search_ca_id%' ";

		//echo "select a.ca_id,    a.it_id    $sql_common  order by a.ca_id, a.it_id desc ";
        // 검색된 내용이 몇행인지를 얻는다
        $sql = " select COUNT(*) as cnt $sql_common ";

        $row = sql_fetch($sql);
        $total_count = $row[cnt];
        if($it_maker)
        echo "&nbsp;&nbsp; 총 상품수 <b>{$total_count}개</b><br>";
        else if($search_str)
        echo "&nbsp;&nbsp; 입력하신 검색어로 총 <b>{$total_count}건</b>의 상품이 검색 되었습니다.<br>";

        // 임시배열에 저장해 놓고 분류별로 출력한다.
        // write_serarch_save() 함수가 임시배열에 있는 내용을 출력함
        if ($total_count > 0) {

			// 김선용 200908 : 미사용
            // 인기검색어
            $sql = " insert into $g4[popular_table]
                        set pp_word = '$search_str',
                            pp_date = '$g4[time_ymd]',
                            pp_ip = '$_SERVER[REMOTE_ADDR]' ";
            sql_query($sql, FALSE);


            unset($save); // 임시 저장 배열
            $sql = " select c.ca_id,
                            a.it_id,if(it_stock_qty <=0,0,1) as cnt,
							match(a.it_name) against('{$search_str2}' in boolean mode) as score
                     $sql_common
                     order by cnt desc,score desc, a.ca_id, a.it_id desc ";

            $result = sql_query($sql);
            for ($i=0; $row=mysql_fetch_array($result); $i++) {
                if ($save[ca_id] != $row[ca_id]) {
                    if ($save[ca_id]) {
                        write_search_save($save);
                        unset($save);
                    }
                    $save[ca_id] = $row[ca_id];
                    $save[cnt] = 0;
                }
                $save[it_id][$save[cnt]] = $row[it_id];
                $save[cnt]++;
            }
            mysql_free_result($result);
            write_search_save($save);
        }
        ?>
    </td>
</tr>
</table>

<?
function write_search_save($save)
{
	global $g4, $search_str , $default , $image_rate , $cart_dir;

    $sql = " select ca_name from $g4[yc4_category_table] where ca_id = '$save[ca_id]' ";
    $row = sql_fetch($sql);

    /*
    echo "
    <table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
    <colgroup width=80>
    <colgroup width=>
    <colgroup width=150>
    <colgroup width=100>
    <tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
    <tr>
        <td colspan=2 height='28'>&nbsp;<b><a href='./list.php?ca_id={$save[ca_id]}'>$row[ca_name]</a></b> ($save[cnt])</td>
        <td align=center>판매가격</td>
        <td align=center>포인트</td>
    </tr>
    <tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
    ";
    */

    // 김선용 2006.12 : 중복 하위분류명이 많으므로 대분류 포함하여 출력
     $ca_temp = "";
     if(strlen($save['ca_id']) > 2) // 중분류 이하일 경우
     {
         $sql2 = " select ca_name from $g4[yc4_category_table] where ca_id='".substr($save[ca_id],0,2)."' ";
        $row2 = sql_fetch($sql2);
        $ca_temp = "<b><a href='./list.php?ca_id=".substr($save[ca_id],0,2)."'>$row2[ca_name]</a></b> &gt; ";
     }
    echo "
    <table width=100% cellpadding=0 cellspacing=0 border=0 class='list_styleA' style='margin:10px 0;'>
	<colgroup>
		<col width='100px'/>
		<col />
		<col width='80px'/>
		<col width='80px'/>
	</colgroup>
	<thead>
    <!--<tr><td colspan=4 height=2 bgcolor=#fa5a00></td></tr>-->
    <tr>
        <th colspan=2 style='text-align:left;padding-left:10px;'>{$ca_temp}<strong><a href='./list.php?ca_id={$save[ca_id]}'>$row[ca_name]</a></strong> ($save[cnt])</td>
        <th align=center>판매가격</td>
        <th align=center>포인트</td>
    </tr>
	</thead>
	<tbody>
    <!--<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>-->";

    for ($i=0; $i<$save[cnt]; $i++) {
        $sql = " select it_id,
                        it_name,
                        it_amount,
                        it_amount2,
                        it_amount3,
                        it_tel_inq,
                        it_point,
                        it_type1,
                        it_type2,
                        it_type3,
                        it_type4,
                        it_type5
                   from $g4[yc4_item_table] where it_id = '{$save[it_id][$i]}' ";
        $row = sql_fetch($sql);

        $image = get_it_image("$row[it_id]_s", (int)($default[de_simg_width] / $image_rate), (int)($default[de_simg_height] / $image_rate), $row[it_id],null,null,true,true);

//        if ($i > 0)
            // echo "<tr><td height=1></td><td bgcolor=#CCCCCC colspan=3></td></tr>";
		$row['it_name'] = get_item_name($row['it_name'],'list');

		if(trim($_GET['search_str']) != ''){
			$it_name = search_font(stripslashes($_GET['search_str']), stripslashes($row['it_name']));
		}else if(trim($_GET['it_maker']) != ''){
			$it_name = search_font(stripslashes(trim($_GET['it_maker'])), stripslashes($row['it_name']));
		}

		
		
        echo "
            <tr>
                <td style='padding:7px;text-align:left;'>$image</td>
                <!--<td>".it_name_icon($row)."</td>-->
				<td style='text-align:left;'><a href='{$g4['shop_path']}/item.php?it_id={$row['it_id']}'>".$it_name."</a></td>
                <!-- <td><span class=amount>".display_amount($row[it_amount])."<em>원</em></span></td> -->
                <td><span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."<em>원</em></span></td>
                <td><span class=item_point>".display_point($row[it_point])."</span></td>
            </tr>";
    }
    // echo "<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>";
    echo "</tbody></table>\n";
}
# 검색어가 없다면 여기로 이동 2014-09-10 홍민기 #
no_str : 
include_once("./_tail.php");
?>
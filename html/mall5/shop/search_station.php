<?
	# 제품관내 검색 페이지 # 

?>
<script type="text/javascript">
if(document.getElementById('search-input'))
	document.getElementById('search-input').value = "<?=stripslashes($_GET['search_str'])?>";
</script>
<table width=100% cellpadding=0 cellspacing=0 align=center border=0>

    <td valign=top>
    <?if($it_maker){?>

		<?}else if($search_str){?>
		<fieldset style="margin:0 5px;">
			<legend>검색어 입력&검색방법 안내</legend>
			※ 검색어는 최소 <u>2글자 이상</u> 입력하고, 여러문구를 입력시에는 띄워서 입력하십시오. 띄워서 입력하면 해당 문구들이 포함된 상품들이 검색됩니다.
		</fieldset>
		<br/>
<?}?>

		<?if($it_maker){?>
			<div class="brandtitle"><b><?=stripslashes($_GET['it_maker'])?></b> products</div>
			<?
			$it_row = sql_fetch("select it_maker_description from {$g4['yc4_item_table']} where it_maker='$it_maker' ".$hide_caQ.$hide_makerQ." limit 1");
			if($it_row['it_maker_description'] != '')
				echo "<div style='margin:5px; padding:4px; border:0px solid #0066cc;'>".conv_content($it_row['it_maker_description'],1)."</div>";
			?>
		<?}else if($search_str){?>
			&nbsp;&nbsp; <?=$search_name;?> <br />
	        &nbsp;&nbsp; 찾으시는 검색어는 &quot; <b><?=stripslashes(get_text($_GET['search_str']))?></b> &quot; 입니다.
		<?}else{?>
			&nbsp;&nbsp; 검색어가 없습니다. 검색어를 입력해 주십시오.
		<?}?>
        <br><br>

		<?
		// 김선용 201211 : 단종상품 미출력
        // QUERY 문에 공통적으로 들어가는 내용
        // 상품명에 검색어가 포한된것과 상품판매가능인것만
        /*
		$sql_common = " 
			from 
				$g4[yc4_item_table] a 
				left join 
				yc4_category_item c on a.it_id = c.it_id
				left join
				$g4[yc4_category_table] b on c.ca_id = b.ca_id
				left join
				shop_category d on b.ca_id = d.ca_id
			where 
				a.it_use = 1 
				and 
				a.it_discontinued = 0 
				and 
				b.ca_use = 1 
				and
				d.s_id = '".$_SESSION['s_id']."'
				".$hide_caQ5.$hide_maker3.$hide_itemQ2;
		*/

		/*
		$sql_common = " 
			from
				yc4_station e
				left join
				shop_category d on e.s_id = d.s_id
				left join
				".$g4['yc4_category_table']." b on d.ca_id = b.ca_id
				left join
				yc4_category_item c on b.ca_id = c.ca_id
				left join
				".$g4['yc4_item_table']." a on c.it_id = a.it_id
			where
				a.it_use = 1 
				and 
				a.it_discontinued = 0 
				and 
				b.ca_use = 1 
				and
				d.s_id = '".$_SESSION['s_id']."'
				".$hide_caQ5.$hide_maker3.$hide_itemQ2;
		
		*/

		$sql_common = " 
			from
				yc4_station e
				left join
				shop_category d on e.s_id = d.s_id
				left join
				".$g4['yc4_category_table']." b on b.ca_id like concat(d.ca_id,'%')
				left join
				yc4_category_item c on b.ca_id = c.ca_id
				left join
				".$g4['yc4_item_table']." a on c.it_id = a.it_id
			where
				a.it_use = 1 
				and 
				a.it_discontinued = 0 
				and 
				b.ca_use = 1 
				and
				d.s_id = '".$_SESSION['s_id']."'
				".$hide_caQ5.$hide_maker3.$hide_itemQ2;

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
				$search_str2 = "+*{$search_str}*";
			else
			{
				$search_str2 = "+*{$search_arr[0]}*";
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
            $sql = " select 
							e.name,
							c.ca_id,
                            a.it_id,if(it_stock_qty <=0,0,1) as cnt,
							match(a.it_name) against('{$search_str2}' in boolean mode) as score
                     $sql_common
                     order by cnt desc, score desc,a.ca_id, a.it_id desc ";

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


include_once("./_tail.php");
?>

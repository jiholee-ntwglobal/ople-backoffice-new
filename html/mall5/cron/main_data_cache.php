<?php
/*
----------------------------------------------------------------------
file name	 : main_data_cache.php
comment		 : 메인 진열상품 및 후기 데이터 캐싱 처리
date		 : 2015-01-21
author		 : ghdalslrdi@ntwglobal.com

----------------------------------------------------------------------
*/


include "/ssd/html/mall5/cache/main_cache.php";

include "db.config.php";
$ople_link = mysql_connect($ople_db['host'], $ople_db['id'], $ople_db['pw']);

$db_selected1 = mysql_select_db('okflex5');

$pay_sql = mysql_query("
	select de_conv_pay from yc4_default
");

$pay = mysql_fetch_assoc($pay_sql);
$pay = $pay['de_conv_pay'];


$result_arr = array();

function get_item_name($it_name){
    if(strpos($it_name,'||') === false){
        $it_name = $it_name;
    }else{
        $tmp_arr = explode('||',$it_name);
        # 브랜드명은 한글만.. #
        $tmp_arr[0] = preg_replace("/\[([^{}]+)\]/i",'', $tmp_arr[0]);
        $it_name = $tmp_arr[0] ."||". $tmp_arr[1];
    }
    $it_name = addslashes($it_name);

    return $it_name;
}

function usd_convert($amount){ // 달러로 변환
    global $pay;
    return number_format(round( ($amount / $pay) ,2),2);

}

# 할인율 퍼센트인지 구하는 함수 2015-01-23 홍민기 #
function get_dc_percent($dc_amount,$msrp_amount){
    return round(100 - ($dc_amount / $msrp_amount  * 100 ));
}

# 메인 후기 데이터 #
$sql = mysql_query("
	select
		a.is_id,
		b.it_id,
		a.img_link,
		b.is_content,
		c.it_name
	from
		yc4_item_ps_main a,
		yc4_item_ps b,
		yc4_item c
	where
		a.is_id = b.is_id
		and
		b.it_id = c.it_id
		and
		a.img_link != ''
");
$result = '';
$i=0;

while($data = mysql_fetch_assoc($sql)){

    $it_name = addslashes(get_item_name($data['it_name']));
    $data['is_content'] =  addslashes($data['is_content']);

    $result_arr['ps'][$i]['is_id'] = $data['is_id'];
    $result_arr['ps'][$i]['it_id'] = $data['it_id'];
    $result_arr['ps'][$i]['img_link'] = $data['img_link'];
    $result_arr['ps'][$i]['it_name'] = $it_name;
    $result_arr['ps'][$i]['is_content'] = $data['is_content'];

    $result .=<<<EOL
\$main['ps']['{$i}']['is_id'] = '{$data['is_id']}';
\$main['ps']['{$i}']['it_id'] = '{$data['it_id']}';
\$main['ps']['{$i}']['img_link'] = '{$data['img_link']}';
\$main['ps']['{$i}']['it_name'] = '{$it_name}';
\$main['ps']['{$i}']['is_content'] = '{$data['is_content']}';


EOL;


    /*
    $result .= '$main['."'ps'".']['.$i.']['."'img_link'".'] = '."'".$data['img_link']."';\n";
    $result .= '$main['."'ps'".']['.$i.']['."'it_name'".'] = '."'".$it_name."';\n";
    //$result .= '$main['."'ps'".']['.$i.']['."'is_content'".'] = '."'".$data['is_content']."';\n";
    */
    $i++;
}

# 메인 아이템 데이터 #
$sql = mysql_query("
	select
		a.m_type,
		a.it_id,
		a.img_link,
		b.it_name,
		b.it_amount
	from
		yc4_main_item a,
		yc4_item b
	where
		a.it_id = b.it_id
		and
		'".date('Ymd')."' between a.st_dt and a.en_dt
");
while($data = mysql_fetch_assoc($sql)){
    $it_name = addslashes(trim(get_item_name($data['it_name'])));
    $data['it_amount_usd'] = usd_convert($data['it_amount']);

    $result_arr['item'][$data['m_type']]['it_id'] = $data['it_id'];
    $result_arr['item'][$data['m_type']]['img_link'] = $data['img_link'];
    $result_arr['item'][$data['m_type']]['it_name'] = $it_name;
    $result_arr['item'][$data['m_type']]['it_amount'] = $data['it_amount'];
    $result_arr['item'][$data['m_type']]['it_amount_usd'] = $data['it_amount_usd'];


    $result .=<<<EOL

\$main['item']['{$data['m_type']}']['it_id'] = '{$data['it_id']}';
\$main['item']['{$data['m_type']}']['img_link'] = '{$data['img_link']}';
\$main['item']['{$data['m_type']}']['it_name'] = '{$it_name}';
\$main['item']['{$data['m_type']}']['it_amount'] = '{$data['it_amount']}';
\$main['item']['{$data['m_type']}']['it_amount_usd'] = '{$data['it_amount_usd']}';

EOL;
    $i++;
}


# 핫딜종 상품 데이터 #
$sql = mysql_query("
	select
		*
	from
		yc4_hotdeal_item
	where
		flag = 'Y'
		and sort > 0
		and sort < 9
	order by sort
	limit 0,8
");
$hotdeal_cnt = 0;
while($data = mysql_fetch_assoc($sql)){
    $hotdeal_cnt++;
    $hotdeal_arr[$data['it_id']] = true;
    $msrp_krw = $data['it_amount_msrp'] * $pay;
    $dc_per = get_dc_percent($data['it_event_amount'],$msrp_krw).'%';

    $result_arr['hotdeal'][$data['sort']]['it_id'] = $data['it_id'];
    $result_arr['hotdeal'][$data['sort']]['it_event_amount'] = $data['it_event_amount'];
    $result_arr['hotdeal'][$data['sort']]['it_amount_msrp'] = $data['it_amount_msrp'];
    $result_arr['hotdeal'][$data['sort']]['it_amount_msrp_krw'] = $msrp_krw;
    $result_arr['hotdeal'][$data['sort']]['dc_per'] = $dc_per;
    $result_arr['hotdeal'][$data['sort']]['img_link'] = $data['img_link'];
    $result_arr['hotdeal'][$data['sort']]['end_fg'] = false;

    $result .=<<<EOL

\$main['hotdel']['{$data['sort']}']['it_id'] = '{$data['it_id']}';
\$main['hotdel']['{$data['sort']}']['it_event_amount'] = '{$data['it_event_amount']}';
\$main['hotdel']['{$data['sort']}']['it_amount_msrp'] = '{$data['it_amount_msrp']}';
\$main['hotdel']['{$data['sort']}']['it_amount_msrp_krw'] = '{$msrp_krw}';
\$main['hotdel']['{$data['sort']}']['dc_per'] = '{$dc_per}';
\$main['hotdel']['{$data['sort']}']['img_link'] = '{$data['img_link']}';
\$main['hotdel']['{$data['sort']}']['end_fg'] = false;

EOL;
}

# 4개 미만일 경우 부족한 만큼 마지막에 종료한 데이터를 종료로 뿌려준다 #
if($hotdeal_cnt < 9){
    $hotdeal_soldout_cnt = 8 - $hotdeal_cnt;
    $sql = mysql_query("
		select
			*
		from
			yc4_hotdeal_item
		where
			flag = 'E'
			and
			en_dt is not null
		order by en_dt desc
		limit 0,".$hotdeal_soldout_cnt."
	");
    while($data = mysql_fetch_assoc($sql)){
        $hotdeal_cnt++;
        $msrp_krw = $data['it_amount_msrp'] * $pay;
        $dc_per = get_dc_percent($data['it_event_amount'],$msrp_krw).'%';

        $result_arr['hotdeal'][$hotdeal_cnt]['it_id'] = $data[it_id];
        $result_arr['hotdeal'][$hotdeal_cnt]['it_event_amount'] = $data[it_event_amount];
        $result_arr['hotdeal'][$hotdeal_cnt]['it_amount_msrp'] = $data[it_amount_msrp];
        $result_arr['hotdeal'][$hotdeal_cnt]['it_amount_msrp_krw'] = $msrp_krw;
        $result_arr['hotdeal'][$hotdeal_cnt]['dc_per'] = $dc_per;
        $result_arr['hotdeal'][$hotdeal_cnt]['img_link'] = $data[img_link];
        $result_arr['hotdeal'][$hotdeal_cnt]['end_fg'] = true;

        $result .=<<<EOL

\$main['hotdel']['{$hotdeal_cnt}']['it_id'] = '{$data[it_id]}';
\$main['hotdel']['{$hotdeal_cnt}']['it_event_amount'] = '{$data[it_event_amount]}';
\$main['hotdel']['{$hotdeal_cnt}']['it_amount_msrp'] = '{$data[it_amount_msrp]}';
\$main['hotdel']['{$hotdeal_cnt}']['it_amount_msrp_krw'] = '{$msrp_krw}';
\$main['hotdel']['{$hotdeal_cnt}']['dc_per'] = '{$dc_per}';
\$main['hotdel']['{$hotdeal_cnt}']['img_link'] = '{$data[img_link]}';
\$main['hotdel']['{$hotdeal_cnt}']['end_fg'] = true;

EOL;



    }
}

# 전체 카테고리 데이터 로드 #
$sql = mysql_query("
	select
		a.s_id,a.ca_id,b.ca_name
	from
		shop_category a,
		yc4_category_new b
	where
		a.ca_id = b.ca_id
	order by
		a.s_id,a.sort
");
while($data = mysql_fetch_assoc($sql)){
    $data['ca_name'] = addslashes($data['ca_name']);
    $result .=<<<EOL

\$main['category']['{$data['s_id']}']['{$data['ca_id']}'] = "{$data['ca_name']}";
EOL;
}

# 관별 메인상품 데이터 로드 2015-03-30 홍민기 #
for($s=1; $s<=5; $s++){

    $sql = mysql_query("
        SELECT
            a.s_id,a.it_id,a.it_name,a.msrp,
            b.it_amount
        FROM
            yc4_station_main_item AS a,
            yc4_item AS b
        WHERE
            a.it_id = b.it_id
            AND
            a.s_id='".$s."'
            AND
            a.useyn='Y'
        ORDER BY a.sort
        limit 16
    ");
    $cnt = mysql_num_rows($sql);
    $view_cnt = floor($cnt/4) * 4;
    $i=0;
    while($data = mysql_fetch_assoc($sql)){
        $i++;
        if($i>$view_cnt){
            break;
        }
        $data['it_name'] = $data['it_name'];

        $result_arr['main_station_item'][$data['s_id']][$data['it_id']] = array('msrp'=>$data['msrp'],'it_name'=>$data['it_name'],'it_amount'=>$data['it_amount']);

        $result .=<<<EOL

\$main['main_station_item']['{$data['s_id']}']['{$data['it_id']}'] = array('msrp'=>"{$data['msrp']}",'it_name'=>"{$data['it_name']}",'it_amount'=>"{$data['it_amount']}");
EOL;
    }
}

# 메인 신상품 데이터 로드 2015-04-08 홍민기 #
$sql = mysql_query("
	select
    *
    from
		yc4_item_new a
	where
		use_fg = 'Y'
");

while($row = mysql_fetch_assoc($sql)){
    switch($row['type']){
        case 'I' :
            $new_item_data = mysql_fetch_assoc(mysql_query("select it_maker,it_amount,it_maker_kor,it_amount_usd from yc4_item where it_id ='".$row['type_value']."'"));
            break;
        case 'B' :
            $new_item_data = mysql_fetch_assoc(mysql_query("select it_maker,it_maker_kor from yc4_item where it_maker = '".$row['type_value']."' limit 1"));
            break;
    }
    $row['item_data'] = $new_item_data;
    $result_arr['main_new_item'][] = $row;

}

if($result_arr){
    $json = json_encode($result_arr);
    $file = fopen('/ssd/html/mall5/cache/main_cache_json.php','w');
    fwrite($file,$json);
    fclose($file);
}

if($result){
    $date = date('Y-m-d H:i:s');
    //$result = '<?php '.PHP_EOL.$result;
    $result = <<<EOL
<?php
# {$date} 생성 #

{$result}
EOL;

    $file = fopen('/ssd/html/mall5/cache/main_cache.php','w');
    fwrite($file,$result);
    fclose($file);

}
echo $result;
?>
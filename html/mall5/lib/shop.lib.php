<?php
header("Content-Type: text/html; charset=$g4[charset]");
//==============================================================================
// 쇼핑몰 함수 모음 시작
//==============================================================================

function count_naver_cpc($it_id)
{
	/* naver cpc count function ADD. 2015-03-24 17:21 이성용*/
	global $g4;
	$sql = " select count(*) as cnt from naver_cpc_counter where date=date_format(NOW(),'%Y%m%d') and it_id='$it_id' ";
    $row = sql_fetch($sql);
	if($row['cnt'] > 0){
		sql_query("update naver_cpc_counter set count=count+1 where date=date_format(NOW(),'%Y%m%d') and it_id='$it_id'");
	} else {
		sql_query("insert into naver_cpc_counter (date, it_id, count) values (date_format(NOW(),'%Y%m%d'),'$it_id',1)");
	}
}


// 장바구니 건수 검사
function get_cart_count($on_uid)
{
    global $g4;

    $sql = " select count(ct_id) as cnt from ".$g4['yc4_cart_table']." where on_uid = '".$on_uid."' ";
    $row = sql_fetch($sql);
    $cnt = (int)$row['cnt'];
    return $cnt;
}


// 상품 이미지를 얻는다 cdn/서버 분기 2014-06-24 홍민기
function get_it_image($img, $width=0, $height=0, $id=null, $attr = null,$detail_view=false,$link = true,$after_load=false)
{
    global $g4,$default;


	if(!$id){
		$attr_id = $img;
	}else{
		$attr_id = $id;
	}



    if($default['de_cdn']) {
        if (!$_SESSION['de_cdn']) { // 접속 지역에 따른 이미지 썸네일 로드 서버 분기(한국이 아니면 전부 미국에서 로드)

            $location_chk = file_get_contents('http://whois.kisa.or.kr/openapi/ipascc.jsp?query=' . $_SERVER['REMOTE_ADDR'] . '&key=2015042817320869610156&answer=json');
            $location_info = json_decode($location_chk);
            $location = $location_info->whois->countryCode;

            if ($location == 'KR') {
                $default['de_cdn'] = true;
                $_SESSION['de_cdn_set'] = true;
            } else {
                $default['de_cdn'] = false;
                $_SESSION['de_cdn_set'] = false;
            }
            $_SESSION['de_cdn'] = true;


        } else {
            $default['de_cdn'] = $_SESSION['de_cdn_set'];
        }
    }

	/*
	if($default['de_cdn']){// 이미지 서버에 이미지가 있는지 체크
		$full_img = "http://115.68.20.84/item/".$img;
		$img_header = get_headers($full_img);
		$img_header = $img_header[0];
		$img_header_arr = explode(' ',$img_header);

		$img_header = array_pop($img_header_arr);

		if($img_header == 'OK'){
			$str = "<img id='$id' src='".$full_img."' width='$width' height='$height' border='0'>";
		}
	}

	if($img_header != 'OK'){// 없거나 cd 체크가 되어 있지 않다면 웹서버 파일 검색
		$full_img = "$g4[path]/data/item/$img";

		if (file_exists($full_img) && $img){
			if (!$width)
			{
				$size = getimagesize($full_img);
				$width = $size[0];
				$height = $size[1];
			}
			$str = "<img id='$id' src='$g4[url]/data/item/$img' width='$width' height='$height' border='0'>";
		}
		else
		{
			$str = "<img id='$id' src='$g4[shop_img_url]/no_image.gif' border='0' ";
			if ($width)
				$str .= "width='$width' height='$height'";
			else
				$str .= "width='$default[de_mimg_width]' height='$default[de_mimg_height]'";
			$str .= ">";
		}
	}*/
	$src_tag = $after_load ? "data-original" : "src";
	if($default['de_cdn']){// 이미지 서버에 이미지가 있는지 체크
//		$full_img = "http://115.68.20.84/item/".$img;
		$full_img = "http://115.68.184.248/item/".$img;

		$str = "<img id='$attr_id' ".$src_tag."='".$full_img."' width='$width' height='$height' border='0' onerror=\"".(($detail_view)?"this.remove();":"this.src='".$g4['path']."/shop/img/no_image.gif'")."\" ".(($detail_view && $attr) ? $attr:"").">";
	}else{// 없거나 cd 체크가 되어 있지 않다면 웹서버 파일 검색
		$full_img = "$g4[path]/data/item/$img";


		if (file_exists($full_img) && $img){
			if (!$width)
			{
				$size = getimagesize($full_img);
				$width = $size[0];
				$height = $size[1];
			}
			if(!$link && $attr){
				$add_attr = $attr;
			}
			$str = "<img id='$attr_id' ".$src_tag."='$g4[url]/data/item/$img' width='$width' height='$height' border='0' ".$add_attr.">";
		}
		else
		{
			$str = "<img id='$attr_id' src='$g4[shop_img_url]/no_image.gif' border='0' ";
			if ($width)
				$str .= "width='$width' height='$height'";
			else
				$str .= "width='$default[de_mimg_width]' height='$default[de_mimg_height]'";
			if(!$link && $attr)
				$str .= " ".$attr;
			$str .= ">";
		}
	}


    if ($link) {
        $str = "<a href='http://ople.com/mall5/shop/item.php?it_id=$id' ".(($attr) ? $attr : '')." target='_blank'>$str</a>";
    }
    return $str;
}




// 이미지를 얻는다
function get_image($img, $width=0, $height=0)
{
	global $g4, $default;

    $full_img = "$g4[path]/data/item/$img";

    if (file_exists($full_img) && $img)
    {
        if (!$width)
        {
            $size = getimagesize($full_img);
            $width = $size[0];
            $height = $size[1];
        }
        $str = "<img id='$img' src='$g4[url]/data/item/$img' width='$width' height='$height' border='0'>";
    }
    else
    {
        $str = "<img id='$img' src='$g4[shop_img_url]/no_image.gif' border='0' ";
        if ($width)
            $str .= "width='$width' height='$height'";
        else
            $str .= "width='$default[de_mimg_width]' height='$default[de_mimg_height]'";
        $str .= ">";
    }


    return $str;
}

/*
// 상품 이미지를 얻는다
function get_it_image($img, $width=0, $height=0, $id="")
{
    global $g4;

    $str = get_image($img, $width, $height);
    if ($id) {
        $str = "<a href='$g4[shop_url]/item.php?it_id=$id'>$str</a>";
    }
    return $str;
}
*/
// 상품의 재고 (창고재고수량 - 주문대기수량)
function get_it_stock_qty($it_id,$adm=false)
{
    global $g4;

    if(!$adm) {
        return 9999;
    }

	# 클리어런스 상품 체크 2015-02-26 홍민기 #
	$clearance_chk = sql_fetch("
		select
			qty - sell_qty as qty
		from
			yc4_clearance_item
		where
			it_id = '".$it_id."'
	");
	if($clearance_chk){
		return $clearance_chk['qty'];
	}


    $sql = " select it_stock_qty from $g4[yc4_item_table] where it_id = '$it_id' ";
    $row = sql_fetch($sql);
    $jaego = (int)$row['it_stock_qty'];

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
               from $g4[yc4_cart_table]
              where it_id = '$it_id'
                and ct_stock_use = 0
                and ct_status in ('주문', '준비') ";
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
}

// 큰 이미지
function get_large_image($img, $it_id, $btn_image=true)
{
    global $g4;

    if (file_exists("$g4[path]/data/item/$img") && $img != "")
    {
        $size   = getimagesize("$g4[path]/data/item/$img");
        $width  = $size[0];
        $height = $size[1];
        $str = "<a href=\"javascript:popup_large_image('$it_id', '$img', $width, $height, '$g4[shop_path]')\">";
        if ($btn_image)
            //$str .= "<img src='$g4[shop_img_path]/btn_zoom.gif' border='0'></a>";
			$str .= "<img src='{$g4['path']}/images/category/category_buy_box01_wide.gif' width=89 height=24 hspace=6 border=0></a>";
    }
    else
        $str = "";
    return $str;
}

// 금액 표시
function display_amount($amount, $tel_inq=false)
{
	global $g4;
    if ($tel_inq)
        $amount = "전화문의";
    else
		// 김선용 : 아래 이미지는 메일발송시에도 공용으로 사용하므로 반드시 $g4['url'] 을 포함하여 절대경로로 입력
        //$amount = number_format($amount, 0) . "원";
		$amount =  "<img src='{$g4['url']}/images/main/main_product_w.gif' width=11 height=11 hspace=3 border='0' align=absmiddle>".number_format($amount, 0);

    return $amount;
}

function display_amount_usd($amount, $tel_inq=false)
{
	global $g4;
    if ($tel_inq)
        $amount = "전화문의";
    else
		// 김선용 : 아래 이미지는 메일발송시에도 공용으로 사용하므로 반드시 $g4['url'] 을 포함하여 절대경로로 입력
        //$amount = number_format($amount, 0) . "원";
		$amount =  number_format(usd_convert($amount), 2);

    return $amount;
}

// 금액표시
// $it : 상품 배열
function get_amount($it)
{
    global $g4,$member, $default, $no_discount_price_flag,$_HOTDEAL_FG;

    if ($it['it_tel_inq']) return '전화문의';



	// 김선용 201208 : lv 3, 4 할인처리
	// 홍민기 2014-05-15 선결제 포인트는 할인 제외
	if(in_array($member['mb_level'], array('3', '4')) && !in_array($it['it_id'],array('1306524520','1251860612','1222827644','1222682189','1210012129','1210591619')) && !$no_discount_price_flag)
	{
		$off_true = false;
		$off_arr = explode("|", $default['de_mb_level_off']);
        /*
        if($_SERVER['REMOTE_ADDR'] == '59.17.43.129') {
            foreach($off_arr as $val){
                list($lv,$off) = explode('=>',$val);
                if($lv == $member['mb_level'] && $off){
                    $amount = $it['it_amount'] * (1 - ($off / 100));
                    $off_true = true;
                    break;
                }
            }
        }else{
            for ($k = 3; $k < 5; $k++) {
                if (array_shift(explode('=>', $off_arr[($k - 3)])) == $member['mb_level'] && array_pop(explode('=>', $off_arr[($k - 3)]))) {
                    $off = array_pop(explode('=>', $off_arr[($k - 3)]));
                    $amount = $it['it_amount'] * (1 - ($off / 100));
                    $off_true = true;
                    break;
                }
            }
        }
        */
        foreach($off_arr as $val){
            list($lv,$off) = explode('=>',$val);
            if($lv == $member['mb_level'] && $off){
                $amount = $it['it_amount'] * (1 - ($off / 100));
                $off_true = true;
                break;
            }
        }
		if(!$off_true)
			$amount = $it[it_amount];
	}
	else
	{
		if ($member[mb_level] > 2) // 특별회원
			$amount = $it[it_amount3];

		if ($member[mb_level] == 2 || $amount == 0) // 회원가격
			$amount = $it[it_amount2];

		if ($member[mb_level] == 1 || $amount == 0) // 비회원가격
			$amount = $it[it_amount];

		if(manager_chk($member['mb_id'])){
			$amount = round($it['it_amount'] * 0.75 / 100) * 100;
		}
	}

	# 핫딜존 가격 로드 #
	if($_HOTDEAL_FG){
		$hotdeal_chk = sql_fetch("select it_event_amount from yc4_hotdeal_item where it_id = '".$it['it_id']."' and flag = 'Y' and sort > 0 and sort < 9");
		if($hotdeal_chk){
			$amount = $hotdeal_chk['it_event_amount'];
		}
	}

    return (int)$amount;
}


// 포인트 표시
function display_point($point)
{
	global $g4;
	// 김선용 : 아래 이미지는 메일발송시에도 공용으로 사용하므로 반드시 $g4['url'] 을 포함하여 절대경로로 입력
    //return number_format($point, 0) . "점";
	return "<img src='{$g4['url']}/images/main/main_product_p.gif' width=11 height=11 hspace=3 border='0' align=absmiddle>".number_format($point, 0);
}

// 포인트를 구한다
function get_point($amount, $point)
{
    return (int)($amount * $point / 100);
}

// HTML 특수문자 변환 htmlspecialchars
function htmlspecialchars2($str)
{
    $trans = array("\"" => "&#034;", "'" => "&#039;", "<"=>"&#060;", ">"=>"&#062;");
    $str = strtr($str, $trans);
    return $str;
}

// 파일을 업로드 함
function upload_file($srcfile, $destfile, $dir)
{
	if ($destfile == "") return false;
    // 업로드 한후 , 퍼미션을 변경함
	@move_uploaded_file($srcfile, "$dir/$destfile");
	@chmod("$dir/$destfile", 0644);
	return true;
}

// 유일키를 생성
function get_unique_id($len=32)
{
    global $g4;

    $result = @mysql_query(" LOCK TABLES $g4[yc4_on_uid_table] WRITE, $g4[yc4_cart_table] READ, $g4[yc4_order_table] READ ");
    if (!$result) {
        $sql = " CREATE TABLE `$g4[yc4_on_uid_table]` (
                    `on_id` int(11) NOT NULL auto_increment,
                    `on_uid` varchar(32) NOT NULL default '',
                    `on_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
                    `session_id` varchar(32) NOT NULL default '',
                    PRIMARY KEY  (`on_id`),
                    UNIQUE KEY `on_uid` (`on_uid`) ) ";
        sql_query($sql, false);
    }

    // 이틀전 자료는 모두 삭제함
	// 김선용 200804 : 1일로 수정, 최적화
    $ytime = date("Y-m-d", $g4['server_time'] - 86400 * 1);
    $sql = " delete from $g4[yc4_on_uid_table] where on_datetime < '$ytime' ";
    sql_query($sql);
	//sql_query("OPTIMIZE TABLE $g4[yc4_on_uid_table]");

    $unique = false;

    do {
        sql_query(" INSERT INTO $g4[yc4_on_uid_table] set on_uid = NOW(), on_datetime = NOW(), session_id = '".session_id()."' ", false);
        $id = @mysql_insert_id();
        $uid = md5($id);
        sql_query(" UPDATE $g4[yc4_on_uid_table] set on_uid = '$uid' where on_id = '$id' ");

        // 장바구니에도 겹치는게 있을 수 있으므로 ...
        $sql = "select COUNT(*) as cnt from $g4[yc4_cart_table] where on_uid = '$uid' ";
        $row = sql_fetch($sql);
        if (!$row[cnt]) {
            // 주문서에도 겹치는게 있을 수 있으므로 ...
            $sql = "select COUNT(*) as cnt from $g4[yc4_order_table] where on_uid = '$uid' ";
            $row = sql_fetch($sql);
            if (!$row[cnt])
                $unique = true;
        }
    } while (!$unique); // $unique 가 거짓인동안 실행

    @mysql_query(" UNLOCK TABLES ");

	return $uid;
}

// 주문서 번호를 얻는다.
function get_new_od_id()
{
    global $g4;
    /*

    // 주문서 테이블 Lock 걸고
    sql_query(" LOCK TABLES $g4[yc4_order_table] READ, $g4[yc4_order_table] WRITE ", FALSE);
    // 주문서 번호를 만든다.
    $date = date("ymd", time());    // 2002년 3월 7일 일경우 020307
    $sql = " select max(od_id) as max_od_id from $g4[yc4_order_table] where SUBSTRING(od_id, 1, 6) = '$date' ";
    $row = sql_fetch($sql);
    $od_id = $row[max_od_id];
    if ($od_id == 0)
        $od_id = 1;
    else {
        $od_id = (int)substr($od_id, -4);
        $od_id++;
    }
    $od_id = $date . substr("0000" . $od_id, -4);
    // 주문서 테이블 Lock 풀고
    sql_query(" UNLOCK TABLES ", FALSE);*/

    sql_query("INSERT INTO od_id_generator_" . date('Ymd') ." (order_date) VALUES ('". date('Y-m-d') ."')");
    $order_id_seq = mysql_insert_id();

    $od_id = date('ymd') . str_pad($order_id_seq, 4, '0',STR_PAD_LEFT);

    return $od_id;
}

function message($subject, $content, $align="left", $width="450")
{
	$str = "
	    <table width=$width cellpadding=4 align=center>
	        <tr><td class=line height=1></td></tr>
	        <tr>
	            <td align=center>$subject</td>
	        </tr>
	        <tr><td class=line height=1></td></tr>
	        <tr>
	            <td>
	                <table width=100% cellpadding=8 cellspacing=0>
	                    <tr>
	                        <td class=leading align=$align>$content</td>
	                    </tr>
	                </table>
	            </td>
	        </tr>
	        <tr><td class=line height=1></td></tr>
	    </table>
	    <br>
	    ";
    return $str;
}

// 시간이 비어 있는지 검사
function is_null_time($datetime)
{
	// 김선용 201207 : php v5.4 대체
	// 공란 0 : - 제거
	//$datetime = ereg_replace("[ 0:-]", "", $datetime);
	$datetime = preg_replace("/[\s0\:-]/", "", $datetime);
	if ($datetime == "")
	    return true;
	else
	    return false;
}

// 출력유형, 스킨파일, 1라인이미지수, 총라인수, 이미지폭, 이미지높이
// 1.02.01 $ca_id 추가
function display_type($type, $skin_file, $list_mod, $list_row, $img_width, $img_height, $ca_id="")
{
	global $member, $g4 ,$domain_flag;

    // 상품의 갯수
    $items = $list_mod * $list_row;

	// 김선용 201211 : 단종상품 미출력
    // 1.02.00
    // it_order 추가
    $sql = " select *
               from $g4[yc4_item_table]
              where it_use = '1' and it_discontinued=0
                and it_type{$type} = '1' ";
    if ($ca_id) $sql .= " and ca_id like '$ca_id%' ";
	// ople.co.kr 로 접속시 메인페이지에 숨길 제품들 표시 안함
	if ($domain_flag == 'kr') $sql .= $hide_caQ4.$hide_makerQ;
    $sql .= " order by it_order, it_id desc
              limit $items ";
    $result = sql_query($sql);
    if (!mysql_num_rows($result)) {
        return false;
    }

    $file = "$g4[shop_path]/$skin_file";
    if (!file_exists($file)) {
        echo "<span class=point>{$file} 파일을 찾을 수 없습니다.</span>";
    } else {
        $td_width = (int)(100 / $list_mod);
        include $file;
    }
}

// 분류별 출력
// 스킨파일번호, 1라인이미지수, 총라인수, 이미지폭, 이미지높이 , 분류번호
function display_category($no, $list_mod, $list_row, $img_width, $img_height, $ca_id="")
{
	global $member, $g4;

    // 상품의 갯수
    $items = $list_mod * $list_row;

	// 김선용 201211 : 단종상품 미출력
    $sql = " select * from $g4[yc4_item_table] where it_use = '1' and it_discontinued=0 ";
    if ($ca_id)
        $sql .= " and ca_id LIKE '{$ca_id}%' ";
    $sql .= " order by it_order, it_id desc limit $items ";
    $result = sql_query($sql);
    if (!mysql_num_rows($result)) {
        return false;
    }

    $file = "$g4[shop_path]/maintype{$no}.inc.php";
    if (!file_exists($file)) {
        echo "<span class=point>{$file} 파일을 찾을 수 없습니다.</span>";
    } else {
        $td_width = (int)(100 / $list_mod);
        include $file;
    }
}

// 별
function get_star($score)
{
    if ($score > 8) $star = "5";
    else if ($score > 6) $star = "4";
    else if ($score > 4) $star = "3";
    else if ($score > 2) $star = "2";
    else if ($score > 0) $star = "1";
    else $star = "5";

    return $star;
}

// 별 이미지
function get_star_image($it_id)
{
    global $g4;

    $sql = "select (SUM(is_score) / COUNT(*)) as score from $g4[yc4_item_ps_table] where it_id = '$it_id' ";
    $row = sql_fetch($sql);

    return (int)get_star($row[score]);
}

// 메일 보내는 내용을 HTML 형식으로 만든다.
function email_content($str)
{
    global $g4;

    $s = "";
    $s .= "<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset={$g4['charset']}\"><title>메일</title></head>\r\n";
    $s .= "<body>\r\n";
    $s .= $str;
    $s .= "\r\n</body>\r\n";
    $s .= "</html>";

    return $s;
}

// 타임스탬프 형식으로 넘어와야 한다.
// 시작시간, 종료시간
function gap_time($begin_time, $end_time)
{
    $gap = $end_time - $begin_time;
    $time[days]    = (int)($gap / 86400);
    $time[hours]   = (int)(($gap - ($time[days] * 86400)) / 3600);
    $time[minutes] = (int)(($gap - ($time[days] * 86400 + $time[hours] * 3600)) / 60);
    $time[seconds] = (int)($gap - ($time[days] * 86400 + $time[hours] * 3600 + $time[minutes] * 60));
    return $time;
}


// 공란없이 이어지는 문자 자르기 (wayboard 참고 (way.co.kr))
function continue_cut_str($str, $len=80)
{
	$pattern = "/[^\s\n<>]{".$len."}/";
	// 김선용 201207 : php v5.4 대체
    //return eregi_replace($pattern, "\\0\n", $str);
	return preg_replace($pattern, "\\0\n", $str);
}

// 제목별로 컬럼 정렬하는 QUERY STRING
// $type 이 1이면 반대
function title_sort($col, $type=0)
{
    global $sort1, $sort2;
    global $_SERVER;
    global $page;
    global $doc;

    $q1 = "sort1=$col";
    if ($type) {
        $q2 = "sort2=desc";
        if ($sort1 == $col) {
            if ($sort2 == "desc") {
                $q2 = "sort2=asc";
            }
        }
    } else {
        $q2 = "sort2=asc";
        if ($sort1 == $col) {
            if ($sort2 == "asc") {
                $q2 = "sort2=desc";
            }
        }
    }
    #return "$_SERVER[PHP_SELF]?$q1&$q2&page=$page";
    return "$_SERVER[PHP_SELF]?$q1&$q2&page=$page";
}


// 세션값을 체크하여 이쪽에서 온것이 아니면 메인으로
function session_check()
{
    global $g4;

    if (!trim(get_session('ss_on_uid')))
        gotourl("$g4[path]/");
}

// 상품 옵션
function get_item_options($subject, $option, $index)
{
    $subject = trim($subject);
    $option  = trim($option);

    if (!$subject || !$option) return "";

    $str = "";

    $arr = explode("\n", $option);
    // 옵션이 하나일 경우
    if (count($arr) == 1)
    {
        $str = $option;
    }
    else
    {
        $str = "<select name=it_opt{$index} onchange='amount_change()'>\n";
        for ($k=0; $k<count($arr); $k++)
        {
            $arr[$k] = str_replace("\r", "", $arr[$k]);
            $opt = explode(";", trim($arr[$k]));
            $str .= "<option value='$arr[$k]'>{$opt[0]}";
            // 옵션에 금액이 있다면
            if ($opt[1] != 0)
            {
                $str .= " (";
                // - 금액이 아니라면 모두 + 금액으로
                if (!preg_match("/[\-]/", $opt[1]))
                    $str .= "+";
                $str .= display_amount($opt[1]) . ")";
            }
            $str .= "</option>\n";
        }
        $str .= "</select>\n<input type=hidden name=it_opt{$index}_subject value='$subject'>\n";
    }

    return $str;
}

// 인수는 $it_id, $it_opt1, ..., $it_opt6 까지 넘어옴
function print_item_options()
{
    global $g4;

    $it_id = func_get_arg(0);
    $sql = " select it_opt1_subject,
                    it_opt2_subject,
                    it_opt3_subject,
                    it_opt4_subject,
                    it_opt5_subject,
                    it_opt6_subject
               from $g4[yc4_item_table]
              where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    $it_name = $str_split = "";
    for ($i=1; $i<=6; $i++)
    {
        $it_opt = trim(func_get_arg($i));
        // 상품옵션에서 0은 제외되는 현상을 수정
        if ($it_opt==null) continue;

        $it_name .= $str_split;
        $it_opt_subject = $it["it_opt{$i}_subject"];
        $opt = explode( ";", $it_opt );
        $it_name .= "&nbsp; $it_opt_subject = $opt[0]";

        if ($opt[1] != 0)
        {
            $it_name .= " (";
            if (preg_match("/[\+]/", $opt[1]) == true)
                $it_name .= "+";
            $it_name .= display_amount($opt[1]) . ")";
        }
        $str_split = "<br>";
    }

    return $it_name;
}

function it_name_icon($it, $it_name="", $url=1,$type='etc',$len=0)
{
    global $g4;

    $str = "";
    if ($it_name)
        $str = $it_name;
    else
        $str = stripslashes($it[it_name]);

	$str = get_item_name($str,$type,$len);

    if ($url)
        $str = "<a href='$g4[shop_path]/item.php?it_id=$it[it_id]'>$str</a>";

    if ($it[it_type1]) $str .= " <img src='$g4[shop_img_path]/icon_type1.gif' border=0 align=absmiddle>";
    if ($it[it_type2]) $str .= " <img src='$g4[shop_img_path]/icon_type2.gif' border=0 align=absmiddle>";
    if ($it[it_type3]) $str .= " <img src='$g4[shop_img_path]/icon_type3.gif' border=0 align=absmiddle>";
    if ($it[it_type4]) $str .= " <img src='$g4[shop_img_path]/icon_type4.gif' border=0 align=absmiddle>";
    if ($it[it_type5]) $str .= " <img src='$g4[shop_img_path]/icon_type5.gif' border=0 align=absmiddle>";

    // 품절
    $stock = get_it_stock_qty($it[it_id]);
    //if ($stock <= 0)
	if(!$it['it_maker']){
		$it_maker = sql_fetch("select it_maker from ".$g4['yc4_item_table']." where it_id = '".$it['it_id']."'");
		$it['it_maker'] = $it_maker['it_maker'];
	}
	if ($stock <= 0 && !in_array(trim($it['it_maker']),array('Nutramax pet','Nutramax') ) )
        $str .= " <img src='$g4[shop_img_path]/icon_pumjul.gif' border=0 align=absmiddle width=30 height=14> ";

    return $str;
}


function it_name_icon_oneday($it, $it2, $it_name="", $url=1,$type='etc',$len=0)
{
    global $g4;

    $str = "";
    if ($it_name)
        $str = $it_name;
    else
        $str = stripslashes($it[it_name]);

	$str = get_item_name($str,$type,$len);

    if ($url)
        $str = "<a href='$g4[shop_path]/item.php?it_id=$it[it_id]'>$str</a>";

    if ($it2[it_type1]) $str .= " <img src='$g4[shop_img_path]/icon_type1.gif' border=0 align=absmiddle>";
    if ($it2[it_type2]) $str .= " <img src='$g4[shop_img_path]/icon_type2.gif' border=0 align=absmiddle>";
    if ($it2[it_type3]) $str .= " <img src='$g4[shop_img_path]/icon_type3.gif' border=0 align=absmiddle>";
    if ($it2[it_type4]) $str .= " <img src='$g4[shop_img_path]/icon_type4.gif' border=0 align=absmiddle>";
    if ($it2[it_type5]) $str .= " <img src='$g4[shop_img_path]/icon_type5.gif' border=0 align=absmiddle>";

    // 품절
	$stockQ = sql_fetch("select real_qty-order_cnt as stock from yc4_oneday_sale_item where it_id = '".$it['it_id']."' ");
	$stock = $stockQ['stock'];
    if ($stock <= 0)
        $str .= " <img src='$g4[shop_img_path]/icon_pumjul.gif' border=0 align=absmiddle width=30 height=14> ";

    return $str;
}

// 일자형식변환
function date_conv($date, $case=1)
{
    if ($case == 1) { // 년-월-일 로 만들어줌
        $date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $date);
    } else if ($case == 2) { // 년월일 로 만들어줌
        $date = preg_replace("/-/", "", $date);
    }

    return $date;
}

// 배너출력
function display_banner($position, $num="")
{
    global $g4;

    if (!$position) $position = "왼쪽";

    include "$g4[shop_path]/boxbanner{$num}.inc.php";
}

// 1.00.02
// 파일번호, 이벤트번호, 1라인이미지수, 총라인수, 이미지폭, 이미지높이
// 1.02.01 $ca_id 추가
function display_event($no, $event, $list_mod, $list_row, $img_width, $img_height, $ca_id="")
{
	global $member, $g4, $new_year_item_set;

    // 상품의 갯수
    $items = $list_mod * $list_row;

    // 1.02.00
    // b.it_order 추가
    $sql = " select b.*
               from $g4[yc4_event_item_table] a,
                    $g4[yc4_item_table] b
              where a.it_id = b.it_id
                and b.it_use = '1'
                and a.ev_id = '$event' ";
    if ($ca_id) $sql .= " and ca_id = '$ca_id' ";
    $sql .= " order by ifnull(a.sort,999) asc, b.it_order, a.it_id desc
              limit $items ";

    $result = sql_query($sql);
    if (!mysql_num_rows($result)) {
        return false;
    }

    $file = "$g4[shop_path]/maintype{$no}.inc.php";
    if (!file_exists($file)) {
        echo "<span class=point>{$file} 파일을 찾을 수 없습니다.</span>";
    } else {
        $td_width = (int)(100 / $list_mod);
        include $file;
    }
}

function get_yn($val, $case='')
{
    switch ($case) {
        case '1' : $result = ($val > 0) ? 'Y' : 'N'; break;
        default :  $result = ($val > 0) ? '예' : '아니오';
    }
    return $result;
}

// 상품명과 건수를 반환
function get_goods($on_uid)
{
    global $g4;

    // 상품명만들기
    $row = sql_fetch(" select a.it_id, b.it_name from $g4[yc4_cart_table] a, $g4[yc4_item_table] b where a.it_id = b.it_id and a.on_uid = '$on_uid' order by ct_id limit 1 ");
    // 상품명에 "(쌍따옴표)가 들어가면 오류 발생함
    $goods[it_id] = $row[it_id];
    $goods[full_name]= $goods[name] = addslashes($row[it_name]);
    // 특수문자제거
    $goods[full_name] = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "",  $goods[full_name]);

    // 상품건수
    $row = sql_fetch(" select count(*) as cnt from $g4[yc4_cart_table] where on_uid = '$on_uid' ");
    $cnt = $row[cnt] - 1;
    if ($cnt)
        $goods[full_name] .= " 외 {$cnt}건";
    $goods[count] = $row[cnt];

    return $goods;
}

// 패턴의 내용대로 해당 디렉토리에서 정렬하여 <select> 태그에 적용할 수 있게 반환
function get_list_skin_options($pattern, $dirname="./")
{
    $str = "";

    unset($arr);
    $handle = opendir($dirname);
    while ($file = readdir($handle)) {
        if (preg_match("/$pattern/", $file, $matches)) {
            $arr[] = $matches[0];
        }
    }
    closedir($handle);

    sort($arr);
    foreach($arr as $key=>$value) {
        $str .= "<option value='$arr[$key]'>$arr[$key]</option>\n";
    }

    return $str;
}


// 일자 시간을 검사한다.
function check_datetime($datetime)
{
	if ($datetime == "0000-00-00 00:00:00")
	    return true;

    $year   = substr($datetime, 0, 4);
    $month  = substr($datetime, 5, 2);
    $day    = substr($datetime, 8, 2);
    $hour   = substr($datetime, 11, 2);
    $minute = substr($datetime, 14, 2);
    $second = substr($datetime, 17, 2);

    $timestamp = mktime($hour, $minute, $second, $month, $day, $year);

    $tmp_datetime = date("Y-m-d H:i:s", $timestamp);
    if ($datetime == $tmp_datetime)
        return true;
    else
        return false;
}


// 김선용 2012027: gift
function get_gift_check($amount, $on_uid)
{
	global $g4;

	$return_arr = array();
	$in_true = false;
	$sql = "select * from {$g4['yc4_gift_table']} where gift_st_time < '{$g4['time_ymdhis']}' and gift_ed_time > '{$g4['time_ymdhis']}' and gift_qty_all > gift_qty_now order by gift_id ";
	$result = sql_query($sql);
	while($row=sql_fetch_array($result))
	{
		if($row['gift_category'] == ''){ // 적용분류 없음
			if($row['gift_amount'] && $row['gift_amount2']){ // 금액 둘다 설정
				if($row['gift_amount'] <= $amount && $row['gift_amount2'] >= $amount) {
					$return_arr[] = $row['gift_id'];
					$in_true = true;
				}
			}else if($row['gift_amount']){ // 이상
				if($row['gift_amount'] <= $amount) {
					$return_arr[] = $row['gift_id'];
					$in_true = true;
				}
			}else if($row['gift_amount2']){ // 이하
				if($row['gift_amount2'] >= $amount){
					$return_arr[] = $row['gift_id'];
					$in_true = true;
				}
			}
		}else{ // 분류 합계. 분류는 하위포함
			// 주문서 작성시 한번만 적용되므로 상태에 따른 처리는 불필요. on_uid 로 처리
			$ct = sql_fetch("select sum(ct_amount*ct_qty) as amount_sum from {$g4['yc4_cart_table']} a
					left join {$g4['yc4_item_table']} b on a.it_id=b.it_id
					where a.on_uid='$on_uid' and b.ca_id like '{$row['gift_category']}%' ");
			if($row['gift_amount'] && $row['gift_amount2']){ // 금액 둘다 설정
				if($row['gift_amount'] <= $ct['amount_sum'] && $row['gift_amount2'] >= $ct['amount_sum']) {
					$return_arr[] = $row['gift_id'];
					$in_true = true;
				}
			}else if($row['gift_amount']){ // 이상
				if($row['gift_amount'] <= $ct['amount_sum']) {
					$return_arr[] = $row['gift_id'];
					$in_true = true;
				}
			}else if($row['gift_amount2']){ // 이하
				if($row['gift_amount2'] >= $ct['amount_sum']){
					$return_arr[] = $row['gift_id'];
					$in_true = true;
				}
			}
		}
		if($in_true){
			sql_query("update {$g4['yc4_gift_table']} set gift_qty_now=gift_qty_now+1 where gift_id='{$row['gift_id']}' ");
			$in_true = false;
		}
	}
	if(count($return_arr))
		return implode(';', $return_arr);
	else
		return;
}

function get_item_name($item_name,$type = 'etc',$len=0){
	/*
		2014-09-23 홍민기
		상품명 정규화
			영문제조사, 한글제조사||
			영문상품명, 용량/단위||
			한글상품명, 용량/단위||
			부가설명

		ex : get_item_name(상품명,타입)
		type : detail,list,etc
				detail : 상세피이지
				list : 상품리스트
				etc : 장바구니,주문서,후기 등등
	*/
	/*
	if($len){
		$item_name = conv_subject($item_name,$len,"…");
	}
	*/
	$it_name_arr = explode('||',$item_name);
	if(count($it_name_arr)>1){
		switch($type){
			case 'detail' :
				foreach($it_name_arr as $key => $val){
					if($val){
						switch($key){
							case 0 : $class='brand'; break;
							case 1 : $class='eng'; break;
							case 2 : $class='kor'; break;
							case 3 : $class='etc'; break;
							default : $class='etc'; break;
						}
						$it_name .= "<span class='item_name_detail item_name_".$class."_deatil'>".$val."</span>";
					}
				}
				break;
			case 'list' :
				preg_match("/\[([^{}]+)\]/i", $it_name_arr[0], $brand_nm_eng);
				$brand_nm_eng = "[".$brand_nm_eng[1]."]";


				if($len){
					$item_tmp_name = trim($brand_nm_eng).'||'.$it_name_arr[1].'||'.$it_name_arr[2].'||'.$it_name_arr[3];
					//$item_tmp_name = $it_name_arr[0].'||'.$it_name_arr[1].'||'.$it_name_arr[2].'||'.$it_name_arr[3];
					$item_tmp_name = conv_subject($item_tmp_name,$len,"…");
					$it_name_arr = explode('||',$item_tmp_name);
					if(is_array($it_name_arr)){
						foreach($it_name_arr as $val){
							$val = str_replace('|','',$val);
						}
					}
				}


				if($it_name_arr[0]){
					$it_name .= "<span class='item_name_list item_name_brand'>".trim($brand_nm_eng)."</span>";
					//$it_name .= "<span class='item_name_list item_name_brand'>".$it_name_arr[0]."</span>";
				}
				if($it_name_arr[1]){
					$it_name .= "<span class='item_name_list item_name_eng'>".$it_name_arr[1]."</span>";
				}
				if($it_name_arr[2]){
					$it_name .= "<span class='item_name_list item_name_kor'>".$it_name_arr[2]."</span>";
				}
				/*
				if($it_name_arr[3]){
					$it_name .= "<span class='item_name_list item_name_etc'>".$it_name_arr[3]."</span>";
				}
				*/
				break;
			case 'etc' :
				$it_name = str_replace('||',' ,',$item_name);
				break;
			case 'keyword' :
				$it_name = str_replace('||',' ',$item_name);
				break;
			case 'korshort' :
				$it_maker_kor_name = preg_replace("/\[([^{}]+)\]/i",'', $it_name_arr[0]);

				$it_name = "<span class='item_name_list item_name_brand'>".$it_maker_kor_name."</span><span class='item_name_list item_name_kor'>".$it_name_arr[1]."</span>";
				break;
            case 'excel_mode' :

                $it_name = $it_name_arr[1];
                break;
		}

		if(!$it_name){
			if($len>0){
				$it_name = conv_subject($item_name,$len,"…");
			}else{
				$it_name = $item_name;
			}
		}
	}else{
		if($len>0){
			$it_name = conv_subject($item_name,$len,"…");
		}else{
			$it_name = $item_name;
		}
	}

	return $it_name;
}

function manager_chk($mb_id = null){
	global $g4,$member;
	if(!$mb_id){
		$mb_id = $member['mb_id'];
	}
	$chk = sql_fetch("
		select count(*) as cnt from manager_id where mb_id = '".$mb_id."'
	");

	if($chk['cnt'] < 1){
		return false;
	}else{
		return true;
	}

}

# 할인율 퍼센트인지 구하는 함수 2015-01-23 홍민기 #
function get_dc_percent($dc_amount,$msrp_amount){
	return round(100 - ($dc_amount / $msrp_amount  * 100 ));
}



# 배송비 이벤트 2015-03-19 홍민기 #
function new_send_cost_chk($on_uid){
	global $g4,$default;

	/*
	$no_send_cost = $default['no_send_cost']; // 무료배송 금액


	$data = sql_fetch("
		select
			sum(ifnull(b.it_health_cnt,0) * a.ct_qty) as tot_health_cnt,
			sum(a.ct_qty * a.ct_amount) as tot_amount
		from
			".$g4['yc4_cart_table']." a,
			".$g4['yc4_item_table']." b
		where
			a.it_id = b.it_id
			and
			a.on_uid = '".$on_uid."'
	");




	$send_cost = old_send_cost_chk($data['tot_amount']);
	$send_promo = false;
	$req_send_usd = usd_convert($no_send_cost); // 무료배송 필요 주문금액 달러
	$send_cost_usd = usd_convert($data['tot_amount']); // 주문금액 합계(달러)

	# 건기식이 6병 이하일 경우 7만원 이상은 무조건 무료배송 #
	if($data['tot_health_cnt'] <= $default['no_send_cost_health_cnt']){
		if($data['tot_amount'] >= $no_send_cost || $send_cost_usd >= $req_send_usd){
			$send_cost = 0;
			$send_promo = true;
		}

	}


	return array(
		'send_cost' => $send_cost, // 배송비
		'send_promo_fg' => $send_promo, // 프로모션 적용 여부
		'req_send_usd' => $req_send_usd, // 필요 주문금액(달러)
		'send_cost_usd' => $send_cost_usd, // 상품금액(달러)
		'health_cnt' => $data['tot_health_cnt'],
		'tot_amount' => $data['tot_amount']
	);
	*/
	$no_send_cost = 40 * $default['de_conv_pay']; // 40달러 이상 무료배송


	# 상품 총 금액 로드 #
	$data = sql_fetch("
		select
			sum(ifnull(b.it_health_cnt,0) * a.ct_qty) as tot_health_cnt,
			sum(a.ct_qty * a.ct_amount) as tot_amount
		from
			".$g4['yc4_cart_table']." a,
			".$g4['yc4_item_table']." b
		where
			a.it_id = b.it_id
			and
			a.on_uid = '".$on_uid."'
	");
	
	# 포인트 결제금액 로드 #
	$point_amount = sql_query("
		select od_temp_point from ".$g4['yc4_order_table']." where on_uid = '".$on_uid."'
	");
	$point_amount = $point_amount['od_temp_point'];



	$send_cost = old_send_cost_chk($data['tot_amount']);
	$send_promo = false;
	$req_send_usd = usd_convert($no_send_cost); // 무료배송 필요 주문금액 달러


	# 포인트 결제 제외 상품 총 결제금액 #
	$send_cost_usd = usd_convert($data['tot_amount'] - $point_amount); // 주문금액 합계(달러)


	# 건기식이 6병 이하일 경우 7만원 이상은 무조건 무료배송 #
	if($data['tot_health_cnt'] <= $default['no_send_cost_health_cnt']){
		if($data['tot_amount'] >= $no_send_cost || $send_cost_usd >= $req_send_usd){
			$send_cost = 0;
			$send_promo = true;
		}

	}


	return array(
		'send_cost' => $send_cost, // 배송비
		'send_promo_fg' => $send_promo, // 프로모션 적용 여부
		'req_send_usd' => $req_send_usd, // 필요 주문금액(달러)
		'send_cost_usd' => $send_cost_usd, // 상품금액(달러)
		'health_cnt' => $data['tot_health_cnt'],
		'tot_amount' => $data['tot_amount']
	);
}

# 기존 배송비 정책 #
function old_send_cost_chk($amount){
	global $default;

	$send_cost_limit = explode(";", $default['de_send_cost_limit']);
	$send_cost_list  = explode(";", $default['de_send_cost_list']);
	$send_cost_limit_cnt = count($send_cost_limit);
	$send_cost = 0;
	for ($k=0; $k<$send_cost_limit_cnt; $k++) {
		// 총판매금액이 배송비 상한가 보다 작다면
		if ($amount < $send_cost_limit[$k]) {
			$send_cost = $send_cost_list[$k];
			break;
		}
	}

	return $send_cost;
}

# 셋트상품 원래가격 구하는 함수 2015-02-03 홍민기 #
function set_item_ori_amount($it_id){
	global $g4;
	$it = sql_fetch("
		select
			it_id,it_amount,it_tel_inq
		from
			".$g4['yc4_item_table']."
		where
			it_id = '".$it_id."'
		");

	$it_amount = get_amount($it);

	$set_item_chk = sql_query("
		select
			a.child_it_id as it_id,a.child_qty,b.it_amount
		from
			yc4_item_set a
			left join
			".$g4['yc4_item_table']." b on a.child_it_id = b.it_id
		where
			a.it_id = '".$it_id."'

	");

	$ori_it_amount = 0;
	$child_it_id_arr = array();
	while($row = sql_fetch_array($set_item_chk)){
		$ori_it_amount += get_amount($row) * $row['child_qty'];
		$child_it_id_arr[] = $row['it_id'];
	}
	if($it_id == '1417404352'){
		$ori_it_amount = 8400 * 2;
	}
	if($ori_it_amount == 0){
		return false;
	}
	$dc_per = get_dc_percent($it_amount,$ori_it_amount);

	return array('it_amount'=>$it_amount,'ori_it_amount'=>$ori_it_amount,'dc_per'=>$dc_per,'child_it_id_arr'=>$child_it_id_arr);
}

# 클리어런스 상품 재고차감 2015-02-26 홍민기 #
function clearance_sell_qty($on_uid){
	global $g4;
	$sql = sql_query("
		select
			a.it_id,b.ct_qty,b.ct_status
		from
			yc4_clearance_item a,
			".$g4['yc4_cart_table']." b
		where
			a.it_id = b.it_id
			and
			b.on_uid = '".$on_uid."'
	");
	while($row = sql_fetch_array($sql)){
		$continue = false;
		switch($row['ct_status']){
			case '준비' : $ct_qty = $row['ct_qty'];
				break;
			case '취소' : $ct_qty = $row['ct_qty'] * -1;
				break;
			default : $continue = true;
				break;
		}
		if($continue){
			continue;
		}
		sql_query("
			update
				yc4_clearance_item
			set sell_qty = sell_qty + ".(int)$ct_qty."
			where it_id = '".$row['it_id']."'
		");
		$qty_chk = sql_fetch("
			select
				qty - sell_qty as qty
			from
				yc4_clearance_item
			where it_id = '".$row['it_id']."'
		");
		$qty = $qty_chk['qty'];
		if($qty_chk['qty'] <= 0){
			sql_query("
				update
					yc4_clearance_item
				set soldout_dt = '".$g4['time_ymdhis']."'
				where it_id = '".$row['it_id']."'
			");
		}
	}
}


# 장바구니에 선결제 포인트가 있는지 체크 #
function point_pay_chk($on_uid){
	global $g4;

	$sql = sql_fetch("select count(*) as cnt from ".$g4['yc4_cart_table']." where on_uid = '".$on_uid."' and it_id in ('1210012129', '1210591619', '1222682189', '1222827644', '1251860612', '1306524520')");



	if($sql['cnt'] > 0){
		return false;
	}else{
		return true;
	}		
}




function first_buy_chk($mb_id,$on_uid = null){
	global $g4,$member;


	if(!$member['mb_id']){
		return false;	
	}

	if($on_uid){
		$sql_add = " and a.on_uid != '".$on_uid."'";
	}else{
		$sql_add = "";
	}	

	$sql = sql_fetch("
		select
			count( distinct a.od_id ) as cnt
		from
			yc4_order a
			left join yc4_cart b on a.on_uid = b.on_uid
		where
			a.mb_id = '".$member['mb_id']."'
			and b.ct_status != '취소'
		".$sql_add
	);



	if($sql['cnt']> 0){
		return false;
	}else{
		return true;
	}
}


# 첫 구매시 혜택 이벤트 #
function first_buy_promo($on_uid){
	global $g4,$member;



	if(date('Ymd') > 20150430){
		return false;
	}




	//if(first_buy_chk($member['mb_id']) && point_pay_chk($on_uid)){
	if(point_pay_chk($on_uid)){
		
		# 결제 예샹금액 체크 #

		$sql = sql_fetch("select od_temp_bank + od_temp_card as tot_amount from ".$g4['yc4_order_table']." where on_uid = '".$on_uid."'");

		$tot_amount = $sql['tot_amount'];
		$tot_amount = usd_convert($tot_amount);

		if($tot_amount < 50){
			return false;
		}

		$dc_amount = round($sql['tot_amount'] * 0.1);

		$update_sql = "update ".$g4['yc4_order_table']." set od_dc_amount = '".$dc_amount."', od_shop_memo = concat(od_shop_memo,'\\n','첫 구매시 할인 혜택 ".$dc_amount."할인') where on_uid = '".$on_uid."'";

		/*
		if($_SERVER['REMOTE_ADDR'] == '59.17.43.129'){
			echo $update_sql;
			exit;
		}
		*/



		if(sql_query($update_sql)){
			return true;
		}else{
			return false;
		}

	
	}else{
		return false;
	}

}


function main_best_item($it_id,$it_name,$msrp,$first_fg=false){
    global $g4,$default;
    $it = sql_fetch("select it_amount from ".$g4['yc4_item_table']." where it_id = '".$it_id."'");

    $it_amount_usd = round($it['it_amount'] / $default['de_conv_pay'],2);
    $msrp_per = round(($msrp - $it_amount_usd) / $msrp * 100);
    //$msrp_per = $msrp - $it_amount_usd;
    $it_link = $g4['shop_path'].'/item.php?it_id='.$it_id;

    $html = "
        <li ".($first_fg ? "class='first'":"").">
            <span class='discount_box'><strong>".$msrp_per."%</strong></span>
            <a href='".$it_link."'>
                ".get_it_image($it_id.'_s',175,175,null,null,null,null,null)."
            </a>
            <span class='best_title'><a href='".$it_link."'>".$it_name."</a></span>
            <span class='best_price'><em class='msrp_price'>$ ".number_format($msrp,2)."</em> <strong>$ ".number_format($it_amount_usd,2)."</strong><em class='won_price'>(￦ ".number_format($it['it_amount']).")</em></span>
        </li>
    ";

    return $html;
}

function nordic_tot_event_chk($on_uid){
    global $g4;

    $sql = sql_fetch("
        select
          sum(a.ct_qty * a.ct_amount) as amount
        from
            ".$g4['yc4_cart_table']." a,
            ".$g4['yc4_item_table']." b
        where
            a.it_id = b.it_id
            and b.it_maker = 'Nordic Naturals'
            and a.on_uid = '".$on_uid."'
    ");

    if($sql['amount'] >= 80000){
        $od = sql_fetch("select od_id from ".$g4['yc4_order_table']." where on_uid = '".$on_uid."'");

        # 중복 적용 방지 #
        $sql2 = sql_fetch("
            select count(*) as cnt
            from yc4_event_data
            WHERE
                ev_code = 'nordic'
                AND ev_data_type = 'tot_order'
                AND value1 = '".$on_uid."'
        ");

        if($sql2['cnt']>0){
            return false;
        }
        return true;
    }




    return false;
}


/*
노르딕 토트 우산 증정 이벤트
주문서 저장시 처리됨
*/
function nordic_tot_event($on_uid){
    global $g4;
    if(!nordic_tot_event_chk($on_uid)){
        return false;
    }
    $od = sql_fetch("
      select od_id,on_uid,mb_id
      from ".$g4['yc4_order_table']."
      where on_uid = '".$on_uid."'
    ");

    $insert_sql = "
        insert into yc4_event_data (ev_code,ev_data_type,value1,value2)
        values ('nordic','tot_order','".$od['od_id']."','".$od['mb_id']."')
    ";

    sql_query($insert_sql);

    sql_query("
        insert into
            ".$g4['yc4_cart_table']."
        set
            on_uid = '".$on_uid."',
            it_id = '1408688719',
            it_opt1 = '',
            it_opt2 = '',
            it_opt3 = '',
            it_opt4 = '',
            it_opt5 = '',
            it_opt6 = '',
            ct_status = '주문',
            ct_history = '노르딕 이벤트 사은품',
            ct_amount = 0,
            ct_point = 0,
            ct_point_use = 0,
            ct_stock_use = 0,
            ct_qty = 1,
            ct_time = '".date('Y-m-d H:i:s')."',
            ct_ip = '".$_SERVER['REMOTE_ADDR']."',
            ct_send_cost = '',
            ct_mb_id = '".$od['mb_id']."',
            ct_ship_os_pid = '',
            ct_ship_ct_qty = '',
            ct_ship_stock_use = ''
    ");

    sql_query("
        update ".$g4['yc4_order_table']." set
        od_shop_memo = concat(od_shop_memo,'\\n','노르딕 8만원 이상 구매 사은품 증정')
        where on_uid = '".$on_uid."'
    ");

}

//==============================================================================
// 쇼핑몰 함수 모음 끝
//==============================================================================
?>
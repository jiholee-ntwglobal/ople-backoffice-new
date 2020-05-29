<?php
$sub_menu = "300100";
include_once("./_common.php");
include_once $g4['full_path']."/lib/icode.sms.lib.php";
include_once $g4['full_path']."/lib/opk_db.php";
$opk_db = new opk_db;
if ($w == "u" || $w == "d")
    check_demo();

if ($w == '' || $w == 'u')
    auth_check($auth[$sub_menu], "w");
else if ($w == 'd')
    auth_check($auth[$sub_menu], "d");

$usd_input_exception = array('1511056740','1222682189','1511056780');

if(!(float)$it_amount_usd && !in_array($it_id, $usd_input_exception)){
    alert('상품 금액이 입력되지 않았습니다.');
    exit;
}

// 상품삭제
// 메세지출력후 주문개별내역페이지로 이동
function itemdelete($it_id)
{
    return false;
    /* // 상품 삭제기능 삭제 2017-03-08 강경인
    global $g4, $is_admin,$opk_db;
    $str = $comma = $od_id = "";
    $sql = " select b.od_id
               from $g4[yc4_cart_table] a,
                    $g4[yc4_order_table] b
              where a.on_uid = b.on_uid
                and a.it_id = '$it_id'
                and a.ct_status != '쇼핑' ";
    $result = sql_query($sql);
    $i=0;
    while ($row = sql_fetch_array($result))
    {
        if (!$od_id)
            $od_id = $row[od_id];

        $i++;
        if ($i % 10 == 0) $str .= "\\n";
        $str .= "$comma$row[od_id]";
        $comma = " , ";
    }
    if ($str)
    {
        alert("이 상품과 관련된 주문이 총 {$i} 건 존재하므로 주문서를 삭제한 후 상품을 삭제하여 주십시오.\\n\\n$str", "./orderstatuslist.php?sort1=od_id&sel_field=od_id&search=$od_id");
    }
	// 상품 이미지 삭제
    @unlink("$g4[path]/data/item/$it_id"."_s");
    @unlink("$g4[path]/data/item/$it_id"."_m");
    @unlink("$g4[path]/data/item/$it_id"."_l1");
    @unlink("$g4[path]/data/item/$it_id"."_l2");
    @unlink("$g4[path]/data/item/$it_id"."_l3");
    @unlink("$g4[path]/data/item/$it_id"."_l4");
    @unlink("$g4[path]/data/item/$it_id"."_l5");
    // 상, 하단 이미지 삭제
    @unlink("$g4[path]/data/item/$it_id"."_h");
    @unlink("$g4[path]/data/item/$it_id"."_t");

    // 장바구니 삭제
	$sql = " delete from $g4[yc4_cart_table] where it_id = '$it_id' ";
	sql_query($sql);
    $opk_db->query($sql);
    // 이벤트삭제
    $sql = " delete from $g4[yc4_event_item_table] where it_id = '$it_id' ";
	sql_query($sql);
    $opk_db->query($sql);
    // 사용후기삭제
    $sql = " delete from $g4[yc4_item_ps_table] where it_id = '$it_id' ";
	sql_query($sql);
    $opk_db->query($sql);
    // 상품문의삭제
    $sql = " delete from $g4[yc4_item_qa_table] where it_id = '$it_id' ";
	sql_query($sql);
    $opk_db->query($sql);
    // 관련상품삭제
    $sql = " delete from $g4[yc4_item_relation_table] where it_id = '$it_id' or it_id2 = '$it_id' ";
	sql_query($sql);
    $opk_db->query($sql);
    //------------------------------------------------------------------------
    // HTML 내용에서 에디터에 올라간 이미지의 경로를 얻어 삭제함
    //------------------------------------------------------------------------
    $sql = " select * from $g4[yc4_item_table] where it_id = '$it_id' ";
    $it = sql_fetch($sql);
    $s = $it[it_explan];
    $img_file = Array();
    while($s) {
        $pos = strpos($s, "/data/cheditor");
        $s = substr($s, $pos, strlen($s));
        $pos = strpos($s, '"');
        // 결과값
        $file_path = substr($s, 0, $pos);
        if (!$file_path) break;
        $img_file[] = $file_path;
        $s = substr($s, $pos, strlen($s));
    }
    for($i=0;$i<count($img_file);$i++) {
        $f = $g4[path].$img_file[$i];
        if (file_exists($f))
            @unlink($f);
    }
    //------------------------------------------------------------------------
    // 상품 삭제
	$sql = " delete from $g4[yc4_item_table] where it_id = '$it_id' ";
	sql_query($sql);
    $opk_db->query($sql);
    */
}


//------------------------------------------------------------------------------
// 금액 오류 검사
$line1 = true;
$cnt = 0;
if ($w == "" || $w == "u")
{
    for ($i=1; $i<=6; $i++)
    {
        $it_opt = $_POST["it_opt{$i}"];
        unset($opt);
        $opt = explode("\n", $it_opt);
        for ($k=0; $k<count($opt); $k++)
        {
            // 첫라인에는 금액옵션을 줄 수 없음
            if ($k == 0)
            {
                // 첫라인에 '셑'과 같은 문자를 입력할 수 없음
                // if (preg_match("/;/", $opt[$k])) {
                if (!preg_match("/&/", $opt[$k]) && preg_match("/;/", $opt[$k]))
                {
                    $line1 = false;
                    break;
                }
            }

            // 옵션금액에 + 또는 - 부호가 없다면 오류
            unset($exp);
            $exp = explode(";", $opt[$k]);
            if ($exp[1] > 0)
            {
                if (!preg_match("/^([+|-])/", $exp[1])) {
                    $cnt++;
                    break;
                }
            }
        }
    }
}

if (!$line1) {
    alert("옵션의 첫라인에는 금액을 입력할 수 없습니다.");
}

if ($cnt > 0) {
    alert("옵션의 금액 입력 오류입니다.\\n\\n추가되는 금액은 + 부호를\\n\\n할인되는 금액은 - 부호를 붙여 주십시오.");
}
//------------------------------------------------------------------------------

/* // 이미지 관련 작업 삭제 2017-03-08 강경인
@mkdir("$g4[path]/data/item", 0707);
@chmod("$g4[path]/data/item", 0707);

if ($it_himg_del)  @unlink("$g4[path]/data/item/{$it_id}_h");
if ($it_timg_del)  @unlink("$g4[path]/data/item/{$it_id}_t");
if ($it_simg_del)  @unlink("$g4[path]/data/item/{$it_id}_s");
if ($it_mimg_del)  @unlink("$g4[path]/data/item/{$it_id}_m");
if ($it_limg1_del) @unlink("$g4[path]/data/item/{$it_id}_l1");
if ($it_limg2_del) @unlink("$g4[path]/data/item/{$it_id}_l2");
if ($it_limg3_del) @unlink("$g4[path]/data/item/{$it_id}_l3");
if ($it_limg4_del) @unlink("$g4[path]/data/item/{$it_id}_l4");
if ($it_limg5_del) @unlink("$g4[path]/data/item/{$it_id}_l5");

$conn_id = @ftp_connect($g4['front_ftp_server']);
$login_result = @ftp_login($conn_id, $g4['front_ftp_user_name'], $g4['front_ftp_user_pass']);

// 이미지(대)만 업로드하고 자동생성 체크일 경우 이미지(중,소) 자동생성
if ($createimage && $_FILES[it_limg1][name]){
    upload_file($_FILES[it_limg1][tmp_name], $it_id."_l1", "$g4[path]/data/item");

    $image = "$g4[path]/data/item/$it_id"."_l1";
    $size = getimagesize($image);
    $src = @imagecreatefromjpeg($image);
	@ftp_fput($conn_id, "/ssd/ople_data/data/item/$it_id"."_l1", $_FILES[it_limg1][tmp_name], FTP_BINARY);

    if (!$src)    {
        echo "<script>alert('이미지(대)가 JPG 파일이 아닙니다.');</script>";
    }
    else    {
        // gd 버전에 따라
        if (function_exists("imagecopyresampled")) {
            // 이미지(소) 생성
            $dst = imagecreatetruecolor($default[de_simg_width], $default[de_simg_height]);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $default[de_simg_width], $default[de_simg_height], $size[0], $size[1]);
        } else {
            // 이미지(소) 생성
            $dst = imagecreate($default[de_simg_width], $default[de_simg_height]);
            imagecopyresized($dst, $src, 0, 0, 0, 0, $default[de_simg_width], $default[de_simg_height], $size[0], $size[1]);
        }
        imagejpeg($dst, "$g4[path]/data/item/$it_id"."_s", 90);
		@ftp_fput($conn_id, "/ssd/ople_data/data/item/$it_id"."_s", $_FILES[it_limg1][tmp_name], FTP_BINARY);

        if (function_exists("imagecopyresampled")) {
            // 이미지(중) 생성
            $dst = imagecreatetruecolor($default[de_mimg_width], $default[de_mimg_height]);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $default[de_mimg_width], $default[de_mimg_height], $size[0], $size[1]);
        } else {
            // 이미지(중) 생성
            $dst = imagecreate($default[de_mimg_width], $default[de_mimg_height]);
            imagecopyresized($dst, $src, 0, 0, 0, 0, $default[de_mimg_width], $default[de_mimg_height], $size[0], $size[1]);
        }
        @imagejpeg($dst, "$g4[path]/data/item/$it_id"."_m", 90);
		@ftp_fput($conn_id, "/ssd/ople_data/data/item/$it_id"."_m", $_FILES[it_limg1][tmp_name], FTP_BINARY);
    }
}
ftp_close($conn_id);
*/

if ($w == "" || $w == "u")
{
    // 다음 입력을 위해서 옵션값을 쿠키로 한달동안 저장함
    //@setcookie("ck_ca_id",  $ca_id,  time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
    //@setcookie("ck_maker",  stripslashes($it_maker),  time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
    //@setcookie("ck_origin", stripslashes($it_origin), time() + 86400*31, $default[de_cookie_dir], $default[de_cookie_domain]);
    @set_cookie("ck_ca_id", $ca_id, time() + 86400*31);
    @set_cookie("ck_ca_id2", $ca_id2, time() + 86400*31);
    @set_cookie("ck_ca_id3", $ca_id3, time() + 86400*31);
	// 김선용 201211 :
    //@set_cookie("ck_maker", stripslashes($it_maker), time() + 86400*31);
	@set_cookie("ck_maker", $it_maker, time() + 86400*31);
    @set_cookie("ck_origin", stripslashes($it_origin), time() + 86400*31);
}


// 관련상품을 우선 삭제함
sql_query(" delete from $g4[yc4_item_relation_table] where it_id = '$it_id' ");
$opk_db->query(" delete from $g4[yc4_item_relation_table] where it_id = '$it_id' ");
// 관련상품의 반대도 삭제
sql_query(" delete from $g4[yc4_item_relation_table] where it_id2 = '$it_id' ");
$opk_db->query(" delete from $g4[yc4_item_relation_table] where it_id2 = '$it_id' ");
// 이벤트상품을 우선 삭제함
sql_query(" delete from $g4[yc4_event_item_table] where it_id = '$it_id' ");
$opk_db->query(" delete from $g4[yc4_event_item_table] where it_id = '$it_id' ");



$it_cust_amount = $it_cust_amount_usd ? round($it_cust_amount_usd * $default['de_conv_pay']) : $it_cust_amount;
//$it_amount = ($_POST['it_amount_usd']!="" || $_POST['it_amount_usd']==0 || $_POST['it_amount_usd']=="0.00") ? $_POST['it_amount'] : round($it_amount_usd * $default['de_conv_pay']) ;

if(in_array($it_id, $usd_input_exception)) {
    $it_amount = ($_POST['it_amount_usd']!="" || $_POST['it_amount_usd']==0 || $_POST['it_amount_usd']=="0.00") ? $_POST['it_amount'] : round($it_amount_usd * $default['de_conv_pay']) ;
}else{
    $it_amount = $it_amount_usd ? round($it_amount_usd * $default['de_conv_pay']) : $it_amount;

}
$sql_common = " ca_id            = '$ca_id',
                ca_id2           = '$ca_id2',
                ca_id3           = '$ca_id3',
                it_name          = '$it_name',
                it_gallery       = '$it_gallery',
                it_maker         = '$it_maker',
                it_origin        = '$it_origin',
                it_opt1_subject  = '$it_opt1_subject',
                it_opt2_subject  = '$it_opt2_subject',
                it_opt3_subject  = '$it_opt3_subject',
                it_opt4_subject  = '$it_opt4_subject',
                it_opt5_subject  = '$it_opt5_subject',
                it_opt6_subject  = '$it_opt6_subject',
                it_opt1          = '$it_opt1',
                it_opt2          = '$it_opt2',
                it_opt3          = '$it_opt3',
                it_opt4          = '$it_opt4',
                it_opt5          = '$it_opt5',
                it_opt6          = '$it_opt6',
                it_type1         = '$it_type1',
                it_type2         = '$it_type2',
                it_type3         = '$it_type3',
                it_type4         = '$it_type4',
                it_type5         = '$it_type5',
                it_basic         = '$it_basic',
                it_explan        = '$it_explan',
                it_explan_html   = '$it_explan_html',
                it_cust_amount   = '$it_cust_amount',
                it_amount        = '$it_amount',
                it_amount2       = '$it_amount2',
                it_amount3       = '$it_amount3',
                it_point         = '$it_point',
				ihappy_amount	 = '$ihappy_amount',
                it_sell_email    = '$it_sell_email',
                it_use           = '$it_use',
                it_stock_qty     = '$it_stock_qty',
                it_head_html     = '$it_head_html',
                it_tail_html     = '$it_tail_html',
                it_time          = '$g4[time_ymdhis]',
                it_ip            = '$_SERVER[REMOTE_ADDR]',
                it_order         = '$it_order',
                it_tel_inq       = '$it_tel_inq',
				/* 김선용 200812 */
				SKU = '$SKU',
				ca_id4 = '$ca_id4',
				ca_id5 = '$ca_id5',
				it_order_onetime_limit_cnt = '$it_order_onetime_limit_cnt',
				/* // 김선용 201210 : */
				it_health_cnt = trim('$it_health_cnt'),
				it_discontinued = '$it_discontinued', /* 단종 */
				/* // 김선용 201306 : seo */
				it_meta_title		= trim('$it_meta_title'),
				it_meta_description	= '$it_meta_description',
				it_meta_keyword		= trim('$it_meta_keyword'),
				it_meta_h1			= trim('$it_meta_h1'),
				it_amount_usd = '".$it_amount_usd."',
				it_cust_amount_usd = '".$it_cust_amount_usd."'
                ";
$opk_amount = round($it_amount*1.05,-2);
if ($w == "")
{
    if (!trim($it_id)) {
        alert("상품 코드가 없으므로 상품을 추가하실 수 없습니다.");
    }

    $sql = " insert $g4[yc4_item_table]
                set it_id = '$it_id',
                it_create_time='$g4[time_ymdhis]',
					$sql_common	";
    sql_query($sql);
    $opk_db->query($sql);
    $opk_db->query("update {$g4['yc4_item_table']} set it_amount = '".$opk_amount."' where it_id = '".$it_id."'");
}
else if ($w == "u")
{

	# 네이버에 상품 수정 테이블에 해당상품이 처리되지 않은 데이터가 있다면 update else insert
	$naver_brief_chk = sql_fetch("select uid from naver_ep_brief where it_id = '".$it_id."' and generate_time is null");

	if($naver_brief_chk['uid']){
		sql_query("
			update
				naver_ep_brief
			set
				update_yn = 'Y'
			where
				uid = '".$naver_brief_chk['uid']."'
		");
	}else{
		sql_query("
			insert into
				naver_ep_brief
			(it_id,update_yn,create_date)
			values
			('".$it_id."','Y','".$g4['time_ymdhis']."')
		");
		$naver_brief_chk['uid'] = mysql_insert_id();
	}

	// 김선용 201211 : 단종제외 처리
	// 김선용 201211 : 1차 재고수정여부 확인
 	// 김선용 201208 : 상품입고 sms처리, 발송시간은 오전09~오후21시 사이를 기준(새벽에 상품수정시 sms발송을 할 수 없으므로)
	// 09~21 시 사이만 발송
	if(!$_POST['it_discontinued'] && $_POST['it_stock_qty_old'] < 1 && $_POST['it_stock_qty'] > 0){ // 단종제외, 기존재고가 1미만이고 입력한 재고가 1이상이면
		it_sms_send($it_id, $_POST['it_stock_qty']);
		$soldout_flag = 'i';
        $soldout_fg = 'N';
		sql_query("
			update
				naver_ep_brief
			set
				resume_yn = 'Y',
				pause_yn = null
			where
				uid = '".$naver_brief_chk['uid']."'
		");

	}elseif($_POST['it_stock_qty_old'] > 0 && $_POST['it_stock_qty'] < 1){
		$soldout_flag = 'o';
        $soldout_fg = 'Y';
		sql_query("
			update
				naver_ep_brief
			set
				pause_yn = 'Y',
				resume_yn = null
			where
				uid = '".$naver_brief_chk['uid']."'
		");
	}






	# 품절 및 품절 해제시 히스토리 저장 2014-08-20 홍민기 #
	if($soldout_flag){

		sql_query("update yc4_soldout_history set current_fg='N' where it_id='".$it_id."'");

		sql_query("
			insert into
				yc4_soldout_history
			(
				it_id,flag,mb_id,time,ip,current_fg
			)values(
				'".$it_id."','".$soldout_flag."','".$member['mb_id']."','".$g4['time_ymdhis']."','".$_SERVER['REMOTE_ADDR']."','Y'
			)
		");

        $soldout_history_fnc = function($it_id,$fg,$mb_id){
            global $g4;
            if(!in_array($fg,array('Y','N'))){
                return false;
            }

            if(!$mb_id){
                $mb_id = $_SESSION['ss_mb_id'];
            }
            if(!$mb_id){
                return false;
            }

            include_once $g4['full_path'].'/lib/db.php';
            $db = new db();
            $ntics_stmt =  $db->ntics_db->prepare("select a.upc,b.currentqty from ople_mapping a left join N_MASTER_ITEM b on a.upc = b.upc where a.it_id = ? and b.upc is not null");
            $ntics_stmt->execute(array($it_id));
            if($ntics_stmt === false){
                return false;
            }
            $ntics_data = $ntics_stmt->fetch(PDO::FETCH_ASSOC);
            if(!trim($ntics_data['upc'])){
                return false;
            }
            $params = array('OPLE',$ntics_data['upc'],$it_id,$fg,'OPLE-'.$mb_id,$ntics_data['currentqty']);
//		$db->ntics_db->beginTransaction();
            $insert_stmt = $db->ntics_db->prepare("insert into soldout_proc_history (channel,upc,it_id,flag,create_dt,create_id,ntics_qty) VALUES (?,?,?,?,FORMAT ( GETDATE(), 'yyyyMMddHHmmss'),?,?)");
            if($insert_stmt->execute($params) === false){
                return false;
            }
            $uid = $db->ntics_db->lastInsertId();
            if(!$uid){
                return false;
            }

//		$db->ntics_db->rollBack();
            return true;
        };

        $soldout_history_fnc($it_id,$soldout_fg,$member['mb_id']);
	}


	# 가격 히스토리 저장 2015-03-24 홍민기 #
	$it = sql_fetch("select it_amount from ".$g4['yc4_item_table']." where it_id = '".$it_id."'");

	if($it['it_amount'] != $it_amount){
		sql_query("
			insert into yc4_item_amount_history (it_id,amount,update_id,update_dt,fg)
			values('".$it_id."','".$it_amount."','".$member['mb_id']."','".$g4['time_ymdhis']."','M')
		");
	}



	$sql = " update $g4[yc4_item_table]
                set $sql_common
              where it_id = '$it_id' ";
    sql_query($sql);
    $opk_db->query($sql);
    $opk_db->query("update {$g4['yc4_item_table']} set it_amount = '".$opk_amount."' where it_id = '".$it_id."'");

        $cps_ca_name =  $_POST['cps_ca_name'];
        $cps_ca_name2 =  $_POST['cps_ca_name2'];
        $cps_ca_name3 =  $_POST['cps_ca_name3'];
        $cps_ca_name4 =  $_POST['cps_ca_name4'];

        $cps_count = sql_fetch("select count(*) cnt from yc4_cps_item where it_id = '".$it_id."'");

        if($cps_count['cnt']>0){
            if($_POST['cps_use_yn']=="y"){

                //update
                sql_query($a="update yc4_cps_item set 
                            cps_ca_name = '$cps_ca_name',
                            cps_ca_name2 = '$cps_ca_name2',
                            cps_ca_name3 = '$cps_ca_name3',
                            cps_ca_name4 = '$cps_ca_name4',
                            update_date = now(),
                            use_yn ='y'
                            where it_id = '$it_id'");
            }else{
                //update
                sql_query($a="update yc4_cps_item set 
                            cps_ca_name = '$cps_ca_name',
                            cps_ca_name2 = '$cps_ca_name2',
                            cps_ca_name3 = '$cps_ca_name3',
                            cps_ca_name4 = '$cps_ca_name4',
                            update_date = now(),
                            use_yn = 'n'
                            where it_id = '$it_id'");

            }

        }else{
            if($_POST['cps_use_yn']=="y") {

                //insert
                sql_query($a = "insert into yc4_cps_item(it_id, cps_ca_name, cps_ca_name2, cps_ca_name3, cps_ca_name4, create_date, use_yn)
              values ('$it_id','$cps_ca_name','$cps_ca_name2','$cps_ca_name3','$cps_ca_name4',now(), 'y')");
            }

        }


}else if ($w == "d"){

// 아이템 삭제 기능 삭제 20174-03-08 강경인
//    if ($is_admin != 'super')
//    {
//        $sql = " select it_id from $g4[yc4_item_table] a, $g4[yc4_category_table] b
//                  where a.it_id = '$it_id'
//                    and a.ca_id = b.ca_id
//                    and b.ca_mb_id = '$member[mb_id]' ";
//        $row = sql_fetch($sql);
//        if (!$row[it_id])
//            alert("\'{$member[mb_id]}\' 님께서 삭제 할 권한이 없는 상품입니다.");
//    }
//
//    itemdelete($it_id);
}

if ($w == "" || $w == "u")
{
    // 관련상품 등록
    $it_id2 = explode(",", $it_list);
    for ($i=0; $i<count($it_id2); $i++)
    {
        if (trim($it_id2[$i]))
        {
            $sql = " insert into $g4[yc4_item_relation_table]
                        set it_id  = '$it_id',
                            it_id2 = '$it_id2[$i]' ";
            sql_query($sql, false);
            $opk_db->query($sql);


            // 관련상품의 반대로도 등록
            $sql = " insert into $g4[yc4_item_relation_table]
                        set it_id  = '$it_id2[$i]',
                            it_id2 = '$it_id' ";
            sql_query($sql, false);
            $opk_db->query($sql);
        }
    }

    // 이벤트상품 등록
    $ev_id = explode(",", $ev_list);
    for ($i=0; $i<count($ev_id); $i++)
    {
        if (trim($ev_id[$i]))
        {
            $sql = " insert into $g4[yc4_event_item_table]
                        set ev_id = '$ev_id[$i]',
                            it_id = '$it_id' ";
            sql_query($sql, false);
            $opk_db->query($sql);
        }
    }

    /* 이미지 관련 기능 삭제 2017-03-08 강경인
    if ($_FILES[it_simg][name])  upload_file($_FILES[it_simg][tmp_name],  $it_id . "_s",  "$g4[path]/data/item");
    if ($_FILES[it_mimg][name])  upload_file($_FILES[it_mimg][tmp_name],  $it_id . "_m",  "$g4[path]/data/item");
    if ($_FILES[it_limg1][name]) upload_file($_FILES[it_limg1][tmp_name], $it_id . "_l1", "$g4[path]/data/item");
    if ($_FILES[it_limg2][name]) upload_file($_FILES[it_limg2][tmp_name], $it_id . "_l2", "$g4[path]/data/item");
    if ($_FILES[it_limg3][name]) upload_file($_FILES[it_limg3][tmp_name], $it_id . "_l3", "$g4[path]/data/item");
    if ($_FILES[it_limg4][name]) upload_file($_FILES[it_limg4][tmp_name], $it_id . "_l4", "$g4[path]/data/item");
    if ($_FILES[it_limg5][name]) upload_file($_FILES[it_limg5][tmp_name], $it_id . "_l5", "$g4[path]/data/item");
    if ($_FILES[it_himg][name])  upload_file($_FILES[it_himg][tmp_name], $it_id . "_h", "$g4[path]/data/item");
    if ($_FILES[it_timg][name])  upload_file($_FILES[it_timg][tmp_name], $it_id . "_t", "$g4[path]/data/item");
    */

	# 신 카테고리 등록 2014-07-10 홍민기 #
	if($_POST['new_cate']){
		# 해당 상품의 기존 카테고리 정보 삭제 #
		sql_query("delete from yc4_category_item where it_id = '".$it_id."'");

		# 설정한 카테고리 데이터 저장 #
		if( is_array($_POST['new_cate']) ){
			foreach($_POST['new_cate'] as $val){
				if(trim($val)){
					$cate_insert .= (($cate_insert) ? ",":"")."('".$it_id."','".$val."')";
				}
			}
			if($cate_insert) {
				$cate_insert = "values".$cate_insert;
				sql_query("insert into yc4_category_item (it_id,ca_id) ".$cate_insert."");
			}
		}
	}
}

$qstr = "$qstr&sca=$sca&page=$page";


// 김선용 201208 :
if($return_url != ''){
	$qstr = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=".urlencode($search);
	$qstr .= "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
	goto_url("$return_url.php?$qstr");
}


if ($w == "u") {
    goto_url("./itemform.php?w=u&it_id=$it_id&$qstr");
} else if ($w == "d")  {
    goto_url("./itemlist.php?$qstr");
}

echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=$g4[charset]\">";
?>
<script>
    if (confirm("계속 입력하시겠습니까?"))
        //location.href = "<?="./itemform.php?it_id=$it_id&sort1=$sort1&sort2=$sort2&sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search&page=$page"?>";
        location.href = "<?="./itemform.php?it_id=$it_id&$qstr"?>";
    else
        location.href = "<?="./itemlist.php?$qstr"?>";
</script>

<?php
include_once "./_common.php";

if ($w == '' || $w == 'u')
{
	// 김선용 200908 :
	/*
    // 세션에 저장된 토큰과 폼으로 넘어온 토큰을 비교하여 틀리면 에러
    if ($token && get_session("ss_token") == $token) {
        // 맞으면 세션을 지워 다시 입력폼을 통해서 들어오도록 한다.
        set_session("ss_token", "");
    } else {
        alert_close("토큰 에러");
    }
	*/

    $key = get_session("captcha_keystring");
    if (!($key && $key == $_POST['is_key'])) {
        session_unregister("captcha_keystring");
        alert("정상적인 접근이 아닌것 같습니다.");
    }

    if (!$is_member)
    {
        if (!trim($_POST['is_name'])) alert("이름을 입력하여 주십시오.");
        if (!trim($_POST['is_password'])) alert("패스워드를 입력하여 주십시오.");
    }
    else
    {
        $_POST['is_name'] = $member['mb_name'];
        $is_password = $member['mb_password'];
    }

    if (!trim($_POST['is_subject'])) alert("제목을 입력하여 주십시오.");
    if (!trim($_POST['is_content'])) alert("내용을 입력하여 주십시오.");

    $is_password = sql_password($is_password);

	// 김선용 201006 :
	$is_subject = htmlspecialchars2($_POST['is_subject']);
	$is_content = $_POST['is_content'];
	$is_name = htmlspecialchars2($_POST['is_name']);
	$is_score = strip_tags($_POST['is_score']);
	$it_id = (int)$it_id;
}

if($_POST['refer']){
	$url = $_POST['refer'];
}else{
	$url = "./item.php?it_id=".$it_id;
}


$upload_msg = "";
$img_put = "";
$upload_dir = $g4['path']."/data/ituse";
$del_arr = array();
for($k=0; $k<count($_FILES['is_image']['tmp_name']); $k++)
{
	if($_FILES['is_image']['tmp_name'][$k])
	{
		if(!preg_match("/\.(gif|jp[e]?g|png)$/i", $_FILES['is_image']['name'][$k])) // 확장자
		{
			$upload_msg .= "이미지등록은 gif, jpg, jpeg, png 만 가능합니다.\\n({$_FILES['is_image']['name'][$k]})\\n\\n";
			continue;
		}
		else if($_FILES['is_image']['error'][$k] != 0) // 저장실패
		{
			$upload_msg .= "파일업로드 실패\\n({$_FILES['is_image']['name'][$k]})\\n\\n";
			continue;
		}
		else
		{
			// 파일명은 추측이 불가능하게. 중복 최소화를 위해 상품코드를 붙여줌.
			$file_ext = pathinfo($_FILES['is_image']['name'][$k]);
			while(1){ // 중복파일 처리
				$file_name = "{$it_id}_{$k}_".md5(uniqid("")).".".$file_ext['extension'];
				if(!file_exists("{$upload_dir}/$file_name"))
					break;
			}
			upload_file($_FILES['is_image']['tmp_name'][$k], $file_name, $upload_dir);
			$img_put .= " is_image{$k} = '$file_name', ";
			$del_arr[] = $k;
		}
		$photo = true;
	}
	else
		$img_put .= " is_image{$k} = '', ";
}

if ($w == '')
{
    $sql = " select max(is_id) as max_is_id from $g4[yc4_item_ps_table] ";
    $row = sql_fetch($sql);
    $max_is_id = $row[max_is_id];

    $sql = " select max(is_id) as max_is_id from $g4[yc4_item_ps_table]
              where it_id = '$it_id'
                and is_ip = '$member[mb_id]' ";
    $row = sql_fetch($sql);
    if ($row[max_is_id] && $row[max_is_id] == $max_is_id)
        alert("같은 상품에 대하여 계속해서 평가하실 수 없습니다.");

	if($_POST['od_id']){ // 주문번호가 넘어왔을 경우
		$od_id = $_POST['od_id'];
	}else{ // 주문번호가 넘어오지 않았을 경우 해당 상품을 구매한 가장 최근 주문번호를 불러온다
		// 상품 수령확인을 해서 주문상태가 완료일 경우에만 포인트를 지급한다.
		$od_chk = sql_fetch("select on_uid from {$g4['yc4_cart_table']} where it_id='$it_id' and ct_status = '완료' and ct_mb_id='{$member['mb_id']}' order by ct_time asc limit 1");
		if($od_chk){
			$od_chk2 = sql_fetch("select od_id from ".$g4['yc4_order_table']." where on_uid = '".$od_chk['on_uid']."'");
			if($od_chk2){
				$od_id = $od_chk2['od_id'];
			}
		}
	}

	# $od_id 가 있다는 것은 포인트를 받을 자격이 있다는것임 2014-07-23 홍민기

	/*
	// 김선용 201208 : 사용후기 처음 작성자 포인트지급
	if($member['mb_id'] && $default['de_it_use_first_postpoint']){
		$chk2 = sql_fetch("select count(is_id) as count from $g4[yc4_item_ps_table] where it_id = '$it_id' and is_confirm = '1' ");
		if(!$chk2['count']){
			$chk = sql_fetch("select ct_id from {$g4['yc4_cart_table']} where it_id='$it_id' and ct_status in ('배송','완료') and ct_mb_id='{$member['mb_id']}' limit 1 ");
			if($chk['ct_id']){
				if(insert_point($member['mb_id'], $default['de_it_use_first_postpoint'], "{$it_id} 상품에 대해 처음으로 구매후기 작성", "@ituse_first", $member['mb_id'], $it_id) == 1){
					$upload_msg .= "이 상품에 대해 처음으로 구매후기를 작성하셔서\\n\\n".nf($default['de_it_use_first_postpoint'])." 포인트가 지급되었습니다.";
					$point_insert = true;
				}
			}
		}
	}
	*/
	if($member['mb_id'] && $default['de_it_use_first_postpoint'] && $od_id){
		$chk2 = sql_fetch("select count(is_id) as count from $g4[yc4_item_ps_table] where it_id = '$it_id' and is_confirm = '1' ");
		if(!$chk2['count']){

			if(insert_point($member['mb_id'], $default['de_it_use_first_postpoint'], "{$it_id} 상품에 대해 처음으로 구매후기 작성", "@ituse_first", $member['mb_id'], $it_id) == 1){
				$upload_msg .= "이 상품에 대해 처음으로 구매후기를 작성하셔서\\n\\n".nf($default['de_it_use_first_postpoint'])." 포인트가 지급되었습니다.";
                $is_point = $default['de_it_use_first_postpoint'];
				$point_insert = true;
			}

		}
	}


	/*
	if($member['mb_id'] && !$point_insert){
		// 홍민기 2014-07-11 : 일반후기 작성시 200포인트 지급
		$chk = sql_fetch("select ct_id from {$g4['yc4_cart_table']} where it_id='$it_id' and ct_status = '완료' and ct_mb_id='{$member['mb_id']}' limit 1");
		if($chk['ct_id']){

			if($photo){ // 포토후기 작성시
				if(insert_point($member['mb_id'], 500, "{$it_id} 상품에 포토 구매후기 작성", "@ituse_first", $member['mb_id'], $it_id)){
					$upload_msg .= "이 상품에 대해 포토후기를 작성하셔서\\n\\n".nf(500)." 포인트가 지급되었습니다.";
				}
			}else{ // 일반후기 작성시
				if(insert_point($member['mb_id'], 200, "{$it_id} 상품에 대해 구매후기 작성", "@ituse_first", $member['mb_id'], $it_id)){
					$upload_msg .= "이 상품에 대해 후기를 작성하셔서\\n\\n".nf(200)." 포인트가 지급되었습니다.";
				}
			}
		}
	}
	*/
	if($member['mb_id'] && !$point_insert && $od_id){
		# 구매금액에 따른 포인트 차등지급 2014-07-23 홍민기 #
		$order_info = sql_fetch("
			select
				od_receipt_bank + od_receipt_card + od_receipt_point as amount,
				on_uid
			from
				".$g4['yc4_order_table']."
			where
				od_id = '".$od_id."'
		");
		$order_amount = $order_info['amount']; // 결제금액

		$item_amount = sql_fetch("select ct_amount from ".$g4['yc4_cart_table']." where on_uid = '".$order_info['on_uid']."'");
		$item_amount = $item_amount['ct_amount']; // 상품 구입 금액



		// 홍민기 2014-07-11 : 일반후기 작성시 200포인트 지급
		if($photo){ // 포토후기 작성시 만원 이하는 상품금액의 5% 지급, 이상은 500원 지급 2014-07-23 홍민기
			if($order_amount <= 10000){
				$photo_point = $item_amount * 0.05 ;
			}else{
				$photo_point = 500;
			}

			if(insert_point($member['mb_id'], $photo_point, "{$it_id} 상품에 포토 구매후기 작성", "@ituse-".$od_id.'-'.$it_id, $member['mb_id'], $it_id)){
                $is_point = $photo_point;
				$upload_msg .= "이 상품에 대해 포토후기를 작성하셔서\\n\\n".nf($photo_point)." 포인트가 지급되었습니다.";
				$point_insert = true;
			}
		}else{ // 일반후기 작성시 무조건 200원
			if(insert_point($member['mb_id'], 200, "{$it_id} 상품에 대해 구매후기 작성", "@ituse-".$od_id.'-'.$it_id, $member['mb_id'], $it_id)){
                $is_point = 200;
                $upload_msg .= "이 상품에 대해 후기를 작성하셔서\\n\\n".nf(200)." 포인트가 지급되었습니다.";
				$point_insert = true;
			}
		}

	}

	// 후기 테이블에 주문번호 저장
    $od_id_insert = '';
	if($od_id){
		$od_id_insert = ",od_id = '".$od_id."'";
	}
    if($is_point){
        $od_id_insert .= ",is_point = '".$is_point."'";
    }

    $sql = "insert $g4[yc4_item_ps_table]
               set it_id = '$it_id',
                   mb_id = '$member[mb_id]',
                   is_score = '$is_score',
                   is_name = '$is_name',
                   is_password = '$is_password',
                   is_subject = '$is_subject',
                   is_content = '$is_content',
                   is_time = now(),
				   {$img_put}
                   is_ip = '$_SERVER[REMOTE_ADDR]'
				   ".$od_id_insert."
	";

    if (!$default[de_item_ps_use])
        $sql .= ", is_confirm = '1' ";
    sql_query($sql);

    if ($default[de_item_ps_use])
        alert("평가하신 글은 관리자가 확인한 후에 표시됩니다.", $url);
    else
	{
		// 김선용 201208 :
		if($upload_msg != '')
			alert("$upload_msg", $url);
		else
	        goto_url($url);
	}
}
else if ($w == 'u')
{
    $sql = " select * from $g4[yc4_item_ps_table] where is_id = '$is_id' ";
    $row = sql_fetch($sql);
    if ($row[is_password] != $is_password)
        alert("패스워드가 틀리므로 수정하실 수 없습니다.");

	if( $row['od_id'] ){
		alert('포인트를 지급받은 후기는 수정하실 수 없습니다.');
	}

	// 김선용 201208 : 이미지삭제
	if(count($del_arr)){
		for($k=0; $k<count($del_arr); $k++)
			@unlink("$upload_dir/".$row["is_image{$del_arr[$k]}"]);
	}

    $sql = " update $g4[yc4_item_ps_table]
                set is_subject = '$is_subject',
                    is_content = '$is_content',
					{$img_put}
                    is_score = '$is_score'
              where is_id = '$is_id' ";
    sql_query($sql);

	// 김선용 201208 :
	if($upload_msg != '')
		alert("$upload_msg", $url);
	else
		goto_url($url);
}
else if ($w == 'd')
{
    if ($is_member)
    {
        $sql = " select count(*) as cnt from $g4[yc4_item_ps_table] where mb_id = '$member[mb_id]' and is_id = '$is_id' ";
        $row = sql_fetch($sql);
        if (!$row[cnt])
            alert("자신의 사용후기만 삭제하실 수 있습니다.");
    }
    else
    {
        $is_password = sql_password($is_password);

        $sql = " select is_password from $g4[yc4_item_ps_table] where is_id = '$is_id' ";
        $row = sql_fetch($sql);
        if ($row[is_password] != $is_password)
            alert("패스워드가 틀리므로 삭제하실 수 없습니다.");
    }
	// 김선용 201208 : 이미지삭제
	$chk = sql_fetch("select * from $g4[yc4_item_ps_table] where is_id = '$is_id' ");
	if($chk['od_id'] && !$is_admin){
		alert('포인트를 지급받은 후기는 삭제할 수 없습니다.');
		exit;
	}
	for($k=0; $k<5; $k++){
		if($chk["is_image{$k}"] != '')
			@unlink("$upload_dir/".$chk["is_image{$k}"]);
	}


    $sql = " delete from $g4[yc4_item_ps_table] where mb_id = '$member[mb_id]' and is_id = '$is_id' ";
    sql_query($sql);

    goto_url($url);
}
?>
<?php
// 김선용 201211 : 복수배송처리

if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 제대로된 include 시에만 실행
if (!defined("_ORDERMAIL_")) exit;

// 주문자님께 메일발송 체크를 했다면
if ($post_send_mail)
{
    $od = sql_fetch(" select on_uid, od_name, od_email, od_shop_memo from $g4[yc4_order_table] where od_id = '$od_id' ");

    unset($cart_list);
    unset($delivery_list);

    $sql = " select a.*,
                    b.it_name,
                    b.it_opt1_subject,
                    b.it_opt2_subject,
                    b.it_opt3_subject,
                    b.it_opt4_subject,
                    b.it_opt5_subject,
                    b.it_opt6_subject
               from $g4[yc4_cart_table] a left join $g4[yc4_item_table] b on (b.it_id=a.it_id)
              where a.on_uid = '$od[on_uid]' ";
	$sql .= " and ct_ship_os_pid like '%{$post_pid}%' ";
	$sql .= " order by a.ct_id ";
    $result = sql_query($sql);
    for ($i=0; $ct=sql_fetch_array($result); $i++) {
        // 상품 옵션
        $s_option = "";
        $str_split = "";
        for ($n=1; $n<=6; $n++) {
            if ($ct["it_opt{$n}"] == "") {
                continue;
            }

            $s_option .= $str_split;
            $it_opt_subject = $ct["it_opt{$n}_subject"];

            unset($opt);
            $opt = explode( ";", trim($ct["it_opt{$n}"]) );
            $s_option .= "$it_opt_subject = $opt[0]";
            $str_split = "<br>";
        }

        if ($s_option == "") {
            $s_option = "없음";
        }

        $cart_list[$i][it_id]   = $ct[it_id];
        $cart_list[$i][it_name] = $ct[it_name];
        $cart_list[$i][it_opt]  = $s_option;

		$h_qty = 0;
		// 수량처리. os_pid 처리
		if($ct['ct_ship_ct_qty']){
			$qty = explode("|", $ct['ct_ship_ct_qty']);
			$pid = explode("|", $ct['ct_ship_os_pid']);
			for($h=0; $h<count($pid); $h++){
				if($pid[$h] == $post_pid && $qty[$h] != '')
					$h_qty = (int)$qty[$h];
			}
		}
		$cart_list[$i][ct_status] = "배송중";
		$cart_list[$i][ct_qty]    = $h_qty;
    }

	$is_delivery = false;
	if ((int)$post_dl_id > 0) {
		$dl = sql_fetch(" select * from $g4[yc4_delivery_table] where dl_id = '$post_dl_id' ");
		$delivery_list[dl_url]          = $dl[dl_url];
		$delivery_list[dl_company]      = $dl[dl_company];
		$delivery_list[dl_tel]          = $dl[dl_tel];
		$delivery_list[od_invoice]      = $post_invoice;
		$delivery_list[od_invoice_time] = $post_invoice_time;
		$is_delivery = true;
	}

    // 배송내역이 있다면 메일 발송
    if($is_delivery)
    {
        ob_start();
        include "$g4[shop_path]/mail/ordermail_multi.mail.php";
        $content = ob_get_contents();
        ob_end_clean();

        $title = "{$od[od_name]}님께서 주문하신 내역을 다음과 같이 처리하였습니다.";
        $email = $od[od_email];

        // 메일 보낸 내역 상점메모에 update
        $od_shop_memo = "$g4[time_ymdhis] - 복수배송내역 메일발송\n" . $od[od_shop_memo];
        sql_query(" update $g4[yc4_order_table] set od_shop_memo = '$od_shop_memo' where od_id = '$od_id' ");

		// 메일발송주소 고정처리
		if($default['de_post_mail_addr'] != '')
			$from_mail['mb_email'] = $default['de_post_mail_addr'];
		else
			$from_mail = get_admin('super');

        mailer($config[cf_title], $from_mail[mb_email], $email, $title, $content, 1);
    }
}
?>
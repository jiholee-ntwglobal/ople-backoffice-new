<?
# 이벤트 정보 및 이벤트 상품을 배열로 반환 2014-07-03 홍민기
function gift_event($it_id){
	$it = sql_fetch("
		select
			it_id,
			ca_id,
			ca_id2,
			ca_id3,
			ca_id4,
			ca_id5,
			it_maker
		from
			yc4_item
		where
			it_id = '".$it_id."'
	");

	if(!$it) return false;

	# 구매금액별 이벤트 { 2014-07-03 홍민기 #
	$gift_event_qry_ca_id .= (($gift_event_qry_ca_id && $it['ca_id']) ? ',':'').(($it['ca_id']) ? "'".$it['ca_id']."'":'');
	$gift_event_qry_ca_id .= (($gift_event_qry_ca_id && $it['ca_id2']) ? ',':'').(($it['ca_id2']) ? "'".$it['ca_id2']."'":'');
	$gift_event_qry_ca_id .= (($gift_event_qry_ca_id && $it['ca_id3']) ? ',':'').(($it['ca_id3']) ? "'".$it['ca_id3']."'":'');
	$gift_event_qry_ca_id .= (($gift_event_qry_ca_id && $it['ca_id4']) ? ',':'').(($it['ca_id4']) ? "'".$it['ca_id4']."'":'');
	$gift_event_qry_ca_id .= (($gift_event_qry_ca_id && $it['ca_id5']) ? ',':'').(($it['ca_id5']) ? "'".$it['ca_id5']."'":'');
	if($gift_event_qry_ca_id) $gift_event_qry_ca_id = " or a.ca_id in (".$gift_event_qry_ca_id.")";
	$gift_event_qry = sql_query("
		select
			a.name,a.event_type,a.it_maker,a.comment,a.bid,a.priod_view,a.st_dt,a.en_dt,
			b.it_id,b.od_amount,b.show_amount,
			c.ca_name,
			d.it_name,d.it_cust_amount,d.it_amount,d.it_stock_qty
		from
			yc4_free_gift_event a
			left join
			yc4_free_gift_event_item b on a.bid = b.bid
			left join
			yc4_category c on a.ca_id = c.ca_id
			left join
			yc4_item d on b.it_id = d.it_id
		where
			a.st_dt <= '".date('Ymd')."'
			and
			a.en_dt >= '".date('Ymd')."'
			and
			(
				a.it_maker = '".mysql_real_escape_string($it['it_maker'])."'
				".$gift_event_qry_ca_id."
				or
				a.event_type = 'A'
			)
			and
			d.it_name is not null
			and
			b.it_id is not null
			and
			b.use = 'Y'
		order by a.bid desc,a.event_type desc,b.od_amount desc
	");


	for($i=0; $gift_event_result = sql_fetch_array($gift_event_qry); $i++){
		$gift_event[$i] = $gift_event_result;
	}

	return $gift_event;



}
?>
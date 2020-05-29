<?
include_once("./_common.php");


function category_item_insert($ca_id,$it_id_arr){
	/*
		카테고리내 상품 등록 2014-08-13 홍민기
		category_item_insert(카테고리 아이디, 상품코드배열) => insert된 상품 수 return
	*/
	if(!is_array($it_id_arr)){
		echo 'lakshjdflksadjf';
		return false;
	}else{
		foreach( $it_id_arr as $key => $val ){
			$qry .= (($qry) ? ", ":" values ")."('".$ca_id."', '".$val."') ";
		}

		if($qry){

			sql_query("insert into yc4_category_item (ca_id,it_id) ".$qry);

			return count($it_id_arr);
		}
	}
}


function category_item_insert3($ca_id,$it_id_arr){
	/*
		카테고리내 상품 등록 2014-08-13 홍민기
		category_item_insert(카테고리 아이디, 상품코드배열) => insert된 상품 수 return
	*/
	if(!is_array($it_id_arr)){
		echo 'lakshjdflksadjf';
		return false;
	}else{
		foreach( $it_id_arr as $key => $val ){
			$qry .= (($qry) ? ", ":" values ")."('".$ca_id."', '".$val."') ";
		}

		if($qry){

			sql_query("insert into yc4_category_item (ca_id,it_id) ".$qry);

			return count($it_id_arr);
		}
	}
}

?>
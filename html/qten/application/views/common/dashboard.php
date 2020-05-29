<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-30
 * Time: 오전 11:25
 */

//if($_SERVER['REMOTE_ADDR']=='211.214.213.101'){
//
//    echo "<pre>";
//    var_dump($arr = array('1','1','1','2','4','3','2'));
//
//    var_dump(array_count_values($arr));
//
//    var_dump(array_unique($arr));
//    var_dump(array_diff_assoc($arr, array_unique($arr)));
//    echo "</pre>";
//    //var_dump(array_unique(array_diff_assoc($arr, array_unique($arr))));
//
//
//
//}

?>
<div class="row">
    <div class="col-lg-12">
        <h3 class="page-header">qten Dashboard</h3>
    </div>
</div>

    <pre>
<!--        -	주문처리 동기화
        	주문서 수집 : 매시 50분
        	주문서 배송전산 등록 : 매시 25분
        	주문 상품 재고 차감 : 매시 40분
        	주문 상품 판매 데이터 등록 : 매시 10분
        	송장번호 동기화 시간(배송전산->백오피스) : 매시간 5분, 35분
        	송장번호 데이트 시간(백오피스->ESM) : 매시간 40분
-->
        -	상품 관리
        	VCODE 동기화 시간 : 매시 58분
        	NTICS 재고 동기화 시간 : 매시 35분

        -	품절 예외 상품 동기화
        	판매중 : 매시 35분
        	품절 : 매시 35분

        -	품절상태 API 요청
        	단품 : 매시 45분부터 시작
        	옵션 : 매시 55분부터 시작

        -	상품 일괄 가격조정
        	단품 : 매시 30분


        -	품절조건
        	해외사업자 : 엔틱스 수량 <=2


        -	품절해제조건
        	해외사업자 : 엔틱스 수량 > 2
    </pre>
</div>

<!--        -	VCODE,UPC 재고 동기화-->
<!--        	NTICS -> 뉴베이 [VCODE] : 매시 00분-->
<!--        	NTICS -> 뉴베이 [UPC 재고] : 매시 30분-->
<!---->
<!--        -	품절 예외 상품 동기화-->
<!--        	판매중 : 매시 30분-->
<!--        	품절 : 매시 30분-->
<!---->
<!--        -	상품 일괄 가격조정-->
<!--        	단품 : 매시 25분-->
<!---->
<!--        -	품절상태 API 요청-->
<!--        	단품 : 매시 35분-->
<!--        	옵션 : 매시 40분-->
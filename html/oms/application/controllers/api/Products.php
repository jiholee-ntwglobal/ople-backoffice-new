<?php

/**
 * EBay 2.0 주문서 수집 중복방지용 상품정보 조회 API
 * Class Products
 */
class Products extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('api/ApiItemModel');
    }

    public function index() {}

    /**
     * ebay 1.0 상품인지 조회
     *
     * http://oms.ntwsec.com/oms/api/products/productno/B552172311
     */
    public function productno() {
        $searchProductNo = trim($this->uri->segment(4));

        if( $searchProductNo !== '' ) {
            $isExist = $this->ApiItemModel->searchProductNo($searchProductNo);
            echo $isExist ? "Y" : "N";
        } else {
            echo "E";
        }

    }

    public function vcode() {
        echo "vcode";
    }
}
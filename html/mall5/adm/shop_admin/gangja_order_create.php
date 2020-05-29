<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-04-27
 * Time: 오후 12:15
 */
$sub_menu = "400124";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "강자닷컴 주문서 생성";
define('bootstrap', true);
include_once("$g4[admin_path]/admin.head.php");
?>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

<h4>강자닷컴 주문서 처리(오플 주문서 생성)</h4>
<a href="gangja_order_list.php" style="color: green">주문서 리스트</a><a style="color: blue" href="gangja_mapping_list.php" target="_blank"> 매핑 리스트</a>
<br>
<span style="color: red">※ 강자닷컴(발송대기 관리)에서 다운로드한 엑셀 파일을 업로드합니다(발송대기 주문서)<br>
    ※ 강자닷컴 상품과 오플상품이 맵핑이 되어있지 않는 상품이 하나라도 있을경우 해당 엑셀파일은 오플 주문서가 생성 되지않습니다
</span>
<br>
<div class="row">
    <div class="col-lg-12">
        <form  method="post" action="./gangja_order_create_01.php" onsubmit="return frm_chk(this);"
              enctype="multipart/form-data">
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-lg-12">
                        <p class="pull-left">주문서생성</p>
                        <input type="hidden" name="mode" value="msrp_excel_change">
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="input-group">
                            <input type="file" class="form-control" name="excel_file">
                            <span class="input-group-btn">
                            <button class="btn btn-default" type="submit">파일 업로드</button>
                        </span>
                        </div><!-- /input-group -->
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<?
include_once("$g4[admin_path]/admin.tail.php");
?>

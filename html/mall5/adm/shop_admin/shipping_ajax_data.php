<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-09-08
 * Time: 오전 10:33
 */
$sub_menu = "400400";
include_once("./_common.php");
$invoice_cjnum_data = $_POST['invoice_cjnum_data'];
$invoice_chk = $_POST['invoice_chk'];
$dl_company = $_POST['dl_company'];
$od_invoice_time = $_POST['od_invoice_time'];

echo subtitle("배송정보");

if($invoice_cjnum_data ) { ?>

<table width=100% cellpadding=0 cellspacing=0 border=0 class='list_styleAD'>
    <colgroup><col width='140'><col /></colgroup>
    <tbody>
    <tr>
        <th>배송회사</th>
        <td><?php echo $dl_company;?></td>
    </tr>
    <tr>
        <th>운송장번호</th>
        <td>
            <p><a href='http://track.rocketparcel.com/track/wbl/<?php echo $invoice_cjnum_data;?>' target='_blank'><?php echo $invoice_cjnum_data; ?></a></p>
        </td>
    </tr>
    <tr>
        <th>배송일시</th>
        <td><?php echo $od_invoice_time; ?></td>
    </tr>
    <?if ($invoice_chk=='1') {

        $host = 'track.rocketparcel.com';

        $ServiceKey = 'c9151199-17a7-496b-925e-20aa864ad4ba';

        $service_url = "https://{$host}/track/if/KR-IN/v1.0/JSON/key/{$ServiceKey}/lang/kr/hawbNo/{$invoice_cjnum_data}";

        $ch = curl_init($service_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                //'Content-Length: ' . strlen($data_str)
            )
        );

        $result = curl_exec($ch);

        $tracking_history_json = json_decode($result, true);

        if($tracking_history_json['hubResponse']['header']['responseCode'] == '00'){ ?>

    <tr>
        <th>배송진행상황</th>
        <td>
            <table width=100% cellpadding=0 cellspacing=0 border=0 class='list_styleAD'>
                <tr>
                    <td width='200'>
                        <b>처리위치</b>
                    </td>
                    <td width='150'>
                        <b>처리상태</b>
                    </td>
                    <td width='200'>
                        <b>처리일시</b>
                    </td>
                </tr>
                <?php $trackHistrory = $tracking_history_json['hubResponse']['trackHistrory'];

                foreach($trackHistrory as $th){

                    $time_warning = ($th['processSttus'] == '출항') ? '<span style="color:red;font-weight:bold">(현지시간)</span>' : ''; ?>
                <tr>
                    <td><?php echo $th['nowLc'];?></td>
                    <td><?php echo $th['processSttus'];?></td>
                    <td><?php echo$th['dlvyDate']. $th['dlvyTime']. $time_warning;?></td>
                </tr>
            <?php } ?>
            </table>
        </td>

        <?php }
    }?>
    </tbody>
</table>
<div style='text-align:left;color:red;padding:5px 0;'>중복 송장일 경우 배송 진행상황을 보여주지않습니다</div>
<div style='text-align:left;color:red;padding:5px 0;'>국내등기/택배조회는 통관이 완료되고 택배사 전산에 업데이트 된 이후부터 조회가 가능합니다.<br/>
항공배송 및 통관과정 중에는 국내 택배 정보는 나오지 않습니다.</div>

<?php }else{ ?>
    <span class=leading>아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</span><br>
    <span class=leading>오래된 주문서(2년이상 지난 주문서)일 경우 배송정보를 불러오지 못할수 있습니다 </span>
<?php }?>


<? 
$excel_down = 'g4_write_test_smart'; //엑셀 다운로드 테이블 설정 g4_write 는 기본 그누 세팅시 자동으로 붙습니다.
$wr_id = $id = $_GET['wr_id']; 

$db_conn = mysql_connect('localhost', 'lich25', '710625') or die('서버에 접속하지 못했습니다.');
mysql_select_db('계정명', $db_conn);


if ($ms =="excel"){ 
$g4[title] = "엑셀 문서 다운로드"; 
  header( "Content-type: application/vnd.ms-excel" ); 
  header( "Content-Disposition: attachment; filename=상품구매.xls" ); 
  //header( "Content-Description: PHP4 Generated Data" ); 
 } else if ($ms =="power"){ 
 $g4[title] = "파워포인트 문서 다운로드"; 
  header( "Content-type: application/vnd.ms-powerpoint" ); 
  header( "Content-Disposition: attachment; filename=상품구매.ppt" ); 
  // header( "Content-Description: PHP4 Generated Data" ); 
 } else if ($ms =="word"){ 
  $g4[title] = "워드 문서 다운로드"; 
  header( "Content-type: application/vnd.ms-word" ); 
  header( "Content-Disposition: attachment; filename=상품구매.doc" ); 
  //header( "Content-Description: PHP4 Generated Data" ); 
 } else if ($ms =="memo"){ 
  $g4[title] = "메모 문서 다운로드"; 
  header( "Content-type: application/vnd.ms-notepad" ); 
  header( "Content-Disposition: attachment; filename=상품구매.txt" ); 
 } else { 
  header( "Content-type: application/vnd.ms-excel" ); 
  header( "Content-Disposition: attachment; filename=상품구매.xls" ); 
 } 
  header( "Content-Description: PHP4 Generated Data" ); 

 

$temp=mysql_fetch_array(mysql_query("select count(*) from $excel_down where wr_is_comment = '0' and wr_content = '$wr_id' ")); 
        $result=@mysql_query("select * from $excel_down where wr_is_comment = '0'  and wr_content = '$wr_id' order by wr_datetime desc"); 

      $number=$temp[0] 
?> 
<html> 
<head> 
<title><?=$g4[title]?> -페이지-</title> 
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr"> 
</head> 
<body> 
    <table width=100% cellpadding=0 cellspacing=0 border=1> 
      <tr align=center height=25 bgcolor=CCCCCC> 
        <td style=font-weight:bold;>번호</td>
		<td style=font-weight:bold;>상품명</td> <!--//wr_subject--->
		<td style=font-weight:bold;>구매자 아이디</td> <!--//mb_id--->
		<td style=font-weight:bold;>구매자 별명</td> <!--//wr_name--->
		<td style=font-weight:bold;>구매일</td> <!--//wr_datetime--->

		<td style=font-weight:bold;>구매자 성명</td> <!--//wr_1--->
		<td style=font-weight:bold;>구매자 연락처</td> <!--//wr_2--->
		<td style=font-weight:bold;>구매자 배송지</td> <!--//wr_3--->
		<td style=font-weight:bold;>구매자 이메일</td> <!--//wr_4--->
		<td style=font-weight:bold;>구매금액</td> <!--//wr_5--->
		<td style=font-weight:bold;>판매사</td> <!--//wr_6--->
		<td style=font-weight:bold;>옵션1</td> <!--//wr_7--->
		<td style=font-weight:bold;>옵션2</td> <!--//wr_8--->
		<td style=font-weight:bold;>옵션3</td> <!--//wr_9--->
		<td style=font-weight:bold;>입금상태</td> <!--//wr_10--->
		
  
  
      </tr>

<? 
 while($data=mysql_fetch_array($result)) { 

 
 
 echo" 
        <tr height=23> 
    <td align=center>$number</td>
    <td>$data[wr_subject]</td>
    <td>$data[mb_id]</td>
    <td>$data[wr_name]</td>
    <td>$data[wr_datetime]</td>
    
	<td>$data[wr_1]</td>
	<td>$data[wr_2]</td>
	<td>$data[wr_3]</td>
	<td>$data[wr_4]</td>
	<td>$data[wr_5]</td>
	<td>$data[wr_6]</td>
	<td>$data[wr_7]</td>
	<td>$data[wr_8]</td>
	<td>$data[wr_9]</td>
	<td>$data[wr_10]</td>

        </tr>";

  $number--; 
  } 
  echo " 
        </table> 
        </body> 
        </html>"; 
?> 


<? 
$excel_down = 'g4_write_test_smart'; //���� �ٿ�ε� ���̺� ���� g4_write �� �⺻ �״� ���ý� �ڵ����� �ٽ��ϴ�.
$wr_id = $id = $_GET['wr_id']; 

$db_conn = mysql_connect('localhost', 'lich25', '710625') or die('������ �������� ���߽��ϴ�.');
mysql_select_db('������', $db_conn);


if ($ms =="excel"){ 
$g4[title] = "���� ���� �ٿ�ε�"; 
  header( "Content-type: application/vnd.ms-excel" ); 
  header( "Content-Disposition: attachment; filename=��ǰ����.xls" ); 
  //header( "Content-Description: PHP4 Generated Data" ); 
 } else if ($ms =="power"){ 
 $g4[title] = "�Ŀ�����Ʈ ���� �ٿ�ε�"; 
  header( "Content-type: application/vnd.ms-powerpoint" ); 
  header( "Content-Disposition: attachment; filename=��ǰ����.ppt" ); 
  // header( "Content-Description: PHP4 Generated Data" ); 
 } else if ($ms =="word"){ 
  $g4[title] = "���� ���� �ٿ�ε�"; 
  header( "Content-type: application/vnd.ms-word" ); 
  header( "Content-Disposition: attachment; filename=��ǰ����.doc" ); 
  //header( "Content-Description: PHP4 Generated Data" ); 
 } else if ($ms =="memo"){ 
  $g4[title] = "�޸� ���� �ٿ�ε�"; 
  header( "Content-type: application/vnd.ms-notepad" ); 
  header( "Content-Disposition: attachment; filename=��ǰ����.txt" ); 
 } else { 
  header( "Content-type: application/vnd.ms-excel" ); 
  header( "Content-Disposition: attachment; filename=��ǰ����.xls" ); 
 } 
  header( "Content-Description: PHP4 Generated Data" ); 

 

$temp=mysql_fetch_array(mysql_query("select count(*) from $excel_down where wr_is_comment = '0' and wr_content = '$wr_id' ")); 
        $result=@mysql_query("select * from $excel_down where wr_is_comment = '0'  and wr_content = '$wr_id' order by wr_datetime desc"); 

      $number=$temp[0] 
?> 
<html> 
<head> 
<title><?=$g4[title]?> -������-</title> 
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr"> 
</head> 
<body> 
    <table width=100% cellpadding=0 cellspacing=0 border=1> 
      <tr align=center height=25 bgcolor=CCCCCC> 
        <td style=font-weight:bold;>��ȣ</td>
		<td style=font-weight:bold;>��ǰ��</td> <!--//wr_subject--->
		<td style=font-weight:bold;>������ ���̵�</td> <!--//mb_id--->
		<td style=font-weight:bold;>������ ����</td> <!--//wr_name--->
		<td style=font-weight:bold;>������</td> <!--//wr_datetime--->

		<td style=font-weight:bold;>������ ����</td> <!--//wr_1--->
		<td style=font-weight:bold;>������ ����ó</td> <!--//wr_2--->
		<td style=font-weight:bold;>������ �����</td> <!--//wr_3--->
		<td style=font-weight:bold;>������ �̸���</td> <!--//wr_4--->
		<td style=font-weight:bold;>���űݾ�</td> <!--//wr_5--->
		<td style=font-weight:bold;>�ǸŻ�</td> <!--//wr_6--->
		<td style=font-weight:bold;>�ɼ�1</td> <!--//wr_7--->
		<td style=font-weight:bold;>�ɼ�2</td> <!--//wr_8--->
		<td style=font-weight:bold;>�ɼ�3</td> <!--//wr_9--->
		<td style=font-weight:bold;>�Աݻ���</td> <!--//wr_10--->
		
  
  
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


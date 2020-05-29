<?php
/**
 * Created by PhpStorm.
 * User: developer_gbs
 * Date: 2017-05-02
 * Time: 오전 11:24
 */
$sub_menu = "800100";
include_once("./_common.php");

exit;
$chk = sql_fetch("
SELECT count(*) cnt
FROM yc4_event_data
WHERE     ev_code = 'insta_201705_gift'
      AND ev_data_type = 'gift_item'
      AND value1 = 'kongha2'
      AND value3 IS NULL
");
if($chk['cnt']<=0){
    return false;
}
$slq  = sql_query("
SELECT uid, value2 AS it_id
FROM yc4_event_data
WHERE     ev_code = 'insta_201705_gift'
      AND ev_data_type = 'gift_item'
      AND value1 = 'kongha2'
      AND value3 IS NULL
");
while ($row =sql_fetch_array($slq)){
    if($row['uid']&& $row['it_id']){
        echo "asdf";
    }
}






exit;
/*$arr =  array(
    'ajiso0401', 'wonjeontktltntj1emd', 'leeykim', 'jmcity', 'kkulkkul_', 'Nickyko',
    'jdyhappy', 'ssen1225', 'eunha07', 'amelia7', 'nini1230', 'prettymiho',
    'meddogi87', 'pgwj', 'bita1019', 'mijung3024', 'hohi80', 'fische74', 'totaltwo',
    'esther2001', 'archihee', 'cy2051', 'philip3325', 'senoritas', 'jhkace',
    'cityseo', 'lapodo', 'romy5004', 'ljaehyoung', 'crisphj', 'lg1001', 'hygge',
    'aurora517', 'wonyhoya', 'kakim0923', 'starrysky11', 'vigata', 'nevery',
    'pekemon', 'xduke', 'kikihaha1', 'shshin', 'dhyu705', 'adam0401', 'dhclickers',
    'toggy', 'jina0119', 'bush990', 'hakaider', 'neopiash', 'mj00mj', 'kssaoma',
    'flyjames', 'khs586586', 'topgunle', 'Hyeunicelee', 'ezpark', 'shine0027',
    'komddung', 'emmasia', 'wawoo78', 'bionix98', 'teva', 'yong9611',
    'usmarketprice', 'hakmsh50', 'ily0045', 'atti0110', 'seongby', 'candy072',
    'pes805', 'ssm0724', 'hannah211', 'kikoneko', 'hjs1435', 'svrmgrl', 'tko20',
    'goo303030', 'meeya07', 'sophay', 'sony0220', 'min77', 'rubyem', 'ninlng0430',
    'afford', 'jihyun0014', 'gladis', 'ojb123', 'cary03', 'keum78', 'pandora3542',
    'signalx', 'alzzam44', 'shinwoo518', 'kubi9', 'babi77', 'igniskim', 'lemonc81',
    'jspark0624', 'jothing0007', 'blue2k5', 'sooomin', 'parkinsoo', 'nicon29',
    'mingmini', 'jini0415', 'sajin', 'jhchoi', 'perfact21', 'boslll', 'j2s5y00',
    'mypark919', 'poemhd', 'maud75', 'key3123', 'kiki2015', 'sunny4471', 'blue2015',
    'ididid12', 'ejkim427', 'nsvogue', 'duckwoon', 'saverksh', 'ferex',
    'greenwater3', 'salut919', 'firecannon', 'sangsang5000', 'heyjci', 'ssm1914',
    'mikjh3', 'yusun', 'id3207', 'mung1824', 'asahizzang', 'khg6633', 'watch91',
    'sski21', 'kmjsmj', 'bisop7', 'bonosa', 'chaoskun', 'jeiwon58', 'automn',
    'ekshin1', 'psy0600', 'matga1', 'classic1167', 'dlgusal119', 'yebbi47',
    'keju44', 'lupia', 'jhlovelygirl', 'syunikiss00', 'cooljihee', 'stockhi',
    'qa1024', 'wishsk8', 'ksu', 'esthero', 'bluemodern', 'jinah0224', 'jiyeon6503',
    'nam2', 'wlfla0190', 'alicedeejay', 'reine16', 'apolina', 'starry'


);
foreach ($arr as $value){

   $sql = sql_query($erere="
select value2,date_format(value6,'%Y%m%d') date ,value5
from yc4_event_data c 
where c.ev_code='new_year_dc_2017' 
AND c.ev_data_type='od_id'  
AND c.value6 IS not NULL AND c.value7 IS NULL AND value2='".$value."'
");

   while ($row =sql_fetch_array($sql)){

       $sdsd = sql_fetch($erere="
       select 
a.mb_id ,
sum(if(date_format(a.po_datetime ,'%Y%m%d') >= '{$row['date']}'and a.po_point<=0,a.po_point,0)) as ddddd 

from g4_point a , (select value2,min(value6)as value6 from yc4_event_data c where c.ev_code='new_year_dc_2017' AND c.ev_data_type='od_id'  AND c.value6 IS not NULL AND c.value7 IS NULL
group by value2) c, g4_member b 
where a.mb_id = b.mb_id
and a.po_content NOT LIKE '%출석체크 이벤트 20회이상%'
and a.po_content NOT LIKE '%상품에 대해 구매후기 작성%'
and a.po_content NOT LIKE '%마스터카드 주말결제 5% 포인트%'
and a.po_content NOT LIKE '%상품에 대해 처음으로 구매후기 작성%'
and a.po_content NOT LIKE '%가정의달 5% 적립%'
and a.po_content NOT LIKE '%하나비바카드 5% 적립%'
and a.po_content NOT LIKE '%출석체크%'
and a.po_content NOT LIKE '%구매상품 회원직접 수령확인%'
and a.mb_id ='{$value}'
       and b.mb_id = c.value2
group by a.mb_id
       ");
       if(($sdsd['ddddd']+$row['value5'])>0) {
           echo "<pre>";
           var_dump($row);
           echo "\n";
           echo "사용금액 : " . $sdsd['ddddd'] . "\n";
           echo "적립금액 : " . $row['value5'] . "\n";
           echo "사용금액 + 적립금액 : ";
           echo $sdsd['ddddd'] + $row['value5'];
           echo "</pre>";
       }
   }
}*/


//$sql = sql_query("
//select
//value1 as od_id
//, e.value2 as mb_id
//, value5 + sum(ifnull(p.po_point, 0)) as '남은포인트'
//, value5
//, sum(ifnull(p.po_point, 0)) as send
//, m.mb_point
//from yc4_event_data e left join g4_point p
//on p.mb_id=e.value2 and date_format(p.po_datetime, '%Y%m%d')>=date_format(e.value6,'%Y%m%d') and p.po_point<0
//#and p.po_id not in (select po_id from g4_point where po_content like '%설날%')
//left join g4_member m on m.mb_id=e.value2
//where e.ev_code='new_year_dc_2017'
//AND e.value6 is not null
//and mb_point>0
//group by e.value1, e.value2, m.mb_point
//having value5 + sum(ifnull(p.po_point, 0)) >0
//order by value5+sum(ifnull(p.po_point, 0)), m.mb_point
//");
/*$sql = sql_query("
select
value1 as od_id
, e.value2 as mb_id
, value5 + sum(ifnull(p.po_point, 0)) as '남은포인트'
, value5
, sum(ifnull(p.po_point, 0)) as send
, m.mb_point
from yc4_event_data e left join g4_point p
on p.mb_id=e.value2 and date_format(p.po_datetime, '%Y%m%d')>=date_format(e.value6,'%Y%m%d') and p.po_point < 0 AND p.po_content not like '%자동%'
left join g4_member m on m.mb_id=e.value2
where e.ev_code='new_year_dc_2017'
AND e.value6 is not null AND e.value7 is null
and mb_point>0
group by e.value1, e.value2, m.mb_point
having sum(ifnull(p.po_point, 0))=0
order by value5+sum(ifnull(p.po_point, 0)), m.mb_point
");
while ($row = sql_fetch_array($sql)){
    $collect_point = $row['send'] + $row['value5'];
    if($collect_point > 0){
        $mb_point=sql_fetch("select mb_point from g4_member where mb_id='{$row['mb_id']}'");
        $point= $mb_point['mb_point']-$collect_point;
        if($point >=  0){
            echo '-'.$collect_point;
           // insert_point($row['mb_id'], '-'.$collect_point, ' 2017 설날 이벤트 5% 포인트 (주문번호 : '.$row['od_id'].') 포인트회수');
    }
    }

}*/
$sql = sql_query("
select 
value2 as mb_id
,value5 as point 
,date_format(value6,'%Y%m%d%h%i%s') as date
from yc4_event_data e
inner join  g4_member m 
on e.value2 = m.mb_id  
where e.ev_code='new_year_dc_2017'
and mb_point >0 
AND e.value6 is not null AND e.value7 is null
");
while ($row = sql_fetch_array($sql)){
    $sqls = sql_fetch("
    select sum(if(date_format(po_datetime,'%Y%m%d%h%i%s')<='{$row['date']}',po_point,0)) as aa,
sum(if(date_format(po_datetime,'%Y%m%d%h%i%s')>='{$row['date']}'and po_point<=0 ,po_point,0)) as dd
from g4_point 
where mb_id ='{$row['mb_id']}'
and po_content not like '%2017 설날 이벤트 5% 포인트 사용기간 경과 자동 소멸%'

    ");
    $sum =$sqls['aa']+$sqls['dd'];
    if($sum <=0){
        continue;
    }else{
        $sqls1 = sql_fetch("
select mb_point
from g4_member 
where mb_id ='{$row['mb_id']}'
    ");
        if($row['point'] <=$sum){
            if($sqls1['mb_point']-$row['point'] >=0){
             //  insert_point($row['mb_id'], '-'.$row['point'], ' 2017 설날 이벤트 5% 포인트 (주문번호 : '.$row['od_id'].') 사용기간 경과 자동 소멸');
/*                  echo "<pre>";
          echo "아이디 : ".$row['mb_id'].PHP_EOL;
          echo "aa : ".$sqls['aa'].PHP_EOL;
          echo "dd : ".$sqls['dd'].PHP_EOL;
          echo "회원 포인트 : ".$sqls1['mb_point'].PHP_EOL;
          echo "이제  : ".$sum.PHP_EOL;
          echo "설날 포인트 : ".$row['point'].PHP_EOL.PHP_EOL;
          echo "</pre>";*/
            }else {

            }
        }else{
           echo "<pre>";
            echo "아이디 : ".$row['mb_id'].PHP_EOL;
            echo "aa : ".$sqls['aa'].PHP_EOL;
            echo "dd : ".$sqls['dd'].PHP_EOL;
            echo "회원 포인트 : ".$sqls1['mb_point'].PHP_EOL;
            echo "이제  : ".$sum.PHP_EOL;
            echo "설날 포인트 : ".$row['point'].PHP_EOL.PHP_EOL;
            echo "</pre>";

        }

    }
}

/*2017 설날 이벤트 5% 포인트 사용기간 경과 자동 소멸
아이디 : daesoon2
aa : 131013
dd : -130577
회원 포인트 : 436
이제  : 436
설날 포인트 : 8455
아이디 : ksu
aa : 645548
dd : -643890
회원 포인트 : 1658
이제  : 1658
설날 포인트 : 6105
아이디 : firecannon
aa : 60004
dd : -56290
회원 포인트 : 6351
이제  : 3714
설날 포인트 : 7945*/
?>


3

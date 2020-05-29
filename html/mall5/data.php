<?php

if (version_compare(phpversion(), "5.3.0", ">=")  == 1)
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
else
  error_reporting(E_ALL & ~E_NOTICE);

include "_common.php";
require_once('classes/CMySQL.php');

$sParam = $GLOBALS['MySQL']->escape($_GET['q']); // escaping external data
if (! $sParam) exit;

switch ($_GET['mode']) {
    case 'xml': // using XML file as source of data
        $aValues = $aIndexes = array();
        $sFileData = file_get_contents('data.xml'); // reading file content
        $oXmlParser = xml_parser_create('UTF-8');
        xml_parse_into_struct($oXmlParser, $sFileData, $aValues, $aIndexes);
        xml_parser_free( $oXmlParser );

        $aTagIndexes = $aIndexes['ITEM'];
        if (count($aTagIndexes) <= 0) exit;
        foreach($aTagIndexes as $iTagIndex) {
            $sValue = $aValues[$iTagIndex]['value'];
            if (strpos($sValue, $sParam) !== false) {
                echo $sValue . "\n";
            }
        }
        break;
    case 'sql': // using database as source of data
        

		if($_GET['kwan']){ // 관 검색
			$sRequest = "select a.it_name
           from
                     yc4_station e
                     left join
                     shop_category d on e.s_id = d.s_id
                     left join
                     yc4_category_new b on b.ca_id like concat(d.ca_id,'%')
                     left join
                     yc4_category_item c on b.ca_id = c.ca_id
                     left join
                     yc4_item a on c.it_id = a.it_id
           where
                     a.it_use = 1 
                     and 
                     a.it_discontinued = 0 
                     and 
                     b.ca_use = 1 
                     and
                     d.s_id = '$_SESSION[s_id]'
                     and b.ca_id not like ('ev%')  and  match(a.it_name) against('+*{$sParam}*' in boolean mode)".$hide_caQ.$hide_makerQ;
		} else { // 전체검색
			$sRequest = "select it_name from yc4_item where it_use = '1' and it_name LIKE '%{$sParam}%'".$hide_caQ.$hide_makerQ;
		}

		
        $aItemInfo = $GLOBALS['MySQL']->getAll($sRequest);
        foreach ($aItemInfo as $aValues) {
            echo $aValues['it_name'] . "\n";
        }
        break;
}

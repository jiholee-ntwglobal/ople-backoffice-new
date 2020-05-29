<?php
// 암호화키값
$encrypt_key = '203ghghkdlxld!';

### PHP암호화 함수
function encrypt($data,$k) {
	$encrypt_these_chars = "1234567890ABCDEFGHIJKLMNOPQRTSUVWXYZabcdefghijklmnopqrstuvwxyz.,/?!$@^*()_+-=:;~{}";
	$t = $data;
	$result2;
	$ki;
	$ti;
	$keylength = strlen($k);
	$textlength = strlen($t);
	$modulo = strlen($encrypt_these_chars);
	$dbg_key;
	$dbg_inp;
	$dbg_sum;
	for ($result2 = "", $ki = $ti = 0; $ti < $textlength; $ti++, $ki++) {
		if ($ki >= $keylength) {
			$ki = 0;
		}
		$dbg_inp += "["+$ti+"]="+strpos($encrypt_these_chars, substr($t, $ti,1))+" ";
		$dbg_key += "["+$ki+"]="+strpos($encrypt_these_chars, substr($k, $ki,1))+" ";
		$dbg_sum += "["+$ti+"]="+strpos($encrypt_these_chars, substr($k, $ki,1))+ strpos($encrypt_these_chars, substr($t, $ti,1)) % $modulo +" ";
		$c = strpos($encrypt_these_chars, substr($t, $ti,1));
		$d;
		$e;
		if ($c >= 0) {
			$c = ($c + strpos($encrypt_these_chars, substr($k, $ki,1))) % $modulo;
			$d = substr($encrypt_these_chars, $c,1);
			$e .= $d;
		} else {
			$e += $t.substr($ti,1);
		}
	}
	$key2 = $result2;
	$debug = "Key  : "+$k+"\n"+"Input: "+$t+"\n"+"Key  : "+$dbg_key+"\n"+"Input: "+$dbg_inp+"\n"+"Enc  : "+$dbg_sum;
	return $e . "";
}

// 복호화
function decrypt($key2,$secret) {
	$encrypt_these_chars = "1234567890ABCDEFGHIJKLMNOPQRTSUVWXYZabcdefghijklmnopqrstuvwxyz.,/?!$@^*()_+-=:;~{}";
	$input = $key2;
	$output = "";
	$debug = "";
	$k = $secret;
	$t = $input;
	$result;
	$ki;
	$ti;
	$keylength = strlen($k);
	$textlength = strlen($t);
	$modulo = strlen($encrypt_these_chars);
	$dbg_key;
	$dbg_inp;
	$dbg_sum;
	for ($result = "", $ki = $ti = 0; $ti < $textlength; $ti++, $ki++) {
		if ($ki >= $keylength){
			$ki = 0;
		}
		$c = strpos($encrypt_these_chars, substr($t, $ti,1));
		if ($c >= 0) {
			$c = ($c - strpos($encrypt_these_chars , substr($k, $ki,1)) + $modulo) % $modulo;
			$result .= substr($encrypt_these_chars , $c, 1);
		} else {
			$result += substr($t, $ti,1);
		}
	}
	return $result;
}


?>
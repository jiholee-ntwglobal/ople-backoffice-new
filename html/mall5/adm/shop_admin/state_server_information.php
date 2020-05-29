<?php
$sub_menu = "500150";
include_once("./_common.php");
auth_check($auth[$sub_menu], "r");

$g4[title] = "서버정보";
include_once ("$g4[admin_path]/admin.head.php");

$s_hdd_info = nf((floor((disk_free_space($_SERVER['DOCUMENT_ROOT']) / 1024/1024/1024)*100)/100),2)." GB / ".nf((floor((disk_total_space($_SERVER['DOCUMENT_ROOT']) / 1024/1024/1024)*100)/100),2)." GB";

$u_hdd_info = nf((floor((disk_free_space("/") / 1024/1024/1024)*100)/100),2)." GB / ".nf((floor((disk_total_space("/") / 1024/1024/1024)*100)/100),2)." GB";
?>
<p class=p_title><b>[<?=$g4['title']?>]</b></p>
<table border=2 cellpadding=5 cellspacing=0 width=98% align=center style="border-collapse:collapse; border:4px solid #eaeaea;">
<tr>
	<td width=15% align=center><b>PHP</b></td>
	<td width=35%><?=PHP_OS." [v".phpversion()."]";?></td>
	<td width=15% align=center><b>MYSQL</b></td>
	<td width=35%>v<?=mysql_get_server_info();?></td>
</tr>
<tr>
	<td align=center><b>SERVER S/W</b></td>
	<td>
	<?
	if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
		echo $_SERVER['OS'].' ('.$_SERVER['SERVER_SOFTWARE'].')';
	else
		echo apache_get_version();
	?>
	</td>
	<td align=center><b>계정사용량/절대경로</b></td>
	<td><?=exec("du -sh ".$_SERVER['DOCUMENT_ROOT']);?></td>
</tr>
<tr>
	<td align=center><b>SERVER IP</b></td>
	<td>
	<?
	if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
		echo '209.216.56.102';
	else
		echo '209.216.56.102';
	?>
	</td>
	<td align=center><b>SERVER 이름</b></td>
	<td>
	<?
	if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
		echo $_SERVER["SERVER_NAME"];
	else
		echo $_ENV["HOSTNAME"];
	?>
	</td>
</tr>
<tr>
	<td align=center><b>서버 (남은/전체)</B></TD>
	<TD><?=$s_hdd_info?></td>
	<td align=center><b>계정 (남은/전체)</B></TD>
	<TD><?=$u_hdd_info?></TD>
</TR>
</table><br>

<table border=2 cellpadding=5 cellspacing=0 width=98% align=center style="border-collapse:collapse; border:4px solid #eaeaea;">
<tr>
	<td align=center>
		<a id=tab1 href="javascript:change_display(1, 'div_db_status', 'div_server_status', 'div_server_variables');">DB STATUS</a> |
		<a id=tab2 href="javascript:change_display(2, 'div_server_status', 'div_db_status', 'div_server_variables');">SERVER_STATUS</a> |
		<a id=tab3 href="javascript:change_display(3, 'div_server_variables', 'div_db_status', 'div_server_status');">SERVER_VARIABLES</a>
	</td>
</tr>
</table>
<br>


<?
class mysql_status
{
	var $version = 20040617; // this version

	## arguments, for detail print option
	##
	var $what	= 0;		// integer, get mode(1 <= $what <= 7)
	var $db	= '';		// mixed, target database(s)
	var $detail	= FALSE;	// boolean, show detail others databases
	var $link	= '';		// resource link

	## html color setting and check _get()
	##
	var $color	= array
		(
		'line' => '#000000', // line color of HTML table
		'th'   => '#CCCCCC', // head color of HTML table
		'td'   => '#FFFFFF', // TD color of HTML table
		);
	var $_get	= FALSE;	// check _get(), don't this change
	var $bits	= '0';		// string, bits of print mode(000 ~ 111)

	## basic, for table status from databases
	##
	var $host	= '209.216.56.102';		// string, MySQL server name or ip-addr
	var $via	= '';		// string, C/S connection method(SOCKET or TCP/IP)
	var $cdb	= '';		// string, current connected database name
	var $dbs	= '';		// string, number of databases
	var $databases= array();	// array, lists of databases name

	## databases infomation
	##
	var $mydb	= array();	// array, mydb(current database) information
	var $odb	= array();	// array, others database information
	var $dbsum	= array();	// array, simple database summary

	## server status or variables infomation
	##
	var $stat	= array();	// array, MySQL server status
	var $vars	= array();	// array, MySQL server variables
	var $comments	= array();	// array, MySQL server status comments

	## get mysql mydb status and server variables and status
	##
	## usage :
	##	object mysql_status[( [int what [, mixed db [, bool detail]]] )]
	##
	## arguments :
	##	$what	integer(0 <= $what <= 7), bits of get mode
	##		- default : $what 7
	##		- $what 0 : all, same as $what 7
	##		- $what 1 : _MYDB_, only my database(include $db) infomation
	##		- $what 2 : _STAT_, only server status
	##		- $what 3 : $what 1 + $what 2
	##		- $what 4 : _VARS_, only server variables
	##		- $what 5 : $what 1 + $what 4
	##		- $what 6 : $what 2 + $what 4
	##		- $what 7 : all, $what 1 + $waht 2 + $what 4
	##
	##	$db	mixed, if bit($what) 1 exists, table status of $db
	##		- $db false		 : boolean, default current connected database
	##		- $db array()		 : array, same as above
	##		- $db 0			 : integer|string, same as above
	##		- $db ''		 : string, same as above
	##		- $db 'foo'		 : string, only 'foo' database
	##		- $db 'foo,bar,some'	 : string, three databases(foo and bar and some)
	##		- $db array('foo')	 : array, same as above
	##		- $db array('foo','bar') : array, 'foo' and 'bar' databases
	##		- $db -1		 : integer, all databases(if available)
	##
	##	$detail boolean, if bit($what) 1 and $db exists, $db detail view, default false
	##
	function mysql_status($what=0, $db='', $detail=FALSE, $link='')
	{
	$this->what = (int)$what;
	$this->db = $db;
	$this->detail = (boolean)$detail; // boolean
	$this->get_host_info(); // host and connection method

	define('_MYDB_',1);
	define('_STAT_',2);
	define('_VARS_',4);
	}

	## get mydb, status, vars
	##
	function _get()
	{
	## set to TRUE this _get()
	##
	$this->_get = TRUE;

	## check $what
	##
	$what = (int)$this->what;
	if($what<1 || $what>7) $this->what = 7;

	$bits = $this->bits = $this->get_bits($this->what);

	## get mydb or others databases status
	##
	if($bits[0])
	{
		## $sql = 'SELECT DATABASE()'
		##
		$this->get_cdb();

		## $sql = 'SHOW DATABASES', same as 'mysqlshow [OPTIONS]' or mysql_list_dbs()
		##
		$this->get_dbs();

		## $sql = 'SHOW TABLE STATUS', same as 'mysqlshow [OPTIONS] --status mydb'
		##
		$this->get_mydb();

		## others database information
		##
		$this->get_odb($this->db);
	}

	## $sql = 'SHOW STATUS', same as 'mysqladmin [OPTIONS] extended-status'
	##
	if($bits[1]) $stat = $this->get_array('SHOW STATUS');

	## $sql = 'SHOW VARIABLES', same as 'mysqladmin [OPTIONS] variables'
	##
	if($bits[2]) $vars = $this->get_array('SHOW VARIABLES');

	## then, get main server status
	##
	if($bits[1])
	{
		if(!$bits[0]) $this->get_dbs(); // number of databases

		## get comments from $vars, $stat
		##
		$stat = $this->get_comments($vars,$stat);

		## then, get main server status
		##
		$this->get_stat($stat);
	}

	## and then, get main server variables
	##
	if($bits[2]) $this->get_vars($vars);

	## force to flush buffer
	##
	unset($vars);
	unset($stat);
	}

	## decimal to binary
	##
	function get_bits($dec)
	{
	if(!is_int($dec)) return 0;

	$dec = abs($dec);
	$bits = strrev(decbin($dec));

	return $bits; // is not integer, is string
	}

	## get host and via information
	##
	function get_host_info($link='')
	{
	$info = mysql_get_host_info();
	list($this->host,$this->via) = preg_split(';\s+via\s+;',$info);
	unset($info);
	}

	## get current connected database name(if available)
	##
	function get_cdb()
	{
	$sql = 'SELECT DATABASE()';
	if(!$result = @mysql_query($sql)) return;

	$this->cdb = mysql_result($result,0,0);
	mysql_free_result($result);
	}

	## get databases list
	##
	function get_dbs()
	{
	$result = @mysql_list_dbs(); // 'SHOW DATABASES' same as 'mysqlshow [OPTIONS]'
	$nums = @mysql_num_rows($result) + 0;

	if($nums < 2)
	{
		$this->dbs = 'hidden';
		$databases = array(); // this false
	} else
	{
		$this->dbs = $nums .' or more';
		while($list = @mysql_fetch_row($result))
		{ $databases[] = $list[0]; }
	}

	@mysql_free_result($result);

	if($databases && $this->cdb)
	{
		$databases = array_diff($databases,array($this->cdb));
		$this->databases = array_values($databases); // resort by numberic
	}
	}

	## get my database infomation
	##
	function get_mydb()
	{
	$this->mydb = $this->get_db_array($this->cdb);
	}

	## get others database infomation
	##
	function get_odb($db)
	{
	if($db)
	{
		if(is_array($db))
		{ $odb = array_values($db); } // sort by numberic

		else if($db == '-1') // all databases, if available
		{ $odb = &$this->databases; } // array, use reference

		else
		{ $odb = preg_split('/\s*,\s*/',preg_replace('/[,]+$/','',trim($db))); }
	}

	$size = count($odb);
	for($i=0; $i<$size; $i++)
	{ $this->odb[$odb[$i]] = $this->get_db_array($odb[$i]); }

	if(!$this->detail) unset($this->odb);
	}

	## get array of table status from $db
	##
	## http://www.mysql.com/doc/en/SHOW_TABLE_STATUS.html
	##
	function get_db_array($db='')
	{
	if($db) $sql = ' FROM '.$db;
	if(!$result = @mysql_query('SHOW TABLE STATUS'.$sql)) return;

	$i = 0;
	while($lists = mysql_fetch_assoc($result)) // associative array
	{
		$array[] = array
		(
		'name' => $lists['Name'],
		'type' => $lists['Type'],
		'rows' => number_format($lists['Rows']),
		'srow' => $this->hsize($lists['Avg_row_length']), // size of avg row
		'size' => $this->hsize($lists['Data_length']), // size of this table
		'sidx' => $this->hsize($lists['Index_length'])
		);

		$sum['rows'] += $lists['Rows'];
		$sum['size'] += $lists['Data_length'];
		$sum['sidx'] += $lists['Index_length'];

		$i++;
	}

	mysql_free_result($result);

	$this->dbsum[] = $array[] = array
	(
	'name' => '<B>'.$db.'</B>',
	'type' => $i.' tables',
	'rows' => number_format($sum['rows']),
	'srow' => $this->hsize(@round($sum['size']/$sum['rows'])),
	'size' => '<B>'.$this->hsize($sum['size']).'</B>',
	'sidx' => $this->hsize($sum['sidx'])
	);

	return $array;
	}

	## get MySQL server status and variables
	##
	## http://www.mysql.com/doc/en/SHOW_STATUS.html
	## http://www.mysql.com/doc/en/SHOW_VARIABLES.html
	## http://www.mysql.com/information/presentations/presentation-oscon2000-20000719/
	##
	function get_array($sql)
	{
	if(!$result = mysql_query($sql)) return;

	## get array to $vars
	##
	while($lists = mysql_fetch_row($result))
	{ $array[$lists[0]] = $lists[1]; }

	mysql_free_result($result);

	return $array;
	}

	## get comments and return $stat(added)
	##
	## 1) MySQL caches(all threads shared) [***]
	##	- key_buffer_size	: 8MB < INDEX key
	##	- table_cache		: 64 < number of open tables for all threads
	##	- thread_cache_size	: 0 < number of keep in a cache for reuse
	##
	## 2) MySQL buffers(not shared) [***]
	##	- join_buffer_size	: 1MB < FULL-JOIN
	##	- myisam_sort_buffer_size : 8MB < REPAIR, ALTER, LOAD
	##	- record_buffer		: 2MB < sequential scan allocates
	##	- record_rnd_buffer	: 2MB < ORDER BY(to avoid a disk seeks)
	##	- sort_buffer		: 2MB < ORDER BY, GROUP BY
	##	- tmp_table_size	: 32MB < advanced GROUP BY(to avoid a disk seeks)
	##
	## 3) MySQL memory size of INDEX(key)/JOIN/RECORD(read)/SORT/TABLE
	##	- INDEX(key)		: 8MB < [***] key_buffer_size (shared)
	##	- JOIN 			: 1MB <	[***] join_buffer_size (not shared)
	##	- RECORD(read)		: 2MB < [***] record_buffer (not shared)
	##				: 2MB < [***] record_rnd_buffer (not shared)
	##	- SORT			: 8MB < [   ] myisam_sort_buffer_size (not shared)
	##				: 2MB <	[***] sort_buffer (not shared)
	##	- TABLE(tmp)		: 32MB< [***] tmp_table_size(not shared)
	##
	## 4) MySQL timeout
	##	- connect_timeout	: 5 > bad handshake timeout
	##	- interactive_timeout	: 28800 > active to re-active timeout
	##	- wait_timeout		: 28000 > not active to active timeout
	##
	## 5) MySQL connections
	##	- max_connections	: 100 < 'to many connections' error
	##	- max_user_connections	: 0(no limit) or upto [Max_used_connect] status
	##
	function get_comments($vars, $stat)
	{
	global $_SERVER; // for PHP/4.0.x

	$stat['ab_clients'] =
		sprintf('%.2f',$stat['Aborted_clients']*100/$stat['Connections']);
	$stat['ab_connects'] =
		sprintf('%.2f',$stat['Aborted_connects']*100/$stat['Connections']);

	$stat['per_qs'] = sprintf('%.2f',$stat['Questions']/$stat['Uptime']);
	$stat['per_qc'] = sprintf('%.2f',$stat['Questions']/$stat['Connections']);
	$stat['per_cs'] = sprintf('%.2f',$stat['Connections']/$stat['Uptime']);
	$stat['per_kr'] = sprintf('%.2f',@($stat['Key_reads']/$stat['Key_read_requests']));
	$stat['per_kw'] = sprintf('%.2f',@($stat['Key_writes']/$stat['Key_write_requests']));
	$stat['per_tc'] = sprintf('%.2f',$stat['Threads_created']/$stat['Connections']);
	$stat['per_oc'] = sprintf('%.2f',$stat['Opened_tables']/$stat['Connections']);
	$stat['per_ic'] = sprintf('%.3f',$stat['Uptime']/$stat['Connections']);
	$stat['per_iq'] = sprintf('%.3f',$stat['Uptime']/$stat['Questions']);
	$stat['per_pd'] = sprintf('%.2f',$stat['Com_delete']*100/$stat['Questions']);
	$stat['per_pi'] = sprintf('%.2f',$stat['Com_insert']*100/$stat['Questions']);
	$stat['per_ps'] = sprintf('%.2f',$stat['Com_select']*100/$stat['Questions']);
	$stat['per_pu'] = sprintf('%.2f',$stat['Com_update']*100/$stat['Questions']);
	$stat['per_pr'] = sprintf('%.2f',$stat['Com_replace']*100/$stat['Questions']);
	$stat['per_pl'] = sprintf('%.2f',$stat['Com_lock_tables']*100/$stat['Questions']);
	$stat['per_td'] = sprintf('%.2f',
		@($stat['Created_tmp_disk_tables']*100/$stat['Created_tmp_tables']));
	$stat['per_rs'] = sprintf('%.2f',$stat['Bytes_sent']/$stat['Bytes_received']);
	$stat['tcs'] = ceil($stat['per_cs']);

	if($stat['Max_used_connections'] > 0)
	{
		$stat['life_time'] =
			ceil((@($stat['Max_used_connections']*$stat['per_ic'])+1)/2);
		$stat['wto'] =
			ceil($stat['life_time']-@($stat['life_time']/$stat['per_qc']));
		$stat['life_time_str'] = number_format($stat['life_time']);
		$stat['wto_str'] = number_format($stat['wto']);
	} else
	{
		$stat['life_time_str'] = $stat['wto_str'] = 'NA';
	}

	## share refer vars
	##
	if($vars)
	{
		$stat['life_time_max'] =
			ceil((@($vars['max_connections']*$stat['per_ic'])+1)/2);
		$stat['wto_max'] =
			ceil($stat['life_time_max'] -
			@($stat['life_time_max']/$stat['per_qc']));
		$stat['life_time_max_str'] =
			number_format($stat['life_time_max']);
		$stat['wto_max_str'] =
			number_format($stat['wto_max']);

		$stat['refer_vars_wait_timeout'] =
			number_format($vars['wait_timeout']).
			'('.$this->runtime($vars['wait_timeout']).')';
		$stat['refer_vars_interactive_timeout'] =
			number_format($vars['interactive_timeout']).
			'('.$this->runtime($vars['interactive_timeout']).')';
		$stat['refer_vars_connect_timeout'] =
			number_format($vars['connect_timeout']).
			'('.$this->runtime($vars['connect_timeout']).')';
		$stat['refer_vars_key_buffer_size'] =
			number_format($vars['key_buffer_size']).
			'('.$this->hsize($vars['key_buffer_size']).')';
		$stat['refer_vars_max_connections'] =
			number_format($vars['max_connections']);
		$stat['refer_vars_table_cache'] =
			number_format($vars['table_cache']);
		$stat['refer_vars_thread_cache_size'] =
			number_format($vars['thread_cache_size']);
		$stat['refer_vars_tmp_table_size'] =
			number_format($vars['tmp_table_size']).
			'('.$this->hsize($vars['tmp_table_size']).')';
		$stat['refer_vars_long_query_time'] =
			number_format($vars['long_query_time']).
			'('.$this->runtime($vars['long_query_time']).')';
	}

	$B['b'] = '<FONT COLOR=#0000FF>';	// blue color, for ok
	$B['r'] = '<FONT COLOR=#FF0000>';	// red color, for warning
	$B['n'] = '<FONT COLOR=#990000>';	// brown, form notice
	$B['e'] = '</FONT>';

	$busy = array('normal','some busy','very busy','hot busy');
	$ok = $B['b'].', 정상'.$B['e'];
	$rep = $B['n'].',<BR>Replication(미러링)기법을 사용하지 않는다면 '.
		'이 기능을 OFF 하세요.'.$B['e'];
	$tc = '<A HREF="http://linuxchannel.net/board/read.php?table=alpha&no=27"'.
		' TARGET="_blank">[참고]</A>';
	$wt = '<A HREF="http://linuxchannel.net/docs/mysql-timeout.txt"'.
		' TARGET="_blank">[참고]</A>';

	if($stat['per_qs'] < 1) $bkey = 0;
	else if($stat['per_qs'] < 10) $bkey = 1;
	else if($stat['per_qs'] < 20) $bkey = 2;
	else $bkey = 3;

	$this->comments['busy'] = &$busy[$bkey];
	$this->comments['wto'] = '계산된 추정값(추세:'.$stat['wto_str'].
		'초, 임계:'.$stat['wto_max_str'].'초)';
	$this->comments['tcs'] = '계산된 추정값(최소:'.$stat['tcs'].
		', 권장:'.($stat['tcs']*2+1).', 최대:'.($stat['tcs']*3+2).')';

	if($stat['Uptime'] < 86400)
	{
		$this->comments['notice'] = $B['n'].', 예상수치'.$B['e'];
		$this->comments['poor'] = $B['n'].', 누적된 통계자료가 너무 적습니다.'.$B['e'];
	}

	if($stat['ab_clients'] > 1)
	{
		$this->comments['wait_timeout'] =
		$B['r'].',<BR>연결 취소가 많습니다(기준 1%). '.
		$stat['refer_vars_wait_timeout'].'(wait_timeout)초나 '.
		$stat['refer_vars_interactive_timeout'].
		'(interactive_timeout)초보다 '.
		'늦게 close 하거나 연결을 끊지 않는 경우가 많습니다.'.
		$wt.$B['e'];
	}
	else $this->comments['wait_timeout'] = $ok.$wt;

	$this->comments['interactive_timeout'] = &$this->comments['wait_timeout'];

	if($stat['ab_connects'] > 1)
	{
		$this->comments['connect_timeout'] =
		$B['r'].',<BR>잘못된(fail) 접속 요청이 많습니다(연결 실패)'.
		'(기준 1 %). '.$stat['refer_vars_connect_timeout'].
		'(connect_timeout)보다 긴 연결 요청, 틀린 암호, 접근 권한 문제.'.
		$B['e'];
	}
	else $this->comments['connect_timeout'] = &$ok;

	if($stat['Key_read_requests'] > 100 && $stat['per_kr'] > 0.01)
	{
		$this->comments['key_buffer_size'] = $B['r'].
		',<BR>DISK에서 Key를 읽는 요청이 많습니다'.
		'(기준 0.01%). '.$stat['refer_vars_key_buffer_size'].
		'(key_buffer_size)값을 올리세요.'.$B['e'];
	}
	else $this->comments['key_buffer_size'] = &$ok;

	if($vars['log_update'] == 'ON') $this->comments['log_update'] = &$rep;
	if($vars['log_bin'] == 'ON') $this->comments['log_bin'] = &$rep;

	if($vars['max_connections'])
	{
		$max['vars'] = max($stat['Max_used_connections'],$vars['max_connections']);
		$max['stat'] = $stat['Max_used_connections'] / $vars['max_connections'];

		if($max['stat'] > 0.95 )
		{
			if($stat['wto']>5 && $vars['wait_timeout']>$stat['wto'])
			{
				$checkwto = ' 또한 '.
				$this->stat['refer_vars_wait_timeout'].'[wait_timeout]을 '.
				$this->comments['wto'].'으로 튜닝해 보세요';
			}

			$max['to'] = $max['vars'] + (int)($max['vars']*0.2);
			$this->comments['max_connections'] = $B['r'].
			',<BR>최대 접속수에 근접하거나 초과했습니다. '.
			$stat['refer_vars_max_connections'].
			'[max_connections]값을 '.$max['to'].' 정도로 올리세요.'.
			$checkwto.$B['e'];
		}
		else $this->comments['max_connections'] = &$ok;

		$max['table'] = $max['vars'] * 2;

		if($stat['Opened_tables'] > $vars['table_cache'] && $max['table'] > $vars['table_cache'])
		{
			$this->comments['table_cache'] = $B['r'].',<BR>'.
			$stat['refer_vars_table_cache'].'[table_cache]값을 '.$max['table'].' 정도로 올리세요.'.
			$B['e'];
		} else
		{	$this->comments['table_cache'] =
			$B['b'].', '.$stat['per_oc'].'/Connection'.$B['e'];
		}
	}

	if($vars['safe_show_database'] == 'OFF')
	{
		$this->comments['safe_show_database'] = $B['r'].',<BR>'.
		'보안상 좋지 않는 설정입니다. 이 설정을 ON 하세요'.$B['e'];
	}
	else $this->comments['safe_show_database'] = &$ok;

	if($vars['skip_external_locking']) $slm = 'external_';
	if($vars['skip_'.$slm.'locking'] == 'OFF')
	{
		$this->comments['skip_'.$slm.'locking'] = $B['n'].',<BR>'.
		'현재 이 MySQL 서버는 외부 Lock을 사용하는 것으로 설정되어 있습니다. '.
		'잘 모르겠으면 ON으로 설정하세요.'.$B['e'];
	}

	if($vars['skip_networking'] == 'OFF')
	{
		if(preg_match('/TCP/',$this->via)
			&& $_SERVER['SERVER_ADDR'] == @gethostbyname($this->host))
		{
			$this->comments['skip_networking'] = $B['r'].',<BR>'.
			'MySQL 서버와 클라이언트가 서로 동일한 IP 주소임에도 불구하고 '.
			'TCP/IP 연결을 사용하고 있습니다(성능저하). 독립된 다른 호스트에서 '.
			'연결하는 경우가 없다면, 이 옵션을 ON 으로 설정하고 '.
			'UNIX domain socket(localhost:'.$vars['socket'].')을 사용하세요.'.
			$B['e'];
		}
		else if(preg_match('/socket/',$this->via))
		{
			$this->comments['skip_networking'] = $B['n'].',<BR>'.
			'현재 UNIX domain socket(:'.$vars['socket'].')을 사용하고 있습니다. '.
			'독립된 다른 호스트에서 연결하는 경우가 없다면 이 옵션을 ON으로 '.
			'설정하세요'.$B['e'];
		}
		else $this->comments['skip_networking'] = &$ok;
	}
	else
	{
		$this->comments['skip_networking'] = $B['n'].',<BR>다른 독립된 호스트에서 '.
		'접속하지 못하도록 원천봉쇄되어 있습니다.'.$B['e'];
	}

	if($vars['skip_show_database'] == 'ON')
	{
		$this->comments['skip_show_database'] =
		$B['n'].',<BR>너무 제한적인 설정인것 같습니다.'.$B['e'];
	}

	if($stat['per_tc'] > 0.01)
	{
		if($stat['per_cs'] > 1)
		{
			$this->comments['thread_cache_size'] = $B['r'].',<BR>'.
			$stat['refer_vars_thread_cache_size'].
			'[thread_cache_size]값을 올리세요.'.$tc.$B['e'];
		}
		else if($vars['thread_cache_size'] < 2)
		{
			$this->comments['thread_cache_size'] = $B['r'].',<BR>'.
			'현재 서버가 매우 바쁘지 않으므로 '.
			$stat['refer_vars_thread_cache_size'].
			'[thread_cache_size]값을 튜닝 보세요.'.$tc.$B['e'];
		}
		/***
		else
		{
			$this->comments['thread_cache_size'] = $B['r'].',<BR>'.
			'현재 서버가 매우 바쁘지 않으므로, 시간이 좀더 지나면 '.
			'이 메시지가 보이질 않아야 정상입니다.'.$tc.$B['e'];
		}
		***/
	}
	else $this->comments['thread_cache_size'] = $ok.$tc;

	return $stat;
	}

	## get main status
	##
	function get_stat($stat)
	{
	$this->stat = array
	(
	'TOTAL_STATUS' =>
		array($stat['per_qs'],
		'<B>'.$this->comments['busy'].'</B> (1초당 평균 쿼리 요청수)'),
	'CRITICAL_LIFE_TIME' =>
		array($stat['life_time_str'].'초',
		'커넥션당 평균적인(추세) 임계 life time(CUR)'),
	'CRITICAL_LIFE_TIME_MAX' =>
		array($stat['life_time_max_str'].'초',
		'커넥션당 평균적인(최대) 임계 life time(EXP)'),
	'All_databases' =>
		array($this->dbs,
		'데이터베이스 수'),
	'Aborted_clients' =>
		array(number_format($stat['Aborted_clients']),
		'연결 취소 Clients 수, refer '.
		$stat['refer_vars_wait_timeout'].'[wait_timeout] and '.
		$stat['refer_vars_interactive_timeout'].'[interactive_timeout]'),
	'Aborted_connects' =>
		array(number_format($stat['Aborted_connects']),
		'연결 실패수, refer '.
		$stat['refer_vars_connect_timeout'].'[connect_timeout]'),
	'Aborted_clients_percent' =>
		array($stat['ab_clients'].'%',
		'연결 취소율'.$this->comments['wait_timeout']),
	'Aborted_connects_percent' =>
		array($stat['ab_connects'].'%',
		'연결 실패율'.$this->comments['connect_timeout']),
	'Bytes_received' =>
		array($this->hsize($stat['Bytes_received']),
		'수신량(총)'),
	'Bytes_sent' =>
		array($this->hsize($stat['Bytes_sent']),
		'전송량(총)'),
	'Bytes_per_rs' =>
		array($stat['per_rs'],
		'받고(1) 보내는(x) 비율, 1:x'),
	'Bytes_sent_per_sec' =>
		array($this->hsize($stat['Bytes_sent']/$stat['Uptime']),
		'전송량(초당)'),
	'Bytes_sent_per_min' =>
		array($this->hsize($stat['Bytes_sent']*60/$stat['Uptime']),
		'전송량(분당)'),
	'Bytes_sent_per_hour' =>
		array($this->hsize($stat['Bytes_sent']*3600/$stat['Uptime']),
		'전송량(시간당)'),
	'Bytes_sent_per_day' =>
		array($this->hsize($stat['Bytes_sent']*86400/$stat['Uptime']),
		'전송량(하루평균)'.$this->comments['notice']),
	'Com_delete' =>
		array($stat['per_pd'].'%',
		'DELETE 쿼리 사용율(전체 DB 통계)'),
	'Com_insert' =>
		array($stat['per_pi'].'%',
		'INSERT 쿼리 사용율(전체 DB 통계)'),
	'Com_select' =>
		array($stat['per_ps'].'%',
		'SELECT 쿼리 사용율(전체 DB 통계)'),
	'Com_update' =>
		array($stat['per_pu'].'%',
		'UPDATE 쿼리 사용율(전체 DB 통계)'),
	'Com_replace' =>
		array($stat['per_pr'].'%',
		'REPLACE 쿼리 사용율(전체 DB 통계)'),
	'Com_lock_tables' =>
		array($stat['per_pl'].'%',
		'LOCK TABLES 쿼리 사용율(전체 DB 통계)'),
	'Connections' =>
		array(number_format($stat['Connections']),
		'총 connections 수'),
	'Connections_per_sec' =>
		array($stat['per_cs'],
		'초당 connections 수'),
	'Created_tmp_tables' =>
		array(number_format($stat['Created_tmp_tables']),
		'메모리에 생성된 임시 테이블 수'),
	'Created_tmp_disk_tables' =>
		array(number_format($stat['Created_tmp_disk_tables']),
		'DISK에 생성된 임시 테이블 수, '.
		$stat['refer_vars_tmp_table_size'].
		'[tmp_table_size]보다 많은 메모리를 요구하는 SQL 구문을 실행시 누계됨'),
	'Create_tmp_disk_tables_per' =>
		array($stat['per_td'].'%',
		'DISK에 생성된 임시 테이블 사용 비율'),
	'Intervals_of_connection' =>
		array($stat['per_ic'],
		'커넥션 주기(초)'),
	'Intervals_of_question' =>
		array($stat['per_iq'],
		'쿼리 주기(초)'),
	'Key_reads' =>
		array(number_format($stat['Key_reads']),
		'DISK에서 Key 읽은 수'),
	'Key_read_requests' =>
		array(number_format($stat['Key_read_requests']),
		'캐시에서 Key 읽기 요청수'),
	'Key_writes' =>
		array(number_format($stat['Key_writes']),
		'DISK에 Key를 쓴 수'),
	'Key_write_requests' =>
		array(number_format($stat['Key_write_requests']),
		'캐시에 Key 쓰기 요청수'),
	'Key_reads_per_request' => // to < 0.01
		array($stat['per_kr'].'%',
		'DISK에서 Key를 읽는 비율'.$this->comments['key_buffer_size']),
	'Key_writes_per_request' =>
		array($stat['per_kw'].'%',
		'DISK에 Key를 쓰는 비율, 보통 1에 가까워야 정상'),
	'Max_used_connections' =>
		array(number_format($stat['Max_used_connections']),
		'동시에 연결된 최대값'.$this->comments['max_connections']),
	'Open_tables' =>
		array(number_format($stat['Open_tables']),
		'현재 열려있는 Tables 수'),
	'Opened_tables' =>
		array(number_format($stat['Opened_tables']),
		'열렸던 Tables 수, refer '.$stat['refer_vars_table_cache'].
		'[table_cache]'.$this->comments['table_cache']),
	'Questions' =>
		array(number_format($stat['Questions']),
		'현재까지 쿼리 요청수'),
	'Questions_per_connect' =>
		array($stat['per_qc'],
		'커넥션당 평균 쿼리 요청수'),
	'Select_full_join' =>
		array(number_format($stat['Select_full_join']),
		'Key 없이 FULL-JOIN 횟수'),
	'Slow_queries' =>
		array(number_format($stat['Slow_queries']),
		$stat['refer_vars_long_query_time'].
		'(long_query_time)초 보다 큰 쿼리 요청수'),
	'Table_locks_waited' =>
		array($this->runtime($stat['Table_locks_waited']),
		'Lock wait 총시간'),
	'Threads_cached' =>
		array(number_format($stat['Threads_cached']),
		'캐시된 쓰레드수, '.$stat['refer_vars_thread_cache_size'].
		'[thread_cache_size]보다 항상 작음'),
	'Threads_connected' =>
		array(number_format($stat['Threads_connected']),
		'현재 열려있는 커넥션 수'),
	'Threads_created' =>
		array(number_format($stat['Threads_created']),
		'현재까지 handle 커넥션에 생성된 쓰레드 수'),
	'Threads_running' =>
		array(number_format($stat['Threads_running']),
		'현재 구동중인 쓰레드 수(not sleeping)'),
	'Threads_created_per' =>
		array($stat['per_tc'].'%',
		'커넥션당 생성된 평균 쓰레드 수(기준 0.01%)'.
		$this->comments['thread_cache_size']),
	'Uptime' =>
		array($this->runtime($stat['Uptime']),
		'최근 MySQL 서버 구동 시간'),
	);
	}

	## get main variables
	##
	function get_vars($vars)
	{
	$ver = explode('.',preg_replace('/-.+$/','',$vars['version']));
	$ver[1] = sprintf('%02d',$ver[1]);
	$ver[2] = sprintf('%02d',$ver[2]);
	$ver = $ver[0].$ver[1].$ver[2];

	## http://www.mysql.com/doc/en/Upgrading-from-3.23.html
	## myisam_max_extra_sort_file_size, myisam_max_sort_file_size
	##
	if($ver < 40003)
	{
		$upg = array(
		'ast'		=> 1048576, // 1024*1024
		//'bulk'	=> 'myisam_bulk_insert_tree_size',
		//'qtype'	=> 'query_cache_startup_type',
		'rbuffer'	=> 'record_buffer',
		'rndbuffer'	=> 'record_rnd_buffer',
		'sbuffer'	=> 'sort_buffer',
		//'warn'	=> 'warnings',
		'skiplocking'	=> 'skip_locking',
		);
	} else
	{
		$_v4003 = TRUE;
		$upg = array(
		'ast'		=> 1,
		//'bulk'	=> 'bulk_insert_buffer_size',
		//'qtype'	=> 'query_cache_type',
		'rbuffer'	=> 'read_buffer_size',
		'rndbuffer'	=> 'read_rnd_buffer_size',
		'sbuffer'	=> 'sort_buffer_size',
		//'warn'	=> 'log-warnings',
		'skiplocking'	=> 'skip_external_locking',
		);
	}

	$this->vars = array
	(
	'back_log' =>
		array(number_format($vars['back_log']),'man 2 listen, '.
		'net.ipv4.tcp_max_syn_backlog'),
	'connect_timeout' =>
		array(number_format($vars['connect_timeout']).
		'('.$this->runtime($vars['connect_timeout']).')',
		'bad handshake timeout(초)'.$this->comments['connect_timeout']),
	//'delayed_insert_limit',
	//'delayed_insert_timeout',
	'join_buffer_size' =>
		array(number_format($vars['join_buffer_size']).
		'('.$this->hsize($vars['join_buffer_size']).')',
		'[***] FULL-JOIN에 사용되는 메모리'),
	'key_buffer_size' =>
		array(number_format($vars['key_buffer_size']).
		'('.$this->hsize($vars['key_buffer_size']).')',
		'[***] INDEX key buffer에 사용되는 메모리, refer [Key_xxx]'.
		$this->comments['key_buffer_size']),
	'log' =>
		array($vars['log'],'로그 기록 여부'),
	'log_update' =>
		array($vars['log_update'],
		'UPDATE 로그 기록 여부'.$this->comments['log_update']),
	'log_bin' =>
		array($vars['log_bin'],
		'binary 로그 기록 여부'.$this->comments['log_bin']),
	'long_query_time' =>		// status(--log-slow-queries=file)
		array(number_format($vars['long_query_time']).
		'('.$this->runtime($vars['long_query_time']).')',
		'refer '.$this->stat['Slow_queries'][0].'[Slow_queries]'),
	'lower_case_table_names' =>
		array($vars['lower_case_table_names'],
		'테이블 대소문자 구별유무(0 구별)'),
	'max_allowed_packet' =>
		array(number_format($vars['max_allowed_packet']).
		'('.$this->hsize($vars['max_allowed_packet']).')',
		'최대 허용할 패킷'),
	'max_connections' =>
		array(number_format($vars['max_connections']),
		'[***] 최대 동시 접속 커넥션 수, refer '.
		$this->stat['Max_used_connections'][0].'[Max_used_connections]'.
		$this->comments['max_connections']),
	'max_delayed_threads' =>
		array($vars['max_delayed_threads'],
		'최대 delayed 쓰레드 수, INSERT DELAYED 구문과 관련됨'),

	'max_join_size' =>
		array(number_format($vars['max_join_size']).
		'('.$this->hsize($vars['max_join_size']).')',
		'JOIN에 사용될 최대 크기(메모리 아님)'),
	'max_sort_length' =>
		array(number_format($vars['max_sort_length']).
		'('.$this->hsize($vars['max_sort_length']).')',
		'TEXT, BLOB의 정렬에 사용되는 최대 크기'),
	'max_user_connections' =>
		array(number_format($vars['max_user_connections']),
		'최대 동시 user 수(0은 제한없음)'),
	/***
	'max_tmp_tables' =>	// doesn't yet do anything
		array(number_format($vars['max_tmp_tables']),
		'최대 임시 테이블 수'),
	***/
	'myisam_max_extra_sort_file_size' =>	// MBytes(4.0.3 use bytes)
		array(number_format($vars['myisam_max_extra_sort_file_size']).'('.
		$this->hsize($vars['myisam_max_extra_sort_file_size']*$upg['ast']).
		')','빠른 INDEX를 생성시 사용되는 최대 임시 파일 크기'),
	'myisam_max_sort_file_size' =>	// MBytes(4.0.3 use bytes)
		array(number_format($vars['myisam_max_sort_file_size']).'('.
		$this->hsize($vars['myisam_max_sort_file_size']*$upg['ast']).')',
		'REPAIR, ALTER, LOAD...등 INDEX를 재생성시 사용되는 임시 파일 크기'),
	'myisam_sort_buffer_size' =>	// [***] use REPAIR, INDEX sort
		array(number_format($vars['myisam_sort_buffer_size']).
		'('.$this->hsize($vars['myisam_sort_buffer_size']).')',
		'[***] REPAIR, INDEX, ALTER 정렬에 사용하는 메모리'),
	'open_files_limit' =>	// error 'Too many open file files', default '0'
		array(number_format($vars['open_files_limit']),
		'파일 open 제한 수(0은 제한없음)'),
	/***
	'query_buffer_size' =>	// only 3.23.x, start(init) query buffer, default '0'
		array(number_format($vars['query_buffer_size']).
		'('.$this->hsize($vars['query_buffer_size']).')',
		'초기 쿼리 버퍼용 메모리(기본값은 0)'),
	***/
	$upg['rbuffer'] =>
		array(number_format($vars[$upg['rbuffer']]).
		'('.$this->hsize($vars[$upg['rbuffer']]).')',
		'[***] 순차적인 검색에 사용되는 메모리(read_buffer_size)'),
	$upg['rndbuffer'] =>	// [***] use improve ORDER BY(to avoid a disk seeks)
		array(number_format($vars[$upg['rndbuffer']]).
		'('.$this->hsize($vars[$upg['rndbuffer']]).')',
		'[***] ORDER BY 정렬에 사용되는 메모리(to avoid a disk seeks)'),
	'safe_show_database' =>	// only Ver < 4.0.3
		array($vars['safe_show_database'],
		'제한적인 SHOW DATABASE 구문 사용 여부'.
		$this->comments['safe_show_database']),
	$upg['skiplocking'] =>
		array($vars[$upg['skiplocking']],
		'외부 lock 을 사용한다면 OFF로 설정.'.$this->comments[$upg['skiplocking']]),
	'skip_networking' =>
		array($vars['skip_networking'],
		'TCP/IP 연결 여부(ON은 UNIX domain socket, OFF는 TCP/IP 연결)'.
		$this->comments['skip_networking']),
	'skip_show_database' =>
		array($vars['skip_show_database'],
		'SHOW DATABASES 구문 사용 여부(ON은 원천봉쇄, OFF는 사용가능)'.
		$this->comments['skip_show_database']),

	$upg['sbuffer'] =>	// [***] use ORDER BY, GROUP BY
		array(number_format($vars[$upg['sbuffer']]).
		'('.$this->hsize($vars[$upg['sbuffer']]).')',
		'[***] ORDER BY, GROUP BY 정렬에 사용되는 메모리'),
	'table_cache' =>	// [***] refer to [Opened_tables] status
		array(number_format($vars['table_cache']),
		'[***] 한번에(all thread) 열 수 있는 테이블 수, refer '.
		$this->stat['Opened_tables'][0].'[Opened_tables]'),
	'thread_cache_size' =>	// [***] refer to [Connections] and [Threads_created] status
		array(number_format($vars['thread_cache_size']),
		'[***] 쓰레드 캐시 재사용 수, refer '.
		$this->stat['Connections'][0].'[Connections], '.
		$this->stat['Threads_created'][0].'[Threads_created], '.
		$this->comments['tcs'].$this->comments['thread_cache_size']),
	'tmp_table_size' =>	// [***] use in-memory(to disk), GROUP BY
		array(number_format($vars['tmp_table_size']).
		'('.$this->hsize($vars['tmp_table_size']).')',
		'[***] 복잡한 GROUP BY 정렬에 사용되는 메모리(to avoid a disk seeks), '.
		'refer '.$this->stat['Created_tmp_disk_tables'][0].
		'[Created_tmp_disk_tables]'),
	'interactive_timeout' =>
		array(number_format($vars['interactive_timeout']).
		'('.$this->runtime($vars['interactive_timeout']).')',
		'interactive -> re-active에 기다리는 시간(이후 closed)'.
		$this->comments['interactive_timeout']),
	'wait_timeout' =>
		array(number_format($vars['wait_timeout']).
		'('.$this->runtime($vars['wait_timeout']).')',
		'none interactive -> re-active에 기다리는 시간(이후 closed), '.
		$this->comments['wto'].
		$this->comments['wait_timeout']),
	'timezone' =>
		array($vars['timezone'],
		'현재 MySQL 서버의 TIME-ZONE'),
	'version' =>
		array($vars['version'],
		'현재 MySQL 서버 버전')
	);

	if($_v4003) unset($this->vars['safe_show_database']);
	}

	## parsing to html
	##
	## arguments :
	##	$color		array, parsing to TABLE cloor
	##	$color['line']	string, line color of HTML table, default '#000000'
	##	$color['th']	string, head color of HTML table, default '#CCCCCC'
	##	$color['td']	string, TD color of HTML table, default '#FFFFFF'
	##
	##	$return		boolean, 1 return, 0 print
	##
	function tohtml($color=array(), $return=0)
	{
		static $version = FALSE;

		if(!$this->_get) $this->_get();

		$this->_get = FALSE; // reset to false

		if(!$color) $color = $this->color;
		if(!$color['line']) $color['line'] = '#000000';
		if(!$color['th']) $color['th'] = '#CCCCCC';
		if(!$color['td']) $color['td'] = '#FFFFFF';

		//if(!$version) $html = 'mysql status Ver. '.$this->version."<BR>\n";
		//$version = TRUE;
		$html = "";
		$t = array(array('테이블','타입'),array('데이터베이스','개수','(계)'));

		if($this->bits[0])
		{
			$html .= $this->db2html($this->mydb,$color,'MY',$t[0]);
			if($this->odb)
			{
				$tmp = array_keys($this->odb);
				foreach($tmp AS $key)
				{ $html .= $this->db2html($this->odb[$key],$color,$key,$t[0]); }
				unset($tmp);
			}
			else if(count($this->dbsum) > 1)
			{
				$html .= $this->db2html($this->dbsum,$color,'SUMMARY OF',$t[1]);
			}
		}

		//unset($t);

		if($this->bits[1]) $html .= $this->array2html($this->stat,$color,'STATUS');
		if($this->bits[2]) $html .= $this->array2html($this->vars,$color,'VARIABLES');

		if($return) return $html;
		else echo $html;
	}

	function db2html($array, $color, $name, $t)
	{
		$r = "<div id=div_db_status style='display:block;'>";
		$r .= "<p style='margin:0 0 0 10px;'><B>{$name} DATABASE STATUS</B> (connected by {$this->via})</p>
			<table border=2 cellspacing=0 cellpadding=5 align=center bordercolor='#95A3AC' width=99% class=state_table>
			<tr>
			<TD ALIGN=right NOWRAP class=yalign_head>{$t[0]} 이름</TD>
			<TD ALIGN=center NOWRAP class=yalign_head>테이블 {$t[1]}</TD>
			<TD ALIGN=right NOWRAP class=yalign_head>레코드 수{$t[2]}</TD>
			<TD ALIGN=right NOWRAP class=yalign_head>레코드 평균 크기</TD>
			<TD ALIGN=right NOWRAP class=yalign_head>사용량{$t[2]}</TD>
			<TD ALIGN=right NOWRAP class=yalign_head>Index 크기{$t[2]}</TD>
			</TR>";

		$nums = count($array);
		for($i=0; $i<$nums; $i++)
		{
			$r .= "<tr onMouseOver=\"this.style.backgroundColor='#B1BCC2';\" onMouseOut=\"this.style.backgroundColor='';\">
				<TD ALIGN=right>{$array[$i]['name']}</TD>
				<TD ALIGN=center>{$array[$i]['type']}</TD>
				<TD ALIGN=right>{$array[$i]['rows']}</TD>
				<TD ALIGN=right>{$array[$i]['srow']}</TD>
				<TD ALIGN=right>{$array[$i]['size']}</TD>
				<TD ALIGN=right>{$array[$i]['sidx']}</TD>
				</TR>\n";
		}
		$r .=	"</TABLE></div>\n";
		return $r;
	}

	function array2html($array, $color, $name)
	{
		$r = "<div id=div_server_".strtolower($name)." style='display:none;'>";
		$r .= "<p style='margin:0 0 0 10px;'><B>MYSQL SERVER {$name}</B> (connected by {$this->via}) {$this->comments['poor']}</p>
			<table border=2 cellspacing=0 cellpadding=5 align=center bordercolor='#95A3AC' width=99% class=state_table>
			<tr>
			<TD ALIGN=right NOWRAP class=yalign_head>항목</TD>
			<TD ALIGN=center NOWRAP class=yalign_head>{$name}</TD>
			<TD ALIGN=right NOWRAP class=yalign_head>commnets, [***] 표시는 성능 향상과 관련됨</TD>
			</TR>";

		$tmp = array_keys($array);
		foreach($tmp AS $key)
		{
			$r .= "<tr onMouseOver=\"this.style.backgroundColor='#B1BCC2';\" onMouseOut=\"this.style.backgroundColor='';\">
				<TD ALIGN=right>{$key}</TD>
				<TD ALIGN=right NOWRAP>{$array[$key][0]}</TD>
				<TD>{$array[$key][1]}</TD>
				</TR>\n";
		}
		unset($tmp);
		$r .= "</TABLE></div>\n";
		return $r;
	}

	function hsize($bfsize, $sub=0)
	{
		$BYTES = number_format($bfsize).' Bytes';

		if($bfsize < 1024) return $BYTES;
		else if($bfsize < 1048576) $bfsize = number_format(round($bfsize/1024)).' KB';
		else if($bfsize < 1073741827) $bfsize = number_format(round($bfsize/1048576)).' MB';
		else $bfsize = number_format($bfsize/1073741827,1).' GB';

		if($sub) $bfsize .= '('.$BYTES.')';

		return $bfsize;
	}

	function runtime($term, $lang='kr')
	{
		$l['kr'] = $l['ko'] = array('초','분','시간','일','달');
		$l['en'] = array('seconds','minutes','hours','days','months');

		$months	= (int)($term / 2592000);
		$term	= (int)($term % 2592000);
		$days	= (int)($term / 86400);
		$term	= (int)($term % 86400);
		$hours	= (int)($term / 3600);
		$term	= (int)($term % 3600);
		$mins	= (int)($term / 60);
		$secs	= (int)($term % 60);

		$months = $months ? $months.$l[$lang][4].' ' : '';
		$days = $days ? $days.$l[$lang][3].' ' : '';
		$hours = $hours ? $hours.$l[$lang][2].' ' : '';
		$mins = $mins ? $mins.$l[$lang][1].' ' : '';
		$secs .= $l[$lang][0];

		return $months.$days.$hours.$mins.$secs;
	}
} // end of class
$status = new mysql_status;
$status->tohtml();
?>

<script type="text/javascript" language="JavaScript">
<!--
function change_display(n, id, hide_id1, hide_id2)
{
	if(document.getElementById(id).style.display == 'none')
	{
		document.getElementById(id).style.display = 'block';
		document.getElementById(hide_id1).style.display = 'none';
		document.getElementById(hide_id2).style.display = 'none';
	}
	for(var i=1; i<4; i++)
	{
		if(n == i)
			document.getElementById('tab'+i).style.cssText = "font-weight:bold; color:black; cursor:default;";
		else
			document.getElementById('tab'+i).style.cssText = "font-weight:normal; cursor:pointer;";
	}
}
change_display(1, 'div_db_status', 'div_server_status', 'div_server_variables');
//-->
</script>

<? include_once ("$g4[admin_path]/admin.tail.php"); ?>
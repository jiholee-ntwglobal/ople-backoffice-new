<?php

/* ===================================================================
* DESC		: 포인트 지급 및 회수 스크립트
* PATH		: [66.209.90.21] /ssd/html/mall5/adm/point_update_test_201811.php
* URL		: http://66.209.90.21/mall5/adm/point_update_test_201811.php
* CODE		: @point
* --------------------------------------------------------------------
* COMMENTS
* --------------------------------------------------------------------
* 2020-03-27
* ================================================================= */
$sub_menu = "200200";
include_once("./_common.php");

/*=====================================================================
* Type 1 : 쿠폰 포인트 미사용자 회수시
=====================================================================*/
	if ( false )	// TRUE | FALSE
	{
		if ( !in_array($_SERVER['REMOTE_ADDR'], array('211.214.213.101')) )
		{
			echo 'access deny!';
			exit;
		}

		// 포인트 처리 대상 회원 아이디
//		$arr = array('js8540', 'ummummm', 'ejij1209', 'eeqq2002', 'sy990209', 'water4360', 'soultree', 'buss', 'ikea', 'koji', 'cdstm', 'chr3144', 'coolkan', 'dangse', 'youmej', 'sharday', 'zoepsj', 'wool0509', 'zoeywh', 'zoepsy', 'bbong0415', 'zoepsh', 'keum78', 'dy0220', 'archer45', 'sooyoun1914', '99jake', 'luna303', 'upony', 'allis76', 'lusiaa2u', 'ezhschoi', 'jin0932', 'rachel5838', '0kite0', 'cookeg', 'arias1920', 'cuteprincess', 'youma', 'yeewook', 'violetzeb', 'hyeeun1027', 'cotton', 'ggrsky', 'pajayu', 'bless0719', 'ek1651', 'noctoc', 'pinkiha', 'cnsk07', 'compress9', 'littlestep', '5plus', 'hee1707', 'ebbune1st', 'jhm0317', 'huseo00', 'tlsalsdud83', 'skhkyh', 'ymkowin', 'luvyeoni', 'jian0127', 'geniuswack', 'jhyear', 'fanbeltyo', 'ninah', 'mkbssbsmbc', 'onlmalgm', 'eve515', 'ekjin63', 'gamjuice', 'hskabz', 'occu7ter1004', 'envymore', 'wosjw2016', 'crow9909', 'ka333', 'chs2506', 'nancobi', 'nicolej', 'yhee8460', 'kha310', 'dhulee', 'pf34feel', 'sj8543', 'jiny7945', 'smanwife', 'mi02jjong', 'na9057', 'baram7210', 'harin8733', 'hunmam80', 'kutme96', 'tvtst', 'wedes', 'hotrookie', 'jis7282', 'hapywoman', 'firstlove4ev', 'yunjhyun', 'unja7732', 'sa2dacool', 'jiha0702', 'kjy7084', 'misscubic', 'jiwon2dang', 'funnykid', 'jmss700', 'jin2046', 'syunkim1', 'idokim', 'yumihj', 'parkkh77', 'sgkindlove', 'yasoon0204', 'ckffl00', 'yjh0121', 'apostles', 'lima85', 'kblackt', 'lamby1974', 'mollya', 'sss2340', 'oxptmfk12', 'wlstnr99', 'myjin78', 'narim772', 'saqua', 'corntwo', 'ha90180', 'zoomya', 'lillahot', 'fromhae', 'yujaehyu', 'soheee69', 'pes805', 'forte012', 'cchokok', 'gkswlgml09', 'alxn1', 'lotte92', 'mcm0618', 'jhssamzang', 'mongtlang', 'hs7703', 'lina0000', 'wjh3645', 'bee4114', 'tjdgml73', 'bbogle1012', 'pipiru', 'bass1028', 'glow45', 'walking', 'kiba82', 'yj2706', 'lemy', 'ki2886', 'parang7', 'aladinlamp1', 'honey1114', 'jjmhkd29', 'greegodo', 's000730', 'ridinghood', 'nalyny', 'cjljna', 'mzuk77', 'smn0119', 'glow_i', 'mrplace', 'hero622', 'hyeann', 'littleko', 'han5969', 'tsmean0104', 'gido28', 'hotbichnali', 'leesolip311', 'eom4544', 'sunshine8', 'msl00', 'dck21', 'vita1000m', 'mint1071', 'mail1004', '2desperado', 'kjm920', 'jamestywon', 'samsu79', 'solve777', 'hsy0512', 'young0612', 'dalkoo77', 'caprisun74', 'mn379140', 'malgum21', 'padak', 'sizpazz', 'yb7819', 'aqua0404', 'dasa444', 'hyehwa1224', 'pckim', 'jomkl', 'ekfzha80', 'h2451010', 'arylang', 'sue128', 'jaehyeong2207', 'rv1626', 'mir2000y', 'senny7', 'hank217', 'jskim425', 'hanihan80', 'nsuoos', 'sky0856', 'trustnou', 'zealer', 'voiceholic', 'jan0913', 'sunny9691', 'hungu0225', 'freewom', 'kyh90100', 'deresa0378', 'june63', 'eeppunee', 'lees5111', 'millyforever', 'tov0829', 'kklim91', 'kiy1029', 'jss0320', 'elldau', 'cbh153', 'lovebau', 'redsea226', 'csp153', 'd365', 'bb9820', 'bcnelo', 'aqua6angdu', 'ellie27', 'ykj3825', 'yujang', 'flame11', 'garden', 'cosmopark71', 'dudalsss', 'asteroidy');
		$arr = array('adamieo', 'ahnkim8317', 'akeja', 'annet1027', 'anyone57', 'azime', 'babpul80', 'bewoo05', 'budhachoi', 'chanbi0906', 'chke2503', 'choi0057', 'cjh910', 'cloud', 'clwinner1', 'cnchan', 'coojam', 'coralbb', 'core12', 'daduhoon', 'Dalkomm', 'dhkttmsfofl', 'dkrlensxlddl', 'dydtns0621', 'ecofuco69', 'elenabin', 'elfpink7', 'enomeyo', 'eternita', 'eugene3932', 'eun0027', 'eun5434', 'eun8372', 'eva70114', 'eve77', 'florajh', 'fukunishi', 'G0D', 'gfc119', 'gfjh72', 'ghwntld', 'gkt0716', 'goldarm', 'goneng', 'gray7', 'h2ocleaner', 'hellok13', 'hhyj2226', 'hj5321', 'hmj0415', 'hunkguy', 'hwa02100', 'hwarang3721', 'hwryu2000', 'hydroym', 'hyoonb', 'hyshys0987', 'hyunjin6740', 'iamha0', 'iblueya', 'idgyjc', 'ilove7go', 'imsubal', 'inkro', 'iriskh', 'island0902', 'jangmi4747', 'jar6758', 'jasmine8125', 'jazzjung', 'jeong192', 'jjring', 'johihi', 'jsdal60', 'jung9519', 'jus3355', 'jys2028', 'ka8527', 'kdk721215', 'kidds013', 'kimmin', 'kjhss6', 'km0116', 'kms3288', 'kng6091', 'kora3163', 'korosch', 'kshyun75', 'ktotoro7', 'ktthree', 'larsson', 'lion2020', 'mano96', 'metoo817', 'michelle226', 'mik6232', 'minka7', 'miyassi', 'moon1817', 'moyhp', 'nakamichi49', 'nami10', 'narak0920', 'netincome', 'nice0727', 'nmmnmm', 'nnnn1999', 'nnulbuk', 'ock526', 'okm66', 'omjungaa', 'oun0707', 'p612', 'paintsky', 'peter612', 'phjfca', 'piercegirl', 'pillsam', 'pink33', 'pjh0466', 'PJY0708', 'popminx', 'prettytjf', 'psu0828', 'purejae', 'QHA2010', 'rabbitmiea', 'redtea', 'rigg80', 'rose2917', 's1828', 'sajahoo', 'salut919', 'seasunup', 'shshin', 'shy9970', 'sinbaro', 'sky0115', 'smile1209', 'snowbollz', 'sojeong524', 'sonic04', 'sonkeesook1016', 'sonsk0302', 'sopp99', 'sos1004apa', 'spinoja1', 'sportage', 'stella812', 'sun5771', 'tepstep', 'thend1', 'thessun', 'tjswn27', 'totorolo', 'violet0307', 'vivayou', 'viveza', 'whitesy22', 'wldks719', 'wntjdwls73', 'woo5200', 'wool0509', 'wtufr2351', 'xueqi822', 'y7734', 'yabets', 'yegam21', 'yelbest', 'yeom0286', 'you83kyj1', 'youngae0827', 'ysg0584', 'ysm671201', 'ysyn7880', 'yun0hy', 'yuney1', 'zaki', 'zanny', 'zzangkorea');

		$cnt = 0;
		foreach ( $arr as $mb_id )
		{
			//insert_point($mb_id, '-3000', '쿠폰 포인트 ( : OPLECHU19) 적립금 취소', '@passive', $mb_id, $mb_id . '-' . uniqid(''));
			//insert_point($mb_id, '-2000', '쿠폰 포인트 ( : JUNE29OPLE) 적립금 취소', '@passive', $mb_id, $mb_id . '-' . uniqid(''));
			//insert_point($mb_id, '-3000', '쿠폰 포인트 ( : OPLESN2020) 적립금 취소', '@passive', $mb_id, $mb_id . '-' . uniqid(''));
			//insert_point($mb_id, '-2000', '쿠폰 포인트 ( : OPLEWD20) 적립금 취소', '@passive', $mb_id, $mb_id . '-' . uniqid(''));	// 2020-03-16 화이트데이 쿠폰포인트 회수
			//insert_point($mb_id, '-1000', '쿠폰 포인트 ( : CHEERUP20) 적립금 취소', '@passive', $mb_id, $mb_id . '-' . uniqid(''));		// 2020-03-27 오플굿데이 쿠폰포인트 회수
			//insert_point($mb_id, '-2000', '쿠폰 포인트 ( : OPWEEKEND) 적립금 취소', '@passive', $mb_id, $mb_id . '-' . uniqid(''));		// 2020-04-27 쿠폰포인트 회수
			insert_point($mb_id, '-2000', '쿠폰 포인트 ( : OPLEDC2020) 적립금 취소', '@passive', $mb_id, $mb_id . '-' . uniqid(''));		// 2020-05-06 쿠폰포인트 회수

//			insert_point($mb_id, '3000', '미리미리 감사해孝 이벤트 추가 포인트', '@passive', $mb_id, $mb_id . '-' . uniqid(''));			// 2020-04-27 얼리버드 이벤트
//			insert_point($mb_id, '2000', '미리미리 감사해孝 이벤트 추가 포인트', '@passive', $mb_id, $mb_id . '-' . uniqid(''));			// 2020-04-27 얼리버드 이벤트
			//insert_point($mb_id, '3000', '출석체크 이벤트 20회이상 $100이상 주문 3000 포인트 적립', '@passive', $mb_id, $mb_id . '-' . uniqid(''));
			//insert_point($mb_id, '5000', '출석체크 이벤트 20회이상 $100이상 주문 5000 포인트 적립', '@passive', $mb_id, $mb_id . '-' . uniqid(''));

			$cnt++;
		}

		echo $cnt.'건 처리 완료';
	}

/*=====================================================================
* Type 2 : 아이디별 포인트가 다를 경우
=====================================================================*/
	if ( FALSE )	// TRUE | FALSE
	{
		if ( !in_array($_SERVER['REMOTE_ADDR'], array('211.214.213.101')) )
		{
			echo 'access deny!';
			exit;
		}

		$cnt = 0;
		$arr = array('dbitmeca'=>'10');

		foreach ($arr as $mb_id => $point)
		{
			if ( $cnt > 0 && ($cnt % 500) == 0 )
			{
				sleep(1);
			}

			insert_point($mb_id, $point, '럭셔리 홀리데이 경품이벤트 당첨', '@passive', $mb_id, $mb_id . '-' . uniqid(''));
			$cnt++;
		}

		echo $cnt.'건 처리 완료';
	}

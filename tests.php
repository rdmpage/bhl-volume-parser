<?php

//----------------------------------------------------------------------------------------
// Regression tests for parse-volume.php
//
//   php tests.php
//
// Each test is: input, then the expected volume, issue, series and date-parts. Use '-' for
// "expect nothing". Date-parts are written as JSON.
//----------------------------------------------------------------------------------------

require_once (dirname(__FILE__) . '/parse-volume.php');

$tests = array(

	// --- the basics -------------------------------------------------------------------
	array('v.101 (2004)',                 '101',      '-',          '-',  '[[2004]]'),
	array('v.106:no.3 (2009)',            '106',      '3',          '-',  '[[2009]]'),
	array('v.28:pt.3-4 (1922)',           '28',       '3,4',        '-',  '[[1922]]'),
	array('v.104:no.3 (2007:Dec.)',       '104',      '3',          '-',  '[[2007,12]]'),
	array('v.31:pt.3-4 (1926-1927)',      '31',       '3,4',        '-',  '[[1926],[1927]]'),
	array('v.108-109 (2011-2012)',        '108,109',  '-',          '-',  '[[2011],[2012]]'),
	array('v.39 (1933-36)',               '39',       '-',          '-',  '[[1933],[1936]]'),
	array('v.13(1861)',                   '13',       '-',          '-',  '[[1861]]'),

	// --- spacing and punctuation variants ---------------------------------------------
	array('v. 58 (1919)',                 '58',       '-',          '-',  '[[1919]]'),
	array('Vol 26 (1898)',                '26',       '-',          '-',  '[[1898]]'),
	array('Vol. 5 : no. 1-no. 2 (1986)',  '5',        '1,2',        '-',  '[[1986]]'),
	array('"v. 22, no. 2 (1998)"',        '22',       '2',          '-',  '[[1998]]'),
	array('"v.22,pt.3(1998)"',            '22',       '3',          '-',  '[[1998]]'),
	array('v. 22 no. 2 2002',             '22',       '2',          '-',  '[[2002]]'),
	array('"ser. 5, t. 5 (1913)"',        '5',        '-',          '5',  '[[1913]]'),
	array('v.9 PT.1(1884-1885)',          '9',        '1',          '-',  '[[1884],[1885]]'),

	// --- languages --------------------------------------------------------------------
	array('Bd.3 (1882)',                  '3',        '-',          '-',  '[[1882]]'),
	array('bd. 41-42 (1910-1911)',        '41,42',    '-',          '-',  '[[1910],[1911]]'),
	array('Band 3 (1854)',                '3',        '-',          '-',  '[[1854]]'),
	array('Jahrg. 13 (1907)',             '13',       '-',          '-',  '[[1907]]'),
	array('Jahrg.19=Bd.73 (1898)',        '19,73',    '-',          '-',  '[[1898]]'),
	array('31.Jahr. (1877)',              '31',       '-',          '-',  '[[1877]]'),
	array('n.F.:26.Jahrg. (1881-1882)',   '26',       '-',          '-',  '[[1881],[1882]]'),
	array('Bd.28:Heft.1-2,4 (1914)',      '28',       '1,2,4',      '-',  '[[1914]]'),
	array('Heft 5 (1900)',                '5',        '-',          '-',  '[[1900]]'),
	array('Lfg.3 (1927)',                 '3',        '-',          '-',  '[[1927]]'),
	array('Bd.5:Abt.2 (1907)',            '5',        '2',          '-',  '[[1907]]'),
	array('Abt.1:Bd.3 (1907)',            '3',        '1',          '-',  '[[1907]]'),
	array('t.48 (1914-1920)',             '48',       '-',          '-',  '[[1914],[1920]]'),
	array('Tome 4 (1888)',                '4',        '-',          '-',  '[[1888]]'),
	array('Tomo 2 (1901)',                '2',        '-',          '-',  '[[1901]]'),
	array('nouv. sér.:t.3:fasc.2 (1901)', '3',        '2',          '-',  '[[1901]]'),
	array('anno 1 (1896)',                '1',        '-',          '-',  '[[1896]]'),
	array('année 12 (1907)',              '12',       '-',          '-',  '[[1907]]'),
	array('arg.5 (1910)',                 '5',        '-',          '-',  '[[1910]]'),
	array('nr. 179 (1967)',               '179',      '-',          '-',  '[[1967]]'),
	array('pars 3 (1900)',                '3',        '-',          '-',  '[[1900]]'),
	array('d.15 (1826)',                  '15',       '-',          '-',  '[[1826]]'),

	// --- series -----------------------------------------------------------------------
	array('ser.3:v.1 (1911)',             '1',        '-',          '3',  '[[1911]]'),
	array('ser.5:v.11=no.61-66 (1883)',   '11',       '61,62,63,64,65,66', '5', '[[1883]]'),
	array('new ser:v.19 (1924)',          '19',       '-',          '-',  '[[1924]]'),
	array('new ser.:v.16 (1920)',         '16',       '-',          '-',  '[[1920]]'),
	array('n.s. no.40 (2007)',            '40',       '-',          '-',  '[[2007]]'),
	array('n.s. t.3 (1911)',              '3',        '-',          '-',  '[[1911]]'),
	array('ser.4:t.5 (1888)',             '5',        '-',          '4',  '[[1888]]'),
	array('ser.2:v.11=no.31-33 (1851)',   '11',       '31,32,33',   '2',  '[[1851]]'),

	// --- "=" means the same item under another name -----------------------------------
	array('v.102=ser.3:v.32 (1876)',      '102,32',   '-',          '3',  '[[1876]]'),
	array('v.21=no.61-62 (1908-1909)',    '21',       '61,62',      '-',  '[[1908],[1909]]'),
	array('v.16-17=no.50-52 (1899-1901)', '16,17',    '50,51,52',   '-',  '[[1899],[1901]]'),
	array('v.61=Bd.9 (1902)',             '61,9',     '-',          '-',  '[[1902]]'),
	array('[v.12]=[no.45-48] (1887-1888)','12',       '45,46,47,48','-',  '[[1887],[1888]]'),

	// --- several items in one record --------------------------------------------------
	array('v.25:no.1-4;v.26:no.1-4 (2002-2003)', '25,26', '1,2,3,4', '-', '[[2002],[2003]]'),

	// --- dates ------------------------------------------------------------------------
	array('v.83 (1986:Apr-Aug)',          '83',       '-',          '-',  '[[1986,4],[1986,8]]'),
	array('Bd.35 (1921:Apr.-Dec.)',       '35',       '-',          '-',  '[[1921,4],[1921,12]]'),
	array('Bd.24 (1910:Jan.-1911:Jan.)',  '24',       '-',          '-',  '[[1910,1],[1911,1]]'),
	array('1943:Jan.-1946:Dec.',          '1943,1944,1945,1946', '-', '-', '[[1943,1],[1946,12]]'),
	array('1901:v.2 (May-Dec.)',          '2',        '-',          '-',  '[[1901,5],[1901,12]]'),
	array('no.167 (2016:Jun)',            '167',      '-',          '-',  '[[2016,6]]'),
	array('v.21:no.1 (1992:Jan.-Feb.)',   '21',       '1',          '-',  '[[1992,1],[1992,2]]'),
	array('Bd.12 (1899:Mai)',             '12',       '-',          '-',  '[[1899,5]]'),
	array('t.4 (1902:juillet)',           '4',        '-',          '-',  '[[1902,7]]'),
	array('(1983:Jun)',                   '1983',     '-',          '-',  '[[1983,6]]'),
	array('1949:May',                     '1949',     '-',          '-',  '[[1949,5]]'),
	array('v.3 (1877 [i.e. 1878])',       '3',        '-',          '-',  '[[1878]]'),

	// --- year as the enumeration ------------------------------------------------------
	array('1995',                         '1995',     '-',          '-',  '[[1995]]'),
	array('1886:pt.1',                    '1886',     '1',          '-',  '[[1886]]'),
	array('1994-95',                      '1994,1995','-',          '-',  '[[1994],[1995]]'),
	array('1851-1852',                    '1851,1852','-',          '-',  '[[1851],[1852]]'),
	array('1 1921',                       '1',        '-',          '-',  '[[1921]]'),
	array('1883 (vol 10)',                '10',       '-',          '-',  '[[1883]]'),

	// --- numbers, parts and ordinals --------------------------------------------------
	array('no.71 (2019)',                 '71',       '-',          '-',  '[[2019]]'),
	array('no.88 (1915)',                 '88',       '-',          '-',  '[[1915]]'),
	array('pt.3 (1879)',                  '3',        '-',          '-',  '[[1879]]'),
	array('fasc.1-8',                     '1,2,3,4,5,6,7,8', '-',   '-',  '-'),
	array('no.99:pt.3 (1924)',            '99',       '3',          '-',  '[[1924]]'),
	array('12th (1901)',                  '12',       '-',          '-',  '[[1901]]'),
	array('7th (1885)',                   '7',        '-',          '-',  '[[1885]]'),
	array('4, no 2, 1988',                '4',        '2',          '-',  '[[1988]]'),

	// --- roman numerals and letter designations ---------------------------------------
	array('v.XII (1900)',                 '12',       '-',          '-',  '[[1900]]'),
	array('pt.III (1943)',                '3',        '-',          '-',  '[[1943]]'),
	array('v.8:sect.C (1913)',            '8',        'C',          '-',  '[[1913]]'),

	// --- brackets ---------------------------------------------------------------------
	array('[v.8] (1830)',                 '8',        '-',          '-',  '[[1830]]'),
	array('v.8:sect.C (1913) [Botany]',   '8',        'C',          '-',  '[[1913]]'),
	array('Jahrg.4 (1899) [Incomplete]',  '4',        '-',          '-',  '[[1899]]'),

	// --- ranges are expanded ----------------------------------------------------------
	array('no.1-3 (1900)',                '1,2,3',    '-',          '-',  '[[1900]]'),
	array('v.4:no.10-21 (1900)',          '4',        '10,11,12,13,14,15,16,17,18,19,20,21', '-', '[[1900]]'),
	array('v.1-4 (1900-1903)',            '1,2,3,4',  '-',          '-',  '[[1900],[1903]]'),

	// --- year ranges vs report numbers ------------------------------------------------
	array('2018-67',                      '2018',     '67',         '-',  '[[2018]]'),
	array('2011-8',                       '2011',     '8',          '-',  '[[2011]]'),
	array('1877-79',                      '1877,1878,1879', '-',    '-',  '[[1877],[1879]]'),
	array('1992 PART 2',                  '1992',     '2',          '-',  '[[1992]]'),

	// --- the year is not always where you expect it ------------------------------------
	array('no.585 (Jul-Aug:2016)',        '585',      '-',          '-',  '[[2016,7],[2016,8]]'),
	array('VOL FEB 1931',                 '1931',     '-',          '-',  '[[1931]]'),

	// --- ":" subdivides a volume, ";" lists another one --------------------------------
	array('Jahrg.51:Bd.2 (1913)',         '51',       '2',          '-',  '[[1913]]'),
	array('v.31:no.7-12;Index v.31 (1969)', '31',     '7,8,9,10,11,12', '-', '[[1969]]'),

	// --- report series have a number, not a volume -------------------------------------
	array('Technical note-20',            '-',        '-',          '-',  '-'),
	array('PA-146',                       '-',        '-',          '-',  '-'),

	// --- things that are not enumeration ----------------------------------------------
	array('Box 3: Folder 2: Pinus: undated', '-',     '-',          '-',  '-'),
	array('"Sender Barnes, Charles R., 1856-1910"', '-', '-',       '-',  '[[1856],[1910]]'),
);

//----------------------------------------------------------------------------------------
// Parallel numbering: input, then the expected designations as JSON.
$parallel_tests = array(

	array('v.102=ser.3:v.32 (1876)',
		'[{"volume":["102"]},{"volume":["32"],"collection-title":["3"]}]'),

	array('no.250 (1976) = ser.3:no.357 (1976)',
		'[{"volume":["250"]},{"volume":["357"],"collection-title":["3"]}]'),

	array('Jahrg.19=Bd.73 (1898)',
		'[{"volume":["19"]},{"volume":["73"]}]'),

	array('v.21=no.61-62 (1908-1909)',
		'[{"volume":["21"]},{"issue":["61","62"]}]'),

	array('ser.5:v.11=no.61-66 (1883)',
		'[{"volume":["11"],"collection-title":["5"]},{"issue":["61","62","63","64","65","66"]}]'),

	array('v.30=new ser.:v.12=no.61-64 (1901)',
		'[{"volume":["30"]},{"volume":["12"],"collection-title":["new ser."]},{"issue":["61","62","63","64"]}]'),

	// a series marker that is named rather than numbered
	array('Jahrg.27=n.F. Bd.17 (1902)',
		'[{"volume":["27"]},{"volume":["17"],"collection-title":["n.F."]}]'),

	// a sub-series is named rather than numbered
	array('t.7=Oiseaux t.4 (1802)',
		'[{"volume":["7"]},{"volume":["4"],"collection-title":["Oiseaux"]}]'),

	// a volume that is also a part and a range of whole numbers
	array('v.5:pt.2=no.61-62 (1890)',
		'[{"volume":["5"],"issue":["2"]},{"issue":["61","62"]}]'),

	// bracketed enumeration after a complete designation is another designation
	array('v.102=ser.3:v.32 (1876) [no.6206-6207]',
		'[{"volume":["102"]},{"volume":["32"],"collection-title":["3"]},{"issue":["6206","6207"]}]'),

	// a "no." on its own is still the volume, because nothing outranks it
	array('no.53=v.7 (1911)',
		'[{"issue":["53"]},{"volume":["7"]}]'),

	// a single designation has nothing to pair up
	array('v.101 (2004)', '-'),
	array('ser.3:v.1 (1911)', '-'),
);

//----------------------------------------------------------------------------------------
function actual($obj, $field)
{
	switch ($field)
	{
		case 'volume':
			return isset($obj->volume) ? implode(',', $obj->volume) : '-';

		case 'issue':
			return isset($obj->issue) ? implode(',', $obj->issue) : '-';

		case 'series':
			return isset($obj->{'collection-title'}) ? implode(',', $obj->{'collection-title'}) : '-';

		case 'date':
			return isset($obj->issued) ? json_encode($obj->issued->{'date-parts'}) : '-';
	}

	return '-';
}

$failed = 0;

foreach ($tests as $test)
{
	list($text, $volume, $issue, $series, $date) = $test;

	$obj = parse_volume($text);

	$expected = array('volume' => $volume, 'issue' => $issue, 'series' => $series, 'date' => $date);
	$problems = array();

	foreach ($expected as $field => $want)
	{
		$got = actual($obj, $field);
		if ($got !== $want)
		{
			$problems[] = "$field: expected $want, got $got";
		}
	}

	if (count($problems) > 0)
	{
		$failed++;
		echo "FAIL  $text\n";
		foreach ($problems as $problem)
		{
			echo "      $problem\n";
		}
	}
}

foreach ($parallel_tests as $test)
{
	list($text, $want) = $test;

	$obj = parse_volume($text);

	$got = isset($obj->parallel)
		? json_encode($obj->parallel, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
		: '-';

	if ($got !== $want)
	{
		$failed++;
		echo "FAIL  $text\n";
		echo "      parallel: expected $want\n";
		echo "                     got $got\n";
	}
}

echo "\n" . (count($tests) + count($parallel_tests)) . " tests, " . $failed . " failed\n";

exit($failed == 0 ? 0 : 1);

?>

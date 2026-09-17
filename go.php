<?php

//----------------------------------------------------------------------------------------
// Run parse-volume.php over a file of BHL volume strings, one per line.
//
//   php go.php                      summary statistics for volumeinfo.txt
//   php go.php --json               one JSON object per line
//   php go.php --failed             just the lines that did not parse
//   php go.php --tsv                text, volume, issue, series, date as TSV
//   php go.php --file other.txt     use a different input file
//----------------------------------------------------------------------------------------

require_once (dirname(__FILE__) . '/parse-volume.php');

$filename = dirname(__FILE__) . '/volumeinfo.txt';
$mode = 'summary';

for ($i = 1; $i < $argc; $i++)
{
	switch ($argv[$i])
	{
		case '--json':   $mode = 'json';    break;
		case '--failed': $mode = 'failed';  break;
		case '--tsv':    $mode = 'tsv';     break;
		case '--file':   $filename = $argv[++$i]; break;

		default:
			echo "Unknown option: " . $argv[$i] . "\n";
			exit(1);
	}
}

$file_handle = fopen($filename, "r");

if (!$file_handle)
{
	echo "Could not open $filename\n";
	exit(1);
}

$stats = array('rows' => 0, 'parsed' => 0, 'volume' => 0, 'issue' => 0, 'date' => 0, 'month' => 0);

while (($line = fgets($file_handle)) !== false)
{
	$line = trim($line);

	if ($line == '')
	{
		continue;
	}

	$result = parse_volume($line);

	$stats['rows']++;

	if ($result->parsed)
	{
		$stats['parsed']++;
	}

	if (isset($result->volume))
	{
		$stats['volume']++;
	}

	if (isset($result->issue))
	{
		$stats['issue']++;
	}

	if (isset($result->issued->{'date-parts'}[0][0]))
	{
		$stats['date']++;
	}

	if (isset($result->issued->{'date-parts'}[0][1]))
	{
		$stats['month']++;
	}

	switch ($mode)
	{
		case 'json':
			echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
			break;

		case 'failed':
			if (!$result->parsed)
			{
				echo $line . "\n";
			}
			break;

		case 'tsv':
			echo implode("\t", array(
				$line,
				isset($result->volume) ? implode(',', $result->volume) : '',
				isset($result->issue) ? implode(',', $result->issue) : '',
				isset($result->{'collection-title'}) ? implode(',', $result->{'collection-title'}) : '',
				isset($result->issued) ? json_encode($result->issued->{'date-parts'}) : '',
			)) . "\n";
			break;

		default:
			break;
	}
}

fclose($file_handle);

if ($mode == 'summary')
{
	$rows = max(1, $stats['rows']);

	printf("%-10s %8d\n", 'rows',   $stats['rows']);
	printf("%-10s %8d  %5.2f%%\n", 'parsed', $stats['parsed'], 100 * $stats['parsed'] / $rows);
	printf("%-10s %8d  %5.2f%%\n", 'volume', $stats['volume'], 100 * $stats['volume'] / $rows);
	printf("%-10s %8d  %5.2f%%\n", 'issue',  $stats['issue'],  100 * $stats['issue']  / $rows);
	printf("%-10s %8d  %5.2f%%\n", 'year',   $stats['date'],   100 * $stats['date']   / $rows);
	printf("%-10s %8d  %5.2f%%\n", 'month',  $stats['month'],  100 * $stats['month']  / $rows);
}

?>

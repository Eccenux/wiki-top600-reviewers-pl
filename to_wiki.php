<?php
/**
	JSON to wiki-code.
	
	array (
	  'actor_name' => 0,
	  'review_count' => 1,
	  'review_count_intial' => 2,
	  'review_count_total' => 3,
	)	
*/

date_default_timezone_set('Europe/Warsaw');

$inputPaths = [];
$outputPaths = [];
/**
$inputPaths[] = 'quarry-reviewers-pl-2019.json';
$outputPaths[] = "reviewers-pl.wiki";
/**/
$year = "2025";
$inputPaths[] = "quarry-reviewers-pl-$year.json";
$outputPaths[] = "reviewers-pl-$year.wiki";
/**/
$inputPaths[] = 'quarry-reviewers-pl-all.json';
$outputPaths[] = "reviewers-pl-all.wiki";
/**/

//trigger_error("debugger", E_USER_NOTICE);

// mapping helper
class TopReviewer {
	public $actor_name;
	public $review_count;
	public $review_count_intial;
	public $review_count_total;
	
	function __construct($row, $columns) {
		$this->actor_name = $row[$columns['actor_name']];
		$this->review_count = number_format((int)$row[$columns['review_count']], 0, '', ' ');
		$this->review_count_intial = number_format((int)$row[$columns['review_count_intial']], 0, '', ' ');
		$this->review_count_total = number_format((int)$row[$columns['review_count_total']], 0, '', ' ');
	}
}

// Convert all PHP errors (incl. warnings) to ErrorException
set_error_handler(function (
	int $severity,
	string $message,
	string $file,
	int $line
) {
	// Respect @-operator
	if (!(error_reporting() & $severity)) {
		return false;
	}

	throw new ErrorException($message, 0, $severity, $file, $line);
});

// Ensure warnings are reported
error_reporting(E_ALL);

// Optional: don’t hide them
ini_set('display_errors', '1');

/**
$r = new TopReviewer($data->rows[0]);
var_export($r);
var_export($columns);
die();
/**/

function parseJsonToWiki ($inputPath, $outputPath) {
	echo "\n[" . date('c') . "] [INFO] Loading $inputPath...";
	$data = json_decode(file_get_contents($inputPath));
	$columns = array_flip($data->headers);

	$numRow = 0;
	$wiki = '';
	echo "\n[" . date('c') . "] [INFO] Strating loop for $inputPath...";
	foreach($data->rows as $row) {
		$numRow++;
		$r = new TopReviewer($row, $columns);
		
		// top600, but make sure we show all users tied on last place
		if ($numRow > 600 && $prev != $r->review_count) {
			break;
		}
		
		$wiki .= "\n|-\n| $numRow || [[User:{$r->actor_name}|{$r->actor_name}]] || {$r->review_count} || {$r->review_count_intial} || {$r->review_count_total}";

		$prev = $r->review_count;
	}
	$wiki .=  "\n|}\n";

	file_put_contents($outputPath, $wiki);

	echo "\n[" . date('c') . "] [INFO] Done";
}

//
// Main loop
foreach ($inputPaths as $i => $inputPath) {
	$outputPath = $outputPaths[$i] ?? null;

	parseJsonToWiki ($inputPath, $outputPath);
}
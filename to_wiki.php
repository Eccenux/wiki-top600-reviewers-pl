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

/**
$inputPath = 'quarry-reviewers-pl-2019.json';
$outputPath = "reviewers-pl.wiki";
/**/
$inputPath = 'quarry-reviewers-pl-2020.json';
$outputPath = "reviewers-pl-2020.wiki";
/**/
$inputPath = 'quarry-reviewers-pl-all.json';
$outputPath = "reviewers-pl-all.wiki";
/**/

//trigger_error("debugger", E_USER_NOTICE);

$data = json_decode(file_get_contents($inputPath));
$columns = array_flip($data->headers);

// mapping helper
class TopReviewer {
	public $actor_name;
	public $review_count;
	public $review_count_intial;
	public $review_count_total;
	
	function __construct($row) {
		global $columns;
		
		$this->actor_name = $row[$columns['actor_name']];
		$this->review_count = $row[$columns['review_count']];
		$this->review_count_intial = $row[$columns['review_count_intial']];
		$this->review_count_total = $row[$columns['review_count_total']];
	}
}

/**
$r = new TopReviewer($data->rows[0]);
var_export($r);
var_export($columns);
die();
/**/

$numRow = 0;
$wiki = '';
echo "\n[INFO] Strating loop...";
foreach($data->rows as $row) {
	$numRow++;
	$r = new TopReviewer($row);
	
	// top600, but make sure we show all users tied on last place
	if ($numRow > 600 && $prev != $r->review_count) {
		break;
	}
	
	$wiki .= "\n|-\n| $numRow || [[User:{$r->actor_name}|{$r->actor_name}]] || {$r->review_count} || {$r->review_count_intial} || {$r->review_count_total}";

	$prev = $r->review_count;
}
$wiki .=  "\n|}\n";

file_put_contents($outputPath, $wiki);

echo "\n[INFO] Done";
<?php
# encode.txt and decode.txt are made from
# https://groups.google.com/group/geohex/web/test-casev3

set_include_path('../');
require_once('Geo/Hex.php');

$succ_count = 0;
$fail_count = 0;

# encode test
$fp = fopen('encode.txt', 'r');
while (!feof($fp)) {
	$line = fgets($fp);
	if (strlen($line) == 0) {
		continue; // for the last line
	}
	list($lat, $lon, $level, $code) = explode(",", chop($line));
	$g = new \Geo\Hex(array(
		'latitude' => $lat,
		'longitude' => $lon,
		'level' => $level,
	));

	if ($g->code === $code) {
		$succ_count ++;
	} else {
		print "FAILED TESTCASE: " . chop($line) . "\n";
		print "  Expected:" . $code . " Actual:" . $zone["code"] . "\n";
		$fail_count ++;
	}
}

# decode test
$fp = fopen('decode.txt', 'r');
while (!feof($fp)) {
	$line = fgets($fp);
	if (strlen($line) == 0) {
		continue; // for the last line
	}
	list($lat, $lon, $level, $code) = explode(",", chop($line));
	$g = new \Geo\Hex();
	$g->setCode($code);
	$g2 = new \Geo\Hex(array(
		"latitude" => $g->latitude,
		"longitude" => $g->longitude,
		"level" => $g->level,
	));

	if ($g2->code === $code) {
		$succ_count ++;
	} else {
		print "FAILED TESTCASE: " . chop($line) . "\n";
		print "  Expected:" . $code . " Actual:" . $g2->code . "\n";
		$fail_count ++;
	}
}

print "Result: succeed:" . $succ_count . " fail:" . $fail_count . "\n";

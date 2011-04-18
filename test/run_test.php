<?php
# encode.txt and decode.txt are made from
# https://groups.google.com/group/geohex/web/test-casev3

set_include_path('../');
require_once('GeoHex.php');

# convert $f to string as same precision as $reference_f
function float_str($reference_f, $f) {
	if (strpos($reference_f, ".")) {
		$ary = explode(".", $reference_f);
		$precision = strlen($ary[1]);
	} else {
		$precision = 0;
	}
	return sprintf("%." . $precision . "f", $f);
		print $str_f.":".$ary[1].":%.".$precision."f:".$formatted_f . ":" . $f;
}

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
	$g = new Geohex(array(
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
	$g = new Geohex();
	$g->setCode($code);
	$lat_r = float_str($lat, $g->latitude);
	$lon_r = float_str($lon, $g->longitude);

	if ($lat === $lat_r and $lon === $lon_r and $g->level === (int)$level) {
		$succ_count ++;
	} else {
		print "FAILED TESTCASE: " . chop($line) . "\n";
		print "  Expected:" . $lat . "," . $lon . "," . $level . " Actual:" . $lat_r . "," . $lon_r . "," . $g->level . "\n";
		$fail_count ++;
	}
}

print "Result: succeed:" . $succ_count . " fail:" . $fail_count . "\n";

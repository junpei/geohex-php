<?php

set_include_path('../');
require_once('Geo/Hex.php');

$latitude  = 35.68092;
$longitude = 139.76740;
$level     = 7;
$code      = 'XM488548';

$g = new \Geo\Hex(array(
	'latitude' => $latitude,
	'longitude' => $longitude
));

print_r($g);

$ge = new \Geo\Hex(array(
	'code' => $code
));

print_r($ge);

$geo = new \Geo\Hex();

print_r($geo->setLocation($latitude, $longitude));
print_r($geo->setLevel($geo->level - 1));

$geoh = new \Geo\Hex();

print_r($geoh->setCode($code));

$geohe = new \Geo\Hex();

print_r($geohe->setLatitude($latitude)->setLongitude($longitude));

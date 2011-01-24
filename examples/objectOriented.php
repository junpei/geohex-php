<?php

set_include_path('../');
require_once('GeoHex.php');

$latitude  = 35.68092;
$longitude = 139.76740;
$level     = 7;
$code      = 'XM488548';

$g = new Geohex(array(
	'latitude' => $latitude,
	'longitude' => $longitude
));

print_r($g);

$ge = new Geohex(array(
	'code' => $code
));

print_r($ge);

$geo = new Geohex();

print_r($geo->setLocation($latitude, $longitude));
print_r($geo->setLevel($geo->level - 1));

$geoh = new Geohex();

print_r($geoh->setCode($code));

$geohe = new Geohex();

print_r($geohe->setLatitude($latitude)->setLongitude($longitude));

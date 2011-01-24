<?php

set_include_path('../');
require_once('GeoHex.php');

$latitude  = 35.68092;
$longitude = 139.76740;
$level     = 7;
$code      = 'XM488548';

$zone = GeoHex::getZoneByLocation($latitude, $longitude, $level);

print_r($zone);

$zone = GeoHex::getZoneByCode($code);
$coords = GeoHex::getHexCoordsByZone($zone);

print_r($zone);
print_r($coords);

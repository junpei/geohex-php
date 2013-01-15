<?php

set_include_path('../');
require_once('Geo/Hex.php');

$latitude  = 35.68092;
$longitude = 139.76740;
$level     = 7;
$code      = 'XM488548';

$zone = \Geo\Hex::getZoneByLocation($latitude, $longitude, $level);

print_r($zone);

$zone = \Geo\Hex::getZoneByCode($code);
$coords = \Geo\Hex::getHexCoordsByZone($zone);

print_r($zone);
print_r($coords);

<?php

namespace Geo;

/**
 * \Geo\Hex
 * GeoHex by @sa2da (http://geogames.net) is licensed under Creative Commons BY-SA 2.1 Japan License.
 *
 * @category  \Geo
 * @package   \Geo
 * @copyright Copyright (c) 2011 Tonthidot Corporation. (http://www.tonchidot.com)
 * @license   http://creativecommons.org/licenses/by-sa/2.1/jp/
 * @author    KITA, Junpei
 * @version   $Id
 */
class Hex
{
    const VERSION = '3.01';

    const H_KEY  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const H_BASE = 20037508.34;
    const H_DEG  = 0.5235987755983;  # pi() / 30 / 180
    const H_K    = 0.57735026918963; # tan(H_DEG)

    public $x = null;
    public $y = null;
    public $code = null;
    public $level = 7;
    public $latitude = null;
    public $longitude = null;
    public $coords = array();

    /**
     * construct
     *
     * @param array(
     *     'code'      => String,
     *     'level'     => Integer,
     *     'latitude'  => Float,
     *     'longitude' => Float
     * )
     * @return \Geo\Hex
     * @throws \Geo\Exception
     */
    public function __construct()
    {
        switch (func_num_args()) {
        case 3:
            $args = func_get_args();
            $this->latitude = $args[0];
            $this->longitude = $args[1];
            $this->level = $args[2];
            break;
        case 2:
            $args = func_get_args();
            $this->latitude = $args[0];
            $this->longitude = $args[1];
            break;
        case 1:
            $arg = func_get_arg(0);

            if (is_string($arg)) {
                $this->code = $arg;
            }

            else if (is_array($arg)) {
                $keys = array('code', 'level', 'latitude', 'longitude');

                foreach ($keys as $key) {
                    if (isset($arg[$key])) {
                        $this->$key = $arg[$key];
                    }
                }
            }
            break;
        }

        if (isset($this->latitude) && isset($this->longitude))
        {
            $this->setLocation(
                $this->latitude, $this->longitude, $this->level);
        }

        else if (isset($this->code)) {
            $this->setCode($this->code);
        }
    }

    /**
     * public
     */

    public function setLocation($latitude, $longitude, $level = null)
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;

        if (isset($level)) {
            $this->level = $level;
        }

        if (!isset($this->latitude) ||
            !isset($this->longitude) ||
            !isset($this->level)
        ) {
            return $this;
        }

        $zone = self::getZoneByLocation(
            $this->latitude, $this->longitude, $this->level);

        $this->x = $zone['x'];
        $this->y = $zone['y'];
        $this->code = $zone['code'];

        return $this->setCoords();
    }
    public function setCode($code)
    {
        $zone = self::getZoneByCode($code);

        $this->x = $zone['x'];
        $this->y = $zone['y'];
        $this->code = $zone['code'];
        $this->level = $zone['level'];
        $this->latitude = $zone['latitude'];
        $this->longitude = $zone['longitude'];

        return $this->setCoords();
    }
    public function setLevel($level)
    {
        return $this->setLocation(
            $this->latitude, $this->longitude, $level);
    }
    public function setLatitude($latitude)
    {
        return $this->setLocation($latitude, $this->longitude);
    }
    public function setLongitude($longitude)
    {
        return $this->setLocation($this->latitude, $longitude);
    }

    /**
     * public static
     */

    public static function getZoneByLocation($lat, $lon, $level) {
        $level_   = $level + 2;
        $h_size   = self::_calcHexSize($level_);
        $z_xy     = self::_loc2xy($lon, $lat);
        $lon_grid = $z_xy['x'];
        $lat_grid = $z_xy['y'];
        $unit_x   = 6 * $h_size;
        $unit_y   = 6 * $h_size * self::H_K;
        $h_pos_x  = ($lon_grid + $lat_grid / self::H_K) / $unit_x;
        $h_pos_y  = ($lat_grid - self::H_K * $lon_grid) / $unit_y;
        $h_x_0    = floor($h_pos_x);
        $h_y_0    = floor($h_pos_y);
        $h_x_q    = $h_pos_x - $h_x_0;
        $h_y_q    = $h_pos_y - $h_y_0;
        $h_x      = round($h_pos_x);
        $h_y      = round($h_pos_y);

        if ($h_y_q > -$h_x_q + 1) {
            if (($h_y_q < 2 * $h_x_q) && ($h_y_q > 0.5 * $h_x_q)) {
                $h_x = $h_x_0 + 1;
                $h_y = $h_y_0 + 1;
            }
        }

        else if ($h_y_q < -$h_x_q + 1) {
            if (($h_y_q > (2 * $h_x_q) - 1) && ($h_y_q < (0.5 * $h_x_q) + 0.5)) {
                $h_x = $h_x_0;
                $h_y = $h_y_0;
            }
        }

        $h_lat = (self::H_K * $h_x * $unit_x + $h_y * $unit_y) / 2;
        $h_lon = ($h_lat - $h_y * $unit_y) / self::H_K;

        $z_loc = self::_xy2loc($h_lon, $h_lat);
        $z_loc_x = $z_loc['lon'];
        $z_loc_y = $z_loc['lat'];

        if (self::H_BASE - $h_lon < $h_size) {
            $z_loc_x = 180;
            $h_xy    = $h_x;
            $h_x     = $h_y;
            $h_y     = $h_xy;
        }

        $h_code  = '';
        $code3_x = array();
        $code3_y = array();
        $code3   = '';
        $code9   = '';
        $mod_x   = $h_x;
        $mod_y   = $h_y;

        for ($i = 0; $i <= $level_; $i++) {
            $h_pow = pow(3, $level_ - $i);

            if ($mod_x >= ceil($h_pow / 2)) {
                $code3_x[$i] = 2;
                $mod_x -= $h_pow;
            }

            else if ($mod_x <= -ceil($h_pow / 2)) {
                $code3_x[$i] = 0;
                $mod_x += $h_pow;
            }
            
            else {
                $code3_x[$i] = 1;
            }

            if ($mod_y >= ceil($h_pow / 2)) {
                $code3_y[$i] =2;
                $mod_y -= $h_pow;
            }

            else if ($mod_y <= -ceil($h_pow / 2)) {
                $code3_y[$i] = 0;
                $mod_y += $h_pow;
            }
            
            else {
                $code3_y[$i] = 1;
            }
        }

        for ($i = 0; $i < count($code3_x); $i++) {
            $code3  += $code3_x[$i] . $code3_y[$i];
            $code9  += intval((string) $code3, 3);
            $h_code .= $code9;
            $code9   = '';
            $code3   = '';
        }

        $h_2    = substr($h_code, 3);
        $h_1    = substr($h_code, 0, 3);
        $h_a1   = floor($h_1 / 30);
        $h_a2   = $h_1 % 30;
        $h_code = substr(self::H_KEY, $h_a1, 1) . substr(self::H_KEY, $h_a2, 1) . $h_2;

        return array(
            'x' => $h_x,
            'y' => $h_y,
            'code' => $h_code,
            'level' => $level,
            'latitude' => $z_loc_y,
            'longitude' => $z_loc_x
        );
    }
    public static function getZoneByCode($code) {
        $level  = strlen($code);
        $h_size = self::_calcHexSize($level);
        $unit_x = 6 * $h_size;
        $unit_y = 6 * $h_size * self::H_K;
        $h_x    = 0;
        $h_y    = 0;
        $h_dec9 = strpos(self::H_KEY, substr($code, 0, 1)) * 30 + strpos(self::H_KEY, substr($code, 1, 1)) . substr($code, 2);

        if (preg_match('/[15]/', substr($h_dec9, 0, 1)) &&
            preg_match('/[^125]/', substr($h_dec9, 1, 1)) &&
            preg_match('/[^125]/', substr($h_dec9, 2, 1))
        ) {
            if (substr($h_dec9, 0, 1) === 5) {
                $h_dec9 = '7' . substr($h_dec9, 1, strlen($h_dec9));
            }
            
            else if (substr($h_dec9, 0, 1) === 1) {
                $h_dec9 = '3' . substr($h_dec9, 1, strlen($h_dec9));
            }
        }

        $d9xlen = strlen($h_dec9);

        for ($i = 0; $i < $level + 1 - $d9xlen; $i++) {
            $h_dec9 = '0' . $h_dec9;
            $d9xlen++;
        }

        $h_dec3 = '';

        for ($i = 0; $i < $d9xlen; $i++) {
            $h_dec0 = base_convert(substr($h_dec9, $i, 1), 10, 3);

            if (is_null($h_dec0)) {
                $h_dec3 .= "00";
            }
            
            else if (strlen($h_dec0) === 1) {
                $h_dec3 .= '0';
            }

            $h_dec3 .= $h_dec0;
        }

        $h_decx = array();
        $h_decy = array();

        for ($i = 0; $i < strlen($h_dec3) / 2; $i++) {
            $h_decx[$i] = substr($h_dec3, $i * 2, 1);
            $h_decy[$i] = substr($h_dec3, $i * 2 + 1, 1);
        }

        for ($i = 0; $i <= $level; $i++) {
            $h_pow = pow(3, $level - $i);

            if ((int) $h_decx[$i] === 0) {
                $h_x -= $h_pow;
            }
            
            else if ((int) $h_decx[$i] === 2) {
                $h_x += $h_pow;
            }

            if ((int) $h_decy[$i] === 0) {
                $h_y -= $h_pow;

            }
            
            else if ((int) $h_decy[$i] === 2) {
                $h_y += $h_pow;
            }
        }

        $h_lat_y = (self::H_K * $h_x * $unit_x + $h_y * $unit_y) / 2;
        $h_lon_x = ($h_lat_y - $h_y * $unit_y) / self::H_K;
        $h_loc = self::_xy2loc($h_lon_x, $h_lat_y);

        if ($h_loc['lon'] > 180) {
            $h_loc['lon'] -= 360;
            $h_x -= pow(3, $level);
            $h_y += pow(3, $level);
        }
        
        else if ($h_loc['lon'] < -180) {
            $h_loc['lon'] += 360;
            $h_x += pow(3, $level);
            $h_y -= pow(3, $level);
        }

        return array(
            'x' => $h_x,
            'y' => $h_y,
            'code' => $code,
            'level' => strlen($code) - 2,
            'latitude' => $h_loc['lat'],
            'longitude' => $h_loc['lon']
        );
    }
    public static function getHexCoordsByZone($zone)
    {
        $h_lat  = $zone['latitude'];
        $h_lon  = $zone['longitude'];
        $h_xy   = self::_loc2xy($h_lon, $h_lat);
        $h_x    = $h_xy['x'];
        $h_y    = $h_xy['y'];
        $h_deg  = tan(pi() * (60 / 180));
        $h_size = strlen($zone['code']);

        $h_top = self::_xy2locLatitude($h_x, $h_y + $h_deg * $h_size);
        $h_btm = self::_xy2locLatitude($h_x, $h_y - $h_deg * $h_size);

        $h_l  = self::_xy2locLongitude($h_x - 2 * $h_size, $h_y);
        $h_r  = self::_xy2locLongitude($h_x + 2 * $h_size, $h_y);
        $h_cl = self::_xy2locLongitude($h_x - 1 * $h_size, $h_y);
        $h_cr = self::_xy2locLongitude($h_x + 1 * $h_size, $h_y);

        return array(
            array('latitude' =>  $h_lat, 'longitude' => $h_l),
            array('latitude' =>  $h_top, 'longitude' => $h_cl),
            array('latitude' =>  $h_top, 'longitude' => $h_cr),
            array('latitude' =>  $h_lat, 'longitude' => $h_r),
            array('latitude' =>  $h_btm, 'longitude' => $h_cr),
            array('latitude' =>  $h_btm, 'longitude' => $h_cl)
        );
    }

    /**
     * private
     */

    public function setCoords()
    {
        if (isset($this->code) &&
            isset($this->latitude) &&
            isset($this->longitude)
        ) {
            $this->coords = self::getHexCoordsByZone(array(
                'code' => $this->code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ));
        }
        return $this;
    }

    /**
     * private static
     */

    private static function _calcHexSize($level) {
        return self::H_BASE / pow(3, $level + 1);
    }
    private static function _loc2xy($lon, $lat)
    {
        $x = $lon * self::H_BASE / 180;
        $y = log(tan((90 + $lat) * pi() / 360)) / (pi() / 180);
        $y *= self::H_BASE / 180;

        return array(
            'x' => $x,
            'y' => $y
        );
    }
    private static function _xy2loc($x, $y)
    {
        $lon = ($x / self::H_BASE) * 180;
        $lat = ($y / self::H_BASE) * 180;
        $lat = 180 / pi() * (2 * atan(exp($lat * pi() / 180)) - pi() / 2);

        return array(
            'lat' => $lat,
            'lon' => $lon
        );
    }
    private static function _xy2locLatitude($x, $y)
    {
        $coord = self::_xy2loc($x, $y);
        return $coord['lat'];
    }
    private static function _xy2locLongitude($x, $y)
    {
        $coord = self::_xy2loc($x, $y);
        return $coord['lon'];
    }
}

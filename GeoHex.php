<?php

/**
 * GeoHex by @sa2da (http://geogames.net) is licensed under Creative Commons BY-SA 2.1 Japan License. 
 *
 * @category  GeoHex
 * @package   GeoHex
 * @copyright Tonchidot, Inc. KITA, Junpei
 * @version   $Id
 */
class GeoHex
{
    const VERSION = '3.00';

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
     * @return GeoHex
     * @throws GeoHex_Exception
     */
    public function __construct($parameters = array())
    {
        $keys = array('code', 'level', 'latitude', 'longitude');

        foreach ($keys as $key) {
            if (isset($parameters[$key])) {
                $this->$key = $parameters[$key];
            }
        }

        if (isset($this->latitude) &&
            isset($this->longitude))
        {
            $this->setZoneByLocation(
                $this->latitude, $this->longitude, $this->level);
        }

        else if (isset($this->code)) {
            $this->setZoneByCode($this->code);
        }
    }

    /**
     * public
     */

    public function getLevel()
    {
        return strlen($this->code) - 2;
    }
    public function getHexSize()
    {
        return $this->calcHexSize($this->getLevel() + 2);
    }
    public function getHexCoords()
    {
        $h_lat  = $this->latitude;
        $h_lon  = $this->longitude;
        $h_xy   = $this->loc2xy($h_lon, $h_lat);
        $h_x    = $h_xy['x'];
        $h_y    = $h_xy['y'];
        $h_deg  = tan(pi() * (60 / 180));
        $h_size = $this->getHexSize();

        $h_top = $this->xy2locLatitude($h_x, $h_y + $h_deg * $h_size);
        $h_btm = $this->xy2locLatitude($h_x, $h_y - $h_deg * $h_size);

        $h_l  = $this->xy2locLongitude($h_x - 2 * $h_size, $h_y);
        $h_r  = $this->xy2locLongitude($h_x + 2 * $h_size, $h_y);
        $h_cl = $this->xy2locLongitude($h_x - 1 * $h_size, $h_y);
        $h_cr = $this->xy2locLongitude($h_x + 1 * $h_size, $h_y);

        return array(
            array('latitude' =>  $h_lat, 'longitude' => $h_l),
            array('latitude' =>  $h_top, 'longitude' => $h_cl),
            array('latitude' =>  $h_top, 'longitude' => $h_cr),
            array('latitude' =>  $h_lat, 'longitude' => $h_r),
            array('latitude' =>  $h_btm, 'longitude' => $h_cr),
            array('latitude' =>  $h_btm, 'longitude' => $h_cl)
        );
    }
    public function setZoneByLocation($latitude, $longitude, $level = null)
    {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;

        if (isset($level)) {
            $this->level = $level;
        }

        $this->valid('latitude')->valid('longitude')->valid('level');

        $zone = self::getZoneByLocation(
            $this->latitude, $this->longitude, $this->level);

        $this->x = $zone['x'];
        $this->y = $zone['y'];
        $this->code = $zone['code'];

        return $this->setCoords();
    }
    public function setZoneByCode($code)
    {
        $this->code = $code;

        $this->valid('code');

        $zone = self::getZoneByCode($this->code);

        $this->x = $zone['x'];
        $this->y = $zone['y'];
        $this->latitude = $zone['latitude'];
        $this->longitude = $zone['longitude'];

        return $this->setCoords();
    }
    public function setZoneByLevel($level)
    {
        $this->level = $level;
        $this->valid('level');

        return $this->setZoneByLocation(
            $this->latitude, $this->longitude, $level);
    }

    /**
     * public static
     */

    public static function getZoneByLocation($lat, $lon, $level) {
        $level   += 2;
        $h_size   = self::_calcHexSize($level);
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

        for ($i = 0; $i <= $level; $i++) {
            $h_pow = pow(3, $level - $i);

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
        }
        
        else if ($h_loc['lon'] < -180) {
            $h_loc['lon'] += 360;
        }

        return array(
            'x' => $h_x,
            'y' => $h_y,
            'code' => $code,
            'latitude' => $h_loc['lat'],
            'longitude' => $h_loc['lon']
        );
    }

    /**
     * private
     */

    private function calcHexSize($level)
    {
        return self::_calcHexSize($level);
    }
    private function loc2xy($lon, $lat)
    {
        return self::_loc2xy($lon, $lat);
    }
    private function xy2loc($x, $y)
    {
        return self::_xy2loc($x, $y);
    }
    private function xy2locLatitude($x, $y)
    {
        return self::_xy2locLatitude($x, $y);
    }
    private function xy2locLongitude($x, $y)
    {
        return self::_xy2locLongitude($x, $y);
    }
    private function valid($key)
    {
        if (!isset($this->$key)) {
            require_once('GeoHex/Exception.php');
            throw new GeoHex_Exception("not set $key.");
        }

        return $this;
    }
    private function setCoords()
    {
        $this->valid('code')->valid('latitude')->valid('longitude');
        $this->coords = $this->getHexCoords();
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

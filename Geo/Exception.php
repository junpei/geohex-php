<?php

namespace Geo;

/**
 * \Geo\Exception
 * GeoHex by @sa2da (http://geogames.net) is licensed under Creative Commons BY-SA 2.1 Japan License.
 *
 * @category  \Geo
 * @package   \Geo
 * @copyright Copyright (c) 2011 Tonthidot Corporation. (http://www.tonchidot.com)
 * @license   http://creativecommons.org/licenses/by-sa/2.1/jp/
 * @author    KITA, Junpei
 * @version   $Id
 */
class Exception extends \Exception
{
    /**
     * construct
     *
     * @param  string    $message
     * @param  integer   $code
     * @param  Exception $previous
     * @return void
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

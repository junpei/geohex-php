<?php

/**
 * GeoHex Exception
 *
 * @category  GeoHex
 * @package   GeoHex
 * @copyright Tonchidot, Inc. KITA, Junpei
 * @version   $Id
 */
class GeoHex_Exception extends Exception
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

<?php
/**
 * Created by PhpStorm.
 * User: smile
 * Date: 16-1-9
 * Time: 下午10:57
 */

namespace pinst\helper;


class StringHelper
{
    /**
     * Generates a crc checksum same on 32 and 64-bit platforms.
     *
     * @param string $string Input string
     * @return integer
     */
    public static function crc($string)
    {
        $crc = crc32($string);
        if ($crc & 0x80000000) {
            $crc ^= 0xffffffff;
            $crc++;
            $crc = -$crc;
        }
        return $crc;
    }
    /**
     * Generates a random string of given length.
     *
     * @param integer $length String length
     * @return string
     */
    public static function random($length)
    {
        static $chars = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random = '';
        for ($i = 1; $i <= $length; $i++) {
            $random .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $random;
    }


    /**
     * Htmlspecialchars function alias with some parameters automatically set.
     *
     * @param string $string Input string
     * @param integer $quoteStyle Quote style
     * @param boolean $doubleEncode Prevent from double encoding
     * @return string
     */
    public static function escape($string, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
    {
        return @htmlspecialchars($string, (int) $quoteStyle, 'utf-8', (bool) $doubleEncode);
    }

    /**
     * Converts given size in bytes to kB, MB, GB, TB or PB
     * and appends the appropriate unit.
     *
     * @param float $size Input size
     * @param string $decimalPoint Decimal point
     * @param string $thousandsSeparator Thousands separator
     * @return string
     */
    public static function formatBytes($size, $decimalPoint = ',', $thousandsSeparator = ' ')
    {
        static $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        foreach ($units as $unit) {
            if ($size < 1024) {
                break;
            }
            $size = $size / 1024;
        }
        $decimals = ('B' === $unit) || ('kB' === $unit) ? 0 : 1;
        return number_format($size, $decimals, $decimalPoint, $thousandsSeparator) . ' ' . $unit;
    }
}
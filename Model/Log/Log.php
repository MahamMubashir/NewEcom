<?php

namespace NewEcom\ShopSmart\Model\Log;

class Log
{
    const LOG_PATH          = '/var/log/';
    const DEFAULT_FILE      = 'ShopSmart.log';
    const TEMPLATE          = "[%s] %s: %s\n";

    const DATE_FORMAT       = 'Y.m.d H:i:s';
    const TYPE_ERROR        = 'ERROR';
    const TYPE_INFO         = 'INFO';
    const TYPE_WARNING      = 'WARNING';

    /**
     * Checks directory and create it if not exist
     *
     * @param string $filename
     */
    public static function checkDir($filename)
    {
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    /**
     * Saving data into log file
     *
     * @param $message
     * @param string $filename
     * @param null $type
     */
    public static function add($message, $filename = '', $type = null)
    {
        if (!is_string($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }

        if (!isset($type)) {
            $type = static::TYPE_INFO;
        }

        $filename = trim($filename, '\/');
        if (empty($filename)) {
            $filename = static::DEFAULT_FILE;
        }
        static::checkDir(BP . static::LOG_PATH . $filename);
        file_put_contents(
            BP . static::LOG_PATH . $filename,
            sprintf(
                static::TEMPLATE,
                date(static::DATE_FORMAT),
                $type,
                $message
            ),
            FILE_APPEND
        );
    }

    /**
     * Saving info data into log file
     *
     * @param mixed $message
     * @param string $filename
     */
    public static function Info($message, $filename = '')
    {
        static::add($message, $filename, static::TYPE_INFO);
    }

    /**
     * Saving error data into log file
     *
     * @param mixed $message
     * @param string $filename
     */
    public static function Error($message, $filename = '')
    {
        static::add($message, $filename, static::TYPE_ERROR);
    }

    /**
     * Saving error data into log file
     *
     * @param mixed $message
     * @param string $filename
     */
    public static function Warning($message, $filename = '')
    {
        static::add($message, $filename, static::TYPE_WARNING);
    }
}

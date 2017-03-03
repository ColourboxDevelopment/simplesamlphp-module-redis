<?php
/* vim: set ts=4 sw=4 tw=0 et :*/

namespace Predis;

/**
 * Mock implenmentation
 */
class Client
{
    public static $setKey = null;
    public static $setValue = null;
    public static $getKey = null;
    public static $expireKey = null;
    public static $expireValue = null;
    public static $deleteKey = null;

    public function __construct($parameters = null, $options = null)
    {
    }

    public function get($key)
    {
        self::$getKey   = $key;

        if ($key == 'unittest.test.key') {
            return serialize(['ding' => 'bat']);
        }
        return null;
    }

    public function set($key, $value)
    {
        self::$setKey   = $key;
        self::$setValue = $value;
    }

    public function expireat($key, $expire)
    {
        self::$expireKey   = $key;
        self::$expireValue = $expire;
    }

    public function del($key)
    {
        self::$deleteKey   = $key;
    }
}

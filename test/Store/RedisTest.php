<?php

namespace Predis {
    class Client
    {
        public static $setKey = null;
        public static $setValue = null;
        public static $getKey = null;
        public static $expireKey = null;
        public static $expireValue = null;
        public static $deleteKey = null;

        public function get($key)
        {
            self::$getKey   = $key;

            if ($key == 'simpleSAMLphp.test.key') {
                return serialize(['ding' => 'bat']);
            }
            return null;
        }

        public function set($key, $value)
        {
            self::$setKey   = $key;
            self::$setValue = $value;
        }

        public function expire($key, $expire)
        {
            self::$expireKey   = $key;
            self::$expireValue = $expire;
        }

        public function del($key)
        {
            self::$deleteKey   = $key;
        }
    }
}

namespace {
    use Nulpunkt\PhpStub\Stub;

    class SimpleSAML_Store
    {
    }

    class SimpleSAML_Configuration
    {
        public static function getConfig()
        {
            return new SimpleSAML_Configuration;
        }

        public function getString($value)
        {
            if ($value == 'host') {
                return 'localhost';
            }
            if ($value == 'prefix') {
                return 'simpleSAMLphp';
            }
            throw new ErrorException('Called with unexpected value');
        }

        public function getInteger($key)
        {
            if ($key == 'lifetime') {
                return 288000;
            }
            throw new ErrorException('Called with unexpected value');
        }
    }

    class RedisTest extends \PHPUnit_Framework_TestCase
    {
        public function setUp()
        {
            Predis\Client::$setKey      = null;
            Predis\Client::$setValue    = null;
            Predis\Client::$getKey      = null;
            Predis\Client::$expireKey   = null;
            Predis\Client::$expireValue = null;
            Predis\Client::$deleteKey   = null;
        }

        public function testSetKeyInRedis()
        {
            $store = new sspmod_redis_Store_Redis();
            $store->set('test', 'key', ['one', 'two']);

            $this->assertEquals('simpleSAMLphp.test.key', Predis\Client::$setKey);
            $this->assertEquals(serialize(['one', 'two']), Predis\Client::$setValue);
            $this->assertEquals('simpleSAMLphp.test.key', Predis\Client::$expireKey);
            $this->assertEquals(288000, Predis\Client::$expireValue);
        }

        public function testSetKeyWithExpireInRedis()
        {
            $store = new sspmod_redis_Store_Redis();
            $store->set('test', 'key', ['one', 'two'], 11);

            $this->assertEquals('simpleSAMLphp.test.key', Predis\Client::$setKey);
            $this->assertEquals(serialize(['one', 'two']), Predis\Client::$setValue);
            $this->assertEquals('simpleSAMLphp.test.key', Predis\Client::$expireKey);
            $this->assertEquals(11, Predis\Client::$expireValue);
        }

        public function testGetExistingKey()
        {
            $store = new sspmod_redis_Store_Redis();
            $res = $store->get('test', 'key');

            $this->assertEquals('simpleSAMLphp.test.key', Predis\Client::$getKey);
            $this->assertEquals(['ding' => 'bat'], $res);
        }

        public function testGetNonExistingKey()
        {
            $store = new sspmod_redis_Store_Redis();
            $res = $store->get('test', 'nokey');

            $this->assertEquals('simpleSAMLphp.test.nokey', Predis\Client::$getKey);
            $this->assertFalse($res);
        }

        public function testDeleteKey()
        {
            $store = new sspmod_redis_Store_Redis();
            $res = $store->delete('test', 'nokey');

            $this->assertEquals('simpleSAMLphp.test.nokey', Predis\Client::$deleteKey);
        }
    }
}

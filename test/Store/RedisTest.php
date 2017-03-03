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

        public function __construct($host)
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
}

namespace {
    use Nulpunkt\PhpStub\Stub;

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

            SimpleSAML_Configuration::setConfigDir(__DIR__ . DIRECTORY_SEPARATOR . 'fixture');
        }

        public function getHasHostValues()
        {
            return [
                [true],
                [false]
            ];
        }

        /**
         * @dataProvider getHasHostValues
         */
        public function testSetKeyInRedis($hasHost)
        {
            $store = new sspmod_redis_Store_Redis();
            $store->set('test', 'key', ['one', 'two']);

            $this->assertEquals('unittest.test.key', Predis\Client::$setKey);
            $this->assertEquals(serialize(['one', 'two']), Predis\Client::$setValue);
            $this->assertEquals('unittest.test.key', Predis\Client::$expireKey);
            /**
             * Cannot be tested, because time is used and code is not in
             * namespace, so the normal trick does not work.
             */
            //$this->assertEquals(1427739616, \Predis\Client::$expireValue);
        }

        /**
         * @dataProvider getHasHostValues
         */
        public function testSetKeyWithExpireInRedis($hasHost)
        {
            //SimpleSAML_Configuration::setHasHost($hasHost);
            $store = new sspmod_redis_Store_Redis();
            $store->set('test', 'key', ['one', 'two'], 11);

            $this->assertEquals('unittest.test.key', Predis\Client::$setKey);
            $this->assertEquals(serialize(['one', 'two']), Predis\Client::$setValue);
            $this->assertEquals('unittest.test.key', Predis\Client::$expireKey);
            $this->assertEquals(11, Predis\Client::$expireValue);
        }

        /**
         * @dataProvider getHasHostValues
         */
        public function testGetExistingKey($hasHost)
        {
            //SimpleSAML_Configuration::setHasHost($hasHost);
            $store = new sspmod_redis_Store_Redis();
            $res = $store->get('test', 'key');

            $this->assertEquals('unittest.test.key', Predis\Client::$getKey);
            $this->assertEquals(['ding' => 'bat'], $res);
        }

        /**
         * @dataProvider getHasHostValues
         */
        public function testGetNonExistingKey($hasHost)
        {
            //SimpleSAML_Configuration::setHasHost($hasHost);
            $store = new sspmod_redis_Store_Redis();
            $res = $store->get('test', 'nokey');

            $this->assertEquals('unittest.test.nokey', Predis\Client::$getKey);
            $this->assertNull($res);
        }

        /**
         * @dataProvider getHasHostValues
         */
        public function testDeleteKey($hasHost)
        {
            //SimpleSAML_Configuration::setHasHost($hasHost);
            $store = new sspmod_redis_Store_Redis();
            $res = $store->delete('test', 'nokey');

            $this->assertEquals('unittest.test.nokey', Predis\Client::$deleteKey);
        }
    }
}

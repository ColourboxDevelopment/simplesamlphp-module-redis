<?php
/* vim: set ts=4 sw=4 tw=0 et :*/

class DualRedisTest extends \PHPUnit_Framework_TestCase
{
    private function mockPredisExpectingGetKeyAndReturnsValue($key, $value)
    {
        $mock = $this->getMock('\Predis\Client', ['get'], [null]);
        $mock->expects($this->once())
            ->method("get")
            ->with($key)
            ->will($this->returnValue($value));
        return $mock;
    }

    private function mockPredisExpectingSetKeyValue($key, $value)
    {
        $mock = $this->getMock('\Predis\Client', ['set'], [null]);
        $mock->expects($this->once())
            ->method("set")
            ->with($key, $value);
        return $mock;
    }

    private function mockPredisWithKeys($pattern, $keys)
    {
        $mock = $this->getMock('\Predis\Client', ['keys'], [null]);
        $mock->expects($this->once())
            ->method("keys")
            ->with($pattern)
            ->will($this->returnValue($keys));
        return $mock;
    }

    private function mockPredisExpectingDelete($key)
    {
        $mock = $this->getMock('\Predis\Client', ['del'], [null]);
        $mock->expects($this->once())
            ->method("del")
            ->with($key);
        return $mock;
    }

    private function mockPredisExpectingExpireat($key, $timestamp)
    {
        $mock = $this->getMock('\Predis\Client', ['expireat'], [null]);
        $mock->expects($this->once())
            ->method("expireat")
            ->with($key, $timestamp);
        return $mock;
    }

    private function mockPredisExpectingExpire($key, $delta)
    {
        $mock = $this->getMock('\Predis\Client', ['expire'], [null]);
        $mock->expects($this->once())
            ->method("expire")
            ->with($key, $delta);
        return $mock;
    }

    private function mockPredisExpectingExists($key, $boolean)
    {
        $mock = $this->getMock('\Predis\Client', ['exists'], [null]);
        $mock->expects($this->once())
            ->method("exists")
            ->with($key)
            ->will($this->returnValue($boolean));
        return $mock;
    }

    public function testThatGetUsesNewHostIfItHasAValue()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            null,
            $this->mockPredisExpectingGetKeyAndReturnsValue("xyzzy", 42)
        );
        $this->assertSame(42, $redis->get("xyzzy"));
    }

    public function testThatGetUsesOldHostIfNewHostDoesNotHaveAValue()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            $this->mockPredisExpectingGetKeyAndReturnsValue("xyzzy", 420),
            $this->mockPredisExpectingGetKeyAndReturnsValue("xyzzy", null)
        );
        $this->assertSame(420, $redis->get("xyzzy"));
    }

    public function testThatSetUsesNewHost()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            null,
            $this->mockPredisExpectingSetKeyValue("xyzzy", 42)
        );
        $redis->set("xyzzy", 42);
    }

    public function testThatKeysReturnsUniqueKeysFromBothOldAndNewHost()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            $this->mockPredisWithKeys("foo", ["a", "b"]),
            $this->mockPredisWithKeys("foo", ["b", "c", "d"])
        );
        $this->assertSame(["b", "c", "d", "a"], $redis->keys("foo"));
    }

    public function testThatDeleteUsesBothHosts()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            $this->mockPredisExpectingDelete("quux"),
            $this->mockPredisExpectingDelete("quux")
        );
        $redis->del("quux");
    }

    public function testThatExpireatOnlyUsesNewHost()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            null,
            $this->mockPredisExpectingExpireat("quux", 1234)
        );
        $redis->expireat("quux", 1234);
    }

    public function testThatExpireOnlyUsesNewHost()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            null,
            $this->mockPredisExpectingExpire("quux", 4444)
        );
        $redis->expire("quux", 4444);
    }

    public function testThatExistsUsesNewHostFirst()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            null,
            $this->mockPredisExpectingExists("quux", true)
        );
        $this->assertTrue($redis->exists("quux"));
    }

    public function testThatExistsUsesOldHostSecond()
    {
        $redis = new sspmod_redis_Redis_DualRedis(
            $this->mockPredisExpectingExists("quux", true),
            $this->mockPredisExpectingExists("quux", false)
        );
        $this->assertTrue($redis->exists("quux"));
    }
}

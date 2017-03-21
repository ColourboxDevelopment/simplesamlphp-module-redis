<?php
/**
 * Redis store for simpleSAMLphp
 *
 * This store uses the Redis document store to store data from simpleSAMLphp.
 * It implements the simpleSAMLphp datastore API, for easy integration with
 * other parts of simpleSAMLphp.
 *
 * @author    Jacob Christiansen jacob@colourbox.com
 * @copyright 2015 Colourbox ApS
 * @license   http://opensource.org/licenses/MIT MIT-license
 */
class sspmod_redis_Store_Redis extends SimpleSAML_Store
{
    private $redis;
    private $prefix;
    private $lifeTime;

    public function __construct()
    {
        $redisConfig = SimpleSAML_Configuration::getConfig('module_redis.php');

        if ($redisConfig->hasValue('oldHost')) {
            $oldHost = $redisConfig->getValue('oldHost');
            $this->redis = new sspmod_redis_Redis_DualRedis(
                new Predis\Client($oldHost['parameters'], $oldHost['options']),
                new Predis\Client($redisConfig->getValue('parameters'), $redisConfig->getValue('options'))
            );
        } else {
            $this->redis = new Predis\Client($redisConfig->getValue('parameters'), $redisConfig->getValue('options'));
        }
        
        $this->auth();
        
        $this->prefix   = $redisConfig->getString('prefix', 'simpleSAMLphp');
        $this->lifeTime = $redisConfig->getInteger('lifetime', 28800); // 8 hours
    }
    
    protected function auth()
    {
        $redisConfig = SimpleSAML_Configuration::getConfig('module_redis.php');
        if($auth = $redisConfig->getString('auth', ''))
        {
            $this->redis->auth($auth);
        }
    }

    /**
     * Retrieve a value from Redis
     *
     * @param string $type The datatype
     * @param string $key  The key
     * @return mixed|NULL  The value
     */
    public function get($type, $key)
    {
        $redisKey = "{$this->prefix}.$type.$key";
        $value = $this->redis->get($redisKey);

        if (is_null($value)) {
            return null;
        }

        return unserialize($value);
    }

    /**
     * Save a value to Redis
     *
     * If no expiration time is given, then the expiration time is set to the
     * session duration.
     *
     * @param string $type     The datatype
     * @param string $key      The key
     * @param mixed $value     The value
     * @param int|NULL $expire The expiration time (unix timestamp), or NULL if it never expires
     */
    public function set($type, $key, $value, $expire = null)
    {
        $redisKey = "{$this->prefix}.$type.$key";
        if (is_null($expire))
        {
            $expire = time() + $this->lifeTime;
        }
        
        try
        {
            $this->redis->set($redisKey, serialize($value));
            $this->redis->expireat($redisKey, $expire);
        }
        catch(\ Exception $e)
        {
            //on shutdown sometime the auth is not set !
            $this->auth();
            $this->redis->set($redisKey, serialize($value));
            $this->redis->expireat($redisKey, $expire);
        }
    }

    /**
     * Delete a value from Redis
     *
     * @param string $type The datatype
     * @param string $key  The key
     */
    public function delete($type, $key)
    {
        $redisKey = "{$this->prefix}.$type.$key";
        try
        {
            $this->redis->del($redisKey);
        }
        catch(\ Exception $e)
        {
            //on shutdown sometime the auth is not set !
            $this->auth();
            $this->redis->del($redisKey);
        }
    }
}

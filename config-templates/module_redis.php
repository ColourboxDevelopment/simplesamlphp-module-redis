<?php
/**
 * Configuration template for the Redis moduel for simpleSAMLphp
 */
$config = array (
    // Redis server
    'host' => 'tcp://localhost:6379',

    // Redis 3.0 cluster
    //'cluster' => array('tcp://1.0.0.1:6379', 'tcp://1.0.0.2:6379', 'tcp://1.0.0.n:6379'),

    // Key prefix
    'prefix' => 'simplaSAMLphp',

    // Lifitime for all non expiring keys
    'lifetime' => 288000
);

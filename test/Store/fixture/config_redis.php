<?php
/**
 * Configuration template for the Redis moduel for simpleSAMLphp
 */
$config = array (
    // Redis server
    'host' => 'tcp://localhost:6379',

    // Key prefix
    'prefix' => 'simplaSAMLphp',

    // Lifitime for all non expiring keys
    'lifetime' => 288000
);

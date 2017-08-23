<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 13:57
 */

require __DIR__.'/../vendor/autoload.php';

date_default_timezone_set('PRC');

$config = \Kafka\ProducerConfig::getInstance();
$config->setMetadataRefreshIntervalMs(10000);
$config->setMetadataBrokerList('127.0.0.1:9092');
$config->setBrokerVersion('0.10.0.1');
$config->setRequiredAck(1);
$config->setIsAsyn(false);
$config->setProduceInterval(500);

$producer = new \Kafka\Producer(function() {
    return [
        [
            'topic' => 'test',
            'value' => 'test....message.',
            'key' => '',
        ],
    ];
});

$producer->success(function($result) {
    var_dump($result);
});

$producer->error(function($errorCode) {
    var_dump($errorCode);
});
$producer->send(true);
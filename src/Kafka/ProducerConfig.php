<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 13:59
 */

namespace Kafka;

use Kafka\Exception\Config as ExceptionConfig;

class ProducerConfig extends Config {
    use \SingletonTrait;

    protected static $defaults = [
        'requiredAck'     => 1,
        'timeout'         => 5000,
        'isAsyn'          => false,
        'requestTimeout'  => 6000,
        'produceInterval' => 100,
    ];

    public function setRequestTimeout($requestTimeout) {
        if (!is_numeric($requestTimeout) || $requestTimeout < 1 || $requestTimeout > 900000) {
            throw new ExceptionConfig('Set request timeout value is invalid, must set it 1 .. 900000');
        }
        static::$options['requestTimeout'] = $requestTimeout;
    }

    public function setProduceInterval($produceInterval) {
        if (!is_numeric($produceInterval) || $produceInterval < 1 || $produceInterval > 900000) {
            throw new ExceptionConfig('Set produce interval timeout value is invalid, must set it 1 .. 900000');
        }
        static::$options['produceInterval'] = $produceInterval;
    }


    public function setTimeout($timeout) {
        if (!is_numeric($timeout) || $timeout < 1 || $timeout > 900000) {
            throw new ExceptionConfig('Set timeout value is invalid, must set it 1 .. 900000');
        }
        static::$options['timeout'] = $timeout;
    }

    public function setRequiredAck($requiredAck) {
        if (!is_numeric($requiredAck) || $requiredAck < -1 || $requiredAck > 1000) {
            throw new ExceptionConfig('Set required ack value is invalid, must set it -1 .. 1000');
        }
        static::$options['requiredAck'] = $requiredAck;
    }

    public function setIsAsyn($asyn) {
        if (!is_bool($asyn)) {
            throw new ExceptionConfig('Set isAsyn value is invalid, must set it bool value');
        }
        static::$options['isAsyn'] = $asyn;
    }
}
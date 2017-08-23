<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 13:59
 */

namespace Kafka;

use Kafka\Exception\Config as ExceptionConfig;

abstract class Config {
    protected static $options = [];

    private static $defaults = [
        'clientId'                  => 'kafka-php',
        'brokerVersion'             => '0.10.1.0',
        'metadataBrokerList'        => '',
        'messageMaxBytes'           => '1000000',
        'metadataRequestTimeoutMs'  => '60000',
        'metadataRefreshIntervalMs' => '300000',
        'metadataMaxAgeMs'          => -1,
    ];

    public function __call($name, $args) {

        if (strpos($name, 'get') === 0 || strpos($name, 'iet') === 0) {
            $option = strtolower(substr($name, 3, 1)) . substr($name, 4);
            if (isset(self::$options[$option])) {
                return self::$options[$option];
            }

            if (isset(self::$defaults[$option])) {
                return self::$defaults[$option];
            }

            return false;
        }

        if (strpos($name, 'set') === 0) {
            if (count($args) != 1) {
                return false;
            }
            $option                   = strtolower(substr($name, 3, 1)) . substr($name, 4);
            static::$options[$option] = $args[0];

            return true;
        }

        return true;
    }

    public function setClientId($val) {
        $client = trim($val);
        if ($client == '') {
            throw new ExceptionConfig('Set clientId value is invalid, must is not empty string.');
        }
        static::$options['clientId'] = $client;
    }


    public function setBrokerVersion($version) {
        $version = trim($version);
        if ($version == '' || version_compare($version, '0.8.0') < 0) {
            throw new ExceptionConfig('Set broker version value is invalid, must is not empty string and gt 0.8.0.');
        }
        static::$options['brokerVersion'] = $version;
    }

    public function setMetadataBrokerList($list) {
        if (trim($list) == '') {
            throw new ExceptionConfig('Set broker list value is invalid, must is not empty string');
        }
        $tmp   = explode(',', trim($list));
        $lists = array();
        foreach ($tmp as $key => $val) {
            if (trim($val) != '') {
                $lists[] = $val;
            }
        }
        if (empty($lists)) {
            throw new ExceptionConfig('Set broker list value is invalid, must is not empty string');
        }
        foreach ($lists as $val) {
            $hostInfo = explode(':', $val);
            foreach ($hostInfo as $key => $hostVal) {
                if (trim($val) == '') {
                    unset($hostInfo[$key]);
                }
            }
            if (count($hostInfo) != 2) {
                throw new ExceptionConfig('Set broker list value is invalid, must is not empty string');
            }
        }

        static::$options['metadataBrokerList'] = $list;
    }

    public function clear() {
        static::$options = [];
    }

    public function setMessageMaxBytes($messageMaxBytes) {
        if (!is_numeric($messageMaxBytes) || $messageMaxBytes < 1000 || $messageMaxBytes > 1000000000) {
            throw new ExceptionConfig('Set message max bytes value is invalid, must set it 1000 .. 1000000000');
        }
        static::$options['messageMaxBytes'] = $messageMaxBytes;
    }

    public function setMetadataRequestTimeoutMs($metadataRequestTimeoutMs) {
        if (!is_numeric($metadataRequestTimeoutMs) || $metadataRequestTimeoutMs < 10
            || $metadataRequestTimeoutMs > 900000
        ) {
            throw new ExceptionConfig('Set metadata request timeout value is invalid, must set it 10 .. 900000');
        }
        static::$options['metadataRequestTimeoutMs'] = $metadataRequestTimeoutMs;
    }

    public function setMetadataRefreshIntervalMs($metadataRefreshIntervalMs) {
        if (!is_numeric($metadataRefreshIntervalMs) || $metadataRefreshIntervalMs < 10
            || $metadataRefreshIntervalMs > 3600000
        ) {
            throw new ExceptionConfig('Set metadata refresh interval value is invalid, must set it 10 .. 3600000');
        }
        static::$options['metadataRefreshIntervalMs'] = $metadataRefreshIntervalMs;
    }

    public function setMetadataMaxAgeMs($metadataMaxAgeMs) {
        if (!is_numeric($metadataMaxAgeMs) || $metadataMaxAgeMs < 1
            || $metadataMaxAgeMs > 86400000
        ) {
            throw new ExceptionConfig('Set metadata max age value is invalid, must set it 1 .. 86400000');
        }
        static::$options['metadataMaxAgeMs'] = $metadataMaxAgeMs;
    }
}
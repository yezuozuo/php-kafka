<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 14:13
 */

namespace Kafka;

use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

trait LoggerTrait {

    public function log($level, $message, array $context = array()) {
        if ($this->logger == null) {
            $this->logger = new NullLogger();
        }
        $this->logger->log($level, $message, $context);
    }

    public function emergency($message, array $context = array()) {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = array()) {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = array()) {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = array()) {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = array()) {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = array()) {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = array()) {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = array()) {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
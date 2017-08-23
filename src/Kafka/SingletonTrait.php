<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 2017/8/23
 * Time: 14:12
 */

/**
 * Class SingletonTrait
 */
trait SingletonTrait {

    use \Psr\Log\LoggerAwareTrait;
    use \Kafka\LoggerTrait;

    protected static $instance = null;

    private function __construct() {}

    /**
     * @return \Kafka\ProducerConfig
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}